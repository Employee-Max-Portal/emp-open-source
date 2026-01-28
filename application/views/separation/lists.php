<?php $widget = ((in_array(loggedin_role_id(), [1, 2, 3, 5])) ? 'col-md-6' : 'col-md-offset-3 col-md-6'); ?>
<style>
.leave-box {
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 10px 15px;
    margin-bottom: 10px;
    background-color: #fff;
    box-shadow: 0 3px 6px rgba(0,0,0,0.05);
}
.leave-bar-success { background-color: #28a745 !important; }
.leave-bar-warning { background-color: #ffc107 !important; color: #000; }
.leave-bar-danger { background-color: #dc3545 !important; }
</style>

<div class="row">
	<div class="col-md-12">
	<?php if (in_array(loggedin_role_id(), [1, 2, 3, 5])) : ?>
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><?= translate('filter_separation_requests') ?></h4>
			</header>
			<?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
				<div class="panel-body">
					<div class="row mb-sm">
					<?php if (in_array(loggedin_role_id(), [1, 2, 3, 5])) : ?>
						<div class="col-md-6 mb-sm">
							<div class="form-group">
								<label class="control-label"><?= translate('business'); ?> <span class="required">*</span></label>
								<?php
									$arrayBranch = array('all' => translate('all')) + $this->app_lib->getSelectList('branch');

									// Default to "all" if no previous POST value exists
									$selected = set_value('branch_id') ?: 'all';

									echo form_dropdown(
										"branch_id",
										$arrayBranch,
										$selected,
										"class='form-control' onchange='getDesignationByBranch(this.value)' 
										 data-plugin-selectTwo data-width='100%' 
										 data-minimum-results-for-search='Infinity'"
									);
								?>
							</div>
						</div>
					<?php endif; ?>
						<div class="<?= $widget ?> mb-sm">
							<div class="form-group">
								<label class="control-label"><?= translate('role'); ?> <span class="required">*</span></label>
								<?php
									$all_roles = $this->app_lib->getRoles();
									$filtered_roles = array_diff_key($all_roles, array_flip([1, 11, 12]));
									$role_list = array('all' => translate('all')) + $filtered_roles;

									// Default selection logic
									$selected_role = set_value('staff_role') ?: 'all';

									echo form_dropdown(
										"staff_role",
										$role_list,
										$selected_role,
										"class='form-control' data-plugin-selectTwo required data-width=\"100%\" 
										 data-minimum-results-for-search='Infinity'"
									);
								?>
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
		<?php endif; ?>
		
		<section class="panel appear-animation" data-appear-animation="<?= $global_config['animations'] ?>" data-appear-animation-delay="100">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-user-times"></i> <?= translate('separation_request_list') ?></h4>
			</header>
			<div class="panel-body">
				<table class="table table-bordered table-condensed table-hover mb-none table-export">
					<thead>
						<tr>
							<th><?= translate('sl') ?></th>
							<th><?= translate('employee_id') ?></th>
							<th><?= translate('employee_name') ?></th>
							<th><?= translate('title') ?></th>
							<th><?= translate('last_working_date') ?></th>
							<th><?= translate('submitted_on') ?></th>
							<th><?= translate('approved_by') ?></th>
							<th><?= translate('status') ?></th>
							<th><?= translate('action') ?></th>
						</tr>
					</thead>
					<tbody>
						<?php $count = 1;
						if (!empty($separationList)) {
							foreach ($separationList as $row): ?>
								<tr>
									<td><?= $count++ ?></td>
									<td>
										<?php
											$staff = $this->db->select('name,staff_id')->where('id', $row->user_id)->get('staff')->row_array();
											echo $staff['staff_id'];
										?>
									</td>
									<td>
										<?php
											$staff = $this->db->select('name,staff_id')->where('id', $row->user_id)->get('staff')->row_array();
											echo $staff['name'];
										?>
									</td>
									<td><?= html_escape($row->title) ?></td>
									<td><?= _d($row->last_working_date) ?></td>
									<td><?= _d($row->created_at) ?></td>
									<td>
										<?php
											$staff = $this->db->select('name')->where('id', $row->approved_by)->get('staff')->row_array();
											echo $staff['name'];
										?>
									</td>
									<td>
										<?php
											if ($row->status == 1)
												echo '<span class="label label-warning-custom">' . translate('pending') . '</span>';
											elseif ($row->status == 2)
												echo '<span class="label label-success-custom">' . translate('approved') . '</span>';
											elseif ($row->status == 3)
												echo '<span class="label label-danger-custom">' . translate('rejected') . '</span>';
										?>
									</td>
									<td>
										<a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick="getRequestDetails('<?= $row->id ?>')">
											<i class="fas fa-bars"></i>
										</a>
										<?php if (get_permission('separation', 'is_delete')): ?>
											<?= btn_delete('separation/delete/' . $row->id); ?>
										<?php endif; ?>
									</td>
								</tr>
						<?php endforeach; } ?>
					</tbody>
				</table>
			</div>
		</section>
	</div>
</div>

<!-- Modal -->
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
	<section class="panel" id="quick_view"></section>
</div>

<script type="text/javascript">
	function getRequestDetails(id) {
	    $.ajax({
	        url: base_url + 'separation/getApprovelLeaveDetails',
	        type: 'POST',
	        data: { id: id },
	        dataType: "html",
	        success: function (data) {
	            $('#quick_view').html(data);
	            mfp_modal('#modal');
	        }
	    });
	}
</script>
