<section class="panel">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<li>
				<a href="<?=base_url('assets/lists')?>"><i class="fas fa-list-ul"></i> <?=translate('assets_list')?></a>
			</li>
			<li class="active">
				<a href="#update" data-toggle="tab"><i class="far fa-edit"></i> <?=translate('edit_assets')?></a>
			</li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active" id="update">
				<?php echo form_open_multipart($this->uri->uri_string(), array('class' => 'form-horizontal form-bordered frm-submit-data'));?>
				<input type="hidden" name="assets_id" value="<?=$asset['id']?>" >
					
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('business')?> <span class="required">*</span></label>
						<div class="col-md-6">
							<?php
								$arrayBranch = $this->app_lib->getSelectList('branch');
								echo form_dropdown("branch_id", $arrayBranch, $asset['branch_id'], "class='form-control' id='branch_id'
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
							?>
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('category')?> <span class="required">*</span></label>
						<div class="col-md-6">
							<?php
								$array = $this->app_lib->getSelectByBranch('assets_category', $asset['branch_id']);
								echo form_dropdown("category_id", $array, $asset['asset_type'], "class='form-control' id='asset_category_holder' data-plugin-selectTwo
								data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
							<span class="error"></span>
						</div>
					</div>
					
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('assign_to')?> <span class="required">*</span></label>
						<div class="col-md-6">
						<?php
								$array = $this->app_lib->getSelectByBranch('staff', $asset['branch_id']);
								echo form_dropdown("staff_id", $array, $asset['assigned_to'], "class='form-control' id='staff_by_branch' data-plugin-selectTwo
								data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>

							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('asset_name')?> <span class="required">*</span></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="asset_name"  value="<?=$asset['asset_name']?>" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('serial_no')?></label>
						<div class="col-md-6"><input type="text" class="form-control" name="serial_number" value="<?=$asset['serial_number']?>" /></div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('brand')?></label>
						<div class="col-md-6"><input type="text" class="form-control" name="brand" value="<?=$asset['brand']?>" /></div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('price')?></label>
						<div class="col-md-6"><input type="text" class="form-control" name="price" value="<?=$asset['price']?>" /></div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('purchase_date')?> <span class="required">*</span></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="purchase_date" value="<?=$asset['purchase_date']?>" data-plugin-datepicker
							data-plugin-options='{ "todayHighlight" : true }' />
							<span class="error"></span>
						</div>
					</div>
					
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('photo')?></label>
						<input type="hidden" name="old_file" value="<?=$asset['photo']?>">
						<div class="col-md-6"><input type="file" name="photo" class="dropify" data-allowed-file-extensions="jpg png" data-default-file="<?=$this->application_model->get_asset_photo($asset['photo']);?>" /></div>
					</div>
					
					<footer class="panel-footer">
						<div class="row">
							<div class="col-md-offset-3 col-md-2">
								<button type="submit" class="btn btn-default btn-block" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
									<i class="fas fa-plus-circle"></i> <?=translate('update')?>
								</button>
							</div>
						</div>
					</footer>
				<?php echo form_close(); ?>
			</div>
		</div>
	</div>
</section>
<script type="text/javascript">
	$(document).ready(function () {
		$('#branch_id').on('change', function(){
			var branchID = $(this).val();
			$.ajax({
				url: "<?=base_url('ajax/getDataByBranch')?>",
				type: 'POST',
				data: {
					table : 'assets_category',
					branch_id : branchID
				},
				success: function (data) {
					$('#asset_category_holder').html(data);
				}
			});
		});
	});
</script>

<script type="text/javascript">
	$(document).ready(function () {
		$('#branch_id').on('change', function(){
			var branchID = $(this).val();
			$.ajax({
				url: "<?=base_url('ajax/getDataByBranch')?>",
				type: 'POST',
				data: {
					table : 'staff',
					branch_id : branchID
				},
				success: function (data) {
					$('#staff_by_branch').html(data);
				}
			});
		});
	});
</script>