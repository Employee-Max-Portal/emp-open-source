<?php
$this->db->select('rdc.*, staff.name as staff_name, staff.staff_id as staffid');
$this->db->from('rdc');
$this->db->join('staff', 'staff.id = rdc.created_by', 'left');
$this->db->join('rdc_task', 'rdc_task.rdc_id = rdc.id', 'left');
$this->db->where('rdc.id', $request_id);
//$this->db->or_where('rdc_task.id', $request_id);
$row = $this->db->get()->row_array();

?>

<style>
@media (min-width: 992px) {
    .modal-lg { width: 90%; }
}
@media (min-width: 768px) {
    .modal-dialog { margin: 20% auto; }
}
</style>

<header class="panel-heading" style="background: #e7f1ff; border-bottom: 1px solid #ccc;">
    <div class="d-flex align-items-center">
        <div>
            <h4 class="panel-title mb-xs" style="color: #0054a6;">
                <i class="fas fa-scroll"></i> <?= translate('RDC Review') ?>
            </h4>
            <p class="text-muted mb-none">Review the Recurring Discipline & Compliance in detail</p>
        </div>
    </div>
</header>

<div class="panel-body" style="background: #f9fafe;">
    <input type="hidden" name="id" value="<?= $request_id ?>">

    <div class="row mb-md">
        <div class="col-md-12">
            <label class="text-bold">📄 Title:</label> <?= translate($row['title']) ?>
        </div>
    </div>

    <div class="mb-md">
        <label class="text-bold">📘 Description:</label>
        <div class="well well-sm"><?= translate($row['description']) ?: '<span class="text-muted">N/A</span>' ?></div>
    </div>

    <div class="row mb-md">
        <div class="col-md-6"><label class="text-bold">📅 Frequency:</label> <?= translate($row['frequency']) ?></div>
        <div class="col-md-6"><label class="text-bold">🔔 Pre-Reminder Enabled:</label> <?= $row['pre_reminder_enabled'] ? 'Yes' : 'No' ?></div>
    </div>
	
    <div class="row mb-md">
        <div class="col-md-6"><label class="text-bold">🧾 Is Proof Required:</label>  <?= $row['is_proof_required'] ? 'Yes' : 'No' ?></div>
        <div class="col-md-6"><label class="text-bold">⚠️ Escalation Enabled:</label> <?= $row['escalation_enabled'] ? 'Yes' : 'No' ?></div>
    </div>
	
    <div class="row mb-md">
		<div class="col-md-6"><label class="text-bold">⏰ Executor Due Time:</label> 
		<?php 
		$issueDate = new DateTime($row['due_time']);
		echo $issueDate ? $issueDate->format('d/m/y \- h:i A') : 'N/A';
		?>
		</div>
		<div class="col-md-6"><label class="text-bold">⏰ Verifier Due Time:</label> 
		<?php 
		$issueDate = new DateTime($row['verifier_due_time']);
		echo $issueDate ? $issueDate->format('d/m/y \- h:i A') : 'N/A';
		?>
		</div>
    </div>

    <div class="row mb-md">
		<div class="col-md-6">
            <label class="text-bold">👤 Created By:</label> <?= translate($row['staff_name']) ?>
        </div>
        <div class="col-md-6"><label class="text-bold">📅 Created At:</label> 
		<?php 
			$issueDate = new DateTime($row['created_at']);
			//echo $issueDate->format('jS F, Y \a\t h:i A');
			echo $issueDate ? $issueDate->format('d/m/y \- h:i A') : 'N/A';
		?>
		</div>
	</div>
</div>


<footer class="panel-footer bg-light-gray">
    <div class="row">
        <div class="col-md-12 text-right">
            <button type="button" 
                    class="btn btn-default modal-dismiss" 
                    data-dismiss="modal">
                <i class="fas fa-times-circle mr-1"></i> 
                <?= translate('close'); ?>
            </button>
        </div>
    </div>
</footer>

<script>
    // For Bootstrap modal
    $(document).on('click', '.modal-dismiss', function () {
        $(this).closest('.modal').modal('hide');
    });

    // For Magnific Popup fallback
    $(document).on('click', '.modal-dismiss', function () {
        if ($.magnificPopup.instance.isOpen) {
            $.magnificPopup.close();
        }
    });
</script>

