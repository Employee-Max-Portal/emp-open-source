<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><?=translate('select_ground')?></h4>
			<?php
			if (get_permission('server_request', 'is_add')): ?>
					<div class="panel-btn">
						<a href="javascript:void(0);" id="serverRequest" class="btn btn-default btn-circle">
							<i class="fas fa-plus-circle"></i> <?=translate('server_access_request')?>
						</a>
					</div>
			<?php endif; ?>
			</header>
			
			<?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
				<div class="panel-body">
					<div class="row mb-sm">
						<div class="col-md-offset-3 col-md-6 mb-sm">
							<div class="form-group">
	                            <label class="control-label"><?php echo translate('date'); ?> <span class="required">*</span></label>
	                            <div class="input-group">
	                                <span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
	                                <input type="text" class="form-control daterange" name="daterange" value="<?=set_value('daterange', date("Y/m/d", strtotime('-6day')) . ' - ' . date("Y/m/d"))?>" required />
	                            </div>
	                        </div>
						</div>
					</div>
				</div>
				<footer class="panel-footer">
					<div class="row">
						<div class="col-md-offset-10 col-md-2">
							<button type="submit" name="search" value="1" class="btn btn btn-default btn-block"><i class="fas fa-filter"></i> <?=translate('filter')?></button>
						</div>
					</div>
				</footer>
			<?php echo form_close(); ?>
		</section>

		<section class="panel appear-animation" data-appear-animation="<?=$global_config['animations'] ?>" data-appear-animation-delay="100">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-users" aria-hidden="true"></i> <?=translate('server_request')?></h4>
			</header>
			<div class="panel-body">
				<table class="table table-bordered table-condensed table-hover mb-none table-export" >
					<thead>
						<tr>
							<th width="50">#</th>
							<th><?=translate('photo')?></th>
							<th><?=translate('applicant')?></th>
							<th><?=translate('server_name')?></th>
							<th><?=translate('applied_on')?></th>
							<th><?=translate('expired_at')?></th>
							<th style="text-align:center;"><?=translate('status')?></th>
							<th><?=translate('action')?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$count = 1;
						foreach ($list as $row) {?>
						<tr>
							<td><?php echo $count++; ?></td>
							<td class="center"><img class="rounded" src="<?php echo get_image_url('staff', $row['photo']);?>" width="40" height="40" /></td>
							<td><?php
								$getStaff = $this->db->select('name,staff_id')->where('staff_id', $row['staff_id'])->get('staff')->row_array();
									echo $getStaff['staff_id'] . ' - ' . $getStaff['name'];
								?></td>
							<td><?php echo $row['server_name'];?></td>
							<td><?php echo date('jS F, Y', strtotime($row['created_at'])); ?></td>
							<td>
							  <?php 
								echo !empty($row['code_expires_at']) && $row['code_expires_at'] != '0000-00-00 00:00:00'
								  ? date('jS F, Y \a\t h:i A', strtotime($row['code_expires_at']))
								  : 'N/A'; 
							  ?>
							</td>

							<td style="text-align:center;">
								<?php
								if ($row['status'] == 1)
									echo '<span class="label label-warning-custom">' . translate('pending') . '</span>';
								else if ($row['status'] == 2)
									echo '<span class="label label-success-custom">' . translate('approved') . '</span>';
								else if ($row['status'] == 3)
									echo '<span class="label label-danger-custom">' . translate('rejected') . '</span>';
								?>
							</td>
							<td>
							<?php if ($row['status'] == 2): ?>
								<!-- Show "View Code" button only for approved requests -->
								<button type="button" class="btn btn-primary btn-circle icon" onclick="openPasswordModal(<?= $row['id'] ?>)">
									<i class="fas fa-lock"></i>
								</button>
							<?php endif; ?>
								<!--modal dialogbox-->
								<a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick="getRequestDetails('<?=$row['id']?>')">
									<i class="fas fa-bars"></i>
								</a>
							<?php if ($row['status'] == 1 && get_permission('server_request', 'is_delete')): ?>
								<!--delete link-->
								<?php echo btn_delete('server_request/request_delete/' . $row['id']);?>
							<?php endif; ?>	
							</td>
						</tr>
						<?php }?>
					</tbody>
				</table>
			</div>

		</section>
		
					<!-- View Secret Code Modal -->
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="codeModal">
    <section class="panel">
        <header class="panel-heading">
            <h2 class="panel-title">🔐 Verify Password</h2>
        </header>
        <form id="codeVerifyForm" class="form-horizontal form-bordered">
            <div class="panel-body">
                <input type="hidden" name="request_id" id="request_id" />
				<!-- CSRF token -->
			<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" 
				   value="<?= $this->security->get_csrf_hash(); ?>" />
                <div class="form-group">
                    <label class="col-md-4 control-label">EMP Portal Password</label>
                    <div class="col-md-8">
                        <input type="password" name="emp_password" class="form-control" autocomplete="off" required />
                    </div>
                </div>
   
                <div id="secret_code_response" class="text-center mt-xs" style="font-weight: bold;"></div>
            </div>
            <footer class="panel-footer">
                <div class="row">
                    <div class="col-md-12 text-right">
                        <button type="submit" class="btn btn-success modal-confirm">View Secret Code</button>
                        <button type="button" class="btn btn-default modal-dismiss">Close</button>
                    </div>
                </div>
            </footer>
        </form>
    </section>
</div>

<script>
function toggleSecretCode() {
    var input = document.getElementById("secretCodeInput");
    var icon = document.getElementById("eyeIcon");
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}

function copyToClipboard(inputId) {
    var input = document.getElementById(inputId);
    input.select();
    input.setSelectionRange(0, 99999); // for mobile
    document.execCommand("copy");

    Swal.fire({
        icon: 'success',
        title: 'Copied!',
        text: 'Copied: ' + input.value,
        timer: 1500,
        showConfirmButton: false
    });
}

</script>

<script>
function openPasswordModal(request_id) {
    $('#request_id').val(request_id);
    $('#secret_code_response').html('');
    $.magnificPopup.open({
        items: {
            src: '#codeModal'
        },
        type: 'inline',
        preloader: false,
        modal: true
    });
}

$('#codeVerifyForm').submit(function(e) {
    e.preventDefault();
    var formData = $(this).serialize();

    $('#secret_code_response').html('<i class="fas fa-spinner fa-spin"></i> Verifying...');
    $('.modal-confirm').prop('disabled', true);

    $.post('<?= base_url("server_request/view_secret_code") ?>', formData, function(response) {
        $('#secret_code_response').html(response);
        $('.modal-confirm').prop('disabled', false);
    }).fail(function(xhr) {
        $('#secret_code_response').html("<div class='text-danger'>Server error. Try again later.</div>");
        $('.modal-confirm').prop('disabled', false);
        console.error("Error:", xhr.responseText);
    });
});


// Close modal on cancel
$(document).on('click', '.modal-dismiss', function (e) {
    e.preventDefault();
    $.magnificPopup.close();
});
</script>


	</div>
</div>

<!-- Advance Salary View Modal -->
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
	<section class="panel" id='quick_view'></section>
</div>

<?php if (get_permission('server_request', 'is_add')): ?>
<!-- Advance Salary Add Modal -->
<div id="serverRequestModal" class="zoom-anim-dialog modal-block modal-block-primary mfp-hide">
    <section class="panel">
        <div class="panel-heading">
            <h4 class="panel-title"><i class="fas fa-plus-circle"></i> <?=translate('server_access_request');?></h4>
        </div>

		<?php echo form_open_multipart('server_request/request_save', array('class' => 'form-horizontal frm-submit-data')); ?>
        <div class="panel-body">

            <!-- server_name -->
		<div class="form-group <?php if (form_error('server_name')) echo 'has-error'; ?>">
			<label class="col-md-3 control-label"><?=translate('server_name / IP')?> <span class="required">*</span></label>
			<div class="col-md-9">
				<select class="form-control" name="server_name" required>
					<option value="">Select Server</option>
					<option value="202.59.208.112" <?= set_value('server_name') == '202.59.208.112' ? 'selected' : '' ?>>202.59.208.112</option>
					<option value="202.59.208.114" <?= set_value('server_name') == '202.59.208.114' ? 'selected' : '' ?>>202.59.208.114</option>
				</select>
				<span class="error"><?=form_error('server_name')?></span>
			</div>
		</div>
			
			<!-- server_directory -->
			<div class="form-group <?php if (form_error('server_directory')) echo 'has-error'; ?>">
				<label class="col-md-3 control-label"><?=translate('server_directory')?> <span class="required">*</span></label>
				<div class="col-md-9">
					<div class="input-group">
						<span class="input-group-addon" style="min-width:150px; background:#eee;">/var/www/html/</span>
						<input type="text" class="form-control" name="server_directory"
							   placeholder="project_folder" 
							   value="<?= set_value('server_directory') ?>" required />
					</div>
					<span class="help-block small text-muted">You can only set the subdirectory inside <code>/var/www/html/</code>.</span>
					<span class="error"><?= form_error('server_directory') ?></span>
				</div>
			</div>


            <!-- Reason -->
            <div class="form-group mb-md">
                <label class="col-md-3 control-label"><?=translate('reason')?> <span class="required">*</span></label>
                <div class="col-md-9">
                    <textarea class="form-control" rows="4" name="reason" placeholder="Enter your Reason" required><?=set_value('reason')?> </textarea>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="panel-footer">
            <div class="row">
                <div class="col-md-12 text-right">
                    <button type="submit" name="request" value="1" class="btn btn-default mr-xs" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
                        <i class="fas fa-plus-circle"></i> <?=translate('apply') ?>
                    </button>
                    <button class="btn btn-default modal-dismiss"><?=translate('cancel') ?></button>
                </div>
            </div>
        </footer>
        <?php echo form_close();?>
    </section>
</div>

<?php endif; ?>

<script type="text/javascript">
	function getRequestDetails(id) {
	    $.ajax({
	        url: base_url + 'server_request/getRequestDetails',
	        type: 'POST',
	        data: {'id': id},
	        dataType: "html",
	        success: function (data) {
	            $('#quick_view').html(data);
	            mfp_modal('#modal');
	        }
	    });
	}
</script>


