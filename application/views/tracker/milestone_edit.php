<section class="panel">
    <header class="panel-heading">
        <h4 class="panel-title"><i class="fas fa-edit"></i> <?= translate('edit_milestone') ?></h4>
    </header>
    <?php echo form_open('tracker/update_milestone', ['class' => 'form-horizontal milestone-form', 'id' => 'milestoneEditForm']); ?>
    <div class="panel-body">
        <input type="hidden" name="id" value="<?= $milestone->id ?>">
        <input type="hidden" name="redirect_url" value="<?= current_url() ?>">
        <div class="form-group">
            <label class="col-md-3 control-label"><?= translate('title') ?> <span class="required">*</span></label>
            <div class="col-md-8">
                <input type="text" class="form-control" name="title" value="<?= html_escape($milestone->title) ?>" required />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-3 control-label"><?= translate('description') ?></label>
            <div class="col-md-8">
                <textarea name="description" class="form-control" rows="3"><?= html_escape($milestone->description) ?></textarea>
            </div>
        </div>
       <div class="form-group">
			<label class="col-md-3 control-label"><?= translate('due_date') ?> <span class="text-danger">*</span></label>
			<div class="col-md-8">
				<input type="date" class="form-control" name="due_date" value="<?= html_escape($milestone->due_date) ?>" required />
			</div>
		</div>
		<div class="form-group">
			<label class="col-md-3 control-label"><?= translate('status') ?> <span class="text-danger">*</span></label>
			<div class="col-md-8">
				<select name="status" id="milestone_status" class="form-control" required>
					<option value="backlog" <?= ($milestone->status == 'backlog') ? 'selected' : '' ?>><?= translate('Backlog') ?></option>
					<option value="hold" <?= ($milestone->status == 'hold') ? 'selected' : '' ?>><?= translate('Hold') ?></option>
					<option value="todo" <?= ($milestone->status == 'todo') ? 'selected' : '' ?>><?= translate('to-do') ?></option>
					<option value="submitted" <?= ($milestone->status == 'submitted') ? 'selected' : '' ?>><?= translate('submitted') ?></option>
					<option value="in_progress" <?= ($milestone->status == 'in_progress') ? 'selected' : '' ?>><?= translate('in_progress') ?></option>
					<option value="in_review" <?= ($milestone->status == 'in_review') ? 'selected' : '' ?>><?= translate('in_review') ?></option>
					<option value="planning" <?= ($milestone->status == 'planning') ? 'selected' : '' ?>><?= translate('planning') ?></option>
					<option value="observation" <?= ($milestone->status == 'observation') ? 'selected' : '' ?>><?= translate('observation') ?></option>
					<option value="waiting" <?= ($milestone->status == 'waiting') ? 'selected' : '' ?>><?= translate('waiting') ?></option>
					<option value="done" <?= ($milestone->status == 'done') ? 'selected' : '' ?>><?= translate('done') ?></option>
					<option value="solved" <?= ($milestone->status == 'solved') ? 'selected' : '' ?>><?= translate('solved') ?></option>
					<option value="canceled" <?= ($milestone->status == 'canceled') ? 'selected' : '' ?>><?= translate('canceled') ?></option>
				</select>
			</div>
		</div>
		<div class="form-group" id="remarks_group" style="display: none;">
			<label class="col-md-3 control-label"><?= translate('remarks') ?> <span class="text-danger">*</span></label>
			<div class="col-md-8">
				<textarea name="remarks" id="milestone_remarks" class="form-control" rows="3" placeholder="Please provide remarks for this status change..."><?= html_escape($milestone->remarks ?? '') ?></textarea>
			</div>
		</div>
		<div class="form-group">
			<label class="col-md-3 control-label"><?=translate('assign_to')?> <span class="required">*</span></label>
			<div class="col-md-8">
				<?php
				$this->db->select('s.id, s.name');
				$this->db->from('staff AS s');
				$this->db->join('login_credential AS lc', 'lc.user_id = s.id');
				$this->db->where('lc.active', 1);   // only active users
				$this->db->where_not_in('lc.role', [1, 9]);   // exclude super admin, etc.
				$this->db->order_by('s.name', 'ASC');
				$query = $this->db->get();

				$staffArray = ['' => 'Select']; // <-- default first option
				foreach ($query->result() as $row) {
					$staffArray[$row->id] = $row->name;
				}
				echo form_dropdown("assigned_to", $staffArray, $milestone->assigned_to, "class='form-control' id='assigned_to' required data-plugin-selectTwo data-placeholder=\"Select\" data-width='100%' ");
				?>
							
				<span class="error"></span>
			</div>
		</div>
        <div class="form-group">
            <label class="col-md-3 control-label"><?= translate('type') ?> <span class="required">*</span></label>
            <div class="col-md-8">
                <select name="type" id="milestone_type" class="form-control" required>
                    <option value="">Select Type</option>
                    <option value="regular" <?= ($milestone->type == 'regular') ? 'selected' : '' ?>>Regular</option>
                    <option value="client" <?= ($milestone->type == 'client') ? 'selected' : '' ?>>Client</option>
                    <option value="in_house" <?= ($milestone->type == 'in_house') ? 'selected' : '' ?>>In House</option>
                </select>
            </div>
        </div>
        <div class="form-group" id="client_group" style="display: none;">
            <label class="col-md-3 control-label"><?= translate('client_name') ?> <span class="required">*</span></label>
            <div class="col-md-8">
                <select name="client_id" id="client_id" class="form-control" data-plugin-selectTwo data-placeholder="Select Client" data-width="100%">
                    <option value="">Select Client</option>
                    <?php
                    $this->db->select('id, client_name, company');
                    $this->db->from('contact_info');
                    $this->db->where('deleted', 0);
                    $this->db->order_by('client_name', 'ASC');
                    $contacts = $this->db->get()->result();
                    foreach ($contacts as $contact) {
                        $selected = (isset($milestone->client_id) && $milestone->client_id == $contact->id) ? 'selected' : '';
                        $display_name = $contact->client_name . (!empty($contact->company) ? ' (' . $contact->company . ')' : '');
                        echo '<option value="' . $contact->id . '" ' . $selected . '>' . html_escape($display_name) . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-3 control-label">Stage <span class="required">*</span></label>
            <div class="col-md-8">
                <select name="stage" class="form-control" required>
                    <option value="planning" <?= ($milestone->stage == 'planning') ? 'selected' : '' ?>>Planning</option>
                    <option value="execution" <?= ($milestone->stage == 'execution') ? 'selected' : '' ?>>Execution</option>
                </select>
            </div>
        </div>
		<div class="form-group">
			<label class="col-md-3 control-label"><?= translate('priority') ?> <span class="required">*</span></label>
			<div class="col-md-8">
				 <select name="priority" class="form-control" required>
                    <option value="">Select Priority</option>
                    <option value="Low" <?= ($milestone->priority == 'Low') ? 'selected' : '' ?>>Low</option>
                    <option value="Medium" <?= ($milestone->priority == 'Medium') ? 'selected' : '' ?>>Medium</option>
                    <option value="High" <?= ($milestone->priority == 'High') ? 'selected' : '' ?>>High</option>
                    <option value="Urgent" <?= ($milestone->priority == 'Urgent') ? 'selected' : '' ?>>Urgent</option>
                </select>
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
$(document).ready(function() {
    function toggleClientField() {
        var type = $('#milestone_type').val();
        if (type === 'client') {
            $('#client_group').show();
            $('#client_id').prop('required', true);
        } else {
            $('#client_group').hide();
            $('#client_id').prop('required', false);
        }
    }
    
    // Check on page load
    toggleClientField();
    
    // Check when type changes
    $('#milestone_type').change(function() {
        toggleClientField();
    });
    
    function toggleRemarks() {
        var status = $('#milestone_status').val();
        if (status === 'submitted' || status === 'done' || status === 'completed' || status === 'solved') {
            $('#remarks_group').show();
            $('#milestone_remarks').prop('required', true);
        } else {
            $('#remarks_group').hide();
            $('#milestone_remarks').prop('required', false);
        }
    }
    
    // Check on page load
    toggleRemarks();
    
    // Check when status changes
    $('#milestone_status').change(function() {
        toggleRemarks();
        
        var status = $(this).val();
        var milestoneId = $('input[name="id"]').val();
        
        // Check task completion for completion statuses
        if (status === 'done' || status === 'solved') {
            checkMilestoneTasks(milestoneId, status);
        }
    });
    
    function checkMilestoneTasks(milestoneId, status) {
        $.ajax({
            url: '<?= base_url('tracker/check_milestone_tasks') ?>',
            type: 'POST',
            data: {
                milestone_id: milestoneId,
                '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (!response.can_complete) {
                        var message = 'Cannot mark milestone as ' + status + '.\n\n';
                        message += 'There are ' + response.incomplete_count + ' incomplete tasks out of ' + response.total_count + ' total tasks.\n\n';
                        message += 'Incomplete tasks:\n';
                        
                        response.incomplete_tasks.forEach(function(task) {
                            message += '• ' + task.unique_id + ' - ' + task.task_title + ' (' + task.task_status + ')\n';
                        });
                        
                        message += '\nPlease complete all tasks before marking the milestone as ' + status + '.';
                        
                        alert(message);
                        
                        // Reset status to previous value
                        $('#milestone_status').val('<?= $milestone->status ?>').trigger('change');
                    }
                }
            },
            error: function() {
                alert('Error checking task completion status');
                $('#milestone_status').val('<?= $milestone->status ?>').trigger('change');
            }
        });
    }
    
    // Add validation before form submission
    $('#milestoneEditForm').on('submit', function(e) {
        e.preventDefault();
        var status = $('#milestone_status').val();
        if (status === 'completed' || status === 'done' || status === 'solved') {
            var confirmation = confirm('Are you sure you want to mark this milestone as ' + status + '? This will verify that all related tasks are completed.');
            if (!confirmation) {
                return false;
            }
        }
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $.magnificPopup.close();
                    refreshMilestonesTable();
                    showAlert('success', response.message);
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function() {
                showAlert('error', 'An error occurred while updating the milestone');
            }
        });
    });
});
</script>
