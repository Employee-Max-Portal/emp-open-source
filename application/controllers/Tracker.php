<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Tracker extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('tracker_model');
		$this->load->helper('time');

    }

    public function index()
    {
        redirect(base_url('tracker/my_issues'));
    }


    public function labels()
    {
        $labels = $this->db->get('task_labels')->result();
		foreach($labels as $label) {
			$this->db->like('label', $label->id);
			$label->task_count = $this->db->count_all_results('tracker_issues');
		}
		$this->data['labels'] = $labels;

		 $this->data['headerelements'] = array(
            'css' => array(
                'vendor/dropify/css/dropify.min.css',
            ),
            'js' => array(
                'vendor/dropify/js/dropify.min.js',
            ),
        );

        $this->data['title'] = translate('Labels');
        $this->data['sub_page'] = 'tracker/labels';
        $this->data['main_menu'] = 'tracker';
        $this->load->view('layout/index', $this->data);

    }

	public function add_label()
	{
		$data = [
			'name' => $this->input->post('name', true),
			'slug' => url_title($this->input->post('name', true), 'dash', true),
			'description' => $this->input->post('description', true)
		];
		$this->db->insert('task_labels', $data);

		if ($this->input->is_ajax_request()) {
			echo json_encode(['status' => 'success', 'redirect' => base_url('tracker/labels')]);
			exit;
		} else {
			redirect('tracker/labels');
		}
	}


	public function getLabelEdit()
	{
		$id = $this->input->post('id');
		$this->data['label'] = $this->db->get_where('task_labels', ['id' => $id])->row();
		$this->load->view('tracker/edit_label', $this->data);
	}

    public function update_label($id)
    {
        $data = [
            'name' => $this->input->post('name', true),
            'slug' => url_title($this->input->post('name', true), 'dash', true),
            'description' => $this->input->post('description', true)
        ];
        $this->db->where('id', $id)->update('task_labels', $data);
        redirect('tracker/labels');
    }

    public function delete_label($id)
    {
        $this->db->where('id', $id)->delete('task_labels');
        redirect('tracker/labels');
    }

	public function add_departments()
		{
			$post = $this->input->post();

			$data = [
				'title' => $post['title'],
				'identifier' => strtoupper($post['identifier']),
				'description' => $post['description'],
				//'icon_color' => $post['icon_color'],
				'default_status' => $post['default_status'],
				'is_private' => isset($post['is_private']) ? 1 : 0,
				'auto_join' => isset($post['auto_join']) ? 1 : 0,
				'owner_id' => get_loggedin_user_id(),
				'assigned_issuer' => $post['assigned_issuer'],
			];

			$this->db->insert('tracker_departments', $data);
			$department_id = $this->db->insert_id();

			// Insert members
			if (isset($post['members']) && is_array($post['members'])) {
				foreach ($post['members'] as $staff_id) {
					$this->db->insert('tracker_department_members', [
						'department_id' => $department_id,
						'staff_id' => $staff_id
					]);
				}
			}

			// Return or redirect
			if ($this->input->is_ajax_request()) {
				echo json_encode(['status' => 'success', 'redirect' => $_SERVER['HTTP_REFERER']]);
				exit;
			} else {
				redirect($_SERVER['HTTP_REFERER']); // Or your preferred redirect
			}
		}

	// application/controllers/Tracker.php

	public function all_departments()
	{
		if (!get_permission('tracker_department', 'is_view')) access_denied();
		$data['all_departments'] = $this->tracker_model->get_all_departments_summary();

		$data['title'] = translate('all_departments');
		$data['sub_page'] = 'tracker/departments';
		$data['main_menu'] = 'tracker';

		$this->load->view('layout/index', $data);
	}

	public function join_department($id)
	{
		$staff_id = get_loggedin_user_id();

		if (!$this->tracker_model->is_member($id, $staff_id)) {
			$this->db->insert('tracker_department_members', [
				'department_id' => $id,
				'staff_id' => $staff_id
			]);
		}
		redirect($_SERVER['HTTP_REFERER']);
	}

	public function leave_department($id)
	{
		$staff_id = get_loggedin_user_id();

		$this->db->where(['department_id' => $id, 'staff_id' => $staff_id])
				 ->delete('tracker_department_members');

		redirect($_SERVER['HTTP_REFERER']);
	}


	public function getDepartmentEdit() {
		if (!get_permission('tracker_department', 'is_edit')) access_denied();

		$id = $this->input->post('id');
		$data['department'] = $this->tracker_model->get_department_by_id($id);
		$this->load->view('tracker/department_edit', $data);
	}

	public function update_department() {
		if (!get_permission('tracker_department', 'is_edit')) access_denied();

		$id = $this->input->post('id');
		$post = $this->input->post();
		$data = [
				'title' => $post['title'],
				'identifier' => strtoupper($post['identifier']),
				'description' => $post['description'],
				'default_status' => $post['default_status'],
				'is_private' => isset($post['is_private']) ? 1 : 0,
				'auto_join' => isset($post['auto_join']) ? 1 : 0,
				'owner_id' => get_loggedin_user_id(),
				'assigned_issuer' => $post['assigned_issuer'],
			];

		$this->db->where('id', $id)->update('tracker_departments', $data);

		set_alert('success', translate('department_updated_successfully'));
		redirect($_SERVER['HTTP_REFERER']); // Or your preferred redirect
	}


	public function delete_department($id = '')
	{
		if (!get_permission('tracker_department', 'is_delete')) {
			access_denied();
		}

		// Delete related components
		$this->db->where('department_id', $id)->delete('tracker_components');

		// Delete related milestones
		$this->db->where('department_id', $id)->delete('tracker_milestones');

		// Delete related members
		$this->db->where('department_id', $id)->delete('tracker_department_members');

		// Delete the department itself
		$this->db->where('id', $id)->delete('tracker_departments');

		set_alert('success', translate('department_deleted_successfully'));
		redirect($_SERVER['HTTP_REFERER']); // Or use a named route
	}

	public function getDepartmentMembers() {
		$department_id = $this->input->post('department_id');
		$data['members'] = $this->tracker_model->get_department_members($department_id);
		$this->load->view('tracker/department_members', $data);
	}


	public function initiatives() {

		if (!get_permission('tracker_initiatives', 'is_view')) access_denied();

		$data['department'] = $department;
		$data['initiatives'] = $this->tracker_model->getInitiatives();
		$data['active_identifier'] = $department_identifier;
		$data['title'] = translate('initiatives');
		$data['sub_page'] = 'tracker/initiatives';
		$data['main_menu'] = 'tracker';
		$this->load->view('layout/index', $data);
	}

	public function add_initiatives() {
		if (!get_permission('tracker_initiatives', 'is_add')) access_denied();

		$data = [
			'title'       => $this->input->post('title'),
			'description' => $this->input->post('description'),
			'lead_id' => $this->input->post('lead_id'),
		];

		if (!empty($data['title'])) {
			$this->db->insert('tracker_components', $data);
			set_alert('success', translate('component_added_successfully'));
		} else {
			set_alert('error', translate('missing_required_fields'));
		}

		 redirect($_SERVER['HTTP_REFERER']); // Or your preferred redirect
	}


	public function get_initiatives_edit() {
		if (!get_permission('tracker_initiatives', 'is_edit')) access_denied();

		$id = $this->input->post('id');
		$data['component'] = $this->tracker_model->get_component_by_id($id);
		$this->load->view('tracker/initiatives_edit', $data);
	}

	public function delete_initiatives($id = '') {
		if (!get_permission('tracker_initiatives', 'is_delete')) access_denied();

		$this->db->where('id', $id)->delete('tracker_components');
		set_alert('success', translate('component_deleted_successfully'));
		redirect($_SERVER['HTTP_REFERER']); // Or your preferred redirect
	}

	public function update_initiatives() {
		if (!get_permission('tracker_initiatives', 'is_edit')) access_denied();

		$id = $this->input->post('id');
		$data = [
			'title'       => $this->input->post('title'),
			'description' => $this->input->post('description'),
			'lead_id' => $this->input->post('lead_id'),
		];

		$this->db->where('id', $id)->update('tracker_components', $data);
		set_alert('success', translate('component_updated_successfully'));
		redirect($_SERVER['HTTP_REFERER']); // Or your preferred redirect
	}


	public function get_pending_task_details($task_id = null) {
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		if (!$task_id) {
			$task_id = $this->input->get('task_id') ?: $this->input->post('task_id');
		}

		if (!$task_id) {
			http_response_code(400);
			echo json_encode(['error' => 'Task ID is required']);
			return;
		}

		$staff_id = get_loggedin_user_id();
		$role_id = loggedin_role_id();

		// Check if extension columns exist
		$extension_columns_exist = $this->db->field_exists('extension_reason', 'tracker_issues');

		// Build select query based on available columns
		$select_fields = '
			t.*,
			created_staff.name as created_by_name,
			created_staff.photo as created_by_photo,
			assigned_staff.name as assigned_to_name,
			assigned_staff.photo as assigned_to_photo,
			coordinator_staff.name as coordinator_name,
			coordinator_staff.photo as coordinator_photo,
			tc.title as component_title,
			tm.title as milestone_title,
			tt.name as task_type_name,
			parent_task.id as parent_issue_id,
			parent_task.unique_id as parent_issue_unique_id,
			parent_task.task_title as parent_task_title,
			declined_staff.name as declined_by_name,
			approved_staff.name as approved_by_name';

		if ($extension_columns_exist) {
			$select_fields .= ',
			extension_staff.name as extension_requested_by_name';
		}

		$this->db->select($select_fields);
		$this->db->from('tracker_issues t');
		$this->db->join('staff created_staff', 't.created_by = created_staff.id', 'left');
		$this->db->join('staff assigned_staff', 't.assigned_to = assigned_staff.id', 'left');
		$this->db->join('staff coordinator_staff', 't.coordinator = coordinator_staff.id', 'left');
		$this->db->join('staff declined_staff', 't.declined_by = declined_staff.id', 'left');
		$this->db->join('staff approved_staff', 't.approved_by = approved_staff.id', 'left');

		if ($extension_columns_exist) {
			$this->db->join('staff extension_staff', 't.extension_requested_by = extension_staff.id', 'left');
		}

		$this->db->join('tracker_components tc', 't.component = tc.id', 'left');
		$this->db->join('tracker_milestones tm', 't.milestone = tm.id', 'left');
		$this->db->join('task_types tt', 't.task_type = tt.id', 'left');
		$this->db->join('tracker_issues parent_task', 't.parent_issue = parent_task.id', 'left');
		$this->db->where('t.id', $task_id);

		// Role-based access control
		if (in_array($role_id, [1, 2, 3, 5])) {
            // Admin roles can view all tasks
			} elseif ($role_id == 8) {
				// Role 8 can view their own tasks and department members' tasks
				$this->db->group_start();
				$this->db->where('ti.assigned_to', $staff_id);
				$this->db->or_where('ti.assigned_to IN (SELECT id FROM staff WHERE department = (SELECT department FROM staff WHERE id = ' . $staff_id . '))');

				$this->db->or_where('ti.created_by', $staff_id);
				$this->db->group_end();
			} else {
				// Other roles can only view their own tasks or tasks they created
				$this->db->group_start();
				$this->db->where('ti.assigned_to', $staff_id);
				$this->db->or_where('ti.created_by', $staff_id);
				$this->db->group_end();
			}

		$this->db->where_in('t.approval_status', ['pending', 'declined']);

		$task = $this->db->get()->row();

		if (!$task) {
			http_response_code(404);
			echo json_encode(['error' => 'Task not found or access denied']);
			return;
		}

		// Format the response
		$response = [
			'id' => $task->id,
			'unique_id' => $task->unique_id,
			'task_title' => $task->task_title,
			'task_description' => $task->task_description,
			'task_status' => $task->task_status,
			'category' => $task->category,
			'priority_level' => $task->priority_level,
			'estimation_time' => $task->estimation_time,
			'estimated_end_time' => $task->estimated_end_time,
			'logged_at' => $task->logged_at,
			'approval_status' => $task->approval_status,
			'decline_reason' => $task->decline_reason,
			'declined_at' => $task->declined_at,
			'declined_by_name' => $task->declined_by_name,
			'approved_at' => $task->approved_at,
			'approved_by_name' => $task->approved_by_name,
			'created_by_name' => $task->created_by_name,
			'created_by_photo' => $task->created_by_photo ? get_image_url('staff', $task->created_by_photo) : null,
			'assigned_to_name' => $task->assigned_to_name,
			'assigned_to_photo' => $task->assigned_to_photo ? get_image_url('staff', $task->assigned_to_photo) : null,
			'coordinator_name' => $task->coordinator_name,
			'coordinator_photo' => $task->coordinator_photo ? get_image_url('staff', $task->coordinator_photo) : null,
			'component_name' => $task->component_title,
			'milestone_name' => $task->milestone_title,
			'task_type_name' => $task->task_type_name,
			'spent_time' => isset($task->spent_time) ? $task->spent_time : 0,
			'remaining_time' => isset($task->remaining_time) ? $task->remaining_time : 0,
			'parent_issue_id' => $task->parent_issue_id,
			'parent_issue_unique_id' => $task->parent_issue_unique_id,
			'parent_task_title' => $task->parent_task_title
		];

		// Add extension fields if columns exist
		if ($extension_columns_exist) {
			$response['extension_reason'] = isset($task->extension_reason) ? $task->extension_reason : null;
			$response['extension_requested_by_name'] = isset($task->extension_requested_by_name) ? $task->extension_requested_by_name : null;
			$response['extension_requested_at'] = isset($task->extension_requested_at) ? $task->extension_requested_at : null;
			$response['extension_new_due_date'] = isset($task->extension_new_due_date) ? $task->extension_new_due_date : null;
			$response['extension_new_estimation'] = isset($task->extension_new_estimation) ? $task->extension_new_estimation : null;
		} else {
			$response['extension_reason'] = null;
			$response['extension_requested_by_name'] = null;
			$response['extension_requested_at'] = null;
			$response['extension_new_due_date'] = null;
			$response['extension_new_estimation'] = null;
		}

		// Set content type and return JSON
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response));
	}

	public function initiatives_back($department_identifier = '') {

		if (!get_permission('tracker_initiatives', 'is_view')) access_denied();
		if (empty($department_identifier)) redirect('dashboard');
		$department = $this->tracker_model->get_department_by_identifier($department_identifier);
		if (!$department) show_404();

		$data['department'] = $department;
		$data['components'] = $this->tracker_model->get_components_by_department($department->id);
		$data['active_identifier'] = $department_identifier;
		$data['title'] = translate('initiatives');
		$data['sub_page'] = 'tracker/components';
		$data['main_menu'] = 'tracker';
		$this->load->view('layout/index', $data);
	}

	public function add_component() {
		if (!get_permission('tracker_initiatives', 'is_add')) access_denied();

		$data = [
			'department_id'  => $this->input->post('department_id'),
			'title'       => $this->input->post('title'),
			'description' => $this->input->post('description'),
			'lead_id' => $this->input->post('lead_id'),
		];

		if (!empty($data['title']) && !empty($data['department_id'])) {
			$this->db->insert('tracker_components', $data);
			set_alert('success', translate('component_added_successfully'));
		} else {
			set_alert('error', translate('missing_required_fields'));
		}

		 redirect($_SERVER['HTTP_REFERER']); // Or your preferred redirect
	}


	public function get_component_edit() {
		if (!get_permission('tracker_initiatives', 'is_edit')) access_denied();

		$id = $this->input->post('id');
		$data['component'] = $this->tracker_model->get_component_by_id($id);
		$this->load->view('tracker/component_edit', $data);
	}

	public function delete_component($id = '') {
		if (!get_permission('tracker_initiatives', 'is_delete')) access_denied();

		$this->db->where('id', $id)->delete('tracker_components');
		set_alert('success', translate('component_deleted_successfully'));
		redirect($_SERVER['HTTP_REFERER']); // Or your preferred redirect
	}

	public function update_component() {
		if (!get_permission('tracker_initiatives', 'is_edit')) access_denied();

		$id = $this->input->post('id');
		$data = [
			'title'       => $this->input->post('title'),
			'description' => $this->input->post('description'),
			'lead_id' => $this->input->post('lead_id'),
		];

		$this->db->where('id', $id)->update('tracker_components', $data);
		set_alert('success', translate('component_updated_successfully'));
		redirect($_SERVER['HTTP_REFERER']); // Or your preferred redirect
	}



	public function milestones($department_identifier = '') {
		if (!get_permission('tracker_milestone', 'is_view')) access_denied();
		if (empty($department_identifier)) redirect('dashboard');
		$department = $this->tracker_model->get_department_by_identifier($department_identifier);
		if (!$department) show_404();

		$data['department'] = $department;
		$data['milestones'] = $this->tracker_model->get_milestones_by_department($department->id);

		$data['active_identifier'] = $department_identifier;
		$data['title'] = translate('milestones');
		$data['sub_page'] = 'tracker/milestones';
		$data['main_menu'] = 'tracker';
		$this->load->view('layout/index', $data);
	}

	public function add_milestone() {
		if (!get_permission('tracker_milestone', 'is_add')) access_denied();

		$data = [
			'department_id'  => $this->input->post('department_id'),
			'title'       => $this->input->post('title'),
			'description' => $this->input->post('description'),
			'status' => $this->input->post('status'),
			'due_date' => $this->input->post('due_date'),
			'assigned_to' => $this->input->post('assigned_to'),
			'type' => $this->input->post('type'),
			'stage' => $this->input->post('stage'),
			'priority' => $this->input->post('priority'),
			'client_id' => $this->input->post('client_id'),
		];

		if (!empty($data['title']) && !empty($data['department_id'])) {
			$this->db->insert('tracker_milestones', $data);
			if ($this->input->is_ajax_request()) {
				echo json_encode(['success' => true, 'message' => translate('milestone_added_successfully')]);
				return;
			}
			set_alert('success', translate('milestone_added_successfully'));
		} else {
			if ($this->input->is_ajax_request()) {
				echo json_encode(['success' => false, 'message' => translate('missing_required_fields')]);
				return;
			}
			set_alert('error', translate('missing_required_fields'));
		}

		 redirect($_SERVER['HTTP_REFERER']); // Or your preferred redirect
	}

	public function get_milestone_edit() {
		if (!get_permission('tracker_milestone', 'is_edit')) access_denied();

		$id = $this->input->post('id');
		$data['milestone'] = $this->tracker_model->get_milestone_by_id($id);
		$this->load->view('tracker/milestone_edit', $data);
	}

	public function get_milestones_data() {
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		$department_id = $this->input->post('department_id');
		if (empty($department_id)) {
			echo json_encode(['success' => false, 'message' => 'Department ID required']);
			return;
		}

		$this->db->select('tm.*, s.name as assigned_to_name, ci.client_name, ci.company');
		$this->db->from('tracker_milestones tm');
		$this->db->join('staff s', 'tm.assigned_to = s.id', 'left');
		$this->db->join('contact_info ci', 'tm.client_id = ci.id', 'left');
		$this->db->where('tm.department_id', $department_id);
		$this->db->order_by('tm.created_at', 'DESC');
		$milestones = $this->db->get()->result();

		// Add task progress for each milestone
		foreach ($milestones as $milestone) {
			$total_tasks = $this->db->select('COUNT(*) as count')
				->from('tracker_issues')
				->where('milestone', $milestone->id)
				->get()->row()->count;

			$completed_tasks = $this->db->select('COUNT(*) as count')
				->from('tracker_issues')
				->where('milestone', $milestone->id)
				->where_in('task_status', ['completed', 'done', 'solved'])
				->get()->row()->count;

			$milestone->total_tasks = $total_tasks;
			$milestone->completed_tasks = $completed_tasks;
			$milestone->progress_percentage = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100) : 0;
		}

		echo json_encode(['success' => true, 'milestones' => $milestones]);
	}

	public function delete_milestone($id = '') {
		if (!get_permission('tracker_milestone', 'is_delete')) access_denied();

		$this->db->where('id', $id)->delete('tracker_milestones');
		if ($this->input->is_ajax_request()) {
			echo json_encode(['success' => true, 'message' => translate('milestone_deleted_successfully')]);
			return;
		}
		set_alert('success', translate('milestone_deleted_successfully'));
		redirect($_SERVER['HTTP_REFERER']); // Or your preferred redirect
	}

	public function update_milestone() {
		if (!get_permission('tracker_milestone', 'is_edit')) access_denied();

		$id = $this->input->post('id');
		$status = $this->input->post('status');
		$remarks = $this->input->post('remarks');

		// Validate remarks for submitted/done status
		if (in_array($status, ['submitted', 'done']) && empty($remarks)) {
			set_alert('error', 'Remarks are required when status is submitted or done.');
			redirect($_SERVER['HTTP_REFERER']);
			return;
		}

		// Check if milestone is being marked as completed/done/solved
		if (in_array($status, ['completed', 'done', 'solved'])) {
			// Check if all related tracker tasks are completed
			$incomplete_tasks = $this->db->select('COUNT(*) as count')
				->from('tracker_issues')
				->where('milestone', $id)
				->where_not_in('task_status', ['completed', 'done', 'solved', 'hold', 'canceled'])
				->get()->row();

			if ($incomplete_tasks->count > 0) {
				set_alert('error', "Cannot complete milestone. There are {$incomplete_tasks->count} incomplete tasks related to this milestone. Please complete all tasks first.");
				redirect($_SERVER['HTTP_REFERER']);
				return;
			}
		}

		$data = [
			'title'       => $this->input->post('title'),
			'description' => $this->input->post('description'),
			'status' => $status,
			'due_date' => $this->input->post('due_date'),
			'assigned_to' => $this->input->post('assigned_to'),
			'type' => $this->input->post('type'),
			'stage' => $this->input->post('stage'),
			'priority' => $this->input->post('priority'),
			'remarks' => $remarks,
			'client_id' => $this->input->post('client_id'),
		];

		$this->db->where('id', $id)->update('tracker_milestones', $data);
		if ($this->input->is_ajax_request()) {
			echo json_encode(['success' => true, 'message' => translate('milestone_updated_successfully')]);
			return;
		}
		set_alert('success', translate('milestone_updated_successfully'));
		redirect($_SERVER['HTTP_REFERER']); // Or your preferred redirect
	}

	public function check_milestone_tasks() {
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		$milestone_id = $this->input->post('milestone_id');
		if (empty($milestone_id)) {
			echo json_encode(['success' => false, 'message' => 'Milestone ID is required']);
			return;
		}

		// Get incomplete tasks count
		$incomplete_tasks = $this->db->select('COUNT(*) as count')
			->from('tracker_issues')
			->where('milestone', $milestone_id)
			->where_not_in('task_status', ['completed', 'done', 'solved', 'hold', 'canceled'])
			->get()->row();

		// Get total tasks count
		$total_tasks = $this->db->select('COUNT(*) as count')
			->from('tracker_issues')
			->where('milestone', $milestone_id)
			->get()->row();

		// Get incomplete task details
		$incomplete_task_details = $this->db->select('unique_id, task_title, task_status')
			->from('tracker_issues')
			->where('milestone', $milestone_id)
			->where_not_in('task_status', ['completed', 'done', 'solved', 'hold', 'canceled'])
			->get()->result();

		echo json_encode([
			'success' => true,
			'incomplete_count' => $incomplete_tasks->count,
			'total_count' => $total_tasks->count,
			'can_complete' => $incomplete_tasks->count == 0,
			'incomplete_tasks' => $incomplete_task_details
		]);
	}

	public function verify_milestone() {
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		// Check if user has permission to verify (roles 1,2,3,5,8)
		if (!in_array(loggedin_role_id(), [1, 2, 3, 5, 8])) {
			echo json_encode(['success' => false, 'message' => 'Access denied']);
			return;
		}

		$milestone_id = $this->input->post('milestone_id');
		$verification_remarks = trim($this->input->post('verification_remarks'));

		if (empty($milestone_id)) {
			echo json_encode(['success' => false, 'message' => 'Milestone ID is required']);
			return;
		}

		if (empty($verification_remarks)) {
			echo json_encode(['success' => false, 'message' => 'Verification remarks are required']);
			return;
		}

		// Check if milestone exists and is completed/done/solved
		$milestone = $this->db->where('id', $milestone_id)
							 ->where_in('status', ['completed', 'done', 'solved'])
							 ->get('tracker_milestones')
							 ->row();

		if (!$milestone) {
			echo json_encode(['success' => false, 'message' => 'Milestone not found or not eligible for verification']);
			return;
		}

		// Update milestone as verified
		$update_data = [
			'is_verified' => 1,
			'verified_by' => get_loggedin_user_id(),
			'verified_at' => date('Y-m-d H:i:s'),
			'verification_remarks' => $verification_remarks
		];

		$result = $this->db->where('id', $milestone_id)->update('tracker_milestones', $update_data);

		if ($result) {
			echo json_encode(['success' => true, 'message' => 'Milestone verified successfully']);
		} else {
			echo json_encode(['success' => false, 'message' => 'Failed to verify milestone']);
		}
	}

	public function get_milestone_verification_details() {
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		$milestone_id = $this->input->post('milestone_id');
		if (empty($milestone_id)) {
			echo json_encode(['success' => false, 'message' => 'Milestone ID is required']);
			return;
		}

		$this->db->select('tm.*, s.name as verified_by_name');
		$this->db->from('tracker_milestones tm');
		$this->db->join('staff s', 'tm.verified_by = s.id', 'left');
		$this->db->where('tm.id', $milestone_id);
		$milestone = $this->db->get()->row();

		if (!$milestone) {
			echo json_encode(['success' => false, 'message' => 'Milestone not found']);
			return;
		}

		echo json_encode([
			'success' => true,
			'milestone' => [
				'title' => $milestone->title,
				'description' => $milestone->description,
				'status' => $milestone->status,
				'is_verified' => $milestone->is_verified,
				'verified_by_name' => $milestone->verified_by_name,
				'verified_at' => $milestone->verified_at ? date('d M Y, h:i A', strtotime($milestone->verified_at)) : null,
				'verification_remarks' => $milestone->verification_remarks,
				'remarks' => $milestone->remarks,
				'assigned_to' => $milestone->assigned_to
			]
		]);
	}

	public function award_champion_badge() {
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		if (!in_array(loggedin_role_id(), [1, 2, 3, 5, 8])) {
			echo json_encode(['success' => false, 'message' => 'Access denied']);
			return;
		}

		$milestone_id = $this->input->post('milestone_id');
		$badge_reason = trim($this->input->post('badge_reason'));

		if (empty($milestone_id) || empty($badge_reason)) {
			echo json_encode(['success' => false, 'message' => 'Milestone ID and reason are required']);
			return;
		}

		$milestone = $this->db->where('id', $milestone_id)->get('tracker_milestones')->row();
		if (!$milestone) {
			echo json_encode(['success' => false, 'message' => 'Milestone not found']);
			return;
		}

		$badge_data = [
			'milestone_id' => $milestone_id,
			'staff_id' => $milestone->assigned_to,
			'awarded_by' => get_loggedin_user_id(),
			'badge_reason' => $badge_reason,
			'awarded_at' => date('Y-m-d H:i:s')
		];

		$result = $this->db->insert('milestone_champion_badges', $badge_data);
		echo json_encode([
			'success' => $result,
			'message' => $result ? 'Champion badge awarded successfully' : 'Failed to award badge'
		]);
	}

	public function champion_badges() {
		if (!get_permission('tracker_milestone', 'is_view')) access_denied();

		$this->db->select('mcb.*, tm.title as milestone_title, s1.name as staff_name, s2.name as awarded_by_name');
		$this->db->from('milestone_champion_badges mcb');
		$this->db->join('tracker_milestones tm', 'mcb.milestone_id = tm.id');
		$this->db->join('staff s1', 'mcb.staff_id = s1.id');
		$this->db->join('staff s2', 'mcb.awarded_by = s2.id');
		$this->db->order_by('mcb.awarded_at', 'DESC');
		$data['badges'] = $this->db->get()->result();

		$data['title'] = 'Champion Badges Report';
		$data['sub_page'] = 'tracker/champion_badges';
		$data['main_menu'] = 'champion_badges';
		$this->load->view('layout/index', $data);
	}

	public function bulk_redeem_badges() {
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		if (!in_array(loggedin_role_id(), [1, 2, 3, 5, 8])) {
			echo json_encode(['success' => false, 'message' => 'Access denied']);
			return;
		}

		$badge_ids = $this->input->post('badge_ids');
		if (empty($badge_ids) || !is_array($badge_ids)) {
			echo json_encode(['success' => false, 'message' => 'No badges selected']);
			return;
		}

		$this->db->where_in('id', $badge_ids);
		$this->db->where('status', 'active');
		$result = $this->db->update('milestone_champion_badges', [
			'status' => 'redeemed',
			'redeemed_at' => date('Y-m-d H:i:s')
		]);

		echo json_encode([
			'success' => $result,
			'message' => $result ? 'Badges redeemed successfully' : 'Failed to redeem badges'
		]);
	}


	public function add_customer_query_column() {
		// Check if column already exists
		if (!$this->db->field_exists('customer_query_data', 'tracker_issues')) {
			// Add the column
			$this->db->query("ALTER TABLE tracker_issues ADD COLUMN customer_query_data TEXT NULL AFTER category");
			echo "Column 'customer_query_data' added successfully to tracker_issues table.";
		} else {
			echo "Column 'customer_query_data' already exists in tracker_issues table.";
		}
	}

	public function task_types() {
		$data['task_types'] = $this->db->get('task_types')->result();
		$data['title'] = translate('task_types');
		$data['sub_page'] = 'tracker/task_types';
		$data['main_menu'] = 'tracker';
		$this->load->view('layout/index', $data);
	}

	public function add_task_type() {
		$data = [
			'name' => $this->input->post('name'),
			'description' => $this->input->post('description'),
			'created_by' => get_loggedin_user_id(),
			'created_at' => date('Y-m-d H:i:s')
		];

		$this->db->insert('task_types', $data);
		set_alert('success', translate('task_type_added_successfully'));
		redirect('tracker/task_types');
	}

	public function get_task_type_edit() {
		$data['task_type_id'] = $this->input->post('id');
		$this->load->view('tracker/task_type_edit', $data);
	}

	public function update_task_type() {

		$id = $this->input->post('id');
		$data = [
			'name' => $this->input->post('name'),
			'description' => $this->input->post('description')
		];

		$this->db->where('id', $id)->update('task_types', $data);
		set_alert('success', translate('task_type_updated_successfully'));
		redirect('tracker/task_types');
	}

	public function delete_task_type($id) {

		$this->db->where('id', $id)->delete('task_types');
		set_alert('success', translate('task_type_deleted_successfully'));
		redirect('tracker/task_types');
	}

	public function save_issue()
{
    if ($this->input->post()) {

        $UserID = get_loggedin_user_id();

        // Extract POST data
        $post = $this->input->post();

        // Validate Customer Query fields if category is Customer Query
        if (isset($post['category']) && $post['category'] === 'Customer Query') {
            $errors = [];
            if (empty($post['source'])) {
                $errors[] = 'Source is required for Customer Query';
            }
            if (empty($post['contact_info'])) {
                $errors[] = 'Contact info is required for Customer Query';
            }
            if (empty($post['request_body'])) {
                $errors[] = 'Request body is required for Customer Query';
            }

            if (!empty($errors)) {
                set_alert('error', implode(', ', $errors));
                redirect($_SERVER['HTTP_REFERER']);
                return;
            }
        }

		// Generate unique ID based on department
		$identifier = $post['department']; // Example: EMP, SCH, etc.

		// Get max number for this prefix
		$this->db->select('MAX(CAST(SUBSTRING_INDEX(unique_id, "-", -1) AS UNSIGNED)) as max_num');
		$this->db->like('unique_id', $identifier . '-', 'after');
		$this->db->from('tracker_issues');
		$query = $this->db->get();
		$row = $query->row();

		$max_num = $row && $row->max_num ? (int)$row->max_num : 0;

		// Generate next available number
		$next_number = $max_num + 1;

		// Format unique ID
		$unique_id = $identifier . '-' . $next_number;


        // Handle parent_issue fallback logic
        $parent_issue = null;
        if (!empty($post['parent_issue'])) {
            $parent_issue = (int)$post['parent_issue'];
        }

        // Determine approval status based on creator and assignee
        $approval_status = 'approved'; // Default to approved
        if ($UserID != $post['assigned_to']) {
            // If creator and assignee are different, task needs approval
            $approval_status = 'pending';
        }

        // Prepare data for insertion
        $data = [
            'created_by' => $UserID, // OR $post['staff_id'] if sent from form
            'unique_id' => $unique_id,
            'department' => $post['department'],
            'category' => $post['category'] ?? null,
            'task_title' => $post['task_title'],
            'task_description' => $this->input->post('task_description', false), // Allow HTML
            'task_status' => $post['task_status'],
            'priority_level' => $post['priority_level'] ?? null,
            'assigned_to' => $post['assigned_to'],
            'coordinator' => $post['coordinator'] ?? null,
            'label' => !empty($post['label']) ? implode(',', $post['label']) : null,
            'component' => $post['component'] ?? null,
            'estimation_time' => $post['estimation_time'] ?? null,
            'milestone' => $post['milestone'] ?? null,
            'estimated_end_time' => !empty($post['estimated_end_time']) ? date('Y-m-d 23:59:59', strtotime($post['estimated_end_time'])) : null,
            'parent_issue' => $parent_issue,
            'sop_ids' => !empty($post['sop_ids']) ? implode(',', $post['sop_ids']) : null,
            'task_type' => $post['task_type'] ?? null,
            'approval_status' => $approval_status,
            'logged_at' => date('Y-m-d H:i:s'),
        ];

        // Handle Customer Query data
        if (isset($post['category']) && $post['category'] === 'Customer Query') {
            $customer_data = [
                'source' => $post['source'] ?? null,
                'contact_info' => $post['contact_info'] ?? null,
                'request_body' => $post['request_body'] ?? null,
                'requested_at' => !empty($post['requested_at']) ? date('Y-m-d H:i:s', strtotime($post['requested_at'])) : null
            ];
            $data['customer_query_data'] = json_encode($customer_data);
        }

        // If auto-approved, set approval details
        if ($approval_status === 'approved') {
            $data['approved_at'] = date('Y-m-d H:i:s');
            $data['approved_by'] = $UserID;
        }

        // Insert into DB
        $inserted = $this->db->insert('tracker_issues', $data);
        $issue_id = $this->db->insert_id();

		if ($inserted && $issue_id) {
			// Add to staff_task_log
			$task_log_data = [
				'staff_id'    => $post['assigned_to'],
				'location'    => 'Tracker',
				'task_title'  => $post['task_title'],
				'start_time'  => date('Y-m-d H:i:s'),
				'task_status' => 'In Progress',
				'logged_at'   => date('Y-m-d H:i:s'),
				'tracker_id' => $issue_id
			];

			$this->db->insert('staff_task_log', $task_log_data);
		}

        if ($inserted) {
            if ($approval_status === 'pending') {
                // Send notification to assignee for approval
                $this->send_task_assignment_notification($issue_id, $post['assigned_to']);
                set_alert('success', translate('Task created and sent for approval'));
            } else {
                set_alert('success', translate('new issue added successfully'));
            }
        } else {
            set_alert('error', translate('failed to create issue'));
        }

        redirect($_SERVER['HTTP_REFERER']);
    }
}


	public function my_issues() {
		if (!get_permission('tracker_issues', 'is_view')) {
			access_denied();
		}

		$UserID = get_loggedin_user_id();
		// Get all issues
		$this->db->select('*');
		$this->db->from('tracker_issues');
		$this->db->where('assigned_to', $UserID);
		$this->db->order_by('logged_at', 'DESC');
		$data['all_issues'] = $this->db->get()->result();

		// Group issues by status
		$data['grouped_issues'] = array();
		foreach ($data['all_issues'] as $issue) {
			$status_key = trim($issue->task_status);
			$data['grouped_issues'][$status_key][] = $issue;
		}

		// Get issue counts by status
		$data['issue_counts'] = array();
		$statuses = $this->tracker_model->get_all_statuses();
		$status_keys = array_keys($statuses);

		foreach ($status_keys as $status_key) {
			$this->db->where('task_status', $status_key);
			$data['issue_counts'][$status_key] = $this->db->count_all_results('tracker_issues');
		}

		$staff_query = $this->db->get('staff')->result();
		$data['staff_lookup'] = [];
		foreach ($staff_query as $s) {
			$data['staff_lookup'][$s->id] = $s;
		}

		$dept_query = $this->db->get('tracker_departments')->result();
		$data['dept_lookup'] = [];
		foreach ($dept_query as $d) {
			$data['dept_lookup'][$d->id] = $d;
		}

		$mile_query = $this->db->get('tracker_milestones')->result();
		$data['mile_lookup'] = [];
		foreach ($mile_query as $d) {
			$data['mile_lookup'][$d->id] = $d;
		}

		$comp_query = $this->db->get('tracker_components')->result();
		$data['comp_lookup'] = [];
		foreach ($comp_query as $d) {
			$data['comp_lookup'][$d->id] = $d;
		}

		$label_query = $this->db->get('task_labels')->result();
		$data['label_lookup'] = [];
		foreach ($label_query as $d) {
			$data['label_lookup'][$d->id] = $d;
		}

		$sop_query = $this->db->get('sop')->result();
		$data['sop_lookup'] = [];
		foreach ($sop_query as $d) {
			$data['sop_lookup'][$d->id] = $d;
		}

		$task_type_query = $this->db->get('task_types')->result();
		$data['task_type_lookup'] = [];
		foreach ($task_type_query as $d) {
			$data['task_type_lookup'][$d->id] = $d;
		}

		// Add status configuration for the modal
		$data['status_config'] = $this->tracker_model->get_all_statuses();
		$data['priority_config'] = $this->tracker_model->get_all_priority_details();

		$data['active_identifier'] = $identifier;
		$data['title'] = translate('Issue Tracker');
		$data['sub_page'] = 'tracker/my_issues';
		$data['main_menu'] = 'tracker';

		// Load additional JS for the modal
		$data['headerelements'] = [
			'js' => [
				'vendor/moment/moment.min.js',
				'vendor/daterangepicker/daterangepicker.js',
				'vendor/dropify/js/dropify.min.js',
			],
			'css' => [
				'vendor/daterangepicker/daterangepicker.css',
				'vendor/dropify/css/dropify.min.css',
                'vendor/moment/moment.js',
			]
		];

		$this->load->view('layout/index', $data);
	}

	// AJAX endpoint to get issues data
	public function get_my_issues_data() {
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		if (!get_permission('tracker_issues', 'is_view')) {
			$this->output
				->set_status_header(403)
				->set_content_type('application/json')
				->set_output(json_encode(['success' => false, 'message' => 'Access denied']));
			return;
		}

		$UserID = get_loggedin_user_id();
		$load_all = $this->input->get('load_all') === 'true';

		// Get issues with related data in one query
		$this->db->select('
			ti.*,
			s.name as assigned_to_name,
			s.photo as assigned_to_photo,
			tc.title as component_title,
			tm.title as milestone_title,
			tt.name as task_type_name,
			parent_task.unique_id as parent_unique_id
		');
		$this->db->from('tracker_issues ti');
		$this->db->join('staff s', 'ti.assigned_to = s.id', 'left');
		$this->db->join('tracker_components tc', 'ti.component = tc.id', 'left');
		$this->db->join('tracker_milestones tm', 'ti.milestone = tm.id', 'left');
		$this->db->join('task_types tt', 'ti.task_type = tt.id', 'left');
		$this->db->join('tracker_issues parent_task', 'ti.parent_issue = parent_task.id', 'left');
		$this->db->where('ti.assigned_to', $UserID);
		$this->db->where('ti.approval_status !=', 'pending'); // Exclude pending tasks
		$this->db->where('ti.approval_status !=', 'declined'); // Exclude pending tasks


		// Apply date filter if not loading all
		if (!$load_all) {
			$thirty_days_ago = date('Y-m-d H:i:s', strtotime('-30 days'));
			$this->db->where('ti.logged_at >=', $thirty_days_ago);
		}

		$this->db->order_by('ti.logged_at', 'DESC');
		$all_issues = $this->db->get()->result();

		// Process labels for each issue
		foreach ($all_issues as $issue) {
			if (!empty($issue->label)) {
				$label_ids = explode(',', $issue->label);
				$label_names = [];
				foreach ($label_ids as $label_id) {
					$label_id = trim($label_id);
					$label = $this->db->select('name')->where('id', $label_id)->get('task_labels')->row();
					if ($label) {
						$label_names[] = $label->name;
					}
				}
				$issue->label_names = implode(', ', $label_names);
			} else {
				$issue->label_names = '';
			}

			// Add photo URL
			if ($issue->assigned_to_photo) {
				$issue->assigned_to_photo = get_image_url('staff', $issue->assigned_to_photo);
			}
		}

		// Get status configuration
		$status_config = $this->tracker_model->get_all_statuses();

		// Define priority order
		$priority_order = ['todo', 'in_progress', 'completed'];
		$ordered_statuses = [];

		// Add priority statuses first
		foreach ($priority_order as $status) {
			if (isset($status_config[$status])) {
				$ordered_statuses[$status] = $status_config[$status];
			}
		}

		// Add remaining statuses
		foreach ($status_config as $status => $config) {
			if (!in_array($status, $priority_order)) {
				$ordered_statuses[$status] = $config;
			}
		}

		// Initialize all statuses with empty arrays
		$grouped_issues = [];
		foreach ($ordered_statuses as $status => $config) {
			$grouped_issues[$status] = [];
		}

		// Group issues by status
		foreach ($all_issues as $issue) {
			$status_key = trim($issue->task_status);
			if (isset($grouped_issues[$status_key])) {
				$grouped_issues[$status_key][] = $issue;
			}
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'success' => true,
				'issues' => $grouped_issues,
				'status_config' => $ordered_statuses,
				'load_all' => $load_all,
				'total_count' => count($all_issues)
			]));
	}

	public function my_coordination() {
		if (!get_permission('tracker_issues', 'is_view')) {
			access_denied();
		}

		// Add status configuration for the modal
		$data['status_config'] = $this->tracker_model->get_all_statuses();
		$data['priority_config'] = $this->tracker_model->get_all_priority_details();

		$data['active_identifier'] = $identifier;
		$data['title'] = translate('Issue Tracker');
		$data['sub_page'] = 'tracker/my_coordination';
		$data['main_menu'] = 'tracker';

		// Load additional JS for the modal
		$data['headerelements'] = [
			'js' => [
				'vendor/moment/moment.min.js',
				'vendor/daterangepicker/daterangepicker.js',
				'vendor/dropify/js/dropify.min.js',
			],
			'css' => [
				'vendor/daterangepicker/daterangepicker.css',
				'vendor/dropify/css/dropify.min.css',
                'vendor/moment/moment.js',
			]
		];

		$this->load->view('layout/index', $data);
	}

	// AJAX endpoint to get issues data
	public function get_my_coordination_data() {
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		if (!get_permission('tracker_issues', 'is_view')) {
			$this->output
				->set_status_header(403)
				->set_content_type('application/json')
				->set_output(json_encode(['success' => false, 'message' => 'Access denied']));
			return;
		}

		$UserID = get_loggedin_user_id();
		$role_id = loggedin_role_id();
		$load_all = $this->input->get('load_all') === 'true';

		// Get issues with related data in one query
		$this->db->select('
			ti.*,
			s.name as assigned_to_name,
			s.photo as assigned_to_photo,
			tc.title as component_title,
			tm.title as milestone_title,
			tt.name as task_type_name,
			parent_task.unique_id as parent_unique_id
		');
		$this->db->from('tracker_issues ti');
		$this->db->join('staff s', 'ti.assigned_to = s.id', 'left');
		$this->db->join('tracker_components tc', 'ti.component = tc.id', 'left');
		$this->db->join('tracker_milestones tm', 'ti.milestone = tm.id', 'left');
		$this->db->join('task_types tt', 'ti.task_type = tt.id', 'left');
		$this->db->join('tracker_issues parent_task', 'ti.parent_issue = parent_task.id', 'left');

		// Check if user has access - roles 1,2,3,5 or assigned coordinator
		if (!in_array($role_id, [1, 2, 3, 5])) {
			$this->db->where('ti.coordinator', $UserID);
		}
		$this->db->where('ti.coordinator !=', Null);
		$this->db->where('ti.approval_status !=', 'pending'); // Exclude pending tasks
		$this->db->where('ti.approval_status !=', 'declined'); // Exclude pending tasks


		// Apply date filter if not loading all
		if (!$load_all) {
			$thirty_days_ago = date('Y-m-d H:i:s', strtotime('-30 days'));
			$this->db->where('ti.logged_at >=', $thirty_days_ago);
		}

		$this->db->order_by('ti.logged_at', 'DESC');
		$all_issues = $this->db->get()->result();

		// Process labels for each issue
		foreach ($all_issues as $issue) {
			if (!empty($issue->label)) {
				$label_ids = explode(',', $issue->label);
				$label_names = [];
				foreach ($label_ids as $label_id) {
					$label_id = trim($label_id);
					$label = $this->db->select('name')->where('id', $label_id)->get('task_labels')->row();
					if ($label) {
						$label_names[] = $label->name;
					}
				}
				$issue->label_names = implode(', ', $label_names);
			} else {
				$issue->label_names = '';
			}

			// Add photo URL
			if ($issue->assigned_to_photo) {
				$issue->assigned_to_photo = get_image_url('staff', $issue->assigned_to_photo);
			}
		}

		// Get status configuration
		$status_config = $this->tracker_model->get_all_statuses();

		// Define priority order
		$priority_order = ['todo', 'in_progress', 'completed'];
		$ordered_statuses = [];

		// Add priority statuses first
		foreach ($priority_order as $status) {
			if (isset($status_config[$status])) {
				$ordered_statuses[$status] = $status_config[$status];
			}
		}

		// Add remaining statuses
		foreach ($status_config as $status => $config) {
			if (!in_array($status, $priority_order)) {
				$ordered_statuses[$status] = $config;
			}
		}

		// Initialize all statuses with empty arrays
		$grouped_issues = [];
		foreach ($ordered_statuses as $status => $config) {
			$grouped_issues[$status] = [];
		}

		// Group issues by status
		foreach ($all_issues as $issue) {
			$status_key = trim($issue->task_status);
			if (isset($grouped_issues[$status_key])) {
				$grouped_issues[$status_key][] = $issue;
			}
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'success' => true,
				'issues' => $grouped_issues,
				'status_config' => $ordered_statuses,
				'load_all' => $load_all,
				'total_count' => count($all_issues)
			]));
	}


	// AJAX endpoint to get all issues data
	public function get_all_issues_data() {
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		if (!get_permission('tracker_issues', 'is_view')) {
			$this->output
				->set_status_header(403)
				->set_content_type('application/json')
				->set_output(json_encode(['success' => false, 'message' => 'Access denied']));
			return;
		}

		$load_all = $this->input->get('load_all') === 'true';

		// Get issues with related data in one query
		$this->db->select('
			ti.*,
			s.name as assigned_to_name,
			s.photo as assigned_to_photo,
			tc.title as component_title,
			tm.title as milestone_title,
			tt.name as task_type_name,
			parent_task.unique_id as parent_unique_id
		');
		$this->db->from('tracker_issues ti');
		$this->db->join('staff s', 'ti.assigned_to = s.id', 'left');
		$this->db->join('tracker_components tc', 'ti.component = tc.id', 'left');
		$this->db->join('tracker_milestones tm', 'ti.milestone = tm.id', 'left');
		$this->db->join('task_types tt', 'ti.task_type = tt.id', 'left');
		$this->db->join('tracker_issues parent_task', 'ti.parent_issue = parent_task.id', 'left');
		$this->db->where('ti.approval_status !=', 'pending'); // Exclude pending tasks
		$this->db->where('ti.approval_status !=', 'declined'); // Exclude pending tasks

		// Apply date filter if not loading all
		if (!$load_all) {
			$thirty_days_ago = date('Y-m-d H:i:s', strtotime('-30 days'));
			$this->db->where('ti.logged_at >=', $thirty_days_ago);
		}

		$this->db->order_by('ti.logged_at', 'DESC');
		$all_issues = $this->db->get()->result();

		// Process labels for each issue
		foreach ($all_issues as $issue) {
			if (!empty($issue->label)) {
				$label_ids = explode(',', $issue->label);
				$label_names = [];
				foreach ($label_ids as $label_id) {
					$label_id = trim($label_id);
					$label = $this->db->select('name')->where('id', $label_id)->get('task_labels')->row();
					if ($label) {
						$label_names[] = $label->name;
					}
				}
				$issue->label_names = implode(', ', $label_names);
			} else {
				$issue->label_names = '';
			}

			// Add photo URL
			if ($issue->assigned_to_photo) {
				$issue->assigned_to_photo = get_image_url('staff', $issue->assigned_to_photo);
			}
		}

		// Get status configuration
		$status_config = $this->tracker_model->get_all_statuses();

		// Define priority order
		$priority_order = ['todo', 'in_progress', 'completed'];
		$ordered_statuses = [];

		// Add priority statuses first
		foreach ($priority_order as $status) {
			if (isset($status_config[$status])) {
				$ordered_statuses[$status] = $status_config[$status];
			}
		}

		// Add remaining statuses
		foreach ($status_config as $status => $config) {
			if (!in_array($status, $priority_order)) {
				$ordered_statuses[$status] = $config;
			}
		}

		// Initialize all statuses with empty arrays
		$grouped_issues = [];
		foreach ($ordered_statuses as $status => $config) {
			$grouped_issues[$status] = [];
		}

		// Group issues by status
		foreach ($all_issues as $issue) {
			$status_key = trim($issue->task_status);
			if (isset($grouped_issues[$status_key])) {
				$grouped_issues[$status_key][] = $issue;
			}
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'success' => true,
				'issues' => $grouped_issues,
				'status_config' => $ordered_statuses,
				'load_all' => $load_all,
				'total_count' => count($all_issues)
			]));
	}

	// AJAX endpoint to get department-specific issues data
	public function get_department_issues_data($identifier = '') {
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		if (!get_permission('tracker_issues', 'is_view')) {
			$this->output
				->set_status_header(403)
				->set_content_type('application/json')
				->set_output(json_encode(['success' => false, 'message' => 'Access denied']));
			return;
		}

		if (empty($identifier)) {
			$this->output
				->set_status_header(400)
				->set_content_type('application/json')
				->set_output(json_encode(['success' => false, 'message' => 'Department identifier required']));
			return;
		}

		$load_all = $this->input->get('load_all') === 'true';

		// Get department-specific issues with related data
		$this->db->select('
			ti.*,
			s.name as assigned_to_name,
			s.photo as assigned_to_photo,
			tc.title as component_title,
			tm.title as milestone_title,
			tt.name as task_type_name,
			parent_task.unique_id as parent_unique_id
		');
		$this->db->from('tracker_issues ti');
		$this->db->join('staff s', 'ti.assigned_to = s.id', 'left');
		$this->db->join('tracker_components tc', 'ti.component = tc.id', 'left');
		$this->db->join('tracker_milestones tm', 'ti.milestone = tm.id', 'left');
		$this->db->join('task_types tt', 'ti.task_type = tt.id', 'left');
		$this->db->join('tracker_issues parent_task', 'ti.parent_issue = parent_task.id', 'left');
		$this->db->where('ti.department', $identifier);
		$this->db->where('ti.approval_status !=', 'pending'); // Exclude pending tasks
		$this->db->where('ti.approval_status !=', 'declined'); // Exclude pending tasks

		// Apply date filter if not loading all
		if (!$load_all) {
			$thirty_days_ago = date('Y-m-d H:i:s', strtotime('-30 days'));
			$this->db->where('ti.logged_at >=', $thirty_days_ago);
		}

		$this->db->order_by('ti.logged_at', 'DESC');
		$all_issues = $this->db->get()->result();

		// Process labels for each issue
		foreach ($all_issues as $issue) {
			if (!empty($issue->label)) {
				$label_ids = explode(',', $issue->label);
				$label_names = [];
				foreach ($label_ids as $label_id) {
					$label_id = trim($label_id);
					$label = $this->db->select('name')->where('id', $label_id)->get('task_labels')->row();
					if ($label) {
						$label_names[] = $label->name;
					}
				}
				$issue->label_names = implode(', ', $label_names);
			} else {
				$issue->label_names = '';
			}

			// Add photo URL
			if ($issue->assigned_to_photo) {
				$issue->assigned_to_photo = get_image_url('staff', $issue->assigned_to_photo);
			}
		}

		// Get status configuration
		$status_config = $this->tracker_model->get_all_statuses();

		// Define priority order
		$priority_order = ['todo', 'in_progress', 'completed'];
		$ordered_statuses = [];

		// Add priority statuses first
		foreach ($priority_order as $status) {
			if (isset($status_config[$status])) {
				$ordered_statuses[$status] = $status_config[$status];
			}
		}

		// Add remaining statuses
		foreach ($status_config as $status => $config) {
			if (!in_array($status, $priority_order)) {
				$ordered_statuses[$status] = $config;
			}
		}

		// Initialize all statuses with empty arrays
		$grouped_issues = [];
		foreach ($ordered_statuses as $status => $config) {
			$grouped_issues[$status] = [];
		}

		// Group issues by status
		foreach ($all_issues as $issue) {
			$status_key = trim($issue->task_status);
			if (isset($grouped_issues[$status_key])) {
				$grouped_issues[$status_key][] = $issue;
			}
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'success' => true,
				'issues' => $grouped_issues,
				'status_config' => $ordered_statuses,
				'load_all' => $load_all,
				'total_count' => count($all_issues)
			]));
	}

	public function all_issues() {
		if (!get_permission('tracker_issues', 'is_view')) {
			access_denied();
		}

		// Get all issues
		$this->db->select('*');
		$this->db->from('tracker_issues');
		$this->db->order_by('logged_at', 'DESC');
		$data['all_issues'] = $this->db->get()->result();

		// Group issues by status
		$data['grouped_issues'] = array();
		foreach ($data['all_issues'] as $issue) {
			$status_key = trim($issue->task_status);
			$data['grouped_issues'][$status_key][] = $issue;
		}

		// Get issue counts by status
		$data['issue_counts'] = array();
		$statuses = $this->tracker_model->get_all_statuses();
		$status_keys = array_keys($statuses);

		foreach ($status_keys as $status_key) {
			$this->db->where('task_status', $status_key);
			$data['issue_counts'][$status_key] = $this->db->count_all_results('tracker_issues');
		}

		$staff_query = $this->db->get('staff')->result();

		// ❌ Remove Superadmin (ID 1)
		unset($staff_query[0]);

		$data['staff_lookup'] = [];
		foreach ($staff_query as $s) {
			$data['staff_lookup'][$s->id] = $s;
		}

		$dept_query = $this->db->get('tracker_departments')->result();
		$data['dept_lookup'] = [];
		foreach ($dept_query as $d) {
			$data['dept_lookup'][$d->id] = $d;
		}

		$mile_query = $this->db->get('tracker_milestones')->result();
		$data['mile_lookup'] = [];
		foreach ($mile_query as $d) {
			$data['mile_lookup'][$d->id] = $d;
		}

		$comp_query = $this->db->get('tracker_components')->result();
		$data['comp_lookup'] = [];
		foreach ($comp_query as $d) {
			$data['comp_lookup'][$d->id] = $d;
		}

		$label_query = $this->db->get('task_labels')->result();
		$data['label_lookup'] = [];
		foreach ($label_query as $d) {
			$data['label_lookup'][$d->id] = $d;
		}

		$sop_query = $this->db->get('sop')->result();
		$data['sop_lookup'] = [];
		foreach ($sop_query as $d) {
			$data['sop_lookup'][$d->id] = $d;
		}

		$task_type_query = $this->db->get('task_types')->result();
		$data['task_type_lookup'] = [];
		foreach ($task_type_query as $d) {
			$data['task_type_lookup'][$d->id] = $d;
		}

		// Add status configuration for the modal
		$data['status_config'] = $this->tracker_model->get_all_statuses();
		$data['priority_config'] = $this->tracker_model->get_all_priority_details();

		$data['active_identifier'] = $identifier;
		$data['title'] = translate('Issue Tracker');
		$data['sub_page'] = 'tracker/all_issues';
		$data['main_menu'] = 'tracker';

		// Load additional JS for the modal
		$data['headerelements'] = [
			'js' => [
				'vendor/moment/moment.min.js',
				'vendor/daterangepicker/daterangepicker.js',
				'vendor/dropify/js/dropify.min.js',
			],
			'css' => [
				'vendor/daterangepicker/daterangepicker.css',
				'vendor/dropify/css/dropify.min.css',
                'vendor/moment/moment.js',
			]
		];

		$this->load->view('layout/index', $data);
	}

	public function issue_tracker($identifier = '') {
		if (!get_permission('tracker_issues', 'is_view')) {
			access_denied();
		}
		$data['department'] = $this->db->where('identifier', $identifier)->get('tracker_departments')->row();

		// Get all issues
		$this->db->select('*');
		$this->db->from('tracker_issues');
		$this->db->where('department', $identifier);
		$this->db->order_by('logged_at', 'DESC');
		$data['all_issues'] = $this->db->get()->result();

		// Group issues by status
		$data['grouped_issues'] = array();
		foreach ($data['all_issues'] as $issue) {
			$status_key = trim($issue->task_status);
			$data['grouped_issues'][$status_key][] = $issue;
		}

		// Get issue counts by status
		$data['issue_counts'] = array();
		$statuses = $this->tracker_model->get_all_statuses();
		$status_keys = array_keys($statuses);

		foreach ($status_keys as $status_key) {
			$this->db->where('task_status', $status_key);
			$data['issue_counts'][$status_key] = $this->db->count_all_results('tracker_issues');
		}

		$staff_query = $this->db->get('staff')->result();

		// ❌ Remove Superadmin (ID 1)
		unset($staff_query[0]);

		$data['staff_lookup'] = [];
		foreach ($staff_query as $s) {
			$data['staff_lookup'][$s->id] = $s;
		}

		$dept_query = $this->db->get('tracker_departments')->result();
		$data['dept_lookup'] = [];
		foreach ($dept_query as $d) {
			$data['dept_lookup'][$d->id] = $d;
		}

		$mile_query = $this->db->get('tracker_milestones')->result();
		$data['mile_lookup'] = [];
		foreach ($mile_query as $d) {
			$data['mile_lookup'][$d->id] = $d;
		}

		$comp_query = $this->db->get('tracker_components')->result();
		$data['comp_lookup'] = [];
		foreach ($comp_query as $d) {
			$data['comp_lookup'][$d->id] = $d;
		}

		$label_query = $this->db->get('task_labels')->result();
		$data['label_lookup'] = [];
		foreach ($label_query as $d) {
			$data['label_lookup'][$d->id] = $d;
		}

		$sop_query = $this->db->get('sop')->result();
		$data['sop_lookup'] = [];
		foreach ($sop_query as $d) {
			$data['sop_lookup'][$d->id] = $d;
		}

		$task_type_query = $this->db->get('task_types')->result();
		$data['task_type_lookup'] = [];
		foreach ($task_type_query as $d) {
			$data['task_type_lookup'][$d->id] = $d;
		}
		// Add status configuration for the modal
		$data['status_config'] = $this->tracker_model->get_all_statuses();
		$data['priority_config'] = $this->tracker_model->get_all_priority_details();

		$data['active_identifier'] = $identifier;
		$data['title'] = translate('Issue Tracker');
		$data['sub_page'] = 'tracker/issues';
		$data['main_menu'] = 'tracker';

		// Load additional JS for the modal
		$data['headerelements'] = [
			'js' => [
				'vendor/moment/moment.min.js',
				'vendor/daterangepicker/daterangepicker.js',
				'vendor/dropify/js/dropify.min.js',
				'vendor/summernote/summernote.css',
			],
			'css' => [
				'vendor/daterangepicker/daterangepicker.css',
				'vendor/dropify/css/dropify.min.css',
                'vendor/moment/moment.js',
				'vendor/summernote/summernote.js',
			]
		];

		$this->load->view('layout/index', $data);
	}

	public function get_sub_tasks($parentTaskId) {
		$this->db->select('id, unique_id, task_title, task_status, priority_level, estimation_time');
		$this->db->where('parent_issue', $parentTaskId);
		$this->db->where('id !=', $parentTaskId);
		$this->db->where('parent_issue !=', 0);
		$this->db->where('parent_issue !=', NULL);
		$this->db->order_by('id', 'DESC'); // ✅ Order by id descending
		$subTasks = $this->db->get('tracker_issues')->result_array();

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($subTasks));
	}

	public function update_task_field() {
    // Check if it's an AJAX request
    if (!$this->input->is_ajax_request()) {
        $this->output
            ->set_status_header(400)
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => 'Bad request']));
        return;
    }

    // Get input data
    $task_id = $this->input->post('task_id');
    $field   = $this->input->post('field');
    $value   = $this->input->post('value');

    // Log for debugging
    log_message('debug', 'Update task field - Task ID: ' . $task_id . ', Field: ' . $field . ', Value: ' . $value);

    // Validate inputs
    if (empty($task_id) || empty($field)) {
        $this->output
            ->set_status_header(400)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => false,
                'message' => 'Missing parameters - Task ID: ' . $task_id . ', Field: ' . $field
            ]));
        return;
    }

    // Allowed fields to update
    $allowed_fields = [
        'task_status', 'priority_level', 'assigned_to', 'coordinator', 'label', 'component',
        'milestone', 'task_title', 'task_description', 'estimated_end_time',
        'estimation_time', 'parent_issue', 'sop_ids', 'task_type', 'category'
    ];
    if (!in_array($field, $allowed_fields)) {
        $this->output
            ->set_status_header(400)
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => 'Invalid field']));
        return;
    }

    // -------------------------
    // Get old value before update
    // -------------------------
    if (is_numeric($task_id)) {
        $old_task = $this->db->select($field)->where('id', $task_id)->get('tracker_issues')->row();
    } else {
        $old_task = $this->db->select($field)->where('unique_id', $task_id)->get('tracker_issues')->row();
    }
    $old_value = $old_task ? $old_task->$field : null;

    // If no change, skip update & comment
    if ((string)$old_value === (string)$value) {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => 'No changes detected',
                'field'   => $field,
                'value'   => $value
            ]));
        return;
    }

    // Prepare update data
    $update_data = [
        $field => $value ?: null
    ];

    // Log the update data
    log_message('debug', 'Update data: ' . json_encode($update_data));

    // Update the database - try both unique_id and id
    if (is_numeric($task_id)) {
        $this->db->where('id', $task_id);
    } else {
        $this->db->where('unique_id', $task_id);
    }
    $result = $this->db->update('tracker_issues', $update_data);

    // Log the query for debugging
    log_message('debug', 'Update query: ' . $this->db->last_query());
    log_message('debug', 'Affected rows: ' . $this->db->affected_rows());

    if ($result) {
        // Add automatic comment for field changes
        $fields_for_comment = [
            'task_status', 'priority_level', 'task_title', 'component', 'milestone',
            'label', 'estimated_end_time', 'estimation_time', 'assigned_to', 'coordinator', 'parent_issue', 'sop_ids', 'task_type', 'category'
        ];
        if (in_array($field, $fields_for_comment)) {
            $this->add_automatic_comment($task_id, $field, $value, $old_value);
        }

        $response = [
            'success'   => true,
            'message'   => 'Updated successfully',
            'field'     => $field,
            'new_value' => $value,
            'old_value' => $old_value
        ];

        // If updating parent_issue, get parent task details
        if ($field === 'parent_issue' && !empty($value)) {
            $parent_task = $this->db->select('unique_id, task_title')
                ->where('id', $value)
                ->get('tracker_issues')
                ->row();
            if ($parent_task) {
                $response['parent_details'] = [
                    'unique_id'  => $parent_task->unique_id,
                    'task_title' => $parent_task->task_title
                ];
            }
        }
    } else {
        $error = $this->db->error();
        $response = [
            'success' => false,
            'message' => 'Database error: ' . $error['message']
        ];
    }

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
}

// -------------------------
// Automatic comment function
// -------------------------
private function add_automatic_comment($task_id, $field, $new_value, $old_value) {
    // Get numeric task ID
    if (is_numeric($task_id)) {
        $task = $this->db->select('id')->where('id', $task_id)->get('tracker_issues')->row();
    } else {
        $task = $this->db->select('id')->where('unique_id', $task_id)->get('tracker_issues')->row();
    }
    if (!$task) return;

    $user_id = get_loggedin_user_id();

    // Get logged-in user name from staff table
    $staff = $this->db->select('name')->where('id', $user_id)->get('staff')->row();
    $user_name = $staff ? $staff->name : 'Unknown User';

    // Resolve foreign keys
    if ($field === 'assigned_to' && !empty($new_value)) {
        $assignee = $this->db->select('name')->where('id', $new_value)->get('staff')->row();
        $new_value = $assignee ? $assignee->name : $new_value;
        if (!empty($old_value)) {
            $old_assignee = $this->db->select('name')->where('id', $old_value)->get('staff')->row();
            $old_value = $old_assignee ? $old_assignee->name : $old_value;
        }
    } elseif ($field === 'coordinator' && !empty($new_value)) {
        $coordinator = $this->db->select('name')->where('id', $new_value)->get('staff')->row();
        $new_value = $coordinator ? $coordinator->name : $new_value;
        if (!empty($old_value)) {
            $old_coordinator = $this->db->select('name')->where('id', $old_value)->get('staff')->row();
            $old_value = $old_coordinator ? $old_coordinator->name : $old_value;
        }
    } elseif ($field === 'label' && !empty($new_value)) {
        $label = $this->db->select('name')->where('id', $new_value)->get('labels')->row();
        $new_value = $label ? $label->name : $new_value;
        if (!empty($old_value)) {
            $old_label = $this->db->select('name')->where('id', $old_value)->get('labels')->row();
            $old_value = $old_label ? $old_label->name : $old_value;
        }
    } elseif ($field === 'component' && !empty($new_value)) {
        $component = $this->db->select('title')->where('id', $new_value)->get('tracker_components')->row();
        $new_value = $component ? $component->title : $new_value;
        if (!empty($old_value)) {
            $old_component = $this->db->select('title')->where('id', $old_value)->get('tracker_components')->row();
            $old_value = $old_component ? $old_component->title : $old_value;
        }
    } elseif ($field === 'milestone' && !empty($new_value)) {
        $milestone = $this->db->select('title')->where('id', $new_value)->get('tracker_milestones')->row();
        $new_value = $milestone ? $milestone->title : $new_value;
        if (!empty($old_value)) {
            $old_milestone = $this->db->select('title')->where('id', $old_value)->get('tracker_milestones')->row();
            $old_value = $old_milestone ? $old_milestone->title : $old_value;
        }
	} elseif ($field === 'sop_ids' && !empty($new_value)) {
        $sop_ids = explode(',', $new_value);
        $sop_titles = [];
        foreach ($sop_ids as $sop_id) {
            $sop_id = trim($sop_id);
            $sop = $this->db->select('title')->where('id', $sop_id)->get('sop')->row();
            if ($sop) {
                $sop_titles[] = $sop->title;
            }
        }
        $new_value = !empty($sop_titles) ? implode(', ', $sop_titles) : $new_value;

        if (!empty($old_value)) {
            $old_sop_ids = explode(',', $old_value);
            $old_sop_titles = [];
            foreach ($old_sop_ids as $old_sop_id) {
                $old_sop_id = trim($old_sop_id);
                $old_sop = $this->db->select('title')->where('id', $old_sop_id)->get('sop')->row();
                if ($old_sop) {
                    $old_sop_titles[] = $old_sop->title;
                }
            }
            $old_value = !empty($old_sop_titles) ? implode(', ', $old_sop_titles) : $old_value;
        }
    } elseif ($field === 'task_type' && !empty($new_value)) {
        $task_type = $this->db->select('name')->where('id', $new_value)->get('task_types')->row();
        $new_value = $task_type ? $task_type->name : $new_value;
        if (!empty($old_value)) {
            $old_task_type = $this->db->select('name')->where('id', $old_value)->get('task_types')->row();
            $old_value = $old_task_type ? $old_task_type->name : $old_value;
        }
    }

    // Only add comment if value actually changed
    if ((string)$old_value === (string)$new_value) return;

    // Readable field names
    $field_labels = [
        'task_status'       => 'status',
        'priority_level'    => 'priority level',
        'assigned_to'       => 'assignee',
        'coordinator'       => 'coordinator',
        'label'             => 'label',
        'component'         => 'initiatives',
        'milestone'         => 'milestone',
        'task_title'        => 'title',
        'task_description'  => 'description',
        'estimated_end_time'=> 'due date',
        'estimation_time'   => 'estimated time',
        'parent_issue'      => 'parent task',
        'sop_ids'           => 'SOP IDs',
        'task_type'         => 'task type',
        'category'          => 'category'
    ];

    $field_name = $field_labels[$field] ?? str_replace('_', ' ', $field);

    $comment_text = sprintf(
        "%s changed the %s from '%s' to '%s'.",
        $user_name,
        $field_name,
        $old_value ?? 'empty',
        $new_value ?? 'empty'
    );

    $this->db->insert('tracker_comments', [
        'task_id'      => $task->id,
        'comment_text' => $comment_text,
        'author_id'    => 1,
        'created_at'   => date('Y-m-d H:i:s')
    ]);
}
	public function get_sop_title($id) {
		$sop = $this->db->select('title')->where('id', $id)->get('sop')->row();
		if ($sop) {
			echo json_encode(['title' => $sop->title]);
		} else {
			echo json_encode(['title' => null]);
		}
	}

	public function get_all_issues($current_task_id = null) {
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		$this->db->select('id, unique_id, task_title');
		$this->db->from('tracker_issues');
		if ($current_task_id) {
			$this->db->where('id !=', $current_task_id);
		}
		$this->db->order_by('unique_id', 'ASC');
		$issues = $this->db->get()->result();

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($issues));
	}



	// Add this method to your Tracker controller

	public function get_task_details($task_id = null)
	{

		// Ensure only AJAX requests
		if (!$this->input->is_ajax_request()) {
			//show_404();
		}

		if (!$task_id) {
			$task_id = $this->input->get('task_id') ?: $this->input->post('task_id');
		}

		if (!$task_id) {
			http_response_code(400);
			echo json_encode(['error' => 'Task ID is required']);
			return;
		}

		// Fetch complete task details from database
		$this->db->select('
			t.*,
			assigned_staff.name as assigned_to_name,
			assigned_staff.photo as assigned_to_photo,
			created_staff.name as created_by_name,
			created_staff.photo as created_by_photo,
			coordinator_staff.name as coordinator_name,
			coordinator_staff.photo as coordinator_photo,
			approved_staff.name as approved_by_name,
			d.title as department_name,
			m.title as milestone_name,
			comp.title as component_name,
			parent_task.id as parent_issue_id,
			parent_task.unique_id as parent_issue_unique_id,
			parent_task.task_title as parent_task_title
		');
		$this->db->from('tracker_issues t');
		$this->db->join('tracker_issues parent_task', 't.parent_issue = parent_task.id', 'left');
		$this->db->join('staff assigned_staff', 't.assigned_to = assigned_staff.id', 'left');
		$this->db->join('staff created_staff', 't.created_by = created_staff.id', 'left');
		$this->db->join('staff coordinator_staff', 't.coordinator = coordinator_staff.id', 'left');
		$this->db->join('staff approved_staff', 't.approved_by = approved_staff.id', 'left');
		$this->db->join('tracker_departments d', 't.department = d.id', 'left');
		$this->db->join('tracker_milestones m', 't.milestone = m.id', 'left');
		$this->db->join('tracker_components comp', 't.component = comp.id', 'left');
		$this->db->where('t.id', $task_id);
		$this->db->or_where('t.unique_id', $task_id);

		$task = $this->db->get()->row();

		if (!$task) {
			http_response_code(404);
			echo json_encode(['error' => 'Task not found']);
			return;
		}

		// Format the response
		$response = [
			'id' => $task->id,
			'unique_id' => $task->unique_id,
			'task_title' => $task->task_title,
			'task_description' => $task->task_description,
			'task_status' => $task->task_status,
			'priority_level' => $task->priority_level,
			'assigned_to' => $task->assigned_to,
			'assigned_to_name' => $task->assigned_to_name,
			'assigned_to_photo' => $task->assigned_to_photo ? get_image_url('staff', $task->assigned_to_photo) : null,
			'created_by_name' => $task->created_by_name,
			'created_by_photo' => $task->created_by_photo ? get_image_url('staff', $task->created_by_photo) : null,
			'coordinator' => $task->coordinator,
			'coordinator_name' => $task->coordinator_name,
			'coordinator_photo' => $task->coordinator_photo ? get_image_url('staff', $task->coordinator_photo) : null,
			'approved_by_name' => $task->approved_by_name ?? null,
			'approved_at' => $task->approved_at ?? null,
			'department_name' => $task->department_name,
			'milestone_name' => $task->milestone_name,
			'component' => $task->component,
			'component_name' => $task->component_name,
			'milestone' => $task->milestone,
			'estimation_time' => $task->estimation_time,
			'estimated_end_time' => $task->estimated_end_time,
			'logged_at' => $task->logged_at,
			'label' => $task->label,
			'spent_time' => $task->spent_time,
			'remaining_time' => $task->remaining_time,
			'parent_issue' => $task->parent_issue,
			'parent_issue_id' => $task->parent_issue_id,
			'parent_issue_unique_id' => $task->parent_issue_unique_id,
			'parent_task_title' => $task->parent_task_title,
			'sop_ids' => $task->sop_ids ?? null,
			'task_type' => $task->task_type ?? null,
			'category' => $task->category ?? null,
			'customer_query_data' => $task->customer_query_data ?? null
		];

		// Get labels
		if (!empty($task->label)) {
			$label_ids = explode(',', $task->label);
			$label_names = [];

			foreach ($label_ids as $label_id) {
				$label_id = trim($label_id);
				$label = $this->db->get_where('task_labels', ['id' => $label_id])->row();
				if ($label) {
					$label_names[] = $label->name;
				}
			}

			$response['labels'] = $label_names;
		}

		// Get SOP IDs
		if (!empty($task->sop_ids)) {
			$sop_ids = explode(',', $task->sop_ids);
			$sop_names = [];

			foreach ($sop_ids as $sop_id) {
				$sop_id = trim($sop_id);
				$sop = $this->db->select('title')->where('id', $sop_id)->get('sop')->row();
				if ($sop) {
					$sop_names[] = $sop->title;
				}
			}

			$response['sop_names'] = $sop_names;
		}

		// Set content type and return JSON
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response));
	}

	// Add to your Tracker controller
	public function get_comments($task_unique_id) {
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		// First get the task ID from unique_id
		$this->db->select('id');
		$this->db->where('unique_id', $task_unique_id);
		$task = $this->db->get('tracker_issues')->row();

		if (!$task) {
			$this->output
				->set_status_header(404)
				->set_output(json_encode(['error' => 'Task not found']));
			return;
		}

		// Then get comments for this task
		$this->db->select('tc.*, s.id as author_id,  s.name as author_name, s.photo as author_photo');
		$this->db->from('tracker_comments tc');
		$this->db->join('staff s', 'tc.author_id = s.id');
		$this->db->where('tc.task_id', $task->id);
		$this->db->order_by('tc.created_at', 'DESC');
		$comments = $this->db->get()->result();

		// Format dates and photos
		foreach ($comments as &$comment) {
			$comment->formatted_date = date('M j, Y g:i A', strtotime($comment->created_at));
			$comment->author_photo = get_image_url('staff', $comment->author_photo);
			 // Set author name as 'System' if author_id is 1
			if ($comment->author_id == 1) {
				$comment->author_name = 'System';
			}
		}



		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($comments));
	}

	public function add_comment() {
    if (!$this->input->is_ajax_request()) {
        show_404();
    }

    $task_unique_id = $this->input->post('task_id');
    $comment_text = $this->input->post('comment_text');

    // Validate inputs
    if (empty($task_unique_id) || empty($comment_text)) {
        $this->output
            ->set_status_header(400)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => false,
                'message' => 'Task ID and comment text are required'
            ]));
        return;
    }

    // Get the actual task ID from unique_id
    $this->db->select('id, task_title');
    $this->db->where('unique_id', $task_unique_id);
    $task = $this->db->get('tracker_issues')->row();

    if (!$task) {
        $this->output
            ->set_status_header(404)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => false,
                'message' => 'Task not found'
            ]));
        return;
    }

    $staff_id = get_loggedin_user_id();

    // Extract mentions from comment text
    $mentioned_users = $this->extract_mentions($comment_text);

    // Prepare comment data
    $comment_data = [
        'task_id' => $task->id,
        'comment_text' => $comment_text,
        'author_id' => $staff_id,
        'created_at' => date('Y-m-d H:i:s')
    ];

    // Insert comment
    $this->db->insert('tracker_comments', $comment_data);
    $comment_id = $this->db->insert_id();

    if (!$comment_id) {
        $error = $this->db->error();
        log_message('error', 'Database error: ' . print_r($error, true));

        $this->output
            ->set_status_header(500)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => false,
                'message' => 'Failed to save comment',
                'error' => $error
            ]));
        return;
    }

    // Create notifications for mentioned users
    if (!empty($mentioned_users)) {
        $this->create_mention_notifications($mentioned_users, $task_unique_id, $task->task_title, $staff_id, $comment_text);
    }

    // Get the full comment data to return
    $this->db->select('tc.*, s.name as author_name, s.photo as author_photo');
    $this->db->from('tracker_comments tc');
    $this->db->join('staff s', 'tc.author_id = s.id');
    $this->db->where('tc.id', $comment_id);
    $comment = $this->db->get()->row();

    // Format the date for display
    $comment->formatted_date = date('M j, Y g:i A', strtotime($comment->created_at));

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'success' => true,
            'comment' => $comment,
            'message' => 'Comment added successfully'
        ]));
}


public function add_comment_ajax() {
    $task_unique_id = $this->input->post('task_id');
    $comment_text = $this->input->post('comment_text');

    // Validate inputs
    if (empty($task_unique_id) || empty($comment_text)) {
        if ($this->input->is_ajax_request()) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Task ID and comment text are required'
                ]));
            return;
        } else {
            set_alert('error', 'Task ID and comment text are required');
            redirect($_SERVER['HTTP_REFERER']);
            return;
        }
    }

    // Get the actual task ID from unique_id
    $this->db->select('id, task_title');
    $this->db->where('unique_id', $task_unique_id);
    $task = $this->db->get('tracker_issues')->row();

    if (!$task) {
        if ($this->input->is_ajax_request()) {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Task not found'
                ]));
            return;
        } else {
            set_alert('error', 'Task not found');
            redirect($_SERVER['HTTP_REFERER']);
            return;
        }
    }

    $staff_id = get_loggedin_user_id();

    // Extract mentions from comment text
    $mentioned_users = $this->extract_mentions($comment_text);

    // Prepare comment data
    $comment_data = [
        'task_id' => $task->id,
        'comment_text' => $comment_text,
        'author_id' => $staff_id,
        'created_at' => date('Y-m-d H:i:s')
    ];

    // Insert comment
    $this->db->insert('tracker_comments', $comment_data);
    $comment_id = $this->db->insert_id();

    if (!$comment_id) {
        $error = $this->db->error();
        log_message('error', 'Database error: ' . print_r($error, true));

        if ($this->input->is_ajax_request()) {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Failed to save comment',
                    'error' => $error
                ]));
            return;
        } else {
            set_alert('error', 'Failed to save comment');
            redirect($_SERVER['HTTP_REFERER']);
            return;
        }
    }

    // Create notifications for mentioned users
    if (!empty($mentioned_users)) {
        $this->create_mention_notifications($mentioned_users, $task_unique_id, $task->task_title, $staff_id, $comment_text);
    }

    // Get the full comment data to return
    $this->db->select('tc.*, s.name as author_name, s.photo as author_photo');
    $this->db->from('tracker_comments tc');
    $this->db->join('staff s', 'tc.author_id = s.id');
    $this->db->where('tc.id', $comment_id);
    $comment = $this->db->get()->row();

    // Format the date for display
    $comment->formatted_date = date('M j, Y g:i A', strtotime($comment->created_at));

    if ($this->input->is_ajax_request()) {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'comment' => $comment,
                'message' => 'Comment added successfully'
            ]));
    } else {
        set_alert('success', 'Comment added successfully');
        redirect($_SERVER['HTTP_REFERER']);
    }
}


public function update_comment() {
    $comment_id = $this->input->post('comment_id');
    $comment_text = $this->input->post('comment_text');
    $staff_id = get_loggedin_user_id();

    if (empty($comment_id) || empty($comment_text)) {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => false,
                'message' => 'Comment ID and text are required'
            ]));
        return;
    }

    // Update comment with ownership check
    $this->db->where('id', $comment_id);
    $this->db->where('author_id', $staff_id);
    $result = $this->db->update('tracker_comments', [
        'comment_text' => $comment_text
    ]);

    if ($result) {
        // Handle mentions in updated comment
        $mentioned_users = $this->extract_mentions($comment_text);
        if (!empty($mentioned_users)) {
            // Get comment and task details
            $comment = $this->db->select('tc.task_id')->from('tracker_comments tc')
                ->where('tc.id', $comment_id)->get()->row();
            if ($comment) {
                $task = $this->db->select('unique_id, task_title')->where('id', $comment->task_id)
                    ->get('tracker_issues')->row();
                if ($task) {
                    $this->create_mention_notifications($mentioned_users, $task->unique_id,
                        $task->task_title, $staff_id, $comment_text);
                }
            }
        }
    }

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'success' => (bool)$result,
            'message' => $result ? 'Comment updated successfully' : 'Failed to update comment or access denied'
        ]));
}

public function delete_comment() {
    if (!$this->input->is_ajax_request()) {
        show_404();
    }

    $comment_id = $this->input->post('comment_id');
    $staff_id = get_loggedin_user_id();

    if (empty($comment_id)) {
        $this->output
            ->set_status_header(400)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => false,
                'message' => 'Comment ID is required'
            ]));
        return;
    }

    // Check if user owns the comment
    $this->db->where(['id' => $comment_id, 'author_id' => $staff_id]);
    $comment = $this->db->get('tracker_comments')->row();

    if (!$comment) {
        $this->output
            ->set_status_header(403)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => false,
                'message' => 'Comment not found or access denied'
            ]));
        return;
    }

    // Delete comment
    $this->db->where('id', $comment_id);
    $result = $this->db->delete('tracker_comments');

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'success' => $result,
            'message' => $result ? 'Comment deleted successfully' : 'Failed to delete comment'
        ]));
}

public function delete_issue() {
    $task_unique_id = $this->input->post('task_id');

    if (empty($task_unique_id)) {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => false,
                'message' => 'Task ID is required'
            ]));
        return;
    }

    // Get issue ID first
    $issue = $this->db->where('unique_id', $task_unique_id)->get('tracker_issues')->row();

    if ($issue) {
        // Delete related planner events
        $this->db->where('issue_id', $issue->id)->delete('planner_events');

        // Delete issue
        $this->db->where('unique_id', $task_unique_id);
        $result = $this->db->delete('tracker_issues');
    } else {
        $result = false;
    }

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'success' => $result,
            'message' => $result ? 'Issue deleted successfully' : 'Failed to delete issue'
        ]));
}


	public function get_all_milestones()
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		$milestones = [];
		$this->db->select('id, title');
		$this->db->from('tracker_milestones');
		$this->db->order_by('title', 'ASC');
		$query = $this->db->get();

		foreach ($query->result() as $row) {
			$milestones[$row->id] = $row->title;
		}

		echo json_encode($milestones);
	}

	public function get_in_progress_milestones()
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		$milestones = [];
		$this->db->select('id, title');
		$this->db->from('tracker_milestones');
		$this->db->where('status', 'in_progress');
		$this->db->order_by('title', 'ASC');
		$query = $this->db->get();

		foreach ($query->result() as $row) {
			$milestones[$row->id] = $row->title;
		}

		echo json_encode($milestones);
	}

	public function get_tasks_by_milestone()
	{
		$milestone_id = $this->input->post('milestone_id');
		$filter = $this->input->post('filter');

		$tasks = [];
		if ($milestone_id) {
			$this->db->select('id, task_title');
			$this->db->from('tracker_issues');
			$this->db->where('milestone', $milestone_id);

			// Apply filter based on parent_issue
			if ($filter === 'main') {
				$this->db->where('parent_issue IS NULL');
			} elseif ($filter === 'sub') {
				$this->db->where('parent_issue IS NOT NULL');
			}
			// For 'all' filter, no additional condition needed

			$query = $this->db->get();

			foreach ($query->result() as $row) {
				$tasks[$row->id] = $row->task_title;
			}
		}

		echo json_encode($tasks);
	}

// Get available users for mentions
public function get_mention_users() {
    if (!$this->input->is_ajax_request()) {
        show_404();
    }

    $search = $this->input->get('search');

	$this->db->select('staff.id, staff.name, staff.photo');
	$this->db->from('staff');
	$this->db->join('login_credential', 'login_credential.user_id = staff.id');
	$this->db->where('staff.id !=', 1);
	$this->db->where('login_credential.active', 1);
	$this->db->where('login_credential.role !=', 9);

	if (!empty($search)) {
		$this->db->like('staff.name', $search);
	}

	$this->db->limit(10);
	$users = $this->db->get()->result();


    // Format user data
    $formatted_users = [];
    foreach ($users as $user) {
        $formatted_users[] = [
            'id' => $user->id,
            'name' => $user->name,
            'photo' => $user->photo ? get_image_url('staff', $user->photo) : null
        ];
    }

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($formatted_users));
}

// Extract mentioned user IDs from comment text
private function extract_mentions($comment_text) {
    preg_match_all('/@\[(\d+)\]/', $comment_text, $matches);
    return array_unique($matches[1]);
}

// Create notifications for mentioned users
private function create_mention_notifications($mentioned_users, $task_unique_id, $task_title, $author_id, $comment_text) {
    // Get author name
    $author = $this->db->select('name')->where('id', $author_id)->get('staff')->row();
    $author_name = $author ? $author->name : 'Someone';

    foreach ($mentioned_users as $user_id) {
        // Don't notify the author
        if ($user_id == $author_id) continue;

        // Get user details including telegram_id
        $user = $this->db->select('name, telegram_id')->where('id', $user_id)->get('staff')->row();

        $notification_data = [
            'user_id' => $user_id,
            'type' => 'mention',
            'title' => 'You were mentioned in a comment',
            'message' => $author_name . ' mentioned you in a comment on task ' . $task_unique_id . ': ' . $task_title,
            'url'        => base_url('tracker/all_issues'),
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('notifications', $notification_data);

        // Send Telegram notification if user has telegram_id
        if ($user && !empty($user->telegram_id)) {
            $this->send_telegram_notification($user->telegram_id, $user->name, $author_name, $task_title, $task_unique_id, $comment_text);
        }
    }
}

// Send Telegram notification
private function send_telegram_notification($chat_id, $staff_name, $author_name, $task_title, $task_unique_id, $comment_text) {
   			$bot_token = $telegram_bot;
    $today = date('d M Y');
	$tg_message = "🛎️ *Task Mention Notification*\n\n" .
    "📅 *Date:* {$today}\n" .
    "👨‍💼 *You were mentioned by:* {$author_name}\n\n" .
    "📌 *Task:* {$task_unique_id} - {$task_title}\n" .
    "💬 *Comment:* {$comment_text}\n" .
    "🔗 [Open Task](" . base_url('tracker/all_issues') . ")";

    $payload = [
        'chat_id' => $chat_id,
        'text' => $tg_message,
        'parse_mode' => 'Markdown',
        'disable_web_page_preview' => true,
    ];

    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_exec($ch);
    curl_close($ch);
}

// Task approval methods
public function pending_approval() {
    if (!get_permission('tracker_issues', 'is_view')) {
        access_denied();
    }

    // Get status configuration for the modal
    $data['status_config'] = $this->tracker_model->get_all_statuses();
    $data['priority_config'] = $this->tracker_model->get_all_priority_details();

    // Add statuses and priorities arrays for the view
    $data['statuses'] = [
        'todo' => translate('to-do'),
        'in_progress' => translate('in_progress'),
        'in_review' => translate('in_review'),
        'submitted' => translate('submitted'),
        'planning' => translate('planning'),
        'observation' => translate('observation'),
        'waiting' => translate('waiting'),
        'completed' => translate('completed'),
        'backlog' => translate('Backlog'),
        'hold' => translate('Hold'),
        'solved' => translate('solved'),
        'canceled' => translate('canceled')
    ];

    $data['priorities'] = [
        'Low' => translate('Low'),
        'Medium' => translate('Medium'),
        'High' => translate('High'),
        'Urgent' => translate('Urgent')
    ];

    // Get staff for dropdowns
    $this->db->select('s.id, s.name');
    $this->db->from('staff AS s');
    $this->db->join('login_credential AS lc', 'lc.user_id = s.id');
    $this->db->where('lc.active', 1);
    $this->db->where_not_in('lc.role', [1, 9, 10, 11, 12]);
    $this->db->order_by('s.name', 'ASC');
    $data['staff_list'] = $this->db->get()->result();

    // Get components, milestones, and task types
    $data['components'] = $this->db->get('tracker_components')->result();
    $data['milestones'] = $this->db->get('tracker_milestones')->result();
    $data['task_types'] = $this->db->get('task_types')->result();

    // Get staff lookup for modal
    $staff_query = $this->db->get('staff')->result();
    $data['staff_lookup'] = [];
    foreach ($staff_query as $s) {
        $data['staff_lookup'][$s->id] = $s;
    }

    // Get label lookup
    $label_query = $this->db->get('task_labels')->result();
    $data['label_lookup'] = [];
    foreach ($label_query as $d) {
        $data['label_lookup'][$d->id] = $d;
    }

    $data['title'] = translate('Pending Approval');
    $data['sub_page'] = 'tracker/pending_approval';
    $data['main_menu'] = 'tracker';

    // Load additional JS for the modal
    $data['headerelements'] = [
        'js' => [
            'vendor/moment/moment.min.js',
            'vendor/daterangepicker/daterangepicker.js',
            'vendor/dropify/js/dropify.min.js',
        ],
        'css' => [
            'vendor/daterangepicker/daterangepicker.css',
            'vendor/dropify/css/dropify.min.css',
            'vendor/moment/moment.js',
        ]
    ];

    $this->load->view('layout/index', $data);
}

public function get_pending_tasks() {
    if (!$this->input->is_ajax_request()) {
        show_404();
    }

    if (!get_permission('tracker_issues', 'is_view')) {
        $this->output
            ->set_status_header(403)
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => 'Access denied']));
        return;
    }

    $staff_id = get_loggedin_user_id();
    $role_id = loggedin_role_id();

    try {
        // Get tasks - for roles 1,2,3,5 show all pending/declined tasks, otherwise only assigned to current user
        $this->db->select('ti.*, s1.name as created_by_name, s1.photo as created_by_photo, s2.name as assigned_to_name, s2.photo as assigned_to_photo');
        $this->db->from('tracker_issues ti');
        $this->db->join('staff s1', 's1.id = ti.created_by', 'left');
        $this->db->join('staff s2', 's2.id = ti.assigned_to', 'left');

        // Role-based access control - roles 1,2,3,5 can view all, role 8 can view their own and department members, others only their tasks
        if (in_array($role_id, [1, 2, 3, 5])) {
            // Admin roles can view all tasks
        } elseif ($role_id == 8) {
            // Role 8 can view their own tasks and department members' tasks
            $this->db->group_start();
            $this->db->where('ti.assigned_to', $staff_id);
            $this->db->or_where('ti.assigned_to IN (SELECT id FROM staff WHERE department = (SELECT department FROM staff WHERE id = ' . $staff_id . '))');

            $this->db->or_where('ti.created_by', $staff_id);
            $this->db->group_end();
        } else {
            // Other roles can only view their own tasks or tasks they created
            $this->db->group_start();
            $this->db->where('ti.assigned_to', $staff_id);
            $this->db->or_where('ti.created_by', $staff_id);
            $this->db->group_end();
        }

        $this->db->where_in('ti.approval_status', ['pending', 'declined']);
        $this->db->order_by('ti.logged_at', 'DESC');

        $tasks = $this->db->get()->result();

        // Add photo URLs
        foreach ($tasks as $task) {
            if ($task->created_by_photo) {
                $task->created_by_photo = get_image_url('staff', $task->created_by_photo);
            }
            if ($task->assigned_to_photo) {
                $task->assigned_to_photo = get_image_url('staff', $task->assigned_to_photo);
            }
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'tasks' => $tasks]));

    } catch (Exception $e) {
        log_message('error', 'Error in get_pending_tasks: ' . $e->getMessage());
        $this->output
            ->set_status_header(500)
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => 'Failed to load pending tasks']));
    }
}

public function delete_task() {
    if (!$this->input->is_ajax_request()) {
        show_404();
    }

    $task_id = $this->input->post('task_id');
    $staff_id = get_loggedin_user_id();

    if (empty($task_id)) {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => 'Task ID is required']));
        return;
    }

    // Only the task creator can delete the task
    $task = $this->db->where(['id' => $task_id, 'created_by' => $staff_id])
                     ->get('tracker_issues')->row();

    if (!$task) {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => 'Only the task creator can delete this task']));
        return;
    }

    // Delete the task
    $result = $this->db->where('id', $task_id)->delete('tracker_issues');

    if ($result) {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'message' => 'Task deleted successfully']));
    } else {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => 'Failed to delete task']));
    }
}

public function approve_task() {
    if (!$this->input->is_ajax_request()) {
        show_404();
    }

    $task_id = $this->input->post('task_id');
    $staff_id = get_loggedin_user_id();

    if (empty($task_id)) {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => 'Task ID is required']));
        return;
    }

    // Only the assigned_to person can approve tasks
    $task = $this->db->where(['id' => $task_id, 'assigned_to' => $staff_id, 'approval_status' => 'pending'])
                     ->get('tracker_issues')->row();

    if (!$task) {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => 'Only the assigned person can approve this task']));
        return;
    }

    // Update task status to approved
    $update_data = [
        'approval_status' => 'approved',
        'approved_at' => date('Y-m-d H:i:s'),
        'approved_by' => $staff_id
    ];

    $this->db->where('id', $task_id);
    $result = $this->db->update('tracker_issues', $update_data);

    if ($result) {
        // Add to staff_task_log
        $task_log_data = [
            'staff_id'    => $task->assigned_to,
            'location'    => 'Tracker',
            'task_title'  => $task->task_title,
            'start_time'  => date('Y-m-d H:i:s'),
            'task_status' => 'In Progress',
            'logged_at'   => date('Y-m-d H:i:s'),
            'tracker_id' => $task_id
        ];

        $this->db->insert('staff_task_log', $task_log_data);

        // Send notification to task creator
        $this->send_approval_notification($task, 'approved');

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'message' => 'Task approved successfully']));
    } else {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => 'Failed to approve task']));
    }
}

public function request_extension() {
    if (!$this->input->is_ajax_request()) {
        show_404();
    }

    $task_id = $this->input->post('task_id');
    $extension_reason = $this->input->post('extension_reason');
    $new_due_date = $this->input->post('new_due_date');
    $new_estimation = $this->input->post('new_estimation');
    $staff_id = get_loggedin_user_id();

    if (empty($task_id)) {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => 'Task ID is required']));
        return;
    }

    if (empty(trim($extension_reason))) {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => 'Please provide a reason for extension request']));
        return;
    }

    // Check if extension columns exist
    if (!$this->db->field_exists('extension_reason', 'tracker_issues')) {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => 'Extension request feature is not available. Please run the database update script.']));
        return;
    }

    // Only the assigned_to person can request extensions
    $this->db->select('ti.*, creator.name as creator_name, creator.telegram_id as creator_telegram, coord.name as coordinator_name, coord.telegram_id as coordinator_telegram');
    $this->db->from('tracker_issues ti');
    $this->db->join('staff creator', 'ti.created_by = creator.id', 'left');
    $this->db->join('staff coord', 'ti.coordinator = coord.id', 'left');
    $this->db->where('ti.id', $task_id);
    $this->db->where('ti.assigned_to', $staff_id);
    $task = $this->db->get()->row();

    if (!$task) {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => 'Only the assigned person can request extension for this task']));
        return;
    }

    // Get requester name
    $requester = $this->db->select('name')->where('id', $staff_id)->get('staff')->row();
    $requester_name = $requester ? $requester->name : 'Unknown';

    // Store extension request in database
    $extension_data = [
        'extension_reason' => $extension_reason,
        'extension_requested_by' => $staff_id,
        'extension_requested_at' => date('Y-m-d H:i:s')
    ];

    if ($new_due_date) {
        $extension_data['extension_new_due_date'] = $new_due_date;
    }

    if ($new_estimation) {
        $extension_data['extension_new_estimation'] = $new_estimation;
    }

    $this->db->where('id', $task_id)->update('tracker_issues', $extension_data);

    // Send Telegram notifications
    $this->send_extension_request_notifications($task, $requester_name, $extension_reason, $new_due_date, $new_estimation);

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode(['success' => true, 'message' => 'Extension request sent successfully']));
}

private function send_extension_request_notifications($task, $requester_name, $reason, $new_due_date = null, $new_estimation = null) {
    $bot_token = $telegram_bot;
    $today = date('d M Y');

    $message = "⏰ *Task Extension Request*\n\n" .
        "📅 *Date:* {$today}\n" .
        "👤 *Requested by:* {$requester_name}\n\n" .
        "📌 *Task:* {$task->unique_id} - {$task->task_title}\n" .
        "💬 *Reason:* {$reason}\n";

    if ($new_due_date) {
        $message .= "📆 *Requested Due Date:* {$new_due_date}\n";
    }

    if ($new_estimation) {
        $message .= "⏱️ *Requested Estimation:* {$new_estimation}h\n";
    }

    $message .= "\n🔗 [View Task](" . base_url('tracker/all_issues') . ")";

    $payload = [
        'text' => $message,
        'parse_mode' => 'Markdown',
        'disable_web_page_preview' => true,
    ];

    // Send to creator
    if (!empty($task->creator_telegram)) {
        $payload['chat_id'] = $task->creator_telegram;
        $this->send_telegram_message($bot_token, $payload);
    }

    // Send to coordinator if exists and different from creator
    if (!empty($task->coordinator_telegram) && $task->coordinator_telegram != $task->creator_telegram) {
        $payload['chat_id'] = $task->coordinator_telegram;
        $this->send_telegram_message($bot_token, $payload);
    }
}

private function send_telegram_message($bot_token, $payload) {
    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_exec($ch);
    curl_close($ch);
}

public function accept_declined_task() {
    if (!$this->input->is_ajax_request()) {
        show_404();
    }

    $task_id = $this->input->post('task_id');
    $staff_id = get_loggedin_user_id();
    $role_id = loggedin_role_id();

    if (empty($task_id)) {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => 'Task ID is required']));
        return;
    }

    // Verify access - either assigned user or roles 1,2,3,5
    $this->db->where('id', $task_id);
    $this->db->where('approval_status', 'declined');
    if (!in_array($role_id, [1, 2, 3, 5])) {
        $this->db->where('assigned_to', $staff_id);
    }
    $task = $this->db->get('tracker_issues')->row();

    if (!$task) {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => 'Task not found or access denied']));
        return;
    }

    // Update task status to approved
    $update_data = [
        'approval_status' => 'approved',
        'approved_at' => date('Y-m-d H:i:s'),
        'approved_by' => $staff_id,
        'decline_reason' => null,
        'declined_at' => null,
        'declined_by' => null
    ];

    $this->db->where('id', $task_id);
    $result = $this->db->update('tracker_issues', $update_data);

    if ($result) {
        // Add to staff_task_log
        $task_log_data = [
            'staff_id'    => $task->assigned_to,
            'location'    => 'Tracker',
            'task_title'  => $task->task_title,
            'start_time'  => date('Y-m-d H:i:s'),
            'task_status' => 'In Progress',
            'logged_at'   => date('Y-m-d H:i:s'),
            'tracker_id' => $task_id
        ];

        $this->db->insert('staff_task_log', $task_log_data);

        // Send notification to task creator
        $this->send_approval_notification($task, 'accepted');

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'message' => 'Task accepted successfully']));
    } else {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => 'Failed to accept task']));
    }
}

private function send_approval_notification($task, $action, $reason = null) {
    // Get task creator details
    $creator = $this->db->select('name, telegram_id')->where('id', $task->created_by)->get('staff')->row();
    $approver = $this->db->select('name')->where('id', get_loggedin_user_id())->get('staff')->row();

    if (!$creator) return;

    $action_text = $action === 'approved' ? 'approved' : ($action === 'accepted' ? 'accepted' : 'declined');
    $message = "Task {$task->unique_id} has been {$action_text} by {$approver->name}";
    if ($reason && $action === 'declined') {
        $message .= ". Reason: {$reason}";
    }

    // Create notification
    $notification_data = [
        'user_id' => $task->created_by,
        'type' => 'task_approval',
        'title' => 'Task ' . ucfirst($action_text),
        'message' => $message,
        'url' => base_url('tracker/all_issues'),
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];

    $this->db->insert('notifications', $notification_data);

    // Send Telegram notification if available
    if (!empty($creator->telegram_id)) {
        $this->send_telegram_approval_notification($creator->telegram_id, $creator->name, $task, $action, $approver->name, $reason);
    }
}

private function send_telegram_approval_notification($chat_id, $creator_name, $task, $action, $approver_name, $reason = null) {
   	$bot_token = $telegram_bot;
	
    $today = date('d M Y');
    $action_emoji = $action === 'approved' ? '✅' : ($action === 'accepted' ? '✅' : '❌');
    $action_text = $action === 'approved' ? 'APPROVED' : ($action === 'accepted' ? 'ACCEPTED' : 'DECLINED');

    $tg_message = "{$action_emoji} *Task {$action_text}*\n\n" .
        "📅 *Date:* {$today}\n" .
        "👨💼 *{$action_text} by:* {$approver_name}\n\n" .
        "📌 *Task:* {$task->unique_id} - {$task->task_title}\n";

    if ($reason && $action === 'declined') {
        $tg_message .= "💬 *Reason:* {$reason}\n";
    }

    $tg_message .= "🔗 [View Tasks](" . base_url('tracker/all_issues') . ")";

    $payload = [
        'chat_id' => $chat_id,
        'text' => $tg_message,
        'parse_mode' => 'Markdown',
        'disable_web_page_preview' => true,
    ];

    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_exec($ch);
    curl_close($ch);
}

private function send_task_assignment_notification($task_id, $assignee_id) {
    // Get task details
    $task = $this->db->select('unique_id, task_title, created_by')->where('id', $task_id)->get('tracker_issues')->row();
    if (!$task) return;

    // Get creator and assignee details
    $creator = $this->db->select('name')->where('id', $task->created_by)->get('staff')->row();
    $assignee = $this->db->select('name, telegram_id')->where('id', $assignee_id)->get('staff')->row();

    if (!$assignee) return;

    $message = "{$assignee->name}, you have been assigned a new task '{$task->task_title}' ({$task->unique_id}) by {$creator->name}. Please review and approve/decline.";

    // Create notification
    $notification_data = [
        'user_id' => $assignee_id,
        'type' => 'task_assignment',
        'title' => 'New Task Assignment',
        'message' => $message,
        'url' => base_url('tracker/pending_approval'),
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];

    $this->db->insert('notifications', $notification_data);

    // Send Telegram notification if available
    if (!empty($assignee->telegram_id)) {
        $this->send_telegram_assignment_notification($assignee->telegram_id, $assignee->name, $task, $creator->name);
    }
}

private function send_telegram_assignment_notification($chat_id, $assignee_name, $task, $creator_name) {
	$bot_token = $telegram_bot;
    $today = date('d M Y');
    $tg_message = "📋 *New Task Assignment*\n\n" .
        "📅 *Date:* {$today}\n" .
        "👨💼 *Assigned by:* {$creator_name}\n\n" .
        "📌 *Task:* {$task->unique_id} - {$task->task_title}\n" .
        "⏳ *Status:* Pending Your Approval\n\n" .
        "Please review and approve/decline this task.\n" .
        "🔗 [Review Task](" . base_url('tracker/pending_approval') . ")";

    $payload = [
        'chat_id' => $chat_id,
        'text' => $tg_message,
        'parse_mode' => 'Markdown',
        'disable_web_page_preview' => true,
    ];

    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_exec($ch);
    curl_close($ch);
}

public function get_coordinator_by_role() {
	if (!$this->input->is_ajax_request()) {
		show_404();
	}

	$assigned_user_id = $this->input->post('assigned_user_id');
	if (empty($assigned_user_id)) {
		echo json_encode(['success' => false, 'message' => 'Assigned user ID is required']);
		return;
	}

	// Get the assigned user's role and department
	$this->db->select('lc.role, s.department');
	$this->db->from('staff s');
	$this->db->join('login_credential lc', 'lc.user_id = s.id');
	$this->db->where('s.id', $assigned_user_id);
	$assigned_user = $this->db->get()->row();

	if (!$assigned_user) {
		echo json_encode(['success' => false, 'message' => 'Assigned user not found']);
		return;
	}

	$coordinator_id = null;

	// Apply coordinator logic based on role
	if ($assigned_user->role == 3) {
		// Role 3 coordinator is role 3 (self)
		$coordinator_id = $assigned_user_id;
	} elseif ($assigned_user->role == 4) {
		// If role 4 is assigned, coordinator should be role 8 from same department, fallback to role 3
		$this->db->select('s.id');
		$this->db->from('staff s');
		$this->db->join('login_credential lc', 'lc.user_id = s.id');
		$this->db->where('lc.role', 8);
		$this->db->where('s.department', $assigned_user->department);
		$this->db->where('lc.active', 1);
		$coordinator = $this->db->get()->row();

		if ($coordinator) {
			$coordinator_id = $coordinator->id;
		} else {
			// Fallback to role 3
			$this->db->select('s.id');
			$this->db->from('staff s');
			$this->db->join('login_credential lc', 'lc.user_id = s.id');
			$this->db->where('lc.role', 3);
			$this->db->where('lc.active', 1);
			$coordinator = $this->db->get()->row();

			if ($coordinator) {
				$coordinator_id = $coordinator->id;
			}
		}
	} elseif (in_array($assigned_user->role, [5, 8])) {
		// If role 5 or 8 is assigned, coordinator should be role 3
		$this->db->select('s.id');
		$this->db->from('staff s');
		$this->db->join('login_credential lc', 'lc.user_id = s.id');
		$this->db->where('lc.role', 3);
		$this->db->where('lc.active', 1);
		$coordinator = $this->db->get()->row();

		if ($coordinator) {
			$coordinator_id = $coordinator->id;
		}
	}

	echo json_encode([
		'success' => true,
		'coordinator_id' => $coordinator_id
	]);
}

}
