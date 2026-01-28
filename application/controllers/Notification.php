<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Notification extends Admin_Controller
{
	private $uploadDir;
    private $serviceAccountPath;
    private $logFile;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('notification_model');

		$this->uploadDir = FCPATH . 'uploads/notifications/';
        $this->serviceAccountPath = FCPATH . 'assets/notification/service-account.json';
        $this->logFile = FCPATH . 'assets/notification/logs/fcm_notifications.log';
    }

    public function index()
    {
        if (!get_permission('notification', 'is_view')) {
            access_denied();
        }

        $this->data['notification_list'] = $this->notification_model->get_all();

        if ($this->input->post('save_notification')) {
            if (!get_permission('notification', 'is_add')) {
                access_denied();
            }

            $title = $this->input->post('title');
            $text = $this->input->post('text');
            $name = $this->input->post('name');
            $image = '';

            $this->log_message("Starting notification process - Title: $title");

            // Handle file upload using move_uploaded_file
            if (isset($_FILES['notification_image']['tmp_name']) && !empty($_FILES['notification_image']['name'])) {
                $extension = pathinfo($_FILES['notification_image']['name'], PATHINFO_EXTENSION);
                $fileName = 'notification_' . time() . '_' . uniqid() . '.' . $extension;
                $finalFilePath = $this->uploadDir . $fileName;

                if (move_uploaded_file($_FILES['notification_image']['tmp_name'], $finalFilePath)) {
                    $image = base_url('uploads/notifications/' . $fileName);
                    $this->log_message("Image uploaded successfully: $image");
                }
            }

            $save_data = [
                'title' => $title,
                'text' => $text,
                'name' => $name,
                'image' => $image,
            ];

            if ($this->notification_model->insert($save_data)) {
                $this->log_message("Notification saved to database successfully");
                // Send FCM notification after saving to database
                $this->send_fcm_notification($title, $text, $image);
                set_alert('success', 'Notification sent successfully.');
            } else {
                $this->log_message("ERROR: Failed to save notification to database");
                set_alert('error', 'Failed to save notification.');
            }
            redirect(base_url('notification'));
        }

        $this->data['title'] = 'Notification Management';
        $this->data['sub_page'] = 'notification/list';
        $this->data['main_menu'] = 'notification';
        $this->load->view('layout/index', $this->data);
    }

    private function send_fcm_notification($title, $text, $image = '')
    {
        $this->log_message("Starting FCM notification send process");

        // Get all FCM tokens from staff table
        $tokens = $this->db->select('fcm_token')
                          ->where('fcm_token IS NOT NULL')
                          ->where('fcm_token !=', '')
                          ->get('staff')
                          ->result_array();

        if (empty($tokens)) {
            $this->log_message("ERROR: No FCM tokens found in database");
            return false;
        }

        $this->log_message("Found " . count($tokens) . " FCM tokens");

        $accessToken = $this->get_access_token();
        $projectId = 'emp-app-f5a2d';

        $successCount = 0;
        $failureCount = 0;

        foreach ($tokens as $tokenRow) {
            $fcmToken = $tokenRow['fcm_token'];

            // Build message with proper image handling for all platforms
            $message = [
                "message" => [
                    "token" => $fcmToken,
                    "notification" => [
                        "title" => $title,
                        "body" => $text
                    ],
                    "data" => [
                        "title" => $title,
                        "body" => $text,
                        "image" => $image
                    ]
                ]
            ];

            // Add image for different platforms if image exists
            if (!empty($image)) {
                // Android specific image
                $message["message"]["android"] = [
                    "notification" => [
                        "image" => $image
                    ]
                ];

                // iOS specific image
                $message["message"]["apns"] = [
                    "payload" => [
                        "aps" => [
                            "mutable-content" => 1
                        ]
                    ],
                    "fcm_options" => [
                        "image" => $image
                    ]
                ];

                // Web push image
                $message["message"]["webpush"] = [
                    "headers" => [
                        "image" => $image
                    ]
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

				// Check if response contains UNREGISTERED
				if (strpos($response, 'UNREGISTERED') !== false) {
					$this->db->where('fcm_token', $fcmToken)->update('staff', ['fcm_token' => NULL]);
					$this->log_message("INFO: Removed UNREGISTERED token from DB: " . substr($fcmToken, 0, 20) . "...");
				}
			}
        }
        $this->log_message("FCM send completed - Success: $successCount, Failures: $failureCount");
        return true;
    }

	 public function send_fcm_notification_test_v2($title='test', $text='test text', $image = '')
    {

        $this->log_message("Starting FCM notification send process");

        // Get all FCM tokens from staff table
        $tokens = $this->db->select('fcm_token')
                          ->where('fcm_token IS NOT NULL')
                          ->where('fcm_token !=', '')
                          ->get('staff')
                          ->result_array();

        if (empty($tokens)) {
            $this->log_message("ERROR: No FCM tokens found in database");
            return false;
        }

        $this->log_message("Found " . count($tokens) . " FCM tokens");

        $accessToken = $this->get_access_token();
        $projectId = 'emp-app-f5a2d';

        $successCount = 0;
        $failureCount = 0;

        foreach ($tokens as $tokenRow) {
            $fcmToken = $tokenRow['fcm_token'];

            // Build message with proper image handling for all platforms
            $message = [
                "message" => [
                    "token" => $fcmToken,
                    "notification" => [
                        "title" => $title,
                        "body" => $text
                    ],
                    "data" => [
                        "title" => $title,
                        "body" => $text,
                        "image" => $image
                    ]
                ]
            ];

            // Add image for different platforms if image exists
            if (!empty($image)) {
                // Android specific image
                $message["message"]["android"] = [
                    "notification" => [
                        "image" => $image
                    ]
                ];

                // iOS specific image
                $message["message"]["apns"] = [
                    "payload" => [
                        "aps" => [
                            "mutable-content" => 1
                        ]
                    ],
                    "fcm_options" => [
                        "image" => $image
                    ]
                ];

                // Web push image
                $message["message"]["webpush"] = [
                    "headers" => [
                        "image" => $image
                    ]
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
print_r ($response);
			die();
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

          if ($httpCode == 200) {
				$successCount++;
				$this->log_message("SUCCESS: Notification sent to token: " . substr($fcmToken, 0, 20) . "...");
			} else {
				$failureCount++;
				$this->log_message("ERROR: Failed to send to token: " . substr($fcmToken, 0, 20) . "... HTTP Code: $httpCode Response: $response");

				// Check if response contains UNREGISTERED
				if (strpos($response, 'UNREGISTERED') !== false) {
					$this->db->where('fcm_token', $fcmToken)->update('staff', ['fcm_token' => NULL]);
					$this->log_message("INFO: Removed UNREGISTERED token from DB: " . substr($fcmToken, 0, 20) . "...");
				}
			}

        }

        $this->log_message("FCM send completed - Success: $successCount, Failures: $failureCount");
        return true;
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


    public function delete($id = '')
    {
        if (!get_permission('notification', 'is_delete')) {
            access_denied();
        }

        $row = $this->notification_model->get($id);
        if ($row && !empty($row->image)) {
            $file_path = str_replace(base_url(), FCPATH, $row->image);
            if (file_exists($file_path)) {
                @unlink($file_path);
            }
        }

        $this->notification_model->delete($id);
        $this->log_message("Notification deleted - ID: $id");
        set_alert('success', 'Notification deleted successfully.');
        redirect(base_url('notification'));
    }
}
