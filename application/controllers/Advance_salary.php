<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Advance_salary extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('advancesalary_model');
        $this->load->model('email_model');

        $this->uploadDir = FCPATH . 'uploads/notifications/';
        $this->serviceAccountPath = FCPATH . 'assets/notification/service-account.json';
        $this->logFile = FCPATH . 'assets/notification/logs/fcm_notifications.log';
    }

    public function index()
    {
        if (!get_permission('advance_salary_manage', 'is_view')) {
            access_denied();
        }
       if (isset($_POST['update'])) {
            if (!get_permission('advance_salary_manage', 'is_add')) {
                access_denied();
            }

		$id = $this->input->post('id');
		$status = (int) $this->input->post('status');
		$payment_status = (int) $this->input->post('payment_status');
		$payment_method = $this->input->post('payment_method');
		$comments = $this->input->post('comments');
		$logged_in_user_id = get_loggedin_user_id();

		// 🔍 Fetch existing data
		$existing = $this->db->get_where('advance_salary', ['id' => $id])->row();

		if (!$existing) {
			show_error('Invalid request ID.');
		}

		$update_data = [
			'status' => $status,
			'payment_status' => $payment_status,
			'payment_method' => $payment_method,
			'comments' => $comments,
			'paid_date' => date("Y-m-d H:i:s"),
		];

		// ✅ Only set issued_by if it wasn't set before
		if (empty($existing->issued_by)) {
			$update_data['issued_by'] = $logged_in_user_id;
		}

		// ✅ Only set paid_by if payment_status is Paid (2) or Rejected (3)
		if ($payment_status == 2 || $payment_status == 3) {
			$update_data['paid_by'] = $logged_in_user_id;
		}

		// 🔄 Final update
		$this->db->where('id', $id);
		$update_result = $this->db->update('advance_salary', $update_data);

		// 💰 Sync with Cashbook if payment is completed
		if ($update_result && $payment_status == 2) {
			$this->load->model('cashbook_model');
			$this->sync_with_cashbook($id);
		}

			 // 2. Fetch updated advance salary record
			$advance = $this->db->get_where('advance_salary', ['id' => $id])->row();

			if ($advance) {
				$userID = $advance->staff_id;

				// Map status
				$status_label = '';
				switch ($status) {
					case 1: $status_label = 'Pending'; break;
					case 2: $status_label = 'Approved'; break;
					case 3: $status_label = 'Rejected'; break;
				}

				// Get names
				$staff = $this->db->get_where('staff', ['id' => $userID])->row();
				$issuer = $this->db->get_where('staff', ['id' => $logged_in_user_id])->row();
				$staff_name = $staff ? $staff->name : 'Staff';
				$issuer_name = $issuer ? $issuer->name : 'Admin';

				// Set message
				if ($status == 2) {
					if ($payment_status == 1) {
						$message = 'Dear ' . $staff_name . ', your advance salary request has been accepted by '. $issuer_name . ' and is awaiting for accounts review.';
						$title = 'Advance Salary Request Accepted';
					} elseif ($payment_status == 2) {
						$message = 'Dear ' . $staff_name . ', your advance salary request has been approved and the payment is marked as completed by ' . $issuer_name . '.';
						$title = 'Advance Salary Request - Paid';
					} else {
						$message = 'Dear ' . $staff_name . ', your advance salary request has been approved by ' . $issuer_name . '.';
					}
				} else {
					$message = 'Dear ' . $staff_name . ', your advance salary request has been ' . strtolower($status_label) . ' by ' . $issuer_name . '.';
					$title = 'Advance Salary ' . $status_label;
				}

				// Insert notification
				$this->db->insert('notifications', [
					'user_id'    => $userID,
					'type'       => 'advance_salary',
					'title'      => $title,
					'message'    => $message,
					'url'        => base_url('advance_salary/request'),
					'is_read'    => 0,
					'created_at' => date('Y-m-d H:i:s')
				]);

				// Send FCM notification to applicant
				$applicant_tokens = $this->db->select('fcm_token')
				                             ->where('id', $userID)
				                             ->where('fcm_token IS NOT NULL')
				                             ->where('fcm_token !=', '')
				                             ->get('staff')
				                             ->result_array();

				if (!empty($applicant_tokens)) {
				    $tokens = array_column($applicant_tokens, 'fcm_token');
				    $this->send_fcm_notification($title, $message, '', $tokens, [
				        'type'              => 'advance_salary_status_update',
				        'advance_salary_id' => (string)$id,
				        'status'            => (string)$status,
				        'action'            => 'view'
				    ]);
				}
			}

            set_alert('success', translate('information_has_been_updated_successfully'));
            redirect(base_url('advance_salary'));
        }

        $month = '';
        $year = '';
        if (isset($_POST['search'])) {
            $month_year = $this->input->post('month_year');
            $month = date("m", strtotime($month_year));
            $year = date("Y", strtotime($month_year));
        }
		//$branch_id = $this->application_model->get_branch_id();
		$branch_id = $this->input->post('branch_id');
        $this->data['advanceslist'] = $this->advancesalary_model->getAdvanceSalaryList($month, $year, $branch_id);
        $this->data['title'] = translate('advance_salary');
        $this->data['sub_page'] = 'advance_salary/index';
        $this->data['main_menu'] = 'advance_salary';
        $this->load->view('layout/index', $this->data);
    }

    public function save()
    {
        if (!get_permission('advance_salary_manage', 'is_add')) {
            ajax_access_denied();
        }

        $this->form_validation->set_rules('staff_id', translate('applicant'), 'required');
        $this->form_validation->set_rules('amount', translate('amount'), 'required|numeric|greater_than[0]|callback_check_salary');
        $this->form_validation->set_rules('month_year', translate('deduct_month'), 'required|callback_check_advance_month');
        if ($this->form_validation->run() == true) {
			$branch_id = $this->db->query("SELECT branch_id FROM staff WHERE id = ?", array($this->input->post('staff_id')))->row()->branch_id;

            //$branch_id = $this->application_model->get_branch_id();
            $insertData = array(
                'unique_id' => generate_unique_id('advance_salary'),
                'staff_id' => $this->input->post('staff_id'),
                'deduct_month' => date("m", strtotime($this->input->post('month_year'))),
                'year' => date("Y", strtotime($this->input->post('month_year'))),
                'amount' => $this->input->post('amount'),
                'reason' => $this->input->post('reason'),
                'issued_by' => get_loggedin_user_id(),
                'paid_date' => date("Y-m-d H:i:s"),
                'request_date' => date("Y-m-d H:i:s"),
                'status' => 2,
                'branch_id' => $branch_id,
            );
            $this->db->insert('advance_salary', $insertData);

            // getting information for send email alert
            $getStaff = $this->db->select('branch_id,email,name,')->where('id', $insertData['staff_id'])->get('staff')->row();
            $insertData['comments'] = $insertData['reason'];
            $insertData['staff_name'] = $getStaff->name;
            $insertData['email'] = $getStaff->email;
            $insertData['deduct_motnh'] = $insertData['year'] . '-' . $insertData['deduct_month'];
            $this->email_model->sentAdvanceSalary($insertData);

            $url = base_url('advance_salary');
            $array = array('status' => 'success', 'url' => $url, 'error' => '');
            set_alert('success', translate('information_has_been_saved_successfully'));
        } else {
            $error = $this->form_validation->error_array();
            $array = array('status' => 'fail', 'url' => '', 'error' => $error);
        }
        echo json_encode($array);
    }

    public function delete($id = '')
    {
        if (get_permission('advance_salary_manage', 'is_delete')) {
            // Check branch restrictions
            $this->app_lib->check_branch_restrictions('advance_salary', $id);
            $this->db->where('id', $id);
            $this->db->delete('advance_salary');
        }
    }

    public function request()
    {
        if (!get_permission('advance_salary_request', 'is_view')) {
            access_denied();
        }
        $month = '';
        $year = '';
        $staff_id = get_loggedin_user_id();
        if (isset($_POST['search'])) {
            $month_year = $this->input->post('month_year');
            $month = date("m", strtotime($month_year));
            $year = date("Y", strtotime($month_year));
        }
        $this->data['advanceslist'] = $this->advancesalary_model->getAdvanceSalaryList($month, $year, '', $staff_id);
        $this->data['title'] = translate('advance_salary');
        $this->data['sub_page'] = 'advance_salary/request';
        $this->data['main_menu'] = 'advance_salary';
        $this->load->view('layout/index', $this->data);
    }

    public function request_save()
    {
        if (!get_permission('advance_salary_request', 'is_add')) {
            ajax_access_denied();
        }

      // Check for unsatisfied task-related warnings
        $has_unsatisfied_warnings = $this->db
            ->select('id')
            ->from('warnings')
            ->where('user_id', get_loggedin_user_id())
            ->where('task_unique_id IS NOT NULL')
            ->group_start() // open bracket for OR conditions
                ->where('status !=', 2)
                ->or_where('manager_review', 5)
                ->or_where('advisor_review !=', 2)
            ->group_end()   // close bracket
            ->get()
            ->num_rows() > 0;

        if ($has_unsatisfied_warnings) {
            // Check for penalty work days and served status
            $user_id = get_loggedin_user_id();
            $staff_info = $this->db->select('id, staff_id')->where('id', $user_id)->get('staff')->row();

            if ($staff_info) {
                $penalty_days = $this->db->select('penalty_date, warning_id')
                    ->from('penalty_days')
                    ->where('staff_id', $staff_info->staff_id)
                    ->get()
                    ->result_array();

                $unserved_penalties = [];
                foreach ($penalty_days as $penalty) {
                    $is_served = $this->db->select('id')
                        ->where('staff_id', $user_id)
                        ->where('DATE(date)', $penalty['penalty_date'])
                        ->where_in('status', ['P', 'L'])
                        ->get('staff_attendance')
                        ->num_rows() > 0;

                    if (!$is_served) {
                        $unserved_penalties[] = $penalty['penalty_date'];
                    }
                }

                // Only block if there are unserved penalty days
                if (!empty($unserved_penalties)) {
                    $error_msg = 'You have unserved penalty work days on: ' . implode(', ', array_map(function($date) {
                        return date('d M Y', strtotime($date));
                    }, $unserved_penalties)) . '. Please serve these penalty days first.';

                    $array = array('status' => 'fail', 'error' => array('amount' => $error_msg));
                    echo json_encode($array);
                    return;
                }
            }
        }

        if ($_POST) {
            $this->form_validation->set_rules('amount', translate('amount'),'required|numeric|less_than_equal_to[10000]|callback_check_salary');

            $this->form_validation->set_rules('month_year', translate('deduct_month'), 'required|callback_check_advance_month');
            if ($this->form_validation->run() == true) {
                $insertData = array(
                    'unique_id' => generate_unique_id('advance_salary'),
                    'staff_id' => get_loggedin_user_id(),
                    'deduct_month' => date("m", strtotime($this->input->post('month_year'))),
                    'year' => date("Y", strtotime($this->input->post('month_year'))),
                    'amount' => $this->input->post('amount'),
                    'reason' => $this->input->post('reason'),
                    'request_date' => date("Y-m-d H:i:s"),
                    'branch_id' => get_loggedin_branch_id(),
                    'status' => 1,
                );
                $this->db->insert('advance_salary', $insertData);
				 // Fetch current staff name
				$staff = $this->db->get_where('staff', ['id' => get_loggedin_user_id()])->row();
				$staff_name = $staff ? $staff->name : 'Staff';
				$reason = $this->input->post('reason');
				// Create Notification
				$notificationData = array(
					'user_id'    => get_loggedin_user_id(),
					'type'       => 'advance_salary',
					'title'      => 'Advance Salary Requested by ' . $staff_name,
					'message'    => 'Dear Concern, ' . $staff_name . 'has requested an advance salary.',
					'is_read'    => 0,
					'url'        => base_url('advance_salary'),
					'created_at' => date('Y-m-d H:i:s')
				);

				$this->db->insert('notifications', $notificationData);
				$advance_id = $this->db->insert_id();

				// Send Telegram notification
				$department = $this->db->get_where('staff_department', ['id' => $staff->department])->row();
				$department_name = $department ? $department->name : 'Unknown Department';
				$today_display = date('d M Y');
				$formatted_amount = number_format($this->input->post('amount'), 2);
				$month_display = date('F Y', strtotime($this->input->post('month_year')));

				$bot_token = $telegram_bot;
				$chat_id = $telegram_chatID;

				$tg_message = "💰 *New Advance Salary Request from {$staff_name}*\n\n" .
							"📅 *Date:* {$today_display}\n" .
							"👤 *Name:* {$staff_name}\n" .
							"🏢 *Department:* {$department_name}\n\n" .
							"💵 *Amount:* BDT {$formatted_amount}\n" .
							"📅 *Deduct Month:* {$month_display}\n" .
							"📝 *Reason:* {$reason}\n\n" .
							"🔗 [Review Request](" . base_url('advance_salary') . ")";

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
				                          ->get_where('staff', ['id' => get_loggedin_user_id()])
				                          ->row();

				// Build FCM notification
				$title = 'New Advance Salary Request';
				$formatted_amount = number_format($this->input->post('amount'), 2);
				$who = $staff_details ? $staff_details->name : 'An employee';
				$month_display = date('F Y', strtotime($this->input->post('month_year')));

				$body = sprintf(
				    '%s requested %s BDT advance salary for %s.',
				    $who,
				    $formatted_amount,
				    $month_display
				);

				// Get FCM tokens based on requestor role (same logic as email)
				$recipientTokens = [];
				$requestor_role = $this->db->select('login_credential.role')
					->from('staff')
					->join('login_credential', 'login_credential.user_id = staff.id')
					->where('staff.id', get_loggedin_user_id())
					->get()->row()->role;

				if ($requestor_role == 5) {
					// Role 5 -> FCM to role 3
					$tokens = $this->db->select('staff.fcm_token')
						->from('staff')
						->join('login_credential', 'login_credential.user_id = staff.id')
						->where('login_credential.role', 3)
						->where('login_credential.active', 1)
						->where('staff.fcm_token IS NOT NULL')
						->where('staff.fcm_token !=', '')
						->get()->result_array();
					$recipientTokens = array_column($tokens, 'fcm_token');
				} elseif ($requestor_role == 3) {
					// Role 3 -> FCM to role 5
					$tokens = $this->db->select('staff.fcm_token')
						->from('staff')
						->join('login_credential', 'login_credential.user_id = staff.id')
						->where('login_credential.role', 5)
						->where('login_credential.active', 1)
						->where('staff.fcm_token IS NOT NULL')
						->where('staff.fcm_token !=', '')
						->get()->result_array();
					$recipientTokens = array_column($tokens, 'fcm_token');
				} elseif ($requestor_role == 8) {
					// Role 8 -> FCM to roles 5,3
					$tokens = $this->db->select('staff.fcm_token')
						->from('staff')
						->join('login_credential', 'login_credential.user_id = staff.id')
						->where_in('login_credential.role', [3, 5])
						->where('login_credential.active', 1)
						->where('staff.fcm_token IS NOT NULL')
						->where('staff.fcm_token !=', '')
						->get()->result_array();
					$recipientTokens = array_column($tokens, 'fcm_token');
				} else {
					// Others -> FCM to role 8 + roles 5,3
					$tokens = $this->db->select('staff.fcm_token')
						->from('staff')
						->join('login_credential', 'login_credential.user_id = staff.id')
						->group_start()
							->where('login_credential.role', 8)
							->where('staff.department', $staff_details->department)
						->group_end()
						->or_group_start()
							->where_in('login_credential.role', [3, 5])
						->group_end()
						->where('login_credential.active', 1)
						->where('staff.fcm_token IS NOT NULL')
						->where('staff.fcm_token !=', '')
						->get()->result_array();
					$recipientTokens = array_column($tokens, 'fcm_token');
				}

				// Send FCM notification
				if (!empty($recipientTokens)) {
				    $this->send_fcm_notification($title, $body, '', $recipientTokens, [
				        'type'            => 'advance_salary_request',
				        'advance_id'      => (string)$advance_id,
				        'staff_id'        => (string)get_loggedin_user_id(),
				        'action'          => 'review'
				    ]);
				} else {
				    $this->log_message("INFO: No recipient FCM tokens found for advance_id={$advance_id}");
				}

				// 1. Get current user info with role
				$requestor_id = get_loggedin_user_id();
				$requestor = $this->db
					->select('staff.name, staff.department, staff.email, login_credential.role')
					->join('login_credential', 'login_credential.user_id = staff.id')
					->where('staff.id', $requestor_id)
					->get('staff')
					->row_array();

				$department_id = $requestor['department'];
				$requestor_name = $requestor['name'];
				$requestor_role = $requestor['role'];

				$deduct_month = date("F", strtotime($this->input->post('month_year')));
				$year         = date("Y", strtotime($this->input->post('month_year')));
				$amount       = $this->input->post('amount');
				$reason       = $this->input->post('reason');

				// Determine email recipients based on requestor role
				$to_email = '';
				$to_name = '';
				$cc_emails = [];

				if ($requestor_role == 5) {
					// Role 5 requests -> mail goes to role 3
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
					// Role 3 requests -> mail goes to role 5
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
					// Role 8 requests -> mail goes to role 5, CC to role 3
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
					// Get role 3 for CC
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
					// Others -> mail goes to role 8 (department manager), CC to roles 5,3
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
					// Get roles 5,3 for CC
					$cc_list = $this->db->select('staff.email')
						->from('staff')
						->join('login_credential', 'login_credential.user_id = staff.id')
						->where_in('login_credential.role', [3, 5])
						->where('login_credential.active', 1)
						->get()->result_array();
					$cc_emails = array_column($cc_list, 'email');
				}

				// Proceed only if recipient exists
				if (!empty($to_email)) {

					// Email subject and body
					$mail_subject = 'Advance Salary Request from ' . $requestor_name;

						  $mail_body = "
						<html>
						  <body style='font-family:Arial, sans-serif; background:#f9f9f9; padding:20px;'>
							<table style='max-width:600px; margin:auto; background:#fff; border-radius:8px; padding:20px; box-shadow:0 0 10px rgba(0,0,0,0.05);'>
							  <tr><td style='text-align:center;'>
								<h2 style='color:#0054a6;'>Advance Salary Request</h2>
							  </td></tr>
							  <tr><td style='font-size:15px; color:#333;'>
								<p>Dear <strong>{$to_name}</strong>,</p>
								<p><strong>{$requestor_name}</strong> has submitted an advance salary request. Below are the details:</p>

								<p style='margin: 15px 0 5px 0;'><strong>&#128181; Amount:</strong> BDT{$amount}</p>
								<p><strong>&#128197; Deduct Month:</strong> {$deduct_month}, {$year}</p>
								<p><strong>&#128221; Reason:</strong> {$reason}</p>

								<p style='margin-top:20px;'>Please log in to review and process this request accordingly.</p>

								<p style='margin-top:30px;'>Thank you,<br><strong>EMP Team</strong></p>
								<p style='text-align:center; font-size:14px; color:#888; margin-top:40px;'>
								  From <strong>EMP</strong> with <span style='color:#e63946;'>&#10084;&#65039;</span>
								</p>
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
						'smtp_pass'     => $config['smtp_pass'], // App Password
						'smtp_secure'   => $config['smtp_encryption'],
						'smtp_port'     => $config['smtp_port'],
						'from_email'    => $config['email'],
						'from_name'     => 'EMP Admin',
						'to_email'      => $to_email,
						'to_name'       => $to_name,
						'subject'       => $mail_subject,
						'body'          => $mail_body,
						'cc'            => implode(',', $cc_emails)
					];


					// Send the email
					$this->email_model->send_email_yandex($email_data);
				}

                $url = base_url('advance_salary/request');
                $array = array('status' => 'success', 'url' => $url);
                set_alert('success', translate('information_has_been_saved_successfully'));
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
        }
    }

    public function request_delete($id = '')
    {
        if (get_permission('advance_salary_request', 'is_delete')) {
            $this->db->where('staff_id', get_loggedin_user_id());
            $this->db->where('id', $id);
            $this->db->where('status', 1);
            $this->db->delete('advance_salary');
        }
    }

    public function getRequestDetails()
    {
        if (get_permission('advance_salary_request', 'is_view')) {
            $this->data['salary_id'] = $this->input->post('id');
            $this->load->view('advance_salary/modal_request_details', $this->data);
        }
    }

    // employee salary allocation validation checking
    public function check_salary($amount)
    {
        if ($amount) {
            if ($this->uri->segment(2) == 'request_save') {
                $staff_id = get_loggedin_user_id();
            } else {
                $staff_id = $this->input->post('staff_id');
            }
            $get_salary = $this->advancesalary_model->getBasicSalary($staff_id, $amount);
            if ($get_salary == 1) {
                $this->form_validation->set_message('check_salary', 'This Employee Is Not Allocated Salary !');
                return false;
            } elseif ($get_salary == 2) {
                $this->form_validation->set_message('check_salary', 'Your Advance Amount Exceeds Basic Salary !');
                return false;
            } elseif ($get_salary == 3) {
                return true;
            }
        }
    }

    // verification of payment to employees salary this month
    public function check_advance_month($month)
    {
        $staff_id = $this->input->post('staff_id');
        $getValidation = $this->advancesalary_model->getAdvanceValidMonth($staff_id, $month);
        if ($getValidation == true) {
            return true;
        } else {
            $this->form_validation->set_message('check_advance_month', 'This Month Salary Already Paid Or Requested !');
            return false;
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

    /**
     * Sync advance salary with cashbook
     */
    private function sync_with_cashbook($advance_id)
    {
        try {
            $this->cashbook_model->syncAdvanceSalary($advance_id);
        } catch (Exception $e) {
            error_log("Advance Salary Cashbook Sync Error: " . $e->getMessage());
        }
    }

    // Get advance salary details for approval modal
    public function getAdvanceSalaryDetails()
    {
        if (get_permission('advance_salary_manage', 'is_view')) {
            $this->data['salary_id'] = $this->input->post('id');
            $this->load->view('advance_salary/approvel_modalView', $this->data);
        }
    }

    // AJAX endpoint for advance salary approval
    public function advance_salary_ajax()
    {
        header('Content-Type: application/json');

        if (!get_permission('advance_salary_manage', 'is_add')) {
            echo json_encode(['status' => 'error', 'message' => 'Access denied']);
            return;
        }

        $id = $this->input->post('id');
        $status = (int) $this->input->post('status');
        $payment_status = (int) $this->input->post('payment_status');
        $payment_method = $this->input->post('payment_method');
        $comments = $this->input->post('comments');
        $logged_in_user_id = get_loggedin_user_id();

        // Fetch existing data
        $existing = $this->db->get_where('advance_salary', ['id' => $id])->row();

        if (!$existing) {
            echo json_encode(['status' => 'error', 'message' => 'Advance salary request not found']);
            return;
        }

        $update_data = [
            'status' => $status,
            'payment_status' => $payment_status,
            'payment_method' => $payment_method,
            'comments' => $comments,
            'paid_date' => date("Y-m-d H:i:s"),
        ];

        // Only set issued_by if it wasn't set before
        if (empty($existing->issued_by)) {
            $update_data['issued_by'] = $logged_in_user_id;
        }

        // Only set paid_by if payment_status is Paid (2) or Rejected (3)
        if ($payment_status == 2 || $payment_status == 3) {
            $update_data['paid_by'] = $logged_in_user_id;
        }

        // Update the advance salary
        $this->db->where('id', $id);
        $result = $this->db->update('advance_salary', $update_data);

        // 💰 Sync with Cashbook if payment is completed
        if ($result && $payment_status == 2) {
            $this->load->model('cashbook_model');
            $this->sync_with_cashbook($id);
        }

        if ($result) {
            // Send notifications (same logic as in index method)
            $advance = $this->db->get_where('advance_salary', ['id' => $id])->row();

            if ($advance) {
                $userID = $advance->staff_id;

                // Map status
                $status_label = '';
                switch ($status) {
                    case 1: $status_label = 'Pending'; break;
                    case 2: $status_label = 'Approved'; break;
                    case 3: $status_label = 'Rejected'; break;
                }

                // Get names
                $staff = $this->db->get_where('staff', ['id' => $userID])->row();
                $issuer = $this->db->get_where('staff', ['id' => $logged_in_user_id])->row();
                $staff_name = $staff ? $staff->name : 'Staff';
                $issuer_name = $issuer ? $issuer->name : 'Admin';

                // Set message
                if ($status == 2) {
                    if ($payment_status == 1) {
                        $message = 'Dear ' . $staff_name . ', your advance salary request has been accepted by '. $issuer_name . ' and is awaiting for accounts review.';
                        $title = 'Advance Salary Request Accepted';
                    } elseif ($payment_status == 2) {
                        $message = 'Dear ' . $staff_name . ', your advance salary request has been approved and the payment is marked as completed by ' . $issuer_name . '.';
                        $title = 'Advance Salary Request - Paid';
                    } else {
                        $message = 'Dear ' . $staff_name . ', your advance salary request has been approved by ' . $issuer_name . '.';
                        $title = 'Advance Salary ' . $status_label;
                    }
                } else {
                    $message = 'Dear ' . $staff_name . ', your advance salary request has been ' . strtolower($status_label) . ' by ' . $issuer_name . '.';
                    $title = 'Advance Salary ' . $status_label;
                }

                // Insert notification
                $this->db->insert('notifications', [
                    'user_id'    => $userID,
                    'type'       => 'advance_salary',
                    'title'      => $title,
                    'message'    => $message,
                    'url'        => base_url('advance_salary/request'),
                    'is_read'    => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                // Send FCM notification to applicant
                $applicant_tokens = $this->db->select('fcm_token')
                                             ->where('id', $userID)
                                             ->where('fcm_token IS NOT NULL')
                                             ->where('fcm_token !=', '')
                                             ->get('staff')
                                             ->result_array();

                if (!empty($applicant_tokens)) {
                    $tokens = array_column($applicant_tokens, 'fcm_token');
                    $this->send_fcm_notification($title, $message, '', $tokens, [
                        'type'              => 'advance_salary_status_update',
                        'advance_salary_id' => (string)$id,
                        'status'            => (string)$status,
                        'action'            => 'view'
                    ]);
                }
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Advance salary updated successfully',
                'advance_status' => $status,
                'payment_status' => $payment_status
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update advance salary']);
        }
    }
}
