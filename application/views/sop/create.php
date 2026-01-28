<section class="panel">
	<header class="panel-heading">
		<h4 class="panel-title"><i class="fas fa-clipboard-list"></i> <?=translate('create') . " " . translate('sop')?></h4>
	</header>
	<div class="panel-body">
		<?php echo form_open(current_url(), array('method' => 'post', 'class' => 'form-bordered form-horizontal', 'autocomplete' => 'off')); ?>

		<!-- SOP Title -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('title')?> <span class="required">*</span></label>
			<div class="col-md-8">
				<input type="text" class="form-control" name="title" required />
				<span class="error"></span>
			</div>
		</div>

		<!-- Task Purpose -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('task_purpose')?> <span class="required">*</span></label>
			<div class="col-md-8">
				<textarea name="task_purpose" class="form-control" rows="2" required></textarea>
				<span class="error"></span>
			</div>
		</div>

		<!-- Instructions -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('instructions')?> <span class="required">*</span></label>
			<div class="col-md-8">
				<textarea name="instructions" class="summernote form-control" id="instructions" rows="3" required></textarea>
				<span class="error"></span>
			</div>
		</div>
		<!-- Executor Stage -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('executor_stage_title')?></label>
			<div class="col-md-8">
				<input type="text" name="executor_stage_title" class="form-control" placeholder="Executor Stage Title (e.g. Task Execution)" required />
				<div id="executor-labels-wrapper" class="mt-sm">
					<div class="label-row mb-sm">
						<div class="row">
							<div class="col-md-11">
								<input type="text" name="executor_stage_labels[]" class="form-control" placeholder="Executor Stage Label (e.g. Step 1: Collect Data)" required />
							</div>
							<div class="col-md-1">
								<button type="button" class="btn btn-danger btn-remove-executor-label"><i class="fas fa-trash"></i></button>
							</div>
						</div>
					</div>
				</div>
				<button type="button" class="btn btn-sm btn-default mt-xs" id="add-executor-label"><i class="fas fa-plus"></i> <?=translate('add_label')?></button>
			</div>
		</div>

		<!-- Verifier Stage -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('verifier_stage_title')?></label>
			<div class="col-md-8">
				<input type="text" name="verifier_stage_title" class="form-control" placeholder="Verifier Stage Title (e.g. Approval Process)" required />
				<div id="verifier-labels-wrapper" class="mt-sm">
					<div class="label-row mb-sm">
						<div class="row">
							<div class="col-md-11">
								<input type="text" name="verifier_stage_labels[]" class="form-control" placeholder="Verifier Stage Label (e.g. Step 1: Review Document)" required />
							</div>
							<div class="col-md-1">
								<button type="button" class="btn btn-danger btn-remove-verifier-label"><i class="fas fa-trash"></i></button>
							</div>
						</div>
					</div>
				</div>
				<button type="button" class="btn btn-sm btn-default mt-xs" id="add-verifier-label"><i class="fas fa-plus"></i> <?=translate('add_label')?></button>
			</div>
		</div>



		<!-- Proof Required -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('proof_required')?> <span class="required">*</span></label>
			<div class="col-md-8">
				<div class="checkbox-inline">
					<label><input type="checkbox" name="proof_required_text" value="1" class="proof-required"> <?=translate('text')?></label>
				</div>
				<div class="checkbox-inline">
					<label><input type="checkbox" name="proof_required_image" value="1" class="proof-required"> <?=translate('image')?></label>
				</div>
				<div class="checkbox-inline">
					<label><input type="checkbox" name="proof_required_file" value="1" class="proof-required"> <?=translate('file')?></label>
				</div>
				<span class="error" id="proof-required-error" style="color:red;display:none;"></span>
			</div>
		</div>

		<!-- Verifier Role -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('verifier_role')?></label>
			<div class="col-md-8">
				<?php
					$array = $this->app_lib->getSelectList('roles');
					$keysToUnset = [1, 2, 4, 9];
					foreach ($keysToUnset as $key) {
						unset($array[$key]);
					}
					echo form_dropdown(
						"verifier_role[]",
						$array,
						array(),
						"class='form-control' id='verifier_role' required multiple data-plugin-selectTwo data-placeholder=\"Select\" data-width='100%'"
					);
				?>
				<span class="error"><?= form_error('verifier_role[]') ?></span>
			</div>
		</div>

		<!-- Expected Time -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('expected_duration')?></label>
			<div class="col-md-8">
				<input type="text" name="expected_time" class="form-control" placeholder="e.g. 2 hours, 90 mins, 1.5 days" />
			</div>
		</div>

	</div>
	<footer class="panel-footer">
		<div class="row">
			<div class="col-md-offset-3 col-md-2">
				<button type="submit" class="btn btn-default btn-block">
					<i class="fas fa-plus-circle"></i> <?=translate('save')?>
				</button>
			</div>
		</div>
	</footer>
	<?php echo form_close(); ?>
</section>

<script>
$(document).ready(function () {
    const executorLabelTemplate = `
        <div class="label-row mb-sm">
            <div class="row">
                <div class="col-md-11">
                    <input type="text" name="executor_stage_labels[]" class="form-control" placeholder="Executor Stage Label" required />
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-remove-executor-label"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>`;

    const verifierLabelTemplate = `
        <div class="label-row mb-sm">
            <div class="row">
                <div class="col-md-11">
                    <input type="text" name="verifier_stage_labels[]" class="form-control" placeholder="Verifier Stage Label" required />
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-remove-verifier-label"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>`;

    $('#add-executor-label').click(function () {
        $('#executor-labels-wrapper').append(executorLabelTemplate);
    });

    $('#add-verifier-label').click(function () {
        $('#verifier-labels-wrapper').append(verifierLabelTemplate);
    });

    $(document).on('click', '.btn-remove-executor-label', function () {
        $(this).closest('.label-row').remove();
    });

    $(document).on('click', '.btn-remove-verifier-label', function () {
        $(this).closest('.label-row').remove();
    });
});
</script>
