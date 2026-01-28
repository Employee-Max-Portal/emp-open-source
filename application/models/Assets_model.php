<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Assets_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

   public function asset_save($data)
	{

		$insert = array(
			'branch_id'     => $data['branch_id'],
			'asset_type'   => $data['category_id'],
			'assigned_to'   => $data['staff_id'],
			'asset_name'    => $data['asset_name'],
			'serial_number' => $data['serial_number'],
			'brand'         => $data['brand'],
			'price'         => $data['price'],
			'purchase_date' => date('Y-m-d', strtotime($data['purchase_date'])),
			'status'        => 'available',
		);

		// Photo Upload
		if (!empty($_FILES['photo']['name'])) {
			$config['upload_path'] = 'uploads/asset_photos/';
			$config['allowed_types'] = 'jpg|jpeg|png';
			$config['file_name'] = 'asset_' . app_generate_hash();
			$this->upload->initialize($config);
			if ($this->upload->do_upload('photo')) {
				$insert['photo'] = $this->upload->data('file_name');
			}
		}

		 if (!isset($data['assets_id'])) {
				$this->db->insert('assets', $insert);
			} else {
				if ($_FILES['photo']['name'] != "") {
					if (!empty($data['old_file'])) {
						$file = 'uploads/asset_photos/' . $data['old_file'];
						if (file_exists($file)) {
							@unlink($file);
						}
					}
				}
				$this->db->where('id', $data['assets_id']);
				$this->db->update('assets', $insert);
			}


		return $this->db->affected_rows() > 0;
	}

   public function category_save($data)
	{
		$arrayData = array(
			'name' => $data['name'],
			'branch_id' => is_superadmin_loggedin() ? $data['branch_id'] : $this->application_model->get_branch_id(),
		);

		if (!isset($data['category_id']) || empty($data['category_id'])) {
			// INSERT new category
			$this->db->insert('assets_category', $arrayData);
		} else {
			// UPDATE existing category
			$this->db->where('id', $data['category_id']);
			$this->db->update('assets_category', $arrayData);
		}
	}

}
