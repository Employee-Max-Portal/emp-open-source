<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
function mermaidImageUrl($diagramText) {
    // Encode Mermaid code to Base64 (URL safe)
    $base64 = base64_encode($diagramText);
    $base64url = rtrim(strtr($base64, '+/', '-_'), '=');
    return "https://mermaid.ink/img/" . $base64url;
}

 function get_task_statuses()
    {
        return array(
            'backlog'     => translate('Backlog'),
            'hold'        => translate('Hold'),
            'todo'        => translate('to-do'),
            'submitted'   => translate('submitted'),
            'in_progress' => translate('in_progress'),
            'in_review'   => translate('in_review'),
            'planning'    => translate('planning'),
            'observation' => translate('observation'),
            'waiting'     => translate('waiting'),
            'done'        => translate('done'),
            'solved'      => translate('solved'),
            'canceled'    => translate('canceled'),
        );
    }
// get staff db id
function get_break_condition()
{
    $userID = get_loggedin_user_id();
    $CI = &get_instance();
    
    $CI->db->select('pause_status');
    $CI->db->from('staff');
    $CI->db->where('id', $userID);
    
    $query = $CI->db->get();
    
    if ($query->num_rows() > 0) {
        $row = $query->row();
        return $row->pause_status;
    } else {
        return '3';
    }
	
}

function get_history_id()
{
    $userID = get_loggedin_user_id();
    $CI = &get_instance();
    
    $CI->db->select('pause_history');
    $CI->db->from('staff');
    $CI->db->where('id', $userID);
    
    $query = $CI->db->get();
    
    if ($query->num_rows() > 0) {
        $row = $query->row();
        return $row->pause_history;
    } else {
        return '3';
    }
	
}

function get_break_id()
{
    $userID = get_loggedin_user_id();
    $CI = &get_instance();
    
    $CI->db->select('pause_id');
    $CI->db->from('staff');
    $CI->db->where('id', $userID);
    
    $query = $CI->db->get();
    
    if ($query->num_rows() > 0) {
        $row = $query->row();
        return $row->pause_id;
    } else {
        return '3';
    }
}

function get_break_starttime()
{
    $userID = get_loggedin_user_id();
    $CI = &get_instance();
    
    // Select the pause_id and break_name
    $CI->db->select('pause_history.start_datetime');
    $CI->db->from('staff');
    
    // Left join the breaks table on pause_id
    $CI->db->join('pause_history', 'staff.pause_history = pause_history.id', 'left');

    $CI->db->where('staff.id', $userID);
    
    $query = $CI->db->get();
    
    if ($query->num_rows() > 0) {
        $row = $query->row();
        
        // Return the break_name if it exists, otherwise return '3'
        return $row->start_datetime;
    } else {
        return '3';
    }
}



function get_break_name()
{
    $userID = get_loggedin_user_id();
    $CI = &get_instance();
    
    // Select the pause_id and break_name
    $CI->db->select('pauses.name');
    $CI->db->from('staff');
    
    // Left join the breaks table on pause_id
    $CI->db->join('pauses', 'staff.pause_id = pauses.id', 'left');

    $CI->db->where('staff.id', $userID);
    
    $query = $CI->db->get();
    
    if ($query->num_rows() > 0) {
        $row = $query->row();
        
        // Return the break_name if it exists, otherwise return '3'
        return $row->name;
    } else {
        return '3';
    }
}



// return translation
function translate($word = '')
{
    $CI = &get_instance();
    if ($CI->session->has_userdata('set_lang')) {
        $set_lang = $CI->session->userdata('set_lang');
    } else {
        $set_lang = get_global_setting('translation');
    }

    if ($set_lang == '') {
        $set_lang = 'english';
    }

    $sql = "SELECT `english`,`" . $set_lang . "` FROM `languages` WHERE `word` = ?";
    $query = $CI->db->query($sql, array($word));
    if ($query->num_rows() > 0) {
        if (isset($query->row()->$set_lang) && $query->row()->$set_lang != '') {
            return $query->row()->$set_lang;
        } else {
            return $query->row()->english;
        }
    } else {
        $arrayData = array(
            'word' => $word,
            'english' => ucwords(str_replace('_', ' ', $word)),
        );
        $CI->db->insert('languages', $arrayData);
        return ucwords(str_replace('_', ' ', $word));
    }
}

function moduleIsEnabled($prefix)
{
    $ci = &get_instance();
    $role_id = $ci->session->userdata('loggedin_role_id');
    $branchID = $ci->session->userdata('loggedin_branch');
    if ($role_id == 1) {
        return 1;
    }
    $sql = "SELECT IF(`oaf`.`isEnabled` is NULL, 1, `oaf`.`isEnabled`) as `status` FROM `permission_modules` LEFT JOIN `modules_manage` as `oaf` ON `oaf`.`modules_id` = `permission_modules`.`id` AND `oaf`.`branch_id` = " . $ci->db->escape($branchID) . " WHERE `permission_modules`.`prefix` = " . $ci->db->escape($prefix);
    $result = $ci->db->query($sql)->row();
    if (empty($result)) {
        return 1;
    } else {
        return $result->status;
    }
}

function get_permission($permission, $can = '')
{
    $ci = &get_instance();
    $role_id = $ci->session->userdata('loggedin_role_id');
    if ($role_id == 1) {
        return true;
    }
    $permissions = get_staff_permissions($role_id);
    foreach ($permissions as $permObject) {
        if ($permObject->permission_prefix == $permission && $permObject->$can == '1') {
            return true;
        }
    }
    return false;
}

function get_staff_permissions($id)
{
    $ci = &get_instance();
    $sql = "SELECT `staff_privileges`.*, `permission`.`id` as `permission_id`, `permission`.`prefix` as `permission_prefix` FROM `staff_privileges` JOIN `permission` ON `permission`.`id`=`staff_privileges`.`permission_id` WHERE `staff_privileges`.`role_id` = " . $ci->db->escape($id);
    $result = $ci->db->query($sql)->result();
    return $result;
}

function get_session_id()
{
    $CI = &get_instance();
    if ($CI->session->has_userdata('set_session_id')) {
        $session_id = $CI->session->userdata('set_session_id');
    } else {
        $session_id = get_global_setting('session_id');
    }
    return $session_id;
}

function is_secure($url)
{
    if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) {
        $val = 'https://' . $url;
    } else {
        $val = 'http://' . $url;
    }
    return $val;
}

function get_global_setting($name = '')
{
    $CI = &get_instance();
    $name = trim($name);
    $CI->db->where('id', 1);
    $CI->db->select($name);
    $query = $CI->db->get('global_settings');

    if ($query->num_rows() > 0) {
        $row = $query->row();
        return $row->$name;
    }
}

// is superadmin logged in @return boolean
function is_superadmin_loggedin()
{
    $CI = &get_instance();
    if ($CI->session->userdata('loggedin_role_id') == 1) {
        return true;
    }
    return false;
}

// is admin logged in @return boolean
function is_admin_loggedin()
{
    $CI = &get_instance();
    if ($CI->session->userdata('loggedin_role_id') == 2) {
        return true;
    }
    return false;
}


// get logged in user id - login credential DB id
function get_loggedin_id()
{
    $ci = &get_instance();
    return $ci->session->userdata('loggedin_id');
}

// get staff db id
function get_loggedin_user_id()
{
    $ci = &get_instance();
    return $ci->session->userdata('loggedin_userid');
}

// get session loggedin
function is_loggedin()
{
    $CI = &get_instance();
    if ($CI->session->has_userdata('loggedin')) {
        return true;
    }
    return false;
}

// get loggedin role name
function loggedin_role_name()
{
    $CI = &get_instance();
    $roleID = $CI->session->userdata('loggedin_role_id');
    return $CI->db->select('name')->where('id', $roleID)->get('roles')->row()->name;
}

function loggedin_role_id()
{
    $ci = &get_instance();
    return $ci->session->userdata('loggedin_role_id');
}

// get logged in user type
function get_loggedin_user_type()
{
    $CI = &get_instance();
    return $CI->session->userdata('loggedin_type');
}

// get logged in user type
function get_loggedin_branch_id()
{
    $CI = &get_instance();
    return $CI->session->userdata('loggedin_branch');
}

// get table name by type and id
function get_type_name_by_id($table, $type_id = '', $field = 'name')
{
    $CI = &get_instance();
    $get = $CI->db->select($field)->from($table)->where('id', $type_id)->limit(1)->get()->row_array();
    return $get[$field];
}

// set session alert / flashdata
function set_alert($type, $message)
{
    $CI = &get_instance();
    $CI->session->set_flashdata('alert-message-' . $type, $message);
}

// generate md5 hash
function app_generate_hash()
{
    return md5(rand() . microtime() . time() . uniqid());
}

// generate encryption key
function generate_encryption_key()
{
    $CI = &get_instance();
    // In case accessed from my_functions_helper.php
    $CI->load->library('encryption');
    $key = bin2hex($CI->encryption->create_key(16));
    return $key;
}

// generate get RDC Proofs url
function get_rdc_proofs2($file_name = '')
{
    if ($file_name == 'defualt.png' || empty($file_name)) {
        $image_url = base_url('uploads/app_image/defualt.png');
    } else {
        if (file_exists('uploads/attachments/rdc_proofs/' . $file_name)) {
            $image_url = base_url('uploads/attachments/rdc_proofs/' . $file_name);
        } else {
            $image_url = base_url('uploads/app_image/defualt.png');
        }
    }
    return $image_url;
}

function get_rdc_proofs($file_name = '')
{
    // Handle null, empty, or default values
    if (is_null($file_name) || $file_name == 'defualt.png' || empty($file_name) || trim($file_name) == '') {
        return ''; // Return empty string for dropify to show default state
    } else {
        if (file_exists('uploads/attachments/rdc_proofs/' . $file_name)) {
            $image_url = base_url('uploads/attachments/rdc_proofs/' . $file_name);
        } else {
            return ''; // Return empty string if file doesn't exist
        }
    }
    return $image_url;
}

// generate get image url
function get_image_url($role = '', $file_name = '')
{
    if ($file_name == 'defualt.png' || empty($file_name)) {
        $image_url = base_url('uploads/app_image/defualt.png');
    } else {
        if (file_exists('uploads/images/' . $role . '/' . $file_name)) {
            $image_url = base_url('uploads/images/' . $role . '/' . $file_name);
        } else {
            $image_url = base_url('uploads/app_image/defualt.png');
        }
    }
    return $image_url;
}

// get date format config
function _d($date)
{
    if ($date == '' || is_null($date) || $date == '0000-00-00') {
        return '';
    }
    $formats = 'Y-m-d';
    $get_format = get_global_setting('date_format');
    if ($get_format != '') {
        $formats = $get_format;
    }
    return date($formats, strtotime($date));
}

// delete url
function btn_delete($uri)
{
    return "<button class='btn btn-danger icon btn-circle' onclick=confirm_modal('" . base_url($uri) . "') ><i class='fas fa-trash-alt'></i></button>";
}

// delete url
function csrf_jquery_token()
{
    $csrf = [get_instance()->security->get_csrf_token_name() => get_instance()->security->get_csrf_hash()];
    return $csrf;
}

function check_hash_restrictions($table, $id, $hash)
{
    $CI = &get_instance();
    if (!$table || !$id || !$hash) {
        show_404();
    }

    $query = $CI->db->select('hash')->from($table)->where('id', $id)->get();
    if ($query->num_rows() > 0) {
        $get_hash = $query->row()->hash;
    } else {
        $get_hash = '';
    }
    if (empty($hash) || ($get_hash != $hash)) {
        show_404();
    }
}

function get_nicetime($date)
{
    $get_format = get_global_setting('date_format');
    if (empty($date)) {
        return "Unknown";
    }
    // Current time as MySQL DATETIME value
    $csqltime = date('Y-m-d H:i:s');
    // Current time as Unix timestamp
    $ptime = strtotime($date);
    $ctime = strtotime($csqltime);

    //Now calc the difference between the two
    $timeDiff = floor(abs($ctime - $ptime) / 60);

    //Now we need find out whether or not the time difference needs to be in
    //minutes, hours, or days
    if ($timeDiff < 2) {
        $timeDiff = "Just now";
    } elseif ($timeDiff > 2 && $timeDiff < 60) {
        $timeDiff = floor(abs($timeDiff)) . " minutes ago";
    } elseif ($timeDiff > 60 && $timeDiff < 120) {
        $timeDiff = floor(abs($timeDiff / 60)) . " hour ago";
    } elseif ($timeDiff < 1440) {
        $timeDiff = floor(abs($timeDiff / 60)) . " hours ago";
    } elseif ($timeDiff > 1440 && $timeDiff < 2880) {
        $timeDiff = floor(abs($timeDiff / 1440)) . " day ago";
    } elseif ($timeDiff > 2880) {
        $timeDiff = date($get_format, $ptime);
    }
    return $timeDiff;
}

function bytesToSize($path, $filesize = '')
{
    if (!is_numeric($filesize)) {
        $bytes = sprintf('%u', filesize($path));
    } else {
        $bytes = $filesize;
    }
    if ($bytes > 0) {
        $unit = intval(log($bytes, 1024));
        $units = [
            'B',
            'KB',
            'MB',
            'GB',
        ];
        if (array_key_exists($unit, $units) === true) {
            return sprintf('%d %s', $bytes / pow(1024, $unit), $units[$unit]);
        }
    }
    return $bytes;
}

function array_to_object($array)
{
    if (!is_array($array) && !is_object($array)) {
        return new stdClass();
    }
    return json_decode(json_encode((object) $array));
}

function access_denied()
{
    set_alert('error', translate('access_denied'));
    redirect(site_url('dashboard'));
}

function ajax_access_denied()
{
    set_alert('error', translate('access_denied'));
    $array = array('status' => 'access_denied');
    echo json_encode($array);
    exit();
}

function slugify($text)
{
    // replace non letter or digits by -
    $text = preg_replace('~[^\pL\d]+~u', '_', $text);

    // transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);

    // trim
    $text = trim($text, '_');

    // remove duplicated - symbols
    $text = preg_replace('~-+~', '_', $text);

    // lowercase
    $text = strtolower($text);
    return $text;
}

function get_request_url()
{
    $url = $_SERVER['QUERY_STRING'];
    $url = (!empty($url) ? '?' . $url : '');
    return $url;
}

function delete_dir($dirPath)
{
    if (!is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            delete_dir($file);
        } else {
            unlink($file);
        }
    }
    if (rmdir($dirPath)) {
        return true;
    }
    return false;
}

function currencyFormat($amount = 0)
{
    $CI = &get_instance();
    $array              = $CI->data['global_config'];
    $currency           = $array['currency'];
    $currency_symbol    = $array['currency_symbol'];
    $currency_formats   = $array['currency_formats'];
    $symbol_position    = $array['symbol_position'];

    $amount = empty($amount) ? 0 : $amount;
    $value = $amount;
    if ($currency_formats == 1) {
        $value = number_format($amount, 2, '.', '');
    } elseif ($currency_formats == 2) {
        $value = moneyFormatIndia($amount);
    } elseif ($currency_formats == 3) {
        $value = number_format($amount, 3, '.', ',');
    } elseif ($currency_formats == 4) {
        $value = number_format($amount, 2, ',', '.');
    } elseif ($currency_formats == 5) {
        $value = number_format($amount, 2, '.', ',');
    } elseif ($currency_formats == 6) {
        $value = number_format($amount, 2, ',', ' ');
    } elseif ($currency_formats == 7) {
        $value = number_format($amount, 2, '.', ' ');
    } elseif ($currency_formats == 8) {
        $value = $amount;
    }

    if ($symbol_position == 1) {
        $value = $currency_symbol . $value; 
    } elseif ($symbol_position == 2) {
        $value = $value . $currency_symbol;
    } elseif ($symbol_position == 3) {
        $value = $currency_symbol . " " . $value;
    } elseif ($symbol_position == 4) {
        $value = $value . " " . $currency_symbol;
    } elseif ($symbol_position == 5) {
        $value = $currency . " " . $value;
    } elseif ($symbol_position == 6) {
        $value = $value . " " . $currency;
    }
    return $value;
}

/**
 * Generate unique ID for leave, fund requisition, and advance salary
 * @param string $type - 'leave', 'fund_requisition', or 'advance_salary'
 * @return string - unique ID with prefix
 */
function generate_unique_id($type)
{
    $CI = &get_instance();
    
    // Define prefixes
    $prefixes = [
        'leave' => 'lev',
        'fund_requisition' => 'fnd',
        'advance_salary' => 'adv'
    ];
    
    // Define table names
    $tables = [
        'leave' => 'leave_application',
        'fund_requisition' => 'fund_requisition',
        'advance_salary' => 'advance_salary'
    ];
    
    if (!isset($prefixes[$type]) || !isset($tables[$type])) {
        return false;
    }
    
    $prefix = $prefixes[$type];
    $table = $tables[$type];
    
    // Get the next number by finding the highest existing number for this type
    $CI->db->select('unique_id');
    $CI->db->from($table);
    $CI->db->where('unique_id LIKE', $prefix . '%');
    $CI->db->order_by('id', 'DESC');
    $CI->db->limit(1);
    $query = $CI->db->get();
    
    $next_number = 1;
    
    if ($query->num_rows() > 0) {
        $last_unique_id = $query->row()->unique_id;
        // Extract number from the unique_id (remove prefix)
        $last_number = (int) substr($last_unique_id, strlen($prefix));
        $next_number = $last_number + 1;
    }
    
    // Generate unique ID with zero padding (6 digits)
    $unique_id = $prefix . str_pad($next_number, 6, '0', STR_PAD_LEFT);
    
    // Double check if this ID already exists (in case of concurrent requests)
    $check_query = $CI->db->where('unique_id', $unique_id)->get($table);
    if ($check_query->num_rows() > 0) {
        // If exists, try with next number
        $next_number++;
        $unique_id = $prefix . str_pad($next_number, 6, '0', STR_PAD_LEFT);
    }
    
    return $unique_id;
}
