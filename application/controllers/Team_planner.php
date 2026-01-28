<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Team_planner extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('team_planner_model');
    }

    public function index()
    {
        if (!get_permission('team_planner', 'is_view')) {
            access_denied();
        }

        // Check if mobile device
        if ($this->is_mobile()) {
            return $this->mobile();
        }

        $this->data['departments'] = $this->get_filtered_departments();
        $this->data['user_dept_id'] = $this->get_user_department_id();
        $this->data['title'] = 'Team Planner';
        $this->data['sub_page'] = 'team_planner/index';
        $this->data['main_menu'] = 'team_planner';
        $this->load->view('layout/index', $this->data);
    }

    public function mobile()
    {
        if (!get_permission('team_planner', 'is_view')) {
            access_denied();
        }

        $this->data['departments'] = $this->get_filtered_departments();
        $this->data['user_dept_id'] = $this->get_user_department_id();
        $this->data['title'] = 'Team Planner Mobile';
        $this->data['sub_page'] = 'team_planner/mobile';
        $this->data['main_menu'] = 'team_planner';
        $this->load->view('layout/index', $this->data);
    }

    public function card()
    {
        if (!get_permission('team_planner', 'is_view')) {
            access_denied();
        }

        $this->data['departments'] = $this->get_filtered_departments();
        $this->data['user_dept_id'] = $this->get_user_department_id();
        $this->data['title'] = 'Team Planner';
        $this->data['sub_page'] = 'team_planner/card_view';
        $this->data['main_menu'] = 'team_planner';
        $this->load->view('layout/index', $this->data);
    }


    private function is_mobile()
    {
        $user_agent = $this->input->server('HTTP_USER_AGENT');
        return preg_match('/(android|iphone|ipad|mobile|tablet)/i', $user_agent);
    }

    public function force_mobile()
    {
        return $this->mobile();
    }

    private function get_filtered_departments()
    {
        $branch_id = $this->session->userdata('loggedin_branch');

        // Get all departments that have active staff members in the same branch
        $query = $this->db->select('sd.id, sd.name')
                          ->distinct()
                          ->from('staff_department sd')
                          ->join('staff s', 's.department = sd.id', 'inner')
                          ->join('login_credential lc', 's.id = lc.user_id', 'inner')
                          ->where('s.branch_id', $branch_id)
                          ->where('lc.active', 1)
                          ->where('lc.role !=', 9)
                          ->where('lc.user_id !=', 37)
                          ->order_by('sd.name', 'ASC')
                          ->get();

        return $query ? $query->result() : [];
    }

    private function get_user_department_id()
    {
        $logged_user_id = $this->session->userdata('loggedin_userid');
        $staff_dept = $this->db->select('department')
                              ->where('id', $logged_user_id)
                              ->get('staff')
                              ->row();
        return $staff_dept ? $staff_dept->department : null;
    }

    public function get_team_data()
    {
        try {
            $department_id = $this->input->get('department_id');
            $start = $this->input->get('start');
            $end = $this->input->get('end');

            // Validate inputs
            if (!$department_id || !is_numeric($department_id)) {
                $this->output->set_status_header(400)
                           ->set_content_type('application/json')
                           ->set_output(json_encode(['error' => 'Invalid department ID']));
                return;
            }

            if (!$start || !$end) {
                $this->output->set_status_header(400)
                           ->set_content_type('application/json')
                           ->set_output(json_encode(['error' => 'Start and end dates are required']));
                return;
            }

            // Validate date format
            if (!DateTime::createFromFormat('Y-m-d', $start) || !DateTime::createFromFormat('Y-m-d', $end)) {
                $this->output->set_status_header(400)
                           ->set_content_type('application/json')
                           ->set_output(json_encode(['error' => 'Invalid date format']));
                return;
            }

            $team_data = $this->team_planner_model->get_team_schedule($department_id, $start, $end);

            $this->output->set_content_type('application/json')
                         ->set_output(json_encode($team_data));

        } catch (Exception $e) {
            log_message('error', 'Team planner error: ' . $e->getMessage());
            $this->output->set_status_header(500)
                       ->set_content_type('application/json')
                       ->set_output(json_encode(['error' => 'Internal server error']));
        }
    }
}