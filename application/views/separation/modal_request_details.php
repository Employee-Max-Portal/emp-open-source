<?php 
$row = $this->db->where('id', $request_id)->get('separation_requests')->row();
?>
<header class="panel-heading">
	<h4 class="panel-title"><i class="fas fa-bars"></i> <?= translate('separation_details') ?></h4>
</header>

<div class="panel-body">
	<div class="table-responsive">
		<table class="table borderless mb-none">
			<tbody>
				<tr>
					<th width="150"><?= translate('reviewed_by') ?> :</th>
					<td>
						<?php
							if (!empty($row->approved_by)) {
								echo html_escape(get_type_name_by_id('staff', $row->approved_by));
							} else {
								echo translate('unreviewed');
							}
						?>
					</td>
				</tr>
				<tr>
					<th><?= translate('applicant') ?> :</th>
					<td>
						<?php
							$getStaff = $this->db->select('name, staff_id')->where('id', $row->user_id)->get('staff')->row_array();
							echo isset($getStaff['name']) ? $getStaff['name'] : 'N/A';
						?>
					</td>
				</tr>
				<tr>
					<th><?= translate('staff_id') ?> :</th>
					<td><?= isset($getStaff['staff_id']) ? $getStaff['staff_id'] : 'N/A' ?></td>
				</tr>
				<tr>
					<th><?= translate('title') ?> :</th>
					<td><?= html_escape($row->title) ?></td>
				</tr>
				<tr>
					<th><?= translate('last_working_date') ?> :</th>
					<td><?= _d($row->last_working_date) ?></td>
				</tr>
				<tr>
					<th><?= translate('applied_on') ?> :</th>
					<td><?= _d($row->created_at) . ' ' . date('h:i A', strtotime($row->created_at)) ?></td>
				</tr>
				<tr>
					<th><?= translate('reason') ?> :</th>
					<td><?= (!empty($row->reason) ? nl2br(html_escape($row->reason)) : 'N/A') ?></td>
				</tr>
				<?php if (!empty($row->enc_file_name)) { ?>
				<tr>
					<th><?= translate('attachment') ?> :</th>
					<td>
						<a class="btn btn-primary btn-sm" target="_blank" href="<?=base_url('uploads/attachments/separation/' . $row['enc_file_name'])?>"><i class="fas fa-eye"></i> <?php echo translate('view'); ?></a>
						<a class="btn btn-default btn-sm" target="_blank" href="<?=base_url('separation/download/' . $row['id'] . '/' . $row['enc_file_name'])?>"><i class="far fa-arrow-alt-circle-down"></i> <?php echo translate('download'); ?></a>
					</td>
				</tr>
				<?php } ?>
				<tr>
					<th><?= translate('comments') ?> :</th>
					<td><?= (!empty($row->comments) ? nl2br(html_escape($row->comments)) : 'N/A') ?></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<footer class="panel-footer">
	<div class="row">
		<div class="col-md-12 text-right">
			<button class="btn btn-default modal-dismiss"><?= translate('close') ?></button>
		</div>
	</div>
</footer>
