<link rel="stylesheet" href="<?= base_url('assets/css/goals-dashboard.css') ?>">

<style>
/* CEO Dashboard Styles */
.ceo-header-card {
    background: #f8f9fa;
    color: #2d3748;
    padding: 15px;
    border-radius: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
}

.header-content h2 {
    margin: 0 0 10px 0;
    font-size: 28px;
    font-weight: 700;
}

.header-content p {
    margin: 0;
    color: #6c757d;
    font-size: 16px;
}

.header-stats {
    display: flex;
    gap: 30px;
}

.stat-item {
    text-align: center;
}

.stat-value {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 1px;
}

@media (max-width: 768px) {
    .ceo-header-card {
        flex-direction: column;
        gap: 15px;
        padding: 12px;
        text-align: center;
    }
    .header-content h2 {
        font-size: 20px;
        margin-bottom: 5px;
    }
    .header-content p {
        font-size: 14px;
    }
    .header-stats {
        gap: 15px;
        flex-wrap: wrap;
        justify-content: center;
    }
    .stat-value {
        font-size: 18px;
    }
    .stat-label {
        font-size: 10px;
    }
}
</style>


 <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="ceo-header-card">
                <div class="header-content">
                    <h2>Goals Overview</h2>
                    <p>Track progress, budgets, and team performance across all active goals and pods.</p>
                </div>
                <div class="header-stats">
                    <?php 
                    $total_goals = count($goals);
                    $total_tasks = 0;
                    $total_hours = 0;
                    $total_remaining_hours = 0;
                    $total_direct_cost = 0;
                    $cost_per_hour = $global_config['cost_per_hour'] ?? 50;
                    
                    foreach ($goals as $goal) {
                        $total_tasks += $goal['task_metrics']['open_tasks'];
                        $total_hours += $goal['task_metrics']['total_hours'];
                        $total_remaining_hours += $goal['task_metrics']['remaining_hours'];
                        $total_direct_cost += $goal['financial_metrics']['total_cost'];
                    }
                    $total_indirect_cost = $total_hours * $cost_per_hour;
                    ?>
                    <div class="stat-item">
                        <div class="stat-value"><?= $total_goals ?></div>
                        <div class="stat-label">Active Goals</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $total_tasks ?></div>
                        <div class="stat-label">Remaining Tasks</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= number_format($total_hours, 0) ?>h</div>
                        <div class="stat-label">Total Hours</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= number_format($total_remaining_hours, 0) ?>h</div>
                        <div class="stat-label">Remaining Hours</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
	
<div class="row">
    <div class="container-fluid">
        <div class="row" id="goals-grid">
            <?php if (!empty($goals)): ?>
                <?php foreach ($goals as $goal): ?>
                    <div class="col-md-4 mb-4">
                        <div class="goal-card" data-goal-id="<?= $goal['id'] ?>" onclick="openGoalDetail(<?= $goal['id'] ?>)">
                            <!-- Goal Header -->
                            <div class="goal-header">
                                <div>
                                    <div class="goal-name" style="font-weight: 700; font-size: 18px;"><?= htmlspecialchars($goal['goal_name']) ?></div>
                                </div>
                            </div>
							<hr>
                            <!-- Pod Section -->
                            <div class="pod-section">
                                <div class="pod-owner">
                                    <div>
                                        <strong>Owner:</strong> <img src="<?= get_image_url('staff', $goal['pod_owner_photo']) ?>" alt="Pod Owner" class="pod-avatar"> <span class="pod-owner-name"><?= htmlspecialchars($goal['pod_owner_name']) ?> </span>
                                    </div>
                                </div>
                                
                                <?php if (!empty($goal['pod_members'])): ?>
                                    <div class="pod-members">
										<div>
											<strong>Members:</strong>						
											<?php foreach ($goal['pod_members'] as $member): ?>
												<?php if ($member['staff_id'] != $goal['pod_owner_id']): ?>
													<img src="<?= get_image_url('staff', $member['photo']) ?>" 
														 alt="<?= htmlspecialchars($member['name']) ?>" 
														 class="member-avatar"
														 title="<?= htmlspecialchars($member['name']) ?> - <?= htmlspecialchars($member['role']) ?>">
												<?php endif; ?>
											<?php endforeach; ?>
										</div>
									</div>
                                <?php endif; ?>
                            </div>

							<!-- Execution Stage -->
                            <div class="stage-indicator">
                                <strong>Stage:</strong>
                                <?php 
                                $stages = ['WHY', 'HOW', 'WHO'];
                                $currentStageIndex = array_search($goal['execution_stage'], $stages);
                                foreach ($stages as $index => $stage): 
                                    $isActive = ($stage === $goal['execution_stage']);
                                    $isCompleted = ($index < $currentStageIndex);
                                    $opacity = $isCompleted ? '0.5' : '1';
                                    $stageJust = strtolower($stage) . '_justification';
                                    $stageDate = strtolower($stage) . '_updated_at';
                                ?>
                                <div class="stage-item stage-<?= strtolower($stage) ?> <?= $isActive ? 'active' : '' ?>" 
                                     style="opacity: <?= $opacity ?>;"
                                     data-stage="<?= $stage ?>"
                                     data-justification="<?= htmlspecialchars($goal[$stageJust] ?? '') ?>"
                                     data-updated="<?= !empty($goal[$stageDate]) ? date('M d, Y h:i A', strtotime($goal[$stageDate])) : '' ?>"
                                     onclick="event.stopPropagation(); updateStage(<?= $goal['id'] ?>, '<?= $goal['execution_stage'] ?>', this)">
                                    <?= $stage ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <!-- Targets Section -->
                            <?php if (!empty($goal['targets'])): ?>
                                <div class="targets-section">
								
                                <strong>Targets:</strong> 
                                    <?php 
                                    $target_names = [];
                                    foreach ($goal['targets'] as $target) {
                                        $target_names[] = $target['target_name'];
                                    }
                                    echo implode(' | ', $target_names);
                                    ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Task & Execution Metrics -->
                            <div class="metrics-grid">
                                <div class="metric-item" onclick="event.stopPropagation(); showGoalTasks(<?= $goal['id'] ?>)">
                                    <div class="metric-number"><?= $goal['task_metrics']['open_tasks'] ?> Tasks</div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-number"><?= number_format($goal['task_metrics']['total_hours'], 1) ?>H</div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-number"><?= number_format($goal['task_metrics']['remaining_hours'], 1) ?>H</div>
                                </div>
                            </div>
								
                            <!-- Financial Metrics -->
                            <div class="financial-section">
                                <?php 
                                $indirect_cost = $goal['task_metrics']['total_hours'] * $global_config['total_cost_per_hour'];
                                ?>
                                <div style="font-size: 12px; color: #64748b;">Direct Cost: ৳<?= number_format($goal['financial_metrics']['total_cost'], 2) ?></div>
                                <div style="font-size: 12px; color: #64748b;">Indirect Cost: ৳<?= number_format($indirect_cost, 2) ?></div>
                            </div>

                            <!-- Last Update -->
                            <div class="footer-section">
                                <span class="status-badge status-<?= $goal['status'] ?>">
                                    <?= ucfirst(str_replace('_', ' ', $goal['status'])) ?>
                                </span>
                                <span class="last-update">Last: <?= date('M d, H:i', strtotime($goal['last_update'])) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-md-12">
                    <div class="text-center" style="padding: 60px 20px;">
                        <i class="fas fa-bullseye fa-4x" style="color: #cbd5e1; margin-bottom: 20px;"></i>
                        <h4 style="color: #64748b;">No Goals Configured</h4>
                        <p style="color: #94a3b8;">Start by creating your first 2026 goal</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Goal Tasks Modal -->
<div class="modal fade" id="goalTasksModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-tasks"></i> Goal Tasks
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="goal-tasks-content">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin"></i> Loading tasks...
                </div>
            </div>
        </div>
    </div>
</div>

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
                    <input type="hidden" id="stage_goal_id" name="goal_id">
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

<!-- Trigger click on page load -->
<script>
$(document).ready(function() {
    // Ensure the button exists and is visible
    if ($('#sidebar-toggle-button').length) {
        setTimeout(function() {
            $('#sidebar-toggle-button').click();  // Trigger the click event
        }, 10);  // 100ms delay to ensure it's ready
    }
});
</script>

<script>
// Auto-refresh dashboard every 30 seconds
setInterval(function() {
    refreshDashboard();
}, 30000);

function refreshDashboard() {
    $.ajax({
        url: '<?= base_url('goals/get_live_data') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                updateDashboardData(response.goals);
                $('#last-updated').text(response.last_updated);
            }
        }
    });
}

function updateDashboardData(goals) {
    // Update goal cards with new data
    goals.forEach(function(goal) {
        const card = $(`.goal-card[data-goal-id="${goal.id}"]`);
        if (card.length) {
            // Update metrics
            card.find('.metric-number').eq(0).text(goal.task_metrics.open_tasks);
            card.find('.metric-number').eq(1).text(parseFloat(goal.task_metrics.total_hours).toFixed(1) + 'h');
            card.find('.metric-number').eq(2).text(parseFloat(goal.task_metrics.today_hours).toFixed(1) + 'h');
            
            // Update status badge
            const statusBadge = card.find('.status-badge');
            statusBadge.removeClass('status-on_track status-at_risk status-blocked');
            statusBadge.addClass('status-' + goal.status);
            
            const statusIcons = {'on_track': '', 'at_risk': '', 'blocked': ''};
            statusBadge.text(goal.status.replace('_', ' ').toUpperCase());
        }
    });
}

function openGoalDetail(goalId) {
    window.location.href = '<?= base_url('goals/detail/') ?>' + goalId;
}

function showGoalTasks(goalId) {
    $('#goalTasksModal').modal('show');
    
    $.ajax({
        url: '<?= base_url('goals/get_goal_tasks') ?>',
        type: 'POST',
        data: { goal_id: goalId },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                let html = '';
                if (response.tasks.length > 0) {
                    html = '<div class="table-responsive"><table class="table table-hover">';
                    html += '<thead><tr><th>Task</th><th>Assigned To</th><th>Status</th><th>Priority</th></tr></thead><tbody>';
                    
                    response.tasks.forEach(function(task) {
                        const priorityColors = {
                            'high': 'danger',
                            'medium': 'warning', 
                            'low': 'info'
                        };
                        const priorityClass = priorityColors[task.priority] || 'secondary';
                        
                        html += `<tr>
                            <td><strong>${task.task_title}</strong><br><small class="text-muted">${task.unique_id}</small></td>
                            <td>${task.assigned_to_name || 'Unassigned'}</td>
                            <td><span class="badge badge-${task.task_status === 'completed' ? 'success' : 'warning'}">${task.task_status}</span></td>
                            <td><span class="badge badge-${priorityClass}">${task.priority}</span></td>
                        </tr>`;
                    });
                    html += '</tbody></table></div>';
                } else {
                    html = '<div class="text-center py-4"><i class="fas fa-tasks fa-3x text-muted mb-3"></i><h5 class="text-muted">No Open Tasks</h5></div>';
                }
                $('#goal-tasks-content').html(html);
            }
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

$('#stageUpdateForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '<?= base_url('goals/update_stage') ?>',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                $('#stageUpdateModal').modal('hide');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        }
    });
});
</script>