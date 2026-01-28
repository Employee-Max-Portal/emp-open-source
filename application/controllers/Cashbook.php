<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cashbook extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('cashbook_model');
    }

    public function index()
    {
        if (!get_permission('cashbook_manage', 'is_view')) {
            access_denied();
        }

        $start_date = '';
        $end_date = '';
        $account_type = '';

        if (isset($_POST['search'])) {
            $daterange = explode(' - ', $this->input->post('daterange'));
            $start_date = date("Y-m-d", strtotime($daterange[0]));
            $end_date = date("Y-m-d", strtotime($daterange[1]));
            $account_type = $this->input->post('account_type');
        }

        try {
            $this->data['cashbook_data'] = $this->cashbook_model->getCashbookData($start_date, $end_date, $account_type);
            $this->data['current_balance'] = $this->cashbook_model->getCurrentBalance(get_loggedin_branch_id(), $start_date, $end_date, $account_type);
            
            if ($this->db->conn_id) {
                $this->data['account_types'] = $this->db->get('cashbook_accounts')->result_array();
            } else {
                $this->data['account_types'] = [];
            }

            // Get unsynced fund requisitions count
            if ($this->db->conn_id) {
                $unsynced_count = $this->db->select('COUNT(*) as count')
                    ->from('fund_requisition fr')
                    ->where('fr.payment_status', 2)
                    ->where('fr.id NOT IN (SELECT reference_id FROM cashbook_entries WHERE reference_type = "fund_requisition")', null, false)
                    ->get()->row()->count;
                $this->data['unsynced_funds'] = $unsynced_count;
            } else {
                $this->data['unsynced_funds'] = 0;
            }
        } catch (Exception $e) {
            log_message('error', 'Database error in cashbook index: ' . $e->getMessage());
            $this->data['cashbook_data'] = [];
            $this->data['current_balance'] = array(
                'cash_balance' => 0,
                'bank_asia_balance' => 0,
                'premier_bank_balance' => 0,
                'total_balance' => 0
            );
            $this->data['account_types'] = [];
            $this->data['unsynced_funds'] = 0;
        }
        $this->data['title'] = translate('cashbook');
        $this->data['sub_page'] = 'cashbook/index';
        $this->data['main_menu'] = 'cashbook';
        $this->data['headerelements'] = array(
            'css' => array('vendor/daterangepicker/daterangepicker.css'),
            'js' => array(
                'vendor/moment/moment.js',
                'vendor/daterangepicker/daterangepicker.js',
            ),
        );
        $this->load->view('layout/index', $this->data);
    }

    public function transactions_details()
    {
        if (get_permission('cashbook_manage', 'is_edit')) {
            try {
                if (!$this->db->conn_id) {
                    echo json_encode([]);
                    return;
                }
                
                $id = $this->input->post('id');
                $this->db->where('id', $id);
                $query = $this->db->get('cashbook_entries');
                $result = $query->row_array();
                echo json_encode($result ?: []);
            } catch (Exception $e) {
                log_message('error', 'Database error in transactions_details: ' . $e->getMessage());
                echo json_encode([]);
            }
        }
    }

	 public function transaction_edit()
    {
        if (!get_permission('cashbook_manage', 'is_edit')) {
            ajax_access_denied();
        }

        if ($_POST) {
            try {
                if (!$this->db->conn_id) {
                    $array = array('status' => 'fail', 'error' => 'Database connection failed');
                    echo json_encode($array);
                    return;
                }
                
                $this->form_validation->set_rules('entry_date', translate('date'), 'required');
                $this->form_validation->set_rules('entry_type', translate('entry_type'), 'required');
                $this->form_validation->set_rules('account_type_id', translate('account_type'), 'required');
                $this->form_validation->set_rules('amount', translate('amount'), 'required|numeric');
                $this->form_validation->set_rules('description', translate('description'), 'required');

                if ($this->form_validation->run() !== false) {
                    $entry_date = $this->input->post('entry_date');
                    $entry_datetime = date('Y-m-d H:i:s', strtotime($entry_date));

                    $account_id = $this->input->post('account_type_id');
                    $account = $this->db->get_where('cashbook_accounts', ['id' => $account_id])->row();
                    $account_name = $account ? $account->name : 'cash';

                    $arrayUpdate = array(
                        'entry_date' => $entry_datetime,
                        'entry_type' => $this->input->post('entry_type'),
                        'account_type_id' => $account_id,
                        'account_type' => $account_name,
                        'amount' => $this->input->post('amount'),
                        'description' => $this->input->post('description')
                    );

                    $entry_id = $this->input->post('entry_id');

                    $this->db->where('id', $entry_id);
                    $this->db->update('cashbook_entries', $arrayUpdate);

                    if ($this->db->affected_rows() > 0) {
                        $this->log_cashbook_action('EDIT', $entry_id, $arrayUpdate, get_loggedin_user_id());
                        set_alert('success', translate('information_has_been_updated_successfully'));
                        $array = array('status' => 'success', 'url' => base_url('cashbook'));
                    } else {
						set_alert('success', translate('information_has_been_updated_successfully'));
                        $array = array('status' => 'success', 'url' => base_url('cashbook'));
                    }
                } else {
                    $error = $this->form_validation->error_array();
                    $array = array('status' => 'fail', 'error' => $error);
                }
            } catch (Exception $e) {
                log_message('error', 'Database error in transaction_edit: ' . $e->getMessage());
                $array = array('status' => 'fail', 'error' => 'Database error occurred');
            }
        } else {
            $array = array('status' => 'fail', 'error' => 'No data received');
        }

        echo json_encode($array);
    }

	public function expense()
    {
        if (!get_permission('cashbook_manage', 'is_view')) {
            access_denied();
        }

        $data['title'] = translate('expense_management');
        $data['sub_page'] = 'cashbook/expense';
        $data['main_menu'] = 'cashbook';

        // Get filter parameters from daterange
        $branch_id = $this->input->post('branch_id');
        $date_from = null;
        $date_to = null;
        $type = $this->input->post('type');

        if (isset($_POST['search'])) {
            $daterange = explode(' - ', $this->input->post('daterange'));
            $date_from = date('Y-m-d', strtotime($daterange[0]));
            $date_to = date('Y-m-d', strtotime($daterange[1]));
        }

        try {
            // Get expense data - loads all fund requisitions, advance salary, and salary payments by default
            $data['expenses'] = $this->cashbook_model->get_all_expenses($branch_id, $date_from, $date_to, $type);
            $data['total_amount'] = $this->cashbook_model->get_total_expenses($branch_id, $date_from, $date_to, $type);

            // Get separate totals for each expense type based on current filter
            if (!$type || $type == 'fund_requisition') {
                $data['fund_requisition_total'] = $this->cashbook_model->get_total_expenses($branch_id, $date_from, $date_to, 'fund_requisition');
            } else {
                $data['fund_requisition_total'] = 0;
            }

            if (!$type || $type == 'advance_salary') {
                $data['advance_salary_total'] = $this->cashbook_model->get_total_expenses($branch_id, $date_from, $date_to, 'advance_salary');
            } else {
                $data['advance_salary_total'] = 0;
            }

            if (!$type || $type == 'salary_payment') {
                $data['salary_payment_total'] = $this->cashbook_model->get_total_expenses($branch_id, $date_from, $date_to, 'salary_payment');
            } else {
                $data['salary_payment_total'] = 0;
            }

            if ($this->db->conn_id) {
                $data['branches'] = $this->db->get('branch')->result();
            } else {
                $data['branches'] = [];
            }
        } catch (Exception $e) {
            log_message('error', 'Database error in expense: ' . $e->getMessage());
            $data['expenses'] = [];
            $data['total_amount'] = 0;
            $data['fund_requisition_total'] = 0;
            $data['advance_salary_total'] = 0;
            $data['salary_payment_total'] = 0;
            $data['branches'] = [];
        }

        $data['headerelements'] = array(
            'css' => array('vendor/daterangepicker/daterangepicker.css'),
            'js' => array(
                'vendor/moment/moment.js',
                'vendor/daterangepicker/daterangepicker.js',
            ),
        );

        $this->load->view('layout/index', $data);
    }

	public function income()
    {
        if (!get_permission('cashbook_manage', 'is_view')) {
            access_denied();
        }

        $data['title'] = translate('sales_revenue_ERP');
        $data['sub_page'] = 'cashbook/income';
        $data['main_menu'] = 'cashbook';

        // Get filter parameters from daterange
        $date_from = null;
        $date_to = null;

        if (isset($_POST['search'])) {
            $daterange = explode(' - ', $this->input->post('daterange'));
            $date_from = date('Y-m-d', strtotime($daterange[0]));
            $date_to = date('Y-m-d', strtotime($daterange[1]));
        }

        try {
            // Get income data from sales
            $data['incomes'] = $this->cashbook_model->get_all_incomes($date_from, $date_to, null);
            $data['total_amount'] = $this->cashbook_model->get_total_incomes($date_from, $date_to, null);

            // Get cash and bank totals
            $data['cash_total'] = $this->cashbook_model->get_income_by_payment_method('cash', $date_from, $date_to);
            $data['bank_total'] = $this->cashbook_model->get_income_by_payment_method('bank', $date_from, $date_to);

            // Calculate total dues
            $data['total_dues'] = $this->cashbook_model->get_total_dues($date_from, $date_to);
        } catch (Exception $e) {
            log_message('error', 'Database error in income: ' . $e->getMessage());
            $data['incomes'] = [];
            $data['total_amount'] = 0;
            $data['cash_total'] = 0;
            $data['bank_total'] = 0;
            $data['total_dues'] = 0;
        }
		$data['headerelements'] = array(
			'css' => array(
				'vendor/daterangepicker/daterangepicker.css',
			),
			'js' => array(
				'vendor/moment/moment.js',
				'vendor/daterangepicker/daterangepicker.js',
			),
		);
        $this->load->view('layout/index', $data);
    }

    public function dues()
    {
        if (!get_permission('cashbook_manage', 'is_view')) {
            access_denied();
        }

        $data['title'] = translate('due_report');
        $data['sub_page'] = 'cashbook/dues';
        $data['main_menu'] = 'cashbook';

        $date_from = null;
        $date_to = null;

        if (isset($_POST['search'])) {
            $daterange = explode(' - ', $this->input->post('daterange'));
            $date_from = date('Y-m-d', strtotime($daterange[0]));
            $date_to = date('Y-m-d', strtotime($daterange[1]));
        }

        $data['dues'] = $this->cashbook_model->get_all_dues($date_from, $date_to);
        $data['total_due_amount'] = $this->cashbook_model->get_total_dues($date_from, $date_to);
        $data['cash_dues'] = $this->cashbook_model->get_dues_by_payment_method('cash', $date_from, $date_to);
        $data['bank_dues'] = $this->cashbook_model->get_dues_by_payment_method('bank', $date_from, $date_to);

        $data['headerelements'] = array(
            'css' => array('vendor/daterangepicker/daterangepicker.css'),
            'js' => array(
                'vendor/moment/moment.js',
                'vendor/daterangepicker/daterangepicker.js',
            ),
        );

        $this->load->view('layout/index', $data);
    }

	/* department form validation rules */
    protected function accounts_validation()
    {
        $this->form_validation->set_rules('name', translate('account_name'), 'trim|required');
    }

    public function accounts()
    {
        if ($_POST) {
            if (!get_permission('cashbook_accounts', 'is_add')) {
                access_denied();
            }
            $this->accounts_validation();
            if ($this->form_validation->run() !== false) {
                $arrayAccounts = array(
                    'name' => $this->input->post('name'),
                );
                $this->db->insert('cashbook_accounts', $arrayAccounts);
                set_alert('success', translate('information_has_been_saved_successfully'));
                redirect(base_url('cashbook/accounts'));
            }
        }
        $this->data['accounts'] = $this->app_lib->get_table('cashbook_accounts');
        $this->data['title'] = translate('accounts type');
        $this->data['sub_page'] = 'cashbook/accounts';
        $this->data['main_menu'] = 'cashbook';
        $this->load->view('layout/index', $this->data);
    }

    public function account_edit()
    {
        if (!get_permission('cashbook_accounts', 'is_edit')) {
            ajax_access_denied();
        }
        $this->accounts_validation();
        if ($this->form_validation->run() !== false) {
            $arrayAccounts = array(
                'name' => $this->input->post('name'),
            );
            $account_id = $this->input->post('account_id');
            $this->db->where('id', $account_id);
            $this->db->update('cashbook_accounts', $arrayAccounts);
            set_alert('success', translate('information_has_been_updated_successfully'));
            $array  = array('status' => 'success');
        } else {
            $error = $this->form_validation->error_array();
            $array = array('status' => 'fail','error' => $error);
        }
        echo json_encode($array);
    }

    public function account_delete($id)
    {
        if (!get_permission('cashbook_accounts', 'is_delete')) {
            access_denied();
        }
        $this->db->where('id', $id);
        $this->db->delete('cashbook_accounts');
    }


    public function report()
    {
        if (!get_permission('cashbook_manage', 'is_view')) {
            access_denied();
        }

        $this->data['title'] = translate('profit_and_loss_report');
        $this->data['sub_page'] = 'cashbook/report';
        $this->data['main_menu'] = 'cashbook';

        $month = date('m');
        $year = date('Y');

        if (isset($_POST['search'])) {
            $month_year = $this->input->post('month_year');
            if (!empty($month_year)) {
                $month = date("m", strtotime($month_year));
                $year = date("Y", strtotime($month_year));
            }
        }

        $date_from = $year . '-' . $month . '-01';
        $date_to = date('Y-m-t', strtotime($date_from));

        $this->data['month_name'] = date("F Y", strtotime($date_from));
        $this->data['selected_month'] = $year . '-' . $month;

        // Get Data
        $report_data = $this->cashbook_model->get_report_entries($date_from, $date_to);
        $this->data['incomes'] = $report_data['incomes'];
        $this->data['expenses'] = $report_data['expenses'];

        $this->data['headerelements'] = array(
            'css' => array('vendor/bootstrap-datepicker/css/bootstrap-datepicker.min.css'),
            'js' => array(
                'vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js',
            ),
        );

        $this->load->view('layout/index', $this->data);
    }

    public function get_detailed_report()
    {
        if (!get_permission('cashbook_manage', 'is_view')) {
            ajax_access_denied();
        }

        $month_year = $this->input->post('month_year');
        $month = date("m", strtotime($month_year));
        $year = date("Y", strtotime($month_year));

        $date_from = $year . '-' . $month . '-01';
        $date_to = date('Y-m-t', strtotime($date_from));

        // Get detailed data (individual entries instead of grouped)
        $detailed_data = $this->cashbook_model->get_detailed_entries($date_from, $date_to);

        echo json_encode($detailed_data);
    }


    public function get_expense_category_details()
    {
        if (!get_permission('cashbook_manage', 'is_view')) {
            ajax_access_denied();
        }

        $reference_type = $this->input->post('reference_type');
        $month_year = $this->input->post('month_year');
        $month = date("m", strtotime($month_year));
        $year = date("Y", strtotime($month_year));

        $date_from = $year . '-' . $month . '-01';
        $date_to = date('Y-m-t', strtotime($date_from));

        $category_details = $this->cashbook_model->get_expense_details_by_type($reference_type, $date_from, $date_to);

        echo json_encode(['expenses' => $category_details]);
    }


    public function get_fund_category_details()
    {
        if (!get_permission('cashbook_manage', 'is_view')) {
            ajax_access_denied();
        }

        $category_id = $this->input->post('category_id');
        $month_year = $this->input->post('month_year');
        $month = date("m", strtotime($month_year));
        $year = date("Y", strtotime($month_year));

        $date_from = $year . '-' . $month . '-01';
        $date_to = date('Y-m-t', strtotime($date_from));

        $category_details = $this->cashbook_model->get_expense_details_by_category($category_id, $date_from, $date_to);

        // Get category name
        $category = $this->db->get_where('fund_category', ['id' => $category_id])->row();
        $category_name = $category ? $category->name : 'Unknown Category';

        echo json_encode(['expenses' => $category_details, 'category_name' => $category_name]);
    }

    public function get_sales_revenue_details()
    {
        if (!get_permission('cashbook_manage', 'is_view')) {
            ajax_access_denied();
        }

        $month_year = $this->input->post('month_year');
        $month = date("m", strtotime($month_year));
        $year = date("Y", strtotime($month_year));

        $date_from = $year . '-' . $month . '-01';
        $date_to = date('Y-m-t', strtotime($date_from));

        $income_details = $this->cashbook_model->get_sales_revenue_details($date_from, $date_to);

        echo json_encode(['incomes' => $income_details]);
    }

    public function get_income_type_details()
    {
        if (!get_permission('cashbook_manage', 'is_view')) {
            ajax_access_denied();
        }

        $reference_type = $this->input->post('reference_type');
        $month_year = $this->input->post('month_year');
        $month = date("m", strtotime($month_year));
        $year = date("Y", strtotime($month_year));

        $date_from = $year . '-' . $month . '-01';
        $date_to = date('Y-m-t', strtotime($date_from));

        $income_details = $this->cashbook_model->get_income_details_by_type($reference_type, $date_from, $date_to);

        echo json_encode(['incomes' => $income_details]);
    }

    public function summary()
    {
        if (!get_permission('cashbook_manage', 'is_view')) {
            access_denied();
        }

        $month = '';
        $year = '';

        if (isset($_POST['search'])) {
            $month_year = $this->input->post('month_year');
            $month = date("m", strtotime($month_year));
            $year = date("Y", strtotime($month_year));
        }

        $this->data['summary_data'] = $this->cashbook_model->getMonthlySummary($month, $year);
        $this->data['title'] = translate('cashbook_summary');
        $this->data['sub_page'] = 'cashbook/summary';
        $this->data['main_menu'] = 'cashbook';
        $this->load->view('layout/index', $this->data);
    }

    public function add_entry()
    {
        if (!get_permission('cashbook_manage', 'is_add')) {
            ajax_access_denied();
        }

        if ($_POST) {
            $this->form_validation->set_rules('entry_type', translate('entry_type'), 'required');
            $this->form_validation->set_rules('amount', translate('amount'), 'required|numeric');
            $this->form_validation->set_rules('description', translate('description'), 'required');
            $this->form_validation->set_rules('account_type', translate('account_type'), 'required');

            if ($this->form_validation->run() == true) {
                $account_id = $this->input->post('account_type');
                $account = $this->db->get_where('cashbook_accounts', ['id' => $account_id])->row();
                $account_name = $account ? $account->name : 'cash';

                // Determine reference type based on entry type and cash_in_type
                $reference_type = 'manual';
                if ($this->input->post('entry_type') == 'in' && $this->input->post('cash_in_type') == 'opening_balance') {
                    $reference_type = 'opening_balance';
                }

                $insertData = array(
                    'entry_type' => $this->input->post('entry_type'),
                    'amount' => $this->input->post('amount'),
                    'description' => $this->input->post('description'),
                    'account_type_id' => $account_id,
                    'account_type' => $account_name,
                    'reference_type' => $reference_type,
                    'reference_id' => 0,
                    'created_by' => get_loggedin_user_id(),
                    'entry_date' => date('Y-m-d H:i:s'),
                    'branch_id' => get_loggedin_branch_id(),
                );

                $this->db->insert('cashbook_entries', $insertData);
                $entry_id = $this->db->insert_id();

                // Log the manual entry creation
                $this->log_cashbook_action('MANUAL_CREATE', $entry_id, $insertData, get_loggedin_user_id());

                $url = base_url('cashbook');
                $array = array('status' => 'success', 'url' => $url, 'error' => '');
                set_alert('success', translate('information_has_been_saved_successfully'));
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'url' => '', 'error' => $error);
            }
            echo json_encode($array);
        }
    }

    public function transfer()
    {
        if (!get_permission('cashbook_manage', 'is_add')) {
            echo json_encode(['status' => 'fail', 'error' => 'Access denied']);
            return;
        }

        $from_account_id = $this->input->post('from_account');
        $to_account_id = $this->input->post('to_account');
        $amount = $this->input->post('amount');
        $description = $this->input->post('description');

        if (!$from_account_id || !$to_account_id || !$amount || !$description) {
            echo json_encode(['status' => 'fail', 'error' => 'All fields are required']);
            return;
        }

        if ($from_account_id == $to_account_id) {
            echo json_encode(['status' => 'fail', 'error' => 'Cannot transfer to same account']);
            return;
        }

        $from_account = $this->db->get_where('cashbook_accounts', ['id' => $from_account_id])->row();
        $to_account = $this->db->get_where('cashbook_accounts', ['id' => $to_account_id])->row();

        if (!$from_account || !$to_account) {
            echo json_encode(['status' => 'fail', 'error' => 'Invalid accounts']);
            return;
        }

        $current_time = date('Y-m-d H:i:s');

        // OUT entry (deduct from source)
        $this->db->insert('cashbook_entries', [
            'entry_type' => 'out',
            'amount' => $amount,
            'description' => 'Transfer to ' . $to_account->name . ': ' . $description,
            'account_type_id' => $from_account_id,
            'account_type' => $from_account->name,
            'reference_type' => 'transfer',
            'created_by' => get_loggedin_user_id(),
            'entry_date' => $current_time,
            'branch_id' => get_loggedin_branch_id()
        ]);

        // IN entry (add to destination)
        $this->db->insert('cashbook_entries', [
            'entry_type' => 'in',
            'amount' => $amount,
            'description' => 'Transfer from ' . $from_account->name . ': ' . $description,
            'account_type_id' => $to_account_id,
            'account_type' => $to_account->name,
            'reference_type' => 'transfer',
            'created_by' => get_loggedin_user_id(),
            'entry_date' => $current_time,
            'branch_id' => get_loggedin_branch_id()
        ]);

        set_alert('success', translate('information_has_been_updated_successfully'));
        redirect(base_url('cashbook'));
    }

    public function delete($id = '')
    {
        if (get_permission('cashbook_manage', 'is_delete')) {
            // Log before deletion
            $entry = $this->db->get_where('cashbook_entries', ['id' => $id])->row_array();
            if ($entry) {
                $this->log_cashbook_action('DELETE', $id, $entry, get_loggedin_user_id());
            }

            $this->db->where('id', $id);
            //$this->db->where_in('reference_type', ['manual', 'opening_balance']); // Allow deletion of manual and opening balance entries
            $this->db->delete('cashbook_entries');
        }
    }

    public function sync_fund_requisitions()
    {
        if (!get_permission('cashbook_manage', 'is_add')) {
            access_denied();
        }

        $synced = 0;
        $errors = 0;

        // Get all paid fund requisitions that are not synced
        $funds = $this->db->select('fr.id')
            ->from('fund_requisition fr')
            ->where('fr.payment_status', 2)
            ->where('fr.id NOT IN (SELECT reference_id FROM cashbook_entries WHERE reference_type = "fund_requisition")', null, false)
            ->get()->result_array();

        foreach ($funds as $fund) {
            try {
                $this->cashbook_model->syncFundRequisition($fund['id']);
                $synced++;
            } catch (Exception $e) {
                $errors++;
                $this->log_cashbook_action('SYNC_ERROR', $fund['id'], ['error' => $e->getMessage()], get_loggedin_user_id());
            }
        }

        $message = "Sync completed: {$synced} synced, {$errors} errors";
        if ($errors > 0) {
            set_alert('warning', $message);
        } else {
            set_alert('success', $message);
        }

        redirect(base_url('cashbook'));
    }

    public function fix_legacy_entries()
    {
        if (!get_permission('cashbook_manage', 'is_add')) {
            access_denied();
        }

        $updated = 0;

        // Get cash account ID
        $cash_account = $this->db->get_where('cashbook_accounts', ['name' => 'cash'])->row();
        $cash_account_id = $cash_account ? $cash_account->id : null;

        if ($cash_account_id) {
            // Update entries with account_type='cash' but no account_type_id
            $this->db->where('account_type', 'cash');
            $this->db->where('account_type_id IS NULL');
            $this->db->update('cashbook_entries', [
                'account_type_id' => $cash_account_id,
                'account_type' => null
            ]);
            $updated += $this->db->affected_rows();

            // Update entries with no account_type and no account_type_id (default to cash)
            $this->db->where('(account_type IS NULL OR account_type = "")');
            $this->db->where('account_type_id IS NULL');
            $this->db->update('cashbook_entries', [
                'account_type_id' => $cash_account_id
            ]);
            $updated += $this->db->affected_rows();

            // Re-sync existing fund requisitions and advance salaries to fix their account_type_id
            $funds = $this->db->select('reference_id')
                ->where('reference_type', 'fund_requisition')
                ->get('cashbook_entries')
                ->result_array();

            foreach ($funds as $fund) {
                try {
                    $this->cashbook_model->syncFundRequisition($fund['reference_id']);
                } catch (Exception $e) {
                    // Continue with other entries
                }
            }

            $advances = $this->db->select('reference_id')
                ->where('reference_type', 'advance_salary')
                ->get('cashbook_entries')
                ->result_array();

            foreach ($advances as $advance) {
                try {
                    $this->cashbook_model->syncAdvanceSalary($advance['reference_id']);
                } catch (Exception $e) {
                    // Continue with other entries
                }
            }
        }

        set_alert('success', "Fixed {$updated} legacy entries and re-synced all transactions");
        redirect(base_url('cashbook'));
    }

    public function setup_accounts()
    {
        if (!get_permission('cashbook_manage', 'is_add')) {
            access_denied();
        }

        // Check if accounts table exists and has data
        if (!$this->db->table_exists('cashbook_accounts')) {
            set_alert('error', 'Cashbook accounts table does not exist. Please run the database setup first.');
            redirect(base_url('cashbook'));
            return;
        }

        $count = $this->db->count_all('cashbook_accounts');
        if ($count == 0) {
            // Insert default accounts
            $default_accounts = [
                ['name' => 'Cash in Hand', 'description' => 'Physical cash available'],
                ['name' => 'Bank Asia', 'description' => 'Bank Asia account'],
                ['name' => 'Premier Bank', 'description' => 'Premier Bank account'],
                ['name' => 'Mobile Banking', 'description' => 'bKash, Nagad, Rocket etc.']
            ];

            $this->db->insert_batch('cashbook_accounts', $default_accounts);
            set_alert('success', 'Default cashbook accounts created successfully');
        } else {
            set_alert('info', 'Cashbook accounts already exist');
        }

        redirect(base_url('cashbook/accounts'));
    }

	public function monthly_sales_revenue()
    {
        if (!get_permission('cashbook_manage', 'is_view')) {
            access_denied();
        }

        // Get selected month or default to current month
        $selected_month = $this->input->post('month_year') ?: date('Y-m');

        // Get individual sales entries for the selected month excluding specific clients
        $this->db->select('entry_date, description, amount');
        $this->db->from('cashbook_entries');
        $this->db->where('entry_type', 'in');
        $this->db->where('reference_type', 'sales');
        $this->db->where("DATE_FORMAT(entry_date, '%Y-%m') =", $selected_month);
        // Exclude Sunnyat Ali, I3 Technologies, and Jerin Apu
        $this->db->where('description NOT LIKE', '%Sunnyat Ali%');
        $this->db->where('description NOT LIKE', '%I3 Technologies%');
        $this->db->where('description NOT LIKE', '%Jerin Apu%');
        $this->db->order_by('entry_date', 'ASC');

        $this->data['sales_data'] = $this->db->get()->result();
        $this->data['selected_month'] = $selected_month;
        $this->data['month_name'] = date('F Y', strtotime($selected_month . '-01'));
        $this->data['title'] = 'Monthly Sales Revenue';
        $this->data['sub_page'] = 'cashbook/monthly_sales_revenue';
        $this->data['main_menu'] = 'cashbook';
        $this->load->view('layout/index', $this->data);
    }

    private function log_cashbook_action($action, $entry_id, $data, $user_id)
    {
        $timestamp = date('Y-m-d H:i:s');
        $user_info = $this->db->select('name, staff_id')->where('id', $user_id)->get('staff')->row();
        $user_name = $user_info ? $user_info->name . ' (' . $user_info->staff_id . ')' : 'Unknown';

        $logEntry = "[$timestamp] ACTION: $action | ENTRY_ID: $entry_id | USER: $user_name | DATA: " . json_encode($data) . PHP_EOL;

        $logFile = FCPATH . 'application/logs/cashbook.log';
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        if (file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX) === false) {
            error_log("Cashbook Log: $logEntry");
        }
    }
}