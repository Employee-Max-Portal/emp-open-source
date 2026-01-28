<div class="row">
	<div class="col-md-12">
		<div class="panel">
			<header class="panel-heading">
				<h4 class="panel-title">Champion Badges</h4>
				<div class="panel-btn">
					<button class="btn btn-sm btn-danger" onclick="bulkRedeemBadges()">
						<i class="fas fa-exchange-alt"></i> Bulk Redeem Selected
					</button>
				</div>
			</header>

			<div class="panel-body">
				<table class="table table-bordered table-hover" id="badgesTable">
					<thead>
						<tr>
							<th><input type="checkbox" id="selectAll"></th>
							<th>Employee Name</th>
							<th>Milestone</th>
							<th>Reason</th>
							<th>Awarded By</th>
							<th>Awarded Date</th>
							<th>Status</th>
							<th>Redeemed Date</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($badges as $badge): ?>
							<tr>
								<td>
									<?php if ($badge->status == 'active'): ?>
										<input type="checkbox" class="badge-checkbox" value="<?= $badge->id ?>">
									<?php endif; ?>
								</td>
								<td><?= $badge->staff_name ?></td>
								<td><?= $badge->milestone_title ?></td>
								<td><?= $badge->badge_reason ?></td>
								<td><?= $badge->awarded_by_name ?></td>
								<td><?= date('d M Y, h:i A', strtotime($badge->awarded_at)) ?></td>
								<td>
									<?php if ($badge->status == 'active'): ?>
										<span class="badge badge-success">Active</span>
									<?php else: ?>
										<span class="badge badge-secondary">Redeemed</span>
									<?php endif; ?>
								</td>
								<td>
									<?= $badge->redeemed_at ? date('d M Y, h:i A', strtotime($badge->redeemed_at)) : '-' ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<script>
$('#selectAll').change(function() {
    $('.badge-checkbox').prop('checked', this.checked);
});

function bulkRedeemBadges() {
    var selectedBadges = [];
    $('.badge-checkbox:checked').each(function() {
        selectedBadges.push($(this).val());
    });
    
    if (selectedBadges.length === 0) {
        alert('Please select badges to redeem');
        return;
    }
    
    if (!confirm('Are you sure you want to redeem ' + selectedBadges.length + ' badge(s)?')) {
        return;
    }
    
    $.ajax({
        url: '<?= base_url('tracker/bulk_redeem_badges') ?>',
        type: 'POST',
        data: {
            badge_ids: selectedBadges,
            '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.message || 'Failed to redeem badges');
            }
        },
        error: function() {
            alert('An error occurred while redeeming badges');
        }
    });
}
</script>