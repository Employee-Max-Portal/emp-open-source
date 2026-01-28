<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Goals extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('goals_model');
    }

    // Main dashboard view
    public function index() {
        $this->data['goals'] = $this->goals_model->get_all_goals_with_metrics();
        $this->data['title'] = 'Goals Dashboard';
        $this->data['sub_page'] = 'goals/dashboard';
        $this->data['main_menu'] = 'dashboard';
        $this->load->view('layout/index', $this->data);
    }

    // Get live data for dashboard refresh
    public function get_live_data() {
        header('Content-Type: application/json');

        $goals = $this->goals_model->get_all_goals_with_metrics();
        echo json_encode([
            'status' => 'success',
            'goals' => $goals,
            'last_updated' => date('H:i:s')
        ]);
    }

    // Update execution stage
    public function update_stage() {
        header('Content-Type: application/json');

        $goal_id = $this->input->post('goal_id');
        $stage = $this->input->post('stage');
        $next_stage = $this->input->post('next_stage');
        $justification = $this->input->post('justification');
        $user_id = get_loggedin_user_id();

        if (empty($goal_id) || empty($stage) || empty($justification)) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
            return;
        }

        // Check if user is pod owner or admin
        $goal = $this->goals_model->get_goal_by_id($goal_id);
        if (!$goal || ($goal['pod_owner_id'] != $user_id && !is_superadmin_loggedin())) {
            echo json_encode(['status' => 'error', 'message' => 'Access denied']);
            return;
        }

        $result = $this->goals_model->update_execution_stage($goal_id, $stage, $next_stage, $justification, $user_id);

        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Stage updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update stage']);
        }
    }

    // Get goal tasks for modal
    public function get_goal_tasks() {
        header('Content-Type: application/json');

        $goal_id = $this->input->post('goal_id');
        if (empty($goal_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Goal ID required']);
            return;
        }

        // Get milestone IDs for this goal
        $this->db->select('milestone_id');
        $this->db->where('goal_id', $goal_id);
        $milestone_ids = array_column($this->db->get('goal_milestones')->result_array(), 'milestone_id');

        if (empty($milestone_ids)) {
            echo json_encode([
                'status' => 'success',
                'tasks' => [],
                'debug' => 'No milestones associated with this goal'
            ]);
            return;
        }

        // Get tasks from tracker_issues using the same approach as tasks dashboard
        $this->db->select('ti.*, s.name as staff_name, s.photo, tt.name as task_type_name, td.title as department_name');
        $this->db->from('tracker_issues ti');
        $this->db->join('staff s', 's.id = ti.assigned_to', 'left');
        $this->db->join('task_types tt', 'tt.id = ti.task_type', 'left');
        $this->db->join('tracker_departments td', 'td.identifier = ti.department', 'left');
        $this->db->where_in('ti.milestone', $milestone_ids);
        $this->db->where_not_in('ti.task_status', ['completed', 'cancelled']);
        $this->db->order_by('ti.task_status', 'ASC');
        $this->db->order_by('ti.logged_at', 'DESC');
        $tasks = $this->db->get()->result_array();

        echo json_encode([
            'status' => 'success',
            'tasks' => $tasks,
            'milestone_ids' => $milestone_ids,
            'debug' => 'Found ' . count($tasks) . ' tasks from ' . count($milestone_ids) . ' milestones'
        ]);
    }

    // Save target (add/edit)
    public function save_target() {
        header('Content-Type: application/json');

        $goal_id = $this->input->post('goal_id');
        $target_name = $this->input->post('target_name');
        $target_value = $this->input->post('target_value');
        $target_id = $this->input->post('target_id');
        $user_id = get_loggedin_user_id();

        if (empty($goal_id) || empty($target_name)) {
            echo json_encode(['status' => 'error', 'message' => 'Goal ID and target name required']);
            return;
        }

        // Check if user is pod owner or admin
        $goal = $this->goals_model->get_goal_by_id($goal_id);
        if (!$goal || ($goal['pod_owner_id'] != $user_id && !is_superadmin_loggedin())) {
            echo json_encode(['status' => 'error', 'message' => 'Access denied']);
            return;
        }

        $result = $this->goals_model->save_target($goal_id, $target_name, $target_value, $target_id);

        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Target saved successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save target']);
        }
    }

    // Add financial entry
    public function add_financial() {
        header('Content-Type: application/json');

        $goal_id = $this->input->post('goal_id');
        $type = $this->input->post('type'); // cost or revenue
        $amount = $this->input->post('amount');
        $description = $this->input->post('description');
        $date = $this->input->post('date') ?: date('Y-m-d');
        $user_id = get_loggedin_user_id();

        if (empty($goal_id) || empty($type) || empty($amount)) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
            return;
        }

        // Check if user is pod owner or admin
        $goal = $this->goals_model->get_goal_by_id($goal_id);
        if (!$goal || ($goal['pod_owner_id'] != $user_id && !is_superadmin_loggedin())) {
            echo json_encode(['status' => 'error', 'message' => 'Access denied']);
            return;
        }

        $result = $this->goals_model->add_financial_entry($goal_id, $type, $amount, $description, $date);

        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Financial entry added successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add financial entry']);
        }
    }

    // Goal detail page
    public function detail($goal_id) {
        $goal = $this->goals_model->get_goal_with_metrics($goal_id);
        if (!$goal) {
            show_404();
        }

        $this->data['goal'] = $goal;
        $this->data['title'] = 'Goal Details - ' . $goal['goal_name'];
        $this->data['sub_page'] = 'goals/detail';
        $this->data['main_menu'] = 'goals';
        $this->load->view('layout/index', $this->data);
    }

    // Goals management page
    public function manage() {
        $this->data['goals'] = $this->goals_model->get_all_goals();
        $this->db->select('s.id, s.name');
        $this->db->from('staff AS s');
        $this->db->join('login_credential AS lc', 'lc.user_id = s.id');
        $this->db->where('lc.active', 1); // only active users
        $this->db->where_not_in('lc.role', [1, 9, 10, 11, 12]); // exclude certain roles
        $this->db->where_not_in('s.id', [49]); // exclude specific staff ID
        $this->db->order_by('s.name', 'ASC');

        $this->data['staff'] = $this->db->get()->result_array();

        $this->data['milestones'] = $this->db->get('tracker_milestones')->result_array();
        $this->data['title'] = 'Manage Goals';
        $this->data['sub_page'] = 'goals/manage';
        $this->data['main_menu'] = 'goals';
        $this->load->view('layout/index', $this->data);
    }

    // Add/Edit goal
    public function save() {
        $is_ajax = $this->input->is_ajax_request();

        $goal_id = $this->input->post('goal_id');
        $goal_name = $this->input->post('goal_name');
        $goal_category = $this->input->post('goal_category');
        $description = $this->input->post('description');
        $pod_owner_id = $this->input->post('pod_owner_id');
        $execution_stage = $this->input->post('execution_stage');
        $targets = $this->input->post('targets');
        $target_values = $this->input->post('target_values');
        $milestones = $this->input->post('milestones');
        $milestone_dates = $this->input->post('milestone_dates');
        $pod_members = $this->input->post('pod_members');

        if (empty($goal_name) || empty($pod_owner_id)) {
            if ($is_ajax) {
                echo json_encode(['status' => 'error', 'message' => 'Goal name and pod owner required']);
                return;
            }
            $this->session->set_flashdata('error', 'Goal name and pod owner required');
            redirect('goals/manage');
            return;
        }

        $result = $this->goals_model->save_goal($goal_id, $goal_name, $goal_category, $description, $pod_owner_id, $execution_stage);

        if ($result) {
            $saved_goal_id = $goal_id ?: $this->db->insert_id();

            // Save targets
            if (!empty($targets)) {
                $this->goals_model->save_goal_targets($saved_goal_id, $targets, $target_values);
            }

            // Save milestones - handle empty array
            $this->goals_model->save_goal_milestones($saved_goal_id, $milestones, $milestone_dates);

            // Save pod members - handle empty array
            $this->goals_model->save_pod_members($saved_goal_id, $pod_members, $pod_owner_id);

            // Handle attachment deletions
            $delete_attachments = $this->input->post('delete_attachments');
            if (!empty($delete_attachments)) {
                foreach ($delete_attachments as $attachment_id) {
                    $attachment = $this->goals_model->get_attachment($attachment_id);
                    if ($attachment) {
                        $file_path = './uploads/attachments/goals/' . $attachment['enc_file_name'];
                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                        $this->goals_model->delete_attachment($attachment_id);
                    }
                }
            }

            // Handle file uploads - check if files are actually uploaded
            if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
                $this->handle_attachments($saved_goal_id);
            }

            if ($is_ajax) {
                $goal = $this->goals_model->get_goal_data($saved_goal_id);

                // Get pod owner name
                $this->db->select('name');
                $this->db->where('id', $goal['pod_owner_id']);
                $owner = $this->db->get('staff')->row_array();
                $goal['pod_owner_name'] = $owner ? $owner['name'] : 'Unknown';

                echo json_encode([
                    'status' => 'success',
                    'message' => $goal_id ? 'Goal updated successfully' : 'Goal created successfully',
                    'goal' => $goal
                ]);
                return;
            }

            if ($goal_id) {
                $this->session->set_flashdata('success', 'Information Has Been Updated Successfully');
            } else {
                $this->session->set_flashdata('success', 'Information Has Been Saved Successfully');
            }
        } else {
            if ($is_ajax) {
                echo json_encode(['status' => 'error', 'message' => 'Failed to save goal']);
                return;
            }
            $this->session->set_flashdata('error', 'Failed to save goal');
        }

        redirect('goals/manage');
    }

    private function handle_attachments($goal_id) {
        $config['upload_path'] = './uploads/attachments/goals/';
        $config['allowed_types'] = 'pdf|doc|docx|jpg|png|xlsx|xls|jpeg|gif|bmp';
        $config['max_size'] = 10240; // 10MB
        $config['encrypt_name'] = true;

        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, true);
        }

        $this->load->library('upload', $config);

        $files = $_FILES['attachments'];
        $file_count = count($files['name']);

        for ($i = 0; $i < $file_count; $i++) {
            if (!empty($files['name'][$i])) {
                $_FILES['single_file']['name'] = $files['name'][$i];
                $_FILES['single_file']['type'] = $files['type'][$i];
                $_FILES['single_file']['tmp_name'] = $files['tmp_name'][$i];
                $_FILES['single_file']['error'] = $files['error'][$i];
                $_FILES['single_file']['size'] = $files['size'][$i];

                $this->upload->initialize($config);

                if ($this->upload->do_upload('single_file')) {
                    $upload_data = $this->upload->data();
                    $this->goals_model->save_attachment(
                        $goal_id,
                        $upload_data['orig_name'],
                        $upload_data['file_name'],
                        $upload_data['file_size']
                    );
                } else {
                    $error = $this->upload->display_errors();
                    log_message('error', 'Goal attachment upload failed: ' . $error);
                    $this->session->set_flashdata('error', 'File upload failed: ' . strip_tags($error));
                }
            }
        }
    }

    // Delete attachment
    public function delete_attachment() {
        header('Content-Type: application/json');

        $attachment_id = $this->input->post('attachment_id');
        if (empty($attachment_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Attachment ID required']);
            return;
        }

        // Get attachment details
        $attachment = $this->goals_model->get_attachment($attachment_id);
        if (!$attachment) {
            echo json_encode(['status' => 'error', 'message' => 'Attachment not found']);
            return;
        }

        // Check permissions
        $goal = $this->goals_model->get_goal_by_id($attachment['goal_id']);
        $user_id = get_loggedin_user_id();
        if (!$goal || ($goal['pod_owner_id'] != $user_id && !is_superadmin_loggedin())) {
            echo json_encode(['status' => 'error', 'message' => 'Access denied']);
            return;
        }

        // Delete file from filesystem
        $file_path = './uploads/attachments/goals/' . $attachment['enc_file_name'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Delete from database
        $result = $this->goals_model->delete_attachment($attachment_id);

        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Attachment deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete attachment']);
        }
    }

    // Download/view attachment
    public function download_attachment($attachment_id) {
        $attachment = $this->goals_model->get_attachment($attachment_id);
        if (!$attachment) {
            show_404();
        }

        $file_path = './uploads/attachments/goals/' . $attachment['enc_file_name'];
        if (!file_exists($file_path)) {
            show_404();
        }

        $this->load->helper('download');
        $file_data = file_get_contents($file_path);
        force_download($attachment['orig_file_name'], $file_data);
    }
    public function delete($goal_id = null) {
        if ($goal_id) {
            $result = $this->goals_model->delete_goal($goal_id);

            if ($result) {
                $this->session->set_flashdata('success', 'Information Has Been Deleted Successfully');
            } else {
                $this->session->set_flashdata('error', 'Failed to delete goal');
            }

            redirect('goals/manage');
        } else {
            header('Content-Type: application/json');

            $goal_id = $this->input->post('goal_id');
            if (empty($goal_id)) {
                echo json_encode(['status' => 'error', 'message' => 'Goal ID required']);
                return;
            }

            $result = $this->goals_model->delete_goal($goal_id);

            if ($result) {
                echo json_encode(['status' => 'success', 'message' => 'Goal deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete goal']);
            }
        }
    }

    // Get goal data for editing
    public function get_goal_data() {
        header('Content-Type: application/json');

        $goal_id = $this->input->post('goal_id');
        if (empty($goal_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Goal ID required']);
            return;
        }

        $goal = $this->goals_model->get_goal_data($goal_id);
        if (!$goal) {
            echo json_encode(['status' => 'error', 'message' => 'Goal not found']);
            return;
        }

        // Get pod owner name
        $this->db->select('name');
        $this->db->where('id', $goal['pod_owner_id']);
        $owner = $this->db->get('staff')->row_array();
        $goal['pod_owner_name'] = $owner ? $owner['name'] : 'Unknown';

        // Get targets
        $targets = $this->goals_model->get_goal_targets($goal_id);

        // Get milestones
        $this->db->select('milestone_id');
        $this->db->where('goal_id', $goal_id);
        $milestone_ids = array_column($this->db->get('goal_milestones')->result_array(), 'milestone_id');

        // Get pod members
        $this->db->select('staff_id');
        $this->db->where('goal_id', $goal_id);
        $this->db->where('role !=', 'Pod Owner');
        $member_ids = array_column($this->db->get('goal_pod_members')->result_array(), 'staff_id');

        // Get attachments
        $attachments = $this->goals_model->get_goal_attachments($goal_id);

        $goal['targets'] = $targets;
        $goal['milestone_ids'] = $milestone_ids;
        $goal['member_ids'] = $member_ids;
        $goal['attachments'] = $attachments;

        echo json_encode(['status' => 'success', 'goal' => $goal]);
    }
}