<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kpi extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
		$this->load->model('probation_model');
		$this->load->model('employee_model');
		$this->load->model('kpi_model');

    }

    public function index()
{
	if (!get_permission('objectives_kpi', 'is_view')) {
		access_denied();
	}

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		if (!get_permission('objectives_kpi', 'is_add')) {
		}

		// Retrieve submitted POST data
		$data = $this->input->post();

		$query = $this->db->get_where('staff', ['id' => $data['staff_id']]);
		$staff = $query->row();

		$branch_id = $staff->branch_id ?? null;
		$department_id = $staff->department ?? null;

		// Insert into kpi_form table
		$formData = array(
			'branch_id'      => $branch_id,
			'objective_name' => $data['objective_name'],
			'department_id'  => $department_id,
			'staff_id'       => $data['staff_id'],
			'manager_id'     => $data['manager_id'],
			'daterange'      => $data['daterange'],
			'created_at'     => date('Y-m-d H:i:s'), // Optional: add timestamps
		);
		$this->db->insert('kpi_form', $formData);
		$form_id = $this->db->insert_id();

		// Insert subtasks into kpi_form_details
		$subtasks = $data['subtasks'];
		if (!empty($subtasks)) {
			foreach ($subtasks as $subtask) {
				if (!empty($subtask['name']) && !empty($subtask['weight'])) {
					$detailData = array(
						'kpi_form_id' => $form_id,
						'name'        => $subtask['name'],
						'description' => $subtask['description'],
						'weight'      => $subtask['weight'],
					);
					$this->db->insert('kpi_form_details', $detailData);
				}
			}
		}

		// Set success response
		redirect(base_url('kpi/profile/' . $data['staff_id']));
		set_alert('success', translate('information_has_been_saved_successfully'));

	}

	// Initial view load
	$this->data['title'] = translate('Objective Kpi');
	$this->data['sub_page'] = 'ppm/objectives_kpi';
	$this->data['main_menu'] = 'ppm';
	$this->load->view('layout/index', $this->data);
}


public function kpi_form_edit($id)
{
    if (!get_permission('salary_template', 'is_edit')) {
        access_denied();
    }

    // Optional: Branch restriction (if used in your project)
    $this->app_lib->check_branch_restrictions('kpi_form', $id);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $form_id = $this->input->post('form_id');

        // Update main KPI form
        $updateData = [
            'branch_id'      => $this->input->post('branch_id'),
            'objective_name' => $this->input->post('objective_name'),
            'department_id'  => $this->input->post('department_id'),
            'staff_id'       => $this->input->post('staff_id'),
            'manager_id'     => $this->input->post('manager_id'),
            'daterange'      => $this->input->post('daterange'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ];

        $this->db->where('id', $form_id);
        $this->db->update('kpi_form', $updateData);

        // First delete old subtasks (or implement update logic)
        $this->db->where('kpi_form_id', $form_id);
        $this->db->delete('kpi_form_details');

        // Re-insert updated subtasks
        $subtasks = $this->input->post('subtasks');
        if (!empty($subtasks)) {
            foreach ($subtasks as $task) {
                if (!empty($task['name']) && !empty($task['weight'])) {
                    $this->db->insert('kpi_form_details', [
                        'kpi_form_id' => $form_id,
                        'name'        => $task['name'],
                        'description' => $task['description'],
                        'weight'      => $task['weight'],
                    ]);
                }
            }
        }

        set_alert('success', translate('information_has_been_updated_successfully'));
        echo json_encode(['status' => 'success', 'url' => base_url('kpi')]);
        exit;
    }

    // Load data for form
    $this->data['template'] = $this->app_lib->getTable('kpi_form', ['t.id' => $id], true);
    $this->data['template_id'] = $id;
    $this->data['subtasks'] = $this->db->where('kpi_form_id', $id)->get('kpi_form_details')->result_array();
    $this->data['title'] = translate('edit') . ' KPI';
    $this->data['sub_page'] = 'ppm/kpi_form_edit';
    $this->data['main_menu'] = 'ppm';
    $this->load->view('layout/index', $this->data);
}


	// delete KPI form from database
	public function kpi_form_delete($id)
	{
		// Delete all subtasks first
		$this->db->where('kpi_form_id', $id);
		$this->db->delete('kpi_form_details');

		// Then delete the main form entry
		$this->db->where('id', $id);
		$this->db->delete('kpi_form');
	}


public function save_rating()
{
    $form_id = $this->input->post('form_id');
    $staff_id = $this->input->post('staff_id');
    $rating = $this->input->post('rating');
    $role = $this->input->post('role');

    if ($role == 'staff') {
        $this->db->where('id', $form_id)->update('kpi_form', ['staff_rating' => $rating]);
    } else {
        $this->db->where('id', $form_id)->update('kpi_form', ['manager_rating' => $rating]);
    }

    set_alert('success', translate('information_has_been_updated_successfully'));
   	$this->session->set_flashdata('profile_tab', 1);
	 redirect($_SERVER['HTTP_REFERER']);
}

public function save_feedback()
{
    $form_id = $this->input->post('form_id');
    $staff_id = $this->input->post('staff_id');
    $feedback = $this->input->post('content');
    $submitted_by = get_loggedin_user_id(); // assuming you have this helper

    // Validation (optional, but good practice)
    if (empty($feedback)) {
        set_alert('error', 'Feedback content cannot be empty.');
        redirect($_SERVER['HTTP_REFERER']);
        return;
    }

    // Prepare data for insert
    $data = [
        'form_id'       => $form_id,
        'staff_id'      => $staff_id,
        'submitted_by'  => $submitted_by,
        'feedback'      => $feedback,
        'created_at'    => date('Y-m-d H:i:s'),
    ];

    // Insert into kpi_feedback table
    $this->db->insert('kpi_feedback', $data);

    set_alert('success', 'Feedback saved successfully');
    $this->session->set_flashdata('competencies_behaviours', 1);
	 redirect($_SERVER['HTTP_REFERER']);
}

public function delete_feedback($id = null)
{
    if ($id === null) {
        show_error('ID is required', 400);
    }

    $this->db->delete('kpi_feedback', ['id' => $id]);
    echo json_encode(['success' => true]);
}

	// Delete KPI form from database using GET parameter
public function kpi_form_delete_v2($id = null)
{
	if ($id === null || !is_numeric($id)) {
		show_error('Invalid or missing ID', 400);
	}

	// Delete related details first
	$this->db->where('kpi_form_id', $id);
	$this->db->delete('kpi_form_details');

	// Delete related details first
	$this->db->where('form_id', $id);
	$this->db->delete('kpi_feedback');

	// Then delete the main form
	$this->db->where('id', $id);
	$this->db->delete('kpi_form');

	// Optionally redirect or return response
	    echo json_encode(['success' => true]);
}

  /* getting all employee list */
    public function view()
    {
        if (!get_permission('objectives_kpi', 'is_view')) {
            access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        $this->data['act_role'] = $role;
        $this->data['title'] = translate('employee');
        $this->data['sub_page'] = 'ppm/index';
        $this->data['main_menu'] = 'ppm';
        $this->data['stafflist'] = $this->kpi_model->getStaffList($branchID);
        $this->load->view('layout/index', $this->data);
    }

	public function profile($id = '')
{
    if (!get_permission('objectives_kpi', 'is_edit')) {
        access_denied();
    }

    if ($this->input->post('submit') == 'update1') {
        $data = $this->input->post();

        // Save assessment data
        $this->kpi_model->save_assessment($data);

        set_alert('success', translate('information_has_been_updated_successfully'));
        $this->session->set_flashdata('profile_tab', 1);
        redirect(base_url('kpi/profile/' . $id));
    }

	$this->data['kpi_data'] = $this->kpi_model->get_kpi_data_by_user($id); // Add this line
	$this->data['kpi_comp_behav'] = $this->kpi_model->get_kpi_comp_behav_by_user($id);
	$this->data['kpi_approval'] = $this->kpi_model->get_kpiApprovals($id);
	$this->data['kpi_feedback'] = $this->kpi_model->get_kpiFeedbacks($id);

    $this->data['staff'] = $this->kpi_model->getSingleStaff($id);
    $this->data['title'] = translate('objectives_kpi');
    $this->data['sub_page'] = 'ppm/profile';
    $this->data['main_menu'] = 'ppm';
    $this->load->view('layout/index', $this->data);
}

  // employee bank details are create here / ajax
    public function save_approvals()
    {
           $userID = get_loggedin_user_id();

			  $data = array(
                'staff_id' => $this->input->post('staff_id'),
                'created_by' => $userID,  // Make sure staff_id is passed as input
                'phase'    => $this->input->post('phase'),
                'status'   => $this->input->post('status'),
                'remarks'  => $this->input->post('remarks'),
                'created_at' => date('Y-m-d H:i:s'),
            );

            // Insert data into the database
            $this->db->insert('kpi_approval', $data);

            set_alert('success', translate('information_has_been_saved_successfully'));
            $this->session->set_flashdata('approval_hierarchy', 1);
            echo json_encode(array('status' => 'success'));
    }

}
