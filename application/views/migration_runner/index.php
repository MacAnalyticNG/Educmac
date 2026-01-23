<section class="panel">
    <div class="tabs-custom">
        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#migrations" data-toggle="tab">
                    <i class="fas fa-database"></i> <?= translate('database_migrations') ?>
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <div id="migrations" class="tab-pane active">
                <div class="row mb-lg">
                    <div class="col-md-4">
                        <div class="panel panel-color panel-primary">
                            <div class="panel-heading text-center">
                                <h3 class="panel-title">
                                    <i class="fas fa-list-ul"></i> <?= translate('migration_status') ?>
                                </h3>
                            </div>
                            <div class="panel-body text-center">
                                <p class="text-muted"><?= translate('view_current_migration_status') ?></p>
                                <a href="<?= base_url('migration_runner/status') ?>" class="btn btn-primary btn-block mt-sm">
                                    <i class="fas fa-eye"></i> <?= translate('view_details') ?>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="panel panel-color panel-success">
                            <div class="panel-heading text-center">
                                <h3 class="panel-title">
                                    <i class="fas fa-arrow-up"></i> <?= translate('migrate_to_latest') ?>
                                </h3>
                            </div>
                            <div class="panel-body text-center">
                                <p class="text-muted"><?= translate('run_all_pending_migrations') ?></p>
                                <button onclick="confirmMigration('latest')" class="btn btn-success btn-block mt-sm">
                                    <i class="fas fa-sync-alt"></i> <?= translate('run_migration') ?>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="panel panel-color panel-warning">
                            <div class="panel-heading text-center">
                                <h3 class="panel-title">
                                    <i class="fas fa-undo"></i> <?= translate('rollback_migration') ?>
                                </h3>
                            </div>
                            <div class="panel-body text-center">
                                <p class="text-muted"><?= translate('rollback_to_previous_version') ?></p>
                                <button onclick="confirmRollback()" class="btn btn-warning btn-block mt-sm">
                                    <i class="fas fa-backward"></i> <?= translate('rollback') ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel panel-warning">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <i class="fas fa-exclamation-triangle"></i> <?= translate('important_notes') ?>
                        </h4>
                    </div>
                    <div class="panel-body">
                        <ul>
                            <li class="mb-xs">
                                <i class="fas fa-database text-primary"></i> <?= translate('migration_note_backup') ?>
                            </li>
                            <li class="mb-xs">
                                <i class="fas fa-user-shield text-success"></i> <?= translate('migration_note_superadmin') ?>
                            </li>
                            <li class="mb-xs">
                                <i class="fas fa-vial text-info"></i> <?= translate('migration_note_test') ?>
                            </li>
                            <li class="mb-xs">
                                <i class="fas fa-exclamation-circle text-danger"></i> <?= translate('migration_note_caution') ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
    function confirmMigration(type) {
        swal({
            title: "<?= translate('are_you_sure') ?>",
            text: "<?= translate('confirm_run_migration') ?>",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn btn-default swal2-btn-default",
            cancelButtonClass: "btn btn-default swal2-btn-default",
            confirmButtonText: "<?= translate('yes_run_migration') ?>",
            cancelButtonText: "<?= translate('cancel') ?>",
            buttonsStyling: false
        }).then((result) => {
            if (result.value) {
                window.location.href = base_url + 'migration_runner/' + type;
            }
        });
    }

    function confirmRollback() {
        swal({
            title: "<?= translate('are_you_sure') ?>",
            text: "<?= translate('confirm_rollback_migration') ?>",
            type: "error",
            showCancelButton: true,
            confirmButtonClass: "btn btn-default swal2-btn-default",
            cancelButtonClass: "btn btn-default swal2-btn-default",
            confirmButtonText: "<?= translate('yes_rollback') ?>",
            cancelButtonText: "<?= translate('cancel') ?>",
            buttonsStyling: false
        }).then((result) => {
            if (result.value) {
                window.location.href = base_url + 'migration_runner/rollback';
            }
        });
    }
</script>