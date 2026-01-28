<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends My_Controller {

    private $serviceAccountPath = FCPATH . 'assets/notification/service-account.json';
    private $logFile = FCPATH . 'assets/notification/logs/fcm_log.txt';

    public function __construct() {
        parent::__construct();
        $this->load->database();
		$this->load->model('leave_model');
        $this->load->model('email_model');
		$this->load->model('sop_model');
        $this->load->model('rdc_model');
        $this->load->model('dashboard_model');
        $this->load->model('cashbook_model');
    }

	public function rdc_reminder()
	{
		// Get global pre_reminder and escalation_levels
		$settings = $this->db->get('global_settings')->row(); // returns object
		$pre_reminder = isset($settings->pre_reminder) ? (int)$settings->pre_reminder : 0;
		$escalation_levels = isset($settings->escalation_levels) ? json_decode($settings->escalation_levels, true) : [];

		if ($pre_reminder <= 0) return;

		$now = new DateTime();
		$current_day = strtolower($now->format('l'));
		$current_date = (int)$now->format('j');

		$notifications = $this->db->get_where('rdc_notifications', ['is_active' => 1])->result();

		foreach ($notifications as $notif) {
			$rdc = $this->db->get_where('rdc', ['id' => $notif->rdc_id])->row();
			if (!$rdc) continue;

			$schedule_time = null;
			if ($notif->frequency == 'daily' && !empty($notif->daily_time)) {
				$schedule_time = DateTime::createFromFormat('H:i:s', $rdc->due_time);
			} elseif ($notif->frequency == 'weekly' && $notif->weekly_day == $current_day && !empty($notif->weekly_time)) {
				$schedule_time = DateTime::createFromFormat('H:i:s', $rdc->due_time);
			} elseif ($notif->frequency == 'bimonthly' && ((int)$notif->bimonthly_day1 === $current_date || (int)$notif->bimonthly_day2 === $current_date) && !empty($notif->bimonthly_time)) {
				$schedule_time = DateTime::createFromFormat('H:i:s', $rdc->due_time);
			} elseif ($notif->frequency == 'monthly' && (int)$notif->monthly_day === $current_date && !empty($notif->monthly_time)) {
				$schedule_time = DateTime::createFromFormat('H:i:s', $rdc->due_time);
			} elseif ($notif->frequency == 'yearly' && (int)$notif->yearly_month === (int)date('n') && (int)$notif->yearly_day === $current_date && !empty($notif->yearly_time)) {
				$schedule_time = DateTime::createFromFormat('H:i:s', $rdc->due_time);
			}

			if ($schedule_time) {
				$schedule_time->setDate((int)date('Y'), (int)date('m'), (int)date('d'));

				$staff = $this->db->get_where('staff', ['id' => $rdc->assigned_user])->row();
				if (!$staff) continue;

				$staff_name = $staff->name;
				$department_id = $staff->department;
				$department = $this->db->get_where('staff_department', ['id' => $department_id])->row();
				$department_name = $department ? $department->name : 'Department';

				$config = $this->email_model->get_email_config();
				$bot_token = $telegram_bot;
				$chat_id = $telegram_chatID;

				if ($now < $schedule_time) {
					$diff = $now->diff($schedule_time);
					$diff_minutes = $diff->h * 60 + $diff->i;

					if ($diff_minutes <= $pre_reminder) {
						// Send Reminder Notification
						$this->db->insert('notifications', [
							'user_id'    => $rdc->assigned_user,
							'type'       => 'rdc_task_reminder',
							'title'      => 'RDC Task Reminder',
							'message'    => 'Dear ' . $staff_name . ', your RDC task "' . $rdc->title . '" is scheduled to be completed in ' . $diff_minutes . ' minute' . ($diff_minutes > 1 ? 's' : '') . '. This is a reminder to prepare accordingly.',
							'url'        => base_url('rdc'),
							'is_read'    => 0,
							'created_at' => date('Y-m-d H:i:s')
						]);

						// Deactivate & re-insert notification row
						$this->db->where('id', $notif->id)->update('rdc_notifications', ['is_active' => 0]);
						$this->db->insert('rdc_notifications', [
							'rdc_id' => $notif->rdc_id,
							'frequency' => $notif->frequency,
							'daily_time' => $notif->daily_time,
							'weekly_day' => $notif->weekly_day,
							'weekly_time' => $notif->weekly_time,
							'bimonthly_day1' => $notif->bimonthly_day1,
							'bimonthly_day2' => $notif->bimonthly_day2,
							'bimonthly_time' => $notif->bimonthly_time,
							'monthly_day' => $notif->monthly_day,
							'monthly_time' => $notif->monthly_time,
							'yearly_month' => $notif->yearly_month,
							'yearly_day' => $notif->yearly_day,
							'yearly_time' => $notif->yearly_time,
							'is_active' => 1,
							'created_at' => date('Y-m-d H:i:s'),
						]);

						// Telegram Reminder
						$due_time_formatted = date('g:i A', strtotime($rdc->due_time));
						$today_display = date('d M Y');
						$tg_message = "🛎️ *RDC Task Reminder*\n\n" .
							"📅 *Date:* {$today_display}\n" .
							"👤 *Name:* {$staff_name}\n" .
							"🏢 *Department:* {$department_name}\n\n" .
							"📌 *Task:* {$rdc->title}\n" .
							"⏰ *Due in:* {$diff_minutes} minute" . ($diff_minutes > 1 ? 's' : '') . "\n" .
							"🕒 *Scheduled Time:* {$due_time_formatted}\n\n" .
							"🔗 [Open Task](" . base_url('rdc') . ")";

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

						// Email to Staff + CC Manager
						$to_email = $staff->email;
						$to_name = $staff_name;

						$manager = $this->db->select('staff.email, staff.name')
							->from('staff')
							->join('login_credential', 'login_credential.user_id = staff.id')
							->where('login_credential.role', 8)
							->where('staff.department', $department_id)
							->where('login_credential.active', 1)
							->get()
							->row_array();

						if ($manager && !empty($manager['email'])) {
							$cc_email = $manager['email'];

							$mail_body = "<html><body style='font-family:Arial,sans-serif;'><p>Dear <strong>{$to_name}</strong>,</p>
							<p>This is an automated reminder for the RDC task:</p>
							<p><strong>Task:</strong> {$rdc->title}</p>
							<p><strong>Time Left:</strong> {$diff_minutes} minutes</p>
							<p>Please ensure it's completed on time.</p><br><p>EMP</p></body></html>";

							$email_data = [
								'smtp_host' => $config['smtp_host'],
								'smtp_auth' => true,
								'smtp_user' => $config['smtp_user'],
								'smtp_pass' => $config['smtp_pass'],
								'smtp_secure' => $config['smtp_encryption'],
								'smtp_port' => $config['smtp_port'],
								'from_email' => $config['email'],
								'from_name' => 'EMP Admin',
								'to_email' => $to_email,
								'to_name' => $to_name,
								'subject' => 'RDC Task Reminder',
								'body' => $mail_body,
								'cc' => $cc_email
							];

							$this->email_model->send_email_yandex($email_data);
						}

						// Send FCM notification
						$fcm_tokens = $this->get_staff_fcm_tokens([$rdc->assigned_user]);
						if (!empty($fcm_tokens)) {
							$this->send_fcm_notification(
								'RDC Task Reminder',
								'Your RDC task "' . $rdc->title . '" is due in ' . $diff_minutes . ' minute' . ($diff_minutes > 1 ? 's' : ''),
								'',
								$fcm_tokens,
								['type' => 'rdc_reminder', 'rdc_id' => $rdc->id]
							);
						}
					}
				} else {
					// 🔥 Escalation Logic (only runs if overdue)
					$overdue_minutes = round(($now->getTimestamp() - $schedule_time->getTimestamp()) / 60, 2);

					foreach ($escalation_levels as $level) {
						$required_minutes = ((int)$level['delay_hours']) * 60;

						if ($overdue_minutes >= $required_minutes) {
							$this->db->select('staff.name, staff.email')
								->from('staff')
								->join('login_credential', 'login_credential.user_id = staff.id')
								->where('login_credential.role', $level['role_id'])
								->where('login_credential.active', 1);

							if ((int)$level['role_id'] === 8) {
								$this->db->where('staff.department', $department_id);
							}

							$users = $this->db->get()->result();

							foreach ($users as $user) {
								$subject = "Escalation Notice: Overdue RDC Task";
								$body = "<html><body><p>Dear {$user->name},</p>
								<p>The following RDC task has exceeded its scheduled time by {$overdue_minutes} minutes:</p>
								<ul><li><strong>Task:</strong> {$rdc->title}</li>
								<li><strong>Assigned To:</strong> {$staff_name}</li>
								<li><strong>Scheduled Time:</strong> {$schedule_time->format('h:i A')}</li>
								<li><strong>Delay:</strong> {$overdue_minutes} minutes</li></ul>
								<p>Please take necessary action.</p></body></html>";

								$email_data = [
									'smtp_host' => $config['smtp_host'],
									'smtp_auth' => true,
									'smtp_user' => $config['smtp_user'],
									'smtp_pass' => $config['smtp_pass'],
									'smtp_secure' => $config['smtp_encryption'],
									'smtp_port' => $config['smtp_port'],
									'from_email' => $config['email'],
									'from_name' => 'EMP Admin',
									'to_email' => $user->email,
									'to_name' => $user->name,
									'subject' => $subject,
									'body' => $body
								];

								$this->email_model->send_email_yandex($email_data);

								$tg_message = "⚠️ *RDC Task Escalation Alert*\n\n" .
									"📌 *Task:* {$rdc->title}\n" .
									"👤 *Assigned:* {$staff_name}\n" .
									"⏰ *Scheduled:* {$schedule_time->format('h:i A')}\n" .
									"🚨 *Overdue by:* {$overdue_minutes} minutes\n\n" .
									"🔗 [View Task](" . base_url('rdc') . ")";

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

								log_message('debug', "Escalation email + Telegram sent to {$user->email}");

								// Send FCM notification
								$fcm_tokens = $this->get_staff_fcm_tokens([$rdc->assigned_user]);
								if (!empty($fcm_tokens)) {
									$this->send_fcm_notification(
										'RDC Task Escalation Alert',
										'Your RDC task "' . $rdc->title . '" is escalated',
										'',
										$fcm_tokens,
										['type' => 'rdc_escalation', 'rdc_id' => $rdc->id]
									);
								}
							}
						}
					}
				}
			}
		}

		echo json_encode(['status' => 'success', 'message' => 'RDC reminders and escalations processed.']);
	}

	public function create_recurring_rdc_tasks()
	{
		date_default_timezone_set('Asia/Dhaka');
		$today = date('Y-m-d');
		$weekday = date('l');
		$day_of_month = date('j');

		log_message('info', 'RDC Task Creation started for date: ' . $today);

		// Function to check if date is a working day
		$is_working_day = function($date) {
			$check_date = new DateTime($date);
			$day_name = $check_date->format('l');

			if ($day_name === 'Friday' || $day_name === 'Saturday') {
				return false;
			}

			try {
				$holiday_query = $this->db->where('type', 'holiday')
					->where('start_date <=', $date)
					->where('end_date >=', $date)
					->get('event');

				if ($holiday_query && $holiday_query->num_rows() > 0) {
					return false;
				}
			} catch (Exception $e) {
				log_message('error', 'Holiday check failed: ' . $e->getMessage());
			}

			return true;
		};

		$get_next_working_day = function($date) use ($is_working_day) {
			$check_date = new DateTime($date);
			$max_attempts = 10;
			$attempts = 0;

			while ($attempts < $max_attempts) {
				$date_str = $check_date->format('Y-m-d');
				if ($is_working_day($date_str)) {
					return $date_str;
				}
				$check_date->modify('+1 day');
				$attempts++;
			}
			return $date;
		};

		$due_date = $is_working_day($today) ? $today : $get_next_working_day($today);

		try {
			$notifications = $this->db->where('is_active', 1)->get('rdc_notifications')->result_array();

			if (empty($notifications)) {
				log_message('info', 'No active RDC notifications found');
				echo json_encode(['status' => 'success', 'message' => 'No active notifications to process']);
				return;
			}

			$tasks_created = 0;

			foreach ($notifications as $notify) {
				$create_task = false;

				switch ($notify['frequency']) {
					case 'daily':
						$create_task = $is_working_day($today);
						break;
					case 'weekly':
						$create_task = (strtolower($notify['weekly_day']) == strtolower($weekday));
						break;
					case 'bimonthly':
						$create_task = (intval($notify['bimonthly_day1']) == intval($day_of_month) ||
									   intval($notify['bimonthly_day2']) == intval($day_of_month));
						break;
					case 'monthly':
						$create_task = (intval($notify['monthly_day']) == intval($day_of_month));
						break;
					case 'yearly':
						$create_task = (intval($notify['yearly_month']) == intval(date('n')) &&
									   intval($notify['yearly_day']) == intval($day_of_month));
						break;
					default:
						log_message('error', 'Unknown frequency: ' . $notify['frequency']);
						continue 2;
				}

				if (!$create_task) {
					log_message('debug', 'Task creation skipped for RDC ID: ' . $notify['rdc_id'] . ', Frequency: ' . $notify['frequency']);
					continue;
				}

				$rdc = $this->db->where('id', $notify['rdc_id'])->get('rdc')->row_array();
				if (!$rdc) {
					log_message('error', 'RDC not found for ID: ' . $notify['rdc_id']);
					continue;
				}

				$assigned_user = $this->get_assigned_user_with_flag($rdc, $today);
				if (!$assigned_user) {
					log_message('warning', 'No user assigned for RDC ID: ' . $rdc['id']);
					continue;
				}

				$task_created = $this->create_rdc_task_record($rdc, $assigned_user, $due_date, $notify);

				if ($task_created) {
					$tasks_created++;
					log_message('info', 'RDC task created for user: ' . $assigned_user . ', RDC: ' . $rdc['title']);
				}
			}

			log_message('info', 'RDC Task Creation completed. Tasks created: ' . $tasks_created);
			echo json_encode(['status' => 'success', 'message' => 'RDC recurring task creation complete. Tasks created: ' . $tasks_created]);

		} catch (Exception $e) {
			log_message('error', 'RDC Task Creation failed: ' . $e->getMessage());
			echo json_encode(['status' => 'error', 'message' => 'Task creation failed: ' . $e->getMessage()]);
		}
	}

	private function get_assigned_user_with_flag($rdc, $today)
	{
		if ($rdc['is_random_assignment'] != 1) {
			return $this->get_assigned_user($rdc, $today);
		}

		$user_pool = json_decode($rdc['user_pool'], true);
		if (empty($user_pool)) {
			return null;
		}

		$available_user = $this->get_next_available_user($rdc['id'], $user_pool, $today);

		if ($available_user) {
			$this->set_assignment_flag($rdc['id'], $available_user, 1);
			return $available_user;
		}

		$this->reset_assignment_flags($rdc['id'], $user_pool);
		return $this->get_next_available_user($rdc['id'], $user_pool, $today);
	}

	private function get_assigned_user($rdc, $today)
	{
		$assigned_user = $rdc['assigned_user'];

		if ($rdc['is_random_assignment'] == 1) {
			log_message('debug', 'Processing random assignment for RDC ID: ' . $rdc['id']);

			if (!empty($rdc['user_pool']) && $rdc['user_pool'] !== 'null') {
				$user_pool = json_decode($rdc['user_pool'], true);
				if (is_array($user_pool) && !empty($user_pool)) {
					$user_pool = array_filter($user_pool, function($id) {
						return is_numeric($id) && $id > 0;
					});

					if (!empty($user_pool)) {
						$available_users = [];
						foreach ($user_pool as $user_id) {
							if (!$this->is_user_on_leave($user_id, $today)) {
								$available_users[] = $user_id;
							}
						}

						if (!empty($available_users)) {
							$assigned_user = $available_users[array_rand($available_users)];
							log_message('debug', 'Random assignment selected user ID: ' . $assigned_user);
						} else {
							log_message('warning', 'All users in pool are on leave for RDC ID: ' . $rdc['id']);
							return null;
						}
					}
				}
			} else {
				$assigned_user = $this->rdc_model->get_next_rotation_staff($rdc['id']);
				if (!$assigned_user) {
					log_message('warning', 'No rotation staff available for RDC ID: ' . $rdc['id']);
					return null;
				}
			}
		} else {
			if ($this->is_user_on_leave($assigned_user, $today)) {
				log_message('warning', 'Assigned user ' . $assigned_user . ' is on leave for RDC ID: ' . $rdc['id']);
				return null;
			}
		}

		return $assigned_user;
	}

	private function get_next_available_user($rdc_id, $user_pool, $today)
	{
		$this->db->select('raf.user_id')
			->from('rdc_assignment_flags raf')
			->where('raf.rdc_id', $rdc_id)
			->where('raf.assignment_flag', 0)
			->where_in('raf.user_id', $user_pool);

		$available_users = $this->db->get()->result_array();
		$available_user_ids = array_column($available_users, 'user_id');

		$final_users = [];
		foreach ($available_user_ids as $user_id) {
			if (!$this->is_user_on_leave($user_id, $today)) {
				$final_users[] = $user_id;
			}
		}

		return !empty($final_users) ? $final_users[array_rand($final_users)] : null;
	}

	private function set_assignment_flag($rdc_id, $user_id, $flag_value)
	{
		$this->db->replace('rdc_assignment_flags', [
			'rdc_id' => $rdc_id,
			'user_id' => $user_id,
			'assignment_flag' => $flag_value
		]);
	}

	private function reset_assignment_flags($rdc_id, $user_pool)
	{
		foreach ($user_pool as $user_id) {
			$this->db->replace('rdc_assignment_flags', [
				'rdc_id' => $rdc_id,
				'user_id' => $user_id,
				'assignment_flag' => 0
			]);
		}

		log_message('info', "Reset assignment flags for RDC ID: {$rdc_id}");
	}

	private function is_user_on_leave($user_id, $date)
	{
		try {
			$leave_check = $this->db->where('user_id', $user_id)
				->where('start_date <=', $date)
				->where('end_date >=', $date)
				->where_in('status', [1, 2])
				->get('leave_application');

			return ($leave_check && $leave_check->num_rows() > 0);
		} catch (Exception $e) {
			log_message('error', 'Leave check failed for user ' . $user_id . ': ' . $e->getMessage());
			return false;
		}
	}

	private function create_rdc_task_record($rdc, $assigned_user, $due_date, $notify)
	{
		try {
			// Fetch SOP data (handle multiple SOPs)
			$sop_ids = !empty($rdc['sop_ids']) ? json_decode($rdc['sop_ids'], true) : [];
			if (empty($sop_ids)) return false;

			// Get all SOPs and combine their stages
			$sops = $this->db->where_in('id', $sop_ids)->get('sop')->result_array();
			if (empty($sops)) return false;

			$executor_stages = [];
			$verifier_stages = [];
			$verifier_roles = [];
			$total_expected_time = 0;

			foreach ($sops as $sop) {
				if (!empty($sop['executor_stage'])) {
					$executor_stages[] = $sop['executor_stage'];
				}
				if (!empty($sop['verifier_stage'])) {
					$verifier_stages[] = $sop['verifier_stage'];
				}
				if (!empty($sop['verifier_role'])) {
					$roles = explode(',', $sop['verifier_role']);
					$verifier_roles = array_merge($verifier_roles, $roles);
				}

				if (!empty($sop['expected_time'])) {
					$time_str = strtolower(trim($sop['expected_time']));
					$hours = 0;

					preg_match('/\d+/', $time_str, $matches);
					$value = isset($matches[0]) ? (float)$matches[0] : 0;

					if (strpos($time_str, 'day') !== false) {
						$hours = $value * 24;
					} elseif (strpos($time_str, 'hour') !== false) {
						$hours = $value;
					} elseif (strpos($time_str, 'minute') !== false) {
						$hours = $value / 60;
					} else {
						$hours = $value;
					}

					$total_expected_time += $hours;
				}
			}

			$combined_executor_stages = json_encode(array_unique($executor_stages));
			$combined_verifier_stages = json_encode(array_unique($verifier_stages));
			$combined_verifier_roles = implode(',', array_unique(array_filter($verifier_roles)));

			// Calculate due times based on frequency
			$due_time = $rdc['due_time'];
			$verifier_due_time = $rdc['verifier_due_time'];

			if ($rdc['frequency'] === 'daily') {
				$original_time = date('H:i:s', strtotime($rdc['due_time']));
				$due_time = $due_date . ' ' . $original_time;

				$verifier_original_time = date('H:i:s', strtotime($rdc['verifier_due_time']));
				$verifier_due_time = date('Y-m-d', strtotime('+3 days')) . ' ' . $verifier_original_time;
			} elseif ($rdc['frequency'] === 'weekly') {
				$original_time = date('H:i:s', strtotime($rdc['due_time']));
				$due_time = date('Y-m-d', strtotime($due_date . ' +1 day')) . ' ' . $original_time;

				$verifier_original_time = date('H:i:s', strtotime($rdc['verifier_due_time']));
				$verifier_due_time = date('Y-m-d', strtotime('+3 days')) . ' ' . $verifier_original_time;
			} elseif ($rdc['frequency'] === 'bimonthly') {
				$original_time = date('H:i:s', strtotime($rdc['due_time']));
				$due_time = date('Y-m-d', strtotime($due_date . ' +2 days')) . ' ' . $original_time;

				$verifier_original_time = date('H:i:s', strtotime($rdc['verifier_due_time']));
				$verifier_due_time = date('Y-m-d', strtotime('+3 days')) . ' ' . $verifier_original_time;
			} elseif ($rdc['frequency'] === 'monthly') {
				$original_time = date('H:i:s', strtotime($rdc['due_time']));
				$due_time = date('Y-m-d', strtotime($due_date . ' +5 days')) . ' ' . $original_time;

				$verifier_original_time = date('H:i:s', strtotime($rdc['verifier_due_time']));
				$verifier_due_time = date('Y-m-d', strtotime('+5 days')) . ' ' . $verifier_original_time;
			} elseif ($rdc['frequency'] === 'yearly') {
				$original_time = date('H:i:s', strtotime($rdc['due_time']));
				$due_time = date('Y-m-d', strtotime($due_date . ' +5 days')) . ' ' . $original_time;

				$verifier_original_time = date('H:i:s', strtotime($rdc['verifier_due_time']));
				$verifier_due_time = date('Y-m-d', strtotime('+10 days')) . ' ' . $verifier_original_time;
			}

			$verifier_required = isset($rdc['verifier_required']) && $rdc['verifier_required'] == 1;

			// Insert into rdc_task
			$task_data = [
				'title' => $rdc['title'],
				'description' => $rdc['description'],
				'frequency' => $rdc['frequency'],
				'assigned_user' => $assigned_user,
				'is_random_assignment' => $rdc['is_random_assignment'],
				'user_pool' => $rdc['user_pool'],
				'due_time' => $due_time,
				'verifier_due_time' => $verifier_required ? $verifier_due_time : null,
				'verifier_required' => $rdc['verifier_required'] ?? 1,
				'sop_id' => $sop_ids[0],
				'sop_ids' => json_encode($sop_ids),
				'rdc_id' => $rdc['id'],
				'is_proof_required' => $rdc['is_proof_required'],
				'pre_reminder_enabled' => $rdc['pre_reminder_enabled'],
				'escalation_enabled' => $rdc['escalation_enabled'],
				'executor_stages' => $combined_executor_stages,
				'verifier_stages' => $verifier_required ? $combined_verifier_stages : null,
				'verified_by' => $verifier_required ? $combined_verifier_roles : null,
				'verify_status' => $verifier_required ? 1 : 4,
				'created_by' => $rdc['created_by'],
			];

			$this->db->insert('rdc_task', $task_data);
			$task_id = $this->db->insert_id();

			if (!$task_id) return false;

			// Send notifications
			$this->send_task_notifications($rdc, $assigned_user, $due_time);
			$this->create_tracker_entries($rdc, $assigned_user, $due_time, $total_expected_time);
			// Send notification to assigned user
			//$this->send_rdc_task_telegram_notification($assigned_user, $rdc, $due_time);


			return true;

		} catch (Exception $e) {
			log_message('error', 'Failed to create RDC task record: ' . $e->getMessage());
			return false;
		}
	}

	private function send_task_notifications($rdc, $assigned_user, $due_time)
	{
		$staff = $this->db->where('id', $assigned_user)->get('staff')->row_array();
		if (!$staff) return;

		$task_title = $rdc['title'];
		$schedule = date("g:i A", strtotime($due_time));
		$staff_name = $staff['name'];
		$department = $staff['department'];

		$departments = $this->db->where('id', $department)->get('staff_department')->row_array();
		$department_name = $departments ? $departments['name'] : 'Unknown';

		$title = 'RDC Task Generated';
		$message = 'Dear ' . $staff_name . ', your RDC task "' . $task_title . '" has been generated. Please complete the task before: ' . $schedule . '.';

		$this->db->insert('notifications', [
			'user_id'    => $assigned_user,
			'type'       => 'rdc_task_generate',
			'title'      => $title,
			'message'    => $message,
			'url'        => base_url('rdc'),
			'is_read'    => 0,
			'created_at' => date('Y-m-d H:i:s')
		]);

		// Send individual email notification
		$this->send_individual_task_email($assigned_user, $rdc, $due_time);

		// Send personal Telegram notification only
		if (!empty($staff['telegram_id'])) {
			$bot_token = $telegram_bot;
			$today = date('d M Y');

			$tg_message = "🛎️ *RDC Task Generated*\n\n" .
				"📅 *Date:* {$today}\n" .
				"👤 *Name:* {$staff_name}\n" .
				"🏢 *Department:* {$department_name}\n\n" .
				"📌 *Task:* {$task_title}\n" .
				"🕒 *Due Time:* {$schedule}\n\n" .
				"🔗 [Open Task](" . base_url('rdc') . ")";

			$payload = [
				'chat_id' => $staff['telegram_id'],
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

			log_message('debug', "Personal RDC notification sent to {$staff_name}");
		} else {
			log_message('debug', "No telegram_id for staff: {$staff_name}");
		}

		// Send FCM notification
		$task_fcm_tokens = $this->get_staff_fcm_tokens([$assigned_user]);
		if (!empty($task_fcm_tokens)) {
			$this->send_fcm_notification(
				'RDC Task Generated',
				'New RDC task "' . $task_title . '" has been assigned to you. Due: ' . $schedule,
				'',
				$task_fcm_tokens,
				['type' => 'rdc_task_generated', 'rdc_id' => $rdc['id']]
			);
		}
	}

	private function send_individual_task_email($assigned_user, $rdc, $due_time)
	{
		$staff = $this->db->where('id', $assigned_user)->get('staff')->row_array();
		if (!$staff || empty($staff['email'])) return;

		$config = $this->email_model->get_email_config();
		$task_title = $rdc['title'];
		$schedule = date("g:i A", strtotime($due_time));
		$staff_name = $staff['name'];
		$department_id = $staff['department'];

		// Get department manager for CC
		$manager = $this->db->select('staff.email, staff.name')
			->from('staff')
			->join('login_credential', 'login_credential.user_id = staff.id')
			->where('login_credential.role', 8)
			->where('staff.department', $department_id)
			->where('login_credential.active', 1)
			->get()
			->row_array();

		$mail_body = "<html><body style='font-family:Arial,sans-serif;'>
			<p>Dear <strong>{$staff_name}</strong>,</p>
			<p>A new RDC task has been assigned to you:</p>
			<p><strong>Task:</strong> {$task_title}</p>
			<p><strong>Due Time:</strong> {$schedule}</p>
			<p>Please complete the task on time.</p>
			<br><p>EMP</p></body></html>";

		$email_data = [
			'smtp_host' => $config['smtp_host'],
			'smtp_auth' => true,
			'smtp_user' => $config['smtp_user'],
			'smtp_pass' => $config['smtp_pass'],
			'smtp_secure' => $config['smtp_encryption'],
			'smtp_port' => $config['smtp_port'],
			'from_email' => $config['email'],
			'from_name' => 'EMP Admin',
			'to_email' => $staff['email'],
			'to_name' => $staff_name,
			'subject' => 'New RDC Task Assigned',
			'body' => $mail_body,
			'cc' => $manager ? $manager['email'] : null
		];

		$this->email_model->send_email_yandex($email_data);
	}

	private function create_tracker_entries($rdc, $assigned_user, $due_time, $total_expected_time)
	{
		// Add to tracker departments
		$rdc_dept = $this->db->where('identifier', 'RDC')->get('tracker_departments')->row();
		if (!$rdc_dept) {
			$this->db->insert('tracker_departments', [
				'title' => 'RDC Tasks',
				'identifier' => 'RDC',
				'description' => 'Recurring Discipline & Compliance Tasks',
				'default_status' => 'todo',
				'is_private' => 0,
				'auto_join' => 1,
				'owner_id' => 1,
				'assigned_issuer' => 1
			]);
		}

		// Get max number for RDC prefix
		$this->db->select('MAX(CAST(SUBSTRING_INDEX(unique_id, "-", -1) AS UNSIGNED)) as max_num');
		$this->db->like('unique_id', 'RDC-', 'after');
		$this->db->from('tracker_issues');
		$row = $this->db->get()->row();

		$max_num = $row && $row->max_num ? (int)$row->max_num : 0;
		$tracker_unique_id = 'RDC-' . ($max_num + 1);

// Prepare sop_ids for tracker_issues (extract first SOP ID as string)
        $tracker_sop_ids = null;
        if (!empty($rdc['sop_ids'])) {
            $decoded_sop_ids = html_entity_decode($rdc['sop_ids']);
            $sop_array = json_decode($decoded_sop_ids, true);
            if (is_array($sop_array) && !empty($sop_array)) {
                $tracker_sop_ids = (string)$sop_array[0];
            }
        }

		$this->db->insert('tracker_issues', [
			'created_by' => 1,
			'unique_id' => $tracker_unique_id,
			'department' => 'RDC',
			'task_title' => $rdc['title'],
			'task_description' => $rdc['description'],
			'task_status' => 'todo',
			'priority_level' => 'Medium',
			'assigned_to' => $assigned_user,
			'coordinator' => !empty($rdc['coordinator']) ? $rdc['coordinator'] : null,
            'task_type' => !empty($rdc['task_type']) ? $rdc['task_type'] : null,
            'milestone' => !empty($rdc['milestone']) ? $rdc['milestone'] : null,
            'component' => !empty($rdc['initiatives']) ? $rdc['initiatives'] : null,
            'sop_ids' => $tracker_sop_ids,
			'estimation_time' => $total_expected_time > 0 ? $total_expected_time : null,
			'estimated_end_time' => date('Y-m-d 23:59:59', strtotime($due_time)),
			'logged_at' => date('Y-m-d H:i:s')
		]);

		$tracker_issue_id = $this->db->insert_id();

		$tz = new DateTimeZone('Asia/Dhaka');
		$task_log_data = [
			'staff_id'    => $assigned_user,
			'location'    => 'RDC Task',
			'task_title'  => $rdc['title'],
			'start_time'  => (new DateTime('now', $tz))->format('Y-m-d H:i:s'),
			'task_status' => 'In Progress',
			'logged_at'   => (new DateTime('now', $tz))->format('Y-m-d H:i:s'),
			'tracker_id' => $tracker_issue_id,
		];

		$this->db->insert('staff_task_log', $task_log_data);
	}

	public function send_todays_task_status()
	{
		$today = date('Y-m-d');
		$tasks = $this->db->where('DATE(created_at)', $today)->get('rdc_task')->result(); // adjust table name if needed

		if (empty($tasks)) return;

		$task_lines = [];
		foreach ($tasks as $task) {
			$staff = $this->db->get_where('staff', ['id' => $task->assigned_user])->row();
			$staff_name = $staff ? $staff->name : 'Unknown';

			$line = "📌 *Task:* {$task->title}\n";
			$line .= "👤 *Assigned To:* {$staff_name}\n";
			$line .= "🕒 *Due Time:* " . date('g:i A', strtotime($task->due_time)) . "\n";

			if ($task->task_status == 2) {
				$line .= "📊 *Executor Review:* " . ($task->task_status == 2 ? '✅ Completed' : '⏳ Pending') . "\n";
				$line .= "🧍‍♂️ *Executor Cleared On:* " . date('g:i A', strtotime($task->exe_cleared_on)) . "\n";
			}

			if ($task->verify_status == 2) {
				$line .= "📊 *Verifier Review:* " . ($task->verify_status == 2 ? '✅ Completed' : '⏳ Pending') . "\n";
				$line .= "🧍‍♀️ *Verifier Cleared On:* " . date('g:i A', strtotime($task->ver_cleared_on)) . "\n";
			}

			$line .= "\n";
			$task_lines[] = $line;
		}

		$final_message = "📅 *Today's Task Summary (" . date('d M Y') . ")*\n\n" . implode("\n", $task_lines);

	   // Telegram send block
			$bot_token = $telegram_bot;
			$chat_id = $telegram_chatID;

		$payload = [
			'chat_id' => $chat_id,
			'text' => $final_message,
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

		// ✅ Send Email to role 3, 5, 8
		$staff = $this->db->get_where('staff', ['id' => $task->assigned_user])->row();

		// 5. Send email to staff + CC to department manager
		$department_id  = $staff->department;

		// 2. Get all staff (roles 2, 3, 5)
		$this->db->select('staff.email, staff.name')
			->from('staff')
			->join('login_credential', 'login_credential.user_id = staff.id')
			->where_in('login_credential.role', [2, 3, 5])
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

		if ($manager && !empty($manager['email'])) {
		$to_name = $manager['name'];
		$to_email = $manager['email'];
		$cc_emails = array_column($cc_list, 'email');

		// Email subject and body
		$mail_subject = 'RDC Task Updates of ' . $today;

		$mail_body = "
		<html>
		<body style='font-family:Arial,sans-serif;padding:20px;background:#f4f4f4'>
			<div style='max-width:600px;margin:auto;background:#fff;padding:20px;border-radius:8px;'>
				<h2 style='color:#0054a6;'>Today's Task Summary</h2>
				<p>Date: <strong>" . date('d M Y') . "</strong></p>
				<pre style='white-space:pre-wrap;font-size:14px;color:#333;'>" . htmlspecialchars(strip_tags($final_message)) . "</pre>
				<p style='font-size:12px;color:#888;'>This is an automated report from EMP.</p>
			</div>
		</body>
		</html>";

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

			$this->email_model->send_email_yandex($email_data);
		}
	}

	public function check_escalation_tasks()
{
    $now = new DateTime();

    $tasks = $this->db->from('rdc_task')
        ->where('(task_status = 1 OR verify_status = 1)', null, false)
        ->where('escalation_enabled', 1)
        ->order_by('created_at', 'DESC')
        ->get()
        ->result();

    $escalated_tasks = [];

    foreach ($tasks as $task) {
        $due_time = !empty($task->due_time) ? DateTime::createFromFormat('Y-m-d H:i:s', $task->due_time) : null;
        $verifier_time = !empty($task->verifier_due_time) ? DateTime::createFromFormat('Y-m-d H:i:s', $task->verifier_due_time) : null;

        $is_due_passed = $due_time && $now > $due_time
            && $task->task_status == 1
            && $task->is_escalated_executor == 0;

        $is_verifier_due_passed = isset($task->verifier_required) && $task->verifier_required == 1
            && $verifier_time && $now > $verifier_time
            && $task->verify_status == 1
            && $task->is_escalated_verifier == 0;

        // Escalate executor if needed
        if ($is_due_passed) {
            $this->db->where('id', $task->id)->update('rdc_task', ['is_escalated_executor' => 1]);
        }

        // Escalate verifier if needed
        if ($is_verifier_due_passed) {
            $this->db->where('id', $task->id)->update('rdc_task', ['is_escalated_verifier' => 1]);
        }

        // Include only if any escalation happened
        if ($is_due_passed || $is_verifier_due_passed) {
            $escalated_tasks[] = [
                'task_id'       => $task->id,
                'title'         => $task->title,
                'assigned_user' => $task->assigned_user,
                'rdc_id'        => $task->rdc_id,
                'due_time'      => $task->due_time,
                'verifier_due_time' => $task->verifier_due_time,
                'task_status'   => $task->task_status,
                'verify_status' => $task->verify_status,
                'escalation_enabled' => (bool)$task->escalation_enabled,
                'is_escalated_executor' => $is_due_passed ? 1 : (int)$task->is_escalated_executor,
                'is_escalated_verifier' => $is_verifier_due_passed ? 1 : (int)$task->is_escalated_verifier,
            ];
        }
    }

    if (!empty($escalated_tasks)) {
        echo json_encode([
            'status' => 'success',
            'count' => count($escalated_tasks),
            'tasks' => $escalated_tasks
        ]);
    } else {
        echo json_encode([
            'status' => 'not_found',
            'message' => 'No overdue escalated tasks found.'
        ]);
    }
}


public function save_telegram_chat_id() {
    $json = file_get_contents('php://input');
    $payload = json_decode($json, true);

    // Validate JSON
    if (empty($payload) || !isset($payload['staff_code']) || !isset($payload['telegram_id'])) {
        $this->output->set_status_header(400)->set_output(json_encode([
            'status' => 'error',
            'message' => 'Invalid or missing JSON data. Required: staff_code, telegram_id'
        ]));
        return;
    }

    $staff_code   = $payload['staff_code'];
    $telegram_id  = $payload['telegram_id'];

    // Lookup staff by staff_code
    $this->db->where('staff_id', $staff_code);
    $staff_row = $this->db->get('staff')->row();

    if (!$staff_row) {
        $this->output->set_status_header(404)->set_output(json_encode([
            'status' => 'error',
            'message' => 'Staff not found for provided staff_code: ' . $staff_code
        ]));
        return;
    }

    // Update telegram_id (chat_id) in staff table
    $this->db->where('id', $staff_row->id);
    $this->db->update('staff', ['telegram_id' => $telegram_id]);

    if ($this->db->affected_rows() > 0) {
        $response = [
            'status'  => 'success',
            'message' => 'Telegram chat_id saved successfully.',
            'staff_id' => $staff_row->id,
            'staff_code' => $staff_code,
            'telegram_id' => $telegram_id
        ];
    } else {
        $response = [
            'status'  => 'no_change',
            'message' => 'No changes made. Telegram ID might already be set to this value.',
            'staff_id' => $staff_row->id
        ];
    }

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
}

    public function save_attendance() {
        $json = file_get_contents('php://input');
        $payload = json_decode($json, true);

        if (empty($payload) || !isset($payload[0]['data'])) {
            $this->output->set_status_header(400)->set_output(json_encode([
                'status' => 'error',
                'message' => 'Invalid or missing JSON data.'
            ]));
            return;
        }

        $data = $payload[0]['data'];


       // Lookup staff by staff.staff_id (employee ID)
		$this->db->where('staff_id', $data['person_id']);
		$staff_row = $this->db->get('staff')->row();

		if (!$staff_row) {
			$this->output->set_status_header(404)->set_output(json_encode([
				'status' => 'error',
				'message' => 'Staff not found for provided person_id: ' . $data['person_id']
			]));
			return;
		}

		$staff_id = $staff_row->id;         // actual staff.id used in attendance
		$branch_id   = $staff_row->branch_id;

        $date          = $data['date'];
        $check_in_time = $data['check_in'];
        $check_out_time = $data['check_out'];
        $check_in_img  = $data['check_in_img'];
        $check_out_img = $data['check_out_img'];

        // Determine if status is 'L' (Late) or 'P' (Present)
        $late_threshold = strtotime('10:30:59');
        $check_in_unix = strtotime($check_in_time);
        $status = ($check_in_unix > $late_threshold) ? 'L' : 'P';

        // Check if the record already exists
        $this->db->where('staff_id', $staff_id);
        $this->db->where('date', $date);
        $existing = $this->db->get('staff_attendance')->row();

        if ($existing) {
            // Update only out_time and check_out_img
            $updateData = [
                'out_time'      => $check_out_time,
               // 'check_out_img' => $this->app_lib->upload_check_out_image('attendance')
                'check_out_img' => $check_out_img
            ];

            $this->db->where('id', $existing->id);
            $this->db->update('staff_attendance', $updateData);

            $response = [
                'status'  => 'updated',
                'message' => 'Attendance record updated successfully.',
                'id'      => $existing->id
            ];
        } else {
            // Insert new attendance
            $insertData = [
                'staff_id'       => $staff_id,
                'status'         => $status,
                'remark'         => '',
                'qr_code'        => 0,
                'in_time'        => $check_in_time,
                //'check_in_img'   => $this->app_lib->upload_check_in_image('attendance'),
                'check_in_img'   => $check_in_img,
                'out_time'       => $check_out_time,
                //'check_out_img'  => $this->app_lib->upload_check_out_image('attendance'),
                'check_out_img'  => $check_out_img,
                'date'           => $date,
                'branch_id'      => $branch_id
            ];

            $this->db->insert('staff_attendance', $insertData);

            $response = [
                'status'  => 'inserted',
                'message' => 'Attendance record inserted successfully.',
                'id'      => $this->db->insert_id()
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }


public function send_warning_emails() {
    $warnings = $this->db
        ->where('email_sent', 0)
		->where('status', 1)
        ->get('warnings')
        ->result_array();

    foreach ($warnings as $row) {
        $staff = $this->db
            ->select('staff.name, staff.department, staff.email')
            ->join('login_credential', 'login_credential.user_id = staff.id')
            ->where('staff.id', $row['user_id'])
            ->get('staff')
            ->row_array();

        if (!$staff || empty($staff['email'])) continue;

        $department_id = $staff['department'];
        $staff_name = $staff['name'];
        $staff_email = $staff['email'];
        $issuer = get_type_name_by_id('staff', $row['issued_by']);
        $clearance_time = $row['clearance_time'];

        // HR + COO
        $this->db->select('staff.email, staff.name')
            ->from('staff')
            ->join('login_credential', 'login_credential.user_id = staff.id')
            ->where_in('login_credential.role', [3, 5])
            ->where('login_credential.active', 1);
        $hr_coo_list = $this->db->get()->result_array();

        // Department manager
        $this->db->select('staff.email, staff.name')
            ->from('staff')
            ->join('login_credential', 'login_credential.user_id = staff.id')
            ->where('login_credential.role', 8)
            ->where('staff.department', $department_id)
            ->where('login_credential.active', 1);
        $manager = $this->db->get()->row_array();

        $cc_emails = array_column($hr_coo_list, 'email');
        if (!empty($manager['email'])) {
            $cc_emails[] = $manager['email'];
        }

        $mail_subject = 'Warning Issued to ' . $staff_name;
        $mail_body = "
        <html>
          <body style='font-family:Arial, sans-serif; background:#f9f9f9; padding:20px;'>
            <table style='max-width:600px; margin:auto; background:#fff; border-radius:8px; padding:20px; box-shadow:0 0 10px rgba(0,0,0,0.05);'>
              <tr><td style='text-align:center;'>
                <h2 style='color:#d32f2f;'>Employee Warning Notice</h2>
              </td></tr>
              <tr><td style='font-size:15px; color:#333;'>
                <p>Dear <strong>{$staff_name}</strong>,</p>
                <p>A task under your responsibility is marked as pending (<strong>{$row['category']}</strong>). Please resolve it and submit a brief explanation via the EMP system within <strong>{$clearance_time}</strong> hours.</p>
                <p style='margin-top:20px;'>Thank you,<br><strong>Employee Max Portal(EMP)</strong></p>
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
            'to_email'      => $staff_email,
            'to_name'       => $staff_name,
            'subject'       => $mail_subject,
            'body'          => $mail_body,
            'cc'            => implode(',', $cc_emails)
        ];
	$this->db->where('id', $row['id'])->update('warnings', [
    'email_sent'    => 1,
    'email_sent_at' => date('Y-m-d H:i:s') // Current timestamp
	]);


     $this->email_model->send_email_yandex($email_data);


    }
}

public function send_separation_emails() {
    $separations = $this->db
        ->where('email_sent', 0)
        //->where('DATE(created_at)', date('Y-m-d'))
        ->get('separation_requests')
        ->result_array();

    foreach ($separations as $row) {
        $staff = $this->db
            ->select('staff.name, staff.department, staff.email')
            ->join('login_credential', 'login_credential.user_id = staff.id')
            ->where('staff.id', $row['user_id'])
            ->get('staff')
            ->row_array();

        if (!$staff || empty($staff['email'])) continue;

        $department_id = $staff['department'];
        $staff_name = $staff['name'];
        $staff_email = $staff['email'];

        $last_working_date = $row['last_working_date'];
        $reason = $row['reason'];

        // Get requester role
        $requestor_role = $this->db->select('login_credential.role')
            ->from('staff')
            ->join('login_credential', 'login_credential.user_id = staff.id')
            ->where('staff.id', $row['user_id'])
            ->get()->row()->role;

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

        // Only proceed if recipient exists
        if (!empty($to_email)) {

            // Email subject and body
            $mail_subject = 'Separation Request Submitted by ' . $staff_name;
            $mail_body = "
            <html>
              <body style='font-family:Arial, sans-serif; background:#f4f4f4; padding:20px;'>
                <table style='max-width:600px; margin:auto; background:#ffffff; border-radius:8px; padding:30px; box-shadow:0 0 10px rgba(0,0,0,0.05);'>
                  <tr><td style='text-align:center;'>
                    <h2 style='color:#2c3e50;'>Employee Separation Request</h2>
                  </td></tr>
                  <tr><td style='font-size:15px; color:#34495e;'>
                    <p>Dear <strong>Concern</strong>,</p>
                    <p>This is to inform you that <strong>{$staff_name}</strong> has submitted a formal separation (resignation) request.</p>
                    <p><strong>Last Working Date:</strong> " . date("d M, Y", strtotime($last_working_date)) . "</p>
                    <p><strong>Reason:</strong><br>" . nl2br(htmlspecialchars($reason)) . "</p>
                    <p>You are requested to log in to the EMP portal to review and take necessary actions.</p>
                    <p style='margin-top:20px;'>Best regards,<br><strong>EMP</strong></p>
                  </td></tr>
                </table>
              </body>
            </html>";

            // Email configuration
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

            // Mark the email as sent
            $this->db->where('id', $row['id'])->update('separation_requests', [
                'email_sent'    => 1,
                'email_sent_at' => date('Y-m-d H:i:s') // Current timestamp
            ]);
        }
    }
}


public function send_event_emails() {
    $events = $this->db
        ->where('email_sent', 0)
        ->get('event')
        ->result_array();

    foreach ($events as $row) {

        $title = $row['title'];
        $event_type = $row['type'];
        $start_date = $row['start_date'];
        $end_date = $row['end_date'];

		// 1. Get all active users except roles 1 and 9
        $this->db->select('staff.email, staff.name')
            ->from('staff')
            ->join('login_credential', 'login_credential.user_id = staff.id')
            ->where_not_in('login_credential.role', [1, 9, 11, 12]) // HR + COO roles
            ->where('login_credential.active', 1);
        $recipients = $this->db->get()->result_array();

            // Build list of CC emails (HR and COO)
            $cc_emails = array_column($recipients, 'email');

            // Email subject and body
           	$mail_subject = 'Company ' . ucfirst($event_type) . ' - ' . $title;
            $mail_body = "
            <html>
			  <body style='font-family:Arial, sans-serif; background:#f4f4f4; padding:20px;'>
				<table style='max-width:600px; margin:auto; background:#ffffff; border-radius:8px; padding:30px; box-shadow:0 0 10px rgba(0,0,0,0.05);'>
				  <tr><td style='text-align:center;'>
					<h2 style='color:#2c3e50;'>Upcoming " . ucfirst($event_type) . " Notification</h2>
				  </td></tr>
				  <tr><td style='font-size:15px; color:#34495e;'>
					<p>Dear Team,</p>
					<p>We are pleased to inform you that a new event has been scheduled at <strong>Employee Max Portal (EMP)</strong>.</p>
					<p><strong>Event Title:</strong> {$title}<br>
					   <strong>Type:</strong> " . ucfirst($event_type) . "<br>
					   <strong>Schedule:</strong> {$start_date} to {$end_date}</p>
					<p>You are kindly requested to check the event details and stay updated via the Events section in your EMP portal.</p>
					<p>If you have any questions or require further information, please contact the HR/Admin department.</p>
					<p style='margin-top:20px;'>Warm regards,<br>
					<strong>EMP Administration</strong></p>
				  </td></tr>
				</table>
			  </body>
			</html>";

            // Email configuration
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
				'to_email'      => 'admin@emp.com.bd',
				'to_name'       => 'EMP Admin',
				'subject'       => $mail_subject,
				'body'          => $mail_body,
				'cc'            => implode(',', $cc_emails),
			];

            // Send the email
            $this->email_model->send_email_yandex($email_data);

            // Mark the email as sent
            $this->db->where('id', $row['id'])->update('event', [
                'email_sent'    => 1,
                'email_sent_at' => date('Y-m-d H:i:s') // Current timestamp
            ]);

    }
}

public function send_rps_emails()
{
    $rps_policies = $this->db
        ->where('email_sent', 1)
        ->get('policy')
        ->result_array();

     if (empty($rps_policies)) {
        echo json_encode(['status' => 'no_policies', 'message' => 'No policies to process']);
        return;
    }

    foreach ($rps_policies as $policy) {
        $title = $policy['title'];
        $description = $policy['description'];

        // Ensure department exists
        $rps_dept = $this->db->where('identifier', 'RPS')->get('tracker_departments')->row();
        if (!$rps_dept) {
            $this->db->insert('tracker_departments', [
                'title'           => 'Rules & Policy',
                'identifier'      => 'RPS',
                'description'     => 'Rules & Policy of EMP',
                'default_status'  => 'todo',
                'is_private'      => 0,
                'auto_join'       => 1,
                'owner_id'        => 1,
                'assigned_issuer' => 1
            ]);
        }

        // Get all active users
        $active_users = $this->db->select('staff.id, staff.name, staff.email')
            ->from('staff')
            ->join('login_credential', 'login_credential.user_id = staff.id')
            ->where('login_credential.active', 1)
            ->where_not_in('login_credential.role', [1, 2, 9, 10, 11, 12])
            ->where_not_in('login_credential.user_id', [22, 23, 28, 25, 31, 37, 49])
            ->get()->result_array();

        $bcc_emails = array_column($active_users, 'email');
        $deadline = date('Y-m-d 23:59:59', strtotime('+7 days'));

        // Create tracker issues for all users
        foreach ($active_users as $user) {
            $max_row = $this->db->select('MAX(CAST(SUBSTRING_INDEX(unique_id, "-", -1) AS UNSIGNED)) as max_num')
                ->like('unique_id', 'RPS-', 'after')
                ->get('tracker_issues')
                ->row();

            $max_num = $max_row && $max_row->max_num ? (int)$max_row->max_num : 0;
            $tracker_unique_id = 'RPS-' . ($max_num + 1);

            $this->db->insert('tracker_issues', [
                'created_by'         => 1,
                'unique_id'          => $tracker_unique_id,
                'department'         => 'RPS',
                'task_title'         => 'Review Rules & Policy: ' . $title,
                'task_description'   => $description,
                'task_status'        => 'todo',
                'priority_level'     => 'Medium',
                'assigned_to'        => $user['id'],
                'estimation_time'    => '1',
                'estimated_end_time' => $deadline,
                'logged_at'          => date('Y-m-d H:i:s')
            ]);

			$tracker_issue_id = $this->db->insert_id();

			// 3) Final payload

			$tz = new DateTimeZone('Asia/Dhaka');
			$task_log_data = [
				'staff_id'    => $user['id'],
				'location'    => 'Review Rules & Policy',
				'task_title'  => 'Review Rules & Policy: ' . $title,
				'start_time'  => (new DateTime('now', $tz))->format('Y-m-d H:i:s'),
				'task_status' => 'In Progress',
				'logged_at'   => (new DateTime('now', $tz))->format('Y-m-d H:i:s'),
				'tracker_id' => $tracker_issue_id,
			];

			$this->db->insert('staff_task_log', $task_log_data);
        }

        // Prepare email
        $mail_subject = 'New Rules & Policy Document Published: "' . $title . '"';
        $mail_body = "
        <html>
        <body style='font-family:Arial,sans-serif; background:#f9f9f9; padding:20px;'>
            <table style='max-width:600px; margin:auto; background:#fff; border-radius:8px; padding:20px; box-shadow:0 0 10px rgba(0,0,0,0.05);'>
                <tr><td style='text-align:center;'>
                    <h2 style='color:#d32f2f;'>Rules & Policy</h2>
                </td></tr>
                <tr><td style='font-size:15px; color:#333;'>
                    <p>Dear Concern,</p>
                    <p>A new document has been published: <strong>{$title}</strong></p>
                    <p><strong>Description:</strong> {$description}</p>
                    <p>Please review it by: <strong>" . date('d M Y', strtotime('+7 days')) . "</strong></p>
                    <p style='margin-top:20px;'>Thank you,<br><strong>Employee Max Portal (EMP)</strong></p>
                </td></tr>
            </table>
        </body>
        </html>";

        // Email configuration
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
            'to_email'      => 'noreply@emp.com.bd', // Use a real or dummy HR address
			'to_name'       => 'EMP Admin',
            'subject'     => $mail_subject,
            'body'        => $mail_body,
            'bcc'         => implode(',', $bcc_emails), // ✅ use BCC here
        ];

        // Send email once for all users
        $this->email_model->send_email_yandex($email_data);

        // Mark email as sent
        $this->db->where('id', $policy['id'])->update('policy', [
            'email_sent'    => 0,
            'email_sent_at' => date('Y-m-d H:i:s')
        ]);
    }

    echo json_encode(['status' => 'success', 'message' => 'RPS emails processed successfully']);
}


public function send_warning_reminder_emails() {
    $warnings = $this->db
        ->where('status', 1)
        ->where('email_sent', 1)
        ->get('warnings')
        ->result_array();

    foreach ($warnings as $row) {
        if (empty($row['email_sent_at']) || empty($row['clearance_time'])) continue;

		$current_time = new DateTime();
        $emailSentTime = new DateTime($row['email_sent_at']);
        $deadline = clone $emailSentTime;
        $deadline->modify("+{$row['clearance_time']} hours");

        if ($current_time <= $deadline) continue; // Still within clearance time

        $staff = $this->db
            ->select('staff.name, staff.department, staff.email')
            ->join('login_credential', 'login_credential.user_id = staff.id')
            ->where('staff.id', $row['user_id'])
            ->get('staff')
            ->row_array();

        if (!$staff || empty($staff['email'])) continue;

        $department_id = $staff['department'];
        $staff_name = $staff['name'];
        $staff_email = $staff['email'];
        $issuer = get_type_name_by_id('staff', $row['issued_by']);

        // HR + COO
        $this->db->select('staff.email, staff.name')
            ->from('staff')
            ->join('login_credential', 'login_credential.user_id = staff.id')
            ->where_in('login_credential.role', [3, 5])
            ->where('login_credential.active', 1);
        $hr_coo_list = $this->db->get()->result_array();

        // Department manager
        $this->db->select('staff.email, staff.name')
            ->from('staff')
            ->join('login_credential', 'login_credential.user_id = staff.id')
            ->where('login_credential.role', 8)
            ->where('staff.department', $department_id)
            ->where('login_credential.active', 1);
        $manager = $this->db->get()->row_array();

        $cc_emails = array_column($hr_coo_list, 'email');
        if (!empty($manager['email'])) {
            $cc_emails[] = $manager['email'];
        }

        $mail_subject = 'Reminder: Clearance Deadline Missed - ' . $staff_name;
        $mail_body = "
        <html>
          <body style='font-family:Arial, sans-serif; background:#f9f9f9; padding:20px;'>
            <table style='max-width:600px; margin:auto; background:#fff; border-radius:8px; padding:20px; box-shadow:0 0 10px rgba(0,0,0,0.05);'>
              <tr><td style='text-align:center;'>
                <h2 style='color:#d32f2f;'>Reminder: Clearance Deadline Passed</h2>
              </td></tr>
              <tr><td style='font-size:15px; color:#333;'>
                <p>Dear <strong>{$staff_name}</strong>,</p>
                <p>Your clearance time for the warning under <strong>{$row['category']}</strong> has expired. You are hereby reminded to resolve the task and submit your explanation within the extended clearance time of <strong>6 hours</strong>.</p>
                <p>This is a final reminder before further disciplinary action is considered.</p>
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
            'to_email'      => $staff_email,
            'to_name'       => $staff_name,
            'subject'       => $mail_subject,
            'body'          => $mail_body,
            'cc'            => implode(',', $cc_emails)
        ];

        // Update clearance time, email_sent flag and sent time
        $this->db->where('id', $row['id'])->update('warnings', [
            'clearance_time' => 6,
            'email_sent'     => 2,
            'email_sent_at'  => date('Y-m-d H:i:s')
        ]);

        $this->email_model->send_email_yandex($email_data);
    }
}


public function update_existing_leave_balances_for_non_regulars()
{
    $today = new DateTime();
    $current_year = (int)date('Y');

    // Get all leave categories
    $leave_categories = $this->db->get('leave_category')->result();
    if (empty($leave_categories)) {
        log_message('error', '[CRON] No leave categories defined.');
        return;
    }

    // Get all non-regular employees
    $employees = $this->db
        ->where_not_in('employee_type', ['regular'])
        ->get('staff')
        ->result();

    foreach ($employees as $emp) {
        $joining_date = new DateTime($emp->joining_date);
        $year_start = new DateTime($current_year . '-01-01');

        // Use year start if employee joined before current year, otherwise use joining date
        $calculation_start_date = ($joining_date < $year_start) ? $year_start : $joining_date;
        $days_since_calculation_start = $calculation_start_date->diff($today)->days;
        $years_completed = $joining_date->diff($today)->y;

        foreach ($leave_categories as $cat) {
            $category_id = (int)$cat->id;
            $default_days = (float)$cat->days;

            // 🔍 Must exist in leave_balance
            $existing = $this->db->get_where('leave_balance', [
                'user_id' => $emp->id,
                'leave_category_id' => $category_id,
                'year' => $current_year
            ])->row();

            if (!$existing) {
                log_message('info', "[SKIP] No existing leave_balance for staff_id: {$emp->id}, category_id: {$category_id}");
                continue;
            }

            $new_total = 0;

            if ($years_completed >= 1) {
                // ✅ Full entitlement after 1 year
                $new_total = $default_days;
            } elseif ($days_since_calculation_start >= 26 && $category_id === 1) {
                // ✅ Prorated Annual Leave only if category_id == 1
                $new_total = floor($days_since_calculation_start / 26);

            } else {
                continue; // not eligible
            }

            if ($new_total <= 0) continue;

            // ✅ Update existing record
            $this->db->where('id', $existing->id)->update('leave_balance', [
                'total_days' => $new_total
            ]);

            log_message('info', "[UPDATED] staff_id: {$emp->id}, category_id: {$category_id}, total_days: {$new_total}");
        }
    }
}


private $bot_token = '';
private $chat_id = '';

/**
 * Get FCM tokens for specific staff members
 */
private function get_staff_fcm_tokens($staff_ids) {
    if (empty($staff_ids)) return [];

    $tokens = $this->db->select('fcm_token')
        ->where_in('id', $staff_ids)
        ->where('fcm_token IS NOT NULL', null, false)
        ->where('fcm_token !=', '')
        ->get('staff')
        ->result_array();

    return array_column($tokens, 'fcm_token');
}

/*  Send FCM notification */

public function send_fcm_notification($title, $text, $image = '', $tokens = null, array $extraData = []) {
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
        } else {
            $failureCount++;
            if (strpos($response, 'UNREGISTERED') !== false) {
                $this->db->where('fcm_token', $fcmToken)->update('staff', ['fcm_token' => NULL]);
            }
        }
    }

    $this->log_message("FCM send completed - Success: $successCount, Failures: $failureCount");
    return ($successCount > 0);
}

private function get_access_token() {
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

    return isset($tokenData['access_token']) ? $tokenData['access_token'] : null;
}

private function create_jwt($serviceAccount) {
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

private function log_message($message) {
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

public function telegram_feedback()
{
    $input_raw = file_get_contents("php://input");
    file_put_contents(FCPATH . "telegram_debug.txt", "[" . date('Y-m-d H:i:s') . "] " . $input_raw . PHP_EOL, FILE_APPEND);

    $input = json_decode($input_raw, true);

    // ✅ Handle button callback
    if (isset($input['callback_query'])) {
        $chat_id     = $input['callback_query']['message']['chat']['id'];
        $data        = $input['callback_query']['data'];
        $callback_id = $input['callback_query']['id'];
        $user        = $input['callback_query']['from']['first_name'] ?? 'User';

        $this->respond_callback($callback_id, "Waiting for your input...");

        if (preg_match('/^reply_summary_(\d+)/', $data, $matches)) {
            $summary_id = $matches[1];

            // Save to pending table
            $this->db->insert('telegram_pending_replies', [
                'chat_id'    => $chat_id,
                'summary_id' => $summary_id
            ]);

            $this->sendTelegramMessage($chat_id, "waiting for your reply....");
        }

        return;
    }

    // ✅ Handle normal messages
    if (isset($input['message']['text'])) {
        $chat_id    = $input['message']['chat']['id'];
        $message    = $input['message']['text'];
        $user_name  = $input['message']['from']['first_name'] ?? 'User';

        // Check if this user has a pending reply
        $pending = $this->db
            ->where('chat_id', $chat_id)
            ->order_by('id', 'DESC')
            ->get('telegram_pending_replies')
            ->row();

        if ($pending) {
            $summary_id = $pending->summary_id;

			$first_name = $input['message']['from']['first_name'] ?? '';
			$last_name  = $input['message']['from']['last_name'] ?? '';
			$username   = $input['message']['from']['username'] ?? '';
			$full_name  = trim($first_name . ' ' . $last_name);

			$sender = $username ? "{$username}" : $full_name;

			$this->db->insert('telegram_logs', [
				'module_id'  => $summary_id,
				'message'     => $message,
				'sender_name' => $sender,
				'module_name' => 'Daily Work Summary'
			]);


            // Delete from pending table
            $this->db->delete('telegram_pending_replies', ['id' => $pending->id]);

           $this->sendTelegramMessage($chat_id, "✅ Thank you *{$sender}*! Your reply has been saved for summary #{$summary_id}.");

        } else {
            $this->sendTelegramMessage($chat_id, "⚠️ No active summary to reply to. Please click a '💬 Reply' button first.");
        }

        return;
    }
}

private function respond_callback($callback_id, $text = '')
{
    $url = "https://api.telegram.org/bot{$this->bot_token}/answerCallbackQuery";
    file_get_contents($url . '?' . http_build_query([
        'callback_query_id' => $callback_id,
        'text'              => $text
    ]));
}

private function sendTelegramMessage($chat_id, $message)
{
    $url = "https://api.telegram.org/bot{$this->bot_token}/sendMessage";
    file_get_contents($url . '?' . http_build_query([
        'chat_id'    => $chat_id,
        'text'       => $message,
        'parse_mode' => 'Markdown'
    ]));
}


public function probation_reminder()
	{
		$today = date('Y-m-d');

		// Get staff with intern or probation status
		$this->db->select('staff.*, staff_department.name as department_name');
		$this->db->from('staff');
		$this->db->join('staff_department', 'staff_department.id = staff.department', 'left');
		$this->db->where_in('employee_type', ['intern', 'probation']);
		$staff_list = $this->db->get()->result();

		foreach ($staff_list as $staff) {
			$joining_date = new DateTime($staff->joining_date);
			$today_date = new DateTime($today);
			$days_passed = $joining_date->diff($today_date)->days;

			// Check if today matches any reminder day
			$reminder_days = [80, 85, 90, 350, 355, 360];
			$should_send = false;
			$reminder_day = 0;

			foreach ($reminder_days as $day) {
				if ($days_passed == $day) {
					// Check if reminder already sent for this day
					$existing = $this->db->get_where('probation_reminders', [
						'staff_id' => $staff->id,
						'reminder_day' => $day,
						'sent_date' => $today
					])->row();

					if (!$existing) {
						$should_send = true;
						$reminder_day = $day;
						$period_name = ($day <= 90) ? 'Internship' : 'Probation';
						$total_days = ($day <= 90) ? 90 : 365;
						$remaining_days = $total_days - $days_passed;
					}
					break;
				}
			}

			if ($should_send) {
				// Get department manager
				$manager = $this->db->select('staff.email, staff.name')
					->from('staff')
					->join('login_credential', 'login_credential.user_id = staff.id')
					->where('login_credential.role', 8)
					->where('staff.department', $staff->department)
					->where('login_credential.active', 1)
					->get()->row();

				// Get HR (role 5)
				$hr = $this->db->select('staff.email, staff.name')
					->from('staff')
					->join('login_credential', 'login_credential.user_id = staff.id')
					->where('login_credential.role', 5)
					->where('login_credential.active', 1)
					->get()->row();

				// Get COO (role 3)
				$coo = $this->db->select('staff.email, staff.name')
					->from('staff')
					->join('login_credential', 'login_credential.user_id = staff.id')
					->where('login_credential.role', 3)
					->where('login_credential.active', 1)
					->get()->row();

				$config = $this->email_model->get_email_config();

				 // ✅ Prepare email content
            $subject = "{$period_name} Period Ending Soon - {$staff->name}";
            $body = "
            <html>
              <body style='font-family:Arial,sans-serif; background:#f4f4f4; padding:20px;'>
                <table style='max-width:600px; margin:auto; background:#ffffff; border-radius:8px; padding:30px; box-shadow:0 0 10px rgba(0,0,0,0.05);'>
                  <tr><td style='text-align:center;'>
                    <h2 style='color:#2c3e50;'>{$period_name} Period Ending Soon</h2>
                  </td></tr>
                  <tr><td style='font-size:15px; color:#34495e;'>
                    <p>Dear Team,</p>
                    <p>This is to inform you that the {$period_name} period for the following employee is ending soon:</p>
                    <ul>
                      <li><strong>Name:</strong> {$staff->name}</li>
                      <li><strong>Employee ID:</strong> {$staff->staff_id}</li>
                      <li><strong>Department:</strong> {$staff->department_name}</li>
                      <li><strong>Joining Date:</strong> {$staff->joining_date}</li>
                      <li><strong>Days Completed:</strong> {$days_passed} days</li>
                      <li><strong>Days Remaining:</strong> {$remaining_days} days</li>
                    </ul>
                    <p>Please take necessary action for evaluation and next steps.</p>
                    <p style='margin-top:20px;'>Best regards,<br><strong>EMP</strong></p>
                  </td></tr>
                </table>
              </body>
            </html>";

				$cc_emails = [];
				if ($hr && !empty($hr->email)) $cc_emails[] = $hr->email;
				if ($coo && !empty($coo->email)) $cc_emails[] = $coo->email;

				if ($manager && !empty($manager->email)) {
					$email_data = [
						'smtp_host' => $config['smtp_host'],
						'smtp_auth' => true,
						'smtp_user' => $config['smtp_user'],
						'smtp_pass' => $config['smtp_pass'],
						'smtp_secure' => $config['smtp_encryption'],
						'smtp_port' => $config['smtp_port'],
						'from_email' => $config['email'],
						'from_name' => 'EMP Admin',
						'to_email' => $manager->email,
						'to_name' => $manager->name,
						'subject' => $subject,
						'body' => $body,
						'cc' => implode(',', $cc_emails)
					];

					$this->email_model->send_email_yandex($email_data);

					// Log the sent reminder
					$message = "Day {$reminder_day}: {$period_name} period for {$staff->name} - {$remaining_days} days remaining";
					$this->db->insert('probation_reminders', [
						'staff_id' => $staff->id,
						'reminder_day' => $reminder_day,
						'message' => $message,
						'sent_date' => $today,
						'created_at' => date('Y-m-d H:i:s')
					]);
				}
			}
		}

		echo json_encode(['status' => 'success', 'message' => 'Probation reminder check completed']);
	}



public function get_staff_info()
{
    // Set JSON response header
    $this->output->set_content_type('application/json');

    // Get staff IDs from query parameter
    $staff_ids = $this->input->get('staff_ids');

    if (empty($staff_ids)) {
        $this->output->set_output(json_encode([
            'status' => 'error',
            'message' => 'staff_ids parameter is required'
        ]));
        return;
    }

    // Convert comma-separated string to array
    $staff_ids_array = explode(',', $staff_ids);
    $staff_ids_array = array_map('trim', $staff_ids_array);

    // Validate staff IDs
    foreach ($staff_ids_array as $id) {
        if (!is_numeric($id)) {
            $this->output->set_output(json_encode([
                'status' => 'error',
                'message' => 'Invalid staff ID: ' . $id
            ]));
            return;
        }
    }

    try {
        // Get staff information with current salary
        $this->db->select('s.name, s.staff_id as employee_id, sd.name as designation')
                 ->from('staff s')
                 ->join('staff_designation sd', 'sd.id = s.designation', 'left')
                 ->where_in('s.id', $staff_ids_array);

        $staff_data = $this->db->get()->result_array();

        if (empty($staff_data)) {
            $this->output->set_output(json_encode([
                'status' => 'error',
                'message' => 'No staff found for the provided IDs'
            ]));
            return;
        }

        $this->output->set_output(json_encode([
            'status' => 'success',
            'data' => $staff_data
        ]));

    } catch (Exception $e) {
        $this->output->set_output(json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]));
    }
}


	public function update_overdue_milestones()
	{
		$today = date('Y-m-d');

		$this->db->where('due_date <', $today);
		$this->db->where('status !=', 'done');
		$result = $this->db->update('tracker_milestones', ['status' => 'hold']);

		$affected_rows = $this->db->affected_rows();

		echo json_encode([
			'status' => 'success',
			'message' => "Updated {$affected_rows} overdue milestones to hold status"
		]);
	}

	public function log_email()
	{
		$this->output->set_content_type('application/json');

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		if (empty($data)) {
			$this->output->set_status_header(400)->set_output(json_encode([
				'status' => 'error',
				'message' => 'Invalid JSON data'
			]));
			return;
		}

		// Validate required fields
		$required_fields = ['from_email', 'to_email', 'subject', 'body', 'trigger_source', 'trigger_module'];
		foreach ($required_fields as $field) {
			if (empty($data[$field])) {
				$this->output->set_status_header(400)->set_output(json_encode([
					'status' => 'error',
					'message' => "Missing required field: {$field}"
				]));
				return;
			}
		}

		try {
			$log_data = [
				'from_email' => $data['from_email'],
				'to_email' => $data['to_email'],
				'cc_email' => isset($data['cc_email']) ? $data['cc_email'] : null,
				'bcc_email' => isset($data['bcc_email']) ? $data['bcc_email'] : null,
				'subject' => $data['subject'],
				'body' => $data['body'],
				'trigger_source' => $data['trigger_source'],
				'trigger_module' => $data['trigger_module'],
				'status' => $data['status'],
				'error_message' => isset($data['error_message']) ? $data['error_message'] : null,
				'sent_at' => isset($data['sent_at']) ? $data['sent_at'] : date('Y-m-d H:i:s')
			];

			$this->db->insert('email_logs', $log_data);
			$log_id = $this->db->insert_id();

			if ($log_id) {
				$this->output->set_output(json_encode([
					'status' => 'success',
					'message' => 'Email log saved successfully',
					'log_id' => $log_id
				]));
			} else {
				$this->output->set_status_header(500)->set_output(json_encode([
					'status' => 'error',
					'message' => 'Failed to save email log'
				]));
			}

		} catch (Exception $e) {
			$this->output->set_status_header(500)->set_output(json_encode([
				'status' => 'error',
				'message' => 'Database error: ' . $e->getMessage()
			]));
		}
	}

	/**
	 * Add cashbook entry via API (matches Cashbook::add_entry)
	 */
	/**
	 * Get cashbook accounts
	 */
	public function cashbook_accounts()
	{
		try {
			$accounts = $this->db->select('id, name')
								->from('cashbook_accounts')
								->get()
								->result_array();

			$this->output_json($accounts);

		} catch (Exception $e) {
			log_message('error', 'Cashbook accounts API error: ' . $e->getMessage());
			$this->output_json(['success' => false, 'message' => 'Internal server error']);
		}
	}

	/**
	 * Add cashbook entry via API (matches Cashbook::add_entry)
	 */
	public function add_cashbook_entry()
	{
		try {
			$input = json_decode(file_get_contents('php://input'), true);

			if (!$input) {
				$this->output_json(['success' => false, 'message' => 'Invalid JSON input']);
				return;
			}

			// Validate required fields
			$required_fields = ['entry_type', 'amount', 'description', 'account_type'];
			foreach ($required_fields as $field) {
				if (!isset($input[$field]) || $input[$field] === '') {
					$this->output_json(['success' => false, 'message' => "Missing required field: {$field}"]);
					return;
				}
			}

			// Validate entry_type
			if (!in_array($input['entry_type'], ['in', 'out'])) {
				$this->output_json(['success' => false, 'message' => 'entry_type must be "in" or "out"']);
				return;
			}

			// Validate amount
			if (!is_numeric($input['amount']) || $input['amount'] <= 0) {
				$this->output_json(['success' => false, 'message' => 'amount must be a positive number']);
				return;
			}

			// Validate branch_id exists
			$branch_id = $input['branch_id'] ?? 1;
			$branch_exists = $this->db->get_where('branch', ['id' => $branch_id])->row();
			if (!$branch_exists) {
				// Get first available branch or use null
				$first_branch = $this->db->select('id')->get('branch')->row();
				$branch_id = $first_branch ? $first_branch->id : null;
			}

			// Handle account_type_id if provided
			$account_id = null;
			$account_name = $input['account_type'];

			if (isset($input['account_type_id']) && !empty($input['account_type_id'])) {
				// Use provided account_type_id
				$account = $this->db->get_where('cashbook_accounts', ['id' => $input['account_type_id']])->row();
				if ($account) {
					$account_id = $account->id;
					$account_name = $account->name;
				} else {
					$this->output_json(['success' => false, 'message' => 'Invalid account_type_id']);
					return;
				}
			} else {
				// Handle account_type as before
				if (is_numeric($input['account_type'])) {
					// Account ID provided
					$account = $this->db->get_where('cashbook_accounts', ['id' => $input['account_type']])->row();
					if ($account) {
						$account_id = $account->id;
						$account_name = $account->name;
					} else {
						$this->output_json(['success' => false, 'message' => 'Invalid account_type ID']);
						return;
					}
				} else {
					// Account name provided
					$account = $this->db->get_where('cashbook_accounts', ['name' => $input['account_type']])->row();
					if ($account) {
						$account_id = $account->id;
						$account_name = $account->name;
					} else {
						// Create new account with minimal data
						$new_account_data = [
							'name' => $input['account_type']
						];
						$result = $this->db->insert('cashbook_accounts', $new_account_data);
						$account_id = $this->db->insert_id();
						$account_name = $input['account_type'];

						if (!$result || !$account_id) {
							$db_error = $this->db->error();
							$this->output_json([
								'success' => false,
								'message' => 'Failed to create account',
								'debug' => [
									'account_data' => $new_account_data,
									'db_error' => $db_error,
									'result' => $result,
									'account_id' => $account_id
								]
							]);
							return;
						}
					}
				}
			}

			// Prepare entry data (same structure as Cashbook::add_entry)
			$insertData = array(
				'entry_type' => $input['entry_type'],
				'amount' => $input['amount'],
				'description' => $input['description'],
				'invoice_no' => $input['invoice_no'],
				'account_type_id' => $account_id,
				'account_type' => $account_name,
				'reference_type' => $input['reference_type'] ?? 'api',
				'reference_id' => $input['reference_id'] ?? 0,
				'created_by' => $input['created_by'] ?? 1,
				'entry_date' => $input['entry_date'] ?? date('Y-m-d H:i:s'),
				'branch_id' => $branch_id,
			);

			// Skip branch_id if null (no valid branch found)
			if ($branch_id === null) {
				unset($insertData['branch_id']);
			}

			// Insert entry
			$result = $this->db->insert('cashbook_entries', $insertData);
			$entry_id = $this->db->insert_id();

			// Debug information
			$db_error = $this->db->error();
			log_message('debug', 'Cashbook insert result: ' . ($result ? 'true' : 'false'));
			log_message('debug', 'Entry ID: ' . $entry_id);
			log_message('debug', 'DB Error: ' . json_encode($db_error));
			log_message('debug', 'Insert data: ' . json_encode($insertData));

			if ($result && $entry_id > 0) {
				$this->output_json([
					'success' => true,
					'message' => 'Cashbook entry added successfully',
					'entry_id' => $entry_id
				]);
			} else {
				$this->output_json([
					'success' => false,
					'message' => 'Failed to insert cashbook entry',
					'debug' => [
						'result' => $result,
						'entry_id' => $entry_id,
						'db_error' => $db_error,
						'insert_data' => $insertData
					]
				]);
			}

		} catch (Exception $e) {
			log_message('error', 'Cashbook entry API error: ' . $e->getMessage());
			$this->output_json(['success' => false, 'message' => 'Internal server error: ' . $e->getMessage()]);
		}
	}

	public function initialize_rdc_flags()
	{
		$rdcs = $this->db->get('rdc')->result_array();

		foreach ($rdcs as $rdc) {
			if ($rdc['is_random_assignment'] == 1 && !empty($rdc['user_pool'])) {
				$user_pool = json_decode($rdc['user_pool'], true);
				$this->reset_assignment_flags($rdc['id'], $user_pool);
			}
		}

		echo json_encode(['status' => 'success', 'message' => 'RDC flags initialized']);
	}

	public function milestone_reminder_status()
	{
		$today = date('Y-m-d');
		$tomorrow = date('Y-m-d', strtotime('+1 day'));
		$in_2_days = date('Y-m-d', strtotime('+2 days'));
		$in_3_days = date('Y-m-d', strtotime('+3 days'));

		// Get pending milestones
		$pending_milestones = $this->db->select('tm.id, tm.title, tm.due_date, tm.status, td.title as department_name, s.name as assigned_name')
			->from('tracker_milestones tm')
			->join('tracker_departments td', 'tm.department_id = td.id')
			->join('staff s', 'tm.assigned_to = s.id', 'left')
			->where('tm.due_date >=', $today)
			->where('tm.due_date <=', $in_3_days)
			->where_not_in('tm.status', ['completed', 'done', 'solved', 'canceled'])
			->order_by('tm.due_date', 'ASC')
			->get()->result_array();

		// Get sent reminders today
		$sent_today = $this->db->select('COUNT(*) as count')
			->where('sent_date', $today)
			->get('milestone_reminders_sent')->row()->count;

		// Get reminder statistics
		$reminder_stats = $this->db->select('reminder_type, COUNT(*) as count')
			->where('sent_date', $today)
			->group_by('reminder_type')
			->get('milestone_reminders_sent')->result_array();

		echo json_encode([
			'status' => 'success',
			'current_time' => date('Y-m-d H:i:s'),
			'pending_milestones' => count($pending_milestones),
			'milestones_detail' => $pending_milestones,
			'reminders_sent_today' => $sent_today,
			'reminder_breakdown' => $reminder_stats,
			'next_execution_window' => '10:00-10:30 AM daily'
		]);
	}

	public function milestone_due_reminders_test()
	{
		// Test version without time restriction
		$today = date('Y-m-d');
		$reminder_types = [
			'3_days' => date('Y-m-d', strtotime('+3 days')),
			'2_days' => date('Y-m-d', strtotime('+2 days')),
			'1_day' => date('Y-m-d', strtotime('+1 day')),
			'today' => $today
		];

		$total_sent = 0;

		foreach ($reminder_types as $reminder_type => $target_date) {
			$milestones = $this->get_milestones_for_reminder($target_date, $reminder_type, $today);

			foreach ($milestones as $milestone) {
				$this->send_milestone_reminder_notifications($milestone, $reminder_type);
				$this->mark_milestone_reminder_sent($milestone->id, $reminder_type, $today);
				$total_sent++;
			}
		}

		echo json_encode([
			'status' => 'success',
			'message' => "Milestone reminders processed. Total sent: {$total_sent}",
			'reminder_types' => $reminder_types
		]);
	}

	public function milestone_due_reminders()
	{
		// Check if it's between 10:00 AM and 10:30 AM
		$current_hour = (int)date('H');
		$current_minute = (int)date('i');

		if ($current_hour != 10 || $current_minute > 30) {
			echo json_encode(['status' => 'skipped', 'message' => 'Not in allowed time window (10:00-10:30 AM)']);
			return;
		}

		$today = date('Y-m-d');
		$reminder_types = [
			'3_days' => date('Y-m-d', strtotime('+3 days')),
			'2_days' => date('Y-m-d', strtotime('+2 days')),
			'1_day' => date('Y-m-d', strtotime('+1 day')),
			'today' => $today
		];

		$total_sent = 0;

		foreach ($reminder_types as $reminder_type => $target_date) {
			$milestones = $this->get_milestones_for_reminder($target_date, $reminder_type, $today);

			foreach ($milestones as $milestone) {
				$this->send_milestone_reminder_notifications($milestone, $reminder_type);
				$this->mark_milestone_reminder_sent($milestone->id, $reminder_type, $today);
				$total_sent++;
			}
		}

		echo json_encode([
			'status' => 'success',
			'message' => "Milestone reminders processed. Total sent: {$total_sent}"
		]);
	}

	private function get_milestones_for_reminder($target_date, $reminder_type, $sent_date)
	{
		$this->db->select('tm.id, tm.title, tm.due_date, tm.priority, tm.assigned_to, td.title as department_name, td.identifier as department_identifier, s.name as assigned_name, s.email as assigned_email, s.telegram_id');
		$this->db->from('tracker_milestones tm');
		$this->db->join('tracker_departments td', 'tm.department_id = td.id');
		$this->db->join('staff s', 'tm.assigned_to = s.id', 'left');
		$this->db->where('DATE(tm.due_date)', $target_date);
		$this->db->where_not_in('tm.status', ['completed', 'done', 'solved', 'canceled']);

		// Exclude already sent reminders
		$this->db->where("NOT EXISTS (
			SELECT 1 FROM milestone_reminders_sent mrs
			WHERE mrs.milestone_id = tm.id
			AND mrs.reminder_type = '$reminder_type'
			AND mrs.sent_date = '$sent_date'
		)");

		return $this->db->get()->result();
	}

	private function send_milestone_reminder_notifications($milestone, $reminder_type)
	{
		// Send email notification
		$this->send_milestone_email_reminder($milestone, $reminder_type);

		// Send Telegram notification
		$this->send_milestone_telegram_reminder($milestone, $reminder_type);

		// Send FCM notification
		if (!empty($milestone->assigned_to)) {
			$fcm_tokens = $this->get_staff_fcm_tokens([$milestone->assigned_to]);
			if (!empty($fcm_tokens)) {
				$days_text = $this->get_days_text($reminder_type);
				$this->send_fcm_notification(
					'Milestone Due Reminder',
					"Your milestone '{$milestone->title}' is due {$days_text}",
					'',
					$fcm_tokens,
					['type' => 'milestone_reminder', 'milestone_id' => $milestone->id]
				);
			}
		}
	}

	private function send_milestone_email_reminder($milestone, $reminder_type)
	{
		if (empty($milestone->assigned_email)) return;

		$config = $this->email_model->get_email_config();
		$days_text = $this->get_days_text($reminder_type);

		$subject = "Milestone Due Reminder - {$days_text}";
		$body = $this->build_milestone_email_body($milestone, $days_text);

		// Get department manager for CC
		$manager = $this->get_department_manager($milestone->department_id);

		$email_data = [
			'smtp_host' => $config['smtp_host'],
			'smtp_auth' => true,
			'smtp_user' => $config['smtp_user'],
			'smtp_pass' => $config['smtp_pass'],
			'smtp_secure' => $config['smtp_encryption'],
			'smtp_port' => $config['smtp_port'],
			'from_email' => $config['email'],
			'from_name' => 'EMP Admin',
			'to_email' => $milestone->assigned_email,
			'to_name' => $milestone->assigned_name,
			'subject' => $subject,
			'body' => $body,
			'cc' => $manager ? $manager['email'] : null
		];

		$this->email_model->send_email_yandex($email_data);
	}

	private function build_milestone_email_body($milestone, $days_text)
	{
		$due_date_formatted = date('d M Y', strtotime($milestone->due_date));

		return "<html>
		<body style='font-family:Arial,sans-serif; background:#f4f4f4; padding:20px;'>
			<table style='max-width:600px; margin:auto; background:#ffffff; border-radius:8px; padding:30px; box-shadow:0 0 10px rgba(0,0,0,0.05);'>
				<tr><td style='text-align:center;'>
					<h2 style='color:#2c3e50;'>Milestone Due Reminder</h2>
				</td></tr>
				<tr><td style='font-size:15px; color:#34495e;'>
					<p>Dear <strong>{$milestone->assigned_name}</strong>,</p>
					<p>This is a reminder that your milestone is due <strong>{$days_text}</strong>.</p>

					<div style='background:#f8f9fa; padding:15px; border-radius:5px; margin:20px 0;'>
						<p><strong>Milestone:</strong> {$milestone->title}</p>
						<p><strong>Department:</strong> {$milestone->department_name}</p>
						<p><strong>Due Date:</strong> {$due_date_formatted}</p>
						<p><strong>Priority:</strong> {$milestone->priority}</p>
					</div>

					<p>Please ensure timely completion of this milestone.</p>
					<p style='margin-top:20px;'>Best regards,<br><strong>EMP Admin</strong></p>
				</td></tr>
			</table>
		</body>
		</html>";
	}

	private function send_milestone_telegram_reminder($milestone, $reminder_type)
	{
		// Use personal bot token (same as RDC system)
		$bot_token = $telegram_bot;
				
		$days_text = $this->get_days_text($reminder_type);

		$due_date_formatted = date('d M Y', strtotime($milestone->due_date));
		$today_display = date('d M Y');

		$tg_message = "⏰ *Milestone Due Reminder*\n\n" .
			"📅 *Date:* {$today_display}\n" .
			"👤 *Assigned:* {$milestone->assigned_name}\n" .
			"🏢 *Department:* {$milestone->department_name}\n\n" .
			"📌 *Milestone:* {$milestone->title}\n" .
			"⏳ *Due:* {$days_text}\n" .
			"📅 *Due Date:* {$due_date_formatted}\n" .
			"🎯 *Priority:* {$milestone->priority}\n\n" .
			"🔗 [View Milestones](" . base_url('tracker/milestones/' . $milestone->department_identifier) . ")";

		// Send only to individual staff member (personal notification)
		if (!empty($milestone->telegram_id)) {
			$this->send_telegram_message($bot_token, $milestone->telegram_id, $tg_message);
			log_message('debug', "Personal Telegram sent to {$milestone->assigned_name} for milestone: {$milestone->title}");
		} else {
			log_message('debug', "No telegram_id for staff: {$milestone->assigned_name}, milestone: {$milestone->title}");
		}
	}

	private function send_telegram_message($bot_token, $chat_id, $message)
	{
		$payload = [
			'chat_id' => $chat_id,
			'text' => $message,
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
	}

	private function get_days_text($reminder_type)
	{
		$texts = [
			'3_days' => 'in 3 days',
			'2_days' => 'in 2 days',
			'1_day' => 'tomorrow',
			'today' => 'today'
		];
		return $texts[$reminder_type];
	}

	private function mark_milestone_reminder_sent($milestone_id, $reminder_type, $sent_date)
	{
		$this->db->insert('milestone_reminders_sent', [
			'milestone_id' => $milestone_id,
			'reminder_type' => $reminder_type,
			'sent_date' => $sent_date
		]);
	}

	private function get_department_manager($department_id)
	{
		// Get department info first
		$department = $this->db->select('assigned_issuer')->where('id', $department_id)->get('tracker_departments')->row();

		if ($department && $department->assigned_issuer) {
			return $this->db->select('email, name')
				->where('id', $department->assigned_issuer)
				->get('staff')
				->row_array();
		}

		return null;
	}

	public function test_rdc_personal_notifications()
	{
		// Test the personal notification system
		$test_rdc = [
			'id' => 999,
			'title' => 'Test RDC Task - Personal Notifications',
			'description' => 'Testing personal notification system'
		];

		$test_user_id = 42; // Replace with actual staff ID for testing
		$test_due_time = date('Y-m-d H:i:s', strtotime('+2 hours'));

		// Test individual email
		$this->send_individual_task_email($test_user_id, $test_rdc, $test_due_time);

		// Test personal Telegram (via send_task_notifications)
		$this->send_task_notifications($test_rdc, $test_user_id, $test_due_time);

		echo json_encode([
			'status' => 'success',
			'message' => 'Personal RDC notifications tested',
			'test_user_id' => $test_user_id,
			'test_due_time' => $test_due_time
		]);
	}

	public function check_late_completed_tasks()
	{
		$today = date('Y-m-d');
		$processed = 0;

		// Get tasks completed today that are late and don't have todos yet
		// Check planner_events updated_at for actual completion time
		$late_tasks = $this->db->select('ti.id, ti.unique_id, ti.task_title, ti.assigned_to, ti.estimated_end_time')
			->from('tracker_issues ti')
			->join('planner_events pe', 'pe.issue_id = ti.id')
			->where('ti.task_status', 'completed')
			->where('ti.is_late', 1)
			->where('DATE(pe.updated_at)', $today)
			->where('ti.unique_id NOT IN (SELECT task_unique_id FROM warnings WHERE task_unique_id IS NOT NULL)', null, false)
			->group_by('ti.id')
			->get()->result();

		foreach ($late_tasks as $task) {
			$estimated_end_date = date('Y-m-d', strtotime($task->estimated_end_time));

			// Double check if task is actually late
			if ($today > $estimated_end_date) {
				$this->create_late_task_todo_api($task);
				$processed++;
			}
		}

		echo json_encode([
			'status' => 'success',
			'message' => 'Late task check completed',
			'processed' => $processed,
			'timestamp' => date('Y-m-d H:i:s')
		]);
	}

	// Create todo for late task (API version)
	private function create_late_task_todo_api($task)
	{
		// Check if warning already exists for this task
		$existing_warning = $this->db->select('id')
			->from('warnings')
			->where('task_unique_id', $task->unique_id)
			->where('category', 'Late Task Submission')
			->get()->row();

		if ($existing_warning) {
			return; // Skip if warning already exists
		}

		// Get assigned staff details with role
		$staff_info = $this->db
			->select('staff.name, staff.telegram_id, staff.department, login_credential.role')
			->join('login_credential', 'login_credential.user_id = staff.id')
			->where('staff.id', $task->assigned_to)
			->get('staff')
			->row();

		if (!$staff_info) {
			return; // Skip if staff not found
		}

		// Skip excluded roles and staff IDs
		$excluded_roles = [1, 2, 3, 9, 10, 11, 12];
		$excluded_staff_ids = [49, 22, 23, 28, 25, 31];

		if (in_array($staff_info->role, $excluded_roles) || in_array($task->assigned_to, $excluded_staff_ids)) {
			return; // Skip excluded roles and staff
		}

		$department_id = $staff_info->department;
		$employee_role = $staff_info->role;

		// Insert into warnings table
		$warning_data = [
			'user_id' => $task->assigned_to,
			'role_id' => 0,
			'branch_id' => 0,
			'clearance_time' => 24,
			'reason' => "Late submission explanation required for task: {$task->task_title} ({$task->unique_id}).",
			'reference' => "Late Submission - {$task->unique_id}",
			'category' => 'Late Task Submission',
			'task_unique_id' => $task->unique_id,
			'effect' => 'Business',
			'issued_by' => 1,
			'status' => 1,
			'issue_date' => date('Y-m-d H:i:s')
		];

		// Roles that must be reviewed by COO
		$coo_review_roles = [3, 5, 8];

		// Fetch COO once
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
		if ($employee_role == 4 && !empty($department_id)) {

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
				$warning_data['manager_id'] = $manager->id;
				$warning_data['manager_review'] = 1;
			} elseif ($coo) {
				$warning_data['manager_id'] = $coo->id;
				$warning_data['manager_review'] = 1;
			}
		}

		/**
		 * CASE 2: Roles 3, 5, 8
		 * → Always COO
		 */
		if (in_array($employee_role, $coo_review_roles) && $coo) {
			$warning_data['manager_id'] = $coo->id;
			$warning_data['manager_review'] = 1;
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
			$warning_data['advisor_id'] = $advisor->id;
			$warning_data['advisor_review'] = 1;
		}


		$this->db->insert('warnings', $warning_data);

		// Create system notification
		$this->db->insert('notifications', [
			'user_id' => $task->assigned_to,
			'type' => 'late_task_todo',
			'title' => 'Late Task Completion Todo',
			'message' => 'You have a new todo for late completion of task "' . $task->task_title . '". Please provide explanation within 24 hours.',
			'url' => base_url('todo'),
			'is_read' => 0,
			'created_at' => date('Y-m-d H:i:s')
		]);

		// Send personal Telegram message
		if ($staff_info && !empty($staff_info->telegram_id)) {
			$this->send_telegram_message_api($staff_info->telegram_id, $staff_info->name, $task->task_title, $task->unique_id);
		}
	}

	// Send Telegram message (API version)
	private function send_telegram_message_api($telegram_id, $staff_name, $task_title, $task_id)
	{
		$bot_token = $telegram_bot;
		$today = date('d M Y');

		$message = "⚠️ *Late Task Completion Todo*\n\n" .
			"📅 *Date:* {$today}\n" .
			"👤 *Name:* {$staff_name}\n\n" .
			"📌 *Task:* {$task_title}\n" .
			"🆔 *Task ID:* {$task_id}\n" .
			"⏰ *Deadline:* 24 hours\n\n" .
			"Please provide explanation for late completion via EMP portal.\n\n" .
			"🔗 [Open Todo](" . base_url('todo') . ")";

		$payload = [
			'chat_id' => $telegram_id,
			'text' => $message,
			'parse_mode' => 'Markdown',
			'disable_web_page_preview' => true
		];

		$url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		curl_exec($ch);
		curl_close($ch);
	}

	public function create_monthly_revenue_share()
	{
		// Get previous month's date range
		$previous_month = date('Y-m', strtotime('-1 month'));
		$month_name = date('F Y', strtotime($previous_month . '-01'));

		// Delete any existing fund requisitions for this month to prevent duplicates
		$this->db->where('staff_id', 10)
			->where('category_id', 18)
			->where('milestone', 46)
			->like('reason', 'Revenue share of ' . $month_name)
			->delete('fund_requisition');

		// Get sales revenue for previous month (same logic as monthly_sales_revenue)
		$this->db->select('SUM(amount) as total_revenue');
		$this->db->from('cashbook_entries');
		$this->db->where('entry_type', 'in');
		$this->db->where('reference_type', 'sales');
		$this->db->where("DATE_FORMAT(entry_date, '%Y-%m') =", $previous_month);
		// Exclude specific clients as per monthly_sales_revenue function
		$this->db->where('description NOT LIKE', '%Sunnyat Ali%');
		$this->db->where('description NOT LIKE', '%I3 Technologies%');
		$this->db->where('description NOT LIKE', '%Jerin Apu%');

		$result = $this->db->get()->row();
		$total_revenue = $result ? $result->total_revenue : 0;

		if ($total_revenue <= 0) {
			echo json_encode([
				'status' => 'skipped',
				'message' => 'No revenue found for ' . $month_name
			]);
			return;
		}

		// Calculate 10% revenue share
		$revenue_share = $total_revenue * 0.10;

		// Create fund requisition
		$fund_data = [
			'unique_id' => generate_unique_id('fund_requisition'),
			'staff_id' => 10, // Applicant ID
			'amount' => $revenue_share,
			'category_id' => 18, // Category ID
			'milestone' => 46, // Category ID
			'reason' => 'Revenue share of ' . $month_name . ' (10% of ' . number_format($total_revenue, 2) . ')',
			'request_date' => date('Y-m-d H:i:s'),
			'status' => 1, // Pending status
			'payment_status' => 1, // Pending payment
			'billing_type' => 'One Time',
			'branch_id' => 2,
			'orig_file_name' => '',
			'enc_file_name' => '',
			'adjust_amount' => 0
		];

		try {
			$this->db->insert('fund_requisition', $fund_data);
			$fund_id = $this->db->insert_id();

			if ($fund_id) {
				echo json_encode([
					'status' => 'success',
					'message' => 'Monthly revenue share fund requisition created successfully',
					'fund_id' => $fund_id,
					'month' => $month_name,
					'total_revenue' => number_format($total_revenue, 2),
					'revenue_share' => number_format($revenue_share, 2)
				]);
			} else {
				echo json_encode([
					'status' => 'error',
					'message' => 'Failed to create fund requisition'
				]);
			}

		} catch (Exception $e) {
			log_message('error', 'Revenue share fund requisition error: ' . $e->getMessage());
			echo json_encode([
				'status' => 'error',
				'message' => 'Database error: ' . $e->getMessage()
			]);
		}
	}

	public function work_summary_followup()
	{
		$today = date('Y-m-d');
		$current_day = date('w');
		$test_mode = $this->input->get('test') === '1';

		if (!$test_mode && ($current_day == 5 || $current_day == 6)) {
			echo "Weekend - No TODOs created\n";
			return;
		}

		if (!$test_mode && $this->is_holiday($today)) {
			echo "Holiday - No TODOs created\n";
			return;
		}

		$employees = $this->db->select('staff.id, staff.name, staff.email, staff.telegram_id')
			->from('staff')
			->join('login_credential', 'login_credential.user_id = staff.id')
			->where('login_credential.active', 1)
			->where_not_in('login_credential.role', [1, 2, 3, 9, 10, 11, 12])
			->where_not_in('staff.id', [49, 22, 23, 28, 25, 31])
			->get()->result();

		$created_count = 0;
		$skipped_count = 0;

		foreach ($employees as $employee) {
			$submitted = $this->db->where([
				'user_id' => $employee->id,
				'summary_date' => $today
			])->count_all_results('daily_work_summaries');

			if ($submitted > 0) {
				$skipped_count++;
				continue;
			}

			if ($this->is_on_leave($employee->id, $today)) {
				$skipped_count++;
				continue;
			}

			if ($this->todo_already_exists($employee->id, $today)) {
				$skipped_count++;
				continue;
			}

			$this->create_work_summary_todo($employee, $today);
			$created_count++;
		}

		echo "Work Summary Follow-up: {$created_count} TODOs created, {$skipped_count} skipped\n";
	}

	private function todo_already_exists($user_id, $date)
	{
		return $this->db->where('user_id', $user_id)
			->where('category', 'Work Summary')
			->where('DATE(issue_date)', $date)
			->count_all_results('warnings') > 0;
	}

	private function create_work_summary_todo($employee, $date)
	{
		// Get employee role and department
		$staff_info = $this->db
			->select('staff.department, login_credential.role')
			->join('login_credential', 'login_credential.user_id = staff.id')
			->where('staff.id', $employee->id)
			->get('staff')
			->row();

		$department_id = $staff_info ? $staff_info->department : null;
		$employee_role = $staff_info ? $staff_info->role : null;

		$reference = 'EMP/HR & ADMIN/Work Summary/TO-DO/' . date('Ymd') . '-Daily Work Summary';

		$todo_data = [
			'reference' => $reference,
			'category' => 'Work Summary',
			'effect' => 'Productivity',
			'user_id' => $employee->id,
			'reason' => 'Daily work summary not submitted for ' . date('jS F, Y', strtotime($date)) . '. Please provide a explanation for why you did not submit your work summary.',
			'clearance_time' => 24,
			'status' => 1,
			'issued_by' => 1,
			'issue_date' => date('Y-m-d H:i:s')
		];

		// Roles that should be reviewed directly by COO
		$coo_review_roles = [3, 5, 8];

		// Fetch COO once (reuse everywhere)
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
		 * → Department manager (role 8)
		 * → Fallback to COO
		 */
		if ($employee_role == 4 && $department_id) {

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
				$todo_data['manager_id'] = $manager->id;
				$todo_data['manager_review'] = 1;
			} elseif ($coo) {
				$todo_data['manager_id'] = $coo->id;
				$todo_data['manager_review'] = 1;
			}
		}

		/**
		 * CASE 2: Roles 3, 5, 8
		 * → Always COO
		 */
		if (in_array($employee_role, $coo_review_roles) && $coo) {
			$todo_data['manager_id'] = $coo->id;
			$todo_data['manager_review'] = 1;
		}

		/**
		 * Advisor assignment (unchanged)
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
			$todo_data['advisor_id'] = $advisor->id;
			$todo_data['advisor_review'] = 1;
		}


		$this->db->insert('warnings', $todo_data);
		$todo_id = $this->db->insert_id();

		$this->send_todo_notifications($employee, $todo_id, $date);
	}

	private function send_todo_notifications($employee, $todo_id, $date)
	{
		// Database notification
		$this->db->insert('notifications', [
			'user_id' => $employee->id,
			'type' => 'work_summary_todo',
			'title' => 'Work Summary TODO Created',
			'message' => 'A TODO has been created for your missing work summary on ' . date('jS F, Y', strtotime($date)) . '. Please submit it within 24 hours.',
			'url' => base_url('todo'),
			'is_read' => 0,
			'created_at' => date('Y-m-d H:i:s')
		]);

		// Telegram notification
		if (!empty($employee->telegram_id)) {
			$bot_token = $telegram_bot;
			$message = "📋 *Work Summary TODO Created*\n\n" .
				"📅 *Date:* " . date('d M Y', strtotime($date)) . "\n" .
				"👤 *Dear {$employee->name}*,\n\n" .
				"⚠️ You haven't submitted your daily work summary.\n" .
				"📝 A TODO has been created. Please complete within 24 hours.\n\n" .
				"🔗 [Complete TODO](" . base_url('todo') . ")";

			$payload = [
				'chat_id' => $employee->telegram_id,
				'text' => $message,
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
		}

		// Email notification
		if (!empty($employee->email)) {
			$config = $this->email_model->get_email_config();
			$mail_body = "<html><body style='font-family:Arial,sans-serif;'>
				<p>Dear <strong>{$employee->name}</strong>,</p>
				<p>A TODO has been automatically created because you haven't submitted your daily work summary for " . date('jS F, Y', strtotime($date)) . ".</p>
				<p><strong>Action Required:</strong> Please submit your work summary within 24 hours.</p>
				<p><a href='" . base_url('todo') . "'>Complete TODO</a></p>
				<br><p>EMP System</p></body></html>";

			$email_data = [
				'smtp_host' => $config['smtp_host'],
				'smtp_auth' => true,
				'smtp_user' => $config['smtp_user'],
				'smtp_pass' => $config['smtp_pass'],
				'smtp_secure' => $config['smtp_encryption'],
				'smtp_port' => $config['smtp_port'],
				'from_email' => $config['email'],
				'from_name' => 'EMP Admin',
				'to_email' => $employee->email,
				'to_name' => $employee->name,
				'subject' => 'Work Summary TODO Created',
				'body' => $mail_body
			];

			$this->email_model->send_email_yandex($email_data);
		}

		// FCM notification
		$fcm_tokens = $this->get_staff_fcm_tokens([$employee->id]);
		if (!empty($fcm_tokens)) {
			$this->send_fcm_notification(
				'Work Summary TODO Created',
				'A TODO has been created for your missing work summary. Please complete within 24 hours.',
				'',
				$fcm_tokens,
				['type' => 'work_summary_todo', 'todo_id' => $todo_id]
			);
		}
	}

	public function work_summary_reminder()
	{
		$today = date('Y-m-d');
		$current_day = date('w'); // 0=Sunday, 6=Saturday
		$test_mode = $this->input->get('test') === '1';

		// Skip weekends (Friday=5, Saturday=6) unless testing
		if (!$test_mode && ($current_day == 5 || $current_day == 6)) {
			echo "Weekend - No reminders sent\n";
			return;
		}

		// Check if today is holiday unless testing
		if (!$test_mode && $this->is_holiday($today)) {
			echo "Holiday - No reminders sent\n";
			return;
		}

		// Get all active employees with Telegram IDs
		$employees = $this->db->select('staff.id, staff.name, staff.telegram_id')
			->from('staff')
			->join('login_credential', 'login_credential.user_id = staff.id')
			->where('login_credential.active', 1)
			->where_not_in('login_credential.role', [1, 2, 3, 9, 10, 11, 12])
			->where_not_in('staff.id', [49, 22, 23, 28, 25, 31])
			->where('staff.telegram_id IS NOT NULL')
			->where('staff.telegram_id !=', '')
			->get()->result();

		$sent_count = 0;
		$skipped_count = 0;

		foreach ($employees as $employee) {
			// Check if already submitted today
			$submitted = $this->db->where([
				'user_id' => $employee->id,
				'summary_date' => $today
			])->count_all_results('daily_work_summaries');

			if ($submitted > 0) {
				$skipped_count++;
				continue;
			}

			// Check if on leave today
			if ($this->is_on_leave($employee->id, $today)) {
				$skipped_count++;
				continue;
			}

			// Send reminder
			$this->send_work_summary_reminder($employee);
			$sent_count++;
		}

		echo "Work Summary Reminders: {$sent_count} sent, {$skipped_count} skipped\n";
	}

	private function is_holiday($date)
	{
		return $this->db->where('type', 'holiday')
			->where('status', 1)
			->where('start_date <=', $date)
			->where('end_date >=', $date)
			->count_all_results('event') > 0;
	}

	private function is_on_leave($staff_id, $date)
	{
		return $this->db->where('user_id', $staff_id)
			->where('status', 2)
			->where('start_date <=', $date)
			->where('end_date >=', $date)
			->count_all_results('leave_application') > 0;
	}

	private function send_work_summary_reminder($employee)
	{
		// Use your existing bot token
		$bot_token = $telegram_bot;
		$today_display = date('d M Y');

		$message = "🔔 *Work Summary Reminder*\n\n" .
			"📅 *Date:* {$today_display}\n" .
			"👤 *Dear {$employee->name}*,\n\n" .
			"⚠️ You haven't submitted your daily work summary yet.\n" .
			"📝 Please submit it before end of day.\n\n" .
			"🔗 [Submit Now](" . base_url('dashboard') . ")";

		$payload = [
			'chat_id' => $employee->telegram_id,
			'text' => $message,
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

		// Optional: Log the response for debugging
		// error_log("Telegram reminder sent to {$employee->name}: " . $response);
	}

	private function output_json($data)
	{
		header('Content-Type: application/json');
		echo json_encode($data);
		exit;
	}

	public function absence_todo_check()
	{
		$today = date('Y-m-d');
		$test_mode = $this->input->get('test') === '1';

		// Skip weekends unless testing
		$current_day = date('w');
		if (!$test_mode && ($current_day == 5 || $current_day == 6)) {
			echo json_encode(['status' => 'skipped', 'message' => 'Weekend - No absence checks']);
			return;
		}

		// Skip holidays unless testing
		if (!$test_mode && $this->is_holiday($today)) {
			echo json_encode(['status' => 'skipped', 'message' => 'Holiday - No absence checks']);
			return;
		}

		// Get all active employees
		$employees = $this->db->select('staff.id, staff.name, staff.email, staff.telegram_id')
			->from('staff')
			->join('login_credential', 'login_credential.user_id = staff.id')
			->where('login_credential.active', 1)
			->where_not_in('login_credential.role', [1, 2, 3, 9, 10, 11, 12])
			->where_not_in('staff.id', [49, 22, 23, 28, 25, 31])
			->get()->result();

		$todo_created = 0;
		$skipped_count = 0;

		foreach ($employees as $employee) {
			// Check if absent today
			$attendance = $this->db->select('status')
				->where('staff_id', $employee->id)
				->where('date', $today)
				->get('staff_attendance')->row();

			// Skip if present or late
			if ($attendance && in_array($attendance->status, ['P', 'L'])) {
				$skipped_count++;
				continue;
			}

			// Check if on approved leave
			if ($this->is_on_leave($employee->id, $today)) {
				$skipped_count++;
				continue;
			}

			// Check if TODO already exists for today
			if ($this->absence_todo_exists($employee->id, $today)) {
				$skipped_count++;
				continue;
			}

			$this->create_absence_todo($employee, $today);
			$todo_created++;
		}

		echo json_encode([
			'status' => 'completed',
			'todos_created' => $todo_created,
			'skipped' => $skipped_count
		]);
	}

	private function absence_todo_exists($user_id, $date)
	{
		return $this->db->where('user_id', $user_id)
			->where('category', 'Absence')
			->where('DATE(issue_date)', $date)
			->count_all_results('warnings') > 0;
	}

	private function create_absence_todo($employee, $date)
	{
		// Get employee role and department
		$staff_info = $this->db
			->select('staff.department, login_credential.role')
			->join('login_credential', 'login_credential.user_id = staff.id')
			->where('staff.id', $employee->id)
			->get('staff')
			->row();

		$department_id = $staff_info ? $staff_info->department : null;
		$employee_role = $staff_info ? $staff_info->role : null;

		$reference = 'EMP/HR & ADMIN/Absence/TO-DO/' . date('Ymd') . '-Unauthorized Absence';

		$todo_data = [
			'reference' => $reference,
			'category' => 'Absence',
			'effect' => 'Attendance',
			'user_id' => $employee->id,
			'reason' => 'Unauthorized absence detected on ' . date('jS F, Y', strtotime($date)) . '. Please provide explanation for your absence.',
			'clearance_time' => 24,
			'status' => 1,
			'issued_by' => 1,
			'issue_date' => date('Y-m-d H:i:s')
		];

		// Roles that must be reviewed by COO
		$coo_review_roles = [3, 5, 8];

		// Fetch COO once
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
		if ($employee_role == 4 && !empty($department_id)) {

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
				$todo_data['manager_id'] = $manager->id;
				$todo_data['manager_review'] = 1;
			} elseif ($coo) {
				$todo_data['manager_id'] = $coo->id;
				$todo_data['manager_review'] = 1;
			}
		}

		/**
		 * CASE 2: Roles 3, 5, 8
		 * → Always COO
		 */
		if (in_array($employee_role, $coo_review_roles) && $coo) {
			$todo_data['manager_id'] = $coo->id;
			$todo_data['manager_review'] = 1;
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
			$todo_data['advisor_id'] = $advisor->id;
			$todo_data['advisor_review'] = 1;
		}


		$this->db->insert('warnings', $todo_data);
	}


	private function send_absence_todo_notifications($employee, $todo_id, $date)
	{
		// Database notification
		$this->db->insert('notifications', [
			'user_id' => $employee->id,
			'type' => 'absence_todo',
			'title' => 'Absence TODO Created',
			'message' => 'A TODO has been created for your unauthorized absence on ' . date('jS F, Y', strtotime($date)) . '. Please provide explanation within 24 hours.',
			'url' => base_url('todo'),
			'is_read' => 0,
			'created_at' => date('Y-m-d H:i:s')
		]);

		// Telegram notification
		if (!empty($employee->telegram_id)) {
			$bot_token = $telegram_bot;
			$message = "⚠️ *Absence TODO Created*\n\n" .
				"📅 *Date:* " . date('d M Y', strtotime($date)) . "\n" .
				"👤 *Dear {$employee->name}*,\n\n" .
				"❌ You were absent without approved leave.\n" .
				"📝 A TODO has been created. Please provide explanation within 24 hours.\n\n" .
				"🔗 [Complete TODO](" . base_url('todo') . ")";

			$payload = [
				'chat_id' => $employee->telegram_id,
				'text' => $message,
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
		}

		// Email notification
		if (!empty($employee->email)) {
			$config = $this->email_model->get_email_config();
			$mail_body = "<html><body style='font-family:Arial,sans-serif;'>
				<p>Dear <strong>{$employee->name}</strong>,</p>
				<p>A TODO has been automatically created because you were absent without approved leave on " . date('jS F, Y', strtotime($date)) . ".</p>
				<p><strong>Action Required:</strong> Please provide explanation for your absence within 24 hours.</p>
				<p><a href='" . base_url('todo') . "'>Complete TODO</a></p>
				<br><p>EMP System</p></body></html>";

			$email_data = [
				'smtp_host' => $config['smtp_host'],
				'smtp_auth' => true,
				'smtp_user' => $config['smtp_user'],
				'smtp_pass' => $config['smtp_pass'],
				'smtp_secure' => $config['smtp_encryption'],
				'smtp_port' => $config['smtp_port'],
				'from_email' => $config['email'],
				'from_name' => 'EMP Admin',
				'to_email' => $employee->email,
				'to_name' => $employee->name,
				'subject' => 'Absence TODO Created',
				'body' => $mail_body
			];

			$this->email_model->send_email_yandex($email_data);
		}

		// FCM notification
		$fcm_tokens = $this->get_staff_fcm_tokens([$employee->id]);
		if (!empty($fcm_tokens)) {
			$this->send_fcm_notification(
				'Absence TODO Created',
				'A TODO has been created for your unauthorized absence. Please provide explanation within 24 hours.',
				'',
				$fcm_tokens,
				['type' => 'absence_todo', 'todo_id' => $todo_id]
			);
		}
	}

	public function manual_leave_balance_reset()
	{
		$current_year = (int)date('Y');
		$reset_count = 0;

		// Disable parental leave for all employees
		$this->db->update('staff', ['parental_leave_enabled' => 0]);

		// Get all active employees
		$employees_query = "
			SELECT s.id, s.employee_type, s.joining_date, s.branch_id
			FROM staff s
			JOIN login_credential lc ON lc.user_id = s.id
			WHERE lc.active = 1
			AND s.id != 1
			AND lc.role NOT IN (9, 10, 11, 12)
		";

		$employees_result = $this->db->query($employees_query);

		if ($employees_result->num_rows() > 0) {
			// Get all leave categories
			$categories_result = $this->db->get('leave_category');
			$categories = $categories_result->result_array();

			foreach ($employees_result->result_array() as $employee) {
				foreach ($categories as $category) {
					$total_days = 0;

					// Calculate allocation based on employee type
					if ($employee['employee_type'] === 'regular') {
						$total_days = (float)$category['days'];
					} elseif ($employee['employee_type'] === 'intern') {
						if (strtolower($category['name']) === 'sick leave') {
							$total_days = 2;
						} else {
							$total_days = 0;
						}
					} elseif ($employee['employee_type'] === 'probation') {
						$joining_date = new DateTime($employee['joining_date']);
						$current_date = new DateTime();
						$months_worked = $joining_date->diff($current_date)->m + ($joining_date->diff($current_date)->y * 12);

						if (strtolower($category['name']) === 'sick leave') {
							// No sick leave if under 3 months
							if ($months_worked < 3) {
								$total_days = 0;
							} else {
								// Calculate probation end date (9 months from joining)
								$probation_end_date = clone $joining_date;
								$probation_end_date->add(new DateInterval('P9M'));

								// Get end of current year or probation end date, whichever is earlier
								$year_end = new DateTime($current_year . '-12-31');
								$end_date = min($probation_end_date, $year_end);

								// Calculate months from current year start to end date
								$year_start = new DateTime($current_year . '-01-01');
								$months_in_year = $year_start->diff($end_date)->m + ($year_start->diff($end_date)->y * 12) + 1;

								$monthly_sick_leave = (float)$category['days'] / 12;
								$calculated_days = $monthly_sick_leave * $months_in_year;
								$total_days = round($calculated_days);
							}
						} elseif (strtolower($category['name']) === 'annual') {
							$interval_days = $current_date->diff($joining_date)->days;
							if ($interval_days >= 26) {
								$total_days = floor($interval_days / 26);
							}
						} else {
							$total_days = 0;
						}
					}

					// Check if record exists for current year
					$existing = $this->db->get_where('leave_balance', [
						'user_id' => $employee['id'],
						'leave_category_id' => $category['id'],
						'year' => $current_year
					])->row_array();

					if ($existing) {
						// Update existing record
						$this->db->where('id', $existing['id'])
							->update('leave_balance', [
								'total_days' => $total_days,
								'used_days' => 0,
								'updated_at' => date('Y-m-d H:i:s')
							]);
					} else {
						// Insert new record
						$this->db->insert('leave_balance', [
							'user_id' => $employee['id'],
							'leave_category_id' => $category['id'],
							'total_days' => $total_days,
							'used_days' => 0,
							'year' => $current_year,
							'updated_at' => date('Y-m-d H:i:s')
						]);
					}
					$reset_count++;
				}
			}
		}

		echo json_encode([
			'status' => 'success',
			'message' => 'Leave balances reset successfully for year ' . $current_year,
			'records_processed' => $reset_count,
			'employees_found' => $employees_result->num_rows(),
			'current_year' => $current_year
		]);
	}

	public function check_daily_goal_tasks()
	{
		header('Content-Type: application/json');

		$yesterday = date('Y-m-d', strtotime('-1 day'));

		$this->db->select('g.id as goal_id, g.goal_name, g.pod_owner_id, s.name as pod_owner_name, s.department, lc.role')
			->from('goals g')
			->join('staff s', 's.id = g.pod_owner_id', 'left')
			->join('login_credential lc', 'lc.user_id = s.id', 'left');
		$all_goals = $this->db->get()->result();

		if (empty($all_goals)) {
			echo json_encode(['status' => 'success', 'has_tasks' => false, 'message' => 'No goals found']);
			return;
		}

		$goals_with_owners = [];
		foreach ($all_goals as $goal) {
			$goals_with_owners[$goal->goal_id] = [
				'goal_name' => $goal->goal_name,
				'pod_owner_id' => $goal->pod_owner_id,
				'pod_owner_name' => $goal->pod_owner_name,
				'department' => $goal->department,
				'role' => $goal->role,
				'task_count' => 0
			];
		}

		$this->db->select('g.id as goal_id, gm.milestone_id')
			->from('goals g')
			->join('goal_milestones gm', 'gm.goal_id = g.id')
			->where('gm.milestone_id IS NOT NULL');
		$goal_milestones = $this->db->get()->result();

		$milestone_ids = array_unique(array_column($goal_milestones, 'milestone_id'));

		$total_task_count = 0;
		if (!empty($milestone_ids)) {
			$this->db->select('COUNT(DISTINCT ti.id) as count')
				->from('tracker_issues ti')
				->join('planner_events pe', 'pe.issue_id = ti.id')
				->where('DATE(pe.start_time)', $yesterday)
				->where_in('ti.milestone', $milestone_ids);
			$result = $this->db->get()->row();
			$total_task_count = $result ? (int)$result->count : 0;

			$goal_milestone_map = [];
			foreach ($goal_milestones as $gm) {
				if (!isset($goal_milestone_map[$gm->goal_id])) {
					$goal_milestone_map[$gm->goal_id] = [];
				}
				$goal_milestone_map[$gm->goal_id][] = $gm->milestone_id;
			}

			foreach ($goal_milestone_map as $goal_id => $milestone_ids_for_goal) {
				$this->db->select('COUNT(DISTINCT ti.id) as count')
					->from('tracker_issues ti')
					->join('planner_events pe', 'pe.issue_id = ti.id')
					->where('DATE(pe.start_time)', $yesterday)
					->where_in('ti.milestone', $milestone_ids_for_goal);
				$goal_result = $this->db->get()->row();
				$goals_with_owners[$goal_id]['task_count'] = $goal_result ? (int)$goal_result->count : 0;
			}
		}

		$todos_created = 0;
		foreach ($goals_with_owners as $goal_id => $goal) {
			if ($goal['task_count'] == 0) {
				$existing_todo = $this->db->where('user_id', $goal['pod_owner_id'])
					->where('category', 'Goal Planning')
					->where('DATE(issue_date)', date('Y-m-d'))
					->where('reason LIKE', '%' . $goal['goal_name'] . '%')
					->count_all_results('warnings');

				if ($existing_todo == 0) {
					$this->create_goal_planning_todo($goal_id, $goal, $yesterday);
					$todos_created++;
				}
			}
		}

		echo json_encode([
			'status' => 'success',
			'has_tasks' => $total_task_count > 0,
			'task_count' => $total_task_count,
			'date' => $yesterday,
			'todos_created' => $todos_created,
			'goals' => array_values($goals_with_owners)
		]);
	}

	private function create_goal_planning_todo($goal_id, $goal, $date)
	{
		$reference = 'EMP/GOALS/Planning/TO-DO/' . date('Ymd') . '-' . $goal_id;
		$yesterday = $date;
		$clearance_time = 24;
		$todo_data = [
			'reference' => $reference,
			'category' => 'Goal Planning',
			'effect' => 'Goal Execution',
			'user_id' => $goal['pod_owner_id'],
			'reason' => 'No tasks planned for today ' .$yesterday . ' for goal: ' . $goal['goal_name'] . '. Please explain for why no tasks were planned or recorded for this goal.',
			'clearance_time' => $clearance_time,
			'status' => 1,
			'email_sent' => 1,
			'issued_by' => 1,
			'issue_date' => date('Y-m-d H:i:s')
		];

		// Roles that must be reviewed by COO
		$coo_review_roles = [3, 5, 8];

		// Fetch COO once
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
		if ($goal['role'] == 4 && !empty($goal['department'])) {

			$manager = $this->db
				->select('staff.id')
				->from('staff')
				->join('login_credential', 'login_credential.user_id = staff.id')
				->where('staff.department', $goal['department'])
				->where('login_credential.role', 8)
				->where('login_credential.active', 1)
				->get()
				->row();

			if ($manager) {
				$todo_data['manager_id'] = $manager->id;
				$todo_data['manager_review'] = 1;
			} elseif ($coo) {
				$todo_data['manager_id'] = $coo->id;
				$todo_data['manager_review'] = 1;
			}
		}

		/**
		 * CASE 2: Non-regular roles (3, 5, 8)
		 * → Always COO
		 */
		if (in_array($goal['role'], $coo_review_roles) && $coo) {
			$todo_data['manager_id'] = $coo->id;
			$todo_data['manager_review'] = 1;
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
			$todo_data['advisor_id'] = $advisor->id;
			$todo_data['advisor_review'] = 1;
		}


		$this->db->insert('warnings', $todo_data);
		$todo_id = $this->db->insert_id();

		$owner = $this->db->select('name, email, telegram_id')->where('id', $goal['pod_owner_id'])->get('staff')->row();

		if ($owner) {
			$this->db->insert('notifications', [
				'user_id' => $goal['pod_owner_id'],
				'type' => 'goal_planning_todo',
				'title' => 'Goal Planning TODO Created',
				'message' => 'No tasks planned for today for goal: ' . $goal['goal_name'] . '. Please plan tasks and explain.',
				'url' => base_url('todo'),
				'is_read' => 0,
				'created_at' => date('Y-m-d H:i:s')
			]);

			if (!empty($owner->email)) {
				$config = $this->email_model->get_email_config();

				$mail_subject = 'Explanation Required: No Tasks Logged for ' . $goal['goal_name'];
				$mail_body = "
				<html>
				  <body style='font-family:Arial, sans-serif; background:#f9f9f9; padding:20px;'>
					<table style='max-width:600px; margin:auto; background:#fff; border-radius:8px; padding:20px; box-shadow:0 0 10px rgba(0,0,0,0.05);'>
					  <tr><td style='text-align:center;'>
						<h2 style='color:#d32f2f;'>Employee Explanation Notice</h2>
					  </td></tr>
					  <tr><td style='font-size:15px; color:#333;'>
						<p>Dear <strong>{$owner->name}</strong>,</p>
						<p>A TODO/Showcause has been created under the goal: <strong> {$goal['goal_name']}</strong>.</p>
						<p><strong>Yesterday ({$yesterday})</strong>, no tasks were logged against this goal by you or any POD member. Please provide a brief explanation via the EMP system within <strong>{$clearance_time}</strong> hours for why no tasks were planned or recorded for this goal.</p>

						<p style='margin-top:20px;'>Thank you,<br><strong>Employee Max Portal (EMP)</strong></p>
					  </td></tr>
					</table>
				  </body>
				</html>";

				$email_data = [
					'smtp_host' => $config['smtp_host'],
					'smtp_auth' => true,
					'smtp_user' => $config['smtp_user'],
					'smtp_pass' => $config['smtp_pass'],
					'smtp_secure' => $config['smtp_encryption'],
					'smtp_port' => $config['smtp_port'],
					'from_email' => $config['email'],
					'from_name' => 'EMP Admin',
					'to_email' => $owner->email,
					'to_name' => $owner->name,
					'subject' => $mail_subject,
					'body' => $mail_body
				];
				$this->email_model->send_email_yandex($email_data);
			}

			if (!empty($owner->telegram_id)) {
				$bot_token = $telegram_bot;
				$yesterday = date('jS F, Y', strtotime('-1 day', strtotime($date)));
				$message = "⚠️ *Explanation Required*\n\n" .
					"👤 Dear *{$owner->name}*,\n\n" .
					"A TODO/Showcause has been created under the goal:\n" .
					"🎯 *{$goal['goal_name']}*\n\n" .
					"📅 *Yesterday ({$yesterday})*, no tasks were logged against this goal by you or any POD member.\n\n" .
					"📝 Please provide a brief explanation for why no tasks were planned or recorded for this goal.\n\n" .
					"Thank you for your prompt attention.\n\n" .
					"🔗 [Complete TODO](" . base_url('todo') . ")";

				$payload = [
					'chat_id' => $owner->telegram_id,
					'text' => $message,
					'parse_mode' => 'Markdown',
					'disable_web_page_preview' => true
				];

				$url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
				curl_exec($ch);
				curl_close($ch);
			}

				$bot_token = $telegram_bot;
				$group_chat_id = $telegram_chatID;
			$yesterday = date('jS F, Y', strtotime('-1 day', strtotime($date)));
			$group_message = "⚠️ *Explanation Required: No Tasks Logged*\n\n" .
				"🎯 *Goal:* {$goal['goal_name']}\n" .
				"👤 *Pod Owner:* {$goal['pod_owner_name']}\n" .
				"📅 *Date:* {$yesterday}\n\n" .
				"❌ No tasks were logged by the pod owner or any POD member.\n" .
				"📝 TODO/Showcause created for explanation.";

			$payload = [
				'chat_id' => $group_chat_id,
				'text' => $group_message,
				'parse_mode' => 'Markdown'
			];

			$url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
			curl_exec($ch);
			curl_close($ch);
		}
	}

	public function remind_goal_tasks_today()
	{
		header('Content-Type: application/json');

		$today = date('Y-m-d');

		$this->db->select('g.id as goal_id, g.goal_name, g.pod_owner_id, s.name as pod_owner_name, s.email, s.telegram_id')
			->from('goals g')
			->join('staff s', 's.id = g.pod_owner_id', 'left');
		$all_goals = $this->db->get()->result();

		if (empty($all_goals)) {
			echo json_encode(['status' => 'success', 'message' => 'No goals found']);
			return;
		}

		$goals_with_owners = [];
		foreach ($all_goals as $goal) {
			$goals_with_owners[$goal->goal_id] = [
				'goal_name' => $goal->goal_name,
				'pod_owner_id' => $goal->pod_owner_id,
				'pod_owner_name' => $goal->pod_owner_name,
				'email' => $goal->email,
				'telegram_id' => $goal->telegram_id,
				'task_count' => 0
			];
		}

		$this->db->select('g.id as goal_id, gm.milestone_id')
			->from('goals g')
			->join('goal_milestones gm', 'gm.goal_id = g.id')
			->where('gm.milestone_id IS NOT NULL');
		$goal_milestones = $this->db->get()->result();

		$milestone_ids = array_unique(array_column($goal_milestones, 'milestone_id'));

		if (!empty($milestone_ids)) {
			$goal_milestone_map = [];
			foreach ($goal_milestones as $gm) {
				if (!isset($goal_milestone_map[$gm->goal_id])) {
					$goal_milestone_map[$gm->goal_id] = [];
				}
				$goal_milestone_map[$gm->goal_id][] = $gm->milestone_id;
			}

			foreach ($goal_milestone_map as $goal_id => $milestone_ids_for_goal) {
				$this->db->select('COUNT(DISTINCT ti.id) as count')
					->from('tracker_issues ti')
					->join('planner_events pe', 'pe.issue_id = ti.id')
					->where('DATE(pe.start_time)', $today)
					->where_in('ti.milestone', $milestone_ids_for_goal);
				$goal_result = $this->db->get()->row();
				$goals_with_owners[$goal_id]['task_count'] = $goal_result ? (int)$goal_result->count : 0;
			}
		}

		$reminders_sent = 0;
		foreach ($goals_with_owners as $goal_id => $goal) {
			if ($goal['task_count'] == 0) {
				$this->send_goal_task_reminder($goal, $today);
				$reminders_sent++;
			}
		}

		echo json_encode([
			'status' => 'success',
			'date' => $today,
			'reminders_sent' => $reminders_sent,
			'goals' => array_values($goals_with_owners)
		]);
	}

	private function send_goal_task_reminder($goal, $date)
	{
		if (!empty($goal['email'])) {
			$config = $this->email_model->get_email_config();
			$today_formatted = date('jS F, Y', strtotime($date));

			$mail_subject = 'Reminder: Plan Tasks for Today - ' . $goal['goal_name'];
			$mail_body = "
			<html>
			  <body style='font-family:Arial, sans-serif; background:#f9f9f9; padding:20px;'>
				<table style='max-width:600px; margin:auto; background:#fff; border-radius:8px; padding:20px; box-shadow:0 0 10px rgba(0,0,0,0.05);'>
				  <tr><td style='text-align:center;'>
					<h2 style='color:black;'>Friendly Reminder Notice</h2>
				  </td></tr>
				  <tr><td style='font-size:15px; color:#333;'>
					<p>Dear <strong>{$goal['pod_owner_name']}</strong>,</p>
					<p>This is a friendly reminder regarding your goal: <strong>{$goal['goal_name']}</strong>

					<p><strong>Today ({$today_formatted})</strong>, no tasks have been planned for this goal yet. Please plan and schedule tasks in the planner to ensure progress.</p>


					<p style='margin-top:20px;'>Thank you,<br><strong>Employee Max Portal (EMP)</strong></p>
				  </td></tr>
				</table>
			  </body>
			</html>";


			$email_data = [
				'smtp_host' => $config['smtp_host'],
				'smtp_auth' => true,
				'smtp_user' => $config['smtp_user'],
				'smtp_pass' => $config['smtp_pass'],
				'smtp_secure' => $config['smtp_encryption'],
				'smtp_port' => $config['smtp_port'],
				'from_email' => $config['email'],
				'from_name' => 'EMP Admin',
				'to_email' => $goal['email'],
				'to_name' => $goal['pod_owner_name'],
				'subject' => $mail_subject,
				'body' => $mail_body
			];
			$this->email_model->send_email_yandex($email_data);
		}

		if (!empty($goal['telegram_id'])) {
			$bot_token = $telegram_bot;
			$today_formatted = date('jS F, Y', strtotime($date));
			$message = "📌 *Reminder: Plan Tasks for Today*\n\n" .
				"👤 Dear *{$goal['pod_owner_name']}*,\n\n" .
				"This is a friendly reminder regarding your goal:\n" .
				"🎯 *{$goal['goal_name']}*\n\n" .
				"📅 *Today ({$today_formatted})*, no tasks have been planned for this goal yet.\n\n" .
				"📝 Please plan and schedule tasks in the planner to ensure progress.\n\n" .
				"Thank you for your attention.\n\n" .
				"🔗 [Plan Tasks Now](" . base_url('planner') . ")";

			$payload = [
				'chat_id' => $goal['telegram_id'],
				'text' => $message,
				'parse_mode' => 'Markdown',
				'disable_web_page_preview' => true
			];

			$url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
			curl_exec($ch);
			curl_close($ch);
		}
	}

	}