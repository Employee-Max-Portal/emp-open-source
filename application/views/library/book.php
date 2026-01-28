<section class="panel">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#create" data-toggle="tab"><i class="far fa-edit"></i> <?=translate('add_policy')?></a>
			</li>
		</ul>
		<div class="tab-content">
			<div id="create" class="tab-pane active">
				<?php if (get_permission('policy', 'is_add')): ?>
				<?php echo form_open_multipart($this->uri->uri_string(), array(
						'class' => 'form-horizontal form-bordered frm-submit-data',
						'enctype' => 'multipart/form-data' // ✅ important for file + HTML input
					)); ?>

					<?php if (in_array(loggedin_role_id(), [1, 2, 3, 5])): ?>
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('business')?> <span class="required">*</span></label>
						<div class="col-md-6">
							<?php
								$arrayBranch = $this->app_lib->getSelectList('branch');
								echo form_dropdown("branch_id", $arrayBranch, "", "class='form-control' id='branch_id'
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
							?>
							<span class="error"></span>
						</div>
					</div>
					<?php endif; ?>
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('policy_name')?> <span class="required">*</span></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="book_title" value="" />
							<span class="error"></span>
						</div>
					</div>
					
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('category')?> <span class="required">*</span></label>
						<div class="col-md-6">
							<?php
								$array = $this->app_lib->getSelectByBranch('policy_category', $branch_id);
								echo form_dropdown("category_id", $array, set_value('category_id'), "class='form-control' id='book_category_holder' data-plugin-selectTwo
								data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('description') ?></label>
						<div class="col-md-6">
							<textarea name="description" id="description" class="summernote"></textarea>
							<span class="error"><?php echo form_error('description'); ?></span>
						</div>
					</div>

					<div class="form-group">
						<label class="col-md-3 control-label"><?php echo translate('document') . " " . translate('file'); ?> </label>
						<div class="col-md-6">
							<input type="file" name="document_file" class="dropify" data-height="110" data-default-file="" id="adocument_file" />
							<span class="error"></span>
						</div>
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
		// Initialize Summernote
		$('.summernote').summernote({
			height: 300,
			dialogsInBody: true,
			toolbar: [
				['style', ['bold', 'italic', 'underline', 'clear']],
				['font', ['fontsize']],
				['para', ['ul', 'ol', 'paragraph']],
				['insert', ['link']],
				['view', ['fullscreen', 'codeview']]
			]
		});

		// Custom tag button click inserts text
		$('.btn_tag').on('click', function() {
	var txtToAdd = $(this).data("value");
	var $focusedEditor = $('.summernote:focus');
	if ($focusedEditor.length > 0) {
		$focusedEditor.summernote('insertText', txtToAdd);
	} else {
		// fallback to first one
		$('.summernote').eq(0).summernote('insertText', txtToAdd);
	}
});

	});
</script>
 