<section class="panel">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<li>
				<a href="<?= base_url('sop') ?>">
					<i class="fas fa-list-ul"></i> <?= translate('sop') . " " . translate('list') ?>
				</a>
			</li>
			<li class="active">
				<a href="#edit" data-toggle="tab">
					<i class="far fa-edit"></i> <?= translate('edit') . " " . translate('sop') ?>
				</a>
			</li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active" id="edit">
				<?php echo form_open(current_url(), ['method' => 'post', 'class' => 'form-bordered form-horizontal', 'autocomplete' => 'off']); ?>
				<input type="hidden" name="id" value="<?= $sop_list['id'] ?>">

				<!-- SOP Title -->
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('title')?> <span class="required">*</span></label>
					<div class="col-md-8">
						<input type="text" class="form-control" name="title" value="<?= $sop_list['title'] ?>" required />
					</div>
				</div>

				<!-- Task Purpose -->
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('task_purpose')?> <span class="required">*</span></label>
					<div class="col-md-8">
						<textarea name="task_purpose" class="form-control" rows="2" required><?= $sop_list['task_purpose'] ?></textarea>
					</div>
				</div>

				<!-- Instructions -->
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('instructions')?> <span class="required">*</span></label>
					<div class="col-md-8">
						<textarea name="instructions" class="summernote form-control" rows="3" required><?= $sop_list['instructions'] ?></textarea>
					</div>
				</div>

				<!-- Executor Stage -->
				<?php
				$executor = json_decode($sop_list['executor_stage'], true);
				$executor_labels = !empty($executor['labels']) ? $executor['labels'] : [''];
				?>
				<div class="form-group">
					<label class="col-md-3 control-label">Executor Stage Title</label>
					<div class="col-md-8">
						<input type="text" name="executor_stage_title" class="form-control" placeholder="Executor Stage Title (e.g. Task Execution)" value="<?= isset($executor['title']) ? $executor['title'] : '' ?>" />
						<div id="executor-labels-wrapper" class="mt-sm">
							<?php foreach ($executor_labels as $label): ?>
								<div class="label-row mb-sm">
									<div class="row">
										<div class="col-md-11">
											<input type="text" name="executor_stage_labels[]" class="form-control" value="<?= $label ?>" placeholder="Executor Stage Label" required />
										</div>
										<div class="col-md-1">
											<button type="button" class="btn btn-danger btn-remove-executor-label"><i class="fas fa-trash"></i></button>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
						<button type="button" class="btn btn-sm btn-default mt-xs" id="add-executor-label"><i class="fas fa-plus"></i> Add Label</button>
					</div>
				</div>
				<!-- Verifier Stage -->
				<?php
				$verifier = json_decode($sop_list['verifier_stage'], true);
				$verifier_labels = !empty($verifier['labels']) ? $verifier['labels'] : [''];
				?>
				<div class="form-group">
					<label class="col-md-3 control-label">Verifier Stage Title</label>
					<div class="col-md-8">
						<input type="text" name="verifier_stage_title" class="form-control" placeholder="Verifier Stage Title (e.g. Approval Process)" value="<?= isset($verifier['title']) ? $verifier['title'] : '' ?>" />
						<div id="verifier-labels-wrapper" class="mt-sm">
							<?php foreach ($verifier_labels as $label): ?>
								<div class="label-row mb-sm">
									<div class="row">
										<div class="col-md-11">
											<input type="text" name="verifier_stage_labels[]" class="form-control" value="<?= $label ?>" placeholder="Verifier Stage Label" required />
										</div>
										<div class="col-md-1">
											<button type="button" class="btn btn-danger btn-remove-verifier-label"><i class="fas fa-trash"></i></button>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
						<button type="button" class="btn btn-sm btn-default mt-xs" id="add-verifier-label"><i class="fas fa-plus"></i> Add Label</button>
					</div>
				</div>
				
				<!-- Proof Required -->
				<div class="form-group">
					<label class="col-md-3 control-label">Proof Required</label>
					<div class="col-md-8 checkbox">
						<label><input type="checkbox" name="proof_required_text" value="1" <?= $sop_list['proof_required_text'] ? 'checked' : '' ?>> Text</label>
						<label><input type="checkbox" name="proof_required_image" value="1" <?= $sop_list['proof_required_image'] ? 'checked' : '' ?>> Image</label>
						<label><input type="checkbox" name="proof_required_file" value="1" <?= $sop_list['proof_required_file'] ? 'checked' : '' ?>> File</label>
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
						$selectedRoles = !empty($sop_list['verifier_role']) ? explode(',', $sop_list['verifier_role']) : [];
						echo form_dropdown(
							"verifier_role[]",
							$array,
							$selectedRoles,
							"class='form-control' id='verifier_role' required multiple data-plugin-selectTwo data-placeholder=\"Select\" data-width='100%'"
						);
						?>
					</div>
				</div>

				<!-- Expected Duration -->
				<div class="form-group">
					<label class="col-md-3 control-label">Expected Duration</label>
					<div class="col-md-8">
						<input type="text" class="form-control" name="expected_time" value="<?= $sop_list['expected_time'] ?>" />
					</div>
				</div>

				<!-- Submit -->
				<footer class="panel-footer">
					<div class="row">
						<div class="col-md-offset-3 col-md-2">
							<button type="submit" class="btn btn-default btn-block" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
								<i class="fas fa-save"></i> <?= translate('update') ?>
							</button>
						</div>
					</div>
				</footer>

				<?php echo form_close(); ?>
			</div>
		</div>
	</div>
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

<style>
.label-row .row {
    display: flex;
    align-items: center;
    margin: 0;
}

.label-row .col-md-11 {
    flex: 1;
    padding-right: 5px;
}

.label-row .col-md-1 {
    flex: 0 0 auto;
    padding-left: 5px;
    text-align: right;
}

.btn-remove-executor-label,
.btn-remove-verifier-label {
    white-space: nowrap;
    min-width: 40px;
}

@media (max-width: 768px) {
    .label-row .row {
        flex-wrap: nowrap;
    }
    
    .label-row .col-md-11 {
        flex: 1;
        min-width: 0;
    }
    
    .label-row .col-md-1 {
        flex: 0 0 50px;
    }
    
    .btn-remove-executor-label,
    .btn-remove-verifier-label {
        padding: 6px 8px;
        font-size: 12px;
    }
}
</style>
