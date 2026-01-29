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

<?php if (isset($telegram_logs)): ?>
<section class="panel appear-animation mt-sm"
         data-appear-animation="<?= $global_config['animations'] ?>"
         data-appear-animation-delay="100">
    <header class="panel-heading">
        <h4 class="panel-title">
            <i class="fab fa-telegram"></i> Telegram Feedback Replies
        </h4>
    </header>
    <div class="panel-body">

        <!-- Desktop Table -->
        <div class="table-responsive responsive-table">
            <table class="table table-bordered table-hover table-condensed mb-none text-dark" style="width: 100%;">
                <thead class="text-nowrap">
                    <tr>
                        <th>SL</th>
                        <th>Employee</th>
                        <th>Module</th>
                        <th>Summary Date</th>
                        <th>Completed Tasks</th>
                        <th>Message</th>
                        <th>Submitted By</th>
                        <th>Date Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($telegram_logs)): ?>
                        <?php $sl = 1; foreach ($telegram_logs as $feedback): ?>
                            <tr>
                                <td><?= $sl++; ?></td>
                                <td><?= htmlspecialchars($feedback->name ?? '-') ?></td>
                                <td><?= htmlspecialchars($feedback->module_name ?: '-') ?></td>
                                <td><?= htmlspecialchars($feedback->summary_date ?? '-') ?></td>
                                <td>
                                    <?php
                                    $completed = json_decode($feedback->completed_tasks ?? '[]');
                                    if (!empty($completed)) {
                                        foreach ($completed as $task) {
                                            echo '- ' . htmlspecialchars($task->title ?? '-') . '<br>';
                                        }
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($feedback->message) ?></td>
                                <td><?= htmlspecialchars($feedback->sender_name ?: '-') ?></td>
                                <td><?= date('d M Y, h:i A', strtotime($feedback->datetime)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No feedback found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="responsive-card-table">
            <?php if (!empty($telegram_logs)): ?>
                <?php foreach ($telegram_logs as $feedback): ?>
                    <div class="noti-card mb-3">
                        <span><strong>Employee:</strong> <?= htmlspecialchars($feedback->name ?? '-') ?></span>
                        <span><strong>Module:</strong> <?= htmlspecialchars($feedback->module_name ?: '-') ?></span>
                        <span><strong>Summary Date:</strong> <?= htmlspecialchars($feedback->summary_date ?? '-') ?></span>
                        <span><strong>Completed Tasks:</strong>
                            <?php
                            $completed = json_decode($feedback->completed_tasks ?? '[]');
                            if (!empty($completed)) {
                                echo '<ul>';
                                foreach ($completed as $task) {
                                    echo '<li>' . htmlspecialchars($task->title ?? '-') . '</li>';
                                }
                                echo '</ul>';
                            } else {
                                echo ' - ';
                            }
                            ?>
                        </span>
                        <span><strong>Message:</strong> <?= htmlspecialchars($feedback->message) ?></span>
                        <span><strong>Submitted By:</strong> <?= htmlspecialchars($feedback->sender_name ?: '-') ?></span>
                        <span><strong>Date Time:</strong> <?= date('d M Y, h:i A', strtotime($feedback->datetime)) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center">
                    <p>No feedback found</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php endif; ?>