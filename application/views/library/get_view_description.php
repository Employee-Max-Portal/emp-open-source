<style>
    @media print {
        .card-header {
            display: none !important; /* hides the header in print */
        }
    }
</style>

<div class="card mb-4">

    <?php if (!empty($reference)) : ?>
        <div style="margin-bottom:15px; padding:10px; background:#f9f9f9; border:1px solid #eee;">
            <strong>Reference No:</strong>
            <span style="color:#007bff;"><?php echo $reference; ?></span>
        </div>
    <?php endif; ?>
    
    <div class="card-header" style="padding:10px 15px; background:#f5f5f5; border-bottom:1px solid #ddd;">
        <h3 class="card-title mb-0">📄 Description of <?php echo $title; ?></h3>
    </div>

    <div class="card-body" id="description-content" style="font-size:15px; line-height:1.8; color:#333;">
        <?php echo $description; ?>
    </div>

</div>
