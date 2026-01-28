<style>
.leave-box {
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 10px 15px;
    margin-bottom: 10px;
    background-color: #fff;
    box-shadow: 0 3px 6px rgba(0,0,0,0.05);
}

.leave-bar-success {
    background-color: #28a745 !important;
}
.leave-bar-warning {
    background-color: #ffc107 !important;
    color: #000;
}
.leave-bar-danger {
    background-color: #dc3545 !important;
}

.dept-filter {
    margin-right: 8px;
    margin-bottom: 8px;
    padding: 8px 16px;
    border-radius: 25px;
    border: 2px solid #e3e6f0;
    background: linear-gradient(135deg, #f8f9fc 0%, #ffffff 100%);
    color: #5a5c69;
    font-weight: 500;
    font-size: 13px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
}

.dept-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-color: #4e73df;
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    color: white;
}

.dept-filter.active {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
    color: white !important;
    border-color: #4e73df !important;
    box-shadow: 0 4px 15px rgba(78, 115, 223, 0.4);
    transform: translateY(-1px);
}

.dept-filter.active::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.dept-filter.active:hover::before {
    left: 100%;
}

#department-filters {
    margin-bottom: 20px;
    padding: 15px;
    background: linear-gradient(135deg, #f8f9fc 0%, #ffffff 100%);
    border-radius: 10px;
    border: 1px solid #e3e6f0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

#department-filters::before {
    content: '🏢 Filter by Department:';
    display: block;
    font-weight: 600;
    color: #5a5c69;
    margin-bottom: 10px;
    font-size: 14px;
}

body .btn-primary {
    color: #777;
    text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
    background-color: #00a51f;
    border-color: #fff;
}
</style>
<div class="row">
	<div class="col-md-12">
	<?php if (in_array(loggedin_role_id(), [1, 2, 3, 5])) : ?>
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><?=translate('select_ground')?></h4>
			</header>
			<?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
				<div class="panel-body">
					<div class="row mb-sm">
					<?php if (in_array(loggedin_role_id(), [1, 2, 3, 5])) : ?>
					 <div class="col-md-offset-3 col-md-6 mb-sm">
							 <div class="form-group">
	                            <label class="control-label"><?php echo translate('date'); ?> <span class="required">*</span></label>
	                            <div class="input-group">
	                                <span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
	                                <input type="text" class="form-control daterange" name="daterange" value="<?=set_value('daterange', date("Y/m/d", strtotime('-6day')) . ' - ' . date("Y/m/d"))?>" required />
	                            </div>
	                        </div>
						</div>
					<?php endif; ?>
					</div>
				</div>
				<footer class="panel-footer">
					<div class="row">
						<div class="col-md-offset-10 col-md-2">
							<button type="submit" name="search" value="1" class="btn btn btn-default btn-block"><i class="fas fa-filter"></i> <?=translate('filter')?></button>
						</div>
					</div>
				</footer>
			<?php echo form_close(); ?>
		</section>
		<?php endif; ?>
		
		<section class="panel appear-animation" data-appear-animation="<?=$global_config['animations'] ?>" data-appear-animation-delay="100">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-users" aria-hidden="true"></i> <?=translate('work_list')?></h4>
			</header>
			<div class="panel-body">
				<!-- Department Filter Buttons -->
				<div class="row mb-3">
					<div class="col-md-12">
						<div class="btn-group" role="group" id="department-filters">
							<button type="button" class="btn btn-primary dept-filter active" data-department="all"><?=translate('all_departments')?></button>
							<!-- Department buttons will be loaded here -->
						</div>
					</div>
				</div>
				<!-- Loading Spinner -->
				<div id="loading-spinner" class="text-center" style="display: none; padding: 50px;">
					<div class="spinner-border text-primary" role="status">
						<span class="sr-only">Loading...</span>
					</div>
					<p class="mt-2">Loading work summaries...</p>
				</div>
				
				<table class="table table-bordered table-condensed table-hover mb-none table-export" id="work-summary-table">
	<thead>
		<tr>
			<th style="width: 10px;"><?= translate('sl') ?></th>
			<th style="width: 80px;"><?= translate('employee') ?></th>
			<th style="width: 40px;"><?= translate('date') ?></th>
			<th style="width: 400px;"><?= translate('assigned_tasks') ?></th>
			<th style="width: 400px;"><?= translate('completed_tasks') ?></th>
			<th class="text-center" style="width: 50px;"><?= translate('Time') ?></th>
			<th class="text-center" style="width: 20px;"><?= translate('ratio') ?></th>
			<th class="text-center" style="width: 20px;"><?= translate('overall_rating') ?></th>
			<th class="text-center" style="width: 60px;"><?= translate('status') ?></th>
			<?php if (get_permission('work_summary', 'is_view') ||get_permission('work_summary', 'is_edit') || get_permission('work_summary', 'is_delete')): ?>
			<th class="text-center" style="width: 70px;"><?= translate('action') ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tbody>
		<?php
		$count = 1;
		if (!empty($work_summaries)) {
			foreach ($work_summaries as $row):
				$assigned = json_decode($row['assigned_tasks'], true);
				$completed = json_decode($row['completed_tasks'], true);
				$totalTime = 0;
				$getStaff = $this->db->select('name, staff_id')->where('id', $row['user_id'])->get('staff')->row_array();
		?>
		<tr>
			<td><?= $count++ ?></td>
			<td><?= $getStaff['staff_id'] ?> - <?= $getStaff['name'] ?></td>
			<td><?= date("M d, Y", strtotime($row['summary_date'])); ?></td>
			

			<!-- Assigned Tasks -->
			<td>
				<ul class="pl-3 mb-0">
					<?php foreach ($assigned as $task): ?>
						<li>
							<?= html_escape($task['title']); ?>
							<?php
							// Check if there's a link and format it
							$link = trim($task['link'] ?? '');
							if (!empty($link)) {
								if (strpos($link, 'http://') === 0 || strpos($link, 'https://') === 0) {
									// It's a URL
									echo '<a href="' . html_escape($link) . '" target="_blank" title="Open link">';
									echo '<i class="fas fa-link text-primary ml-1"></i>';
									echo '</a>';
								} else {
									// It's plain text (not a link)
									
									echo '<strong class="text-muted small"> - ' . html_escape($link) . '</strong>';
								}
							}

							// Check planner status for assigned tasks
							$planner_status = isset($task['planner']) && $task['planner'] == 1 ? 'In Planner' : 'Not in Planner';
							?>
							<span class="text-muted small"> - <?= $planner_status ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</td>

			<!-- Completed Tasks with Time & Planner -->
			<td>
				<ul class="pl-3 mb-0">
					<?php foreach ((array) $completed as $task): ?>
						<?php
							$taskTitle = html_escape($task['title']);
							$linkRaw   = trim($task['link'] ?? '');
							$taskTime = isset($task['time']) ? floatval($task['time']) : 0;
							$totalTime += $taskTime;

							// Convert float hours to hour + minute format
							$hours = floor($totalTime); // Get the whole number part as hours
							$minutes = round(($totalTime - $hours) * 60); // Convert the decimal part to minutes

							$timeFormatted = "{$hours} hour" . ($hours != 1 ? "s" : "") . " {$minutes} min" . ($minutes != 1 ? "s" : "");


							// Check if it's a URL
							$isURL = (strpos($linkRaw, 'http://') === 0 || strpos($linkRaw, 'https://') === 0);
						?>
						<li>
							<?= $taskTitle ?>

							<?php if (!empty($linkRaw)): ?>
								<?php if ($isURL): ?>
									<a href="<?= html_escape($linkRaw) ?>" target="_blank" title="Open link">
										<i class="fas fa-check-circle text-success ml-1"></i>
									</a>
								<?php else: ?>
									<strong class="text-muted small"> - <?= $linkRaw ?></strong>
								<?php endif; ?>
							<?php endif; ?>

							<span class="text-muted small">(<?= $taskTime ?> hours)</span>
						</li>
					<?php endforeach; ?>
				</ul>
				<!-- ✅ Show Telegram Message if exists -->
				<?php if (!empty($row['telegram_message'])): ?>
					<div class="mt-3 p-3 border-left border-primary shadow-sm rounded bg-white">
						<div class="d-flex align-items-start">
							<div>
								<span class="font-weight-bold d-block mb-1"><i class="fab fa-telegram-plane text-primary fa-lg"></i> <strong>Remarks: </strong> <?= nl2br(html_escape($row['telegram_message'])) ?> <strong>by:</strong> <?= nl2br(html_escape($row['sender_name'])) ?></span>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</td>


			<!-- Total Time -->
			<td class="text-center"><?= $timeFormatted?></td>

			<!-- Completion Ratio -->
			<td class="text-center"><?= intval($row['completion_ratio']) ?>%</td>
			
			<td class="text-center">
				<?= is_null($row['rating']) ? 'Under Review' : $row['rating'] ?>
			</td>


			<!-- Status -->
			<td class="work-summary-status-<?= $row['id'] ?>">
				<?php
					if ($row['status'] == 1)
						$status = '<span class="label label-warning-custom text-xs">' . translate('in_review') . '</span>';
					elseif ($row['status'] == 2)
						$status = '<span class="label label-success-custom text-xs">' . translate('approved') . '</span>';
					elseif ($row['status'] == 3)
						$status = '<span class="label label-danger-custom text-xs">' . translate('warning') . '</span>';
					echo $status;
				?>
			</td>

			<!-- Action -->
			<?php if (get_permission('work_summary', 'is_view') || get_permission('work_summary', 'is_edit') || get_permission('work_summary', 'is_delete')): ?>
			<td>
				<?php if (get_permission('work_summary', 'is_view') || get_permission('work_summary', 'is_edit')): ?>
					<a href="javascript:void(0);" class="btn btn-circle icon btn-default" onclick="getApprovelWorkDetails('<?= $row['id'] ?>')">
						<i class="fas fa-bars"></i>
					</a>
					<?php if (loggedin_role_id() == 1): ?>
					<a href="javascript:void(0);" class="btn btn-circle icon btn-default" onclick="editSummaryDate('<?= $row['id'] ?>', '<?= $row['summary_date'] ?>')" title="Edit Date">
						<i class="fas fa-edit"></i>
					</a>
					<?php endif; ?>
				<?php endif; ?>
				<?php if (get_permission('work_summary', 'is_delete')): ?>
					<?= btn_delete('dashboard/work_summary_delete/' . $row['id']) ?>
				<?php endif; ?>
			</td>
			<?php endif; ?>
		</tr>
		<?php
			endforeach;
		} else {
		?>
			<tr>
				<td colspan="10" class="text-center">No work summaries available.</td>
			</tr>
		<?php } ?>
	</tbody>
</table>

				<!-- Pagination -->
				<div class="row mt-3" style="display:none;">
					<div class="col-md-6">
						<div id="pagination-info" class="text-muted"></div>
					</div>
					<div class="col-md-6">
						<nav aria-label="Work summary pagination">
							<ul class="pagination pagination-sm justify-content-end" id="pagination-controls"></ul>
						</nav>
					</div>
				</div>



			</div>
		</section>
	</div>
</div>

<!-- View Modal -->
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
	<section class="panel" id='quick_view'></section>
</div>

<!-- Edit Date Modal -->
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="editDateModal">
	<section class="panel">
		<header class="panel-heading">
			<h4 class="panel-title">Edit Summary Date</h4>
		</header>
		<div class="panel-body">
			<form id="editDateForm">
				<input type="hidden" id="edit_summary_id" name="summary_id">
				<div class="form-group">
					<label class="control-label">Summary Date <span class="required">*</span></label>
					<div class="input-group">
						<span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
						<input type="date" class="form-control" id="edit_summary_date" name="summary_date" required>
					</div>
				</div>
			</form>
		</div>
		<footer class="panel-footer">
			<div class="row">
				<div class="col-md-12 text-right">
					<button class="btn btn-default" onclick="$.magnificPopup.close();">Cancel</button>
					<button class="btn btn-primary" onclick="updateSummaryDate()">Update Date</button>
				</div>
			</div>
		</footer>
	</section>
</div>

<script>
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>

<script type="text/javascript">
	$(document).ready(function () {
		$('#daterange').daterangepicker({
			opens: 'left',
		    locale: {format: 'YYYY/MM/DD'}
		});

	    $('#addLeave').on('click', function(){
	        mfp_modal('#addLeaveModal');
	    });

        $('#class_id').on('change', function() {
            var class_id = $(this).val();
            var branch_id = ($( "#branch_id" ).length ? $('#branch_id').val() : "");
			$.ajax({
				url: base_url + 'ajax/getStudentByClass',
				type: 'POST',
				data: {
					branch_id: branch_id,
					class_id: class_id
				},
				success: function (data) {
					$('#applicant_id').html(data);
				}
			});
        });

		// Load departments and setup filtering
		loadDepartments();
		
		// Department filter click handler
		$(document).on('click', '.dept-filter', function() {
			$('.dept-filter').removeClass('active');
			$(this).addClass('active');
			
			var department = $(this).data('department');
			filterByDepartment(department, 1);
		});
		
		// Pagination click handler
		$(document).on('click', '.page-link', function(e) {
			e.preventDefault();
			var page = $(this).data('page');
			var department = $('.dept-filter.active').data('department');
			filterByDepartment(department, page);
		});
	});

	// Load departments for filter buttons
	function loadDepartments() {
		$.ajax({
			url: base_url + 'dashboard/get_departments',
			type: 'GET',
			dataType: 'json',
			success: function(data) {
				var buttons = '';
				$.each(data, function(index, dept) {
					buttons += '<button type="button" class="btn btn-default dept-filter" data-department="' + dept + '">' + dept + '</button>';
				});
				$('#department-filters').append(buttons);
			}
		});
	}

	// Filter work summaries by department with pagination
	function filterByDepartment(department, page = 1) {
		showLoading(true);
		
		$.ajax({
			url: base_url + 'dashboard/filter_work_summaries',
			type: 'POST',
			data: {
				department: department,
				daterange: $('input[name="daterange"]').val(),
				page: page,
				limit: 20
			},
			dataType: 'json',
			success: function(response) {
				showLoading(false);
				updateWorkSummaryTable(response.data);
				updatePagination(response.pagination);
			},
			error: function() {
				showLoading(false);
				alert('Error loading work summaries');
			}
		});
	}
	
	// Show/hide loading spinner
	function showLoading(show) {
		if (show) {
			$('#loading-spinner').show();
			$('#work-summary-table').hide();
		} else {
			$('#loading-spinner').hide();
			$('#work-summary-table').show();
		}
	}
	
	// Update pagination controls
	function updatePagination(pagination) {
		var paginationHtml = '';
		var totalPages = pagination.total_pages;
		var currentPage = pagination.current_page;
		
		// Previous button
		if (currentPage > 1) {
			paginationHtml += '<li class="page-item"><a class="page-link" href="#" data-page="' + (currentPage - 1) + '">Previous</a></li>';
		}
		
		// Page numbers
		var startPage = Math.max(1, currentPage - 2);
		var endPage = Math.min(totalPages, currentPage + 2);
		
		for (var i = startPage; i <= endPage; i++) {
			var activeClass = (i === currentPage) ? ' active' : '';
			paginationHtml += '<li class="page-item' + activeClass + '"><a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
		}
		
		// Next button
		if (currentPage < totalPages) {
			paginationHtml += '<li class="page-item"><a class="page-link" href="#" data-page="' + (currentPage + 1) + '">Next</a></li>';
		}
		
		$('#pagination-controls').html(paginationHtml);
		
		// Update info
		var start = ((currentPage - 1) * pagination.per_page) + 1;
		var end = Math.min(currentPage * pagination.per_page, pagination.total_records);
		$('#pagination-info').text('Showing ' + start + ' to ' + end + ' of ' + pagination.total_records + ' entries');
	}

	// Update the work summary table with filtered data
	function updateWorkSummaryTable(workSummaries) {
		var tbody = $('table tbody');
		tbody.empty();
		
		if (workSummaries.length === 0) {
			tbody.append('<tr><td colspan="10" class="text-center">No work summaries available for selected department.</td></tr>');
			return;
		}
		
		var count = 1;
		$.each(workSummaries, function(index, row) {
			var assigned = JSON.parse(row.assigned_tasks || '[]');
			var completed = JSON.parse(row.completed_tasks || '[]');
			var totalTime = 0;
			
			// Build assigned tasks HTML
			var assignedHtml = '<ul class="pl-3 mb-0">';
			$.each(assigned, function(i, task) {
				var plannerStatus = (task.planner == 1) ? 'In Planner' : 'Not in Planner';
				var linkHtml = '';
				if (task.link) {
					if (task.link.startsWith('http')) {
						linkHtml = '<a href="' + task.link + '" target="_blank"><i class="fas fa-link text-primary ml-1"></i></a>';
					} else {
						linkHtml = '<strong class="text-muted small"> - ' + task.link + '</strong>';
					}
				}
				assignedHtml += '<li>' + task.title + linkHtml + '<span class="text-muted small"> - ' + plannerStatus + '</span></li>';
			});
			assignedHtml += '</ul>';
			
			// Build completed tasks HTML
			var completedHtml = '<ul class="pl-3 mb-0">';
			$.each(completed, function(i, task) {
				totalTime += parseFloat(task.time || 0);
				var linkHtml = '';
				if (task.link) {
					if (task.link.startsWith('http')) {
						linkHtml = '<a href="' + task.link + '" target="_blank"><i class="fas fa-check-circle text-success ml-1"></i></a>';
					} else {
						linkHtml = '<strong class="text-muted small"> - ' + task.link + '</strong>';
					}
				}
				completedHtml += '<li>' + task.title + linkHtml + '<span class="text-muted small">(' + task.time + ' hours)</span></li>';
			});
			completedHtml += '</ul>';
			
			// Add telegram message if exists
			if (row.telegram_message) {
				completedHtml += '<div class="mt-3 p-3 border-left border-primary shadow-sm rounded bg-white">';
				completedHtml += '<div class="d-flex align-items-start"><div>';
				completedHtml += '<span class="font-weight-bold d-block mb-1"><i class="fab fa-telegram-plane text-primary fa-lg"></i> <strong>Remarks: </strong>' + row.telegram_message + ' <strong>by:</strong> ' + (row.sender_name || '') + '</span>';
				completedHtml += '</div></div></div>';
			}
			
			// Format time
			var hours = Math.floor(totalTime);
			var minutes = Math.round((totalTime - hours) * 60);
			var timeFormatted = hours + ' hour' + (hours != 1 ? 's' : '') + ' ' + minutes + ' min' + (minutes != 1 ? 's' : '');
			
			// Status
			var statusHtml = '';
			if (row.status == 1) {
				statusHtml = '<span class="label label-warning-custom text-xs">In Review</span>';
			} else if (row.status == 2) {
				statusHtml = '<span class="label label-success-custom text-xs">Approved</span>';
			} else if (row.status == 3) {
				statusHtml = '<span class="label label-danger-custom text-xs">Warning</span>';
			}
			
			// Action buttons
			var actionHtml = '';
			<?php if (get_permission('work_summary', 'is_view') || get_permission('work_summary', 'is_edit')): ?>
			actionHtml += '<a href="javascript:void(0);" class="btn btn-circle icon btn-default" onclick="getApprovelWorkDetails(' + row.id + ')"><i class="fas fa-bars"></i></a>';
			<?php endif; ?>
			<?php if (get_permission('work_summary', 'is_delete')): ?>
			actionHtml += '<a href="' + base_url + 'dashboard/work_summary_delete/' + row.id + '" class="btn btn-circle icon btn-default" onclick="return confirm(\'Are you sure?\')"><i class="fas fa-trash"></i></a>';
			<?php endif; ?>
			
			var tr = '<tr>' +
				'<td>' + count + '</td>' +
				'<td>' + (row.staff_id || '') + ' - ' + (row.name || '') + '</td>' +
				'<td>' + new Date(row.summary_date).toLocaleDateString('en-US', {month: 'short', day: '2-digit', year: 'numeric'}) + '</td>' +
				'<td>' + assignedHtml + '</td>' +
				'<td>' + completedHtml + '</td>' +
				'<td class="text-center">' + timeFormatted + '</td>' +
				'<td class="text-center">' + parseInt(row.completion_ratio || 0) + '%</td>' +
				'<td class="text-center">' + (row.rating || 'Under Review') + '</td>' +
				'<td class="work-summary-status-' + row.id + '">' + statusHtml + '</td>';
			
			<?php if (get_permission('work_summary', 'is_view') || get_permission('work_summary', 'is_edit') || get_permission('work_summary', 'is_delete')): ?>
			tr += '<td>' + actionHtml + '</td>';
			<?php endif; ?>
			
			tr += '</tr>';
			tbody.append(tr);
			count++;
		});
	}

	// get leave approvel details
	function getApprovelWorkDetails(id) {
    $.ajax({
        url: base_url + 'dashboard/getApprovelWorkDetails',
        type: 'POST',
        data: {'id': id},
        dataType: "html",
        success: function (data) {
            $('#quick_view').html(data);
            mfp_modal('#modal');
        }
    });
	}

	// Update work summary status and rating instantly
	function updateWorkSummaryStatus(summaryId, status, rating) {
		// Update status
		var statusHtml = '';
		if (status == 1) statusHtml = '<span class="label label-warning-custom text-xs"><?= translate('in_review') ?></span>';
		else if (status == 2) statusHtml = '<span class="label label-success-custom text-xs"><?= translate('approved') ?></span>';
		else if (status == 3) statusHtml = '<span class="label label-danger-custom text-xs"><?= translate('warning') ?></span>';
		
		// Update the status cell
		$('.work-summary-status-' + summaryId).html(statusHtml);
		
		// Update the rating cell
		if (rating) {
			$('tr').find('td').each(function() {
				if ($(this).hasClass('work-summary-status-' + summaryId)) {
					$(this).prev().html(rating);
				}
			});
		}
	}

	// Edit summary date
	function editSummaryDate(summaryId, currentDate) {
		$('#edit_summary_id').val(summaryId);
		$('#edit_summary_date').val(currentDate);
		mfp_modal('#editDateModal');
	}

	// Update summary date
	function updateSummaryDate() {
		var summaryId = $('#edit_summary_id').val();
		var newDate = $('#edit_summary_date').val();
		
		if (!newDate) {
			alert('Please select a date');
			return;
		}
		
		$.ajax({
			url: base_url + 'dashboard/update_summary_date',
			type: 'POST',
			data: {
				summary_id: summaryId,
				summary_date: newDate
			},
			dataType: 'json',
			success: function(response) {
				if (response.success) {
					$.magnificPopup.close();
					location.reload();
				} else {
					alert(response.message || 'Error updating date');
				}
			},
			error: function() {
				alert('Error updating summary date');
			}
		});
	}

</script>