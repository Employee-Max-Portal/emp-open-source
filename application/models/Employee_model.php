<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Employee_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

   public function save($data, $role = null, $id = null)
	{
		$qualification = $data['qualification'];
		if ($qualification === 'Others') {
			$qualification = $data['other_qualification'];
		}

		$inser_data1 = array(
			'branch_id' => $this->application_model->get_branch_id(),
			'name' => $data['name'],
			'sex' => $data['sex'],
			'religion' => $data['religion'],
			'blood_group' => $data['blood_group'],
			'birthday' => $data["birthday"],
			'mobileno' => $data['mobile_no'],
			'telegram_id' => $data['telegram_id'],
			'present_address' => $data['present_address'],
			'permanent_address' => $data['permanent_address'],
			'photo' => $this->app_lib->upload_image('staff'),
			'designation' => $data['designation_id'],
			'department' => $data['department_id'],
			'joining_date' => date("Y-m-d", strtotime($data['joining_date'])),
			'qualification' => $qualification,
			'experience_details' => $data['experience_details'],
			'total_experience' => $data['total_experience'],
			'email' => $data['email'],
			'employee_type' => $data['employee_type'],
			'parental_leave_enabled' => isset($data['parental_leave_enabled']) ? $data['parental_leave_enabled'] : '0',
			'facebook_url' => $data['facebook'],
			'linkedin_url' => $data['linkedin'],
			'twitter_url' => $data['twitter'],
		);

		$inser_data2 = array(
			'username' => $data["username"],
			'role' => $data["user_role"],
		);

		if (!isset($data['staff_id']) && empty($data['staff_id'])) {
			// Generate and assign staff ID
			$inser_data1['staff_id'] = $data["username"];

			// Insert staff info
			$this->db->insert('staff', $inser_data1);
			$employeeID = $this->db->insert_id();

			// Insert login credentials
			$inser_data2['active'] = 1;
			$inser_data2['user_id'] = $employeeID;
			$inser_data2['password'] = $this->app_lib->pass_hashed($data["password"]);
			$this->db->insert('login_credential', $inser_data2);

			// Save bank info if not skipped
			if (!isset($data['chkskipped'])) {
				$data['staff_id'] = $employeeID;
				$this->bankSave($data);
			}

			// Allocate leave balance for intern employees
			if ($data['employee_type'] === 'intern') {
				$this->allocate_intern_sick_leave($employeeID);
			}
		} else {
			$inser_data1['staff_id'] = $data['staff_id_no'];

			// Get old employee type before update
			$old_employee = $this->db->select('employee_type')->where('id', $data['staff_id'])->get('staff')->row();
			$old_type = $old_employee ? $old_employee->employee_type : null;

			$this->db->where('id', $data['staff_id']);
			$this->db->update('staff', $inser_data1);

			// Check if employee type changed from probation to regular
			if ($old_type && $old_type !== $data['employee_type']) {
				$this->update_leave_balance_on_type_change($data['staff_id'], $old_type, $data['employee_type']);
			}

			$this->db->where('user_id', $data['staff_id']);
			$this->db->where_not_in('role', array(6, 7));
			$this->db->update('login_credential', $inser_data2);

			$employeeID = $data['staff_id'];
		}
		
		$designation_name = $this->db->query("SELECT name FROM staff_designation WHERE id = ?", array($data['designation_id']))->row('name');
		$department_name = $this->db->query("SELECT name FROM staff_department WHERE id = ?", array($data['department_id']))->row('name');
		$role_name = $this->db->query("SELECT name FROM roles WHERE id = ?", array($data['user_role']))->row('name');
		$branch_name = $this->db->query("SELECT name FROM branch WHERE id = ?", array($data['branch_id']))->row('name');

		// ✅ Send data to N8N Webhook
		$webhook_payload = array(
			'employee_id' => $employeeID,
			'name' => $data['name'],
			'sex' => $data['sex'],
			'religion' => $data['religion'],
			'blood_group' => $data['blood_group'],
			'birthday' => $data["birthday"],
			'mobile' => $data['mobile_no'],
			'telegram_id' => $data['telegram_id'],
			'present_address' => $data['present_address'],
			'permanent_address' => $data['permanent_address'],
			'branch' => $branch_name,
			'role' => $role_name,
			'designation' => $designation_name,
			'department' => $department_name,
			'joining_date' => date("Y-m-d", strtotime($data['joining_date'])),
			'qualification' => $qualification,
			'experience_details' => $data['experience_details'],
			'total_experience' => $data['total_experience'],
			'email' => $data['email'],
			'employee_type' => $data['employee_type'],
			'username' => $data["username"]
		);

		$webhook_url = "https://n8n.emp.com.bd/webhook-test/6e88ddd7-e337-44cb-a049-4c29600599a9";
		$ch = curl_init($webhook_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhook_payload));
		$response = curl_exec($ch);
		curl_close($ch);
		// Optional: log_message('debug', 'Webhook response: ' . $response);

		return $employeeID;
	}

	// Update leave balance when employee type changes from probation to regular
	public function update_leave_balance_on_type_change($employee_id, $old_type, $new_type)
	{
		if ($old_type === 'probation' && $new_type === 'regular') {
			$current_year = (int)date('Y');
			$current_month = (int)date('n');
			$remaining_months = 12 - $current_month + 1;
			
			// Get all leave categories
			$categories = $this->db->get('leave_category')->result();
			
			foreach ($categories as $category) {
				$monthly_allocation = (float)$category->days / 12;
				$new_total_days = round($monthly_allocation * $remaining_months);
				
				// Update or insert leave balance
				$existing = $this->db->get_where('leave_balance', [
					'user_id' => $employee_id,
					'leave_category_id' => $category->id,
					'year' => $current_year
				])->row();
				
				if ($existing) {
					$this->db->where('id', $existing->id)
						->update('leave_balance', [
							'total_days' => $new_total_days,
							'updated_at' => date('Y-m-d H:i:s')
						]);
				} else {
					$this->db->insert('leave_balance', [
						'user_id' => $employee_id,
						'leave_category_id' => $category->id,
						'total_days' => $new_total_days,
						'used_days' => 0,
						'year' => $current_year,
						'updated_at' => date('Y-m-d H:i:s')
					]);
				}
			}
		}
	}



    // GET SINGLE EMPLOYEE DETAILS
    public function getSingleStaff($id = '')
    {
        $this->db->select('staff.*,staff_designation.name as designation_name,staff_department.name as department_name,login_credential.role as role_id,login_credential.active,login_credential.username, roles.name as role');
        $this->db->from('staff');
        $this->db->join('login_credential', 'login_credential.user_id = staff.id and login_credential.role != "6" and login_credential.role != "7"', 'inner');
        $this->db->join('roles', 'roles.id = login_credential.role', 'left');
        $this->db->join('staff_designation', 'staff_designation.id = staff.designation', 'left');
        $this->db->join('staff_department', 'staff_department.id = staff.department', 'left');
        $this->db->where('staff.id', $id);
        if (!is_superadmin_loggedin()) {
            $this->db->where('staff.branch_id', get_loggedin_branch_id());
        }
        $query = $this->db->get();
        if ($query->num_rows() == 0) {
            show_404();
        }
        return $query->row_array();
    }

    // get staff all list
   public function getStaffList($branchID = '', $role_id = '', $active = 1)
	{
		$this->db->select('staff.*, 
						   staff_designation.name as designation_name, 
						   staff_department.name as department_name, 
						   login_credential.role as role_id, 
						   roles.name as role');
		$this->db->from('staff');
		
		// LEFT JOIN login_credential
		$this->db->join('login_credential', 'login_credential.user_id = staff.id', 'left');
		$this->db->join('roles', 'roles.id = login_credential.role', 'left');
		$this->db->join('staff_designation', 'staff_designation.id = staff.designation', 'left');
		$this->db->join('staff_department', 'staff_department.id = staff.department', 'left');

		if (!empty($role_id) && $role_id !== 'all') {
			$this->db->where('login_credential.role', $role_id);
		}

		// Conditional filter for branch
		if (!empty($branch_id) && $branch_id !== 'all') {
			if (!in_array(loggedin_role_id(), [1, 2, 3, 5]) && !empty($branchID)) {
			$this->db->where('staff.branch_id', $branchID);
		}
		}

		$this->db->where('login_credential.active', $active);
		$this->db->order_by('staff.id', 'ASC');

		return $this->db->get()->result();
	}

    public function bankSave($data)
    {
        $inser_data = array(
            'staff_id' => $data['staff_id'],
            'bank_name' => $data['bank_name'],
            'holder_name' => $data['holder_name'],
            'bank_branch' => $data['bank_branch'],
            'bank_address' => $data['bank_address'],
            'ifsc_code' => $data['ifsc_code'],
            'account_no' => $data['account_no'],
        );
        if (isset($data['bank_id'])) {
            $this->db->where('id', $data['bank_id']);
            $this->db->update('staff_bank_account', $inser_data);
        } else {
            $this->db->insert('staff_bank_account', $inser_data);
        }  
    }

    public function csvImport($row, $branchID, $userRole, $designationID, $departmentID)
    {
        $inser_data1 = array(
            'name' => $row['Name'],
            'sex' => $row['Gender'],
            'religion' => $row['Religion'],
            'blood_group' => $row['BloodGroup'],
            'birthday' => date("Y-m-d", strtotime($row['DateOfBirth'])),
            'joining_date' => date("Y-m-d", strtotime($row['JoiningDate'])),
            'qualification' => $row['Qualification'],
            'mobileno' => $row['MobileNo'],
            'present_address' => $row['PresentAddress'],
            'permanent_address' => $row['PermanentAddress'],
            'email' => $row['Email'],
            'designation' => $designationID,
            'department' => $departmentID,
            'branch_id' => $branchID,
            'photo' => 'defualt.png',
        );

        $inser_data2 = array(
            'username' => $row["Email"],
            'role' => $userRole,
        );

        // RANDOM STAFF ID GENERATE
        $inser_data1['staff_id'] = substr(app_generate_hash(), 3, 7);
        // SAVE EMPLOYEE INFORMATION IN THE DATABASE
        $this->db->insert('staff', $inser_data1);
        $employeeID = $this->db->insert_id();

        // SAVE EMPLOYEE LOGIN CREDENTIAL INFORMATION IN THE DATABASE
        $inser_data2['active'] = 1;
        $inser_data2['user_id'] = $employeeID;
        $inser_data2['password'] = $this->app_lib->pass_hashed($row["Password"]);
        $this->db->insert('login_credential', $inser_data2);
        return true;
    }
	
	
	 // Pause save and update function
    public function save_pause($data)
    {
        $insert_pause = array(
            'date_time' => date('Y-m-d H:i:s'),
            'assign_to' => $data['user_id'],
            'name' => $data['break_name'],
            'status' => $data['status']
        );
        if (isset($data['break_id']) && !empty($data['break_id'])) {
            $this->db->where('id', $data['break_id']);
            $this->db->update('pauses', $insert_pause);
        } else {
            $this->db->insert('pauses', $insert_pause);
        }
    }
	
	public function add_pause_history($break_id,$user_id)
    {
        $insert_data = array(
            'start_datetime' => date('Y-m-d H:i:s'),
            'user_id' => $user_id,
            'pause_id' => $break_id
        );
        
		$this->db->insert('pause_history', $insert_data);
		
		return $this->db->insert_id();
    }
	
	
	public function update_pause_history($history_id,$user_id,$remarks)
    {
        $update_data = array(
            'end_datetime' => date('Y-m-d H:i:s'),
            'remarks' => $remarks,
            'status' => 0,
        );
        $this->db->where('id', $history_id);
		$this->db->update('pause_history', $update_data);
    }
	
	 public function get_pause_id($user_id)
    {
        $this->db->select('pause_id');
        $this->db->from('pauses');
        $this->db->order_by('id', 'DSC');
        return $this->db->get()->result_array();
    }

	
	
	public function update_break_detail($user_id,$history_id,$break_id,$pause_status)
    {
        $update_data = array(
            'pause_id' => $break_id,
            'pause_status' => $pause_status,
            'pause_history' => $history_id
        );
        $this->db->where('id', $user_id);
		$this->db->update('staff', $update_data);
    }

    // get Pause list function
    public function get_pause_list()
    {
        $this->db->select('*');
        $this->db->from('pauses');
        $this->db->order_by('id', 'DESC');
        return $this->db->get()->result_array();
    }
	
	public function get_break_history()
    {
        $this->db->select('pause_history.*,pauses.name as pause_name, staff.name as staff_name');
        $this->db->from('pause_history');
        $this->db->join('staff', 'staff.id = pause_history.user_id', 'left');
        $this->db->join('pauses', 'pauses.id = pause_history.pause_id', 'left');
		
		$this->db->where('staff.id IS NOT NULL');

		$this->db->order_by('pause_history.id', 'DESC');
        return $this->db->get()->result_array();
    }
	
	// best employee
	
	public function get_all_scores($month)
	{
		$logged_user_dept = $this->get_logged_user_department();
		
		return $this->db
			->select('employee_scores.*, staff.name, staff.staff_id as employee_id, staff_designation.name as designation, staff.photo, staff.department, staff_department.name as department_name, adjuster.name as adjusted_by_name')
			->from('employee_scores')
			->join('staff', 'staff.id = employee_scores.staff_id')
			->join('staff_designation', 'staff_designation.id = staff.designation')
			->join('staff_department', 'staff_department.id = staff.department', 'left')
			->join('staff as adjuster', 'adjuster.id = employee_scores.adjusted_by', 'left')
			->join('login_credential', 'login_credential.user_id = employee_scores.staff_id')
			->where('employee_scores.month', $month)
			->where('login_credential.active', 1)
			->order_by('employee_scores.final_score', 'DESC')
			->get()
			->result_array();
	}


	public function get_logged_user_department()
	{
		return $this->db
			->select('staff.department')
			->from('staff')
			->where('staff.id', get_loggedin_user_id())
			->get()
			->row('department');
	}

	public function get_user_score($month, $user_id)
	{
		return $this->db
			->select('employee_scores.*, staff.name, staff.staff_id as employee_id, staff_designation.name as designation, staff.photo')
			->from('employee_scores')
			->join('staff', 'staff.id = employee_scores.staff_id')
			->join('staff_designation', 'staff_designation.id = staff.designation')
			->where('employee_scores.month', $month)
			->where('employee_scores.staff_id', $user_id)
			->get()
			->result_array();
	}
	
    public function generate_scores($month)
    {
        // Fetch all staff in branch
		$staffs = $this->db
			->select('staff.*, login_credential.role')
			->from('staff')
			->join('login_credential', 'login_credential.user_id = staff.id')
			->where('login_credential.active', 1)
			->where_not_in('login_credential.role', [1, 9, 10, 11, 12])
			->where_not_in('staff.id', [49, 37, 23])
			->get()
			->result_array();
			
		// Fetch scores from the settings
		$settings = $this->db
			->select('completion_ratio, quality_score, work_summary, attendance_score, warning_penalty')
			->from('global_settings')
			->get()
			->row_array(); // fetch a single row as associative array

		// Settings from DB
		$completion_weight   = $settings['completion_ratio'];
		$quality_weight      = $settings['quality_score'];
		$work_summary_weight = $settings['work_summary'];
		$attendance_weight   = $settings['attendance_score'];
		$warnings_weight   	 = $settings['warning_penalty'];

        foreach ($staffs as $staff) {
            $staff_id = $staff['id'];

			// Actual metric values
			$completion_rate = $this->calc_completion_rate($staff_id, $month);
			$quality_score = $this->calc_quality_score($staff_id, $month);
			$work_summary_score = $this->calc_work_summary_score($staff_id, $month);
			$attendance_score = $this->calc_attendance_score($staff_id, $month);
			$warnings = $this->count_warnings($staff_id, $month);
			$warning_penalty = $this->calculate_warning_penalty($staff_id, $month, $warnings_weight);
		
            // Final Score
			$final_score = (
				$completion_rate * ($completion_weight / 100) +
				$quality_score * ($quality_weight / 100) +
				$work_summary_score * ($work_summary_weight / 100) +
				$attendance_score * ($attendance_weight / 100)
				// ) - ($warnings * 10);
			) - $warning_penalty;

			$insert = array(
                'staff_id' => $staff_id,
                'month' => $month,
                'completion_rate' => min($completion_rate, 100),
                'quality_score' => min($quality_score, 100),
                'work_summary_score' => min($work_summary_score, 100),
                'attendance_score' => min($attendance_score, 100),
                'warning_count' => $warnings,
                'warning_penalty' => $warning_penalty,
                'adjustment_value' => 0,
                'adjustment_remarks' => null,
                'final_score' => min(max($final_score, 0), 100), // cap at 100 and avoid negative
                'generated_at' => date('Y-m-d H:i:s'),
            );

            // Insert or update
            $exists = $this->db->where(['staff_id' => $staff_id, 'month' => $month])->get('employee_scores')->row();

            if ($exists) {
                $this->db->where('id', $exists->id)->update('employee_scores', $insert);
            } else {
                $this->db->insert('employee_scores', $insert);
            }
        }
    }

    // --- Sample placeholder metric functions below ---
	
	public function calc_completion_rate($staff_id, $month) {
		// Get RDC tasks
		$this->db->select("COUNT(*) AS total_rdc, SUM(CASE WHEN task_status = 2 THEN 1 ELSE 0 END) AS completed_rdc");
		$this->db->from('rdc_task');
		$this->db->where('assigned_user', $staff_id);
		$this->db->like('due_time', $month, 'after');
		$rdc_result = $this->db->get();
		$rdc_data = $rdc_result ? $rdc_result->row() : null;

		// Get tracker issues
		$this->db->select("COUNT(*) AS total_tracker, SUM(CASE WHEN task_status = 'completed' THEN 1 ELSE 0 END) AS completed_tracker");
		$this->db->from('tracker_issues');
		$this->db->where('assigned_to', $staff_id);
		$this->db->like('estimated_end_time', $month, 'after');
		$tracker_result = $this->db->get();
		$tracker_data = $tracker_result ? $tracker_result->row() : null;

		$total_tasks = ($rdc_data->total_rdc ?? 0) + ($tracker_data->total_tracker ?? 0);
		$completed_tasks = ($rdc_data->completed_rdc ?? 0) + ($tracker_data->completed_tracker ?? 0);

		if ($total_tasks > 0) {
			return round(($completed_tasks / $total_tasks) * 100, 2);
		}
		return 0;
	}


   public function calc_on_time_rate($staff_id, $month) {
    $this->db->select("COUNT(*) AS total_tasks, 
                      SUM(CASE WHEN actual_end_time <= estimated_end_time AND task_status = 'completed' THEN 1 ELSE 0 END) AS on_time");
    $this->db->from('staff_task_log');
    $this->db->where('staff_id', $staff_id);
    $this->db->where('is_scrap', 0);
    $this->db->where('task_status', 'completed');
    $this->db->like('logged_at', $month, 'after');

    $result = $this->db->get()->row();
	
    if ($result && $result->total_tasks > 0) {
        return round(($result->on_time / $result->total_tasks) * 100, 2);
    }
    return 0;
	}


    public function calc_quality_score($staff_id, $month) {
    $this->db->select("AVG(
        CASE rating
            WHEN 'Excellent' THEN 100
            WHEN 'Very Good' THEN 90
            WHEN 'Good' THEN 80
            WHEN 'Average' THEN 70
            WHEN 'Poor' THEN 50
            ELSE 0
        END
    ) AS quality_score");
    $this->db->from('daily_work_summaries');
    $this->db->where('user_id', $staff_id);
    $this->db->like('summary_date', $month, 'after');

    $result = $this->db->get()->row();

    return ($result && $result->quality_score !== null) ? round($result->quality_score, 2) : 0;
	}
	
	
	public function calc_work_summary_score($staff_id, $month) {
		$start_date = $month . '-01';
		$end_date = date('Y-m-t', strtotime($start_date));

		// Get working days (same logic as attendance_score)
		$period = new DatePeriod(
			new DateTime($start_date),
			new DateInterval('P1D'),
			new DateTime($end_date . ' +1 day')
		);

		// Get holidays
		$this->db->select('start_date, end_date');
		$this->db->from('event');
		$this->db->where('type', 'holiday');
		$this->db->where('status', 1);
		$this->db->where('start_date <=', $end_date);
		$this->db->where('end_date >=', $start_date);
		$holiday_events = $this->db->get()->result();

		$holiday_dates = [];
		foreach ($holiday_events as $event) {
			$event_start = new DateTime($event->start_date);
			$event_end = new DateTime($event->end_date);
			while ($event_start <= $event_end) {
				$holiday_dates[] = $event_start->format('Y-m-d');
				$event_start->modify('+1 day');
			}
		}

		// Get leave dates
		$this->db->select('start_date, end_date');
		$this->db->from('leave_application');
		$this->db->where('user_id', $staff_id);
		$this->db->where('status', 2);
		$this->db->where('start_date <=', $end_date);
		$this->db->where('end_date >=', $start_date);
		$leave_entries = $this->db->get()->result();

		$leave_dates = [];
		foreach ($leave_entries as $leave) {
			$leave_start = new DateTime($leave->start_date);
			$leave_end = new DateTime($leave->end_date);
			while ($leave_start <= $leave_end) {
				$date_str = $leave_start->format('Y-m-d');
				$day = $leave_start->format('w');
				if ($day != 5 && $day != 6 && !in_array($date_str, $holiday_dates)) {
					$leave_dates[] = $date_str;
				}
				$leave_start->modify('+1 day');
			}
		}

		// Count working days
		$working_days = 0;
		$leave_dates_map = array_flip($leave_dates);

		foreach ($period as $date) {
			$day = $date->format('w');
			$date_str = $date->format('Y-m-d');
			if ($day != 5 && $day != 6 && !in_array($date_str, $holiday_dates) && !isset($leave_dates_map[$date_str])) {
				$working_days++;
			}
		}

		// Count daily work summaries submitted
		$this->db->select('COUNT(*) as submitted_days');
		$this->db->from('daily_work_summaries');
		$this->db->where('user_id', $staff_id);
		$this->db->where('DATE(created_at) >= ', $start_date);
		$this->db->where('DATE(created_at) <= ', $end_date);
		$result = $this->db->get()->row();

		if ($working_days > 0) {
			return round(($result->submitted_days / $working_days) * 100, 2);
		}
		return 0;
	}

   public function calc_attendance_score($staff_id, $month)
	{
		$start_date = $month . '-01';
		$end_date = date('Y-m-t', strtotime($start_date));

		// Step 1: Count total weekdays (Sun–Thu) in the month
		$period = new DatePeriod(
			new DateTime($start_date),
			new DateInterval('P1D'),
			new DateTime($end_date . ' +1 day')
		);

		// ✅ Fetch holiday dates from the `event` table
		$this->db->select('start_date, end_date');
		$this->db->from('event');
		$this->db->where('type', 'holiday');  // Must exactly match the string "holiday"
		$this->db->where('status', 1);
		$this->db->where('start_date <=', $end_date);
		$this->db->where('end_date >=', $start_date);
		$holiday_events = $this->db->get()->result();

		$holiday_dates = [];

		foreach ($holiday_events as $event) {
			$event_start = new DateTime($event->start_date);
			$event_end = new DateTime($event->end_date);

			while ($event_start <= $event_end) {
				$holiday_dates[] = $event_start->format('Y-m-d');
				$event_start->modify('+1 day');
			}
		}

		$working_days = 0;

		foreach ($period as $date) {
			$day = $date->format('w'); // 0 = Sunday, 5 = Friday, 6 = Saturday
			$date_str = $date->format('Y-m-d');

			// ✅ Only count working days (Sun–Thu) that are not holidays
			if ($day != 5 && $day != 6 && !in_array($date_str, $holiday_dates)) {
				$working_days++;
			}
		}

		// Step 2: Get approved leave date ranges
		$this->db->select('start_date, end_date');
		$this->db->from('leave_application');
		$this->db->where('user_id', $staff_id);
		$this->db->where('status', 2); // Approved
		$this->db->where("start_date <=", $end_date);
		$this->db->where("end_date >=", $start_date);
		$leave_entries = $this->db->get()->result();

		$leave_dates = [];

		foreach ($leave_entries as $leave) {
			$leave_start = new DateTime($leave->start_date);
			$leave_end = new DateTime($leave->end_date);
			
			while ($leave_start <= $leave_end) {
				$date_str = $leave_start->format('Y-m-d');
				$day = $leave_start->format('w'); // 0 = Sun ... 6 = Sat
				// Only add if it’s a working day (Sun–Thu) and not a holiday
				if ($day != 5 && $day != 6 && !in_array($date_str, $holiday_dates)) {
					$leave_dates[] = $date_str;
				}
				$leave_start->modify('+1 day');
			}
		}

		$unique_leave_dates = array_unique($leave_dates);
		$leave_days = count($unique_leave_dates);

		$expected_days = max(0, $working_days - $leave_days);

		// First: Build a leave date list (if not done already)
		$leave_dates_map = array_flip($unique_leave_dates); // e.g. ['2025-07-02' => true]
		$holiday_dates_map = array_flip($holiday_dates);         // Holidays
		
		// Step 3: Get all attendance records for the staff in the month
		$this->db->select('date, status, in_time');
		$this->db->from('staff_attendance');
		$this->db->where('staff_id', $staff_id);
		$this->db->like('date', $month, 'after');
		$attendance_rows = $this->db->get()->result();

		$present = 0;

		foreach ($attendance_rows as $row) {
			$date_str = $row->date;

			// Skip if on leave
			if (isset($leave_dates_map[$date_str])) {
				continue;
			}

			// Check if in_time is before 10:00 AM
			if (!empty($row->in_time)) {
				$in_time = strtotime($row->in_time);
				$cutoff_time = strtotime('10:00:59');
				
				if ($in_time < $cutoff_time) {
					$present++; // Give marks if arrived before 10:00 AM
				}
			}
		}
			
		// 4. Serve penalty_days where attendance exists
		$this->db->select('id, penalty_date');
		$this->db->from('penalty_days');
		$this->db->where('staff_id', $staff_id);
		$this->db->where('is_served', 0);
		$this->db->like('penalty_date', $month, 'after');
		$penalties = $this->db->get()->result();

		$unserved = 0;

		foreach ($penalties as $penalty) {
			$this->db->from('staff_attendance');
			$this->db->where('staff_id', $staff_id);
			$this->db->where('date', $penalty->penalty_date);
			if ($this->db->count_all_results() > 0) {
				// Mark as served
				$this->db->where('id', $penalty->id);
				$this->db->update('penalty_days', ['is_served' => 1]);
			} else {
				$unserved++;
			}
		}

		// 5. Calculate score with deductions
		if ($expected_days <= 0) return 0;

		$base_score = ($present / $expected_days) * 100;
		$unserved_penalty = $unserved * 1; // -1% per unserved penalty

		$final_score = round(max($base_score - $unserved_penalty, 0), 2);

		return $final_score;
	}

	public function calc_kpi_score($staff_id, $month) 
	{
		// Convert month format from YYYY-MM to YYYY/MM
		$formatted_month = str_replace('-', '/', $month);

		$this->db->select("AVG(manager_rating) AS avg_score");
		$this->db->from('kpi_form');
		$this->db->where('staff_id', $staff_id);
		$this->db->like('daterange', $formatted_month, 'after');

		$result = $this->db->get()->row();

		return ($result && $result->avg_score !== null) ? round($result->avg_score, 2) : 0;
	}

	public function calculate_warning_penalty($staff_id, $month, $warnings_weight)
	{
		$this->db->select("
			SUM(
				CASE 
					WHEN status = 1 THEN {$warnings_weight}
					WHEN status = 4 THEN {$warnings_weight}
					WHEN status = 2 THEN {$warnings_weight} / 2
					ELSE 0
				END
			) AS penalty
		", false); // false prevents CodeIgniter from escaping the SQL

		$this->db->from('warnings');
		$this->db->where('user_id', $staff_id);
		$this->db->like('issue_date', $month, 'after');

		$result = $this->db->get()->row();
		return ($result && $result->penalty !== null) ? (float) $result->penalty : 0;
	}

    public function count_warnings($staff_id, $month) {
    $this->db->from('warnings');
    $this->db->where('user_id', $staff_id);
    $this->db->like('issue_date', $month, 'after');
    
    return $this->db->count_all_results();
	}

	// Allocate 2 days sick leave for intern employees
	public function allocate_intern_sick_leave($employee_id)
	{
		$current_year = (int)date('Y');
		
		// Get sick leave category
		$sick_leave = $this->db->get_where('leave_category', ['name' => 'Sick Leave'])->row();
		
		if ($sick_leave) {
			$this->db->insert('leave_balance', [
				'user_id' => $employee_id,
				'leave_category_id' => $sick_leave->id,
				'total_days' => 2,
				'used_days' => 0,
				'year' => $current_year,
				'updated_at' => date('Y-m-d H:i:s')
			]);
		}
	}
}