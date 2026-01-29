<?php defined('BASEPATH') or exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->output->set_header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT');
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        $this->output->set_header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

        // Check if system is properly configured
        if ($this->config->item('installed') == false) {
            // If config files don't exist, show setup message instead of redirect
            if (!file_exists(APPPATH.'config/database.php') || !file_exists(APPPATH.'config/config.php')) {
                $this->showSetupMessage();
                return;
            }
            redirect(site_url('install'));
        }

        $get_config = $this->db->get_where('global_settings', array('id' => 1))->row_array();
        $branchID = $this->application_model->get_branch_id();
        if (!empty($branchID)) {
            $branch = $this->db->select('currency_formats,symbol_position,symbol,currency,timezone')->where('id', $branchID)->get('branch')->row();
            $get_config['currency'] = $branch->currency;
            $get_config['currency_symbol'] = $branch->symbol;
            $get_config['currency_formats'] = $branch->currency_formats;
            $get_config['symbol_position'] = $branch->symbol_position;
            if (!empty($branch->timezone)) {
                $get_config['timezone'] = $branch->timezone;
            }
        }
        $this->data['global_config'] = $get_config;
        $this->data['theme_config'] = $this->db->get_where('theme_settings', array('id' => 1))->row_array();
        date_default_timezone_set($get_config['timezone']);
    }

    private function showSetupMessage()
    {
        echo '<!DOCTYPE html>
<html>
<head>
    <title>EMP Setup Required</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; margin-bottom: 20px; }
        .step { background: #ecf0f1; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .code { background: #2c3e50; color: #ecf0f1; padding: 10px; border-radius: 3px; font-family: monospace; margin: 5px 0; }
        .warning { background: #e74c3c; color: white; padding: 10px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="header">🚀 EMP Setup Required</h1>
        <div class="warning">Configuration files are missing. Please complete the setup steps below:</div>
        
        <div class="step">
            <h3>Step 1: Database Configuration</h3>
            <div class="code">cp application/config/database.php.example application/config/database.php</div>
            <p>Edit database.php with your MySQL credentials</p>
        </div>
        
        <div class="step">
            <h3>Step 2: Import Database</h3>
            <div class="code">mysql -u username -p database_name < sql/emp.sql</div>
        </div>
        
        <div class="step">
            <h3>Step 3: System Configuration</h3>
            <div class="code">cp application/config/config.php.example application/config/config.php</div>
            <p>Set <strong>$config["installed"] = TRUE;</strong> in config.php</p>
        </div>
        
        <div class="step">
            <h3>Step 4: Set Permissions</h3>
            <div class="code">chmod -R 755 uploads/ application/logs/</div>
        </div>
        
        <p><strong>After completing these steps, refresh this page.</strong></p>
        <p>For detailed instructions, see <a href="INSTALL.md">INSTALL.md</a></p>
    </div>
</body>
</html>';
        exit;
    }

    public function getBranchDetails()
    {
        $branchID = $this->application_model->get_branch_id();
        $this->db->select('*');
        $this->db->where('id', $branchID);
        $this->db->from('branch');
        $r = $this->db->get()->row_array();
        if (empty($r)) {
            return ['stu_generate' => "", 'grd_generate' => ""];
        } else {
            return $r;
        }
    }

    public function photoHandleUpload($str, $fields)
    {
        $allowedExts = array_map('trim', array_map('strtolower', explode(',', $this->data['global_config']['image_extension'])));
        $allowedSizeKB = $this->data['global_config']['image_size'];
        $allowedSize = floatval(1024 * $allowedSizeKB);
        if (isset($_FILES["$fields"]) && !empty($_FILES["$fields"]['name'])) {
            $file_size = $_FILES["$fields"]["size"];
            $file_name = $_FILES["$fields"]["name"];
            $extension = pathinfo($file_name, PATHINFO_EXTENSION);
            if ($files = filesize($_FILES["$fields"]['tmp_name'])) {
                if (!in_array(strtolower($extension), $allowedExts)) {
                    $this->form_validation->set_message('photoHandleUpload', translate('this_file_type_is_not_allowed'));
                    return false;
                }
                if ($file_size > $allowedSize) {
                    $this->form_validation->set_message('photoHandleUpload', translate('file_size_shoud_be_less_than') . " $allowedSizeKB KB.");
                    return false;
                }
            } else {
                $this->form_validation->set_message('photoHandleUpload', translate('error_reading_the_file'));
                return false;
            }
            return true;
        }
    }

    public function fileHandleUpload($str, $fields)
    {
        $allowedExts = array_map('trim', array_map('strtolower', explode(',', $this->data['global_config']['file_extension'])));
        $allowedSizeKB = $this->data['global_config']['file_size'];
        $allowedSize = floatval(1024 * $allowedSizeKB);
        if (isset($_FILES["$fields"]) && !empty($_FILES["$fields"]['name'])) {
            $file_size = $_FILES["$fields"]["size"];
            $file_name = $_FILES["$fields"]["name"];
            $extension = pathinfo($file_name, PATHINFO_EXTENSION);
            if ($files = filesize($_FILES["$fields"]['tmp_name'])) {
                if (!in_array(strtolower($extension), $allowedExts)) {
                    $this->form_validation->set_message('fileHandleUpload', translate('this_file_type_is_not_allowed'));
                    return false;
                }
                if ($file_size > $allowedSize) {
                    $this->form_validation->set_message('fileHandleUpload', translate('file_size_shoud_be_less_than') . " $allowedSizeKB KB.");
                    return false;
                }
            } else {
                $this->form_validation->set_message('fileHandleUpload', translate('error_reading_the_file'));
                return false;
            }
            return true;
        }
    }
}

class Admin_Controller extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!is_loggedin()) {
            $this->session->set_userdata('redirect_url', current_url());
            redirect(base_url('authentication'), 'refresh');
        }
    }
}

class Dashboard_Controller extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!is_loggedin()) {
            $this->session->set_userdata('redirect_url', current_url());
            redirect(base_url('authentication'), 'refresh');
        }
    }
}


class Authentication_Controller extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('authentication_model');
    }
}