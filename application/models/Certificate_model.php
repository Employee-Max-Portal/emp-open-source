<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Certificate_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getList()
    {
        $this->db->select('*');
        $this->db->from('certificates_templete');
        $this->db->order_by('id', 'ASC');
        $result = $this->db->get()->result_array();
        return $result;
    }

    public function save($data)
    {
        $background_file = '';
        $oldBackground_file = $this->input->post('old_background_file');
        if (isset($_FILES["background_file"]) && !empty($_FILES['background_file']['name'])) {
            $config['upload_path'] = './uploads/certificate/';
            $config['allowed_types'] = 'jpg|png';
            $config['overwrite'] = false;
            $this->upload->initialize($config);
            if ($this->upload->do_upload("background_file")) {
                // need to unlink previous photo
                if (!empty($oldBackground_file)) {
                    $unlink_path = 'uploads/certificate/' . $oldBackground_file;
                    if (file_exists($unlink_path)) {
                        @unlink($unlink_path);
                    }
                }
                $background_file = $this->upload->data('file_name');
            }
        } else {
            if (!empty($oldBackground_file)) {
                $background_file = $oldBackground_file;
            }
        }

        $logo_file = '';
        $oldLogo_file = $this->input->post('old_logo_file');
        if (isset($_FILES["logo_file"]) && !empty($_FILES['logo_file']['name'])) {
            $config['upload_path'] = './uploads/certificate/';
            $config['allowed_types'] = 'jpg|png';
            $config['overwrite'] = false;
            $this->upload->initialize($config);
            if ($this->upload->do_upload("logo_file")) {
                // need to unlink previous photo
                if (!empty($oldLogo_file)) {
                    $unlink_path = 'uploads/certificate/' . $oldLogo_file;
                    if (file_exists($unlink_path)) {
                        @unlink($unlink_path);
                    }
                }
                $logo_file = $this->upload->data('file_name');
            }
        } else {
            if (!empty($oldLogo_file)) {
                $logo_file = $oldLogo_file;
            }
        }

        $signature_file = '';
        $oldSignature_file = $this->input->post('old_signature_file');
        if (isset($_FILES["signature_file"]) && !empty($_FILES['signature_file']['name'])) {
            $config['upload_path'] = './uploads/certificate/';
            $config['allowed_types'] = 'jpg|png';
            $config['overwrite'] = false;
            $this->upload->initialize($config);
            if ($this->upload->do_upload("signature_file")) {
                // need to unlink previous photo
                if (!empty($oldSignature_file)) {
                    $unlink_path = 'uploads/certificate/' . $oldSignature_file;
                    if (file_exists($unlink_path)) {
                        @unlink($unlink_path);
                    }
                }
                $signature_file = $this->upload->data('file_name');
            }
        } else {
            if (!empty($oldSignature_file)) {
                $signature_file = $oldSignature_file;
            }
        }

        $qrCode = $data['emp_qr_code'];

        $arrayLive = array(
            'name' => $data['certificate_name'],
            'purpose' => $data['purpose'],
            'user_type' => 2,
            'page_layout' => $data['page_layout'],
            'qr_code' => $qrCode,
            'top_space' => empty($data['top_space']) ? 0 : $data['top_space'],
            'bottom_space' => empty($data['bottom_space']) ? 0 : $data['bottom_space'],
            'right_space' => empty($data['right_space']) ? 0 : $data['right_space'],
            'left_space' => empty($data['left_space']) ? 0 : $data['left_space'],
            'background' => $background_file,
            'logo' => $logo_file,
            'signature' => $signature_file,
            'content' => $this->input->post('content', false),
        );
        if (!isset($data['certificate_id'])) {
            $this->db->insert('certificates_templete', $arrayLive);
        } else {
            $this->db->where('id', $data['certificate_id']);
            $this->db->update('certificates_templete', $arrayLive);
        }
    }

	// get staff all details
    public function getEmployeeList($branch_id, $role_id)
    {
        $this->db->select('staff.*,staff_designation.name as designation_name,staff_department.name as department_name,login_credential.role as role_id, roles.name as role');
        $this->db->from('staff');
        $this->db->join('login_credential', 'login_credential.user_id = staff.id and login_credential.role != 1', 'inner');
        $this->db->join('roles', 'roles.id = login_credential.role', 'left');
        $this->db->join('staff_designation', 'staff_designation.id = staff.designation', 'left');
        $this->db->join('staff_department', 'staff_department.id = staff.department', 'left');
         // Conditional filter for role
		if (!empty($role_id) && $role_id !== 'all') {
			$this->db->where('login_credential.role', $role_id);
		}

		// Conditional filter for branch
		if (!empty($branch_id) && $branch_id !== 'all') {
			$this->db->where('staff.branch_id', $branch_id);
		}

		//$this->db->where('login_credential.active', 1);
		$this->db->where('login_credential.role !=', 9);
        return $this->db->get()->result();
    }

    public function tagsList($roleID = "")
    {
        $arrayTags = array();
        $arrayTags[] = '{name}';
        $arrayTags[] = '{gender}';
        if ($roleID == 2) {
            $arrayTags[] = '{staff_photo}';
            $arrayTags[] = '{staff_id}';
            $arrayTags[] = '{joining_date}';
            $arrayTags[] = '{designation}';
            $arrayTags[] = '{department}';
            $arrayTags[] = '{qualification}';
            $arrayTags[] = '{total_experience}';
        }
        $arrayTags[] = '{religion}';
        $arrayTags[] = '{blood_group}';
        $arrayTags[] = '{birthday}';
        $arrayTags[] = '{email}';
        $arrayTags[] = '{mobileno}';
        $arrayTags[] = '{present_address}';
        $arrayTags[] = '{permanent_address}';
        $arrayTags[] = '{logo}';
        $arrayTags[] = '{signature}';
        $arrayTags[] = '{qr_code}';
        $arrayTags[] = '{institute_name}';
        $arrayTags[] = '{institute_email}';
        $arrayTags[] = '{institute_address}';
        $arrayTags[] = '{institute_mobile_no}';
        $arrayTags[] = '{purpose}';
        $arrayTags[] = '{print_date}';
        $arrayTags[] = '{today}';
        return $arrayTags;
    }

    public function tagsReplace($roleID, $userID, $templete, $print_date)
    {
        $body = $templete['content'];
        $photo_size = $templete['photo_size'];
        $photo_style = $templete['photo_style'];
        $tags = $this->tagsList($roleID);
		if ($roleID == 2) {
            $userDetails = $this->getStaff($userID);


        }
        $arr = array('{', '}');
        foreach ($tags as $tag) {
            $field = str_replace($arr, '', $tag);
			if ($field == 'staff_photo') {
				$photo = '<img class="' . ($photo_style == 1 ? '' : 'rounded') . '" src="' . get_image_url('staff', $userDetails['photo']) . '" width="' . $photo_size . '">';
				$body = str_replace($tag, $photo, $body);
			} else if ($field == 'logo') {
				if (!empty($templete['logo'])) {
					$logo_ph = '<img src="' . base_url('uploads/certificate/' . $templete['logo']) . '">';
					$body = str_replace($tag, $logo_ph, $body);
				}
			} else if ($field == 'signature') {
				if (!empty($templete['signature'])) {
					$signature_ph = '<img src="' . base_url('uploads/certificate/' . $templete['signature']) . '">';
					$body = str_replace($tag, $signature_ph, $body);
				}
			} else if ($field == 'print_date') {
				$body = str_replace($tag, date('Y-m-d', strtotime($print_date)), $body);
			} else if ($field == 'purpose') {
				$body = str_replace($tag, $templete['purpose'], $body);
			} else if ($field == 'qr_code') {
				if (!empty($templete['qr_code'])) {
					$qr_code = $templete['qr_code'];
					$params['savename'] = 'uploads/qr_code/sta_' . $userDetails['id'] . '.png';
					$params['level'] = 'M';
					$params['size'] = 4;
					$params['data'] = ucfirst($qr_code) . " - " . $userDetails[$qr_code];
					$qrCode = $this->ciqrcode->generate($params);
					$photo = '<img src="' . base_url($qrCode) . '">';
					$body = str_replace($tag, $photo, $body);
				}
			} else if ($field == 'today') {
				// Check if user is inactive and get separation date
				$this->db->select('active');
				$this->db->from('login_credential');
				$this->db->where('user_id', $userID);
				$login_status = $this->db->get()->row_array();

				if ($login_status && $login_status['active'] != 1) {
					$this->db->select('last_working_date');
					$this->db->from('separation_requests');
					$this->db->where('user_id', $userID);
					$separation = $this->db->get()->row_array();
					if ($separation && !empty($separation['last_working_date'])) {
						$print_date = $separation['last_working_date'];
					}
				}
				$body = str_replace($tag, date('Y-m-d', strtotime($print_date)), $body);
			} else if ($field == 'gender') {
				$body = str_replace($tag, $userDetails['sex'], $body);
			} else {
				$body = str_replace($tag, $userDetails[$field], $body);
			}

        }
        return $body;
    }

    public function getStaff($id)
    {
        $this->db->select('s.*,s.department as deid,s.designation as desid,staff_department.name as department,staff_designation.name as designation,br.name as institute_name,br.email as institute_email,br.address as institute_address,br.mobileno as institute_mobile_no');
        $this->db->from('staff as s');
        $this->db->join('staff_department', 'staff_department.id = s.department', 'left');
        $this->db->join('staff_designation', 'staff_designation.id = s.designation', 'left');
        $this->db->join('branch as br', 'br.id = s.branch_id', 'left');
        $this->db->where('s.id', $id);
        $query = $this->db->get();
        return $query->row_array();
    }
}