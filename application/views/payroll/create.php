<?php $currency_symbol = $global_config['currency_symbol'];
/* echo "<pre>";
print_r ($staff);
echo "</pre>";  */?>
<section class="panel">
    <div class="panel-body">
        <div class="row mt-md">
            <div class="col-md-8">
                <section class="panel panel-custom">
                    <header class="panel-heading panel-heading-custom">
                        <h4 class="panel-title"><i class="fas fa-user-tag"></i> <?=translate('salary_details')?></h4>
                    </header>
                    <div class="panel-body panel-body-custom">
                        <div class="row mb-md">
                            <div class="col-md-3 mt-sm">
                                <center>
                                    <img class="img-thumbnail" width="132px" height="132px" src="<?=get_image_url('staff', $staff['photo'])?>">
                                </center>
                            </div>
                            <div class="col-md-7 mt-md">
                                <div class="table-responsive">
                                    <table class="table table-condensed text-dark mb-none">
                                        <tbody>
                                            <tr>
                                                <th class="top-b-none"><?=translate('name')?>:</th>
                                                <td class="top-b-none"><?=$staff['name'] ?></td>
                                            </tr>
                                            <tr>
                                                <th><?=translate('joining_date')?>:</th>
                                                <td><?=_d($staff['joining_date'])?></td>
                                            </tr>
                                            <tr>
                                                <th><?=translate('designation')?>:</th>
                                                <td><?=$staff['designation_name']?></td>
                                            </tr>
                                            <tr>
                                                <th><?=translate('department')?>:</th>
                                                <td><?=$staff['department_name']?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <hr class="solid mt-xs">
                        <div class="row">
                            <div class="col-md-offset-2 col-md-8">
                                <div class="table-responsive">
                                    <table class="table table-condensed text-dark">
                                        <tbody>
                                            <tr>
												<th class="top-b-none"><?=translate('salary_grade')?> :</th>
												<td class="top-b-none">
													<?php
													// Get all increments for this staff to show cumulative percentage
													$all_increments = $this->db->select('increment_percentage')
														->where('staff_id', $staff['id'])
														->order_by('increment_date', 'ASC')
														->get('salary_increments')
														->result_array();
													
													if (!empty($all_increments)) {
														$increment_parts = [];
														foreach ($all_increments as $inc) {
															$increment_parts[] = $inc['increment_percentage'] . '%';
														}
														echo $staff['template_name'] . ' + ' . implode(' + ', $increment_parts) . ' ' . translate('increment');
													} else {
														echo $staff['template_name'];
													}
													?>
												</td>
											</tr>

                                            <tr>
                                                <th><?=translate('basic_salary')?> :</td>
                                                <td><?=currencyFormat($staff['basic_salary'])?></td>
                                            </tr>
                                            <?php /* <tr>
                                                <th><?=translate('overtime')?> :</td>
                                                <td><?=currencyFormat($staff['overtime_salary'])?></td>
                                            </tr> */?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mt-lg">
                                <section class="panel">
                                    <header class="panel-heading">
                                        <h4 class="panel-title"><?=translate('allowances')?></h4>
                                    </header>
                                    <div class="panel-body">
                                        <div class="table-responsive text-dark">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th><?=translate('name'); ?></th>
                                                        <th class="text-right"><?=translate('amount')?></th>
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

                                                    if(count($allowances)){
                                                        foreach ($allowances as $allowance):
                                                        $total_allowance += floatval($allowance['amount']);
                                                        ?>
                                                            <tr>
                                                                <td><?=$allowance['name']; ?></td>
                                                                <td class="text-right"><?=currencyFormat($allowance['amount']); ?></td>
                                                            </tr>
                                                    <?php endforeach; } else {
                                                        echo '<tr> <td colspan="2"> <h5 class="text-danger text-center">' . translate('no_information_available') .  '</h5> </td></tr>';
                                                    }?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </section>
                            </div>
                            <div class="col-md-6 mt-lg">
                                <section class="panel">
                                    <header class="panel-heading">
                                        <h4 class="panel-title"><?=translate('deductions')?></h4>
                                    </header>
                                    <div class="panel-body">
                                        <div class="table-responsive text-dark">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th><?=translate('name'); ?></th>
                                                        <th class="text-right"><?=translate('amount')?></th>
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
															<td><?= $deduction['name'] ?></td>
															<td class="text-right"><?= currencyFormat($deduction['amount']) ?></td>
														</tr>
													<?php endforeach;
												}

												 if(!empty($advance_salary)){
                                                        $total_deduction += $advance_salary;
                                                        echo '<tr><td>Advance Salary</td><td class="text-right">' . currencyFormat($advance_salary) . '</td</tr>';
                                                    }
												?>
												
											</tbody>
											 <tbody id="deduction_items">
												<!-- These rows will be updated dynamically via JS -->
											</tbody>
											<tfoot>
												<tr>
													<th><?= translate('total') ?></th>
													<th class="text-right" id="deduction_total_display"><?= currencyFormat($total_deduction) ?></th>
												</tr>
											</tfoot>

                                            </table>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-md-4">
				<section class="panel panel-custom">
    			<?php if (!empty($staff['warnings']) && is_array($staff['warnings'])): ?>
						<?php
							$unresolved_warnings = array_filter($staff['warnings'], function($w) {
								return isset($w['status']) && $w['status'] == 1 ||  $w['status'] == 4 ;
							});
						?>
						<?php if (!empty($unresolved_warnings)): ?>
						<header class="panel-heading bg-danger text-white">
								<h4 class="panel-title">
									<i class="fas fa-exclamation-triangle"></i> <?= translate('unresolved_warnings') ?>
								</h4>
							</header>
							<div class="card mt-md mb-md border-danger">
								
								<div class="panel-body panel-body-custom">
									<div class="table-responsive">
										<table class="table table-bordered mb-0">
											<thead class="bg-light">
												<tr>
													<th><?= translate('refrence') ?></th>
													<th><?= translate('penalty_workday') ?></th>
													<th><?= translate('action') ?></th>
												</tr>
											</thead>
											<tbody>
												<?php foreach ($unresolved_warnings as $index => $warn): ?>
													<tr>
														<td><?= $warn['refrence'] ?></td>
													
														<form method="post" action="<?= base_url('todo/send_reminder') ?>" class="d-inline">
														<td>
															<?php
															try {
																 
															$datetime = new DateTime(); // current time
																
															} catch (Exception $e) {
																$datetime = new DateTime(); // fallback
															}

															$formatted_input = $datetime->format('Y-m-d\TH:i'); // input format for datetime-local
															$formatted_display = $datetime->format('h.iA \a\t d/m/Y'); // display format
															?>
															<input type="datetime-local" 
																   name="penalty_workday" 
																   class="form-control"
																   value="<?= $formatted_input ?>" />
															<small class="text-muted">
																<?= $formatted_display ?>
															</small>
														</td>
														<td>
															<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>" />
															<input type="hidden" name="warning_id" value="<?= $warn['id'] ?>" />
															<input type="hidden" name="staff_id" value="<?= $warn['user_id'] ?>" />
															<button type="submit" class="btn btn-sm btn-outline-warning">
																<i class="fas fa-bell"></i> <?= translate('reminder') ?>
															</button>
															</td>
														</form>

													</tr>
												<?php endforeach; ?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						<?php endif; ?>
					<?php endif; ?>
 
    				<header class="panel-heading panel-heading-custom">
    					<h4 class="panel-title"><i class="fas fa-stamp"></i> <?=translate('payment_details')?></h4>
    				</header>
					
                    <?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
                        <input type="hidden" name="branch_id" value="<?=$staff['branch_id']; ?>">
                        <input type="hidden" name="staff_id" value="<?=$staff['id']; ?>">
                        <input type="hidden" name="basic_salary" value="<?=$staff['basic_salary']; ?>">
                        <input type="hidden" name="salary_template_id" value="<?=$staff['salary_template_id']; ?>">
                        <input type="hidden" name="month" value="<?=$month; ?>">
                        <input type="hidden" name="year" value="<?=$year; ?>">

        				<div class="panel-body panel-body-custom">
						 
        					<div class="form-group">
        						<label class="control-label"><?=translate('total_allowance')?></label>
        						<input type="number" class="form-control" name="total_allowance" id="total_allowance" value="<?=$total_allowance; ?>" readonly />
        					</div>
							
							<div class="form-group">
								<label class="control-label"><?=translate('penalties')?></label>
								<table class="table table-bordered" id="penalties_table">
									<thead>
										<tr>
											<th><?=translate('type')?></th>
											<th><?=translate('value')?></th>
											<th><?=translate('action')?></th>
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
													<select class="form-control penalty_type" name="penalty[<?= $idx ?>][type]" readonly>
														<option value="<?= $penalty['type'] ?>" selected><?= $type_label ?></option>
													</select>
													<input type="hidden" class="form-control penalty_custom_name" name="penalty[<?= $idx ?>][custom_name]" value="<?= $penalty['custom_name'] ?? '' ?>" />
												</td>
												<td>
													<input type="number" class="form-control penalty_value" name="penalty[<?= $idx ?>][value]" min="0" value="<?= $penalty['value'] ?>" readonly />
												</td>
												<td>
													<button type="button" class="btn btn-danger btn-sm remove-penalty"><i class="fas fa-trash-alt"></i></button>
												</td>
											</tr>
										<?php 
												$idx++;
											}
										} else {
										?>
										<?php if (!empty($staff['late_count']) && $staff['late_count'] > 0): ?>
											<tr data-index="0">
												<td>
													<select class="form-control penalty_type" name="penalty[0][type]" readonly>
														<option value="attendance" selected><?=translate('attendance')?></option>
													</select>
													<input type="hidden" class="form-control penalty_custom_name" name="penalty[0][custom_name]" value="" />
												</td>
												<td>
													<input type="number" class="form-control penalty_value" name="penalty[0][value]" min="0" value="<?= $staff['late_count'] ?>" readonly />
												</td>
												<td>
													<button type="button" class="btn btn-danger btn-sm remove-penalty"><i class="fas fa-trash-alt"></i></button>
												</td>
											</tr>
											<?php endif; ?>
											
											<?php if (!empty($staff['absent_count']) && $staff['absent_count'] > 0): ?>
											<tr data-index="1">
												<td>
													<select class="form-control penalty_type" name="penalty[1][type]" readonly>
														<option value="absent" selected><?=translate('absent')?></option>
													</select>
													<input type="hidden" class="form-control penalty_custom_name" name="penalty[1][custom_name]" value="" />
												</td>
												<td>
													<input type="number" class="form-control penalty_value" name="penalty[1][value]" min="0" value="<?= $staff['absent_count'] ?>" readonly />
												</td>
												<td>
													<button type="button" class="btn btn-danger btn-sm remove-penalty"><i class="fas fa-trash-alt"></i></button>
												</td>
											</tr>
											<?php endif; ?>
											
											<?php if (!empty($staff['unpaid_leaves']) && $staff['unpaid_leaves'] > 0): ?>
											<tr data-index="2">
												<td>
													<select class="form-control penalty_type" name="penalty[2][type]" readonly>
														<option value="unpaid_leave" selected><?=translate('unpaid_leaves')?></option>
													</select>
													<input type="hidden" class="form-control penalty_custom_name" name="penalty[2][custom_name]" value="" />
												</td>
												<td>
													<input type="number" class="form-control penalty_value" name="penalty[2][value]" min="0" value="<?= $staff['unpaid_leaves'] ?>" readonly />
												</td>
												<td>
													<button type="button" class="btn btn-danger btn-sm remove-penalty"><i class="fas fa-trash-alt"></i></button>
												</td>
											</tr>

											<?php endif; ?>
										<?php } ?>

									</tbody>

								</table>
								<button type="button" class="btn btn-sm btn-default" id="add_penalty_btn"><i class="fas fa-plus"></i> <?=translate('add_penalty')?></button>
							</div>


							<div class="form-group" id="late_days_field" style="display:none;">
								<label class="control-label"><?=translate('late_entries')?></label>
								<input type="number" class="form-control" name="late_entries" id="late_entries" min="0" value="0" />
							</div>

							<div class="form-group" id="direct_penalty_field" style="display:none;">
								<label class="control-label"><?=translate('penalty_amount')?></label>
								<input type="number" class="form-control" name="direct_penalty" id="direct_penalty" value="0" />
							</div>


        					<div class="form-group">
        						<label class="control-label"><?=translate('total_deduction')?></label>
        						<input type="number" class="form-control" name="total_deduction" id="total_deduction" value="<?=$total_deduction; ?>" readonly />
        					</div>
        					<?php /* <div class="form-group">
        						<label class="control-label"><?=translate('overtime_total_hour'); ?></label>
        						<input type="number" class="form-control" id="overtime_total_hour" name="overtime_total_hour" value="<?=set_value('overtime_total_hour'); ?>" />
        					</div>
        					<div class="form-group">
        						<label class="control-label"><?=translate('overtime_amount')?></label>
        						<input type="number" class="form-control" id="overtime_amount" name="ov_amount" value="0" readonly />
                                <input type="hidden" id="overtimeamount" name="overtime_amount" value="0" />
        					</div> */ ?>
        					<?php
        						/* $salary = $staff['basic_salary'] + $total_allowance;
        						$net_salary = ($salary - $total_deduction); */
        					?>
        					<div class="form-group">
        						<label class="control-label"><?=translate('net_salary')?></label>
        						<input type="text" class="form-control" name="net_salary" id="net_salary" value="<?=$net_salary; ?>" readonly />
        					</div>
        					<div class="form-group">
        						<label class="control-label"><?=translate('payment_method')?> <span class="required">*</span></label>
        						<select name="pay_via" class="form-control" required data-plugin-selectTwo data-width="100%" data-minimum-results-for-search="Infinity">
        							<option value=""><?=translate('select')?></option>
        							<?php 
        							$account_types = $this->db->get('cashbook_accounts')->result_array();
        							foreach($account_types as $account): ?>
        								<option value="<?= $account['id'] ?>"><?= ucfirst($account['name']) ?></option>
        							<?php endforeach; ?>
        						</select>
        					</div>
                        <?php
                        $links = $this->payroll_model->get('transactions_links', array('branch_id' => $staff['branch_id']), true);
                        if ($links['status'] == 1) {
                        ?>
                            <div class="form-group">
                                <label class="control-label"><?php echo translate('account'); ?> <span class="required">*</span></label>
                                <?php
                                    $accounts_list = $this->app_lib->getSelectByBranch('accounts', $staff['branch_id']);
                                    echo form_dropdown("account_id", $accounts_list, $links['expense'], "class='form-control' id='account_id' required data-plugin-selectTwo data-width='100%'");
                                ?>
                            </div>
                        <?php } ?>
        					<div class="mb-lg">
        						<label class="control-label"><?=translate('remarks')?></label>
                                <textarea class="form-control" name="remarks" rows="2" maxlength="50"><?=set_value('remarks')?></textarea>
        					</div>
        				</div>
        				<div class="panel-footer panel-footer-custom">
        					<div class="row">
        						<div class="col-md-offset-6 col-md-6">
        							<!--<button type="submit" name="paid" value="1" class="btn btn-default btn-block"><?=translate('paid')?></button> -->
									<button type="submit" name="paid" value="1" class="btn btn-default btn-block" <?= (!empty($staff['warnings']) || !empty($staff['has_salary_block'])) ? 'disabled' : '' ?>>
									<?=translate('paid')?>
								</button>

        						</div>
        					</div>
        				</div>
                    <?php echo form_close(); ?>
    			</section>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
$(document).ready(function () {
    const per_hour = <?= floatval($staff['overtime_salary']); ?>;
    const base_allowance = <?= floatval($total_allowance); ?>;
    const base_deduction = <?= floatval($total_deduction); ?>;
    const base_net_salary = <?= floatval($staff['basic_salary'] + $total_allowance - $total_deduction); ?>;
    const attendance_penalty = <?= floatval($staff['attendance_penalty']); ?>;

    let penaltyIndex = 0;

    function getPenaltyRow(index) {
        return `
        <tr data-index="${index}">
            <td>
                <select class="form-control penalty_type" name="penalty[${index}][type]">
                    <option value=""><?=translate('select')?></option>
                    <option value="attendance"><?=translate('attendance')?></option>
                    <option value="rules"><?=translate('rules_violation')?></option>
                    <option value="others"><?=translate('others')?></option>
                </select>
                <input type="text" class="form-control mt-1 penalty_custom_name" name="penalty[${index}][custom_name]" placeholder="<?=translate('enter_custom_name')?>" style="display:none;" />
            </td>
            <td>
                <input type="number" class="form-control penalty_value" name="penalty[${index}][value]" min="0" value="0" />
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-penalty"><i class="fas fa-trash-alt"></i></button>
            </td>
        </tr>
        `;
    }

    function recalculateSalary() {
        let overtime_hour = parseFloat($('#overtime_total_hour').val()) || 0;
        let overtime_amount = overtime_hour * per_hour;

        let advance_salary = parseFloat($('#advance_salary').val()) || 0;

        let penalty_amount = 0;
        let deductionHTML = '';
        let penaltyDescriptions = [];

        // Loop through penalty rows
        $('#penalties_table tbody tr').each(function () {
            let type = $(this).find('.penalty_type').val();
            let value = parseFloat($(this).find('.penalty_value').val()) || 0;
            let customName = $(this).find('.penalty_custom_name').val();
            let label = '';
			
			if (type === 'absent') {
				let absentCount = parseInt(value);
				let base_salary = <?= floatval($staff['basic_salary']) ?>;
				let calculated = absentCount * (base_salary / 30);
				penalty_amount += calculated;
				let dayLabel = absentCount === 1 ? '<?= translate('day') ?>' : '<?= translate('days') ?>';
				let label = '<?= translate('absent_penalty') ?> - ' + absentCount + ' ' + dayLabel;
				penaltyDescriptions.push({ label: label, value: calculated });
			}

			else if (type === 'unpaid_leave') {
				let unpaidCount = parseInt(value);
				let base_salary = <?= floatval($staff['basic_salary']) ?>;
				let calculated = unpaidCount * (base_salary / 30);
				penalty_amount += calculated;
				let dayLabel = unpaidCount === 1 ? '<?= translate('day') ?>' : '<?= translate('days') ?>';
				let label = '<?= translate('unpaid_leave') ?> - ' + unpaidCount + ' ' + dayLabel;
				penaltyDescriptions.push({ label: label, value: calculated });
			}


            else if (type === 'attendance') {
					let penalty_count = Math.floor(value / 3);
					let base_salary = <?= isset($staff['increment_percentage']) ? floatval($staff['basic_salary']) : floatval($staff['basic_salary']) ?>;
					let calculated = penalty_count * (base_salary / 30);
					penalty_amount += calculated;
					let lateCount = <?= (int)($staff['late_count'] ?? 0); ?>;
					let dayLabel = lateCount === 1 ? '<?= translate('day') ?>' : '<?= translate('days') ?>';
					let label = '<?= translate('late_attendance') ?> - ' + lateCount + ' ' + dayLabel;
					penaltyDescriptions.push({ label: label, value: calculated });
				}else if (type === 'rules') {
                penalty_amount += value;
                label = '<?=translate('rules_violation')?>';
                penaltyDescriptions.push({ label: label, value: value });
            } else if (type === 'others') {
                penalty_amount += value;
                label = customName || '<?=translate('others_penalty')?>';
                penaltyDescriptions.push({ label: label, value: value });
            }
        });

        // Totals
        let total_allowance = base_allowance + overtime_amount;
        let total_deduction = base_deduction + advance_salary + penalty_amount;
        let net_salary = (base_net_salary + overtime_amount) - (advance_salary + penalty_amount);

        // Update input fields
		 const currencySymbol = "<?= $currency_symbol ?>";
        $('#overtime_amount').val(overtime_amount.toFixed(2));
        $('#overtimeamount').val(overtime_amount.toFixed(2));
        $('#total_allowance').val(total_allowance.toFixed(2));
        $('#total_deduction').val(total_deduction.toFixed(2));
        $('#net_salary').val(net_salary.toFixed(2));

        // Update deduction rows
        <?php foreach ($deductions as $deduction): ?>
        deductionHTML += `<tr><td><?= $deduction['name'] ?></td><td class="text-right"><?= currencyFormat($deduction['amount']) ?></td></tr>`;
        <?php endforeach; ?>

        if (advance_salary > 0) {
            deductionHTML += `<tr><td><?= translate('advance_salary') ?></td><td class="text-right">${advance_salary.toFixed(2)}</td></tr>`;
        }

        penaltyDescriptions.forEach(function (penalty) {
            deductionHTML += `<tr><td>${penalty.label}</td><td class="text-right">${currencySymbol + penalty.value.toFixed(2)}</td></tr>`;

        });

       
        $('#deduction_items').html(deductionHTML);
        $('#deduction_total_display').text(currencySymbol + total_deduction.toFixed(2));
    }

    // Add new penalty row
    $('#add_penalty_btn').on('click', function () {
        $('#penalties_table tbody').append(getPenaltyRow(penaltyIndex++));
    });

    // Remove a penalty row
    $('#penalties_table').on('click', '.remove-penalty', function () {
        $(this).closest('tr').remove();
        recalculateSalary();
    });

    // Show/hide custom name input based on type
    $('#penalties_table').on('change', '.penalty_type', function () {
        const type = $(this).val();
        const customInput = $(this).closest('td').find('.penalty_custom_name');
        if (type === 'others') {
            customInput.show();
        } else {
            customInput.hide().val('');
        }
        recalculateSalary();
    });

    // Trigger recalculation on value input
    $('#penalties_table').on('input change', '.penalty_value, .penalty_custom_name', function () {
        recalculateSalary();
    });

    // Other triggers
    $('#overtime_total_hour, #advance_salary').on('input change', function () {
        recalculateSalary();
    });

    // Initial call
    recalculateSalary();
});
</script>

