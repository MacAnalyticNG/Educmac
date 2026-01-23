<section class="panel">
    <div class="tabs-custom">
        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#status" data-toggle="tab">
                    <i class="fas fa-list-ul"></i> <?= translate('migration_status') ?>
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <div id="status" class="tab-pane active">
                <div class="mb-md">
                    <div class="alert alert-<?= !empty($current_version) ? 'success' : 'warning' ?>">
                        <i class="fas fa-database"></i>
                        <strong><?= translate('current_database_version') ?>:</strong>
                        <?= !empty($current_version) ? '<code style="background: #f5f5f5; padding: 2px 6px; border-radius: 3px;">' . $current_version . '</code>' : translate('not_migrated_yet') ?>
                    </div>
                </div>

                <?php if (empty($migrations)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <?= translate('no_migration_files_found') ?>
                    </div>
                <?php else: ?>
                    <div class="mb-md">
                        <button class="btn btn-success btn-circle" onclick="confirmMigration('latest')">
                            <i class="fas fa-sync-alt"></i> <?= translate('migrate_to_latest') ?>
                        </button>
                        <a href="<?= base_url('migration_runner') ?>" class="btn btn-default btn-circle">
                            <i class="fas fa-arrow-left"></i> <?= translate('back_to_menu') ?>
                        </a>
                    </div>

                    <table class="table table-bordered table-hover table-condensed mb-none">
                        <thead>
                            <tr>
                                <th width="80"><?= translate('sl') ?></th>
                                <th width="180"><?= translate('version') ?></th>
                                <th><?= translate('migration') ?></th>
                                <th width="120" class="text-center"><?= translate('status') ?></th>
                                <th width="150" class="text-center"><?= translate('action') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($migrations as $migration): ?>
                                <tr>
                                    <td><?= $migration['number'] ?></td>
                                    <td><code style="background: #f5f5f5; padding: 2px 6px; border-radius: 3px;"><?= $migration['version'] ?></code></td>
                                    <td><?= $migration['name'] ?></td>
                                    <td class="text-center">
                                        <?php if ($migration['status'] === 'applied'): ?>
                                            <span class="label label-success">
                                                <i class="fas fa-check"></i> <?= translate('applied') ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="label label-warning">
                                                <i class="fas fa-clock"></i> <?= translate('pending') ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($migration['status'] === 'pending'): ?>
                                            <button class="btn btn-xs btn-success" onclick="runSpecificMigration('<?= $migration['version'] ?>', '<?= htmlspecialchars($migration['name'], ENT_QUOTES) ?>')">
                                                <i class="fas fa-play"></i> Run
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-xs btn-info" onclick="runSpecificMigration('<?= $migration['version'] ?>', '<?= htmlspecialchars($migration['name'], ENT_QUOTES) ?>')" title="Re-apply this migration">
                                                <i class="fas fa-sync"></i> Re-run
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
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

    function runSpecificMigration(version, name) {
        swal({
            title: "<?= translate('are_you_sure') ?>",
            text: "Run migration: " + name + " (v" + version + ")?",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn btn-default swal2-btn-default",
            cancelButtonClass: "btn btn-default swal2-btn-default",
            confirmButtonText: "<?= translate('yes_run_migration') ?>",
            cancelButtonText: "<?= translate('cancel') ?>",
            buttonsStyling: false
        }).then((result) => {
            if (result.value) {
                window.location.href = base_url + 'migration_runner/run_specific/' + version;
            }
        });
    }
</script>