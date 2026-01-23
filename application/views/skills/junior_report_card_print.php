<?php
$branchID = $branchID;
$exam_id = $exam_id;
$student_array = $student_array;
$class_id = $class_id;
$section_id = $section_id;
$sessionID = $sessionID;
$print_date = $print_date;
$template_id = isset($template_id) ? $template_id : null;

// Load models
$this->load->model('skills_model');
$this->load->model('marksheet_template_model');
$this->load->model('exam_model');

// Get marksheet template if provided
$marksheet_template = null;
if ($template_id) {
    $marksheet_template = $this->marksheet_template_model->getTemplate($template_id, $branchID);
}
?>
<style type="text/css">
    .mark-container {
        padding: <?= $marksheet_template ? ($marksheet_template['top_space'] . 'px ' . $marksheet_template['right_space'] . 'px ' . $marksheet_template['bottom_space'] . 'px ' . $marksheet_template['left_space'] . 'px') : '20px' ?>;
    }

    .background {
        width: 100%;
        height: 100%;
    <?php if ($marksheet_template && !empty($marksheet_template['background'])) { ?>
        background-image: url("<?= base_url('uploads/marksheet/' . $marksheet_template['background']) ?>") !important;
        background-repeat: no-repeat !important;
        background-size: 100% 100% !important;
    <?php } else { ?>
        background: #fff;
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

    .skills-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }

    .skills-table th,
    .skills-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
        font-size: 12px;
    }

    .skills-table th {
        background-color: #f9f9f9;
        font-weight: 600;
    }

    .rating-badge {
        display: inline-block;
        padding: 4px 10px;
        background-color: #0088cc;
        color: white;
        border-radius: 3px;
        font-weight: bold;
        font-size: 11px;
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

    .remarks-section {
        margin-top: 25px;
        page-break-inside: avoid;
    }

    .remark-box {
        border: 1px solid #ddd;
        padding: 15px;
        margin-bottom: 15px;
        background-color: #f9f9f9;
    }

    .remark-label {
        font-weight: bold;
        color: #333;
        margin-bottom: 8px;
        font-size: 13px;
    }

    .remark-content {
        min-height: 60px;
        padding: 10px;
        background-color: white;
        border: 1px solid #e0e0e0;
        font-size: 12px;
        line-height: 1.6;
    }

    .rating-scale-box {
        margin-top: 15px;
        padding: 10px;
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        font-size: 11px;
    }

    h4 {
        margin-top: 15px;
        margin-bottom: 10px;
        border-bottom: 2px solid #333;
        padding-bottom: 5px;
        font-size: 16px;
    }

    @media print {
        .pagebreak {
            page-break-before: always;
        }
    }
</style>
<?php
if (!empty($student_array)) {
    foreach ($student_array as $sc => $studentID) {
        // Get student information
        $this->db->select('s.*, e.roll, e.id as enrollID, e.branch_id, e.session_id, e.class_id, e.section_id,
                          c.name as class, se.name as section,
                          CONCAT_WS(" ", s.first_name, s.last_name) as name,
                          IFNULL(p.father_name, "N/A") as father_name,
                          IFNULL(p.mother_name, "N/A") as mother_name,
                          br.name as institute_name, br.email as institute_email,
                          br.address as institute_address, br.mobileno as institute_mobile_no');
        $this->db->from('student s');
        $this->db->join('enroll e', 'e.student_id = s.id AND e.session_id = ' . $sessionID . ' AND e.class_id = ' . $class_id . ' AND e.section_id = ' . $section_id, 'left');
        $this->db->join('class c', 'e.class_id = c.id', 'left');
        $this->db->join('section se', 'e.section_id = se.id', 'left');
        $this->db->join('parent p', 'p.id = s.parent_id', 'left');
        $this->db->join('branch br', 'br.id = e.branch_id', 'left');
        $this->db->where('s.id', $studentID);
        $student = $this->db->get()->row_array();

        if (empty($student)) {
            continue;
        }

        $schoolYear = get_type_name_by_id('schoolyear', $sessionID, 'school_year');

        // Get skills ratings grouped by category
        $skills_ratings_grouped = $this->skills_model->getStudentRatingsByCategory($studentID, $exam_id, $sessionID);

        // Get teacher and head teacher remarks
        $teacher_remarks = '';
        $head_teacher_remarks = '';
        if (!empty($skills_ratings_grouped)) {
            // Get remarks from first rating entry (they should be the same across all entries for a student)
            $first_category = reset($skills_ratings_grouped);
            if (!empty($first_category['items'])) {
                $first_item = reset($first_category['items']);
                $teacher_remarks = isset($first_item['teacher_remarks']) ? $first_item['teacher_remarks'] : '';
                $head_teacher_remarks = isset($first_item['head_teacher_remarks']) ? $first_item['head_teacher_remarks'] : '';
            }
        }

        // Prepare extended data for template
        $extendsData = [];
        $extendsData['print_date'] = $print_date;
        $extendsData['schoolYear'] = $schoolYear;
        $extendsData['jr_teacher_comment'] = $teacher_remarks;
        $extendsData['jr_head_teacher_comment'] = $head_teacher_remarks;

        // Generate header and footer content
        $header_content = '';
        $footer_content = '';
        if ($marksheet_template) {
            $header_content = $this->marksheet_template_model->tagsReplace($student, $marksheet_template, $extendsData, 'header_content');
            $footer_content = $this->marksheet_template_model->tagsReplace($student, $marksheet_template, $extendsData, 'footer_content');
        }
        ?>
        <div style="position: relative; width: 100%; height: 100%;" class="<?= $sc > 0 ? 'pagebreak' : '' ?>">
            <div class="mark-container background">
                <?php echo $header_content; ?>

                <?php
                // Check if subjects table should be shown (from template settings)
                $show_subjects_table = $marksheet_template && isset($marksheet_template['subjects_table']) && $marksheet_template['subjects_table'] == 1;

                if ($show_subjects_table):
                    // Load subject model if not already loaded
                    $this->load->model('subject_model');

                    // Get the exam details
                    $exam = $this->db->get_where('exam', array('id' => $exam_id))->row_array();
                ?>

                <h4>ACADEMIC PERFORMANCE</h4>
                <table class="table table-condensed table-bordered" style="margin-bottom: 20px;">
                    <thead>
                        <tr>
                            <th width="40%">Subject</th>
                            <th width="15%" style="text-align: center;">Score</th>
                            <th width="15%" style="text-align: center;">Grade</th>
                            <th width="30%" style="text-align: center;">Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $getSubjectsList = $this->subject_model->getSubjectByClassSection($student['class_id'], $student['section_id']);
                    $getSubjectsList = $getSubjectsList->result_array();
                    foreach ($getSubjectsList as $subject_row):
                        // Get marks for this subject and exam
                        $this->db->select('mark.*');
                        $this->db->from('mark');
                        $this->db->where('mark.student_id', $student['id']);
                        $this->db->where('mark.subject_id', $subject_row['subject_id']);
                        $this->db->where('mark.exam_id', $exam_id);
                        $this->db->where('mark.session_id', $sessionID);
                        $mark_row = $this->db->get()->row_array();

                        if ($mark_row):
                            $obtain_marks = floatval($mark_row['mark']);

                            // Get grade
                            $grade_details = $this->exam_model->get_grade($obtain_marks, $exam['term_id'], $student['class_id']);
                    ?>
                        <tr>
                            <td><?= $subject_row['subject_name'] ?></td>
                            <td style="text-align: center;"><?= number_format($obtain_marks, 1) ?></td>
                            <td style="text-align: center;"><?= $grade_details['grade'] ?></td>
                            <td style="text-align: center;"><?= $grade_details['remark'] ?></td>
                        </tr>
                    <?php
                        endif;
                    endforeach;
                    ?>
                    </tbody>
                </table>
                <?php endif; ?>

                <h4>SKILLS ASSESSMENT REPORT</h4>

                <?php if (!empty($skills_ratings_grouped)): ?>
                    <div class="skills-section">
                        <?php foreach ($skills_ratings_grouped as $category_type => $category_data): ?>
                            <div class="skills-category-<?= $category_type ?>">
                                <div class="skills-header">
                                    <?= strtoupper($category_data['category_name']) ?>
                                </div>
                                <table class="skills-table">
                                    <thead>
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="50%">Skill Item</th>
                                        <th width="20%" style="text-align: center;">Rating</th>
                                        <th width="25%" style="text-align: center;">Description</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $count = 1;
                                    foreach ($category_data['items'] as $item): ?>
                                        <tr>
                                            <td><?= $count++ ?></td>
                                            <td><?= $item['item_name'] ?></td>
                                            <td style="text-align: center;">
                                                <span class="rating-badge"><?= $item['rating_label'] ?></span>
                                            </td>
                                            <td style="text-align: center;"><?= $item['rating_description'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endforeach; ?>

                        <?php
                        // Get rating scale
                        $ratings_scale = $this->skills_model->getRatings($branchID, 'active');
                        if (!empty($ratings_scale)): ?>
                            <div class="rating-scale-box">
                                <strong>Rating Scale:</strong><br>
                                <?php foreach ($ratings_scale as $rating): ?>
                                    <span style="margin-right: 15px;">
                                        <span class="rating-badge"><?= $rating['label'] ?></span> = <?= $rating['description'] ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; padding: 30px; color: #999;">No skills assessment data available for this student.</p>
                <?php endif; ?>

                <!-- Teacher and Head Teacher Remarks Section -->
                <div class="remarks-section">
                    <h4>TEACHER'S REMARKS</h4>
                    <div class="remark-box">
                        <div class="remark-label">Class Teacher's Comment:</div>
                        <div class="remark-content">
                            <?= !empty($teacher_remarks) ? nl2br(htmlspecialchars($teacher_remarks)) : '&nbsp;' ?>
                        </div>
                    </div>

                    <div class="remark-box">
                        <div class="remark-label">Head Teacher's Comment:</div>
                        <div class="remark-content">
                            <?= !empty($head_teacher_remarks) ? nl2br(htmlspecialchars($head_teacher_remarks)) : '&nbsp;' ?>
                        </div>
                    </div>
                </div>

                <?php echo $footer_content; ?>
            </div>
        </div>
    <?php }
} ?>
