<?php $widget = ((in_array(loggedin_role_id(), [1, 2, 3, 5])) ? 'col-md-4' : 'col-md-6'); ?>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><?=translate('select_ground')?></h4>
			</header>
			<?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
				<div class="panel-body">
					<div class="row mb-sm">
						<div class="<?=$widget?> mb-sm">
	                        <div class="form-group">
	                            <label class="control-label"><?php echo translate('date'); ?> <span class="required">*</span></label>
	                            <div class="input-group">
	                                <span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
	                                <input type="text" class="form-control daterange" name="daterange" value="<?=set_value('daterange', date("Y/m/d", strtotime('-6day')) . ' - ' . date("Y/m/d"))?>" required />
	                            </div>
	                        </div>
                        </div>
					</div>
				</div>
				<footer class="panel-footer">
					<div class="row">
						<div class="col-md-offset-10 col-md-2">
							<button type="submit" name="search" value="1" class="btn btn btn-default btn-block"><i class="fas fa-filter"></i> <?=translate('filter')?></button>
						</div>
					</div>
				</footer>
			<?php echo form_close(); ?>
		</section>
	<?php if (isset($penalty_days)) { ?>
		<section class="panel appear-animation" data-appear-animation="<?=$global_config['animations'] ?>" data-appear-animation-delay="100">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-users" aria-hidden="true"></i> <?=translate('penalty_days')?></h4>
			</header>
			<div class="panel-body">
				<table class="table table-bordered table-condensed table-hover mb-none table-export">
					<thead>
						<tr>
							<th><?= translate('sl') ?></th>
							<th><?= translate('employee') ?></th>
							<th><?= translate('penalty_date') ?></th>
							<th><?= translate('warning_reason') ?></th>
							<th><?= translate('category') ?></th>
							<th><?= translate('effect') ?></th>
							<th><?= translate('issued_on') ?></th>
							<th><?= translate('served_status') ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$count = 1;
						if (!empty($penalty_days)) {
							foreach ($penalty_days as $row) {
								// Fetch employee info (optional if already present)
								$staffInfo = $this->db->select('id, name, staff_id')->where('staff_id', $row['staff_id'])->get('staff')->row_array();
								$employee = $staffInfo ? $staffInfo['staff_id'] . ' - ' . $staffInfo['name'] : $row['staff_id'];

								// Check if employee was present or late on the penalty date
								$is_served = $this->db->select('id')
									->where('staff_id', $staffInfo['id'])
									->where('DATE(date)', $row['penalty_date'])
									->where_in('status', ['P', 'L'])
									->get('staff_attendance')
									->num_rows() > 0;
								?>
								<tr>
									<td><?= $count++ ?></td>
									<td><?= $employee ?></td>
									<td><?= _d($row['penalty_date']) ?></td>
									<td><?= translate($row['warning_reason']) ?></td>
									<td><?= translate($row['category']) ?></td>
									<td><?= translate($row['effect']) ?></td>
									<td><?= _d($row['issue_date']) ?></td>
									<td>
										<?php if ($is_served): ?>
											<span class="label label-success-custom"><?= translate('yes') ?></span>
										<?php else: ?>
											<span class="label label-danger-custom"><?= translate('no') ?></span>
										<?php endif; ?>
									</td>
								</tr>
							<?php }
						} ?>
					</tbody>
				</table>

			</div>
		</section>
	<?php } ?>
	</div>
</div>