
<style>
/* Hidden scrollbar styles */
::-webkit-scrollbar {
	width: 0px;
	height: 0px;
	background: transparent;
}
* {
	scrollbar-width: none;
	-ms-overflow-style: none;
}
</style>
<style>
.content-header {
    background: white;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
}

.content-title {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.add-label-btn {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border: none;
    border-radius: 8px;
    padding: 12px 20px;
    color: white;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.add-label-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
    color: white;
}

.labels-table-container {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
}

.labels-table {
    margin: 0;
    border: none;
}

.labels-table thead th {
    background: #f8fafc;
    border: none;
    padding: 16px 20px;
    font-weight: 600;
    color: #374151;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e5e7eb;
}

.labels-table tbody td {
    padding: 16px 20px;
    border: none;
    border-bottom: 1px solid #f3f4f6;
    color: #6b7280;
    font-size: 14px;
}

.labels-table tbody tr:hover {
    background: #f9fafb;
}

.labels-table tbody tr:last-child td {
    border-bottom: none;
}

.action-btn {
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
    background: white;
    color: #6b7280;
    transition: all 0.2s ease;
    margin-right: 8px;
}

.action-btn:hover {
    background: #f3f4f6;
    color: #374151;
    border-color: #d1d5db;
}

.edit-btn:hover {
    background: #dbeafe;
    color: #1e40af;
    border-color: #93c5fd;
}

.delete-btn:hover {
    background: #fee2e2;
    color: #dc2626;
    border-color: #fca5a5;
}
</style>
<div class="row" style="height: calc(108vh);">
	<div class="col-md-3">
        <?php include 'sidebar.php'; ?>
    </div>
    <div class="col-md-9" style="height: 100%; overflow-y: auto;">
	<?php $labels = isset($labels) ? $labels : []; ?>

		<div class="panel">
			<header class="panel-heading d-flex justify-content-between align-items-center">
				<h4 class="panel-title"><?= translate('labels') ?></h4>
				<div class="panel-btn">
					<a href="javascript:void(0);" class="btn btn-sm btn-primary" onclick="getLabelAddModal()">
						<i class="fa fa-plus-circle"></i> <?= translate('add') ?>
					</a>
				</div>
			</header>

			<div class="panel-body">
				
        <div class="labels-table-container">
            <table class="table labels-table">
                <thead>
                    <tr>
                        <th><?= translate('label_name') ?></th>
                        <th><?= translate('description') ?></th>
                        <th><?= translate('slug') ?></th>
                        <th><?= translate('task_count') ?></th>
                        <th><?= translate('updated') ?></th>
                        <th><?= translate('action') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($labels as $label): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($label->name) ?></strong></td>
                        <td><?= htmlspecialchars($label->description) ?></td>
                        <td><code><?= htmlspecialchars($label->slug) ?></code></td>
                        <td style="text-align:center;"><span class="badge badge-primary"><?= $label->task_count ?></span></td>
                        <td><?= time_ago($label->updated_at) ?></td>
                        <td>
                            <button onclick="getLabelEdit(<?= $label->id ?>)" class="action-btn edit-btn" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <?php echo btn_delete('tracker/delete_label/' . $label->id, ['class' => 'action-btn delete-btn']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
			</div>
		</div>

		<!-- Edit Label Modal Container -->
		<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
			<section class="panel" id="quick_view"></section>
		</div>



		<!-- Add Label Modal Container -->
		<div id="labelAddModal" class="zoom-anim-dialog modal-block modal-block-primary mfp-hide">
			<section class="panel">
				<header class="panel-heading">
					<h4 class="panel-title"><i class="fas fa-plus-circle"></i> <?= translate('add_label') ?></h4>
				</header>
				<?php echo form_open('tracker/add_label', ['class' => 'form-horizontal frm-submit', 'data-url' => base_url('tracker/labels')]); ?>

				<div class="panel-body">
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('label_name') ?> <span class="required">*</span></label>
						<div class="col-md-8">
							<input type="text" class="form-control" name="name" required />
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('description') ?></label>
						<div class="col-md-8">
							<textarea name="description" class="form-control" rows="2"></textarea>
						</div>
					</div>
				</div>
				<footer class="panel-footer">
					<div class="row">
						<div class="col-md-12 text-right">
							<button type="submit" class="btn btn-default mr-xs" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
								<i class="fas fa-plus-circle"></i> <?= translate('add_label') ?>
							</button>
							<button class="btn btn-default modal-dismiss">Cancel</button>
						</div>
					</div>
				</footer>
				<?php echo form_close(); ?>
			</section>
		</div>
	</div>

<script type="text/javascript">
function getLabelEdit(id) {
    $.ajax({
        url: base_url + 'tracker/getLabelEdit',
        type: 'POST',
        data: { id: id },
        success: function(response) {
            $('#quick_view').html(response);
            mfp_modal('#modal');
        }
    });
}


function getLabelAddModal() {
    mfp_modal('#labelAddModal');
}
</script>