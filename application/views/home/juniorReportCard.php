<style type="text/css">
	@media print {
		.pagebreak {
			page-break-before: always;
		}
	}
	.mark-container {
	    background: #fff;
	    width: 1000px;
	    position: relative;
	    z-index: 2;
	    margin: 0 auto;
	    padding: 20px 30px;
	}
	table {
	    border-collapse: collapse;
	    width: 100%;
	    margin: 0 auto;
	}
	.skills-section {
        margin-top: 20px;
        page-break-inside: avoid;
    }
    .skills-header {
        background-color: #f5f5f5;
        padding: 10px;
        font-weight: bold;
        margin-top: 15px;
        margin-bottom: 10px;
        border-left: 4px solid #0088cc;
        font-size: 14px;
    }
    .skills-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }
    .skills-table th,
    .skills-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
        font-size: 12px;
    }
    .skills-table th {
        background-color: #f9f9f9;
        font-weight: 600;
    }
    .rating-badge {
        display: inline-block;
        padding: 4px 10px;
        background-color: #0088cc;
        color: white;
        border-radius: 3px;
        font-weight: bold;
        font-size: 11px;
    }
    .skills-category-affective .skills-header {
        border-left-color: #5bc0de;
    }
    .skills-category-psychomotor .skills-header {
        border-left-color: #5cb85c;
    }
    .skills-category-cognitive .skills-header {
        border-left-color: #f0ad4e;
    }
    .remarks-section {
        margin-top: 25px;
        page-break-inside: avoid;
    }
    .remark-box {
        border: 1px solid #ddd;
        padding: 15px;
        margin-bottom: 15px;
        background-color: #f9f9f9;
    }
    .remark-label {
        font-weight: bold;
        color: #333;
        margin-bottom: 8px;
        font-size: 13px;
    }
    .remark-content {
        min-height: 60px;
        padding: 10px;
        background-color: white;
        border: 1px solid #e0e0e0;
        font-size: 12px;
        line-height: 1.6;
    }
    .rating-scale-box {
        margin-top: 15px;
        padding: 10px;
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        font-size: 11px;
    }
    h4 {
        margin-top: 15px;
        margin-bottom: 10px;
        border-bottom: 2px solid #333;
        padding-bottom: 5px;
        font-size: 16px;
    }
</style>
<?php
	$student = $result['student'];
	$examID = $exam_id;
	$getExam = $this->db->where(array('id' => $examID))->get('exam')->row_array();
	$getSchool = $this->db->where(array('id' => $getExam['branch_id']))->get('branch')->row_array();
	$schoolYear = get_type_name_by_id('schoolyear', $sessionID, 'school_year');

	// Load skills model if not already loaded
	$this->load->model('skills_model');

	// Get skills ratings grouped by category
	$skills_ratings_grouped = $this->skills_model->getStudentRatingsByCategory($student['id'], $examID, $sessionID);

	// Get teacher and head teacher remarks
	$teacher_remarks = '';
	$head_teacher_remarks = '';
	if (!empty($skills_ratings_grouped)) {
		// Get remarks from first rating entry (they should be the same across all entries for a student)
		$first_category = reset($skills_ratings_grouped);
		if (!empty($first_category['items'])) {
			$first_item = reset($first_category['items']);
			$teacher_remarks = isset($first_item['teacher_remarks']) ? $first_item['teacher_remarks'] : '';
			$head_teacher_remarks = isset($first_item['head_teacher_remarks']) ? $first_item['head_teacher_remarks'] : '';
		}
	}
?>
<div class="mark-container">
	<table border="0" style="margin-top: 20px; height: 100px;">
		<tbody>
			<tr>
			<td style="width:40%;vertical-align: top;"><img style="max-width:225px;" src="<?=$this->application_model->getBranchImage($getExam['branch_id'], 'report-card-logo')?>"></td>
			<td style="width:60%;vertical-align: top;">
				<table align="right" class="table-head" style="text-align: right;">
					<tbody>
						<tr><th style="font-size: 26px;" class="text-right"><?=$getSchool['school_name']?></th></tr>
						<tr><th style="font-size: 14px; padding-top: 4px;" class="text-right">Academic Session : <?=$schoolYear?></th></tr>
						<tr><td><?=$getSchool['address']?></td></tr>
						<tr><td><?=$getSchool['mobileno']?></td></tr>
						<tr><td><?=$getSchool['email']?></td></tr>
					</tbody>
				</table>
			</td>
			</tr>
		</tbody>
	</table>
	<div style="width: 100%;">
		<div style="width: 80%; float: left;">
			<table class="table table-bordered" style="margin-top: 20px;">
				<tbody>
					<tr>
						<th>Name</td>
						<td><?=$student['first_name'] . " " . $student['last_name']?></td>
						<th>Register No</td>
						<td><?=$student['register_no']?></td>
						<th>Roll Number</td>
						<td><?=$student['roll']?></td>
					</tr>
					<tr>
						<th>Father Name</td>
						<td><?=$student['father_name']?></td>
						<th>Admission Date</td>
						<td><?=_d($student['admission_date'])?></td>
						<th>Date of Birth</td>
						<td><?=_d($student['birthday'])?></td>
					</tr>
					<tr>
						<th>Mother Name</td>
						<td><?=$student['mother_name']?></td>
						<th>Class</td>
						<td><?=$student['class'] . " (" . $student['section'] . ")"?></td>
						<th>Gender</td>
						<td><?=ucfirst($student['gender'])?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div style="width: 20%; float: left; text-align: right;">
			<img src="<?php echo get_image_url('student', $student['photo']); ?>" style="margin-top: 20px; border-radius: 10px;" height="120">
		</div>
	</div>

	<!-- Academic Performance Section (matching regular reportCard.php) -->
	<?php
	// Check if we should show subjects table (for combined report cards)
	$show_subjects = isset($show_subjects) && $show_subjects === true;
	if ($show_subjects && isset($result['exam']) && !empty($result['exam'])):
		$getMarksList = $result['exam'];
	?>
	<table class="table table-condensed table-bordered mt-lg">
		<thead>
			<tr>
				<th>Subjects</th>
			<?php
			$markDistribution = json_decode($getExam['mark_distribution'], true);
			foreach ($markDistribution as $id) {
				?>
				<th><?php echo get_type_name_by_id('exam_mark_distribution',$id)  ?></th>
			<?php } ?>
			<?php if ($getExam['type_id'] == 1) { ?>
				<th>Total</th>
			<?php } elseif($getExam['type_id'] == 2) { ?>
				<th>Grade</th>
				<th>Point</th>
				<th>Remark</th>
			<?php } elseif ($getExam['type_id'] == 3) { ?>
				<th>Total</th>
				<th>Grade</th>
				<th>Point</th>
				<th>Remark</th>
			<?php } ?>
			</tr>
		</thead>
		<tbody>
		<?php
		$colspan = count($markDistribution) + 1;
		$total_grade_point = 0;
		$grand_obtain_marks = 0;
		$grand_full_marks = 0;
		$result_status = 1;
		foreach ($getMarksList as $row) {
		?>
			<tr>
				<td valign="middle" width="35%"><?=$row['subject_name']?></td>
			<?php
			$total_obtain_marks = 0;
			$total_full_marks = 0;
			$fullMarkDistribution = json_decode($row['mark_distribution'], true);
			$obtainedMark = json_decode($row['get_mark'], true);
			foreach ($fullMarkDistribution as $i => $val) {
				$obtained_mark = floatval($obtainedMark[$i]);
				$fullMark = floatval($val['full_mark']);
				$passMark = floatval($val['pass_mark']);
				if ($obtained_mark < $passMark) {
					$result_status = 0;
				}

				$total_obtain_marks += $obtained_mark;
				$obtained = $row['get_abs'] == 'on' ? 'Absent' : $obtained_mark;
				$total_full_marks += $fullMark;
				?>
			<?php if ($getExam['type_id'] == 1 || $getExam['type_id'] == 3){ ?>
				<td valign="middle">
					<?php
						if ($row['get_abs'] == 'on') {
							echo 'Absent';
						} else {
							echo $obtained_mark . '/' . $fullMark;
						}
					?>
				</td>
			<?php } if ($getExam['type_id'] == 2) { ?>
				<td valign="middle">
					<?php
						if ($row['get_abs'] == 'on') {
							echo 'Absent';
						} else {
							$percentage_grade = ($obtained_mark * 100) / $fullMark;
							$grade = $this->exam_model->get_grade($percentage_grade, $getExam['branch_id']);
							echo $grade['name'];
						}
					?>
				</td>
			<?php } ?>
			<?php
			}
			$grand_obtain_marks += $total_obtain_marks;
			$grand_full_marks += $total_full_marks;
			?>
			<?php if($getExam['type_id'] == 1 || $getExam['type_id'] == 3) { ?>
				<td valign="middle"><?=$total_obtain_marks . "/" . $total_full_marks?></td>
			<?php } if($getExam['type_id'] == 2) {
				$colspan += 1;
				$percentage_grade = ($total_obtain_marks * 100) / $total_full_marks;
				$grade = $this->exam_model->get_grade($percentage_grade, $getExam['branch_id']);
				$total_grade_point += $grade['grade_point'];
				?>
				<td valign="middle"><?=$grade['name']?></td>
				<td valign="middle"><?=number_format($grade['grade_point'], 2, '.', '')?></td>
				<td valign="middle"><?=$grade['remark']?></td>
			<?php } if ($getExam['type_id'] == 3) {
				$colspan += 2;
				$percentage_grade = ($total_obtain_marks * 100) / $total_full_marks;
				$grade = $this->exam_model->get_grade($percentage_grade, $getExam['branch_id']);
				$total_grade_point += $grade['grade_point'];
				?>
				<td valign="middle"><?=$grade['name']?></td>
				<td valign="middle"><?=number_format($grade['grade_point'], 2, '.', '')?></td>
				<td valign="middle"><?=$grade['remark']?></td>
			<?php } ?>
			</tr>
		<?php } ?>
		<?php if ($getExam['type_id'] == 1 || $getExam['type_id'] == 3) { ?>
			<tr class="text-weight-semibold">
				<td valign="top" >GRAND TOTAL :</td>
				<td valign="top" colspan="<?=$colspan?>"><?=$grand_obtain_marks . '/' . $grand_full_marks; ?>, Average : <?php $percentage = ($grand_obtain_marks * 100) / $grand_full_marks; echo number_format($percentage, 2, '.', '')?>%</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<?php endif; ?>

	<!-- Skills Assessment Section (2x2 Grid Layout) -->
	<?php if (!empty($skills_ratings_grouped)): ?>
		<h4 style="margin-top: 20px;">SKILLS ASSESSMENT</h4>

		<?php
		// Get rating scale and display it before skills
		$ratings_scale = $this->skills_model->getRatings($getExam['branch_id'], 'active');
		if (!empty($ratings_scale)): ?>
			<div style="margin-top: 10px; margin-bottom: 15px; padding: 8px; background-color: #f9f9f9; border: 1px solid #ddd; font-size: 9px;">
				<strong>Rating Scale:</strong>
				<?php foreach ($ratings_scale as $rating): ?>
					<span style="margin-left: 10px;">
						<span style="display: inline-block; padding: 2px 6px; background-color: #0088cc; color: white; border-radius: 3px; font-weight: bold;">
							<?= $rating['label'] ?>
						</span> = <?= $rating['description'] ?>
					</span>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php
		// Convert skills to array for easier iteration
		$skills_array = array_values($skills_ratings_grouped);
		$total_categories = count($skills_array);

		// Display in 2x2 grid
		for ($i = 0; $i < $total_categories; $i += 2):
		?>
			<table style="width: 100%; margin-bottom: 15px; border: none;" cellpadding="0" cellspacing="0">
				<tr style="vertical-align: top;">
					<?php
					// First category in the row
					if (isset($skills_array[$i])):
						$category_data = $skills_array[$i];
						$category_type = array_keys($skills_ratings_grouped)[$i];
					?>
					<td style="width: 48%; padding-right: 2%;">
						<div class="skills-category-<?= $category_type ?>" style="margin-bottom: 10px;">
							<div style="background-color: #f5f5f5; padding: 5px 10px; font-weight: bold; font-size: 11px; border-left: 3px solid <?= $category_type == 'affective' ? '#5bc0de' : ($category_type == 'psychomotor' ? '#5cb85c' : '#f0ad4e') ?>;">
								<?= strtoupper($category_data['category_name']) ?>
							</div>
							<table class="table table-condensed table-bordered" style="font-size: 10px; margin-bottom: 5px;">
								<thead>
								<tr>
									<th width="5%">#</th>
									<th width="55%">Skill Item</th>
									<th width="20%" style="text-align: center;">Rating</th>
									<th width="20%" style="text-align: center;">Remark</th>
								</tr>
								</thead>
								<tbody>
								<?php $count = 1;
								foreach ($category_data['items'] as $item): ?>
									<tr>
										<td><?= $count++ ?></td>
										<td><?= $item['item_name'] ?></td>
										<td style="text-align: center;">
											<span style="display: inline-block; padding: 2px 6px; background-color: #0088cc; color: white; border-radius: 3px; font-weight: bold; font-size: 9px;">
												<?= $item['rating_label'] ?>
											</span>
										</td>
										<td style="text-align: center; font-size: 9px;"><?= $item['rating_description'] ?></td>
									</tr>
								<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</td>
					<?php endif; ?>

					<?php
					// Second category in the row (if exists)
					if (isset($skills_array[$i + 1])):
						$category_data = $skills_array[$i + 1];
						$category_type = array_keys($skills_ratings_grouped)[$i + 1];
					?>
					<td style="width: 48%; padding-left: 2%;">
						<div class="skills-category-<?= $category_type ?>" style="margin-bottom: 10px;">
							<div style="background-color: #f5f5f5; padding: 5px 10px; font-weight: bold; font-size: 11px; border-left: 3px solid <?= $category_type == 'affective' ? '#5bc0de' : ($category_type == 'psychomotor' ? '#5cb85c' : '#f0ad4e') ?>;">
								<?= strtoupper($category_data['category_name']) ?>
							</div>
							<table class="table table-condensed table-bordered" style="font-size: 10px; margin-bottom: 5px;">
								<thead>
								<tr>
									<th width="5%">#</th>
									<th width="55%">Skill Item</th>
									<th width="20%" style="text-align: center;">Rating</th>
									<th width="20%" style="text-align: center;">Remark</th>
								</tr>
								</thead>
								<tbody>
								<?php $count = 1;
								foreach ($category_data['items'] as $item): ?>
									<tr>
										<td><?= $count++ ?></td>
										<td><?= $item['item_name'] ?></td>
										<td style="text-align: center;">
											<span style="display: inline-block; padding: 2px 6px; background-color: #0088cc; color: white; border-radius: 3px; font-weight: bold; font-size: 9px;">
												<?= $item['rating_label'] ?>
											</span>
										</td>
										<td style="text-align: center; font-size: 9px;"><?= $item['rating_description'] ?></td>
									</tr>
								<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</td>
					<?php else: ?>
					<td style="width: 48%; padding-left: 2%;"></td>
					<?php endif; ?>
				</tr>
			</table>
		<?php endfor; ?>
	<?php else: ?>
		<p style="text-align: center; padding: 30px; color: #999;">No skills assessment data available.</p>
	<?php endif; ?>

	<!-- Teacher Remarks in single row -->
	<div style="margin-top: 20px;">
		<table class="table table-bordered" style="font-size: 11px;">
			<tr>
				<th style="width: 150px; background-color: #f5f5f5;">Class Teacher:</th>
				<td style="padding: 10px;"><?= !empty($teacher_remarks) ? nl2br(htmlspecialchars($teacher_remarks)) : '&nbsp;' ?></td>
				<th style="width: 150px; background-color: #f5f5f5;">Head Teacher:</th>
				<td style="padding: 10px;"><?= !empty($head_teacher_remarks) ? nl2br(htmlspecialchars($head_teacher_remarks)) : '&nbsp;' ?></td>
			</tr>
		</table>
	</div>

	<?php if ($attendance == 1) {
		$year = explode('-', $schoolYear);
		$getTotalWorking = $this->db->where(array('enroll_id' => $student['enrollID'], 'status !=' => 'H', 'year(date)' => $year[0]))->get('student_attendance')->num_rows();
		$getTotalAttendance = $this->db->where(array('enroll_id' => $student['enrollID'], 'status' => 'P', 'year(date)' => $year[0]))->get('student_attendance')->num_rows();
		$attenPercentage = empty($getTotalWorking) ? '0.00' : ($getTotalAttendance * 100) / $getTotalWorking;
		?>
	<div style="width: 50%; margin-top: 20px;">
		<table class="table table-bordered table-condensed">
			<tbody>
				<tr>
					<th colspan="2" class="text-center">Attendance</th>
				</tr>
				<tr>
					<th style="width: 65%;">No. of working days</th>
					<td><?=$getTotalWorking?></td>
				</tr>
				<tr>
					<th style="width: 65%;">No. of days attended</th>
					<td><?=$getTotalAttendance?></td>
				</tr>
				<tr>
					<th style="width: 65%;">Attendance Percentage</th>
					<td><?=number_format($attenPercentage, 2, '.', '') ?>%</td>
				</tr>
			</tbody>
		</table>
	</div>
	<?php } ?>

	<table style="width:100%; outline:none; margin-top: 35px;">
		<tbody>
			<tr>
				<td style="font-size: 15px; text-align:left;">Print Date : <?=_d($print_date)?></td>
				<td style="border-top: 1px solid #ddd; font-size:15px;text-align:left">Principal Signature</td>
				<td style="border-top: 1px solid #ddd; font-size:15px;text-align:center;">Class Teacher Signature</td>
				<td style="border-top: 1px solid #ddd; font-size:15px;text-align:right;">Parent Signature</td>
			</tr>
		</tbody>
	</table>
</div>
