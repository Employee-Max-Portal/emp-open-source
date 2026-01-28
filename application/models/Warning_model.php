<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Warning_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

   public function getWarnings($branch_id = null, $staff_id = null)
	{
	$login_id = get_loggedin_user_id();
    $login_role = loggedin_role_id();

    // 🔹 Get HOD's department
    $hod_department = '';
    if ($login_role == 8) {
        $hod = $this->db->select('department')->where('id', $login_id)->get('staff')->row();
        if ($hod) {
            $hod_department = $hod->department;
        }
    }

    $this->db->select('w.*, s.name, s.staff_id');
    $this->db->from('warnings w');
    $this->db->join('staff s', 's.id = w.user_id', 'left');
	$this->db->join('login_credential as lc', 'lc.user_id = s.id', 'left');

    // Apply filters conditionally
    if (!empty($branch_id) && $branch_id !== 'all') {
			$this->db->where('s.branch_id', $branch_id);
		}

  // 🔐 Restrict to staff’s own warnings if role not in [1, 2, 3, 5]
    if (!in_array(loggedin_role_id(), [1, 2, 3, 5, 9,10])) {
        if ($login_role == 8 && !empty($hod_department)) {
            // HOD can see warnings in their department
            $this->db->where('s.department', $hod_department);
        } else {
            // Others can only see their own
            $this->db->where('w.user_id', $login_id);
        }
    }

	$this->db->where('lc.active', 1);
	// 👇 Add ordering by newest warning first
    $this->db->order_by('w.id', 'DESC');

    return $this->db->get()->result_array();
	}

    public function getWarningList($filters = '')
    {
        $this->db->select('w.*, s.name, s.staff_id');
        $this->db->from('warnings w');
        $this->db->join('staff s', 's.id = w.user_id', 'left');
        if (!empty($filters)) {
            $this->db->where($filters);
        }
        return $this->db->get()->result_array();

    }

    public function saveWarning($data)
    {
        $this->db->insert('warnings', $data);
        return $this->db->insert_id();
    }

    public function deleteWarning($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('warnings');
    }

	public function getWarningById($id)
{
    return $this->db
        ->select('w.*, s.name, s.staff_id, p.title, pd.*')
        ->from('warnings w')
        ->join('staff s', 's.id = w.user_id', 'left')
        ->join('policy p', 'p.id = w.policy_id', 'left')
        ->join('penalty_days pd', 'pd.warning_id = w.id', 'left')
        ->where('w.id', $id)
        ->get()
        ->row_array();
}

public function getWarningByIds($id)
{
    // Fetch main warning info
    $warning = $this->db
        ->select('w.*, s.name, s.staff_id, p.title')
        ->from('warnings w')
        ->join('staff s', 's.id = w.user_id', 'left')
        ->join('policy p', 'p.id = w.policy_id', 'left')
        ->where('w.id', $id)
        ->get()
        ->row_array();

    // Fetch all penalty days for this warning
    $penalty_days = $this->db
        ->select('*')
        ->from('penalty_days')
        ->where('warning_id', $id)
        ->order_by('penalty_date', 'asc')
        ->get()
        ->result_array();

    // Attach to main array
    $warning['penalty_days'] = $penalty_days;

    return $warning;
}


public function getPenalties($start_date = '', $end_date = '')
{
    $this->db->select('pd.*, w.reason AS warning_reason, w.issue_date, w.category, w.effect, s.name, s.staff_id');
    $this->db->from('penalty_days pd');
    $this->db->join('warnings w', 'w.id = pd.warning_id', 'left');
    $this->db->join('staff s', 's.id = pd.staff_id', 'left');

    if (!empty($start_date) && !empty($end_date)) {
        $this->db->where('pd.penalty_date >=', $start_date);
        $this->db->where('pd.penalty_date <=', $end_date);
    }

    // Role-based access control
    $role_id = loggedin_role_id();
    $allowed_roles = [1, 2, 3, 5]; // Roles that can see all data

    if (!in_array($role_id, $allowed_roles)) {
        // For other roles, only show data from their department
		$staff_id = get_loggedin_user_id();
		$this->db->where('pd.staff_id', $staff_id);
    }

    return $this->db->get()->result_array();
}


}
