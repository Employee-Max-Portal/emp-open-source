<?php
if (!empty($sop_ids)) {
    $sop_ids_array = json_decode($sop_ids, true);
	
    //if (is_array($sop_ids_array) && !empty($sop_ids_array)) {
        $sops = $this->db->select('*')->where_in('id', $sop_ids_array)->get('sop')->result_array();
    // } else {
        // $sops = [];
    // }
} else {
    $sops = [];
}
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
.sop-item {
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 20px;
    padding: 15px;
    background: #fff;
}
.sop-number {
    background: #0054a6;
    color: white;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 10px;
}
</style>

<header class="panel-heading" style="background: #e7f1ff; border-bottom: 1px solid #ccc;">
    <div class="d-flex align-items-center">
        <div>
            <h4 class="panel-title mb-xs" style="color: #0054a6;">
                <i class="fas fa-scroll"></i> <?= translate('Linked SOPs Review') ?>
            </h4>
            <p class="text-muted mb-none">Review all Standard Operating Procedures linked to this task</p>
        </div>
    </div>
</header>

<div class="panel-body" style="background: #f9fafe;">
    <?php if (!empty($sops)): ?>
        <?php foreach ($sops as $index => $sop): ?>
            <div class="sop-item">
                <div class="row mb-md">
                    <div class="col-md-12">
                        <h5>
                            <span class="sop-number"><?= $index + 1 ?></span>
                            <?= html_escape($sop['title']) ?>
                        </h5>
                    </div>
                </div>

                <!-- Task Purpose -->
                <div class="mb-md">
                    <label class="text-bold">🎯 Task Purpose:</label>
                    <div class="well well-sm"><?= nl2br(html_escape($sop['task_purpose'])) ?></div>
                </div>

                <!-- Instructions -->
                <div class="mb-md">
                    <label class="text-bold">📝 Instructions:</label>
                    <div class="well well-sm">
                        <?= $sop['instructions'] ? $sop['instructions'] : '<span class="text-muted">No instructions provided.</span>' ?>
                    </div>
                </div>

                <!-- Executor Flow -->
                <div class="mb-md">
                    <label class="text-bold">Executor Flow:</label>
                    <button type="button" class="btn btn-default btn-circle icon" onclick="showFlowImage('<?= addslashes(mermaidImageUrl($sop['executor_mermaid'])) ?>', 'Executor Flow - <?= addslashes($sop['title']) ?>')"><i class="fas fa-eye"></i></button>
                </div>

                <!-- Verifier Flow -->
                <div class="mb-md">
                    <label class="text-bold">Verifier Flow:</label>
                    <button type="button" class="btn btn-default btn-circle icon" onclick="showFlowImage('<?= addslashes(mermaidImageUrl($sop['verifier_mermaid'])) ?>', 'Verifier Flow - <?= addslashes($sop['title']) ?>')"><i class="fas fa-eye"></i></button>
                </div>

                <!-- Proof Required -->
                <div class="mb-md">
                    <label class="text-bold">📎 Proof Required:</label>
                    <ul style="padding-left: 20px;">
                        <?php if ($sop['proof_required_text']) echo '<li>Text</li>'; ?>
                        <?php if ($sop['proof_required_image']) echo '<li>Image</li>'; ?>
                        <?php if ($sop['proof_required_file']) echo '<li>File</li>'; ?>
                    </ul>
                </div>

                <!-- Verifier Roles -->
                <div class="mb-md">
                    <label class="text-bold">👥 Verifier Roles:</label>
                    <?php
                    $verifier_ids = explode(',', $sop['verifier_role']);
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
                    <label class="text-bold">⏱️ Expected Time:</label> <?= translate($sop['expected_time']) ?: 'N/A' ?>
                </div>
            </div>


        <?php endforeach; ?>
    <?php else: ?>
        <div class="text-center">
            <p class="text-muted">No SOPs linked to this task.</p>
        </div>
    <?php endif; ?>
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
    
    // Show flow image in a new window
    function showFlowImage(imageUrl, title) {
        if (!imageUrl || imageUrl === '') {
            alert('No flow diagram available');
            return;
        }
        const win = window.open('', '_blank', 'width=1000,height=800,scrollbars=yes,resizable=yes');
        win.document.write('<html><head><title>' + title + '</title></head><body style="margin:0;padding:20px;text-align:center;background:#f5f5f5;"><h3>' + title + '</h3><img src="' + imageUrl + '" style="max-width:100%;border:1px solid #ddd;background:white;padding:10px;"></body></html>');
    }
</script>

