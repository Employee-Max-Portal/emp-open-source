<section class="panel">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<li class="<?php echo (empty(validation_errors()) ? 'active' : ''); ?>">
				<a href="#list" data-toggle="tab"><i class="fas fa-list-ul"></i> <?= translate('request_lists') ?></a>
			</li>
			<?php if (get_permission('separation', 'is_add')) { ?>
			<li class="<?php echo (!empty(validation_errors()) ? 'active' : ''); ?>">
				<a href="#create" data-toggle="tab"><i class="far fa-edit"></i> <?= translate('add_request') ?></a>
			</li>
			<?php } ?>
		</ul>

		<div class="tab-content">

			<!-- Separation List -->
			<div id="list" class="tab-pane <?php echo (empty(validation_errors()) ? 'active' : ''); ?>">
				<table class="table table-bordered table-condensed table-hover mb-none table_default">
					<thead>
						<tr>
							<th><?= translate('sl') ?></th>
							<th><?= translate('employee_name') ?></th>
							<th><?= translate('title') ?></th>
							<th><?= translate('last_working_date') ?></th>
							<th><?= translate('reason') ?></th>
							<th><?= translate('attachment') ?></th>
							<th><?= translate('applied_on') ?></th>
							<th><?= translate('status') ?></th>
							<th><?= translate('action') ?></th>
						</tr>
					</thead>
					<tbody>
						<?php $count = 1;
						if (!empty($separationList)) {
							foreach ($separationList as $row) { ?>
								<tr>
									<td><?= $count++ ?></td>
									<td>
										<?php
											$staff = $this->db->select('name, staff_id')->where('id', $row->user_id)->get('staff')->row_array();
											echo isset($staff['name']) ? $staff['name'] . '<br><small> - ' . $staff['staff_id'] . '</small>' : '-';
										?>
									</td>
									<td><?= html_escape($row->title) ?></td>
									<td><?= _d($row->last_working_date) ?></td>
									<td><?= nl2br(html_escape($row->reason)) ?></td>
									<td>
										<?php if (!empty($row->enc_file_name)): ?>
											<a href="<?= base_url('uploads/attachments/separation/' . $row->enc_file_name) ?>" target="_blank">
												<i class="fas fa-paperclip"></i> <?= html_escape($row->orig_file_name) ?>
											</a>
										<?php else: ?>
											-
										<?php endif; ?>
									</td>
									<td><?= _d($row->created_at) ?></td>
									<td>
										<?php
											if ($row->status == 1)
												echo '<span class="label label-warning-custom">' . translate('pending') . '</span>';
											elseif ($row->status == 2)
												echo '<span class="label label-success-custom">' . translate('approved') . '</span>';
											elseif ($row->status == 3)
												echo '<span class="label label-danger-custom">' . translate('rejected') . '</span>';
											else
												echo '-';
										?>
									</td>
									<td>
										<a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick="getRequestDetails('<?= $row->id ?>')">
											<i class="fas fa-bars"></i>
										</a>
										<?php if ($row->status == 1 && get_permission('separation', 'is_delete')): ?>
											<?= btn_delete('separation/request_delete/' . $row->id); ?>
										<?php endif; ?>
									</td>
								</tr>
						<?php } } else { ?>
							<tr><td colspan="9" class="text-center text-muted"><?= translate('no_data_available') ?></td></tr>
						<?php } ?>
					</tbody>
				</table>

			</div>

			<!-- Create Separation Form -->
			<?php if (get_permission('separation', 'is_add')) { ?>
			<div class="tab-pane <?php echo (!empty(validation_errors()) ? 'active' : ''); ?>" id="create">
				<?= form_open_multipart($this->uri->uri_string(), array('class' => 'form-horizontal form-bordered validate')) ?>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('last_working_date') ?> <span class="required">*</span></label>
						<div class="col-md-6">
							<div class="input-group">
								<span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
								<input type="text" class="form-control datepicker" name="last_working_date" id="last_working_date"
									value="<?= html_escape(set_value('last_working_date', date('Y-m-d'))) ?>" required />
							</div>
							<span class="error"><?= form_error('last_working_date') ?></span>
						</div>
					</div>

					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('reason') ?> <span class="required">*</span></label>
						<div class="col-md-6">
							<textarea class="form-control" name="reason" rows="3" required><?= set_value('reason') ?></textarea>
						</div>
						<span class="error"><?= form_error('reason') ?></span>
					</div>


					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('attachment') ?></label>
						<div class="col-md-6 mb-md">
							<input type="file" name="attachment_file" class="dropify" data-height="80" />
							<span class="error"><?= form_error('attachment_file') ?></span>
						</div>
					</div>

					<footer class="panel-footer">
						<div class="row">
							<div class="col-md-offset-3 col-md-2">
					
								<button type="submit" name="save" value="1" class="btn btn-default btn-block" 
										onclick="let btn = this; setTimeout(() => { btn.disabled = true; btn.innerHTML = '<i class=\'fas fa-spinner fa-spin\'></i> Processing'; setTimeout(() => { btn.disabled = false; btn.innerHTML = '<i class=\'fas fa-plus-circle\'></i> <?=translate('submit')?>'; }, 10000); }, 50);">
									<i class="fas fa-plus-circle"></i> <?=translate('submit')?>
								</button>

							</div>
						</div>
					</footer>

				<?= form_close(); ?>
			</div>
			<?php } ?>

		</div>
	</div>
</section>

<!-- Quick View Modal -->
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
	<section class="panel" id="quick_view"></section>
</div>

<!-- JS: Datepicker + Modal -->
<script type="text/javascript">
$(document).ready(function() {
	$('.datepicker').datepicker({
		format: 'yyyy-mm-dd',
		autoclose: true,
		todayHighlight: true
	});
});

function getRequestDetails(id) {
	$.ajax({
		url: base_url + 'separation/getRequestDetails',
		type: 'POST',
		data: { 'id': id },
		dataType: "html",
		success: function(data) {
			$('#quick_view').html(data);
			mfp_modal('#modal');
		}
	});
}
</script>
