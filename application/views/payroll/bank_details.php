<section class="panel">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#list" data-toggle="tab"><i class="fas fa-list-ul"></i> <?=translate('assets_list')?></a>
			</li>
<?php if (get_permission('assets', 'is_add')): ?>
			<li>
				<a href="#create" data-toggle="tab"><i class="far fa-edit"></i> <?=translate('add_asset')?></a>
			</li>
<?php endif; ?>	
		</ul>
		<div class="tab-content">
			<div id="list" class="tab-pane active">
				<table class="table table-bordered table-hover table-condensed mb-none tbr-top table-export">
					<thead>
						<tr>
							<th><?= translate('sl') ?></th>
							<th><?= translate('branch') ?></th>
							<th><?= translate('assigned_to') ?></th>
							<th><?= translate('asset_type') ?></th>
							<th><?= translate('asset_name') ?></th>
							<th><?= translate('serial_number') ?></th>
							<th><?= translate('brand') ?></th>
							<th><?= translate('status') ?></th>
							<th><?= translate('photo') ?></th>
							<th><?= translate('price') ?></th>
							<th><?= translate('purchase_date') ?></th>
							<th><?= translate('action') ?></th>
						</tr>
					</thead>
					<tbody>
						<?php $count = 1; foreach ($assetlist as $row): ?>
						<tr>
							<td><?= $count++ ?></td>
							<td><?= get_type_name_by_id('branch', $row['branch_id']) ?></td>
							<td><?= get_type_name_by_id('staff', $row['assigned_to']) ?></td>
							<td><?php echo get_type_name_by_id('assets_category', $row['asset_type']);?></td>
							<td><?= $row['asset_name'] ?></td>
							<td><?= $row['serial_number'] ?></td>
							<td><?= $row['brand'] ?></td>
							<td><span class="label label-info"><?= ucfirst($row['status']) ?></span></td>
							<td>
								<?php if ($row['photo']): ?>
									<img src="<?= base_url('uploads/asset_photos/' . $row['photo']) ?>" alt="Asset Photo" width="70">
								<?php else: ?>
									<span class="text-muted"><?= translate('no_photo') ?></span>
								<?php endif; ?>
							</td>
							<td><?= currencyFormat($row['price']) ?></td>
							<td><?= _d($row['purchase_date']) ?></td>
							<td class="min-w-c">
							<?php if (get_permission('assets', 'is_edit')): ?>
								<!--update link-->
								<a href="<?php echo base_url('assets/assets_edit/' .  $row['id'] );?>" class="btn btn-default btn-circle icon">
									<i class="fas fa-pen-nib"></i>
								</a>
							<?php endif; if (get_permission('assets', 'is_delete')): ?>
								<!--deletion link-->
								<?php echo btn_delete('assets/assets_delete/' . $row['id']);?>
							<?php endif; ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
<?php if (get_permission('assets', 'is_add')): ?>
			<div class="tab-pane" id="create">
				<?php echo form_open_multipart($this->uri->uri_string(), array('class' => 'form-horizontal form-bordered frm-submit-data'));?>
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('branch')?> <span class="required">*</span></label>
						<div class="col-md-6">
							<?php
								$arrayBranch = $this->app_lib->getSelectList('branch');
								echo form_dropdown("branch_id", $arrayBranch, "", "class='form-control' id='branch_id'
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
							?>
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('category')?> <span class="required">*</span></label>
						<div class="col-md-6">
							<?php
								$array = $this->app_lib->getSelectByBranch('assets_category', $branch_id);
								echo form_dropdown("category_id", $array, set_value('category_id'), "class='form-control' id='asset_category_holder' data-plugin-selectTwo
								data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
							<span class="error"></span>
						</div>
					</div>
					
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('assign_to')?> <span class="required">*</span></label>
						<div class="col-md-6">
							<?php
								$array = $this->app_lib->getSelectList('staff', $branch_id);
								echo form_dropdown("staff_id", $array, set_value('staff_id'), "class='form-control' id='asset_category_holder' data-plugin-selectTwo
								data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('asset_name')?> <span class="required">*</span></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="asset_name" value="" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('serial_no')?></label>
						<div class="col-md-6"><input type="text" class="form-control" name="serial_number"/></div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('brand')?></label>
						<div class="col-md-6"><input type="text" class="form-control" name="brand"/></div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('price')?></label>
						<div class="col-md-6"><input type="text" class="form-control" name="price"/></div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('purchase_date')?> <span class="required">*</span></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="purchase_date" value="<?=set_value('purchase_date', date('Y-m-d'))?>" data-plugin-datepicker
							data-plugin-options='{ "todayHighlight" : true }' />
							<span class="error"></span>
						</div>
					</div>
					
				
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('photo')?></label>
						<div class="col-md-6"><input type="file" name="photo" class="dropify" data-allowed-file-extensions="jpg png" /></div>
					</div>
					
					<footer class="panel-footer">
						<div class="row">
							<div class="col-md-offset-3 col-md-2">
								<button type="submit" class="btn btn-default btn-block" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
									<i class="fas fa-plus-circle"></i> <?=translate('save')?>
								</button>
							</div>
						</div>
					</footer>
				<?php echo form_close(); ?>
			</div>
<?php endif; ?>	
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
					$('#book_category_holder').html(data);
				}
			});
		});
	});
</script>