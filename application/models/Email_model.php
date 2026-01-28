<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Email_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('mailer');
    }

	public function get_all_user_emails()
	{
		$this->db->select('staff.email, staff.name');
		$this->db->from('staff');
		$this->db->join('login_credential AS lc', 'lc.user_id = staff.id', 'left');
		$this->db->where('lc.active', 1);
		$this->db->where('staff.id !=', 1);
		$this->db->where('staff.email !=', ''); // Exclude empty emails
		$query = $this->db->get();

		return $query->result();
	}


	public function get_email_config() {
        $query = $this->db->get('email_config');
        return $query->row_array();
    }

	function get_admin_email() {
    $query = "SELECT institute_email FROM global_settings LIMIT 1";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['institute_email'] ?? '';
    }

    return '';
}


    public function save_email_config($data) {
		$this->db->update('email_config', $data);
        return true;
    }



public function send_email_yandex($email_data)
{
    // Prepare and log the email data
    log_message('debug', 'Sending email to: ' . $email_data['to_email']);
    if (isset($email_data['cc'])) {
        log_message('debug', 'CC: ' . $email_data['cc']);
    }

    // Send Email via External API
	$ch = curl_init(base_url('assets/email_project/sems.php'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($email_data));

    $email_response = curl_exec($ch);

    if (curl_errno($ch)) {
        log_message('error', 'Registration Email cURL Error: ' . curl_error($ch));
    } else {
        log_message('debug', 'Registration Email Response: ' . $email_response);
    }

    curl_close($ch);
}

    public function sentStaffRegisteredAccount($data)
    {
        $emailTemplate = $this->getEmailTemplates(1);
        if ($emailTemplate['notified'] == 1 && !empty($data['email'])) {
            $role_name = get_type_name_by_id('roles', $data['user_role']);
            $message = $emailTemplate['template_body'];
            $message = str_replace("{institute_name}", get_global_setting('institute_name'), $message);
            $message = str_replace("{name}", $data['name'], $message);
            $message = str_replace("{login_username}", $data['username'], $message);
            $message = str_replace("{password}", $data['password'], $message);
            $message = str_replace("{user_role}", $role_name, $message);
            $message = str_replace("{login_url}", base_url(), $message);
            $msgData['recipient'] = $data['email'];
            $msgData['subject'] = $emailTemplate['subject'];
            $msgData['message'] = $message;
            return $this->sendEmail($msgData);
        }
    }

    public function sentStaffSalaryPay($data)
    {
        $emailTemplate = $this->getEmailTemplates(5);
        if ($emailTemplate['notified'] == 1 && !empty($data['recipient'])) {
            $message = $emailTemplate['template_body'];
            $message = str_replace("{institute_name}", get_type_name_by_id('branch', $data['branch_id']), $message);
            $message = str_replace("{name}", $data['name'], $message);
            $message = str_replace("{month_year}", $data['month_year'], $message);
            $message = str_replace("{payslip_no}", $data['payslip_no'], $message);
            $message = str_replace("{payslip_url}", $data['payslip_url'], $message);
            $msgData['recipient'] = $data['recipient'];
            $msgData['subject'] = $emailTemplate['subject'];
            $msgData['message'] = $message;
            return $this->sendEmail($msgData);
        }
    }

    public function sentAdvanceSalary($data)
    {
        $email_alert = false;
        if ($data['status'] == 2) {
            //send advance salary approve email
            $emailTemplate = $this->getEmailTemplates(9, $data['branch_id']);
            if ($emailTemplate['notified'] == 1 && !empty($data['email'])) {
                $email_alert = true;
            }
        } elseif ($data['status'] == 3) {
            //send advance salary reject email
            $emailTemplate = $this->getEmailTemplates(10, $data['branch_id']);
            if ($emailTemplate['notified'] == 1 && !empty($data['email'])) {
                $email_alert = true;
            }
        }
        if ($email_alert == true) {
            $message = $emailTemplate['template_body'];
            $message = str_replace("{institute_name}", get_global_setting('institute_name'), $message);
            $message = str_replace("{applicant_name}", $data['staff_name'], $message);
            $message = str_replace("{deduct_motnh}", date("F Y", strtotime($data['deduct_motnh'])), $message);
            $message = str_replace("{comments}", $data['comments'], $message);
            $message = str_replace("{amount}", $data['amount'], $message);
            $msgData['branch_id'] = $data['branch_id'];
            $msgData['recipient'] = $data['email'];
            $msgData['subject'] = $emailTemplate['subject'];
            $msgData['message'] = $message;
            return $this->sendEmail($msgData);
        }
    }

    public function sentLeaveRequest($data)
    {
        $email_alert = false;
        if ($data['status'] == 2) {
            //send leave salary approve email
            $emailTemplate = $this->getEmailTemplates(7);
            if ($emailTemplate['notified'] == 1 && !empty($data['email'])) {
                $email_alert = true;
            }
        } elseif ($data['status'] == 3) {
            //send leave salary reject email
            $emailTemplate = $this->getEmailTemplates(8);
            if ($emailTemplate['notified'] == 1 && !empty($data['email'])) {
                $email_alert = true;
            }
        }
        if ($email_alert == true) {
            $message = $emailTemplate['template_body'];
            $message = str_replace("{institute_name}", get_global_setting('institute_name'), $message);
            $message = str_replace("{applicant_name}", $data['applicant'], $message);
            $message = str_replace("{start_date}", _d($data['start_date']), $message);
            $message = str_replace("{end_date}", _d($data['end_date']), $message);
            $message = str_replace("{comments}", $data['comments'], $message);
            $msgData['recipient'] = $data['email'];
            $msgData['subject'] = $emailTemplate['subject'];
            $msgData['message'] = $message;
            return $this->sendEmail($msgData);
        }
    }


    public function changePassword($data)
    {
        $emailTemplate = $this->getEmailTemplates(3, $data['branch_id']);
        if ($emailTemplate['notified'] == 1) {
            $user = $this->application_model->getUserNameByRoleID(loggedin_role_id(), get_loggedin_user_id());
            if (!empty($user['email'])) {
                $message = $emailTemplate['template_body'];
                $message = str_replace("{institute_name}", get_type_name_by_id('branch', $data['branch_id']), $message);
                $message = str_replace("{name}", $user['name'], $message);
                $message = str_replace("{email}", $user['email'], $message);
                $message = str_replace("{password}", $data['password'], $message);
                $msgData['recipient'] = $user['email'];
                $msgData['subject'] = $emailTemplate['subject'];
                $msgData['message'] = $message;
                $msgData['branch_id'] = $data['branch_id'];
                return $this->sendEmail($msgData);
            }
        }
    }

    public function sentForgotPassword($data)
    {
        $emailTemplate = $this->db->where(array('template_id' => 2, 'branch_id' => $data['branch_id']))->get('email_templates_details')->row_array();
        if ($emailTemplate['notified'] == 1 && !empty($data['email'])) {
            $message = $emailTemplate['template_body'];
            $message = str_replace("{institute_name}", get_global_setting('institute_name'), $message);
            $message = str_replace("{username}", $data['username'] , $message);
            $message = str_replace("{name}", $data['name'], $message);
            $message = str_replace("{reset_url}", $data['reset_url'], $message);
            $message = str_replace("{email}", $data['email'], $message);
            $msgData['branch_id'] = $data['branch_id'];
            $msgData['recipient'] = $data['email'];
            $msgData['subject'] = $emailTemplate['subject'];
            $msgData['message'] = $message;
            return $this->sendEmail($msgData);
        }
    }



    public function sendEmail($data)
    {
        if (empty($data['branch_id'])) {
            $data['branch_id'] = $this->application_model->get_branch_id();
        }
        if ($this->mailer->send($data)) {
            return true;
        } else {
            return false;
        }
    }

    public function getEmailTemplates($id, $branchID = '')
    {
        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }
        $this->db->select('td.*');
        $this->db->from('email_templates_details as td');
        $this->db->where('td.template_id', $id);
        $this->db->where('td.branch_id', $branchID);
        $result = $this->db->get()->row_array();
        if (empty($result)) {
            $array = array(
                'notified' => '',
                'template_body' => '',
                'subject' => '',
            );
            return $array;
        } else {
           return $result;
        }
    }


}