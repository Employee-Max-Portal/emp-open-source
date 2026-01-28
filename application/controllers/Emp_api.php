<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Load manually installed JWT classes
require_once(APPPATH . '../assets/vendor/jwt/src/JWT.php');
require_once(APPPATH . '../assets/vendor/jwt/src/Key.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


class Emp_api extends My_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
		$this->load->model('leave_model');
        $this->load->model('email_model');
        $this->load->model('attendance_model');
        $this->load->model('employee_model');
       // $this->load->model('tracker_model');
        $this->load->model('warning_model');
        $this->load->model('sop_model');
        $this->load->model('rdc_model');
        $this->load->model('dashboard_model');

		$this->jwt_key = $this->config->item('jwt_key');
        $this->jwt_exp = $this->config->item('jwt_token_expire_time');

        $this->load->model('notification_model');

		$this->uploadDir = FCPATH . 'uploads/notifications/';
        $this->serviceAccountPath = FCPATH . 'assets/notification/service-account.json';
        $this->logFile = FCPATH . 'assets/notification/logs/fcm_notifications.log';
    }

//EMP API's for mobile app

	private $jwt_key;
    private $jwt_exp;


public function token_validation()
{
    header('Content-Type: application/json');

    $headers = apache_request_headers();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

    if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        echo json_encode(['status' => 'fail', 'message' => 'Authorization token missing or malformed']);
        return;
    }


     $token = $matches[1];

    try {
        // ✅ Decode token
        $decoded = JWT::decode($token, new Key($this->jwt_key, 'HS256'));

        // ✅ Extract payload
        $expTime = $decoded->exp;
        $issuedAt = $decoded->iat;
        $data = $decoded->data ?? [];

        return $this->output
            ->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'valid'      => true,
                'message'    => 'Token is valid.',
                'issued_at'  => date('Y-m-d H:i:s', $issuedAt),
                'expires_at' => date('Y-m-d H:i:s', $expTime)
            ]));
    } catch (\Firebase\JWT\ExpiredException $e) {
        return $this->send_response(401, 'Expired Token', 'The token has expired.');
    } catch (\Exception $e) {
        return $this->send_response(401, 'Invalid Token', 'The token is invalid or corrupted.');
    }
}


 public function login() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return $this->send_response(405, 'Method Not Allowed', 'Only POST requests are allowed.');
    }

    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    if (empty($data['username']) || empty($data['password'])) {
        return $this->send_response(400, 'Bad Request', 'Username/Email and password are required.');
    }

    $username = trim($data['username']);
    $password = $data['password'];

    $this->db->select('lc.*, s.name, s.email, s.department, s.id, s.photo, s.staff_id, r.name as role_name');
    $this->db->from('login_credential lc');
    $this->db->join('staff s', 's.id = lc.user_id');
    $this->db->join('roles r', 'r.id = lc.role');
    $this->db->where('s.email', $username);
    $this->db->or_where('lc.username', $username);
    $this->db->limit(1);
    $query = $this->db->get();

    if ($query->num_rows() === 0) {
        return $this->send_response(401, 'Unauthorized', 'Invalid email or password.');
    }

    $user = $query->row();

    if (!$this->app_lib->verify_password($password, $user->password)) {
        return $this->send_response(401, 'Unauthorized', 'Invalid email or password.');
    }

    if ((int)$user->active !== 1) {
        return $this->send_response(403, 'Forbidden', 'User account is inactive.');
    }

    $departmentName = null;
    if (!empty($user->department)) {
        $departmentName = $this->db
            ->select('name')
            ->where('id', $user->department)
            ->get('staff_department')
            ->row('name');
    }

    $issuedAt = time();
    $expireAt = $issuedAt + $this->jwt_exp;

    $payload = [
        'iss'  => base_url(),
        'iat'  => $issuedAt,
        'exp'  => $expireAt,
        'id'  => $user->id,
        'data' => [
            'user_id'   => $user->id,
            'staff_id'   => $user->staff_id,
            'email'      => $user->email,
            'name'       => $user->name,
            'photo'      => $user->photo,
            'role'       => $user->role_name,
            'department' => $departmentName ?? 'N/A'
        ]
    ];

    $jwt = JWT::encode($payload, $this->jwt_key, 'HS256');

    return $this->output
        ->set_status_header(200)
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'token' => $jwt,
            'user'  => $payload['data']
        ]));
}


    private function send_response($status, $error, $message) {
        return $this->output
            ->set_status_header($status)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'error'   => $error,
                'message' => $message
            ]));
    }


public function auth_reset() {
    // ✅ Only allow POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return $this->send_response(405, 'Method Not Allowed', 'Only POST requests are allowed.');
    }

    // ✅ Get raw JSON input
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    // ✅ Validate
    if (empty($data['email']) || empty($data['password'])) {
        return $this->send_response(400, 'Bad Request', 'Email and new password are required.');
    }

    $email = trim($data['email']);
    $new_password = trim($data['password']);

    // ✅ Fetch user
    $this->db->select('lc.*, s.name, s.email, s.department, s.id, s.photo, s.staff_id, r.name as role_name');
    $this->db->from('login_credential lc');
    $this->db->join('staff s', 's.id = lc.user_id');
    $this->db->join('roles r', 'r.id = lc.role');
    $this->db->where('s.email', $email);
    $this->db->limit(1);
    $query = $this->db->get();

    if ($query->num_rows() === 0) {
        return $this->send_response(404, 'Not Found', 'No user found with that email.');
    }

    $user = $query->row();

    // ✅ Hash password
    $hashed_password = $this->app_lib->pass_hashed($new_password);

    // ✅ Update
    $this->db->where('user_id', $user->id);
    $this->db->update('login_credential', ['password' => $hashed_password]);

    // ✅ Generate JWT
    $issuedAt = time();
    $expireAt = $issuedAt + $this->jwt_exp;

    $departmentName = null;
    if (!empty($user->department)) {
        $departmentName = $this->db
            ->select('name')
            ->where('id', $user->department)
            ->get('staff_department')
            ->row('name');
    }

    $payload = [
        'iss'  => base_url(),
        'iat'  => $issuedAt,
        'exp'  => $expireAt,
        'id'  => $user->id,
        'data' => [
            'user_id'   => $user->id,
            'staff_id'   => $user->staff_id,
            'email'      => $user->email,
            'name'       => $user->name,
            'photo'      => $user->photo,
            'role'       => $user->role_name,
            'department' => $departmentName ?? 'N/A'
        ]
    ];

    $jwt = JWT::encode($payload, $this->jwt_key, 'HS256');

    return $this->output
        ->set_status_header(200)
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'success' => true,
            'message' => 'Password updated successfully.',
            'token'   => $jwt,
            'user'  => $payload['data']
        ]));
}


public function check_account()
{
    // ✅ Only allow POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return $this->send_response(405, 'Method Not Allowed', 'Only POST requests are allowed.');
    }

    // ✅ Get raw JSON input
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    // ✅ Validate input
    if (empty($data['email'])) {
        return $this->send_response(400, 'Bad Request', 'Email field is required.');
    }

    $email = trim($data['email']);

    // ✅ Query database
    $this->db->select('s.id, s.name, s.email');
    $this->db->from('login_credential lc');
    $this->db->join('staff s', 's.id = lc.user_id');
    $this->db->where('s.email', $email);
    $query = $this->db->get();

    if ($query->num_rows() > 0) {
        // ✅ Account found
        $user = $query->row();
        return $this->output
            ->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => 'Valid account',
                'data' => [
                    'email' => $user->email,
                    'name' => $user->name
                ]
            ]));
    } else {
        // ❌ Account not found
        return $this->output
            ->set_status_header(404)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => false,
                'message' => 'Account not found'
            ]));
    }
}

public function leave_types_get()
    {
        $staff_id = $this->verify_user();

        if (!$staff_id) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(401)
                ->set_output(json_encode(['error' => 'Unauthorized']));
        }

        $types = $this->db->select('id, name')->get('leave_category')->result_array();
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($types));
    }

public function leaves_post()
{
    // Token Authentication (expects Bearer Token in Header)
    $staff_id = $this->verify_user();

    if (!$staff_id) {
        return $this->output
            ->set_status_header(401)
            ->set_output(json_encode(['message' => 'Unauthorized']));
    }

    // Get data from form-data
    $leave_type_id = $this->input->post('leave_type_id');
    $reason = $this->input->post('reason');
    $start_date = $this->input->post('start_date');
    $end_date = $this->input->post('end_date');

    // Validate required fields
    if (!$leave_type_id || !$reason || !$start_date || !$end_date) {
        return $this->output
            ->set_status_header(400)
            ->set_output(json_encode(['message' => 'Missing required fields']));
    }

    // Get session_id from config
    $getConfig = $this->db
        ->select('translation, session_id')
        ->get_where('global_settings', ['id' => 1])
        ->row();

    // Handle file upload
    $orig_file_name = '';
    $enc_file_name = '';

    if (!empty($_FILES['attachment_file']['name'])) {
        $upload_path = './uploads/attachments/leave/';
        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = '*'; // Or restrict to 'pdf|jpg|png|docx'
        $config['max_size']         = '2024';
        $config['encrypt_name'] = true;
        $this->upload->initialize($config);
        if ($this->upload->do_upload('attachment_file')) {
            $fileData = $this->upload->data();
            $orig_file_name = $fileData['client_name'];
            $enc_file_name = $fileData['file_name'];
        } else {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode(['message' => 'File upload failed', 'error' => $this->upload->display_errors()]));
        }
    }

    // Prepare leave data
    $leave = [
        'user_id'        => $staff_id,
        'role_id'        => 0,
        'category_id'    => $leave_type_id,
        'reason'         => $reason,
        'start_date'     => $start_date,
        'end_date'       => $end_date,
        'leave_days'     => $this->count_days($start_date, $end_date),
        'apply_date'     => date('Y-m-d'),
        'status'         => 1, // pending
        'approved_by'    => 0,
        'orig_file_name' => $orig_file_name,
        'enc_file_name'  => $enc_file_name,
        'comments'       => '',
        'session_id'     => $getConfig->session_id,
        'branch_id'      => null,
    ];

    // Save to DB
    $this->db->insert('leave_application', $leave);
    $leave_id = $this->db->insert_id();

	$leave_category = $this->db->get_where('leave_category', ['id' => $leave_type_id])->row();
	$leave_title = $leave_category ? $leave_category->name : 'Leave';

	// 1. Get current user info
	$requestor = $this->db
		->select('staff.name, staff.department, staff.email')
		->join('login_credential', 'login_credential.user_id = staff.id')
		->where('staff.id', $staff_id)
		->get('staff')
		->row_array();

	$department_id = $requestor['department'];
	$requestor_name = $requestor['name'];

	$notificationData = array(
		'user_id'   => $staff_id,
		'type'      => 'leave',
		'title'     => $requestor_name . ' Requested a ' . $leave_title . ' for ' . $leave_days . ' day\'s',
		'message'   => $reason,
		'url'        => base_url('leave'),
		'is_read'   => 0,
		'created_at'=> date('Y-m-d H:i:s')
	);

	$this->db->insert('notifications', $notificationData);

	// 2. Get all staff (roles 2, 5)
	$this->db->select('staff.email, staff.name')
		->from('staff')
		->join('login_credential', 'login_credential.user_id = staff.id')
		->where_in('login_credential.role', [3, 5])
		->where('login_credential.active', 1);
	$cc_list = $this->db->get()->result_array();

	// 3. Get manager of same department (role 8)
	$this->db->select('staff.email, staff.name')
		->from('staff')
		->join('login_credential', 'login_credential.user_id = staff.id')
		->where('login_credential.role', 8)
		->where('staff.department', $department_id)
		->where('login_credential.active', 1);
	$manager = $this->db->get()->row_array();

	// Only proceed if a manager exists
	if ($manager && !empty($manager['email'])) {
		$to_name = $manager['name'];
		$to_email = $manager['email'];

		// Build list of CC emails
		$cc_emails = array_column($cc_list, 'email');

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

    // Get requester details for notification
    $staff_details = $this->db->select('id, name, department')
                              ->get_where('staff', ['id' => $staff_id])
                              ->row();

    // Build notification
    $title = 'New Leave Request';
    $leave_days = (int) $this->count_days($start_date, $end_date);
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

    // Get approver tokens
    $recipientTokens = $this->get_leave_approver_tokens(
        $staff_details ? $staff_details->department : null,
        [2, 5],
        8
    );

    // Send FCM notification
    if (!empty($recipientTokens)) {
        $this->send_fcm_notification($title, $body, '', $recipientTokens, [
            'type'      => 'leave_request',
            'leave_id'  => (string)$leave_id,
            'staff_id'  => (string)$staff_id,
            'action'    => 'review'
        ]);
    } else {
        $this->log_message("INFO: No recipient FCM tokens found for leave_id={$leave_id}");
    }

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'message' => 'Leave application submitted successfully.',
            'leave_id' => $leave_id,
            'status' => 'pending',
            'notified' => count($recipientTokens)
        ]));
}

/**
 * Get FCM tokens for HR/Finance (roles $hrFinanceRoles) and the manager (role $managerRole)
 * within the same department (if provided).
 */
/**
 * Always include roles 3 & 5 (global).
 * Also include everyone in the requester's department (which includes role 8 there).
 * If you want "manager-only in department" instead of everyone, set $includeDepartmentAll = false.
 */
private function get_leave_approver_tokens(
    $departmentId,
    array $alwaysRoles = [3, 5],
    $managerRole = 8,
    $includeDepartmentAll = true
) {
    if (empty($departmentId)) {
        // Safety: if no department passed, fall back to only global roles
        $includeDepartmentAll = false;
    }

    $this->db->distinct(); // avoid duplicates from join
    $this->db->select('s.fcm_token')
             ->from('staff s')
             ->join('login_credential lc', 'lc.user_id = s.id', 'inner')
             ->group_start()
                 // OR branch 1: global roles (3,5) anywhere
                 ->where_in('lc.role', $alwaysRoles)
             ->group_end();

    // OR branch 2: department filter
    if ($includeDepartmentAll) {
        // everyone in the same department (includes role 8 and other members)
        $this->db->or_group_start()
                     ->where('s.department', $departmentId)
                 ->group_end();
    } else {
        // only manager (role 8) of the same department
        $this->db->or_group_start()
                     ->where('s.department', $departmentId)
                     ->where('lc.role', $managerRole)
                 ->group_end();
    }

    $this->db->where('s.fcm_token IS NOT NULL', null, false)
             ->where('s.fcm_token !=', '');

    $rows = $this->db->get()->result_array();

    // unique non-empty tokens
    $tokens = [];
    foreach ($rows as $r) {
        $t = $r['fcm_token'] ?? '';
        if ($t !== '') $tokens[$t] = true;
    }
    return array_keys($tokens);
}


/**
 * Send FCM notification to specific tokens (preferred) or all staff if $tokens is null.
 * $extraData is merged into the data payload (handy for deep links / app routing).
 */
public function send_fcm_notification($title, $text, $image = '', $tokens = null, array $extraData = [])
{
    $this->log_message("Starting FCM notification send process");

    if ($tokens === null) {
        // Fallback: all tokens (not recommended for leave)
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

    // Base message data (string values recommended for FCM data)
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

		// Ensure directory exists
		$logDir = dirname($this->logFile);
		if (!is_dir($logDir)) {
			mkdir($logDir, 0755, true);
		}

		// Write to log file
		if (file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX) === false) {
			// Fallback to error_log if file write fails
			error_log("FCM Log: $message");
		}
	}

private function count_days($start, $end) {
    $diff = strtotime($end) - strtotime($start);
    return round($diff / (60 * 60 * 24)) + 1;
}


public function leaves_get()
{
    $staff_id = $this->verify_user();

    log_message('info', 'API Hit: leaves_get() called. staff_id: ' . ($staff_id ?? 'Unauthenticated'));

    // Get the 'limit' query parameter
    $limit = $this->input->get('limit', TRUE); // 'TRUE' enables XSS filtering

    // Build the query
    $this->db->select('
            la.id,
            lc.name AS leave_type,
            la.start_date,
            la.end_date,
            la.leave_days AS days,
            la.apply_date AS applied_at,
            la.status')
        ->from('leave_application la')
        ->join('leave_category lc', 'la.category_id = lc.id', 'left')
        ->where('la.user_id', $staff_id)
        ->order_by('la.apply_date', 'DESC');

    // Apply limit if provided and valid
    if (is_numeric($limit) && $limit > 0) {
        $this->db->limit((int)$limit);
    }

    $applications = $this->db->get()->result_array();

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($applications));
}


public function leave_get($id)
{
    $staff_id = $this->verify_user();

    $leave = $this->db->select('
			la.id,
			lc.name AS leave_type,
			la.start_date,
			la.end_date,
			la.leave_days AS days,
			la.apply_date AS applied_at,
			la.status')
		->from('leave_application la')
		->join('leave_category lc', 'la.category_id = lc.id', 'left')
		->where('la.user_id', $staff_id)
		->where('la.id', $id)
		->order_by('la.apply_date', 'DESC')
		->get()
		->result_array();

    if ($leave) {
        return $this->output->set_content_type('application/json')->set_output(json_encode($leave));
    } else {
        return $this->output->set_status_header(404)->set_output(json_encode(['message' => 'Not Found']));
    }
}


public function leave_balance()
{
    $staff_id = $this->verify_user();
    if (!$staff_id) {
        return $this->output
            ->set_status_header(401)
            ->set_output(json_encode(['message' => 'Unauthorized']));
    }

    // Get employee type (for leave policy)
    $staff = $this->db->get_where('staff', ['id' => $staff_id])->row();
    if (!$staff) {
        return $this->output
            ->set_status_header(404)
            ->set_output(json_encode(['message' => 'Staff not found']));
    }

    $employee_type = $staff->employee_type ?? 'Regular';

    // Fetch all leave categories
    $categories = $this->db->get('leave_category')->result_array();
    $balance_data = [];

    foreach ($categories as $cat) {
        $leave_id = $cat['id'];
        $name = $cat['name'];

        // Get total allowed days from policy (assuming stored in leave_category table)
        $allowed = $cat['days'] ?? 0;

       // Sum total leave days from leave table
		$used = $this->db->select_sum('leave_days')
						 ->where('user_id', $staff_id)
						 ->where('category_id', $leave_id)
						 ->where('status', 2)
						 ->get('leave_application')
						 ->row()
						 ->leave_days;

		// Fallback if null
		$used = $used ?? 0;

        $remaining = max(0, $allowed - $used);

        $balance_data[] = [
            'category' => $name,
            'allowed' => $allowed,
            'used' => $used,
            'remaining' => $remaining
        ];
    }

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'staff_id' => $staff_id,
            'employee_type' => $employee_type,
            'leave_balance' => $balance_data
        ]));
}

public function leave_delete()
{
    $staff_id = $this->verify_user();

    if (!$staff_id) {
        return $this->output
            ->set_status_header(401)
            ->set_output(json_encode(['message' => 'Unauthorized']));
    }

    $leave_id = $this->input->post('leave_id');

    if (!$leave_id) {
        return $this->output
            ->set_status_header(400)
            ->set_output(json_encode(['message' => 'Missing leave_id']));
    }

    // Ensure the leave exists, belongs to the user, and is in status = 1 (pending)
    $leave = $this->db
        ->where('id', $leave_id)
        ->where('user_id', $staff_id)
        ->where('status', 1)
        ->get('leave_application')
        ->row();

    if (!$leave) {
        return $this->output
            ->set_status_header(404)
            ->set_output(json_encode(['message' => 'Leave not found or not deletable']));
    }

    // Delete the leave
    $this->db->where('id', $leave_id)->delete('leave_application');

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode(['message' => 'Leave request deleted successfully']));
}

//attendance
public function attendance_checkin()
{
    $staff_id = $this->verify_user();

    if (!$staff_id) {
        return $this->output
            ->set_status_header(401)
            ->set_output(json_encode(['message' => 'Unauthorized']));
    }

    $ip_address = $this->input->ip_address();


    $data = json_decode(file_get_contents("php://input"), true);
	$remarks =  $data['remarks'] ?? null;

    $date = date('Y-m-d');
    $in_time = date('H:i:s');

    $staff_info = $this->db->get_where('staff', ['id' => $staff_id])->row();
    $branch_id = $staff_info->branch_id;

    $weekends = $this->attendance_model->getWeekendDaysSession($branch_id);
    $day_name = date('l', strtotime($date));
    if (in_array($day_name, $weekends)) {
        return $this->output->set_status_header(403)->set_output(json_encode([
            'message' => "Cannot check in on a weekend ($day_name)."
        ]));
    }

    $holiday_string = $this->attendance_model->getHolidays($branch_id);
    $holiday_array = explode('","', trim($holiday_string, '"'));
    if (in_array($date, $holiday_array)) {
        return $this->output->set_status_header(403)->set_output(json_encode([
            'message' => "Cannot check in on a holiday ($date)."
        ]));
    }

    $late_threshold = strtotime('10:30:59');
    $status = (strtotime($in_time) > $late_threshold) ? 'L' : 'P';

    $existing = $this->db->where(['staff_id' => $staff_id, 'date' => $date])->get('staff_attendance')->row();

    if ($existing) {
        $this->db->where('id', $existing->id)->update('staff_attendance', [
            'in_time' => $in_time,
            'status'  => $status
        ]);
        $status_msg = 'updated';
    } else {
        $this->db->insert('staff_attendance', [
            'staff_id'       => $staff_id,
            'status'         => $status,
            'remark'         => $remarks,
            'qr_code'        => 0,
            'is_manual'      => 1,
            'in_time'        => $in_time,
            'date'           => $date,
            'branch_id'      => $branch_id,
            'ip_address'     => $ip_address,
            'approval_status'=> 'pending'
        ]);
        $status_msg = 'inserted';

        // POST to n8n webhook
        $post_data = [[
            "tenant_id" => 15,
            "event_id" => time(),
            "capture_id" => uniqid(),
            "task_name" => "Face Detection",
            "person_id" => $staff_info->staff_id,
            "group_name" => "Face Detection",
            "name" => $staff_info->name,
            "capture_date" => $date,
            "image_url" => "",
            "check_in" => $in_time,
            "check_out" => null,
            "check_out_image" => null
        ]];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://n8n.emp.com.bd/webhook/308adc14-31ce-4430-80bd-d3f5732ffc71",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($post_data),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json']
        ]);
        curl_exec($curl);
        curl_close($curl);
    }

    return $this->output->set_content_type('application/json')->set_output(json_encode([
        'status' => $status_msg,
        'status_label' => ($status === 'P') ? 'Present' : 'Late',
        'date' => $date,
        'in_time' => $in_time
    ]));
}

public function attendance_checkout()
{
    $staff_id = $this->verify_user();
    if (!$staff_id) {
        return $this->output
            ->set_status_header(401)
            ->set_output(json_encode(['message' => 'Unauthorized']));
    }

    $ip_address = $this->input->ip_address();
    $date = date('Y-m-d');
    $out_time = date('H:i:s');

    $existing = $this->db->where(['staff_id' => $staff_id, 'date' => $date])->get('staff_attendance')->row();

    if ($existing) {
        $this->db->where('id', $existing->id)->update('staff_attendance', [
            'out_time' => $out_time,
            'ip_address' => $ip_address
        ]);
        $status_msg = 'updated';
    } else {
        $status_msg = 'not_found';
    }

    return $this->output->set_content_type('application/json')->set_output(json_encode([
        'status' => $status_msg,
        'date' => $date,
        'out_time' => $out_time
    ]));
}

public function get_today_attendance()
{
    // Verify user and get user ID
    $user_id = $this->verify_user();
    if (!$user_id) {
        return $this->output
            ->set_status_header(401)
            ->set_output(json_encode(['message' => 'Unauthorized']));
    }

    $branchID = $this->input->get('branch_id'); // optional
    $today = date('Y-m-d');

    // Fetch attendance data
    $this->db->select('staff_id, status, in_time, out_time')
             ->from('staff_attendance')
             ->where('date', $today);

    if (!empty($branchID)) {
        $this->db->where('branch_id', $branchID);
    }

    $attendance_data = $this->db->get()->result_array();

    // Map attendance
    $attendance_lookup = [];
    foreach ($attendance_data as $row) {
        $attendance_lookup[$row['staff_id']] = [
            'status' => $row['status'],
            'in_time' => $row['in_time'],
            'out_time' => $row['out_time']
        ];
    }

    // Default values
    $attendance_status = $attendance_lookup[$user_id]['status'] ?? 'A';
    $in_time = $attendance_lookup[$user_id]['in_time'] ?? null;
    $out_time = $attendance_lookup[$user_id]['out_time'] ?? null;

    // Human-readable label
    switch ($attendance_status) {
        case 'P': $attendance_label = 'Present'; break;
        case 'L': $attendance_label = 'Late'; break;
        default:  $attendance_label = 'Absent'; break;
    }

    // Total working hours
    $total_hours = null;
    if (!empty($in_time) && !empty($out_time)) {
        $start = new DateTime($in_time);
        $end = new DateTime($out_time);
        $interval = $start->diff($end);
        $total_hours = $interval->format('%h hr %i min');
    }

    // ✅ Calculate total break duration
    $break_duration = null;
$today_start = $today . ' 00:00:00';
$today_end = $today . ' 23:59:59';

$breaks = $this->db
    ->select('pause_history.*, pauses.name as pause_name')
    ->from('pause_history')
    ->join('pauses', 'pauses.id = pause_history.pause_id', 'left')
    ->where('pause_history.user_id', $user_id)
    ->where('pause_history.start_datetime >=', $today_start)
    ->where('pause_history.start_datetime <=', $today_end)
    ->get()
    ->result();

$total_break_seconds = 0;
$current_time = new DateTime();

foreach ($breaks as $break) {
    if (!empty($break->start_datetime)) {
		$break_label = $break->pause_name;
        $start = new DateTime($break->start_datetime);
        $end = !empty($break->end_datetime) ? new DateTime($break->end_datetime) : $current_time;

        $diff = $start->diff($end);
        $total_break_seconds += ($diff->h * 3600) + ($diff->i * 60) + $diff->s;
    }
}

if ($total_break_seconds > 0) {
    $hours = floor($total_break_seconds / 3600);
    $minutes = floor(($total_break_seconds % 3600) / 60);
    $break_duration = "{$hours} hr {$minutes} min";
}

    // Response
    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'date' => $today,
            'status_code' => $attendance_status,
            'status_label' => $attendance_label,
            'in_time' => $in_time,
            'out_time' => $out_time,
            'total_hours' => $total_hours,
            'break_label' => $break_label,
            'break_duration' => $break_duration
        ]));
}


public function get_attendance_history()
{
    $user_id = $this->verify_user();

    if (!$user_id) {
        return $this->output
            ->set_status_header(401)
            ->set_content_type('application/json')
            ->set_output(json_encode(['message' => 'Unauthorized']));
    }

    // Input params
    $month = (int) $this->input->get('month');
    $year = (int) $this->input->get('year');
    $limit = $this->input->get('limit') ? (int) $this->input->get('limit') : null;
    $week = $this->input->get('week'); // boolean param

    // Determine date range
    if ($week) {
        $from = date('Y-m-d', strtotime('-6 days')); // includes today
        $to = date('Y-m-d');
        $month = (int)date('n');
        $year = (int)date('Y');
    } elseif ($month > 0 && $year > 0) {
        $from = date('Y-m-01', strtotime("$year-$month-01"));
        $to = date('Y-m-t', strtotime($from));
    } else {
        $from = date('Y-m-01');
        $to = date('Y-m-t');
        $month = date('n');
        $year = date('Y');
    }

    // Get staff info for branch
    $staff = $this->db->where('id', $user_id)->get('staff')->row_array();
    $branch_id = $staff ? $staff['branch_id'] : null;

    // Weekend and holiday setup
    $weekends = $this->attendance_model->getWeekendDaysSession($branch_id);
    $holiday_string = $this->attendance_model->getHolidays($branch_id);
    $holiday_array = explode('","', trim($holiday_string, '"'));

    // Get all days in date range (including weekends)
    $validDays = [];
    $current = strtotime($from);
    $end = strtotime($to);

    while ($current <= $end) {
        $date = date('Y-m-d', $current);
        $dayName = date('l', $current);
        $isWeekend = ($dayName === 'Friday' || $dayName === 'Saturday');
        $isHoliday = in_array($date, $holiday_array);

        // Include all days except holidays
        if (!$isHoliday) {
            $validDays[] = $date;
        }
        $current = strtotime('+1 day', $current);
    }

    // Fetch raw attendance
    $attendance_data = $this->db->select('id, staff_id, date, status, in_time, out_time')
        ->from('staff_attendance')
        ->where('staff_id', $user_id)
        ->where('date >=', $from)
        ->where('date <=', $to)
        ->get()
        ->result_array();

    // Fetch leave applications for the date range
    $leave_applications = $this->db->select('la.*, lc.name as leave_category_name')
        ->from('leave_application la')
        ->join('leave_category lc', 'lc.id = la.category_id', 'left')
        ->where('la.user_id', $user_id)
        ->where('la.status', 2) // Only approved leaves
        ->where('la.start_date <=', $to)
        ->where('la.end_date >=', $from)
        ->get()
        ->result_array();

    // Build map of leave days
    $leave_map = [];
    foreach ($leave_applications as $leave) {
        $start = strtotime($leave['start_date']);
        $end = strtotime($leave['end_date']);

        $current_leave_day = $start;
        while ($current_leave_day <= $end) {
            $leave_date = date('Y-m-d', $current_leave_day);
            // Only include leave days that are valid working days
            if (in_array($leave_date, $validDays)) {
                $leave_map[$leave_date] = [
                    'leave_category' => $leave['leave_category_name'],
                    'reason' => $leave['reason'],
                    'status' => 'Leave'
                ];
            }
            $current_leave_day = strtotime('+1 day', $current_leave_day);
        }
    }

    // Build map of attendance by date
    $attendance_map = [];
    foreach ($attendance_data as $row) {
        if (in_array($row['date'], $validDays)) {
            $attendance_map[$row['date']] = $row;
        }
    }

    // Build result
    $result = [];
    foreach ($validDays as $current_date) {
        $row = $attendance_map[$current_date] ?? null;
        $leave_info = $leave_map[$current_date] ?? null;

        $status_code = 'A';
        $status_label = 'Absent';
        $in_time = null;
        $out_time = null;
        $total_hours = null;
        $break_label = null;
        $break_duration = null;
        $leave_category = null;
        $leave_reason = null;

        if ($leave_info) {
            $status_code = 'LV';
            $status_label = $leave_info['status'];
            $leave_category = $leave_info['leave_category'];
            $leave_reason = $leave_info['reason'];
        } elseif ($row) {
            $status_code = $row['status'];
            $status_label = ($status_code === 'P') ? 'Present' : (($status_code === 'L') ? 'Late' : 'Absent');
            $in_time = $row['in_time'];
            $out_time = $row['out_time'];

            if (!empty($in_time) && !empty($out_time)) {
                $start = new DateTime($in_time);
                $end = new DateTime($out_time);
                $interval = $start->diff($end);
                $total_hours = $interval->format('%h hr %i min');
            }

            // Breaks
            $date_start = $current_date . ' 00:00:00';
            $date_end = $current_date . ' 23:59:59';

            $breaks = $this->db
                ->select('pause_history.*, pauses.name as pause_name')
                ->from('pause_history')
                ->join('pauses', 'pauses.id = pause_history.pause_id', 'left')
                ->where('pause_history.user_id', $user_id)
                ->where('pause_history.start_datetime >=', $date_start)
                ->where('pause_history.start_datetime <=', $date_end)
                ->get()
                ->result();

            $total_break_seconds = 0;
            $now = new DateTime();

            foreach ($breaks as $break) {
                $break_label = $break->pause_name;
                $start = new DateTime($break->start_datetime);
                $end = !empty($break->end_datetime) ? new DateTime($break->end_datetime) : $now;
                $diff = $start->diff($end);
                $total_break_seconds += ($diff->h * 3600) + ($diff->i * 60) + $diff->s;
            }

            if ($total_break_seconds > 0) {
                $hours = floor($total_break_seconds / 3600);
                $minutes = floor(($total_break_seconds % 3600) / 60);
                $break_duration = "{$hours} hr {$minutes} min";
            }
        }

        $result[] = [
            'date' => $current_date,
            'status_code' => $status_code,
            'status_label' => $status_label,
            'in_time' => $in_time,
            'out_time' => $out_time,
            'total_hours' => $total_hours,
            'break_label' => $break_label,
            'break_duration' => $break_duration,
            'leave_category' => $leave_category,
            'leave_reason' => $leave_reason
        ];
    }

    if ($week) {
        // Sort descending
        usort($result, fn($a, $b) => strcmp($b['date'], $a['date']));
    } else {
        // Sort ascending
        usort($result, fn($a, $b) => strcmp($a['date'], $b['date']));
    }

    if ($limit) {
        $result = array_slice($result, 0, $limit);
    }

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'status' => true,
            'month' => $month,
            'year' => $year,
            'from_date' => $from,
            'to_date' => $to,
            'limit' => $limit,
            'data' => $result
        ]));
}

//attendance


//assets

public function get_assets()
{
    $user_id = $this->verify_user();
    if (!$user_id) {
        return $this->output
            ->set_status_header(401)
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
    }

    $assets = $this->db
        ->select('
            assets.id,
            assets_category.name AS assets_category,
            assets.asset_name,
            assets.serial_number,
            assets.brand,
            assets.specification,
            staff.name AS assigned_employee,
            assets.status,
            assets.photo,
            assets.remarks
        ')
        ->from('assets')
        ->join('assets_category', 'assets.asset_type = assets_category.id', 'left')
        ->join('staff', 'assets.assigned_to = staff.id', 'left')
        ->order_by('assets.id', 'DESC')
        ->get()
        ->result_array();

    // Append full URL to photo
    $base_photo_url = base_url('uploads/asset_photos/');
    foreach ($assets as &$asset) {
        $asset['photo'] = $asset['photo']
            ? $base_photo_url . $asset['photo']
            : null;
    }

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'status' => true,
            'data' => $assets
        ]));
}

//assets

//organizational chart

public function get_organization_chart()
{
    $user_id = $this->verify_user();
    if (!$user_id) {
        return $this->output
            ->set_status_header(401)
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
    }

    // Fetch all organization chart data
    $org_data = $this->db
        ->select('oc.*, s.staff_id as employee_id, s.name as staff_name, s.photo as staff_photo, sd.name as department_name')
        ->from('organization_chart oc')
        ->join('staff s', 's.id = oc.staff_id')
        ->join('staff_department sd', 'sd.id = oc.department_id')
        ->where('oc.active', 1)
        ->get()
        ->result_array();

    $tree_data = [];
    $departments = [];

    // 1. Find COO
    foreach ($org_data as $item) {
        if ($item['position_type'] === 'COO') {
            $tree_data = [
                'id' => $item['staff_id'],
                'employee_id' => $item['employee_id'],
                'name' => $item['staff_name'],
                'photo' => $item['staff_photo'],
                'designation' => 'COO',
                'children' => []
            ];
            break;
        }
    }

    // 2. Department Heads
    foreach ($org_data as $item) {
        if ($item['position_type'] === 'Head') {
            $dept_id = $item['department_id'];
            if (!isset($departments[$dept_id])) {
                $departments[$dept_id] = [
					'id' => $item['staff_id'],
					'employee_id' => $item['employee_id'],
                    'name' => $item['staff_name'],
                    'photo' => $item['staff_photo'],
                    'designation' => 'Head of ' . $item['department_name'],
                    'children' => [],
                    'department_name' => $item['department_name']
                ];
            } else {
                $departments[$dept_id]['name'] .= ', ' . $item['staff_name'];
            }
        }
    }

    // 3. Incharges
    foreach ($org_data as $item) {
        if ($item['position_type'] === 'Incharge') {
            $dept_id = $item['department_id'];
            if (isset($departments[$dept_id])) {
                $incharge_found = false;
                foreach ($departments[$dept_id]['children'] as &$child) {
                    if (strpos($child['designation'], 'Incharge') !== false) {
                        $child['name'] .= ', ' . $item['staff_name'];
                        $incharge_found = true;
                        break;
                    }
                }
                unset($child);
                if (!$incharge_found) {
                    $departments[$dept_id]['children'][] = [
						'id' => $item['staff_id'],
						'employee_id' => $item['employee_id'],
                        'name' => $item['staff_name'],
                        'photo' => $item['staff_photo'],
                        'designation' => 'Incharge - ' . $departments[$dept_id]['department_name'],
                        'children' => []
                    ];
                }
            }
        }
    }

    // 4. Employees
    $direct_to_coo = [];
    foreach ($org_data as $item) {
        if ($item['position_type'] === 'Employee') {
            $dept_id = $item['department_id'];
            $employee = [
                'id' => $item['staff_id'],
                'employee_id' => $item['employee_id'],
                'name' => $item['staff_name'],
                'photo' => $item['staff_photo'],
                'designation' => 'Employee - ' . $item['department_name']
            ];
            if (isset($departments[$dept_id])) {
                if (!empty($departments[$dept_id]['children'])) {
                    $lastIndex = count($departments[$dept_id]['children']) - 1;
                    $departments[$dept_id]['children'][$lastIndex]['children'][] = $employee;
                } else {
                    $departments[$dept_id]['children'][] = $employee;
                }
            } else {
                $direct_to_coo[] = $employee;
            }
        }
    }

    // 5. Final structure
    $tree_data['children'] = array_values($departments);
    $tree_data['children'] = array_merge($tree_data['children'], $direct_to_coo);

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'status' => true,
            'data' => [$tree_data]
        ]));
}

//organizational chart

//advance salary

public function advance_salary_request()
{
    $staff_id = $this->verify_user();
    if (!$staff_id) {
        return $this->output
            ->set_status_header(401)
            ->set_output(json_encode(['message' => 'Unauthorized']));
    }

    $data = json_decode(file_get_contents("php://input"), true);
    $amount = $data['amount'] ?? null;
    $month_year = $data['month_year'] ?? null;
    $reason = $data['reason'] ?? '';

    // ✅ Basic validation
    if (!$amount || !$month_year) {
        return $this->output
            ->set_status_header(400)
            ->set_output(json_encode(['message' => 'Amount and Month-Year are required.']));
    }

    if (!is_numeric($amount) || $amount > 10000) {
        return $this->output
            ->set_status_header(400)
            ->set_output(json_encode(['message' => 'Amount must be numeric and ≤ 10000.']));
    }

    // ✅ Parse and insert
    $deduct_month = date("m", strtotime($month_year));
    $year = date("Y", strtotime($month_year));
    $request_date = date("Y-m-d H:i:s");

    // Fetch staff info
    $staff = $this->db->get_where('staff', ['id' => $staff_id])->row();
    $branch_id = $staff->branch_id ?? null;

    $insertData = [
        'staff_id' => $staff_id,
        'deduct_month' => $deduct_month,
        'year' => $year,
        'amount' => $amount,
        'reason' => $reason,
        'request_date' => $request_date,
        'branch_id' => $branch_id,
        'status' => 1,
    ];

    $this->db->insert('advance_salary', $insertData);
    $advance_id = $this->db->insert_id();

	// 1. Get current user info
	$requestor = $this->db
		->select('staff.name, staff.department, staff.email')
		->join('login_credential', 'login_credential.user_id = staff.id')
		->where('staff.id', $staff_id)
		->get('staff')
		->row_array();

	$department_id = $requestor['department'];
	$requestor_name = $requestor['name'];

	// Create Notification
	$notificationData = array(
		'user_id'    => $staff_id,
		'type'       => 'advance_salary',
		'title'      => 'Advance Salary Requested by ' . $requestor_name,
		'message'    => 'Dear Concern, ' . $requestor_name . 'has requested an advance salary.',
		'is_read'    => 0,
		'url'        => base_url('advance_salary'),
		'created_at' => date('Y-m-d H:i:s')
	);

	$this->db->insert('notifications', $notificationData);
	// 2. Get all staff (roles 2, 5)
	$this->db->select('staff.email, staff.name')
		->from('staff')
		->join('login_credential', 'login_credential.user_id = staff.id')
		->where_in('login_credential.role', [3, 5])
		->where('login_credential.active', 1);
	$cc_list = $this->db->get()->result_array();

	// 3. Get manager of same department (role 8)
	$this->db->select('staff.email, staff.name')
		->from('staff')
		->join('login_credential', 'login_credential.user_id = staff.id')
		->where('login_credential.role', 8)
		->where('staff.department', $department_id)
		->where('login_credential.active', 1);
	$manager = $this->db->get()->row_array();

	// Proceed only if a manager exists
	if ($manager && !empty($manager['email'])) {
		$to_name = $manager['name'];
		$to_email = $manager['email'];
		$cc_emails = array_column($cc_list, 'email');

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

    // Get requester details for notification
    $staff_details = $this->db->select('id, name, department')
                              ->get_where('staff', ['id' => $staff_id])
                              ->row();

    // Build notification
    $title = 'New Advance Salary Request';
    $formatted_amount = number_format($amount, 2);
    $who = $staff_details ? $staff_details->name : 'An employee';
    $month_display = date('F Y', strtotime($month_year));

    $body = sprintf(
        '%s requested %s BDT advance salary for %s.',
        $who,
        $formatted_amount,
        $month_display
    );

    // Get approver tokens
    $recipientTokens = $this->get_fund_approver_tokens(
        $staff_details ? $staff_details->department : null,
        [2, 5],
        8
    );

    // Send FCM notification
    if (!empty($recipientTokens)) {
        $this->send_fcm_notification($title, $body, '', $recipientTokens, [
            'type'            => 'advance_salary_request',
            'advance_id'      => (string)$advance_id,
            'staff_id'        => (string)$staff_id,
            'action'          => 'review'
        ]);
    } else {
        $this->log_message("INFO: No recipient FCM tokens found for advance_id={$advance_id}");
    }

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'message' => 'Advance salary request submitted successfully.',
            'advance_id' => $advance_id,
            'status' => 'pending',
            'notified' => count($recipientTokens)
        ]));
}


public function advance_salary_list()
{
    $staff_id = $this->verify_user();
    if (!$staff_id) {
        return $this->output
            ->set_status_header(401)
            ->set_output(json_encode(['message' => 'Unauthorized']));
    }

    $advances = $this->db->select('id, amount, deduct_month, year, reason, request_date, status,payment_status')
        ->from('advance_salary')
        ->where('staff_id', $staff_id)
        ->order_by('request_date', 'DESC')
        ->get()
        ->result_array();

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($advances));
}


public function advance_salary_get($id)
{
    $staff_id = $this->verify_user();

    if (!$staff_id) {
        return $this->output
            ->set_status_header(401)
            ->set_output(json_encode(['message' => 'Unauthorized']));
    }

    $advance = $this->db->select('id, amount, deduct_month, year, reason, request_date, status')
        ->from('advance_salary')
        ->where('id', $id)
        ->where('staff_id', $staff_id)
        ->get()
        ->row_array();

    if ($advance) {
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($advance));
    } else {
        return $this->output
            ->set_status_header(404)
            ->set_output(json_encode(['message' => 'Advance salary request not found.']));
    }
}

public function advance_salary_delete()
{
    $staff_id = $this->verify_user();

    if (!$staff_id) {
        return $this->output
            ->set_status_header(401)
            ->set_output(json_encode(['message' => 'Unauthorized']));
    }

    $advance_id = $this->input->post('advance_id');

    if (!$advance_id) {
        return $this->output
            ->set_status_header(400)
            ->set_output(json_encode(['message' => 'Missing advance_id']));
    }

    // Validate: request must belong to user and be pending
    $advance = $this->db
        ->where('id', $advance_id)
        ->where('staff_id', $staff_id)
        ->where('status', 1) // Only pending requests can be deleted
        ->get('advance_salary')
        ->row();

    if (!$advance) {
        return $this->output
            ->set_status_header(404)
            ->set_output(json_encode(['message' => 'Advance salary request not found or not deletable']));
    }

    $this->db->where('id', $advance_id)->delete('advance_salary');

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode(['message' => 'Advance salary request deleted successfully']));
}
//advance salary

//Fund Requisition

public function fund_types_get()
    {
        $staff_id = $this->verify_user();

        if (!$staff_id) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(401)
                ->set_output(json_encode(['error' => 'Unauthorized']));
        }

        $types = $this->db->select('id, name')->get('fund_category')->result_array();
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($types));
    }


public function fund_requisition_request()
{
    $staff_id = $this->verify_user();
    if (!$staff_id) {
        return $this->output
            ->set_status_header(401)
            ->set_output(json_encode(['message' => 'Unauthorized']));
    }

    // Input from form-data
    $category_id   = $this->input->post('category_id');
    $amount        = $this->input->post('amount');
    $billing_type  = $this->input->post('billing_type');
    $reason        = $this->input->post('reason');
    $token         = $this->input->post('crm_token');

    // Validate required
    if (!$category_id || !$amount || !$billing_type) {
        return $this->output
            ->set_status_header(400)
            ->set_output(json_encode(['message' => 'Missing required fields.']));
    }

    if (!is_numeric($amount) || $amount > 10000) {
        return $this->output
            ->set_status_header(400)
            ->set_output(json_encode(['message' => 'Amount must be numeric and ≤ 10000.']));
    }

    // Get category
    $category = $this->db->select('name')->where('id', $category_id)->get('fund_category')->row_array();
    $category_name = strtolower(trim($category['name'] ?? ''));

    // Token validation for conveyance
    if (in_array($category_name, ['conveyance', 'convence']) && empty($token)) {
        return $this->output
            ->set_status_header(400)
            ->set_output(json_encode(['message' => 'CRM Token No. is required for conveyance category.']));
    }

    // Handle file upload
    $orig_file_name = '';
    $enc_file_name  = '';
    if (!empty($_FILES['attachment_file']['name'])) {
        $upload_path = './uploads/attachments/fund_requisition/';
        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = '*'; // Or restrict to 'pdf|jpg|png|docx'
        $config['max_size']         = '2024';
        $config['encrypt_name'] = true;
        $this->upload->initialize($config);
        if ($this->upload->do_upload('attachment_file')) {
            $fileData = $this->upload->data();
            $orig_file_name = $fileData['client_name'];
            $enc_file_name  = $fileData['file_name'];
        } else {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode(['message' => 'File upload failed', 'error' => $this->upload->display_errors()]));
        }
    }

    // Insert
    $branch_id = $this->db->get_where('staff', ['id' => $staff_id])->row('branch_id');
    $insertData = [
        'staff_id'       => $staff_id,
        'amount'         => $amount,
        'category_id'    => $category_id,
        'reason'         => $reason,
        'token'          => in_array($category_name, ['conveyance', 'convence']) ? $token : null,
        'billing_type'   => $billing_type,
        'request_date'   => date("Y-m-d H:i:s"),
        'branch_id'      => $branch_id,
        'status'         => 1,
        'payment_status' => 1,
        'orig_file_name' => $orig_file_name,
        'enc_file_name'  => $enc_file_name,
    ];
    $this->db->insert('fund_requisition', $insertData);
    $req_id = $this->db->insert_id();

	// Send email & notification
	// Notify Manager (role 8) and CC roles 3, 5
	$requestor = $this->db
		->select('staff.name, staff.department, staff.email')
		->join('login_credential', 'login_credential.user_id = staff.id')
		->where('staff.id', $staff_id)
		->get('staff')
		->row_array();

	$department_id = $requestor['department'] ?? 0;
	$requestor_name = $requestor['name'] ?? 'Staff';

	//Send system Notification
	$this->db->insert('notifications', [
		'user_id'    => $staff_id,
		'type'       => 'fund_requisition',
		'title'      => 'Fund Requisition Requested by ' . $requestor_name,
		'message'    => 'Dear Concern, ' . $requestor_name . ' has submitted a fund requisition',
		'is_read'    => 0,
		'url'        => base_url('fund_requisition'),
		'created_at' => date('Y-m-d H:i:s'),
	]);
	$cc_list = $this->db
		->select('staff.email, staff.name')
		->from('staff')
		->join('login_credential', 'login_credential.user_id = staff.id')
		->where_in('login_credential.role', [3, 5])
		->where('login_credential.active', 1)
		->get()->result_array();

	$manager = $this->db
		->select('staff.email, staff.name')
		->from('staff')
		->join('login_credential', 'login_credential.user_id = staff.id')
		->where('login_credential.role', 8)
		->where('staff.department', $department_id)
		->where('login_credential.active', 1)
		->get()->row_array();

	if ($manager && !empty($manager['email'])) {
		$to_email = $manager['email'];
		$to_name = $manager['name'];
		$cc_emails = array_column($cc_list, 'email');

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
    // Get requester details (name, department) for the notification
    $staff = $this->db->select('id, name, department')
                      ->get_where('staff', ['id' => $staff_id])
                      ->row();

    // Build notification text
    $title = 'New Fund Requisition Request';

    // Format amount
    $formatted_amount = number_format($amount, 2);
    $who = $staff ? $staff->name : 'An employee';
    $category_display = $category['name'] ?? 'Fund';

    // Final message
    $body = sprintf(
        '%s requested %s BDT for %s (%s).',
        $who,
        $formatted_amount,
        $category_display,
        $billing_type
    );

    // Fetch recipient tokens:
    // - HR/Admin/Finance roles 2 or 5 (adjust to your role mapping)
    // - Manager (role 8) from the same department as the requester
    $recipientTokens = $this->get_fund_approver_tokens(
        $staff ? $staff->department : null,
        [2, 5],
        8
    );

    // Fire FCM (only if we have someone to notify)
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

    // Output
    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'message' => 'Fund requisition submitted successfully.',
            'fund_requisition_id' => $req_id,
            'status' => 'pending',
            'notified' => count($recipientTokens)
        ]));
}

/**
 * Get FCM tokens for fund requisition approvers
 * Similar to get_leave_approver_tokens but for fund requisitions
 */
private function get_fund_approver_tokens(
    $departmentId,
    array $alwaysRoles = [2, 5],
    $managerRole = 8,
    $includeDepartmentAll = true
) {
    if (empty($departmentId)) {
        // Safety: if no department passed, fall back to only global roles
        $includeDepartmentAll = false;
    }

    $this->db->distinct(); // avoid duplicates from join
    $this->db->select('s.fcm_token')
             ->from('staff s')
             ->join('login_credential lc', 'lc.user_id = s.id', 'inner')
             ->group_start()
                 // OR branch 1: global roles (2,5) anywhere
                 ->where_in('lc.role', $alwaysRoles)
             ->group_end();

    // OR branch 2: department filter
    if ($includeDepartmentAll) {
        // everyone in the same department (includes role 8 and other members)
        $this->db->or_group_start()
                     ->where('s.department', $departmentId)
                 ->group_end();
    } else {
        // only manager (role 8) of the same department
        $this->db->or_group_start()
                     ->where('s.department', $departmentId)
                     ->where('lc.role', $managerRole)
                 ->group_end();
    }

    $this->db->where('s.fcm_token IS NOT NULL', null, false)
             ->where('s.fcm_token !=', '');

    $rows = $this->db->get()->result_array();

    // unique non-empty tokens
    $tokens = [];
    foreach ($rows as $r) {
        $t = $r['fcm_token'] ?? '';
        if ($t !== '') $tokens[$t] = true;
    }
    return array_keys($tokens);
}

public function get_fund_requisitions()
{
    $staff_id = $this->verify_user();
    if (!$staff_id) {
        return $this->output
            ->set_status_header(401)
            ->set_output(json_encode(['message' => 'Unauthorized']));
    }

    $requisitions = $this->db
        ->select('fr.id, fr.amount, fc.name AS category, fr.billing_type, fr.token, fr.reason, fr.request_date, fr.status, fr.payment_status, fr.orig_file_name, fr.enc_file_name')
        ->from('fund_requisition fr')
        ->join('fund_category fc', 'fc.id = fr.category_id', 'left')
        ->where('fr.staff_id', $staff_id)
        ->order_by('fr.request_date', 'DESC')
        ->get()
        ->result_array();

    // Append full attachment URL if exists
    foreach ($requisitions as &$req) {
        $req['attachment_url'] = $req['enc_file_name']
            ? base_url('uploads/attachments/fund_requisition/' . $req['enc_file_name'])
            : null;
    }

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($requisitions));
}

public function get_fund_requisition_by_id($id)
{
    $staff_id = $this->verify_user();
    if (!$staff_id) {
        return $this->output
            ->set_status_header(401)
            ->set_output(json_encode(['message' => 'Unauthorized']));
    }

    $requisition = $this->db
        ->select('fr.id, fr.amount, fc.name AS category, fr.billing_type, fr.token, fr.reason, fr.request_date, fr.status, fr.payment_status, fr.orig_file_name, fr.enc_file_name')
        ->from('fund_requisition fr')
        ->join('fund_category fc', 'fc.id = fr.category_id', 'left')
        ->where('fr.id', $id)
        ->where('fr.staff_id', $staff_id)
        ->get()
        ->row_array();

    if (!$requisition) {
        return $this->output
            ->set_status_header(404)
            ->set_output(json_encode(['message' => 'Fund requisition not found.']));
    }

    // Add attachment URL
    $requisition['attachment_url'] = $requisition['enc_file_name']
        ? base_url('uploads/attachments/fund_requisition/' . $requisition['enc_file_name'])
        : null;

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($requisition));
}

public function fund_requisition_delete()
{
    $staff_id = $this->verify_user();

    if (!$staff_id) {
        return $this->output
            ->set_status_header(401)
            ->set_output(json_encode(['message' => 'Unauthorized']));
    }

    $fund_id = $this->input->post('fund_id');
    if (!$fund_id) {
        return $this->output
            ->set_status_header(400)
            ->set_output(json_encode(['message' => 'Missing fund_id']));
    }

    // Check if the fund requisition exists for this staff and is pending
    $this->db->where('id', $fund_id);
    $this->db->where('staff_id', $staff_id);
    $this->db->where('status', 1); // Only pending
    $query = $this->db->get('fund_requisition');

    if ($query->num_rows() === 0) {
        return $this->output
            ->set_status_header(404)
            ->set_output(json_encode(['message' => 'Fund requisition not found or already deleted.']));
    }

    // If found, proceed with delete
    $this->db->where('id', $fund_id);
    $this->db->where('staff_id', $staff_id);
    $this->db->where('status', 1);
    $this->db->delete('fund_requisition');

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode(['message' => 'Fund requisition deleted successfully.']));
}

//Fund Requisition

//Profile
public function get_profile()
{
    $staff_id = $this->verify_user();

    if (!$staff_id) {
        return $this->output
            ->set_status_header(401)
            ->set_output(json_encode(['message' => 'Unauthorized']));
    }

    $this->db->select('staff.staff_id, staff.name, staff.email, staff.mobileno, staff.total_experience, staff.joining_date, staff.present_address,, staff.photo, staff_designation.name as designation, staff_department.name as department, roles.name as role');
	$this->db->from('staff');
	$this->db->join('login_credential', 'login_credential.user_id = staff.id', 'inner');
	$this->db->join('roles', 'roles.id = login_credential.role', 'left');
	$this->db->join('staff_designation', 'staff_designation.id = staff.designation', 'left');
	$this->db->join('staff_department', 'staff_department.id = staff.department', 'left');
	$this->db->where('staff.id', $staff_id);
	$query = $this->db->get();
	$profile = ($query->num_rows() > 0) ? $query->row_array() : null;



    if ($profile) {
        // Add full image URL
        $profile['photo_url'] = !empty($profile['photo'])
            ? base_url('uploads/images/staff/' . $profile['photo'])
            : null;

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($profile));
    }

	if (!$profile) {
		return $this->output
			->set_content_type('application/json')
			->set_status_header(404)
			->set_output(json_encode(['status' => false, 'message' => 'User not found.']));
	}

}

public function profile_update()
{
    $staff_id = $this->verify_user();
    if (!$staff_id) {
        return $this->output
            ->set_status_header(401)
            ->set_output(json_encode(['message' => 'Unauthorized']));
    }

    $this->load->library('form_validation');

    $this->form_validation->set_rules('name', 'Name', 'trim|required');
    $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
    $this->form_validation->set_rules('mobile_no', 'Mobile No', 'trim|required');
    $this->form_validation->set_rules('present_address', 'Present Address', 'trim|required');

    if (!$this->form_validation->run()) {
        return $this->output
            ->set_status_header(400)
            ->set_output(json_encode([
                'message' => 'Validation Failed',
                'errors' => $this->form_validation->error_array()
            ]));
    }

    $data = $this->input->post();

    // Get existing photo from DB
    $staff = $this->db->select('photo')->where('id', $staff_id)->get('staff')->row_array();
    $old_photo = $staff && !empty($staff['photo']) ? $staff['photo'] : 'default.png';

    // Image upload logic (only if file is uploaded)
    $new_photo = $old_photo;
    if (isset($_FILES['user_photo']) && !empty($_FILES['user_photo']['name'])) {
        $upload_path = './uploads/images/staff/';
        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = 'jpg|jpeg|png';
        $config['encrypt_name'] = TRUE;
        $config['overwrite'] = FALSE;

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if ($this->upload->do_upload('user_photo')) {
            // Delete old image if exists
            if (!empty($old_photo) && $old_photo != 'default.png' && file_exists($upload_path . $old_photo)) {
                @unlink($upload_path . $old_photo);
            }
            $new_photo = $this->upload->data('file_name');
        }
    }

    // Build update array
    $update_data = [
        'name' => $data['name'],
        'email' => $data['email'],
        'mobileno' => $data['mobile_no'],
        'present_address' => $data['present_address'],
        'photo' => $new_photo,
    ];

    $this->db->where('id', $staff_id);
    $this->db->update('staff', $update_data);

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode(['message' => 'Profile updated successfully']));
}

//Profile

//Reports
public function get_recent_transactions()
{
    // Verify user
    $staff_id = $this->verify_user();
    if (!$staff_id) {
        return $this->output
            ->set_status_header(401)
            ->set_output(json_encode(['message' => 'Unauthorized']));
    }

    // Get limit from query param
    $limit = $this->input->get('limit', TRUE);

    // Get current month date range
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-t');

    // Get Fund Requisitions for current month
    $this->db->select('fund_requisition.id, fund_requisition.staff_id, fund_requisition.amount, fund_requisition.reason, fund_requisition.request_date as date, fund_requisition.status, fund_requisition.payment_status, fund_category.name as category')
         ->from('fund_requisition')
         ->join('fund_category', 'fund_category.id = fund_requisition.category_id', 'left')
         ->where('DATE(fund_requisition.request_date) >=', $start_date)
         ->where('DATE(fund_requisition.request_date) <=', $end_date)
         ->order_by('fund_requisition.request_date', 'DESC');

    if (!empty($staff_id)) {
        $this->db->where('fund_requisition.staff_id', $staff_id);
    }

    $fund_requisitions = $this->db->get()->result_array();

    // Get Advance Salaries for current month
    $this->db->select('id, staff_id, amount, reason, request_date as date, status, payment_status')
             ->from('advance_salary')
             ->where('DATE(request_date) >=', $start_date)
             ->where('DATE(request_date) <=', $end_date)
             ->order_by('request_date', 'DESC');

    if (!empty($staff_id)) {
        $this->db->where('staff_id', $staff_id);
    }

    $advance_salaries = $this->db->get()->result_array();

    // Tag types
    foreach ($fund_requisitions as &$item) {
        $item['type'] = 'Fund Requisition';
    }

    foreach ($advance_salaries as &$item) {
        $item['type'] = 'Advance Salary';
    }

    // Merge and sort all
    $transactions = array_merge($fund_requisitions, $advance_salaries);
    usort($transactions, function ($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });

    // Apply limit if valid
    if (is_numeric($limit) && $limit > 0) {
        $transactions = array_slice($transactions, 0, (int)$limit);
    }

    // Output
    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'status' => 'success',
            'transactions' => $transactions
        ]));
}

//Reports

//Separation

public function separation_request()
{
    $user_id = $this->verify_user();
    if (!$user_id) {
        return $this->output
            ->set_status_header(401)
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
    }

	$employee = $this->db->get_where('login_credential', ['user_id' => $user_id])->row();
	$employee_role = $employee ? $employee->role : '0';

    $reason = $this->input->post('reason', true);
    $last_working_date = $this->input->post('last_working_date', true);

    if (empty($reason) || empty($last_working_date)) {
        return $this->output
            ->set_status_header(422)
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => false, 'message' => 'Required fields are missing']));
    }

    // Handle file upload
    $orig_file_name = '';
    $enc_file_name = '';

    if (!empty($_FILES['attachment_file']['name'])) {
        $upload_path = './uploads/attachments/separation/';
        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = '*'; // Or restrict to 'pdf|jpg|png|docx'
        $config['max_size']         = '2024';
        $config['encrypt_name'] = true;
        $this->upload->initialize($config);
        if ($this->upload->do_upload('attachment_file')) {
            $fileData = $this->upload->data();
            $orig_file_name = $fileData['client_name'];
            $enc_file_name = $fileData['file_name'];
        } else {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode(['message' => 'File upload failed', 'error' => $this->upload->display_errors()]));
        }
    }

    // Insert into DB
    $data = [
        'user_id' => $user_id,
        'role_id' => $employee_role,
        'session_id' => get_session_id(),
        'title' => 'Resignation Letter',
        'reason' => $reason,
        'last_working_date' => date("Y-m-d", strtotime($last_working_date)),
        'status' => 1,
        'orig_file_name' => $orig_file_name,
        'enc_file_name' => $enc_file_name,
        'created_at' => date("Y-m-d H:i:s"),
        'email_sent' => 0,
    ];
    $this->db->insert('separation_requests', $data);
    $separation_id = $this->db->insert_id();

    // Get requester details for notification
    $staff_details = $this->db->select('id, name, department')
                              ->get_where('staff', ['id' => $user_id])
                              ->row();
	$staff_name = $staff_details ? $staff_details->name : 'An employee';

	$notificationData = array(
		'user_id'    => $user_id,
		'type'       => 'separation',
		'title'      => 'Separation Request Submitted by ' . $staff_name,
		'message' 	 => 'Dear Concern, ' . $staff_name . ' has submitted a resignation letter and it is pending review.',
		'url'        => base_url('separation/lists'),
		'is_read'    => 0,
		'created_at' => date('Y-m-d H:i:s')
	);

	$this->db->insert('notifications', $notificationData);
    // Build notification
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
        [1, 2, 3, 5], // Admin, HR roles for separation
        8
    );

    // Send FCM notification
    if (!empty($recipientTokens)) {
        $this->send_fcm_notification($title, $body, '', $recipientTokens, [
            'type'           => 'separation_request',
            'separation_id'  => (string)$separation_id,
            'staff_id'       => (string)$user_id,
            'action'         => 'review'
        ]);
    } else {
        $this->log_message("INFO: No recipient FCM tokens found for separation_id={$separation_id}");
    }

    // Legacy notification for backward compatibility
    $notification = [
        'user_id' => $user_id,
        'type' => 'separation',
        'title' => 'Separation Request Submitted by ' . $who,
        'message' => 'Dear Concern, ' . $who . ' has submitted a resignation letter and it is pending review.',
        'url' => base_url('separation/lists'),
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    $this->db->insert('notifications', $notification);

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'status' => true,
            'message' => 'Separation request submitted successfully.',
            'separation_id' => $separation_id,
            'notified' => count($recipientTokens)
        ]));
}


public function get_separation_requests()
{
    $user_id = $this->verify_user();
    if (!$user_id) {
        return $this->output
            ->set_status_header(401)
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
    }

    if (loggedin_role_id() == 1 || loggedin_role_id() == 2) {
        $requests = $this->db->order_by('id', 'desc')->get('separation_requests')->result();
    } else {
        $requests = $this->db->where('user_id', $user_id)->order_by('id', 'desc')->get('separation_requests')->result();
    }

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'status' => true,
            'data' => $requests
        ]));
}

public function delete_separation_request()
{
    $user_id = $this->verify_user();
    if (!$user_id) {
        return $this->output
            ->set_status_header(401)
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
    }

    // Read JSON input
    $rawInput = json_decode(file_get_contents("php://input"), true);
    $id = isset($rawInput['id']) ? (int)$rawInput['id'] : null;

    if (empty($id)) {
        return $this->output
            ->set_status_header(400)
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => false, 'message' => 'Missing separation request ID']));
    }

    $where = [
        'status' => 1,
        'user_id' => $user_id,
        'id' => $id,
    ];

    $app = $this->db->where($where)->get('separation_requests')->row_array();
    if (!$app) {
        return $this->output
            ->set_status_header(404)
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => false, 'message' => 'Request not found or cannot be deleted']));
    }

    // Delete file if exists
    $file_path = FCPATH . 'uploads/attachments/separation/' . $app['enc_file_name'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }

    $this->db->where($where)->delete('separation_requests');

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'status' => true,
            'message' => 'Request deleted successfully.'
        ]));
}

//Separation

//break

public function break_types_get()
    {
        $staff_id = $this->verify_user();

        if (!$staff_id) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(401)
                ->set_output(json_encode(['error' => 'Unauthorized']));
        }

        $types = $this->db->select('id, name')->get('pauses')->result_array();
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($types));
    }

public function get_break_status()
{
    $staff_id = $this->verify_user();

    if (!$staff_id) {
        return $this->output->set_status_header(401)->set_output(json_encode(['message' => 'Unauthorized']));
    }

    // Default response
    $response = [
        'is_on_break'    => false,
        'pause_id'       => null,
        'pause_status'   => null,
        'history_id'     => null,
        'start_time'     => null,
        'duration'       => null,
    ];

    // Fetch pause details from staff table
    $status = $this->db
        ->select('pause_id, pause_status, pause_history')
        ->where('id', $staff_id)
        ->get('staff')
        ->row_array();

    if ($status && (int)$status['pause_status'] === 1) {
        $response['is_on_break'] = true;
        $response['pause_id'] = $status['pause_id'];
        $response['pause_status'] = $status['pause_status'];
        $response['history_id'] = $status['pause_history'];

        // Fetch active pause history
        $history = $this->db->get_where('pause_history', [
            'id' => $status['pause_history'],
            'user_id' => $staff_id,
            'status' => 1
        ])->row();

        if ($history) {
            $start = new DateTime($history->start_datetime);
            $now = new DateTime();
            $diff = $start->diff($now);

            $response['start_time'] = $history->start_datetime;
            $response['duration'] = $diff->format('%h hr %i min');
        }
    }

    return $this->output->set_content_type('application/json')->set_output(json_encode($response));
}

public function post_break_action()
{
    $staff_id = $this->verify_user();

    if (!$staff_id) {
        return $this->output->set_status_header(401)->set_output(json_encode(['message' => 'Unauthorized']));
    }

    $input = json_decode(file_get_contents("php://input"), true);
    $action = $input['action'] ?? null;

    if (!$action || !in_array($action, ['start', 'end'])) {
        return $this->output->set_status_header(400)->set_output(json_encode(['message' => 'Invalid action']));
    }

    if ($action === 'start') {
        $break_id = $input['break_id'] ?? null;

        if (!$break_id) {
            return $this->output->set_status_header(400)->set_output(json_encode(['message' => 'Break ID required']));
        }

        // Insert into pause_history
        $this->db->insert('pause_history', [
            'user_id' => $staff_id,
            'pause_id' => $break_id,
            'start_datetime' => date('Y-m-d H:i:s'),
            'status' => 1
        ]);

        $history_id = $this->db->insert_id();

        // Update staff table
        $this->db->where('id', $staff_id)->update('staff', [
            'pause_status' => 1,
            'pause_id' => $break_id,
            'pause_history' => $history_id
        ]);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['message' => 'Break started.', 'history_id' => $history_id]));

    } elseif ($action === 'end') {
        $history_id = $input['history_id'] ?? null;

        if (!$history_id) {
            return $this->output->set_status_header(400)->set_output(json_encode(['message' => 'History ID required']));
        }

        // Get break history
        $history = $this->db
            ->where('id', $history_id)
            ->where('user_id', $staff_id)
            ->where('status', 1)
            ->get('pause_history')
            ->row();

        if (!$history) {
            return $this->output->set_status_header(404)->set_output(json_encode(['message' => 'Active break not found']));
        }

        $start = new DateTime($history->start_datetime);
        $now = new DateTime();
        $minutes = $start->diff($now)->i + ($start->diff($now)->h * 60);

        // Require remarks if duration > 30 minutes
        if ($minutes > 30 && empty($input['remarks'])) {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode(['message' => 'Remarks required if break exceeded 30 minutes.']));
        }

        // Update break history
        $this->db->where('id', $history_id)->update('pause_history', [
            'end_datetime' => $now->format('Y-m-d H:i:s'),
            'remarks' => $input['remarks'] ?? null,
            'status' => 0
        ]);

        // Update staff table
        $this->db->where('id', $staff_id)->update('staff', [
            'pause_status' => 0,
            'pause_id' => null,
            'pause_history' => null
        ]);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['message' => 'Break ended successfully.']));
    }
}

//break

//rules and policy

public function get_rules_policies()
{
    // Verify user
    $staff_id = $this->verify_user();
    if (!$staff_id) {
        return $this->output
            ->set_status_header(401)
            ->set_output(json_encode(['message' => 'Unauthorized']));
    }

    $categories = $this->db->get('policy_category')->result_array();

	$categorized_policies = [];

	foreach ($categories as $cat) {
		// Get branch/business name
		$this->db->where('id', $cat['branch_id']);
		$business = $this->db->get('branch')->row_array();

		// Get policies for this category
		$this->db->where('category_id', $cat['id']);
		$policies = $this->db->get('policy')->result_array();

		// Attach download links to each policy
		foreach ($policies as &$policy) {
			$policy['download_url'] = base_url('api/download_attachment?file=' . urlencode($policy['document_enc_name']));
		}

		// Combine branch and category names
		$category_name = ($business ? $business['name'] : 'Unknown Branch') . ' - ' . $cat['name'];

		$categorized_policies[] = [
			'category_name' => $category_name,
			'policies' => $policies,
		];
	}


    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'status' => 'success',
            'data' => $categorized_policies
        ]));
}

public function get_policy_description()
{
    $staff_id = $this->verify_user();
    if (!$staff_id) {
        return $this->output
            ->set_status_header(401)
            ->set_output(json_encode(['message' => 'Unauthorized']));
    }

    $policy_id = $this->input->get('id');

    $this->db->select('title, description');
    $this->db->where('id', $policy_id);
    $query = $this->db->get('policy');

    if ($query->num_rows() > 0) {
        $row = $query->row();
        $response = [
            'title' => $row->title,
            'description' => $row->description // this already contains raw HTML
        ];
    } else {
        $response = [
            'title' => 'Not found',
            'description' => 'No description found.'
        ];
    }

    return $this->output
        ->set_content_type('application/json')
        ->set_output(
            json_encode(
                ['status' => 'success', 'data' => $response],
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            )
        );
}

public function download_attachment()
{
    $encrypt_name = urldecode($this->input->get('file'));

    if (!preg_match('/^[^.][-a-z0-9_.]+[a-z]$/i', $encrypt_name)) {
        show_404();
    }

    $row = $this->db->select('document_file_name')->where('document_enc_name', $encrypt_name)->get('policy')->row();

    if (!empty($row)) {
        $file_name = $row->document_file_name;
        $file_path = FCPATH . 'uploads/attachments/documents/' . $encrypt_name;

        if (file_exists($file_path)) {
            $this->load->helper('download');
            return force_download($file_name, file_get_contents($file_path));
        }
    }

    show_404();
}

//rules and policy


//calendar

public function get_calendar_events()
{
    $staff_id = $this->verify_user();
    if (!$staff_id) {
        return $this->output
            ->set_status_header(401)
            ->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
    }

    // ✅ Get query params: month and year
    $month = $this->input->get('month');
    $year  = $this->input->get('year');

    $this->db->where('status', 1);

    // ✅ Validate and apply month/year filter
    if (!empty($month) && !empty($year) && is_numeric($month) && is_numeric($year)) {
        $month = str_pad($month, 2, '0', STR_PAD_LEFT); // ensure 2-digit format
        $start_date = "{$year}-{$month}-01";
        $end_date = date("Y-m-t", strtotime($start_date));

        $this->db->where('start_date >=', $start_date);
        $this->db->where('start_date <=', $end_date);
    }

    $events = $this->db->get('event')->result();

    $response = [];

    foreach ($events as $row) {
        $event = [
            'id'         => $row->id,
            'title'      => $row->title,
            'start_date' => $row->start_date,
            'end_date'   => $row->end_date,
            'type'       => $row->type,
            'icon'       => ($row->type === 'holiday') ? 'umbrella-beach' : get_type_name_by_id('event_types', $row->type, 'icon'),
            'color'      => ($row->type === 'holiday') ? '#dc3545' : '#007bff',
        ];
        $response[] = $event;
    }

    return $this->output
        ->set_status_header(200)
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'status' => true,
            'data'   => $response
        ]));
}


public function get_calendar_event_details()
{
	$staff_id = $this->verify_user();
    if (!$staff_id) {
        return $this->output
            ->set_status_header(401)
            ->set_output(json_encode(['message' => 'Unauthorized']));
    }

    $event_id = $this->input->post('event_id');

    if (empty($event_id)) {
        echo json_encode(['status' => false, 'message' => 'Missing event_id']);
        return;
    }

    $event = $this->db->get_where('event', ['id' => $event_id])->row_array();

    if (!$event) {
        echo json_encode(['status' => false, 'message' => 'Event not found']);
        return;
    }

    $data = [
        'id' => $event['id'],
        'title' => $event['title'],
        'type' => $event['type'],
        'start_date' => _d($event['start_date']),
        'end_date' => _d($event['end_date']),
        'description' => !empty($event['remark']) && $event['type'] !== 'others' ? $event['remark'] : 'N/A',
        'icon' => ($event['type'] === 'holiday') ? 'umbrella-beach' : get_type_name_by_id('event_types', $event['type'], 'icon'),
    ];

    echo json_encode(['status' => true, 'data' => $data]);
}

//calendar

//todo

    // ✅ 1. GET: All TODOs for logged-in user
    public function get_todo_list()
    {
		$staff_id = $this->verify_user();
		if (!$staff_id) {
			return $this->output
				->set_status_header(401)
				->set_output(json_encode(['message' => 'Unauthorized']));
		}


    $warnings = $this->db->where('user_id', $staff_id)
                         ->order_by('issue_date', 'DESC')
                         ->get('warnings')->result();

    $response = [];

    foreach ($warnings as $w) {
        $penalties = $this->db
            ->where('warning_id', $w->id)
            ->get('penalty_days')->result_array();

        // Format datetime
        $created_at = $w->issue_date;
        $clearance_deadline = date("Y-m-d H:i", strtotime($created_at . " +{$w->clearance_time} hours"));
        $status_label = [
            '1' => 'Pending',
            '2' => 'Cleared',
            '3' => 'Rejected'
        ][$w->status] ?? 'Unknown';

        $response[] = [
            'id' => $w->id,
            'user_id' => $w->user_id,
            'issued_by' => $w->issued_by,
            'issue_date' => date("Y-m-d H:i", strtotime($w->issue_date)),
            'reason' => $w->reason,
            'comments' => $w->comments,
            'status' => $status_label,
            'reference' => $w->refrence,
            'category' => $w->category,
            'effect' => $w->effect,
            'clearance_deadline' => $clearance_deadline,
            'cleared_on' => $w->cleared_on ? date("Y-m-d H:i", strtotime($w->cleared_on)) : null,
            'created_at' => $w->issue_date,
            'email_sent_at' => $w->email_sent_at ? date("Y-m-d H:i", strtotime($w->email_sent_at)) : null,
            'penalty' => $w->penalty,
            'total_penalty_days' => array_map(function ($p) {
                return $p['penalty_date'];
            }, $penalties)
        ];
    }
return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => true, 'data' => $response]));

}

// ✅ 2. POST: Submit response to a warning (comments, status, penalty)
    public function submit_todo_response()
    {
		$staff_id = $this->verify_user();
		if (!$staff_id) {
			return $this->output
				->set_status_header(401)
				->set_output(json_encode(['message' => 'Unauthorized']));
		}
        $data = json_decode(file_get_contents("php://input"), true);

        $warning_id     = $data['id'] ?? null;
        $comments       = $data['comments'] ?? '';
        $status         = $data['status'] ?? 1; // 1 = pending, 2 = cleared, 3 = rejected
        $penalty_dates  = $data['penalty_dates'] ?? [];
        $effect         = $data['effect'] ?? '';
        $category       = $data['category'] ?? '';
        $cleared_on     = date('Y-m-d H:i:s');


        // 1. Update warning
        $this->db->where('id', $warning_id)->update('warnings', [
            'comments'     => $comments,
            'status'       => $status,
            'cleared_on'   => $cleared_on,
            'approved_by'  => $staff_id,
            'email_sent'   => 1,
            'email_sent_at' => date('Y-m-d H:i:s'),
        ]);

        // 2. Delete old penalty dates
        $this->db->where('warning_id', $warning_id)->delete('penalty_days');

        // 3. Insert new penalty days
        foreach ($penalty_dates as $date) {
            if (!empty($date)) {
                $this->db->insert('penalty_days', [
                    'staff_id'     => $staff_id,
                    'penalty_date' => $date,
                    'reason'       => $comments,
                    'is_served'    => 0,
                    'created_at'   => $cleared_on,
                    'updated_at'   => $cleared_on,
                    'warning_id'   => $warning_id
                ]);
            }
        }

		// 1. Get current staff info
		$staff = $this->db
			->select('staff.name, staff.department, staff.email, staff.staff_id as employee_id')
			->join('login_credential', 'login_credential.user_id = staff.id')
			->where('staff.id', $staff_id)
			->get('staff')
			->row_array();

		$department_id = $staff['department'];
		$staff_name = $staff['name'];
		$staff_email = $staff['email'];
		$employee_id = $staff['employee_id'];

		// ✅ Optional: Add notification (same as your existing logic)
        $status_label = ['1' => 'Pending', '2' => 'Cleared', '3' => 'Rejected'][$status];

        $this->db->insert('notifications', [
            'user_id'    => $staff_id,
            'type'       => 'warning',
            'title'      => 'Warning Acknowledgement: ' . $status_label,
            'message'    => $staff_name . ' has ' . strtolower($status_label) . ' a warning.',
            'url'        => base_url('todo'),
            'is_read'    => 0,
            'created_at' => $cleared_on
        ]);

		// 2. Issuer name
		$issued_by_name = get_type_name_by_id('staff', $staff_id);

		// 3. Get HR and COO emails (roles 3 and 5)
		$this->db->select('staff.email, staff.name, login_credential.role')
			->from('staff')
			->join('login_credential', 'login_credential.user_id = staff.id')
			->where_in('login_credential.role', [3, 5])
			->where('login_credential.active', 1);
		$hr_coo_list = $this->db->get()->result_array();

		// Extract HR email (first role 3 match)
		$hr_email = '';
		foreach ($hr_coo_list as $person) {
			if ($person['role'] == 5 && !empty($person['email'])) {
				$hr_email = $person['email'];
				break;
			}
		}
		if (empty($hr_email) && !empty($hr_coo_list[0]['email'])) {
			$hr_email = $hr_coo_list[0]['email']; // fallback
		}

		// 4. Get Department Manager (role 8)
		$this->db->select('staff.email, staff.name')
			->from('staff')
			->join('login_credential', 'login_credential.user_id = staff.id')
			->where('login_credential.role', 8)
			->where('staff.department', $department_id)
			->where('login_credential.active', 1);
		$manager = $this->db->get()->row_array();

		// 5. Get Department Manager (role 8)
		$this->db->select('staff.email, staff.name')
			->from('staff')
			->join('login_credential', 'login_credential.user_id = staff.id')
			->where('login_credential.role', 9)
			->where('login_credential.active', 1);
		$disciplinary_manager = $this->db->get()->row_array();

		// 6. Build CC list: COO + Manager
		$cc_emails = [];
		foreach ($hr_coo_list as $person) {
			if ($person['role'] == 3 && !empty($person['email'])) {
				$cc_emails[] = $person['email']; // COO
			}
		}
		if (!empty($manager['email'])) {
			$cc_emails[] = $manager['email']; // Manager
		}
		if (!empty($disciplinary_manager['email'])) {
			$cc_emails[] = $disciplinary_manager['email']; // Manager
		}

		// 7. Prepare the email content
		$comments = $this->input->post('comments');

		$issue_date = date('Y-m-d H:i:s');

		$mail_subject = 'Acknowledgement of Warning Explanation Submitted by ' . $staff_name;

		$mail_body = "
		<html>
		  <body style='font-family:Arial, sans-serif; background:#f9f9f9; padding:20px;'>
			<table style='max-width:600px; margin:auto; background:#fff; border-radius:8px; padding:20px; box-shadow:0 0 10px rgba(0,0,0,0.05);'>
			  <tr><td style='text-align:center;'>
				<h2 style='color:#1976d2;'>Warning Explanation Submitted</h2>
			  </td></tr>
			  <tr><td style='font-size:15px; color:#333;'>
				<p>Dear HR,</p>
				<p>I am writing to formally submit my explanation regarding the warning issued to me that falls under the category of <strong>{$category}</strong> that affects the <strong>{$effect}</strong>.</p>

				<p><strong>Here is my Explanation:</strong></p>
				<p>{$comments}</p>

				<p>Sincerely,<br>
					<strong>{$staff_name}</strong><br>
					Employee ID: {$employee_id}<br>
					Email: {$staff_email}
				</p>
				<p>Thank you.</p>

			  </td></tr>
			</table>
		  </body>
		</html>
		";

		// 7. Email Configuration
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
			'to_email'      => $hr_email,
			'to_name'       => 'HR',
			'subject'       => $mail_subject,
			'body'          => $mail_body,
			'cc'            => implode(',', $cc_emails)
		];

		// 8. Send the email
		$this->email_model->send_email_yandex($email_data);


        // Get staff details for notification
        $staff_details = $this->db->select('id, name, department')
                                  ->get_where('staff', ['id' => $staff_id])
                                  ->row();

        // Build notification
        $title = 'Warning Acknowledgement Submitted';
        $status_labels = ['1' => 'Pending', '2' => 'Cleared', '3' => 'Rejected'];
        $status_label = $status_labels[$status] ?? 'Unknown';
        $who = $staff_details ? $staff_details->name : 'An employee';

        $body = sprintf(
            '%s has %s a warning with status: %s.',
            $who,
            strtolower($status_label === 'Pending' ? 'responded to' : $status_label),
            $status_label
        );

        // Get approver tokens (HR/Admin for warning responses)
        $recipientTokens = $this->get_fund_approver_tokens(
            $staff_details ? $staff_details->department : null,
            [1, 2, 3, 5], // Admin, HR roles for warnings
            8
        );

        // Send FCM notification
        if (!empty($recipientTokens)) {
            $this->send_fcm_notification($title, $body, '', $recipientTokens, [
                'type'       => 'warning_response',
                'warning_id' => (string)$warning_id,
                'staff_id'   => (string)$staff_id,
                'action'     => 'review'
            ]);
        } else {
            $this->log_message("INFO: No recipient FCM tokens found for warning_id={$warning_id}");
        }

        echo json_encode([
            'status' => true,
            'message' => 'Response submitted successfully',
            'notified' => count($recipientTokens)
        ]);
    }


public function get_penalty_report()
{
    // ✅ Allow only POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return $this->send_response(405, 'Method Not Allowed', 'Only POST requests are allowed.');
    }

    // ✅ Authenticate user
    $staff_id = $this->verify_user(); // assumes JWT or token-based check
    if (!$staff_id) {
        return $this->send_response(401, 'Unauthorized', 'You must be logged in.');
    }

    // ✅ Parse input
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    $start_date = !empty($data['start_date']) ? date("Y-m-d", strtotime($data['start_date'])) : null;
    $end_date   = !empty($data['end_date'])   ? date("Y-m-d", strtotime($data['end_date'])) : null;

    // ✅ Get user role from login_credential
    $this->db->select('role');
    $this->db->where('user_id', $staff_id);
    $role_id = $this->db->get('login_credential')->row('role');

    // ✅ Build query inline
    $this->db->select('pd.*, w.reason AS warning_reason, w.issue_date, w.category, w.effect, s.name, s.staff_id');
    $this->db->from('penalty_days pd');
    $this->db->join('warnings w', 'w.id = pd.warning_id', 'left');
    $this->db->join('staff s', 's.id = pd.staff_id', 'left');

    // ✅ Optional date filter
    if ($start_date && $end_date) {
        $this->db->where('pd.penalty_date >=', $start_date);
        $this->db->where('pd.penalty_date <=', $end_date);
    }

    // ✅ Role-based access filtering
    $allowed_roles = [1, 2, 3, 5]; // admins, HR, etc.
    if (!in_array($role_id, $allowed_roles)) {
        $this->db->where('pd.staff_id', $staff_id);
    }

    $query = $this->db->get();
    $penalties = $query->result_array();

    return $this->output
        ->set_status_header(200)
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'success' => true,
            'message' => 'Penalty report fetched successfully.',
            'data' => $penalties
        ]));
}

//todo


//Tracker

	//label

      public function get_labels()
    {
        $user_id = $this->verify_user();
        if (!$user_id) {
            return $this->output
                ->set_status_header(401)
                ->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
        }

        $labels = $this->db->select('id, name, slug, description, task_count, color, created_at, updated_at')
                           ->from('task_labels')
                           ->order_by('created_at', 'DESC')
                           ->get()->result();

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'count' => count($labels),
                'data' => $labels
            ]));
    }

    // ✅ POST /api/labels/create
    public function add_label()
    {
        $user_id = $this->verify_user();
        if (!$user_id) {
            return $this->output
                ->set_status_header(401)
                ->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
        }

        $data = json_decode(file_get_contents("php://input"), true);

        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';
        $color = $data['color'] ?? null;

        if (empty($name)) {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode(['status' => false, 'message' => 'Label name is required']));
        }

        $slug = url_title($name, 'dash', true);

        // Check for duplicate
        if ($this->db->get_where('task_labels', ['slug' => $slug])->num_rows() > 0) {
            return $this->output
                ->set_status_header(409)
                ->set_output(json_encode(['status' => false, 'message' => 'Label with this name already exists']));
        }

        $insertData = [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'color' => $color
        ];

        $this->db->insert('task_labels', $insertData);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'message' => 'Label created successfully',
                'id' => $this->db->insert_id()
            ]));
    }

	//label

	//department

	 // ✅ GET /api/departments
    public function get_all_departments()
    {
        $user_id = $this->verify_user();
        if (!$user_id) {
            return $this->output
                ->set_status_header(401)
                ->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
        }

        $departments = $this->tracker_model->get_all_departments_summary();

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'count' => count($departments),
                'data' => $departments
            ]));
    }


	 // ✅ POST /api/departments/create
    public function create_department()
    {
        $user_id = $this->verify_user();
        if (!$user_id) {
            return $this->output
                ->set_status_header(401)
                ->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['title']) || empty($data['identifier'])) {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode(['status' => false, 'message' => 'Title and Identifier are required']));
        }

        $department = [
            'title' => $data['title'],
            'identifier' => strtoupper($data['identifier']),
            'description' => $data['description'] ?? '',
            'default_status' => $data['default_status'] ?? '',
            'is_private' => !empty($data['is_private']) ? 1 : 0,
            'auto_join' => !empty($data['auto_join']) ? 1 : 0,
            'owner_id' => $user_id,
            'assigned_issuer' => $data['assigned_issuer'] ?? ''
        ];

        $this->db->insert('tracker_departments', $department);
        $department_id = $this->db->insert_id();

        // Add members if provided
        if (!empty($data['members']) && is_array($data['members'])) {
            foreach ($data['members'] as $member_id) {
                $this->db->insert('tracker_department_members', [
                    'department_id' => $department_id,
                    'staff_id' => $member_id
                ]);
            }
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'message' => 'Department created successfully',
                'department_id' => $department_id
            ]));
    }

	// ✅ POST /api/departments/join (with body: { "department_id": 5 })
	public function join_departments()
	{
		$user_id = $this->verify_user();
		if (!$user_id) {
			return $this->output
				->set_status_header(401)
				->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
		}

		$data = json_decode(file_get_contents("php://input"), true);
		$department_id = $data['department_id'] ?? null;

		if (empty($department_id)) {
			return $this->output
				->set_status_header(400)
				->set_output(json_encode(['status' => false, 'message' => 'Department ID is required']));
		}

		// 🔍 Fetch department
		$department = $this->db->get_where('tracker_departments', ['id' => $department_id])->row();
		if (!$department) {
			return $this->output
				->set_status_header(404)
				->set_output(json_encode(['status' => false, 'message' => 'Department not found']));
		}

		// ✅ Already a member?
		if ($this->tracker_model->is_member($department_id, $user_id)) {
			return $this->output
				->set_status_header(200)
				->set_output(json_encode(['status' => false, 'message' => 'You are already a member of this department']));
		}

		// 🔐 Auto join allowed?
		if ((int)$department->auto_join !== 1) {
			return $this->output
				->set_status_header(403)
				->set_output(json_encode(['status' => false, 'message' => 'Joining is not allowed. This department is locked']));
		}

		// ✅ Add user to department
		$this->db->insert('tracker_department_members', [
			'department_id' => $department_id,
			'staff_id' => $user_id
		]);

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'status' => true,
				'message' => 'Joined department successfully'
			]));
	}

	//department

	//components

	public function add_components()
	{
		$user_id = $this->verify_user();
		if (!$user_id) {
			return $this->output
				->set_status_header(401)
				->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
		}

		$data = json_decode(file_get_contents("php://input"), true);

		$department_id = $data['department_id'] ?? null;
		$title = $data['title'] ?? null;
		$description = $data['description'] ?? '';
		$lead_id = $data['lead_id'] ?? null;

		if (empty($title) || empty($department_id)) {
			return $this->output
				->set_status_header(400)
				->set_output(json_encode(['status' => false, 'message' => 'Missing required fields']));
		}

		$insert_data = [
			'department_id' => $department_id,
			'title' => $title,
			'description' => $description,
			'lead_id' => $lead_id,
		];

		$this->db->insert('tracker_components', $insert_data);

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'status' => true,
				'message' => 'Component added successfully'
			]));
	}

	public function get_components($department_identifier = '')
	{
		$user_id = $this->verify_user();
		if (!$user_id) {
			return $this->output
				->set_status_header(401)
				->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
		}

		if (empty($department_identifier)) {
			return $this->output
				->set_status_header(400)
				->set_output(json_encode(['status' => false, 'message' => 'Missing department identifier']));
		}

		$department = $this->tracker_model->get_department_by_identifier($department_identifier);
		if (!$department) {
			return $this->output
				->set_status_header(404)
				->set_output(json_encode(['status' => false, 'message' => 'Department not found']));
		}

		$components = $this->tracker_model->get_components_by_department($department->id);

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'status' => true,
				'department' => $department,
				'components' => $components,
			]));
	}

	public function get_all_components()
	{
		$user_id = $this->verify_user();
		if (!$user_id) {
			return $this->output
				->set_status_header(401)
				->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
		}

		$components = $this->tracker_model->get_all_components();

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'status' => true,
				'components' => $components,
			]));
	}

	//components

	//Milestones
	public function get_milestones($department_identifier = '')
	{
		$user_id = $this->verify_user();
		if (!$user_id) {
			return $this->output
				->set_status_header(401)
				->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
		}

		if (empty($department_identifier)) {
			echo json_encode(['status' => false, 'message' => 'Department identifier is required.']);
			return;
		}

		$department = $this->tracker_model->get_department_by_identifier($department_identifier);
		if (!$department) {
			echo json_encode(['status' => false, 'message' => 'Department not found.']);
			return;
		}

		$milestones = $this->tracker_model->get_milestones_by_department($department->id);

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'status' => true,
				'department' => $department,
				'milestones' => $milestones,
			]));
	}

	public function get_all_milestones()
	{
		$user_id = $this->verify_user();
		if (!$user_id) {
			return $this->output
				->set_status_header(401)
				->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
		}

		$milestones = $this->tracker_model->get_all_milestones();

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'status' => true,
				'milestones' => $milestones,
			]));
	}

	public function add_milestone()
	{
		$user_id = $this->verify_user();
		if (!$user_id) {
			return $this->output
				->set_status_header(401)
				->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
		}

		// 🔄 Parse raw JSON input
		$raw_input = json_decode(trim(file_get_contents('php://input')), true);

		if (!$raw_input) {
			return $this->output
				->set_status_header(400)
				->set_output(json_encode(['status' => false, 'message' => 'Invalid JSON payload']));
		}

		$department_id = $raw_input['department_id'] ?? null;
		$title = $raw_input['title'] ?? null;
		$description = $raw_input['description'] ?? null;
		$status = $raw_input['status'] ?? null;
		$due_date = $raw_input['due_date'] ?? null;

		if (empty($title) || empty($department_id)) {
			return $this->output
				->set_status_header(422)
				->set_output(json_encode(['status' => false, 'message' => 'Missing required fields']));
		}

		$data = [
			'department_id' => $department_id,
			'title' => $title,
			'description' => $description,
			'status' => $status,
			'due_date' => $due_date,
		];

		$this->db->insert('tracker_milestones', $data);

		return $this->output
			->set_status_header(200)
			->set_output(json_encode(['status' => true, 'message' => 'Milestone added successfully']));
	}

	//Milestones

	//issue
	public function add_issue()
	{
		$user_id = $this->verify_user(); // Authenticate API user
		if (!$user_id) {
			return $this->output
				->set_status_header(401)
				->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
		}

		$raw_post = $this->input->raw_input_stream;
		$json_data = json_decode($raw_post, true);

		$department = $json_data['department'];  // This is assumed to be the department ID

		$query = $this->db->query("SELECT identifier FROM tracker_departments WHERE id = ?", array($department));

		if ($query->num_rows() > 0) {
			$row = $query->row();
			$identifier = $row->identifier;  // Return the identifier value
		} else {
			$identifier = null; // Not found
		}

		// Count how many existing rows have the same identifier prefix
		$this->db->like('unique_id', $identifier . '-', 'after');
		$this->db->from('tracker_issues');
		$count = $this->db->count_all_results();
		$next_number = $count + 1;
		$unique_id = $identifier . '-' . $next_number;

		// Prepare data
		$data = [
			'created_by'        => $user_id,
			'unique_id'         => $unique_id,
			'department'        => $identifier,
			'task_title'        => $json_data['task_title'],
			'task_description'  => $json_data['task_description'] ?? '',
			'task_status'       => $json_data['task_status'] ?? 'open',
			'priority_level'    => $json_data['priority_level'] ?? 'normal',
			'assigned_to'       => $json_data['assigned_to'] ?? null,
			'label'             => !empty($json_data['label']) ? implode(',', $json_data['label']) : null, // multi-select
			'component'         => $json_data['component'] ?? null, // ID of selected component
			'estimation_time'   => $json_data['estimation_time'] ?? null,
			'milestone'         => $json_data['milestone'] ?? null, // ID of selected milestone
			'estimated_end_time'=> !empty($json_data['estimated_end_time']) ? date('Y-m-d H:i:s', strtotime($json_data['estimated_end_time'])) : null,
			'parent_issue'      => $json_data['parent_issue'] ?? null,
			'logged_at'         => date('Y-m-d H:i:s')
		];

		$result = $this->db->insert('tracker_issues', $data);

		if ($result) {
			return $this->output
				->set_status_header(200)
				->set_output(json_encode(['status' => true, 'message' => 'New issue added successfully.']));
		} else {
			return $this->output
				->set_status_header(500)
				->set_output(json_encode(['status' => false, 'message' => 'Failed to create issue.']));
		}
	}

public function update_task_issue()
{
	// Only accept POST requests
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		return $this->output
			->set_status_header(405)
			->set_content_type('application/json')
			->set_output(json_encode(['success' => false, 'message' => 'Method Not Allowed']));
	}

	// Authenticate user
	$user_id = $this->verify_user();
	if (!$user_id) {
		return $this->output
			->set_status_header(401)
			->set_content_type('application/json')
			->set_output(json_encode(['success' => false, 'message' => 'Unauthorized']));
	}

	// Parse input
	$data = json_decode($this->input->raw_input_stream, true);
	$task_id = $data['task_id'] ?? null;

	if (empty($task_id)) {
		return $this->output
			->set_status_header(400)
			->set_content_type('application/json')
			->set_output(json_encode(['success' => false, 'message' => 'Missing task_id']));
	}

	// Allowed fields for update
	$allowed_fields = [
		'task_title',
		'task_description',
		'label',
		'priority_level',
		'assigned_to',
		'task_status',
		'component',
		'milestone'
	];

	// Build update data dynamically
	$update_data = [];
	foreach ($allowed_fields as $field) {
		if (isset($data[$field])) {
			$update_data[$field] = $data[$field];
		}
	}

	if (empty($update_data)) {
		return $this->output
			->set_status_header(400)
			->set_content_type('application/json')
			->set_output(json_encode(['success' => false, 'message' => 'No valid fields provided for update']));
	}

	// Execute update
	$this->db->where('id', $task_id);
	$updated = $this->db->update('tracker_issues', $update_data);

	if ($updated) {
		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'success' => true,
				'message' => 'Task updated successfully',
				'task_id' => $task_id,
				'updated_fields' => $update_data
			]));
	} else {
		$error = $this->db->error();
		return $this->output
			->set_status_header(500)
			->set_content_type('application/json')
			->set_output(json_encode([
				'success' => false,
				'message' => 'Database error',
				'error' => $error
			]));
	}
}


public function api_all_issues()
{
	$user_id = $this->verify_user(); // Authenticate API user
	if (!$user_id) {
		return $this->output
			->set_status_header(401)
			->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
	}

    // 1. Get all issues
    $this->db->select('
        ti.id,
        ti.unique_id,
        td.id as department_id,
        td.title as department_name,
        ti.task_title,
        ti.task_description,
        ti.task_status,
        ti.priority_level,
        ti.assigned_to as assigned_user,
        s.name as assigned_to,
        sa.name as created_by,
        ti.label,
        tc.id as component_id,
        tc.title as component_name,
        ti.estimation_time,
        tm.id as milestone_id,
        tm.title as milestone_title,
        ti.estimated_end_time,
        ti.parent_issue,
        ti.logged_at,
        ti.spent_time,
        ti.remaining_time
    ');
    $this->db->from('tracker_issues ti');
    $this->db->join('tracker_departments td', 'td.identifier = ti.department', 'left');
    $this->db->join('staff s', 's.id = ti.assigned_to', 'left');
    $this->db->join('staff sa', 'sa.id = ti.created_by', 'left');
    $this->db->join('tracker_components tc', 'tc.id = ti.component', 'left');
    $this->db->join('tracker_milestones tm', 'tm.id = ti.milestone', 'left');
    $this->db->where('ti.assigned_to', $user_id);
    $this->db->order_by('ti.logged_at', 'DESC');

    $issues = $this->db->get()->result();

    // 2. Prepare label map for quick lookup
    $label_map = [];
    $labels = $this->db->get('task_labels')->result();
    foreach ($labels as $lbl) {
        $label_map[$lbl->id] = $lbl->name;
    }

    // 3. Convert label IDs to label names for each issue
    foreach ($issues as &$issue) {
        if (!empty($issue->label)) {
            $label_ids = explode(',', $issue->label);
            $label_names = [];

            foreach ($label_ids as $lid) {
                $lid = trim($lid);
                if (isset($label_map[$lid])) {
                    $label_names[] = $label_map[$lid];
                }
            }

            $issue->labels = $label_names;
        } else {
            $issue->labels = [];
        }
    }

    // ✅ Return JSON

	return $this->output
		->set_content_type('application/json')
		->set_output(json_encode([
			'success' => true,
			'all_issues' => $issues
		]));
}

public function get_sub_tasks($parentTaskId = null)
{
	header('Content-Type: application/json');
	$user_id = $this->verify_user();
	if (!$user_id) {
		return $this->output
			->set_status_header(401)
			->set_output(json_encode(['success' => false, 'message' => 'Unauthorized']));
	}

	// ✅ Validate method and input
	if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
		return $this->output
			->set_status_header(405)
			->set_output(json_encode(['status' => false, 'message' => 'Method Not Allowed']));
	}

	if (!$parentTaskId || !is_numeric($parentTaskId)) {
		return $this->output
			->set_status_header(400)
			->set_output(json_encode(['status' => false, 'message' => 'Invalid or missing parent task ID']));
	}

	// ✅ Fetch sub-tasks

    $this->db->select('
        ti.id,
        ti.unique_id,
        td.id as department_id,
        td.title as department_name,
        ti.task_title,
        ti.task_description,
        ti.task_status,
        ti.priority_level,
        ti.assigned_to as assigned_user,
        s.name as assigned_to,
        sa.name as created_by,
        ti.label,
        tc.id as component_id,
        tc.title as component_name,
        ti.estimation_time,
        tm.id as milestone_id,
        tm.title as milestone_title,
        ti.estimated_end_time,
        ti.parent_issue,
        ti.logged_at,
        ti.spent_time,
        ti.remaining_time
    ');
    $this->db->from('tracker_issues ti');
    $this->db->join('tracker_departments td', 'td.identifier = ti.department', 'left');
    $this->db->join('staff s', 's.id = ti.assigned_to', 'left');
    $this->db->join('staff sa', 'sa.id = ti.created_by', 'left');
    $this->db->join('tracker_components tc', 'tc.id = ti.component', 'left');
    $this->db->join('tracker_milestones tm', 'tm.id = ti.milestone', 'left');
    $this->db->where('ti.assigned_to', $user_id);
    $this->db->where('ti.parent_issue', $parentTaskId);
    $this->db->where('ti.id!=', $parentTaskId);
    $this->db->where('ti.parent_issue !=', 0);
    $this->db->where('ti.parent_issue IS NOT NULL', null, false);
    $this->db->order_by('ti.logged_at', 'DESC');

    $subTasks = $this->db->get()->result();


    // 2. Prepare label map for quick lookup
    $label_map = [];
    $labels = $this->db->get('task_labels')->result();
    foreach ($labels as $lbl) {
        $label_map[$lbl->id] = $lbl->name;
    }

    // 3. Convert label IDs to label names for each issue
    foreach ($subTasks as &$issue) {
        if (!empty($issue->label)) {
            $label_ids = explode(',', $issue->label);
            $label_names = [];

            foreach ($label_ids as $lid) {
                $lid = trim($lid);
                if (isset($label_map[$lid])) {
                    $label_names[] = $label_map[$lid];
                }
            }

            $issue->labels = $label_names;
        } else {
            $issue->labels = [];
        }
    }
	// ✅ Return response
	return $this->output
		->set_output(json_encode([
			'status' => true,
			'message' => 'Sub-tasks fetched successfully',
			'data' => $subTasks
		]));
}

	//issue

	//comment
	public function get_comments_api($task_unique_id)
{
	if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
		return $this->output
			->set_status_header(405)
			->set_output(json_encode(['success' => false, 'message' => 'Method Not Allowed']));
	}

	$user_id = $this->verify_user();
	if (!$user_id) {
		return $this->output
			->set_status_header(401)
			->set_output(json_encode(['success' => false, 'message' => 'Unauthorized']));
	}

	// Get task ID by unique_id
	$task = $this->db->select('id')->where('id', $task_unique_id)->get('tracker_issues')->row();
	if (!$task) {
		return $this->output
			->set_status_header(404)
			->set_output(json_encode(['success' => false, 'message' => 'Task not found']));
	}

	// Get all comments for this task
	$this->db->select('tc.*, s.name AS author_name, s.photo AS author_photo');
	$this->db->from('tracker_comments tc');
	$this->db->join('staff s', 'tc.author_id = s.id', 'left');
	$this->db->where('tc.task_id', $task->id);
	$this->db->order_by('tc.created_at', 'DESC');
	$comments = $this->db->get()->result();

	// Format comment dates
	foreach ($comments as &$comment) {
		$comment->formatted_date = date('M j, Y g:i A', strtotime($comment->created_at));
	}

	return $this->output
		->set_content_type('application/json')
		->set_output(json_encode([
			'success' => true,
			'comments' => $comments
		]));
}


public function add_comment_api()
{
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		return $this->output
			->set_status_header(405)
			->set_content_type('application/json')
			->set_output(json_encode(['success' => false, 'message' => 'Method Not Allowed']));
	}

	$user_id = $this->verify_user();
	if (!$user_id) {
		return $this->output
			->set_status_header(401)
			->set_output(json_encode(['success' => false, 'message' => 'Unauthorized']));
	}

	$data = json_decode($this->input->raw_input_stream, true);
	$task_unique_id = $data['task_id'] ?? null;
	$comment_text = $data['comment_text'] ?? null;

	if (empty($task_unique_id) || empty($comment_text)) {
		return $this->output
			->set_status_header(400)
			->set_output(json_encode(['success' => false, 'message' => 'Task ID and comment text are required']));
	}

	// Save comment
	$comment_data = [
		'task_id' => $task_unique_id,
		'comment_text' => $comment_text,
		'author_id' => $user_id,
		'created_at' => date('Y-m-d H:i:s')
	];

	$this->db->insert('tracker_comments', $comment_data);
	$comment_id = $this->db->insert_id();

	if (!$comment_id) {
		$error = $this->db->error();
		return $this->output
			->set_status_header(500)
			->set_output(json_encode(['success' => false, 'message' => 'Failed to add comment', 'error' => $error]));
	}

	// Return full comment
	$this->db->select('tc.*, s.name AS author_name, s.photo AS author_photo');
	$this->db->from('tracker_comments tc');
	$this->db->join('staff s', 'tc.author_id = s.id', 'left');
	$this->db->where('tc.id', $comment_id);
	$comment = $this->db->get()->row();
	$comment->formatted_date = date('M j, Y g:i A', strtotime($comment->created_at));

	return $this->output
		->set_content_type('application/json')
		->set_output(json_encode([
			'success' => true,
			'message' => 'Comment added successfully',
			'comment' => $comment
		]));
}

	//comment

	//all status
	public function get_all_statuses()
	{
		$user_id = $this->verify_user();
		if (!$user_id) {
			return $this->output
				->set_status_header(401)
				->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
		}

		$status = $this->tracker_model->get_all_status();

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'status' => true,
				'data' => $status,
			]));
	}
	//all status

	//all priorites
	public function get_all_priorities()
	{
		$user_id = $this->verify_user();
		if (!$user_id) {
			return $this->output
				->set_status_header(401)
				->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
		}

		$priorites = $this->tracker_model->get_all_priorities();

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'status' => true,
				'data' => $priorites,
			]));
	}
	//all priorites



//Tracker

//Work Summaries
public function api_work_summary()
{
   $user_id = $this->verify_user();
		if (!$user_id) {
			return $this->output
				->set_status_header(401)
				->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
		}


    // 📅 Get 'month' parameter from GET, default to current month
    $month = $this->input->get('month') ?: date('Y-m');

    // 🧮 Extract start and end date of the given month
    $start = date("Y-m-01", strtotime($month));
    $end   = date("Y-m-t", strtotime($month));

    // 📦 Fetch filtered summaries
    $summaries = $this->dashboard_model->getWorkSummaries($start, $end, $user_id);

    // ✅ Return JSON response
	return $this->output
		->set_content_type('application/json')
		->set_output(json_encode([
			'status' => true,
			'month' => $month,
			'date_range' => "$start to $end",
			'data' => $summaries
		]));
}

public function get_today_completed_tasks()
{
	$user_id = $this->verify_user();
		if (!$user_id) {
			return $this->output
				->set_status_header(401)
				->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
		}

	$date = $this->input->get('date') ?: date('Y-m-d');

	// Fetch staff name & department
	$staff_data = $this->db
		->select('staff.name AS staff_name, staff_department.name AS department_name')
		->from('staff')
		->join('staff_department', 'staff.department = staff_department.id', 'left')
		->where('staff.id', $user_id)
		->get()
		->row();

	// Fetch task logs
	$this->db->where('staff_id', $user_id);
	$this->db->where('DATE(logged_at)', $date);
	$tasks = $this->db->get('staff_task_log')->result();

	$assigned = [];
	$completed = [];

	foreach ($tasks as $task) {
		$data = [
			'title' => $task->task_title,
			'link' => $task->proof ?? '',
			'planner' => true, // optional if needed
			'time_spent' => $task->task_status == 'Completed'
				? round((strtotime($task->ended_at) - strtotime($task->start_time)) / 3600, 2)
				: 0
		];

		$assigned[] = $data;
		if ($task->task_status == 'Completed') {
			$completed[] = $data;
		}
	}

	 // ✅ Return JSON response
	return $this->output
		->set_content_type('application/json')
		->set_output(json_encode([
			'status' => true,
			'summary_date' => $date,
			'assigned_tasks' => $assigned,
			'completed_tasks' => $completed
		]));
}


public function submit_work_summary()
{
    $user_id = $this->verify_user();
    if (!$user_id) {
        return $this->output
            ->set_status_header(401)
            ->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
        return;
    }

    $data = json_decode($this->input->raw_input_stream, true);
    $today = date('Y-m-d');

    // Staff details
    $staff_data = $this->db
        ->select('staff.name AS staff_name, staff_department.name AS department_name')
        ->from('staff')
        ->join('staff_department', 'staff.department = staff_department.id', 'left')
        ->where('staff.id', $user_id)
        ->get()
        ->row();

    // Prevent duplicates
    $exists = $this->db->where(['user_id' => $user_id, 'summary_date' => $today])->count_all_results('daily_work_summaries');
    if ($exists > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Daily summary already submitted for today.']);
        return;
    }

    // Assigned tasks
    $assigned_tasks = [];
    $planner_count = 0;
    $non_planner_count = 0;
    $planned_titles = [];

    foreach ($data['assigned_tasks'] ?? [] as $task) {
        if (!empty($task['title'])) {
            $is_planner = isset($task['in_planner']) && $task['in_planner'] == 1 ? 1 : 0;
            if ($is_planner) {
                $planner_count++;
                $planned_titles[] = strtolower(trim($task['title']));
            } else {
                $non_planner_count++;
            }
            $assigned_tasks[] = [
                'title' => trim($task['title']),
                'link' => trim($task['link'] ?? ''),
                'planner' => $is_planner
            ];
        }
    }

    // Completed tasks
    $completed_tasks = [];
    $total_time = 0;
    $planned_completed = 0;
    $unplanned_completed = 0;

    foreach ($data['completed_tasks'] ?? [] as $i => $task) {
        if (!empty($task['title'])) {
            $hours = floatval($task['hours_spent'] ?? 0);
            $total_time += $hours;
            $title_lower = strtolower(trim($task['title']));
            if (in_array($title_lower, $planned_titles)) {
                $planned_completed++;
            } else {
                $unplanned_completed++;
            }
            $completed_tasks[] = [
                'task_id' => $i,
                'title' => trim($task['title']),
                'link' => trim($task['proof_link'] ?? ''),
                'time' => $hours,
                'status' => 'pending'
            ];
        }
    }

    // Completion ratio like JS
    $total_planned = count($planned_titles);
    if ($total_planned > 0) {
        $completion_ratio = (100 / $total_planned) * ($planned_completed + $unplanned_completed);
        $completion_ratio = min($completion_ratio, 300); // Cap at 300%
    } else {
        $completion_ratio = 0;
    }

    // Workload score
    $workload = ($planner_count * 100) + ($non_planner_count * 200);

    // Summary payload
    $summary = [
        'user_id' => $user_id,
        'summary_date' => $today,
        'name' => $staff_data->staff_name,
        'department' => $staff_data->department_name,
        'assigned_tasks' => json_encode($assigned_tasks),
        'completed_tasks' => json_encode($completed_tasks),
        'completion_ratio' => round($completion_ratio, 2),
        //'workload_score' => $workload,
        'total_time_spent' => round($total_time, 2),
        'blockers' => trim($data['summary']['blockers'] ?? ''),
        'next_steps' => trim($data['summary']['next_steps'] ?? ''),
        'status' => 1
    ];

    $this->db->insert('daily_work_summaries', $summary);
    $summary_id = $this->db->insert_id();

    // Notification
    $notification = [
        'user_id' => $user_id,
        'type' => 'daily_work_summary',
        'title' => 'Daily Work Summary Submitted',
        'message' => "{$summary['name']} has submitted the daily work summary for {$today}.",
        'url' => base_url('dashboard/work_summary'),
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    $this->db->insert('notifications', $notification);

    // Get staff details for FCM notification
    $staff_details = $this->db->select('id, name, department')
                              ->get_where('staff', ['id' => $user_id])
                              ->row();

    // Build FCM notification
    $title = 'Daily Work Summary Submitted';
    $who = $staff_details ? $staff_details->name : 'An employee';
    $body = sprintf(
        '%s has submitted their daily work summary for %s.',
        $who,
        $today
    );

    // Get approver tokens (HR/Admin roles for work summaries)
    $recipientTokens = $this->get_fund_approver_tokens(
        $staff_details ? $staff_details->department : null,
        [1, 2, 3, 5], // Admin, HR roles
        8
    );

    // Send FCM notification
    if (!empty($recipientTokens)) {
        $this->send_fcm_notification($title, $body, '', $recipientTokens, [
            'type'       => 'work_summary_submitted',
            'summary_id' => (string)$summary_id,
            'staff_id'   => (string)$user_id,
            'date'       => $today,
            'action'     => 'review'
        ]);
    } else {
        $this->log_message("INFO: No recipient FCM tokens found for summary_id={$summary_id}");
    }

    // Telegram
	$bot_token = $telegram_bot;
	$chat_id = $telegram_chatID;

    $message = "📅 *Date:* {$today}\n" .
        "👤 *Name:* {$summary['name']}\n" .
        "🧑‍💻 *Department:* {$summary['department']}\n\n" .
        "📌 *Assigned Tasks:* " . count($assigned_tasks) . "\n";

    foreach ($assigned_tasks as $i => $task) {
        $message .= ($i + 1) . ". {$task['title']} – " . ($task['planner'] ? 'In Planner' : 'Not in Planner') . "\n";
    }

    $message .= "\n✅ *Completed Tasks:* " . count($completed_tasks) . "\n";
    foreach ($completed_tasks as $i => $task) {
        $link = $task['link'] ? "({$task['link']})" : "";
        $message .= ($i + 1) . ". {$task['title']} {$link} – ⏱️ {$task['time']} hrs\n";
    }

    $message .= "\n📊 *Completion Ratio:* " . round($completion_ratio, 2) . "%\n" .
        "🧮 *Workload Score:* {$summary['workload_score']}\n" .
        "⏱️ *Total Time Spent:* {$summary['total_time_spent']} hrs\n";

    if (!empty($summary['blockers'])) {
        $message .= "\n🚫 *Blockers:*\n{$summary['blockers']}\n";
    }
    if (!empty($summary['next_steps'])) {
        $message .= "\n➡ *Next Steps:*\n{$summary['next_steps']}\n";
    }

    $message .= "\n🔗 [View Summary](" . base_url('dashboard/work_summary') . ")";

    $payload = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'Markdown',
        'disable_web_page_preview' => true,
        'reply_markup' => json_encode([
            'inline_keyboard' => [
                [
                    ['text' => '💬 Reply', 'callback_data' => "reply_summary_{$summary_id}"]
                ]
            ]
        ])
    ];

    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    $response = curl_exec($ch);
    curl_close($ch);

    log_message('debug', 'Telegram summary sent: ' . $response);

    echo json_encode(['status' => 'success', 'message' => 'Daily summary submitted successfully.']);
}


//Work Summaries


//Best Employee

public function api_employee_award()
{
   $user_id = $this->verify_user();
		if (!$user_id) {
			return $this->output
				->set_status_header(401)
				->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
		}

    $month = $this->input->get('month') ?: date('Y-m');

    // Get scores from model
    $scores = $this->employee_model->get_all_scores($month);

    // Send JSON response
    echo json_encode([
        'status' => true,
        'month' => $month,
        'data' => $scores
    ]);
}

//Best Employee

//SOP

public function get_sop_list()
{
	$user_id = $this->verify_user();
		if (!$user_id) {
			return $this->output
				->set_status_header(401)
				->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
		}

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        return $this->output
            ->set_status_header(405)
            ->set_output(json_encode([
                'status' => false,
                'message' => 'Method Not Allowed'
            ]));
    }

    $list = $this->sop_model->getList();

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'status' => true,
            'data' => $list
        ]));
}

public function get_sop_details($id = null)
{
	$user_id = $this->verify_user();
		if (!$user_id) {
			return $this->output
				->set_status_header(401)
				->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
		}

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        return $this->output
            ->set_status_header(405)
            ->set_output(json_encode([
                'status' => false,
                'message' => 'Method Not Allowed'
            ]));
    }

    if (!$id || !is_numeric($id)) {
        return $this->output
            ->set_status_header(400)
            ->set_output(json_encode([
                'status' => false,
                'message' => 'Invalid SOP ID'
            ]));
    }

    $sop = $this->sop_model->getDetailsById($id);

    if (!$sop) {
        return $this->output
            ->set_status_header(404)
            ->set_output(json_encode([
                'status' => false,
                'message' => 'SOP not found'
            ]));
    }

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'status' => true,
            'data' => $sop
        ]));
}


//SOP

	//all user
	public function api_staff()
	{
		$user_id = $this->verify_user(); // Authenticate API user
		if (!$user_id) {
			return $this->output
				->set_status_header(401)
				->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
		}


		// 🔄 Get full list from app_lib helper
		$staffList = $this->app_lib->getSelectList('staff');

		// ❌ Remove Superadmin (ID 1)
		unset($staffList[1]);

		// ✅ Format as array of objects (key => value)
		$output = [];
		foreach ($staffList as $id => $name) {
			$output[] = [
				'id' => $id,
				'name' => $name
			];
		}

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'status' => true,
				'data' => $output,
			]));
	}

	public function api_manager()
	{
		$user_id = $this->verify_user(); // Authenticate API user
		if (!$user_id) {
			return $this->output
				->set_status_header(401)
				->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
		}

		// Get staff joined with login_credential where role IN (3, 5, 8)
		$this->db->select('staff.id, staff.name');
		$this->db->from('staff');
		$this->db->join('login_credential', 'login_credential.user_id = staff.id');
		$this->db->where_in('login_credential.role', [3, 5, 8]);
		$query = $this->db->get();

		$staffList = $query->result_array();

		// Format as array of objects (id => name)
		$output = [];
		foreach ($staffList as $row) {
			$output[] = [
				'id' => $row['id'],
				'name' => $row['name']
			];
		}

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'status' => true,
				'data' => $output,
			]));
	}

	//all user

//KPI

public function submit_kpi()
{
	header('Content-Type: application/json');

	// ✅ Token/User Verification
	$user_id = $this->verify_user(); // Returns false if unauthorized
	if (!$user_id) {
		return $this->output
			->set_status_header(401)
			->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
	}

	// ✅ Only POST allowed
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		return $this->output
			->set_status_header(405)
			->set_output(json_encode(['status' => false, 'message' => 'Method Not Allowed']));
	}

	// ✅ Decode JSON input
	$data = json_decode($this->input->raw_input_stream, true);

	// ✅ Basic validation
	if (empty($data['objective_name']) || empty($data['daterange'])) {
		return $this->output
			->set_status_header(400)
			->set_output(json_encode(['status' => false, 'message' => 'Missing required fields']));
	}

	// ✅ Fetch Staff Info
	$staff = $this->db->get_where('staff', ['id' => $user_id])->row();
	if (!$staff) {
		return $this->output
			->set_status_header(404)
			->set_output(json_encode(['status' => false, 'message' => 'Staff not found']));
	}
	$branch_id = $staff->branch_id ?? null;
	$department_id = $staff->department ?? null;

	// ✅ Insert KPI Form
	$formData = [
		'branch_id'      => $branch_id,
		'objective_name' => trim($data['objective_name']),
		'department_id'  => $department_id,
		'staff_id'       => $user_id,
		'manager_id'     => $data['manager_id'] ?? $user_id,
		'daterange'      => trim($data['daterange']),
		'created_at'     => date('Y-m-d H:i:s')
	];
	$this->db->insert('kpi_form', $formData);
	$form_id = $this->db->insert_id();

	// ✅ Insert KPI Details (subtasks)
	$subtasks = $data['subtasks'] ?? [];
	foreach ($subtasks as $subtask) {
		if (!empty($subtask['name']) && isset($subtask['weight'])) {
			$detailData = [
				'kpi_form_id' => $form_id,
				'name'        => trim($subtask['name']),
				'description' => trim($subtask['description'] ?? ''),
				'weight'      => floatval($subtask['weight']),
			];
			$this->db->insert('kpi_form_details', $detailData);
		}
	}

	// ✅ Return Success
	return $this->output
		->set_output(json_encode([
			'status' => true,
			'message' => 'KPI form submitted successfully',
			'form_id' => $form_id
		]));
}

public function update_kpi($id = null)
{
	header('Content-Type: application/json');
	$user_id = $this->verify_user(); // Returns false if unauthorized
		if (!$user_id) {
			return $this->output
				->set_status_header(401)
				->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
		}

	if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
		return $this->output
			->set_status_header(405)
			->set_output(json_encode(['status' => false, 'message' => 'Method Not Allowed']));
	}

	if (!$id || !is_numeric($id)) {
		return $this->output
			->set_status_header(400)
			->set_output(json_encode(['status' => false, 'message' => 'Invalid KPI form ID']));
	}

	$input = json_decode($this->input->raw_input_stream, true);

	// Validate mandatory fields
	if (empty($input['objective_name']) || empty($input['daterange'])) {
		return $this->output
			->set_status_header(422)
			->set_output(json_encode(['status' => false, 'message' => 'Required fields are missing']));
	}

	$staff = $this->db->get_where('staff', ['id' => $user_id])->row();

	// Update form
	$updateData = [
		'branch_id'      => $staff->branch_id,
		'department_id'  => $staff->department,
		'objective_name' => trim($input['objective_name']),
		'staff_id'       => $user_id,
		'manager_id'     => $input['manager_id'] ?? null,
		'daterange'      => trim($input['daterange']),
		'updated_at'     => date('Y-m-d H:i:s')
	];

	$this->db->where('id', $id)->update('kpi_form', $updateData);

	// Replace subtasks
	$this->db->where('kpi_form_id', $id)->delete('kpi_form_details');
	foreach ($input['subtasks'] ?? [] as $task) {
		if (!empty($task['name']) && isset($task['weight'])) {
			$this->db->insert('kpi_form_details', [
				'kpi_form_id' => $id,
				'name'        => trim($task['name']),
				'description' => trim($task['description'] ?? ''),
				'weight'      => floatval($task['weight']),
			]);
		}
	}

	return $this->output
		->set_output(json_encode(['status' => true, 'message' => 'KPI form updated successfully']));
}

public function submit_kpi_rating()
{
	header('Content-Type: application/json');

	$user_id = $this->verify_user(); // Returns false if unauthorized
		if (!$user_id) {
			return $this->output
				->set_status_header(401)
				->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
		}

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		return $this->output
			->set_status_header(405)
			->set_output(json_encode(['status' => false, 'message' => 'Method Not Allowed']));
	}

	$data = json_decode($this->input->raw_input_stream, true);

	if (empty($data['form_id']) || empty($data['rating'])) {
		return $this->output
			->set_status_header(422)
			->set_output(json_encode(['status' => false, 'message' => 'Missing fields']));
	}

	$this->db->where('id', $data['form_id'])->update('kpi_form', ['staff_rating' => floatval($data['rating'])]);


	return $this->output
		->set_output(json_encode(['status' => true, 'message' => 'Rating saved successfully']));
}

public function get_kpi_ratings($form_id = null)
{
	header('Content-Type: application/json');

	$user_id = $this->verify_user(); // Returns false if unauthorized
		if (!$user_id) {
			return $this->output
				->set_status_header(401)
				->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
		}

	// ✅ Method check
	if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
		return $this->output
			->set_status_header(405)
			->set_output(json_encode(['status' => false, 'message' => 'Method Not Allowed']));
	}

	// ✅ Validate form ID
	if (!$form_id || !is_numeric($form_id)) {
		return $this->output
			->set_status_header(400)
			->set_output(json_encode(['status' => false, 'message' => 'Invalid or missing KPI form ID']));
	}

	// ✅ Fetch feedbacks
	$feedbacks = $this->db
		->select('kf.staff_rating, s.name AS submitted_by_name')
		->from('kpi_form AS kf')
		->join('staff AS s', 'kf.staff_id = s.id', 'left')
		->where('kf.id', $form_id)
		->order_by('kf.created_at', 'DESC')
		->get()
		->result_array();

	return $this->output
		->set_output(json_encode([
			'status' => true,
			'message' => 'Ratings fetched successfully',
			'data' => $feedbacks
		]));
}


public function submit_kpi_feedback()
{
	header('Content-Type: application/json');

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		return $this->output
			->set_status_header(405)
			->set_output(json_encode(['status' => false, 'message' => 'Method Not Allowed']));
	}

	$data = json_decode($this->input->raw_input_stream, true);
	$user_id = $this->verify_user();

	if (!$user_id) {
		return $this->output
			->set_status_header(401)
			->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
	}

	if (empty($data['form_id']) || empty($data['feedback'])) {
		return $this->output
			->set_status_header(422)
			->set_output(json_encode(['status' => false, 'message' => 'Missing required fields']));
	}

	$insert = [
		'form_id'      => $data['form_id'],
		'staff_id'     => $user_id,
		'submitted_by' => $user_id,
		'feedback'     => trim($data['feedback']),
		'created_at'   => date('Y-m-d H:i:s')
	];

	$this->db->insert('kpi_feedback', $insert);

	return $this->output
		->set_output(json_encode(['status' => true, 'message' => 'Feedback submitted successfully']));
}

public function get_kpi_feedbacks($form_id = null)
{
	header('Content-Type: application/json');

	$user_id = $this->verify_user(); // Returns false if unauthorized
		if (!$user_id) {
			return $this->output
				->set_status_header(401)
				->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
		}

	// ✅ Method check
	if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
		return $this->output
			->set_status_header(405)
			->set_output(json_encode(['status' => false, 'message' => 'Method Not Allowed']));
	}

	// ✅ Validate form ID
	if (!$form_id || !is_numeric($form_id)) {
		return $this->output
			->set_status_header(400)
			->set_output(json_encode(['status' => false, 'message' => 'Invalid or missing KPI form ID']));
	}

	// ✅ Fetch feedbacks
	$feedbacks = $this->db
		->select('kf.id, kf.feedback, kf.created_at, s.name AS submitted_by_name')
		->from('kpi_feedback AS kf')
		->join('staff AS s', 'kf.submitted_by = s.id', 'left')
		->where('kf.form_id', $form_id)
		->order_by('kf.created_at', 'DESC')
		->get()
		->result_array();

	return $this->output
		->set_output(json_encode([
			'status' => true,
			'message' => 'Feedbacks fetched successfully',
			'data' => $feedbacks
		]));
}


public function delete_kpi_feedback($id = null)
{
	header('Content-Type: application/json');

	$user_id = $this->verify_user(); // Returns false if unauthorized
		if (!$user_id) {
			return $this->output
				->set_status_header(401)
				->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
		}
	if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
		return $this->output
			->set_status_header(405)
			->set_output(json_encode(['status' => false, 'message' => 'Method Not Allowed']));
	}

	if (!$id || !is_numeric($id)) {
		return $this->output
			->set_status_header(400)
			->set_output(json_encode(['status' => false, 'message' => 'Invalid feedback ID']));
	}

	$this->db->delete('kpi_feedback', ['id' => $id]);

	return $this->output
		->set_output(json_encode(['status' => true, 'message' => 'Feedback deleted successfully']));
}

public function get_kpi_list()
{
	header('Content-Type: application/json');

	$user_id = $this->verify_user(); // Returns false if unauthorized
		if (!$user_id) {
			return $this->output
				->set_status_header(401)
				->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
		}

	// ✅ Method check
	if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
		return $this->output
			->set_status_header(405)
			->set_output(json_encode(['status' => false, 'message' => 'Method Not Allowed']));
	}

	// ✅ Validate user ID
	if (!$user_id || !is_numeric($user_id)) {
		return $this->output
			->set_status_header(400)
			->set_output(json_encode(['status' => false, 'message' => 'Invalid or missing user ID']));
	}

	// ✅ Fetch KPI forms
	$this->db->where('staff_id', $user_id);
	$forms = $this->db->get('kpi_form')->result_array();

	foreach ($forms as &$form) {
		$form_id = $form['id'];
		$form['subtasks'] = $this->db->where('kpi_form_id', $form_id)->get('kpi_form_details')->result_array();
		$form['total_weight'] = array_sum(array_column($form['subtasks'], 'weight'));
		$form['progress'] = min(intval($form['total_weight']), 100);
	}

	return $this->output
		->set_output(json_encode([
			'status' => true,
			'message' => 'KPI forms fetched successfully',
			'data' => $forms
		]));
}

public function get_kpi_list_by_id($id = null)
{
    header('Content-Type: application/json');

    // ✅ Authenticate user
    $user_id = $this->verify_user();
    if (!$user_id) {
        return $this->output
            ->set_status_header(401)
            ->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
    }

    // ✅ Method check
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        return $this->output
            ->set_status_header(405)
            ->set_output(json_encode(['status' => false, 'message' => 'Method Not Allowed']));
    }

    // ✅ Validate PPM ID
    if (!$id || !is_numeric($id)) {
        return $this->output
            ->set_status_header(400)
            ->set_output(json_encode(['status' => false, 'message' => 'Invalid or missing PPM ID']));
    }

    // ✅ Fetch PPM main record
    $ppm = $this->db->where('id', $id)->get('kpi_form')->row_array();
    if (!$ppm) {
        return $this->output
            ->set_status_header(404)
            ->set_output(json_encode(['status' => false, 'message' => 'PPM not found']));
    }

    // ✅ Fetch Subtasks (Details)
    $ppm['subtasks'] = $this->db->where('kpi_form_id', $id)->get('kpi_form_details')->result_array();

    // ✅ Calculate total weight and progress
    $ppm['total_weight'] = array_sum(array_column($ppm['subtasks'], 'weight'));
    $ppm['progress'] = min(intval($ppm['total_weight']), 100);

    return $this->output
        ->set_output(json_encode([
            'status' => true,
            'message' => 'PPM details fetched successfully',
            'data' => $ppm
        ]));
}

public function delete_kpi($id = null)
{
	header('Content-Type: application/json');

	// ✅ Authenticate
	$user_id = $this->verify_user();
	if (!$user_id) {
		return $this->output
			->set_status_header(401)
			->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
	}

	// ✅ Method check
	if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
		return $this->output
			->set_status_header(405)
			->set_output(json_encode(['status' => false, 'message' => 'Method Not Allowed']));
	}

	// ✅ Validate ID
	if (!$id || !is_numeric($id)) {
		return $this->output
			->set_status_header(400)
			->set_output(json_encode(['status' => false, 'message' => 'Invalid KPI ID']));
	}

	// ✅ Fetch KPI and validate ownership
	$form = $this->db->get_where('kpi_form', ['id' => $id])->row();
	if (!$form) {
		return $this->output
			->set_status_header(404)
			->set_output(json_encode(['status' => false, 'message' => 'KPI form not found']));
	}

	if ((int)$form->staff_id !== (int)$user_id) {
		return $this->output
			->set_status_header(403)
			->set_output(json_encode(['status' => false, 'message' => 'Forbidden: You do not own this KPI form']));
	}

	// ✅ Delete subtasks and form
	$this->db->where('kpi_form_id', $id)->delete('kpi_form_details');
	$this->db->where('id', $id)->delete('kpi_form');

	return $this->output
		->set_output(json_encode(['status' => true, 'message' => 'KPI form deleted successfully']));
}

//KPI

//RDC

public function get_rdc_task_list()
{
    header('Content-Type: application/json');

	// ✅ Authenticate
	$user_id = $this->verify_user();
	if (!$user_id) {
		return $this->output
			->set_status_header(401)
			->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
	}

    $start = $this->input->get('start_date');
    $end   = $this->input->get('end_date');

    // Default: all tasks
    $tasks = $this->rdc_model->getTasks($start, $end, $user_id);

    echo json_encode([
        'status' => true,
        'data' => $tasks,
    ]);
}

public function get_rdc_task_by_id($id = null)
{
    header('Content-Type: application/json');

    // ✅ Authenticate
    $user_id = $this->verify_user();
    if (!$user_id) {
        return $this->output
            ->set_status_header(401)
            ->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
    }

    // ✅ Method Check
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        return $this->output
            ->set_status_header(405)
            ->set_output(json_encode(['status' => false, 'message' => 'Method Not Allowed']));
    }

    // ✅ Validate Task ID
    if (!$id || !is_numeric($id)) {
        return $this->output
            ->set_status_header(400)
            ->set_output(json_encode(['status' => false, 'message' => 'Invalid or missing RDC Task ID']));
    }

    // ✅ Fetch Task
    $task = $this->rdc_model->getTaskById($id, $user_id); // secure with user ID ownership

    if (!$task) {
        return $this->output
            ->set_status_header(404)
            ->set_output(json_encode(['status' => false, 'message' => 'RDC Task not found']));
    }

    return $this->output
        ->set_output(json_encode([
            'status' => true,
            'message' => 'RDC Task fetched successfully',
            'data' => $task
        ]));
}


public function get_executor_verification_items($rdc_task_id = null)
{
    header('Content-Type: application/json');

    // User authentication
    $user_id = $this->verify_user(); // Your existing token/user verification logic
    if (!$user_id) {
        return $this->output
            ->set_status_header(401)
            ->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
    }

    // Validate RDC Task ID
    if (!$rdc_task_id || !is_numeric($rdc_task_id)) {
        return $this->output
            ->set_status_header(400)
            ->set_output(json_encode(['status' => false, 'message' => 'Invalid RDC Task ID']));
    }

    // Fetch RDC task
    $this->db->where('id', $rdc_task_id);
    $task = $this->db->get('rdc_task')->row_array();


    $this->db->where('id', $task['sop_id']);
    $sop_details = $this->db->get('sop')->row_array();

    if (!$task) {
        return $this->output
            ->set_status_header(404)
            ->set_output(json_encode(['status' => false, 'message' => 'Task not found']));
    }

    // Decode executor_stages field
	$executor_stage = json_decode($sop_details['executor_stage'], true);
	$executor_stages = json_decode($task['executor_stages'], true);
	$completed = $executor_stages['completed'] ?? [];

	return $this->output
		->set_output(json_encode([
			'status' => true,
			'message' => 'Executor verifications fetched successfully',
			'data' => [
				'completed' => $completed,
				'executor_stage' => $executor_stage
			]
		]));

}

public function submit_rdc_task()
{
	header('Content-Type: application/json');

    $data = $this->input->post();

	$task_id = $data['task_id'];

	if (!$task_id) {
		echo json_encode(['status' => false, 'message' => 'Task ID is required']);
		return;
	}

	// User authentication
    $user_id = $this->verify_user(); // Your existing token/user verification logic
    if (!$user_id) {
        return $this->output
            ->set_status_header(401)
            ->set_output(json_encode(['status' => false, 'message' => 'Unauthorized']));
    }

	// Fetch the RDC task
	$task = $this->db->get_where('rdc_task', ['id' => $task_id])->row();
	if (!$task) {
		echo json_encode(['status' => false, 'message' => 'RDC Task not found']);
		return;
	}

	$isEmployee = ($task->assigned_user == $user_id);

	$response = [];

	// ✅ Executor (Employee) Submission
	if ($isEmployee) {
		$UpdateData = [
			'task_status'          => $data['task_status'],
			'executor_explanation' => $data['executor_explanation'],
			'exe_cleared_on'       => date('Y-m-d H:i:s'),
		];

		// Optional: Proof text
		$proof_text = $data['proof_text'];
		if (!empty($proof_text)) {
			$UpdateData['proof_text'] = $proof_text;
		}

		// Handle file image once and reuse for all
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
				$orig_file_name = $this->upload->data('orig_name');
				$enc_file_name = $this->upload->data('file_name');
			} else {
				$error = $this->upload->display_errors();
				set_alert('error', $error);
				return;
			}
			$UpdateData['proof_image'] = $enc_file_name;
		}

		// Handle file upload once and reuse for all
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
				$orig_file_name = $this->upload->data('orig_name');
				$enc_file_name = $this->upload->data('file_name');
			} else {
				$error = $this->upload->display_errors();
				set_alert('error', $error);
				return;
			}

			$UpdateData['proof_file'] = $enc_file_name;
		}

		// ✅ Executor Checklist
		//$executorChecklist = $data['executor_checklist'];
		$executorChecklist = json_decode($data['executor_checklist'], true);

		if (is_array($executorChecklist)) {
			$executor_stage = json_decode($task->executor_stage ?? '{}', true);
			$executor_stage['completed'] = $executorChecklist;
			$UpdateData['executor_stages'] = json_encode($executor_stage);
		}

		$this->db->where('id', $task_id)->update('rdc_task', $UpdateData);
		$response['executor'] = 'submitted';

		// Send FCM notification for RDC task submission
		$staff_details = $this->db->select('id, name, department')
							  ->get_where('staff', ['id' => $user_id])
							  ->row();

		$title = 'RDC Task Submitted';
		$who = $staff_details ? $staff_details->name : 'An employee';

		// Notification
		$notification = [
			'user_id' => $user_id,
			'type' => 'rdc_task',
			'title' => 'RDC Task Submitted',
			'message' => "{$staff_details->name} has submitted the RDC Task.",
			'url' => base_url('rdc'),
			'is_read' => 0,
			'created_at' => date('Y-m-d H:i:s')
		];
		$this->db->insert('notifications', $notification);


		$body = sprintf('%s has submitted RDC task "%s".', $who, $task->title);

		// Get approver tokens (HR/Admin roles for RDC task submissions)
		$recipientTokens = $this->get_fund_approver_tokens(
			$staff_details ? $staff_details->department : null,
			[1, 2, 3, 5], // Admin, HR roles
			8
		);

		// Send FCM notification
		if (!empty($recipientTokens)) {
			$this->send_fcm_notification($title, $body, '', $recipientTokens, [
				'type'       => 'rdc_task_submitted',
				'task_id'    => (string)$task_id,
				'staff_id'   => (string)$user_id,
				'action'     => 'review'
			]);
		} else {
			$this->log_message("INFO: No recipient FCM tokens found for rdc_task_id={$task_id}");
		}
	}

	echo json_encode([
		'status' => true,
		'message' => 'Task submitted successfully',
		'result' => $response
	]);
}

//RDC

//Work Summary of this month

public function api_month_tasks()
{
    // 0) Auth
    $user_id = $this->verify_user();
    if (!$user_id) {
        return $this->output
            ->set_status_header(401)
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => 'Unauthorized']));
    }

    // 1) Date handling (Asia/Dhaka) + optional ?date=YYYY-MM-DD or ?month=YYYY-MM
    $tz         = new DateTimeZone('Asia/Dhaka');
    $dateParam  = $this->input->get('date', true);
    $monthParam = $this->input->get('month', true);

    if (!empty($monthParam)) {
        $dt = DateTime::createFromFormat('Y-m', $monthParam, $tz) ?: new DateTime('now', $tz);
    } elseif (!empty($dateParam)) {
        $dt = DateTime::createFromFormat('Y-m-d', $dateParam, $tz) ?: new DateTime('now', $tz);
    } else {
        $dt = new DateTime('now', $tz);
    }

    $startOfMonth  = (clone $dt)->modify('first day of this month')->setTime(0, 0, 0);
    $startNextMon  = (clone $startOfMonth)->modify('first day of next month')->setTime(0, 0, 0);
    $startBoundary = $startOfMonth->format('Y-m-d H:i:s'); // inclusive
    $nextBoundary  = $startNextMon->format('Y-m-d H:i:s'); // exclusive

    // === CONFIG
    $ESTIMATION_MINUTES_PER_UNIT = 60; // change to 1 if estimation_time already minutes
    $completedSqlSet = "'done','completed','complete','resolved','closed','finished','success'";

    // ================= TRACKER ISSUES AGGREGATES (SAFE) =================
    if ($this->db->table_exists('tracker_issues')) {
        $this->db->select("
            COUNT(*) AS total,
            SUM(CASE WHEN LOWER(task_status) IN ($completedSqlSet) THEN 1 ELSE 0 END) AS completed,
            SUM(COALESCE(estimation_time, 0)) AS est_units, SUM(COALESCE(spent_time, 0)) AS st_units
        ", false);
        $this->db->from('tracker_issues ti');
        $this->db->where('ti.assigned_to', $user_id);
        $this->db->group_start()
            ->group_start()
                ->where('ti.logged_at >=', $startBoundary)
                ->where('ti.logged_at <',  $nextBoundary)
            ->group_end()
            ->or_group_start()
                ->where('ti.estimated_end_time >=', $startBoundary)
                ->where('ti.estimated_end_time <',  $nextBoundary)
            ->group_end()
        ->group_end();

        $trackerAgg = $this->db->get()->row();

        $tracker_total   = (int)($trackerAgg->total ?? 0);
        $tracker_done    = (int)($trackerAgg->completed ?? 0);
        //$tracker_units   = (float)($trackerAgg->est_units ?? 0.0);
        $tracker_units   = (float)($trackerAgg->st_units ?? 0.0);
        $tracker_minutes = $tracker_units * $ESTIMATION_MINUTES_PER_UNIT;
    } else {
        $tracker_total = 0;
        $tracker_done = 0;
        $tracker_minutes = 0.0;
    }

    // ================= METRICS =================
    $total_tasks     = $tracker_total;
    $total_completed = $tracker_done;

    $completion_ratio   = ($total_tasks > 0) ? ($total_completed / $total_tasks) : 0.0;
    $completion_percent = round($completion_ratio * 100, 2);

    //$total_hours = round(($staff_minutes + $tracker_minutes) / 60.0, 2);
    $total_hours = round(($tracker_minutes) / 60.0, 2);

    // ================= RESPONSE =================
    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'success' => true,
            'data'    => [
                'total_tasks'         => $total_tasks,
                'total_completed'     => $total_completed,
                'completion_rate'  => $completion_percent,
                'total_hours'         => $total_hours,
            ],
        ]));
}

//Work Summary of this month

	public function update_fcm_token()
	{
		$this->output->set_content_type('application/json');

		// Check if the request has JSON input
		$input = json_decode(trim(file_get_contents("php://input")), true);

		// Fallback for form-data
		$user_id = $input['user_id'] ?? $this->input->get_post('user_id', true);
		$fcm_token = $input['fcm_token'] ?? $this->input->get_post('fcm_token', true);

		if (empty($user_id)) {
			return $this->output
				->set_status_header(400)
				->set_output(json_encode([
					'status' => 'error',
					'message' => 'User ID is required.'
				]));
		}

		if (empty($fcm_token)) {
			return $this->output
				->set_status_header(400)
				->set_output(json_encode([
					'status' => 'error',
					'message' => 'FCM token is required.'
				]));
		}

		// Check if user exists
		$user = $this->db->get_where('staff', ['id' => $user_id])->row();

		if (!$user) {
			return $this->output
				->set_status_header(404)
				->set_output(json_encode([
					'status' => 'error',
					'message' => 'User not found.'
				]));
		}

		// Update FCM token
		$this->db->where('id', $user_id);
		$this->db->update('staff', ['fcm_token' => $fcm_token]);

		return $this->output
			->set_status_header(200)
			->set_output(json_encode([
				'status' => 'success',
				'message' => 'FCM token updated successfully.'
			]));
	}

protected function verify_user()
{
    $headers = $this->input->request_headers();

    $authHeader = '';
    foreach ($headers as $key => $value) {
        if (strtolower($key) === 'authorization') {
            $authHeader = $value;
            break;
        }
    }

    if (!$authHeader) return false;

    $token = str_replace('Bearer ', '', $authHeader);

    try {
        $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($this->jwt_key, 'HS256'));
        return $decoded->id ?? false;
    } catch (\Throwable $e) { // ✅ Catches everything including ExpiredException and FatalErrors
        log_message('error', 'JWT decode failed: ' . $e->getMessage());
        return false;
    }
}

}
