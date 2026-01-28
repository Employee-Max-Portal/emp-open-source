<div class="row appear-animation" data-appear-animation="<?=$global_config['animations'] ?>">
	<div class="col-md-12">
		<?php echo form_open_multipart($this->uri->uri_string()); ?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4><i class="fas fa-user-edit"></i> <?=translate('assessment_details')?></h4>
				</div>
				
				<div class="panel-body">
					<input type="hidden" name="staff_id" id="staff_id" value="<?php echo $staff['id']; ?>">

					<p class="text-info"><strong><?=translate('please make a detailed assessment covering the entire probation period and not any short specific one time observation')?></strong></p>

					<div class="headers-line mt-md">
						<i class="fas fa-tasks"></i> <?=translate('Work aptitude & Attitude')?>
					</div>

						<?php
						$ratings = ['Excellent', 'Good', 'Satisfactory', 'Poor'];
						$criteria = [
							'job_performance' => 'Job Performance (incl. technical competence)',
							'analytical' => 'Analytical / Problem Solving',
							'attitude' => 'Attitude to Work',
							'communication' => 'Ability to Communicate & Get Along',
							'pressure' => 'Ability to Work Under Pressure',
							'attendance' => 'Attendance / Punctuality',
							'qr_values' => 'Core Values',
							'initiative' => 'Initiative & Creativity',
							'focus' => 'Focus on Results',
							'people' => 'People Management',
							'decision' => 'Decision Making',
						];

						function ordinal($number) {
							$ends = ['th','st','nd','rd','th','th','th','th','th','th'];
							if (($number % 100) >= 11 && ($number % 100) <= 13)
								return $number . 'th';
							else
								return $number . $ends[$number % 10];
						}
						?>

					<div class="table-responsive">
						<table class="table table-bordered">
							<thead>
								<tr>
									<th>Criteria</th>
									<?php for ($m = 1; $m <= 6; $m++): ?>
										<th><?= ordinal($m) ?> Month</th>
									<?php endfor; ?>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($criteria as $field => $label): ?>
									<tr>
										<td><?= $label ?></td>
										<?php for ($m = 1; $m <= 6; $m++): ?>
											<td>
												<select name="<?= $field . '_' . $m ?>" class="form-control" <?= loggedin_role_id() == 10 ? 'disabled' : '' ?>>
													<option value="">Select</option>
													<?php foreach ($ratings as $r): ?>
														<?php
															$selected = '';
															if (isset($staff['probation_assessment'][$m][$field]) && $staff['probation_assessment'][$m][$field] == $r) {
																$selected = 'selected';
															}
														?>
														<option value="<?= $r ?>" <?= $selected ?>><?= $r ?></option>
													<?php endforeach; ?>
												</select>
											</td>
										<?php endfor; ?>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>

					<div class="headers-line">
						<i class="fas fa-comments"></i> <?=translate('work Aptitude & attitude')?>
					</div>

					<?php
					$month_labels = ['First', 'Second', 'Third', 'Fourth', 'Fifth', 'Sixth'];
					for ($m = 1; $m <= 6; $m++): 
						$month_data = $staff['probation_assessment'][$m] ?? [];
						$remark = $month_data['remarks'] ?? '';
						$meeting_done = $month_data['meeting_done'] ?? 0;
						$meeting_date = $month_data['meeting_date'] ?? '';
						
						// For role 10, only show months with remarks data
						if (loggedin_role_id() == 10 && empty(trim($remark))) {
							continue;
						}
					?>
					<div class="headers-line mt-md mb-sm">
						<i class="fas fa-calendar-alt"></i> <?= $month_labels[$m - 1] ?> Month
					</div>

					<div class="form-group mb-3">
						<label class="control-label"><?= translate('Work Aptitude & Attitude') ?> (<?= translate('Contd') ?>)</label>
						<textarea class="form-control" name="remarks_<?= $m ?>" rows="3" <?= loggedin_role_id() == 10 ? 'readonly' : '' ?>><?= set_value('remarks_' . $m, $remark) ?></textarea>
					</div>

					<div class="row">
						<div class="col-md-6">
							<div class="checkbox">
								<label>
									<input type="checkbox" name="meeting_done_<?= $m ?>" value="1" <?= $meeting_done ? 'checked' : '' ?> <?= loggedin_role_id() == 10 ? 'disabled' : '' ?>>
									<?= translate('Face-to-face meeting completed?') ?>
								</label>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label"><?= translate('Face-to-face meeting conducted on') ?></label>
								<input type="date" class="form-control" name="meeting_date_<?= $m ?>" value="<?= set_value('meeting_date_' . $m, $meeting_date) ?>" <?= loggedin_role_id() == 10 ? 'readonly' : '' ?> />
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label class="control-label"><?= translate('Status: ') ?></label>
								<div class="d-flex align-items-center gap-3 mt-1">
									<label class="mb-0">
										<input type="radio" name="advisor_review_<?= $m ?>" value="1"
											<?= (isset($month_data['advisor_review']) && $month_data['advisor_review'] == '1') ? 'checked' : 'checked' ?> <?= loggedin_role_id() != 10 ? 'disabled' : '' ?>>
										<?= translate('Under Review') ?>
									</label>
									<label class="mb-0">
										<input type="radio" name="advisor_review_<?= $m ?>" value="0"
											<?= (isset($month_data['advisor_review']) && $month_data['advisor_review'] == '0') ? 'checked' : '' ?> <?= loggedin_role_id() != 10 ? 'disabled' : '' ?>>
										<?= translate('Approved') ?>
									</label>
								</div>
							</div>
						</div>
						<div class="col-md-9">
							<div class="form-group mb-3">
								<label class="control-label"><?= translate('Advisor Comment') ?></label>
								<textarea class="form-control" name="advisor_comment_<?= $m ?>" rows="3" <?= loggedin_role_id() != 10 ? 'readonly' : '' ?>><?= set_value('advisor_comment_' . $m, $month_data['advisor_comment'] ?? '') ?></textarea>
							</div>
						</div>
					</div>

					<hr>
					<?php endfor; ?>
				</div>

				<div class="panel-footer">
					<div class="row">
						<div class="col-md-offset-9 col-md-3">
							<button type="submit" name="submit" value="update" class="btn btn-default btn-block"><?=translate('update')?></button>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<style>
	.rating-excellent { background-color: #d4edda !important; color: #155724 !important; }
	.rating-good { background-color: #cce7ff !important; color: #004085 !important; }
	.rating-satisfactory { background-color: #fff3cd !important; color: #856404 !important; }
	.rating-poor { background-color: #f8d7da !important; color: #721c24 !important; }
	.textarea-filled { background-color: #f8f9fa !important; border-color: #007bff !important; }
	.checkbox-checked { background-color: #e8f5e8 !important; }
	.date-filled { background-color: #f0f8ff !important; border-color: #007bff !important; }
</style>

<script type="text/javascript">
	var authenStatus = "<?=$staff['active']?>";
	
	// Apply color coding to selected dropdowns and textareas
	$(document).ready(function() {
		$('select[name*="_"]').each(function() {
			applyRatingColor(this);
		}).change(function() {
			applyRatingColor(this);
		});
		
		$('textarea[name*="remarks_"], textarea[name*="advisor_comment_"]').each(function() {
			applyTextareaColor(this);
		}).on('input', function() {
			applyTextareaColor(this);
		});
		
		$('input[type="checkbox"]').each(function() {
			applyCheckboxColor(this);
		}).change(function() {
			applyCheckboxColor(this);
		});
		
		$('input[type="date"]').each(function() {
			applyDateColor(this);
		}).change(function() {
			applyDateColor(this);
		});
	});
	
	function applyRatingColor(select) {
		var value = $(select).val().toLowerCase();
		$(select).removeClass('rating-excellent rating-good rating-satisfactory rating-poor');
		
		if (value === 'excellent') {
			$(select).addClass('rating-excellent');
		} else if (value === 'good') {
			$(select).addClass('rating-good');
		} else if (value === 'satisfactory') {
			$(select).addClass('rating-satisfactory');
		} else if (value === 'poor') {
			$(select).addClass('rating-poor');
		}
	}
	
	function applyTextareaColor(textarea) {
		var value = $(textarea).val().trim();
		$(textarea).removeClass('textarea-filled');
		
		if (value.length > 0) {
			$(textarea).addClass('textarea-filled');
		}
	}
	
	function applyCheckboxColor(checkbox) {
		var $parent = $(checkbox).closest('.checkbox');
		$parent.removeClass('checkbox-checked');
		
		if ($(checkbox).is(':checked')) {
			$parent.addClass('checkbox-checked');
		}
	}
	
	function applyDateColor(dateInput) {
		var value = $(dateInput).val();
		$(dateInput).removeClass('date-filled');
		
		if (value) {
			$(dateInput).addClass('date-filled');
		}
	}
</script>
