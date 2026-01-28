<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Probation_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

  public function save_assessment($data)
	{
		$staff_id = $data['staff_id'];
		$current_role = loggedin_role_id();

		for ($i = 1; $i <= 6; $i++) {
			$exists = $this->db->get_where('employee_probation_assessment', [
				'staff_id' => $staff_id,
				'month' => $i
			])->row();

			if ($current_role == 10) {
				// Advisor role - only update advisor fields
				if ($exists) {
					$row = array(
						'advisor_comment' => isset($data['advisor_comment_' . $i]) ? $data['advisor_comment_' . $i] : null,
						'advisor_review' => isset($data['advisor_review_' . $i]) ? $data['advisor_review_' . $i] : 1
					);
					$this->db->where('id', $exists->id);
					$this->db->update('employee_probation_assessment', $row);
				}
			} else {
				// Other roles - update all fields except advisor fields
				$row = array(
					'staff_id' => $staff_id,
					'month' => $i,
					'job_performance' => $data['job_performance_' . $i],
					'analytical' => $data['analytical_' . $i],
					'attitude' => $data['attitude_' . $i],
					'communication' => $data['communication_' . $i],
					'pressure' => $data['pressure_' . $i],
					'attendance' => $data['attendance_' . $i],
					'qr_values' => $data['qr_values_' . $i],
					'initiative' => $data['initiative_' . $i],
					'focus' => $data['focus_' . $i],
					'people' => $data['people_' . $i],
					'decision' => $data['decision_' . $i],
					'remarks' => $data['remarks_' . $i],
					'meeting_date' => !empty($data['meeting_date_' . $i]) ? date('Y-m-d', strtotime($data['meeting_date_' . $i])) : null,
					'meeting_done' => isset($data['meeting_done_' . $i]) ? 1 : 0
				);

				if ($exists) {
					$this->db->where('id', $exists->id);
					$this->db->update('employee_probation_assessment', $row);
				} else {
					$this->db->insert('employee_probation_assessment', $row);
				}
			}
		}
	}


    // GET SINGLE EMPLOYEE DETAILS
  public function getSingleStaff($id = '')
{
    $this->db->select('staff.*, staff_designation.name as designation_name, staff_department.name as department_name, login_credential.role as role_id, login_credential.active, login_credential.username, roles.name as role');
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

    $staff = $query->row_array();

    // Now fetch assessment records per month
    $this->db->where('staff_id', $id);
    $assessment_rows = $this->db->get('employee_probation_assessment')->result_array();

    // Format as [month => [field => value]]
    $assessment = [];
    foreach ($assessment_rows as $row) {
        $month = $row['month'];
        $assessment[$month] = $row;
    }

    $staff['probation_assessment'] = $assessment;
    return $staff;
}

public function getStaffList($branchID = '', $active = 1)
{
	$excludedRoles = [1, 9, 11, 12];
    $excludedStaff = [49, 37, 23];
    $this->db->select('
        staff.*,
        staff_designation.name as designation_name,
        staff_department.name as department_name,
        login_credential.role as role_id,
        roles.name as role
    ');
    $this->db->from('staff');
	$this->db->join('login_credential', 'login_credential.user_id = staff.id','left');

    $this->db->join('roles', 'roles.id = login_credential.role', 'left');
    $this->db->join('staff_designation', 'staff_designation.id = staff.designation', 'left');
    $this->db->join('staff_department', 'staff_department.id = staff.department', 'left');
	$this->db->where_not_in('login_credential.role', $excludedRoles);
    $this->db->where_not_in('staff.id', $excludedStaff);
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
    } elseif ($role_id == 10) {
        // Role 10 – only staff who have data in employee_probation_assessment
        $this->db->where('staff.id IN (SELECT DISTINCT staff_id FROM employee_probation_assessment)');
    } else {
        // Other users – only their own record
        $this->db->where('staff.id', $user_id);
    }

    $this->db->order_by('staff.id', 'ASC');
    return $this->db->get()->result();
}


}
