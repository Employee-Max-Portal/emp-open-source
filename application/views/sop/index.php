
<section class="panel">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<li class="active">
                <a href="#list" data-toggle="tab">
                    <i class="fas fa-list-ul"></i> <?=translate('sop') ." ". translate('list')?>
                </a>
			</li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane box active mb-md" id="list">
				<table class="table table-bordered table-hover mb-none table-condensed table-export">
					<thead>
						<tr>
							<th><?=translate('sl')?></th>
							<th><?=translate('title')?></th>
							<th><?=translate('purpose')?></th>
							<th><?=translate('verifier')?></th>
							<th><?=translate('expected_time')?></th>
							<th><?=translate('updated_at')?></th>
							<th class="no-sort"><?=translate('action')?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$count = 1;
						foreach ($list as $row):
							$verifier_ids = explode(',', $row['verifier_role']);
							$verifier_names = [];
							foreach ($verifier_ids as $rid) {
								$role = $this->db->get_where('roles', ['id' => $rid])->row();
								if ($role) {
									$verifier_names[] = $role->name;
								}
							}
						?>
						<tr>
							<td><?= $count++; ?></td>
							<td><?= html_escape($row['title']); ?></td>
							<td><?= html_escape($row['task_purpose']); ?></td>
							<td><?= implode(', ', $verifier_names); ?></td>
							<td><?= translate($row['expected_time']); ?></td>
							<td>
								<?= !empty($row['updated_at']) ? date('d M Y, h:i A', strtotime($row['updated_at'])) : '--'; ?>
							</td>

							<td class="min-w-c">
								<a href="javascript:void(0);" class="btn btn-default btn-circle icon" data-original-title="<?=translate('view')?>" onclick="getDetailed('<?=$row['id']?>')">
									<i class="fas fa-eye"></i>
								</a>
								<?php if (get_permission('sop_management', 'is_edit')) { ?>
									<a href="<?= base_url('sop/edit/' . $row['id']); ?>" class="btn btn-circle btn-default icon">
										<i class="fas fa-edit"></i>
									</a>
								<?php } if (get_permission('sop_management', 'is_delete')) { ?>
									<?= btn_delete('sop/delete/' . $row['id']); ?>
								<?php } ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

			</div>
		<?php if (get_permission('sop_management', 'is_add')): ?>
			<div class="tab-pane" id="add">
			<?php echo form_open(current_url(), array('method' => 'post', 'class' => 'form-bordered form-horizontal', 'autocomplete' => 'off')); ?>

			<!-- SOP Title -->
			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('title')?> <span class="required">*</span></label>
				<div class="col-md-8">
					<input type="text" class="form-control" name="title" required />
					<span class="error"></span>
				</div>
			</div>

			<!-- Task Purpose -->
			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('task_purpose')?> <span class="required">*</span></label>
				<div class="col-md-8">
					<textarea name="task_purpose" class="form-control" rows="2" required></textarea>
					<span class="error"></span>
				</div>
			</div>

			<!-- Instructions -->
			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('instructions')?> <span class="required">*</span></label>
				<div class="col-md-8">
					<textarea name="instructions" class="summernote form-control" id="instructions" rows="3" required></textarea>
					<span class="error"></span>
				</div>
			</div>

			<!-- Proof Required (Checkboxes in one line) -->
			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('proof_required')?> <span class="required">*</span></label>
				<div class="col-md-8">
					<div class="checkbox-inline">
						<label><input type="checkbox" name="proof_required_text" value="1" class="proof-required"> <?=translate('text')?></label>
					</div>
					<div class="checkbox-inline">
						<label><input type="checkbox" name="proof_required_image" value="1" class="proof-required"> <?=translate('image')?></label>
					</div>
					<div class="checkbox-inline">
						<label><input type="checkbox" name="proof_required_file" value="1" class="proof-required"> <?=translate('file')?></label>
					</div>
					<span class="error" id="proof-required-error" style="color:red;display:none;"></span>
				</div>
			</div>

			<!-- Verifier Role -->
			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('verifier_role')?></label>

				 <div class="col-md-8">
                <?php
					$array = $this->app_lib->getSelectList('roles');
					// Remove ID 1(superadmin) from the array
					$keysToUnset = [1,2,4,9];
					foreach ($keysToUnset as $key) {
						unset($array[$key]);
					}

					echo form_dropdown("verifier_role[]", $array, array(), "class='form-control' id='verifier_role' required multiple data-plugin-selectTwo data-placeholder=\"Select\" data-width='100%' "
					);
				?>

                    <span class="error"><?= form_error('verifier_role[]') ?></span>
                </div>
			</div>

			<!-- Expected Time -->
			<div class="form-group">
				<label class="col-md-3 control-label"><?=translate('expected_duration')?></label>
				<div class="col-md-8">
					<input type="text" name="expected_time" class="form-control" placeholder="e.g. 2 hours" />
				</div>
			</div>

			<!-- Submit Button -->
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-offset-3 col-md-2">
						<button type="submit" class="btn btn-default btn-block">
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

<!-- Advance Salary View Modal -->
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
	<section class="panel" id='quick_view'></section>
</div>



<script type="text/javascript">
	function getDetailed(id) {
	    $.ajax({
	        url: base_url + 'sop/getDetailed',
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
