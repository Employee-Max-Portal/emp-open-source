<section class="panel">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">	
			
			<li class="active">
				<a href="#list" data-toggle="tab"><i class="fas fa-history"></i> <?php echo translate('break') . " " . translate('History'); ?></a>
			</li>
			
			<li >
				<a href="<?php echo base_url('employee/staff_break'); ?>"><i class="fas fa-list-ul"></i> <?php echo translate('break') . " " . translate('Management'); ?></a>
			</li>


		</ul>
		<div class="tab-content">
		
		
		
			<div id="list" class="tab-pane <?php echo (!isset($validation_error) ? 'active' : ''); ?>">
				<table class="table table-bordered table-hover table-condensed table_default" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th><?php echo translate('sl'); ?></th>
							<th><?php echo translate('User'); ?></th>
							<th><?php echo translate('Break Name'); ?></th>
							<th><?php echo translate('Started Time'); ?></th>
							<th><?php echo translate('Ended Time'); ?></th>
							<th><?php echo translate('Duration (H:M:S)'); ?></th>
							<th><?php echo translate('Status'); ?></th>

						</tr>
					</thead>
			<tbody>
                        <?php
                        $count = 1;

                        if (isset($break_history)) {
                            foreach ($break_history as $row):
                        ?>
                        <tr class="<?php echo $row_class; ?>">
                            <td><?php echo $count++; ?></td>
                            <td><?php echo $row['staff_name']; ?></td>
                            <td><?php echo $row['pause_name']; ?></td>
							<td><?php echo $row['start_datetime']; ?></td>
							<td><?php echo $row['end_datetime']; ?></td>
							<td><?php 
								$st_time = $row['start_datetime'];
								$et_time = $row['end_datetime'];

								// Check if $et_time is empty, then set it to the current datetime
								if(empty($et_time)){
									$et_time = date('Y-m-d H:i:s');
								}

								// Convert strings to DateTime objects
								$start_datetime = new DateTime($st_time);
								$end_datetime = new DateTime($et_time);

								// Calculate the difference
								$interval = $start_datetime->diff($end_datetime);

								// Format the difference as hours:minutes:seconds
								$hours = str_pad($interval->h, 2, '0', STR_PAD_LEFT);
								$minutes = str_pad($interval->i, 2, '0', STR_PAD_LEFT);
								$seconds = str_pad($interval->s, 2, '0', STR_PAD_LEFT);

								$formatted_difference = "$hours:$minutes:$seconds";

								echo $formatted_difference;
								?>

							</td>
							<td><?php 
							$status = $row['status'];
							
							if($status==1){
								echo "<b style='color:red;'>On Break</b>";
							}
							else{
								echo "Completed";
							}
							?>
							</td>
							
							
						</tr>
						<?php endforeach; }?>
					</tbody>
				</table>
			</div>
		
			
			

			

		</div>
	</div>
	
</section>
