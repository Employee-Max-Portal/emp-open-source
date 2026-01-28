<!---Page view styles---->
<style>
.issue-tracker {
    background: #f8fafc;
    min-height: 100vh;
    padding: 20px;
}

.tracker-header {
    background: white;
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 15px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border: 1px solid #e5e7eb;
}

.tracker-title {
    color: #1f2937;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.header-actions {
    align-items: center;
    gap: 10px;
}

#dataInfo {
    white-space: nowrap;
}

.loading-state {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.spinner {
    margin: 0 auto 20px;
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<!---Accordian styles---->
<style>
	.accordion-card {
		border: 1px solid #dee2e6;
		border-radius: 12px;
		margin-bottom: 8px;
		background: #fff;
		box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
		transition: all 0.3s ease;
		overflow: hidden;
	}
	
	.accordion-card:hover {
		box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
		transform: translateY(-2px);
	}
	
	.accordion-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 8px 15px;
		cursor: pointer;
		font-weight: 600;
		font-size: 1.1rem;
		color: #000;
		background: #f7f7f8;
		user-select: none;
		position: relative;
		transition: all 0.3s ease;
	}
	
	.accordion-header:hover {
		opacity: 0.9;
	}
	
	.accordion-header::after {
		content: '\f078';
		font-family: 'Font Awesome 6 Free';
		font-weight: 900;
		font-size: 0.9rem;
		transition: transform 0.3s ease;
	}
	
	.accordion-header.active::after {
		transform: rotate(180deg);
	}
	
	.status-info {
		display: flex;
		align-items: center;
		gap: 12px;
		font-size: 1.3rem;
	}
	
	.status-icon {
		width: 30px;
		height: 30px;
		border-radius: 50%;
		background: rgba(255, 255, 255, 0.2);
		display: flex;
		align-items: center;
		justify-content: center;
		font-weight: bold;
		font-size: 1.1rem;
	}
	
	.item-count {
		background: #e8e8e9;
		color: black;
		padding: 6px 12px;
		border-radius: 20px;
		font-size: 1.5rem;
		font-weight: 500;
		text-align: center;
	}
	
	.accordion-body {
		max-height: 0;
		overflow: hidden;
		transition: max-height 0.4s ease-out, padding 0.4s ease-out;
		background-color: #ffffff;
	}
	
	.accordion-body.active {
		max-height: 100%;
		padding: 24px;
	}
	
	.task-item {
		display: flex;
		align-items: center;
		padding: 5px 10px;
		border-bottom: 1px solid #f0f0f1;
		background: #f7f7f8;
		transition: all 0.2s ease;
		border-radius: 8px;
		gap: 10px;
		margin-bottom: 8px;
	}
	
	.task-item:last-child {
		border-bottom: none;
	}
	
	.task-item:hover {
		background-color: #e8e8e9;
		margin: 0 -12px;
		padding-left: 12px;
		padding-right: 12px;
		border-radius: 8px;
	}
	
	.task-id {
		width: 80px;
		flex-shrink: 0;
		font-weight: 600;
		color: #1976d2;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		font-size: 1.5rem;
	}
	
	.task-title {
		width: 200px;
		flex-shrink: 0;
		font-weight: 600;
		color: #2d3748;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		font-size: 1.5rem;
	}
	
	.task-category {
		width: 120px;
		flex-shrink: 0;
		padding: 5px 10px;
		border: 1px solid #cccccc;
		background: #f2f2f4;
		color: #000;
		border-radius: 20px;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		font-size: 1.5rem;
		text-align: center;
	}
	
	.task-duration {
		width: 60px;
		flex-shrink: 0;
		padding: 5px 10px;
		border: 1px solid #cccccc;
		background: #f2f2f4;
		color: #000;
		border-radius: 20px;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		font-size: 1.5rem;
		text-align: center;
	}
	
	.user-avatar-container {
		width: 40px;
		flex-shrink: 0;
		margin-left: auto;
	}
	
	.task-actions {
		display: flex;
		gap: 8px;
		margin-left: 16px;
	}
	
	.btn-approve {
		background: #10b981;
		color: white;
		border: none;
		padding: 6px 12px;
		border-radius: 6px;
		font-size: 0.8rem;
		cursor: pointer;
		transition: background 0.2s;
	}
	
	.btn-approve:hover {
		background: #059669;
	}
	
	.btn-decline {
		background: #ef4444;
		color: white;
		border: none;
		padding: 6px 12px;
		border-radius: 6px;
		font-size: 0.8rem;
		cursor: pointer;
		transition: background 0.2s;
	}
	
	.btn-delete {
		background: #dc3545;
		color: white;
		border: none;
		padding: 6px 12px;
		border-radius: 6px;
		font-size: 0.8rem;
		cursor: pointer;
		transition: background 0.2s;
	}
	
	.btn-delete:hover {
		background: #c82333;
	}
	
	.no-data {
		text-align: center;
		padding: 60px 20px;
		color: #a0aec0;
		font-size: 1.1rem;
	}
	
	.no-data i {
		font-size: 3rem;
		margin-bottom: 16px;
		opacity: 0.3;
	}
</style>

<!-- Modal styles -->
<style>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1050;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow-y: auto;
}

.modal-content {
    background: white;
    width: 90%;
    max-width: 1200px;
    max-height: 90vh;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    background: #f8f9fa;
}

.modal-title-section {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.modal-title {
    font-size: 18px;
    font-weight: 600;
    margin: 0;
    color: #333;
}

.modal-body {
    display: flex;
    flex: 1;
    overflow: hidden;
}

.modal-main {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
}

.modal-sidebar {
    width: 300px;
    background: #f8f9fa;
    padding: 20px;
    border-left: 1px solid #eee;
    overflow-y: auto;
}

/* Editable styles */
.editable-title {
  min-height: 44px;
  padding: 8px 12px;
  border: 1px solid #e9ecef;
  border-radius: 4px;
  font-size: 1.5rem;
  font-weight: 600;
  outline: none;
  position: relative;
  transition: all 0.2s ease;
  background: #f8f9fa;
  cursor: text;
}

.editable-title::after {
  content: "✏️ Click to edit";
  position: absolute;
  right: 8px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 12px;
  color: #6c757d;
  background: rgba(255,255,255,0.9);
  padding: 2px 6px;
  border-radius: 3px;
  font-weight: normal;
}

.editable-title:focus {
  border-color: #4a89dc;
  background: #fff;
  box-shadow: 0 0 0 2px rgba(74, 137, 220, 0.2);
}

.editable-title:focus::after {
  display: none;
}

.editable-description {
  min-height: 100px;
  padding: 12px;
  border: 1px solid #e9ecef;
  border-radius: 4px;
  outline: none;
  position: relative;
  transition: all 0.2s ease;
  background: #f8f9fa;
  cursor: text;
}

.editable-description::after {
  content: "✏️ Click to edit description";
  position: absolute;
  right: 8px;
  top: 8px;
  font-size: 12px;
  color: #6c757d;
  background: rgba(255,255,255,0.9);
  padding: 2px 6px;
  border-radius: 3px;
  font-weight: normal;
}

.editable-description:focus {
  border-color: #ddd;
  background: #fff;
  box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
}

.editable-description:focus::after {
  display: none;
}

[contenteditable][data-placeholder]:empty:before {
  content: attr(data-placeholder);
  color: #888;
  font-style: italic;
}

.component-value, .milestone-value, .task-type-value {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #333;
    font-size: 14px;
}

.sidebar-item {
    margin-bottom: 20px;
}

.sidebar-item label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #666;
    margin-bottom: 5px;
    text-transform: uppercase;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 8px;
}

.user-avatar {
    width: 20px;
    height: 20px;
    border-radius: 50%;
}

.task-id-badge {
    background: #e3f2fd;
    color: #1976d2;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
    margin-bottom: 10px;
    display: inline-block;
}

.close-btn {
    position: absolute;
    top: 15px;
    right: 15px;
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.close-btn:hover {
    color: #333;
    background: rgba(0,0,0,0.1);
}

.decline-reason-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1060;
    display: flex;
    align-items: center;
    justify-content: center;
}

.decline-reason-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    position: relative;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.decline-reason-content h4 {
    margin-bottom: 15px;
    color: #374151;
}

.decline-reason-content textarea {
    width: 100%;
    min-height: 100px;
    padding: 10px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    resize: vertical;
    margin-bottom: 15px;
}

.decline-reason-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.btn {
    padding: 8px 16px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    background: #f9fafb;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn:hover {
    background: #f3f4f6;
}

.btn-secondary {
    background: #6b7280;
    color: white;
    border-color: #6b7280;
}

.btn-secondary:hover {
    background: #4b5563;
    border-color: #4b5563;
}

@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        max-height: 95vh;
    }
    
    .modal-body {
        flex-direction: column;
    }
    
    .modal-sidebar {
        width: 100%;
        border-left: none;
        border-top: 1px solid #eee;
    }
}
</style>

<div class="row" style="height: calc(100vh);">
    <div class="col-md-3">
        <?php include 'sidebar.php'; ?>
    </div>
    <div class="col-md-9" style="height: 100%; overflow-y: auto;">
        <div class="issue-tracker">
            <!-- Header -->
            <div class="tracker-header">
                <div class="row d-flex justify-content-between align-items-center">
                    <div class="col-md-8">
                        <h3 class="tracker-title">
                            <i class="fas fa-clock"></i>
                            Pending Approval
                        </h3>
                    </div>
                    <div class="col-md-4">
                        <div class="header-actions" style="text-align:end">
                            <span id="dataInfo" class="text-muted" style="font-size: 12px;">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div id="loadingState" class="loading-state">
                <div class="spinner"></div>
                <h4>Loading Pending Tasks...</h4>
                <p>Please wait while we fetch your pending approvals</p>
            </div>

            <!-- Tasks Container -->
            <div id="tasksContainer" class="panel-body" style="display: none;">
                <!-- Tasks will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Task Details Modal -->
<div id="taskDetailsModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title-section">
                <button class="close-btn" onclick="closeTaskModal()">&times;</button>
                <span class="task-id-badge" id="modalTaskId">Loading...</span>
            </div>
            <div id="parentTaskInfo" style="display: none; margin-bottom: 10px; padding: 8px 12px; background: #e3f2fd; border-radius: 4px; font-size: 0.9em; color: #1976d2; cursor: pointer;" onclick="openParentTask()">
                <i class="fas fa-arrow-up" style="margin-right: 5px;"></i>
                <strong>Parent Task:</strong> <span id="parentTaskDetails"></span>
            </div>
            <div class="editable-title" id="modalTaskTitle" contenteditable="true" data-placeholder="Task Title">Loading...</div>
        </div>

        <div class="modal-body">
            <div class="modal-main">
                <div class="description-section">
                    <div id="modalTaskDescription" class="editable-description" contenteditable="true" data-placeholder="Add description" style="line-height: 1.6; color: #666;">Loading...</div>
                </div>
            </div>

            <div class="modal-sidebar">
                <!-- Status -->
                <div class="sidebar-item">
                    <label><i class="fas fa-columns"></i> Status</label>
                    <select id="modalStatusSelect" class="form-control" onchange="updateTaskField('task_status', this.value)">
                        <?php foreach($statuses as $value => $label): ?>
                            <?php if($value !== 'completed'): ?>
                                <option value="<?= $value ?>"><?= $label ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Priority -->
                <div class="sidebar-item">
                    <label><i class="fas fa-exclamation-circle"></i> Priority</label>
                    <select id="modalPrioritySelect" class="form-control" onchange="updateTaskField('priority_level', this.value)">
                        <?php foreach($priorities as $value => $label): ?>
                            <option value="<?= $value ?>"><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Category -->
                <div class="sidebar-item">
                    <label><i class="fas fa-list-alt"></i> Category</label>
                    <select id="modalCategorySelect" class="form-control" onchange="updateTaskField('category', this.value)">
                        <option value="Milestone">Milestone</option>
                        <option value="Incident">Incident</option>
                        <option value="Customer Query">Customer Query</option>
                        <option value="Explore">Explore</option>
                        <option value="EMP Request">EMP Request</option>
                    </select>
                </div>
                
                <!-- Created By -->
                <div class="sidebar-item">
                    <label><i class="fas fa-user-plus"></i> Created By</label>
                    <div class="user-info">
                        <img id="modalCreatedByAvatar" src="" alt="User Avatar" class="user-avatar" style="display:none;">
                        <span id="modalCreatedBy">Loading...</span>
                    </div>
                </div>
                
                <!-- Assignee -->
                <div class="sidebar-item">
                    <label><i class="fas fa-user-check"></i> Assignee</label>
                    <div class="user-info">
                        <span id="modalAssignee">Loading...</span>
                    </div>
                </div>
                
                <!-- Coordinator -->
                <div class="sidebar-item">
                    <label><i class="fas fa-user-cog"></i> Coordinator</label>
                    <div class="user-info">
                        <span id="modalCoordinator">-</span>
                    </div>
                </div>
                
                <!-- Component -->
                <div class="sidebar-item">
                    <label><i class="fas fa-puzzle-piece"></i> Initiative</label>
                    <div class="component-value">
                        <span id="modalComponent">-</span>
                    </div>
                </div>
                
                <!-- Milestone -->
                <div class="sidebar-item">
                    <label><i class="fas fa-flag-checkered"></i> Milestone</label>
                    <div class="milestone-value">
                        <span id="modalMilestone">-</span>
                    </div>
                </div>
                
                <!-- Task Type -->
                <div class="sidebar-item">
                    <label><i class="fa-solid fa-database"></i> Task Type</label>
                    <div class="task-type-value">
                        <span id="modalTaskType">-</span>
                    </div>
                </div>
                
                <!-- Due Date -->
                <div class="sidebar-item">
                    <label><i class="fas fa-calendar-alt"></i> Due Date</label>
                    <input type="date" id="modalDueDateInput" class="form-control" onchange="updateTaskField('estimated_end_time', this.value)">
                </div>
                
                <!-- Time Tracking -->
                <div class="sidebar-item">
                    <label><i class="fas fa-clock"></i> Estimation</label>
                    <input type="number" step="0.1" id="modalEstimationInput" class="form-control" placeholder="Hours" onchange="updateTaskField('estimation_time', this.value)">
                </div>
                <div class="sidebar-item">
                    <label><i class="fas fa-hourglass-half"></i> Spent Time</label>
                    <div id="modalSpentTime">-</div>
                </div>
                <div class="sidebar-item">
                    <label><i class="fas fa-hourglass-end"></i> Remaining Time</label>
                    <div id="modalRemainingTime">-</div>
                </div>
                
                <!-- Decline Reason Section -->
                <div id="declineReasonSection" class="sidebar-item" style="display: none; margin-top: 20px; padding: 15px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px;">
                    <label style="color: #dc2626; margin-bottom: 10px;"><i class="fas fa-exclamation-triangle"></i> Decline Reason</label>
                    <div style="background: white; padding: 10px; border-radius: 4px; margin-bottom: 8px; font-size: 14px; line-height: 1.5;">
                        <div id="declineReasonText" style="color: #374151;"></div>
                    </div>
                    <div style="font-size: 12px; color: #6b7280;">
                        <strong>Declined by:</strong> <span id="declinedByName">-</span><br>
                        <strong>Declined at:</strong> <span id="declinedAtTime">-</span>
                    </div>
                </div>
                
                <!-- Extension Request Section -->
                <div id="extensionRequestSection" class="sidebar-item" style="display: none; margin-top: 20px; padding: 15px; background: #fef3c7; border: 1px solid #fbbf24; border-radius: 6px;">
                    <label style="color: #d97706; margin-bottom: 10px;"><i class="fas fa-clock"></i> Extension Request</label>
                    <div style="background: white; padding: 10px; border-radius: 4px; margin-bottom: 8px; font-size: 14px; line-height: 1.5;">
                        <div id="extensionReasonText" style="color: #374151;"></div>
                    </div>
                    <div style="font-size: 12px; color: #6b7280;">
                        <strong>Requested by:</strong> <span id="extensionRequestedBy">-</span><br>
                        <strong>Requested at:</strong> <span id="extensionRequestedAt">-</span><br>
                        <div id="extensionNewDueDate" style="display: none;"><strong>New Due Date:</strong> <span id="extensionNewDueDateValue">-</span><br></div>
                        <div id="extensionNewEstimation" style="display: none;"><strong>New Estimation:</strong> <span id="extensionNewEstimationValue">-</span>h</div>
                    </div>
                </div>
                
                <!-- Approval Details Section -->
                <div id="approvalDetailsSection" class="sidebar-item" style="display: none; margin-top: 20px; padding: 15px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px;">
                    <label style="color: #16a34a; margin-bottom: 10px;"><i class="fas fa-check-circle"></i> Approval Details</label>
                    <div style="font-size: 12px; color: #374151;">
                        <strong>Approved by:</strong> <span id="approvedByName">-</span><br>
                        <strong>Approved at:</strong> <span id="approvedAtTime">-</span>
                    </div>
                </div>
                
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                    <button class="btn-approve" onclick="approveTask()" style="width: 100%; margin-bottom: 10px; padding: 12px; font-size: 14px;">
                        <i class="fas fa-check"></i> Approve Task
                    </button>
                    <button class="btn-decline" onclick="showDeclineModal()" style="width: 100%; padding: 12px; font-size: 14px;">
                        <i class="fas fa-times"></i> Decline Task
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Extension Request Modal -->
<div id="extensionRequestModal" class="decline-reason-modal" style="display: none;">
    <div class="decline-reason-content">
        <h4>Request Extension <span style="color: #f59e0b;">*</span></h4>
        <p style="margin-bottom: 10px; color: #6b7280; font-size: 14px;">Please provide details for your extension request:</p>
        
        <label style="font-size: 12px; font-weight: 600; color: #666; margin-bottom: 5px; display: block;">Reason for Extension:</label>
        <textarea id="extensionReasonTextarea" name="extension_reason" placeholder="Enter your reason for requesting extension..." required style="border: 2px solid #d1d5db; margin-bottom: 15px;"></textarea>
        
        <div style="display: flex; gap: 15px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label style="font-size: 12px; font-weight: 600; color: #666; margin-bottom: 5px; display: block;">New Due Date (Optional):</label>
                <input type="date" id="newDueDateInput" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;">
            </div>
            <div style="flex: 1;">
                <label style="font-size: 12px; font-weight: 600; color: #666; margin-bottom: 5px; display: block;">New Estimation (Optional):</label>
                <input type="number" id="newEstimationInput" placeholder="Hours" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;">
            </div>
        </div>
        
        <div class="decline-reason-actions">
            <button class="btn btn-secondary" onclick="closeExtensionModal()">Cancel</button>
            <button class="btn-decline" onclick="requestExtension()" style="background: #f59e0b; border-color: #f59e0b;">
                <i class="fas fa-clock"></i> Request Extension
            </button>
        </div>
    </div>
</div>

<script>
let currentTaskId = null;

document.addEventListener('DOMContentLoaded', function() {
    loadPendingTasks();
});

function loadPendingTasks() {
    document.getElementById('loadingState').style.display = 'block';
    document.getElementById('tasksContainer').style.display = 'none';
    
    fetch('<?= base_url('tracker/get_pending_tasks') ?>', {
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            renderTasks(data.tasks);
            updateTaskCount(data.tasks.length);
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('tasksContainer').style.display = 'block';
        } else {
            showError(data.message || 'Failed to load tasks');
        }
    })
    .catch(error => {
        console.error('Error loading tasks:', error);
        showError('Failed to load pending tasks. Please refresh the page.');
    });
}

function renderTasks(tasks) {
    const container = document.getElementById('tasksContainer');
    container.innerHTML = '';
    
    if (!tasks || tasks.length === 0) {
        container.innerHTML = `
            <div class="no-data">
                <i class="fas fa-check-circle"></i>
                <div>No Pending Approvals</div>
                <p>You have no tasks waiting for your approval at the moment.</p>
            </div>
        `;
        return;
    }
    
    // Separate pending and declined tasks
    const pendingTasks = tasks.filter(task => task.approval_status === 'pending');
    const declinedTasks = tasks.filter(task => task.approval_status === 'declined');
    
    // Create accordion card for pending tasks
    if (pendingTasks.length > 0) {
        const pendingCard = document.createElement('div');
        pendingCard.className = 'accordion-card';
        pendingCard.innerHTML = `
            <div class="accordion-header" onclick="toggleAccordion('pending')">
                <div class="col-md-8 status-info">
                    <div class="status-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #f59e0bdd 100%);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <span>Pending Approval</span>
                </div>
                <div class="item-count">${pendingTasks.length} items</div>
            </div>
            <div class="accordion-body active" id="accordion-body-pending">
                ${renderTaskItems(pendingTasks)}
            </div>
        `;
        container.appendChild(pendingCard);
    }
    
    // Create accordion card for declined tasks
    if (declinedTasks.length > 0) {
        const declinedCard = document.createElement('div');
        declinedCard.className = 'accordion-card';
        declinedCard.innerHTML = `
            <div class="accordion-header" onclick="toggleAccordion('declined')">
                <div class="col-md-8 status-info">
                    <div class="status-icon" style="background: linear-gradient(135deg, #ef4444 0%, #ef4444dd 100%);">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <span>Declined</span>
                </div>
                <div class="item-count">${declinedTasks.length} items</div>
            </div>
            <div class="accordion-body" id="accordion-body-declined">
                ${renderTaskItems(declinedTasks, true)}
            </div>
        `;
        container.appendChild(declinedCard);
    }
}

function renderTaskItems(tasks, isDeclined = false) {
    const currentUserId = <?= get_loggedin_user_id() ?>;
    
    return tasks.map(task => {
        // Only show action buttons if current user is the assigned person
        let actionButtons = '';
        if (task.assigned_to == currentUserId) {
            actionButtons = isDeclined ? `
                <div class="task-actions" onclick="event.stopPropagation()" style="margin-left: 16px;">
                    <button class="btn-approve" onclick="acceptDeclinedTask(${task.id})" title="Accept Task">
                        <i class="fas fa-check"></i>
                    </button>
                </div>
            ` : `
                <div class="task-actions" onclick="event.stopPropagation()" style="margin-left: 16px;">
                    <button class="btn-approve" onclick="approveTaskDirect(${task.id})" title="Approve">
                        <i class="fas fa-check"></i>
                    </button>
                    <button class="btn-decline" onclick="extensionTaskDirect(${task.id})" title="Request Extension">
                        <i class="fas fa-clock"></i>
                    </button>
                </div>
            `;
        }
        
        // Add delete button only for task creator
        if (task.created_by == currentUserId) {
            const deleteButton = `
                <button class="btn-delete" onclick="deleteTask(${task.id})" title="Delete Task">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            
            if (actionButtons) {
                // Insert delete button into existing actions
                actionButtons = actionButtons.replace('</div>', deleteButton + '</div>');
            } else {
                // Create new action div with just delete button
                actionButtons = `
                    <div class="task-actions" onclick="event.stopPropagation()" style="margin-left: 16px;">
                        ${deleteButton}
                    </div>
                `;
            }
        }
        
        return `
            <div class="task-item" onclick="openTaskDetails(${task.id})" style="cursor: pointer;">
                <div class="col-md-10" style="display: flex; align-items: center;">
                    <div class="task-id" style="font-weight: 700; font-size: 1.4rem; font-style: italic; min-width: 80px; text-shadow: 0 1px 2px rgba(0,0,0,0.1);">${task.unique_id}</div>
                    <div class="task-title" style="font-weight: 700; font-size: 1.4rem; flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-shadow: 0 1px 2px rgba(0,0,0,0.1);">${task.task_title}</div>
                </div>
                <div class="col-md-2" style="display: flex; justify-content: flex-end; align-items: center; gap: 8px;">
                    <div class="task-category" title="Category">
                        ${task.category || 'General'}
                    </div>
                    <div class="task-duration" title="Duration">
                        ${task.estimation_time || '0'}h
                    </div>
                    <div class="user-avatar-container" style="width: 40px; flex-shrink: 0;">
                        ${task.assigned_to_photo ? 
                            `<img src="${task.assigned_to_photo}" width="40" height="40" class="rounded" alt="${task.assigned_to_name}" title="${task.assigned_to_name}" />` :
                            `<div class="user-avatar" style="background: #e2e8f0; display: flex; align-items: center; justify-content: center; color: #a0aec0; width: 40px; height: 40px; border-radius: 50%;"><i class="fas fa-user"></i></div>`
                        }
                    </div>
                </div>
                ${actionButtons}
            </div>
        `;
    }).join('');
}

function toggleAccordion(id) {
    const header = document.querySelector(`[onclick="toggleAccordion('${id}')"]`);
    const body = document.getElementById('accordion-body-' + id);
    
    header.classList.toggle('active');
    body.classList.toggle('active');
}

function openTaskDetails(taskId) {
    currentTaskId = taskId;
    const modal = document.getElementById('taskDetailsModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    
    // Reset modal content
    const elements = {
        'modalTaskId': 'Loading...',
        'modalTaskTitle': 'Loading...',
        'modalTaskDescription': 'Loading...',
        'modalStatus': '-',
        'modalPriority': '-',
        'modalCategory': '-',
        'modalCreatedBy': '-',
        'modalAssignee': '-',
        'modalCoordinator': '-',
        'modalComponent': '-',
        'modalMilestone': '-',
        'modalTaskType': '-',
        'modalDueDate': '-',
        'modalEstimation': '-',
        'modalSpentTime': '-',
        'modalRemainingTime': '-'
    };
    
    Object.entries(elements).forEach(([id, text]) => {
        const element = document.getElementById(id);
        if (element) element.textContent = text;
    });
    
    // Hide avatars
    const avatars = ['modalCreatedByAvatar'];
    avatars.forEach(id => {
        const element = document.getElementById(id);
        if (element) element.style.display = 'none';
    });
    
    // Load task details
    fetch(`<?= base_url('tracker/get_pending_task_details/') ?>${taskId}`, {
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        if (data.error) throw new Error(data.error);
        populateModal(data);
    })
    .catch(err => {
        console.error('Error:', err);
        showModalError(err.message || 'Failed to load task details');
    });
}

function populateModal(task) {
    document.getElementById('modalTaskId').textContent = task.unique_id;
    
    // Set editable title and description
    const titleElement = document.getElementById('modalTaskTitle');
    titleElement.textContent = task.task_title || '';
    titleElement.dataset.originalValue = task.task_title || '';
    
    const descElement = document.getElementById('modalTaskDescription');
    descElement.innerHTML = task.task_description || '';
    descElement.dataset.originalValue = task.task_description || '';
    
    // Handle parent task display
    const parentInfo = document.getElementById('parentTaskInfo');
    if (task.parent_issue_unique_id && task.parent_task_title) {
        document.getElementById('parentTaskDetails').textContent = task.parent_issue_unique_id + ' - ' + task.parent_task_title;
        parentInfo.style.display = 'block';
        parentInfo.dataset.parentId = task.parent_issue_id;
    } else {
        parentInfo.style.display = 'none';
    }
    
    // Set select values
    document.getElementById('modalStatusSelect').value = task.task_status || 'todo';
    document.getElementById('modalPrioritySelect').value = task.priority_level || 'medium';
    document.getElementById('modalCategorySelect').value = task.category || 'Milestone';
    
    // Users
    document.getElementById('modalCreatedBy').textContent = task.created_by_name || 'Unknown';
    document.getElementById('modalAssignee').textContent = task.assigned_to_name || 'Unassigned';
    document.getElementById('modalCoordinator').textContent = task.coordinator_name || 'None';
    
    // Set user avatars
    const createdByAvatar = document.getElementById('modalCreatedByAvatar');
    
    if (createdByAvatar) {
        if (task.created_by_photo) {
            createdByAvatar.src = task.created_by_photo;
            createdByAvatar.style.display = 'inline-block';
        } else {
            createdByAvatar.style.display = 'none';
        }
    }
    
    // Project details
    document.getElementById('modalComponent').textContent = task.component_name || 'None';
    document.getElementById('modalMilestone').textContent = task.milestone_name || 'None';
    document.getElementById('modalTaskType').textContent = task.task_type_name || 'None';
    
    // Dates and time
    document.getElementById('modalDueDateInput').value = task.estimated_end_time ? task.estimated_end_time.split(' ')[0] : '';
    document.getElementById('modalEstimationInput').value = task.estimation_time || '';
    document.getElementById('modalSpentTime').textContent = (task.spent_time || '0') + 'h';
    document.getElementById('modalRemainingTime').textContent = (task.remaining_time || '0') + 'h';
    
    // Handle decline reason display
    const declineReasonSection = document.getElementById('declineReasonSection');
    if (task.approval_status === 'declined' && task.decline_reason) {
        document.getElementById('declineReasonText').textContent = task.decline_reason;
        if (task.declined_by_name) {
            document.getElementById('declinedByName').textContent = task.declined_by_name;
        }
        if (task.declined_at) {
            document.getElementById('declinedAtTime').textContent = formatDate(task.declined_at);
        }
        declineReasonSection.style.display = 'block';
    } else {
        declineReasonSection.style.display = 'none';
    }
    
    // Handle approval details display
    const approvalDetailsSection = document.getElementById('approvalDetailsSection');
    if (task.approval_status === 'approved' && task.approved_at) {
        if (task.approved_by_name) {
            document.getElementById('approvedByName').textContent = task.approved_by_name;
        }
        document.getElementById('approvedAtTime').textContent = formatDate(task.approved_at);
        approvalDetailsSection.style.display = 'block';
    } else {
        approvalDetailsSection.style.display = 'none';
    }
    
    // Handle extension request display
    const extensionRequestSection = document.getElementById('extensionRequestSection');
    if (task.extension_reason) {
        document.getElementById('extensionReasonText').textContent = task.extension_reason;
        if (task.extension_requested_by_name) {
            document.getElementById('extensionRequestedBy').textContent = task.extension_requested_by_name;
        }
        if (task.extension_requested_at) {
            document.getElementById('extensionRequestedAt').textContent = formatDate(task.extension_requested_at);
        }
        
        // Show optional fields if they exist
        const newDueDateDiv = document.getElementById('extensionNewDueDate');
        const newEstimationDiv = document.getElementById('extensionNewEstimation');
        
        if (task.extension_new_due_date) {
            document.getElementById('extensionNewDueDateValue').textContent = task.extension_new_due_date;
            newDueDateDiv.style.display = 'block';
        } else {
            newDueDateDiv.style.display = 'none';
        }
        
        if (task.extension_new_estimation) {
            document.getElementById('extensionNewEstimationValue').textContent = task.extension_new_estimation;
            newEstimationDiv.style.display = 'block';
        } else {
            newEstimationDiv.style.display = 'none';
        }
        
        extensionRequestSection.style.display = 'block';
    } else {
        extensionRequestSection.style.display = 'none';
    }
    
    // Show/hide action buttons based on approval status and user permissions
    const currentUserId = <?= get_loggedin_user_id() ?>;
    const actionButtons = document.querySelector('.modal-sidebar > div:last-child');
    
    if (task.approval_status === 'declined') {
        // Only show accept button if current user is the assigned person
        if (task.assigned_to == currentUserId) {
            actionButtons.innerHTML = `
                <button class="btn-approve" onclick="acceptDeclinedTask()" style="width: 100%; padding: 12px; font-size: 14px;">
                    <i class="fas fa-check"></i> Accept Task
                </button>
            `;
            actionButtons.style.display = 'block';
        } else {
            actionButtons.style.display = 'none';
        }
    } else if (task.approval_status === 'approved') {
        // Hide action buttons for approved tasks
        actionButtons.style.display = 'none';
    } else {
        // Only show approve/extension buttons if current user is the assigned person
        if (task.assigned_to == currentUserId) {
            actionButtons.innerHTML = `
                <button class="btn-approve" onclick="approveTask()" style="width: 100%; margin-bottom: 10px; padding: 12px; font-size: 14px;">
                    <i class="fas fa-check"></i> Approve Task
                </button>
                <button class="btn-decline" onclick="showExtensionModal()" style="width: 100%; padding: 12px; font-size: 14px;">
                    <i class="fas fa-clock"></i> Request Extension
                </button>
            `;
            actionButtons.style.display = 'block';
        } else {
            actionButtons.style.display = 'none';
        }
    }
}

function showModalError(message) {
    document.getElementById('modalTaskTitle').textContent = 'Error';
    document.getElementById('modalTaskDescription').textContent = message;
}

function openParentTask() {
    const parentInfo = document.getElementById('parentTaskInfo');
    const parentId = parentInfo.dataset.parentId;
    if (parentId) {
        // Try to fetch parent task details from general endpoint
        currentTaskId = parentId;
        const modal = document.getElementById('taskDetailsModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        // Reset modal content
        const elements = {
            'modalTaskId': 'Loading...',
            'modalTaskTitle': 'Loading...',
            'modalTaskDescription': 'Loading...',
            'modalStatus': '-',
            'modalPriority': '-',
            'modalCategory': '-',
            'modalCreatedBy': '-',
            'modalAssignee': '-',
            'modalComponent': '-',
            'modalMilestone': '-',
            'modalTaskType': '-',
            'modalDueDate': '-',
            'modalEstimation': '-',
            'modalSpentTime': '-',
            'modalRemainingTime': '-'
        };
        
        Object.entries(elements).forEach(([id, text]) => {
            const element = document.getElementById(id);
            if (element) element.textContent = text;
        });
        
        // Hide avatars and parent info
        const avatars = ['modalCreatedByAvatar', 'modalAssigneeAvatar', 'modalCoordinatorAvatar'];
        avatars.forEach(id => {
            const element = document.getElementById(id);
            if (element) element.style.display = 'none';
        });
        document.getElementById('parentTaskInfo').style.display = 'none';
        
        // Try to load parent task details using general task details endpoint
        fetch(`<?= base_url('tracker/get_task_details/') ?>${parentId}`, {
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
        .then(response => {
            if (!response.ok) throw new Error('Parent task not accessible');
            return response.json();
        })
        .then(data => {
            if (data.error) throw new Error(data.error);
            populateModalReadOnly(data);
        })
        .catch(err => {
            console.error('Error:', err);
            closeTaskModal();
            // Fallback to my_issues page
            window.location.href = '<?= base_url('tracker/my_issues') ?>';
        });
    }
}

function populateModalReadOnly(task) {
    // Same as populateModal but hide approve/decline buttons and disable editing
    populateModal(task);
    
    // Disable editing for parent task view
    document.getElementById('modalTaskTitle').contentEditable = false;
    document.getElementById('modalTaskDescription').contentEditable = false;
    
    // Disable all selects
    const selects = document.querySelectorAll('.modal-sidebar select, .modal-sidebar input');
    selects.forEach(select => select.disabled = true);
    
    // Hide approve/decline buttons for parent task view
    const actionButtons = document.querySelector('.modal-sidebar > div:last-child');
    if (actionButtons) {
        actionButtons.style.display = 'none';
    }
}

function closeTaskModal() {
    const modal = document.getElementById('taskDetailsModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    // Re-enable editing when closing modal
    document.getElementById('modalTaskTitle').contentEditable = true;
    document.getElementById('modalTaskDescription').contentEditable = true;
    
    // Re-enable all selects
    const selects = document.querySelectorAll('.modal-sidebar select, .modal-sidebar input');
    selects.forEach(select => select.disabled = false);
    
    currentTaskId = null;
}

function approveTask() {
    if (!currentTaskId) return;
    approveTaskDirect(currentTaskId);
}

function approveTaskDirect(taskId) {
    if (!confirm('Are you sure you want to approve this task?')) return;
    
    const formData = new FormData();
    formData.append('task_id', taskId);
    formData.append('<?= $this->security->get_csrf_token_name() ?>', '<?= $this->security->get_csrf_hash() ?>');
    
    fetch('<?= base_url('tracker/approve_task') ?>', {
        method: 'POST',
        body: formData,
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Task approved successfully', 'success');
            closeTaskModal();
            loadPendingTasks(); // Refresh the list
        } else {
            showToast(data.message || 'Failed to approve task', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to approve task', 'error');
    });
}

function showExtensionModal() {
    const modal = document.getElementById('extensionRequestModal');
    const textarea = document.getElementById('extensionReasonTextarea');
    const dueDateInput = document.getElementById('newDueDateInput');
    const estimationInput = document.getElementById('newEstimationInput');
    
    if (modal) {
        modal.style.display = 'flex';
    }
    if (textarea) {
        textarea.value = '';
        textarea.style.borderColor = '#d1d5db';
        setTimeout(() => textarea.focus(), 100);
    }
    if (dueDateInput) {
        dueDateInput.value = '';
    }
    if (estimationInput) {
        estimationInput.value = '';
    }
}

function closeExtensionModal() {
    const modal = document.getElementById('extensionRequestModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function requestExtension() {
    const textarea = document.getElementById('extensionReasonTextarea');
    const dueDateInput = document.getElementById('newDueDateInput');
    const estimationInput = document.getElementById('newEstimationInput');
    
    if (!textarea) {
        showToast('Extension reason field not found', 'error');
        return;
    }
    
    const reason = textarea.value ? textarea.value.trim() : '';
    const newDueDate = dueDateInput ? dueDateInput.value : '';
    const newEstimation = estimationInput ? estimationInput.value : '';
    
    if (reason.length === 0) {
        showToast('Please provide a reason for extension request', 'error');
        textarea.focus();
        textarea.style.borderColor = '#ef4444';
        return;
    }
    
    if (!currentTaskId) {
        showToast('No task selected', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('task_id', currentTaskId);
    formData.append('extension_reason', reason);
    if (newDueDate) formData.append('new_due_date', newDueDate);
    if (newEstimation) formData.append('new_estimation', newEstimation);
    formData.append('<?= $this->security->get_csrf_token_name() ?>', '<?= $this->security->get_csrf_hash() ?>');
    
    fetch('<?= base_url('tracker/request_extension') ?>', {
        method: 'POST',
        body: formData,
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Extension request sent successfully', 'success');
            closeExtensionModal();
            closeTaskModal();
            loadPendingTasks();
        } else {
            showToast(data.message || 'Failed to send extension request', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to send extension request', 'error');
    });
}

function extensionTaskDirect(taskId) {
    currentTaskId = taskId;
    showExtensionModal();
}

function deleteTask(taskId) {
    if (!confirm('Are you sure you want to delete this task? This action cannot be undone.')) return;
    
    const formData = new FormData();
    formData.append('task_id', taskId);
    formData.append('<?= $this->security->get_csrf_token_name() ?>', '<?= $this->security->get_csrf_hash() ?>');
    
    fetch('<?= base_url('tracker/delete_task') ?>', {
        method: 'POST',
        body: formData,
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Task deleted successfully', 'success');
            loadPendingTasks(); // Refresh the list
        } else {
            showToast(data.message || 'Failed to delete task', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to delete task', 'error');
    });
}

function acceptDeclinedTask(taskId = null) {
    const id = taskId || currentTaskId;
    if (!id) return;
    
    if (!confirm('Are you sure you want to accept this declined task?')) return;
    
    const formData = new FormData();
    formData.append('task_id', id);
    formData.append('<?= $this->security->get_csrf_token_name() ?>', '<?= $this->security->get_csrf_hash() ?>');
    
    fetch('<?= base_url('tracker/accept_declined_task') ?>', {
        method: 'POST',
        body: formData,
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Task accepted successfully', 'success');
            if (taskId) {
                // Called from task list, just refresh
                loadPendingTasks();
            } else {
                // Called from modal, close modal and refresh
                closeTaskModal();
                loadPendingTasks();
            }
        } else {
            showToast(data.message || 'Failed to accept task', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to accept task', 'error');
    });
}

function updateTaskCount(count) {
    const countElement = document.getElementById('dataInfo');
    if (count === 0) {
        countElement.textContent = 'No tasks';
    } else {
        countElement.textContent = `${count} task${count !== 1 ? 's' : ''} total`;
    }
}

function showError(message) {
    document.getElementById('loadingState').innerHTML = `
        <div style="text-align: center; padding: 60px 20px; color: #dc3545;">
            <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 16px;"></i>
            <h4>Error Loading Tasks</h4>
            <p>${message}</p>
            <button onclick="location.reload()" class="btn btn-primary">Retry</button>
        </div>
    `;
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : 'success'} toast-notification`;
    toast.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function formatDate(dateString) {
    if (!dateString) return 'Not set';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Close modals when clicking outside
document.getElementById('taskDetailsModal').addEventListener('click', function(e) {
    if (e.target === this || e.target.classList.contains('modal-overlay')) {
        closeTaskModal();
    }
});

document.getElementById('extensionRequestModal').addEventListener('click', function(e) {
    if (e.target === this || e.target.classList.contains('decline-reason-modal')) {
        closeExtensionModal();
    }
});

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeTaskModal();
        closeExtensionModal();
    }
});

// Editable functionality
document.addEventListener('DOMContentLoaded', function() {
    // Title and description edit handlers
    document.addEventListener('blur', function(e) {
        if (e.target.id === 'modalTaskTitle') {
            updateTaskField('task_title', e.target.textContent);
        } else if (e.target.id === 'modalTaskDescription') {
            updateTaskField('task_description', e.target.innerHTML);
        }
    }, true);
    
    // Setup editable fields
    setupEditableFields();
});

function setupEditableFields() {
    const editables = document.querySelectorAll('[contenteditable="true"]');
    
    editables.forEach(el => {
        el.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.textContent = this.dataset.originalValue;
                this.blur();
            }
        });
        
        el.addEventListener('focus', function() {
            this.dataset.originalValue = this.textContent;
        });
    });
}

function updateTaskField(field, value) {
    if (!currentTaskId) return;
    
    const formData = new FormData();
    formData.append('task_id', currentTaskId);
    formData.append('field', field);
    formData.append('value', typeof value === 'string' ? value.trim() : value);
    formData.append('<?= $this->security->get_csrf_token_name() ?>', '<?= $this->security->get_csrf_hash() ?>');
    
    fetch('<?= base_url('tracker/update_task_field') ?>', {
        method: 'POST',
        body: formData,
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(res => {
        if (res.success) {
            // Update original value to prevent reverting
            if (field === 'task_title') {
                document.getElementById('modalTaskTitle').dataset.originalValue = value;
            } else if (field === 'task_description') {
                document.getElementById('modalTaskDescription').dataset.originalValue = value;
            }
            
            // Refresh list if status changed
            if (field === 'task_status' || field === 'category') {
                loadPendingTasks();
            }
        } else {
            showToast(res.message || 'Update failed', 'error');
            revertFieldValue(field);
        }
    })
    .catch(error => {
        console.error('Network error:', error);
        revertFieldValue(field);
    });
}

function revertFieldValue(field) {
    if (field === 'task_title') {
        const el = document.getElementById('modalTaskTitle');
        el.textContent = el.dataset.originalValue;
    } else if (field === 'task_description') {
        const el = document.getElementById('modalTaskDescription');
        el.innerHTML = el.dataset.originalValue;
    }
}
</script>