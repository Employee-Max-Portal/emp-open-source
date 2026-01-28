<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Team_meetings extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('meeting_minutes_model');
    }

    public function index()
    {
        if (!get_permission('team_meetings', 'is_view')) {
            access_denied();
        }
        $userRole = loggedin_role_id();

		$start_date = '';
		$end_date = '';

		if ($this->input->post('search')) {
			$daterange = explode(' - ', $this->input->post('daterange'));
			$start_date = date("Y-m-d", strtotime($daterange[0]));
			$end_date = date("Y-m-d", strtotime($daterange[1]));
		}

		$this->data['meetings'] = $this->meeting_minutes_model->get_meetings($userRole,$start_date, $end_date);
		$this->data['start_date'] = $start_date;
		$this->data['end_date'] = $end_date;

        $this->data['title'] = translate('team_meetings');
        $this->data['sub_page'] = 'team_meetings/index';
        $this->data['main_menu'] = 'team_meetings';
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/summernote/summernote.css',
				'vendor/dropify/css/dropify.min.css',
				'vendor/daterangepicker/daterangepicker.css',
            ),
            'js' => array(
                'vendor/summernote/summernote.min.js',
				'vendor/dropify/js/dropify.min.js',
			'vendor/moment/moment.js',
				'vendor/daterangepicker/daterangepicker.js',
            ),
        );
        $this->load->view('layout/index', $this->data);
    }

	function save(){

        // Handle form submission for adding new meeting
        if ($_POST) {
            if (!get_permission('team_meetings', 'is_add')) {
                access_denied();
            }

            $this->form_validation->set_rules('title', translate('title'), 'trim|required');
            $this->form_validation->set_rules('date', translate('date'), 'trim|required');
            $this->form_validation->set_rules('meeting_type', translate('meeting_type'), 'trim|required');
            $this->form_validation->set_rules('summary', translate('summary'), 'trim|required');

            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();
                $post['created_by'] = get_loggedin_user_id();

                // Handle file upload
                if (!empty($_FILES['attachment']['name'])) {
                    $config['upload_path'] = './uploads/attachments/meetings/';
                    $config['allowed_types'] = 'pdf|doc|docx|jpg|jpeg|png';
                    $config['max_size'] = 5120;
                    $config['encrypt_name'] = true;

                    if (!is_dir($config['upload_path'])) {
                        mkdir($config['upload_path'], 0755, true);
                    }

                    $this->upload->initialize($config);
                    if ($this->upload->do_upload('attachment')) {
                        $post['attachments'] = $this->upload->data('file_name');
                    }
                }

                $this->meeting_minutes_model->save($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                redirect(base_url('team_meetings'));
            }
        }

	}

    public function getDetails()
    {
		$this->data['id'] = $this->input->post('id');

		$meeting = $this->meeting_minutes_model->get_single($this->data['id']);
        if (empty($meeting)) {
            show_404();
        }

        // Check visibility permissions
        $userRole = loggedin_role_id();
        if ($meeting['meeting_type'] == 'management' && !in_array($userRole, [1, 2, 3, 5, 8])) {
            access_denied();
        }

        $this->data['meeting'] = $meeting;

		$this->load->view('team_meetings/view', $this->data);
    }

    public function edit($id = '')
    {
        if (!get_permission('team_meetings', 'is_edit')) {
            access_denied();
        }

        $meeting = $this->meeting_minutes_model->get_single($id);
        if (empty($meeting)) {
            show_404();
        }

        // Check permissions
        $userRole = loggedin_role_id();
        $userId = get_loggedin_user_id();
        if (!in_array($userRole, [1, 2, 3, 5, 8]) && $meeting['created_by'] != $userId) {
            access_denied();
        }

        if ($_POST) {
            $this->form_validation->set_rules('title', translate('title'), 'trim|required');
            $this->form_validation->set_rules('date', translate('date'), 'trim|required');
            $this->form_validation->set_rules('meeting_type', translate('meeting_type'), 'trim|required');
            $this->form_validation->set_rules('summary', translate('summary'), 'trim|required');

            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();
                $post['id'] = $id;
                $post['created_by'] = get_loggedin_user_id();
                // Handle file upload
                if (!empty($_FILES['attachment']['name'])) {
                    $config['upload_path'] = './uploads/attachments/meetings/';
                    $config['allowed_types'] = 'pdf|doc|docx|jpg|jpeg|png';
                    $config['max_size'] = 5120;
                    $config['encrypt_name'] = true;

                    $this->upload->initialize($config);
                    if ($this->upload->do_upload('attachment')) {
                        // Delete old file if exists
                        if (!empty($meeting['attachments'])) {
                            $old_file = $config['upload_path'] . $meeting['attachments'];
                            if (file_exists($old_file)) {
                                unlink($old_file);
                            }
                        }
                        $post['attachments'] = $this->upload->data('file_name');
                    }
                }

                $this->meeting_minutes_model->save($post);
                set_alert('success', translate('information_has_been_updated_successfully'));
                redirect(base_url('team_meetings'));
            }
        }

        $branchID = $this->application_model->get_branch_id();
        $this->data['meeting'] = $meeting;
        $this->data['title'] = translate('edit_meeting');
        $this->data['sub_page'] = 'team_meetings/edit';
        $this->data['main_menu'] = 'team_meetings';
        $this->data['headerelements'] = array(
           'css' => array(
                'vendor/summernote/summernote.css',
				'vendor/dropify/css/dropify.min.css',
            ),
            'js' => array(
                'vendor/summernote/summernote.min.js',
				'vendor/dropify/js/dropify.min.js',
            ),
        );
        $this->load->view('layout/index', $this->data);
    }

    public function view($id = '')
    {
        if (!get_permission('team_meetings', 'is_view')) {
            access_denied();
        }

        $meeting = $this->meeting_minutes_model->get_single($id);
        if (empty($meeting)) {
            show_404();
        }

        // Check visibility permissions
        $userRole = loggedin_role_id();
        if ($meeting['meeting_type'] == 'management' && !in_array($userRole, [1, 2, 3, 5, 8])) {
            access_denied();
        }

        $this->data['meeting'] = $meeting;
        $this->data['title'] = translate('meeting_details');
        $this->data['sub_page'] = 'team_meetings/view';
        $this->data['main_menu'] = 'team_meetings';
        $this->load->view('layout/index', $this->data);
    }

    public function delete($id = '')
    {
        if (!get_permission('team_meetings', 'is_delete')) {
            access_denied();
        }

        $meeting = $this->meeting_minutes_model->get_single($id);
        if (!empty($meeting)) {
            // Check permissions
            $userRole = loggedin_role_id();
            $userId = get_loggedin_user_id();
            if (in_array($userRole, [1, 2, 3, 5, 8]) || $meeting['created_by'] == $userId) {
                // Delete attachment file if exists
                if (!empty($meeting['attachments'])) {
                    $file_path = './uploads/attachments/meetings/' . $meeting['attachments'];
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                }
                $this->meeting_minutes_model->delete($id);
            }
        }
        redirect(base_url('team_meetings'));
    }

    public function download($id = '')
    {
        $meeting = $this->meeting_minutes_model->get_single($id);
        if (!empty($meeting) && !empty($meeting['attachments'])) {
            // Check visibility permissions
            $userRole = loggedin_role_id();
            if ($meeting['meeting_type'] == 'management' && !in_array($userRole, [1, 2, 3, 5, 8])) {
                access_denied();
            }

            $file_path = './uploads/attachments/meetings/' . $meeting['attachments'];
            if (file_exists($file_path)) {
                $this->load->helper('download');
                force_download($meeting['attachments'], file_get_contents($file_path));
            }
        }
        show_404();
    }
}