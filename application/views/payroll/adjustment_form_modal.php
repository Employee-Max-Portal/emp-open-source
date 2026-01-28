<?php $currency_symbol = $global_config['currency_symbol']; ?>
<?php echo form_open('payroll/save_adjustment', array('id' => 'adjustment_form')); ?>
<input type="hidden" name="staff_id" value="<?= $staff['id']; ?>">
<input type="hidden" name="month" value="<?= $month; ?>">
<input type="hidden" name="year" value="<?= $year; ?>">
<input type="hidden" name="basic_salary" value="<?= $staff['basic_salary']; ?>">
<input type="hidden" name="allowances" id="allowances_data" value="">
<input type="hidden" name="deductions" id="deductions_data" value="">

<div class="panel-body">
    <div class="row mb-md">
        <div class="col-md-3">
            <center>
                <img class="img-thumbnail" width="100px" height="100px" src="<?= get_image_url('staff', $staff['photo']); ?>">
            </center>
        </div>
        <div class="col-md-9">
            <table class="table table-condensed">
                <tr><th><?= translate('name'); ?>:</th><td><?= $staff['name']; ?></td></tr>
                <tr><th><?= translate('designation'); ?>:</th><td><?= $staff['designation_name']; ?></td></tr>
                <tr><th><?= translate('salary_grade'); ?>:</th><td><?= $staff['template_name']; ?></td></tr>
                <tr><th><?= translate('basic_salary'); ?>:</th><td><?= currencyFormat($staff['basic_salary']); ?></td></tr>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <section class="panel">
                <header class="panel-heading"><h4 class="panel-title"><?= translate('allowances'); ?></h4></header>
                <div class="panel-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th><?= translate('name'); ?></th>
                                <th class="text-right"><?= translate('amount'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_allowance = 0;
                            if (isset($adjustment_allowances) && !empty($adjustment_allowances)) {
                                $allowances = $adjustment_allowances;
                            } elseif (isset($staff['increment_components'])) {
                                $allowances = array_filter($staff['increment_components'], fn($i) => $i['type'] == 1);
                            } else {
                                $allowances = $this->payroll_model->get('salary_template_details', array('salary_template_id' => $staff['salary_template_id'], 'type' => 1));
                            }
                            
                            if (count($allowances)) {
                                foreach ($allowances as $allowance):
                                    $total_allowance += floatval($allowance['amount']);
                            ?>
                                <tr>
                                    <td><?= $allowance['name']; ?></td>
                                    <td class="text-right"><?= currencyFormat($allowance['amount']); ?></td>
                                </tr>
                            <?php endforeach; } else {
                                echo '<tr><td colspan="2"><h5 class="text-danger text-center">' . translate('no_information_available') . '</h5></td></tr>';
                            } ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="col-md-6">
            <section class="panel">
                <header class="panel-heading"><h4 class="panel-title"><?= translate('deductions'); ?></h4></header>
                <div class="panel-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th><?= translate('name'); ?></th>
                                <th class="text-right"><?= translate('amount'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_deduction = 0;
                            $advance_salary = $staff['advance_amount'];
                            
                            if (isset($adjustment_deductions) && !empty($adjustment_deductions)) {
                                $deductions = $adjustment_deductions;
                            } else {
                                $deductions = $this->payroll_model->get('salary_template_details', array('salary_template_id' => $staff['salary_template_id'], 'type' => 2));
                            }
                            
                            if (count($deductions)) {
                                foreach ($deductions as $deduction):
                                    $total_deduction += floatval($deduction['amount']);
                            ?>
                                <tr>
                                    <td><?= $deduction['name']; ?></td>
                                    <td class="text-right"><?= currencyFormat($deduction['amount']); ?></td>
                                </tr>
                            <?php endforeach; }
                            
                            if (!empty($advance_salary)) {
                                $total_deduction += $advance_salary;
                                echo '<tr><td>Advance Salary</td><td class="text-right">' . currencyFormat($advance_salary) . '</td></tr>';
                            }
                            ?>
                        </tbody>
                        <tbody id="deduction_items_adj"></tbody>
                        <tfoot>
                            <tr>
                                <th><?= translate('total'); ?></th>
                                <th class="text-right" id="deduction_total_display_adj"><?= currencyFormat($total_deduction); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <div class="form-group">
        <label><?= translate('penalties'); ?></label>
        <table class="table table-bordered" id="penalties_table_adj">
            <thead>
                <tr>
                    <th><?= translate('type'); ?></th>
                    <th><?= translate('value'); ?></th>
                    <th><?= translate('action'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (isset($adjustment_penalties) && !empty($adjustment_penalties)) {
                    $idx = 0;
                    foreach ($adjustment_penalties as $penalty) {
                        if (empty($penalty['type'])) continue;
                        $type_label = $penalty['type'];
                        if ($penalty['type'] == 'attendance') $type_label = translate('attendance');
                        elseif ($penalty['type'] == 'absent') $type_label = translate('absent');
                        elseif ($penalty['type'] == 'unpaid_leave') $type_label = translate('unpaid_leaves');
                        elseif ($penalty['type'] == 'rules') $type_label = translate('rules_violation');
                        elseif ($penalty['type'] == 'others') $type_label = $penalty['custom_name'] ?: translate('others');
                ?>
                    <tr data-index="<?= $idx ?>">
                        <td>
                            <select class="form-control penalty_type_adj" name="penalty[<?= $idx ?>][type]">
                                <option value="<?= $penalty['type'] ?>" selected><?= $type_label ?></option>
                                <option value="attendance"><?= translate('attendance'); ?></option>
                                <option value="absent"><?= translate('absent'); ?></option>
                                <option value="unpaid_leave"><?= translate('unpaid_leaves'); ?></option>
                                <option value="rules"><?= translate('rules_violation'); ?></option>
                                <option value="others"><?= translate('others'); ?></option>
                            </select>
                            <input type="text" class="form-control mt-1 penalty_custom_name_adj" name="penalty[<?= $idx ?>][custom_name]" placeholder="<?= translate('enter_custom_name'); ?>" value="<?= $penalty['custom_name'] ?? '' ?>" style="<?= $penalty['type'] == 'others' ? '' : 'display:none;' ?>" />
                        </td>
                        <td><input type="number" class="form-control penalty_value_adj" name="penalty[<?= $idx ?>][value]" min="0" value="<?= $penalty['value'] ?>" /></td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-penalty-adj"><i class="fas fa-trash-alt"></i></button></td>
                    </tr>
                <?php 
                        $idx++;
                    }
                } else {
                ?>
                    <?php if (!empty($staff['late_count']) && $staff['late_count'] > 0): ?>
                    <tr data-index="0">
                        <td>
                            <select class="form-control penalty_type_adj" name="penalty[0][type]">
                                <option value="attendance" selected><?= translate('attendance'); ?></option>
                            </select>
                            <input type="hidden" class="penalty_custom_name_adj" name="penalty[0][custom_name]" value="" />
                        </td>
                        <td><input type="number" class="form-control penalty_value_adj" name="penalty[0][value]" min="0" value="<?= $staff['late_count']; ?>" /></td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-penalty-adj"><i class="fas fa-trash-alt"></i></button></td>
                    </tr>
                    <?php endif; ?>
                    
                    <?php if (!empty($staff['absent_count']) && $staff['absent_count'] > 0): ?>
                    <tr data-index="1">
                        <td>
                            <select class="form-control penalty_type_adj" name="penalty[1][type]">
                                <option value="absent" selected><?= translate('absent'); ?></option>
                            </select>
                            <input type="hidden" class="penalty_custom_name_adj" name="penalty[1][custom_name]" value="" />
                        </td>
                        <td><input type="number" class="form-control penalty_value_adj" name="penalty[1][value]" min="0" value="<?= $staff['absent_count']; ?>" /></td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-penalty-adj"><i class="fas fa-trash-alt"></i></button></td>
                    </tr>
                    <?php endif; ?>
                    
                    <?php if (!empty($staff['unpaid_leaves']) && $staff['unpaid_leaves'] > 0): ?>
                    <tr data-index="2">
                        <td>
                            <select class="form-control penalty_type_adj" name="penalty[2][type]">
                                <option value="unpaid_leave" selected><?= translate('unpaid_leaves'); ?></option>
                            </select>
                            <input type="hidden" class="penalty_custom_name_adj" name="penalty[2][custom_name]" value="" />
                        </td>
                        <td><input type="number" class="form-control penalty_value_adj" name="penalty[2][value]" min="0" value="<?= $staff['unpaid_leaves']; ?>" /></td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-penalty-adj"><i class="fas fa-trash-alt"></i></button></td>
                    </tr>
                    <?php endif; ?>
                <?php } ?>
            </tbody>
        </table>
        <button type="button" class="btn btn-sm btn-default" id="add_penalty_btn_adj"><i class="fas fa-plus"></i> <?= translate('add_penalty'); ?></button>
    </div>

    <div class="form-group">
        <label><?= translate('total_allowance'); ?></label>
        <input type="number" class="form-control" name="total_allowance" id="total_allowance_adj" value="<?= isset($adjustment) ? $adjustment->total_allowance : $total_allowance; ?>" readonly />
    </div>

    <div class="form-group">
        <label><?= translate('total_deduction'); ?></label>
        <input type="number" class="form-control" name="total_deduction" id="total_deduction_adj" value="<?= isset($adjustment) ? $adjustment->total_deduction : $total_deduction; ?>" readonly />
    </div>

    <div class="form-group">
        <label><?= translate('net_salary'); ?></label>
        <input type="text" class="form-control" name="net_salary" id="net_salary_adj" value="<?= isset($adjustment) ? $adjustment->net_salary : ($staff['basic_salary'] + $total_allowance - $total_deduction); ?>" readonly />
    </div>

    <div class="text-right">
        <button type="button" class="btn btn-default modal-dismiss"><?= translate('close'); ?></button>
        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> <?= translate('save_adjustment'); ?></button>
    </div>
</div>
<?php echo form_close(); ?>

<script>
$(document).ready(function() {
    const base_allowance_adj = <?= floatval($total_allowance); ?>;
    const base_deduction_adj = <?= floatval($total_deduction); ?>;
    const base_net_salary_adj = <?= floatval($staff['basic_salary'] + $total_allowance - $total_deduction); ?>;
    let penaltyIndex_adj = <?= isset($adjustment_penalties) ? count($adjustment_penalties) : 3 ?>;

    const allowancesData = <?= json_encode($allowances); ?>;
    $('#allowances_data').val(JSON.stringify(allowancesData));

    const deductionsData = <?= json_encode($deductions); ?>;
    $('#deductions_data').val(JSON.stringify(deductionsData));

    function getPenaltyRowAdj(index) {
        return `
        <tr data-index="${index}">
            <td>
                <select class="form-control penalty_type_adj" name="penalty[${index}][type]">
                    <option value=""><?= translate('select'); ?></option>
                    <option value="attendance"><?= translate('attendance'); ?></option>
                    <option value="rules"><?= translate('rules_violation'); ?></option>
                    <option value="others"><?= translate('others'); ?></option>
                </select>
                <input type="text" class="form-control mt-1 penalty_custom_name_adj" name="penalty[${index}][custom_name]" placeholder="<?= translate('enter_custom_name'); ?>" style="display:none;" />
            </td>
            <td><input type="number" class="form-control penalty_value_adj" name="penalty[${index}][value]" min="0" value="0" /></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-penalty-adj"><i class="fas fa-trash-alt"></i></button></td>
        </tr>`;
    }

    function recalculateSalaryAdj() {
        let penalty_amount = 0;
        let deductionHTML = '';
        const currencySymbol = "<?= $currency_symbol ?>";

        $('#penalties_table_adj tbody tr').each(function() {
            let type = $(this).find('.penalty_type_adj').val();
            let value = parseFloat($(this).find('.penalty_value_adj').val()) || 0;
            let customName = $(this).find('.penalty_custom_name_adj').val();
            let label = '';

            if (type === 'absent') {
                let absentCount = parseInt(value);
                let base_salary = <?= floatval($staff['basic_salary']); ?>;
                let calculated = absentCount * (base_salary / 30);
                penalty_amount += calculated;
                let dayLabel = absentCount === 1 ? '<?= translate('day'); ?>' : '<?= translate('days'); ?>';
                label = '<?= translate('absent_penalty'); ?> - ' + absentCount + ' ' + dayLabel;
                deductionHTML += `<tr><td>${label}</td><td class="text-right">${currencySymbol + calculated.toFixed(2)}</td></tr>`;
            } else if (type === 'unpaid_leave') {
                let unpaidCount = parseInt(value);
                let base_salary = <?= floatval($staff['basic_salary']); ?>;
                let calculated = unpaidCount * (base_salary / 30);
                penalty_amount += calculated;
                let dayLabel = unpaidCount === 1 ? '<?= translate('day'); ?>' : '<?= translate('days'); ?>';
                label = '<?= translate('unpaid_leave'); ?> - ' + unpaidCount + ' ' + dayLabel;
                deductionHTML += `<tr><td>${label}</td><td class="text-right">${currencySymbol + calculated.toFixed(2)}</td></tr>`;
            } else if (type === 'attendance') {
                let penalty_count = Math.floor(value / 3);
                let base_salary = <?= floatval($staff['basic_salary']); ?>;
                let calculated = penalty_count * (base_salary / 30);
                penalty_amount += calculated;
                let lateCount = parseInt(value);
                let dayLabel = lateCount === 1 ? '<?= translate('day'); ?>' : '<?= translate('days'); ?>';
                label = '<?= translate('late_attendance'); ?> - ' + lateCount + ' ' + dayLabel;
                deductionHTML += `<tr><td>${label}</td><td class="text-right">${currencySymbol + calculated.toFixed(2)}</td></tr>`;
            } else if (type === 'rules') {
                penalty_amount += value;
                label = '<?= translate('rules_violation'); ?>';
                deductionHTML += `<tr><td>${label}</td><td class="text-right">${currencySymbol + value.toFixed(2)}</td></tr>`;
            } else if (type === 'others') {
                penalty_amount += value;
                label = customName || '<?= translate('others_penalty'); ?>';
                deductionHTML += `<tr><td>${label}</td><td class="text-right">${currencySymbol + value.toFixed(2)}</td></tr>`;
            }
        });

        let total_deduction = base_deduction_adj + penalty_amount;
        let net_salary = base_net_salary_adj - penalty_amount;

        $('#total_deduction_adj').val(total_deduction.toFixed(2));
        $('#net_salary_adj').val(net_salary.toFixed(2));
        $('#deduction_items_adj').html(deductionHTML);
        $('#deduction_total_display_adj').text(currencySymbol + total_deduction.toFixed(2));
    }

    $('#add_penalty_btn_adj').on('click', function() {
        $('#penalties_table_adj tbody').append(getPenaltyRowAdj(penaltyIndex_adj++));
    });

    $('#penalties_table_adj').on('click', '.remove-penalty-adj', function() {
        $(this).closest('tr').remove();
        recalculateSalaryAdj();
    });

    $('#penalties_table_adj').on('change', '.penalty_type_adj', function() {
        const type = $(this).val();
        const customInput = $(this).closest('td').find('.penalty_custom_name_adj');
        if (type === 'others') {
            customInput.show();
        } else {
            customInput.hide().val('');
        }
        recalculateSalaryAdj();
    });

    $('#penalties_table_adj').on('input change', '.penalty_value_adj, .penalty_custom_name_adj', function() {
        recalculateSalaryAdj();
    });

    recalculateSalaryAdj();
});
</script>
