<section class="panel">
    <header class="panel-heading">
        <h4 class="panel-title"><i class="far fa-edit"></i> <?= translate('edit_label') ?></h4>
    </header>
    <?php echo form_open('tracker/update_label/' . $label->id, ['class' => 'form-horizontal frm-submit']); ?>
        <div class="panel-body">
            <div class="form-group">
                <label class="col-md-3 control-label"><?= translate('label_name') ?> <span class="required">*</span></label>
                <div class="col-md-8">
                    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($label->name) ?>" required>
                    <span class="error"></span>
                </div>
            </div>
            <div class="form-group mb-md">
                <label class="col-md-3 control-label"><?= translate('description') ?></label>
                <div class="col-md-8">
                    <textarea class="form-control" name="description" rows="2"><?= htmlspecialchars($label->description) ?></textarea>
                </div>
            </div>
        </div>
        <footer class="panel-footer">
            <div class="row">
                <div class="col-md-12 text-right">
                    <button type="submit" class="btn btn-default" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
                        <i class="far fa-save"></i> <?= translate('update') ?>
                    </button>
                    <button class="btn btn-default modal-dismiss"><?= translate('cancel') ?></button>
                </div>
            </div>
        </footer>
    <?php echo form_close(); ?>
</section>
