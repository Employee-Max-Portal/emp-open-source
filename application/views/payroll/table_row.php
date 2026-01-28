<?php 
static $i = 1;
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
    <td><strong><?= number_format($gross_salary, 2) . $currency_symbol; ?></strong></td>
    <td><strong><?= number_format($total_deduction, 2) . $currency_symbol; ?></strong></td>
    
    <td><?= number_format($advance, 2) . $currency_symbol; ?></td>
    <td><strong><?= number_format($net_salary, 2) . $currency_symbol; ?></strong></td>
    <td>
        <?php
        echo "<span class='label $labelMode'>$status_txt</span>";
        ?>
    </td>
    <td class="min-w-c text-center" data-staff-id="<?= $row->id; ?>">
        <?php if ($status == 'unpaid'): ?>
            <?php if ($has_adjustment && $adjustment_status == 1): ?>
                <span class="btn btn-success btn-circle mb-xs disabled" data-toggle="tooltip" data-original-title="<?= translate('verified'); ?>">
                    <i class="fas fa-check-circle"></i> <?= translate('verified'); ?>
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
                   data-original-title="<?= translate('verify'); ?>">
                    <i class="fas fa-check"></i> <?= translate('verify'); ?>
                </a>
                
            <?php endif; ?>
            <?php else: ?>
                <a href="javascript:void(0);" onclick="openAdjustmentModal(<?= $row->id; ?>, <?= $month; ?>, <?= $year; ?>)" 
                   class="btn btn-warning btn-circle mb-xs" 
                   data-toggle="tooltip" 
                   data-original-title="<?= translate('adjust_salary'); ?>">
                    <i class="fas fa-sliders-h"></i> <?= translate('adjust'); ?>
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
