<section class="panel">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<li>
                <a href="<?=base_url('certificate')?>">
                    <i class="fas fa-list-ul"></i> <?=translate('certificate') ." ". translate('list')?>
                </a>
			</li>
			<li class="active">
                <a href="#edit" data-toggle="tab">
                   <i class="far fa-edit"></i> <?=translate('edit') . " " . translate('certificate')?>
                </a>
			</li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active" id="edit">
					<?php echo form_open($this->uri->uri_string(), array('class' => 'form-bordered form-horizontal frm-submit-data'));?>
					<input type="hidden" name="certificate_id" value="<?=$certificate['id']?>">
					
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('Certificate') . " " . translate('name')?> <span class="required">*</span></label>
						<div class="col-md-8">
							<input type="text" class="form-control" name="certificate_name" value="<?=$certificate['name']?>" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('Purpose')?>
						 <small class="text-muted d-block"><?= translate('(If NOC)') ?></small></label>
						<div class="col-md-8">
							<input type="text" class="form-control" name="purpose" value="<?=$certificate['purpose']?>" />
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
								echo form_dropdown("page_layout", $arrayType, $certificate['page_layout'], "class='form-control' data-width='100%'
								data-plugin-selectTwo  data-minimum-results-for-search='Infinity'");
							?>
							<span class="error"></span>
						</div>
					</div>

					<div class="form-group stafftag">
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
								echo form_dropdown("emp_qr_code", $arrayType, $certificate['qr_code'], "class='form-control' data-width='100%'
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
									<input type="text" class="form-control" name="top_space" value="<?=$certificate['top_space']?>" placeholder="Top Space (px)" />
								</div>
								<div class="col-xs-6">
									<input type="text" class="form-control" name="bottom_space" value="<?=$certificate['bottom_space']?>" placeholder="Bottom Space (px)" />
								</div>
							</div>
						</div>
						<div class="mt-md col-md-offset-3 col-md-8">
							<div class="row">
								<div class="col-xs-6">
									<input type="text" class="form-control" name="right_space" value="<?=$certificate['right_space']?>" placeholder="Right Space (px)" />
								</div>
								<div class="col-xs-6">
									<input type="text" class="form-control" name="left_space" value="<?=$certificate['left_space']?>" placeholder="Left Space (px)" />
								</div>
							</div>
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('signature') . " " . translate('image')?></label>
						<div class="col-md-8">
							<?php
							$signature_url = '';
							if (!empty($certificate['signature']) && file_exists('uploads/certificate/' . $certificate['signature'])) {
								$signature_url = base_url('uploads/certificate/' . $certificate['signature']);
							}
							?>
							<input type="file" name="signature_file" class="dropify" data-height="100" <?php if($signature_url) echo 'data-default-file="' . $signature_url . '"'; ?> />
							<input type="hidden" name="old_signature_file" value="<?=$certificate['signature']?>">
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('logo') . " " . translate('image')?></label>
						<div class="col-md-8">
							<?php
							$logo_url = '';
							if (!empty($certificate['logo']) && file_exists('uploads/certificate/' . $certificate['logo'])) {
								$logo_url = base_url('uploads/certificate/' . $certificate['logo']);
							}
							?>
							<input type="file" name="logo_file" class="dropify" data-height="100" <?php if($logo_url) echo 'data-default-file="' . $logo_url . '"'; ?> />
							<input type="hidden" name="old_logo_file" value="<?=$certificate['logo']?>">
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('background') . " " . translate('image')?></label>
						<div class="col-md-8">
							<?php
							$background_url = '';
							if (!empty($certificate['background']) && file_exists('uploads/certificate/' . $certificate['background'])) {
								$background_url = base_url('uploads/certificate/' . $certificate['background']);
							}
							?>
							<input type="file" name="background_file" class="dropify" data-height="100" <?php if($background_url) echo 'data-default-file="' . $background_url . '"'; ?> />
							<input type="hidden" name="old_background_file" value="<?=$certificate['background']?>">
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label">Certificate Content <span class="required">*</span></label>
						<div class="col-md-8">
							<textarea name="content" class="form-control" id="certificateConten" rows="10"><?=$certificate['content']?></textarea>
							<span class="error"></span>
							<div class="studenttags" style="<?=$certificate['user_type'] == 1 ? '' : 'display: none;' ?>">
							<?php 
							$tagsList = $this->certificate_model->tagsList(1); 
							foreach ($tagsList as $key => $value) {
								?>
								<a data-value=" <?=$value?> " class="btn btn-default mt-sm btn-xs btn_tag"><?=$value?></a>
							<?php } ?>
							</div>
							<div class="stafftag" style="<?=$certificate['user_type'] == 2 ? '' : 'display: none;' ?>">
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
									<i class="fas fa-plus-circle"></i> <?=translate('update')?>
								</button>
							</div>
						</div>
					</footer>
				<?php echo form_close(); ?>
			</div>
		</div>
	</div>
</section>