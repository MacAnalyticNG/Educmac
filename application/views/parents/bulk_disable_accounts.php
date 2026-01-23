<?php if (is_superadmin_loggedin()): ?>
    <section class="panel">
        <header class="panel-heading">
            <h4 class="panel-title"><?= translate('select_ground') ?></h4>
        </header>
        <?php echo form_open('parents/bulk_disable_accounts', array('class' => 'validate')); ?>
        <div class="panel-body">
            <div class="row mb-sm">
                <div class="col-md-offset-3 col-md-6">
                    <div class="form-group">
                        <label class="control-label"><?= translate('branch') ?> <span class="required">*</span></label>
                        <?php
                        $arrayBranch = $this->app_lib->getSelectList('branch');
                        echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' required
							data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <footer class="panel-footer">
            <div class="row">
                <div class="col-md-offset-10 col-md-2">
                    <button type="submit" name="search" value="1" class="btn btn-default btn-block"> <i class="fas fa-filter"></i> <?= translate('filter') ?></button>
                </div>
            </div>
        </footer>
        <?php echo form_close(); ?>
    </section>
<?php endif; ?>

<div class="row">
    <div class="col-md-12">
        <section class="panel appear-animation" data-appear-animation="<?= $global_config['animations'] ?>" data-appear-animation-delay="100">
            <header class="panel-heading">
                <h4 class="panel-title"><i class="fas fa-users"></i> <?php echo translate('parents_list'); ?></h4>
            </header>
            <?php echo form_open('parents/bulk_disable_accounts', array('class' => 'validate')); ?>
            <div class="panel-body mb-md">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> <?= translate('bulk_disable_warning_message') ?>
                </div>
                <table class="table table-bordered table-hover table-condensed mb-none table-export">
                    <thead>
                        <tr>
                            <th width="40px">
                                <div class="checkbox-replace">
                                    <label class="i-checks"><input type="checkbox" id="selectAllchkbox"><i></i></label>
                                </div>
                            </th>
                            <th><?= translate('sl') ?></th>
                            <?php if (is_superadmin_loggedin()) { ?>
                                <th><?= translate('branch') ?></th>
                            <?php } ?>
                            <th><?= translate('guardian_name') ?></th>
                            <th><?= translate('relation') ?></th>
                            <th><?= translate('occupation') ?></th>
                            <th><?= translate('mobile_no') ?></th>
                            <th><?= translate('email') ?></th>
                            <th><?= translate('status') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $count = 1;
                        if (!empty($parentslist)) {
                            foreach ($parentslist as $row):
                                // Get login status
                                $this->db->select('active');
                                $this->db->where('user_id', $row->id);
                                $this->db->where('role', 6);
                                $login_status = $this->db->get('login_credential')->row();
                                $is_active = ($login_status && $login_status->active == 1);
                        ?>
                                <tr>
                                    <td class="checked-area">
                                        <div class="checkbox-replace">
                                            <label class="i-checks"><input type="checkbox" name="bulk_disable_operations[]" value="<?= html_escape($row->id) ?>" <?php echo (!$is_active ? 'disabled' : ''); ?>><i></i></label>
                                        </div>
                                    </td>
                                    <td><?php echo $count++; ?></td>
                                    <?php if (is_superadmin_loggedin()) { ?>
                                        <td><?php echo html_escape(get_type_name_by_id('branch', $row->branch_id)); ?></td>
                                    <?php } ?>
                                    <td><?php echo html_escape($row->name); ?></td>
                                    <td><?php echo html_escape($row->relation); ?></td>
                                    <td><?php echo html_escape($row->occupation); ?></td>
                                    <td><?php echo html_escape($row->mobileno); ?></td>
                                    <td><?php echo html_escape($row->email); ?></td>
                                    <td>
                                        <?php if ($is_active): ?>
                                            <span class="label label-success"><i class="fas fa-check-circle"></i> <?= translate('active') ?></span>
                                        <?php else: ?>
                                            <span class="label label-danger"><i class="fas fa-ban"></i> <?= translate('disabled') ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                        <?php
                            endforeach;
                        } else {
                            $colspan = is_superadmin_loggedin() ? 9 : 8;
                            echo '<tr><td colspan="' . $colspan . '" class="text-center">' . translate('no_information_available') . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php if (get_permission('parent', 'is_edit') && !empty($parentslist)): ?>
                <footer class="panel-footer">
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" name="disable_accounts" value="1" class="btn btn-danger pull-right" onclick="return confirm('<?= translate('are_you_sure_to_disable_selected_accounts') ?>')">
                                <i class="fas fa-ban"></i> <?= translate('disable_selected_accounts') ?>
                            </button>
                        </div>
                    </div>
                </footer>
            <?php endif; ?>
            <?php echo form_close(); ?>
        </section>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#selectAllchkbox').on('ifChecked', function(event) {
            $('input[name="bulk_disable_operations[]"]:not(:disabled)').iCheck('check');
        });
        $('#selectAllchkbox').on('ifUnchecked', function(event) {
            $('input[name="bulk_disable_operations[]"]').iCheck('uncheck');
        });
    });
</script>