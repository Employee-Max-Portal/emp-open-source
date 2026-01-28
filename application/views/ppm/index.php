<section class="panel">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#">
					<i class="far fa-user-circle"></i> <?php echo translate('employee'); ?> <?php echo translate('list'); ?>
				</a>
			</li>
		</ul>

		<div class="tab-content">
			<div class="tab-pane box active">
				<div class="export_title"><?php echo translate('employee') . " " . translate('list'); ?></div>
				<table class="table table-bordered table-hover table-condensed table-export" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th style="width: 10px;"><?php echo translate('sl'); ?></th>
							<th class="no-sort" style="width: 60px;"><?php echo translate('photo'); ?></th>
							<th style="width: 100px;"><?php echo translate('employee_id'); ?></th>
							<th style="min-width: 150px;"><?php echo translate('name'); ?></th>
							<th style="min-width: 140px;"><?php echo translate('designation'); ?></th>
							<th style="width: 160px;"><?php echo translate('avg_staff_rating'); ?></th>
							<th style="width: 200px;"><?php echo translate('avg_manager_rating'); ?></th>
							<th style="width: 90px;"><?php echo translate('total_kpi'); ?></th>
							<th style="width: 10px;"><?php echo translate('action'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php $i = 1; foreach ($stafflist as $row): ?>
						<tr>
							<td><?php echo $i++; ?></td>
							<td class="center">
								<img src="<?php echo get_image_url('staff', $row->photo); ?>" height="50" />
							</td>
							<td><?php echo $row->staff_id; ?></td>
							<td><?php echo $row->name; ?></td>
							<td><?php echo $row->designation_name; ?></td>
							<td><?php echo number_format($row->avg_staff_rating, 2); ?></td>
							<td><?php echo number_format($row->avg_manager_rating, 2); ?></td>
							<td><?php echo $row->total_kpi; ?></td>
							<td class="min-w-c">
								<?php if (get_permission('probation', 'is_edit')): ?>
								<a href="<?php echo base_url('kpi/profile/'.$row->id); ?>" class="btn btn-circle btn-default icon" data-toggle="tooltip" 
								data-original-title="<?=translate('profile')?>">
									<i class="far fa-arrow-alt-circle-right"></i>
								</a>
								<?php endif; ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

			</div>
		</div>
	</div>
</section>