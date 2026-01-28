<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">⚠️ Warning of <strong><?php echo $category ; ?></strong> that affects the <strong><?php echo $effect ; ?></strong></h5>
    </div>
    <div class="card-body" id="description-content" style="font-size:15px; line-height:1.8; color:#333;">
        <?= htmlspecialchars_decode($description) ?>

    </div>
</div>
