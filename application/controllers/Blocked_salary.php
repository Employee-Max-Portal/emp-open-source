<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Blocked_salary extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('blocked_salary_model');
        $this->load->model('email_model');

        $this->uploadDir = FCPATH . 'uploads/notifications/';
        $this->serviceAccountPath = FCPATH . 'assets/notification/service-account.json';
        $this->logFile = FCPATH . 'assets/notification/logs/fcm_notifications.log';
    }

    public function index()
    {
        if (!get_permission('rdc_salary_blocks', 'is_view')) {
            access_denied();
        }

        if (isset($_POST['update'])) {
            if (!get_permission('rdc_salary_blocks', 'is_edit')) {
                access_denied();
            }

            $id = $this->input->post('id');
            $user_role = $this->session->userdata('loggedin_role_id');
            $logged_staff_id = $this->session->userdata('loggedin_userid');
            $is_manager = in_array($user_role, [1, 2, 3, 5, 8]);

            // Get current block details
            $block_details = $this->blocked_salary_model->getBlockedSalaryById($id);
            $is_affected_staff = ($block_details['staff_id'] == $logged_staff_id);

            $arrayData = array();

            if ($is_affected_staff) {
                // Staff updating their explanation
                $arrayData['staff_explanation'] = $this->input->post('staff_explanation');
            } elseif ($is_manager) {
                // Manager updating status and comments
                $arrayData['approved_by'] = get_loggedin_user_id();
                $arrayData['status'] = $this->input->post('status');
                $arrayData['manager_comments'] = $this->input->post('manager_comments');
                $arrayData['cleared_on'] = date('Y-m-d H:i:s');
            }

            $this->db->where('id', $id);
            $this->db->update('salary_blocks', $arrayData);

            // Only send notifications and emails for manager actions
            if ($is_manager && isset($arrayData['status'])) {
                // Fetch the updated salary block
                $salary_block = $this->db->get_where('salary_blocks', ['id' => $id])->row();

                if ($salary_block) {
                    $staff_id = $salary_block->staff_id;
                    $status = $this->input->post('status');

                    // Map status
                    $status_label = '';
                    switch ($status) {
                        case 1:
                            $status_label = 'Pending';
                            break;
                        case 2:
                            $status_label = 'Unblocked';
                            break;
                        case 3:
                            $status_label = 'Rejected';
                            break;
                    }

                    // Get staff name
                    $staff = $this->db->get_where('staff', ['id' => $staff_id])->row();
                    $staff_name = $staff ? $staff->name : 'Staff';

                    // Notification insert
                    $notificationData = array(
                        'user_id'    => $staff_id,
                        'type'       => 'salary_block',
                        'title'      => 'Salary Block ' . $status_label,
                        'message'    => 'Dear ' . $staff_name . ', your salary block status has been updated to ' . strtolower($status_label) . '.',
                        'url'        => base_url('blocked_salary'),
                        'is_read'    => 0,
                        'created_at' => date('Y-m-d H:i:s')
                    );

                    $this->db->insert('notifications', $notificationData);

                    // Send FCM notification
                    $fcm_tokens = $this->db->select('fcm_token')
                                           ->where('id', $staff_id)
                                           ->where('fcm_token IS NOT NULL')
                                           ->where('fcm_token !=', '')
                                           ->get('staff')
                                           ->result_array();

                    if (!empty($fcm_tokens)) {
                        $tokens = array_column($fcm_tokens, 'fcm_token');
                        $this->send_fcm_notification(
                            'Salary Block ' . $status_label,
                            'Your salary block status has been updated to ' . strtolower($status_label) . '.',
                            '',
                            $tokens,
                            [
                                'type' => 'salary_block_update',
                                'block_id' => (string)$id,
                                'status' => (string)$status,
                                'action' => 'view'
                            ]
                        );
                    }
                }
            }

            // Send notifications, FCM, and email when staff submits explanation
            if ($is_affected_staff && $this->input->post('staff_explanation')) {
                $staff_explanation = $this->input->post('staff_explanation');
                $staff_id = get_loggedin_user_id();
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

                // Get HR and COO staff IDs and emails (roles 3 and 5)
                $this->db->select('staff.id, staff.email, staff.name, login_credential.role')
                    ->from('staff')
                    ->join('login_credential', 'login_credential.user_id = staff.id')
                    ->where_in('login_credential.role', [3, 5])
                    ->where('login_credential.active', 1);
                $hr_coo_list = $this->db->get()->result_array();

                // Send notifications to HR and COO
                foreach ($hr_coo_list as $person) {
                    $notificationData = array(
                        'user_id'    => $person['id'],
                        'type'       => 'salary_block_explanation',
                        'title'      => 'Salary Block Explanation Submitted',
                        'message'    => $staff_name . ' has submitted an explanation for their salary block request.',
                        'url'        => base_url('blocked_salary'),
                        'is_read'    => 0,
                        'created_at' => date('Y-m-d H:i:s')
                    );
                    $this->db->insert('notifications', $notificationData);
                }

                // Send FCM notifications to HR and COO
                $hr_coo_tokens = $this->db->select('fcm_token')
                                          ->from('staff')
                                          ->join('login_credential', 'login_credential.user_id = staff.id')
                                          ->where_in('login_credential.role', [3, 5])
                                          ->where('login_credential.active', 1)
                                          ->where('fcm_token IS NOT NULL')
                                          ->where('fcm_token !=', '')
                                          ->get()
                                          ->result_array();

                if (!empty($hr_coo_tokens)) {
                    $tokens = array_column($hr_coo_tokens, 'fcm_token');
                    $this->send_fcm_notification(
                        'Salary Block Explanation Submitted',
                        $staff_name . ' has submitted an explanation for their salary block request.',
                        '',
                        $tokens,
                        [
                            'type' => 'salary_block_explanation',
                            'block_id' => (string)$id,
                            'staff_name' => $staff_name,
                            'action' => 'view'
                        ]
                    );
                }

                // Extract HR email (first role 5 match)
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

                // Get Department Manager (role 8) for notifications and email
                $this->db->select('staff.id, staff.email, staff.name')
                    ->from('staff')
                    ->join('login_credential', 'login_credential.user_id = staff.id')
                    ->where('login_credential.role', 8)
                    ->where('staff.department', $department_id)
                    ->where('login_credential.active', 1);
                $manager = $this->db->get()->row_array();

                // Send notification to Department Manager
                if (!empty($manager)) {
                    $notificationData = array(
                        'user_id'    => $manager['id'],
                        'type'       => 'salary_block_explanation',
                        'title'      => 'Salary Block Explanation Submitted',
                        'message'    => $staff_name . ' has submitted an explanation for their salary block request.',
                        'url'        => base_url('blocked_salary'),
                        'is_read'    => 0,
                        'created_at' => date('Y-m-d H:i:s')
                    );
                    $this->db->insert('notifications', $notificationData);

                    // Send FCM notification to Department Manager
                    $manager_token = $this->db->select('fcm_token')
                                              ->where('id', $manager['id'])
                                              ->where('fcm_token IS NOT NULL')
                                              ->where('fcm_token !=', '')
                                              ->get('staff')
                                              ->row_array();

                    if (!empty($manager_token)) {
                        $this->send_fcm_notification(
                            'Salary Block Explanation Submitted',
                            $staff_name . ' has submitted an explanation for their salary block request.',
                            '',
                            [$manager_token['fcm_token']],
                            [
                                'type' => 'salary_block_explanation',
                                'block_id' => (string)$id,
                                'staff_name' => $staff_name,
                                'action' => 'view'
                            ]
                        );
                    }
                }

                // Build CC list: COO + Manager
                $cc_emails = [];
                foreach ($hr_coo_list as $person) {
                    if ($person['role'] == 3 && !empty($person['email'])) {
                        $cc_emails[] = $person['email']; // COO
                    }
                }
                if (!empty($manager['email'])) {
                    $cc_emails[] = $manager['email']; // Manager
                }

                // Prepare the email content
                $mail_subject = 'Salary Block Unblock Request Submitted by ' . $staff_name;

                $mail_body = "
                <html>
                  <body style='font-family:Arial, sans-serif; background:#f9f9f9; padding:20px;'>
                    <table style='max-width:600px; margin:auto; background:#fff; border-radius:8px; padding:20px; box-shadow:0 0 10px rgba(0,0,0,0.05);'>
                      <tr><td style='text-align:center;'>
                        <h2 style='color:#1976d2;'>Salary Block Unblock Request</h2>
                      </td></tr>
                      <tr><td style='font-size:15px; color:#333;'>
                        <p>Dear HR,</p>
                        <p>I am writing to formally submit my request to unblock my salary that was previously blocked due to an escalation.</p>

                        <p><strong>My Explanation:</strong></p>
                        <p>{$staff_explanation}</p>

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

                // Email Configuration
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

                // Send the email
                $this->email_model->send_email_yandex($email_data);
            }

            if ($is_affected_staff) {
                set_alert('success', translate('explanation_submitted_successfully'));
            } else {
                set_alert('success', translate('information_has_been_updated_successfully'));
            }
            redirect(base_url('blocked_salary'));
        }

        $staffID = get_loggedin_user_id();
        if ($_POST['search']) {
            $this->data['branch_id'] = $this->input->post('branch_id');
            $this->data['staff_role'] = $this->input->post('staff_role');

            $this->data['blocked_salary_list'] = $this->blocked_salary_model->getBlockedSalaries($this->data['branch_id'], $staffID);
        } else {
            $this->data['blocked_salary_list'] = $this->blocked_salary_model->getBlockedSalaries(null, $staffID);
        }

        $this->data['main_menu']        = 'rdc';
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

        $this->data['title']            = translate('Blocked Salaries');
        $this->data['sub_page']         = 'blocked_salary/index';
        $this->load->view('layout/index', $this->data);
    }

    // get add modal
    public function getApprovelDetails()
    {
        if (get_permission('rdc_salary_blocks', 'is_edit')) {
            $this->data['block_id'] = $this->input->post('id');
            $this->load->view('blocked_salary/approvel_modalView', $this->data);
        }
    }

    public function get_view_description()
    {
        $block_id = $this->input->post('id');
        $this->db->select('*');
        $this->db->from('salary_blocks');
        $this->db->where('id', $block_id);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $this->data['description'] = $query->row()->reason;
            $this->data['task_title'] = 'Salary Block';
        } else {
            $this->data['description'] = 'No reason found.';
            $this->data['task_title'] = 'No task found.';
        }

        // Load the view with the fetched description
        $this->load->view('blocked_salary/get_view_description', $this->data);
    }

    public function delete($id = '')
    {
        if (get_permission('rdc_salary_blocks', 'is_delete')) {
            $this->db->where('id', $id);
            $this->db->delete('salary_blocks');
        }
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