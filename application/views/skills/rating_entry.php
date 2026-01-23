<style>
	.input-sm {
		padding: 6px 1px !important;
	}

	/* Table scrolling with fixed first columns */
	#skills-rating-table {
		white-space: nowrap;
	}

	#skills-rating-table th:nth-child(1),
	#skills-rating-table td:nth-child(1) {
		position: sticky;
		left: 0;
		min-width: 40px;
		background: white;
		z-index: 3;
	}

	#skills-rating-table th:nth-child(2),
	#skills-rating-table td:nth-child(2) {
		position: sticky;
		left: 40px;
		min-width: 180px;
		background: white;
		z-index: 2;
	}

	/* Rating buttons */
	.rating-btns {
		display: flex;
		gap: 3px;
		justify-content: center;
		flex-wrap: nowrap;
	}

	.rating-btns label {
		margin: 0;
		cursor: pointer;
	}

	.rating-btns input[type="radio"] {
		display: none;
	}

	.rating-btn {
		display: inline-block;
		padding: 4px 8px;
		border: 1px solid #ddd;
		background: #fff;
		border-radius: 3px;
		font-size: 12px;
		font-weight: bold;
		transition: all 0.2s;
		min-width: 30px;
		text-align: center;
	}

	.rating-btns input[type="radio"]:checked + .rating-btn {
		background: #0088cc;
		color: white;
		border-color: #0088cc;
	}

	.rating-btn:hover {
		background: #e6f2ff;
		border-color: #0088cc;
	}

	.rating-btns input[type="radio"]:checked + .rating-btn:hover {
		background: #006ba3;
	}

	/* Category separators */
	.category-separator {
		border-right: 3px solid #333 !important;
	}

	.category-affective {
		background-color: #f0f8ff !important;
	}

	.category-psychomotor {
		background-color: #f0fff0 !important;
	}

	.category-cognitive {
		background-color: #fffaf0 !important;
	}
</style>

<?php $widget = (is_superadmin_loggedin() ? 2 : 3); ?>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<?php echo form_open($this->uri->uri_string(), array('class' => 'validate', 'id' => 'filter-form')); ?>
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-filter"></i> <?= translate('select_ground') ?></h4>
			</header>
			<div class="panel-body">
				<div class="row mb-sm">
					<?php if (is_superadmin_loggedin()): ?>
						<div class="col-md-2 mb-sm">
							<div class="form-group">
								<label class="control-label"><?= translate('branch') ?> <span class="required">*</span></label>
								<?php
								$arrayBranch = $this->app_lib->getSelectList('branch');
								echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id', $branch_id), "class='form-control' id='branch_id'
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
								?>
							</div>
						</div>
					<?php endif; ?>
					<div class="col-md-<?php echo $widget; ?> mb-sm">
						<div class="form-group">
							<label class="control-label"><?= translate('exam') ?> <span class="required">*</span></label>
							<?php
							$arrayExam = array("" => translate('select'));
							$exams = $this->db->get_where('exam', array('branch_id' => $branch_id, 'session_id' => get_session_id()))->result();
							foreach ($exams as $row) {
								$arrayExam[$row->id] = $this->application_model->exam_name_by_id($row->id);
							}
							echo form_dropdown("exam_id", $arrayExam, set_value('exam_id'), "class='form-control' id='exam_id' required
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
						</div>
					</div>
					<div class="col-md-<?php echo $widget; ?> mb-sm">
						<div class="form-group">
							<label class="control-label"><?= translate('class') ?> <span class="required">*</span></label>
							<?php
							$arrayClass = $this->app_lib->getClass($branch_id);
							echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id'
								onchange='getSectionByClass(this.value,0)' required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
						</div>
					</div>
					<div class="col-md-<?php echo $widget; ?> mb-sm">
						<div class="form-group">
							<label class="control-label"><?= translate('section') ?> <span class="required">*</span></label>
							<?php
							$arraySection = $this->app_lib->getSections(set_value('class_id'), false);
							echo form_dropdown("section_id", $arraySection, set_value('section_id'), "class='form-control' id='section_id' required
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
						</div>
					</div>
					<div class="col-md-<?php echo $widget; ?> mb-sm">
						<div class="form-group">
							<label class="control-label"><?= translate('class_level') ?> <span class="required">*</span></label>
							<?php
							$arrayLevel = array(
								'' => translate('select'),
								'primary' => 'Primary',
								'junior' => 'Junior',
								'senior' => 'Senior',
							);
							echo form_dropdown("class_level", $arrayLevel, set_value('class_level'), "class='form-control' id='class_level' required
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
						</div>
					</div>
				</div>
			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-offset-10 col-md-2">
						<button type="button" id="load-skills-btn" class="btn btn btn-default btn-block">
							<i class="fas fa-filter"></i> <?= translate('load_students') ?>
						</button>
					</div>
				</div>
			</footer>
			<?php echo form_close(); ?>
		</section>

		<!-- Skills Rating Entry Section -->
		<section class="panel" id="rating-section" style="display:none;">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-star"></i> <?= translate('skills_rating_entry') ?></h4>
			</header>
			<div class="panel-body">
				<div class="alert alert-info">
					<i class="fas fa-info-circle"></i> Rate each student on the skills below. Use the rating scale defined in your system.
				</div>

				<!-- Legend -->
				<div class="mb-lg" id="rating-legend"></div>

				<!-- Skills Rating Form -->
				<?php echo form_open('skills/save_ratings', array('class' => 'frm-submit-rating', 'id' => 'rating-form')); ?>
				<input type="hidden" name="exam_id" id="hidden_exam_id" value="">
				<input type="hidden" name="class_id" id="hidden_class_id" value="">
				<input type="hidden" name="section_id" id="hidden_section_id" value="">

				<div class="table-responsive" style="overflow-x: auto; max-width: 100%;">
					<table class="table table-bordered table-hover table-condensed" id="skills-rating-table" style="min-width: 100%;">
						<thead id="rating-table-head">
							<!-- Will be populated dynamically -->
						</thead>
						<tbody id="rating-table-body">
							<!-- Will be populated dynamically -->
						</tbody>
					</table>
				</div>
				<?php echo form_close(); ?>
			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-12">
						<button type="button" id="save-ratings-btn" class="btn btn-success btn-lg">
							<i class="fas fa-save"></i> <?= translate('save_all_ratings') ?>
						</button>
						<button type="button" id="clear-form-btn" class="btn btn-default btn-lg">
							<i class="fas fa-times"></i> <?= translate('clear') ?>
						</button>
					</div>
				</div>
			</footer>
		</section>
	</div>
</div>

<script type="text/javascript">
	var skillsData = [];
	var studentsData = [];
	var ratingsData = [];

	// Load students and skills when filter button is clicked
	$('#load-skills-btn').on('click', function() {
		var exam_id = $('#exam_id').val();
		var class_id = $('#class_id').val();
		var section_id = $('#section_id').val();
		var class_level = $('#class_level').val();
		var branch_id = $('#branch_id').val() || '<?= $branch_id ?>';

		if (!exam_id || !class_id || !section_id || !class_level) {
			swal({
				toast: true,
				position: 'top-end',
				type: 'warning',
				title: '<?= translate('alert') ?>',
				text: 'Please select all required fields',
				showConfirmButton: false,
				timer: 3000
			});
			return;
		}

		// Show loading
		$(this).html('<i class="fas fa-spinner fa-spin"></i> Loading...').prop('disabled', true);

		// STEP 1: Load ratings scale first (needed for building dropdowns)
		loadRatingsScale(branch_id, function() {
			console.log('Ratings loaded:', ratingsData);

			// STEP 2: Load students after ratings are ready
			$.ajax({
				url: '<?= base_url("skills/getStudents") ?>',
				type: 'POST',
				data: {
					class_id: class_id,
					section_id: section_id,
					exam_id: exam_id,
					branch_id: branch_id
				},
				dataType: 'json',
				success: function(response) {
					console.log('Response from server:', response);

					var students = response.students || response;
					var debug = response.debug;

					if (debug) {
						console.log('Debug info:', debug);
					}

					studentsData = students;

					if (!students || students.length === 0) {
						var message = 'No students found in the selected class/section';
						if (debug && debug.message) {
							message = debug.message;
							console.log('Students by session:', debug.students_by_session);
						}
						swal({
							type: 'warning',
							title: '<?= translate('alert') ?>',
							text: message,
							confirmButtonClass: 'btn btn-default swal2-btn-default',
							buttonsStyling: false
						});
						$('#load-skills-btn').html('<i class="fas fa-filter"></i> <?= translate('load_students') ?>').prop('disabled', false);
						return;
					}

					// STEP 3: Load skills based on class level
					loadSkills(class_level, branch_id, function() {
						console.log('Skills loaded:', skillsData);

						// STEP 4: Build the rating table (now we have ratings, students, and skills)
						buildRatingTable(students);

						// Set hidden fields
						$('#hidden_exam_id').val(exam_id);
						$('#hidden_class_id').val(class_id);
						$('#hidden_section_id').val(section_id);

						// Show rating section
						$('#rating-section').slideDown();
						$('#load-skills-btn').html('<i class="fas fa-filter"></i> <?= translate('load_students') ?>').prop('disabled', false);

						// Scroll to rating section
						$('html, body').animate({
							scrollTop: $('#rating-section').offset().top - 100
						}, 500);
					});
				},
				error: function(xhr, status, error) {
					console.error('Error loading students:', error, xhr.responseText);
					swal({
						type: 'error',
						title: '<?= translate('error') ?>',
						text: 'Error loading students: ' + error,
						confirmButtonClass: 'btn btn-default swal2-btn-default',
						buttonsStyling: false
					});
					$('#load-skills-btn').html('<i class="fas fa-filter"></i> <?= translate('load_students') ?>').prop('disabled', false);
				}
			});
		});
	});

	// Load ratings scale
	function loadRatingsScale(branch_id, callback) {
		$.ajax({
			url: '<?= base_url("skills/getRatingsScale") ?>',
			type: 'POST',
			data: {
				branch_id: branch_id
			},
			dataType: 'json',
			success: function(ratings) {
				ratingsData = ratings;
				displayRatingLegend(ratings);
				if (callback) callback();
			},
			error: function(xhr, status, error) {
				console.error('Error loading ratings scale:', error);
				swal({
					type: 'error',
					title: '<?= translate('error') ?>',
					text: 'Error loading rating scale. Please try again.',
					confirmButtonClass: 'btn btn-default swal2-btn-default',
					buttonsStyling: false
				});
				$('#load-skills-btn').html('<i class="fas fa-filter"></i> <?= translate('load_students') ?>').prop('disabled', false);
			}
		});
	}

	// Display rating legend
	function displayRatingLegend(ratings) {
		var html = '<div class="panel panel-default"><div class="panel-body"><strong>Rating Scale:</strong> ';
		ratings.forEach(function(rating) {
			html += '<span class="badge badge-primary mr-sm" style="font-size:13px;margin-right:10px;">' +
				rating.label + ' = ' + rating.description + '</span>';
		});
		html += '</div></div>';
		$('#rating-legend').html(html);
	}

	// Load skills for class level
	function loadSkills(class_level, branch_id, callback) {
		// For now, we'll use a simple approach - fetch all active skills
		// In production, you'd filter by class_level
		$.ajax({
			url: '<?= base_url("skills/getSkillsByLevel") ?>',
			type: 'POST',
			data: {
				class_level: class_level,
				branch_id: branch_id
			},
			dataType: 'json',
			success: function(skills) {
				skillsData = skills;
				if (callback) callback();
			},
			error: function() {
				// Fallback - use sample data
				skillsData = [];
				if (callback) callback();
			}
		});
	}

	// Build rating table
	function buildRatingTable(students) {
		if (students.length === 0) {
			$('#rating-table-body').html('<tr><td colspan="10" class="text-center">No students found</td></tr>');
			return;
		}

		// Validate that we have ratings data
		if (!ratingsData || ratingsData.length === 0) {
			console.error('Rating scale data not loaded yet!');
			swal({
				type: 'error',
				title: '<?= translate('error') ?>',
				text: 'Rating scale not loaded. Please try again.',
				confirmButtonClass: 'btn btn-default swal2-btn-default',
				buttonsStyling: false
			});
			return;
		}

		// Validate that we have skills data
		if (!skillsData || skillsData.length === 0) {
			console.error('Skills data not loaded yet!');
			swal({
				type: 'warning',
				title: '<?= translate('alert') ?>',
				text: 'No skills found for this class level. Please configure skills first.',
				confirmButtonClass: 'btn btn-default swal2-btn-default',
				buttonsStyling: false
			});
			return;
		}

		// Build table header
		var headerHtml = '<tr>';
		headerHtml += '<th width="30">#</th>';
		headerHtml += '<th width="180">Student Name</th>';

		// Group skills by category
		var groupedSkills = {};
		skillsData.forEach(function(skill) {
			if (!groupedSkills[skill.category_type]) {
				groupedSkills[skill.category_type] = {
					name: skill.category_name,
					items: []
				};
			}
			groupedSkills[skill.category_type].items.push(skill);
		});

		// Add skill columns with separators between categories
		var categoryIndex = 0;
		var categoryKeys = Object.keys(groupedSkills);
		for (var type in groupedSkills) {
			var items = groupedSkills[type].items;
			items.forEach(function(skill, index) {
				var isSeparator = (index === items.length - 1 && categoryIndex < categoryKeys.length - 1);
				var separatorClass = isSeparator ? ' category-separator' : '';
				var categoryClass = ' category-' + type;
				headerHtml += '<th width="100" class="text-center' + categoryClass + separatorClass + '" style="writing-mode:vertical-rl;transform:rotate(180deg);">' +
					skill.item_name + '</th>';
			});
			categoryIndex++;
		}

		// Add remarks columns at the end
		headerHtml += '<th width="250">Teacher\'s Remark</th>';
		headerHtml += '<th width="250">Head Teacher\'s Remark</th>';
		headerHtml += '</tr>';
		$('#rating-table-head').html(headerHtml);

		// Load existing ratings first, then build table
		loadExistingRatings(function(existingRatings) {
			// Build table body
			var bodyHtml = '';
			students.forEach(function(student, index) {
				bodyHtml += '<tr>';
				bodyHtml += '<td>' + (index + 1) + '</td>';
				bodyHtml += '<td>' + student.fullname + '</td>';
				bodyHtml += '<input type="hidden" name="enroll_ids[' + student.student_id + ']" value="' + student.id + '">';

				// Get existing ratings for this student
				var studentRatings = existingRatings[student.student_id] || {};

				// Add rating buttons for each skill with category separators
				var skillIndex = 0;
				for (var type in groupedSkills) {
					var items = groupedSkills[type].items;
					items.forEach(function(skill, itemIndex) {
						var existingRating = studentRatings[skill.id];
						var isSeparator = (itemIndex === items.length - 1 && type !== Object.keys(groupedSkills)[Object.keys(groupedSkills).length - 1]);
						var separatorClass = isSeparator ? ' category-separator' : '';
						var categoryClass = ' category-' + type;
						bodyHtml += '<td class="' + categoryClass + separatorClass + '">';
						bodyHtml += '<div class="rating-btns">';
						ratingsData.forEach(function(rating) {
							var checked = (existingRating == rating.id) ? 'checked' : '';
							bodyHtml += '<label>';
							bodyHtml += '<input type="radio" name="ratings[' + student.student_id + '][' + skill.id + ']" value="' + rating.id + '" ' + checked + '>';
							bodyHtml += '<span class="rating-btn">' + rating.label + '</span>';
							bodyHtml += '</label>';
						});
						bodyHtml += '</div>';
						bodyHtml += '</td>';
						skillIndex++;
					});
				}

				// Add remarks columns at the end
				var existingTeacherRemarks = existingRatings.teacher_remarks && existingRatings.teacher_remarks[student.student_id] ? existingRatings.teacher_remarks[student.student_id] : '';
				var existingHeadTeacherRemarks = existingRatings.head_teacher_remarks && existingRatings.head_teacher_remarks[student.student_id] ? existingRatings.head_teacher_remarks[student.student_id] : '';

				bodyHtml += '<td><textarea name="teacher_remarks[' + student.student_id + ']" class="form-control input-sm" rows="2" placeholder="Teacher\'s Remark" style="min-width: 250px;">' + existingTeacherRemarks + '</textarea></td>';
				bodyHtml += '<td><textarea name="head_teacher_remarks[' + student.student_id + ']" class="form-control input-sm" rows="2" placeholder="Head Teacher\'s Remark" style="min-width: 250px;">' + existingHeadTeacherRemarks + '</textarea></td>';

				bodyHtml += '</tr>';
			});
			$('#rating-table-body').html(bodyHtml);
		});
	}

	// Load existing ratings for students
	function loadExistingRatings(callback) {
		var exam_id = $('#hidden_exam_id').val();
		var class_id = $('#hidden_class_id').val();
		var section_id = $('#hidden_section_id').val();

		if (!exam_id || !class_id || !section_id) {
			if (callback) callback({});
			return;
		}

		$.ajax({
			url: '<?= base_url("skills/getExistingRatingsForClass") ?>',
			type: 'POST',
			data: {
				exam_id: exam_id,
				class_id: class_id,
				section_id: section_id
			},
			dataType: 'json',
			success: function(data) {
				console.log('Existing ratings loaded:', data);
				if (callback) callback(data || {});
			},
			error: function() {
				console.log('No existing ratings found or error loading');
				if (callback) callback({});
			}
		});
	}

	// Save ratings
	$('#save-ratings-btn').on('click', function() {
		var $btn = $(this);
		$btn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);

		var formData = $('#rating-form').serialize();
		console.log('Saving ratings...', formData);

		$.ajax({
			url: '<?= base_url("skills/save_ratings") ?>',
			type: 'POST',
			data: formData,
			dataType: 'json',
			success: function(data) {
				console.log('Save response:', data);
				if (data.status == 'success') {
					swal({
						type: 'success',
						title: '<?= translate('successfully') ?>',
						text: data.message || 'Skills ratings saved successfully!',
						confirmButtonClass: 'btn btn-default swal2-btn-default',
						buttonsStyling: false
					}).then((result) => {
						$('#rating-section').slideUp();
						$('#filter-form')[0].reset();
					});
				} else {
					swal({
						type: 'error',
						title: '<?= translate('error') ?>',
						text: data.error || 'An error occurred',
						confirmButtonClass: 'btn btn-default swal2-btn-default',
						buttonsStyling: false
					});
					if (data.debug) {
						console.log('Debug info:', data.debug);
					}
				}
				$btn.html('<i class="fas fa-save"></i> <?= translate('save_all_ratings') ?>').prop('disabled', false);
			},
			error: function(xhr, status, error) {
				console.error('Error saving ratings:', error, xhr.responseText);
				swal({
					type: 'error',
					title: '<?= translate('error') ?>',
					text: 'Error saving ratings: ' + error,
					confirmButtonClass: 'btn btn-default swal2-btn-default',
					buttonsStyling: false
				});
				$btn.html('<i class="fas fa-save"></i> <?= translate('save_all_ratings') ?>').prop('disabled', false);
			}
		});
	});

	// Clear form
	$('#clear-form-btn').on('click', function() {
		swal({
			title: '<?= translate('are_you_sure') ?>',
			text: 'Are you sure you want to clear all ratings?',
			type: 'warning',
			showCancelButton: true,
			confirmButtonClass: 'btn btn-default swal2-btn-default',
			cancelButtonClass: 'btn btn-default swal2-btn-default',
			confirmButtonText: '<?= translate('yes_continue') ?>',
			cancelButtonText: '<?= translate('cancel') ?>',
			buttonsStyling: false
		}).then((result) => {
			if (result.value) {
				$('#rating-form')[0].reset();
				swal({
					toast: true,
					position: 'top-end',
					type: 'success',
					title: 'Form cleared',
					showConfirmButton: false,
					timer: 2000
				});
			}
		});
	});

	// Branch change handler (for superadmin) - using existing global functions from app.fn.js
	$('#branch_id').on('change', function() {
		var branchID = $(this).val();
		getClassByBranch(branchID);
		getExamByBranch(branchID);
	});
</script>

<style>
	/* Additional table styles */
	#skills-rating-table th {
		text-align: center;
		vertical-align: middle;
	}

	#skills-rating-table tbody td {
		vertical-align: middle;
		white-space: nowrap;
	}

	.badge-primary {
		background-color: #0088cc;
		padding: 5px 10px;
		font-size: 13px;
	}

	/* Sticky header for better scrolling */
	#skills-rating-table thead th {
		position: sticky;
		top: 0;
		background: white;
		z-index: 10;
		box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
	}

	/* Extra z-index for sticky header cells that are also sticky horizontally */
	#skills-rating-table thead th:nth-child(1) {
		z-index: 13;
	}

	#skills-rating-table thead th:nth-child(2) {
		z-index: 12;
	}
</style>