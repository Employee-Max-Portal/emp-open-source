<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Library_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

public function book_save($data)
{

    $arraybook = array(
        'branch_id'    => $this->application_model->get_branch_id(),
        'title'        => $data['book_title'],
        'category_id'  => $data['category_id'],
        'email_sent'  => 1,
        'description'  => $this->input->post('description', false),
    );

    // Upload document (PDF, DOCX, Excel, etc.)
    if (isset($_FILES["document_file"]) && !empty($_FILES['document_file']['name'])) {
        $config['upload_path']   = './uploads/attachments/documents/';
        $config['allowed_types'] = 'gif|jpg|png|pdf|docx|csv|txt|xls|xlsx|xlsm';
        $config['max_size']      = '2048';
        $config['encrypt_name']  = true;

        $this->upload->initialize($config);
        if ($this->upload->do_upload("document_file")) {
            // Delete existing file if exists
            $exist_file_name = $this->input->post('exist_file_name');
            $exist_file_path = FCPATH . 'uploads/attachments/documents/' . $exist_file_name;
            if (file_exists($exist_file_path)) {
                unlink($exist_file_path);
            }

            // Set uploaded file info into the book table
            $arraybook['document_file_name'] = $this->upload->data('orig_name');
            $arraybook['document_enc_name']  = $this->upload->data('file_name');
        } else {
            set_alert('error', strip_tags($this->upload->display_errors()));
        }
    }

    // Insert or update logic
    if (empty($data['book_id'])) {
        $this->db->insert('policy', $arraybook);
    } else {
        $this->db->where('id', $data['book_id']);
        $this->db->update('policy', $arraybook);
    }

	 $notificationData = array(
		'user_id'    => '',
		'type'       => 'policy',
		'title'      => 'New Policy Document Published',
		'message'    => 'A new document titled "' . $data['book_title'] . '" has been uploaded. Please review it.',
		'url'        => base_url('library/categorized_view'),
		'is_read'    => 0,
		'created_at' => date('Y-m-d H:i:s')
	);
	$this->db->insert('notifications', $notificationData);

    return $this->db->affected_rows() > 0;
}


    public function category_save($data)
    {
        $arrayData = array(
            'name' => $data['name'],
            'branch_id' => $this->application_model->get_branch_id(),
        );
       if (!isset($data['category_id']) || empty($data['category_id'])) {
        // INSERT new category
        $this->db->insert('policy_category', $arrayData);
		} else {
            $this->db->where('id', $data['category_id']);
            $this->db->update('policy_category', $arrayData);
        }
    }

}
