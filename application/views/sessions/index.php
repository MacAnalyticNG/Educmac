<div class="row">
	<div class="col-md-5">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><?=translate('add_session')?></h4>
			</header>
			<?php echo form_open($this->uri->uri_string()); ?>
			<div class="panel-body">
				<div class="form-group mb-md">
					<label class="control-label"><?=translate('session')?> <span class="required">*</span></label>
					<input type="text" class="form-control" name="session" value="<?=set_value('session')?>" placeholder="e.g., 2024/2025" />
					<span class="error"><?=form_error('session')?></span>
					<small class="text-muted"><?=translate('format')?>: YYYY/YYYY (e.g., 2024/2025)</small>
				</div>
			</div>
			<div class="panel-footer">
				<div class="row">
					<div class="col-md-12">
						<button class="btn btn-default pull-right" type="submit" name="save" value="1">
							<i class="fas fa-plus-circle"></i> <?=translate('save')?>
						</button>
					</div>
				</div>
			</div>
			<?php echo form_close();?>
		</section>
	</div>

	<div class="col-md-7">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><?=translate('sessions_list')?></h4>
			</header>
			<div class="panel-body">
				<div class="table-responsive">
					<table class="table table-bordered table-hover table-condensed mb-md">
						<thead>
							<tr>
								<th><?=translate('session')?></th>
								<th><?=translate('status')?></th>
								<th><?=translate('created_at')?></th>
								<th><?=translate('action')?></th>
							</tr>
						</thead>
						<tbody>

						<?php
						$result = $this->db->order_by('school_year', 'DESC')->get('schoolyear')->result_array();
						if (count($result)):
							foreach ($result as $row):
						?>
							<tr>
								<td><strong><?php echo $row['school_year']; ?></strong></td>
								<td>
									<?php if (get_session_id() == $row['id']) :?>
									<span class="label label-success"> <?=translate('active')?></span>
									<?php else: ?>
									<a href="<?=base_url('sessions/set_academic/' . $row['id'])?>" class="label label-default">
										<?=translate('activate')?>
									</a>
									<?php endif;?>
								</td>
								<th><?php echo _d($row['created_at']);?></th>
								<td>
									<!-- view terms -->
									<a class="btn btn-default btn-circle icon viewTerms" href="javascript:void(0);" data-session-id="<?=$row['id']?>" data-session-name="<?=$row['school_year']?>" title="<?=translate('view_terms')?>">
										<i class="fas fa-calendar-week"></i>
									</a>

									<!-- update link -->
									<a class="btn btn-default btn-circle icon editModal" href="javascript:void(0);" data-id="<?=$row['id']?>" data-session="<?=$row['school_year']?>">
										<i class="fas fa-pen-nib"></i>
									</a>

									<!-- delete link -->
									<?php
									if (get_session_id() != $row['id'])
										echo btn_delete('sessions/delete/' . $row['id']);
									?>
								</td>
							</tr>
						<?php endforeach; else: ?>
							<tr>
								<td colspan="4" class="text-center">
									<i class="fas fa-inbox" style="font-size: 48px; color: #ccc; margin: 20px 0;"></i>
									<p><?=translate('no_sessions_found')?></p>
								</td>
							</tr>
						<?php endif;?>
						</tbody>
					</table>
				</div>
			</div>
		</section>
	</div>
</div>

<!-- Academic Terms Panel -->
<?php if (academic_terms_enabled()): ?>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title">
					<i class="fas fa-list"></i> <?=translate('terms_for_current_session')?>
					<?php if (isset($branch_id) && !empty($branch_id)): ?>
						<small class="text-muted">(Branch ID: <?=$branch_id?>)</small>
					<?php endif; ?>
				</h4>
			</header>
			<div class="panel-body">
				<?php if (isset($all_terms) && !empty($all_terms)): ?>
					<div class="table-responsive">
						<table class="table table-bordered table-hover mb-md">
							<thead>
								<tr>
									<th><?=translate('term_name')?></th>
									<th><?=translate('start_date')?></th>
									<th><?=translate('end_date')?></th>
									<th><?=translate('total_weeks')?></th>
									<th><?=translate('status')?></th>
									<th><?=translate('action')?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($all_terms as $term): ?>
									<tr data-term-id="<?=$term->id?>">
										<td><strong><?=$term->term_name?></strong></td>
										<td class="editable-date" data-field="start_date" data-term-id="<?=$term->id?>">
											<span class="date-display"><?=date('M d, Y', strtotime($term->start_date))?></span>
											<input type="date" class="form-control date-input" value="<?=$term->start_date?>" style="display:none; max-width: 150px;">
											<button class="btn btn-xs btn-info edit-date-btn" title="<?=translate('click_to_edit')?>">
												<i class="fas fa-pencil-alt"></i>
											</button>
										</td>
										<td class="editable-date" data-field="end_date" data-term-id="<?=$term->id?>">
											<span class="date-display"><?=date('M d, Y', strtotime($term->end_date))?></span>
											<input type="date" class="form-control date-input" value="<?=$term->end_date?>" style="display:none; max-width: 150px;">
											<button class="btn btn-xs btn-info edit-date-btn" title="<?=translate('click_to_edit')?>">
												<i class="fas fa-pencil-alt"></i>
											</button>
										</td>
										<td class="total-weeks-cell"><?=$term->total_weeks?> <?=translate('weeks')?></td>
										<td>
											<?php if ($term->is_active == 1): ?>
												<span class="label label-success">
													<i class="fas fa-check-circle"></i> <?=translate('active')?>
												</span>
											<?php else: ?>
												<span class="label label-default">
													<i class="fas fa-circle"></i> <?=translate('inactive')?>
												</span>
											<?php endif; ?>
										</td>
										<td>
											<a class="btn btn-default btn-sm edit-term" href="javascript:void(0);"
												data-term-id="<?=$term->id?>"
												data-term-name="<?=$term->term_name?>"
												data-start-date="<?=$term->start_date?>"
												data-end-date="<?=$term->end_date?>"
												data-total-weeks="<?=$term->total_weeks?>"
												data-branch-id="<?=isset($branch_id) ? $branch_id : ''?>"
												title="<?=translate('edit_term')?>">
												<i class="fas fa-edit"></i> <?=translate('edit')?>
											</a>

											<?php if ($term->is_active != 1): ?>
												<button class="btn btn-success btn-sm activate-term" data-term-id="<?=$term->id?>" data-branch-id="<?=isset($branch_id) ? $branch_id : ''?>" title="<?=translate('activate_term')?>">
													<i class="fas fa-check"></i> <?=translate('activate')?>
												</button>
											<?php else: ?>
												<span class="text-success">
													<i class="fas fa-check-circle"></i> <?=translate('current')?>
												</span>
											<?php endif; ?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php else: ?>
					<div class="alert alert-warning">
						<i class="fas fa-exclamation-triangle"></i>
						<?=translate('no_terms_found_for_current_session')?>
						<?php if (isset($branch_id)): ?>
							<br><small>Branch ID: <?=$branch_id?>, Session ID: <?=get_session_id()?></small>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
	</div>
</div>
<?php endif; ?>

<!-- Edit Session Modal -->
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
	<section class="panel">
		<?php echo form_open('sessions/edit', array('class' => 'frm-submit')); ?>
			<header class="panel-heading">
				<h4 class="panel-title">
					<i class="far fa-edit"></i> <?=translate('edit_session')?>
				</h4>
			</header>
			<div class="panel-body">
				<input type="hidden" name="schoolyear_id" id="schoolyear_id" value="" >
				<div class="form-group mb-md">
					<label class="control-label"><?=translate('sessions')?> <span class="required">*</span></label>
					<input type="text" class="form-control" value="" name="session" id="session" placeholder="e.g., 2024/2025" />
					<span class="error"></span>
				</div>
			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-12 text-right">
						<button type="submit" class="btn btn-default" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing"><?=translate('update')?></button>
						<button class="btn btn-default modal-dismiss"><?=translate('cancel')?></button>
					</div>
				</div>
			</footer>
		<?php echo form_close();?>
	</section>
</div>

<!-- Edit Term Modal -->
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="editTermModal">
	<section class="panel">
		<?php echo form_open('sessions/edit_term', array('class' => 'frm-submit-term')); ?>
			<header class="panel-heading">
				<h4 class="panel-title">
					<i class="fas fa-edit"></i> <?=translate('edit_term')?>
				</h4>
			</header>
			<div class="panel-body">
				<input type="hidden" name="term_id" id="edit_term_id" value="">
				<input type="hidden" name="branch_id" id="edit_branch_id" value="">

				<div class="form-group mb-md">
					<label class="control-label"><?=translate('term_name')?></label>
					<input type="text" class="form-control" name="term_name" id="edit_term_name" readonly style="background-color: #f5f5f5;" />
				</div>

				<div class="row">
					<div class="col-md-6">
						<div class="form-group mb-md">
							<label class="control-label"><?=translate('start_date')?> <span class="required">*</span></label>
							<input type="text" class="form-control" name="start_date" id="edit_start_date" data-plugin-datepicker data-plugin-options='{ "todayHighlight": true, "format": "yyyy-mm-dd" }' autocomplete="off" />
							<span class="error"></span>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group mb-md">
							<label class="control-label"><?=translate('end_date')?> <span class="required">*</span></label>
							<input type="text" class="form-control" name="end_date" id="edit_end_date" data-plugin-datepicker data-plugin-options='{ "todayHighlight": true, "format": "yyyy-mm-dd" }' autocomplete="off" />
							<span class="error"></span>
						</div>
					</div>
				</div>

				<div class="form-group mb-md">
					<label class="control-label"><?=translate('total_weeks')?></label>
					<input type="number" class="form-control" name="total_weeks" id="edit_total_weeks" min="1" max="52" readonly style="background-color: #f5f5f5;" />
					<span class="error"></span>
					<small class="text-muted"><i class="fas fa-info-circle"></i> <?=translate('auto_calculated_from_dates')?></small>
				</div>
			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-12 text-right">
						<button type="submit" class="btn btn-default" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
							<?=translate('update')?>
						</button>
						<button type="button" class="btn btn-default modal-dismiss"><?=translate('cancel')?></button>
					</div>
				</div>
			</footer>
		<?php echo form_close();?>
	</section>
</div>

<!-- View Terms Modal -->
<div class="zoom-anim-dialog modal-block modal-block-lg modal-block-primary mfp-hide" id="termsModal">
	<section class="panel">
		<header class="panel-heading">
			<h4 class="panel-title">
				<i class="fas fa-calendar-week"></i> <span id="termsModalTitle"><?=translate('terms')?></span>
			</h4>
		</header>
		<div class="panel-body" id="termsModalBody">
			<div style="text-align: center; padding: 40px;">
				<i class="fas fa-spinner fa-spin" style="font-size: 48px; color: #2196F3;"></i>
				<p style="margin-top: 15px; color: #666;"><?=translate('loading')?>...</p>
			</div>
		</div>
		<footer class="panel-footer">
			<div class="row">
				<div class="col-md-12 text-right">
					<button type="button" class="btn btn-default modal-dismiss"><?=translate('close')?></button>
				</div>
			</div>
		</footer>
	</section>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		// Calculate weeks from date range
		function calculateWeeks(startDate, endDate) {
			if (!startDate || !endDate) {
				return '';
			}
			var start = new Date(startDate);
			var end = new Date(endDate);
			if (start > end) {
				return '';
			}
			var diffTime = Math.abs(end - start);
			var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
			var weeks = Math.ceil(diffDays / 7);
			return weeks;
		}

		// Auto-calculate weeks when dates change
		$('#edit_start_date, #edit_end_date').on('change', function() {
			var startDate = $('#edit_start_date').val();
			var endDate = $('#edit_end_date').val();
			var weeks = calculateWeeks(startDate, endDate);
			if (weeks !== '') {
				$('#edit_total_weeks').val(weeks);
			}
		});

		// Edit term form submission
		$('.frm-submit-term').on('submit', function(e) {
			e.preventDefault();
			var form = $(this);
			var btn = form.find('button[type="submit"]');
			var btnText = btn.html();
			btn.prop('disabled', true).html(btn.data('loading-text'));
			$('.error').html('');

			$.ajax({
				url: form.attr('action'),
				type: 'POST',
				data: form.serialize(),
				dataType: 'json',
				success: function(response) {
					btn.prop('disabled', false).html(btnText);
					if (response.status === 'success') {
						$.magnificPopup.close();
						setTimeout(function() {
							location.reload();
						}, 300);
					} else if (response.status === 'fail') {
						swal({
							title: '<?=translate('error')?>',
							text: '<?=translate('validation_errors')?>',
							type: 'error',
							confirmButtonClass: 'btn btn-default',
							buttonsStyling: false
						});
						$.each(response.error, function(key, value) {
							$('input[name="' + key + '"]').parent().find('.error').html(value);
						});
					}
				},
				error: function(xhr, status, error) {
					btn.prop('disabled', false).html(btnText);
					console.error('AJAX Error:', error);
					swal({
						title: '<?=translate('error')?>',
						text: '<?=translate('an_error_occurred_please_try_again')?>',
						type: 'error',
						confirmButtonClass: 'btn btn-default',
						buttonsStyling: false
					});
				}
			});
		});

		// Edit term button
		$('.edit-term').on('click', function() {
			var termId = $(this).data('term-id');
			var termName = $(this).data('term-name');
			var startDate = $(this).data('start-date');
			var endDate = $(this).data('end-date');
			var totalWeeks = $(this).data('total-weeks');
			var branchId = $(this).data('branch-id');

			$('.error').html("");
			$('#edit_term_id').val(termId);
			$('#edit_term_name').val(termName);
			$('#edit_start_date').val(startDate);
			$('#edit_end_date').val(endDate);
			$('#edit_total_weeks').val(totalWeeks);
			$('#edit_branch_id').val(branchId);

			if (!totalWeeks && startDate && endDate) {
				var weeks = calculateWeeks(startDate, endDate);
				if (weeks !== '') {
					$('#edit_total_weeks').val(weeks);
				}
			}

			mfp_modal('#editTermModal');
		});

		// Edit session modal
		$('.editModal').on('click', function() {
			var id = $(this).data('id');
			var session = $(this).data('session');
			$('.error').html("");
			$('#schoolyear_id').val(id);
			$('#session').val(session);
			mfp_modal('#modal');
		});

		// Activate term
		$('.activate-term').on('click', function() {
			var termId = $(this).data('term-id');
			var branchId = $(this).data('branch-id');
			var btn = $(this);

			if (!branchId) {
				swal({
					title: '<?=translate('error')?>',
					text: '<?=translate('branch_id_not_found')?>',
					type: 'error',
					confirmButtonClass: 'btn btn-default',
					buttonsStyling: false
				});
				return;
			}

			swal({
				title: '<?=translate('activate_term')?>',
				text: '<?=translate('are_you_sure_you_want_to_activate_this_term')?>?',
				type: 'warning',
				showCancelButton: true,
				confirmButtonClass: 'btn btn-default',
				cancelButtonClass: 'btn btn-default',
				confirmButtonText: '<?=translate('yes_continue')?>',
				cancelButtonText: '<?=translate('cancel')?>',
				buttonsStyling: false
			}).then((result) => {
				if (result.value) {
					btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> <?=translate('processing')?>');

					$.ajax({
						url: '<?=base_url('sessions/activate_term')?>',
						type: 'POST',
						dataType: 'json',
						data: {
							term_id: termId,
							branch_id: branchId,
							<?=$this->security->get_csrf_token_name()?>: '<?=$this->security->get_csrf_hash()?>'
						},
						success: function(response) {
							if (response.status === 'success') {
								location.reload();
							} else {
								swal({
									title: '<?=translate('error')?>',
									text: response.message,
									type: 'error',
									confirmButtonClass: 'btn btn-default',
									buttonsStyling: false
								});
								btn.prop('disabled', false).html('<i class="fas fa-check"></i> <?=translate('activate')?>');
							}
						},
						error: function(xhr, status, error) {
							console.error('AJAX Error:', error);
							console.error('Response:', xhr.responseText);
							swal({
								title: '<?=translate('error')?>',
								text: '<?=translate('an_error_occurred_please_try_again')?>',
								type: 'error',
								confirmButtonClass: 'btn btn-default',
								buttonsStyling: false
							});
							btn.prop('disabled', false).html('<i class="fas fa-check"></i> <?=translate('activate')?>');
						}
					});
				}
			});
		});

		// View terms for a session
		$('.viewTerms').on('click', function() {
			var sessionId = $(this).data('session-id');
			var sessionName = $(this).data('session-name');
			var branchId = <?=isset($branch_id) ? $branch_id : 'null'?>;

			if (!branchId) {
				swal({
					title: '<?=translate('error')?>',
					text: '<?=translate('branch_id_not_found')?>',
					type: 'error',
					confirmButtonClass: 'btn btn-default',
					buttonsStyling: false
				});
				return;
			}

			$('#termsModalTitle').text('<?=translate('terms_for')?> ' + sessionName);
			$('#termsModalBody').html('<div style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin" style="font-size: 48px; color: #2196F3;"></i><p style="margin-top: 15px; color: #666;"><?=translate('loading')?>...</p></div>');

			mfp_modal('#termsModal');

			$.ajax({
				url: '<?=base_url('sessions/get_session_terms_ajax')?>',
				type: 'POST',
				dataType: 'json',
				data: {
					session_id: sessionId,
					branch_id: branchId,
					<?=$this->security->get_csrf_token_name()?>: '<?=$this->security->get_csrf_hash()?>'
				},
				success: function(response) {
					console.log('Terms Response:', response);
					if (response.status === 'success' && response.terms && response.terms.length > 0) {
						var html = '<div class="table-responsive"><table class="table table-bordered table-hover">';
						html += '<thead><tr>';
						html += '<th><?=translate('term_name')?></th>';
						html += '<th><?=translate('start_date')?></th>';
						html += '<th><?=translate('end_date')?></th>';
						html += '<th><?=translate('weeks')?></th>';
						html += '<th><?=translate('status')?></th>';
						html += '</tr></thead><tbody>';

						$.each(response.terms, function(index, term) {
							html += '<tr>';
							html += '<td><strong>' + term.term_name + '</strong></td>';
							html += '<td>' + term.start_date + '</td>';
							html += '<td>' + term.end_date + '</td>';
							html += '<td>' + term.total_weeks + ' <?=translate('weeks')?></td>';
							html += '<td>';
							if (term.is_active == 1) {
								html += '<span class="label label-success"><i class="fas fa-check-circle"></i> <?=translate('active')?></span>';
							} else {
								html += '<span class="label label-default"><i class="fas fa-circle"></i> <?=translate('inactive')?></span>';
							}
							html += '</td>';
							html += '</tr>';
						});
						html += '</tbody></table></div>';
						$('#termsModalBody').html(html);
					} else {
						var errorMsg = response.message || '<?=translate('no_terms_found')?>';
						$('#termsModalBody').html('<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> ' + errorMsg + '</div>');
					}
				},
				error: function(xhr, status, error) {
					console.error('AJAX Error:', error);
					console.error('Response:', xhr.responseText);
					$('#termsModalBody').html('<div class="alert alert-danger"><i class="fas fa-times-circle"></i> <?=translate('failed_to_load_terms')?><br><small>Error: ' + error + '</small></div>');
				}
			});
		});

		// Inline date editing functionality
		$('.edit-date-btn').on('click', function() {
			var cell = $(this).closest('.editable-date');
			var displaySpan = cell.find('.date-display');
			var inputField = cell.find('.date-input');
			var editBtn = $(this);

			if (inputField.is(':visible')) {
				// Save mode
				var newValue = inputField.val();
				var termId = cell.data('term-id');
				var field = cell.data('field');

				if (!newValue) {
					swal({
						title: '<?=translate('error')?>',
						text: '<?=translate('please_select_a_date')?>',
						type: 'error',
						confirmButtonClass: 'btn btn-default',
						buttonsStyling: false
					});
					return;
				}

				// Save via AJAX
				editBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

				$.ajax({
					url: '<?=base_url('sessions/quick_adjust_term')?>',
					type: 'POST',
					dataType: 'json',
					data: {
						term_id: termId,
						field: field,
						value: newValue,
						<?=$this->security->get_csrf_token_name()?>: '<?=$this->security->get_csrf_hash()?>'
					},
					success: function(response) {
						if (response.status === 'success') {
							// Update display
							var dateObj = new Date(newValue);
							var options = { year: 'numeric', month: 'short', day: 'numeric' };
							displaySpan.text(dateObj.toLocaleDateString('en-US', options));

							// Update total weeks if returned
							if (response.total_weeks) {
								cell.closest('tr').find('.total-weeks-cell').html(response.total_weeks + ' <?=translate('weeks')?>');
							}

							// Toggle back to view mode
							inputField.hide();
							displaySpan.show();
							editBtn.prop('disabled', false).html('<i class="fas fa-pencil-alt"></i>');

							swal({
								title: '<?=translate('success')?>',
								text: response.message,
								type: 'success',
								timer: 2000,
								showConfirmButton: false
							});
						} else {
							swal({
								title: '<?=translate('error')?>',
								text: response.message,
								type: 'error',
								confirmButtonClass: 'btn btn-default',
								buttonsStyling: false
							});
							editBtn.prop('disabled', false).html('<i class="fas fa-pencil-alt"></i>');
						}
					},
					error: function(xhr, status, error) {
						console.error('AJAX Error:', error);
						swal({
							title: '<?=translate('error')?>',
							text: '<?=translate('an_error_occurred_please_try_again')?>',
							type: 'error',
							confirmButtonClass: 'btn btn-default',
							buttonsStyling: false
						});
						editBtn.prop('disabled', false).html('<i class="fas fa-pencil-alt"></i>');
					}
				});
			} else {
				// Edit mode
				displaySpan.hide();
				inputField.show().focus();
				editBtn.html('<i class="fas fa-save"></i>');
			}
		});

		// Cancel edit on ESC key
		$('.date-input').on('keydown', function(e) {
			if (e.key === 'Escape') {
				var cell = $(this).closest('.editable-date');
				$(this).hide();
				cell.find('.date-display').show();
				cell.find('.edit-date-btn').html('<i class="fas fa-pencil-alt"></i>');
			}
		});

		// Save on Enter key
		$('.date-input').on('keydown', function(e) {
			if (e.key === 'Enter') {
				$(this).closest('.editable-date').find('.edit-date-btn').click();
			}
		});
	});
</script>

<style>
	.editable-date {
		position: relative;
	}
	.editable-date .edit-date-btn {
		margin-left: 8px;
		padding: 2px 6px;
		font-size: 11px;
	}
	.editable-date .date-input {
		display: inline-block;
		width: auto;
		font-size: 13px;
		padding: 4px 8px;
	}
	.editable-date .date-display {
		display: inline-block;
		min-width: 100px;
	}
	.total-weeks-cell {
		font-weight: bold;
		color: #2196F3;
	}
</style>
