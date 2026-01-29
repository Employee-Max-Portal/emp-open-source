<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Employee extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('employee_model');
        $this->load->model('email_model');
        $this->load->model('leave_model');
    }

    public function index()
    {
        redirect(base_url('dashboard'));
    }

    /* staff form validation rules */
    protected function employee_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'trim|required');
        }
        $this->form_validation->set_rules('name', translate('name'), 'trim|required');
        $this->form_validation->set_rules('mobile_no', translate('mobile_no'), 'trim|required');
        $this->form_validation->set_rules('present_address', translate('present_address'), 'trim|required');
        
        $userRole = $this->input->post('user_role');
        
        // Make these fields non-mandatory for roles 11 and 12
        if (!in_array($userRole, [11, 12])) {
            $this->form_validation->set_rules('designation_id', translate('designation'), 'trim|required');
            $this->form_validation->set_rules('department_id', translate('department'), 'trim|required');
            $this->form_validation->set_rules('qualification', translate('qualification'), 'trim|required');
        }
        
        $this->form_validation->set_rules('joining_date', translate('joining_date'), 'trim|required');
        $this->form_validation->set_rules('user_role', translate('role'), 'trim|required|callback_valid_role');
        $this->form_validation->set_rules('username', translate('username'), 'trim|required|callback_unique_username');
       if ($this->input->post('staff_id')) {
            $this->form_validation->set_rules('staff_id_no', translate('staff_id'), 'trim|required|callback_unique_staffID');
        }
        $this->form_validation->set_rules('email', translate('email'), 'trim|required|valid_email');
        if (!isset($_POST['staff_id'])) {
            $this->form_validation->set_rules('password', translate('password'), 'trim|required|min_length[4]');
            $this->form_validation->set_rules('retype_password', translate('retype_password'), 'trim|required|matches[password]');
        }
        $this->form_validation->set_rules('facebook', 'Facebook', 'valid_url');
        $this->form_validation->set_rules('twitter', 'Twitter', 'valid_url');
        $this->form_validation->set_rules('linkedin', 'Linkedin', 'valid_url');
        $this->form_validation->set_rules('user_photo', 'profile_picture', 'callback_photoHandleUpload[user_photo]');
        // custom fields validation rules
        $class_slug = $this->router->fetch_class();
        // Custom fields helper not available - skipping custom field validation
        /*
        $customFields = getCustomFields($class_slug);
        foreach ($customFields as $fields_key => $fields_value) {
            if ($fields_value['required']) {
                $fieldsID = $fields_value['id'];
                $fieldLabel = $fields_value['field_label'];
                $this->form_validation->set_rules("custom_fields[employee][" . $fieldsID . "]", $fieldLabel, 'trim|required');
            }
        }
        */
    }

    /* getting all employee list */
    public function view($role = 2)
    {
        if (!get_permission('employee', 'is_view') || ($role == 1)) {
            access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        $this->data['act_role'] = $role;
        $this->data['title'] = translate('employee');
        $this->data['sub_page'] = 'employee/view';
        $this->data['main_menu'] = 'employee';
        $this->data['stafflist'] = $this->employee_model->getStaffList($branchID, $role);
        $this->load->view('layout/index', $this->data);
    }

    /* bank form validation rules */
    protected function bank_validation()
    {
        $this->form_validation->set_rules('bank_name', translate('bank_name'), 'trim|required');
        $this->form_validation->set_rules('holder_name', translate('holder_name'), 'trim|required');
        $this->form_validation->set_rules('bank_branch', translate('bank_branch'), 'trim|required');
        $this->form_validation->set_rules('account_no',  translate('account_no'), 'trim|required');
    }

    /* employees all information are prepared and stored in the database here */
    public function add()
    {
        if (!get_permission('employee', 'is_add')) {
            access_denied();
        }
        if ($_POST) {
            $userRole = $this->input->post('user_role');

            $this->employee_validation();
            if (!isset($_POST['chkskipped'])) {
                $this->bank_validation();
            }
            if ($this->form_validation->run() !== false) {
                //save all employee information in the database
                $post = $this->input->post();
				
                $joining_date = $this->input->post('joining_date');
                $employee_type = $this->input->post('employee_type');
				

                $empID = $this->employee_model->save($post);
                
				if ($empID) {
					// Initialize leave balance
					$this->leave_model->initialize_leave_balance(
						$empID,
						$joining_date,
						$employee_type
					);
				}
	
                // handle custom fields data
                //$class_slug = $this->router->fetch_class();
                //$customField = $this->input->post("custom_fields[$class_slug]");
                //if (!empty($customField)) {
                    // Custom fields helper not available - skipping custom field save
                    // saveCustomFields($customField, $empID);
                //}
                set_alert('success', translate('information_has_been_saved_successfully'));
                
                //send account activate email
                //$this->email_model->sentStaffRegisteredAccount($post);
				
				// ✅ NEW EMAIL BLOCK FOR CONNECT ACTIVATION
            if ($this->db->affected_rows() > 0) {
                $mail_subject = 'New account created at EMP';
                $mail_body = "
                <html>
                  <body style='margin:0; padding:0; font-family:Arial, sans-serif; background-color:#f4f4f4;'>
                    <table align='center' width='100%' cellpadding='0' cellspacing='0' style='background-color:#f4f4f4; padding:30px 0;'>
                      <tr>
                        <td align='center'>
                          <table width='600' cellpadding='0' cellspacing='0' style='background:#ffffff; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1); padding:30px;'>
                            <tr>
                              <td align='center' style='padding-bottom:20px;'>
                                <h2 style='color:#333;'> <span style='color:#0054a6;'>EMP</span></h2>
                              </td>
                            </tr>
                            <tr>
                              <td style='color:#444; font-size:16px; line-height:1.6;'>
                                <p>Dear <strong>{$post['name']}</strong>,</p>
                                <p>Your <strong>EMP</strong> account has been created.</p>
                                <p>You can now log in and start using our services.</p>
								  <table cellpadding='6' cellspacing='0' style='background-color:#f9f9f9; border:1px solid #ddd; border-radius:6px; font-size:15px; width:100%; margin-top:15px;'>
								  <tr>
									<td><strong>&#128100; Name:</strong></td>
									<td>{$post['name']}</td>
								  </tr>
								  <tr>
									<td><strong>&#128222; Phone:</strong></td>
									<td>{$post['mobile_no']}</td>
								  </tr>
								  <tr>
									<td><strong>&#128273; Username:</strong></td>
									<td>{$post['username']}</td>
								  </tr>
								  <tr>
									  <td><strong>&#128737;&#65039; Password:</strong></td>
									  <td>{$post['password']}</td>
									</tr>
								</table>
                                <p style='text-align:center; margin:30px 0;'>
                                  <a href='https://emp.com.bd/' style='background-color:#0054a6; color:#ffffff; padding:12px 24px; border-radius:6px; text-decoration:none; font-size:16px; display:inline-block;'>Click Here to Login</a>
                                </p>
                                <p>If the button doesn't work, you can also copy and paste this link into your browser:</p>
                                <p><a href='https://emp.com.bd/'>https://emp.com.bd/</a></p>
                                <p style='margin-top:30px;'>Thank you,<br><strong>EMP Team</strong></p>
                                <p style='text-align:center; font-size:14px; color:#888; margin-top:40px;'>
                                  From <strong>Team EMP</strong> with <span style='color:#e63946;'>&#10084;&#65039;</span>
                                </p>
                              </td>
                            </tr>
                            <tr>
                              <td align='center' style='padding-top:30px; font-size:12px; color:#999; border-top:1px solid #eee;'>
                                <p>&copy; " . date('Y') . " EMP. All rights reserved.</p>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                  </body>
                </html>";

               $config = $this->email_model->get_email_config();

					$email_data = [
						'smtp_host'     => $config['smtp_host'],
						'smtp_auth'     => true,
						'smtp_user'     => $config['smtp_user'],
						'smtp_pass'     => $config['smtp_pass'], // App Password
						'smtp_secure'   => $config['smtp_encryption'],
						'smtp_port'     => $config['smtp_port'],
						'from_email'    => $config['email'],
						'from_name'     => 'EMP Team',
						'to_email'      => $to_email,
						'to_name'       => $to_name,
						'subject'       => $mail_subject,
						'body'          => $mail_body
					];
					
					
					// Send the email
					$this->email_model->send_email_yandex($email_data);
                set_alert('success', translate('user_authenticated_successfully'));
            } else {
                set_alert('error', translate('authentication_failed'));
            }
			
                redirect(base_url('employee/view/' . $post['user_role']));
            }
        }
        $this->data['branch_id'] = $this->application_model->get_branch_id();
        $this->data['title'] = translate('add_employee');
        $this->data['sub_page'] = 'employee/add';
        $this->data['main_menu'] = 'employee';
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/dropify/css/dropify.min.css',
            ),
            'js' => array(
                'js/employee.js',
                'vendor/dropify/js/dropify.min.js',
            ),
        );
        $this->load->view('layout/index', $this->data);
    }

    /* profile preview and information are controlled here */
    public function profile($id = '')
    {
        if (!get_permission('employee', 'is_edit')) {
            access_denied();
        }
        if ($this->input->post('submit') == 'update') {
            $this->employee_validation();
            if ($this->form_validation->run() == true) {
                //save all employee information in the database
                $this->employee_model->save($this->input->post());
				
				$joining_date = $this->input->post('joining_date');
                $employee_type = $this->input->post('employee_type');
                $staff_id = $this->input->post('staff_id');
			
				if ($staff_id) {

					// Initialize leave balance
					$this->leave_model->update_leave_balance(
						$staff_id,
						$joining_date,
						$employee_type
					);
				}
                /* // handle custom fields data
                $class_slug = $this->router->fetch_class();
                $customField = $this->input->post("custom_fields[$class_slug]");
                if (!empty($customField)) {
                    saveCustomFields($customField, $id);
                } */
                set_alert('success', translate('information_has_been_updated_successfully'));
                $this->session->set_flashdata('profile_tab', 1);
                redirect(base_url('employee/profile/' . $id));
            } else {
                $this->session->set_flashdata('profile_tab', 1);
            }
        }
        $this->data['categorylist'] = $this->app_lib->get_document_category();
        $this->data['staff'] = $this->employee_model->getSingleStaff($id);
        $this->data['title'] = translate('employee_profile');
        $this->data['sub_page'] = 'employee/profile';
        $this->data['main_menu'] = 'employee';
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/dropify/css/dropify.min.css',
            ),
            'js' => array(
                'js/employee.js',
                'vendor/dropify/js/dropify.min.js',
            ),
        );
        $this->load->view('layout/index', $this->data);
    }

    // user interface and employees all information are prepared and stored in the database here
    public function delete($id = '')
    {
        if (!get_permission('employee', 'is_delete')) {
            access_denied();
        }
        // check student restrictions
        if (!is_superadmin_loggedin()) {
            $this->db->where('branch_id', get_loggedin_branch_id());
        }
        $this->db->delete('staff', array('id' => $id));
        $this->db->delete('leave_balance', array('user_id' => $id));
        if ($this->db->affected_rows() > 0) {
            $this->db->where('user_id', $id);
            $this->db->where_not_in('role', array(1, 6, 7));
            $this->db->delete('login_credential');
        }
    }

    // unique valid username verification is done here
    public function unique_username($username)
    {
        if ($this->input->post('staff_id')) {
            $staff_id = $this->input->post('staff_id');
            $login_id = $this->app_lib->get_credential_id($staff_id);
            $this->db->where_not_in('id', $login_id);
        }
        $this->db->where('username', $username);
        $query = $this->db->get('login_credential');

        if ($query->num_rows() > 0) {
            $this->form_validation->set_message("unique_username", translate('username_has_already_been_used'));
            return false;
        } else {
            return true;
        }
    }

    // unique valid staff id verification is done here
    public function unique_staffID($id)
    {
        $branchID = $this->application_model->get_branch_id();
        if ($this->input->post('staff_id')) {
            $staff_id = $this->input->post('staff_id');
            $this->db->where_not_in('id', $staff_id);
        }
        $this->db->where('branch_id', $branchID);
        $this->db->where('staff_id', $id);
        $query = $this->db->get('staff');

        if ($query->num_rows() > 0) {
            $this->form_validation->set_message("unique_staffID", translate('already_taken'));
            return false;
        } else {
            return true;
        }
    }


    public function valid_role($id)
    {
        $restrictions = array(1, 6, 7);
        if (in_array($id, $restrictions)) {
            $this->form_validation->set_message("valid_role", translate('selected_role_restrictions'));
            return false;
        } else {
            return true;
        }
    }

    // employee login password change here by admin
    public function change_password()
    {
        if (!get_permission('employee', 'is_edit')) {
            ajax_access_denied();
        }
        
        $studentID = $this->input->post('staff_id');
        $password = $this->input->post('password');
        $authentication = $this->input->post('authentication');
        
        // Get current user status
        $this->db->where('user_id', $studentID);
        $this->db->where_not_in('role', array(1, 6, 7));
        $current_user = $this->db->get('login_credential')->row();
        
        if (!$current_user) {
            $array = array('status' => 'fail', 'error' => array('general' => 'User not found'));
            echo json_encode($array);
            return;
        }
        
        if (isset($authentication)) {
            // Authentication checkbox is checked
            if ($current_user->active == 0) {
                // User is deactivated, checkbox means activate
                $this->form_validation->set_rules('password', translate('password'), 'trim|required|min_length[4]');
                if ($this->form_validation->run() !== false) {
                    $this->db->where_not_in('role', array(1, 6, 7));
                    $this->db->where('user_id', $studentID);
                    $this->db->update('login_credential', array(
                        'active' => 1,
                        'password' => $this->app_lib->pass_hashed($password)
                    ));
                    set_alert('success', translate('user_authenticated_successfully'));
                    $array = array('status' => 'success');
                } else {
                    $error = $this->form_validation->error_array();
                    $array = array('status' => 'fail', 'error' => $error);
                }
            } else {
                // User is active, checkbox means deactivate
                $this->db->where_not_in('role', array(1, 6, 7));
                $this->db->where('user_id', $studentID);
                $this->db->update('login_credential', array('active' => 0));
                set_alert('success', translate('authentication_deactivated_successfully'));
                $array = array('status' => 'success');
            }
        } else {
            // Just password change
            if ($current_user->active == 1) {
                $this->form_validation->set_rules('password', translate('password'), 'trim|required|min_length[4]');
                if ($this->form_validation->run() !== false) {
                    $this->db->where_not_in('role', array(1, 6, 7));
                    $this->db->where('user_id', $studentID);
                    $this->db->update('login_credential', array('password' => $this->app_lib->pass_hashed($password)));
                    set_alert('success', translate('information_has_been_updated_successfully'));
                    $array = array('status' => 'success');
                } else {
                    $error = $this->form_validation->error_array();
                    $array = array('status' => 'fail', 'error' => $error);
                }
            } else {
                $array = array('status' => 'fail', 'error' => array('general' => 'Cannot change password for deactivated user'));
            }
        }
        
        echo json_encode($array);
    }

    // employee bank details are create here / ajax
    public function bank_account_create()
    {
        if (!get_permission('employee', 'is_edit')) {
            ajax_access_denied();
        }
        $this->bank_validation();
        if ($this->form_validation->run() !== false) {
            $post = $this->input->post();
            $this->employee_model->bankSave($post);
            set_alert('success', translate('information_has_been_saved_successfully'));
            $this->session->set_flashdata('bank_tab', 1);
            echo json_encode(array('status' => 'success'));
        } else {
            $error = $this->form_validation->error_array();
            echo json_encode(array('status' => 'fail', 'error' => $error));
        }
        
    }

    // employee bank details are update here / ajax
    public function bank_account_update()
    {
        if (!get_permission('employee', 'is_edit')) {
            ajax_access_denied();
        }
        $this->bank_validation();
        if ($this->form_validation->run() !== false) {
            $post = $this->input->post();
            $this->employee_model->bankSave($post);
            $this->session->set_flashdata('bank_tab', 1);
            set_alert('success', translate('information_has_been_updated_successfully'));
            echo json_encode(array('status' => 'success'));
        } else {
            $error = $this->form_validation->error_array();
            echo json_encode(array('status' => 'fail', 'error' => $error));
        }
    }

    // employee bank details are delete here
    public function bankaccount_delete($id)
    {
        if (get_permission('employee', 'is_edit')) {
            $this->db->where('id', $id);
            $this->db->delete('staff_bank_account');
            $this->session->set_flashdata('bank_tab', 1);
        }
    }

    public function bank_details()
    {
        $id = $this->input->post('id');
        $this->db->where('id', $id);
        $query = $this->db->get('staff_bank_account');
        $result = $query->row_array();
        echo json_encode($result);
    }

    protected function document_validation()
    {
        $this->form_validation->set_rules('document_title', translate('document_title'), 'trim|required');
        $this->form_validation->set_rules('document_category', translate('document_category'), 'trim|required');
        if ($this->uri->segment(2) != 'document_update') {
            if (isset($_FILES['document_file']['name']) && empty($_FILES['document_file']['name'])) {
                $this->form_validation->set_rules('document_file', translate('document_file'), 'required');
            }
        }
    }

    // employee document details are create here / ajax
    public function document_create()
    {
        if (!get_permission('employee', 'is_edit')) {
            ajax_access_denied();
        }
        $this->document_validation();
        if ($this->form_validation->run() !== false) {
            $insert_doc = array(
                'staff_id' => $this->input->post('staff_id'),
                'title' => $this->input->post('document_title'),
                'category_id' => $this->input->post('document_category'),
                'remarks' => $this->input->post('remarks'),
            );
            // uploading file using codeigniter upload library
            $config['upload_path'] = './uploads/attachments/documents/';
            $config['allowed_types'] = 'gif|jpg|png|pdf|docx|csv|txt';
            $config['max_size'] = '2048';
            $config['encrypt_name'] = true;
            $this->upload->initialize($config);
            if ($this->upload->do_upload("document_file")) {
                $insert_doc['file_name'] = $this->upload->data('orig_name');
                $insert_doc['enc_name'] = $this->upload->data('file_name');
                $this->db->insert('staff_documents', $insert_doc);
                set_alert('success', translate('information_has_been_saved_successfully'));
            } else {
                set_alert('error', strip_tags($this->upload->display_errors()));
            }
            $this->session->set_flashdata('documents_details', 1);
            echo json_encode(array('status' => 'success'));
        } else {
            $error = $this->form_validation->error_array();
            echo json_encode(array('status' => 'fail', 'error' => $error));
        }
    }

    // employee document details are update here / ajax
    public function document_update()
    {
        if (!get_permission('employee', 'is_edit')) {
            ajax_access_denied();
        }
        // validate inputs
        $this->document_validation();
        if ($this->form_validation->run() !== false) {
            $document_id = $this->input->post('document_id');
            $insert_doc = array(
                'title' => $this->input->post('document_title'),
                'category_id' => $this->input->post('document_category'),
                'remarks' => $this->input->post('remarks'),
            );
            if (isset($_FILES["document_file"]) && !empty($_FILES['document_file']['name'])) {
                $config['upload_path'] = './uploads/attachments/documents/';
                $config['allowed_types'] = 'gif|jpg|png|pdf|docx|csv|txt';
                $config['max_size'] = '2048';
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if ($this->upload->do_upload("document_file")) {
                    $exist_file_name = $this->input->post('exist_file_name');
                    $exist_file_path = FCPATH . 'uploads/attachments/documents/' . $exist_file_name;
                    if (file_exists($exist_file_path)) {
                        unlink($exist_file_path);
                    }
                    $insert_doc['file_name'] = $this->upload->data('orig_name');
                    $insert_doc['enc_name'] = $this->upload->data('file_name');
                } else {
                    set_alert('error', strip_tags($this->upload->display_errors()));
                }
            }
            set_alert('success', translate('information_has_been_updated_successfully'));
            $this->db->where('id', $document_id);
            $this->db->update('staff_documents', $insert_doc);
            echo json_encode(array('status' => 'success'));
            $this->session->set_flashdata('documents_details', 1);
        } else {
            $error = $this->form_validation->error_array();
            echo json_encode(array('status' => 'fail', 'error' => $error));
        }
        
    }

    // employee document details are delete here
    public function document_delete($id)
    {
        if (get_permission('employee', 'is_edit')) {
            $enc_name = $this->db->select('enc_name')->where('id', $id)->get('staff_documents')->row()->enc_name;
            $file_name = FCPATH . 'uploads/attachments/documents/' . $enc_name;
            if (file_exists($file_name)) {
                unlink($file_name);
            }
            $this->db->where('id', $id);
            $this->db->delete('staff_documents');
            $this->session->set_flashdata('documents_details', 1);
        }
    }

    public function document_details()
    {
        $id = $this->input->post('id');
        $this->db->where('id', $id);
        $query = $this->db->get('staff_documents');
        $result = $query->row_array();
        echo json_encode($result);
    }

    /* file downloader */
    public function documents_download()
    {
        $encrypt_name = urldecode($this->input->get('file'));
        if(preg_match('/^[^.][-a-z0-9_.]+[a-z]$/i', $encrypt_name)) {
            $file_name = $this->db->select('file_name')->where('enc_name', $encrypt_name)->get('staff_documents')->row()->file_name;
            if (!empty($file_name)) {
                $this->load->helper('download');
                force_download($file_name, file_get_contents('uploads/attachments/documents/' . $encrypt_name));
            }
        }
    }

    /* department form validation rules */
    protected function department_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }
        $this->form_validation->set_rules('department_name', translate('department_name'), 'trim|required|callback_unique_department');
    }

    // employee department user interface and information are controlled here
    public function department()
    {
        if ($_POST) {
            if (!get_permission('department', 'is_add')) {
                access_denied();
            }
            $this->department_validation();
            if ($this->form_validation->run() !== false) {
                $arrayDepartment = array(
                    'name' => $this->input->post('department_name'), 
                    'branch_id' => $this->application_model->get_branch_id(), 
                );
                $this->db->insert('staff_department', $arrayDepartment);
                set_alert('success', translate('information_has_been_saved_successfully'));
                redirect(base_url('employee/department'));
            }
        }
        $this->data['department'] = $this->app_lib->getTable('staff_department');
        $this->data['title'] = translate('employee');
        $this->data['sub_page'] = 'employee/department';
        $this->data['main_menu'] = 'employee';
        $this->load->view('layout/index', $this->data);
    }

    public function department_edit()
    {
        if (!get_permission('department', 'is_edit')) {
            ajax_access_denied();
        }
        $this->department_validation();
        if ($this->form_validation->run() !== false) {
            $arrayDepartment = array(
                'name' => $this->input->post('department_name'), 
                'branch_id' => $this->application_model->get_branch_id(), 
            );
            $department_id = $this->input->post('department_id');
            $this->db->where('id', $department_id);
            $this->db->update('staff_department', $arrayDepartment);
            set_alert('success', translate('information_has_been_updated_successfully'));
            $array  = array('status' => 'success');
        } else {
            $error = $this->form_validation->error_array();
            $array = array('status' => 'fail','error' => $error);
        }
        echo json_encode($array);
    }

    public function department_delete($id)
    {
        if (!get_permission('department', 'is_delete')) {
            access_denied();
        }
        if (!is_superadmin_loggedin()) {
            $this->db->where('branch_id', get_loggedin_branch_id());
        }
        $this->db->where('id', $id);
        $this->db->delete('staff_department');
    }

    // unique valid department name verification is done here
    public function unique_department($name)
    {
        $department_id = $this->input->post('department_id');
        $branchID = $this->application_model->get_branch_id();
        if (!empty($department_id)) {
            $this->db->where_not_in('id', $department_id);
        }

        $this->db->where('branch_id', $branchID);
        $this->db->where('name', $name);
        $q = $this->db->get('staff_department');
        if ($q->num_rows() > 0) {
            $this->form_validation->set_message("unique_department", translate('already_taken'));
            return false;
        } else {
            return true;
        }
    }

    /* designation form validation rules */
    protected function designation_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }
        $this->form_validation->set_rules('designation_name', translate('designation_name'), 'trim|required|callback_unique_designation');
    }

    // employee designation user interface and information are controlled here
    public function designation()
    {
        if ($_POST) {
            if (!get_permission('designation', 'is_add')) {
                access_denied();
            }
            $this->designation_validation();
            if ($this->form_validation->run() !== false) {
                $arrayData = array(
                    'name' => $this->input->post('designation_name'), 
                    'grade' => $this->input->post('grade'), 
                    'branch_id' => $this->application_model->get_branch_id(), 
                );
                $this->db->insert('staff_designation', $arrayData);
                set_alert('success', translate('information_has_been_saved_successfully'));
                redirect(base_url('employee/designation'));
            }
        }
        $this->data['designation'] = $this->app_lib->getTable('staff_designation');
        $this->data['title'] = translate('employee');
        $this->data['sub_page'] = 'employee/designation';
        $this->data['main_menu'] = 'employee';
        $this->load->view('layout/index', $this->data);
    }

    public function designation_edit()
    {
        if (!get_permission('designation', 'is_edit')) {
            ajax_access_denied();
        }
        $this->designation_validation();
        if ($this->form_validation->run() !== false) {
            $designation_id = $this->input->post('designation_id');
            $arrayData = array(
                'name' => $this->input->post('designation_name'), 
				'grade' => $this->input->post('grade'), 
                'branch_id' => $this->application_model->get_branch_id(), 
            );
            $this->db->where('id', $designation_id);
            $this->db->update('staff_designation', $arrayData);
            set_alert('success', translate('information_has_been_updated_successfully'));
            $array  = array('status' => 'success');
        } else {
            $error = $this->form_validation->error_array();
            $array = array('status' => 'fail','error' => $error);
        }
        echo json_encode($array);
    }

    public function designation_delete($id)
    {
        if (!get_permission('designation', 'is_delete')) {
            access_denied();
        }
        $this->db->where('id', $id);
        $this->db->delete('staff_designation');
    }

    // unique valid designation name verification is done here
    public function unique_designation($name)
    {
        $designation_id = $this->input->post('designation_id');
        $branchID = $this->application_model->get_branch_id();
        if (!empty($designation_id)) {
            $this->db->where_not_in('id', $designation_id);
        }
        $this->db->where('name', $name);
        $this->db->where('branch_id', $branchID);
        $q = $this->db->get('staff_designation');
        if ($q->num_rows() > 0) {
            $this->form_validation->set_message("unique_designation", translate('already_taken'));
            return false;
        } else {
            return true;
        }
    }
	
    // showing disable authentication student list
    public function disable_authentication()
    {
        // check access permission
        if (!get_permission('employee_disable_authentication', 'is_view')) {
            access_denied();
        }

        if (isset($_POST['search'])) {
            //$branchID = $this->application_model->get_branch_id();
            $role = $this->input->post('staff_role');
            $branchID = $this->input->post('branch_id');
			
            $this->data['stafflist'] = $this->employee_model->getStaffList($branchID, $role, 0);
        }

        if (isset($_POST['auth'])) {
            if (!get_permission('employee_disable_authentication', 'is_add')) {
                access_denied();
            }
            $stafflist = $this->input->post('views_bulk_operations');
            if (isset($stafflist)) {
                foreach ($stafflist as $id) {
                    $this->db->where('user_id', $id);
                    $this->db->where_not_in('role', array(1, 6, 7));
                    $this->db->update('login_credential', array('active' => 1));
                }
                set_alert('success', translate('information_has_been_updated_successfully'));
            } else {
                set_alert('error', 'Please select at least one item');
            }
            redirect(base_url('employee/disable_authentication'));
        }
        $this->data['title'] = translate('deactivate_account');
        $this->data['sub_page'] = 'employee/disable_authentication';
        $this->data['main_menu'] = 'employee';
        $this->load->view('layout/index', $this->data);
    }

    /* employee csv importer */
    public function csv_import()
    {
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'trim|required');
        }
        $this->form_validation->set_rules('user_role', translate('role'), 'trim|required');
        $this->form_validation->set_rules('designation_id', translate('designation'), 'trim|required');
        $this->form_validation->set_rules('department_id', translate('department'), 'trim|required');
        if (isset($_FILES['userfile']['name']) && empty($_FILES['userfile']['name'])) {
            $this->form_validation->set_rules('userfile', "Select CSV File", 'required');
        }
        if ($this->form_validation->run() !== false) {
            $branchID = $this->application_model->get_branch_id();
            $userRole = $this->input->post('user_role');
            $designationID = $this->input->post('designation_id');
            $departmentID = $this->input->post('department_id');
            $err_msg = "";
            $i = 0;
            $this->load->library('csvimport');
            $csv_array = $this->csvimport->get_array($_FILES["userfile"]["tmp_name"]);
            if ($csv_array) {
                $columnHeaders = array('Name','Gender','Religion','BloodGroup','DateOfBirth','JoiningDate','Qualification','MobileNo','PresentAddress','PermanentAddress','Email','Password');
                $csvData = array();
                foreach ($csv_array as $row) {
                    if ($i == 0) {
                        $csvData = array_keys($row);
                    }
                    $checkCSV = array_diff($columnHeaders, $csvData);
                    if (count($checkCSV) <= 0) {
                        if (filter_var($row['Email'], FILTER_VALIDATE_EMAIL)) {
                            // verify existing username
                            $this->db->where('username', $row['Email']);
                            $query = $this->db->get_where('login_credential');
                            if ($query->num_rows() > 0) {
                                $err_msg .= $row['Name'] . " - Imported Failed : Email Already Exists.<br>";
                            } else {
                                // save all employee information in the database
                                $this->employee_model->csvImport($row, $branchID, $userRole, $designationID, $departmentID);
                                $i++;
                            }
                        } else {
                            $err_msg .= $row['Name'] . " - Imported Failed : Invalid Email.<br>";
                        }
                    } else {
                        set_alert('error', translate('invalid_csv_file'));
                    }
                }
                if ($err_msg != null) {
                    $msgRes = $i . ' Students Have Been Successfully Added. <br>';
                    $msgRes .= $err_msg;
                    echo json_encode(array('status' => 'errlist', 'errMsg' => $msgRes));
                    exit();
                }
                if ($i > 0) {
                    set_alert('success', $i . ' Students Have Been Successfully Added');
                }
            } else {
                set_alert('error', translate('invalid_csv_file'));
            }
            echo json_encode(array('status' => 'success'));
        } else {
            $error = $this->form_validation->error_array();
            echo json_encode(array('status' => 'fail', 'error' => $error));
        }
    }

    /* sample csv downloader */
    public function csv_Sampledownloader()
    {
        $this->load->helper('download');
        $data = file_get_contents('uploads/multi_employee_sample.csv');
        force_download("multi_employee_sample.csv", $data);
    }
	
	 /* getting all employee list */
    public function staff_break()
    {
        if (!get_permission('breaks', 'is_view') || ($role == 1 || $role == 2 || $role == 5)) {
            access_denied();
        }
		$userID = get_loggedin_user_id();

        if ($_POST) {
			$data = $this->input->post();
			$pause_id = $this->employee_model->save_pause($data);
			set_alert('success', translate('New Break added successfully'));
			redirect(base_url('employee/staff_break')); 
        }
        $this->data['pause_list'] = $this->employee_model->get_pause_list($userID);
        $this->data['title'] = translate('breaks');
        $this->data['sub_page'] = 'employee/breaks';
        $this->data['main_menu'] = 'settings';
        $this->load->view('layout/index', $this->data);
    }
	
		 /* getting all employee list */
    public function staff_break_history()
    {
        if (!get_permission('breaks', 'is_view') || ($role == 1 || $role == 2 || $role == 5)) {
            access_denied();
        }
		$userID = get_loggedin_user_id();

        $this->data['break_history'] = $this->employee_model->get_break_history();
        $this->data['title'] = translate('breaks history');
        $this->data['sub_page'] = 'employee/break_history';
        $this->data['main_menu'] = 'settings';
        $this->load->view('layout/index', $this->data);
    }

	public function break_edit()
    {
		$id = $this->input->post('id');
		$this->db->where('id', $id);
		$query = $this->db->get('pauses');
		$result = $query->row_array();
		echo json_encode($result);
    }
	
	public function break_delete($id = '')
    {
        if (!get_permission('breaks', 'is_delete')) {
            access_denied();
        }
        $this->db->where(array('id' => $id))->delete("pauses");
    }
	
	
	public function get_break_details()
{
	$user_id = get_loggedin_user_id();

	if ($this->input->post('submit')) {
		$data = $this->input->post();
		$break_type = $data['submit'];

		if ($break_type == 'activate_break') {
			$break_id = $data['break_id'];
			$break_control = $data['break_control'];

			$history_id = $this->employee_model->add_pause_history($break_id, $user_id);
			$this->employee_model->update_break_detail($user_id, $history_id, $break_id, 1);

			set_alert('success', translate('Break Activated Successfully'));
			redirect($_SERVER['HTTP_REFERER']);
		}
		elseif ($break_type == 'inactivate_break') {
			$break_id = $data['break_id'];
			$history_id = $data['history_id'];
			$remarks = $data['remarks'];
			$break_starttime = $data['break_starttime'] ?? null;

			// Check time difference
			if (!empty($break_starttime)) {
				$start = new DateTime($break_starttime);
				$now = new DateTime();
				$interval = $start->diff($now);
				$minutes = ($interval->h * 60) + $interval->i;

				if ($minutes > 30) {
					if (empty(trim($data['remarks'] ?? ''))) {
						set_alert('error', "Remarks are required when break exceeds 30 minutes.");
						redirect($this->uri->uri_string());
					}
				}
			}

			$this->employee_model->update_pause_history($history_id, $user_id, $remarks);
			$this->employee_model->update_break_detail($user_id, 0, 0, 0);

			set_alert('success', translate('Break Inactivated Successfully'));
			redirect($_SERVER['HTTP_REFERER']);
		}
		else {
			set_alert('info', translate('No Information updated'));
			redirect($_SERVER['HTTP_REFERER']);
		}
	}

	// If no submit, load view
	if ($this->input->post()) {
		$data = $this->input->post();

		$user_id = $data['id'] ?? $user_id;
		$break_control = get_break_condition();

		$this->data['use_id'] = $user_id;
		$this->data['break_control'] = $break_control;
		$this->data['pause_list'] = $this->employee_model->get_pause_list($user_id);

		$this->load->view('employee/break_control', $this->data);
	}
}

    public function employee_award()
    {
        if (!get_permission('employee_award', 'is_view')) {
            access_denied();
        }

		$month = $this->input->get('month') ?: date('Y-m');
		$this->data['month'] = $month;
		
		// Check if current user has role 4
		$UserID = get_loggedin_user_id();
		$user_role = loggedin_role_id();
		
		if ($user_role && $user_role == 4) {
			// Role 4 users can only see their own data
			$this->data['scores'] = $this->employee_model->get_user_score($month, $UserID);
			$this->data['is_role_4'] = true;
		} else {
			// Other roles can see all scores
			$this->data['scores'] = $this->employee_model->get_all_scores($month);
			$this->data['is_role_4'] = false;
		}
		// Get logged user's department for discipline button restrictions
		$this->data['logged_user_dept'] = $this->employee_model->get_logged_user_department();

        $this->data['title'] = translate('employee_award');
        $this->data['sub_page'] = 'employee/employee_award';
        $this->data['main_menu'] = 'employee_award';

        $this->load->view('layout/index', $this->data);
    }

    // Manual Trigger (Admin/COO)
    public function generate_employee_award()
    {
        if (!get_permission('employee_award', 'is_add')) {
            access_denied();
        }
		if ($this->input->post()) {
			$month = $this->input->post('month_year');
			$this->employee_model->generate_scores($month);
		}
        set_alert('success', translate('scores_generated_successfully'));
		redirect($_SERVER['HTTP_REFERER']); // Or your preferred redirect
    }

	public function update_adjustment()
	{
		if (!get_permission('employee_award', 'is_edit')) {
			access_denied();
		}

		$logged_user_id = get_loggedin_user_id();
		$score_id = $this->input->post('score_id');
		$adjustment_value = floatval($this->input->post('adjustment_value'));
		$adjustment_remarks = $this->input->post('adjustment_remarks');

		// Get the score row
		$score = $this->db->get_where('employee_scores', ['id' => $score_id])->row_array();
		if (!$score) {
			set_alert('error', 'Invalid score entry.');
			redirect(base_url('employee/employee_award'));
		}

		$employee = $this->db->get_where('staff', ['id' => $score['staff_id']])->row_array();
		if (!$employee) {
			set_alert('error', 'Invalid employee.');
			redirect(base_url('employee/employee_award'));
		}

		// Get current user's role and department
		$logged_user = $this->db->get_where('staff', ['id' => $logged_user_id])->row_array();
		$logged_user_role = $this->db->get_where('login_credential', ['user_id' => $logged_user_id])->row_array();
		if (!$logged_user) {
			set_alert('error', 'Invalid user.');
			redirect(base_url('employee/employee_award'));
		}

		// Only allow department manager (role_id == 8) to update employees from their own department
		if ($logged_user_role['role'] == 8 && $logged_user['department'] != $employee['department']) {
			set_alert('error', 'You are not allowed to adjust scores for other departments.');
			redirect(base_url('employee/employee_award'));
		}

		// Update score with adjustment
		$final_score = $score['final_score'] - $score['adjustment_value'] + $adjustment_value;

		$this->db->where('id', $score_id);
		$this->db->update('employee_scores', [
			'adjustment_value' => $adjustment_value,
			'adjustment_remarks' => $adjustment_remarks,
			'final_score' => $final_score,
			'adjusted_by' => $logged_user_id,
		]);

		set_alert('success', 'Adjustment saved and score updated.');
		redirect(base_url('employee/employee_award?month=' . $score['month']));
	}

	public function get_next_employee_id()
	{
		$branch_id = $this->input->post('branch_id');
		
		$base_id = 20000;
		if ($branch_id == 2) {
			$base_id = 10000;
		} elseif ($branch_id == 10) {
			$base_id = 30000;
		}
		
		$this->db->select('staff_id');
		$this->db->where('staff_id >=', $base_id);
		$this->db->where('staff_id <', $base_id + 10000);
		$this->db->order_by('staff_id', 'DESC');
		$this->db->limit(1);
		$query = $this->db->get('staff');
		
		if ($query->num_rows() > 0) {
			$last_id = $query->row()->staff_id;
			$next_id = $last_id + 1;
		} else {
			$next_id = $base_id + 1;
		}
		
		echo $next_id;
	}
	
	// Roles & Responsibilities management
    public function roles_responsibilities()
    {
        if ($_POST) {
            if (!get_permission('roles_responsibilities', 'is_add')) {
                access_denied();
            }
            $this->form_validation->set_rules('designation_id', translate('designation'), 'trim|required');
            $this->form_validation->set_rules('responsibility_ids[]', translate('responsibilities'), 'required');
            
            if ($this->form_validation->run() !== false) {
                $designation_id = $this->input->post('designation_id');
                $responsibility_ids = $this->input->post('responsibility_ids');
                
                // Delete existing assignments
                $this->db->where('staff_designation', $designation_id);
                $this->db->delete('staff_roles_responsibilities');
                
                // Insert new assignments
                foreach($responsibility_ids as $resp_id) {
                    $this->db->insert('staff_roles_responsibilities', array(
                        'staff_designation' => $designation_id,
                        'responsibility_id' => $resp_id
                    ));
                }
                
                set_alert('success', translate('information_has_been_saved_successfully'));
                redirect(base_url('employee/roles_responsibilities'));
            }
        }
        
        $this->data['designations'] = $this->db->get('staff_designation')->result_array();
        $this->data['responsibilities'] = $this->employee_model->get_responsibilities();
		$this->data['role_responsibilities'] = $this->employee_model->get_role_responsibilities();
        $this->data['all_responsibilities'] = $this->employee_model->get_responsibilities();
        $this->data['title'] = translate('roles_responsibilities');
        $this->data['sub_page'] = 'employee/roles_responsibilities';
        $this->data['main_menu'] = 'employee';
        $this->load->view('layout/index', $this->data);
    }
    
    public function role_responsibility_edit()
    {
        if (!get_permission('roles_responsibilities', 'is_edit')) {
            ajax_access_denied();
        }
        
        $this->form_validation->set_rules('designation_id', translate('designation'), 'trim|required');
        $this->form_validation->set_rules('responsibility_ids[]', translate('responsibilities'), 'required');
        
        if ($this->form_validation->run() !== false) {
            $designation_id = $this->input->post('designation_id');
            $responsibility_ids = $this->input->post('responsibility_ids');
            
            // Delete existing assignments
            $this->db->where('staff_designation', $designation_id);
            $this->db->delete('staff_roles_responsibilities');
            
            // Insert new assignments
            foreach($responsibility_ids as $resp_id) {
                $this->db->insert('staff_roles_responsibilities', array(
                    'staff_designation' => $designation_id,
                    'responsibility_id' => $resp_id
                ));
            }
            
            set_alert('success', translate('information_has_been_updated_successfully'));
            $array = array('status' => 'success');
        } else {
            $error = $this->form_validation->error_array();
            $array = array('status' => 'fail', 'error' => $error);
        }
        echo json_encode($array);
    }
        public function role_responsibility_details()
    {
        $designation_id = $this->input->post('designation_id');
        $this->db->select('responsibility_id, priority');
        $this->db->where('staff_designation', $designation_id);
        $this->db->order_by('priority', 'ASC');
        $result = $this->db->get('staff_roles_responsibilities')->result_array();
        echo json_encode($result);
    }
    public function role_responsibility_delete($designation_id)
    {
        if (!get_permission('roles_responsibilities', 'is_delete')) {
            access_denied();
        }
        $this->db->where('staff_designation', $designation_id);
        $this->db->delete('staff_roles_responsibilities');
        redirect(base_url('employee/roles_responsibilities'));
    }
    
}
