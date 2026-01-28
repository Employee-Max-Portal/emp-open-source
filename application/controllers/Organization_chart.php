<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Organization_chart extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('organization_model');
    }


	public function index()
	{
		if (!get_permission('organization_chart', 'is_add')) {
			access_denied();
		}

		if ($this->input->post()) {
			$this->form_validation->set_rules('staff_id', 'Staff', 'required|integer');
			$this->form_validation->set_rules('department_id', 'Department', 'required|integer');
			$this->form_validation->set_rules('branch_id', 'Branch', 'required|integer');
			$this->form_validation->set_rules('position_type', 'Position Type', 'required');

			if ($this->form_validation->run() == true) {
					$parentStaffId = $this->input->post('parent_staff_id');

					$data = [
						'staff_id' => $this->input->post('staff_id'),
						'department_id' => $this->input->post('department_id'),
						'branch_id' => $this->input->post('branch_id'),
						'position_type' => $this->input->post('position_type'),
						'active' => 1
					];

					// Only set parent_staff_id if it's not empty
					if (!empty($parentStaffId)) {
						$data['parent_staff_id'] = $parentStaffId;
					}

				if ($this->db->insert('organization_chart', $data)) {
					set_alert('success', 'Record added successfully');
				} else {
					set_alert('error', 'Failed to add record');
				}

				redirect('organization_chart');
			}
				}


		$this->data['org_chart_list'] = $this->organization_model->get_all();
		$this->data['title'] = translate('organization_chart_list');
		$this->data['sub_page'] = 'organization_chart/index';
		$this->data['main_menu'] = 'organization_chart';
		$this->load->view('layout/index', $this->data);
	}

   public function edit()
	{
		if ($_POST) {
			$id = $this->input->post('id');
			$this->form_validation->set_rules('staff_id', 'Staff', 'required|integer');
			$this->form_validation->set_rules('department_id', 'Department', 'required|integer');
			$this->form_validation->set_rules('position_type', 'Position Type', 'required');
			if ($this->form_validation->run() == true) {
				$data = [
					'staff_id' => $this->input->post('staff_id'),
					'department_id' => $this->input->post('department_id'),
					'branch_id' => $this->input->post('branch_id'),
					'position_type' => $this->input->post('position_type'),
					'parent_staff_id' => $this->input->post('parent_staff_id'),
				];
				$this->organization_model->update($id, $data);
				set_alert('success', 'Record updated successfully');
			} else {
				set_alert('error', validation_errors());
			}
		}
		redirect($_SERVER['HTTP_REFERER']);
	}


    public function delete($id)
    {
        if (!isset($id) || empty($id)) {
            redirect('organization_chart');
        }
        $this->organization_model->delete($id);
        set_alert('success', 'Record deleted successfully');
        redirect('organization_chart');
    }

	public function get_single()
	{
		if ($_POST) {
			$id = $this->input->post('id');
			$row = $this->organization_model->get_single($id);
			echo json_encode($row);
		}
	}

public function chart() {
	if (!get_permission('organization_chart', 'is_view')) {
		access_denied();
	}

	// Get all organization chart data
	$this->db->select('oc.*, s.name as staff_name, s.photo as staff_photo, sd.name as department_name')
    ->from('organization_chart oc')
    ->join('staff s', 's.id = oc.staff_id')
    ->join('login_credential lc', 'lc.user_id = s.id')
    ->join('staff_department sd', 'sd.id = oc.department_id')
    ->where('oc.active', 1)   // only active org chart rows
    ->where('lc.active', 1)   // only active staff
    ->where_not_in('lc.role', [1, 9]); // optional: remove super admin etc.

	$org_data = $this->db->get()->result_array();


	$tree_data = [];
	$departments = [];

	// 1. Find COO
	foreach ($org_data as $item) {
		if ($item['position_type'] === 'COO') {
			$tree_data = [
				'name' => $item['staff_name'],
				'photo' => $item['staff_photo'],
				'designation' => 'COO',
				'children' => []
			];
			break;
		}
	}

	// 2. Group department heads (allow multiple heads)
	foreach ($org_data as $item) {
		if ($item['position_type'] === 'Head') {
			$dept_id = $item['department_id'];
			if (!isset($departments[$dept_id])) {
				$departments[$dept_id] = [
					'name' => $item['staff_name'],
					'photo' => $item['staff_photo'],
					'designation' => 'Head of ' . $item['department_name'],
					'children' => [],
					'department_name' => $item['department_name'] // useful later
				];
			} else {
				$departments[$dept_id]['name'] .= ', ' . $item['staff_name'];
			}
		}
	}

	// 3. Add Incharges under department (group in one node)
	foreach ($org_data as $item) {
		if ($item['position_type'] === 'Incharge') {
			$dept_id = $item['department_id'];
			if (isset($departments[$dept_id])) {
				$incharge_found = false;

				// Check if already an incharge group exists
				foreach ($departments[$dept_id]['children'] as &$child) {
					if (strpos($child['designation'], 'Incharge') !== false) {
						$child['name'] .= ', ' . $item['staff_name'];
						$incharge_found = true;
						break;
					}
				}
				unset($child);

				// If not found, create new incharge group
				if (!$incharge_found) {
					$departments[$dept_id]['children'][] = [
						'name' => $item['staff_name'],
						'photo' => $item['staff_photo'],
						'designation' => 'Incharge - ' . $departments[$dept_id]['department_name'],
						'children' => []
					];
				}
			}
		}
	}

	// 4. Add Employees under respective Incharge (or under head if no incharge)
	$direct_to_coo = [];

foreach ($org_data as $item) {
	if ($item['position_type'] === 'Employee') {
		$dept_id = $item['department_id'];
		$employee = [
			'name' => $item['staff_name'],
			'photo' => $item['staff_photo'],
			'designation' => 'Employee - ' . $item['department_name']
		];

		if (isset($departments[$dept_id])) {
			// If Incharge exists under department
			if (!empty($departments[$dept_id]['children'])) {
				$lastIndex = count($departments[$dept_id]['children']) - 1;
				$departments[$dept_id]['children'][$lastIndex]['children'][] = $employee;
			} else {
				// No incharge, put directly under department
				$departments[$dept_id]['children'][] = $employee;
			}
		} else {
			// No head, no department record — assign directly to COO
			$direct_to_coo[] = $employee;
		}
	}
}


	// 5. Finalize department list under COO
	$tree_data['children'] = array_values($departments);
	$tree_data['children'] = array_merge($tree_data['children'], $direct_to_coo);


	// Pass to view
	$this->data['org_chart'] = [$tree_data];
	$this->data['title'] = translate('organization_chart');
	$this->data['sub_page'] = 'organization_chart/chart';
	$this->data['main_menu'] = 'organization_chart';
	$this->load->view('layout/index', $this->data);
}

}
