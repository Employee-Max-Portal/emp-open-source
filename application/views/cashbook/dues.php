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
						<div class="col-md-offset-3 col-md-6 mb-sm">
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
				<h4 class="panel-title"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i> <?=translate('due_report')?></h4>
			</header>
			<div class="panel-body">
				<?php if (!empty($dues)): ?>
					<!-- Summary Cards -->
					<div class="row mb-lg">
						<div class="col-md-4">
							<div class="panel panel-featured panel-featured-danger">
								<div class="panel-body text-center">
									<h3 class="text-weight-bold text-color-danger"><?= number_format($total_due_amount, 2) ?> BDT</h3>
									<p class="text-color-dark"><?= translate('total_due_amount') ?></p>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="panel panel-featured panel-featured-primary">
								<div class="panel-body text-center">
									<h3 class="text-weight-bold text-color-primary"><?= number_format($cash_dues, 2) ?> BDT</h3>
									<p class="text-color-dark">Fully Due</p>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="panel panel-featured panel-featured-warning">
								<div class="panel-body text-center">
									<h3 class="text-weight-bold text-color-warning"><?= number_format($bank_dues, 2) ?> BDT</h3>
									<p class="text-color-dark">Partial Dues</p>
								</div>
							</div>
						</div>
					</div>

					<!-- Debug Info 
					<div class="alert alert-info">
						<strong>Total:</strong> Cash + Bank = <?= number_format($cash_dues + $bank_dues, 2) ?> BDT | 
						Difference = <?= number_format($total_due_amount - ($cash_dues + $bank_dues), 2) ?> BDT
					</div> -->

					<!-- Due Details Table -->
					<div class="table-responsive">
						<table class="table table-bordered table-hover table-condensed mb-none text-dark table-export" style="width: 100%; font-size: 12px;">
							<thead>
								<tr>
									<th><?=translate('sl')?></th>
									<th><?=translate('date')?></th>
									<th><?=translate('invoice_no')?></th>
									<th><?=translate('customer')?></th>
									<th><?=translate('phone')?></th>
									<th><?=translate('status')?></th>
									<th class="text-right"><?=translate('total_amount')?></th>
									<th class="text-right"><?=translate('paid_amount')?></th>
									<th class="text-right"><?=translate('due_amount')?></th>
									<th><?=translate('created_by')?></th>
								</tr>
							</thead>
							<tbody>
								<?php 
								$count = 1;
								foreach($dues as $due): ?>
								<tr>
									<td><?= $count++ ?></td>
									<td><?= date('m/d/Y h:i A', strtotime($due->date)) ?></td>
									<td><?= $due->invoice_no ?></td>
									<td><?= !empty($due->business_name) ? $due->business_name . ', ' . $due->customer_name : $due->customer_name ?></td>
									<td><?= $due->phone ?></td>
									<td>
										<?php if($due->status == 'partial'): ?>
											<span class="label label-warning">Partial</span>
										<?php else: ?>
											<span class="label label-danger">Due</span>
										<?php endif; ?>
									</td>
									<td class="text-right">৳ <?= number_format($due->total_amount, 2) ?></td>
									<td class="text-right">৳ <?= number_format($due->paid_amount, 2) ?></td>
									<td class="text-right">৳ <?= number_format($due->due_amount, 2) ?></td>
									<td><?= $due->created_by ?></td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php else: ?>
					<div class="alert alert-info">
						<i class="fas fa-info-circle"></i> <?= translate('no_dues_found') ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		// Initialize daterangepicker
		$('.daterange').daterangepicker({
			opens: 'left',
		    locale: {format: 'YYYY/MM/DD'}
		});

		// Initialize tooltips
		$('[data-toggle="tooltip"]').tooltip();
	});
</script>