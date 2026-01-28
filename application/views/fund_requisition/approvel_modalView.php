<?php
$this->db->select('fund_requisition.*,fund_category.name as fund_name,staff.name as staff_name,staff.staff_id as staffid',);
$this->db->from('fund_requisition');
$this->db->join('staff', 'staff.id = fund_requisition.staff_id', 'left');
$this->db->join('fund_category', 'fund_category.id = fund_requisition.category_id', 'left');
$this->db->where('fund_requisition.id', $fund_id);
$row = $this->db->get()->row_array();
$ledger_entries = !empty($row['ledger_entries']) ? json_decode($row['ledger_entries'], true) : [];
$can_adjust_ledger = in_array(loggedin_role_id(), [1, 2, 5]);

?>

<form id="fund-approval-form" method="post">
<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
	<header class="panel-heading">
		<h4 class="panel-title"><i class="fas fa-list-ol"></i> <?=translate('review')?></h4>
	</header>
	<div class="panel-body">
		<div class="table-responsive">
			<table class="table borderless mb-none">
				<tbody>
				
                    <input type="hidden" name="id" value="<?=$fund_id?>">
					<tr>
						<th width="120"><?=translate('reviewed_by')?> :</th>
						<td>
							<?php
                                if(!empty($row['issued_by'])){
                                    echo html_escape(get_type_name_by_id('staff', $row['issued_by']));
                                }else{
                                    echo translate('unreviewed');
                                }
							?>
						</td>
					</tr>
					<?php if (!empty($row['paid_by'])) { ?>
					<tr>
						<th width="120"><?=translate('Paid By')?> :</th>
						<td>
							<?php
                                echo html_escape(get_type_name_by_id('staff', $row['paid_by']));
							?>
						</td>
					</tr>
					<?php } ?>

					<tr>
						<th><?=translate('applicant')?> :</th>
						<td><?=ucfirst($row['staffid'] . ' - ' . $row['staff_name'])?></td>
					</tr>
					<?php if (!empty($row['milestone'])) { ?>
					<tr>
						<th><?=translate('milestone')?> :</th>
						<td><?php $getMilestone = $this->db->select('title')->where('id', $row['milestone'])->get('tracker_milestones')->row_array();
							echo $getMilestone['title'];?></td>
					</tr>
					<?php } ?>
					
					<?php if (!empty($row['task_id'])) { ?>
					<tr>
						<th><?=translate('task details')?> :</th>
						<td><?php $getTask = $this->db->select('unique_id, task_title')->where('id', $row['task_id'])->get('tracker_issues')->row_array();
							echo $getTask['task_title'];?>
							<button type="button" class="btn btn-info btn-xs ml-1" onclick="viewTask('<?= $getTask['unique_id'] ?>')" title="View Task">
								<i class="fas fa-eye"></i>
							</button></td>
					</tr>
					<?php } ?>
					
					<tr>
						<th><?=translate('fund_type')?> :</th>
						<td><?=ucfirst($row['fund_name'])?></td>
					</tr>
					<?php
					$fund_name = strtolower(trim($row['fund_name']));
					if ($fund_name == 'conveyance' || $fund_name == 'convence'):
					?>
					<tr>
						<th><?=translate('CRM Token / Lead No.')?> :</th>
						<td><?=ucfirst($row['token'])?></td>
					</tr>
					<?php endif; ?>
					<tr>
						<th><?=translate('amount')?> :</th>
						<td><?=html_escape($global_config['currency_symbol'] . $row['amount'])?></td>
					</tr>
					<tr>
						<th><?=translate('applied_on')?> : </th>
						<td><?php echo _d($row['request_date']);?></td>
					</tr>
					<tr>
						<th><?=translate('reason')?> : </th>
						<td width="350"><?=(empty($row['reason']) ? 'N/A' : $row['reason']);?></td>
					</tr>
					<?php if (!empty($row['enc_file_name'])) { ?>
					<tr>
						<th><?php echo translate('attachment'); ?> : </th>
						<td>
							<a class="btn btn-primary btn-sm" target="_blank" href="<?=base_url('uploads/attachments/fund_requisition/' . $row['enc_file_name'])?>"><i class="fas fa-eye"></i> <?php echo translate('view'); ?></a>
							<a class="btn btn-default btn-sm" target="_blank" href="<?=base_url('fund_requisition/download/' . $row['id'] . '/' . $row['enc_file_name'])?>"><i class="far fa-arrow-alt-circle-down"></i> <?php echo translate('download'); ?></a>
						</td>
					</tr>
					<?php } ?>
					<tr>
						<th><?=translate('Billing Type')?> : </th>
						<td width="350"><?=(empty($row['billing_type']) ? 'N/A' : translate($row['billing_type']));?></td>
					</tr>
					<tr>
                        <th><?=translate('status')?> : </th>
						<th colspan="1">
                            <div class="radio-custom radio-inline">
                                <input type="radio" id="pending" name="status" value="1" <?php echo ($row['status'] == 1 ? ' checked' : '');?>>
                                <label for="pending"><?=translate('pending')?></label>
                            </div>
                            <div class="radio-custom radio-inline">
                                <input type="radio" id="approved" name="status" value="2" <?php echo ($row['status'] == 2 ? ' checked' : '');?>>
                                <label for="approved"><?=translate('approved')?></label>
                            </div>
                            <div class="radio-custom radio-inline">
                                <input type="radio" id="reject" name="status" value="3" <?php echo ($row['status'] == 3 ? ' checked' : '');?>>
                                <label for="reject"><?=translate('reject')?></label>
                            </div>
						</th>
					</tr>
					<tr>
						<th><?=translate('payment_status')?> : </th>
						<th colspan="1">
							<?php if (in_array(loggedin_role_id(), [1, 5])) : ?>
								<select name="payment_status" class="form-control">
									<option value="1" <?= ($row['payment_status'] == 1 ? 'selected' : '') ?>><?=translate('unpaid')?></option>
									<option value="2" <?= ($row['payment_status'] == 2 ? 'selected' : '') ?>><?=translate('paid')?></option>
									<option value="3" <?= ($row['payment_status'] == 3 ? 'selected' : '') ?>><?=translate('reject')?></option>
									<option value="4" <?= ($row['payment_status'] == 4 ? 'selected' : '') ?>><?=translate('in_review')?></option>
								</select>
						   <?php else: ?>
								<select class="form-control" disabled>
									<option value="1" <?= ($row['payment_status'] == 1 ? 'selected' : '') ?>><?=translate('unpaid')?></option>
									<option value="2" <?= ($row['payment_status'] == 2 ? 'selected' : '') ?>><?=translate('paid')?></option>
									<option value="3" <?= ($row['payment_status'] == 3 ? 'selected' : '') ?>><?=translate('reject')?></option>
									<option value="4" <?= ($row['payment_status'] == 4 ? 'selected' : '') ?>><?=translate('in_review')?></option>
								</select>
								<!-- ✅ Hidden input to pass selected value in POST -->
								<input type="hidden" name="payment_status" value="<?= $row['payment_status'] ?>">
							<?php endif; ?>
						</th>
					</tr>
					<tr>
						<th><?=translate('payment_method')?> : </th>
						<th colspan="1">
							<?php 
							$account_types = $this->db->get('cashbook_accounts')->result_array();
							if (in_array(loggedin_role_id(), [1, 5])) : ?>
								<select name="payment_method" class="form-control">
									<option value="" <?= (empty($row['payment_method']) ? 'selected' : '') ?>><?=translate('select')?></option>
									<?php foreach($account_types as $account): ?>
										<option value="<?= $account['id'] ?>" <?= ($row['payment_method'] == $account['id'] ? 'selected' : '') ?>><?= ucfirst($account['name']) ?></option>
									<?php endforeach; ?>
								</select>
						   <?php else: ?>
								<select class="form-control" disabled>
									<option value="" <?= (empty($row['payment_method']) ? 'selected' : '') ?>><?=translate('select')?></option>
									<?php foreach($account_types as $account): ?>
										<option value="<?= $account['id'] ?>" <?= ($row['payment_method'] == $account['id'] ? 'selected' : '') ?>><?= ucfirst($account['name']) ?></option>
									<?php endforeach; ?>
								</select>
								<!-- ✅ Hidden input to pass selected value in POST -->
								<input type="hidden" name="payment_method" value="<?= $row['payment_method'] ?>">
							<?php endif; ?>
						</th>
					</tr>

					<tr>
						<th><?php echo translate('comments'); ?> : </th>
						<td><textarea class="form-control" name="comments" rows="3"><?php echo html_escape($row['comments']); ?></textarea></td>
					</tr>
				</tbody>
			</table>
			<?php if (!empty($ledger_entries)) { ?>
				<h5><strong><?=translate('Ledger Entries:')?></strong></h5>
				<table class="table table-bordered">
					<thead>
						<tr>
							<th><?=translate('Name')?></th>
							<th><?=translate('Amount')?></th>
						</tr>
					</thead>
					<tbody>
						<?php
							$grand_total = 0;
							foreach ($ledger_entries as $entry) {
								$grand_total += (float)$entry['amount'];
						?>
							<tr>
								<td><?= html_escape($entry['name']) ?></td>
								<td><?= currencyFormat($entry['amount']) ?></td>
							</tr>
						<?php } ?>
					</tbody>
				</table>

				<!-- Display Totals -->
				<div class="col-md-offset-7 mt-3">
					<p><strong><?=translate('Ledger Total')?>:</strong> <?= currencyFormat($grand_total) ?></p>
					<p><strong><?=translate('Original Amount')?>:</strong> <?= currencyFormat($row['amount']) ?></p>

					<?php
						$difference = $grand_total - (float)$row['amount'];
						if ($difference < 0) {
						
						echo '<p><strong>' . translate('Due') . '</strong>: <span id="dueAmount">0.00</span> | <strong>' . translate('Remaining') . '</strong>: <span id="extraAmount">' . currencyFormat(abs($difference)) .  '</span></p>';

						} elseif ($difference > 0) {
							 echo '<p><strong>' . translate('Due') . '</strong>: <span id="dueAmount">' . currencyFormat(abs($difference)) .  '</span> | <strong>' . translate('Remaining') . '</strong>: <span id="extraAmount">0.00</span></p>';
						} else {
							echo '<p><strong>' . translate('Due') . '</strong>: <span id="dueAmount">0.00</span> | <strong>' . translate('Remaining') . '</strong>: <span id="extraAmount">0.00</span></p>';
						}
					?>
				</div>
			<?php } ?>
			
			<?php if (!empty($ledger_entries) && $can_adjust_ledger): ?>
				<h5><strong><?=translate('Adjust Ledger:')?></strong></h5>
				<?php
					$grand_total = array_sum(array_column($ledger_entries, 'amount'));
					$original_amount = (float)$row['amount'];
					$difference = $grand_total - $original_amount;

					$adjust_amount = $row['adjust_amounts'] ?? (abs($difference) > 0 ? abs($difference) : '');
					
					$ledger_status = $row['ledger_status'] ?? '';
				?>
				<table class="table table-bordered">
					<tr>
						<th><?=translate('Adjustment Amount')?>:</th>
						<td>
							<input type="number" step="0.01" name="adjust_amount" class="form-control" value="<?= html_escape($adjust_amount) ?>" required>
						</td>
					</tr>
					<tr>
						<th><?=translate('Ledger Status')?>:</th>
						<td>
							<select name="ledger_status" class="form-control" required>
								<option value="received" <?= ($ledger_status == 'received' ? 'selected' : '') ?>><?=translate('Received')?></option>
								<option value="cleared" <?= ($ledger_status == 'cleared' ? 'selected' : '') ?>><?=translate('Cleared')?></option>
								<option value="pending" <?= ($ledger_status == 'pending' ? 'selected' : '') ?>><?=translate('Pending')?></option>
							</select>
						</td>
					</tr>
				</table>
			<?php elseif (!empty($ledger_entries)) : ?>
				<h5><strong><?=translate('Ledger Adjustment')?></strong></h5>
				<table class="table table-bordered">
					<tr>
						<th><?=translate('Amount')?>:</th>
						<td><?= currencyFormat($row['adjust_amount'] ?? 0) ?></td>
					</tr>
					<tr>
						<th><?=translate('Ledger Status')?>:</th>
						<td><?= ucfirst($row['ledger_status'] ?? 'N/A') ?></td>
					</tr>
				</table>
			<?php endif; ?>
		</div>
	</div>
	<footer class="panel-footer">
		<div class="row">
			<div class="col-md-12 text-right">
			<?php if ($row['status'] == 2 || $row['payment_status'] == 4 || $row['ledger_status'] == NULL) { ?>
				<button class="btn btn-default mr-xs" type="submit" name="update" value="1" id="submit-btn">
					<i class="fas fa-plus-circle"></i> <span class="btn-text"><?php echo translate('apply'); ?></span>
					<i class="fas fa-spinner fa-spin" style="display: none;"></i>
				</button>
			<?php } ?>
				<button class="btn btn-default modal-dismiss"><?=translate('close')?></button>
			</div>
		</div>
	</footer>
</form>

<!-- Task Details Modal -->
<div class="modal fade" id="taskDetailsModal" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-xl custom-task-modal" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Task Details</h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<!-- Task details will be loaded here -->
			</div>
		</div>
	</div>
</div>


<style>
.modal-block {
    background: transparent;
    padding: 0;
    text-align: left;
    max-width: 80%;
    margin: 40px auto;
    position: relative;
}
  .custom-task-modal {
    width: 80% !important;
    max-width: 1200px;
    height: 90vh;
  }

  .custom-task-modal .modal-content {
    height: 100%;
    display: flex;
    flex-direction: column;
  }

  .custom-task-modal .modal-body {
    flex: 1;
    overflow-y: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
  }

  .custom-task-modal .modal-body::-webkit-scrollbar {
    width: 0px;
    background: transparent;
  }

  #commentsList {
    scrollbar-width: none;
    -ms-overflow-style: none;
  }

  #commentsList::-webkit-scrollbar {
    width: 0px;
    background: transparent;
  }

  /* Mention system styles */
  .mention-container {
    position: relative;
  }

  .mention-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    min-width: 200px;
  }

  #mentionDropdown {
    bottom: 100%;
    top: auto;
  }

  .mention-item {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
  }

  .mention-item:hover,
  .mention-item.selected {
    background: #f0f8ff;
  }

  .mention-item:last-child {
    border-bottom: none;
  }
  
  .mention-name {
    font-size: 14px;
    color: #333;
  }

  .mention-highlight {
    background: #e3f2fd;
    color: #1976d2;
    padding: 2px 4px;
    border-radius: 3px;
    font-weight: 500;
  }
</style>


<script>

// View task details in modal (global function)
function viewTask(id) {
	$.ajax({
		url: base_url + 'dashboard/viewTracker_Issue',
		type: 'POST',
		data: {'id': id},
		dataType: "html",
		success: function (data) {
			$('#taskDetailsModal .modal-body').html(data);
			$('#taskDetailsModal').modal('show');
		}
	});
}


$(document).ready(function() {
	$('#fund-approval-form').on('submit', function(e) {
		e.preventDefault();
		
		var $form = $(this);
		var $submitBtn = $('#submit-btn');
		var $btnText = $submitBtn.find('.btn-text');
		var $spinner = $submitBtn.find('.fa-spinner');
		
		// Show loading state
		$submitBtn.prop('disabled', true);
		$btnText.hide();
		$spinner.show();
		
		$.ajax({
			url: base_url + 'fund_requisition/fund_requisition_ajax',
			type: 'POST',
			data: $form.serialize(),
			dataType: 'json',
			success: function(response) {
				if (response.status === 'success') {
					// Get the fund requisition ID, selected status and payment status
					var fundId = $('input[name="id"]').val();
					var selectedStatus = $('input[name="status"]:checked').val();
					var selectedPaymentStatus = $('select[name="payment_status"]').val() || $('input[name="payment_status"]').val();
					
					// Update status and payment status instantly
					if (typeof parent.updateFundRequisitionStatus === 'function' && selectedStatus && selectedPaymentStatus) {
						parent.updateFundRequisitionStatus(fundId, selectedStatus, selectedPaymentStatus);
					}
					
					// Close modal
					parent.$.magnificPopup.close();
					
					// Show success message
					if (typeof parent.toastr !== 'undefined') {
						parent.toastr.success('Fund requisition updated successfully');
					}
				} else {
					if (typeof toastr !== 'undefined') {
						toastr.error(response.message || 'Error updating fund requisition');
					} else {
						alert(response.message || 'Error updating fund requisition');
					}
				}
			},
			error: function() {
				if (typeof toastr !== 'undefined') {
					toastr.error('Network error occurred');
				} else {
					alert('Network error occurred');
				}
			},
			complete: function() {
				// Reset button state
				$submitBtn.prop('disabled', false);
				$btnText.show();
				$spinner.hide();
			}
		});
	});
});
</script>
