<?php $widget = ((in_array(loggedin_role_id(), [1, 2, 3, 5]))? 4 : 6); 

$currency_symbol = isset($global_config['currency_symbol']) ? $global_config['currency_symbol'] : '৳'; // Default fallback
$Role_id = loggedin_role_id();
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
								<label class="control-label">
									<?php echo translate('business'); ?> <span class="required">*</span>
								</label>
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
								<label class="control-label">
									<?php echo translate('role'); ?> <span class="required">*</span>
								</label>

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

        <?php if(isset($stafflist)): 
		/* echo "<pre>";
		print_r ($stafflist);
		echo "</pre>"; */?>
            <section class="panel appear-animation" data-appear-animation="<?=$global_config['animations'] ?>" data-appear-animation-delay="100">
                <header class="panel-heading">
                    <h4 class="panel-title"><?php echo translate('employee') . " " . translate('list'); ?></h4>
                </header>
                <div class="panel-body">
                    <div class="mb-sm mt-xs">
                        <table class="table table-bordered table-hover table-condensed table-export" cellspacing="0" width="100%">
							<thead>
								<tr>
									<th><?= translate('SL'); ?></th>
									<th><?= translate('name'); ?></th>
									<th><?= translate('designation'); ?></th>
									<th><?= translate('bank_info'); ?></th>
									<th><?= translate('gross_salary'); ?></th>
									<th><?= translate('basic_salary'); ?></th>
									<th><?= translate('total_allowance'); ?></th>
									<th><?= translate('total_earnings'); ?></th>
									<th><?= translate('total_deduction'); ?></th>
									
									<th><?= translate('add_adjustment'); ?>
									<th><?= translate('net_pay'); ?></th>
									<th><?= translate('status'); ?></th>
									<th><?= translate('action'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php $i = 1; foreach ($stafflist as $row): ?>
									<?php
										// Check for adjustment
										$adjustment = $this->db->get_where('salary_adjustments', [
											'staff_id' => $row->id,
											'month' => $month,
											'year' => $year
										])->row();
										
										$advance = (float)($row->advance_amount ?? 0);
										if ($adjustment) {
											$basic = (float)$adjustment->basic_salary;
											$total_allowance = (float)$adjustment->total_allowance;
											$gross_salary = $basic + $total_allowance;
											$total_deduction = (float)$adjustment->total_deduction;
											$net_salary = (float)$adjustment->net_salary;
											$has_adjustment = true;
											$adjustment_status = isset($adjustment->status) ? (int)$adjustment->status : 0;
											$adjustment_id = $adjustment->id;
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
											$has_adjustment = false;
											$adjustment_status = 0;
											$adjustment_id = 0;
										}

										$bank = $row->bank_account ?? [];
									?>
									<tr>
										<td><?= $i++; ?></td>
										<td><?= $row->staff_id . ' - ' . $row->name; ?></td>
										<td><?= $row->designation_name; ?></td>
										<td>
											<?php if (!empty($bank)): ?>
												<?= $bank['account_no']; ?>
											<?php endif; ?>
										</td>
										<td><strong><?= number_format($gross_salary, 2) . $currency_symbol; ?></strong></td>
										<td><?= number_format($basic, 2) . $currency_symbol; ?></td>
										<td><?= number_format($total_allowance, 2) . $currency_symbol; ?></td>
										<td><strong><?=number_format($gross_salary, 2) . $currency_symbol; ?></strong></td>
										<td><strong><?= number_format($total_deduction, 2) . $currency_symbol; ?></strong></td>
										
										<td><?= number_format($advance, 2) . $currency_symbol; ?></td>
										<td><strong><?= number_format($net_salary, 2) . $currency_symbol; ?></strong></td>
										<td>
											<?php
												$status = ($row->salary_id == 0 ? 'unpaid' : 'paid');
												$labelMode = $status == 'paid' ? 'label-success-custom' : 'label-info-custom';
												$status_txt = $status == 'paid' ? translate('salary') . " " . translate('paid') : translate('salary') . " " . translate('unpaid');
												echo "<span class='label $labelMode'>$status_txt</span>";
											?>
										</td>
										<td class="min-w-c text-center" data-staff-id="<?= $row->id; ?>">
											<?php if ($status == 'unpaid'): ?>
												<?php if ($has_adjustment && $adjustment_status == 1): ?>
													<span class="btn btn-success btn-circle mb-xs disabled" data-toggle="tooltip" data-original-title="<?= translate('approved'); ?>">
														<i class="fas fa-check-circle"></i> <?= translate('approved'); ?>
													</span>
												<?php elseif ($has_adjustment && $adjustment_status == 0): ?>
													<a href="javascript:void(0);" onclick="openAdjustmentModal(<?= $row->id; ?>, <?= $month; ?>, <?= $year; ?>)" 
													   class="btn btn-success btn-circle mb-xs" 
													   data-toggle="tooltip" 
													   data-original-title="<?= translate('adjust_salary'); ?>">
														<i class="fas fa-sliders-h"></i> <?= translate('adjusted'); ?>
													</a>
													<?php if (in_array($Role_id, [1, 2, 3])): ?>
													<a href="javascript:void(0);" onclick="verifyAdjustment(<?= $adjustment_id; ?>, <?= $row->id; ?>, <?= $month; ?>, <?= $year; ?>)" 
													   class="btn btn-info btn-circle mb-xs" 
													   data-toggle="tooltip" 
													   data-original-title="<?= translate('approve'); ?>">
														<i class="fas fa-check"></i> <?= translate('approve'); ?>
													</a>
													
												<?php endif; ?>
												<?php else: ?>
													<a href="javascript:void(0);" onclick="openAdjustmentModal(<?= $row->id; ?>, <?= $month; ?>, <?= $year; ?>)" 
													   class="btn btn-warning btn-circle mb-xs" 
													   data-toggle="tooltip" 
													   data-original-title="<?= translate('adjust_salary'); ?>">
														<i class="fas fa-sliders-h"></i> <?= translate('approve'); ?>
													</a>
												<?php endif; ?>
											<?php endif; ?>

											<?php if ($status == 'paid'): ?>
												<a href="<?= base_url('payroll/invoice/' . $row->salary_id . '/' . $row->salary_hash); ?>" 
												   class="btn btn-default btn-circle mb-xs" 
												   data-toggle="tooltip" 
												   data-original-title="<?= translate('view_payslip'); ?>">
													<i class="fas fa-eye"></i> <?= translate('payslip'); ?>
												</a>
											<?php elseif ($has_adjustment && $adjustment_status == 1): ?>
												<a href="javascript:void(0);" onclick="openPaymentModal(<?= $row->id; ?>, <?= $month; ?>, <?= $year; ?>)" 
												   class="btn btn-default btn-circle mb-xs" 
												   data-toggle="tooltip" 
												   data-original-title="<?= translate('pay_now'); ?>">
													<i class="far fa-credit-card"></i> <?= translate('pay_now'); ?>
												</a>
											<?php else: ?>
												<a href="javascript:void(0);" 
												   class="btn btn-default btn-circle mb-xs disabled" 
												   data-toggle="tooltip" 
												   data-original-title="<?= translate('pay_now'); ?>">
													<i class="far fa-credit-card"></i> <?= translate('pay_now'); ?>
												</a>
											<?php endif; ?>
										</td>

									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
                    </div>
                </div>
            </section>
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
<div class="zoom-anim-dialog modal-block modal-block-lg mfp-hide" id="modal_adjustment" style="max-width: 900px!important;">
    <section class="panel">
        <header class="panel-heading">
            <h4 class="panel-title"><i class="fas fa-sliders-h"></i> <?= translate('salary_adjustment'); ?></h4>
        </header>
        <div class="panel-body" id="adjustment_content">
            <!-- Content loaded via AJAX -->
        </div>
    </section>
</div>

<div class="zoom-anim-dialog modal-block modal-block-lg mfp-hide" id="modal_payment" style="max-width: 600px!important;">
    <section class="panel">
        <header class="panel-heading">
            <h4 class="panel-title"><i class="far fa-credit-card"></i> <?= translate('pay_now'); ?></h4>
        </header>
        <div class="panel-body" id="payment_content">
            <!-- Content loaded via AJAX -->
        </div>
    </section>
</div>

<script>
var currentBranchId = '<?= isset($_POST['branch_id']) ? $_POST['branch_id'] : 'all'; ?>';

function openAdjustmentModal(staff_id, month, year) {
    $.ajax({
        url: base_url + 'payroll/get_adjustment_form',
        type: 'POST',
        data: { 
            staff_id: staff_id, 
            month: month, 
            year: year 
        },
        success: function(response) {
            $('#adjustment_content').html(response);
            $.magnificPopup.open({
                items: { src: '#modal_adjustment' },
                type: 'inline',
                callbacks: {
                    open: function() {
                        initAdjustmentForm(staff_id, month, year);
                    }
                }
            });
        },
        error: function() {
            alert('Failed to load adjustment form.');
        }
    });
}

function initAdjustmentForm(staff_id, month, year) {
    $(document).off('submit', '#adjustment_form').on('submit', '#adjustment_form', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        
        $.ajax({
            url: base_url + 'payroll/save_adjustment',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    $.magnificPopup.close();
                    updateTableRow(staff_id, month, year);
                    swal('Success', response.message || 'Adjustment saved successfully', 'success');
                } else {
                    $.magnificPopup.close();
                    swal('Error', response.message || 'Failed to save adjustment', 'error');
                }
            },
            error: function() {
                $.magnificPopup.close();
                swal('Error', 'Failed to save adjustment', 'error');
            }
        });
    });
}

function verifyAdjustment(adjustment_id, staff_id, month, year) {
    swal({
        title: 'Are you sure?',
        text: 'Do you want to verify this adjustment?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, verify it!',
        cancelButtonText: 'Cancel'
    }).then(function(result) {
        if (result.value) {
            $.ajax({
                url: base_url + 'payroll/verify_adjustment/' + adjustment_id,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'success') {
                        updateTableRow(staff_id, month, year);
                        swal('Verified!', response.message || 'Adjustment verified successfully', 'success');
                    } else {
                        swal('Error', response.message || 'Failed to verify adjustment', 'error');
                    }
                },
                error: function() {
                    swal('Error', 'Failed to verify adjustment', 'error');
                }
            });
        }
    });
}

function updateTableRow(staff_id, month, year) {
    $.ajax({
        url: base_url + 'payroll/get_table_row',
        type: 'POST',
        data: { 
            staff_id: staff_id, 
            month: month, 
            year: year,
            branch_id: currentBranchId
        },
        success: function(response) {
            $('td[data-staff-id="' + staff_id + '"]').closest('tr').replaceWith(response);
        },
        error: function() {
            console.error('Failed to update table row');
        }
    });
}

function openPaymentModal(staff_id, month, year) {
    $.ajax({
        url: base_url + 'payroll/get_payment_form',
        type: 'POST',
        data: { 
            staff_id: staff_id, 
            month: month, 
            year: year 
        },
        success: function(response) {
            $('#payment_content').html(response);
            $.magnificPopup.open({
                items: { src: '#modal_payment' },
                type: 'inline',
                callbacks: {
                    open: function() {
                        setTimeout(function() {
                            initPaymentForm(staff_id, month, year);
                        }, 100);
                    }
                }
            });
        },
        error: function() {
            alert('Failed to load payment form.');
        }
    });
}

function initPaymentForm(staff_id, month, year) {
    $('#payment_form').off('submit').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize() + '&paid=1';
        
        $.ajax({
            url: base_url + 'payroll/process_payment',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    $.magnificPopup.close();
                    updateTableRow(staff_id, month, year);
                    swal('Success', response.message || 'Payment processed successfully', 'success');
                } else {
                    swal('Error', response.message || 'Failed to process payment', 'error');
                }
            },
            error: function() {
                swal('Error', 'Failed to process payment', 'error');
            }
        });
    });
}

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
</script>

<style>
.panel-body-custom { padding: 20px; }
.panel-heading-custom { background: #f5f5f5; padding: 10px 20px; }
</style>
