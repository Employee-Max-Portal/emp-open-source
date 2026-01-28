<?php
if($break_control==1){
	//// Already Active, need to do to inactive
	$div_style_active = "";
	$div_style_inactive = "style=display:none";
	
}
else{
	//// No active breaks, need to create one
	$div_style_active = "style=display:none";
	$div_style_inactive = "";

}
?>
		<div <?php echo $div_style_active; ?>>
				<?php echo form_open($this->uri->uri_string()); ?>
				<div class="panel-body">
					<input type="hidden" name="date_time" value="<?php echo date('Y-m-d H:i:s'); ?>" >
					<input type="hidden" name="user_id" value="<?php echo get_loggedin_user_id();?>">
					<input type="hidden" name="break_control" value="<?php echo $break_control;?>">
					<input type="hidden" name="break_id" value="<?php echo get_break_id();?>">
					<input type="hidden" name="break_name" value="<?php echo get_break_name();?>">
					<input type="hidden" name="break_starttime" value="<?php echo get_break_starttime();?>">
					<input type="hidden" name="history_id" value="<?php echo get_history_id();?>">
				
					<table class="table table-bordered table-hover table-condensed table_default" cellspacing="0" width="100%">
						<tr>
							<td><strong style="color: #3366ff;">Active Break:</strong></td>
							<td><?php echo get_break_name(); ?></td>
						</tr>
						<tr>
							<td><strong style="color: #00cc66;">Started On:</strong></td>
							<td>
								<?php
								$break_starttime = get_break_starttime();
								$datetime = new DateTime($break_starttime);
								$formatted_date = $datetime->format('F j, Y');
								$formatted_time = $datetime->format('h:i A');
								$formatted_datetime = $formatted_date . " " . $formatted_time;
								echo $formatted_datetime;

								// Calculate time difference
								$now = new DateTime();
								$interval = $datetime->diff($now);
								$minutes_diff = ($interval->h * 60) + $interval->i;

								// If more than 30 mins, show remarks box
								if ($minutes_diff > 30) {
									echo '<div class="form-group mt-md">';
									echo '<label for="remarks"><strong style="color:red;">Remarks (Break exceeded 30 minutes) *</strong></label>';
									echo '<textarea name="remarks" id="remarks" class="form-control" rows="3" required placeholder="Explain reason..."></textarea>';
									echo '</div>';
								}
								?>
							</td>

						</tr>
					</table>
				</div>
				
				<div class="panel-footer">
					<div class="row">
						<div class="col-md-12">
							<button type="submit" name="submit" value="inactivate_break" class="btn btn-default pull-right" 
										onclick="setTimeout(() => {this.disabled=true; this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Processing';}, 100);">
									<i class="fas fa-minus-circle"></i> <?=translate('inactive_break')?>
								</button>
						</div>	
					</div>
				</div>
			<?php echo form_close(); ?>
			
		</div>
		
		<div <?php echo $div_style_inactive; ?>>
				<?php echo form_open($this->uri->uri_string()); ?>
				<div class="panel-body">
					
					<input type="hidden" name="date_time" value="<?php echo date('Y-m-d H:i:s'); ?>" >
					<input type="hidden" name="user_id" value="<?php echo get_loggedin_user_id();?>">
					<input type="hidden" name="break_control" value="<?php echo $break_control;?>">
				
					<div class="form-group mb-md">
						<label class="control-label"><?php echo translate('select'); ?> <?php echo translate('break'); ?> <span class="required">*</span></label>
						<select class='form-control' name="break_id" id="break_id" data-plugin-selectTwo data-width='100%' required >
							<option value=''><?php echo translate('select'); ?><?php echo translate('....'); ?></option>
							<?php
							
							foreach ($pause_list as $row) {
								$id = $row['id'];
								$name = $row['name'];
								echo "<option value='$id'>$name</option>";
							}
							?>
						</select>
				
					</div>
					
				</div>
				<div class="panel-footer">
					<div class="row">
						<div class="col-md-12">
							<button type="submit" name="submit" value="activate_break" class="btn btn-default pull-right" 
										onclick="setTimeout(() => {this.disabled=true; this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Processing';}, 100);">
									<i class="fas fa-plus-circle"></i> <?=translate('take_break')?>
								</button>
						</div>	
					</div>
				</div>
			<?php echo form_close(); ?>
			
		</div>
	