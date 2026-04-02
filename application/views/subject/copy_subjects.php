<section class="panel">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#copy" data-toggle="tab"><i class="fas fa-copy"></i> <?=translate('copy_subjects')?></a>
			</li>
		</ul>
		<div class="tab-content">
			<div id="copy" class="tab-pane active">
				<div class="panel-body">
					<div class="row">
						<div class="col-md-8 col-md-offset-2">
							<div class="alert alert-info">
								<i class="fas fa-info-circle"></i>
								<strong>How it works:</strong> Select a previous session to copy all its subject-class assignments into the current active session. 
								Existing assignments will not be duplicated.
							</div>

							<!-- Source Session Selection -->
							<div class="form-group">
								<label class="control-label"><strong><?=translate('source')?> <?=translate('session')?></strong> <span class="required">*</span></label>
								<select name="source_session_id" id="source_session_id" class="form-control" data-plugin-selectTwo data-width="100%">
									<option value=""><?=translate('select')?></option>
									<?php foreach ($sessions as $session): ?>
										<?php if ($session['id'] != $current_session_id): ?>
											<option value="<?=$session['id']?>"><?=html_escape($session['school_year'])?></option>
										<?php endif; ?>
									<?php endforeach; ?>
								</select>
							</div>

							<!-- Target Session (Read-Only) -->
							<div class="form-group">
								<label class="control-label"><strong><?=translate('target')?> <?=translate('session')?> (<?=translate('current')?>)</strong></label>
								<?php 
									$current_session_name = '';
									foreach ($sessions as $s) {
										if ($s['id'] == $current_session_id) {
											$current_session_name = $s['school_year'];
											break;
										}
									}
								?>
								<input type="text" class="form-control" value="<?=html_escape($current_session_name)?>" readonly disabled />
							</div>

							<!-- Preview Button -->
							<div class="form-group">
								<button type="button" id="btn_preview" class="btn btn-info btn-block" onclick="previewSubjects()">
									<i class="fas fa-search"></i> <?=translate('preview')?> <?=translate('subject')?> Assignments
								</button>
							</div>

							<!-- Preview Results -->
							<div id="preview_area" style="display:none;">
								<hr>
								<h4><i class="fas fa-list"></i> Assignments to be Copied</h4>
								<div id="preview_content"></div>
								<hr>
								<div class="form-group">
									<button type="button" id="btn_copy" class="btn btn-success btn-lg btn-block" onclick="executeCopy()">
										<i class="fas fa-copy"></i> Copy All Subjects to Current Session
									</button>
								</div>
							</div>

							<!-- Result Message -->
							<div id="result_area" style="display:none;">
								<div class="alert alert-success" id="result_message"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<script type="text/javascript">
function previewSubjects() {
	var sourceSessionId = $('#source_session_id').val();
	if (!sourceSessionId) {
		alert('Please select a source session.');
		return;
	}

	var btn = $('#btn_preview');
	btn.html('<i class="fas fa-spinner fa-spin"></i> Loading...').prop('disabled', true);
	$('#preview_area').hide();
	$('#result_area').hide();

	$.ajax({
		url: "<?=base_url('subject/preview_copy_subjects')?>",
		type: 'POST',
		dataType: 'json',
		data: { source_session_id: sourceSessionId },
		success: function(response) {
			btn.html('<i class="fas fa-search"></i> <?=translate("preview")?> <?=translate("subject")?> Assignments').prop('disabled', false);

			if (response.status === 'success') {
				var html = '<div class="table-responsive"><table class="table table-bordered table-striped table-condensed">';
				html += '<thead><tr><th>#</th><th><?=translate("class")?></th><th><?=translate("section")?></th><th><?=translate("subject")?></th></tr></thead><tbody>';
				
				var count = 1;
				$.each(response.data, function(key, group) {
					html += '<tr>';
					html += '<td>' + count + '</td>';
					html += '<td><strong>' + group.class_name + '</strong></td>';
					html += '<td>' + group.section_name + '</td>';
					html += '<td>';
					$.each(group.subjects, function(i, subj) {
						html += '<span class="label label-default mr-xs mb-xs" style="display:inline-block;margin:2px;">' + subj + '</span> ';
					});
					html += '</td>';
					html += '</tr>';
					count++;
				});

				html += '</tbody></table></div>';
				html += '<p class="text-muted"><strong>Total:</strong> ' + response.total + ' subject assignment(s) found.</p>';

				$('#preview_content').html(html);
				$('#preview_area').slideDown();
			} else if (response.status === 'empty') {
				$('#preview_content').html('<div class="alert alert-warning">' + response.message + '</div>');
				$('#preview_area').slideDown();
				$('#btn_copy').hide();
			} else {
				alert(response.message || 'An error occurred.');
			}
		},
		error: function() {
			btn.html('<i class="fas fa-search"></i> <?=translate("preview")?> <?=translate("subject")?> Assignments').prop('disabled', false);
			alert('An error occurred while loading the preview.');
		}
	});
}

function executeCopy() {
	var sourceSessionId = $('#source_session_id').val();
	if (!sourceSessionId) {
		alert('Please select a source session.');
		return;
	}

	if (!confirm('Are you sure you want to copy all subject assignments to the current session?')) {
		return;
	}

	var btn = $('#btn_copy');
	btn.html('<i class="fas fa-spinner fa-spin"></i> Copying...').prop('disabled', true);

	$.ajax({
		url: "<?=base_url('subject/copy_subjects_save')?>",
		type: 'POST',
		dataType: 'json',
		data: { source_session_id: sourceSessionId },
		success: function(response) {
			btn.html('<i class="fas fa-copy"></i> Copy All Subjects to Current Session').prop('disabled', false);

			if (response.status === 'success') {
				$('#preview_area').slideUp();
				$('#result_message').html('<i class="fas fa-check-circle"></i> ' + response.message);
				$('#result_area').slideDown();
				
				// Refresh after 2 seconds
				setTimeout(function() {
					window.location.href = "<?=base_url('subject/class_assign')?>";
				}, 2000);
			} else {
				alert(response.message || 'An error occurred.');
			}
		},
		error: function() {
			btn.html('<i class="fas fa-copy"></i> Copy All Subjects to Current Session').prop('disabled', false);
			alert('An error occurred while copying subjects.');
		}
	});
}
</script>
