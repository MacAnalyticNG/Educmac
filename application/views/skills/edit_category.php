<section class="panel">
	<div class="panel-heading">
		<h3 class="panel-title">
			<i class="fas fa-edit"></i> <?=translate('edit_skills_category')?>
		</h3>
	</div>
	<?php echo form_open($this->uri->uri_string(), array('class' => 'frm-submit'));?>
	<div class="panel-body">
		<div class="form-horizontal form-bordered">
			<?php if (is_superadmin_loggedin()): ?>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('branch')?> <span class="required">*</span></label>
					<div class="col-md-6">
						<?php
							$arrayBranch = $this->app_lib->getSelectList('branch');
							echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id', $category['branch_id']), "class='form-control'
							data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
						?>
						<span class="error"></span>
					</div>
				</div>
			<?php endif; ?>
			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('category_name')?> <span class="required">*</span></label>
				<div class="col-md-6">
					<input type="text" class="form-control" name="name" value="<?=set_value('name', $category['name'])?>" autocomplete="off" />
					<span class="error"></span>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('type')?> <span class="required">*</span></label>
				<div class="col-md-6">
					<?php
						$arrayType = array(
							'' => translate('select'),
							'affective' => 'Affective (Social & Emotional)',
							'psychomotor' => 'Psychomotor (Physical & Motor)',
							'cognitive' => 'Cognitive (Mental & Intellectual)',
						);
						echo form_dropdown("type", $arrayType, set_value('type', $category['type']), "class='form-control'
						data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
					?>
					<span class="error"></span>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('class_level')?> <span class="required">*</span></label>
				<div class="col-md-6">
					<?php
						$arrayLevel = array(
							'' => translate('select'),
							'primary' => 'Primary',
							'junior' => 'Junior',
							'senior' => 'Senior',
						);
						echo form_dropdown("class_level", $arrayLevel, set_value('class_level', $category['class_level']), "class='form-control'
						data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
					?>
					<span class="error"></span>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('description')?></label>
				<div class="col-md-6">
					<textarea name="description" rows="3" class="form-control"><?=set_value('description', $category['description'])?></textarea>
					<span class="error"></span>
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
						echo form_dropdown("status", $arrayStatus, set_value('status', $category['status']), "class='form-control'
						data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
					?>
					<span class="error"></span>
				</div>
			</div>
		</div>
	</div>
	<footer class="panel-footer">
		<div class="row">
			<div class="col-md-offset-3 col-md-6">
				<button type="submit" class="btn btn-default mr-xs" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
					<i class="fas fa-check"></i> <?=translate('update')?>
				</button>
				<a href="<?=base_url('skills/categories')?>" class="btn btn-default">
					<i class="fas fa-times"></i> <?=translate('cancel')?>
				</a>
			</div>
		</div>
	</footer>
	<?php echo form_close();?>
</section>

<script type="text/javascript">
	$('.frm-submit').on('submit', function(e) {
		e.preventDefault();
		var $form = $(this);
		var $btn = $form.find('button[type="submit"]');
		$btn.button('loading');
		$.ajax({
			url: $form.attr('action'),
			type: 'POST',
			dataType: 'json',
			data: $form.serialize(),
			success: function(data) {
				if (data.status == 'success') {
					window.location.href = data.url;
				} else {
					$btn.button('reset');
					$.each(data.error, function(key, value) {
						$('[name="' + key + '"]').closest('.form-group').find('.error').html(value);
					});
				}
			}
		});
	});
</script>
