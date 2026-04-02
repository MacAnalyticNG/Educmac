<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-futbol-o"></i> Enroll in <?php echo htmlspecialchars($activity->name); ?></h1>
    </section>

    <section class="content">
        <div class="row">
            <!-- Add Enrollment -->
            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Assign Student</h3>
                    </div>
                    <div class="box-body">
                        <?php if($this->session->flashdata('success')): ?>
                            <div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
                        <?php endif; ?>
                        <?php if($this->session->flashdata('error')): ?>
                            <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
                        <?php endif; ?>

                        <?php echo form_open('extracurricular/save_assign'); ?>
                            <input type="hidden" name="activity_id" value="<?php echo $activity->id; ?>">
                            <div class="form-group">
                                <label>Select Student</label>
                                <select name="student_id" class="form-control" required>
                                    <option value="">-- Select Student --</option>
                                    <?php foreach ($students as $stu): ?>
                                        <option value="<?php echo $stu['id']; ?>"><?php echo $stu['first_name'] . ' ' . $stu['last_name'] . ' (' . $stu['register_no'] . ')'; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Enroll Student</button>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>

            <!-- List Enrollments -->
            <div class="col-md-8">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Enrolled Students</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Register No</th>
                                    <th>Name</th>
                                    <th>Date Enrolled</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($enrollments)): ?>
                                    <?php foreach ($enrollments as $enr): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($enr['register_no']); ?></td>
                                        <td><?php echo htmlspecialchars($enr['first_name'] . ' ' . $enr['last_name']); ?></td>
                                        <td><?php echo $enr['enrollment_date']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No students enrolled yet.</td>
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
