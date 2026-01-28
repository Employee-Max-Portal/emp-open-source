<?php
$this->db->select('server_access_requests.*, staff.name as staff_name, staff.staff_id as staffid, staff.photo');
$this->db->from('server_access_requests');
$this->db->join('staff', 'staff.id = server_access_requests.staff_id', 'left');
$this->db->where('server_access_requests.id', $request_id);
$row = $this->db->get()->row_array();
?>

<?php echo form_open('server_request'); ?>
<header class="panel-heading" style="background: #e7f1ff; border-bottom: 1px solid #ccc;">
    <div class="d-flex align-items-center">
        <!--<img src="<?= get_image_url('staff', $row['photo']) ?>" alt="avatar" style="width: 50px; height: 50px; border-radius: 50%; margin-right: 10px;">-->
        <div>
            <h4 class="panel-title mb-xs" style="color: #0054a6;"><i class="fas fa-shield-alt"></i> <?= translate('server_access_review') ?></h4>
            <p class="text-muted mb-none">Review and take action on the access request</p>
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
        <label class="text-bold">🛂 Reviewed By:</label> <?= !empty($row['issued_by']) ? html_escape(get_type_name_by_id('staff', $row['issued_by'])) : translate('unreviewed') ?>
    </div>

    <div class="mb-md">
        <label class="text-bold">📌 Status:</label>
        <div class="radio-custom radio-inline">
            <input type="radio" id="pending" name="status" value="1" <?= ($row['status'] == 1 ? ' checked' : '') ?>>
            <label for="pending"><i class="fas fa-hourglass-half text-warning"></i> <?= translate('pending') ?></label>
        </div>
        <div class="radio-custom radio-inline">
            <input type="radio" id="approved" name="status" value="2" <?= ($row['status'] == 2 ? ' checked' : '') ?>>
            <label for="approved"><i class="fas fa-check-circle text-success"></i> <?= translate('approved') ?></label>
        </div>
        <div class="radio-custom radio-inline">
            <input type="radio" id="reject" name="status" value="3" <?= ($row['status'] == 3 ? ' checked' : '') ?>>
            <label for="reject"><i class="fas fa-times-circle text-danger"></i> <?= translate('reject') ?></label>
        </div>
    </div>

    <div class="mb-md">
        <label class="text-bold">💬 Comments:</label>
        <textarea class="form-control" name="comments" rows="2"><?= html_escape($row['comments']) ?></textarea>
    </div>
</div>
<div class="alert alert-warning mt-md">
    <i class="fas fa-exclamation-triangle"></i> 
    <strong>Note:</strong> Once approved, this cannot be undone. Review the reason and directory carefully before finalizing.
</div>

<footer class="panel-footer bg-light-gray">
    <div class="row">
        <div class="col-md-12 text-right">
            <?php if ($row['status'] != 2) { ?>
                <button class="btn btn-primary" type="submit" name="update" value="1">
                    <i class="fas fa-check"></i> <?= translate('apply') ?>
                </button>
            <?php } ?>
            <button class="btn btn-default modal-dismiss"><?= translate('close') ?></button>
        </div>
    </div>
</footer>
<?php echo form_close(); ?>
