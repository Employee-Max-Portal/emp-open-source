<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">
  <div class="col-md-12">
    <div class="panel">
      <div class="panel-heading professional-header">
        <div class="header-left">
          <h4 class="panel-title">
            <i class="fas fa-calendar-alt"></i> Team View
          </h4>
        </div>
        <div class="header-right">
          <div class="date-controls-header">
            <button id="prev-date" class="btn btn-sm btn-outline-secondary">
              <i class="fas fa-chevron-left"></i>
            </button>
            <input type="date" id="search-date" class="form-control date-input" value="<?= date('Y-m-d') ?>">
            <button id="next-date" class="btn btn-sm btn-outline-secondary">
              <i class="fas fa-chevron-right"></i>
            </button>
          </div>
          <button class="btn btn-sm btn-primary" id="dept-toggle">
            Departments
          </button>
        </div>
      </div>
      <div class="panel-body">
        
        <!-- Department List -->
        <div id="dept-view" class="dept-view" style="display: none;">
          <div class="dept-list">
            <?php foreach($departments as $dept): ?>
              <div class="dept-item" data-id="<?= $dept->id ?>">
                <i class="fas fa-building"></i>
                <span><?= $dept->name ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Team Card View -->
        <div id="card-view" class="card-view">
        
        <div id="selected-dept-info" class="selected-dept-info" style="display: none;">
          <span id="selected-dept-name">Select Department</span>
        </div>
        
        <div id="team-summary" class="team-summary" style="display: none;">
          <div class="summary-item">
            <span class="summary-number" id="total-staff">0</span>
            <span class="summary-label">Total Staff</span>
          </div>
          <div class="summary-item">
            <span class="summary-number" id="total-tasks">0</span>
            <span class="summary-label">Planner Tasks (Today)</span>
          </div>
          <div class="summary-item">
            <span class="summary-number" id="staff-on-leave">0</span>
            <span class="summary-label">On Leave</span>
          </div>
          <!-- <div class="summary-item">
            <span class="summary-number" id="staff-available">0</span>
            <span class="summary-label">Available</span>
          </div> -->
        </div>
        
        <div id="team-cards" class="team-cards">
          <div class="text-center text-muted">
            <i class="fas fa-building fa-3x"></i>
            <p>Select a department to view team cards</p>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<style>
.dept-view {
  min-height: 400px;
}

.dept-list {
  padding: 5px 0;
  max-height: 400px;
  overflow-y: auto;
}

.dept-item {
  display: flex;
  align-items: center;
  padding: 12px 15px;
  margin: 5px 0;
  background: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.dept-item:hover {
  background: #e9ecef;
  border-color: #adb5bd;
}

.dept-item.active {
  background: #007bff;
  color: white;
  border-color: #0056b3;
}

.dept-item i {
  margin-right: 10px;
  font-size: 14px;
  width: 16px;
}

.professional-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 5px 20px;
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
  border-bottom: 2px solid #e9ecef;
}

.header-left .panel-title {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  color: #2c3e50;
}

.header-left .panel-title i {
  margin-right: 8px;
  color: #007bff;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 15px;
}

.date-controls-header {
  display: flex;
  align-items: center;
  gap: 5px;
  background: #f8f9fa;
  padding: 5px;
  border-radius: 8px;
  border: 1px solid #dee2e6;
}

.date-controls-header .btn {
  border: none;
  background: transparent;
  color: #6c757d;
  padding: 6px 10px;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.date-controls-header .btn:hover {
  background: #e9ecef;
  color: #495057;
}

.date-input {
  border: none;
  background: transparent;
  padding: 6px 10px;
  font-size: 14px;
  font-weight: 500;
  color: #495057;
  width: 140px;
  text-align: center;
}

.date-input:focus {
  outline: none;
  box-shadow: none;
  background: #ffffff;
  border-radius: 4px;
}

.selected-dept-info {
  text-align: center;
  padding: 10px 15px;
  margin-bottom: 10px;
  background: #28a745;
  border-radius: 6px;
  color: white;
  font-size: 20px;
}

.team-summary {
  display: flex;
  justify-content: space-around;
  padding: 10px;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border: 1px solid #dee2e6;
  border-radius: 8px;
  margin-bottom: 10px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.summary-item {
  text-align: center;
  flex: 1;
}

.summary-number {
  display: block;
  font-size: 28px;
  font-weight: bold;
  color: #007bff;
  margin-bottom: 5px;
}

.summary-label {
  font-size: 12px;
  color: #666;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 500;
}

.team-cards {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
}

.staff-card {
  flex: 0 0 calc(33.333% - 14px);
  background: white;
  border: 1px solid #dee2e6;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  transition: all 0.3s ease;
  position: relative;
}

.staff-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.staff-card.no-schedule {
  opacity: 0.7;
  border-color: #e9ecef;
}

.staff-card-header {
  display: flex;
  align-items: center;
  padding: 8px;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-bottom: 1px solid #dee2e6;
  position: relative;
}

.staff-photo {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  margin-right: 15px;
  object-fit: cover;
  border: 3px solid #fff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  flex-shrink: 0;
}

.staff-info {
  flex: 1;
  min-width: 0;
}

.staff-name {
  font-weight: 600;
  font-size: 16px;
  margin-bottom: 4px;
  color: #2c3e50;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.staff-code-inline {
  font-weight: 400;
  font-size: 12px;
  color: #6c757d;
}

.staff-id {
  font-size: 12px;
  color: #6c757d;
  background: #e9ecef;
  padding: 2px 8px;
  border-radius: 12px;
  display: inline-block;
}

.staff-status-container {
  position: absolute;
  right: 15px;
  top: 20px;
  display: flex;
  gap: 4px;
  align-items: center;
}

.staff-idle {
  font-size: 10px;
  padding: 2px 5px;
  border-radius: 6px;
  font-weight: 600;
  background: #ffc107;
  white-space: nowrap;
  text-transform: uppercase;
}

.staff-status {
  font-size: 10px;
  padding: 2px 6px;
  border-radius: 6px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  white-space: nowrap;
}

.staff-status.available {
  background: #d4edda;
  color: #155724;
}

.staff-status.busy {
  background: #f8d7da;
  color: #721c24;
}

.staff-status.on-leave {
  background: #fff3cd;
  color: #856404;
}

.staff-status.idle {
  background: #d1ecf1;
  color: #0c5460;
}

.staff-status.full-loaded {
  background: #d4edda;
  color: #155724;
}

.staff-status.overloaded {
  background: #f8d7da;
  color: #721c24;
}

.staff-card-body {
  padding-top: 5px;
  padding-left: 10px;
  padding-right: 10px;
  padding-bottom: 5px;
}

.event-item {
  display: flex;
  align-items: flex-start;
  padding: 6px 6px;
  margin: 2px 0;
  border-radius: 6px;
  font-size: 13px;
  border-left: 4px solid #007bff;
  background: #f8f9fa;
  transition: all 0.2s ease;
}

.event-item:hover {
  background: #e9ecef;
  transform: translateX(2px);
}

.event-item.high { 
  background: #fff5f5; 
  border-left-color: #dc3545; 
}

.event-item.medium { 
  background: #fffbf0; 
  border-left-color: #ffc107; 
}

.event-item.low { 
  background: #f0fff4; 
  border-left-color: #28a745; 
}

.event-item.completed {
  opacity: 0.6;
  background: #e9ecef;
}

.event-item.completed .event-title {
  text-decoration: line-through;
}

.event-time {
  font-weight: 600;
  margin-right: 5px;
  //min-width: 65px;
  font-size: 11px;
  color: #495057;
  flex-shrink: 0;
}

.event-content {
  flex: 1;
  min-width: 0;
}

.event-title {
  font-weight: 500;
  line-height: 1.3;
  margin-bottom: 2px;
  word-break: break-word;
}

.event-priority {
  font-size: 12px;
  padding: 1px 4px;
  border-radius: 3px;
  font-weight: 600;
  margin-left: 10px;
  background: #6c757d;
  color: white;
}

.no-events {
  text-align: center;
  color: #6c757d;
  font-style: italic;
  padding: 20px;
  font-size: 14px;
}

.no-events i {
  font-size: 24px;
  margin-bottom: 8px;
  display: block;
}

.leave-item {
  background: #fff3cd;
  color: #856404;
  padding: 10px 15px;
  margin: 6px 0;
  border-radius: 6px;
  text-align: center;
  font-size: 13px;
  border-left: 4px solid #ffc107;
  font-weight: 600;
}

.leave-item i {
  margin-right: 8px;
}

@media (max-width: 992px) {
  .staff-card {
    flex: 0 0 calc(50% - 10px);
  }
}

@media (max-width: 768px) {
  .staff-card {
    flex: 0 0 100%;
  }
  
  .professional-header {
    flex-direction: column;
    gap: 15px;
    padding: 15px;
  }
  
  .header-right {
    width: 100%;
    justify-content: space-between;
  }
  
  .date-controls-header {
    flex: 1;
    margin-right: 10px;
  }
  
  .team-summary {
    padding: 15px;
  }
  
  .summary-number {
    font-size: 24px;
  }
}

.staff-id-status {
  display: flex;
  align-items: center;
  gap: 8px; /* space between ID and status */
  margin-top: 4px;
}

</style>

<script>
$(document).ready(function() {
  let selectedDeptId = null;
  let selectedDeptName = '';

  let currentView = 'schedule';

  // View toggle
  $('#dept-toggle').on('click', function() {
    if (currentView === 'schedule') {
      $('#card-view').hide();
      $('#dept-view').show();
      $(this).html('Schedule');
      currentView = 'departments';
    } else {
      $('#dept-view').hide();
      $('#card-view').show();
      $(this).html('Departments');
      currentView = 'schedule';
    }
  });

  // Department selection
  $('.dept-item').on('click', function() {
    $('.dept-item').removeClass('active');
    $(this).addClass('active');
    
    selectedDeptId = $(this).data('id');
    selectedDeptName = $(this).find('span').text();
    
    updateDeptInfo();
    $('#selected-dept-info').show();
    
    // Switch back to cards view
    $('#dept-view').hide();
    $('#card-view').show();
    $('#dept-toggle').html('Departments');
    currentView = 'schedule';
    
    loadTeamCards();
  });

function updateDeptInfo() {
  const searchDate = new Date($('#search-date').val());

  // Extract parts individually
  const weekday = searchDate.toLocaleDateString('en-US', { weekday: 'long' });
  const day = searchDate.toLocaleDateString('en-US', { day: 'numeric' });
  const month = searchDate.toLocaleDateString('en-US', { month: 'long' });
  const year = searchDate.toLocaleDateString('en-US', { year: 'numeric' });

  // Build in desired format: Monday, 22 September, 2025
  const formattedDate = `${weekday}, ${day} ${month}, ${year}`;

  $('#selected-dept-name').text(`${selectedDeptName} - ${formattedDate}`);
}


  // Date controls
  $('#prev-date').on('click', function() {
    const currentDate = new Date($('#search-date').val());
    currentDate.setDate(currentDate.getDate() - 1);
    $('#search-date').val(currentDate.toISOString().split('T')[0]);
    if (selectedDeptName) updateDeptInfo();
    loadTeamCards();
  });

  $('#next-date').on('click', function() {
    const currentDate = new Date($('#search-date').val());
    currentDate.setDate(currentDate.getDate() + 1);
    $('#search-date').val(currentDate.toISOString().split('T')[0]);
    if (selectedDeptName) updateDeptInfo();
    loadTeamCards();
  });

  $('#search-date').on('change', function() {
    if (selectedDeptName) updateDeptInfo();
    loadTeamCards();
  });
  
  $('#today-btn').on('click', function() {
    $('#search-date').val(new Date().toISOString().split('T')[0]);
    loadTeamCards();
  });

  function loadTeamCards() {
    if (!selectedDeptId) {
      $('#team-cards').html(`
        <div class="text-center text-muted w-100">
          <i class="fas fa-building fa-3x"></i>
          <p>Select a department to view team cards</p>
        </div>
      `);
      $('#team-summary').hide();
      return;
    }

    const searchDate = $('#search-date').val();
    
    $('#team-cards').html(`
      <div class="text-center w-100">
        <i class="fas fa-spinner fa-spin fa-2x"></i>
        <p>Loading team data...</p>
      </div>
    `);

    $.ajax({
      url: '<?= base_url("team_planner/get_team_data") ?>',
      data: { 
        department_id: selectedDeptId, 
        start: searchDate, 
        end: searchDate 
      },
      dataType: 'json',
      timeout: 10000,
      success: function(data) {
        renderTeamCards(data);
      },
      error: function(xhr, status, error) {
        let errorMsg = 'Error loading team data';
        if (status === 'timeout') {
          errorMsg = 'Request timed out. Please try again.';
        } else if (xhr.status === 0) {
          errorMsg = 'No internet connection';
        }
        $('#team-cards').html(`
          <div class="alert alert-danger w-100">
            <i class="fas fa-exclamation-triangle"></i> ${errorMsg}
            <br><button class="btn btn-sm btn-outline-danger mt-2" onclick="loadTeamCards()">Retry</button>
          </div>
        `);
        $('#team-summary').hide();
      }
    });
  }

  function renderTeamCards(teamData) {
    if (!teamData || teamData.length === 0) {
      $('#team-cards').html(`
        <div class="alert alert-info w-100">
          <i class="fas fa-info-circle"></i> No staff found in selected department
        </div>
      `);
      $('#team-summary').hide();
      return;
    }

    // Calculate summary
    let totalStaff = teamData.length;
    let staffWithTasks = 0;
    let staffOnLeave = 0;
    let totalTasks = 0;
    let staffAvailable = 0;
    
    teamData.forEach(staff => {
      if (staff.events && staff.events.length > 0) {
        staffWithTasks++;
        totalTasks += staff.events.length;
      }
      if (staff.leaves && staff.leaves.length > 0) {
        staffOnLeave++;
      } else if (!staff.events || staff.events.length === 0) {
        staffAvailable++;
      }
    });

    // Function to calculate workload hours and status
    function calculateWorkload(events) {
      if (!events || events.length === 0) {
        return { totalHours: 0, status: 'idle', statusText: 'Idle - 8h 30m' };
      }
      
      let totalMinutes = 0;
      events.forEach(event => {
        const startTime = new Date(event.start_time);
        const endTime = new Date(event.end_time);
        const diffMs = endTime - startTime;
        totalMinutes += Math.floor(diffMs / (1000 * 60));
      });
      
      // Add 30 minutes for lunch time
      //totalMinutes += 30;
      
      const hours = Math.floor(totalMinutes / 60);
      const minutes = totalMinutes % 60;
      const totalHoursDecimal = totalMinutes / 60;
      
      let status, statusText, idleText = '';
      if (totalHoursDecimal < 8.5) {
        // Calculate pending hours to reach 8.5 hours
        const pendingMinutes = (8.5 * 60) - totalMinutes;
        const pendingHours = Math.floor(pendingMinutes / 60);
        const pendingMins = Math.floor(pendingMinutes % 60);
        
        status = 'idle';
        statusText = 'Under Loaded';
        if (totalHoursDecimal === 0) {
          idleText = 'Idle 8h 30m';
        } else {
          idleText = `Idle - ${pendingHours}h ${pendingMins}m`;
        }
      } else if (totalHoursDecimal === 8.5) {
        status = 'full-loaded';
        statusText = 'Full Loaded';
      } else {
        status = 'overloaded';
        statusText = 'Overloaded';
      }
      
      return { totalHours: totalHoursDecimal, status, statusText, idleText };
    }

    // Update summary
    $('#total-staff').text(totalStaff);
    $('#total-tasks').text(totalTasks);
    $('#staff-on-leave').text(staffOnLeave);
    $('#staff-available').text(staffAvailable);
    $('#team-summary').show();

    let html = '';
    
    teamData.forEach(staff => {
      const photoUrl = staff.staff_photo ? 
        `<?= base_url('uploads/images/staff/') ?>${staff.staff_photo}` : 
        `<?= base_url('uploads/images/staff/defualt.png') ?>`;
      
      // Calculate workload
      const workload = calculateWorkload(staff.events);
      
      // Determine staff status
      let status = 'available';
      let statusText = 'Available';
      let idleText = '';
      
      if (staff.leaves && staff.leaves.length > 0) {
        status = 'on-leave';
        statusText = 'On Leave';
      } else if (staff.events && staff.events.length > 0) {
        status = workload.status;
        statusText = workload.statusText;
        idleText = workload.idleText || '';
      } else {
        // No events, show full idle time
        status = 'idle';
        statusText = 'Under Loaded';
        idleText = 'Idle - 8h 30m';
      }
      
      const cardClass = (!staff.events || staff.events.length === 0) && (!staff.leaves || staff.leaves.length === 0) ? ' no-schedule' : '';
      
      html += `
        <div class="staff-card${cardClass}">
        <div class="staff-card-header">
		  <img src="${photoUrl}" alt="${staff.staff_name}" class="staff-photo"
			   onerror="this.src='<?= base_url('uploads/images/staff/defualt.png') ?>'">

		  <div class="staff-info">
			<div class="staff-name">
			  ${staff.staff_name} <span class="staff-code-inline">(${staff.staff_code})</span>
			</div>

			<div class="staff-id-status">
			  <span class="staff-id">${staff.designation || 'N/A'}</span>
			  ${idleText ? `<span class="staff-idle">${idleText}</span>` : ''}
			  <span class="staff-status ${status}">${statusText}</span>
			</div>
		  </div>
		</div>

          <div class="staff-card-body">
      `;

      // Show leaves first
      if (staff.leaves && staff.leaves.length > 0) {
        staff.leaves.forEach(leave => {
          html += `
            <div class="leave-item">
              <i class="fas fa-calendar-times"></i> On Leave
            </div>
          `;
        });
      }

      // Show events
      if (staff.events && staff.events.length > 0) {
        // Sort events by start time
        const sortedEvents = staff.events.sort((a, b) => new Date(a.start_time) - new Date(b.start_time));
        
        sortedEvents.forEach(event => {
          const startTime = new Date(event.start_time);
          const endTime = new Date(event.end_time);
          const timeStr = `${startTime.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})} - ${endTime.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}`;
          const priorityClass = event.priority_level || 'low';
          const completedClass = event.status == 1 ? ' completed' : '';
          const taskPriority = event.task_priority;
          
          // Generate category code
          let categoryCode = '';
          if (event.category) {
            const category = event.category.toLowerCase();
            if (category.includes('milestone')) {
              categoryCode = 'M';
            } else if (category.includes('emp request')) {
              categoryCode = 'ER';
            } else if (category.includes('customer query')) {
              categoryCode = 'CQ';
            } else if (category.includes('incident')) {
              categoryCode = 'I';
            } else if (category.includes('explore')) {
              categoryCode = 'E';
            } else {
              categoryCode = category.charAt(0).toUpperCase();
            }
          }
          
          const taskTitle = categoryCode ? `<strong>${categoryCode}</strong> - ${event.task_title || 'Untitled Task'}` : (event.task_title || 'Untitled Task');
          
          html += `
            <div class="event-item ${priorityClass}${completedClass}">
              <div class="event-time">${timeStr}</div>
              <div class="event-content">
                <div class="event-title">${taskTitle}<span class="event-priority">${taskPriority}</span></div>
              </div>
            </div>
          `;
        });
      } else if (!staff.leaves || staff.leaves.length === 0) {
        html += `
          <div class="no-events">
            <i class="fas fa-calendar-check"></i>
            No tasks scheduled for today
          </div>
        `;
      }

      html += `
          </div>
        </div>
      `;
    });

    $('#team-cards').html(html);
  }

  // Auto-select user's department if available
  if ($('.dept-item').length > 0) {
    setTimeout(() => {
      var userDeptId = '<?= isset($user_dept_id) ? $user_dept_id : '' ?>';
      var $targetDept = userDeptId ? $('.dept-item[data-id="' + userDeptId + '"]') : $('.dept-item:first');
      if ($targetDept.length > 0) {
        $targetDept.click();
      } else {
        $('.dept-item:first').click();
      }
    }, 100);
  } else {
    $('#team-cards').html(`
      <div class="alert alert-info w-100">
        <i class="fas fa-info-circle"></i> No departments available
      </div>
    `);
    $('#team-summary').hide();
  }
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