<div class="row">
	<div class="col-md-12">

		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><?= translate('select_ground') ?></h4>
			</header>
			<?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
				<div class="panel-body">
					<div class="row mb-sm">
						<div class="col-md-offset-3 col-md-3 mb-sm">
							<div class="form-group">
	                            <label class="control-label"><?php echo translate('date'); ?> <span class="required">*</span></label>
	                            <div class="input-group">
	                                <span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
	                                <input type="text" class="form-control daterange" name="daterange" value="<?=set_value('daterange', date("Y/m/d", strtotime('-6day')) . ' - ' . date("Y/m/d"))?>" required />
	                            </div>
	                        </div>
						</div>
						<div class="col-md-3 mb-sm">
							<div class="form-group">
								<label class="control-label"><?php echo translate('status'); ?></label>
								<select name="status" class="form-control">
									<?php 
									$current_status = $this->input->post('status');
									// If no filter applied, default to pending (1)
									if (!$this->input->post('search')) {
										$current_status = '1';
									}
									?>
									<option value="" <?= ($current_status === '' ? 'selected' : ''); ?>>
										<?php echo translate('all'); ?>
									</option>
									<option value="1" <?= ($current_status == '1' ? 'selected' : ''); ?>>
										<?php echo translate('pending'); ?>
									</option>
									<option value="2" <?= ($current_status == '2' ? 'selected' : ''); ?>>
										<?php echo translate('completed'); ?>
									</option>
									<option value="3" <?= ($current_status == '3' ? 'selected' : ''); ?>>
										<?php echo translate('canceled'); ?>
									</option>
									<option value="4" <?= ($current_status == '4' ? 'selected' : ''); ?>>
										<?php echo translate('hold'); ?>
									</option>
								</select>
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


		<?php if (isset($task_stats)): ?>
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><?=translate('task_statistics')?></h4>
			</header>
			<div class="panel-body">
				<div class="row">
					<div class="col-md-3">
						<div class="alert alert-warning">
							<strong><?=translate('pending')?>:</strong> <?=$task_stats['pending']?>
						</div>
					</div>
					<div class="col-md-3">
						<div class="alert alert-success">
							<strong><?=translate('completed')?>:</strong> <?=$task_stats['completed']?>
						</div>
					</div>
					<div class="col-md-3">
						<div class="alert alert-danger">
							<strong><?=translate('canceled')?>:</strong> <?=$task_stats['canceled']?>
						</div>
					</div>
					<div class="col-md-3">
						<div class="alert alert-info">
							<strong><?=translate('hold')?>:</strong> <?=$task_stats['hold']?>
						</div>
					</div>
				</div>
			</div>
		</section>
		<?php endif; ?>

		
		<section class="panel appear-animation" data-appear-animation="<?=$global_config['animations'] ?>" data-appear-animation-delay="100">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-users" aria-hidden="true"></i> <?=translate('list_of_tasks')?></h4>
			</header>
			<div class="panel-body">
				<table class="table table-bordered table-condensed table-hover mb-none table-export" >
					<thead>
						<tr>
							<th><?=translate('task_title')?></th>
							<th><?=translate('executor')?></th>
							<th><?=translate('verifier')?></th>
							<th><?=translate('frequency')?></th>
							<th><?=translate('due_time')?></th>
							<th><?=translate('completed_at')?></th>
							<th><?=translate('created_at')?></th>
							<th style="text-align:center;"><?=translate('executor_status')?></th>
							<th style="text-align:center;"><?=translate('verifier_status')?></th>
							<th style="text-align:center;" width="100"><?=translate('action')?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						if (count($tasklist)) { 
							foreach($tasklist as $row) {
								?>
						<tr>
							<td><?= htmlspecialchars($row['title']) ?></td>
							<td><?php echo $row['name']; ?>	</td>
							<td>
								<?php
								if (!empty($row['verifier_role'])) {
									// 1. Convert "2,5,8" to array of role IDs
									$roleIds = array_map('trim', explode(',', $row['verifier_role']));

									// 2. Get all user_ids matching those roles
									$userList = $this->db->select('user_id')
														 ->where_in('role', $roleIds)
														 ->get('login_credential')
														 ->result_array();

									if (!empty($userList)) {
										$userIds = array_column($userList, 'user_id');

										// 3. Get staff (id + name) for those users
										$staffList = $this->db->select('id, name')
															  ->where_in('id', $userIds)
															  ->get('staff')
															  ->result_array();

										if (!empty($staffList)) {
											$staffNames = [];
											foreach ($staffList as $staff) {
												if ($staff['id'] == $row['created_by']) {
													// Mark the one that matches created_by
													$staffNames[] = '<b>'.$staff['name'].'</b>';
												} else {
													$staffNames[] = $staff['name'];
												}
											}
											echo implode(', ', $staffNames);
										} else {
											echo '-';
										}
									} else {
										echo '-';
									}
								} else {
									echo '-';
								}
								?>
								</td>
							<td><?= ucfirst($row['frequency']) ?></td>
							<td>
								<?php
									$dueDate = new DateTime($row['due_time']);
									echo $dueDate->format('jS M, Y \a\t g:i A');
								?>
							</td>
							<td>
								<?php
									if (!empty($row['exe_cleared_on'])) {
										$clearDate = new DateTime($row['exe_cleared_on']);
										echo $clearDate->format('jS M, Y \a\t g:i A');
									} else {
										echo '-';
									}
								?>
							</td>

							<td>
								<?php
									$createDate = new DateTime($row['created_at']);
									echo $createDate->format('jS M, Y \a\t g:i A');
								?>
							</td>
							<td style="text-align:center;" class="task-status-<?= $row['id'] ?>">
								<?php
								if ($row['task_status'] == 1)
									echo '<span class="label label-warning-custom">' . translate('pending') . '</span>';
								else if ($row['task_status'] == 2)
									echo '<span class="label label-success-custom">' . translate('completed') . '</span>';
								else if ($row['task_status'] == 3)
									echo '<span class="label label-danger-custom">' . translate('rejected') . '</span>';
								else if ($row['task_status'] == 4)
									echo '<span class="label label-warning-custom">' . translate('hold') . '</span>';
								?>
							</td>
							<td style="text-align:center;" class="verify-status-<?= $row['id'] ?>">
								<?php
								if ($row['verify_status'] == 1)
									echo '<span class="label label-warning-custom">' . translate('pending') . '</span>';
								else if ($row['verify_status'] == 2)
									echo '<span class="label label-success-custom">' . translate('approved') . '</span>';
								else if ($row['verify_status'] == 3)
									echo '<span class="label label-danger-custom">' . translate('rejected') . '</span>';
								else if ($row['verify_status'] == 4)
									echo '<span class="label label-default">' . translate('not applicable') . '</span>';
								?>
							</td>

							<td>
							<a href="javascript:void(0);" class="btn btn-info btn-circle icon" onclick="getDetailedSop('<?=$row['id']?>')" data-toggle="tooltip" data-original-title="<?php echo translate('View SOP'); ?>" >
									<i class="fas fa-eye" style="color: #ffffff;"></i>
								</a>
							<?php if (!empty($row['proof_image']) || !empty($row['proof_file'])): ?>
								<a href="javascript:void(0);" class="btn btn-success btn-circle icon" onclick="viewProofFiles('<?=$row['id']?>')" data-toggle="tooltip" data-original-title="<?php echo translate('View Proof Files'); ?>">
									<i class="fas fa-paperclip" style="color: #ffffff;"></i>
								</a>
							<?php endif; ?>
							<?php if (get_permission('rdc_management', 'is_edit')) { ?>
								<a href="javascript:void(0);" class="btn btn-circle icon btn-default" onclick="getApprovelDetails('<?=$row['id']?>')">
									<i class="fas fa-bars"></i>
								</a>
							<?php } ?>
							<?php if (get_permission('rdc_management', 'is_delete')) { ?>
								<?php echo btn_delete('rdc/delete/' . $row['id']); ?>
							<?php } ?>
							</td>
						</tr>
						<?php } } ?>
					</tbody>
				</table>
			</div>
		</section>
	</div>
</div>

<!-- View Modal -->
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
	<section class="panel" id='quick_view'></section>
</div>

<script>
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>

<script type="text/javascript">
	$(document).ready(function () {
		// Initialize Summernote
		$('.summernote').summernote({
			height: 100,
			dialogsInBody: true,
			toolbar: [
				['style', ['bold', 'italic', 'underline', 'clear']],
				['font', ['fontsize']],
				['para', ['ul', 'ol', 'paragraph']],
				['insert', ['link']],
				['view', ['fullscreen', 'codeview']]
			]
		});

		// Custom tag button click inserts text
		$('.btn_tag').on('click', function() {
	var txtToAdd = $(this).data("value");
	var $focusedEditor = $('.summernote:focus');
	if ($focusedEditor.length > 0) {
		$focusedEditor.summernote('insertText', txtToAdd);
	} else {
		// fallback to first one
		$('.summernote').eq(0).summernote('insertText', txtToAdd);
	}
});

	});
</script>
<script type="text/javascript">
	$(document).ready(function () {
		$('#daterange').daterangepicker({
			opens: 'left',
		    locale: {format: 'YYYY/MM/DD'}
		});
		
		// Status button click handler
		$('.status-btn').on('click', function() {
			$('.status-btn').removeClass('active');
			$(this).addClass('active');
			$('#status_filter').val($(this).data('status'));
			$('form.validate').submit();
		});
	});

	// get approvel details
	function getApprovelDetails(id) {
	    $.ajax({
	        url: base_url + 'rdc/getApprovelDetails',
	        type: 'POST',
	        data: {'id': id},
	        dataType: "html",
	        success: function (data) {
				$('#quick_view').html(data);
				mfp_modal('#modal');
	        }
	    });
	}

	// get Sop details
	function getDetailedSop(id) {
	    $.ajax({
	        url: base_url + 'rdc/getDetailedSop',
	        type: 'POST',
	        data: {'id': id},
	        dataType: "html",
	        success: function (data) {
				$('#quick_view').html(data);
				mfp_modal('#modal');
	        }
	    });
	}

	// View proof files
	function viewProofFiles(id) {
	    $.ajax({
	        url: base_url + 'rdc/viewProofFiles',
	        type: 'POST',
	        data: {'id': id},
	        dataType: "html",
	        success: function (data) {
				$('#quick_view').html(data);
				mfp_modal('#modal');
	        }
	    });
	}

	
	// Update task status cells instantly
	function updateTaskStatus(taskId, taskStatus, verifyStatus) {
		// Update task status
		var taskStatusHtml = '';
		if (taskStatus == 1) taskStatusHtml = '<span class="label label-warning-custom"><?= translate('pending') ?></span>';
		else if (taskStatus == 2) taskStatusHtml = '<span class="label label-success-custom"><?= translate('completed') ?></span>';
		else if (taskStatus == 3) taskStatusHtml = '<span class="label label-danger-custom"><?= translate('rejected') ?></span>';
		else if (taskStatus == 4) taskStatusHtml = '<span class="label label-warning-custom"><?= translate('hold') ?></span>';
		
		// Update verify status
		var verifyStatusHtml = '';
		if (verifyStatus == 1) verifyStatusHtml = '<span class="label label-warning-custom"><?= translate('pending') ?></span>';
		else if (verifyStatus == 2) verifyStatusHtml = '<span class="label label-success-custom"><?= translate('approved') ?></span>';
		else if (verifyStatus == 3) verifyStatusHtml = '<span class="label label-danger-custom"><?= translate('rejected') ?></span>';
		
		// Update the cells
		$('.task-status-' + taskId).html(taskStatusHtml);
		$('.verify-status-' + taskId).html(verifyStatusHtml);
	}

</script>