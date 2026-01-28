<section class="panel">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#update" data-toggle="tab"><i class="far fa-edit"></i> <?=translate('edit_policy')?></a>
			</li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active" id="update">
				<?php echo form_open_multipart($this->uri->uri_string(), array('class' => 'form-horizontal form-bordered frm-submit-data'));?>
				<input type="hidden" name="book_id" value="<?=$book['id']?>" >
				<input type="hidden" name="exist_file_name" value="<?= $book['document_enc_name'] ?>">

					<?php if (is_superadmin_loggedin()): ?>
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('business')?> <span class="required">*</span></label>
						<div class="col-md-6">
							<?php
								$arrayBranch = $this->app_lib->getSelectList('branch');
								echo form_dropdown("branch_id", $arrayBranch, $book['branch_id'], "class='form-control' id='branch_id'
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
							?>
							<span class="error"></span>
						</div>
					</div>
					<?php endif; ?>
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('policy_name')?> <span class="required">*</span></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="book_title"  value="<?=$book['title']?>" />
							<span class="error"></span>
						</div>
					</div>
					
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('category')?> <span class="required">*</span></label>
						<div class="col-md-6">
							<?php
								$array = $this->app_lib->getSelectByBranch('policy_category', $book['branch_id']);
								echo form_dropdown("category_id", $array, $book['category_id'], "class='form-control' id='book_category_holder' data-plugin-selectTwo
								data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
							<span class="error"></span>
						</div>
					</div>
					
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('description') ?></label>
						<div class="col-md-6">
							
							
							<textarea name="description" id="description" class="summernote"><?=$book['description']?></textarea>
							<span class="error"><?php echo form_error('description'); ?></span>
						</div>
					</div>

					
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('document_file')?></label>
						<input type="hidden" name="exist_file_name" value="<?= $book['document_enc_name'] ?>">
						<div class="col-md-6">
							<input type="file"
								   name="document_file"
								   class="dropify"
								   data-allowed-file-extensions="pdf docx txt csv"
								   data-default-file="<?= !empty($book['document_enc_name']) ? base_url('uploads/attachments/documents/' . $book['document_enc_name']) : '' ?>" />
						</div>
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
					table : 'policy_category',
					branch_id : branchID
				},
				success: function (data) {
					$('#book_category_holder').html(data);
				}
			});
		});
	});
</script>

<script type="text/javascript">
	$(document).ready(function () {
		$('.btn_tag').on('click', function() {
			var txtToAdd = $(this).data("value");
			$('.summernote').summernote('editor.insertText', txtToAdd);
		});
	});
</script>