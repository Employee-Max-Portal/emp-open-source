<section class="panel">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#">
					<i class="far fa-user-circle"></i> <?php echo translate('pending'); ?> <?php echo translate('attendances'); ?>
				</a>
			</li>
		</ul>

		<div class="tab-content">
			<div class="tab-pane box active">
				<div class="export_title"><?php echo translate('pending_attendance') . " " . translate('list'); ?></div>
				<form method="POST" action="<?= base_url('attendance/update_attendance') ?>">
					<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />
				<table class="table table-bordered table-hover table-condensed table-export" cellspacing="0" width="100%">
					<thead>
					  <tr>
						<th><?php echo translate('employee'); ?></th>
						<th><?php echo translate('Check-in'); ?></th>
						<th><?php echo translate('status'); ?></th>
						<th><?php echo translate('action'); ?></th> <!-- Add action column for button -->
					  </tr>
					</thead>
					<tbody>
					  <?php
					  //$date = date('Y-m-d'); // Or set your desired date

					  foreach ($pending_attendance as $key => $att):
					  ?>
						<tr>
						<input type="hidden" name="attendance[<?=$key?>][attendance_id]" value="<?=$att['atten_id']?>">
						  <td><?php echo htmlspecialchars($att['staff_code']) . ' - ' . htmlspecialchars($att['name']); ?></td>
						  <td>
							<?php
							// Format the in_time value
							if (!empty($att['in_time'])) {
							  echo date('h:i A, d M Y', strtotime($att['in_time'] . $att['date']));
							} else {
							  echo 'N/A'; // Optional: Show 'N/A' if in_time is empty
							}
							?>
						  </td>

						  <td>
							<?php
							// Check the attendance status and assign button color and tooltip
							if ($att['att_status'] === 'L') {
							  echo '<button class="btn btn-warning btn-sm" data-toggle="tooltip" title="' . htmlspecialchars($att['att_remark']) . '">Late</button>';
							} elseif ($att['att_status'] === 'P') {
							  echo '<button class="btn btn-success btn-sm" data-toggle="tooltip" title="' . htmlspecialchars($att['att_remark']) . '">Present</button>';
							} else {
							  // Default button for other statuses if needed
							  echo '<button class="btn btn-secondary btn-sm" data-toggle="tooltip" title="' . htmlspecialchars($att['att_remark']) . '">' . htmlspecialchars($att['att_status']) . '</button>';
							}
							?>
						  </td>

						  <td>
							<div class="radio-custom radio-success radio-inline mt-xs">
							  <input type="radio" value="P" <?=($att['att_status'] == 'P' ? 'checked' : '')?> name="attendance[<?=$key?>][status]" id="pstatus_<?=$key?>">
							  <label for="pstatus_<?=$key?>"><?=translate('present')?></label>
							</div>
							<br>
							<div class="radio-custom radio-inline mt-xs">
							  <input type="radio" value="L" <?=($att['att_status'] == 'L' ? 'checked' : '')?> name="attendance[<?=$key?>][status]" id="lstatus_<?=$key?>">
							  <label for="lstatus_<?=$key?>"><?=translate('late')?></label>
							</div>
						  </td> 
						</tr>
					  <?php endforeach; ?>
					</tbody>
				</table>
				<div class="panel-footer">
					<div class="row">
					  <div class="col-md-offset-8 col-md-3">
						<button type="button" id="openConfirmModal" class="btn btn-default btn-block"><?=translate('update')?></button>
					  </div>
					</div>
			  </div>
			</form>
			</div>
		</div>
	</div>
</section>




<!-- Confirm Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmModalLabel">Confirm Update</h5>
      </div>
      <div class="modal-body">
        Are you sure you want to update attendance?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" id="confirmSubmit" class="btn btn-primary">Yes, Update</button>
      </div>
    </div>
  </div>
</div>

<script>
  document.getElementById('openConfirmModal').addEventListener('click', function (e) {
    $('#confirmModal').modal('show');
  });

  document.getElementById('confirmSubmit').addEventListener('click', function () {
  document.querySelector('form[action*="update_attendance"]').submit();
});

</script>