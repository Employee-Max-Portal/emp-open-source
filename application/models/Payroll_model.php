<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Payroll_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    // employee basic salary validation by salary template
    public function get_basic_salary($staff_id, $amount = 0)
    {
        $q = $this->db->get_where('staff', array('id' => $staff_id))->row_array();
        if (empty($q['salary_template_id']) || $q['salary_template_id'] == 0) {
            return 1;
        } else {
            $basic_salary = $this->db->get_where("salary_template", array('id' => $q['salary_template_id']))->row()->basic_salary;
            if ($amount > $basic_salary) {
                return 2;
            }
        }
        return 3;
    }

    // employee advance salary validation by month
    public function get_advance_valid_month($staff_id, $month)
    {
        $get_advance_month = $this->db->get_where("advance_salary", array(
            "staff_id" => $staff_id,
            "deduct_month" => date("m", strtotime($month)),
            "year" => date("Y", strtotime($month)),
            "status" => 2,
        ))->num_rows();
        $get_salary_month = $this->db->get_where("payslip", array(
            "staff_id" => $staff_id,
            "month" => date("m", strtotime($month)),
            "year" => date("Y", strtotime($month)),
        ))->num_rows();
        if ($get_advance_month == 0 && $get_salary_month == 0) {
            return true;
        } else {
            return false;
        }
    }


	public function save_payslip($data)
{

	$staff_id = $data['staff_id'];
	$month = $data['month'];
	$year = $data['year'];
	$total_allowance = $data['total_allowance'];
	$total_deduction = $data['total_deduction'];
	$net_salary = $data['net_salary'];
	$salary_template_id = $data['salary_template_id'];
	$branchID = $this->application_model->get_branch_id();

	// check if already paid
	$exist_verify = $this->db->select('id')
		->where(['staff_id' => $staff_id, 'month' => $month, 'year' => $year])
		->get('payslip')
		->num_rows();

	if ($exist_verify > 0) {
		return ['status' => 'failed'];
	}

	// Base payslip data
	$arrayPayslip = [
		'staff_id' => $staff_id,
		'month' => $month,
		'year' => $year,
		'basic_salary' => $data['basic_salary'],
		'total_allowance' => $total_allowance,
		'total_deduction' => $total_deduction,
		'net_salary' => $net_salary,
		'bill_no' => $this->app_lib->get_bill_no('payslip'),
		'remarks' => $data['remarks'],
		'hash' => app_generate_hash(),
		'pay_via' => $data['pay_via'],
		'branch_id' => $branchID,
		'paid_by' => get_loggedin_user_id(),
	];

	// Insert payslip
	$this->db->insert('payslip', $arrayPayslip);
	$payslip_id = $this->db->insert_id();

	// Load salary increment if applicable
	$payslipData = [];
	$payment_date = date("Y-m-d", strtotime("$year-$month-01"));

	$increment = $this->db->select('salary_components')
		->where('staff_id', $staff_id)
		->where('increment_date <=', $payment_date)
		->order_by('increment_date', 'DESC')
		->limit(1)
		->get('salary_increments')
		->row_array();

	if (!empty($increment)) {
		// Use salary components from increment (JSON)
		$components = json_decode($increment['salary_components'], true);
		foreach ($components as $row) {
			$payslipData[] = [
				'payslip_id' => $payslip_id,
				'name' => $row['name'],
				'amount' => $row['amount'],
				'type' => $row['type'],
			];
		}
	} else {
		// Use default template
		$getTemplate = $this->get("salary_template_details", ['salary_template_id' => $salary_template_id]);
		foreach ($getTemplate as $row) {
			$payslipData[] = [
				'payslip_id' => $payslip_id,
				'name' => $row['name'],
				'amount' => $row['amount'],
				'type' => $row['type'],
			];
		}
	}

	// Get all advance salary entries for this month/year
	$advance_salaries = $this->db->select('amount, reason')
		->where('staff_id', $staff_id)
		->where('deduct_month', $month)
		->where('year', $year)
		->get('advance_salary')
		->result_array();

	// Add each advance salary as separate deduction
	foreach ($advance_salaries as $index => $advance) {
		$advance_name = count($advance_salaries) > 1 ? "Advance Salary #" . ($index + 1) : "Advance Salary";
		if (!empty($advance['reason'])) {
			$advance_name .= " (" . $advance['reason'] . ")";
		}

		$payslipData[] = [
			'payslip_id' => $payslip_id,
			'name' => $advance_name,
			'amount' => $advance['amount'],
			'type' => 2,
		];
	}

	// Penalties
	if (!empty($data['penalty']) && is_array($data['penalty'])) {
		foreach ($data['penalty'] as $penaltyItem) {
			if (!isset($penaltyItem['type']) || !isset($penaltyItem['value'])) continue;

			$type = $penaltyItem['type'];
			$value = (float)$penaltyItem['value'];
			$penalty_amount = 0;
			$penalty_label = '';

			if ($type === 'attendance') {
				$late_days = (int)$value;
				$basic_salary = isset($data['basic_salary']) ? (float)$data['basic_salary'] : 0;
				if ($late_days >= 3 && $basic_salary > 0) {
					$penalty_count = floor($late_days / 3);
					$attendance_penalty = isset($data['attendance_penalty']) ? (float)$data['attendance_penalty'] : ($basic_salary / 30);
					$penalty_amount = round($penalty_count * $attendance_penalty);
					$day_text = $late_days === 1 ? '1 day' : $late_days . ' days';
					$penalty_label = "Late Attendance - {$day_text}";
				}
			} elseif ($type === 'absent') {
				$absent_days = (int)$value;
				$basic_salary = isset($data['basic_salary']) ? (float)$data['basic_salary'] : 0;
				if ($absent_days > 0 && $basic_salary > 0) {
					$penalty_amount = round(($basic_salary / 30) * $absent_days);
					$day_text = $absent_days === 1 ? '1 day' : $absent_days . ' days';
					$penalty_label = "Absent Penalty - {$day_text}";
				}
			} elseif ($type === 'unpaid_leave') {
				$unpaid_days = (int)$value;
				$basic_salary = isset($data['basic_salary']) ? (float)$data['basic_salary'] : 0;
				if ($unpaid_days > 0 && $basic_salary > 0) {
					$penalty_amount = round(($basic_salary / 30) * $unpaid_days);
					$day_text = $unpaid_days === 1 ? '1 day' : $unpaid_days . ' days';
					$penalty_label = "Unpaid Leave - {$day_text}";
				}
			} elseif ($type === 'rules') {
				$penalty_amount = $value;
				$penalty_label = "Rules Violation";
			} elseif ($type === 'others') {
				$penalty_amount = $value;
				$penalty_label = !empty($penaltyItem['custom_name']) ? $penaltyItem['custom_name'] : "Other Deductions";
			}

			if ($penalty_amount > 0 && $penalty_label != '') {
				$payslipData[] = [
					'payslip_id' => $payslip_id,
					'name' => $penalty_label,
					'amount' => $penalty_amount,
					'type' => 2, // deduction
				];
			}
		}
	}


	// Save all payslip items
	$this->db->insert_batch('payslip_details', $payslipData);

	// Save transaction
	if (isset($data['account_id'])) {
		$this->saveTransaction([
			'account_id' => $data['account_id'],
			'date' => date("Y-m-d"),
			'amount' => $net_salary,
			'month' => $month,
			'year' => $year,
		]);
	}

	// Sync with cashbook
	$this->load->model('cashbook_model');
	$this->syncPayrollWithCashbook($payslip_id);

	// Email
	$payslip_url = base_url('payroll/invoice/' . $payslip_id . '/' . $arrayPayslip['hash']);
	$arrayEmail = [
		'branch_id' => $branchID,
		'name' => get_type_name_by_id('staff', $staff_id),
		'month_year' => date('F', strtotime($year . '-' . $month)),
		'payslip_no' => $arrayPayslip['bill_no'],
		'payslip_url' => $payslip_url,
		'recipient' => get_type_name_by_id('staff', $staff_id, 'email'),
	];
	$this->email_model->sentStaffSalaryPay($arrayEmail);

	return ['status' => 'success', 'uri' => $payslip_url];
}


    // voucher transaction save function
    public function saveTransaction($data)
    {
        $branchID       = $this->application_model->get_branch_id();
        $accountID      = $data['account_id'];
        $amount         = $data['amount'];
        $month          = $data['month'];
        $year           = $data['year'];
        $description    = date("M-Y", strtotime($year . '-' . $month)) . " Paying Employees Salaries";

        // get the current balance of the selected account
        $qbal   = $this->app_lib->get_table('accounts', $accountID, true);
        $cbal   = $qbal['balance'];
        $bal    = ($cbal - $amount);
        // query system voucher head / insert
        $arrayHead = array(
            'name'      => 'Employees Salary Payment',
            'type'      => 'expense',
            'system'    => 1,
            'branch_id' => $branchID
        );
        $this->db->where($arrayHead);
        $query =$this->db->get('voucher_head');
        if ($query->num_rows() == 1) {
            $voucher_headID = $query->row()->id;
        } else {
            $this->db->insert('voucher_head', $arrayHead);
            $voucher_headID = $this->db->insert_id();
        }
        // query system transactions / insert
        $arrayTransactions =array(
            'account_id'        => $accountID,
            'voucher_head_id'   => $voucher_headID,
            'type'              => 'expense',
            'system'            => 1,
            'branch_id'         => $branchID
        );
        $this->db->where($arrayTransactions);
        $this->db->where('description', $description);
        $query =$this->db->get('transactions');
        if ($query->num_rows() > 0) {
            $this->db->set('amount', 'amount+' . $amount, FALSE);
            $this->db->set('dr', 'dr+' . $amount, FALSE);
            $this->db->set('bal', $bal);
            $this->db->where('id', $query->row()->id);
            $this->db->update('transactions');
        } else {
            $arrayTransactions['date']           = date("Y-m-d");
            $arrayTransactions['ref']           = '';
            $arrayTransactions['amount']        = $amount;
            $arrayTransactions['dr']            = $amount;
            $arrayTransactions['cr']            = 0;
            $arrayTransactions['bal']           = $bal;
            $arrayTransactions['pay_via']       = 5;
            $arrayTransactions['description']   = $description;
            $this->db->insert('transactions', $arrayTransactions);
        }

        $this->db->where('id', $accountID);
        $this->db->update('accounts', array('balance' => $bal));
    }

    private function syncPayrollWithCashbook($payslip_id)
    {
        try {
            $this->cashbook_model->syncPayroll($payslip_id);
        } catch (Exception $e) {
            error_log("Payroll Cashbook Sync Error: " . $e->getMessage());
        }
    }

    public function getInvoice($id)
    {
        $this->db->select('payslip.*,staff.name as staff_name,staff.mobileno,IFNULL(staff_designation.name, "N/A") as designation_name,IFNULL(staff_department.name, "N/A") as department_name,branch.business_name,branch.email as school_email,branch.mobileno as school_mobileno,branch.address as school_address');
        $this->db->from('payslip');
        $this->db->join('staff', 'staff.id = payslip.staff_id', 'left');
        $this->db->join('staff_designation', 'staff_designation.id = staff.designation', 'left');
        $this->db->join('staff_department', 'staff_department.id = staff.department', 'left');
        $this->db->join('branch', 'branch.id = staff.branch_id', 'left');
        $this->db->where('payslip.id', $id);
        return $this->db->get()->row_array();
    }

    // get staff all details
    public function getEmployeeList($branch_id, $role_id)
    {
        $this->db->select('staff.*,staff_designation.name as designation_name,staff_department.name as department_name,login_credential.role as role_id, roles.name as role');
        $this->db->from('staff');
        $this->db->join('login_credential', 'login_credential.user_id = staff.id and login_credential.role != 1', 'inner');
        $this->db->join('roles', 'roles.id = login_credential.role', 'left');
        $this->db->join('staff_designation', 'staff_designation.id = staff.designation', 'left');
        $this->db->join('staff_department', 'staff_department.id = staff.department', 'left');
         // Conditional filter for role
		if (!empty($role_id) && $role_id !== 'all') {
			$this->db->where('login_credential.role', $role_id);
		}

		// Conditional filter for branch
		if (!empty($branch_id) && $branch_id !== 'all') {
			$this->db->where('staff.branch_id', $branch_id);
		}

		$this->db->where('login_credential.active', 1);
        $this->db->where_not_in('login_credential.role', [1, 9, 10, 11, 12]);
        $this->db->where_not_in('staff.id', [49]);
       // $this->db->where('staff.designation', $designation);
        return $this->db->get()->result();
    }

    // get employee payment list
	public function getEmployeePaymentList($branch_id = '', $role_id, $month, $year)
	{
		// Validate inputs
		if (!checkdate((int)$month, 1, (int)$year)) {
			return []; // Invalid month/year
		}

		$selected_date = strtotime("$year-$month-01");
		$current_date = strtotime(date('Y-m-01'));

		// Prevent future date processing
		if ($selected_date > $current_date) {
			return [];
		}

		$payment_date = date("Y-m-d", $selected_date);

		$this->db->select('staff.*, staff_designation.name as designation_name, staff_department.name as department_name, login_credential.role as role_id, roles.name as role, IFNULL(payslip.id, 0) as salary_id, payslip.hash as salary_hash, salary_template.name as template_name, salary_template.basic_salary');
		$this->db->from('staff');
		$this->db->join('login_credential', 'login_credential.user_id = staff.id and login_credential.role != 1', 'inner');
		$this->db->join('roles', 'roles.id = login_credential.role', 'left');
		$this->db->join('staff_designation', 'staff_designation.id = staff.designation', 'left');
		$this->db->join('staff_department', 'staff_department.id = staff.department', 'left');
		$this->db->join('payslip', 'payslip.staff_id = staff.id and payslip.month = ' . $this->db->escape($month) . ' and payslip.year = ' . $this->db->escape($year), 'left');
		$this->db->join('salary_template', 'salary_template.id = staff.salary_template_id', 'left');

		if (!empty($role_id) && $role_id !== 'all') {
			$this->db->where('login_credential.role', $role_id);
		}
		if (!empty($branch_id) && $branch_id !== 'all') {
			$this->db->where('staff.branch_id', $branch_id);
		}

        $this->db->where_not_in('login_credential.role', [1, 9, 10, 11, 12]);
        $this->db->where_not_in('staff.id', [49]);
		$this->db->where('login_credential.active', 1);
		$this->db->where('staff.salary_template_id !=', 0);
		$staff_list = $this->db->get()->result();

		foreach ($staff_list as &$staff) {
			// Salary increment - get latest increment for this staff
			$increment = $this->db->select('basic_salary, salary_components, increment_percentage, new_salary')
				->where('staff_id', $staff->id)
				->where('increment_date <=', $payment_date)
				->order_by('increment_date', 'DESC')
				->limit(1)
				->get('salary_increments')
				->row_array();

			if (!empty($increment)) {
				$staff->basic_salary = $increment['basic_salary'];
				$staff->increment_percentage = $increment['increment_percentage'];
				$staff->increment_components = json_decode($increment['salary_components'], true);
				$staff->current_gross_salary = $increment['new_salary'];
			} else {
				// Calculate gross from template if no increment
				$template_details = $this->db->get_where('salary_template_details', ['salary_template_id' => $staff->salary_template_id])->result_array();
				$gross = (float)$staff->basic_salary;
				foreach ($template_details as $detail) {
					$gross += (float)$detail['amount'];
				}
				$staff->current_gross_salary = $gross;
			}

			// Salary template and components
			$staff->template_info = $this->db->get_where('salary_template', ['id' => $staff->salary_template_id])->row_array();
			$staff->template_components = $this->db->get_where('salary_template_details', ['salary_template_id' => $staff->salary_template_id])->result_array();

			// Bank info
			$staff->bank_account = $this->db->get_where('staff_bank_account', ['staff_id' => $staff->id])->row_array();

			// Get all advance salary entries for this month/year
			$advance_salaries = $this->db->select('amount, reason')
				->where('staff_id', $staff->id)
				->where('deduct_month', $month)
				->where('year', $year)
				->get('advance_salary')
				->result_array();

			// Calculate total advance amount
			$total_advance_amount = 0;
			foreach ($advance_salaries as $advance) {
				$total_advance_amount += $advance['amount'];
			}

			$staff->advance_amount = $total_advance_amount;
			$staff->advance_entries = $advance_salaries;

			// Late count
			$staff->late_count = $this->db->where('staff_id', $staff->id)
				->where('status', 'L')
				->where('MONTH(date)', $month)
				->where('YEAR(date)', $year)
				->count_all_results('staff_attendance');

			// Present dates
			$present_dates = $this->db->select('DATE(date) as date')
				->where('staff_id', $staff->id)
				->where_in('status', ['P', 'L'])
				->where('MONTH(date)', $month)
				->where('YEAR(date)', $year)
				->get('staff_attendance')
				->result_array();
			$present_dates = array_column($present_dates, 'date');

			// Leaves
			$accepted_leave_dates = [];
			$leave_dates = $this->db->select('start_date, end_date')
				->where('user_id', $staff->id)
				->where('status', 2)
				->where('category_id !=', 'unpaid')
				->where('category_id =', 'parental')
				->where('MONTH(start_date) <=', $month)
				->where('MONTH(end_date) >=', $month)
				->where('YEAR(start_date) <=', $year)
				->where('YEAR(end_date) >=', $year)
				->get('leave_application')->result_array();

			foreach ($leave_dates as $leave) {
				try {
					$period = new DatePeriod(
						new DateTime($leave['start_date']),
						new DateInterval('P1D'),
						(new DateTime($leave['end_date']))->modify('+1 day')
					);
					foreach ($period as $dt) {
						if ($dt->format('Y-m') == "$year-$month") {
							$accepted_leave_dates[] = $dt->format('Y-m-d');
						}
					}
				} catch (Exception $e) {
					continue;
				}
			}

			// Holidays
			$holiday_dates = [];
			$holidays = $this->db->select('start_date, end_date')
				->where('type', 'holiday')
				->where('status', 1)
				->where('MONTH(start_date) <=', $month)
				->where('MONTH(end_date) >=', $month)
				->where('YEAR(start_date) <=', $year)
				->where('YEAR(end_date) >=', $year)
				->get('event')->result_array();

			foreach ($holidays as $holiday) {
				try {
					$period = new DatePeriod(
						new DateTime($holiday['start_date']),
						new DateInterval('P1D'),
						(new DateTime($holiday['end_date']))->modify('+1 day')
					);
					foreach ($period as $dt) {
						if ($dt->format('Y-m') == "$year-$month") {
							$holiday_dates[] = $dt->format('Y-m-d');
						}
					}
				} catch (Exception $e) {
					continue;
				}
			}

			// Absent calculation
			$total_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
			$absent_count = 0;

			for ($d = 1; $d <= $total_days; $d++) {
				$date = "$year-$month-" . str_pad($d, 2, '0', STR_PAD_LEFT);
				$day_of_week = date('N', strtotime($date));

				if (in_array($date, $present_dates) || in_array($date, $accepted_leave_dates) || in_array($date, $holiday_dates)) {
					continue;
				}

				if (in_array($day_of_week, [5, 6])) {
					continue; // Skip weekends (Fri, Sat)
				}

				$absent_count++;
			}

			$staff->absent_count = $absent_count;

			// Warnings
			$staff->warnings = $this->db
				->where('user_id', $staff->id)
				->where('status', 1)
				->order_by('issue_date', 'DESC')
				->get('warnings')
				->result_array();

			// Check for active salary blocks
			$salary_blocks = $this->db
				->where('staff_id', $staff->id)
				->where('status', 1) // Only pending blocks
				->get('salary_blocks')
				->result_array();

			$staff->salary_blocks = $salary_blocks;
			$staff->has_salary_block = !empty($salary_blocks);
		}

		return $staff_list;
	}

    // get employee payment list
    public function getEmployeePayment($staff_id, $month, $year)
{
    // Check for adjustment first
    $adjustment = $this->db->get_where('salary_adjustments', [
        'staff_id' => $staff_id,
        'month' => $month,
        'year' => $year
    ])->row();

    $sql = "SELECT `staff`.*, `staff_designation`.`name` as `designation_name`, `staff_department`.`name` as `department_name`, `login_credential`.`role` as `role_id`, `roles`.`name` as `role`,
    `salary_template`.`name` as `template_name`, `salary_template`.`basic_salary`, `salary_template`.`overtime_salary`, `salary_template`.`attendance_penalty`
    FROM `staff`
    INNER JOIN `login_credential` ON `login_credential`.`user_id` = `staff`.`id`
    LEFT JOIN `roles` ON `roles`.`id` = `login_credential`.`role`
    LEFT JOIN `staff_designation` ON `staff_designation`.`id` = `staff`.`designation`
    LEFT JOIN `staff_department` ON `staff_department`.`id` = `staff`.`department`
    LEFT JOIN `salary_template` ON `salary_template`.`id` = `staff`.`salary_template_id`
    WHERE `staff`.`id` = " . $this->db->escape($staff_id);

    $staff = $this->db->query($sql)->row_array();

    // If adjustment exists, override with adjustment data
    if ($adjustment) {
        $staff['basic_salary'] = $adjustment->basic_salary;
        $staff['has_adjustment'] = true;
        return $staff;
    }

    // Get all advance salary entries for this month/year
    $advance_salaries = $this->db->select('amount, reason')
        ->where('staff_id', $staff_id)
        ->where('deduct_month', $month)
        ->where('year', $year)
        ->get('advance_salary')
        ->result_array();

    // Calculate total advance amount
    $total_advance_amount = 0;
    foreach ($advance_salaries as $advance) {
        $total_advance_amount += $advance['amount'];
    }

    $staff['advance_amount'] = $total_advance_amount;
    $staff['advance_entries'] = $advance_salaries;

	$start_date = "$year-$month-01";
	$end_date = date("Y-m-t", strtotime($start_date)); // Gets last date of the month (e.g., 2025-02-28 or 2025-03-31)

    // NEW LOGIC: Check for salary increment applicable for this month
    $payment_date = date("Y-m-d", strtotime("$year-$month-01"));
	$increment = $this->db->select('si.basic_salary, si.salary_components, si.increment_percentage, si.new_salary')
		->from('salary_increments si')
		->where('si.staff_id', $staff_id)
		->where('si.increment_date <=', $payment_date)
		->order_by('si.increment_date', 'DESC')
		->limit(1)
		->get()
		->row_array();


    if (!empty($increment)) {
        // Apply incremented salary structure
        $staff['basic_salary'] = $increment['basic_salary'];
        $staff['salary_template_id'] = null; // skip template fetch
        $staff['increment_components'] = json_decode($increment['salary_components'], true);
		$staff['increment_percentage'] = $increment['increment_percentage']; // NEW
    }

	// Late attendance count for the selected month
	$late_count = $this->db
		->where('staff_id', $staff_id)
		->where('status', 'L')
		->where('MONTH(date)', $month)
		->where('YEAR(date)', $year)
		->count_all_results('staff_attendance');

	$staff['late_count'] = $late_count;


	// Get total days of the selected month
	$total_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

	// Step 1: Fetch all present or late days
	$present_dates = $this->db
		->select('DATE(date) as date')
		->where('staff_id', $staff_id)
		->where_in('status', ['P', 'L'])
		->where('MONTH(date)', $month)
		->where('YEAR(date)', $year)
		->get('staff_attendance')
		->result_array();
	$present_dates = array_column($present_dates, 'date');

	// Step 2: Accepted leave days
	$leave_dates = $this->db->select('start_date, end_date')
		->where('user_id', $staff_id)
		->where('status', 2)
		->where('start_date <=', $end_date)
		->where('end_date >=', $start_date)
		->get('leave_application')
		->result_array();

	$accepted_leave_dates = [];
	foreach ($leave_dates as $leave) {
		$period = new DatePeriod(
			new DateTime($leave['start_date']),
			new DateInterval('P1D'),
			(new DateTime($leave['end_date']))->modify('+1 day')
		);
		foreach ($period as $dt) {
			if ($dt->format('Y-m') == "$year-$month") {
				$accepted_leave_dates[] = $dt->format('Y-m-d');
			}
		}
	}

	// Step 3: Fetch holiday dates from the `event` table
	$holiday_dates = [];

	$start_date = "$year-$month-01";
	$end_date = date("Y-m-t", strtotime($start_date)); // Gets last date of the month (e.g., 2025-02-28 or 2025-03-31)

	$holidays = $this->db->select('start_date, end_date')
		->where('type', 'holiday')
		->where('status', 1)
		->where('start_date <=', $end_date)
		->where('end_date >=', $start_date)
		->get('event')
		->result_array();


	foreach ($holidays as $holiday) {
		$period = new DatePeriod(
			new DateTime($holiday['start_date']),
			new DateInterval('P1D'),
			(new DateTime($holiday['end_date']))->modify('+1 day')
		);
		foreach ($period as $dt) {
			if ($dt->format('Y-m') == "$year-$month") {
				$holiday_dates[] = $dt->format('Y-m-d');
			}
		}
	}


	// Step 4: Loop and count absents (excluding weekends + holidays)
	$absent_count = 0;
	for ($d = 1; $d <= $total_days; $d++) {
		$date = "$year-$month-" . str_pad($d, 2, '0', STR_PAD_LEFT);
		$day_of_week = date('N', strtotime($date)); // 6 = Saturday, 7 = Sunday

		if (in_array($date, $present_dates) || in_array($date, $accepted_leave_dates) || in_array($date, $holiday_dates)) {
			continue; // skip present, leave, holiday
		}

		if (in_array($day_of_week, [5, 6])) {
			continue; // skip weekends
		}

		$absent_count++;
	}

	$staff['absent_count'] = $absent_count;

	$warnings = $this->db
    ->where('user_id', $staff_id)
	->where('status', '1')
	->or_where('status', '4')
    ->order_by('issue_date', 'DESC')
    ->get('warnings')
    ->result_array();

	$staff['warnings'] = $warnings;

	// Check for active salary blocks
	$salary_blocks = $this->db
		->where('staff_id', $staff_id)
		->where('status', 1) // Only pending blocks
		->get('salary_blocks')
		->result_array();

	$staff['salary_blocks'] = $salary_blocks;
	$staff['has_salary_block'] = !empty($salary_blocks);

	// Step 2.1: Unpaid leave days (category_id = 0)
	$unpaid_leave_dates = [];
	$unpaid_leaves = $this->db->select('start_date, end_date')
		->where('user_id', $staff_id)
		->where('status', 2)
		->where('category_id', 'unpaid') // Unpaid leave
		->where('start_date <=', $end_date)
		->where('end_date >=', $start_date)
		->get('leave_application')
		->result_array();

	foreach ($unpaid_leaves as $leave) {
		$period = new DatePeriod(
			new DateTime($leave['start_date']),
			new DateInterval('P1D'),
			(new DateTime($leave['end_date']))->modify('+1 day')
		);
		foreach ($period as $dt) {
			if ($dt->format('Y-m') == "$year-$month") {
				$unpaid_leave_dates[] = $dt->format('Y-m-d');
			}
		}
	}

	// Optional: store unpaid leave count
	$staff['unpaid_leaves'] = count($unpaid_leave_dates);
	$staff['has_adjustment'] = false;
    return $staff;
}


    public function getAdvanceSalaryList($month='', $year='', $branch_id = '')
    {
        $this->db->select('advance_salary.*,staff.name,staff.photo,login_credential.role as role_id,roles.name as role');
        $this->db->from('advance_salary');
        $this->db->join('staff', 'staff.id = advance_salary.staff_id', 'inner');
        $this->db->join('login_credential', 'login_credential.user_id = staff.id and login_credential.role != 6 and login_credential.role != 7', 'left');
        $this->db->join('roles', 'roles.id = login_credential.role', 'left');
        if (!empty($month)) {
            $this->db->where('advance_salary.deduct_month', $month);
            $this->db->where('advance_salary.year', $year);
        }
        if (!empty($branch_id)) {
            $this->db->where('advance_salary.branch_id', $branch_id);
        }
        return $this->db->get()->result_array();
    }

    // get summary report function
    public function get_summary($branch_id, $month = '', $year = '', $staffID)
    {
		$excluded_roles = [1, 2, 3, 5];
		$logged_role_id = loggedin_role_id();

        $this->db->select('payslip.*,staff.name as staff_name,staff.mobileno,IFNULL(staff_designation.name, "N/A") as designation_name,IFNULL(staff_department.name, "N/A") as department_name,payment_types.name as payvia');
        $this->db->from('payslip');
        $this->db->join('staff', 'staff.id = payslip.staff_id', 'left');
        $this->db->join('staff_designation', 'staff_designation.id = staff.designation', 'left');
        $this->db->join('staff_department', 'staff_department.id = staff.department', 'left');
        $this->db->join('payment_types', 'payment_types.id = payslip.pay_via', 'left');

		if (!in_array($logged_role_id, $excluded_roles) && !empty($staffID)) {
			$this->db->where('payslip.staff_id', get_loggedin_user_id());
		}

		// Conditional filter for branch
		if (!empty($branch_id) && $branch_id !== 'all') {
			$this->db->where('payslip.branch_id', $branch_id);
		}

        $this->db->where('payslip.month', $month);
        $this->db->where('payslip.year', $year);
        return $this->db->get()->result_array();
    }

	 // get staff all list
    public function getStaffList($active = 1)
    {
        $this->db->select('staff.*,staff_designation.name as designation_name,staff_department.name as department_name,login_credential.role as role_id, roles.name as role');
        $this->db->from('staff');
        $this->db->join('login_credential', 'login_credential.user_id = staff.id and login_credential.role != "1"', 'inner');
        $this->db->join('roles', 'roles.id = login_credential.role', 'left');
        $this->db->join('staff_designation', 'staff_designation.id = staff.designation', 'left');
        $this->db->join('staff_department', 'staff_department.id = staff.department', 'left');
        $this->db->where('login_credential.active', $active);
        $this->db->order_by('staff.id', 'ASC');
        return $this->db->get()->result();
    }
}
