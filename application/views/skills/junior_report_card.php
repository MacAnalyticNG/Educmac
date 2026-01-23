<?php $widget = (is_superadmin_loggedin() ? 2 : 3); ?>
<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <?php echo form_open('skills/junior_report_card', array('class' => 'validate')); ?>
            <header class="panel-heading">
                <h4 class="panel-title"><?= translate('junior_report_card') ?> - <?= translate('select_ground') ?></h4>
            </header>
            <div class="panel-body">
                <div class="row mb-sm">
                    <?php if (is_superadmin_loggedin()): ?>
                        <div class="col-md-3 mb-sm">
                            <div class="form-group">
                                <label class="control-label"><?= translate('branch') ?> <span class="required">*</span></label>
                                <?php
                                $arrayBranch = $this->app_lib->getSelectList('branch');
                                echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='branch_id'
								data-plugin-selectTwo data-width='100%'");
                                ?>
                                <span class="error"><?php echo form_error('branch_id'); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="col-md-<?= $widget ?> mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('academic_year') ?> <span class="required">*</span></label>
                            <?php
                            $arrayYear = array("" => translate('select'));
                            $years = $this->db->get('schoolyear')->result();
                            foreach ($years as $year) {
                                $arrayYear[$year->id] = $year->school_year;
                            }
                            echo form_dropdown("session_id", $arrayYear, set_value('session_id', get_session_id()), "class='form-control'
							data-plugin-selectTwo data-width='100%'");
                            ?>
                            <span class="error"><?php echo form_error('session_id'); ?></span>
                        </div>
                    </div>
                    <div class="col-md-<?= $widget ?> mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('exam') ?> <span class="required">*</span></label>
                            <?php
                            $arrayExam = array("" => translate('select'));
                            if (!empty($branch_id)) {
                                $this->db->order_by('id', 'asc');
                                $exams = $this->db->get_where('exam', array('branch_id' => $branch_id, 'session_id' => get_session_id()))->result();
                                foreach ($exams as $row) {
                                    $arrayExam[$row->id] = $this->application_model->exam_name_by_id($row->id);
                                }
                            }
                            echo form_dropdown("exam_id", $arrayExam, set_value('exam_id'), "class='form-control' id='exam_id'
							data-plugin-selectTwo data-width='100%'");
                            ?>
                            <span class="error"><?php echo form_error('exam_id'); ?></span>
                        </div>
                    </div>
                    <div class="col-md-3 mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('class') ?> <span class="required">*</span></label>
                            <?php
                            $arrayClass = $this->app_lib->getClass($branch_id);
                            echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)'
							data-plugin-selectTwo data-width='100%' ");
                            ?>
                            <span class="error"><?php echo form_error('class_id'); ?></span>
                        </div>
                    </div>
                    <div class="col-md-<?= $widget ?> mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('section') ?> <span class="required">*</span></label>
                            <?php
                            $arraySection = $this->app_lib->getSections(set_value('class_id'), false);
                            echo form_dropdown("section_id", $arraySection, set_value('section_id'), "class='form-control' id='section_id'
							data-plugin-selectTwo data-width='100%' ");
                            ?>
                            <span class="error"><?php echo form_error('section_id'); ?></span>
                        </div>
                    </div>
                    <div class="col-md-3 mt-xs">
                        <div class="form-group">
                            <label class="control-label"><?= translate('marksheet') . " " . translate('template'); ?></label>
                            <?php
                            $arrayTemplate = $this->app_lib->getSelectByBranch('marksheet_template', $branch_id);
                            echo form_dropdown("template_id", $arrayTemplate, set_value('template_id'), "class='form-control' id='templateID'
							data-plugin-selectTwo data-width='100%' ");
                            ?>
                            <span class="error"><?php echo form_error('template_id'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-md-offset-10 col-md-2">
                        <button type="submit" name="submit" value="search" class="btn btn-default btn-block"><i class="fas fa-filter"></i> <?= translate('filter') ?></button>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </section>

        <?php if (isset($student)): ?>
            <section class="panel appear-animation" data-appear-animation="<?= $global_config['animations'] ?>" data-appear-animation-delay="100">
                <div class="panel-body">
                    <h5 class="chart-title mb-xs"><i class="fas fa-users"></i> <?= translate('student') . " " . translate('list') ?></h5>
                    <?php
                    if (count($student)) {
                        ?>
                        <?php echo form_open('skills/reportCardPdf', array("target" => "_blank")); ?>
                        <input type="hidden" name="exam_id" value="<?= $examID ?>">
                        <input type="hidden" name="class_id" value="<?= $this->input->post('class_id') ?>">
                        <input type="hidden" name="section_id" value="<?= $this->input->post('section_id') ?>">
                        <input type="hidden" name="session_id" value="<?= $this->input->post('session_id') ?>">
                        <input type="hidden" name="template_id" value="<?= $this->input->post('template_id') ?>">
                        <input type="hidden" id="getPrint_date" name="print_date" value="">
                        <table class="table table-condensed table-hover table-bordered mt-md">
                            <thead>
                            <tr>
                                <th width="50">
                                    <div class="checkbox-replace">
                                        <label class="i-checks"><input type="checkbox" id="userCheckAll"><i></i></label>
                                    </div>
                                </th>
                                <th><?= translate('name') ?></th>
                                <th><?= translate('register_no') ?></th>
                                <th><?= translate('roll') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            foreach ($student as $row):
                                ?>
                                <tr>
                                    <td class="center">
                                        <div class="checkbox-replace">
                                            <label class="i-checks"><input type="checkbox" name="student_id[]" value="<?= $row['id'] ?>"><i></i></label>
                                        </div>
                                    </td>
                                    <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                                    <td><?php echo $row['register_no']; ?></td>
                                    <td><?php echo $row['roll']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label"><?= translate('date') ?></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
                                        <input type="text" class="form-control" name="print_date_view" value="<?= date('Y-m-d') ?>" data-plugin-datepicker
                                               data-plugin-options='{ "todayHighlight" : true }' autocomplete="off"/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-offset-6 col-md-3">
                                <div class="form-group mt-md">
                                    <button type="submit" name="submit" value="pdf" class="btn btn-default btn-block"><i class="fas fa-file-pdf"></i> <?= translate('generate_pdf') ?></button>
                                </div>
                            </div>
                        </div>
                        <?php echo form_close(); ?>
                        <?php
                    } else {
                        echo '<h5 class="text-danger text-weight-semibold">' . translate('no_information_available') . '</h5>';
                    }
                    ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
    $('#userCheckAll').on('ifChecked', function(event) {
        $('input[name="student_id[]"]').iCheck('check');
    });
    $('#userCheckAll').on('ifUnchecked', function(event) {
        $('input[name="student_id[]"]').iCheck('uncheck');
    });

    $('input[name="print_date_view"]').on('change', function() {
        $('#getPrint_date').val($(this).val());
    });

    $(document).ready(function() {
        $('#getPrint_date').val($('input[name="print_date_view"]').val());
    });
</script>
