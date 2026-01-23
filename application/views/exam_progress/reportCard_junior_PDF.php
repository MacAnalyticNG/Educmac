<?php
$CI =& get_instance();
$marksheet_template = $CI->marksheet_template_model->getTemplate($templateID, $branchID);
?>
<style type="text/css">
	.mark-container {
	    padding: <?=$marksheet_template['top_space'] . 'px ' . $marksheet_template['right_space'] . 'px ' . $marksheet_template['bottom_space'] . 'px ' . $marksheet_template['left_space'] . 'px'?>;
	}

	.background {
		width: 100%;
		height: 100%;
	<?php if (empty($marksheet_template['background'])) { ?>
		background: #fff;
	<?php } else { ?>
		background-image: url("<?=base_url('uploads/marksheet/' . $marksheet_template['background'])?>") !important;
		background-repeat: no-repeat !important;
		background-size: 100% 100% !important;
	<?php } ?>
	}

	.skills-section {
		margin-top: 20px;
		page-break-inside: avoid;
	}

	.skills-header {
		background-color: #f5f5f5;
		padding: 8px;
		font-weight: bold;
		margin-bottom: 5px;
		border-left: 4px solid #0088cc;
		font-size: 13px;
	}

	.skills-table {
		width: 100%;
		border-collapse: collapse;
		margin-bottom: 15px;
	}

	.skills-table th,
	.skills-table td {
		border: 1px solid #ddd;
		padding: 6px 8px;
		text-align: left;
	}

	.skills-table th {
		background-color: #f9f9f9;
		font-weight: 600;
	}

	.rating-badge {
		display: inline-block;
		padding: 3px 8px;
		background-color: #0088cc;
		color: white;
		border-radius: 3px;
		font-weight: bold;
		font-size: 12px;
	}

	.skills-category-affective {
		border-left-color: #5bc0de;
	}

	.skills-category-psychomotor {
		border-left-color: #5cb85c;
	}

	.skills-category-cognitive {
		border-left-color: #f0ad4e;
	}
</style>
<?php
$extINTL = extension_loaded('intl');
$CI =& get_instance();
if (!empty($student_array)) {
	foreach ($student_array as $sc => $studentID) {
		$result = $CI->exam_progress_model->getStudentReportCard($studentID, $sessionID, $class_id, $section_id);
		$student = $result['student'];
		$schoolYear = get_type_name_by_id('schoolyear', $sessionID, 'school_year');

		// Get teacher and head teacher remarks for template tags
		$remarks = ['teacher_remarks' => '', 'head_teacher_remarks' => ''];
		foreach ($examArray as $exam_id) {
			$remarks = $CI->skills_model->getStudentRemarks($studentID, $exam_id, $sessionID);
			if (!empty($remarks['teacher_remarks']) || !empty($remarks['head_teacher_remarks'])) {
				break;
			}
		}

		$extendsData = [];
		$extendsData['print_date'] = $print_date;
		$extendsData['schoolYear'] = $schoolYear;
		$extendsData['teacher_comments'] = '';
		$extendsData['jr_teacher_comment'] = $remarks['teacher_remarks'];
		$extendsData['jr_head_teacher_comment'] = $remarks['head_teacher_remarks'];
		$header_content = $CI->marksheet_template_model->tagsReplace($student, $marksheet_template, $extendsData, 'header_content');
		$footer_content = $CI->marksheet_template_model->tagsReplace($student, $marksheet_template, $extendsData, 'footer_content');
		?>
<div style="position: relative; width: 100%; height: 100%;">
	<div class="mark-container background">
		<?php echo $header_content; ?>

		<h4 style="margin-top: 20px; margin-bottom: 15px; border-bottom: 2px solid #333;">
			SKILLS ASSESSMENT (Affective / Psychomotor / Cognitive)
		</h4>

		<?php
		// Get skills ratings for this student
		$skills_ratings_grouped = [];
		foreach ($examArray as $exam_id) {
			$skills_ratings = $CI->skills_model->getStudentRatingsByCategory($studentID, $exam_id, $sessionID);
			if (!empty($skills_ratings)) {
				$skills_ratings_grouped = $skills_ratings;
				break;
			}
		}

		if (!empty($skills_ratings_grouped)):
			// Display rating scale legend at the top
			$ratings_scale = $CI->skills_model->getRatings($branchID, 'active');
			if (!empty($ratings_scale)): ?>
			<div style="margin-bottom: 15px; padding: 8px; background-color: #f9f9f9; border: 1px solid #ddd;">
				<strong>Rating Scale:</strong>
				<?php foreach ($ratings_scale as $rating): ?>
					<span style="margin-right: 15px;">
						<span class="rating-badge"><?=$rating['label']?></span> = <?=$rating['description']?>
					</span>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<?php
			// Arrange categories in 2-column layout using HTML table
			$categories_array = array_values($skills_ratings_grouped);
			$total_categories = count($categories_array);

			for ($i = 0; $i < $total_categories; $i += 2):
				?>
				<table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; border: none;">
					<tr>
						<?php
						// First column
						$category_data = $categories_array[$i];
						$category_type = array_keys($skills_ratings_grouped)[$i];
						?>
						<td style="width: 49%; vertical-align: top; padding-right: 10px; border: none;">
							<div class="skills-header skills-category-<?=$category_type?>">
								<?=strtoupper($category_data['category_name'])?>
							</div>
							<table class="skills-table">
								<thead>
									<tr>
										<th width="10%">#</th>
										<th width="50%">Skill Item</th>
										<th width="20%" style="text-align: center;">Rating</th>
										<th width="20%" style="text-align: center;">Description</th>
									</tr>
								</thead>
								<tbody>
									<?php $count = 1; foreach ($category_data['items'] as $item): ?>
									<tr>
										<td><?=$count++?></td>
										<td><?=$item['item_name']?></td>
										<td style="text-align: center;">
											<span class="rating-badge"><?=$item['rating_label']?></span>
										</td>
										<td style="text-align: center; font-size: 10px;"><?=$item['rating_description']?></td>
									</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</td>

						<?php
						// Second column (if exists)
						if (isset($categories_array[$i + 1])):
							$category_data = $categories_array[$i + 1];
							$category_type = array_keys($skills_ratings_grouped)[$i + 1];
						?>
						<td style="width: 49%; vertical-align: top; padding-left: 10px; border: none;">
							<div class="skills-header skills-category-<?=$category_type?>">
								<?=strtoupper($category_data['category_name'])?>
							</div>
							<table class="skills-table">
								<thead>
									<tr>
										<th width="10%">#</th>
										<th width="50%">Skill Item</th>
										<th width="20%" style="text-align: center;">Rating</th>
										<th width="20%" style="text-align: center;">Description</th>
									</tr>
								</thead>
								<tbody>
									<?php $count = 1; foreach ($category_data['items'] as $item): ?>
									<tr>
										<td><?=$count++?></td>
										<td><?=$item['item_name']?></td>
										<td style="text-align: center;">
											<span class="rating-badge"><?=$item['rating_label']?></span>
										</td>
										<td style="text-align: center; font-size: 10px;"><?=$item['rating_description']?></td>
									</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</td>
						<?php else: ?>
						<td style="width: 49%; vertical-align: top; border: none;"></td>
						<?php endif; ?>
					</tr>
				</table>
			<?php endfor; ?>
		<?php else: ?>
			<p style="text-align: center; padding: 20px; color: #999;">No skills assessment data available for this student.</p>
		<?php endif; ?>

		<?php echo $footer_content; ?>
	</div>
</div>
<?php if ((count($student_array) - 1) != $sc) { ?>
	<div style="page-break-after: always;"></div>
<?php } } } ?>
