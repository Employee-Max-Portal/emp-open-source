<section class="panel">
	<header class="panel-heading">
		<h4 class="panel-title"><i class="fas fa-clipboard-list"></i> <?=translate('create') . " " . translate('task')?></h4>
	</header>
	<div class="panel-body">
		<?php echo form_open(current_url(), array('method' => 'post', 'class' => 'form-bordered form-horizontal', 'autocomplete' => 'off')); ?>

		<!-- RDC Title -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('title')?> <span class="required">*</span></label>
			<div class="col-md-8">
				<input type="text" class="form-control" name="title" required />
				<span class="error"></span>
			</div>
		</div>

		<!-- Description -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('description')?> <span class="required">*</span></label>
			<div class="col-md-8">
				<textarea name="description" class="summernote form-control" id="description" rows="2" required></textarea>
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
						'',
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
						'',
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
					echo form_dropdown("coordinator", $coordinatorArray, array(), "class='form-control' required data-plugin-selectTwo data-width='100%'");
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
						'',
						"class='form-control' data-plugin-selectTwo data-width='100%'"
					);
				?>
				<span class="error"></span>
			</div>
		</div>
		
		<!-- Assigned to -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('assigned_to')?> <span class="required">*</span></label>
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
                       echo form_dropdown("assigned_user", $staffArray, array(), "class='form-control' id='assigned_user' required data-plugin-selectTwo data-placeholder=\"Select\" data-width='100%' ");
                    ?>

				<span class="error"><?= form_error('assigned_user') ?></span>
			</div>
		</div>
		
		<!-- User Pool Selection (hidden by default) -->
		<div class="form-group" id="user_pool_section" style="display: none;">
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

					echo form_dropdown("user_pool[]", $staffArray, array(), "class='form-control' id='user_pool' multiple data-plugin-selectTwo data-width='100%' data-placeholder='Select multiple users'");
				?>
				<span class="error"></span>
				<small class="help-block">Select multiple users from whom one will be randomly assigned for each task.</small>
			</div>
		</div>
		
		<!-- SOP Selection Dropdown -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('Select SOPs')?> <span class="required">*</span></label>
			<div class="col-md-8">
				<?php
					$array = $this->app_lib->getSelectList('sop');			
					echo form_dropdown("sop_ids[]", $array, array(), "class='form-control' id='sop_ids' multiple
					data-plugin-selectTwo data-width='100%' data-placeholder='Select multiple SOPs'");
				?>
				<span class="error"></span>
			</div>
		</div>

		<!-- Due Time -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('Due Time')?> <span class="required">*</span></label>
			<div class="col-md-8">
				<input type="datetime-local" name="due_time" class="form-control">
			</div>
		</div>
		
		<!-- Verifier Due Time -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('Verifier Due Time')?> <span class="required">*</span></label>
			<div class="col-md-8">
				<input type="datetime-local" name="verifier_due_time" class="form-control">
			</div>
		</div>

		<!-- Verifier Required -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('Verifier Required')?></label>
			<div class="col-md-8">
				<label class="checkbox-inline">
					<input type="checkbox" name="verifier_required" value="1" checked> <?=translate('Yes, verifier is required for this task')?>
				</label>
			</div>
		</div>

		<!-- Frequency -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('frequency')?> <span class="required">*</span></label>
			<div class="col-md-8">
				<select name="frequency" class="form-control" id="frequencySelector" required>
					<option value="">Select</option>
					<option value="daily">Daily</option>
					<option value="weekly">Weekly</option>
					<option value="bimonthly">Twice a Month (15 Days)</option>
					<option value="monthly">Monthly</option>
					<option value="yearly">Yearly</option>
				</select>
			</div>
		</div>

		<!-- Frequency Details Container -->
		<div class="form-group" id="frequencyDetails"></div>
	
		
		<!-- Proof -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('is proof required')?> <span class="required">*</span></label>
			<div class="col-md-8">
				<select name="is_proof_required" class="form-control" required>
					<option value="">Select</option>
					<option value="1">Yes</option>
					<option value="0">No</option>
				</select>
			</div>
		</div>	
		
		<!-- Pre-reminder (Y/N + Timing) -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('Pre-reminder')?> <span class="required">*</span></label>
			<div class="col-md-8">
				<!-- Yes/No radio buttons -->
				<div class="radio-inline">
					<label>
						<input type="radio" name="pre_reminder_enabled" value="1" class="pre-reminder-toggle"> <?=translate('Yes')?>
					</label>
				</div>
				<div class="radio-inline">
					<label>
						<input type="radio" name="pre_reminder_enabled" value="0" class="pre-reminder-toggle"> <?=translate('No')?>
					</label>
				</div>
				
				<span class="error" id="pre-reminder-error" style="color:red; display:none;"></span>
			</div>
		</div>


		<!-- Escalation Required -->
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('Escalation')?> <span class="required">*</span></label>
			<div class="col-md-8">
				<div class="radio-inline">
					<label>
						<input type="radio" name="escalation" value="1"> <?=translate('enabled')?>
					</label>
				</div>
				<div class="radio-inline">
					<label>
						<input type="radio" name="escalation" value="0"> <?=translate('disabled')?>
					</label>
				</div>
				<span class="error" id="proof-required-error" style="color:red;display:none;"></span>
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
						<input type="time" name="daily_time" class="form-control" required />
					</div>
				</div>`;
		} else if (value === 'weekly') {
			html = `
				<div class="form-group">
					<label class="col-md-3 control-label">Weekday <span class="required">*</span></label>
					<div class="col-md-4">
						<select name="weekly_day" class="form-control" required>
							<option value="">Select</option>
							<option value="sunday">Sunday</option>
							<option value="monday">Monday</option>
							<option value="tuesday">Tuesday</option>
							<option value="wednesday">Wednesday</option>
							<option value="thursday">Thursday</option>
							<option value="friday">Friday</option>
							<option value="saturday">Saturday</option>
						</select>
					</div>
					<label class="col-md-1 control-label">Time</label>
					<div class="col-md-3">
						<input type="time" name="weekly_time" class="form-control" required />
					</div>
				</div>`;
		} else if (value === 'bimonthly') {
			html = `
				<div class="form-group">
					<label class="col-md-3 control-label">Days of Month <span class="required">*</span></label>
					<div class="col-md-4">
						<select name="bimonthly_day1" class="form-control" required>
							<option value="">First Day</option>
							${[...Array(15)].map((_, i) => `<option value="${i+1}">${i+1}</option>`).join('')}
						</select>
					</div>
					<div class="col-md-4">
						<select name="bimonthly_day2" class="form-control" required>
							<option value="">Second Day</option>
							${[...Array(16)].map((_, i) => `<option value="${i+16}">${i+16}</option>`).join('')}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Time</label>
					<div class="col-md-8">
						<input type="time" name="bimonthly_time" class="form-control" required />
					</div>
				</div>`;
		} else if (value === 'monthly') {
			html = `
				<div class="form-group">
					<label class="col-md-3 control-label">Day of Month <span class="required">*</span></label>
					<div class="col-md-4">
						<select name="monthly_day" class="form-control" required>
							<option value="">Select</option>
							${[...Array(31)].map((_, i) => `<option value="${i+1}">${i+1}</option>`).join('')}
						</select>
					</div>
					<label class="col-md-1 control-label">Time</label>
					<div class="col-md-3">
						<input type="time" name="monthly_time" class="form-control" required />
					</div>
				</div>`;
		} else if (value === 'yearly') {
			html = `
				<div class="form-group">
					<label class="col-md-3 control-label">Month <span class="required">*</span></label>
					<div class="col-md-4">
						<select name="yearly_month" class="form-control" required>
							<option value="">Select Month</option>
							<option value="1">January</option>
							<option value="2">February</option>
							<option value="3">March</option>
							<option value="4">April</option>
							<option value="5">May</option>
							<option value="6">June</option>
							<option value="7">July</option>
							<option value="8">August</option>
							<option value="9">September</option>
							<option value="10">October</option>
							<option value="11">November</option>
							<option value="12">December</option>
						</select>
					</div>
					<label class="col-md-1 control-label">Day</label>
					<div class="col-md-3">
						<select name="yearly_day" class="form-control" required>
							<option value="">Select Day</option>
							${[...Array(31)].map((_, i) => `<option value="${i+1}">${i+1}</option>`).join('')}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Time</label>
					<div class="col-md-8">
						<input type="time" name="yearly_time" class="form-control" required />
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
