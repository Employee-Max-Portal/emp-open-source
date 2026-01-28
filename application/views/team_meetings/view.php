<header class="panel-heading">
	<h4 class="panel-title"><i class="fas fa-bars"></i> <?php echo translate('details'); ?></h4>
</header>
<div class="panel-body">

		<div class="col-md-12">
			<div class="panel panel-accordion">
				<div class="panel-heading">
					<h4 class="panel-title">
						<i class="fas fa-calendar-alt"></i> <?php echo $meeting['title']; ?>
						<span class="pull-right">
							<span class="label label-<?php echo ($meeting['meeting_type'] == 'management') ? 'warning' : 'success'; ?>">
								<?php echo ucfirst($meeting['meeting_type']); ?>
							</span>
						</span>
					</h4>
				</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-md-6">
							<table class="table table-bordered">
								<tr>
									<td><strong><?php echo translate('date'); ?>:</strong></td>
									<td><?php echo date('d M Y', strtotime($meeting['date'])); ?></td>
								</tr>
								<?php if (!empty($meeting['attachments'])): ?>
								<tr>
									<td><strong><?php echo translate('attachment'); ?>:</strong></td>
									<td> <a href="<?php echo base_url('team_meetings/download/' . $meeting['id']); ?>" 
									   class="btn btn-default btn-sm" target="_blank">
										<i class="fas fa-download"></i> <?php echo translate('download'); ?>
									</a></td>
								</tr>
								<?php endif; ?>
								<tr>
									<td><strong><?php echo translate('hosted_by'); ?>:</strong></td>
									<td>
									<?php 
										$getStaff = $this->db->select('name')
															 ->where('id', $meeting['meeting_host'])
															 ->get('staff')
															 ->row_array();
										echo $getStaff['name'] ?? '-';
									?>
								</td>
								</tr>
								<tr>
									<td><strong><?php echo translate('created_by'); ?>:</strong></td>
									<td><?php echo $meeting['created_by_name']; ?></td>
								</tr>
								<tr>
									<td><strong><?php echo translate('created_at'); ?>:</strong></td>
									<td><?php echo date('d M Y H:i', strtotime($meeting['created_at'])); ?></td>
								</tr>
							</table>
						</div>
						<div class="col-md-6">
							<?php if (!empty($meeting['participant_names'])): ?>
							<div class="panel panel-default">
								<div class="panel-heading">
									<h5 class="panel-title"><?php echo translate('participants'); ?></h5>
								</div>
								<div class="panel-body">
									<ul class="list-unstyled">
										<?php foreach ($meeting['participant_names'] as $participant): ?>
										<li><i class="fas fa-user"></i> <?php echo $participant; ?></li>
										<?php endforeach; ?>
									</ul>
								</div>
							</div>
							<?php endif; ?>
							
						</div>
					</div>
					
					<div class="row mt-md">
						<div class="col-md-12">
							<div class="panel panel-default">
								<div class="panel-heading">
									<h5 class="panel-title"><?php echo translate('meeting_minutes'); ?></h5>
								</div>
								<div class="panel-body">
									<?php echo $meeting['summary']; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>          
</div>

<footer class="panel-footer">
	<div class="row">
		<div class="col-md-12 text-right">
			<button class="btn btn-default modal-dismiss"><?php echo translate('close'); ?></button>
		</div>
	</div>
</footer>