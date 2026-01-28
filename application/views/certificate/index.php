
<section class="panel">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<li class="active">
                <a href="#list" data-toggle="tab">
                    <i class="fas fa-list-ul"></i> <?=translate('certificate') ." ". translate('list')?>
                </a>
			</li>
<?php if (get_permission('certificate_templete', 'is_add')): ?>
			<li>
                <a href="#add" data-toggle="tab">
                   <i class="far fa-edit"></i> <?=translate('add') . " ". translate('certificate')?>
                </a>
			</li>
<?php endif; ?>
		</ul>
		<div class="tab-content">
			<div class="tab-pane box active mb-md" id="list">
				<table class="table table-bordered table-hover mb-none table-condensed table-export">
					<thead>
						<tr>
							<th><?=translate('sl')?></th>
							<th><?=translate('certificate') . " " . translate('name')?></th>
							<th><?=translate('applicable_user')?></th>
							<th><?=translate('page_layout')?></th>
							<th class="no-sort"><?=translate('background') . " " . translate('image')?></th>
							<th><?=translate('created_at')?></th>
							<th><?=translate('action')?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$count = 1;
						foreach ($certificatelist as $row):
						?>
						<tr>
							<td><?php echo $count++; ?></td>
							<td><?php echo $row['name']; ?></td>
							<td><?php
							if ($row['user_type'] == 1) {
								echo translate('student');
							} else {
								echo translate('employee');
							} 
							?></td>
							<td><?php
							if ($row['page_layout'] == 1) {
								echo "A4 (Portrait)";
							} else {
								echo "A4 (Landscape)";
							} 
							?></td>
							<td>
								<?php
							        $imgPath = 'uploads/certificate/' . $row['background'];
							        if (file_exists($imgPath) && !empty($row['background'])) {
							            $imgPath = base_url($imgPath);
							        } else {
							            $imgPath = base_url('uploads/language_flags/defualt.png');
							        }

								 ?>
								<img class="" src="<?=$imgPath?>" height="50">
							</td>
							<td><?php echo _d($row['created_at']);?></td>
							<td class="min-w-c">
								<!-- view link -->
								<a href="javascript:void(0);" class="btn btn-circle btn-default icon" data-toggle="tooltip" data-original-title="<?=translate('view')?>" 
								onclick="getCertificate('<?=$row['id'] ?>');">
									<i class="fas fa-bars"></i>
								</a>
							<?php if (get_permission('certificate_templete', 'is_edit')) { ?>
								<a href="<?=base_url('certificate/edit/' . $row['id']);?>" class="btn btn-circle btn-default icon">
									<i class="fas fa-pen-nib"></i>
								</a>
							<?php } if (get_permission('certificate_templete', 'is_delete')) { ?>
								<!-- deletion link -->
								<?php echo btn_delete('certificate/delete/'.$row['id']);?>
							<?php } ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
<?php if (get_permission('certificate_templete', 'is_add')): ?>
			<div class="tab-pane" id="add">
					<?php echo form_open($this->uri->uri_string(), array('class' => 'form-bordered form-horizontal frm-submit-data'));?>
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('Certificate') . " " . translate('name')?> <span class="required">*</span></label>
						<div class="col-md-8">
							<input type="text" class="form-control" name="certificate_name" value="" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('Purpose')?>
						 <small class="text-muted d-block"><?= translate('(If NOC)') ?></small></label>
						<div class="col-md-8">
							<input type="text" class="form-control" name="purpose" value="" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-md-3">Page Layout <span class="required">*</span></label>
						<div class="col-md-8">
							<?php
								$arrayType = array(
									'' => translate('select'),
									'1' => "A4 (Portrait)",
									'2' => "A4 (Landscape)"
								);
								echo form_dropdown("page_layout", $arrayType, set_value('page_layout'), "class='form-control' data-width='100%'
								data-plugin-selectTwo  data-minimum-results-for-search='Infinity'");
							?>
							<span class="error"></span>
						</div>
					</div>
					
					<div class="form-group">
						<label class="control-label col-md-3">QR Code Text <span class="required">*</span></label>
						<div class="col-md-8">
							<?php
								$arrayType = array(
									'' => translate('select'),
									'name' => translate('name'),
									'staff_id' => translate('employee_id'),
									'birthday' => translate('birthday'),
									'joining_date' => translate('joining_date'),
								);
								echo form_dropdown("emp_qr_code", $arrayType, set_value('qr_code'), "class='form-control' data-width='100%'
								data-plugin-selectTwo  data-minimum-results-for-search='Infinity'");
							?>
							<span class="error"></span>
						</div>
					</div>
					
					<div class="form-group">
						<label class="col-md-3 control-label">Layout Spacing <span class="required">*</span></label>
						<div class="col-md-8">
							<div class="row">
								<div class="col-xs-6">
									<input type="text" class="form-control" name="top_space" value="" placeholder="Top Space (px)" />
								</div>
								<div class="col-xs-6">
									<input type="text" class="form-control" name="bottom_space" value="" placeholder="Bottom Space (px)" />
								</div>
							</div>
						</div>
						<div class="mt-md col-md-offset-3 col-md-8">
							<div class="row">
								<div class="col-xs-6">
									<input type="text" class="form-control" name="right_space" value="" placeholder="Right Space (px)" />
								</div>
								<div class="col-xs-6">
									<input type="text" class="form-control" name="left_space" value="" placeholder="Left Space (px)" />
								</div>
							</div>
							<span class="error"></span>
						</div>
					</div>
					
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('signature') . " " . translate('image')?></label>
						<div class="col-md-8">
							<input type="file" name="signature_file" class="dropify" data-height="100" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('logo') . " " . translate('image')?></label>
						<div class="col-md-8">
							<input type="file" name="logo_file" class="dropify" data-height="100" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('background') . " " . translate('image')?></label>
						<div class="col-md-8">
							<input type="file" name="background_file" class="dropify" data-height="100" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label">Certificate Content <span class="required">*</span></label>
						<div class="col-md-8">
							<textarea name="content" class="form-control" id="certificateConten" rows="10"></textarea>
							<span class="error"></span>
							<div class="stafftag">
							<?php 
							$tagsList = $this->certificate_model->tagsList(2); 
							foreach ($tagsList as $key => $value) {
								?>
								<a data-value=" <?=$value?> " class="btn btn-default mt-sm btn-xs btn_tag"><?=$value?></a>
							<?php } ?>
							</div>
						</div>
					</div>
					<footer class="panel-footer">
						<div class="row">
							<div class="col-md-offset-3 col-md-2">
								<button type="submit" class="btn btn-default btn-block" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
									<i class="fas fa-plus-circle"></i> <?=translate('save')?>
								</button>
							</div>
						</div>
					</footer>
				<?php echo form_close(); ?>
			</div>
<?php endif; ?>
		</div>
	</div>
</section>

<div class="zoom-anim-dialog modal-block modal-block-lg mfp-hide payroll-t-modal" id="modal">
	<section class="panel">
		<header class="panel-heading">
			<h4 class="panel-title"><i class="fas fa-bars"></i> <?php echo translate('certificate') . " " . translate('view'); ?></h4>
		</header>
		<div class="panel-body">
			<div id="quick_view"></div>
		</div>
		<footer class="panel-footer">
			<div class="row">
				<div class="col-md-12 text-right">
					<button class="btn btn-default modal-dismiss"><?php echo translate('close'); ?></button>
				</div>
			</div>
		</footer>
	</section>
</div>