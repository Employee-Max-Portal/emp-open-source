<div class="row">
    <div class="col-md-12">
	<!-- Filter Section -->
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><?=translate('filter_options')?></h4>
				<?php if (get_permission('cashbook_manage', 'is_add')): ?>
					<div class="panel-btn">
						<button type="button" class="btn btn-success" onclick="mfp_modal('#addEntryModal')"><?=translate('add')?></button>
						<button type="button" class="btn btn-info" onclick="mfp_modal('#transferModal')">Transfer</button>
					</div>
				<?php endif; ?>
			</header>
			
			<?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
				<div class="panel-body">
					<div class="row mb-sm">
						<div class="col-md-6 mb-sm">
							<div class="form-group">
							<label class="control-label"><?=translate('date_range')?></label>
                            <input type="text" class="form-control" name="daterange" value="<?php echo set_value('daterange'); ?>" />
							</div>
						</div>
						<div class="col-md-6 mb-sm">
							<div class="form-group">
							<label class="control-label"><?=translate('account_type')?></label>
                            <select name="account_type" class="form-control">
                                <option value="">All</option>
                                <?php foreach($account_types as $account): ?>
                                    <option value="<?= $account['id'] ?>"><?= ucfirst($account['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
							</div>
						</div>						
					</div>
				</div>
				<footer class="panel-footer">
					<div class="row">
						<div class="col-md-8">
					
						</div>
						<div class="col-md-4">
							<button type="submit" name="search" value="1" class="btn btn-primary btn-block">
								<i class="fas fa-filter"></i> <?= translate('filter') ?>
							</button>
						</div>
					</div>
				</footer>
			<?php echo form_close(); ?>
		</section>


        <section class="panel">
            <header class="panel-heading">
                <h4 class="panel-title"><?=translate('cashbook')?></h4>
            </header>
            
            <!-- Current Balance Cards -->
            <div class="row" style="margin: 15px;">
                <div class="col-md-3">
                    <div class="panel panel-default">
                        <div class="panel-body text-center">
                            <h4 class="text-success">Cash Balance</h4>
                            <h3><?= number_format($current_balance['cash_balance'], 2) ?> BDT</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="panel panel-default">
                        <div class="panel-body text-center">
                            <h4 class="text-info">Bank Asia</h4>
                            <h3><?= number_format($current_balance['bank_asia_balance'], 2) ?> BDT</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="panel panel-default">
                        <div class="panel-body text-center">
                            <h4 class="text-warning">Premier Bank</h4>
                            <h3><?= number_format($current_balance['premier_bank_balance'], 2) ?> BDT</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="panel panel-default">
                        <div class="panel-body text-center">
                            <h4 class="text-primary">Total Balance</h4>
                            <h3><?= number_format($current_balance['total_balance'], 2) ?> BDT</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel-body">
                <!-- Cashbook Entries Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-condensed table-export" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="10%"><?=translate('date')?></th>
                                <th width="40%"><?=translate('description')?></th>
                                <th width="8%"><?=translate('from')?></th>
                                <th width="8%"><?=translate('cash_in')?></th>
                                <th width="8%"><?=translate('cash_out')?></th>
                                <th width="8%"><?=translate('reference')?></th>
                                <th width="10%"><?=translate('created_by')?></th>
                                <?php if (get_permission('cashbook_manage', 'is_delete')): ?>
                                <th width="8%"><?=translate('action')?></th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($cashbook_data as $entry): ?>
                            <tr>
								<td style="display:none;"><?php echo $i++; ?></td>
                                <td><?= date('d M Y', strtotime($entry['entry_date'])) ?></td>
                                <td><?= $entry['description'] ?></td>
                                <td>
                                    <?php 
                                    $account_name = !empty($entry['account_type']) ? $entry['account_type'] : 'cash'; // Use account_type field
                                    ?>
                                    <span class="label label-<?= strtolower($account_name) == 'cash' ? 'success' : 'info' ?>">
                                        <?= ucfirst($account_name) ?>
                                    </span>
                                </td>
                                <td class="text-success">
                                    <?= ($entry['entry_type'] == 'in') ? number_format($entry['amount'], 2) : '-' ?>
                                </td>
                                <td class="text-danger">
                                    <?= ($entry['entry_type'] == 'out') ? number_format($entry['amount'], 2) : '-' ?>
                                </td>
                                <td>
                                    <?php 
                                    $ref_type = $entry['reference_type'];
                                    $label_class = 'default';
                                    $display_text = ucfirst(str_replace('_', ' ', $ref_type));
                                    
                                    if ($ref_type == 'opening_balance') {
                                        $label_class = 'success';
                                        $display_text = 'Opening Balance';
                                    } elseif ($ref_type != 'manual') {
                                        $label_class = 'primary';
                                    }
                                    ?>
                                    <span class="label label-<?= $label_class ?>">
                                        <?= $display_text ?>
                                    </span>
                                </td>
                                <td><?= $entry['created_by_name'] ?></td>
                                <?php if (get_permission('cashbook_manage', 'is_delete') || get_permission('cashbook_manage', 'is_edit')): ?>
                                <td>
									<?php if (get_permission('cashbook_manage', 'is_edit')): ?>
										<a class="btn btn-default btn-circle icon" href="javascript:void(0);" onclick="getDetails('<?=$entry['id']?>')">
										<i class="fas fa-pen-nib"></i>
										</a>
									<?php endif; ?>
									<?php if (get_permission('cashbook_manage', 'is_delete')): ?>
										<?php echo btn_delete('cashbook/delete/' . $entry['id']); ?>
									<?php endif; ?>
                                    <?php if ($entry['reference_type'] == 'manual' || $entry['reference_type'] == 'opening_balance'): ?>
                                       
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- Add Entry Modal -->
<div id="addEntryModal" class="zoom-anim-dialog modal-block modal-block-primary mfp-hide">
    <section class="panel">
        <header class="panel-heading">
            <h4 class="panel-title"><?=translate('add_cashbook_entry')?></h4>
        </header>
        <?php echo form_open('cashbook/add_entry', array('class' => 'form-horizontal form-bordered', 'id' => 'addEntryForm')); ?>
        <div class="panel-body">
            <div class="form-group">
                <label class="col-md-3 control-label"><?=translate('entry_type')?> <span class="required">*</span></label>
                <div class="col-md-9">
                    <select name="entry_type" class="form-control" required id="entry_type">
                        <option value="">Select Type</option>
                        <option value="in">Cash In</option>
                        <option value="out">Cash Out</option>
                    </select>
                </div>
            </div>
            <div class="form-group" id="cash_in_type_group" style="display: none;">
                <label class="col-md-3 control-label"><?=translate('cash_in_type')?> <span class="required">*</span></label>
                <div class="col-md-9">
                    <select name="cash_in_type" class="form-control" id="cash_in_type">
                        <option value="opening_balance">Opening Balance</option>
                        <option value="manual">Manual Entry</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label"><?=translate('account_type')?> <span class="required">*</span></label>
                <div class="col-md-9">
                    <select name="account_type" class="form-control" required>
                        <option value="">Select Account</option>
                        <?php foreach($account_types as $account): ?>
                            <option value="<?= $account['id'] ?>"><?= ucfirst($account['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label"><?=translate('amount')?> <span class="required">*</span></label>
                <div class="col-md-9">
                    <input type="number" step="0.01" class="form-control" name="amount" required />
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label"><?=translate('description')?> <span class="required">*</span></label>
                <div class="col-md-9">
                    <textarea name="description" class="form-control" rows="3" required></textarea>
                </div>
            </div>
        </div>
        <footer class="panel-footer">
            <div class="row">
                <div class="col-md-12 text-right">
                    <button type="submit" class="btn btn-success"><?=translate('save')?></button>
                    <button class="btn btn-default modal-dismiss"><?=translate('close')?></button>
                </div>
            </div>
        </footer>
        <?php echo form_close(); ?>
    </section>
</div>

<!-- Transfer Modal -->
<div id="transferModal" class="zoom-anim-dialog modal-block modal-block-info mfp-hide">
    <section class="panel">
        <header class="panel-heading">
            <h4 class="panel-title">Transfer Between Accounts</h4>
        </header>
        <?php echo form_open('cashbook/transfer', array('class' => 'form-horizontal form-bordered', 'id' => 'transferForm')); ?>
        <div class="panel-body">
            <div class="form-group">
                <label class="col-md-3 control-label">From Account <span class="required">*</span></label>
                <div class="col-md-9">
                    <select name="from_account" class="form-control" required>
                        <option value="">Select From Account</option>
                        <?php foreach($account_types as $account): ?>
                            <option value="<?= $account['id'] ?>"><?= ucfirst($account['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">To Account <span class="required">*</span></label>
                <div class="col-md-9">
                    <select name="to_account" class="form-control" required>
                        <option value="">Select To Account</option>
                        <?php foreach($account_types as $account): ?>
                            <option value="<?= $account['id'] ?>"><?= ucfirst($account['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Amount <span class="required">*</span></label>
                <div class="col-md-9">
                    <input type="number" step="0.01" class="form-control" name="amount" required />
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Description <span class="required">*</span></label>
                <div class="col-md-9">
                    <textarea name="description" class="form-control" rows="3" required placeholder="Transfer description..."></textarea>
                </div>
            </div>
        </div>
        <footer class="panel-footer">
            <div class="row">
                <div class="col-md-12 text-right">
                    <button type="submit" class="btn btn-info">Transfer</button>
                    <button class="btn btn-default modal-dismiss">Close</button>
                </div>
            </div>
        </footer>
        <?php echo form_close(); ?>
    </section>
</div>


<?php if (get_permission('cashbook_manage', 'is_edit')): ?>
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
	<section class="panel">
		<header class="panel-heading">
			<h4 class="panel-title"><i class="far fa-edit"></i> <?php echo translate('edit') . " " . translate('entry'); ?></h4>
		</header>
		<?php echo form_open('cashbook/transaction_edit', array('class' => 'frm-submit')); ?>
			<div class="panel-body">
				<input type="hidden" name="entry_id" id="et_id" value="" />
				<div class="form-group mb-md">
					<label class="control-label"><?php echo translate('date'); ?> <span class="required">*</span></label>
					<input type="date" class="form-control" id="eentry_date" name="entry_date" required>
				</div>
				<div class="form-group mb-md">
					<label class="control-label"><?php echo translate('entry_type'); ?> <span class="required">*</span></label>
					<select class="form-control" id="eentry_type" name="entry_type" required>
						<option value="in">Cash In</option>
						<option value="out">Cash Out</option>
					</select>
				</div>
				<div class="form-group mb-md">
					<label class="control-label"><?php echo translate('account_type'); ?> <span class="required">*</span></label>
					<select class="form-control" id="eaccount_type" name="account_type_id" required>
						<?php foreach($account_types as $account): ?>
							<option value="<?= $account['id'] ?>"><?= ucfirst($account['name']) ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="form-group mb-md">
					<label class="control-label"><?php echo translate('amount'); ?> <span class="required">*</span></label>
					<input type="number" step="0.01" class="form-control" id="eamount" name="amount" required>
				</div>
				<div class="form-group mb-md">
					<label class="control-label"><?php echo translate('description'); ?> <span class="required">*</span></label>
					<textarea class="form-control" id="edescription" name="description" rows="3" required></textarea>
				</div>
			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-12 text-right">
						<button type="submit" class="btn btn-success" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
							<i class="fas fa-save"></i> <?php echo translate('update'); ?>
						</button>
						<button class="btn btn-default modal-dismiss"><?php echo translate('cancel'); ?></button>
					</div>
				</div>
			</footer>
		<?php echo form_close(); ?>
	</section>
</div>
<?php endif; ?>

<script type="text/javascript">
$(document).ready(function() {
    $('input[name="daterange"]').daterangepicker({
        format: 'YYYY-MM-DD',
        startDate: moment().subtract(29, 'days'),
        endDate: moment(),
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    });
    
    // Show/hide cash in type based on entry type selection
    $('#entry_type').change(function() {
        if ($(this).val() === 'in') {
            $('#cash_in_type_group').show();
            $('#cash_in_type').attr('required', true);
        } else {
            $('#cash_in_type_group').hide();
            $('#cash_in_type').attr('required', false);
        }
    });
});
</script>


<script>
function getDetails(id) {
    $.ajax({
        url: base_url + 'cashbook/transactions_details',
        type: 'POST',
        data: {'id': id},
        dataType: "json",
         success: function (data) {
            $('.error').html("");
            $('#et_id').val(data.id);
            
            // Format the datetime to date for the date input
            var entryDate = data.entry_date;
            if (entryDate) {
                // Extract just the date part (YYYY-MM-DD) from datetime
                var dateOnly = entryDate.split(' ')[0];
                $('#eentry_date').val(dateOnly);
            }
            
            $('#eentry_type').val(data.entry_type);
            $('#eaccount_type').val(data.account_type_id);
            $('#eamount').val(data.amount);
            $('#edescription').val(data.description);
            mfp_modal('#modal');
        },
        error: function(xhr, status, error) {
            console.log('Error fetching entry details:', error);
            alert('Error loading entry details. Please try again.');
        }
    });
}

// Handle edit form submission
$(document).ready(function() {
    var isSubmitting = false;
    
    // Handle edit form submission specifically
    $('.frm-submit').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (isSubmitting) {
            return false;
        }
        
        isSubmitting = true;
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.html();
        
        // Show loading state
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $.magnificPopup.close();
                    if (response.url) {
                        window.location.href = response.url;
                    } else {
                        location.reload();
                    }
                } else {
                    // Show error message
                    var errorMsg = '';
                    if (typeof response.error === 'object') {
                        $.each(response.error, function(key, value) {
                            errorMsg += value + '<br>';
                        });
                    } else {
                        errorMsg = response.error || 'An error occurred';
                    }
                    alert('Error: ' + errorMsg.replace(/<br>/g, '\n'));
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error:', error);
                alert('An error occurred while processing your request. Please try again.');
            },
            complete: function() {
                // Reset button state
                submitBtn.prop('disabled', false).html(originalText);
                isSubmitting = false;
            }
        });
        
        return false;
    });
    
    // Handle add entry form submission
    $('#addEntryForm').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (isSubmitting) {
            return false;
        }
        
        isSubmitting = true;
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.html();
        
        // Show loading state
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $.magnificPopup.close();
                    if (response.url) {
                        window.location.href = response.url;
                    } else {
                        location.reload();
                    }
                } else {
                    // Show error message
                    var errorMsg = '';
                    if (typeof response.error === 'object') {
                        $.each(response.error, function(key, value) {
                            errorMsg += value + '<br>';
                        });
                    } else {
                        errorMsg = response.error || 'An error occurred';
                    }
                    alert('Error: ' + errorMsg.replace(/<br>/g, '\n'));
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error:', error);
                alert('An error occurred while processing your request. Please try again.');
            },
            complete: function() {
                // Reset button state
                submitBtn.prop('disabled', false).html(originalText);
                isSubmitting = false;
            }
        });
        
        return false;
    });
    
    // Transfer form validation
    $('select[name="from_account"], select[name="to_account"]').change(function() {
        var fromAccount = $('select[name="from_account"]').val();
        var toAccount = $('select[name="to_account"]').val();
        
        if (fromAccount && toAccount && fromAccount === toAccount) {
            alert('From and To accounts cannot be the same');
            $(this).val('');
        }
    });
});
</script>