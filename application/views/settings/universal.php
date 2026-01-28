<section class="panel appear-animation" data-appear-animation="<?php echo $global_config['animations']; ?>" data-appear-animation-delay="100">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<li <?=(empty($this->session->flashdata('active')) ? 'class="active"' : '');?>>
				<a href="#setting" data-toggle="tab">
					<i class="fas fa-chalkboard-teacher"></i> 
				   <span class="hidden-xs"> <?=translate('general_settings')?></span>
				</a>
			</li>
			<li <?=($this->session->flashdata('active') == 2 ? 'class="active"' : '');?>>
				<a href="#theme" data-toggle="tab">
				   <i class="fas fa-paint-roller"></i>
				   <span class="hidden-xs"> <?=translate('theme_settings')?></span>
				</a>
			
			</li>
			<li <?=($this->session->flashdata('active') == 3 ? 'class="active"' : '');?>>
				<a href="#upload" data-toggle="tab">
				   <i class="fab fa-uikit"></i>
				   <span class="hidden-xs"> <?=translate('logo')?></span>
				</a>
			</li>
			<li <?=($this->session->flashdata('active') == 4 ? 'class="active"' : '');?>>
				<a href="#file_settings" data-toggle="tab">
				   <i class="far fa-file-alt"></i>
				   <span class="hidden-xs"> Upload file settings</span>
				</a>
			</li>
			<li <?=($this->session->flashdata('active') == 5 ? 'class="active"' : '');?>>
				<a href="#employee_scores" data-toggle="tab">
				   <i class="fas fa-medal"></i>
				   <span class="hidden-xs"> <?=translate('employee_scores')?></span>
				</a>
			</li>
			<li <?=($this->session->flashdata('active') == 6 ? 'class="active"' : '');?>>
				<a href="#employee_cost_per_hour" data-toggle="tab">
				   <i class="fas fa-money-bill-wave"></i>
				   <span class="hidden-xs"> <?=translate('cost_per_hour')?></span>
				</a>
			</li>
		</ul>

		<div class="tab-content">
			<div class="tab-pane box <?=(empty($this->session->flashdata('active')) ? 'active' : '');?>" id="setting">
				<?php echo form_open($this->uri->uri_string(), array( 'class' 	=> 'validate form-horizontal form-bordered' )); ?>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('name')?></label>
					<div class="col-md-6">
						<input type="text" class="form-control" name="institute_name" value="<?=set_value('institute_name', $global_config['institute_name'])?>" />
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('mobile_no');?></label>
					<div class="col-md-6">
						<input type="text" class="form-control" name="mobileno" value="<?=set_value('mobileno', $global_config['mobileno'])?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('address');?></label>
					<div class="col-md-6">
						<textarea name="address" rows="2" class="form-control" aria-required="true"><?=set_value('address', $global_config['address'])?></textarea>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('email');?></label>
					<div class="col-md-6">
						<input type="text" class="form-control" name="institute_email" value="<?=set_value('institute_email', $global_config['institute_email'])?>" />
					</div>
				</div>
				
				<div class="form-group">
					<label class="col-md-3 control-label">
						<?=translate('currency');?>
					</label>
					<div class="col-md-6">
						<input type="text" class="form-control" name="currency" value="<?=set_value('currency', $global_config['currency'])?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('currency_symbol');?></label>
					<div class="col-md-6">
						<input type="text" class="form-control" name="currency_symbol" value="<?=set_value('currency_symbol', $global_config['currency_symbol'])?>" />
					</div>
				</div>
               
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('language');?></label>
					<div class="col-md-6">
						<?php
						$languages = $this->db->select('id,lang_field,name')->where('status', 1)->get('language_list')->result();
						foreach ($languages as $lang) {
							$array[$lang->lang_field] = ucfirst($lang->name);
						}
						echo form_dropdown("translation", $array, set_value('translation', $global_config['translation']), "class='form-control' data-plugin-selectTwo 
							data-width='100%' data-minimum-results-for-search='Infinity' ");
						?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('session');?></label>
					<div class="col-md-6">
						<?php
						$arrayYear = array("" => translate('select'));
						$years = $this->db->get('businessyear')->result();
						foreach ($years as $year) {
							$arrayYear[$year->id] = $year->year;
						}
						echo form_dropdown("session_id", $arrayYear, set_value('session_id', $global_config['session_id']), "class='form-control' required
						data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
						?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('timezone');?></label>
					<div class="col-md-6">
						<?php
						$timezones = $this->app_lib->timezone_list();
						echo form_dropdown("timezone", $timezones, set_value('timezone', $global_config['timezone']), "class='form-control populate' required id='timezones' 
						data-plugin-selectTwo data-width='100%'");
						?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">
						<?=translate('animations');?>
					</label>
					<div class="col-md-6">
						<?php
						$getAnimationslist = $this->app_lib->getAnimationslist();
						echo form_dropdown("animations", $getAnimationslist, set_value('animations', $global_config['animations']), "class='form-control populate' required
						id='timezones' data-plugin-selectTwo data-width='100%'");
						?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">
						<?=translate('preloader_backend');?>
					</label>
					<div class="col-md-6">
						<?php
						$getPreloaderlist = array('1' => translate('yes'), '2' => translate('no'));
						echo form_dropdown("preloader_backend", $getPreloaderlist, set_value('preloader_backend', $global_config['preloader_backend']), "class='form-control' required
						data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
						?>
					</div>
				</div>
				
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('footer_text');?></label>
					<div class="col-md-6">
						<input type="text" class="form-control" name="footer_text" value="<?=set_value('footer_text', $global_config['footer_text'])?>" />
					</div>
				</div>
				
				<footer class="panel-footer mt-lg">
					<div class="row">
						<div class="col-md-2 col-sm-offset-3">
							<button type="submit" class="btn btn btn-default btn-block" name="submit" value="setting">
								<i class="fas fa-plus-circle"></i> <?=translate('save');?>
							</button>
						</div>
					</div>
				</footer>
				<?php echo form_close(); ?>
			</div>

			<div class="tab-pane box <?= ($this->session->flashdata('active') == 2 ? 'active' : ''); ?>" id="theme">
				<?= form_open($this->uri->uri_string(), ['class' => 'needs-validation', 'novalidate' => '']); ?>

				<div class="form-group row">
					<label class="col-2 col-md-2 col-form-label"><?= translate('theme') ?></label>
					<div class="col-8 col-md-8">
						<div class="d-flex flex-wrap gap-1">
							<label class="theme-box text-center">
								<input type="radio" name="dark_skin" value="false" <?= ($theme_config['dark_skin'] == 'false' ? 'checked' : ''); ?>>
								<div class="theme-img">
									<img src="<?= base_url('assets/images/theme/light.png') ?>" class="img-fluid" alt="Light Theme">
								</div>
								<div class="small mt-1"><?= translate('light_mode') ?></div>
							</label>

							<label class="theme-box text-center">
								<input type="radio" name="dark_skin" value="true" <?= ($theme_config['dark_skin'] == 'true' ? 'checked' : ''); ?>>
								<div class="theme-img">
									<img src="<?= base_url('assets/images/theme/dark.png') ?>" class="img-fluid" alt="Dark Theme">
								</div>
								<div class="small mt-1"><?= translate('dark_mode') ?></div>
							</label>
						</div>
					</div>
				</div>

				<div class="form-group row mt-4">
					<label class="col-2 col-md-2 col-form-label"><?= translate('border') ?></label>
					<div class="col-8 col-md-8">
						<div class="d-flex flex-wrap gap-1">
							<label class="theme-box text-center">
								<input type="radio" name="border_mode" value="true" <?= ($theme_config['border_mode'] == 'true' ? 'checked' : '') ?>>
								<div class="theme-img">
									<img src="<?= base_url('assets/images/theme/rounded.png') ?>" class="img-fluid" alt="Rounded">
								</div>
								<div class="small mt-1"><?= translate('rounded') ?></div>
							</label>

							<label class="theme-box text-center">
								<input type="radio" name="border_mode" value="false" <?= ($theme_config['border_mode'] == 'false' ? 'checked' : '') ?>>
								<div class="theme-img">
									<img src="<?= base_url('assets/images/theme/square.png') ?>" class="img-fluid" alt="Square">
								</div>
								<div class="small mt-1"><?= translate('square') ?></div>
							</label>
						</div>
					</div>
				</div>

				<div class="form-group text-center mt-4">
					<button type="submit" class="btn btn-primary px-4" name="submit" value="theme">
						<i class="fas fa-save"></i> <?= translate('save'); ?>
					</button>
				</div>

				<?= form_close(); ?>
			</div>
			<style>
			.theme-box-wrapper {
				flex: 1 1 120px;
				max-width: 150px;
				text-align: center;
			}

			.theme-box {
				border: 1px solid #ddd;
				border-radius: 8px;
				padding: 10px;
				background-color: #fff;
				transition: box-shadow 0.3s ease;
			}

			.theme-box:hover {
				box-shadow: 0 0 10px rgba(0,0,0,0.1);
			}

			.theme-img img {
				max-width: 100%;
				height: auto;
				display: block;
				margin: 5px auto;
			}

			@media (max-width: 768px) {
				.form-group .control-label {
					text-align: left !important;
					margin-bottom: 5px;
				}
				.theme-box-wrapper {
					max-width: 100%;
				}
			}

			</style>
			<div class="tab-pane box <?=($this->session->flashdata('active') == 3 ? 'active' : '');?>" id="upload">
				<?php echo form_open_multipart($this->uri->uri_string(), array('class' 	=> 'validate')); ?>
				<!-- all logo -->
				<div class="headers-line">
					<i class="fab fa-envira"></i> <?=translate('logo');?>
				</div>
				<?php $system_logo = $this->app_lib->get_image_url('settings/' . $global_config['system_logo']); ?>
				<div class="row">
					<div class="col-md-3">
						<div class="form-group">
							<label class="control-label"><?=translate('system_logo');?></label>
							<input type="file" name="system_logo" class="dropify" data-allowed-file-extensions="png" data-default-file="<?php echo html_escape($system_logo); ?>" />
						</div>
					</div>
				</div>

				<footer class="panel-footer">
					<div class="row">
						<div class="col-md-2 col-sm-offset-10">
							<button type="submit" class="btn btn btn-default btn-block" name="submit" value="logo">
								<i class="fas fa-upload"></i> <?=translate('upload')?>
							</button>
						</div>
					</div>
				</footer>
				<?php echo form_close(); ?>
			</div>
			<div class="tab-pane box <?=($this->session->flashdata('active') == 4 ? 'active' : '');?>" id="file_settings">
				<?php echo form_open('settings/file_types_save', array( 'class' 	=> 'frm-submit-msg' )); ?>
				<div class="mb-lg">
					<!-- settings for image -->
					<div class="headers-line mt-lg">
						<i class="fas fa-images"></i> Settings For Image
					</div>

					<div class="form-group">
						<label class="control-label">Allowed Extension <span class="required">*</span></label>
						<textarea class="form-control" rows="4" name="image_extension" placeholder="" autocomplete="off"><?php echo $global_config['image_extension'] ?></textarea>
						<span class="error"></span>
					</div>
					<div class="form-group">
						<label class="control-label">Upload Size (in KB) <span class="required">*</span></label>
						<input type="text" class="form-control" name="image_size" autocomplete="off"  value="<?=$global_config['image_size']?>" />
						<span class="error"></span>
					</div>

					<!-- settings for file -->
					<div class="headers-line mt-lg">
						<i class="far fa-folder-open"></i> Settings For Files
					</div>
					<div class="form-group">
						<label class="control-label">Allowed Extension <span class="required">*</span></label>
						<textarea class="form-control" rows="4" name="file_extension" placeholder="" autocomplete="off"><?php echo $global_config['file_extension'] ?></textarea>
						<span class="error"></span>
					</div>
					<div class="form-group">
						<label class="control-label">Upload Size (in KB) <span class="required">*</span></label>
						<input type="text" class="form-control" autocomplete="off" name="file_size" value="<?=$global_config['file_size']?>" />
						<span class="error"></span>
					</div>
				</div>
				<footer class="panel-footer">
					<div class="row">
						<div class="col-md-2 col-sm-offset-10">
							<button type="submit" class="btn btn btn-default btn-block" name="submit" value="file_types" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
								<i class="fas fa-plus-circle"></i> <?=translate('save')?>
							</button>
						</div>
					</div>
				</footer>
				<?php echo form_close(); ?>
			</div>
			<div class="tab-pane box <?=($this->session->flashdata('active') == 5 ? 'active' : '');?>" id="employee_scores">
				<?php echo form_open($this->uri->uri_string(), array( 'class' 	=> 'validate form-horizontal form-bordered' )); ?>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('task_completion_ratio (%)')?></label>
					<div class="col-md-6">
						<input type="number" class="form-control" name="completion_ratio" value="<?=set_value('completion_ratio', $global_config['completion_ratio'])?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('quality_work_ratio (%)');?></label>
					<div class="col-md-6">
						<input type="number" class="form-control" name="quality_score" value="<?=set_value('quality_score', $global_config['quality_score'])?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('work_summary_ratio (%)');?></label>
					<div class="col-md-6">
						<input type="number" class="form-control" name="work_summary" value="<?=set_value('work_summary', $global_config['work_summary'])?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('attendance_ratio (%)');?></label>
					<div class="col-md-6">
						<input type="number" class="form-control" name="attendance_score" value="<?=set_value('attendance_score', $global_config['attendance_score'])?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('warning_penalty_ratio (%)');?></label>
					<div class="col-md-6">
						<input type="number" class="form-control" name="warning_penalty" value="<?=set_value('warning_penalty', $global_config['warning_penalty'])?>" />
					</div>
				</div>
				<footer class="panel-footer mt-lg">
					<div class="row">
						<div class="col-md-2 col-sm-offset-3">
							<button type="submit" class="btn btn btn-default btn-block" name="submit" value="employee_scores">
								<i class="fas fa-plus-circle"></i> <?=translate('save');?>
							</button>
						</div>
					</div>
				</footer>
				<?php echo form_close(); ?>
			</div>
			<div class="tab-pane box <?=($this->session->flashdata('active') == 6 ? 'active' : '');?>" id="employee_cost_per_hour">
				<?php echo form_open($this->uri->uri_string(), array( 'class' 	=> 'validate form-horizontal form-bordered' )); ?>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('cost_per_hour');?></label>
					<div class="col-md-6">
						<input type="float" class="form-control" name="cost_per_hour" value="<?=set_value('cost_per_hour', $global_config['cost_per_hour'])?>" />
					</div>
				</div>
				<footer class="panel-footer mt-lg">
					<div class="row">
						<div class="col-md-2 col-sm-offset-3">
							<button type="submit" class="btn btn btn-default btn-block" name="submit" value="cost_per_hour">
								<i class="fas fa-plus-circle"></i> <?=translate('save');?>
							</button>
						</div>
					</div>
				</footer>
				<?php echo form_close(); ?>
			</div>
		</div>
	</div>
</section>