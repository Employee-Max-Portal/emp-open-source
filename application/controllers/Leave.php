<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Leave extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('leave_model');
        $this->load->model('email_model');

        $this->uploadDir = FCPATH . 'uploads/notifications/';
        $this->serviceAccountPath = FCPATH . 'assets/notification/service-account.json';
        $this->logFile = FCPATH . 'assets/notification/logs/fcm_notifications.log';
    }

    public function index()
    {
        if (!get_permission('leave_manage', 'is_view')) {
            access_denied();
        }

        if (isset($_POST['update'])) {
            if (!get_permission('leave_manage', 'is_add')) {
                access_denied();
            }
			$issued_by = get_loggedin_user_id();
            $arrayLeave = array(
                'approved_by' => $issued_by,
                'status'    => $this->input->post('status'),
                'comments'  => $this->input->post('comments'),
            );
            $id = $this->input->post('id');
            $this->db->where('id', $id);
            $this->db->update('leave_application', $arrayLeave);

			// 2. Fetch leave details
			$leave_status = $this->input->post('status');

			$status_label = '';
			if ($leave_status == 1) {
				$status_label = 'Pending';
			} elseif ($leave_status == 2) {
				$status_label = 'Approved';
			} elseif ($leave_status == 3) {
				$status_label = 'Rejected';
			}

			$leave = $this->db->get_where('leave_application', ['id' => $id])->row();
			if ($leave) {
				$userID = $leave->user_id;
				$leave_type_id = $leave->category_id;
				$leave_days = $leave->leave_days;

				// 3. Fetch category and staff
				$leave_category = $this->db->get_where('leave_category', ['id' => $leave_type_id])->row();
				$staff = $this->db->get_where('staff', ['id' => $userID])->row();
				$issuer = $this->db->get_where('staff', ['id' => $issued_by])->row();

				$leave_title = $leave_category ? $leave_category->name : 'Leave';
				$staff_name = $staff ? $staff->name : 'Staff';
				$issuer_name = $issuer ? $issuer->name : 'Admin';

				// 4. Build message
				$message = 'Dear ' . $staff_name . ', your ' . $leave_days . ' day' . ($leave_days > 1 ? 's' : '') . ' ' . $leave_title . ' has been ' . $status_label . ' by ' . $issuer_name . '.';

				// 5. Insert notification
				$notificationData = array(
					'user_id'    => $userID,
					'type'       => 'leave',
					'title'      => 'Leave Request ' . $status_label,
					'message'    => $message,
					'url'        => base_url('leave/request'),
					'is_read'    => 0,
					'created_at' => date('Y-m-d H:i:s')
				);

				$this->db->insert('notifications', $notificationData);

				// Send FCM notification to leave applicant
				$applicant_tokens = $this->db->select('fcm_token')
				                             ->where('id', $userID)
				                             ->where('fcm_token IS NOT NULL')
				                             ->where('fcm_token !=', '')
				                             ->get('staff')
				                             ->result_array();

				if (!empty($applicant_tokens)) {
				    $tokens = array_column($applicant_tokens, 'fcm_token');
				    $fcm_title = 'Leave Request ' . $status_label;
				    $this->send_fcm_notification($fcm_title, $message, '', $tokens, [
				        'type'      => 'leave_status_update',
				        'leave_id'  => (string)$id,
				        'status'    => (string)$leave_status,
				        'action'    => 'view'
				    ]);
				}
			}

            set_alert('success', translate('information_has_been_updated_successfully'));
            redirect(base_url('leave'));
        }

		$staffID = get_loggedin_user_id();
		$roleID = loggedin_role_id();

		if ($this->input->post('search')) {
			//$this->data['branch_id'] = $this->application_model->get_branch_id();
			$this->data['branch_id'] = $this->input->post('branch_id');

			// Only pass staffID if user is not privileged
			$limit_to_staff = !in_array($roleID, [1, 2, 3, 5]) ? $staffID : null;

			$this->data['leavelist'] = $this->leave_model->getLeaves(
				$this->data['branch_id'],
				$limit_to_staff
			);
		} else {
			$limit_to_staff = !in_array(loggedin_role_id(), [1, 2, 3, 5]) ? $staffID : null;
			$this->data['leavelist'] = $this->leave_model->getLeaves(null, null, $limit_to_staff);
		}

		$leave_user_cards = [];
		$logged_in_user_id = get_loggedin_user_id();
		$logged_in_role_id = loggedin_role_id();
		$branchID = get_loggedin_branch_id();
		$view_all_roles = [2, 3, 5]; // HR/Admin roles

		// 🧭 Get relevant employees
		if ($logged_in_role_id == 1) {
			$employees = $this->db->select('staff.*, login_credential.username')
				->from('staff')
				->join('login_credential', 'login_credential.user_id = staff.id')
				->where('login_credential.active', 1)
				->where('staff.id !=', 1)
                ->where_not_in('login_credential.role', [1, 9, 10, 11, 12])
				->get()
				->result();
		} elseif (in_array($logged_in_role_id, $view_all_roles)) {
			$employees = $this->db->select('staff.*, login_credential.username')
				->from('staff')
				->join('login_credential', 'login_credential.user_id = staff.id')
				->where('login_credential.active', 1)
				->where_not_in('login_credential.role', [1, 9, 10, 11, 12])
				//->where('staff.branch_id', $branchID)
				->where('staff.id !=', 1)
				->get()
				->result();
		} elseif ($logged_in_role_id == 8) {
			$hod_department = $this->db->select('department')
				->where('id', $logged_in_user_id)
				->get('staff')
				->row('department');

			if (!empty($hod_department)) {
				$employees = $this->db->select('staff.*, login_credential.username')
					->from('staff')
					->join('login_credential', 'login_credential.user_id = staff.id')
					->where('login_credential.active', 1)
					->where('staff.branch_id', $branchID)
					->where('staff.department', $hod_department)
					->get()
					->result();
			}
		} else {
			$employee = $this->db->select('staff.*, login_credential.username')
				->from('staff')
				->join('login_credential', 'login_credential.user_id = staff.id')
				->where('staff.id', $logged_in_user_id)
				->where('login_credential.active', 1)
				->get()
				->row();
			if ($employee) $employees = [$employee];
		}

		// 🧮 Loop employees and calculate balances
		foreach ($employees as $employee) {
			if (!$employee) continue;

			$user_id = $employee->id;
			$total_allowed = 0;
			$total_used = 0;

			// Ensure current year balances exist
			$this->leave_model->ensure_current_year_balances($user_id);

			// 🎯 Get all PAID leave balances for current year
			$current_year = date('Y');
			$leave_balances = $this->db->where('user_id', $user_id)
										 ->where('year', $current_year)
										 ->get('leave_balance')
										 ->result();

			foreach ($leave_balances as $balance) {
				// Skip parental leave if disabled for this user
				$category = $this->db->get_where('leave_category', ['id' => $balance->leave_category_id])->row();
				if ($category && $category->name === 'Parental Leave' && (!$employee->parental_leave_enabled)) {
					continue;
				}

				$allowed = (float)$balance->total_days;
				$total_allowed += $allowed;

				// ✅ Count only approved leave applications from this category for current year
				$used = (float)($this->db->select_sum('leave_days')
					->where([
						'user_id' => $user_id,
						'category_id' => $balance->leave_category_id,
						'status' => 2,
						'YEAR(start_date)' => $current_year
					])
					->get('leave_application')
					->row()
					->leave_days ?? 0);

				$total_used += $used;
			}

			// ❌ No unpaid leave counted at all

			$leave_user_cards[] = [
				'name'       => $employee->name,
				'username'   => $employee->username,
				'staff_id'   => $employee->staff_id,
				'photo'      => $employee->photo ?? 'default.png',
				'used'       => $total_used,       // excludes unpaid
				'total'      => $total_allowed,    // excludes unpaid
				'in_time'    => $in_time ?? '',
				'branchID'   => $employee->branch_id,
			];
		}

		// 📌 Sort by staff ID
		usort($leave_user_cards, fn($a, $b) => strcmp($a['staff_id'], $b['staff_id']));

		// 📤 Send to view
		$this->data['leave_user_balances'] = $leave_user_cards;


        $this->data['main_menu']        = 'leave';
        $this->data['headerelements']   = array(
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

        $this->data['title']            = translate('leave');
        $this->data['sub_page']         = 'leave/index';
        $this->load->view('layout/index', $this->data);
    }

    // get add leave modal
    public function getApprovelLeaveDetails()
    {
        if (get_permission('leave_manage', 'is_add')) {
            $this->data['leave_id'] = $this->input->post('id');
            $this->load->view('leave/approvel_modalView', $this->data);
        }
    }

    public function save(){
        if ($_POST) {
            if (!get_permission('leave_manage', 'is_add')) {
                access_denied();
            }

            $this->form_validation->set_rules('applicant_id', translate('applicant'), 'trim|required');
			$leave_category = $this->input->post('leave_category');

			if ($leave_category === 'unpaid' || $leave_category === 'parental') {
				$this->form_validation->set_rules('leave_category', translate('leave_category'), 'required');
			} else {
				$this->form_validation->set_rules('leave_category', translate('leave_category'), 'required|callback_leave_check');
			}

            $this->form_validation->set_rules('daterange', translate('leave_date'), 'trim|required|callback_date_check');

            if ($this->form_validation->run() !== false) {
                $applicant_id   = $this->input->post('applicant_id');
                $leave_type_id  = $this->input->post('leave_category');
                $daterange      = explode(' - ', $this->input->post('daterange'));
                $start_date     = date("Y-m-d", strtotime($daterange[0]));
                $end_date       = date("Y-m-d", strtotime($daterange[1]));
                $reason         = $this->input->post('reason');
                $comments       = $this->input->post('comments');
                $apply_date     = date("Y-m-d H:i:s");
                $datetime1      = new DateTime($start_date);
                $datetime2      = new DateTime($end_date);
                $leave_days     = $datetime2->diff($datetime1)->format("%a") + 1;
                $orig_file_name = '';
                $enc_file_name  = '';
                // upload attachment file
                if (isset($_FILES["attachment_file"]) && !empty($_FILES['attachment_file']['name'])) {
                    $config['upload_path']      = './uploads/attachments/leave/';
                    $config['allowed_types']    = "*";
                    $config['max_size']         = '2024';
                    $config['encrypt_name']     = true;
                    $this->upload->initialize($config);
                    $this->upload->do_upload("attachment_file");
                    $orig_file_name = $this->upload->data('orig_name');
                    $enc_file_name  = $this->upload->data('file_name');
                }
                $arrayData = array(
                    'unique_id'         => generate_unique_id('leave'),
                    'user_id'           => $applicant_id,
                    'session_id'        => get_session_id(),
                    'category_id'       => $leave_type_id,
                    'reason'            => $reason,
                    'start_date'        => date("Y-m-d", strtotime($start_date)),
                    'end_date'          => date("Y-m-d", strtotime($end_date)),
                    'leave_days'        => $leave_days,
                    'status'            => 2,
                    'orig_file_name'    => $orig_file_name,
                    'enc_file_name'     => $enc_file_name,
                    'apply_date'        => $apply_date,
                    'approved_by'       => get_loggedin_user_id(),
                    'comments'          => $comments,
                );

                $this->db->insert('leave_application', $arrayData);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url    = base_url('leave');
                $array  = array('status' => 'success', 'url' => $url, 'error' => '');
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'url' => '', 'error' => $error);
            }
            echo json_encode($array);
        }
    }

    public function delete($id = '')
    {
        if (get_permission('leave_manage', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->where('id', $id);
            $this->db->delete('leave_application');
        }
    }

    public function date_check($daterange)
    {
        $daterange = explode(' - ', $daterange);
        $start_date = date("Y-m-d", strtotime($daterange[0]));
        $end_date = date("Y-m-d", strtotime($daterange[1]));

        $today = date('Y-m-d');
     /*    if ($today == $start_date) {
            $this->form_validation->set_message('date_check', "You can not leave the current day.");
            return false;
        } */
        if ($this->input->post('applicant_id')) {
            $applicant_id = $this->input->post('applicant_id');
            $role_id = $this->input->post('user_role');
        } else {
            $applicant_id = get_loggedin_user_id();
            $role_id = loggedin_role_id();
        }
        $getUserLeaves = $this->db->get_where('leave_application', array('user_id' => $applicant_id, 'role_id' => $role_id))->result();
        if (!empty($getUserLeaves)) {
            foreach ($getUserLeaves as $user_leave) {
                $get_dates = $this->user_leave_days($user_leave->start_date, $user_leave->end_date);
                $result_start = in_array($start_date, $get_dates);
                $result_end = in_array($end_date, $get_dates);
                if (!empty($result_start) || !empty($result_end)) {
                    $this->form_validation->set_message('date_check', 'Already have leave in the selected time.');
                    return false;
                }
            }
        }
        return true;
    }

	public function leave_check($type_id)
	{
		// 🛑 Bypass validation if 'unpaid' or 'parental'
		//if ($type_id === 'unpaid' || $type_id === 'parental') {
		//	return true;
		//}

		if (!empty($type_id)) {
			$daterange = explode(' - ', $this->input->post('daterange'));
			$start_date = date("Y-m-d", strtotime($daterange[0]));
			$end_date = date("Y-m-d", strtotime($daterange[1]));

			$applicant_id = $this->input->post('applicant_id') ?: get_loggedin_user_id();
			$role_id = $this->input->post('user_role') ?: loggedin_role_id();
			$current_year = date('Y');

			if (!empty($start_date) && !empty($end_date)) {
				// Get leave balance from leave_balance table for current year
				$leave_balance = $this->db->select('total_days')
					->where([
						'user_id' => $applicant_id,
						'leave_category_id' => $type_id,
						'year' => $current_year
					])
					->get('leave_balance')
					->row();

				$leave_total = $leave_balance ? $leave_balance->total_days : 0;

				$total_spent = $this->db->select('IFNULL(SUM(leave_days), 0) as total_days')
					->where([
						'user_id' => $applicant_id,
						'role_id' => $role_id,
						'category_id' => $type_id,
						'status' => '2',
						'YEAR(start_date)' => $current_year
					])
					->get('leave_application')
					->row()->total_days;

				$datetime1 = new DateTime($start_date);
				$datetime2 = new DateTime($end_date);
				$leave_days = $datetime2->diff($datetime1)->format("%a") + 1;

				$left_leave = ($leave_total - $total_spent);

				if ($left_leave < $leave_days) {
					$this->form_validation->set_message('leave_check', "Applied for $leave_days days, but only $left_leave days available.");
					return false;
				} else {
					return true;
				}
			} else {
				$this->form_validation->set_message('leave_check', "Select all required fields.");
				return false;
			}
		}
	}


    public function getRequestDetails()
    {
        $this->data['leave_id'] = $this->input->post('id');
        $this->load->view('leave/modal_request_details', $this->data);
    }

    public function request()
    {
        // check access permission
        if (!get_permission('leave_request', 'is_view')) {
            access_denied();
        }
		$userID = get_loggedin_user_id();
        if (isset($_POST['save'])) {

            if (!get_permission('leave_request', 'is_add')) {
                access_denied();
            }
			$leave_category = $this->input->post('leave_category');
            if ($leave_category === 'unpaid' || $leave_category === 'parental') {
				$this->form_validation->set_rules('leave_category', translate('leave_category'), 'required');
			} else {
				$this->form_validation->set_rules('leave_category', translate('leave_category'), 'required|callback_leave_check');
			}
            $this->form_validation->set_rules('daterange', translate('leave_date'), 'trim|required|callback_date_check');
            $this->form_validation->set_rules('attachment_file', translate('attachment'), 'callback_handle_upload');
            if ($this->form_validation->run() !== false) {
                $leave_type_id  = $this->input->post('leave_category');
                $daterange      = explode(' - ', $this->input->post('daterange'));
                $start_date     = date("Y-m-d", strtotime($daterange[0]));
                $end_date       = date("Y-m-d", strtotime($daterange[1]));
                $reason         = $this->input->post('reason');
                $apply_date     = date("Y-m-d H:i:s");
                $datetime1      = new DateTime($start_date);
                $datetime2      = new DateTime($end_date);
                $leave_days     = $datetime2->diff($datetime1)->format("%a") + 1;
                $orig_file_name = '';
                $enc_file_name  = '';
                // upload attachment file
                if (isset($_FILES["attachment_file"]) && !empty($_FILES['attachment_file']['name'])) {
                    $config['upload_path']      = './uploads/attachments/leave/';
                    $config['allowed_types']    = "*";
                    $config['max_size']         = '2024';
                    $config['encrypt_name']     = true;
                    $this->upload->initialize($config);
                    $this->upload->do_upload("attachment_file");
                    $orig_file_name = $this->upload->data('orig_name');
                    $enc_file_name  = $this->upload->data('file_name');
                }
                $arrayData = array(
                    'unique_id'         => generate_unique_id('leave'),
                    'user_id'           => $userID,
                    'session_id'        => get_session_id(),
                    'category_id'       => $leave_type_id,
                    'reason'            => $reason,
                    'start_date'        => date("Y-m-d", strtotime($start_date)),
                    'end_date'          => date("Y-m-d", strtotime($end_date)),
                    'leave_days'        => $leave_days,
                    'status'            => 1,
                    'orig_file_name'    => $orig_file_name,
                    'enc_file_name'     => $enc_file_name,
                    'apply_date'        => $apply_date,
                );
                $this->db->insert('leave_application', $arrayData);
                $leave_id = $this->db->insert_id();

				$leave_category = $this->db->get_where('leave_category', ['id' => $leave_type_id])->row();
				$staff = $this->db->get_where('staff', ['id' => $userID])->row();
				$leave_title = $leave_category ? $leave_category->name : $this->input->post('leave_category');
				$staff_name = $staff->name;

				$notificationData = array(
					'user_id'   => $userID,
					'type'      => 'leave',
					'title'     => $staff_name . ' Requested a ' . $leave_title . ' for ' . $leave_days . ' day\'s',
					'message'   => $reason,
					'url'        => base_url('leave'),
					'is_read'   => 0,
					'created_at'=> date('Y-m-d H:i:s')
				);

				$this->db->insert('notifications', $notificationData);

				// Send Telegram notification
				$department = $this->db->get_where('staff_department', ['id' => $staff->department])->row();
				$department_name = $department ? $department->name : 'Unknown Department';
				$today_display = date('d M Y');

				$bot_token = $telegram_bot;
				$chat_id = $telegram_chatID;

				$tg_message = "🛎️ *New Leave Request from {$staff_name}*\n\n" .
							"📅 *Date:* {$today_display}\n" .
							"👤 *Name:* {$staff_name}\n" .
							"🏢 *Department:* {$department_name}\n\n" .
							"📌 *Leave Type:* {$leave_title}\n" .
							"📅 *Duration:* {$leave_days} day" . ($leave_days > 1 ? 's' : '') . "\n" .
							"📝 *Reason:* {$reason}\n" .
							"🗓️ *From:* {$start_date} to {$end_date}\n\n" .
							"🔗 [Review Request](" . base_url('leave') . ")";

				$payload = [
					'chat_id' => $chat_id,
					'text' => $tg_message,
					'parse_mode' => 'Markdown',
					'disable_web_page_preview' => true,
				];

				$url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
				curl_exec($ch);
				curl_close($ch);

				// Get staff details for FCM notification
				$staff_details = $this->db->select('id, name, department')
				                          ->get_where('staff', ['id' => $userID])
				                          ->row();

				// Build FCM notification
				$title = 'New Leave Request';
				$dayLabel = $leave_days === 1 ? 'day' : 'days';
				$who = $staff_details ? $staff_details->name : 'An employee';
				$startFmt = date('j M Y', strtotime($start_date));
				$endFmt = date('j M Y', strtotime($end_date));

				$body = sprintf(
				    '%s requested a leave from %s to %s (%d %s).',
				    $who,
				    $startFmt,
				    $endFmt,
				    $leave_days,
				    $dayLabel
				);

				// Get approver tokens based on role
				$recipientTokens = [];
				if ($requestor_role == 5) {
					$recipientTokens = $this->get_tokens_by_role([3]);
				} elseif ($requestor_role == 3) {
					$recipientTokens = $this->get_tokens_by_role([5]);
				} elseif ($requestor_role == 8) {
					$recipientTokens = $this->get_tokens_by_role([5, 3]);
				} else {
					$recipientTokens = $this->get_tokens_by_role([8, 5, 3]);
				}

				// Send FCM notification
				if (!empty($recipientTokens)) {
				    $this->send_fcm_notification($title, $body, '', $recipientTokens, [
				        'type'      => 'leave_request',
				        'leave_id'  => (string)$leave_id,
				        'staff_id'  => (string)$userID,
				        'action'    => 'review'
				    ]);
				} else {
				    $this->log_message("INFO: No recipient FCM tokens found for leave_id={$leave_id}");
				}

				// Get current user info and role
				$requestor_id = get_loggedin_user_id();
				$requestor = $this->db
					->select('staff.name, staff.department, staff.email')
					->join('login_credential', 'login_credential.user_id = staff.id')
					->where('staff.id', $requestor_id)
					->get('staff')
					->row_array();

				$requestor_role = loggedin_role_id();
				$department_id = $requestor['department'];
				$requestor_name = $requestor['name'];

				$to_email = '';
				$to_name = '';
				$cc_emails = [];

				// Email routing logic based on role
				if ($requestor_role == 5) {
					// Role 5 requests go to Role 3
					$recipient = $this->db->select('staff.email, staff.name')
						->from('staff')
						->join('login_credential', 'login_credential.user_id = staff.id')
						->where('login_credential.role', 3)
						->where('login_credential.active', 1)
						->get()->row_array();

					if ($recipient) {
						$to_email = $recipient['email'];
						$to_name = $recipient['name'];
					}
				} elseif ($requestor_role == 3) {
					// Role 3 requests go to Role 5
					$recipient = $this->db->select('staff.email, staff.name')
						->from('staff')
						->join('login_credential', 'login_credential.user_id = staff.id')
						->where('login_credential.role', 5)
						->where('login_credential.active', 1)
						->get()->row_array();

					if ($recipient) {
						$to_email = $recipient['email'];
						$to_name = $recipient['name'];
					}
				} elseif ($requestor_role == 8) {
					// Role 8 requests go to Role 5 with Role 3 in CC
					$recipient = $this->db->select('staff.email, staff.name')
						->from('staff')
						->join('login_credential', 'login_credential.user_id = staff.id')
						->where('login_credential.role', 5)
						->where('login_credential.active', 1)
						->get()->row_array();

					if ($recipient) {
						$to_email = $recipient['email'];
						$to_name = $recipient['name'];
					}

					// Add Role 2 to CC
					$cc_recipient = $this->db->select('staff.email')
						->from('staff')
						->join('login_credential', 'login_credential.user_id = staff.id')
						->where('login_credential.role', 3)
						->where('login_credential.active', 1)
						->get()->row_array();

					if ($cc_recipient) {
						$cc_emails[] = $cc_recipient['email'];
					}
				} else {
					// For other roles, send to department manager (Role 8) with Role 5,3 in CC
					$manager = $this->db->select('staff.email, staff.name')
						->from('staff')
						->join('login_credential', 'login_credential.user_id = staff.id')
						->where('login_credential.role', 8)
						->where('staff.department', $department_id)
						->where('login_credential.active', 1)
						->get()->row_array();

					if ($manager) {
						$to_email = $manager['email'];
						$to_name = $manager['name'];
					}

					// Add Role 5 and 3 to CC
					$cc_recipients = $this->db->select('staff.email')
						->from('staff')
						->join('login_credential', 'login_credential.user_id = staff.id')
						->where_in('login_credential.role', [5, 3])
						->where('login_credential.active', 1)
						->get()->result_array();

					foreach ($cc_recipients as $cc_recipient) {
						$cc_emails[] = $cc_recipient['email'];
					}
				}

				// Send email if recipient found
				if (!empty($to_email)) {
					$mail_subject = 'New Leave Request from ' . $requestor_name;

					$mail_body = "
					<html>
					  <body style='font-family:Arial, sans-serif; background:#f9f9f9; padding:20px;'>
						<table style='max-width:600px; margin:auto; background:#fff; border-radius:8px; padding:20px; box-shadow:0 0 10px rgba(0,0,0,0.05);'>
						  <tr><td style='text-align:center;'>
							<h2 style='color:#0054a6;'>Employee Leave Request</h2>
						  </td></tr>
						  <tr><td style='font-size:15px; color:#333;'>
							<p>Dear <strong>{$to_name}</strong>,</p>
							<p><strong>{$requestor_name}</strong> has requested a leave of {$leave_days} days for {$reason} from {$start_date} to {$end_date}.</p>
							<p>Please review the request in the system.</p>
							<p style='margin-top:20px;'>Thank you,<br><strong>EMP Team</strong></p>
							<p style='text-align:center; font-size:14px; color:#888; margin-top:40px;'>From <strong>EMP</strong> with <span style='color:#e63946;'>&#10084;&#65039;</span></p>
						  </td></tr>
						</table>
					  </body>
					</html>
					";

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
						'body'          => $mail_body
					];

					// Add CC if exists
					if (!empty($cc_emails)) {
						$email_data['cc'] = implode(',', $cc_emails);
					}

					// Send the email
					$this->email_model->send_email_yandex($email_data);
				}

                set_alert('success', translate('information_has_been_saved_successfully'));
                redirect(base_url('leave/request'));
            }
        }
        $where = array('la.user_id' => get_loggedin_user_id());
        $this->data['leavelist'] = $this->leave_model->getLeaveList($where);
		// Get leave balances for current user
		$user_id = get_loggedin_user_id();
		$role_id = loggedin_role_id();
		$current_year = date('Y');

		$leave_categories = $this->db
			->get('leave_category')
			->result_array();

		$leave_balance = [];

		foreach ($leave_categories as $category) {
			$category_id = $category['id'];
			$allowed_days = (int)$category['days'];

			$used_days = $this->db
				->select_sum('leave_days')
				->where([
					'user_id' => $user_id,
					'category_id' => $category_id,
					'status' => 2, // accepted leaves
					'YEAR(start_date)' => $current_year
				])
				->get('leave_application')
				->row()
				->leave_days ?? 0;

			// ✅ Fix: Cast category_id to string for safe JS access
			$leave_balance[(string)$category_id] = max(0, $allowed_days - (int)$used_days);
		}


		// Add unpaid and parental explicitly
		$leave_balance['unpaid'] = '';
		$leave_balance['parental'] = '';
		$this->data['leave_balance'] = $leave_balance;


		$leave_chart_data = [];
		$staff = $this->db->select('parental_leave_enabled')->where('id', $user_id)->get('staff')->row();
		$query = $this->db->select('lc.id, lc.name, lc.days, lb.total_days')
			->from('leave_category lc')
			->join('leave_balance lb', 'lb.leave_category_id = lc.id')
			->where('lb.user_id', $user_id)
			->where('lb.year', $current_year)
			->where('lc.days >', 0);

		if (!$staff || !$staff->parental_leave_enabled) {
			$query->where('lc.name !=', 'Parental Leave');
		}

		$leave_categories = $query->get()->result_array();

		foreach ($leave_categories as $category) {
			$category_id = $category['id'];
			$allowed_days = (int)$category['total_days']; // Use total_days from leave_balance

			$used_days = $this->db
				->select_sum('leave_days')
				->where([
					'user_id' => $user_id,
					'category_id' => $category_id,
					'status' => 2,
					'YEAR(start_date)' => $current_year
				])
				->get('leave_application')
				->row()
				->leave_days ?? 0;

			$used_days = (int)$used_days;
			$remaining_days = max(0, $allowed_days - $used_days);

			$leave_chart_data[] = [
				'category' => $category['name'],
				'used' => $used_days,
				'remaining' => $remaining_days,
			];
		}

		$this->data['leave_chart_data'] = $leave_chart_data;

        $this->data['title'] = translate('leaves');
        $this->data['sub_page'] = 'leave/request';
        $this->data['main_menu'] = 'leave';
        $this->data['headerelements']   = array(
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

    public function request_delete($id = '')
    {
        $where = array(
            'status' => 1,
            'id' => $id,
        );

        $app = $this->db->where($where)->get('leave_application')->row_array();
        $file_name = FCPATH . 'uploads/attachments/leave/' . $app['enc_file_name'];
        if (file_exists($file_name)) {
            unlink($file_name);
        }
        $this->db->where($where)->delete('leave_application');
    }

    /* category form validation rules */
    protected function category_validation()
    {
        $this->form_validation->set_rules('leave_category', translate('leave_category'), 'trim|required|callback_unique_category');
        $this->form_validation->set_rules('leave_days', translate('leave_days'), 'trim|required');
    }

    // leave category information are prepared and stored in the database here
    public function category()
    {
        if (isset($_POST['save'])) {
            if (!get_permission('leave_category', 'is_add')) {
                access_denied();
            }
            $this->category_validation();
            if ($this->form_validation->run() !== false) {
                $arrayData = array(
                    'name' => $this->input->post('leave_category'),
                    'days' => $this->input->post('leave_days'),
                );
                $this->db->insert('leave_category', $arrayData);
                set_alert('success', translate('information_has_been_saved_successfully'));
                redirect(base_url('leave/category'));
            }
        }
        $this->data['title'] = translate('leave');
        $this->data['category'] = $this->app_lib->getTable('leave_category');
        $this->data['sub_page'] = 'leave/category';
        $this->data['main_menu'] = 'leave';
        $this->load->view('layout/index', $this->data);
    }

    public function category_edit()
    {
        if (!get_permission('leave_category', 'is_edit')) {
            ajax_access_denied();
        }
        $this->category_validation();
        if ($this->form_validation->run() !== false) {
            $category_id = $this->input->post('category_id');
            $arrayData = array(
                'name' => $this->input->post('leave_category'),
                'days' => $this->input->post('leave_days'),
            );
            $this->db->where('id', $category_id);
            $this->db->update('leave_category', $arrayData);
            set_alert('success', translate('information_has_been_updated_successfully'));
            $array  = array('status' => 'success');
        } else {
            $error = $this->form_validation->error_array();
            $array = array('status' => 'fail','error' => $error);
        }
        echo json_encode($array);
    }

    public function category_delete($id = '')
    {
        if (!get_permission('leave_category', 'is_delete')) {
            access_denied();
        }
        if (!is_superadmin_loggedin()){
            $this->db->where('branch_id', get_loggedin_branch_id());
        }
        $this->db->where('id', $id);
        $this->db->delete('leave_category');
    }

    public function getCategory()
    {
        $html = "";
        $branchID = $this->application_model->get_branch_id();
        if (!empty($branchID)) {
            $query = $this->db->select('id,name,days')
            ->get('leave_category');
            if ($query->num_rows() != 0) {
                $html .= '<option value="">' . translate('select') . '</option>';
                $sections = $query->result_array();
                foreach ($sections as $row) {
                    $html .= '<option value="' . $row['id'] . '">' . $row['name'] . ' (' . $row['days'] . ')' . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select') . '</option>';
        }
        echo $html;
    }

    // unique valid name verification is done here
    public function unique_category($name)
    {
        $category_id = $this->input->post('category_id');
        //$role_id = $this->input->post('role_id');
        $branch_id = $this->application_model->get_branch_id();
        if (!empty($category_id)) {
            $this->db->where_not_in('id', $category_id);
        }
        $this->db->where('name', $name);
       // $this->db->where('role_id', $role_id);
        $this->db->where('branch_id', $branch_id);
        $query = $this->db->get('leave_category');
        if ($query->num_rows() > 0) {
            if (!empty($category_id)) {
                set_alert('error', "The Category name are already used");
            } else {
                $this->form_validation->set_message("unique_category", translate('already_taken'));
            }
            return false;
        } else {
            return true;
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
            $leave = $this->db->get('leave_application')->row();
            if ($file != $leave->enc_file_name) {
                access_denied();
            }
            $this->load->helper('download');
            $fileData = file_get_contents('./uploads/attachments/leave/' . $leave->enc_file_name);
            force_download($leave->orig_file_name, $fileData);
        }
    }

    public function user_leave_days($start_date, $end_date)
    {
        $dates      = array();
        $current    = strtotime($start_date);
        $end_date   = strtotime($end_date);
        while ($current <= $end_date) {
            $dates[] = date('Y-m-d', $current);
            $current = strtotime('+1 day', $current);
        }
        return $dates;
    }

    /**
     * Get FCM tokens by specific roles
     */
    private function get_tokens_by_role($roles) {
        $tokens = $this->db->select('fcm_token')
            ->from('staff')
            ->join('login_credential', 'login_credential.user_id = staff.id')
            ->where_in('login_credential.role', $roles)
            ->where('login_credential.active', 1)
            ->where('fcm_token IS NOT NULL')
            ->where('fcm_token !=', '')
            ->get()
            ->result_array();

        return array_column($tokens, 'fcm_token');
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

    // AJAX endpoint for leave approval
    public function leave_ajax()
    {
        header('Content-Type: application/json');

        if (!get_permission('leave_manage', 'is_add')) {
            echo json_encode(['status' => 'error', 'message' => 'Access denied']);
            return;
        }

        $issued_by = get_loggedin_user_id();
        $id = $this->input->post('id');
        $status = $this->input->post('status');
        $comments = $this->input->post('comments');

        if (empty($id) || empty($status)) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
            return;
        }

        $arrayLeave = array(
            'approved_by' => $issued_by,
            'status'    => $status,
            'comments'  => $comments,
        );

        $this->db->where('id', $id);
        $result = $this->db->update('leave_application', $arrayLeave);

        if ($result) {
            // Send notifications (same logic as in index method)
            $leave_status = $status;

            $status_label = '';
            if ($leave_status == 1) {
                $status_label = 'Pending';
            } elseif ($leave_status == 2) {
                $status_label = 'Approved';
            } elseif ($leave_status == 3) {
                $status_label = 'Rejected';
            }

            $leave = $this->db->get_where('leave_application', ['id' => $id])->row();
            if ($leave) {
                $userID = $leave->user_id;
                $leave_type_id = $leave->category_id;
                $leave_days = $leave->leave_days;

                // Fetch category and staff
                $leave_category = $this->db->get_where('leave_category', ['id' => $leave_type_id])->row();
                $staff = $this->db->get_where('staff', ['id' => $userID])->row();
                $issuer = $this->db->get_where('staff', ['id' => $issued_by])->row();

                $leave_title = $leave_category ? $leave_category->name : 'Leave';
                $staff_name = $staff ? $staff->name : 'Staff';
                $issuer_name = $issuer ? $issuer->name : 'Admin';

                // Build message
                $message = 'Dear ' . $staff_name . ', your ' . $leave_days . ' day' . ($leave_days > 1 ? 's' : '') . ' ' . $leave_title . ' has been ' . $status_label . ' by ' . $issuer_name . '.';

                // Insert notification
               /*  $notificationData = array(
                    'user_id'    => $userID,
                    'type'       => 'leave',
                    'title'      => 'Leave Request ' . $status_label,
                    'message'    => $message,
                    'url'        => base_url('leave/request'),
                    'is_read'    => 0,
                    'created_at' => date('Y-m-d H:i:s')
                );

                $this->db->insert('notifications', $notificationData); */

                // Send FCM notification to leave applicant
                $applicant_tokens = $this->db->select('fcm_token')
                                             ->where('id', $userID)
                                             ->where('fcm_token IS NOT NULL')
                                             ->where('fcm_token !=', '')
                                             ->get('staff')
                                             ->result_array();

                if (!empty($applicant_tokens)) {
                    $tokens = array_column($applicant_tokens, 'fcm_token');
                    $fcm_title = 'Leave Request ' . $status_label;
                    $this->send_fcm_notification($fcm_title, $message, '', $tokens, [
                        'type'      => 'leave_status_update',
                        'leave_id'  => (string)$id,
                        'status'    => (string)$leave_status,
                        'action'    => 'view'
                    ]);
                }
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Leave request updated successfully',
                'leave_status' => $status
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update leave request']);
        }
    }


    public function reports()
    {
        if (!get_permission('leave_reports', 'is_view')) {
            access_denied();
        }

        $where = array();

        if (isset($_POST['search'])) {
            $branch_id = $this->input->post('branch_id');
            $userRole = $this->input->post('role_id');
            $daterange = explode(' - ', $this->input->post('daterange'));
            $start = date("Y-m-d", strtotime($daterange[0]));
            $end = date("Y-m-d", strtotime($daterange[1]));
            $where['la.start_date >='] = $start;
            $where['la.start_date <='] = $end;
            //$where['la.role_id'] = $userRole;
            $this->data['leavelist'] = $this->leave_model->getLeaveList($where, $userRole, $branch_id);
        }

        $this->data['title'] = translate('leave');
        $this->data['sub_page'] = 'leave/reports';
        $this->data['main_menu'] = 'leave_reports';
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/daterangepicker/daterangepicker.css',
            ),
            'js' => array(
                'vendor/moment/moment.js',
                'vendor/daterangepicker/daterangepicker.js',
            ),
        );
        $this->load->view('layout/index', $this->data);
    }

    public function get_leave_categories()
    {
        $applicant_id = $this->input->post('applicant_id');
        $current_year = date('Y');
        $html = '<option value="">' . translate('select') . '</option>';

        if ($applicant_id) {
            // Check if parental leave is enabled for this staff
            $staff = $this->db->select('parental_leave_enabled')->where('id', $applicant_id)->get('staff')->row();

            // Get leave balances for the applicant for current year
            $leave_balances = $this->db->select('lb.leave_category_id, lb.total_days, lc.name, lc.days')
                ->from('leave_balance lb')
                ->join('leave_category lc', 'lc.id = lb.leave_category_id')
                ->where('lb.user_id', $applicant_id)
                ->where('lb.year', $current_year)
                ->where('lb.total_days >', 0)
                ->get()
                ->result();

            foreach ($leave_balances as $balance) {
                // Skip parental leave if not enabled for this staff
                if ($balance->name === 'Parental Leave' && (!$staff || !$staff->parental_leave_enabled)) {
                    continue;
                }

                // Calculate used days for current year
                $used_days = $this->db->select_sum('leave_days')
                    ->where('user_id', $applicant_id)
                    ->where('category_id', $balance->leave_category_id)
                    ->where('status', 2)
                    ->where('YEAR(start_date)', $current_year)
                    ->get('leave_application')
                    ->row()
                    ->leave_days ?? 0;

                $remaining = $balance->total_days - $used_days;

                if ($remaining > 0) {
                    $html .= '<option value="' . $balance->leave_category_id . '">' . $balance->name . ' (' . $remaining . ' days remaining)</option>';
                }
            }

        }

        echo $html;
    }
}
