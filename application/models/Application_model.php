<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Application_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function get_branch_id()
    {
        if (is_superadmin_loggedin()) {
            return $this->input->post('branch_id');
        } else {
            return get_loggedin_branch_id();
        }
    }

    public function getSQLMode()
    {
        $sql = $this->db->query('SELECT @@sql_mode as mode')->row();
        $r = strpos($sql->mode, 'ONLY_FULL_GROUP_BY') !== false ? true : false;
        return $r;
    }

    public function profilePicUpload()
    {
        if (isset($_FILES["user_photo"]) && !empty($_FILES['user_photo']['name'])) {
            $file_size = $_FILES["user_photo"]["size"];
            $file_name = $_FILES["user_photo"]["name"];
            $allowedExts = array('jpg', 'jpeg', 'png');
            $extension = pathinfo($file_name, PATHINFO_EXTENSION);
            if ($files = filesize($_FILES['user_photo']['tmp_name'])) {
                if (!in_array(strtolower($extension), $allowedExts)) {
                    $this->form_validation->set_message('handle_upload', translate('this_file_type_is_not_allowed'));
                    return false;
                }
                if ($file_size > 2097152) {
                    $this->form_validation->set_message('handle_upload', translate('file_size_shoud_be_less_than') . " 2048KB.");
                    return false;
                }
            } else {
                $this->form_validation->set_message('handle_upload', translate('error_reading_the_file'));
                return false;
            }
            return true;
        }
    }

    public function getUserNameByRoleID($roleID, $userID = '')
    {
        if ($roleID == 6) {
            $sql = "SELECT `name`,`email`,`mobileno`,`photo`,`branch_id` FROM `parent` WHERE `id` = " . $this->db->escape($userID);
            return $this->db->query($sql)->row_array();
        } elseif ($roleID == 7) {
            $sql = "SELECT `student`.`id`, `mobileno`, CONCAT_WS(' ',`student`.`first_name`, `student`.`last_name`) as `name`, `student`.`email`, `student`.`photo`, `enroll`.`branch_id` FROM `student` INNER JOIN `enroll` ON `enroll`.`student_id` = `student`.`id` AND `enroll`.`session_id` = " . $this->db->escape(get_session_id()) . " WHERE `student`.`id` = " . $this->db->escape($userID);
            return $this->db->query($sql)->row_array();
        } else {
            $sql = "SELECT `name`,`mobileno`,`email`,`photo`,`branch_id` FROM `staff` WHERE `id` = " . $this->db->escape($userID);
            return $this->db->query($sql)->row_array();
        }
    }

    public function getLangImage($id = '', $thumb = true)
    {
        $file_path = 'uploads/language_flags/flag_' . $id . '_thumb.png';
        if (file_exists($file_path)) {
            if ($thumb == true) {
                $image_url = base_url($file_path);
            } else {
                $image_url = base_url('uploads/language_flags/flag_' . $id . '.png');
            }
        } else {
            if ($thumb == true) {
                $image_url = base_url('uploads/language_flags/defualt_thumb.png');
            } else {
                $image_url = base_url('uploads/language_flags/defualt.png');
            }
        }
        return $image_url;
    }

    public function get_asset_photo($name)
    {
        if (empty($name)) {
            $image_url = base_url('uploads/asset_photos/defualt.png');
        } else {
            $file_path = 'uploads/asset_photos/' . $name;
            if (file_exists($file_path)) {
                $image_url = base_url($file_path);
            } else {
                $image_url = base_url('uploads/asset_photos/defualt.png');
            }
        }
        return $image_url;
    }

    public function get_book_cover_image($name)
    {
        if (empty($name)) {
            $image_url = base_url('uploads/book_cover/defualt.png');
        } else {
            $file_path = 'uploads/book_cover/' . $name;
            if (file_exists($file_path)) {
                $image_url = base_url($file_path);
            } else {
                $image_url = base_url('uploads/book_cover/defualt.png');
            }
        }
        return $image_url;
    }

    public function getBranchImage($id = '', $type = 'logo')
    {
        $file_path = 'uploads/app_image/' . $type . '-' . $id . '.png';
        if (file_exists($file_path) && !empty($id)) {
            $image_url = base_url($file_path);
        } else {
            $image_url = base_url("uploads/app_image/$type.png");
        }
        return $image_url;
    }

    public function checkArrayDBVal($data, $table)
    {
        if (!empty($data)) {
            return $data;
        }

        $config = array();
        $result = $this->db->list_fields($table);
        foreach ($result as $key => $value) {
            $config[$value] = "";
        }
        return $config;
    }

    public function getWeekends($school_id = '')
    {
        if (!empty($school_id)) {
            $r = $this->db->select('weekends')->where('id', $school_id)->get('branch')->row();
            if (!empty($r)) {
                return $r->weekends;
            } else {
                return "";
            }
        }
        return "";
    }
}
