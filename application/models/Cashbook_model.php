<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Cashbook_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_all_expenses($branch_id = null, $date_from = null, $date_to = null, $type = null)
    {
        $expenses = [];

        // Fund Requisitions
        if (!$type || $type == 'fund_requisition') {
            try {
                if (!$this->db->conn_id) {
                    return $expenses;
                }
                
                $this->db->select('fr.id, fr.amount, fr.reason as description, fr.create_at as date, s.name as employee_name, b.name as branch_name, "Fund Requisition" as type');
                $this->db->from('fund_requisition fr');
                $this->db->join('staff s', 's.id = fr.staff_id', 'left');
                $this->db->join('branch b', 'b.id = fr.branch_id', 'left');

                if ($branch_id) {
                    $this->db->where('fr.branch_id', $branch_id);
                }
                if ($date_from) {
                    $this->db->where('DATE(fr.create_at) >=', $date_from);
                }
                if ($date_to) {
                    $this->db->where('DATE(fr.create_at) <=', $date_to);
                }

                $result = $this->db->get();
                if ($result) {
                    $fund_requisitions = $result->result();
                    $expenses = array_merge($expenses, $fund_requisitions);
                }
            } catch (Exception $e) {
                log_message('error', 'Database error in get_all_expenses (fund_requisition): ' . $e->getMessage());
            }
        }

        // Advance Salary
        if (!$type || $type == 'advance_salary') {
            try {
                if (!$this->db->conn_id) {
                    return $expenses;
                }
                
                $this->db->select('asa.id, asa.amount, "Advance Salary" as description, asa.create_at as date, s.name as employee_name, b.name as branch_name, "Advance Salary" as type');
                $this->db->from('advance_salary asa');
                $this->db->join('staff s', 's.id = asa.staff_id', 'left');
                $this->db->join('branch b', 'b.id = asa.branch_id', 'left');

                if ($branch_id) {
                    $this->db->where('asa.branch_id', $branch_id);
                }
                if ($date_from) {
                    $this->db->where('DATE(asa.create_at) >=', $date_from);
                }
                if ($date_to) {
                    $this->db->where('DATE(asa.create_at) <=', $date_to);
                }

                $result = $this->db->get();
                if ($result) {
                    $advance_salaries = $result->result();
                    $expenses = array_merge($expenses, $advance_salaries);
                }
            } catch (Exception $e) {
                log_message('error', 'Database error in get_all_expenses (advance_salary): ' . $e->getMessage());
            }
        }

        // Salary Payments
        if (!$type || $type == 'salary_payment') {
            try {
                if (!$this->db->conn_id) {
                    return $expenses;
                }
                
                $this->db->select('sp.id, sp.net_salary as amount, CONCAT("Salary Payment - ", sp.month, "/", sp.year) as description, sp.created_at as date, "paid" as status, s.name as employee_name, b.name as branch_name, "Salary Payment" as type');
                $this->db->from('payslip sp');
                $this->db->join('staff s', 's.id = sp.staff_id', 'left');
                $this->db->join('branch b', 'b.id = sp.branch_id', 'left');

                if ($branch_id) {
                    $this->db->where('sp.branch_id', $branch_id);
                }
                if ($date_from) {
                    $this->db->where('DATE(sp.created_at) >=', $date_from);
                }
                if ($date_to) {
                    $this->db->where('DATE(sp.created_at) <=', $date_to);
                }

                $result = $this->db->get();
                if ($result) {
                    $salary_payments = $result->result();
                    $expenses = array_merge($expenses, $salary_payments);
                }
            } catch (Exception $e) {
                log_message('error', 'Database error in get_all_expenses (salary_payment): ' . $e->getMessage());
            }
        }

        // Sort by date descending
        if (!empty($expenses)) {
            usort($expenses, function($a, $b) {
                return strtotime($b->date) - strtotime($a->date);
            });
        }

        return $expenses;
    }

    public function get_total_expenses($branch_id = null, $date_from = null, $date_to = null, $type = null)
    {
        try {
            $total = 0;
            $expenses = $this->get_all_expenses($branch_id, $date_from, $date_to, $type);

            foreach ($expenses as $expense) {
                $total += $expense->amount;
            }

            return $total;
        } catch (Exception $e) {
            log_message('error', 'Database error in get_total_expenses: ' . $e->getMessage());
            return 0;
        }
    }

    public function get_all_incomes($date_from = null, $date_to = null, $type = null, $only_paid = false)
    {
        $incomes = [];

        // ============================
        // 1. SALES INCOME (ERP SALES)
        // ============================
        if (!$type || $type == 'sales') {
            try {
                $erp = $this->load->database('erp', TRUE);
                
                if (!$erp || !$erp->conn_id) {
                    log_message('error', 'ERP database connection failed in get_all_incomes');
                    return $incomes;
                }

                // Get all transactions with payment summary
                $erp->select('
                    t.id AS transaction_id,
                    t.final_total AS total_amount,
                    COALESCE(paid.total_paid, 0) AS paid_amount,
                    (t.final_total - COALESCE(paid.total_paid, 0)) AS due_amount,
                    t.transaction_date AS date,
                    t.payment_status AS status,
                    bl.name AS business_location,
                    REPLACE(TRIM(COALESCE(c.name, t.custom_field_1, "Walk-in Customer")), ",", "") AS customer_name,
                    COALESCE(c.mobile, c.contact_id, "") AS phone,
                    c.supplier_business_name as business_name,
                    "sales" AS type,
                    COALESCE(GROUP_CONCAT(DISTINCT tp.method SEPARATOR ", "), "") AS payment_method,
                    t.invoice_no,
                    CONCAT(u.first_name, " ", u.last_name) AS created_by
                ');

                $erp->from('transactions t');
                $erp->join('contacts c', 'c.id = t.contact_id', 'left');
                $erp->join('users u', 'u.id = t.created_by', 'left');
                $erp->join('business_locations bl', 'bl.id = t.location_id', 'left');
                $erp->join('(SELECT transaction_id, SUM(amount) as total_paid FROM transaction_payments GROUP BY transaction_id) paid', 'paid.transaction_id = t.id', 'left');
                $erp->join('transaction_payments tp', 'tp.transaction_id = t.id', 'left');

                $erp->where('t.type', 'sell');
                $erp->where('t.status', 'final');
                $erp->where('bl.id', 1);

                // Date filter
                if ($date_from) {
                    $erp->where('DATE(t.transaction_date) >=', $date_from);
                }
                if ($date_to) {
                    $erp->where('DATE(t.transaction_date) <=', $date_to);
                }

                $erp->group_by('t.id');
                $erp->order_by('t.transaction_date', 'DESC');

                $query = $erp->get();
                if ($query) {
                    $sales_income = $query->result();
                    foreach ($sales_income as $income) {
                        $income->id = $income->transaction_id;
                        $income->amount = $income->paid_amount; // For compatibility
                    }
                    $incomes = array_merge($incomes, $sales_income);
                }
            } catch (Exception $e) {
                log_message('error', 'ERP database error in get_all_incomes: ' . $e->getMessage());
            }
        }

        /* // ============================
        // 2. OTHER INCOME (CASHBOOK)
        // ============================
        if (!$type || $type == 'other') {

            $this->db->select('
                ce.id,
                ce.amount,
                ce.entry_date AS date,
                "paid" AS status,
                CASE
                    WHEN ce.reference_type = "opening_balance" THEN "Opening Balance"
                    ELSE "Manual Entry"
                END AS customer_name,
                "other" AS type,
                "" AS payment_method,
                "" AS invoice_no,
                "" AS created_by
            ');

            $this->db->from('cashbook_entries ce');
            $this->db->where('ce.entry_type', 'in');
            $this->db->where_in('ce.reference_type', ['manual', 'opening_balance']);

            if ($date_from) {
                $this->db->where('DATE(ce.entry_date) >=', $date_from);
            }
            if ($date_to) {
                $this->db->where('DATE(ce.entry_date) <=', $date_to);
            }

            $result = $this->db->get();
            if ($result) {
                $other_income = $result->result();
                $incomes = array_merge($incomes, $other_income);
            }
        } */

        // ============================
        // 3. SORT BY DATE DESCENDING
        // ============================
        if (!empty($incomes)) {
            usort($incomes, function($a, $b) {
                return strtotime($b->date) - strtotime($a->date);
            });
        }

        return $incomes;
    }


    public function get_total_incomes($date_from = null, $date_to = null, $type = null)
    {
        $total_sales = 0;
        $total_other = 0;

        // Get sales income from ERP
        if (!$type || $type == 'sales') {
            try {
                $erp = $this->load->database('erp', TRUE);
                
                if (!$erp || !$erp->conn_id) {
                    log_message('error', 'ERP database connection failed in get_total_incomes');
                    return 0;
                }

                // Sum actual payment amounts (professional approach)
                $erp->select('SUM(tp.amount) as total_amount');
                $erp->from('transaction_payments tp');
                $erp->join('transactions t', 't.id = tp.transaction_id');
                $erp->join('business_locations bl', 'bl.id = t.location_id', 'left');
                $erp->where('t.type', 'sell');
                $erp->where('t.status', 'final');
                $erp->where('bl.id', 1);

                if ($date_from) {
                    $erp->where('DATE(t.transaction_date) >=', $date_from);
                }
                if ($date_to) {
                    $erp->where('DATE(t.transaction_date) <=', $date_to);
                }

                $result = $erp->get()->row();
                $total_sales = $result->total_amount ?? 0;
            } catch (Exception $e) {
                log_message('error', 'ERP database error in get_total_incomes: ' . $e->getMessage());
                $total_sales = 0;
            }
        }

        /* // Get other income (manual entries and opening balance)
        if (!$type || $type == 'other') {
            $this->db->select('SUM(amount) as total_amount');
            $this->db->from('cashbook_entries');
            $this->db->where('entry_type', 'in');
            $this->db->where_in('reference_type', ['manual', 'opening_balance']);

            if ($date_from) {
                $this->db->where('DATE(entry_date) >=', $date_from);
            }
            if ($date_to) {
                $this->db->where('DATE(entry_date) <=', $date_to);
            }

            $result = $this->db->get()->row();
            $total_other = $result->total_amount ?? 0;
        } */

        return $total_sales + $total_other;
    }

    public function get_income_by_payment_method($payment_method, $date_from = null, $date_to = null)
    {
        try {
            $erp = $this->load->database('erp', TRUE);
            
            if (!$erp || !$erp->conn_id) {
                log_message('error', 'ERP database connection failed in get_income_by_payment_method');
                return 0;
            }
            
            $erp->select('SUM(tp.amount) as total_amount');
            $erp->from('transaction_payments tp');
            $erp->join('transactions t', 't.id = tp.transaction_id');
            $erp->join('business_locations bl', 'bl.id = t.location_id', 'left');
            $erp->where('t.type', 'sell');
            $erp->where('t.status', 'final');
            $erp->where('bl.id', 1);

            // Map payment methods
            if ($payment_method == 'cash') {
                $erp->where('tp.method', 'cash');
            } else if ($payment_method == 'bank') {
                $erp->where_in('tp.method', ['bank_transfer', 'bank', 'cheque']);
            }

            if ($date_from) {
                $erp->where('DATE(t.transaction_date) >=', $date_from);
            }
            if ($date_to) {
                $erp->where('DATE(t.transaction_date) <=', $date_to);
            }

            return $erp->get()->row()->total_amount ?? 0;
        } catch (Exception $e) {
            log_message('error', 'ERP database error in get_income_by_payment_method: ' . $e->getMessage());
            return 0;
        }
    }

    public function get_total_dues($date_from = null, $date_to = null)
    {
        try {
            $erp = $this->load->database('erp', TRUE);
            
            if (!$erp || !$erp->conn_id) {
                log_message('error', 'ERP database connection failed in get_total_dues');
                return 0;
            }

            // Get total due amount
            $erp->select('SUM(t.final_total) as total_due');
            $erp->from('transactions t');
            $erp->join('business_locations bl', 'bl.id = t.location_id', 'left');
            $erp->where('t.type', 'sell');
            $erp->where('t.status', 'final');
            $erp->where('bl.id', 1);
            $erp->where('t.payment_status', 'due');

            if ($date_from) {
                $erp->where('DATE(t.transaction_date) >=', $date_from);
            }
            if ($date_to) {
                $erp->where('DATE(t.transaction_date) <=', $date_to);
            }

            $due_total = $erp->get()->row()->total_due ?? 0;

            // Get partial payment due amounts
            $erp2 = $this->load->database('erp', TRUE);
            if (!$erp2 || !$erp2->conn_id) {
                return $due_total;
            }
            
            $erp2->select('SUM(t.final_total - COALESCE(tp_sum.paid_amount, 0)) as partial_due');
            $erp2->from('transactions t');
            $erp2->join('(
                SELECT transaction_id, SUM(amount) as paid_amount
                FROM transaction_payments
                GROUP BY transaction_id
            ) tp_sum', 'tp_sum.transaction_id = t.id', 'left');
            $erp2->join('business_locations bl', 'bl.id = t.location_id', 'left');
            $erp2->where('t.type', 'sell');
            $erp2->where('t.status', 'final');
            $erp2->where('bl.id', 1);
            $erp2->where('t.payment_status', 'partial');

            if ($date_from) {
                $erp2->where('DATE(t.transaction_date) >=', $date_from);
            }
            if ($date_to) {
                $erp2->where('DATE(t.transaction_date) <=', $date_to);
            }

            $partial_due = $erp2->get()->row()->partial_due ?? 0;

            return $due_total + $partial_due;
        } catch (Exception $e) {
            log_message('error', 'ERP database error in get_total_dues: ' . $e->getMessage());
            return 0;
        }
    }

    public function getCashbookData($start_date = '', $end_date = '', $account_type = '')
    {
        try {
            if (!$this->db->conn_id) {
                log_message('error', 'Database connection failed in getCashbookData');
                return [];
            }
            
            $this->db->select('ce.*, s.name as created_by_name, ca.name as account_name');
            $this->db->from('cashbook_entries ce');
            $this->db->join('staff s', 's.id = ce.created_by', 'left');
            $this->db->join('cashbook_accounts ca', 'ca.id = ce.account_type_id', 'left');

            if (!empty($start_date) && !empty($end_date)) {
                $this->db->where('DATE(ce.entry_date) >=', $start_date);
                $this->db->where('DATE(ce.entry_date) <=', $end_date);
            }

            if (!empty($account_type) && $account_type != 'all') {
                $this->db->where('ce.account_type_id', $account_type);
            }

            $this->db->order_by('ce.entry_date DESC, ce.id DESC');
            return $this->db->get()->result_array();
        } catch (Exception $e) {
            log_message('error', 'Database error in getCashbookData: ' . $e->getMessage());
            return [];
        }
    }

    public function getCurrentBalance($branch_id = null, $start_date = '', $end_date = '', $account_type = '')
    {
        try {
            if (!$this->db->conn_id) {
                log_message('error', 'Database connection failed in getCurrentBalance');
                return array(
                    'cash_balance' => 0,
                    'bank_asia_balance' => 0,
                    'premier_bank_balance' => 0,
                    'total_balance' => 0
                );
            }
            
            // Get all accounts and categorize them
            $accounts = $this->db->get('cashbook_accounts')->result_array();
            $cash_accounts = [];
            $bank_asia_accounts = [];
            $premier_bank_accounts = [];

        foreach ($accounts as $account) {
            $name = strtolower($account['name']);
            if (strpos($name, 'cash') !== false || strpos($name, 'hand') !== false) {
                $cash_accounts[] = $account['id'];
            } elseif (strpos($name, 'bank asia') !== false) {
                $bank_asia_accounts[] = $account['id'];
            } elseif (strpos($name, 'premier') !== false) {
                $premier_bank_accounts[] = $account['id'];
            }
        }

        // Calculate cash balance
        $cash_in = $this->getBalanceByAccounts($cash_accounts, 'in', $branch_id, true, $start_date, $end_date, $account_type);
        $cash_out = $this->getBalanceByAccounts($cash_accounts, 'out', $branch_id, true, $start_date, $end_date, $account_type);

        // Calculate Bank Asia balance
        $bank_asia_in = $this->getBalanceByAccounts($bank_asia_accounts, 'in', $branch_id, false, $start_date, $end_date, $account_type);
        $bank_asia_out = $this->getBalanceByAccounts($bank_asia_accounts, 'out', $branch_id, false, $start_date, $end_date, $account_type);

        // Calculate Premier Bank balance
        $premier_bank_in = $this->getBalanceByAccounts($premier_bank_accounts, 'in', $branch_id, false, $start_date, $end_date, $account_type);
        $premier_bank_out = $this->getBalanceByAccounts($premier_bank_accounts, 'out', $branch_id, false, $start_date, $end_date, $account_type);

        $cash_balance = $cash_in - $cash_out;
        $bank_asia_balance = $bank_asia_in - $bank_asia_out;
        $premier_bank_balance = $premier_bank_in - $premier_bank_out;

            return array(
                'cash_balance' => $cash_balance,
                'bank_asia_balance' => $bank_asia_balance,
                'premier_bank_balance' => $premier_bank_balance,
                'total_balance' => $cash_balance + $bank_asia_balance + $premier_bank_balance
            );
        } catch (Exception $e) {
            log_message('error', 'Database error in getCurrentBalance: ' . $e->getMessage());
            return array(
                'cash_balance' => 0,
                'bank_asia_balance' => 0,
                'premier_bank_balance' => 0,
                'total_balance' => 0
            );
        }
    }

    private function getBalanceByAccounts($account_ids, $entry_type, $branch_id = null, $include_legacy = false, $start_date = '', $end_date = '', $account_type = '')
    {
        try {
            if (!$this->db->conn_id) {
                return 0;
            }
            
            $this->db->select_sum('amount');
            $this->db->where('entry_type', $entry_type);

        // Date filters
        if (!empty($start_date) && !empty($end_date)) {
            $this->db->where('DATE(entry_date) >=', $start_date);
            $this->db->where('DATE(entry_date) <=', $end_date);
        }

        // Account type filter
        if (!empty($account_type)) {
            $this->db->where('account_type_id', $account_type);
        } else {
            if (!empty($account_ids) || $include_legacy) {
                $this->db->group_start();
                if (!empty($account_ids)) {
                    $this->db->where_in('account_type_id', $account_ids);
                }
                if ($include_legacy) {
                    if (!empty($account_ids)) $this->db->or_group_start();
                    $this->db->where('account_type', 'cash');
                    $this->db->or_where('account_type_id IS NULL');
                    $this->db->or_where('account_type IS NULL');
                    $this->db->or_where('account_type', '');
                    if (!empty($account_ids)) $this->db->group_end();
                }
                $this->db->group_end();
            } else {
                $this->db->where('1=0'); // No matching accounts
            }
        }

            if ($branch_id) $this->db->where('branch_id', $branch_id);
            return $this->db->get('cashbook_entries')->row()->amount ?? 0;
        } catch (Exception $e) {
            log_message('error', 'Database error in getBalanceByAccounts: ' . $e->getMessage());
            return 0;
        }
    }

    public function getMonthlySummary($month = '', $year = '')
    {
        // Build different where clauses for different tables
        $fund_where = '';
        $advance_where = '';
        $payroll_where = '';
        $cashbook_where = '';

        if (!empty($month) && !empty($year)) {
            $fund_where = "WHERE MONTH(create_at) = '$month' AND YEAR(create_at) = '$year'";
            $advance_where = "WHERE MONTH(create_at) = '$month' AND YEAR(create_at) = '$year'";
            $payroll_where = "WHERE MONTH(created_at) = '$month' AND YEAR(created_at) = '$year'";
            $cashbook_where = "WHERE MONTH(entry_date) = '$month' AND YEAR(entry_date) = '$year'";
        }

        // Get fund requisitions summary
        $fund_requisitions = $this->db->query("
            SELECT
                SUM(CASE WHEN payment_status = 2 THEN amount ELSE 0 END) as paid_amount,
                COUNT(CASE WHEN payment_status = 2 THEN 1 END) as paid_count,
                SUM(CASE WHEN payment_status = 1 THEN amount ELSE 0 END) as pending_amount,
                COUNT(CASE WHEN payment_status = 1 THEN 1 END) as pending_count
            FROM fund_requisition
            $fund_where
        ")->row_array();

        // Get advance salary summary
        $advance_salary = $this->db->query("
            SELECT
                SUM(CASE WHEN payment_status = 2 THEN amount ELSE 0 END) as paid_amount,
                COUNT(CASE WHEN payment_status = 2 THEN 1 END) as paid_count,
                SUM(CASE WHEN payment_status = 1 THEN amount ELSE 0 END) as pending_amount,
                COUNT(CASE WHEN payment_status = 1 THEN 1 END) as pending_count
            FROM advance_salary
            $advance_where
        ")->row_array();

        // Get payroll summary
        $payroll = $this->db->query("
            SELECT
                SUM(net_salary) as total_paid,
                COUNT(*) as total_count
            FROM payslip
            $payroll_where
        ")->row_array();

        // Get cashbook entries summary (including opening balance)
        $cashbook_summary = $this->db->query("
            SELECT
                SUM(CASE WHEN entry_type = 'in' AND (account_type = 'cash' OR reference_type = 'opening_balance') THEN amount ELSE 0 END) as cash_in,
                SUM(CASE WHEN entry_type = 'out' AND account_type = 'cash' THEN amount ELSE 0 END) as cash_out,
                SUM(CASE WHEN entry_type = 'in' AND account_type = 'bank' THEN amount ELSE 0 END) as bank_in,
                SUM(CASE WHEN entry_type = 'out' AND account_type = 'bank' THEN amount ELSE 0 END) as bank_out,
                SUM(CASE WHEN entry_type = 'in' AND reference_type = 'opening_balance' THEN amount ELSE 0 END) as opening_balance_total
            FROM cashbook_entries
            $cashbook_where
        ")->row_array();

        return array(
            'fund_requisitions' => $fund_requisitions,
            'advance_salary' => $advance_salary,
            'payroll' => $payroll,
            'cashbook' => $cashbook_summary
        );
    }

    public function addCashbookEntry($data)
    {
        // Validate required fields
        $required = ['entry_type', 'amount', 'description', 'account_type', 'created_by'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Required field missing: {$field}");
            }
        }

        // Set defaults
        if (empty($data['entry_date'])) {
            $data['entry_date'] = date('Y-m-d H:i:s');
        }
        if (empty($data['reference_type'])) {
            $data['reference_type'] = 'manual';
        }
        if (empty($data['reference_id'])) {
            $data['reference_id'] = 0;
        }

        return $this->db->insert('cashbook_entries', $data);
    }

    public function syncFundRequisition($fund_id)
    {
        if (empty($fund_id)) {
            throw new Exception('Fund ID is required for cashbook sync');
        }

        $fund = $this->db->select('fr.*, s.name as staff_name, fc.name as category_name')
            ->from('fund_requisition fr')
            ->join('staff s', 's.id = fr.staff_id', 'left')
            ->join('fund_category fc', 'fc.id = fr.category_id', 'left')
            ->where('fr.id', $fund_id)
            ->get()->row_array();

        if (!$fund) {
            throw new Exception('Fund requisition not found: ' . $fund_id);
        }

        if ($fund['payment_status'] != 2) {
            throw new Exception('Fund requisition is not marked as paid: ' . $fund_id);
        }

        // Check if entry already exists
        $existing = $this->db->get_where('cashbook_entries', array(
            'reference_type' => 'fund_requisition',
            'reference_id' => $fund_id
        ))->row();

        // Get account details
        $account_type_id = $fund['payment_method'];
        $account_name = 'cash'; // Default

        if (!empty($account_type_id)) {
            $account = $this->db->get_where('cashbook_accounts', ['id' => $account_type_id])->row();
            if ($account) {
                $account_name = $account->name;
            }
        } else {
            // Default to cash account
            $cash_account = $this->db->get_where('cashbook_accounts', ['name' => 'cash'])->row();
            if ($cash_account) {
                $account_type_id = $cash_account->id;
                $account_name = $cash_account->name;
            }
        }

        if ($existing) {
            // Update existing entry
            $update_data = array(
                'amount' => $fund['amount'],
                'description' => $this->buildFundDescription($fund),
                'account_type_id' => $account_type_id,
                'account_type' => $account_name,
                'entry_date' => $fund['paid_date']
            );

            $this->db->where('id', $existing->id);
            return $this->db->update('cashbook_entries', $update_data);
        } else {
            // Create new entry
            $entry_data = array(
                'entry_type' => 'out',
                'amount' => $fund['amount'],
                'description' => $this->buildFundDescription($fund),
                'account_type_id' => $account_type_id,
                'account_type' => $account_name,
                'reference_type' => 'fund_requisition',
                'reference_id' => $fund_id,
                'created_by' => $fund['paid_by'] ?: $fund['issued_by'],
                'entry_date' => $fund['paid_date'] ?: date('Y-m-d H:i:s'),
                'branch_id' => $fund['branch_id']
            );

            return $this->db->insert('cashbook_entries', $entry_data);
        }
    }

    private function buildFundDescription($fund)
    {
        $staff_name = $fund['staff_name'] ?: 'Unknown Staff';
        $category = $fund['category_name'] ?: 'General';
        $reason = !empty($fund['reason']) ? ' - ' . substr($fund['reason'], 0, 50) : '';

        return "Fund Requisition: {$staff_name} ({$category}){$reason}";
    }

    public function syncAdvanceSalary($advance_id)
    {
        if (empty($advance_id)) {
            throw new Exception('Advance Salary ID is required for cashbook sync');
        }

        $advance = $this->db->select('asa.*, s.name as staff_name')
            ->from('advance_salary asa')
            ->join('staff s', 's.id = asa.staff_id', 'left')
            ->where('asa.id', $advance_id)
            ->get()->row_array();

        if (!$advance) {
            throw new Exception('Advance salary not found: ' . $advance_id);
        }

        if ($advance['payment_status'] != 2) {
            throw new Exception('Advance salary is not marked as paid: ' . $advance_id);
        }

        // Check if entry already exists
        $existing = $this->db->get_where('cashbook_entries', array(
            'reference_type' => 'advance_salary',
            'reference_id' => $advance_id
        ))->row();

        // Get account details
        $account_type_id = $advance['payment_method'];
        $account_name = 'cash'; // Default

        if (!empty($account_type_id)) {
            $account = $this->db->get_where('cashbook_accounts', ['id' => $account_type_id])->row();
            if ($account) {
                $account_name = $account->name;
            }
        } else {
            // Default to cash account
            $cash_account = $this->db->get_where('cashbook_accounts', ['name' => 'cash'])->row();
            if ($cash_account) {
                $account_type_id = $cash_account->id;
                $account_name = $cash_account->name;
            }
        }

        if ($existing) {
            // Update existing entry
            $update_data = array(
                'amount' => $advance['amount'],
                'description' => $this->buildAdvanceSalaryDescription($advance),
                'account_type_id' => $account_type_id,
                'account_type' => $account_name,
                'entry_date' => $advance['paid_date']
            );

            $this->db->where('id', $existing->id);
            return $this->db->update('cashbook_entries', $update_data);
        } else {
            // Create new entry
            $entry_data = array(
                'entry_type' => 'out',
                'amount' => $advance['amount'],
                'description' => $this->buildAdvanceSalaryDescription($advance),
                'account_type_id' => $account_type_id,
                'account_type' => $account_name,
                'reference_type' => 'advance_salary',
                'reference_id' => $advance_id,
                'created_by' => $advance['paid_by'] ?: $advance['issued_by'],
                'entry_date' => $advance['paid_date'] ?: date('Y-m-d H:i:s'),
                'branch_id' => $advance['branch_id']
            );

            return $this->db->insert('cashbook_entries', $entry_data);
        }
    }

    private function buildAdvanceSalaryDescription($advance)
    {
        $staff_name = $advance['staff_name'] ?: 'Unknown Staff';
        $month_year = date('M Y', strtotime($advance['year'] . '-' . $advance['deduct_month'] . '-01'));
        $reason = !empty($advance['reason']) ? ' - ' . substr($advance['reason'], 0, 50) : '';

        return "Advance Salary: {$staff_name} ({$month_year}){$reason}";
    }

    public function syncPayroll($payslip_id)
    {
        if (empty($payslip_id)) {
            throw new Exception('Payslip ID is required for cashbook sync');
        }

        $payslip = $this->db->select('p.*, s.name as staff_name')
            ->from('payslip p')
            ->join('staff s', 's.id = p.staff_id', 'left')
            ->where('p.id', $payslip_id)
            ->get()->row_array();

        if (!$payslip) {
            throw new Exception('Payslip not found: ' . $payslip_id);
        }

        // Check if entry already exists
        $existing = $this->db->get_where('cashbook_entries', array(
            'reference_type' => 'payroll',
            'reference_id' => $payslip_id
        ))->row();

        // Get account details
        $account_type_id = $payslip['pay_via'];
        $account_name = 'cash'; // Default

        if (!empty($account_type_id)) {
            $account = $this->db->get_where('cashbook_accounts', ['id' => $account_type_id])->row();
            if ($account) {
                $account_name = $account->name;
            }
        } else {
            // Default to cash account
            $cash_account = $this->db->get_where('cashbook_accounts', ['name' => 'cash'])->row();
            if ($cash_account) {
                $account_type_id = $cash_account->id;
                $account_name = $cash_account->name;
            }
        }

        if ($existing) {
            // Update existing entry
            $update_data = array(
                'amount' => $payslip['net_salary'],
                'description' => $this->buildPayrollDescription($payslip),
                'account_type_id' => $account_type_id,
                'account_type' => $account_name,
                'entry_date' => date('Y-m-d H:i:s')
            );

            $this->db->where('id', $existing->id);
            return $this->db->update('cashbook_entries', $update_data);
        } else {
            // Create new entry
            $entry_data = array(
                'entry_type' => 'out',
                'amount' => $payslip['net_salary'],
                'description' => $this->buildPayrollDescription($payslip),
                'account_type_id' => $account_type_id,
                'account_type' => $account_name,
                'reference_type' => 'payroll',
                'reference_id' => $payslip_id,
                'created_by' => $payslip['paid_by'],
                'entry_date' => date('Y-m-d H:i:s'),
                'branch_id' => $payslip['branch_id']
            );

            return $this->db->insert('cashbook_entries', $entry_data);
        }
    }

    private function buildPayrollDescription($payslip)
    {
        $staff_name = $payslip['staff_name'] ?: 'Unknown Staff';
        $month_year = date('M Y', strtotime($payslip['year'] . '-' . $payslip['month'] . '-01'));

        return "Salary Payment: {$staff_name} ({$month_year})";
    }

    public function bulkSyncFundRequisitions()
    {
        $results = ['synced' => 0, 'errors' => 0, 'messages' => []];

        // Get all paid fund requisitions that are not synced
        $funds = $this->db->select('fr.id, fr.amount, s.name as staff_name')
            ->from('fund_requisition fr')
            ->join('staff s', 's.id = fr.staff_id', 'left')
            ->where('fr.payment_status', 2)
            ->where('fr.id NOT IN (SELECT reference_id FROM cashbook_entries WHERE reference_type = "fund_requisition")', null, false)
            ->get()->result_array();

        foreach ($funds as $fund) {
            try {
                $this->syncFundRequisition($fund['id']);
                $results['synced']++;
                $results['messages'][] = "Synced Fund #{$fund['id']} - {$fund['staff_name']} (BDT {$fund['amount']})";
            } catch (Exception $e) {
                $results['errors']++;
                $results['messages'][] = "Error Fund #{$fund['id']}: " . $e->getMessage();
            }
        }

        return $results;
    }

    public function getUnsyncedFundRequisitions()
    {
        return $this->db->select('fr.id, fr.amount, fr.reason, s.name as staff_name, fc.name as category')
            ->from('fund_requisition fr')
            ->join('staff s', 's.id = fr.staff_id', 'left')
            ->join('fund_category fc', 'fc.id = fr.category_id', 'left')
            ->where('fr.payment_status', 2)
            ->where('fr.id NOT IN (SELECT reference_id FROM cashbook_entries WHERE reference_type = "fund_requisition")', null, false)
            ->order_by('fr.paid_date', 'DESC')
            ->get()->result_array();
    }

	public function get_report_entries($date_from, $date_to)
	{
		// ===========================
		// 1. GROUP INCOMES - SALES REVENUE CATEGORY
		// ===========================

		// Get total sales revenue from cashbook entries
		$this->db->select("
			SUM(amount) AS total_amount,
			COUNT(*) AS count
		");
		$this->db->from('cashbook_entries');
		$this->db->where('entry_type', 'in');
		$this->db->where('reference_type', 'sales');

		if ($date_from) $this->db->where('DATE(entry_date) >=', $date_from);
		if ($date_to)   $this->db->where('DATE(entry_date) <=', $date_to);

		$sales_revenue = $this->db->get()->row();

		// Get other income types (non-sales)
		$this->db->select("
			reference_type AS customer_name,
			SUM(amount) AS total_amount,
			COUNT(*) AS count,
			MIN(entry_date) AS entry_date,
			MIN(account_type) AS account_type,
			'reference_type' AS income_type
		");
		$this->db->from('cashbook_entries');
		$this->db->where('entry_type', 'in');
		$this->db->where('reference_type !=', 'sales');
		$this->db->where('reference_type !=', 'opening_balance');
		$this->db->where('reference_type !=', 'transfer'); // Exclude transfer amounts

		if ($date_from) $this->db->where('DATE(entry_date) >=', $date_from);
		if ($date_to)   $this->db->where('DATE(entry_date) <=', $date_to);

		$this->db->group_by('reference_type');
		$other_incomes = $this->db->get()->result();

		// Combine and format all incomes
		$income_categories = [];

		// Add Sales Revenue category first
		if ($sales_revenue && $sales_revenue->total_amount > 0) {
			$income_categories[] = (object) [
				'description' => 'Total Sales Revenue (' . $sales_revenue->count . ')',
				'amount' => $sales_revenue->total_amount,
				'customer_name' => 'Sales Revenue',
				'income_type' => 'sales_revenue',
				'count' => $sales_revenue->count
			];
		}

		// Add other income types
		foreach ($other_incomes as $income) {
			$display_name = ucwords(str_replace('_', ' ', $income->customer_name));
			$income_categories[] = (object) [
				'description' => 'Total: ' . $display_name . ' (' . $income->count . ')',
				'amount' => $income->total_amount,
				'customer_name' => $display_name,
				'income_type' => 'reference_type',
				'reference_type' => $income->customer_name,
				'entry_date' => $income->entry_date
			];
		}

		// Sort by amount descending
		usort($income_categories, function($a, $b) {
			return $b->amount - $a->amount;
		});

		// ===========================
		// 2. EXPENSES - FUND REQUISITIONS BY CATEGORY + OTHERS BY TYPE
		// ===========================

		// Get fund requisitions grouped by category
		$this->db->select("
			fc.name AS category_name,
			fc.id AS category_id,
			SUM(ce.amount) AS total_amount,
			COUNT(*) AS count,
			'fund_category' AS expense_type
		");
		$this->db->from("cashbook_entries ce");
		$this->db->join("fund_requisition fr", "fr.id = ce.reference_id", "inner");
		$this->db->join("fund_category fc", "fc.id = fr.category_id", "inner");
		$this->db->where("ce.entry_type", "out");
		$this->db->where("ce.reference_type", "fund_requisition");

		if ($date_from) $this->db->where('DATE(ce.entry_date) >=', $date_from);
		if ($date_to)   $this->db->where('DATE(ce.entry_date) <=', $date_to);

		$this->db->group_by('fc.id');
		$fund_categories = $this->db->get()->result();

		// Get other expense types (non-fund requisitions)
		$this->db->select("
			reference_type AS category_name,
			0 AS category_id,
			SUM(amount) AS total_amount,
			COUNT(*) AS count,
			'reference_type' AS expense_type
		");
		$this->db->from("cashbook_entries");
		$this->db->where("entry_type", "out");
		$this->db->where("reference_type !=", "fund_requisition");
		$this->db->where("reference_type !=", "transfer");

		if ($date_from) $this->db->where('DATE(entry_date) >=', $date_from);
		if ($date_to)   $this->db->where('DATE(entry_date) <=', $date_to);

		$this->db->group_by('reference_type');
		$other_expenses = $this->db->get()->result();

		// Combine and format all expenses
		$expense_categories = [];

		// Add fund categories
		foreach ($fund_categories as $expense) {
			$expense_categories[] = (object) [
				'description' => 'Total Fund Requisition: ' . $expense->category_name . ' (' . $expense->count . ')',
				'amount' => $expense->total_amount,
				'category_id' => $expense->category_id,
				'category_name' => $expense->category_name,
				'expense_type' => 'fund_category'
			];
		}

		// Add other expense types
		foreach ($other_expenses as $expense) {
			$display_name = ucwords(str_replace('_', ' ', $expense->category_name));
			$expense_categories[] = (object) [
				'description' => 'Total: ' . $display_name . ' (' . $expense->count . ')',
				'amount' => $expense->total_amount,
				'reference_type' => $expense->category_name,
				'category_name' => $display_name,
				'expense_type' => 'reference_type'
			];
		}

		// Sort by amount descending
		usort($expense_categories, function($a, $b) {
			return $b->amount - $a->amount;
		});

		return [
			"incomes"  => $income_categories,
			"expenses" => $expense_categories
		];
	}

	public function get_detailed_entries($date_from, $date_to)
	{
		// Get incomes (same as grouped report but individual entries)
		$this->db->select("*");
		$this->db->from('cashbook_entries');
		$this->db->where('entry_type', 'in');
		$this->db->where('invoice_no IS NOT NULL', null, false);
		$this->db->where('reference_type !=', 'transfer'); // Exclude transfer amounts

		if ($date_from) $this->db->where('DATE(entry_date) >=', $date_from);
		if ($date_to)   $this->db->where('DATE(entry_date) <=', $date_to);

		$this->db->order_by("entry_date", "ASC");
		$incomes = $this->db->get()->result();

		foreach ($incomes as $row) {
			$row->customer_name = $row->description;
		}

		// Get other incomes without invoice_no
		$this->db->select("*");
		$this->db->from("cashbook_entries");
		$this->db->where("entry_type", "in");
		$this->db->where("invoice_no IS NULL", null, false);
		$this->db->where("reference_type !=", "opening_balance");
		$this->db->where('reference_type !=', 'transfer'); // Exclude transfer amounts

		if ($date_from) $this->db->where('DATE(entry_date) >=', $date_from);
		if ($date_to)   $this->db->where('DATE(entry_date) <=', $date_to);

		$this->db->order_by("entry_date", "ASC");
		$no_invoice_incomes = $this->db->get()->result();

		foreach ($no_invoice_incomes as $row) {
			$row->customer_name = $row->description;
		}

		$all_incomes = array_merge($incomes, $no_invoice_incomes);

		// Get expenses (individual entries)
		$this->db->select("*");
		$this->db->from("cashbook_entries");
		$this->db->where("entry_type", "out");
		$this->db->where('reference_type !=', 'transfer'); // Exclude transfer amounts

		if ($date_from) $this->db->where('DATE(entry_date) >=', $date_from);
		if ($date_to)   $this->db->where('DATE(entry_date) <=', $date_to);

		$this->db->order_by("entry_date", "ASC");
		$expenses = $this->db->get()->result();

		return [
			"incomes"  => $all_incomes,
			"expenses" => $expenses
		];
	}

	public function get_expense_details_by_type($reference_type, $date_from, $date_to)
	{
		$this->db->select("*");
		$this->db->from("cashbook_entries");
		$this->db->where("entry_type", "out");
		$this->db->where("reference_type", $reference_type);

		if ($date_from) $this->db->where('DATE(entry_date) >=', $date_from);
		if ($date_to)   $this->db->where('DATE(entry_date) <=', $date_to);

		$this->db->order_by("entry_date", "DESC");
		return $this->db->get()->result();
	}

	public function get_expense_details_by_category($category_id, $date_from, $date_to)
	{
		$this->db->select("ce.*, fr.reason");
		$this->db->from("cashbook_entries ce");
		$this->db->join("fund_requisition fr", "fr.id = ce.reference_id AND ce.reference_type = 'fund_requisition'", "inner");
		$this->db->where("ce.entry_type", "out");
		$this->db->where("fr.category_id", $category_id);

		if ($date_from) $this->db->where('DATE(ce.entry_date) >=', $date_from);
		if ($date_to)   $this->db->where('DATE(ce.entry_date) <=', $date_to);

		$this->db->order_by("ce.entry_date", "DESC");
		return $this->db->get()->result();
	}

	public function get_sales_revenue_details($date_from, $date_to)
	{
		$this->db->select("*");
		$this->db->from("cashbook_entries");
		$this->db->where("entry_type", "in");
		$this->db->where("reference_type", "sales");

		if ($date_from) $this->db->where('DATE(entry_date) >=', $date_from);
		if ($date_to)   $this->db->where('DATE(entry_date) <=', $date_to);

		$this->db->order_by("entry_date", "DESC");
		return $this->db->get()->result();
	}

	public function get_income_details_by_type($reference_type, $date_from, $date_to)
	{
		$this->db->select("*");
		$this->db->from("cashbook_entries");
		$this->db->where("entry_type", "in");
		$this->db->where("reference_type", $reference_type);
		$this->db->where("invoice_no IS NULL", null, false);

		if ($date_from) $this->db->where('DATE(entry_date) >=', $date_from);
		if ($date_to)   $this->db->where('DATE(entry_date) <=', $date_to);

		$this->db->order_by("entry_date", "DESC");
		return $this->db->get()->result();
	}

	public function get_all_dues($date_from = null, $date_to = null)
    {
        $dues = [];

        $erp = $this->load->database('erp', TRUE);

        $sql = "SELECT
            t.id AS transaction_id,
            t.final_total AS total_amount,
            COALESCE(paid.total_paid, 0) AS paid_amount,
            (t.final_total - COALESCE(paid.total_paid, 0)) AS due_amount,
            t.transaction_date AS date,
            t.payment_status AS status,
            bl.name AS business_location,
            REPLACE(TRIM(COALESCE(c.name, t.custom_field_1, 'Walk-in Customer')), ',', '') AS customer_name,
            COALESCE(c.mobile, c.contact_id, '') AS phone,
            c.supplier_business_name as business_name,
            'sales' AS type,
            t.invoice_no,
            CONCAT(u.first_name, ' ', u.last_name) AS created_by
        FROM transactions t
        LEFT JOIN contacts c ON c.id = t.contact_id
        LEFT JOIN users u ON u.id = t.created_by
        LEFT JOIN business_locations bl ON bl.id = t.location_id
        LEFT JOIN (SELECT transaction_id, SUM(amount) as total_paid FROM transaction_payments GROUP BY transaction_id) paid ON paid.transaction_id = t.id
        WHERE t.type = 'sell'
        AND t.status = 'final'
        AND bl.id = 1
        AND (t.payment_status = 'due' OR t.payment_status = 'partial')
        AND (t.final_total - COALESCE(paid.total_paid, 0)) > 0";

        if ($date_from) {
            $sql .= " AND DATE(t.transaction_date) >= '$date_from'";
        }
        if ($date_to) {
            $sql .= " AND DATE(t.transaction_date) <= '$date_to'";
        }

        $sql .= " ORDER BY t.transaction_date DESC";

        $query = $erp->query($sql);
        if ($query) {
            $dues = $query->result();
            foreach ($dues as $due) {
                $due->id = $due->transaction_id;
            }
        }

        return $dues;
    }

    public function get_dues_by_payment_method($payment_method, $date_from = null, $date_to = null)
    {
        $erp = $this->load->database('erp', TRUE);

        if ($payment_method == 'cash') {
            // Get fully due transactions (no payments made)
            $sql = "SELECT SUM(t.final_total) as total_due
                FROM transactions t
                LEFT JOIN business_locations bl ON bl.id = t.location_id
                WHERE t.type = 'sell'
                AND t.status = 'final'
                AND bl.id = 1
                AND t.payment_status = 'due'";
        } else {
            // Get partial payment dues (some payment made, usually bank)
            $sql = "SELECT SUM(t.final_total - COALESCE(paid.total_paid, 0)) as total_due
                FROM transactions t
                LEFT JOIN business_locations bl ON bl.id = t.location_id
                LEFT JOIN (SELECT transaction_id, SUM(amount) as total_paid FROM transaction_payments GROUP BY transaction_id) paid ON paid.transaction_id = t.id
                WHERE t.type = 'sell'
                AND t.status = 'final'
                AND bl.id = 1
                AND t.payment_status = 'partial'
                AND (t.final_total - COALESCE(paid.total_paid, 0)) > 0";
        }

        if ($date_from) {
            $sql .= " AND DATE(t.transaction_date) >= '$date_from'";
        }
        if ($date_to) {
            $sql .= " AND DATE(t.transaction_date) <= '$date_to'";
        }

        $result = $erp->query($sql);
        return $result ? ($result->row()->total_due ?? 0) : 0;
    }


    public function get_milestone_sales_income($milestone_id)
    {
        try {
            $erp = $this->load->database('erp', TRUE);

            // Check if milestone_id field exists, if not use contact milestone_id
            $erp->select('SUM(tp.amount) as total_amount');
            $erp->from('transaction_payments tp');
            $erp->join('transactions t', 't.id = tp.transaction_id');
            $erp->join('contacts c', 'c.id = t.contact_id', 'left');
            $erp->join('business_locations bl', 'bl.id = t.location_id', 'left');
            $erp->where('t.type', 'sell');
            $erp->where('t.status', 'final');
            $erp->where('bl.id', 1);
            $erp->where('c.milestone_id', $milestone_id);

            $result = $erp->get()->row();
            return $result ? ($result->total_amount ?? 0) : 0;
        } catch (Exception $e) {
            log_message('error', 'Error in get_milestone_sales_income: ' . $e->getMessage());
            return 0;
        }
    }

    public function get_milestone_fund_requisitions($milestone_id)
    {
        try {
            $this->db->select('SUM(amount) as total_amount');
            $this->db->from('fund_requisition');
            $this->db->where('milestone', $milestone_id);
            $this->db->where('payment_status', 2);

            $result = $this->db->get()->row();
            return $result ? ($result->total_amount ?? 0) : 0;
        } catch (Exception $e) {
            log_message('error', 'Error in get_milestone_fund_requisitions: ' . $e->getMessage());
            return 0;
        }
    }

    public function get_milestone_income_details($milestone_id)
    {
        try {
            $erp = $this->load->database('erp', TRUE);

            $erp->select('t.transaction_date as date, c.name as customer_name, t.invoice_no, tp.amount');
            $erp->from('transaction_payments tp');
            $erp->join('transactions t', 't.id = tp.transaction_id');
            $erp->join('contacts c', 'c.id = t.contact_id', 'left');
            $erp->join('business_locations bl', 'bl.id = t.location_id', 'left');
            $erp->where('t.type', 'sell');
            $erp->where('t.status', 'final');
            $erp->where('bl.id', 1);
            $erp->where('c.milestone_id', $milestone_id);
            $erp->order_by('t.transaction_date', 'DESC');

            return $erp->get()->result();
        } catch (Exception $e) {
            log_message('error', 'Error in get_milestone_income_details: ' . $e->getMessage());
            return [];
        }
    }

    public function get_milestone_expense_details($milestone_id)
    {
        try {
            $this->db->select('fr.create_at as date, fr.reason, fr.amount, s.name as staff_name');
            $this->db->from('fund_requisition fr');
            $this->db->join('staff s', 's.id = fr.staff_id', 'left');
            $this->db->where('fr.milestone', $milestone_id);
            $this->db->where('fr.payment_status', 2);
            $this->db->order_by('fr.create_at', 'DESC');

            return $this->db->get()->result();
        } catch (Exception $e) {
            log_message('error', 'Error in get_milestone_expense_details: ' . $e->getMessage());
            return [];
        }
    }

    public function get_milestone_indirect_costs($milestone_id)
    {
        try {
            // Get total spent hours for the milestone
            $this->db->select('SUM(spent_time) as total_hours');
            $this->db->from('tracker_issues');
            $this->db->where('milestone', $milestone_id);
            $result = $this->db->get()->row();

            return $result ? ($result->total_hours ?? 0) : 0;
        } catch (Exception $e) {
            log_message('error', 'Error in get_milestone_indirect_costs: ' . $e->getMessage());
            return 0;
        }
    }

    public function get_milestone_dues($milestone_id)
    {
        try {
            $erp = $this->load->database('erp', TRUE);

            $erp->select('t.transaction_date as date, c.name as customer_name, t.invoice_no, t.final_total as total_amount, COALESCE(paid.total_paid, 0) as paid_amount, (t.final_total - COALESCE(paid.total_paid, 0)) as due_amount');
            $erp->from('transactions t');
            $erp->join('contacts c', 'c.id = t.contact_id', 'left');
            $erp->join('business_locations bl', 'bl.id = t.location_id', 'left');
            $erp->join('(SELECT transaction_id, SUM(amount) as total_paid FROM transaction_payments GROUP BY transaction_id) paid', 'paid.transaction_id = t.id', 'left');
            $erp->where('t.type', 'sell');
            $erp->where('t.status', 'final');
            $erp->where('bl.id', 1);
            $erp->where('c.milestone_id', $milestone_id);
            $erp->where('(t.payment_status = "due" OR t.payment_status = "partial")');
            $erp->having('due_amount > 0');
            $erp->order_by('t.transaction_date', 'DESC');

            return $erp->get()->result();
        } catch (Exception $e) {
            log_message('error', 'Error in get_milestone_dues: ' . $e->getMessage());
            return [];
        }
    }

}