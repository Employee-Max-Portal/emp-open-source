<div class="row">
    <div class="col-md-12">
        <section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><?= translate('all_contacts') ?></h4>
					<div class="panel-btn">
						<?php if (get_permission('contact_info', 'is_add')): ?>
                            <button class="btn btn-default btn-circle" data-toggle="modal" data-target="#contactModal">
                                <i class="fas fa-user-plus"></i>Add New Contact
                            </button>
                        <?php endif; ?>
					</div>
			</header>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-condensed" cellspacing="0" width="100%" id="contactTable">
                        <thead>
                            <tr>
                                <th><?= translate('sl') ?></th>
                                <th><?= translate('client_name') ?></th>
                                <th><?= translate('email') ?></th>
                                <th><?= translate('phone') ?></th>
                                <th><?= translate('company') ?></th>
                                <th><?= translate('action') ?></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- Contact Modal -->
<div class="modal fade" id="contactModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?= translate('contact_info') ?></h4>
            </div>
            <?= form_open('contact_info/save', array('class' => 'form-horizontal', 'id' => 'contactForm')) ?>
            <div class="modal-body">
                <input type="hidden" name="contact_id" id="contact_id">
                
                <div class="form-group">
                    <label class="col-md-3 control-label"><?= translate('client_name') ?> <span class="required">*</span></label>
                    <div class="col-md-9">
                        <input type="text" class="form-control" name="client_name" id="client_name" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label"><?= translate('email') ?> <span class="required">*</span></label>
                    <div class="col-md-9">
                        <input type="email" class="form-control" name="email" id="email" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label"><?= translate('phone') ?> <span class="required">*</span></label>
                    <div class="col-md-9">
                        <input type="text" class="form-control" name="phone" id="phone" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label"><?= translate('company') ?></label>
                    <div class="col-md-9">
                        <input type="text" class="form-control" name="company" id="company">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label"><?= translate('address') ?></label>
                    <div class="col-md-9">
                        <textarea class="form-control" name="address" id="address" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-default"><?= translate('save') ?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= translate('cancel') ?></button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<!-- Milestones Modal -->
<div class="modal fade" id="milestonesModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document" style="width: 80%; max-width: none;">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header" style="background: #f8f9fa; border-radius: 12px 12px 0 0; border-bottom: 1px solid #dee2e6;">
                <button type="button" class="close" data-dismiss="modal" style="color: #000; opacity: 0.8; font-size: 24px;">&times;</button>
                <h4 class="modal-title" style="font-weight: 600; margin: 0;"><i class="fas fa-project-diagram mr-2"></i>Client Milestones & Tasks</h4>
            </div>
            <div class="modal-body" id="milestonesContent" style="padding: 25px; background: #f8f9fa; max-height: 70vh; overflow-y: auto;">
                <div class="text-center" style="padding: 40px;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px; color: #667eea;"></i>
                    <p style="margin-top: 15px; color: #6c757d;">Loading milestones...</p>
                </div>
            </div>
            <div class="modal-footer" style="background: white; border-top: 1px solid #e9ecef; border-radius: 0 0 12px 12px;">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Task Details Modal -->
<div class="modal fade" id="taskDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document" style="width: 80%; max-width: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?= translate('task_details') ?></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <!-- Task details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var table = $('#contactTable').DataTable({
        ajax: {
            url: '<?= base_url("contact_info/get_list") ?>',
            type: 'GET',
            dataSrc: ''
        },
        columns: [
            { 
                data: null,
                render: function(data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            { data: 'client_name' },
            { data: 'email' },
            { data: 'phone' },
            { data: 'company' },
            {
                data: null,
                render: function(data, type, row) {
                    var actions = '';
                    actions += '<a href="#" class="btn btn-info btn-circle icon view-milestones" data-id="' + row.id + '" data-name="' + row.client_name + '" title="View Milestones"><i class="fas fa-eye"></i></a> ';
                    <?php if (get_permission('contact_info', 'is_edit')): ?>
                    actions += '<a href="#" class="btn btn-default btn-circle icon edit-contact" data-id="' + row.id + '" title="<?= translate('edit') ?>"><i class="fas fa-pen-nib"></i></a> ';
                    <?php endif; ?>
                    <?php if (get_permission('contact_info', 'is_delete')): ?>
                    actions += '<button class="btn btn-danger icon btn-circle delete-contact" data-id="' + row.id + '" title="<?= translate('delete') ?>"><i class="fas fa-trash-alt"></i></button>';
                    <?php endif; ?>
                    return actions;
                }
            }
        ]
    });

    // AJAX form submission
    $('#contactForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#contactModal').modal('hide');
                    table.ajax.reload();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('An error occurred. Please try again.');
            }
        });
    });

    // View milestones
    $('#contactTable').on('click', '.view-milestones', function() {
        var clientId = $(this).data('id');
        var clientName = $(this).data('name');
        
        $('#milestonesModal .modal-title').html('<i class="fas fa-project-diagram mr-2"></i>Milestones & Tasks - ' + clientName);
        $('#milestonesContent').html('<div class="text-center" style="padding: 40px;"><i class="fas fa-spinner fa-spin" style="font-size: 24px; color: #667eea;"></i><p style="margin-top: 15px; color: #6c757d;">Loading milestones...</p></div>');
        $('#milestonesModal').modal('show');
        
        $.get('<?= base_url("contact_info/get_client_milestones/") ?>' + clientId, function(data) {
            try {
                if (typeof data === 'string') {
                    data = JSON.parse(data);
                }
            } catch (e) {
                data = [];
            }
            if (data && data.length > 0) {
                var html = '';
                
                // If multiple milestones, show accordion view
                if (data.length > 1) {
                    data.forEach(function(milestone, index) {
                        var progress = milestone.total_tasks > 0 ? Math.round((milestone.completed_tasks / milestone.total_tasks) * 100) : 0;
                        var milestoneId = 'milestone_' + milestone.id;
                        
                        html += `
                            <div class="milestone-accordion-card" style="border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 12px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.07);">
                                <div class="milestone-accordion-header" onclick="toggleClientMilestoneAccordion('${milestoneId}')" 
                                     style="display: flex; justify-content: space-between; align-items: center; padding: 15px; cursor: pointer; font-weight: 600; font-size: 14px; color: #000; background: #f7f7f8; user-select: none; transition: all 0.3s ease; border-radius: 8px 8px 0 0;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div class="milestone-icon" style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 14px;">
                                            <i class="fas fa-project-diagram"></i>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; color: #2d3748;">${milestone.title}</div>
                                            <div style="font-size: 12px; color: #6c757d; margin-top: 2px;"><?= translate('due') ?>: ${milestone.due_date || '<?= translate('not_set') ?>'}</div>
                                        </div>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="background: #e8e8e9; color: black; padding: 4px 8px; border-radius: 12px; font-size: 12px;">${progress}%</span>
                                        <i class="fas fa-chevron-down" style="transition: transform 0.3s ease;"></i>
                                    </div>
                                </div>
                                <div class="milestone-accordion-body" id="accordion-body-${milestoneId}" 
                                     style="max-height: 0; overflow: hidden; transition: max-height 0.4s ease-out, padding 0.4s ease-out; background-color: #ffffff;">
                                    <div style="padding: 15px;">
                                        <div class="milestone-details" style="margin-bottom: 15px; padding: 12px; background: #f8f9fa; border-radius: 6px;">
                                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                                <span><strong><?= translate('status') ?>:</strong> ${milestone.status ? milestone.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : '<?= translate('active') ?>'}</span>
                                                <span><strong><?= translate('priority') ?>:</strong> ${milestone.priority ? milestone.priority.charAt(0).toUpperCase() + milestone.priority.slice(1) : '<?= translate('medium') ?>'}</span>
                                            </div>
                                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                                <span><strong><?= translate('budget') ?>:</strong> ৳${milestone.budget ? Number(milestone.budget).toLocaleString() : '0'}</span>
                                                <span><strong><?= translate('tasks') ?>:</strong> ${milestone.completed_tasks}/${milestone.total_tasks}</span>
                                            </div>
                                            <div style="background: #e9ecef; border-radius: 6px; height: 8px; overflow: hidden; margin-top: 8px;">
                                                <div style="background: linear-gradient(90deg, #06d6a0 0%, #20c997 100%); height: 100%; width: ${progress}%; transition: width 0.3s ease; border-radius: 6px;"></div>
                                            </div>
                                        </div>
                                        <div id="milestone-tasks-${milestone.id}" class="milestone-tasks-container">
                                            <div class="text-center" style="padding: 20px; color: #6c757d;">
                                                <i class="fas fa-spinner fa-spin"></i> Loading tasks...
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    // Single milestone - show tasks directly
                    var milestone = data[0];
                    var progress = milestone.total_tasks > 0 ? Math.round((milestone.completed_tasks / milestone.total_tasks) * 100) : 0;
                    
                    html = `
                        <div class="single-milestone-view">
                            <div class="milestone-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                                <h5 style="margin: 0 0 10px 0; font-weight: 600;">${milestone.title}</h5>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <span style="background: rgba(255,255,255,0.2); padding: 4px 8px; border-radius: 12px; font-size: 12px; margin-right: 10px;"><?= translate('status') ?>: ${milestone.status ? milestone.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : '<?= translate('active') ?>'}</span>
                                        <span style="background: rgba(255,255,255,0.2); padding: 4px 8px; border-radius: 12px; font-size: 12px;"><?= translate('due') ?>: ${milestone.due_date || '<?= translate('not_set') ?>'}</span>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="font-size: 24px; font-weight: 700;">${progress}%</div>
                                        <div style="font-size: 12px; opacity: 0.9;">${milestone.completed_tasks}/${milestone.total_tasks} tasks</div>
                                    </div>
                                </div>
                                <div style="background: rgba(255,255,255,0.3); border-radius: 6px; height: 8px; overflow: hidden; margin-top: 15px;">
                                    <div style="background: #fff; height: 100%; width: ${progress}%; transition: width 0.3s ease; border-radius: 6px;"></div>
                                </div>
                            </div>
                            <div id="milestone-tasks-${milestone.id}" class="milestone-tasks-container">
                                <div class="text-center" style="padding: 40px; color: #6c757d;">
                                    <i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i>
                                    <p style="margin-top: 15px;">Loading tasks...</p>
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                $('#milestonesContent').html(html);
                
                // Load tasks for each milestone
                data.forEach(function(milestone) {
                    loadMilestoneTasks(milestone.id);
                });
            } else {
                $('#milestonesContent').html('<div class="text-center" style="padding: 40px; color: #6c757d;"><i class="fas fa-project-diagram" style="font-size: 48px; color: #dee2e6; margin-bottom: 15px;"></i><p>No milestones found for this client</p></div>');
            }
        }).fail(function() {
            $('#milestonesContent').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-2"></i>Error loading milestones</div>');
        });
    });

    // Edit contact
    $('#contactTable').on('click', '.edit-contact', function() {
        var id = $(this).data('id');
        $.get('<?= base_url("contact_info/get_single/") ?>' + id, function(data) {
            if (data && !data.error) {
                $('#contact_id').val(data.id);
                $('#client_name').val(data.client_name);
                $('#email').val(data.email);
                $('#phone').val(data.phone);
                $('#company').val(data.company);
                $('#address').val(data.address);
                $('#contactModal').modal('show');
            }
        });
    });

    // AJAX delete contact
    $('#contactTable').on('click', '.delete-contact', function() {
        var id = $(this).data('id');
        
        swal({
            title: '<?= translate('are_you_sure') ?>',
            text: '<?= translate('delete_confirmation') ?>',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<?= translate('yes_delete') ?>',
            cancelButtonText: '<?= translate('cancel') ?>'
        }).then((result) => {
            if (result) {
                window.location.href = '<?= base_url("contact_info/delete/") ?>' + id;
            }
        });
    });

    // Reset form on modal close
    $('#contactModal').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
        $('#contact_id').val('');
    });
});

// Toggle client milestone accordion sections
function toggleClientMilestoneAccordion(id) {
    // Close all milestone accordions first
    document.querySelectorAll('[id^="accordion-body-milestone_"]').forEach(body => {
        if (body.id !== 'accordion-body-' + id) {
            const headerId = body.id.replace('accordion-body-', '');
            const header = document.querySelector(`[onclick="toggleClientMilestoneAccordion('${headerId}')"]`);
            const chevron = header ? header.querySelector('.fas.fa-chevron-down') : null;
            
            body.style.maxHeight = '0px';
            if (header) header.style.background = '#f7f7f8';
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        }
    });
    
    // Toggle the clicked accordion
    const body = document.getElementById('accordion-body-' + id);
    const header = document.querySelector(`[onclick="toggleClientMilestoneAccordion('${id}')"]`);
    const chevron = header ? header.querySelector('.fas.fa-chevron-down') : null;
    
    if (body && header) {
        if (body.style.maxHeight && body.style.maxHeight !== '0px') {
            // Close
            body.style.maxHeight = '0px';
            header.style.background = '#f7f7f8';
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        } else {
            // Open
            header.style.background = '#e8e8e9';
            if (chevron) chevron.style.transform = 'rotate(180deg)';
            
            // Load tasks if needed
            const milestoneId = id.replace('milestone_', '');
            const tasksContainer = document.getElementById('milestone-tasks-' + milestoneId);
            if (tasksContainer && tasksContainer.innerHTML.includes('Loading tasks')) {
                loadMilestoneTasks(milestoneId);
            }
            
            // Set height after content loads
            setTimeout(() => {
                body.style.maxHeight = body.scrollHeight + 'px';
            }, 50);
        }
    }
}

// Load milestone tasks with status grouping
function loadMilestoneTasks(milestoneId) {
    $.ajax({
        url: '<?= base_url("contact_info/get_milestone_tasks/") ?>' + milestoneId,
        type: 'GET',
        dataType: 'json',
        success: function(tasks) {
            let html = '';
            const container = $('#milestone-tasks-' + milestoneId);
            
            if (tasks && tasks.length > 0) {
                // Group tasks by status
                const groupedTasks = {
                    todo: [],
                    in_progress: [],
                    in_review: [],
                    completed: [],
                    hold: [],
                    canceled: []
                };
                
                tasks.forEach(function(task) {
                    const status = task.task_status || 'todo';
                    if (groupedTasks[status]) {
                        groupedTasks[status].push(task);
                    } else {
                        groupedTasks.todo.push(task);
                    }
                });
                
                // Status configuration
                const statusConfig = {
                    todo: { title: '<?= translate("to_do") ?>', color: '#ffbe0b', icon: 'fas fa-clipboard-list' },
                    in_progress: { title: '<?= translate("in_progress") ?>', color: '#3a86ff', icon: 'fas fa-play-circle' },
                    in_review: { title: '<?= translate("in_review") ?>', color: '#17a2b8', icon: 'fas fa-eye' },
                    completed: { title: '<?= translate("completed") ?>', color: '#06d6a0', icon: 'fas fa-check-circle' },
                    hold: { title: '<?= translate("on_hold") ?>', color: '#fd7e14', icon: 'fas fa-pause-circle' },
                    canceled: { title: '<?= translate("canceled") ?>', color: '#dc3545', icon: 'fas fa-times-circle' }
                };
                
                // Create task status sections
                Object.keys(statusConfig).forEach(function(status) {
                    if (groupedTasks[status].length > 0) {
                        const config = statusConfig[status];
                        html += `
                            <div class="task-status-section" style="margin-bottom: 15px;">
                                <div class="task-status-header" onclick="toggleTaskStatusAccordion('status_${status}_${milestoneId}')" style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; background: ${config.color}15; border-left: 4px solid ${config.color}; border-radius: 4px; margin-bottom: 8px; cursor: pointer; transition: all 0.3s ease;">
                                    <div class="status-icon" style="width: 20px; height: 20px; border-radius: 50%; background: ${config.color}; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px;">
                                        <i class="${config.icon}"></i>
                                    </div>
                                    <span style="font-weight: 600; color: #2d3748;">${config.title}</span>
                                    <span style="background: ${config.color}; color: white; padding: 2px 6px; border-radius: 10px; font-size: 11px; font-weight: 500;">${groupedTasks[status].length}</span>
                                    <i class="fas fa-chevron-down" style="margin-left: auto; transition: transform 0.3s ease;"></i>
                                </div>
                                <div class="task-status-body" id="status-body-status_${status}_${milestoneId}" style="max-height: 0; overflow: hidden; transition: max-height 0.4s ease-out;">
                                    <div class="task-list">
                        `;
                        
                        groupedTasks[status].forEach(function(task) {
                            html += `
                                <div class="task-item" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; margin-bottom: 4px; background: #f8f9fa; border-radius: 6px; border-left: 3px solid ${config.color}; transition: all 0.2s ease;" 
                                     onmouseover="this.style.background='#e9ecef'" onmouseout="this.style.background='#f8f9fa'">
                                    <div style="flex: 1;">
                                        <div style="font-weight: 600; color: #2d3748; font-size: 13px; margin-bottom: 2px;">${task.task_title}</div>
                                        <div style="font-size: 11px; color: #6c757d;">
                                            <span style="margin-right: 10px;"><i class="fas fa-user"></i> ${task.assigned_to || 'Unassigned'}</span>
                                            <span style="margin-right: 10px;"><i class="fas fa-clock"></i> ${task.estimated_hours || '0'}h</span>
                                        </div>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        ${task.priority ? `<span style="background: ${task.priority === 'high' ? '#dc3545' : task.priority === 'medium' ? '#ffc107' : '#28a745'}; color: white; padding: 2px 6px; border-radius: 10px; font-size: 10px; text-transform: uppercase;">${task.priority}</span>` : ''}
                                        <span onclick="viewTask('${task.unique_id}')" style="background: #e3f2fd; color: #1976d2; padding: 2px 6px; border-radius: 10px; font-size: 10px; font-weight: 500; cursor: pointer; transition: all 0.2s ease;" onmouseover="this.style.background='#1976d2'; this.style.color='white'" onmouseout="this.style.background='#e3f2fd'; this.style.color='#1976d2'">${task.task_id || 'N/A'}</span>
                                    </div>
                                </div>
                            `;
                        });
                        
                        html += `
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                });
                
                if (html === '') {
                    html = '<div class="text-center" style="padding: 20px; color: #6c757d;"><i class="fas fa-tasks" style="font-size: 24px; color: #dee2e6; margin-bottom: 10px;"></i><p>No tasks found</p></div>';
                }
            } else {
                html = '<div class="text-center" style="padding: 20px; color: #6c757d;"><i class="fas fa-tasks" style="font-size: 24px; color: #dee2e6; margin-bottom: 10px;"></i><p>No tasks found for this milestone</p></div>';
            }
            
            container.html(html);
        },
        error: function() {
            $('#milestone-tasks-' + milestoneId).html('<div class="alert alert-danger" style="margin: 10px 0;"><i class="fas fa-exclamation-triangle mr-2"></i>Error loading tasks</div>');
        }
    });
}

// Toggle task status accordion sections
function toggleTaskStatusAccordion(id) {
    const header = document.querySelector(`[onclick="toggleTaskStatusAccordion('${id}')"]`);
    const body = document.getElementById('status-body-' + id);
    const chevron = header.querySelector('.fas.fa-chevron-down');
    
    if (body.style.maxHeight && body.style.maxHeight !== '0px') {
        body.style.maxHeight = '0px';
        chevron.style.transform = 'rotate(0deg)';
        header.style.background = header.style.background.replace('25', '15');
    } else {
        body.style.maxHeight = body.scrollHeight + 'px';
        chevron.style.transform = 'rotate(180deg)';
        header.style.background = header.style.background.replace('15', '25');
    }
    
    // Update parent milestone accordion height multiple times to ensure accuracy
    const updateParentHeight = () => {
        const milestoneBody = header.closest('[id^="accordion-body-milestone_"]');
        if (milestoneBody && milestoneBody.style.maxHeight !== '0px') {
            milestoneBody.style.maxHeight = 'auto';
            setTimeout(() => {
                milestoneBody.style.maxHeight = milestoneBody.scrollHeight + 'px';
            }, 10);
        }
    };
    
    // Update immediately and after transitions complete
    updateParentHeight();
    setTimeout(updateParentHeight, 100);
    setTimeout(updateParentHeight, 400);
}

// View task details in modal (global function)
function viewTask(id) {
    $.ajax({
        url: '<?= base_url("dashboard/viewTracker_Issue") ?>',
        type: 'POST',
        data: {'id': id},
        dataType: "html",
        success: function (data) {
            $('#taskDetailsModal .modal-body').html(data);
            $('#taskDetailsModal').modal('show');
        }
    });
}
</script>