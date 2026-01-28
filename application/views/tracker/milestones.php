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

/* Overdue milestone styling */
.overdue-row {
	background-color: #ffebee !important;
	border-left: 4px solid #f44336 !important;
}

.overdue-row:hover {
	background-color: #ffcdd2 !important;
}

.overdue-row td {
	color: #d32f2f !important;
	font-weight: 500;
}
</style>

<div class="row" style="height: calc(108vh);">
	<div class="col-md-3">
        <?php include 'sidebar.php'; ?>
    </div>
    <div class="col-md-9" style="height: 100%; overflow-y: auto;">
	
		<div class="panel">
			<header class="panel-heading d-flex justify-content-between align-items-center">
				<h4 class="panel-title"><?= html_escape($department->title) ?> - <?= translate('milestones') ?></h4>
				<?php if (get_permission('tracker_milestone', 'is_add')): ?>
				<div class="panel-btn">
					<a href="javascript:void(0);" class="btn btn-sm btn-primary" onclick="getAddModal(<?= $department->id ?>)">
						<i class="fa fa-plus-circle"></i> <?= translate('add') ?>
					</a>
				</div>
				<?php endif; ?>
			</header>

			<div class="panel-body" style="padding: 0px!important;">
				<div class="tabs-custom">
					<ul class="nav nav-tabs">
						<li class="active">
							<a href="#all" data-toggle="tab"><i class="fas fa-list"></i> All</a>
						</li>
						<li>
							<a href="#in_progress" data-toggle="tab"><i class="fas fa-play-circle"></i> In Progress</a>
						</li>
						<li>
							<a href="#hold" data-toggle="tab"><i class="fas fa-pause-circle"></i> Hold</a>
						</li>
						<li>
							<a href="#completed" data-toggle="tab"><i class="fas fa-check-circle"></i> Completed</a>
						</li>
					</ul>
					<div class="tab-content">
						<div id="all" class="tab-pane active">
							<div class="row mb-3">
								<div class="col-md-12">
									<ul class="nav nav-pills" style="margin-bottom: 15px;">
										<li class="active"><a href="javascript:void(0)" onclick="filterByType('all', '')" data-type="">All Types</a></li>
										<li><a href="javascript:void(0)" onclick="filterByType('all', 'regular')" data-type="regular">Regular</a></li>
										<li><a href="javascript:void(0)" onclick="filterByType('all', 'in_house')" data-type="in_house">In House</a></li>
										<li><a href="javascript:void(0)" onclick="filterByType('all', 'client')" data-type="client">Client</a></li>
									</ul>
								</div>
							</div>
							<div class="table-responsive">
								<table class="table table-bordered table-hover table-condensed table_default" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th><?= translate('type') ?></th>
											<th>Stage</th>
											<th><?= translate('title') ?></th>
											<th><?= translate('assigned_to') ?></th>
											<th><?= translate('description') ?></th>
											<th>Task Progress</th>
											<th><?= translate('priority') ?></th>
											<th><?= translate('status') ?></th>
											<th>Verification</th>
											<th><?= translate('due_date') ?></th>
											<th><?= translate('action') ?></th>
										</tr>
									</thead>
									<tbody>
						<?php foreach ($milestones as $row): ?>
							<?php
								// Check if milestone is overdue (past due date and not completed)
								$is_overdue = (strtotime($row->due_date) < strtotime(date('Y-m-d')) && !in_array($row->status, ['completed', 'done', 'solved']));
								$row_class = $is_overdue ? 'overdue-row' : ''; // Custom overdue styling
							?>
							<tr class="<?= $row_class ?>" data-type="<?= $row->type ?>">
								<td><?= translate($row->type) ?></td>
								<td><span class="badge badge-<?= $row->stage == 'planning' ? 'info' : 'primary' ?>"><?= ucfirst($row->stage) ?></span></td>
								<td><?= translate($row->title) ?></td>
								<td>
									<?php 
										$getStaff = $this->db->select('name')
															 ->where('id', $row->assigned_to)
															 ->get('staff')
															 ->row_array();
										echo $getStaff['name'] ?? '-';
									?>
								</td>
								<td><?= translate($row->description) ?></td>
								<td>
									<?php
										// Get task progress for this milestone
										$total_tasks = $this->db->select('COUNT(*) as count')
											->from('tracker_issues')
											->where('milestone', $row->id)
											->get()->row()->count;
										
										$completed_tasks = $this->db->select('COUNT(*) as count')
											->from('tracker_issues')
											->where('milestone', $row->id)
											->where_in('task_status', ['completed', 'done', 'solved'])
											->get()->row()->count;
										
										if ($total_tasks > 0) {
											$progress_percentage = round(($completed_tasks / $total_tasks) * 100);
											$progress_class = $progress_percentage == 100 ? 'success' : ($progress_percentage >= 50 ? 'warning' : 'danger');
											echo "<div class='progress' style='margin-bottom: 0;'>";
											echo "<div class='progress-bar progress-bar-{$progress_class}' style='width: {$progress_percentage}%'>{$completed_tasks}/{$total_tasks}</div>";
											echo "</div>";
											echo "<small class='text-muted'>{$progress_percentage}% Complete</small>";
										} else {
											echo "<span class='text-muted'>No tasks</span>";
										}
									?>
								</td>
								<td>
									<span class="badge badge-info"><?= translate($row->priority) ?></span>
								</td>
								<td>
									<span class="badge badge-info"><?= translate($row->status) ?></span>
								</td>
								<td>
									<?php if (in_array($row->status, ['completed', 'done', 'solved'])): ?>
										<?php if (in_array(loggedin_role_id(), [1, 2, 3, 5, 8])): ?>
											<?php if (isset($row->is_verified) && $row->is_verified): ?>
												<button class="btn btn-sm btn-success" onclick="showVerificationDetails(<?= $row->id ?>)">
													<i class="fas fa-check-circle"></i> Verified
												</button>
												<?php 
													$badge_check = $this->db->where(['milestone_id' => $row->id, 'staff_id' => $row->assigned_to])->get('milestone_champion_badges')->row();
													if ($badge_check): 
												?>
													<span class="badge badge-warning ml-1"><i class="fas fa-trophy"></i> Awarded</span>
												<?php else: ?>
													<button class="btn btn-sm btn-warning ml-1" onclick="showChampionBadgeModal(<?= $row->id ?>)">
														<i class="fas fa-trophy"></i> Award
													</button>
												<?php endif; ?>
											<?php else: ?>
												<button class="btn btn-sm btn-warning" onclick="showVerificationModal(<?= $row->id ?>)">
													<i class="fas fa-check"></i> Verify
												</button>
											<?php endif; ?>
										<?php else: ?>
											<?php if (isset($row->is_verified) && $row->is_verified): ?>
												<button class="btn btn-sm btn-success" onclick="showVerificationDetails(<?= $row->id ?>)">
													<i class="fas fa-check-circle"></i> Verified
												</button>
											<?php else: ?>
												<span class="badge badge-secondary">Pending Verification</span>
											<?php endif; ?>
										<?php endif; ?>
									<?php else: ?>
										<span class="text-muted">-</span>
									<?php endif; ?>
								</td>
								<td><?= date('d M Y', strtotime($row->due_date)) ?></td>
								<td>
									<a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick="getEdit(<?= $row->id ?>)">
										<i class="fa fa-pen-nib"></i>
									</a>
									<a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick="deleteMilestone(<?= $row->id ?>)">
										<i class="fa fa-trash"></i>
									</a>
								</td>
							</tr>
						<?php endforeach; ?>

											<?php if (empty($milestones)): ?>
												<tr>
												<td colspan="11" class="text-center text-muted"><?= translate('no_data_found') ?></td>
												</tr>
											<?php endif; ?>
										</tbody>
									</table>
								</div>
							</div>
							
							<div class="tab-pane" id="in_progress">
								<div class="row mb-3">
									<div class="col-md-12">
										<ul class="nav nav-pills" style="margin-bottom: 15px;">
											<li class="active"><a href="javascript:void(0)" onclick="filterByType('in_progress', '')" data-type="">All Types</a></li>
											<li><a href="javascript:void(0)" onclick="filterByType('in_progress', 'regular')" data-type="regular">Regular</a></li>
											<li><a href="javascript:void(0)" onclick="filterByType('in_progress', 'in_house')" data-type="in_house">In House</a></li>
											<li><a href="javascript:void(0)" onclick="filterByType('in_progress', 'client')" data-type="client">Client</a></li>
										</ul>
									</div>
								</div>
								<div class="table-responsive">
									<table class="table table-bordered table-hover table-condensed table_default" cellspacing="0" width="100%">
										<thead>
											<tr>
												<th><?= translate('type') ?></th>
												<th>Stage</th>
												<th><?= translate('title') ?></th>
												<th><?= translate('assigned_to') ?></th>
												<th><?= translate('description') ?></th>
												<th>Task Progress</th>
												<th><?= translate('priority') ?></th>
												<th><?= translate('status') ?></th>
												<th>Verification</th>
												<th><?= translate('due_date') ?></th>
												<th><?= translate('action') ?></th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($milestones as $row): ?>
												<?php if (in_array($row->status, ['in_progress', 'in progress', 'ongoing'])): ?>
													<?php
														$is_overdue = (strtotime($row->due_date) < strtotime(date('Y-m-d')) && !in_array($row->status, ['completed', 'done', 'solved']));
														$row_class = $is_overdue ? 'overdue-row' : '';
													?>
													<tr class="<?= $row_class ?>" data-type="<?= $row->type ?>">
														<td><?= translate($row->type) ?></td>
														<td><span class="badge badge-<?= $row->stage == 'planning' ? 'info' : 'primary' ?>"><?= ucfirst($row->stage) ?></span></td>
														<td><?= translate($row->title) ?></td>
														<td>
															<?php 
																$getStaff = $this->db->select('name')->where('id', $row->assigned_to)->get('staff')->row_array();
																echo $getStaff['name'] ?? '-';
															?>
														</td>
														<td><?= translate($row->description) ?></td>
														<td>
															<?php
																$total_tasks = $this->db->select('COUNT(*) as count')->from('tracker_issues')->where('milestone', $row->id)->get()->row()->count;
																$completed_tasks = $this->db->select('COUNT(*) as count')->from('tracker_issues')->where('milestone', $row->id)->where_in('task_status', ['completed', 'done', 'solved'])->get()->row()->count;
																if ($total_tasks > 0) {
																	$progress_percentage = round(($completed_tasks / $total_tasks) * 100);
																	$progress_class = $progress_percentage == 100 ? 'success' : ($progress_percentage >= 50 ? 'warning' : 'danger');
																	echo "<div class='progress' style='margin-bottom: 0;'><div class='progress-bar progress-bar-{$progress_class}' style='width: {$progress_percentage}%'>{$completed_tasks}/{$total_tasks}</div></div><small class='text-muted'>{$progress_percentage}% Complete</small>";
																} else {
																	echo "<span class='text-muted'>No tasks</span>";
																}
															?>
														</td>
														<td><span class="badge badge-info"><?= translate($row->priority) ?></span></td>
														<td><span class="badge badge-info"><?= translate($row->status) ?></span></td>
														<td><span class="text-muted">-</span></td>
														<td><?= date('d M Y', strtotime($row->due_date)) ?></td>
														<td>
															<a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick="getEdit(<?= $row->id ?>)"><i class="fa fa-pen-nib"></i></a>
															<a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick="deleteMilestone(<?= $row->id ?>)"><i class="fa fa-trash"></i></a>
														</td>
													</tr>
												<?php endif; ?>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</div>
							
							<div class="tab-pane" id="hold">
								<div class="row mb-3">
									<div class="col-md-12">
										<ul class="nav nav-pills" style="margin-bottom: 15px;">
											<li class="active"><a href="javascript:void(0)" onclick="filterByType('hold', '')" data-type="">All Types</a></li>
											<li><a href="javascript:void(0)" onclick="filterByType('hold', 'regular')" data-type="regular">Regular</a></li>
											<li><a href="javascript:void(0)" onclick="filterByType('hold', 'in_house')" data-type="in_house">In House</a></li>
											<li><a href="javascript:void(0)" onclick="filterByType('hold', 'client')" data-type="client">Client</a></li>
										</ul>
									</div>
								</div>
								<div class="table-responsive">
									<table class="table table-bordered table-hover table-condensed table_default" cellspacing="0" width="100%">
										<thead>
											<tr>
												<th><?= translate('type') ?></th>
												<th>Stage</th>
												<th><?= translate('title') ?></th>
												<th><?= translate('assigned_to') ?></th>
												<th><?= translate('description') ?></th>
												<th>Task Progress</th>
												<th><?= translate('priority') ?></th>
												<th><?= translate('status') ?></th>
												<th>Verification</th>
												<th><?= translate('due_date') ?></th>
												<th><?= translate('action') ?></th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($milestones as $row): ?>
												<?php if (in_array($row->status, ['hold', 'on_hold', 'paused'])): ?>
													<?php
														$is_overdue = (strtotime($row->due_date) < strtotime(date('Y-m-d')) && !in_array($row->status, ['completed', 'done', 'solved']));
														$row_class = $is_overdue ? 'overdue-row' : '';
													?>
													<tr class="<?= $row_class ?>" data-type="<?= $row->type ?>">
														<td><?= translate($row->type) ?></td>
														<td><span class="badge badge-<?= $row->stage == 'planning' ? 'info' : 'primary' ?>"><?= ucfirst($row->stage) ?></span></td>
														<td><?= translate($row->title) ?></td>
														<td>
															<?php 
																$getStaff = $this->db->select('name')->where('id', $row->assigned_to)->get('staff')->row_array();
																echo $getStaff['name'] ?? '-';
															?>
														</td>
														<td><?= translate($row->description) ?></td>
														<td>
															<?php
																$total_tasks = $this->db->select('COUNT(*) as count')->from('tracker_issues')->where('milestone', $row->id)->get()->row()->count;
																$completed_tasks = $this->db->select('COUNT(*) as count')->from('tracker_issues')->where('milestone', $row->id)->where_in('task_status', ['completed', 'done', 'solved'])->get()->row()->count;
																if ($total_tasks > 0) {
																	$progress_percentage = round(($completed_tasks / $total_tasks) * 100);
																	$progress_class = $progress_percentage == 100 ? 'success' : ($progress_percentage >= 50 ? 'warning' : 'danger');
																	echo "<div class='progress' style='margin-bottom: 0;'><div class='progress-bar progress-bar-{$progress_class}' style='width: {$progress_percentage}%'>{$completed_tasks}/{$total_tasks}</div></div><small class='text-muted'>{$progress_percentage}% Complete</small>";
																} else {
																	echo "<span class='text-muted'>No tasks</span>";
																}
															?>
														</td>
														<td><span class="badge badge-info"><?= translate($row->priority) ?></span></td>
														<td><span class="badge badge-info"><?= translate($row->status) ?></span></td>
														<td><span class="text-muted">-</span></td>
														<td><?= date('d M Y', strtotime($row->due_date)) ?></td>
														<td>
															<a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick="getEdit(<?= $row->id ?>)"><i class="fa fa-pen-nib"></i></a>
															<a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick="deleteMilestone(<?= $row->id ?>)"><i class="fa fa-trash"></i></a>
														</td>
													</tr>
												<?php endif; ?>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</div>
							
							<div class="tab-pane" id="completed">
								<div class="row mb-3">
									<div class="col-md-12">
										<ul class="nav nav-pills" style="margin-bottom: 15px;">
											<li class="active"><a href="javascript:void(0)" onclick="filterByType('completed', '')" data-type="">All Types</a></li>
											<li><a href="javascript:void(0)" onclick="filterByType('completed', 'regular')" data-type="regular">Regular</a></li>
											<li><a href="javascript:void(0)" onclick="filterByType('completed', 'in_house')" data-type="in_house">In House</a></li>
											<li><a href="javascript:void(0)" onclick="filterByType('completed', 'client')" data-type="client">Client</a></li>
										</ul>
									</div>
								</div>
								<div class="table-responsive">
									<table class="table table-bordered table-hover table-condensed table_default" cellspacing="0" width="100%">
										<thead>
											<tr>
												<th><?= translate('type') ?></th>
												<th>Stage</th>
												<th><?= translate('title') ?></th>
												<th><?= translate('assigned_to') ?></th>
												<th><?= translate('description') ?></th>
												<th>Task Progress</th>
												<th><?= translate('priority') ?></th>
												<th><?= translate('status') ?></th>
												<th>Verification</th>
												<th><?= translate('due_date') ?></th>
												<th><?= translate('action') ?></th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($milestones as $row): ?>
												<?php if (in_array($row->status, ['completed', 'done', 'solved'])): ?>
													<tr data-type="<?= $row->type ?>">
														<td><?= translate($row->type) ?></td>
														<td><span class="badge badge-<?= $row->stage == 'planning' ? 'info' : 'primary' ?>"><?= ucfirst($row->stage) ?></span></td>
														<td><?= translate($row->title) ?></td>
														<td>
															<?php 
																$getStaff = $this->db->select('name')->where('id', $row->assigned_to)->get('staff')->row_array();
																echo $getStaff['name'] ?? '-';
															?>
														</td>
														<td><?= translate($row->description) ?></td>
														<td>
															<?php
																$total_tasks = $this->db->select('COUNT(*) as count')->from('tracker_issues')->where('milestone', $row->id)->get()->row()->count;
																$completed_tasks = $this->db->select('COUNT(*) as count')->from('tracker_issues')->where('milestone', $row->id)->where_in('task_status', ['completed', 'done', 'solved'])->get()->row()->count;
																if ($total_tasks > 0) {
																	$progress_percentage = round(($completed_tasks / $total_tasks) * 100);
																	$progress_class = $progress_percentage == 100 ? 'success' : ($progress_percentage >= 50 ? 'warning' : 'danger');
																	echo "<div class='progress' style='margin-bottom: 0;'><div class='progress-bar progress-bar-{$progress_class}' style='width: {$progress_percentage}%'>{$completed_tasks}/{$total_tasks}</div></div><small class='text-muted'>{$progress_percentage}% Complete</small>";
																} else {
																	echo "<span class='text-muted'>No tasks</span>";
																}
															?>
														</td>
														<td><span class="badge badge-info"><?= translate($row->priority) ?></span></td>
														<td><span class="badge badge-info"><?= translate($row->status) ?></span></td>
														<td>
															<?php if (in_array(loggedin_role_id(), [1, 2, 3, 5, 8])): ?>
																<?php if (isset($row->is_verified) && $row->is_verified): ?>
																	<button class="btn btn-sm btn-success" onclick="showVerificationDetails(<?= $row->id ?>)"><i class="fas fa-check-circle"></i> Verified</button>
																	<?php 
																		$badge_check = $this->db->where(['milestone_id' => $row->id, 'staff_id' => $row->assigned_to])->get('milestone_champion_badges')->row();
																		if ($badge_check): 
																	?>
																		<span class="badge badge-warning ml-1"><i class="fas fa-trophy"></i> Awarded</span>
																	<?php else: ?>
																		<button class="btn btn-sm btn-warning ml-1" onclick="showChampionBadgeModal(<?= $row->id ?>)"><i class="fas fa-trophy"></i> Award</button>
																	<?php endif; ?>
																<?php else: ?>
																	<button class="btn btn-sm btn-warning" onclick="showVerificationModal(<?= $row->id ?>)"><i class="fas fa-check"></i> Verify</button>
																<?php endif; ?>
															<?php else: ?>
																<?php if (isset($row->is_verified) && $row->is_verified): ?>
																	<button class="btn btn-sm btn-success" onclick="showVerificationDetails(<?= $row->id ?>)"><i class="fas fa-check-circle"></i> Verified</button>
																<?php else: ?>
																	<span class="badge badge-secondary">Pending Verification</span>
																<?php endif; ?>
															<?php endif; ?>
														</td>
														<td><?= date('d M Y', strtotime($row->due_date)) ?></td>
														<td>
															<a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick="getEdit(<?= $row->id ?>)"><i class="fa fa-pen-nib"></i></a>
															<a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick="deleteMilestone(<?= $row->id ?>)"><i class="fa fa-trash"></i></a>
														</td>
													</tr>
												<?php endif; ?>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>

			</div>
		</div>

		<!-- Edit Component Modal -->
		<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
			<section class="panel" id="quick_view"></section>
		</div>

		<!-- Verification Modal -->
		<div id="verificationModal" class="zoom-anim-dialog modal-block modal-block-primary mfp-hide">
			<section class="panel">
				<header class="panel-heading">
					<h4 class="panel-title"><i class="fas fa-check-circle"></i> Verify Milestone</h4>
				</header>
				<div class="panel-body">
					<form id="verificationForm">
						<input type="hidden" id="verify_milestone_id" name="milestone_id">
						<div class="form-group">
							<label class="control-label">Verification Remarks <span class="required">*</span></label>
							<textarea name="verification_remarks" id="verification_remarks" class="form-control" rows="4" placeholder="Enter verification remarks..." required></textarea>
						</div>
					</form>
				</div>
				<footer class="panel-footer">
					<div class="row">
						<div class="col-md-12 text-right">
							<button type="button" class="btn btn-success" onclick="submitVerification()">
								<i class="fas fa-check"></i> Verify Milestone
							</button>
							<button class="btn btn-default modal-dismiss">Cancel</button>
						</div>
					</div>
				</footer>
			</section>
		</div>

		<!-- Verification Details Modal -->
		<div id="verificationDetailsModal" class="zoom-anim-dialog modal-block modal-block-primary mfp-hide">
			<section class="panel">
				<header class="panel-heading">
					<h4 class="panel-title"><i class="fas fa-info-circle"></i> Verification Details</h4>
				</header>
				<div class="panel-body" id="verificationDetailsContent">
					<!-- Content will be loaded here -->
				</div>
				<footer class="panel-footer">
					<div class="row">
						<div class="col-md-12 text-right">
							<button class="btn btn-default modal-dismiss">Close</button>
						</div>
					</div>
				</footer>
			</section>
		</div>

		<!-- Champion Badge Modal -->
		<div id="championBadgeModal" class="zoom-anim-dialog modal-block modal-block-primary mfp-hide">
			<section class="panel">
				<header class="panel-heading">
					<h4 class="panel-title"><i class="fas fa-trophy"></i> Award Champion Badge</h4>
				</header>
				<div class="panel-body">
					<form id="championBadgeForm">
						<input type="hidden" id="badge_milestone_id" name="milestone_id">
						<div class="form-group">
							<label class="control-label">Reason for Award <span class="required">*</span></label>
							<textarea name="badge_reason" id="badge_reason" class="form-control" rows="4" placeholder="Describe why this person deserves a champion badge..." required></textarea>
						</div>
					</form>
				</div>
				<footer class="panel-footer">
					<div class="row">
						<div class="col-md-12 text-right">
							<button type="button" class="btn btn-warning" onclick="submitChampionBadge()">
								<i class="fas fa-trophy"></i> Award Badge
							</button>
							<button class="btn btn-default modal-dismiss">Cancel</button>
						</div>
					</div>
				</footer>
			</section>
		</div>

		<!-- Add Component Modal -->
		<div id="milestoneAddModal" class="zoom-anim-dialog modal-block modal-block-primary mfp-hide">
			<section class="panel">
				<header class="panel-heading">
					<h4 class="panel-title"><i class="fas fa-flag"></i> <?= translate('add_milestone') ?></h4>
				</header>
			
				<?php echo form_open('tracker/add_milestone', ['class' => 'form-horizontal', 'id' => 'milestoneAddForm']); ?>
				<div class="panel-body">
					<input type="hidden" name="department_id" id="milestones_department_id" value="">
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('title') ?> <span class="required">*</span></label>
						<div class="col-md-8">
							<input type="text" class="form-control" name="title" required />
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('description') ?></label>
						<div class="col-md-8">
							<textarea name="description" class="form-control" rows="2"></textarea>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('due_date') ?><span class="required">*</span></label>
						<div class="col-md-8">
							<input type="date" class="form-control" name="due_date" required />
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('status') ?></label>
						<div class="col-md-8">
							<select name="status" class="form-control">
								<option value="backlog"><?= translate('Backlog') ?></option>
								<option value="hold"><?= translate('Hold') ?></option>
								<option value="todo"><?= translate('to-do') ?></option>
								<option value="submitted"><?= translate('submitted') ?></option>
								<option value="in_progress"><?= translate('in_progress') ?></option>
								<option value="in_review"><?= translate('in_review') ?></option>
								<option value="planning"><?= translate('planning') ?></option>
								<option value="observation"><?= translate('observation') ?></option>
								<option value="waiting"><?= translate('waiting') ?></option>
								<option value="completed"><?= translate('completed') ?></option>
								<option value="done"><?= translate('done') ?></option>
								<option value="solved"><?= translate('solved') ?></option>
								<option value="canceled"><?= translate('canceled') ?></option>
							</select>
						</div>
					</div>
					
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('assigned_to')?> <span class="required">*</span></label>
						<div class="col-md-8">
							<?php
							$this->db->select('s.id, s.name');
							$this->db->from('staff AS s');
							$this->db->join('login_credential AS lc', 'lc.user_id = s.id');
							$this->db->where('lc.active', 1);   // only active users
							$this->db->where_not_in('lc.role', [1, 9]);   // exclude super admin, etc.
							$this->db->order_by('s.name', 'ASC');
							$query = $this->db->get();

							$staffArray = ['' => 'Select']; // <-- default first option
							foreach ($query->result() as $row) {
								$staffArray[$row->id] = $row->name;
							}
							echo form_dropdown("assigned_to", $staffArray, array(), "class='form-control' id='assigned_to' required data-plugin-selectTwo data-placeholder=\"Select\" data-width='100%' ");
							?>

							<span class="error"><?= form_error('assigned_to') ?></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('type') ?> <span class="required">*</span></label>
						<div class="col-md-8">
							<select name="type" id="milestone_add_type" class="form-control" required>
								<option value="">Select Type</option>
								<option value="regular">Regular</option>
								<option value="client">Client</option>
								<option value="in_house">In House</option>
							</select>
						</div>
					</div>
					<div class="form-group" id="client_add_group" style="display: none;">
						<label class="col-md-3 control-label"><?= translate('client_name') ?> <span class="required">*</span></label>
						<div class="col-md-8">
							<select name="client_id" id="client_add_id" class="form-control" data-plugin-selectTwo data-placeholder="Select Client" data-width="100%">
								<option value="">Select Client</option>
								<?php
								$this->db->select('id, client_name, company');
								$this->db->from('contact_info');
								$this->db->where('deleted', 0);
								$this->db->order_by('client_name', 'ASC');
								$contacts = $this->db->get()->result();
								foreach ($contacts as $contact) {
									$display_name = $contact->client_name . (!empty($contact->company) ? ' (' . $contact->company . ')' : '');
									echo '<option value="' . $contact->id . '">' . html_escape($display_name) . '</option>';
								}
								?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label">Stage <span class="required">*</span></label>
						<div class="col-md-8">
							<select name="stage" class="form-control" required>
								<option value="planning">Planning</option>
								<option value="execution">Execution</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('priority') ?> <span class="required">*</span></label>
						<div class="col-md-8">
							<select name="priority" class="form-control" required>
								<option value="">Select Priority</option>
								<option value="Low">Low</option>
								<option value="Medium">Medium</option>
								<option value="High">High</option>
								<option value="Urgent">Urgent</option>
							</select>
						</div>
					</div>
				</div>
				<footer class="panel-footer">
					<div class="row">
						<div class="col-md-12 text-right">
							<button type="submit" class="btn btn-default mr-xs" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
								<i class="fas fa-plus-circle"></i> <?= translate('add_milestone') ?>
							</button>
							<button class="btn btn-default modal-dismiss"><?= translate('cancel') ?></button>
						</div>
					</div>
				</footer>
				<?php echo form_close(); ?>
			</section>
		</div>

	</div>
</div>

<script type="text/javascript">
var currentDepartmentId = <?= $department->id ?>;

function getEdit(id) {
    $.ajax({
        url: base_url + 'tracker/get_milestone_edit',
        type: 'POST',
        data: { id: id },
        success: function(response) {
            $('#quick_view').html(response);
            mfp_modal('#modal');
        }
    });
}

function getAddModal(departmentId) {
    $('#milestones_department_id').val(departmentId);
    mfp_modal('#milestoneAddModal');
}

function refreshMilestonesTable() {
    $.ajax({
        url: '<?= base_url('tracker/get_milestones_data') ?>',
        type: 'POST',
        data: {
            department_id: currentDepartmentId,
            '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                buildMilestonesTable(response.milestones);
            }
        }
    });
}

function buildMilestonesTable(milestones) {
    var html = '';
    if (milestones.length === 0) {
        html = '<tr><td colspan="11" class="text-center text-muted">No data found</td></tr>';
    } else {
        milestones.forEach(function(row) {
            var isOverdue = new Date(row.due_date) < new Date() && !['completed', 'done', 'solved'].includes(row.status);
            var rowClass = isOverdue ? 'overdue-row' : '';
            var stageBadge = row.stage === 'planning' ? 'info' : 'primary';
            
            // Build task progress
            var taskProgressHtml = '';
            if (row.total_tasks > 0) {
                var progressClass = row.progress_percentage == 100 ? 'success' : (row.progress_percentage >= 50 ? 'warning' : 'danger');
                taskProgressHtml = '<div class="progress" style="margin-bottom: 0;">';
                taskProgressHtml += '<div class="progress-bar progress-bar-' + progressClass + '" style="width: ' + row.progress_percentage + '%">' + row.completed_tasks + '/' + row.total_tasks + '</div>';
                taskProgressHtml += '</div>';
                taskProgressHtml += '<small class="text-muted">' + row.progress_percentage + '% Complete</small>';
            } else {
                taskProgressHtml = '<span class="text-muted">No tasks</span>';
            }
            
            html += '<tr class="' + rowClass + '">';
            html += '<td>' + row.type + '</td>';
            html += '<td><span class="badge badge-' + stageBadge + '">' + row.stage.charAt(0).toUpperCase() + row.stage.slice(1) + '</span></td>';
            html += '<td>' + row.title + '</td>';
            html += '<td>' + (row.assigned_to_name || '-') + '</td>';
            html += '<td>' + (row.description || '') + '</td>';
            html += '<td>' + taskProgressHtml + '</td>';
            html += '<td><span class="badge badge-info">' + row.priority + '</span></td>';
            html += '<td><span class="badge badge-info">' + translateStatus(row.status) + '</span></td>';
            html += '<td><span class="text-muted">-</span></td>'; // Verification placeholder
            html += '<td>' + formatDate(row.due_date) + '</td>';
            html += '<td>';
            html += '<a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick="getEdit(' + row.id + ')">';
            html += '<i class="fa fa-pen-nib"></i></a> ';
            html += '<a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick="deleteMilestone(' + row.id + ')">';
            html += '<i class="fa fa-trash"></i></a>';
            html += '</td></tr>';
        });
    }
    $('#milestonesTableBody').html(html);
}

function translateStatus(status) {
    var translations = {
        'backlog': 'Backlog',
        'hold': 'Hold',
        'todo': 'To-Do',
        'submitted': 'Submitted',
        'in_progress': 'In Progress',
        'in_review': 'In Review',
        'planning': 'Planning',
        'observation': 'Observation',
        'waiting': 'Waiting',
        'completed': 'Completed',
        'done': 'Done',
        'solved': 'Solved',
        'canceled': 'Canceled'
    };
    return translations[status] || status;
}

function formatDate(dateString) {
    var date = new Date(dateString);
    return date.toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
}

function deleteMilestone(id) {
    if (confirm('Are you sure you want to delete this milestone?')) {
        $.ajax({
            url: '<?= base_url('tracker/delete_milestone/') ?>' + id,
            type: 'POST',
            data: {
                '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    refreshMilestonesTable();
                    showAlert('success', response.message);
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function() {
                showAlert('error', 'An error occurred while deleting the milestone');
            }
        });
    }
}

function showAlert(type, message) {
    var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible" role="alert">';
    alertHtml += '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>';
    alertHtml += message + '</div>';
    
    $('.panel-body').prepend(alertHtml);
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 3000);
}

// Add milestone form submission
$('#milestoneAddForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $.magnificPopup.close();
                refreshMilestonesTable();
                showAlert('success', response.message);
                $('#milestoneAddForm')[0].reset();
            } else {
                showAlert('error', response.message);
            }
        },
        error: function() {
            showAlert('error', 'An error occurred while adding the milestone');
        }
    });
});

// Handle type change for add form
$('#milestone_add_type').change(function() {
    var type = $(this).val();
    if (type === 'client') {
        $('#client_add_group').show();
        $('#client_add_id').prop('required', true);
    } else {
        $('#client_add_group').hide();
        $('#client_add_id').prop('required', false);
    }
});

function showVerificationModal(milestoneId) {
    $('#verify_milestone_id').val(milestoneId);
    $('#verification_remarks').val('');
    mfp_modal('#verificationModal');
}

function submitVerification() {
    var milestoneId = $('#verify_milestone_id').val();
    var remarks = $('#verification_remarks').val().trim();
    
    if (!remarks) {
        alert('Please enter verification remarks');
        return;
    }
    
    $.ajax({
        url: '<?= base_url('tracker/verify_milestone') ?>',
        type: 'POST',
        data: {
            milestone_id: milestoneId,
            verification_remarks: remarks,
            '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $.magnificPopup.close();
                location.reload();
            } else {
                alert(response.message || 'Failed to verify milestone');
            }
        },
        error: function() {
            alert('An error occurred while verifying the milestone');
        }
    });
}

function showVerificationDetails(milestoneId) {
    $.ajax({
        url: '<?= base_url('tracker/get_milestone_verification_details') ?>',
        type: 'POST',
        data: {
            milestone_id: milestoneId,
            '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                var milestone = response.milestone;
                var content = '<div class="row">' +
                    '<div class="col-md-12">' +
                    '<h5><strong>Milestone:</strong> ' + milestone.title + '</h5>' +
                    '<p><strong>Status:</strong> <span class="badge badge-info">' + milestone.status + '</span></p>' +
                    '<p><strong>Description:</strong> ' + (milestone.description || 'N/A') + '</p>' +
                    '<p><strong>Completion Remarks:</strong> ' + (milestone.remarks || 'N/A') + '</p>' +
                    '<hr>' +
                    '<h6><strong>Verification Details:</strong></h6>' +
                    '<p><strong>Verified by:</strong> ' + milestone.verified_by_name + '</p>' +
                    '<p><strong>Verified at:</strong> ' + milestone.verified_at + '</p>' +
                    '<p><strong>Verification Remarks:</strong> ' + milestone.verification_remarks + '</p>' +
                    '</div>' +
                    '</div>';
                $('#verificationDetailsContent').html(content);
                mfp_modal('#verificationDetailsModal');
            } else {
                alert(response.message || 'Failed to load verification details');
            }
        },
        error: function() {
            alert('An error occurred while loading verification details');
        }
    });
}

function showChampionBadgeModal(milestoneId) {
    $('#badge_milestone_id').val(milestoneId);
    $('#badge_reason').val('');
    mfp_modal('#championBadgeModal');
}

function submitChampionBadge() {
    var milestoneId = $('#badge_milestone_id').val();
    var reason = $('#badge_reason').val().trim();
    
    if (!reason) {
        alert('Please enter a reason for the award');
        return;
    }
    
    $.ajax({
        url: '<?= base_url('tracker/award_champion_badge') ?>',
        type: 'POST',
        data: {
            milestone_id: milestoneId,
            badge_reason: reason,
            '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $.magnificPopup.close();
                alert('Champion badge awarded successfully!');
            } else {
                alert(response.message || 'Failed to award badge');
            }
        },
        error: function() {
            alert('An error occurred while awarding the badge');
        }
    });
}
</script>
<script>
function filterByType(tabId, selectedType) {
	// Update active state for pills
	var pills = document.querySelectorAll('#' + tabId + ' .nav-pills li');
	pills.forEach(function(pill) {
		pill.classList.remove('active');
	});
	
	// Find and activate the clicked pill
	var clickedPill = document.querySelector('#' + tabId + ' .nav-pills a[data-type="' + selectedType + '"]').parentElement;
	clickedPill.classList.add('active');
	
	// Filter table rows
	var table = document.querySelector('#' + tabId + ' table tbody');
	var rows = table.getElementsByTagName('tr');
	
	for (var i = 0; i < rows.length; i++) {
		var row = rows[i];
		var rowType = row.getAttribute('data-type');
		
		if (selectedType === '' || rowType === selectedType) {
			row.style.display = '';
		} else {
			row.style.display = 'none';
		}
	}
}
</script>