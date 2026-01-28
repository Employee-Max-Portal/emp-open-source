<?php
    $isRole8 = loggedin_role_id() == 8;
    //$isRole8 = 8;
    $cardColClass = $isRole8 ? 'col-lg-3 col-sm-6 col-xs-6' : 'col-md-4 col-sm-6 col-xs-6';
?>

<div class="row">
    <div class="container-fluid">
        <div class="row g-4">
            <div class="<?= $cardColClass ?>">
                <div class="stats-card">
                    <div class="stats-card-header bg-primary">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stats-card-body">
                        <h3 class="stats-number"><?= htmlspecialchars($total_tasks) ?></h3>
                        <p class="stats-label">Total Tasks</p>
                    </div>
                </div>
            </div>

            <div class="<?= $cardColClass ?>">
                <div class="stats-card">
                    <div class="stats-card-header bg-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-card-body">
                        <h3 class="stats-number"><?= htmlspecialchars($total_verified) ?></h3>
                        <p class="stats-label">Verified Tasks</p>
                    </div>
                </div>
            </div>

            <div class="<?= $cardColClass ?>">
                <div class="stats-card">
                    <div class="stats-card-header bg-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-card-body">
                        <h3 class="stats-number"><?= htmlspecialchars($total_unverified) ?></h3>
                        <p class="stats-label">Pending Verifications</p>
                    </div>
                </div>
            </div>
			<?php if ($isRole8): ?>
            <div class="<?= $cardColClass ?>">
                <div class="stats-card">
                    <div class="stats-card-header bg-info">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <div class="stats-card-body">
                        <h3 class="stats-number"><?= htmlspecialchars($total_tasks_under_you) ?></h3>
                        <p class="stats-label">Tasks Under You</p>
                    </div>
                </div>
            </div>
			<?php endif; ?>
        </div>
    </div>
</div>

<style>
.stats-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: transform 0.2s ease;
    height: 100%;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
}

.stats-card-header {
    padding: 1.5rem;
    text-align: center;
}

.stats-card-header i {
    font-size: 2.5rem;
    color: white;
}

.stats-card-body {
    padding: 1.5rem;
    text-align: center;
}

.stats-number {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
    color: #2c3e50;
}

.stats-label {
    font-size: 1rem;
    color: #6c757d;
    margin: 0;
    font-weight: 500;
}

.bg-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.bg-success { background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%); }
.bg-warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.bg-info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
</style>

<br>

<!--- Executor View-->

<?php if (in_array(loggedin_role_id(), [1, 2, 3, 4, 5, 8, 10])) { ?>

<div class="row">
	<div class="container-fluid">
		<div class="row g-3">
			<div class="col-lg-6 col-md-6">
				<div class="data-card">
					<div class="card-header">
						<i class="fas fa-calendar-day"></i>
						<h5>Today's Tasks</h5>
					</div>
					<div class="card-body">
						<div class="search-container">
							<input type="text" id="todayTasksSearch" class="form-control" placeholder="Search tasks..." style="margin-bottom: 10px;">
						</div>
						<div class="table-container">
						<?php if (!empty($todays_tasks)): ?>
							<div class="table-responsive">
								<table class="data-table">
									<thead>
										<tr><th>Task</th><th>Due Time</th></tr>
									</thead>
									<tbody>
										<?php foreach($todays_tasks as $task): ?>
											<tr>
												<td><?= htmlspecialchars($task['title']) ?></td>
												<td><span class="time-badge">
												<?php 
												if (!empty($task['due_time'])) {
													$dueDate = new DateTime($task['due_time']);
													echo $dueDate->format('jS M, Y \a\t g:i A');
												} else {
													echo 'N/A';
												}
												?></span></td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						<?php else: ?>
							<div class="no-data">No tasks for today</div>
						<?php endif; ?>
						</div>
					</div>
				</div>
			</div>

			<div class="col-lg-6 col-md-6">
				<div class="data-card">
					<div class="card-header">
						<i class="fas fa-chart-line"></i>
						<h5>Performance Overview</h5>
					</div>
					<div class="card-body chart-body">
						<canvas id="performanceChart"></canvas>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="container-fluid">
		
		<div class="row g-3 mt-3">
			<div class="col-lg-12">
				<div class="data-card">
					<div class="card-header">
						<i class="fas fa-clock"></i>
						<h5>Pending Verifications</h5>
					</div>
					<div class="card-body">
						<div class="search-container">
							<input type="text" id="pendingVerificationsSearch" class="form-control" placeholder="Search verifications..." style="margin-bottom: 10px;">
						</div>
						<div class="table-container">
						<?php if (!empty($pending_verifications)): ?>
							<div class="table-responsive">
								<table class="data-table">
									<thead>
										<tr><th>Title</th><th>Executor</th><th>Due Time</th><th>Status</th></tr>
									</thead>
									<tbody>
										<?php foreach($pending_verifications as $veri): ?>
											<tr>
												<td><?= htmlspecialchars($veri['title']) ?></td>
												<td>
													<?php
													$getStaff = $this->db->select('name,staff_id')->where('id', $veri['assigned_user'])->get('staff')->row_array();
													echo $getStaff['staff_id'] . ' - ' . $getStaff['name'];
													?>
												</td>
												<td>
													<?php
													if (!empty($veri['due_time'])) {
														$dueDate = new DateTime($veri['due_time']);
														$currentDate = new DateTime();
														$daysDiff = $currentDate->diff($dueDate)->days;
														$isOverdue = $currentDate > $dueDate && $daysDiff > 6;
														$badgeClass = $isOverdue ? 'extreme-due' : 'time-badge';
														echo '<span class="' . $badgeClass . '">' . $dueDate->format('jS M, Y') . '</span>';
													} else {
														echo '<span class="time-badge">N/A</span>';
													}
													?>
												</td>
												<td>
													<?php
													if ($veri['verify_status'] == 1)
														echo '<span class="status-badge status-warning">' . translate('pending') . '</span>';
													else if ($veri['verify_status'] == 2)
														echo '<span class="status-badge status-success">' . translate('completed') . '</span>';
													else if ($veri['verify_status'] == 3)
														echo '<span class="status-badge status-danger">' . translate('rejected') . '</span>';
													?>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						<?php else: ?>
							<div class="no-data">No pending verifications</div>
						<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
.fixed-height-body {
    height: 300px;
    overflow-y: auto;
    padding: 1.25rem;
}

.chart-body {
    height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.25rem;
}

.dashboard-content {
    padding: 1.5rem 0;
}

.data-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: all 0.3s ease;
    height: 100%;
    border: 1px solid rgba(0,0,0,0.05);
}

.data-card:hover {
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}

.card-header {
    padding: 1.25rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.card-header i {
    font-size: 1.25rem;
    opacity: 0.9;
}

.card-header h5 {
    margin: 0;
}

.card-body {
    padding: 1.25rem;
    max-height: 400px;
    overflow-y: auto;
}

.chart-body {
    height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: #f8f9fa;
    padding: 0.75rem 0.5rem;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
    font-size: 1.3rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.data-table td {
    padding: 0.75rem 0.5rem;
    border-bottom: 1px solid #f1f3f4;
    vertical-align: middle;
	font-size: 1.3rem;
}

.data-table tr:hover {
    background: #f8f9fa;
}

.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 8px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-warning { background: #fff3cd; color: #856404; }
.status-success { background: #d4edda; color: #155724; }
.status-danger { background: #f8d7da; color: #721c24; }
.status-secondary { background: #e2e3e5; color: #383d41; }

.time-badge, .date-badge {
    background: #e9ecef;
    color: #495057;
    padding: 0.25rem 0.5rem;
    border-radius: 8px;
    font-weight: 500;
}

.extreme-due {
    background: #dc3545;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 8px;
    font-weight: 600;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

.no-data {
    text-align: center;
    color: #6c757d;
    font-style: italic;
    padding: 2rem;
    background: #f8f9fa;
    border-radius: 8px;
    margin: 1rem 0;
}

.bg-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.bg-success { background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%); }
.bg-warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.bg-info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }

@media (max-width: 768px) {
    .dashboard-page, .dashboard-content { padding: 1rem 0; }
    .stats-number { font-size: 1.75rem; }
    .stats-label { font-size: 0.8rem; }
    .card-body { padding: 1rem; max-height: 300px; }
    .data-table th, .data-table td { padding: 0.5rem 0.25rem; font-size: 0.8rem; }
    .chart-body { height: 250px; }
}

@media (max-width: 576px) {
    .stats-card-header { padding: 1rem; }
    .stats-card-body { padding: 1rem; }
    .data-table { font-size: 0.75rem; }
    .status-badge, .time-badge, .date-badge { font-size: 0.7rem; padding: 0.2rem 0.4rem; }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('performanceChart'), {
    type: 'doughnut',
    data: {
        labels: ['Completed', 'Remaining'],
        datasets: [{
            data: [<?= $discipline_score ?>, <?= 100 - $discipline_score ?>],
            backgroundColor: ['#4285f4', '#e9ecef'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } },
            title: { display: true, text: 'Monthly Discipline Score: <?= $discipline_score ?>%', font: { size: 14, weight: 'bold' } }
        }
    }
});

// Search functionality for Today's Tasks
document.getElementById('todayTasksSearch').addEventListener('keyup', function() {
    var input = this.value.toLowerCase();
    var table = document.querySelector('#todayTasksSearch').closest('.data-card').querySelector('table');
    var rows = table.getElementsByTagName('tr');
    
    for (var i = 1; i < rows.length; i++) {
        var cells = rows[i].getElementsByTagName('td');
        var found = false;
        for (var j = 0; j < cells.length; j++) {
            if (cells[j].textContent.toLowerCase().indexOf(input) > -1) {
                found = true;
                break;
            }
        }
        rows[i].style.display = found ? '' : 'none';
    }
});

// Search functionality for Pending Verifications
document.getElementById('pendingVerificationsSearch').addEventListener('keyup', function() {
    var input = this.value.toLowerCase();
    var table = document.querySelector('#pendingVerificationsSearch').closest('.data-card').querySelector('table');
    var rows = table.getElementsByTagName('tr');
    
    for (var i = 1; i < rows.length; i++) {
        var cells = rows[i].getElementsByTagName('td');
        var found = false;
        for (var j = 0; j < cells.length; j++) {
            if (cells[j].textContent.toLowerCase().indexOf(input) > -1) {
                found = true;
                break;
            }
        }
        rows[i].style.display = found ? '' : 'none';
    }
});
</script>

<?php } ?>

<!--- Executor View-->

<br>

<!--- Verifier View-->

<?php if (in_array(loggedin_role_id(), [1, 2, 3, 5, 8, 10])) { ?>

<div class="row">
	<!-- Team Task Status -->
	<div class="col-md-6">
	  <div class="table-section">
		<div class="section-header">
		  <i class="fas fa-users"></i>
		  <h4>Team Task Status</h4>
		</div>
		<div class="table-container">
		<?php if (!empty($team_task_status)): ?>
		  <table>
			<thead>
			  <tr>
				<th>Employee</th>
				<th>Status</th>
				<th>Verification</th>
				<th>Date</th>
			  </tr>
			</thead>
			<tbody>
			  <?php foreach ($team_task_status as $task): ?>
				<tr>
				  <td><?= htmlspecialchars($task['employee_id'] . ' - ' .$task['name']) ?></td>
				  <td>
					<?php
					  $status_classes = [1 => 'warning', 2 => 'success', 3 => 'danger', 4 => 'secondary'];
					  $status_labels = [1 => 'Pending', 2 => 'Completed', 3 => 'Canceled', 4 => 'On Hold'];
					  $class = $status_classes[$task['task_status']] ?? 'secondary';
					  $label = $status_labels[$task['task_status']] ?? 'Unknown';
					  echo "<span class=\"status-badge status-{$class}\">{$label}</span>";
					?>
				  </td>
				  <td>
					<?php
					  $verify_classes = [1 => 'warning', 2 => 'success', 3 => 'danger'];
					  $verify_labels = [1 => 'Pending', 2 => 'Approved', 3 => 'Rejected'];
					  $class = $verify_classes[$task['verify_status']] ?? 'secondary';
					  $label = $verify_labels[$task['verify_status']] ?? 'Unknown';
					  echo "<span class=\"status-badge status-{$class}\">{$label}</span>";
					?>
				  </td>
				  <td><?= date('M d, Y', strtotime($task['created_at'])) ?></td>
				</tr>
			  <?php endforeach; ?>
			</tbody>
		  </table>
		<?php else: ?>
		  <p class="no-data">No team task records found.</p>
		<?php endif; ?>
		</div>
	  </div>
	</div>

	<!-- Escalated Issues -->
	<div class="col-md-6">
	  <div class="table-section">
		<div class="section-header">
		  <i class="fas fa-exclamation-triangle"></i>
		  <h4>Escalated Issues</h4>
		</div>
		<div class="table-container">
		<?php if (!empty($escalated_issues)): ?>
		  <table>
			<thead>
			  <tr>
				<th>Title</th>
				<th>Type</th>
				<th>Remarks</th>
				<th>Date</th>
			  </tr>
			</thead>
			<tbody>
			  <?php foreach ($escalated_issues as $issue): ?>
				<tr>
				  <td><?= htmlspecialchars($issue['title']) ?></td>
				  <td>
					<?php if ($issue['is_escalated_executor']): ?>
					  <span class="status-badge status-danger">Executor</span>
					<?php endif; ?>
					<?php if ($issue['is_escalated_verifier']): ?>
					  <span class="status-badge status-warning">Verifier</span>
					<?php endif; ?>
				  </td>
				  <td>
					<?php
					  $log = $this->db->select('remarks')->where('task_id', $issue['id'])->order_by('id', 'DESC')->limit(1)->get('rdc_task_escalation_log')->row_array();
					  echo !empty($log['remarks']) ? htmlspecialchars($log['remarks']) : '-';
					?>
				  </td>
				  <td><?= !empty($issue['created_at']) ? date('M d, Y h:i A', strtotime($issue['created_at'])) : '-' ?></td>
				</tr>
			  <?php endforeach; ?>
			</tbody>
		  </table>
		<?php else: ?>
		  <p class="no-data">No escalated issues found.</p>
		<?php endif; ?>
		</div>
	  </div>
	</div>
</div>

<style>
.table-container {
    height: 250px;
    overflow-y: auto;
}

.search-container {
    margin-bottom: 10px;
}

.search-container input {
    border-radius: 6px;
    border: 1px solid #ddd;
    padding: 8px 12px;
    font-size: 14px;
}
</style>

<?php } ?>

<!--- Verifier View-->

<br>

<!--- COO/ CEO View-->

<?php if (in_array(loggedin_role_id(), [1, 2, 3, 5, 8, 10])) { ?>

<div class="row">
    <div class="container-fluid">
        <div class="row g-3">
            <!-- Escalated Tasks -->
            <div class="col-lg-4 col-md-6">
                <div class="data-card">
                    <div class="card-header">
                        <i class="fas fa-exclamation-circle"></i>
                        <h5><?php echo translate('Escalated Tasks') ?></h5>
                    </div>
                    <div class="card-body fixed-height-body">
                        <?php if (!empty($escalated_tasks)): ?>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th><?php echo translate('task_title') ?></th>
                                            <th><?php echo translate('executor') ?></th>
                                            <th><?php echo translate('type') ?></th>
                                            <!-- <th><?php echo translate('date') ?></th> -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($escalated_tasks as $task): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($task['title']) ?></td>
                                                <td>
                                                    <?php 
                                                        $getStaff = $this->db->select('name,staff_id')->where('id', $task['assigned_user'])->get('staff')->row_array();
                                                        echo $getStaff['staff_id'] . ' - ' . $getStaff['name'];
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($task['is_escalated_executor'])): ?>
                                                        <span class="status-badge status-danger"><?php echo translate('executor') ?></span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($task['is_escalated_verifier'])): ?>
                                                        <span class="status-badge status-warning"><?php echo translate('verifier') ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <!-- <td><span class="date-badge"><?= !empty($task['created_at']) ? date('M d, Y', strtotime($task['created_at'])) : 'N/A' ?></span></td> -->
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="no-data"><?php echo translate('No escalated tasks found') ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Salary Lock Alerts -->
            <div class="col-lg-4 col-md-12">
                <div class="data-card">
                    <div class="card-header">
                        <i class="fas fa-lock"></i>
                        <h5><?php echo translate('salary locked') ?></h5>
                    </div>
                    <div class="card-body fixed-height-body">
                        <?php if (!empty($salary_lock_alerts)): ?>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th><?php echo translate('employee') ?></th>
                                            <!-- <th><?php echo translate('task') ?></th> -->
                                            <th><?php echo translate('reason') ?></th>
                                            <th><?php echo translate('locked at') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($salary_lock_alerts as $block): ?>
                                            <?php $getStaff = $this->db->select('name, staff_id')->where('id', $block['staff_id'])->get('staff')->row_array(); ?>
                                            <tr>
                                                <td><?= htmlspecialchars($getStaff['staff_id'] . ' - ' . $getStaff['name']) ?></td>
                                                <!-- <td><?= htmlspecialchars($block['title']) ?></td> -->
                                                <td><?= htmlspecialchars($block['reason']) ?></td>
                                                <td><span class="date-badge"><?= !empty($block['created_at']) ? date('M d, Y', strtotime($block['created_at'])) : 'N/A' ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="no-data">No salary lock alerts found</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
			
			<!-- Department Compliance Chart -->
			<div class="col-lg-4 col-md-6">
				<div class="data-card">
					<div class="card-header">
						<i class="fas fa-chart-bar"></i>
						<h5><?php echo translate('department_compliance') ?></h5>
					</div>
					<div class="card-body chart-body">
						<canvas id="complianceChart"></canvas>
					</div>
				</div>
			</div>

        </div>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($department_compliance)): ?>
        const labels = [<?php foreach($department_compliance as $row): ?>'<?= addslashes($row['department_name']) ?>',<?php endforeach; ?>];
        const data = [<?php foreach($department_compliance as $row): ?><?= $row['compliance_percent'] ?>,<?php endforeach; ?>];
        const colors = ['#4285f4', '#34a853', '#fbbc04', '#ea4335', '#9c27b0'];
    <?php else: ?>
        const labels = ['No Data'];
        const data = [100];
        const colors = ['#e9ecef'];
    <?php endif; ?>
    
    new Chart(document.getElementById('complianceChart'), {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                title: { display: true, text: 'Department Compliance %' }
            }
        }
    });
});
</script>


<?php } ?>

<style>
.table-section { background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 1.5rem; margin-bottom: 1rem; }
.section-header { display: flex; align-items: center; margin-bottom: 1rem; }
.section-header i { color: #4285f4; margin-right: 0.5rem; }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #f1f3f4; font-size: 1.3rem; }
th { background: #f8f9fa; font-weight: 600; }
.status-badge { padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; }
.status-warning { background: #fff3cd; color: #856404; }
.status-success { background: #d4edda; color: #155724; }
.status-danger { background: #f8d7da; color: #721c24; }
.status-secondary { background: #e2e3e5; color: #383d41; }
.no-data { color: #6c757d; font-style: italic; text-align: center; padding: 2rem; }
</style>
<!--- COO/ CEO View-->
