<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Contact_info_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function save($data, $id = null)
    {
        if (empty($id)) {
            $this->db->insert('contact_info', $data);
            return $this->db->insert_id();
        } else {
            $this->db->where('id', $id);
            return $this->db->update('contact_info', $data);
        }
    }

    public function get_contact_list()
    {
        $this->db->select('*');
        $this->db->from('contact_info');
        $this->db->where('deleted', 0);
        $this->db->order_by('id', 'DESC');
        return $this->db->get()->result();
    }

    public function get_single_contact($id)
    {
        $this->db->select('*');
        $this->db->from('contact_info');
        $this->db->where('id', $id);
        $this->db->where('deleted', 0);
        return $this->db->get()->row();
    }
}