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

    .card {
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    }

    .card-body {
        padding: 20px;
    }

    .card-title {
        font-size: 1.1rem;
        font-weight: bold;
        color: #333;
    }

    .form-control {
        font-size: 14px;
        padding: 10px;
    }

    .form-group label {
        font-weight: bold;
    }

    .form-group .d-flex {
        gap: 15px;
    }

    .mb-3 {
        margin-bottom: 20px;
    }

    .form-control.mt-2 {
        margin-top: 10px;
    }

/* Drawer styles */
.drawer-modal {
	position: fixed;
	top: 0;
	right: -100%;
	width: 500px;
	max-width: 90%;
	height: 100%;
	background: #fff;
	box-shadow: -2px 0 8px rgba(0, 0, 0, 0.3);
	z-index: 1050;
	transition: right 0.4s ease;
	overflow-y: auto;
}
.drawer-modal.open {
	right: 0;
}
.drawer-overlay {
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background: rgba(0, 0, 0, 0.3);
	z-index: 1040;
	display: none;
}
.drawer-overlay.active {
	display: block;
}
</style>


<!-- Optional Custom Styles (adjust according to your needs) -->
<style>
    .form-group label {
        font-weight: bold;
    }

    .form-control {
        font-size: 14px;
        padding: 8px;
    }

    .form-group .d-flex {
        gap: 10px;
    }

    .panel-body {
        padding: 20px;
    }

    .col-md-6 {
        padding-right: 15px;
        padding-left: 15px;
    }
</style>

	<div class="col-md-12">
		<div class="panel-group" id="accordion">
			<div class="panel panel-accordion">
				<div class="panel-heading">
					<h4 class="panel-title">
                 
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#profile">
							<i class="fas fa-user-edit"></i> <?=translate('objectives_kpi')?>
						</a>
					</h4>
				</div>
<div id="profile" class="accordion-body collapse <?= ($this->session->flashdata('profile_tab') ? 'in' : ''); ?>">
    <div class="panel-body">
        <div class="accordion" id="kpiAccordionAll">

           <!-- Desktop Header -->
			<div class="row font-weight-bold border-bottom py-2 mb-2 desktop-header px-2">
				<div class="col-md-3"><?= translate('objectives') ?> & <?= translate('kpi') ?></div>
				<div class="col-md-3"><?= translate('target_completion') ?></div>
				<div class="col-md-2"><?= translate('staff_rating') ?></div>
				<div class="col-md-2"><?= translate('manager_rating') ?></div>
				<div class="col-md-2"><?= translate('actions') ?></div>
			</div>


            <?php foreach ($kpi_data ?? [] as $count => $row):
                $form_id = $row['id'];
                $subtasks = $row['subtasks'] ?? [];
                $staff_rating = $row['staff_rating'] ?? null;
                $manager_rating = $row['manager_rating'] ?? null;
                $daterange_parts = explode(' - ', $row['daterange']);
                $formatted_end_date = !empty($daterange_parts[1]) ? date('jS F, Y', strtotime($daterange_parts[1])) : '';
            ?>
                <div class="card mb-3">
                    <div class="card-header p-2" data-toggle="collapse" data-target="#collapse<?= $form_id ?>" style="cursor:pointer;">
                        <div class="row align-items-center gy-2 gx-2">
                          <div class="col-12 col-md-3">
							<strong><?= $count + 1 ?>. <?= html_escape($row['objective_name']) ?></strong>
						</div>

						<div class="col-6 col-md-3">
							<span class="mobile-label"><?= translate('target_completion') ?>:</span>
							<?= html_escape($formatted_end_date) ?>
						</div>

						<div class="col-6 col-md-2">
							<span class="mobile-label"><?= translate('staff_rating') ?>:</span>
							<a class="btn btn-sm btn-info w-100" <?= (in_array(loggedin_role_id(), [1, 2]) || get_loggedin_user_id() == $row['staff_id']) ? "onclick=\"getStaffRatingModal('{$form_id}', '{$row['staff_id']}', '{$staff_rating}')\"" : "disabled" ?>>
								<?= $staff_rating !== null ? $staff_rating . ' %' : translate('rate_now') ?>
							</a>
						</div>

						<div class="col-6 col-md-2">
							<span class="mobile-label"><?= translate('manager_rating') ?>:</span>
							<a class="btn btn-sm btn-warning w-100" <?= (in_array(loggedin_role_id(), [1, 2]) || get_loggedin_user_id() == $row['manager_id']) ? "onclick=\"getManagerRatingModal('{$form_id}', '{$row['manager_id']}', '{$manager_rating}')\"" : "disabled" ?>>
								<?= $manager_rating !== null ? $manager_rating . ' %' : translate('rate_now') ?>
							</a>
						</div>

						<div class="col-6 col-md-2 d-flex flex-wrap justify-content-start gap-1 mt-2 mt-md-0">
							<span class="mobile-label w-100"><?= translate('actions') ?>:</span>
							<?php if (in_array(loggedin_role_id(), [1, 2]) || get_loggedin_user_id() == $row['staff_id'] || get_loggedin_user_id() == $row['manager_id']): ?>
								<a class="btn btn-sm btn-success" onclick="getFeedbackModal('<?= $form_id ?>', '<?= $row['staff_id'] ?>', `<?= $row['feedback'] ?? '' ?>`)"><i class="fas fa-comments"></i></a>
							<?php endif; ?>
							<?php if (get_permission('objectives_kpi', 'is_edit')): ?>
								<a class="btn btn-sm btn-default" href="<?= base_url('kpi/kpi_form_edit/' . $form_id) ?>"><i class="fas fa-pen-nib"></i></a>
							<?php endif; ?>
							<?php if (get_permission('objectives_kpi', 'is_delete')): ?>
								<?= btn_delete('kpi/kpi_form_delete_v2/' . $form_id) ?>
							<?php endif; ?>
						</div>

                        </div>
                    </div>

                    <div class="card-body collapse" id="collapse<?= $form_id ?>">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <p><strong><?= translate('business') ?>:</strong> <?= get_type_name_by_id('branch', $row['branch_id']) ?></p>
                                <p><strong><?= translate('department') ?>:</strong> <?= get_type_name_by_id('staff_department', $row['department_id']) ?></p>
                                <p><strong><?= translate('assigned_to') ?>:</strong> <?= get_type_name_by_id('staff', $row['staff_id']) ?></p>
                                <p><strong><?= translate('assigned_manager') ?>:</strong> <?= get_type_name_by_id('staff', $row['manager_id']) ?></p>
                            </div>
                            <div class="col-12 col-md-6">
                                <p><strong><?= translate('weightage') ?>:</strong> <?= $row['total_weight'] ?>%</p>
                                <div class="d-flex align-items-center">
                                    <strong class="me-2"><?= translate('progress') ?>:</strong>
                                    <div class="flex-grow-1">
                                        <div class="progress">
                                            <div class="progress-bar" style="width: <?= $row['progress'] ?>%;">
                                                <?= $row['progress'] ?>%
                                            </div>
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
                                    <strong>KPI <?= $i + 1 ?>:</strong> <?= html_escape($task['name']) ?><br>
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

<style>
@media (min-width: 992px) {
    .mobile-label {
        display: none !important;
    }
}
@media (max-width: 991.98px) {
    .mobile-label {
        display: block;
        font-size: 12px;
        color: #6c757d; /* muted text */
        margin-bottom: 2px;
    }
}


@media (max-width: 767.98px) {
	
	.desktop-header {
        display: none !important;
    }
	
    .card-header .btn {
        font-size: 13px;
        padding: 4px 8px;
    }
    .progress-bar {
        font-size: 11px;
    }
    .accordion .card-header {
        padding: 8px 10px;
    }
    .btn {
        margin-bottom: 4px;
    }
}

.accordion .card {
    border: 1px solid #dee2e6;
    border-radius: 6px;
    margin-bottom: 12px;
}

.accordion .card-header {
    background: #f8f9fa;
    user-select: none;
}

.progress {
    height: 18px;
    border-radius: 6px;
    background: #e9ecef;
}

.progress-bar {
    background-color: #28a745;
    height: 100%;
    text-align: center;
    color: #fff;
    line-height: 18px;
}

</style>


		</div>	
		</div>
		
		<div class="panel-group" id="accordion">
				<div class="panel panel-accordion">
					<div class="panel-heading">
						<h4 class="panel-title">
							<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#competencies_behaviours">
								<i class="far fa-address-card"></i> <?=translate('competencies_&_behaviours')?>
							</a>
						</h4>
					</div>
				   <div id="competencies_behaviours" class="accordion-body collapse">
						<?php echo form_open_multipart($this->uri->uri_string()); ?>
						<input type="hidden" name="staff_id" id="staff_id" value="<?php echo $row['staff_id']; ?>">
						<input type="hidden" name="manager_id" id="manager_id" value="<?php echo $row['manager_id']; ?>">
						<div class="panel-body">
							<div class="row">
								<?php 
								$fields = [
									'Customer First',
									'Personal Effectiveness',
									'Driven to Deliver',
									'Commercial Focus',
									'Upholding Standards',
									'Inspiring Leadership'
								];
								
								$tooltips = [
									'Customer First' => 'Puts customer needs first and builds trust.',
									'Personal Effectiveness' => 'Manages self well and works smart under pressure.',
									'Driven to Deliver' => 'Takes ownership and completes work on time.',
									'Commercial Focus' => 'Thinks about company goals and smart decisions.',
									'Upholding Standards' => 'Follows rules and works with honesty.',
									'Inspiring Leadership' => 'Leads by example and supports the team.'
								];


								$kpi_data = isset($kpi_comp_behav) ? $kpi_comp_behav : []; // prefilled data from controller

								foreach ($fields as $field) { 
									$field_key = str_replace(' ', '_', strtolower($field));
								?>
									<div class="col-md-6 mb-3">
										<div class="card">
											<div class="card-body">
												<div style="background-color: #f0f4f8; padding: 10px; border-radius: 8px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
													<div style="font-size: 1.5rem; font-weight: bold;">
														<?= translate($field) ?>
													</div>
													<i class="fas fa-info-circle text-primary"
													   data-toggle="tooltip"
													   data-placement="top"
													   title="<?= $tooltips[$field] ?>"></i>
												</div>


												<div class="d-flex justify-content-between mb-3">
													
												<div class="row">
													<div class="col-md-6">
														 <div class="form-group flex-fill mr-2">
															<label for="<?=$field_key?>_staff_rating" class="font-weight-bold"><?=translate('Staff Rating')?></label>
															<select class="form-control" id="<?=$field_key?>_staff_rating" name="<?=$field_key?>_staff_rating">
																<option>-Select-</option>
																<?php for ($i = 10; $i <= 100; $i += 10): ?>
																	<option value="<?=$i?>" <?= isset($kpi_data[$field_key . '_staff_rating']) && $kpi_data[$field_key . '_staff_rating'] == $i ? 'selected' : '' ?>><?=$i?>%</option>
																<?php endfor; ?>
															</select>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group flex-fill ml-2">
															<label for="<?=$field_key?>_manager_rating" class="font-weight-bold"><?=translate('Manager Rating')?></label>
															<select class="form-control" id="<?=$field_key?>_manager_rating" name="<?=$field_key?>_manager_rating">
																<option>-Select-</option>
																<?php for ($i = 10; $i <= 100; $i += 10): ?>
																	<option value="<?=$i?>" <?= isset($kpi_data[$field_key . '_manager_rating']) && $kpi_data[$field_key . '_manager_rating'] == $i ? 'selected' : '' ?>><?=$i?>%</option>
																<?php endfor; ?>
															</select>
														</div>
													</div>
												</div>
												
												<div class="row">
													<div class="col-md-6">
														<div class="form-group flex-fill mr-2">
															<label for="<?=$field_key?>_staff_feedback" class="font-weight-bold"><?=translate('Staff Feedback')?></label>
															<textarea class="form-control mt-2" 
																name="<?=$field_key?>_staff_feedback" 
																id="<?=$field_key?>_staff_feedback" 
																placeholder="<?=translate('Add Feedback')?>"><?= isset($kpi_data[$field_key . '_staff_feedback']) ? $kpi_data[$field_key . '_staff_feedback'] : '' ?></textarea>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group flex-fill ml-2">
															<label for="<?=$field_key?>_manager_feedback" class="font-weight-bold"><?=translate('Manager Feedback')?></label>
															<textarea class="form-control mt-2" 
																name="<?=$field_key?>_manager_feedback" 
																id="<?=$field_key?>_manager_feedback" 
																placeholder="<?=translate('Add Feedback')?>"><?= isset($kpi_data[$field_key . '_manager_feedback']) ? $kpi_data[$field_key . '_manager_feedback'] : '' ?></textarea>
														</div>
													</div>
												</div>

												</div>
												
											</div>
										</div>
									</div>
								<?php } ?>
							</div>
						</div>
						<div class="panel-footer">
							<div class="row">
								<div class="col-md-offset-9 col-md-3">
									<button type="submit" name="submit" value="update1" class="btn btn-default btn-block"><?=translate('update')?></button>
								</div>
							</div>
						</div>
						<?= form_close(); ?>
					</div>
				</div>
		</div>

		<div class="panel-group" id="accordion">
			<div class="panel panel-accordion">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#approval_hierarchy">
							<i class="fas fa-university"></i> <?=translate('approval_hierarchy')?>
						</a>
					</h4>
				</div>
				<div id="approval_hierarchy" class="accordion-body collapse <?=($this->session->flashdata('approval_hierarchy') == 1 ? 'in' : ''); ?>">
					<div class="panel-body">
						<div class="text-right mb-sm">
							<a href="javascript:void(0);" onclick="mfp_modal('#addModal')" class="btn btn-circle btn-default mb-sm">
								<i class="fas fa-plus-circle"></i> <?=translate('add')?>
							</a>
						</div>
						<div class="table-responsive mb-md">
							<table class="table table-bordered table-hover table-condensed table-export mb-none">
								<thead>
									<tr>
										<th><?=translate('Phase')?></th>
										<th><?=translate('approver')?></th>
										<th><?=translate('Status')?></th>
										<th><?=translate('acted on')?></th>
										<th><?=translate('Remarks')?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									$count = 1;
									// Assuming $kpi_approval is the array you provided
									if (!empty($kpi_approval)) {
										foreach ($kpi_approval as $approval) {
										$created_at = date("d M, Y \t h:i A", strtotime($approval->created_at));
											?>
											<tr>
												<td><?php echo $approval->phase; ?></td>
												<td><?php echo $approval->name; ?></td>
												<td><?php echo $approval->status; ?></td>
												<td><?php echo $created_at; ?></td>
												<td><?php echo $approval->remarks; ?></td>
												
											</tr>
											<?php
										}
									} else {
										echo '<tr> <td colspan="8"> <h5 class="text-danger text-center">' . translate('no_information_available') . '</h5> </td></tr>';
									}
									?>
								</tbody>
							</table>

						</div>
					</div>
				</div>
			</div>
		</div>
		
</div>

<!-- Approval Details Add Modal -->
<div id="addModal" class="zoom-anim-dialog modal-block modal-block-primary mfp-hide">
    <section class="panel">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="fas fa-plus-circle"></i> <?php echo translate('add'); ?>
            </h4>
        </div>

        <!-- Open form and post to the correct controller method -->
        <?php echo form_open('kpi/save_approvals', array('class' => 'form-horizontal frm-submit')); ?>
		
           <input type="hidden" name="staff_id" value="<?php echo $row['staff_id']; ?>">
            <div class="panel-body">
                <!-- Phase Dropdown -->
                <div class="form-group mt-sm">
                    <label class="col-md-3 control-label"><?php echo translate('phase'); ?> <span class="required">*</span></label>
                    <div class="col-md-9">
                        <select class="form-control" name="phase" required>
                            <option value=""><?php echo translate('select'); ?></option>
                            <option value="objective_settings"><?php echo translate('objective_settings'); ?></option>
                            <option value="mid_year_review"><?php echo translate('mid_year_review'); ?></option>
                            <option value="annual_review"><?php echo translate('annual_review'); ?></option>
                        </select>
                        <span class="error"></span>
                    </div>
                </div>

                <!-- Status Dropdown -->
                <div class="form-group mt-sm">
                    <label class="col-md-3 control-label"><?php echo translate('status'); ?> <span class="required">*</span></label>
                    <div class="col-md-9">
                        <select class="form-control" name="status" required>
                            <option value=""><?php echo translate('select'); ?></option>
                            <option value="approved"><?php echo translate('approved'); ?></option>
                            <option value="rejected"><?php echo translate('rejected'); ?></option>
                            <option value="pending"><?php echo translate('pending'); ?></option>
                            <option value="initiator"><?php echo translate('initiator'); ?></option>
                        </select>
                        <span class="error"></span>
                    </div>
                </div>

                <!-- Remarks -->
                <div class="form-group mt-sm">
                    <label class="col-md-3 control-label"><?php echo translate('remarks'); ?> <span class="required">*</span></label>
                    <div class="col-md-9">
                        <input type="text" class="form-control" name="remarks" id="aremarks" required />
                        <span class="error"></span>
                    </div>
                </div>
            </div>

            <footer class="panel-footer">
                <div class="row">
                    <div class="col-md-12 text-right">
                        <button type="submit" class="btn btn-default" id="bankaddbtn" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
                            <i class="fas fa-plus-circle"></i> <?php echo translate('save'); ?>
                        </button>
                        <button class="btn btn-default modal-dismiss"><?php echo translate('cancel'); ?></button>
                    </div>
                </div>
            </footer>
        <?php echo form_close(); ?>
    </section>
</div>



<div class="drawer-overlay" id="drawerOverlay" onclick="closeFeedbackDrawer()"></div>

<div id="feedbackDrawer" class="drawer-modal">
	<section class="panel">
		<header class="panel-heading">
			<h4 class="panel-title"><?= translate('feedback') ?></h4>
			<button type="button" class="close pull-right" onclick="closeFeedbackDrawer()" style="margin-top: -25px;">&times;</button>
		</header>

		<div class="tabs-custom">
			<ul class="nav nav-tabs">
				<li class="<?= empty($validation_error) ? 'active' : '' ?>">
					<a href="#feedback_list" data-toggle="tab"><i class="fas fa-list-ul"></i> <?= translate('feedback_list') ?></a>
				</li>
				<li class="<?= !empty($validation_error) ? 'active' : '' ?>">
					<a href="#add_feedback" data-toggle="tab"><i class="far fa-edit"></i> <?= translate('add_feedback') ?></a>
				</li>
			</ul>

			<div class="tab-content">
				<div id="feedback_list" class="tab-pane <?= empty($validation_error) ? 'active' : '' ?>">
					<div class="panel-body">
						<table class="table table-bordered table-hover mb-none">
							<thead>
								<tr>
									<th><?= translate('submission_date') ?></th>
									<th><?= translate('submission_by') ?></th>
									<th><?= translate('feedback') ?></th>
									<th><?= translate('action') ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($kpi_feedback as $feedback): ?>
									<tr>
										<td><?= date('d-M-Y H:i', strtotime($feedback->created_at)) ?></td>
										<td><?= html_escape($feedback->submitted_by_name) ?></td>
										<td><?= html_escape($feedback->feedback) ?></td>
										<td><?php echo btn_delete('kpi/delete_feedback/' . $feedback->id); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>

					</div>
				</div>

				<div id="add_feedback" class="tab-pane <?= !empty($validation_error) ? 'active' : '' ?>">
					<?= form_open('kpi/save_feedback', ['class' => 'form-horizontal form-bordered frm-submit-feedback']) ?>
						<div class="panel-body">
						 <input type="hidden" name="staff_id" id="staff_id" value="<?php echo $row['staff_id']; ?>">
						 <input type="hidden" name="form_id" id="form_id" value="<?php echo $form_id; ?>">
						 
							<div class="form-group">
								<label class="col-md-3 control-label"><?= translate('feedback') ?> <span class="required">*</span></label>
								<div class="col-md-6">
									<textarea class="form-control" name="content" rows="4"><?= set_value('content') ?></textarea>
									<span class="error"><?= form_error('content') ?></span>
								</div>
							</div>
						</div>
						<footer class="panel-footer mt-lg">
							<div class="row">
								<div class="col-md-offset-5 col-md-3">
									<button type="submit" class="btn btn-default btn-block" name="submit" value="save">
										<i class="fas fa-plus-circle"></i> <?= translate('submit') ?>
									</button>
								</div>
							</div>
						</footer>
					<?= form_close() ?>
				</div>
			</div>
		</div>
	</section>
</div>


<script>
function getFeedbackModal(formId, staffId, feedback = '') {
	document.getElementById('feedbackDrawer').classList.add('open');
	document.getElementById('drawerOverlay').classList.add('active');
	// Optional: assign values to hidden fields if needed
}

function closeFeedbackDrawer() {
	document.getElementById('feedbackDrawer').classList.remove('open');
	document.getElementById('drawerOverlay').classList.remove('active');
}

</script>

<script type="text/javascript">
	var authenStatus = "<?=$staff['active']?>";
</script>



<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="ratingModal">
    <section class="panel">
        <header class="panel-heading">
            <h4 class="panel-title"> <?= translate('Assessment') ?></h4>
        </header>
        <?php echo form_open('kpi/save_rating', array('class' => 'frm-submit-rating')); ?>
        <div class="panel-body">
            <input type="hidden" name="form_id" id="rating_form_id" />
            <input type="hidden" name="staff_id" id="rating_staff_id" />
            <input type="hidden" name="role" id="rating_role" />

            <div class="form-group">
                <label class="control-label"><?= translate('Evaluation') ?> <span class="required">*</span></label>
             
				<select class="form-control" id="rating_value" name="rating" required>
					<option value=""><?= translate('select') ?></option>
					<?php for ($i = 10; $i <= 100; $i += 10): ?>
						<option value="<?=$i?>"><?=$i?>%</option>
					<?php endfor; ?>
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
</script>
