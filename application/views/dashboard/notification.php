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
/* Hide table on screens smaller than 768px (mobile) */
@media (max-width: 767.98px) {
    .table-export {
        display: none !important;
    }
    .dataTables_wrapper {
        display: none !important;
    }
    .responsive-table {
        display: none !important;
    }
    .responsive-card-table {
        display: block !important;
    }
}

/* Hide card view on desktop (optional for clarity) */
@media (min-width: 768px) {
    .responsive-card-table {
        display: none !important;
    }
    
	.responsive-table {
        display: block !important;
    }
}

@media (min-width: 769px) {
    .responsive-table {
        display: table;
    }
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

<?php if (isset($notifications)): ?>
<section class="panel appear-animation mt-sm"
         data-appear-animation="<?= $global_config['animations'] ?>"
         data-appear-animation-delay="100">
    <header class="panel-heading">
        <h4 class="panel-title">
            <i class="fas fa-bell"></i> <?= translate('all_notifications') ?>
            <?php if (!isset($filter_applied) || !$filter_applied): ?>
                <small class="text-muted">(<?= translate('last_7_days') ?>)</small>
            <?php endif; ?>
        </h4>
    </header>
    <div class="panel-body">

        <!-- Desktop Table
        <div class="table-responsive responsive-table"> -->
        <div>
            <table class="table table-bordered table-hover table-condensed mb-none text-dark table-export">
                <thead class="text-nowrap">
                    <tr>
                        <th><?= translate('sl') ?></th>
                        <th><?= translate('type') ?></th>
                        <th><?= translate('title') ?></th>
                        <th><?= translate('description') ?></th>
                        <th><?= translate('date') ?></th>
                        <th><?= translate('status') ?></th>
                        <?php if (in_array(loggedin_role_id(), [1, 2, 3, 5])): ?>
                            <th><?= translate('viewed_by') ?></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $count = 1; if (!empty($notifications)): ?>
                        <?php foreach ($notifications as $noti): ?>
                            <tr><td><?php echo $count++; ?></td>
                                <td><?= translate($noti->type) ?></td>
                                <td><strong><?= htmlspecialchars($noti->title) ?></strong></td>
                                <td><?= htmlspecialchars($noti->message) ?></td>
                                <td><?= date('d M Y, h:i A', strtotime($noti->created_at)) ?></td>
                                <td>
                                    <?= $noti->is_viewed
                                        ? '<span class="badge bg-success">✅ Seen</span>'
                                        : '<span class="badge bg-danger">❌ Unseen</span>' ?>
                                </td>
                                <?php if (in_array(loggedin_role_id(), [1, 2, 3, 5])): ?>
                                    <td>
                                        <?php
                                        $views = $this->db->select('s.name')
                                            ->from('notification_views nv')
                                            ->join('staff s', 's.id = nv.staff_id')
                                            ->where('nv.notification_id', $noti->id)
                                            ->get()
                                            ->result();
                                        echo implode(', ', array_column($views, 'name')) ?: '<em>None</em>';
                                        ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= in_array(loggedin_role_id(), [1, 2, 3, 5]) ? '6' : '5' ?>" class="text-center">
                                <?= translate('no_notifications_found') ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card Version -->
        <div class="responsive-card-table">
            <?php if (!empty($notifications)): ?>
                <?php foreach ($notifications as $noti): ?>
                    <div class="noti-card">
                        <span><strong><?= translate('type') ?>:</strong> <?= translate($noti->type) ?></span>
                        <span><strong><?= translate('title') ?>:</strong> <?= htmlspecialchars($noti->title) ?></span>
                        <span><strong><?= translate('description') ?>:</strong> <?= htmlspecialchars($noti->message) ?></span>
                        <span><strong><?= translate('date') ?>:</strong> <?= date('d M Y, h:i A', strtotime($noti->created_at)) ?></span>
                        <span>
    						<strong><?= translate('status') ?>:</strong>
    						<?= $noti->is_viewed
    							? '<span class="badge bg-success" style="display:inline-block; margin-left:5px;">✅ Seen</span>'
    							: '<span class="badge bg-danger" style="display:inline-block; margin-left:5px;">❌ Unseen</span>' ?>
    					</span>

                        <?php if (in_array(loggedin_role_id(), [1, 2, 3, 5])): ?>
                            <span><strong><?= translate('viewed_by') ?>:</strong>
                                <?php
                                $views = $this->db->select('s.name')
                                    ->from('notification_views nv')
                                    ->join('staff s', 's.id = nv.staff_id')
                                    ->where('nv.notification_id', $noti->id)
                                    ->get()
                                    ->result();
                                echo implode(', ', array_column($views, 'name')) ?: '<em>None</em>';
                                ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center">
                    <p><?= translate('no_notifications_found') ?></p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php endif; ?>