<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Goals_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    // Get all goals with pod members and metrics
    public function get_all_goals_with_metrics() {
        $this->db->select('g.*, s.name as pod_owner_name, s.photo as pod_owner_photo');
        $this->db->from('goals g');
        $this->db->join('staff s', 's.id = g.pod_owner_id', 'left');
        $this->db->order_by('g.id', 'ASC');
        $goals = $this->db->get()->result_array();

        foreach ($goals as &$goal) {
            $goal = $this->enrich_goal_data($goal);
        }

        return $goals;
    }

    // Get single goal with metrics
    public function get_goal_with_metrics($goal_id) {
        $this->db->select('g.*, s.name as pod_owner_name, s.photo as pod_owner_photo');
        $this->db->from('goals g');
        $this->db->join('staff s', 's.id = g.pod_owner_id', 'left');
        $this->db->where('g.id', $goal_id);
        $goal = $this->db->get()->row_array();

        if ($goal) {
            $goal = $this->enrich_goal_data($goal);
        }

        return $goal;
    }

    // Get basic goal info
    public function get_goal_by_id($goal_id) {
        $this->db->where('id', $goal_id);
        return $this->db->get('goals')->row_array();
    }

    // Enrich goal data with all metrics
    private function enrich_goal_data($goal) {
        // Get targets
        $goal['targets'] = $this->get_goal_targets($goal['id']);

        // Get pod members
        $goal['pod_members'] = $this->get_pod_members($goal['id']);

        // Get task metrics
        $goal['task_metrics'] = $this->get_task_metrics($goal['id']);

        // Get financial metrics
        $goal['financial_metrics'] = $this->get_financial_metrics($goal['id']);

        // Get attachments
        $goal['attachments'] = $this->get_goal_attachments($goal['id']);

        // Auto-update status based on activity
        $goal['status'] = $this->calculate_goal_status($goal['id']);

        return $goal;
    }

    // Get goal targets
    public function get_goal_targets($goal_id) {
        $this->db->where('goal_id', $goal_id);
        $this->db->order_by('id', 'ASC');
        return $this->db->get('goal_targets')->result_array();
    }

    // Get pod members with staff details
    public function get_pod_members($goal_id) {
        $this->db->select('gpm.*, s.name, s.photo, s.staff_id');
        $this->db->from('goal_pod_members gpm');
        $this->db->join('staff s', 's.id = gpm.staff_id', 'left');
        $this->db->where('gpm.goal_id', $goal_id);
        $this->db->order_by('gpm.role', 'ASC');
        return $this->db->get()->result_array();
    }

    // Get task metrics from tracker_issues via milestones
    public function get_task_metrics($goal_id) {
        // Get milestone IDs for this goal
        $this->db->select('milestone_id');
        $this->db->where('goal_id', $goal_id);
        $milestone_query = $this->db->get('goal_milestones');
        $milestone_ids = array_column($milestone_query->result_array(), 'milestone_id');

        if (empty($milestone_ids)) {
            return [
                'open_tasks' => 0,
                'total_hours' => 0,
                'today_hours' => 0,
                'remaining_hours' => 0
            ];
        }

        // Get open tasks count
        $this->db->where_in('milestone', $milestone_ids);
        $this->db->where_not_in('task_status', ['completed', 'canceled']);
        $open_tasks = $this->db->count_all_results('tracker_issues');

        // Get total spent hours (all time)
        $this->db->select('SUM(spent_time) as total_hours');
        $this->db->where_in('milestone', $milestone_ids);
        $total_hours_result = $this->db->get('tracker_issues')->row();
        $total_hours = $total_hours_result ? (float)$total_hours_result->total_hours : 0;

        // Get spent hours today
        $today = date('Y-m-d');
        $this->db->select('SUM(spent_time) as today_hours');
        $this->db->where_in('milestone', $milestone_ids);
        $this->db->where('DATE(logged_at)', $today);
        $today_hours_result = $this->db->get('tracker_issues')->row();
        $today_hours = $today_hours_result ? (float)$today_hours_result->today_hours : 0;

        // Get remaining hours (estimation_time - spent_time for incomplete tasks)
        $this->db->select('SUM(GREATEST(0, estimation_time - spent_time)) as remaining_hours');
        $this->db->where_in('milestone', $milestone_ids);
        $this->db->where_not_in('task_status', ['completed', 'canceled']);
        $remaining_hours_result = $this->db->get('tracker_issues')->row();
        $remaining_hours = $remaining_hours_result ? (float)$remaining_hours_result->remaining_hours : 0;

        return [
            'open_tasks' => $open_tasks,
            'total_hours' => $total_hours,
            'today_hours' => $today_hours,
            'remaining_hours' => $remaining_hours
        ];
    }

    // Get financial metrics
    public function get_financial_metrics($goal_id) {
        // Get total costs
        $this->db->select('SUM(amount) as total_cost');
        $this->db->where('goal_id', $goal_id);
        $this->db->where('type', 'cost');
        $cost_result = $this->db->get('goal_financials')->row();
        $total_cost = $cost_result ? (float)$cost_result->total_cost : 0;

        // Get total revenue
        $this->db->select('SUM(amount) as total_revenue');
        $this->db->where('goal_id', $goal_id);
        $this->db->where('type', 'revenue');
        $revenue_result = $this->db->get('goal_financials')->row();
        $total_revenue = $revenue_result ? (float)$revenue_result->total_revenue : 0;

        $net_impact = $total_revenue - $total_cost;
        $impact_status = $net_impact > 0 ? 'profit' : ($net_impact < 0 ? 'loss' : 'break_even');

        return [
            'total_cost' => $total_cost,
            'total_revenue' => $total_revenue,
            'net_impact' => $net_impact,
            'impact_status' => $impact_status
        ];
    }

    // Calculate goal status based on activity and tasks via milestones
    public function calculate_goal_status($goal_id) {
        // Get milestone IDs for this goal
        $this->db->select('milestone_id');
        $this->db->where('goal_id', $goal_id);
        $milestone_query = $this->db->get('goal_milestones');
        $milestone_ids = array_column($milestone_query->result_array(), 'milestone_id');

        if (empty($milestone_ids)) {
            return 'on_track';
        }

        // Check for blocked tasks
        $this->db->where_in('milestone', $milestone_ids);
        $this->db->where('task_status', 'blocked');
        $blocked_tasks = $this->db->count_all_results('tracker_issues');

        if ($blocked_tasks > 0) {
            return 'blocked';
        }

        // Check for no activity in 48 hours
        $this->db->where_in('milestone', $milestone_ids);
        $this->db->where('logged_at >=', date('Y-m-d H:i:s', strtotime('-48 hours')));
        $recent_activity = $this->db->count_all_results('tracker_issues');

        if ($recent_activity == 0) {
            return 'at_risk';
        }

        return 'on_track';
    }

    // Update execution stage
    public function update_execution_stage($goal_id, $stage, $next_stage, $justification, $user_id) {
        $stage_lower = strtolower($stage);
        $data = [
            $stage_lower . '_justification' => $justification,
            $stage_lower . '_updated_at' => date('Y-m-d H:i:s'),
            'execution_stage' => $next_stage,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('id', $goal_id);
        return $this->db->update('goals', $data);
    }

    // Add/Update target
    public function save_target($goal_id, $target_name, $target_value, $target_id = null) {
        $data = [
            'goal_id' => $goal_id,
            'target_name' => $target_name,
            'target_value' => $target_value
        ];

        if ($target_id) {
            $this->db->where('id', $target_id);
            return $this->db->update('goal_targets', $data);
        } else {
            return $this->db->insert('goal_targets', $data);
        }
    }

    // Get goal tasks for modal via milestones
    public function get_goal_tasks($goal_id) {
        // Get milestone IDs for this goal
        $this->db->select('milestone_id');
        $this->db->where('goal_id', $goal_id);
        $milestone_query = $this->db->get('goal_milestones');
        $milestone_ids = array_column($milestone_query->result_array(), 'milestone_id');

        if (empty($milestone_ids)) {
            return [];
        }

        $this->db->select('ti.*, s.name as assigned_to_name');
        $this->db->from('tracker_issues ti');
        $this->db->join('staff s', 'FIND_IN_SET(s.id, ti.assigned_to)', 'left');
        $this->db->where_in('ti.milestone', $milestone_ids);
        $this->db->where_not_in('ti.task_status', ['completed', 'canceled']);
        $this->db->order_by('ti.priority', 'DESC');
        $this->db->order_by('ti.created_at', 'DESC');
        return $this->db->get()->result_array();
    }

    // Add financial entry
    public function add_financial_entry($goal_id, $type, $amount, $description, $date) {
        $data = [
            'goal_id' => $goal_id,
            'type' => $type,
            'amount' => $amount,
            'description' => $description,
            'date' => $date,
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->db->insert('goal_financials', $data);
    }

    // Get all goals (basic info)
    public function get_all_goals() {
        $this->db->select('g.*, s.name as pod_owner_name');
        $this->db->from('goals g');
        $this->db->join('staff s', 's.id = g.pod_owner_id', 'left');
        $this->db->order_by('g.id', 'ASC');
        return $this->db->get()->result_array();
    }

    // Save goal (add/edit)
    public function save_goal($goal_id, $goal_name, $goal_category, $description, $pod_owner_id, $execution_stage) {
        $data = [
            'goal_name' => $goal_name,
            'goal_category' => $goal_category,
            'description' => $description,
            'pod_owner_id' => $pod_owner_id,
            'execution_stage' => $execution_stage ?: 'WHY',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($goal_id) {
            $this->db->where('id', $goal_id);
            return $this->db->update('goals', $data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            return $this->db->insert('goals', $data);
        }
    }

    // Delete goal
    public function delete_goal($goal_id) {
        $this->db->where('id', $goal_id);
        return $this->db->delete('goals');
    }

    // Get goal data for editing
    public function get_goal_data($goal_id) {
        $this->db->where('id', $goal_id);
        return $this->db->get('goals')->row_array();
    }

    // Save goal targets
    public function save_goal_targets($goal_id, $targets, $target_values = null) {
        // Delete existing targets
        $this->db->where('goal_id', $goal_id);
        $this->db->delete('goal_targets');

        // Insert new targets
        for ($i = 0; $i < count($targets); $i++) {
            if (!empty($targets[$i])) {
                $data = [
                    'goal_id' => $goal_id,
                    'target_name' => $targets[$i],
                    'target_value' => null // Now using description format
                ];
                $this->db->insert('goal_targets', $data);
            }
        }
    }

    // Save goal milestones (now using tracker_milestones IDs)
    public function save_goal_milestones($goal_id, $milestone_ids, $milestone_dates = null) {
        // Delete existing milestone links
        $this->db->where('goal_id', $goal_id);
        $this->db->delete('goal_milestones');

        // Insert new milestone links
        if (!empty($milestone_ids)) {
            foreach ($milestone_ids as $milestone_id) {
                if (!empty($milestone_id)) {
                    $data = [
                        'goal_id' => $goal_id,
                        'milestone_id' => $milestone_id
                    ];
                    $this->db->insert('goal_milestones', $data);
                }
            }
        }
    }

    // Save pod members
    public function save_pod_members($goal_id, $member_ids, $pod_owner_id) {
        // Delete existing members
        $this->db->where('goal_id', $goal_id);
        $this->db->delete('goal_pod_members');

        // Add pod owner first
        $data = [
            'goal_id' => $goal_id,
            'staff_id' => $pod_owner_id,
            'role' => 'Pod Owner'
        ];
        $this->db->insert('goal_pod_members', $data);

        // Add other members
        if (!empty($member_ids)) {
            foreach ($member_ids as $member_id) {
                if (!empty($member_id) && $member_id != $pod_owner_id) {
                    $data = [
                        'goal_id' => $goal_id,
                        'staff_id' => $member_id,
                        'role' => 'Member'
                    ];
                    $this->db->insert('goal_pod_members', $data);
                }
            }
        }
    }

    // Get goal attachments
    public function get_goal_attachments($goal_id) {
        $this->db->select('ga.*, s.name as uploaded_by_name');
        $this->db->from('goal_attachments ga');
        $this->db->join('staff s', 's.id = ga.uploaded_by', 'left');
        $this->db->where('ga.goal_id', $goal_id);
        $this->db->order_by('ga.created_at', 'DESC');
        return $this->db->get()->result_array();
    }

    // Get single attachment
    public function get_attachment($attachment_id) {
        $this->db->where('id', $attachment_id);
        return $this->db->get('goal_attachments')->row_array();
    }

    // Delete attachment
    public function delete_attachment($attachment_id) {
        $this->db->where('id', $attachment_id);
        return $this->db->delete('goal_attachments');
    }
    public function save_attachment($goal_id, $orig_file_name, $enc_file_name, $file_size) {
        $data = [
            'goal_id' => $goal_id,
            'orig_file_name' => $orig_file_name,
            'enc_file_name' => $enc_file_name,
            'file_name' => $orig_file_name, // Keep for backward compatibility
            'file_path' => './uploads/attachments/goals/' . $enc_file_name, // Keep for backward compatibility
            'file_size' => $file_size,
            'uploaded_by' => get_loggedin_user_id(),
            'created_at' => date('Y-m-d H:i:s')
        ];
        return $this->db->insert('goal_attachments', $data);
    }
}