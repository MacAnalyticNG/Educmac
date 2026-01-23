<?php $widget = (is_superadmin_loggedin() ? 4 : 6); ?>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><?=translate('select_ground')?></h4>
			</header>
			<?php echo form_open('student/bulk_disable_accounts', array('class' => 'validate'));?>
			<div class="panel-body">
				<div class="row mb-sm">
					<?php if (is_superadmin_loggedin()): ?>
					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label"><?=translate('branch')?> <span class="required">*</span></label>
							<?php
								$arrayBranch = $this->app_lib->getSelectList('branch');
								echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='branch_id' required onchange='getClassByBranch(this.value)'
								data-plugin-selectTwo data-width='100%'");
							?>
						</div>
					</div>
					<?php endif; ?>
					<div class="col-md-<?php echo $widget; ?> mb-sm">
						<div class="form-group">
							<label class="control-label"><?=translate('class')?> <span class="required">*</span></label>
							<?php
								$arrayClass = $this->app_lib->getClass($branch_id);
								echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' required onchange='getSectionByClass(this.value)'
								data-plugin-selectTwo data-width='100%'");
							?>
						</div>
					</div>
					<div class="col-md-<?php echo $widget; ?> mb-sm">
						<div class="form-group">
							<label class="control-label"><?=translate('section')?> <span class="required">*</span></label>
							<?php
								$arraySection = $this->app_lib->getSections(set_value('class_id'));
								echo form_dropdown("section_id", $arraySection, set_value('section_id'), "class='form-control' id='section_id' required
								data-plugin-selectTwo data-width='100%'");
							?>
						</div>
					</div>
				</div>
			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-offset-10 col-md-2">
						<button type="submit" name="search" value="1" class="btn btn-default btn-block"> <i class="fas fa-filter"></i> <?=translate('filter')?></button>
					</div>
				</div>
			</footer>
			<?php echo form_close();?>
		</section>

		<?php if (isset($students)): ?>
		<section class="panel appear-animation" data-appear-animation="<?=$global_config['animations'] ?>" data-appear-animation-delay="100">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-user-graduate"></i> <?php echo translate('student_list');?></h4>
			</header>
			<?php echo form_open('student/bulk_disable_accounts', array('class' => 'validate')); ?>
			<div class="panel-body mb-md">
				<div class="alert alert-warning">
					<i class="fas fa-exclamation-triangle"></i> <?=translate('bulk_disable_warning_message')?>
				</div>
				<table class="table table-bordered table-hover table-condensed mb-none table-export">
					<thead>
						<tr>
							<th width="40px">
								<div class="checkbox-replace">
									<label class="i-checks"><input type="checkbox" id="selectAllchkbox"><i></i></label>
								</div>
							</th>
							<th width="80"><?php echo translate('photo');?></th>
							<th><?=translate('name')?></th>
							<th><?=translate('register_no')?></th>
							<th><?=translate('roll')?></th>
							<th><?=translate('class')?></th>
							<th><?=translate('section')?></th>
							<th><?=translate('mobile_no')?></th>
							<th><?=translate('status')?></th>
						</tr>
					</thead>
					<tbody>
						<?php 
						if (!empty($students)) {
							foreach ($students as $row): 
								// Get login status
								$this->db->select('active');
								$this->db->where('user_id', $row['student_id']);
								$this->db->where('role', 7);
								$login_status = $this->db->get('login_credential')->row();
								$is_active = ($login_status && $login_status->active == 1);
						?>
						<tr>
							<td class="checked-area">
								<div class="checkbox-replace">
									<label class="i-checks"><input type="checkbox" name="bulk_disable_operations[]" value="<?=html_escape($row['student_id'])?>" <?php echo (!$is_active ? 'disabled' : ''); ?>><i></i></label>
								</div>
							</td>
							<td class="center">
								<img class="rounded" src="<?php echo get_image_url('student', $row['photo']); ?>" width="35" height="35" />
							</td>
							<td><?php echo html_escape($row['first_name'] . ' ' . $row['last_name']);?></td>
							<td><?php echo html_escape($row['register_no']);?></td>
							<td><?php echo html_escape($row['roll']);?></td>
							<td><?php echo html_escape($row['class_name']);?></td>
							<td><?php echo html_escape($row['section_name']);?></td>
							<td><?php echo html_escape($row['mobileno']);?></td>
							<td>
								<?php if ($is_active): ?>
									<span class="label label-success"><i class="fas fa-check-circle"></i> <?=translate('active')?></span>
								<?php else: ?>
									<span class="label label-danger"><i class="fas fa-ban"></i> <?=translate('disabled')?></span>
								<?php endif; ?>
							</td>
						</tr>
						<?php 
							endforeach;
						} else {
							echo '<tr><td colspan="9" class="text-center">' . translate('no_information_available') . '</td></tr>';
						}
						?>
					</tbody>
				</table>
			</div>
			<?php if(get_permission('student', 'is_edit') && !empty($students)): ?>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-12">
						<button type="submit" name="disable_accounts" value="1" class="btn btn-danger pull-right" onclick="return confirm('<?=translate('are_you_sure_to_disable_selected_accounts')?>')">
							<i class="fas fa-ban"></i> <?=translate('disable_selected_accounts')?>
						</button>
					</div>
				</div>
			</footer>
			<?php endif; ?>
			<?php echo form_close(); ?>
		</section>
		<?php endif;?>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		$('#selectAllchkbox').on('ifChecked', function(event) {
			$('input[name="bulk_disable_operations[]"]:not(:disabled)').iCheck('check');
		});
		$('#selectAllchkbox').on('ifUnchecked', function(event) {
			$('input[name="bulk_disable_operations[]"]').iCheck('uncheck');
		});
	});
</script>
