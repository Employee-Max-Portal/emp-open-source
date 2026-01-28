<div class="row">
	<div class="col-md-12">
		 <!-- Filter Section -->
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><?= translate('filter_options') ?></h4>
			</header>
			<?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
				<div class="panel-body">
					<div class="row mb-sm">
						<div class="col-md-3 mb-sm">
							<div class="form-group">
								<label class="control-label"><?= translate('branch') ?></label>
								<select name="branch_id" class="form-control">
									<option value=""><?= translate('all_branches') ?></option>
									<?php foreach ($branches as $branch): ?>
										<option value="<?= $branch->id ?>" <?= $this->input->post('branch_id') == $branch->id ? 'selected' : '' ?>>
											<?= $branch->name ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="col-md-3 mb-sm">
							<div class="form-group">
								<label class="control-label"><?= translate('type') ?></label>
								<select name="type" class="form-control">
									<option value=""><?= translate('all_types') ?></option>
									<option value="fund_requisition" <?= $this->input->post('type') == 'fund_requisition' ? 'selected' : '' ?>><?= translate('fund_requisition') ?></option>
									<option value="advance_salary" <?= $this->input->post('type') == 'advance_salary' ? 'selected' : '' ?>><?= translate('advance_salary') ?></option>
									<option value="salary_payment" <?= $this->input->post('type') == 'salary_payment' ? 'selected' : '' ?>><?= translate('salary_payment') ?></option>
								</select>
							</div>
						</div>
						<div class="col-md-6 mb-sm">
							<div class="form-group">
								<label class="control-label"><?= translate('date_range') ?> <span class="required">*</span></label>
								<div class="input-group">
									<span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
									<input type="text" class="form-control daterange" name="daterange" value="<?= set_value('daterange', (!empty($this->input->post('daterange'))) ? $this->input->post('daterange') : date('Y/m/d', strtotime('-30day')) . ' - ' . date('Y/m/d')) ?>" required />
								</div>
							</div>
						</div>
					</div>
				</div>
				<footer class="panel-footer">
					<div class="row">
						<div class="col-md-offset-10 col-md-2">
							<button type="submit" name="search" value="1" class="btn btn-default btn-block">
								<i class="fas fa-filter"></i> <?= translate('filter') ?>
							</button>
						</div>
					</div>
				</footer>
			<?php echo form_close(); ?>
		</section>

		<section class="panel appear-animation" data-appear-animation="<?=$global_config['animations'] ?>" data-appear-animation-delay="100">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-chart-bar" aria-hidden="true"></i> <?=translate('all_expense_list')?></h4>
			</header>
			<div class="panel-body">
				<?php if (!empty($expenses)): ?>
					<!-- Summary Cards -->
					<div class="row mb-lg">
						<div class="col-md-3">
							<div class="panel panel-featured panel-featured-primary">
								<div class="panel-body text-center">
									<h3 class="text-weight-bold text-color-primary"><?= number_format($total_amount, 2) ?> BDT</h3>
									<p class="text-color-dark"><?= translate('total_expenses') ?></p>
								</div>
							</div>
						</div>
						<div class="col-md-3">
							<div class="panel panel-featured panel-featured-warning">
								<div class="panel-body text-center">
									<h3 class="text-weight-bold text-color-warning"><?= number_format($fund_requisition_total, 2) ?> BDT</h3>
									<p class="text-color-dark"><?= translate('fund_requisition_expense') ?></p>
								</div>
							</div>
						</div>
						<div class="col-md-3">
							<div class="panel panel-featured panel-featured-danger">
								<div class="panel-body text-center">
									<h3 class="text-weight-bold text-color-danger"><?= number_format($advance_salary_total, 2) ?> BDT</h3>
									<p class="text-color-dark"><?= translate('advance_salary_expense') ?></p>
								</div>
							</div>
						</div>
						<div class="col-md-3">
							<div class="panel panel-featured panel-featured-info">
								<div class="panel-body text-center">
									<h3 class="text-weight-bold text-color-info"><?= number_format($salary_payment_total, 2) ?> BDT</h3>
									<p class="text-color-dark"><?= translate('salary_payment_expense') ?></p>
								</div>
							</div>
						</div>
					</div>

					<!-- Expense Details Table -->
					<div class="table-responsive">
						<table class="table table-bordered table-hover table-condensed mb-none text-dark table-export" style="width: 100%;">
							<thead>
								<tr>
									<th width="10"><?=translate('sl')?></th>
									<th width="60"><?=translate('date')?></th>
									<th width="80"><?=translate('type')?></th>
									<th width="100"><?=translate('employee')?></th>
									<th><?=translate('description')?></th>
									<th  width="100" class="text-center"><?=translate('amount')?></th>
								</tr>
							</thead>
							<tbody>
								<?php 
								$count = 1;
								foreach($expenses as $expense): 
								?>
								<tr>
									<td><?= $count++ ?></td>
									<td><?= date('d M Y', strtotime($expense->date)) ?></td>
									<td>
										<span class="badge" style="padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 500; 
											<?php if($expense->type == 'Fund Requisition'): ?>
												background-color: #fff3cd; color: #856404; border: 1px solid #ffeaa7;
											<?php elseif($expense->type == 'Advance Salary'): ?>
												background-color: #d1ecf1; color: #0c5460; border: 1px solid #b8daff;
											<?php else: ?>
												background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;
											<?php endif; ?>">
											<?= $expense->type ?>
										</span>
									</td>
									<td><?= $expense->employee_name ?></td>
									<td><?= $expense->description ?></td>
									<td class="text-center">
										<strong><?= number_format($expense->amount, 2) ?> BDT</strong>
									</td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php else: ?>
					<div class="alert alert-info">
						<i class="fas fa-info-circle"></i> <?= translate('no_expense_found') ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
	</div>
</div>