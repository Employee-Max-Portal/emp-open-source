<?php $row = $this->warning_model->getWarningByIds($warnings_id); 

?>

<style>
.modal-block {
    background: transparent;
    padding: 0;
    text-align: left;
    max-width: 750px;
    margin: 40px auto;
    position: relative;
}
</style>


<?php echo form_open('todo', ['id' => 'main-form']); ?>
<input type="hidden" name="category" id="category" value="<?php echo $row['category']; ?>">
<input type="hidden" name="effect" id="effect" value="<?php echo $row['effect']; ?>">
	<header class="panel-heading">
		<h4 class="panel-title"><i class="fas fa-bars"></i> <?php echo translate('details'); ?></h4>
	</header>
	<div class="panel-body">
		<div class="table-responsive">
			<table class="table borderless mb-none">
				<tbody>
					<tr>
						<th width="150"><?=translate('reviewed_by')?> :</th>
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
						<th width="150"><?=translate('issued_by')?> :</th>
						<td>
							<?php
	                            if(!empty($row['issued_by'])){
	                                echo get_type_name_by_id('staff', $row['issued_by']);
	                            }else{
	                                echo translate('not_specified');
	                            }
							?>
						</td>
					</tr>
					<tr>
						<th width="150"><?php echo translate('applicant'); ?> : </th>
						<td><?php
							$getStaff = $this->db->select('name,staff_id')->where('id', $row['user_id'])->get('staff')->row_array();
							echo $getStaff['staff_id'] . ' - '. $getStaff['name'];
								?></td>
					</tr>

					<tr>
						<th width="150"><?php echo translate('reference'); ?> : </th>
						<td><?php echo translate($row['reference']); ?></td>
					</tr>
					
					<tr>
						<th width="150"><?php echo translate('reason'); ?> : </th>
						<td><?php echo translate($row['reason']); ?></td>
					</tr>
					<tr>
						<th width="150"><?php echo translate('issue_date'); ?> : </th>
						<td><?php 
							$issueDate = new DateTime($row['issue_date']);
							echo $issueDate->format('jS F, Y \a\t h:i A');
						?></td>
					</tr>
					<tr>
						<th width="150"><?php echo translate('deadline'); ?> :</th>
						<td>
							<?php
								$clearance = new DateTime($row['email_sent_at']);
								$clearance->modify('+' . (int)$row['clearance_time'] . ' hours');
								echo $clearance->format('jS F, Y \a\t h:i A');
							?>
						</td>
					</tr>
					<?php if (!empty($row['penalty'])) { ?>
					<tr>
						<th width="150"><?php echo translate('penalty_work'); ?> : </th>
						<td>
							<?php $dayText = ($row['penalty'] > 1) ? 'Days' : 'Day';
							echo $row['penalty'] . ' '.$dayText; ?>
						</td>
					</tr>
					<?php if (!empty($row['penalty_reason'])) { ?>
					<tr>
						<th width="150"><?php echo translate('penalty_reason'); ?> : </th>
						<td><?php echo translate($row['penalty_reason']); ?></td>
					</tr>
					<?php } ?>
					<tr>
						<th width="150"><?= translate('Select Penalty Workdate') ?> :</th>
						<td>
							<div class="col-md-8">
								<div id="penalty-dates-container">
									<?php for($i = 1; $i <= $row['penalty']; $i++): ?>
									<div class="input-group mb-2">
										<span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
										<input type="date" name="penalty_dates[]" class="form-control penalty-date-input" 
											   value="<?= isset($row['penalty_days'][$i-1]['penalty_date']) ? $row['penalty_days'][$i-1]['penalty_date'] : '' ?>"
											   placeholder="Select penalty work date <?= $i ?>" title="Must select the weekends" required />
									</div>
									<?php endfor; ?>
								</div>
							</div>
						</td>
					</tr>


					<?php } ?>
					<?php if (!empty($row['enc_file_name'])) { ?>
					<tr>
						<th width="150"><?php echo translate('attachment'); ?> : </th>
						<td>
						<a class="btn btn-primary btn-sm" target="_blank" href="<?=base_url('uploads/attachments/warnings/' . $row['enc_file_name'])?>"><i class="fas fa-eye"></i> <?php echo translate('view'); ?></a>
						<a class="btn btn-default btn-sm" target="_blank" href="<?=base_url('todo/download/' . $row['id'] . '/' . $row['enc_file_name'])?>"><i class="far fa-arrow-alt-circle-down"></i> <?php echo translate('download'); ?></a>
						</td>
					</tr>
					<?php } ?>
					<tr>
						<th width="150"><?php echo translate('employee_review'); ?> :</th>
						<th>
							<div class="radio-custom radio-inline">
								<input type="radio" id="approved" name="status" value="2"
								required <?= ($row['status'] == 2 || empty($row['status']) ? 'checked' : 'checked'); ?>>

								<label for="approved"><?php echo translate('cleared'); ?></label>
							</div>
							<?php if (in_array(loggedin_role_id(), [1, 2, 3, 5, 8]) && $row['status'] == 1): ?>

							<div class="radio-custom radio-inline">
		                        <input type="radio" id="penalty" name="status" value="4" <?= ($row['status'] == 4 ? 'checked' : ''); ?>>
		                        <label for="penalty"><?php echo translate('penalty'); ?></label>
		                    </div>
							<?php endif; ?>

							<input type="hidden" name="id" value="<?= $warnings_id ?>">
						</th>
					</tr>
					<?php if ($row['manager_id']): ?>
					<tr>
						<th width="150"><?php echo translate('manager_review'); ?> :</th>
						<th>
							<?php
								$user_id = get_loggedin_user_id();
								$role_id = loggedin_role_id();
								//$is_manager = ($role_id == 8);
								$is_same_manager = ($row['manager_id'] == $user_id);

								$is_admin_hr_coo = in_array($role_id, [1, 2, 3, 5, 8]);
							?>
							<div class="radio-custom radio-inline">
								<input type="radio" id="approved" name="manager_review" value="2"
							<?= ($row['manager_review'] == 2 || empty($row['manager_review']) ? 'checked' : '') ?>
							<?= !$is_same_manager ? 'disabled' : '' ?> required>
								<label for="approved"><?php echo translate('cleared'); ?></label>
							</div>
							<?php if (!empty($row['task_unique_id'])): ?>
							<div class="radio-custom radio-inline">
								<input type="radio" id="unsatisfied_mgr" name="manager_review" value="5" 
									<?= ($row['manager_review'] == 5 ? 'checked' : '') ?>
									<?= !$is_same_manager ? 'disabled' : '' ?>>
								<label for="unsatisfied_mgr"><?php echo translate('unsatisfied'); ?></label>
							</div>
							<?php endif; ?>
							<?php if ($is_admin_hr_coo && $row['manager_review'] == 1): ?>
							<div class="radio-custom radio-inline">
								<input type="radio" id="penalty" name="manager_review" value="4"
									<?= ($row['manager_review'] == 4 ? 'checked' : '') ?> onchange="togglePenaltyDate()">
								<label for="penalty"><?php echo translate('penalty'); ?></label>
							</div>
							<?php endif; ?>
							<input type="hidden" name="id" value="<?= $warnings_id ?>">
						</th>
					</tr>
					<?php endif; ?>
					<tr>
						<th width="150"><?php echo translate('advisor_review'); ?> :</th>
						<th>
							<div class="radio-custom radio-inline">
								<input type="radio" id="advisor_cleared" name="advisor_review" value="2"
									<?= (isset($row['advisor_review']) && $row['advisor_review'] == 2) ? 'checked' : '' ?>
									<?= loggedin_role_id() != 10 ? 'disabled' : 'required' ?>>
								<label for="advisor_cleared"><?php echo translate('cleared'); ?></label>
							</div>
							<div class="radio-custom radio-inline">
								<input type="radio" id="advisor_penalty" name="advisor_review" value="4"
									<?= (isset($row['advisor_review']) && $row['advisor_review'] == 4) ? 'checked' : '' ?>
									<?= loggedin_role_id() != 10 ? 'disabled' : '' ?> onchange="togglePenaltyDate()">
								<label for="advisor_penalty"><?php echo translate('penalty'); ?></label>
							</div>
							<input type="hidden" name="id" value="<?= $warnings_id ?>">
						</th>
					</tr>
					<!-- Penalty Date Selection for Manager/Advisor Review = 4 -->
					<tr id="penalty-date-row" style="display: none;">
						<th width="150"><?= translate('penalty_work_date') ?> :</th>
						<td>
							<div class="input-group">
								<span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
								<input type="date" name="penalty_dates[]" class="form-control" 
									   placeholder="Select penalty work date" title="Select 1 day penalty work date" />
							</div>
						</td>
					</tr>

						<!-- Penalty Reminder Section -->
					<?php if (in_array(loggedin_role_id(), [1, 2, 3, 5, 8]) && $row['status'] == 1): ?>
					<tr>
						<th width="150"><?= translate('Penalty Workdays') ?> :</th>
						<td colspan="2">
							<div class="col-md-3">
								<input type="number" class="form-control" id="penalty_workday" name="penalty_workday">
							</div>
							<div class="col-md-6">
								<!-- This input was missing but expected by your JS -->
								<input type="text" id="penalty_reason_input" class="form-control" placeholder="Reason">

							</div>
							<div class="col-md-2">
								<!-- Submit Button -->
								<button type="button" class="btn btn-sm btn-outline-warning mt-2" onclick="submitPenaltyReminder()">
									 <?= translate('reminder') ?>
								</button>
							</div>
						</td>
					</tr>

					<?php endif; ?>
					<tr>
						<th width="150"><?php echo translate('employee_explanation'); ?> :</th>
						<td>
							<textarea name="comments" class="summernote" rows="3" required><?php echo $row['comments']; ?></textarea>
						</td>
					</tr>
					<?php if ($row['manager_id']): ?>
						<?php
							$user_id = get_loggedin_user_id();
							//$is_manager = ($role_id == 8);
							$is_same_manager = ($row['manager_id'] == $user_id);
						?>
					<tr>
						<th width="150"><?php echo translate('manager_explanation'); ?> :</th>
						<td>
							<textarea class="form-control" name="manager_explanation" rows="3"
								
								<?= !$is_same_manager ? 'readonly' : 'required' ?>>
								<?php echo $row['manager_explanation']; ?>
							</textarea>
						</td>
					</tr>
					<?php endif; ?>
					<tr>
						<th width="150"><?php echo translate('advisor_explanation'); ?> :</th>
						<td>
							<textarea class="form-control" name="advisor_explanation" rows="3"
								<?= loggedin_role_id() != 10 ? 'readonly' : 'required' ?>>
								<?php echo isset($row['advisor_explanation']) ? $row['advisor_explanation'] : ''; ?>
							</textarea>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<footer class="panel-footer">
    <div class="row">
      <?php
			$issueDate = new DateTime($row['email_sent_at']);

			if (!empty($row['clearance_time'])) {
				$issueDate->modify('+' . (int)$row['clearance_time'] . ' hours');
			}

			$now = new DateTime();

			$currentUserId = get_loggedin_user_id();
			$isEmployee = ($currentUserId == $row['user_id']);
			$isManager = ($currentUserId == $row['manager_id']);
			$isAdvisor = (loggedin_role_id() == 10);

			// Expired if time passed
			$timeExpired = ($now > $issueDate);

			// Disable logic for each role
			$isEmployeeExpired = $timeExpired || $row['status'] == 2;
			$isManagerExpired = $timeExpired || $row['manager_review'] == 2;
			$isAdvisorExpired = isset($row['advisor_review']) && $row['advisor_review'] == 2;

			// Button disable condition
			$isSubmitDisabled = ($isEmployee && $isEmployeeExpired) || ($isManager && $isManagerExpired) || ($isAdvisor && $isAdvisorExpired);
			
			// Enable submit if manager or advisor selects penalty (4)
			$managerPenalty = (isset($row['manager_review']) && $row['manager_review'] == 4);
			$advisorPenalty = (isset($row['advisor_review']) && $row['advisor_review'] == 4);
			if ($managerPenalty || $advisorPenalty) {
				$isSubmitDisabled = false;
			}
		?>


	<div class="col-md-12 text-right">
		<?php if ($isEmployee || $isManager || $isAdvisor): ?>
			<button class="btn btn-default mr-xs" type="button" name="update" value="1" onclick="showConfirmationModal()" id="submit-btn"
				<?= $isSubmitDisabled ? 'disabled' : '' ?>>
				<i class="fas fa-save"></i> <?php echo translate('submit'); ?>
			</button>
		<?php endif; ?>
		<button type="button" class="btn btn-default modal-dismiss"><?php echo translate('close'); ?></button>
	</div>

 
    </div>
</footer>

<?php echo form_close(); ?>

<!-- Enhanced Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md" role="document">
    <div class="modal-content border-0 shadow-lg rounded-lg">
      
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title font-weight-bold" id="confirmationModalLabel">
          <i class="fas fa-exclamation-triangle mr-2"></i>
          <?php echo translate('please_confirm_your_submission'); ?>
        </h5>
        <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body py-4 px-4">
        <p class="mb-2 font-weight-semibold text-dark">
          Are you certain that all the information provided in this form is 
          <strong>accurate and true</strong>?
     
          If you're unsure, please <strong>review it before submitting</strong>.
        </p>
        <div class="alert alert-danger mb-0 border-0 font-weight-bold">
          <i class="fas fa-ban mr-1"></i>
          Providing false or incorrect information may result in disciplinary action.
        </div>
      </div>

      <div class="modal-footer justify-content-between px-4 py-3">
        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">
          <i class="fas fa-times-circle mr-1"></i> <?php echo translate('cancel'); ?>
        </button>
        <button type="button" class="btn btn-success btn-sm" onclick="confirmSubmission()">
          <i class="fas fa-check-circle mr-1"></i> <?php echo translate('yes_submit'); ?>
        </button>
      </div>
    </div>
  </div>
</div>


<!-- Separate form for sending penalty reminder -->
<form method="post" action="<?= base_url('todo/send_reminder') ?>" id="penalty-reminder-form" style="display:none;">
	<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>" />
	<input type="hidden" name="warning_id" value="<?= $row['id'] ?>" />
	<input type="hidden" name="staff_id" value="<?= $row['user_id'] ?>" />
	<input type="hidden" name="penalty_workday" id="penalty_workday_hidden">
	<input type="hidden" name="penalty_reason" id="penalty_reason_hidden">

</form>

<script>

$(document).ready(function() {
    $('.penalty-date-input').on('change', function() {
        const selectedDate = $(this).val();
        const currentInput = $(this);
        
        $('.penalty-date-input').not(currentInput).each(function() {
            if ($(this).val() === selectedDate) {
                alert('This date has already been selected. Please choose a different date.');
                currentInput.val('');
                return false;
            }
        });
    });
    
    // Check initial state on page load
    togglePenaltyDate();
});

function togglePenaltyDate() {
    const managerPenalty = document.querySelector('input[name="manager_review"]:checked');
    const advisorPenalty = document.querySelector('input[name="advisor_review"]:checked');
    const penaltyRow = document.getElementById('penalty-date-row');
    const submitBtn = document.getElementById('submit-btn');
    
    if ((managerPenalty && managerPenalty.value === '4') || (advisorPenalty && advisorPenalty.value === '4')) {
        penaltyRow.style.display = 'table-row';
        penaltyRow.querySelector('input[type="date"]').required = true;
        if (submitBtn) submitBtn.disabled = false;
    } else {
        penaltyRow.style.display = 'none';
        penaltyRow.querySelector('input[type="date"]').required = false;
    }
}

</script>

<script>
function submitPenaltyReminder() {
	const dateValue = document.getElementById('penalty_workday').value;
	const reasonValue = document.getElementById('penalty_reason_input').value;

	document.getElementById('penalty_workday_hidden').value = dateValue;
	document.getElementById('penalty_reason_hidden').value = reasonValue;

	document.getElementById('penalty-reminder-form').submit();
}


function showConfirmationModal() {
	$('#confirmationModal').modal('show');
}

function confirmSubmission() {
	// Hide the modal
	$('#confirmationModal').modal('hide');
	
	// Add the hidden input for update action
	const form = document.getElementById('main-form');
	const updateInput = document.createElement('input');
	updateInput.type = 'hidden';
	updateInput.name = 'update';
	updateInput.value = '1';
	form.appendChild(updateInput);
	
	// Submit the form
	form.submit();
}
</script>

<script>
$(document).ready(function() {
$('#daterange').daterangepicker({
    locale: {
        format: 'YYYY/MM/DD'
    },
    drops: 'up',           // ⬆️ Show above the input
    opens: 'center',       // or 'left' / 'right'
    autoUpdateInput: false
});


    // Optional: update value on select
    $('#daterange').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY/MM/DD') + ' - ' + picker.endDate.format('YYYY/MM/DD'));
    });

    $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });
});

</script>

<script type="text/javascript">
	$(document).ready(function () {
		// Initialize Summernote
		$('.summernote').summernote({
			height: 100,
			dialogsInBody: true,
			toolbar: [
				['style', ['bold', 'italic', 'underline', 'clear']],
				['font', ['fontsize']],
				['para', ['ul', 'ol', 'paragraph']],
				['insert', ['link']],
				['view', ['fullscreen', 'codeview']]
			]
		});

		// Custom tag button click inserts text
		$('.btn_tag').on('click', function() {
	var txtToAdd = $(this).data("value");
	var $focusedEditor = $('.summernote:focus');
	if ($focusedEditor.length > 0) {
		$focusedEditor.summernote('insertText', txtToAdd);
	} else {
		// fallback to first one
		$('.summernote').eq(0).summernote('insertText', txtToAdd);
	}
});

	});
</script>
 