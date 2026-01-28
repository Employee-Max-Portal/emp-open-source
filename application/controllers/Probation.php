<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Probation extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('probation_model');

    }

    public function index()
    {
        if (!get_permission('probation', 'is_view')) {
            access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        $this->data['act_role'] = $role;
        $this->data['title'] = translate('probation');
        $this->data['sub_page'] = 'probation/index';
        $this->data['main_menu'] = 'probation';
        $this->data['stafflist'] = $this->probation_model->getStaffList($branchID);
        $this->load->view('layout/index', $this->data);
    }

	public function profile($id = '')
{
    if (!get_permission('probation', 'is_edit')) {
        access_denied();
    }

    if ($this->input->post('submit') == 'update') {
        $data = $this->input->post();

        // Save assessment data
        $this->probation_model->save_assessment($data);

        set_alert('success', translate('information_has_been_updated_successfully'));
        $this->session->set_flashdata('profile_tab', 1);
        redirect(base_url('probation/profile/' . $id));
    }

    $this->data['staff'] = $this->probation_model->getSingleStaff($id);
    $this->data['title'] = translate('employee_probation');
    $this->data['sub_page'] = 'probation/profile';
    $this->data['main_menu'] = 'probation';
    $this->load->view('layout/index', $this->data);
}

public function acknowledgement()
{
    if (!get_permission('probation', 'is_view')) {
        access_denied();
    }

    $branchID = $this->application_model->get_branch_id();
    $staffList = $this->probation_model->getStaffList($branchID);

		// Attach probation assessment to each staff
	   foreach ($staffList as &$staff) {
		$staff = (array) $staff; // Convert object to array
		$staff['assessment'] = [];
		$rows = $this->db->get_where('employee_probation_assessment', ['staff_id' => $staff['id']])->result_array();
		foreach ($rows as $r) {
			$staff['assessment'][$r['month']] = $r;
		}
	}

    $this->data['acknowledgements'] = $staffList;
    $this->data['title'] = 'Probation Acknowledgements';
    $this->data['sub_page'] = 'probation/acknowledgement';
    $this->data['main_menu'] = 'probation';
    $this->load->view('layout/index', $this->data);
}

public function export_acknowledgement()
{
    $staffList = $this->probation_model->getStaffList();

    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=probation_acknowledgement.csv");
    $output = fopen("php://output", "w");
    fputcsv($output, ['Staff Number', 'Staff Name', 'Joining Date', 'Probation Ends On', 'Evaluation Month(s)', 'Status']);

    foreach ($staffList as $staff) {
        $assessment = $this->db->get_where('employee_probation_assessment', ['staff_id' => $staff->id])->result_array();
        $completed = 0;
        foreach ($assessment as $a) {
            if (!empty($a['meeting_done']) && !empty($a['meeting_date'])) {
                $completed++;
            }
        }

        $status = $completed > 0 ? 'Evaluation Completed' : 'Pending';

        fputcsv($output, [
            $staff->staff_id,
            $staff->name,
            $staff->joining_date,
            date('d M Y', strtotime('+6 months', strtotime($staff->joining_date))),
            $completed,
            $status
        ]);
    }

    fclose($output);
    exit;
}


}
