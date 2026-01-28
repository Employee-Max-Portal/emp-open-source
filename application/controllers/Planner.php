<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Planner extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('planner_model');
    }

    public function index()
    {
        if (!get_permission('my_planner', 'is_view')) {
            access_denied();
        }

        // Check if mobile device
        if ($this->is_mobile()) {
            return $this->mobile();
        }

        $this->data['title']      = translate('my_planner');
        $this->data['sub_page']   = 'planner/index';
        $this->data['main_menu']  = 'tracker';

        // FullCalendar + Interaction plugin for external drag, jQuery & Bootstrap

		$data['headerelements'] = [
			'js' => [
				'assets/planner/js/jquery.min.js',
				'assets/planner/js/bootstrap.bundle.min.js',
				'assets/planner/js/main.min.js',
				'assets/planner/js/locales-all.min.js',

                'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/interaction.min.js',
			],
			'css' => [
				'assets/planner/css/main.min.css',
			]
		];

        $this->load->view('layout/index', $this->data);
    }

    public function mobile()
    {
        if (!get_permission('my_planner', 'is_view')) {
            access_denied();
        }

        $this->data['title']      = translate('my_planner');
        $this->data['sub_page']   = 'planner/mobile';
        $this->data['main_menu']  = 'tracker';

        // Mobile-optimized resources (no FullCalendar needed)
        $data['headerelements'] = [
            'js' => [
                'assets/planner/js/jquery.min.js',
                'assets/planner/js/bootstrap.bundle.min.js',
            ],
            'css' => [
                // Minimal CSS for mobile
            ]
        ];

        $this->load->view('layout/index', $this->data);
    }

    private function is_mobile()
    {
        $user_agent = $this->input->server('HTTP_USER_AGENT');
        return preg_match('/(android|iphone|ipad|mobile|tablet)/i', $user_agent);
    }

    public function force_mobile()
    {
        return $this->mobile();
    }

   public function get_events()
	{
		if (!get_permission('my_planner', 'is_view')) ajax_access_denied();

		$user_id = get_loggedin_user_id();
		//$user_id = 15;
		$start   = $this->input->get('start');
		$end     = $this->input->get('end');

		$rows = $this->planner_model->get_user_events($user_id, $start, $end);

		$out = [];
		foreach ($rows as $e) {
			$color = '#3a87ad';
			if ($e->priority_level === 'high')   $color = '#d9534f';
			if ($e->priority_level === 'medium') $color = '#f0ad4e';
			if ($e->priority_level === 'low')    $color = '#5cb85c';

			$out[] = [
				'id'    => (string)$e->event_id,      // IMPORTANT: planner_events.id
				'title' => $e->task_title,
				'start' => $e->start_time,
				'end'   => $e->end_time,
				'color' => $color,
				'extendedProps' => [
					'description' => $e->task_description,
					'priority'    => $e->priority_level,
					'priorityTask'=> (int)$e->task_priority,
					'status'      => (int)$e->status,
					'issueId'     => (int)$e->issue_id,
				],
			];
		}

		$this->output->set_content_type('application/json')
					 ->set_output(json_encode($out));
	}


   // application/controllers/Planner.php
	public function get_issues()
	{
		if (!get_permission('my_planner', 'is_view')) ajax_access_denied();

		$user_id = get_loggedin_user_id();
		//$user_id = 15;
		$issues  = $this->planner_model->get_user_issues($user_id);

		$out = [];
		foreach ($issues as $i) {
			$out[] = [
				'id'             => (int)$i->id,
				'title'          => $i->task_title,
				'unique_id'      => $i->unique_id,
				'priority'       => $i->priority_level,
				'description'    => $i->task_description,
				'estimated_time' => (float)$i->estimation_time, // hours
				'status'         => strtolower((string)$i->task_status), // <-- add this
				'parent_issue'   => $i->parent_issue ? (int)$i->parent_issue : null,
			];
		}

		$this->output->set_content_type('application/json')
					 ->set_output(json_encode($out));
	}


    public function create_event()
	{
		if (!get_permission('my_planner', 'is_add')) {
			ajax_access_denied();
		}

		$user_id  = get_loggedin_user_id();

		$issue_id = (int)$this->input->post('issue_id');

		// Use 30-minute default slot for drag and drop
		$duration_hours = 0.5; // 30 minutes
		$duration_sec   = (int)round($duration_hours * 3600);

		$start_time = $this->input->post('start_time'); // startStr from client
		$start_ts   = strtotime($start_time);
		$end_time   = date('Y-m-d H:i:s', $start_ts + $duration_sec);

		$data = array(
			'user_id'    => $user_id,
			'issue_id'   => $issue_id,
			'start_time' => date('Y-m-d H:i:s', $start_ts),
			'end_time'   => $end_time,
			'status'     => 0, // not completed
		);

		$event_id = $this->planner_model->create_event($data);

		// NEW: when scheduled, set the issue status to in_progress
		// (avoid downgrading a completed task back to in_progress)
		$issue = $this->db->where('id', $issue_id)->get('tracker_issues')->row();
		if ($issue && strtolower($issue->task_status) !== 'completed') {
			$this->planner_model->update_issue_status($issue_id, 'in_progress');
		}

		$this->output->set_content_type('application/json')
					 ->set_output(json_encode(array('status' => 'success', 'event_id' => $event_id)));
	}

   public function update_event()
	{
		if (!get_permission('my_planner', 'is_edit')) ajax_access_denied();

		$event_id   = (int)$this->input->post('event_id');
		$start_time = $this->input->post('start_time'); // from event.startStr
		$end_time   = $this->input->post('end_time');   // from event.endStr

		$data = [];
		if ($start_time) $data['start_time'] = date('Y-m-d H:i:s', strtotime($start_time));
		if ($end_time)   $data['end_time']   = date('Y-m-d H:i:s', strtotime($end_time));

		$ok = $this->planner_model->update_event($event_id, $data) > 0;

		$this->output->set_content_type('application/json')
					 ->set_output(json_encode(['status' => $ok ? 'success' : 'error']));
	}

	public function update_status()
	{
		if (!get_permission('my_planner', 'is_edit')) ajax_access_denied();

		$event_id = (int)$this->input->post('event_id');
		$status   = (int)$this->input->post('status');   // 1 = completed, 0 = not completed
		$issue_id = (int)$this->input->post('issue_id');

		// Always update the clicked event
		$affected1 = $this->planner_model->update_event_status($event_id, $status);

		$affected2 = 0;
		$affected3 = 0;

		if ($issue_id) {
			if ($status === 1) {
				// Mark ALL blocks of this issue completed + close the issue
				$affected2 = $this->planner_model->update_events_status_by_issue($issue_id, 1);

				// Calculate total spent time from all planner events for this task
				$total_spent_hours = $this->planner_model->calculate_total_spent_time($issue_id);
				$affected3 = $this->planner_model->update_issue_status($issue_id, 'completed', $total_spent_hours);

				// Update parent task spent time if this is a sub-task
				$task = $this->db->select('parent_issue')->where('id', $issue_id)->get('tracker_issues')->row();
				if ($task && !empty($task->parent_issue)) {
					$this->db->set('spent_time', 'spent_time + ' . (float)$total_spent_hours, FALSE);
					$this->db->set('remaining_time', 'estimation_time - spent_time', FALSE);
					$this->db->where('id', $task->parent_issue);
					$this->db->update('tracker_issues');
				}


				// Update staff_task_log to completed
				$this->planner_model->update_staff_task_log($issue_id, 'Completed', $event_id);
			} else {
				// RE-OPEN: clear completion on ALL blocks of this issue + set issue to in_progress
				$affected2 = $this->planner_model->update_events_status_by_issue($issue_id, 0);
				$affected3 = $this->planner_model->update_issue_status($issue_id, 'in_progress');

				// Update staff_task_log to in progress
				$this->planner_model->update_staff_task_log($issue_id, 'In Progress', $event_id);
			}
		}

		$ok = ($affected1 + $affected2 + $affected3) > 0;

		$this->output->set_content_type('application/json')
					 ->set_output(json_encode(['status' => $ok ? 'success' : 'error']));
	}


   public function delete_event()
{
    if (!get_permission('my_planner', 'is_delete')) ajax_access_denied();

    $event_id = (int)$this->input->post('event_id');
    $ok = $this->planner_model->delete_event($event_id) > 0;

    $this->output->set_content_type('application/json')
                 ->set_output(json_encode(['status' => $ok ? 'success' : 'error']));
}


public function update_priority()
{
    if (!get_permission('my_planner', 'is_edit')) ajax_access_denied();

    $issue_id = (int)$this->input->post('issue_id');
    $task_priority = (int)$this->input->post('task_priority');

    // Validate task priority range (1-10)
    if ($task_priority < 1 || $task_priority > 10) {
        $this->output->set_content_type('application/json')
                     ->set_output(json_encode(['status' => 'error', 'message' => 'Invalid priority value']));
        return;
    }

    // Update the task priority in tracker_issues table
    $this->db->where('id', $issue_id);
    $result = $this->db->update('tracker_issues', ['task_priority' => $task_priority]);

    $this->output->set_content_type('application/json')
                 ->set_output(json_encode(['status' => $result ? 'success' : 'error']));
}


public function get_comments()
{
    if (!get_permission('my_planner', 'is_view')) ajax_access_denied();

    $issue_id = (int)$this->input->get('issue_id');
    $comments = $this->planner_model->get_task_comments($issue_id);

    $this->output->set_content_type('application/json')
                 ->set_output(json_encode($comments));
}

public function add_comment()
{
    if (!get_permission('my_planner', 'is_edit')) ajax_access_denied();

    $issue_id = (int)$this->input->post('issue_id');
    $comment = $this->input->post('comment');

    if ($comment && trim($comment) !== '') {
        $comment_id = $this->planner_model->add_task_comment($issue_id, $comment);

        // Handle mentions
        $mentioned_users = $this->extract_mentions($comment);
        if (!empty($mentioned_users)) {
            // Get task details
            $task = $this->db->select('unique_id, task_title')->where('id', $issue_id)->get('tracker_issues')->row();
            if ($task) {
                $this->create_mention_notifications($mentioned_users, $task->unique_id, $task->task_title, get_loggedin_user_id(), $comment);
            }
        }

        $this->output->set_content_type('application/json')
                     ->set_output(json_encode(['status' => 'success', 'comment_id' => $comment_id]));
    } else {
        $this->output->set_content_type('application/json')
                     ->set_output(json_encode(['status' => 'error', 'message' => 'Comment cannot be empty']));
    }
}

public function update_comment()
{
    if (!get_permission('my_planner', 'is_edit')) ajax_access_denied();

    $comment_id = (int)$this->input->post('comment_id');
    $comment_text = $this->input->post('comment_text');

    if ($comment_text && trim($comment_text) !== '') {
        $result = $this->planner_model->update_task_comment($comment_id, $comment_text);

        if ($result) {
            // Handle mentions in updated comment
            $mentioned_users = $this->extract_mentions($comment_text);
            if (!empty($mentioned_users)) {
                // Get comment and task details
                $comment = $this->db->select('tc.task_id')->from('tracker_comments tc')
                    ->where('tc.id', $comment_id)->get()->row();
                if ($comment) {
                    $task = $this->db->select('unique_id, task_title')->where('id', $comment->task_id)
                        ->get('tracker_issues')->row();
                    if ($task) {
                        $this->create_mention_notifications($mentioned_users, $task->unique_id,
                            $task->task_title, get_loggedin_user_id(), $comment_text);
                    }
                }
            }
        }

        $this->output->set_content_type('application/json')
                     ->set_output(json_encode(['status' => $result ? 'success' : 'error']));
    } else {
        $this->output->set_content_type('application/json')
                     ->set_output(json_encode(['status' => 'error', 'message' => 'Comment cannot be empty']));
    }
}

public function delete_comment()
{
    if (!get_permission('my_planner', 'is_edit')) ajax_access_denied();

    $comment_id = (int)$this->input->post('comment_id');
    $result = $this->planner_model->delete_task_comment($comment_id);

    $this->output->set_content_type('application/json')
                 ->set_output(json_encode(['status' => $result ? 'success' : 'error']));
}

// Get available users for mentions
public function get_mention_users() {
    if (!$this->input->is_ajax_request()) {
        show_404();
    }

    // Handle both GET and POST requests for backward compatibility
    $search = $this->input->post('search') ?: $this->input->get('search');

	$this->db->select('staff.id, staff.name, staff.photo');
	$this->db->from('staff');
	$this->db->join('login_credential', 'login_credential.user_id = staff.id');
	$this->db->where('staff.id !=', 1);
	$this->db->where('login_credential.active', 1);
	$this->db->where('login_credential.role !=', 9);

	if (!empty($search)) {
		$this->db->like('staff.name', $search);
	}

	$this->db->limit(10);
	$users = $this->db->get()->result();


    // Format user data
    $formatted_users = [];
    foreach ($users as $user) {
        $formatted_users[] = [
            'id' => $user->id,
            'name' => $user->name,
            'photo' => $user->photo ? get_image_url('staff', $user->photo) : null
        ];
    }

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($formatted_users));
}

// Extract mentioned user IDs from comment text
private function extract_mentions($comment_text) {
    preg_match_all('/@\[(\d+)\]/', $comment_text, $matches);
    return array_unique($matches[1]);
}

// Create notifications for mentioned users
private function create_mention_notifications($mentioned_users, $task_unique_id, $task_title, $author_id, $comment_text) {
    // Get author name
    $author = $this->db->select('name')->where('id', $author_id)->get('staff')->row();
    $author_name = $author ? $author->name : 'Someone';

    foreach ($mentioned_users as $user_id) {
        // Don't notify the author
        if ($user_id == $author_id) continue;

        // Get user details including telegram_id
        $user = $this->db->select('name, telegram_id')->where('id', $user_id)->get('staff')->row();

        $notification_data = [
            'user_id' => $user_id,
            'type' => 'mention',
            'title' => 'You were mentioned in a planner comment',
            'message' => $author_name . ' mentioned you in a planner comment on task ' . $task_unique_id . ': ' . $task_title,
            'url' => base_url('planner'),
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('notifications', $notification_data);

        // Send Telegram notification if user has telegram_id
        if ($user && !empty($user->telegram_id)) {
            $this->send_telegram_notification($user->telegram_id, $user->name, $author_name, $task_title, $task_unique_id, $comment_text);
        }
    }
}

// Send Telegram notification
private function send_telegram_notification($chat_id, $staff_name, $author_name, $task_title, $task_unique_id, $comment_text) {
    $bot_token = $telegram_bot;
    $today = date('d M Y');
    $clean_comment = strip_tags($comment_text);
    $tg_message = "🛎️ *Planner Mention Notification*\n\n" .
        "📅 *Date:* {$today}\n" .
        "👤 *Mentioned:* {$staff_name}\n" .
        "👨💼 *By:* {$author_name}\n\n" .
        "📌 *Task:* {$task_unique_id} - {$task_title}\n" .
        "💬 *Comment:* {$clean_comment}\n\n" .
        "🔗 [Open Planner](" . base_url('planner') . ")";

    $payload = [
        'chat_id' => $chat_id,
        'text' => $tg_message,
        'parse_mode' => 'Markdown',
        'disable_web_page_preview' => true,
    ];

    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_exec($ch);
    curl_close($ch);
}

// Get SOP checklist for a task
public function get_sop_checklist()
{
    if (!get_permission('my_planner', 'is_view')) ajax_access_denied();

    $issue_id = (int)$this->input->get('issue_id');

    // Get task details to check if it has SOP
    $task = $this->db->select('sop_ids')->where('id', $issue_id)->get('tracker_issues')->row();

    if (!$task || empty($task->sop_ids)) {
        $this->output->set_content_type('application/json')
                     ->set_output(json_encode(['status' => 'success', 'executor_checklist' => null, 'executor_completed' => []]));
        return;
    }

    // Parse SOP IDs - handle both JSON array and comma-separated string
    $sop_ids = [];
    if (is_string($task->sop_ids)) {
        // Try to decode as JSON first
        $decoded = json_decode($task->sop_ids, true);
        if (is_array($decoded)) {
            $sop_ids = $decoded;
        } else {
            // Fallback to comma-separated
            $sop_ids = explode(',', $task->sop_ids);
        }
    } elseif (is_array($task->sop_ids)) {
        $sop_ids = $task->sop_ids;
    }

    $sop_ids = array_filter(array_map('trim', $sop_ids));

    if (empty($sop_ids)) {
        $this->output->set_content_type('application/json')
                     ->set_output(json_encode(['status' => 'success', 'executor_checklist' => null, 'executor_completed' => []]));
        return;
    }

    // Get executor checklist from SOPs
    $executor_checklist = [];
    $sop_titles = [];
    foreach ($sop_ids as $sop_id) {
        $sop = $this->db->select('executor_stage, title')->where('id', $sop_id)->get('sop')->row();
        if ($sop) {
            $sop_titles[] = $sop->title;
            if (!empty($sop->executor_stage)) {
                $stage = json_decode($sop->executor_stage, true);
                if (is_array($stage) && isset($stage['labels']) && is_array($stage['labels'])) {
                    $executor_checklist = array_merge($executor_checklist, $stage['labels']);
                }
            }
        }
    }

    // Get completed checklist items for this task
    $completed = $this->planner_model->get_completed_executor_checklist($issue_id);

    $this->output->set_content_type('application/json')
                 ->set_output(json_encode([
                     'status' => 'success',
                     'executor_checklist' => array_unique($executor_checklist),
                     'executor_completed' => $completed,
                     'sop_titles' => $sop_titles
                 ]));
}

// Save executor checklist progress
public function save_executor_checklist()
{
    if (!get_permission('my_planner', 'is_edit')) ajax_access_denied();

    $issue_id = (int)$this->input->post('issue_id');
    $completed_items = $this->input->post('completed_items');

    if (!is_array($completed_items)) {
        $completed_items = [];
    }

    $result = $this->planner_model->save_executor_checklist_progress($issue_id, $completed_items);

    $this->output->set_content_type('application/json')
                 ->set_output(json_encode(['status' => $result ? 'success' : 'error']));
}

// Save checklist progress (keep for backward compatibility)
public function save_checklist_progress()
{
    if (!get_permission('my_planner', 'is_edit')) ajax_access_denied();

    $issue_id = (int)$this->input->post('issue_id');
    $completed_items = $this->input->post('completed_items');

    if (!is_array($completed_items)) {
        $completed_items = [];
    }

    $result = $this->planner_model->save_checklist_progress($issue_id, $completed_items);

    $this->output->set_content_type('application/json')
                 ->set_output(json_encode(['status' => $result ? 'success' : 'error']));
}

public function getDetailedSop()
{
    if (get_permission('my_planner', 'is_view')) {
        $task_id = $this->input->post('id');

        $issue = $this->db->select('sop_ids')->where('id', $task_id)->get('tracker_issues')->row();

        if ($issue && !empty($issue->sop_ids)) {
            $this->data['sop_ids'] = $issue->sop_ids;
        } else {
            $this->data['sop_ids'] = null;
        }

        $this->load->view('rdc/ViewMultipleSopDetails', $this->data);
    }
}

}
