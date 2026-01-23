<?php if (is_superadmin_loggedin()): ?>
	<?php $this->load->view('frontend/branch_select'); ?>
<?php endif;
if (!empty($branch_id)): ?>
	<div class="row">
		<div class="col-md-3 mb-md">
			<?php include 'sidebar.php'; ?>
		</div>
		<div class="col-md-9">
			<section class="panel">
				<header class="panel-heading">
					<h4 class="panel-title"><?= translate('exam_results') ?></h4>
				</header>
				<?php echo form_open_multipart($this->uri->uri_string() . get_request_url(), array('class' => 'form-horizontal form-bordered frm-submit-data')); ?>
				<div class="panel-body">
					<div class="form-group mt-md">
						<label class="col-md-2 control-label"><?php echo translate('page') . " " . translate('title'); ?> <span class="required">*</span></label>
						<div class="col-md-8">
							<input type="text" class="form-control" name="page_title" value="<?php echo set_value('page_title', $admitcard['page_title']); ?>" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group mt-md">
						<label class="col-md-2 control-label"><?php echo translate('description'); ?> <span class="required">*</span></label>
						<div class="col-md-8">
							<textarea name="description" class="summernote"><?php echo set_value('description', $admitcard['description']); ?></textarea>
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-2 control-label"><?php echo translate('banner_photo'); ?> <span class="required">*</span></label>
						<div class="col-md-8">
							<input type="hidden" name="old_photo" value="<?php echo $admitcard['banner_image']; ?>">
							<input type="file" name="photo" class="dropify" data-height="150" data-default-file="<?php echo base_url('uploads/frontend/banners/' . $admitcard['banner_image']); ?>" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-2 control-label"><?php echo translate('meta') . " " . translate('keyword'); ?></label>
						<div class="col-md-8">
							<input type="text" class="form-control" name="meta_keyword" value="<?php echo set_value('meta_keyword', $admitcard['meta_keyword']); ?>" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-2 control-label"><?php echo translate('meta') . " " . translate('description'); ?></label>
						<div class="col-md-8">
							<input type="text" class="form-control" name="meta_description" value="<?php echo set_value('meta_description', $admitcard['meta_description']); ?>" />
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<div class="checkbox-replace">
								<label class="i-checks">
									<input type="checkbox" name="attendance" value="1" <?php echo $admitcard['attendance'] == 1 ? 'checked' : '' ?>><i></i> Print Attendance
								</label>
							</div>
							<div class="checkbox-replace mt-xs">
								<label class="i-checks">
									<input type="checkbox" name="grade_scale" value="1" <?php echo $admitcard['grade_scale'] == 1 ? 'checked' : '' ?>><i></i> Print Grade Scale
								</label>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label class="col-md-2 control-label"><?php echo translate('section') . " " . translate('template') . " " . translate('mapping'); ?></label>
						<div class="col-md-8">
							<div class="panel panel-default mt-sm">
								<div class="panel-heading">
									<h4 class="panel-title"><?php echo translate('assign_marksheet_templates_to_sections'); ?></h4>
									<small class="text-muted">Map each section to a specific marksheet template. Multiple sections can use the same template.</small>
								</div>
								<div class="panel-body">
									<table class="table table-condensed table-bordered">
										<thead>
											<tr>
												<th width="40%"><?php echo translate('class'); ?></th>
												<th width="40%"><?php echo translate('section'); ?></th>
												<th width="20%"><?php echo translate('template'); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php
											$this->db->select('section.id, section.name as section_name, class.name as class_name');
											$this->db->from('section');
											$this->db->join('sections_allocation', 'sections_allocation.section_id = section.id', 'inner');
											$this->db->join('class', 'class.id = sections_allocation.class_id', 'inner');
											$this->db->where('section.branch_id', $branch_id);
											$this->db->order_by('class.name', 'ASC');
											$this->db->order_by('section.name', 'ASC');
											$sections = $this->db->get()->result();

											// Get all templates for this branch
											$templates = $this->db->select('id, name')->where('branch_id', $branch_id)->get('marksheet_template')->result();

											// Get existing section-template mappings
											$mappings = array();
											$mapped = $this->db->select('section_id, template_id')
												->where('branch_id', $branch_id)
												->get('section_marksheet_template')
												->result();
											foreach ($mapped as $m) {
												$mappings[$m->section_id] = $m->template_id;
											}

											foreach ($sections as $section):
												$selected_template = isset($mappings[$section->id]) ? $mappings[$section->id] : 0;
											?>
												<tr>
													<td><?php echo $section->class_name; ?></td>
													<td><?php echo $section->section_name; ?></td>
													<td>
														<select name="section_template[<?php echo $section->id; ?>]" class="form-control" data-plugin-selectTwo data-minimum-results-for-search="Infinity">
															<option value="0" <?php echo $selected_template == 0 ? 'selected' : ''; ?>>Default Template</option>
															<?php foreach ($templates as $template): ?>
																<option value="<?php echo $template->id; ?>" <?php echo $selected_template == $template->id ? 'selected' : ''; ?>>
																	<?php echo $template->name; ?>
																</option>
															<?php endforeach; ?>
														</select>
													</td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
				<footer class="panel-footer mt-sm">
					<div class="row">
						<div class="col-md-2 col-md-offset-2">
							<button type="submit" class="btn btn-default btn-block" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
								<i class="fas fa-plus-circle"></i> <?php echo translate('save'); ?>
							</button>
						</div>
					</div>
				</footer>
				<?php echo form_close(); ?>
			</section>
		</div>
	</div>
<?php endif; ?>