<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ajax extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('ajax_model');
    }

    public function getAdvanceSalaryDetails()
    {
        if (get_permission('advance_salary', 'is_add')) {
            $this->data['salary_id'] = $this->input->post('id');
            $this->load->view('advance_salary/approvel_modalView', $this->data);
        }
    }
    public function getFundRequisitionDetails()
    {
        if (get_permission('fund_requisition_manage', 'is_add')) {
            $this->data['fund_id'] = $this->input->post('id');
            $this->load->view('fund_requisition/approvel_modalView', $this->data);
        }
    }

    public function getLeaveCategoryDetails()
    {
        if (get_permission('leave_category', 'is_edit')) {
            $id = $this->input->post('id');
            $this->db->where('id', $id);
            $query = $this->db->get('leave_category');
            $result = $query->row_array();
            echo json_encode($result);
        }
    }

    public function getDataByBranch()
    {
        $html = "";
        $table = $this->input->post('table');
        $branch_id = $this->application_model->get_branch_id();
        if (!empty($branch_id)) {
            $result = $this->db->select('id,name')->where('branch_id', $branch_id)->get($table)->result_array();
            if (count($result)) {
                $html .= "<option value=''>" . translate('select') . "</option>";
                foreach ($result as $row) {
                    $html .= '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select_branch_first') . '</option>';
        }
        echo $html;
    }

    public function getStafflistRole()
    {
        $html = "";
        $branch_id = $this->application_model->get_branch_id();
        if (!empty($branch_id)) {
            $role_id = $this->input->post('role_id');
            $selected_id = (isset($_POST['staff_id']) ? $_POST['staff_id'] : 0);
            $this->db->select('staff.id,staff.name,staff.staff_id,lc.role');
            $this->db->from('staff');
            $this->db->join('login_credential as lc', 'lc.user_id = staff.id AND lc.role != 6 AND lc.role != 7', 'inner');
            if (!empty($role_id)) {
                $this->db->where('lc.role', $role_id);
            }
            $this->db->where('staff.branch_id', $branch_id);
            $this->db->order_by('staff.id', 'asc');
            $result = $this->db->get()->result_array();
            if (count($result)) {
                $html .= "<option value=''>" . translate('select') . "</option>";
                foreach ($result as $staff) {
                    $selected = ($staff['id'] == $selected_id ? 'selected' : '');
                    $html .= "<option value='" . $staff['id'] . "' " . $selected . ">" . $staff['name'] . " (" . $staff['staff_id'] . ")</option>";
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select_branch_first') . '</option>';
        }
        echo $html;
    }

    // get staff all details
    public function getEmployeeList()
    {
        $html = "";
        $role_id = $this->input->post('role');
        $designation = $this->input->post('designation');
        $department = $this->input->post('department');
        $selected_id = (isset($_POST['staff_id']) ? $_POST['staff_id'] : 0);
        $this->db->select('staff.*,staff_designation.name as des_name,staff_department.name as dep_name,login_credential.role as role_id, roles.name as role');
        $this->db->from('staff');
        $this->db->join('login_credential', 'login_credential.user_id = staff.id', 'inner');
        $this->db->join('roles', 'roles.id = login_credential.role', 'left');
        $this->db->join('staff_designation', 'staff_designation.id = staff.designation', 'left');
        $this->db->join('staff_department', 'staff_department.id = staff.department', 'left');
        $this->db->where('login_credential.role', $role_id);
        $this->db->where('login_credential.active', 1);
        if ($designation != '') {
            $this->db->where('staff.designation', $designation);
        }

        if ($department != '') {
            $this->db->where('staff.department', $department);
        }

        $result = $this->db->get()->result_array();
        if (count($result)) {
            $html .= "<option value=''>" . translate('select') . "</option>";
            foreach ($result as $row) {
                $selected = ($row['id'] == $selected_id ? 'selected' : '');
                $html .= "<option value='" . $row['id'] . "' " . $selected . ">" . $row['name'] . " (" . $row['staff_id'] . ")</option>";
            }
        } else {
            $html .= '<option value="">' . translate('no_information_available') . '</option>';
        }
        echo $html;
    }

    public function get_salary_template_details()
    {
        if (get_permission('salary_template', 'is_view')) {
            $template_id = $this->input->post('id');
            $this->data['allowances'] = $this->ajax_model->get('salary_template_details', array('type' => 1, 'salary_template_id' => $template_id));
            $this->data['deductions'] = $this->ajax_model->get('salary_template_details', array('type' => 2, 'salary_template_id' => $template_id));
            $this->data['template'] = $this->ajax_model->get('salary_template', array('id' => $template_id), true);
            $this->load->view('payroll/qview_salary_templete', $this->data);
        }
    }

    public function department_details()
    {
        if (get_permission('department', 'is_edit')) {
            $id = $this->input->post('id');
            $this->db->where('id', $id);
            $query = $this->db->get('staff_department');
            $result = $query->row_array();
            echo json_encode($result);
        }
    }

    public function cashbook_accounts_details()
    {
        if (get_permission('cashbook_accounts', 'is_edit')) {
            $id = $this->input->post('id');
            $this->db->where('id', $id);
            $query = $this->db->get('cashbook_accounts');
            $result = $query->row_array();
            echo json_encode($result);
        }
    }

    public function designation_details()
    {
        if (get_permission('designation', 'is_edit')) {
            $id = $this->input->post('id');
            $this->db->where('id', $id);
            $query = $this->db->get('staff_designation');
            $result = $query->row_array();
            echo json_encode($result);
        }
    }

    public function responsibility_details()
    {
        if (get_permission('responsibilities', 'is_edit')) {
            $id = $this->input->post('id');
            $this->db->where('id', $id);
            $query = $this->db->get('staff_responsibilities');
            $result = $query->row_array();
            echo json_encode($result);
        }
    }


    public function role_responsibility_details()
    {
        if (get_permission('roles_responsibilities', 'is_edit')) {
            $designation_id = $this->input->post('designation_id');
            $this->db->select('id, responsibility_id');
            $this->db->from('staff_roles_responsibilities');
            $this->db->where('staff_designation', $designation_id);
            $this->db->order_by('id', 'ASC');
            $query = $this->db->get();
            $result = array();
            foreach($query->result() as $row) {
                $result[] = (int)$row->responsibility_id;
            }
            echo json_encode($result);
        }
    }
}
