<div class="row">
	<div class="col-md-5">
<?php if (get_permission('fund_requisition_category', 'is_add')): ?>
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="far fa-edit"></i> <?=translate('add') . " " . translate('category')?></h4>
			</header>
			<?php echo form_open($this->uri->uri_string()); ?>
				<div class="panel-body">
					<div class="form-group mb-md">
						<label class="control-label"><?=translate('name')?> <span class="required">*</span></label>
						<input type="text" class="form-control" name="name" value="<?=set_value('name')?>" />
						<span class="error"><?=form_error('name')?></span>
					</div>
				</div>
				
				<div class="panel-footer">
					<div class="row">
						<div class="col-md-12">
							<button class="btn btn-default pull-right" type="submit" name="save" value="1">
								<i class="fas fa-plus-circle"></i> <?=translate('save')?>
							</button>
						</div>	
					</div>
				</div>
			<?php echo form_close();?>
		</section>
	</div>
<?php endif; ?>
<?php if (get_permission('fund_requisition_category', 'is_view')): ?>
	<div class="col-md-<?php if (get_permission('fund_requisition_category', 'is_add')){ echo '7'; }else{echo '12';} ?>">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-list-ul"></i> <?=translate('category') . " " . translate('list')?></h4>
			</header>
			<div class="panel-body">
				<div class="table-responsive">
					<table class="table table-bordered table-hover table-condensed mb-none">
						<thead>
							<tr>
								<th><?=translate('sl')?></th>
								<th><?=translate('name')?></th>
								<th><?=translate('action')?></th>
							</tr>
						</thead>
						<tbody>
							<?php 
								$count = 1;
								if (count($categorylist)){
								foreach ($categorylist as $row):
							?>
							<tr>
							    <td><?php echo $count++;?></td>
								<td><?php echo $row['name']; ?></td>
								<td>
								<?php if (get_permission('fund_requisition_category', 'is_edit')): ?>
									<!-- update link -->
									<a class="btn btn-default btn-circle icon" onclick="getCategoryModal(this)" href="javascript:void(0);"
									data-id="<?=$row['id']?>" data-name="<?=$row['name']?>" data-branch="<?=$row['branch_id']?>">
										<i class="fas fa-pen-nib"></i>
									</a>
								<?php endif; if (get_permission('fund_requisition_category', 'is_delete')): ?>	
									<!--delete link-->
									<?php echo btn_delete('fund_requisition/category_delete/' . $row['id']);?>
								<?php endif;?>
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
<?php endif; ?>
</div>

<?php if (get_permission('fund_requisition_category', 'is_edit')): ?>
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
	<section class="panel">
		<?php echo form_open('fund_requisition/category_edit', array('class' => 'frm-submit')); ?>
			<header class="panel-heading">
				<h4 class="panel-title">
					<i class="far fa-edit"></i> <?=translate('edit') . " " . translate('category')?>
				</h4>
			</header>
			<div class="panel-body">
				<input type="hidden" name="category_id" id="ecategory_id" value="" />

				<div class="form-group mb-md">
					<label class="control-label"><?=translate('name')?> <span class="required">*</span></label>
					<input type="text" class="form-control" name="name" id="ename" value="" />
					<span class="error"></span>
				</div>

			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-12 text-right">
						<button type="submit" class="btn btn-default" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
							<i class="fas fa-plus-circle"></i> <?=translate('update')?>
						</button>
						<button class="btn btn-default modal-dismiss"><?=translate('cancel')?></button>
					</div>
				</div>
			</footer>
		<?php echo form_close();?>
	</section>
</div>
<?php endif; ?>