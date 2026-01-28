<section class="panel">
    <div class="tabs-custom">
        <ul class="nav nav-tabs">
            <li>
                <a href="<?php echo base_url('team_meetings'); ?>">
                    <i class="fas fa-list"></i> <?php echo translate('team_meetings'); ?>
                </a>
            </li>
            <li class="active">
                <a href="#add" data-toggle="tab">
                    <i class="fas fa-plus-circle"></i> <?php echo translate('add_meeting'); ?>
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane box active" id="add">
                <?php echo form_open_multipart('team_meetings/add', array('class' => 'form-horizontal form-bordered validate')); ?>
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo translate('title'); ?> <span class="required">*</span></label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="title" value="<?php echo set_value('title'); ?>" required />
                            <span class="error"><?php echo form_error('title'); ?></span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo translate('date'); ?> <span class="required">*</span></label>
                        <div class="col-md-6">
                            <input type="date" class="form-control" name="date" value="<?php echo set_value('date'); ?>" required />
                            <span class="error"><?php echo form_error('date'); ?></span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo translate('meeting_type'); ?> <span class="required">*</span></label>
                        <div class="col-md-6">
                            <select name="meeting_type" class="form-control" required>
                                <option value=""><?php echo translate('select'); ?></option>
                                <option value="public" <?php echo set_select('meeting_type', 'public'); ?>>
                                    <?php echo translate('public'); ?>
                                </option>
                                <option value="management" <?php echo set_select('meeting_type', 'management'); ?>>
                                    <?php echo translate('management'); ?>
                                </option>
                            </select>
                            <span class="error"><?php echo form_error('meeting_type'); ?></span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo translate('participants'); ?></label>
                        <div class="col-md-6">
                            <select name="participants[]" class="form-control" multiple data-plugin-multiselect 
                                    data-plugin-options='{"enableFiltering": true, "enableCaseInsensitiveFiltering": true}'>
                                <?php foreach ($staff_list as $staff): ?>
                                <option value="<?php echo $staff->id; ?>">
                                    <?php echo $staff->name . ' (' . $staff->designation_name . ')'; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo translate('summary'); ?> <span class="required">*</span></label>
                        <div class="col-md-8">
                            <textarea name="summary" class="form-control summernote" rows="10" required><?php echo set_value('summary'); ?></textarea>
                            <span class="error"><?php echo form_error('summary'); ?></span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo translate('attachment'); ?></label>
                        <div class="col-md-6">
                            <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" />
                            <small class="help-block"><?php echo translate('allowed_file_types'); ?>: PDF, DOC, DOCX, JPG, PNG (Max: 5MB)</small>
                        </div>
                    </div>
                    
                    <footer class="panel-footer">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-2">
                                <button type="submit" class="btn btn btn-default btn-block">
                                    <i class="fas fa-plus-circle"></i> <?php echo translate('save'); ?>
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
$(document).ready(function() {
    $('.summernote').summernote({
        height: 200,
        minHeight: null,
        maxHeight: null,
        focus: false
    });
});
</script>