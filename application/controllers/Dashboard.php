<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends Dashboard_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('dashboard_model');
        $this->load->model('attendance_model');
        $this->load->model('tasks_model');

        $this->load->model('meeting_minutes_model');
        $this->load->model('email_model');

        $this->uploadDir = FCPATH . 'uploads/notifications/';
        $this->serviceAccountPath = FCPATH . 'assets/notification/service-account.json';
        $this->logFile = FCPATH . 'assets/notification/logs/fcm_notifications.log';
    }

	public function index()
		{
		$logged_in_user_id = get_loggedin_user_id();
		$logged_in_role_id = loggedin_role_id();
		 // 🔹 Redirect advisors (role 10) to advisor dashboard
		if ($logged_in_role_id == 10) {
			redirect(base_url('advisor'));
			return;
		}

		$leave_user_cards = [];
		$branchID = get_loggedin_branch_id();
		$today = date('Y-m-d');
		$view_all_roles = [1, 2, 3, 5];

		if ($this->input->get('branch_id')) {
			$branchID = $this->input->get('branch_id');
		}

		if (in_array($logged_in_role_id, [2, 3, 5])) {
			$branchID = null;
		}

		$this->db->select('staff_id, status, in_time, out_time')
			->from('staff_attendance')
			->where('date', $today);
		if (!empty($branchID)) {
			$this->db->where('branch_id', $branchID);
		}
		$attendance_data = $this->db->get()->result_array();

		$attendance_lookup = [];
		foreach ($attendance_data as $row) {
			$attendance_lookup[$row['staff_id']] = [
				'status' => $row['status'],
				'in_time' => $row['in_time'],
				'out_time' => $row['out_time']
			];
		}

		$this->db->select('staff.id')
			->from('staff')
			->join('login_credential', 'login_credential.user_id = staff.id')
			->where('login_credential.active', 1)
			->where_not_in('login_credential.role', [1, 9, 10, 11, 12]);

		$total_staff = $this->db->count_all_results();

		$this->db->select('staff_id')
			->from('staff_attendance')
			->join('login_credential', 'login_credential.user_id = staff_attendance.staff_id')
			->where('login_credential.active', 1)
			->where_not_in('login_credential.role', [1, 9, 10, 11, 12])
			->where('date', $today)
			->where_in('status', ['P', 'L']);
		if (!empty($branchID)) {
			$this->db->where('branch_id', $branchID);
		}
		$total_present = $this->db->count_all_results();

		$total_absent = $total_staff - $total_present;

		$employees = [];
		$logged_user_department = null;

		if (in_array($logged_in_role_id, $view_all_roles)) {
		$this->db->select('staff.*, login_credential.username, staff_designation.name as designation_name')
				 ->from('staff')
				 ->join('login_credential', 'login_credential.user_id = staff.id')
				 ->join('staff_designation', 'staff_designation.id = staff.designation', 'left')
				 ->where('login_credential.active', 1)
				->where_not_in('login_credential.role', [1, 9, 10, 11, 12])
				 ->where('staff.id !=', 1);

			if (!empty($branchID)) {
				$this->db->where('staff.branch_id', $branchID);
			}
			$employees = $this->db->get()->result();

		} elseif ($logged_in_role_id == 8) {
			$logged_user_department = $this->db->select('department')->from('staff')->where('id', $logged_in_user_id)->get()->row('department');

			$this->db->select('staff.*, login_credential.username, staff_designation.name as designation_name')
				->from('staff')
				->join('login_credential', 'login_credential.user_id = staff.id')
				->join('staff_designation', 'staff_designation.id = staff.designation', 'left')
				->where('login_credential.active', 1)
				->where('staff.department', $logged_user_department)
				->where('staff.id !=', 1);
			if (!empty($branchID)) {
				$this->db->where('staff.branch_id', $branchID);
			}
			$employees = $this->db->get()->result();

		} else {
			$this->db->select('staff.*, login_credential.username, staff_designation.name as designation_name')
				->from('staff')
				->join('login_credential', 'login_credential.user_id = staff.id')
				->join('staff_designation', 'staff_designation.id = staff.designation', 'left')
				->where('login_credential.active', 1)
				->where('staff.id', $logged_in_user_id);
			$employee = $this->db->get()->row();
			$employees = ($employee) ? [$employee] : [];
		}

		foreach ($employees as $employee) {
			$user_id = $employee->id;
			$branch_id = $employee->branch_id;

			// ✅ Fixed Leave Summary using leave_balance
			$leave_summary = [];
			$current_year = date('Y');
			$staff = $this->db->select('parental_leave_enabled')->where('id', $user_id)->get('staff')->row();
			$query = $this->db
				->select('lc.name as category, lb.total_days as allowed, lb.leave_category_id')
				->from('leave_balance as lb')
				->join('leave_category as lc', 'lc.id = lb.leave_category_id')
				->where('lb.user_id', $user_id)
				->where('lb.year', $current_year)
				->where('lc.days >', 0);
				//->where('lc.id !=', 2); // to terminate the casual leaves

			if (!$staff || !$staff->parental_leave_enabled) {
				$query->where('lc.name !=', 'Parental Leave');
			}

			$leave_balances = $query->get()->result();

			foreach ($leave_balances as $balance) {
				$used = (float) ($this->db->select_sum('leave_days')
					->where([
						'user_id' => $user_id,
						'category_id' => $balance->leave_category_id,
						'status' => 2,
						'YEAR(start_date)' => $current_year
					])
					->get('leave_application')
					->row()
					->leave_days ?? 0);

				$leave_summary[] = [
					'category' => $balance->category,
					'allowed'  => (float) $balance->allowed,
					'used'     => $used
				];
			}

			$attendance_status = $attendance_lookup[$user_id]['status'] ?? 'A';
			$in_time = $attendance_lookup[$user_id]['in_time'] ?? null;
			$out_time = $attendance_lookup[$user_id]['out_time'] ?? null;

			switch ($attendance_status) {
				case 'P':
					$attendance_label = 'Present';
					break;
				case 'L':
					$attendance_label = 'Late';
					break;
				default:
					$attendance_label = 'Absent';
					break;
			}

			// ✅ Get current task from planner based on time match
			$current_datetime = date('Y-m-d H:i:s');
			$planner_task = null;

			// Get current task from planner_events
			$planner_task = $this->db->select('ti.task_title, pe.start_time, pe.end_time')
				->from('planner_events pe')
				->join('tracker_issues ti', 'ti.id = pe.issue_id', 'left')
				->where('pe.user_id', $user_id)
				->where('pe.start_time <=', $current_datetime)
				->where('pe.end_time >=', $current_datetime)
				->where('pe.status', 0)
				->order_by('pe.start_time', 'ASC')
				->limit(1)
				->get()
				->row();

			// If no planner task matches current time, show "No task in the planner"
			if (!$planner_task) {
				$task = (object)[
					'latest_task_title' => 'No task in the planner',
					'task_time' => null
				];
			} else {
				$task = (object)[
					'latest_task_title' => $planner_task->task_title,
					'task_time' => $planner_task->start_time
				];
			}

			$break = $this->db->select('pause_history.id, pauses.name as break_name')
				->from('pause_history')
				->join('pauses', 'pauses.id = pause_history.pause_id', 'left')
				->where('pause_history.user_id', $user_id)
				->where('pause_history.start_datetime IS NOT NULL')
				->where('pause_history.end_datetime IS NULL')
				->order_by('pause_history.id', 'DESC')
				->limit(1)
				->get()
				->row();

			$is_on_break = $break ? true : false;
			$break_name = $break->break_name ?? null;


			$today = date('Y-m-d');

			$leave = $this->db->select('leave_category.name as leave_name')
				->from('leave_application')
				->join('leave_category', 'leave_application.category_id = leave_category.id', 'left')
				->where('leave_application.user_id', $user_id)
				->where('leave_application.status', 2) // status = 2 means approved
				->where("'$today' BETWEEN leave_application.start_date AND leave_application.end_date", null, false)
				->order_by('leave_application.id', 'DESC')
				->limit(1)
				->get()
				->row();

			$is_on_leave = $leave ? true : false;
			$leave_name = $leave->leave_name ?? null;

			$this->db->select('COUNT(penalty_days.id) as pending_penalty_count')
				 ->from('penalty_days')
				 ->where('penalty_days.staff_id', $user_id)
				 ->group_start()
					 ->where('NOT EXISTS (SELECT 1 FROM staff_attendance
										  WHERE staff_attendance.staff_id = penalty_days.staff_id
										  AND DATE(staff_attendance.date) = DATE(penalty_days.penalty_date)
										  AND (staff_attendance.status = "P" OR staff_attendance.status = "L"))', null, false)
				 ->group_end();

		$query = $this->db->get();
		$result = $query->row();
		$pending_count = $result ? $result->pending_penalty_count : 0;

			$leave_user_cards[] = [
				'name'         => $employee->name,
				'username'     => $employee->username,
				'designation'  => $employee->designation_name ?? 'N/A',
				'staff_id'     => $employee->id,
				'employee_id'  => $employee->staff_id,
				'photo'        => $employee->photo ?? 'default.png',
				'attendance'   => $attendance_label,
				'in_time'      => $in_time,
				'out_time'     => $out_time,
				'branchID'     => $branchID,
				'leave_summary'=> $leave_summary,
				'latest_task' => $task->latest_task_title ?? null,
				'task_time' => $task->task_time ?? null,
				'is_on_break' => $is_on_break,
				'break_name' => $break_name,
				'is_on_leave'  => $is_on_leave,
				'leave_name'   => $leave_name,
				'pending_count'   => $pending_count,
			];
		}

		if ($logged_in_role_id == 8 && $logged_user_department !== null) {
			$total_under_you = $this->db
				->where('department', $logged_user_department)
				->where('id !=', $logged_in_user_id)
				->count_all_results('staff');
		} else {
			$total_under_you = 0;
		}

		// Sort by attendance status: Absent > Late > Present
		usort($leave_user_cards, function ($a, $b) {
			$priority = ['Absent' => 1, 'Late' => 2, 'Present' => 3];
			return $priority[$a['attendance']] <=> $priority[$b['attendance']];
		});


		// --------- NEW: Prepare attendance summary data for last 30 days pie chart ---------

		// Get list of weekends and holidays for the branch (replace with your actual model calls)
		$weekends = $this->attendance_model->getWeekendDaysSession($branchID);  // returns array of 'Y-m-d' weekend dates in last 7 days
		$holidays = explode('","', $this->attendance_model->getHolidays($branchID)); // returns array of 'Y-m-d' holiday dates

		// Build array of valid working dates within last 7 days (exclude weekends and holidays)
		// Build array of valid working dates for current month
		$currentMonth = date('m');
		$currentYear = date('Y');
		$startDate = "{$currentYear}-{$currentMonth}-01";
		//$endDate = date('Y-m-t', strtotime($startDate)); // Last day of the month
		$endDate = date('Y-m-d'); // Today's date

		$valid_working_dates = [];
		$period = new DatePeriod(
			new DateTime($startDate),
			new DateInterval('P1D'),
			(new DateTime($endDate))->modify('+1 day') // include end date
		);

		foreach ($period as $dateObj) {
			$date = $dateObj->format('Y-m-d');
			if (!in_array($date, $weekends) && !in_array($date, $holidays)) {
				$valid_working_dates[] = $date;
			}
		}

		$valid_days_count = count($valid_working_dates);

		$attendance_data = ['P' => 0, 'L' => 0, 'A' => 0, 'LV' => 0];

		if (!in_array($logged_in_role_id, ['1', '2', '3', '5', '9'])) {

			if (!empty($valid_working_dates)) {
				// For individual user
				$this->db->select('status, COUNT(*) as count')
					->from('staff_attendance')
					->where_in('date', $valid_working_dates)
					->where('staff_id', $logged_in_user_id)
					->group_by('status');
				$attendance_stats = $this->db->get()->result_array();

				foreach ($attendance_stats as $row) {
					if (isset($attendance_data[$row['status']])) {
						$attendance_data[$row['status']] = (int)$row['count'];
					}
				}

				// Count approved leave days within the valid working dates
				$this->db->select('start_date, end_date')
					->from('leave_application')
					->where('user_id', $logged_in_user_id)
					->where('status', 2)
					->group_start()
						->where('start_date <=', end($valid_working_dates))
						->where('end_date >=', reset($valid_working_dates))
					->group_end();
				$leave_entries = $this->db->get()->result();

				$leave_days = [];
				foreach ($leave_entries as $entry) {
					$leave_period = new DatePeriod(
						new DateTime($entry->start_date),
						new DateInterval('P1D'),
						(new DateTime($entry->end_date))->modify('+1 day')
					);
					foreach ($leave_period as $ldate) {
						$d = $ldate->format('Y-m-d');
						if (in_array($d, $valid_working_dates)) {
							$leave_days[$d] = true;
						}
					}
				}

				$attendance_data['LV'] = count($leave_days);

				$total_expected = count($valid_working_dates);
				$marked = $attendance_data['P'] + $attendance_data['L'] + $attendance_data['LV'];
				$attendance_data['A'] = max(0, $total_expected - $marked);

			} else {
				// No valid working dates; initialize all counts to zero
				$attendance_data['P'] = 0;
				$attendance_data['L'] = 0;
				$attendance_data['LV'] = 0;
				$attendance_data['A'] = 0;
			}
		}

		$this->data['attendance_pie_data'] = $attendance_data;

		// --------- NEW: Prepare tracker issues and RDC task pie chart data ---------
		$this->data['tracker_issues_pie_data'] = $this->dashboard_model->getTrackerIssuesPieData($logged_in_user_id, $logged_in_role_id);
		$this->data['rdc_task_pie_data'] = $this->dashboard_model->getRdcTaskPieData($logged_in_user_id, $logged_in_role_id);

		$this->data['leave_user_cards'] = $leave_user_cards;
		$this->data['total_staff'] = $total_staff;
		$this->data['total_present'] = $total_present;
		$this->data['total_absent'] = $total_absent;
		$this->data['total_under_you'] = $total_under_you;
		$this->data['recent_summaries'] = $this->dashboard_model->get_recent_summaries();
		$this->data['meetings'] = $this->meeting_minutes_model->get_latest_summaries($logged_in_role_id);
		$this->data['headerelements']   = array(
			'css' => array(
				'vendor/dropify/css/dropify.min.css',
				'vendor/daterangepicker/daterangepicker.css',
				'vendor/fullcalendar/fullcalendar.css',
			),
			'js' => array(
				'vendor/dropify/js/dropify.min.js',
				'vendor/moment/moment.js',
				'vendor/daterangepicker/daterangepicker.js',
				'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js',
				'vendor/fullcalendar/fullcalendar.js',
			),
		);

		$this->data['logged_in_role_id'] = $logged_in_role_id;
		$this->data['title'] = translate('dashboard');
		$this->data['sub_page'] = 'dashboard/index';
		$this->data['main_menu'] = 'dashboard';

		$this->load->view('layout/index', $this->data);
	}

	public function update_attendance() {
		// Get the attendance status data from the form
		$attendance_data = $this->input->post('attendance');

		// Check if the data is not empty
		if (!empty($attendance_data)) {
			foreach ($attendance_data as $key => $data) {
				// Retrieve the attendance ID and new status
				$atten_id = $data['attendance_id']; // 'P' for present or 'L' for late
				$atten_status = $data['status']; // 'P' for present or 'L' for late

				// Update the attendance status in the database
				$this->db->where('id', $atten_id);
				$this->db->update('staff_attendance', [
					'status' => $atten_status,
					'is_manual' => 0,  // Mark as reviewed
					'approval_status' => 'approved'  // Optionally auto-approve
				]);
			}

			// Set a success message
			$this->session->set_flashdata('message', 'Attendance status updated successfully.');
		} else {
			// Set an error message if no attendance is selected for update
			$this->session->set_flashdata('error', 'No attendance selected for update.');
		}

		// Redirect to the dashboard
		redirect('dashboard');
	}

	public function update_attendance_single() {
		$attendance_id = $this->input->post('attendance_id');
		$status = $this->input->post('status');

		if ($attendance_id && $status) {
			$this->db->where('id', $attendance_id);
			$this->db->update('staff_attendance', [
				'status' => $status,
				'is_manual' => 0,
				'approval_status' => 'approved'
			]);
			$this->session->set_flashdata('message', 'Single attendance updated.');
		} else {
			$this->session->set_flashdata('error', 'Invalid input for single attendance update.');
		}

		redirect('dashboard');
	}

	public function manual_checkin()
	{
		$staff_id = get_loggedin_user_id();
		$branch_id = get_loggedin_branch_id();
		$in_time = $this->input->post('in_time');
		$date = $this->input->post('date');
		$ip_address = $this->input->ip_address();
		$remarks = $this->input->post('remarks');  // Capture remarks


		// STEP 1: Check if date is weekend
		$weekends = $this->attendance_model->getWeekendDaysSession($branch_id); // returns array of days ['Sunday', 'Friday'] etc.
		$day_name = date('l', strtotime($date)); // e.g., 'Friday'
		if (in_array($day_name, $weekends)) {
			echo json_encode([
				'status' => 'error',
				'message' => 'Cannot check in on a weekend (' . $day_name . ').',
			]);
			return;
		}

		// STEP 2: Check if date is a holiday
		$holiday_string = $this->attendance_model->getHolidays($branch_id);
		$holiday_array = explode('","', trim($holiday_string, '"'));
		if (in_array($date, $holiday_array)) {
			echo json_encode([
				'status' => 'error',
				'message' => 'Cannot check in on a holiday (' . $date . ').',
			]);
			return;
		}

		// STEP 3: Determine attendance status
		$late_threshold = strtotime('10:30:59');
		$check_in_unix = strtotime($in_time);
		$status = ($check_in_unix > $late_threshold) ? 'L' : 'P';

		// STEP 4: Save or update attendance
		$existing = $this->db->where(['staff_id' => $staff_id, 'date' => $date])->get('staff_attendance')->row();

		if ($existing) {
			$this->db->where('id', $existing->id)->update('staff_attendance', [
				'in_time' => $in_time,
				'status' => $status
			]);
			$status_msg = 'updated';
		} else {
			$this->db->insert('staff_attendance', [
				'staff_id' => $staff_id,
				'status' => $status,
				'remark' => $remarks,
				'qr_code' => 0,
				'is_manual' => 1,
				'in_time' => $in_time,
				'date' => $date,
				'branch_id' => $branch_id,
				'ip_address' => $ip_address,
				'approval_status' => 'pending'
			]);
			$status_msg = 'inserted';

			// POST to n8n webhook
			$staff_info = $this->db->get_where('staff', ['id' => $staff_id])->row();

			$post_data = [[
				"tenant_id" => 15,
				"event_id" => time(),
				"capture_id" => uniqid(),
				"task_name" => "Face Detection",
				"person_id" =>$staff_info ? $staff_info->staff_id : "Unknown",
				"group_name" => "Face Detection",
				"name" => $staff_info ? $staff_info->name : "Unknown",
				"capture_date" => $date,
				"image_url" => "", // Optional: can be generated from internal logic if available
				"check_in" => $in_time,
				"check_out" => null,
				"check_out_image" => null
			]];

			// CURL to webhook
			$curl = curl_init();
			curl_setopt_array($curl, [
				CURLOPT_URL => "https://n8n.emp.com.bd/webhook/308adc14-31ce-4430-80bd-d3f5732ffc71",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => json_encode($post_data),
				CURLOPT_HTTPHEADER => [
					'Content-Type: application/json'
				]
			]);
			$response = curl_exec($curl);
			curl_close($curl);
		}

		echo json_encode([
			'status' => $status_msg,
			'status_label' => $status == 'P' ? 'Present' : 'Late',
			'redirect' => base_url('dashboard')
		]);
	}

	public function manual_checkout()
	{
		$staff_id = get_loggedin_user_id();
		$out_time = $this->input->post('out_time');
		$date = $this->input->post('date');
		$ip_address = $this->input->ip_address();

		$existing = $this->db->where(['staff_id' => $staff_id, 'date' => $date])->get('staff_attendance')->row();

		if ($existing) {
			$this->db->where('id', $existing->id)->update('staff_attendance', [
				'ip_address' => $ip_address,
				'out_time' => $out_time
			]);
			$status_msg = 'updated';
		} else {
			$status_msg = 'failed';
		}

		echo json_encode([
			'status' => $status_msg,
			'redirect' => base_url('dashboard')
		]);
	}

	public function log_task()
	{
		$post = $this->input->post();

		// Extract fields
		$staff_id        = $post['staff_id'];
		$location        = $post['location'];
		$task_title      = $post['task_title'];
		$task_description= $post['task_description'];
		$project         = $post['project'];
		$priority_level  = $post['priority_level'];
		$start_time      = str_replace('T', ' ', $post['start_time']);
		$estimated_end_time = !empty($post['estimated_end_time']) ? str_replace('T', ' ', $post['estimated_end_time']) : null;
		$actual_end_time = !empty($post['actual_end_time']) ? str_replace('T', ' ', $post['actual_end_time']) : null;
		$task_status     = $post['task_status'];
		$progress_percent= $post['progress_percent'] ?? 0;
		$remarks         = $post['remarks'];
		$supervisor      = $post['supervisor'];
		$timestamp       = date('Y-m-d H:i:s');

		// Handle multiple file uploads
		$uploaded_files = [];
		if (!empty($_FILES['attachments']['name'][0])) {
			$filesCount = count($_FILES['attachments']['name']);
			for ($i = 0; $i < $filesCount; $i++) {
				$_FILES['file']['name']     = $_FILES['attachments']['name'][$i];
				$_FILES['file']['type']     = $_FILES['attachments']['type'][$i];
				$_FILES['file']['tmp_name'] = $_FILES['attachments']['tmp_name'][$i];
				$_FILES['file']['error']    = $_FILES['attachments']['error'][$i];
				$_FILES['file']['size']     = $_FILES['attachments']['size'][$i];

				$config['upload_path']      = './uploads/attachments/staff_tasks/';
				$config['allowed_types']    = 'jpg|jpeg|png|pdf|doc|docx|xls|xlsx';
				$config['max_size']         = 2048; // 2MB
				$config['encrypt_name']     = true;

				$this->load->library('upload', $config);
				$this->upload->initialize($config);

				if ($this->upload->do_upload('file')) {
					$uploadedData = $this->upload->data();
					$uploaded_files[] = $uploadedData['file_name'];
				} else {
					// You can handle upload errors here
					// $error = $this->upload->display_errors();
					// set_alert('error', $error);
					// return redirect('dashboard');
				}
			}
		}

		// Store uploaded filenames as JSON string (you can change to comma-separated if you want)
		$attachments_json = json_encode($uploaded_files);

		// Prepare data for insertion
		$insert_data = [
			'staff_id'          => $staff_id,
			'location'          => $location,
			'task_title'        => $task_title,
			'start_time'        => $start_time,
			'task_status'       => $task_status,
			'logged_at'         => $timestamp
		];

		$this->db->insert('staff_task_log', $insert_data);

		set_alert('success', translate('information_has_been_saved_successfully'));
		redirect($_SERVER['HTTP_REFERER']);
	}

		// Add this method to your Tasks controller
		public function update_task_log()
		{
			// Ensure it's an AJAX request
			if (!$this->input->is_ajax_request()) {
				echo json_encode(['success' => false, 'message' => 'Invalid request']);
				return;
			}

			// Get POST data
			$task_id        = $this->input->post('task_id');
			$task_status    = $this->input->post('task_status');
			$task_title     = $this->input->post('task_title');
			$location       = $this->input->post('location');
			$start_time_raw = $this->input->post('start_time');
			$end_time_raw   = $this->input->post('end_time');
			$reason   = $this->input->post('reason');
			$proof = $this->input->post('proof');

			// Validate required fields
			if (empty($task_id) || empty($task_status)) {
				echo json_encode(['success' => false, 'message' => 'Missing required fields']);
				return;
			}

			// Format dates
			$formatted_start_time = !empty($start_time_raw) ? date('Y-m-d H:i:s', strtotime($start_time_raw)) : null;
			$formatted_end_time   = !empty($end_time_raw) ? date('Y-m-d H:i:s', strtotime($end_time_raw)) : null;

			// Build update data
			$update_data = [
				'task_status'     => $task_status,
				'task_title'      => $task_title,
				'location'        => $location,
				'start_time'      => $formatted_start_time,
				'actual_end_time' => $formatted_end_time,
				'ended_at'        => $formatted_end_time, 
				'reason'		=> $reason,
				'proof' => $proof, // ✅ Save proof
			];

			// Remove nulls to prevent overwriting with null
			$update_data = array_filter($update_data, fn($v) => $v !== null);

			// Update the task
			$this->db->where('id', $task_id);
			$result = $this->db->update('staff_task_log', $update_data);

			// Return response
			if ($result) {
				echo json_encode(['success' => true]);
			} else {
				echo json_encode(['success' => false, 'message' => 'Database update failed']);
			}
		}


	private $allowed_roles = [1, 2, 3, 5];

	private function role_check() {
		$role = loggedin_role_id();
		return in_array($role, $this->allowed_roles);
	}

	public function get_user_notifications() {
		$user_id = get_loggedin_user_id();
		$role_id = loggedin_role_id();
		$limit = 10;

		if (in_array($role_id, [1, 2, 3, 5])) {
			// ✅ Role can see all notifications assigned to themselves
			$notifications = $this->db
				->order_by('created_at', 'DESC')
				->limit($limit)
				->get('notifications')
				->result();
		} else {
			// ✅ Others: same department users' notifications
			$my_department = $this->db
				->select('department')
				->where('id', $user_id)
				->get('staff')
				->row('department');

			$staff_ids = $this->db
				->select('id')
				->from('staff')
				->where('department', $my_department)
				->get()
				->result_array();

			$ids = array_column($staff_ids, 'id');

			if (!empty($ids)) {
				$this->db->where_in('user_id', $ids);
				$this->db->or_where('user_id', 0); // fallback
			} else {
				$this->db->where('user_id', $user_id); // fallback
				$this->db->or_where('user_id', 0); // fallback
			}

			$notifications = $this->db
				->order_by('created_at', 'DESC')
				->limit($limit)
				->get('notifications')
				->result();
		}

		echo json_encode($notifications);
	}

	public function mark_as_read($id) {
		$user_id = get_loggedin_user_id();
		$role_id = loggedin_role_id();

		$notification = $this->db->where('id', $id)->get('notifications')->row();

		if (!$notification) {
			show_error('Notification not found', 404);
		}

		$can_read = false;

		if ($role_id == 1 || $role_id == 2 || $role_id == 3 || $role_id == 5 || $notification->user_id == $user_id || $notification->user_id == 0) {
			$can_read = true;
		} elseif ($role_id == 8 || $role_id == 4) {
			$my_dept = $this->db->select('department')->from('staff')->where('id', $user_id)->get()->row('department');
			$target_dept = $this->db->select('department')->from('staff')->where('id', $notification->user_id)->get()->row('department');
			if ($my_dept == $target_dept) {
				$can_read = true;
			}
		}

		if (!$can_read) {
			show_error('Unauthorized to mark this notification as read', 403);
		}

		// ✅ Mark as read
		$this->db->where('id', $id)->update('notifications', ['is_read' => 1]);

		// ✅ Log the view (only once per staff-notification)
		$exists = $this->db->get_where('notification_views', [
			'notification_id' => $id,
			'staff_id' => $user_id
		])->num_rows();

		if ($exists == 0) {
			$this->db->insert('notification_views', [
				'notification_id' => $id,
				'staff_id' => $user_id,
				'viewed_at' => date('Y-m-d H:i:s')
			]);
		}

		echo json_encode(['status' => 'success']);
	}

	public function view_log($notification_id) {
		if (loggedin_role_id() != 1) show_error('Unauthorized', 403); // Only Super Admin

		$views = $this->db->select('staff.name, notification_views.viewed_at')
			->from('notification_views')
			->join('staff', 'staff.id = notification_views.staff_id')
			->where('notification_views.notification_id', $notification_id)
			->order_by('notification_views.viewed_at', 'desc')
			->get()->result();

		echo "<h3>Notification View Log</h3><table border='1' cellpadding='8'><tr><th>Staff</th><th>Viewed At</th></tr>";
		foreach ($views as $v) {
			echo "<tr><td>{$v->name}</td><td>{$v->viewed_at}</td></tr>";
		}
		echo "</table>";
	}

	public function notification()
{
    if (!get_permission('notification', 'is_view')) {
        access_denied();
    }

    $staffID = get_loggedin_user_id();
    $user_role = get_loggedin_user_type(); // e.g., 1 = Superadmin, 2 = Admin, etc.

    if ($_POST) {
        // Filter by selected month/year
        $timestamp = strtotime($this->input->post('timestamp'));

        $this->data['month'] = date('m', $timestamp);
        $this->data['year'] = date('Y', $timestamp);
        $this->data['days'] = date('t', strtotime("{$this->data['year']}-{$this->data['month']}-01"));

        $this->data['notifications'] = $this->dashboard_model->getFilteredNotifications(
            $staffID,
            $user_role,
            $this->data['month'],
            $this->data['year']
        );
        $this->data['filter_applied'] = true;
    } else {
        // Load last 7 days data by default
        $this->data['notifications'] = $this->dashboard_model->getRecentNotifications(
            $staffID,
            $user_role,
            7 // last 7 days
        );
        $this->data['filter_applied'] = false;
    }

    $this->data['title'] = translate('all_notifications');
    $this->data['sub_page'] = 'dashboard/notification';
    $this->data['main_menu'] = 'notification';
    $this->load->view('layout/index', $this->data);
}

public function activity_logs()
{
    if (!get_permission('activity_logs', 'is_view')) {
        access_denied();
    }

    $staffID = get_loggedin_user_id();
    $user_role = get_loggedin_user_type(); // e.g., 1 = Superadmin, 2 = Admin, etc.

    if ($_POST) {
        // Filter by selected month/year
        $timestamp = strtotime($this->input->post('timestamp'));

        $this->data['month'] = date('m', $timestamp);
        $this->data['year'] = date('Y', $timestamp);
        $this->data['days'] = date('t', strtotime("{$this->data['year']}-{$this->data['month']}-01"));

        $this->data['activity_logs'] = $this->dashboard_model->getFilteredTaskLogs(
            $staffID,
            $user_role,
            $this->data['month'],
            $this->data['year']
        );
        $this->data['filter_applied'] = true;
    } else {
        // Load last 7 days data by default
        $this->data['activity_logs'] = $this->dashboard_model->getRecentTaskLogs(
            $staffID,
            $user_role,
            7 // last 7 days
        );
        $this->data['filter_applied'] = false;
    }

    $this->data['title'] = translate('all_task_logs');
    $this->data['sub_page'] = 'dashboard/activity_logs';
    $this->data['main_menu'] = 'notification';
    $this->load->view('layout/index', $this->data);
}


public function activity_dashboard()
{
    // Get today's live data
    $today = date('Y-m-d');
    $limit=200;
    // Total tasks generated today
    $this->data['total_tasks_today'] = $this->dashboard_model->getTotalTasksToday();

    $this->data['milestone_task'] = $this->dashboard_model->getTasksTodayByCategory('Milestone');
    $this->data['incident_task'] = $this->dashboard_model->getTasksTodayByCategory('Incident');
    $this->data['customer_query_task'] = $this->dashboard_model->getTasksTodayByCategory('Customer Query');
    $this->data['explore_task'] = $this->dashboard_model->getTasksTodayByCategory('Explore');
    $this->data['request_task'] = $this->dashboard_model->getTasksTodayByCategory('EMP Request');

    // Contact main query from email_logs table
    $this->data['email_contacts_today'] = $this->dashboard_model->getEmailContactsToday();

    // Total meeting minutes or team meetings today
    $this->data['meeting_minutes_today'] = $this->dashboard_model->getMeetingMinutesToday();

    // Client meetings and support from tracker_issues
    $this->data['client_physical_meet'] = $this->dashboard_model->getClientPhysicalMeetToday();
    $this->data['client_online_meet'] = $this->dashboard_model->getClientOnlineMeetToday();
    $this->data['support_tasks'] = $this->dashboard_model->getSupportTasksToday();
    $this->data['billing_tasks'] = $this->dashboard_model->getBillingTasksToday();

    // Total activities and completion rate
    $this->data['total_activities_today'] = $this->dashboard_model->getTotalActivitiesToday();
    $this->data['completion_rate_today'] = $this->dashboard_model->getCompletionRateToday();

    $this->data['active_employee'] = $this->dashboard_model->getActiveEmployeesCount();
    $this->data['average_working_hour'] = $this->dashboard_model->getAverageWorkingHours();
    $this->data['attendance_rate_today'] = $this->dashboard_model->getAttendanceRate();
    $this->data['email_logs'] = $this->dashboard_model->getEmailLogs($limit);
    $this->data['cdr_logs'] = $this->dashboard_model->getCdrData($limit);
    $this->data['crm_activities'] = $this->dashboard_model->get_crm_activity($limit);
    $this->data['shipment_activities'] = $this->dashboard_model->getShipmentActivities();
    $this->data['shipment_completion_rate'] = $this->dashboard_model->getShipmentCompletionRate();

    // Get milestone-based data for ongoing tasks
    $this->data['milestone_data'] = $this->tasks_model->get_milestone_dashboard_data();

    $this->data['title'] = translate('activity_dashboard');
    $this->data['sub_page'] = 'dashboard/activity_dashboard';
    $this->data['main_menu'] = 'dashboard';
    $this->load->view('layout/index', $this->data);
}


	// AJAX endpoint to get live activity data
	public function get_live_activity_data()
	{
		header('Content-Type: application/json');

		$today = date('Y-m-d');

		$data = [
			'email_contacts_today' => $this->dashboard_model->getEmailContactsToday(),
			'meeting_minutes_today' => $this->dashboard_model->getMeetingMinutesToday(),
			'client_physical_meet' => $this->dashboard_model->getClientPhysicalMeetToday(),
			'client_online_meet' => $this->dashboard_model->getClientOnlineMeetToday(),
			'support_tasks' => $this->dashboard_model->getSupportTasksToday(),
			'total_activities_today' => $this->dashboard_model->getTotalActivitiesToday(),
			'completion_rate_today' => $this->dashboard_model->getCompletionRateToday(),
			'last_updated' => date('H:i:s')
		];

		echo json_encode($data);
	}

	public function work_summary()
{
	if (!get_permission('work_summary', 'is_view')) {
		access_denied();
	}

	if (isset($_POST['update'])) {
		if (!get_permission('work_summary', 'is_edit')) {
			access_denied();
		}

		$data = $this->input->post();
		$issued_by = get_loggedin_user_id();
		$id = $this->input->post('id');
		$comments = $this->input->post('comments');
		$rating = $this->input->post('overall_rating') ?? null;
		$approvals = $data['approval'] ?? [];

		// ✅ Fetch original row
		$summary = $this->db->get_where('daily_work_summaries', ['id' => $id])->row_array();

		if (!$summary) {
			set_alert('error', 'Summary not found.');
			redirect($_SERVER['HTTP_REFERER']);
		}

		$completed_tasks = json_decode($summary['completed_tasks'], true);
		foreach ($completed_tasks as $index => &$task) {
			$title = $task['title'] ?? '';
			foreach ($approvals as $item) {
				if ($item['title'] == $title) {
					$task['approval'] = $item['decision'];
					break;
				}
			}
		}
		unset($task); // break reference

		// ✅ Update the summary
		$this->db->where('id', $id);
		$this->db->update('daily_work_summaries', [
			'approved_by'     => $issued_by,
			'status'          => 2, // Mark as approved
			'comments'        => $comments,
			'rating'          => $rating,
			'completed_tasks' => json_encode($completed_tasks),
		]);

		// 🔔 Send approval notification
		if (!empty($summary)) {
			$staff    = $this->db->get_where('staff', ['id' => $summary['user_id']])->row();
			$approver = $this->db->get_where('staff', ['id' => $issued_by])->row();

			$staff_name    = $staff ? $staff->name : 'Employee';
			$approver_name = $approver ? $approver->name : 'Admin';

			$message = "Hi {$staff_name}, your work summary for {$summary['summary_date']} has been reviewed by {$approver_name}. ";
			$message .= "Overall rating: {$rating}.";
			if (!empty($comments)) {
				$message .= " Comments: {$comments}";
			}

			$this->db->insert('notifications', [
				'user_id'    => $summary['user_id'],
				'type'       => 'work_summary_reviewed',
				'title'      => 'Work Summary Reviewed',
				'message'    => $message,
				'url'        => base_url('dashboard/work_summary'),
				'is_read'    => 0,
				'created_at' => date('Y-m-d H:i:s')
			]);

			// Send FCM notification to the staff member
			$fcm_tokens = $this->db->select('fcm_token')
			                       ->where('id', $summary['user_id'])
			                       ->where('fcm_token IS NOT NULL')
			                       ->where('fcm_token !=', '')
			                       ->get('staff')
			                       ->result_array();

			if (!empty($fcm_tokens)) {
			    $tokens = array_column($fcm_tokens, 'fcm_token');
			    $this->send_fcm_notification(
			        'Work Summary Reviewed',
			        "Your work summary has been reviewed by {$approver_name}. Rating: {$rating}",
			        '',
			        $tokens,
			        [
			            'type' => 'work_summary_reviewed',
			            'summary_id' => (string)$id,
			            'rating' => (string)$rating,
			            'action' => 'view'
			        ]
			    );
			}

			// Send FCM notification to the staff member
			$fcm_tokens = $this->db->select('fcm_token')
			                       ->where('id', $summary['user_id'])
			                       ->where('fcm_token IS NOT NULL')
			                       ->where('fcm_token !=', '')
			                       ->get('staff')
			                       ->result_array();

			if (!empty($fcm_tokens)) {
			    $tokens = array_column($fcm_tokens, 'fcm_token');
			    $this->send_fcm_notification(
			        'Work Summary Reviewed',
			        "Your work summary has been reviewed by {$approver_name}. Rating: {$rating}",
			        '',
			        $tokens,
			        [
			            'type' => 'work_summary_reviewed',
			            'summary_id' => (string)$id,
			            'rating' => (string)$rating,
			            'action' => 'view'
			        ]
			    );
			}
		}

		set_alert('success', translate('information_has_been_updated_successfully'));
		redirect($_SERVER['HTTP_REFERER']);
	}

	// 📆 Filter by date - Load only first 50 records
	if ($this->input->post('search')) {
		$daterange = explode(' - ', $this->input->post('daterange'));
		$start = date("Y-m-d", strtotime($daterange[0]));
		$end = date("Y-m-d", strtotime($daterange[1]));
		$this->data['work_summaries'] = $this->dashboard_model->getWorkSummariesPaginated($start, $end, '', null, 100, 0);
	} else {
		$this->data['work_summaries'] = $this->dashboard_model->getWorkSummariesPaginated(null, null, '', null, 100, 0);
	}

	// 🖼️ View
	$this->data['main_menu'] = 'notification';
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

	$this->data['title'] = translate('work_summaries');
	$this->data['sub_page'] = 'dashboard/work_summary';
	$this->load->view('layout/index', $this->data);
}



	public function work_summary_delete($id = '')
{
	if (get_permission('work_summary', 'is_delete')) {
		$this->db->where('id', $id);
		$this->db->delete('daily_work_summaries');
	}
}

public function create_warning($staff_id) {
	if (!get_permission('warning', 'is_add')) {
		access_denied();
	}
	$data['staff_id'] = $staff_id;
	$data['staff_info'] = $this->db->get_where('staff', ['id' => $staff_id])->row();
	$this->load->view('dashboard/warning_form', $data); // Your form view
}

public function save_warning() {
	if (!get_permission('warning', 'is_add')) {
		access_denied();
	}

	$staff_id = $this->input->post('staff_id');
	$reason = $this->input->post('reason');

	$this->db->insert('warnings', [
		'staff_id' => $staff_id,
		'reason'   => $reason,
		'issued_by'=> get_loggedin_user_id(),
		'status'   => 1,
		'created_at' => date('Y-m-d H:i:s')
	]);

	set_alert('success', 'Warning issued successfully.');
	redirect('dashboard/work_summary');
}

    // get add leave modal
    public function getApprovelWorkDetails()
	{
		if (get_permission('work_summary', 'is_view')) {
			$this->data['work_id'] = $this->input->post('id');
			$this->load->view('dashboard/approvel_modalView', $this->data);
		}
	}


private $bot_token = '';
private $chat_id = '';

public function submit_work_summary()
{
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') show_404();

	$user_id = get_loggedin_user_id();
	$today = date('Y-m-d');

	$exists = $this->db->where('user_id', $user_id)->where('summary_date', $today)->count_all_results('daily_work_summaries');
	if ($exists > 0) {
		set_alert('error', 'You have already submitted your daily summary for today.');
		redirect($_SERVER['HTTP_REFERER']);
		return;
	}

	$data = $this->input->post();

	$assigned_tasks = [];
	$planner_checkboxes = $data['assigned_tasks_planner'] ?? [];

	// Debug: Log the received data
	log_message('debug', 'Planner checkboxes received: ' . json_encode($planner_checkboxes));
	log_message('debug', 'Task titles received: ' . json_encode($data['assigned_tasks_title'] ?? []));

	$task_index = 0; // Track actual task index (excluding empty titles)
	for ($i = 0; $i < count($data['assigned_tasks_title'] ?? []); $i++) {
		if (!empty($data['assigned_tasks_title'][$i])) {
			// Get planner status from the corresponding index in the planner array
			$is_planner = isset($planner_checkboxes[$task_index]) && $planner_checkboxes[$task_index] == '1' ? 1 : 0;
			$assigned_tasks[] = [
				'title' => trim($data['assigned_tasks_title'][$i]),
				'link' => trim($data['assigned_tasks_link'][$i] ?? ''),
				'planner' => $is_planner
			];
			log_message('debug', "Task {$task_index}: {$data['assigned_tasks_title'][$i]} - Planner: {$is_planner}");
			$task_index++; // Only increment for non-empty tasks
		}
	}

	// Debug: Log the final assigned tasks
	log_message('debug', 'Final assigned tasks: ' . json_encode($assigned_tasks));

	$completed_tasks = [];
	$total_time = 0;
	for ($i = 0; $i < count($data['completed_tasks_title'] ?? []); $i++) {
		if (!empty($data['completed_tasks_title'][$i])) {
			$hours = floatval($data['completed_tasks_time'][$i] ?? 0);
			$total_time += $hours;
			$completed_tasks[] = [
				'task_id' => $i,
				'title' => trim($data['completed_tasks_title'][$i]),
				'link' => trim($data['completed_tasks_link'][$i] ?? ''),
				'time' => $hours,
				'status' => 'pending'
			];
		}
	}

	$summary = [
		'user_id' => $user_id,
		'summary_date' => $today,
		'name' => trim($data['name'] ?? 'Unknown'),
		'department' => trim($data['department'] ?? 'Unknown'),
		'assigned_tasks' => json_encode($assigned_tasks),
		'completed_tasks' => json_encode($completed_tasks),
		'completion_ratio' => floatval($data['completion_ratio'] ?? 0),
		'total_time_spent' => round($total_time, 2),
		'blockers' => trim($data['blockers'] ?? ''),
		'next_steps' => trim($data['next_steps'] ?? ''),
		'status' => 1
	];

	$this->db->insert('daily_work_summaries', $summary);
	$summary_id = $this->db->insert_id();

	// 🔔 Notification (internal)
	$staff = $this->db->select('name')->where('id', $user_id)->get('staff')->row_array();
	$staff_name = $staff['name'] ?? 'Staff';

	$notification = [
		'user_id'    => $user_id,
		'type'       => 'daily_work_summary',
		'title'      => 'Daily Work Summary Submitted',
		'message'    => "{$staff_name} has submitted the daily work summary for {$today}.",
		'url'        => base_url('dashboard/work_summary'),
		'is_read'    => 0,
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

	// Get approver tokens
	$recipientTokens = $this->get_fund_approver_tokens(
	    $staff_details ? $staff_details->department : null,
	    [1, 2, 3, 5],
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

	// Prepare message
	$message = "📅 *Date:* {$today}\n" .
		"👤 *Name:* {$staff_name}\n" .
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

	$message .= "\n📊 *Completion Ratio:* {$summary['completion_ratio']}%\n" .
		"⏱️ *Completed Task Total Spent Time:* {$summary['total_time_spent']} hrs\n";

	if (!empty($summary['blockers'])) {
		$message .= "\n🚫 *Blockers:*\n{$summary['blockers']}\n";
	}
	if (!empty($summary['next_steps'])) {
		$message .= "\n➡ *Next Steps:*\n{$summary['next_steps']}\n";
	}

	$message .= "\n🔗 [View Summary](" . base_url('dashboard/work_summary') . ")";

	// Send interactive Telegram message
	$this->send_interactive_summary_to_telegram($message, $summary_id);

	set_alert('success', 'Daily summary submitted successfully.');
	redirect($_SERVER['HTTP_REFERER']);
}


	// ✅ Send summary message with inline button
	private function send_interactive_summary_to_telegram($text, $summary_id)
	{
		$keyboard = [
			'inline_keyboard' => [
				[
					['text' => '💬 Reply', 'callback_data' => "reply_summary_{$summary_id}"]
				]
			]
		];

		$payload = [
			'chat_id' => $this->chat_id,
			'text' => $text,
			'parse_mode' => 'Markdown',
			'disable_web_page_preview' => true,
			'reply_markup' => json_encode($keyboard)
		];

		$url = "https://api.telegram.org/bot{$this->bot_token}/sendMessage";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		$response = curl_exec($ch);
		curl_close($ch);

		log_message('debug', 'Telegram summary sent: ' . $response);
	}

public function get_staff_tasks() {
    $staff_id = get_loggedin_user_id();
    $date = $this->input->get('date') ?: date('Y-m-d');

    $this->db->where('staff_id', $staff_id);
    $this->db->where('DATE(logged_at)', $date);
    $tasks = $this->db->get('staff_task_log')->result();

    $assigned = [];
    $completed = [];

    foreach($tasks as $task) {
        $data = [
            'id' => $task->id,
            'title' => html_escape($task->task_title),
            'link' => $task->proof ?? '',
            'planner' => 0, // Default to not in planner since staff_task_log doesn't track this
			'time_spent' => (strtolower($task->task_status) === 'completed') ?
                round((strtotime($task->ended_at) - strtotime($task->start_time)) / 3600, 2) : 0
        ];

        // All tasks go to assigned
        $assigned[] = $data;

        // Only completed tasks also go to completed
		if (isset($task->task_status) && strtolower($task->task_status) === 'completed') {
			$completed[] = $data;
		}
    }

    echo json_encode(['assigned' => $assigned, 'completed' => $completed]);
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

	// Get unique departments for filter buttons
	public function get_departments()
	{
		$this->db->select('DISTINCT(department) as department');
		$this->db->from('daily_work_summaries');
		$this->db->where('department IS NOT NULL');
		$this->db->where('department !=', '');
		$this->db->order_by('department', 'ASC');
		$result = $this->db->get()->result_array();

		$departments = array();
		foreach ($result as $row) {
			if (!empty(trim($row['department']))) {
				$departments[] = trim($row['department']);
			}
		}

		header('Content-Type: application/json');
		echo json_encode($departments);
	}

	// Filter work summaries by department with pagination
	public function filter_work_summaries()
	{
		$department = $this->input->post('department');
		$daterange = $this->input->post('daterange');
		$page = (int)$this->input->post('page') ?: 1;
		$limit = (int)$this->input->post('limit') ?: 200;

		$start = null;
		$end = null;

		// Parse date range if provided
		if (!empty($daterange)) {
			$dates = explode(' - ', $daterange);
			if (count($dates) == 2) {
				$start = date("Y-m-d", strtotime($dates[0]));
				$end = date("Y-m-d", strtotime($dates[1]));
			}
		}

		// Get total count for pagination
		$total_records = $this->dashboard_model->getWorkSummariesCount($start, $end, '', $department);
		$total_pages = ceil($total_records / $limit);
		$offset = ($page - 1) * $limit;

		// Get paginated work summaries
		$work_summaries = $this->dashboard_model->getWorkSummariesPaginated($start, $end, '', $department, $limit, $offset);

		$response = [
			'data' => $work_summaries,
			'pagination' => [
				'current_page' => $page,
				'total_pages' => $total_pages,
				'total_records' => $total_records,
				'per_page' => $limit
			]
		];

		header('Content-Type: application/json');
		echo json_encode($response);
	}

	// AJAX endpoint for work summary approval
	public function work_summary_ajax()
	{
		header('Content-Type: application/json');

		if (!get_permission('work_summary', 'is_edit')) {
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}

		$data = $this->input->post();
		$issued_by = get_loggedin_user_id();
		$id = $this->input->post('id');
		$comments = $this->input->post('comments');
		$rating = $this->input->post('overall_rating') ?? null;
		$status = $this->input->post('status') ?? 2; // Default to approved if not set
		$approvals = $data['approval'] ?? [];

		// Fetch original row
		$summary = $this->db->get_where('daily_work_summaries', ['id' => $id])->row_array();

		if (!$summary) {
			echo json_encode(['status' => 'error', 'message' => 'Summary not found']);
			return;
		}

		$completed_tasks = json_decode($summary['completed_tasks'], true);
		foreach ($completed_tasks as $index => &$task) {
			$title = $task['title'] ?? '';
			foreach ($approvals as $item) {
				if ($item['title'] == $title) {
					$task['approval'] = $item['decision'];
					break;
				}
			}
		}
		unset($task);

		// Update the summary
		$this->db->where('id', $id);
		$result = $this->db->update('daily_work_summaries', [
			'approved_by'     => $issued_by,
			'status'          => $status,
			'comments'        => $comments,
			'rating'          => $rating,
			'completed_tasks' => json_encode($completed_tasks),
		]);

		if ($result) {
			// Process declined tasks to update tracker status
			foreach ($completed_tasks as $task) {
				if (isset($task['approval']) && $task['approval'] === 'declined') {
					$this->update_declined_task_status(
						$task['link'] ?? '',
						$task['title'] ?? '',
						$summary['user_id'],
						$issued_by,
						$summary['summary_date']
					);
				}
			}

			echo json_encode([
				'status' => 'success',
				'message' => 'Work summary updated successfully',
				'work_summary_status' => $status
			]);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Failed to update work summary']);
		}
	}

	// Delete activity log
	public function delete_activity_log($id = '')
	{
		if (get_permission('dashboard', 'is_delete')) {
			$this->db->where('id', $id);
			$this->db->delete('staff_task_log');
		}
		redirect($_SERVER['HTTP_REFERER']);
	}

	// Get tracker tasks for modal
	public function get_tracker_tasks()
	{
		$task_type = $this->input->post('task_type');
		$category = $this->input->post('category');
		$today = date('Y-m-d');

		$this->db->select('ti.*, tt.name as task_type_name, s.name as assigned_to_name');
		$this->db->from('tracker_issues ti');
		$this->db->join('task_types tt', 'tt.id = ti.task_type', 'left');
		$this->db->join('staff s', 'FIND_IN_SET(s.id, ti.assigned_to)', 'left');
		$this->db->where('DATE(ti.logged_at)', $today);

		// Filter by category if provided
		if (!empty($category)) {
			$this->db->where('ti.category', $category);
		} else {
			// Handle other task types for backward compatibility
			switch ($task_type) {
				case 'support':
					$this->db->like('tt.name', 'Support', 'both');
					break;
				case 'billing':
					$this->db->group_start();
					$this->db->like('tt.name', 'Billing', 'both');
					$this->db->or_like('tt.name', 'Payment', 'both');
					$this->db->group_end();
					break;
				case 'physical_meeting':
					$this->db->like('tt.name', 'Physical Meeting', 'both');
					break;
				case 'online_meeting':
					$this->db->like('tt.name', 'Online Meeting', 'both');
					break;
				case 'meeting':
					$this->db->group_start();
					$this->db->like('tt.name', 'Meeting', 'both');
					$this->db->group_end();
					break;
				case 'email':
					$this->db->group_start();
					$this->db->like('tt.name', 'Email', 'both');
					$this->db->or_like('tt.name', 'Contact', 'both');
					$this->db->group_end();
					break;
				case 'all':
				default:
					// No additional filter for all tasks
					break;
			}
		}

		$this->db->order_by('ti.logged_at', 'DESC');
		$this->db->limit(50);
		$tasks = $this->db->get()->result_array();

		echo json_encode([
			'status' => 'success',
			'tasks' => $tasks
		]);
	}

	// Get shipments for modal
	public function get_shipments()
	{
		$shipment_status = $this->input->post('shipment_status');

		try {
			$this->db->select('*');
			$this->db->from('shipments');

			if ($shipment_status && $shipment_status !== 'all') {
				$this->db->where('status', $shipment_status);
			}

			$this->db->where('status !=', 'cancelled');
			$this->db->order_by('created_at', 'DESC');
			$this->db->limit(50);
			$shipments = $this->db->get()->result_array();

			echo json_encode([
				'status' => 'success',
				'shipments' => $shipments
			]);
		} catch (Exception $e) {
			echo json_encode([
				'status' => 'error',
				'message' => 'Error loading shipments'
			]);
		}
	}

	//Get Tracker Issue description
	 public function viewTracker_Issue()
    {
		$this->data['task_id'] = $this->input->post('id');
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

		$this->load->view('dashboard/view_tracker_issue_details', $this->data);
    }

	// Get comments for a specific task
	public function get_task_comments()
	{
		$task_id = $this->input->post('task_id');

		if (empty($task_id)) {
			echo json_encode(['status' => 'error', 'message' => 'Task ID required']);
			return;
		}

	$comments = $this->db->select("
        tc.*,
        CASE
            WHEN tc.author_id = 1 THEN 'System'
            ELSE s.name
        END as author_name
    ")
    ->from('tracker_comments tc')
    ->join('staff s', 'tc.author_id = s.id', 'left')
    ->where('tc.task_id', $task_id)
    ->order_by('tc.created_at', 'DESC')
    ->get()
    ->result();


		echo json_encode(['status' => 'success', 'comments' => $comments]);
	}

	// Add comment to a task
	public function add_task_comment()
	{
		$task_id = $this->input->post('task_id');
		$comment_text = $this->input->post('comment_text');

		if (empty($task_id) || empty($comment_text)) {
			echo json_encode(['status' => 'error', 'message' => 'Task ID and comment required']);
			return;
		}

		// Convert @[id] back to @name for display while keeping @[id] for processing
		$display_comment = $this->convert_mentions_for_display($comment_text);

		$data = [
			'task_id' => $task_id,
			'comment_text' => $display_comment,
			'author_id' => get_loggedin_user_id(),
			'created_at' => date('Y-m-d H:i:s')
		];

		if ($this->db->insert('tracker_comments', $data)) {
			// Handle mentions
			$mentioned_users = $this->extract_mentions($comment_text);
			if (!empty($mentioned_users)) {
				// Get task details
				$task = $this->db->select('unique_id, task_title')->where('id', $task_id)->get('tracker_issues')->row();
				if ($task) {
					$this->create_mention_notifications($mentioned_users, $task->unique_id, $task->task_title, get_loggedin_user_id(), $comment_text);
				}
			}

			// Get the inserted comment with author name
			$comment_id = $this->db->insert_id();
			$comment = $this->db->select('tc.*, s.name as author_name')
				->from('tracker_comments tc')
				->join('staff s', 'tc.author_id = s.id', 'left')
				->where('tc.id', $comment_id)
				->get()->row();

			echo json_encode(['status' => 'success', 'comment' => $comment]);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Failed to add comment']);
		}
	}

	// Edit comment
	public function edit_task_comment()
	{
		$comment_id = $this->input->post('comment_id');
		$comment_text = $this->input->post('comment_text');
		$user_id = get_loggedin_user_id();

		if (empty($comment_id) || empty($comment_text)) {
			echo json_encode(['status' => 'error', 'message' => 'Comment ID and text required']);
			return;
		}

		// Convert @[id] back to @name for display while keeping @[id] for processing
		$display_comment = $this->convert_mentions_for_display($comment_text);

		// Update only if user owns the comment
		$this->db->where('id', $comment_id);
		$this->db->where('author_id', $user_id);
		$result = $this->db->update('tracker_comments', ['comment_text' => $display_comment]);

		if ($result && $this->db->affected_rows() > 0) {
			// Handle mentions in edited comment
			$mentioned_users = $this->extract_mentions($comment_text);
			if (!empty($mentioned_users)) {
				// Get task details from comment
				$comment = $this->db->select('tc.task_id')->from('tracker_comments tc')->where('tc.id', $comment_id)->get()->row();
				if ($comment) {
					$task = $this->db->select('unique_id, task_title')->where('id', $comment->task_id)->get('tracker_issues')->row();
					if ($task) {
						$this->create_mention_notifications($mentioned_users, $task->unique_id, $task->task_title, get_loggedin_user_id(), $comment_text);
					}
				}
			}

			echo json_encode(['status' => 'success']);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Failed to update comment or access denied']);
		}
	}

	// Delete comment
	public function delete_task_comment()
	{
		$comment_id = $this->input->post('comment_id');
		$user_id = get_loggedin_user_id();

		if (empty($comment_id)) {
			echo json_encode(['status' => 'error', 'message' => 'Comment ID required']);
			return;
		}

		// Delete only if user owns the comment
		$this->db->where('id', $comment_id);
		$this->db->where('author_id', $user_id);
		$result = $this->db->delete('tracker_comments');

		if ($result && $this->db->affected_rows() > 0) {
			echo json_encode(['status' => 'success']);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Failed to delete comment or access denied']);
		}
	}

	public function get_mention_users() {
		if (!$this->input->server('HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest') {
			show_404();
		}

		$search = $this->input->get('search');

		$this->db->select('staff.id, staff.name, staff.photo');
		$this->db->from('staff');
		$this->db->join('login_credential', 'login_credential.user_id = staff.id');
		$this->db->where('staff.id !=', 1);
		$this->db->where('login_credential.active', 1);
		$this->db->where('login_credential.role !=', 9);

		if (!empty($search)) {
			$this->db->like('staff.name', $search);
		}

		$this->db->limit(10);
		$users = $this->db->get()->result();


		$formatted_users = [];
		foreach ($users as $user) {
			$formatted_users[] = [
				'id' => $user->id,
				'name' => $user->name,
				'photo' => $user->photo ? get_image_url('staff', $user->photo) : null
			];
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($formatted_users));
	}

	private function extract_mentions($comment_text) {
		preg_match_all('/@\[(\d+)\]/', $comment_text, $matches);
		return array_unique($matches[1]);
	}

	private function create_mention_notifications($mentioned_users, $task_unique_id, $task_title, $author_id, $comment_text) {
		$author = $this->db->select('name')->where('id', $author_id)->get('staff')->row();
		$author_name = $author ? $author->name : 'Someone';

		foreach ($mentioned_users as $user_id) {
			if ($user_id == $author_id) continue;

			$user = $this->db->select('name, telegram_id')->where('id', $user_id)->get('staff')->row();

			$notification_data = [
				'user_id' => $user_id,
				'type' => 'mention',
				'title' => 'You were mentioned in a dashboard comment',
				'message' => $author_name . ' mentioned you in a dashboard comment on task ' . $task_unique_id . ': ' . $task_title,
				'url' => base_url('dashboard/nhd_dashboard'),
				'is_read' => 0,
				'created_at' => date('Y-m-d H:i:s')
			];

			$this->db->insert('notifications', $notification_data);

			// Send Telegram notification if user has telegram_id
			if ($user && !empty($user->telegram_id)) {
				$this->send_telegram_notification($user->telegram_id, $user->name, $author_name, $task_title, $task_unique_id, $comment_text);
			}
		}
	}

	private function convert_mentions_for_display($comment_text) {
		// Convert @[id] to @name for display
		return preg_replace_callback('/@\[(\d+)\]/', function($matches) {
			$user_id = $matches[1];
			$user = $this->db->select('name')->where('id', $user_id)->get('staff')->row();
			return $user ? '@' . $user->name : $matches[0];
		}, $comment_text);
	}


	public function update_summary_date()
	{
		// Only superadmin (role 1) can edit summary dates
		if (loggedin_role_id() != 1) {
			echo json_encode(['success' => false, 'message' => 'Access denied. Only superadmin can edit summary dates.']);
			return;
		}

		$summary_id = $this->input->post('summary_id');
		$summary_date = $this->input->post('summary_date');

		if (empty($summary_id) || empty($summary_date)) {
			echo json_encode(['success' => false, 'message' => 'Summary ID and date are required.']);
			return;
		}

		// Validate date format
		if (!DateTime::createFromFormat('Y-m-d', $summary_date)) {
			echo json_encode(['success' => false, 'message' => 'Invalid date format.']);
			return;
		}

		// Update the summary date
		$this->db->where('id', $summary_id);
		$result = $this->db->update('daily_work_summaries', ['summary_date' => $summary_date]);

		if ($result) {
			echo json_encode(['success' => true, 'message' => 'Summary date updated successfully.']);
		} else {
			echo json_encode(['success' => false, 'message' => 'Failed to update summary date.']);
		}
	}

	private function send_telegram_notification($chat_id, $staff_name, $author_name, $task_title, $task_unique_id, $comment_text) {
		$bot_token = $telegram_bot;
		$today = date('d M Y');
		// Convert @[id] to @name for Telegram display
		$display_comment = $this->convert_mentions_for_display($comment_text);
		$clean_comment = strip_tags($display_comment);
		$tg_message = "🛎️ *Dashboard Mention Notification*\n\n" .
			"📅 *Date:* {$today}\n" .
			"👤 *Mentioned:* {$staff_name}\n" .
			"👨💼 *By:* {$author_name}\n\n" .
			"📌 *Task:* {$task_unique_id} - {$task_title}\n" .
			"💬 *Comment:* {$clean_comment}\n\n" .
			"🔗 [Open Dashboard](" . base_url('dashboard/nhd_dashboard') . ")";

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
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
		curl_exec($ch);
		curl_close($ch);
	}

	/**
	 * Update tracker task status to TODO when declined in work summary
	 */
	private function update_declined_task_status($task_link, $task_title, $employee_id, $reviewer_id, $summary_date) {
		// Extract unique_id from link
		$unique_id = $this->extract_tracker_id($task_link);

		if (empty($unique_id)) {
			return false; // Not a tracker task
		}

		// Get task from tracker_issues
		$task = $this->db->select('id, task_status, assigned_to')
						 ->where('unique_id', $unique_id)
						 ->get('tracker_issues')
						 ->row();

		if (!$task) {
			return false; // Task not found
		}

		// Verify task belongs to employee
		if ($task->assigned_to != $employee_id) {
			return false; // Security check
		}

		// Only update if not already todo
		if ($task->task_status === 'todo') {
			return true; // Already in correct status
		}

		// Update task status to todo
		$this->db->where('id', $task->id)
				 ->update('tracker_issues', ['task_status' => 'todo']);

		// Add automatic comment with enhanced details
		$this->add_decline_comment($task->id, $reviewer_id, $summary_date);

		// Send notification to employee
		$this->send_task_decline_notification($employee_id, $unique_id, $task_title, $reviewer_id);

		return true;
	}

	/**
	 * Extract tracker unique_id from various link formats
	 */
	private function extract_tracker_id($link) {
		if (empty($link)) {
			return null;
		}

		// Pattern 1: Direct unique_id (e.g., "EMP-123")
		if (preg_match('/^[A-Z]+-\d+$/', $link)) {
			return $link;
		}

		// Pattern 2: URL containing unique_id
		if (preg_match('/([A-Z]+-\d+)/', $link, $matches)) {
			return $matches[1];
		}

		return null;
	}

	/**
	 * Add automatic comment to tracker task with enhanced details
	 */
	private function add_decline_comment($task_id, $reviewer_id, $summary_date) {
		$reviewer = $this->db->select('name')
						   ->where('id', $reviewer_id)
						   ->get('staff')
						   ->row();

		$reviewer_name = $reviewer ? $reviewer->name : 'Supervisor';

		$comment_text = sprintf(
			"This task was declined by %s from work summary of %s. Status changed to TODO for rework.",
			$reviewer_name,
			$summary_date
		);

		$this->db->insert('tracker_comments', [
			'task_id'      => $task_id,
			'comment_text' => $comment_text,
			'author_id'    => 1, // System user
			'created_at'   => date('Y-m-d H:i:s')
		]);
	}

	/**
	 * Send notification about task decline
	 */
	private function send_task_decline_notification($employee_id, $unique_id, $task_title, $reviewer_id) {
		$reviewer = $this->db->select('name')
						   ->where('id', $reviewer_id)
						   ->get('staff')
						   ->row();

		$reviewer_name = $reviewer ? $reviewer->name : 'Supervisor';

		$notification_data = [
			'user_id'    => $employee_id,
			'type'       => 'task_declined',
			'title'      => 'Task Declined - Rework Required',
			'message'    => sprintf(
				"%s declined your task '%s' (%s) in work summary review. Status changed to TODO.",
				$reviewer_name,
				$task_title,
				$unique_id
			),
			'url'        => base_url('tracker/my_issues'),
			'is_read'    => 0,
			'created_at' => date('Y-m-d H:i:s')
		];

		$this->db->insert('notifications', $notification_data);
	}

	/**
	 * Create todo for late tracker task
	 */
	public function add_late_remark() {
		header('Content-Type: application/json');

		$unique_id = $this->input->post('unique_id');
		$author_id = get_loggedin_user_id();

		if (empty($unique_id)) {
			echo json_encode(['status' => 'error', 'message' => 'Task ID required']);
			return;
		}

		// Get task from tracker_issues
		$task = $this->db->select('id, task_title, assigned_to')
						 ->where('unique_id', $unique_id)
						 ->get('tracker_issues')
						 ->row();

		if (!$task) {
			echo json_encode(['status' => 'error', 'message' => 'Task not found']);
			return;
		}

		// Get employee's department and find manager (role 8)
		$employee = $this->db->select('department')->where('id', $task->assigned_to)->get('staff')->row();
		$manager_id = null;
		if ($employee) {
			$manager = $this->db->select('staff.id')
							 ->from('staff')
							 ->join('login_credential', 'login_credential.user_id = staff.id')
							 ->where('staff.department', $employee->department)
							 ->where('login_credential.role', 8)
							 ->where('login_credential.active', 1)
							 ->get()
							 ->row();
			$manager_id = $manager ? $manager->id : null;
		}

		// Create todo (warning) for late submission
		$todo_data = [
			'user_id' => $task->assigned_to,
			'manager_id' => $manager_id,
			'role_id' => 4,
			'branch_id' => 1,
			'session_id' => get_session_id(),
			'clearance_time' => 24,
			'reason' => "Late submission explanation required for task: {$task->task_title} ({$unique_id}).",
			'reference' => "Late Submission - {$unique_id}",
			'category' => 'Late Submission',
			'effect' => 'Task Completion',
			'status' => 1,
			'manager_review' => $manager_id ? 1 : 0,
			'issued_by' => $author_id,
			'issue_date' => date('Y-m-d H:i:s')
		];

		$result = $this->db->insert('warnings', $todo_data);

		if ($result) {
			$this->db->insert('notifications', [
				'user_id' => $task->assigned_to,
				'type' => 'todo',
				'title' => 'Late Submission Todo Created',
				'message' => "A todo has been created for your late task submission: {$unique_id}",
				'url' => base_url('todo'),
				'is_read' => 0,
				'created_at' => date('Y-m-d H:i:s')
			]);

			echo json_encode(['status' => 'success']);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Failed to create todo']);
		}
	}
}