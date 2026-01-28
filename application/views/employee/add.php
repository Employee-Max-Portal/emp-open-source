<?php $widget = ((in_array(loggedin_role_id(), [1, 2, 3, 5])) ? '4' : '6'); ?>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
				<div class="panel-heading">
                    <div class="panel-btn">
						<a href="javascript:void(0);" onclick="mfp_modal('#multipleImport')" class="btn btn-circle btn-default mb-sm">
							<i class="fas fa-plus-circle"></i> <?=translate('multiple_import')?>
						</a>
                    </div>
					<h4 class="panel-title">
						<i class="far fa-user-circle"></i> <?=translate('add_employee')?>
					</h4>
				</div>
			<?php echo form_open_multipart($this->uri->uri_string()); ?>
				<div class="panel-body">
					<!-- academic details-->
					<div class="headers-line mt-md">
						<i class="fas fa-school"></i> <?=translate('official_details')?>
					</div>
					<div class="row">
						<div class="col-md-<?=$widget?> mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('business')?> <span class="required">*</span></label>
								<?php
									$arrayBranch = $this->app_lib->getSelectList('branch');
									echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='branch_id'
									data-plugin-selectTwo data-width='100%'");
								?>
								<span class="error"><?php echo form_error('branch_id'); ?></span>
							</div>
						</div>

						<div class="col-md-<?=$widget?> mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('role')?> <span class="required">*</span></label>
								<?php
									$role_list = $this->app_lib->getRoles();
									echo form_dropdown("user_role", $role_list, set_value('user_role'), "class='form-control' id='user_role'
									data-plugin-selectTwo data-width='100%'");
								?>
								<span class="error"><?php echo form_error('user_role'); ?></span>
							</div>
						</div>
						<div class="col-md-<?=$widget?> mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('joining_date')?> <span class="required">*</span></label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fas fa-birthday-cake"></i></span>
									<input type="text" class="form-control" name="joining_date" data-plugin-datepicker data-plugin-options='{ "todayHighlight" : true }'
									autocomplete="off" value="<?=set_value('joining_date')?>" />
								</div>
								<span class="error"><?php echo form_error('joining_date'); ?></span>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-4 mb-sm" id="designation_div">
							<div class="form-group">
								<label class="control-label"><?=translate('designation')?> <span class="required">*</span></label>
								<?php
									$department_list = $this->app_lib->getDesignation($branch_id);
									echo form_dropdown("designation_id", $department_list, set_value('designation_id'), "class='form-control' id='designation_id'
									data-plugin-selectTwo data-width='100%'");
								?>
								<span class="error"><?php echo form_error('designation_id'); ?></span>
							</div>
						</div>
						<div class="col-md-4 mb-sm" id="department_div">
							<div class="form-group">
								<label class="control-label"><?=translate('department')?> <span class="required">*</span></label>
								<?php
									$department_list = $this->app_lib->getDepartment($branch_id);
									echo form_dropdown("department_id", $department_list, set_value('department_id'), "class='form-control' id='department_id'
									data-plugin-selectTwo data-width='100%'");
								?>
								<span class="error"><?php echo form_error('department_id'); ?></span>
							</div>
						</div>
						<div class="col-md-4 mb-sm" id="type_div">
							<div class="form-group">
								<label class="control-label"><?=translate('employee_type')?> <span class="required">*</span></label>
								<?php
									$employee_type_options = [
										'intern' => 'Intern',
										'probation' => 'Probation',
										'regular' => 'Regular'
									];
									echo form_dropdown(
										'employee_type', 
										$employee_type_options, 
										set_value('employee_type'), 
										"class='form-control' id='employee_type' 
										data-plugin-selectTwo data-width='100%'"
									);
								?>
								<span class="error"><?php echo form_error('employee_type'); ?></span>
							</div>
						</div>

					</div>
					<div class="row mb-lg">
						<div class="col-md-4 mb-sm" id="qualification_div">
							<div class="form-group">
								<label class="control-label"><?= translate('qualification') ?> <span class="required">*</span></label>
								<?php
								$qualifications = array(
									'' => translate('select'),
									'SSC' => 'SSC',
									'HSC' => 'HSC',
									'Diploma' => 'Diploma',
									'Undergraduate' => 'Undergraduate',
									'Graduate' => 'Graduate',
									'Post Graduate' => 'Post Graduate',
									'Professional Courses' => 'Professional Courses',
									'Training / Workshop' => 'Training / Workshop',
									'Others' => 'Others'
								);

								echo form_dropdown(
									'qualification',
									$qualifications,
									set_value('qualification'),
									"class='form-control' id='qualification_select' data-plugin-selectTwo data-width='100%'"
								);
								?>
								<span class="error"><?php echo form_error('qualification'); ?></span>

								<!-- Input field for 'Others' qualification -->
								<div id="other_qualification_input" class="mt-2" style="display: none;">
									<input type="text" name="other_qualification" value="<?= set_value('other_qualification') ?>" class="form-control" placeholder="<?= translate('Please specify your qualification') ?>">
									<span class="error"><?php echo form_error('other_qualification'); ?></span>
								</div>
							</div>
						</div>
						<script>
							$(document).ready(function () {
								$('#qualification_select').on('change', function () {
									if ($(this).val() === 'Others') {
										$('#other_qualification_input').slideDown();
									} else {
										$('#other_qualification_input').slideUp();
									}
								});

								$('#qualification_select').trigger('change');
								
								function updateEmployeeId() {
									var branchId = $('#branch_id').val();
									if (branchId) {
										$.ajax({
											url: '<?=base_url("employee/get_next_employee_id")?>',
											type: 'POST',
											data: { branch_id: branchId },
											success: function(response) {
												$('#employee_id').val(response);
											}
										});
									}
								}
								
								setTimeout(function() {
									$('#branch_id').on('change select2:select', updateEmployeeId);
								}, 500);

								// Role based field visibility
								$('#user_role').on('change', function() {
									var role = $(this).val();
									if (role == 11 || role == 12) {
										$('#designation_div, #department_div, #type_div, #qualification_div, #experience_div, #total_experience_div').hide();
									// Remove required asterisks for hidden fields
									$('#designation_div .required, #department_div .required, #type_div .required, #qualification_div .required').hide();
									} else {
										$('#designation_div, #department_div, #type_div, #qualification_div, #experience_div, #total_experience_div').show();
									// Show required asterisks for visible fields
									$('#designation_div .required, #department_div .required, #type_div .required, #qualification_div .required').show();
									}
								});
								$('#user_role').trigger('change');
							});
						</script>


						<div class="col-md-4 mb-sm" id="experience_div">
							<div class="form-group">
								<label class="control-label"><?=translate('experience_details')?></label>
								<textarea class="form-control" rows="1" name="experience_details"></textarea>
							</div>
						</div>
						<div class="col-md-4 mb-sm" id="total_experience_div">
							<div class="form-group">
								<label class="control-label"><?=translate('total_experience')?></label>
								<input type="text" class="form-control" name="total_experience" value="<?=set_value('total_experience')?>" autocomplete="off" />
								
							</div>
						</div>
					</div>

					<!-- employee details -->
					<div class="headers-line mt-md">
						<i class="fas fa-user-check"></i> <?=translate('employee_details')?>
					</div>
					<div class="row">
						<div class="col-md-4 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('name')?> <span class="required">*</span></label>
								<div class="input-group">
									<span class="input-group-addon"><i class="far fa-user"></i></span>
									<input type="text" class="form-control" name="name" value="<?=set_value('name')?>" autocomplete="off" />
								</div>
								<span class="error"><?php echo form_error('name'); ?></span>
							</div>
						</div>
						<div class="col-md-4 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('gender')?></label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa-solid fa-mars-and-venus"></i></span>
									<?php
										$array = array(
											"" => translate('select'),
											"male" => translate('male'),
											"female" => translate('female')
										);
										echo form_dropdown("sex", $array, set_value('sex'), "class='form-control' data-plugin-selectTwo data-width='100%'");
									?>
								</div>
							</div>
						</div>
						<div class="col-md-4 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('telegram_id')?></label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa-regular fa-comment"></i></span>
									<input type="text" class="form-control" name="telegram_id" value="<?=set_value('telegram_id')?>" autocomplete="off" />
								</div>
								<span class="error"><?php echo form_error('telegram_id'); ?></span>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-4 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('religion')?></label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa-solid fa-hands-praying"></i></span>
									<?php
										$religions = array(
											'' => translate('select'),
											'Islam' => 'Islam',
											'Hinduism' => 'Hinduism',
											'Christianity' => 'Christianity',
											'Buddhism' => 'Buddhism',
											'Other' => 'Other'
										);

										echo form_dropdown(
											'religion',
											$religions,
											set_value('religion'),
											"class='form-control' data-plugin-selectTwo data-width='100%'"
										);
									?>
								</div>
							</div>
						</div>

						<div class="col-md-4 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('blood_group')?></label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa-solid fa-droplet"></i></span>
									<?php
										$bloodArray = $this->app_lib->getBloodgroup();
										echo form_dropdown("blood_group", $bloodArray, set_value("blood_group"), "class='form-control populate' data-plugin-selectTwo
										data-width='100%'");
									?>
								</div>
							</div>
						</div>

						<div class="col-md-4 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('birthday')?> </label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fas fa-birthday-cake"></i></span>
									<input class="form-control" name="birthday" autocomplete="off" value="<?=set_value('birthday')?>" data-plugin-datepicker
									data-plugin-options='{ "startView": 2 }' type="text">
								</div>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-6 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('mobile_no')?> <span class="required">*</span></label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fas fa-phone-volume"></i></span>
									<input class="form-control" name="mobile_no" type="text" value="<?=set_value('mobile_no')?>" autocomplete="off" />
								</div>
								<span class="error"><?php echo form_error('mobile_no'); ?></span>
							</div>
						</div>
						<div class="col-md-6 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('email')?> <span class="required">*</span></label>
								<div class="input-group">
									<span class="input-group-addon"><i class="far fa-envelope-open"></i></span>
									<input type="text" class="form-control" name="email" id="email" value="<?=set_value('email')?>" autocomplete="off" />
								</div>
								<span class="error"><?php echo form_error('email'); ?></span>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-6 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('present_address')?> <span class="required">*</span></label>
								<textarea class="form-control" rows="2" name="present_address" placeholder="<?=translate('present_address')?>" ><?=set_value('present_address')?></textarea>
							</div>
							<span class="error"><?php echo form_error('present_address'); ?></span>
						</div>
						<div class="col-md-6 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('permanent_address')?></label>
								<textarea class="form-control" rows="2" name="permanent_address" placeholder="<?=translate('permanent_address')?>" ><?=set_value('permanent_address')?></textarea>
							</div>
						</div>
					</div>

					<div class="row mb-md">
						<div class="col-md-12">
							<div class="form-group">
								<label for="input-file-now"><?=translate('profile_picture')?></label>
								<input type="file" name="user_photo" class="dropify" />
								<span class="error"><?php echo form_error('user_photo'); ?></span>
							</div>
						</div>
					</div>

					<!-- login details -->
					<div class="headers-line">
						<i class="fas fa-user-lock"></i> <?=translate('login_details')?>
					</div>

					<div class="row mb-lg">
						<div class="col-md-6 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('employee_id')?> <span class="required">*</span></label>
								<div class="input-group">
									<span class="input-group-addon"><i class="far fa-user"></i></span>
									<?php
										$selected_branch = set_value('branch_id');
										$base_id = 20000;
										if ($selected_branch == 2) {
											$base_id = 10000;
										} elseif ($selected_branch == 10) {
											$base_id = 30000;
										}
										
										$this->db->select('staff_id');
										$this->db->where('staff_id >=', $base_id);
										$this->db->where('staff_id <', $base_id + 10000);
										$this->db->order_by('staff_id', 'DESC');
										$this->db->limit(1);
										$query = $this->db->get('staff');
										
										if ($query->num_rows() > 0) {
											$last_id = $query->row()->staff_id;
											$next_id = $last_id + 1;
										} else {
											$next_id = $base_id + 1;
										}
										$default_employee_id = set_value('username') ? set_value('username') : $next_id;
									?>
									<input type="text" class="form-control" name="username" id="employee_id" value="<?=$default_employee_id?>" autocomplete="off" />
								</div>
								<span class="error"><?php echo form_error('username'); ?></span>
							</div>
						</div>
						<div class="col-md-3 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('password')?> <span class="required">*</span></label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fas fa-unlock-alt"></i></span>
									<input type="password" class="form-control" name="password" value="<?=set_value('password')?>" />
								</div>
								<span class="error"><?php echo form_error('password'); ?></span>
							</div>
						</div>
						<div class="col-md-3 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('retype_password')?> <span class="required">*</span></label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fas fa-unlock-alt"></i></span>
									<input type="password" class="form-control" name="retype_password" value="<?=set_value('retype_password')?>" />
								</div>
								<span class="error"><?php echo form_error('retype_password'); ?></span>
							</div>
						</div>
					</div>


					<!-- bank details -->
					<div class="headers-line">
						<i class="fas fa-university"></i> <?=translate('bank_details')?>
					</div>
					<div class="mb-sm checkbox-replace">
						<label class="i-checks"><input type="checkbox" name="chkskipped" id="chk_bank_skipped" value="true" <?=set_checkbox('chkskipped', 'true')?> >
							<i></i> <?=translate('skipped_bank_details')?>
						</label>
					</div>
					<div id="bank_details_form" <?php if(!empty(set_value('chkskipped'))) { ?> style="display: none" <?php } ?>>
						<div class="row">
							<div class="col-md-4 mb-sm">
								<div class="form-group">
									<label class="control-label"><?=translate('bank') . " " . translate('name')?> <span class="required">*</span></label>
									<?php
										$banks = array(
											'' => translate('select'),
											'Dutch-Bangla Bank Limited (DBBL)' => 'Dutch-Bangla Bank Limited (DBBL)',
											'BRAC Bank Limited' => 'BRAC Bank Limited',
											'Islami Bank Bangladesh Limited' => 'Islami Bank Bangladesh Limited',
											'Prime Bank Limited' => 'Prime Bank Limited',
											'Bank Asia Limited' => 'Bank Asia Limited',
											'Eastern Bank Limited (EBL)' => 'Eastern Bank Limited (EBL)',
											'City Bank Limited' => 'City Bank Limited',
											'Standard Chartered Bank' => 'Standard Chartered Bank',
											'Other' => 'Other'
										);

										echo form_dropdown(
											'bank_name',
											$banks,
											set_value('bank_name'),
											"class='form-control' data-plugin-selectTwo data-width='100%'"
										);
									?>
									<span class="error"><?php echo form_error('bank_name'); ?></span>
								</div>
							</div>

							<div class="col-md-4 mb-sm">
								<div class="form-group">
									<label class="control-label"><?=translate('account_holder_name')?> <span class="required">*</span></label>
									<input type="text" class="form-control" name="holder_name" value="<?=set_value('holder_name')?>" />
									<span class="error"><?php echo form_error('holder_name'); ?></span>
								</div>
							</div>
							<div class="col-md-4 mb-sm">
								<div class="form-group">
									<label class="control-label"><?=translate('bank_branch')?> <span class="required">*</span></label>
									<input type="text" class="form-control" name="bank_branch" value="<?=set_value('bank_branch')?>" />
									<span class="error"><?php echo form_error('bank_branch'); ?></span>
								</div>
							</div>
						</div>

						<div class="row mb-lg">
							<div class="col-md-4 mb-sm">
								<div class="form-group">
									<label class="control-label"><?=translate('bank_address')?></label>
									<input type="text" class="form-control" name="bank_address" value="<?=set_value('bank_address')?>" />
								</div>
							</div>
							<div class="col-md-4 mb-sm">
								<div class="form-group">
									<label class="control-label"><?=translate('routing_no')?></label>
									<input type="text" class="form-control" name="ifsc_code" value="<?=set_value('ifsc_code')?>" />
								</div>
							</div>
							<div class="col-md-4 mb-sm">
								<div class="form-group">
									<label class="control-label"><?=translate('account_no')?> <span class="required">*</span></label>
									<input type="text" class="form-control" name="account_no" value="<?=set_value('account_no')?>" />
									<span class="error"><?php echo form_error('account_no'); ?></span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<footer class="panel-footer">
					<div class="row">
						<div class="col-md-offset-10 col-md-2">
							<button type="submit" name="submit" value="save" class="btn btn btn-default btn-block"> <i class="fas fa-plus-circle"></i> <?=translate('save')?></button>
						</div>
					</div>
				</footer>
			<?php echo form_close();?>
		</section>
	</div>
</div>

<!-- multiple import modal -->
<div id="multipleImport" class="zoom-anim-dialog modal-block modal-block-lg mfp-hide">
    <section class="panel">
        <div class="panel-heading">
            <h4 class="panel-title"><i class="fas fa-plus-circle"></i> <?php echo translate('multiple_import'); ?></h4>
        </div>
        <?php echo form_open_multipart('employee/csv_import', array('class' => 'form-horizontal', 'id' => 'importCSV')); ?>
            <div class="panel-body">
            	<div class="alert-danger" id="errorList" style="display: none; padding: 8px;">rthtrhtr</div>
				<div class="form-group mt-md">
					<div class="col-md-12 mb-md">
						<a class="btn btn-default pull-right" href="<?=base_url('employee/csv_Sampledownloader')?>">
							<i class='fas fa-file-download'></i> Download Sample Import File
						</a>
					</div>
					<br>
					<div class="col-md-12">
						<div class="alert alert-subl">
							<strong>Instructions :</strong><br/>
							1. Download the first sample file.<br/>
							2. Open the downloaded "CSV" file and carefully fill in the employee details.<br/>
							3. The date you are trying to enter the "Date Of Birth" and "Joining Date" column make sure the date format is Y-m-d (<?=date('Y-m-d')?>).<br/>
						</div>
					</div>
				</div> 
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('business')?> <span class="required">*</span></label>
					<div class="col-md-9">
						<?php
							$arrayBranch = $this->app_lib->getSelectList('branch');
							echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='branchID_mod'
							data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
						?>
						<span class="error"></span>
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('role')?> <span class="required">*</span></label>
					<div class="col-md-9">
						<?php
							$role_list = $this->app_lib->getRoles();
							echo form_dropdown("user_role", $role_list, set_value('user_role'), "class='form-control'
							data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
						?>
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('designation')?> <span class="required">*</span></label>
					<div class="col-md-9">
						<?php
							$department_list = $this->app_lib->getDesignation($branch_id);
							echo form_dropdown("designation_id", $department_list, set_value('designation_id'), "class='form-control' id='designationID_mod'
							data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
						?>
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('department')?> <span class="required">*</span></label>
					<div class="col-md-9">
						<?php
							$department_list = $this->app_lib->getDepartment($branch_id);
							echo form_dropdown("department_id", $department_list, set_value('department_id'), "class='form-control' id='departmentID_mod'
							data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
						?>
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group mb-xs">
					<label class="control-label col-md-3">Select CSV File <span class="required">*</span></label>
					<div class="col-md-9">
						<input type="file" name="userfile" class="dropify" data-height="70" data-allowed-file-extensions="csv" />
						<span class="error"></span>
					</div>
				</div>
            </div>
            <footer class="panel-footer">
                <div class="row">
                    <div class="col-md-12 text-right">
                        <button type="submit" class="btn btn-default mr-xs" id="bankaddbtn" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
                            <i class="fas fa-plus-circle"></i> <?php echo translate('import'); ?>
                        </button>
                        <button class="btn btn-default modal-dismiss"><?php echo translate('close'); ?></button>
                    </div>
                </div>
            </footer>
        <?php echo form_close(); ?>
    </section>
</div>