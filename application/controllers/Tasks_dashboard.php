<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tasks_dashboard extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('tasks_model');
        $this->load->model('fundrequisition_model');
        $this->load->model('cashbook_model');
        $this->load->helper('url');
    }

    public function index()
    {
        // Get milestone-based data for ongoing tasks
        $this->data['milestone_data'] = $this->tasks_model->get_milestone_dashboard_data();

        // Get recent completed tasks by category
        $this->data['recent_regular'] = $this->tasks_model->get_recent_completed('regular');
        $this->data['recent_client'] = $this->tasks_model->get_recent_completed('client');
        $this->data['recent_inhouse'] = $this->tasks_model->get_recent_completed('inhouse');

        // Get planning stage milestones by category
        $this->data['planning_regular'] = $this->tasks_model->get_milestones_by_stage_and_type('planning', 'regular');
        $this->data['planning_client'] = $this->tasks_model->get_milestones_by_stage_and_type('planning', 'client');
        $this->data['planning_inhouse'] = $this->tasks_model->get_milestones_by_stage_and_type('planning', 'in_house');

        // Get hold status milestones by category
        $this->data['hold_regular'] = $this->tasks_model->get_milestones_by_status_and_type('hold', 'regular');
        $this->data['hold_client'] = $this->tasks_model->get_milestones_by_status_and_type('hold', 'client');
        $this->data['hold_inhouse'] = $this->tasks_model->get_milestones_by_status_and_type('hold', 'in_house');

        $this->data['title'] = 'Tasks Dashboard';
        $this->data['sub_page'] = 'tasks_dashboard/index';
        $this->data['main_menu'] = 'dashboard';
        $this->load->view('layout/index', $this->data);
    }

    public function get_milestone_tasks()
    {
        $milestone_id = $this->input->post('milestone_id');
        $task_type = $this->input->post('task_type', true) ?: 'all';
        $tasks = $this->tasks_model->get_tasks_by_milestone($milestone_id, $task_type);
        echo json_encode($tasks);
    }

    public function organization_indirect_cost()
    {
        $office_overhead = $this->input->post('office_overhead') ?: $this->input->get('office_overhead') ?: 50000;
        $this->data['organization_indirect_cost'] = $this->tasks_model->get_organization_indirect_cost($office_overhead);
        $this->data['title'] = 'Organization Indirect Cost Analysis';
        $this->data['sub_page'] = 'tasks_dashboard/organization_indirect_cost';
        $this->data['main_menu'] = 'dashboard';
        $this->load->view('layout/index', $this->data);
    }

    public function get_milestone_task_counts()
    {
        $milestone_id = $this->input->post('milestone_id');
        $counts = $this->tasks_model->get_milestone_task_counts($milestone_id);
        echo json_encode($counts);
    }

    public function get_milestone_financials()
    {
        $milestone_id = $this->input->post('milestone_id');

        if (!$milestone_id) {
            echo '<div class="alert alert-danger">Milestone ID is required</div>';
            return;
        }

        echo '<div class="text-center">';
        echo '<a href="' . base_url('tasks_dashboard/milestone_report/' . $milestone_id) . '" class="btn btn-primary" target="_blank">';
        echo '<i class="fas fa-file-alt"></i> View Detailed Financial Report';
        echo '</a>';
        echo '</div>';
    }

	public function milestone_financial_report()
		{
			$milestone_id = $this->input->post('id');

			// Get milestone details
			$this->db->select('*');
			$this->db->from('tracker_milestones');
			$this->db->where('id', $milestone_id);
			$milestone = $this->db->get()->row_array();

			$this->data['milestone'] = $milestone;
			$this->data['milestone_id'] = $milestone_id;

			$this->load->view('tasks_dashboard/milestone_report', $this->data);

		}

    public function milestone_report($milestone_id = null)
    {
        if (!$milestone_id) {
            show_404();
            return;
        }

        // Get milestone details
        $this->db->select('*');
        $this->db->from('tracker_milestones');
        $this->db->where('id', $milestone_id);
        $milestone = $this->db->get()->row_array();

        if (!$milestone) {
            show_404();
            return;
        }

        $this->data['milestone'] = $milestone;
        $this->data['milestone_id'] = $milestone_id;

        // Check if it's an AJAX request
        if ($this->input->is_ajax_request() || $this->input->server('HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest') {
            $this->load->view('tasks_dashboard/milestone_report', $this->data);
        } else {
            $this->data['title'] = 'Milestone Financial Report - ' . $milestone['title'];
            $this->data['sub_page'] = 'tasks_dashboard/milestone_report';
            $this->data['main_menu'] = 'dashboard';
            $this->load->view('layout/index', $this->data);
        }
    }

    private function calc_kpi_score($staff_id, $month)
    {
        $formatted_month = str_replace('-', '/', $month);
        $this->db->select('AVG(manager_rating) AS avg_score');
        $this->db->from('kpi_form');
        $this->db->where('staff_id', $staff_id);
        $this->db->like('daterange', $formatted_month, 'after');
        $result = $this->db->get()->row();
        return ($result && $result->avg_score !== null) ? round($result->avg_score, 2) : 0;
    }

    public function employee_financial_report()
    {
        $month = $this->input->get('month') ?: date('Y-m');

        $this->db->select('s.id, s.staff_id, s.name, sd.name as department_name');
        $this->db->from('staff s');
        $this->db->join('staff_department sd', 's.department = sd.id', 'left');
		$this->db->join('login_credential', 'login_credential.user_id = s.id');
		$this->db->where('login_credential.active', 1);
		$this->db->where_not_in('login_credential.role', [1, 9, 10, 11, 12]);
		$this->db->where_not_in('s.id', [37, 31, 25, 28, 22, 23]);
        $this->db->order_by('s.staff_id', 'ASC');
        $employees = $this->db->get()->result_array();

        foreach ($employees as &$emp) {
            // Attendance Summary
            $this->db->select('COUNT(*) as present_days');
            $this->db->where('staff_id', $emp['id']);
            $this->db->where("DATE_FORMAT(date, '%Y-%m') =", $month);
			$this->db->where_in('status', ['P', 'L']);
            $result = $this->db->get('staff_attendance');
            $emp['attendance'] = $result ? ($result->row()->present_days ?: 0) : 0;

            // KPI Score
            $emp['kpi'] = $this->calc_kpi_score($emp['id'], $month);

            // Task Summary from tracker_issues
            $this->db->select('COUNT(*) as total_tasks');
            $this->db->where('assigned_to', $emp['id']);
            $this->db->where("DATE_FORMAT(estimated_end_time, '%Y-%m') =", $month);
            $result = $this->db->get('tracker_issues');
            $emp['tasks'] = $result ? ($result->row()->total_tasks ?: 0) : 0;

            // Outcome Summary from completed tasks in tracker_issues
            $this->db->select('COUNT(*) as completed_tasks');
            $this->db->where('assigned_to', $emp['id']);
            $this->db->where('task_status', 'completed');
            $this->db->where("DATE_FORMAT(estimated_end_time, '%Y-%m') =", $month);
            $result = $this->db->get('tracker_issues');
            $emp['outcome'] = $result ? ($result->row()->completed_tasks ?: 0) : 0;

            // Salary Details - Get from payslip or calculate from salary_increments/template
            $this->db->select('id, basic_salary, total_allowance, total_deduction, net_salary');
            $this->db->where('staff_id', $emp['id']);
            $this->db->where('month', date('m', strtotime($month)));
            $this->db->where('year', date('Y', strtotime($month)));
            $salary = $this->db->get('payslip')->row();

            if ($salary) {
                $emp['gross_salary'] = $salary->basic_salary + $salary->total_allowance;

                // If total_deduction is 0, calculate manually
                if ($salary->total_deduction > 0) {
                    $emp['deduction'] = $salary->total_deduction;
                } else {
                    $m = date('m', strtotime($month));
                    $y = date('Y', strtotime($month));

                    // Advance salary
                    $advance_total = $this->db->select('SUM(amount) as total')
                        ->where('staff_id', $emp['id'])
                        ->where('deduct_month', $m)
                        ->where('year', $y)
                        ->get('advance_salary')->row()->total ?: 0;

                    // Late count
                    $late_count = $this->db->where('staff_id', $emp['id'])
                        ->where('status', 'L')
                        ->where('MONTH(date)', $m)
                        ->where('YEAR(date)', $y)
                        ->count_all_results('staff_attendance');

                    // Present dates
                    $present_dates = $this->db->select('DATE(date) as date')
                        ->where('staff_id', $emp['id'])
                        ->where_in('status', ['P', 'L'])
                        ->where('MONTH(date)', $m)
                        ->where('YEAR(date)', $y)
                        ->get('staff_attendance')->result_array();
                    $present_dates = array_column($present_dates, 'date');

                    // Accepted leaves
                    $accepted_leave_dates = [];
                    $leaves = $this->db->select('start_date, end_date')
                        ->where('user_id', $emp['id'])
                        ->where('status', 2)
                        ->where('category_id !=', 0)
                        ->where('MONTH(start_date) <=', $m)
                        ->where('MONTH(end_date) >=', $m)
                        ->where('YEAR(start_date) <=', $y)
                        ->where('YEAR(end_date) >=', $y)
                        ->get('leave_application')->result_array();

                    foreach ($leaves as $leave) {
                        $period = new DatePeriod(
                            new DateTime($leave['start_date']),
                            new DateInterval('P1D'),
                            (new DateTime($leave['end_date']))->modify('+1 day')
                        );
                        foreach ($period as $dt) {
                            if ($dt->format('Y-m') == "$y-$m") {
                                $accepted_leave_dates[] = $dt->format('Y-m-d');
                            }
                        }
                    }

                    // Holidays
                    $holiday_dates = [];
                    $holidays = $this->db->select('start_date, end_date')
                        ->where('type', 'holiday')
                        ->where('status', 1)
                        ->where('MONTH(start_date) <=', $m)
                        ->where('MONTH(end_date) >=', $m)
                        ->where('YEAR(start_date) <=', $y)
                        ->where('YEAR(end_date) >=', $y)
                        ->get('event')->result_array();

                    foreach ($holidays as $holiday) {
                        $period = new DatePeriod(
                            new DateTime($holiday['start_date']),
                            new DateInterval('P1D'),
                            (new DateTime($holiday['end_date']))->modify('+1 day')
                        );
                        foreach ($period as $dt) {
                            if ($dt->format('Y-m') == "$y-$m") {
                                $holiday_dates[] = $dt->format('Y-m-d');
                            }
                        }
                    }

                    // Calculate absent count
                    $total_days = cal_days_in_month(CAL_GREGORIAN, $m, $y);
                    $absent_count = 0;
                    for ($d = 1; $d <= $total_days; $d++) {
                        $date = "$y-$m-" . str_pad($d, 2, '0', STR_PAD_LEFT);
                        $day_of_week = date('N', strtotime($date));

                        if (in_array($date, $present_dates) || in_array($date, $accepted_leave_dates) || in_array($date, $holiday_dates)) {
                            continue;
                        }
                        if (in_array($day_of_week, [5, 6])) {
                            continue;
                        }
                        $absent_count++;
                    }

                    // Calculate deductions
                    $late_deduction = ($salary->basic_salary / 30) * floor($late_count / 3);
                    $absent_deduction = ($salary->basic_salary / 30) * $absent_count;
                    $emp['deduction'] = $advance_total + $late_deduction + $absent_deduction;
                }

                $emp['final_salary'] = $salary->net_salary;
            } else {
                // Calculate from salary_increments or salary_template
                $latest_increment = $this->db->select('new_salary, basic_salary, salary_components')
                    ->where('staff_id', $emp['id'])
                    ->order_by('increment_date', 'DESC')
                    ->limit(1)
                    ->get('salary_increments')->row();

                if ($latest_increment) {
                    $emp['gross_salary'] = $latest_increment->new_salary;
                    $components = json_decode($latest_increment->salary_components, true) ?: [];
                    $deductions = array_filter($components, function($c) { return isset($c['type']) && $c['type'] == 2; });
                    $emp['deduction'] = array_sum(array_column($deductions, 'amount'));
                } else {
                    $this->db->select('s.salary_template_id, t.basic_salary');
                    $this->db->from('staff s');
                    $this->db->join('salary_template t', 't.id = s.salary_template_id', 'left');
                    $this->db->where('s.id', $emp['id']);
                    $template = $this->db->get()->row();

                    if ($template && $template->salary_template_id) {
                        $basic = (float)$template->basic_salary;
                        $this->db->select('SUM(amount) as total');
                        $this->db->where('salary_template_id', $template->salary_template_id);
                        $this->db->where('type', 1);
                        $allowances = $this->db->get('salary_template_details')->row();
                        $emp['gross_salary'] = $basic + ($allowances ? $allowances->total : 0);

                        $this->db->select('SUM(amount) as total');
                        $this->db->where('salary_template_id', $template->salary_template_id);
                        $this->db->where('type', 2);
                        $deductions = $this->db->get('salary_template_details')->row();
                        $emp['deduction'] = $deductions ? $deductions->total : 0;
                    } else {
                        $emp['gross_salary'] = 0;
                        $emp['deduction'] = 0;
                    }
                }
                $emp['final_salary'] = $emp['gross_salary'] - $emp['deduction'];
            }

            // Total Convenience from fund_requisition (category_id = 2 for Conveyance)
            $this->db->select('SUM(amount) as total_convenience');
            $this->db->where('staff_id', $emp['id']);
            $this->db->where('category_id', 2);
            $this->db->where('status', 2);
            $this->db->where("DATE_FORMAT(request_date, '%Y-%m') =", $month);
            $result = $this->db->get('fund_requisition');
            $emp['convenience'] = $result ? ($result->row()->total_convenience ?: 0) : 0;
        }

        $this->data['employees'] = $employees;
        $this->data['month'] = $month;
        $this->data['title'] = 'Employee Financial Summary';
        $this->data['sub_page'] = 'tasks_dashboard/employee_summary';
        $this->data['main_menu'] = 'dashboard';
        $this->load->view('layout/index', $this->data);
    }
}