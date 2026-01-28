<div class="row">
<?php if (get_permission('cashbook_accounts', 'is_add')): ?>
	<div class="col-md-5">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="far fa-edit"></i> <?php echo translate('add') . " " . translate('accounts'); ?></h4>
			</header>
			<?php echo form_open($this->uri->uri_string()); ?>
				<div class="panel-body">
					<div class="form-group mb-md">
						<label class="control-label"><?php echo translate('accounts_name'); ?> <span class="required">*</span></label>
						<input type="text" class="form-control" name="name" value="<?php echo set_value('name'); ?>" />
						<span class="error"><?=form_error('name')?></span>
					</div>
				</div>
				<div class="panel-footer">
					<div class="row">
						<div class="col-md-12">
							<button class="btn btn-default pull-right" type="submit"><i class="fas fa-plus-circle"></i> <?php echo translate('save'); ?></button>
						</div>	
					</div>
				</div>
			<?php echo form_close(); ?>
		</section>
	</div>
<?php endif; ?>
<?php if (get_permission('cashbook_accounts', 'is_view')): ?>
	<div class="col-md-<?php if (get_permission('cashbook_accounts', 'is_add')){ echo "7"; }else{echo "12";} ?>">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-list-ul"></i> <?php echo translate('account') . " " . translate('list'); ?></h4>
			</header>
			<div class="panel-body">
				<div class="table-responsive">
					<table class="table table-bordered table-hover table-condensed mb-none">
						<thead>
							<tr>
								<th width="5%"><?php echo translate('sl'); ?></th>
								<th width="80%"><?php echo translate('name'); ?></th>
								<th width="15%"><?php echo translate('action'); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php
						$count = 1;
						if (count($accounts)) {
							foreach ($accounts as $row):
						?>
							<tr>
								<td><?php echo $count++; ?></td>
								<td><?php echo $row['name']; ?></td>
								<td class="min-w-xs">
								<?php if (get_permission('cashbook_accounts', 'is_edit')): ?>
									<a class="btn btn-default btn-circle icon" href="javascript:void(0);" onclick="getAccountDetails('<?=$row['id']?>')">
										<i class="fas fa-pen-nib"></i>
									</a>
								<!--<?php  endif; if (get_permission('cashbook_accounts', 'is_delete')): ?>
									<?php echo btn_delete('cashbook/account_delete/' . $row['id']); ?>
								<?php endif; ?> -->
								</td>
							</tr>
						<?php
							endforeach;
						}else{
								echo '<tr><td colspan="4"><h5 class="text-danger text-center">' . translate('no_information_available') . '</td></tr>';
						}
						?>
						</tbody>
					</table>
				</div>
			</div>
		</section>
	</div>
</div>
<?php endif; ?>
<?php if (get_permission('cashbook_accounts', 'is_edit')): ?>
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
	<section class="panel">
		<header class="panel-heading">
			<h4 class="panel-title"><i class="far fa-edit"></i> <?php echo translate('edit') . " " . translate('account'); ?></h4>
		</header>
		<?php echo form_open('cashbook/account_edit', array('class' => 'frm-submit')); ?>
			<div class="panel-body">
				<input type="hidden" name="account_id" id="eaccount_id" value="" />
				<div class="form-group mb-md">
					<label class="control-label"><?php echo translate('name'); ?> <span class="required">*</span></label>
					<input type="text" class="form-control" value="" name="name" id="cname" />
					<span class="error"></span>
				</div>
			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-12 text-right">
						<button type="submit" class="btn btn-default" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
							<i class="fas fa-plus-circle"></i> <?php echo translate('update'); ?>
						</button>
						<button class="btn btn-default modal-dismiss"><?php echo translate('cancel'); ?></button>
					</div>
				</div>
			</footer>
		<?php echo form_close(); ?>
	</section>
</div>
<?php endif; ?>

<script>
// get department details
function getAccountDetails(id) {
    $.ajax({
        url: base_url + 'ajax/cashbook_accounts_details',
        type: 'POST',
        data: {'id': id},
        dataType: "json",
        success: function (data) {
            $('.error').html("");
            $('#eaccount_id').val(data.id);
            $('#cname').val(data.name);
            mfp_modal('#modal');
        }
    });
}
</script>