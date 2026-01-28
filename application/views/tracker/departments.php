
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

/* Professional UI Styles */
.departments-container {
	background: #f8fafc;
	min-height: 100vh;
	padding: 20px;
}

.departments-card {
	background: white;
	border-radius: 12px;
	box-shadow: 0 2px 8px rgba(0,0,0,0.08);
	border: none;
	overflow: hidden;
}

.departments-header {
	background: #ffffff;
	border-bottom: 1px solid #e2e8f0;
	padding: 10px 20px;
	display: flex;
	justify-content: space-between;
	align-items: center;
}

.departments-title {
	font-size: 24px;
	font-weight: 600;
	color: #1e293b;
	margin: 0;
}

.add-btn {
	background: #3b82f6;
	color: white;
	border: none;
	padding: 5px 10px;
	border-radius: 8px;
	font-weight: 500;
	transition: all 0.2s;
}

.add-btn:hover {
	background: #2563eb;
	transform: translateY(-1px);
}

.filter-section {
	padding: 10px 20px 10px;
	background: #f8fafc;
	border-bottom: 1px solid #e2e8f0;
}

.filter-label {
	font-weight: 600;
	color: #374151;
	margin-bottom: 8px;
	display: block;
}

.filter-select {
	border: 1px solid #d1d5db;
	border-radius: 8px;
	padding: 8px 12px;
	background: white;
	color: #374151;
	font-size: 14px;
	transition: border-color 0.2s;
}

.filter-select:focus {
	border-color: #3b82f6;
	outline: none;
	box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.departments-table {
	margin: 0;
	border: none;
}

.departments-table thead th {
	background: #f1f5f9;
	border: none;
	border-bottom: 2px solid #e2e8f0;
	padding: 16px 24px;
	font-weight: 600;
	color: #475569;
	font-size: 14px;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}

.departments-table tbody td {
	padding: 20px 24px;
	border: none;
	border-bottom: 1px solid #f1f5f9;
	vertical-align: middle;
	color: #374151;
}

.departments-table tbody tr {
	transition: background-color 0.2s;
}

.departments-table tbody tr:hover {
	background: #f8fafc;
}

.dept-name {
	font-weight: 600;
	color: #1e293b;
	font-size: 16px;
}

.member-count {
	background: #e0f2fe;
	color: #0369a1;
	padding: 4px 12px;
	border-radius: 20px;
	font-weight: 500;
	font-size: 13px;
	display: inline-block;
}

.incharge-name {
	color: #6b7280;
	font-weight: 500;
}

.status-badge {
	padding: 6px 12px;
	border-radius: 20px;
	font-weight: 500;
	font-size: 12px;
	text-transform: capitalize;
}

.action-btn {
	width: 36px;
	height: 36px;
	border-radius: 8px;
	border: none;
	margin: 0 4px;
	transition: all 0.2s;
	display: inline-flex;
	align-items: center;
	justify-content: center;
}

.btn-join {
	background: #dcfce7;
	color: #16a34a;
}

.btn-join:hover {
	background: #bbf7d0;
	transform: translateY(-1px);
}

.btn-leave {
	background: #fee2e2;
	color: #dc2626;
}

.btn-leave:hover {
	background: #fecaca;
	transform: translateY(-1px);
}

.btn-private {
	background: #fef3c7;
	color: #d97706;
	opacity: 0.7;
}

.btn-edit {
	background: #f3f4f6;
	color: #6b7280;
}

.btn-edit:hover {
	background: #e5e7eb;
	color: #374151;
}

.btn-delete {
	background: #fef2f2;
	color: #ef4444;
}

.btn-delete:hover {
	background: #fee2e2;
}

.sort-icon {
	cursor: pointer;
	color: #9ca3af;
	margin-left: 8px;
	transition: color 0.2s;
}

.sort-icon:hover {
	color: #374151;
}
select {
    border: 1px solid #E5E7E9;
    border-radius: 6px;
    height: 35px;
    padding: 12px;
    outline: none;
}

</style>
<div class="row" style="height: calc(108vh);">
	<div class="col-md-3">
        <?php include 'sidebar.php'; ?>
    </div>
    <div class="col-md-9 departments-container">
	<?php 
	$all_departments = isset($all_departments) ? $all_departments : []; 
	$current_user_id = get_loggedin_user_id();
	?>

		<div class="departments-card">
			<header class="departments-header">
				<h4 class="departments-title"><?= translate('all_departments') ?></h4>
				<?php if (get_permission('tracker_department', 'is_add')): ?>
					<a href="javascript:void(0);" onclick="mfp_modal('#addDepartmentModal')" class="add-btn">
						<i class="fas fa-plus-circle me-2"></i><?= translate('add') ?>
					</a>
				<?php endif; ?>
			</header>

			<div class="filter-section">
				<select id="statusFilter" class="filter-select">
					<option value=""><?= translate('all_status') ?></option>
					<option value="backlog"><?= translate('Backlog') ?></option>
                    <option value="hold"><?= translate('Hold') ?></option>
                    <option value="todo"><?= translate('to-do') ?></option>
                    <option value="submitted"><?= translate('submitted') ?></option>
                    <option value="in_progress"><?= translate('in_progress') ?></option>
                    <option value="in_review"><?= translate('in_review') ?></option>
                    <option value="planning"><?= translate('planning') ?></option>
                    <option value="observation"><?= translate('observation') ?></option>
                    <option value="waiting"><?= translate('waiting') ?></option>
                    <option value="done"><?= translate('done') ?></option>
                    <option value="solved"><?= translate('solved') ?></option>
                    <option value="canceled"><?= translate('canceled') ?></option>
				</select>
			</div>

			<table class="departments-table table" id="departmentsTable">
					<thead>
						<tr>
							<th><?= translate('Department') ?></th>
							<th><?= translate('members') ?></th>
							<th><?= translate('incharge') ?></th>
							<th><?= translate('status') ?><i class="fa fa-sort sort-icon" onclick="sortByStatus()"></i></th>
							<th><?= translate('action') ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($all_departments as $department): ?>
						<tr data-status="<?= strtolower($department['default_status']) ?>">
							<td><span class="dept-name"><?= translate($department['title']) ?></span></td>
							<td><span class="member-count" style="cursor: pointer;" onclick="showMembers(<?= $department['id'] ?>)"><?= $department['member_count'] ?></span></td>
							<td><span class="incharge-name"><?= !empty($department['default_assignee_name']) ? translate($department['default_assignee_name']) : 'Unassigned' ?></span></td>
							<td><span class="status-badge" style="background: #e0f2fe; color: #0369a1;"><?= translate($department['default_status']) ?></span></td>
							<td>
								<?php 
								$this->load->model('tracker_model');
								$is_member = $this->tracker_model->is_member($department['id'], $current_user_id);
								?>
								<?php if ($department['is_private']): ?>
									<button class="action-btn btn-private" title="This department is private" disabled>
										<i class="fa fa-lock"></i>
									</button>
								<?php else: ?>
									<?php if ($is_member): ?>
										<a href="<?= base_url('tracker/leave_department/' . $department['id']) ?>" 
										   class="action-btn btn-leave" 
										   title="Leave this department"
										   onclick="return confirm('Are you sure you want to leave this department?');">
										   <i class="fa fa-minus"></i>
										</a>
									<?php else: ?>
										<a href="<?= base_url('tracker/join_department/' . $department['id']) ?>" 
										   class="action-btn btn-join" 
										   title="Join this department">
											<i class="fa fa-plus"></i>
										</a>
									<?php endif; ?>
								<?php endif; ?>
								<?php if (get_permission('tracker_department', 'is_edit')): ?>
									<button class="action-btn btn-edit" title="Edit" onclick="getEdit(<?= $department['id']?>)">
										<i class="fa fa-pen"></i>
									</button>
								<?php endif; ?>
								<?php if (get_permission('tracker_department', 'is_delete')): ?>
									<button class="action-btn btn-delete" title="Delete" onclick="if(confirm('Are you sure?')) window.location='<?= base_url('tracker/delete_department/' . $department['id']) ?>'">
										<i class="fa fa-trash"></i>
									</button>
								<?php endif; ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
		</div>

		<!-- Edit Label Modal Container -->
		<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
			<section class="panel" id="quick_view"></section>
		</div>

		<!-- Members Modal -->
		<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="membersModal">
			<section class="panel">
				<header class="panel-heading">
					<h4 class="panel-title">Department Members</h4>
					<button type="button" class="mfp-close">×</button>
				</header>
				<div class="panel-body" id="membersContent">
					<!-- Members will be loaded here -->
				</div>
			</section>
		</div>

	</div>
<script type="text/javascript">
function getEdit(id) {
    $.ajax({
        url: base_url + 'tracker/getDepartmentEdit',
        type: 'POST',
        data: { id: id },
        success: function(response) {
            $('#quick_view').html(response);
            mfp_modal('#modal');
        }
    });
}

function showMembers(departmentId) {
    $.ajax({
        url: base_url + 'tracker/getDepartmentMembers',
        type: 'POST',
        data: { department_id: departmentId },
        success: function(response) {
            $('#membersContent').html(response);
            mfp_modal('#membersModal');
        },
        error: function() {
            $('#membersContent').html('<p>Error loading members</p>');
            mfp_modal('#membersModal');
        }
    });
}

// Live sorting by status
let sortAsc = true;
function sortByStatus() {
    const tbody = document.querySelector('#departmentsTable tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        const statusA = a.getAttribute('data-status');
        const statusB = b.getAttribute('data-status');
        return sortAsc ? statusA.localeCompare(statusB) : statusB.localeCompare(statusA);
    });
    
    rows.forEach(row => tbody.appendChild(row));
    sortAsc = !sortAsc;
}

// Live filtering by status
document.getElementById('statusFilter').addEventListener('change', function() {
    const filterValue = this.value.toLowerCase();
    const rows = document.querySelectorAll('#departmentsTable tbody tr');
    
    rows.forEach(row => {
        const status = row.getAttribute('data-status');
        row.style.display = (!filterValue || status === filterValue) ? '' : 'none';
    });
});
</script>