<section class="panel">
    <div class="panel-heading">
        <h4 class="panel-title"><i class="fas fa-info-circle"></i> <?= translate('instructions') ?></h4>
    </div>
    <div class="panel-body">
        <div id="regular_instructions">
            <h5><strong><?= translate('import_instructions') ?></strong></h5>
            <ol>
                <li><?= translate('select_class_section_subject_and_exam') ?></li>
                <li><?= translate('click_download_template_to_get_excel_file') ?></li>
                <li><?= translate('fill_in_marks_in_the_excel_file') ?></li>
                <li><?= translate('for_absent_students_enter_yes_in_absent_column') ?></li>
                <li><?= translate('upload_the_filled_excel_file') ?></li>
                <li style="color:red;"><strong><?= translate('do_not_modify_student_id_register_no_or_roll') ?></strong></li>
            </ol>
        </div>
    </div>
</section>

<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <?php echo form_open_multipart($this->uri->uri_string(), array('class' => 'validate')); ?>
            <div class="panel-heading">
                <h4 class="panel-title"><i class="fas fa-filter"></i> <?= translate('filter_settings') ?></h4>
            </div>
            <div class="panel-body">
                <div class="row mb-sm">
                    <div class="col-md-3 mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('exam') ?> <span class="required">*</span></label>
                            <?php
                            $arrayExam = array("" => translate('select'));
                            if (!empty($branch_id)) {
                                $exams = $this->db->get_where('exam', array('branch_id' => $branch_id, 'session_id' => get_session_id()))->result();
                                foreach ($exams as $row) {
                                    $arrayExam[$row->id] = $this->application_model->exam_name_by_id($row->id);
                                }
                            }
                            echo form_dropdown("exam_id", $arrayExam, set_value('exam_id'), "class='form-control' id='exam_id' required data-plugin-selectTwo
                                data-width='100%' data-minimum-results-for-search='Infinity' ");
                            ?>
                        </div>
                    </div>
                    <div class="col-md-3 mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('class') ?> <span class="required">*</span></label>
                            <?php
                            $arrayClass = $this->app_lib->getClass($branch_id);
                            echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)'
                                required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
                            ?>
                        </div>
                    </div>
                    <div class="col-md-3 mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('section') ?> <span class="required">*</span></label>
                            <?php
                            $arraySection = $this->app_lib->getSections(set_value('class_id'), false);
                            echo form_dropdown("section_id", $arraySection, set_value('section_id'), "class='form-control' id='section_id' required
                                data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
                            ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label"><?= translate('subject') ?> <span class="required">*</span></label>
                            <?php
                            if (!empty(set_value('class_id'))) {
                                $arraySubject = array("" => translate('select'));
                                $query = $this->subject_model->getSubjectByClassSection(set_value('class_id'), set_value('section_id'));
                                $subjects = $query->result_array();
                                foreach ($subjects as $row) {
                                    $subjectID = $row['subject_id'];
                                    $arraySubject[$subjectID] = $row['subjectname'];
                                }
                            } else {
                                $arraySubject = array("" => translate('select_class_first'));
                            }
                            echo form_dropdown("subject_id", $arraySubject, set_value('subject_id'), "class='form-control' id='subject_id' required
                                data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="panel-footer">
                <div class="row" id="regular_download_row">
                    <div class="col-md-12 text-right">
                        <button type="button" id="download_template" class="btn btn-default" disabled>
                            <i class="fas fa-download"></i> <?= translate('download_template') ?>
                        </button>
                    </div>
                </div>
            </footer>
            <?php echo form_close(); ?>
        </section>

        <?php if (!empty($filename_warning)): ?>
            <section class="panel panel-warning">
                <header class="panel-heading">
                    <h4 class="panel-title"><i class="fas fa-exclamation-triangle"></i> File Name Mismatch</h4>
                </header>
                <div class="panel-body">
                    <?= $filename_warning ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($import_errors)): ?>
            <section class="panel panel-danger">
                <header class="panel-heading">
                    <h4 class="panel-title"><i class="fas fa-exclamation-triangle"></i> Import Errors (<?= count($import_errors) ?>)</h4>
                </header>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student Name/Email</th>
                                    <th>Assessment Type</th>
                                    <th>Error Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($import_errors as $index => $error): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><strong><?= isset($error['student_name']) ? $error['student_name'] : $error['student_email'] ?></strong></td>
                                        <td><?= isset($error['distribution']) ? $error['distribution'] : 'N/A' ?></td>
                                        <td class="text-danger">
                                            <?php if (isset($error['student_name'])): ?>
                                                Marks for <strong><?= $error['student_name'] ?></strong> was not imported.
                                                <strong><?= $error['distribution'] ?></strong> mark of <strong><?= $error['mark_entered'] ?></strong>
                                                exceeds maximum score of <strong><?= $error['max_mark'] ?></strong>
                                            <?php else: ?>
                                                <?= $error['error'] ?>
                                                <?php if (isset($error['mark_obtained']) && isset($error['max_mark'])): ?>
                                                    <br>Mark: <strong><?= $error['mark_obtained'] ?></strong> exceeds maximum: <strong><?= $error['max_mark'] ?></strong>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php echo form_open_multipart($this->uri->uri_string() . '/upload_file', array('class' => 'validate', 'id' => 'upload_form')); ?>
        <section class="panel" id="upload_panel" style="display:none;">
            <header class="panel-heading">
                <h4 class="panel-title"><i class="fas fa-file-excel"></i> <?= translate('upload_marks_file') ?></h4>
            </header>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label"><?= translate('choose_excel_file') ?> <span class="required">*</span></label>
                            <div class="input-group">
                                <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls" required class="form-control">
                                <input type="hidden" name="class_id" id="upload_class_id">
                                <input type="hidden" name="section_id" id="upload_section_id">
                                <input type="hidden" name="subject_id" id="upload_subject_id">
                                <input type="hidden" name="exam_id" id="upload_exam_id">
                            </div>
                            <span class="help-block"><i class="fas fa-info-circle"></i> <?= translate('accepted_formats') ?>: .xlsx, .xls (<?= translate('maximum_size') ?>: 2MB)</span>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="panel-footer">
                <div class="row">
                    <div class="col-md-12 text-right">
                        <button type="submit" name="upload" value="1" class="btn btn-primary" id="upload_submit_btn">
                            <i class="fas fa-upload"></i> <?= translate('import_marks') ?>
                        </button>
                    </div>
                </div>
            </footer>
        </section>
        <?php echo form_close(); ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {

        $('#section_id').on('change', function() {
            var classID = $('#class_id').val();
            var sectionID = $(this).val();
            $.ajax({
                url: base_url + 'subject/getByClassSection',
                type: 'POST',
                data: {
                    classID: classID,
                    sectionID: sectionID
                },
                success: function(data) {
                    $('#subject_id').html(data);
                }
            });
            checkUploadButton();
        });

        $('#class_id, #exam_id, #subject_id').on('change', function() {
            checkUploadButton();
            updateHiddenFields();
        });

        function checkUploadButton() {
            var classID = $('#class_id').val();
            var sectionID = $('#section_id').val();
            var subjectID = $('#subject_id').val();
            var examID = $('#exam_id').val();

            if (classID && sectionID && subjectID && examID) {
                $('#download_template').prop('disabled', false);
                $('#upload_panel').slideDown();
            } else {
                $('#download_template').prop('disabled', true);
                $('#upload_panel').slideUp();
            }
        }

        function updateHiddenFields() {
            $('#upload_class_id').val($('#class_id').val());
            $('#upload_section_id').val($('#section_id').val());
            $('#upload_subject_id').val($('#subject_id').val());
            $('#upload_exam_id').val($('#exam_id').val());
        }

        $('#download_template').on('click', function() {
            var classID = $('#class_id').val();
            var sectionID = $('#section_id').val();
            var subjectID = $('#subject_id').val();
            var examID = $('#exam_id').val();

            if (!classID || !sectionID || !subjectID || !examID) {
                alert('<?= translate("please_select_all_fields") ?>');
                return;
            }

            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> <?= translate("generating") ?>...');

            $.ajax({
                url: base_url + 'exam/download_marks_template',
                type: 'POST',
                data: {
                    class_id: classID,
                    section_id: sectionID,
                    subject_id: subjectID,
                    exam_id: examID
                },
                dataType: 'json',
                success: function(response) {
                    btn.prop('disabled', false).html('<i class="fas fa-download"></i> <?= translate("download_template") ?>');

                    if (response.status === 'success') {
                        window.location.href = response.download_url;
                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-download"></i> <?= translate("download_template") ?>');
                    alert('<?= translate("error_generating_template") ?>');
                }
            });
        });

        updateHiddenFields();
    });
</script>
