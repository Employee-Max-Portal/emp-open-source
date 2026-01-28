<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Separation extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('email_model');

        $this->uploadDir = FCPATH . 'uploads/notifications/';
        $this->serviceAccountPath = FCPATH . 'assets/notification/service-account.json';
        $this->logFile = FCPATH . 'assets/notification/logs/fcm_notifications.log';
    }

	public function index()
	{
		// check access permission
		if (!get_permission('separation', 'is_add')) {
			access_denied();
		}

		// handle form submission
		if ($this->input->post('save')) {
			if (!get_permission('separation', 'is_add')) {
				access_denied();
			}
				// input fields
				$title       = 'Resignation Letter ';
				$reason      = $this->input->post('reason');
				$last_working_date = $this->input->post('last_working_date');
				$branch_id   = $this->application_model->get_branch_id();
				$created_at  = date("Y-m-d H:i:s");

				// handle file upload
				$orig_file_name = '';
				$enc_file_name  = '';
				if (isset($_FILES["attachment_file"]) && !empty($_FILES['attachment_file']['name'])) {
					$config['upload_path']      = './uploads/attachments/separation/';
					$config['allowed_types']    = "*";
					$config['max_size']         = '2024';
					$config['encrypt_name']     = true;

					$this->upload->initialize($config);
					if ($this->upload->do_upload("attachment_file")) {
						$orig_file_name = $this->upload->data('orig_name');
						$enc_file_name  = $this->upload->data('file_name');
					}
				}

				// insert into DB
				$data = array(
					'user_id'         => get_loggedin_user_id(),
					'role_id'         => loggedin_role_id(),
					'session_id'      => get_session_id(),
					//'branch_id'       => $branch_id,
					'title'           => $title,
					'reason'          => $reason,
					'last_working_date' => date("Y-m-d", strtotime($last_working_date)),
					'status'          => 1, // pending
					'orig_file_name'  => $orig_file_name,
					'enc_file_name'   => $enc_file_name,
					'created_at'      => $created_at,
					'email_sent' => 0,
				);

				$this->db->insert('separation_requests', $data);
				$separation_id = $this->db->insert_id();

				// ✅ Insert notification for the employee
				$staff = $this->db->get_where('staff', ['id' => get_loggedin_user_id()])->row();
				$staff_name = $staff ? $staff->name : 'Staff';

				$notificationData = array(
					'user_id'    => get_loggedin_user_id(),
					'type'       => 'separation',
					'title'      => 'Separation Request Submitted by ' . $staff_name,
					'message' 	 => 'Dear Concern, ' . $staff_name . ' has submitted a resignation letter and it is pending review.',
					'url'        => base_url('separation/lists'),
					'is_read'    => 0,
					'created_at' => date('Y-m-d H:i:s')
				);

				$this->db->insert('notifications', $notificationData);

				// Get staff details for FCM notification
				$staff_details = $this->db->select('id, name, department')
				                          ->get_where('staff', ['id' => get_loggedin_user_id()])
				                          ->row();

				// Build FCM notification
				$title = 'New Separation Request';
				$who = $staff_details ? $staff_details->name : 'An employee';
				$working_date = date('j M Y', strtotime($last_working_date));

				$body = sprintf(
				    '%s submitted a resignation letter with last working date %s.',
				    $who,
				    $working_date
				);

				// Get approver tokens (HR/Admin roles for separation requests)
				$recipientTokens = $this->get_fund_approver_tokens(
				    $staff_details ? $staff_details->department : null,
				    [2, 3, 5], // Admin, HR roles for separation
				    8
				);

				// Send FCM notification
				if (!empty($recipientTokens)) {
				    $this->send_fcm_notification($title, $body, '', $recipientTokens, [
				        'type'           => 'separation_request',
				        'separation_id'  => (string)$separation_id,
				        'staff_id'       => (string)get_loggedin_user_id(),
				        'action'         => 'review'
				    ]);
				} else {
				    $this->log_message("INFO: No recipient FCM tokens found for separation_id={$separation_id}");
				}

				set_alert('success', translate('information_has_been_saved_successfully'));
				redirect(base_url('separation'));

		}

				if (loggedin_role_id() == 1 || loggedin_role_id() == 2) {
				$this->data['separationList'] = $this->db->order_by('id', 'desc')->get('separation_requests')->result();
			} else {
				$where = array('user_id' => get_loggedin_user_id());
				$this->data['separationList'] = $this->db->where($where)->order_by('id', 'desc')->get('separation_requests')->result();
			}


		// view rendering
		$this->data['title'] = translate('separation_requests');
		$this->data['sub_page'] = 'separation/index';
		$this->data['main_menu'] = 'separation';
		$this->data['headerelements'] = array(
			'css' => array(
				'vendor/dropify/css/dropify.min.css',
				'vendor/datepicker/datepicker3.css',
			),
			'js' => array(
				'vendor/dropify/js/dropify.min.js',
				'vendor/datepicker/bootstrap-datepicker.js',
			),
		);
		$this->load->view('layout/index', $this->data);
	}

	 public function getRequestDetails()
    {
        $this->data['request_id'] = $this->input->post('id');
        $this->load->view('separation/modal_request_details', $this->data);
    }

	 public function request_delete($id = '')
    {
        $where = array(
            'status' => 1,
            'user_id' => get_loggedin_user_id(),
            'role_id' => loggedin_role_id(),
            'id' => $id,
        );
        $app = $this->db->where($where)->get('separation_requests')->row_array();
        $file_name = FCPATH . 'uploads/attachments/separation/' . $app['enc_file_name'];
        if (file_exists($file_name)) {
            unlink($file_name);
        }
        $this->db->where($where)->delete('separation_requests');
    }

	 public function delete($id = '')
    {
        $where = array(
            'id' => $id,
        );
        $app = $this->db->where($where)->get('separation_requests')->row_array();
        $file_name = FCPATH . 'uploads/attachments/separation/' . $app['enc_file_name'];
        if (file_exists($file_name)) {
            unlink($file_name);
        }
        $this->db->where($where)->delete('separation_requests');
    }



	public function lists()
	{
		if (!get_permission('separation', 'is_view')) {
			access_denied();
		}

		// Update status and comments
		if (isset($_POST['update'])) {
			if (!get_permission('separation', 'is_add')) {
				access_denied();
			}
			$issued_by = get_loggedin_user_id();
			$updateData = array(
				'approved_by' => $issued_by,
				'status' => $this->input->post('status'),
				'comments' => $this->input->post('comments'),
			);


			$id = $this->input->post('id');
			$this->db->where('id', $id);
			$this->db->update('separation_requests', $updateData);


			// ✅ Fetch updated data for notification
			$request = $this->db->get_where('separation_requests', ['id' => $id])->row();

			if ($request) {
				$userID = $request->user_id;
				$status = $this->input->post('status');

				// Get status label
				$status_label = '';
				switch ($status) {
					case 1:
						$status_label = 'Pending';
						break;
					case 2:
						$status_label = 'Approved';
						break;
					case 3:
						$status_label = 'Rejected';
						break;
				}

				// Get staff name
				$staff = $this->db->get_where('staff', ['id' => $userID])->row();
				$issuer = $this->db->get_where('staff', ['id' => $issued_by])->row();
				$staff_name = $staff ? $staff->name : 'Staff';
				$issuer_name = $issuer ? $issuer->name : 'Admin';

				// Notification
				$message = 'Dear ' . $staff_name . ', your resignation request has been ' . strtolower($status_label) . ' by ' . $issuer_name . '.';
				$notificationData = array(
					'user_id'    => $userID,
					'type'       => 'separation',
					'title'      => 'Separation Request ' . $status_label,
					'message'    => $message,
					'url'        => base_url('separation'),
					'is_read'    => 0,
					'created_at' => date('Y-m-d H:i:s')
				);

				$this->db->insert('notifications', $notificationData);

				// Send FCM notification to applicant
				$applicant_tokens = $this->db->select('fcm_token')
				                             ->where('id', $userID)
				                             ->where('fcm_token IS NOT NULL')
				                             ->where('fcm_token !=', '')
				                             ->get('staff')
				                             ->result_array();

				if (!empty($applicant_tokens)) {
				    $tokens = array_column($applicant_tokens, 'fcm_token');
				    $this->send_fcm_notification('Separation Request ' . $status_label, $message, '', $tokens, [
				        'type'           => 'separation_status_update',
				        'separation_id'  => (string)$id,
				        'status'         => (string)$status,
				        'action'         => 'view'
				    ]);
				}
			}

			set_alert('success', translate('information_has_been_updated_successfully'));
			redirect(base_url('separation/lists'));
		}

		// Search filter (branch, role)
		$branch_id = $this->input->post('branch_id');
		$role_id = $this->input->post('role_id');

		$logged_in_user_id = get_loggedin_user_id();
		$logged_in_role_id = loggedin_role_id();

		// 🔹 Get HOD's department
		$hod_department = '';
		if ($logged_in_role_id == 8) {
			$hod_department = $this->db->select('department')
				->where('id', $logged_in_user_id)
				->get('staff')
				->row('department');
		}

		$this->db->select('separation_requests.*, login_credential.role, roles.name as role_name');
		$this->db->from('separation_requests');
		$this->db->join('login_credential', 'login_credential.user_id = separation_requests.user_id AND login_credential.role = separation_requests.role_id', 'left');
		$this->db->join('roles', 'roles.id = separation_requests.role_id', 'left');
		$this->db->join('staff', 'staff.id = separation_requests.user_id', 'left');

		// 🧩 Join staff table only if filtering by department
		if ($logged_in_role_id == 8 && !empty($hod_department)) {
			$this->db->where('staff.department', $hod_department);
		}

		// ✅ Role filter
		if (!empty($role_id) && $role_id !== 'all') {
			$this->db->where('separation_requests.role_id', $role_id);
		}

		// ✅ Branch filter
		if (!empty($branch_id) && $branch_id !== 'all') {
			$this->db->where('staff.branch_id', $branch_id);
		}

		// ✅ Final ordering and fetch
		$this->db->order_by('separation_requests.id', 'DESC');
		$this->data['separationList'] = $this->db->get()->result();


		// Page setup
		$this->data['main_menu'] = 'separation';
		$this->data['sub_page'] = 'separation/lists';
		$this->data['title'] = translate('separation_lists');
		$this->data['headerelements'] = array(
			'css' => array(
				'vendor/dropify/css/dropify.min.css',
				'vendor/daterangepicker/daterangepicker.css',
			),
			'js' => array(
				'vendor/dropify/js/dropify.min.js',
				'vendor/moment/moment.js',
				'vendor/daterangepicker/daterangepicker.js',
			),
		);

		$this->load->view('layout/index', $this->data);
	}

	// get add leave modal
    public function getApprovelLeaveDetails()
    {
        if (get_permission('separation', 'is_add')) {
            $this->data['request_id'] = $this->input->post('id');
            $this->load->view('separation/approvel_modalView', $this->data);
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

            $this->db->select('orig_file_name,enc_file_name');
            $this->db->where('id', $id);
            $leave = $this->db->get('separation_requests')->row();
            if ($file != $leave->enc_file_name) {
                access_denied();
            }
            $this->load->helper('download');
            $fileData = file_get_contents('./uploads/attachments/separation/' . $leave->enc_file_name);
            force_download($leave->orig_file_name, $fileData);
        }
    }

    /**
     * Get FCM tokens for fund requisition approvers
     */
    private function get_fund_approver_tokens(
        $departmentId,
        array $alwaysRoles = [2, 5],
        $managerRole = 8,
        $includeDepartmentAll = true
    ) {
        if (empty($departmentId)) {
            $includeDepartmentAll = false;
        }

        $this->db->distinct();
        $this->db->select('s.fcm_token')
                 ->from('staff s')
                 ->join('login_credential lc', 'lc.user_id = s.id', 'inner')
                 ->group_start()
                     ->where_in('lc.role', $alwaysRoles)
                 ->group_end();

        if ($includeDepartmentAll) {
            $this->db->or_group_start()
                         ->where('s.department', $departmentId)
                     ->group_end();
        } else {
            $this->db->or_group_start()
                         ->where('s.department', $departmentId)
                         ->where('lc.role', $managerRole)
                     ->group_end();
        }

        $this->db->where('s.fcm_token IS NOT NULL', null, false)
                 ->where('s.fcm_token !=', '');

        $rows = $this->db->get()->result_array();

        $tokens = [];
        foreach ($rows as $r) {
            $t = $r['fcm_token'] ?? '';
            if ($t !== '') $tokens[$t] = true;
        }
        return array_keys($tokens);
    }

    /**
     * Send FCM notification to specific tokens
     */
    public function send_fcm_notification($title, $text, $image = '', $tokens = null, array $extraData = [])
    {
        $this->log_message("Starting FCM notification send process");

        if ($tokens === null) {
            $tokens = $this->db->select('fcm_token')
                ->where('fcm_token IS NOT NULL', null, false)
                ->where('fcm_token !=', '')
                ->get('staff')
                ->result_array();
            $tokens = array_map(function($r){ return $r['fcm_token']; }, $tokens);
        }

        if (empty($tokens)) {
            $this->log_message("ERROR: No FCM tokens to send to");
            return false;
        }

        $this->log_message("Prepared " . count($tokens) . " FCM tokens");

        $accessToken = $this->get_access_token();
        $projectId   = 'emp-app-f5a2d';

        $successCount = 0;
        $failureCount = 0;

        $dataPayload = array_merge([
            "title" => (string)$title,
            "body"  => (string)$text,
            "image" => (string)$image,
            "click_action" => "FLUTTER_NOTIFICATION_CLICK"
        ], array_map('strval', $extraData));

        foreach ($tokens as $fcmToken) {
            $message = [
                "message" => [
                    "token" => $fcmToken,
                    "notification" => [
                        "title" => $title,
                        "body"  => $text
                    ],
                    "data" => $dataPayload
                ]
            ];

            if (!empty($image)) {
                $message["message"]["android"] = [
                    "notification" => [ "image" => $image ]
                ];
                $message["message"]["apns"] = [
                    "payload" => [ "aps" => [ "mutable-content" => 1 ] ],
                    "fcm_options" => [ "image" => $image ]
                ];
                $message["message"]["webpush"] = [
                    "headers" => [ "image" => $image ]
                ];
            }

            $headers = [
                "Authorization: Bearer $accessToken",
                "Content-Type: application/json; UTF-8"
            ];

            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            $this->log_message("Sending to token: " . substr($fcmToken, 0, 20) . "... with payload: " . json_encode($message));

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode == 200) {
                $successCount++;
                $this->log_message("SUCCESS: Notification sent to token: " . substr($fcmToken, 0, 20) . "...");
            } else {
                $failureCount++;
                $this->log_message("ERROR: Failed to send to token: " . substr($fcmToken, 0, 20) . "... HTTP Code: $httpCode Response: $response");

                if (strpos($response, 'UNREGISTERED') !== false) {
                    $this->db->where('fcm_token', $fcmToken)->update('staff', ['fcm_token' => NULL]);
                    $this->log_message("INFO: Removed UNREGISTERED token from DB: " . substr($fcmToken, 0, 20) . "...");
                }
            }
        }
        $this->log_message("FCM send completed - Success: $successCount, Failures: $failureCount");
        return ($successCount > 0);
    }

    private function get_access_token()
    {
        $this->log_message("Getting OAuth2 access token");

        $serviceAccount = json_decode(file_get_contents($this->serviceAccountPath), true);

        $jwt = $this->create_jwt($serviceAccount);

        $postData = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $serviceAccount['token_uri']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

        $response = curl_exec($ch);
        curl_close($ch);

        $tokenData = json_decode($response, true);

        if (isset($tokenData['access_token'])) {
            $this->log_message("OAuth2 access token obtained successfully");
            return $tokenData['access_token'];
        } else {
            $this->log_message("ERROR: Failed to get access token - Response: $response");
            return null;
        }
    }

    private function create_jwt($serviceAccount)
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'RS256']);
        $now = time();
        $payload = json_encode([
            'iss' => $serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => $serviceAccount['token_uri'],
            'exp' => $now + 3600,
            'iat' => $now
        ]);

        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = '';
        openssl_sign($base64Header . '.' . $base64Payload, $signature, $serviceAccount['private_key'], OPENSSL_ALGO_SHA256);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64Header . '.' . $base64Payload . '.' . $base64Signature;
    }

    private function log_message($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] $message" . PHP_EOL;

        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        if (file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX) === false) {
            error_log("FCM Log: $message");
        }
    }

}
