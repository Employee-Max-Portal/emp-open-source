<section class="panel">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#log" data-toggle="tab">
					<i class="fas fa-history"></i> <?=translate('sop') . " " . translate('log')?>
				</a>
			</li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane box active mb-md" id="log">
				<table class="table table-bordered table-hover mb-none table-condensed table-export">
					<thead>
						<tr>
							<th><?=translate('sl')?></th>
							<th><?=translate('sop_id')?></th>
							<th><?=translate('action_by')?></th>
							<th><?=translate('action')?></th>
							<th><?=translate('title')?></th>
							<th><?=translate('verifier')?></th>
							<th><?=translate('expected_time')?></th>
							<th><?=translate('timestamp')?></th>
							<th><?=translate('details')?></th>
						</tr>
					</thead>
					<tbody>
						<?php
$count = 1;
foreach ($logs as $log):
	$data = json_decode($log['data_snapshot'], true);
	if (!is_array($data)) $data = [];

	// Handle title (create/delete vs update)
	if ($log['action'] == 'update' && isset($data['title']['new'])) {
		$title = $data['title']['new'];
	} elseif (isset($data['title'])) {
		$title = is_array($data['title']) ? $data['title']['new'] : $data['title'];
	} else {
		$title = '-';
	}

	// Handle verifier_role
	$verifier_names = [];
	if ($log['action'] == 'update' && isset($data['verifier_role']['new'])) {
		$verifier_ids = explode(',', $data['verifier_role']['new']);
	} elseif (isset($data['verifier_role'])) {
		$verifier_ids = explode(',', is_array($data['verifier_role']) ? $data['verifier_role']['new'] ?? '' : $data['verifier_role']);
	} else {
		$verifier_ids = [];
	}
	foreach ($verifier_ids as $rid) {
		$role = $this->db->get_where('roles', ['id' => $rid])->row();
		if ($role) $verifier_names[] = $role->name;
	}

	// Expected Time
	if ($log['action'] == 'update' && isset($data['expected_time']['new'])) {
		$expected_time = $data['expected_time']['new'];
	} elseif (isset($data['expected_time'])) {
		$expected_time = is_array($data['expected_time']) ? $data['expected_time']['new'] ?? '-' : $data['expected_time'];
	} else {
		$expected_time = '-';
	}

	// Staff Name
	$staff = $this->db->get_where('staff', ['id' => $log['staff_id']])->row();
	$staff_name = $staff ? $staff->name : 'Unknown';
?>
<tr>
	<td><?= $count++; ?></td>
	<td><?= $log['sop_id'] ?></td>
	<td><?= html_escape($staff_name); ?></td>
	<td>
		<span class="label label-<?= $log['action'] == 'create' ? 'success' : ($log['action'] == 'update' ? 'warning' : 'danger') ?>">
			<?= ucfirst($log['action']) ?>
		</span>
	</td>
	<td><?= html_escape($title); ?></td>
	<td><?= implode(', ', $verifier_names); ?></td>
	<td><?= $expected_time !== '-' ? $expected_time . ' ' . translate('hours') : '-' ?></td>
	<td><?= date('d M Y h:i A', strtotime($log['created_at'])) ?></td>
	<td class="min-w-c">
		<a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick='viewLogDetails(<?= $log["id"] ?>)' title="<?=translate('view')?>">
			<i class="fas fa-eye"></i>
		</a>
	</td>
</tr>
<?php endforeach; ?>


					</tbody>
				</table>
			</div>
		</div>
	</div>
</section>

<!-- Modal for Details -->
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="log_modal">
	<section class="panel" id='log_detail_view'></section>
</div>

<script type="text/javascript">
	function viewLogDetails(id) {
	    $.ajax({
	        url: base_url + 'sop/getLogDetail',
	        type: 'POST',
	        data: {'id': id},
	        dataType: "html",
	        success: function (data) {
	            $('#log_detail_view').html(data);
	            mfp_modal('#log_modal');
	        }
	    });
	}
</script>
