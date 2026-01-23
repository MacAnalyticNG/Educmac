<section class="panel">
    <div class="tabs-custom">
        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#result" data-toggle="tab">
                    <i class="fas fa-<?= $status === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                    <?= translate('migration_result') ?>
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <div id="result" class="tab-pane active">
                <div class="text-center" style="padding: 40px 20px;">
                    <?php if ($status === 'success'): ?>
                        <i class="fas fa-check-circle" style="font-size: 80px; color: #5cb85c; margin-bottom: 30px;"></i>
                        <h2 class="text-weight-bold text-success"><?= translate('success') ?></h2>
                        <p class="mt-md mb-lg" style="font-size: 16px; color: #666;"><?= $message ?></p>
                    <?php else: ?>
                        <i class="fas fa-exclamation-circle" style="font-size: 80px; color: #d9534f; margin-bottom: 30px;"></i>
                        <h2 class="text-weight-bold text-danger"><?= translate('error') ?></h2>
                        <div class="mt-md mb-lg">
                            <div class="alert alert-danger text-left" style="max-width: 800px; margin: 0 auto;">
                                <h4><i class="fas fa-bug"></i> <?= translate('error_details') ?></h4>
                                <div style="max-height: 300px; overflow-y: auto; background: #fff; padding: 15px; border-radius: 4px; margin-top: 10px;">
                                    <pre style="white-space: pre-wrap; word-wrap: break-word; margin: 0; font-size: 13px; color: #333;"><?= htmlspecialchars($message) ?></pre>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="mt-lg">
                        <a href="<?= base_url('migration_runner/status') ?>" class="btn btn-primary btn-lg">
                            <i class="fas fa-list-ul"></i> <?= translate('view_status') ?>
                        </a>
                        <a href="<?= base_url('migration_runner') ?>" class="btn btn-default btn-lg">
                            <i class="fas fa-arrow-left"></i> <?= translate('back_to_menu') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>