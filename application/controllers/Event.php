<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Event extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('event_model');
        $this->load->model('email_model');
    }

    public function index()
{
    // Permission check
    if (!get_permission('event', 'is_view')) {
        access_denied();
    }

    // Handle form submission
    if ($_POST) {
        if (!get_permission('event', 'is_add')) {
            ajax_access_denied();
        }

        $this->form_validation->set_rules('title', translate('title'), 'trim|required');
        $this->form_validation->set_rules('daterange', translate('date'), 'trim|required');

        if ($this->form_validation->run() !== false) {
            // Parse date range
            $daterange = explode(' - ', $this->input->post('daterange'));
            $start_date = date("Y-m-d", strtotime($daterange[0]));
            $end_date = date("Y-m-d", strtotime($daterange[1]));

			$event_type = $this->input->post('type_id');

			// Check if the "Others" option was selected
			if ($event_type == 'others') {
				$event_type = $this->input->post('custom_event_type');  // Use the custom event type
			}
            // Prepare data for insert
            $eventData = array(
                'title'         => $this->input->post('title'),
                'remark'        => $this->input->post('remarks'),
                'status'        => 1,
				'type'          => $event_type,  // Use the selected or custom event type
                'start_date'    => $start_date,
                'end_date'      => $end_date,
                'created_by'    => get_loggedin_user_id(),
                'session_id'    => get_session_id(),
                'created_at'    => date('Y-m-d H:i:s'),
                //'branch_id'     => $branchID,
				'email_sent' => 1,
            );

            // Save event
            $this->db->insert('event', $eventData);


			// ✅ Notify all active staff

			$notificationData = array(
				'user_id'    => '',
				'type'       => 'event',
				'title'      => 'New Event Scheduled',
				'message'    => 'A new event "' . $eventData['title'] . '" (' . $event_type . ') has been scheduled from ' . $start_date . ' to ' . $end_date . '.',
				'url'        => base_url('event'),
				'is_read'    => 0,
				'created_at' => date('Y-m-d H:i:s')
			);
			$this->db->insert('notifications', $notificationData);

			// 1. Get all active users except roles 1 and 9
			$recipients = $this->db
				->select('staff.email, staff.name')
				->from('staff')
				->join('login_credential', 'login_credential.user_id = staff.id')
				->where_not_in('login_credential.role', [1, 9])
				->where('login_credential.active', 1)
				->get()
				->result_array();

			// 2. Prepare email content
			$mail_subject = 'New Company Event: "' . $eventData['title'] . '"';

			$mail_body = "
			<html>
			  <body style='font-family:Arial, sans-serif; background:#f4f4f4; padding:20px;'>
				<table style='max-width:600px; margin:auto; background:#ffffff; border-radius:8px; padding:30px; box-shadow:0 0 10px rgba(0,0,0,0.05);'>
				  <tr><td style='text-align:center;'>
					<h2 style='color:#2c3e50;'>Upcoming Event Notification</h2>
				  </td></tr>
				  <tr><td style='font-size:15px; color:#34495e;'>
					<p>Dear Team,</p>
					<p>We are pleased to inform you that a new event has been scheduled at <strong>Employee Max Portal (EMP)</strong>.</p>
					<p><strong>Event Title:</strong> {$eventData['title']}<br>
					   <strong>Type:</strong> {$event_type}<br>
					   <strong>Schedule:</strong> {$start_date} to {$end_date}</p>
					<p>You are kindly requested to check the event details and stay updated via the Events section in your EMP portal.</p>
					<p>If you have any questions or require further information, please contact the HR/Admin department.</p>
					<p style='margin-top:20px;'>Warm regards,<br>
					<strong>EMP Administration</strong></p>
				  </td></tr>
				</table>
			  </body>
			</html>
			";

			// 3. Extract CC email list
			$cc_emails = array_column($recipients, 'email');

			// 4. Send single email with CC
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
				'to_name'       => 'Employee Max Portal',
				'subject'       => $mail_subject,
				'body'          => $mail_body,
				'cc'            => implode(',', $cc_emails),
			];

			$this->email_model->send_email_yandex($email_data);

            // AJAX response
            echo json_encode(array(
                'status' => 'success',
                'url'    => base_url('event'),
                'error'  => ''
            ));
        } else {
            // Validation error response
            echo json_encode(array(
                'status' => 'fail',
                'url'    => '',
                'error'  => $this->form_validation->error_array()
            ));
        }
        exit;
    }

    // Page asset and layout setup
    $jsAssets = array(
        'vendor/moment/moment.js',
        'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js',
        'vendor/fullcalendar/fullcalendar.js',
    );

    $this->data['branch_id'] = $branchID;
    $this->data['title'] = translate('calendar');
    $this->data['sub_page'] = 'event/index';
    $this->data['main_menu'] = 'event';

    $this->data['headerelements'] = array(
        'css' => array(
            'vendor/summernote/summernote.css',
            'vendor/daterangepicker/daterangepicker.css',
            'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css',
            'vendor/fullcalendar/fullcalendar.css',
        ),
        'js' => array_merge($jsAssets, array(
            'vendor/summernote/summernote.js',
            'vendor/daterangepicker/daterangepicker.js',
        )),
    );

    $this->load->view('layout/index', $this->data);
}


	public function getEventsList()
    {
        if (get_permission('event', 'is_view')) {
            $eventdata = [];

            // Get regular events
            $this->db->where('status', 1);
            $events = $this->db->get('event')->result();
            if (!empty($events)) {
                foreach ($events as $row) {
                    $arrayData = array(
                        'id' => $row->id,
                        'title' => $row->title,
                        'start' => $row->start_date,
                        'end' => date('Y-m-d', strtotime($row->end_date . "+1 days")),
                        'className' => 'fc-event-primary',
                        'type' => 'event'
                    );
                    $eventdata[] = $arrayData;
                }
            }

            // Get approved leave applications
            $this->db->select('la.id, la.start_date, la.end_date, s.name as staff_name')
                     ->from('leave_application la')
                     ->join('staff s', 's.id = la.user_id')
                     ->where('la.status', 2); // Approved leaves
            $leaves = $this->db->get()->result();

            if (!empty($leaves)) {
                foreach ($leaves as $leave) {
                    $arrayData = array(
                        'id' => $leave->id,
                        'title' => $leave->staff_name . ' - On Leave',
                        'start' => $leave->start_date,
                        'end' => date('Y-m-d', strtotime($leave->end_date . "+1 days")),
                        'className' => 'fc-event-warning',
                        'type' => 'leave'
                    );
                    $eventdata[] = $arrayData;
                }
            }

            echo json_encode($eventdata);
        }
    }

    public function edit($id='')
    {
        // check access permission
        if (!get_permission('event', 'is_edit')) {
            access_denied();
        }
        $this->data['event'] = $this->app_lib->getTable('event', array('t.id' => $id), true);
        if (empty($this->data['event'])) {
            redirect('dashboard');
        }

        //$branchID = $this->application_model->get_branch_id();
        if ($_POST) {

            $this->form_validation->set_rules('title', translate('title'), 'trim|required');

            $this->form_validation->set_rules('daterange', translate('date'), 'trim|required');

            if ($this->form_validation->run() !== false) {


                $daterange = explode(' - ', $this->input->post('daterange'));
                $start_date = date("Y-m-d", strtotime($daterange[0]));
                $end_date = date("Y-m-d", strtotime($daterange[1]));
				$event_type = $this->input->post('type_id');

			// Check if the "Others" option was selected
			if ($event_type == 'others') {
				$event_type = $this->input->post('custom_event_type');  // Use the custom event type
			}
                $arrayEvent = array(
                    'id' => $this->input->post('id'),
                    //'branch_id' => $branchID,
                    'type' => $event_type,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                );
                $this->event_model->save($arrayEvent);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('event');
                $array = array('status' => 'success', 'url' => $url, 'error' => '');
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'url' => '', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('calendar');
        $this->data['sub_page'] = 'event/edit';
        $this->data['main_menu'] = 'event';
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/summernote/summernote.css',
                'vendor/daterangepicker/daterangepicker.css',
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css',
            ),
            'js' => array(
                'vendor/summernote/summernote.js',
                'vendor/moment/moment.js',
                'vendor/daterangepicker/daterangepicker.js',
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js',
            ),
        );
        $this->load->view('layout/index', $this->data);
    }

    public function delete($id = '')
    {
        // check access permission
        if (get_permission('event', 'is_delete')) {
            $event_db = $this->db->where('id', $id)->get('event')->row_array();
            $file_name = $event_db['image'];
            if ($event_db['created_by'] == get_loggedin_user_id() || is_superadmin_loggedin()) {
                $this->db->where('id', $id);
                $this->db->delete('event');
                if ($file_name !== 'defualt.png') {
                    $file_name = 'uploads/frontend/events/' . $file_name;
                    if (file_exists($file_name)) {
                        unlink($file_name);
                    }
                }
            } else {
                set_alert('error', 'You do not have permission to delete');
            }
        } else {
            set_alert('error', translate('access_denied'));
        }
    }


public function getDetails()
{
    $id = $this->input->post('event_id');

    if (empty($id)) {
        redirect(base_url(), 'refresh');
    }

    $this->db->where('id', $id);
    $event = $this->db->get('event')->row_array();

    if (empty($event)) {
        echo "<tbody><tr><td colspan='2'>" . translate('event_not_found') . "</td></tr></tbody>";
        return;
    }

    // Handle event type properly

	 $type = $event['type'];

    $remark = !empty($event['remark']) && $event['type'] !== 'others' ? $event['remark'] : 'N/A';

    // Generate the event details HTML
    $html = "<tbody>";
    $html .= "<tr><td>" . translate('title') . "</td><td>" . translate($event['title']) . "</td></tr>";
    $html .= "<tr><td>" . translate('type') . "</td><td>" . translate($type) . "</td></tr>";
    $html .= "<tr><td>" . translate('start_date') . "</td><td>" . _d($event['start_date']) . "</td></tr>";
    $html .= "<tr><td>" . translate('end_date') . "</td><td>" . _d($event['end_date']) . "</td></tr>";
    $html .= "<tr><td>" . translate('description') . "</td><td>" . translate($remark) . "</td></tr>";
    $html .= "</tbody>";

    echo $html;
}


public function getLeaveDetails()
{
    $id = $this->input->post('leave_id');

	if (empty($id)) {
        redirect(base_url(), 'refresh');
    }

    $this->db->select('la.*, s.name as staff_name, s.staff_id, lc.name as leave_category')
             ->from('leave_application la')
             ->join('staff s', 's.id = la.user_id')
             ->join('leave_category lc', 'lc.id = la.category_id', 'left')
             ->where('la.id', $id);
    $leave = $this->db->get()->row_array();

	/* $this->db->where('id', $id);
    $leave = $this->db->get('leave_application')->row_array(); */

	/* print_r ($leave);
	die(); */

    if (empty($leave)) {
        echo "<tbody><tr><td colspan='2'>" . translate('leave_not_found') . "</td></tr></tbody>";
        return;
    }

    // Calculate leave duration
    $start_date = new DateTime($leave['start_date']);
    $end_date = new DateTime($leave['end_date']);
    $duration = $start_date->diff($end_date)->days + 1;

    // Generate the leave details HTML
    $html = "<tbody>";
    $html .= "<tr><td>" . translate('applicant') . "</td><td>" . $leave['staff_name'] . " (" . $leave['staff_id'] . ")</td></tr>";
    $html .= "<tr><td>" . translate('category') . "</td><td>" . ($leave['leave_category'] ?: 'Unpaid Leave') . "</td></tr>";
    $html .= "<tr><td>" . translate('start_date') . "</td><td>" . _d($leave['start_date']) . "</td></tr>";
    $html .= "<tr><td>" . translate('end_date') . "</td><td>" . _d($leave['end_date']) . "</td></tr>";
    $html .= "<tr><td>" . translate('duration') . "</td><td>" . $duration . " day(s)</td></tr>";
    $html .= "<tr><td>" . translate('reason') . "</td><td>" . ($leave['reason'] ?: 'N/A') . "</td></tr>";
    $html .= "</tbody>";

    echo $html;
}

}
