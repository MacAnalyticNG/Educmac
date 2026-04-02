<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-futbol-o"></i> Extracurricular Activities</h1>
    </section>

    <section class="content">
        <div class="row">

            <!-- Add Activity -->
            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Add Activity Type</h3>
                    </div>
                    <div class="box-body">
                        <?php if($this->session->flashdata('success')): ?>
                            <div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
                        <?php endif; ?>

                        <form action="<?php echo site_url('extracurricular/create'); ?>" method="post">
                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                            <div class="form-group">
                                <label>Activity Name</label>
                                <input type="text" name="name" class="form-control" required placeholder="e.g. Football Club">
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Description of the activity..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Add Activity</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- List Activities -->
            <div class="col-md-8">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Available Activities</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</td>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($activities)): ?>
                                    <?php foreach ($activities as $act): ?>
                                    <tr>
                                        <td><?php echo $act->id; ?></td>
                                        <td><?php echo htmlspecialchars($act->name); ?></td>
                                        <td><?php echo htmlspecialchars($act->description); ?></td>
                                        <td>
                                            <a href="<?php echo site_url('extracurricular/assign/' . $act->id); ?>" class="btn btn-default btn-sm">Enroll Student</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No activities defined yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>
