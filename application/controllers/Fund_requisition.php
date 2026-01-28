<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Fund_requisition extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('fundrequisition_model');
        $this->load->model('email_model');
        $this->load->model('cashbook_model');

        $this->uploadDir = FCPATH . 'uploads/notifications/';
        $this->serviceAccountPath = FCPATH . 'assets/notification/service-account.json';
        $this->logFile = FCPATH . 'assets/notification/logs/fcm_notifications.log';
        $this->fundLogFile = FCPATH . 'application/logs/fund_requisition.log';
    }

public function index()
{
    if (!get_permission('fund_requisition_manage', 'is_view')) {
        access_denied();
    }

   if (isset($_POST['update'])) {
	if (!get_permission('fund_requisition_manage', 'is_add')) {
		access_denied();
	}

	$id = $this->input->post('id');
	$status = (int) $this->input->post('status');
	$payment_status = (int) $this->input->post('payment_status');
	$payment_method = $this->input->post('payment_method');
	$comments = $this->input->post('comments');
	$logged_in_user_id = get_loggedin_user_id();

	// 🔍 Fetch existing data
	$existing = $this->db->get_where('fund_requisition', ['id' => $id])->row();

	if (!$existing) {
		show_error('Invalid requisition ID.');
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

	// ✅ If role is Admin, HR or Accounts (1,2,5), allow ledger adjustment
	if (in_array(loggedin_role_id(), [1, 2, 5])) {
		$update_data['adjust_amount'] = (float) $this->input->post('adjust_amount');
		$update_data['ledger_status'] = $this->input->post('ledger_status');
	}

	// 🔄 Final update
	$this->db->where('id', $id);
	$update_result = $this->db->update('fund_requisition', $update_data);

	// 📝 Log the update
	$this->log_fund_action('UPDATE', $id, $update_data, $logged_in_user_id);

	// 💰 Sync with Cashbook if payment is completed
	if ($update_result && $payment_status == 2) {
		$this->sync_with_cashbook($id);
	}

	// Notification logic
	$funds = $this->db->get_where('fund_requisition', ['id' => $id])->row();

	if ($funds) {
		$userID = $funds->staff_id;

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
				$message = 'Dear ' . $staff_name . ', your fund requisition has been accepted and is awaiting for accounts review.';
			} elseif ($payment_status == 2) {
				$message = 'Dear ' . $staff_name . ', your fund requisition has been approved and the payment is marked as completed by ' . $issuer_name . '.';
			} else {
				$message = 'Dear ' . $staff_name . ', your fund requisition has been approved by ' . $issuer_name . '.';
			}
		} else {
			$message = 'Dear ' . $staff_name . ', your fund requisition request has been ' . strtolower($status_label) . ' by ' . $issuer_name . '.';
		}

		// Insert notification
		$this->db->insert('notifications', [
			'user_id'    => $userID,
			'type'       => 'fund_requisition',
			'title'      => 'Fund Requisition ' . $status_label,
			'message'    => $message,
			'url'        => base_url('fund_requisition/request'),
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
		    $this->send_fcm_notification('Fund Requisition ' . $status_label, $message, '', $tokens, [
		        'type'                => 'fund_requisition_status_update',
		        'fund_requisition_id' => (string)$id,
		        'status'              => (string)$status,
		        'action'              => 'view'
		    ]);
		}
	}

	set_alert('success', translate('information_has_been_updated_successfully'));
	redirect(base_url('fund_requisition'));
}


    // Handle search filter
    $start = $end = '';
    if (isset($_POST['search'])) {
        $daterange = explode(' - ', $this->input->post('daterange'));
        $start = date("Y-m-d", strtotime($daterange[0]));
        $end = date("Y-m-d", strtotime($daterange[1]));
    }

    $this->data['fundlist'] = $this->fundrequisition_model->getFundRequisitions($start, $end, get_loggedin_user_id());
    $this->data['title'] = translate('fund_requisition');
    $this->data['sub_page'] = 'fund_requisition/index';
    $this->data['main_menu'] = 'fund_requisition';
    $this->data['headerelements'] = array(
        'css' => array('vendor/daterangepicker/daterangepicker.css'),
        'js' => array(
            'vendor/moment/moment.js',
            'vendor/daterangepicker/daterangepicker.js',
        ),
    );
    $this->load->view('layout/index', $this->data);
}


	 // category information are prepared and stored in the database here
    public function category()
    {
		if (!get_permission('fund_requisition_category', 'is_add')) {
                access_denied();
            }
        if (isset($_POST['save'])) {

			$this->fundrequisition_model->category_save($this->input->post());
			set_alert('success', translate('information_has_been_saved_successfully'));
			redirect(base_url('fund_requisition/category'));
        }
        $this->data['categorylist'] = $this->fundrequisition_model->get_all_categories();
        $this->data['title'] = translate('category');
        $this->data['sub_page'] = 'fund_requisition/category';
        $this->data['main_menu'] = 'fund_requisition';
        $this->load->view('layout/index', $this->data);
    }

    public function category_edit()
    {
        if ($_POST) {
            if (!get_permission('fund_requisition_category', 'is_edit')) {
                ajax_access_denied();
            }

			$this->fundrequisition_model->category_save($this->input->post());
			set_alert('success', translate('information_has_been_updated_successfully'));
			$url = base_url('fund_requisition/category');
			$array = array('status' => 'success', 'url' => $url, 'error' => '');

            echo json_encode($array);
        }
    }

    public function category_delete($id)
    {
        if (get_permission('fund_requisition_category', 'is_delete')) {
            $this->db->where('id', $id);
            $this->db->delete('fund_category');
        }
    }

    public function save()
{
    if (!get_permission('fund_requisition_manage', 'is_add')) {
        ajax_access_denied();
    }

    $staff_id = $this->input->post('staff_id');
    $category_id = $this->input->post('category_id');
    $amount = $this->input->post('amount');
    $reason = $this->input->post('reason');
    $token = $this->input->post('token');
	$milestone_id = $this->input->post('milestone_id');
	$task_id = $this->input->post('task_id');
    $billing_type = $this->input->post('billing_type');
    $is_lead = $this->input->post('is_lead') ? 1 : 0;

    // Validate required fields
    if (empty($staff_id) || empty($category_id) || empty($amount) || empty($billing_type)) {
        echo json_encode(['status' => 'error', 'error' => 'Required fields missing.']);
        return;
    }

    // Get category name
    $category_name = $this->db->select('name')->where('id', $category_id)->get('fund_category')->row('name');

    // If category is "Conveyance" or "Convence", token must be present
    if (strtolower(trim($category_name)) == 'conveyance' || strtolower(trim($category_name)) == 'convence') {
        if (empty($token)) {
            echo json_encode(['status' => 'error', 'error' => 'CRM Token is required for Conveyance category.']);
            return;
        }
    } else {
        // If not conveyance, ignore token
        $token = null;
    }

    // Get branch ID of selected staff
    $branch = $this->db->select('branch_id')->where('id', $staff_id)->get('staff')->row();
    $branch_id = $branch ? $branch->branch_id : 0;

    // Prepare data for insertion
    $insertData = array(
        'unique_id' => generate_unique_id('fund_requisition'),
        'staff_id' => $staff_id,
        'category_id' => $category_id,
        'amount' => $amount,
        'reason' => $reason,
        'token' => $token,
		'milestone' => $is_lead ? null : $milestone_id,
		'task_id' => $is_lead ? null : $task_id,
        'billing_type' => $billing_type,
        'is_lead' => $is_lead,
        'issued_by' => get_loggedin_user_id(),
        'paid_date' => date("Y-m-d H:i:s"),
        'request_date' => date("Y-m-d H:i:s"),
        'status' => 2, // Pending
        'branch_id' => $branch_id,
    );

    $this->db->insert('fund_requisition', $insertData);
    $fund_id = $this->db->insert_id();

    // 📝 Log the creation
    $this->log_fund_action('CREATE', $fund_id, $insertData, get_loggedin_user_id());

    // Success response
    $url = base_url('fund_requisition');
    $array = array('status' => 'success', 'url' => $url, 'error' => '');
    set_alert('success', translate('information_has_been_saved_successfully'));
    echo json_encode($array);
}

    public function delete($id = '')
    {
        if (get_permission('fund_requisition_manage', 'is_delete')) {
            // Check branch restrictions
            $this->app_lib->check_branch_restrictions('fund_requisition', $id);

            // Log before deletion
            $fund_data = $this->db->get_where('fund_requisition', ['id' => $id])->row_array();
            $this->log_fund_action('DELETE', $id, $fund_data, get_loggedin_user_id());

            $this->db->where('id', $id);
            $this->db->delete('fund_requisition');
        }
    }

    public function request()
    {
        if (!get_permission('fund_requisition_request', 'is_view')) {
            access_denied();
        }
		$User_ID = get_loggedin_user_id();
        if (isset($_POST['search'])) {
           $daterange = explode(' - ', $this->input->post('daterange'));
			$start = date("Y-m-d", strtotime($daterange[0]));
			$end = date("Y-m-d", strtotime($daterange[1]));

        }
        $this->data['fundlist'] = $this->fundrequisition_model->getFundRequisitions_request($start, $end, $User_ID);
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
        $this->data['title'] = translate('fund_requisition');
        $this->data['sub_page'] = 'fund_requisition/request';
        $this->data['main_menu'] = 'fund_requisition';
        $this->load->view('layout/index', $this->data);
    }

   public function request_save()
{
    if (!get_permission('fund_requisition_request', 'is_add')) {
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
        $category_id = $this->input->post('category_id');
        $category = $this->db->select('name')->where('id', $category_id)->get('fund_category')->row_array();
        $category_name = strtolower(trim($category['name'] ?? ''));

        // Base validation rules
        //$this->form_validation->set_rules('amount', translate('amount'), 'required|numeric|less_than_equal_to[10000]|callback_check_salary');
		$this->form_validation->set_rules('amount', translate('amount'), 'required|numeric');
        $this->form_validation->set_rules('category_id', translate('fund_category'), 'required');
        $this->form_validation->set_rules('billing_type', translate('billing_type'), 'required');

        // Token is required only if category is "conveyance" or "convence"
        if (in_array($category_name, ['conveyance', 'convence'])) {
            $this->form_validation->set_rules('token', translate('CRM Token No.'), 'required');
        }

        if ($this->form_validation->run() == true) {

            $staff_id = get_loggedin_user_id();
            $amount = $this->input->post('amount');
            $token = $this->input->post('token');
            $reason = $this->input->post('reason');
            $milestone_id = $this->input->post('milestone_id');
            $task_id = $this->input->post('task_id');
            $billing_type = $this->input->post('billing_type');
            $is_lead = $this->input->post('is_lead') ? 1 : 0;
            $branch_id = get_loggedin_branch_id();
			$orig_file_name = '';
			$enc_file_name  = '';
			// upload attachment file
			if (isset($_FILES["attachment_file"]) && !empty($_FILES['attachment_file']['name'])) {
				$config['upload_path']      = './uploads/attachments/fund_requisition/';
				$config['allowed_types']    = "*";
				$config['max_size']         = '2024';
				$config['encrypt_name']     = true;
				$this->upload->initialize($config);
				$this->upload->do_upload("attachment_file");
				$orig_file_name = $this->upload->data('orig_name');
				$enc_file_name  = $this->upload->data('file_name');
			}
            // Insert data
            $insertData = [
                'unique_id' => generate_unique_id('fund_requisition'),
                'staff_id' => $staff_id,
                'amount' => $amount,
                'category_id' => $category_id,
                'reason' => $reason,
                'token' => in_array($category_name, ['conveyance', 'convence']) ? $token : null,
                'milestone' => $is_lead ? null : $milestone_id,
                'task_id' => $is_lead ? null : $task_id,
                'billing_type' => $billing_type,
                'is_lead' => $is_lead,
                'request_date' => date("Y-m-d H:i:s"),
                'branch_id' => $branch_id,
                'status' => 1, // pending
                'payment_status' => 1, // pending
				'orig_file_name'    => $orig_file_name,
                'enc_file_name'     => $enc_file_name,
            ];
            $this->db->insert('fund_requisition', $insertData);
            $req_id = $this->db->insert_id();

            // 📝 Log the request creation
            $this->log_fund_action('REQUEST_CREATE', $req_id, $insertData, $staff_id);

            // Send notification
            $staff = $this->db->get_where('staff', ['id' => $staff_id])->row();
            $staff_name = $staff->name ?? 'Staff';

            $this->db->insert('notifications', [
                'user_id'    => $staff_id,
                'type'       => 'fund_requisition',
                'title'      => 'Fund Requisition Requested by ' . $staff_name,
                'message'    => 'Dear Concern, ' . $staff_name . ' has submitted a fund requisition',
                'is_read'    => 0,
                'url'        => base_url('fund_requisition'),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // Get staff details for FCM notification
            $staff_details = $this->db->select('id, name, department')
                                      ->get_where('staff', ['id' => $staff_id])
                                      ->row();

            // Build FCM notification
            $title = 'New Fund Requisition Request';
            $formatted_amount = number_format($amount, 2);
            $who = $staff_details ? $staff_details->name : 'An employee';
            $category_display = $category['name'] ?? 'Fund';

            $body = sprintf(
                '%s requested %s BDT for %s (%s).',
                $who,
                $formatted_amount,
                $category_display,
                $billing_type
            );

            // Send Telegram notification
            $department = $this->db->get_where('staff_department', ['id' => $staff->department])->row();
            $department_name = $department ? $department->name : 'Unknown Department';
            $today_display = date('d M Y');
            $formatted_amount = number_format($amount, 2);

			$bot_token = $telegram_bot;
			$chat_id = $telegram_chatID;

            $tg_message = "💰 *New Fund Requisition from {$staff_name}*\n\n" .
                        "📅 *Date:* {$today_display}\n" .
                        "👤 *Name:* {$staff_name}\n" .
                        "🏢 *Department:* {$department_name}\n\n" .
                        "📂 *Category:* {$category_display}\n" .
                        "💵 *Amount:* BDT {$formatted_amount}\n" .
                        "🏷️ *Billing Type:* {$billing_type}\n" .
                        "📝 *Reason:* {$reason}\n\n" .
                        "🔗 [Review Request](" . base_url('fund_requisition') . ")";

            $payload = [
                'chat_id' => $chat_id,
                'text' => $tg_message,
                'parse_mode' => 'Markdown',
                'disable_web_page_preview' => true,
            ];

            $url_tg = "https://api.telegram.org/bot{$bot_token}/sendMessage";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url_tg);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_exec($ch);
            curl_close($ch);

            // Get FCM tokens based on requestor role (same logic as email)
            $recipientTokens = [];
            $requestor_role = $this->db->select('login_credential.role')
                ->from('staff')
                ->join('login_credential', 'login_credential.user_id = staff.id')
                ->where('staff.id', $staff_id)
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
                    'type'                => 'fund_requisition_request',
                    'fund_requisition_id' => (string)$req_id,
                    'staff_id'            => (string)$staff_id,
                    'action'              => 'review'
                ]);
            } else {
                $this->log_message("INFO: No recipient FCM tokens found for fund_requisition_id={$req_id}");
            }

            // Get current user info with role
            $requestor = $this->db
                ->select('staff.name, staff.department, staff.email, login_credential.role')
                ->join('login_credential', 'login_credential.user_id = staff.id')
                ->where('staff.id', $staff_id)
                ->get('staff')
                ->row_array();

            $department_id = $requestor['department'] ?? 0;
            $requestor_name = $requestor['name'] ?? 'Staff';
            $requestor_role = $requestor['role'];

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

            if (!empty($to_email)) {

                // Compose email
                $mail_subject = 'Fund Requisition Request from ' . $requestor_name;
                $mail_body = "
                <html>
                <body style='font-family:Arial, sans-serif; background:#f9f9f9; padding:20px;'>
                    <table style='max-width:600px; margin:auto; background:#fff; border-radius:8px; padding:20px; box-shadow:0 0 10px rgba(0,0,0,0.05);'>
                    <tr><td style='text-align:center;'><h2 style='color:#0054a6;'>Fund Requisition Request</h2></td></tr>
                    <tr><td style='font-size:15px; color:#333;'>
                        <p>Dear <strong>Concern</strong>,</p>
                        <p><strong>" . htmlspecialchars($requestor_name) . "</strong> has submitted a fund requisition request:</p>
						<p><strong>&#128197; Fund Category:</strong> " . htmlspecialchars($category['name'] ?? '') . "</p>
                        <p><strong>&#128181; Amount:</strong> BDT " . number_format((float)$amount, 2) . "</p>
                        <p><strong>&#128179; Billing Type:</strong> " . htmlspecialchars($billing_type) . "</p>
                       ";
                if (in_array($category_name, ['conveyance', 'convence'])) {
                    $mail_body .= "<p><strong>&#128197; CRM Token No:</strong> " . htmlspecialchars($token) . "</p>";
                }
                $mail_body .= "<p><strong>&#128221; Reason:</strong> " . nl2br(htmlspecialchars($reason)) . "</p>
                        <p style='margin-top:20px;'>Please log in to the system to review and take necessary action.</p>
                        <p style='margin-top:30px;'>Thank you,<br><strong>EMP Team</strong></p>
                        <p style='text-align:center; font-size:14px; color:#888; margin-top:40px;'>From <strong>EMP</strong> with <span style='color:#e63946;'>&#10084;&#65039;</span></p>
                    </td></tr>
                    </table>
                </body>
                </html>";

                $config = $this->email_model->get_email_config();
                $email_data = [
                    'smtp_host'   => $config['smtp_host'],
                    'smtp_auth'   => true,
                    'smtp_user'   => $config['smtp_user'],
                    'smtp_pass'   => $config['smtp_pass'],
                    'smtp_secure' => $config['smtp_encryption'],
                    'smtp_port'   => $config['smtp_port'],
                    'from_email'  => $config['email'],
                    'from_name'   => 'EMP Admin',
                    'to_email'    => $to_email,
                    'to_name'     => $to_name,
                    'subject'     => $mail_subject,
                    'body'        => $mail_body,
                    'cc'          => implode(',', $cc_emails),
                ];

                $this->email_model->send_email_yandex($email_data);
            }

            $url = base_url('fund_requisition/request');
            $array = ['status' => 'success', 'url' => $url];
            set_alert('success', translate('information_has_been_saved_successfully'));
        } else {
            $error = $this->form_validation->error_array();
            $array = ['status' => 'fail', 'error' => $error];
        }

        echo json_encode($array);
    }
}

public function save_ledger()
{
    $fund_id = $this->input->post('fund_id');
    $json_data = $this->input->post('json_data');

    if (empty($fund_id) || empty($json_data)) {
        $this->log_fund_action('LEDGER_SAVE_ERROR', $fund_id, ['error' => 'Missing data'], get_loggedin_user_id());
        set_alert('error', 'Missing data');
        redirect_back();
    }

    $data = [
        'ledger_entries' => $json_data,
        'payment_status' => 4,
        'ledger_added_at' => date('Y-m-d H:i:s')
    ];

    $this->db->where('id', $fund_id);
    $updated = $this->db->update('fund_requisition', $data);

    // Log the ledger save action
    $this->log_fund_action('LEDGER_SAVE', $fund_id, $data, get_loggedin_user_id());

    if ($updated) {
        set_alert('success', 'Ledger saved successfully!');
    } else {
        set_alert('error', 'Database update failed!');
    }

    redirect($_SERVER['HTTP_REFERER']);
}


    public function request_delete($id = '')
    {
        if (get_permission('fund_requisition_request', 'is_delete')) {
            // Log before deletion
            $fund_data = $this->db->get_where('fund_requisition', ['id' => $id, 'status' => 1])->row_array();
            if ($fund_data) {
                $this->log_fund_action('REQUEST_DELETE', $id, $fund_data, get_loggedin_user_id());
            }

            $this->db->where('id', $id);
            $this->db->where('status', 1);
            $this->db->delete('fund_requisition');
        }
    }

    public function getRequestDetails()
    {
        if (get_permission('fund_requisition_request', 'is_view')) {
            $this->data['fund_id'] = $this->input->post('id');
            $this->load->view('fund_requisition/modal_request_details', $this->data);
        }
    }

    // Get fund requisition details for approval modal
    public function getFundRequisitionDetails()
    {
        if (get_permission('fund_requisition_manage', 'is_view')) {
            $this->data['fund_id'] = $this->input->post('id');
            $this->load->view('fund_requisition/approvel_modalView', $this->data);
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

	public function get_tracker_tasks_by_milestone()
	{
		$milestone_id = $this->input->post('milestone_id');
		$staff_id = $this->input->post('staff_id');

		$tasks = [];
		if ($milestone_id) {
			$this->db->select('id, task_title');
			$this->db->from('tracker_issues');
			$this->db->where('milestone', $milestone_id);

			// Filter by staff_id if provided
			if ($staff_id) {
				$this->db->where('assigned_to', $staff_id);
			}

			$query = $this->db->get();

			foreach ($query->result() as $row) {
				$tasks[$row->id] = $row->task_title;
			}
		}

		echo json_encode($tasks);
	}

    public function download($id = '', $file = '')
    {
        if (!empty($id) && !empty($file)) {

            $this->db->select('orig_file_name,enc_file_name');
            $this->db->where('id', $id);
            $fund = $this->db->get('fund_requisition')->row();
            if ($file != $fund->enc_file_name) {
                access_denied();
            }
            $this->load->helper('download');
            $fileData = file_get_contents('./uploads/attachments/fund_requisition/' . $fund->enc_file_name);
            force_download($fund->orig_file_name, $fileData);
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
            $get_salary = $this->fundrequisition_model->getBasicSalary($staff_id, $amount);
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
        $getValidation = $this->fundrequisition_model->getAdvanceValidMonth($staff_id, $month);
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
     * Sync fund requisition with cashbook
     */
    private function sync_with_cashbook($fund_id)
    {
        try {
            $this->cashbook_model->syncFundRequisition($fund_id);
            $this->log_fund_action('CASHBOOK_SYNC', $fund_id, ['status' => 'success'], get_loggedin_user_id());
        } catch (Exception $e) {
            $this->log_fund_action('CASHBOOK_SYNC_ERROR', $fund_id, ['error' => $e->getMessage()], get_loggedin_user_id());
        }
    }

    /**
     * Log fund requisition actions
     */
    private function log_fund_action($action, $fund_id, $data, $user_id)
    {
        $timestamp = date('Y-m-d H:i:s');
        $user_info = $this->db->select('name, staff_id')->where('id', $user_id)->get('staff')->row();
        $user_name = $user_info ? $user_info->name . ' (' . $user_info->staff_id . ')' : 'Unknown';

        $logEntry = "[$timestamp] ACTION: $action | FUND_ID: $fund_id | USER: $user_name | DATA: " . json_encode($data) . PHP_EOL;

        $logDir = dirname($this->fundLogFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        if (file_put_contents($this->fundLogFile, $logEntry, FILE_APPEND | LOCK_EX) === false) {
            error_log("Fund Requisition Log: $logEntry");
        }
    }

    // AJAX endpoint for fund requisition approval
    public function fund_requisition_ajax()
    {
        header('Content-Type: application/json');

        if (!get_permission('fund_requisition_manage', 'is_add')) {
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
        $existing = $this->db->get_where('fund_requisition', ['id' => $id])->row();

        if (!$existing) {
            echo json_encode(['status' => 'error', 'message' => 'Fund requisition not found']);
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

        // If role is Admin, HR or Accounts (1,2,5), allow ledger adjustment
        if (in_array(loggedin_role_id(), [1, 2, 5])) {
            $adjust_amount = $this->input->post('adjust_amount');
            $ledger_status = $this->input->post('ledger_status');
            if (!empty($adjust_amount)) {
                $update_data['adjust_amount'] = (float) $adjust_amount;
            }
            if (!empty($ledger_status)) {
                $update_data['ledger_status'] = $ledger_status;
            }
        }

        // Update the fund requisition
        $this->db->where('id', $id);
        $result = $this->db->update('fund_requisition', $update_data);

        // 📝 Log the AJAX update
        $this->log_fund_action('AJAX_UPDATE', $id, $update_data, $logged_in_user_id);

        // 💰 Sync with Cashbook if payment is completed
        if ($result && $payment_status == 2) {
            $this->sync_with_cashbook($id);
        }

        if ($result) {
            // Send notifications (same logic as in index method)
            $funds = $this->db->get_where('fund_requisition', ['id' => $id])->row();

            if ($funds) {
                $userID = $funds->staff_id;

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
                        $message = 'Dear ' . $staff_name . ', your fund requisition has been accepted and is awaiting for accounts review.';
                    } elseif ($payment_status == 2) {
                        $message = 'Dear ' . $staff_name . ', your fund requisition has been approved and the payment is marked as completed by ' . $issuer_name . '.';
                    } else {
                        $message = 'Dear ' . $staff_name . ', your fund requisition has been approved by ' . $issuer_name . '.';
                    }
                } else {
                    $message = 'Dear ' . $staff_name . ', your fund requisition request has been ' . strtolower($status_label) . ' by ' . $issuer_name . '.';
                }

                // Insert notification
                $this->db->insert('notifications', [
                    'user_id'    => $userID,
                    'type'       => 'fund_requisition',
                    'title'      => 'Fund Requisition ' . $status_label,
                    'message'    => $message,
                    'url'        => base_url('fund_requisition/request'),
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
                    $this->send_fcm_notification('Fund Requisition ' . $status_label, $message, '', $tokens, [
                        'type'                => 'fund_requisition_status_update',
                        'fund_requisition_id' => (string)$id,
                        'status'              => (string)$status,
                        'action'              => 'view'
                    ]);
                }
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Fund requisition updated successfully',
                'fund_status' => $status,
                'payment_status' => $payment_status
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update fund requisition']);
        }
    }

    // AJAX endpoint for staff search
    public function search_staff()
    {
        header('Content-Type: application/json');

        if (!get_permission('fund_requisition_manage', 'is_view')) {
            echo json_encode(['status' => 'error', 'message' => 'Access denied']);
            return;
        }

        $search_term = $this->input->post('search_term');

        if (empty($search_term) || strlen($search_term) < 2) {
            echo json_encode(['status' => 'error', 'message' => 'Search term too short']);
            return;
        }

        // Search for staff by name
        $this->db->select('s.id, s.name, s.staff_id');
        $this->db->from('staff s');
        $this->db->join('login_credential lc', 'lc.user_id = s.id', 'left');
        $this->db->like('s.name', $search_term);
        $this->db->where('lc.active', 1);
        $this->db->limit(1);
        $staff = $this->db->get()->row();

        if (!$staff) {
            echo json_encode(['status' => 'error', 'message' => 'Staff not found']);
            return;
        }

        // Get fund requisition summary for this staff
        $this->db->select('
            COUNT(*) as total_requests,
            COALESCE(SUM(amount), 0) as total_amount,
            COALESCE(SUM(CASE WHEN payment_status = 2 THEN amount ELSE 0 END), 0) as paid_amount,
            COALESCE(SUM(CASE WHEN payment_status = 1 THEN amount ELSE 0 END), 0) as pending_amount
        ');
        $this->db->from('fund_requisition');
        $this->db->where('staff_id', $staff->id);
        $summary = $this->db->get()->row();

        $response_data = [
            'staff_name' => $staff->name,
            'total_requests' => $summary->total_requests,
            'total_amount' => number_format($summary->total_amount, 2),
            'paid_amount' => number_format($summary->paid_amount, 2),
            'pending_amount' => number_format($summary->pending_amount, 2)
        ];

        echo json_encode(['status' => 'success', 'data' => $response_data]);
    }

    // Get revenue share data for modal
    public function get_revenue_share_data()
    {
        header('Content-Type: application/json');

        if (!get_permission('fund_requisition_manage', 'is_view')) {
            echo json_encode(['status' => 'error', 'message' => 'Access denied']);
            return;
        }

        $month_year = $this->input->post('month_year');

        if (empty($month_year)) {
            echo json_encode(['status' => 'error', 'message' => 'Month year is required']);
            return;
        }

        try {
            // Convert month year to date format for query
            $date = DateTime::createFromFormat('F Y', $month_year);
            if (!$date) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid month year format']);
                return;
            }

            $query_month = $date->format('Y-m');

            // Get sales revenue for the month (same logic as monthly_sales_revenue)
            $this->db->select('SUM(amount) as total_revenue');
            $this->db->from('cashbook_entries');
            $this->db->where('entry_type', 'in');
            $this->db->where('reference_type', 'sales');
            $this->db->where("DATE_FORMAT(entry_date, '%Y-%m') =", $query_month);
            // Exclude specific clients
            $this->db->where('description NOT LIKE', '%Sunnyat Ali%');
            $this->db->where('description NOT LIKE', '%I3 Technologies%');
            $this->db->where('description NOT LIKE', '%Jerin Apu%');

            $result = $this->db->get()->row();
            $total_revenue = $result ? $result->total_revenue : 0;

            // Calculate 10% revenue share
            $revenue_share = $total_revenue * 0.10;

            // Get detailed entries for breakdown
            $this->db->select('entry_date, description, amount');
            $this->db->from('cashbook_entries');
            $this->db->where('entry_type', 'in');
            $this->db->where('reference_type', 'sales');
            $this->db->where("DATE_FORMAT(entry_date, '%Y-%m') =", $query_month);
            $this->db->where('description NOT LIKE', '%Sunnyat Ali%');
            $this->db->where('description NOT LIKE', '%I3 Technologies%');
            $this->db->where('description NOT LIKE', '%Jerin Apu%');
            $this->db->order_by('entry_date', 'ASC');

            $entries = $this->db->get()->result_array();

            $response_data = [
                'total_revenue' => $total_revenue,
                'revenue_share' => $revenue_share,
                'entries' => $entries
            ];

            echo json_encode(['status' => 'success', 'data' => $response_data]);

        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error processing request: ' . $e->getMessage()]);
        }
    }
}
