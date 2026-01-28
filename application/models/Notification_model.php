<?php
class Notification_model extends CI_Model
{
    public function insert($data)
    {
        $data['datetime'] = date('Y-m-d H:i:s');
        return $this->db->insert('fcm_notifications', $data);
    }

    public function get_all()
    {
        return $this->db->order_by('id', 'desc')->get('fcm_notifications')->result();
    }

    public function get($id)
    {
        return $this->db->where('id', $id)->get('fcm_notifications')->row();
    }

    public function delete($id)
    {
        return $this->db->where('id', $id)->delete('fcm_notifications');
    }
}
