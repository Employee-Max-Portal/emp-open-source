<style>
/* Professional Sidebar Styling */
.tracker-sidebar {
    background: #ffffff;
    border-right: 1px solid #e5e7eb;
    height: calc(112vh - 60px); /* Adjusted height to account for any header */
    flex-shrink: 0;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
    display: flex;
    flex-direction: column;
}

.sidebar-scroll-container {
    overflow-y: auto;
    flex-grow: 1;
    height: 100%;
}

.sidebar-header {
    padding: 20px 16px;
    color: #777;
    border-bottom: 1px solid #e5e7eb;
    flex-shrink: 0;
}

.sidebar-title {
    font-size: 18px;
    font-weight: 700;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.new-issue-btn {
    background: linear-gradient(135deg, #36a436 0%, #36a436 100%);
    border: none;
    border-radius: 10px;
    padding: 12px 16px;
    color: white;
    font-weight: 600;
    font-size: 14px;
    width: 100%;
    margin: 16px 0;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
    text-decoration: none;
    display: inline-block;
    text-align: center;
    text-decoration: none!important;
}

a{
    text-decoration: none!important;
}

.new-issue-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
    color: white!important;
    text-decoration: none!important;
}

.nav-section {
    margin: 10px;
    flex-shrink: 0;
}

.nav-section-title {
    font-size: 12px;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    padding: 12px 16px 8px;
    margin-bottom: 5px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
    border-bottom: 1px solid #f3f4f6;
}

.nav-pills {
    padding: 0;
    margin: 0;
    list-style: none;
}

.nav-pills li {
    margin: 0;
}

.nav-pills li a {
    padding: 12px 20px;
    margin: 0;
    color: #374151;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
    display: flex;
    align-items: center;
    text-decoration: none;
}

.nav-pills li a:hover {
    background: #f3f4f6;
    color: #1f2937!important;
    border-left-color: #e5e7eb;
    text-decoration: none;
}

.nav-pills li.active a {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    color: #1e40af;
    border-left-color: #3b82f6;
    font-weight: 600;
}

.nav-pills li a i {
    width: 20px;
    margin-right: 12px;
    text-align: center;
    font-size: 16px;
}

.sidebar-divider {
    height: 1px;
    background: #f3f4f6;
    margin: 12px 16px;
    border: none;
}

.department-toggle {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}

.department-toggle-header {
    padding: 12px 16px;
    background: white;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.department-toggle-header:hover {
    background: #f8fafc;
}

.department-toggle-title {
    font-weight: 600;
    color: #374151;
    font-size: 14px;
    text-decoration: none;
    display: flex;
    align-items: center;
}

.department-toggle-title:hover {
    text-decoration: none;
    color: #374151;
}

.add-dept-btn {
    background: #10b981;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.add-dept-btn:hover {
    background: #059669;
    transform: scale(1.05);
}

.department-content {
    transition: all 0.3s ease;
    overflow: hidden;
}

.department-content.collapsed {
    max-height: 0;
    opacity: 0;
}

.department-content.expanded {
    opacity: 1;
}

.chevron-icon {
    transition: transform 0.2s ease;
}

.chevron-icon.rotated {
    transform: rotate(180deg);
}

.department-item {
    background: white;
    margin: 8px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.department-title {
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    padding: 10px 16px;
    font-weight: 600;
    color: #475569;
    font-size: 13px;
    border-bottom: 1px solid #e2e8f0;
}

.department-links {
    padding: 4px 0;
}

.department-links .nav-pills li a {
    padding: 8px 20px;
    font-size: 13px;
    color: #6b7280;
}

.department-links .nav-pills li a:hover {
    background: #f9fafb;
    color: #374151;
}

.department-links .nav-pills li.active a {
    background: #eff6ff;
    color: #2563eb;
    border-left-color: #3b82f6;
}

.no-departments {
    text-align: center;
    padding: 20px;
    color: #9ca3af;
    font-style: italic;
}

.add-department-link {
    text-align: center;
    padding: 16px;
    border-top: 1px solid #e5e7eb;
}

.add-department-link a {
    color: #3b82f6;
    font-weight: 500;
    text-decoration: none;
    font-size: 14px;
}

.add-department-link a:hover {
    color: #1d4ed8;
    text-decoration: none;
}

/* Ensure the panel takes full height */
.panel.mailbox {
    height: 108vh;
    display: flex;
    flex-direction: column;
}

.panel-body {
    flex-grow: 1;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
</style>

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

<?php
$url = '';
if ($this->input->get('type')) {
   $url = '?type=' . $this->input->get('type', true);
}
?>

<div class="panel mailbox">
    <div class="tracker-sidebar">
        <!-- Scrollable content container -->
        <div class="sidebar-scroll-container">
            <h4 class="nav-header text-bold" style="padding: 8px 15px; text-transform: uppercase; color: #999;"> Tracker</h4>
			
            <!-- Main Navigation -->
            <div class="nav-section">
                <ul class="nav nav-pills nav-stacked">
                    <li <?=$sub_page == 'tracker/my_issues' ? 'class="active"' : '';?>>
                        <a href="<?=base_url('tracker/my_issues')?>">
                            <i class="fas fa-crosshairs"></i>
                            <?=translate('my') . " " . translate('issues')?>
                        </a>
                    </li>
                    <li <?=$sub_page == 'tracker/my_coordination' ? 'class="active"' : '';?>>
                        <a href="<?=base_url('tracker/my_coordination')?>">
                            <i class="fas fa-user-tie"></i>
                            My Coordination
                        </a>
                    </li>
                    <li <?=$sub_page == 'tracker/all_issues' ? 'class="active"' : '';?>>
                        <a href="<?=base_url('tracker/all_issues')?>">
                            <i class="fas fa-tasks"></i>
                            <?=translate('all') . " " . translate('issues')?>
                        </a>
                    </li>
					<li <?=$sub_page == 'tracker/pending_approval' ? 'class="active"' : '';?>>
						<a href="<?=base_url('tracker/pending_approval')?>">
							<i class="fas fa-clock"></i>
							Pending Approval
							<?php
							$UserID = get_loggedin_user_id();
							$RoleId = loggedin_role_id();
							
							$this->db->where_in('approval_status', ['pending', 'declined']);
							
							if (!in_array($RoleId, [1, 2, 3, 5])) {
								$this->db->where('assigned_to', $UserID);
							}
							
							$pending_count = $this->db->count_all_results('tracker_issues');
							
							if ($pending_count > 0):
							?>
							<span class="badge badge-warning" style="background: #f59e0b; color: white; padding: 2px 6px; border-radius: 10px; font-size: 11px; margin-left: 8px;"><?= $pending_count ?></span>
							<?php endif; ?>
						</a>
                    </li>
                </ul>
            </div>

			
            <hr class="sidebar-divider">

            <!-- Planner Section -->
            <div class="nav-section">
                <h5 class="nav-section-title">
                    <i class="fas fa-calendar-alt" style="margin-right: 8px;"></i>Planner
                </h5>
                <ul class="nav nav-pills nav-stacked">
                    <li <?=$sub_page == 'planner/index' ? 'class="active"' : '';?>>
                        <a href="<?=base_url('planner' . $url)?>">
                            <i class="fas fa-calendar"></i>
                            <?=translate('my_planner')?>
                        </a>
                    </li>
                    <li <?=$sub_page == 'team_planner/index' ? 'class="active"' : '';?>>
                        <a href="<?=base_url('team_planner' . $url)?>">
                            <i class="fas fa-calendar-day"></i>
                            <?=translate('team_planner')?>
                        </a>
                    </li>
                </ul>
            </div>

            <hr class="sidebar-divider">

            <!-- Management Section -->
            <div class="nav-section">
                <h5 class="nav-section-title">
                    <i class="fas fa-cog" style="margin-right: 8px;"></i>Management
                </h5>
                <ul class="nav nav-pills nav-stacked">
                    <li <?=$sub_page == 'tracker/departments' ? 'class="active"' : '';?>>
                        <a href="<?=base_url('tracker/all_departments' . $url)?>">
                            <i class="fas fa-building"></i>
                            <?=translate('all_departments')?>
                        </a>
                    </li>
                    <li <?=$sub_page == 'tracker/labels' ? 'class="active"' : '';?>>
                        <a href="<?=base_url('tracker/labels' . $url)?>">
                            <i class="fas fa-tags"></i>
                            <?=translate('labels')?>
                        </a>
                    </li>
                    <li <?=$sub_page == 'tracker/initiatives' ? 'class="active"' : '';?>>
                        <a href="<?=base_url('tracker/initiatives' . $url)?>">
                            <i class="fa-solid fa-briefcase"></i>
                            <?=translate('initiatives')?>
                        </a>
                    </li>
                    <li <?=$sub_page == 'tracker/task_types' ? 'class="active"' : '';?>>
                        <a href="<?=base_url('tracker/task_types' . $url)?>">
                            <i class="fa-solid fa-database"></i>
                            <?=translate('task_type')?>
                        </a>
                    </li>
                </ul>
            </div>
			
            <hr class="sidebar-divider">
            <!-- Departments Section -->
            <div class="nav-section">
                <h5 class="nav-section-title">
                    <i class="fas fa-folder" style="margin-right: 8px;"></i>Departments
                </h5>
                
                <div class="department-toggle">
                    <div class="department-toggle-header" onclick="toggleDepartments()">
                        <div class="department-toggle-title">
                            <i class="fas fa-chevron-down chevron-icon" id="chevronIcon" style="margin-right: 8px;"></i>
                            <?= translate('your_departments') ?>
                        </div>
                        <button onclick="event.stopPropagation(); mfp_modal('#addDepartmentModal')" class="btn btn-primary btn-circle icon" title="<?= translate('add_new_department') ?>">
                            <i class="fas fa-plus" style="margin-right: 4px;"></i>
                        </button>
                    </div>
                    
                    <div id="projectList" class="department-content expanded">
                        <?php
                        $staff_id = get_loggedin_user_id();
                        $role_id = loggedin_role_id(); // Make sure this returns role_id (1 for admin)

                        if ($staff_id == 1 || $role_id == 1) {
                            // Admin/superuser: show all departments
                            $departments = $this->db->select('id, title, identifier')
                                ->from('tracker_departments')
                                ->get()
                                ->result();
                        } else {
                            // Regular user: show only joined or owned departments
                            $departments = $this->db->select('p.id, p.title, p.identifier')
                                ->from('tracker_departments p')
                                ->join('tracker_department_members pm', 'pm.department_id = p.id', 'left')
                                ->group_start()
                                    ->where('pm.staff_id', $staff_id)
                                    ->or_where('p.owner_id', $staff_id)
                                ->group_end()
                                ->group_by('p.id')
                                ->get()
                                ->result();
                        }

                        if (count($departments)):
                            foreach ($departments as $nav_dept):
                        ?>

                            <div class="department-item">
                                <div class="department-title">
                                    <i class="fas fa-folder-open" style="margin-right: 8px;"></i><?= $nav_dept->title ?>
                                </div>
                                <div class="department-links">
                                    <ul class="nav nav-pills nav-stacked">
                                        <li <?= ($sub_page == 'tracker/issues' && isset($active_identifier) && $active_identifier == $nav_dept->identifier) ? 'class="active"' : ''; ?>>
                                            <a href="<?= base_url('tracker/issue_tracker/' . $nav_dept->identifier) ?>">
                                                <i class="fas fa-bug"></i>
                                                <?= translate('issues') ?>
                                            </a>
                                        </li>
                                        <!--<li <?= ($sub_page == 'tracker/components' && isset($active_identifier) && $active_identifier == $nav_dept->identifier) ? 'class="active"' : ''; ?>>
                                            <a href="<?= base_url('tracker/initiatives/' . $nav_dept->identifier) ?>">
                                                <i class="fas fa-puzzle-piece"></i>
                                                <?= translate('initiatives') ?>
                                            </a>
                                        </li> -->
                                        <li <?= ($sub_page == 'tracker/milestones' && isset($active_identifier) && $active_identifier == $nav_dept->identifier) ? 'class="active"' : ''; ?>>
                                            <a href="<?= base_url('tracker/milestones/' . $nav_dept->identifier) ?>">
                                                <i class="fas fa-flag"></i>
                                                <?= translate('milestones') ?>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        <?php
                            endforeach;
                        else:
                        ?>
                            <div class="no-departments">
                                <i class="fas fa-folder-open" style="font-size: 24px; margin-bottom: 8px; color: #d1d5db;"></i>
                                <p><?= translate('no_projects_found') ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="add-department-link">
                            <a href="javascript:void(0);" onclick="mfp_modal('#addDepartmentModal')">
                                <i class="fas fa-plus-circle" style="margin-right: 8px;"></i>Add New Department
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- End of scrollable container -->
    </div>
</div>

<script>
function toggleDepartments() {
    const projectList = document.getElementById('projectList');
    const chevronIcon = document.getElementById('chevronIcon');
    
    if (projectList.classList.contains('expanded')) {
        projectList.classList.remove('expanded');
        projectList.classList.add('collapsed');
        chevronIcon.classList.add('rotated');
    } else {
        projectList.classList.remove('collapsed');
        projectList.classList.add('expanded');
        chevronIcon.classList.remove('rotated');
    }
}
</script>


<div id="addDepartmentModal" class="zoom-anim-dialog modal-block modal-block-primary mfp-hide">
    <section class="panel">
        <header class="panel-heading">
            <h4 class="panel-title"><i class="fas fa-plus-circle"></i> <?= translate('add_department') ?></h4>
        </header>

        <?php echo form_open('tracker/add_departments', [
            'class' => 'form-horizontal frm-submit',
            'method' => 'post'
        ]); ?>

        <div class="panel-body">
            <div class="form-group">
                <label class="col-md-3 control-label"><?= translate('title') ?> <span class="required">*</span></label>
                <div class="col-md-8">
                    <input type="text" class="form-control" name="title" id="deptTitle" required />
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label"><?= translate('identifier') ?> (ID)</label>
                <div class="col-md-4">
                    <input type="text" class="form-control" name="identifier" id="deptIdentifier" placeholder="e.g. TSK" />
                </div>
               
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label"><?= translate('about') ?></label><span class="required">*</span>
                <div class="col-md-8">
                    <textarea name="description" rows="2" class="form-control" placeholder="Write something..." required></textarea>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label"><?= translate('status') ?></label>
                <div class="col-md-8">
                    <select name="default_status" class="form-control">
                        <option value="todo"><?= translate('to-do') ?></option>
                        <option value="backlog"><?= translate('Backlog') ?></option>
                        <option value="hold"><?= translate('Hold') ?></option>
                        <option value="submitted"><?= translate('submitted') ?></option>
                        <option value="in_progress"><?= translate('in_progress') ?></option>
                        <option value="in_review"><?= translate('in_review') ?></option>
                        <option value="planning"><?= translate('planning') ?></option>
                        <option value="observation"><?= translate('observation') ?></option>
                        <option value="waiting"><?= translate('waiting') ?></option>
                        <option value="completed"><?= translate('completed') ?></option>
                        <option value="solved"><?= translate('solved') ?></option>
                        <option value="canceled"><?= translate('canceled') ?></option>
                    </select>
                </div>
            </div>
			<div class="form-group">
				<label class="col-md-3 control-label"><?= translate('incharge') ?></label>
				<div class="col-md-8">
					 <?php
						  $array = $this->app_lib->getSelectList('staff');
							// Remove ID 1(superadmin) from the array
							unset($array[1]);
							echo form_dropdown("assigned_issuer", $array, array(), "class='form-control' id='assigned_issuer' data-plugin-selectTwo data-placeholder=\"Select\" data-width='100%' "
							);
						?>
				</div>
			</div>
			<div class="form-group mb-md">
				<label class="col-md-3 control-label"><?= translate('auto_join') ?></label>
				<div class="col-md-8">
					<div class="checkbox-replace">
						<label class="i-checks">
							<input type="checkbox" name="auto_join" value="1">
							<i></i> <?= translate('allow_members_to_join_without_invite') ?>
						</label>
					</div>
				</div>
			</div>

			<div class="form-group mb-md">
				<label class="col-md-3 control-label"><?= translate('make_private') ?></label>
				<div class="col-md-8">
					<div class="checkbox-replace">
						<label class="i-checks">
							<input type="checkbox" name="is_private" value="1">
							<i></i> <?= translate('only_members_can_see') ?>
						</label>
					</div>
				</div>
			</div>

			<div class="form-group">
				<label class="col-md-3 control-label"><?= translate('members') ?></label>
				<div class="col-md-8">
					 <?php
						  $array = $this->app_lib->getSelectList('staff');
							// Remove ID 1(superadmin) from the array
							unset($array[1]);
							echo form_dropdown("members[]", $array, array(), "class='form-control' id='members' required multiple data-plugin-selectTwo data-placeholder=\"Select\" data-width='100%' "
							);
						?>
				</div>
			</div>

        </div>

        <footer class="panel-footer">
            <div class="row">
                <div class="col-md-12 text-right">
                    <button type="submit" class="btn btn-default" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
                        <i class="fas fa-plus-circle"></i> <?= translate('create') ?>
                    </button>
                    <button class="btn btn-default modal-dismiss"><?= translate('cancel') ?></button>
                </div>
            </div>
        </footer>

        <?php echo form_close(); ?>
    </section>
</div>


<!-- Huly Issue Modal -->
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
				<div class="col-md-3">
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

				<!-- Component -->
				<div class="col-md-3">
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
							   onblur="if(!this.value)this.type='text'">
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
                                "class='form-control' multiple required 
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