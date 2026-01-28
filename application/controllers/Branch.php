<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Branch extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('branch_model');
    }

    /* branch all data are prepared and stored in the database here */
   public function index()
	{
		if (in_array(loggedin_role_id(), [1, 2, 3, 5])) {
			if ($this->input->post('submit') == 'save') {
				$this->form_validation->set_rules('branch_name', translate('branch_name'), 'required|callback_unique_name');
				//$this->form_validation->set_rules('business_name', translate('business_name'), 'required');
				$this->form_validation->set_rules('email', translate('email'), 'required|valid_email');
				$this->form_validation->set_rules('mobileno', translate('mobile_no'), 'required');

				if ($this->form_validation->run() == true) {
					$post = $this->input->post();
					if ($this->branch_model->save($post)) {
						set_alert('success', translate('information_has_been_saved_successfully'));
					}
					redirect(base_url('branch'));
				} else {
					$this->data['validation_error'] = true;
				}
			}

			$this->data['title'] = translate('business');
			$this->data['sub_page'] = 'branch/add';
			$this->data['main_menu'] = 'branch';
			$this->data['headerelements'] = [
				'css' => ['vendor/dropify/css/dropify.min.css'],
				'js'  => ['vendor/dropify/js/dropify.min.js'],
			];

			$this->load->view('layout/index', $this->data);

		} else {
			$this->session->set_userdata('last_page', current_url());
			redirect(base_url(), 'refresh');
		}
	}


    /* branch information update here */
    public function edit($id = '')
    {
        if (in_array(loggedin_role_id(), [1, 2, 3, 5])) {
            if ($this->input->post('submit') == 'save') {
              $this->form_validation->set_rules('branch_name', translate('branch_name'), 'required|callback_unique_name');
				//$this->form_validation->set_rules('business_name', translate('business_name'), 'required');
				$this->form_validation->set_rules('email', translate('email'), 'required|valid_email');
				$this->form_validation->set_rules('mobileno', translate('mobile_no'), 'required');
                if ($this->form_validation->run() == true) {
                    $post = $this->input->post();
                    $response = $this->branch_model->save($post, $id);
                    if ($response) {
                        set_alert('success', translate('information_has_been_updated_successfully'));
                    }
                    redirect(base_url('branch'));
                }
            }

            $this->data['data'] = $this->branch_model->getSingle('branch', $id, true);
            $this->data['title'] = translate('business');
            $this->data['sub_page'] = 'branch/edit';
            $this->data['main_menu'] = 'branch';
            $this->data['headerelements'] = array(
                'css' => array(
                    'vendor/dropify/css/dropify.min.css',
                ),
                'js' => array(
                    'vendor/dropify/js/dropify.min.js',
                ),
            );
            $this->load->view('layout/index', $this->data);
        } else {
            $this->session->set_userdata('last_page', current_url());
            redirect(base_url(), 'refresh');
        }
    }

    /* delete information */
    public function delete_data($id = '')
    {
       if (in_array(loggedin_role_id(), [1, 2, 3, 5])) {
            $this->db->where('id', $id);
            $this->db->delete('branch');

            //delete branch all staff
            $result = $this->db->select('id')->where('branch_id', $id)->get('staff')->result();
            foreach ($result as $key => $value) {
                $this->db->where('user_id', $value->id);
                $this->db->delete('login_credential');

                $this->db->where('id', $value->id);
                $this->db->delete('staff');
            }
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    /* unique valid branch name verification is done here */
    public function unique_name($name)
    {
        $branch_id = $this->input->post('branch_id');
        if (!empty($branch_id)) {
            $this->db->where_not_in('id', $branch_id);
        }
        $this->db->where('name', $name);
        $name = $this->db->get('branch')->num_rows();
        if ($name == 0) {
            return true;
        } else {
            $this->form_validation->set_message("unique_name", translate('already_taken'));
            return false;
        }
    }
}
