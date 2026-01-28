<?php $row = $this->leave_model->getLeaveList2(array('la.id' => $leave_id), true); ?>
<form id="leave-approval-form" method="post">
<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
	<header class="panel-heading">
		<h4 class="panel-title"><i class="fas fa-bars"></i> <?php echo translate('details'); ?></h4>
	</header>
	<div class="panel-body">
		<div class="table-responsive">
			<table class="table borderless mb-none">
				<tbody>
					<tr>
						<th width="120"><?=translate('reviewed_by')?> :</th>
						<td>
							<?php
	                            if(!empty($row['approved_by'])){
	                                echo get_type_name_by_id('staff', $row['approved_by']);
	                            }else{
	                                echo translate('unreviewed');
	                            }
							?>
						</td>
					</tr>
					<tr>
						<th><?php echo translate('applicant'); ?> : </th>
						<td><?php
								if ($row['role_id'] == 7) {
								 	$getStudent = $this->application_model->getStudentDetails($row['user_id']);
								 	echo $getStudent['first_name'] . " " . $getStudent['last_name'];
								} else {
									$getStaff = $this->db->select('name,staff_id')->where('id', $row['user_id'])->get('staff')->row_array();
									echo $getStaff['name'];
								}?></td>
					</tr>

					<tr>
						<th><?php echo translate('staff_id'); ?> : </th>
						<td><?php echo $getStaff['staff_id']; ?></td>
					</tr>

					<tr>
						<th><?php echo translate('leave_category'); ?> : </th>
						<td><?php echo $row['category_name'] ?? 'Unpaid Leave'; ?></td>
					</tr>
					<tr>
						<th><?php echo translate('apply') . " " . translate('date'); ?> : </th>
						<td><?php echo _d($row['created_at']) . " " . date('h:i A' ,strtotime($row['created_at'])); ?></td>
					</tr>
					<tr>
						<th><?php echo translate('start_date'); ?> : </th>
						<td><?php echo _d($row['start_date']); ?></td>
					</tr>
					<tr>
						<th><?php echo translate('end_date'); ?> : </th>
						<td><?php echo _d($row['end_date']); ?></td>
					</tr>
					<tr>
						<th><?php echo translate('reason'); ?> : </th>
						<td><?php echo (empty($row['reason']) ? 'N/A' : $row['reason']); ?></td>
					</tr>
<?php if (!empty($row['enc_file_name'])) { ?>
					<tr>
						<th><?php echo translate('attachment'); ?> : </th>
						<td>						
						<a class="btn btn-primary btn-sm" target="_blank" href="<?=base_url('uploads/attachments/leave/' . $row['enc_file_name'])?>"><i class="fas fa-eye"></i> <?php echo translate('view'); ?></a>
						<a class="btn btn-default btn-sm" target="_blank" href="<?=base_url('leave/download/' . $row['id'] . '/' . $row['enc_file_name'])?>"><i class="far fa-arrow-alt-circle-down"></i> <?php echo translate('download'); ?></a>
						
						</td>
					</tr>
<?php } ?>
					<tr>
		                <th><?php echo translate('status'); ?> : </th>
						<th>
		                    <div class="radio-custom radio-inline">
		                        <input type="radio" id="pending" name="status" value="1" <?php echo ($row['status'] == 1 ? ' checked' : ''); ?>>
		                        <label for="pending"><?php echo translate('pending'); ?></label>
		                    </div>
		                    <div class="radio-custom radio-inline">
		                        <input type="radio" id="paid" name="status" value="2" <?php echo ($row['status'] == 2 ? ' checked' : ''); ?>>
		                        <label for="paid"><?php echo translate('approved'); ?></label>
		                    </div>
		                    <div class="radio-custom radio-inline">
		                        <input type="radio" id="reject" name="status" value="3" <?php echo ($row['status'] == 3 ? ' checked' : ''); ?>>
		                        <label for="reject"><?php echo translate('reject'); ?></label>
		                    </div>
		                    <input type="hidden" name="id" value="<?=$leave_id?>">
						</th>
					</tr>
					<tr>
						<th><?php echo translate('comments'); ?> : </th>
						<td><textarea class="form-control" name="comments" rows="3"><?php echo $row['comments']; ?></textarea></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<footer class="panel-footer">
		<div class="row">
			<div class="col-md-12 text-right">
				<button class="btn btn-default mr-xs" type="submit" name="update" value="1" id="submit-btn">
					<i class="fas fa-plus-circle"></i> <span class="btn-text"><?php echo translate('apply'); ?></span>
					<i class="fas fa-spinner fa-spin" style="display: none;"></i>
				</button>
				<button class="btn btn-default modal-dismiss"><?php echo translate('close'); ?></button>
			</div>
		</div>
	</footer>
</form>

<script>
$(document).ready(function() {
	$('#leave-approval-form').on('submit', function(e) {
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
			url: base_url + 'leave/leave_ajax',
			type: 'POST',
			data: $form.serialize(),
			dataType: 'json',
			success: function(response) {
				if (response.status === 'success') {
					// Get the leave ID and selected status
					var leaveId = $('input[name="id"]').val();
					var selectedStatus = $('input[name="status"]:checked').val();
					
					// Update status instantly
					if (typeof parent.updateLeaveStatus === 'function' && selectedStatus) {
						parent.updateLeaveStatus(leaveId, selectedStatus);
					}
					
					// Close modal
					parent.$.magnificPopup.close();
					
					// Show success message
					if (typeof parent.toastr !== 'undefined') {
						parent.toastr.success('Leave request updated successfully');
					}
				} else {
					if (typeof toastr !== 'undefined') {
						toastr.error(response.message || 'Error updating leave request');
					} else {
						alert(response.message || 'Error updating leave request');
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