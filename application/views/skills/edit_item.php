<section class="panel">
	<div class="panel-heading">
		<h3 class="panel-title">
			<i class="fas fa-edit"></i> <?=translate('edit_skills_item')?>
		</h3>
	</div>
	<?php echo form_open($this->uri->uri_string(), array('class' => 'frm-submit'));?>
	<div class="panel-body">
		<div class="form-horizontal form-bordered">
			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('category')?> <span class="required">*</span></label>
				<div class="col-md-6">
					<?php
						$arrayCategories = array('' => translate('select'));
						foreach ($categories as $category) {
							$arrayCategories[$category['id']] = $category['name'] . ' (' . ucfirst($category['type']) . ')';
						}
						echo form_dropdown("category_id", $arrayCategories, set_value('category_id', $item['category_id']), "class='form-control'
						data-plugin-selectTwo data-width='100%'");
					?>
					<span class="error"></span>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('item_name')?> <span class="required">*</span></label>
				<div class="col-md-6">
					<input type="text" class="form-control" name="item_name" value="<?=set_value('item_name', $item['item_name'])?>" autocomplete="off" />
					<span class="error"></span>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('description')?></label>
				<div class="col-md-6">
					<textarea name="description" rows="3" class="form-control"><?=set_value('description', $item['description'])?></textarea>
					<span class="error"></span>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('display_order')?></label>
				<div class="col-md-6">
					<input type="number" class="form-control" name="display_order" value="<?=set_value('display_order', $item['display_order'])?>" min="0" />
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
						echo form_dropdown("status", $arrayStatus, set_value('status', $item['status']), "class='form-control'
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
				<a href="<?=base_url('skills/items')?>" class="btn btn-default">
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
