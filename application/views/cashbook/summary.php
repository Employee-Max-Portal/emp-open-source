<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <header class="panel-heading">
                <h4 class="panel-title"><?=translate('cashbook_summary')?></h4>
            </header>
            
            <div class="panel-body">
                <!-- Search Form -->
                <?php echo form_open($this->uri->uri_string(), array('class' => 'form-horizontal')); ?>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label"><?=translate('month_year')?></label>
                            <input type="text" class="form-control" name="month_year" value="<?php echo set_value('month_year'); ?>" data-plugin-datepicker data-plugin-options='{"format": "yyyy-mm", "viewMode": "months", "minViewMode": "months"}' />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="control-label">&nbsp;</label>
                            <button type="submit" name="search" value="1" class="btn btn-default btn-block"><?=translate('search')?></button>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>

                <?php if (!empty($summary_data)): ?>
                <!-- Summary Cards -->
                <div class="row">
                    <!-- Fund Requisitions -->
                    <div class="col-md-6">
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                                <h4 class="panel-title">Fund Requisitions</h4>
                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Paid:</strong> <?= $summary_data['fund_requisitions']['paid_count'] ?> entries</p>
                                        <p><strong>Amount:</strong> <?= number_format($summary_data['fund_requisitions']['paid_amount'], 2) ?> BDT</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Pending:</strong> <?= $summary_data['fund_requisitions']['pending_count'] ?> entries</p>
                                        <p><strong>Amount:</strong> <?= number_format($summary_data['fund_requisitions']['pending_amount'], 2) ?> BDT</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Advance Salary -->
                    <div class="col-md-6">
                        <div class="panel panel-warning">
                            <div class="panel-heading">
                                <h4 class="panel-title">Advance Salary</h4>
                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Paid:</strong> <?= $summary_data['advance_salary']['paid_count'] ?> entries</p>
                                        <p><strong>Amount:</strong> <?= number_format($summary_data['advance_salary']['paid_amount'], 2) ?> BDT</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Pending:</strong> <?= $summary_data['advance_salary']['pending_count'] ?> entries</p>
                                        <p><strong>Amount:</strong> <?= number_format($summary_data['advance_salary']['pending_amount'], 2) ?> BDT</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Payroll -->
                    <div class="col-md-6">
                        <div class="panel panel-success">
                            <div class="panel-heading">
                                <h4 class="panel-title">Payroll Summary</h4>
                            </div>
                            <div class="panel-body">
                                <p><strong>Total Salaries Paid:</strong> <?= $summary_data['payroll']['total_count'] ?> employees</p>
                                <p><strong>Total Amount:</strong> <?= number_format($summary_data['payroll']['total_paid'], 2) ?> BDT</p>
                            </div>
                        </div>
                    </div>

                    <!-- Cashbook Summary -->
                    <div class="col-md-6">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h4 class="panel-title">Cash Flow Summary</h4>
                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Cash In:</strong> <?= number_format($summary_data['cashbook']['cash_in'], 2) ?> BDT</p>
                                        <p><strong>Cash Out:</strong> <?= number_format($summary_data['cashbook']['cash_out'], 2) ?> BDT</p>
                                        <p><strong>Cash Balance:</strong> <?= number_format($summary_data['cashbook']['cash_in'] - $summary_data['cashbook']['cash_out'], 2) ?> BDT</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Bank In:</strong> <?= number_format($summary_data['cashbook']['bank_in'], 2) ?> BDT</p>
                                        <p><strong>Bank Out:</strong> <?= number_format($summary_data['cashbook']['bank_out'], 2) ?> BDT</p>
                                        <p><strong>Bank Balance:</strong> <?= number_format($summary_data['cashbook']['bank_in'] - $summary_data['cashbook']['bank_out'], 2) ?> BDT</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>