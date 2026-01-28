<div class="row">
    <div class="col-md-12">
	 <section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><?= translate('select_ground') ?></h4>
			</header>
				<div class="panel-body">
					<div class="row mb-sm">
						<div class="col-md-offset-3 col-md-6 mb-sm">
							<form method="GET" class="form-inline">
								<div class="form-group mr-2">
									<label for="start_date" class="mr-1"><?= translate('from') ?>:</label>
									<input type="date" name="start_date" id="start_date" class="form-control" value="<?= $start_date ?>">
								</div>
								<div class="form-group mr-2">
									<label for="end_date" class="mr-1"><?= translate('to') ?>:</label>
									<input type="date" name="end_date" id="end_date" class="form-control" value="<?= $end_date ?>">
								</div>
								<button type="submit" class="btn btn-primary">
									<i class="fas fa-filter"></i> <?= translate('filter') ?>
								</button>
								<a href="<?= base_url('rdc/deleted_tasks') ?>" class="btn btn-default ml-2">
									<i class="fas fa-refresh"></i> <?= translate('reset') ?>
								</a>
							</form>
						</div>
					</div>
				</div>
		</section>
		
        <section class="panel">
            <header class="panel-heading">
                <h4 class="panel-title">
                    <i class="fas fa-trash"></i> <?= translate('deleted_rdc_tasks') ?>
                </h4>
            </header>
			
            <!-- Date Filter -->
            <div class="panel-body">
              
                <!-- Tasks Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="deletedTasksTable">
                        <thead>
                            <tr>
                                <th><?= translate('id') ?></th>
                                <th><?= translate('title') ?></th>
                                <th><?= translate('assigned_to') ?></th>
                                <th><?= translate('department') ?></th>
                                <th><?= translate('status') ?></th>
                                <th><?= translate('created_at') ?></th>
                                <th><?= translate('deleted_by') ?></th>
                                <th><?= translate('deleted_at') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($deleted_tasks)): ?>
                                <?php foreach ($deleted_tasks as $task): ?>
                                    <tr>
                                        <td><?= $task['id'] ?></td>
                                        <td>
                                            <strong><?= html_escape($task['title']) ?></strong>

                                        </td>
                                        <td>
                                            <?= html_escape($task['assigned_name']) ?>
                                        </td>
                                        <td><?= html_escape($task['department_name']) ?></td>
                                        <td>
                                            <?php
                                            $status_labels = [
                                                1 => ['label' => translate('pending'), 'class' => 'warning'],
                                                2 => ['label' => translate('completed'), 'class' => 'success'],
                                                3 => ['label' => translate('canceled'), 'class' => 'danger'],
                                                4 => ['label' => translate('hold'), 'class' => 'info']
                                            ];
                                            $status = $status_labels[$task['task_status']] ?? ['label' => 'Unknown', 'class' => 'default'];
                                            ?>
                                            <span class="label label-<?= $status['class'] ?>"><?= $status['label'] ?></span>
                                        </td>
                                        <td>
                                            <?= date('Y-m-d H:i', strtotime($task['created_at'])) ?>
                                        </td>
                                        <td>
                                            <strong><?= html_escape($task['deleted_by_name']) ?></strong>
                                        </td>
                                        <td>
                                            <?= $task['deleted_at'] ? date('Y-m-d H:i', strtotime($task['deleted_at'])) : '-' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <i class="fas fa-info-circle"></i> <?= translate('no_deleted_tasks_found') ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#deletedTasksTable').DataTable({
        "order": [[ 7, "desc" ]],
        "pageLength": 25,
        "responsive": true,
        "language": {
            "search": "<?= translate('search') ?>:",
            "lengthMenu": "<?= translate('show') ?> _MENU_ <?= translate('entries') ?>",
            "info": "<?= translate('showing') ?> _START_ <?= translate('to') ?> _END_ <?= translate('of') ?> _TOTAL_ <?= translate('entries') ?>",
            "paginate": {
                "first": "<?= translate('first') ?>",
                "last": "<?= translate('last') ?>",
                "next": "<?= translate('next') ?>",
                "previous": "<?= translate('previous') ?>"
            }
        }
    });
});
</script>