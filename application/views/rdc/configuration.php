<section class="panel appear-animation" data-appear-animation="<?php echo $global_config['animations']; ?>" data-appear-animation-delay="100">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<li <?=(empty($this->session->flashdata('active')) ? 'class="active"' : '');?>>
				<a href="#reminder" data-toggle="tab">
					<i class="fas fa-bell"></i>
				   <span class="hidden-xs"> <?=translate('reminder_settings')?></span>
				</a>
			</li>
			<li <?=($this->session->flashdata('active') == 2 ? 'class="active"' : '');?>>
				<a href="#escalation" data-toggle="tab">
				  <i class="fas fa-exclamation-triangle"></i>
				   <span class="hidden-xs"> <?=translate('escalation_settings')?></span>
				</a>
			
			</li>
		</ul>

		<div class="tab-content">
			<div class="tab-pane box <?=(empty($this->session->flashdata('active')) ? 'active' : '');?>" id="reminder">
				<?php echo form_open($this->uri->uri_string(), array( 'class' 	=> 'validate form-horizontal form-bordered' )); ?>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('pre_reminder (in minutes)')?></label>
					<div class="col-md-6">
						<input type="number" class="form-control" name="pre_reminder" value="<?=set_value('pre_reminder', $global_config['pre_reminder'])?>" placeholder="<?=translate('Time before event in minutes')?>" />
					</div>
				</div>
				
				<footer class="panel-footer mt-lg">
					<div class="row">
						<div class="col-md-2 col-sm-offset-3">
							<button type="submit" class="btn btn btn-default btn-block" name="submit" value="reminder">
								<i class="fas fa-plus-circle"></i> <?=translate('save');?>
							</button>
						</div>
					</div>
				</footer>
				<?php echo form_close(); ?>
			</div>

		<div class="tab-pane box <?= ($this->session->flashdata('active') == 2 ? 'active' : ''); ?>" id="escalation">
			<?= form_open($this->uri->uri_string(), ['class' => 'needs-validation form-horizontal form-bordered', 'novalidate' => '']); ?>
			
				<?php
				$levels = json_decode($global_config['escalation_levels'] ?? '[]', true);
				$array = $this->app_lib->getSelectList('roles');

				// Remove unwanted roles
				$keysToUnset = [1, 2, 4, 9];
				foreach ($keysToUnset as $key) {
					unset($array[$key]);
				}

				// Fallback if no levels stored
				if (empty($levels)) {
					$levels = [
						['level' => 1, 'delay_hours' => '', 'role_id' => '', 'message' => '', 'channel' => 'in-app'],
					];
				}

				foreach ($levels as $i => $level): ?>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= "Level " . ($i + 1) . " - Delay (in hours)" ?></label>
						<div class="col-md-2">
							<input type="number" class="form-control" name="delay_hours[]" placeholder="e.g. 24" required
								value="<?= html_escape($level['delay_hours']) ?>" />
						</div>

						<label class="col-md-1 control-label">To</label>
						<div class="col-md-3">
							<?= form_dropdown(
								'escalate_to_role[]',
								$array,
								$level['role_id'],
								"class='form-control' required"
							); ?>
						</div>
					</div>

				<?php endforeach; ?>

				<hr>


			<!-- Submit Button -->
			<footer class="panel-footer mt-lg">
				<div class="row">
					<div class="col-md-2 col-sm-offset-3">
						<button type="submit" class="btn btn-default btn-block" name="submit" value="escalation">
							<i class="fas fa-exclamation-circle"></i> <?= translate('save'); ?>
						</button>
					</div>
				</div>
			</footer>

			<?= form_close(); ?>
		</div>

		</div>
	</div>
</section>