<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Dashboard_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

// Existing method for filtered notifications by month/year
public function getFilteredNotifications($staffID, $role, $month, $year)
{
    $startDate = "{$year}-{$month}-01";
    $endDate = date("Y-m-t", strtotime($startDate));

    $this->db->select("n.*,
                       s.name AS created_by,
                       IF(nv.staff_id IS NULL, 0, 1) AS is_viewed,
                       nv.viewed_at")
             ->from('notifications AS n')
             ->join('staff AS s', 's.id = n.user_id', 'left')
			 ->join('login_credential AS lc', 'lc.user_id = s.id', 'left')
             ->join('notification_views AS nv', 'nv.notification_id = n.id AND nv.staff_id = ' . $staffID, 'left')
             ->where('lc.active', 1)
             ->where("DATE(n.created_at) BETWEEN '{$startDate}' AND '{$endDate}'");
    $this->db->order_by('n.created_at', 'DESC');
    return $this->db->get()->result();
}

// New method for recent notifications (last X days)
public function getRecentNotifications($staffID, $role, $days = 7)
{
    $startDate = date('Y-m-d', strtotime("-{$days} days"));
    $endDate = date('Y-m-d');

    $this->db->select("n.*,
                       s.name AS created_by,
                       IF(nv.staff_id IS NULL, 0, 1) AS is_viewed,
                       nv.viewed_at")
             ->from('notifications AS n')
             ->join('staff AS s', 's.id = n.user_id', 'left')
			 ->join('login_credential AS lc', 'lc.user_id = s.id', 'left')
             ->join('notification_views AS nv', 'nv.notification_id = n.id AND nv.staff_id = ' . $staffID, 'left')
             ->where('lc.active', 1)
             ->where("DATE(n.created_at) BETWEEN '{$startDate}' AND '{$endDate}'");
    $this->db->order_by('n.created_at', 'DESC');
    return $this->db->get()->result();
}

// Existing method for filtered task logs by month/year
public function getFilteredTaskLogs($staffID, $role, $month, $year)
{
    $startDate = "{$year}-{$month}-01";
    $endDate = date("Y-m-t", strtotime($startDate));

    $this->db->select('
        stl.*,
        sp.name AS supervisor_name,
        s.name AS staff_name,
    ');
    $this->db->from('staff_task_log AS stl');
    $this->db->join('staff AS sp', 'sp.id = stl.supervisor', 'left');
    $this->db->join('staff AS s', 's.id = stl.staff_id', 'left');
    $this->db->join('login_credential AS lc', 'lc.user_id = s.id', 'left');
    // If staff, limit to own tasks
    if ($role === '4') {
        $this->db->where('stl.staff_id', $staffID);
    }

    $this->db->where('lc.active', 1);
    $this->db->where('DATE(stl.logged_at) >=', $startDate);
    $this->db->where('DATE(stl.logged_at) <=', $endDate);
    $this->db->order_by('stl.logged_at', 'DESC');
    $query = $this->db->get();

    return $query->result();
}

// New method for recent task logs (last X days)
public function getRecentTaskLogs($staffID, $role, $days = 7)
{
    $startDate = date('Y-m-d', strtotime("-{$days} days"));
    $endDate = date('Y-m-d');

    $this->db->select('
        stl.*,
        sp.name AS supervisor_name,
        s.name AS staff_name,
    ');
    $this->db->from('staff_task_log AS stl');
    $this->db->join('staff AS sp', 'sp.id = stl.supervisor', 'left');
    $this->db->join('staff AS s', 's.id = stl.staff_id', 'left');
    $this->db->join('login_credential AS lc', 'lc.user_id = s.id', 'left');
    // If staff, limit to own tasks
    if ($role === '4') {
        $this->db->where('stl.staff_id', $staffID);
    }

    $this->db->where('lc.active', 1);
    $this->db->where('DATE(stl.logged_at) >=', $startDate);
    $this->db->where('DATE(stl.logged_at) <=', $endDate);
    $this->db->order_by('stl.logged_at', 'DESC');
    $query = $this->db->get();

    return $query->result();
}

public function get_recent_summaries()
{
	$User_id = get_loggedin_user_id();

	$this->db->select('daily_work_summaries.*');
	$this->db->from('daily_work_summaries');
	$this->db->where('summary_date >=', date('Y-m-d', strtotime('-6 days')));
	$this->db->where('summary_date <=', date('Y-m-d'));

	if (!in_array(loggedin_role_id(), [1, 2, 3, 5, 8])) {
        $this->db->where('user_id', $User_id);
    }
	$this->db->order_by('daily_work_summaries.id', 'DESC');

	return $this->db->get()->result_array();
}



	public function getWorkSummaries($start = null, $end = null, $user_id = '', $department = null)
{

	$this->db->select('daily_work_summaries.*, staff.name, staff.staff_id');
	$this->db->from('daily_work_summaries');

	$this->db->join('staff', 'staff.id = daily_work_summaries.user_id', 'left');

	// Optional: date range filter
	if (!empty($start) && !empty($end)) {
		$this->db->where('summary_date >=', $start);
		$this->db->where('summary_date <=', $end);
	}

	// Optional: user filter
    if (!empty($user_id)) {
        $this->db->where('user_id', $user_id);
    }

	// Optional: department filter
	if (!empty($department) && $department !== 'all') {
		$this->db->where('daily_work_summaries.department', $department);
	}

	$this->db->order_by('daily_work_summaries.id', 'DESC');
	return $this->db->get()->result_array();
}

// Get work summaries count for pagination
public function getWorkSummariesCount($start = null, $end = null, $user_id = '', $department = null)
{
	$this->db->from('daily_work_summaries');

	// Optional: date range filter
	if (!empty($start) && !empty($end)) {
		$this->db->where('summary_date >=', $start);
		$this->db->where('summary_date <=', $end);
	}

	// Optional: user filter
    if (!empty($user_id)) {
        $this->db->where('user_id', $user_id);
    }

	// Optional: department filter
	if (!empty($department) && $department !== 'all') {
		$this->db->where('department', $department);
	}

	return $this->db->count_all_results();
}

// Get paginated work summaries
public function getWorkSummariesPaginated($start = null, $end = null, $user_id = '', $department = null, $limit = 50, $offset = 0)
{
	$this->db->select('daily_work_summaries.*, staff.name, staff.staff_id');
	$this->db->from('daily_work_summaries');

	$this->db->join('staff', 'staff.id = daily_work_summaries.user_id', 'left');

	// Optional: date range filter
	if (!empty($start) && !empty($end)) {
		$this->db->where('summary_date >=', $start);
		$this->db->where('summary_date <=', $end);
	}

	// Optional: user filter
    if (!empty($user_id)) {
        $this->db->where('user_id', $user_id);
    }

	// Optional: department filter
	if (!empty($department) && $department !== 'all') {
		$this->db->where('daily_work_summaries.department', $department);
	}

	$this->db->order_by("CASE WHEN daily_work_summaries.status = 1 THEN 0 ELSE 1 END ASC", null, false);
	$this->db->order_by('daily_work_summaries.id', 'DESC');

	$this->db->limit($limit, $offset);
	return $this->db->get()->result_array();
}


 // get leave list
    public function getWorkSummaryById($where = '', $single = false)
    {
        $this->db->select('dws.*, s.name, s.staff_id');
        $this->db->from('daily_work_summaries dws');
        $this->db->join('staff s', 's.id = dws.user_id', 'left');
        if (!empty($where)) {
            $this->db->where($where);
        }
        if ($single == false) {
            $this->db->order_by('dws.id', 'DESC');
            return $this->db->get()->result_array();
        } else {
            return $this->db->get()->row_array();
        }
    }

	public function get_crm_activity($limit = '')
	{
		try {
			// Step 1: Load crm database
			$crm = $this->load->database('crm', TRUE);

			// Check if database connection is successful
			if (!$crm || !$crm->conn_id) {
				log_message('error', 'Failed to connect to crm database');
				return [];
			}

			// Step 2: Build query to fetch activities with Assigned To and Related To
			$crm->select('
				a.activityid,
				a.subject,
				a.activitytype AS activity_type,
				COALESCE(es.eventstatus, ts.taskstatus, a.eventstatus, a.status) AS status,
				a.date_start AS start_date,
				a.due_date AS due_date,
				a.time_start,
				a.time_end,
				a.location,
				e.description,
				CONCAT(u.first_name, " ", u.last_name) AS assigned_to,
				CASE
					WHEN rel.setype = "Contacts" THEN CONCAT(c.firstname, " ", c.lastname)
					WHEN rel.setype = "Accounts" THEN ac.accountname
					WHEN rel.setype = "Leads" THEN CONCAT(l.firstname, " ", l.lastname)
					ELSE rel.setype
				END AS related_to
			');


			// From main activity tables
			$crm->from('vtiger_activity a');
			$crm->join('vtiger_crmentity e', 'e.crmid = a.activityid', 'inner');
			$crm->join('vtiger_activitycf acf', 'acf.activityid = a.activityid', 'left');
			$crm->join('vtiger_eventstatus es', 'es.eventstatus = a.eventstatus', 'left');
			$crm->join('vtiger_taskstatus ts', 'ts.taskstatus = a.status', 'left');

			// Assigned To
			$crm->join('vtiger_users u', 'u.id = e.smownerid', 'left');

			// Related To
			$crm->join('vtiger_seactivityrel se', 'se.activityid = a.activityid', 'left');
			$crm->join('vtiger_crmentity rel', 'rel.crmid = se.crmid', 'left');
			$crm->join('vtiger_contactdetails c', 'c.contactid = rel.crmid', 'left');
			$crm->join('vtiger_account ac', 'ac.accountid = rel.crmid', 'left');
			$crm->join('vtiger_leaddetails l', 'l.leadid = rel.crmid', 'left');

			// Conditions
			$crm->where('e.deleted', 0);

			// Ordering & limit
			$crm->order_by('e.crmid', 'DESC');
			$crm->limit($limit);

			// Execute query
			$query = $crm->get();
			
			// Check if query was successful
			if (!$query) {
				log_message('error', 'CRM activity query failed: ' . $crm->error()['message']);
				return [];
			}

			$results = $query->result_array();
			return $results;
			
		} catch (Exception $e) {
			log_message('error', 'Exception in get_crm_activity: ' . $e->getMessage());
			return [];
		}
	}

    /* annual academic fees summary charts */
    public function getWeekendAttendance($branchID = '')
    {
        $days = array();
        $employee_att = array();
        $student_att = array();
        $now = new DateTime("6 days ago");
        $interval = new DateInterval('P1D'); // 1 Day interval
        $period = new DatePeriod($now, $interval, 6); // 7 Days
        foreach ($period as $day) {
            $days[] = $day->format("d-M");
            $this->db->select('id');
            if (!empty($branchID)) {
                $this->db->where('branch_id', $branchID);
            }

            $this->db->where('date = "' . $day->format('Y-m-d') . '" AND (status = "P" OR status = "L")');
            $student_att[]['y'] = $this->db->get('student_attendance')->num_rows();

            $this->db->select('id');
            if (!empty($branchID)) {
                $this->db->where('branch_id', $branchID);
            }

            $this->db->where('date = "' . $day->format('Y-m-d') . '" AND (status = "P" OR status = "L")');
            $employee_att[]['y'] = $this->db->get('staff_attendance')->num_rows();
        }
        return array(
            'days' => $days,
            'employee_att' => $employee_att,
            'student_att' => $student_att,
        );
    }

    public function languageShortCodes($lang='')
    {
        $codes = array (
          'english' => 'en',
          'bengali' => 'bn',
          'arabic' => 'ar',
          'french' => 'fr',
          'hindi' => 'hi',
          'indonesian' => 'id',
          'italian' => 'it',
          'japanese' => 'ja',
          'korean' => 'ko',
          'portuguese' => 'pt',
          'thai' => 'th',
          'turkish' => 'tr',
          'urdu' => 'ur',
          'chinese' => 'zh',
          'afrikaans' => 'af',
          'german' => 'de',
          'nepali' => 'ne',
          'russian' => 'ru',
          'danish' => 'da',
          'armenian' => 'hy',
          'georgian' => 'ka',
          'marathi' => 'mr',
          'malay' => 'ms',
          'tamil' => 'ta',
          'telugu' => 'te',
          'swedish' => 'sv',
          'dutch' => 'nl',
          'greek' => 'el',
          'spanish' => 'es',
          'punjabi' => 'pa',
        );
        return empty($codes[$lang]) ? '' : $codes[$lang];
    }

    public function getTrackerIssuesPieData($staffID, $role)
    {
        try {
            $this->db->select('task_status, COUNT(*) as count');
            $this->db->from('tracker_issues');

            // Role-based filtering
            if (!in_array($role, [1, 2, 3, 5])) {
                $this->db->where('assigned_to', $staffID);
            }

            $this->db->group_by('task_status');
            $query = $this->db->get();
            
            if (!$query) {
                return ['todo' => 0, 'in_progress' => 0, 'completed' => 0, 'canceled' => 0];
            }
            
            $result = $query->result_array();

            $data = ['todo' => 0, 'in_progress' => 0, 'completed' => 0, 'canceled' => 0];
            foreach ($result as $row) {
                if (isset($data[$row['task_status']])) {
                    $data[$row['task_status']] = (int)$row['count'];
                }
            }

            return $data;
        } catch (Exception $e) {
            log_message('error', 'Exception in getTrackerIssuesPieData: ' . $e->getMessage());
            return ['todo' => 0, 'in_progress' => 0, 'completed' => 0, 'canceled' => 0];
        }
    }

    public function getRdcTaskPieData($staffID, $role)
    {
        try {
            $this->db->select('task_status, COUNT(*) as count');
            $this->db->from('rdc_task');
            $this->db->where('flag', 1); // Only active tasks

            // Role-based filtering
            if (!in_array($role, [1, 2, 3, 5, 8])) {
                $this->db->where('assigned_user', $staffID);
            }

            $this->db->group_by('task_status');
            $query = $this->db->get();
            
            if (!$query) {
                return ['pending' => 0, 'completed' => 0, 'canceled' => 0, 'hold' => 0];
            }
            
            $result = $query->result_array();

            $data = ['pending' => 0, 'completed' => 0, 'canceled' => 0, 'hold' => 0];
            foreach ($result as $row) {
                switch ($row['task_status']) {
                    case 1: $data['pending'] = (int)$row['count']; break;
                    case 2: $data['completed'] = (int)$row['count']; break;
                    case 3: $data['canceled'] = (int)$row['count']; break;
                    case 4: $data['hold'] = (int)$row['count']; break;
                }
            }

            return $data;
        } catch (Exception $e) {
            log_message('error', 'Exception in getRdcTaskPieData: ' . $e->getMessage());
            return ['pending' => 0, 'completed' => 0, 'canceled' => 0, 'hold' => 0];
        }
    }

	// Get total tasks generated today
    public function getTotalTasksToday()
    {
        $today = date('Y-m-d');
        $this->db->where('DATE(logged_at)', $today);
        return $this->db->count_all_results('tracker_issues');
    }

    // Get email contacts from email_logs table today
    public function getEmailContactsToday()
    {
        $today = date('Y-m-d');
        $this->db->select('COUNT(DISTINCT to_email) as unique_contacts');
        $this->db->where('DATE(created_at)', $today);
        $result = $this->db->get('email_logs')->row();
        return $result ? $result->unique_contacts : 0;
    }

    // Get meeting minutes or team meetings today
    public function getMeetingMinutesToday()
    {
        $today = date('Y-m-d');
        $this->db->where('date', $today);
        return $this->db->count_all_results('meeting_minutes');
    }

    // Get client physical meetings from tracker_issues today
	public function getClientPhysicalMeetToday()
	{
		$today = date('Y-m-d');

		$this->db->select('tracker_issues.id'); // Optional, helps CI handle joins properly
		$this->db->from('tracker_issues');
		$this->db->join('task_types', 'task_types.id = tracker_issues.task_type', 'left');
		$this->db->where('DATE(tracker_issues.logged_at)', $today);
		$this->db->like('task_types.name', 'Physical Meeting', 'both');

		return $this->db->count_all_results();
	}

    // Get tasks from tracker_issues for today by category
	public function getTasksTodayByCategory($category)
	{
		$today = date('Y-m-d');

		$this->db->select('id');
		$this->db->from('tracker_issues');
		$this->db->where('DATE(tracker_issues.logged_at)', $today);
		$this->db->like('category', $category, 'both');

		return $this->db->count_all_results();
	}


    // Get client online meetings from tracker_issues today
    public function getClientOnlineMeetToday()
	{
		$today = date('Y-m-d');

		$this->db->select('tracker_issues.id'); // Optional, helps CI handle joins properly
		$this->db->from('tracker_issues');
		$this->db->join('task_types', 'task_types.id = tracker_issues.task_type', 'left');
		$this->db->where('DATE(tracker_issues.logged_at)', $today);
		$this->db->like('task_types.name', 'Online Meeting', 'both');

		return $this->db->count_all_results();
	}


    // Get support tasks from tracker_issues today
	public function getSupportTasksToday()
	{
		$today = date('Y-m-d');

		$this->db->select('tracker_issues.id');
		$this->db->from('tracker_issues');
		$this->db->join('task_types', 'task_types.id = tracker_issues.task_type', 'left');
		$this->db->where('DATE(tracker_issues.logged_at)', $today);
		$this->db->like('task_types.name', 'Support', 'both'); // equivalent to LIKE '%Support%'

		return $this->db->count_all_results();
	}


    // Get billing tasks from tracker_issues today
	public function getBillingTasksToday()
	{
		$today = date('Y-m-d');

		$this->db->select('tracker_issues.id');
		$this->db->from('tracker_issues');
		$this->db->join('task_types', 'task_types.id = tracker_issues.task_type', 'left');
		$this->db->where('DATE(tracker_issues.logged_at)', $today);
		// Grouped like for OR condition
		$this->db->group_start();
		$this->db->like('task_types.name', 'Billing', 'both');
		$this->db->or_like('task_types.name', 'Payment', 'both');
		$this->db->group_end();

		return $this->db->count_all_results();
	}

    // Get total activities today (sum of all activity types)
    public function getTotalActivitiesToday()
    {
        $today = date('Y-m-d');

        // Count from multiple sources
        $tasks = $this->getTotalTasksToday();
        $emails = $this->getEmailContactsToday();
        $meetings = $this->getMeetingMinutesToday();
        $physical_meets = $this->getClientPhysicalMeetToday();
        $online_meets = $this->getClientOnlineMeetToday();
        $support_tasks = $this->getSupportTasksToday();

        return $tasks + $emails + $meetings + $physical_meets + $online_meets + $support_tasks;
    }

    // Get completion rate for today
    public function getCompletionRateToday()
    {
        $today = date('Y-m-d');

        // Get total tasks for today
        $this->db->where('DATE(logged_at)', $today);
        $total_tasks = $this->db->count_all_results('tracker_issues');

        if ($total_tasks == 0) {
            return 0;
        }

        // Get completed tasks for today
        $this->db->where('DATE(logged_at)', $today);
        $this->db->where('task_status', 'completed');
        $completed_tasks = $this->db->count_all_results('tracker_issues');

        return round(($completed_tasks / $total_tasks) * 100, 2);
    }

 // Get active employees count
    public function getActiveEmployeesCount()
    {
        $this->db->select('COUNT(staff.id) as count');
        $this->db->from('staff');
        $this->db->join('login_credential', 'login_credential.user_id = staff.id');
        $this->db->where('login_credential.active', 1);
		$this->db->where_not_in('login_credential.user_id', [37, 31, 25, 28, 22, 23]);
        $this->db->where_not_in('login_credential.role', [1, 9, 10, 11, 12]);
        $result = $this->db->get()->row();
        return $result ? $result->count : 0;
    }

    // Get average working hours from last 7 days
	public function getAverageWorkingHours()
	{
		$start_date = date('Y-m-d', strtotime('-7 days'));
		$end_date   = date('Y-m-d');

		$this->db->select('AVG(TIMESTAMPDIFF(HOUR, sa.in_time, sa.out_time)) AS avg_hours');
		$this->db->from('staff_attendance sa');
		$this->db->join('login_credential lc', 'lc.user_id = sa.staff_id');
		$this->db->join('staff s', 's.id = sa.staff_id');

		// Active employees only
		$this->db->where('lc.active', 1);
		$this->db->where_not_in('lc.user_id', [37, 31, 25, 28, 22, 23]);
		$this->db->where_not_in('lc.role', [1, 9, 10, 11, 12]);

		// Date range
		$this->db->where('sa.date >=', $start_date);
		$this->db->where('sa.date <=', $end_date);

		// Exclude weekends (Friday=6, Saturday=7 in MySQL DAYOFWEEK)
		$this->db->where('DAYOFWEEK(sa.date) NOT IN (6,7)', NULL, FALSE);

		// Exclude holidays if holiday table exists
		if ($this->db->table_exists('holidays')) {
			$this->db->where('sa.date NOT IN (SELECT date FROM holidays WHERE date BETWEEN "' . $start_date . '" AND "' . $end_date . '")', NULL, FALSE);
		}

		// Only valid entries with both in and out times
		$this->db->where('sa.in_time IS NOT NULL');
		$this->db->where('sa.out_time IS NOT NULL');
		$this->db->where('sa.in_time !=', '00:00:00');
		$this->db->where('sa.out_time !=', '00:00:00');
		$this->db->where_in('sa.status', ['P', 'L']);

		$result = $this->db->get()->row();

		return $result && $result->avg_hours ? round($result->avg_hours, 1) : 0;
	}


    // Get attendance rate
    public function getAttendanceRate()
	{
		$today = date('Y-m-d');

		$total_employees = $this->getActiveEmployeesCount();
		if ($total_employees == 0) return 0;

		$this->db->select('COUNT(DISTINCT sa.staff_id) AS present_count');
		$this->db->from('staff_attendance sa');
		$this->db->join('login_credential lc', 'lc.user_id = sa.staff_id');

		// Only active employees
		$this->db->where('lc.active', 1);
		$this->db->where_not_in('lc.user_id', [37, 31, 25, 28, 22, 23]);
		$this->db->where_not_in('lc.role', [1, 9, 10, 11, 12]);

		$this->db->where('sa.date', $today);
		$this->db->where_in('sa.status', ['P', 'L']);

		$result = $this->db->get()->row();
		$present = $result ? $result->present_count : 0;

		return round(($present / $total_employees) * 100, 1);
	}


    // Get email logs for table display
    public function getEmailLogs($limit = '')
    {
        $this->db->select('*');
        $this->db->from('email_logs');
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result_array();
    }

    // Get CDR data from external API
    public function getCdrData($limit = '')
    {
        $url = 'https://connect.com.bd/api_webhook/get_cdr_offices.php';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $data = json_decode($response, true);
            if ($data && $data['status'] === 'success' && isset($data['data'])) {
                return array_slice($data['data'], 0, $limit);
            }
        }

        return [];
    }

    // Get shipment activities by status
    public function getShipmentActivities()
    {
        try {
            $this->db->select('status, COUNT(*) as count');
            $this->db->from('shipments');
            $this->db->where('status !=', 'cancelled');
            $this->db->group_by('status');
            $result = $this->db->get();

            $status_counts = [
                'ordered' => 0,
                'in_transit' => 0,
                'received' => 0,
                'delivered' => 0,
                'total_shipments' => 0
            ];

            if ($result) {
                foreach ($result->result_array() as $row) {
                    $status = $row['status'];
                    $count = (int)$row['count'];

                    // Map statuses to our dashboard categories
                    switch ($status) {
                        case 'ordered':
                        case 'in_production':
                            $status_counts['ordered'] += $count;
                            break;
                        case 'in_transit':
                        case 'agent_warehouse':
                        case 'bd_customs':
                            $status_counts['in_transit'] += $count;
                            break;
                        case 'received':
                            $status_counts['received'] += $count;
                            break;
                        case 'delivered':
                            $status_counts['delivered'] += $count;
                            break;
                    }
                    $status_counts['total_shipments'] += $count;
                }
            }

            return $status_counts;
        } catch (Exception $e) {
            return [
                'ordered' => 0,
                'in_transit' => 0,
                'received' => 0,
                'delivered' => 0,
                'total_shipments' => 0
            ];
        }
    }

    // Get shipment completion rate
    public function getShipmentCompletionRate()
    {
        try {
            $this->db->select('COUNT(*) as total');
            $this->db->from('shipments');
            $this->db->where('status !=', 'cancelled');
            $result = $this->db->get();
            $total = $result ? $result->row()->total : 0;

            if ($total == 0) return 0;

            $this->db->select('COUNT(*) as completed');
            $this->db->from('shipments');
            $this->db->where('status', 'delivered');
            $result = $this->db->get();
            $completed = $result ? $result->row()->completed : 0;

            return round(($completed / $total) * 100, 1);
        } catch (Exception $e) {
            return 0;
        }
    }

}