<section class="panel">
    <header class="panel-heading">
        <h4 class="panel-title"><i class="fas fa-edit"></i> <?= translate('edit_initiatives') ?></h4>
    </header>
    <?php echo form_open('tracker/update_initiatives', ['class' => 'form-horizontal frm-submit', 'data-url' => current_url()]); ?>
    <div class="panel-body">
        <input type="hidden" name="id" value="<?= $component->id ?>">
        <input type="hidden" name="redirect_url" value="<?= current_url() ?>">
        <div class="form-group">
            <label class="col-md-3 control-label"><?= translate('title') ?> <span class="required">*</span></label>
            <div class="col-md-8">
                <input type="text" class="form-control" name="title" value="<?= html_escape($component->title) ?>" required />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-3 control-label"><?= translate('description') ?></label>
            <div class="col-md-8">
                <textarea name="description" class="form-control" rows="3"><?= html_escape($component->description) ?></textarea>
            </div>
        </div>
		
		<div class="form-group">
			<label class="col-md-3 control-label"><i class="fas fa-user"></i> <?= translate('lead') ?> <span class="text-danger">*</span></label>
			<div class="col-md-8">
				<?php
					$array = $this->app_lib->getSelectList('staff');
					unset($array[1]); // Remove Superadmin
					echo form_dropdown(
						"lead_id",
						$array,
						set_value('lead_id', $component->lead_id),
						"class='form-control' id='lead_id' required data-plugin-selectTwo data-placeholder=\"Select\" data-width='100%'"
					);
				?>
			</div>
		</div>
    </div>
    <footer class="panel-footer">
        <div class="row">
            <div class="col-md-12 text-right">
                <button type="submit" class="btn btn-default mr-xs" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
                    <i class="fas fa-check-circle"></i> <?= translate('update') ?>
                </button>
                <button class="btn btn-default modal-dismiss"><?= translate('cancel') ?></button>
            </div>
        </div>
    </footer>
    <?php echo form_close(); ?>
</section>
