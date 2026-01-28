<style>
a {
    text-decoration: none!important;
}
@media print {
    a {
        text-decoration: none !important;
    }
}
</style>
<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <header class="panel-heading">
                <h4 class="panel-title"><?=translate('filter_options')?></h4>
            </header>
            <?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-4 col-md-offset-4">
                        <div class="form-group">
                            <label class="control-label"><?=translate('month')?></label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fas fa-calendar-check"></i></span>
                                <input type="text" class="form-control monthpicker" name="month_year" value="<?= $selected_month ?>" required readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="panel-footer">
                <div class="row">
                    <div class="col-md-12 text-right">
                        <button type="submit" name="search" value="1" class="btn btn-primary">
                            <i class="fas fa-filter"></i> <?= translate('filter') ?>
                        </button>
                    </div>
                </div>
            </footer>
            <?php echo form_close(); ?>
        </section>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <section class="panel" id="print-section">

			<header class="panel-heading">
				<div class="text-right">
                        <button class="btn btn-info btn-sm" onclick="showDetailedReport()"><i class="fas fa-list"></i> Detailed Report</button>
                        <button class="btn btn-default btn-sm" onclick="printReport()"><i class="fas fa-print"></i> Print</button>
                        <button class="btn btn-success btn-sm" onclick="exportExcel()"><i class="fas fa-file-excel"></i> Excel</button>
                    </div>
                <h4 class="text-center">Profit & Loss Statement</h4>
                <h5 class="text-center">For the Month <?= $month_name ?></h5>
            </header>
            <div class="panel-body">
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
                            $max_rows = max(count($incomes), count($expenses));
                            $total_income = 0;
                            $total_expense = 0;
                            
                            // Calculate totals
                            foreach ($incomes as $income) {
                                $total_income += $income->amount;
                            }
                            foreach ($expenses as $expense) {
                                $total_expense += $expense->amount;
                            }
                            
                            for ($i = 0; $i < $max_rows; $i++) {
                                $income = isset($incomes[$i]) ? $incomes[$i] : null;
                                $expense = isset($expenses[$i]) ? $expenses[$i] : null;
                            ?>
                            <tr>
                                <td><?php 
                                    if ($income) {
                                        if (isset($income->income_type) && $income->income_type == 'sales_revenue') {
                                            echo '<a href="javascript:void(0)" onclick="showSalesRevenueDetails()" style="color: #337ab7; text-decoration: underline;">' . ($income->description ?? '') . '</a>';
                                        } elseif (isset($income->income_type) && $income->income_type == 'reference_type') {
                                            echo '<a href="javascript:void(0)" onclick="showIncomeDetails(\'' . $income->reference_type . '\')" style="color: #337ab7; text-decoration: underline;">' . ($income->description ?? '') . '</a>';
                                        } else {
                                            echo ($income->description ?? '');
                                        }
                                    }
                                ?></td>
                                <td class="text-right"><?= $income ? number_format($income->amount, 2) : '' ?></td>
                                <td><?php 
                                    if ($expense) {
                                        if (isset($expense->expense_type) && $expense->expense_type == 'fund_category') {
                                            echo '<a href="javascript:void(0)" onclick="showFundCategoryDetails(' . $expense->category_id . ')" style="color: #337ab7; text-decoration: underline;">' . ($expense->description ?? '') . '</a>';
                                        } else {
                                            echo '<a href="javascript:void(0)" onclick="showExpenseDetails(\'' . $expense->reference_type . '\')" style="color: #337ab7; text-decoration: underline;">' . ($expense->description ?? '') . '</a>';
                                        }
                                    }
                                ?></td>
                                <td class="text-right"><?= $expense ? number_format($expense->amount, 2) : '' ?></td>
                            </tr>
                            <?php } ?>
                            
                            <?php if ($max_rows == 0): ?>
                            <tr>
                                <td colspan="4" class="text-center"><?= translate('no_information_available') ?></td>
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
                            <?php 
                            $balance = $total_income - $total_expense;
                            $label = $balance >= 0 ? 'Net Profit' : 'Net Loss';
                            $color = $balance >= 0 ? '#dff0d8' : '#f2dede'; // Green for profit, Red for loss
                            
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
                            <tr style="background-color: <?= $color ?>; font-weight: bold;">
                                <td colspan="2" style="border: none;"></td>
                                <td class="text-right"><?= $label ?></td>
                                <td class="text-right"><?= number_format(abs($balance), 2) ?></td>
                            </tr>
                            <tr style="background-color: #f9f9f9; font-weight: bold;">
                                <td colspan="2" class="text-right"><?= $label ?> in Words:</td>
                                <td colspan="2" ><?= $amount_in_words ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- Detailed Report Modal -->
<div class="modal fade" id="detailedReportModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="width: 90%; max-width: none;">
        <div class="modal-content">
            <div class="modal-header text-center">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Profit & Loss Statement</h4>
                <div class="text-right" style="position: absolute; top: 10px; right: 50px;">
                    <button class="btn btn-default btn-xs" onclick="printDetailedReport()"><i class="fas fa-print"></i> Print</button>
                    <button class="btn btn-success btn-xs" onclick="exportDetailedExcel()"><i class="fas fa-file-excel"></i> Excel</button>
                </div>
            </div>
            <div class="panel-body">
                <div class="table-responsive" id="detailed-report-container">
                    <table class="table table-bordered table-condensed" id="detailed-report-table">
                        <thead>
                            <tr style="background-color: #e5e5e5;">
                                <th class="text-center" width="40%">Income</th>
                                <th class="text-center" width="10%">Amount in Taka</th>
                                <th class="text-center" width="40%">Expenses</th>
                                <th class="text-center" width="10%">Amount in Taka</th>
                            </tr>
                        </thead>
                        <tbody id="detailedReportBody">
                        </tbody>
                        <tfoot id="detailedReportFooter">
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="panel-footer modal-footer text-center">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Expense Category Details Modal -->
<div class="modal fade" id="expenseCategoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document" style="width: 80%; max-width: none;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="categoryModalTitle">Category Details</h4>
                <div class="text-right" style="position: absolute; top: 10px; right: 50px;">
                    <button class="btn btn-default btn-xs" onclick="printCategoryDetails()"><i class="fas fa-print"></i> Print</button>
                </div>
            </div>
            <div class="panel-body">
                <div class="table-responsive" id="category-details-container">
                    <table class="table table-bordered table-striped" id="category-details-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th class="text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody id="categoryDetailsBody">
                        </tbody>
                        <tfoot id="categoryDetailsFooter">
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="panel-footer modal-footer text-center">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $(".monthpicker").datepicker({
            format: "yyyy-mm",
            startView: "months", 
            minViewMode: "months",
            autoclose: true
        });
    });

     function printReport() {
        var printContents = document.getElementById('report-table-container').innerHTML;
        var originalContents = document.body.innerHTML;
        var header = '<h4 class="text-center">Profit & Loss Statement</h4><h5 class="text-center">For the Month <?= $month_name ?></h5><br>';

        // Remove anchor tags and keep only text content
        printContents = printContents.replace(/<a[^>]*>(.*?)<\/a>/g, '$1');
        
        document.body.innerHTML = header + printContents;
        window.print();
        document.body.innerHTML = originalContents;
        window.location.reload(); // Reload to restore event listeners
    }
    function exportExcel() {
        var dt = new Date();
        var day = dt.getDate().toString().padStart(2, '0');
        var month = (dt.getMonth() + 1).toString().padStart(2, '0');
        var year = dt.getFullYear();
        var hour = dt.getHours().toString().padStart(2, '0');
        var mins = dt.getMinutes().toString().padStart(2, '0');
        var postfix = day + "." + month + "." + year + "_" + hour + "." + mins;
        
        // Create Excel content with proper formatting
        var excelContent = createExcelContent();
        
        var blob = new Blob([excelContent], {
            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        });
        
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'Profit_Loss_Report_' + postfix + '.xls';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }
    
    function createExcelContent() {
        var table = document.getElementById('report-table');
        var html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        html += '<head><meta charset="utf-8"><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        html += '<style>table { border-collapse: collapse; } th, td { border: 1px solid #000; padding: 5px; } .text-right { text-align: right; } .text-center { text-align: center; }</style>';
        html += '</head><body>';
        html += '<h3 style="text-align: center;">Profit & Loss Statement</h3>';
        html += '<h4 style="text-align: center;">For the Month <?= $month_name ?></h4>';
        html += '<br>';
        html += table.outerHTML;
        html += '</body></html>';
        return html;
    }

    function showDetailedReport() {
        var monthYear = $('input[name="month_year"]').val();
        
        $.post('<?= base_url('cashbook/get_detailed_report') ?>', {
            month_year: monthYear
        }, function(response) {
            var data = JSON.parse(response);
            var tbody = $('#detailedReportBody');
            var tfoot = $('#detailedReportFooter');
            tbody.empty();
            tfoot.empty();
            
            // Update modal title with month info
            var monthName = new Date(monthYear + '-01').toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
            $('#detailedReportModal .modal-title').html('Profit & Loss Statement<br><small>For the Month ' + monthName + '</small>');
            
            var incomes = data.incomes || [];
            var expenses = data.expenses || [];
            var maxRows = Math.max(incomes.length, expenses.length);
            var totalIncome = 0;
            var totalExpense = 0;
            
            // Calculate totals
            incomes.forEach(function(income) {
                totalIncome += parseFloat(income.amount || 0);
            });
            expenses.forEach(function(expense) {
                totalExpense += parseFloat(expense.amount || 0);
            });
            
            // Build rows
            for (var i = 0; i < maxRows; i++) {
                var income = incomes[i] || null;
                var expense = expenses[i] || null;
                
                var row = '<tr>' +
                    '<td>' + (income ? (income.customer_name || income.description || '') : '') + '</td>' +
                    '<td class="text-right">' + (income ? parseFloat(income.amount).toLocaleString('en-US', {minimumFractionDigits: 2}) : '') + '</td>' +
                    '<td>' + (expense ? (expense.description || '') : '') + '</td>' +
                    '<td class="text-right">' + (expense ? parseFloat(expense.amount).toLocaleString('en-US', {minimumFractionDigits: 2}) : '') + '</td>' +
                    '</tr>';
                tbody.append(row);
            }
            
            if (maxRows === 0) {
                tbody.append('<tr><td colspan="4" class="text-center">No data available</td></tr>');
            }
            
            // Add totals footer
            var balance = totalIncome - totalExpense;
            var label = balance >= 0 ? 'Net Profit' : 'Net Loss';
            var color = balance >= 0 ? '#dff0d8' : '#f2dede';
            
            // Convert balance to words
            var balanceInWords = numberToWords(Math.abs(balance)) + ' Taka Only';
            
            tfoot.append(
                '<tr style="background-color: #f2dede; font-weight: bold;">' +
                '<td class="text-right">Total Income</td>' +
                '<td class="text-right">' + totalIncome.toLocaleString('en-US', {minimumFractionDigits: 2}) + '</td>' +
                '<td class="text-right">Total Expenses</td>' +
                '<td class="text-right">' + totalExpense.toLocaleString('en-US', {minimumFractionDigits: 2}) + '</td>' +
                '</tr>' +
                '<tr style="background-color: ' + color + '; font-weight: bold;">' +
                '<td colspan="2" style="border: none;"></td>' +
                '<td class="text-right">' + label + '</td>' +
                '<td class="text-right">' + Math.abs(balance).toLocaleString('en-US', {minimumFractionDigits: 2}) + '</td>' +
                '</tr>' +
                '<tr style="background-color: #f9f9f9; font-weight: bold;">' +
                '<td colspan="2" class="text-right">' + label + ' in Words:</td>' +
                '<td colspan="2">' + balanceInWords + '</td>' +
                '</tr>'
            );
            
            $('#detailedReportModal').modal('show');
        });
    }

    function showExpenseDetails(referenceType) {
        var monthYear = $('input[name="month_year"]').val();
        var categoryName = referenceType.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
        
        $('#categoryModalTitle').text(categoryName + ' Details');
        
        $.post('<?= base_url('cashbook/get_expense_category_details') ?>', {
            reference_type: referenceType,
            month_year: monthYear
        }, function(response) {
            var data = JSON.parse(response);
            var tbody = $('#categoryDetailsBody');
            var tfoot = $('#categoryDetailsFooter');
            tbody.empty();
            tfoot.empty();
            
            var total = 0;
            
            if (data.expenses && data.expenses.length > 0) {
                data.expenses.forEach(function(expense) {
                    total += parseFloat(expense.amount);
                    var row = '<tr>' +
                        '<td>' + new Date(expense.entry_date).toLocaleDateString() + '</td>' +
                        '<td>' + (expense.description || '') + '</td>' +
                        '<td class="text-right">' + parseFloat(expense.amount).toLocaleString('en-US', {minimumFractionDigits: 2}) + '</td>' +
                        '</tr>';
                    tbody.append(row);
                });
                
                tfoot.append(
                    '<tr style="background-color: #f2dede; font-weight: bold;">' +
                    '<td colspan="2" class="text-right">Total</td>' +
                    '<td class="text-right">' + total.toLocaleString('en-US', {minimumFractionDigits: 2}) + '</td>' +
                    '</tr>'
                );
            } else {
                tbody.append('<tr><td colspan="3" class="text-center">No data available</td></tr>');
            }
            
            $('#expenseCategoryModal').modal('show');
        });
    }

    function showFundCategoryDetails(categoryId) {
        var monthYear = $('input[name="month_year"]').val();
        
        $.post('<?= base_url('cashbook/get_fund_category_details') ?>', {
            category_id: categoryId,
            month_year: monthYear
        }, function(response) {
            var data = JSON.parse(response);
            var tbody = $('#categoryDetailsBody');
            var tfoot = $('#categoryDetailsFooter');
            tbody.empty();
            tfoot.empty();
            
            $('#categoryModalTitle').text(data.category_name + ' Details');
            
            var total = 0;
            
            if (data.expenses && data.expenses.length > 0) {
                data.expenses.forEach(function(expense) {
                    total += parseFloat(expense.amount);
                    var row = '<tr>' +
                        '<td>' + new Date(expense.entry_date).toLocaleDateString() + '</td>' +
                        '<td>' + (expense.description || '') + '</td>' +
                        '<td class="text-right">' + parseFloat(expense.amount).toLocaleString('en-US', {minimumFractionDigits: 2}) + '</td>' +
                        '</tr>';
                    tbody.append(row);
                });
                
                tfoot.append(
                    '<tr style="background-color: #f2dede; font-weight: bold;">' +
                    '<td colspan="2" class="text-right">Total</td>' +
                    '<td class="text-right">' + total.toLocaleString('en-US', {minimumFractionDigits: 2}) + '</td>' +
                    '</tr>'
                );
            } else {
                tbody.append('<tr><td colspan="3" class="text-center">No data available</td></tr>');
            }
            
            $('#expenseCategoryModal').modal('show');
        });
    }

    function showSalesRevenueDetails() {
        var monthYear = $('input[name="month_year"]').val();
        
        $('#categoryModalTitle').text('Sales Revenue Details');
        
        $.post('<?= base_url('cashbook/get_sales_revenue_details') ?>', {
            month_year: monthYear
        }, function(response) {
            var data = JSON.parse(response);
            var tbody = $('#categoryDetailsBody');
            var tfoot = $('#categoryDetailsFooter');
            tbody.empty();
            tfoot.empty();
            
            var total = 0;
            
            if (data.incomes && data.incomes.length > 0) {
                data.incomes.forEach(function(income) {
                    total += parseFloat(income.amount);
                    var row = '<tr>' +
                        '<td>' + new Date(income.entry_date).toLocaleDateString() + '</td>' +
                        '<td>' + (income.description || '') + '</td>' +
                        '<td class="text-right">' + parseFloat(income.amount).toLocaleString('en-US', {minimumFractionDigits: 2}) + '</td>' +
                        '</tr>';
                    tbody.append(row);
                });
                
                tfoot.append(
                    '<tr style="background-color: #dff0d8; font-weight: bold;">' +
                    '<td colspan="2" class="text-right">Total</td>' +
                    '<td class="text-right">' + total.toLocaleString('en-US', {minimumFractionDigits: 2}) + '</td>' +
                    '</tr>'
                );
            } else {
                tbody.append('<tr><td colspan="3" class="text-center">No data available</td></tr>');
            }
            
            $('#expenseCategoryModal').modal('show');
        });
    }

    function showIncomeDetails(referenceType) {
        var monthYear = $('input[name="month_year"]').val();
        var categoryName = referenceType.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
        
        $('#categoryModalTitle').text(categoryName + ' Details');
        
        $.post('<?= base_url('cashbook/get_income_type_details') ?>', {
            reference_type: referenceType,
            month_year: monthYear
        }, function(response) {
            var data = JSON.parse(response);
            var tbody = $('#categoryDetailsBody');
            var tfoot = $('#categoryDetailsFooter');
            tbody.empty();
            tfoot.empty();
            
            var total = 0;
            
            if (data.incomes && data.incomes.length > 0) {
                data.incomes.forEach(function(income) {
                    total += parseFloat(income.amount);
                    var row = '<tr>' +
                        '<td>' + new Date(income.entry_date).toLocaleDateString() + '</td>' +
                        '<td>' + (income.description || '') + '</td>' +
                        '<td class="text-right">' + parseFloat(income.amount).toLocaleString('en-US', {minimumFractionDigits: 2}) + '</td>' +
                        '</tr>';
                    tbody.append(row);
                });
                
                tfoot.append(
                    '<tr style="background-color: #dff0d8; font-weight: bold;">' +
                    '<td colspan="2" class="text-right">Total</td>' +
                    '<td class="text-right">' + total.toLocaleString('en-US', {minimumFractionDigits: 2}) + '</td>' +
                    '</tr>'
                );
            } else {
                tbody.append('<tr><td colspan="3" class="text-center">No data available</td></tr>');
            }
            
            $('#expenseCategoryModal').modal('show');
        });
    }
    
    function printCategoryDetails() {
        var printContents = document.getElementById('category-details-container').innerHTML;
        var originalContents = document.body.innerHTML;
        var categoryTitle = $('#categoryModalTitle').text();
        var monthYear = $('input[name="month_year"]').val();
        var monthName = new Date(monthYear + '-01').toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        
        var header = '<h4 class="text-center">' + categoryTitle + '</h4>' +
                     '<h5 class="text-center">For the Month ' + monthName + '</h5><br>';

        document.body.innerHTML = header + printContents;
        window.print();
        document.body.innerHTML = originalContents;
        window.location.reload();
    }

    function printDetailedReport() {
        var printContents = document.getElementById('detailed-report-container').innerHTML;
        var originalContents = document.body.innerHTML;
        var monthName = $('#detailedReportModal .modal-title small').text().replace('Profit & Loss Statement', '').trim();
        var header = '<h4 class="text-center">Profit & Loss Statement</h4><h5 class="text-center">' + monthName + '</h5><br>';

        document.body.innerHTML = header + printContents;
        window.print();
        document.body.innerHTML = originalContents;
        window.location.reload();
    }

    function numberToWords(number) {
        number = parseInt(number);
        if (number == 0) return 'Zero';
        
        var ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        var tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        
        if (number < 20) {
            return ones[number];
        } else if (number < 100) {
            return tens[Math.floor(number / 10)] + (number % 10 != 0 ? ' ' + ones[number % 10] : '');
        } else if (number < 1000) {
            return ones[Math.floor(number / 100)] + ' Hundred' + (number % 100 != 0 ? ' ' + numberToWords(number % 100) : '');
        } else if (number < 100000) {
            return numberToWords(Math.floor(number / 1000)) + ' Thousand' + (number % 1000 != 0 ? ' ' + numberToWords(number % 1000) : '');
        } else if (number < 10000000) {
            return numberToWords(Math.floor(number / 100000)) + ' Lakh' + (number % 100000 != 0 ? ' ' + numberToWords(number % 100000) : '');
        } else {
            return numberToWords(Math.floor(number / 10000000)) + ' Crore' + (number % 10000000 != 0 ? ' ' + numberToWords(number % 10000000) : '');
        }
    }

    function exportDetailedExcel() {
        var dt = new Date();
        var day = dt.getDate().toString().padStart(2, '0');
        var month = (dt.getMonth() + 1).toString().padStart(2, '0');
        var year = dt.getFullYear();
        var hour = dt.getHours().toString().padStart(2, '0');
        var mins = dt.getMinutes().toString().padStart(2, '0');
        var postfix = day + "." + month + "." + year + "_" + hour + "." + mins;
        
        var excelContent = createDetailedExcelContent();
        
        var blob = new Blob([excelContent], {
            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        });
        
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'Detailed_Profit_Loss_Report_' + postfix + '.xls';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }
    
    function createDetailedExcelContent() {
        var table = document.getElementById('detailed-report-table');
        var monthName = $('#detailedReportModal .modal-title small').text().replace('Profit & Loss Statement', '').trim();
        var html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        html += '<head><meta charset="utf-8"><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        html += '<style>table { border-collapse: collapse; } th, td { border: 1px solid #000; padding: 5px; } .text-right { text-align: right; } .text-center { text-align: center; }</style>';
        html += '</head><body>';
        html += '<h3 style="text-align: center;">Profit & Loss Statement</h3>';
        html += '<h4 style="text-align: center;">' + monthName + '</h4>';
        html += '<br>';
        html += table.outerHTML;
        html += '</body></html>';
        return html;
    }
</script>
