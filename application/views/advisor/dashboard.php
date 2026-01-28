<?php $currency_symbol = '৳'; ?>

<!-- Dashboard Header -->
<div class="dashboard-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
				<h4>
                    <i class="fas fa-tachometer-alt"></i> Monitor and manage departmental activities
                </h4>
            </div>
            <div class="col-md-4 text-right">
                <button type="button" class="btn btn-success btn-lg" onclick="mfp_modal('#createTaskModal')">
                    <i class="fas fa-plus-circle"></i> Create New Task
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stats-section">
    <div class="container-fluid">
        <div class="row">
            <?php 
            $total_tasks = array_sum(array_column($department_summary, 'total_tasks'));
            $total_pending = array_sum(array_column($department_summary, 'pending_tasks'));
            $total_completed = array_sum(array_column($department_summary, 'completed_tasks'));
            $completion_rate = $total_tasks > 0 ? round(($total_completed / $total_tasks) * 100) : 0;
            ?>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $total_tasks ?></h3>
                        <p>Total Tasks</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $total_pending ?></h3>
                        <p>Pending Tasks</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $total_completed ?></h3>
                        <p>Completed Tasks</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card stat-card-info">
                    <div class="stat-icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $completion_rate ?>%</h3>
                        <p>Completion Rate</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="filter-section">
    <div class="container-fluid">
        <div class="filter-card">
            <div class="filter-header">
                <h4><i class="fas fa-filter"></i> Filter Tasks</h4>
            </div>
            <form id="taskFilterForm" class="filter-form">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>From Date</label>
                            <input type="date" name="date_from" id="date_from" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>To Date</label>
                            <input type="date" name="date_to" id="date_to" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Department</label>
                            <select name="department_id" id="department_id" class="form-control">
                                <option value="">All Departments</option>
                                <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept->identifier ?>"><?= html_escape($dept->title) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" id="filterTasks" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Main Content Section -->
<div class="main-content">
    <div class="container-fluid">
       <!-- Row 2: Department Task Summary & Department Performance -->
        <div class="row equal-height-row">
            <!-- Department Task Summary -->
            <div class="col-lg-8">
                <div class="content-card h-100">
                    <div class="card-header">
                        <h4><i class="fas fa-building"></i> Department Task Summary</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-modern table-export" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th><?=translate('department')?></th>
                                        <th class="text-center"><?=translate('total_tasks')?></th>
                                        <th class="text-center"><?=translate('pending')?></th>
                                        <th class="text-center"><?=translate('completed')?></th>
                                        <th class="text-center"><?=translate('progress')?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($department_summary as $dept): 
                                        $progress = $dept->total_tasks > 0 ? round(($dept->completed_tasks / $dept->total_tasks) * 100) : 0;
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="dept-info">
                                                <i class="fas fa-folder text-primary"></i>
                                                <strong><?= translate($dept->title) ?></strong>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-primary badge-modern"><?= $dept->total_tasks ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-warning badge-modern"><?= $dept->pending_tasks ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-success badge-modern"><?= $dept->completed_tasks ?></span>
                                        </td>
                                        <td class="text-center">
                                            <div class="progress progress-sm">
                                                <div class="progress-bar bg-success" style="width: <?= $progress ?>%"></div>
                                            </div>
                                            <small class="text-muted"><?= $progress ?>%</small>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Department Performance Chart -->
            <div class="col-lg-4">
                <div class="content-card h-100">
                    <div class="card-header">
                        <h4><i class="fas fa-chart-pie"></i> Department Performance</h4>
                    </div>
                    <div class="card-body text-center">
                        <canvas id="departmentChart" width="350" height="350"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Tasks Section -->
<div class="tasks-section">
    <div class="container-fluid">
        <div class="content-card">
            <div class="card-header">
                <h4><i class="fas fa-list-alt"></i> Recent Tasks</h4>
            </div>
            <div class="card-body">
                <div id="tasksContainer">
                    <?php if (!empty($recent_tasks)): ?>
                    <div class="table-responsive">
                        <table class="table table-modern table-export" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th width="5%">SL</th>
                                    <th width="25%"><?=translate('title')?></th>
                                    <th width="15%"><?=translate('assigned_to')?></th>
                                    <th width="15%"><?=translate('dept')?></th>
                                    <th width="8%" class="text-center"><?=translate('status')?></th>
                                    <th width="8%" class="text-center"><?=translate('due date')?></th>
                                    <th width="8%" class="text-center"><?=translate('review')?></th>
                                    <th width="16%" class="text-center"><?=translate('action')?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $count = 1; foreach ($recent_tasks as $task): 
                                    switch($task->task_status) {
                                        case 'completed':
                                            $status_class = 'success';
                                            break;
                                        case 'in_progress':
                                            $status_class = 'primary';
                                            break;
                                        case 'in_review':
                                            $status_class = 'info';
                                            break;
                                        case 'hold':
                                            $status_class = 'warning';
                                            break;
                                        case 'canceled':
                                            $status_class = 'danger';
                                            break;
                                        default:
                                            $status_class = 'secondary';
                                            break;
                                    }
                                ?>
                                <tr>
                                    <td><span class="row-number"><?= $count++ ?></span></td>
                                    <td>
                                        <div class="task-title">
                                            <strong style="font-size:14px;"><?= html_escape($task->task_title) ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="user-info">
                                            <i class="fas fa-user-circle text-muted"></i>
                                            <?= html_escape($task->assigned_name) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="dept-info">
                                            <i class="fas fa-building text-muted"></i>
                                            <?= html_escape($task->department_name) ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-<?= $status_class ?> badge-modern">
                                            <?= ucfirst(str_replace('_', ' ', $task->task_status)) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="date-display">
                                            <?= $task->estimated_end_time ? date('M d, Y', strtotime($task->estimated_end_time)) : '-' ?>
                                        </span>
                                    </td>
									 <td class="text-center">
										<?php
											if ($task->advisor_review == 0)
												$status = '<span class="label label-warning-custom badge-modern text-xs">' . translate('pending') . '</span>';
											else if ($task->advisor_review  == 1)
												$status = '<span class="label label-success-custom badge-modern text-xs">' . translate('completed') . '</span>';
											echo ($status);
										?>
									</td>
                                    <td class="text-center">
										<button type="button" class="button btn btn-info btn-xs ml-1" onclick="viewTask('<?= $task->id ?>')" title="View Task"><i class="fas fa-eye"></i> View</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-tasks fa-3x text-muted"></i>
                        <h5 class="mt-3">No Recent Tasks</h5>
                        <p class="text-muted">No tasks found for the last 7 days</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>



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
/* Hidden scrollbar styles */
::-webkit-scrollbar {
    width: 0px;
    height: 0px;
    background: transparent;
}
* {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

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
</script>
<!-- Main Content Section -->
<div class="main-content">
    <div class="container-fluid">
        <!-- Row 1: Department Fund Summary & Quick Actions -->
        <div class="row equal-height-row">
            <!-- Department Fund Summary -->
            <div class="col-lg-8">
                <div class="content-card h-100">
                    <div class="card-header">
                        <h4><i class="fas fa-money-bill-wave"></i> Department Fund Summary (<?= date('Y') ?>)</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-modern table-export" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th><?=translate('department')?></th>
                                        <th class="text-center"><?=translate('total_requests')?></th>
                                        <th class="text-center"><?=translate('total_amount')?></th>
                                        <th class="text-center"><?=translate('approved_amount')?></th>
                                        <th class="text-center"><?=translate('approval_rate')?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($fund_summary as $fund): 
                                        $approval_rate = $fund->total_amount > 0 ? round(($fund->approved_amount / $fund->total_amount) * 100) : 0;
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="dept-info">
                                                <i class="fas fa-building text-info"></i>
                                                <strong><?= html_escape($fund->department_name) ?></strong>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info badge-modern"><?= $fund->total_requests ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="amount-display"><?= $currency_symbol . number_format($fund->total_amount) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="amount-display text-success"><?= $currency_symbol . number_format($fund->approved_amount) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <div class="progress progress-sm">
                                                <div class="progress-bar bg-info" style="width: <?= $approval_rate ?>%"></div>
                                            </div>
                                            <small class="text-muted"><?= $approval_rate ?>%</small>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="col-lg-4">
                <div class="content-card h-100">
                    <div class="card-header">
                        <h4><i class="fas fa-bolt"></i> Quick Actions</h4>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions">
                            <a href="<?= base_url('advisor/finance') ?>" class="quick-action-item" target="_blank">
                                <div class="action-icon bg-primary">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="action-content">
                                    <h5>Finance Overview</h5>
                                    <p>View financial reports</p>
                                </div>
                            </a>
                            <a href="<?= base_url('tracker/all_issues') ?>" class="quick-action-item" target="_blank">
                                <div class="action-icon bg-success">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <div class="action-content">
                                    <h5>Task Tracker</h5>
                                    <p>Manage all tasks</p>
                                </div>
                            </a>
                            <a href="<?= base_url('rdc/dashboard') ?>" class="quick-action-item" target="_blank">
                                <div class="action-icon bg-warning">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <div class="action-content">
                                    <h5>RDC Dashboard</h5>
                                    <p>Monitor activities</p>
                                </div>
                            </a>
                            <a href="<?= base_url('dashboard/work_summary') ?>" class="quick-action-item" target="_blank">
                                <div class="action-icon bg-info">
                                    <i class="fas fa-history"></i>
                                </div>
                                <div class="action-content">
                                    <h5>Work Summaries</h5>
                                    <p>Review work reports</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Task Modal -->
<div id="createTaskModal" class="zoom-anim-dialog modal-block mfp-hide modal-block-lg">
    <section class="panel">
        <div class="panel-heading">
            <h4 class="panel-title"><i class="fas fa-plus-circle"></i> <?= translate('create_new_task') ?></h4>
        </div>
        <?php echo form_open('advisor/create_task', [
            'class' => 'form-horizontal', 
            'method' => 'POST',
            'id' => 'advisorTaskForm'
        ]); ?>
        <div class="panel-body">
            
            <!-- Department -->
            <div class="row form-group">
                <div class="col-md-12">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fas fa-building"></i>
                        </span>
                        <select name="department" id="taskDepartment" class="form-control" required data-plugin-selectTwo data-placeholder="<?= translate('select_department') ?>" data-width="100%">
                            <option value=""><?= translate('select_department') ?></option>
                            <?php foreach ($departments as $dept): ?>
                            <option value="<?= $dept->identifier ?>"><?= html_escape($dept->title) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <span class="error"><?= form_error('department') ?></span>
                </div>
            </div>

            <!-- Title -->
            <div class="row form-group">
                <div class="col-md-12">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fas fa-heading"></i>
                        </span>
                        <input type="text" 
                               class="form-control" 
                               id="task_title" 
                               name="title" 
                               placeholder="<?= translate('enter_title') ?>" 
                               required>
                    </div>
                    <span class="error"><?= form_error('title') ?></span>
                </div>
            </div>

            <!-- Description -->
            <div class="row form-group">
                <div class="col-md-12">
                    <textarea name="description" id="task_description" class="summernote"></textarea>
                    <span class="error"><?= form_error('description') ?></span>
                </div>
            </div>
            
            <!-- Status, Priority, Assigned To -->
            <div class="row form-group">
                <!-- Status -->
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fas fa-tasks"></i>
                        </span>
                        <select name="task_status" class="form-control" required data-plugin-selectTwo data-width="100%">
                            <option value="todo"><?= translate('to-do') ?></option>
                            <option value="in_progress"><?= translate('in_progress') ?></option>
                            <option value="in_review"><?= translate('in_review') ?></option>
                            <option value="completed"><?= translate('completed') ?></option>
                            <option value="hold"><?= translate('Hold') ?></option>
                            <option value="canceled"><?= translate('canceled') ?></option>
                        </select>
                    </div>
                    <span class="error"><?= form_error('task_status') ?></span>
                </div>

                <!-- Priority -->
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fas fa-exclamation-circle"></i>
                        </span>
                        <select name="priority" class="form-control" data-plugin-selectTwo data-width="100%">
                            <option value="low"><?= translate('Low') ?></option>
                            <option value="medium" selected><?= translate('Medium') ?></option>
                            <option value="high"><?= translate('High') ?></option>
                            <option value="urgent"><?= translate('Urgent') ?></option>
                        </select>
                    </div>
                    <span class="error"><?= form_error('priority') ?></span>
                </div>

                <!-- Assigned To -->
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fas fa-user"></i>
                        </span>
                        <?php
                        $staff_array = $this->app_lib->getSelectList('staff');
                        unset($staff_array[1]); // Remove superadmin
                        echo form_dropdown(
                            "assigned_to",
                            $staff_array,
                            get_loggedin_user_id(), // Default to current user
                            "class='form-control' required
                            data-plugin-selectTwo
                            data-placeholder='".translate('assign_to')."'
                            data-width='100%'"
                        );
                        ?>
                    </div>
                    <span class="error"><?= form_error('assigned_to') ?></span>
                </div>

                <!-- Due Date -->
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fas fa-calendar-alt"></i>
                        </span>
                        <input type="date" 
                               name="due_date" 
                               class="form-control"
                               placeholder="<?= translate('select_due_date') ?>"
                               min="<?= date('Y-m-d') ?>">
                    </div>
                    <span class="error"><?= form_error('due_date') ?></span>
                </div>
            </div>

        </div>
        <footer class="panel-footer">
            <div class="row">
                <div class="col-md-12 text-right">
                    <button type="submit" class="btn btn-default mr-xs" id="saveTaskBtn">
                        <i class="fas fa-plus-circle"></i> <?= translate('create_task') ?>
                    </button>
                    <button type="button" class="btn btn-default modal-dismiss"><?= translate('cancel') ?></button>
                </div>
            </div>
        </footer>
        <?php echo form_close(); ?>
    </section>
</div>

<!-- Comments Modal -->
<div class="modal fade" id="commentsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center">
                <h4 class="modal-title mb-0" id="commentsModalTitle">Task: </h4>
                <button type="button" class="btn-close-custom" data-dismiss="modal" aria-label="Close">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                <div id="existingComments">
                    <h5>Comments:</h5>
                    <div id="commentsList"></div>
                </div>
                <hr>
                <form id="commentForm">
                    <input type="hidden" id="commentTaskId" name="task_id">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <div class="mention-container">
                        <textarea name="comment" id="commentText" class="form-control" rows="3" placeholder="Write Comment Here (Type @ to mention someone)" required></textarea>
                        <div id="mentionDropdown" class="mention-dropdown" style="display: none;"></div>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .btn-close-custom {
        background: #ff4d4f;
        color: #fff;
		float: right;
        font-size: 28px;
        font-weight: bold;
        border: none;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        line-height: 24px;
        text-align: center;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    .btn-close-custom:hover {
        background: #d9363e;
    }
</style>

<style>
/* Professional Dashboard Styles */

.dashboard-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 10px 0;
    margin-bottom: 20px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.dashboard-title {
    font-size: 2.5rem;
    font-weight: 300;
    margin: 0;
}

.dashboard-subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 5px 0 0 0;
}

.stats-section {
    margin-bottom: 20px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    border-left: 4px solid;
    transition: transform 0.2s ease;
    display: flex;
    align-items: center;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
}

.stat-card-primary { border-left-color: #007bff; }
.stat-card-success { border-left-color: #28a745; }
.stat-card-warning { border-left-color: #ffc107; }
.stat-card-info { border-left-color: #17a2b8; }

.stat-icon {
    font-size: 2.5rem;
    margin-right: 20px;
    opacity: 0.8;
}

.stat-card-primary .stat-icon { color: #007bff; }
.stat-card-success .stat-icon { color: #28a745; }
.stat-card-warning .stat-icon { color: #ffc107; }
.stat-card-info .stat-icon { color: #17a2b8; }

.stat-content h3 {
    font-size: 2.2rem;
    font-weight: 600;
    margin: 0;
    color: #2c3e50;
}

.stat-content p {
    margin: 5px 0 0 0;
    color: #6c757d;
    font-weight: 500;
}

.filter-section {
    margin-bottom: 30px;
}

.filter-card {
    background: white;
    border-radius: 12px;
    padding: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.filter-header h4 {
    color: #2c3e50;
    margin-bottom: 10px;
    font-weight: 600;
}

.content-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    margin-bottom: 30px;
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 20px 25px;
    border-bottom: 1px solid #dee2e6;
}

.card-header h4 {
    margin: 0;
    color: #2c3e50;
    font-weight: 600;
    font-size: 1.2rem;
}

.card-body {
    padding: 25px;
}

.table-modern {
    margin: 0;
}

.table-modern thead th {
    background-color: #f8f9fa;
    border-top: none;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    color: #495057;
    padding: 15px 12px;
}

.table-modern tbody td {
    padding: 15px 12px;
    vertical-align: middle;
    border-top: 1px solid #f1f3f4;
}

.table-modern tbody tr:hover {
    background-color: #f8f9fa;
}

.badge-modern {
    padding: 6px 12px;
    font-size: 1rem;
    font-weight: 500;
    border-radius: 20px;
}

.progress-sm {
    height: 6px;
    margin-bottom: 5px;
    border-radius: 3px;
}

.dept-info, .user-info {
    display: flex;
    align-items: center;
    gap: 8px;
}

.quick-actions {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.quick-action-item {
    display: flex;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s ease;
    border: 1px solid #e9ecef;
}

.quick-action-item:hover {
    background: #e9ecef;
    text-decoration: none;
    transform: translateX(5px);
}

.action-icon {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    color: white;
    font-size: 1.2rem;
}

.action-content h5 {
    margin: 0 0 5px 0;
    color: #2c3e50;
    font-weight: 600;
}

.action-content p {
    margin: 0;
    color: #6c757d;
    font-size: 1rem;
}

.amount-display {
    font-weight: 600;
    font-size: 1.4rem;
}

.task-title strong {
    color: #2c3e50;
}

.date-display {
    font-size: 1.2rem;
    color: #6c757d;
}

.row-number {
    background: #e9ecef;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: 600;
    color: #495057;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state i {
    opacity: 0.3;
}

.empty-state h5 {
    color: #6c757d;
    margin-top: 20px;
}

/* .main-content, .fund-section, .tasks-section {
    margin-bottom: 30px;
} */

.equal-height-row {
    display: flex;
    flex-wrap: wrap;
}

.equal-height-row > [class*="col-"] {
    display: flex;
    flex-direction: column;
}

.h-100 {
    height: 100% !important;
}

.modal-block.modal-block-lg {
    max-width: 90%;
}

.input-group-addon {
    background-color: #f8f9fa;
    border-color: #dee2e6;
    color: #495057;
}

.panel-heading {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #dee2e6;
}

.panel-title {
    color: #495057;
    font-weight: 600;
}

.form-control {
    border-radius: 4px;
    border: 1px solid #ced4da;
}

.form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn-default {
    background-color: #6c757d;
    border-color: #6c757d;
    color: #fff;
}

.btn-default:hover {
    background-color: #5a6268;
    border-color: #545b62;
    color: #fff;
}

.error {
    color: #dc3545;
    font-size: 12px;
    margin-top: 5px;
    display: block;
}

/* Mention system styles */
.mention-container {
  position: relative;
}

.mention-dropdown {
  position: absolute;
  bottom: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #ddd;
  border-radius: 4px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  max-height: 200px;
  overflow-y: auto;
  z-index: 1000;
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

.mentioned-user {
  background: #e3f2fd;
  color: #1976d2;
  padding: 2px 4px;
  border-radius: 3px;
  font-weight: 500;
}

/* Mention Styles */
.mention-dropdown {
    position: absolute;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    width: 250px;
}

.mention-item {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
}

.mention-item:hover {
    background-color: #f8f9fa;
}

.mention-item.selected {
    background-color: #007bff;
    color: white;
}

.mention-highlight {
    background-color: #e3f2fd;
    color: #1976d2;
    padding: 2px 4px;
    border-radius: 3px;
    font-weight: 500;
}
</style>

<!-- Summernote CSS/JS -->
<link rel="stylesheet" href="<?= base_url('assets/vendor/summernote/summernote.css') ?>">
<script src="<?= base_url('assets/vendor/summernote/summernote.js') ?>"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Task form submission handling
    $('#advisorTaskForm').submit(function(e) {
        e.preventDefault();
        var saveBtn = $('#saveTaskBtn');
        saveBtn.prop('disabled', true);
        saveBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing');
        
        // Submit the form
        this.submit();
        
        // Re-enable after 5 seconds if still on page
        setTimeout(() => {
            saveBtn.prop('disabled', false);
            saveBtn.html('<i class="fas fa-plus-circle"></i> <?= translate('create_task') ?>');
        }, 5000);
    });
    
    // No need for department-based staff loading since we show all staff directly

    $('#filterTasks').click(function() {
        var formData = $('#taskFilterForm').serialize();
        $.get('<?= base_url('advisor/get_tasks') ?>', formData, function(data) {
            if (data.tasks && data.department_summary) {
                updateTasksTable(data.tasks);
                updateDepartmentSummary(data.department_summary);
            }
        }).fail(function() {
            alert('Error loading filtered data');
        });
    });

    $('#commentForm').submit(function(e) {
        e.preventDefault();
        
        // Convert mentions to proper format before submission
        var commentText = $('#commentText').val();
        var mentionData = $('#commentText').data('mentions') || [];
        
        // Replace @Name with @[id] format for server processing
        mentionData.forEach(function(mention) {
            var namePattern = new RegExp('@' + mention.name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g');
            commentText = commentText.replace(namePattern, '@[' + mention.id + ']');
        });
        
        var formData = $(this).serialize().replace(
            encodeURIComponent($('#commentText').val()),
            encodeURIComponent(commentText)
        );
        
        $.post('<?= base_url('advisor/add_task_comment') ?>', formData, function(response) {
            if (response.status === 'success') {
                $('#commentText').val('').removeData('mentions');
                loadTaskComments($('#commentTaskId').val());
                updateReviewStatusAjax($('#commentTaskId').val(), 1);
            } else {
                alert('Failed to add comment: ' + (response.message || 'Unknown error'));
            }
        }, 'json').fail(function(xhr, status, error) {
            alert('Error: Could not add comment. Please try again.');
        });
    });
    
    // Mention functionality
    var mentionUsers = [];
    var selectedMentionIndex = -1;
    
    $('#commentText').on('input', function(e) {
        var text = $(this).val();
        var cursorPos = this.selectionStart;
        var textBeforeCursor = text.substring(0, cursorPos);
        var atIndex = textBeforeCursor.lastIndexOf('@');
        
        // Clean up mention data when text changes
        var mentionData = $(this).data('mentions') || [];
        var validMentions = [];
        mentionData.forEach(function(mention) {
            if (text.includes('@' + mention.name)) {
                validMentions.push(mention);
            }
        });
        $(this).data('mentions', validMentions);
        
        if (atIndex !== -1) {
            var searchTerm = textBeforeCursor.substring(atIndex + 1);
            if (searchTerm.length >= 0 && !searchTerm.includes(' ')) {
                searchMentionUsers(searchTerm, atIndex);
            } else {
                hideMentionDropdown();
            }
        } else {
            hideMentionDropdown();
        }
    });
    
    $('#commentText').on('keydown', function(e) {
        if ($('#mentionDropdown').is(':visible')) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedMentionIndex = Math.min(selectedMentionIndex + 1, mentionUsers.length - 1);
                updateMentionSelection();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedMentionIndex = Math.max(selectedMentionIndex - 1, 0);
                updateMentionSelection();
            } else if (e.key === 'Enter' && selectedMentionIndex >= 0) {
                e.preventDefault();
                selectMentionUser(mentionUsers[selectedMentionIndex]);
            } else if (e.key === 'Escape') {
                hideMentionDropdown();
            }
        }
    });
    
    function searchMentionUsers(searchTerm, atIndex, textarea) {
        $.get('<?= base_url('advisor/get_mention_users') ?>', {search: searchTerm}, function(users) {
            mentionUsers = users;
            selectedMentionIndex = users.length > 0 ? 0 : -1;
            showMentionDropdown(users, atIndex, textarea);
        });
    }
    
    function showMentionDropdown(users, atIndex, textarea) {
        var dropdown = $('#mentionDropdown');
        var html = '';
        
        users.forEach(function(user, index) {
            html += '<div class="mention-item' + (index === 0 ? ' selected' : '') + '" data-user-id="' + user.id + '" data-user-name="' + user.name + '">';
            html += '<span class="mention-name">' + user.name + '</span>';
            html += '</div>';
        });
        
        dropdown.html(html).show();
    }
    
    function updateMentionSelection() {
        $('.mention-item').removeClass('selected');
        $('.mention-item').eq(selectedMentionIndex).addClass('selected');
    }
    
    function selectMentionUser(user, textarea) {
        textarea = textarea || $('#commentText');
        var textareaEl = textarea[0];
        var text = textareaEl.value;
        var cursorPos = textareaEl.selectionStart;
        var textBeforeCursor = text.substring(0, cursorPos);
        var atIndex = textBeforeCursor.lastIndexOf('@');
        
        var beforeMention = text.substring(0, atIndex);
        var afterCursor = text.substring(cursorPos);
        var mentionText = '@' + user.name + ' ';
        
        textareaEl.value = beforeMention + mentionText + afterCursor;
        textareaEl.selectionStart = textareaEl.selectionEnd = beforeMention.length + mentionText.length;
        
        // Store the mention data for form submission
        var mentionData = textarea.data('mentions') || [];
        mentionData.push({name: user.name, id: user.id, position: atIndex});
        textarea.data('mentions', mentionData);
        
        hideMentionDropdown();
        textareaEl.focus();
    }
    
    function hideMentionDropdown() {
        $('#mentionDropdown').hide();
        selectedMentionIndex = -1;
    }
    
    $(document).on('click', '.mention-item', function() {
        var userId = $(this).data('user-id');
        var userName = $(this).data('user-name');
        selectMentionUser({id: userId, name: userName}, $('#commentText'));
    });
    
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.mention-textarea, #mentionDropdown').length) {
            hideMentionDropdown();
        }
    });
    
    function setupMentionForTextarea(textarea) {
        textarea.off('input.mention').on('input.mention', function(e) {
            var text = $(this).val();
            var cursorPos = this.selectionStart;
            var textBeforeCursor = text.substring(0, cursorPos);
            var atIndex = textBeforeCursor.lastIndexOf('@');
            
            // Clean up mention data when text changes
            var mentionData = $(this).data('mentions') || [];
            var validMentions = [];
            mentionData.forEach(function(mention) {
                if (text.includes('@' + mention.name)) {
                    validMentions.push(mention);
                }
            });
            $(this).data('mentions', validMentions);
            
            if (atIndex !== -1) {
                var searchTerm = textBeforeCursor.substring(atIndex + 1);
                if (searchTerm.length >= 0 && !searchTerm.includes(' ')) {
                    searchMentionUsers(searchTerm, atIndex, textarea);
                } else {
                    hideMentionDropdown();
                }
            } else {
                hideMentionDropdown();
            }
        });
        
        textarea.off('keydown.mention').on('keydown.mention', function(e) {
            if ($('#mentionDropdown').is(':visible')) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    selectedMentionIndex = Math.min(selectedMentionIndex + 1, mentionUsers.length - 1);
                    updateMentionSelection();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    selectedMentionIndex = Math.max(selectedMentionIndex - 1, 0);
                    updateMentionSelection();
                } else if (e.key === 'Enter' && selectedMentionIndex >= 0) {
                    e.preventDefault();
                    selectMentionUser(mentionUsers[selectedMentionIndex], textarea);
                } else if (e.key === 'Escape') {
                    hideMentionDropdown();
                }
            }
        });
    }
    
    // Initialize mention for main comment textarea on modal open
    $(document).on('shown.bs.modal', '#commentsModal', function() {
        $('#commentText').removeData('mentions');
        setupMentionForTextarea($('#commentText'));
    });
    
    // Clean up on modal close
    $(document).on('hidden.bs.modal', '#commentsModal', function() {
        $('#commentText').val('').removeData('mentions');
        hideMentionDropdown();
    });
    
    initDepartmentChart();
    
    // Initialize Select2 for modal dropdowns and Summernote
    $(document).on('mfpOpen', function() {
        setTimeout(function() {
            $('#taskDepartment, select[name="assigned_to"], select[name="task_status"], select[name="priority"]').select2({
                width: '100%',
                dropdownParent: $('#createTaskModal')
            });
            
            // Initialize Summernote
            $('.summernote').summernote({
                height: 100,
                dialogsInBody: true,
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['fontsize']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link']],
                    ['view', ['codeview']]
                ]
            });
        }, 100);
    });
    

});

function viewComments(taskId, taskTitle) {
    $('#commentTaskId').val(taskId);
    $('#commentsModalTitle').text('Task: ' + taskTitle);
    loadTaskComments(taskId);
    $('#commentsModal').modal('show');
}

function loadTaskComments(taskId) {
    $.get('<?= base_url('advisor/get_task_comments') ?>', {task_id: taskId}, function(data) {
        var html = '';
        var currentUserId = <?= get_loggedin_user_id() ?>;
        if (data.length > 0) {
            $.each(data, function(index, comment) {
                var date = new Date(comment.created_at).toLocaleDateString('en-GB', {
                    day: '2-digit',
                    month: 'short',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                html += '<div class="comment-item" style="margin-bottom: 10px; padding: 8px; background: #f9f9f9; border-left: 3px solid #007bff;" data-comment-id="' + comment.id + '">';
                html += '<div style="display: flex; justify-content: space-between; align-items: center;">';
                html += '<div><strong>' + comment.author_name + '</strong> <small class="text-muted">(' + date + ')</small></div>';

                html += '</div>';
                var commentText = comment.comment_text.replace(/\n/g, '<br>');
                // Highlight mentions that are already in @name format
                commentText = commentText.replace(/@([A-Za-z\s]+)/g, '<span class="mention-highlight">@$1</span>');
                html += '<div class="comment-text" style="margin: 5px 0 0 0;">' + commentText + '</div>';

                html += '</div>';
            });
        } else {
            html = '<p class="text-muted">No comments yet</p>';
        }
        $('#commentsList').html(html);
    });
}

function updateTasksTable(tasks) {
    var html = '';
    if (tasks.length > 0) {
        html = '<div class="table-responsive"><table class="table table-modern table-export" cellspacing="0" width="100%" id="filteredTasksTable">';
        html += '<thead><tr><th>SL</th><th>Title</th><th>Assigned To</th><th>Dept</th><th class="text-center">Status</th><th class="text-center">Due Date</th><th class="text-center">Actions</th></tr></thead><tbody>';
        
        $.each(tasks, function(index, task) {
            var statusClass = task.task_status == 'completed' ? 'success' : 'warning';
            var dueDate = task.estimated_end_time ? new Date(task.estimated_end_time).toLocaleDateString('en-GB').replace(/\//g, '-') : '-';
            
            html += '<tr>';
            html += '<td><span class="row-number">' + (index + 1) + '</span></td>';
            html += '<td><div class="task-title"><strong>' + task.task_title + '</strong></div></td>';
            html += '<td><div class="user-info"><i class="fas fa-user-circle text-muted"></i> ' + task.assigned_name + '</div></td>';
            html += '<td><div class="dept-info"><i class="fas fa-building text-muted"></i> ' + task.department_name + '</div></td>';
            html += '<td class="text-center"><span class="badge badge-' + statusClass + ' badge-modern">' + task.task_status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) + '</span></td>';
            html += '<td class="text-center"><span class="date-display">' + dueDate + '</span></td>';
            html += '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-info" onclick="viewComments(' + task.id + ', \'' + task.task_title + '\');"><i class="fas fa-comments"></i> Comments</button></td>';
            html += '</tr>';
        });
        
        html += '</tbody></table></div>';
    } else {
        html = '<div class="empty-state"><i class="fas fa-tasks fa-3x text-muted"></i><h5 class="mt-3">No Tasks Found</h5><p class="text-muted">No tasks match your filter criteria</p></div>';
    }
    
    $('#tasksContainer').html(html);
    
    // Initialize DataTable for filtered results
    if (tasks.length > 0) {
        $('#filteredTasksTable').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[0, 'asc']]
        });
    }
}

var departmentChart;

function initDepartmentChart() {
    var ctx = document.getElementById('departmentChart').getContext('2d');
    var departmentData = <?= json_encode($department_summary) ?>;
    
    departmentChart = createChart(ctx, departmentData);
}

function createChart(ctx, departmentData) {
    var labels = [];
    var completionRates = [];
    var colors = ['#28a745', '#17a2b8', '#ffc107', '#dc3545', '#6f42c1', '#fd7e14'];
    
    departmentData.forEach(function(dept) {
        if (parseInt(dept.total_tasks) > 0) {
            var rate = Math.round((parseInt(dept.completed_tasks) / parseInt(dept.total_tasks)) * 100);
            labels.push(dept.title.substring(0, 15) + ' (' + rate + '%)');
            completionRates.push(rate);
        }
    });
    
    if (labels.length > 0) {
        return new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: completionRates,
                    backgroundColor: colors.slice(0, labels.length)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 10
                            }
                        }
                    }
                }
            }
        });
    } else {
        ctx.font = '14px Arial';
        ctx.fillText('No data available', 50, 100);
        return null;
    }
}

function updateDepartmentSummary(departmentData) {
    // Update the department summary table (first table in main content)
    var tableHtml = '';
    departmentData.forEach(function(dept) {
        var progress = dept.total_tasks > 0 ? Math.round((dept.completed_tasks / dept.total_tasks) * 100) : 0;
        tableHtml += '<tr>';
        tableHtml += '<td><div class="dept-info"><i class="fas fa-folder text-primary"></i><strong>' + dept.title + '</strong></div></td>';
        tableHtml += '<td class="text-center"><span class="badge badge-primary badge-modern">' + dept.total_tasks + '</span></td>';
        tableHtml += '<td class="text-center"><span class="badge badge-warning badge-modern">' + dept.pending_tasks + '</span></td>';
        tableHtml += '<td class="text-center"><span class="badge badge-success badge-modern">' + dept.completed_tasks + '</span></td>';
        tableHtml += '<td class="text-center">';
        tableHtml += '<div class="progress progress-sm"><div class="progress-bar bg-success" style="width: ' + progress + '%"></div></div>';
        tableHtml += '<small class="text-muted">' + progress + '%</small>';
        tableHtml += '</td>';
        tableHtml += '</tr>';
    });
    
    // Target the department summary table specifically
    $('.main-content .table-modern tbody').first().html(tableHtml);
    
    // Update the chart
    if (departmentChart) {
        departmentChart.destroy();
    }
    var ctx = document.getElementById('departmentChart').getContext('2d');
    departmentChart = createChart(ctx, departmentData);
    
    // Update statistics cards
    var totalTasks = departmentData.reduce((sum, dept) => sum + parseInt(dept.total_tasks), 0);
    var totalPending = departmentData.reduce((sum, dept) => sum + parseInt(dept.pending_tasks), 0);
    var totalCompleted = departmentData.reduce((sum, dept) => sum + parseInt(dept.completed_tasks), 0);
    var completionRate = totalTasks > 0 ? Math.round((totalCompleted / totalTasks) * 100) : 0;
    
    $('.stat-card-primary .stat-content h3').text(totalTasks);
    $('.stat-card-warning .stat-content h3').text(totalPending);
    $('.stat-card-success .stat-content h3').text(totalCompleted);
    $('.stat-card-info .stat-content h3').text(completionRate + '%');
}

function updateReviewStatusAjax(taskId, status) {
    $.post('<?= base_url('advisor/update_review_status') ?>', {
        task_id: taskId,
        status: status,
        '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
    }, function(response) {
        if (response.status === 'success') {
            var statusText = status == 1 ? '<span class="label label-success-custom badge-modern text-xs">Completed</span>' : '<span class="label label-warning-custom badge-modern text-xs">Pending</span>';
            $('button[onclick*="viewComments(' + taskId + ',"]').closest('tr').find('td:nth-child(7)').html(statusText);
        }
    }, 'json');
}



</script>