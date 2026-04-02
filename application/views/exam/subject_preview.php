<?php $widget = (is_superadmin_loggedin() ? 2 : 3); ?>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
		<?php echo form_open($this->uri->uri_string(), array('class' => 'validate'));?>
			<header class="panel-heading">
				<h4 class="panel-title"><?=translate('select_ground')?></h4>
			</header>
			<div class="panel-body">
				<div class="row mb-sm">
					<?php if (is_superadmin_loggedin()): ?>
					<div class="col-md-2 mb-sm">
						<div class="form-group">
							<label class="control-label"><?=translate('branch')?> <span class="required">*</span></label>
							<?php
								$arrayBranch = $this->app_lib->getSelectList('branch');
								echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='branch_id'
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
							?>
						</div>
					</div>
					<?php endif; ?>
					<div class="col-md-<?php echo $widget; ?> mb-sm">
						<div class="form-group">
							<label class="control-label"><?=translate('exam')?> <span class="required">*</span></label>
							<?php
								if(isset($branch_id)){
									$arrayExam = array("" => translate('select'));
									$exams = $this->db->get_where('exam', array('branch_id' => $branch_id,'session_id' => get_session_id()))->result();
									foreach ($exams as $row){
										$arrayExam[$row->id] = $this->application_model->exam_name_by_id($row->id);
									}
								} else {
									$arrayExam = array("" => translate('select_branch_first'));
								}
								echo form_dropdown("exam_id", $arrayExam, set_value('exam_id'), "class='form-control' id='exam_id' required data-plugin-selectTwo
								data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
						</div>
					</div>
					<div class="col-md-3 mb-sm">
						<div class="form-group">
							<label class="control-label"><?=translate('class')?> <span class="required">*</span></label>
							<?php
								$arrayClass = $this->app_lib->getClass($branch_id);
								echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)'
								required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
						</div>
					</div>
					<div class="col-md-<?php echo $widget; ?> mb-sm">
						<div class="form-group">
							<label class="control-label"><?=translate('section')?> <span class="required">*</span></label>
							<?php
								$arraySection = $this->app_lib->getSections(set_value('class_id'), false);
								echo form_dropdown("section_id", $arraySection, set_value('section_id'), "class='form-control' id='section_id' required
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label class="control-label"><?=translate('subject')?> <span class="required">*</span></label>
							<?php
								if(!empty(set_value('class_id'))) {
									$arraySubject = array("" => translate('select'));
									$query = $this->subject_model->getSubjectByClassSection(set_value('class_id'), set_value('section_id'));
									$subjects = $query->result_array();
									foreach ($subjects as $row){
										$subjectID = $row['subject_id'];
										$arraySubject[$subjectID] = $row['subjectname'];
									}
								} else {
									$arraySubject = array("" => translate('select_class_first'));
								}
								echo form_dropdown("subject_id", $arraySubject, set_value('subject_id'), "class='form-control' id='subject_id' required
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
						</div>
					</div>
				</div>
			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-offset-10 col-md-2">
						<button type="submit" name="search" value="1" class="btn btn btn-default btn-block"> <i class="fas fa-filter"></i> <?=translate('filter')?></button>
					</div>
				</div>
			</footer>
			<?php echo form_close();?>
		</section>
		
		<?php if (isset($student)): ?>
		<section class="panel appear-animation" data-appear-animation="<?php echo $global_config['animations'];?>" data-appear-animation-delay="100">
			<header class="panel-heading">
                <div class="panel-btn">
                    <button onclick="fn_printElem('printResult')" class="btn btn-default btn-circle icon" data-toggle="tooltip" data-original-title="<?=translate('print')?>">
                        <i class="fas fa-print"></i>
                    </button>
                </div>
				<h4 class="panel-title"><i class="fas fa-users"></i> <?=translate('subject_marks_preview')?></h4>
			</header>
			<div class="panel-body">
                <div id="printResult">
                    <style type="text/css">
                        @media print {
                            .table > tbody > tr > td, .table > tbody > tr > th, .table > tfoot > tr > td, .table > tfoot > tr > th, .table > tfoot > tr > td, .table > thead > tr > td, .table > thead > tr > th {
                                padding: 5px !important;
                                font-size: 12px !important;
                                border: 1px solid #000 !important;
                                color: #000 !important;
                            }
                            table {
                                width: 100%;
                                border-collapse: collapse;
                            }
                            body {
                                color: #000 !important;
                                font-size: 14px;
                            }
                            .center {
                                text-align: center;
                            }
                        }
                    </style>
                    <div class="visible-print">
                        <h4 class="text-center"><strong><?=$this->application_model->get_branch_name()?></strong></h4>
                        <h5 class="text-center"><strong><?=translate('subject_marks_preview')?></strong></h5>
                        <p class="text-center">
                            Class: <?=$this->application_model->class_name_by_id($class_id)?>, Section: <?=$this->db->get_where('section', ['id' => $section_id])->row()->name?><br>
                            Subject: <?=$this->db->get_where('subject', ['id' => $subject_id])->row()->name?>
                        </p>
                    </div>

				<?php if (!empty($student) && !empty($timetable_detail)) { ?>
				<div class="table-responsive mt-md mb-lg">
					<table class="table table-bordered table-condensed table-hover mb-none">
						<thead>
							<tr>
								<th><?=translate('sl')?></th>
								<th><?=translate('student_name')?></th>
								<th><?=translate('register_no')?></th>
							<?php
							$distributions = json_decode($timetable_detail['mark_distribution'], true);
							foreach ($distributions as $i => $value) {
								echo "<th>" . get_type_name_by_id('exam_mark_distribution', $i) . " (" . $value['full_mark'] . ")</th>";
							} ?>
								<th>Total (100)</th>
								<th>Grade</th>
								<th>Remark</th>
							</tr>
						</thead>
						<tbody>
							<?php 
							$count = 1;
							foreach ($student as $key => $row):
                                $total_score = 0;
                                $is_absent = ($row['get_abs'] == 'on');
								?>
							<tr>
								<td><?php echo $count++; ?></td>
								<td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
								<td><?php echo $row['register_no']; ?></td>
								<?php
								$getDetails = [];
								if (!empty($row['get_mark'])) {
									$getDetails = json_decode($row['get_mark'], true);
								}
								foreach ($distributions as $id => $ass) {
									$existMark = isset($getDetails[$id]) ? $getDetails[$id]  : '';
                                    if(is_numeric($existMark)) {
                                        $total_score += $existMark;
                                    }
                                    if ($is_absent) {
                                        echo "<td>Absent</td>";
                                    } else {
									    echo "<td>{$existMark}</td>";
                                    }
								} 
                                
                                $grade = $this->exam_model->get_grade($total_score, $branch_id);
                                ?>
                                <td><strong><?= $is_absent ? "Absent" : $total_score ?></strong></td>
                                <td><?= $is_absent ? "-" : ($grade['name'] ?? '') ?></td>
                                <td><?= $is_absent ? "-" : ($grade['remark'] ?? '') ?></td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
                </div>
				<?php } else { echo '<div class="alert alert-subl mt-md text-center">' . translate('no_information_available') . '</div>'; } ?>
			</div>
		</section>
		<?php endif; ?>
	</div>
</div>
	
<script type="text/javascript">
	$(document).ready(function () {
		$('#branch_id').on('change', function() {
			var branchID = $(this).val();
			getClassByBranch(branchID);
			getExamByBranch(branchID);
			$('#subject_id').html('').append('<option value=""><?=translate("select")?></option>');
		});

		$('#section_id').on('change', function() {
			var classID = $('#class_id').val();
			var sectionID =$(this).val();
			$.ajax({
				url: base_url + 'subject/getByClassSection',
				type: 'POST',
				data: {
					classID: classID,
					sectionID: sectionID
				},
				success: function (data) {
					$('#subject_id').html(data);
				}
			});
		});
	});

    function fn_printElem(elem) {
        var printContents = document.getElementById(elem).innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        window.location.reload();
    }
</script>
