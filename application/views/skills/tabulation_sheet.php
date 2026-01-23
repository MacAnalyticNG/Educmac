<?php
$widget = (is_superadmin_loggedin() ? 2 : 3);
$branch = $this->db->where('id',$branch_id)->get('branch')->row_array();
?>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<?php echo form_open('skills/tabulation_sheet', array('class' => 'validate')); ?>
			<header class="panel-heading">
				<h4 class="panel-title"><?=translate('select_ground')?></h4>
			</header>
			<div class="panel-body">
				<div class="row mb-sm">
				<?php if (is_superadmin_loggedin() ): ?>
					<div class="col-md-3 mb-sm">
						<div class="form-group">
							<label class="control-label"><?=translate('branch')?> <span class="required">*</span></label>
							<?php
								$arrayBranch = $this->app_lib->getSelectList('branch');
								echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='branch_id' required
								data-plugin-selectTwo data-width='100%'");
							?>
						</div>
					</div>
				<?php endif; ?>
					<div class="col-md-<?=$widget?> mb-sm">
						<div class="form-group">
							<label class="control-label"><?=translate('academic_year')?> <span class="required">*</span></label>
							<?php
								$arrayYear = array("" => translate('select'));
								$years = $this->db->get('schoolyear')->result();
								foreach ($years as $year){
									$arrayYear[$year->id] = $year->school_year;
								}
								echo form_dropdown("session_id", $arrayYear, set_value('session_id', get_session_id()), "class='form-control' required
								data-plugin-selectTwo data-width='100%'");
							?>
						</div>
					</div>
					<div class="col-md-<?=$widget?> mb-sm">
						<div class="form-group">
							<label class="control-label"><?=translate('exam')?> <span class="required">*</span></label>
							<?php

								if(!empty($branch_id)){
									$arrayExam = array("" => translate('select'));
									$exams = $this->db->get_where('exam', array('branch_id' => $branch_id,'session_id' => get_session_id()))->result();
									foreach ($exams as $exam){
										$arrayExam[$exam->id] = $this->application_model->exam_name_by_id($exam->id);
									}
								} else {
									$arrayExam = array("" => translate('select_branch_first'));
								}
								echo form_dropdown("exam_id", $arrayExam, set_value('exam_id'), "class='form-control' id='exam_id' required
								data-plugin-selectTwo data-width='100%'");
							?>
						</div>
					</div>

					<div class="col-md-3 mb-sm">
						<div class="form-group">
							<label class="control-label"><?=translate('class')?> <span class="required">*</span></label>
							<?php
								$arrayClass = $this->app_lib->getClass($branch_id);
								echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)'
								required data-plugin-selectTwo data-width='100%'");
							?>
						</div>
					</div>

					<div class="col-md-<?=$widget?>">
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
			<div class="panel-footer">
				<div class="row">
					<div class="col-md-offset-10 col-md-2">
						<button type="submit" name="submit" value="search" class="btn btn-default btn-block"><i class="fas fa-filter"></i> <?=translate('filter')?></button>
					</div>
				</div>
			</div>
			<?php echo form_close();?>
		</section>

		<?php if (isset($skills_categories) && !empty($skills_categories)) { ?>
			<section class="panel appear-animation" data-appear-animation="<?php echo $global_config['animations'];?>" data-appear-animation-delay="100">
				<header class="panel-heading">
					<h4 class="panel-title">
						<i class="fas fa-users"></i> <?=translate('junior_tabulation_sheet')?>
					</h4>
				</header>
				<div class="panel-body">
					<div class="mt-sm mb-md">
						<!-- hidden school information prints -->
						<div class="export_title"><?php echo translate('class') . ' : ' . get_type_name_by_id('class', set_value('class_id'));
									echo ' ( ' . translate('section') . ' : ' . get_type_name_by_id('section', set_value('section_id')) . ' ) - ' . $this->application_model->exam_name_by_id(set_value('exam_id')) . " Junior Tabulation Sheet";
									?></div>
						<div class="visible-print fn_print">
							<center>
								<h4 class="text-dark text-weight-bold"><?=$branch['name']?></h4>
								<h5 class="text-dark"><?=$branch['address']?></h5>
								<h5 class="text-dark text-weight-bold"><?=$this->application_model->exam_name_by_id(set_value('exam_id'))?> - Junior Tabulation Sheet</h5>
								<h5 class="text-dark">
									<?php
									echo translate('class') . ' : ' . get_type_name_by_id('class', set_value('class_id'));
									echo ' ( ' . translate('section') . ' : ' . get_type_name_by_id('section', set_value('section_id')) . ' )';
									?>
								</h5>
								<hr>
							</center>
						</div>

						<!-- Rating Scale Legend -->
						<?php if (!empty($ratings_scale)): ?>
						<div class="alert alert-info mb-md">
							<strong><i class="fas fa-info-circle"></i> Rating Scale:</strong>
							<?php foreach ($ratings_scale as $rating): ?>
								<span class="label label-primary ml-sm"><?= $rating['label'] ?></span> = <?= $rating['description'] ?> &nbsp;&nbsp;
							<?php endforeach; ?>
						</div>
						<?php endif; ?>

						<div class="table-responsive">
							<table class="table table-bordered table-hover table-condensed mb-none" id="tableExport" style="font-size: 11px;">
								<thead class="text-dark">
									<tr>
										<th rowspan="2" style="vertical-align: middle;"><?=translate('students')?></th>
										<th rowspan="2" style="vertical-align: middle;"><?=translate('register_no')?></th>
										<th rowspan="2" style="vertical-align: middle;"><?=translate('roll')?></th>
										<?php
										// Calculate total columns for skills
										$total_skills = 0;
										foreach($skills_categories as $category_group):
											$item_count = count($category_group['items']);
											$total_skills += $item_count;
											echo '<th colspan="' . $item_count . '" class="text-center" style="background-color: #f5f5f5;">' . strtoupper($category_group['category']['category_name']) . '</th>';
										endforeach;
										?>
									</tr>
									<tr>
										<?php
										// Skill item names as sub-headers
										foreach($skills_categories as $category_group):
											foreach($category_group['items'] as $item):
												echo '<th style="font-size: 9px; min-width: 80px;">' . $item['item_name'] . '</th>';
											endforeach;
										endforeach;
										?>
									</tr>
								</thead>
								<tbody>
									<?php
									if (!empty($students_list)) {
										foreach ($students_list as $student):
											$student_id = $student->student_id;
									?>
									<tr>
										<td><?= $student->fullname ?></td>
										<td><?= $student->register_no ?></td>
										<td><?= $student->roll ?></td>
										<?php
										// Display ratings for each skill item
										foreach($skills_categories as $category_group):
											foreach($category_group['items'] as $item):
												$skill_item_id = $item['id'];
												$rating_id = isset($ratings[$student_id][$skill_item_id]) ? $ratings[$student_id][$skill_item_id] : null;

												// Find the rating label
												$rating_label = '-';
												if ($rating_id) {
													foreach ($ratings_scale as $rating) {
														if ($rating['id'] == $rating_id) {
															$rating_label = $rating['label'];
															break;
														}
													}
												}

												echo '<td class="text-center">';
												if ($rating_label != '-') {
													echo '<span class="label label-primary">' . $rating_label . '</span>';
												} else {
													echo '<span class="text-muted">-</span>';
												}
												echo '</td>';
											endforeach;
										endforeach;
										?>
									</tr>
									<?php
										endforeach;
									} else {
										echo '<tr><td colspan="' . (3 + $total_skills) . '" class="text-center">No students found</td></tr>';
									}
									?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</section>
		<?php } ?>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		$('#branch_id').on("change", function() {
			var branchID = $(this).val();
			getClassByBranch(branchID);
			getExamByBranch(branchID);
		});

		<?php if (isset($skills_categories) && !empty($skills_categories)): ?>
		var tabulation_sheet = $('#tableExport').DataTable({
			"dom": '<"row"<"col-sm-6 mb-xs"B><"col-sm-6"f>><"table-responsive"t>p',
			"lengthChange": false,
			"pageLength": -1,
			"ordering": false,
			"scrollX": true,
			"buttons": [
				{
					extend: 'copyHtml5',
					text: '<i class="far fa-copy"></i>',
					titleAttr: 'Copy',
					title: $('.export_title').html(),
					exportOptions: {
						columns: ':visible'
					}
				},
				{
					extend: 'excelHtml5',
					text: '<i class="fa fa-file-excel"></i>',
					titleAttr: 'Excel',
					title: $('.export_title').html(),
					exportOptions: {
						columns: ':visible'
					}
				},
				{
					extend: 'csvHtml5',
					text: '<i class="fa fa-file-alt"></i>',
					titleAttr: 'CSV',
					title: $('.export_title').html(),
					exportOptions: {
						columns: ':visible'
					}
				},
				{
					extend: 'pdfHtml5',
					text: '<i class="fa fa-file-pdf"></i>',
					titleAttr: 'PDF',
					title: $('.export_title').html(),
					orientation: 'landscape',
					pageSize: 'A4',
					footer: true,
					customize: function ( win ) {
						win.styles.tableHeader.fontSize = 8;
						win.styles.tableFooter.fontSize = 8;
						win.styles.tableHeader.alignment = 'left';
					},
					exportOptions: {
						columns: ':visible'
					}
				},
				{
					extend: 'print',
					text: '<i class="fa fa-print"></i>',
					titleAttr: 'Print',
					title: $('.fn_print').html(),
					customize: function ( win ) {
						$(win.document.body)
							.css( 'font-size', '8pt' );

						$(win.document.body).find( 'table' )
							.addClass( 'compact' )
							.css( 'font-size', 'inherit' );

						$(win.document.body).find( 'h1' )
							.css( 'font-size', '14pt' );
					},
					footer: true,
					exportOptions: {
						columns: ':visible'
					}
				},
				{
					extend: 'colvis',
					text: '<i class="fas fa-columns"></i>',
					titleAttr: 'Columns',
					title: $('.export_title').html(),
					postfixButtons: ['colvisRestore']
				},
			]
		});
		<?php endif; ?>
	});
</script>
