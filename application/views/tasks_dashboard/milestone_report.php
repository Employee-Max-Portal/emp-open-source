<?php
// Get milestone financial data
$income_data = $this->cashbook_model->get_milestone_income_details($milestone_id);
$expense_data = $this->cashbook_model->get_milestone_expense_details($milestone_id);
$due_data = $this->cashbook_model->get_milestone_dues($milestone_id);

// Get indirect costs
$total_spent_hours = $this->cashbook_model->get_milestone_indirect_costs($milestone_id);
$cost_per_hour = $global_config['cost_per_hour'] ?? 0;
$indirect_cost_amount = $total_spent_hours * $cost_per_hour;

// Filter out zero amount entries
$income_data = array_values(array_filter($income_data, function($income) {
    return $income->amount > 0;
}));
$expense_data = array_values(array_filter($expense_data, function($expense) {
    return $expense->amount > 0;
}));

$total_income = array_sum(array_column($income_data, 'amount'));
$total_expense = array_sum(array_column($expense_data, 'amount')) + $indirect_cost_amount;
$balance = $total_income - $total_expense;
$label = $balance >= 0 ? 'Net Profit' : 'Net Loss';
$color = $balance >= 0 ? '#dff0d8' : '#f2dede';

// Number to words function
function numberToWords($number) {
    $number = (int) $number;
    if ($number == 0) return 'Zero';
    
    $ones = array('', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen');
    $tens = array('', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety');
    
    if ($number < 20) {
        return $ones[$number];
    } elseif ($number < 100) {
        return $tens[intval($number / 10)] . ($number % 10 != 0 ? ' ' . $ones[$number % 10] : '');
    } elseif ($number < 1000) {
        return $ones[intval($number / 100)] . ' Hundred' . ($number % 100 != 0 ? ' ' . numberToWords($number % 100) : '');
    } elseif ($number < 100000) {
        return numberToWords(intval($number / 1000)) . ' Thousand' . ($number % 1000 != 0 ? ' ' . numberToWords($number % 1000) : '');
    } elseif ($number < 10000000) {
        return numberToWords(intval($number / 100000)) . ' Lakh' . ($number % 100000 != 0 ? ' ' . numberToWords($number % 100000) : '');
    } else {
        return numberToWords(intval($number / 10000000)) . ' Crore' . ($number % 10000000 != 0 ? ' ' . numberToWords($number % 10000000) : '');
    }
}

$amount_in_words = numberToWords(abs($balance)) . ' Taka Only';
?>

<section class="panel">
	<header class="panel-heading">
		<div class="row">
			<div class="col-md-6 text-left">
				<h4>Milestone Financial Report</h4>
			</div>
			<div class="col-md-6 text-right"  id="print-button">
				<button class="btn btn-default btn-sm" onclick="printReport()"><i class="fas fa-print"></i> Print</button>
				<button class="btn btn-success btn-sm" onclick="exportExcel()"><i class="fas fa-file-excel"></i> Excel</button>
			</div>
		</div>
	</header>
	<div class="panel-body" id="print-section">
		<header class="heading">
			<h4 class="text-center">Milestone Financial Report</h4>
			<h5 class="text-center"><?= htmlspecialchars($milestone['title']) ?></h5>
		</header>
		<div class="table-responsive" id="report-table-container">
			<table class="table table-bordered table-condensed table-hover" id="report-table">
				<thead>
					<tr style="background-color: #e5e5e5;">
						<th class="text-center" width="40%">Income</th>
						<th class="text-center" width="10%">Amount in Taka</th>
						<th class="text-center" width="40%">Expenses</th>
						<th class="text-center" width="10%">Amount in Taka</th>
					</tr>
				</thead>
				<tbody>
					<?php 
					// Add indirect cost as an expense entry
					if ($indirect_cost_amount > 0) {
						$indirect_expense = (object) [
							'reason' => 'Indirect Cost (' . round($total_spent_hours, 1) . ' hours worked)',
							'amount' => $indirect_cost_amount
						];
						$expense_data[] = $indirect_expense;
					}
					
					$max_rows = max(count($income_data), count($expense_data));
					
					for ($i = 0; $i < $max_rows; $i++) {
						$income = isset($income_data[$i]) ? $income_data[$i] : null;
						$expense = isset($expense_data[$i]) ? $expense_data[$i] : null;
					?>
					<tr>
						<td><?= $income ? htmlspecialchars($income->customer_name) . '- Invoice: ' . htmlspecialchars($income->invoice_no) : '' ?></td>
						<td class="text-right"><?= $income ? number_format($income->amount, 2) : '' ?></td>
						<td><?= $expense ? htmlspecialchars($expense->reason) : '' ?></td>
						<td class="text-right"><?= $expense ? number_format($expense->amount, 2) : '' ?></td>
					</tr>
					<?php } ?>
					
					<?php if ($max_rows == 0): ?>
					<tr>
						<td colspan="4" class="text-center">No information available</td>
					</tr>
					<?php endif; ?>
				</tbody>
				<tfoot>
					<tr style="background-color: #f2dede; font-weight: bold;">
						<td class="text-right">Total Income</td>
						<td class="text-right"><?= number_format($total_income, 2) ?></td>
						<td class="text-right">Total Expenses</td>
						<td class="text-right"><?= number_format($total_expense, 2) ?></td>
					</tr>
					<tr style="background-color: <?= $color ?>; font-weight: bold;">
						<td colspan="2" style="border: none;"></td>
						<td class="text-right"><?= $label ?></td>
						<td class="text-right"><?= number_format(abs($balance), 2) ?></td>
					</tr>
					<tr style="background-color: #f9f9f9; font-weight: bold;">
						<td colspan="2" class="text-right"><?= $label ?> in Words:</td>
						<td colspan="2"><?= $amount_in_words ?></td>
					</tr>
				</tfoot>
			</table>
		</div>
		
		<!-- Outstanding Dues Section -->
		<?php if (!empty($due_data)): ?>
		<div style="margin-top: 30px;">
			<h5><strong>Outstanding Dues</strong></h5>
			<div class="table-responsive">
				<table class="table table-bordered table-condensed">
					<thead>
						<tr style="background-color: #e5e5e5;">
							<th>Date</th>
							<th>Customer</th>
							<th>Invoice</th>
							<th class="text-right">Total Amount</th>
							<th class="text-right">Paid Amount</th>
							<th class="text-right">Due Amount</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($due_data as $due): ?>
						<tr>
							<td><?= date('d-m-Y', strtotime($due->date)) ?></td>
							<td><?= htmlspecialchars($due->customer_name) ?></td>
							<td><?= htmlspecialchars($due->invoice_no) ?></td>
							<td class="text-right"><?= number_format($due->total_amount, 2) ?></td>
							<td class="text-right"><?= number_format($due->paid_amount, 2) ?></td>
							<td class="text-right"><?= number_format($due->due_amount, 2) ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
					<tfoot>
						<tr style="background-color: #f2dede; font-weight: bold;">
							<td colspan="5" class="text-right">Total Outstanding Dues</td>
							<td class="text-right"><?= number_format(array_sum(array_column($due_data, 'due_amount')), 2) ?></td>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
		<?php endif; ?>
	</div>
	<footer class="panel-footer">
		<div class="row">
			<div class="col-md-12 text-right">
				<button class="btn btn-default modal-dismiss"><?=translate('close')?></button>
			</div>
		</div>
	</footer>
</section>


<script type="text/javascript">
    function printReport() {
        var printContents = document.getElementById('print-section').innerHTML;
        var originalContents = document.body.innerHTML;
        
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        window.location.reload();
    }

    function exportExcel() {
		
        var dt = new Date();
        var day = dt.getDate().toString().padStart(2, '0');
        var month = (dt.getMonth() + 1).toString().padStart(2, '0');
        var year = dt.getFullYear();
        var hour = dt.getHours().toString().padStart(2, '0');
        var mins = dt.getMinutes().toString().padStart(2, '0');
        var postfix = day + "." + month + "." + year + "_" + hour + "." + mins;
        
        var excelContent = createExcelContent();
        
        var blob = new Blob([excelContent], {
            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        });
        
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'Milestone_Financial_Report_' + postfix + '.xls';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }
    
    function createExcelContent() {
        var printSection = document.getElementById('print-section');
        var html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        html += '<head><meta charset="utf-8"><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        html += '<style>table { border-collapse: collapse; } th, td { border: 1px solid #000; padding: 5px; } .text-right { text-align: right; } .text-center { text-align: center; }</style>';
        html += '</head><body>';
        html += printSection.innerHTML;
        html += '</body></html>';
        return html;
    }
</script>