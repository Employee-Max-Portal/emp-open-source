<?php
$this->db->select('fund_requisition.*,fund_category.name as fund_name,staff.name as staff_name,staff.staff_id as staffid',);
$this->db->from('fund_requisition');
$this->db->join('staff', 'staff.id = fund_requisition.staff_id', 'left');
$this->db->join('fund_category', 'fund_category.id = fund_requisition.category_id', 'left');
$this->db->where('fund_requisition.id', $fund_id);
$row = $this->db->get()->row_array();
$ledger_entries = !empty($row['ledger_entries']) ? json_decode($row['ledger_entries'], true) : [];

?>
<header class="panel-heading">
	<h4 class="panel-title"><i class="fas fa-list-ol"></i> <?=translate('review')?></h4>
</header>
<div class="panel-body">
	<div class="table-responsive">
		<table class="table borderless mb-none">
			<tbody>
				<tr>
					<th width="120"><?=translate('reviewed_by')?> :</th>
					<td>
						<?php
                            if(!empty($row['issued_by'])){
                                echo html_escape(get_type_name_by_id('staff', $row['issued_by']));
                            }else{
                                echo translate('unreviewed');
                            }
						?>
					</td>
				</tr>
				<?php if (!empty($row['paid_by'])) { ?>
				<tr>
					<th width="120"><?=translate('Paid By')?> :</th>
					<td>
						<?php
							echo html_escape(get_type_name_by_id('staff', $row['paid_by']));
						?>
					</td>
				</tr>
				<?php } ?>
				<tr>
					<th><?=translate('applicant')?> :</th>
					<td><?=ucfirst($row['staffid'] . ' - ' . $row['staff_name'])?></td>
				</tr>
				<?php if (!empty($row['milestone'])) { ?>
				<tr>
					<th><?=translate('milestone')?> :</th>
					<td><?php $getMilestone = $this->db->select('title')->where('id', $row['milestone'])->get('tracker_milestones')->row_array();
						echo $getMilestone['title'];?></td>
				</tr>
				<?php } ?>
				
				<?php if (!empty($row['task_id'])) { ?>
				<tr>
					<th><?=translate('task details')?> :</th>
					<td><?php $getTask = $this->db->select('unique_id, task_title')->where('id', $row['task_id'])->get('tracker_issues')->row_array();
						echo $getTask['task_title'];?>
						<button type="button" class="btn btn-info btn-xs ml-1" onclick="viewTask('<?= $getTask['unique_id'] ?>')" title="View Task">
							<i class="fas fa-eye"></i>
						</button></td>
				</tr>
				<?php } ?>
				<tr>
					<th><?=translate('fund_type')?> :</th>
					<td><?=ucfirst($row['fund_name'])?></td>
				</tr>
				<?php
				$fund_name = strtolower(trim($row['fund_name']));
				if ($fund_name == 'conveyance' || $fund_name == 'convence'):
				?>
				<tr>
					<th><?=translate('CRM Token / Lead No.')?> :</th>
					<td><?=ucfirst($row['token'])?></td>
				</tr>
					<?php endif; ?>
				<tr>
					<th><?=translate('amount')?> :</th>
					<td><?=html_escape(currencyFormat($row['amount']))?></td>
				</tr>
				<tr>
					<th><?=translate('applied_on')?> : </th>
					<td><?php echo _d($row['request_date']);?></td>
				</tr>
				<tr>
					<th><?=translate('reason')?> : </th>
					<td width="350"><?=(empty($row['reason']) ? 'N/A' : $row['reason']);?></td>
				</tr>
				<?php if (!empty($row['enc_file_name'])) { ?>
				<tr>
					<th><?php echo translate('attachment'); ?> : </th>
					<td>
						<a class="btn btn-primary btn-sm" target="_blank" href="<?=base_url('uploads/attachments/fund_requisition/' . $row['enc_file_name'])?>"><i class="fas fa-eye"></i> <?php echo translate('view'); ?></a>
						<a class="btn btn-default btn-sm" target="_blank" href="<?=base_url('fund_requisition/download/' . $row['id'] . '/' . $row['enc_file_name'])?>"><i class="far fa-arrow-alt-circle-down"></i> <?php echo translate('download'); ?></a>
					</td>
				</tr>
				<?php } ?>
				<tr>
					<th><?=translate('Billing Type')?> : </th>
					<td width="350"><?=(empty($row['billing_type']) ? 'N/A' : translate($row['billing_type']));?></td>
				</tr>
				<tr>
					<th><?php echo translate('comments'); ?> : </th>
					<td width="350"><?=(empty($row['comments']) ? 'N/A' : $row['comments']);?></td>
				</tr>
			</tbody>
		</table>
		<?php if (!empty($ledger_entries)) { ?>
    <h5><strong><?=translate('Ledger Entries:')?></strong></h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th><?=translate('Name')?></th>
                <th><?=translate('Amount')?></th>
            </tr>
        </thead>
        <tbody>
            <?php
                $grand_total = 0;
                foreach ($ledger_entries as $entry) {
                    $grand_total += (float)$entry['amount'];
            ?>
                <tr>
                    <td><?= html_escape($entry['name']) ?></td>
                    <td><?= currencyFormat($entry['amount']) ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Display Totals -->
    <div class="col-md-offset-7 mt-3">
        <p><strong><?=translate('Ledger Total')?>:</strong> <?= currencyFormat($grand_total) ?></p>
        <p><strong><?=translate('Original Amount')?>:</strong> <?= currencyFormat($row['amount']) ?></p>

        <?php
			$difference = $grand_total - (float)$row['amount'];
			if ($difference < 0) {
			
			echo '<p><strong>' . translate('Due') . '</strong>: <span id="dueAmount">0.00</span> | <strong>' . translate('Remaining') . '</strong>: <span id="extraAmount">' . currencyFormat(abs($difference)) .  '</span></p>';

			} elseif ($difference > 0) {
				 echo '<p><strong>' . translate('Due') . '</strong>: <span id="dueAmount">' . currencyFormat(abs($difference)) .  '</span> | <strong>' . translate('Remaining') . '</strong>: <span id="extraAmount">0.00</span></p>';
			} else {
				echo '<p><strong>' . translate('Due') . '</strong>: <span id="dueAmount">0.00</span> | <strong>' . translate('Remaining') . '</strong>: <span id="extraAmount">0.00</span></p>';
			}
		?>
    </div>
<?php } ?>

	</div>
	<?php if ($row['status'] == 2 && $row['payment_status'] == 2 && $row['ledger_status'] == Null): ?>
<hr>

<h5><strong><?=translate('Ledger Inputs')?>:</strong></h5>
<form  id="ledgerForm"  method="post" action="<?= base_url('fund_requisition/save_ledger') ?>">
<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />
    <table class="table table-bordered" id="ledgerTable">
        <thead>
            <tr>
                <th><?=translate('Name')?></th>
                <th><?=translate('Amount')?></th>
                <th width="50"><?=translate('Action')?></th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
    <button type="button" class="btn btn-success" id="addRow"><i class="fas fa-plus-circle"></i> <?=translate('Add Row')?></button>

    <div class="col-md-offset-8 mt-3">
        <p><strong><?=translate('Ledger Total')?>:</strong> <span id="grandTotal">0</span></p>
        <p><strong><?=translate('Original Amount')?>:</strong> <?=currencyFormat($row['amount'])?></p>
        <p><strong><?=translate('Due')?></strong>: <span id="dueAmount">0</span> | <strong><?=translate('Remaining')?></strong>: <span id="extraAmount">0</span></p>
    </div>

    <input type="hidden" name="fund_id" value="<?=$row['id']?>">
    <input type="hidden" name="json_data" id="json_data">

    <button type="submit" class="col-md-offset-8 btn btn-primary"><?=translate('Submit Ledger')?></button>
</form>
<?php endif; ?>


	<hr>
	<h5><strong><?=translate('Ledger Adjustment Summary:')?></strong></h5>
	<table class="table table-bordered">
		<tr>
			<th><?=translate('Adjustment Amount')?></th>
			<td><?= currencyFormat($row['adjust_amount']) ?></td>
		</tr>
		<tr>
			<th><?=translate('Ledger Status')?></th>
			<td><?= ucfirst(html_escape($row['ledger_status'])) ?></td>
		</tr>
	</table>


</div>

<footer class="panel-footer">
	<div class="row">
		<div class="col-md-12 text-right">
			<button class="btn btn-default modal-dismiss"><?=translate('close')?></button>
		</div>
	</div>
</footer>


<!-- Task Details Modal -->
<div class="modal fade" id="taskDetailsModal" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-xl custom-task-modal" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Task Details</h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<!-- Task details will be loaded here -->
			</div>
		</div>
	</div>
</div>


<style>
.modal-block {
    background: transparent;
    padding: 0;
    text-align: left;
    max-width: 80%;
    margin: 40px auto;
    position: relative;
}
  .custom-task-modal {
    width: 80% !important;
    max-width: 1200px;
    height: 90vh;
  }

  .custom-task-modal .modal-content {
    height: 100%;
    display: flex;
    flex-direction: column;
  }

  .custom-task-modal .modal-body {
    flex: 1;
    overflow-y: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
  }

  .custom-task-modal .modal-body::-webkit-scrollbar {
    width: 0px;
    background: transparent;
  }

  #commentsList {
    scrollbar-width: none;
    -ms-overflow-style: none;
  }

  #commentsList::-webkit-scrollbar {
    width: 0px;
    background: transparent;
  }

  /* Mention system styles */
  .mention-container {
    position: relative;
  }

  .mention-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    min-width: 200px;
  }

  #mentionDropdown {
    bottom: 100%;
    top: auto;
  }

  .mention-item {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
  }

  .mention-item:hover,
  .mention-item.selected {
    background: #f0f8ff;
  }

  .mention-item:last-child {
    border-bottom: none;
  }
  
  .mention-name {
    font-size: 14px;
    color: #333;
  }

  .mention-highlight {
    background: #e3f2fd;
    color: #1976d2;
    padding: 2px 4px;
    border-radius: 3px;
    font-weight: 500;
  }
</style>


<script>
// View task details in modal (global function)
function viewTask(id) {
	$.ajax({
		url: base_url + 'dashboard/viewTracker_Issue',
		type: 'POST',
		data: {'id': id},
		dataType: "html",
		success: function (data) {
			$('#taskDetailsModal .modal-body').html(data);
			$('#taskDetailsModal').modal('show');
		}
	});
}

function recalculateTotals() {
    let total = 0;
    $("#ledgerTable tbody tr").each(function() {
        let amt = parseFloat($(this).find(".amount").val()) || 0;
        total += amt;
    });

    $("#grandTotal").text(total.toFixed(2));
    let originalAmount = <?= (float)$row['amount'] ?>;

    if (total < originalAmount) {
        $(extraAmount).text((originalAmount - total).toFixed(2));
        $("#dueAmount").text("0.00");
    } else if (total > originalAmount) {
        $("#dueAmount").text((total - originalAmount).toFixed(2));
        $("#extraAmount").text("0.00");
    } else {
        $("#dueAmount, #extraAmount").text("0.00");
    }
}

$("#addRow").click(function() {
    $("#ledgerTable tbody").append(`
        <tr>
            <td><input type="text" name="name[]" class="form-control" required></td>
            <td><input type="number" step="0.01" name="amount[]" class="form-control amount" required></td>
            <td><button type="button" class="btn btn-danger btn-sm removeRow"><i class="fas fa-times"></i></button></td>
        </tr>
    `);
});

$("#ledgerTable").on("click", ".removeRow", function() {
    $(this).closest("tr").remove();
    recalculateTotals();
});

$("#ledgerTable").on("input", ".amount", function() {
    recalculateTotals();
});

// Before form submit: convert rows into JSON
document.getElementById("ledgerForm").addEventListener("submit", function(e) {
    let entries = [];
    $("#ledgerTable tbody tr").each(function() {
        let name = $(this).find("input[name='name[]']").val();
        let amt = parseFloat($(this).find("input[name='amount[]']").val()) || 0;
        if (name && amt) {
            entries.push({ name: name, amount: amt });
        }
    });

    document.getElementById("json_data").value = JSON.stringify(entries);
});

$(document).ready(function() {
    recalculateTotals();
});

</script>
