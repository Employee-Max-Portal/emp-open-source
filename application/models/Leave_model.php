<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Leave_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

	// get leave list
	public function getLeaves($branch_id = null, $single = false)
	{
		$login_id = get_loggedin_user_id(); // Current user ID
		$login_role = loggedin_role_id();   // Current user role ID

		// 🔹 Get HOD's department
		$hod_department = '';
		if ($login_role == 8) {
			$hod = $this->db->select('department')->where('id', $login_id)->get('staff')->row();
			if ($hod) {
				$hod_department = $hod->department;
			}
		}

		// 🔹 Main query
		$this->db->select('la.*, c.name as category_name, r.name as role');
		$this->db->from('leave_application as la');
		$this->db->join('leave_category as c', 'c.id = la.category_id', 'left');
		$this->db->join('roles as r', 'r.id = la.role_id', 'left');
		$this->db->join('staff as s', 's.id = la.user_id', 'left');
		$this->db->join('login_credential as lc', 'lc.user_id = s.id', 'left');

		// 🧩 Join staff table only if role is HOD
		if ($login_role == 8 && !empty($hod_department)) {
			$this->db->where('s.department', $hod_department);
		}

		// 🔹 Filter by session
		$this->db->where('la.session_id', get_session_id());

		// 🔹 Filter by branch
		if (!empty($branch_id) && $branch_id !== 'all') {
			$this->db->where('s.branch_id', $branch_id);
		}

		$this->db->where('lc.active', 1);
		// 🔹 Sort by latest
		$this->db->order_by('la.id', 'DESC');

		// 🔹 Return result
		return $single ? $this->db->get()->row_array() : $this->db->get()->result_array();
	}

    // get leave list
    public function getLeaveList($where = [], $userRole = null, $branch_id = null, $single = false)
{
    $this->db->select('la.*, c.name as category_name, r.name as role');
    $this->db->from('leave_application as la');
    $this->db->join('leave_category as c', 'c.id = la.category_id', 'left');
    $this->db->join('roles as r', 'r.id = la.role_id', 'left');
    $this->db->join('staff as s', 's.id = la.user_id', 'left');
	$this->db->join('login_credential as lc', 'lc.user_id = s.id', 'left');


    // Filter by session
    $this->db->where('la.session_id', get_session_id());

    // Apply additional where condition(s)
    if (!empty($where)) {
        if (is_array($where)) {
            $this->db->where($where);
        } else {
            // If it's a raw string, use it cautiously
            $this->db->where($where, null, false);
        }
    }

    // Filter by user role
    if (!empty($userRole) && $userRole !== 'all') {
        $this->db->where('la.role_id', $userRole);
    }

    // Filter by branch
    if (!empty($branch_id) && $branch_id !== 'all') {
        $this->db->where('la.branch_id', $branch_id);
    }

	$this->db->where('lc.active', 1);
    // Order results and return
    $this->db->order_by('la.id', 'DESC');

    if ($single) {
        return $this->db->get()->row_array();
    } else {
        return $this->db->get()->result_array();
    }
}

  // get leave list
    public function getLeaveList2($where = '', $single = false)
    {
        $this->db->select('la.*,c.name as category_name,r.name as role');
        $this->db->from('leave_application as la');
        $this->db->join('leave_category as c', 'c.id = la.category_id', 'left');
        $this->db->join('roles as r', 'r.id = la.role_id', 'left');
        $this->db->where('session_id', get_session_id());
        if (!empty($where)) {
            $this->db->where($where);
        }
        if ($single == false) {
            $this->db->order_by('la.id', 'DESC');
            return $this->db->get()->result_array();
        } else {
            return $this->db->get()->row_array();
        }
    }


	 public function calculate_leave_balance22($user_id, $branch_id)
    {
        // Get employee type (Regular or Intern) and joining date
        $employee = $this->db->select('employee_type, joining_date')
                             ->from('staff')
                             ->where('id', $user_id)
                             ->get()
                             ->row();

        $leave_balance = [];

        // Fetch leave categories per branch
        $leave_categories = $this->db->where('branch_id', $branch_id)
                                     ->get('leave_category')
                                     ->result_array();

        foreach ($leave_categories as $category) {
            $category_id = $category['id'];
            $allowed_days = (int)$category['days'];

            // If the employee is an intern
            if ($employee->employee_type == 'intern') {
                // Assume fixed leave days for interns (e.g., 3 days for a 3-month internship)
                $leave_balance[$category_id] = 3; // Adjust this based on internship length
            } else {
                // If regular employee, calculate leave days based on joining date
                $join_date = new DateTime($employee->joining_date);
                $current_date = new DateTime(date('Y-m-d'));
                $months_worked = $current_date->diff($join_date)->m;

                // Pro-rate leave days for regular employees
                $pro_rate_leave = ($months_worked / 12) * $allowed_days;
                $leave_balance[$category_id] = round($pro_rate_leave);
            }
        }

        return $leave_balance;
    }


	public function calculate_leave_balance($user_id, $branch_id)
{
    // Get employee type and joining date
    $employee = $this->db->select('employee_type, joining_date')
                         ->from('staff')
                         ->where('id', $user_id)
                         ->get()
                         ->row();

    if (!$employee) {
        return [];
    }

    $leave_balance = [];
    $joining_date = new DateTime($employee->joining_date);
    $current_date = new DateTime(date('Y-m-d'));
    $current_year = date('Y');
    $join_in_current_year = $joining_date->format('Y') == $current_year;
    $days_employed = $current_date->diff($joining_date)->days;
    $months_employed = floor($days_employed / 30);

    // Fetch leave categories per branch
    $leave_categories = $this->db->where('branch_id', $branch_id)
                                 ->get('leave_category')
                                 ->result_array();

    foreach ($leave_categories as $category) {
        $category_id = $category['id'];
        $allowed_days = (int)$category['days'];
        $category_name = strtolower($category['name']);

        switch ($employee->employee_type) {
            case 'intern':
                // Interns receive 1 leave per month, max 3 for 3-month internship
                $leave_balance[$category_id] = min($months_employed, 3);
                break;

            case 'probationary':
                // Probationary: only annual leave accrues at 1 day per 26 days
                if (strpos($category_name, 'annual') !== false) {
                    $leave_balance[$category_id] = floor($days_employed / 26);
                } else {
                    // No sick or casual leave for probationary employees
                    $leave_balance[$category_id] = 0;
                }
                break;

            default: // Regular/Permanent
                // Regular employees get all leave types from day one
                if ($join_in_current_year) {
                    // Pro-rate for employees who joined during current year
                    $remaining_months = 12 - (int)$joining_date->format('n') + 1;
                    $ratio = $remaining_months / 12;
                    $leave_balance[$category_id] = round($allowed_days * $ratio);
                } else {
                    // Full allocation for employees who joined before current year
                    $leave_balance[$category_id] = $allowed_days;
                }
        }

        // Calculate used days
        $used_days = $this->db->select_sum('leave_days')
            ->where([
                'user_id' => $user_id,
                'category_id' => $category_id,
                'status' => 2 // Approved leaves
            ])
            ->get('leave_application')
            ->row()
            ->leave_days ?? 0;

        // Subtract used days from balance
        $leave_balance[$category_id] = max(0, $leave_balance[$category_id] - (float)$used_days);
    }

    return $leave_balance;
}


public function get_accrued_annual_leave($user_id)
{
    $staff = $this->db->get_where('staff', ['id' => $user_id])->row();
    $joining = new DateTime($staff->joining_date);
    $worked_days = (new DateTime())->diff($joining)->days;
    return floor($worked_days / 26);
}
public function get_leave_balance_by_type($user_id, $category_id)
{
    $employee = $this->db->get_where('staff', ['id' => $user_id])->row();
    $balance = $this->calculate_leave_balance($user_id);

    return isset($balance[$category_id]) ? $balance[$category_id] : 0;
}

 public function initialize_leave_balance($user_id, $joining_date_str, $employee_type)
    {
        $joining_date = new DateTime($joining_date_str);
        $today = new DateTime();
        $year = (int)$today->format('Y');

        // Fetch all leave categories
        $leave_categories = $this->db->get('leave_category')->result();

        foreach ($leave_categories as $category) {
            $total_days = 0;

            if ($employee_type === 'regular') {
                $total_days = (float)$category->days;

            } elseif (in_array($employee_type, ['probation', 'intern'])) {

                // Check if joining date is today
                if ($joining_date->format('Y-m-d') === $today->format('Y-m-d')) {
                    $total_days = 0;
                } else {
                    // Calculate days since joining
                    $interval_days = $today->diff($joining_date)->days;

                    if (strtolower($category->name) === 'annual') {
                        $total_days = floor($interval_days / 26); // Prorated annual
                    } else {
                        $total_days = 0;
                    }
                }
            }

            // Prepare leave balance data
            $leave_balance_data = [
                'user_id' => $user_id,
                'leave_category_id' => $category->id,
                'total_days' => $total_days,
                'used_days' => 0,
                'year' => $year,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            // Insert into leave_balance
            $this->db->insert('leave_balance', $leave_balance_data);
        }
    }


public function update_leave_balance($user_id, $joining_date_str, $employee_type)
{
    $joining_date = new DateTime($joining_date_str);
    $today = new DateTime();
    $year = (int)$today->format('Y');

    // Fetch all leave categories
    $leave_categories = $this->db->get('leave_category')->result();

    foreach ($leave_categories as $category) {
        $category_id = (int)$category->id;
        $total_days = 0;

        if ($employee_type === 'regular') {
            $total_days = (float)$category->days;

        } elseif (in_array($employee_type, ['probation', 'intern'])) {
            $interval_days = $today->diff($joining_date)->days;

            if ($interval_days >= 26 && $category_id === 1) {
                // Only annual leave eligible for proration
                $total_days = floor($interval_days / 26);
            } else {
                $total_days = 0;
            }
        }

        // Check if record exists
        $existing = $this->db->get_where('leave_balance', [
            'user_id' => $user_id,
            'leave_category_id' => $category_id,
            'year' => $year
        ])->row();

        if ($existing) {
            // ✅ UPDATE
            $this->db->where('id', $existing->id)
                ->update('leave_balance', [
                    'total_days' => $total_days,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        } else {
            // ✅ INSERT
            $this->db->insert('leave_balance', [
                'user_id' => $user_id,
                'leave_category_id' => $category_id,
                'total_days' => $total_days,
                'used_days' => 0,
                'year' => $year,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
}

/**
 * Get leave balance for specific year
 */
public function get_year_balance($user_id, $year, $category_id = null)
{
    $this->db->where('user_id', $user_id)
             ->where('year', $year);

    if ($category_id) {
        $this->db->where('leave_category_id', $category_id);
        return $this->db->get('leave_balance')->row();
    }

    return $this->db->get('leave_balance')->result();
}

/**
 * Get all years with balance records for a user
 */
public function get_balance_years($user_id)
{
    return $this->db->select('DISTINCT year')
                   ->where('user_id', $user_id)
                   ->order_by('year', 'DESC')
                   ->get('leave_balance')
                   ->result_array();
}

/**
 * Get leave balance for current year
 */
public function get_current_year_balance($user_id, $category_id = null)
{
    $current_year = date('Y');
    $this->db->where('user_id', $user_id)
             ->where('year', $current_year);

    if ($category_id) {
        $this->db->where('leave_category_id', $category_id);
        return $this->db->get('leave_balance')->row();
    }

    return $this->db->get('leave_balance')->result();
}

/**
 * Check if leave balances exist for current year
 */
public function has_current_year_balances($user_id)
{
    $current_year = date('Y');
    $count = $this->db->where('user_id', $user_id)
                     ->where('year', $current_year)
                     ->count_all_results('leave_balance');
    return $count > 0;
}

/**
 * Initialize leave balances for new year if they don't exist
 */
public function ensure_current_year_balances($user_id)
{
    if (!$this->has_current_year_balances($user_id)) {
        $employee = $this->db->get_where('staff', ['id' => $user_id])->row();
        if ($employee) {
            $this->initialize_leave_balance($user_id, $employee->joining_date, $employee->employee_type);
        }
    }
}

}
