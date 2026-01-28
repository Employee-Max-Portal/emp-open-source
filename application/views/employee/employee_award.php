<section class="panel">

	<header class="panel-heading d-flex justify-content-between align-items-center">
				<h4 class="panel-title"><i class="fas fa-trophy"></i> <?= translate('employee_scores') ?></h4>
				<?php if (get_permission('employee_award', 'is_add')): ?>
				<div class="panel-btn">
					<button class="btn btn-default mb-md" data-toggle="modal" data-target="#generateScoreModal">
						<i class="fas fa-sync-alt"></i> <?=translate('generate_scores')?>
					</button>
				</div>

				<?php endif; ?>
			</header>
    <div class="panel-body">
	
        <?=form_open(base_url('employee/employee_award'), ['method' => 'get', 'class' => 'form-inline mb-md'])?>
            <div class="form-group">
                <label class="control-label"><?=translate('select_month')?>: </label>
                <input type="month" name="month" class="form-control" value="<?=set_value('month', $this->input->get('month') ?: date('Y-m'))?>" onchange="this.form.submit();" />
            </div>
        <?=form_close(); ?>

        
<style>
.winner-card .winner-trophy {
    animation: bounce 2s infinite;
}
@media (min-width: 992px) {
    .modal-lg {
        width: 50%;
    }
}
.btn-danger-light {
	background-color: #fce4e4 !important;
	border-color: #f5c2c2 !important;
	color: #c82333 !important;
}

</style>
 <!-- Winner Announcement -->
        <?php if (!empty($scores) && !$is_role_4): ?>
            <div class="winner-card mb-4">
                <div class="card border-warning">
                    <div class="card-body text-center bg-light">
                        <div class="winner-trophy mb-3">
                            <i class="fas fa-trophy fa-3x text-warning"></i>
                        </div>
                        <h3 class="text-primary mb-2"><?=get_type_name_by_id('staff', $scores[0]['staff_id'])?></h3>
                        <h5 class="text-muted mb-2">Employee of the <?=date('F Y', strtotime($month . '-01'))?></h5>
                        <div class="score-display">
                            <span class="badge badge-success badge-lg" style="font-size: 1.2em; padding: 10px 20px;">
                                Score: <?=round($scores[0]['final_score'], 2)?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Score Table -->
		<table class="table table-bordered table-hover table-condensed table-export">
			<thead>
				<tr>
					<th><?=translate('sl')?></th>
					<th><?=translate('employee')?></th>
					<th><?= translate('task_completion') . ' (' . $global_config['completion_ratio'] . '%)' ?></th>
					<th><?= translate('quality of work') . ' (' . $global_config['quality_score'] . '%)' ?></th>
					<th><?= translate('work summary') . ' (' . $global_config['work_summary'] . '%)' ?></th>
					<th><?= translate('attendance') . ' (' . $global_config['attendance_score'] . '%)' ?></th>
					<th><?=translate('total warnings')?></th>
					<th><?= translate('warnings penalty') . ' (' . $global_config['warning_penalty'] . '%)' ?></th>
					<?php if (!$is_role_4): ?>
					<th><?=translate('Discipline')?></th>
					<?php endif; ?>
					<th><?=translate('score')?></th>
				</tr>
			</thead>
			<tbody>
				<?php $i=1; foreach($scores as $row): ?>
				<tr>
					<td><?=$i++?></td>
					<td><?=get_type_name_by_id('staff', $row['staff_id'])?></td>
					<td><?=$row['completion_rate']?>%</td>
					<td><?=$row['quality_score']?>%</td>
					<td><?=$row['work_summary_score']?>%</td>
					<td><?=$row['attendance_score']?>%</td>
					<td><?=$row['warning_count']?></td>
					<td><?=$row['warning_penalty']?>%</td>
					<?php if (!$is_role_4): ?>
					<td>
					<?php
						$role = loggedin_role_id();
						$adjustmentValue = floatval($row['adjustment_value']);
						$btnClass = ($adjustmentValue == 0) ? 'btn-danger-light' : 'btn-default';
						$can_adjust = in_array($role, [3, 5, 8]) && ($role != 8 || $logged_user_dept == $row['department']);
					?>
					<?php if ($can_adjust): ?>
						<button class="btn <?=$btnClass?> btn-circle icon" data-toggle="modal" data-target="#adjustModal<?=$row['id']?>">
							<?= $row['adjustment_value'] ?>%
						</button>
					<?php else: ?>
						<button class="btn <?=$btnClass?> btn-circle icon" disabled title="<?= in_array($role, [3, 5, 8]) ? 'Different department' : 'Not authorized' ?>">
							<?= $row['adjustment_value'] ?>%
						</button>
					<?php endif; ?>
					<?php if (!empty($row['adjusted_by_name'])): ?>
						<br><small class="text-muted">by <?= $row['adjusted_by_name'] ?></small>
					<?php endif; ?>
					</td>
					<?php endif; ?>


					<td><strong><?=round($row['final_score'], 2)?></strong></td>
				</tr>

				<!-- Bootstrap Modal -->
				<div class="modal fade" id="adjustModal<?=$row['id']?>" tabindex="-1" role="dialog" aria-labelledby="adjustModalLabel<?=$row['id']?>" aria-hidden="true">
					<div class="modal-dialog modal-lg" role="document">
						<section class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title" id="adjustModalLabel<?=$row['id']?>">
									<i class="fas fa-sliders-h"></i> <?=get_type_name_by_id('staff', $row['staff_id'])?> - <?=translate('adjustment')?>
								</h4>
							</div>
							<?=form_open(base_url('employee/update_adjustment'), ['class' => 'form-horizontal', 'method' => 'post'])?>
							<div class="panel-body">
								<input type="hidden" name="score_id" value="<?=$row['id']?>" />

								<div class="form-group row">
									<label class="col-md-4 control-label">Adjustment Value (out of 10) <span class="required">*</span></label>
									<div class="col-md-8">
										<input type="number" step="0.01" name="adjustment_value" value="<?=$row['adjustment_value']?>" class="form-control adjustment-input"  required min="0" max="10" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 control-label"><?=translate('remarks')?></label>
									<div class="col-md-8">
										<textarea name="adjustment_remarks" class="form-control" rows="3"><?=$row['adjustment_remarks']?></textarea>
									</div>
								</div>
							</div>

							<footer class="panel-footer text-right">
								<button type="submit" class="btn btn-success">
									<i class="fas fa-check-circle"></i> <?=translate('save')?>
								</button>
								<button type="button" class="btn btn-default" data-dismiss="modal"><?=translate('cancel')?></button>
							</footer>
							<?=form_close()?>
						</section>
					</div>
				</div>
				<?php endforeach; ?>
			</tbody>
		</table>
    </div>
</section>

<!-- Modal -->
<div class="modal fade" id="generateScoreModal" tabindex="-1" role="dialog" aria-labelledby="generateScoreModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form action="<?=base_url('employee/generate_employee_award')?>" method="post">
	 <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />
      <div class="panel-body">
        <div class="modal-header">
          <h5 class="modal-title" id="generateScoreModalLabel">Select Month</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
			<label class="control-label"><?php echo translate('month'); ?> <span class="required">*</span></label>
			<input type="text" class="form-control monthyear" autocomplete="off" name="month_year" value="<?php echo set_value('month_year', date("Y-m")); ?>" required/>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Generate</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>


<script>
document.querySelectorAll('.adjustment-input').forEach(function(input) {
    input.addEventListener('input', function() {
        let val = parseFloat(this.value);
        if (val < 0) this.value = 0;
        if (val > 10) this.value = 10;
    });
});
</script>
