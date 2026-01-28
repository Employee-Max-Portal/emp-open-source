<?php $widget = ((in_array(loggedin_role_id(), [1, 2, 3, 5])) ? 'col-md-6' : 'col-md-offset-3 col-md-6'); ?>
<div class="row">
	<div class="col-md-12">
		<?php if (in_array(loggedin_role_id(), [1, 2, 3, 5])): ?>
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><?=translate('select_ground')?></h4>
			</header>
			<?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
				<div class="panel-body">
					<div class="row mb-sm">
						<div class="col-md-offset-3 col-md-6 mb-sm">
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
		<?php endif; ?>
		
		<section class="panel appear-animation" data-appear-animation="<?=$global_config['animations'] ?>" data-appear-animation-delay="100">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-users" aria-hidden="true"></i> <?=translate('server_requests')?></h4>
			</header>
			<div class="panel-body">
				<table class="table table-bordered table-condensed table-hover mb-none table-export" >
					<thead>
						<tr>
							<th width="50">#</th>
							<th><?=translate('photo')?></th>
							<th><?=translate('applicant')?></th>
							<th><?=translate('server_name')?></th>
							<th><?=translate('applied_on')?></th>
							<th><?=translate('expired_at')?></th>
							<th style="text-align:center;"><?=translate('status')?></th>
							<th><?=translate('action')?></th>
						</tr>
					</thead>
					<tbody>
						<?php $count = 1;
						foreach ($request_list as $row) {?>
						<tr>
							<td><?php echo $count++; ?></td>
							<td class="center"><img class="rounded" src="<?php echo get_image_url('staff', $row['photo']);?>" width="40" height="40" /></td>
							<td><?php
								$getStaff = $this->db->select('name,staff_id')->where('staff_id', $row['staff_id'])->get('staff')->row_array();
									echo $getStaff['staff_id'] . ' - ' . $getStaff['name'];
								?></td>
							<td><?php echo $row['server_name'];?></td>
							<td><?php echo date('jS F, Y', strtotime($row['created_at'])); ?></td>
							<td>
							  <?php 
								echo !empty($row['code_expires_at']) && $row['code_expires_at'] != '0000-00-00 00:00:00'
								  ? date('jS F, Y \a\t h:i A', strtotime($row['code_expires_at']))
								  : 'N/A'; 
							  ?>
							</td>

							<td style="text-align:center;">
								<?php
								if ($row['status'] == 1)
									echo '<span class="label label-warning-custom">' . translate('pending') . '</span>';
								else if ($row['status'] == 2)
									echo '<span class="label label-success-custom">' . translate('approved') . '</span>';
								else if ($row['status'] == 3)
									echo '<span class="label label-danger-custom">' . translate('rejected') . '</span>';
								?>
							</td>
							<td>
							<?php if (get_permission('server_manage', 'is_add')): ?>
								<!--modal dialogbox-->
								<a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick="getDetailedRequest('<?=$row['id']?>')">
									<i class="fas fa-bars"></i>
								</a>
							<?php endif; ?>
							<?php if (get_permission('server_manage', 'is_delete')): ?>
								<!--delete link-->
								<?php echo btn_delete('server_request/delete/' . $row['id']);?>
							<?php endif; ?>
							</td>
						</tr>
						<?php }?>
					</tbody>
				</table>
			</div>
		</section>
	</div>
</div>

<!-- Advance Salary View Modal -->
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
	<section class="panel" id='quick_view'></section>
</div>
<!-- Fund Requisition Modal -->
<div id="serverRequestModal" class="zoom-anim-dialog modal-block modal-block-lg mfp-hide">
    <section class="panel">
        <div class="panel-heading">
            <h4 class="panel-title"><i class="fas fa-plus-circle"></i> <?php echo translate('server_access_request'); ?></h4>
        </div>
		<?php echo form_open('server_request/save', array('class' => 'form-horizontal frm-submit', 'method' => 'post')); ?>
			<div class="panel-body">

				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('applicant')?> <span class="required">*</span></label>
					<div class="col-md-8">
						<?php
							$array = $this->app_lib->getSelectList('staff');
							unset($array[1]);						
							echo form_dropdown("staff_id", $array, set_value('staff_id'), "class='form-control' id='staff_id'
							data-plugin-selectTwo data-width='100%' ");
						?>
						<span class="error"></span>
					</div>
				</div>
			
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('server_name')?> <span class="required">*</span></label>
					<div class="col-md-8">
						<input type="text" class="form-control" value="<?=set_value('server_name')?>" name="server_name" required />
						<span class="error"></span>
					</div>
				</div>
				
				<div class="form-group mb-md">
					<label class="col-md-3 control-label"><?=translate('reason')?></label>
					<div class="col-md-8">
						<textarea class="form-control" rows="2" name="reason" placeholder="Enter your Reason"><?=set_value('reason')?></textarea>
					</div>
				</div>
				
			</div>

		    <footer class="panel-footer">
		        <div class="row">
		            <div class="col-md-12 text-right">
		                <button type="submit" class="btn btn-default mr-xs" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
		                    <i class="fas fa-plus-circle"></i> <?=translate('apply') ?>
		                </button>
		                <button class="btn btn-default modal-dismiss"><?=translate('cancel') ?></button>
		            </div>
		        </div>
		    </footer>
		<?php echo form_close();?>
    </section>
</div>

<script type="text/javascript">
	function getStafflistRole() {
    	var staff_role = $('#staff_role').val();
    	var branch_id = ($( "#branch_id" ).length ? $('#branch_id').val() : "");
        $.ajax({
            url: base_url + 'ajax/getStafflistRole',
            type: "POST",
            data:{ 
            	role_id: staff_role,
            	branch_id: branch_id 
            },
            success: function (data) {
            	$('#staff_id').html(data);
            }
        });
	}
</script>

<script type="text/javascript">
	function getDetailedRequest(id) {
	    $.ajax({
	        url: base_url + 'server_request/getDetailedRequest',
	        type: 'POST',
	        data: {'id': id},
	        dataType: "html",
	        success: function (data) {
	            $('#quick_view').html(data);
	            mfp_modal('#modal');
	        }
	    });
	}
</script>

