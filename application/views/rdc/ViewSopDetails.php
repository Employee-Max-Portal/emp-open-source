<?php
$this->db->select('sop.*, staff.name as staff_name, staff.staff_id as staffid');
$this->db->from('sop');
$this->db->join('staff', 'staff.id = sop.created_by', 'left');
$this->db->where('sop.id', $request_id);
$row = $this->db->get()->row_array();
?>
<style>
@media (min-width: 992px) {
    .modal-lg {
        width: 90%;
    }
}
@media (min-width: 768px) {
    .modal-dialog {
        margin: 20% auto;
    }
}
</style>
<header class="panel-heading" style="background: #e7f1ff; border-bottom: 1px solid #ccc;">
    <div class="d-flex align-items-center">
        <div>
            <h4 class="panel-title mb-xs" style="color: #0054a6;">
                <i class="fas fa-scroll"></i> <?= translate('SOP Review') ?>
            </h4>
            <p class="text-muted mb-none">Review the Standard Operating Procedure in detail</p>
        </div>
    </div>
</header>

<div class="panel-body" style="background: #f9fafe;">
    <input type="hidden" name="id" value="<?= $request_id ?>">

    <!-- Title & Author -->
    <div class="row mb-md">
        <div class="col-md-6">
            <label class="text-bold">📄 Title:</label> <?= html_escape($row['title']) ?>
        </div>
        <!--<div class="col-md-6">
            <label class="text-bold">👤 Created By:</label> <?= html_escape($row['staff_name']) ?> (<?= html_escape($row['staffid']) ?>)
        </div> -->
    </div>

    <!-- Task Purpose -->
    <div class="mb-md">
        <label class="text-bold">🎯 Task Purpose:</label>
        <div class="well well-sm"><?= nl2br(html_escape($row['task_purpose'])) ?></div>
    </div>

    <!-- Instructions -->
    <div class="mb-md">
        <label class="text-bold">📝 Instructions:</label>
        <div class="well well-sm">
            <?= $row['instructions'] ? $row['instructions'] : '<span class="text-muted">No instructions provided.</span>' ?>
        </div>
    </div>
	
    <!-- Executor Flow -->
    <div class="mb-md">
        <label class="text-bold">Executor Flow:</label>
        <button type="button" class="btn btn-default btn-circle icon" data-toggle="modal" data-target="#executorModal"><i class="fas fa-eye"></i></button>
    </div>
	
    <!-- Verifier Flow  -->
    <div class="mb-md">
        <label class="text-bold">Verifier Flow:</label>
        <button type="button" class="btn btn-default btn-circle icon" data-toggle="modal" data-target="#verifierModal"><i class="fas fa-eye"></i></button>
    </div>

    <!-- Proof Required -->
    <div class="mb-md">
        <label class="text-bold">📎 Proof Required:</label>
        <ul style="padding-left: 20px;">
            <?php if ($row['proof_required_text']) echo '<li>Text</li>'; ?>
            <?php if ($row['proof_required_image']) echo '<li>Image</li>'; ?>
            <?php if ($row['proof_required_file']) echo '<li>File</li>'; ?>
        </ul>
    </div>

    <!-- Verifier Roles -->
    <div class="mb-md">
        <label class="text-bold">👥 Verifier Roles:</label>
        <?php
        $verifier_ids = explode(',', $row['verifier_role']);
        $verifier_names = [];
        foreach ($verifier_ids as $rid) {
            $role = $this->db->get_where('roles', ['id' => $rid])->row();
            if ($role) {
                $verifier_names[] = $role->name;
            }
        }
        echo implode(', ', $verifier_names) ?: '<span class="text-muted">N/A</span>';
        ?>
    </div>

    <!-- Expected Time -->
    <div class="mb-md">
        <label class="text-bold">⏱️ Expected Time:</label> <?= translate($row['expected_time']) ?: 'N/A' ?>
    </div>
</div>

<footer class="panel-footer bg-light-gray">
    <div class="row">
        <div class="col-md-12 text-right">
            <button class="btn btn-default modal-dismiss"><?= translate('close') ?></button>
        </div>
    </div>
</footer>



<!-- Executor Flow Modal -->
<div class="modal fade" id="executorModal" tabindex="-1" role="dialog" aria-labelledby="executorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="executorModalLabel">Executor Flow Diagram</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body text-center">
        <?php if ($row['executor_mermaid']) { ?>
            <img src="<?= mermaidImageUrl($row['executor_mermaid']) ?>" alt="Executor Flow" style="max-width: 100%;">
        <?php } else { ?>
            <p class="text-muted">No executor flow defined.</p>
        <?php } ?>
      </div>
    </div>
  </div>
</div>

<!-- Verifier Flow Modal -->
<div class="modal fade" id="verifierModal" tabindex="-1" role="dialog" aria-labelledby="verifierModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="verifierModalLabel">Verifier Flow Diagram</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body text-center">
        <?php if ($row['verifier_mermaid']) { ?>
            <img src="<?= mermaidImageUrl($row['verifier_mermaid']) ?>" alt="Verifier Flow" style="max-width: 100%;">
        <?php } else { ?>
            <p class="text-muted">No verifier flow defined.</p>
        <?php } ?>
      </div>
    </div>
  </div>
</div>