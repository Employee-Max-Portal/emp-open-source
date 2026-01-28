<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Attendance extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('attendance_model');
        if (!moduleIsEnabled('attendance')) {
            access_denied();
        }
        $getAttendanceType = $this->app_lib->getAttendanceType();
        if ($getAttendanceType != 2 && $getAttendanceType != 0) {
            access_denied();
        }
    }

    public function index()
    {
        if (get_loggedin_id()) {
            redirect(base_url('dashboard'), 'refresh');
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    public function getWeekendsHolidays()
    {
        if (!get_permission('student_attendance', 'is_add')) {
            ajax_access_denied();
        }
        if ($_POST) {
            $branchID = $this->input->post('branch_id');
            $getWeekends = $this->application_model->getWeekends($branchID);
            $getHolidays = $this->attendance_model->getHolidays($branchID);
            echo json_encode(['getWeekends' => $getWeekends, 'getHolidays' => '["' . $getHolidays . '"]']);
        }
    }

   public function employees_entry()
{
    if (!get_permission('employee_attendance', 'is_add')) {
        access_denied();
    }

    $branchID = $this->application_model->get_branch_id();

    // Search Action
    if (isset($_POST['search'])) {
        if (in_array(loggedin_role_id(), [1, 2, 3, 5])) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }
        $this->form_validation->set_rules('date', translate('date'), 'trim|required|callback_check_weekendday|callback_check_holiday|callback_get_valid_date');
        if ($this->form_validation->run() == true) {
            $date = $this->input->post('date');
            $this->data['date'] = $date;

          // ✅ Only get manual attendance
			$this->db->select('sa.id as atten_id, sa.staff_id, sa.ip_address, sa.status as att_status, sa.remark as att_remark, sa.in_time, s.name, s.staff_id as staff_code, s.photo, s.id')
				->from('staff_attendance sa')
				->join('staff s', 's.id = sa.staff_id')
				->where('sa.date', $date)
				->where('sa.is_manual', 1)
				//->or_where('sa.ip_address !=', '103.180.244.161')
				->order_by('s.staff_id', 'ASC');

			// ✅ Conditionally apply branch filter
			if (!empty($branchID) && $branchID !== 'all') {
				$this->db->where('sa.branch_id', $branchID);
			}

			$this->data['attendencelist'] = $this->db->get()->result_array();

        }
    }

    // Save Action
    if (isset($_POST['save'])) {
        $attendance = $this->input->post('attendance');
        $date = $this->input->post('date');
      /*   $current_time = date('H:i:s');
        $time_threshold = '10:30:59'; */

        foreach ($attendance as $key => $value) {
            /* $attStatus = isset($value['status']) ? $value['status'] : '';
            if (strtotime($current_time) > strtotime($time_threshold) && $attStatus == 'P') {
                $attStatus = 'L';
            } */
			$attStatus = (isset($value['status']) ? $value['status'] : "");
			$updateData = [
				'status' => $attStatus,
				'remark' => $value['remark'],
				'is_manual' => 0, // ✅ Mark as officially reviewed
				'approval_status' => 'approved', // ✅ Optional: auto-approve if part of approval flow
			];

            $this->db->where('id', $value['attendance_id']);
            $this->db->update('staff_attendance', $updateData);
        }

        set_alert('success', translate('information_has_been_updated_successfully'));
        redirect(current_url());
    }

    $this->data['getWeekends'] = $this->application_model->getWeekends($branchID);
    $this->data['branch_id'] = $branchID;
    $this->data['title'] = translate('employee_attendance');
    $this->data['sub_page'] = 'attendance/employees_entries';
    $this->data['main_menu'] = 'attendance';
    $this->load->view('layout/index', $this->data);
}

  public function attendance_approval()
{
    if (!get_permission('attendance_approval', 'is_add')) {
        access_denied();
    }

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

	$this->db->select('
		sa.id as atten_id,
		sa.staff_id,
		sa.in_time,
		sa.date,
		sa.status as att_status,
		sa.remark as att_remark,
		s.name as name,
		s.staff_id as staff_code,
		s.id
	');
	$this->db->from('staff_attendance sa');
	$this->db->join('staff s', 's.id = sa.staff_id');
	$this->db->join('login_credential as lc', 'lc.user_id = s.id', 'left');

	// ✅ Department-based restriction for HOD
	if ($logged_in_role_id == 8 && !empty($hod_department)) {
		$this->db->where('s.department', $hod_department);
	}

	// ✅ Manual attendance only (excluding superadmin)
	$this->db->group_start();
	$this->db->where('sa.is_manual', 1);
	$this->db->where('s.id !=', 1);
	$this->db->group_end();

	$this->db->where('lc.active', 1);
	// ✅ Order and retrieve
	$this->db->order_by('s.staff_id', 'ASC');
	$this->data['pending_attendance'] = $this->db->get()->result_array();


    $this->data['title'] = translate('attendance_approval');
    $this->data['sub_page'] = 'attendance/attendance_approval';
    $this->data['main_menu'] = 'attendance';
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
    redirect('attendance/attendance_approval');
}


public function manage_attendances()
{
    if (!get_permission('manage_attendance', 'is_add')) {
        access_denied();
    }

    //$branchID = $this->application_model->get_branch_id();

    // Search Action
    if (isset($_POST['search'])) {
        if (in_array(loggedin_role_id(), [1, 2, 3, 5])) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }
        $this->form_validation->set_rules('date', translate('date'), 'trim|required|callback_check_weekendday|callback_check_holiday|callback_get_valid_date');
        if ($this->form_validation->run() == true) {
            $date = $this->input->post('date');
            $branchID = $this->input->post('branch_id');

            $this->data['date'] = $date;
            $this->data['attendencelist'] = $this->attendance_model->getStaffAttendence($date, $branchID);
        }
    }

    // Save Action
    if (isset($_POST['save'])) {
        $attendance = $this->input->post('attendance');
        $date = $this->input->post('date');
        $branchID = $this->input->post('branch_id');
        $data = $this->input->post();

       foreach ($attendance as $key => $value) {
			// Skip rows with empty or null status
			if (!isset($value['status']) || trim($value['status']) === '') {
				continue;
			}

			$attStatus = $value['status'];
			$rawInTime = isset($value['in_time']) ? $value['in_time'] : null;

			// Convert in_time to 'H:i:s' (MySQL TIME format)
			$inTime = !empty($rawInTime) ? date('H:i:s', strtotime($rawInTime)) : null;

			$arrayAttendance = array(
				'staff_id' => $value['staff_id'],
				'status' => $attStatus,
				'remark' => $value['remark'],
				'in_time' => $inTime,
				'date' => $date,
				'branch_id' => $branchID,
				'is_manual' => 0,
			);

			if (empty($value['attendance_id'])) {
				$this->db->insert('staff_attendance', $arrayAttendance);
			} else {
				$this->db->where('id', $value['attendance_id']);
				$this->db->update('staff_attendance', array(
					'status' => $attStatus,
					'remark' => $value['remark'],
					'in_time' => $inTime,
					'is_manual' => 0,
				));
			}
		}


        set_alert('success', translate('information_has_been_updated_successfully'));
        redirect(current_url());
    }

    $this->data['getWeekends'] = $this->application_model->getWeekends($branchID);
    $this->data['branch_id'] = $branchID;
    $this->data['title'] = translate('manage_attendance');
    $this->data['sub_page'] = 'attendance/manage_entries';
    $this->data['main_menu'] = 'attendance';
    $this->load->view('layout/index', $this->data);
}

    /* employees attendance reports are produced here */
    public function employeewise_report()
    {
        if (!get_permission('employee_attendance_report', 'is_view')) {
            access_denied();
        }

        if ($_POST) {
			$staffID = get_loggedin_user_id();
            $this->data['branch_id'] = $this->input->post('branch_id');

            $this->data['role_id'] = $this->input->post('staff_role');
            $this->data['month'] = date('m', strtotime($this->input->post('timestamp')));
            $this->data['year'] = date('Y', strtotime($this->input->post('timestamp')));
            $this->data['days'] = date('t', strtotime($this->data['year'] . "-" . $this->data['month']));
            $this->data['stafflist'] = $this->attendance_model->getStaffList($this->data['branch_id'], $this->data['role_id'], $staffID);
        }
        $this->data['title'] = translate('employee_attendance');
        $this->data['sub_page'] = 'attendance/employees_report';
        $this->data['main_menu'] = 'attendance_report';
        $this->load->view('layout/index', $this->data);
    }

    public function get_valid_date($date)
    {
        $present_date = date('Y-m-d');
        $date = date("Y-m-d", strtotime($date));
        if ($date > $present_date) {
            $this->form_validation->set_message("get_valid_date", "Please Enter Correct Date");
            return false;
        } else {
            return true;
        }
    }

    public function check_holiday($date)
    {
        $branchID = $this->application_model->get_branch_id();
        $getHolidays = $this->attendance_model->getHolidays($branchID);
        $getHolidaysArray = explode('","', $getHolidays);

        if (!empty($getHolidaysArray)) {
            if (in_array($date, $getHolidaysArray)) {
                $this->form_validation->set_message('check_holiday', 'You have selected a holiday.');
                return false;
            } else {
                return true;
            }
        }
    }

    public function check_weekendday($date)
    {
        $branchID = $this->application_model->get_branch_id();
        $getWeekendDays = $this->attendance_model->getWeekendDaysSession($branchID);
        if (!empty($getWeekendDays)) {
            if (in_array($date, $getWeekendDays)) {
                $this->form_validation->set_message('check_weekendday', "You have selected a weekend date.");
                return false;
            } else {
                return true;
            }
        }
        return true;
    }

	public function proxy_image()
{
    $url = urldecode($this->input->get('url'));

    // Allow both IP and domain
    $allowed_hosts = ['103.51.129.55', 'emp.com.bd'];

    // Add protocol if missing
    if (!preg_match('/^https?:\/\//', $url)) {
        $url = 'http://' . $url;
    }

    // Validate URL
    $parsed_url = parse_url($url);
    if (!$parsed_url || !in_array($parsed_url['host'], $allowed_hosts) ||
        strpos($parsed_url['path'], '/ai_camera_captures/') === false) {
        show_error('Invalid image source', 403);
    }

    // Try to get image with timeout
    $context = stream_context_create(['http' => ['timeout' => 10]]);
    $image_data = @file_get_contents($url, false, $context);

    // Fallback to IP if domain fails
    if ($image_data === false && $parsed_url['host'] === 'emp.com.bd') {
        $ip_url = str_replace('emp.com.bd', '103.51.129.55', $url);
        $image_data = @file_get_contents($ip_url, false, $context);
    }

    if ($image_data !== false) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->buffer($image_data);
        header("Content-Type: " . $mime_type);
        echo $image_data;
    } else {
        show_error('Image not found', 404);
    }
}

}
