<section class="panel">
    <div class="panel-heading">
        <h4 class="panel-title"><i class="fas fa-plus-circle"></i> <?= translate('issue_warning'); ?></h4>
    </div>
    <?php echo form_open_multipart('todo/save_warning', ['class' => 'form-horizontal', 'method' => 'POST']); ?>
    <div class="panel-body">

        <!-- 🔖 Refrence -->
        <div class="form-group">
            <label class="col-md-3 control-label"><?= translate('refrence') ?></label>
            <div class="col-md-9">
                <input type="text" class="form-control" id="refrence" name="refrence" value="WRN-<?= time() ?>">
                <span class="error"><?php echo form_error('refrence'); ?></span>
            </div>
        </div>

        <!-- 📂 Category Dropdown -->
        <div class="form-group">
            <label class="col-md-3 control-label"><?= translate('category') ?></label>
            <div class="col-md-9">
                <select class="form-control" name="category" id="category" onchange="toggleCustomCategory(this)">
                    <option value="">Select Category</option>
                    <option value="Violation">Violation</option>
                    <option value="Dispute">Dispute</option>
                    <option value="Pending Jobs">Pending Jobs</option>
                    <option value="others">Others</option>
                </select>
                <input type="text" class="form-control mt-2" id="category_other" name="category_other" placeholder="Enter custom category" style="display:none;">
                <span class="error"><?php echo form_error('category'); ?></span>
            </div>
        </div>

        <!-- ⚠️ Effect Dropdown -->
        <div class="form-group">
            <label class="col-md-3 control-label"><?= translate('effect') ?></label>
            <div class="col-md-9">
                <select class="form-control" name="effect" id="effect" onchange="toggleCustomEffect(this)">
                    <option value="">Select Effect</option>
                    <option value="User">User</option>
                    <option value="Multiple User">Multiple User</option>
                    <option value="Business">Business</option>
                    <option value="Customer">Customer</option>
                    <option value="others">Others</option>
                </select>
                <input type="text" class="form-control mt-2" id="effect_other" name="effect_other" placeholder="Enter custom effect" style="display:none;">
                <span class="error"><?php echo form_error('effect'); ?></span>
            </div>
        </div>

        <!-- 👤 Applicant -->
        <div class="form-group">
            <label class="col-md-3 control-label"><?= translate('applicant') ?> <span class="required">*</span></label>
            <div class="col-md-9">
                <?php
				
                $array = $staff_list ?? $this->app_lib->getSelectList('staff');
                unset($array[1]); // remove superadmin
                echo form_dropdown(
                    "applicant_id[]",
                    $array,
                    isset($default_user_id) ? [$default_user_id] : [],
                    "class='form-control' id='applicant_id' multiple data-plugin-selectTwo data-placeholder='Select' data-width='100%'"
                );
                ?>
                <span class="error"></span>
            </div>
        </div>

        <!-- 📋 Reason -->
        <div class="form-group">
            <label class="col-md-3 control-label"><?= translate('description') ?></label>
            <div class="col-md-9">
                <textarea class="form-control" id="reason" name="reason" rows="3"><?= isset($default_reason) ? html_escape($default_reason) : '' ?></textarea>
                <span class="error"><?php echo form_error('reason'); ?></span>
            </div>
        </div>

        <!-- 📎 Attachment -->
        <div class="form-group">
            <label class="col-md-3 control-label"><?= translate('attachment') ?></label>
            <div class="col-md-9">
                <input type="file" name="attachment_file" id="attachment_file" class="dropify" data-height="80" />
                <span class="error"></span>
            </div>
        </div>

        <!-- ⏰ Deadline -->
        <div class="form-group">
            <label class="col-md-3 control-label">Deadline <span class="required">*</span></label>
            <div class="col-md-9">
                <?php
                echo form_dropdown(
                    "clearance_time",
                    [
                        '' => 'Select Deadline',
                        '24' => '24 Hours',
                        '48' => '48 Hours',
                        '72' => '72 Hours',
                    ],
                    set_value('clearance_time'),
                    "class='form-control' id='clearance_time' data-plugin-selectTwo data-width='100%'"
                );
                ?>
                <span class="error"></span>
            </div>
        </div>

    </div>

    <!-- 🔘 Footer Buttons -->
    <footer class="panel-footer">
        <div class="row">
            <div class="col-md-12 text-right">
                <button type="submit" class="btn btn-default mr-xs" id="savebtn">
                    <i class="fas fa-plus-circle"></i> <?= translate('apply') ?>
                </button>
                <button type="button" class="btn btn-default modal-dismiss"><?= translate('cancel') ?></button>
            </div>
        </div>
    </footer>

    <?php echo form_close(); ?>
</section>

<!-- ✅ Custom JS for dynamic input display -->
<script>
function toggleCustomCategory(select) {
    document.getElementById('category_other').style.display = select.value === 'others' ? 'block' : 'none';
}
function toggleCustomEffect(select) {
    document.getElementById('effect_other').style.display = select.value === 'others' ? 'block' : 'none';
}
</script>
