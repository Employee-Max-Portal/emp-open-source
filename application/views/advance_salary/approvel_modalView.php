<?php
$this->db->select('advance_salary.*,staff.name as staff_name,staff.staff_id as staffid');
$this->db->from('advance_salary');
$this->db->join('staff', 'staff.id = advance_salary.staff_id', 'left');
$this->db->where('advance_salary.id', $salary_id);
$row = $this->db->get()->row_array();
?>

<form id="advance-approval-form" method="post">
<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
	<header class="panel-heading">
		<h4 class="panel-title"><i class="fas fa-list-ol"></i> <?=translate('review')?></h4>
	</header>
	<div class="panel-body">
		<div class="table-responsive">
			<table class="table borderless mb-none">
				<tbody>
                    <input type="hidden" name="id" value="<?=$salary_id?>">
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
					<tr>
						<th><?=translate('applicant')?> :</th>
						<td><?=ucfirst($row['staffid'] . ' - ' . $row['staff_name'])?></td>
					</tr>
					
					<tr>
						<th><?=translate('amount')?> :</th>
						<td><?=html_escape($global_config['currency_symbol'] . $row['amount'])?></td>
					</tr>
					<tr>
						<th><?=translate('deduct_month')?> :</th>
						<td><?=date("F Y", strtotime($row['year'].'-'. $row['deduct_month']))?></td>
					</tr>
					<tr>
						<th><?=translate('applied_on')?> : </th>
						<td><?php echo _d($row['request_date']);?></td>
					</tr>
					<tr>
						<th><?=translate('reason')?> : </th>
						<td width="350"><?=(empty($row['reason']) ? 'N/A' : $row['reason']);?></td>
					</tr>
					<tr>
                        <th><?=translate('manager approval')?> : </th>
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
								</select>
						   <?php else: ?>
								<select class="form-control" disabled>
									<option value="1" <?= ($row['payment_status'] == 1 ? 'selected' : '') ?>><?=translate('unpaid')?></option>
									<option value="2" <?= ($row['payment_status'] == 2 ? 'selected' : '') ?>><?=translate('paid')?></option>
									<option value="3" <?= ($row['payment_status'] == 3 ? 'selected' : '') ?>><?=translate('reject')?></option>
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
		</div>
	</div>
	<footer class="panel-footer">
		<div class="row">
			<div class="col-md-12 text-right">
			<?php if ($row['status'] !== 2) { ?>
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

<script>
$(document).ready(function() {
	$('#advance-approval-form').on('submit', function(e) {
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
			url: base_url + 'advance_salary/advance_salary_ajax',
			type: 'POST',
			data: $form.serialize(),
			dataType: 'json',
			success: function(response) {
				if (response.status === 'success') {
					// Get the advance salary ID, selected status and payment status
					var salaryId = $('input[name="id"]').val();
					var selectedStatus = $('input[name="status"]:checked').val();
					var selectedPaymentStatus = $('select[name="payment_status"]').val() || $('input[name="payment_status"]').val();
					
					// Update status and payment status instantly
					if (typeof parent.updateAdvanceSalaryStatus === 'function' && selectedStatus && selectedPaymentStatus) {
						parent.updateAdvanceSalaryStatus(salaryId, selectedStatus, selectedPaymentStatus);
					}
					
					// Close modal
					parent.$.magnificPopup.close();
					
					// Show success message
					if (typeof parent.toastr !== 'undefined') {
						parent.toastr.success('Advance salary updated successfully');
					}
				} else {
					if (typeof toastr !== 'undefined') {
						toastr.error(response.message || 'Error updating advance salary');
					} else {
						alert(response.message || 'Error updating advance salary');
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
