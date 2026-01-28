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
							<th width="3%"><?php echo translate('sl'); ?></th>
							<th  width="10%" class="no-sort"><?php echo translate('photo'); ?></th>
							<th width="10%"><?php echo translate('business'); ?></th>
							<th width="15%"><?php echo translate('name'); ?></th>
							<th width="14%"><?php echo translate('designation'); ?></th>
							<th width="15%"><?php echo translate('department'); ?></th>
							<th width="10%"><?php echo translate('email'); ?></th>
							<th width="10%"><?php echo translate('mobile_no'); ?></th>
							<th width="10%"><?php echo translate('joining_date'); ?></th>
							<th width="3%"><?php echo translate('action'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php $i = 1; foreach ($stafflist as $row): ?>
						<tr>
							<td><?php echo $i++; ?></td>
							<td class="center">
								<img src="<?php echo get_image_url('staff', $row->photo); ?>" height="50" />
							</td>
							<td><?php echo get_type_name_by_id('branch', $row->branch_id);?></td>
							<td><?php echo $row->name; ?></td>
							<td><?php echo $row->designation_name; ?></td>
							<td><?php echo $row->department_name; ?></td>
							<td><?php echo $row->email; ?></td>
							<td><?php echo $row->mobileno; ?></td>
							<td><?php echo _d($row->joining_date); ?></td>
							<td class="min-w-c">
							<?php if (get_permission('probation', 'is_edit')): ?>
								<a href="<?php echo base_url('probation/profile/'.$row->id); ?>" class="btn btn-circle btn-default icon" data-toggle="tooltip" 
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