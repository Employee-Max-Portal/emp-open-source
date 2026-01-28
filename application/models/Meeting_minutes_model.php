<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Meeting_minutes_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function save($data)
    {
        $insert_data = array(
            'meeting_host' => $data['meeting_host'],
            'title' => $data['title'],
            'date' => date('Y-m-d', strtotime($data['date'])),
            'meeting_type' => $data['meeting_type'],
            'participants' => isset($data['staff_id']) ? implode(',', $data['staff_id']) : '',
            'summary' => $data['summary'],
            'created_by' => $data['created_by']
        );

        if (isset($data['attachments'])) {
            $insert_data['attachments'] = $data['attachments'];
        }

        if (isset($data['id']) && !empty($data['id'])) {
            $insert_data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->where('id', $data['id']);
            $this->db->update('meeting_minutes', $insert_data);
        } else {
            $this->db->insert('meeting_minutes', $insert_data);
        }
    }


	public function get_meetings($userRole, $start_date = '', $end_date = '')
	{
		$this->db->select('mm.*, s.name as created_by_name');
		$this->db->from('meeting_minutes mm');
		$this->db->join('staff s', 's.id = mm.created_by', 'left');

		// Subquery to get participant names correctly
		$this->db->select("(
			SELECT GROUP_CONCAT(sp.name SEPARATOR ', ')
			FROM staff sp
			WHERE FIND_IN_SET(sp.id, mm.participants)
		) AS participant_names", false);

		// Role-based filter: only show public meetings if not privileged role
		if (!in_array($userRole, [1,2,3,5,8])) {
			$this->db->where('mm.meeting_type', 'public');
		}


		// Date filter if provided
		if (!empty($start_date) && !empty($end_date)) {
			$this->db->where('mm.date >=', $start_date);
			$this->db->where('mm.date <=', $end_date);
		}

		$this->db->order_by('mm.date', 'DESC');
		$this->db->group_by('mm.id');

		return $this->db->get()->result_array();
	}


    public function get_single($id)
    {
        $this->db->select('mm.*, s.name as created_by_name');
        $this->db->from('meeting_minutes mm');
        $this->db->join('staff s', 's.id = mm.created_by', 'left');
        $this->db->where('mm.id', $id);

        $result = $this->db->get()->row_array();

        if (!empty($result) && !empty($result['participants'])) {
            $participant_ids = explode(',', $result['participants']);
            $this->db->select('name');
            $this->db->from('staff');
            $this->db->where_in('id', $participant_ids);
            $participants = $this->db->get()->result_array();
            $result['participant_names'] = array_column($participants, 'name');
        }

        return $result;
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('meeting_minutes');
    }

	     public function get_latest_summaries($userRole, $limit = 10)
    {
        $this->db->select('mm.id, mm.title, mm.date, mm.summary, mm.meeting_type, s.name as created_by_name');
        $this->db->from('meeting_minutes mm');
        $this->db->join('staff s', 's.id = mm.created_by', 'left');

        $this->db->select("(
            SELECT GROUP_CONCAT(sp.name SEPARATOR ', ')
            FROM staff sp
            WHERE FIND_IN_SET(sp.id, mm.participants)
        ) AS participant_names", false);

        if (!in_array($userRole, [1,2,3,5,8])) {
            $this->db->where('mm.meeting_type', 'public');
        }

        $this->db->order_by('mm.date', 'DESC');
        $this->db->limit($limit);

        return $this->db->get()->result_array();
    }

}