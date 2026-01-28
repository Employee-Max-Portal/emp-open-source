<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Fundrequisition_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

	public function get_all_categories() {
        $this->db->select('*');
        $this->db->from('fund_category');
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get();
        return $query->result_array();
    }

	 public function category_save($data)
    {
        $arrayData = array(
            'name' => $data['name'],
        );
       if (!isset($data['category_id']) || empty($data['category_id'])) {
        // INSERT new category
        $this->db->insert('fund_category', $arrayData);
		} else {
            $this->db->where('id', $data['category_id']);
            $this->db->update('fund_category', $arrayData);
        }
    }


public function getFundRequisitions_request($start_date = '', $end_date = '')
{
    $this->db->select('fr.*, s.name, s.staff_id, s.photo, fc.name as category');
    $this->db->from('fund_requisition fr');
    $this->db->join('staff s', 's.id = fr.staff_id', 'left');
    $this->db->join('fund_category fc', 'fc.id = fr.category_id', 'left');
	$this->db->join('login_credential as lc', 'lc.user_id = s.id', 'left');
    if (!empty($start_date) && !empty($end_date)) {
        $this->db->where('fr.create_at >=', $start_date);
        $this->db->where('fr.create_at <=', $end_date);
    }

    // Role-based access control
    $role_id = loggedin_role_id();
    $allowed_roles = [1, 2]; // Roles that can see all data

    if (!in_array($role_id, $allowed_roles)) {
        // For other roles, only show data from their department
		$staff_id = get_loggedin_user_id();
		$this->db->where('fr.staff_id', $staff_id);
    }

	$this->db->where('lc.active', 1);
	$this->db->order_by('fr.id', 'DESC');
    return $this->db->get()->result_array();
}


    // employee basic salary validation by salary template
    public function getBasicSalary($staff_id='', $amount='')
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


	 public function getFundRequisitions($start_date = '', $end_date = '', $staff_id = '')
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
			$this->db->select('fund_requisition.*, staff.name, staff.staff_id, staff.photo, lc.role as role_id, roles.name as role, fc.name as category');
			$this->db->from('fund_requisition');
			$this->db->join('staff', 'staff.id = fund_requisition.staff_id', 'inner');
			$this->db->join('login_credential as lc', 'lc.user_id = staff.id', 'left');
			$this->db->join('roles', 'roles.id = lc.role', 'left');
			$this->db->join('fund_category fc', 'fc.id = fund_requisition.category_id', 'left');

			if (!empty($start_date) && !empty($end_date)) {
				$this->db->where('DATE(fund_requisition.request_date) >=', $start_date);
				$this->db->where('DATE(fund_requisition.request_date) <=', $end_date);

			}

			if ($login_role == 4) {
				$this->db->where('fund_requisition.staff_id', $staff_id);
			}

			// 🔹 Department filter for HOD
			if ($login_role == 8 && !empty($hod_department)) {
				$this->db->where('staff.department', $hod_department);
			}

			$this->db->order_by('fund_requisition.id', 'DESC');

			return $this->db->get()->result_array();
		}

}
