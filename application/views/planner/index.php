<!---Page view styles-->

<style>
/* Hidden scrollbar styles */
::-webkit-scrollbar {
	width: 0px;
	height: 0px;
	background: transparent;
}
* {
	scrollbar-width: none;
	-ms-overflow-style: none;
}

@media (min-width: 768px) {
    .modal-dialog {
        background: transparent;
		width: 60%;
		margin: 40px auto;
		position: relative;
    }
}
</style>

<div class="row" style="height: calc(108vh);">
    <div class="col-md-3">
        <?php $this->load->view('tracker/sidebar'); ?>
    </div>
    <div class="col-md-9" style="height: 100%; overflow-y: auto;">
<link rel="stylesheet" href="<?= base_url('assets/planner/css/main.min.css') ?>">


<style>
  #calendar { background:#fff; border-radius:4px; box-shadow:0 1px 3px rgba(0,0,0,0.1); min-height:700px; }
  .task-section-title{
    font-weight:600; font-size:12px; text-transform:uppercase;
    color:#666; margin:14px 0 6px;
  }
  .task-item.inline{
    display:flex; align-items:center; justify-content:space-between;
    background:#fff; border-radius:8px; padding:8px 12px; margin-bottom:8px;
    box-shadow:0 2px 6px rgba(0,0,0,0.05); font-size:14px; cursor:grab; transition:background .2s;
    position:relative;
  }
  .task-item.inline.main-task .task-title {
    font-style: italic;
    font-weight: bold;
  }
  .task-item.inline.main-task .task-id {
    font-weight: bold;
  }
  .task-tooltip {
    position: fixed;
    background: white;
    color: #333;
    padding: 12px 16px;
    border-radius: 6px;
    font-size: 13px;
    white-space: nowrap;
    z-index: 9999;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border: 1px solid #ddd;
    max-width: 300px;
    white-space: normal;
  }
  .task-tooltip:after {
    content: '';
    position: absolute;
    top: 100%;
    left: 20px;
    margin-left: -5px;
    border: 5px solid transparent;
    border-top-color: white;
  }
  .task-tooltip:before {
    content: '';
    position: absolute;
    top: 100%;
    left: 20px;
    margin-left: -6px;
    border: 6px solid transparent;
    border-top-color: #ddd;
  }
  .task-item.inline:hover .task-tooltip {
    opacity: 1;
  }
  .task-item.inline:hover{ background:#f9f9f9; }
  .task-id{ font-weight:600; font-size:13px; background:#eaf2ff; color:#2f6bd8; padding:3px 6px; border-radius:6px; margin-right:10px; white-space:nowrap; }
  .task-title{ flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; margin-right:10px; color:#333; }
  .task-time{ font-size:12px; color:#666; white-space:nowrap; }
  .text-danger{ color:#d9534f!important; } .text-warning{ color:#f0ad4e!important; } .text-success{ color:#5cb85c!important; }
  .completed-event{ opacity:.6; text-decoration:line-through; }
  /* .task-item.is-completed { cursor:not-allowed; opacity:.6; } */
  .task-item.is-completed .task-title,
  .task-item.is-completed .task-id { text-decoration: line-through; }
  .fc .fc-toolbar .fc-button{ background:darkslategrey; }
  
  /* Force toolbar to stay in one row with overflow handling */
  #calendar {
    overflow: hidden !important;
  }
  .fc-header-toolbar.fc-toolbar.fc-toolbar-ltr {
    display: flex !important;
    flex-wrap: nowrap !important;
    align-items: center !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    width: 100% !important;
    box-sizing: border-box !important;
  }
  .fc-toolbar-chunk {
    display: flex !important;
    flex-wrap: nowrap !important;
    align-items: center !important;
    min-width: 0 !important;
  }
  .fc-toolbar-chunk:nth-child(2) {
    flex: 1 !important;
    justify-content: center !important;
    overflow: hidden !important;
  }
  .fc .fc-button {
    padding: 2px 4px !important;
    font-size: 10px !important;
    margin: 0 1px !important;
    min-width: 0 !important;
    flex-shrink: 1 !important;
  }
  .fc .fc-toolbar-title {
    font-size: 14px !important;
    margin: 0 5px !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
  }
  
  
  :root{
	  --weekend-stripe: rgba(255, 193, 7, 0.22);  /* subtle amber */
	  --weekend-stripe2: rgba(255, 193, 7, 0.02);
	}

	/* Stripe the entire day column/cell */
	.fc-daygrid .bd-weekend-col .fc-daygrid-day-frame,
	.fc-timegrid .bd-weekend-col .fc-timegrid-col-frame {
	  background-image: repeating-linear-gradient(
		135deg,                         /* diagonal stripes */
		var(--weekend-stripe) 0px,
		var(--weekend-stripe) 5px,
		var(--weekend-stripe2) 5px,
		var(--weekend-stripe2) 10px
	  );
	}

	/* Make the header for Fri/Sat stand out too */
	.fc .bd-weekend-header {
	  background-image: repeating-linear-gradient(
		135deg,
		rgba(255,193,7,0.35) 0px,
		rgba(255,193,7,0.35) 4px,
		rgba(255,193,7,0.10) 4px,
		rgba(255,193,7,0.10) 8px
	  );
	}
.fc .bd-weekend-header .fc-col-header-cell-cushion { font-weight: 600; }


	/* Scrollable task list */
	#task-list{
	  max-height: 82vh;           /* adjust if you want more/less */
	  overflow-y: auto;
	  overflow-x: hidden;
	  padding-right: 6px;         /* space for scrollbar */
	  -webkit-overflow-scrolling: touch; /* smooth on iOS */
	  overscroll-behavior: contain;
	}

	/* Pretty, unobtrusive scrollbar */
	#task-list::-webkit-scrollbar{ width: 8px; }
	#task-list::-webkit-scrollbar-track{ background: transparent; }
	#task-list::-webkit-scrollbar-thumb{
	  background: rgba(0,0,0,.15);
	  border-radius: 6px;
	}
	#task-list:hover::-webkit-scrollbar-thumb{ background: rgba(0,0,0,.28); }

	/* Keep section headers visible while scrolling */
	.task-section-title{
	  position: sticky;
	  top: 0;
	  background: #fff;
	  z-index: 2;
	  padding-top: 6px;
	  padding-bottom: 6px;
	  border-bottom: 1px solid rgba(0,0,0,0.06);
	}

  /* Mention system styles */
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
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
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
  
  .mention-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    margin-right: 8px;
    object-fit: cover;
  }
  
  .mention-name {
    font-size: 14px;
    color: #333;
  }
  
  .mentioned-user {
    background: #e3f2fd;
    color: #1976d2;
    padding: 2px 4px;
    border-radius: 3px;
    font-weight: 500;
  }

  /* Checkbox styling similar to RDC */
  .checkbox-custom {
    position: relative;
    display: block;
    margin-top: 10px;
    margin-bottom: 10px;
  }
  
  .checkbox-custom input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    cursor: pointer;
  }
  
  .checkbox-custom label {
    position: relative;
    cursor: pointer;
    padding-left: 25px;
    margin-bottom: 0;
    font-weight: normal;
  }
  
  .checkbox-custom label:before {
    content: '';
    position: absolute;
    left: 0;
    top: 2px;
    width: 17px;
    height: 17px;
    border: 2px solid #ddd;
    border-radius: 3px;
    background: white;
  }
  
  .checkbox-custom input[type="checkbox"]:checked + label:before {
    background: #5cb85c;
    border-color: #5cb85c;
  }
  
  .checkbox-custom input[type="checkbox"]:checked + label:after {
    content: '\2713';
    position: absolute;
    left: 4px;
    top: 0;
    color: white;
    font-size: 12px;
    font-weight: bold;
  }
  
  .checkbox-custom.checkbox-success input[type="checkbox"]:checked + label:before {
    background: #5cb85c;
    border-color: #5cb85c;
  }

  /* Mobile responsive styles */
  @media (max-width: 768px) {
    .row .col-md-4, .row .col-md-8 {
      width: 100%;
      margin-bottom: 15px;
    }
    
    .panel-body {
      padding: 10px;
    }
    
    #calendar {
      min-height: 400px;
    }
    
    .fc .fc-button {
      padding: 4px 6px !important;
      font-size: 11px !important;
      margin: 0 2px !important;
    }
    
    .fc .fc-toolbar-title {
      font-size: 16px !important;
    }
    
    .task-item.inline {
      padding: 6px 8px;
      font-size: 12px;
    }
    
    .task-id {
      font-size: 11px;
      padding: 2px 4px;
    }
    
    .task-title {
      font-size: 12px;
    }
    
    .task-time {
      font-size: 10px;
    }
    
    .modal-dialog {
      margin: 10px;
      width: auto;
    }
    
    .mention-dropdown {
      max-height: 120px;
    }
    
    .mention-item {
      padding: 6px 8px;
      font-size: 12px;
    }
    
    .mention-avatar {
      width: 20px;
      height: 20px;
    }
  }

</style>

<div class="row">
  <div class="col-md-12">
    <div class="panel">
      <div class="panel-body">
        <div class="row">
          <div class="col-md-4">
            <div class="panel panel-primary">
              <div class="panel-heading">
                <h3 class="panel-title"><?= translate('my_tasks') ?></h3>
              </div>
              <div class="panel-body" style="min-height:600px;">
                <div class="form-group">
                  <input type="text" id="task-search" class="form-control" placeholder="<?= translate('search_tasks') ?>..." style="margin-bottom:10px;">
                </div>
                <div id="task-list">
                  <div class="text-center">
                    <i class="fas fa-spinner fa-spin"></i> <?= translate('loading') ?>...
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-8">
            <div id="calendar"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title" id="eventModalLabel"><?= translate('task_details') ?></h4>
      </div>
      <div class="modal-body">
        <h4 id="event-title"></h4>
		<button type="button" class="btn btn-sm btn-info pull-right" id="view-sop-btn" style="margin-top: -5px;">
              <i class="fas fa-eye"></i> <?= translate('view_sop') ?>
            </button>
        <p><strong><?= translate('time') ?>:</strong> <span id="event-time"></span></p>
        <p><strong><?= translate('priority') ?>:</strong> <span id="event-priority"></span></p>
        <p><strong><?= translate('description') ?>:</strong></p>
        <div id="event-description" class="well well-sm"></div>
        <div class="form-group">
          <label><?= translate('task_priority') ?>:</label>
          <select id="task-priority" class="form-control">
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
          <label><?= translate('custom_timeline') ?>:</label>
          <div class="row">
            <div class="col-md-6">
              <label><?= translate('start_time') ?>:</label>
              <input type="datetime-local" id="custom-start-time" class="form-control">
            </div>
            <div class="col-md-6">
              <label><?= translate('end_time') ?>:</label>
              <input type="datetime-local" id="custom-end-time" class="form-control">
            </div>
          </div>
        </div>
        <div class="form-group">
          <label><?= translate('comments') ?>:</label>
          <div id="event-comments-list" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; background: #f9f9f9;"></div>
          <div class="mention-container" style="position: relative;">
            <textarea id="event-comment" class="form-control" rows="2" placeholder="<?= translate('add_comment') ?>... (Type @ to mention someone)"></textarea>
            <div id="mentionDropdown" class="mention-dropdown" style="display: none;"></div>
          </div>
          <button type="button" class="btn btn-sm btn-primary" id="add-comment-btn" style="margin-top: 5px;"><?= translate('add_comment') ?></button>
        </div>
        
        <!-- Executor Checklist Section -->
        <div id="executor-checklist-section" style="display: none;">
          <div class="form-group">
            <label><?= translate('sop_checklist') ?>:</label>
            <div class="clearfix"></div>
            <div id="checklist-progress-bar" style="margin-bottom: 10px; display: none;">
              <div class="progress" style="height: 20px; margin-bottom: 5px;">
                <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 0%; background-color: #d9534f;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                  <span id="progress-text">0%</span>
                </div>
              </div>
              <small id="completion-status" class="text-muted">Complete at least 80% to mark task as completed</small>
            </div>
            <div id="executor-checklist-items" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;"></div>
          </div>
        </div>
        
        <div class="form-group">
          <div class="checkbox">
            <label><input type="checkbox" id="event-status"> <?= translate('mark_as_completed') ?></label>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-default" data-dismiss="modal"><?= translate('close') ?></button>
        <button class="btn btn-primary" id="update-timeline"><?= translate('update_timeline') ?></button>
        <button class="btn btn-danger" id="delete-event"><?= translate('delete') ?></button>
      </div>
    </div>
  </div>
</div>

<script src="<?= base_url('assets/planner/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= base_url('assets/planner/js/main.min.js') ?>"></script>
<script src="<?= base_url('assets/planner/js/interaction.min.js') ?>"></script>
<script src="<?= base_url('assets/planner/js/locales-all.min.js') ?>"></script>
<script src="<?= base_url('assets/js/mention-system.js') ?>"></script>

<script>
$(function() {
  if (typeof FullCalendar === 'undefined') { console.error('FullCalendar is not loaded!'); return; }

  /* ===== CSRF: attach token to all POSTs (CI3) ===== */
  function getCookie(name){ const m=document.cookie.match(new RegExp('(^| )'+name+'=([^;]+)')); return m?decodeURIComponent(m[2]):null; }
  var csrfCookieName = '<?= $this->config->item('csrf_cookie_name'); ?>';
  var csrfTokenName  = '<?= $this->security->get_csrf_token_name(); ?>';
  $.ajaxSetup({
    beforeSend: function(xhr, settings) {
      if (settings.type === 'POST') {
        const token = getCookie(csrfCookieName) || '';
        if (typeof settings.data === 'string') {
          settings.data += (settings.data ? '&' : '') + encodeURIComponent(csrfTokenName)+'='+encodeURIComponent(token);
        } else if (typeof settings.data === 'object') {
          settings.data = settings.data || {};
          settings.data[csrfTokenName] = token;
        }
      }
    }
  });

  /* ===== Calendar (Default 5-day view + header nav) ===== */
  const calendarEl = document.getElementById('calendar');

  const calendar = new FullCalendar.Calendar(calendarEl, {
    timeZone: 'Asia/Dhaka',
    locale: 'en',
    nowIndicator: true,

    // Default view = 3-day time grid starting from yesterday
    initialView: 'threeDay',
    initialDate: new Date(Date.now() - 24 * 60 * 60 * 1000), // Start from yesterday
    views: {
      threeDay: { type: 'timeGrid', duration: { days: 3 }, buttonText: '3 days' }
    },

    // Header toolbar with nav + quick month/week helpers
    headerToolbar: {
      left: 'prev today next',
      center: 'title',
      right: 'threeDay,timeGridWeek,dayGridMonth thisMonth,nextMonth'
    },
    customButtons: {
      thisMonth: {
        text: 'This Month',
        click: function() {
          calendar.changeView('dayGridMonth');
          calendar.gotoDate(new Date());
        }
      },
      nextMonth: {
        text: 'Next Month',
        click: function() {
          const d = new Date();
          d.setDate(1);
          d.setMonth(d.getMonth() + 1);
          calendar.changeView('dayGridMonth');
          calendar.gotoDate(d);
        }
      }
    },
	
	dayCellClassNames: function(arg) {
	  // 0=Sun, 1=Mon, ... 5=Fri, 6=Sat
	  const dow = arg.date.getDay();
	  if (dow === 5 || dow === 6) return ['bd-weekend-col'];
	  return [];
	},
	dayHeaderClassNames: function(arg) {
	  const dow = arg.date.getDay();
	  if (dow === 5 || dow === 6) return ['bd-weekend-header'];
	  return [];
	},

    editable: true,
    droppable: true,
    slotDuration: '00:30:00',
    slotMinTime: '09:00:00',
    slotMaxTime: '22:00:00',

    events: function(fetchInfo, success, failure) {
      $.ajax({
        url: '<?= base_url("planner/get_events") ?>',
        data: { start: fetchInfo.start.toISOString(), end: fetchInfo.end.toISOString() },
        dataType: 'json'
      }).done(success)
        .fail(function(_, __, err){ console.error('get_events fail:', err); failure(err); });
    },

    // Use startStr/endStr; for all-day (month) drops, default to 10:00
    eventReceive: function(info) {
      const ev = info.event;
      let startForServer = ev.startStr;
      if (ev.allDay || (ev.startStr && ev.startStr.length === 10)) {
        // YYYY-MM-DD -> add 10:00:00 local time
        startForServer = ev.startStr + 'T10:00:00';
        // also update the event object so it sits at 10:00 if you switch to time-grid
		//ev.setAllDay(false);
        ev.setStart(startForServer);
      }

      $.ajax({
        url: '<?= base_url("planner/create_event") ?>',
        method: 'POST',
        dataType: 'json',
        data: {
          issue_id: ev.extendedProps.issueId,
          start_time: startForServer
        }
      }).done(function(resp) {
        if (resp && resp.status === 'success') {
          ev.setProp('id', String(resp.event_id));
          loadTasks(); // reflect in left list (issue moves to In Progress)
          // optional: calendar.refetchEvents();
        } else {
          console.error('create_event error:', resp);
          info.revert();
        }
      }).fail(function(xhr) {
        console.error('create_event failed:', xhr.responseText);
        info.revert();
      });
    },

    eventDrop: function(info) {
      const ev = info.event;
      $.post('<?= base_url("planner/update_event") ?>', {
        event_id: ev.id,
        start_time: ev.startStr,
        end_time:   ev.endStr || null
      }, function(resp) {
        if (!resp || resp.status !== 'success') info.revert();
      }, 'json').fail(function(){ info.revert(); });
    },

    eventResize: function(info) {
      const ev = info.event;
      $.post('<?= base_url("planner/update_event") ?>', {
        event_id: ev.id,
        start_time: ev.startStr,
        end_time:   ev.endStr || null
      }, function(resp) {
        if (!resp || resp.status !== 'success') info.revert();
      }, 'json').fail(function(){ info.revert(); });
    },

    eventClick: function(info) {
      const ev = info.event;
      $('#event-title').text(ev.title);
      const startStr = ev.start ? ev.start.toLocaleString() : '';
      const endStr   = ev.end   ? ev.end.toLocaleString()   : '';
      $('#event-time').text(startStr + (endStr ? ' - ' + endStr : ''));
      $('#event-priority').text(ev.extendedProps.priority || '');
      $('#event-description').html(ev.extendedProps.description || '<?= translate("no_description") ?>');
      $('#event-status').prop('checked', parseInt(ev.extendedProps.status || 0, 10) === 1);
       
      // Populate priority dropdown
      const priorityValue = ev.extendedProps.priorityTask ? String(ev.extendedProps.priorityTask) : '';
      const $prioritySelect = $('#task-priority');
      
      // Check if the priority value exists in dropdown options (excluding empty option)
      const validOptions = $prioritySelect.find('option[value!=""]').map(function() { return String($(this).val()); }).get();
      
      if (priorityValue && validOptions.includes(priorityValue)) {
        $prioritySelect.val(priorityValue);
      } else {
        // If no value or invalid value, show 'Select Priority'
        $prioritySelect.val('');
      }
      
      // Populate custom timeline inputs (server time)
      if (ev.start) {
        const startISO = ev.start.toISOString().slice(0, 16);
        $('#custom-start-time').val(startISO);
      }
      if (ev.end) {
        const endISO = ev.end.toISOString().slice(0, 16);
        $('#custom-end-time').val(endISO);
      }
      
      // Load comments
      loadComments(ev.extendedProps.issueId);
      
      // Load executor checklist
      loadExecutorChecklist(ev.extendedProps.issueId);
      
      $('#eventModal').data('event', ev);
      $('#eventModal').modal('show');
    },

    eventDidMount: function(info) {
      if (parseInt(info.event.extendedProps.status || 0, 10) === 1) {
        info.el.classList.add('completed-event');
      }
    }
  });

  calendar.render();

  /* ===== Tasks: grouped buckets & external drag ===== */
  function normalizeStatus(s) {
    if (!s) return 'pending';
    return String(s).toLowerCase().trim().replace(/[^a-z0-9]/g, '_');
  }

  function taskRowHTML(t, isCompleted) {
    let priorityClass = '';
    if (t.priority === 'high')   priorityClass = 'text-danger';
    if (t.priority === 'medium') priorityClass = 'text-warning';
    if (t.priority === 'low')    priorityClass = 'text-success';
    const completedClass = isCompleted ? ' is-completed' : '';
    const isMainTask = !t.parent_issue;
    const taskTypeClass = isMainTask ? ' main-task' : ' sub-task';
    const safeDesc = (t.description || '').replace(/"/g,'&quot;');
    return `
      <div class="task-item inline${completedClass}${taskTypeClass}"
           data-issue-id="${t.id}"
           data-duration="${t.estimated_time || 1}"
           data-priority="${t.priority || ''}"
           data-description="${safeDesc}"
           onclick="openTaskDetails(${t.id})">
        <span class="task-id ${priorityClass}">${t.unique_id}</span>
        <span class="task-title ${priorityClass}">${t.title}</span>
        <span class="task-time">${t.estimated_time || '?'}h</span>
        <div class="task-tooltip">${t.title}</div>
      </div>`;
  }

  function draggableEventData(el) {
    const title = el.querySelector('.task-title')?.innerText || 'Task';
    const priority = el.getAttribute('data-priority') || 'low';
    const description = el.getAttribute('data-description') || '';
    const issueId = parseInt(el.getAttribute('data-issue-id'), 10) || null;
    return {
      title: title,
      duration: { hours: 0.5 },
      extendedProps: { priority, description, issueId, status: 0 }
    };
  }

  let allTasks = [];

  function renderBuckets(buckets, searchTerm = '') {
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
      let list = buckets[statusKey] || [];
      if (searchTerm) {
        list = list.filter(t => 
          t.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
          t.unique_id.toLowerCase().includes(searchTerm.toLowerCase()) ||
          (t.description && t.description.toLowerCase().includes(searchTerm.toLowerCase()))
        );
      }
      const label = statusKey.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
      html += `<div class="task-section">`;
      html += `<div class="task-section-title">${label} (${list.length})</div>`;
      html += `<div id="task-${statusKey}">`;
      list.forEach(t => { html += taskRowHTML(t, statusKey === 'completed'); });
      html += `</div></div>`;
    });
    $('#task-list').html(html);

    // Add tooltip functionality
    $(document).on('mouseenter', '.task-item', function(e) {
      const tooltip = $(this).find('.task-tooltip');
      tooltip.css({
        'position': 'fixed',
        'top': e.pageY - tooltip.outerHeight() - 10,
        'left': e.pageX - tooltip.outerWidth() / 2,
        'opacity': 1
      });
    }).on('mouseleave', '.task-item', function() {
      $(this).find('.task-tooltip').css('opacity', 0);
    });

    // Prevent drag when clicking on task items for details
    $(document).on('click', '.task-item', function(e) {
      e.stopPropagation();
    });

    // Make all non-completed sections draggable
    sortedKeys.forEach(statusKey => {
      if (statusKey !== 'completed') {
        const element = document.getElementById(`task-${statusKey}`);
        if (element) {
          new FullCalendar.Draggable(element, {
            itemSelector: '.task-item:not(.is-completed)',
            eventData: draggableEventData
          });
        }
      }
    });
  }

  function loadTasks() {
    $('#task-list').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> <?= translate('loading') ?>...</div>');
    $.ajax({
      url: '<?= base_url("planner/get_issues") ?>',
      dataType: 'json'
    }).done(function(tasks) {
      if (!Array.isArray(tasks) || tasks.length === 0) {
        $('#task-list').html('<div class="alert alert-info"><?= translate("no_tasks_assigned") ?></div>');
        return;
      }
      allTasks = tasks;
      const buckets = {};
      tasks.forEach(function(t) {
        const st = normalizeStatus(t.status);
        if (!buckets[st]) buckets[st] = [];
        buckets[st].push(t);
      });
      renderBuckets(buckets, $('#task-search').val());
    }).fail(function(_, __, err) {
      $('#task-list').html('<div class="alert alert-danger">Error loading tasks: '+ err +'</div>');
    });
  }

  // Search functionality
  $('#task-search').on('input', function() {
    const searchTerm = $(this).val();
    const buckets = {};
    allTasks.forEach(function(t) {
      const st = normalizeStatus(t.status);
      if (!buckets[st]) buckets[st] = [];
      buckets[st].push(t);
    });
    renderBuckets(buckets, searchTerm);
  });

  // Initialize mention system
  let mentionSystem;
  
  function initializeMentionSystem() {
    if (typeof MentionSystem !== 'undefined') {
      mentionSystem = new MentionSystem('event-comment', 'mentionDropdown', '<?= base_url() ?>');
    }
  }

  // Process mentions in comment text for display
  function processMentions(text) {
    if (typeof MentionSystem !== 'undefined') {
      return MentionSystem.processMentions(text);
    }
    return text;
  }

  // initial load
  loadTasks();
  
  // Initialize mention system after a short delay to ensure script is loaded
  setTimeout(initializeMentionSystem, 100);

  // Executor Checklist functions
  let hasExecutorChecklist = false;
  let currentIssueId = null;
  
  function loadExecutorChecklist(issueId) {
    currentIssueId = issueId;
    $('#executor-checklist-section').hide();
    $('#executor-checklist-items').html('<i class="fas fa-spinner fa-spin"></i> Loading checklist...');
    
    $.get('<?= base_url("planner/get_sop_checklist") ?>', { issue_id: issueId }, function(response) {
      if (response && response.status === 'success') {
        if (response.executor_checklist && response.executor_checklist.length > 0) {
          hasExecutorChecklist = true;
          let html = '';
          if (response.sop_titles && response.sop_titles.length > 0) {
            html += '<div style="margin-bottom: 10px; padding: 8px; background: #e8f4fd; border-radius: 4px;">';
            html += '<strong>SOP(s):</strong> ' + response.sop_titles.join(', ');
            html += '</div>';
          }
          html += '<ul style="list-style: none; padding-left: 0;">';
          response.executor_checklist.forEach((item, index) => {
            const isChecked = response.executor_completed.includes(item);
            html += `<li style="margin-bottom: 8px;">`;
            html += `<div class="checkbox-custom checkbox-success">`;
            html += `<input type="checkbox" class="executor-checkbox" id="exec_${index}" value="${item}" ${isChecked ? 'checked' : ''}>`;
            html += `<label for="exec_${index}" style="margin-left: 5px;">${item}</label>`;
            html += `</div></li>`;
          });
          html += '</ul>';
          html += '<button type="button" class="btn btn-sm btn-success" id="save-executor-checklist">Save Progress</button>';
          $('#executor-checklist-items').html(html);
          $('#checklist-progress-bar').show();
          $('#view-sop-btn').show();
          $('#executor-checklist-section').show();
          
          // Check completion percentage and update mark as completed checkbox
          updateCompletionStatus();
          
          // Add event listener for checklist changes
          $(document).on('change', '.executor-checkbox', updateCompletionStatus);
        } else {
          hasExecutorChecklist = false;
          $('#executor-checklist-items').html('<div class="text-muted">No checklist available for this task</div>');
          $('#checklist-progress-bar').hide();
          $('#view-sop-btn').hide();
          $('#event-status').prop('disabled', false);
        }
      } else {
        hasExecutorChecklist = false;
        $('#executor-checklist-items').html('<div class="text-muted">No checklist available</div>');
        $('#checklist-progress-bar').hide();
        $('#view-sop-btn').hide();
        $('#event-status').prop('disabled', false);
      }
    }, 'json').fail(function() {
      hasExecutorChecklist = false;
      $('#executor-checklist-items').html('<div class="text-danger">Error loading checklist</div>');
      $('#checklist-progress-bar').hide();
      $('#view-sop-btn').hide();
      $('#event-status').prop('disabled', false);
    });
  }
  
  // View SOP details
  $(document).on('click', '#view-sop-btn', function() {
    if (!currentIssueId) return;
    
    $.ajax({
      url: '<?= base_url("planner/getDetailedSop") ?>',
      type: 'POST',
      data: {'id': currentIssueId},
      dataType: 'html',
      success: function(data) {
        // Remove existing SOP modal if any
        $('#sopViewModal').remove();
        
        // Create a temporary modal for SOP view
        const sopModal = $(`
          <div class="modal fade" id="sopViewModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
              <div class="modal-content">
                ${data}
              </div>
            </div>
          </div>
        `);
        
        // Append and show
        $('body').append(sopModal);
        $('#sopViewModal').modal({backdrop: 'static', keyboard: false});
        
        // Clean up on close and restore main modal
        $('#sopViewModal').on('hidden.bs.modal', function() {
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
  
  function updateCompletionStatus() {
    if (!hasExecutorChecklist) return;
    
    const totalItems = $('.executor-checkbox').length;
    const checkedItems = $('.executor-checkbox:checked').length;
    const completionPercentage = totalItems > 0 ? Math.round((checkedItems / totalItems) * 100) : 0;
    
    // Update progress bar
    const progressBar = $('#progress-bar');
    const progressText = $('#progress-text');
    const completionStatus = $('#completion-status');
    
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
      $('#event-status').prop('disabled', false);
    } else {
      $('#event-status').prop('disabled', true).prop('checked', false);
    }
  }

  // Save executor checklist progress
  $(document).on('click', '#save-executor-checklist', function() {
    const ev = $('#eventModal').data('event');
    const completedItems = [];
    
    $('#executor-checklist-items input[type="checkbox"]:checked').each(function() {
      completedItems.push($(this).val());
    });
    
    $.post('<?= base_url("planner/save_executor_checklist") ?>', {
      issue_id: ev.extendedProps.issueId,
      completed_items: completedItems
    }, function(response) {
      if (response && response.status === 'success') {
        updateCompletionStatus();
        const btn = $('#save-executor-checklist');
        const originalText = btn.text();
        btn.text('Saved!').addClass('btn-success').removeClass('btn-primary');
        setTimeout(() => {
          btn.text(originalText).removeClass('btn-success').addClass('btn-primary');
        }, 2000);
      } else {
        alert('Failed to save checklist progress');
      }
    }, 'json').fail(function() {
      alert('Error saving checklist progress');
    });
  });

  // Comment functions
  function loadComments(issueId) {
    $('#event-comments-list').html('<i class="fas fa-spinner fa-spin"></i> Loading...');
    $.get('<?= base_url("planner/get_comments") ?>', { issue_id: issueId }, function(comments) {
      let html = '';
      const currentUserId = <?= get_loggedin_user_id() ?>;
      if (comments && comments.length > 0) {
        comments.forEach(c => {
          const date = new Date(c.created_at).toLocaleString();
          html += `<div class="comment-item" style="margin-bottom: 15px; padding: 10px; border-left: 4px solid #007bff; background: #edeff1; border-radius: 6px;" data-comment-id="${c.id}">`;
          html += `<div style="display: flex; justify-content: space-between; align-items: center;">`;
          html += `<div><small><strong>${c.user_name || 'User'}</strong> - ${date}</small></div>`;
          if (parseInt(c.author_id) === currentUserId) {
            html += `<div>`;
            html += `<button class="btn btn-xs btn-warning edit-comment-btn" data-comment-id="${c.id}" style="margin-right: 5px;">Edit</button>`;
            html += `<button class="btn btn-xs btn-danger delete-comment-btn" data-comment-id="${c.id}">Delete</button>`;
            html += `</div>`;
          }
          html += `</div>`;
          html += `<div class="comment-text" style="margin-top: 6px;">${processMentions(c.comment_text.replace(/\n/g, '<br>'))}</div>`;
          html += `<textarea class="form-control edit-comment-text" style="display: none; margin-top: 5px;">${c.comment_text}</textarea>`;
          html += `<div class="edit-comment-actions" style="display: none; margin-top: 5px;">`;
          html += `<button class="btn btn-xs btn-success save-comment-btn" data-comment-id="${c.id}">Save</button> `;
          html += `<button class="btn btn-xs btn-default cancel-edit-btn">Cancel</button>`;
          html += `</div>`;
          html += `</div>`;
        });
      } else {
        html = '<div class="text-muted">No comments yet</div>';
      }
      $('#event-comments-list').html(html);
    }, 'json');
  }

  $('#add-comment-btn').on('click', function() {
    const ev = $('#eventModal').data('event');
    const comment = $('#event-comment').val().trim();
    if (!comment) return;
    
    $.post('<?= base_url("planner/add_comment") ?>', {
      issue_id: ev.extendedProps.issueId,
      comment: comment
    }, function(resp) {
      if (resp && resp.status === 'success') {
        $('#event-comment').val('');
        loadComments(ev.extendedProps.issueId);
      }
    }, 'json');
  });

  // Comment edit/delete handlers
  $(document).on('click', '.edit-comment-btn', function() {
    const commentId = $(this).data('comment-id');
    const commentItem = $(`.comment-item[data-comment-id="${commentId}"]`);
    const editTextarea = commentItem.find('.edit-comment-text');
    
    commentItem.find('.comment-text').hide();
    editTextarea.show();
    commentItem.find('.edit-comment-actions').show();
    $(this).parent().hide();
    
    // Initialize mention system for edit textarea
    if (typeof MentionSystem !== 'undefined' && !editTextarea.data('mention-initialized')) {
      const textareaId = 'edit-comment-' + commentId;
      const dropdownId = 'edit-mention-dropdown-' + commentId;
      
      editTextarea.attr('id', textareaId);
      
      // Create dropdown container if it doesn't exist
      if (!commentItem.find('.edit-mention-dropdown').length) {
        const container = $('<div class="mention-container" style="position: relative;"></div>');
        editTextarea.wrap(container);
        editTextarea.after(`<div id="${dropdownId}" class="mention-dropdown edit-mention-dropdown" style="display: none;"></div>`);
      }
      
      new MentionSystem(textareaId, dropdownId, '<?= base_url() ?>');
      editTextarea.data('mention-initialized', true);
    }
    
    editTextarea.focus();
  });
  
  $(document).on('click', '.cancel-edit-btn', function() {
    const commentItem = $(this).closest('.comment-item');
    commentItem.find('.comment-text').show();
    commentItem.find('.edit-comment-text').hide();
    commentItem.find('.edit-comment-actions').hide();
    commentItem.find('.edit-comment-btn').parent().show();
  });
  
  $(document).on('click', '.save-comment-btn', function() {
    const commentId = $(this).data('comment-id');
    const newText = $(this).closest('.comment-item').find('.edit-comment-text').val();
    
    $.post('<?= base_url("planner/update_comment") ?>', {
      comment_id: commentId,
      comment_text: newText
    }, function(response) {
      if (response.status === 'success') {
        const ev = $('#eventModal').data('event');
        loadComments(ev.extendedProps.issueId);
      } else {
        alert('Failed to update comment');
      }
    }, 'json');
  });
  
  $(document).on('click', '.delete-comment-btn', function() {
    if (!confirm('Are you sure you want to delete this comment?')) return;
    
    const commentId = $(this).data('comment-id');
    $.post('<?= base_url("planner/delete_comment") ?>', {
      comment_id: commentId
    }, function(response) {
      if (response.status === 'success') {
        const ev = $('#eventModal').data('event');
        loadComments(ev.extendedProps.issueId);
      } else {
        alert('Failed to delete comment');
      }
    }, 'json');
  });

	
	
  /* ===== Priority Change Handler ===== */
  $('#task-priority').on('change', function() {
    const ev = $('#eventModal').data('event');
    const task_priority = $(this).val();
    
    // Don't update if no priority selected
    if (!task_priority) {
      return;
    }
    
    $.post('<?= base_url("planner/update_priority") ?>', {
      issue_id: ev.extendedProps.issueId,
      task_priority: task_priority
    }, function(resp) {
      if (resp && resp.status === 'success') {
        ev.setExtendedProp('priorityTask', task_priority);
        loadTasks(); // Refresh task list to reflect task priority change
        
        // Show toast message
        showToast('Priority updated successfully!', 'success');
      } else {
        alert('<?= translate("update_failed") ?>');
      }
    }, 'json');
  });
  
  // Toast notification function
  function showToast(message, type = 'info') {
    const toastId = 'toast-' + Date.now();
    const bgColor = type === 'success' ? '#5cb85c' : type === 'error' ? '#d9534f' : '#5bc0de';
    
    const toast = $(`
      <div id="${toastId}" style="
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${bgColor};
        color: white;
        padding: 12px 20px;
        border-radius: 4px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
        font-size: 14px;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
      ">${message}</div>
    `);
    
    $('body').append(toast);
    
    // Animate in
    setTimeout(() => {
      toast.css({ opacity: 1, transform: 'translateX(0)' });
    }, 10);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
      toast.css({ opacity: 0, transform: 'translateX(100%)' });
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }


  /* ===== Update Timeline ===== */
  $('#update-timeline').on('click', function() {
    const ev = $('#eventModal').data('event');
    const startTime = $('#custom-start-time').val();
    const endTime = $('#custom-end-time').val();
    
    if (!startTime) {
      alert('<?= translate("please_select_start_time") ?>');
      return;
    }
    
    $.post('<?= base_url("planner/update_event") ?>', {
      event_id: ev.id,
      start_time: startTime,
      end_time: endTime || null
    }, function(resp) {
      if (resp && resp.status === 'success') {
        calendar.refetchEvents();
        $('#eventModal').modal('hide');
      } else {
        alert('<?= translate("update_failed") ?>');
      }
    }, 'json');
  });

  /* ===== Complete / Delete ===== */
  $('#event-status').on('change', function() {
    const ev = $('#eventModal').data('event');
    const status = $(this).is(':checked') ? 1 : 0;

    $.post('<?= base_url("planner/update_status") ?>', {
      event_id: ev.id,
      issue_id: ev.extendedProps.issueId,
      status: status
    }, function(resp) {
      if (resp && resp.status === 'success') {
        const targetIssueId = parseInt(ev.extendedProps.issueId, 10);
        calendar.getEvents().forEach(function(e) {
          if (parseInt(e.extendedProps.issueId, 10) === targetIssueId) {
            e.setExtendedProp('status', status);
            if (status === 1) e.setProp('classNames', ['completed-event']);
            else e.setProp('classNames', []);
          }
        });
        $('#eventModal').modal('hide');
        loadTasks();
        if (typeof window.triggerTopbarUpdate === 'function') {
          window.triggerTopbarUpdate();
        }
      } else {
        $('#event-status').prop('checked', !status);
      }
    }, 'json');
  });

  $('#delete-event').on('click', function() {
    if (!confirm('<?= translate("confirm_delete") ?>')) return;
    const ev = $('#eventModal').data('event');
    $.post('<?= base_url("planner/delete_event") ?>', { event_id: ev.id }, function(resp) {
      if (resp && resp.status === 'success') {
        ev.remove();
        $('#eventModal').modal('hide');
        loadTasks();
      }
    }, 'json');
  });

  // Function to open task details in new window/tab
  window.openTaskDetails = function(taskId) {
    const url = '<?= base_url("tracker/my_issues") ?>';
    const newWindow = window.open(url, '_blank');
    
    // Wait for the new window to load and then open the task modal
    newWindow.addEventListener('load', function() {
      setTimeout(function() {
        if (typeof newWindow.openTaskModal === 'function') {
          newWindow.openTaskModal(taskId);
        }
      }, 500);
    });
  };
});
</script>
</div>
</div>