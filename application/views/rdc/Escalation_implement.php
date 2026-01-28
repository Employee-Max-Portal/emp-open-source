<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0"><strong>Escalation Action Panel</strong></h5>
    </div>
</div>

<form id="escalation-form" method="post">
    <input type="hidden" name="task_id" value="<?= $escalation_id; ?>">
	<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />
    <div class="card mb-3">
        <div class="card-body">
            <!-- Executor Preview -->
            <div class="form-group mb-3">
                <label>Executor:</label>
                <input type="text" class="form-control" value="<?= $executor_name ?>" readonly>
            </div>

            <!-- Target Person -->
            <div class="form-group mb-3">
                <label for="target_person">Apply Action To:</label>
                <select name="target_person" class="form-control" id="target_person_select" required onchange="toggleVerifierDropdown()">
                    <option value="">Select Person</option>
                    <option value="executor">Executor (<?= $executor_name ?>)</option>
                    <?php if (!empty($verifier_list)): ?>
                        <option value="verifier">Verifier</option>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Verifier Dropdown (only if verifier selected) -->
            <div class="form-group mb-3" id="verifier_selector" style="display:none;">
                <label for="verifier_id">Select Verifier:</label>
                <select name="verifier_id" class="form-control">
                    <?php foreach ($verifier_list as $id => $name): ?>
                        <option value="<?= $id ?>"><?= $name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Action Type -->
            <div class="form-group mb-3">
                <label for="action_type">Select Action:</label>
                <select name="action_type" class="form-control" required>
                    <option value="">Select Action</option>
                    <option value="block_salary">🔒 Block Salary</option>
                    <option value="showcause">📄 Issue Showcause/Todo</option>
					<option value="cleared">✅ Cleared</option>
                </select>
            </div>

            <!-- Reason -->
            <div class="form-group mb-3">
                <label for="reason">Reason / Comment:</label>
                <textarea name="reason" class="form-control" rows="4" placeholder="Describe why this action is being taken..." required></textarea>
            </div>
			
		<footer class="panel-footer">
			<div class="row">
				<div class="col-md-12 text-right">
					<button type="submit" class="btn btn-danger" id="submit-btn">
						<span class="btn-text">Apply Action</span>
						<i class="fas fa-spinner fa-spin" style="display: none;"></i>
					</button>
				</div>
			</div>
		</footer>
		
        </div>
    </div>
</form>

<script>
function toggleVerifierDropdown() {
    const target = document.getElementById('target_person_select').value;
    const verifierSelector = document.getElementById('verifier_selector');
    const verifierDropdown = verifierSelector.querySelector('select');

    if (target === 'verifier') {
        verifierSelector.style.display = 'block';
        verifierDropdown.disabled = false;
    } else {
        verifierSelector.style.display = 'none';
        verifierDropdown.disabled = true;
        verifierDropdown.value = ""; // Clear any pre-selected value
    }
}

$(document).ready(function() {
	$('#escalation-form').on('submit', function(e) {
		e.preventDefault();
		
		var $form = $(this);
		var $submitBtn = $('#submit-btn');
		var $btnText = $submitBtn.find('.btn-text');
		var $spinner = $submitBtn.find('.fa-spinner');
		
		// Show loading state
		$submitBtn.prop('disabled', true);
		$btnText.hide();
		$spinner.show();
		
		$.ajax({
			url: base_url + 'rdc/escalation_action_submit',
			type: 'POST',
			data: $form.serialize(),
			dataType: 'json',
			success: function(response) {
				console.log('Success response:', response);
				if (response.status === 'success') {
					// Get the task ID
					var taskId = $('input[name="task_id"]').val();
					
					// Update escalation details in parent window
					if (typeof parent.updateEscalationDetails === 'function') {
						parent.updateEscalationDetails(taskId, response.escalation_data);
					}
					
					// Close modal
					parent.$.magnificPopup.close();
					
					// Show success message with SweetAlert
					if (typeof parent.swal !== 'undefined') {
						parent.swal({
							title: 'Success!',
							text: 'Escalation action applied successfully',
							type: 'success',
							timer: 2000,
							showConfirmButton: false
						});
					} else if (typeof parent.toastr !== 'undefined') {
						parent.toastr.success('Escalation action applied successfully');
					}
				} else {
					if (typeof toastr !== 'undefined') {
						toastr.error(response.message || 'Error applying escalation action');
					} else {
						alert(response.message || 'Error applying escalation action');
					}
				}
			},
			error: function(xhr, status, error) {
				console.log('AJAX Error:', xhr, status, error);
				console.log('Response Text:', xhr.responseText);
				if (typeof toastr !== 'undefined') {
					toastr.error('Network error: ' + error);
				} else {
					alert('Network error: ' + error);
				}
			},
			complete: function() {
				// Reset button state
				$submitBtn.prop('disabled', false);
				$btnText.show();
				$spinner.hide();
			}
		});
	});
});
</script>
