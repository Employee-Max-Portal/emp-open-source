<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Rdc_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

	public function getTaskById($id, $user_id)
	{
		return $this->db
			->where('id', $id)
			->where('assigned_user', $user_id) // prevent accessing others' tasks
			->get('rdc_task') // adjust table name as needed
			->row_array();
	}


	public function getTasks($start_date = '', $end_date = '', $staff_id = null, $status = null)
	{
    $login_role = loggedin_role_id();

    // 🔹 Get HOD's department
    $hod_department = '';
    if ($login_role == 8) {
        $hod = $this->db->select('department')->where('id', $staff_id)->get('staff')->row();
        if ($hod) {
            $hod_department = $hod->department;
        }
    }

    $this->db->select('rt.*, sa.name, s.name, s.staff_id as employee_id');
    $this->db->from('rdc_task rt');
    $this->db->join('staff s', 's.id = rt.assigned_user', 'left');
    $this->db->join('staff sa', 'sa.id = rt.created_by', 'left');

	if (!empty($start_date) && !empty($end_date)) {
        $this->db->where('rt.created_at >=', $start_date . ' 00:00:00');
        $this->db->where('rt.created_at <=', $end_date . ' 23:59:59');
    }

    if (!empty($status)) {
        if ($status == 1) {
            // For pending: show tasks that are pending in either task_status OR verify_status
            $this->db->group_start();
            $this->db->where('rt.task_status', 1);
            $this->db->or_where('rt.verify_status', 1);
            $this->db->group_end();
        } else {
            $this->db->where('rt.task_status', $status);
        }
    }
  // 🔐 Restrict to staff’s own warnings if role not in [1, 2, 3, 5]
    if (!in_array(loggedin_role_id(), [1, 2, 3, 5, 8])) {
       /*  if ($login_role == 8 && !empty($hod_department)) {
            // HOD can see warnings in their department
            $this->db->where('s.department', $hod_department);
        } else { */
            // Others can only see their own
            $this->db->where('rt.assigned_user', $staff_id);
       // }
    }

	// 👇 Add ordering by newest warning first
    $this->db->where('rt.flag', 1);
    $this->db->order_by('rt.id', 'DESC');

    $tasks = $this->db->get()->result_array();

    // Get SOP data for each task
    foreach ($tasks as &$task) {
        // Initialize empty SOP fields
        $task['sop_title'] = '';
        $task['task_purpose'] = '';
        $task['instructions'] = '';
        $task['proof_required_text'] = '';
        $task['proof_required_image'] = '';
        $task['proof_required_file'] = '';
        $task['verifier_role'] = '';
        $task['expected_time'] = '';
        $task['executor_stage'] = '';
        $task['verifier_stage'] = '';

        if (!empty($task['sop_ids'])) {
            $sop_ids = json_decode($task['sop_ids'], true);
            if (is_array($sop_ids) && !empty($sop_ids)) {
                $this->db->select('GROUP_CONCAT(title SEPARATOR ", ") as sop_title, GROUP_CONCAT(DISTINCT task_purpose SEPARATOR "; ") as task_purpose, GROUP_CONCAT(DISTINCT instructions SEPARATOR "; ") as instructions, GROUP_CONCAT(DISTINCT proof_required_text SEPARATOR ", ") as proof_required_text, GROUP_CONCAT(DISTINCT proof_required_image SEPARATOR ", ") as proof_required_image, GROUP_CONCAT(DISTINCT proof_required_file SEPARATOR ", ") as proof_required_file, GROUP_CONCAT(DISTINCT verifier_role SEPARATOR ", ") as verifier_role, GROUP_CONCAT(DISTINCT expected_time SEPARATOR ", ") as expected_time, executor_stage, verifier_stage');
                $this->db->from('sop');
                $this->db->where_in('id', $sop_ids);
                $this->db->limit(1); // Get first SOP for executor_stage and verifier_stage
                $sop_data = $this->db->get()->row_array();

                if ($sop_data) {
                    $task = array_merge($task, $sop_data);
                }
            }
        }
    }

    return $tasks;
	}


	public function getTask_byID($id)
	{
		// Fetch the main task info first
		$task = $this->db
			->select('rt.*')
			->from('rdc_task rt')
			->where('rt.id', $id)
			->get()
			->row_array();

		// Check if task was found
		if (!$task) {
			return null;
		}

		// Now get SOP data if sop_ids exists
		if (!empty($task['sop_ids'])) {
			$sop_ids = json_decode($task['sop_ids'], true);
			if (is_array($sop_ids) && !empty($sop_ids)) {
				$this->db->select('GROUP_CONCAT(title SEPARATOR ", ") as sop_title, GROUP_CONCAT(DISTINCT task_purpose SEPARATOR "; ") as task_purpose, GROUP_CONCAT(DISTINCT proof_required_text SEPARATOR ", ") as proof_required_text, GROUP_CONCAT(DISTINCT proof_required_image SEPARATOR ", ") as proof_required_image, GROUP_CONCAT(DISTINCT proof_required_file SEPARATOR ", ") as proof_required_file, GROUP_CONCAT(DISTINCT verifier_role SEPARATOR ", ") as verifier_role, GROUP_CONCAT(DISTINCT expected_time SEPARATOR ", ") as expected_time, executor_stage, verifier_stage');
				$this->db->from('sop');
				$this->db->where_in('id', $sop_ids);
				$this->db->limit(1); // Get first SOP for executor_stage and verifier_stage
				$sop_data = $this->db->get()->row_array();

				if ($sop_data) {
					$task = array_merge($task, $sop_data);
				}
			}
		}

		return $task;
	}


public function save($post)
{
    // Check if random assignment or multi-user random
    $is_random = ($post['assigned_user'] === 'random');
    $is_multi_random = ($post['assigned_user'] === 'multi_random') && !empty($post['user_pool']) && is_array($post['user_pool']);

    // Build RDC payload
    $data = [
        'title'                => $post['title'] ?? null,
        'description'          => $post['description'] ?? null,
        'task_type'            => !empty($post['task_type']) ? (int)$post['task_type'] : null,
        'milestone'            => !empty($post['milestone']) ? (int)$post['milestone'] : null,
        'coordinator'          => !empty($post['coordinator']) ? (int)$post['coordinator'] : null,
        'initiatives'          => !empty($post['initiatives']) ? (int)$post['initiatives'] : null,
        'frequency'            => $post['frequency'],
        'assigned_user'        => ($is_random || $is_multi_random) ? null : ($post['assigned_user'] ?? null),
        'is_random_assignment' => ($is_random || $is_multi_random) ? 1 : 0,
        'user_pool'            => $is_multi_random ? json_encode($post['user_pool']) : null,
        'due_time'             => isset($post['due_time']) ? date('Y-m-d H:i:s', strtotime($post['due_time'])) : null,
        'verifier_due_time'    => isset($post['verifier_due_time']) ? date('Y-m-d H:i:s', strtotime($post['verifier_due_time'])) : null,
        'verifier_required'    => isset($post['verifier_required']) ? 1 : 0,
        'sop_ids'              => isset($post['sop_ids']) && is_array($post['sop_ids']) ? json_encode($post['sop_ids']) : json_encode([]),
        'is_proof_required'    => isset($post['is_proof_required']) ? (int)$post['is_proof_required'] : 0,
        'pre_reminder_enabled' => isset($post['pre_reminder_enabled']) ? (int)$post['pre_reminder_enabled'] : 0,
        'pre_reminder_minutes' => isset($post['pre_reminder_timing']) ? (int)$post['pre_reminder_timing'] : 0,
        'escalation_enabled'   => isset($post['escalation']) ? (int)$post['escalation'] : 0,
    ];

    // Build notifications payload only for relevant frequency fields
    $notification = [
        'frequency' => $post['frequency'] ?? null,
        'is_active' => 1,
    ];

    if (($post['frequency'] ?? null) === 'daily') {
        $notification['daily_time'] = $post['daily_time'] ?? null;
    } elseif ($post['frequency'] === 'weekly') {
        $notification['weekly_day']  = $post['weekly_day'] ?? null;
        $notification['weekly_time'] = $post['weekly_time'] ?? null;
    } elseif ($post['frequency'] === 'bimonthly') {
        $notification['bimonthly_day1'] = isset($post['bimonthly_day1']) ? (int)$post['bimonthly_day1'] : null;
        $notification['bimonthly_day2'] = isset($post['bimonthly_day2']) ? (int)$post['bimonthly_day2'] : null;
        $notification['bimonthly_time'] = $post['bimonthly_time'] ?? null;
    } elseif ($post['frequency'] === 'monthly') {
        $notification['monthly_day']  = isset($post['monthly_day']) ? (int)$post['monthly_day'] : null;
        $notification['monthly_time'] = $post['monthly_time'] ?? null;
    } elseif ($post['frequency'] === 'yearly') {
        $notification['yearly_month'] = isset($post['yearly_month']) ? (int)$post['yearly_month'] : null;
        $notification['yearly_day']   = isset($post['yearly_day']) ? (int)$post['yearly_day'] : null;
        $notification['yearly_time']  = $post['yearly_time'] ?? null;
    }

    if (empty($post['id'])) {
        // INSERT
        $data['created_by'] = get_loggedin_user_id();
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('rdc', $data);
        $insert_id = $this->db->insert_id();

        $notification['rdc_id']    = $insert_id;
        $notification['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('rdc_notifications', $notification);

        // Initialize rotation tracking if random assignment
        if ($is_random || $is_multi_random) {
            $this->init_rotation_tracking($insert_id);
        }

        // Create RDC task
        $this->create_rdc_task($insert_id, $data, $post);
    } else {
        // UPDATE RDC
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $post['id'])->update('rdc', $data);

        $this->db->where('rdc_id', $post['id'])->update('rdc_notifications', $notification);

        // Create RDC task on template update as well
        $this->create_rdc_task($post['id'], $data, $post);
    }
}

public function get_random_user_from_pool($rdc_id)
{
    $rdc = $this->db->select('user_pool, is_random_assignment')
                    ->where('id', $rdc_id)
                    ->get('rdc')
                    ->row_array();

    if (!$rdc || !$rdc['is_random_assignment']) {
        return null;
    }

    // If user_pool exists, select randomly from it
    if (!empty($rdc['user_pool'])) {
        $user_pool = json_decode($rdc['user_pool'], true);
        if (is_array($user_pool) && !empty($user_pool)) {
            return $user_pool[array_rand($user_pool)];
        }
    }

    // Fallback to existing random logic if no user pool
    return $this->get_random_user();
}

public function getRDC_tasks($start_date = '', $end_date = '')
{
    $this->db->select('rt.*, sa.name, s.name, s.staff_id as employee_id');
    $this->db->from('rdc rt');
    $this->db->join('staff s', 's.id = rt.assigned_user', 'left');
    $this->db->join('staff sa', 'sa.id = rt.created_by', 'left');

    if (!empty($start_date) && !empty($end_date)) {
        $this->db->where('rt.created_at >=', $start_date);
        $this->db->where('rt.created_at <=', $end_date);
    }

    // Role-based access control
    $role_id = loggedin_role_id();
    $allowed_roles = [1, 2, 3, 5, 8]; // Roles that can see all data

    if (!in_array($role_id, $allowed_roles)) {
        // For other roles, only show data from their department
		$staff_id = get_loggedin_user_id();
		$this->db->where('rt.assigned_user', $staff_id);
    }
	$this->db->order_by('rt.id', 'DESC');
    return $this->db->get()->result_array();
}

public function getEscalatedRDC_tasks($start_date = '', $end_date = '')
{
    $this->db->select('rt.*, sa.name, s.name, s.staff_id as employee_id, ertel.name as escalated_person, rtel.action_type, rtel.remarks as escaltion_reason');
    $this->db->from('rdc_task rt');
    $this->db->join('staff s', 's.id = rt.assigned_user', 'left');
    $this->db->join('staff sa', 'sa.id = rt.created_by', 'left');
    $this->db->join('rdc_task_escalation_log rtel', 'rtel.task_id = rt.id', 'left');
    $this->db->join('staff ertel', 'ertel.id = rtel.staff_id', 'left');

    // Filter: Escalated tasks only
    $this->db->where('(rt.is_escalated_executor = 1 OR rt.is_escalated_verifier = 1)', null, false);

    // Optional date filter (on created_at)
    if (!empty($start_date) && !empty($end_date)) {
        $this->db->where('rt.created_at >=', $start_date);
        $this->db->where('rt.created_at <=', $end_date);
    }

    // Role-based access control
    $role_id = loggedin_role_id();
    $allowed_roles = [1, 2, 3, 5, 8]; // Admins, Managers, Dept Heads

    if (!in_array($role_id, $allowed_roles)) {
        $staff_id = get_loggedin_user_id();
        $this->db->where('rt.assigned_user', $staff_id);
    }
	$this->db->where('rt.flag', 1);
    $this->db->order_by('rt.id', 'DESC');
    $tasks = $this->db->get()->result_array();

    // Get SOP data for each task
    foreach ($tasks as &$task) {
        if (!empty($task['sop_ids'])) {
            $sop_ids = json_decode($task['sop_ids'], true);
            if (is_array($sop_ids) && !empty($sop_ids)) {
                $this->db->select('GROUP_CONCAT(title SEPARATOR ", ") as sop_title, GROUP_CONCAT(DISTINCT task_purpose SEPARATOR "; ") as task_purpose, GROUP_CONCAT(DISTINCT instructions SEPARATOR "; ") as instructions, GROUP_CONCAT(DISTINCT proof_required_text SEPARATOR ", ") as proof_required_text, GROUP_CONCAT(DISTINCT proof_required_image SEPARATOR ", ") as proof_required_image, GROUP_CONCAT(DISTINCT proof_required_file SEPARATOR ", ") as proof_required_file, GROUP_CONCAT(DISTINCT verifier_role SEPARATOR ", ") as verifier_role, GROUP_CONCAT(DISTINCT expected_time SEPARATOR ", ") as expected_time');
                $this->db->from('sop');
                $this->db->where_in('id', $sop_ids);
                $sop_data = $this->db->get()->row_array();

                if ($sop_data) {
                    $task = array_merge($task, $sop_data);
                }
            }
        }
    }

    return $tasks;
}


// 1.1 Today's tasks
public function get_todays_tasks($staff_id)
{
    $login_role = loggedin_role_id();
    $today = date('Y-m-d');

    $staff_ids = [$staff_id];

    // For (HOD) 8 (In-charge), show all tasks in their department
/*     if (in_array($login_role, [8])) {
        $staff = $this->db->select('department')->where('id', $staff_id)->get('staff')->row();
        if (!$staff) return [];

        $department_id = $staff->department;

        $staff_list = $this->db->select('id')->from('staff')->where('department', $department_id)->get()->result_array();
        $staff_ids = array_column($staff_list, 'id');

        if (empty($staff_ids)) return [];
    }
 */
    $this->db->select('*');
    $this->db->from('rdc_task');
    if (!in_array($login_role, [1, 2, 3, 5, 8])) {
        $this->db->where_in('assigned_user', $staff_ids);
    }
    $this->db->where('DATE(created_at)', $today);
	$this->db->where('flag', 1);
    return $this->db->get()->result_array();
}


// 1.2 Pending verifications
public function get_user_pending_verifications($staff_id)
{
    $login_role = loggedin_role_id();

    $staff_ids = [$staff_id];

    // For HOD (5) or In-Charge (8), get all staff in same department
/*     if (in_array($login_role, [8])) {
        $staff = $this->db->select('department')->where('id', $staff_id)->get('staff')->row();
        if (!$staff) return [];

        $department_id = $staff->department;

        $staff_list = $this->db->select('id')->from('staff')->where('department', $department_id)->get()->result_array();
        $staff_ids = array_column($staff_list, 'id');

        if (empty($staff_ids)) return [];
    } */

    $this->db->select('*');
    $this->db->from('rdc_task');
    if (!in_array($login_role, [1, 2, 3, 5, 8])) {
        $this->db->where_in('assigned_user', $staff_ids);
    }
    $this->db->where('verify_status', 1); // pending
    $this->db->where('task_status', 1);   // submitted

    $this->db->where('flag', 1);

    return $this->db->get()->result_array();
}


// 1.3 Monthly discipline score (e.g. % of completed on-time tasks)
public function get_user_discipline_score($staff_id)
{
    $month = date('Y-m');
    $login_role = loggedin_role_id();

    $staff_ids = [$staff_id];

    // If user is HOD (5) or In-Charge (8), include all in department
   /*  if (in_array($login_role, [8])) {
        $staff = $this->db->select('department')->where('id', $staff_id)->get('staff')->row();
        if (!$staff) return 0;

        $department_id = $staff->department;

        $staff_list = $this->db->select('id')->from('staff')->where('department', $department_id)->get()->result_array();
        $staff_ids = array_column($staff_list, 'id');

        if (empty($staff_ids)) return 0;
    } */

    // Total assigned tasks this month
    $this->db->from('rdc_task');
    if (!in_array($login_role, [1, 2, 3, 5, 8])) {
        $this->db->where_in('assigned_user', $staff_ids);
    }
    $this->db->like('created_at', $month, 'after');
    $total = $this->db->count_all_results();

    // Total completed (task_status = 2)
    $this->db->from('rdc_task');
        if (!in_array($login_role, [1, 2, 3, 5, 8])) {
        $this->db->where_in('assigned_user', $staff_ids);
    }
    $this->db->like('created_at', $month, 'after');
    $this->db->where('task_status', 2);

	$this->db->where('flag', 1);
    $completed = $this->db->count_all_results();

    return ($total > 0) ? round(($completed / $total) * 100, 2) : 0;
}


// 2.1 Get team members' task statuses
public function get_team_task_status($supervisor_id)
{
    $login_role = loggedin_role_id();
    $current_month = date('Y-m');

    // Step 1: Get department of supervisor
    $staff = $this->db->select('department')->where('id', $supervisor_id)->get('staff')->row();
    if (!$staff) return [];

    $department_id = $staff->department;

    // Step 2: Fetch task status for team members
    $this->db->select('s.id as staff_id, s.staff_id as employee_id, s.name, rt.task_status, rt.verify_status, rt.created_at')
             ->from('staff s')
             ->join('rdc_task rt', 'rt.assigned_user = s.id', 'left')
             ->like('rt.created_at', $current_month, 'after');

    // Step 3: Apply department filter for non-admin roles
    if (!in_array($login_role, [1, 2, 3, 5, 8])) {
        $this->db->where('s.department', $department_id);
    }

    $this->db->where('rt.flag', 1);
    $this->db->order_by('rt.id', 'DESC');

    return $this->db->get()->result_array();
}


// 2.2 Awaiting verifications by team
public function get_team_pending_verifications($supervisor_id)
{
    $login_role = loggedin_role_id();

    // Regular users have no access
    if ($login_role == 4) {
        return [];
    }

    $this->db->select('rt.*')
        ->from('rdc_task rt')
        ->join('staff s', 's.id = rt.assigned_user')
        ->where('rt.verify_status', 1)
        ->where('rt.task_status', 2)
        ->where('rt.flag', 1);

    // For admins/managers → show all
    if (!in_array($login_role, [1, 2, 3, 5, 8])) {
        // Get supervisor's department
        $staff = $this->db->select('department')->where('id', $supervisor_id)->get('staff')->row();
        if (!$staff) return [];

        $department_id = $staff->department;
        $this->db->where('s.department', $department_id);
    }

    return $this->db->get()->result_array();
}


// 2.3 Escalated issues under supervisor
public function get_team_escalated_tasks($supervisor_id)
{
    $login_role = loggedin_role_id();

    // Role 4 has no access
    if ($login_role == 4) {
        return [];
    }

    $department_id = null;

    // For non-admin roles, fetch department of supervisor
    if (!in_array($login_role, [1, 2, 3, 5, 8])) {
        $staff = $this->db->select('department')->where('id', $supervisor_id)->get('staff')->row();
        if (!$staff) return [];
        $department_id = $staff->department;
    }

    // Main query: fetch escalated tasks
    $this->db->select('rt.*')
             ->from('rdc_task rt')
             ->join('staff s', 's.id = rt.assigned_user', 'left')
             ->group_start()
                 ->where('rt.is_escalated_executor', 1)
                 ->or_where('rt.is_escalated_verifier', 1)
             ->group_end();

    // If department scope is applicable
    if (!is_null($department_id)) {
        $this->db->where('s.department', $department_id);
    }
    $this->db->where('rt.flag', 1);
    return $this->db->get()->result_array();
}

// 3.1 Get all escalated tasks
public function get_all_escalated_tasks()
{
    $this->db->select('*')
        ->from('rdc_task')
        ->group_start()
            ->where('is_escalated_executor', 1)
            ->or_where('is_escalated_verifier', 1)
        ->group_end()
		->where('flag', 1);
    return $this->db->get()->result_array();
}

// 3.2 Compliance % per department
public function get_department_compliance()
{
    $sql = "
        SELECT
            d.name AS department_name,
            COUNT(rt.id) AS total_tasks,
            SUM(rt.task_status = 2 AND rt.verify_status = 2) AS compliant_tasks,
            ROUND(SUM(rt.task_status = 2 AND rt.verify_status = 2) / NULLIF(COUNT(rt.id), 0) * 100, 2) AS compliance_percent
        FROM rdc_task rt
        JOIN staff s ON s.id = rt.assigned_user
        JOIN staff_department d ON d.id = s.department
        WHERE rt.flag = 1
          AND MONTH(rt.created_at) = MONTH(CURDATE())
          AND YEAR(rt.created_at) = YEAR(CURDATE())
        GROUP BY d.id
    ";

    return $this->db->query($sql)->result_array();
}


// 3.3 Salary lock alerts
public function get_salary_block_alerts()
{
    $this->db->select('sb.*, s.name, rt.title');
    $this->db->from('salary_blocks sb');
    $this->db->join('staff s', 's.id = sb.staff_id', 'left');
    $this->db->join('rdc_task rt', 'rt.id = sb.task_id', 'left');
    $this->db->where('sb.status', 1); // active blocks only
    $this->db->where('rt.flag', 1);
    return $this->db->get()->result_array();
}


// Total tasks assigned to the current user
public function get_total_tasks($staff_id)
{
    $login_role = loggedin_role_id();
    $current_month = date('Y-m');

    // FIRST: Get department if needed
    $staff_ids = [$staff_id]; // default to self
/*     if (in_array($login_role, [8])) {
        $staff = $this->db->select('department')->where('id', $staff_id)->get('staff')->row();
        if (!$staff) return 0;

        $department_id = $staff->department;

        // Get all users in that department
        $staff_list = $this->db->select('id')->from('staff')->where('department', $department_id)->get()->result_array();
        $staff_ids = array_column($staff_list, 'id');
        if (empty($staff_ids)) return 0;
    } */

    // THEN: Build main query
    $this->db->from('rdc_task');
   // $this->db->like('rdc_task.created_at', $current_month, 'after');
	if (!in_array($login_role, [1, 2, 3, 5, 8])) {
		$this->db->where_in('assigned_user', $staff_ids);
    }

    $this->db->where('flag', 1);
    return $this->db->count_all_results();
}


// Tasks assigned to user complete verification (status=2, verify=2)
public function get_total_verified_tasks($staff_id)
{
    $login_role = loggedin_role_id();

    // Default to own ID
    $staff_ids = [$staff_id];

    /* if (in_array($login_role, [8])) {
        // Get department of the supervisor
        $staff = $this->db->select('department')->where('id', $staff_id)->get('staff')->row();
        if (!$staff) return 0;

        $department_id = $staff->department;

        // Get all staff IDs in that department
        $staff_list = $this->db->select('id')->from('staff')->where('department', $department_id)->get()->result_array();
        $staff_ids = array_column($staff_list, 'id');

        if (empty($staff_ids)) return 0;
    } */

    // Now filter RDC tasks
    $this->db->from('rdc_task');
    if (!in_array($login_role, [1, 2, 3, 5, 8])) {
        $this->db->where_in('assigned_user', $staff_ids);
    }
    $this->db->where('task_status', 2); // completed
    $this->db->where('verify_status', 2); // verified

    $this->db->where('flag', 1);
    return $this->db->count_all_results();
}


// Tasks assigned to user but pending verification (status=2, verify=1)
public function get_total_unverified_tasks($staff_id)
{
    $login_role = loggedin_role_id();

    // Default: only own ID
    $staff_ids = [$staff_id];

  /*   if (in_array($login_role, [8])) {
        // Get supervisor's department
        $staff = $this->db->select('department')->where('id', $staff_id)->get('staff')->row();
        if (!$staff) return 0;

        $department_id = $staff->department;

        // Get all staff IDs in department
        $staff_list = $this->db->select('id')->from('staff')->where('department', $department_id)->get()->result_array();
        $staff_ids = array_column($staff_list, 'id');

        if (empty($staff_ids)) return 0;
    }
 */
    // Filter RDC tasks
    $this->db->from('rdc_task');
    if (!in_array($login_role, [1, 2, 3, 5, 8])) {
        $this->db->where_in('assigned_user', $staff_ids);
    }
    $this->db->where('task_status', 2); // completed
    $this->db->where('verify_status', 1); // pending verification

    $this->db->where('flag', 1);
    return $this->db->count_all_results();
}


// Tasks under your team (if you're a verifier/supervisor)
public function get_total_tasks_under_verifier($verifier_id)
{
    $login_role = loggedin_role_id();

    // 🟢 If role is 1 or 2 → show all
    if (in_array($login_role, [1, 2])) {
        return $this->db->count_all('rdc_task');
    }

    // 🔴 If role is 4 → not allowed to see tasks under others
    if ($login_role == 4) {
        return 0;
    }

    // 🟡 For supervisor-level roles (3, 5, 8)
    // Step 1: Get current department
    $staff = $this->db->select('department')->where('id', $verifier_id)->get('staff')->row();
    if (!$staff) return 0;
    $department_id = $staff->department;

    // Step 2: Fetch tasks assigned to staff of same department + where verifier_role matches
    $this->db->select('rt.id')
        ->from('rdc_task rt')
        ->join('staff s', 's.id = rt.assigned_user')
        //->join('sop sop', 'sop.id = rt.sop_id')
		->join('sop sp', 'JSON_CONTAINS(rt.sop_ids, CAST(sp.id AS JSON))', 'left')

        ->where('rt.flag', 1)
        ->where('s.department', $department_id)
        ->where("FIND_IN_SET(" . $this->db->escape_str($login_role) . ", sp.verifier_role) >", 0);

    return $this->db->count_all_results();
}

// Initialize rotation tracking for a template
public function init_rotation_tracking($rdc_template_id)
{
    // Get the RDC template to check if it has a user pool
    $rdc = $this->db->select('user_pool, is_random_assignment')
                    ->where('id', $rdc_template_id)
                    ->get('rdc')
                    ->row();

    $staff_ids = [];

    if ($rdc && $rdc->is_random_assignment) {
        if (!empty($rdc->user_pool)) {
            // Use the specific user pool
            $user_pool = json_decode($rdc->user_pool, true);
            if (is_array($user_pool) && !empty($user_pool)) {
                $staff_ids = $user_pool;
            }
        } else {
            // Use all staff for rotation
            $staff_list = $this->app_lib->getSelectList('staff');
            // Remove empty/null "Select" option if exists
            unset($staff_list['']);
            // Remove ID 1 (superadmin) if it exists
            unset($staff_list[1]);
            // Get only the keys (staff IDs)
            $staff_ids = array_keys($staff_list);
        }
    }

    if (!empty($staff_ids)) {
        $rotation_data = [
            'rdc_template_id' => $rdc_template_id,
            'staff_pool' => json_encode($staff_ids),
            'current_index' => 0,
            'last_assigned_staff_id' => null
        ];

        $this->db->insert('rdc_rotation_tracking', $rotation_data);
    }
}

// Get next staff in rotation
public function get_next_rotation_staff($rdc_template_id)
{
    $rotation = $this->db->get_where('rdc_rotation_tracking', ['rdc_template_id' => $rdc_template_id])->row();

    if (!$rotation) {
        return null;
    }

    $staff_pool = json_decode($rotation->staff_pool, true);
    if (empty($staff_pool)) {
        return null;
    }

    $current_index = $rotation->current_index;
    $next_staff_id = $staff_pool[$current_index];

    // Update rotation tracking
    $next_index = ($current_index + 1) % count($staff_pool);
    $this->db->where('rdc_template_id', $rdc_template_id)
        ->update('rdc_rotation_tracking', [
            'current_index' => $next_index,
            'last_assigned_staff_id' => $next_staff_id,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

    return $next_staff_id;
}

// Get pending tasks report grouped by person
public function getPendingTasksReport($start_date = '', $end_date = '')
{
    $login_role = loggedin_role_id();
    $staff_id = get_loggedin_user_id();

    // Build the query
    $this->db->select('rt.*, s.name as staff_name, s.staff_id as employee_id, s.department, sd.name as department_name');
    $this->db->from('rdc_task rt');
    $this->db->join('staff s', 's.id = rt.assigned_user', 'left');
    $this->db->join('staff_department sd', 'sd.id = s.department', 'left');

    // Filter for pending tasks only
    $this->db->where('rt.task_status', 1); // 1 = pending
    $this->db->where('rt.flag', 1);

    // Date filter if provided
    if (!empty($start_date) && !empty($end_date)) {
        $this->db->where('rt.created_at >=', $start_date . ' 00:00:00');
        $this->db->where('rt.created_at <=', $end_date . ' 23:59:59');
    }

    // Role-based access control
    if (!in_array($login_role, [1, 2, 3, 5]) && !in_array($staff_id, [15])) {
        if ($login_role == 8) {
            // HOD can see tasks in their department
            $hod = $this->db->select('department')->where('id', $staff_id)->get('staff')->row();
            if ($hod) {
                $this->db->where('s.department', $hod->department);
            }
        } else {
            // Others can only see their own tasks
            $this->db->where('rt.assigned_user', $staff_id);
        }
    }

    $this->db->order_by('s.name', 'ASC');
    $this->db->order_by('rt.due_time', 'ASC');

    $tasks = $this->db->get()->result_array();

    // Group tasks by person
    $grouped_tasks = [];
    foreach ($tasks as $task) {
        $person_key = $task['assigned_user'];
        if (!isset($grouped_tasks[$person_key])) {
            $grouped_tasks[$person_key] = [
                'staff_name' => $task['staff_name'],
                'employee_id' => $task['employee_id'],
                'department_name' => $task['department_name'],
                'tasks' => [],
                'total_pending' => 0
            ];
        }
        $grouped_tasks[$person_key]['tasks'][] = $task;
        $grouped_tasks[$person_key]['total_pending']++;
    }

    return $grouped_tasks;
}

// Get task statistics by status
public function getTaskStats($start_date = '', $end_date = '', $staff_id = null)
{
    $login_role = loggedin_role_id();

    $this->db->select('task_status, COUNT(*) as count');
    $this->db->from('rdc_task rt');
    $this->db->join('staff s', 's.id = rt.assigned_user', 'left');

    if (!empty($start_date) && !empty($end_date)) {
        $this->db->where('rt.created_at >=', $start_date . ' 00:00:00');
        $this->db->where('rt.created_at <=', $end_date . ' 23:59:59');
    }

    if (!in_array($login_role, [1, 2, 3, 5, 8])) {
        $this->db->where('rt.assigned_user', $staff_id);
    }

    $this->db->where('rt.flag', 1);
    $this->db->group_by('rt.task_status');
    $result = $this->db->get()->result_array();

    $stats = ['pending' => 0, 'completed' => 0, 'canceled' => 0, 'hold' => 0];
    foreach ($result as $row) {
        switch ($row['task_status']) {
            case 1: $stats['pending'] = $row['count']; break;
            case 2: $stats['completed'] = $row['count']; break;
            case 3: $stats['canceled'] = $row['count']; break;
            case 4: $stats['hold'] = $row['count']; break;
        }
    }

    // Get additional pending count from verify_status = 1
    $this->db->select('COUNT(*) as count');
    $this->db->from('rdc_task rt');
    $this->db->join('staff s', 's.id = rt.assigned_user', 'left');

    if (!empty($start_date) && !empty($end_date)) {
        $this->db->where('rt.created_at >=', $start_date . ' 00:00:00');
        $this->db->where('rt.created_at <=', $end_date . ' 23:59:59');
    }

    if (!in_array($login_role, [1, 2, 3, 5, 8])) {
        $this->db->where('rt.assigned_user', $staff_id);
    }

    $this->db->where('rt.verify_status', 1);
    $this->db->where('rt.task_status !=', 1); // Don't double count
    $this->db->where('rt.flag', 1);
    $verify_pending = $this->db->get()->row()->count;

    $stats['pending'] += $verify_pending;

    return $stats;
}

// Soft delete RDC task
public function softDeleteTask($task_id)
{
    $data = [
        'flag' => 0,
        'deleted_by' => get_loggedin_user_id(),
        'deleted_at' => date('Y-m-d H:i:s')
    ];

    return $this->db->where('id', (int)$task_id)->update('rdc_task', $data);
}

// Get deleted RDC tasks
public function getDeletedTasks($start_date = '', $end_date = '')
{
    $login_role = loggedin_role_id();

    $this->db->select('rt.*, s.name as assigned_name, s.staff_id as employee_id, sd.name as department_name, ds.name as deleted_by_name')
        ->from('rdc_task rt')
        ->join('staff s', 's.id = rt.assigned_user', 'left')
        ->join('staff_department sd', 'sd.id = s.department', 'left')
        ->join('staff ds', 'ds.id = rt.deleted_by', 'left')
        ->where('rt.flag', 0);

    if (!empty($start_date) && !empty($end_date)) {
        $this->db->where('rt.deleted_at >=', $start_date . ' 00:00:00');
        $this->db->where('rt.deleted_at <=', $end_date . ' 23:59:59');
    }

    // Role-based access control
    if (!in_array($login_role, [1, 2, 3, 5, 8])) {
      /*   if ($login_role == 8) {
            $hod = $this->db->select('department')->where('id', get_loggedin_user_id())->get('staff')->row();
            if ($hod) {
                $this->db->where('s.department', $hod->department);
            }
        } else { */
            $this->db->where('rt.assigned_user', get_loggedin_user_id());
       /*  } */
    }

    $this->db->order_by('rt.deleted_at', 'DESC');
    return $this->db->get()->result_array();
}

// Get random user for assignment
private function get_random_user()
{
    $staff_list = $this->app_lib->getSelectList('staff');
    unset($staff_list['']);
    unset($staff_list[1]);
    $staff_ids = array_keys($staff_list);
    return !empty($staff_ids) ? $staff_ids[array_rand($staff_ids)] : null;
}


// Create RDC task when template is created
public function create_rdc_task_back($rdc_id, $rdc_data, $post)
{
    // Check if today is a holiday
    $today = date('Y-m-d');
    $holiday_check = $this->db->where('type', 'holiday')
        ->where('start_date <=', $today)
        ->where('end_date >=', $today)
        ->get('event')->row();

    if ($holiday_check) {
        return; // Skip task creation during holidays
    }

    // Determine assigned user
    $assigned_user = null;
    if (!empty($rdc_data['assigned_user'])) {
        $assigned_user = $rdc_data['assigned_user'];
    } elseif ($rdc_data['is_random_assignment']) {
        if (!empty($rdc_data['user_pool'])) {
            $user_pool = json_decode($rdc_data['user_pool'], true);
            $assigned_user = $user_pool[array_rand($user_pool)];
        } else {
            $assigned_user = $this->get_random_user();
        }
    }

    if ($assigned_user) {
        // Fetch SOP data (handle multiple SOPs)
        $sop_ids = !empty($rdc_data['sop_ids']) ? json_decode($rdc_data['sop_ids'], true) : [];
        if (empty($sop_ids)) return;

        // Get all SOPs and combine their stages
        $sops = $this->db->where_in('id', $sop_ids)->get('sop')->result_array();
        if (empty($sops)) return;

        $executor_stages = [];
        $verifier_stages = [];
        $verifier_roles = [];
        $total_expected_time = 0;

        foreach ($sops as $sop) {
            if (!empty($sop['executor_stage'])) {
                $executor_stages[] = $sop['executor_stage'];
            }
            if (!empty($sop['verifier_stage'])) {
                $verifier_stages[] = $sop['verifier_stage'];
            }
            if (!empty($sop['verifier_role'])) {
                $roles = explode(',', $sop['verifier_role']);
                $verifier_roles = array_merge($verifier_roles, $roles);
            }

            if (!empty($sop['expected_time'])) {
                $time_str = strtolower(trim($sop['expected_time'])); // normalize
                $hours = 0;

                // Extract number
                preg_match('/\d+/', $time_str, $matches);
                $value = isset($matches[0]) ? (float)$matches[0] : 0;

                // Detect unit
                if (strpos($time_str, 'day') !== false) {
                    $hours = $value * 24;
                } elseif (strpos($time_str, 'hour') !== false) {
                    $hours = $value;
                } elseif (strpos($time_str, 'minute') !== false) {
                    $hours = $value / 60;
                } else {
                    // fallback: assume it's already hours
                    $hours = $value;
                }

                $total_expected_time += $hours;
            }
        }

        $combined_executor_stages = json_encode(array_unique($executor_stages));
        $combined_verifier_stages = json_encode(array_unique($verifier_stages));
        $combined_verifier_roles = implode(',', array_unique(array_filter($verifier_roles)));

        // Calculate due times based on frequency
        $due_time = $rdc_data['due_time'];
        $verifier_due_time = $rdc_data['verifier_due_time'];

        if ($rdc_data['frequency'] === 'daily') {
            // For daily tasks: use today's date with original time
            $original_time = date('H:i:s', strtotime($rdc_data['due_time']));
            $due_time = date('Y-m-d') . ' ' . $original_time;

            // Verifier due time: 3 days from today with same time
            $verifier_original_time = date('H:i:s', strtotime($rdc_data['verifier_due_time']));
            $verifier_due_time = date('Y-m-d', strtotime('+3 days')) . ' ' . $verifier_original_time;
        } elseif ($rdc_data['frequency'] === 'weekly') {
            // For weekly tasks: use today's date with original time
            $original_time = date('H:i:s', strtotime($rdc_data['due_time']));
            $due_time = date('Y-m-d', strtotime('+1 days')) . ' ' . $original_time;

            // Verifier due time: 3 days from today with same time
            $verifier_original_time = date('H:i:s', strtotime($rdc_data['verifier_due_time']));
            $verifier_due_time = date('Y-m-d', strtotime('+3 days')) . ' ' . $verifier_original_time;
        } elseif ($rdc_data['frequency'] === 'bimonthly') {
            // For bimonthly tasks: use today's date with original time
            $original_time = date('H:i:s', strtotime($rdc_data['due_time']));
            $due_time = date('Y-m-d', strtotime('+2 days')) . ' ' . $original_time;

            // Verifier due time: 3 days from today with same time
            $verifier_original_time = date('H:i:s', strtotime($rdc_data['verifier_due_time']));
            $verifier_due_time = date('Y-m-d', strtotime('+3 days')) . ' ' . $verifier_original_time;
        } elseif ($rdc_data['frequency'] === 'monthly') {
            // For monthly tasks: use today's date with original time
            $original_time = date('H:i:s', strtotime($rdc_data['due_time']));
            $due_time = date('Y-m-d', strtotime('+5 days')) . ' ' . $original_time;

            // Verifier due time: 3 days from today with same time
            $verifier_original_time = date('H:i:s', strtotime($rdc_data['verifier_due_time']));
            $verifier_due_time = date('Y-m-d', strtotime('+5 days')) . ' ' . $verifier_original_time;
        } elseif ($rdc_data['frequency'] === 'yearly') {
            // For yearly tasks: use today's date with original time
            $original_time = date('H:i:s', strtotime($rdc_data['due_time']));
            $due_time = date('Y-m-d', strtotime('+5 days')) . ' ' . $original_time;

            // Verifier due time: 10 days from today with same time
            $verifier_original_time = date('H:i:s', strtotime($rdc_data['verifier_due_time']));
            $verifier_due_time = date('Y-m-d', strtotime('+10 days')) . ' ' . $verifier_original_time;
        }

        // Check if verifier is required
        $verifier_required = isset($rdc_data['verifier_required']) && $rdc_data['verifier_required'] == 1;

        $task_data = [
            'rdc_id' => $rdc_id,
            'title' => $rdc_data['title'],
            'description' => $rdc_data['description'],
            'frequency' => $rdc_data['frequency'],
            'assigned_user' => $assigned_user,
            'is_random_assignment' => $rdc_data['is_random_assignment'],
            'user_pool' => $rdc_data['user_pool'],
            'due_time' => $due_time,
            'verifier_due_time' => $verifier_required ? $verifier_due_time : null,
            'verifier_required' => $rdc_data['verifier_required'] ?? 1,
            'sop_id' => $sop_ids[0],
            'sop_ids' => json_encode($sop_ids),
            'is_proof_required' => $rdc_data['is_proof_required'],
            'pre_reminder_enabled' => $rdc_data['pre_reminder_enabled'] ?? 0,
            'pre_reminder_minutes' => $rdc_data['pre_reminder_minutes'] ?? 0,
            'escalation_enabled' => $rdc_data['escalation_enabled'] ?? 0,
            'executor_stages' => $combined_executor_stages,
            'verifier_stages' => $verifier_required ? $combined_verifier_stages : null,
            'verified_by' => $verifier_required ? $combined_verifier_roles : null,
            'verify_status' => $verifier_required ? 1 : 4,
            'task_status' => 1,
            'flag' => 1,
            'created_by' => get_loggedin_user_id(),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('rdc_task', $task_data);

        // Add to tracker_issues
        $rdc_dept = $this->db->where('identifier', 'RDC')->get('tracker_departments')->row();
        if (!$rdc_dept) {
            $this->db->insert('tracker_departments', [
                'title' => 'RDC Tasks',
                'identifier' => 'RDC',
                'description' => 'Recurring Discipline & Compliance Tasks',
                'default_status' => 'todo',
                'is_private' => 0,
                'auto_join' => 1,
                'owner_id' => 1,
                'assigned_issuer' => 1
            ]);
            $rdc_dept_id = $this->db->insert_id();
        } else {
            $rdc_dept_id = $rdc_dept->id;
        }

		// Get max number for RDC prefix
		$this->db->select('MAX(CAST(SUBSTRING_INDEX(unique_id, "-", -1) AS UNSIGNED)) as max_num');
		$this->db->like('unique_id', 'RDC-', 'after');
		$this->db->from('tracker_issues');
		$row = $this->db->get()->row();

		$max_num = $row && $row->max_num ? (int)$row->max_num : 0;

		// Generate next unique ID
		$tracker_unique_id = 'RDC-' . ($max_num + 1);


        $this->db->insert('tracker_issues', [
            'created_by' => get_loggedin_user_id(),
            'unique_id' => $tracker_unique_id,
            'department' => 'RDC',
            'task_title' => $rdc_data['title'],
            'task_description' => $rdc_data['description'],
            'task_status' => 'todo',
            'priority_level' => 'Medium',
            'assigned_to' => $assigned_user,
            'estimation_time' => $total_expected_time > 0 ? $total_expected_time : null,
            'estimated_end_time' => date('Y-m-d 23:59:59', strtotime($due_time)),
            'logged_at' => date('Y-m-d H:i:s')
        ]);
        $tracker_issue_id = $this->db->insert_id();

        // Add to staff_task_log
        $this->db->insert('staff_task_log', [
            'staff_id' => $assigned_user,
            'location' => 'RDC Task',
            'task_title' => $rdc_data['title'],
            'start_time' => $due_time,
            'task_status' => 'In Progress',
            'logged_at' => date('Y-m-d H:i:s'),
            'tracker_id' => $tracker_issue_id
        ]);
    }
}


// Create RDC task when template is created
public function create_rdc_task($rdc_id, $rdc_data, $post)
{
    date_default_timezone_set('Asia/Dhaka');
    $today = date('Y-m-d');
    $weekday = date('l');

    // Ensure date format is correct
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $today)) {
        $today = date('Y-m-d');
    }

    // Function to get next working day
    $get_next_working_day = function($date) {
        $check_date = new DateTime($date);
        while (true) {
            $day_name = $check_date->format('l');
            $date_str = $check_date->format('Y-m-d');

            if ($day_name === 'Friday' || $day_name === 'Saturday') {
                $check_date->modify('+1 day');
                continue;
            }

            $holiday_query = $this->db->where('type', 'holiday')
                ->where('start_date <=', $date_str)
                ->where('end_date >=', $date_str)
                ->get('event');

            if ($holiday_query === false) {
                return $date_str;
            }

            $holiday = $holiday_query->row();

            if (!$holiday) {
                return $date_str;
            }

            $check_date->modify('+1 day');
        }
    };

    // Set due date to next working day if today is weekend/holiday
    $due_date = $today;
    if ($weekday === 'Friday' || $weekday === 'Saturday') {
        $due_date = $get_next_working_day($today);
    } else {
        $holiday_query = $this->db->where('type', 'holiday')
            ->where('start_date <=', $today)
            ->where('end_date >=', $today)
            ->get('event');

        if ($holiday_query === false) {
            $holiday_check = null;
        } else {
            $holiday_check = $holiday_query->row();
        }

        if ($holiday_check) {
            $due_date = $get_next_working_day($today);
        }
    }

    // Determine assigned user
    $assigned_user = null;
    if (!empty($rdc_data['assigned_user'])) {
        $assigned_user = $rdc_data['assigned_user'];
    } elseif ($rdc_data['is_random_assignment']) {
        if (!empty($rdc_data['user_pool'])) {
            $user_pool = json_decode($rdc_data['user_pool'], true);
            if (is_array($user_pool) && !empty($user_pool)) {
                $user_pool = array_filter($user_pool, function($id) {
                    return is_numeric($id) && $id > 0;
                });

                if (!empty($user_pool)) {
                    $assigned_user = $user_pool[array_rand($user_pool)];

                    // Check if assigned staff is on leave today
                    $leave_check = $this->db->where('user_id', $assigned_user)
                        ->where('start_date <=', $today)
                        ->where('end_date >=', $today)
                        ->where('status', 2)
                        ->get('leave_application')->row();

                    if ($leave_check) {
                        $available_users = array_diff($user_pool, [$assigned_user]);
                        if (!empty($available_users)) {
                            $available_users = array_values($available_users);
                            $assigned_user = $available_users[array_rand($available_users)];
                        } else {
                            return; // Skip if all users in pool are on leave
                        }
                    }
                } else {
                    return; // Skip if user pool is empty after filtering
                }
            } else {
                return; // Skip if user pool is invalid
            }
        } else {
            $assigned_user = $this->get_next_rotation_staff($rdc_id);
            if (!$assigned_user) return;

            $leave_check = $this->db->where('user_id', $assigned_user)
                ->where('start_date <=', $today)
                ->where('end_date >=', $today)
                ->where('status', 2)
                ->get('leave_application')->row();

            if ($leave_check) {
                $assigned_user = $this->get_next_rotation_staff($rdc_id);
                if (!$assigned_user) return;
            }
        }
    }

    // Check if assigned staff is on leave and adjust due date
    if ($assigned_user) {
        $leave_check = $this->db->where('user_id', $assigned_user)
            ->where('start_date <=', $today)
            ->where('end_date >=', $today)
            ->where('status', 2)
            ->get('leave_application')->row();

        if ($leave_check) {
            $check_date = new DateTime($today);
            while (true) {
                $check_date->modify('+1 day');
                $check_date_str = $check_date->format('Y-m-d');

                if ($check_date->format('l') === 'Friday' || $check_date->format('l') === 'Saturday') continue;

                $holiday_query = $this->db->where('type', 'holiday')
                    ->where('start_date <=', $check_date_str)
                    ->where('end_date >=', $check_date_str)
                    ->get('event');

                if ($holiday_query === false) {
                    continue;
                }

                $holiday = $holiday_query->row();
                if ($holiday) continue;

                $future_leave = $this->db->where('user_id', $assigned_user)
                    ->where('start_date <=', $check_date_str)
                    ->where('end_date >=', $check_date_str)
                    ->where('status', 2)
                    ->get('leave_application')->row();

                if (!$future_leave) {
                    $due_date = $check_date_str;
                    break;
                }
            }
        }
    }

    if ($assigned_user) {
        // Fetch SOP data (handle multiple SOPs)
        $sop_ids = !empty($rdc_data['sop_ids']) ? json_decode($rdc_data['sop_ids'], true) : [];
        if (empty($sop_ids)) return;

        // Get all SOPs and combine their stages
        $sops = $this->db->where_in('id', $sop_ids)->get('sop')->result_array();
        if (empty($sops)) return;

        $executor_stages = [];
        $verifier_stages = [];
        $verifier_roles = [];
        $total_expected_time = 0;

        foreach ($sops as $sop) {
            if (!empty($sop['executor_stage'])) {
                $executor_stages[] = $sop['executor_stage'];
            }
            if (!empty($sop['verifier_stage'])) {
                $verifier_stages[] = $sop['verifier_stage'];
            }
            if (!empty($sop['verifier_role'])) {
                $roles = explode(',', $sop['verifier_role']);
                $verifier_roles = array_merge($verifier_roles, $roles);
            }

            if (!empty($sop['expected_time'])) {
                $time_str = strtolower(trim($sop['expected_time'])); // normalize
                $hours = 0;

                // Extract number
                preg_match('/\d+/', $time_str, $matches);
                $value = isset($matches[0]) ? (float)$matches[0] : 0;

                // Detect unit
                if (strpos($time_str, 'day') !== false) {
                    $hours = $value * 24;
                } elseif (strpos($time_str, 'hour') !== false) {
                    $hours = $value;
                } elseif (strpos($time_str, 'minute') !== false) {
                    $hours = $value / 60;
                } else {
                    // fallback: assume it's already hours
                    $hours = $value;
                }

                $total_expected_time += $hours;
            }
        }

        $combined_executor_stages = json_encode(array_unique($executor_stages));
        $combined_verifier_stages = json_encode(array_unique($verifier_stages));
        $combined_verifier_roles = implode(',', array_unique(array_filter($verifier_roles)));

        // Calculate due times based on frequency
        $due_time = $rdc_data['due_time'];
        $verifier_due_time = $rdc_data['verifier_due_time'];

        if ($rdc_data['frequency'] === 'daily') {
            // For daily tasks: use due date with original time
            $original_time = date('H:i:s', strtotime($rdc_data['due_time']));
            $due_time = $due_date . ' ' . $original_time;

            // Verifier due time: 3 days from today with same time
            $verifier_original_time = date('H:i:s', strtotime($rdc_data['verifier_due_time']));
            $verifier_due_time = date('Y-m-d', strtotime($today . ' +3 days')) . ' ' . $verifier_original_time;
        } elseif ($rdc_data['frequency'] === 'weekly') {
            // For weekly tasks: add 1 day to due time
            $original_time = date('H:i:s', strtotime($rdc_data['due_time']));
            $due_time = date('Y-m-d', strtotime($due_date . ' +1 day')) . ' ' . $original_time;

            // Verifier due time: 3 days from today with same time
            $verifier_original_time = date('H:i:s', strtotime($rdc_data['verifier_due_time']));
            $verifier_due_time = date('Y-m-d', strtotime($today . ' +3 days')) . ' ' . $verifier_original_time;
        } elseif ($rdc_data['frequency'] === 'bimonthly') {
            // For bimonthly tasks: add 2 days to due time
            $original_time = date('H:i:s', strtotime($rdc_data['due_time']));
            $due_time = date('Y-m-d', strtotime($due_date . ' +2 days')) . ' ' . $original_time;

            // Verifier due time: 3 days from today with same time
            $verifier_original_time = date('H:i:s', strtotime($rdc_data['verifier_due_time']));
            $verifier_due_time = date('Y-m-d', strtotime($today . ' +3 days')) . ' ' . $verifier_original_time;
        } elseif ($rdc_data['frequency'] === 'monthly') {
            // For monthly tasks: add 5 days to due time
            $original_time = date('H:i:s', strtotime($rdc_data['due_time']));
            $due_time = date('Y-m-d', strtotime($due_date . ' +5 days')) . ' ' . $original_time;

            // Verifier due time: 3 days from today with same time
            $verifier_original_time = date('H:i:s', strtotime($rdc_data['verifier_due_time']));
            $verifier_due_time = date('Y-m-d', strtotime($today . ' +3 days')) . ' ' . $verifier_original_time;
        } elseif ($rdc_data['frequency'] === 'yearly') {
            // For yearly tasks: add 5 days to due time
            $original_time = date('H:i:s', strtotime($rdc_data['due_time']));
            $due_time = date('Y-m-d', strtotime($due_date . ' +5 days')) . ' ' . $original_time;

            // Verifier due time: 10 days from today with same time
            $verifier_original_time = date('H:i:s', strtotime($rdc_data['verifier_due_time']));
            $verifier_due_time = date('Y-m-d', strtotime($today . ' +10 days')) . ' ' . $verifier_original_time;
        }

        // Check if verifier is required
        $verifier_required = isset($rdc_data['verifier_required']) && $rdc_data['verifier_required'] == 1;

        $task_data = [
            'rdc_id' => $rdc_id,
            'title' => $rdc_data['title'],
            'description' => $rdc_data['description'],
            'frequency' => $rdc_data['frequency'],
            'assigned_user' => $assigned_user,
            'is_random_assignment' => $rdc_data['is_random_assignment'],
            'user_pool' => $rdc_data['user_pool'],
            'due_time' => $due_time,
            'verifier_due_time' => $verifier_required ? $verifier_due_time : null,
            'verifier_required' => $rdc_data['verifier_required'] ?? 1,
            'sop_id' => $sop_ids[0],
            'sop_ids' => json_encode($sop_ids),
            'is_proof_required' => $rdc_data['is_proof_required'],
            'pre_reminder_enabled' => $rdc_data['pre_reminder_enabled'] ?? 0,
            'pre_reminder_minutes' => $rdc_data['pre_reminder_minutes'] ?? 0,
            'escalation_enabled' => $rdc_data['escalation_enabled'] ?? 0,
            'executor_stages' => $combined_executor_stages,
            'verifier_stages' => $verifier_required ? $combined_verifier_stages : null,
            'verified_by' => $verifier_required ? $combined_verifier_roles : null,
            'verify_status' => $verifier_required ? 1 : 4,
            'task_status' => 1,
            'flag' => 1,
            'created_by' => get_loggedin_user_id(),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('rdc_task', $task_data);

        // Add to tracker_issues
        $rdc_dept = $this->db->where('identifier', 'RDC')->get('tracker_departments')->row();
        if (!$rdc_dept) {
            $this->db->insert('tracker_departments', [
                'title' => 'RDC Tasks',
                'identifier' => 'RDC',
                'description' => 'Recurring Discipline & Compliance Tasks',
                'default_status' => 'todo',
                'is_private' => 0,
                'auto_join' => 1,
                'owner_id' => 1,
                'assigned_issuer' => 1
            ]);
            $rdc_dept_id = $this->db->insert_id();
        } else {
            $rdc_dept_id = $rdc_dept->id;
        }

		// Get max number for RDC prefix
		$this->db->select('MAX(CAST(SUBSTRING_INDEX(unique_id, "-", -1) AS UNSIGNED)) as max_num');
		$this->db->like('unique_id', 'RDC-', 'after');
		$this->db->from('tracker_issues');
		$row = $this->db->get()->row();

		$max_num = $row && $row->max_num ? (int)$row->max_num : 0;

		// Generate next unique ID
		$tracker_unique_id = 'RDC-' . ($max_num + 1);


        // Prepare sop_ids for tracker_issues (extract first SOP ID as string)
        $tracker_sop_ids = null;
        if (!empty($rdc_data['sop_ids'])) {
            $decoded_sop_ids = html_entity_decode($rdc_data['sop_ids']);
            $sop_array = json_decode($decoded_sop_ids, true);
            if (is_array($sop_array) && !empty($sop_array)) {
                $tracker_sop_ids = (string)$sop_array[0];
            }
        }

        $this->db->insert('tracker_issues', [
            'created_by' => get_loggedin_user_id(),
            'unique_id' => $tracker_unique_id,
            'department' => 'RDC',
            'task_title' => $rdc_data['title'],
            'task_description' => $rdc_data['description'],
            'task_status' => 'todo',
            'priority_level' => 'Medium',
            'assigned_to' => $assigned_user,
            'coordinator' => !empty($rdc_data['coordinator']) ? $rdc_data['coordinator'] : null,
            'task_type' => !empty($rdc_data['task_type']) ? $rdc_data['task_type'] : null,
            'milestone' => !empty($rdc_data['milestone']) ? $rdc_data['milestone'] : null,
            'component' => !empty($rdc_data['initiatives']) ? $rdc_data['initiatives'] : null,
            'sop_ids' => $tracker_sop_ids,
            'estimation_time' => $total_expected_time > 0 ? $total_expected_time : null,
            'estimated_end_time' => date('Y-m-d 23:59:59', strtotime($due_time)),
            'logged_at' => date('Y-m-d H:i:s')
        ]);
        $tracker_issue_id = $this->db->insert_id();

        // Add to staff_task_log
        $this->db->insert('staff_task_log', [
            'staff_id' => $assigned_user,
            'location' => 'RDC Task',
            'task_title' => $rdc_data['title'],
            'start_time' => $due_time,
            'task_status' => 'In Progress',
            'logged_at' => date('Y-m-d H:i:s'),
            'tracker_id' => $tracker_issue_id
        ]);

		// Send notification to assigned user
		$this->send_rdc_task_telegram_notification($assigned_user, $rdc_data, $due_time);
    }
}

	/**
	 * Send individual Telegram notification when RDC task is created
	 */
	private function send_rdc_task_telegram_notification($assigned_user, $rdc, $due_time)
	{
		// Get staff details
		$staff = $this->db->where('id', $assigned_user)->get('staff')->row();
		if (!$staff || empty($staff->telegram_id)) {
			log_message('debug', 'No staff found or telegram_id missing for user: ' . $assigned_user);
			return; // Skip if no staff found or no telegram_id
		}

		$staff_name = $staff->name;
		$chat_id = $staff->telegram_id;

		// Format due time
		$due_time_formatted = date('g:i A', strtotime($due_time));
		$due_date_formatted = date('d M Y', strtotime($due_time));

		// Get task title from rdc array
		$task_title = is_array($rdc) ? $rdc['title'] : $rdc;

		// Prepare Telegram message
		$tg_message = "🛎️ *RDC Task Generated*\n\n" . "Dear {$staff_name}, your RDC task \"{$task_title}\" has been generated. Please complete the task before: {$due_time_formatted} on {$due_date_formatted}.";

		// Send individual notification
		$bot_token = $telegram_bot;
		$payload = [
			'chat_id' => $chat_id,
			'text' => $tg_message,
			'parse_mode' => 'Markdown',
			'disable_web_page_preview' => true,
		];

		$url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
		$ch = curl_init();

		if ($ch === false) {
			log_message('error', 'Failed to initialize cURL for Telegram notification');
			return;
		}

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$response = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_error = curl_error($ch);
		curl_close($ch);

		if ($response === false || !empty($curl_error)) {
			log_message('error', 'Telegram notification failed for ' . $staff_name . ': ' . $curl_error);
			return;
		}

		if ($http_code !== 200) {
			log_message('error', 'Telegram API returned HTTP ' . $http_code . ' for ' . $staff_name . ': ' . $response);
			return;
		}

		log_message('debug', 'RDC task notification sent successfully to ' . $staff_name . ': ' . $response);
	}

}
