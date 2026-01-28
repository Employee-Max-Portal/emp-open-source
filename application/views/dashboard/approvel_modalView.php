<?php 
$row = $this->dashboard_model->getWorkSummaryById(array('dws.id' => $work_id), true); 
$isReadOnly = loggedin_role_id() == 10;
?>
<form id="approval-form" method="post">
<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
<input type="hidden" name="id" value="<?= $row['id']; ?>">
	<header class="panel-heading">
		<h4 class="panel-title"><i class="fas fa-bars"></i> <?= translate('work_summary_details'); ?></h4>
	</header>
	<div class="panel-body">
		<div class="table-responsive">
			<table class="table borderless mb-none">
				<tbody>
					<tr>
						<th width="120"><?=translate('reviewed_by')?> :</th>
						<td>
							<?php
								if (!empty($row['approved_by'])) {
									echo get_type_name_by_id('staff', $row['approved_by']);
								} else {
									echo translate('unreviewed');
								}
							?>
						</td>
					</tr>
					<tr>
						<th width="150"><?= translate('employee'); ?> :</th>
						<td><?= html_escape($row['staff_id'] . ' - ' .$row['name']); ?></td>
					</tr>
					<tr>
						<th><?= translate('department'); ?> :</th>
						<td><?= html_escape($row['department']); ?></td>
					</tr>
					<tr>
						<th><?= translate('summary_date'); ?> :</th>
						<td><?= _d($row['summary_date']); ?></td>
					</tr>
					<!-- Assigned Tasks -->
					<tr>
						<th><?= translate('assigned_tasks'); ?> :</th>
						<td>
							<ul class="pl-3 mb-0">
								<?php 
									$assigned = json_decode($row['assigned_tasks'], true);
									foreach ((array) $assigned as $index => $task): 
										$title = html_escape($task['title']);
										$link = trim($task['link'] ?? '');
										$planner = isset($task['planner']) ? intval($task['planner']) : 0;
										$isURL = (strpos($link, 'http://') === 0 || strpos($link, 'https://') === 0);
								?>
									<li>
										<?= $title ?>
										<?php if (!empty($link)): ?>
											<?php if ($isURL): ?>
												<a href="<?= html_escape($link) ?>" target="_blank" title="Open link">
													<i class="fas fa-link text-primary ml-1"></i>
												</a>
											<?php else: ?>
											<strong class="text-muted small"> - <?= $link ?></strong>
											<?php endif; ?>
										<?php endif; ?>

										<?php if ($planner === 1): ?>
											<span class="text-muted small">– In Planner</span>
										<?php else: ?>
											<span class="text-muted small">– Not in Planner</span>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
						</td>
					</tr>

					<tr>
						<th><?= translate('completed_tasks'); ?> :</th>
						<td>
							<ul class="pl-3 mb-0">
								<?php 
									$completed = json_decode($row['completed_tasks'], true);
									$totalTime = 0;
									foreach ((array) $completed as $index => $task): 
										$title    = html_escape($task['title']);
										$link     = trim($task['link'] ?? '');
										$isURL    = (strpos($link, 'http://') === 0 || strpos($link, 'https://') === 0);
										$time     = isset($task['time']) ? floatval($task['time']) : 0;
										$decision = isset($task['approval']) ? $task['approval'] : '';
										$totalTime += $time;
										
										// Convert float hours to hour + minute format
										$hours = floor($totalTime); // Get the whole number part as hours
										$minutes = round(($totalTime - $hours) * 60); // Convert the decimal part to minutes

										$timeFormatted = "{$hours} hour" . ($hours != 1 ? "s" : "") . " {$minutes} min" . ($minutes != 1 ? "s" : "");

								?>
									<li class="mb-2">
										<strong><?= $title ?></strong>
										<?php if (!empty($link)): ?>
											<?php if ($isURL): ?>
												<a href="<?= html_escape($link) ?>" target="_blank" title="Open link">
													<i class="fas fa-check-circle text-success ml-1"></i>
												</a>
											<?php else: ?>
												<strong class="text-muted small"> - <?= $link ?></strong>
												
													<button type="button" class="btn btn-info btn-xs ml-1" onclick="viewTask('<?= $link ?>')" title="View Task">
														<i class="fas fa-eye"></i>
													</button>
													
													<!-- <?php
													// Check if tracker task is late
													if (preg_match('/^[A-Z]+-\d+$/', $link) || preg_match('/([A-Z]+-\d+)/', $link, $matches)) {
														$unique_id = preg_match('/^[A-Z]+-\d+$/', $link) ? $link : $matches[1];
														$tracker_task = $this->db->select('is_late')->where('unique_id', $unique_id)->get('tracker_issues')->row();
														if ($tracker_task && $tracker_task->is_late == 1) {
															echo '<span class="badge badge-warning ml-1" title="Late Submission"><i class="fas fa-clock"></i> Late</span>';
															echo '<button type="button" class="btn btn-warning btn-xs ml-1" onclick="createLateTodo(\'' . $unique_id . '\')" title="Create Todo for Late Submission"><i class="fas fa-exclamation-triangle"></i> Todo</button>';
														}
													}
													?> -->
								
											<?php endif; ?>
										<?php endif; ?>

										<span class="text-muted small">(<?= $time ?> hours)</span>

										<!-- Accept / Decline buttons with task title -->
										<div class="mt-1">
											<label class="mr-1 text-xs">Decision:</label>

											<!-- Pass task title -->
											<input type="hidden" name="approval[<?= $index ?>][title]" value="<?= $title ?>">

											<div class="radio-custom radio-inline">
												<input type="radio" id="accept_<?= $index ?>" name="approval[<?= $index ?>][decision]" value="accepted" <?= ($decision === 'accepted') ? 'checked' : '' ?> <?= $isReadOnly ? 'disabled' : '' ?>>
												<label for="accept_<?= $index ?>" class="text">Accept</label>
											</div>
											<div class="radio-custom radio-inline">
												<input type="radio" id="decline_<?= $index ?>" name="approval[<?= $index ?>][decision]" value="declined" <?= ($decision === 'declined') ? 'checked' : '' ?> <?= $isReadOnly ? 'disabled' : '' ?>>
												<label for="decline_<?= $index ?>" class="text">Decline</label>
											</div>
										</div>
									</li>
								<?php endforeach; ?>
							</ul>
						</td>
					</tr>

					<tr>
						<th><?= translate('total_completed_task_spent_time') ?> :</th>
						<td><strong><?= $timeFormatted ?></strong></td>
					</tr>
					<tr>
						<th><?= translate('completion_ratio'); ?> :</th>
						<td><?= intval($row['completion_ratio']) ?>%</td>
					</tr>
					<tr>
						<th><?= translate('blockers'); ?> :</th>
						<td><?= !empty($row['blockers']) ? html_escape($row['blockers']) : 'N/A'; ?></td>
					</tr>
					<tr>
						<th><?= translate('next_steps'); ?> :</th>
						<td><?= !empty($row['next_steps']) ? html_escape($row['next_steps']) : 'N/A'; ?></td>
					</tr>
					
					<tr>
						<th><?= translate('overall_rating'); ?> :</th>
						<td>
							<div class="radio-custom radio-inline">
								<input type="radio" id="rating_excellent" name="overall_rating" value="Excellent" <?= (isset($row['rating']) && $row['rating'] == 'Excellent') ? 'checked' : '' ?> <?= $isReadOnly ? 'disabled' : '' ?>>
								<label for="rating_excellent">Excellent</label>
							</div>
							<div class="radio-custom radio-inline">
								<input type="radio" id="rating_good" name="overall_rating" value="Good" <?= (isset($row['rating']) && $row['rating'] == 'Good') ? 'checked' : '' ?> <?= $isReadOnly ? 'disabled' : '' ?>>
								<label for="rating_good">Good</label>
							</div>
							<div class="radio-custom radio-inline">
								<input type="radio" id="rating_satisfactory" name="overall_rating" value="Average" <?= (isset($row['rating']) && $row['rating'] == 'Average') ? 'checked' : '' ?> <?= $isReadOnly ? 'disabled' : '' ?>>
								<label for="rating_satisfactory">Average</label>
							</div>
							<div class="radio-custom radio-inline">
								<input type="radio" id="rating_poor" name="overall_rating" value="Poor" <?= (isset($row['rating']) && $row['rating'] == 'Poor') ? 'checked' : '' ?> <?= $isReadOnly ? 'disabled' : '' ?>>
								<label for="rating_poor">Poor</label>
							</div>
						</td>
					</tr>

					<input type="hidden" name="status" value="2">


					<tr>
						<th><?= translate('comments'); ?> :</th>
						<td>
							<textarea name="comments" class="form-control" rows="3" <?= $isReadOnly ? 'readonly' : '' ?>><?= html_escape($row['comments'] ?? '') ?></textarea>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<footer class="panel-footer">
		<div class="row">
			<div class="col-md-12 text-right">
			<?php if (get_permission('work_summary', 'is_edit') && !$isReadOnly): ?>
				<button type="submit" name="update" value="1" class="btn btn-default mr-xs" id="submit-btn">
					<i class="fas fa-check-circle"></i> <span class="btn-text"><?= translate('submit'); ?></span>
					<i class="fas fa-spinner fa-spin" style="display: none;"></i>
				</button>
				<?php endif; ?>
				<button type="button" class="btn btn-default modal-dismiss">
					<?= translate('close'); ?>
				</button>
			</div>
		</div>
	</footer>
</form>

<!-- Task Details Modal -->
<div class="modal fade" id="taskDetailsModal" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-xl custom-task-modal" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Task Details</h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<!-- Task details will be loaded here -->
			</div>
		</div>
	</div>
</div>


<style>
.modal-block {
    background: transparent;
    padding: 0;
    text-align: left;
    max-width: 80%;
    margin: 40px auto;
    position: relative;
}
  .custom-task-modal {
    width: 80% !important;
    max-width: 1200px;
    height: 90vh;
  }

  .custom-task-modal .modal-content {
    height: 100%;
    display: flex;
    flex-direction: column;
  }

  .custom-task-modal .modal-body {
    flex: 1;
    overflow-y: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
  }

  .custom-task-modal .modal-body::-webkit-scrollbar {
    width: 0px;
    background: transparent;
  }

  #commentsList {
    scrollbar-width: none;
    -ms-overflow-style: none;
  }

  #commentsList::-webkit-scrollbar {
    width: 0px;
    background: transparent;
  }

  /* Mention system styles */
  .mention-container {
    position: relative;
  }

  .mention-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    min-width: 200px;
  }

  #mentionDropdown {
    bottom: 100%;
    top: auto;
  }

  .mention-item {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
  }

  .mention-item:hover,
  .mention-item.selected {
    background: #f0f8ff;
  }

  .mention-item:last-child {
    border-bottom: none;
  }
  
  .mention-name {
    font-size: 14px;
    color: #333;
  }

  .mention-highlight {
    background: #e3f2fd;
    color: #1976d2;
    padding: 2px 4px;
    border-radius: 3px;
    font-weight: 500;
  }
</style>


<script>
$(document).ready(function() {
	$('#approval-form').on('submit', function(e) {
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
			url: base_url + 'dashboard/work_summary_ajax',
			type: 'POST',
			data: $form.serialize(),
			dataType: 'json',
			success: function(response) {
				if (response.status === 'success') {
					// Get the work summary ID, selected status and rating
					var summaryId = $('input[name="id"]').val();
					var selectedStatus = $('input[name="status"]').val();
					var selectedRating = $('input[name="overall_rating"]:checked').val();
					
					// Update status and rating instantly
					if (typeof parent.updateWorkSummaryStatus === 'function' && selectedStatus) {
						parent.updateWorkSummaryStatus(summaryId, selectedStatus, selectedRating);
					}
					
					// Close modal
					parent.$.magnificPopup.close();
					
					// Show success message
					if (typeof parent.toastr !== 'undefined') {
						parent.toastr.success('Work summary updated successfully');
					}
				} else {
					if (typeof toastr !== 'undefined') {
						toastr.error(response.message || 'Error updating work summary');
					} else {
						alert(response.message || 'Error updating work summary');
					}
				}
			},
			error: function() {
				toastr.error('Network error occurred');
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

// View task details in modal (global function)
function viewTask(id) {
	$.ajax({
		url: base_url + 'dashboard/viewTracker_Issue',
		type: 'POST',
		data: {'id': id},
		dataType: "html",
		success: function (data) {
			$('#taskDetailsModal .modal-body').html(data);
			$('#taskDetailsModal').modal('show');
		}
	});
}

// Create todo for late tracker task
function createLateTodo(uniqueId) {
	$.ajax({
		url: '<?= base_url('dashboard/add_late_remark'); ?>',
		type: 'POST',
		data: {
			'unique_id': uniqueId,
			'<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
		},
		dataType: 'json',
		success: function(response) {
			if (response.status === 'success') {
				if (typeof toastr !== 'undefined') {
					toastr.success('Todo created successfully for late submission');
				} else {
					alert('Todo created successfully for late submission');
				}
			} else {
				if (typeof toastr !== 'undefined') {
					toastr.error(response.message || 'Failed to create todo');
				} else {
					alert(response.message || 'Failed to create todo');
				}
			}
		},
		error: function() {
			if (typeof toastr !== 'undefined') {
				toastr.error('Network error occurred');
			} else {
				alert('Network error occurred');
			}
		}
	});
}
</script>
