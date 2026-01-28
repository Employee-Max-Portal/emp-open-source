<?php $widget = ((in_array(loggedin_role_id(), [1, 2, 3, 5])) ? 'col-md-6' : 'col-md-offset-3 col-md-6'); ?>
<div class="row">
	<div class="col-md-12">
		
		<?php if (in_array(loggedin_role_id(), [1, 2, 3, 5])) : ?>
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><?=translate('select_ground')?></h4>
			<?php if (get_permission('advance_salary_manage', 'is_add')): ?>
				<div class="panel-btn">
					<a href="javascript:void(0);" id="advanceSalary" class="btn btn-default btn-circle" >
						<i class="fas fa-plus-circle"></i> Add Advance Salary
					</a>
				</div>
			<?php endif; ?>
			</header>
			<?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
				<div class="panel-body">
					<div class="row mb-sm">
						<?php if (in_array(loggedin_role_id(), [1, 2, 3, 5])) : ?>
						<div class="col-md-6 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('business'); ?> <span class="required">*</span></label>
								<?php
									$arrayBranch = $this->app_lib->getSelectList('branch');
									echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' required
									data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
								?>
							</div>
						</div>
					<?php endif; ?>
						<div class="<?=$widget?> mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('deduct_month')?> <span class="required">*</span></label>
								 <input type="text" class="form-control monthyear" required name="month_year" value="<?=set_value('month_year',date("Y-m"))?>" />
							</div>
						</div>
					</div>
				</div>
				<footer class="panel-footer">
					<div class="row">
						<div class="col-md-offset-10 col-md-2">
							<button type="submit" name="search" value="1" class="btn btn btn-default btn-block"><i class="fas fa-filter"></i> <?=translate('filter')?></button>
						</div>
					</div>
				</footer>
			<?php echo form_close(); ?>
		</section>
		<?php endif; ?>
		<section class="panel appear-animation" data-appear-animation="<?=$global_config['animations'] ?>" data-appear-animation-delay="100">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-users" aria-hidden="true"></i> <?=translate('advance_salary')?></h4>
			</header>
			<div class="panel-body">
				<table class="table table-bordered table-condensed table-hover mb-none table-export" id="advance-salary-table">
					<thead>
						<tr>
							<th width="10"><?=translate('sl')?></th>
							<th><?=translate('photo')?></th>
							<?php if (in_array(loggedin_role_id(), [1, 2, 3, 5])) : ?>
							<th><?=translate('business')?></th>
							<?php endif; ?>
							<th><?=translate('applicant')?></th>
							<th><?=translate('amount')?></th>
							<th><?=translate('deduct_month')?></th>
							<th><?=translate('applied_on')?></th>
							<th style="text-align:center;"><?=translate('manager approval')?></th>
							<th style="text-align:center;"><?=translate('payment_status')?></th>
							<th><?=translate('action')?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$count = 1;
						foreach ($advanceslist as $row) {?>
						<tr>
							<td><?php echo $count++; ?></td>
							<td class="center"><img class="rounded" src="<?php echo get_image_url('staff', $row['photo']);?>" width="40" height="40" /></td>
							<?php if (in_array(loggedin_role_id(), [1, 2, 3, 5])) : ?>
							<td><?php echo get_type_name_by_id('branch', $row['branch_id']);?></td>
							<?php endif; ?>
							<td><?php echo $row['name'];?><br><small><?php echo $row['uniqid'];?></small></td>
							<td><?php echo html_escape($global_config['currency_symbol'] . $row['amount'])?></td>
							<td><?php echo date("F Y", strtotime($row['year'] .'-'. $row['deduct_month']));?></td>
							<td><?php echo _d($row['request_date']);?></td>
							<td class="advance-status-<?= $row['id'] ?>" style="text-align:center;">
								<?php
								if ($row['status'] == 1)
									echo '<span class="label label-warning-custom">' . translate('pending') . '</span>';
								else if ($row['status'] == 2)
									echo '<span class="label label-success-custom">' . translate('approved') . '</span>';
								else if ($row['status'] == 3)
									echo '<span class="label label-danger-custom">' . translate('rejected') . '</span>';
								?>
							</td>
							<td class="advance-payment-status-<?= $row['id'] ?>" style="text-align:center;">
								<?php
								if ($row['payment_status'] == 1)
									echo '<span class="label label-warning-custom">' . translate('unpaid') . '</span>';
								else if ($row['payment_status'] == 2)
									echo '<span class="label label-success-custom">' . translate('Paid') . '</span>';
								else if ($row['payment_status'] == 3)
									echo '<span class="label label-danger-custom">' . translate('rejected') . '</span>';
								else if ($row['payment_status'] == 4)
									echo '<span class="label label-warning-custom">' . translate('in review') . '</span>';
								?>
							</td>
							<td>
							<?php if (get_permission('advance_salary_manage', 'is_add')): ?>
								<!--modal dialogbox-->
								<a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick="getAdvanceSalaryDetails('<?=$row['id']?>')">
									<i class="fas fa-bars"></i>
								</a>
							<?php endif; ?>
							<?php if (get_permission('advance_salary_manage', 'is_delete')): ?>
								<!--delete link-->
								<?php echo btn_delete('advance_salary/delete/' . $row['id']);?>
							<?php endif; ?>
							</td>
						</tr>
						<?php }?>
					</tbody>
				</table>
			</div>
		</section>
	</div>
</div>

<!-- Advance Salary View Modal -->
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
	<section class="panel" id='quick_view'></section>
</div>

<!-- Advance Salary Add Modal -->
<div id="advanceSalaryModal" class="zoom-anim-dialog modal-block modal-block-lg mfp-hide">
    <section class="panel">
        <div class="panel-heading">
            <h4 class="panel-title"><i class="fas fa-plus-circle"></i> <?php echo translate('advance_salary'); ?></h4>
        </div>
		<?php echo form_open('advance_salary/save', array('class' => 'form-horizontal frm-submit')); ?>
			<div class="panel-body">
			
			<?php if (in_array(loggedin_role_id(), [1, 2, 3, 5])) : ?>
			<div class="form-group mt-md">
				<!-- <label class="col-md-3 control-label"><?=translate('business')?> <span class="required">*</span></label>
				<div class="col-md-9">
					<?php
						$arrayBranch = $this->app_lib->getSelectList('branch');
						echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' onchange='getStafflistRole()'
						id='branch_id' data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
					?>
					<span class="error"></span>
				</div>
			</div>
			<?php endif; ?>
	        <div class="form-group mt-md">
				<label class="col-md-3 control-label"><?=translate('role')?> <span class="required">*</span></label>
				<div class="col-md-9">
	                <?php
	                    $role_list = $this->app_lib->getRoles();
	                    echo form_dropdown("staff_role", $role_list, set_value('staff_role'), "class='form-control' id='staff_role' onchange='getStafflistRole()' data-plugin-selectTwo
	                    data-width='100%' data-minimum-results-for-search='Infinity' ");
	                ?>
	                <span class="error"></span>
				</div>
			</div> -->
			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('applicant')?> <span class="required">*</span></label>
				<div class="col-md-8">
					<?php
						$this->db->select('s.id, s.name');
						$this->db->from('staff AS s');
						$this->db->join('login_credential AS lc', 'lc.user_id = s.id');
						$this->db->where('lc.active', 1);   // only active users
						$this->db->where_not_in('lc.role', [1, 9, 11,12]);   // exclude super admin, etc.
						$this->db->where_not_in('s.id', [49]);
						$this->db->order_by('s.name', 'ASC');
						$query = $this->db->get();

						$staffArray = ['' => 'Select']; // <-- default first option
						foreach ($query->result() as $row) {
							$staffArray[$row->id] = $row->name;
						}

						echo form_dropdown("staff_id", $staffArray, "", "class='form-control' id='staff_id' data-plugin-selectTwo required data-width='100%'");
					?>
					<span class="error"></span>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('deduct_month');?> <span class="required">*</span></label>
				<div class="col-md-8">
	                <input type="text" class="form-control monthyear" name="month_year" id="month_year" value="<?=set_value('month_year',date("Y-m"))?>" />
					<span class="error"></span>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('amount')?> <span class="required">*</span></label>
				<div class="col-md-8">
					<input type="text" class="form-control" value="<?=set_value('amount')?>" name="amount"/>
					<span class="error"></span>
				</div>
			</div>
			<div class="form-group mb-md">
				<label class="col-md-3 control-label"><?=translate('reason')?></label>
				<div class="col-md-8">
					<textarea class="form-control" rows="4" name="reason" placeholder="Enter your Reason"><?=set_value('reason')?></textarea>
				</div>
			</div>
			</div>
		    <footer class="panel-footer">
		        <div class="row">
		            <div class="col-md-12 text-right">
		                <button type="submit" class="btn btn-default mr-xs" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
		                    <i class="fas fa-plus-circle"></i> <?=translate('apply') ?>
		                </button>
		                <button class="btn btn-default modal-dismiss"><?=translate('cancel') ?></button>
		            </div>
		        </div>
		    </footer>
		<?php echo form_close();?>
    </section>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		// Advance salary modal trigger
		$('#advanceSalary').on('click', function(){
			mfp_modal('#advanceSalaryModal');
		});
	});

	function getStafflistRole() {
    	var staff_role = $('#staff_role').val();
    	var branch_id = ($( "#branch_id" ).length ? $('#branch_id').val() : "");
        $.ajax({
            url: base_url + 'ajax/getStafflistRole',
            type: "POST",
            data:{ 
            	role_id: staff_role,
            	branch_id: branch_id 
            },
            success: function (data) {
            	$('#staff_id').html(data);
            }
        });
	}

	// Get advance salary details for approval modal
	function getAdvanceSalaryDetails(id) {
		$.ajax({
			url: base_url + 'advance_salary/getAdvanceSalaryDetails',
			type: 'POST',
			data: {'id': id},
			dataType: "html",
			success: function (data) {
				$('#quick_view').html(data);
				mfp_modal('#modal');
			}
		});
	}

	// Update advance salary status and payment status instantly
	function updateAdvanceSalaryStatus(salaryId, status, paymentStatus) {
		// Update status
		var statusHtml = '';
		if (status == 1) statusHtml = '<span class="label label-warning-custom"><?= translate('pending') ?></span>';
		else if (status == 2) statusHtml = '<span class="label label-success-custom"><?= translate('approved') ?></span>';
		else if (status == 3) statusHtml = '<span class="label label-danger-custom"><?= translate('rejected') ?></span>';
		
		// Update payment status
		var paymentStatusHtml = '';
		if (paymentStatus == 1) paymentStatusHtml = '<span class="label label-warning-custom"><?= translate('unpaid') ?></span>';
		else if (paymentStatus == 2) paymentStatusHtml = '<span class="label label-success-custom"><?= translate('Paid') ?></span>';
		else if (paymentStatus == 3) paymentStatusHtml = '<span class="label label-danger-custom"><?= translate('rejected') ?></span>';
		else if (paymentStatus == 4) paymentStatusHtml = '<span class="label label-warning-custom"><?= translate('in review') ?></span>';
		
		// Update the status cells
		$('.advance-status-' + salaryId).html(statusHtml);
		$('.advance-payment-status-' + salaryId).html(paymentStatusHtml);
	}
</script>