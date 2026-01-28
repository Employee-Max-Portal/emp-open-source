<style>
body .btn-primary {
    color: #ffffff;
    text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
    background-color: #00a51f;
    border-color: #11a300;
}
body .btn-primary:hover {
    border-color: #2dc4e9 !important;
    background-color: #089296;
}

html.sidebar-light:not(.dark) .header .logo-env {
    background: linear-gradient(to right, rgb(255 255 255) 0%, #fff 100%);
}

.notification-bell {
    position: relative;
    padding: 12px;
    cursor: pointer;
    font-size: 18px;
}

.notification-dot {
    position: absolute;
    top: 6px;
    right: 6px;
    background: red;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.notification-dropdown {
    position: absolute;
    right: 0;
    top: 36px;
    background: #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    width: 300px;
    max-height: 400px;
    overflow-y: auto;
    display: none;
    z-index: 9999;
    border-radius: 6px;
}

.notification-dropdown.show {
    display: block;
}

.notification-item {
    padding: 12px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
}

.notification-item:hover {
    background: #f2f2f2;
}

.notification-item.unread::before {
    content: '';
    display: inline-block;
    width: 8px;
    height: 8px;
    background: red;
    border-radius: 50%;
    margin-right: 8px;
    vertical-align: middle;
}

</style>

<style>
.btn-sm {
    padding: 6px 12px !important;
    font-size: 1rem !important;
    border-radius: 6px !important;
    transition: all 0.2s ease !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.btn-sm:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.btn-sm:active {
    transform: translateY(1px);
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.btn.btn-info.btn-sm {
	background-color: #4B352A;
	border: 1px solid #4B352A;
	color: #fff;
	min-width: 120px;
	transition: background-color 0.3s ease;
}
.btn.btn-info.btn-sm:hover {
	background-color: #3B2921;
	cursor: pointer;
}

/* Shortcut Menu Box Styles */
.header-menubox {
    position: absolute !important;
    top: 100% !important;
    left: 50% !important;
    transform: translateX(-50%) !important;
    background: #fff !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
    padding: 15px !important;
    min-width: 280px !important;
    z-index: 1000 !important;
    margin-top: 5px !important;
}

.short-q {
    width: 100%;
}

.menu-icon-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
    width: 100%;
}

.menu-icon-grid a {
    display: flex !important;
    align-items: center !important;
    padding: 12px 15px !important;
    text-decoration: none !important;
    color: #374151 !important;
    border-radius: 6px !important;
    transition: all 0.2s ease !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    border: 1px solid #f3f4f6 !important;
    background: #f9fafb !important;
}

.menu-icon-grid a:hover {
    background: #e5f3ff !important;
    color: #1976d2 !important;
    border-color: #bbdefb !important;
    text-decoration: none !important;
    transform: translateY(-1px) !important;
}

.menu-icon-grid a i {
    margin-right: 8px !important;
    font-size: 16px !important;
    width: 20px !important;
    text-align: center !important;
    color: #6b7280 !important;
}

.menu-icon-grid a:hover i {
    color: #1976d2 !important;
}

@media (max-width: 768px) {
    .header-menubox {
        left: auto !important;
        right: 0 !important;
        transform: none !important;
        min-width: 250px !important;
    }
    
    .menu-icon-grid {
        grid-template-columns: 1fr !important;
    }
}

</style>

<style>
.drawer-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
    z-index: 1050;
}

.drawer-modal {
    position: fixed;
    top: 0;
    right: -30%;
    width: 30%;
    height: 100%;
    background: #fff;
    box-shadow: -2px 0 10px rgba(0,0,0,0.1);
    transition: right 0.3s ease;
    z-index: 9999;
    padding: 2rem;
}

.drawer-modal.active {
    right: 0;
}

.drawer-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.close-drawer {
    background: none;
    border: none;
    font-size: 1.5rem;
    line-height: 1;
    color: #333;
    cursor: pointer;
}

</style>

<header class="header">
	<div class="logo-env">
		<a href="<?php echo base_url('dashboard');?>" class="logo">
			<!--<img src="<?=$this->application_model->getBranchImage(get_loggedin_branch_id(), 'logo-small')?>" height="40">-->
			<img src="<?php echo base_url();?>uploads/app_image/emp-logo.png" height="40">
		</a>

		<div class="visible-xs toggle-sidebar-left" data-toggle-class="sidebar-left-opened" data-target="html" data-fire-event="sidebar-left-opened">
			<i class="fa fa-bars" aria-label="Toggle sidebar"></i>
		</div>
	</div>

	<div class="header-left hidden-xs">
		<ul class="header-menu">
			<!-- sidebar toggle button -->
			<li>
				<div id="sidebar-toggle-button" class="header-menu-icon sidebar-toggle" data-toggle-class="sidebar-left-collapsed" data-target="html" data-fire-event="sidebar-left-toggle">
					<i class="fas fa-bars" aria-label="Toggle sidebar"></i>
				</div>
			</li>
			<!-- full screen button -->
			<li>
				<div class="header-menu-icon s-expand">
					<i class="fas fa-expand"></i>
				</div>
			</li>
			<!-- Shortcut Menu Box -->
			<li>
				<div class="header-menu-icon dropdown-toggle" data-toggle="dropdown">
					<i class="fas fa-th"></i>
				</div>
				<div class="dropdown-menu header-menubox">
					<div class="short-q">
						<div class="menu-icon-grid">
							<a href="<?php echo base_url('planner'); ?>"><i class="fas fa-calendar-alt"></i>My Planner</a>
							<a href="<?php echo base_url('team_planner/card'); ?>"><i class="fas fa-users"></i>Team Planner</a>
							<a href="<?php echo base_url('rdc'); ?>"><i class="fas fa-tasks"></i>RDC Task</a>
							<a href="<?php echo base_url('todo'); ?>"><i class="fas fa-exclamation-triangle"></i>To-Do</a>
						</div>
					</div>
				</div>
			</li>
			
			<!-- search menu -->
			<li>
				<div class="menu-search-container">
					<input type="text" id="menuSearch" placeholder="Search menu..." class="menu-search-input">
					<i class="fas fa-search search-icon"></i>
				</div>
			</li>
		</ul>
	</div>

	<div class="header-right">

		<ul class="header-menu">
			<?php
				$loggedin_user_id = get_loggedin_user_id();
				$loggedin_role_id = loggedin_role_id();
				$allowed_roles = [1, 2, 3, 5];

				$unreadCount = 0;

				// ✅ Role 1,2,3,5 → See all their own notifications
				if (in_array($loggedin_role_id, $allowed_roles)) {
					$this->db->group_start();
					$this->db->where(['user_id' => $loggedin_user_id, 'is_read' => 0]);
					$this->db->or_where(['user_id' => 0]);
					$this->db->group_end();
					$unreadCount = $this->db->count_all_results('notifications');
				}else {
					// ✅ Others → See notifications from same department
					$my_department = $this->db->select('department')->from('staff')->where('id', $loggedin_user_id)->get()->row('department');
					
					$staff_ids = $this->db
						->select('id')
						->from('staff')
						->where('department', $my_department)
						->get()->result_array();

					$ids = array_column($staff_ids, 'id');

					
					if (!empty($ids)) {
						$unreadCount = $this->db
							->where_in('user_id', $ids)
							//->or_where('user_id', 0)
							->where('is_read', 0)
							->count_all_results('notifications');
					}
				}
			?>
			<?php
				$user_id = get_loggedin_user_id();
				$today = date('Y-m-d');

				$already_submitted = $this->db
					->where('user_id', $user_id)
					->where('summary_date', $today)
					->count_all_results('daily_work_summaries') > 0;
			?>
			
			<!-- new issue button -->
			<li class="new-issue-topbar-wrapper" style="padding-left: 8px;">
				<button type="button" class="btn btn-outline-primary btn-sm" onclick="mfp_modal('#IssueModal')" style="min-width: 100px; background-color: #36a436; border: 1px solid #36a436; color: #fff; transition: background-color 0.3s ease;" onmouseover="this.style.backgroundColor='#2d8a2d';" onmouseout="this.style.backgroundColor='#36a436';">
					<i class="fas fa-plus-circle" style="margin-right: 6px;"></i>New Issue
				</button>
			</li>

			<!-- daily work summary button -->
			<li class="daily-summary-wrapper" style="padding-left: 8px;">
				<?php if ($already_submitted): ?>
					<button type="button" class="btn btn-success btn-sm" style="min-width: 110px;" disabled>
						<i class="fas fa-check-circle" style="margin-right: 6px;"></i>Submitted
					</button>
				<?php else: ?>
					<button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleDailySummaryDrawer()" style="min-width: 110px; background-color: #CA7842; border: 1px solid #CA7842; color: #fff; transition: background-color 0.3s ease;" onmouseover="this.style.backgroundColor='#A45F35';" onmouseout="this.style.backgroundColor='#CA7842';">
						<i class="fas fa-clipboard-list" style="margin-right: 6px;"></i>Daily Summary
					</button>
				<?php endif; ?>
			</li>

			<!-- break button -->
			<li class="break-button-wrapper" style="padding-left: 8px;">
				<a href="javascript:void(0);"
				   onclick="get_break_details('<?php echo get_loggedin_user_id(); ?>')"
				   style="text-decoration: none;">

					<?php 
						$break_condition = get_break_condition();
						$button_class = $break_condition == 1 ? 'btn btn-warning btn-sm' : 'btn btn-info btn-sm';
						$button_text = $break_condition == 1 ? translate('Break : Active') : translate('Break : Inactive');
					?>
					
					<button type="button" class="<?php echo $button_class; ?>" style="min-width: 120px;">
						<i class="fas fa-coffee" style="margin-right: 6px;"></i><?php echo $button_text; ?>
					</button>

				</a>
			</li>

			<!-- notification bell -->
			<li class="notification-wrapper position-relative" onclick="toggleNotificationDropdown()" style="cursor: pointer;">
				<a class="header-menu-icon">
					<i class="fa fa-bell"></i>
				
				<?php if ($unreadCount > 0): ?>
					<span class="notification-dot"
						style="position: absolute; top: 2px; right: 2px; width: 10px; height: 10px; background: red; border-radius: 50%;">
					</span>
				<?php endif; ?>
				</a>
				<div id="notification-dropdown"
					class="notification-dropdown"
					style="position: absolute; top: 40px; background: #fff; width: 300px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); display: none; z-index: 1000;">
					<!-- Will be populated via JS -->
				</div>
			</li>
		</ul>
		
		<?php

		$userID = get_loggedin_user_id();

		// Query to get the photo from the staff table where id == userID
		$query = $this->db->select('photo')  // Select the photo column
						  ->from('staff')   // From the staff table
						  ->where('id', $userID)  // Where id matches userID
						  ->get();  // Execute the query

		// Check if a result was returned
		if ($query->num_rows() > 0) {
			$userProfileImg = $this->app_lib->get_image_url('staff/' . $query->row()->photo);
		} else {
			// Fallback image or handling if no photo is found
			$userProfileImg = $this->app_lib->get_image_url('default.jpg');
		}

		?>

		<!-- user profile box -->
		<span class="separator"></span>
		<div id="userbox" class="userbox">
			<a href="#" data-toggle="dropdown">
				<figure class="profile-picture">
					<img src="<?php echo html_escape($userProfileImg); ?>" alt="user-image" class="img-circle" height="35">
				</figure>
			</a>
			<div class="dropdown-menu">
				<ul class="dropdown-user list-unstyled">
					<li class="user-p-box">
						<div class="dw-user-box">
							<div class="u-img">
								<img src="<?php echo html_escape($userProfileImg); ?>" alt="user">
							</div>
							<div class="u-text">
								<h4><?php echo $this->session->userdata('name');?></h4>
								<p class="text-muted"><?php echo ucfirst(loggedin_role_name());?></p>
								<a href="<?php echo base_url('authentication/logout'); ?>" class="btn btn-danger btn-xs"><i class="fas fa-sign-out-alt"></i> <?php echo translate('logout');?></a>
							</div>
						</div>
					</li>
					<li role="separator" class="divider"></li>
					<li><a href="<?php echo base_url('profile');?>"><i class="fas fa-user-shield"></i> <?php echo translate('profile');?></a></li>
					<li><a href="<?php echo base_url('profile/password');?>"><i class="fas fa-mars-stroke-h"></i> <?php echo translate('reset_password');?></a></li>
					<!-- <li><a href="<?php echo base_url('communication/mailbox/inbox');?>"><i class="far fa-envelope"></i> <?php echo translate('mailbox');?></a></li> -->
					<?php if(get_permission('global_settings', 'is_view')):?>
						<li role="separator" class="divider"></li>
						<li><a href="<?php echo base_url('settings/universal');?>"><i class="fas fa-toolbox"></i> <?php echo translate('global_settings');?></a></li>
					<?php endif; ?>
					
					<li role="separator" class="divider"></li>
					<li><a href="<?php echo base_url('authentication/logout');?>"><i class="fas fa-sign-out-alt"></i> <?php echo translate('logout');?></a></li>
				</ul>
			</div>
		</div>
	</div>
</header>

<?php
	$user_id = get_loggedin_user_id();
	$staff_data = $this->db
		->select('staff.name AS staff_name, staff_department.name AS department_name')
		->from('staff')
		->join('staff_department', 'staff.department = staff_department.id', 'left')
		->where('staff.id', $user_id)
		->get()
		->row();
?>


<style>
  #daily-summary-drawer {
    position: fixed; 
    top: 0;
    right: -70%;
    width: 70%;
    height: 100%;
    background: #fff;
    box-shadow: -2px 0 8px rgba(0,0,0,0.2);
    transition: right 0.3s ease-in-out;
    z-index: 2000;
    padding: 20px;
    overflow-y: auto;
    overflow-x: hidden;
  }

  @media (max-width: 768px) {
    #daily-summary-drawer {
      right: -90%;
      width: 90%;
    }
  }
</style>


<!-- Daily Work Summary Drawer -->
<div id="drawer-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
background-color: rgba(0, 0, 0, 0.4); z-index: 1999;"></div>

<div id="daily-summary-drawer">
	<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
		<h4 class="font-weight-bold mb-0">📋 Daily Work Summary <b>(<?= date('Y-m-d'); ?>)</b></h4>
		<button onclick="toggleDailySummaryDrawer()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666;">&times;</button>
	</div>
	<br>
	<form id="daily-summary-form" action="<?= base_url('dashboard/submit_work_summary') ?>" method="POST" onsubmit="return handleFormSubmission(this)">
		<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />
		<input type="hidden" name="summary_date" class="form-control mb-2" value="<?= date('Y-m-d'); ?>" readonly>
		<input type="hidden" name="department" class="form-control mb-2" value="<?= $staff_data->department_name ?? 'Unknown'; ?>" readonly>
		<input type="hidden" name="name" class="form-control mb-2" value="<?= $staff_data->staff_name ?? 'Unknown'; ?>" readonly>

		<!-- 📌 Assigned Tasks -->
		<div class="form-group">
			<label class="col-md-4 control-label" style="padding-right: 5px;">📌 <?= translate('Assigned Tasks:') ?></label>
			<div class="col-md-8" style="padding: 0 5px;" id="assigned_task_list">
				<div class="row mb-3 task-row" id="assigned_task_0" style="padding-bottom: 10px;">
					<div class="col-md-4" style="padding-right: 2px;">
						<input type="text" class="form-control" name="assigned_tasks_title[]" placeholder="Task Title">
					</div>
					<div class="col-md-4" style="padding-right: 2px;">
						<input type="text" class="form-control" name="assigned_tasks_link[]" placeholder="Task Link">
					</div>
					<div class="col-md-2" style="padding-right: 2px;">
						<div class="form-check mt-2">
							<input type="checkbox" class="form-check-input assigned-planner-check" name="assigned_tasks_planner_0" value="1" data-index="0">
							<label class="form-check-label small">Planner</label>
						</div>
					</div>

					<div class="col-md-2 text-right">
						<button type="button" class="btn btn-danger btn-md" onclick="event.stopPropagation(); removeAssignedTask(0)">
							<i class="fas fa-times"></i>
						</button>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-offset-4 col-md-12">
					<button type="button" class="btn btn-default btn-sm mt-2" onclick="addAssignedTaskRow()">
						<i class="fas fa-plus-circle"></i> Add Task
					</button>
				</div>
			</div>
		</div>

		<!-- ✅ Completed Tasks -->
		<div class="form-group">
			<label class="col-md-4 control-label" style="padding-right: 5px;">✅ <?= translate('Completed Tasks:') ?></label>
			<div class="col-md-8" style="padding: 0 5px;" id="completed_task_list">
				<div class="row mb-3 task-row" id="completed_task_0" style="padding-bottom: 10px;">
					<div class="col-md-4" style="padding-right: 2px;">
						<input type="text" class="form-control" name="completed_tasks_title[]" placeholder="Completed Title">
					</div>
					<div class="col-md-4" style="padding-right: 2px;">
						<input type="text" class="form-control" name="completed_tasks_link[]" placeholder="Proof Link">
					</div>
					<div class="col-md-2" style="padding-right: 2px;">
						<input type="text" class="form-control completed-time-input" name="completed_tasks_time[]" placeholder="Hours Spent" oninput="calculateTotalTimeSpent()">
					</div>
					<div class="col-md-2 text-right">
						<button type="button" class="btn btn-danger btn-md" onclick="event.stopPropagation(); removeCompletedTask(0)">
							<i class="fas fa-times"></i>
						</button>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-offset-4 col-md-12">
					<button type="button" class="btn btn-default btn-sm mt-2" onclick="addCompletedTaskRow()">
						<i class="fas fa-plus-circle"></i> Add Task
					</button>
				</div>
			</div>
		</div>

		<!-- 📉 Completion Ratio -->
		<div class="form-group">
			<label class="col-md-4 control-label" style="padding-right: 5px;">📉 <?= translate('Completion Ratio (%):') ?></label>
			<div class="col-md-8" style="padding: 0 5px;">
				<input type="text" id="calculated_work_ratio"  name="completion_ratio" class="form-control mb-2" readonly value="0%">
			</div>
		</div>

		<!-- ⏱️ Total Time Spent -->
		<div class="form-group">
			<label class="col-md-4 control-label" style="padding-right: 5px;">⏱️ <?= translate('Completed Task Total Spent Time (hrs):') ?></label>
			<div class="col-md-8" style="padding: 0 5px;">
				<input type="text" id="total_time_spent" class="form-control mb-2" readonly value="0">
			</div>
		</div>

		<!-- 🚫 Blockers -->
		<div class="form-group">
			<label class="col-md-4 control-label" style="padding-right: 5px;">🚫 <?= translate('Blockers:') ?></label>
			<div class="col-md-8" style="padding: 0 5px;">
				<textarea name="blockers" class="form-control mb-2" rows="2"></textarea>
			</div>
		</div>

		<!-- ➡ Next Steps -->
		<div class="form-group">
			<label class="col-md-4 control-label" style="padding-right: 5px;">➡ <?= translate('Next Steps:') ?></label>
			<div class="col-md-8" style="padding: 0 5px;">
				<textarea name="next_steps" class="form-control mb-2" rows="2"></textarea>
			</div>
		</div>
		
		<!-- Confirmation Checkbox -->
		<div class="form-group">
			<div class="col-md-8 offset-md-4">
				<input type="checkbox" name="confirmation" required>
				<label for="confirmation">I confirm that the provided daily work summary data is accurate.</label>
			</div>
		</div>
		<br>
		<div class="button-wrapper">
			<!-- Submit -->
			<button type="submit" class="custom-submit-btn" >Submit Summary</button>
		</div>
	</form>
</div>

<style>
.button-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 20px 0;
}

.custom-submit-btn {
    width: 20%;
    padding: 12px 0;
    font-size: 1.1rem;
    background-color: #007bff;
    border: none;
    border-radius: 12px;
    color: white;
    transition: background-color 0.3s ease, transform 0.2s ease;
    box-shadow: 0 4px 10px rgba(0, 123, 255, 0.2);
}

/* Hover effect */
.custom-submit-btn:hover {
    background-color: #0056b3;
    transform: scale(1.02);
}

/* Tablet view */
@media (max-width: 992px) {
    .custom-submit-btn {
        width: 40%;
    }
}

/* Mobile view */
@media (max-width: 576px) {
    .custom-submit-btn {
        width: 80%;
    }
}

/* Menu Search Styles */
.menu-search-container {
    position: relative;
    display: flex;
    align-items: center;
    margin-left: 10px;
}

.menu-search-input {
    width: 200px;
    padding: 8px 35px 8px 12px;
    border: 1px solid #ddd;
    border-radius: 20px;
    font-size: 14px;
    outline: none;
    transition: all 0.3s ease;
}

.menu-search-input:focus {
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0,123,255,0.3);
    width: 250px;
}

.search-icon {
    position: absolute;
    right: 12px;
    color: #666;
    pointer-events: none;
}

/* Highlighted menu items */
.menu-highlight {
    background-color: #fff3cd !important;
    border-left: 3px solid #ffc107 !important;
    animation: highlight-pulse 1s ease-in-out;
}

@keyframes highlight-pulse {
    0%, 100% { background-color: #fff3cd; }
    50% { background-color: #ffeaa7; }
}

.menu-highlight a {
    font-weight: bold !important;
    color: #856404 !important;
}

@media (max-width: 768px) {
    .menu-search-container {
        display: none;
    }
    
    .header-menu {
        flex-wrap: wrap;
        gap: 5px;
    }
    
    .header-menu li {
        margin: 2px 4px;
    }
    
    .log-task-topbar-wrapper,
    .daily-summary-wrapper,
    .break-button-wrapper {
        padding-left: 2px !important;
    }
    
    .btn-sm {
        padding: 4px 6px;
        font-size: 11px;
        min-width: 80px !important;
    }
    
    .drawer-modal {
        width: 90% !important;
        right: -90% !important;
    }
    
    .drawer-modal.active {
        right: 0 !important;
    }
}
</style>
<!-- Scripts -->
<script>
document.addEventListener("DOMContentLoaded", function () {
	loadStaffTasks();
});

function loadStaffTasks() {
	fetch('<?= base_url("dashboard/get_staff_tasks") ?>?date=<?= date("Y-m-d") ?>')
	.then(response => response.json())
	.then(data => {
		populateAssignedTasks(data.assigned);
		populateCompletedTasks(data.completed);
		updateSummaryStats();
	});
}
function populateAssignedTasks(tasks) {
    const container = document.getElementById("assigned_task_list");
    container.innerHTML = '';
    assignedTaskIndex = 0;
    
    if(tasks.length === 0) {
        addAssignedTaskRow();
        return;
    }
    
    tasks.forEach((task, index) => {
        const html = `
        <div class="row mb-3 task-row" id="assigned_task_${index}" style="padding-bottom: 10px;">
			<div class="col-md-4" style="padding-right: 2px;">
                <input type="text" class="form-control" name="assigned_tasks_title[]" value="${task.title}" placeholder="Task Title" oninput="calculateCompletionRatio()">
            </div>
            <div class="col-md-4" style="padding-right: 2px;">
                <input type="text" class="form-control" name="assigned_tasks_link[]" value="${task.link}" placeholder="Task Link">
            </div>
            <div class="col-md-2" style="padding-right: 2px;">
                <div class="form-check mt-2">
                    <input type="checkbox" class="form-check-input assigned-planner-check" name="assigned_tasks_planner_${index}" value="1" data-index="${index}" ${task.planner == 1 ? 'checked' : ''} onchange="calculateCompletionRatio()">
                    <label class="form-check-label small">Planner</label>
                </div>
            </div>
            <div class="col-md-2 text-right">
                <button type="button" class="btn btn-danger btn-md" onclick="event.stopPropagation(); removeAssignedTask(${index})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>`;
        container.insertAdjacentHTML('beforeend', html);
        assignedTaskIndex = index + 1;
    });
}

function populateCompletedTasks(tasks) {
    const container = document.getElementById("completed_task_list");
    container.innerHTML = '';
    completedTaskIndex = 0;
    
    if(tasks.length === 0) {
        addCompletedTaskRow();
        return;
    }
    
    tasks.forEach((task, index) => {
        const escapedTimeSpent = $('<div>').text(task.time_spent).html();
        const html = `
        <div class="row mb-3 task-row" id="completed_task_${index}" style="padding-bottom: 10px;">
			<div class="col-md-4" style="padding-right: 2px;">
                <input type="text" class="form-control" name="completed_tasks_title[]" value="${task.title}" placeholder="Completed Title" oninput="calculateCompletionRatio()">
            </div>
            <div class="col-md-4" style="padding-right: 2px;">
                <input type="text" class="form-control" name="completed_tasks_link[]" value="${task.link}" placeholder="Proof Link">
            </div>
            <div class="col-md-2" style="padding-right: 2px;">
                <input type="text" class="form-control completed-time-input" name="completed_tasks_time[]" value="${escapedTimeSpent}" placeholder="Hours Spent" oninput="calculateTotalTimeSpent()">
            </div>
            <div class="col-md-2 text-right">
                <button type="button" class="btn btn-danger btn-md" onclick="event.stopPropagation(); removeCompletedTask(${index})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>`;
        container.insertAdjacentHTML('beforeend', html);
        completedTaskIndex = index + 1;
    });
}


function toggleDailySummaryDrawer() {
	const drawer = document.getElementById('daily-summary-drawer');
	const overlay = document.getElementById('drawer-overlay');
	const isOpen = drawer.style.right === '0px';
	const isMobile = window.innerWidth <= 768;
	const closedPosition = isMobile ? '-95%' : '-70%';
	drawer.style.right = isOpen ? closedPosition : '0px';
	overlay.style.display = isOpen ? 'none' : 'block';
}

function updateSummaryStats() {
	calculateTotalTimeSpent();
	calculateCompletionRatio();
}

document.addEventListener('click', function (e) {
	const drawer = document.getElementById('daily-summary-drawer');
	const overlay = document.getElementById('drawer-overlay');
	const isInsideDrawer = drawer.contains(e.target);
	const isTriggerButton = e.target.closest('.daily-summary-wrapper');
	if (!isInsideDrawer && !isTriggerButton && overlay.style.display === 'block') {
		const isMobile = window.innerWidth <= 768;
		const closedPosition = isMobile ? '-95%' : '-70%';
		drawer.style.right = closedPosition;
		overlay.style.display = 'none';
	}
});

function calculateTotalTimeSpent() {
	let total = 0;
	document.querySelectorAll('input[name="completed_tasks_time[]"]').forEach(input => {
		const val = parseFloat(input.value);
		if (!isNaN(val)) total += val;
	});
	
	const hours = Math.floor(total);
	const mins = Math.round((total - hours) * 60);
	const timeText = `${hours} hours, ${mins} mins`;
	
	document.getElementById('total_time_spent').value = timeText;
}

function calculateCompletionRatio() {
	const assignedTitles = Array.from(document.querySelectorAll('input[name="assigned_tasks_title[]"]')).map(input => input.value.trim().toLowerCase());
	
	// Get planner status by checking each checkbox in order (not by data-index)
	const assignedCheckboxes = document.querySelectorAll('.assigned-planner-check');
	const assignedPlanners = Array.from(assignedCheckboxes).map(checkbox => checkbox.checked);

	const plannedTasks = assignedTitles
		.map((title, idx) => ({ title, isPlanner: assignedPlanners[idx] || false }))
		.filter(task => task.isPlanner && task.title)
		.map(task => task.title);

	const totalPlanned = plannedTasks.length;
	let plannedCompleted = 0;
	let unplannedCompleted = 0;

	document.querySelectorAll('input[name="completed_tasks_title[]"]').forEach(input => {
		const title = input.value.trim().toLowerCase();
		if (!title) return;
		if (plannedTasks.includes(title)) {
			plannedCompleted += 1;
		} else {
			unplannedCompleted += 1;
		}
	});

	let ratio = 0;
	if (totalPlanned > 0) {
		ratio = (100 / totalPlanned) * (plannedCompleted + unplannedCompleted);
	}

	ratio = Math.min(ratio, 300);
	document.getElementById('calculated_work_ratio').value = `${ratio.toFixed(2)}%`;
	document.querySelector('input[name="completion_ratio"]').value = ratio.toFixed(2);
}

let assignedTaskIndex = 1;
function addAssignedTaskRow() {
    const container = document.getElementById("assigned_task_list");
    // Get the current number of rows to determine the correct index
    const currentRows = container.querySelectorAll('.task-row');
    const newIndex = currentRows.length;
    
    const html = `
    <div class="row mb-3 task-row" id="assigned_task_${newIndex}" style="padding-bottom: 10px;">
        <div class="col-md-4" style="padding-right: 2px;">
            <input type="text" class="form-control" name="assigned_tasks_title[]" placeholder="Task Title" oninput="calculateCompletionRatio()">
        </div>
        <div class="col-md-4" style="padding-right: 2px;">
            <input type="text" class="form-control" name="assigned_tasks_link[]" placeholder="Task Link">
        </div>
        <div class="col-md-2" style="padding-right: 2px;">
        <div class="form-check mt-2">
            <input type="checkbox" class="form-check-input assigned-planner-check" name="assigned_tasks_planner_${newIndex}" value="1" data-index="${newIndex}" onchange="calculateCompletionRatio()">
            <label class="form-check-label small">Planner</label>
        </div>
    </div>
        <div class="col-md-2 text-right">
            <button type="button" class="btn btn-danger btn-md" onclick="event.stopPropagation(); removeAssignedTask(${newIndex})">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    assignedTaskIndex = Math.max(assignedTaskIndex, newIndex + 1);
}

function removeAssignedTask(index) {
	const list = document.getElementById("assigned_task_list");
	const rows = list.querySelectorAll('.task-row');
	if (rows.length <= 1) return alert("At least one assigned task is required.");
	const row = document.getElementById(`assigned_task_${index}`);
	if (row) row.remove();
	calculateCompletionRatio();
}

let completedTaskIndex = 1;

function addCompletedTaskRow() {
    const container = document.getElementById("completed_task_list");
    const html = `
    <div class="row mb-3 task-row" id="completed_task_${completedTaskIndex}" style="padding-bottom: 10px;">
        <div class="col-md-4" style="padding-right: 2px;">
            <input type="text" class="form-control" name="completed_tasks_title[]" placeholder="Completed Title" oninput="calculateCompletionRatio()">
        </div>
        <div class="col-md-4" style="padding-right: 2px;">
            <input type="text" class="form-control" name="completed_tasks_link[]" placeholder="Proof Link">
        </div>
        <div class="col-md-2" style="padding-right: 2px;">
            <input type="text" class="form-control completed-time-input" name="completed_tasks_time[]" placeholder="Hours Spent" oninput="calculateTotalTimeSpent()">
        </div>
        <div class="col-md-2 text-right">
            <button type="button" class="btn btn-danger btn-md" onclick="event.stopPropagation(); removeCompletedTask(${completedTaskIndex})">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>`;
    container.insertAdjacentHTML('beforeend', html);
    completedTaskIndex++;
    calculateCompletionRatio();
}


function removeCompletedTask(index) {
	/* const list = document.getElementById("completed_task_list");
	const rows = list.querySelectorAll('.task-row');
	if (rows.length <= 1) return alert("At least one completed task is required."); */
	const row = document.getElementById(`completed_task_${index}`);
	if (row) row.remove();
	calculateTotalTimeSpent();
	calculateCompletionRatio();
}

// Handle form submission to collect checkbox states
function handleFormSubmission(form) {
	// Remove any existing planner inputs
	const existingInputs = form.querySelectorAll('input[name="assigned_tasks_planner[]"]');
	existingInputs.forEach(input => input.remove());
	
	// Get all assigned task rows
	const taskRows = document.querySelectorAll('#assigned_task_list .task-row');
	let taskIndex = 0;
	
	taskRows.forEach((row, index) => {
		const titleInput = row.querySelector('input[name="assigned_tasks_title[]"]');
		if (titleInput && titleInput.value.trim()) {
			const checkbox = row.querySelector('.assigned-planner-check');
			const isChecked = checkbox ? checkbox.checked : false;
			
			// Create hidden input for this task's planner status
			const hiddenInput = document.createElement('input');
			hiddenInput.type = 'hidden';
			hiddenInput.name = 'assigned_tasks_planner[]';
			hiddenInput.value = isChecked ? '1' : '0';
			form.appendChild(hiddenInput);
			
			taskIndex++;
		}
	});
	
	return true; // Allow form submission
}
</script>


<script>
function toggleNotificationDropdown() {
	const dropdown = document.getElementById('notification-dropdown');
	dropdown.classList.toggle('show');

	if (dropdown.classList.contains('show')) {
		// Fetch only if opening
		$.getJSON(base_url + 'dashboard/get_user_notifications', function(data) {
			let html = '';
			if (data.length === 0) {
				html = '<div class="notification-item">No new notifications</div>';
			} else {
				data.forEach(n => {
					const isUnread = n.is_read == 0 ? 'unread' : '';
					html += `<div class="notification-item ${isUnread}" id="notification-item-${n.id}" onclick="markAsRead(${n.id}, '${n.url}')">
								<strong>${n.title}</strong><br>
								<small>${n.message}</small>
							</div>`;
				});
			}
			$('#notification-dropdown').html(html);
		});
	}
}

// 🔹 Close on ESC
document.addEventListener('keydown', function(event) {
	if (event.key === "Escape") {
		document.getElementById('notification-dropdown').classList.remove('show');
	}
});

// 🔹 Close on outside click
document.addEventListener('click', function(event) {
	const dropdown = document.getElementById('notification-dropdown');
	const notificationWrapper = document.querySelector('.notification-wrapper');

	// Close if clicking outside notification area
	if (!notificationWrapper.contains(event.target) && !dropdown.contains(event.target)) {
		dropdown.classList.remove('show');
	}
});

// 🔹 Mark as read
function markAsRead(id, url) {
	$.post(base_url + 'dashboard/mark_as_read/' + id, function() {
		$(`#notification-item-${id}`).removeClass('unread');

		// Remove red dot if all read
		if ($('.notification-item.unread').length === 0) {
			$('.notification-dot').remove();
		}

		// Optionally redirect
		if (url && url !== null && url !== 'null') {
			window.location.href = url;
		} else {
			//location.reload(); // Refresh current page
		}

	});
}

// 🔁 Auto-refresh every 60s
window.refreshNotifications = function() {
	// Only run if page is fully loaded and we're on dashboard
	if (document.readyState === 'complete' && window.location.pathname !== '/authentication') {
		$.getJSON(base_url + 'dashboard/get_user_notifications', function(data) {
			const unread = data.filter(n => !n.is_read).length;
			if (unread > 0 && $('.notification-dot').length === 0) {
				$('.notification-bell').append('<span class="notification-dot"></span>');
			} else if (unread === 0) {
				$('.notification-dot').remove();
			}
		});
	}
};

window.refreshTaskSummary = function() {
	fetch('<?= base_url("dashboard/get_staff_tasks") ?>?date=<?= date("Y-m-d") ?>')
		.then(res => res.json())
		.then(data => {
			if (typeof populateAssignedTasks === 'function') {
				populateAssignedTasks(data.assigned);
				populateCompletedTasks(data.completed);
				updateSummaryStats();
			}
		});
};

window.triggerTopbarUpdate = function() {
	refreshNotifications();
	refreshTaskSummary();
};

setInterval(() => {
	refreshNotifications();
	//refreshTaskSummary();
}, 10000);

// Delay initial notification load to prevent login redirect interference
setTimeout(refreshNotifications, 1000);
</script>


<style>
    #modal_breakControl {
        max-width: 90% !important;
        width: 700px !important; /* Default base width */
    }

    @media (max-width: 992px) {
        #modal_breakControl {
            width: 80% !important;
        }
    }

    @media (max-width: 576px) {
        #modal_breakControl {
            width: 95% !important;
        }
    }
</style>


<div class="zoom-anim-dialog modal-block modal-block-lg mfp-hide payroll-t-modal" id="modal_breakControl">
    <section class="panel">
        <header class="panel-heading">
            <h4 class="panel-title">
                <i class="fas fa-bars"></i>
                <?php echo translate('Break') . " " . translate('control'); ?>
            </h4>
        </header>
        <div class="panel-body">
            <div id="quick_view_tray">
                <!-- Dynamic break content will be loaded here -->
            </div>
        </div>
        <footer class="panel-footer">
            <div class="row">
                <div class="col-md-offset-1 col-md-12">
                    <button class="btn btn-default modal-dismiss"><?php echo translate('close'); ?></button>
                </div>
            </div>
        </footer>
    </section>
</div>

<script>
function get_break_details(id) {
    $.ajax({
        url: base_url + 'employee/get_break_details',
        type: 'POST',
        data: {
            'id': id
        },
        dataType: "html",
        success: function (data) {
            $('#quick_view_tray').html(data);
            mfp_modal('#modal_breakControl');
        }
    });
}

// Fix for modal close buttons
$(document).ready(function() {
    // Ensure modal-dismiss buttons work properly
    $(document).off('click', '.modal-dismiss').on('click', '.modal-dismiss', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $.magnificPopup.close();
    });
});
</script>

<!-- Marked.js for Markdown rendering -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

<?php
  $user_id = get_loggedin_user_id();
  $staff_info = $this->db->get_where('staff', ['id' => $user_id])->row();
  $user_name = $staff_info ? $staff_info->name : 'Unknown';
  $session_id = session_id();
?>

<style>
.modal-block.modal-block-lg {
    max-width: 95%;
}
.modal-block {
    margin: 20px auto;
}
</style>

<!-- Issue Modal -->
<div id="IssueModal" class="zoom-anim-dialog modal-block mfp-hide modal-block-lg">
    <section class="panel">
        <div class="panel-heading">
             <h4 class="panel-title"><i class="fas fa-plus-circle"></i> <?= translate('add_new_issue') ?></h4>
        </div>
        <?php echo form_open_multipart('tracker/save_issue', [
            'class' => 'form-horizontal', 
            'method' => 'POST',
            'id' => 'issueForm'
        ]); ?>
        <div class="panel-body">
            
          <!-- Department and Category -->
			<div class="row form-group">
				<div class="col-md-6">
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fas fa-building"></i>
						</span>
						<?php
							$department_options = ['' => translate('select_department')];
							foreach ($this->db->get('tracker_departments')->result() as $depts) {
								$department_options[$depts->identifier] = $depts->title;
							}
							echo form_dropdown(
								'department',
								$department_options,
								'',
								"class='form-control' id='departmentSelect' required 
								 data-plugin-selectTwo 
								 data-placeholder='".translate('select_department')."'
								 data-width='100%'"
							);
						?>
					</div>
					<span class="error"><?= form_error('department') ?></span>
				</div>
				<div class="col-md-6">
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fas fa-list-alt"></i>
						</span>
						<select name="category" class="form-control" required data-plugin-selectTwo data-width="100%">
							<option value="Milestone">Milestone</option>
							<option value="Incident">Incident</option>
							<option value="Customer Query">Customer Query</option>
							<option value="Explore">Explore</option>
							<option value="EMP Request">EMP Request</option>
						</select>
					</div>
					<span class="error"><?= form_error('category') ?></span>
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
							   name="task_title" 
							   placeholder="<?= translate('enter_title') ?>" 
							   required>
					</div>
					<span class="error"><?= form_error('task_title') ?></span>
				</div>
			</div>

            <!-- Description -->
            <div class="row form-group">
                <div class="col-md-12">
                    <textarea name="task_description" id="task_description" class="summernote" required ></textarea>
                    <span class="error"><?= form_error('task_description') ?></span>
                </div>
            </div>

            <!-- Customer Query Fields (shown only when category is Customer Query) -->
			<div id="customerQueryFields" style="display: none;">
				<div class="row form-group">
					<div class="col-md-4">
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fas fa-link"></i>
							</span>
							<select name="source" class="form-control" data-plugin-selectTwo data-width="100%" id="sourceSelect">
								<option value="">Select Source</option>
								<option value="Email">Email</option>
								<option value="Phone">Phone</option>
								<option value="Chat">Chat</option>
								<option value="Portal">Portal</option>
								<option value="Other">Other</option>
							</select>
						</div>
						<span class="error"><?= form_error('source') ?></span>
					</div>
					<div class="col-md-4">
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fas fa-address-book"></i>
							</span>
							<input type="text" name="contact_info" class="form-control" placeholder="Contact Information (Email/Phone)">
						</div>
						<span class="error"><?= form_error('contact_info') ?></span>
					</div>
					<div class="col-md-4">
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fas fa-calendar-alt"></i>
							</span>
							<input type="datetime-local" name="requested_at" class="form-control" placeholder="Requested At">
						</div>
						<span class="error"><?= form_error('requested_at') ?></span>
					</div>
				</div>
				<div class="row form-group">
					<div class="col-md-12">
						<label>Request/Mail Body:</label>
						<textarea name="request_body" class="form-control" rows="4" placeholder="Enter the original request or mail body..."></textarea>
						<span class="error"><?= form_error('request_body') ?></span>
					</div>
				</div>
			</div>
            <br>
            <!-- Status, Priority, Assignee, Component, Estimation, Milestone, -->
            <div class="row form-group">
               <!-- Status -->
				<div class="col-md-3">
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fas fa-tasks"></i>
						</span>
						<select name="task_status" class="form-control" required data-plugin-selectTwo data-width="100%">
							<?php 
							$statuses = [
								//'' => translate('status'),
								'todo' => translate('to-do'),
								'in_progress' => translate('in_progress'),
								'in_review' => translate('in_review'),
								'submitted' => translate('submitted'),
								'planning' => translate('planning'),
								'observation' => translate('observation'),
								'waiting' => translate('waiting'),
								'completed' => translate('completed'),
								'backlog' => translate('Backlog'),
								'hold' => translate('Hold'),
								'solved' => translate('solved'),
								'canceled' => translate('canceled')
							];
							foreach ($statuses as $value => $label): ?>
								<option value="<?= $value ?>"><?= $label ?></option>
							<?php endforeach; ?>
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
						<select name="priority_level" class="form-control" data-plugin-selectTwo data-width="100%">
							<?php 
							$priorities = [
								//'' => translate('priority'),
								'Medium' => translate('Medium'),
								'Low' => translate('Low'),
								'High' => translate('High'),
								'Urgent' => translate('Urgent')
							];
							foreach ($priorities as $value => $label): ?>
								<option value="<?= $value ?>"><?= $label ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<span class="error"><?= form_error('priority_level') ?></span>
				</div>

				<!-- Assignee -->
				<div class="col-md-2">
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fas fa-user"></i>
						</span>
						<?php
						$this->db->select('s.id, s.name');
						$this->db->from('staff AS s');
						$this->db->join('login_credential AS lc', 'lc.user_id = s.id');
						$this->db->where('lc.active', 1);   // only active users
						$this->db->where_not_in('lc.role', [1, 9, 10, 11, 12]);   // exclude super admin, etc.
						$this->db->where_not_in('s.id', [49]);   // exclude super admin, etc.
						$this->db->order_by('s.name', 'ASC');
						$query = $this->db->get();

						$staffArray = ['' => 'Select']; // <-- default first option
						foreach ($query->result() as $row) {
							$staffArray[$row->id] = $row->name;
						}
						
						// Get logged-in user staff_id (adjust session key if different)
						$logged_in_user = get_loggedin_user_id();;

						echo form_dropdown(
							"assigned_to",
							$staffArray,
							$logged_in_user, // Default selected value
							"class='form-control' required
							data-plugin-selectTwo
							data-placeholder='".translate('assign_to')."'
							data-width='100%'"
						);
						?>
					</div>

					<span class="error"><?= form_error('assigned_to') ?></span>
				</div>

				<!-- Coordinator -->
				<div class="col-md-2">
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fas fa-user-tie"></i>
						</span>
						<?php
						// Get default coordinator based on logged-in user's role
						$default_coordinator = '';
						$logged_in_role = loggedin_role_id();
						$logged_in_dept = $this->db->select('department')->from('staff')->where('id', $logged_in_user)->get()->row();
						
						if ($logged_in_role == 3) {
							// Role 3 coordinator is role 3 (self)
							$default_coordinator = $logged_in_user;
						} elseif ($logged_in_role == 4 && $logged_in_dept) {
							// Find role 8 from same department, fallback to role 3
							$coordinator = $this->db->select('s.id')
								->from('staff s')
								->join('login_credential lc', 'lc.user_id = s.id')
								->where('lc.role', 8)
								->where('s.department', $logged_in_dept->department)
								->where('lc.active', 1)
								->get()->row();
							if ($coordinator) {
								$default_coordinator = $coordinator->id;
							} else {
								// Fallback to role 3
								$coordinator = $this->db->select('s.id')
									->from('staff s')
									->join('login_credential lc', 'lc.user_id = s.id')
									->where('lc.role', 3)
									->where('lc.active', 1)
									->get()->row();
								if ($coordinator) $default_coordinator = $coordinator->id;
							}
						} elseif (in_array($logged_in_role, [5, 8])) {
							// Find role 3
							$coordinator = $this->db->select('s.id')
								->from('staff s')
								->join('login_credential lc', 'lc.user_id = s.id')
								->where('lc.role', 3)
								->where('lc.active', 1)
								->get()->row();
							if ($coordinator) $default_coordinator = $coordinator->id;
						}
						
						echo form_dropdown(
							"coordinator",
							$staffArray,
							$default_coordinator,
							"class='form-control' required
							data-plugin-selectTwo
							data-placeholder='Select Coordinator'
							data-width='100%'"
						);
						?>
					</div>
					<span class="error"><?= form_error('coordinator') ?></span>
				</div>

				<!-- Component -->
				<div class="col-md-2">
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fas fa-puzzle-piece"></i>
						</span>
						<?php
							$tsk_component = ['' => translate('initiatives')];
							foreach ($this->db->get('tracker_components')->result() as $task_component) {
								$tsk_component[$task_component->id] = $task_component->title;
							}
							echo form_dropdown(
								'component',
								$tsk_component,
								'',
								"class='form-control' data-plugin-selectTwo data-width='100%'"
							);
						?>
					</div>
					<span class="error"><?= form_error('component') ?></span>
				</div>
            </div>

            
            <!--  Labels, Due Date, Parent -->
            <div class="row form-group">
                
				<!-- Labels -->
				<div class="col-md-6">
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fas fa-tags"></i>
						</span>
						<?php
							echo form_dropdown(
								"label[]", 
								$this->app_lib->getSelectList('task_labels'), 
								[], 
								"class='form-control' multiple required 
								 data-plugin-selectTwo 
								 data-placeholder='".translate('select_labels')."' 
								 data-width='100%'"
							);
						?>
					</div>
					<span class="error"><?= form_error('label[]') ?></span>
				</div>
				<!-- Estimation -->
				<div class="col-md-3">
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fas fa-clock"></i>
						</span>
						<input type="number" step="0.1" name="estimation_time" class="form-control" 
							   placeholder="<?= translate('estimated_time') ?>..." required />
					</div>
					<span class="error"><?= form_error('estimation_time') ?></span>
				</div>
				
				<!-- Due Date -->
				<div class="col-md-3">
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fas fa-calendar-alt"></i>
						</span>
						<input type="date" 
							   name="estimated_end_time" 
							   class="form-control"
							   placeholder="<?= translate('select_due_date') ?>"
							   min="<?= date('Y-m-d') ?>"
							   onfocus="(this.type='date')"
							   onblur="if(!this.value)this.type='text'" required>
					</div>
					<span class="error"><?= form_error('estimated_end_time') ?></span>
				</div>
				
            </div>

            
            <!--  Labels, Due Date, Parent -->
            <div class="row form-group">
				<!-- task types -->
				<div class="col-md-3">
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa-solid fa-database"></i>
						</span>
						<?php
							$task_type = ['' => translate('task_types')];
							$task_type += $this->app_lib->getSelectList('task_types');
							echo form_dropdown(
								'task_type',
								$task_type,
								'',
								"class='form-control' required data-plugin-selectTwo data-width='100%'"
							);
						?>
					</div>
					<span class="error"><?= form_error('task_type') ?></span>
				</div>
				<!-- Milestone -->
				<div class="col-md-3">
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fas fa-flag-checkered"></i>
						</span>
						<?php
							$tsk_milestone = ['' => translate('milestone')];
							/* foreach ($this->db->get('tracker_milestones')->result() as $task_milestone) {
								$tsk_milestone[$task_milestone->id] = $task_milestone->title;
							} */
							$tsk_milestone += $this->app_lib->getSelectList_v2('tracker_milestones', '', ['status' => 'in_progress']);
							echo form_dropdown(
								'milestone',
								$tsk_milestone,
								'',
								"class='form-control' id='milestone_select' required data-plugin-selectTwo data-width='100%'"
							);
						?>
					</div>
					<span class="error"><?= form_error('milestone') ?></span>
				</div>
				<!-- Task Filter -->
				<div class="col-md-3">
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fas fa-filter"></i>
						</span>
						<select id="task_filter" class="form-control" data-plugin-selectTwo data-width="100%">
							<option value="all">All Task</option>
							<option value="main">Main Task</option>
							<option value="sub">Sub Task</option>
						</select>
					</div>
				</div>
				<!-- Parent Task -->
				<div class="col-md-3">
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fas fa-level-up-alt"></i>
						</span>
						<select name="parent_issue" id="parent_task_select" class="form-control" data-plugin-selectTwo data-width="100%">
							<option value="">Please select milestone first</option>
						</select>
					</div>
					<span class="error"><?= form_error('parent_issue') ?></span>
				</div>
			</div>
			
			<div class="row form-group">
				
				<!-- SOP IDs -->
				<div class="col-md-12">
					<div class="input-group">
						<span class="input-group-addon">
							 <i class="fas fa-file-alt"></i>
						</span>
						 <?php
                            $sop_options = [];
                            $sops = $this->db->select('id, title')
                                            ->from('sop')
                                            ->get()->result();
                            foreach ($sops as $sop) {
                                $sop_options[$sop->id] = $sop->title;
                            }
                            echo form_dropdown(
                                'sop_ids[]',
                                $sop_options,
                                '',
                                "class='form-control' multiple
								 data-plugin-selectTwo 
                                 data-placeholder='Select SOP IDs'
                                 data-width='100%'"
                            );
                        ?>
                    </div>
                    <span class="error"><?= form_error('sop_ids[]') ?></span>
				</div>

            </div>

        </div>
        <footer class="panel-footer">
            <div class="row">
                <div class="col-md-12 text-right">
                    <button type="submit" class="btn btn-default mr-xs" id="savebtn">
                        <i class="fas fa-plus-circle"></i> <?=translate('apply')?>
                    </button>
                    <button type="button" class="btn btn-default modal-dismiss"><?=translate('cancel')?></button>
                </div>
            </div>
        </footer>
        <?php echo form_close();?>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('issueForm');
    const saveBtn = document.getElementById('savebtn');
    
    form.addEventListener('submit', function(e) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing';
        
        // Re-enable after 5 seconds if still on page
        setTimeout(() => {
            if (!saveBtn.disabled) return;
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-plus-circle"></i> <?=translate('apply')?>';
        }, 5000);
    });
    
    // Additional fix for modal close buttons
    $(document).off('click', '.modal-dismiss').on('click', '.modal-dismiss', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $.magnificPopup.close();
    });
});
</script>


<!-- Inline CSS Links -->
<link rel="stylesheet" href="<?= base_url('assets/vendor/dropify/css/dropify.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/vendor/summernote/summernote.css') ?>">
<!-- Inline JS Scripts -->
<script src="<?= base_url('assets/vendor/dropify/js/dropify.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/moment/moment.js') ?>"></script>
<script src="<?= base_url('assets/vendor/summernote/summernote.js') ?>"></script>


<script type="text/javascript">

	$(document).ready(function () {
		// Initialize Summernote
		$('.summernote').summernote({
			height: 50,
			dialogsInBody: true,
			toolbar: [
				['style', ['bold', 'italic', 'underline', 'clear']],
				['font', ['fontsize']],
				['para', ['ul', 'ol', 'paragraph']],
				['insert', ['link']],
				['view', ['fullscreen', 'codeview']]
			]
		});

		// Auto-select department based on user's department
		<?php 
		$user_id = get_loggedin_user_id();
		$user_dept = $this->db->select('sd.name as dept_name')
							->from('staff s')
							->join('staff_department sd', 's.department = sd.id', 'left')
							->where('s.id', $user_id)
							->get()->row();
		if ($user_dept && $user_dept->dept_name): ?>
		var userDeptName = '<?= strtolower($user_dept->dept_name) ?>';
		var bestMatch = '';
		var bestScore = 0;
		
		$('#departmentSelect option').each(function() {
			var optionText = $(this).text().toLowerCase();
			if (optionText && optionText !== 'select department') {
				var score = calculateSimilarity(userDeptName, optionText);
				if (score >= 0.5 && score > bestScore) {
					bestScore = score;
					bestMatch = $(this).val();
				}
			}
		});
		
		if (bestMatch) {
			$('#departmentSelect').val(bestMatch).trigger('change');
		}
		<?php endif; ?>

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

		// Auto-generate identifier from title
		$('#deptTitle').on('input', function() {
			const title = $(this).val();
			const identifier = title.substring(0, 4).toUpperCase();
			$('#deptIdentifier').val(identifier);
		});

		// Milestone-Task dependency
		$('#parent_task_select').prop('disabled', true);
		
		// Handle category changes for milestone, SOP and customer query fields
		$('select[name="category"]').on('change', function() {
			var selectedCategory = $(this).val();
			var milestoneSelect = $('#milestone_select');
			var sopSelect = $('select[name="sop_ids[]"]');
			var customerQueryFields = $('#customerQueryFields');
			
			// Update milestone options based on category
			if (selectedCategory === 'EMP Request' || selectedCategory === 'Incident') {
				// Load all milestones for EMP Request and Incident
				loadAllMilestones();
				milestoneSelect.prop('required', false);
				sopSelect.prop('required', false);
			} else if (selectedCategory === 'Milestone') {
				// Load only in_progress milestones for Milestone category
				loadInProgressMilestones();
				milestoneSelect.prop('required', true);
				sopSelect.prop('required', true);
			} else {
				// Load only in_progress milestones for other categories
				loadInProgressMilestones();
				milestoneSelect.prop('required', false);
				sopSelect.prop('required', false);
				milestoneSelect.val('').trigger('change');
			}
			
			// Show/hide customer query fields
			if (selectedCategory === 'Customer Query') {
				customerQueryFields.show();
				$('#sourceSelect').prop('required', true);
				$('input[name="contact_info"], textarea[name="request_body"]').prop('required', true);
			} else {
				customerQueryFields.hide();
				$('#sourceSelect').prop('required', false);
				$('input[name="contact_info"], textarea[name="request_body"]').prop('required', false);
			}
		});
		
		// Initialize milestone and SOP requirement and options based on default category
		var initialCategory = $('select[name="category"]').val();
		var sopSelect = $('select[name="sop_ids[]"]');
		if (initialCategory === 'Milestone') {
			$('#milestone_select').prop('required', true);
			sopSelect.prop('required', true);
			loadInProgressMilestones();
		} else if (initialCategory === 'EMP Request' || initialCategory === 'Incident') {
			$('#milestone_select').prop('required', false);
			sopSelect.prop('required', false);
			loadAllMilestones();
		} else {
			$('#milestone_select').prop('required', false);
			sopSelect.prop('required', false);
			loadInProgressMilestones();
		}
		
		// Initialize customer query fields visibility
		if (initialCategory === 'Customer Query') {
			$('#customerQueryFields').show();
			$('#sourceSelect').prop('required', true);
			$('input[name="contact_info"], textarea[name="request_body"]').prop('required', true);
		}
		
		// Functions to load milestone options
		function loadAllMilestones() {
			$.ajax({
				url: '<?= base_url("tracker/get_all_milestones") ?>',
				type: 'POST',
				dataType: 'json',
				success: function(data) {
					var milestoneSelect = $('#milestone_select');
					milestoneSelect.empty();
					milestoneSelect.append('<option value="">Select Milestone</option>');
					$.each(data, function(key, value) {
						milestoneSelect.append('<option value="'+ key +'">'+ value +'</option>');
					});
					milestoneSelect.trigger('change');
				},
				error: function() {
					console.error('Failed to load all milestones');
				}
			});
		}
		
		function loadInProgressMilestones() {
			$.ajax({
				url: '<?= base_url("tracker/get_in_progress_milestones") ?>',
				type: 'POST',
				dataType: 'json',
				success: function(data) {
					var milestoneSelect = $('#milestone_select');
					milestoneSelect.empty();
					milestoneSelect.append('<option value="">Select Milestone</option>');
					$.each(data, function(key, value) {
						milestoneSelect.append('<option value="'+ key +'">'+ value +'</option>');
					});
					milestoneSelect.trigger('change');
				},
				error: function() {
					console.error('Failed to load in-progress milestones');
				}
			});
		}
		
		function loadTasks() {
			var milestoneId = $('#milestone_select').val();
			var taskFilter = $('#task_filter').val();
			
			if (milestoneId) {
				$('#parent_task_select').prop('disabled', false);
				$.ajax({
					url: '<?= base_url("tracker/get_tasks_by_milestone") ?>',
					type: 'POST',
					data: { 
						milestone_id: milestoneId,
						filter: taskFilter
					},
					dataType: 'json',
					success: function(data) {
						$('#parent_task_select').empty();
						if (Object.keys(data).length > 0) {
							$('#parent_task_select').append('<option value="">Select Parent Task</option>');
							$.each(data, function(key, value) {
								$('#parent_task_select').append('<option value="'+ key +'">'+ value +'</option>');
							});
						} else {
							$('#parent_task_select').append('<option value="">No tasks available for this milestone</option>');
						}
						$('#parent_task_select').trigger('change');
					},
					error: function() {
						$('#parent_task_select').empty();
						$('#parent_task_select').append('<option value="">Error loading tasks</option>');
					}
				});
			} else {
				$('#parent_task_select').prop('disabled', true);
				$('#parent_task_select').empty();
				$('#parent_task_select').append('<option value="">Please select milestone first</option>');
			}
		}
		
		$('#milestone_select').on('change', loadTasks);
		$('#task_filter').on('change', loadTasks);
		
		// Coordinator selection based on assigned person's role
		$('select[name="assigned_to"]').on('change', function() {
			var assignedUserId = $(this).val();
			if (assignedUserId) {
				updateCoordinator(assignedUserId);
			} else {
				$('select[name="coordinator"]').val('').trigger('change');
			}
		});
		
		function updateCoordinator(assignedUserId) {
			$.ajax({
				url: '<?= base_url("tracker/get_coordinator_by_role") ?>',
				type: 'POST',
				data: { assigned_user_id: assignedUserId },
				dataType: 'json',
				success: function(response) {
					if (response.success && response.coordinator_id) {
						$('select[name="coordinator"]').val(response.coordinator_id).trigger('change');
					} else {
						$('select[name="coordinator"]').val('').trigger('change');
					}
				},
				error: function() {
					console.error('Failed to get coordinator');
					$('select[name="coordinator"]').val('').trigger('change');
				}
			});
		}

	});

	// Function to calculate string similarity
	function calculateSimilarity(str1, str2) {
		if (str1 === str2) return 1;
		if (str1.length === 0 || str2.length === 0) return 0;
		
		// Check if one string contains the other
		if (str1.includes(str2) || str2.includes(str1)) {
			return Math.max(str2.length / str1.length, str1.length / str2.length);
		}
		
		// Simple word matching
		var words1 = str1.split(' ');
		var words2 = str2.split(' ');
		var matches = 0;
		
		for (var i = 0; i < words1.length; i++) {
			for (var j = 0; j < words2.length; j++) {
				if (words1[i] === words2[j] || words1[i].includes(words2[j]) || words2[j].includes(words1[i])) {
					matches++;
					break;
				}
			}
		}
		
		return matches / Math.max(words1.length, words2.length);
	}
</script> 