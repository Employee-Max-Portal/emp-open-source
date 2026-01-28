<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Tracker_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

	public function get_timetable_tasks($date = '')
	{
		$this->db->select('*');
		$this->db->from('tracker_issues');
		$this->db->order_by('id', 'DESC');

		$query = $this->db->get(); // Execute the query
		return $query->result();   // Return the result as an array of objects
	}


	public function getLabels()
	{
		$this->db->select('*');
		$this->db->from('task_labels');
		$this->db->order_by('id', 'DESC');

		$query = $this->db->get(); // Execute the query
		return $query->result();   // Return the result as an array of objects
	}

	public function get_department_by_identifier($identifier) {
		return $this->db->get_where('tracker_departments', ['identifier' => $identifier])->row();
	}


	public function get_department_members($department_id)
	{
		$this->db->select('s.id, s.name, s.photo');
		$this->db->from('tracker_department_members dm');
		$this->db->join('staff s', 'dm.staff_id = s.id');
		$this->db->where('dm.department_id', $department_id);
		$this->db->order_by('s.name', 'asc');
		return $this->db->get()->result();
	}


	public function get_components_by_department($department_id) {
    return $this->db->where('department_id', $department_id)
                    ->order_by('id', 'desc')
                    ->get('tracker_components')
                    ->result();
	}

	public function getInitiatives()
	{
		$this->db->select('*');
		$this->db->from('tracker_components');
		$this->db->order_by('id', 'DESC');

		$query = $this->db->get(); // Execute the query
		return $query->result();   // Return the result as an array of objects
	}

	public function get_milestones_by_department($department_id) {
    return $this->db->where('department_id', $department_id)
                    ->order_by('id', 'desc')
                    ->get('tracker_milestones')
                    ->result();
	}

	public function get_component_by_id($id) {
		return $this->db->get_where('tracker_components', ['id' => $id])->row();
	}

	public function get_milestone_by_id($id) {
		$this->db->select('tm.*, ci.client_name, ci.company');
		$this->db->from('tracker_milestones tm');
		$this->db->join('contact_info ci', 'tm.client_id = ci.id', 'left');
		$this->db->where('tm.id', $id);
		return $this->db->get()->row();
	}

	public function get_all_components()
	{
		return $this->db
			->select('tracker_components.*, tracker_departments.title AS department_title')
			->from('tracker_components')
			->join('tracker_departments', 'tracker_components.department_id = tracker_departments.id', 'left')
			->order_by('tracker_components.id', 'DESC')
			->get()
			->result();
	}

	public function get_all_milestones()
	{
		return $this->db
			->select('tracker_milestones.*, tracker_departments.title AS department_title')
			->from('tracker_milestones')
			->join('tracker_departments', 'tracker_milestones.department_id = tracker_departments.id', 'left')
			->order_by('tracker_milestones.id', 'DESC')
			->get()
			->result();
	}


	public function get_all_departments_summary()
	{
		$this->db->select('d.*, COUNT(dm.id) as member_count, s.name as default_assignee_name');
		$this->db->from('tracker_departments d');
		$this->db->join('tracker_department_members dm', 'dm.department_id = d.id', 'left');
		$this->db->join('staff s', 's.id = d.assigned_issuer', 'left');
		$this->db->group_by('d.id');
		$this->db->order_by('d.title', 'asc');

		$query = $this->db->get();
		return $query->result_array();
	}

	public function is_member($department_id, $staff_id)
	{
		return $this->db->get_where('tracker_department_members', [
			'department_id' => $department_id,
			'staff_id' => $staff_id
		])->num_rows() > 0;
	}


	public function get_department_by_id($id) {
		return $this->db->get_where('tracker_departments', ['id' => $id])->row();
	}


public function get_issues_by_department($identifier, $status_filter = null)
{
    $this->db->where('label', $identifier); // assuming label is used for department matching

    if ($status_filter) {
        $this->db->where('task_status', $status_filter);
    }

    return $this->db->get('tracker_issues')->result();
}


public function get_issues($department_id, $status = null) {
    $this->db->select('*');
    $this->db->from('tracker_issues');
    $this->db->where('department', $department_id);

    if ($status && $status != 'all') {
        $this->db->where('task_status', $status);
    }

    $this->db->order_by('logged_at', 'DESC');
    return $this->db->get()->result();
}

function get_all_statuses(): array
{
    return [
        'backlog' => [
            'title' => translate('Backlog'),
            'icon' => 'fas fa-archive',
            'color' => '#6366f1',
            'bg' => '#eef2ff'
        ],
        'hold' => [
            'title' => translate('Hold'),
            'icon' => 'fas fa-pause-circle',
            'color' => '#f97316',
            'bg' => '#fff7ed'
        ],
        'todo' => [
            'title' => translate('To-Do'),
            'icon' => 'fas fa-list',
            'color' => '#0ea5e9',
            'bg' => '#e0f2fe'
        ],
        'in_progress' => [
            'title' => translate('In Progress'),
            'icon' => 'fas fa-play-circle',
            'color' => '#8b5cf6',
            'bg' => '#f3e8ff'
        ],
        'in_review' => [
            'title' => translate('In Review'),
            'icon' => 'fas fa-eye',
            'color' => '#ec4899',
            'bg' => '#fce7f3'
        ],
        'planning' => [
            'title' => translate('Planning'),
            'icon' => 'fas fa-lightbulb',
            'color' => '#14b8a6',
            'bg' => '#ccfbf1'
        ],
        'observation' => [
            'title' => translate('Observation'),
            'icon' => 'fas fa-search',
            'color' => '#f43f5e',
            'bg' => '#ffe4e6'
        ],
        'waiting' => [
            'title' => translate('Waiting'),
            'icon' => 'fas fa-hourglass-half',
            'color' => '#eab308',
            'bg' => '#fef9c3'
        ],
        'completed' => [
            'title' => translate('completed'),
            'icon' => 'fas fa-check',
            'color' => '#16a34a',
            'bg' => '#dcfce7'
        ],
        'canceled' => [
            'title' => translate('Canceled'),
            'icon' => 'fas fa-times-circle',
            'color' => '#b91c1c',
            'bg' => '#fee2e2'
        ]
    ];
}

function get_all_priorities(): array
{
    return [
        [
            'key' => 'Low',
            'title' => translate('Low'),
            'icon'  => 'fas fa-arrow-down',
            'color' => '#059669',
            'bg'    => '#d1fae5'
        ],
        [
            'key' => 'Medium',
            'title' => translate('Medium'),
            'icon'  => 'fas fa-bars',
            'color' => '#d97706',
            'bg'    => '#fed7aa'
        ],
        [
            'key' => 'High',
            'title' => translate('High'),
            'icon'  => 'fas fa-exclamation-circle',
            'color' => '#dc2626',
            'bg'    => '#fecaca'
        ],
        [
            'key' => 'Urgent',
            'title' => translate('Urgent'),
            'icon'  => 'fas fa-bolt',
            'color' => '#991b1b',
            'bg'    => '#fee2e2'
        ],
    ];
}


function get_all_priority_details(): array
{
    return [
        'Low' => [
            'title' => translate('Low'),
            'icon'  => 'fas fa-arrow-down',
            'color' => '#059669',
            'bg'    => '#d1fae5'
        ],
        'Medium' => [
            'title' => translate('Medium'),
            'icon'  => 'fas fa-bars',
            'color' => '#d97706',
            'bg'    => '#fed7aa'
        ],
        'High' => [
            'title' => translate('High'),
            'icon'  => 'fas fa-exclamation-circle',
            'color' => '#dc2626',
            'bg'    => '#fecaca'
        ],
        'Urgent' => [
            'title' => translate('Urgent'),
            'icon'  => 'fas fa-bolt',
            'color' => '#991b1b',
            'bg'    => '#fee2e2'
        ],
    ];
}

public function get_issue_counts($department_id) {
    $counts = array();

    $statuses = array('open', 'in_progress', 'in_review', 'resolved', 'closed');

    foreach ($statuses as $status) {
        $this->db->where('department', $department_id);
        $this->db->where('task_status', $status);
        $counts[$status] = $this->db->count_all_results('tracker_issues');
    }

    // Total count
    $this->db->where('department', $department_id);
    $counts['all'] = $this->db->count_all_results('tracker_issues');

    return $counts;
}

}