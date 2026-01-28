<section class="panel">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<?php
			$this->db->where_not_in('id', array(1,6,7));
			$roles = $this->db->get('roles')->result();
			foreach ($roles as $role){
			?> 	
			<li class="<?php if ($role->id == $act_role) echo 'active'; ?>">
				<a href="<?php echo base_url('employee/view/' . $role->id); ?>">
					<i class="far fa-user-circle"></i> <?php echo $role->name?>
				</a>
			</li>
			<?php } ?>
		</ul>
		<div class="tab-content">
			<div class="tab-pane box active">
				<div class="export_title"><?php echo translate('employee') . " " . translate('list'); ?></div>
				<table class="table table-bordered table-hover table-condensed table-export" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th><?php echo translate('sl'); ?></th>
							<th class="no-sort"><?php echo translate('photo'); ?></th>
							<th><?php echo translate('branch'); ?></th>
							<th><?php echo translate('name'); ?></th>
							<th><?php echo translate('designation'); ?></th>
							<th><?php echo translate('department'); ?></th>
							<th><?php echo translate('email'); ?></th>
							<th><?php echo translate('mobile_no'); ?></th>
						<?php if (get_permission('employee', 'is_edit') || get_permission('employee', 'is_delete')): ?>
							<th><?php echo translate('action'); ?></th>
						<?php endif; ?>

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
							<?php if (get_permission('employee', 'is_edit') || get_permission('employee', 'is_delete')): ?>
							<td class="min-w-c">
							<?php if (get_permission('employee', 'is_edit')): ?>
								<a href="<?php echo base_url('employee/profile/'.$row->id); ?>" class="btn btn-circle btn-default icon" data-toggle="tooltip" 
								data-original-title="<?=translate('profile')?>">
									<i class="far fa-arrow-alt-circle-right"></i>
								</a>
							<?php endif; if (get_permission('employee', 'is_delete')): ?>
								<?php echo btn_delete('employee/delete/' . $row->id); ?>
							<?php endif; ?>
							</td>
								<?php endif; ?>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</section>