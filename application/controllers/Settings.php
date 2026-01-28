<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Settings extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        redirect(base_url(), 'refresh');
    }

    /* global settings controller */
    public function universal()
    {
        if (!get_permission('global_settings', 'is_view')) {
            access_denied();
        }

        if ($_POST) {
            if (!get_permission('global_settings', 'is_edit')) {
                access_denied();
            }
            // License check removed to allow settings updates
        }

        $config = array();
        if ($this->input->post('submit') == 'setting') {
            foreach ($this->input->post() as $input => $value) {
                if ($input == 'submit') {
                    continue;
                }
                $config[$input] = $value;
            }
            
            $this->db->where('id', 1);
            $this->db->update('global_settings', $config);

            if (isset($config['translation'])) {
                $isRTL = $this->app_lib->getRTLStatus($config['translation']);
                $this->session->set_userdata(['set_lang' => $config['translation'], 'is_rtl' => $isRTL]);
            }
            
            set_alert('success', translate('the_configuration_has_been_updated'));
            redirect(current_url());
        }

        if ($this->input->post('submit') == 'theme') {
            foreach ($this->input->post() as $input => $value) {
                if ($input == 'submit') {
                    continue;
                }
                $config[$input] = $value;
            }
            $this->db->where('id', 1);
            $this->db->update('theme_settings', $config);
            set_alert('success', translate('the_configuration_has_been_updated'));
            $this->session->set_flashdata('active', 2);
            redirect(current_url());
        }

        if ($this->input->post('submit') == 'logo') {
            // Check if upload directory exists, create if not
            $upload_dir = './uploads/images/settings/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $logo_file = $this->app_lib->upload_system_logo('settings');
            if ($logo_file && $logo_file != 'defualt.png') {
                $config = array(
                    'system_logo' => $logo_file,
                );
                
                $this->db->where('id', 1);
                $this->db->update('global_settings', $config);
                
                set_alert('success', translate('the_configuration_has_been_updated'));
            } else {
                set_alert('error', translate('file_upload_failed'));
            }
            $this->session->set_flashdata('active', 3);
            redirect(current_url());
        }
		
		if ($this->input->post('submit') == 'employee_scores') {
            foreach ($this->input->post() as $input => $value) {
                if ($input == 'submit') {
                    continue;
                }
                $config[$input] = $value;
            }
            $this->db->where('id', 1);
            $this->db->update('global_settings', $config);

            set_alert('success', translate('the_configuration_has_been_updated'));
            $this->session->set_flashdata('active', 5);
            redirect(current_url());
        }
		
		if ($this->input->post('submit') == 'cost_per_hour') {
            foreach ($this->input->post() as $input => $value) {
                if ($input == 'submit') {
                    continue;
                }
                $config[$input] = $value;
            }
            $this->db->where('id', 1);
            $this->db->update('global_settings', $config);

            set_alert('success', translate('the_information_has_been_updated'));
            $this->session->set_flashdata('active', 6);
            redirect(current_url());
        }
		

        $this->data['title'] = translate('global_settings');
        $this->data['sub_page'] = 'settings/universal';
        $this->data['main_menu'] = 'settings';
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

    public function file_types_save() {
        if ($_POST) {
            if (!get_permission('global_settings', 'is_view')) {
                ajax_access_denied();
            }
            $this->form_validation->set_rules('image_extension', translate('image_extension'), 'trim|required');
            $this->form_validation->set_rules('image_size', translate('image_size'), 'trim|required|numeric');
            $this->form_validation->set_rules('file_extension', translate('file_extension'), 'trim|required');
            $this->form_validation->set_rules('file_size', translate('file_size'), 'trim|required|numeric');
            if ($this->form_validation->run() == true) {
                $arrayType = array(
                    'image_extension' => $this->input->post('image_extension'), 
                    'image_size' => $this->input->post('image_size'), 
                    'file_extension' => $this->input->post('file_extension'), 
                    'file_size' => $this->input->post('file_size'), 
                );

                $this->db->where('id', 1);
                $this->db->update('global_settings', $arrayType);
                $array = array('status' => 'success', 'message' => translate('the_configuration_has_been_updated'));
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
        }
    }

    public function unique_branchname($name)
    {
        $this->db->where_not_in('id', get_loggedin_branch_id());
        $this->db->where('name', $name);
        $name = $this->db->get('branch')->num_rows();
        if ($name == 0) {
            return true;
        } else {
            $this->form_validation->set_message("unique_branchname", translate('already_taken'));
            return false;
        }
    }

    public function branchUpdate($data)
    {
        $arrayBranch = array(
            'name' => $data['branch_name'],
            'school_name' => $data['school_name'],
            'email' => $data['email'],
            'mobileno' => $data['mobileno'],
            'currency' => $data['currency'],
            'symbol' => $data['currency_symbol'],
            'city' => $data['city'],
            'state' => $data['state'],
            'address' => $data['address'],
        );
        $this->db->where('id', get_loggedin_branch_id());
        $this->db->update('branch', $arrayBranch);
    }

}
