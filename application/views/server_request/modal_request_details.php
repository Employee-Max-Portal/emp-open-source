<?php
$this->db->select('server_access_requests.*, staff.name as staff_name, staff.staff_id as staffid');
$this->db->from('server_access_requests');
$this->db->join('staff', 'staff.id = server_access_requests.staff_id', 'left');
$this->db->where('server_access_requests.id', $request_id);
$row = $this->db->get()->row_array();
?>
<header class="panel-heading" style="background: #e7f1ff; border-bottom: 1px solid #ccc;">
    <div class="d-flex align-items-center">
        <div>
            <h4 class="panel-title mb-xs" style="color: #0054a6;"><i class="fas fa-shield-alt"></i> <?= translate('server_access_review') ?></h4>
        </div>
    </div>
</header>

<div class="panel-body" style="background: #f9fafe;">
    <input type="hidden" name="id" value="<?= $request_id ?>">

    <div class="row mb-md">
        <div class="col-md-6">
            <label class="text-bold">👤 Applicant:</label> <?= ucfirst($row['staff_name']) ?> (<?= $row['staffid'] ?>)
        </div>
        <div class="col-md-6">
            <label class="text-bold">🗓️ Applied On:</label> <?= _d($row['created_at']) ?>
        </div>
    </div>

    <div class="row mb-md">
        <div class="col-md-6">
            <label class="text-bold">🖥️ Server Name / IP:</label> <?= ucfirst($row['server_name']) ?>
        </div>
        <div class="col-md-6" style="word-wrap: break-word; white-space: normal;">
			<label class="text-bold">📁 Directory:</label> <?= htmlspecialchars($row['server_directory']) ?>
		</div>
    </div>

    <div class="mb-md">
        <label class="text-bold">📝 Reason:</label>
        <div class="well well-sm" style="min-height: 50px;">
            <?= empty($row['reason']) ? '<span class="text-muted">N/A</span>' : nl2br(htmlspecialchars($row['reason'])) ?>
        </div>
    </div>
	
   <div class="mb-md">
		<label class="text-bold">🔄 Status:</label>
		<?php
			$status = (int) $row['status'];
			if ($status === 1) {
				echo '<span class="badge badge-warning"><i class="fas fa-hourglass-half"></i> Pending</span>';
			} elseif ($status === 2) {
				echo '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Approved</span>';
			} elseif ($status === 3) {
				echo '<span class="badge badge-danger"><i class="fas fa-times-circle"></i> Rejected</span>';
			} else {
				echo '<span class="badge badge-secondary"><i class="fas fa-question-circle"></i> Unknown</span>';
			}
		?>
	</div>

	
	 <div class="mb-md">
       <label class="text-bold">💬 Comments:</label>
        <div class="well well-sm" style="min-height: 50px;">
            <?= empty($row['comments']) ? '<span class="text-muted">N/A</span>' : nl2br(htmlspecialchars($row['comments'])) ?>
        </div>
    </div>
	
	<!-- Reminder Note -->
	<div class="alert alert-info mt-lg" style="margin-top: 20px;">
		<i class="fas fa-info-circle"></i>
		<strong>Reminder:</strong> If this request is approved, you will receive a secret code that is valid for <strong>1 hour</strong> from the moment you open it.
	</div>
</div>

<footer class="panel-footer">
	<div class="row">
		<div class="col-md-12 text-right">
			<button class="btn btn-default modal-dismiss"><?=translate('close')?></button>
		</div>
	</div>
</footer>
<style>
.badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    color: #fff;
}
.badge-warning { background-color: #f0ad4e; }
.badge-success { background-color: #5cb85c; }
.badge-danger  { background-color: #d9534f; }
.badge-secondary { background-color: #777; }
</style>
