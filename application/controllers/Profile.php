<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Profile extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('employee_model');
        $this->load->model('profile_model');
        $this->load->model('email_model');
    }

    public function index()
    {
        $userID = get_loggedin_user_id();
        $loggedinRoleID = loggedin_role_id();
        $branchID = get_loggedin_branch_id();

            if ($_POST) {
                $this->form_validation->set_rules('name', translate('name'), 'trim|required');
                $this->form_validation->set_rules('mobile_no', translate('mobile_no'), 'trim|required');
                $this->form_validation->set_rules('present_address', translate('present_address'), 'trim|required');
                if (is_admin_loggedin()) {
                    $this->form_validation->set_rules('designation_id', translate('designation'), 'trim|required');
                    $this->form_validation->set_rules('department_id', translate('department'), 'trim|required');
                    $this->form_validation->set_rules('joining_date', translate('joining_date'), 'trim|required');
                    $this->form_validation->set_rules('qualification', translate('qualification'), 'trim|required');
                }
                $this->form_validation->set_rules('email', translate('email'), 'trim|required|valid_email');

                if ($this->form_validation->run() == true) {
                    $data = $this->input->post();
                    $this->profile_model->staffUpdate($data);
                    set_alert('success', translate('information_has_been_updated_successfully'));
                    redirect(base_url('profile'));
                }
            }
            $this->data['staff'] = $this->employee_model->getSingleStaff($userID);
            $this->data['sub_page'] = 'profile/employee';


        $this->data['title'] = translate('profile') . " " . translate('edit');
        $this->data['main_menu'] = 'profile';
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/dropify/css/dropify.min.css',
            ),
            'js' => array(
                'vendor/dropify/js/dropify.min.js',
            ),
        );
        $this->load->view('layout/index', $this->data);
    }

    // unique valid username verification is done here
    public function unique_username($username)
    {
        if (empty($username)) {
            return true;
        }
        $this->db->where_not_in('id', get_loggedin_id());
        $this->db->where('username', $username);
        $query = $this->db->get('login_credential');
        if ($query->num_rows() > 0) {
            $this->form_validation->set_message("unique_username", translate('username_has_already_been_used'));
            return false;
        } else {
            return true;
        }
    }

    // when user change his password
    public function password()
    {
        if ($_POST) {
            $this->form_validation->set_rules('current_password', 'Current Password', 'trim|required|min_length[4]|callback_check_validate_password');
            $this->form_validation->set_rules('new_password', 'New Password', 'trim|required|min_length[4]');
            $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'trim|required|min_length[4]|matches[new_password]');
            if ($this->form_validation->run() == true) {
                $new_password = $this->input->post('new_password');
                $this->db->where('id', get_loggedin_id());
                $this->db->update('login_credential', array('password' => $this->app_lib->pass_hashed($new_password)));
                // password change email alert
                $emailData = array(
                    'branch_id' => get_loggedin_branch_id(),
                    'password' => $new_password,
                );
                $this->email_model->changePassword($emailData);
                set_alert('success', translate('password_has_been_changed'));
                $array = array('status' => 'success');
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }

        $this->data['sub_page'] = 'profile/password_change';
        $this->data['main_menu'] = 'profile';
        $this->data['title'] = translate('profile');
        $this->load->view('layout/index', $this->data);
    }

    // when user change his username
    public function username_change()
    {
        if ($_POST) {
            $this->form_validation->set_rules('username', translate('username'), 'trim|required|callback_unique_username');
            if ($this->form_validation->run() == true) {
                $username = $this->input->post('username');

                // update login credential information in the database
                $this->db->where('user_id', get_loggedin_user_id());
                $this->db->where('role', loggedin_role_id());
                $this->db->update('login_credential', array('username' => $username));

                set_alert('success', translate('information_has_been_updated_successfully'));
                $array = array('status' => 'success');
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }
    }

    // current password verification is done here
    public function check_validate_password($password)
    {
        if ($password) {
            $getPassword = $this->db->select('password')
                ->where('id', get_loggedin_id())
                ->get('login_credential')->row()->password;
            $getVerify = $this->app_lib->verify_password($password, $getPassword);
            if ($getVerify) {
                return true;
            } else {
                $this->form_validation->set_message("check_validate_password", translate('current_password_is_invalid'));
                return false;
            }
        }
    }

    protected function bank_validation()
    {
        $this->form_validation->set_rules('bank_name', translate('bank_name'), 'trim|required');
        $this->form_validation->set_rules('holder_name', translate('holder_name'), 'trim|required');
        $this->form_validation->set_rules('bank_branch', translate('bank_branch'), 'trim|required');
        $this->form_validation->set_rules('account_no',  translate('account_no'), 'trim|required');
    }

    public function bank_account_create()
    {
        $this->bank_validation();
        if ($this->form_validation->run() !== false) {
            $post = $this->input->post();
            $this->profile_model->bankSave($post);
            set_alert('success', translate('information_has_been_saved_successfully'));
            echo json_encode(array('status' => 'success'));
        } else {
            $error = $this->form_validation->error_array();
            echo json_encode(array('status' => 'fail', 'error' => $error));
        }
    }

    public function bank_account_update()
    {
        $this->bank_validation();
        if ($this->form_validation->run() !== false) {
            $post = $this->input->post();
            $this->profile_model->bankSave($post);
            set_alert('success', translate('information_has_been_updated_successfully'));
            echo json_encode(array('status' => 'success'));
        } else {
            $error = $this->form_validation->error_array();
            echo json_encode(array('status' => 'fail', 'error' => $error));
        }
    }

    public function bankaccount_delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('staff_bank_account');
    }

    public function getStaffBankDetails()
    {
        $id = $this->input->post('id');
        $this->db->where('id', $id);
        $query = $this->db->get('staff_bank_account');
        $result = $query->row_array();
        echo json_encode($result);
    }
}

