<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-money"></i> Student Wallet System</h1>
    </section>

    <section class="content">
        <div class="row">
            <?php if ($role_id != 7 && $role_id != 6): ?>
                <!-- Admin / Staff Selector -->
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Select User to Manage Wallet</h3>
                        </div>
                        <div class="box-body">
                            <?php echo form_open('wallet/index'); ?>
                                <div class="form-group row">
                                    <div class="col-md-3">
                                        <select name="selection_type" class="form-control" required>
                                            <option value="student" <?php echo ($selection_type == 'student') ? 'selected' : ''; ?>>Student</option>
                                            <option value="parent" <?php echo ($selection_type == 'parent') ? 'selected' : ''; ?>>Parent</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <select name="user_id" class="form-control" required>
                                            <option value="">-- Select Person --</option>
                                            <optgroup label="Students">
                                            <?php if(isset($students)): foreach ($students as $stu): ?>
                                                <option value="<?php echo $stu['id']; ?>" <?php echo ($selection_type == 'student' && $user_id == $stu['id']) ? 'selected' : ''; ?>>
                                                    <?php echo $stu['first_name'] . ' ' . $stu['last_name'] . ' (' . $stu['register_no'] . ')'; ?>
                                                </option>
                                            <?php endforeach; endif; ?>
                                            </optgroup>
                                            <optgroup label="Parents">
                                            <?php if(isset($parents)): foreach ($parents as $par): ?>
                                                <option value="<?php echo $par['id']; ?>" <?php echo ($selection_type == 'parent' && $user_id == $par['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($par['name']) . ' (' . htmlspecialchars($par['email']) . ')'; ?>
                                                </option>
                                            <?php endforeach; endif; ?>
                                            </optgroup>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-primary btn-block">Load Wallet</button>
                                    </div>
                                </div>
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($wallet) && $wallet): ?>
            <!-- Balance Card -->
            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Current Balance <br/><small><?php echo htmlspecialchars($user_info ?? ''); ?></small></h3>
                    </div>
                    <div class="box-body text-center">
                        <h2>₦<?php echo number_format($wallet->balance ?? 0, 2); ?></h2>
                    </div>
                </div>

                <!-- Deposit Form -->
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Make a Deposit</h3>
                    </div>
                    <div class="box-body">
                        <?php if($this->session->flashdata('success')): ?>
                            <div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
                        <?php endif; ?>
                        <?php if($this->session->flashdata('error')): ?>
                            <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
                        <?php endif; ?>

                        <?php echo form_open('wallet/deposit'); ?>
                            <?php if ($wallet->student_id): ?>
                                <input type="hidden" name="student_id" value="<?php echo $wallet->student_id; ?>">
                            <?php endif; ?>
                            <?php if ($wallet->parent_id): ?>
                                <input type="hidden" name="parent_id" value="<?php echo $wallet->parent_id; ?>">
                            <?php endif; ?>
                            <div class="form-group">
                                <label>Amount (₦)</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required placeholder="Enter amount">
                            </div>
                            <button type="submit" class="btn btn-success btn-block">Deposit Funds</button>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="col-md-8">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Transaction History</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Date Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Amount (₦)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($transactions)): ?>
                                    <?php foreach ($transactions as $t): ?>
                                    <tr>
                                        <td><?php echo date('d M Y, h:i A', strtotime($t->created_at)); ?></td>
                                        <td>
                                            <?php if ($t->type == 'deposit'): ?>
                                                <span class="label label-success">Deposit</span>
                                            <?php elseif ($t->type == 'withdrawal'): ?>
                                                <span class="label label-danger">Withdrawal</span>
                                            <?php else: ?>
                                                <span class="label label-warning">Transfer</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($t->description); ?></td>
                                        <td><?php echo number_format($t->amount, 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No transactions found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php endif; ?>
        </div>
    </section>
</div>
