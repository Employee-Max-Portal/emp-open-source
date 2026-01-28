<style>
    
@media (min-width: 768px) {
    .modal-dialog {
        width: 90%;
        margin: 20px auto;
    }
}

.goal-detail {
    background: #f8fafc;
    min-height: 100vh;
}

.goal-header {
    background: #fff;
    color: black;
    padding: 30px 0;
    margin-bottom: 30px;
    border-radius: 0 0 20px 20px;
}

.detail-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    height: 100%;
}

.status-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-on_track { background: #dcfce7; color: #166534; }
.status-at_risk { background: #fef3c7; color: #92400e; }
.status-blocked { background: #fecaca; color: #991b1b; }

.pod-member {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8fafc;
    border-radius: 8px;
    margin-bottom: 8px;
}

.member-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.target-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: #f8fafc;
    border-radius: 8px;
    margin-bottom: 8px;
}

.metrics-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.metric-card {
    background: white;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.metric-number {
    font-size: 28px;
    font-weight: 700;
    color: #1e293b;
}

.metric-label {
    font-size: 14px;
    color: #64748b;
    margin-top: 8px;
}

.stage-selector {
    display: flex;
    gap: 12px;
    margin: 20px 0;
}

.stage-btn {
    flex: 1;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.stage-why { background: #dbeafe; color: #1e40af; }
.stage-how { background: #fed7aa; color: #c2410c; }
.stage-who { background: #dcfce7; color: #166534; }

.stage-btn.active {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
</style>

<div class="goal-detail">
    <div class="goal-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <h1 style="margin: 0; font-size: 32px; font-weight: 700;"><?= htmlspecialchars($goal['goal_name']) ?></h1>
                </div>
                <div class="col-md-4 text-right">
                    <span class="status-badge status-<?= $goal['status'] ?>">
                        <?php
                        $status_icons = ['on_track' => '🟢', 'at_risk' => '🟠', 'blocked' => '🔴'];
                        echo $status_icons[$goal['status']] . ' ' . ucfirst(str_replace('_', ' ', $goal['status']));
                        ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        
        <div class="row" style="display: flex; align-items: stretch;">
            <div class="col-md-4" style="display: flex;">
                <!-- Execution Stage -->
                <div class="detail-card" style="display: flex; flex-direction: column; width: 100%;">
                    <h4><i class="fas fa-tasks"></i> Execution Stage</h4>
                    <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; text-align: center;">
                        <div id="stage-buttons" style="text-align: center; margin: 20px 0; display: flex; gap: 10px; justify-content: center;">
                            <?php 
                            $stages = ['WHY', 'HOW', 'WHO'];
                            $currentStageIndex = array_search($goal['execution_stage'], $stages);
                            foreach ($stages as $index => $stage): 
                                if ($index <= $currentStageIndex):
                                    $isActive = ($stage === $goal['execution_stage']);
                                    $opacity = $isActive ? '1' : '0.5';
                                    $stageJust = strtolower($stage) . '_justification';
                                    $stageDate = strtolower($stage) . '_updated_at';
                            ?>
                                <div class="stage-btn stage-<?= strtolower($stage) ?> <?= $isActive ? 'active' : '' ?>" 
                                     data-stage="<?= $stage ?>"
                                     data-justification="<?= htmlspecialchars($goal[$stageJust] ?? '') ?>"
                                     data-updated="<?= !empty($goal[$stageDate]) ? date('M d, Y h:i A', strtotime($goal[$stageDate])) : '' ?>"
                                     style="display: inline-block; padding: 12px 20px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; opacity: <?= $opacity ?>;" 
                                     onclick="updateStage(<?= $goal['id'] ?>, '<?= $goal['execution_stage'] ?>', this)">
                                    <?= $stage ?>
                                </div>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                        <?php if (!empty($goal['stage_justification'])): ?>
                            <div style="margin-top: 16px; padding: 12px; background: #f1f5f9; border-radius: 8px; font-size: 12px;">
                                <strong>Last Change:</strong><br>
                                <?= htmlspecialchars($goal['stage_justification']) ?><br>
                                <small class="text-muted">Updated: <?= date('M d, Y h:i A', strtotime($goal['updated_at'])) ?></small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4" style="display: flex;">
                <!-- Task Metrics -->
                <div class="detail-card" style="display: flex; flex-direction: column; width: 100%;">
                    <h4><i class="fas fa-chart-bar"></i> Task Metrics</h4>
                    <div style="flex: 1; display: flex; justify-content: space-around; align-items: center; text-align: center;">
                        <div>
                            <div class="metric-number" style="font-size: 24px;"><?= $goal['task_metrics']['open_tasks'] ?></div>
                            <div class="metric-label" style="font-size: 12px;">Open Tasks</div>
                        </div>
                        <div>
                            <div class="metric-number" style="font-size: 24px;"><?= number_format($goal['task_metrics']['total_hours'], 1) ?>h</div>
                            <div class="metric-label" style="font-size: 12px;">Total Hours</div>
                        </div>
                        <div>
                            <div class="metric-number" style="font-size: 24px;"><?= number_format($goal['task_metrics']['today_hours'], 1) ?>h</div>
                            <div class="metric-label" style="font-size: 12px;">Today</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4" style="display: flex;">
                <!-- Financial Summary -->
                <div class="detail-card" style="display: flex; flex-direction: column; width: 100%;">
                    <h4><i class="fas fa-chart-line"></i> Cost Summary</h4>
                    <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
                        <?php 
                        $cost_per_hour = $global_config['cost_per_hour'] ?? 50;
                        $currency_symbol = $global_config['currency_symbol'] ?? '$';
                        $indirect_cost = $goal['task_metrics']['total_hours'] * $cost_per_hour;
                        $total_cost = $goal['financial_metrics']['total_cost'] + $indirect_cost;
                        ?>
                        <div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span>Direct Cost:</span>
                                <span><?= $currency_symbol ?><?= number_format($goal['financial_metrics']['total_cost'], 2) ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span>Indirect Cost:</span>
                                <span><?= $currency_symbol ?><?= number_format($indirect_cost, 2) ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-weight: 600; border-top: 1px solid #e2e8f0; padding-top: 8px;">
                                <span>Total Cost:</span>
                                <span><?= $currency_symbol ?><?= number_format($total_cost, 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <!-- Second Row: Content and Pod Members -->
        <div class="row">
            <div class="col-md-8">
                <!-- Goal Description -->
                <div class="detail-card">
                    <h4><i class="fas fa-info-circle"></i> Goal Description</h4>
                    <div><?= html_entity_decode($goal['description']) ?></div>
                </div>

                <!-- Targets -->
                <div class="detail-card">
                    <h4><i class="fas fa-bullseye"></i> Targets</h4>
                    <?php if (!empty($goal['targets'])): ?>
                        <ul style="margin: 0; padding-left: 20px;">
                            <?php foreach ($goal['targets'] as $target): ?>
                                <li style="margin-bottom: 8px; line-height: 1.5;">
                                    <strong><?= htmlspecialchars($target['target_name']) ?></strong>
                                    <?php if (!empty($target['target_value'])): ?>
                                        <span style="color: #64748b;"> - <?= htmlspecialchars($target['target_value']) ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p style="color: #64748b;">No targets defined yet.</p>
                    <?php endif; ?>
                </div>

                <!-- Tasks -->
                <div class="detail-card">
                    <h4><i class="fas fa-tasks"></i> Open Tasks</h4>
                    <div id="goal-tasks-list">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin"></i> Loading tasks...
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Pod Members -->
                <div class="detail-card">
                    <h4><i class="fas fa-users"></i> Pod Members</h4>
                    <?php if (!empty($goal['pod_members'])): ?>
                        <?php foreach ($goal['pod_members'] as $member): ?>
                            <div class="pod-member">
                                <img src="<?= get_image_url('staff', $member['photo']) ?>" 
                                     alt="<?= htmlspecialchars($member['name']) ?>" class="member-avatar">
                                <div>
                                    <div style="font-weight: 600;"><?= htmlspecialchars($member['name']) ?></div>
                                    <div style="font-size: 12px; color: #64748b;"><?= htmlspecialchars($member['role']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #64748b;">No pod members assigned.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Attachments -->
                <?php if (!empty($goal['attachments'])): ?>
                <div class="detail-card">
                    <h4><i class="fas fa-paperclip"></i> Attachments</h4>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th width="50%">File Name</th>
                                    <th width="25%">Uploaded By</th>
                                    <th width="25%" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($goal['attachments'] as $attachment): ?>
                                <tr>
                                    <td>
                                        <div style="cursor: pointer; color: #2b6cb0;" onclick="window.open('<?= base_url('uploads/attachments/goals/' . $attachment['enc_file_name']) ?>', '_blank')">
                                            <i class="fas fa-file" style="margin-right: 8px;"></i>
                                            <?= htmlspecialchars($attachment['orig_file_name']) ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($attachment['uploaded_by_name'] ?: 'Unknown') ?></td>
                                    <td class="text-center">
                                        <a href="<?= base_url('goals/download_attachment/' . $attachment['id']) ?>" class="btn btn-sm btn-outline-primary" title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button onclick="deleteAttachment(<?= $attachment['id'] ?>)" class="btn btn-sm btn-outline-danger ml-1" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Load tasks when page loads
$(document).ready(function() {
    loadGoalTasks();
});

function loadGoalTasks() {
    $.post('<?= base_url('goals/get_goal_tasks') ?>', {
        goal_id: <?= $goal['id'] ?>
    }, function(response) {
        if (response.status === 'success') {
            displayTasks(response.tasks);
        } else {
            $('#goal-tasks-list').html('<p class="text-muted">No tasks found for this goal.</p>');
        }
    }, 'json');
}

function displayTasks(tasks) {
    if (tasks.length === 0) {
        $('#goal-tasks-list').html('<p class="text-muted">No open tasks found.</p>');
        return;
    }
    
    // Group tasks by status
    const groupedTasks = {};
    tasks.forEach(task => {
        if (!groupedTasks[task.task_status]) {
            groupedTasks[task.task_status] = [];
        }
        groupedTasks[task.task_status].push(task);
    });
    
    let html = '<div class="accordion" id="tasksAccordion">';
    
    Object.keys(groupedTasks).forEach((status, index) => {
        const statusTasks = groupedTasks[status];
        const statusLabel = status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ');
        const isOpen = index === 0 ? 'show' : '';
        const collapsed = index === 0 ? '' : 'collapsed';
        
        html += `
            <div class="card">
                <div class="card-header" id="heading${status}">
                    <h6 class="mb-0">
                        <button class="btn btn-link ${collapsed}" type="button" data-toggle="collapse" data-target="#collapse${status}">
                            ${statusLabel} (${statusTasks.length})
                        </button>
                    </h6>
                </div>
                <div id="collapse${status}" class="collapse ${isOpen}" data-parent="#tasksAccordion">
                    <div class="card-body">
        `;
        
        statusTasks.forEach(task => {
            const priorityColor = {
                'high': 'danger',
                'medium': 'warning', 
                'low': 'info'
            }[task.priority] || 'secondary';
            
            html += `
                <div class="task-item" style="padding: 12px; border: 1px solid #e9ecef; border-radius: 8px; margin-bottom: 8px; cursor: pointer;" onclick="openTaskModal(${task.id})">
                    <div style="display: flex; justify-content: between; align-items: center;">
                        <div style="flex: 1;">
                            <strong>${task.subject}</strong>
                            <div style="font-size: 12px; color: #6c757d; margin-top: 4px;">
                                <span class="badge badge-${priorityColor}">${task.priority}</span>
                                ${task.staff_name ? `<span class="ml-2">Assigned to: ${task.staff_name}</span>` : ''}
                                ${task.spent_time ? `<span class="ml-2">${task.spent_time}h logged</span>` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += `
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    $('#goal-tasks-list').html(html);
}

function openTaskModal(taskId) {
    // Use the same modal approach as tasks dashboard
    window.open('<?= base_url('tasks_dashboard/task_modal/') ?>' + taskId, '_blank', 'width=800,height=600');
}

function updateStage(goalId, currentStage) {
    // Add stage update functionality if needed
    console.log('Update stage for goal:', goalId, 'current:', currentStage);
}
</script>


<!-- Stage Update Modal -->
<div class="modal fade" id="stageUpdateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Execution Stage</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="stageUpdateForm">
                <div class="modal-body">
                    <?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()) ?>
                    <input type="hidden" id="stage_goal_id" name="goal_id" value="<?= $goal['id'] ?>">
                    <input type="hidden" id="stage_value" name="stage">
                    <input type="hidden" id="next_stage" name="next_stage">
                    
                    <div class="form-group">
                        <label>Stage: <span id="stage_display"></span></label>
                    </div>
                    
                    <div id="current_stage_info"></div>
                    
                    <div class="form-group">
                        <label>Justification <span class="text-danger">*</span></label>
                        <textarea name="justification" class="form-control" rows="3" 
                                  placeholder="Why are you changing to this stage?" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Stage</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Load tasks on page load
$(document).ready(function() {
    loadGoalTasks();
});

function loadGoalTasks() {
    $.ajax({
        url: '<?= base_url('goals/get_goal_tasks') ?>',
        type: 'POST',
        data: { 
            goal_id: <?= $goal['id'] ?>,
            '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
        },
        dataType: 'json',
        success: function(response) {
            console.log('Tasks response:', response);
            if (response.status === 'success') {
                let html = '';
                if (response.tasks.length > 0) {
                    // Group tasks by status like tasks dashboard
                    const groupedTasks = {
                        todo: [],
                        in_progress: [],
                        in_review: [],
                        pending: [],
                        blocked: [],
                        hold: []
                    };
                    
                    response.tasks.forEach(function(task) {
                        const status = task.task_status || 'todo';
                        if (groupedTasks[status]) {
                            groupedTasks[status].push(task);
                        } else {
                            groupedTasks.todo.push(task);
                        }
                    });
                    
                    // Status configuration
                    const statusConfig = {
                        todo: { title: 'To Do', color: '#ffbe0b', icon: 'fas fa-clipboard-list' },
                        pending: { title: 'Pending', color: '#6c757d', icon: 'fas fa-clock' },
                        in_progress: { title: 'In Progress', color: '#3a86ff', icon: 'fas fa-play-circle' },
                        in_review: { title: 'In Review', color: '#17a2b8', icon: 'fas fa-eye' },
                        blocked: { title: 'Blocked', color: '#dc3545', icon: 'fas fa-ban' },
                        hold: { title: 'On Hold', color: '#fd7e14', icon: 'fas fa-pause-circle' }
                    };
                    
                    // Create accordion structure for each status
                    Object.keys(statusConfig).forEach(function(status) {
                        if (groupedTasks[status].length > 0) {
                            const config = statusConfig[status];
                            html += `
                                <div class="accordion-card" style="border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 8px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.07);">
                                    <div class="accordion-header" onclick="toggleTaskAccordion('goal_task_${status}')" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 15px; cursor: pointer; font-weight: 600; font-size: 12px; color: #000; background: #f7f7f8; user-select: none; transition: all 0.3s ease;">
                                        <div class="status-info" style="display: flex; align-items: center; gap: 12px;">
                                            <div class="status-icon" style="width: 24px; height: 24px; border-radius: 50%; background: ${config.color}; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px;">
                                                <i class="${config.icon}"></i>
                                            </div>
                                            <span>${config.title}</span>
                                        </div>
                                        <div class="item-count" style="background: #e8e8e9; color: black; padding: 4px 8px; border-radius: 12px; font-size: 12px;">${groupedTasks[status].length} tasks</div>
                                    </div>
                                    <div class="accordion-body" id="accordion-body-goal_task_${status}" style="max-height: 0; overflow: hidden; transition: max-height 0.4s ease-out, padding 0.4s ease-out; background-color: #ffffff;">
                            `;
                            
                            groupedTasks[status].forEach(function(task) {
                                html += `
                                    <div class="task-item" onclick="viewTask('${task.unique_id}')" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center; padding: 6px 10px; border-bottom: 1px solid #f0f0f1; background: #f7f7f8; transition: all 0.2s ease; border-radius: 8px; margin-bottom: 3px;" onmouseover="this.style.background='#e8e8e9'" onmouseout="this.style.background='#f7f7f8'">
                                        <div style="display: flex; align-items: center; gap: 10px; overflow: hidden;">
                                            <div class="task-id" style="width: 80px; flex-shrink: 0; font-weight: 600; color: #1976d2; font-size: 12px;">
                                                ${task.unique_id}
                                            </div>
                                            <div class="task-title" style="font-weight: 600; color: #2d3748; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 12px;">
                                                ${task.task_title}
                                            </div>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 5px; flex-shrink: 0;">
                                            <div style="padding: 2px 6px; background: #e3f2fd; color: #1976d2; border-radius: 12px; font-size: 9px; font-weight: 500;">
                                                ${task.staff_name || 'Unassigned'}
                                            </div>
                                            ${task.department_name ? `<div style="padding: 2px 6px; background: #e3f2fd; color: #1976d2; border-radius: 12px; font-size: 9px; font-weight: 500;">${task.department_name.substring(0, 3).toUpperCase()}.</div>` : ''}
                                            <div class="task-estimate" style="padding: 3px 8px; border: 1px solid #cccccc; background: #f2f2f4; color: #000; border-radius: 20px; font-size: 11px; min-width: 40px; text-align: center;">
                                                ${task.estimation_time || '0'}h
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                            
                            html += `
                                    </div>
                                </div>
                            `;
                        }
                    });
                } else {
                    html = `<p style="color: #64748b;">No open tasks found. ${response.debug || ''}</p>`;
                }
                $('#goal-tasks-list').html(html);
            } else {
                $('#goal-tasks-list').html(`<p style="color: #dc2626;">Error: ${response.message}</p>`);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', xhr.responseText);
            $('#goal-tasks-list').html(`<p style="color: #dc2626;">Failed to load tasks: ${error}</p>`);
        }
    });
}

// Toggle task accordion sections
function toggleTaskAccordion(id) {
    const header = document.querySelector(`[onclick="toggleTaskAccordion('${id}')"]`);
    const body = document.getElementById('accordion-body-' + id);
    
    if (body.style.maxHeight && body.style.maxHeight !== '0px') {
        body.style.maxHeight = '0px';
        body.style.padding = '0px';
        header.style.background = '#f7f7f8';
    } else {
        body.style.maxHeight = 'none';
        body.style.padding = '15px';
        header.style.background = '#e8e8e9';
        
        // Ensure proper height calculation
        setTimeout(() => {
            body.style.maxHeight = body.scrollHeight + 20 + 'px';
        }, 10);
    }
}

// View task details (reuse from tasks dashboard)
function viewTask(id) {
    $.ajax({
        url: '<?= base_url('dashboard/viewTracker_Issue') ?>',
        type: 'POST',
        data: {'id': id},
        dataType: "html",
        success: function (data) {
            // Create modal if it doesn't exist
            if (!$('#taskDetailsModal').length) {
                $('body').append(`
                    <div class="modal fade" id="taskDetailsModal" tabindex="-1" role="dialog">
                        <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Task Details</h4>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body"></div>
                            </div>
                        </div>
                    </div>
                `);
            }
            $('#taskDetailsModal .modal-body').html(data);
            $('#taskDetailsModal').modal('show');
        }
    });
}

function updateStage(goalId, currentStage, clickedBtn) {
    var clickedStage = $(clickedBtn).data('stage');
    var clickedJustification = $(clickedBtn).data('justification');
    var clickedUpdated = $(clickedBtn).data('updated');
    
    var nextStage = clickedStage === 'WHY' ? 'HOW' : (clickedStage === 'HOW' ? 'WHO' : 'WHO');
    $('#stage_goal_id').val(goalId);
    $('#stage_value').val(clickedStage);
    $('#next_stage').val(nextStage);
    $('#stage_display').text(clickedStage + ' → ' + nextStage);
    
    if (clickedJustification) {
        $('#current_stage_info').html('<div style="padding: 10px; background: #e3f2fd; border-radius: 4px; margin-bottom: 10px;"><strong>Current ' + clickedStage + ' Justification:</strong><br>' + clickedJustification + '<br><small class="text-muted">' + clickedUpdated + '</small></div>');
    } else {
        $('#current_stage_info').empty();
    }
    
    $('#stageUpdateModal').modal('show');
}

function deleteAttachment(attachmentId) {
    if (confirm('Are you sure you want to delete this attachment?')) {
        $.ajax({
            url: '<?= base_url('goals/delete_attachment') ?>',
            type: 'POST',
            data: { 
                attachment_id: attachmentId,
                '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('tr').has('button[onclick="deleteAttachment(' + attachmentId + ')"]').fadeOut(300, function() {
                        $(this).remove();
                    });
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    }
}

$('#stageUpdateForm').on('submit', function(e) {
    e.preventDefault();
    
    var formData = $(this).serialize();
    
    $.ajax({
        url: '<?= base_url('goals/update_stage') ?>',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                $('#stageUpdateModal').modal('hide');
                var newStage = $('#next_stage').val();
                var clickedStage = $('#stage_value').val();
                var goalId = $('#stage_goal_id').val();
                var justification = $('textarea[name="justification"]').val();
                var now = new Date();
                var dateStr = now.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'}) + ' ' + now.toLocaleTimeString('en-US', {hour: 'numeric', minute: '2-digit', hour12: true});
                
                var stageOrder = ['WHY', 'HOW', 'WHO'];
                var newIndex = stageOrder.indexOf(newStage);
                
                var existingStageData = {};
                $('.stage-btn').each(function() {
                    var stage = $(this).data('stage');
                    existingStageData[stage] = {
                        justification: $(this).data('justification'),
                        updated: $(this).data('updated')
                    };
                });
                
                existingStageData[clickedStage] = {
                    justification: justification,
                    updated: dateStr
                };
                
                $('#stage-buttons').empty();
                stageOrder.forEach(function(stage, index) {
                    if (index <= newIndex) {
                        var isActive = (stage === newStage);
                        var opacity = isActive ? '1' : '0.5';
                        var stageData = existingStageData[stage] || {justification: '', updated: ''};
                        var btn = $('<div></div>')
                            .addClass('stage-btn stage-' + stage.toLowerCase() + (isActive ? ' active' : ''))
                            .css({'display': 'inline-block', 'padding': '12px 20px', 'border': 'none', 'border-radius': '8px', 'font-weight': '600', 'cursor': 'pointer', 'opacity': opacity})
                            .text(stage)
                            .attr('data-stage', stage)
                            .attr('data-justification', stageData.justification)
                            .attr('data-updated', stageData.updated)
                            .on('click', function() { updateStage(goalId, newStage, this); });
                        $('#stage-buttons').append(btn);
                    }
                });
                
                $('textarea[name="justification"]').val('');
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Stage update error:', xhr.responseText);
            alert('Failed to update stage: ' + error);
        }
    });
});
</script>