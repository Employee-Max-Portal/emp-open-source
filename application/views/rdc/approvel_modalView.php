<?php $row = $this->rdc_model->getTask_byID($task_id); 


/* echo "<pre>";
print_r ($row);
echo "</pre>"; */
/* 
// Decode the JSON safely - handle both old single SOP and new multiple SOPs
$executor_stage = !empty($row['executor_stage']) ? json_decode($row['executor_stage'], true) : [];
$verifier_stage = !empty($row['verifier_stage']) ? json_decode($row['verifier_stage'], true) : [];

// Handle stored stages from multiple SOPs - each element is a JSON string
$executor_stages_data = !empty($row['executor_stages']) ? json_decode($row['executor_stages'], true) : [];
$verifier_stages_data = !empty($row['verifier_stages']) ? json_decode($row['verifier_stages'], true) : [];

// Combine all executor and verifier stages if multiple SOPs
if (is_array($executor_stages_data) && !empty($executor_stages_data)) {
    $combined_executor = ['title' => 'Combined Executor Tasks', 'labels' => []];
    foreach ($executor_stages_data as $stage_json) {
        $stage = json_decode($stage_json, true); // Decode each JSON string
        if (isset($stage['labels']) && is_array($stage['labels'])) {
            $combined_executor['labels'] = array_merge($combined_executor['labels'], $stage['labels']);
        }
    }
    $executor_stage = $combined_executor;
}

if (is_array($verifier_stages_data) && !empty($verifier_stages_data)) {
    $combined_verifier = ['title' => 'Combined Verifier Tasks', 'labels' => []];
    foreach ($verifier_stages_data as $stage_json) {
        $stage = json_decode($stage_json, true); // Decode each JSON string
        if (isset($stage['labels']) && is_array($stage['labels'])) {
            $combined_verifier['labels'] = array_merge($combined_verifier['labels'], $stage['labels']);
        }
    }
    $verifier_stage = $combined_verifier;
}

// Get completed items from stored data
$executor_completed = [];
if (!empty($row['executor_stages'])) {
    $stored_executor = json_decode($row['executor_stages'], true);
    if (isset($stored_executor['completed']) && is_array($stored_executor['completed'])) {
        $executor_completed = $stored_executor['completed'];
    }
}

$verifier_completed = [];
if (!empty($row['verifier_stages'])) {
    $stored_verifier = json_decode($row['verifier_stages'], true);
    if (isset($stored_verifier['completed']) && is_array($stored_verifier['completed'])) {
        $verifier_completed = $stored_verifier['completed'];
    }
}

$totalExecutorChecklist = isset($executor_stage['labels']) ? count($executor_stage['labels']) : 0;
$totalVerifierChecklist = isset($verifier_stage['labels']) ? count($verifier_stage['labels']) : 0;
 */
 
 $executor_stage = json_decode($row['executor_stage'], true);
$verifier_stage = json_decode($row['verifier_stage'], true);

$executor_completed = !empty($row['executor_stages']) ? json_decode($row['executor_stages'], true)['completed'] ?? [] : [];
$verifier_completed = !empty($row['verifier_stages']) ? json_decode($row['verifier_stages'], true)['completed'] ?? [] : [];

$totalExecutorChecklist = isset($executor_stage['labels']) ? count($executor_stage['labels']) : 0;
$totalVerifierChecklist = isset($verifier_stage['labels']) ? count($verifier_stage['labels']) : 0;


$task_status = $row['task_status'];
$verifier_status = $row['verify_status'];


$logged_role_id = loggedin_role_id();
$logged_user_id = get_loggedin_user_id();

$is_assigned_user = ($logged_user_id == $row['assigned_user']);

// Determine verifier permission
$verifier_roles = explode(',', $row['verifier_role']);
$is_verifier_allowed = false;

if (in_array($logged_role_id, $verifier_roles)) {
    /* if ($logged_role_id == 8) {
        $assigned_dept = $this->db->select('department')->where('id', $row['assigned_user'])->get('staff')->row('department');
        $hod_match = $this->db->where(['id' => $logged_user_id, 'department' => $assigned_dept])->get('staff')->num_rows();
        $is_verifier_allowed = ($hod_match > 0);
    } else {
        $is_verifier_allowed = true;
    } */
	
	$is_verifier_allowed = true;
}

$disable_verifier_note = !$is_verifier_allowed ? 'disabled' : '';

$disable_executor_note = !$is_assigned_user ? 'disabled' : ''; 

?>
<style>

@media (min-width: 768px) {
    .modal-dialog {
        width: 700px;
        margin: 60% auto;
    }
}

/* Dropify styling improvements */
.dropify-wrapper {
    border: 2px dashed #ddd;
    border-radius: 4px;
    position: relative;
    cursor: pointer;
    transition: border-color 0.3s ease;
}

.dropify-wrapper:hover {
    border-color: #007bff;
}

.dropify-wrapper .dropify-message {
    font-size: 14px;
    color: #999;
}

.dropify-wrapper.has-preview {
    border: 2px solid #28a745;
}

.dropify-wrapper .dropify-clear {
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 3px;
    padding: 5px 10px;
    font-size: 12px;
}

.dropify-wrapper .dropify-clear:hover {
    background: #c82333;
}

</style>	
<form id="approval-form" method="post" enctype="multipart/form-data">
<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

<input type="hidden" name="id" value="<?=$task_id?>">
<input type="hidden" name="verifier_role" value="<?=$row['verifier_role']?>">
	<header class="panel-heading">
		<h4 class="panel-title"><i class="fas fa-bars"></i> <?php echo translate('details'); ?></h4>
	</header>
	<div class="panel-body">
		<div class="table-responsive">
			<table class="table borderless mb-none">
				<tbody>

					<tr>
						<th width="150"><?=translate('task_description')?> :</th>
						<td>
							<?php echo translate($row['description']); ?>
						</td>
					</tr>
					
					<tr>
						<th width="150"><?php echo translate('deadline'); ?> : </th>
						<td><?php 
						$dueDate = new DateTime($row['due_time']);
						echo $dueDate->format('jS M, Y \a\t g:i A');
						?></td>
					</tr>
					
					<!--<tr>
						<th width="150"><?php echo translate('expected_time'); ?> : </th>
						 <td>
							<?php echo translate($row['expected_time'] . ' Hours'); ?>
						</td>
					</tr> -->
					
					<?php if (!empty($row['enc_file_name'])) { ?>
					<tr>
						<th width="150"><?php echo translate('attachment'); ?> : </th>
						<td><a class="btn btn-default btn-sm" target="_blank" href="<?=base_url('rdc/download/' . $row['id'] . '/' . $row['enc_file_name'])?>"><i class="far fa-arrow-alt-circle-down"></i> <?php echo translate('download'); ?></a></td>
					</tr>
					<?php } ?>
					
					<?php if ($row['is_proof_required'] == 1): ?>
					<tr>
						<th width="150"><?php echo translate('proof_submission'); ?> :</th>
						<td>
							<?php if ($row['proof_required_text'] == 1): ?>
								<div class="form-group">
									<label><?php echo translate('text_explanation'); ?></label>
									<input type="text" class="form-control" name="proof_text" placeholder="Enter proof text" value="<?php echo translate($row['proof_text']); ?>">
								</div>
							<?php endif; ?>

							<?php if ($row['proof_required_image'] == 1): ?>
								<div class="form-group">
									<label><?php echo translate('upload_images'); ?> <small>(<?php echo translate('multiple_files_allowed'); ?>)</small></label>
									<input type="file" name="proof_images[]" class="form-control" multiple accept="image/*" data-max-file-size="3M"/>
									<?php if (!empty($row['proof_image'])): ?>
										<div class="mt-2">
											<small class="text-muted"><?php echo translate('current_image'); ?>:</small>
											<a href="<?=get_rdc_proofs($row['proof_image'])?>" target="_blank" class="btn btn-xs btn-info"><?php echo translate('view_current'); ?></a>
										</div>
									<?php endif; ?>
								</div>
							<?php endif; ?>

							<?php if ($row['proof_required_file'] == 1): ?>
								<div class="form-group">
									<label><?php echo translate('upload_files'); ?> <small>(<?php echo translate('multiple_files_allowed'); ?>)</small></label>
									<input type="file" name="proof_files[]" class="form-control" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.zip,.rar" data-max-file-size="10M"/>
									<?php if (!empty($row['proof_file'])): ?>
										<div class="mt-2">
											<small class="text-muted"><?php echo translate('current_file'); ?>:</small>
											<a href="<?=get_rdc_proofs($row['proof_file'])?>" target="_blank" class="btn btn-xs btn-success"><?php echo translate('download_current'); ?></a>
										</div>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</td>
					</tr>
					<?php endif; ?>

					<?php if (!empty($executor_stage['labels'])): ?>
					<tr>
						<th width="150"><?php echo translate('executor_checklist'); ?> :</th>
						<td>
							<strong><?php echo $executor_stage['title']; ?></strong>
							<ul style="list-style: none; padding-left: 0;">
								<?php foreach ($executor_stage['labels'] as $index => $label): ?>
									<?php $is_checked = in_array($label, $executor_completed); ?>
									<li>
										<div class="checkbox-custom checkbox-success">
											<input type="checkbox"
												name="executor_checklist[]"
												value="<?php echo $label; ?>"
												id="exec_<?php echo $index; ?>"
												<?= $is_checked ? 'checked' : '' ?>
												<?= !$is_assigned_user ? 'disabled' : '' ?>>
											<label for="exec_<?php echo $index; ?>"><?php echo $label; ?></label>
										</div>
									</li>
								<?php endforeach; ?>
							</ul>
						</td>
					</tr>
					<?php endif; ?>

					<?php if (!empty($verifier_stage['labels'])): ?>
					<tr>
						<th width="150"><?php echo translate('verifier_checklist'); ?> :</th>
						<td>
							<strong><?php echo $verifier_stage['title']; ?></strong>
							<ul style="list-style: none; padding-left: 0;">
								<?php foreach ($verifier_stage['labels'] as $index => $label): ?>
									<?php $is_checked = in_array($label, $verifier_completed); ?>
									<li>
										<div class="checkbox-custom checkbox-primary">
											<input type="checkbox"
												name="verifier_checklist[]"
												value="<?php echo $label; ?>"
												id="verify_<?php echo $index; ?>"
												<?= $is_checked ? 'checked' : '' ?>
												<?= !$is_verifier_allowed ? 'disabled' : '' ?>>
											<label for="verify_<?php echo $index; ?>"><?php echo $label; ?></label>
										</div>
									</li>
								<?php endforeach; ?>
							</ul>
						</td>
					</tr>
					<?php endif; ?>
					
					<tr>
						<th width="150"><?php echo translate('executor_review'); ?> :</th>
						<th>
							<div class="radio-custom radio-inline">
								<input type="radio" id="pending" name="task_status" value="1"
									<?= ($row['task_status'] == 1 ? 'checked' : '') ?>
									<?= !$is_assigned_user ? 'disabled' : '' ?>>
								<label for="pending"><?php echo translate('pending'); ?></label>
							</div>

							<div class="radio-custom radio-inline">
								<input type="radio" id="completed" name="task_status" value="2"
									<?= ($row['task_status'] == 2 ? 'checked' : '') ?>
									<?= !$is_assigned_user ? 'disabled' : '' ?>>
								<label for="completed"><?php echo translate('completed'); ?></label>
							</div>

							<div class="radio-custom radio-inline">
								<input type="radio" id="reject" name="task_status" value="3"
									<?= ($row['task_status'] == 3 ? 'checked' : '') ?>
									<?= !$is_assigned_user ? 'disabled' : '' ?>>
								<label for="reject"><?php echo translate('reject'); ?></label>
							</div>

							<div class="radio-custom radio-inline">
								<input type="radio" id="hold" name="task_status" value="4"
									<?= ($row['task_status'] == 4 ? 'checked' : '') ?>
									<?= !$is_assigned_user ? 'disabled' : '' ?>>
								<label for="hold"><?php echo translate('hold'); ?></label>
							</div>
						</th>
					</tr>

					<tr>
						<th width="150"><?php echo translate('verifier_review'); ?> :</th>
						<th>
							<div class="radio-custom radio-inline">
								<input type="radio" id="pending" name="verify_status" value="1"
									<?= ($row['verify_status'] == 1 ? 'checked' : '') ?>
									<?= !$is_verifier_allowed ? 'disabled' : '' ?>>
								<label for="pending"><?php echo translate('pending'); ?></label>
							</div>

							<div class="radio-custom radio-inline">
								<input type="radio" id="approved" name="verify_status" value="2"
									<?= ($row['verify_status'] == 2 ? 'checked' : '') ?>
									<?= !$is_verifier_allowed ? 'disabled' : '' ?>>
								<label for="approved"><?php echo translate('approved'); ?></label>
							</div>

							<div class="radio-custom radio-inline">
								<input type="radio" id="reject" name="verify_status" value="3"
									<?= ($row['verify_status'] == 3 ? 'checked' : '') ?>
									<?= !$is_verifier_allowed ? 'disabled' : '' ?>>
								<label for="reject"><?php echo translate('reject'); ?></label>
							</div>
						</th>
					</tr>
					
					<tr>
						<th width="150"><?php echo translate('executor_explanation'); ?> :</th>
						<td>
							<textarea name="executor_explanation" class="summernote" rows="2">
								<?php echo $row['executor_explanation']; ?>
							</textarea>
						</td>
					</tr>
					
					<tr>
						<th width="150"><?php echo translate('verifier_explanation'); ?> :</th>
						<td>
							<textarea name="verifier_explanation" class="summernote" rows="2">
								<?php echo $row['verifier_explanation']; ?>
							</textarea>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	
<footer class="panel-footer">
    <div class="row">
        <div class="col-md-12 text-right">
           <button class="btn btn-default mr-xs" type="submit" id="submit-btn">
				<i class="fas fa-check-circle"></i> <span class="btn-text"><?php echo translate('submit'); ?></span>
				<i class="fas fa-spinner fa-spin" style="display: none;"></i>
			</button>
			 <?php 
           /*  // Check if due time has passed
            $is_due_time_passed = false;
            if (!empty($row['due_time'])) {
                $current_time = date('H:i:s');
                $due_time = date('H:i:s', strtotime($row['due_time']));
                $is_due_time_passed = ($current_time > $due_time);
            }
            
            if ($is_assigned_user || $is_verifier_allowed): ?>
                    <!-- Disabled only for assigned user after due time 
                <?php if ($is_assigned_user && $is_due_time_passed): ?>
                    <button class="btn btn-default mr-xs" type="button" disabled title="Due time has passed">
                        <i class="fas fa-save"></i> <?php echo translate('submit'); ?>
                    </button>
                <?php else: ?>-->
                    <!-- Enabled for verifiers OR assigned user before due time -->
                    <button class="btn btn-default mr-xs" type="submit" id="submit-btn">
                        <i class="fas fa-check-circle"></i> <span class="btn-text"><?php echo translate('submit'); ?></span>
                        <i class="fas fa-spinner fa-spin" style="display: none;"></i>
                    </button>
               <!-- <?php endif; ?>-->
            <?php endif; */ ?>
            <button type="button" class="btn btn-default modal-dismiss"><?php echo translate('close'); ?></button>
        </div>
    </div>
</footer>

</form>





<script>
$(document).ready(function() {
	$('#approval-form').on('submit', function(e) {
		e.preventDefault();
		
		var $form = $(this);
		var $submitBtn = $('#submit-btn');
		var $btnText = $submitBtn.find('.btn-text');
		var $spinner = $submitBtn.find('.fa-spinner');
		
		// Show loading state
		$submitBtn.prop('disabled', true);
		$btnText.hide();
		$spinner.show();
		
		// Create FormData for file uploads
		var formData = new FormData(this);
		formData.append('update', '1');
		
		$.ajax({
			url: base_url + 'rdc/rdc_ajax',
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			dataType: 'json',
			success: function(response) {
				if (response.status === 'success') {
					// Close modal
					parent.$.magnificPopup.close();
					
					// Update status cells in parent table
					if (response.task_status && response.verify_status) {
						parent.updateTaskStatus(<?= $task_id ?>, response.task_status, response.verify_status);
					}
				} else {
					if (typeof toastr !== 'undefined') {
						toastr.error(response.message || 'Error updating task');
					} else {
						alert(response.message || 'Error updating task');
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


<script>
const totalExecutorChecklist = <?= $totalExecutorChecklist ?>;
const totalVerifierChecklist = <?= $totalVerifierChecklist ?>;
const taskStatus = <?= $task_status ?>;
const verifyStatus = <?= $verifier_status ?>;

let completedExecutorChecklist = <?= json_encode($executor_completed) ?>;
let completedVerifierChecklist = <?= json_encode($verifier_completed) ?>;

function checkSubmissionEligibility() {

	const executorChecklistChecked = $('input[name="executor_checklist[]"]:checked').length;
	const verifierChecklistChecked = $('input[name="verifier_checklist[]"]:checked').length;

	const isExecutorDone = (taskStatus == "2" && totalExecutorChecklist > 0 && executorChecklistChecked >= totalExecutorChecklist);
	const isVerifierDone = (verifyStatus == "2" && totalVerifierChecklist > 0 && verifierChecklistChecked >= totalVerifierChecklist);

	// ✅ Only disable if the current user is executor and executor is done
	// ✅ Or the current user is verifier and verifier is done
	let shouldDisable = false;

	<?php if ($is_assigned_user): ?>
		shouldDisable = isExecutorDone;
	<?php elseif ($is_verifier_allowed): ?>
		shouldDisable = isVerifierDone;
	<?php endif; ?>

	if (shouldDisable) {
		$('#submit-btn')
			.prop('disabled', true)
			.attr('title', 'Checklist already completed and status is final.');
	} else {
		$('#submit-btn')
			.prop('disabled', false)
			.removeAttr('title');
	}
}

// 🔁 Auto check on page load and interaction
$(document).ready(function () {
	checkSubmissionEligibility();

	$('input[name="task_status"], input[name="verify_status"], input[name="executor_checklist[]"], input[name="verifier_checklist[]"]').on('change', function () {
		checkSubmissionEligibility();
	});
});
</script>

<script>
    $(document).ready(function() {
        // Initialize dropify for all dropify elements
        $('.dropify').dropify({
            messages: {
                'default': 'Drag and drop a file here or click',
                'replace': 'Drag and drop or click to replace',
                'remove':  'Remove',
                'error':   'Ooops, something wrong happened.'
            },
            error: {
                'fileSize': 'The file size is too big ({{ value }} max).',
                'fileExtension': 'The file extension is not allowed ({{ value }} only).'
            }
        });
        
        // Handle dropify clear event to reset the input
        $('.dropify').on('dropify.beforeClear', function(event, element) {
            return confirm('Do you really want to delete "' + element.file.name + '"?');
        });
        
        $('.dropify').on('dropify.afterClear', function(event, element) {
            // File has been removed, input is now empty
            console.log('File removed from dropify');
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