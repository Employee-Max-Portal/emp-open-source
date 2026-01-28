<?php $rdc = $rdc_list[0]; ?>
<section class="panel">
	<header class="panel-heading">
		<h4 class="panel-title"><i class="fas fa-clipboard-list"></i> <?= translate('edit') . " " . translate('task') ?></h4>
	</header>
	<div class="panel-body">
		<?php echo form_open(current_url(), array('method' => 'post', 'class' => 'form-bordered form-horizontal', 'autocomplete' => 'off')); ?>
		<input type="hidden" name="id" value="<?= $rdc->id ?>">

		<!-- Title -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?= translate('title') ?> <span class="required">*</span></label>
			<div class="col-md-8">
				<input type="text" class="form-control" name="title" value="<?= html_escape($rdc->title) ?>" required />
				<span class="error"></span>
			</div>
		</div>

		<!-- Description -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?= translate('description') ?> <span class="required">*</span></label>
			<div class="col-md-8">
				<textarea name="description" class="summernote form-control" id="description" rows="2" required><?= html_escape($rdc->description) ?></textarea>
				<span class="error"></span>
			</div>
		</div>

		<!-- Task Type -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('task_type')?> <span class="required">*</span></label>
			<div class="col-md-8">
				<?php
					$task_type = ['' => translate('task_types')];
					$task_type += $this->app_lib->getSelectList('task_types');
					echo form_dropdown(
						'task_type',
						$task_type,
						$rdc->task_type,
						"class='form-control' required data-plugin-selectTwo data-width='100%'"
					);
				?>
				<span class="error"></span>
			</div>
		</div>

		<!-- Milestone -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('milestone')?> <span class="required">*</span></label>
			<div class="col-md-8">
				<?php
					$tsk_milestone = ['' => translate('milestone')];
					$tsk_milestone += $this->app_lib->getSelectList_v2('tracker_milestones', '', ['status' => 'in_progress']);
					echo form_dropdown(
						'milestone',
						$tsk_milestone,
						$rdc->milestone,
						"class='form-control' required data-plugin-selectTwo data-width='100%'"
					);
				?>
				<span class="error"></span>
			</div>
		</div>

		<!-- Coordinator -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('coordinator')?> <span class="required">*</span></label>
			<div class="col-md-8">
				<?php
					$this->db->select('s.id, s.name');
					$this->db->from('staff AS s');
					$this->db->join('login_credential AS lc', 'lc.user_id = s.id');
					$this->db->where('lc.active', 1);
					$this->db->where_not_in('lc.role', [1, 9, 10, 11, 12]);
					$this->db->where_not_in('s.id', [49]);
					$this->db->order_by('s.name', 'ASC');
					$query = $this->db->get();

					$coordinatorArray = ['' => 'Select Coordinator'];
					foreach ($query->result() as $row) {
						$coordinatorArray[$row->id] = $row->name;
					}
					echo form_dropdown("coordinator", $coordinatorArray, $rdc->coordinator, "class='form-control' required data-plugin-selectTwo data-width='100%'");
				?>
				<span class="error"></span>
			</div>
		</div>

		<!-- Initiatives -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('initiatives')?></label>
			<div class="col-md-8">
				<?php
					$tsk_component = ['' => translate('initiatives')];
					foreach ($this->db->get('tracker_components')->result() as $task_component) {
						$tsk_component[$task_component->id] = $task_component->title;
					}
					echo form_dropdown(
						'initiatives',
						$tsk_component,
						$rdc->initiatives,
						"class='form-control' data-plugin-selectTwo data-width='100%'"
					);
				?>
				<span class="error"></span>
			</div>
		</div>

		<!-- Assigned To -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?= translate('assigned_to') ?> <span class="required">*</span></label>
			<div class="col-md-8">
				<?php
					$this->db->select('s.id, s.name');
					$this->db->from('staff AS s');
					$this->db->join('login_credential AS lc', 'lc.user_id = s.id');
					$this->db->where('lc.active', 1);   // only active users
					$this->db->where_not_in('lc.role', [1, 9, 11,12]);   // exclude super admin, etc.
					$this->db->where_not_in('s.id', [49]);
					$this->db->order_by('s.name', 'ASC');
					$query = $this->db->get();

					$staffArray = ['' => 'Select',
					'random' => 'Random Assignment (Rotation)',
					'multi_random' => 'Multi-User Random Assignment']; // <-- default first option
					foreach ($query->result() as $row) {
						$staffArray[$row->id] = $row->name;
					}
					// Determine current selection
					$current_selection = $rdc->assigned_user;
					if ($rdc->is_random_assignment && !empty($rdc->user_pool)) {
						$current_selection = 'multi_random';
					} elseif ($rdc->is_random_assignment) {
						$current_selection = 'random';
					}
					
					echo form_dropdown("assigned_user", $staffArray, $current_selection, "class='form-control' id='assigned_user' required data-plugin-selectTwo data-placeholder=\"Select\" data-width='100%' ");
				?>
			</div>
		</div>
		
		<!-- User Pool Selection -->
		<div class="form-group" id="user_pool_section" style="<?= (!empty($rdc->user_pool)) ? 'display: block;' : 'display: none;' ?>">
			<label class="col-md-3 control-label"><?=translate('Select Users for Pool')?> <span class="required">*</span></label>
			<div class="col-md-8">
				<?php
					$this->db->select('s.id, s.name');
					$this->db->from('staff AS s');
					$this->db->join('login_credential AS lc', 'lc.user_id = s.id');
					$this->db->where('lc.active', 1);   // only active users
					$this->db->where_not_in('lc.role', [1, 9, 11,12]);   // exclude super admin, etc.
					$this->db->where_not_in('s.id', [49]);
					$this->db->order_by('s.name', 'ASC');
					$query = $this->db->get();

					$staffArray = ['' => 'Select']; // <-- default first option
					foreach ($query->result() as $row) {
						$staffArray[$row->id] = $row->name;
					}

					$selected_pool = !empty($rdc->user_pool) ? json_decode($rdc->user_pool, true) : [];
					echo form_dropdown("user_pool[]", $staffArray, $selected_pool, "class='form-control' id='user_pool' multiple data-plugin-selectTwo data-width='100%' data-placeholder='Select multiple users for random assignment'");
				?>
				<span class="error"></span>
				<small class="help-block">Select multiple users from whom one will be randomly assigned for each task.</small>
			</div>
		</div>

		<!-- SOP -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?= translate('Select SOPs') ?> <span class="required">*</span></label>
			<div class="col-md-8">
				<?php
					$array = $this->app_lib->getSelectList('sop');
					$selected_sops = !empty($rdc->sop_ids) ? json_decode($rdc->sop_ids, true) : [];
					echo form_dropdown("sop_ids[]", $array, $selected_sops, "class='form-control' id='sop_ids' multiple data-plugin-selectTwo data-width='100%' data-placeholder='Select multiple SOPs'");
				?>
			</div>
		</div>

		<!-- Due Time -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?= translate('Due Time') ?> <span class="required">*</span></label>
			<div class="col-md-8">
				<input type="datetime-local" name="due_time" class="form-control" value="<?= $rdc->due_time ?>">
			</div>
		</div>

		<!-- Verifier Due Time -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?= translate('Verifier Due Time') ?> <span class="required">*</span></label>
			<div class="col-md-8">
				<input type="datetime-local" name="verifier_due_time" class="form-control" value="<?= $rdc->verifier_due_time ?>">
			</div>
		</div>

		<!-- Verifier Required -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?= translate('Verifier Required') ?></label>
			<div class="col-md-8">
				<label class="checkbox-inline">
					<input type="checkbox" name="verifier_required" value="1" <?= (isset($rdc->verifier_required) && $rdc->verifier_required == 1) ? 'checked' : (!isset($rdc->verifier_required) ? 'checked' : '') ?>> <?= translate('Yes, verifier is required for this task') ?>
				</label>
			</div>
		</div>

		<!-- Frequency -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?= translate('frequency') ?> <span class="required">*</span></label>
			<div class="col-md-8">
				<select name="frequency" class="form-control" id="frequencySelector" required>
					<option value="">Select</option>
					<option value="daily" <?= $rdc->frequency == 'daily' ? 'selected' : '' ?>>Daily</option>
					<option value="weekly" <?= $rdc->frequency == 'weekly' ? 'selected' : '' ?>>Weekly</option>
					<option value="bimonthly" <?= $rdc->frequency == 'bimonthly' ? 'selected' : '' ?>>Twice a Month (15 Days)</option>
					<option value="monthly" <?= $rdc->frequency == 'monthly' ? 'selected' : '' ?>>Monthly</option>
					<option value="yearly" <?= $rdc->frequency == 'yearly' ? 'selected' : '' ?>>Yearly</option>
				</select>
			</div>
		</div>

		<!-- Frequency Details -->
		<div id="frequencyDetails">
			<?php if ($rdc->frequency == 'daily'): ?>
				<div class="form-group">
					<label class="col-md-3 control-label">Time <span class="required">*</span></label>
					<div class="col-md-8">
						<input type="time" name="daily_time" class="form-control" value="<?= $rdc->daily_time ?>" required />
					</div>
				</div>
			<?php elseif ($rdc->frequency == 'weekly'): ?>
				<div class="form-group">
					<label class="col-md-3 control-label">Weekday <span class="required">*</span></label>
					<div class="col-md-4">
						<select name="weekly_day" class="form-control" required>
							<?php
								$days = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
								foreach ($days as $day) {
									echo "<option value='$day' ".($rdc->weekly_day == $day ? 'selected' : '').">".ucfirst($day)."</option>";
								}
							?>
						</select>
					</div>
					<label class="col-md-1 control-label">Time</label>
					<div class="col-md-3">
						<input type="time" name="weekly_time" class="form-control" value="<?= $rdc->weekly_time ?>" required />
					</div>
				</div>
			<?php elseif ($rdc->frequency == 'bimonthly'): ?>
				<div class="form-group">
					<label class="col-md-3 control-label">Days of Month <span class="required">*</span></label>
					<div class="col-md-4">
						<select name="bimonthly_day1" class="form-control" required>
							<option value="">First Day</option>
							<?php for ($i=1; $i<=15; $i++): ?>
								<option value="<?= $i ?>" <?= $rdc->bimonthly_day1 == $i ? 'selected' : '' ?>><?= $i ?></option>
							<?php endfor; ?>
						</select>
					</div>
					<div class="col-md-4">
						<select name="bimonthly_day2" class="form-control" required>
							<option value="">Second Day</option>
							<?php for ($i=16; $i<=31; $i++): ?>
								<option value="<?= $i ?>" <?= $rdc->bimonthly_day2 == $i ? 'selected' : '' ?>><?= $i ?></option>
							<?php endfor; ?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Time</label>
					<div class="col-md-8">
						<input type="time" name="bimonthly_time" class="form-control" value="<?= $rdc->bimonthly_time ?>" required />
					</div>
				</div>
			<?php elseif ($rdc->frequency == 'monthly'): ?>
				<div class="form-group">
					<label class="col-md-3 control-label">Day of Month <span class="required">*</span></label>
					<div class="col-md-4">
						<select name="monthly_day" class="form-control" required>
							<?php for ($i=1; $i<=31; $i++): ?>
								<option value="<?= $i ?>" <?= $rdc->monthly_day == $i ? 'selected' : '' ?>><?= $i ?></option>
							<?php endfor; ?>
						</select>
					</div>
					<label class="col-md-1 control-label">Time</label>
					<div class="col-md-3">
						<input type="time" name="monthly_time" class="form-control" value="<?= $rdc->monthly_time ?>" required />
					</div>
				</div>
			<?php elseif ($rdc->frequency == 'yearly'): ?>
				<div class="form-group">
					<label class="col-md-3 control-label">Month <span class="required">*</span></label>
					<div class="col-md-4">
						<select name="yearly_month" class="form-control" required>
							<option value="">Select Month</option>
							<?php 
								$months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
								for ($i=1; $i<=12; $i++): 
							?>
								<option value="<?= $i ?>" <?= $rdc->yearly_month == $i ? 'selected' : '' ?>><?= $months[$i-1] ?></option>
							<?php endfor; ?>
						</select>
					</div>
					<label class="col-md-1 control-label">Day</label>
					<div class="col-md-3">
						<select name="yearly_day" class="form-control" required>
							<option value="">Select Day</option>
							<?php for ($i=1; $i<=31; $i++): ?>
								<option value="<?= $i ?>" <?= $rdc->yearly_day == $i ? 'selected' : '' ?>><?= $i ?></option>
							<?php endfor; ?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Time</label>
					<div class="col-md-8">
						<input type="time" name="yearly_time" class="form-control" value="<?= $rdc->yearly_time ?>" required />
					</div>
				</div>
			<?php endif; ?>
		</div>

		<!-- Proof Required -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?= translate('is proof required') ?> <span class="required">*</span></label>
			<div class="col-md-8">
				<select name="is_proof_required" class="form-control" required>
					<option value="">Select</option>
					<option value="1" <?= $rdc->is_proof_required ? 'selected' : '' ?>>Yes</option>
					<option value="0" <?= !$rdc->is_proof_required ? 'selected' : '' ?>>No</option>
				</select>
			</div>
		</div>

		<!-- Pre-reminder -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?= translate('Pre-reminder') ?> <span class="required">*</span></label>
			<div class="col-md-8">
				<label class="radio-inline">
					<input type="radio" name="pre_reminder_enabled" value="1" <?= $rdc->pre_reminder_enabled ? 'checked' : '' ?>> <?= translate('Yes') ?>
				</label>
				<label class="radio-inline">
					<input type="radio" name="pre_reminder_enabled" value="0" <?= !$rdc->pre_reminder_enabled ? 'checked' : '' ?>> <?= translate('No') ?>
				</label>
			</div>
		</div>

		<!-- Escalation -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?= translate('Escalation') ?> <span class="required">*</span></label>
			<div class="col-md-8">
				<label class="radio-inline">
					<input type="radio" name="escalation_enabled" value="1" <?= $rdc->escalation_enabled ? 'checked' : '' ?>> <?= translate('enabled') ?>
				</label>
				<label class="radio-inline">
					<input type="radio" name="escalation_enabled" value="0" <?= !$rdc->escalation_enabled ? 'checked' : '' ?>> <?= translate('disabled') ?>
				</label>
			</div>
		</div>
		<br>
		<footer class="panel-footer">
			<div class="row">
				<div class="col-md-offset-3 col-md-2">
					<button type="submit" class="btn btn-default btn-block">
						<i class="fas fa-save"></i> <?= translate('update') ?>
					</button>
				</div>
			</div>
		</footer>
		<?php echo form_close(); ?>
	</div>
</section>


<script>
const rdcData = <?= json_encode($rdc) ?>;

$(document).ready(function () {
	// Handle assignment type change
	$('#assigned_user').on('change', function () {
		const value = $(this).val();
		if (value === 'multi_random') {
			$('#user_pool_section').show();
			$('#user_pool').prop('required', true);
		} else {
			$('#user_pool_section').hide();
			$('#user_pool').prop('required', false);
		}
	});
	
	$('#frequencySelector').on('change', function () {
		const value = $(this).val();
		let html = '';

		if (value === 'daily') {
			html = `
				<div class="form-group">
					<label class="col-md-3 control-label">Time <span class="required">*</span></label>
					<div class="col-md-8">
						<input type="time" name="daily_time" class="form-control" value="${rdcData.daily_time || ''}" required />
					</div>
				</div>`;
		} else if (value === 'weekly') {
			const days = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
			html = `
				<div class="form-group">
					<label class="col-md-3 control-label">Weekday <span class="required">*</span></label>
					<div class="col-md-4">
						<select name="weekly_day" class="form-control" required>
							<option value="">Select</option>
							${days.map(day => `<option value="${day}" ${rdcData.weekly_day === day ? 'selected' : ''}>${day.charAt(0).toUpperCase() + day.slice(1)}</option>`).join('')}
						</select>
					</div>
					<label class="col-md-1 control-label">Time</label>
					<div class="col-md-3">
						<input type="time" name="weekly_time" class="form-control" value="${rdcData.weekly_time || ''}" required />
					</div>
				</div>`;
		} else if (value === 'bimonthly') {
			html = `
				<div class="form-group">
					<label class="col-md-3 control-label">Days of Month <span class="required">*</span></label>
					<div class="col-md-4">
						<select name="bimonthly_day1" class="form-control" required>
							<option value="">First Day</option>
							${[...Array(15)].map((_, i) => `<option value="${i+1}" ${rdcData.bimonthly_day1 == (i+1) ? 'selected' : ''}>${i+1}</option>`).join('')}
						</select>
					</div>
					<div class="col-md-4">
						<select name="bimonthly_day2" class="form-control" required>
							<option value="">Second Day</option>
							${[...Array(16)].map((_, i) => `<option value="${i+16}" ${rdcData.bimonthly_day2 == (i+16) ? 'selected' : ''}>${i+16}</option>`).join('')}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Time</label>
					<div class="col-md-8">
						<input type="time" name="bimonthly_time" class="form-control" value="${rdcData.bimonthly_time || ''}" required />
					</div>
				</div>`;
		} else if (value === 'monthly') {
			html = `
				<div class="form-group">
					<label class="col-md-3 control-label">Day of Month <span class="required">*</span></label>
					<div class="col-md-4">
						<select name="monthly_day" class="form-control" required>
							<option value="">Select</option>
							${[...Array(31)].map((_, i) => `<option value="${i+1}" ${rdcData.monthly_day == (i+1) ? 'selected' : ''}>${i+1}</option>`).join('')}
						</select>
					</div>
					<label class="col-md-1 control-label">Time</label>
					<div class="col-md-3">
						<input type="time" name="monthly_time" class="form-control" value="${rdcData.monthly_time || ''}" required />
					</div>
				</div>`;
		} else if (value === 'yearly') {
			const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
			html = `
				<div class="form-group">
					<label class="col-md-3 control-label">Month <span class="required">*</span></label>
					<div class="col-md-4">
						<select name="yearly_month" class="form-control" required>
							<option value="">Select Month</option>
							${months.map((month, i) => `<option value="${i+1}" ${rdcData.yearly_month == (i+1) ? 'selected' : ''}>${month}</option>`).join('')}
						</select>
					</div>
					<label class="col-md-1 control-label">Day</label>
					<div class="col-md-3">
						<select name="yearly_day" class="form-control" required>
							<option value="">Select Day</option>
							${[...Array(31)].map((_, i) => `<option value="${i+1}" ${rdcData.yearly_day == (i+1) ? 'selected' : ''}>${i+1}</option>`).join('')}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Time</label>
					<div class="col-md-8">
						<input type="time" name="yearly_time" class="form-control" value="${rdcData.yearly_time || ''}" required />
					</div>
				</div>`;
		}

		$('#frequencyDetails').html(html);
	});
});
</script>

<script>
document.querySelectorAll('input[name="pre_reminder_enabled"]').forEach(function(el) {
	el.addEventListener('change', function() {
		if (this.value === '1') {
			document.getElementById('pre-reminder-timing').style.display = 'flex';
		} else {
			document.getElementById('pre-reminder-timing').style.display = 'none';
		}
	});
});
</script>
