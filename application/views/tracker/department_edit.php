<section class="panel">
    <header class="panel-heading">
        <h4 class="panel-title"><i class="fas fa-edit"></i> <?= translate('edit_milestone') ?></h4>
    </header>
    <?php echo form_open('tracker/update_department', ['class' => 'form-horizontal frm-submit', 'data-url' => current_url()]); ?>
	
    <div class="panel-body">
		<input type="hidden" name="id" value="<?= $department->id ?>">
		<input type="hidden" name="redirect_url" value="<?= current_url() ?>">

		<div class="form-group">
			<label class="col-md-3 control-label"><?= translate('title') ?> <span class="required">*</span></label>
			<div class="col-md-8">
				<input type="text" class="form-control" name="title" value="<?= html_escape($department->title) ?>" required />
			</div>
		</div>

		<div class="form-group">
			<label class="col-md-3 control-label"><?= translate('identifier') ?> (ID)</label>
			<div class="col-md-4">
				<input type="text" class="form-control" name="identifier" value="<?= html_escape($department->identifier) ?>" placeholder="e.g. TSK" />
			</div>
		</div>

		<div class="form-group">
			<label class="col-md-3 control-label"><?= translate('description') ?></label>
			<div class="col-md-8">
				<textarea name="description" class="form-control" rows="3"><?= html_escape($department->description) ?></textarea>
			</div>
		</div>

		<div class="form-group">
			<label class="col-md-3 control-label"><?= translate('default_status') ?></label>
			<div class="col-md-8">
				<select name="default_status" class="form-control">
					<?php
					$statuses = ['backlog', 'hold', 'todo', 'submitted', 'in_progress', 'in_review', 'planning', 'observation', 'waiting', 'done', 'solved', 'canceled'];
					foreach ($statuses as $status):
					?>
						<option value="<?= $status ?>" <?= ($department->default_status == $status) ? 'selected' : '' ?>>
							<?= translate($status) ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<div class="form-group">
			<label class="col-md-3 control-label"><?= translate('assigned_for_issues') ?></label>
			<div class="col-md-8">
				<?php
				$staff_array = $this->app_lib->getSelectList('staff');
				unset($staff_array[1]); // remove superadmin
				echo form_dropdown("assigned_issuer", $staff_array, $department->assigned_issuer ?? '', "class='form-control' id='assigned_issuer' data-plugin-selectTwo data-placeholder=\"Select\" data-width='100%' ");
				?>
			</div>
		</div>

		<div class="form-group mb-md">
			<label class="col-md-3 control-label"><?= translate('auto_join') ?></label>
			<div class="col-md-8">
				<div class="checkbox-replace">
					<label class="i-checks">
						<input type="checkbox" name="auto_join" value="1" <?= ($department->auto_join) ? 'checked' : '' ?>>
						<i></i> <?= translate('allow_members_to_join_without_invite') ?>
					</label>
				</div>
			</div>
		</div>

		<div class="form-group mb-md">
			<label class="col-md-3 control-label"><?= translate('make_private') ?></label>
			<div class="col-md-8">
				<div class="checkbox-replace">
					<label class="i-checks">
						<input type="checkbox" name="is_private" value="1" <?= ($department->is_private) ? 'checked' : '' ?>>
						<i></i> <?= translate('only_members_can_see') ?>
					</label>
				</div>
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
<script>
$(document).ready(function () {
    // Auto-generate identifier on title input
    $("input[name='title']").on("input", function () {
        let title = $(this).val();
        let identifier = title.substring(0, 4).toUpperCase().replace(/[^A-Z0-9]/g, '');
        $("input[name='identifier']").val(identifier);
    });

    // Restrict manual input on identifier
    $("input[name='identifier']").on("input", function () {
        let val = $(this).val();
        val = val.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 4);
        $(this).val(val);
    });
});
</script>