<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">
  <div class="col-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <i class="fas fa-users"></i> <?= translate('team_planner') ?>
        <div class="panel-control">
          <button class="btn btn-sm btn-primary" id="mobile-dept-toggle">
            <i class="fas fa-building"></i> Departments
          </button>
        </div>
      </div>
      <div class="panel-body">
        
        <!-- Mobile Department List -->
        <div id="mobile-dept-view" class="mobile-view" style="display: none;">
          <div class="mobile-dept-list">
            <?php foreach($departments as $dept): ?>
              <div class="mobile-dept-item" data-id="<?= $dept->id ?>">
                <i class="fas fa-building"></i>
                <span><?= $dept->name ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Mobile Team Schedule View -->
        <div id="mobile-schedule-view" class="mobile-view">
          <div class="mobile-date-controls">
            <button id="mobile-prev-date" class="btn btn-sm btn-default" title="Previous Day">
              <i class="fas fa-chevron-left"></i>
            </button>
            <input type="date" id="mobile-search-date" class="form-control" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d', strtotime('+1 year')) ?>" min="<?= date('Y-m-d', strtotime('-1 year')) ?>">
            <button id="mobile-next-date" class="btn btn-sm btn-default" title="Next Day">
              <i class="fas fa-chevron-right"></i>
            </button>
          </div>
          
          <div class="mobile-selected-dept">
            <span id="selected-dept-name">Select Department</span>
          </div>
          
          <div id="mobile-team-schedule" class="mobile-team-schedule">
            <div class="text-center text-muted">
              <i class="fas fa-building"></i>
              <p>Select a department to view team schedule</p>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<style>
.mobile-view {
  min-height: 400px;
}

.mobile-dept-list {
  padding: 5px 0;
  max-height: 400px;
  overflow-y: auto;
}

.mobile-dept-item {
  display: flex;
  align-items: center;
  padding: 12px 15px;
  margin: 5px 0;
  background: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.15s ease;
  -webkit-tap-highlight-color: transparent;
}

.mobile-dept-item:active {
  transform: scale(0.98);
}

.mobile-dept-item:hover {
  background: #e9ecef;
  border-color: #adb5bd;
}

.mobile-dept-item.active {
  background: #007bff;
  color: white;
  border-color: #0056b3;
}

.mobile-dept-item i {
  margin-right: 10px;
  font-size: 14px;
  width: 16px;
}

.mobile-date-controls {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
  padding: 8px;
  background: #f8f9fa;
  border-radius: 6px;
  position: sticky;
  top: 0;
  z-index: 10;
}

.mobile-date-controls .form-control {
  flex: 1;
  text-align: center;
  font-size: 14px;
  height: 36px;
}

.mobile-date-controls .btn {
  min-width: 36px;
  height: 36px;
  padding: 0;
}

.mobile-selected-dept {
  text-align: center;
  padding: 8px 12px;
  margin-bottom: 12px;
  background: #e3f2fd;
  border-radius: 6px;
  font-weight: 600;
  color: #1976d2;
  font-size: 14px;
}

.mobile-team-schedule {
  min-height: 300px;
  max-height: 70vh;
  overflow-y: auto;
}

.mobile-staff-card {
  background: white;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  margin-bottom: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.mobile-staff-card.no-schedule {
  opacity: 0.7;
}

.mobile-staff-header {
  display: flex;
  align-items: center;
  padding: 10px 12px;
  background: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
  position: relative;
}

.mobile-staff-photo {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  margin-right: 10px;
  object-fit: cover;
  border: 2px solid #ddd;
  flex-shrink: 0;
}

.mobile-staff-info {
  flex: 1;
  min-width: 0;
}

.mobile-staff-name {
  font-weight: 600;
  font-size: 14px;
  margin-bottom: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.mobile-staff-id {
  font-size: 11px;
  color: #666;
}

.mobile-staff-status-container {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
  gap: 3px;
  align-items: center;
}

.mobile-staff-idle {
  font-size: 8px;
  padding: 2px 4px;
  border-radius: 4px;
  font-weight: 500;
  background: #ffc107;
  color: #856404;
  white-space: nowrap;
}

.mobile-staff-status {
  font-size: 9px;
  padding: 2px 5px;
  border-radius: 4px;
  font-weight: 500;
  white-space: nowrap;
}

.mobile-staff-status.available {
  background: #d4edda;
  color: #155724;
}

.mobile-staff-status.busy {
  background: #f8d7da;
  color: #721c24;
}

.mobile-staff-status.on-leave {
  background: #fff3cd;
  color: #856404;
}

.mobile-staff-status.idle {
  background: #d1ecf1;
  color: #0c5460;
}

.mobile-staff-status.full-loaded {
  background: #d4edda;
  color: #155724;
}

.mobile-staff-status.overloaded {
  background: #f8d7da;
  color: #721c24;
}

.mobile-staff-events {
  padding: 10px 12px;
}

.mobile-event-item {
  display: flex;
  align-items: flex-start;
  padding: 6px 10px;
  margin: 4px 0;
  border-radius: 4px;
  font-size: 12px;
  border-left: 3px solid #007bff;
  background: #f8f9fa;
}

.mobile-event-item.high { 
  background: #fff5f5; 
  border-left-color: #dc3545; 
}

.mobile-event-item.medium { 
  background: #fffbf0; 
  border-left-color: #ffc107; 
}

.mobile-event-item.low { 
  background: #f0fff4; 
  border-left-color: #28a745; 
}

.mobile-event-item.completed {
  opacity: 0.6;
  background: #e9ecef;
}

.mobile-event-item.completed .mobile-event-title {
  text-decoration: line-through;
}

.mobile-event-time {
  font-weight: 600;
  margin-right: 8px;
  min-width: 65px;
  font-size: 10px;
  color: #495057;
  flex-shrink: 0;
}

.mobile-event-title {
  flex: 1;
  font-weight: 500;
  line-height: 1.3;
  margin-right: 6px;
  word-break: break-word;
}

.mobile-event-id {
  font-size: 9px;
  background: #e9ecef;
  padding: 1px 4px;
  border-radius: 8px;
  flex-shrink: 0;
  color: #6c757d;
}

.mobile-event-priority {
  font-size: 10px;
  padding: 1px 3px;
  border-radius: 3px;
  font-weight: 600;
  margin-left: 4px;
  background: #6c757d;
  color: white;
}

.mobile-event-category {
  font-size: 8px;
  background: #6c757d;
  color: white;
  padding: 1px 3px;
  border-radius: 2px;
  margin-right: 4px;
  font-weight: 600;
  flex-shrink: 0;
}

.mobile-no-events {
  text-align: center;
  color: #6c757d;
  font-style: italic;
  padding: 15px;
  font-size: 13px;
}

.mobile-leave-item {
  background: #fff3cd;
  color: #856404;
  padding: 6px 10px;
  margin: 4px 0;
  border-radius: 4px;
  text-align: center;
  font-size: 11px;
  border-left: 3px solid #ffc107;
  font-weight: 500;
}

.mobile-summary {
  display: flex;
  justify-content: space-around;
  padding: 8px;
  background: #f8f9fa;
  border-radius: 6px;
  margin-bottom: 12px;
  font-size: 12px;
}

.mobile-summary-item {
  text-align: center;
}

.mobile-summary-number {
  font-weight: bold;
  font-size: 16px;
  display: block;
}

.mobile-summary-label {
  color: #666;
  font-size: 10px;
}

@media (max-width: 576px) {
  .mobile-date-controls {
    padding: 6px;
    gap: 6px;
  }
  
  .mobile-staff-header {
    padding: 8px 10px;
  }
  
  .mobile-event-item {
    padding: 5px 8px;
    font-size: 11px;
  }
  
  .mobile-event-time {
    min-width: 60px;
    font-size: 9px;
  }
  
  .mobile-staff-photo {
    width: 32px;
    height: 32px;
  }
  
  .mobile-staff-name {
    font-size: 13px;
  }
}
</style>

<script>
$(document).ready(function() {
  let currentView = 'schedule';
  let selectedDeptId = null;
  let selectedDeptName = '';

  // View toggle
  $('#mobile-dept-toggle').on('click', function() {
    if (currentView === 'schedule') {
      $('#mobile-schedule-view').hide();
      $('#mobile-dept-view').show();
      $(this).html('<i class="fas fa-calendar"></i> Schedule');
      currentView = 'departments';
    } else {
      $('#mobile-dept-view').hide();
      $('#mobile-schedule-view').show();
      $(this).html('<i class="fas fa-building"></i> Departments');
      currentView = 'schedule';
    }
  });

  // Department selection
  $('.mobile-dept-item').on('click', function() {
    $('.mobile-dept-item').removeClass('active');
    $(this).addClass('active');
    
    selectedDeptId = $(this).data('id');
    selectedDeptName = $(this).find('span').text();
    
    $('#selected-dept-name').text(selectedDeptName);
    
    // Switch back to schedule view
    $('#mobile-dept-view').hide();
    $('#mobile-schedule-view').show();
    $('#mobile-dept-toggle').html('<i class="fas fa-building"></i> Departments');
    currentView = 'schedule';
    
    loadTeamSchedule();
  });

  // Date controls
  $('#mobile-prev-date').on('click', function() {
    const currentDate = new Date($('#mobile-search-date').val());
    currentDate.setDate(currentDate.getDate() - 1);
    $('#mobile-search-date').val(currentDate.toISOString().split('T')[0]);
    loadTeamSchedule();
  });

  $('#mobile-next-date').on('click', function() {
    const currentDate = new Date($('#mobile-search-date').val());
    currentDate.setDate(currentDate.getDate() + 1);
    $('#mobile-search-date').val(currentDate.toISOString().split('T')[0]);
    loadTeamSchedule();
  });

  $('#mobile-search-date').on('change', function() {
    loadTeamSchedule();
  });
  
  $('#mobile-today').on('click', function() {
    $('#mobile-search-date').val(new Date().toISOString().split('T')[0]);
    loadTeamSchedule();
  });

  function loadTeamSchedule() {
    if (!selectedDeptId) {
      $('#mobile-team-schedule').html(`
        <div class="text-center text-muted">
          <i class="fas fa-building"></i>
          <p>Select a department to view team schedule</p>
        </div>
      `);
      return;
    }

    const searchDate = $('#mobile-search-date').val();
    
    $('#mobile-team-schedule').html(`
      <div class="text-center">
        <i class="fas fa-spinner fa-spin"></i> Loading...
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
        renderTeamSchedule(data);
      },
      error: function(xhr, status, error) {
        let errorMsg = 'Error loading team schedule';
        if (status === 'timeout') {
          errorMsg = 'Request timed out. Please try again.';
        } else if (xhr.status === 0) {
          errorMsg = 'No internet connection';
        }
        $('#mobile-team-schedule').html(`
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> ${errorMsg}
            <br><button class="btn btn-sm btn-outline-danger mt-2" onclick="loadTeamSchedule()">Retry</button>
          </div>
        `);
      }
    });
  }

  function renderTeamSchedule(teamData) {
    if (!teamData || teamData.length === 0) {
      $('#mobile-team-schedule').html(`
        <div class="alert alert-info">
          <i class="fas fa-info-circle"></i> No staff found in selected department
        </div>
      `);
      return;
    }

    // Calculate summary
    let totalStaff = teamData.length;
    let staffWithTasks = 0;
    let staffOnLeave = 0;
    let totalTasks = 0;
    
    teamData.forEach(staff => {
      if (staff.events && staff.events.length > 0) {
        staffWithTasks++;
        totalTasks += staff.events.length;
      }
      if (staff.leaves && staff.leaves.length > 0) {
        staffOnLeave++;
      }
    });

    // Function to get category abbreviation
    function getCategoryAbbreviation(categoryName) {
      if (!categoryName) return '';
      
      const category = categoryName.toLowerCase();
      if (category.includes('milestone')) return 'M';
      if (category.includes('emp request') || category.includes('employee request')) return 'ER';
      if (category.includes('customer query') || category.includes('customer')) return 'CQ';
      if (category.includes('bug') || category.includes('issue')) return 'BG';
      if (category.includes('feature') || category.includes('enhancement')) return 'FT';
      if (category.includes('support')) return 'SP';
      if (category.includes('maintenance')) return 'MT';
      if (category.includes('training')) return 'TR';
      if (category.includes('meeting')) return 'MG';
      if (category.includes('review')) return 'RV';
      if (category.includes('testing')) return 'TS';
      if (category.includes('deployment')) return 'DP';
      
      // Default: take first 2 characters
      return categoryName.substring(0, 2).toUpperCase();
    }

    // Function to calculate workload hours and status
    function calculateWorkload(events) {
      if (!events || events.length === 0) {
        return { totalHours: 0, status: 'idle', statusText: 'Under Loaded', idleText: 'Idle - 8h 30m' };
      }
      
      let totalMinutes = 0;
      events.forEach(event => {
        const startTime = new Date(event.start_time);
        const endTime = new Date(event.end_time);
        const diffMs = endTime - startTime;
        totalMinutes += Math.floor(diffMs / (1000 * 60));
      });
      
      // Add 30 minutes for lunch time
      totalMinutes += 30;
      
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

    let html = `
      <div class="mobile-summary">
        <div class="mobile-summary-item">
          <span class="mobile-summary-number">${totalStaff}</span>
          <span class="mobile-summary-label">Total Staff</span>
        </div>
        <div class="mobile-summary-item">
          <span class="mobile-summary-number">${totalTasks}</span>
          <span class="mobile-summary-label">Tasks</span>
        </div>
        <div class="mobile-summary-item">
          <span class="mobile-summary-number">${staffOnLeave}</span>
          <span class="mobile-summary-label">On Leave</span>
        </div>
      </div>
    `;
    
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
        idleText = 'Idle 8h 30m';
      }
      
      const cardClass = (!staff.events || staff.events.length === 0) && (!staff.leaves || staff.leaves.length === 0) ? ' no-schedule' : '';
      
      html += `
        <div class="mobile-staff-card${cardClass}">
          <div class="mobile-staff-header">
            <img src="${photoUrl}" alt="${staff.staff_name}" class="mobile-staff-photo" onerror="this.src='<?= base_url('uploads/images/staff/defualt.png') ?>'">
            <div class="mobile-staff-info">
              <div class="mobile-staff-name">${staff.staff_name}</div>
              <div class="mobile-staff-id">${staff.staff_code}</div>
            </div>
            <div class="mobile-staff-status-container">
              ${idleText ? `<div class="mobile-staff-idle">${idleText}</div>` : ''}
              <div class="mobile-staff-status ${status}">${statusText}</div>
            </div>
          </div>
          <div class="mobile-staff-events">
      `;

      // Show leaves first
      if (staff.leaves && staff.leaves.length > 0) {
        staff.leaves.forEach(leave => {
          html += `
            <div class="mobile-leave-item">
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
          const taskPriority = event.task_priority || 5;
          const categoryAbbr = getCategoryAbbreviation(event.category_name);
          //const taskTitle = categoryAbbr ? `${categoryAbbr} - ${event.task_title || 'Untitled Task'}` : (event.task_title || 'Untitled Task');
          
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
            <div class="mobile-event-item ${priorityClass}${completedClass}">
              <div class="mobile-event-time">${timeStr}</div>
              <div class="mobile-event-title">${taskTitle}<span class="mobile-event-priority">${taskPriority}</span></div>
              <div class="mobile-event-id">${event.unique_id || 'N/A'}</div>
            </div>
          `;
        });
      } else if (!staff.leaves || staff.leaves.length === 0) {
        html += `
          <div class="mobile-no-events">
            <i class="fas fa-calendar-check"></i> No tasks scheduled
          </div>
        `;
      }

      html += `
          </div>
        </div>
      `;
    });

    $('#mobile-team-schedule').html(html);
  }

  // Auto-select user's department if available, otherwise first department
  if ($('.mobile-dept-item').length > 0) {
    setTimeout(() => {
      var userDeptId = '<?= isset($user_dept_id) ? $user_dept_id : '' ?>';
      var $targetDept = userDeptId ? $('.mobile-dept-item[data-id="' + userDeptId + '"]') : $('.mobile-dept-item:first');
      if ($targetDept.length > 0) {
        $targetDept.click();
      } else {
        $('.mobile-dept-item:first').click();
      }
    }, 100);
  } else {
    $('#mobile-team-schedule').html(`
      <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> No departments available
      </div>
    `);
  }
  
  // Add pull-to-refresh functionality
  let startY = 0;
  let pullDistance = 0;
  const pullThreshold = 80;
  
  $('#mobile-team-schedule').on('touchstart', function(e) {
    if ($(this).scrollTop() === 0) {
      startY = e.originalEvent.touches[0].pageY;
    }
  });
  
  $('#mobile-team-schedule').on('touchmove', function(e) {
    if (startY && $(this).scrollTop() === 0) {
      pullDistance = e.originalEvent.touches[0].pageY - startY;
      if (pullDistance > 0 && pullDistance < pullThreshold) {
        e.preventDefault();
      }
    }
  });
  
  $('#mobile-team-schedule').on('touchend', function() {
    if (pullDistance > pullThreshold) {
      loadTeamSchedule();
    }
    startY = 0;
    pullDistance = 0;
  });
});
</script>