<?php if ($unsynced_funds > 0): ?>
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    <strong>Sync Required:</strong> There are <?= $unsynced_funds ?> paid fund requisitions that need to be synced with the cashbook.
    <a href="<?= base_url('cashbook/sync_fund_requisitions') ?>" class="btn btn-sm btn-primary ml-2">
        <i class="fas fa-sync"></i> Sync Now
    </a>
</div>
<?php endif; ?>