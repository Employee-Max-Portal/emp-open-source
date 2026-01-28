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
                    <button class="btn btn-default btn-sm" onclick="printReport()"><i class="fas fa-print"></i> Print</button>
                    <button class="btn btn-success btn-sm" onclick="exportExcel()"><i class="fas fa-file-excel"></i> Excel</button>
                </div>
                <h4 class="panel-title text-center">Sales Report</h4>
                <h5 class="text-center">For the Month <?= $month_name ?></h5></h6>
            </header>
            <div class="panel-body">
                <div class="table-responsive" id="report-table-container">
                    <table class="table table-bordered table-condensed table-hover" id="report-table">
                        <thead>
                            <tr style="background-color: #e5e5e5;">
                                <th class="text-center">Date</th>
                                <th class="text-center">Description</th>
                                <th class="text-center">Amount (BDT)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $grand_total = 0;
                            if (!empty($sales_data)): 
                                foreach ($sales_data as $row): 
                                    $grand_total += $row->amount;
                            ?>
                            <tr>
                                <td><?php echo date('d-m-Y', strtotime($row->entry_date)); ?></td>
                                <td><?php echo $row->description; ?></td>
                                <td class="text-right"><?php echo number_format($row->amount, 2); ?></td>
                            </tr>
                            <?php 
                                endforeach; 
                            else: 
                            ?>
                            <tr>
                                <td colspan="3" class="text-center"><?= translate('no_information_available') ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #dff0d8; font-weight: bold;">
                                <td colspan="2" class="text-right">Total Sales</td>
                                <td class="text-right"><?php echo number_format($grand_total, 2); ?></td>
                            </tr>
                            <tr style="background-color: #f0f8ff; font-weight: bold;">
                                <td colspan="2" class="text-right">Revenue Share (10%)</td>
                                <td class="text-right"><?php echo number_format($grand_total * 0.10, 2); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </section>
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
        var header = '<h4 class="text-center">Sales Report</h4><h5 class="text-center">For the Month <?= $month_name ?></h5><br>';

        document.body.innerHTML = header + printContents;
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
        a.download = 'Sales_Report_' + postfix + '.xls';
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
        html += '<h3 style="text-align: center;">Sales Report</h3>';
        html += '<h4 style="text-align: center;">For the Month <?= $month_name ?></h4>';
        html += '<br>';
        html += table.outerHTML;
        html += '</body></html>';
        return html;
    }
</script>