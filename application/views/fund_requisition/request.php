<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><?=translate('select_ground')?></h4>
			<?php
			$employee = $this->db->get_where('staff', ['id' => get_loggedin_user_id()])->row();
			$isRegular = ($employee && $employee->employee_type === 'regular');

			if (get_permission('fund_requisition_request', 'is_add')): ?>
				<?php /* if ($isRegular): ?>
					<div class="panel-btn">
						<a href="javascript:void(0);" id="fundRequisition" class="btn btn-default btn-circle">
							<i class="fas fa-plus-circle"></i> <?=translate('fund_requisition_request')?>
						</a>
					</div>
				<?php else: ?>
					<div class="panel-btn">
						<i class="fa fa-info-circle"></i>
						<strong>Note:</strong> You don't have this permission. Please contact HR for assistance.
					</div>
				<?php endif; */ ?>
				<div class="panel-btn">
						<a href="javascript:void(0);" id="fundRequisition" class="btn btn-default btn-circle">
							<i class="fas fa-plus-circle"></i> <?=translate('fund_requisition_request')?>
						</a>
					</div>
			<?php endif; ?>


			</header>
			<?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
				<div class="panel-body">
					<div class="row mb-sm">
						<div class="col-md-offset-3 col-md-6 mb-sm">
							<div class="form-group">
	                            <label class="control-label"><?php echo translate('date'); ?> <span class="required">*</span></label>
	                            <div class="input-group">
	                                <span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
	                                <input type="text" class="form-control daterange" name="daterange" value="<?=set_value('daterange', date("Y/m/d", strtotime('-6day')) . ' - ' . date("Y/m/d"))?>" required />
	                            </div>
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

		<section class="panel appear-animation" data-appear-animation="<?=$global_config['animations'] ?>" data-appear-animation-delay="100">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-users" aria-hidden="true"></i> <?=translate('fund_requisition')?></h4>
			</header>
			<div class="panel-body">
				<table class="table table-bordered table-condensed table-hover mb-none table-export" >
					<thead>
						<tr>
							<th width="2%"><?=translate('sl')?></th>
							<th width="8%"><?=translate('date')?></th>
							<!-- <th><?=translate('photo')?></th> -->
							<th width="10%"><?=translate('applicant')?></th>
							<th width="15%"><?=translate('Milestone / CRM')?></th>
							<th width="17%"><?=translate('reason')?></th>
							<th width="8%"><?=translate('category')?></th>
							<th width="6%"><?=translate('billing_type')?></th>
							<th width="8%"><?=translate('amount')?></th>
							<th width="6%" style="text-align:center;"><?=translate('status')?></th>
							<th width="8%" style="text-align:center;"><?=translate('payment_status')?></th>
							<th width="12%"><?=translate('action')?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$count = 1;
						foreach ($fundlist as $row) { ?>
						<tr>
							<td><?php echo $count++; ?></td>
							<td><?php echo _d($row['create_at']);?></td>
							<!--<td class="center"><img class="rounded" src="<?php echo get_image_url('staff', $row['photo']);?>" width="40" height="40" /></td> -->
							<td><?php
								echo !empty($row['orig_file_name']) ? '<i class="fas fa-paperclip"></i> ' : '';
								$getStaff = $this->db->select('name,staff_id')->where('staff_id', $row['staff_id'])->get('staff')->row_array();
									echo $getStaff['name'];
								?></td>
							<td><?php 
							if ($row['is_lead'] == 1) {
								echo 'CRM Lead: ' . ($row['token'] ? $row['token'] : 'N/A');
							} else {
								if (!empty($row['milestone'])) {
									$getMilestone = $this->db->select('title')->where('id', $row['milestone'])->get('tracker_milestones')->row_array();
									echo $getMilestone['title'];
								} else {
									echo ($row['token'] ? 'CRM Token: ' . $row['token'] : 'N/A');
								}
							}
							?></td>
							<td><?php echo translate($row['reason']);?></td>
							<td><?php echo translate($row['category']);?></td>
							<td><?php echo translate($row['billing_type']);?></td>
							<td><?php echo $global_config['currency_symbol'] . $row['amount'];?></td>
							<td style="text-align:center;">
								<?php
								if ($row['status'] == 1)
									echo '<span class="label label-warning-custom">' . translate('pending') . '</span>';
								else if ($row['status'] == 2)
									echo '<span class="label label-success-custom">' . translate('approved') . '</span>';
								else if ($row['status'] == 3)
									echo '<span class="label label-danger-custom">' . translate('rejected') . '</span>';
								?>
							</td>
							<td style="text-align:center;">
								<?php
								if ($row['payment_status'] == 1)
									echo '<span class="label label-warning-custom">' . translate('unpaid') . '</span>';
								else if ($row['payment_status'] == 2)
									echo '<span class="label label-success-custom">' . translate('paid') . '</span>';
								else if ($row['payment_status'] == 3)
									echo '<span class="label label-danger-custom">' . translate('rejected') . '</span>';
								else if ($row['payment_status'] == 4)
									echo '<span class="label label-warning-custom">' . translate('in review') . '</span>';
								?>
							</td>
							<td>
								<!--modal dialogbox-->
								<a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick="getRequestDetails('<?=$row['id']?>')">
									<i class="fas fa-bars"></i>
								</a>
								<?php if ($row['category_id'] == 18): // Revenue Share category ?>
								<!--revenue share details button-->
								<a href="javascript:void(0);" class="btn btn-info btn-circle icon" onclick="showRevenueShareDetails('<?=$row['reason']?>')" title="View Revenue Details">
									<i class="fas fa-eye"></i>
								</a>
								<?php endif; ?>
							<?php if ($row['status'] == 1 && get_permission('fund_requisition_request', 'is_delete')): ?>
								<!--delete link-->
								<?php echo btn_delete('fund_requisition/request_delete/' . $row['id']);?>
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

<!-- Revenue Share Details Modal -->
<div id="revenueShareModal" class="zoom-anim-dialog modal-block modal-block-lg mfp-hide">
    <section class="panel">
        <div class="panel-heading">
            <h4 class="panel-title"><i class="fas fa-eye"></i> Revenue Share Details</h4>
        </div>
        <div class="panel-body">
            <div id="revenueShareContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
                </div>
            </div>
        </div>
        <footer class="panel-footer">
            <div class="row">
                <div class="col-md-12 text-right">
                    <button class="btn btn-default modal-dismiss">Close</button>
                </div>
            </div>
        </footer>
    </section>
</div>

<?php if (get_permission('fund_requisition_request', 'is_add')): ?>
<!-- Advance Salary Add Modal -->
<div id="fundRequisitionModal" class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" style="max-width: 50%; width: 50%;">
    <section class="panel">
        <div class="panel-heading">
            <h4 class="panel-title"><i class="fas fa-plus-circle"></i> <?=translate('fund_requisition');?></h4>
        </div>

		<?php echo form_open_multipart('fund_requisition/request_save', array('class' => 'form-horizontal frm-submit-data')); ?>
        <div class="panel-body">

            <!-- Fund Category -->
            <div class="form-group mt-md">
                <label class="col-md-3 control-label"><?=translate('category');?> <span class="required">*</span></label>
                <div class="col-md-9">
                    <?php
                        $array = $this->app_lib->getSelectList('fund_category');			
                        echo form_dropdown("category_id", $array, set_value('category_id'), "class='form-control' id='category_id'
                        data-plugin-selectTwo data-width='100%' ");
                    ?>
                    <span class="error"></span>
                </div>
            </div>

            <!-- Amount -->
            <div class="form-group <?php if (form_error('amount')) echo 'has-error';?>">
                <label class="col-md-3 control-label"><?=translate('amount')?> <span class="required">*</span></label>
                <div class="col-md-9">
                    <input type="number" class="form-control" value="<?=set_value('amount')?>" name="amount" />
                    <span class="error"></span>
                </div>
            </div>

            <!-- CRM Token (conditionally shown) -->
            <!-- Is Lead Checkbox -->
            <div class="form-group">
                <label class="col-md-3 control-label"></label>
                <div class="col-md-9">
                    <label class="checkbox-inline">
                        <input type="checkbox" id="is_lead" name="is_lead" value="1"> Is Lead
                    </label>
                </div>
            </div>

            <div class="form-group crm-token-group <?php if (form_error('token')) echo 'has-error';?>" style="display: none;">
                <label class="col-md-3 control-label">CRM Token or Lead No.</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" value="<?=set_value('token')?>" name="token" id="crm_token_input" />
                    <span class="error"></span>
                </div>
            </div>

            <!-- Reason -->
            <div class="form-group mb-md">
                <label class="col-md-3 control-label"><?=translate('reason')?></label>
                <div class="col-md-9">
                    <textarea class="form-control" rows="4" name="reason" placeholder="Enter your Reason"><?=set_value('reason')?></textarea>
                </div>
            </div>
			<div class="form-group">
				<label class="col-md-3 control-label"><?php echo translate('attachment'); ?></label>
				<div class="col-md-9">
					<input type="file" name="attachment_file" id="attachment_file" class="dropify" data-height="80" />
					<span class="error"></span>
				</div>
			</div>
			<!-- Fund Milestone -->
            <div class="form-group mt-md milestone-task-group">
                <label class="col-md-3 control-label"><?=translate('milestone');?> <span class="required">*</span></label>
                <div class="col-md-9">
                    <?php
						$array = $this->app_lib->getSelectList_v2('tracker_milestones', '', ['status' => 'in_progress']);			
						echo form_dropdown("milestone_id", $array, set_value('milestone_id'), "class='form-control' id='milestone_id'
						data-plugin-selectTwo data-width='100%' required");
					?>
                    <span class="error"></span>
                </div>
            </div>
			<!-- Fund Task -->
			<div class="form-group mt-md milestone-task-group">
				<label class="col-md-3 control-label"><?=translate('task');?> <span class="required">*</span></label>
				<div class="col-md-9">
					<select name="task_id" id="task_id" class="form-control" data-plugin-selectTwo data-width="100%" required>
						<option value="">Please select milestone first</option>
					</select>
					<span class="error"></span>
				</div>
			</div>

			<!-- Payment Type -->
            <div class="form-group">
                <label class="col-md-3 control-label"><?=translate('billing_type')?> <span class="required">*</span></label>
                <div class="col-md-9">
                    <select name="billing_type" class="form-control" required>
                        <option value=""><?=translate('select')?></option>
                        <option value="One Time" <?=set_value('billing_type') == 'one_time' ? 'selected' : ''?>>One Time</option>
                        <option value="MRC" <?=set_value('billing_type') == 'MRC' ? 'selected' : ''?>>MRC</option>
                    </select>
                    <span class="error"></span>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <footer class="panel-footer">
            <div class="row">
                <div class="col-md-12 text-right">
                    <button type="submit" name="request" value="1" class="btn btn-default mr-xs" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
                        <i class="fas fa-plus-circle"></i> <?=translate('apply') ?>
                    </button>
                    <button class="btn btn-default modal-dismiss"><?=translate('cancel') ?></button>
                </div>
            </div>
        </footer>
        <?php echo form_close();?>
    </section>
</div>

<?php endif; ?>
<script>
$(document).ready(function() {
    // Disable task dropdown initially
    $('#task_id').prop('disabled', true);
    
    $('#milestone_id').change(function() {
        var milestoneId = $(this).val();
        var staffId = <?= get_loggedin_user_id() ?>;

        if (milestoneId) {
            $('#task_id').prop('disabled', false);
            $.ajax({
                url: "<?= base_url('fund_requisition/get_tracker_tasks_by_milestone'); ?>",
                type: "POST",
                data: { milestone_id: milestoneId, staff_id: staffId },
                dataType: "json",
                success: function(data) {
                    $('#task_id').empty();
                    if (Object.keys(data).length > 0) {
                        $('#task_id').append('<option value="">Select Task</option>');
                        $.each(data, function(key, value) {
                            $('#task_id').append('<option value="'+ key +'">'+ value +'</option>');
                        });
                    } else {
                        $('#task_id').append('<option value="">No tasks available for this milestone</option>');
                    }
                    $('#task_id').trigger('change');
                },
                error: function() {
                    $('#task_id').empty();
                    $('#task_id').append('<option value="">Error loading tasks</option>');
                }
            });
        } else {
            $('#task_id').prop('disabled', true);
            $('#task_id').empty();
            $('#task_id').append('<option value="">Please select milestone first</option>');
        }
    });
});
</script>

<script type="text/javascript">
	function getRequestDetails(id) {
	    $.ajax({
	        url: base_url + 'fund_requisition/getRequestDetails',
	        type: 'POST',
	        data: {'id': id},
	        dataType: "html",
	        success: function (data) {
	            $('#quick_view').html(data);
	            mfp_modal('#modal');
	        }
	    });
	}

	// Show revenue share details
	function showRevenueShareDetails(reason) {
		// Extract month from reason text
		var monthMatch = reason.match(/Revenue share of ([A-Za-z]+ \d{4})/);
		if (!monthMatch) {
			alert('Unable to extract month from reason');
			return;
		}
		
		var monthYear = monthMatch[1];
		
		$('#revenueShareContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
		mfp_modal('#revenueShareModal');
		
		$.ajax({
			url: '<?= base_url("fund_requisition/get_revenue_share_data") ?>',
			type: 'POST',
			data: { month_year: monthYear },
			dataType: 'json',
			success: function(response) {
				if (response.status === 'success') {
					var html = '<div class="row">';
					html += '<div class="col-md-12">';
					html += '<h5><strong>Revenue Share for ' + monthYear + '</strong></h5>';
					html += '<table class="table table-bordered table-striped">';
					html += '<thead><tr><th>Date</th><th>Description</th><th class="text-right">Amount</th></tr></thead><tbody>';
					
					if (response.data.entries && response.data.entries.length > 0) {
						$.each(response.data.entries, function(index, entry) {
							html += '<tr>';
							html += '<td>' + entry.entry_date + '</td>';
							html += '<td>' + entry.description + '</td>';
							html += '<td class="text-right"><?= $global_config["currency_symbol"] ?>' + parseFloat(entry.amount).toLocaleString() + '</td>';
							html += '</tr>';
						});
					}
					
					// Add total and revenue share rows
					html += '<tr style="border-top: 2px solid #333; font-weight: bold;">';
					html += '<td colspan="2"><strong>Total Revenue:</strong></td>';
					html += '<td class="text-right"><strong><?= $global_config["currency_symbol"] ?>' + parseFloat(response.data.total_revenue).toLocaleString() + '</strong></td>';
					html += '</tr>';
					html += '<tr style="background-color: #f0f8ff; font-weight: bold;">';
					html += '<td colspan="2"><strong>Revenue Share (10%):</strong></td>';
					html += '<td class="text-right"><strong><?= $global_config["currency_symbol"] ?>' + parseFloat(response.data.revenue_share).toLocaleString() + '</strong></td>';
					html += '</tr>';
					
					html += '</tbody></table>';
					html += '</div></div>';
					$('#revenueShareContent').html(html);
				} else {
					$('#revenueShareContent').html('<div class="alert alert-danger">' + response.message + '</div>');
				}
			},
			error: function() {
				$('#revenueShareContent').html('<div class="alert alert-danger">Error loading revenue details</div>');
			}
		});
	}
</script>
<script>
    $(document).ready(function() {
        function toggleCrmTokenField() {
            let selectedText = $("#category_id option:selected").text().toLowerCase().trim();
            if (selectedText === 'conveyance' || selectedText === 'convence') {
                $(".crm-token-group").show();
                $("#crm_token_input").prop('required', false);
            } else {
                $(".crm-token-group").hide();
                $("#crm_token_input").prop('required', false).val('');
            }
        }

        function toggleMilestoneTaskFields() {
            if ($("#is_lead").is(':checked')) {
                $(".milestone-task-group").hide();
                $("#milestone_id").prop('required', false);
                $("#task_id").prop('required', false);
            } else {
                $(".milestone-task-group").show();
                $("#milestone_id").prop('required', true);
                $("#task_id").prop('required', true);
            }
        }

        // On load
        toggleCrmTokenField();
        toggleMilestoneTaskFields();

        // On dropdown change
        $("#category_id").on('change', toggleCrmTokenField);
        
        // On checkbox change
        $("#is_lead").on('change', toggleMilestoneTaskFields);
    });
</script>
