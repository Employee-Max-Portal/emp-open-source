<section class="panel">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#log" data-toggle="tab">
					<i class="fas fa-history"></i> <?= translate('escalation') . " " . translate('logs') ?>
				</a>
			</li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane box active mb-md" id="log">
				<table class="table table-bordered table-hover mb-none table-condensed table-export">
					<thead>
						<tr>
							<th><?= translate('sl') ?></th>
							<th><?= translate('for_task') ?></th>
							<th><?= translate('action_by') ?></th>
							<th><?= translate('action_type') ?></th>
							<th><?= translate('remarks') ?></th>
							<th><?= translate('timestamp') ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$count = 1;
						foreach ($logs as $log):
							// Fetch staff name
							$staff = $this->db->get_where('staff', ['id' => $log['staff_id']])->row();
							$staff_name = $staff ? $staff->name : 'Unknown';
							
							// Fetch staff name
							$task = $this->db->get_where('rdc_task', ['id' => $log['task_id']])->row();
							$task_title = $task ? $task->title : 'Unknown';

							// Format action type nicely
							$action_label = ucwords(str_replace('_', ' ', $log['action_type']));
						?>
							<tr>
								<td><?= $count++; ?></td>
								<td><?= html_escape($task_title) ?></td>
								<td><?= html_escape($staff_name) ?></td>
								<td>
									<span class="label label-info">
										<?= $action_label ?>
									</span>
								</td>
								<td><?= !empty($log['remarks']) ? nl2br(html_escape($log['remarks'])) : '--' ?></td>
								<td><?= date('d M Y h:i A', strtotime($log['created_at'])) ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</section>
