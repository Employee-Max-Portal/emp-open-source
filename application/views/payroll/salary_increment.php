<div class="row">
	<div class="col-md-12">
		<section class="panel appear-animation" data-appear-animation="fadeInUp" data-appear-animation-delay="100">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-layer-group"></i> <?=translate('salary_increment_list')?></h4>
				<?php if (get_permission('salary_increment', 'is_add')): ?>
				<div class="panel-btn">
					<a href="javascript:void(0);" id="addIncrement" class="btn btn-default btn-circle">
						<i class="fas fa-plus-circle"></i> <?=translate('add')?>
					</a>
				</div>
				<?php endif; ?>
			</header>

			<div class="panel-body">
				<table class="table table-bordered table-hover table-condensed table-export">
					<thead>
						<tr>
							<th><?=translate('sl')?></th>
							<th><?=translate('employee')?></th>
							<th><?=translate('old_salary')?></th>
							<th><?=translate('increment_%')?></th>
							<th><?=translate('increment_amount')?></th>
							<th><?=translate('new_salary')?></th>
							<th><?=translate('effective_date')?></th>
							<th><?=translate('reason')?></th>
							<?php if (get_permission('salary_increment', 'is_edit') || get_permission('salary_increment', 'is_delete')): ?>
							<th><?=translate('action')?></th>
							<?php endif; ?>
						</tr>
					</thead>
					<tbody>
						<?php $count = 1; if (count($increments)) {
							foreach ($increments as $row): ?>
							<tr>
								<td><?= $count++; ?></td>
								<td><?= $row->staff_id.' - '.$row->name; ?></td>
								<td><?= number_format($row->old_salary, 2); ?></td>
								<td><?= $row->increment_percentage ?>%</td>
								<td><?= number_format($row->increment_amount, 2); ?></td>
								<td><?= number_format($row->new_salary, 2); ?></td>
								<td><?= date('d M, Y', strtotime($row->increment_date)); ?></td>
								<td><?= $row->reason; ?></td>
								<?php if (get_permission('salary_increment', 'is_edit') || get_permission('salary_increment', 'is_delete')): ?>
								<td>
									<!--<?php if (get_permission('salary_increment', 'is_edit')): ?>
										<a class="btn btn-default btn-circle icon" href="javascript:void(0);"  onclick="getIncrementEdit('<?=$row->id?>')">
											<i class="fas fa-pen"></i>
										</a>
									<?php endif; ?> -->
									<?php if (get_permission('salary_increment', 'is_delete')): ?>
										<?= btn_delete('payroll/delete_salary_increment/' . $row->id); ?>
									<?php endif; ?>
								</td>
								<?php endif; ?>
							</tr>
						<?php endforeach; } ?>
					</tbody>
				</table>
			</div>
		</section>
	</div>
</div>

<!-- Add/Edit Modal -->
<div id="incrementModal" class="zoom-anim-dialog modal-block mfp-hide modal-block-lg">
	<section class="panel">
		<header class="panel-heading">
			<h4 class="panel-title"><i class="fas fa-plus-circle"></i> <?=translate('add_salary_increment')?></h4>
		</header>
		<?= form_open('payroll/save_salary_increment', ['class' => 'form-horizontal', 'id' => 'incrementForm']) ?>
		<div class="panel-body">
			<input type="hidden" name="id" id="increment_id" />

			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('employee')?> <span class="required">*</span></label>
				<div class="col-md-9">
					<?php
						$this->db->select('s.id, s.name');
						$this->db->from('staff AS s');
						$this->db->join('login_credential AS lc', 'lc.user_id = s.id');
						$this->db->where('lc.active', 1);   // only active users
						$this->db->where_not_in('lc.role', [1, 9]);   // exclude super admin, etc.
						$this->db->order_by('s.name', 'ASC');
						$query = $this->db->get();

						$staffArray = ['' => 'Select']; // <-- default first option
						foreach ($query->result() as $row) {
							$staffArray[$row->id] = $row->name;
						}

						echo form_dropdown("staff_id", $staffArray, "", "class='form-control' id='staff_id' data-plugin-selectTwo required data-width='100%'");
					?>
				</div>
			</div>

			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('old_salary')?> (<?=translate('gross')?>)</label>
				<div class="col-md-9">
					<input type="text" class="form-control" id="old_salary" readonly />
				</div>
			</div>

			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('increment_%')?> <span class="required">*</span></label>
				<div class="col-md-9">
					<input type="number" step="0.01" class="form-control" id="increment_percentage" name="increment_percentage" />
				</div>
			</div>

			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('increment_amount')?> <span class="required">*</span></label>
				<div class="col-md-9">
					<input type="number" step="0.01" class="form-control" id="increment_amount" name="increment_amount" required />
				</div>
			</div>


			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('new_salary')?> (<?=translate('gross')?>)</label>
				<div class="col-md-9">
					<input type="text" class="form-control" id="new_salary" readonly />
				</div>
			</div>

			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('salary_breakdown')?></label>
				<div class="col-md-9" id="salaryBreakdown" style="background:#f9f9f9; padding:10px; border-radius:5px;">
					<em><?=translate('select_an_employee_to_view_details')?></em>
				</div>
			</div>

			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('effective_date')?> <span class="required">*</span></label>
				<div class="col-md-9">
					<input type="date" name="increment_date" class="form-control" id="increment_date" required />
				</div>
			</div>

			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('reason')?></label>
				<div class="col-md-9">
					<textarea name="reason" class="form-control" id="reason" rows="3"></textarea>
				</div>
			</div>
		</div>
		<footer class="panel-footer">
			<div class="row">
				<div class="col-md-12 text-right">
					<button type="submit" class="btn btn-default" id="saveBtn">
						<i class="fas fa-save"></i> <?=translate('save')?>
					</button>
					<button type="button" class="btn btn-default modal-dismiss"><?=translate('cancel')?></button>
				</div>
			</div>
		</footer>
		<?= form_close(); ?>
	</section>
</div>

<!-- JS -->
<script type="text/javascript">
$('#addIncrement').on('click', function () {
	$('#incrementForm')[0].reset();
	$('#increment_id').val('');
	$('#salaryBreakdown').html('<em><?=translate('select_an_employee_to_view_details')?></em>');
	mfp_modal('#incrementModal');
});

$('#staff_id').on('change', function () {
	let staffId = $(this).val();
	if (staffId) {
		$.ajax({
			url: base_url + 'payroll/get_staff_salary_details/' + staffId,
			dataType: 'json',
			success: function (data) {
				const gross = parseFloat(data.gross_salary);
				const basic = parseFloat(data.basic_salary);

				$('#old_salary').val(gross.toFixed(2));
				$('#increment_percentage').trigger('keyup');

				let breakdown = '<ul class="mb-none">';
				breakdown += `<li><strong>Basic Salary:</strong> ${basic.toFixed(2)}</li>`;
				if (data.salary_details.length > 0) {
					data.salary_details.forEach(function (item) {
						breakdown += `<li>${item.name}: ${parseFloat(item.amount).toFixed(2)}</li>`;
					});
				}
				breakdown += '</ul>';
				$('#salaryBreakdown').html(breakdown);
			}
		});
	}
});

// Trigger both fields
$('#increment_percentage').on('keyup change', function () {
	const gross = parseFloat($('#old_salary').val());
	const percentage = parseFloat($(this).val());

	if (!isNaN(gross) && !isNaN(percentage)) {
		const amount = (gross * percentage) / 100;
		const newSalary = gross + amount;
		$('#increment_amount').val(amount.toFixed(2));
		$('#new_salary').val(newSalary.toFixed(2));
	}
});

$('#increment_amount').on('keyup change', function () {
	const gross = parseFloat($('#old_salary').val());
	const amount = parseFloat($(this).val());

	if (!isNaN(gross) && !isNaN(amount) && gross > 0) {
		const percentage = (amount / gross) * 100;
		const newSalary = gross + amount;
		$('#increment_percentage').val(percentage.toFixed(2));
		$('#new_salary').val(newSalary.toFixed(2));
	}
});

function getIncrementEdit(id) {
	$.ajax({
		url: base_url + "payroll/get_salary_increment_edit/" + id,
		dataType: 'json',
		success: function (data) {
			$('#increment_id').val(data.id);
			$('#staff_id').val(data.staff_id).trigger('change');
			$('#increment_percentage').val(data.increment_percentage);
			$('#increment_amount').val(data.increment_amount);
			$('#increment_date').val(data.increment_date);
			$('#reason').val(data.reason);
			
			// Set old salary and breakdown from edit data
			setTimeout(() => {
				$('#old_salary').val(parseFloat(data.old_salary).toFixed(2));
				$('#new_salary').val(parseFloat(data.new_salary).toFixed(2));
				
				// Show salary breakdown
				let breakdown = '<ul class="mb-none">';
				breakdown += `<li><strong>Basic Salary:</strong> ${parseFloat(data.basic_salary).toFixed(2)}</li>`;
				if (data.salary_components && data.salary_components.length > 0) {
					data.salary_components.forEach(function (item) {
						breakdown += `<li>${item.name}: ${parseFloat(item.amount).toFixed(2)}</li>`;
					});
				}
				breakdown += '</ul>';
				$('#salaryBreakdown').html(breakdown);
			}, 500);
			
			mfp_modal('#incrementModal');
		}
	});
}
</script>
