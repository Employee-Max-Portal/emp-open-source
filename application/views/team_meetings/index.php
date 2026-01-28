<div class="row">
	<div class="col-md-12">
	
	<section class="panel">
		<div class="tabs-custom">
			<ul class="nav nav-tabs">
				<li class="active">
					<a href="#list" data-toggle="tab">
						<i class="fas fa-list"></i> <?php echo translate('team_meetings'); ?>
					</a>
				</li>
				<?php if (get_permission('team_meetings', 'is_add')): ?>
				<li>
					<a href="#create" data-toggle="tab">
						<i class="fas fa-plus-circle"></i> <?php echo translate('add_meeting'); ?>
					</a>
				</li>
				<?php endif; ?>
			</ul>
			<div class="tab-content">
				<div class="tab-pane box active" id="list">
				  
					<div class="export_title"><?php echo translate('team_meetings') . " " . translate('list'); ?></div>
					<table class="table table-bordered table-hover table-condensed table-export">
						<thead>
							<tr>
								<th><?php echo translate('sl'); ?></th>
								<th><?php echo translate('host'); ?></th>
								<th><?php echo translate('title'); ?></th>
								<th><?php echo translate('type'); ?></th>
								<th><?php echo translate('participants'); ?></th>
								<th><?php echo translate('date'); ?></th>
								<th><?php echo translate('created_by'); ?></th>
								<th style="width:150px";><?php echo translate('action'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php $i = 1; foreach ($meetings as $meeting): ?>
							<tr>
								<td><?php echo $i++; ?></td>
								<td>
									<?php 
										$getStaff = $this->db->select('name')
															 ->where('id', $meeting['meeting_host'])
															 ->get('staff')
															 ->row_array();
										echo $getStaff['name'] ?? '-';
									?>
								</td>
								<td><?php echo $meeting['title']; ?></td>
								<td>
									<span class="label label-<?php echo ($meeting['meeting_type'] == 'management') ? 'warning' : 'success'; ?>">
										<?php echo ucfirst($meeting['meeting_type']); ?>
									</span>
								</td>
								<td>
									<?php 
									if (!empty($meeting['participant_names'])) {
										echo $meeting['participant_names'];
									} else {
										echo '-';
									}
									?>
								</td>
								<td><?php echo date('d M Y', strtotime($meeting['date'])); ?></td>
								<td><?php echo $meeting['created_by_name']; ?></td>
								<td class="min-w-c">
									
									<a href="javascript:void(0);" class="btn btn-circle icon btn-default" onclick="getDetails('<?=$meeting['id']?>')">
										<i class="fas fa-eye"></i>
									</a>
									
									 <?php if (!empty($meeting['attachments'])): ?>
										<a href="<?php echo base_url('team_meetings/download/' . $meeting['id']); ?>" 
										   class="btn btn-circle icon btn-default" data-toggle="tooltip" data-original-title="<?php echo translate('download'); ?>">
											<i class="fas fa-download"></i>
										</a>
									<?php endif; ?>
									<?php 
									$userRole = loggedin_role_id();
									$userId = get_loggedin_user_id();
									if (get_permission('team_meetings', 'is_edit') && 
										(in_array($userRole, [1, 2, 3, 5, 8]) || $meeting['created_by'] == $userId)): 
									?>
									<a href="<?php echo base_url('team_meetings/edit/' . $meeting['id']); ?>" 
									   class="btn btn-circle icon btn-default" data-toggle="tooltip"
									   data-original-title="<?php echo translate('edit'); ?>">
										<i class="fas fa-edit"></i>
									</a>
									<?php endif; ?>
									<?php 
									if (get_permission('team_meetings', 'is_delete') && 
										(in_array($userRole, [1, 2, 3, 5, 8]) || $meeting['created_by'] == $userId)): 
									?>
									<?php echo btn_delete('team_meetings/delete/' . $meeting['id']); ?>
									<?php endif; ?>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
		<!-- View Modal -->
		<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
			<section class="panel" id='quick_view'></section>
		</div>

				<?php if (get_permission('team_meetings', 'is_add')): ?>
				<div class="tab-pane" id="create">
					<?php echo form_open_multipart('team_meetings/save', array('class' => 'form-horizontal', 'method' => 'POST')); ?>
						<div class="form-group">
							<label class="col-md-3 control-label"><?=translate('host')?> <span class="required">*</span></label>
							<div class="col-md-6">
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
								echo form_dropdown("meeting_host", $staffArray, array(), "class='form-control' id='meeting_host' required data-plugin-selectTwo data-placeholder=\"Select\" data-width='100%' ");
								?>

								<span class="error"><?= form_error('assigned_to') ?></span>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label"><?php echo translate('title'); ?> <span class="required">*</span></label>
							<div class="col-md-6">
								<input type="text" class="form-control" name="title" value="<?php echo set_value('title'); ?>" />
								<span class="error"><?php echo form_error('title'); ?></span>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label"><?php echo translate('date'); ?> <span class="required">*</span></label>
							<div class="col-md-6">
								<input type="date" class="form-control" name="date" value="<?php echo set_value('date', date('Y-m-d')); ?>" />
								<span class="error"><?php echo form_error('date'); ?></span>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label"><?php echo translate('type'); ?> <span class="required">*</span></label>
							<div class="col-md-6">
								<select name="meeting_type" class="form-control" data-plugin-selectTwo data-width="100%">
									<option value=""><?php echo translate('select'); ?></option>
									<option value="public" <?php echo set_select('meeting_type', 'public'); ?>><?php echo translate('public'); ?></option>
									<option value="management" <?php echo set_select('meeting_type', 'management'); ?>><?php echo translate('management'); ?></option>
								</select>
								<span class="error"><?php echo form_error('meeting_type'); ?></span>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label"><?php echo translate('participants'); ?></label>
						   <div class="col-md-6">
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
							echo form_dropdown("staff_id[]", $staffArray, array(), "class='form-control' id='staff_id' required multiple data-plugin-selectTwo data-placeholder=\"Select\" data-width='100%' "
						);
							
							?>

								<span class="error"></span>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label"><?php echo translate('meeting_minutes'); ?> <span class="required">*</span></label>
							<div class="col-md-6">
								<textarea name="summary" class="form-control summernote" rows="5"><?php echo set_value('summary'); ?></textarea>
								<span class="error"><?php echo form_error('summary'); ?></span>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label"><?php echo translate('attachment'); ?></label>
							<div class="col-md-6">
								<input type="file" name="attachment" class="dropify" data-allowed-file-extensions="pdf doc docx jpg jpeg png" />
							</div>
						</div>
						<footer class="panel-footer">
							<div class="row">
								<div class="col-md-offset-3 col-md-2">
									<button type="submit" class="btn btn-default btn-block" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
										<i class="fas fa-plus-circle"></i> <?php echo translate('save'); ?>
									</button>
								</div>
							</div>
						</footer>
					<?php echo form_close(); ?>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</section>

	</div>
</div>
<style>
.modal-block {
    background: transparent;
    padding: 0;
    text-align: left;
    max-width: 60%;
    margin: 40px auto;
    position: relative;
}
</style>
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

// get details
	function getDetails(id) {
	    $.ajax({
	        url: base_url + 'team_meetings/getDetails',
	        type: 'POST',
	        data: {'id': id},
	        dataType: "html",
	        success: function (data) {
				$('#quick_view').html(data);
				mfp_modal('#modal');
	        }
	    });
	}
	
$(document).ready(function() {
    $('.summernote').summernote({
        height: 150,
        minHeight: null,
        maxHeight: null,
        focus: false
    });
});
</script>