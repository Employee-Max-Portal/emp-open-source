<link rel="stylesheet" href="<?= base_url('assets/css/goals-dashboard.css') ?>">

<section class="panel">
	<div class="tabs-custom">
		<div class="panel-heading">
			<h4 class="panel-title">
				<i class="fas fa-bullseye"></i> Goals Management
				<button class="btn btn-primary btn-sm pull-right" onclick="openGoalModal()">
					<i class="fas fa-plus"></i> Add New
				</button>
			</h4>
		</div>
		<div class="tab-content">
			<div class="tab-pane box active">
				<table class="table table-bordered table-hover table-condensed table-export" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th><?php echo translate('goal name'); ?></th>
							<th><?php echo translate('pod owner'); ?></th>
							<th><?php echo translate('statge'); ?></th>
							<th><?php echo translate('status'); ?></th>
							<th><?php echo translate('created'); ?></th>
							<th><?php echo translate('action'); ?></th>

						</tr>
					</thead>
					<tbody>
						<?php if (!empty($goals)): ?>
							<?php foreach ($goals as $goal): ?>
								<tr>
									<td>
										<strong><?= htmlspecialchars($goal['goal_name']) ?></strong>
										
									</td>
									<td><?= htmlspecialchars($goal['pod_owner_name']) ?></td>
									<td>
										<span class="badge badge-<?= $goal['execution_stage'] == 'WHY' ? 'primary' : ($goal['execution_stage'] == 'HOW' ? 'warning' : 'success') ?>">
											<?= $goal['execution_stage'] ?>
										</span>
									</td>
									<td>
										<span class="badge badge-<?= $goal['status'] == 'on_track' ? 'success' : ($goal['status'] == 'at_risk' ? 'warning' : 'danger') ?>">
											<?= ucfirst(str_replace('_', ' ', $goal['status'])) ?>
										</span>
									</td>
									<td><?= date('M d, Y', strtotime($goal['created_at'])) ?></td>
									<td>
										<button class="btn btn-info btn-xs" onclick="viewGoal(<?= $goal['id'] ?>)" title="View Details">
											<i class="fas fa-eye"></i>
										</button>
										<button class="btn btn-warning btn-xs" onclick="editGoal(<?= $goal['id'] ?>)" title="Edit">
											<i class="fas fa-edit"></i>
										</button>
										<?php echo btn_delete('goals/delete/' . $goal['id']); ?>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php else: ?>
							<tr>
								<td colspan="6" class="text-center">No goals found</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</section>

<!-- Goal Modal -->
<div class="modal fade" id="goalModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="panel-body">
            <div class="modal-header">
                <h5 class="modal-title" id="goalModalTitle">Add New Goal</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="goalForm" method="POST" action="<?= base_url('goals/save') ?>" enctype="multipart/form-data">
                <div class="modal-body">
                    <?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()) ?>
                    <input type="hidden" id="goal_id" name="goal_id">
                    
                    <div class="form-group">
                        <label>Goal Name <span class="text-danger">*</span></label>
                        <input type="text" name="goal_name" class="form-control" required>
                    </div>

                     <div class="form-group">
                        <label>Targets</label>
                        <div id="targets-container">
                            <div class="target-row">
                                <div class="row">
                                    <div class="col-md-11">
                                        <textarea name="targets[]" class="form-control" rows="1" placeholder="Describe the target..."></textarea>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-success btn-sm" onclick="addTarget()"><i class="fas fa-plus"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Goal Description</label>
                        <textarea name="description" id="description" class="form-control summernote" rows="2" placeholder="Describe the goal..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Milestones</label>
                        <?php
                        $milestone_options = [];
                        foreach ($milestones as $milestone) {
                            $milestone_options[$milestone['id']] = $milestone['title'];
                        }
                        echo form_dropdown(
                            'milestones[]',
                            $milestone_options,
                            '',
                            "class='form-control' multiple
                             data-plugin-selectTwo 
                             data-placeholder='Select Milestones'
                             data-width='100%'"
                        );
                        ?>
                        <small class="text-muted">Select multiple milestones</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Pod Members</label>
                        <?php
                        $staff_options = [];
                        foreach ($staff as $member) {
                            $staff_options[$member['id']] = $member['name'];
                        }
                        echo form_dropdown(
                            'pod_members[]',
                            $staff_options,
                            '',
                            "class='form-control' multiple
                             data-plugin-selectTwo 
                             data-placeholder='Select Pod Members'
                             data-width='100%'"
                        );
                        ?>
                        <small class="text-muted">Pod Owner will be added automatically</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Attachments</label>
                        <input type="file" name="attachments[]" class="form-control" multiple accept=".pdf,.doc,.docx,.jpg,.png,.xlsx">
                        <small class="text-muted">Allowed: PDF, DOC, DOCX, JPG, PNG, XLSX</small>
                        <div id="existing-attachments" style="margin-top: 10px;"></div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Pod Owner <span class="text-danger">*</span></label>
                                <?php
                                $pod_owner_options = ['' => translate('select_pod_owner')];
                                foreach ($staff as $member) {
                                    $pod_owner_options[$member['id']] = $member['name'];
                                }
                                echo form_dropdown(
                                    'pod_owner_id',
                                    $pod_owner_options,
                                    '',
                                    "class='form-control' required
                                     data-plugin-selectTwo 
                                     data-placeholder='Select Pod Owner'
                                     data-width='100%'"
                                );
                                ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Execution Stage</label>
                                <?php
                                $stage_options = [
                                    'WHY' => 'WHY',
                                    'HOW' => 'HOW', 
                                    'WHO' => 'WHO'
                                ];
                                echo form_dropdown(
                                    'execution_stage',
                                    $stage_options,
                                    'WHY',
                                    "class='form-control'
                                     data-plugin-selectTwo 
                                     data-width='100%'"
                                );
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Goal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#goalsTable').DataTable({
        "order": [[ 5, "desc" ]],
        "pageLength": 25
    });
    
    // Show success/error alerts
    <?php if ($this->session->flashdata('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '<?= $this->session->flashdata('success') ?>',
            timer: 3000,
            showConfirmButton: false
        });
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '<?= $this->session->flashdata('error') ?>'
        });
    <?php endif; ?>
});

function openGoalModal() {
    $('#goalModalTitle').text('Add New Goal');
    $('#goalForm')[0].reset();
    $('#goal_id').val('');
    
    // Reset targets container
    $('#targets-container').empty();
    addEmptyTarget();
    
    // Reset select2 dropdowns
    $('select[name="pod_owner_id"]').val('').trigger('change');
    $('select[name="execution_stage"]').val('WHY').trigger('change');
    $('select[name="milestones[]"]').val([]).trigger('change');
    $('select[name="pod_members[]"]').val([]).trigger('change');
    
    // Reset summernote
    $('#description').summernote('code', '');
    
    $('#goalModal').modal('show');
}

function editGoal(goalId) {
    // Get goal data via AJAX and populate form
    $.ajax({
        url: '<?= base_url('goals/get_goal_data') ?>',
        type: 'POST',
        data: { goal_id: goalId },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                const goal = response.goal;
                $('#goalModalTitle').text('Edit Goal');
                $('#goal_id').val(goal.id);
                $('input[name="goal_name"]').val(goal.goal_name);
                $('#description').summernote('code', goal.description || '');
                $('select[name="pod_owner_id"]').val(goal.pod_owner_id).trigger('change');
                $('select[name="execution_stage"]').val(goal.execution_stage).trigger('change');
                
                // Populate targets
                $('#targets-container').empty();
                if (goal.targets && goal.targets.length > 0) {
                    goal.targets.forEach(function(target, index) {
                        const isFirst = index === 0;
                        const html = `<div class="target-row" ${!isFirst ? 'style="margin-top: 10px;"' : ''}>
                            <div class="row">
                                <div class="col-md-11">
                                    <textarea name="targets[]" class="form-control" rows="1" placeholder="Describe the target...">${target.target_name}</textarea>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-${isFirst ? 'success' : 'danger'} btn-sm" onclick="${isFirst ? 'addTarget()' : '$(this).closest(\".target-row\").remove()'}">
                                        <i class="fas fa-${isFirst ? 'plus' : 'minus'}"></i>
                                    </button>
                                </div>
                            </div>
                        </div>`;
                        $('#targets-container').append(html);
                    });
                } else {
                    addEmptyTarget();
                }
                
                // Populate milestones
                if (goal.milestone_ids) {
                    $('select[name="milestones[]"]').val(goal.milestone_ids).trigger('change');
                }
                
                // Populate pod members
                if (goal.member_ids) {
                    $('select[name="pod_members[]"]').val(goal.member_ids).trigger('change');
                }
                
                // Show existing attachments
                $('#existing-attachments').empty();
                if (goal.attachments && goal.attachments.length > 0) {
                    let attachmentHtml = '<div style="margin-top: 10px;"><strong>Existing Attachments:</strong></div>';
                    goal.attachments.forEach(function(attachment) {
                        attachmentHtml += `<div class="attachment-row" data-attachment-id="${attachment.id}" style="background: #f8f9fa; margin: 5px 0;">
                            <div class="col-md-6">
                                <span style="cursor: pointer; color: #007bff;" onclick="window.open('<?= base_url('uploads/attachments/goals/') ?>' + attachment.enc_file_name, '_blank')">
                                    <i class="fas fa-file"></i> ${attachment.orig_file_name}
                                </span>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">by ${attachment.uploaded_by_name || 'Unknown'}</small>
                            </div>
                            <div class="col-md-3 text-right">
                                <button type="button" onclick="deleteAttachmentFromEdit(${attachment.id})" class="btn btn-xs btn-outline-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>`;
                    });
                    $('#existing-attachments').html(attachmentHtml);
                }
                
                $('#goalModal').modal('show');
            }
        }
    });
}

function addEmptyTarget() {
    const html = `<div class="target-row">
        <div class="row">
            <div class="col-md-11">
                <textarea name="targets[]" class="form-control" rows="1" placeholder="Describe the target..."></textarea>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-success btn-sm" onclick="addTarget()"><i class="fas fa-plus"></i></button>
            </div>
        </div>
    </div>`;
    $('#targets-container').append(html);
}

function viewGoal(goalId) {
    window.location.href = '<?= base_url('goals/detail/') ?>' + goalId;
}

$('#goalForm').on('submit', function(e) {
    e.preventDefault();
    
    var formData = new FormData(this);
    var goalId = $('#goal_id').val();
    
    $.ajax({
        url: '<?= base_url('goals/save') ?>',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                $('#goalModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                updateTableRow(response.goal, goalId);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: response.message
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to save goal'
            });
        }
    });
});

function updateTableRow(goal, isEdit) {
    var table = $('.table-export').DataTable();
    var stageBadge = goal.execution_stage == 'WHY' ? 'primary' : (goal.execution_stage == 'HOW' ? 'warning' : 'success');
    var statusBadge = goal.status == 'on_track' ? 'success' : (goal.status == 'at_risk' ? 'warning' : 'danger');
    var statusText = goal.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
    var createdDate = new Date(goal.created_at).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'});
    
    var rowData = [
        '<strong>' + goal.goal_name + '</strong>',
        goal.pod_owner_name,
        '<span class="badge badge-' + stageBadge + '">' + goal.execution_stage + '</span>',
        '<span class="badge badge-' + statusBadge + '">' + statusText + '</span>',
        createdDate,
        '<button class="btn btn-info btn-xs" onclick="viewGoal(' + goal.id + ')" title="View Details"><i class="fas fa-eye"></i></button> ' +
        '<button class="btn btn-warning btn-xs" onclick="editGoal(' + goal.id + ')" title="Edit"><i class="fas fa-edit"></i></button> ' +
        '<a href="<?= base_url("goals/delete/") ?>' + goal.id + '" class="btn btn-danger btn-xs" onclick="return confirm(\'Are you sure?\')" title="Delete"><i class="fas fa-trash"></i></a>'
    ];
    
    if (isEdit) {
        var row = table.rows().nodes().to$().filter(function() {
            return $(this).find('button[onclick*="editGoal(' + goal.id + ')"]').length > 0;
        });
        table.row(row).data(rowData).draw(false);
    } else {
        table.row.add(rowData).draw(false);
    }
}

function addTarget() {
    const html = `<div class="target-row" style="margin-top: 10px;">
        <div class="row">
            <div class="col-md-11">
                <textarea name="targets[]" class="form-control" rows="1" placeholder="Describe the target..."></textarea>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger btn-sm" onclick="$(this).closest('.target-row').remove()"><i class="fas fa-minus"></i></button>
            </div>
        </div>
    </div>`;
    $('#targets-container').append(html);
}

function deleteAttachmentFromEdit(attachmentId) {
    $(`.attachment-row[data-attachment-id="${attachmentId}"]`).remove();
    $('<input>').attr({
        type: 'hidden',
        name: 'delete_attachments[]',
        value: attachmentId
    }).appendTo('#goalForm');
}

function addMilestone() {
    // Not needed anymore since using dropdown
}
</script>