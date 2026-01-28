<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">
  <div class="col-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <i class="fas fa-calendar-alt"></i> <?= translate('task_planner') ?>
        <div class="panel-control">
          <button class="btn btn-sm btn-primary" id="mobile-view-toggle">
            <i class="fas fa-list"></i> <?= translate('tasks') ?>
          </button>
        </div>
      </div>
      <div class="panel-body">
        
        <!-- Mobile Calendar View -->
        <div id="mobile-calendar-view" class="mobile-view">
          <div class="mobile-today-header">
            <h5 id="mobile-today-date" class="mb-0"></h5>
          </div>
          <div class="mobile-today-card">
            <div class="mobile-today-number"></div>
            <div id="mobile-today-events" class="mobile-today-events"></div>
          </div>
          <div class="mobile-drop-zone" id="mobile-drop-zone">
            <i class="fas fa-calendar-plus"></i>
            <span>Drop tasks here to schedule for today</span>
          </div>
        </div>

        <!-- Mobile Task List View -->
        <div id="mobile-task-view" class="mobile-view" style="display: none;">
          <div class="mobile-search-bar">
            <input type="text" id="mobile-task-search" class="form-control" placeholder="<?= translate('search_tasks') ?>">
          </div>
          <div id="mobile-task-list" class="mobile-task-list"></div>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Mobile Event Modal -->
<div class="modal fade" id="mobileEventModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="mobile-event-title"></h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
	  <div class="form-group">
			<button type="button" class="btn btn-sm btn-info pull-right" id="mobile-view-sop-btn" style="margin-top: -5px; font-size: 11px;">
			  <i class="fas fa-eye"></i> <?= translate('view_sop') ?>
			</button>
          <div id="mobile-event-description"></div>
        </div>
        <div class="form-group">
          <label><?= translate('priority') ?></label>
          <span id="mobile-event-priority" class="badge"></span>
        </div>
        <div class="form-group">
          <label><?= translate('task_priority') ?>:</label>
          <select id="mobile-task-priority" class="form-control">
            <option value="">Select Priority</option>
            <option value="1">1 - Highest</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5 - Medium</option>
            <option value="6">6</option>
            <option value="7">7</option>
            <option value="8">8</option>
            <option value="9">9</option>
            <option value="10">10 - Lowest</option>
          </select>
        </div>
        <div class="form-group">
          <label><?= translate('time') ?></label>
          <div id="mobile-event-time"></div>
        </div>
        <div class="form-group">
          <label><?= translate('start_time') ?></label>
          <input type="datetime-local" id="mobile-custom-start-time" class="form-control">
        </div>
        <div class="form-group">
          <label><?= translate('end_time') ?></label>
          <input type="datetime-local" id="mobile-custom-end-time" class="form-control">
        </div>
        <div class="form-group">
          <label><?= translate('comments') ?></label>
          <div id="mobile-event-comments-list" style="max-height: 150px; overflow-y: auto; border: 1px solid #ddd; padding: 8px; margin-bottom: 8px; background: #f9f9f9;"></div>
          <div class="mention-container" style="position: relative;">
            <textarea id="mobile-event-comment" class="form-control" rows="2" placeholder="<?= translate('add_comment') ?>... (Type @ to mention someone)"></textarea>
            <div id="mobileMentionDropdown" class="mention-dropdown" style="display: none;"></div>
          </div>
          <button type="button" class="btn btn-sm btn-primary" id="mobile-add-comment-btn" style="margin-top: 5px;"><?= translate('add_comment') ?></button>
        </div>
        
        <!-- Mobile SOP Checklist Section -->
        <div id="mobile-sop-checklist-section" style="display: none;">
          <div class="form-group">
            <label><?= translate('sop_checklist') ?>:</label>
            <div class="clearfix"></div>
            <div id="mobile-checklist-progress-bar" style="margin-bottom: 8px; display: none;">
              <div class="progress" style="height: 16px; margin-bottom: 4px;">
                <div id="mobile-progress-bar" class="progress-bar" role="progressbar" style="width: 0%; background-color: #d9534f;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                  <span id="mobile-progress-text" style="font-size: 11px;">0%</span>
                </div>
              </div>
              <small id="mobile-completion-status" class="text-muted" style="font-size: 10px;">Complete at least 80% to mark task as completed</small>
            </div>
            <div id="mobile-sop-checklist-items" style="max-height: 150px; overflow-y: auto; border: 1px solid #ddd; padding: 8px; background: #f9f9f9; font-size: 12px;"></div>
          </div>
        </div>
        
        <div class="form-group">
          <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="mobile-event-status">
            <label class="custom-control-label" for="mobile-event-status"><?= translate('completed') ?></label>
          </div>
        </div>
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" id="mobile-update-timeline">
          <i class="fas fa-save"></i> <?= translate('update') ?>
        </button>
        <button type="button" class="btn btn-danger" id="mobile-delete-event">
          <i class="fas fa-trash"></i> <?= translate('delete') ?>
        </button>
      </div>
    </div>
  </div>
</div>

<style>
.mobile-view {
  min-height: 400px;
}

.mobile-today-header {
  text-align: center;
  padding: 15px 0;
  border-bottom: 1px solid #eee;
  margin-bottom: 15px;
  background: #e3f2fd;
  border-radius: 8px;
}

.mobile-today-card {
  background: white;
  border: 2px solid #2196f3;
  border-radius: 12px;
  margin-bottom: 20px;
  overflow: hidden;
}

.mobile-today-number {
  background: #2196f3;
  color: white;
  text-align: center;
  padding: 20px;
  font-size: 48px;
  font-weight: bold;
}

.mobile-today-events {
  min-height: 300px;
  padding: 15px;
}

.mobile-event-item {
  background: #2196f3;
  color: white;
  padding: 8px 12px;
  margin: 4px 0;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
}

.mobile-event-item.completed {
  background: #4caf50;
  text-decoration: line-through;
}

.mobile-event-item.high { background: #f44336; }
.mobile-event-item.medium { background: #ff9800; }
.mobile-event-item.low { background: #4caf50; }

.mobile-search-bar {
  margin-bottom: 15px;
}

.mobile-task-list {
  max-height: 500px;
  overflow-y: auto;
}

.mobile-task-section {
  margin-bottom: 20px;
}

.mobile-task-section-title {
  background: #f8f9fa;
  padding: 10px;
  font-weight: bold;
  border-radius: 5px;
  margin-bottom: 10px;
}

.mobile-task-item {
  display: flex;
  align-items: center;
  padding: 12px;
  border: 1px solid #eee;
  border-radius: 5px;
  margin-bottom: 8px;
  background: white;
  cursor: grab;
  touch-action: none;
  user-select: none;
  transition: all 0.2s ease;
}

.mobile-task-item:active {
  background: #f0f0f0;
  cursor: grabbing;
}

.mobile-task-item.dragging {
  opacity: 0.5;
  transform: scale(0.95);
  z-index: 1000;
  position: relative;
}

.mobile-drop-zone {
  display: none;
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  background: #007bff;
  color: white;
  padding: 15px 25px;
  border-radius: 25px;
  box-shadow: 0 4px 12px rgba(0,123,255,0.3);
  z-index: 1001;
  text-align: center;
  min-width: 200px;
}

.mobile-drop-zone.active {
  display: block;
  animation: pulse 1s infinite;
}

@keyframes pulse {
  0% { transform: translateX(-50%) scale(1); }
  50% { transform: translateX(-50%) scale(1.05); }
  100% { transform: translateX(-50%) scale(1); }
}

.mobile-today-card.drop-target {
  border-color: #4caf50 !important;
  background: #e8f5e8 !important;
}

.mobile-today-card.drop-target .mobile-today-number {
  background: #4caf50 !important;
}

.mobile-task-item.is-completed {
  opacity: 0.6;
  text-decoration: line-through;
}

.mobile-task-id {
  font-weight: bold;
  margin-right: 10px;
  min-width: 60px;
  font-size: 12px;
}

.mobile-task-title {
  flex: 1;
  font-size: 14px;
}

.mobile-task-time {
  font-size: 12px;
  color: #666;
  margin-left: 10px;
}

.mobile-task-id.text-danger { color: #dc3545 !important; }
.mobile-task-id.text-warning { color: #ffc107 !important; }
.mobile-task-id.text-success { color: #28a745 !important; }

/* Mention system styles for mobile */
.mention-container {
  position: relative;
}

.mention-dropdown {
  position: absolute;
  bottom: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #ddd;
  border-radius: 4px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  max-height: 150px;
  overflow-y: auto;
  z-index: 1000;
}

.mention-item {
  display: flex;
  align-items: center;
  padding: 6px 10px;
  cursor: pointer;
  border-bottom: 1px solid #f0f0f0;
  font-size: 12px;
}

.mention-item:hover,
.mention-item.selected {
  background: #f0f8ff;
}

.mention-item:last-child {
  border-bottom: none;
}

.mention-avatar {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  margin-right: 6px;
  object-fit: cover;
}

.mention-name {
  font-size: 12px;
  color: #333;
}

.mentioned-user {
  background: #e3f2fd;
  color: #1976d2;
  padding: 1px 3px;
  border-radius: 2px;
  font-weight: 500;
  font-size: 11px;
}

@media (max-width: 576px) {
  .mobile-task-item {
    padding: 10px;
  }
  
  .mobile-task-title {
    font-size: 13px;
  }
}
</style>

<script>
$(document).ready(function() {
  let allTasks = [];
  let allEvents = [];
  let currentView = 'calendar';

  // View toggle
  $('#mobile-view-toggle').on('click', function() {
    if (currentView === 'calendar') {
      $('#mobile-calendar-view').hide();
      $('#mobile-task-view').show();
      $(this).html('<i class="fas fa-calendar"></i> <?= translate("calendar") ?>');
      currentView = 'tasks';
      loadTasks();
    } else {
      $('#mobile-task-view').hide();
      $('#mobile-calendar-view').show();
      $(this).html('<i class="fas fa-list"></i> <?= translate("tasks") ?>');
      currentView = 'calendar';
      renderTodayView();
    }
  });

  function renderTodayView() {
    const today = new Date();
    $('#mobile-today-date').text(today.toLocaleDateString('en-US', { 
      weekday: 'long',
      year: 'numeric',
      month: 'long', 
      day: 'numeric'
    }));
    
    $('.mobile-today-number').text(today.getDate());
    loadTodayEvents();
  }

  function loadTodayEvents() {
    const today = new Date();
    const todayStr = today.toISOString().split('T')[0];
    const tomorrow = new Date(today);
    tomorrow.setDate(today.getDate() + 1);
    const tomorrowStr = tomorrow.toISOString().split('T')[0];

    $.ajax({
      url: '<?= base_url("planner/get_events") ?>',
      data: {
        start: todayStr,
        end: tomorrowStr
      },
      dataType: 'json'
    })
    .done(function(events) {
      // Filter events between 9 AM to 9 PM
      const filteredEvents = events.filter(event => {
        const eventStart = new Date(event.start);
        const hour = eventStart.getHours();
        return hour >= 9 && hour < 21;
      });
      
      // Sort events by start time
      filteredEvents.sort((a, b) => new Date(a.start) - new Date(b.start));
      
      allEvents = filteredEvents;
      
      let html = '';
      filteredEvents.forEach(event => {
        const statusClass = event.extendedProps.status === 1 ? ' completed' : '';
        const priorityClass = event.extendedProps.priority || 'low';
        const startTime = new Date(event.start).toLocaleTimeString('en-US', { 
          hour: 'numeric', 
          minute: '2-digit',
          hour12: true 
        });
        
        html += `
          <div class="mobile-event-item ${priorityClass}${statusClass}" data-event-id="${event.id}">
            <strong>${startTime}</strong> - ${event.title}
          </div>`;
      });
      
      if (html === '') {
        html = '<div class="text-muted text-center">No events scheduled for today</div>';
      }
      
      $('#mobile-today-events').html(html);
    }).fail(function() {
      $('#mobile-today-events').html('<div class="text-muted text-center">Error loading events</div>');
    });
  }

  function loadEvents() {
    loadTodayEvents();
  }

  function loadTasks() {
    $('#mobile-task-list').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
    
    $.ajax({
      url: '<?= base_url("planner/get_issues") ?>',
      dataType: 'json'
    }).done(function(tasks) {
      allTasks = tasks;
      renderTasks(tasks);
    });
  }

  function renderTasks(tasks, searchTerm = '') {
    if (searchTerm) {
      tasks = tasks.filter(t => 
        t.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
        t.unique_id.toLowerCase().includes(searchTerm.toLowerCase())
      );
    }

    const buckets = {};
    tasks.forEach(t => {
      const status = normalizeStatus(t.status);
      if (!buckets[status]) buckets[status] = [];
      buckets[status].push(t);
    });

    let html = '';
    // Define the desired order: todo, hold, in_progress, others, completed
    const statusOrder = ['todo', 'in_progress', 'hold'];
    const allKeys = Object.keys(buckets);
    const sortedKeys = [];
    
    // Add priority statuses first
    statusOrder.forEach(status => {
      if (allKeys.includes(status)) {
        sortedKeys.push(status);
      }
    });
    
    // Add other statuses (except completed)
    allKeys.forEach(key => {
      if (!statusOrder.includes(key) && key !== 'completed') {
        sortedKeys.push(key);
      }
    });
    
    // Add completed last
    if (allKeys.includes('completed')) {
      sortedKeys.push('completed');
    }

    sortedKeys.forEach(statusKey => {
      const list = buckets[statusKey] || [];
      const label = statusKey.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
      
      html += `<div class="mobile-task-section">`;
      html += `<div class="mobile-task-section-title">${label} (${list.length})</div>`;
      
      list.forEach(t => {
        let priorityClass = '';
        if (t.priority === 'high') priorityClass = 'text-danger';
        if (t.priority === 'medium') priorityClass = 'text-warning';
        if (t.priority === 'low') priorityClass = 'text-success';
        
        const completedClass = statusKey === 'completed' ? ' is-completed' : '';
        
        html += `
          <div class="mobile-task-item${completedClass}" 
               data-issue-id="${t.id}"
               data-duration="${t.estimated_time || 1}"
               data-priority="${t.priority || ''}"
               data-description="${(t.description || '').replace(/"/g,'&quot;')}">
            <span class="mobile-task-id ${priorityClass}">${t.unique_id}</span>
            <span class="mobile-task-title">${t.title}</span>
            <span class="mobile-task-time">${t.estimated_time || '?'}h</span>
          </div>`;
      });
      
      html += `</div>`;
    });

    $('#mobile-task-list').html(html);
  }

  function normalizeStatus(s) {
    if (!s) return 'pending';
    return String(s).toLowerCase().trim().replace(/[^a-z0-9]/g, '_');
  }

  // Task search
  $('#mobile-task-search').on('input', function() {
    const searchTerm = $(this).val();
    renderTasks(allTasks, searchTerm);
  });

  // Event click handler
  $(document).on('click', '.mobile-event-item', function(e) {
    e.stopPropagation();
    e.preventDefault();
    const eventId = $(this).data('event-id');
    const event = allEvents.find(e => e.id == eventId);
    if (event) {
      showEventModal(event);
    }
  });

  // Drag and Drop Implementation
  let draggedTask = null;
  let isDragging = false;
  let dragTimeout = null;
  let isCreatingEvent = false;
  let hasMoved = false;

  $(document).on('touchstart mousedown', '.mobile-task-item:not(.is-completed)', function(e) {
    e.preventDefault();
    const $task = $(this);
    draggedTask = $task;
    isDragging = false;
    hasMoved = false;
    
    dragTimeout = setTimeout(() => {
      if (draggedTask && !hasMoved) {
        isDragging = true;
        draggedTask.addClass('dragging');
        $('#mobile-drop-zone').addClass('active');
        $('.mobile-today-card').addClass('drop-target');
      }
    }, 500);
  });

  $(document).on('touchmove mousemove', function(e) {
    hasMoved = true;
    if (isDragging) {
      e.preventDefault();
    }
  });

  $(document).on('touchend mouseup', function(e) {
    if (dragTimeout) {
      clearTimeout(dragTimeout);
      dragTimeout = null;
    }
    
    if (!draggedTask || isCreatingEvent) {
      resetDragState();
      return;
    }
    
    if (isDragging) {
      const touch = e.originalEvent.changedTouches ? e.originalEvent.changedTouches[0] : e.originalEvent;
      const elementBelow = document.elementFromPoint(touch.clientX, touch.clientY);
      const todayCard = $(elementBelow).closest('.mobile-today-card');
      const dropZone = $(elementBelow).closest('#mobile-drop-zone');
      
      if (todayCard.length > 0 || dropZone.length > 0) {
        createEventForTask(draggedTask);
      }
    } else if (!hasMoved) {
      // Quick tap without movement
      createEventForTask(draggedTask);
    }
    
    resetDragState();
  });

  function resetDragState() {
    if (draggedTask) {
      draggedTask.removeClass('dragging');
    }
    $('#mobile-drop-zone').removeClass('active');
    $('.mobile-today-card').removeClass('drop-target');
    draggedTask = null;
    isDragging = false;
    hasMoved = false;
  }

  function createEventForTask($task) {
    if (isCreatingEvent) return;
    isCreatingEvent = true;
    
    const issueId = $task.data('issue-id');
    const now = new Date();
    
    // Use current time as start time
    const startTime = new Date(now.getFullYear(), now.getMonth(), now.getDate(), now.getHours(), now.getMinutes(), 0, 0);
    
    $.post('<?= base_url("planner/create_event") ?>', {
      issue_id: issueId,
      start_time: startTime.getFullYear() + '-' + 
                  String(startTime.getMonth() + 1).padStart(2, '0') + '-' + 
                  String(startTime.getDate()).padStart(2, '0') + ' ' + 
                  String(startTime.getHours()).padStart(2, '0') + ':' + 
                  String(startTime.getMinutes()).padStart(2, '0') + ':00'
    }, function(resp) {
      if (resp && resp.status === 'success') {
        loadEvents();
        loadTasks();
      }
      isCreatingEvent = false;
    }, 'json').fail(function() {
      isCreatingEvent = false;
    });
  }

  function showEventModal(event) {
    $('#mobile-event-title').text(event.title);
    $('#mobile-event-priority').text(event.extendedProps.priority || 'Normal')
      .removeClass('badge-danger badge-warning badge-success')
      .addClass(event.extendedProps.priority === 'high' ? 'badge-danger' : 
                event.extendedProps.priority === 'medium' ? 'badge-warning' : 'badge-success');
    
    // Populate priority dropdown
    const priorityValue = event.extendedProps.priorityTask ? String(event.extendedProps.priorityTask) : '';
    const $mobilePrioritySelect = $('#mobile-task-priority');
    
    // Check if the priority value exists in dropdown options (excluding empty option)
    const validOptions = $mobilePrioritySelect.find('option[value!=""]').map(function() { return String($(this).val()); }).get();
    
    if (priorityValue && validOptions.includes(priorityValue)) {
      $mobilePrioritySelect.val(priorityValue);
    } else {
      // If no value or invalid value, show 'Select Priority'
      $mobilePrioritySelect.val('');
    }
    
    const startStr = new Date(event.start).toLocaleString();
    const endStr = event.end ? new Date(event.end).toLocaleString() : '';
    $('#mobile-event-time').text(startStr + (endStr ? ' - ' + endStr : ''));
    
    $('#mobile-event-description').html(event.extendedProps.description || '<?= translate("no_description") ?>');
    $('#mobile-event-status').prop('checked', parseInt(event.extendedProps.status || 0, 10) === 1);
    
    if (event.start) {
      $('#mobile-custom-start-time').val(new Date(event.start).toISOString().slice(0, 16));
    }
    if (event.end) {
      $('#mobile-custom-end-time').val(new Date(event.end).toISOString().slice(0, 16));
    }
    
    // Load comments
    loadMobileComments(event.extendedProps.issueId);
    
    // Load SOP checklist
    loadMobileSopChecklist(event.extendedProps.issueId);
    
    $('#mobileEventModal').data('event', event).modal('show');
  }

  /* ===== Mobile Priority Change Handler ===== */
  $('#mobile-task-priority').on('change', function() {
    const event = $('#mobileEventModal').data('event');
    const task_priority = $(this).val();
    
    // Don't update if no priority selected
    if (!task_priority) {
      return;
    }
    
    $.post('<?= base_url("planner/update_priority") ?>', {
      issue_id: event.extendedProps.issueId,
      task_priority: task_priority
    }, function(resp) {
      if (resp && resp.status === 'success') {
        event.extendedProps.priorityTask = task_priority;
        loadTasks(); // Refresh task list to reflect task priority change
        
        // Show mobile toast message
        showMobileToast('Priority updated successfully!', 'success');
      } else {
        alert('<?= translate("update_failed") ?>');
      }
    }, 'json');
  });
  
  // Mobile toast notification function
  function showMobileToast(message, type = 'info') {
    const toastId = 'mobile-toast-' + Date.now();
    const bgColor = type === 'success' ? '#5cb85c' : type === 'error' ? '#d9534f' : '#5bc0de';
    
    const toast = $(`
      <div id="${toastId}" style="
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: ${bgColor};
        color: white;
        padding: 10px 16px;
        border-radius: 4px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
        font-size: 13px;
        opacity: 0;
        transition: all 0.3s ease;
        max-width: 90%;
        text-align: center;
      ">${message}</div>
    `);
    
    $('body').append(toast);
    
    // Animate in
    setTimeout(() => {
      toast.css({ opacity: 1 });
    }, 10);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
      toast.css({ opacity: 0 });
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  // Modal event handlers
  $('#mobile-update-timeline').on('click', function() {
    const event = $('#mobileEventModal').data('event');
    const startTime = $('#mobile-custom-start-time').val();
    const endTime = $('#mobile-custom-end-time').val();
    
    if (!startTime) {
      alert('<?= translate("please_select_start_time") ?>');
      return;
    }
    
    $.post('<?= base_url("planner/update_event") ?>', {
      event_id: event.id,
      start_time: startTime,
      end_time: endTime || null
    }, function(resp) {
      if (resp && resp.status === 'success') {
        loadEvents();
        $('#mobileEventModal').modal('hide');
      }
    }, 'json');
  });

  $('#mobile-event-status').on('change', function() {
    const event = $('#mobileEventModal').data('event');
    const status = $(this).is(':checked') ? 1 : 0;

    $.post('<?= base_url("planner/update_status") ?>', {
      event_id: event.id,
      issue_id: event.extendedProps.issueId,
      status: status
    }, function(resp) {
      if (resp && resp.status === 'success') {
        loadEvents();
        loadTasks();
        $('#mobileEventModal').modal('hide');
        if (typeof window.triggerTopbarUpdate === 'function') {
          window.triggerTopbarUpdate();
        }
      } else {
        $('#mobile-event-status').prop('checked', !status);
      }
    }, 'json');
  });

  $('#mobile-delete-event').on('click', function() {
    if (!confirm('<?= translate("confirm_delete") ?>')) return;
    
    const event = $('#mobileEventModal').data('event');
    $.post('<?= base_url("planner/delete_event") ?>', { 
      event_id: event.id 
    }, function(resp) {
      if (resp && resp.status === 'success') {
        loadEvents();
        loadTasks();
        $('#mobileEventModal').modal('hide');
      }
    }, 'json');
  });

  // Initialize mobile mention system
  let mobileMentionSystem;
  
  function initializeMobileMentionSystem() {
    if (typeof MentionSystem !== 'undefined') {
      mobileMentionSystem = new MentionSystem('mobile-event-comment', 'mobileMentionDropdown', '<?= base_url() ?>');
    }
  }

  // Process mentions in comment text for display
  function processMobileMentions(text) {
    if (typeof MentionSystem !== 'undefined') {
      return MentionSystem.processMentions(text);
    }
    return text;
  }

  // Mobile SOP Checklist functions
  let mobileHasSopChecklist = false;
  
  function loadMobileSopChecklist(issueId) {
    $('#mobile-sop-checklist-section').hide();
    $('#mobile-sop-checklist-items').html('<i class="fas fa-spinner fa-spin"></i> Loading...');
    
    $.get('<?= base_url("planner/get_sop_checklist") ?>', { issue_id: issueId }, function(response) {
      if (response && response.status === 'success') {
        if (response.executor_checklist && response.executor_checklist.length > 0) {
          mobileHasSopChecklist = true;
          let html = '';
          if (response.sop_titles && response.sop_titles.length > 0) {
            html += '<div style="margin-bottom: 8px; padding: 6px; background: #e8f4fd; border-radius: 3px; font-size: 11px;">';
            html += '<strong>SOP(s):</strong> ' + response.sop_titles.join(', ');
            html += '</div>';
          }
          response.executor_checklist.forEach((item, index) => {
            const isChecked = response.executor_completed.includes(item);
            html += `<div style="margin-bottom: 6px;">`;
            html += `<label style="font-size: 11px; display: flex; align-items: center;">`;
            html += `<input type="checkbox" class="mobile-executor-checkbox" value="${item}" ${isChecked ? 'checked' : ''} style="margin-right: 6px;">`;
            html += `${item}</label></div>`;
          });
          html += '<button type="button" class="btn btn-xs btn-success" id="mobile-save-sop-checklist" style="margin-top: 5px;">Save</button>';
          $('#mobile-sop-checklist-items').html(html);
          $('#mobile-checklist-progress-bar').show();
          $('#mobile-view-sop-btn').show();
          $('#mobile-sop-checklist-section').show();
          
          updateMobileCompletionStatus();
          $(document).on('change', '.mobile-executor-checkbox', updateMobileCompletionStatus);
        } else {
          mobileHasSopChecklist = false;
          $('#mobile-checklist-progress-bar').hide();
          $('#mobile-view-sop-btn').hide();
          $('#mobile-event-status').prop('disabled', false);
        }
      } else {
        mobileHasSopChecklist = false;
        $('#mobile-checklist-progress-bar').hide();
        $('#mobile-view-sop-btn').hide();
        $('#mobile-event-status').prop('disabled', false);
      }
    }, 'json').fail(function() {
      mobileHasSopChecklist = false;
      $('#mobile-checklist-progress-bar').hide();
      $('#mobile-view-sop-btn').hide();
      $('#mobile-event-status').prop('disabled', false);
    });
  }
  
  // View SOP details for mobile
  $(document).on('click', '#mobile-view-sop-btn', function() {
    const event = $('#mobileEventModal').data('event');
    if (!event || !event.extendedProps.issueId) return;
    
    $.ajax({
      url: '<?= base_url("planner/getDetailedSop") ?>',
      type: 'POST',
      data: {'id': event.extendedProps.issueId},
      dataType: 'html',
      success: function(data) {
        $('#mobileSopViewModal').remove();
        
        const sopModal = $(`
          <div class="modal fade" id="mobileSopViewModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
              <div class="modal-content">
                ${data}
              </div>
            </div>
          </div>
        `);
        
        $('body').append(sopModal);
        $('#mobileSopViewModal').modal({backdrop: 'static', keyboard: false});
        
        $('#mobileSopViewModal').on('hidden.bs.modal', function() {
          $(this).remove();
          $('body').addClass('modal-open');
          if (!$('.modal-backdrop').length) {
            $('body').append('<div class="modal-backdrop fade in"></div>');
          }
        });
      },
      error: function() {
        alert('<?= translate("error_loading_sop") ?>');
      }
    });
  });
  
  function updateMobileCompletionStatus() {
    if (!mobileHasSopChecklist) return;
    
    const totalItems = $('.mobile-executor-checkbox').length;
    const checkedItems = $('.mobile-executor-checkbox:checked').length;
    const completionPercentage = totalItems > 0 ? Math.round((checkedItems / totalItems) * 100) : 0;
    
    // Update progress bar
    const progressBar = $('#mobile-progress-bar');
    const progressText = $('#mobile-progress-text');
    const completionStatus = $('#mobile-completion-status');
    
    progressBar.css('width', completionPercentage + '%').attr('aria-valuenow', completionPercentage);
    progressText.text(completionPercentage + '%');
    
    // Update progress bar color based on completion
    if (completionPercentage >= 100) {
      progressBar.css('background-color', '#5cb85c'); // Green for 100%
      completionStatus.text('Checklist completed! ✓').removeClass('text-muted text-warning').addClass('text-success');
    } else if (completionPercentage >= 80) {
      progressBar.css('background-color', '#f0ad4e'); // Orange for 80-99%
      completionStatus.text('Ready to complete task (≥80% completed)').removeClass('text-muted text-success').addClass('text-warning');
    } else {
      progressBar.css('background-color', '#d9534f'); // Red for <80%
      completionStatus.text(`Complete at least 80% to mark task as completed (Currently: ${completionPercentage}%)`).removeClass('text-warning text-success').addClass('text-muted');
    }
    
    // Enable/disable task completion based on 80% rule
    if (completionPercentage >= 80) {
      $('#mobile-event-status').prop('disabled', false);
    } else {
      $('#mobile-event-status').prop('disabled', true).prop('checked', false);
    }
  }
  
  $(document).on('click', '#mobile-save-sop-checklist', function() {
    const event = $('#mobileEventModal').data('event');
    const completedItems = [];
    
    $('.mobile-executor-checkbox:checked').each(function() {
      completedItems.push($(this).val());
    });
    
    $.post('<?= base_url("planner/save_executor_checklist") ?>', {
      issue_id: event.extendedProps.issueId,
      completed_items: completedItems
    }, function(response) {
      if (response && response.status === 'success') {
        updateMobileCompletionStatus();
        const btn = $('#mobile-save-sop-checklist');
        btn.text('Saved!').removeClass('btn-success').addClass('btn-info');
        setTimeout(() => {
          btn.text('Save').removeClass('btn-info').addClass('btn-success');
        }, 1500);
      } else {
        alert('Failed to save checklist');
      }
    }, 'json').fail(function() {
      alert('Error saving checklist');
    });
  });

  // Mobile comment functions
  function loadMobileComments(issueId) {
    $('#mobile-event-comments-list').html('<i class="fas fa-spinner fa-spin"></i> Loading...');
    $.get('<?= base_url("planner/get_comments") ?>', { issue_id: issueId }, function(comments) {
      let html = '';
      const currentUserId = <?= get_loggedin_user_id() ?>;
      if (comments && comments.length > 0) {
        comments.forEach(c => {
          const date = new Date(c.created_at).toLocaleString();
          html += `<div class="mobile-comment-item" style="margin-bottom: 12px; padding: 8px; border-left: 3px solid #007bff; font-size: 12px; background: #f8f9fa; border-radius: 4px;" data-comment-id="${c.id}">`;
          html += `<div style="display: flex; justify-content: space-between; align-items: center;">`;
          html += `<div><small><strong>${c.user_name || 'User'}</strong> - ${date}</small></div>`;
          if (parseInt(c.author_id) === currentUserId) {
            html += `<div>`;
            html += `<button class="btn btn-xs btn-warning mobile-edit-comment-btn" data-comment-id="${c.id}" style="margin-right: 3px; font-size: 10px;">Edit</button>`;
            html += `<button class="btn btn-xs btn-danger mobile-delete-comment-btn" data-comment-id="${c.id}" style="font-size: 10px;">Del</button>`;
            html += `</div>`;
          }
          html += `</div>`;
          html += `<div class="mobile-comment-text" style="margin-top: 4px;">${processMobileMentions(c.comment_text.replace(/\n/g, '<br>'))}</div>`;
          html += `<textarea class="form-control mobile-edit-comment-text" style="display: none; margin-top: 5px; font-size: 12px;">${c.comment_text}</textarea>`;
          html += `<div class="mobile-edit-comment-actions" style="display: none; margin-top: 5px;">`;
          html += `<button class="btn btn-xs btn-success mobile-save-comment-btn" data-comment-id="${c.id}" style="font-size: 10px;">Save</button> `;
          html += `<button class="btn btn-xs btn-default mobile-cancel-edit-btn" style="font-size: 10px;">Cancel</button>`;
          html += `</div>`;
          html += `</div>`;
        });
      } else {
        html = '<div class="text-muted">No comments yet</div>';
      }
      $('#mobile-event-comments-list').html(html);
    }, 'json');
  }

  $('#mobile-add-comment-btn').on('click', function() {
    const event = $('#mobileEventModal').data('event');
    const comment = $('#mobile-event-comment').val().trim();
    if (!comment) return;
    
    $.post('<?= base_url("planner/add_comment") ?>', {
      issue_id: event.extendedProps.issueId,
      comment: comment
    }, function(resp) {
      if (resp && resp.status === 'success') {
        $('#mobile-event-comment').val('');
        loadMobileComments(event.extendedProps.issueId);
      }
    }, 'json');
  });

  // Mobile comment edit/delete handlers
  $(document).on('click', '.mobile-edit-comment-btn', function() {
    const commentId = $(this).data('comment-id');
    const commentItem = $(`.mobile-comment-item[data-comment-id="${commentId}"]`);
    const editTextarea = commentItem.find('.mobile-edit-comment-text');
    
    commentItem.find('.mobile-comment-text').hide();
    editTextarea.show();
    commentItem.find('.mobile-edit-comment-actions').show();
    $(this).parent().hide();
    
    // Initialize mention system for edit textarea
    if (typeof MentionSystem !== 'undefined' && !editTextarea.data('mention-initialized')) {
      const textareaId = 'mobile-edit-comment-' + commentId;
      const dropdownId = 'mobile-edit-mention-dropdown-' + commentId;
      
      editTextarea.attr('id', textareaId);
      
      // Create dropdown container if it doesn't exist
      if (!commentItem.find('.mobile-edit-mention-dropdown').length) {
        const container = $('<div class="mention-container" style="position: relative;"></div>');
        editTextarea.wrap(container);
        editTextarea.after(`<div id="${dropdownId}" class="mention-dropdown mobile-edit-mention-dropdown" style="display: none; position: absolute; bottom: 100%; left: 0; right: 0;"></div>`);
      }
      
      new MentionSystem(textareaId, dropdownId, '<?= base_url() ?>');
      editTextarea.data('mention-initialized', true);
    }
    
    editTextarea.focus();
  });
  
  $(document).on('click', '.mobile-cancel-edit-btn', function() {
    const commentItem = $(this).closest('.mobile-comment-item');
    commentItem.find('.mobile-comment-text').show();
    commentItem.find('.mobile-edit-comment-text').hide();
    commentItem.find('.mobile-edit-comment-actions').hide();
    commentItem.find('.mobile-edit-comment-btn').parent().show();
  });
  
  $(document).on('click', '.mobile-save-comment-btn', function() {
    const commentId = $(this).data('comment-id');
    const newText = $(this).closest('.mobile-comment-item').find('.mobile-edit-comment-text').val();
    
    $.post('<?= base_url("planner/update_comment") ?>', {
      comment_id: commentId,
      comment_text: newText
    }, function(response) {
      if (response.status === 'success') {
        const event = $('#mobileEventModal').data('event');
        loadMobileComments(event.extendedProps.issueId);
      } else {
        alert('Failed to update comment');
      }
    }, 'json');
  });
  
  $(document).on('click', '.mobile-delete-comment-btn', function() {
    if (!confirm('Delete this comment?')) return;
    
    const commentId = $(this).data('comment-id');
    $.post('<?= base_url("planner/delete_comment") ?>', {
      comment_id: commentId
    }, function(response) {
      if (response.status === 'success') {
        const event = $('#mobileEventModal').data('event');
        loadMobileComments(event.extendedProps.issueId);
      } else {
        alert('Failed to delete comment');
      }
    }, 'json');
  });

  // Initialize
  renderTodayView();
  
  // Initialize mobile mention system
  setTimeout(initializeMobileMentionSystem, 100);
});

// Load mention system script
$.getScript('<?= base_url("assets/js/mention-system.js") ?>', function() {
  // Reinitialize mention system after script loads
  if (typeof MentionSystem !== 'undefined') {
    initializeMobileMentionSystem();
  }
});
</script>