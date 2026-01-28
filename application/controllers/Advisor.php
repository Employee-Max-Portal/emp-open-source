<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Advisor extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('advisor_model');

        // Check if user has superadmin (role_id = 1) or hr (role_id = 5) or advisor role (role_id = 10)
        if (!in_array(loggedin_role_id(), [1, 5, 10])) {
            access_denied();
        }
    }

    public function index()
    {
        $data['departments'] = $this->advisor_model->get_departments();
        $data['department_summary'] = $this->advisor_model->get_department_summary();
        $data['recent_tasks'] = $this->advisor_model->get_recent_tasks(7);
        $data['fund_summary'] = $this->advisor_model->get_fund_summary_by_department();

        $data['title'] = 'Advisor Dashboard';
        $data['sub_page'] = 'advisor/dashboard';
        $data['main_menu'] = 'dashboard';
        $this->load->view('layout/index', $data);
    }

    public function get_tasks()
    {
        $department_id = $this->input->get('department_id');
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');

        $tasks = $this->advisor_model->get_filtered_tasks($department_id, $date_from, $date_to);
        $department_summary = $this->advisor_model->get_filtered_department_summary($department_id, $date_from, $date_to);

        $response = [
            'tasks' => $tasks,
            'department_summary' => $department_summary
        ];

        $this->output->set_content_type('application/json')
                     ->set_output(json_encode($response));
    }

    public function add_task_comment()
    {
        $task_id = (int)$this->input->post('task_id');
        $comment = $this->input->post('comment');

        if ($comment && trim($comment) !== '') {
            // Convert @[id] back to @name for display while keeping @[id] for processing
            $display_comment = $this->convert_mentions_for_display($comment);

            $result = $this->advisor_model->add_task_comment($task_id, $display_comment);

            // Update advisor review status to completed
            $this->db->where('id', $task_id)->update('tracker_issues', ['advisor_review' => 1]);

            // Handle mentions
            $mentioned_users = $this->extract_mentions($comment);
            if (!empty($mentioned_users)) {
                // Get task details
                $task = $this->db->select('unique_id, task_title')->where('id', $task_id)->get('tracker_issues')->row();
                if ($task) {
                    $this->create_mention_notifications($mentioned_users, $task->unique_id, $task->task_title, get_loggedin_user_id(), $comment);
                }
            }

            $this->output->set_content_type('application/json')
                         ->set_output(json_encode(['status' => 'success', 'result' => $result]));
        } else {
            $this->output->set_content_type('application/json')
                         ->set_output(json_encode(['status' => 'error', 'message' => 'Comment cannot be empty']));
        }
    }

    public function create_task()
    {
        $data = [
            'task_title' => $this->input->post('title'),
            'task_description' => $this->input->post('description'),
            'department' => $this->input->post('department'),
            'assigned_to' => $this->input->post('assigned_to'),
            'estimated_end_time' => $this->input->post('due_date'),
            'created_by' => get_loggedin_user_id(),
            'task_status' => $this->input->post('task_status'),
            'priority_level' => $this->input->post('priority'),
            'logged_at' => date('Y-m-d H:i:s')
        ];

        $result = $this->advisor_model->create_task($data);

        if ($result) {
            set_alert('success', 'Task created successfully');
        } else {
            set_alert('error', 'Failed to create task');
        }

        redirect('advisor');
    }

     public function finance()
    {
        $date_from = null;
        $date_to = null;

		if ($this->input->post('daterange')) {
            $daterange = explode(' - ', $this->input->post('daterange'));
            if (count($daterange) == 2) {
                $date_from = date('Y-m-d', strtotime($daterange[0]));
                $date_to = date('Y-m-d', strtotime($daterange[1]));
            }
        }

        $data['fund_requisitions'] = $this->advisor_model->get_fund_requisitions($date_from, $date_to);
        $data['advance_salaries'] = $this->advisor_model->get_advance_salaries($date_from, $date_to);

        $data['title'] = 'Finance Overview';
        $data['sub_page'] = 'advisor/finance';
        $data['main_menu'] = 'dashboard';
        $this->load->view('layout/index', $data);
    }

    public function get_department_staff()
    {
        $department_id = $this->input->get('department_id');
        $staff = $this->advisor_model->get_department_staff($department_id);

        $this->output->set_content_type('application/json')
                     ->set_output(json_encode($staff));
    }

    public function get_task_comments()
    {
        $task_id = $this->input->get('task_id');
        $comments = $this->advisor_model->get_task_comments($task_id);

        $this->output->set_content_type('application/json')
                     ->set_output(json_encode($comments));
    }

    public function update_task_comment()
    {
        $comment_id = (int)$this->input->post('comment_id');
        $comment_text = $this->input->post('comment_text');

        if ($comment_text && trim($comment_text) !== '') {
            // Convert @[id] back to @name for display while keeping @[id] for processing
            $display_comment = $this->convert_mentions_for_display($comment_text);

            $result = $this->advisor_model->update_task_comment($comment_id, $display_comment);

            // Get task ID and update advisor review status
            $comment = $this->db->select('task_id')->where('id', $comment_id)->get('tracker_comments')->row();
            if ($comment) {
                $this->db->where('id', $comment->task_id)->update('tracker_issues', ['advisor_review' => 1]);
            }

            // Handle mentions in edited comment
            $mentioned_users = $this->extract_mentions($comment_text);
            if (!empty($mentioned_users)) {
                // Get task details from comment
                if ($comment) {
                    $task = $this->db->select('unique_id, task_title')->where('id', $comment->task_id)->get('tracker_issues')->row();
                    if ($task) {
                        $this->create_mention_notifications($mentioned_users, $task->unique_id, $task->task_title, get_loggedin_user_id(), $comment_text);
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

    public function delete_task_comment()
    {
        $comment_id = (int)$this->input->post('comment_id');
        $result = $this->advisor_model->delete_task_comment($comment_id);

        $this->output->set_content_type('application/json')
                     ->set_output(json_encode(['status' => $result ? 'success' : 'error']));
    }

    public function update_review_status()
    {
        $task_id = (int)$this->input->post('task_id');
        $status = (int)$this->input->post('status');

        $result = $this->db->where('id', $task_id)->update('tracker_issues', ['advisor_review' => $status]);

        $this->output->set_content_type('application/json')
                     ->set_output(json_encode(['status' => $result ? 'success' : 'error']));
    }

    public function get_mention_users() {
        if (!$this->input->server('HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest') {
            show_404();
        }

        $search = $this->input->get('search');

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

    private function extract_mentions($comment_text) {
        preg_match_all('/@\[(\d+)\]/', $comment_text, $matches);
        return array_unique($matches[1]);
    }

    private function create_mention_notifications($mentioned_users, $task_unique_id, $task_title, $author_id, $comment_text) {
        $author = $this->db->select('name')->where('id', $author_id)->get('staff')->row();
        $author_name = $author ? $author->name : 'Someone';

        foreach ($mentioned_users as $user_id) {
            if ($user_id == $author_id) continue;

            $user = $this->db->select('name, telegram_id')->where('id', $user_id)->get('staff')->row();

            $notification_data = [
                'user_id' => $user_id,
                'type' => 'mention',
                'title' => 'You were mentioned in an advisor comment',
                'message' => $author_name . ' mentioned you in an advisor comment on task ' . $task_unique_id . ': ' . $task_title,
                'url' => base_url('advisor'),
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

    private function convert_mentions_for_display($comment_text) {
        // Convert @[id] to @name for display
        return preg_replace_callback('/@\[(\d+)\]/', function($matches) {
            $user_id = $matches[1];
            $user = $this->db->select('name')->where('id', $user_id)->get('staff')->row();
            return $user ? '@' . $user->name : $matches[0];
        }, $comment_text);
    }

    private function send_telegram_notification($chat_id, $staff_name, $author_name, $task_title, $task_unique_id, $comment_text) {
        $bot_token = $telegram_bot;
				
        $today = date('d M Y');
        // Convert @[id] to @name for Telegram display
        $display_comment = $this->convert_mentions_for_display($comment_text);
        $clean_comment = strip_tags($display_comment);
        $tg_message = "🛎️ *Advisor Mention Notification*\n\n" .
            "📅 *Date:* {$today}\n" .
            "👤 *Mentioned:* {$staff_name}\n" .
            "👨💼 *By:* {$author_name}\n\n" .
            "📌 *Task:* {$task_unique_id} - {$task_title}\n" .
            "💬 *Comment:* {$clean_comment}\n\n" .
            "🔗 [Open Advisor Dashboard](" . base_url('advisor') . ")";

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
}