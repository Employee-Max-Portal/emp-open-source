<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Attendance_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

	public function getStaffAttendence($date, $branchID)
	{
		$userID = get_loggedin_id();

		if (empty($date)) {
			$date = date('Y-m-d');
		}

		$this->db->select("
			staff.*,
			lc.role,
			sa.id AS atten_id,
			IFNULL(sa.status, 0) AS att_status,
			sa.remark AS att_remark,
			sa.in_time
		");

		$this->db->from('staff');

		$this->db->join(
			'login_credential AS lc',
			'lc.user_id = staff.id',
			'left'
		);

		$this->db->join(
			'staff_attendance AS sa',
			"sa.staff_id = staff.id AND sa.date = " . $this->db->escape($date),
			'left'
		);

		// 🔒 Global exclusions
		$this->db->where('lc.active', 1);
		$this->db->where_not_in('lc.role', [1, 9, 11, 12]);   // exclude super admin, etc.
		$this->db->where_not_in('staff.id', [49]);           // exclude specific staff
		$this->db->where('staff.id !=', 1);                  // legacy/system user

		// 🔐 Role-based visibility
		if (in_array(loggedin_role_id(), [1, 2, 3, 5])) {

			if (!empty($branchID) && $branchID !== 'all') {
				$this->db->where('staff.branch_id', $branchID);
			}

		} else {
			// Non-privileged users see only themselves
			$this->db->where('staff.id', $userID);
		}

		$this->db->order_by('staff.id', 'ASC');

		return $this->db->get()->result_array();
	}


    // GET STAFF ALL DETAILS
   public function getStaffList($branch_id = '', $role_id = '', $staffID, $active = 1)
	{
		$excluded_roles = [1, 2, 3, 5];
		$logged_role_id = loggedin_role_id();

		$this->db->select('staff.*, login_credential.role as role_id, roles.name as role');
		$this->db->from('staff');
		$this->db->join('login_credential', 'login_credential.user_id = staff.id', 'inner');
		$this->db->join('roles', 'roles.id = login_credential.role', 'left');
		$this->db->where_not_in('login_credential.role', [1, 9, 11, 12]);


		if (!empty($role_id) && $role_id !== 'all') {
			$this->db->where('login_credential.role', $role_id);
		}

		if (!empty($branch_id) && $branch_id !== 'all') {
			$this->db->where('staff.branch_id', $branch_id);
		}

		if (!in_array($logged_role_id, $excluded_roles) && !empty($staffID)) {
			$this->db->where('staff.id', get_loggedin_user_id());
		}

		$this->db->where('login_credential.active', $active);
		$this->db->order_by('staff.id', 'ASC');

		return $this->db->get()->result_array();
	}

	public function getWeekendDaysSession($branch_id = '')
	{
		// If no branch selected or branch is 'all', use the first branch's weekends
		if (empty($branch_id) || $branch_id === 'all') {
			$branch_id = $this->db->select('id')->order_by('id', 'ASC')->limit(1)->get('branch')->row('id');
		}

		$date_from = strtotime(date("Y-m-01")); // First day of current month
		$date_to = strtotime(date("Y-m-t"));    // Last day of current month
		$oneDay = 60 * 60 * 24;

		$allDays = array(
			'0' => 'Sunday',
			'1' => 'Monday',
			'2' => 'Tuesday',
			'3' => 'Wednesday',
			'4' => 'Thursday',
			'5' => 'Friday',
			'6' => 'Saturday',
		);

		$weekendDay = $this->application_model->getWeekends($branch_id);
		$weekendArrays = explode(',', $weekendDay);
		$weekendDateArrays = [];

		for ($i = $date_from; $i <= $date_to; $i += $oneDay) {
			if (!empty($weekendArrays)) {
				foreach ($weekendArrays as $weekendValue) {
					$weekendValue = trim($weekendValue);
					if (is_numeric($weekendValue) && $weekendValue >= 0 && $weekendValue <= 6) {
						if (date('l', $i) === $allDays[$weekendValue]) {
							$weekendDateArrays[] = date('Y-m-d', $i);
						}
					}
				}
			}
		}

		return $weekendDateArrays;
	}


    public function getHolidays($school_id = '')
    {
        //$this->db->where('branch_id', $school_id);
        $this->db->where('session_id', get_session_id());
        $this->db->where(['status' => 1, 'type' => 'holiday']);
        $this->db->order_by('start_date', 'asc');
        $holidays = $this->db->get('event')->result();
        $allHolidayList = array();
        if (!empty($holidays)) {
            foreach ($holidays as $holiday) {
                $from_date = strtotime($holiday->start_date);
                $to_date = strtotime($holiday->end_date);
                $oneday = 60 * 60 * 24;
                for ($i = $from_date; $i <= $to_date; $i = $i + $oneday) {
                    $allHolidayList[] = date('Y-m-d', $i);
                }
            }
        }
        $uniqueHolidays = array_unique($allHolidayList);
        if (!empty($uniqueHolidays)) {
            $uniqueHolidays = implode('","', $uniqueHolidays);
        } else {
            $uniqueHolidays = '';
        }
        return $uniqueHolidays;
    }
}
