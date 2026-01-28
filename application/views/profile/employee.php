<?php $disabled = (is_admin_loggedin() ?  '' : 'disabled'); ?>
<div class="row appear-animation" data-appear-animation="<?=$global_config['animations'] ?>">
	<div class="col-md-12 mb-lg">
		<div class="profile-head social">
			<div class="col-md-12 col-lg-4 col-xl-3">
				<div class="image-content-center user-pro">
					<div class="preview">
						<img src="<?=get_image_url('staff', $staff['photo'])?>">
					</div>
				</div>
			</div>
			<div class="col-md-12 col-lg-5 col-xl-5">
				<h5><?php echo $staff['name']; ?></h5>
				<p><?php echo ucfirst($staff['role'])?> / <?php echo html_escape($staff['designation_name']); ?></p>
				<ul>
					<li><div class="icon-holder" data-toggle="tooltip" data-original-title="<?=translate('employee_id')?>"><i class="fas fa-user"></i></div> <?=(!empty($staff['username']) ? $staff['username'] : 'N/A'); ?></li>
					<li><div class="icon-holder" data-toggle="tooltip" data-original-title="<?=translate('department')?>"><i class="fas fa-user-tie"></i></div> <?=(!empty($staff['department_name']) ? $staff['department_name'] : 'N/A'); ?></li>
					<li><div class="icon-holder" data-toggle="tooltip" data-original-title="<?=translate('birthday')?>"><i class="fas fa-birthday-cake"></i></div> <?=_d($staff['birthday'])?></li>
					<li><div class="icon-holder" data-toggle="tooltip" data-original-title="<?=translate('joining_date')?>"><i class="far fa-calendar-alt"></i></div> <?=_d($staff['joining_date'])?></li>
					<li><div class="icon-holder" data-toggle="tooltip" data-original-title="<?=translate('mobile_no')?>"><i class="fas fa-phone"></i></div> <?=(!empty($staff['mobileno']) ? $staff['mobileno'] : 'N/A'); ?></li>
					<li><div class="icon-holder" data-toggle="tooltip" data-original-title="<?=translate('email')?>"><i class="far fa-envelope"></i></div> <?=$staff['email']?></li>
					<li><div class="icon-holder" data-toggle="tooltip" data-original-title="<?=translate('present_address')?>"><i class="fas fa-home"></i></div> <?=(!empty($staff['present_address']) ? $staff['present_address'] : 'N/A'); ?></li>
				</ul>
			</div>
		</div>
	</div>

	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="far fa-edit"></i> <?php echo translate('profile'); ?></h4>
			</header>
            <?php echo form_open_multipart($this->uri->uri_string()); ?>
				<div class="panel-body">
					<fieldset>
						<input type="hidden" name="staff_id" id="staff_id" value="<?php echo $staff['id']; ?>">
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
										<input class="form-control" name="name" type="text" value="<?=set_value('name', $staff['name'])?>" />
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
											echo form_dropdown("sex", $array, set_value('sex', $staff['sex']), "class='form-control' data-plugin-selectTwo
											data-width='100%' data-minimum-results-for-search='Infinity'");
										?>
									</div>
								</div>
							</div>
							<div class="col-md-4 mb-sm">
								<div class="form-group">
									<label class="control-label"><?=translate('telegram_id')?> </label>
									<div class="input-group">
										<span class="input-group-addon"><i class="fa-regular fa-comment"></i></span>
										<input class="form-control" name="telegram_id" type="text" value="<?=set_value('telegram_id', $staff['telegram_id'])?>" />
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

											echo form_dropdown("religion", $religions, set_value('religion', $staff['religion']), "class='form-control' data-plugin-selectTwo
											data-width='100%' data-minimum-results-for-search='Infinity'");
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
											echo form_dropdown("blood_group", $bloodArray, set_value('blood_group', $staff['blood_group']), "class='form-control populate' data-plugin-selectTwo
											data-width='100%' data-minimum-results-for-search='Infinity' ");
										?>
									</div>
								</div>
							</div>

							<div class="col-md-4 mb-sm">
								<div class="form-group">
									<label class="control-label"><?=translate('birthday')?> </label>
									<div class="input-group">
										<span class="input-group-addon"><i class="fas fa-birthday-cake"></i></span>
										<input class="form-control" name="birthday" value="<?=set_value('birthday', $staff['birthday'])?>" data-plugin-datepicker data-plugin-options='{ "startView": 2 }' autocomplete="off" type="text">
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
										<input class="form-control" name="mobile_no" type="text" value="<?=set_value('mobile_no', $staff['mobileno'])?>" />
									</div>
									<span class="error"><?php echo form_error('mobile_no'); ?></span>
								</div>
							</div>
							<div class="col-md-6 mb-sm">
								<div class="form-group">
									<label class="control-label"><?=translate('email')?> <span class="required">*</span></label>
									<div class="input-group">
										<span class="input-group-addon"><i class="far fa-envelope-open"></i></span>
										<input type="text" class="form-control" name="email" id="email" value="<?=set_value('email', html_escape($staff['email']))?>" />
									</div>
									<span class="error"><?php echo form_error('email'); ?></span>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-6 mb-sm">
								<div class="form-group">
									<label class="control-label"><?=translate('present_address')?> <span class="required">*</span></label>
									<textarea class="form-control" rows="2" name="present_address" placeholder="<?=translate('present_address')?>" ><?=set_value('present_address', $staff['present_address'])?></textarea>
									<span class="error"><?php echo form_error('present_address'); ?></span>
								</div>
							</div>
							<div class="col-md-6 mb-sm">
								<div class="form-group">
									<label class="control-label"><?=translate('permanent_address')?></label>
									<textarea class="form-control" rows="2" name="permanent_address" placeholder="<?=translate('permanent_address')?>" ><?=set_value('permanent_address', $staff['permanent_address'])?></textarea>
								</div>
							</div>
						</div>
						
						<div class="row mb-md">
							<div class="col-md-12 mb-lg">
								<div class="form-group">
									<label for="input-file-now"><?=translate('profile_picture')?></label>
									<input type="file" name="user_photo" class="dropify" data-default-file="<?=get_image_url('staff', $staff['photo'])?>"/>
									<span class="error"><?php echo form_error('user_photo'); ?></span>
								</div>
							</div>
							<input type="hidden" name="old_user_photo" value="<?=html_escape($staff['photo'])?>">
						</div>

<?php if (!is_superadmin_loggedin()) { ?>
						<!-- academic details-->
						<div class="headers-line">
							<i class="fas fa-school"></i> <?=translate('academic_details')?>
						</div>
						<div class="row">
							<div class="col-md-4 mb-sm">
								<div class="form-group">
									<label class="control-label"><?=translate('branch')?> <span class="required">*</span></label>
									<?php
										$arrayBranch = $this->app_lib->getSelectList('branch');
										echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id', $staff['branch_id']), "class='form-control' id='branch_id' disabled
										data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
									?>
									<span class="error"><?php echo form_error('branch_id'); ?></span>
								</div>
							</div>
							<div class="col-md-4 mb-sm">
								<div class="form-group">
									<label class="control-label"><?=translate('designation')?> <span class="required">*</span></label>
									<?php
										$designation_list = $this->app_lib->getDesignation($staff['branch_id']);
										echo form_dropdown("designation_id", $designation_list, set_value('designation_id', $staff['designation']), "class='form-control' id='designation_id' $disabled
										data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
									?>
									<span class="error"><?php echo form_error('designation_id'); ?></span>
								</div>
							</div>
							<div class="col-md-4 mb-sm">
								<div class="form-group">
									<label class="control-label"><?=translate('department')?> <span class="required">*</span></label>
									<?php
										$department_list = $this->app_lib->getDepartment($staff['branch_id']);
										echo form_dropdown("department_id", $department_list, set_value('department_id', $staff['department']), "class='form-control' id='department_id' $disabled
										data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
									?>
									<span class="error"><?php echo form_error('department_id'); ?></span>
								</div>
							</div>
						</div>

						<div class="row mb-lg">
							<div class="col-md-4 mb-sm">
								<div class="form-group">
									<label class="control-label"><?=translate('joining_date')?> <span class="required">*</span></label>
									<div class="input-group">
										<span class="input-group-addon"><i class="fas fa-birthday-cake"></i></span>
										<input type="text" class="form-control" name="joining_date" data-plugin-datepicker data-plugin-options='{ "todayHighlight" : true }' <?=$disabled?>
										autocomplete="off" value="<?=set_value('joining_date', $staff['joining_date'])?>">
									</div>
									<span class="error"><?php echo form_error('joining_date'); ?></span>
								</div>
							</div>
							<div class="col-md-4 mb-sm">
								<div class="form-group">
									<label class="control-label"><?=translate('qualification')?> <span class="required">*</span></label>
									<input type="text" class="form-control" name="qualification" <?=$disabled?> value="<?=set_value('qualification', $staff['qualification'])?>" />
									<span class="error"><?php echo form_error('qualification'); ?></span>
								</div>
							</div>
							<div class="col-md-4 mb-sm">
								<div class="form-group">
									<label class="control-label"><?=translate('role')?> <span class="required">*</span></label>
									<?php
										$role_list = $this->app_lib->getRoles();
										echo form_dropdown("user_role", $role_list, set_value('user_role', $staff['role_id']), "class='form-control' disabled
										data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
									?>
									<span class="error"><?php echo form_error('user_role'); ?></span>
								</div>
							</div>
						</div>
<?php } ?>
					</fieldset>
				</div>
				<div class="panel-footer">
					<div class="row">
						<div class="col-md-offset-9 col-md-3">
							<button class="btn btn-default btn-block" type="submit"><i class="fas fa-plus-circle"></i> <?php echo translate('update'); ?></button>
						</div>	
					</div>
				</div>
			<?php echo form_close(); ?>
		</section>
		
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-university"></i> <?=translate('bank_account')?></h4>
			</header>
			<div class="panel-body">
				<div class="text-right mb-sm">
					<a href="javascript:void(0);" onclick="mfp_modal('#addBankModal')" class="btn btn-circle btn-default mb-sm">
						<i class="fas fa-plus-circle"></i> <?=translate('add_bank')?>
					</a>
				</div>
				<div class="table-responsive mb-md">
					<table class="table table-bordered table-hover table-condensed mb-none">
					<thead>
						<tr>
							<th>#</th>
							<th><?=translate('bank_name')?></th>
							<th><?=translate('account_name')?></th>
							<th><?=translate('branch')?></th>
							<th><?=translate('bank_address')?></th>
							<th><?=translate('routing_no')?></th>
							<th><?=translate('account_no')?></th>
							<th><?=translate('actions')?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$count = 1;
						$this->db->where('staff_id', $staff['id']);
						$bankResult = $this->db->get('staff_bank_account')->result_array();
						if (count($bankResult)) {
							foreach($bankResult as $bank):
						?>
						<tr>
							<td><?php echo $count++?></td>
							<td><?php echo $bank['bank_name']; ?></td>
							<td><?php echo $bank['holder_name']; ?></td>
							<td><?php echo $bank['bank_branch']; ?></td>
							<td><?php echo $bank['bank_address']; ?></td>
							<td><?php echo $bank['ifsc_code']; ?></td>
							<td><?php echo $bank['account_no']; ?></td>
							<td class="min-w-c">
								<a href="javascript:void(0);" onclick="editStaffBank('<?=$bank['id']?>')" class="btn btn-circle icon btn-default">
									<i class="fas fa-pen-nib"></i>
								</a>
								<?php echo btn_delete('profile/bankaccount_delete/' . $bank['id']); ?>
							</td>
						</tr>
						<?php
							endforeach;
						}else{
							echo '<tr><td colspan="8"><h5 class="text-danger text-center">' . translate('no_information_available') . '</h5></td></tr>';
						}
						?>
					</tbody>
					</table>
				</div>
			</div>
		</section>
	</div>
</div>

<!-- Bank Details Add Modal -->
<div id="addBankModal" class="zoom-anim-dialog modal-block modal-block-primary mfp-hide">
	<section class="panel">
		<div class="panel-heading">
			<h4 class="panel-title"><i class="fas fa-plus-circle"></i> <?php echo translate('add') . " " . translate('bank'); ?></h4>
		</div>
		<?php echo form_open('profile/bank_account_create', array('class' => 'form-horizontal frm-submit')); ?>
			<div class="panel-body">
				<input type="hidden" name="staff_id" value="<?php echo $staff['id']; ?>">
				<div class="form-group mt-sm">
					<label class="col-md-3 control-label"><?php echo translate('bank') . " " . translate('name'); ?> <span class="required">*</span></label>
					<div class="col-md-9">
						<input type="text" class="form-control" name="bank_name" value= "Bank Asia LTD." id="abank_name" readonly />
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group mt-sm">
					<label class="col-md-3 control-label"><?php echo translate('holder_name'); ?> <span class="required">*</span></label>
					<div class="col-md-9">
						<input type="text" class="form-control" name="holder_name" id="aholder_name" />
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group mt-sm">
					<label class="col-md-3 control-label"><?php echo translate('bank_branch'); ?> <span class="required">*</span></label>
					<div class="col-md-9">
						<input type="text" class="form-control" name="bank_branch" id="abank_branch" />
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group mt-sm">
					<label class="col-md-3 control-label"><?php echo translate('routing_no'); ?></label>
					<div class="col-md-9">
						<input type="text" class="form-control" name="ifsc_code" id="aifsc_code" />
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group mt-sm">
					<label class="col-md-3 control-label"><?php echo translate('account_no'); ?> <span class="required">*</span></label>
					<div class="col-md-9">
						<input type="text" class="form-control" name="account_no" id="aaccount_no" />
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group mb-md">
					<label class="col-md-3 control-label"><?php echo translate('bank') . " " . translate('address'); ?></label>
					<div class="col-md-9">
						<input type="text" class="form-control" name="bank_address" id="abank_address" />
						<span class="error"></span>
					</div>
				</div>
			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-12 text-right">
						<button type="submit" class="btn btn-default" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
							<i class="fas fa-plus-circle"></i> <?php echo translate('save'); ?>
						</button>
						<button class="btn btn-default modal-dismiss"><?php echo translate('cancel'); ?></button>
					</div>
				</div>
			</footer>
		<?php echo form_close(); ?>
	</section>
</div>

<!-- Bank Details Edit Modal -->
<div id="editBankModal" class="zoom-anim-dialog modal-block modal-block-primary mfp-hide">
	<section class="panel">
		<header class="panel-heading">
			<h4 class="panel-title"><i class="far fa-edit"></i> <?php echo translate('edit') . " " . translate('bank_account'); ?></h4>
		</header>
		<?php echo form_open('profile/bank_account_update', array('class' => 'form-horizontal frm-submit')); ?>
			<div class="panel-body">
				<input type="hidden" name="bank_id" id="ebank_id" value="">
				<input type="hidden" name="staff_id" value="<?php echo $staff['id']; ?>">
				<div class="form-group mt-sm">
					<label class="col-md-3 control-label"><?php echo translate('bank') . " " . translate('name'); ?> <span class="required">*</span></label>
					<div class="col-md-9">
						<input type="text" class="form-control" name="bank_name" id="ebank_name" value="" />
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group mt-sm">
					<label class="col-md-3 control-label"><?php echo translate('holder_name'); ?> <span class="required">*</span></label>
					<div class="col-md-9">
						<input type="text" class="form-control" name="holder_name" id="eholder_name" value="" />
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group mt-sm">
					<label class="col-md-3 control-label"><?php echo translate('bank_branch'); ?> <span class="required">*</span></label>
					<div class="col-md-9">
						<input type="text" class="form-control" name="bank_branch" id="ebank_branch" value="" />
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group mt-sm">
					<label class="col-md-3 control-label"><?php echo translate('routing_no'); ?></label>
					<div class="col-md-9">
						<input type="text" class="form-control" name="ifsc_code" id="eifsc_code" value="" />
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group mt-sm">
					<label class="col-md-3 control-label"><?php echo translate('account_no'); ?> <span class="required">*</span></label>
					<div class="col-md-9">
						<input type="text" class="form-control" name="account_no" id="eaccount_no" value="" />
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group mb-md">
					<label class="col-md-3 control-label"><?php echo translate('bank') . " " . translate('address'); ?></label>
					<div class="col-md-9">
						<input type="text" class="form-control" name="bank_address" id="ebank_address" value="" />
						<span class="error"></span>
					</div>
				</div>
			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-12 text-right">
						<button type="submit" class="btn btn-default" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
							<?php echo translate('update'); ?>
						</button>
						<button class="btn btn-default modal-dismiss"><?php echo translate('cancel'); ?></button>
					</div>
				</div>
			</footer>
		<?php echo form_close(); ?>
	</section>
</div>

<script type="text/javascript">
function editStaffBank(id) {
	$.ajax({
		url: base_url + 'profile/getStaffBankDetails',
		type: 'POST',
		data: {'id': id},
		dataType: "json",
		success: function (data) {
			$('#ebank_id').val(data.id);
			$('#ebank_name').val(data.bank_name);
			$('#eholder_name').val(data.holder_name);
			$('#ebank_branch').val(data.bank_branch);
			$('#eifsc_code').val(data.ifsc_code);
			$('#eaccount_no').val(data.account_no);
			$('#ebank_address').val(data.bank_address);
			mfp_modal('#editBankModal');
		}
	});
}
</script>
