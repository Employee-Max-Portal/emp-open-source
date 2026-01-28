<section class="panel">
<div class="tabs-custom">
<ul class="nav nav-tabs">
    <li class="nav-item">
        <a class="nav-link" href="<?php echo base_url('kpi'); ?>">
            <i class="fas fa-list-ul"></i> <?php echo translate('objective') . ' ' . translate('list'); ?>
        </a>
    </li>
    <?php if (get_permission('salary_template', 'is_add')): ?>
    <li class="nav-item active">
        <a class="nav-link active" href="#template_edit" data-toggle="tab">
            <i class="fas fa-list-ul"></i> <?php echo translate('edit') . ' ' . translate('objective'); ?>
        </a>
    </li>
    <?php endif; ?>
</ul>

		
<div class="tab-content">
<?php echo form_open($this->uri->uri_string(), array('class' => 'form-horizontal form-bordered frm-submit')); ?>
<input type="hidden" name="form_id" value="<?= $template_id ?>">


<div class="form-group">
	<label class="col-md-3 control-label"><?= translate('objective_name') ?> <span class="required">*</span></label>
	<div class="col-md-6">
		<input type="text" name="objective_name" class="form-control" value="<?= html_escape($template['objective_name']) ?>" />
	</div>
</div>

<div class="form-group">
	<label class="col-md-3 control-label"><?= translate('assigned_to') ?> <span class="required">*</span></label>
	<div class="col-md-6">
		<?php
		$staff = $this->app_lib->getSelectList('staff');
		unset($staff[1]); // remove superadmin if needed
		echo form_dropdown("staff_id", $staff, set_value('staff_id', $template['staff_id']), "class='form-control' data-plugin-selectTwo");
		?>
	</div>
</div>

<div class="form-group">
	<label class="col-md-3 control-label"><?= translate('assigned_manager') ?> <span class="required">*</span></label>
	<div class="col-md-6">
		<?php
		echo form_dropdown("manager_id", $staff, set_value('manager_id', $template['manager_id']), "class='form-control' data-plugin-selectTwo");
		?>
	</div>
</div>

<div class="form-group">
	<label class="col-md-3 control-label"><?= translate('estimate_date') ?> <span class="required">*</span></label>
	<div class="col-md-6">
		<div class="input-group">
			<span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
			<input type="text" class="form-control" name="daterange" id="daterange" value="<?= html_escape($template['daterange']) ?>" required />
		</div>
	</div>
</div>

<!-- Subtasks Section -->
<div class="row">
	<div class="col-md-12 mt-lg">
		<section class="panel panel-custom">
			<header class="panel-heading panel-heading-custom">
				<h4 class="panel-title"><?= translate('Objective_lists') ?></h4>
			</header>
			<div class="panel-body">
				<?php
				if (!empty($subtasks)):
					foreach ($subtasks as $key => $task): ?>
						<div class="row <?= ($key > 0) ? 'mt-md' : '' ?>" id="subtask_row_<?= $key ?>">
							<div class="col-md-4">
								<input type="text" name="subtasks[<?= $key ?>][name]" class="form-control" value="<?= html_escape($task['name']) ?>" placeholder="<?= translate('name') ?>" />
							</div>
							<div class="col-md-4">
								<input type="text" name="subtasks[<?= $key ?>][description]" class="form-control" value="<?= html_escape($task['description']) ?>" placeholder="<?= translate('description') ?>" />
							</div>
							<div class="col-md-3">
								<input type="number" name="subtasks[<?= $key ?>][weight]" class="form-control" value="<?= intval($task['weight']) ?>" placeholder="<?= translate('weight') ?>" min="0" />
							</div>
							<div class="col-md-1 text-right">
								<button type="button" class="btn btn-danger" onclick="$('#subtask_row_<?= $key ?>').remove();">
									<i class="fas fa-times"></i>
								</button>
							</div>
						</div>
				<?php endforeach;
				else: ?>
					<div class="row" id="subtask_row_0">
						<div class="col-md-4">
							<input type="text" name="subtasks[0][name]" class="form-control" placeholder="<?= translate('sub_task_name') ?>" />
						</div>
						<div class="col-md-4">
							<input type="text" name="subtasks[0][description]" class="form-control" placeholder="<?= translate('description') ?>" />
						</div>
						<div class="col-md-3">
							<input type="number" name="subtasks[0][weight]" class="form-control" placeholder="<?= translate('weight') ?>" min="0" />
						</div>
						<div class="col-md-1 text-right"></div>
					</div>
				<?php endif; ?>

				<div id="add_new_subtask"></div>
				<button type="button" class="btn btn-default mt-md" onclick="addSubtaskRows()">
					<i class="fas fa-plus-circle"></i> <?= translate('add_rows') ?>
				</button>
			</div>
		</section>
	</div>
</div>

<footer class="panel-footer">
	<div class="row">
		<div class="col-md-offset-9 col-md-3">
			<button type="submit" class="btn btn-default btn-block" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
				<i class="fas fa-plus-circle"></i> <?= translate('update') ?>
			</button>
		</div>
	</div>
</footer>

<?php echo form_close(); ?>
</div>
</div>
</section>
<script type="text/javascript">
	let iSubtask = <?= count($subtasks) ?: 1 ?>;

	function addSubtaskRows() {
		const html = `
			<div class="row mt-md" id="subtask_row_${iSubtask}">
				<div class="col-md-4">
					<input type="text" name="subtasks[${iSubtask}][name]" class="form-control" placeholder="<?= translate('name') ?>" />
				</div>
				<div class="col-md-4">
					<input type="text" name="subtasks[${iSubtask}][description]" class="form-control" placeholder="<?= translate('description') ?>" />
				</div>
				<div class="col-md-3">
					<input type="number" name="subtasks[${iSubtask}][weight]" class="form-control" placeholder="<?= translate('weight') ?>" min="0" />
				</div>
				<div class="col-md-1 text-right">
					<button type="button" class="btn btn-danger" onclick="$('#subtask_row_${iSubtask}').remove();">
						<i class="fas fa-times"></i>
					</button>
				</div>
			</div>`;
		$('#add_new_subtask').append(html);
		iSubtask++;
	}

	$(document).ready(function () {
		$('#daterange').daterangepicker({
			opens: 'left',
			locale: { format: 'YYYY/MM/DD' }
		});
	});
</script>
