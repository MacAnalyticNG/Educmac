<style>
    .cdev-notification {
        padding: 8px !important;
        margin-top: 9px !important;
    }
</style>

<section class="cdev-dashboard-card">
    <div class="cdev-card-header">
        <h4 class="cdev-card-title">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" style="display: inline-block; vertical-align: middle;" aria-hidden="true">
                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                <g id="SVGRepo_iconCarrier">
                    <path d="M12 6.25C12.4142 6.25 12.75 6.58579 12.75 7V13C12.75 13.4142 12.4142 13.75 12 13.75C11.5858 13.75 11.25 13.4142 11.25 13V7C11.25 6.58579 11.5858 6.25 12 6.25Z" fill="currentColor"></path>
                    <path d="M13 16C13 16.5523 12.5523 17 12 17C11.4477 17 11 16.5523 11 16C11 15.4477 11.4477 15 12 15C12.5523 15 13 15.4477 13 16Z" fill="currentColor"></path>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 1.25C11.2954 1.25 10.6519 1.44359 9.94858 1.77037C9.26808 2.08656 8.48039 2.55304 7.49457 3.13685L6.74148 3.58283C5.75533 4.16682 4.96771 4.63324 4.36076 5.07944C3.73315 5.54083 3.25177 6.01311 2.90334 6.63212C2.55548 7.25014 2.39841 7.91095 2.32306 8.69506C2.24999 9.45539 2.24999 10.3865 2.25 11.556V12.444C2.24999 13.6135 2.24999 14.5446 2.32306 15.3049C2.39841 16.0891 2.55548 16.7499 2.90334 17.3679C3.25177 17.9869 3.73315 18.4592 4.36076 18.9206C4.96771 19.3668 5.75533 19.8332 6.74148 20.4172L7.4946 20.8632C8.48038 21.447 9.2681 21.9135 9.94858 22.2296C10.6519 22.5564 11.2954 22.75 12 22.75C12.7046 22.75 13.3481 22.5564 14.0514 22.2296C14.7319 21.9134 15.5196 21.447 16.5054 20.8632L17.2585 20.4172C18.2446 19.8332 19.0323 19.3668 19.6392 18.9206C20.2669 18.4592 20.7482 17.9869 21.0967 17.3679C21.4445 16.7499 21.6016 16.0891 21.6769 15.3049C21.75 14.5446 21.75 13.6135 21.75 12.4441V11.556C21.75 10.3866 21.75 9.45538 21.6769 8.69506C21.6016 7.91095 21.4445 7.25014 21.0967 6.63212C20.7482 6.01311 20.2669 5.54083 19.6392 5.07944C19.0323 4.63324 18.2447 4.16683 17.2585 3.58285L16.5054 3.13685C15.5196 2.55303 14.7319 2.08656 14.0514 1.77037C13.3481 1.44359 12.7046 1.25 12 1.25ZM8.22524 4.44744C9.25238 3.83917 9.97606 3.41161 10.5807 3.13069C11.1702 2.85676 11.5907 2.75 12 2.75C12.4093 2.75 12.8298 2.85676 13.4193 3.13069C14.0239 3.41161 14.7476 3.83917 15.7748 4.44744L16.4609 4.85379C17.4879 5.46197 18.2109 5.89115 18.7508 6.288C19.2767 6.67467 19.581 6.99746 19.7895 7.36788C19.9986 7.73929 20.1199 8.1739 20.1838 8.83855C20.2492 9.51884 20.25 10.378 20.25 11.5937V12.4063C20.25 13.622 20.2492 14.4812 20.1838 15.1614C20.1199 15.8261 19.9986 16.2607 19.7895 16.6321C19.581 17.0025 19.2767 17.3253 18.7508 17.712C18.2109 18.1089 17.4879 18.538 16.4609 19.1462L15.7748 19.5526C14.7476 20.1608 14.0239 20.5884 13.4193 20.8693C12.8298 21.1432 12.4093 21.25 12 21.25C11.5907 21.25 11.1702 21.1432 10.5807 20.8693C9.97606 20.5884 9.25238 20.1608 8.22524 19.5526L7.53909 19.1462C6.5121 18.538 5.78906 18.1089 5.24924 17.712C4.72326 17.3253 4.419 17.0025 4.2105 16.6321C4.00145 16.2607 3.88005 15.8261 3.81618 15.1614C3.7508 14.4812 3.75 13.622 3.75 12.4063V11.5937C3.75 10.378 3.7508 9.51884 3.81618 8.83855C3.88005 8.1739 4.00145 7.73929 4.2105 7.36788C4.419 6.99746 4.72326 6.67467 5.24924 6.288C5.78906 5.89115 6.5121 5.46197 7.53909 4.85379L8.22524 4.44744Z" fill="currentColor"></path>
                </g>
            </svg>
            <?= translate('instructions') ?>
        </h4>
    </div>
    <div class="cdev-card-body">
        <h5><strong><?= translate('attendance') ?> <?= translate('import') ?>/<?= translate('export') ?> Instructions</strong></h5>
        <ol>
            <li><?= translate('select') ?> <?= translate('class') ?> and <?= translate('section') ?></li>
            <li><?= translate('click') ?> "<?= translate('download_template') ?>" to get Excel file with all school days for <strong><?= isset($active_term) ? $active_term->term_name : 'the active term' ?></strong></li>
            <li>Fill in attendance status for each student and date:
                <ul>
                    <li><strong>P</strong> = <?= translate('present') ?></li>
                    <li><strong>A</strong> = <?= translate('absent') ?></li>
                    <li><strong>L</strong> = <?= translate('late') ?></li>
                    <li><strong>HD</strong> = <?= translate('half_day') ?></li>
                </ul>
            </li>
            <li>Upload the filled Excel file</li>
            <li style="color:red;"><strong>Do not modify Student ID, Register No, or Roll columns</strong></li>
        </ol>
        <div class="cdev-notification cdev-notification-notice">
            <div class="cdev-notification-icon">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="24px" height="24px">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2.75C8.27208 2.75 5.25 5.77208 5.25 9.5C5.25 11.4985 6.11758 13.2934 7.49907 14.5304L7.50342 14.5343C8.06008 15.0328 8.48295 15.4114 8.78527 15.6886C9.06989 15.9495 9.29537 16.1628 9.41353 16.3086L9.42636 16.3244C9.64763 16.5974 9.84045 16.8353 9.9676 17.1199C10.0948 17.4044 10.1434 17.7067 10.1992 18.0537L10.2024 18.0738C10.231 18.2517 10.2425 18.4701 10.247 18.75H13.753C13.7575 18.4701 13.769 18.2517 13.7976 18.0738L13.8008 18.0537C13.8566 17.7067 13.9052 17.4044 14.0324 17.1199C14.1596 16.8353 14.3524 16.5974 14.5736 16.3244L14.5865 16.3086C14.7046 16.1628 14.9301 15.9495 15.2147 15.6886C15.5171 15.4114 15.94 15.0327 16.4966 14.5343L16.5009 14.5304C17.8824 13.2934 18.75 11.4985 18.75 9.5C18.75 5.77208 15.7279 2.75 12 2.75ZM13.7436 20.25H10.2564C10.2597 20.3542 10.2646 20.4453 10.2721 20.5273C10.2925 20.7524 10.3269 20.8341 10.3505 20.875C10.4163 20.989 10.511 21.0837 10.625 21.1495C10.6659 21.1731 10.7476 21.2075 10.9727 21.2279C11.2082 21.2493 11.5189 21.25 12 21.25C12.4811 21.25 12.7918 21.2493 13.0273 21.2279C13.2524 21.2075 13.3341 21.1731 13.375 21.1495C13.489 21.0837 13.5837 20.989 13.6495 20.875C13.6731 20.8341 13.7075 20.7524 13.7279 20.5273C13.7354 20.4453 13.7403 20.3542 13.7436 20.25ZM3.75 9.5C3.75 4.94365 7.44365 1.25 12 1.25C16.5563 1.25 20.25 4.94365 20.25 9.5C20.25 11.9428 19.1874 14.1384 17.5016 15.6479C16.9397 16.151 16.5234 16.5238 16.2284 16.7942C16.0809 16.9295 15.9681 17.0351 15.8849 17.1162C15.8434 17.1566 15.8117 17.1886 15.788 17.2134C15.7763 17.2256 15.7675 17.2352 15.7611 17.2423C15.7546 17.2496 15.7518 17.253 15.7519 17.2529C15.4917 17.574 15.4354 17.6568 15.4019 17.7319C15.3683 17.8069 15.3442 17.9041 15.2786 18.3121C15.2527 18.4732 15.25 18.7491 15.25 19.5V19.5322C15.25 19.972 15.25 20.3514 15.2218 20.6627C15.192 20.9918 15.1259 21.3178 14.9486 21.625C14.7511 21.967 14.467 22.2511 14.125 22.4486C13.8178 22.6259 13.4918 22.692 13.1627 22.7218C12.8514 22.75 12.472 22.75 12.0322 22.75H11.9678C11.528 22.75 11.1486 22.75 10.8374 22.7218C10.5082 22.692 10.1822 22.6259 9.875 22.4486C9.53296 22.2511 9.24892 21.967 9.05144 21.625C8.87407 21.3178 8.80802 20.9918 8.77818 20.6627C8.74997 20.3514 8.74998 19.972 8.75 19.5322L8.75 19.5C8.75 18.7491 8.74735 18.4732 8.72144 18.3121C8.6558 17.9041 8.63166 17.8069 8.59812 17.7319C8.56459 17.6568 8.50828 17.574 8.24812 17.2529C8.24792 17.2527 8.24514 17.2493 8.23888 17.2423C8.23249 17.2352 8.22369 17.2256 8.21199 17.2134C8.18835 17.1886 8.15661 17.1566 8.11513 17.1162C8.03189 17.0351 7.91912 16.9295 7.77161 16.7942C7.4766 16.5238 7.06034 16.151 6.49845 15.6479C4.81263 14.1384 3.75 11.9428 3.75 9.5ZM9.89202 13.3508C10.2506 13.1434 10.7094 13.2659 10.9168 13.6245C11.134 14 11.5383 14.25 12 14.25C12.4617 14.25 12.866 14 13.0832 13.6245C13.2906 13.2659 13.7494 13.1434 14.108 13.3508C14.4665 13.5582 14.589 14.017 14.3816 14.3755C14.0284 14.9862 13.4454 15.4496 12.75 15.6464V17C12.75 17.4142 12.4142 17.75 12 17.75C11.5858 17.75 11.25 17.4142 11.25 17V15.6464C10.5546 15.4496 9.97163 14.9862 9.61836 14.3755C9.41095 14.017 9.53347 13.5582 9.89202 13.3508Z" fill="currentColor"></path>
                </svg>
            </div>
            <div class="cdev-notification-content">
                <p class="cdev-notification-title"><?= translate('note') ?>:</p>
                <p class="cdev-notification-message">
                    The template will only include school days (excluding weekends and holidays) for <strong><?= isset($active_term) ? $active_term->term_name : 'the active term' ?></strong>.
                </p>
            </div>
        </div>
    </div>
</section>

<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <?php echo form_open_multipart($this->uri->uri_string(), array('class' => 'validate')); ?>
            <div class="cdev-card-header">
                <h4 class="cdev-card-title">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="19px" height="19px" style="display: inline-block; vertical-align: middle;" aria-hidden="true">
                        <path d="M3.06164 5.58315C3.33079 4.94977 3.94954 4.53799 4.63683 4.53799H19.3632C20.0505 4.53799 20.6692 4.94977 20.9384 5.58315L12 11.0544L3.06164 5.58315Z" fill="currentColor"></path>
                        <path d="M2.25 7.44605V16.7C2.25 18.1081 3.39188 19.25 4.8 19.25H19.2C20.6081 19.25 21.75 18.1081 21.75 16.7V7.44605L12 13.3294L2.25 7.44605Z" fill="currentColor"></path>
                    </svg>
                    <?= translate('filter_settings') ?>
                </h4>
            </div>
            <div class="panel-body">
                <div class="row mb-sm">
                    <?php if (is_superadmin_loggedin()): ?>
                        <div class="col-md-3 mb-sm">
                            <div class="form-group">
                                <label class="control-label"><?= translate('branch') ?> <span class="required">*</span></label>
                                <?php
                                $arrayBranch = $this->app_lib->getSelectList('branch');
                                echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id', $branch_id), "class='form-control' onchange='getClassByBranch(this.value)' id='branch_id'
                                data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
                                ?>
                                <span class="error"><?= form_error('branch_id') ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="col-md-<?php echo is_superadmin_loggedin() ? '3' : '6'; ?> mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('class') ?> <span class="required">*</span></label>
                            <?php
                            $arrayClass = $this->app_lib->getClass($branch_id);
                            echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)'
                                data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
                            ?>
                            <span class="error"><?= form_error('class_id') ?></span>
                        </div>
                    </div>
                    <div class="col-md-<?php echo is_superadmin_loggedin() ? '3' : '6'; ?> mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('section') ?> <span class="required">*</span></label>
                            <?php
                            $arraySection = $this->app_lib->getSections(set_value('class_id'), false);
                            echo form_dropdown("section_id", $arraySection, set_value('section_id'), "class='form-control' id='section_id'
                                data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
                            ?>
                            <span class="error"><?= form_error('section_id') ?></span>
                        </div>
                    </div>
                </div>
                <?php if (isset($active_term)): ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <i class="fas fa-calendar-alt"></i> <strong><?= translate('active_term') ?>:</strong> <?= $active_term->term_name ?>
                                (<?= date('M d, Y', strtotime($active_term->start_date)) ?> - <?= date('M d, Y', strtotime($active_term->end_date)) ?>)
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <footer class="panel-footer">
                <div class="row">
                    <div class="col-md-12 text-right">
                        <button type="button" class="btn btn-default" id="download_template" disabled>
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="19px" height="19px" style="display: inline-block; vertical-align: middle;" aria-hidden="true">
                                <path d="M12.5535 16.5061C12.4114 16.6615 12.2106 16.75 12 16.75C11.7894 16.75 11.5886 16.6615 11.4465 16.5061L7.44648 12.1311C7.16698 11.8254 7.18822 11.351 7.49392 11.0715C7.79963 10.792 8.27402 10.8132 8.55352 11.1189L11.25 14.0682V3C11.25 2.58579 11.5858 2.25 12 2.25C12.4142 2.25 12.75 2.58579 12.75 3V14.0682L15.4465 11.1189C15.726 10.8132 16.2004 10.792 16.5061 11.0715C16.8118 11.351 16.833 11.8254 16.5535 12.1311L12.5535 16.5061Z" fill="currentColor"></path>
                                <path d="M3.75 15C3.75 14.5858 3.41422 14.25 3 14.25C2.58579 14.25 2.25 14.5858 2.25 15V15.0549C2.24998 16.4225 2.24996 17.5248 2.36652 18.3918C2.48754 19.2919 2.74643 20.0497 3.34835 20.6516C3.95027 21.2536 4.70814 21.5125 5.60825 21.6335C6.47522 21.75 7.57754 21.75 8.94513 21.75H15.0549C16.4225 21.75 17.5248 21.75 18.3918 21.6335C19.2919 21.5125 20.0497 21.2536 20.6517 20.6516C21.2536 20.0497 21.5125 19.2919 21.6335 18.3918C21.75 17.5248 21.75 16.4225 21.75 15.0549V15C21.75 14.5858 21.4142 14.25 21 14.25C20.5858 14.25 20.25 14.5858 20.25 15C20.25 16.4354 20.2484 17.4365 20.1469 18.1919C20.0482 18.9257 19.8678 19.3142 19.591 19.591C19.3142 19.8678 18.9257 20.0482 18.1919 20.1469C17.4365 20.2484 16.4354 20.25 15 20.25H9C7.56459 20.25 6.56347 20.2484 5.80812 20.1469C5.07435 20.0482 4.68577 19.8678 4.40901 19.591C4.13225 19.3142 3.9518 18.9257 3.85315 18.1919C3.75159 17.4365 3.75 16.4354 3.75 15Z" fill="currentColor"></path>
                            </svg>
                            <span><?= translate('download_template') ?></span>
                        </button>
                    </div>
                </div>
            </footer>
            <?php echo form_close(); ?>
        </section>

        <?php if ($this->session->flashdata('import_errors')): ?>
            <?php $import_errors = $this->session->flashdata('import_errors'); ?>
            <section class="panel panel-danger">
                <header class="panel-heading">
                    <h4 class="panel-title"><i class="fas fa-exclamation-triangle"></i> Import Errors (<?= count($import_errors) ?>)</h4>
                </header>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Row</th>
                                    <th>Register No</th>
                                    <th>Date</th>
                                    <th>Error</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($import_errors as $error): ?>
                                    <tr>
                                        <td><?= $error['row'] ?></td>
                                        <td><?= $error['register_no'] ?></td>
                                        <td><?= $error['date'] ?></td>
                                        <td><?= $error['error'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php echo form_open_multipart($this->uri->uri_string(), array('class' => 'validate', 'id' => 'upload_form')); ?>
        <input type="hidden" name="class_id" id="upload_class_id">
        <input type="hidden" name="section_id" id="upload_section_id">
        <?php if (is_superadmin_loggedin()): ?>
            <input type="hidden" name="branch_id" id="upload_branch_id">
        <?php endif; ?>
        <section class="panel" id="upload_panel" style="display:none;">
            <header class="panel-heading">
                <h4 class="panel-title">
                    <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" fill="#000000" width="19px" height="19px" style="display: inline-block; vertical-align: middle;" aria-hidden="true">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path d="M16 3l-12 12.223h7v13.777h10v-13.777h7z"></path>
                        </g>
                    </svg>
                    <?= translate('upload') ?> Attendance File
                </h4>
            </header>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label"><?= translate('select_excel_file') ?> <span class="required">*</span></label>
                            <div class="custom-file-upload">
                                <div class="file-upload-wrapper">
                                    <input type="text" class="form-control file-name-display" id="file_name_display" placeholder="No file chosen" readonly>
                                    <button type="button" class="btn btn-default btn-browse" id="browse_btn">
                                        <i class="fas fa-folder-open"></i> <?= translate('browse') ?>
                                    </button>
                                </div>
                                <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls" style="display:none;">
                            </div>
                            <span class="help-block">
                                <i class="fas fa-info-circle"></i> Supported formats: .xlsx, .xls
                            </span>
                            <span class="error"><?= form_error('excel_file') ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="panel-footer">
                <div class="row">
                    <div class="col-md-12 text-right">
                        <button type="submit" name="upload" value="1" class="btn btn-default" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
                            <i class="fas fa-upload"></i> <?= translate('upload') ?>
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
        $('#browse_btn').on('click', function(e) {
            e.preventDefault();
            $('#excel_file').trigger('click');
        });

        $('#excel_file').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            if (fileName) {
                $('#file_name_display').val(fileName);
            } else {
                $('#file_name_display').val('');
            }
        });

        $('#section_id').on('change', function() {
            checkUploadButton();
            updateHiddenFields();
        });

        $('#class_id').on('change', function() {
            checkUploadButton();
            updateHiddenFields();
        });

        function checkUploadButton() {
            var classID = $('#class_id').val();
            var sectionID = $('#section_id').val();

            if (classID && sectionID) {
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
            <?php if (is_superadmin_loggedin()): ?>
                $('#upload_branch_id').val($('#branch_id').val());
            <?php endif; ?>
        }

        $('#download_template').on('click', function() {
            var classID = $('#class_id').val();
            var sectionID = $('#section_id').val();

            if (!classID || !sectionID) {
                alert('<?= translate("please_select_all_fields") ?>');
                return;
            }

            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> <?= translate("generating") ?>...');

            $.ajax({
                url: base_url + 'attendance/download_attendance_template',
                type: 'POST',
                data: {
                    class_id: classID,
                    section_id: sectionID
                    <?php if (is_superadmin_loggedin()): ?>,
                        branch_id: $('#branch_id').val()
                    <?php endif; ?>
                },
                dataType: 'json',
                success: function(response) {
                    btn.prop('disabled', false).html('<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="19px" height="19px" style="display: inline-block; vertical-align: middle;" aria-hidden="true"><path d="M12.5535 16.5061C12.4114 16.6615 12.2106 16.75 12 16.75C11.7894 16.75 11.5886 16.6615 11.4465 16.5061L7.44648 12.1311C7.16698 11.8254 7.18822 11.351 7.49392 11.0715C7.79963 10.792 8.27402 10.8132 8.55352 11.1189L11.25 14.0682V3C11.25 2.58579 11.5858 2.25 12 2.25C12.4142 2.25 12.75 2.58579 12.75 3V14.0682L15.4465 11.1189C15.726 10.8132 16.2004 10.792 16.5061 11.0715C16.8118 11.351 16.833 11.8254 16.5535 12.1311L12.5535 16.5061Z" fill="currentColor"></path><path d="M3.75 15C3.75 14.5858 3.41422 14.25 3 14.25C2.58579 14.25 2.25 14.5858 2.25 15V15.0549C2.24998 16.4225 2.24996 17.5248 2.36652 18.3918C2.48754 19.2919 2.74643 20.0497 3.34835 20.6516C3.95027 21.2536 4.70814 21.5125 5.60825 21.6335C6.47522 21.75 7.57754 21.75 8.94513 21.75H15.0549C16.4225 21.75 17.5248 21.75 18.3918 21.6335C19.2919 21.5125 20.0497 21.2536 20.6517 20.6516C21.2536 20.0497 21.5125 19.2919 21.6335 18.3918C21.75 17.5248 21.75 16.4225 21.75 15.0549V15C21.75 14.5858 21.4142 14.25 21 14.25C20.5858 14.25 20.25 14.5858 20.25 15C20.25 16.4354 20.2484 17.4365 20.1469 18.1919C20.0482 18.9257 19.8678 19.3142 19.591 19.591C19.3142 19.8678 18.9257 20.0482 18.1919 20.1469C17.4365 20.2484 16.4354 20.25 15 20.25H9C7.56459 20.25 6.56347 20.2484 5.80812 20.1469C5.07435 20.0482 4.68577 19.8678 4.40901 19.591C4.13225 19.3142 3.9518 18.9257 3.85315 18.1919C3.75159 17.4365 3.75 16.4354 3.75 15Z" fill="currentColor"></path></svg> <span><?= translate("download_template") ?></span>');

                    if (response.status === 'success') {
                        window.location.href = response.download_url;
                    } else {
                        alert(response.message || '<?= translate("error_generating_template") ?>');
                    }
                },
                error: function() {
                    btn.prop('disabled', false).html('<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="19px" height="19px" style="display: inline-block; vertical-align: middle;" aria-hidden="true"><path d="M12.5535 16.5061C12.4114 16.6615 12.2106 16.75 12 16.75C11.7894 16.75 11.5886 16.6615 11.4465 16.5061L7.44648 12.1311C7.16698 11.8254 7.18822 11.351 7.49392 11.0715C7.79963 10.792 8.27402 10.8132 8.55352 11.1189L11.25 14.0682V3C11.25 2.58579 11.5858 2.25 12 2.25C12.4142 2.25 12.75 2.58579 12.75 3V14.0682L15.4465 11.1189C15.726 10.8132 16.2004 10.792 16.5061 11.0715C16.8118 11.351 16.833 11.8254 16.5535 12.1311L12.5535 16.5061Z" fill="currentColor"></path><path d="M3.75 15C3.75 14.5858 3.41422 14.25 3 14.25C2.58579 14.25 2.25 14.5858 2.25 15V15.0549C2.24998 16.4225 2.24996 17.5248 2.36652 18.3918C2.48754 19.2919 2.74643 20.0497 3.34835 20.6516C3.95027 21.2536 4.70814 21.5125 5.60825 21.6335C6.47522 21.75 7.57754 21.75 8.94513 21.75H15.0549C16.4225 21.75 17.5248 21.75 18.3918 21.6335C19.2919 21.5125 20.0497 21.2536 20.6517 20.6516C21.2536 20.0497 21.5125 19.2919 21.6335 18.3918C21.75 17.5248 21.75 16.4225 21.75 15.0549V15C21.75 14.5858 21.4142 14.25 21 14.25C20.5858 14.25 20.25 14.5858 20.25 15C20.25 16.4354 20.2484 17.4365 20.1469 18.1919C20.0482 18.9257 19.8678 19.3142 19.591 19.591C19.3142 19.8678 18.9257 20.0482 18.1919 20.1469C17.4365 20.2484 16.4354 20.25 15 20.25H9C7.56459 20.25 6.56347 20.2484 5.80812 20.1469C5.07435 20.0482 4.68577 19.8678 4.40901 19.591C4.13225 19.3142 3.9518 18.9257 3.85315 18.1919C3.75159 17.4365 3.75 16.4354 3.75 15Z" fill="currentColor"></path></svg> <span><?= translate("download_template") ?></span>');
                    alert('<?= translate("error_generating_template") ?>');
                }
            });
        });

        updateHiddenFields();
    });
</script>

<style>
    .cdev-dashboard-card {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    .cdev-card-header {
        padding: 15px;
        background: #f5f5f5;
        border-bottom: 1px solid #ddd;
        border-radius: 4px 4px 0 0;
    }

    .cdev-card-title {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }

    .cdev-card-body {
        padding: 20px;
    }

    .cdev-notification-notice {
        background: #e7f3ff;
        border: 1px solid #b3d9ff;
        border-radius: 4px;
        padding: 12px;
        display: flex;
        align-items: flex-start;
        margin-top: 15px;
    }

    .cdev-notification-icon {
        margin-right: 12px;
        color: #0066cc;
    }

    .cdev-notification-title {
        font-weight: 600;
        margin: 0 0 5px 0;
    }

    .cdev-notification-message {
        margin: 0;
    }

    .custom-file-upload {
        width: 100%;
    }

    .file-upload-wrapper {
        display: flex;
        gap: 10px;
        width: 100%;
    }

    .btn-browse {
        min-width: 120px;
        height: 44px;
        white-space: nowrap;
    }

    .file-name-display {
        flex: 1;
        height: 44px;
        background-color: #fff;
        cursor: default;
    }

    .file-name-display:focus {
        box-shadow: none;
        border-color: #ddd;
    }
</style>