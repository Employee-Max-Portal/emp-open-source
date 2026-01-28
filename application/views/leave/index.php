<style>
.leave-box {
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 10px 15px;
    margin-bottom: 10px;
    background-color: #fff;
    box-shadow: 0 3px 6px rgba(0,0,0,0.05);
}

.leave-bar-success {
    background-color: #28a745 !important;
}
.leave-bar-warning {
    background-color: #ffc107 !important;
    color: #000;
}
.leave-bar-danger {
    background-color: #dc3545 !important;
}

</style>
<div class="row">
	<div class="col-md-12">
	<?php if (in_array(loggedin_role_id(), [1, 2, 3, 5])) : ?>
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><?=translate('select_ground')?></h4>
			<?php if (get_permission('leave_manage', 'is_add')): ?>
				<div class="panel-btn">
					<a href="javascript:void(0);" id="addLeave" class="btn btn-default btn-circle" >
						<i class="fas fa-plus-circle"></i> Add Leave
					</a>
				</div>
			<?php endif; ?>
			</header>
			<?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
				<div class="panel-body">
					<div class="row mb-sm">
					<?php if (in_array(loggedin_role_id(), [1, 2, 3, 5])) : ?>
						<div class="col-md-6 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('business'); ?> <span class="required">*</span></label>
								<?php
								$arrayBranch = array('all' => translate('all')) + $this->app_lib->getSelectList('branch');
								echo form_dropdown(
									"branch_id", 
									$arrayBranch, 
									set_value('branch_id'), 
									"class='form-control' onchange='getDesignationByBranch(this.value)' 
									data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'"
								);
							?>
							</div>
						</div>
					<?php endif; ?>
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
				<h4 class="panel-title"><i class="fas fa-users" aria-hidden="true"></i> <?=translate('employee_leave_summary')?></h4>
			</header>
			<div class="panel-body">
				<div class="row mb-lg">
					
					<?php foreach ($leave_user_balances as $user): 
						$used = $user['used'];
						$total = $user['total'];
						$percent = $total > 0 ? round(($used / $total) * 100) : 0;

						// Color style logic
						if ($percent >= 75) {
							$bar_class = 'leave-bar-danger';
						} elseif ($percent >= 50) {
							$bar_class = 'leave-bar-warning';
						} else {
							$bar_class = 'leave-bar-success';
						}
					?>
						<div class="col-md-4 mb-sm">
							<div class="leave-box">
								<label class="mb-xs d-block">
									<strong><?= $user['name'] ?></strong>
									<small class="text-muted"> ( <?= $used ?> used / <?= $total ?> total )</small>
								</label>
								<div class="progress" style="height: 20px;">
									<div class="progress-bar <?= $bar_class ?>"
										 role="progressbar"
										 style="width: <?= $percent ?>%;"
										 aria-valuenow="<?= $used ?>"
										 aria-valuemin="0"
										 aria-valuemax="<?= $total ?>"
										 data-toggle="tooltip"
										 title="<?= $used ?> of <?= $total ?> days used">
										<?= $percent ?>%
									</div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		
		<section class="panel appear-animation" data-appear-animation="<?=$global_config['animations'] ?>" data-appear-animation-delay="100">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-users" aria-hidden="true"></i> <?=translate('leave_list')?></h4>
			</header>
			<div class="panel-body">
				<table class="table table-bordered table-condensed table-hover mb-none table-export" id="leave-table">
					<thead>
						<tr>
							<th><?=translate('sl')?></th>
							<th><?=translate('applicant')?></th>
							<th><?=translate('leave_category')?></th>
							<th><?=translate('date_of_start')?></th>
							<th><?=translate('date_of_end')?></th>
							<th><?=translate('days')?></th>
                            <th><?=translate('apply_date')?></th>
							<th class="no-sort"><?=translate('status')?></th>
							<th><?=translate('action')?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$count = 1;
						if (count($leavelist)) { 
							foreach($leavelist as $row) {
								?>
						<tr>
							<td><?php echo $count++; ?></td>
							<td><?php
								echo !empty($row['orig_file_name']) ? '<i class="fas fa-paperclip"></i> ' : '';
								$getStaff = $this->db->select('name,staff_id')->where('id', $row['user_id'])->get('staff')->row_array();
									echo $getStaff['name'] . '<br><small> - ' . $getStaff['staff_id'] . '</small>';
								?></td>
							<td><?php echo translate($row['category_name'] ?? $row['category_id'] . ' Leave'); ?></td>
							<td><?php echo _d($row['start_date']) ?></td>
							<td><?php echo _d($row['end_date']) ?></td>
							<td><?php echo $row['leave_days'] ?></td>
							<td><?php echo _d($row['apply_date']) ?></td>
							<td class="leave-status-<?= $row['id'] ?>">
								<?php
								if ($row['status'] == 1)
									$status = '<span class="label label-warning-custom text-xs">' . translate('pending') . '</span>';
								else if ($row['status']  == 2)
									$status = '<span class="label label-success-custom text-xs">' . translate('accepted') . '</span>';
								else if ($row['status']  == 3)
									$status = '<span class="label label-danger-custom text-xs">' . translate('rejected') . '</span>';
								echo ($status);
								?>
							</td>
							<td>
							<?php if (get_permission('leave_manage', 'is_add')) { ?>
								<a href="javascript:void(0);" class="btn btn-circle icon btn-default" onclick="getApprovelLeaveDetails('<?=$row['id']?>')">
									<i class="fas fa-bars"></i>
								</a>
							<?php } ?>
							<?php if (get_permission('leave_manage', 'is_delete')) { ?>
								<?php echo btn_delete('leave/delete/' . $row['id']); ?>
							<?php } ?>
							</td>
						</tr>
						<?php } } ?>
					</tbody>
				</table>
			</div>
		</section>
	</div>
</div>

<!-- Leave View Modal -->
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
	<section class="panel" id='quick_view'></section>
</div>

<?php if (get_permission('leave_manage', 'is_add')): ?>
<!-- Leave Add Modal -->
<div id="addLeaveModal" class="zoom-anim-dialog modal-block mfp-hide modal-block-lg">
    <section class="panel">
        <div class="panel-heading">
            <h4 class="panel-title"><i class="fas fa-plus-circle"></i> <?php echo translate('add_leave'); ?></h4>
        </div>
		<?php echo form_open_multipart('leave/save', array('class' => 'form-horizontal frm-submit-data')); ?>
		<div class="panel-body">
			
			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('applicant')?> <span class="required">*</span></label>
				<div class="col-md-9">
					<?php
						$this->db->select('s.id, s.name');
						$this->db->from('staff AS s');
						$this->db->join('login_credential AS lc', 'lc.user_id = s.id');
						$this->db->where('lc.active', 1);   // only active users
						$this->db->where_not_in('lc.role', [1, 9, 11,12]);   // exclude super admin, etc.
						$this->db->where_not_in('s.id', [49]);
						$this->db->order_by('s.name', 'ASC');
						$query = $this->db->get();

						$staffArray = ['' => 'Select']; // <-- default first option
						foreach ($query->result() as $row) {
							$staffArray[$row->id] = $row->name;
						}
                        echo form_dropdown("applicant_id", $staffArray, set_value('applicant_id'), "class='form-control' id='applicant_id'
                        data-plugin-selectTwo data-width='100%' ");
                    ?>
					<span class="error"></span>
				</div>

			</div>
			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('leave_category')?> <span class="required">*</span></label>
				<div class="col-md-9">
					<select name="leave_category" class="form-control" data-plugin-selectTwo data-width="100%" data-minimum-results-for-search="Infinity" id="leave_category">
						<option value=""><?=translate('select')?></option>
					</select>
					<span class="error"></span>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('leave_date')?> <span class="required">*</span></label>
				<div class="col-md-9">
					<div class="input-group">
						<span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
						<input type="text" class="form-control" name="daterange" id="daterange" value="<?=set_value('daterange', date("Y/m/d") . ' - ' . date("Y/m/d"))?>" required />
					</div>
					<span class="error"></span>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-3 control-label"><?php echo translate('reason'); ?></label>
				<div class="col-md-9">
					<textarea class="form-control" name="reason" rows="3"><?php echo set_value('reason'); ?></textarea>
				</div>
			</div>

			<div class="form-group">
				<label class="col-md-3 control-label"><?php echo translate('attachment'); ?></label>
				<div class="col-md-9">
					<input type="file" name="attachment_file" id="attachment_file" class="dropify" data-height="80" />
					<span class="error"></span>
				</div>
			</div>
			<div class="form-group mb-lg">
				<label class="col-md-3 control-label"><?php echo translate('comments'); ?></label>
				<div class="col-md-9">
					<textarea class="form-control" name="comments" rows="3"><?php echo set_value('comments'); ?></textarea>
				</div>
			</div>
		</div>
		    <footer class="panel-footer">
		        <div class="row">
		            <div class="col-md-12 text-right">
		                <button type="submit" class="btn btn-default mr-xs" id="savebtn" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
		                    <i class="fas fa-plus-circle"></i> <?=translate('apply') ?>
		                </button>
		                <button class="btn btn-default modal-dismiss"><?=translate('cancel') ?></button>
		            </div>
		        </div>
		    </footer>
		<?php echo form_close();?>
    </section>
</div>
<?php endif; ?>
<script>
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>

<script type="text/javascript">
	$(document).ready(function () {
		$('#daterange').daterangepicker({
			opens: 'left',
		    locale: {format: 'YYYY/MM/DD'}
		});

	    $('#addLeave').on('click', function(){
	        mfp_modal('#addLeaveModal');
	    });

	    // Load leave categories when applicant is selected
	    $('#applicant_id').on('change', function(){
	        var applicant_id = $(this).val();
	        $('#leave_category').html('<option value=""><?=translate('select')?></option>');
	        
	        if(applicant_id) {
	            $.ajax({
	                url: base_url + 'leave/get_leave_categories',
	                type: 'POST',
	                data: {applicant_id: applicant_id},
	                success: function(data) {
	                    $('#leave_category').html(data);
	                }
	            });
	        }
	    });

	    // Update daterange picker based on leave category
	    $('#leave_category').on('change', function(){
	        var categoryText = $(this).find('option:selected').text().toLowerCase();
	        var minDate = new Date();
	        
	        if(categoryText.includes('annual')) {
	            minDate.setDate(minDate.getDate() + 7);
	        } else {
	            minDate.setDate(minDate.getDate() + 1);
	        }
	        
	        $('#daterange').daterangepicker({
	            opens: 'left',
	            locale: {format: 'YYYY/MM/DD'},
	            minDate: minDate
	        });
	    });

        $('#class_id').on('change', function() {
            var class_id = $(this).val();
            var branch_id = ($( "#branch_id" ).length ? $('#branch_id').val() : "");
			$.ajax({
				url: base_url + 'ajax/getStudentByClass',
				type: 'POST',
				data: {
					branch_id: branch_id,
					class_id: class_id
				},
				success: function (data) {
					$('#applicant_id').html(data);
				}
			});
        });
	});

	// get leave approvel details
	function getApprovelLeaveDetails(id) {
	    $.ajax({
	        url: base_url + 'leave/getApprovelLeaveDetails',
	        type: 'POST',
	        data: {'id': id},
	        dataType: "html",
	        success: function (data) {
				$('#quick_view').html(data);
				mfp_modal('#modal');
	        }
	    });
	}

	// Update leave status instantly
	function updateLeaveStatus(leaveId, status) {
		// Update status
		var statusHtml = '';
		if (status == 1) statusHtml = '<span class="label label-warning-custom text-xs"><?= translate('pending') ?></span>';
		else if (status == 2) statusHtml = '<span class="label label-success-custom text-xs"><?= translate('accepted') ?></span>';
		else if (status == 3) statusHtml = '<span class="label label-danger-custom text-xs"><?= translate('rejected') ?></span>';
		
		// Update the status cell
		$('.leave-status-' + leaveId).html(statusHtml);
	}

	
</script>