<?php
// Get task details with proof files
$task = $this->db->select('id, title, proof_image, proof_file, proof_text')
                 ->where('id', $task_id)
                 ->get('rdc_task')
                 ->row();

if (!$task) {
    echo '<div class="alert alert-danger">Task not found</div>';
    return;
}
?>

<header class="panel-heading">
    <h4 class="panel-title">
        <i class="fas fa-paperclip"></i> <?= translate('proof_files') ?> - <?= htmlspecialchars($task->title) ?>
    </h4>
</header>

<div class="panel-body" style="max-height: 70vh; overflow-y: auto;">
    
    <?php if (!empty($task->proof_text)): ?>
    <div class="form-group">
        <label><i class="fas fa-file-text"></i> <?= translate('proof_text') ?>:</label>
        <div class="well" style="background: #f9f9f9; padding: 15px; border-radius: 5px;">
            <?= nl2br(htmlspecialchars($task->proof_text)) ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($task->proof_image)): ?>
    <div class="form-group">
        <label><i class="fas fa-image"></i> <?= translate('proof_images') ?>:</label>
        <?php 
        $images = json_decode($task->proof_image, true);
        if (!is_array($images)) {
            $images = [$task->proof_image]; // Handle old single image format
        }
        ?>
        <?php foreach ($images as $index => $image): ?>
        <div style="margin: 15px 0; padding: 10px; border: 1px solid #eee; border-radius: 5px;">
            <div style="text-align: center; margin-bottom: 10px;">
                <img src="<?= base_url('uploads/attachments/rdc_proofs/' . $image) ?>" 
                     alt="Proof Image <?= $index + 1 ?>" 
                     style="max-width: 100%; max-height: 300px; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            </div>
            <div style="text-align: center;">
                <a href="<?= base_url('uploads/attachments/rdc_proofs/' . $image) ?>" 
                   target="_blank" 
                   class="btn btn-primary btn-sm">
                    <i class="fas fa-download"></i> <?= translate('download') ?> <?= $index + 1 ?>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($task->proof_file)): ?>
    <div class="form-group">
        <label><i class="fas fa-file"></i> <?= translate('proof_files') ?>:</label>
        <?php 
        $files = json_decode($task->proof_file, true);
        if (!is_array($files)) {
            $files = [$task->proof_file]; // Handle old single file format
        }
        ?>
        <?php foreach ($files as $index => $file): ?>
        <div style="margin: 15px 0; padding: 15px; border: 1px solid #eee; border-radius: 5px; background: #f9f9f9;">
            <div style="text-align: center; margin-bottom: 10px;">
                <i class="fas fa-file-alt" style="font-size: 36px; color: #6c757d; margin-bottom: 10px;"></i>
                <p style="margin: 0; color: #6c757d; font-weight: 500;"><?= translate('file') ?> <?= $index + 1 ?></p>
            </div>
            <div style="text-align: center;">
                <a href="<?= base_url('uploads/attachments/rdc_proofs/' . $file) ?>" 
                   target="_blank" 
                   class="btn btn-success btn-sm">
                    <i class="fas fa-download"></i> <?= translate('download') ?> <?= $index + 1 ?>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (empty($task->proof_text) && empty($task->proof_image) && empty($task->proof_file)): ?>
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle"></i> <?= translate('no_proof_files_available') ?>
    </div>
    <?php endif; ?>

</div>

<footer class="panel-footer text-right">
    <button type="button" class="btn btn-default" onclick="$.magnificPopup.close();">
        <i class="fas fa-times"></i> <?= translate('close') ?>
    </button>
</footer>