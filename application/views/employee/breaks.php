<section class="panel">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			
			
			<li>
				<a href="<?php echo base_url('employee/staff_break_history'); ?>"><i class="fas fa-history"></i> <?php echo translate('Break') . " " . translate('History'); ?></a>
			</li>
			
			<li class="active">
				<a href="#list" data-toggle="tab"><i class="fas fa-list-ul"></i> <?php echo translate('break') . " " . translate('Management'); ?></a>
			</li>

		</ul>
		
		
		
		<div class="">
			<div id="list" class="container tab-pane <?php echo (!isset($validation_error) ? 'active' : ''); ?>">
			
				<div class="row">

					<div class="col-md-5">
						<section class="panel">
							<header class="panel-heading">
								<h4 class="panel-title"><i class="far fa-edit"></i> <?php echo translate('add') . " " . translate('Break'); ?></h4>
							</header>
							<?php echo form_open($this->uri->uri_string()); ?>
								<div class="panel-body">
									<input type="hidden" name="date_time" value="<?php echo date('Y-m-d H:i:s'); ?>" >
									<input type="hidden" name="user_id" value="<?php echo get_loggedin_user_id();?>">
								
								
									<div class="form-group mb-md">
										<label class="control-label"><?php echo translate('Break Name'); ?><span class="required">*</span></label>
										<input type="text" class="form-control" name="break_name" value="" />
									</div>
									
								</div>
								<div class="panel-footer">
									<div class="row">
										<div class="col-md-12">
											<button class="btn btn-default pull-right" type="submit" name="category" value="1"><i class="fas fa-plus-circle"></i> <?php echo translate('save'); ?></button>
										</div>	
									</div>
								</div>
							<?php echo form_close(); ?>
						</section>
					</div>

					<div class="col-md-<?php if (get_permission('break', 'is_add')){ echo "6"; }else{echo "12";} ?>">
						<section class="panel">
							<header class="panel-heading">
								<h4 class="panel-title"><i class="fas fa-list-ul"></i> <?php echo translate('Break') . " " . translate('list'); ?></h4>
							</header>
							<div class="panel-body">
								<div class="table-responsive">
									<table class="table table-bordered table-hover table-condensed mb-none">
										<thead>
											<tr>
												<th><?php echo translate('sl'); ?></th>
												<th><?php echo translate('created time'); ?></th>
												<th><?php echo translate('break_name'); ?></th>
												<?php if (get_permission('breaks', 'is_edit') || get_permission('breaks', 'is_delete')): ?>
												<th><?php echo translate('action'); ?></th>
												<?php endif; ?>
											</tr>
										</thead>
										<tbody>
										<?php
										$count = 1;
										if (!empty($pause_list)){
											foreach ($pause_list as $row):
											?>
											<tr>
												<td><?php echo $count++; ?></td>
												<td><?php echo html_escape($row['date_time']); ?></td>
												<td><?php echo translate($row['name']); ?></td>
												<?php if (get_permission('breaks', 'is_edit') || get_permission('breaks', 'is_delete')): ?>
												<td>	
												<?php if (get_permission('breaks', 'is_edit')): ?>
													<a class="btn btn-default btn-circle icon" href="javascript:void(0);" data-toggle="tooltip" data-original-title="<?php echo translate('edit');?>"
													onclick="getBreak_details('<?php echo html_escape($row['id']); ?>')">
														<i class="fas fa-pen-nib"></i>
													</a>
													<?php endif; ?>
													<?php if (get_permission('breaks', 'is_delete')): ?>
													<?php echo btn_delete('employee/break_delete/' . $row['id']); ?>
													<?php endif; ?>
												</td>
												<?php endif; ?>
											</tr>
										<?php
												endforeach;
											}else{
												echo '<tr><td colspan="5"><h5 class="text-danger text-center">' . translate('no_information_available') . '</td></tr>';
											}
										?>
										</tbody>
									</table>
								</div>
							</div>
						</section>
					</div>
				</div>
			
			
			
			</div>
		
		</div>
	</div>
</section>

<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal_break">
	<section class="panel">
		<header class="panel-heading">
			<h4 class="panel-title">
				<i class="far fa-edit"></i> <?php echo translate('edit') . " " . translate('break'); ?>
			</h4>
		</header>
		
		
		<?php echo form_open(base_url('employee/staff_break'), array('class' => 'validate')); ?>
			<div class="panel-body">
				<input type="hidden" name="break_id" id="ebreak_id" value="" />
				<input type="hidden" name="date_time" id="edate_time" value="" />
				<input type="hidden" name="user_id" id="euser_id" value="" />
				<div class="form-group mb-md">
					<label class="control-label"><?php echo translate('break'); ?> <?php echo translate('name'); ?><span class="required">*</span></label>
					<input type="text" class="form-control" required  value="" name="break_name" id="ebreak_name" />
				</div>

			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-12 text-right">
						<button type="submit" class="btn btn-default"><?php echo translate('update'); ?></button>
						<button class="btn btn-default modal-dismiss"><?php echo translate('cancel'); ?></button>
					</div>
				</div>
			</footer>
		<?php echo form_close(); ?>
	</section>
</div>



<script>

// get Break details
function getBreak_details(id) {
    $.ajax({
        url: base_url + 'employee/break_edit',
        type: 'POST',
        data: {'id': id},
        dataType: "json",
        success: function (res) {
			$('#ebreak_id').val(res.id);
			$('#edate_time').val(res.date_time);
			$('#ebreak_name').val(res.name);
			$('#estatus').val(res.status);
			$('#euser_id').val(res.assign_to);
			mfp_modal('#modal_break');
        }
    });
}

</script>