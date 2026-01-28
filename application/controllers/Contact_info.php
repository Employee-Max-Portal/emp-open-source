<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Contact_info extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('contact_info_model');
    }

    public function index()
    {
        if (!get_permission('contact_info', 'is_view')) {
            access_denied();
        }

        $this->data['title'] = translate('contact_info');
        $this->data['sub_page'] = 'contact_info/index';
        $this->data['main_menu'] = 'contact_info';
        $this->load->view('layout/index', $this->data);
    }

    public function save()
    {
        $contact_id = $this->input->post('contact_id');
        $permission_type = !empty($contact_id) ? 'is_edit' : 'is_add';

        if (!get_permission('contact_info', $permission_type)) {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['status' => 'error', 'message' => translate('access_denied')]);
                return;
            }
            access_denied();
        }

        if ($_POST) {
            $this->form_validation->set_rules('client_name', translate('client_name'), 'trim|required');
            $this->form_validation->set_rules('email', translate('email'), 'trim|required|valid_email');
            $this->form_validation->set_rules('phone', translate('phone'), 'trim|required');

            if ($this->form_validation->run() !== false) {
                $data = array(
                    'client_name' => $this->input->post('client_name'),
                    'email' => $this->input->post('email'),
                    'phone' => $this->input->post('phone'),
                    'address' => $this->input->post('address'),
                    'company' => $this->input->post('company')
                );

                if (!empty($contact_id)) {
                    $data['updated_at'] = date('Y-m-d H:i:s');
                    $this->contact_info_model->save($data, $contact_id);
                    $message = translate('information_has_been_updated_successfully');
                } else {
                    $data['created_at'] = date('Y-m-d H:i:s');
                    $data['created_by'] = get_loggedin_user_id();
                    $this->contact_info_model->save($data);
                    $message = translate('information_has_been_saved_successfully');
                }

                if ($this->input->is_ajax_request()) {
                    echo json_encode(['status' => 'success', 'message' => $message]);
                    return;
                }

                set_alert('success', $message);
                redirect(base_url('contact_info'));
            } else {
                if ($this->input->is_ajax_request()) {
                    echo json_encode(['status' => 'error', 'message' => strip_tags(validation_errors())]);
                    return;
                }
            }
        }

        if ($this->input->is_ajax_request()) {
            echo json_encode(['status' => 'error', 'message' => 'No data received']);
            return;
        }

        redirect(base_url('contact_info'));
    }

    public function delete($id = '')
    {
        if (!get_permission('contact_info', 'is_delete')) {
            if ($this->input->is_ajax_request()) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => translate('access_denied')]);
                return;
            }
            access_denied();
        }

        if (!empty($id) && is_numeric($id)) {
            $this->contact_info_model->save(array('deleted' => 1), $id);
            $message = translate('information_has_been_deleted_successfully');

            if ($this->input->is_ajax_request()) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'success', 'message' => $message]);
                return;
            }

            set_alert('success', $message);
        } else {
            if ($this->input->is_ajax_request()) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Invalid contact ID']);
                return;
            }
        }

        if (!$this->input->is_ajax_request()) {
            redirect(base_url('contact_info'));
        }
    }

    public function get_single($id)
    {
        if (!get_permission('contact_info', 'is_view')) {
            echo json_encode(['error' => 'Access denied']);
            return;
        }

        header('Content-Type: application/json');

        if (empty($id) || !is_numeric($id)) {
            echo json_encode(['error' => 'Invalid contact ID']);
            return;
        }

        $contact = $this->contact_info_model->get_single_contact($id);
        echo json_encode($contact);
    }

    public function ajax_delete()
    {
        header('Content-Type: application/json');

        if (!get_permission('contact_info', 'is_delete')) {
            echo json_encode(['status' => 'error', 'message' => translate('access_denied')]);
            return;
        }

        $id = $this->input->post('id');

        if (!empty($id) && is_numeric($id)) {
            $this->contact_info_model->save(array('deleted' => 1), $id);
            echo json_encode(['status' => 'success', 'message' => translate('information_has_been_deleted_successfully')]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid contact ID']);
        }
    }

    public function get_list()
    {
        $contacts = $this->contact_info_model->get_contact_list();
        echo json_encode($contacts);
    }

    public function get_client_milestones($client_id)
    {
        header('Content-Type: application/json');

        if (!get_permission('contact_info', 'is_view')) {
            echo json_encode(['error' => 'Access denied']);
            return;
        }

        if (empty($client_id) || !is_numeric($client_id)) {
            echo json_encode(['error' => 'Invalid client ID']);
            return;
        }

        try {
            // Get milestones for this client
            $this->db->select('tm.*');
            $this->db->from('tracker_milestones tm');
            $this->db->where('tm.client_id', $client_id);
            $this->db->order_by('tm.created_at', 'DESC');
            $query = $this->db->get();

            if (!$query) {
                echo json_encode(['error' => 'Database query failed']);
                return;
            }

            $milestones = $query->result_array();

            if (empty($milestones)) {
                echo json_encode([]);
                return;
            }

            // Add task counts and format data for each milestone
            foreach ($milestones as &$milestone) {
                // Get total tasks
                $this->db->where('milestone', $milestone['id']);
                $milestone['total_tasks'] = $this->db->count_all_results('tracker_issues');

                // Get completed tasks
                $this->db->where('milestone', $milestone['id']);
                $this->db->where('task_status', 'completed');
                $milestone['completed_tasks'] = $this->db->count_all_results('tracker_issues');

                // Calculate budget: direct cost + indirect cost
                // Direct cost from fund_requisition
                $this->db->select('SUM(amount) as total_fund');
                $this->db->from('fund_requisition');
                $this->db->where('milestone', $milestone['id']);
                $this->db->where('payment_status', '2');
                $fund_result = $this->db->get()->row();
                $direct_cost = $fund_result ? (float)$fund_result->total_fund : 0;

                // Indirect cost calculation
                $this->db->select('SUM(COALESCE(spent_time, 0)) as total_spent_time');
                $this->db->where('milestone', $milestone['id']);
                $spent_time_result = $this->db->get('tracker_issues')->row();
                $total_spent_time = $spent_time_result ? (float)$spent_time_result->total_spent_time : 0;

                // Get cost per hour from config (default 50)
                $cost_per_hour = 100;
                $config_query = $this->db->get_where('global_config', ['config_key' => 'cost_per_hour']);
                if ($config_query && $config_query->num_rows() > 0) {
                    $cost_per_hour = (float)$config_query->row()->config_value;
                }

                $indirect_cost = $total_spent_time * $cost_per_hour;
                $milestone['budget'] = $direct_cost + $indirect_cost;

                // Format dates
                if (!empty($milestone['due_date'])) {
                    $milestone['due_date'] = date('M d, Y', strtotime($milestone['due_date']));
                }

                // Add default values if missing
                $milestone['status'] = $milestone['status'] ?? 'active';
                $milestone['priority'] = $milestone['priority'] ?? 'medium';
            }

            echo json_encode($milestones);

        } catch (Exception $e) {
            echo json_encode(['error' => 'Database error']);
        }
    }

    public function get_milestone_tasks($milestone_id)
    {
        header('Content-Type: application/json');

        if (!get_permission('contact_info', 'is_view')) {
            echo json_encode(['error' => 'Access denied']);
            return;
        }

        if (empty($milestone_id) || !is_numeric($milestone_id)) {
            echo json_encode(['error' => 'Invalid milestone ID']);
            return;
        }

        try {
            // Get tasks for this milestone
            $this->db->select('ti.*, s.name as assigned_to');
            $this->db->from('tracker_issues ti');
            $this->db->join('staff s', 'ti.assigned_to = s.id', 'left');
            $this->db->where('ti.milestone', $milestone_id);
            $this->db->order_by('ti.logged_at', 'DESC');
            $query = $this->db->get();

            if (!$query) {
                echo json_encode(['error' => 'Database query failed']);
                return;
            }

            $tasks = $query->result_array();

            // Format task data to match expected structure
            foreach ($tasks as &$task) {
                $task['task_id'] = $task['unique_id'] ?? 'N/A';
                $task['estimated_hours'] = $task['estimation_time'] ?? 0;
                $task['priority'] = $task['priority_level'] ?? 'medium';
                $task['assigned_to'] = $task['assigned_to'] ?? 'Unassigned';
            }

            echo json_encode($tasks);

        } catch (Exception $e) {
            echo json_encode(['error' => 'Database error']);
        }
    }
}