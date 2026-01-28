<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Todo extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('warning_model');
        $this->load->model('email_model');

        $this->uploadDir = FCPATH . 'uploads/notifications/';
        $this->serviceAccountPath = FCPATH . 'assets/notification/service-account.json';
        $this->logFile = FCPATH . 'assets/notification/logs/fcm_notifications.log';
    }

    public function index()
    {
        if (!get_permission('todo', 'is_view')) {
            access_denied();
        }


		if ($this->input->post('update')) {

			$warning_id = $this->input->post('id');
			$current_user_id = get_loggedin_user_id();
			$role_id = loggedin_role_id();

			// Fetch the warning to determine role
			$warning = $this->db->get_where('warnings', ['id' => $warning_id])->row();

			$isEmployee = ($current_user_id == $warning->user_id);
			$isManager = ($current_user_id == $warning->manager_id);
			$isAdvisor = ($role_id == 10);

			// Prevent unauthorized users
			if (!$isEmployee && !$isManager && !$isAdvisor) {
				access_denied();
			}

			if ($isEmployee) {
				if (!get_permission('todo', 'is_edit')) access_denied();

				// Update employee fields
				$employeeData = [
					'approved_by' => $current_user_id,
					'status'      => $this->input->post('status'),
					'comments'    => $this->input->post('comments', false),
					'cleared_on'  => date('Y-m-d H:i:s')
				];
				$this->db->where('id', $warning_id)->update('warnings', $employeeData);

				// Penalty Dates
				$penalty_dates = $this->input->post('penalty_dates');
				if (!empty($penalty_dates)) {
					$this->db->where('warning_id', $warning_id)->delete('penalty_days');
					foreach ($penalty_dates as $penalty_date) {
						if (!empty($penalty_date)) {
							$this->db->insert('penalty_days', [
								'staff_id'     => $warning->user_id,
								'penalty_date' => $penalty_date,
								'reason'       => $warning->reason,
								'warning_id'   => $warning_id
							]);
						}
					}
				}

				if ($warning) {
					$staff_id = $warning->user_id;

					$status = $this->input->post('status');

					// 3. Map status
					$status_label = '';
					switch ($status) {
						case 1:
							$status_label = 'Pending';
							break;
						case 2:
							$status_label = 'Cleared';
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
						'type'       => 'warning',
						'title'      => 'Warning Acknowledgement' . $status_label,
						'message'    => 'Dear Concern, ' . $staff_name . ', has ' . strtolower($status_label) . ' of his pending warnings.',
						'url'        => base_url('todo'),
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
							'Warning ' . $status_label,
							'Your warning status has been updated to ' . strtolower($status_label) . '.',
							'',
							$tokens,
							[
								'type' => 'warning_update',
								'warning_id' => (string)$id,
								'status' => (string)$status,
								'action' => 'view'
							]
						);
					}
				}

				// Notifications and Email (Only for employee submission)
				$effect = $this->input->post('effect');
				$category = $this->input->post('category');

				// 1. Get current staff info
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

				// 5. Get Disciplinary Manager (role 9)
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
				$penalty_dates = $this->input->post('penalty_dates');

				$issue_date = date('Y-m-d H:i:s');

				$mail_subject = 'Acknowledgement of Warning Explanation Submitted by ' . $staff_name;

				// Build penalty dates section if exists
				$penalty_section = '';
				if (!empty($penalty_dates) && is_array($penalty_dates)) {
					$penalty_section = "<p><strong>Penalty Work Dates:</strong><br>";
					foreach ($penalty_dates as $date) {
						if (!empty($date)) {
							$penalty_section .= "• " . date('jS F, Y', strtotime($date)) . "<br>";
						}
					}
					$penalty_section .= "</p>";
				}

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

						{$penalty_section}

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

			}

			if ($isManager) {
				if (!in_array($role_id, [8, 3])) access_denied();

				// Update only manager fields
				$managerData = [
					'manager_review'      => $this->input->post('manager_review'),
					'manager_explanation' => $this->input->post('manager_explanation', false),
					'cleared_on'          => date('Y-m-d H:i:s')
				];
				$this->db->where('id', $warning_id)->update('warnings', $managerData);


				$staff_id = $warning->user_id;
				$manager_id = $warning->manager_id;

				// Get staff name
				$staff = $this->db->get_where('staff', ['id' => $staff_id])->row();
				$staff_name = $staff ? $staff->name : 'Staff';

				// Get manager name
				$manager = $this->db->get_where('staff', ['id' => $manager_id])->row();
				$manager_name = $manager ? $manager->name : 'Manager';

				if ($warning) {

					$status = $this->input->post('manager_review');

					// 3. Map status
					$status_label = '';
					switch ($status) {
						case 1:
							$status_label = 'Pending';
							break;
						case 2:
							$status_label = 'Cleared';
							break;
						case 3:
							$status_label = 'Rejected';
							break;
						case 5:
							$status_label = 'Unsatisfied';
							break;
					}

					// Notification insert
					$notificationData = array(
						'user_id'    => $staff_id,
						'type'       => 'warning',
						'title'      => 'Warning Acknowledgement by Manager' . $status_label,
						'message'    => 'Dear Concern, ' . $manager_name . ', has ' . strtolower($status_label) . ' of  ' . $staff_name . ' pending warnings.',
						'url'        => base_url('todo'),
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
							'Warning ' . $status_label,
							'Your warning status has been updated to ' . strtolower($status_label) . '.',
							'',
							$tokens,
							[
								'type' => 'warning_update',
								'warning_id' => (string)$id,
								'status' => (string)$status,
								'action' => 'view'
							]
						);
					}

				}

				// Notifications and Email (Only for employee submission)
				$effect = $this->input->post('effect');
				$category = $this->input->post('category');

				// 1. Get current staff info
				$manager_id = get_loggedin_user_id();
				$manager = $this->db
					->select('staff.name, staff.department, staff.email, staff.staff_id as employee_id')
					->join('login_credential', 'login_credential.user_id = staff.id')
					->where('staff.id', $manager_id)
					->get('staff')
					->row_array();

				$department_id = $manager['department'];
				$manager_name = $manager['name'];
				$manager_email = $manager['email'];
				$employee_id = $manager['employee_id'];


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

				// 5. Get Disciplinary Manager (role 9)
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
				$penalty_dates = $this->input->post('penalty_dates');

				$issue_date = date('Y-m-d H:i:s');

				$mail_subject = 'Acknowledgement of Warning Explanation Submitted by ' . $manager_name;

				// Build penalty dates section if exists
				$penalty_section = '';
				if (!empty($penalty_dates) && is_array($penalty_dates)) {
					$penalty_section = "<p><strong>Penalty Work Dates:</strong><br>";
					foreach ($penalty_dates as $date) {
						if (!empty($date)) {
							$penalty_section .= "• " . date('jS F, Y', strtotime($date)) . "<br>";
						}
					}
					$penalty_section .= "</p>";
				}

				$mail_body = "
				<html>
				  <body style='font-family:Arial, sans-serif; background:#f9f9f9; padding:20px;'>
					<table style='max-width:600px; margin:auto; background:#fff; border-radius:8px; padding:20px; box-shadow:0 0 10px rgba(0,0,0,0.05);'>
					  <tr><td style='text-align:center;'>
						<h2 style='color:#1976d2;'>Warning Explanation Submitted</h2>
					  </td></tr>
					  <tr><td style='font-size:15px; color:#333;'>
						<p>Dear Concern,</p>
						<p>I am writing to formally submit my explanation regarding the warning issued to {$staff_name} that falls under the category of <strong>{$category}</strong> that affects the <strong>{$effect}</strong>.</p>

						<p><strong>Here is my Explanation:</strong></p>
						<p>{$comments}</p>

						{$penalty_section}

						<p>Sincerely,<br>
							<strong>{$manager_name}</strong><br>
							Employee ID: {$employee_id}<br>
							Email: {$manager_email}
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

			}

			if ($isAdvisor) {

				// Update only advisor fields
				$advisorData = [
					'advisor_review'      => $this->input->post('advisor_review'),
					'advisor_explanation' => $this->input->post('advisor_explanation', false),
					'advisor_reviewed_at' => date('Y-m-d H:i:s')
				];

				$this->db->where('id', $warning_id)->update('warnings', $advisorData);


				$staff_id = $warning->user_id;
				$advisor_id = $warning->advisor_id;

				// Get staff name
				$staff = $this->db->get_where('staff', ['id' => $staff_id])->row();
				$staff_name = $staff ? $staff->name : 'Staff';

				// Get advisor name
				$advisor = $this->db->get_where('staff', ['id' => $advisor_id])->row();
				$advisor_name = $advisor ? $advisor->name : 'Advisor';

				if ($warning) {

					$status = $this->input->post('advisor_review');

					// 3. Map status
					$status_label = '';
					switch ($status) {
						case 1:
							$status_label = 'Pending';
							break;
						case 2:
							$status_label = 'Cleared';
							break;
						case 3:
							$status_label = 'Rejected';
							break;
						case 5:
							$status_label = 'Unsatisfied';
							break;
					}

					// Notification insert
					$notificationData = array(
						'user_id'    => $staff_id,
						'type'       => 'warning',
						'title'      => 'Warning Acknowledgement by Advisor' . $status_label,
						'message'    => 'Dear Concern, ' . $advisor_name . ', has ' . strtolower($status_label) . ' of  ' . $staff_name . ' pending warnings.',
						'url'        => base_url('todo'),
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
							'Warning ' . $status_label,
							'Your warning status has been updated to ' . strtolower($status_label) . '.',
							'',
							$tokens,
							[
								'type' => 'warning_update',
								'warning_id' => (string)$id,
								'status' => (string)$status,
								'action' => 'view'
							]
						);
					}

				}

				// Notifications and Email (Only for employee submission)
				$effect = $this->input->post('effect');
				$category = $this->input->post('category');

				// 1. Get current staff info
				$advisor_id = get_loggedin_user_id();
				$advisor = $this->db
					->select('staff.name, staff.department, staff.email, staff.staff_id as employee_id')
					->join('login_credential', 'login_credential.user_id = staff.id')
					->where('staff.id', $advisor_id)
					->get('staff')
					->row_array();

				$staff = $this->db
					->select('staff.name, staff.department, staff.email, staff.staff_id as employee_id')
					->join('login_credential', 'login_credential.user_id = staff.id')
					->where('staff.id', $staff_id)
					->get('staff')
					->row_array();

				$department_id = $staff['department'];
				$advisor_name = $advisor['name'];
				$advisor_email = $advisor['email'];
				$employee_id = $advisor['employee_id'];


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

				// 5. Get Disciplinary Manager (role 9)
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
				$comments = $this->input->post('advisor_explanation');
				$penalty_dates = $this->input->post('penalty_dates');

				$issue_date = date('Y-m-d H:i:s');

				$mail_subject = 'Acknowledgement of Warning Explanation Submitted by ' . $advisor_name;

				// Build penalty dates section if exists
				$penalty_section = '';
				if (!empty($penalty_dates) && is_array($penalty_dates)) {
					$penalty_section = "<p><strong>Penalty Work Dates:</strong><br>";
					foreach ($penalty_dates as $date) {
						if (!empty($date)) {
							$penalty_section .= "• " . date('jS F, Y', strtotime($date)) . "<br>";
						}
					}
					$penalty_section .= "</p>";
				}

				$mail_body = "
				<html>
				  <body style='font-family:Arial, sans-serif; background:#f9f9f9; padding:20px;'>
					<table style='max-width:600px; margin:auto; background:#fff; border-radius:8px; padding:20px; box-shadow:0 0 10px rgba(0,0,0,0.05);'>
					  <tr><td style='text-align:center;'>
						<h2 style='color:#1976d2;'>Warning Explanation Submitted</h2>
					  </td></tr>
					  <tr><td style='font-size:15px; color:#333;'>
						<p>Dear Concern,</p>
						<p>I am writing to formally submit my explanation regarding the warning issued to {$staff_name} that falls under the category of <strong>{$category}</strong> that affects the <strong>{$effect}</strong>.</p>

						<p><strong>Here is my Explanation:</strong></p>
						<p>{$comments}</p>

						{$penalty_section}

						<p>Sincerely,<br>
							<strong>{$advisor_name}</strong><br>
							Employee ID: {$employee_id}<br>
							Email: {$advisor_email}
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

				/* print_r ($email_data);
				die(); */
				// 8. Send the email
				$this->email_model->send_email_yandex($email_data);

			}

			set_alert('success', translate('information_has_been_updated_successfully'));
			redirect(base_url('todo'));
		}

		$staffID = get_loggedin_user_id();
		if ($_POST['search']) {
            //$this->data['branch_id'] = $this->application_model->get_branch_id();
            $this->data['branch_id'] = $this->input->post('branch_id');
            $this->data['staff_role'] = $this->input->post('staff_role');

            $this->data['warning_list'] = $this->warning_model->getWarnings($this->data['branch_id'], $staffID);
        }
		else {
			 $this->data['warning_list'] = $this->warning_model->getWarnings(null, $staffID);
		}


        $this->data['main_menu']        = 'todo';
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

        $this->data['title']            = translate('To-Do');
        $this->data['sub_page']         = 'todo/index';
        $this->load->view('layout/index', $this->data);
    }

    // get add modal
    public function getApprovelDetails()
    {
        if (get_permission('todo', 'is_edit')) {
            $this->data['warnings_id'] = $this->input->post('id');
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

            $this->load->view('todo/approvel_modalView', $this->data);
        }
    }

public function save_warning() {
    if (!get_permission('todo', 'is_add')) {
        access_denied();
    }

    // Load upload library if not autoloaded
    $this->load->library('upload');

    if ($_POST) {
        $penalty = $this->input->post('penalty');
        $reference = $this->input->post('reference');
        $category = $this->input->post('category');
        $category_other = $this->input->post('category_other');
        $final_category = ($category === 'others' && !empty($category_other)) ? $category_other : $category;

        $effect = $this->input->post('effect');
        $effect_other = $this->input->post('effect_other');
        $final_effect = ($effect === 'others' && !empty($effect_other)) ? $effect_other : $effect;

        $applicant_ids = $this->input->post('applicant_id'); // Array of IDs
        $reason = $this->input->post('reason', false);
        $clearance_time = $this->input->post('clearance_time');
        $issue_date = date("Y-m-d H:i:s");

        $orig_file_name = '';
        $enc_file_name = '';

		// Fetch policy details with branch and category
		$this->db->select('p.title, p.description, b.name AS branch_name, pc.name AS category_name');
		$this->db->from('policy AS p');
		$this->db->join('branch AS b', 'b.id = p.branch_id', 'left');
		$this->db->join('policy_category AS pc', 'pc.id = p.category_id', 'left');
		$this->db->where('p.id', $reference);
		$query = $this->db->get();

		if ($query->num_rows() > 0) {
        $row = $query->row();
        // Build formatted reference string
        $date = date('Ymd'); // or use date('d-m-Y') depending on your need
        $reference = $row->branch_name . '/HR & ADMIN/' . $row->category_name . '/TO-DO/' . $date . '-' . $row->title;
		}

        // Handle file upload once and reuse for all
        if (isset($_FILES["attachment_file"]) && !empty($_FILES['attachment_file']['name'])) {
            if (!is_dir('./uploads/attachments/warnings/')) {
                mkdir('./uploads/attachments/warnings/', 0777, TRUE);
            }

            $config['upload_path'] = './uploads/attachments/warnings/';
            $config['allowed_types'] = "*";
            $config['max_size'] = '2024';
            $config['encrypt_name'] = true;

            $this->upload->initialize($config);
            if ($this->upload->do_upload("attachment_file")) {
                $orig_file_name = $this->upload->data('orig_name');
                $enc_file_name = $this->upload->data('file_name');
            } else {
                $error = $this->upload->display_errors();
                set_alert('error', $error);
                redirect(base_url('todo'));
                return;
            }
        }

        // Insert warning for each applicant ID
        foreach ($applicant_ids as $applicant_id) {
		// Step 1: Get applicant's role and department
		$staff_info = $this->db
			->select('staff.name, staff.department, login_credential.role')
			->join('login_credential', 'login_credential.user_id = staff.id')
			->where('staff.id', $applicant_id)
			->get('staff')
			->row();

		$staff_name = $staff_info ? $staff_info->name : 'Staff';
		$department_id = $staff_info ? $staff_info->department : null;
		$applicant_role = $staff_info ? $staff_info->role : null;

		// Step 2: Default null
		$manager_id = null;
		$manager_review = null;


		// Step 3: Insert warning
		$arrayData = array(
			'reference'        => $reference,
			'penalty'         => $penalty,
			'category'        => $final_category,
			'effect'          => $final_effect,
			'user_id'         => $applicant_id,
			'reason'          => $reason,
			'clearance_time'  => $clearance_time,
			'session_id'      => get_session_id(),
			'orig_file_name'  => $orig_file_name,
			'enc_file_name'   => $enc_file_name,
			'status'          => 1,
			'issued_by'       => get_loggedin_user_id(),
			'issue_date'      => $issue_date,
			'email_sent'      => 0
		);
		// Roles that must be reviewed by COO
		$coo_review_roles = [3, 5, 8];

		// Fetch COO once (correct role = 3)
		$coo = $this->db
			->select('staff.id')
			->from('staff')
			->join('login_credential', 'login_credential.user_id = staff.id')
			->where('login_credential.role', 3)
			->where('login_credential.active', 1)
			->get()
			->row();

		/**
		 * CASE 1: Regular staff (role 4)
		 * → Department manager
		 * → Fallback to COO
		 */
		if ($applicant_role == 4 && !empty($department_id)) {

			$manager = $this->db
				->select('staff.id')
				->from('staff')
				->join('login_credential', 'login_credential.user_id = staff.id')
				->where('staff.department', $department_id)
				->where('login_credential.role', 8)
				->where('login_credential.active', 1)
				->get()
				->row();

			if ($manager) {
				$arrayData['manager_id'] = $manager->id;
				$arrayData['manager_review'] = 1;
			} elseif ($coo) {
				$arrayData['manager_id'] = $coo->id;
				$arrayData['manager_review'] = 1;
			}
		}

		/**
		 * CASE 2: Roles 3, 5, 8
		 * → Always COO
		 */
		if (in_array($applicant_role, $coo_review_roles) && $coo) {
			$arrayData['manager_id'] = $coo->id;
			$arrayData['manager_review'] = 1;
		}

		/**
		 * Advisor assignment (safe)
		 */
		$advisor = $this->db
			->select('staff.id')
			->from('staff')
			->join('login_credential', 'login_credential.user_id = staff.id')
			->where('login_credential.role', 10)
			->where('login_credential.active', 1)
			->get()
			->row();

		if ($advisor) {
			$arrayData['advisor_id'] = $advisor->id;
			$arrayData['advisor_review'] = 1;
		}


		$this->db->insert('warnings', $arrayData);

			// ✅ Insert notification for warning issue
			$notificationData = array(
				'user_id'    => $applicant_id,
				'type'       => 'warning',
				'title'      => 'New Warning Issued',
				'message'    => 'Dear ' . $staff_name . ', a new warning has been issued to you. Please take immediate action.',
				'url'        => base_url('todo'),
				'is_read'    => 0,
				'created_at' => date('Y-m-d H:i:s')
			);

			$this->db->insert('notifications', $notificationData);
			 // Send FCM notification to the warned staff
            $fcm_tokens = $this->db->select('fcm_token')
                                   ->where('id', $applicant_id)
                                   ->where('fcm_token IS NOT NULL')
                                   ->where('fcm_token !=', '')
                                   ->get('staff')
                                   ->result_array();

            if (!empty($fcm_tokens)) {
                $tokens = array_column($fcm_tokens, 'fcm_token');
                $this->send_fcm_notification(
                    'New Warning Issued',
                    'A new warning has been issued to you. Please take immediate action.',
                    '',
                    $tokens,
                    [
                        'type' => 'warning_issued',
                        'category' => $final_category,
                        'effect' => $final_effect,
                        'clearance_time' => (string)$clearance_time,
                        'action' => 'view'
                    ]
                );
            }

        }

        set_alert('success', translate('information_has_been_saved_successfully'));
        redirect(base_url('todo'));
    } else {
        redirect(base_url('todo'));
    }
}


public function get_view_description()
	{
		$warning_id = $this->input->post('id');
		$this->db->select('*');
		$this->db->from('warnings');
		$this->db->where('id', $warning_id);
		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			$this->data['description'] = $query->row()->reason;
			$this->data['category'] = $query->row()->category;
			$this->data['effect'] = $query->row()->effect;
		} else {
			$this->data['description'] = 'No reason found.';
			$this->data['category'] = 'No category found.';
			$this->data['effect'] = 'No effect found.';
		}

		$this->load->view('todo/get_view_description', $this->data);
	}

    public function delete($id = '')
    {
        if (get_permission('todo', 'is_delete')) {
            $this->db->where('id', $id);
            $this->db->delete('warnings');
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
            $leave = $this->db->get('warnings')->row();
            if ($file != $leave->enc_file_name) {
                access_denied();
            }
            $this->load->helper('download');
            $fileData = file_get_contents('./uploads/attachments/warnings/' . $leave->enc_file_name);
            force_download($leave->orig_file_name, $fileData);
        }
    }


public function send_reminder()
{
	if (!$this->input->post('warning_id')) {
		show_404();
	}

		$id = $this->input->post('warning_id');
		$penalty_workday = $this->input->post('penalty_workday');
		$staff_id = $this->input->post('staff_id');
		$penalty_reason = $this->input->post('penalty_reason');

		// First get existing penalty
		$this->db->select('penalty');
		$this->db->where('id', $id);
		$query = $this->db->get('warnings');
		$row = $query->row();

		$existing_penalty = !empty($row->penalty) ? $row->penalty : 0;

		// Add old + new
		$new_penalty = $existing_penalty + $penalty_workday;

		$arrayLeave = array(
			'status'    => 4,
			'penalty'   => $new_penalty,
			'penalty_reason'   => $penalty_reason,
		);

		$this->db->where('id', $id);
		$this->db->update('warnings', $arrayLeave);


	//$formatted_date = date('jS F, Y, h:iA', strtotime($penalty_workday_raw));
	$dayText = ($penalty_workday > 1) ? 'days' : 'day';
	$warning = $this->db->where('id', $id)->get('warnings')->row_array();

	if ($warning && $warning['status'] == 4) {
		$staff = $this->db->where('id', $warning['user_id'])->get('staff')->row_array();

		if ($staff && !empty($staff['email'])) {
			$to_email = $staff['email'];
			$to_name = $staff['name'];
			$reason = $warning['reason'];
			$clearance_time =  $warning['clearance_time'];
			$mail_subject = 'Reminder: Pending Warning';

			$mail_body = "<html>
					  <body style='font-family:Arial, sans-serif; background:#f9f9f9; padding:20px;'>
						<table style='max-width:600px; margin:auto; background:#fff; border-radius:8px; padding:20px; box-shadow:0 0 10px rgba(0,0,0,0.05);'>
						  <tr><td style='text-align:center;'>
							<h2 style='color:#d32f2f;'>Reminder: Pending Warning</h2>
						  </td></tr>
						  <tr><td style='font-size:15px; color:#333;'>
							<p>Dear <strong>{$to_name}</strong>,</p>
							<p>As part of a disciplinary action, you are assigned penalty workday for <strong>{$penalty_workday}</strong> {$dayText}.</p>
							<p>Please log in to the system to view full details and acknowledge.</p>
							<p style='margin-top:20px;'>Thank you,<br><strong>Employee Max Portal (EMP)</strong></p>
						</td></tr>
						</table>
					  </body>
					</html>";

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
			];

			$this->email_model->send_email_yandex($email_data);
			set_alert('success', 'Reminder sent successfully to ' . $to_name . '.');
		} else {
			set_alert('error', 'Staff email not found.');
		}
	} else {
		set_alert('error', 'Warning not found or already resolved.');
	}

	redirect($_SERVER['HTTP_REFERER']);
}

	public function reports()
	{
		if (!get_permission('penalty_report', 'is_view')) {
			access_denied();
		}

		if (isset($_POST['search'])) {
			$daterange = explode(' - ', $this->input->post('daterange'));
			$start = date("Y-m-d", strtotime($daterange[0]));
			$end = date("Y-m-d", strtotime($daterange[1]));

			// Pass start and end as an array
			$this->data['penalty_days'] = $this->warning_model->getPenalties($start, $end);
		}

		$this->data['title'] = translate('penalty_report');
		$this->data['sub_page'] = 'todo/reports';
		$this->data['main_menu'] = 'penalty_report';
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

	public function create_warning() {
		if (!get_permission('todo', 'is_add')) {
			access_denied();
		}

		$user_id = $this->input->get('user_id');
		$reason  = urldecode($this->input->get('reason'));

		$this->data['staff_list'] = $this->app_lib->getSelectList('staff');
		unset($data['staff_list'][1]); // remove superadmin

		$this->data['default_user_id'] = $user_id;
		$this->data['default_reason']  = $reason;

        $this->data['main_menu']        = 'todo';
        $this->data['headerelements']   = array(
            'css' => array(
                'vendor/dropify/css/dropify.min.css',
				'vendor/summernote/summernote.css',
            ),
            'js' => array(
                'vendor/dropify/js/dropify.min.js',
                'vendor/moment/moment.js',
				'vendor/summernote/summernote.js',
            ),
        );

        $this->data['title']            = translate('To-Do');
        $this->data['sub_page']         = 'dashboard/warning_form';
        $this->load->view('layout/index', $this->data);
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

    // Check if employee has unsatisfied task-related warnings
    public function has_unsatisfied_task_warnings($user_id)
    {
        $result = $this->db->select('id')
            ->from('warnings')
            ->where('user_id', $user_id)
            ->where('task_unique_id IS NOT NULL')
            ->where('manager_review', 5)
            ->get();

        return $result->num_rows() > 0;
    }

}
