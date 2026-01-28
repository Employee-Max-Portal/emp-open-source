<?php 
$currency_symbol = $global_config['currency_symbol'];

// Use adjusted salary values if available
if (isset($adjustment)) {
    $basic_salary = $adjustment->basic_salary;
    $total_allowance = $adjustment->total_allowance;
    $total_deduction = $adjustment->total_deduction;
    $net_salary = $adjustment->net_salary;
} else {
    // Calculate from template
    $basic_salary = $staff['basic_salary'];
    $total_allowance = 0;
    $total_deduction = 0;
    
    $allowances = $this->payroll_model->get('salary_template_details', array('salary_template_id' => $staff['salary_template_id'], 'type' => 1));
    foreach ($allowances as $allowance) {
        $total_allowance += floatval($allowance['amount']);
    }
    
    $deductions = $this->payroll_model->get('salary_template_details', array('salary_template_id' => $staff['salary_template_id'], 'type' => 2));
    foreach ($deductions as $deduction) {
        $total_deduction += floatval($deduction['amount']);
    }
    
    $advance_salary = isset($staff['advance_amount']) ? $staff['advance_amount'] : 0;
    if (!empty($advance_salary)) {
        $total_deduction += $advance_salary;
    }
    
    $net_salary = $basic_salary + $total_allowance - $total_deduction;
}
?>

<form id="payment_form" method="post">
	<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />
    <input type="hidden" name="branch_id" value="<?=$staff['branch_id']; ?>">
    <input type="hidden" name="staff_id" value="<?=$staff['id']; ?>">
    <input type="hidden" name="basic_salary" value="<?=$basic_salary; ?>">
    <input type="hidden" name="salary_template_id" value="<?=$staff['salary_template_id']; ?>">
    <input type="hidden" name="month" value="<?=$month; ?>">
    <input type="hidden" name="year" value="<?=$year; ?>">
    <input type="hidden" name="total_allowance" value="<?=$total_allowance; ?>">
    <input type="hidden" name="total_deduction" value="<?=$total_deduction; ?>">
    <input type="hidden" name="net_salary" value="<?=$net_salary; ?>">
    <input type="hidden" name="advance_salary" value="<?=$advance_salary; ?>">

    <div class="row">
        <div class="col-md-6">
            <h5><?= translate('employee_details') ?></h5>
            <table class="table table-condensed">
                <tr>
                    <th><?= translate('name') ?>:</th>
                    <td><?= $staff['name'] ?></td>
                </tr>
                <tr>
                    <th><?= translate('designation') ?>:</th>
                    <td><?= $staff['designation_name'] ?></td>
                </tr>
                <tr>
                    <th><?= translate('basic_salary') ?>:</th>
                    <td><?= currencyFormat($basic_salary) ?></td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <h5><?= translate('salary_summary') ?></h5>
            <table class="table table-condensed">
                <tr>
                    <th><?= translate('total_allowance') ?>:</th>
                    <td><?= currencyFormat($total_allowance) ?></td>
                </tr>
                <tr>
                    <th><?= translate('total_deduction') ?>:</th>
                    <td><?= currencyFormat($total_deduction) ?></td>
                </tr>
                <?php if ($advance_salary > 0): ?>
                <tr>
                    <th><?= translate('advance_salary') ?>:</th>
                    <td><?= currencyFormat($advance_salary) ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th><?= translate('net_salary') ?>:</th>
                    <td><strong><?= currencyFormat($net_salary) ?></strong></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label"><?= translate('payment_method') ?> <span class="required">*</span></label>
        <select name="pay_via" class="form-control" required data-plugin-selectTwo data-width="100%" data-minimum-results-for-search="Infinity">
            <option value=""><?= translate('select') ?></option>
            <?php 
            $account_types = $this->db->get('cashbook_accounts')->result_array();
            foreach($account_types as $account): ?>
                <option value="<?= $account['id'] ?>"><?= ucfirst($account['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php
    $links = $this->payroll_model->get('transactions_links', array('branch_id' => $staff['branch_id']), true);
    if ($links['status'] == 1) {
    ?>
        <div class="form-group">
            <label class="control-label"><?php echo translate('account'); ?> <span class="required">*</span></label>
            <?php
                $accounts_list = $this->app_lib->getSelectByBranch('accounts', $staff['branch_id']);
                echo form_dropdown("account_id", $accounts_list, $links['expense'], "class='form-control' id='account_id' required data-plugin-selectTwo data-width='100%'");
            ?>
        </div>
    <?php } ?>

    <div class="form-group">
        <label class="control-label"><?= translate('remarks') ?></label>
        <textarea class="form-control" name="remarks" rows="2" maxlength="50"></textarea>
    </div>

    <div class="text-right">
        <button type="button" class="btn btn-default" onclick="$.magnificPopup.close();"><?= translate('cancel') ?></button>
        <button type="submit" class="btn btn-success"><?= translate('pay_now') ?></button>
    </div>
</form>