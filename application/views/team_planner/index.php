<div class="panel-body">
    <div class="col-md-3">
        <div class="panel">
            <div class="panel-heading">
                <h4>Departments</h4>
            </div>
            <div class="panel-body">
                <div class="department-list">
                    <?php foreach($departments as $dept): ?>
                        <div class="department-item" data-id="<?= $dept->id ?>">
                            <?= $dept->name ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="panel">
            <div class="panel-body">
                <div class="date-controls mb-3">
                    <button id="prev-date" class="btn btn-default">Previous</button>
                    <label>Date:</label>
                    <input type="date" id="search-date" class="form-control" value="<?= date('Y-m-d') ?>" style="display:inline-block; width:auto; margin:0 10px;">
                    <button id="next-date" class="btn btn-default">Next</button>
                </div>
                
                <div id="team-summary" class="team-summary" style="display: none;">
                    <div class="summary-item">
                        <span class="summary-number" id="total-staff">0</span>
                        <span class="summary-label">Total Staff</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-number" id="total-tasks">0</span>
                        <span class="summary-label">Tasks</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-number" id="staff-on-leave">0</span>
                        <span class="summary-label">On Leave</span>
                    </div>
                </div>
                
                <div id="team-timeline" class="team-timeline">
                    <div class="text-center">
                        <p>Select a department to view team schedule</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.team-timeline {
    min-height: 358px;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow-x: auto;
    overflow-y: auto;
}

.department-list {
    max-height: 400px;
    overflow-y: auto;
}

.department-item {
    padding: 10px 15px;
    margin: 5px 0;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
}

.department-item:hover {
    background: #e9ecef;
    border-color: #adb5bd;
}

.department-item.active {
    background: #007bff;
    color: white;
    border-color: #0056b3;
}


.date-controls {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: nowrap;
    padding: 20px 20px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.date-controls label {
    font-weight: 600;
    font-size: 16px;
    color: #495057;
    margin-right: 15px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.date-controls .btn {
    padding: 8px 20px;
    font-weight: 600;
    border-radius: 6px;
    margin: 0 10px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.date-controls .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.date-controls .form-control {
    border-radius: 6px;
    border: 2px solid #dee2e6;
    padding: 8px 12px;
    font-weight: 500;
    transition: border-color 0.3s ease;
    min-width: 150px;
}

@media (max-width: 768px) {
    .date-controls {
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .date-controls .btn {
        margin: 0;
        padding: 6px 15px;
    }
    
    .date-controls .form-control {
        min-width: 120px;
    }
    
    .date-controls label {
        margin-right: 10px;
        font-size: 14px;
    }
}

.date-controls .form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

.team-summary {
    display: flex;
    justify-content: space-around;
    padding: 15px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.summary-item {
    text-align: center;
    flex: 1;
}

.summary-number {
    display: block;
    font-size: 24px;
    font-weight: bold;
    color: #007bff;
    margin-bottom: 5px;
}

.summary-label {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.timeline-container {
    position: relative;
    min-width: 980px;
}

.timeline-header {
    display: flex;
    background: #f5f5f5;
    border-bottom: 2px solid #ddd;
    position: sticky;
    top: 0;
    z-index: 10;
    min-width: 980px;
}

.staff-column {
    width: 200px;
    min-width: 200px;
    max-width: 200px;
    padding: 10px;
    border-right: 1px solid #ddd;
    font-weight: bold;
    background: #fff;
}

.time-grid {
    position: relative;
    width: 780px;
    height: 40px;
    background: 
        repeating-linear-gradient(
            to right,
            #ddd 0px,
            #ddd 1px,
            transparent 1px,
            transparent 60px
        );
}

.hour-marker {
    position: absolute;
    top: 10px;
    font-size: 11px;
    font-weight: bold;
    color: #666;
    transform: translateX(-50%);
    white-space: nowrap;
}

.timeline-row {
    display: flex;
    border-bottom: 1px solid #eee;
    min-height: 60px;
    min-width: 980px;
}

.timeline-row:hover {
    background: #f9f9f9;
}

.staff-cell {
    width: 200px;
    min-width: 200px;
    max-width: 200px;
    padding: 10px;
    border-right: 1px solid #ddd;
    display: flex;
    align-items: center;
    background: #fff;
}

.staff-photo {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 10px;
    object-fit: cover;
    border: 2px solid #ddd;
}

.staff-info {
    flex: 1;
}

.staff-name {
    font-weight: bold;
    font-size: 14px;
    word-wrap: break-word;
    overflow-wrap: break-word;
    line-height: 1.2;
}

.staff-id {
    font-size: 11px;
    color: #666;
}

.task-timeline {
    position: relative;
    width: 780px;
    height: 60px;
    background: 
        repeating-linear-gradient(
            to right,
            #eee 0px,
            #eee 1px,
            transparent 1px,
            transparent 60px
        );
}

.task-bar {
    position: absolute;
    top: 5px;
    height: 50px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    overflow: hidden;
}

.task-bar {
    border-left: 6px solid #3498db;
}

.task-bar.high { border-left-color: #e74c3c; }
.task-bar.medium { border-left-color: #f39c12; }
.task-bar.low { border-left-color: #27ae60; }
.task-bar.completed { opacity: 0.6; }

.task-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 0 12px;
}

.task-title {
    font-size: 12px;
    font-weight: 600;
    color: #2c3e50;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.2;
}

.task-id {
    font-size: 9px;
    color: #6c757d;
    background: #e9ecef;
    padding: 2px 6px;
    border-radius: 10px;
    margin-top: 3px;
    display: inline-block;
    white-space: nowrap;
    width: fit-content;
}

.leave-block {
    background: #95a5a6;
    color: white;
    padding: 2px 5px;
    margin: 1px 0;
    border-radius: 3px;
    font-size: 11px;
    text-align: center;
}

.leave-bar {
    background: #ffc107 !important;
    border-left-color: #f39c12 !important;
    color: #856404 !important;
}

.leave-bar .task-title {
    color: #856404 !important;
    font-weight: bold;
    text-align: center;
}

.leave-bar .task-id {
    background: rgba(133, 100, 4, 0.2) !important;
    color: #856404 !important;
}
</style>

<script>
$(document).ready(function() {
    let currentData = [];
    
    function loadTeamData() {
        const deptId = $('.department-item.active').data('id');
        const searchDate = $('#search-date').val();
        
        if (!deptId) {
            $('#team-timeline').html('<div class="text-center"><p>Click on a department to view team schedule</p></div>');
            $('#team-summary').hide();
            return;
        }
        
        $('#team-timeline').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</div>');
        
        $.ajax({
            url: '<?= base_url("team_planner/get_team_data") ?>',
            data: { department_id: deptId, start: searchDate, end: searchDate },
            dataType: 'json',
            success: function(data) {
                currentData = data;
                renderTimeline(data, searchDate, searchDate);
            },
            error: function(xhr, status, error) {
                console.log('Error:', xhr.responseText);
                $('#team-timeline').html('<div class="alert alert-danger">Error: ' + error + '</div>');
            }
        });
    }
    
    function renderTimeline(teamData, startDate, endDate) {
        if (!teamData || teamData.length === 0) {
            $('#team-timeline').html('<div class="alert alert-info">No staff found in selected department</div>');
            $('#team-summary').hide();
            return;
        }
        
        // Calculate summary statistics
        let totalStaff = teamData.length;
        let totalTasks = 0;
        let staffOnLeave = 0;
        
        teamData.forEach(staff => {
            if (staff.events && staff.events.length > 0) {
                totalTasks += staff.events.length;
            }
            if (staff.leaves && staff.leaves.length > 0) {
                staffOnLeave++;
            }
        });
        
        // Update summary display
        $('#total-staff').text(totalStaff);
        $('#total-tasks').text(totalTasks);
        $('#staff-on-leave').text(staffOnLeave);
        $('#team-summary').show();
        
        let html = '<div class="timeline-container">';
        html += '<div class="timeline-header">';
        html += '<div class="staff-column">Employee</div>';
        html += '<div class="time-grid">';
        
        // Create hour markers
        for (let h = 9; h <= 21; h++) {
            const hour12 = h > 12 ? h - 12 : h;
            const ampm = h >= 12 ? 'PM' : 'AM';
            const displayHour = h === 12 ? 12 : hour12;
            html += `<div class="hour-marker" style="left: ${(h-9) * 60 + 30}px;">${displayHour}:00 ${ampm}</div>`;
        }
        html += '</div></div>';
        
        teamData.forEach(staff => {
            html += '<div class="timeline-row">';
            const photoUrl = staff.staff_photo ? `<?= base_url('uploads/images/staff/') ?>${staff.staff_photo}` : `<?= base_url('uploads/images/staff/defualt.png') ?>`;
            html += `<div class="staff-cell">
                        <img src="${photoUrl}" alt="${staff.staff_name}" class="staff-photo">
                        <div class="staff-info">
                            <div class="staff-name">${staff.staff_name}</div>
                            <div class="staff-id">${staff.staff_code}</div>
                        </div>
                     </div>`;
            
            html += '<div class="task-timeline">';
            
            // Check if staff is on leave first
            if (staff.leaves && staff.leaves.length > 0) {
                // Show full-day leave block
                html += `<div class="task-bar leave-bar" 
                             style="left: 0px; width: 780px;"
                             title="On Leave">
                            <div class="task-content">
                                <div class="task-title">🏖️ ON LEAVE</div>
                            </div>
                         </div>`;
            } else {
                // Show regular events if not on leave
                staff.events.forEach(event => {
                    if (!event.start_time || !event.end_time) return;
                    
                    const startTime = new Date(event.start_time);
                    const endTime = new Date(event.end_time);
                    const startHour = startTime.getHours() + startTime.getMinutes() / 60;
                    const endHour = endTime.getHours() + endTime.getMinutes() / 60;
                    
                    if (startHour >= 9 && startHour <= 21) {
                        const left = (startHour - 9) * 60; // 60px per hour
                        const width = (endHour - startHour) * 60;
                        const priorityClass = event.priority_level || 'low';
                        const completedClass = event.status == 1 ? ' completed' : '';
                        
                        html += `<div class="task-bar ${priorityClass}${completedClass}" 
                                     style="left: ${left}px; width: ${width}px;"
                                     title="${event.task_title} (${startTime.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})} - ${endTime.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})})">
                                    <div class="task-content">
                                        <div class="task-title">${event.task_title}</div>
                                        <div class="task-id">${event.unique_id || 'N/A'}</div>
                                    </div>
                                 </div>`;
                    }
                });
            }
            
            html += '</div></div>';
        });
        
        html += '</div>';
        $('#team-timeline').html(html);
    }
    

    
    $('.department-item').on('click', function() {
        $('.department-item').removeClass('active');
        $(this).addClass('active');
        loadTeamData();
    });
    
    $('#search-date').on('change', loadTeamData);
    
    // Auto-select user's department if available, otherwise first department
    if ($('.department-item').length > 0) {
        var userDeptId = '<?= isset($user_dept_id) ? $user_dept_id : '' ?>';
        var $targetDept = userDeptId ? $('.department-item[data-id="' + userDeptId + '"]') : $('.department-item:first');
        if ($targetDept.length > 0) {
            $targetDept.addClass('active');
        } else {
            $('.department-item:first').addClass('active');
        }
        loadTeamData();
    } else {
        $('#team-timeline').html('<div class="alert alert-info"><i class="fas fa-info-circle"></i> No departments available</div>');
        $('#team-summary').hide();
    }
    
    $('#prev-date').on('click', function() {
        const searchDate = new Date($('#search-date').val());
        searchDate.setDate(searchDate.getDate() - 1);
        $('#search-date').val(searchDate.toISOString().split('T')[0]);
        loadTeamData();
    });
    
    $('#next-date').on('click', function() {
        const searchDate = new Date($('#search-date').val());
        searchDate.setDate(searchDate.getDate() + 1);
        $('#search-date').val(searchDate.toISOString().split('T')[0]);
        loadTeamData();
    });
});
</script>

<!-- Trigger click on page load -->
<script>
$(document).ready(function() {
    // Ensure the button exists and is visible
    if ($('#sidebar-toggle-button').length) {
        setTimeout(function() {
            $('#sidebar-toggle-button').click();  // Trigger the click event
        }, 10);  // 100ms delay to ensure it's ready
    }
});
</script>