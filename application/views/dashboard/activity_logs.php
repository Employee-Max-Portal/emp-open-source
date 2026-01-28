<?php $widget = (in_array(loggedin_role_id(), [1, 2, 3, 5]) ? 4 : 6); ?>
<section class="panel">
	<?= form_open($this->uri->uri_string()); ?>
	<header class="panel-heading">
		<h4 class="panel-title"><?= translate('select_ground') ?></h4>
	</header>
	<div class="panel-body">
		<div class="row">
			<div class="col-12 col-md-<?= $widget ?>">
				<div class="form-group">
					<label class="control-label"><?= translate('month') ?> <span class="required">*</span></label>
					<div class="input-group">
						<input type="text"
							   class="form-control"
							   name="timestamp"
							   value="<?= set_value('timestamp', date('Y-m')) ?>"
							   data-plugin-datepicker
							   required
							   data-plugin-options='{ "format": "yyyy-mm", "minViewMode": "months", "orientation": "bottom"}' />
						<span class="input-group-addon"><i class="icon-event icons"></i></span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<footer class="panel-footer">
		<div class="row">
			<div class="col-12 col-md-2 offset-md-10">
				<button type="submit" name="search" value="1" class="btn btn-default btn-block">
					<i class="fas fa-filter"></i> <?= translate('filter') ?>
				</button>
			</div>
		</div>
	</footer>
	<?= form_close(); ?>
</section>
<style>
@media (max-width: 768px) {
    .responsive-table {
        display: none;
    }
    .responsive-card-table {
        display: block;
    }
}
@media (min-width: 769px) {
    .responsive-card-table {
        display: none;
    }
}
.noti-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 10px;
    background-color: #fff;
}
.noti-card span {
    display: block;
    margin-bottom: 4px;
}
</style>

<?php if (isset($activity_logs)): ?>
<section class="panel appear-animation mt-sm"
         data-appear-animation="<?= $global_config['animations'] ?>"
         data-appear-animation-delay="100">
    <header class="panel-heading">
        <h4 class="panel-title">
            <i class="fas fa-tasks"></i> <?= translate('task_logs') ?>
            <?php if (!isset($filter_applied) || !$filter_applied): ?>
                <small class="text-muted">(<?= translate('last_7_days') ?>)</small>
            <?php endif; ?>
        </h4>
    </header>
    <div class="panel-body">

        <!-- Desktop Table -->
        <div class="table-responsive responsive-table">
            <table class="table table-bordered table-hover table-condensed mb-none text-dark table-export" style="width: 100%;">
                <thead class="text-nowrap">
                    <tr>
                        <th><?= translate('sl') ?></th>
                        <th><?= translate('employee') ?></th>
                        <th><?= translate('title') ?></th>
                        <th><?= translate('location') ?></th>
                        <th><?= translate('status') ?></th>
                        <th><?= translate('duration') ?></th>
                        <th><?= translate('logged_at') ?></th>
						<?php if (loggedin_role_id() == 1) { ?>
                        <th><?= translate('action') ?></th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($activity_logs)): ?>
                        <?php $sl = 1; foreach ($activity_logs as $task): 
                            $start = $task->start_time ? strtotime($task->start_time) : null;
                            $end = $task->actual_end_time ? strtotime($task->actual_end_time) : ($task->ended_at ? strtotime($task->ended_at) : null);
                            if ($start && $end && $end > $start) {
								$diff = $end - $start;
								$hours = floor($diff / 3600);
								$minutes = floor(($diff % 3600) / 60);
								$duration = ($hours ? $hours . ' hour' . ($hours > 1 ? 's' : '') : '') . 
											($hours && $minutes ? ', ' : '') . 
											($minutes ? $minutes . ' min' . ($minutes > 1 ? 's' : '') : '');
							} else {
								$duration = '--';
							}
                        ?>
                            <tr>
    							<td><?= $sl++; ?></td>
                                <td><strong><?= htmlspecialchars($task->staff_name) ?></strong></td>
                                <td><?= htmlspecialchars($task->task_title) ?></td>
                                <td><?= htmlspecialchars($task->location) ?></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($task->task_status) ?></span></td>
                                <td><?= $duration ?></td>
                                <td><?= date('d M Y, h:i A', strtotime($task->logged_at)) ?></td>
								
								<?php if (loggedin_role_id() == 1) { ?>
                                <td>
                                    <?php echo btn_delete('dashboard/delete_activity_log/' . $task->id); ?>
                                </td>
								<?php } ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">
                                <?= translate('no_task_logs_found') ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card Version -->
        <div class="responsive-card-table">
            <?php if (!empty($activity_logs)): ?>
                <?php foreach ($activity_logs as $task): 
                    $start = $task->start_time ? strtotime($task->start_time) : null;
                    $end = $task->actual_end_time ? strtotime($task->actual_end_time) : ($task->ended_at ? strtotime($task->ended_at) : null);
                    $duration = ($start && $end) ? gmdate("H:i", $end - $start) : '--';
                ?>
                    <div class="noti-card">
                        <span><strong><?= translate('employee') ?>:</strong> <?= htmlspecialchars($task->staff_name) ?></span>
                        <span><strong><?= translate('title') ?>:</strong> <?= htmlspecialchars($task->task_title) ?></span>
                        <span><strong><?= translate('location') ?>:</strong> <?= htmlspecialchars($task->location) ?></span>
    					<span><strong><?= translate('status') ?>:</strong> <span class="badge bg-info" style="display: inline-block; margin-left: 5px;"><?= htmlspecialchars($task->task_status) ?></span></span>
                        <span><strong><?= translate('duration') ?>:</strong> <?= $duration ?></span>
                        <span><strong><?= translate('logged_at') ?>:</strong> <?= date('d M Y, h:i A', strtotime($task->logged_at)) ?></span>
                        
						<?php if (loggedin_role_id() == 1) { ?>
						<span><strong><?= translate('action') ?>:</strong> 
                            <?php echo btn_delete('dashboard/delete_activity_log/' . $task->id); ?>
                        </span>
                        <?php } ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center">
                    <p><?= translate('no_task_logs_found') ?></p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php endif; ?>

