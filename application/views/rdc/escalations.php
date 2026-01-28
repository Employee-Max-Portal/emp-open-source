<div class="row">
	<div class="col-md-12">
		<?php if (get_permission('rdc_escalations', 'is_view')): ?>
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><?= translate('select_ground') ?></h4>
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
							<button type="submit" name="search" value="1" class="btn btn-default btn-block">
								<i class="fas fa-filter"></i> <?= translate('filter') ?>
							</button>
						</div>
					</div>
				</footer>
			<?php echo form_close(); ?>
		</section>
		<?php endif; ?>

		
		<section class="panel appear-animation" data-appear-animation="<?=$global_config['animations'] ?>" data-appear-animation-delay="100">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-users" aria-hidden="true"></i> <?=translate('list_of_tasks')?></h4>
			</header>
			<div class="panel-body">
				<table class="table table-bordered table-condensed table-hover mb-none table-export" >
					<thead>
						<tr>
							<th style="text-align:center;" width="20"><?=translate('sl')?></th>
							<th><?=translate('assigned_to')?></th>
							<th><?=translate('task_title')?></th>
							<th style="text-align:center;" ><?=translate('verifiers')?></th>
							<th style="text-align:center;" ><?=translate('executor_review')?></th>
							<th style="text-align:center;" ><?=translate('verifier_review')?></th>
							<th style="text-align:center;" ><?= translate('escalation details') ?></th>
							<th style="text-align:center;" width="100"><?=translate('action')?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$count = 1;
						if (count($tasklist)) { 
							foreach($tasklist as $row) {
								?>
						<tr>
							<td><?php echo $count++; ?></td>
							<td><?php
								$getStaff = $this->db->select('name,staff_id')->where('id', $row['assigned_user'])->get('staff')->row_array();
								echo $getStaff['staff_id'] . ' - ' . $getStaff['name'];
								?>
							</td>
							<td><?= htmlspecialchars($row['title']) ?></td>
							<td style="text-align:center;">
							 <?php
								$verifier_ids = explode(',', $row['verifier_role']);
								$verifier_names = [];
								foreach ($verifier_ids as $rid) {
									$role = $this->db->get_where('roles', ['id' => $rid])->row();
									if ($role) {
										$verifier_names[] = $role->name;
									}
								}
								echo implode(', ', $verifier_names) ?: '<span class="text-muted">N/A</span>';
								?>
							</td>
							<td style="text-align:center;">
								<?php
									// Task Status Label
									if ($row['task_status'] == 1)
										echo '<span class="label label-warning-custom">' . translate('pending') . '</span><br>';
									else if ($row['task_status'] == 2)
										echo '<span class="label label-success-custom">' . translate('completed') . '</span><br>';
									else if ($row['task_status'] == 3)
										echo '<span class="label label-danger-custom">' . translate('rejected') . '</span><br>';
									else if ($row['task_status'] == 4)
										echo '<span class="label label-warning-custom">' . translate('hold') . '</span><br>';
									else
										echo '--<br>';

									// Executor Cleared Timestamp
									if (!empty($row['exe_cleared_on'])) {
										$exe_cleared_on = new DateTime($row['exe_cleared_on']);
										echo '<small>' . $exe_cleared_on->format('jS F, Y \a\t h.i A') . '</small>';
									} else {
										echo '<small>--</small>';
									}
								?>
							</td>


							<td style="text-align:center;">
								<?php
								// Status Label & Timestamp
								if ($row['verify_status'] == 1) {
									echo '<span class="label label-warning-custom">' . translate('pending') . '</span><br>';
									// Timestamp
									if (!empty($row['ver_cleared_on'])) {
										$ver_cleared_on = new DateTime($row['ver_cleared_on']);
										echo '<small>' . $ver_cleared_on->format('jS F, Y \a\t h.i A') . '</small>';
									} else {
										echo '<small>--</small>';
									}
								} elseif ($row['verify_status'] == 2) {
									echo '<span class="label label-success-custom">' . translate('approved') . '</span><br>';
									// Timestamp
									if (!empty($row['ver_cleared_on'])) {
										$ver_cleared_on = new DateTime($row['ver_cleared_on']);
										echo '<small>' . $ver_cleared_on->format('jS F, Y \a\t h.i A') . '</small>';
									} else {
										echo '<small>--</small>';
									}
								} elseif ($row['verify_status'] == 3) {
									echo '<span class="label label-danger-custom">' . translate('rejected') . '</span><br>';
									// Timestamp
									if (!empty($row['ver_cleared_on'])) {
										$ver_cleared_on = new DateTime($row['ver_cleared_on']);
										echo '<small>' . $ver_cleared_on->format('jS F, Y \a\t h.i A') . '</small>';
									} else {
										echo '<small>--</small>';
									}
								} elseif ($row['verify_status'] == 4) {
									echo '<span class="label label-default">' . translate('not applicable') . '</span>';
								} else {
									echo '--<br>';
								}
								?>
							</td>

							<td style="text-align:center;">
								<?php if (!empty($row['escalations'])): ?>
									<button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#escalationModal_<?= $row['id'] ?>">
										<?= translate('view_escalations') ?>
									</button>
								<?php else: ?>
									<span class="text-muted">--</span>
								<?php endif; ?>
							</td>


							<td>
								<a href="javascript:void(0);" class="btn btn-info btn-circle icon" onclick="getDetailedSop('<?=$row['id']?>')" data-toggle="tooltip" data-original-title="<?php echo translate('View SOP'); ?>" >
									<i class="fas fa-eye" style="color: #ffffff;"></i>
								</a>
								<a href="javascript:void(0);" class="btn btn-circle icon btn-default" onclick="getRDCTaskDetails('<?=$row['id']?>')" data-toggle="tooltip" data-original-title="<?php echo translate('View Task'); ?>"  >
									<i class="fas fa-bars"></i>
								</a>
							<?php if (get_permission('rdc_escalations', 'is_view')) { ?>
								<a href="javascript:void(0);" class="btn btn-circle icon btn-default" onclick="blockerLogic('<?=$row['id']?>')" data-toggle="tooltip" data-original-title="<?php echo translate('action'); ?>"  >
									<i class="far fa-arrow-alt-circle-right"></i>
								</a>
							<?php } ?>
							</td>
						</tr>
						<?php } } ?>
					</tbody>
				</table>
			</div>
		</section>
	</div>
</div>

<!-- Escalation Modals -->
<?php if (count($tasklist)) { 
	foreach($tasklist as $row) {
		if (!empty($row['escalations'])): ?>
<div class="modal fade" id="escalationModal_<?= $row['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="escalationModalLabel_<?= $row['id'] ?>" aria-hidden="true">
  <div class="modal-dialog modal-lg">
	<div class="modal-content">
	  <div class="modal-header bg-info">
		<h5 class="modal-title text-white" id="escalationModalLabel_<?= $row['id'] ?>"><?= translate('escalation_details') ?> - <?= htmlspecialchars($row['title']) ?></h5>
		<button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 1; font-size: 24px; font-weight: bold;">
		  <span aria-hidden="true">&times;</span>
		</button>
	  </div>
	  <div class="modal-body">
		<table class="table table-bordered table-condensed table-striped">
		  <thead>
			<tr>
			  <th><?= translate('escalated_person') ?></th>
			  <th><?= translate('action_type') ?></th>
			  <th><?= translate('escalation_reason') ?></th>
			  <th width="80"><?= translate('action') ?></th>
			</tr>
		  </thead>
		  <tbody>
			<?php foreach ($row['escalations'] as $index => $e): ?>
			  <tr id="escalation_row_<?= $row['id'] ?>_<?= $index ?>">
				<td class="escalated-person"><?= $e['escalated_person'] ?></td>
				<td class="action-type"><?= ucwords(str_replace('_', ' ', $e['action_type'])) ?></td>
				<td class="escalation-reason"><?= nl2br(htmlspecialchars($e['escaltion_reason'])) ?></td>
				<td>
				  <button type="button" class="btn btn-xs btn-warning edit-escalation" 
						  data-task-id="<?= $row['id'] ?>" 
						  data-index="<?= $index ?>"
						  data-action="<?= $e['action_type'] ?>"
						  data-reason="<?= htmlspecialchars($e['escaltion_reason']) ?>">
					<i class="fas fa-edit"></i>
				  </button>
				</td>
			  </tr>
			<?php endforeach; ?>
		  </tbody>
		</table>
	  </div>
	  <div class="modal-footer">
		<button type="button" class="btn btn-success" id="save-escalation-<?= $row['id'] ?>" style="display:none;" onclick="saveEscalation(<?= $row['id'] ?>)">
		  <i class="fas fa-save"></i> <?= translate('save') ?>
		</button>
		<button type="button" class="btn btn-secondary" id="cancel-edit-<?= $row['id'] ?>" style="display:none;" onclick="cancelEdit(<?= $row['id'] ?>)">
		  <i class="fas fa-times"></i> <?= translate('cancel') ?>
		</button>
		<button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-times"></i> <?= translate('close') ?></button>
	  </div>
	</div>
  </div>
</div>
<?php 		endif;
	}
} ?>

<!-- View Modal -->
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
	<section class="panel" id='quick_view'></section>
</div>



		
<div class="zoom-anim-dialog modal-block modal-block-lg mfp-hide payroll-t-modal" id="modal_equipment_details" style="width: 100%!important;">
    <section class="panel">
        <header class="panel-heading d-flex justify-content-between align-items-center">
            <div class="row">
                <div class="col-md-6 text-left">
                    <h4 class="panel-title">
						<i class="fas fa-bars"></i> <?php echo translate('Esclations'); ?>
					</h4>
                </div>
            </div>
        </header>
        <div class="panel-body">
            <div id="equipment_details_view_tray">
                <!-- The description content will be loaded here dynamically -->
            </div>
        </div>
        <footer class="panel-footer">
            <div class="row">
                <div class="col-md-offset-11">
                    <button class="btn btn-default modal-dismiss"><?php echo translate('close'); ?></button>
                </div>
            </div>
        </footer>
    </section>
</div>


<script>
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
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
<script type="text/javascript">
	$(document).ready(function () {
		$('#daterange').daterangepicker({
			opens: 'left',
		    locale: {format: 'YYYY/MM/DD'}
		});
	});

	// get Logic details
	function blockerLogic(id) {
	    $.ajax({
	        url: base_url + 'rdc/blockerLogic',
	        type: 'POST',
	        data: {'id': id},
	        dataType: "html",
	        success: function(response) {
				// Inject the response into the modal
				$('#equipment_details_view_tray').html(response);

				// Open the modal
				$.magnificPopup.open({
					items: {
						src: '#modal_equipment_details'
					},
					type: 'inline'
				});
			},
			error: function() {
				alert('Failed to retrieve description.');
			}
	    });
	}

	// get approvel details
	function getRDCTaskDetails(id) {
	    $.ajax({
	        url: base_url + 'rdc/getRDCTaskDetails',
	        type: 'POST',
	        data: {'id': id},
	        dataType: "html",
	        success: function (data) {
				$('#quick_view').html(data);
				mfp_modal('#modal');
	        }
	    });
	}

	// get Sop details
	function getDetailedSop(id) {
	    $.ajax({
	        url: base_url + 'rdc/getDetailedSop',
	        type: 'POST',
	        data: {'id': id},
	        dataType: "html",
	        success: function (data) {
				$('#quick_view').html(data);
				mfp_modal('#modal');
	        }
	    });
	}

	// Edit escalation functionality
	$(document).on('click', '.edit-escalation', function() {
		var taskId = $(this).data('task-id');
		var index = $(this).data('index');
		var currentReason = $(this).data('reason');
		var currentAction = $(this).data('action');
		var row = $('#escalation_row_' + taskId + '_' + index);
		
		// Replace action type cell with select
		var actionCell = row.find('.action-type');
		var actionSelect = '<select class="form-control" id="edit_action_' + taskId + '_' + index + '">' +
			'<option value="block_salary"' + (currentAction === 'block_salary' ? ' selected' : '') + '>Block Salary</option>' +
			'<option value="showcause"' + (currentAction === 'showcause' ? ' selected' : '') + '>Showcause</option>' +
			'<option value="cleared"' + (currentAction === 'cleared' ? ' selected' : '') + '>Cleared</option>' +
			'</select>';
		actionCell.html(actionSelect);
		
		// Replace reason cell with textarea
		var reasonCell = row.find('.escalation-reason');
		reasonCell.html('<textarea class="form-control" rows="3" id="edit_reason_' + taskId + '_' + index + '">' + currentReason + '</textarea>');
		
		// Hide edit button, show save/cancel buttons
		$(this).hide();
		$('#save-escalation-' + taskId).show();
		$('#cancel-edit-' + taskId).show();
		
		// Store original data for cancel
		$('#escalationModal_' + taskId).data('editing', {taskId: taskId, index: index, originalReason: currentReason, originalAction: currentAction});
	});
	
	function saveEscalation(taskId) {
		var editData = $('#escalationModal_' + taskId).data('editing');
		var newReason = $('#edit_reason_' + taskId + '_' + editData.index).val();
		var newAction = $('#edit_action_' + taskId + '_' + editData.index).val();
		
		if (!newReason.trim()) {
			alert('Reason cannot be empty');
			return;
		}
		
		$.ajax({
			url: base_url + 'rdc/update_escalation_reason',
			type: 'POST',
			data: {
				task_id: taskId,
				index: editData.index,
				new_reason: newReason,
				new_action: newAction
			},
			success: function(response) {
				var result = JSON.parse(response);
				if (result.status === 'success') {
					// Update the display
					var row = $('#escalation_row_' + taskId + '_' + editData.index);
					row.find('.escalation-reason').html(newReason.replace(/\n/g, '<br>'));
					row.find('.action-type').html(newAction.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()));
					
					// Update button data attributes
					row.find('.edit-escalation').data('reason', newReason).data('action', newAction);
					
					// Reset buttons
					resetEditMode(taskId);
					
				} else {
					alert('Error: ' + result.message);
				}
			},
			error: function() {
				alert('Failed to update escalation');
			}
		});
	}
	
	function cancelEdit(taskId) {
		var editData = $('#escalationModal_' + taskId).data('editing');
		var row = $('#escalation_row_' + taskId + '_' + editData.index);
		
		// Restore original action and reason
		row.find('.action-type').html(editData.originalAction.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()));
		row.find('.escalation-reason').html(editData.originalReason.replace(/\n/g, '<br>'));
		
		// Reset buttons
		resetEditMode(taskId);
	}
	
	function resetEditMode(taskId) {
		$('#save-escalation-' + taskId).hide();
		$('#cancel-edit-' + taskId).hide();
		$('.edit-escalation[data-task-id="' + taskId + '"]').show();
		$('#escalationModal_' + taskId).removeData('editing');
	}

	// Update escalation details instantly
	function updateEscalationDetails(taskId, escalationData) {
		// Find the escalation details cell for this task
		var escalationCell = $('tr').find('td').filter(function() {
			return $(this).find('button[data-target="#escalationModal_' + taskId + '"]').length > 0;
		});
		
		if (escalationCell.length > 0) {
			// Update the button to show escalations are available
			escalationCell.html('<button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#escalationModal_' + taskId + '"><?= translate('view_escalations') ?></button>');
			
			// Update the modal content if it exists
			var modal = $('#escalationModal_' + taskId);
			if (modal.length > 0) {
				// Rebuild the escalation table in the modal
				var tableBody = modal.find('tbody');
				var newRows = '';
				
				if (escalationData && escalationData.length > 0) {
					$.each(escalationData, function(index, e) {
						newRows += '<tr id="escalation_row_' + taskId + '_' + index + '">' +
							'<td class="escalated-person">' + e.escalated_person + '</td>' +
							'<td class="action-type">' + e.action_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) + '</td>' +
							'<td class="escalation-reason">' + e.escaltion_reason.replace(/\n/g, '<br>') + '</td>' +
							'<td><button type="button" class="btn btn-xs btn-warning edit-escalation" ' +
								'data-task-id="' + taskId + '" data-index="' + index + '" ' +
								'data-action="' + e.action_type + '" data-reason="' + e.escaltion_reason + '">' +
								'<i class="fas fa-edit"></i></button></td>' +
							'</tr>';
					});
				}
				
				tableBody.html(newRows);
			}
		} else {
			// If no escalation cell found, try to find by task ID in action buttons
			var actionCell = $('a[onclick*="blockerLogic(\'' + taskId + '\')"').closest('td');
			if (actionCell.length > 0) {
				var escalationDetailCell = actionCell.prev();
				if (escalationDetailCell.length > 0) {
					escalationDetailCell.html('<button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#escalationModal_' + taskId + '"><?= translate('view_escalations') ?></button>');
				}
			}
		}
	}

</script>