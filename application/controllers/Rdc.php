<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Rdc extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('sop_model');
        $this->load->model('rdc_model');
        $this->load->model('email_model');
        $this->load->library('upload');
    }

	public function index()
	{
		if (!get_permission('rdc_management', 'is_view')) {
			access_denied();
		}

		if ($this->input->post('update')) {
			$task_id = $this->input->post('id');
			$sop_verifier = $this->input->post('verifier_role');
			$current_user_id = get_loggedin_user_id();
			$role_id = loggedin_role_id();

			// Get task
			$task_rdc = $this->db->get_where('rdc_task', ['id' => $task_id])->row();

			if (!$task_rdc) show_404();

			$isEmployee = ($current_user_id == $task_rdc->assigned_user);

			// Determine if verifier
			$verifier_roles = explode(',', $sop_verifier ?? '');
			$isVerifier = false;
			if (in_array($role_id, $verifier_roles)) {
				if ($role_id == 8) {
					$assigned_dept = $this->db->select('department')->where('id', $task_rdc->assigned_user)->get('staff')->row('department');
					$isVerifier = $this->db->where(['id' => $current_user_id, 'department' => $assigned_dept])->get('staff')->num_rows() > 0;
				} else {
					$isVerifier = true;
				}
			}

			// 🚫 Prevent unauthorized access
			if (!$isEmployee && !$isVerifier) {
				access_denied();
			}

			// ✅ Handle Executor (Employee) Submission
			if ($isEmployee && get_permission('rdc_management', 'is_edit')) {
				$executorData = [
					'task_status'          => $this->input->post('task_status'),
					'executor_explanation' => $this->input->post('executor_explanation', false),
					'exe_cleared_on'       => date('Y-m-d H:i:s'),
				];

				// ➕ Proof Text
				$proof_text = $this->input->post('proof_text');
				if (!empty($proof_text)) {
					$executorData['proof_text'] = $proof_text;
				}

				// ➕ Executor Checklist Save
				$executorChecklist = $this->input->post('executor_checklist');
				if (is_array($executorChecklist)) {
					$executor_stage = json_decode($task_rdc->executor_stage ?? '{}', true);
					$executor_stage['completed'] = $executorChecklist;
					$executorData['executor_stages'] = json_encode($executor_stage);
				}

				// Handle multiple image uploads
				if (isset($_FILES["proof_images"]) && !empty($_FILES['proof_images']['name'][0])) {
					if (!is_dir('./uploads/attachments/rdc_proofs/')) {
						mkdir('./uploads/attachments/rdc_proofs/', 0777, TRUE);
					}

					$uploaded_images = [];
					$config['upload_path'] = './uploads/attachments/rdc_proofs/';
					$config['allowed_types'] = 'jpg|jpeg|png|gif';
					$config['max_size'] = '3072';
					$config['encrypt_name'] = true;

					for ($i = 0; $i < count($_FILES['proof_images']['name']); $i++) {
						if (!empty($_FILES['proof_images']['name'][$i])) {
							$_FILES['single_image']['name'] = $_FILES['proof_images']['name'][$i];
							$_FILES['single_image']['type'] = $_FILES['proof_images']['type'][$i];
							$_FILES['single_image']['tmp_name'] = $_FILES['proof_images']['tmp_name'][$i];
							$_FILES['single_image']['error'] = $_FILES['proof_images']['error'][$i];
							$_FILES['single_image']['size'] = $_FILES['proof_images']['size'][$i];

							$this->upload->initialize($config);
							if ($this->upload->do_upload('single_image')) {
								$uploaded_images[] = $this->upload->data('file_name');
							}
						}
					}
					if (!empty($uploaded_images)) {
						$executorData['proof_image'] = json_encode($uploaded_images);
					}
				}

				// Handle multiple file uploads
				if (isset($_FILES["proof_files"]) && !empty($_FILES['proof_files']['name'][0])) {
					if (!is_dir('./uploads/attachments/rdc_proofs/')) {
						mkdir('./uploads/attachments/rdc_proofs/', 0777, TRUE);
					}

					$uploaded_files = [];
					$config['upload_path'] = './uploads/attachments/rdc_proofs/';
					$config['allowed_types'] = 'pdf|doc|docx|xls|xlsx|zip|rar';
					$config['max_size'] = '10240';
					$config['encrypt_name'] = true;

					for ($i = 0; $i < count($_FILES['proof_files']['name']); $i++) {
						if (!empty($_FILES['proof_files']['name'][$i])) {
							$_FILES['single_file']['name'] = $_FILES['proof_files']['name'][$i];
							$_FILES['single_file']['type'] = $_FILES['proof_files']['type'][$i];
							$_FILES['single_file']['tmp_name'] = $_FILES['proof_files']['tmp_name'][$i];
							$_FILES['single_file']['error'] = $_FILES['proof_files']['error'][$i];
							$_FILES['single_file']['size'] = $_FILES['proof_files']['size'][$i];

							$this->upload->initialize($config);
							if ($this->upload->do_upload('single_file')) {
								$uploaded_files[] = $this->upload->data('file_name');
							}
						}
					}
					if (!empty($uploaded_files)) {
						$executorData['proof_file'] = json_encode($uploaded_files);
					}
				}

				$this->db->where('id', $task_id)->update('rdc_task', $executorData);
			}

			// ✅ Handle Verifier Submission
			if ($isVerifier && get_permission('rdc_management', 'is_edit')) {
				$verifierData = [
					'verify_status'        => $this->input->post('verify_status'),
					'verifier_explanation' => $this->input->post('verifier_explanation', false),
					'ver_cleared_on'       => date('Y-m-d H:i:s'),
					'verified_by'          => $current_user_id,
				];

				// ➕ Verifier Checklist Save
				$verifierChecklist = $this->input->post('verifier_checklist');
				if (is_array($verifierChecklist)) {
					$verifier_stage = json_decode($task_rdc->verifier_stage ?? '{}', true);
					$verifier_stage['completed'] = $verifierChecklist;
					$verifierData['verifier_stages'] = json_encode($verifier_stage);
				}

				$this->db->where('id', $task_id)->update('rdc_task', $verifierData);
			}

			set_alert('success', translate('information_has_been_updated_successfully'));
			redirect(base_url('rdc'));
		}

		// 🚦 View Tasks (with or without filter)
		$staffID = get_loggedin_user_id();
		$start = $end = $status = null;

		if ($this->input->post('search')) {
			$daterange = explode(' - ', $this->input->post('daterange'));
			$start = date("Y-m-d", strtotime($daterange[0]));
			$end = date("Y-m-d", strtotime($daterange[1]));
			$status = $this->input->post('status');
		} else {
			// Show only pending tasks by default (status = 1)
			$status = 1;
		}

		$this->data['tasklist'] = $this->rdc_model->getTasks($start, $end, $staffID, $status);
		$this->data['task_stats'] = $this->rdc_model->getTaskStats($start, $end, $staffID);

		// 🚀 View Setup
		$this->data['main_menu'] = 'rdc';
		$this->data['headerelements'] = [
			'css' => [
				'vendor/dropify/css/dropify.min.css',
				'vendor/daterangepicker/daterangepicker.css',
				'vendor/summernote/summernote.css',
			],
			'js' => [
				'vendor/dropify/js/dropify.min.js',
				'vendor/moment/moment.js',
				'vendor/daterangepicker/daterangepicker.js',
				'vendor/summernote/summernote.js',
			],
		];
		$this->data['title'] = translate('rdc_management');
		$this->data['sub_page'] = 'rdc/index';
		$this->load->view('layout/index', $this->data);
	}

    // get add modal
    public function getApprovelDetails()
    {
        if (get_permission('rdc_management', 'is_edit')) {
            $this->data['task_id'] = $this->input->post('id');
			$this->data['headerelements']   = array(
				'css' => array(
					'vendor/dropify/css/dropify.min.css',
					'vendor/daterangepicker/daterangepicker.css',
					'vendor/summernote/summernote.css',
				),
				'js' => array(
					'vendor/dropify/js/dropify.min.js',
					'vendor/moment/moment.js',
					'vendor/daterangepicker/daterangepicker.js',
					'vendor/summernote/summernote.js',
				),
			);

            $this->load->view('rdc/approvel_modalView', $this->data);
        }
    }

    // get add modal
    public function getRDCDetails()
    {
        if (get_permission('rdc_management', 'is_view')) {
            $this->data['request_id'] = $this->input->post('id');

			$this->data['headerelements']   = array(
				'css' => array(
					'vendor/dropify/css/dropify.min.css',
					'vendor/daterangepicker/daterangepicker.css',
					'vendor/summernote/summernote.css',
				),
				'js' => array(
					'vendor/dropify/js/dropify.min.js',
					'vendor/moment/moment.js',
					'vendor/daterangepicker/daterangepicker.js',
					'vendor/summernote/summernote.js',
				),
			);

            $this->load->view('rdc/ViewRDCDetails', $this->data);
        }
    }

    // get add modal
    public function getRDCTaskDetails()
    {
        if (get_permission('rdc_management', 'is_edit')) {
            $this->data['request_id'] = $this->input->post('id');

			$this->data['headerelements']   = array(
				'css' => array(
					'vendor/dropify/css/dropify.min.css',
					'vendor/daterangepicker/daterangepicker.css',
					'vendor/summernote/summernote.css',
				),
				'js' => array(
					'vendor/dropify/js/dropify.min.js',
					'vendor/moment/moment.js',
					'vendor/daterangepicker/daterangepicker.js',
					'vendor/summernote/summernote.js',
				),
			);

            $this->load->view('rdc/ViewRDCTaskDetails', $this->data);
        }
    }

	public function viewProofFiles()
	{
		if (get_permission('rdc_management', 'is_view')) {
			$task_id = $this->input->post('id');
			$this->data['task_id'] = $task_id;
			$this->load->view('rdc/viewProofFiles', $this->data);
		}
	}

	public function getDetailedSop()
	{
		if (get_permission('rdc_management', 'is_view')) {
			$task_id = $this->input->post('id');

			// Get RDC template ID from task
			$task = $this->db->select('rdc_id')->where('id', $task_id)->get('rdc_task')->row();

			if ($task) {
				// Get sop_ids from RDC template
				$rdc = $this->db->select('sop_ids')->where('id', $task->rdc_id)->get('rdc')->row();

				if ($rdc && !empty($rdc->sop_ids)) {
					$this->data['sop_ids'] = $rdc->sop_ids;
				} else {
					$this->data['sop_ids'] = null;
				}
			} else {
				$this->data['sop_ids'] = null;
			}

			$this->load->view('rdc/ViewMultipleSopDetails', $this->data);
		}
	}

	public function getRDCTemplatesSop()
	{
		if (get_permission('rdc_management', 'is_view')) {
			$task_id = $this->input->post('id');

			if ($task_id) {
				// Get sop_ids from RDC template
				$rdc = $this->db->select('sop_ids')->where('id', $task_id)->get('rdc')->row();
				if ($rdc && !empty($rdc->sop_ids)) {
					$this->data['sop_ids'] = $rdc->sop_ids;
				} else {
					$this->data['sop_ids'] = null;
				}
			} else {
				$this->data['sop_ids'] = null;
			}

			$this->load->view('rdc/ViewMultipleSopDetails', $this->data);
		}
	}


	public function getDetailedSop_back()
	{
		if (get_permission('rdc_management', 'is_view')) {
			$id = $this->input->post('id');
			$this->db->select('sop_id');
			$this->db->from('rdc_task');
			$this->db->where('id', $id);
			$query = $this->db->get();

			if ($query->num_rows() > 0) {
				$row = $query->row();
				$this->data['request_id'] = $row->sop_id;
			} else {
				$this->data['request_id'] = null;
			}

			$this->load->view('rdc/ViewSopDetails', $this->data);
		}
	}

	public function getRDCTemplatesSop_back()
	{
		if (get_permission('rdc_management', 'is_view')) {
			$id = $this->input->post('id');
			$this->db->select('sop_id');
			$this->db->from('rdc');
			$this->db->where('id', $id);
			$query = $this->db->get();

			if ($query->num_rows() > 0) {
				$row = $query->row();
				$this->data['request_id'] = $row->sop_id;
			} else {
				$this->data['request_id'] = null;
			}

			$this->load->view('rdc/ViewSopDetails', $this->data);
		}
	}

	protected function rdc_validation()
	{
		$this->form_validation->set_rules('title', translate('title'), 'trim|required');
		$this->form_validation->set_rules('description', translate('description'), 'trim|required');
		$this->form_validation->set_rules('assigned_user', translate('assigned_user'), 'trim|required');

		// Custom validation for sop_ids array
		if (empty($this->input->post('sop_ids')) || !is_array($this->input->post('sop_ids'))) {
			$this->form_validation->set_rules('sop_ids_required', 'SOPs', 'required');
		}

		// Custom validation for user_pool when multi_random is selected
		if ($this->input->post('assigned_user') === 'multi_random') {
			if (empty($this->input->post('user_pool')) || !is_array($this->input->post('user_pool'))) {
				$this->form_validation->set_rules('user_pool_required', 'User Pool', 'required');
			}
		}
	}

	public function create()
	{
		if (!get_permission('rdc_management', 'is_add')) {
			access_denied();
		}

		if ($_POST) {

			$this->rdc_validation();
			if ($this->form_validation->run() !== false) {
				// SAVE INFORMATION IN THE DATABASE FILE
				$this->rdc_model->save($this->input->post());
				set_alert('success', translate('information_has_been_saved_successfully'));
				$array = array('status' => 'success');
			} else {
				$error = $this->form_validation->error_array();
				$array = array('status' => 'fail', 'error' => $error);
			}
			echo json_encode($array);
			redirect(base_url('rdc/templates'));

		}

		$this->data['headerelements'] = array(
			'css' => array(
				'css/certificate.css',
				'vendor/summernote/summernote.css',
				'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css',
			),
			'js' => array(
				'js/certificate.js',
				'vendor/summernote/summernote.js',
				'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js',
			),
		);
		$this->data['list_sop'] = $this->app_lib->get_table('sop', $id, true);
		$this->data['title'] = translate('RDC');
		$this->data['sub_page'] = 'rdc/create';
		$this->data['main_menu'] = 'rdc';
		$this->load->view('layout/index', $this->data);
	}

    public function templates()
    {
        if (!get_permission('rdc_management', 'is_add')) {
            access_denied();
        }
		$User_ID = get_loggedin_user_id();
        if (isset($_POST['search'])) {
           $daterange = explode(' - ', $this->input->post('daterange'));
			$start = date("Y-m-d", strtotime($daterange[0]));
			$end = date("Y-m-d", strtotime($daterange[1]));

        }
        $this->data['tasklist'] = $this->rdc_model->getRDC_tasks($start, $end, $User_ID);
		$this->data['headerelements'] = array(
			'css' => array(
				'css/certificate.css',
				'vendor/summernote/summernote.css',
				'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css',
			),
			'js' => array(
				'js/certificate.js',
				'vendor/summernote/summernote.js',
				'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js',
			),
		);
        $this->data['title'] = translate('RDC - Recurring Discipline & Compliance');
        $this->data['sub_page'] = 'rdc/templates';
        $this->data['main_menu'] = 'rdc';
        $this->load->view('layout/index', $this->data);
    }

	public function edit($id = '')
{
    if (!get_permission('rdc_management', 'is_edit')) {
        access_denied();
    }

    if ($_POST) {
        $this->rdc_validation();
        // Additional validation for edit
        if (empty($this->input->post('sop_ids')) || !is_array($this->input->post('sop_ids'))) {
            $this->form_validation->set_rules('sop_ids_required', 'SOPs', 'required');
        }
        if ($this->form_validation->run() !== false) {

            // Sanitize input
            $post = $this->input->post();

            // Check if random assignment or multi-user random
            $is_random = ($post['assigned_user'] === 'random');
            $is_multi_random = !empty($post['user_pool']) && is_array($post['user_pool']);

            // 1️⃣ Update RDC table
            $rdc_data = array(
                'title'               => $post['title'],
                'description'         => $post['description'],
                'frequency'           => $post['frequency'],
                'assigned_user'       => ($is_random || $is_multi_random) ? null : $post['assigned_user'],
                'is_random_assignment'=> ($is_random || $is_multi_random) ? 1 : 0,
                'user_pool'           => $is_multi_random ? json_encode($post['user_pool']) : null,
                'due_time'            => $post['due_time'],
                'verifier_due_time'   => $post['verifier_due_time'],
                'verifier_required'   => isset($post['verifier_required']) ? 1 : 0,
                'sop_ids'             => isset($post['sop_ids']) && is_array($post['sop_ids']) ? json_encode($post['sop_ids']) : json_encode([]),
                'is_proof_required'   => $post['is_proof_required'],
                'pre_reminder_enabled'=> $post['pre_reminder_enabled'],
                'escalation_enabled'  => $post['escalation_enabled'],
                'updated_at'          => date('Y-m-d H:i:s')
            );

            $this->db->where('id', $id);
            $this->db->update('rdc', $rdc_data);

            // 2️⃣ Update RDC Notifications table
            $notification_data = array(
                'frequency'    => $post['frequency'],
                'daily_time'   => ($post['frequency'] == 'daily') ? $post['daily_time'] : null,
                'weekly_day'   => ($post['frequency'] == 'weekly') ? $post['weekly_day'] ?? null : null,
                'weekly_time'  => ($post['frequency'] == 'weekly') ? $post['weekly_time'] ?? null : null,
                'monthly_day'  => ($post['frequency'] == 'monthly') ? $post['monthly_day'] ?? null : null,
                'monthly_time' => ($post['frequency'] == 'monthly') ? $post['monthly_time'] ?? null : null,
                'bimonthly_day1' => ($post['frequency'] == 'bimonthly') ? $post['bimonthly_day1'] ?? null : null,
                'bimonthly_day2' => ($post['frequency'] == 'bimonthly') ? $post['bimonthly_day2'] ?? null : null,
                'bimonthly_time' => ($post['frequency'] == 'bimonthly') ? $post['bimonthly_time'] ?? null : null,
                'yearly_month' => ($post['frequency'] == 'yearly') ? $post['yearly_month'] ?? null : null,
                'yearly_day' => ($post['frequency'] == 'yearly') ? $post['yearly_day'] ?? null : null,
                'yearly_time' => ($post['frequency'] == 'yearly') ? $post['yearly_time'] ?? null : null,
                'is_active'    => 1
            );

            $this->db->where('rdc_id', $id);
            $this->db->update('rdc_notifications', $notification_data);

            // 3️⃣ Create RDC task, tracker_issues and staff_task_log
            $this->rdc_model->create_rdc_task($id, $rdc_data, $post);

            // 4️⃣ Response
            set_alert('success', translate('information_has_been_saved_successfully'));
            redirect(base_url('rdc/templates'));

        } else {
            $error = $this->form_validation->error_array();
            echo json_encode(array('status' => 'fail', 'error' => $error));
        }
    }

    // Fetch current data
    $this->db->select('rdc.*, rdc_notifications.*')
             ->from('rdc')
             ->join('rdc_notifications', 'rdc_notifications.rdc_id = rdc.id', 'inner')
             ->where('rdc.id', $id);
    $query = $this->db->get();

    $this->data['rdc_list'] = $query->result();
    $this->data['list_sop'] = $this->app_lib->get_table('sop', $id, true);
    $this->data['title'] = translate('RDC') . " " . translate('templete');
    $this->data['headerelements'] = array(
        'css' => array(
            'css/certificate.css',
            'vendor/summernote/summernote.css',
            'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css',
        ),
        'js' => array(
            'js/certificate.js',
            'vendor/summernote/summernote.js',
            'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js',
        ),
    );
    $this->data['sub_page'] = 'rdc/edit';
    $this->data['main_menu'] = 'rdc';
    $this->load->view('layout/index', $this->data);
}



    public function delete_back($id = '')
    {
        if (get_permission('rdc_management', 'is_delete')) {
            $this->db->where('id', $id);
            $this->db->delete('rdc_task');
        }
    }

	public function delete($id = '')
	{
		$logged_in_user = get_loggedin_user_id();

		$data = [
			'flag'       => 0,
			'deleted_by' => $logged_in_user,
			'deleted_at' => date('Y-m-d H:i:s') // optional: track when deleted
		];

		$this->db->where('id', $id);
		$this->db->update('rdc_task', $data);
	}


    public function deleted_tasks()
    {
        if (!get_permission('rdc', 'is_view')) {
            access_denied();
        }

        $data['title'] = translate('deleted_rdc_tasks');
        $data['sub_page'] = 'rdc/deleted_tasks';
        $data['main_menu'] = 'rdc';

        // Get date filters
        $start_date = $this->input->get('start_date');
        $end_date = $this->input->get('end_date');

        $data['deleted_tasks'] = $this->rdc_model->getDeletedTasks($start_date, $end_date);
        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;

        $this->load->view('layout/index', $data);
    }

	public function delete_template($id = '')
	{
		if (get_permission('rdc_management', 'is_delete')) {
			// First delete related notifications
			$this->db->where('rdc_id', $id);
			$this->db->delete('rdc_notifications');

			// Then delete the main RDC entry
			$this->db->where('id', $id);
			$this->db->delete('rdc');
		}
	}


    public function handle_upload()
    {
        if (isset($_FILES["attachment_file"]) && !empty($_FILES['attachment_file']['name'])) {
            $file_type      = $_FILES["attachment_file"]['type'];
            $file_size      = $_FILES["attachment_file"]["size"];
            $file_name      = $_FILES["attachment_file"]["name"];
            $allowedExts    = array('pdf','doc','xls','docx','xlsx','jpg','jpeg','png','gif','bmp');
            $upload_size    = 2097152;
            $extension      = pathinfo($file_name, PATHINFO_EXTENSION);
            if ($files = filesize($_FILES['attachment_file']['tmp_name'])) {
                if (!in_array(strtolower($extension), $allowedExts)) {
                    $this->form_validation->set_message('handle_upload', translate('this_file_type_is_not_allowed'));
                    return false;
                }
                if ($file_size > $upload_size) {
                    $this->form_validation->set_message('handle_upload', translate('file_size_shoud_be_less_than') . " " . ($upload_size / 1024) . " KB");
                    return false;
                }
            } else {
                $this->form_validation->set_message('handle_upload', translate('error_reading_the_file'));
                return false;
            }
            return true;
        } else {
            return true;
        }
    }

    public function download($id = '', $file = '')
    {
        if (!empty($id) && !empty($file)) {

            $this->db->select('proof_image,proof_file');
            $this->db->where('id', $id);
            $rdc = $this->db->get('rdc_task')->row();

            $this->load->helper('download');
            $fileData = file_get_contents('./uploads/attachments/rdc_proofs/' . $rdc->proof_image);
            force_download($leave->orig_file_name, $fileData);
        }
    }

    public function configuration()
    {
        if (!get_permission('rdc_configuration', 'is_view')) {
            access_denied();
        }

        $config = array();
        if ($this->input->post('submit') == 'reminder') {
            foreach ($this->input->post() as $input => $value) {
                if ($input == 'submit') {
                    continue;
                }
                $config[$input] = $value;
            }
            $this->db->where('id', 1);
            $this->db->update('global_settings', $config);

            $isRTL = $this->app_lib->getRTLStatus($config['translation']);
            $this->session->set_userdata(['set_lang' => $config['translation'], 'is_rtl' => $isRTL]);

            set_alert('success', translate('the_configuration_has_been_updated'));
            redirect($_SERVER['HTTP_REFERER']); // Or your preferred redirect
        }

		if ($this->input->post('submit') == 'escalation') {
			// Collect post data
			$delays   = $this->input->post('delay_hours');
			$roles    = $this->input->post('escalate_to_role');
			$messages = $this->input->post('message');

			$escalation_levels = [];

			// Combine into structured array
			foreach ($delays as $index => $delay) {
				$escalation_levels[] = [
					'level'        => $index + 1,
					'delay_hours'  => (int) $delay,
					'role_id'      => (int) ($roles[$index] ?? 0),
					//'message'      => $messages[$index] ?? '',
				];
			}

			// Prepare config to update
			$config = [
				'escalation_levels' => json_encode($escalation_levels, JSON_UNESCAPED_UNICODE),
			];

			// Save to global_settings (assuming ID = 1 or use your structure)
			$this->db->where('id', 1);
			$this->db->update('global_settings', $config);

			set_alert('success', translate('the_configuration_has_been_updated'));
			$this->session->set_flashdata('active', 2);
			redirect($_SERVER['HTTP_REFERER']); // Or your preferred redirect
		}

		$this->data['verifier'] = $this->sop_model->getVerifier();
        $this->data['title'] = translate('RDC - Configuration');
        $this->data['sub_page'] = 'rdc/configuration';
        $this->data['main_menu'] = 'rdc';
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

	public function escalations()
	{
		if (!get_permission('rdc_escalations', 'is_view')) {
			access_denied();
		}

		$User_ID = get_loggedin_user_id();

		$start = $end = '';
		if (isset($_POST['search'])) {
			$daterange = explode(' - ', $this->input->post('daterange'));
			$start = date("Y-m-d", strtotime($daterange[0]));
			$end = date("Y-m-d", strtotime($daterange[1]));
		}

		// Fetch raw tasks
		$rawTasks = $this->rdc_model->getEscalatedRDC_tasks($start, $end, $User_ID);

		// Group tasks by ID with escalation logs nested
		$groupedTasks = [];
		foreach ($rawTasks as $task) {
			$task_id = $task['id'];

			if (!isset($groupedTasks[$task_id])) {
				$groupedTasks[$task_id] = $task;
				$groupedTasks[$task_id]['escalations'] = [];
			}

			// If escalation info exists, add it
			if (!empty($task['escalated_person'])) {
				$groupedTasks[$task_id]['escalations'][] = [
					'escalated_person' => $task['escalated_person'],
					'action_type' => $task['action_type'],
					'escaltion_reason' => $task['escaltion_reason'],
				];
			}
		}

		$this->data['tasklist'] = $groupedTasks;

		// Asset loading
		$this->data['headerelements'] = array(
			'css' => array(
				'vendor/dropify/css/dropify.min.css',
				'vendor/daterangepicker/daterangepicker.css',
				'vendor/summernote/summernote.css',
			),
			'js' => array(
				'vendor/dropify/js/dropify.min.js',
				'vendor/moment/moment.js',
				'vendor/daterangepicker/daterangepicker.js',
				'vendor/summernote/summernote.js',
			),
		);

		$this->data['title'] = translate('RDC - Escalated Tasks');
		$this->data['sub_page'] = 'rdc/escalations';
		$this->data['main_menu'] = 'rdc';
		$this->load->view('layout/index', $this->data);
	}


	   // get add escalation modal
	public function blockerLogic()
	{
		if (!get_permission('rdc_escalations', 'is_view')) {
			return;
		}

		$escalation_id = $this->input->post('id');

		// 1. Fetch task + verifier roles
		$this->db->select('rt.*, s.department AS user_department, sp.verifier_role');
		$this->db->from('rdc_task rt');
		$this->db->join('staff s', 's.id = rt.assigned_user', 'left');
		$this->db->join('sop sp', 'sp.id = rt.sop_id', 'left');
		$this->db->where('rt.id', $escalation_id);
		$task = $this->db->get()->row();

		if (!$task) {
			show_error('Escalated task not found.');
			return;
		}

		// 2. Assigned User (Executor)
		$executor_id = $task->assigned_user;
		$executor_name = $this->db->get_where('staff', ['id' => $executor_id])->row('name');

		// 3. Resolve all possible verifiers
		$verifier_list = [];
		$verifier_id = null;
		$verifier_name = 'Unknown';

		if (!empty($task->verifier_role)) {
			$roles = explode(',', $task->verifier_role);

			foreach ($roles as $role) {
				$role = (int)trim($role);

				$this->db->select('staff.id, staff.name');
				$this->db->from('staff');
				$this->db->join('login_credential', 'login_credential.user_id = staff.id');
				$this->db->where('login_credential.role', $role);
				$this->db->where('login_credential.active', 1);

				if ($role === 8) {
					$this->db->where('staff.department', $task->user_department);
				}

				$verifiers = $this->db->get()->result();

				if ($verifiers) {
					foreach ($verifiers as $v) {
						if (!isset($verifier_list[$v->id])) {
							$verifier_list[$v->id] = $v->name;
						}
					}
				}
			}

			// Assign the first verifier (for backward compatibility)
			if (!empty($verifier_list)) {
				$verifier_id = array_key_first($verifier_list);
				$verifier_name = $verifier_list[$verifier_id];
			}
		}

		// 4. Pass data to view
		$this->data['executor_id'] = $executor_id;
		$this->data['executor_name'] = $executor_name;
		$this->data['verifier_id'] = $verifier_id;
		$this->data['verifier_name'] = $verifier_name;
		$this->data['verifier_list'] = $verifier_list;
		$this->data['escalation_id'] = $escalation_id;

		$this->data['headerelements'] = array(
			'css' => array(
				'vendor/dropify/css/dropify.min.css',
				'vendor/daterangepicker/daterangepicker.css',
				'vendor/summernote/summernote.css',
			),
			'js' => array(
				'vendor/dropify/js/dropify.min.js',
				'vendor/moment/moment.js',
				'vendor/daterangepicker/daterangepicker.js',
				'vendor/summernote/summernote.js',
			),
		);

		$this->load->view('rdc/Escalation_implement', $this->data);
	}

	public function escalation_action_submit()
{
    header('Content-Type: application/json');

    $task_id       = $this->input->post('task_id');
    $target_person = $this->input->post('target_person');
    $action_type   = $this->input->post('action_type');
    $reason        = $this->input->post('reason');

    // Get RDC task with SOP details
    $this->db->select('rt.*, sp.verifier_role, sp.title as sop_title');
    $this->db->from('rdc_task rt');
    $this->db->join('sop sp', 'sp.id = rt.sop_id', 'left');
    $this->db->where('rt.id', $task_id);
    $task = $this->db->get()->row();

    if (!$task) {
        echo json_encode(['status' => 'error', 'message' => 'Escalated task not found.']);
        return;
    }

    // Initialize
    $target_user_ids = [];

    // Get Executor
    if ($target_person === 'executor') {
        if (!empty($task->assigned_user)) {
            $target_user_ids[] = $task->assigned_user;
        }

    } elseif ($target_person === 'verifier') {
		 if (!empty($this->input->post('verifier_id'))) {
            $target_user_ids[] = $this->input->post('verifier_id');
        }
	}

    if (empty($target_user_ids)) {
		echo json_encode(['status' => 'error', 'message' => 'Could not identify any valid users for the escalation action.']);
        return;
    }

    // Apply actions to all target users
    foreach ($target_user_ids as $uid) {

		// ✅ Get staff info
		$staff = $this->db->get_where('staff', ['id' => $uid])->row();
		$staff_name = $staff ? $staff->name : 'Staff';
		$dept_name = 'Unknown';

		if ($staff && isset($staff->department)) {
			$dept = $this->db->get_where('staff_department', ['id' => $staff->department])->row();
			if ($dept) {
				$dept_name = $dept->name;
			}
		}

        if ($action_type === 'block_salary') {
            // Prevent duplicate blocking for same task/user
            $exists = $this->db->get_where('salary_blocks', ['staff_id' => $uid, 'task_id' => $task_id])->num_rows();
            if (!$exists) {
                $this->db->insert('salary_blocks', [
                    'staff_id'   => $uid,
                    'reason'     => $reason,
                    'task_id'    => $task_id,
                    'blocked_by' => get_loggedin_user_id(),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
				$this->db->insert('rdc_task_escalation_log', [
					'task_id'     => $task->id,
					'staff_id'    => $uid,
					'action_type' => 'salary_blocked',
					'remarks'     => $reason
				]);

			// ✅ Insert notification for warning issue
			$notificationData = array(
				'user_id'    => $uid,
				'type'       => 'salary_block',
				'title'      => 'Salary Blocked',
				'message' => 'Dear ' . $staff_name . ', your salary has been put on temporary hold due to an escalation related to a pending RDC task. Kindly address the matter at the earliest.',
				'url'        => base_url('rdc'),
				'is_read'    => 0,
				'created_at' => date('Y-m-d H:i:s')
			);

			$this->db->insert('notifications', $notificationData);

			// Telegram send block
			$bot_token = $telegram_bot;
			$chat_id = $telegram_chatID;

			 // ✅ Prepare Telegram message
            $telegram_message = "⚠️ Salary Block Notice\n\n" .
                "👤 Name: {$staff_name}\n" .
                "🏢 Department: {$dept_name}\n" .
                "📁 Task: {$task->title}\n" .
                "📝 Reason: {$reason}\n\n" .
                "📅 Issued On: " . date('d M, Y h:i A') . "\n" ;
			// 📎 Final link
			$telegram_message .= "🔗 [View](" . base_url('rdc') . ")";

			$payload = [
			'chat_id' => $chat_id,
			'text' => $telegram_message,
			'parse_mode' => 'Markdown',
			'disable_web_page_preview' => true,
			];

			$url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
			$response = curl_exec($ch);
			curl_close($ch);

			log_message('debug', 'Telegram RDC reminder sent: ' . $response);

			if ($staff && !empty($staff->email)) {
				$verifier_roles = explode(',', $task->verifier_role); // e.g., "3,5,8"
				$verifiers = [];

				foreach ($verifier_roles as $role) {
					if ($role == 8) {
						// Only get department-matched staff for role 8
						$role8_verifiers = $this->db->select('staff.email, staff.name')
							->from('staff')
							->join('login_credential', 'login_credential.user_id = staff.id')
							->where('login_credential.role', 8)
							->where('staff.department', $department)
							->where('login_credential.active', 1)
							->get()
							->result_array();

						$verifiers = array_merge($verifiers, $role8_verifiers);
					} else {
						// For other roles, get all
						$other_role_verifiers = $this->db->select('staff.email, staff.name')
							->from('staff')
							->join('login_credential', 'login_credential.user_id = staff.id')
							->where('login_credential.role', $role)
							->where('login_credential.active', 1)
							->get()
							->result_array();

						$verifiers = array_merge($verifiers, $other_role_verifiers);
					}
				}

				// Remove duplicates (if any)
				$verifiers = array_map("unserialize", array_unique(array_map("serialize", $verifiers)));

				// Extract CC emails
				$cc_emails = array_column($verifiers, 'email');
				$cc_email_string = implode(',', $cc_emails);

				$to_name = $staff->name;
				$to_email = $staff->email;

				$mail_subject = 'Salary Blocked Due to Task Escalation';

				$mail_body = "
					<html>
					  <body style='font-family:Arial, sans-serif; background:#f4f4f4; padding:20px;'>
						<table style='max-width:600px; margin:auto; background:#fff; border-radius:8px; padding:20px; box-shadow:0 0 8px rgba(0,0,0,0.05);'>
						  <tr>
							<td style='text-align:center;'>
							  <h2 style='color:#c0392b;'>Salary Block Notice</h2>
							</td>
						  </tr>
						  <tr>
							<td style='font-size:15px; color:#333;'>
							  <p>Dear <strong>{$to_name}</strong>,</p>

							  <p>This is to inform you that your salary has been temporarily <strong>blocked</strong> due to an unresolved RDC task escalation.</p>

							  <p style='margin:15px 0; font-size:16px;'>
								<strong>Task Title:</strong> {$task->title}<br>
								<strong>Reason:</strong> {$reason}<br>
							  </p>

							  <p>Please log in to the EMP portal immediately to review the task and take necessary action.</p>

							  <p style='margin-top:30px;'>Kind regards,<br><strong>EMP System</strong></p>

							  <p style='text-align:center; font-size:13px; color:#888; margin-top:40px;'>
								This is an automated message from EMP system. No reply is necessary.
							  </p>
							</td>
						  </tr>
						</table>
					  </body>
					</html>
					";

				// Send Email
				$config = $this->email_model->get_email_config();

				$email_data = [
					'smtp_host'     => $config['smtp_host'],
					'smtp_auth'     => true,
					'smtp_user'     => $config['smtp_user'],
					'smtp_pass'     => $config['smtp_pass'],
					'smtp_secure'   => $config['smtp_encryption'],
					'smtp_port'     => $config['smtp_port'],
					'from_email'    => $config['email'],
					'from_name'     => 'EMP Admin',
					'to_email'      => $to_email,
					'to_name'       => $to_name,
					'subject'       => $mail_subject,
					'body'          => $mail_body,
					'cc'            => $cc_email_string // ✅ Manager in CC
				];

				$this->email_model->send_email_yandex($email_data);
			}

		} else {
			echo json_encode(['status' => 'error', 'message' => 'Escalation action already applied to selected user for that task.']);
			return;
		}

        } elseif ($action_type === 'showcause') {
            // Get employee's department and manager info
            $employee_dept = $this->db->select('department')->where('id', $uid)->get('staff')->row('department');
            $manager_id = null;
            $advisor_id = null;

            // Find department manager (role 8)
            if ($employee_dept) {
                $manager = $this->db->select('staff.id')
                    ->from('staff')
                    ->join('login_credential', 'login_credential.user_id = staff.id')
                    ->where('staff.department', $employee_dept)
                    ->where('login_credential.role', 8)
                    ->where('login_credential.active', 1)
                    ->get()
                    ->row();
                $manager_id = $manager ? $manager->id : null;
            }

            // Find advisor (role 10) - usually branch-based or general
            $advisor = $this->db->select('staff.id')
                ->from('staff')
                ->join('login_credential', 'login_credential.user_id = staff.id')
                ->where('login_credential.role', 10)
                ->where('login_credential.active', 1)
                ->limit(1)
                ->get()
                ->row();
            $advisor_id = $advisor ? $advisor->id : null;

            $issue_date = date('Y-m-d');

            $this->db->insert('warnings', [
                'user_id'       => $uid,
                'manager_id'    => $manager_id,     // ✅ Properly assign manager
                'advisor_id'    => $advisor_id,     // ✅ Properly assign advisor
                'role_id'       => 4,               // Employee role
                'branch_id'     => 1,               // Default branch
				'advisor_review'     => 1,
                'refrence'      => 'ESCALATION SHOWCAUSE - '. $task->title,
                'category'      => 'RDC Task',
                'effect'        => 'Recurring Discipline & Compliance',
                'session_id'    => get_session_id(),
                'reason'        => $reason,
                'rdc_task_id'   => $task_id,
                'status'        => 1,
                'clearance_time'=> 24,
                'manager_review'=> $manager_id ? 1 : 0,  // Enable manager review if manager exists
                'issued_by'     => get_loggedin_user_id(),
                'issue_date'    => $issue_date,
                'email_sent'    => 0,
            ]);

			$this->db->insert('rdc_task_escalation_log', [
				'task_id'     => $task->id,
				'staff_id'    => $uid,
				'action_type' => 'todo_assigned',
				'remarks'     => $reason
			]);

			// ✅ Insert notification for warning issue
			$notificationData = array(
				'user_id'    => $uid,
				'type'       => 'warning',
				'title'      => 'New Warning Issued',
				'message'    => 'Dear ' . $staff_name . ', a new warning has been issued to you. Please take immediate action.',
				'url'        => base_url('todo'),
				'is_read'    => 0,
				'created_at' => date('Y-m-d H:i:s')
			);

			$this->db->insert('notifications', $notificationData);

			// Telegram send block
			$bot_token = $telegram_bot;
			$chat_id = $telegram_chatID;

			 // ✅ Prepare Telegram message
            $telegram_message = "⚠️ New Warning Issued\n\n" .
                "👤 Name: {$staff_name}\n" .
                "🏢 Department: {$dept_name}\n" .
                "📁 Category: RDC Task\n" .
                "💥 Effect: Recurring Discipline & Compliance\n" .
                "🕒 Clearance Time: 24 Hours\n" .
                "📝 Reason: {$reason}\n\n" .
                "📅 Issued On: " . date('d M, Y h:i A') . "\n" ;
			// 📎 Final link
			$telegram_message .= "🔗 [View Todo](" . base_url('todo') . ")";

			$payload = [
			'chat_id' => $chat_id,
			'text' => $telegram_message,
			'parse_mode' => 'Markdown',
			'disable_web_page_preview' => true,
			];

			$url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
			$response = curl_exec($ch);
			curl_close($ch);

			log_message('debug', 'Telegram RDC reminder sent: ' . $response);

         } elseif ($action_type === 'cleared') {
            // Log the cleared action
            $this->db->insert('rdc_task_escalation_log', [
                'task_id'     => $task->id,
                'staff_id'    => $uid,
                'action_type' => 'cleared',
                'remarks'     => $reason
            ]);

            // Send system notification
            $notificationData = array(
                'user_id'    => $uid,
                'type'       => 'escalation_cleared',
                'title'      => 'Escalated Task Cleared',
                'message'    => 'Dear ' . $staff_name . ', your escalated task has been cleared. ' . $reason,
                'url'        => base_url('rdc'),
                'is_read'    => 0,
                'created_at' => date('Y-m-d H:i:s')
            );

            $this->db->insert('notifications', $notificationData);
        }
    }

    // Get updated escalation data
    $escalation_data = $this->db->select('staff.name as escalated_person, rdc_task_escalation_log.action_type, rdc_task_escalation_log.remarks as escaltion_reason')
        ->from('rdc_task_escalation_log')
        ->join('staff', 'staff.id = rdc_task_escalation_log.staff_id', 'left')
        ->where('rdc_task_escalation_log.task_id', $task_id)
        ->get()
        ->result_array();

    echo json_encode([
        'status' => 'success',
        'message' => 'Escalation action applied successfully.',
        'escalation_data' => $escalation_data
    ]);
}

	public function logs()
{
	if (!get_permission('rdc_escalations', 'is_view')) {
		access_denied();
	}
	$this->data['title'] = translate('RDC - Escalation') . " " . translate('logs');
	$this->data['logs'] = $this->db->order_by('id', 'DESC')->get('rdc_task_escalation_log')->result_array();
	$this->data['sub_page'] = 'rdc/logs';
	$this->data['main_menu'] = 'rdc';
	$this->load->view('layout/index', $this->data);
}


public function update_escalation_reason()
{
	if (!get_permission('rdc_escalations', 'is_edit')) {
		echo json_encode(['status' => 'error', 'message' => 'Access denied']);
		return;
	}

	$task_id = $this->input->post('task_id');
	$index = $this->input->post('index');
	$new_reason = $this->input->post('new_reason');
	$new_action = $this->input->post('new_action');

	if (empty($new_reason)) {
		echo json_encode(['status' => 'error', 'message' => 'Reason cannot be empty']);
		return;
	}

	// Get current escalation log entry
	$current_log = $this->db->where('task_id', $task_id)->limit(1, $index)->get('rdc_task_escalation_log')->row();
	if (!$current_log) {
		echo json_encode(['status' => 'error', 'message' => 'Escalation log not found']);
		return;
	}

	// Update the escalation log
	$update_data = ['remarks' => $new_reason];
	if (!empty($new_action)) {
		$update_data['action_type'] = $new_action;
	}

	$this->db->set($update_data);
	$this->db->where('task_id', $task_id);
	$this->db->limit(1, $index);
	$updated = $this->db->update('rdc_task_escalation_log');

	if ($updated && !empty($new_action) && $new_action !== $current_log->action_type) {
		// Action type changed, process the new action
		$this->process_escalation_action($task_id, $current_log->staff_id, $new_action, $new_reason);
	}

	if ($updated) {
		echo json_encode(['status' => 'success', 'message' => 'Escalation updated successfully']);
	} else {
		echo json_encode(['status' => 'error', 'message' => 'Failed to update escalation']);
	}
}

private function process_escalation_action($task_id, $staff_id, $action_type, $reason)
{
	// Get task and staff info
	$task = $this->db->select('rt.*, sp.title as sop_title')
		->from('rdc_task rt')
		->join('sop sp', 'sp.id = rt.sop_id', 'left')
		->where('rt.id', $task_id)
		->get()->row();

	if (!$task) return;

	$staff = $this->db->get_where('staff', ['id' => $staff_id])->row();
	if (!$staff) return;

	$staff_name = $staff->name;
	$dept_name = 'Unknown';

	if ($staff->department) {
		$dept = $this->db->get_where('staff_department', ['id' => $staff->department])->row();
		if ($dept) $dept_name = $dept->name;
	}

	if ($action_type === 'block_salary') {
		// Check if salary block already exists
		$exists = $this->db->get_where('salary_blocks', ['staff_id' => $staff_id, 'task_id' => $task_id])->num_rows();
		if (!$exists) {
			$this->db->insert('salary_blocks', [
				'staff_id' => $staff_id,
				'reason' => $reason,
				'task_id' => $task_id,
				'blocked_by' => get_loggedin_user_id(),
				'created_at' => date('Y-m-d H:i:s'),
			]);

			// Send notification
			$this->db->insert('notifications', [
				'user_id' => $staff_id,
				'type' => 'salary_block',
				'title' => 'Salary Blocked',
				'message' => 'Dear ' . $staff_name . ', your salary has been put on temporary hold due to an escalation related to a pending RDC task.',
				'url' => base_url('rdc'),
				'is_read' => 0,
				'created_at' => date('Y-m-d H:i:s')
			]);
		}
	} elseif ($action_type === 'showcause') {
		// Get employee's department and manager info
		$employee_dept = $this->db->select('department')->where('id', $staff_id)->get('staff')->row('department');
		$manager_id = null;
		$advisor_id = null;

		// Find department manager (role 8)
		if ($employee_dept) {
			$manager = $this->db->select('staff.id')
				->from('staff')
				->join('login_credential', 'login_credential.user_id = staff.id')
				->where('staff.department', $employee_dept)
				->where('login_credential.role', 8)
				->where('login_credential.active', 1)
				->get()
				->row();
			$manager_id = $manager ? $manager->id : null;
		}

		// Find advisor (role 10)
		$advisor = $this->db->select('staff.id')
			->from('staff')
			->join('login_credential', 'login_credential.user_id = staff.id')
			->where('login_credential.role', 10)
			->where('login_credential.active', 1)
			->limit(1)
			->get()
			->row();
		$advisor_id = $advisor ? $advisor->id : null;

		$this->db->insert('warnings', [
			'user_id'       => $staff_id,
			'manager_id'    => $manager_id,     // ✅ Properly assign manager
			'advisor_id'    => $advisor_id,     // ✅ Properly assign advisor
			'role_id'       => 4,               // Employee role
			'advisor_review'     => 1,
			'refrence'      => 'ESCALATION SHOWCAUSE - '. $task->title,
			'category'      => 'RDC Task',
			'effect'        => 'Recurring Discipline & Compliance',
			'session_id'    => get_session_id(),
			'reason'        => $reason,
			'rdc_task_id'   => $task_id,
			'status'        => 1,
			'clearance_time'=> 24,
			'manager_review'=> $manager_id ? 1 : 0,  // Enable manager review if manager exists
			'issued_by'     => get_loggedin_user_id(),
			'issue_date'    => date('Y-m-d'),
			'email_sent'    => 0,
		]);

		// Send notification
		$this->db->insert('notifications', [
			'user_id' => $staff_id,
			'type' => 'warning',
			'title' => 'New Warning Issued',
			'message' => 'Dear ' . $staff_name . ', a new warning has been issued to you. Please take immediate action.',
			'url' => base_url('todo'),
			'is_read' => 0,
			'created_at' => date('Y-m-d H:i:s')
		]);
	} elseif ($action_type === 'cleared') {
		// Send cleared notification
		$this->db->insert('notifications', [
			'user_id' => $staff_id,
			'type' => 'escalation_cleared',
			'title' => 'Escalated Task Cleared',
			'message' => 'Dear ' . $staff_name . ', your escalated task has been cleared. ' . $reason,
			'url' => base_url('rdc'),
			'is_read' => 0,
			'created_at' => date('Y-m-d H:i:s')
		]);
	}
}


public function dashboard()
{
	if (!get_permission('rdc_dashboard', 'is_view')) {
		access_denied();
	}

	$role_id = loggedin_role_id();
	$staff_id = get_loggedin_user_id();
	//$staff_id = 15;

	// Load shared task-focused metrics for verifiers
	$this->data['total_tasks'] = $this->rdc_model->get_total_tasks($staff_id) ?? 0;
	$this->data['total_verified'] = $this->rdc_model->get_total_verified_tasks($staff_id) ?? 0;
	$this->data['total_unverified'] = $this->rdc_model->get_total_unverified_tasks($staff_id) ?? 0;
	$this->data['total_tasks_under_you'] = $this->rdc_model->get_total_tasks_under_verifier($staff_id) ?? 0;


	 // User role-based dashboard
	if (in_array($role_id, [1, 2, 3, 4, 5, 8])) {
		$this->data['todays_tasks'] = $this->rdc_model->get_todays_tasks($staff_id);
		$this->data['pending_verifications'] = $this->rdc_model->get_user_pending_verifications($staff_id);
		$this->data['discipline_score'] = $this->rdc_model->get_user_discipline_score($staff_id);
	}

	// Supervisor dashboard logic
	if (in_array($role_id, [1, 3, 5, 8])) {
		$this->data['team_task_status'] = $this->rdc_model->get_team_task_status($staff_id);
		//$this->data['awaiting_verifications'] = $this->rdc_model->get_team_pending_verifications($staff_id);
		$this->data['escalated_issues'] = $this->rdc_model->get_team_escalated_tasks($staff_id);
	}

	// COO/CEO logic
	if (in_array($role_id, [1, 3, 5, 8])) {
		$this->data['escalated_tasks'] = $this->rdc_model->get_all_escalated_tasks();
		$this->data['department_compliance'] = $this->rdc_model->get_department_compliance();
		$this->data['salary_lock_alerts'] = $this->rdc_model->get_salary_block_alerts();
	}

	$this->data['title'] = translate('RDC - Dashboard');
	$this->data['sub_page'] = 'rdc/dashboard';
	$this->data['main_menu'] = 'dashboard';
	$this->load->view('layout/index', $this->data);
}

public function reports()
{
	if (!get_permission('rdc_management', 'is_view')) {
		access_denied();
	}

	$start_date = '';
	$end_date = '';

	if ($this->input->post('search')) {
		$daterange = explode(' - ', $this->input->post('daterange'));
		$start_date = date("Y-m-d", strtotime($daterange[0]));
		$end_date = date("Y-m-d", strtotime($daterange[1]));
	}

	// Get pending tasks report data
	$this->data['pending_tasks_report'] = $this->rdc_model->getPendingTasksReport($start_date, $end_date);
	$this->data['start_date'] = $start_date;
	$this->data['end_date'] = $end_date;

	$this->data['headerelements'] = array(
		'css' => array(
			'vendor/daterangepicker/daterangepicker.css',
		),
		'js' => array(
			'vendor/moment/moment.js',
			'vendor/daterangepicker/daterangepicker.js',
		),
	);

	$this->data['title'] = translate('RDC - Pending Tasks Report');
	$this->data['sub_page'] = 'rdc/reports';
	$this->data['main_menu'] = 'rdc_reports';
	$this->load->view('layout/index', $this->data);
}

	// AJAX endpoint for RDC task approval
	public function rdc_ajax()
	{
		header('Content-Type: application/json');

		if (!get_permission('rdc_management', 'is_edit')) {
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}

		try {
			$task_id = $this->input->post('id');
			$sop_verifier = $this->input->post('verifier_role');
			$current_user_id = get_loggedin_user_id();
			$role_id = loggedin_role_id();

			// Get task
			$task_rdc = $this->db->get_where('rdc_task', ['id' => $task_id])->row();

			if (!$task_rdc) {
				echo json_encode(['status' => 'error', 'message' => 'Task not found']);
				return;
			}

			$isEmployee = ($current_user_id == $task_rdc->assigned_user);

			// Determine if verifier
			$verifier_roles = explode(',', $sop_verifier ?? '');
			$isVerifier = false;
			if (in_array($role_id, $verifier_roles)) {
				/* if ($role_id == 8) {
					$assigned_dept = $this->db->select('department')->where('id', $task_rdc->assigned_user)->get('staff')->row('department');
					$isVerifier = $this->db->where(['id' => $current_user_id, 'department' => $assigned_dept])->get('staff')->num_rows() > 0;
				} else {
					$isVerifier = true;
				} */
				$isVerifier = true;
			}

			// Prevent unauthorized access
			if (!$isEmployee && !$isVerifier) {
				echo json_encode(['status' => 'error', 'message' => 'Access denied']);
				return;
			}

			// Handle Executor (Employee) Submission
			if ($isEmployee && get_permission('rdc_management', 'is_edit')) {
				$executorData = [
					'task_status'          => $this->input->post('task_status'),
					'executor_explanation' => $this->input->post('executor_explanation', false),
					'exe_cleared_on'       => date('Y-m-d H:i:s'),
				];

				// Proof Text
				$proof_text = $this->input->post('proof_text');
				if (!empty($proof_text)) {
					$executorData['proof_text'] = $proof_text;
				}

				// Executor Checklist Save
				$executorChecklist = $this->input->post('executor_checklist');
				if (is_array($executorChecklist)) {
					$executor_stage = json_decode($task_rdc->executor_stage ?? '{}', true);
					$executor_stage['completed'] = $executorChecklist;
					$executorData['executor_stages'] = json_encode($executor_stage);
				}

				// Handle file uploads
				if (isset($_FILES["proof_image"]) && !empty($_FILES['proof_image']['name'])) {
					if (!is_dir('./uploads/attachments/rdc_proofs/')) {
						mkdir('./uploads/attachments/rdc_proofs/', 0777, TRUE);
					}

					$config['upload_path'] = './uploads/attachments/rdc_proofs/';
					$config['allowed_types'] = "*";
					$config['max_size'] = '2024';
					$config['encrypt_name'] = true;

					$this->upload->initialize($config);
					if ($this->upload->do_upload("proof_image")) {
						$enc_file_name = $this->upload->data('file_name');
						$executorData['proof_image'] = $enc_file_name;
					} else {
						$error = $this->upload->display_errors();
						echo json_encode(['status' => 'error', 'message' => $error]);
						return;
					}
				}

				if (isset($_FILES["proof_file"]) && !empty($_FILES['proof_file']['name'])) {
					if (!is_dir('./uploads/attachments/rdc_proofs/')) {
						mkdir('./uploads/attachments/rdc_proofs/', 0777, TRUE);
					}

					$config['upload_path'] = './uploads/attachments/rdc_proofs/';
					$config['allowed_types'] = "*";
					$config['max_size'] = '2024';
					$config['encrypt_name'] = true;

					$this->upload->initialize($config);
					if ($this->upload->do_upload("proof_file")) {
						$enc_file_name = $this->upload->data('file_name');
						$executorData['proof_file'] = $enc_file_name;
					} else {
						$error = $this->upload->display_errors();
						echo json_encode(['status' => 'error', 'message' => $error]);
						return;
					}
				}

				$this->db->where('id', $task_id)->update('rdc_task', $executorData);
			}

			// Handle Verifier Submission
			if ($isVerifier && get_permission('rdc_management', 'is_edit')) {
				$verifierData = [
					'verify_status'        => $this->input->post('verify_status'),
					'verifier_explanation' => $this->input->post('verifier_explanation', false),
					'ver_cleared_on'       => date('Y-m-d H:i:s'),
					'verified_by'          => $current_user_id,
				];

				// Verifier Checklist Save
				$verifierChecklist = $this->input->post('verifier_checklist');
				if (is_array($verifierChecklist)) {
					$verifier_stage = json_decode($task_rdc->verifier_stage ?? '{}', true);
					$verifier_stage['completed'] = $verifierChecklist;
					$verifierData['verifier_stages'] = json_encode($verifier_stage);
				}

				$this->db->where('id', $task_id)->update('rdc_task', $verifierData);
			}

			// Get updated task data
			$updated_task = $this->db->get_where('rdc_task', ['id' => $task_id])->row();

			echo json_encode([
				'status' => 'success',
				'message' => 'Task updated successfully',
				'task_status' => $updated_task->task_status,
				'verify_status' => $updated_task->verify_status
			]);

		} catch (Exception $e) {
			echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
		}
	}

}
