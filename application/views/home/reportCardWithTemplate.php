<?php
// Get marksheet template based on student's section (for frontend only)
// Check if section-specific template is configured
$sectionTemplateID = 0;
if (isset($section_id) && !empty($section_id)) {
    $sectionMapping = $this->db->select('template_id')
        ->where(array('branch_id' => $branchID, 'section_id' => $section_id))
        ->get('section_marksheet_template')
        ->row();

    if ($sectionMapping && $sectionMapping->template_id > 0) {
        $sectionTemplateID = $sectionMapping->template_id;
    }
}

// If no section-specific template, fall back to default
if ($sectionTemplateID == 0) {
    $sectionTemplateID = $this->app_lib->getSchoolConfig($branchID, 'default_marksheet_temp')->default_marksheet_temp ?? 0;
}

$marksheet_template = $this->marksheet_template_model->getTemplate($sectionTemplateID, $branchID);

// If template not found, display error and exit
if (empty($marksheet_template) || !is_array($marksheet_template)) {
    echo '<div class="alert alert-danger"><strong>Sorry!</strong> Your result cannot be displayed at this time. Please contact the school administration for assistance.</div>';
    return;
}
?>
<style type="text/css">
    .mark-container {
        height: 100%;
        min-width: 1000px;
        position: relative;
        z-index: 2;
        margin: 0 auto;
        font-size: 12px;
        padding: <?= $marksheet_template['top_space'] . 'px ' . $marksheet_template['right_space'] . 'px ' . $marksheet_template['bottom_space'] . 'px ' . $marksheet_template['left_space'] . 'px' ?>;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        margin: 0 auto;
    }

    @page {
        margin: -2px;
        size: <?php echo $marksheet_template['page_layout'] == 1 ? 'portrait' : 'landscape'; ?>;
    }

    @media print {

        .table-bordered>thead>tr>th,
        .table-bordered>tbody>tr>th,
        .table-bordered>tfoot>tr>th,
        .table-bordered>thead>tr>td,
        .table-bordered>tbody>tr>td,
        .table-bordered>tfoot>tr>td {
            border-color: #000 !important;
            background: transparent !important;
        }
    }

    .table-bordered {
        border-color: #000 !important;
    }

    .background {
        position: absolute;
        z-index: 0;
        width: 100%;
        height: 100%;
        <?php if (empty($marksheet_template['background'])) { ?>background: #fff;
        <?php } else { ?>background-image: url("<?= base_url('uploads/marksheet/' . $marksheet_template['background']) ?>") !important;
        background-repeat: no-repeat !important;
        background-size: 100% 100% !important;
        <?php } ?>
    }

    .skills-section {
        margin-top: 20px;
        page-break-inside: avoid;
    }

    .skills-header {
        background-color: #f5f5f5;
        padding: 10px;
        font-weight: bold;
        margin-top: 15px;
        margin-bottom: 10px;
        border-left: 4px solid #0088cc;
        font-size: 14px;
    }

    .skills-category-affective .skills-header {
        border-left-color: #5bc0de;
    }

    .skills-category-psychomotor .skills-header {
        border-left-color: #5cb85c;
    }

    .skills-category-cognitive .skills-header {
        border-left-color: #f0ad4e;
    }
</style>

<?php
$extINTL = extension_loaded('intl');
if (!empty($studentID)) {
    $CI = &get_instance();
    $result = $CI->exam_model->getStudentReportCard($studentID, $examID, $sessionID, $class_id, $section_id);
    $student = $result['student'];
    $getMarksList = $result['exam'];

    // Get skills ratings (model already loaded in controller)
    $skills_ratings_grouped = $CI->skills_model->getStudentRatingsByCategory($studentID, $examID, $sessionID);

    // Get teacher and head teacher remarks
    $teacher_remarks = '';
    $head_teacher_remarks = '';
    if (!empty($skills_ratings_grouped)) {
        $first_category = reset($skills_ratings_grouped);
        if (!empty($first_category['items'])) {
            $first_item = reset($first_category['items']);
            $teacher_remarks = isset($first_item['teacher_remarks']) ? $first_item['teacher_remarks'] : '';
            $head_teacher_remarks = isset($first_item['head_teacher_remarks']) ? $first_item['head_teacher_remarks'] : '';
        }
    }

    $rankDetail = $this->db->where(array('exam_id ' => $examID, 'enroll_id  ' => $student['enrollID']))->get('exam_rank')->row();
    $getExam = $this->db->where(array('id' => $examID))->get('exam')->row_array();
    $schoolYear = get_type_name_by_id('schoolyear', $sessionID, 'school_year');

    $extendsData = [];
    $extendsData['print_date'] = $print_date;
    $extendsData['schoolYear'] = $schoolYear;
    $extendsData['exam_name'] = $getExam['name'];
    $extendsData['teacher_comments'] = empty($rankDetail->teacher_comments) ? '' : $rankDetail->teacher_comments;
    $extendsData['principal_comments'] = empty($rankDetail->principal_comments) ? '' : $rankDetail->principal_comments;
    $extendsData['jr_teacher_comment'] = $teacher_remarks;
    $extendsData['jr_head_teacher_comment'] = $head_teacher_remarks;
    $header_content = $this->marksheet_template_model->tagsReplace($student, $marksheet_template, $extendsData, 'header_content');
    $footer_content = $this->marksheet_template_model->tagsReplace($student, $marksheet_template, $extendsData, 'footer_content');

    // Check what should be displayed based on template settings
    $show_subjects_table = $marksheet_template && isset($marksheet_template['subjects_table']) && $marksheet_template['subjects_table'] == 1;
    $show_skills = $marksheet_template && isset($marksheet_template['show_skills']) && $marksheet_template['show_skills'] == 1;

?>
    <div style="position: relative; width: 100%; height: 100%;">
        <div class="background"></div>
        <div class="mark-container">
            <?php echo $header_content ?>

            <?php
            // Academic Results Section (only if subjects_table is enabled AND academic data exists)
            if ($show_subjects_table && !empty($getMarksList)):
            ?>
                <table class="table table-condensed table-bordered mt-lg">
                    <thead>
                        <tr>
                            <th>Subjects</th>
                            <?php
                            $markDistribution = json_decode($getExam['mark_distribution'], true);
                            foreach ($markDistribution as $id) {
                            ?>
                                <th><?php echo get_type_name_by_id('exam_mark_distribution', $id)  ?></th>
                            <?php } ?>
                            <?php if ($getExam['type_id'] == 1) { ?>
                                <th>Total</th>
                            <?php } elseif ($getExam['type_id'] == 2) { ?>
                                <th>Grade</th>
                                <th>Point</th>
                                <?php if ($marksheet_template['remark'] == 1) { ?>
                                    <th>Remark</th>
                                <?php } ?>
                            <?php } elseif ($getExam['type_id'] == 3) { ?>
                                <th>Total</th>
                                <th>Grade</th>
                                <th>Point</th>
                                <?php if ($marksheet_template['remark'] == 1) { ?>
                                    <th>Remark</th>
                                <?php } ?>
                            <?php } ?>
                            <?php if ($marksheet_template['subject_position'] == 1) { ?>
                                <th>Subject Position</th>
                            <?php } ?>

                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $colspan = count($markDistribution) + 1;
                        $total_grade_point = 0;
                        $grand_obtain_marks = 0;
                        $grand_full_marks = 0;
                        $result_status = 1;
                        foreach ($getMarksList as $row) {
                        ?>
                            <tr>
                                <td valign="middle"><?= $row['subject_name'] ?></td>
                                <?php
                                $total_obtain_marks = 0;
                                $total_full_marks = 0;
                                $fullMarkDistribution = json_decode($row['mark_distribution'], true);
                                $obtainedMark = json_decode($row['get_mark'], true);
                                foreach ($fullMarkDistribution as $i => $val) {
                                    $obtained_mark = floatval($obtainedMark[$i]);
                                    $fullMark = floatval($val['full_mark']);
                                    $passMark = floatval($val['pass_mark']);
                                    if ($obtained_mark < $passMark) {
                                        $result_status = 0;
                                    }

                                    $total_obtain_marks += $obtained_mark;
                                    $obtained = $row['get_abs'] == 'on' ? 'Absent' : $obtained_mark;
                                    $total_full_marks += $fullMark;
                                ?>
                                    <?php if ($getExam['type_id'] == 1 || $getExam['type_id'] == 3) { ?>
                                        <td valign="middle">
                                            <?php
                                            if ($row['get_abs'] == 'on') {
                                                echo 'Absent';
                                            } else {
                                                echo $obtained_mark . '/' . $fullMark;
                                            }
                                            ?>
                                        </td>
                                    <?php }
                                    if ($getExam['type_id'] == 2) { ?>
                                        <td valign="middle">
                                            <?php
                                            if ($row['get_abs'] == 'on') {
                                                echo 'Absent';
                                            } else {
                                                $percentage_grade = ($obtained_mark * 100) / $fullMark;
                                                $grade = $this->exam_model->get_grade($percentage_grade, $getExam['branch_id']);
                                                echo $grade['name'];
                                            }
                                            ?>
                                        </td>
                                    <?php } ?>
                                <?php
                                }
                                $grand_obtain_marks += $total_obtain_marks;
                                $grand_full_marks += $total_full_marks;
                                ?>
                                <?php if ($getExam['type_id'] == 1 || $getExam['type_id'] == 3) { ?>
                                    <td valign="middle"><?= $total_obtain_marks . "/" . $total_full_marks ?></td>
                                <?php }
                                if ($getExam['type_id'] == 2) {
                                    $colspan += 1;
                                    $percentage_grade = ($total_obtain_marks * 100) / $total_full_marks;
                                    $grade = $this->exam_model->get_grade($percentage_grade, $getExam['branch_id']);
                                    $total_grade_point += $grade['grade_point'];
                                ?>
                                    <td valign="middle"><?= $grade['name'] ?></td>
                                    <td valign="middle"><?= number_format($grade['grade_point'], 2, '.', '') ?></td>
                                    <?php if ($marksheet_template['remark'] == 1) { ?>
                                        <td valign="middle"><?= $grade['remark'] ?></td>
                                    <?php } ?>
                                <?php }
                                if ($getExam['type_id'] == 3) {
                                    $colspan += 2;
                                    $percentage_grade = ($total_obtain_marks * 100) / $total_full_marks;
                                    $grade = $this->exam_model->get_grade($percentage_grade, $getExam['branch_id']);
                                    $total_grade_point += $grade['grade_point'];
                                ?>
                                    <td valign="middle"><?= $grade['name'] ?></td>
                                    <td valign="middle"><?= number_format($grade['grade_point'], 2, '.', '') ?></td>
                                    <?php if ($marksheet_template['remark'] == 1) { ?>
                                        <td valign="middle"><?= $grade['remark'] ?></td>
                                    <?php } ?>
                                <?php } ?>
                                <?php if ($marksheet_template['subject_position'] == 1) { ?>
                                    <td valign="middle"><?php echo $this->exam_progress_model->get_subject_rank($examID, $student['enrollID'], $row['subject_id']); ?></td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                        <?php if ($getExam['type_id'] == 1 || $getExam['type_id'] == 3) { ?>
                            <tr class="text-weight-semibold">
                                <td valign="top">GRAND TOTAL :</td>
                                <td valign="top" colspan="<?= $colspan ?>"><?= $grand_obtain_marks . '/' . $grand_full_marks; ?>, Average : <?php $percentage = ($grand_obtain_marks * 100) / $grand_full_marks;
                                                                                                                                            echo number_format($percentage, 2, '.', '') ?>%</td>
                            </tr>
                        <?php } ?>
                        <?php if ($getExam['type_id'] == 2 || $getExam['type_id'] == 3) { ?>
                            <tr class="text-weight-semibold">
                                <td valign="top">AVERAGE GRADE POINT :</td>
                                <td valign="top" colspan="<?= $colspan ?>"><?= number_format($total_grade_point / count($getMarksList), 2, '.', '') ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <?php if ($getExam['type_id'] == 3) { ?>
                    <p style="font-weight: bold; font-size: 16px;">Total Marks: <?= $grand_obtain_marks ?> / <?= $grand_full_marks ?> (<?= number_format($percentage, 2, '.', '') ?>%)</p>
                <?php } ?>
            <?php endif; ?>

            <?php
            // Skills Assessment Section (only if show_skills is enabled AND skills data exists)
            if ($show_skills && !empty($skills_ratings_grouped)):
            ?>
                <h4 style="margin-top: 20px; border-bottom: 2px solid #333; padding-bottom: 5px;">SKILLS ASSESSMENT</h4>

                <?php
                // Get rating scale and display it before skills
                $ratings_scale = $this->skills_model->getRatings($getExam['branch_id'], 'active');
                if (!empty($ratings_scale)): ?>
                    <div style="margin-top: 10px; margin-bottom: 15px; padding: 8px; background-color: #f9f9f9; border: 1px solid #ddd; font-size: 9px;">
                        <strong>Rating Scale:</strong>
                        <?php foreach ($ratings_scale as $rating): ?>
                            <span style="margin-left: 10px;">
                                <span style="display: inline-block; padding: 2px 6px; background-color: #0088cc; color: white; border-radius: 3px; font-weight: bold;">
                                    <?= $rating['label'] ?>
                                </span> = <?= $rating['description'] ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php
                // Convert skills to array for easier iteration
                $skills_array = array_values($skills_ratings_grouped);
                $total_categories = count($skills_array);

                // Display in 2x2 grid
                for ($i = 0; $i < $total_categories; $i += 2):
                ?>
                    <table style="width: 100%; margin-bottom: 15px; border: none;" cellpadding="0" cellspacing="0">
                        <tr style="vertical-align: top;">
                            <?php
                            // First category in the row
                            if (isset($skills_array[$i])):
                                $category_data = $skills_array[$i];
                                $category_type = array_keys($skills_ratings_grouped)[$i];
                            ?>
                                <td style="width: 48%; padding-right: 2%;">
                                    <div class="skills-category-<?= $category_type ?>" style="margin-bottom: 10px;">
                                        <div style="background-color: #f5f5f5; padding: 5px 10px; font-weight: bold; font-size: 11px; border-left: 3px solid <?= $category_type == 'affective' ? '#5bc0de' : ($category_type == 'psychomotor' ? '#5cb85c' : '#f0ad4e') ?>;">
                                            <?= strtoupper($category_data['category_name']) ?>
                                        </div>
                                        <table class="table table-condensed table-bordered" style="font-size: 10px; margin-bottom: 5px;">
                                            <thead>
                                                <tr>
                                                    <th width="5%">#</th>
                                                    <th width="55%">Skill Item</th>
                                                    <th width="20%" style="text-align: center;">Rating</th>
                                                    <th width="20%" style="text-align: center;">Remark</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $count = 1;
                                                foreach ($category_data['items'] as $item): ?>
                                                    <tr>
                                                        <td><?= $count++ ?></td>
                                                        <td><?= $item['item_name'] ?></td>
                                                        <td style="text-align: center;">
                                                            <span style="display: inline-block; padding: 2px 6px; background-color: #0088cc; color: white; border-radius: 3px; font-weight: bold; font-size: 9px;">
                                                                <?= $item['rating_label'] ?>
                                                            </span>
                                                        </td>
                                                        <td style="text-align: center; font-size: 9px;"><?= $item['rating_description'] ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            <?php endif; ?>

                            <?php
                            // Second category in the row (if exists)
                            if (isset($skills_array[$i + 1])):
                                $category_data = $skills_array[$i + 1];
                                $category_type = array_keys($skills_ratings_grouped)[$i + 1];
                            ?>
                                <td style="width: 48%; padding-left: 2%;">
                                    <div class="skills-category-<?= $category_type ?>" style="margin-bottom: 10px;">
                                        <div style="background-color: #f5f5f5; padding: 5px 10px; font-weight: bold; font-size: 11px; border-left: 3px solid <?= $category_type == 'affective' ? '#5bc0de' : ($category_type == 'psychomotor' ? '#5cb85c' : '#f0ad4e') ?>;">
                                            <?= strtoupper($category_data['category_name']) ?>
                                        </div>
                                        <table class="table table-condensed table-bordered" style="font-size: 10px; margin-bottom: 5px;">
                                            <thead>
                                                <tr>
                                                    <th width="5%">#</th>
                                                    <th width="55%">Skill Item</th>
                                                    <th width="20%" style="text-align: center;">Rating</th>
                                                    <th width="20%" style="text-align: center;">Remark</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $count = 1;
                                                foreach ($category_data['items'] as $item): ?>
                                                    <tr>
                                                        <td><?= $count++ ?></td>
                                                        <td><?= $item['item_name'] ?></td>
                                                        <td style="text-align: center;">
                                                            <span style="display: inline-block; padding: 2px 6px; background-color: #0088cc; color: white; border-radius: 3px; font-weight: bold; font-size: 9px;">
                                                                <?= $item['rating_label'] ?>
                                                            </span>
                                                        </td>
                                                        <td style="text-align: center; font-size: 9px;"><?= $item['rating_description'] ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            <?php else: ?>
                                <td style="width: 48%; padding-left: 2%;"></td>
                            <?php endif; ?>
                        </tr>
                    </table>
                <?php endfor; ?>
            <?php endif; ?>

            <?php
            // Attendance Section
            if ($marksheet_template['attendance_percentage'] == 1):
                $year = explode('-', $schoolYear);
                $getTotalWorking = $this->db->where(array('enroll_id' => $student['enrollID'], 'status !=' => 'H', 'year(date)' => $year[0]))->get('student_attendance')->num_rows();
                $getTotalAttendance = $this->db->where(array('enroll_id' => $student['enrollID'], 'status' => 'P', 'year(date)' => $year[0]))->get('student_attendance')->num_rows();
                $attenPercentage = empty($getTotalWorking) ? '0.00' : ($getTotalAttendance * 100) / $getTotalWorking;
            ?>
                <table class="table table-condensed table-bordered" style="width:50%;margin-top:20px;">
                    <tbody>
                        <tr>
                            <th colspan="2" class="text-center">Attendance</th>
                        </tr>
                        <tr>
                            <th style="width: 65%;">No. of working days</th>
                            <td><?= $getTotalWorking ?></td>
                        </tr>
                        <tr>
                            <th style="width: 65%;">No. of days attended</th>
                            <td><?= $getTotalAttendance ?></td>
                        </tr>
                        <tr>
                            <th style="width: 65%;">Attendance Percentage</th>
                            <td><?= number_format($attenPercentage, 2, '.', '') ?>%</td>
                        </tr>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php
            // Grading Scale
            if ($marksheet_template['grading_scale'] == 1):
            ?>
                <table class="table table-condensed table-bordered" style="width:50%;margin-top:20px;">
                    <thead>
                        <tr>
                            <th colspan="6" class="text-center">Grading Scale</th>
                        </tr>
                        <tr>
                            <th>Grade</th>
                            <th>Remarks</th>
                            <th>Marks From</th>
                            <th>Marks Upto</th>
                            <th>Grade Point</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $grade = $this->db->where(array('branch_id' => $getExam['branch_id']))->get('grade')->result_array();
                        foreach ($grade as $row) {
                        ?>
                            <tr>
                                <td valign="middle"><?= $row['name'] ?></td>
                                <td valign="middle"><?= $row['remark'] ?></td>
                                <td valign="middle"><?= $row['lower_mark'] ?>%</td>
                                <td valign="middle"><?= $row['upper_mark'] ?>%</td>
                                <td valign="middle"><?= $row['grade_point'] ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php
            // Position and Result
            if ($marksheet_template['position'] == 1):
            ?>
                <table class="table table-condensed table-bordered" style="width: 50%;margin-top: 20px;">
                    <tbody>
                        <tr>
                            <th style="width: 65%;">Class Position</th>
                            <td><?php echo empty($rankDetail->class_rank) ? '-' : $rankDetail->class_rank; ?></td>
                        </tr>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php if ($marksheet_template['result'] == 1): ?>
                <table class="table table-condensed table-bordered" style="width: 50%;">
                    <tbody>
                        <tr>
                            <th style="width: 65%;">Result</th>
                            <td style="font-weight: bold;color:<?php echo $result_status == 1 ? 'green' : 'red'; ?>"><?php echo $result_status == 1 ? 'PASS' : 'FAIL'; ?></td>
                        </tr>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php echo $footer_content ?>
        </div>
    </div>
<?php } ?>