<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-bell"></i> Notification Center</h1>
    </section>

    <section class="content">
        <div class="row">

            <!-- Send Notification Form (For Admins) -->
            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Send Broadcast</h3>
                    </div>
                    <div class="box-body">
                        <?php if($this->session->flashdata('success')): ?>
                            <div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
                        <?php endif; ?>

                        <form action="<?php echo site_url('notifications/send'); ?>" method="post">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control" required placeholder="Subject...">
                            </div>
                            <div class="form-group">
                                <label>Message</label>
                                <textarea name="message" class="form-control" rows="4" required placeholder="Message content..."></textarea>
                            </div>
                            <div class="form-group">
                                <label>Target Role</label>
                                <select name="role" class="form-control">
                                    <option value="student">Students</option>
                                    <option value="parent">Parents</option>
                                    <option value="teacher">Teachers</option>
                                    <option value="admin">Administrators</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Send Notification</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- List Notifications -->
            <div class="col-md-8">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">My Notifications</h3>
                    </div>
                    <div class="box-body">
                        <ul class="timeline">
                            <?php if (!empty($notifications)): ?>
                                <?php foreach ($notifications as $n): ?>
                                <li>
                                    <i class="fa fa-envelope <?php echo $n->is_read ? 'bg-gray' : 'bg-blue'; ?>"></i>
                                    <div class="timeline-item">
                                        <span class="time"><i class="fa fa-clock-o"></i> <?php echo date('d M Y, h:i A', strtotime($n->created_at)); ?></span>
                                        <h3 class="timeline-header <?php echo !$n->is_read ? 'text-bold' : ''; ?>"><?php echo htmlspecialchars($n->title); ?></h3>
                                        <div class="timeline-body">
                                            <?php echo nl2br(htmlspecialchars($n->message)); ?>
                                        </div>
                                        <?php if (!$n->is_read): ?>
                                        <div class="timeline-footer">
                                            <a href="<?php echo site_url('notifications/mark_read/'.$n->id); ?>" class="btn btn-primary btn-xs">Mark as Read</a>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                                <li>
                                    <i class="fa fa-clock-o bg-gray"></i>
                                </li>
                            <?php else: ?>
                                <li>
                                    <div class="timeline-item">
                                        <div class="timeline-body text-center">
                                            You have no notifications.
                                        </div>
                                    </div>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>
