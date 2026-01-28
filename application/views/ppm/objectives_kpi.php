<?php $currency_symbol = $global_config['currency_symbol']; ?>

<div class="row">
<section class="panel col-md-12">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#create" data-toggle="tab">
					<i class="far fa-edit"></i> <?php echo translate('add') . ' ' . translate('Objective'); ?>
				</a>
			</li>
		</ul>

		<div class="tab-content">
			<style>
			.accordion .card {
				border: 1px solid #ddd;
				border-radius: 4px;
				margin-bottom: 10px;
				overflow: hidden;
			}
			.accordion .card-header {
				background-color: #f5f5f5;
				padding: 10px 15px;
				cursor: pointer;
				user-select: none;
			}
			.accordion .card-header.active {
				background-color: #e2e6ea;
			}
			.accordion .card-body {
				padding: 15px;
				display: none;
			}
			.accordion .card-body.open {
				display: block;
			}
			.progress {
				height: 18px;
				background-color: #e9ecef;
				border-radius: 4px;
				margin-bottom: 0;
			}
			.progress-bar {
				background-color: #28a745;
				height: 100%;
				text-align: center;
				color: #fff;
				font-size: 12px;
				line-height: 18px;
			}
			</style>

			<div id="template" class="tab-pane">
				<div class="col-md-12">
					<div class="accordion" id="kpiAccordionAll">
						<div class="row font-weight-bold border-bottom py-2 mb-2 d-none d-md-flex" style="padding-left: 15px; padding-right: 15px;">
							<strong>
								<div class="col-md-3"><?= translate('objectives') ?> & <?= translate('kpi') ?></div>
								<div class="col-md-3"><?= translate('target_completion') ?></div>
								<div class="col-md-2"><?= translate('staff_rating') ?></div>
								<div class="col-md-2"><?= translate('manager_rating') ?></div>
								<div class="col-md-2"><?= translate('actions') ?></div>
							</strong>
						</div>

						<?php
						if (!is_superadmin_loggedin()) {
							$this->db->where('branch_id', get_loggedin_branch_id());
						}
						$formlist = $this->db->get('kpi_form')->result_array();
						$userID = get_loggedin_user_id();
						$RoleID = loggedin_role_id();
						$count = 1;

						foreach ($formlist as $row):
							$form_id = $row['id'];
							$subtasks = $this->db->where('kpi_form_id', $form_id)->get('kpi_form_details')->result_array();
							$total_weight = array_sum(array_column($subtasks, 'weight'));
							$progress = min(intval($total_weight), 100);
							$staff_rating = $row['staff_rating'] ?? null;
							$manager_rating = $row['manager_rating'] ?? null;
							$daterange_parts = explode(' - ', $row['daterange']);
							$end_date = isset($daterange_parts[1]) ? trim($daterange_parts[1]) : '';
							$formatted_end_date = $end_date ? date('jS F, Y', strtotime($end_date)) : '';
						?>
							<div class="card mb-2">
								<div class="card-header p-2" data-toggle="collapse" data-target="#collapse<?= $form_id ?>" style="cursor: pointer;">
									<div class="row align-items-center">
										<div class="col-md-3"><strong><?= $count++ ?>. <?= html_escape($row['objective_name']) ?></strong></div>
										<div class="col-md-3"><?= html_escape($formatted_end_date) ?></div>
										<div class="col-md-2">
											<?php if (in_array($RoleID, [1, 2]) || $userID == $row['staff_id']): ?>
												<a class="btn btn-sm btn-info" href="javascript:void(0);" onclick="getStaffRatingModal('<?= $form_id ?>', '<?= $row['staff_id'] ?>', '<?= $staff_rating ?>')">
													<i class="fas fa-star"></i> <?= $staff_rating !== null ? $staff_rating . ' ⭐' : translate('rate_now') ?>
												</a>
											<?php else: ?>
												<button class="btn btn-sm btn-info" disabled>
													<i class="fas fa-star"></i> <?= $staff_rating !== null ? $staff_rating . ' ⭐' : translate('not_allowed') ?>
												</button>
											<?php endif; ?>
										</div>

										<div class="col-md-2">
											<?php if (in_array($RoleID, [1, 2]) || $userID == $row['manager_id']): ?>
												<a class="btn btn-sm btn-warning" href="javascript:void(0);" onclick="getManagerRatingModal('<?= $form_id ?>', '<?= $row['manager_id'] ?>', '<?= $manager_rating ?>')">
													<i class="fas fa-star-half-alt"></i> <?= $manager_rating !== null ? $manager_rating . ' ⭐' : translate('rate_now') ?>
												</a>
											<?php else: ?>
												<button class="btn btn-sm btn-warning" disabled>
													<i class="fas fa-star-half-alt"></i> <?= $manager_rating !== null ? $manager_rating . ' ⭐' : translate('not_allowed') ?>
												</button>
											<?php endif; ?>
										</div>

										<div class="col-md-2 text-right">
											<?php if (get_permission('objectives_kpi', 'is_edit')): ?>
												<a href="<?= base_url('kpi/kpi_form_edit/' . $form_id) ?>" class="btn btn-sm btn-default"><i class="fas fa-pen-nib"></i></a>
											<?php endif; ?>
											<?php if (get_permission('objectives_kpi', 'is_delete')): ?>
												<a href="javascript:void(0);" class="btn btn-sm btn-danger delete-kpi" data-id="<?= $form_id ?>" data-url="<?= base_url('kpi/kpi_form_delete/' . $form_id) ?>">
													<i class="fas fa-trash-alt"></i>
												</a>
											<?php endif; ?>
										</div>
									</div>
								</div>

								<div class="card-body collapse" id="collapse<?= $form_id ?>">
									<div class="row">
										<div class="col-md-6">
											<p><strong><?= translate('branch') ?>:</strong> <?= get_type_name_by_id('branch', $row['branch_id']); ?></p>
											<p><strong><?= translate('department') ?>:</strong> <?= get_type_name_by_id('staff_department', $row['department_id']); ?></p>
											<p><strong><?= translate('assigned_to') ?>:</strong> <?= get_type_name_by_id('staff', $row['staff_id']); ?></p>
											<p><strong><?= translate('assigned_manager') ?>:</strong> <?= get_type_name_by_id('staff', $row['manager_id']); ?></p>
										</div>
										<div class="col-md-6">
											<p><strong><?= translate('weightage') ?>:</strong> <?= $total_weight ?>%</p>
											<div style="display: flex; align-items: center;">
												<strong style="margin-right: 10px; white-space: nowrap;"><?= translate('progress') ?>:</strong>
												<div style="flex: 1;">
													<div class="progress">
														<div class="progress-bar" style="width: <?= $progress ?>%;"><?= $progress ?>%</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<hr>
									<h6><?= translate('KPI_list') ?>:</h6>
									<ol>
										<?php foreach ($subtasks as $i => $task): ?>
											<li>
												<strong>KPI <?= $i + 1 ?>: <?= html_escape($task['name']) ?></strong><br>
												<small><?= html_escape($task['description']) ?></small> — 
												<span class="badge badge-secondary"><?= intval($task['weight']) ?>%</span>
											</li>
										<?php endforeach; ?>
									</ol>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<?php if (get_permission('objectives_kpi', 'is_add')): ?>
			<div id="create" class="tab-pane active">
				<?php echo form_open('kpi', ['method' => 'post']); ?>
				<div class="form-group">
					<label class="col-md-3 control-label"><?php echo translate('objective_name'); ?> <span class="required">*</span></label>
					<div class="col-md-6">
						<input type="text" class="form-control" name="objective_name" placeholder="Objective Name Here" />
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-3 control-label"><?php echo translate('assigned_to'); ?> <span class="required">*</span></label>
					<div class="col-md-6">

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
                        echo form_dropdown("staff_id", $staffArray, set_value('staff_id'), "class='form-control' id='staff_id'
                        data-plugin-selectTwo data-width='100%' ");
                    ?>
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-3 control-label"><?php echo translate('assigned_manager'); ?> <span class="required">*</span></label>
					<div class="col-md-6">
						<?php
							echo form_dropdown("manager_id", $staffArray, '', "class='form-control' id='e_manager_id' data-plugin-selectTwo data-width='100%'");
						?>
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-3 control-label"><?= translate('estimate_date') ?> <span class="required">*</span></label>
					<div class="col-md-6">
						<div class="input-group">
							<span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
							<input type="text" class="form-control" name="daterange" id="daterange" value="<?= set_value('daterange', date("Y/m/d") . ' - ' . date("Y/m/d")) ?>" required />
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-12 mt-lg">
						<section class="panel panel-custom">
							<header class="panel-heading panel-heading-custom">
								<h4 class="panel-title"><?= translate('Objective_lists'); ?></h4>
							</header>
							<div class="panel-body">
								<div class="row">
									<div class="col-md-4 mt-sm">
										<input type="text" class="form-control" name="subtasks[0][name]" placeholder="<?= translate('name'); ?>" />
									</div>
									<div class="col-md-4 mt-sm">
										<input type="text" class="form-control" name="subtasks[0][description]" placeholder="<?= translate('description'); ?>" />
									</div>
									<div class="col-md-4 mt-sm">
										<input type="number" class="form-control" name="subtasks[0][weight]" placeholder="<?= translate('weight'); ?>" min="0" />
									</div>
								</div>
								<div id="add_new_subtask"></div>
								<button type="button" class="btn btn-default mt-md" onclick="addSubtaskRows()">
									<i class="fas fa-plus-circle"></i> <?= translate('add_rows'); ?>
								</button>
							</div>
						</section>
					</div>
				</div>

				<footer class="panel-footer">
					<div class="row">
						<div class="col-md-offset-9 col-md-3">
							<button type="submit" class="btn btn-default btn-block">
								<i class="fas fa-plus-circle"></i> <?= translate('save') ?>
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

<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="ratingModal">
    <section class="panel">
        <header class="panel-heading">
            <h4 class="panel-title"><i class="fas fa-star"></i> <?= translate('give_rating') ?></h4>
        </header>
        <?php echo form_open('kpi/save_rating', array('class' => 'frm-submit-rating')); ?>
        <div class="panel-body">
            <input type="hidden" name="form_id" id="rating_form_id" />
            <input type="hidden" name="staff_id" id="rating_staff_id" />
            <input type="hidden" name="role" id="rating_role" />

            <div class="form-group">
                <label class="control-label"><?= translate('rating') ?> (1–5) <span class="required">*</span></label>
                <select name="rating" id="rating_value" class="form-control" required>
                    <option value=""><?= translate('select') ?></option>
                    <option value="1">1 ⭐</option>
                    <option value="2">2 ⭐⭐</option>
                    <option value="3">3 ⭐⭐⭐</option>
                    <option value="4">4 ⭐⭐⭐⭐</option>
                    <option value="5">5 ⭐⭐⭐⭐⭐</option>
                </select>
                <span class="error"></span>
            </div>
        </div>
        <footer class="panel-footer">
            <div class="row">
                <div class="col-md-12 text-right">
                    <button type="submit" class="btn btn-default" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
                        <i class="fas fa-save"></i> <?= translate('save') ?>
                    </button>
                    <button class="btn btn-default modal-dismiss"><?= translate('cancel') ?></button>
                </div>
            </div>
        </footer>
        <?php echo form_close(); ?>
    </section>
</div>

<script>
function getStaffRatingModal(formId, staffId, currentRating = '') {
    $('#rating_form_id').val(formId);
    $('#rating_staff_id').val(staffId);
    $('#rating_role').val('staff');
    $('#rating_value').val(currentRating).trigger('change');
    mfp_modal('#ratingModal');
}

function getManagerRatingModal(formId, managerId, currentRating = '') {
    $('#rating_form_id').val(formId);
    $('#rating_staff_id').val(managerId);
    $('#rating_role').val('manager');
    $('#rating_value').val(currentRating).trigger('change');
    mfp_modal('#ratingModal');
}

</script>

<!-- External Scripts -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/min/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
$(document).ready(function () {
	$('#daterange').daterangepicker({
		opens: 'left',
		locale: { format: 'YYYY/MM/DD' }
	});
});
</script>

<script>
var iSubtask = 1;
function addSubtaskRows() {
	let html = `
	<div class="row" id="subtask_row_${iSubtask}">
		<div class="col-md-4 mt-md">
			<input type="text" class="form-control" name="subtasks[${iSubtask}][name]" placeholder="<?= translate('name'); ?>" />
		</div>
		<div class="col-md-4 mt-md">
			<input type="text" class="form-control" name="subtasks[${iSubtask}][description]" placeholder="<?= translate('description'); ?>" />
		</div>
		<div class="col-md-3 mt-md">
			<input type="number" class="form-control" name="subtasks[${iSubtask}][weight]" placeholder="<?= translate('weight'); ?>" min="0" />
		</div>
		<div class="col-md-1 mt-md text-right">
			<button type="button" class="btn btn-danger" onclick="deleteSubtaskRow(${iSubtask})"><i class="fas fa-times"></i></button>
		</div>
	</div>`;
	$('#add_new_subtask').append(html);
	iSubtask++;
}

function deleteSubtaskRow(id) {
	$('#subtask_row_' + id).remove();
}

$(document).on('click', '.delete-kpi', function () {
	const url = $(this).data('url');
	if (confirm("<?= translate('are_you_sure_to_delete') ?>")) {
		$.ajax({
			url: url,
			type: 'GET',
			success: function () {
				alert("<?= translate('deleted_successfully') ?>");
				location.reload();
			},
			error: function () {
				alert("<?= translate('delete_failed') ?>");
			}
		});
	}
});

document.addEventListener("DOMContentLoaded", function () {
	const headers = document.querySelectorAll(".accordion .card-header");
	headers.forEach(header => {
		header.addEventListener("click", function () {
			const targetId = this.getAttribute("data-target");
			const content = document.querySelector(targetId);
			if (content.classList.contains("open")) {
				content.classList.remove("open");
				this.classList.remove("active");
			} else {
				document.querySelectorAll(".accordion .card-body").forEach(el => el.classList.remove("open"));
				document.querySelectorAll(".accordion .card-header").forEach(el => el.classList.remove("active"));
				content.classList.add("open");
				this.classList.add("active");
			}
		});
	});
});
</script>