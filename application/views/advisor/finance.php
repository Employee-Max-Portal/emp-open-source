<?php $currency_symbol = '৳'; ?>

<!-- Finance Header -->
<div class="finance-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <h1 class="finance-title">
                    <i class="fas fa-chart-line"></i> Finance Overview
                </h1>
                <p class="finance-subtitle">Monitor fund requisitions and advance salary requests</p>
            </div>
            <div class="col-md-4 text-right">
                <div class="finance-stats">
                    <span class="stat-item">
                        <i class="fas fa-money-bill-wave text-warning"></i>
                        <strong><?= count($fund_requisitions ?? []) ?></strong> Fund Requests
                    </span>
                    <span class="stat-item">
                        <i class="fas fa-coins text-warning"></i>
                        <strong><?= count($advance_salaries ?? []) ?></strong> Advance Salary Requests
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Financial Section with Modern Tabs -->
<div class="finance-content">
    <div class="container-fluid">
        <div class="content-card">
            <div class="modern-tabs-wrapper">
                <!-- Date Range Filter -->
                <div class="filter-section">
                    <form method="POST" class="filter-form">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-4">
                                <div class="form-group">
                                    <label>Date Range</label>
                                    <input type="text" name="daterange" class="form-control" id="daterange" placeholder="Select date range" value="<?= $this->input->post('daterange') ?>">
									 <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" name="filter" class="btn btn-primary btn-block">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Modern Nav tabs -->
                <ul class="nav nav-tabs modern-nav-tabs" role="tablist">
                    <li role="presentation" class="active">
                        <a href="#fund-requisitions" aria-controls="fund-requisitions" role="tab" data-toggle="tab" class="modern-tab-link">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Fund Requisitions</span>
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#advance-salaries" aria-controls="advance-salaries" role="tab" data-toggle="tab" class="modern-tab-link">
                            <i class="fas fa-coins"></i>
                            <span>Advance Salaries</span>
                        </a>
                    </li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content modern-tab-content">
                    <!-- Fund Requisitions Tab -->
                    <div role="tabpanel" class="tab-pane active" id="fund-requisitions">
                        <div class="tab-header">
                            <h5><i class="fas fa-money-bill-wave text-primary"></i> Fund Requisition Records</h5>
                            <span class="record-count badge badge-info"><?= count($fund_requisitions ?? []) ?> Records</span>
                        </div>
                        
                        <?php if (!empty($fund_requisitions)): ?>
                        <div class="table-responsive">
                            <table class="table table-modern table-export" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="10%"><?=translate('photo')?></th>
                                        <th width="12%"><?=translate('employee')?></th>
                                        <th width="20%"><?=translate('reason')?></th>
                                        <th width="10%"><?=translate('category')?></th>
                                        <th width="10%"><?=translate('billing_type')?></th>
                                        <th width="10%" class="text-center"><?=translate('amount')?></th>
                                        <th width="10%" class="text-center"><?=translate('status')?></th>
                                        <th width="13%" class="text-center"><?=translate('disbursed date')?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $count = 1; foreach ($fund_requisitions as $fund): ?>
                                    <tr>
                                        <td><span class="row-number"><?php echo $count++; ?></span></td>
                                        <td class="text-center">
                                            <img class="employee-avatar" src="<?php echo get_image_url('staff', $fund->photo);?>" alt="<?= $fund->staff_name ?>" />
                                        </td>
                                        <td>
                                            <div class="employee-info">
                                                <strong><?= $fund->staff_name ?></strong>
                                                <small class="text-muted d-block">ID: <?= $fund->staff_id ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="dept-info">
                                                <?= translate($fund->reason) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="dept-info">
                                                <?= translate($fund->category) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="dept-info">
                                                <?= translate($fund->billing_type) ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="amount-display"><?php echo $currency_symbol . number_format($fund->amount);?></span>
                                        </td>
                                        <td class="text-center">
											<?php
												if ($fund->status == 1)
													$status = '<span class="label label-warning-custom badge-modern text-xs">' . translate('pending') . '</span>';
												else if ($fund->status  == 2)
													$status = '<span class="label label-success-custom badge-modern text-xs">' . translate('approved') . '</span>';
												else if ($fund->status  == 3)
													$status = '<span class="label label-danger-custom badge-modern text-xs">' . translate('rejected') . '</span>';
												else if ($fund->status  == 4)
													$status = '<span class="label label-danger-custom badge-modern text-xs">' . translate('penalty') . '</span>';
												echo ($status);
											?>
                                        </td>
                                        <td class="text-center">
                                            <span class="date-display">
                                                <?= $fund->status == '2' ? date('M d, Y', strtotime($fund->paid_date)) : '-' ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-money-bill-wave fa-3x text-muted"></i>
                            <h5 class="mt-3">No Fund Requisitions</h5>
                            <p class="text-muted">No fund requisition records found</p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Advance Salaries Tab -->
                    <div role="tabpanel" class="tab-pane" id="advance-salaries">
                        <div class="tab-header">
                            <h5><i class="fas fa-coins text-warning"></i> Advance Salary Records</h5>
                            <span class="record-count badge badge-info"><?= count($advance_salaries ?? []) ?> Records</span>
                        </div>
                        
                        <?php if (!empty($advance_salaries)): ?>
                        <div class="table-responsive">
                            <table class="table table-modern table-export" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="10%"><?=translate('photo')?></th>
                                        <th width="25%"><?=translate('employee')?></th>
                                        <th width="15%"><?=translate('dept')?></th>
                                        <th width="15%" class="text-center"><?=translate('amount')?></th>
                                        <th width="15%" class="text-center"><?=translate('status')?></th>
                                        <th width="15%" class="text-center"><?=translate('disbursed date')?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $count = 1; foreach ($advance_salaries as $advance): ?>
                                    <tr>
                                        <td><span class="row-number"><?php echo $count++; ?></span></td>
                                        <td class="text-center">
                                            <img class="employee-avatar" src="<?php echo get_image_url('staff', $advance->photo);?>" alt="<?= $advance->staff_name ?>" />
                                        </td>
                                        <td>
                                            <div class="employee-info">
                                                <strong><?= $advance->staff_name ?></strong>
                                                <small class="text-muted d-block">ID: <?= $advance->staff_id ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="dept-info">
                                                <i class="fas fa-building text-muted"></i>
                                                <?= html_escape($advance->department_name) ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="amount-display"><?php echo $currency_symbol . number_format($advance->amount) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php
												if ($advance->status == 1)
													$status = '<span class="label label-warning-custom badge-modern text-xs">' . translate('pending') . '</span>';
												else if ($advance->status  == 2)
													$status = '<span class="label label-success-custom badge-modern text-xs">' . translate('approved') . '</span>';
												else if ($advance->status  == 3)
													$status = '<span class="label label-danger-custom badge-modern text-xs">' . translate('rejected') . '</span>';
												else if ($advance->status  == 4)
													$status = '<span class="label label-danger-custom badge-modern text-xs">' . translate('penalty') . '</span>';
												echo ($status);
											?>
                                        </td>
                                        <td class="text-center">
                                            <span class="date-display">
                                                <?= $advance->status == '2' ? date('M d, Y', strtotime($advance->paid_date)) : '-' ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-coins fa-3x text-muted"></i>
                            <h5 class="mt-3">No Advance Salary Requests</h5>
                            <p class="text-muted">No advance salary records found</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Finance Page Styles */
.finance-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px 0;
    margin-bottom: 30px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.finance-title {
    font-size: 2.5rem;
    font-weight: 300;
    margin: 0;
}

.finance-subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 5px 0 0 0;
}

.finance-stats {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.stat-item {
    background: rgba(255,255,255,0.2);
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.finance-content {
    margin-bottom: 30px;
}

.content-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    overflow: hidden;
}

.modern-tabs-wrapper {
    padding: 0;
}

.modern-nav-tabs {
    border-bottom: 2px solid #e9ecef;
    background: #f8f9fa;
    margin: 0;
}

.modern-nav-tabs > li {
    margin-bottom: -2px;
}

.modern-tab-link {
    border: none !important;
    background: transparent !important;
    color: #6c757d !important;
    padding: 15px 20px !important;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.modern-nav-tabs > li.active > .modern-tab-link,
.modern-tab-link:hover {
    color: #495057 !important;
    background: white !important;
    border-bottom: 2px solid #667eea !important;
}

.modern-tab-content {
    padding: 25px;
}

.tab-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e9ecef;
}

.tab-header h5 {
    margin: 0;
    color: #495057;
    font-weight: 600;
}

.record-count {
    font-size: 1.2rem;
    padding: 4px 12px;
}

.table-modern {
    margin: 0;
}

.table-modern thead th {
    background-color: #f8f9fa;
    border-top: none;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    color: #495057;
    padding: 15px 12px;
}

.table-modern tbody td {
    padding: 15px 12px;
    vertical-align: middle;
    border-top: 1px solid #f1f3f4;
}

.table-modern tbody tr:hover {
    background-color: #f8f9fa;
}

.employee-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e9ecef;
}

.employee-info strong {
    color: #495057;
    font-size: 1.4rem;
}

.employee-info small {
    font-size: 1rem;
}

.dept-info {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #6c757d;
}

.amount-display {
    font-weight: 600;
    font-size: 1.2rem;
    color: #28a745;
}

.badge-modern {
    padding: 6px 12px;
    font-size: 1.2rem;
    font-weight: 500;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.date-display {
    font-size: 1.2rem;
    color: #6c757d;
}

.row-number {
    background: #e9ecef;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: 600;
    color: #495057;
    font-size: 1rem;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state i {
    opacity: 0.3;
}

.empty-state h5 {
    color: #6c757d;
    margin-top: 20px;
}

.filter-section {
    background: #f8f9fa;
    padding: 20px;
    border-bottom: 1px solid #e9ecef;
}

.filter-form .form-group {
    margin-bottom: 0;
}

.filter-form label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 5px;
}
</style>

<link rel="stylesheet" href="<?= base_url('assets/vendor/daterangepicker/daterangepicker.css') ?>">
<script src="<?= base_url('assets/vendor/moment/moment.js') ?>"></script>
<script src="<?= base_url('assets/vendor/daterangepicker/daterangepicker.js') ?>"></script>

<script>
$(document).ready(function() {
    $('#daterange').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear'
        }
    });
    
    $('#daterange').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
    });
    
    $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });
});
</script>