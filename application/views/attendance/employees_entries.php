<?php $widget = in_array(loggedin_role_id(), [1, 2, 3, 5]); ?>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<?php echo form_open($this->uri->uri_string()); ?>
			<header class="panel-heading">
				<h4 class="panel-title"><?=translate('select_ground')?></h4>
			</header>
			<div class="panel-body">
				<div class="row mb-sm">
				<?php if (in_array(loggedin_role_id(), [1, 2, 3, 5])): ?>
					<div class="col-md-4 mb-sm">
						<div class="form-group">
							<label class="control-label"><?=translate('branch')?> <span class="required">*</span></label>
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
						<span class="error"><?=form_error('branch_id')?></span>
					</div>
				<?php endif; ?>
					
					<div class="col-md-4 mb-sm">
						<div class="form-group <?php if (form_error('date')) echo 'has-error'; ?>">
							<label class="control-label">
								<?=translate('date')?> <span class="required">*</span>
							</label>
							<div class="input-group">
							    <input type="text" class="form-control" required  name="date" id='attDate' value="<?=set_value('date', date("Y-m-d"))?>" />
							    <span class="input-group-addon"><i class="fas fa-calendar"></i></span>
							</div>
							<span class="error"><?=form_error('date')?></span>
						</div>
					</div>
				</div>
			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-offset-10 col-md-2">
						<button type="submit" name="search" value="1" class="btn btn btn-default btn-block">
							<i class="fas fa-filter"></i> <?=translate('filter')?>
						</button>
					</div>
				</div>
			</footer>
			<?php echo form_close();?>
		</section>
		
		<?php if(isset($attendencelist)): ?>
			<section class="panel appear-animation" data-appear-animation="<?=$global_config['animations'] ?>" data-appear-animation-delay="100">
				<?php
				echo form_open($this->uri->uri_string());
				$data = array('branch_id'=> $branch_id, 'date'=> $date);
				echo form_hidden($data);
				?>
				<header class="panel-heading">
					<h4 class="panel-title"><i class="fas fa-users"></i> <?=translate('employees_list')?></h4>
				</header>
				<div class="panel-body">
					<div class="row">
						<div class="col-md-offset-9 col-md-3">
							<div class="form-group mb-sm">
								<label class="control-label"><?=translate('select_for_everyone')?> <span class="required">*</span></label>
								<?php
									$array = array(
										"" => translate('not_selected'),
										"P" => translate('present'),
										"A" => translate('absent'),
										"L" => translate('late'),
									);
									echo form_dropdown("mark_all_everyone", $array, set_value('mark_all_everyone'), "class='form-control' 
									onchange='selAtten_all(this.value)' data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
								?>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<div class="table-responsive mb-sm mt-xs">
								<table class="table table-bordered table-hover table-condensed mb-none">
									<thead>
										<tr>
											<th width="40">#</th>
											<th width="80"><?=translate('photo')?></th>
											<th><?=translate('employee_id')?></th>
											<th><?=translate('name')?></th>
											<th><?=translate('ip_address')?></th>
											<th><?=translate('check-in time')?></th>
											<th width="200"><?=translate('status')?></th>
											<th><?=translate('remarks')?></th>
										</tr>
									</thead>
									<tbody>
									<?php
									$count = 1;
									if(count($attendencelist)) {
										foreach ($attendencelist as $key => $row):
										
									?>
										<tr>
											<input type="hidden" name="attendance[<?=$key?>][attendance_id]" value="<?=$row['atten_id']?>">
											<input type="hidden" name="attendance[<?=$key?>][staff_id]" value="<?=$row['id']?>">
											<td><?php echo $count++; ?></td>
											<td class="center"><img class="rounded" src="<?php echo get_image_url('staff', $row['photo']); ?>" width="40" height="40" /></td>
											<td><?php echo $row['staff_code']; ?></td>
											<td><?php echo $row['name']; ?></td>
											<td><?php echo $row['ip_address']; ?></td>
											<td>
												<?php
												$datetime = new DateTime($row['in_time']);
												echo $datetime->format('h.iA \a\t d/m/Y');
												?>
											</td>

											<td>
												<div class="radio-custom radio-success radio-inline mt-xs">
													<input type="radio" value="P" <?=($row['att_status'] == 'P' ? 'checked' : '')?> name="attendance[<?=$key?>][status]" id="pstatus_<?=$key?>">
													<label for="pstatus_<?=$key?>"><?=translate('present')?></label>
												</div>
												<!-- <div class="radio-custom radio-danger radio-inline mt-xs">
													<input type="radio" value="A" <?=($row['att_status'] == 'A' ? 'checked' : '')?> name="attendance[<?=$key?>][status]" id="astatus_<?=$key?>">
													<label for="astatus_<?=$key?>"><?=translate('absent')?></label>
												</div> -->
												<div class="radio-custom radio-inline mt-xs">
													<input type="radio" value="L" <?=($row['att_status'] == 'L' ? 'checked' : '')?> name="attendance[<?=$key?>][status]" id="lstatus_<?=$key?>">
													<label for="lstatus_<?=$key?>"><?=translate('late')?></label>
												</div>
												
											</td>
											<td><input class="form-control" name="attendance[<?=$key?>][remark]" type="text" placeholder="<?=translate('remarks')?>" value="<?=$row['att_remark']?>" ></td>
										</tr>
									<?php
										endforeach;
									} else {
										echo '<tr><td colspan="8"><h5 class="text-danger text-center">' . translate('no_information_available') . '</td></tr>';
									}
									?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
				<div class="panel-footer">
					<div class="row">
						<div class="col-md-offset-10 col-md-2">
							<button type="submit" class="btn btn-default btn-block" name="save" value="1">
								<i class="fas fa-plus-circle"></i> <?=translate('save')?>
							</button>
						</div>
					</div>
				</div>
			<?php echo form_close(); ?>
			</section>
		<?php endif; ?>
	</div>
</div>

<script type="text/javascript">
	var dayOfWeekDisabled = "<?php echo $getWeekends ?>";
	$(document).ready(function () {
		$("#attDate").datepicker({
		    orientation: 'bottom',
		    autoclose: true,
		    format: 'yyyy-mm-dd',
		    daysOfWeekDisabled: dayOfWeekDisabled,
		});   
    });
    
	$('select#branchID').change(function() {
		var branchID = $(this).val();
		$.ajax({
			url: base_url + "attendance/getWeekendsHolidays",
			type: 'POST',
			dataType: "json",
			data: {
				branch_id: branchID,
			},
			success: function (data) {
				$('#attDate').val("");
				$('#attDate').datepicker('setDaysOfWeekDisabled', data.getWeekends);
				$('#attDate').datepicker('setDatesDisabled', JSON.parse(data.getHolidays));
			}
		});
	});
</script>