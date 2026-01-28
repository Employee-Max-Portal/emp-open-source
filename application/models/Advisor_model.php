<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Advisor_model extends CI_Model
{
    public function get_departments()
    {
        return $this->db->get('tracker_departments')->result();
    }

    public function get_department_summary()
    {
        $seven_days_ago = date('Y-m-d', strtotime('-7 days'));

        $this->db->select('d.id, d.title,
                          COUNT(t.id) as total_tasks,
                          SUM(CASE WHEN t.task_status = "completed" THEN 1 ELSE 0 END) as completed_tasks,
                          SUM(CASE WHEN t.task_status != "completed" AND t.task_status IS NOT NULL THEN 1 ELSE 0 END) as pending_tasks');
        $this->db->from('tracker_departments d');
        $this->db->join('tracker_issues t', 'd.identifier = t.department AND t.logged_at >= "' . $seven_days_ago . '"', 'left');
        $this->db->join('staff s', 't.assigned_to = s.id', 'left');
        $this->db->join('login_credential lc', 'lc.user_id = s.id', 'left');
        $this->db->group_start();
        $this->db->where_in('lc.role', [2, 3, 5, 8]);
        $this->db->or_where('s.department', 11);
        $this->db->or_where('t.id IS NULL', null, false);
        $this->db->group_end();
        $this->db->group_by('d.id, d.title');
        return $this->db->get()->result();
    }

       public function get_recent_tasks($days = 7)
    {
        $this->db->select('t.*, s.name as assigned_name, d.title as department_name');
        $this->db->from('tracker_issues t');
        $this->db->join('staff s', 't.assigned_to = s.id', 'left');
        $this->db->join('login_credential lc', 'lc.user_id = s.id', 'left');
        $this->db->join('tracker_departments d', 't.department = d.identifier', 'left');
        $this->db->where('t.logged_at >=', date('Y-m-d', strtotime("-$days days")));
        $this->db->group_start();
        $this->db->where_in('lc.role', [2, 3, 5, 8]);
        $this->db->or_where('s.department', 11);
        $this->db->group_end();
        $this->db->order_by('t.logged_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get_filtered_tasks($department_id = null, $date_from = null, $date_to = null)
    {
        $this->db->select('t.*, s.name as assigned_name, d.title as department_name');
        $this->db->from('tracker_issues t');
        $this->db->join('staff s', 't.assigned_to = s.id', 'left');
        $this->db->join('login_credential lc', 'lc.user_id = s.id', 'left');
        $this->db->join('tracker_departments d', 't.department = d.identifier', 'left');

        if ($department_id) {
            $this->db->where('t.department', $department_id);
        }

        if ($date_from) {
            $this->db->where('t.logged_at >=', $date_from);
        }

        if ($date_to) {
            $this->db->where('t.logged_at <=', $date_to . ' 23:59:59');
        }

        $this->db->group_start();
        $this->db->where_in('lc.role', [2, 3, 5, 8]);
        $this->db->or_where('s.department', 11);
        $this->db->group_end();
        $this->db->order_by('t.logged_at', 'DESC');
        return $this->db->get()->result();
    }

	public function add_task_comment($task_id, $comment)
	{
		// 1️⃣ Insert the comment
		$data = [
			'task_id'      => $task_id,
			'comment_text' => $comment,
			'author_id'    => get_loggedin_user_id(),
			'created_at'   => date('Y-m-d H:i:s')
		];

		$inserted = $this->db->insert('tracker_comments', $data);

		// 2️⃣ Update advisor_review flag to 1
		if ($inserted) {
			$this->db->where('id', $task_id);
			$this->db->update('tracker_issues', ['advisor_review' => 1]);
		}

		return $inserted;
	}


public function get_task_comments($task_id)
{
    $this->db->select("
        tc.*,
        CASE
            WHEN tc.author_id = 1 THEN 'System'
            ELSE s.name
        END as author_name
    ");
    $this->db->from('tracker_comments tc');
    $this->db->join('staff s', 'tc.author_id = s.id', 'left');
    $this->db->where('tc.task_id', $task_id);
    $this->db->order_by('tc.created_at', 'ASC');
    return $this->db->get()->result();
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

    public function create_task($data)
    {
        // Generate unique ID based on department
        $department = $this->db->where('identifier', $data['department'])->get('tracker_departments')->row();
        if ($department) {
            $identifier = $department->identifier;

            // Count existing issues with same prefix
            $this->db->like('unique_id', $identifier . '-', 'after');
            $this->db->from('tracker_issues');
            $count = $this->db->count_all_results();

            // Generate next ID number
            $next_number = $count + 1;

            // Format unique ID
            $unique_id = $identifier . '-' . $next_number;

            $data['unique_id'] = $unique_id;
        }

        // Insert into tracker_issues
        $result = $this->db->insert('tracker_issues', $data);

        if ($result) {
            $task_id = $this->db->insert_id();

            // Insert into staff_task_log
            $log_data = [
                'staff_id' => $data['assigned_to'],
				'location'    => 'Tracker',
                'task_title' => $data['task_title'],
                'start_time'  => (new DateTime('now', $tz))->format('Y-m-d H:i:s'),
				'task_status' => 'In Progress',
				'logged_at'   => (new DateTime('now', $tz))->format('Y-m-d H:i:s')
            ];

            $this->db->insert('staff_task_log', $log_data);
        }

        return $result;
    }

     public function get_fund_requisitions($date_from = null, $date_to = null)
    {
        $this->db->select('fr.*, s.name as staff_name, s.staff_id, s.photo, sd.name as department_name, fc.name as category');
        $this->db->from('fund_requisition fr');
        $this->db->join('staff s', 'fr.staff_id = s.id', 'left');
        $this->db->join('staff_department sd', 's.department = sd.id', 'left');
		$this->db->join('fund_category fc', 'fc.id = fr.category_id', 'left');

        if ($date_from) {
            $this->db->where('fr.request_date >=', $date_from . ' 00:00:00');
        }

        if ($date_to) {
            $this->db->where('fr.request_date <=', $date_to . ' 23:59:59');
        }

        $this->db->order_by('fr.id', 'DESC');
        return $this->db->get()->result();
    }

    public function get_advance_salaries($date_from = null, $date_to = null)
    {
        $this->db->select('adv.*, s.name as staff_name,s.staff_id, s.photo,  sd.name as department_name');
        $this->db->from('advance_salary adv');
        $this->db->join('staff s', 'adv.staff_id = s.id', 'left');
        $this->db->join('staff_department sd', 's.department = sd.id', 'left');

        if ($date_from) {
            $this->db->where('adv.request_date >=', $date_from . ' 00:00:00');
        }

        if ($date_to) {
            $this->db->where('adv.request_date <=', $date_to . ' 23:59:59');
        }

        $this->db->order_by('adv.id', 'DESC');
        return $this->db->get()->result();
    }

    public function get_department_staff($department_id)
    {
        $this->db->select('id, name');
        $this->db->where('department', $department_id);
        return $this->db->get('staff')->result();
    }

   public function get_filtered_department_summary($department_id = null, $date_from = null, $date_to = null)
    {
        $this->db->select('d.id, d.title,
                          COUNT(t.id) as total_tasks,
                          SUM(CASE WHEN t.task_status = "completed" THEN 1 ELSE 0 END) as completed_tasks,
                          SUM(CASE WHEN t.task_status != "completed" AND t.task_status IS NOT NULL THEN 1 ELSE 0 END) as pending_tasks');
        $this->db->from('tracker_departments d');
        $this->db->join('tracker_issues t', 'd.identifier = t.department', 'left');
        $this->db->join('staff s', 't.assigned_to = s.id', 'left');
        $this->db->join('login_credential lc', 'lc.user_id = s.id', 'left');

        if ($department_id) {
            $this->db->where('t.department', $department_id);
        }

        if ($date_from) {
            $this->db->where('t.logged_at >=', $date_from);
        }

        if ($date_to) {
            $this->db->where('t.logged_at <=', $date_to . ' 23:59:59');
        }

        $this->db->group_start();
        $this->db->where_in('lc.role', [2, 3, 5, 8]);
        $this->db->or_where('s.department', 11);
        $this->db->or_where('t.id IS NULL', null, false);
        $this->db->group_end();
        $this->db->group_by('d.id, d.title');
        return $this->db->get()->result();
    }

    public function get_fund_summary_by_department()
    {
        $current_year = date('Y');

        $this->db->select('CONCAT(b.name, " - ", sd.name) as department_name,
                          COUNT(fr.id) as total_requests,
                          SUM(CASE WHEN fr.status = 2 THEN fr.amount ELSE 0 END) as approved_amount,
                          SUM(fr.amount) as total_amount');
        $this->db->from('staff_department sd');
        $this->db->join('branch b', 'sd.branch_id = b.id', 'left');
        $this->db->join('staff s', 'sd.id = s.department', 'left');
        $this->db->join('fund_requisition fr', 's.id = fr.staff_id AND YEAR(fr.request_date) = ' . $current_year, 'left');
        $this->db->group_by('sd.id, sd.name, b.name');
        $this->db->having('COUNT(fr.id) > 0');
        $this->db->order_by('approved_amount', 'DESC');
        $query = $this->db->get();

        if ($query === false) {
            return [];
        }

        return $query->result();
    }
}