<div class="row">
	<div class="col-md-12">
		<?php if (get_permission('todo', 'is_delete')): ?>
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><?= translate('select_ground') ?></h4>
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
							<button type="submit" name="search" value="1" class="btn btn-default btn-block">
								<i class="fas fa-filter"></i> <?= translate('filter') ?>
							</button>
						</div>
					</div>
				</footer>
			<?php echo form_close(); ?>
		</section>
		<?php endif; ?>

		
		<section class="panel appear-animation" data-appear-animation="<?=$global_config['animations'] ?>" data-appear-animation-delay="100">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-users" aria-hidden="true"></i> <?=translate('list_of_tasks')?></h4>
			</header>
			<div class="panel-body">
				<table class="table table-bordered table-condensed table-hover mb-none table-export" >
					<thead>
						<tr>
							<th width="50"><?=translate('sl')?></th>
							<th><?=translate('task_title')?></th>
							<th><?=translate('linked sop')?></th>
							<th><?=translate('assigned_to')?></th>
							<th><?=translate('frequency')?></th>
							<th><?=translate('due_time')?></th>
							<th><?=translate('created_at')?></th>
							<th style="text-align:center;" width="130"><?=translate('action')?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$count = 1;
						if (count($tasklist)) { 
							foreach($tasklist as $row) {
								?>
						<tr>
							<td><?php echo $count++; ?></td>
							<td><?= htmlspecialchars($row['title']) ?></td>
							<td>
								<?php 
									if (!empty($row['sop_ids'])) {
										$sop_ids = json_decode($row['sop_ids'], true);
										if (is_array($sop_ids)) {
											$sop_titles = [];
											foreach ($sop_ids as $index => $sop_id) {
												$sop = $this->db->select('title')->where('id', $sop_id)->get('sop')->row();
												if ($sop) {
													$sop_titles[] = ($index + 1) . '. ' . htmlspecialchars($sop->title);
												}
											}
											echo implode('<br>', $sop_titles);
										} else {
											echo 'No SOPs';
										}
									} else {
										echo 'No SOPs';
									}
									?>

							</td>
							<td><?php
								if ($row['is_random_assignment'] == 1) {
									if (!empty($row['user_pool'])) {
										$user_pool = json_decode($row['user_pool'], true);
										if (is_array($user_pool) && !empty($user_pool)) {
											echo '<span class="label label-success">Multi-User Random (' . count($user_pool) . ' users)</span>';
											echo '<br><small>Pool: ';
											$pool_names = [];
											foreach ($user_pool as $user_id) {
												$staff = $this->db->select('name')->where('id', $user_id)->get('staff')->row();
												if ($staff) {
													$pool_names[] = $staff->name;
												}
											}
											echo implode(', ', $pool_names) . '</small>';
										} else {
											echo '<span class="label label-info">Random Assignment (Rotation)</span>';
										}
									} else {
										echo '<span class="label label-info">Random Assignment (Rotation)</span>';
									}
								} else {
									$getStaff = $this->db->select('name,staff_id')->where('id', $row['assigned_user'])->get('staff')->row_array();
									echo $getStaff['staff_id'] . ' - ' . $getStaff['name'];
								}
								?>
							</td>
							<td><?= ucfirst($row['frequency']) ?></td>
							<td>
								<?php
									$issueDate = new DateTime($row['due_time']);
									echo $issueDate->format('jS F, Y \a\t h.i A');
								?>
							</td>
							<td>
								<?php
									$issueDate = new DateTime($row['created_at']);
									echo $issueDate->format('jS F, Y \a\t h.i A');
								?>
							</td>
						
							<td style="text-align:center;" >
							<a href="javascript:void(0);" class="btn btn-info btn-circle icon" onclick="getRDCTemplatesSop('<?=$row['id']?>')" data-toggle="tooltip" data-original-title="<?php echo translate('View SOP'); ?>" >
									<i class="fas fa-eye" style="color: #ffffff;"></i>
								</a>
							<?php if (get_permission('rdc_management', 'is_view')) { ?>
								<a href="javascript:void(0);" class="btn btn-circle icon btn-default" onclick="getRDCDetails('<?=$row['id']?>')">
									<i class="fas fa-bars"></i>
								</a>
							<?php } if (get_permission('rdc_management', 'is_edit')) { ?>
								<a href="<?= base_url('rdc/edit/' . $row['id']); ?>" class="btn btn-circle btn-default icon">
									<i class="fas fa-pen-nib"></i>
								</a>
							<?php } if (get_permission('rdc_management', 'is_delete')) { ?>
								<?php echo btn_delete('rdc/delete_template/' . $row['id']); ?>
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

<!-- View Modal -->
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
	<section class="panel" id='quick_view'></section>
</div>

<script>
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>

<script type="text/javascript">
	$(document).ready(function () {
		// Initialize Summernote
		$('.summernote').summernote({
			height: 100,
			dialogsInBody: true,
			toolbar: [
				['style', ['bold', 'italic', 'underline', 'clear']],
				['font', ['fontsize']],
				['para', ['ul', 'ol', 'paragraph']],
				['insert', ['link']],
				['view', ['fullscreen', 'codeview']]
			]
		});

		// Custom tag button click inserts text
		$('.btn_tag').on('click', function() {
	var txtToAdd = $(this).data("value");
	var $focusedEditor = $('.summernote:focus');
	if ($focusedEditor.length > 0) {
		$focusedEditor.summernote('insertText', txtToAdd);
	} else {
		// fallback to first one
		$('.summernote').eq(0).summernote('insertText', txtToAdd);
	}
});

	});
</script>
<script type="text/javascript">
	$(document).ready(function () {
		$('#daterange').daterangepicker({
			opens: 'left',
		    locale: {format: 'YYYY/MM/DD'}
		});
	});

	// get approvel details
	function getRDCDetails(id) {
	    $.ajax({
	        url: base_url + 'rdc/getRDCDetails',
	        type: 'POST',
	        data: {'id': id},
	        dataType: "html",
	        success: function (data) {
				$('#quick_view').html(data);
				mfp_modal('#modal');
	        }
	    });
	}

	// get Sop details
	function getRDCTemplatesSop(id) {
	    $.ajax({
	        url: base_url + 'rdc/getRDCTemplatesSop',
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