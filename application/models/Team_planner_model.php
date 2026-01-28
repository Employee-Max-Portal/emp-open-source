<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Team_planner_model extends CI_Model
{
    public function get_team_schedule($department_id, $start_date, $end_date)
    {
        // Get staff with their events and leaves in optimized queries
        $CI = &get_instance();
        $branch_id = $CI->session->userdata('loggedin_branch');
        $staff_ids = $this->db->select('s.id, s.name, s.staff_id, sd.name as designation, s.photo')
                            ->from('staff s')
                            ->join('login_credential lc', 's.id = lc.user_id', 'left')
                            ->join('staff_designation sd', 's.designation = sd.id', 'left')
                            ->where('s.department', $department_id)
                            ->where('s.branch_id', $branch_id)
                            ->where_not_in('lc.role', [1, 9, 11, 12])
							->where('lc.user_id !=', 37)
							->where('lc.user_id !=', 13)
							->where('lc.active', 1)
							->order_by('CASE WHEN s.id = 13 THEN 1 ELSE 0 END', 'ASC', false) // push id=13 to the end
							->order_by('s.id', 'ASC')
                            ->get()
                            ->result();

        if (empty($staff_ids)) {
            return [];
        }

        $staff_id_list = array_column($staff_ids, 'id');

        // Get all events for all staff in one query
        $all_events = [];
        if ($this->db->table_exists('planner_events')) {
            $events_result = $this->db->select('
                    pe.user_id, pe.start_time, pe.end_time, pe.status,
                    ti.task_title, ti.priority_level, ti.task_priority, ti.unique_id, ti.category
                ')
                ->from('planner_events pe')
                ->join('tracker_issues ti', 'ti.id = pe.issue_id', 'left')
                ->where_in('pe.user_id', $staff_id_list)
                ->where('pe.start_time <=', $end_date . ' 23:59:59')
                ->where('pe.end_time >=', $start_date . ' 00:00:00')
                ->where('HOUR(pe.start_time) >=', 9)
                ->where('HOUR(pe.start_time) <', 21)
				->where('(ti.task_priority IS NULL OR ti.task_priority != 0)')
                ->order_by('pe.user_id, pe.start_time')
                ->get()
                ->result();

            foreach ($events_result as $event) {
                $all_events[$event->user_id][] = $event;
            }
        }

        // Get all leaves for all staff in one query
        $all_leaves = [];
        $leaves_result = $this->db->select('user_id, start_date, end_date, status')
                                ->where_in('user_id', $staff_id_list)
                                ->where('start_date <=', $end_date)
                                ->where('end_date >=', $start_date)
                                ->where('status', 2)
                                ->get('leave_application')
                                ->result();

        foreach ($leaves_result as $leave) {
            $all_leaves[$leave->user_id][] = $leave;
        }

        // Build final data structure
        $team_data = [];
        foreach ($staff_ids as $member) {
            $team_data[] = [
                'staff_id' => $member->id,
                'staff_name' => $member->name,
                'staff_code' => $member->staff_id,
                'staff_photo' => $member->photo,
                'designation' => $member->designation,
                'events' => isset($all_events[$member->id]) ? $all_events[$member->id] : [],
                'leaves' => isset($all_leaves[$member->id]) ? $all_leaves[$member->id] : [],
                'has_schedule' => isset($all_events[$member->id]) || isset($all_leaves[$member->id])
            ];
        }

        return $team_data;
    }
}