<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Advancesalary_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

 // employee basic salary validation by salary template
    public function getBasicSalary($staff_id='', $amount='')
    {
        $q = $this->db->get_where('staff', array('id' => $staff_id))->row_array();
        if (empty($q['salary_template_id']) || $q['salary_template_id'] == 0) {
            return 1;
        } else {
            // Check for latest salary increment with matching template ID
            $latest_increment = $this->db->select('basic_salary')
                ->from('salary_increments')
                ->where('staff_id', $staff_id)
                ->where('salary_template_id', $q['salary_template_id'])
                ->order_by('increment_date', 'DESC')
                ->limit(1)
                ->get()->row();

            if ($latest_increment) {
                $basic_salary = $latest_increment->basic_salary;
            } else {
                $basic_salary = $this->db->get_where("salary_template", array('id' => $q['salary_template_id']))->row()->basic_salary;
            }

            if ($amount > $basic_salary) {
                return 2;
            }
        }
        return 3;
    }

    // employee advance salary validation by month
    public function getAdvanceValidMonth($staff_id, $month)
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

	 public function getAdvanceSalaryList($month = '', $year = '', $branch_id = '', $staff_id = '')
		{
			$login_id = get_loggedin_user_id(); // Current user ID
			$login_role = loggedin_role_id(); // Current user role ID

			// 🔹 Get HOD's department before starting the main query
			$hod_department = '';
			if ($login_role == 8) {
				$hod = $this->db->select('department')
								->where('id', $login_id)
								->get('staff')
								->row();
				if ($hod) {
					$hod_department = $hod->department;
				}
			}

			// 🔹 Now begin the main query
			$this->db->select('advance_salary.*, staff.name, staff.staff_id as uniqid, staff.photo, lc.role as role_id, roles.name as role');
			$this->db->from('advance_salary');
			$this->db->join('staff', 'staff.id = advance_salary.staff_id', 'inner');
			$this->db->join('login_credential as lc', 'lc.user_id = staff.id and lc.role != 6 and lc.role != 7', 'left');
			$this->db->join('roles', 'roles.id = lc.role', 'left');

			// Filters
			if (!empty($month)) {
				$this->db->where('advance_salary.deduct_month', $month);
				$this->db->where('advance_salary.year', $year);
			}

			if (!empty($branch_id)) {
				$this->db->where('advance_salary.branch_id', $branch_id);
			}

			if (!empty($staff_id)) {
				$this->db->where('advance_salary.staff_id', $staff_id);
			}

			// 🔹 Department filter for HOD
			if ($login_role == 8 && !empty($hod_department)) {
				$this->db->where('staff.department', $hod_department);
			}

			$this->db->where('lc.active', 1);
			$this->db->order_by('advance_salary.id', 'DESC');

			return $this->db->get()->result_array();
		}

}
