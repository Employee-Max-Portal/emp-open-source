<div class="row">
	<div class="col-md-12">
		<?php if (get_permission('todo', 'is_add')): ?>
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><?= translate('select_ground') ?></h4>
					<div class="panel-btn">
						<a href="javascript:void(0);" id="addTodo" class="btn btn-default btn-circle">
							<i class="fas fa-plus-circle"></i> Add
						</a>
					</div>
			</header>
			<?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
				<div class="panel-body">
					<div class="row mb-sm">
							<div class="col-md-6 mb-sm">
								<div class="form-group">
									<label class="control-label"><?= translate('business'); ?> <span class="required">*</span></label>
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
				<h4 class="panel-title"><i class="fas fa-users" aria-hidden="true"></i> <?=translate('to - Do_list')?></h4>
			</header>
			<div class="panel-body">
				<table class="table table-bordered table-condensed table-hover mb-none table-export" >
					<thead>
						<tr>
							<th><?=translate('sl')?></th>
							<th><?=translate('applicant')?></th>
							<th><?=translate('manager')?></th>
                            <th><?=translate('issue_date')?></th>
                            <th><?=translate('clearance_time')?></th>
                            <th><?=translate('cleared_on')?></th>
							<th style="text-align:center;" class="no-sort"><?=translate('employee_review')?></th>
							<th class="no-sort"><?=translate('manager_review')?></th>
							<th class="no-sort"><?=translate('advisor_review')?></th>
							<th style="width:90px;"><?=translate('action')?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$count = 1;
						if (count($warning_list)) { 
							foreach($warning_list as $row) {
								?>
						<tr>
							<td><?php echo $count++; ?></td>
							<td><?php
							echo !empty($row['orig_file_name']) ? '<i class="fas fa-paperclip"></i> ' : '';
							$getStaff = $this->db->select('name,staff_id')->where('id', $row['user_id'])->get('staff')->row_array();
							echo $getStaff['staff_id'] . ' - ' .$getStaff['name'];
							?></td>
							<td><?php
							$getStaff = $this->db->select('name,staff_id')->where('id', $row['manager_id'])->get('staff')->row_array();
							echo $getStaff['name'];
							?></td>
						
							<td>
								<?php
									$issueDate = new DateTime($row['issue_date']);
									echo $issueDate->format('jS F, Y \a\t h.i A');
								?>
							</td>

							<td>
								<?php
									if (!empty($row['clearance_time'])) {
										$clearance = new DateTime($row['issue_date']);
										$clearance->modify('+' . (int)$row['clearance_time'] . ' hours');
										echo $clearance->format('jS F, Y \a\t h.i A');
									} else {
										echo '-';
									}
								?>
							</td>
							<td>
								<?php
									if (!empty($row['cleared_on'])) {
										$cleared_on = new DateTime($row['cleared_on']);
										echo $cleared_on->format('jS F, Y \a\t h.i A');
									} else {
										echo '-';
									}
								?>
							</td>

							<td style="text-align:center;">
								<?php
								if ($row['status'] == 1)
									$status = '<span class="label label-warning-custom text-xs">' . translate('pending') . '</span>';
								else if ($row['status']  == 2)
									$status = '<span class="label label-success-custom text-xs">' . translate('accepted') . '</span>';
								else if ($row['status']  == 3)
									$status = '<span class="label label-danger-custom text-xs">' . translate('rejected') . '</span>';
								else if ($row['status']  == 4)
									$status = '<span class="label label-danger-custom text-xs">' . translate('penalty') . '</span>';
								echo ($status);
								?>
							</td>
							<td>
								<?php
								if ($row['manager_review'] == 1)
									$status2 = '<span class="label label-warning-custom text-xs">' . translate('pending') . '</span>';
								else if ($row['manager_review']  == 2)
									$status2 = '<span class="label label-success-custom text-xs">' . translate('accepted') . '</span>';
								else if ($row['manager_review']  == 3)
									$status2 = '<span class="label label-danger-custom text-xs">' . translate('rejected') . '</span>';
								else if ($row['manager_review']  == 4)
									$status2 = '<span class="label label-danger-custom text-xs">' . translate('penalty') . '</span>';
								else if ($row['manager_review']  == 5)
									$status2 = '<span class="label label-info-custom text-xs">' . translate('unsatisfied') . '</span>';
								else if ($row['manager_review']  == 0)
									$status2 = '<div style="text-align:center;">' . translate('---') . '</div>';

								echo ($status2);
								?>
							</td>
							<td>
								<?php
								if (isset($row['advisor_review'])) {
									if ($row['advisor_review'] == 1)
										$status3 = '<span class="label label-warning-custom text-xs">' . translate('pending') . '</span>';
									else if ($row['advisor_review']  == 2)
										$status3 = '<span class="label label-success-custom text-xs">' . translate('accepted') . '</span>';
									else if ($row['advisor_review']  == 3)
										$status3 = '<span class="label label-danger-custom text-xs">' . translate('rejected') . '</span>';
									else if ($row['advisor_review']  == 4)
										$status3 = '<span class="label label-danger-custom text-xs">' . translate('penalty') . '</span>';
									else if ($row['advisor_review']  == 5)
										$status3 = '<span class="label label-info-custom text-xs">' . translate('unsatisfied') . '</span>';
									else if ($row['advisor_review']  == 0)
										$status3 = '<div style="text-align:center;">' . translate('---') . '</div>';
								} else {
									$status3 = '<div style="text-align:center;">' . translate('---') . '</div>';
								}
								echo ($status3);
								?>
							</td>
							<td>
							  <a class="btn btn-info btn-circle icon" href="javascript:void(0);" data-toggle="tooltip" data-original-title="<?php echo translate('View To-Do'); ?>"
                                                onclick="getView('<?php echo html_escape($row['id']); ?>')">
                                                    <i class="fas fa-eye" style="color: #ffffff;"></i>
                                                </a>
							<?php if (get_permission('todo', 'is_edit')) { ?>
								<a href="javascript:void(0);" class="btn btn-circle icon btn-default" onclick="getApprovelDetails('<?=$row['id']?>')">
									<i class="fas fa-bars"></i>
								</a>
							<?php } ?>
							<?php if (get_permission('todo', 'is_delete')) { ?>
								<?php echo btn_delete('todo/delete/' . $row['id']); ?>
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

<?php if (get_permission('todo', 'is_add')): ?>
<!-- Add Modal -->
<div id="addModal" class="zoom-anim-dialog modal-block mfp-hide modal-block-lg">
    <section class="panel">
        <div class="panel-heading">
            <h4 class="panel-title"><i class="fas fa-plus-circle"></i> <?php echo translate('add_To-Do'); ?></h4>
        </div>
        <!-- Changed from frm-submit-data to regular form submission -->
        <?php echo form_open_multipart('todo/save_warning', array('class' => 'form-horizontal', 'method' => 'POST')); ?>
        <div class="panel-body">
  
			<div class="form-group">
			  <label class="col-md-3 control-label"><?= translate('reference') ?> <span class="required">*</span></label>
			  <div class="col-md-8">
				<?php
				  // Get all policies
				  $this->db->select('id, title');
				  $this->db->from('policy');
				  $query = $this->db->get();
				  $policies = $query->result_array();

				  // Initialize dropdown array
				  $array = array('' => 'Select a Policy');
				  foreach ($policies as $row) {
					  $array[$row['id']] = $row['title']; // title as key & value
				  }

				  echo form_dropdown(
					  "reference",
					  $array,
					  set_value('reference'),
					  "class='form-control' id='reference' data-plugin-selectTwo data-width='100%'"
				  );
				?>
				<span class="error"></span>
			  </div>
			</div>



			<!-- Category Dropdown -->
			<div class="form-group">
				<label class="col-md-3 control-label"><?= translate('category') ?></label>
				<div class="col-md-8">
					<select class="form-control" name="category" id="category">
						<option value="">Select Category</option>
						<option value="Violation">Violation</option>
						<option value="Dispute">Dispute</option>
						<option value="Pending Jobs">Pending Jobs</option>
						<option value="others">Others</option>
					</select>
					<input type="text" class="form-control mt-2" id="category_other" name="category_other" placeholder="Enter custom category" style="display:none;">
					<span class="error"><?php echo form_error('category'); ?></span>
				</div>
			</div>

			<!-- Effect Dropdown -->
			<div class="form-group">
				<label class="col-md-3 control-label"><?= translate('effect') ?></label>
				<div class="col-md-8">
					<select class="form-control" name="effect" id="effect">
						<option value="">Select Effect</option>
						<option value="Multiple User">Multiple User</option>
						<option value="User">User</option>
						<option value="Business">Business</option>
						<option value="Customer">Customer</option>
						<option value="others">Others</option>
					</select>
					<input type="text" class="form-control mt-2" id="effect_other" name="effect_other" placeholder="Enter custom effect" style="display:none;">
					<span class="error"><?php echo form_error('effect'); ?></span>
				</div>
			</div>

            <div class="form-group">
                <label class="col-md-3 control-label"><?=translate('applicant')?> <span class="required">*</span></label>
                <div class="col-md-8">
					<?php
					$this->db->select('s.id, s.name');
					$this->db->from('staff AS s');
					$this->db->join('login_credential AS lc', 'lc.user_id = s.id');
					$this->db->where('lc.active', 1);   // only active users
					$this->db->where_not_in('lc.role', [1, 9, 11,12]);   // exclude super admin, etc.
					$this->db->where_not_in('s.id', [49,23,37]);
					$this->db->order_by('s.name', 'ASC');
					$query = $this->db->get();

					$staffArray = ['' => 'Select']; // <-- default first option
					foreach ($query->result() as $row) {
						$staffArray[$row->id] = $row->name;
					}
					echo form_dropdown("applicant_id[]", $staffArray, array(), "class='form-control' id='applicant_id' required multiple data-plugin-selectTwo data-placeholder=\"Select\" data-width='100%' ");
					?>

                    <span class="error"><?= form_error('applicant_id[]') ?></span>
                </div>
            </div>
           <div class="form-group">
				<label class="col-md-3 control-label"><?= translate('penalty_workdays') ?></label>
				<div class="col-md-8">
					<input type="number" class="form-control" id="penalty" name="penalty">
					<span class="error"><?php echo form_error('penalty'); ?></span>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-3 control-label"><?= translate('description') ?><span class="required">*</span></label>
				<div class="col-md-8">
					<!--<textarea name="reason" id="reason" class="summernote"></textarea>-->
					<textarea class="form-control" id="reason"  name="reason" rows="3" required ></textarea>
					<span class="error"><?php echo form_error('reason'); ?></span>
				</div>
			</div>
			 <div class="form-group">
                <label class="col-md-3 control-label"><?php echo translate('attachment'); ?></label>
                <div class="col-md-8">
                    <input type="file" name="attachment_file" id="attachment_file" class="dropify" data-height="80" />
                    <span class="error"></span>
                </div>
            </div>
			
			<div class="form-group">
				<label class="col-md-3 control-label">Deadline <span class="required">*</span></label>
				<div class="col-md-8">
					<?php
						// Define clearance options
						$clearance_options = array(
							''    => 'Select Deadline',
							/* '6'   => '6 Hours',
							'12'  => '12 Hours', */
							'24'  => '24 Hours',
							'48'  => '48 Hours',
							'72'  => '72 Hours',
						);

						echo form_dropdown("clearance_time", $clearance_options, set_value('clearance_time'), "class='form-control' id='clearance_time' required data-plugin-selectTwo data-width='100%' ");
					?>
					<span class="error"><?php echo form_error('clearance_time'); ?></span>
				</div>
			</div>
        </div>
        <footer class="panel-footer">
            <div class="row">
                <div class="col-md-12 text-right">

					<button type="submit" class="btn btn-default mr-xs" id="savebtn"
							onclick="let btn = this; setTimeout(() => { btn.disabled = true; btn.innerHTML = '<i class=\'fas fa-spinner fa-spin\'></i> Processing'; setTimeout(() => { btn.disabled = false; btn.innerHTML = '<i class=\'fas fa-plus-circle\'></i> <?=translate('apply')?>'; }, 2000); }, 50);">
						<i class="fas fa-plus-circle"></i> <?=translate('apply')?>
					</button>
                    <!-- Changed to type="button" to prevent form submission -->
                    <button type="button" class="btn btn-default modal-dismiss"><?=translate('cancel') ?></button>
                </div>
            </div>
        </footer>
        <?php echo form_close();?>
    </section>
</div>
	
<div class="zoom-anim-dialog modal-block modal-block-lg mfp-hide payroll-t-modal" id="modal_equipment_details" style="width: 100%!important;">
    <section class="panel">
        <header class="panel-heading d-flex justify-content-between align-items-center">
            <div class="row">
                <div class="col-md-6 text-left">
                    <h4 class="panel-title">
						<i class="fas fa-bars"></i> <?php echo translate('To-Do') . " " . translate('Description'); ?>
					</h4>
                </div>
                <div class="col-md-5 text-right">
                    <!-- Print Button in Footer -->
                    <button class="btn btn-primary" onclick="printDescription()">🖨️ Print</button>
                </div>
            </div>
        </header>
        <div class="panel-body">
            <div id="equipment_details_view_tray">
                <!-- The description content will be loaded here dynamically -->
            </div>
        </div>
        <footer class="panel-footer">
            <div class="row">
                <div class="col-md-6 text-left">
                    <!-- Print Button in Footer -->
                    <button class="btn btn-primary" onclick="printDescription()">🖨️ Print</button>
                </div>
                <div class="col-md-6 text-right">
                    <button class="btn btn-default modal-dismiss"><?php echo translate('close'); ?></button>
                </div>
            </div>
        </footer>
    </section>
</div>

<?php endif; ?>

<!-- JS to Toggle Custom Input -->
<script>
	document.getElementById("category").addEventListener("change", function () {
		document.getElementById("category_other").style.display = this.value === "others" ? "block" : "none";
	});
	document.getElementById("effect").addEventListener("change", function () {
		document.getElementById("effect_other").style.display = this.value === "others" ? "block" : "none";
	});
</script>

<script>
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>


<script>
function getView(id) {
    $.ajax({
        url: base_url + 'todo/get_view_description', // Update the URL to the correct controller path
        type: 'POST',
        data: { id: id },
        success: function(response) {
            // Inject the response into the modal
            $('#equipment_details_view_tray').html(response);

            // Open the modal
            $.magnificPopup.open({
                items: {
                    src: '#modal_equipment_details'
                },
                type: 'inline'
            });
        },
        error: function() {
            alert('Failed to retrieve description.');
        }
    });
}

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

	    $('#addTodo').on('click', function(){
	        mfp_modal('#addModal');
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

	// get approvel details
	function getApprovelDetails(id) {
	    $.ajax({
	        url: base_url + 'todo/getApprovelDetails',
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