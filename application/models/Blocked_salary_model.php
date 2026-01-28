<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Blocked_salary_model extends CI_Model
{
    public function getBlockedSalaries($branch_id = null, $staff_id = null)
    {
        $this->db->select('sb.*, s.name as staff_name, s.staff_id as employee_id, sd.name as department_name,
                          rt.title as task_title, blocker.name as blocked_by_name, approver.name as approved_by_name');
        $this->db->from('salary_blocks sb');
        $this->db->join('staff s', 'sb.staff_id = s.id', 'left');
        $this->db->join('staff_department sd', 's.department = sd.id', 'left');
        $this->db->join('rdc_task rt', 'sb.task_id = rt.id', 'left');
        $this->db->join('staff blocker', 'sb.blocked_by = blocker.id', 'left');
        $this->db->join('staff approver', 'sb.approved_by = approver.id', 'left');

        // Role-based access control
        $user_role = loggedin_role_id();
        $logged_staff_id = get_loggedin_user_id();

        if (!in_array($user_role, [1, 2, 3, 5, 8])) {
            $this->db->where('sb.staff_id', $logged_staff_id);
        }

        // If branch_id is provided and not 'all', filter by branch
        if ($branch_id && $branch_id != 'all') {
            $this->db->where('s.branch_id', $branch_id);
        }

        $this->db->order_by('sb.created_at', 'DESC');
        return $this->db->get()->result_array();
    }

    public function getBlockedSalaryById($id)
    {
        $this->db->select('sb.*, s.name as staff_name, s.staff_id as employee_id, s.email as staff_email,
                          sd.name as department_name, rt.title as task_title, rt.description as task_description,
                          blocker.name as blocked_by_name');
        $this->db->from('salary_blocks sb');
        $this->db->join('staff s', 'sb.staff_id = s.id', 'left');
        $this->db->join('staff_department sd', 's.department = sd.id', 'left');
        $this->db->join('rdc_task rt', 'sb.task_id = rt.id', 'left');
        $this->db->join('staff blocker', 'sb.blocked_by = blocker.id', 'left');
        $this->db->where('sb.id', $id);
        return $this->db->get()->row_array();
    }

    public function updateBlockStatus($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('salary_blocks', $data);
    }

    public function deleteBlock($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('salary_blocks');
    }

    public function addStaffExplanation($id, $explanation)
    {
        $data = array(
            'staff_explanation' => $explanation,
            'explanation_date' => date('Y-m-d H:i:s')
        );
        $this->db->where('id', $id);
        return $this->db->update('salary_blocks', $data);
    }

    public function getBlockStats($staff_id = null)
    {
        $stats = array();

        // Role-based access control
        $user_role = loggedin_role_id();
        $logged_staff_id = get_loggedin_user_id();

        $restrict_to_own = !in_array($user_role, [1, 2, 3, 5, 8]);

        // Total blocks
        $this->db->from('salary_blocks');
        if ($staff_id) {
            $this->db->where('staff_id', $staff_id);
        } elseif ($restrict_to_own) {
            $this->db->where('staff_id', $logged_staff_id);
        }
        $stats['total'] = $this->db->count_all_results();

        // Pending blocks
        $this->db->from('salary_blocks');
        $this->db->where('status', 1);
        if ($staff_id) {
            $this->db->where('staff_id', $staff_id);
        } elseif ($restrict_to_own) {
            $this->db->where('staff_id', $logged_staff_id);
        }
        $stats['pending'] = $this->db->count_all_results();

        // Unblocked
        $this->db->from('salary_blocks');
        $this->db->where('status', 2);
        if ($staff_id) {
            $this->db->where('staff_id', $staff_id);
        } elseif ($restrict_to_own) {
            $this->db->where('staff_id', $logged_staff_id);
        }
        $stats['unblocked'] = $this->db->count_all_results();

        // Rejected
        $this->db->from('salary_blocks');
        $this->db->where('status', 3);
        if ($staff_id) {
            $this->db->where('staff_id', $staff_id);
        } elseif ($restrict_to_own) {
            $this->db->where('staff_id', $logged_staff_id);
        }
        $stats['rejected'] = $this->db->count_all_results();

        return $stats;
    }
}