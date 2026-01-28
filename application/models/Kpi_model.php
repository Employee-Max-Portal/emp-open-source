<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kpi_model extends MY_Model {

	public function save_assessment($data)
{
    // Optional: remove 'submit' if present
    unset($data['submit']);

    $save_data = [
        'staff_id' => $data['staff_id'],
        'manager_id' => $data['manager_id'],

        'customer_first_staff_rating' => $data['customer_first_staff_rating'],
        'customer_first_manager_rating' => $data['customer_first_manager_rating'],
        'customer_first_staff_feedback' => $data['customer_first_staff_feedback'],
        'customer_first_manager_feedback' => $data['customer_first_manager_feedback'],

        'personal_effectiveness_staff_rating' => $data['personal_effectiveness_staff_rating'],
        'personal_effectiveness_manager_rating' => $data['personal_effectiveness_manager_rating'],
        'personal_effectiveness_staff_feedback' => $data['personal_effectiveness_staff_feedback'],
        'personal_effectiveness_manager_feedback' => $data['personal_effectiveness_manager_feedback'],

        'driven_to_deliver_staff_rating' => $data['driven_to_deliver_staff_rating'],
        'driven_to_deliver_manager_rating' => $data['driven_to_deliver_manager_rating'],
        'driven_to_deliver_staff_feedback' => $data['driven_to_deliver_staff_feedback'],
        'driven_to_deliver_manager_feedback' => $data['driven_to_deliver_manager_feedback'],

        'commercial_focus_staff_rating' => $data['commercial_focus_staff_rating'],
        'commercial_focus_manager_rating' => $data['commercial_focus_manager_rating'],
        'commercial_focus_staff_feedback' => $data['commercial_focus_staff_feedback'],
        'commercial_focus_manager_feedback' => $data['commercial_focus_manager_feedback'],

        'upholding_standards_staff_rating' => $data['upholding_standards_staff_rating'],
        'upholding_standards_manager_rating' => $data['upholding_standards_manager_rating'],
        'upholding_standards_staff_feedback' => $data['upholding_standards_staff_feedback'],
        'upholding_standards_manager_feedback' => $data['upholding_standards_manager_feedback'],

        'inspiring_leadership_staff_rating' => $data['inspiring_leadership_staff_rating'],
        'inspiring_leadership_manager_rating' => $data['inspiring_leadership_manager_rating'],
        'inspiring_leadership_staff_feedback' => $data['inspiring_leadership_staff_feedback'],
        'inspiring_leadership_manager_feedback' => $data['inspiring_leadership_manager_feedback'],
    ];

    // Check if a record already exists for this staff + manager
    $existing = $this->db->get_where('kpi_comp_behav', [
        'staff_id' => $data['staff_id'],
        'manager_id' => $data['manager_id']
    ])->row();

    if ($existing) {
        // Update
        $this->db->where('id', $existing->id);
        $this->db->update('kpi_comp_behav', $save_data);
    } else {
        // Insert
        $this->db->insert('kpi_comp_behav', $save_data);
    }
}

	public function get_kpi_data_by_user($user_id)
	{
		$this->db->where('staff_id', $user_id);
		$forms = $this->db->get('kpi_form')->result_array();

		foreach ($forms as &$form) {
			$form_id = $form['id'];
			$form['subtasks'] = $this->db->where('kpi_form_id', $form_id)->get('kpi_form_details')->result_array();
			$form['total_weight'] = array_sum(array_column($form['subtasks'], 'weight'));
			$form['progress'] = min(intval($form['total_weight']), 100);
		}

		return $forms;
	}

	public function get_kpi_comp_behav_by_user($staff_id)
	{
		return $this->db
			->where('staff_id', $staff_id)
			->get('kpi_comp_behav')
			->row_array(); // returns as an associative array
	}

	public function get_kpiApprovals($staff_id)
	{
		$this->db->select('kpi_approval.*,staff.name');
        $this->db->from('kpi_approval');
        $this->db->join('staff', 'staff.id = kpi_approval.created_by', 'inner');
        $this->db->where('kpi_approval.staff_id', $staff_id);
        $this->db->order_by('kpi_approval.id', 'DESC');
        return $this->db->get()->result();
	}

	public function get_kpiFeedbacks($staff_id)
	{
		$this->db->select('kpi_feedback.*, staff.name as submitted_by_name');
		$this->db->from('kpi_feedback');
		$this->db->join('staff', 'staff.id = kpi_feedback.submitted_by', 'inner');
		$this->db->where('kpi_feedback.staff_id', $staff_id);
		$this->db->order_by('kpi_feedback.id', 'DESC');
		return $this->db->get()->result(); // or ->result_array() if needed
	}


     // get staff all list
   public function getStaffList($branchID = '', $active = 1)
{
    $this->db->select('
        staff.*,
        staff_designation.name as designation_name,
        staff_department.name as department_name,
        login_credential.role as role_id,
        roles.name as role,
        COALESCE(kpi_data.avg_staff_rating, 0) as avg_staff_rating,
        COALESCE(kpi_data.avg_manager_rating, 0) as avg_manager_rating,
        COALESCE(kpi_data.kpi_count, 0) as total_kpi
    ');
    $this->db->from('staff');
    $this->db->join('login_credential', 'login_credential.user_id = staff.id AND login_credential.role != 1', 'inner');
    $this->db->join('roles', 'roles.id = login_credential.role', 'left');
    $this->db->join('staff_designation', 'staff_designation.id = staff.designation', 'left');
    $this->db->join('staff_department', 'staff_department.id = staff.department', 'left');

    // Join KPI aggregated data
    $this->db->join('(SELECT
                        staff_id,
                        AVG(staff_rating) AS avg_staff_rating,
                        AVG(manager_rating) AS avg_manager_rating,
                        COUNT(*) AS kpi_count
                     FROM kpi_form
                     GROUP BY staff_id
                    ) AS kpi_data', 'kpi_data.staff_id = staff.id', 'left');

    if ($branchID != "") {
        $this->db->where('staff.branch_id', $branchID);
    }

    $this->db->where('login_credential.active', $active);

	 // Get current logged-in role and user ID
    $role_id = loggedin_role_id();
    $user_id = get_loggedin_user_id();

    // Apply corrected role-based filter logic
    if (in_array($role_id, [1, 2, 3, 5])) {
        // Super admins and HR roles – get all staff → no condition
    } elseif ($role_id == 8) {
        // Department head – get staff from same department
        $this->db->where('staff.department = (SELECT department FROM staff WHERE id = ' . $this->db->escape($user_id) . ')');
    } else {
        // Other users – only their own record
        $this->db->where('staff.id', $user_id);
    }

    $this->db->order_by('staff.id', 'ASC');

    return $this->db->get()->result();
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
}