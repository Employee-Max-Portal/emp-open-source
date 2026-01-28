<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Organization_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

	public function get_all()
	{
		$this->db->select('organization_chart.*, staff.name as staff_name, staff_department.name as department_name')
			->from('organization_chart')
			->join('staff', 'staff.id = organization_chart.staff_id', 'left')
			->join('login_credential lc', 'lc.user_id = staff.id', 'left')
			->join('staff_department', 'staff_department.id = organization_chart.department_id', 'left')
			->where('lc.active', 1) // only active staff
			->order_by('organization_chart.display_order', 'ASC');

		return $this->db->get()->result_array();
	}


    public function get_single($id)
    {
        return $this->db->get_where('organization_chart', ['id' => $id])->row_array();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update('organization_chart', $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('organization_chart');
    }

  public function get_staff_list()
{
    $this->db->select('id, name')
             ->from('staff')
             ->where('id', 1); // Correct way to write "id not equal to 1"
    return $this->db->get()->result_array();
}


    public function get_department_list()
    {
        return $this->db->select('id, name')->get('staff_department')->result_array();
    }


    public function get_active_staff()
    {
        $this->db->select('
            staff.id, staff.name, staff.staff_id, staff.department, staff.photo,
            login_credential.role as main_role, roles.id as role_id, roles.name AS role_name,
            staff_department.name as department_name,
            staff_department.branch_id,
            branch.name as branch_name
        ')
        ->from('staff')
        ->join('login_credential', 'login_credential.user_id = staff.id')
        ->join('roles', 'roles.id = login_credential.role', 'left')
        ->join('staff_department', 'staff_department.id = staff.department', 'left')
        ->join('branch', 'branch.id = staff_department.branch_id', 'left')
        ->where('login_credential.active', 1)
        ->order_by('staff.id', 'ASC')
        ->order_by('roles.id', 'ASC');

        return $this->db->get()->result_array();
    }
}
