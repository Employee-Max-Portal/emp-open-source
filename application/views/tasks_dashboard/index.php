<style>
div[id^="accordion-body-"] {
     max-height: auto !important;
}

.label {
    display: inline;
    padding: .2em .6em .3em;
    font-size: 12px;
    font-weight: bold;
    line-height: 1;
    color: #fff;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: .25em;
}
small, .small {
    font-size: 100%;
}
.milestone-card {
    border: 1px solid #f0f4f8;
    border-radius: 8px;
    box-shadow: 0 1px 6px rgba(0,0,0,0.04);
    background: #ffffff;
    transition: all 0.2s ease;
    overflow: hidden;
}
.milestone-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    border-color: #e3f2fd;
}
.milestone-header {
    background: linear-gradient(135deg, #f8fbff 0%, #f0f8ff 100%);
    color: #4a5568;
    padding: 12px 15px;
    border-radius: 8px 8px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #f0f4f8;
}
.milestone-card.client .milestone-header {
    background: linear-gradient(135deg, #fef7f0 0%, #fff5f5 100%);
    color: #e53e3e;
}
.milestone-card.inhouse .milestone-header {
    background: linear-gradient(135deg, #f0fff4 0%, #f7fafc 100%);
    color: #38a169;
}
.milestone-name h5 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    letter-spacing: -0.01em;
}
.milestone-type {
    font-size: 10px;
    margin-top: 3px;
    font-weight: 500;
}
.badge-regular { 
    background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
    color: #5a67d8;
    border: 1px solid #e3f2fd;
}
.badge-client { 
    background: linear-gradient(135deg, #fed7d7 0%, #fbb6ce 100%);
    color: #e53e3e;
    border: 1px solid #fed7d7;
}
.badge-inhouse { 
    background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%);
    color: #38a169;
    border: 1px solid #c6f6d5;
}
.progress-indicators button {
    background: rgba(255,255,255,0.95);
    border: 1px solid #e2e8f0;
    color: #4a5568;
    font-weight: 600;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    border-radius: 6px;
    padding: 4px 8px;
    font-size: 11px;
}
.progress-indicators button:hover {
    background: #ffffff;
    border-color: #cbd5e0;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    color: #2d3748;
}
.milestone-body {
    padding: 12px 15px;
    background: #fafbfc;
}
.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 4px;
    font-size: 11px;
}
.info-row .label {
    color: #6c757d;
    font-weight: 500;
}
.info-row .value {
    color: #495057;
    font-weight: 600;
}
.fund-amount {
    color: #28a745;
    font-weight: 700;
}
.task-summary {
    margin: 8px 9px;
}
.task-summary h6 {
    margin-bottom: 4px;
    font-size: 11px;
    color: #495057;
}
.task-types {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}
.task-type-badge {
    background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
    color: #4a5568;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 9px;
    font-weight: 500;
    border: 1px solid #e2e8f0;
}
.time-summary {
    display: flex;
    justify-content: space-between;
    padding: 0px 12px;
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    border-radius: 6px;
    border: 1px solid #e2e8f0;
}
.time-row {
    text-align: center;
}
.time-label {
    display: block;
    font-size: 10px;
    color: #6c757d;
    margin-bottom: 1px;
}
.time-value {
    font-size: 12px;
    font-weight: 600;
    color: #495057;
}
.progress-section {
    margin-top: 8px;
}
.progress-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
    font-size: 10px;
}
.progress {
    height: 6px;
    border-radius: 3px;
}

/* Milestone Tasks Modal Styles */
.milestone-task-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    margin-bottom: 8px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background: #fff;
}
.milestone-task-item.completed {
    background: #f8fff8;
    border-color: #28a745;
}
.milestone-task-item.in-progress {
    background: #fff8e1;
    border-color: #ffc107;
}
.milestone-task-item.pending {
    background: #f8f9fa;
    border-color: #6c757d;
}
.task-info {
    flex: 1;
}
.task-header-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
}
.task-status-icon {
    width: 16px;
}
.task-status-icon .fa-check-circle { color: #28a745; }
.task-status-icon .fa-clock { color: #ffc107; }
.task-status-icon .fa-circle { color: #6c757d; }
.task-id-badge {
    background: #e9ecef;
    color: #495057;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    margin-left: auto;
}
.task-details-row {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 4px;
}
.task-type-status {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
}
.task-type {
    background: #e3f2fd;
    color: #1976d2;
    padding: 2px 6px;
    border-radius: 3px;
    font-weight: 500;
}
.task-status {
    font-weight: 600;
    color: #495057;
}
.task-actions {
    margin-left: 12px;
}

.task-card {
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    background: #fff;
    height: 550px;
    display: flex;
    flex-direction: column;
}
.task-header {
    background: #f8f9fa;
    color: #495057;
    padding: 10px 15px;
    border-radius: 8px 8px 0 0;
    border-bottom: 1px solid #dee2e6;
}
.task-header.client {
    background: #e3f2fd;
    color: #1976d2;
}
.task-header.inhouse {
    background: #f3e5f5;
    color: #7b1fa2;
}
.task-header.completed {
    background: #f8f9fa;
    color: #495057;
}
.task-title {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}
.task-body {
    padding: 15px;
    flex: 1;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
}
.milestone-group {
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f1f3f4;
}
.milestone-title {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
    font-size: 14px;
    text-transform: uppercase;
}
.task-item {
    padding: 8px 12px;
    margin-bottom: 6px;
    background: #f8f9fa;
    border-radius: 6px;
    border-left: 3px solid #979797;
    font-size: 13px;
}
.task-item.client {
    border-left-color: #979797;
}
.task-item.inhouse {
    border-left-color: #979797;
}
.task-item.completed {
    border-left-color: #979797;
}
.staff-name {
    color: #6c757d;
    font-weight: 500;
}
.spent-time {
    color: #28a745;
    font-weight: 600;
    float: right;
    position: relative;
    cursor: pointer;
    transition: all 0.2s ease;
}
.spent-time:hover {
    color: #20c997;
    transform: scale(1.05);
}

.spent-time:hover::after {
    content: attr(title);
    position: absolute;
    top: 100%;
    right: 50%;
    background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
    color: white;
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 11px;
    white-space: nowrap;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    margin-top: 6px;
    z-index: 1000;
}

.spent-time:hover::before {
    content: '';
    position: absolute;
    bottom: 100%;
    right: 15px;
    border: 5px solid transparent;
    border-top-color: #2d3748;
    margin-bottom: -5px;
    z-index: 1001;
}
.btn-xs:hover {
    color: #20c997;
    transform: scale(1.05);
}
.btn-xs:hover::after {
    content: attr(title);
    position: absolute;
    bottom: 100%;
    right: 0;
    background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
    color: white;
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 11px;
    white-space: nowrap;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1000;
    margin-bottom: 5px;
}
.btn-xs:hover::before {
    content: '';
    position: absolute;
    bottom: 100%;
    right: 15px;
    border: 5px solid transparent;
    border-top-color: #2d3748;
    margin-bottom: -5px;
    z-index: 1001;
}
.button {
    font-weight: 600;
    float: right;
}
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}
@media (max-width: 768px) {
    .task-body {
        max-height: 400px;
        padding: 10px;
    }
	.task-card {
		height: 450px;
	}
	
    .task-header {
        padding: 8px 12px;
    }
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
	
    .accordion-header {
        padding: 8px 10px !important;
        flex-wrap: wrap;
        gap: 8px;
    }
    .milestone-info {
        flex: 1;
        min-width: 0;
    }
    .milestone-info span {
        font-size: 12px !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .progress-info {
        flex-wrap: wrap;
        gap: 4px !important;
    }
    .btn-xs {
        font-size: 9px !important;
        padding: 2px 4px !important;
        margin: 0 1px !important;
    }
    .milestone-icon {
        width: 20px !important;
        height: 20px !important;
        font-size: 10px !important;
    }
    .progress-info span {
        font-size: 10px !important;
        padding: 2px 6px !important;
    }
	#print-button {
        display: none !important;
    }
}

.col-xs-1, .col-sm-1, .col-md-1, .col-lg-1, .col-xs-2, .col-sm-2, .col-md-2, .col-lg-2, .col-xs-3, .col-sm-3, .col-md-3, .col-lg-3, .col-xs-4, .col-sm-4, .col-md-4, .col-lg-4, .col-xs-5, .col-sm-5, .col-md-5, .col-lg-5, .col-xs-6, .col-sm-6, .col-md-6, .col-lg-6, .col-xs-7, .col-sm-7, .col-md-7, .col-lg-7, .col-xs-8, .col-sm-8, .col-md-8, .col-lg-8, .col-xs-9, .col-sm-9, .col-md-9, .col-lg-9, .col-xs-10, .col-sm-10, .col-md-10, .col-lg-10, .col-xs-11, .col-sm-11, .col-md-11, .col-lg-11, .col-xs-12, .col-sm-12, .col-md-12, .col-lg-12 {
    position: relative;
    min-height: 1px;
    padding-right: 10px;
    padding-left: 10px;
}

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
</style>
<?php $cost_per_hour = $global_config['cost_per_hour'] ?>

<!-- CEO Dashboard Header -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="ceo-header-card">
            <div class="header-content">
                <h2>Project Overview</h2>
                <p>Track progress, budgets, and team performance across all active projects</p>
            </div>
            <div class="header-stats">
                <?php 
                $total_projects = count($milestone_data);
                $total_budget = array_sum(array_column($milestone_data, 'total_fund'));
                $total_spent_hours = array_sum(array_column($milestone_data, 'spent_time'));
                $total_remaining_hours = array_sum(array_column($milestone_data, 'remaining_time'));
                $total_indirect_cost = $total_spent_hours * $cost_per_hour;
                ?>
                <div class="stat-item">
                    <div class="stat-value"><?= $total_projects ?></div>
                    <div class="stat-label">Active Projects</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">৳<?= number_format($total_budget) ?></div>
                    <div class="stat-label">Direct Cost</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">৳<?= number_format($total_indirect_cost) ?></div>
                    <div class="stat-label">Indirect Cost</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= round($total_remaining_hours) ?>h</div>
                    <div class="stat-label">Remaining Work</div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="task-card">
            <div class="task-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h4 class="task-title"><i class="fas fa-tasks"></i> Execution - Regular</h4>
                <input type="text" id="searchRegular" placeholder="Search..." style="width: 200px; padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 12px;" onkeyup="filterProjects('regular')">
            </div>
            <div class="task-body">
                <?php if (!empty($milestone_data)): ?>
                    <?php foreach ($milestone_data as $milestone): ?>
                        <?php if ($milestone['type'] == 'regular'): ?>
                            <?php 
                            $progress = $milestone['total_tasks'] > 0 ? round(($milestone['completed_tasks'] / $milestone['total_tasks']) * 100) : 0;
                            ?>
                            <div class="accordion-card" style="border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 8px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.07);">
                                <div class="accordion-header"
									 onclick="toggleMilestoneAccordion('milestone_<?= $milestone['id'] ?>')"
									 style="display: flex; flex-direction: column; gap: 4px; padding: 12px 15px; cursor: pointer; font-weight: 600; font-size: 12px; color: #000; background: #f7f7f8; user-select: none; transition: all 0.3s ease;">

									<!-- ROW 1: TITLE + PROGRESS -->
									<div style="display: flex; justify-content: space-between; align-items: center;">
										<div class="milestone-info" style="display: flex; align-items: center; gap: 12px;">
											<img class="rounded" src="<?php echo get_image_url('staff', $milestone['photo']);?>" width="40" height="40" />
											<span><?= htmlspecialchars($milestone['title']) ?>
											<?php foreach ($milestone['incomplete_departments'] as $dept): ?>
											<span style="background: #ffa68a; color: black; padding: 2px 5px; border-radius: 15px; font-size: 9px;margin-left:10px;">
												<?= strtoupper(substr($dept['department_name'], 0, 1)) ?>. - <?= $dept['count'] ?>
											</span>
											<?php endforeach; ?>
											</span>
										</div>

										<div class="progress-info" style="display: flex; flex-direction: column; align-items: flex-start; gap: 4px;">
											<div style="display: flex; align-items: center; gap: 6px; font-size: 11px; height: 20px;">
												<a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick="showMilestoneFinancialsReport('<?=$milestone['id']?>')">
													<i class="fas fa-eye"></i>
												</a>
												<span style="background: #e8e8e9; color: black; padding: 2px 6px; border-radius: 12px; font-size: 12px;"><?= $progress ?>%</span>
												<i class="fas fa-chevron-down" style="transition: transform 0.3s ease;"></i>
											</div>
											<small style="color: #6c757d; font-size: 7px;">
												<?= date('M d, Y', strtotime($milestone['created_at'])) ?> |
											   <?= $milestone['due_date'] ? date('M d, Y', strtotime($milestone['due_date'])) : 'Not set' ?>
											</small>
										</div>
									</div>
								</div>

                                <div class="accordion-body" id="accordion-body-milestone_<?= $milestone['id'] ?>" style="max-height: 0; overflow: hidden; transition: max-height 0.4s ease-out, padding 0.4s ease-out; background-color: #ffffff;">
                                    <div style="padding: 15px;">
                                        <div style="margin-bottom: 8px;">
                                            <strong>Owner:</strong> <?= htmlspecialchars($milestone['owner_name'] ?: 'Unassigned') ?> <br> 
                                            <strong>Priority:</strong> <?= htmlspecialchars($milestone['priority'] ?: 'Unassigned') ?> |  
                                            <strong>Status:</strong> <?= translate($milestone['status'] ?: 'Unassigned') ?>
											<?php 
                                             $indirect_cost = ($milestone['spent_time'] * $cost_per_hour  ?? 0);
                                            ?>
                                            <span class="spent-time" title="Direct Cost: ৳<?= number_format($milestone['total_fund']) ?> | Indirect Cost: ৳<?= number_format($indirect_cost) ?>">৳<?= number_format($milestone['total_fund']) ?> | ৳<?= number_format($indirect_cost) ?></span>
                                        </div>
                                        <div style="margin-bottom: 8px; font-size: 12px; color: #6c757d;">
                                            <strong>Created:</strong> <?= date('M d, Y', strtotime($milestone['created_at'])) ?> | 
                                            <strong>Due:</strong> <?= $milestone['due_date'] ? date('M d, Y', strtotime($milestone['due_date'])) : 'Not set' ?>
                                        </div>
                                        <div style="margin-bottom: 8px; font-size: 12px; color: #6c757d;">
                                            <strong>Time:</strong> <?= round($milestone['spent_time'], 1) ?>h spent, <?= round($milestone['remaining_time'], 1) ?>h remaining
                                        </div>
                                        <div style="margin-bottom: 12px; font-size: 12px;">
                                            <strong>Task Types:</strong> 
                                            <?php if (!empty($milestone['task_types'])): ?>
                                                <?php foreach ($milestone['task_types'] as $type): ?>
                                                    <span class="task-type-badge"><?= ucfirst($type['task_type']) ?>: <?= $type['count'] ?></span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                            <!-- <?php if (!empty($milestone['incomplete_departments'])): ?>
                                                <br><strong>Dept. Pending:</strong> 
                                                <?php foreach ($milestone['incomplete_departments'] as $dept): ?>
                                                    <span style="background: #fff3cd; color: #856404; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 500; border: 1px solid #ffeaa7;"><?= strtoupper(substr($dept['department_name'], 0, 3)) ?>.: <?= $dept['count'] ?></span>
                                                <?php endforeach; ?>
                                            <?php endif; ?> -->
                                        </div>
                                        <div style="margin-top: 8px; clear: both;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                            <small style="color: #6c757d; font-size: 10px;">Progress: <?= $progress ?>%</small>
                                            <div>
                                                <button type="button" class="btn btn-primary btn-xs" onclick="showMilestoneTasks(<?= $milestone['id'] ?>, 'main')" title="View Main Tasks" style="font-size: 10px; padding: 2px 4px; margin-right: 2px;">[<?= $milestone['main_completed'] ?>/<?= $milestone['main_total'] ?>]</button>
                                                <button type="button" class="btn btn-secondary btn-xs" onclick="showMilestoneTasks(<?= $milestone['id'] ?>, 'sub')" title="View Sub Tasks" style="font-size: 10px; padding: 2px 4px;">[<?= $milestone['sub_completed'] ?>/<?= $milestone['sub_total'] ?>]</button>
                                            </div>
                                        </div>
										<div style="background: #e9ecef; border-radius: 6px; height: 6px; overflow: hidden;">
                                            <div style="background: #28a745; height: 100%; width: <?= $progress ?>%; transition: width 0.3s ease; border-radius: 6px;"></div>
                                        </div>
                                        <div style="margin-top: 6px; text-align: right;">
                                            <small style="color: #6c757d; font-size: 9px;">Last updated: <?= $milestone['last_update'] ? date('M d, Y H:i', strtotime($milestone['last_update'])) : 'Never' ?></small>
                                        </div>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No execution regular milestones</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="task-card">
            <div class="task-header client" style="display: flex; justify-content: space-between; align-items: center;">
                <h4 class="task-title"><i class="fas fa-tasks"></i> Execution - Client</h4>
                <input type="text" id="searchClient" placeholder="Search..." style="width: 200px; padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 12px;" onkeyup="filterProjects('client')">
            </div>
            <div class="task-body">
                <?php if (!empty($milestone_data)): ?>
                    <?php foreach ($milestone_data as $milestone): ?>
                        <?php if ($milestone['type'] == 'client'): ?>
                            <?php 
                            $progress = $milestone['total_tasks'] > 0 ? round(($milestone['completed_tasks'] / $milestone['total_tasks']) * 100) : 0;
                            ?>
                            <div class="accordion-card" style="border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 8px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.07);">
                                <div class="accordion-header"
									 onclick="toggleMilestoneAccordion('milestone_client_<?= $milestone['id'] ?>')"
									 style="display: flex; flex-direction: column; gap: 4px; padding: 12px 15px; cursor: pointer; font-weight: 600; font-size: 12px; color: #000; background: #f7f7f8; user-select: none; transition: all 0.3s ease;">

									<!-- ROW 1: TITLE + PROGRESS -->
									<div style="display: flex; justify-content: space-between; align-items: center;">
										<div class="milestone-info" style="display: flex; align-items: center; gap: 12px;">
											<img class="rounded" src="<?php echo get_image_url('staff', $milestone['photo']);?>" width="40" height="40" />
											<span><?= htmlspecialchars($milestone['title']) ?>
											<?php foreach ($milestone['incomplete_departments'] as $dept): ?>
											<span style="background: #ffa68a; color: black; padding: 2px 5px; border-radius: 15px; font-size: 9px;margin-left:10px;">
												<?= strtoupper(substr($dept['department_name'], 0, 1)) ?>. - <?= $dept['count'] ?>
											</span>
											<?php endforeach; ?>
											</span>
										</div>

										<div class="progress-info" style="display: flex; flex-direction: column; align-items: flex-start; gap: 4px;">
											<div style="display: flex; align-items: center; gap: 6px; font-size: 11px; height: 20px;">
												<a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick="showMilestoneFinancialsReport('<?=$milestone['id']?>')">
													<i class="fas fa-eye"></i>
												</a>
												<span style="background: #e8e8e9; color: black; padding: 2px 6px; border-radius: 12px; font-size: 12px;"><?= $progress ?>%</span>
												<i class="fas fa-chevron-down" style="transition: transform 0.3s ease;"></i>
											</div>
											<small style="color: #6c757d; font-size: 7px;">
												<?= date('M d, Y', strtotime($milestone['created_at'])) ?> |
											   <?= $milestone['due_date'] ? date('M d, Y', strtotime($milestone['due_date'])) : 'Not set' ?>
											</small>
										</div>
									</div>
								</div>
                                <div class="accordion-body" id="accordion-body-milestone_client_<?= $milestone['id'] ?>" style="max-height: 0; overflow: hidden; transition: max-height 0.4s ease-out, padding 0.4s ease-out; background-color: #ffffff;">
                                    <div style="padding: 15px;">
                                        <div style="margin-bottom: 8px;">
                                            <strong>Owner:</strong> <?= htmlspecialchars($milestone['owner_name'] ?: 'Unassigned') ?> <br> 
                                            <strong>Priority:</strong> <?= htmlspecialchars($milestone['priority'] ?: 'Unassigned') ?> |  
                                            <strong>Status:</strong> <?= translate($milestone['status'] ?: 'Unassigned') ?>
											<?php 
                                             $indirect_cost = ($milestone['spent_time'] * $cost_per_hour  ?? 0);
                                            ?>
                                            <span class="spent-time" title="Direct Cost: ৳<?= number_format($milestone['total_fund']) ?> | Indirect Cost: ৳<?= number_format($indirect_cost) ?>">৳<?= number_format($milestone['total_fund']) ?> | ৳<?= number_format($indirect_cost) ?></span>
                                        </div>
                                        <div style="margin-bottom: 8px; font-size: 12px; color: #6c757d;">
                                            <strong>Created:</strong> <?= date('M d, Y', strtotime($milestone['created_at'])) ?> | 
                                            <strong>Due:</strong> <?= $milestone['due_date'] ? date('M d, Y', strtotime($milestone['due_date'])) : 'Not set' ?>
                                        </div>
                                        <div style="margin-bottom: 8px; font-size: 12px; color: #6c757d;">
                                            <strong>Time:</strong> <?= round($milestone['spent_time'], 1) ?>h spent, <?= round($milestone['remaining_time'], 1) ?>h remaining
                                        </div>
                                        <div style="margin-bottom: 12px; font-size: 12px;">
                                            <strong>Task Types:</strong> 
                                            <?php if (!empty($milestone['task_types'])): ?>
                                                <?php foreach ($milestone['task_types'] as $type): ?>
                                                    <span class="task-type-badge"><?= ucfirst($type['task_type']) ?>: <?= $type['count'] ?></span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                            <!-- <?php if (!empty($milestone['incomplete_departments'])): ?>
                                                <br><strong>Dept. Pending:</strong> 
                                                <?php foreach ($milestone['incomplete_departments'] as $dept): ?>
                                                    <span style="background: #fff3cd; color: #856404; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 500; border: 1px solid #ffeaa7;"><?= strtoupper(substr($dept['department_name'], 0, 3)) ?>.: <?= $dept['count'] ?></span>
                                                <?php endforeach; ?>
                                            <?php endif; ?> -->
                                        </div>
                                        <div style="margin-top: 8px; clear: both;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                            <small style="color: #6c757d; font-size: 10px;">Progress: <?= $progress ?>%</small>
                                            <div>
                                                <button type="button" class="btn btn-primary btn-xs" onclick="showMilestoneTasks(<?= $milestone['id'] ?>, 'main')" title="View Main Tasks" style="font-size: 10px; padding: 2px 4px; margin-right: 2px;">[<?= $milestone['main_completed'] ?>/<?= $milestone['main_total'] ?>]</button>
                                                <button type="button" class="btn btn-secondary btn-xs" onclick="showMilestoneTasks(<?= $milestone['id'] ?>, 'sub')" title="View Sub Tasks" style="font-size: 10px; padding: 2px 4px;">[<?= $milestone['sub_completed'] ?>/<?= $milestone['sub_total'] ?>]</button>
                                            </div>
                                        </div>
										<div style="background: #e9ecef; border-radius: 6px; height: 6px; overflow: hidden;">
                                            <div style="background: #28a745; height: 100%; width: <?= $progress ?>%; transition: width 0.3s ease; border-radius: 6px;"></div>
                                        </div>
                                        <div style="margin-top: 6px; text-align: right;">
                                            <small style="color: #6c757d; font-size: 9px;">Last updated: <?= $milestone['last_update'] ? date('M d, Y H:i', strtotime($milestone['last_update'])) : 'Never' ?></small>
                                        </div>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No execution client milestones</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-12 col-sm-12">
        <div class="task-card">
            <div class="task-header inhouse" style="display: flex; justify-content: space-between; align-items: center;">
                <h4 class="task-title"><i class="fas fa-tasks"></i> Execution - In-House Dev.</h4>
                <input type="text" id="searchInhouse" placeholder="Search..." style="width: 200px; padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 12px;" onkeyup="filterProjects('inhouse')">
            </div>
            <div class="task-body">
                <?php if (!empty($milestone_data)): ?>
                    <?php foreach ($milestone_data as $milestone): ?>
                        <?php if ($milestone['type'] == 'in_house'): ?>
                            <?php 
                            $progress = $milestone['total_tasks'] > 0 ? round(($milestone['completed_tasks'] / $milestone['total_tasks']) * 100) : 0;
                            ?>
                            <div class="accordion-card" style="border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 8px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.07);">
                                <div class="accordion-header"
									 onclick="toggleMilestoneAccordion('milestone_inhouse_<?= $milestone['id'] ?>')"
									 style="display: flex; flex-direction: column; gap: 4px; padding: 12px 15px; cursor: pointer; font-weight: 600; font-size: 12px; color: #000; background: #f7f7f8; user-select: none; transition: all 0.3s ease;">

									<!-- ROW 1: TITLE + PROGRESS -->
									<div style="display: flex; justify-content: space-between; align-items: center;">
										<div class="milestone-info" style="display: flex; align-items: center; gap: 12px;">
											<img class="rounded" src="<?php echo get_image_url('staff', $milestone['photo']);?>" width="40" height="40" />
											<span><?= htmlspecialchars($milestone['title']) ?>
											<?php foreach ($milestone['incomplete_departments'] as $dept): ?>
											<span style="background: #ffa68a; color: black; padding: 2px 5px; border-radius: 15px; font-size: 9px;margin-left:10px;">
												<?= strtoupper(substr($dept['department_name'], 0, 1)) ?>. - <?= $dept['count'] ?>
											</span>
											<?php endforeach; ?>
											</span>
										</div>

										<div class="progress-info" style="display: flex; flex-direction: column; align-items: flex-start; gap: 4px;">
											<div style="display: flex; align-items: center; gap: 6px; font-size: 11px; height: 20px;">
												<a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick="showMilestoneFinancialsReport('<?=$milestone['id']?>')">
													<i class="fas fa-eye"></i>
												</a>
												<span style="background: #e8e8e9; color: black; padding: 2px 6px; border-radius: 12px; font-size: 12px;"><?= $progress ?>%</span>
												<i class="fas fa-chevron-down" style="transition: transform 0.3s ease;"></i>
											</div>
											<small style="color: #6c757d; font-size: 7px;">
												<?= date('M d, Y', strtotime($milestone['created_at'])) ?> |
											   <?= $milestone['due_date'] ? date('M d, Y', strtotime($milestone['due_date'])) : 'Not set' ?>
											</small>
										</div>
									</div>
								</div>
                                <div class="accordion-body" id="accordion-body-milestone_inhouse_<?= $milestone['id'] ?>" style="max-height: 0; overflow: hidden; transition: max-height 0.4s ease-out, padding 0.4s ease-out; background-color: #ffffff;">
                                    <div style="padding: 15px;">
                                        <div style="margin-bottom: 8px;">
                                            <strong>Owner:</strong> <?= htmlspecialchars($milestone['owner_name'] ?: 'Unassigned') ?> <br> 
                                            <strong>Priority:</strong> <?= htmlspecialchars($milestone['priority'] ?: 'Unassigned') ?> |  
                                            <strong>Status:</strong> <?= translate($milestone['status'] ?: 'Unassigned') ?>
											<?php 
                                             $indirect_cost = ($milestone['spent_time'] * $cost_per_hour  ?? 0);
                                            ?>
                                            <span class="spent-time" title="Direct Cost: ৳<?= number_format($milestone['total_fund']) ?> | Indirect Cost: ৳<?= number_format($indirect_cost) ?>">৳<?= number_format($milestone['total_fund']) ?> | ৳<?= number_format($indirect_cost) ?></span>
                                        </div>
                                        <div style="margin-bottom: 8px; font-size: 12px; color: #6c757d;">
                                            <strong>Created:</strong> <?= date('M d, Y', strtotime($milestone['created_at'])) ?> | 
                                            <strong>Due:</strong> <?= $milestone['due_date'] ? date('M d, Y', strtotime($milestone['due_date'])) : 'Not set' ?>
                                        </div>
                                        <div style="margin-bottom: 8px; font-size: 12px; color: #6c757d;">
                                            <strong>Time:</strong> <?= round($milestone['spent_time'], 1) ?>h spent, <?= round($milestone['remaining_time'], 1) ?>h remaining
                                        </div>
                                        <div style="margin-bottom: 12px; font-size: 12px;">
                                            <strong>Task Types:</strong> 
                                            <?php if (!empty($milestone['task_types'])): ?>
                                                <?php foreach ($milestone['task_types'] as $type): ?>
                                                    <span class="task-type-badge"><?= ucfirst($type['task_type']) ?>: <?= $type['count'] ?></span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                            <!-- <?php if (!empty($milestone['incomplete_departments'])): ?>
                                                <br><strong>Dept. Pending:</strong> 
                                                <?php foreach ($milestone['incomplete_departments'] as $dept): ?>
                                                    <span style="background: #fff3cd; color: #856404; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 500; border: 1px solid #ffeaa7;"><?= strtoupper(substr($dept['department_name'], 0, 3)) ?>.: <?= $dept['count'] ?></span>
                                                <?php endforeach; ?>
                                            <?php endif; ?> -->
                                        </div>
                                        <div style="margin-top: 8px; clear: both;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                            <small style="color: #6c757d; font-size: 10px;">Progress: <?= $progress ?>%</small>
                                            <div>
                                                <button type="button" class="btn btn-primary btn-xs" onclick="showMilestoneTasks(<?= $milestone['id'] ?>, 'main')" title="View Main Tasks" style="font-size: 10px; padding: 2px 4px; margin-right: 2px;">[<?= $milestone['main_completed'] ?>/<?= $milestone['main_total'] ?>]</button>
                                                <button type="button" class="btn btn-secondary btn-xs" onclick="showMilestoneTasks(<?= $milestone['id'] ?>, 'sub')" title="View Sub Tasks" style="font-size: 10px; padding: 2px 4px;">[<?= $milestone['sub_completed'] ?>/<?= $milestone['sub_total'] ?>]</button>
                                            </div>
                                        </div>
										<div style="background: #e9ecef; border-radius: 6px; height: 6px; overflow: hidden;">
                                            <div style="background: #28a745; height: 100%; width: <?= $progress ?>%; transition: width 0.3s ease; border-radius: 6px;"></div>
                                        </div>
                                        <div style="margin-top: 6px; text-align: right;">
                                            <small style="color: #6c757d; font-size: 9px;">Last updated: <?= $milestone['last_update'] ? date('M d, Y H:i', strtotime($milestone['last_update'])) : 'Never' ?></small>
                                        </div>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No execution in-house milestones</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div style="margin: 30px 0; text-align: center;">
    <button type="button" class="btn btn-outline-info btn-lg" onclick="togglePlanningMilestones()" id="togglePlanningBtn" style="padding: 12px 24px; font-weight: 600; border-width: 2px; border-radius: 8px; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <i class="fas fa-chevron-down" id="togglePlanningIcon"></i> Show Planning Stage Milestones
    </button>
</div>

<div class="row" id="planningMilestonesSection" style="display: none;">
    <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="task-card">
            <div class="task-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h4 class="task-title"><i class="fas fa-clipboard-list"></i> Planning - Regular</h4>
                <input type="text" id="searchPlanningRegular" placeholder="Search..." style="width: 200px; padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 12px;" onkeyup="filterPlanningProjects('regular')">
            </div>
            <div class="task-body">
                <?php if (!empty($planning_regular)): ?>
                    <?php foreach ($planning_regular as $milestone): ?>
                        <?php 
                        $progress = $milestone['total_tasks'] > 0 ? round(($milestone['completed_tasks'] / $milestone['total_tasks']) * 100) : 0;
                        ?>
                        <div class="accordion-card" style="border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 8px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.07);">
                            <div class="accordion-header"
                                 onclick="toggleMilestoneAccordion('planning_milestone_<?= $milestone['id'] ?>')"
                                 style="display: flex; flex-direction: column; gap: 4px; padding: 12px 15px; cursor: pointer; font-weight: 600; font-size: 12px; color: #000; background: #f7f7f8; user-select: none; transition: all 0.3s ease;">

                                <!-- ROW 1: TITLE + PROGRESS -->
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div class="milestone-info" style="display: flex; align-items: center; gap: 12px;">
                                        <img class="rounded" src="<?php echo get_image_url('staff', $milestone['photo']);?>" width="40" height="40" />
                                        <span><?= htmlspecialchars($milestone['title']) ?>
                                        <?php foreach ($milestone['incomplete_departments'] as $dept): ?>
                                        <span style="background: #ffa68a; color: black; padding: 2px 5px; border-radius: 15px; font-size: 9px;margin-left:10px;">
                                            <?= strtoupper(substr($dept['department_name'], 0, 1)) ?>. - <?= $dept['count'] ?>
                                        </span>
                                        <?php endforeach; ?>
                                        </span>
                                    </div>

                                    <div class="progress-info" style="display: flex; align-items: center; gap: 8px;">
                                        
                                    </div>
									
									<div class="progress-info" style="display: flex; flex-direction: column; align-items: flex-start; gap: 4px;">
										<div style="display: flex; align-items: center; gap: 6px; font-size: 11px; height: 20px;">
											<a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick="showMilestoneFinancialsReport('<?=$milestone['id']?>')">
											<i class="fas fa-eye"></i>
											</a>
											<span style="background: #e8e8e9; color: black; padding: 4px 8px; border-radius: 12px; font-size: 12px;"><?= $progress ?>%</span>
											<i class="fas fa-chevron-down" style="transition: transform 0.3s ease;"></i>
										</div>
										<small style="color: #6c757d; font-size: 7px;">
											<?= date('M d, Y', strtotime($milestone['created_at'])) ?> |
										   <?= $milestone['due_date'] ? date('M d, Y', strtotime($milestone['due_date'])) : 'Not set' ?>
										</small>
									</div>
                                </div>
                            </div>

                            <div class="accordion-body" id="accordion-body-planning_milestone_<?= $milestone['id'] ?>" style="max-height: 0; overflow: hidden; transition: max-height 0.4s ease-out, padding 0.4s ease-out; background-color: #ffffff;">
                                <div style="padding: 15px;">
                                    <div style="margin-bottom: 8px;">
                                        <strong>Owner:</strong> <?= htmlspecialchars($milestone['owner_name'] ?: 'Unassigned') ?> <br> 
                                        <strong>Priority:</strong> <?= htmlspecialchars($milestone['priority'] ?: 'Unassigned') ?> |  
                                        <strong>Status:</strong> <?= translate($milestone['status'] ?: 'Unassigned') ?>
                                        <?php 
                                         $indirect_cost = ($milestone['spent_time'] * $cost_per_hour  ?? 0);
                                        ?>
                                        <span class="spent-time" title="Direct Cost: ৳<?= number_format($milestone['total_fund']) ?> | Indirect Cost: ৳<?= number_format($indirect_cost) ?>">৳<?= number_format($milestone['total_fund']) ?> | ৳<?= number_format($indirect_cost) ?></span>
                                    </div>
                                    <div style="margin-bottom: 8px; font-size: 12px; color: #6c757d;">
                                        <strong>Created:</strong> <?= date('M d, Y', strtotime($milestone['created_at'])) ?> | 
                                        <strong>Due:</strong> <?= $milestone['due_date'] ? date('M d, Y', strtotime($milestone['due_date'])) : 'Not set' ?>
                                    </div>
                                    <div style="margin-bottom: 8px; font-size: 12px; color: #6c757d;">
                                        <strong>Time:</strong> <?= round($milestone['spent_time'], 1) ?>h spent, <?= round($milestone['remaining_time'], 1) ?>h remaining
                                    </div>
                                    <div style="margin-bottom: 12px; font-size: 12px;">
                                        <strong>Task Types:</strong> 
                                        <?php if (!empty($milestone['task_types'])): ?>
                                            <?php foreach ($milestone['task_types'] as $type): ?>
                                                <span class="task-type-badge"><?= ucfirst($type['task_type']) ?>: <?= $type['count'] ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </div>
                                    <div style="margin-top: 8px; clear: both;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                        <small style="color: #6c757d; font-size: 10px;">Progress: <?= $progress ?>%</small>
                                        <div>
                                            <button type="button" class="btn btn-primary btn-xs" onclick="showMilestoneTasks(<?= $milestone['id'] ?>, 'main')" title="View Main Tasks" style="font-size: 10px; padding: 2px 4px; margin-right: 2px;">[<?= $milestone['main_completed'] ?>/<?= $milestone['main_total'] ?>]</button>
                                            <button type="button" class="btn btn-secondary btn-xs" onclick="showMilestoneTasks(<?= $milestone['id'] ?>, 'sub')" title="View Sub Tasks" style="font-size: 10px; padding: 2px 4px;">[<?= $milestone['sub_completed'] ?>/<?= $milestone['sub_total'] ?>]</button>
                                        </div>
                                    </div>
                                    <div style="background: #e9ecef; border-radius: 6px; height: 6px; overflow: hidden;">
                                        <div style="background: #28a745; height: 100%; width: <?= $progress ?>%; transition: width 0.3s ease; border-radius: 6px;"></div>
                                    </div>
                                    <div style="margin-top: 6px; text-align: right;">
                                        <small style="color: #6c757d; font-size: 9px;">Last updated: <?= $milestone['last_update'] ? date('M d, Y H:i', strtotime($milestone['last_update'])) : 'Never' ?></small>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No planning regular milestones</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="task-card">
            <div class="task-header client" style="display: flex; justify-content: space-between; align-items: center;">
                <h4 class="task-title"><i class="fas fa-clipboard-list"></i> Planning - Client</h4>
                <input type="text" id="searchPlanningClient" placeholder="Search..." style="width: 200px; padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 12px;" onkeyup="filterPlanningProjects('client')">
            </div>
            <div class="task-body">
                <?php if (!empty($planning_client)): ?>
                    <?php foreach ($planning_client as $milestone): ?>
                        <?php 
                        $progress = $milestone['total_tasks'] > 0 ? round(($milestone['completed_tasks'] / $milestone['total_tasks']) * 100) : 0;
                        ?>
                        <div class="accordion-card" style="border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 8px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.07);">
                            <div class="accordion-header"
                                 onclick="toggleMilestoneAccordion('planning_milestone_client_<?= $milestone['id'] ?>')"
                                 style="display: flex; flex-direction: column; gap: 4px; padding: 12px 15px; cursor: pointer; font-weight: 600; font-size: 12px; color: #000; background: #f7f7f8; user-select: none; transition: all 0.3s ease;">

                                <!-- ROW 1: TITLE + PROGRESS -->
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div class="milestone-info" style="display: flex; align-items: center; gap: 12px;">
                                        <img class="rounded" src="<?php echo get_image_url('staff', $milestone['photo']);?>" width="40" height="40" />
                                        <span><?= htmlspecialchars($milestone['title']) ?>
                                        <?php foreach ($milestone['incomplete_departments'] as $dept): ?>
                                        <span style="background: #ffa68a; color: black; padding: 2px 5px; border-radius: 15px; font-size: 9px;margin-left:10px;">
                                            <?= strtoupper(substr($dept['department_name'], 0, 1)) ?>. - <?= $dept['count'] ?>
                                        </span>
                                        <?php endforeach; ?>
                                        </span>
                                    </div>

                                    <div class="progress-info" style="display: flex; flex-direction: column; align-items: flex-start; gap: 4px;">
										<div style="display: flex; align-items: center; gap: 6px; font-size: 11px; height: 20px;">
											<a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick="showMilestoneFinancialsReport('<?=$milestone['id']?>')">
											<i class="fas fa-eye"></i>
											</a>
											<span style="background: #e8e8e9; color: black; padding: 4px 8px; border-radius: 12px; font-size: 12px;"><?= $progress ?>%</span>
											<i class="fas fa-chevron-down" style="transition: transform 0.3s ease;"></i>
										</div>
										<small style="color: #6c757d; font-size: 7px;">
											<?= date('M d, Y', strtotime($milestone['created_at'])) ?> |
										   <?= $milestone['due_date'] ? date('M d, Y', strtotime($milestone['due_date'])) : 'Not set' ?>
										</small>
									</div>
                                </div>
                            </div>
                            <div class="accordion-body" id="accordion-body-planning_milestone_client_<?= $milestone['id'] ?>" style="max-height: 0; overflow: hidden; transition: max-height 0.4s ease-out, padding 0.4s ease-out; background-color: #ffffff;">
                                <div style="padding: 15px;">
                                    <div style="margin-bottom: 8px;">
                                        <strong>Owner:</strong> <?= htmlspecialchars($milestone['owner_name'] ?: 'Unassigned') ?> <br> 
                                        <strong>Priority:</strong> <?= htmlspecialchars($milestone['priority'] ?: 'Unassigned') ?> |  
                                        <strong>Status:</strong> <?= translate($milestone['status'] ?: 'Unassigned') ?>
                                        <?php 
                                         $indirect_cost = ($milestone['spent_time'] * $cost_per_hour  ?? 0);
                                        ?>
                                        <span class="spent-time" title="Direct Cost: ৳<?= number_format($milestone['total_fund']) ?> | Indirect Cost: ৳<?= number_format($indirect_cost) ?>">৳<?= number_format($milestone['total_fund']) ?> | ৳<?= number_format($indirect_cost) ?></span>
                                    </div>
                                    <div style="margin-bottom: 8px; font-size: 12px; color: #6c757d;">
                                        <strong>Created:</strong> <?= date('M d, Y', strtotime($milestone['created_at'])) ?> | 
                                        <strong>Due:</strong> <?= $milestone['due_date'] ? date('M d, Y', strtotime($milestone['due_date'])) : 'Not set' ?>
                                    </div>
                                    <div style="margin-bottom: 8px; font-size: 12px; color: #6c757d;">
                                        <strong>Time:</strong> <?= round($milestone['spent_time'], 1) ?>h spent, <?= round($milestone['remaining_time'], 1) ?>h remaining
                                    </div>
                                    <div style="margin-bottom: 12px; font-size: 12px;">
                                        <strong>Task Types:</strong> 
                                        <?php if (!empty($milestone['task_types'])): ?>
                                            <?php foreach ($milestone['task_types'] as $type): ?>
                                                <span class="task-type-badge"><?= ucfirst($type['task_type']) ?>: <?= $type['count'] ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </div>
                                    <div style="margin-top: 8px; clear: both;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                        <small style="color: #6c757d; font-size: 10px;">Progress: <?= $progress ?>%</small>
                                        <div>
                                            <button type="button" class="btn btn-primary btn-xs" onclick="showMilestoneTasks(<?= $milestone['id'] ?>, 'main')" title="View Main Tasks" style="font-size: 10px; padding: 2px 4px; margin-right: 2px;">[<?= $milestone['main_completed'] ?>/<?= $milestone['main_total'] ?>]</button>
                                            <button type="button" class="btn btn-secondary btn-xs" onclick="showMilestoneTasks(<?= $milestone['id'] ?>, 'sub')" title="View Sub Tasks" style="font-size: 10px; padding: 2px 4px;">[<?= $milestone['sub_completed'] ?>/<?= $milestone['sub_total'] ?>]</button>
                                        </div>
                                    </div>
                                    <div style="background: #e9ecef; border-radius: 6px; height: 6px; overflow: hidden;">
                                        <div style="background: #28a745; height: 100%; width: <?= $progress ?>%; transition: width 0.3s ease; border-radius: 6px;"></div>
                                    </div>
                                    <div style="margin-top: 6px; text-align: right;">
                                        <small style="color: #6c757d; font-size: 9px;">Last updated: <?= $milestone['last_update'] ? date('M d, Y H:i', strtotime($milestone['last_update'])) : 'Never' ?></small>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No planning client milestones</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-12 col-sm-12">
        <div class="task-card">
            <div class="task-header inhouse" style="display: flex; justify-content: space-between; align-items: center;">
                <h4 class="task-title"><i class="fas fa-clipboard-list"></i> Planning - In-House Dev.</h4>
                <input type="text" id="searchPlanningInhouse" placeholder="Search..." style="width: 200px; padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 12px;" onkeyup="filterPlanningProjects('inhouse')">
            </div>
            <div class="task-body">
                <?php if (!empty($planning_inhouse)): ?>
                    <?php foreach ($planning_inhouse as $milestone): ?>
                        <?php 
                        $progress = $milestone['total_tasks'] > 0 ? round(($milestone['completed_tasks'] / $milestone['total_tasks']) * 100) : 0;
                        ?>
                        <div class="accordion-card" style="border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 8px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.07);">
                            <div class="accordion-header"
                                 onclick="toggleMilestoneAccordion('planning_milestone_inhouse_<?= $milestone['id'] ?>')"
                                 style="display: flex; flex-direction: column; gap: 4px; padding: 12px 15px; cursor: pointer; font-weight: 600; font-size: 12px; color: #000; background: #f7f7f8; user-select: none; transition: all 0.3s ease;">

                                <!-- ROW 1: TITLE + PROGRESS -->
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div class="milestone-info" style="display: flex; align-items: center; gap: 12px;">
                                        <img class="rounded" src="<?php echo get_image_url('staff', $milestone['photo']);?>" width="40" height="40" />
                                        <span><?= htmlspecialchars($milestone['title']) ?>
                                        <?php foreach ($milestone['incomplete_departments'] as $dept): ?>
                                        <span style="background: #ffa68a; color: black; padding: 2px 5px; border-radius: 15px; font-size: 9px;margin-left:10px;">
                                            <?= strtoupper(substr($dept['department_name'], 0, 1)) ?>. - <?= $dept['count'] ?>
                                        </span>
                                        <?php endforeach; ?>
                                        </span>
                                    </div>

                                    <div class="progress-info" style="display: flex; flex-direction: column; align-items: flex-start; gap: 4px;">
										<div style="display: flex; align-items: center; gap: 6px; font-size: 11px; height: 20px;">
											<a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick="showMilestoneFinancialsReport('<?=$milestone['id']?>')">
											<i class="fas fa-eye"></i>
											</a>
											<span style="background: #e8e8e9; color: black; padding: 4px 8px; border-radius: 12px; font-size: 12px;"><?= $progress ?>%</span>
											<i class="fas fa-chevron-down" style="transition: transform 0.3s ease;"></i>
										</div>
										<small style="color: #6c757d; font-size: 7px;">
											<?= date('M d, Y', strtotime($milestone['created_at'])) ?> |
										   <?= $milestone['due_date'] ? date('M d, Y', strtotime($milestone['due_date'])) : 'Not set' ?>
										</small>
									</div>
                                </div>
                            </div>
                            <div class="accordion-body" id="accordion-body-planning_milestone_inhouse_<?= $milestone['id'] ?>" style="max-height: 0; overflow: hidden; transition: max-height 0.4s ease-out, padding 0.4s ease-out; background-color: #ffffff;">
                                <div style="padding: 15px;">
                                    <div style="margin-bottom: 8px;">
                                        <strong>Owner:</strong> <?= htmlspecialchars($milestone['owner_name'] ?: 'Unassigned') ?> <br> 
                                        <strong>Priority:</strong> <?= htmlspecialchars($milestone['priority'] ?: 'Unassigned') ?> |  
                                        <strong>Status:</strong> <?= translate($milestone['status'] ?: 'Unassigned') ?>
                                        <?php 
                                         $indirect_cost = ($milestone['spent_time'] * $cost_per_hour  ?? 0);
                                        ?>
                                        <span class="spent-time" title="Direct Cost: ৳<?= number_format($milestone['total_fund']) ?> | Indirect Cost: ৳<?= number_format($indirect_cost) ?>">৳<?= number_format($milestone['total_fund']) ?> | ৳<?= number_format($indirect_cost) ?></span>
                                    </div>
                                    <div style="margin-bottom: 8px; font-size: 12px; color: #6c757d;">
                                        <strong>Created:</strong> <?= date('M d, Y', strtotime($milestone['created_at'])) ?> | 
                                        <strong>Due:</strong> <?= $milestone['due_date'] ? date('M d, Y', strtotime($milestone['due_date'])) : 'Not set' ?>
                                    </div>
                                    <div style="margin-bottom: 8px; font-size: 12px; color: #6c757d;">
                                        <strong>Time:</strong> <?= round($milestone['spent_time'], 1) ?>h spent, <?= round($milestone['remaining_time'], 1) ?>h remaining
                                    </div>
                                    <div style="margin-bottom: 12px; font-size: 12px;">
                                        <strong>Task Types:</strong> 
                                        <?php if (!empty($milestone['task_types'])): ?>
                                            <?php foreach ($milestone['task_types'] as $type): ?>
                                                <span class="task-type-badge"><?= ucfirst($type['task_type']) ?>: <?= $type['count'] ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </div>
                                    <div style="margin-top: 8px; clear: both;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                        <small style="color: #6c757d; font-size: 10px;">Progress: <?= $progress ?>%</small>
                                        <div>
                                            <button type="button" class="btn btn-primary btn-xs" onclick="showMilestoneTasks(<?= $milestone['id'] ?>, 'main')" title="View Main Tasks" style="font-size: 10px; padding: 2px 4px; margin-right: 2px;">[<?= $milestone['main_completed'] ?>/<?= $milestone['main_total'] ?>]</button>
                                            <button type="button" class="btn btn-secondary btn-xs" onclick="showMilestoneTasks(<?= $milestone['id'] ?>, 'sub')" title="View Sub Tasks" style="font-size: 10px; padding: 2px 4px;">[<?= $milestone['sub_completed'] ?>/<?= $milestone['sub_total'] ?>]</button>
                                        </div>
                                    </div>
                                    <div style="background: #e9ecef; border-radius: 6px; height: 6px; overflow: hidden;">
                                        <div style="background: #28a745; height: 100%; width: <?= $progress ?>%; transition: width 0.3s ease; border-radius: 6px;"></div>
                                    </div>
                                    <div style="margin-top: 6px; text-align: right;">
                                        <small style="color: #6c757d; font-size: 9px;">Last updated: <?= $milestone['last_update'] ? date('M d, Y H:i', strtotime($milestone['last_update'])) : 'Never' ?></small>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No planning in-house milestones</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div style="margin: 30px 0; text-align: center;">
    <button type="button" class="btn btn-outline-warning btn-lg" onclick="toggleHoldMilestones()" id="toggleHoldBtn" style="padding: 12px 24px; font-weight: 600; border-width: 2px; border-radius: 8px; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <i class="fas fa-chevron-down" id="toggleHoldIcon"></i> Show Hold Status Milestones
    </button>
</div>

<div class="row" id="holdMilestonesSection" style="display: none;">
    <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="task-card">
            <div class="task-header" style="background: #fff3cd; color: #856404; display: flex; justify-content: space-between; align-items: center;">
                <h4 class="task-title"><i class="fas fa-pause-circle"></i> Hold - Regular</h4>
                <input type="text" id="searchHoldRegular" placeholder="Search..." style="width: 200px; padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 12px;" onkeyup="filterHoldProjects('regular')">
            </div>
            <div class="task-body">
                <?php if (!empty($hold_regular)): ?>
                    <?php foreach ($hold_regular as $milestone): ?>
                        <?php 
                        $progress = $milestone['total_tasks'] > 0 ? round(($milestone['completed_tasks'] / $milestone['total_tasks']) * 100) : 0;
                        ?>
                        <div class="accordion-card" style="border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 8px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.07);">
                            <div class="accordion-header"
                                 onclick="toggleMilestoneAccordion('hold_milestone_<?= $milestone['id'] ?>')"
                                 style="display: flex; flex-direction: column; gap: 4px; padding: 12px 15px; cursor: pointer; font-weight: 600; font-size: 12px; color: #000; background: #f7f7f8; user-select: none; transition: all 0.3s ease;">

                                <!-- ROW 1: TITLE + PROGRESS -->
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div class="milestone-info" style="display: flex; align-items: center; gap: 12px;">
                                        <img class="rounded" src="<?php echo get_image_url('staff', $milestone['photo']);?>" width="40" height="40" />
                                        <span><?= htmlspecialchars($milestone['title']) ?>
                                        <?php foreach ($milestone['incomplete_departments'] as $dept): ?>
                                        <span style="background: #ffa68a; color: black; padding: 2px 5px; border-radius: 15px; font-size: 9px;margin-left:10px;">
                                            <?= strtoupper(substr($dept['department_name'], 0, 1)) ?>. - <?= $dept['count'] ?>
                                        </span>
                                        <?php endforeach; ?>
                                        </span>
                                    </div>
									
									<div class="progress-info" style="display: flex; flex-direction: column; align-items: flex-start; gap: 4px;">
										<div style="display: flex; align-items: center; gap: 6px; font-size: 11px; height: 20px;">
											<a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick="showMilestoneFinancialsReport('<?=$milestone['id']?>')">
                                            <i class="fas fa-eye"></i>
											</a>
											<span style="background: #e8e8e9; color: black; padding: 4px 8px; border-radius: 12px; font-size: 12px;"><?= $progress ?>%</span>
											<i class="fas fa-chevron-down" style="transition: transform 0.3s ease;"></i>
										</div>
										<small style="color: #6c757d; font-size: 7px;">
											<?= date('M d, Y', strtotime($milestone['created_at'])) ?> |
										   <?= $milestone['due_date'] ? date('M d, Y', strtotime($milestone['due_date'])) : 'Not set' ?>
										</small>
									</div>
                                </div>
                            </div>

                            <div class="accordion-body" id="accordion-body-hold_milestone_<?= $milestone['id'] ?>" style="max-height: 0; overflow: hidden; transition: max-height 0.4s ease-out, padding 0.4s ease-out; background-color: #ffffff;">
                                <div style="padding: 15px;">
                                    <div style="margin-bottom: 8px;">
                                        <strong>Owner:</strong> <?= htmlspecialchars($milestone['owner_name'] ?: 'Unassigned') ?> <br> 
                                        <strong>Priority:</strong> <?= htmlspecialchars($milestone['priority'] ?: 'Unassigned') ?> |  
                                        <strong>Status:</strong> <?= translate($milestone['status'] ?: 'Unassigned') ?>
                                        <?php 
                                         $indirect_cost = ($milestone['spent_time'] * $cost_per_hour  ?? 0);
                                        ?>
                                        <span class="spent-time" title="Direct Cost: ৳<?= number_format($milestone['total_fund']) ?> | Indirect Cost: ৳<?= number_format($indirect_cost) ?>">৳<?= number_format($milestone['total_fund']) ?> | ৳<?= number_format($indirect_cost) ?></span>
                                    </div>
                                    <div style="margin-bottom: 8px; font-size: 12px; color: #6c757d;">
                                        <strong>Created:</strong> <?= date('M d, Y', strtotime($milestone['created_at'])) ?> | 
                                        <strong>Due:</strong> <?= $milestone['due_date'] ? date('M d, Y', strtotime($milestone['due_date'])) : 'Not set' ?>
                                    </div>
                                    <div style="margin-bottom: 8px; font-size: 12px; color: #6c757d;">
                                        <strong>Time:</strong> <?= round($milestone['spent_time'], 1) ?>h spent, <?= round($milestone['remaining_time'], 1) ?>h remaining
                                    </div>
                                    <div style="margin-bottom: 12px; font-size: 12px;">
                                        <strong>Task Types:</strong> 
                                        <?php if (!empty($milestone['task_types'])): ?>
                                            <?php foreach ($milestone['task_types'] as $type): ?>
                                                <span class="task-type-badge"><?= ucfirst($type['task_type']) ?>: <?= $type['count'] ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </div>
                                    <div style="margin-top: 8px; clear: both;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                        <small style="color: #6c757d; font-size: 10px;">Progress: <?= $progress ?>%</small>
                                        <div>
                                            <button type="button" class="btn btn-primary btn-xs" onclick="showMilestoneTasks(<?= $milestone['id'] ?>, 'main')" title="View Main Tasks" style="font-size: 10px; padding: 2px 4px; margin-right: 2px;">[<?= $milestone['main_completed'] ?>/<?= $milestone['main_total'] ?>]</button>
                                            <button type="button" class="btn btn-secondary btn-xs" onclick="showMilestoneTasks(<?= $milestone['id'] ?>, 'sub')" title="View Sub Tasks" style="font-size: 10px; padding: 2px 4px;">[<?= $milestone['sub_completed'] ?>/<?= $milestone['sub_total'] ?>]</button>
                                        </div>
                                    </div>
                                    <div style="background: #e9ecef; border-radius: 6px; height: 6px; overflow: hidden;">
                                        <div style="background: #28a745; height: 100%; width: <?= $progress ?>%; transition: width 0.3s ease; border-radius: 6px;"></div>
                                    </div>
                                    <div style="margin-top: 6px; text-align: right;">
                                        <small style="color: #6c757d; font-size: 9px;">Last updated: <?= $milestone['last_update'] ? date('M d, Y H:i', strtotime($milestone['last_update'])) : 'Never' ?></small>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No hold regular milestones</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="task-card">
            <div class="task-header" style="background: #fff3cd; color: #856404; display: flex; justify-content: space-between; align-items: center;">
                <h4 class="task-title"><i class="fas fa-pause-circle"></i> Hold - Client</h4>
                <input type="text" id="searchHoldClient" placeholder="Search..." style="width: 200px; padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 12px;" onkeyup="filterHoldProjects('client')">
            </div>
            <div class="task-body">
                <?php if (!empty($hold_client)): ?>
                    <?php foreach ($hold_client as $milestone): ?>
                        <?php 
                        $progress = $milestone['total_tasks'] > 0 ? round(($milestone['completed_tasks'] / $milestone['total_tasks']) * 100) : 0;
                        ?>
                        <div class="accordion-card" style="border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 8px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.07);">
                            <div class="accordion-header"
                                 onclick="toggleMilestoneAccordion('hold_milestone_client_<?= $milestone['id'] ?>')"
                                 style="display: flex; flex-direction: column; gap: 4px; padding: 12px 15px; cursor: pointer; font-weight: 600; font-size: 12px; color: #000; background: #f7f7f8; user-select: none; transition: all 0.3s ease;">

                                <!-- ROW 1: TITLE + PROGRESS -->
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div class="milestone-info" style="display: flex; align-items: center; gap: 12px;">
                                        <img class="rounded" src="<?php echo get_image_url('staff', $milestone['photo']);?>" width="40" height="40" />
                                        <span><?= htmlspecialchars($milestone['title']) ?>
                                        <?php foreach ($milestone['incomplete_departments'] as $dept): ?>
                                        <span style="background: #ffa68a; color: black; padding: 2px 5px; border-radius: 15px; font-size: 9px;margin-left:10px;">
                                            <?= strtoupper(substr($dept['department_name'], 0, 1)) ?>. - <?= $dept['count'] ?>
                                        </span>
                                        <?php endforeach; ?>
                                        </span>
                                    </div>

                                    <div class="progress-info" style="display: flex; flex-direction: column; align-items: flex-start; gap: 4px;">
										<div style="display: flex; align-items: center; gap: 6px; font-size: 11px; height: 20px;">
											<a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick="showMilestoneFinancialsReport('<?=$milestone['id']?>')">
                                            <i class="fas fa-eye"></i>
											</a>
											<span style="background: #e8e8e9; color: black; padding: 4px 8px; border-radius: 12px; font-size: 12px;"><?= $progress ?>%</span>
											<i class="fas fa-chevron-down" style="transition: transform 0.3s ease;"></i>
										</div>
										<small style="color: #6c757d; font-size: 7px;">
											<?= date('M d, Y', strtotime($milestone['created_at'])) ?> |
										   <?= $milestone['due_date'] ? date('M d, Y', strtotime($milestone['due_date'])) : 'Not set' ?>
										</small>
									</div>
                                </div>
                            </div>
                            <div class="accordion-body" id="accordion-body-hold_milestone_client_<?= $milestone['id'] ?>" style="max-height: 0; overflow: hidden; transition: max-height 0.4s ease-out, padding 0.4s ease-out; background-color: #ffffff;">
                                <div style="padding: 15px;">
                                    <div style="margin-bottom: 8px;">
                                        <strong>Owner:</strong> <?= htmlspecialchars($milestone['owner_name'] ?: 'Unassigned') ?> <br> 
                                        <strong>Priority:</strong> <?= htmlspecialchars($milestone['priority'] ?: 'Unassigned') ?> |  
                                        <strong>Status:</strong> <?= translate($milestone['status'] ?: 'Unassigned') ?>
                                        <?php 
                                         $indirect_cost = ($milestone['spent_time'] * $cost_per_hour  ?? 0);
                                        ?>
                                        <span class="spent-time" title="Direct Cost: ৳<?= number_format($milestone['total_fund']) ?> | Indirect Cost: ৳<?= number_format($indirect_cost) ?>">৳<?= number_format($milestone['total_fund']) ?> | ৳<?= number_format($indirect_cost) ?></span>
                                    </div>
                                    <div style="margin-bottom: 8px; font-size: 12px; color: #6c757d;">
                                        <strong>Created:</strong> <?= date('M d, Y', strtotime($milestone['created_at'])) ?> | 
                                        <strong>Due:</strong> <?= $milestone['due_date'] ? date('M d, Y', strtotime($milestone['due_date'])) : 'Not set' ?>
                                    </div>
                                    <div style="margin-bottom: 8px; font-size: 12px; color: #6c757d;">
                                        <strong>Time:</strong> <?= round($milestone['spent_time'], 1) ?>h spent, <?= round($milestone['remaining_time'], 1) ?>h remaining
                                    </div>
                                    <div style="margin-bottom: 12px; font-size: 12px;">
                                        <strong>Task Types:</strong> 
                                        <?php if (!empty($milestone['task_types'])): ?>
                                            <?php foreach ($milestone['task_types'] as $type): ?>
                                                <span class="task-type-badge"><?= ucfirst($type['task_type']) ?>: <?= $type['count'] ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </div>
                                    <div style="margin-top: 8px; clear: both;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                        <small style="color: #6c757d; font-size: 10px;">Progress: <?= $progress ?>%</small>
                                        <div>
                                            <button type="button" class="btn btn-primary btn-xs" onclick="showMilestoneTasks(<?= $milestone['id'] ?>, 'main')" title="View Main Tasks" style="font-size: 10px; padding: 2px 4px; margin-right: 2px;">[<?= $milestone['main_completed'] ?>/<?= $milestone['main_total'] ?>]</button>
                                            <button type="button" class="btn btn-secondary btn-xs" onclick="showMilestoneTasks(<?= $milestone['id'] ?>, 'sub')" title="View Sub Tasks" style="font-size: 10px; padding: 2px 4px;">[<?= $milestone['sub_completed'] ?>/<?= $milestone['sub_total'] ?>]</button>
                                        </div>
                                    </div>
                                    <div style="background: #e9ecef; border-radius: 6px; height: 6px; overflow: hidden;">
                                        <div style="background: #28a745; height: 100%; width: <?= $progress ?>%; transition: width 0.3s ease; border-radius: 6px;"></div>
                                    </div>
                                    <div style="margin-top: 6px; text-align: right;">
                                        <small style="color: #6c757d; font-size: 9px;">Last updated: <?= $milestone['last_update'] ? date('M d, Y H:i', strtotime($milestone['last_update'])) : 'Never' ?></small>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No hold client milestones</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-12 col-sm-12">
        <div class="task-card">
            <div class="task-header" style="background: #fff3cd; color: #856404; display: flex; justify-content: space-between; align-items: center;">
                <h4 class="task-title"><i class="fas fa-pause-circle"></i> Hold - In-House Dev.</h4>
                <input type="text" id="searchHoldInhouse" placeholder="Search..." style="width: 200px; padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 12px;" onkeyup="filterHoldProjects('inhouse')">
            </div>
            <div class="task-body">
                <?php if (!empty($hold_inhouse)): ?>
                    <?php foreach ($hold_inhouse as $milestone): ?>
                        <?php 
                        $progress = $milestone['total_tasks'] > 0 ? round(($milestone['completed_tasks'] / $milestone['total_tasks']) * 100) : 0;
                        ?>
                        <div class="accordion-card" style="border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 8px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.07);">
                            <div class="accordion-header"
                                 onclick="toggleMilestoneAccordion('hold_milestone_inhouse_<?= $milestone['id'] ?>')"
                                 style="display: flex; flex-direction: column; gap: 4px; padding: 12px 15px; cursor: pointer; font-weight: 600; font-size: 12px; color: #000; background: #f7f7f8; user-select: none; transition: all 0.3s ease;">

                                <!-- ROW 1: TITLE + PROGRESS -->
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div class="milestone-info" style="display: flex; align-items: center; gap: 12px;">
                                        <img class="rounded" src="<?php echo get_image_url('staff', $milestone['photo']);?>" width="40" height="40" />
                                        <span><?= htmlspecialchars($milestone['title']) ?>
                                        <?php foreach ($milestone['incomplete_departments'] as $dept): ?>
                                        <span style="background: #ffa68a; color: black; padding: 2px 5px; border-radius: 15px; font-size: 9px;margin-left:10px;">
                                            <?= strtoupper(substr($dept['department_name'], 0, 1)) ?>. - <?= $dept['count'] ?>
                                        </span>
                                        <?php endforeach; ?>
                                        </span>
                                    </div>

                                    <div class="progress-info" style="display: flex; flex-direction: column; align-items: flex-start; gap: 4px;">
										<div style="display: flex; align-items: center; gap: 6px; font-size: 11px; height: 20px;">
											<a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick="showMilestoneFinancialsReport('<?=$milestone['id']?>')">
                                            <i class="fas fa-eye"></i>
											</a>
											<span style="background: #e8e8e9; color: black; padding: 4px 8px; border-radius: 12px; font-size: 12px;"><?= $progress ?>%</span>
											<i class="fas fa-chevron-down" style="transition: transform 0.3s ease;"></i>
										</div>
										<small style="color: #6c757d; font-size: 7px;">
											<?= date('M d, Y', strtotime($milestone['created_at'])) ?> |
										   <?= $milestone['due_date'] ? date('M d, Y', strtotime($milestone['due_date'])) : 'Not set' ?>
										</small>
									</div>
                                </div>
                            </div>
                            <div class="accordion-body" id="accordion-body-hold_milestone_inhouse_<?= $milestone['id'] ?>" style="max-height: 0; overflow: hidden; transition: max-height 0.4s ease-out, padding 0.4s ease-out; background-color: #ffffff;">
                                <div style="padding: 15px;">
                                    <div style="margin-bottom: 8px;">
                                        <strong>Owner:</strong> <?= htmlspecialchars($milestone['owner_name'] ?: 'Unassigned') ?> <br> 
                                        <strong>Priority:</strong> <?= htmlspecialchars($milestone['priority'] ?: 'Unassigned') ?> |  
                                        <strong>Status:</strong> <?= translate($milestone['status'] ?: 'Unassigned') ?>
                                        <?php 
                                         $indirect_cost = ($milestone['spent_time'] * $cost_per_hour  ?? 0);
                                        ?>
                                        <span class="spent-time" title="Direct Cost: ৳<?= number_format($milestone['total_fund']) ?> | Indirect Cost: ৳<?= number_format($indirect_cost) ?>">৳<?= number_format($milestone['total_fund']) ?> | ৳<?= number_format($indirect_cost) ?></span>
                                    </div>
                                    <div style="margin-bottom: 8px; font-size: 12px; color: #6c757d;">
                                        <strong>Created:</strong> <?= date('M d, Y', strtotime($milestone['created_at'])) ?> | 
                                        <strong>Due:</strong> <?= $milestone['due_date'] ? date('M d, Y', strtotime($milestone['due_date'])) : 'Not set' ?>
                                    </div>
                                    <div style="margin-bottom: 8px; font-size: 12px; color: #6c757d;">
                                        <strong>Time:</strong> <?= round($milestone['spent_time'], 1) ?>h spent, <?= round($milestone['remaining_time'], 1) ?>h remaining
                                    </div>
                                    <div style="margin-bottom: 12px; font-size: 12px;">
                                        <strong>Task Types:</strong> 
                                        <?php if (!empty($milestone['task_types'])): ?>
                                            <?php foreach ($milestone['task_types'] as $type): ?>
                                                <span class="task-type-badge"><?= ucfirst($type['task_type']) ?>: <?= $type['count'] ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </div>
                                    <div style="margin-top: 8px; clear: both;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                        <small style="color: #6c757d; font-size: 10px;">Progress: <?= $progress ?>%</small>
                                        <div>
                                            <button type="button" class="btn btn-primary btn-xs" onclick="showMilestoneTasks(<?= $milestone['id'] ?>, 'main')" title="View Main Tasks" style="font-size: 10px; padding: 2px 4px; margin-right: 2px;">[<?= $milestone['main_completed'] ?>/<?= $milestone['main_total'] ?>]</button>
                                            <button type="button" class="btn btn-secondary btn-xs" onclick="showMilestoneTasks(<?= $milestone['id'] ?>, 'sub')" title="View Sub Tasks" style="font-size: 10px; padding: 2px 4px;">[<?= $milestone['sub_completed'] ?>/<?= $milestone['sub_total'] ?>]</button>
                                        </div>
                                    </div>
                                    <div style="background: #e9ecef; border-radius: 6px; height: 6px; overflow: hidden;">
                                        <div style="background: #28a745; height: 100%; width: <?= $progress ?>%; transition: width 0.3s ease; border-radius: 6px;"></div>
                                    </div>
                                    <div style="margin-top: 6px; text-align: right;">
                                        <small style="color: #6c757d; font-size: 9px;">Last updated: <?= $milestone['last_update'] ? date('M d, Y H:i', strtotime($milestone['last_update'])) : 'Never' ?></small>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No hold in-house milestones</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div style="margin: 30px 0; text-align: center;">
    <button type="button" class="btn btn-outline-success btn-lg" onclick="toggleCompletedTasks()" id="toggleBtn" style="padding: 12px 24px; font-weight: 600; border-width: 2px; border-radius: 8px; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <i class="fas fa-chevron-down" id="toggleIcon"></i> Show Completed Tasks
    </button>
</div>

<div class="row" id="completedTasksSection" style="display: none;">
    <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="task-card">
            <div class="task-header completed">
                <h4 class="task-title"><i class="fas fa-check-circle"></i> Completed - Regular</h4>
            </div>
            <div class="task-body">
                <?php if (!empty($recent_regular)): ?>
                    <?php 
                    $grouped = [];
                    foreach ($recent_regular as $task) {
                        $milestone = $task['milestone_title'] ?: $task['milestone'];
                        $grouped[$milestone][] = $task;
                    }
                    ?>
                    <?php foreach ($grouped as $milestone => $tasks): ?>
                        <div class="milestone-group">
                            <div class="milestone-title"><?= htmlspecialchars($milestone) ?></div>
                            <?php foreach ($tasks as $index => $task): ?>
                                <div class="task-item completed">
                                    <?= ($index + 1) ?>. <?= htmlspecialchars($task['task_title']) ?>
                                    <span class="spent-time"><?= $task['spent_time'] ?>h</span>
                                    <br><small class="staff-name"><?= htmlspecialchars($task['staff_name']) ?></small>
									<button type="button" class="button btn btn-info btn-xs ml-1" onclick="viewTask('<?= $task['unique_id'] ?>')" title="View Task"><?= htmlspecialchars($task['unique_id']) ?></button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No recent completed tasks</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="task-card">
            <div class="task-header completed">
                <h4 class="task-title"><i class="fas fa-check-circle"></i> Completed - Client</h4>
            </div>
            <div class="task-body">
                <?php if (!empty($recent_client)): ?>
                    <?php 
                    $grouped = [];
                    foreach ($recent_client as $task) {
                        $milestone = $task['milestone_title'] ?: $task['milestone'];
                        $grouped[$milestone][] = $task;
                    }
                    ?>
                    <?php foreach ($grouped as $milestone => $tasks): ?>
                        <div class="milestone-group">
                            <div class="milestone-title"><?= htmlspecialchars($milestone) ?></div>
                            <?php foreach ($tasks as $index => $task): ?>
                                <div class="task-item completed">
                                    <?= ($index + 1) ?>. <?= htmlspecialchars($task['task_title']) ?>
                                    <span class="spent-time"><?= $task['spent_time'] ?>h</span>
                                    <br><small class="staff-name"><?= htmlspecialchars($task['staff_name']) ?></small>
									<button type="button" class="button btn btn-info btn-xs ml-1" onclick="viewTask('<?= $task['unique_id'] ?>')" title="View Task"><?= htmlspecialchars($task['unique_id']) ?></button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No recent completed tasks</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-12 col-sm-12">
        <div class="task-card">
            <div class="task-header completed">
                <h4 class="task-title"><i class="fas fa-check-circle"></i> Completed - In-House</h4>
            </div>
            <div class="task-body">
                <?php if (!empty($recent_inhouse)): ?>
                    <?php 
                    $grouped = [];
                    foreach ($recent_inhouse as $task) {
                        $milestone = $task['milestone_title'] ?: $task['milestone'];
                        $grouped[$milestone][] = $task;
                    }
                    ?>
                    <?php foreach ($grouped as $milestone => $tasks): ?>
                        <div class="milestone-group">
                            <div class="milestone-title"><?= htmlspecialchars($milestone) ?></div>
                            <?php foreach ($tasks as $index => $task): ?>
                                <div class="task-item completed">
                                    <?= ($index + 1) ?>. <?= htmlspecialchars($task['task_title']) ?>
                                    <span class="spent-time"><?= $task['spent_time'] ?>h</span>
                                    <br><small class="staff-name"><?= htmlspecialchars($task['staff_name']) ?></small>
									<button type="button" class="button btn btn-info btn-xs ml-1" onclick="viewTask('<?= $task['unique_id'] ?>')" title="View Task"><?= htmlspecialchars($task['unique_id']) ?></button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No recent completed tasks</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Milestone Tasks Modal -->
<div class="modal fade" id="milestoneTasksModal" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-xl custom-task-modal" role="document">
		<div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
			<div class="modal-header" style="background: #fff; color: #000; border-radius: 12px 12px 0 0;">
				<h4 class="modal-title" style="font-weight: 600; margin: 0;"><i class="fas fa-list-alt mr-2"></i> Milestone Tasks</h4>
				<button type="button" class="close" data-dismiss="modal" style="color: #000; opacity: 0.8; font-size: 24px;">&times;</button>
			</div>
			<div class="modal-body" style="padding: 25px; background: #f8f9fa;">
				<div id="milestoneTasksList">
					<div class="text-center" style="padding: 40px;">
						<i class="fas fa-spinner fa-spin" style="font-size: 24px; color: #667eea;"></i>
						<p style="margin-top: 15px; color: #6c757d;">Loading tasks...</p>
					</div>
				</div>
			</div>
			<div class="modal-footer" style="background: white; border-top: 1px solid #e9ecef; border-radius: 0 0 12px 12px;">
				<div id="milestoneProgress" style="width: 100%;">
					<!-- Progress bar will be inserted here -->
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
    width: 90% !important;
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

<!-- Milestone Financial Report View Modal -->
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
	<section class="panel" id='quick_view'></section>
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

// Get advance salary details for approval modal
	function showMilestoneFinancialsReport(id) {
		$.ajax({
			url: base_url + 'tasks_dashboard/milestone_report/' + id,
			type: 'GET',
			dataType: "html",
			success: function (data) {
				$('#quick_view').html(data);
				mfp_modal('#modal');
			}
		});
	}
	
/// Show milestone tasks in modal
function showMilestoneTasks(milestoneId, taskType = 'all') {
	$('#milestoneTasksModal').modal('show');
	
	$.ajax({
		url: base_url + 'tasks_dashboard/get_milestone_tasks',
		type: 'POST',
		data: {'milestone_id': milestoneId, 'task_type': taskType},
		dataType: 'json',
		success: function (tasks) {
			let html = '';
			if (tasks && tasks.length > 0) {
				// Group tasks by status
				const groupedTasks = {
					todo: [],
					in_progress: [],
					in_review: [],
					completed: [],
					hold: [],
					canceled: []
				};
				
				tasks.forEach(function(task) {
					const status = task.task_status || 'todo';
					if (groupedTasks[status]) {
						groupedTasks[status].push(task);
					} else {
						groupedTasks.todo.push(task);
					}
				});
				
				// Status configuration matching the view_tracker_issue_details.php
				const statusConfig = {
					todo: { title: 'To Do', color: '#ffbe0b', icon: 'fas fa-clipboard-list' },
					in_progress: { title: 'In Progress', color: '#3a86ff', icon: 'fas fa-play-circle' },
					in_review: { title: 'In Review', color: '#17a2b8', icon: 'fas fa-eye' },
					completed: { title: 'Completed', color: '#06d6a0', icon: 'fas fa-check-circle' },
					hold: { title: 'On Hold', color: '#fd7e14', icon: 'fas fa-pause-circle' },
					canceled: { title: 'Canceled', color: '#dc3545', icon: 'fas fa-times-circle' }
				};
				
				// Create accordion structure
				Object.keys(statusConfig).forEach(function(status) {
					if (groupedTasks[status].length > 0) {
						const config = statusConfig[status];
						html += `
							<div class="accordion-card" style="border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 8px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.07);">
								<div class="accordion-header" onclick="toggleTaskAccordion('task_${status}')" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 15px; cursor: pointer; font-weight: 600; font-size: 12px; color: #000; background: #f7f7f8; user-select: none; transition: all 0.3s ease;">
									<div class="status-info" style="display: flex; align-items: center; gap: 12px;">
										<div class="status-icon" style="width: 24px; height: 24px; border-radius: 50%; background: ${config.color}; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px;">
											<i class="${config.icon}"></i>
										</div>
										<span>${config.title}</span>
									</div>
									<div style="display: flex; align-items: center; gap: 8px;">
										${(() => {
											const departments = [...new Set(groupedTasks[status].map(task => task.department_name).filter(dept => dept))];
											return departments.map(dept => 
												`<span style="background: #e3f2fd; color: #1976d2; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 500;">${dept.substring(0, 3).toUpperCase()}.</span>`
											).join('');
										})()}
										<div class="item-count" style="background: #e8e8e9; color: black; padding: 4px 8px; border-radius: 12px; font-size: 12px;">${groupedTasks[status].length} items</div>
									</div>
								</div>
								<div class="accordion-body" id="accordion-body-task_${status}" style="max-height: 0; overflow: hidden; transition: max-height 0.4s ease-out, padding 0.4s ease-out; background-color: #ffffff;">
						`;
						
						groupedTasks[status].forEach(function(task) {
							html += `
								<div class="task-item"
									 onclick="viewTask('${task.unique_id}')"
									 style="cursor: pointer; display: flex; justify-content: space-between; align-items: center; padding: 6px 10px; border-bottom: 1px solid #f0f0f1; background: #f7f7f8; transition: all 0.2s ease; border-radius: 8px; margin-bottom: 3px;"
									 onmouseover="this.style.background='#e8e8e9'"
									 onmouseout="this.style.background='#f7f7f8'">

									<!-- Left side: ID + Title -->
									<div style="display: flex; align-items: center; gap: 10px; overflow: hidden;">
										<div class="task-id" style="width: 80px; flex-shrink: 0; font-weight: 600; color: #1976d2; font-size: 12px;">
											${task.unique_id}
										</div>
										<div class="task-id" style="width: 80px; flex-shrink: 0; font-weight: 600; color: #1976d2; font-size: 12px;">
											<img class="rounded" src="${base_url}uploads/images/staff/${task.photo || 'default.png'}" width="40" height="40" />
										</div>
										<div class="task-title" style="font-weight: 600; color: #2d3748; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 12px;">
											${task.task_title}
										</div>
									</div>

									<!-- Right side: Department + Hour -->
									<div style="display: flex; align-items: center; gap: 5px; flex-shrink: 0;">
										<div class="task-estimate" style="padding: 3px 8px; border: 1px solid #cccccc; background: #f2f2f4; color: #000; border-radius: 20px; font-size: 11px; min-width: 40px; text-align: center;">
											${task.category || 'N/A'}
										</div>
										<div style="padding: 2px 6px; background: #e3f2fd; color: #1976d2; border-radius: 12px; font-size: 9px; font-weight: 500;">
											${task.department_name ? task.department_name.substring(0, 3).toUpperCase() + '.' : 'N/A'}
										</div>
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
				// Calculate progress
				const totalTasks = tasks.length;
				const completedTasks = groupedTasks.completed.length;
				const inProgressTasks = groupedTasks.in_progress.length;
				const inReviewTasks = groupedTasks.in_review.length;
				const todoTasks = groupedTasks.todo.length;
				const holdTasks = groupedTasks.hold.length;
				const canceledTasks = groupedTasks.canceled.length;
				const progressPercentage = totalTasks > 0 ? Math.round((completedTasks / totalTasks) * 100) : 0;
				
				// Create progress bar
				const progressHtml = `
					<div style="margin-bottom: 15px;">
						<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
							<h6 style="margin: 0; color: #495057; font-weight: 600;">Overall Progress</h6>
							<span style="color: #6c757d; font-size: 14px; font-weight: 500;">${completedTasks}/${totalTasks} tasks completed (${progressPercentage}%)</span>
						</div>
						<div style="background: #e9ecef; border-radius: 10px; height: 12px; overflow: hidden;">
							<div style="background: linear-gradient(90deg, #06d6a0 0%, #20c997 100%); height: 100%; width: ${progressPercentage}%; transition: width 0.3s ease; border-radius: 10px;"></div>
						</div>
					</div>
					<div style="display: flex; justify-content: space-around; text-align: center; flex-wrap: wrap; gap: 10px;">
						<div>
							<div style="color: #06d6a0; font-weight: 600; font-size: 16px;">${completedTasks}</div>
							<div style="color: #6c757d; font-size: 11px;">Completed</div>
						</div>
						<div>
							<div style="color: #3a86ff; font-weight: 600; font-size: 16px;">${inProgressTasks}</div>
							<div style="color: #6c757d; font-size: 11px;">In Progress</div>
						</div>
						<div>
							<div style="color: #17a2b8; font-weight: 600; font-size: 16px;">${inReviewTasks}</div>
							<div style="color: #6c757d; font-size: 11px;">In Review</div>
						</div>
						<div>
							<div style="color: #ffbe0b; font-weight: 600; font-size: 16px;">${todoTasks}</div>
							<div style="color: #6c757d; font-size: 11px;">To Do</div>
						</div>
						${holdTasks > 0 ? `<div><div style="color: #fd7e14; font-weight: 600; font-size: 16px;">${holdTasks}</div><div style="color: #6c757d; font-size: 11px;">On Hold</div></div>` : ''}
						${canceledTasks > 0 ? `<div><div style="color: #dc3545; font-weight: 600; font-size: 16px;">${canceledTasks}</div><div style="color: #6c757d; font-size: 11px;">Canceled</div></div>` : ''}
					</div>
				`;
				$('#milestoneProgress').html(progressHtml);
			} else {
				html = '<div class="text-center text-muted" style="padding: 40px;"><i class="fas fa-tasks" style="font-size: 48px; color: #dee2e6; margin-bottom: 15px;"></i><p>No tasks found for this milestone</p></div>';
				$('#milestoneProgress').html('<div style="text-align: center; color: #6c757d;">No progress data available</div>');
			}
			$('#milestoneTasksList').html(html);
		},
		error: function() {
			$('#milestoneTasksList').html('<div class="text-center text-danger" style="padding: 40px;"><i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 15px;"></i><p>Error loading tasks</p></div>');
			$('#milestoneProgress').html('<div style="text-align: center; color: #dc3545;">Unable to load progress data</div>');
		}
	});
}

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

// Toggle milestone accordion sections
function toggleMilestoneAccordion(id) {
	const header = document.querySelector(`[onclick="toggleMilestoneAccordion('${id}')"]`);
	const body = document.getElementById('accordion-body-' + id);
	const chevron = header.querySelector('.fas');
	
	// Close all other accordions first
	document.querySelectorAll('.accordion-body').forEach(otherBody => {
		if (otherBody.id !== 'accordion-body-' + id) {
			const otherHeader = document.querySelector(`[onclick*="${otherBody.id.replace('accordion-body-', '')}"]`);
			const otherChevron = otherHeader ? otherHeader.querySelector('.fas') : null;
			
			otherBody.style.maxHeight = '0px';
			otherBody.style.padding = '0px';
			if (otherHeader) otherHeader.style.background = '#f7f7f8';
			if (otherChevron) otherChevron.style.transform = 'rotate(0deg)';
		}
	});
	
	if (body.style.maxHeight && body.style.maxHeight !== '0px') {
		body.style.maxHeight = '0px';
		body.style.padding = '0px';
		header.style.background = '#f7f7f8';
		chevron.style.transform = 'rotate(0deg)';
	} else {
		body.style.maxHeight = 'none';
		body.style.padding = '0px';
		header.style.background = '#e8e8e9';
		chevron.style.transform = 'rotate(180deg)';
		
		// Ensure proper height calculation
		setTimeout(() => {
			body.style.maxHeight = body.scrollHeight + 20 + 'px';
		}, 10);
	}
}

// Toggle completed tasks section
function toggleCompletedTasks() {
	const section = $('#completedTasksSection');
	const btn = $('#toggleBtn');
	const icon = $('#toggleIcon');
	
	if (section.is(':visible')) {
		section.slideUp(300);
		btn.html('<i class="fas fa-chevron-down" id="toggleIcon"></i> Show Completed Tasks');
		btn.removeClass('btn-success').addClass('btn-outline-success');
	} else {
		section.slideDown(300);
		btn.html('<i class="fas fa-chevron-up" id="toggleIcon"></i> Hide Completed Tasks');
		btn.removeClass('btn-outline-success').addClass('btn-success');
	}
}

// Toggle planning milestones section
function togglePlanningMilestones() {
	const section = $('#planningMilestonesSection');
	const btn = $('#togglePlanningBtn');
	const icon = $('#togglePlanningIcon');
	
	if (section.is(':visible')) {
		section.slideUp(300);
		btn.html('<i class="fas fa-chevron-down" id="togglePlanningIcon"></i> Show Planning Stage Milestones');
		btn.removeClass('btn-info').addClass('btn-outline-info');
	} else {
		section.slideDown(300);
		btn.html('<i class="fas fa-chevron-up" id="togglePlanningIcon"></i> Hide Planning Stage Milestones');
		btn.removeClass('btn-outline-info').addClass('btn-info');
	}
}

// Toggle hold milestones section
function toggleHoldMilestones() {
	const section = $('#holdMilestonesSection');
	const btn = $('#toggleHoldBtn');
	const icon = $('#toggleHoldIcon');
	
	if (section.is(':visible')) {
		section.slideUp(300);
		btn.html('<i class="fas fa-chevron-down" id="toggleHoldIcon"></i> Show Hold Status Milestones');
		btn.removeClass('btn-warning').addClass('btn-outline-warning');
	} else {
		section.slideDown(300);
		btn.html('<i class="fas fa-chevron-up" id="toggleHoldIcon"></i> Hide Hold Status Milestones');
		btn.removeClass('btn-outline-warning').addClass('btn-warning');
	}
}

// Filter projects by search term for individual divs
function filterProjects(type) {
	const searchInput = document.getElementById('search' + type.charAt(0).toUpperCase() + type.slice(1));
	const searchTerm = searchInput.value.toLowerCase();
	
	// Get the specific task card container for this type
	let containerSelector;
	if (type === 'regular') {
		containerSelector = '.col-lg-4:first-child .task-body';
	} else if (type === 'client') {
		containerSelector = '.col-lg-4:nth-child(2) .task-body';
	} else if (type === 'inhouse') {
		containerSelector = '.col-lg-4:nth-child(3) .task-body';
	}
	
	const container = document.querySelector(containerSelector);
	if (!container) return;
	
	const cards = container.querySelectorAll('.accordion-card');
	
	cards.forEach(card => {
		const milestoneText = card.querySelector('.milestone-info span');
		const accordionBody = card.querySelector('.accordion-body');
		
		if (milestoneText) {
			const title = milestoneText.textContent.toLowerCase();
			let owner = '';
			
			// Get owner text from accordion body if it exists
			if (accordionBody) {
				const ownerElement = accordionBody.querySelector('strong');
				if (ownerElement && ownerElement.nextSibling) {
					owner = ownerElement.nextSibling.textContent.toLowerCase();
				}
			}
			
			if (title.includes(searchTerm) || owner.includes(searchTerm)) {
				card.style.display = 'block';
			} else {
				card.style.display = 'none';
			}
		}
	});
}

// Filter planning projects by search term
function filterPlanningProjects(type) {
	const searchInput = document.getElementById('searchPlanning' + type.charAt(0).toUpperCase() + type.slice(1));
	const searchTerm = searchInput.value.toLowerCase();
	
	// Get the specific planning task card container for this type
	let containerSelector;
	if (type === 'regular') {
		containerSelector = '#planningMilestonesSection .col-lg-4:first-child .task-body';
	} else if (type === 'client') {
		containerSelector = '#planningMilestonesSection .col-lg-4:nth-child(2) .task-body';
	} else if (type === 'inhouse') {
		containerSelector = '#planningMilestonesSection .col-lg-4:nth-child(3) .task-body';
	}
	
	const container = document.querySelector(containerSelector);
	if (!container) return;
	
	const cards = container.querySelectorAll('.accordion-card');
	
	cards.forEach(card => {
		const milestoneText = card.querySelector('.milestone-info span');
		const accordionBody = card.querySelector('.accordion-body');
		
		if (milestoneText) {
			const title = milestoneText.textContent.toLowerCase();
			let owner = '';
			
			// Get owner text from accordion body if it exists
			if (accordionBody) {
				const ownerElement = accordionBody.querySelector('strong');
				if (ownerElement && ownerElement.nextSibling) {
					owner = ownerElement.nextSibling.textContent.toLowerCase();
				}
			}
			
			if (title.includes(searchTerm) || owner.includes(searchTerm)) {
				card.style.display = 'block';
			} else {
				card.style.display = 'none';
			}
		}
	});
}

// Filter hold projects by search term
function filterHoldProjects(type) {
	const searchInput = document.getElementById('searchHold' + type.charAt(0).toUpperCase() + type.slice(1));
	const searchTerm = searchInput.value.toLowerCase();
	
	// Get the specific hold task card container for this type
	let containerSelector;
	if (type === 'regular') {
		containerSelector = '#holdMilestonesSection .col-lg-4:first-child .task-body';
	} else if (type === 'client') {
		containerSelector = '#holdMilestonesSection .col-lg-4:nth-child(2) .task-body';
	} else if (type === 'inhouse') {
		containerSelector = '#holdMilestonesSection .col-lg-4:nth-child(3) .task-body';
	}
	
	const container = document.querySelector(containerSelector);
	if (!container) return;
	
	const cards = container.querySelectorAll('.accordion-card');
	
	cards.forEach(card => {
		const milestoneText = card.querySelector('.milestone-info span');
		const accordionBody = card.querySelector('.accordion-body');
		
		if (milestoneText) {
			const title = milestoneText.textContent.toLowerCase();
			let owner = '';
			
			// Get owner text from accordion body if it exists
			if (accordionBody) {
				const ownerElement = accordionBody.querySelector('strong');
				if (ownerElement && ownerElement.nextSibling) {
					owner = ownerElement.nextSibling.textContent.toLowerCase();
				}
			}
			
			if (title.includes(searchTerm) || owner.includes(searchTerm)) {
				card.style.display = 'block';
			} else {
				card.style.display = 'none';
			}
		}
	});
}
</script>