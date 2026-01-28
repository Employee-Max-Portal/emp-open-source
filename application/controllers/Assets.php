<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Assets extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('library_model');
        $this->load->model('assets_model');
    }

    public function index()
    {
        if (is_loggedin()) {
            redirect(base_url('dashboard'));
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    /* form validation rules */
	   protected function asset_validation()
	{
		if (is_superadmin_loggedin()) {
			$this->form_validation->set_rules('branch_id', translate('branch'), 'required');
		}
		$this->form_validation->set_rules('category_id', translate('category'), 'trim|required');
		$this->form_validation->set_rules('asset_name', translate('asset_name'), 'trim|required');
		$this->form_validation->set_rules('purchase_date', translate('purchase_date'), 'trim|required');
		$this->form_validation->set_rules('price', translate('price'), 'trim|required|numeric');
	}


    /* category form validation rules */
    protected function category_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }
        $this->form_validation->set_rules('name', translate('category'), 'trim|required|callback_unique_category');
    }

    // assets page
    public function lists()
    {
        if (!get_permission('assets', 'is_view')) {
            access_denied();
        }

        if ($_POST) {
            if (!get_permission('assets', 'is_add')) {
                ajax_access_denied();
            }
            $this->asset_validation();
            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();

                //save all route information in the database file
                $this->assets_model->asset_save($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('assets/lists');
                $array = array('status' => 'success', 'url' => $url);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }
        $branchID = $this->application_model->get_branch_id();
        $this->data['branch_id'] = $branchID;
        $this->data['assetlist'] = $this->app_lib->getTable('assets');
        $this->data['title'] = translate('assets');
        $this->data['sub_page'] = 'assets/index';
        $this->data['main_menu'] = 'assets';
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


    /* the information is updated here */
    public function assets_edit($id = '')
    {
        if (!get_permission('assets', 'is_edit')) {
            access_denied();
        }

        if ($_POST) {
            $this->asset_validation();
            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();
                //save all route information in the database file
                $this->assets_model->asset_save($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('assets/lists');
                $array = array('status' => 'success', 'url' => $url, 'error' => '');
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'url' => '', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }
        $this->data['asset'] = $this->app_lib->getTable('assets', array('t.id' => $id), true);
        $this->data['branch_id'] = $this->application_model->get_branch_id();
        $this->data['assetlist'] = $this->app_lib->getTable('assets');
        $this->data['title'] = translate('assets_entry');
        $this->data['sub_page'] = 'assets/edit';
        $this->data['main_menu'] = 'assets';
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

    public function assets_delete($id = '')
    {
        if (get_permission('assets', 'is_delete')) {
            $file = 'uploads/asset_photos/' . get_type_name_by_id('assets', $id, 'photo');
            if (file_exists($file)) {
                @unlink($file);
            }
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->where('id', $id);
            $this->db->delete('assets');
        }
    }

    // category information are prepared and stored in the database here
    public function category()
    {
        if (isset($_POST['save'])) {
            if (!get_permission('assets_category', 'is_add')) {
                access_denied();
            }
            $this->category_validation();
            if ($this->form_validation->run() !== false) {
                //save hostel type information in the database file
                $this->assets_model->category_save($this->input->post());
                set_alert('success', translate('information_has_been_saved_successfully'));
                redirect(base_url('assets/category'));
            }
        }
        $this->data['categorylist'] = $this->app_lib->getTable('assets_category');
        $this->data['title'] = translate('category');
        $this->data['sub_page'] = 'assets/category';
        $this->data['main_menu'] = 'assets';
        $this->load->view('layout/index', $this->data);
    }

    public function category_edit()
    {
        if ($_POST) {
            if (!get_permission('assets_category', 'is_edit')) {
                ajax_access_denied();
            }
            $this->category_validation();
            if ($this->form_validation->run() !== false) {
                //update book category information in the database file
                $this->assets_model->category_save($this->input->post());
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('assets/category');
                $array = array('status' => 'success', 'url' => $url, 'error' => '');
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'url' => '', 'error' => $error);
            }
            echo json_encode($array);
        }
    }

    public function category_delete($id)
    {
        if (get_permission('assets_category', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->where('id', $id);
            $this->db->delete('assets_category');
        }
    }
}
