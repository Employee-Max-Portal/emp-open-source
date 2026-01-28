<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sop extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('sop_model');
        $this->load->library('ciqrcode', array('cacheable' => false));
        $this->load->model('employee_model');
        if (!moduleIsEnabled('certificate')) {
            access_denied();
        }
    }

    /* live class form validation rules */
    protected function sop_validation()
    {
        $this->form_validation->set_rules('title', translate('title'), 'trim|required');
        $this->form_validation->set_rules('task_purpose', translate('task_purpose'), 'trim|required');
        $this->form_validation->set_rules('instructions', translate('instructions'), 'trim|required');
    }
	public function index()
	{
		if (!get_permission('sop_management', 'is_view')) {
			access_denied();
		}

		if ($_POST) {
			if (get_permission('sop_management', 'is_add')) {

				 $this->sop_validation();
					if ($this->form_validation->run() !== false) {
						// SAVE INFORMATION IN THE DATABASE FILE
						$this->sop_model->save($this->input->post());
						set_alert('success', translate('information_has_been_saved_successfully'));
						$array = array('status' => 'success');
					} else {
						$error = $this->form_validation->error_array();
						$array = array('status' => 'fail', 'error' => $error);
					}
					echo json_encode($array);
					redirect($_SERVER['HTTP_REFERER']); // Or your preferred redirect

			}
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
		$this->data['branch_id'] = $this->application_model->get_branch_id();
		$this->data['list'] = $this->sop_model->getList();
		$this->data['verifier'] = $this->sop_model->getVerifier();
		/* print_r ($this->data['verifier']);
		die(); */
		$this->data['title'] = translate('SOP') . " " . translate('(Standard Operating Procedure)');
		$this->data['sub_page'] = 'sop/index';
		$this->data['main_menu'] = 'sop';
		$this->load->view('layout/index', $this->data);
	}

	public function create()
	{
		if (!get_permission('sop_management', 'is_add')) {
			access_denied();
		}

		if ($_POST) {

			$this->sop_validation();
			if ($this->form_validation->run() !== false) {
				// SAVE INFORMATION IN THE DATABASE FILE
				$this->sop_model->save($this->input->post());
				set_alert('success', translate('information_has_been_saved_successfully'));
				$array = array('status' => 'success');
			} else {
				$error = $this->form_validation->error_array();
				$array = array('status' => 'fail', 'error' => $error);
			}
			echo json_encode($array);
			redirect(base_url('sop'));

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
		$this->data['verifier'] = $this->sop_model->getVerifier();
		$this->data['title'] = translate('SOP') . " " . translate('(Standard Operating Procedure)');
		$this->data['sub_page'] = 'sop/create';
		$this->data['main_menu'] = 'sop';
		$this->load->view('layout/index', $this->data);
	}


	public function getDetailed()
    {
        if (get_permission('sop_management', 'is_view')) {
            $this->data['request_id'] = $this->input->post('id');
            $this->load->view('sop/viewTemplete', $this->data);
        }
    }

    public function edit($id = '')
    {
        if (!get_permission('sop_management', 'is_edit')) {
            access_denied();
        }
        if ($_POST) {
            $this->sop_validation();
			if ($this->form_validation->run() !== false) {
				// SAVE INFORMATION IN THE DATABASE FILE
				$this->sop_model->save($this->input->post());
				set_alert('success', translate('information_has_been_saved_successfully'));
				$array = array('status' => 'success');
			} else {
				$error = $this->form_validation->error_array();
				$array = array('status' => 'fail', 'error' => $error);
			}
			echo json_encode($array);
			redirect($_SERVER['HTTP_REFERER']); // Or your preferred redirect
        }
        $this->data['sop_list'] = $this->app_lib->get_table('sop', $id, true);

		$this->data['verifier'] = $this->sop_model->getVerifier();
        $this->data['title'] = translate('sop') . " " . translate('templete');
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
        $this->data['sub_page'] = 'sop/edit';
        $this->data['main_menu'] = 'sop';
        $this->load->view('layout/index', $this->data);
    }

    public function delete($id = '')
    {
       if (!get_permission('sop_management', 'is_delete')) {
			access_denied();
		}
		// Optional: Fetch snapshot before delete
		$snapshot = $this->db->get_where('sop', ['id' => $id])->row_array();

		$this->db->where('id', $id)->delete('sop');

		// Log the deletion
		$this->sop_model->log_action($id, 'delete', $snapshot);
    }

	public function logs()
	{
		if (!get_permission('sop_management', 'is_view')) {
			access_denied();
		}
		$this->data['title'] = translate('sop') . " " . translate('logs');
		$this->data['logs'] = $this->db->order_by('id', 'DESC')->get('sop_log')->result_array();
		$this->data['sub_page'] = 'sop/logs';
		$this->data['main_menu'] = 'sop';
		$this->load->view('layout/index', $this->data);
	}

	public function getLogDetail()
	{
	if ($this->input->post('id')) {
		$log = $this->db->get_where('sop_log', ['id' => $this->input->post('id')])->row_array();
		if (!$log) return;

		$data = json_decode($log['data_snapshot'], true);
		if (!is_array($data)) $data = [];

		// Get staff name
		$staff = $this->db->get_where('staff', ['id' => $log['staff_id']])->row();
		$staff_name = $staff ? $staff->name : 'Unknown';

		// Get SOP title (for modal header)
		$title = '-';
		if ($log['action'] == 'update' && isset($data['title']['new'])) {
			$title = $data['title']['new'];
		} elseif (isset($data['title'])) {
			$title = is_array($data['title']) ? $data['title']['new'] : $data['title'];
		}

		echo '<div class="panel-heading"><h4 class="panel-title">';
		echo '<i class="fas fa-info-circle"></i> ' . translate('log_details') . ' - <strong>' . html_escape($log['action']) . '</strong>';
		echo '</h4></div>';

		echo '<div class="panel-body">';
		echo '<table class="table table-striped table-bordered">';
		echo '<tr><th>' . translate('action_by') . '</th><td>' . html_escape($staff_name) . '</td></tr>';
		echo '<tr><th>' . translate('action') . '</th><td>' . ucfirst($log['action']) . '</td></tr>';
		echo '<tr><th>' . translate('timestamp') . '</th><td>' . date('d M Y h:i A', strtotime($log['created_at'])) . '</td></tr>';

		if ($log['action'] == 'update') {
			foreach ($data as $key => $val) {
				if (!is_array($val) || !isset($val['old']) || !isset($val['new'])) continue;

				$label = ucwords(str_replace('_', ' ', $key));

				if ($key == 'verifier_role') {
					$old_roles = explode(',', $val['old']);
					$new_roles = explode(',', $val['new']);

					$old_names = [];
					foreach ($old_roles as $rid) {
						$role = $this->db->get_where('roles', ['id' => $rid])->row();
						if ($role) $old_names[] = $role->name;
					}

					$new_names = [];
					foreach ($new_roles as $rid) {
						$role = $this->db->get_where('roles', ['id' => $rid])->row();
						if ($role) $new_names[] = $role->name;
					}

					echo "<tr><th>$label</th><td><strong>Old:</strong> " . implode(', ', $old_names) . "<br><strong>New:</strong> " . implode(', ', $new_names) . "</td></tr>";
				} else {
					if ($key === 'instructions') {
						echo "<tr><th>$label</th><td><strong>Old:</strong><br>" . $val['old'] . "<br><strong>New:</strong><br>" . $val['new'] . "</td></tr>";
					} else {
						echo "<tr><th>$label</th><td><strong>Old:</strong> " . html_escape($val['old']) . "<br><strong>New:</strong> " . html_escape($val['new']) . "</td></tr>";
					}

				}
			}
		} else {
			// For create/delete logs
			$proof = [];
			if (!empty($data['proof_required_text'])) $proof[] = translate('text');
			if (!empty($data['proof_required_image'])) $proof[] = translate('image');
			if (!empty($data['proof_required_file'])) $proof[] = translate('file');

			foreach ($data as $key => $value) {
				if (in_array($key, ['proof_required_text', 'proof_required_image', 'proof_required_file'])) continue;

				if ($key == 'verifier_role') {
					$role_ids = explode(',', $value);
					$role_names = [];
					foreach ($role_ids as $rid) {
						$role = $this->db->get_where('roles', ['id' => $rid])->row();
						if ($role) $role_names[] = $role->name;
					}
					echo '<tr><th>' . translate('verifier_role') . '</th><td>' . implode(', ', $role_names) . '</td></tr>';
				} elseif ($key == 'expected_time') {
					echo '<tr><th>' . translate('expected_time') . '</th><td>' . $value . ' ' . translate('hours') . '</td></tr>';
				} elseif ($key == 'created_by') {
					$creator = $this->db->get_where('staff', ['id' => $value])->row();
					echo '<tr><th>' . translate('created_by') . '</th><td>' . ($creator ? $creator->name : 'Unknown') . '</td></tr>';
				} else {
					$label = ucwords(str_replace('_', ' ', $key));
					if ($key === 'instructions') {
						echo '<tr><th>' . $label . '</th><td>' . $value . '</td></tr>'; // render as HTML
					} else {
						echo '<tr><th>' . $label . '</th><td>' . html_escape($value) . '</td></tr>';
					}

				}
			}

			if (!empty($proof)) {
				echo '<tr><th>' . translate('proof_required') . '</th><td>' . implode(', ', $proof) . '</td></tr>';
			}
		}

		echo '</table>';
		echo '<div class="text-right mt-md">';
		echo '<button type="button" class="btn btn-default" onclick="$.magnificPopup.close();">';
		echo '<i class="fas fa-times-circle"></i> ' . translate('close') . '</button>';
		echo '</div>';
		echo '</div>';
	}
}


    public function printFn($opt = '')
    {
        if ($_POST) {
            if ($opt == 1) {
                if (!get_permission('generate_student_certificate', 'is_view')) {
                    ajax_access_denied();
                }
            } elseif ($opt == 2) {
                if (!get_permission('generate_employee_certificate', 'is_view')) {
                    ajax_access_denied();
                }
            } else {
                ajax_access_denied();
            }

            //get all QR Code file
            $files = glob('uploads/qr_code/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file); //delete file
                }
            }

            $this->data['user_type'] = $opt;
            $this->data['user_array'] = $this->input->post('user_id');
            $templateID = $this->input->post('templete_id');
            $this->data['template'] = $this->sop_model->get('certificates_templete', array('id' => $templateID), true);
            $this->data['student_array'] = $this->input->post('student_id');
            $this->data['print_date'] = $this->input->post('print_date');
            echo $this->load->view('certificate/printFn', $this->data, true);
        }
    }

    // get templete list based on the branch
    public function getTempleteByBranch()
    {
        $html = "";
        $branchID = $this->application_model->get_branch_id();
        $userType = $this->input->post('user_type');
        if ($userType == 'student') {
            $userType = 1;
        }
        if ($userType == 'staff') {
            $userType = 2;
        }
        if (!empty($branchID)) {
            $this->db->select('id,name');
            $this->db->where(array('branch_id' => $branchID, 'user_type' => $userType));
            $result = $this->db->get('certificates_templete')->result_array();
            if (count($result)) {
                $html .= '<option value="">' . translate('select') . '</option>';
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
}