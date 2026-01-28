<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tasks_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_ongoing_tasks($category)
    {
        $this->db->select('ti.*, tm.title as milestone_title, s.name as staff_name, tt.name as task_type_name');
        $this->db->from('tracker_issues ti');
        $this->db->join('tracker_milestones tm', 'tm.id = ti.milestone', 'left');
        $this->db->join('staff s', 's.id = ti.assigned_to', 'left');
        $this->db->join('task_types tt', 'tt.id = ti.task_type', 'left');

        if ($category == 'regular') {
            $this->db->where_in('tm.type', ['regular']);
        } elseif ($category == 'client') {
            $this->db->where_in('tm.type', ['client']);
        } else {
            $this->db->where_in('tm.type', ['in_house']);
        }

        $this->db->where_not_in('ti.task_status', ['completed', 'cancelled']);
        $this->db->where('ti.parent_issue IS NULL', null, false);
        $this->db->order_by('ti.milestone', 'ASC');
        $this->db->order_by('ti.logged_at', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_recent_completed($category)
    {
        $this->db->select('ti.*, tm.title as milestone_title, s.name as staff_name, tt.name as task_type_name');
        $this->db->from('tracker_issues ti');
        $this->db->join('tracker_milestones tm', 'tm.id = ti.milestone', 'left');
        $this->db->join('staff s', 's.id = ti.assigned_to', 'left');
        $this->db->join('task_types tt', 'tt.id = ti.task_type', 'left');

        if ($category == 'regular') {
            $this->db->where_in('tm.type', ['regular']);
        } elseif ($category == 'client') {
            $this->db->where_in('tm.type', ['client']);
        } else {
            $this->db->where_in('tm.type', ['in_house']);
        }

        $this->db->where('ti.task_status', 'completed');
        $this->db->order_by('ti.milestone', 'ASC');
        $this->db->order_by('ti.reviewed_at', 'DESC');
        $this->db->limit(10);
        return $this->db->get()->result_array();
    }

public function get_milestone_dashboard_data()
{
    $this->db->select('tm.*, s.name as owner_name, s.photo,
                      COUNT(ti.id) as total_tasks,
                      SUM(CASE WHEN ti.task_status = "completed" THEN 1 ELSE 0 END) as completed_tasks,
                      SUM(CASE WHEN ti.task_status NOT IN ("completed", "Complete") THEN COALESCE(ti.remaining_time, 0) ELSE 0 END) as remaining_time,
                      SUM(COALESCE(ti.spent_time, 0)) as spent_time,
                      MAX(ti.logged_at) as last_update');
    $this->db->from('tracker_milestones tm');
    $this->db->join('tracker_issues ti', 'ti.milestone = tm.id', 'left');
    $this->db->join('staff s', 's.id = tm.assigned_to', 'left');
    $this->db->where('tm.status =', 'in_progress');
    $this->db->where('tm.stage =', 'execution');
	$this->db->where('tm.due_date >=', date('Y-m-d'));
    $this->db->group_by('tm.id');
    $this->db->having('total_tasks >', 0);
    $this->db->order_by('tm.type', 'ASC');
    $this->db->order_by('tm.created_at', 'DESC');

    $milestones = $this->db->get()->result_array();

    foreach ($milestones as &$milestone) {
        // Get task type counts
        $this->db->select('tt.name as task_type, COUNT(ti.id) as count');
        $this->db->from('tracker_issues ti');
        $this->db->join('task_types tt', 'tt.id = ti.task_type', 'left');
        $this->db->where('ti.milestone', $milestone['id']);
        $this->db->where('ti.task_type IS NOT NULL');
        $this->db->where('ti.task_type !=', '');
        $this->db->group_by('tt.name');
        $task_types = $this->db->get()->result_array();
        $milestone['task_types'] = !empty($task_types) ? $task_types : [];

        // Get department counts for incomplete tasks
        $this->db->select('td.title as department_name, COUNT(ti.id) as count');
        $this->db->from('tracker_issues ti');
        $this->db->join('tracker_departments td', 'td.identifier = ti.department', 'left');
        $this->db->where('ti.milestone', $milestone['id']);
        $this->db->where_not_in('ti.task_status', ['completed', 'done']);
        $this->db->where('ti.department IS NOT NULL');
        $this->db->where('ti.department !=', '');
        $this->db->group_by('td.title');
        $incomplete_departments = $this->db->get()->result_array();
        $milestone['incomplete_departments'] = !empty($incomplete_departments) ? $incomplete_departments : [];

        // Get main and sub task counts
        $counts = $this->get_milestone_task_counts($milestone['id']);
        $milestone['main_total'] = $counts['main_total'];
        $milestone['main_completed'] = $counts['main_completed'];
        $milestone['sub_total'] = $counts['sub_total'];
        $milestone['sub_completed'] = $counts['sub_completed'];

        // Fund summary
        $this->db->select('SUM(amount) as total_fund');
        $this->db->from('fund_requisition');
        $this->db->where('milestone', $milestone['id']);
        $this->db->where('payment_status', '2');
        $fund_result = $this->db->get()->row_array();
        $milestone['total_fund'] = $fund_result['total_fund'] ?: 0;

        // Total staff salary for indirect cost calculation
        $this->db->select('DISTINCT(ti.assigned_to) as staff_id');
        $this->db->from('tracker_issues ti');
        $this->db->where('ti.milestone', $milestone['id']);
        $this->db->where('ti.assigned_to IS NOT NULL');
        $staff_ids = $this->db->get()->result_array();

        $total_staff_salary = 0;
        foreach ($staff_ids as $staff) {
            $staff_salary = $this->get_staff_current_salary($staff['staff_id']);
            $total_staff_salary += $staff_salary;
        }
        $milestone['total_staff_salary'] = $total_staff_salary;

        // Calculate indirect cost (add office overhead parameter)
        $office_overhead = 50000; // Define your monthly office overhead cost
        $indirect_cost_data = $this->calculate_indirect_cost_per_hour($staff_ids, $total_staff_salary, $office_overhead);
        $milestone['indirect_cost_per_hour'] = $indirect_cost_data['indirect_cost_per_hour'];
        $milestone['total_cost'] = $indirect_cost_data['total_cost'];
        $milestone['cost_breakdown'] = $indirect_cost_data;
    }

    return $milestones;
}


    public function get_tasks_by_milestone($milestone_id, $task_type = 'all')
    {
        $this->db->select('ti.*, s.name as staff_name, s.photo, tt.name as task_type_name, td.title as department_name');
        $this->db->from('tracker_issues ti');
        $this->db->join('staff s', 's.id = ti.assigned_to', 'left');
        $this->db->join('task_types tt', 'tt.id = ti.task_type', 'left');
        $this->db->join('tracker_departments td', 'td.identifier = ti.department', 'left');
        $this->db->where('ti.milestone', $milestone_id);

        if ($task_type == 'main') {
            $this->db->where('ti.parent_issue IS NULL');
        } elseif ($task_type == 'sub') {
            $this->db->where('ti.parent_issue IS NOT NULL');
        }

        $this->db->order_by('ti.task_status', 'ASC');
        $this->db->order_by('ti.logged_at', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_milestone_task_counts($milestone_id)
    {
        // Main tasks (parent_issue IS NULL)
        $this->db->select('COUNT(*) as main_total, SUM(CASE WHEN task_status = "completed" THEN 1 ELSE 0 END) as main_completed');
        $this->db->from('tracker_issues');
        $this->db->where('milestone', $milestone_id);
        $this->db->where('parent_issue IS NULL');
        $main_counts = $this->db->get()->row_array();

        // Sub tasks (parent_issue IS NOT NULL)
        $this->db->select('COUNT(*) as sub_total, SUM(CASE WHEN task_status = "completed" THEN 1 ELSE 0 END) as sub_completed');
        $this->db->from('tracker_issues');
        $this->db->where('milestone', $milestone_id);
        $this->db->where('parent_issue IS NOT NULL');
        $sub_counts = $this->db->get()->row_array();

        return [
            'main_total' => (int)$main_counts['main_total'],
            'main_completed' => (int)$main_counts['main_completed'],
            'sub_total' => (int)$sub_counts['sub_total'],
            'sub_completed' => (int)$sub_counts['sub_completed']
        ];
    }

    // Get current staff salary (considering increments)
    public function get_staff_current_salary($staff_id)
    {
        // First check if staff has salary increments
        $this->db->select('new_salary');
        $this->db->from('salary_increments');
        $this->db->where('staff_id', $staff_id);
        $this->db->order_by('increment_date', 'DESC');
        $this->db->limit(1);
        $increment = $this->db->get()->row();

        if ($increment) {
            return (float)$increment->new_salary;
        }

        // No increment found, get from salary template
        $this->db->select('salary_template_id');
        $this->db->from('staff');
		$this->db->join('login_credential lc', 'lc.user_id = staff.id', 'left');
		$this->db->where('staff.id', $staff_id);
		$this->db->where('lc.active', 1);
        $staff = $this->db->get()->row();

        if (!$staff || !$staff->salary_template_id) {
            return 0;
        }

        // Get salary template basic salary
        $this->db->select('basic_salary');
        $this->db->from('salary_template');
        $this->db->where('id', $staff->salary_template_id);
        $template = $this->db->get()->row();

        if (!$template) {
            return 0;
        }

        // Get salary components (allowances) and calculate total
        $this->db->select('SUM(amount) as total_allowances');
        $this->db->from('salary_template_details');
        $this->db->where('salary_template_id', $staff->salary_template_id);
        $this->db->where('type', 1); // Only allowances
        $allowances = $this->db->get()->row();

        $total_salary = (float)$template->basic_salary + (float)($allowances->total_allowances ?: 0);

        return $total_salary;
    }

    public function get_milestones_by_stage_and_type($stage, $type)
    {
        $this->db->select('tm.*, s.name as owner_name, s.photo,
                          COUNT(ti.id) as total_tasks,
                          SUM(CASE WHEN ti.task_status IN ("completed", "done", "solved") THEN 1 ELSE 0 END) as completed_tasks,
                          SUM(CASE WHEN ti.task_status NOT IN ("completed", "Complete") THEN COALESCE(ti.remaining_time, 0) ELSE 0 END) as remaining_time,
                          SUM(COALESCE(ti.spent_time, 0)) as spent_time,
                          MAX(ti.logged_at) as last_update');
        $this->db->from('tracker_milestones tm');
        $this->db->join('tracker_issues ti', 'ti.milestone = tm.id', 'left');
        $this->db->join('staff s', 's.id = tm.assigned_to', 'left');
        $this->db->where('tm.stage', $stage);
        $this->db->where('tm.type', $type);
        $this->db->group_by('tm.id');
        $this->db->order_by('tm.created_at', 'DESC');

        $milestones = $this->db->get()->result_array();

        foreach ($milestones as &$milestone) {
            // Get task type counts
            $this->db->select('tt.name as task_type, COUNT(ti.id) as count');
            $this->db->from('tracker_issues ti');
            $this->db->join('task_types tt', 'tt.id = ti.task_type', 'left');
            $this->db->where('ti.milestone', $milestone['id']);
            $this->db->where('ti.task_type IS NOT NULL');
            $this->db->where('ti.task_type !=', '');
            $this->db->group_by('tt.name');
            $task_types = $this->db->get()->result_array();
            $milestone['task_types'] = !empty($task_types) ? $task_types : [];

            // Get department counts for incomplete tasks
            $this->db->select('td.title as department_name, COUNT(ti.id) as count');
            $this->db->from('tracker_issues ti');
            $this->db->join('tracker_departments td', 'td.identifier = ti.department', 'left');
            $this->db->where('ti.milestone', $milestone['id']);
            $this->db->where_not_in('ti.task_status', ['completed', 'done']);
            $this->db->where('ti.department IS NOT NULL');
            $this->db->where('ti.department !=', '');
            $this->db->group_by('td.title');
            $incomplete_departments = $this->db->get()->result_array();
            $milestone['incomplete_departments'] = !empty($incomplete_departments) ? $incomplete_departments : [];

            // Get main and sub task counts
            $counts = $this->get_milestone_task_counts($milestone['id']);
            $milestone['main_total'] = $counts['main_total'];
            $milestone['main_completed'] = $counts['main_completed'];
            $milestone['sub_total'] = $counts['sub_total'];
            $milestone['sub_completed'] = $counts['sub_completed'];

            // Fund summary
            $this->db->select('SUM(amount) as total_fund');
            $this->db->from('fund_requisition');
            $this->db->where('milestone', $milestone['id']);
            $this->db->where('payment_status', '2');
            $fund_result = $this->db->get()->row_array();
            $milestone['total_fund'] = $fund_result['total_fund'] ?: 0;
        }

        return $milestones;
    }

    public function get_milestones_by_status_and_type($status, $type)
    {
        $this->db->select('tm.*, s.name as owner_name, s.photo,
                          COUNT(ti.id) as total_tasks,
                          SUM(CASE WHEN ti.task_status IN ("completed", "done", "solved") THEN 1 ELSE 0 END) as completed_tasks,
                          SUM(CASE WHEN ti.task_status NOT IN ("completed", "Complete") THEN COALESCE(ti.remaining_time, 0) ELSE 0 END) as remaining_time,
                          SUM(COALESCE(ti.spent_time, 0)) as spent_time,
                          MAX(ti.logged_at) as last_update');
        $this->db->from('tracker_milestones tm');
        $this->db->join('tracker_issues ti', 'ti.milestone = tm.id', 'left');
        $this->db->join('staff s', 's.id = tm.assigned_to', 'left');
        $this->db->where('tm.status', $status);
        $this->db->where('tm.type', $type);
        $this->db->group_by('tm.id');
        $this->db->order_by('tm.created_at', 'DESC');

        $milestones = $this->db->get()->result_array();

        foreach ($milestones as &$milestone) {
            // Get task type counts
            $this->db->select('tt.name as task_type, COUNT(ti.id) as count');
            $this->db->from('tracker_issues ti');
            $this->db->join('task_types tt', 'tt.id = ti.task_type', 'left');
            $this->db->where('ti.milestone', $milestone['id']);
            $this->db->where('ti.task_type IS NOT NULL');
            $this->db->where('ti.task_type !=', '');
            $this->db->group_by('tt.name');
            $task_types = $this->db->get()->result_array();
            $milestone['task_types'] = !empty($task_types) ? $task_types : [];

            // Get department counts for incomplete tasks
            $this->db->select('td.title as department_name, COUNT(ti.id) as count');
            $this->db->from('tracker_issues ti');
            $this->db->join('tracker_departments td', 'td.identifier = ti.department', 'left');
            $this->db->where('ti.milestone', $milestone['id']);
            $this->db->where_not_in('ti.task_status', ['completed', 'done']);
            $this->db->where('ti.department IS NOT NULL');
            $this->db->where('ti.department !=', '');
            $this->db->group_by('td.title');
            $incomplete_departments = $this->db->get()->result_array();
            $milestone['incomplete_departments'] = !empty($incomplete_departments) ? $incomplete_departments : [];

            // Get main and sub task counts
            $counts = $this->get_milestone_task_counts($milestone['id']);
            $milestone['main_total'] = $counts['main_total'];
            $milestone['main_completed'] = $counts['main_completed'];
            $milestone['sub_total'] = $counts['sub_total'];
            $milestone['sub_completed'] = $counts['sub_completed'];

            // Fund summary
            $this->db->select('SUM(amount) as total_fund');
            $this->db->from('fund_requisition');
            $this->db->where('milestone', $milestone['id']);
            $this->db->where('payment_status', '2');
            $fund_result = $this->db->get()->row_array();
            $milestone['total_fund'] = $fund_result['total_fund'] ?: 0;
        }

        return $milestones;
    }

    // Calculate indirect cost per hour based on office overhead
    public function calculate_indirect_cost_per_hour($staff_ids, $total_staff_salary, $office_indirect_cost = 0)
    {
        $staff_ids_only = array_column($staff_ids, 'staff_id');
        $staff_count = count($staff_ids_only);

        if ($staff_count == 0) {
            return [
                'total_staff_salary' => 0,
                'office_indirect_cost' => 0,
                'total_cost' => 0,
                'indirect_cost_per_hour' => 0,
                'total_staff' => 0,
                'working_hours_per_month' => 0
            ];
        }

        // Constants
        $working_hours_per_day = 8.5;
        $working_days_per_month = 22;
        $working_hours_per_month = $working_hours_per_day * $working_days_per_month;

        // Total cost = Employee Salary + Office Expenses
        $total_cost = $total_staff_salary + $office_indirect_cost;

        // Indirect cost per hour = Total cost ÷ Working hours
        $total_working_hours = $staff_count * $working_hours_per_month;
        $indirect_cost_per_hour = $total_cost / $total_working_hours;

        return [
            'total_staff_salary' => round($total_staff_salary, 2),
            'office_indirect_cost' => round($office_indirect_cost, 2),
            'total_cost' => round($total_cost, 2),
            'indirect_cost_per_hour' => round($indirect_cost_per_hour, 2),
            'total_staff' => $staff_count,
            'working_hours_per_month' => $working_hours_per_month
        ];
    }
    // Get organization-wide indirect cost breakdown
    public function get_organization_indirect_cost($office_overhead = 50000)
    {
        // Get all active staff
        $this->db->select('s.id, s.name');
        $this->db->from('staff s');
        $this->db->join('login_credential lc', 'lc.user_id = s.id', 'inner');
        $this->db->where('lc.active', 1);
        $this->db->where_not_in('lc.role', [1, 9, 10, 11, 12]);
        $active_staff = $this->db->get()->result_array();

        $total_staff_count = count($active_staff);
        $total_organization_salary = 0;

        // Calculate total organization salary
        foreach ($active_staff as $staff) {
            $staff_salary = $this->get_staff_current_salary($staff['id']);
            $total_organization_salary += $staff_salary;
        }

        // Constants
        $working_hours_per_day = 8.5;
        $working_days_per_month = 22;
        $working_hours_per_month = $working_hours_per_day * $working_days_per_month;

        // Calculations
        $total_cost = $total_organization_salary + $office_overhead;
        $total_working_hours = $total_staff_count * $working_hours_per_month;
        $indirect_cost_per_hour = $total_working_hours > 0 ? $total_cost / $total_working_hours : 0;
        $average_salary_per_staff = $total_staff_count > 0 ? $total_organization_salary / $total_staff_count : 0;
        $salary_cost_per_hour = $total_working_hours > 0 ? $total_organization_salary / $total_working_hours : 0;
        $office_cost_per_hour = $total_working_hours > 0 ? $office_overhead / $total_working_hours : 0;

        return [
            'explanation' => [
                'total_active_staff' => $total_staff_count,
                'total_organization_salary' => round($total_organization_salary, 2),
                'office_overhead' => round($office_overhead, 2),
                'total_cost' => round($total_cost, 2),
                'working_hours_per_month' => $working_hours_per_month,
                'total_working_hours' => $total_working_hours
            ],
            'breakdown' => [
                'average_salary_per_staff' => round($average_salary_per_staff, 2),
                'salary_cost_per_hour' => round($salary_cost_per_hour, 2),
                'office_cost_per_hour' => round($office_cost_per_hour, 2),
                'indirect_cost_per_hour' => round($indirect_cost_per_hour, 2)
            ],
            'formula' => [
                'total_cost' => 'Organization Salary (' . number_format($total_organization_salary) . ') + Office Overhead (' . number_format($office_overhead) . ') = ' . number_format($total_cost),
                'total_hours' => $total_staff_count . ' staff × ' . $working_hours_per_month . ' hours = ' . number_format($total_working_hours) . ' hours',
                'hourly_rate' => '' . number_format($total_cost) . ' ÷ ' . number_format($total_working_hours) . ' hours = ' . round($indirect_cost_per_hour, 2) . '/hour'
            ]
        ];
    }
}

