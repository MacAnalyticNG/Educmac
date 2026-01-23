<section class="panel">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#list" data-toggle="tab"><i class="fas fa-list-ul"></i> <?=translate('skills_items')?></a>
			</li>
<?php if (get_permission('skills_items', 'is_add')): ?>
			<li>
				<a href="#create" data-toggle="tab"><i class="far fa-edit"></i> <?=translate('add_item')?></a>
			</li>
<?php endif; ?>
		</ul>
		<div class="tab-content">
			<div id="list" class="tab-pane active">
				<?php if (is_superadmin_loggedin()): ?>
				<div class="panel-body">
					<?php echo form_open($this->uri->uri_string(), array('method' => 'get', 'class' => 'form-inline mb-md')); ?>
						<div class="form-group">
							<label><?= translate('branch') ?>:</label>
							<?php
							$arrayBranch = array("" => translate('all_branches'));
							$branches = $this->app_lib->getSelectList('branch');
							$arrayBranch = $arrayBranch + $branches;
							echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id', $this->input->get('branch_id')),
								"class='form-control' data-plugin-selectTwo data-width='200px' onchange='this.form.submit()'");
							?>
						</div>
					<?php echo form_close(); ?>
				</div>
				<?php endif; ?>
				<table class="table table-bordered table-hover mb-none table-export">
					<thead>
						<tr>
							<th width="50"><?=translate('sl')?></th>
							<th><?=translate('category')?></th>
							<th><?=translate('type')?></th>
							<th><?=translate('item_name')?></th>
							<th><?=translate('description')?></th>
							<th><?=translate('display_order')?></th>
							<th><?=translate('status')?></th>
							<th><?=translate('action')?></th>
						</tr>
					</thead>
					<tbody>
						<?php $count = 1; foreach($items as $row): ?>
						<tr>
							<td><?php echo $count++; ?></td>
							<td><?php echo $row['category_name']; ?></td>
							<td>
								<span class="badge badge-<?php
									echo $row['category_type'] == 'affective' ? 'info' :
										($row['category_type'] == 'psychomotor' ? 'success' : 'warning');
								?>">
									<?php echo ucfirst($row['category_type']); ?>
								</span>
							</td>
							<td><?php echo $row['item_name']; ?></td>
							<td><?php echo $row['description']; ?></td>
							<td><?php echo $row['display_order']; ?></td>
							<td>
								<span class="label label-<?php echo $row['status'] == 'active' ? 'success' : 'danger'; ?>">
									<?php echo ucfirst($row['status']); ?>
								</span>
							</td>
							<td class="action">
							<?php if (get_permission('skills_items', 'is_edit')): ?>
								<!-- edit link -->
								<a href="<?php echo base_url('skills/edit_item/' . $row['id']);?>" class="btn btn-default btn-circle icon">
									<i class="fas fa-pen-nib"></i>
								</a>
							<?php endif; if (get_permission('skills_items', 'is_delete')): ?>
								<!-- delete link -->
								<?php echo btn_delete('skills/delete_item/' . $row['id']);?>
							<?php endif; ?>
							</td>
						</tr>
						<?php endforeach;?>
					</tbody>
				</table>
			</div>
<?php if (get_permission('skills_items', 'is_add')): ?>
			<div class="tab-pane" id="create">
				<?php echo form_open($this->uri->uri_string(), array('class' => 'frm-submit'));?>
					<div class="form-horizontal form-bordered mb-lg">
						<div class="form-group">
							<label class="col-md-3 control-label"><?=translate('category')?> <span class="required">*</span></label>
							<div class="col-md-6">
								<?php
									$arrayCategories = array('' => translate('select'));
									foreach ($categories as $category) {
										$arrayCategories[$category['id']] = $category['name'] . ' (' . ucfirst($category['type']) . ')';
									}
									echo form_dropdown("category_id", $arrayCategories, set_value('category_id'), "class='form-control'
									data-plugin-selectTwo data-width='100%'");
								?>
								<span class="error"></span>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label"><?=translate('item_name')?> <span class="required">*</span></label>
							<div class="col-md-6">
								<input type="text" class="form-control" name="item_name" value="" autocomplete="off"
									placeholder="e.g., Punctuality, Handwriting, Problem Solving" />
								<span class="error"></span>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label"><?=translate('description')?></label>
							<div class="col-md-6">
								<textarea name="description" rows="3" class="form-control"
									placeholder="Brief description of this skill item"></textarea>
								<span class="error"></span>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label"><?=translate('display_order')?></label>
							<div class="col-md-6">
								<input type="number" class="form-control" name="display_order" value="<?php echo isset($next_display_order) ? $next_display_order : 0; ?>" min="0" />
								<span class="error"></span>
								<span class="help-block"><small>Lower numbers appear first in lists</small></span>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label"><?=translate('status')?> <span class="required">*</span></label>
							<div class="col-md-6">
								<?php
									$arrayStatus = array(
										'' => translate('select'),
										'active' => translate('active'),
										'inactive' => translate('inactive'),
									);
									echo form_dropdown("status", $arrayStatus, 'active', "class='form-control'
									data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
								?>
								<span class="error"></span>
							</div>
						</div>
					</div>
					<footer class="panel-footer">
						<div class="row">
							<div class="col-md-offset-3 col-md-6">
								<button type="submit" class="btn btn-default btn-block" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
									<i class="fas fa-plus-circle"></i> <?=translate('save')?>
								</button>
							</div>
						</div>
					</footer>
				<?php echo form_close();?>
			</div>
<?php endif; ?>
		</div>
	</div>
</section>

<script type="text/javascript">
$(document).ready(function() {
	var isSubmitting = false;

	// Prevent duplicate submissions
	$('.frm-submit').off('submit').on('submit', function(e) {
		e.preventDefault();

		// Check if already submitting
		if (isSubmitting) {
			console.log('Form already submitting, ignoring duplicate submission');
			return false;
		}

		var $form = $(this);
		var $btn = $form.find('button[type="submit"]');

		// Set flag and disable button
		isSubmitting = true;
		$btn.prop('disabled', true).button('loading');

		$.ajax({
			url: $form.attr('action'),
			type: 'POST',
			dataType: 'json',
			data: $form.serialize(),
			success: function(data) {
				if (data.status == 'success') {
					window.location.href = data.url;
				} else {
					// Reset flag and button on error
					isSubmitting = false;
					$btn.prop('disabled', false).button('reset');
					$.each(data.error, function(key, value) {
						$('[name="' + key + '"]').closest('.form-group').find('.error').html(value);
					});
				}
			},
			error: function() {
				// Reset flag and button on error
				isSubmitting = false;
				$btn.prop('disabled', false).button('reset');
			}
		});

		return false;
	});
});
</script>
