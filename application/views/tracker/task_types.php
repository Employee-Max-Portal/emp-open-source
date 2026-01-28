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
</style>

<div class="row" style="height: calc(108vh);">
	<div class="col-md-3">
        <?php include 'sidebar.php'; ?>
    </div>
    <div class="col-md-9" style="height: 100%; overflow-y: auto;">
	
		<div class="panel">
			<header class="panel-heading d-flex justify-content-between align-items-center">
				<h4 class="panel-title"><?= translate('task_types') ?></h4>
				<div class="panel-btn">
					<a href="javascript:void(0);" class="btn btn-sm btn-primary" onclick="getAddModal()">
						<i class="fa fa-plus-circle"></i> <?= translate('add') ?>
					</a>
				</div>
			</header>

			<div class="panel-body">
				<table class="table table-bordered table-hover table-condensed table_default" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th><?= translate('name') ?></th>
							<th><?= translate('description') ?></th>
							<th><?= translate('created_by') ?></th>
							<th><?= translate('created_at') ?></th>
							<th><?= translate('action') ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($task_types as $row): ?>
							<tr>
								<td><?= html_escape($row->name) ?></td>
								<td><?= html_escape($row->description) ?></td>
								<td>
									<?php 
										$creator = $this->db->select('name')
														   ->where('id', $row->created_by)
														   ->get('staff')
														   ->row_array();
										echo $creator['name'] ?? '-';
									?>
								</td>
								<td><?= date('d M Y, h:i A', strtotime($row->created_at)) ?></td>
								<td>
									<a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick="getEdit(<?= $row->id ?>)">
										<i class="fa fa-pen-nib"></i>
									</a>
									<?= btn_delete('tracker/delete_task_type/' . $row->id); ?>
								</td>
							</tr>
						<?php endforeach; ?>

						<?php if (empty($task_types)): ?>
							<tr>
								<td colspan="5" class="text-center text-muted"><?= translate('no_data_found') ?></td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>

			</div>
		</div>

		<!-- Edit Modal -->
		<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
			<section class="panel" id="quick_view"></section>
		</div>

		<!-- Add Modal -->
		<div id="taskTypeAddModal" class="zoom-anim-dialog modal-block modal-block-primary mfp-hide">
			<section class="panel">
				<header class="panel-heading">
					<h4 class="panel-title"><i class="fas fa-tag"></i> <?= translate('add_task_type') ?></h4>
				</header>
			
				<?php echo form_open('tracker/add_task_type', ['class' => 'form-horizontal']); ?>
				<div class="panel-body">
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('name') ?> <span class="required">*</span></label>
						<div class="col-md-8">
							<input type="text" class="form-control" name="name" required />
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('description') ?></label>
						<div class="col-md-8">
							<textarea name="description" class="form-control" rows="3" placeholder="Enter task type description..."></textarea>
						</div>
					</div>
				</div>
				<footer class="panel-footer">
					<div class="row">
						<div class="col-md-12 text-right">
							<button type="submit" class="btn btn-default mr-xs" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
								<i class="fas fa-plus-circle"></i> <?= translate('add_task_type') ?>
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
function getEdit(id) {
    $.ajax({
        url: base_url + 'tracker/get_task_type_edit',
        type: 'POST',
        data: { id: id },
        success: function(response) {
            $('#quick_view').html(response);
            mfp_modal('#modal');
        }
    });
}

function getAddModal() {
    mfp_modal('#taskTypeAddModal');
}
</script>