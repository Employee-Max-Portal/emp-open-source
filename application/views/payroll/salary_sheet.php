<?php $widget = ((in_array(loggedin_role_id(), [1, 2, 3, 5]))? 4 : 6); 

$currency_symbol = isset($global_config['currency_symbol']) ? $global_config['currency_symbol'] : ' BDT'; // Default fallback
?>
 
<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <header class="panel-heading">
                <h4 class="panel-title"><?php echo translate('select_ground'); ?></h4>
            </header>
            <?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
                <div class="panel-body">
                    <div class="row mb-sm">
                  <?php if (in_array(loggedin_role_id(), [1, 2, 3, 5])) : ?>
                        <div class="col-md-4 mb-sm">
                            <div class="form-group">
                                <label class="control-label"><?php echo translate('business'); ?> <span class="required">*</span></label>
                               <?php
									$arrayBranch = array('all' => translate('all')) + $this->app_lib->getSelectList('branch');

									// Default to "all" if no previous POST value exists
									$selected = set_value('branch_id') ?: 'all';

									echo form_dropdown(
										"branch_id",
										$arrayBranch,
										$selected,
										"class='form-control' onchange='getDesignationByBranch(this.value)' 
										 data-plugin-selectTwo data-width='100%' 
										 data-minimum-results-for-search='Infinity'"
									);
								?>
                            </div>
                        </div>
                    <?php endif; ?>
                        <div class="col-md-<?=$widget?> mb-sm">
                            <div class="form-group">
                                <label class="control-label"><?php echo translate('role'); ?> <span class="required">*</span></label>
                              <?php
									$all_roles = $this->app_lib->getRoles();
									$filtered_roles = array_diff_key($all_roles, array_flip([1, 9, 11, 12]));
									$role_list = array('all' => translate('all')) + $filtered_roles;

									// Default selection logic
									$selected_role = set_value('staff_role') ?: 'all';

									echo form_dropdown(
										"staff_role",
										$role_list,
										$selected_role,
										"class='form-control' data-plugin-selectTwo required data-width=\"100%\" 
										 data-minimum-results-for-search='Infinity'"
									);
								?>
                            </div>
                        </div>
                        <div class="col-md-<?=$widget?> mb-sm">
                            <div class="form-group">
                                <label class="control-label"><?php echo translate('month'); ?> <span class="required">*</span></label>
                                <input type="text" class="form-control monthyear" autocomplete="off" name="month_year" value="<?php echo set_value('month_year', date("Y-m")); ?>" required/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-md-offset-10 col-md-2">
                            <button type="submit" name="search" value="1" class="btn btn-default btn-block"><i class="fas fa-filter"></i> <?php echo translate('filter'); ?></button>
                        </div>
                    </div>
                </div>
            <?php echo form_close(); ?>
        </section>
<?php if (isset($stafflist)): ?>

  <section class="panel appear-animation" data-appear-animation="<?= $global_config['animations'] ?>" data-appear-animation-delay="100">
    <header class="panel-heading">
        <h4 class="panel-title"><?= translate('employee') . " " . translate('list'); ?></h4>
        
        <!-- Export Buttons -->
       <div class="panel-actions export-buttons" style="float: right; margin-top: -5px;">
			<button type="button" class="btn btn-success btn-sm" onclick="exportToPDF()" title="Export to PDF">
				<i class="fa fa-file-pdf-o"></i> PDF
			</button>
			<button type="button" class="btn btn-primary btn-sm" onclick="exportToExcel()" title="Export to Excel">
				<i class="fa fa-file-excel-o"></i> Excel
			</button>
            <button type="button" class="btn btn-info btn-sm" onclick="printBankLetter()" title="Print Bank Letter">
				<i class="fa fa-print"></i> Bank Letter
			</button>
            <button type="button" class="btn btn-warning btn-sm" onclick="printCashLetter()" title="Print Cash Letter">
				<i class="fa fa-money"></i> Cash Letter
			</button>
		</div>

    </header>
    
    <div class="panel-body">
        <div style="text-align: center; margin-bottom: 20px;" id="report-header">
            <h2 style="margin-bottom: 0;">Your Business Name</h2>
            <div>Mohammadpur, Dhaka.</div>
            <strong>Employee Salary Analysis for <?php echo "$month, $year" ?></strong>
        </div>

        <div class="mb-sm mt-xs">
            <?php
                $branches = [];
                $grand = [
                    'gross' => 0,
                    'basic' => 0,
                    'allowance' => 0,
                    'deduction' => 0,
                    'advance' => 0,
                    'net' => 0,
                ];

                // Group staff by branch
                foreach ($stafflist as $row) {
                    $branches[$row->branch_id][] = $row;
                }
            ?>
		<div class="table-responsive">
			<table class="table table-bordered table-hover table-condensed" id="salary-table">
                <thead>
                    <tr>
                        <th><?= translate('SL'); ?></th>
                        <th><?= translate('employee_id'); ?></th>
                        <th><?= translate('employee_name'); ?></th>
                        <th><?= translate('designation'); ?></th>
                        <th><?= translate('bank_info'); ?></th>
                        <th><?= translate('gross_salary'); ?></th>
                        <th><?= translate('basic_salary'); ?></th>
                        <th><?= translate('total_allowance'); ?></th>
                        <th><?= translate('total_earnings'); ?></th>
                        <th><?= translate('add_adjustment'); ?></th>
                        <th><?= translate('total_deduction'); ?></th>
                        <th><?= translate('net_pay'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sl = 1;
                    foreach ($branches as $branch_id => $staffs):
                        $branch_total = [
                            'gross' => 0,
                            'basic' => 0,
                            'allowance' => 0,
                            'deduction' => 0,
                            'advance' => 0,
                            'net' => 0,
                        ];
                    ?>
                        <!-- Branch Label Row -->
                        <tr style="background: #f1f1f1; font-weight: bold;" class="branch-header">
                            <td colspan="12"><?= 'Branch: ' . get_type_name_by_id('branch', $branch_id); ?></td>
                        </tr>
                        <?php foreach ($staffs as $row):
                            // Check for adjustment
                            $adjustment = $this->db->get_where('salary_adjustments', [
                                'staff_id' => $row->id,
                                'month' => $month,
                                'year' => $year
                            ])->row();
                            
                            $advance = (float)($row->advance_amount ?? 0);
                            
                            $has_adjustment = false;
                            $adjustment_amount = 0;
                            
                            if ($adjustment) {
                                $basic = (float)$adjustment->basic_salary;
                                $total_allowance = (float)$adjustment->total_allowance;
                                $gross_salary = $basic + $total_allowance;
                                $total_deduction = (float)$adjustment->total_deduction;
                                $net_salary = (float)$adjustment->net_salary;
                                $has_adjustment = true;
                                $adjustment_amount = $net_salary - ($basic + $total_allowance - $total_deduction);
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
                            }

                            $bank = $row->bank_account ?? [];

                            // Add to branch and grand totals
                            $branch_total['gross'] += $gross_salary;
                            $branch_total['basic'] += $basic;
                            $branch_total['allowance'] += $total_allowance;
                            $branch_total['deduction'] += $total_deduction;
                            $branch_total['advance'] += $advance;
                            $branch_total['net'] += $net_salary;
                            ?>
                            <tr>
                                <td><?= $sl++; ?></td>
                               <td>
									<a href="javascript:void(0);" class="btn btn-default btn-circle mb-xs"
									   data-toggle="tooltip" data-original-title="<?= translate('salary_certificate'); ?>"
									   onclick="getSalaryCertificate('<?= $row->id ?>', '<?= $month ?>', '<?= $year ?>')">
										<?= $row->staff_id; ?>
									</a>
								</td>
                                <td><?= $row->name; ?></td>
                                <td><?= $row->designation_name; ?></td>
                                <td><?= !empty($bank) ? $bank['account_no'] : ''; ?></td>
                                <td><strong><?= number_format($gross_salary, 2) . $currency_symbol; ?></strong></td>
                                <td><?= number_format($basic, 2) . $currency_symbol; ?></td>
                                <td><?= number_format($total_allowance, 2) . $currency_symbol; ?></td>
                                <td><?= number_format($gross_salary, 2) . $currency_symbol; ?></td>
                                <td><?= $has_adjustment ? number_format($adjustment_amount, 2) . $currency_symbol : '-'; ?></td>
                                <td><?= number_format($total_deduction + $advance, 2) . $currency_symbol; ?></td>
                                <td><strong><?= number_format($net_salary, 2) . $currency_symbol; ?></strong></td>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Branch Total Row -->
                        <tr style="font-weight: bold; background: #e9f7ef;" class="branch-total">
                            <td colspan="5" class="text-right">Branch Total</td>
                            <td><?= number_format($branch_total['gross'], 2) . $currency_symbol; ?></td>
                            <td><?= number_format($branch_total['basic'], 2) . $currency_symbol; ?></td>
                            <td><?= number_format($branch_total['allowance'], 2) . $currency_symbol; ?></td>
                            <td><?= number_format($branch_total['gross'], 2) . $currency_symbol; ?></td>
                            <td><?= number_format($branch_total['advance'], 2) . $currency_symbol; ?></td>
                            <td><?= number_format($branch_total['deduction'], 2) . $currency_symbol; ?></td>
                            <td><?= number_format($branch_total['net'], 2) . $currency_symbol; ?></td>
                        </tr>

                    <?php
                        foreach ($grand as $key => $val) {
                            $grand[$key] += $branch_total[$key];
                        }
                    endforeach;
                    ?>
                </tbody>
                <tfoot>
                    <!-- Grand Total Row -->
                    <tr style="font-weight: bold; background: #dff0d8;" class="grand-total">
                        <td colspan="5" class="text-right">Grand Total</td>
                        <td><?= number_format($grand['gross'], 2) . $currency_symbol; ?></td>
                        <td><?= number_format($grand['basic'], 2) . $currency_symbol; ?></td>
                        <td><?= number_format($grand['allowance'], 2) . $currency_symbol; ?></td>
                        <td><?= number_format($grand['gross'], 2) . $currency_symbol; ?></td>
                        <td><?= number_format($grand['advance'], 2) . $currency_symbol; ?></td>
                        <td><?= number_format($grand['deduction'], 2) . $currency_symbol; ?></td>
                        <td><?= number_format($grand['net'], 2) . $currency_symbol; ?></td>
                    </tr>
                </tfoot>
             </table>
			</div>
        </div>
    </div>
</section>

<!-- Include required libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<style>

@media screen and (max-width: 768px) {
  .panel-actions {
    float: none !important;
    margin-top: 10px !important;
    text-align: center;
  }

  #report-header h2 {
    font-size: 20px;
  }

  #report-header div,
  #report-header strong {
    font-size: 14px;
  }

  .table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  #salary-table th, #salary-table td {
    white-space: nowrap;
  }

  .panel-heading {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
  }
}


/* Existing styles */
.no-export {
    display: none;
}
.export-id {
    display: none;
}

/* Enhanced Print Styles */
@media print {
    .no-export {
        display: none !important;
    }
    .export-id {
        display: inline !important;
    }
    
    /* Table heading - bold and 12px */
    #salary-table thead th {
        font-weight: bold !important;
        font-size: 12px !important;
    }
    
    /* Table body - 11px */
    #salary-table tbody td {
        font-size: 11px !important;
    }
    
    /* Branch total and Grand total rows - 14px bold */
    #salary-table .branch-total td,
    #salary-table .grand-total td {
        font-weight: bold !important;
        font-size: 14px !important;
    }
    
    /* Branch header rows - keep current styling but ensure proper font size */
    #salary-table .branch-header td {
        font-weight: bold !important;
        font-size: 12px !important;
    }
    
    /* Specific columns to be bold - Gross Salary (6th), Total Earnings (9th), Net Pay (12th) */
    #salary-table tbody td:nth-child(6),  /* Gross Salary */
    #salary-table tbody td:nth-child(9),  /* Total Earnings */
    #salary-table tbody td:nth-child(12), /* Net Pay */
    #salary-table tfoot td:nth-child(6),  /* Gross Salary in footer */
    #salary-table tfoot td:nth-child(9),  /* Total Earnings in footer */
    #salary-table tfoot td:nth-child(12) { /* Net Pay in footer */
        font-weight: bold !important;
    }
    
    /* Ensure proper spacing and readability */
    #salary-table {
        border-collapse: collapse !important;
    }
    
    #salary-table th,
    #salary-table td {
        border: 1px solid #000 !important;
        padding: 4px !important;
        text-align: left !important;
    }
    
    /* Right align numeric columns */
    #salary-table td:nth-child(6),   /* Gross Salary */
    #salary-table td:nth-child(7),   /* Basic Salary */
    #salary-table td:nth-child(8),   /* Total Allowance */
    #salary-table td:nth-child(9),   /* Total Earnings */
    #salary-table td:nth-child(10),  /* Total Deduction */
    #salary-table td:nth-child(11),  /* Add Adjustment */
    #salary-table td:nth-child(12) { /* Net Pay */
        text-align: right !important;
    }
    
    /* Center align header text */
    #salary-table thead th {
        text-align: center !important;
    }
    
    /* Ensure branch headers span full width and are left aligned */
    #salary-table .branch-header td {
        text-align: left !important;
    }
    
    /* Branch total and grand total label alignment */
    #salary-table .branch-total td:nth-child(-n+5),
    #salary-table .grand-total td:nth-child(-n+5) {
        text-align: right !important;
    }
}
</style>

<script>
function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'mm', 'a4'); // landscape orientation
    
    // Add header
    doc.setFontSize(16);
    doc.text('Your Business Name', 148, 20, { align: 'center' });
    doc.setFontSize(10);
    doc.text('Mohammadpur, Dhaka.', 148, 28, { align: 'center' });
    doc.setFontSize(12);
    doc.text('Employee Salary Analysis for <?php echo "$month, $year" ?>', 148, 36, { align: 'center' });
    
    // Get table data exactly as displayed
    const table = document.getElementById('salary-table');
    const rows = [];
    
    // Add headers
    const headers = [];
    const headerCells = table.querySelectorAll('thead th');
    headerCells.forEach(cell => headers.push(cell.textContent.trim()));
    
    // Add ALL rows including branch headers, data rows, branch totals, and grand total
    const allRows = table.querySelectorAll('tbody tr, tfoot tr');
    allRows.forEach(row => {
        const rowData = [];
        const cells = row.querySelectorAll('td');
        
        if (row.classList.contains('branch-header')) {
            // Branch header row - span across all columns
            rowData.push(cells[0].textContent.trim());
            for (let i = 1; i < headers.length; i++) {
                rowData.push('');
            }
        } else if (row.classList.contains('branch-total') || row.classList.contains('grand-total')) {
            // Handle Branch Total and Grand Total rows with colspan
            const firstCell = cells[0].textContent.trim(); // "Branch Total" or "Grand Total"
            
            // Add empty cells for the first 4 columns (SL, ID, Name, Designation, Bank Info)
            rowData.push(''); // SL
            rowData.push(''); // Employee ID
            rowData.push(''); // Name
            rowData.push(''); // Designation
            rowData.push(firstCell); // Bank Info column shows the total label
            
            // Add the actual values from the remaining cells
            for (let i = 1; i < cells.length; i++) {
                rowData.push(cells[i].textContent.trim());
            }
        } else {
            // Regular data row
            cells.forEach((cell, index) => {
                if (index === 1) {
                    // For employee ID column in data rows
                    rowData.push(cell.textContent.replace(/\s+/g, ' ').trim().split('\n')[0]);
                } else {
                    rowData.push(cell.textContent.trim());
                }
            });
        }
        rows.push(rowData);
    });
    
    // Generate PDF table with proper formatting
    doc.autoTable({
        head: [headers],
        body: rows,
        startY: 45,
        theme: 'grid',
        styles: { 
            fontSize: 7,
            cellPadding: 2,
            overflow: 'linebreak',
            cellWidth: 'wrap'
        },
        headStyles: { 
            fillColor: [52, 152, 219],
            textColor: [255, 255, 255],
            fontStyle: 'bold'
        },
        columnStyles: {
            0: { cellWidth: 20 },   // SL
            1: { cellWidth: 20 },  // Employee ID
            2: { cellWidth: 25 },  // Name
            3: { cellWidth: 20 },  // Designation
            4: { cellWidth: 20 },  // Bank Info
            5: { cellWidth: 24, halign: 'left' },  // Gross Salary
            6: { cellWidth: 24, halign: 'left' },  // Basic Salary
            7: { cellWidth: 24, halign: 'left' },  // Total Allowance
            8: { cellWidth: 24, halign: 'left' },  // Total Earnings
            9: { cellWidth: 24, halign: 'left' },  // Total Deduction
            10: { cellWidth: 24, halign: 'left' }, // Add Adjustment
            11: { cellWidth: 24, halign: 'left' }  // Net Pay
        },
        		
		 /* didParseCell: function(data) {
			const isHeader = data.section === 'head';
			const isBranchHeader = data.row.raw[0]?.toString().startsWith('Branch:');
			const isBranchTotal = data.row.raw[4] === 'Branch Total';
			const isGrandTotal = data.row.raw[4] === 'Grand Total';
			const isTotalRow = isBranchTotal || isGrandTotal;
			const colIndex = data.column.index;

			// Headings
			if (isHeader) {
				data.cell.styles.fontSize = 11;
				data.cell.styles.fontStyle = 'bold';
				return;
			}

			// Branch header
			if (isBranchHeader) {
				data.cell.styles.fillColor = [241, 241, 241];
				data.cell.styles.fontStyle = 'bold';
				data.cell.styles.fontSize = 11;
				data.cell.styles.halign = 'left';
				return;
			}

			// Branch Total / Grand Total rows
			if (isTotalRow) {
				data.cell.styles.fontStyle = 'bold';
				data.cell.styles.fontSize = 11;
				data.cell.styles.fillColor = isGrandTotal ? [223, 240, 216] : [233, 247, 239];
				data.cell.styles.halign = colIndex >= 5 ? 'left' : 'right';
				return;
			}

			// Default table body styling
			data.cell.styles.fontSize = 11;

			// Bold for specific columns in all rows
			const boldColumns = [5, 8, 11]; // Gross Salary, Total Earnings, Net Pay
			if (boldColumns.includes(colIndex)) {
				data.cell.styles.fontStyle = 'bold';
			}
		} */
		
		 didParseCell: function(data) {
            // Style branch headers
            if (data.row.raw[0] && data.row.raw[0].toString().startsWith('Branch:')) {
                data.cell.styles.fillColor = [241, 241, 241];
                data.cell.styles.fontStyle = 'bold';
                data.cell.styles.halign = 'left';
            }
            // Style branch totals - check column 4 (Bank Info) for the label
            else if (data.column.index === 4 && (data.cell.text === 'Branch Total' || data.cell.text === 'Grand Total')) {
                data.cell.styles.fillColor = data.cell.text === 'Grand Total' ? [223, 240, 216] : [233, 247, 239];
                data.cell.styles.fontStyle = 'bold';
                data.cell.styles.halign = 'left';
            }
            // Style the numeric cells in total rows
            else if (data.column.index >= 5 && data.row.raw[4] && (data.row.raw[4] === 'Branch Total' || data.row.raw[4] === 'Grand Total')) {
                data.cell.styles.fillColor = data.row.raw[4] === 'Grand Total' ? [223, 240, 216] : [233, 247, 239];
                data.cell.styles.fontStyle = 'bold';
                data.cell.styles.halign = 'left';
            }
        }, 
        margin: { left: 5, right: 5 }
    });
    
    // Save PDF
    doc.save('Employee_Salary_Analysis_<?php echo "{$month}_{$year}" ?>.pdf');
}

function exportToExcel() {
    // Create workbook
    const wb = XLSX.utils.book_new();
    
    // Get table data exactly as displayed
    const table = document.getElementById('salary-table');
    const data = [];
    
    // Add company header
    data.push(['Your Business Name', '', '', '', '', '', '', '', '', '', '', '']);
    data.push(['Mohammadpur, Dhaka.', '', '', '', '', '', '', '', '', '', '', '']);
    data.push(['Employee Salary Analysis for <?php echo "$month, $year" ?>', '', '', '', '', '', '', '', '', '', '', '']);
    data.push(['', '', '', '', '', '', '', '', '', '', '', '']); // Empty row
    
    // Add table headers
    const headers = [];
    const headerCells = table.querySelectorAll('thead th');
    headerCells.forEach(cell => headers.push(cell.textContent.trim()));
    data.push(headers);
    
    // Add ALL table data exactly as displayed
    const allRows = table.querySelectorAll('tbody tr, tfoot tr');
    allRows.forEach(row => {
        const rowData = [];
        const cells = row.querySelectorAll('td');
        
        if (row.classList.contains('branch-header')) {
            // Branch header row - merge across all columns
            rowData.push(cells[0].textContent.trim());
            for (let i = 1; i < headers.length; i++) {
                rowData.push('');
            }
        } else if (row.classList.contains('branch-total') || row.classList.contains('grand-total')) {
            // Handle Branch Total and Grand Total rows with colspan
            const firstCell = cells[0].textContent.trim(); // "Branch Total" or "Grand Total"
            
            // Add empty cells for the first 4 columns (SL, ID, Name, Designation)
            rowData.push(''); // SL
            rowData.push(''); // Employee ID
            rowData.push(''); // Name
            rowData.push(''); // Designation
            rowData.push(firstCell); // Bank Info column shows the total label
            
            // Add the actual values from the remaining cells
            for (let i = 1; i < cells.length; i++) {
                rowData.push(cells[i].textContent.trim());
            }
        } else {
            // Regular data row
            cells.forEach((cell, index) => {
                if (index === 1 && !row.classList.contains('branch-total') && !row.classList.contains('grand-total')) {
                    // For employee ID column in data rows, extract just the ID
                    const cellText = cell.textContent.trim();
                    const lines = cellText.split('\n').map(line => line.trim()).filter(line => line);
                    rowData.push(lines[0] || cellText);
                } else {
                    // For all other cells, keep original text
                    rowData.push(cell.textContent.trim());
                }
            });
        }
        data.push(rowData);
    });
    
    // Create worksheet
    const ws = XLSX.utils.aoa_to_sheet(data);
    
    // Set column widths for better text wrapping
    const colWidths = [
        { wch: 6 },   // SL
        { wch: 15 },  // Employee ID
        { wch: 25 },  // Name
        { wch: 20 },  // Designation
        { wch: 20 },  // Bank Info
        { wch: 18 },  // Gross Salary
        { wch: 18 },  // Basic Salary
        { wch: 18 },  // Total Allowance
        { wch: 18 },  // Total Earnings
        { wch: 18 },  // Total Deduction
        { wch: 18 },  // Add Adjustment
        { wch: 18 }   // Net Pay
    ];
    ws['!cols'] = colWidths;
    
    // Enable text wrapping for all cells
    const range = XLSX.utils.decode_range(ws['!ref']);
    for (let row = range.s.r; row <= range.e.r; row++) {
        for (let col = range.s.c; col <= range.e.c; col++) {
            const cellAddress = XLSX.utils.encode_cell({ r: row, c: col });
            if (ws[cellAddress]) {
                if (!ws[cellAddress].s) ws[cellAddress].s = {};
                ws[cellAddress].s.alignment = { 
                    wrapText: true, 
                    vertical: 'center',
                    horizontal: col >= 5 ? 'right' : 'left' // Right align numeric columns
                };
            }
        }
    }
    
    // Merge cells for company header
    ws['!merges'] = [
        { s: { r: 0, c: 0 }, e: { r: 0, c: 11 } }, // Company name
        { s: { r: 1, c: 0 }, e: { r: 1, c: 11 } }, // Address
        { s: { r: 2, c: 0 }, e: { r: 2, c: 11 } }  // Report title
    ];
    
    // Add merge ranges for branch headers
    let currentRow = 5; // Start after headers
    allRows.forEach(row => {
        if (row.classList.contains('branch-header')) {
            ws['!merges'].push({ s: { r: currentRow, c: 0 }, e: { r: currentRow, c: 11 } });
        }
        currentRow++;
    });
    
    // Style company headers first
    const companyHeaderStyle = {
        font: { bold: true, sz: 16 },
        alignment: { horizontal: 'center', vertical: 'center', wrapText: true }
    };
    
    const addressStyle = {
        font: { bold: true, sz: 12 },
        alignment: { horizontal: 'center', vertical: 'center', wrapText: true }
    };
    
    const reportTitleStyle = {
        font: { bold: true, sz: 14 },
        alignment: { horizontal: 'center', vertical: 'center', wrapText: true }
    };
    
    // Apply company header styles
    const companyCell = XLSX.utils.encode_cell({ r: 0, c: 0 });
    if (ws[companyCell]) {
        ws[companyCell].s = companyHeaderStyle;
    }
    
    const addressCell = XLSX.utils.encode_cell({ r: 1, c: 0 });
    if (ws[addressCell]) {
        ws[addressCell].s = addressStyle;
    }
    
    const titleCell = XLSX.utils.encode_cell({ r: 2, c: 0 });
    if (ws[titleCell]) {
        ws[titleCell].s = reportTitleStyle;
    }
    
    // Style table headers and important rows
    const headerStyle = {
        font: { bold: true, color: { rgb: "FFFFFF" } },
        fill: { fgColor: { rgb: "3498DB" } },
        alignment: { horizontal: 'center', vertical: 'center', wrapText: true }
    };
    
    const branchHeaderStyle = {
        font: { bold: true },
        fill: { fgColor: { rgb: "F1F1F1" } },
        alignment: { horizontal: 'left', vertical: 'center', wrapText: true }
    };
    
    const branchTotalStyle = {
        font: { bold: true },
        fill: { fgColor: { rgb: "E9F7EF" } },
        alignment: { horizontal: 'right', vertical: 'center', wrapText: true }
    };
    
    const grandTotalStyle = {
        font: { bold: true },
        fill: { fgColor: { rgb: "DFF0D8" } },
        alignment: { horizontal: 'right', vertical: 'center', wrapText: true }
    };
    
    // Apply styles to table header row
    for (let col = 0; col < headers.length; col++) {
        const cellAddress = XLSX.utils.encode_cell({ r: 4, c: col });
        if (ws[cellAddress]) {
            ws[cellAddress].s = headerStyle;
        }
    }
    
    // Apply styles to data rows
    currentRow = 5;
    allRows.forEach(row => {
        if (row.classList.contains('branch-header')) {
            for (let col = 0; col < headers.length; col++) {
                const cellAddress = XLSX.utils.encode_cell({ r: currentRow, c: col });
                if (ws[cellAddress]) {
                    ws[cellAddress].s = branchHeaderStyle;
                }
            }
        } else if (row.classList.contains('branch-total')) {
            for (let col = 0; col < headers.length; col++) {
                const cellAddress = XLSX.utils.encode_cell({ r: currentRow, c: col });
                if (ws[cellAddress]) {
                    if (col === 4) { // Bank Info column containing "Branch Total" label
                        ws[cellAddress].s = { ...branchTotalStyle, alignment: { horizontal: 'right', vertical: 'center', wrapText: true } };
                    } else if (col >= 5) { // Numeric columns
                        ws[cellAddress].s = branchTotalStyle;
                    } else {
                        ws[cellAddress].s = { ...branchTotalStyle, alignment: { horizontal: 'center', vertical: 'center', wrapText: true } };
                    }
                }
            }
        } else if (row.classList.contains('grand-total')) {
            for (let col = 0; col < headers.length; col++) {
                const cellAddress = XLSX.utils.encode_cell({ r: currentRow, c: col });
                if (ws[cellAddress]) {
                    if (col === 4) { // Bank Info column containing "Grand Total" label
                        ws[cellAddress].s = { ...grandTotalStyle, alignment: { horizontal: 'right', vertical: 'center', wrapText: true } };
                    } else if (col >= 5) { // Numeric columns
                        ws[cellAddress].s = grandTotalStyle;
                    } else {
                        ws[cellAddress].s = { ...grandTotalStyle, alignment: { horizontal: 'center', vertical: 'center', wrapText: true } };
                    }
                }
            }
        }
        currentRow++;
    });
    
    // Add worksheet to workbook
    XLSX.utils.book_append_sheet(wb, ws, 'Salary Analysis');
    
    // Save Excel file
    XLSX.writeFile(wb, 'Employee_Salary_Analysis_<?php echo "{$month}_{$year}" ?>.xlsx');
}
</script>
<?php endif; ?>
    </div>
</div>

<div class="zoom-anim-dialog modal-block modal-block-lg mfp-hide payroll-t-modal" id="modal_details" style="width: 100%!important;">
    <section class="panel">
        <header class="panel-heading">
            <div class="row">
                <div class="col-md-6 text-left">
                    <h4 class="panel-title">
						<i class="fas fa-bars"></i> <?php echo translate('salary') . " " . translate('Certificate'); ?>
					</h4>
                </div>
                <div class="col-md-5 text-right">
                    <!-- Print Button in Footer -->
                    <button class="btn btn-primary" onclick="printDescription()">🖨️ Print</button>
                </div>
            </div>
        </header>
        <div class="panel-body">
            <div id="details_view_tray">
                <!-- The table content will be loaded here dynamically -->
            </div>
        </div>
        <footer class="panel-footer">
            <div class="row">
                <div class="col-md-6 text-left">
                    <!-- Print Button in Footer -->
                    <button class="btn btn-primary" onclick="printDescription()">🖨️ Print</button>
                </div>
                <div class="col-md-6 text-right">
                    <button class="btn btn-default modal-dismiss"><?php echo translate('close'); ?></button>
                </div>
            </div>
        </footer>
    </section>
</div>

<script>
function getSalaryCertificate(staff_id, month, year) {
    $.ajax({
        url: base_url + 'payroll/get_salary_certificate',
        type: 'POST',
        data: {
            staff_id: staff_id,
            month: month,
            year: year
        },
        success: function(response) {
            $('#details_view_tray').html(response);
            $.magnificPopup.open({
                items: {
                    src: '#modal_details'
                },
                type: 'inline'
            });
        },
        error: function() {
            alert('Failed to retrieve salary certificate.');
        }
    });
}
</script>

<script>
function printDescription() {
    const content = document.getElementById('details_view_tray').innerHTML;
    const printWindow = window.open('', '', 'width=800,height=600');
    const backgroundUrl = 'https://emp.com.bd/uploads/app_image/pad.jpg';

    const html = `
    <html>
    <head>
        <title>Salary Certificate</title>
        <style>
            @page {
                size: A4;
            }

            html, body {
                margin: 0;
                padding: 0;
                height: 100%;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            body {
                font-family: Arial, sans-serif;
                position: relative;
            }

            body::before {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-image: url('${backgroundUrl}');
                background-repeat: no-repeat;
                background-position: center;
                background-size: cover;
                z-index: -1;
            }

            .content-wrapper {
                position: relative;
                z-index: 2;
                padding: 60px 50px;
            }

            h1 {
                text-align: center;
                font-size: 22px;
                margin-bottom: 20px;
            }

            .footer {
                text-align: center;
                font-size: 13px;
                color: #666;
                margin-top: 60px;
                border-top: 1px solid #ccc;
            }
        </style>
    </head>
    <body>
        <div class="content-wrapper">
            <div>${content}</div>
        </div>
    </body>
    </html>
    `;

    printWindow.document.open();
    printWindow.document.write(html);
    printWindow.document.close();
    
    // Preload the background image
    const img = new Image();
    img.onload = function() {
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 1000);
    };
    img.src = backgroundUrl;
}

function printBankLetter() {
    // Get month and year from PHP
    // Assuming $month is numeric (e.g., "10") or name. We'll handle both.
    const rawMonth = "<?php echo $month; ?>";
    const rawYear = "<?php echo $year; ?>";
    
    // Helper to format month
    const getMonthName = (m) => {
        const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        if (!isNaN(m)) {
            return monthNames[parseInt(m) - 1] || m;
        }
        return m.substr(0, 3); // If already text, take first 3 chars
    };
    
    const formattedMonth = getMonthName(rawMonth);
    const salaryMonth = `${formattedMonth}-${rawYear}`;
    
    // Get current date
    const date = new Date();
    const day = date.getDate().toString().padStart(2, '0');
    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const currMonth = monthNames[date.getMonth()];
    const currYear = date.getFullYear();
    const currentDate = `${day}-${currMonth}-${currYear}`;

    // Get table data
    const table = document.getElementById('salary-table');
    const rows = table.querySelectorAll('tbody tr');
    let tableHtml = '';
    let sl = 1;
    let totalAmount = 0;
    let currencySymbol = '';

    rows.forEach(row => {
        if (row.classList.contains('branch-header') || row.classList.contains('branch-total')) {
            return; // Skip branch rows
        }
        
        const cells = row.querySelectorAll('td');
        if (cells.length > 0) {
            const name = cells[2].textContent.trim();
            const accountNo = cells[4].textContent.trim();
            const netPay = cells[11].textContent.trim();
            
            // Skip if account number is empty
            if (!accountNo) {
                return;
            }

            // Extract currency symbol from the first valid row if not set
            if (!currencySymbol) {
                const match = netPay.match(/[^\d.,\s-]+$/);
                if (match) currencySymbol = match[0];
            }

            // Parse amount for total calculation
            // Remove commas and non-numeric chars (except dot and minus)
            const cleanAmount = netPay.replace(/,/g, '').replace(/[^\d.-]/g, '');
            const amount = parseFloat(cleanAmount);
            if (!isNaN(amount)) {
                totalAmount += amount;
            }
            
            tableHtml += `
                <tr>
                    <td style="text-align: center; white-space: nowrap;">${sl++}</td>
                    <td>${name}</td>
                    <td style="text-align: center; white-space: nowrap;">${accountNo}</td>
                    <td style="text-align: right; white-space: nowrap;">${netPay}</td>
                </tr>
            `;
        }
    });

    // Format Grand Total
    // Use toLocaleString to add commas, and append currency symbol
    let grandTotal = totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    if (currencySymbol) {
        grandTotal += ' ' + currencySymbol;
    }

    const backgroundUrl = 'https://emp.com.bd/uploads/app_image/pad.jpg';

    const html = `
    <html>
    <head>
        <title>Salary Disbursement Letter</title>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
            @page {
                size: A4;
                margin: 0; 
            }
            body {
                font-family: 'Roboto', Arial, sans-serif;
                margin: 0;
                padding: 0;
                color: #000;
                font-size: 12px;
                line-height: 1.4;
            }
            .page-background {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: -1;
            }
            .page-background img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            
            /* Layout Table to handle margins on every page */
            .layout-table {
                width: 100%;
                border-collapse: collapse;
                border: none;
            }
            .layout-table thead td {
                height: 130px; /* Top margin for letterhead */
                border: none !important;
            }
            .layout-table tfoot td {
                height: 50px; /* Bottom margin */
                border: none !important;
            }
            .layout-table tbody td {
                padding: 0 50px; /* Side margins */
                vertical-align: top;
            }

            .header-date {
                margin-bottom: 15px;
            }
            .recipient {
                margin-bottom: 20px;
            }
            .subject {
                font-weight: bold;
                margin-bottom: 20px;
            }
            .body-text {
                margin-bottom: 15px;
                text-align: justify;
            }
            
            /* Data Table */
            .data-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 15px;
                font-size: 11px;
                border: 1px solid #000;
            }
			
            .data-table th, .data-table td {
                border: 1px solid #000;
                padding: 5px 8px;
            }
            .data-table th {
                background-color: #f2f2f2;
                font-weight: bold;
                text-align: center;
            }
            .total-row td {
                font-weight: bold;
                border: 1px solid #000;
            }
            
            .footer-signature {
                margin-top: 40px;
                page-break-inside: avoid; /* Prevent signature from breaking */
            }
            .signature-img {
                height: 50px;
                margin-bottom: 5px;
            }
            .company-info {
                margin-top: 5px;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class="page-background">
            <img src="${backgroundUrl}" alt="Background">
        </div>
        
        <table class="layout-table">
            <thead>
                <tr><td>&nbsp;</td></tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="header-date">
                            ${currentDate}
                        </div>
                        <div class="recipient">
                            To,<br>
                            Manager<br>
                            Bank Asia Limited<br>
                            Bay's Galleria, 57, Gulshan-1, Dhaka
                        </div>
                        <div class="subject">
                            Subject: Salary Disbursement for ${salaryMonth} - Request for Payroll Processing.
                        </div>
                        <div class="body-text">
                            Dear Sir,<br>
                            I hope this letter finds you well. This letter is issued to formally notify and authorize the disbursement of employee salaries for the month of <strong>${salaryMonth}</strong>, to be processed from the organization’s designated bank account.
                        </div>
                        <div class="body-text">
                            We kindly request your bank's assistance in processing the salary payments for our employees, who hold salary accounts with your esteemed institution. The list of employees and their respective salary amounts for this specific month are as follows:
                        </div>
                        
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="white-space: nowrap; width: 1%;">SL No</th>
                                    <th>Employees Name</th>
                                    <th style="white-space: nowrap; width: 1%;">Employees A/C No.</th>
                                    <th style="white-space: nowrap; width: 1%;">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${tableHtml}
                            </tbody>
                            <tfoot>
                                <tr class="total-row">
                                    <td colspan="3" style="text-align: right;">Total Salary Amount</td>
                                    <td style="text-align: right;">${grandTotal}</td>
                                </tr>
                            </tfoot>
                        </table>
                        
                        <div class="body-text">
                            Please note that we have verified and approved the salary statements for the mentioned employees. The total salary amount reflects the cumulative payment required for the disbursement.
                        </div>
                        <div class="body-text">
                            For your convenience, we have attached the necessary documentation, including the employee salary details and other relevant information. Should you require any additional information or have any queries, please do not hesitate to contact our HR department at Phone Number +88-01918562760.
                        </div>
                        <div class="body-text">
                            Thank you for your prompt attention to this request.
                        </div>
                        
                        <div class="footer-signature">
                            Sincerely,<br><br>
                            <div style="font-family: 'Brush Script MT', cursive; font-size: 24px; margin-bottom: 5px;">----</div>
                            <strong>--------</strong>
                        </div>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr><td>&nbsp;</td></tr>
            </tfoot>
        </table>
    </body>
    </html>
    `;

    const printWindow = window.open('', '', 'width=900,height=1200');
    printWindow.document.open();
    printWindow.document.write(html);
    printWindow.document.close();
    
    // Wait for image to load
    const img = new Image();
    img.onload = function() {
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            // printWindow.close();
        }, 1000);
    };
    img.src = backgroundUrl;
}

function printCashLetter() {
    // Get month and year from PHP
    const rawMonth = "<?php echo $month; ?>";
    const rawYear = "<?php echo $year; ?>";
    
    // Helper to format month
    const getMonthName = (m) => {
        const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        if (!isNaN(m)) {
            return monthNames[parseInt(m) - 1] || m;
        }
        return m.substr(0, 3);
    };
    
    const formattedMonth = getMonthName(rawMonth);
    const salaryMonth = `${formattedMonth}-${rawYear}`;
    
    // Get current date
    const date = new Date();
    const day = date.getDate().toString().padStart(2, '0');
    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const currMonth = monthNames[date.getMonth()];
    const currYear = date.getFullYear();
    const currentDate = `${day}-${currMonth}-${currYear}`;

    // Get table data
    const table = document.getElementById('salary-table');
    const rows = table.querySelectorAll('tbody tr');
    let tableHtml = '';
    let sl = 1;
    let totalAmount = 0;
    let currencySymbol = '';

    rows.forEach(row => {
        if (row.classList.contains('branch-header') || row.classList.contains('branch-total')) {
            return; // Skip branch rows
        }
        
        const cells = row.querySelectorAll('td');
        if (cells.length > 0) {
            const name = cells[2].textContent.trim();
            const designation = cells[3].textContent.trim();
            const accountNo = cells[4].textContent.trim();
            const netPay = cells[11].textContent.trim();
            
            // Skip if account number exists (we only want missing bank info)
            if (accountNo) {
                return;
            }

            // Extract currency symbol
            if (!currencySymbol) {
                const match = netPay.match(/[^\d.,\s-]+$/);
                if (match) currencySymbol = match[0];
            }

            // Parse amount
            const cleanAmount = netPay.replace(/,/g, '').replace(/[^\d.-]/g, '');
            const amount = parseFloat(cleanAmount);
            if (!isNaN(amount)) {
                totalAmount += amount;
            }
            
            tableHtml += `
                <tr>
                    <td style="text-align: center; white-space: nowrap;">${sl++}</td>
                    <td>${name}</td>
                    <td style="text-align: center; white-space: nowrap;">${designation}</td>
                    <td style="text-align: right; white-space: nowrap;">${netPay}</td>
                </tr>
            `;
        }
    });

    // Format Grand Total
    let grandTotal = totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    if (currencySymbol) {
        grandTotal += ' ' + currencySymbol;
    }

    const backgroundUrl = 'https://emp.com.bd/uploads/app_image/pad.jpg';

    const html = `
    <html>
    <head>
        <title>Cash Salary Disbursement</title>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
            @page {
                size: A4;
                margin: 0; 
            }
            body {
                font-family: 'Roboto', Arial, sans-serif;
                margin: 0;
                padding: 0;
                color: #000;
                font-size: 12px;
                line-height: 1.4;
            }
            .page-background {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: -1;
            }
            .page-background img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            
            /* Layout Table */
            .layout-table {
                width: 100%;
                border-collapse: collapse;
                border: none;
            }
            .layout-table thead td {
                height: 130px;
                border: none !important;
            }
            .layout-table tfoot td {
                height: 50px;
                border: none !important;
            }
            .layout-table tbody td {
                padding: 0 50px;
                vertical-align: top;
                border: none !important;
            }

            .header-date {
                margin-bottom: 15px;
            }
            .recipient {
                margin-bottom: 20px;
            }
            .subject {
                font-weight: bold;
                margin-bottom: 20px;
            }
            .body-text {
                margin-bottom: 15px;
                text-align: justify;
            }
            
            /* Data Table */
            .data-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 15px;
                font-size: 11px;
                border: 1px solid #000;
            }
            .data-table th, .data-table td {
                border: 1px solid #000;
                padding: 5px 8px;
            }
            .data-table th {
                background-color: #f2f2f2;
                font-weight: bold;
                text-align: center;
            }
            .total-row td {
                font-weight: bold;
                border: 1px solid #000;
            }
            
            .footer-signature {
                margin-top: 40px;
                page-break-inside: avoid;
            }
        </style>
    </head>
    <body>
        <div class="page-background">
            <img src="${backgroundUrl}" alt="Background">
        </div>
        
        <table class="layout-table">
            <thead>
                <tr><td>&nbsp;</td></tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="header-date">
                            ${currentDate}
                        </div>
                        <div class="recipient">
                            To,<br>
                            The Accounts Department<br>
                            Dhaka
                        </div>
                        <div class="subject">
                            Subject: Cash Salary Disbursement for ${salaryMonth} - Request for Cash Release.
                        </div>
                        <div class="body-text">
                            Dear Sir,<br>
                            I hope this letter finds you well. I am writing regarding the cash disbursement of salaries for our valued employees for the month of <strong>${salaryMonth}</strong>.
                        </div>
                        <div class="body-text">
                            We kindly request you to release the necessary cash amount for the following employees who do not have bank accounts. The salaries will be distributed by the HR Department.
                        </div>
                        
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="white-space: nowrap; width: 1%;">SL No</th>
                                    <th>Employees Name</th>
                                    <th style="white-space: nowrap; width: 1%;">Designation</th>
                                    <th style="white-space: nowrap; width: 1%;">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${tableHtml}
                            </tbody>
                            <tfoot>
                                <tr class="total-row">
                                    <td colspan="3" style="text-align: right;">Total Cash Amount</td>
                                    <td style="text-align: right;">${grandTotal}</td>
                                </tr>
                            </tfoot>
                        </table>
                        
                        <div class="body-text">
                            Please note that we have verified and approved the salary statements for the mentioned employees. The total cash amount reflects the cumulative payment required for the disbursement.
                        </div>
                        <div class="body-text">
                            Should you require any additional information or have any queries, please do not hesitate to contact our HR department.
                        </div>
                        <div class="body-text">
                            Thank you for your prompt attention to this request.
                        </div>
                        
                        <div class="footer-signature">
                            Sincerely,<br><br>
                            <div style="font-family: 'Brush Script MT', cursive; font-size: 24px; margin-bottom: 5px;">----</div>
                            <strong>--------</strong>
                        </div>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr><td>&nbsp;</td></tr>
            </tfoot>
        </table>
    </body>
    </html>
    `;

    const printWindow = window.open('', '', 'width=900,height=1200');
    printWindow.document.open();
    printWindow.document.write(html);
    printWindow.document.close();
    
    // Wait for image to load
    const img = new Image();
    img.onload = function() {
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            // printWindow.close();
        }, 1000);
    };
    img.src = backgroundUrl;
}
</script>
