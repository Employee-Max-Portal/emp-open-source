<?php
// ✅ Basic Info
$employee_name = $staff['name'];
$employee_id = $staff['staff_id'];
$designation = $staff['designation_name'];
$department = $staff['department_name'];
$joining_date = date("d-m-Y", strtotime($staff['joining_date']));
$salary_month = date("F, Y", strtotime($year . '-' . $month . '-01'));

// ✅ Salary Components (basic + allowances)
$salary_breakup = [];

$basic_salary = (float) $staff['basic_salary'];
$salary_breakup[] = ['name' => 'Basic', 'amount' => $basic_salary];

// ✅ Components: fallback to template_components if no increment
$components = [];

if (isset($staff['increment_components']) && is_array($staff['increment_components'])) {
    $components = $staff['increment_components'];
} else {
    $components = $this->payroll_model->get('salary_template_details', array('salary_template_id' => $staff['salary_template_id'], 'type' => 1));
}

// ✅ Add allowances from components
foreach ($components as $component) {
    if ((int)$component['type'] === 1) {
        $salary_breakup[] = [
            'name' => $component['name'],
            'amount' => (float)$component['amount']
        ];
    }
}

// ✅ Earnings
$earnings = array_sum(array_column($salary_breakup, 'amount'));

// ✅ Deductions: Advance, Late Penalty, Absent Penalty
$advance = (float)($staff['advance_amount'] ?? 0);
$late_count = (int)($staff['late_count'] ?? 0);
$absent_count = (int)($staff['absent_count'] ?? 0);

// ✅ Use adjusted basic for penalty calculations
$penalty_base = $basic_salary;
$penalty_per_day = $penalty_base / 30;

$late_penalty = ($late_count >= 3) ? floor($late_count / 3) * $penalty_per_day : 0;
$absent_penalty = $absent_count * $penalty_per_day;

$deductions = [];

if ($advance > 0) {
    $deductions[] = ['name' => 'Advance Salary', 'amount' => $advance];
}
if ($late_penalty > 0) {
    $deductions[] = ['name' => "Late Penalty ({$late_count} Late)", 'amount' => $late_penalty];
}
if ($absent_penalty > 0) {
    $deductions[] = ['name' => "Absent Deduction ({$absent_count} Days)", 'amount' => $absent_penalty];
}

$total_deduction = array_sum(array_column($deductions, 'amount'));

// ✅ Final Net Pay
//$net_remuneration = $earnings - $total_deduction;
$net_remuneration = $earnings;
 
?>

    <style>
        h2 { text-align: center; text-decoration: underline; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 8px 12px; border: 1px solid #ccc; }
        .no-border { border: none !important; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .signature { margin-top: 60px; }
    </style>

<div class="certificate">
    <h2>Salary Certificate</h2>
    <p>
        This is to certify that <strong><?php echo $employee_name; ?></strong> has been working in Your Business Name since 
        <strong><?php echo $joining_date; ?></strong>. He is Active employee of this company under <strong><?php echo $employee_id; ?></strong>. 
        As per our service rule / term of employment, there is no fixed timing of retirement. At present he is serving our company as a 
        <strong><?php echo $designation; ?></strong>, department of <?php echo $department; ?>. His gross salary is below for the month 
        <strong><?php echo $salary_month; ?></strong>.
    </p>

	   <table>
		<thead>
			<tr>
				<th colspan="2">Salary Breakup</th>
				<th class="text-right">Amount</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($salary_breakup as $row): ?>
			<tr>
				<td colspan="2"><?= html_escape($row['name']); ?></td>
				<td class="text-right"><?= number_format($row['amount'], 2) . $global_config['currency_symbol']; ; ?></td>
			</tr>
			<?php endforeach; ?>

			<tr>
				<td colspan="2" class="text-right"><strong>Earnings :</strong></td>
				<td class="text-right"><strong><?= number_format($earnings, 2) . $global_config['currency_symbol'];  ?></strong></td>
			</tr>

			<tr>
				<td colspan="2" class="text-right"><strong>Deductions :</strong></td>
				<td class="text-right"><strong><?= number_format(0, 2) . $global_config['currency_symbol']; ?></strong></td>
			</tr>

			<tr>
				<td colspan="2" class="text-right"><strong>Net Remuneration :</strong></td>
				<td class="text-right"><strong><?= number_format($net_remuneration, 2) . $global_config['currency_symbol']; ?></strong></td>
			</tr>
		</tbody>
	</table>


    <p style="margin-top: 40px;">
        We are issuing this letter on the specific request of our employee without accepting any liability on behalf of this letter or part of this letter on our company.
    </p>

    <p>Thanks and Regards,</p>

    <div class="signature">
        ---------------------------<br>
        Signature & Stamp
    </div>
</div>

