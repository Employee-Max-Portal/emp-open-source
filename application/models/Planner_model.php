<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Planner_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_user_issues($user_id)
    {
        return $this->db->select('*,
                    CASE
                        WHEN LOWER(task_status) = "todo" THEN 1
                        WHEN LOWER(task_status) = "in_progress" THEN 2
                        WHEN LOWER(task_status) = "hold" THEN 3
                        WHEN LOWER(task_status) = "completed" THEN 5
                        ELSE 4
                    END as status_priority')
                        ->where('assigned_to', $user_id)
                        ->where('approval_status', 'approved')
                        ->order_by('status_priority', 'ASC')
                        ->order_by('id', 'DESC')
                        ->get('tracker_issues')
                        ->result();
    }

    // Events for a user overlapping the requested range
	public function get_user_events($user_id, $start_date, $end_date)
	{
		return $this->db
			->select('
				planner_events.id AS event_id,
				planner_events.user_id,
				planner_events.issue_id,
				planner_events.start_time,
				planner_events.end_time,
				planner_events.status,
				tracker_issues.task_title,
				tracker_issues.task_description,
				tracker_issues.priority_level,
				tracker_issues.task_priority
			')
			->from('planner_events')
			->join('tracker_issues', 'tracker_issues.id = planner_events.issue_id')
			// overlap with visible range
			->where('planner_events.user_id', $user_id)
			->where('planner_events.start_time <=', $end_date)
			->where('planner_events.end_time >=', $start_date)
			->get()
			->result();
	}

    public function create_event($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->insert('planner_events', $data);
        return $this->db->insert_id();
    }

    public function update_event($event_id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $event_id)->update('planner_events', $data);
        return $this->db->affected_rows();
    }

    public function delete_event($event_id)
    {
        $this->db->where('id', $event_id)->delete('planner_events');
        return $this->db->affected_rows();
    }

    public function update_event_status($event_id, $status)
    {
        $this->db->where('id', $event_id)
                 ->update('planner_events', array(
                     'status'     => $status,
                     'updated_at' => date('Y-m-d H:i:s'),
                 ));
        return $this->db->affected_rows();
    }

	public function update_issue_status($issue_id, $status, $spent_time = null)
    {
        $data = array('task_status' => $status);

        // Get current task details
        $issue = $this->db->where('id', $issue_id)->get('tracker_issues')->row();

        if ($spent_time !== null) {
            $data['spent_time'] = $spent_time;
            // Calculate remaining time
            if ($issue && $issue->estimation_time) {
                $data['remaining_time'] = max(0.0, $issue->estimation_time - $spent_time);
            }
        }

        // Check if task is being completed and if it's late
        if (strtolower($status) === 'completed' && $issue && !empty($issue->estimated_end_time)) {
            $estimated_end_date = date('Y-m-d', strtotime($issue->estimated_end_time));
            $completion_date = date('Y-m-d');
            $data['is_late'] = ($completion_date > $estimated_end_date) ? 1 : 0;
        }

        $this->db->where('id', $issue_id)->update('tracker_issues', $data);
        return $this->db->affected_rows();
    }

	// Add this new method
	public function update_events_status_by_issue($issue_id, $status)
	{
		$this->db->where('issue_id', $issue_id)
				 ->update('planner_events', [
					 'status'     => (int)$status,
					 'updated_at' => date('Y-m-d H:i:s')
				 ]);
		return $this->db->affected_rows();
	}

	public function get_task_comments($issue_id)
	{
		return $this->db->select("
					tc.*,
					CASE
						WHEN tc.author_id = 1 THEN 'System'
						ELSE s.name
					END as user_name
				")
				->from('tracker_comments tc')
				->join('staff s', 's.id = tc.author_id', 'left')
				->where('tc.task_id', $issue_id)
				->order_by('tc.created_at', 'DESC')
				->get()
				->result();
	}


	public function add_task_comment($issue_id, $comment)
	{
		$data = [
			'task_id' => $issue_id,
			'author_id' => get_loggedin_user_id(),
			'comment_text' => $comment,
			'created_at' => date('Y-m-d H:i:s')
		];
		$this->db->insert('tracker_comments', $data);
		return $this->db->insert_id();
	}

	public function update_task_comment($comment_id, $comment_text)
	{
		$this->db->where('id', $comment_id);
		$this->db->where('author_id', get_loggedin_user_id());
		return $this->db->update('tracker_comments', ['comment_text' => $comment_text]);
	}

	public function delete_task_comment($comment_id)
	{
		$this->db->where('id', $comment_id);
		$this->db->where('author_id', get_loggedin_user_id());
		return $this->db->delete('tracker_comments');
	}

	// Calculate total spent time from all planner events for a task
	public function calculate_total_spent_time($issue_id)
	{
		$events = $this->db->select('start_time, end_time')
					   ->where('issue_id', $issue_id)
					   ->get('planner_events')
					   ->result();

		$total_hours = 0;
		foreach ($events as $event) {
			$start = strtotime($event->start_time);
			$end = strtotime($event->end_time);
			$duration_seconds = $end - $start;
			$duration_hours = $duration_seconds / 3600; // Convert to hours
			$total_hours += $duration_hours;
		}

		return round($total_hours, 2);
	}

	public function update_staff_task_log($issue_id, $task_status, $event_id = null)
	{
		// Get task details
		$task = $this->db->where('id', $issue_id)->get('tracker_issues')->row();
		if (!$task) return false;

		$update_data = ['task_status' => $task_status];

		// Get planner event times if event_id provided
		if ($event_id) {
			$event = $this->db->where('id', $event_id)->get('planner_events')->row();
			if ($event) {
				$update_data['proof'] = $task->unique_id;
				$update_data['start_time'] = $event->start_time;
				$update_data['actual_end_time'] = $task_status === 'Completed' ? $event->end_time : null;
				$update_data['ended_at'] = $task_status === 'Completed' ? $event->end_time : null;
				$update_data['logged_at'] = date('Y-m-d H:i:s');
			}
		} else {
			$update_data['actual_end_time'] = $task_status === 'Completed' ? date('Y-m-d H:i:s') : null;
			$update_data['ended_at'] = $task_status === 'Completed' ? date('Y-m-d H:i:s') : null;
		}

		// Update staff_task_log - check by tracker_id first, then by task_title
		$this->db->where('staff_id', $task->assigned_to);
		$this->db->where('tracker_id', $issue_id);
		$this->db->update('staff_task_log', $update_data);
		return $this->db->affected_rows();
	}

	// Get completed executor checklist items for a task
	public function get_completed_executor_checklist($issue_id)
	{
		$result = $this->db->select('executor_completed')
					   ->where('issue_id', $issue_id)
					   ->get('task_checklist_progress')
					   ->row();

		if ($result && !empty($result->executor_completed)) {
			$items = json_decode($result->executor_completed, true);
			return is_array($items) ? $items : [];
		}

		return [];
	}

	// Get completed checklist items for a task (backward compatibility)
	public function get_completed_checklist_items($issue_id)
	{
		$result = $this->db->select('completed_items')
					   ->where('issue_id', $issue_id)
					   ->get('task_checklist_progress')
					   ->row();

		if ($result && !empty($result->completed_items)) {
			$items = json_decode($result->completed_items, true);
			return is_array($items) ? $items : [];
		}

		return [];
	}

	// Save executor checklist progress for a task
	public function save_executor_checklist_progress($issue_id, $completed_items)
	{
		$data = [
			'issue_id' => $issue_id,
			'executor_completed' => json_encode($completed_items),
			'updated_at' => date('Y-m-d H:i:s')
		];

		// Check if record exists
		$existing = $this->db->where('issue_id', $issue_id)->get('task_checklist_progress')->row();

		if ($existing) {
			$this->db->where('issue_id', $issue_id)->update('task_checklist_progress', $data);
		} else {
			$data['created_at'] = date('Y-m-d H:i:s');
			$this->db->insert('task_checklist_progress', $data);
		}

		return $this->db->affected_rows() > 0;
	}

	// Save checklist progress for a task (backward compatibility)
	public function save_checklist_progress($issue_id, $completed_items)
	{
		$data = [
			'issue_id' => $issue_id,
			'completed_items' => json_encode($completed_items),
			'updated_at' => date('Y-m-d H:i:s')
		];

		// Check if record exists
		$existing = $this->db->where('issue_id', $issue_id)->get('task_checklist_progress')->row();

		if ($existing) {
			$this->db->where('issue_id', $issue_id)->update('task_checklist_progress', $data);
		} else {
			$data['created_at'] = date('Y-m-d H:i:s');
			$this->db->insert('task_checklist_progress', $data);
		}

		return $this->db->affected_rows() > 0;
	}

}
