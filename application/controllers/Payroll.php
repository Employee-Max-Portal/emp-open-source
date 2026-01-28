<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Payroll extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('payroll_model');
        $this->load->model('email_model');
        if (!moduleIsEnabled('human_resource')) {
            access_denied();
        }
    }

    public function index()
    {
        if (!get_permission('salary_payment', 'is_view')) {
            access_denied();
        }
        if (isset($_POST['search'])) {
            $month_year = $this->input->post('month_year');
            $staff_role = $this->input->post('staff_role');
            //$branch_id = $this->application_model->get_branch_id();
            $branch_id = $this->input->post('branch_id');

            $this->data['month'] = date("m", strtotime($month_year));
            $this->data['year'] = date("Y", strtotime($month_year));
            $this->data['stafflist'] = $this->payroll_model->getEmployeePaymentList($branch_id, $staff_role, $this->data['month'], $this->data['year']);
        }

        $this->data['sub_page'] = 'payroll/salary_payment';
        $this->data['main_menu'] = 'payroll';
        $this->data['title'] = translate('payroll');
        $this->load->view('layout/index', $this->data);
    }



    // add staff salary payslip in database
    public function create($id = '', $month = '', $year = '')
    {
        if (!get_permission('salary_payment', 'is_add')) {
            access_denied();
        }

        if (isset($_POST['paid'])) {
            $post = $this->input->post();

            $response = $this->payroll_model->save_payslip($post);
            if ($response['status'] == 'success') {
                // Delete adjustment after payment
                $this->db->where(['staff_id' => $id, 'month' => $month, 'year' => $year]);
                $this->db->delete('salary_adjustments');

                set_alert('success', translate('information_has_been_saved_successfully'));
                redirect($response['uri']);
            } else {
                set_alert('error', "This Month Salary Already Paid !");
                redirect(base_url('payroll'));
            }
        }
        $this->data['month'] = $month;
        $this->data['year'] = $year;
        $this->data['staff'] = $this->payroll_model->getEmployeePayment($id, $this->data['month'], $this->data['year']);

        // Check for saved adjustments
        $adjustment = $this->db->get_where('salary_adjustments', [
            'staff_id' => $id,
            'month' => $month,
            'year' => $year
        ])->row();

        if ($adjustment) {
            $this->data['adjustment'] = $adjustment;
            $this->data['adjustment_allowances'] = json_decode($adjustment->allowances, true);
            $this->data['adjustment_deductions'] = json_decode($adjustment->deductions, true);
            $this->data['adjustment_penalties'] = json_decode($adjustment->penalties, true);
        }

        $this->data['payvia_list'] = $this->app_lib->getSelectList('payment_types');
        $this->data['sub_page'] = 'payroll/create';
        $this->data['main_menu'] = 'payroll';
        $this->data['title'] = translate('payroll');
        $this->load->view('layout/index', $this->data);
    }

    // view staff salary payslip
    public function invoice($id = '', $hash = '')
    {
        if (!get_permission('salary_payment', 'is_view')) {
            access_denied();
        }
        check_hash_restrictions('payslip', $id, $hash);
		$this->data['salary'] = $this->payroll_model->getInvoice($id);
        $this->data['sub_page'] = 'payroll/invoice';
        $this->data['main_menu'] = 'payroll';
        $this->data['title'] = translate('payroll');
        $this->load->view('layout/index', $this->data);
    }

    /* staff template form validation rules */
    protected function template_validation()
    {
        $this->form_validation->set_rules('template_name', translate('salary_grade'), 'required');
        $this->form_validation->set_rules('basic_salary', translate('basic_salary'), 'required|numeric');
    }

    // add staff salary template
    public function salary_template()
    {
        if (!get_permission('salary_template', 'is_view')) {
            access_denied();
        }
        if ($_POST) {
            if (get_permission('salary_template', 'is_add')) {
                // validate inputs
                $this->template_validation();
                if ($this->form_validation->run() == true) {
                    $overtime_rate = (empty($_POST['overtime_rate']) ? 0 : $_POST['overtime_rate']);
					$basic_salary = (float) $this->input->post('basic_salary');

                    // save salary template info
                    $insertData = array(
                        //'branch_id' => $this->application_model->get_branch_id(),
                        'name' => $this->input->post('template_name'),
                        'basic_salary' => $this->input->post('basic_salary'),
                        'overtime_salary' => $overtime_rate,
                       'attendance_penalty' => $basic_salary / 30,
                    );
                    $this->db->insert('salary_template', $insertData);
                    $template_id = $this->db->insert_id();

                    // save all allowance info
                    $allowances = $this->input->post('allowance');
                    foreach ($allowances as $key => $value) {
                        if ($value["name"] != "" && $value["amount"] != "") {
                            $insertAllowance = array(
                                'salary_template_id' => $template_id,
                                'name' => $value["name"],
                                'amount' => $value["amount"],
                                'type' => 1,
                            );
                            $this->db->insert('salary_template_details', $insertAllowance);
                        }
                    }

                    // save all deduction info
                    $deductions = $this->input->post('deduction');
                    foreach ($deductions as $key => $value) {
                        if ($value["name"] != "" && $value["amount"] != "") {
                            $insertDeduction = array(
                                'salary_template_id' => $template_id,
                                'name' => $value["name"],
                                'amount' => $value["amount"],
                                'type' => 2,
                            );
                            $this->db->insert('salary_template_details', $insertDeduction);
                        }
                    }
                    $url = base_url('payroll/salary_template');
                    $array = array('status' => 'success', 'url' => $url, 'error' => '');
                    set_alert('success', translate('information_has_been_saved_successfully'));
                } else {
                    $error = $this->form_validation->error_array();
                    $array = array('status' => 'fail', 'url' => '', 'error' => $error);
                }
                echo json_encode($array);
                exit();
            }
        }
        $this->data['title'] = translate('payroll');
        $this->data['sub_page'] = 'payroll/salary_templete';
        $this->data['main_menu'] = 'payroll';
        $this->load->view('layout/index', $this->data);
    }

    // salary template update by id
    public function salary_template_edit($id)
    {
        if (!get_permission('salary_template', 'is_edit')) {
            access_denied();
        }

        // Check branch restrictions
        $this->app_lib->check_branch_restrictions('salary_template', $id);
        $branchID = $this->application_model->get_branch_id();
        if ($_POST) {
            $this->template_validation();
            if ($this->form_validation->run() == true) {
                $template_id = $this->input->post('salary_template_id');
                $overtime_rate = (empty($_POST['overtime_rate']) ? 0 : $_POST['overtime_rate']);
				$basic_salary = (float) $this->input->post('basic_salary');
                // update salary template info
                $insertData = array(
                    'name' => $this->input->post('template_name'),
                    'basic_salary' => $this->input->post('basic_salary'),
					'attendance_penalty' => $basic_salary / 30,
                    'overtime_salary' => $overtime_rate,
                    //'branch_id' => $branchID,
                );
                $this->db->where('id', $template_id);
                $this->db->update('salary_template', $insertData);

                // update all allowance info
                $allowances = $this->input->post('allowance');
                foreach ($allowances as $key => $value) {
                    if ($value["name"] != "" && $value["amount"] != "") {
                        $insertAllowance = array(
                            'salary_template_id' => $template_id,
                            'name' => $value["name"],
                            'amount' => $value["amount"],
                            'type' => 1,
                        );

                        if (isset($value["old_allowance_id"])) {
                            $this->db->where('id', $value["old_allowance_id"]);
                            $this->db->update('salary_template_details', $insertAllowance);
                        } else {
                            $this->db->insert('salary_template_details', $insertAllowance);
                        }
                    }
                }

                // update all deduction info
                $deductions = $this->input->post('deduction');
                foreach ($deductions as $key => $value) {
                    if ($value["name"] != "" && $value["amount"] != "") {
                        $insertDeduction = array(
                            'salary_template_id' => $template_id,
                            'name' => $value["name"],
                            'amount' => $value["amount"],
                            'type' => 2,
                        );

                        if (isset($value["old_deduction_id"])) {
                            $this->db->where('id', $value["old_deduction_id"]);
                            $this->db->update('salary_template_details', $insertDeduction);
                        } else {
                            $this->db->insert('salary_template_details', $insertDeduction);
                        }
                    }
                }

                $url = base_url('payroll/salary_template');
                $array = array('status' => 'success', 'url' => $url, 'error' => '');
                set_alert('success', translate('information_has_been_updated_successfully'));
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'url' => '', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }

        $this->data['template_id'] = $id;
        $this->data['allowances'] = $this->payroll_model->get('salary_template_details', array('type' => 1, 'salary_template_id' => $id));
        $this->data['deductions'] = $this->payroll_model->get('salary_template_details', array('type' => 2, 'salary_template_id' => $id));
        $this->data['template'] = $this->app_lib->getTable('salary_template', array('t.id' => $id), true);
        $this->data['title'] = translate('payroll');
        $this->data['sub_page'] = 'payroll/salary_templete_edit';
        $this->data['main_menu'] = 'payroll';
        $this->load->view('layout/index', $this->data);
    }

    // delete salary template from database
    public function salary_template_delete($id)
    {
        if (!get_permission('salary_template', 'is_delete')) {
            access_denied();
        }
        // Check student restrictions
        $this->app_lib->check_branch_restrictions('salary_template', $id);
        $this->db->where('salary_template_id', $id);
        $this->db->delete('salary_template_details');
        $this->db->where('id', $id);
        $this->db->delete('salary_template');
    }

    // staff salary allocation
    public function salary_assign()
    {
        if (!get_permission('salary_assign', 'is_view')) {
            access_denied();
        }

        if (isset($_POST['search'])) {
            $staff_role = $this->input->post('staff_role');
            $branchID = $this->input->post('branch_id');
            $this->data['stafflist'] = $this->payroll_model->getEmployeeList($branchID, $staff_role);
        }
        if (isset($_POST['assign'])) {
            if (!get_permission('salary_assign', 'is_add')) {
                access_denied();
            }
            $stafflist = $this->input->post('stafflist');
            if (count($stafflist)) {
                foreach ($stafflist as $key => $value) {
                    $template_id = $value['template_id'];
                    if (empty($template_id)) {
                        $template_id = 0;
                    }

                    $this->db->where('id', $value['id']);
                    $this->db->update('staff', array('salary_template_id' => $template_id));
                }
            }
            set_alert('success', translate('information_has_been_saved_successfully'));
            redirect(base_url('payroll/salary_assign'));
        }

        $this->data['title'] = translate('payroll');
        $this->data['designationlist'] = $this->app_lib->getSelectByBranch('staff_designation', $branchID);
        $this->data['templatelist'] = $this->app_lib->getSelectList('salary_template');
        $this->data['sub_page'] = 'payroll/salary_assign';
        $this->data['main_menu'] = 'payroll';
        $this->load->view('layout/index', $this->data);
    }

    // employees salary statement list
    public function salary_statement()
    {
        if (!get_permission('salary_summary_report', 'is_view')) {
            access_denied();
        }
        if ($_POST) {
            $staffID = '';
            if (!get_permission('salary_payment', 'is_add')) {
                $staffID = get_loggedin_user_id();
            }

        $branchID = $this->application_model->get_branch_id();
            $this->data['month'] = date("m", strtotime($this->input->post('month_year')));
            $this->data['year'] = date("Y", strtotime($this->input->post('month_year')));
            $this->data['payslip'] = $this->payroll_model->get_summary($branchID, $this->data['month'], $this->data['year'], $staffID);
        }
        $this->data['title'] = translate('payroll');
        $this->data['sub_page'] = 'payroll/salary_statement';
        $this->data['main_menu'] = 'payroll_reports';
        $this->load->view('layout/index', $this->data);
    }

    public function payslipPrint()
    {
        if (!get_permission('salary_summary_report', 'is_view')) {
            ajax_access_denied();
        }
        if ($_POST) {
            $this->data['payslip_array'] = $this->input->post('payslip_id');
            echo $this->load->view('payroll/payslipPrint', $this->data, true);
        }
    }

    public function bank_details()
    {
        if (!get_permission('salary_payment', 'is_view')) {
            access_denied();
        }
        if (isset($_POST['search'])) {
            $month_year = $this->input->post('month_year');
            $staff_role = $this->input->post('staff_role');
            $branch_id = $this->application_model->get_branch_id();
            $this->data['month'] = date("m", strtotime($month_year));
            $this->data['year'] = date("Y", strtotime($month_year));
            $this->data['stafflist'] = $this->payroll_model->getEmployeePaymentList($branch_id, $staff_role, $this->data['month'], $this->data['year']);
        }
        $this->data['sub_page'] = 'payroll/bank_details';
        $this->data['main_menu'] = 'payroll';
        $this->data['title'] = translate('payroll');
        $this->load->view('layout/index', $this->data);
    }

	public function salary_increment()
	{
		if (!get_permission('salary_increment', 'is_view')) {
			access_denied();
		}

		$staffID = get_loggedin_user_id();
		if ($this->input->post('search')) {
			$this->data['branch_id'] = $this->application_model->get_branch_id();
			$this->data['staff_role'] = $this->input->post('staff_role');
			$this->data['increments'] = $this->db->select('si.*, s.name')
				->from('salary_increments si')
				->join('staff s', 's.id = si.staff_id', 'left')
				->get()->result();
		} else {
			$this->data['increments'] = $this->db->select('si.*, s.name, s.staff_id')
				->from('salary_increments si')
				->join('staff s', 's.id = si.staff_id', 'left')
				->get()->result();
		}

		$this->data['main_menu'] = 'payroll';
		$this->data['headerelements'] = array(
			'css' => array(
				'vendor/dropify/css/dropify.min.css',
				'vendor/daterangepicker/daterangepicker.css',
				'vendor/summernote/summernote.css',
			),
			'js' => array(
				'vendor/dropify/js/dropify.min.js',
				'vendor/moment/moment.js',
				'vendor/daterangepicker/daterangepicker.js',
				'vendor/summernote/summernote.js',
			),
		);

		$this->data['title'] = translate('salary_increment');
		$this->data['sub_page'] = 'payroll/salary_increment';
		$this->load->view('layout/index', $this->data);
	}

	public function get_salary_increment_edit($id)
	{
		$query = $this->db->select('*')->from('salary_increments')->where('id', $id)->get();
		if ($query->num_rows() > 0) {
			$data = $query->row_array();

			// Decode salary components if exists
			if (!empty($data['salary_components'])) {
				$data['salary_components'] = json_decode($data['salary_components'], true);
			} else {
				$data['salary_components'] = [];
			}

			echo json_encode($data);
		} else {
			echo json_encode(['error' => 'Data not found']);
		}
	}

	public function get_staff_salary_details($staff_id)
	{
		// Check for latest salary increment
		$latest_increment = $this->db->select('new_salary, basic_salary, salary_components')
			->from('salary_increments')
			->where('staff_id', $staff_id)
			->order_by('increment_date', 'DESC')
			->limit(1)
			->get()->row_array();

		if ($latest_increment) {
			// Use incremented salary
			$gross_salary = (float)$latest_increment['new_salary'];
			$basic_salary = (float)$latest_increment['basic_salary'];
			$details = json_decode($latest_increment['salary_components'], true) ?: [];
		} else {
			// Use base salary template
			$this->db->select('s.id as staff_id, s.salary_template_id, t.basic_salary');
			$this->db->from('staff s');
			$this->db->join('salary_template t', 't.id = s.salary_template_id', 'left');
			$this->db->where('s.id', $staff_id);
			$salary = $this->db->get()->row_array();

			$details = $this->db->get_where('salary_template_details', ['salary_template_id' => $salary['salary_template_id']])->result_array();

			$gross_salary = (float)$salary['basic_salary'];
			$basic_salary = (float)$salary['basic_salary'];
			foreach ($details as $row) {
				$gross_salary += (float)$row['amount'];
			}
		}

		$response = [
			'gross_salary' => $gross_salary,
			'basic_salary' => $basic_salary,
			'salary_details' => $details
		];
		echo json_encode($response);
	}

	public function save_salary_increment()
{
	if (!get_permission('salary_increment', 'is_add')) {
		access_denied();
	}

	if ($this->input->post()) {
		$staff_id = $this->input->post('staff_id');
		$increment_percentage = (float)$this->input->post('increment_percentage');
		$increment_date = $this->input->post('increment_date');
		$reason = $this->input->post('reason', true);
		$approved_by = get_loggedin_user_id();
		$edit_id = $this->input->post('id');

		if ($edit_id) {
			// Edit mode: use existing increment data as base
			$existing = $this->db->select('old_salary, basic_salary, salary_components, salary_template_id')
				->from('salary_increments')
				->where('id', $edit_id)
				->get()->row_array();

			$gross_salary = (float)$existing['old_salary'];
			$basic_salary = (float)$existing['basic_salary'];
			$details = json_decode($existing['salary_components'], true) ?: [];
			$template_id = $existing['salary_template_id'];
		} else {
			// Add mode: check for latest increment to use as base
			$latest_increment = $this->db->select('new_salary, basic_salary, salary_components, salary_template_id')
				->from('salary_increments')
				->where('staff_id', $staff_id)
				->order_by('increment_date', 'DESC')
				->limit(1)
				->get()->row_array();

			if ($latest_increment) {
				// Use latest increment as base
				$gross_salary = (float)$latest_increment['new_salary'];
				$basic_salary = (float)$latest_increment['basic_salary'];
				$details = json_decode($latest_increment['salary_components'], true) ?: [];
				$template_id = $latest_increment['salary_template_id'];
			} else {
				// Use base salary template
				$salary_template = $this->db->select('t.basic_salary, t.id as template_id')
					->from('staff s')
					->join('salary_template t', 't.id = s.salary_template_id', 'left')
					->where('s.id', $staff_id)
					->get()->row_array();

				$template_id = $salary_template['template_id'];
				$basic_salary = (float)$salary_template['basic_salary'];
				$details = $this->db->get_where('salary_template_details', ['salary_template_id' => $template_id])->result_array();
				$gross_salary = $basic_salary;
				foreach ($details as $row) {
					$gross_salary += (float)$row['amount'];
				}
			}
		}

		// Get increment amount from form input (more accurate than percentage calculation)
		$increment_amount = (float)$this->input->post('increment_amount');

		// Calculate new salary directly using increment amount
		$new_gross_salary = $gross_salary + $increment_amount;

		// Calculate new basic salary proportionally
		$basic_ratio = $basic_salary / $gross_salary;
		$basic_increment = $increment_amount * $basic_ratio;
		$new_basic_salary = $basic_salary + $basic_increment;

		$new_salary_components = [];
		foreach ($details as $item) {
			$amount = isset($item['amount']) ? (float)$item['amount'] : 0;
			$type = isset($item['type']) ? (int)$item['type'] : 1;
			$name = isset($item['name']) ? $item['name'] : '';

			// Calculate component increment proportionally
			$component_ratio = $amount / $gross_salary;
			$component_increment = $increment_amount * $component_ratio;
			$new_amount = $amount + $component_increment;
			$new_salary_components[] = ['name' => $name, 'amount' => $new_amount, 'type' => $type];
		}

		$data = [
			'staff_id' => $staff_id,
			'old_salary' => $gross_salary,
			'increment_amount' => $increment_amount,
			'increment_percentage' => $increment_percentage,
			'new_salary' => $new_gross_salary,
			'increment_date' => $increment_date,
			'reason' => $reason,
			'salary_template_id' => $template_id,
			'approved_by' => $approved_by,
			'basic_salary' => $new_basic_salary,
			'salary_components' => json_encode($new_salary_components),
		];

		if ($this->input->post('id')) {
			$this->db->where('id', $this->input->post('id'));
			$this->db->update('salary_increments', $data);
			set_alert('success', translate('information_updated_successfully'));
		} else {
			$this->db->insert('salary_increments', $data);
			set_alert('success', translate('information_has_been_saved_successfully'));
		}

		redirect(base_url('payroll/salary_increment'));
	} else {
		redirect(base_url('payroll/salary_increment'));
	}
}

	public function delete_salary_increment($id)
	{
		if (!get_permission('salary_increment', 'is_delete')) {
			access_denied();
		}
		$this->db->where('id', $id);
		$this->db->delete('salary_increments');
	}

	public function get_salary_certificate()
	{
		$staff_id = $this->input->post('staff_id');
		$month = $this->input->post('month');
		$year = $this->input->post('year');

		if (empty($staff_id) || empty($month) || empty($year)) {
			show_error("Invalid request.");
		}

		$this->data['month'] = $month;
		$this->data['year'] = $year;
		$this->data['staff'] = $this->payroll_model->getEmployeePayment($staff_id, $month, $year);

		$this->load->view('payroll/salary_certificate', $this->data);
	}


	 public function salary_sheet()
    {
        if (!get_permission('salary_sheet', 'is_view')) {
            access_denied();
        }
        if (isset($_POST['search'])) {
            $month_year = $this->input->post('month_year');
            $staff_role = $this->input->post('staff_role');
            $branch_id = $this->input->post('branch_id');
            $this->data['month'] = date("m", strtotime($month_year));
            $this->data['year'] = date("Y", strtotime($month_year));
            $this->data['stafflist'] = $this->payroll_model->getEmployeePaymentList($branch_id, $staff_role, $this->data['month'], $this->data['year']);
        }
        $this->data['sub_page'] = 'payroll/salary_sheet';
        $this->data['main_menu'] = 'salary_sheet';
        $this->data['title'] = translate('salary_sheet');
        $this->load->view('layout/index', $this->data);
    }

    public function adjustment_form($id = '', $month = '', $year = '')
    {
        if (!get_permission('salary_payment', 'is_view')) {
            access_denied();
        }

        // Check for existing adjustment for this specific month/year
        $adjustment = $this->db->get_where('salary_adjustments', [
            'staff_id' => $id,
            'month' => $month,
            'year' => $year
        ])->row();

        if ($adjustment) {
            // Use adjustment data
            $this->data['staff'] = $this->payroll_model->getEmployeePayment($id, $month, $year);
            $this->data['adjustment'] = $adjustment;
            $this->data['adjustment_allowances'] = json_decode($adjustment->allowances, true);
            $this->data['adjustment_deductions'] = json_decode($adjustment->deductions, true);
            $this->data['adjustment_penalties'] = json_decode($adjustment->penalties, true);
        } else {
            // Use default staff data
            $this->data['staff'] = $this->payroll_model->getEmployeePayment($id, $month, $year);
        }

        $this->data['month'] = $month;
        $this->data['year'] = $year;
        $this->data['sub_page'] = 'payroll/adjustment_form';
        $this->data['main_menu'] = 'payroll';
        $this->data['title'] = translate('salary_adjustment');
        $this->load->view('layout/index', $this->data);
    }

    public function save_adjustment()
    {
        if (!get_permission('salary_payment', 'is_add')) {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['status' => 'error', 'message' => 'Access denied']);
                exit;
            }
            access_denied();
        }

        // Check for unsatisfied task-related warnings
        $staff_id = $this->input->post('staff_id');
        $has_unsatisfied_warnings = $this->db->select('id')
            ->from('warnings')
            ->where('user_id', $staff_id)
            ->where('task_unique_id IS NOT NULL')
            ->group_start()
                ->where('status !=', 2)
                ->or_where('manager_review', 5)
                ->or_where('advisor_review !=', 2)
            ->group_end()
            ->get()->num_rows() > 0;

        if ($has_unsatisfied_warnings) {
            // Check for penalty work days and served status
            $staff_info = $this->db->select('id, staff_id')->where('id', $staff_id)->get('staff')->row();

            if ($staff_info) {
                $penalty_days = $this->db->select('penalty_date, warning_id')
                    ->from('penalty_days')
                    ->where('staff_id', $staff_info->staff_id)
                    ->get()
                    ->result_array();

                $unserved_penalties = [];
                foreach ($penalty_days as $penalty) {
                    $is_served = $this->db->select('id')
                        ->where('staff_id', $staff_id)
                        ->where('DATE(date)', $penalty['penalty_date'])
                        ->where_in('status', ['P', 'L'])
                        ->get('staff_attendance')
                        ->num_rows() > 0;

                    if (!$is_served) {
                        $unserved_penalties[] = $penalty['penalty_date'];
                    }
                }

                // Only block if there are unserved penalty days
                if (!empty($unserved_penalties)) {
                    $error_msg = 'Cannot adjust salary. Employee has unserved penalty work days on: ' . implode(', ', array_map(function($date) {
                        return date('d M Y', strtotime($date));
                    }, $unserved_penalties)) . '. Employee must serve these penalty days first.';

                    if ($this->input->is_ajax_request()) {
                        echo json_encode(['status' => 'error', 'message' => $error_msg]);
                        exit;
                    }
                    set_alert('error', $error_msg);
                    redirect(base_url('payroll'));
                }
            }
        }

       if ($_POST) {
			$staff_id        = $this->input->post('staff_id');
			$month           = $this->input->post('month');
			$year            = $this->input->post('year');
			$basic_salary    = $this->input->post('basic_salary');
			$allowances      = $this->input->post('allowances');
			$deductions      = $this->input->post('deductions');
			$penalties       = $this->input->post('penalty');
			$total_allowance = $this->input->post('total_allowance');
			$total_deduction = $this->input->post('total_deduction');
			$net_salary      = $this->input->post('net_salary');

			$allowances_json = !empty($allowances) ? $allowances : '[]';
			$deductions_json = !empty($deductions) ? $deductions : '[]';
			$penalties_json  = !empty($penalties) ? json_encode($penalties) : '[]';

			$check = $this->db->get_where('salary_adjustments', [
				'staff_id' => $staff_id,
				'month'    => $month,
				'year'     => $year
			])->row();

			$data = [
				'staff_id'        => $staff_id,
				'month'           => $month,
				'year'            => $year,
				'basic_salary'    => $basic_salary ?: 0,
				'allowances'      => $allowances_json,
				'deductions'      => $deductions_json,
				'penalties'       => $penalties_json,
				'total_allowance' => $total_allowance ?: 0,
				'total_deduction' => $total_deduction ?: 0,
				'net_salary'      => $net_salary ?: 0,
				'adjusted_by'     => get_loggedin_user_id(),
				'adjusted_at'     => date('Y-m-d H:i:s')
			];

			if ($check) {
				$this->db->where('id', $check->id);
				$this->db->update('salary_adjustments', $data);
			} else {
				$this->db->insert('salary_adjustments', $data);
			}

			if ($this->input->is_ajax_request()) {
				echo json_encode(['status' => 'success', 'message' => translate('adjustment_saved_successfully')]);
				exit;
			}

			set_alert('success', translate('adjustment_saved_successfully'));
			redirect(base_url('payroll'));
		}
    }

    public function verify_adjustment($adjustment_id)
    {
        if (!get_permission('salary_payment', 'is_add')) {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['status' => 'error', 'message' => 'Access denied']);
                exit;
            }
            access_denied();
        }

        $data = [
            'status' => 1,
            'verified_by' => get_loggedin_user_id(),
            'verified_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('id', $adjustment_id);
        $this->db->update('salary_adjustments', $data);

        if ($this->input->is_ajax_request()) {
            echo json_encode(['status' => 'success', 'message' => translate('adjustment_verified_successfully')]);
            exit;
        }

        set_alert('success', translate('adjustment_verified_successfully'));
        redirect(base_url('payroll'));
    }

    public function get_adjustment_form()
    {
        if (!get_permission('salary_payment', 'is_view')) {
            ajax_access_denied();
        }

        $staff_id = $this->input->post('staff_id');
        $month = str_pad($this->input->post('month'), 2, '0', STR_PAD_LEFT);
        $year = $this->input->post('year');

        $this->data['staff'] = $this->payroll_model->getEmployeePayment($staff_id, $month, $year);

        $adjustment = $this->db->get_where('salary_adjustments', [
            'staff_id' => $staff_id,
            'month' => $month,
            'year' => $year
        ])->row();

        if ($adjustment) {
            $this->data['adjustment'] = $adjustment;
            $this->data['adjustment_allowances'] = json_decode($adjustment->allowances, true);
            $this->data['adjustment_deductions'] = json_decode($adjustment->deductions, true);
            $this->data['adjustment_penalties'] = json_decode($adjustment->penalties, true);
        }

        $this->data['month'] = $month;
        $this->data['year'] = $year;
        $this->load->view('payroll/adjustment_form_modal', $this->data);
    }

    public function get_table_row()
    {
        if (!get_permission('salary_payment', 'is_view')) {
            ajax_access_denied();
        }

        $staff_id = $this->input->post('staff_id');
        $month = str_pad($this->input->post('month'), 2, '0', STR_PAD_LEFT);
        $year = $this->input->post('year');

        $branch_id = $this->input->post('branch_id') ?: $this->application_model->get_branch_id();
        $staff_role = 'all';

        $stafflist = $this->payroll_model->getEmployeePaymentList($branch_id, $staff_role, $month, $year);

        $currency_symbol = isset($this->data['global_config']['currency_symbol']) ? $this->data['global_config']['currency_symbol'] : '৳';
        $Role_id = loggedin_role_id();

        foreach ($stafflist as $row) {
            if ($row->id == $staff_id) {
                $adjustment = $this->db->get_where('salary_adjustments', [
                    'staff_id' => $row->id,
                    'month' => $month,
                    'year' => $year
                ])->row();

                $advance = (float)($row->advance_amount ?? 0);
                if ($adjustment) {
                    $basic = (float)$adjustment->basic_salary;
                    $total_allowance = (float)$adjustment->total_allowance;
                    $gross_salary = $basic + $total_allowance;
                    $total_deduction = (float)$adjustment->total_deduction;
                    $net_salary = (float)$adjustment->net_salary;
                    $has_adjustment = true;
                    $adjustment_status = isset($adjustment->status) ? (int)$adjustment->status : 0;
                    $adjustment_id = $adjustment->id;
                } else {
                    $basic = (float)$row->basic_salary;
                    $components = isset($row->increment_components) ? $row->increment_components : $row->template_components;

                    $total_allowance = 0;
                    foreach ($components as $comp) {
                        if ((int)$comp['type'] === 1) {
                            $total_allowance += (float)$comp['amount'];
                        }
                    }
                    $gross_salary = $basic + $total_allowance;

                    $late_count = (int)($row->late_count ?? 0);
                    $late_penalty = ($late_count >= 3) ? floor($late_count / 3) * ($basic / 30) : 0;
                    $absent_count = (int)($row->absent_count ?? 0);
                    $absent_penalty = round($absent_count * ($basic / 30));

                    $total_deduction = $late_penalty + $absent_penalty;
                    $net_salary = $gross_salary - $total_deduction - $advance;
                    $has_adjustment = false;
                    $adjustment_status = 0;
                    $adjustment_id = 0;
                }

                $bank = $row->bank_account ?? [];
                $status = ($row->salary_id == 0 ? 'unpaid' : 'paid');
                $labelMode = $status == 'paid' ? 'label-success-custom' : 'label-info-custom';
                $status_txt = $status == 'paid' ? translate('salary') . " " . translate('paid') : translate('salary') . " " . translate('unpaid');

                $this->data['row'] = $row;
                $this->data['basic'] = $basic;
                $this->data['total_allowance'] = $total_allowance;
                $this->data['gross_salary'] = $gross_salary;
                $this->data['total_deduction'] = $total_deduction;
                $this->data['advance'] = $advance;
                $this->data['net_salary'] = $net_salary;
                $this->data['bank'] = $bank;
                $this->data['status'] = $status;
                $this->data['labelMode'] = $labelMode;
                $this->data['status_txt'] = $status_txt;
                $this->data['has_adjustment'] = $has_adjustment;
                $this->data['adjustment_status'] = $adjustment_status;
                $this->data['adjustment_id'] = $adjustment_id;
                $this->data['month'] = $month;
                $this->data['year'] = $year;
                $this->data['currency_symbol'] = $currency_symbol;
                $this->data['Role_id'] = $Role_id;

                $this->load->view('payroll/table_row', $this->data);
                break;
            }
        }
    }

    public function get_payment_form()
    {
        if (!get_permission('salary_payment', 'is_view')) {
            ajax_access_denied();
        }

        $staff_id = $this->input->post('staff_id');
        $month = str_pad($this->input->post('month'), 2, '0', STR_PAD_LEFT);
        $year = $this->input->post('year');

        $this->data['staff'] = $this->payroll_model->getEmployeePayment($staff_id, $month, $year);

        $adjustment = $this->db->get_where('salary_adjustments', [
            'staff_id' => $staff_id,
            'month' => $month,
            'year' => $year
        ])->row();

        if ($adjustment) {
            $this->data['adjustment'] = $adjustment;
            $this->data['adjustment_allowances'] = json_decode($adjustment->allowances, true);
            $this->data['adjustment_deductions'] = json_decode($adjustment->deductions, true);
            $this->data['adjustment_penalties'] = json_decode($adjustment->penalties, true);
        }

        $this->data['month'] = $month;
        $this->data['year'] = $year;
        $this->data['payvia_list'] = $this->app_lib->getSelectList('payment_types');
        $this->load->view('payroll/payment_form_modal', $this->data);
    }

    public function process_payment()
    {
        if (!get_permission('salary_payment', 'is_add')) {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['status' => 'error', 'message' => 'Access denied']);
                exit;
            }
            access_denied();
        }

        if (isset($_POST['paid'])) {
            $post = $this->input->post();
            $staff_id = $post['staff_id'];
            $month = $post['month'];
            $year = $post['year'];

            $response = $this->payroll_model->save_payslip($post);
            if ($response['status'] == 'success') {
                // Delete adjustment after payment
                $this->db->where(['staff_id' => $staff_id, 'month' => $month, 'year' => $year]);
                $this->db->delete('salary_adjustments');

                echo json_encode(['status' => 'success', 'message' => translate('information_has_been_saved_successfully')]);
                exit;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'This Month Salary Already Paid !']);
                exit;
            }
        }

        echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
        exit;
    }

}
