<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><?= translate('filter_options') ?></h4>
			</header>
			<?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
				<div class="panel-body">
					<div class="row mb-sm">
						<div class="col-md-offset-3 col-md-6 mb-sm">
							<div class="form-group">
	                            <label class="control-label"><?php echo translate('date_range'); ?> <span class="required">*</span></label>
	                            <div class="input-group">
	                                <span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
	                                <input type="text" class="form-control daterange" name="daterange" value="<?=set_value('daterange', (!empty($start_date) && !empty($end_date)) ? date('Y/m/d', strtotime($start_date)) . ' - ' . date('Y/m/d', strtotime($end_date)) : date("Y/m/d", strtotime('-30day')) . ' - ' . date("Y/m/d"))?>" required />
	                            </div>
	                        </div>
						</div>
					</div>
				</div>
				<footer class="panel-footer">
					<div class="row">
						<div class="col-md-offset-10 col-md-2">
							<button type="submit" name="search" value="1" class="btn btn-default btn-block">
								<i class="fas fa-filter"></i> <?= translate('filter') ?>
							</button>
						</div>
					</div>
				</footer>
			<?php echo form_close(); ?>
		</section>

		<section class="panel appear-animation" data-appear-animation="<?=$global_config['animations'] ?>" data-appear-animation-delay="100">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-chart-bar" aria-hidden="true"></i> <?=translate('pending_tasks_by_person')?></h4>
			</header>
			<div class="panel-body">
				<?php if (!empty($pending_tasks_report)): ?>
					<!-- Summary Cards -->
					<div class="row mb-lg">
						<div class="col-md-3">
							<div class="panel panel-featured panel-featured-primary">
								<div class="panel-body text-center">
									<h3 class="text-weight-bold text-color-primary"><?= count($pending_tasks_report) ?></h3>
									<p class="text-color-dark"><?= translate('people_with_pending_tasks') ?></p>
								</div>
							</div>
						</div>
						<div class="col-md-3">
							<div class="panel panel-featured panel-featured-warning">
								<div class="panel-body text-center">
									<h3 class="text-weight-bold text-color-warning">
										<?php 
											$total_pending = 0;
											foreach($pending_tasks_report as $person) {
												$total_pending += $person['total_pending'];
											}
											echo $total_pending;
										?>
									</h3>
									<p class="text-color-dark"><?= translate('total_pending_tasks') ?></p>
								</div>
							</div>
						</div>
						<div class="col-md-3">
							<div class="panel panel-featured panel-featured-danger">
								<div class="panel-body text-center">
									<h3 class="text-weight-bold text-color-danger">
										<?php 
											$overdue_count = 0;
											foreach($pending_tasks_report as $person) {
												foreach($person['tasks'] as $task) {
													if (strtotime($task['due_time']) < time()) {
														$overdue_count++;
													}
												}
											}
											echo $overdue_count;
										?>
									</h3>
									<p class="text-color-dark"><?= translate('overdue_tasks') ?></p>
								</div>
							</div>
						</div>
						<div class="col-md-3">
							<div class="panel panel-featured panel-featured-info">
								<div class="panel-body text-center">
									<h3 class="text-weight-bold text-color-info">
										<?php 
											$avg_tasks = count($pending_tasks_report) > 0 ? round($total_pending / count($pending_tasks_report), 1) : 0;
											echo $avg_tasks;
										?>
									</h3>
									<p class="text-color-dark"><?= translate('avg_tasks_per_person') ?></p>
								</div>
							</div>
						</div>
					</div>

					<!-- Detailed Report Table -->
					<div class="table-responsive">
						<table class="table table-bordered table-striped table-hover mb-none">
							<thead>
								<tr>
									<th width="50"><?=translate('sl')?></th>
									<th><?=translate('employee')?></th>
									<th><?=translate('department')?></th>
									<th><?=translate('task_titles')?></th>
									<th class="text-center"><?=translate('pending_tasks')?></th>
									<th class="text-center"><?=translate('overdue_tasks')?></th>
									<th width="100" class="text-center"><?=translate('action')?></th>
								</tr>
							</thead>
							<tbody>
								<?php 
								$count = 1;
								foreach($pending_tasks_report as $person_id => $person): 
									$overdue_tasks = 0;
									$task_titles = array();
									foreach($person['tasks'] as $task) {
										if (strtotime($task['due_time']) < time()) {
											$overdue_tasks++;
										}
										$task_titles[] = htmlspecialchars($task['title']);
									}
								?>
								<tr>
									<td><?= $count++ ?></td>
									<td>
										<strong><?= $person['employee_id'] ?> - <?= htmlspecialchars($person['staff_name']) ?></strong>
									</td>
									<td><?= htmlspecialchars($person['department_name']) ?></td>
									<td><?= implode(', ', $task_titles) ?></td>
									<td class="text-center">
										<span class="label label-warning"><?= $person['total_pending'] ?></span>
									</td>
									<td class="text-center">
										<?php if ($overdue_tasks > 0): ?>
											<span class="label label-danger"><?= $overdue_tasks ?></span>
										<?php else: ?>
											<span class="label label-success">0</span>
										<?php endif; ?>
									</td>
									<td class="text-center">
										<button type="button" class="btn btn-info btn-sm" onclick="viewPersonTasks(<?= $person_id ?>)" data-toggle="tooltip" title="<?= translate('view_tasks') ?>">
											<i class="fas fa-eye"></i>
										</button>
									</td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php else: ?>
					<div class="alert alert-info">
						<i class="fas fa-info-circle"></i> <?= translate('no_pending_tasks_found') ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
	</div>
</div>

<!-- Task Details Modal -->
<div class="modal fade" id="taskDetailsModal" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title"><?= translate('pending_tasks_details') ?></h4>
			</div>
			<div class="modal-body" id="taskDetailsContent">
				<!-- Content will be loaded here -->
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?= translate('close') ?></button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		// Initialize daterangepicker
		$('.daterange').daterangepicker({
			opens: 'left',
		    locale: {format: 'YYYY/MM/DD'}
		});

		// Initialize tooltips
		$('[data-toggle="tooltip"]').tooltip();
	});

	// Function to view person's tasks
	function viewPersonTasks(personId) {
		var tasks = <?= json_encode($pending_tasks_report) ?>;
		var person = tasks[personId];
		
		if (!person) {
			alert('<?= translate('person_not_found') ?>');
			return;
		}

		var content = '<div class="row mb-md">';
		content += '<div class="col-md-12">';
		content += '<h5><strong>' + person.employee_id + ' - ' + person.staff_name + '</strong></h5>';
		content += '<p class="text-muted">' + person.department_name + '</p>';
		content += '</div>';
		content += '</div>';

		content += '<div class="table-responsive">';
		content += '<table class="table table-bordered table-striped">';
		content += '<thead>';
		content += '<tr>';
		content += '<th width="50"><?= translate('sl') ?></th>';
		content += '<th><?= translate('task_title') ?></th>';
		content += '<th><?= translate('due_time') ?></th>';
		content += '<th><?= translate('created_at') ?></th>';
		content += '<th class="text-center"><?= translate('status') ?></th>';
		content += '</tr>';
		content += '</thead>';
		content += '<tbody>';

		for (var i = 0; i < person.tasks.length; i++) {
			var task = person.tasks[i];
			var dueDate = new Date(task.due_time);
			var createdDate = new Date(task.created_at);
			var isOverdue = dueDate < new Date();
			
			content += '<tr' + (isOverdue ? ' class="danger"' : '') + '>';
			content += '<td>' + (i + 1) + '</td>';
			content += '<td>' + task.title + '</td>';
			content += '<td>';
			content += dueDate.toLocaleDateString() + ' ' + dueDate.toLocaleTimeString();
			if (isOverdue) {
				content += ' <span class="label label-danger"><?= translate('overdue') ?></span>';
			}
			content += '</td>';
			content += '<td>' + createdDate.toLocaleDateString() + ' ' + createdDate.toLocaleTimeString() + '</td>';
			content += '<td class="text-center">';
			content += '<span class="label label-warning"><?= translate('pending') ?></span>';
			content += '</td>';
			content += '</tr>';
		}

		content += '</tbody>';
		content += '</table>';
		content += '</div>';

		$('#taskDetailsContent').html(content);
		$('#taskDetailsModal').modal('show');
	}
</script>