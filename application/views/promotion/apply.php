<div class="row">
    <div class="col-md-12">
        <section class="panel" style="border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border-radius: 10px; background: #ffffff;">
            <header class="panel-heading" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 10px 10px 0 0; border: none; border-bottom: 3px solid #007bff;">
                <h4 class="panel-title" style="color: #495057;  font-weight: 600; margin: 0;">
                    <i class="fas fa-arrow-up" style="margin-right: 12px; color: #007bff;"></i>
                    <?=translate('Submit your promotion application with confidence')?>
                </h4>
            </header>
            <?php echo form_open('promotion/apply', array('class' => 'form-horizontal form-bordered validate')); ?>
            <div class="panel-body" style="padding: 40px; background: #fafbfc;">
                <div class="form-group" style="margin-bottom: 25px;">
                    <label class="col-md-3 control-label" style="font-weight: 600; color: #495057; padding-top: 10px;">
                        <i class="fas fa-user-tie text-primary" style="margin-right: 8px;"></i>
                        <?=translate('current_designation')?>
                    </label>
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-addon" style="background: #e9ecef; border-color: #ced4da;">
                                <i class="fas fa-briefcase"></i>
                            </span>
                            <input type="text" class="form-control" value="<?=$staff_info['designation_name']?>" readonly 
                                   style="background: #f8f9fa; border-color: #ced4da; font-weight: 500;">
                        </div>
                        <input type="hidden" name="current_designation" value="<?=$staff_info['designation']?>">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label class="col-md-3 control-label" style="font-weight: 600; color: #495057; padding-top: 10px;">
                        <i class="fas fa-bullseye text-success" style="margin-right: 8px;"></i>
                        <?=translate('target_designation')?> 
                        <span class="required">*</span>
                    </label>
                    <div class="col-md-8">
                        <?php
                        $designation_array = array('' => translate('select'));
                        foreach($designations as $designation) {
                            $designation_array[$designation['id']] = $designation['name'];
                        }
                        echo form_dropdown(
                            "target_designation",
                            $designation_array,
                            '',
                            "class='form-control' id='target_designation' data-plugin-selectTwo data-width='100%' data-placeholder='" . translate('select') . "' required style='border-color: #ced4da;'"
                        );
                        ?>
                        <small class="text-muted" style="margin-top: 5px; display: block;">
                            Choose the position you want to be promoted to
                        </small>
                    </div>
                </div>

                <div id="responsibilities_section" style="display: none; margin-bottom: 25px;">
                    <div class="form-group">
                        <label class="col-md-3 control-label" style="font-weight: 600; color: #495057; padding-top: 10px;">
                            <i class="fas fa-tasks text-info" style="margin-right: 8px;"></i>
                            <?=translate('eligible_responsibilities')?> 
                            <span class="required">*</span>
                        </label>
                        <div class="col-md-8">
                            <div id="responsibilities_list"></div>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label class="col-md-3 control-label" style="font-weight: 600; color: #495057; padding-top: 10px;">
                        <i class="fas fa-pen-fancy" style="color: #6f42c1; margin-right: 8px;"></i>
                        <?=translate('summary_reason')?> 
                        <span class="required">*</span>
                    </label>
                    <div class="col-md-8">
                        <textarea name="summary_reason" class="form-control" rows="6" 
                                  placeholder="<?=translate('explain_why_you_deserve_promotion')?>" required
                                  style="border-color: #ced4da; resize: vertical;"></textarea>
                        <small class="text-muted" style="margin-top: 5px; display: block;">
                            Highlight your achievements and contributions
                        </small>
                    </div>
                </div>
            </div>
            <footer class="panel-footer text-center" style="background: #ffffff; border-top: 1px solid #e9ecef; padding: 30px; border-radius: 0 0 10px 10px;">
                <button type="submit" class="btn btn-primary btn-lg" name="submit" value="save" 
                        style="padding: 12px 30px; font-weight: 600; border-radius: 6px; box-shadow: 0 2px 10px rgba(0,123,255,0.3);">
                    <i class="fas fa-paper-plane"></i> <?=translate('submit_application')?>
                </button>
                <a href="<?=base_url('promotion')?>" class="btn btn-default btn-lg ml-sm" 
                   style="padding: 12px 30px; font-weight: 600; border-radius: 6px; background: #6c757d; border-color: #6c757d; color: white;">
                    <i class="fas fa-arrow-left"></i> <?=translate('cancel')?>
                </a>
            </footer>
            <?php echo form_close(); ?>
        </section>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#target_designation').change(function() {
        var currentDesignation = $('input[name="current_designation"]').val();
        var targetDesignation = $(this).val();
        
        if (targetDesignation) {
            $.ajax({
                url: '<?=base_url("promotion/get_eligible_responsibilities")?>',
                type: 'POST',
                data: {
                    current_designation: currentDesignation,
                    target_designation: targetDesignation
                },
                dataType: 'json',
                success: function(response) {
                    if (response.length > 0) {
                        var html = '<div style="background: #e3f2fd; border: 1px solid #bbdefb; border-radius: 6px; padding: 15px; margin-bottom: 20px;"><small class="text-muted"><i class="fas fa-info-circle" style="color: #1976d2;"></i> <?=translate("select_responsibilities_you_can_handle")?></small></div>';
                        $.each(response, function(index, responsibility) {
                            html += '<div class="panel" style="border: 1px solid #e9ecef; border-radius: 8px; margin-bottom: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">';
                            html += '<div class="panel-heading" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef; border-radius: 8px 8px 0 0;">';
                            html += '<div class="checkbox" style="margin: 0;">';
                            html += '<label style="font-weight: 600; color: #495057;">';
                            html += '<input type="checkbox" name="responsibilities[]" value="' + responsibility.id + '" required style="margin-right: 10px;"> ';
                            html += '<i class="fas fa-clipboard-check" style="color: #28a745; margin-right: 8px;"></i>';
                            html += responsibility.title;
                            html += '</label>';
                            html += '</div>';
                            html += '</div>';
                            html += '<div class="panel-body" style="background: #ffffff;">';
                            if (responsibility.description) {
                                html += '<div style="background: #f8f9fa; border-left: 3px solid #17a2b8; padding: 12px; margin-bottom: 15px;"><p class="text-muted" style="margin: 0;"><i class="fas fa-quote-left" style="color: #17a2b8;"></i> ' + responsibility.description + '</p></div>';
                            }
                            html += '<div class="form-group mb-none" style="margin: 5px;">';
                            html += '<label style="font-weight: 600; color: #495057;"><i class="fas fa-edit" style="color: #6f42c1; margin-right: 5px;"></i> <?=translate("remarks")?> <span class="required">*</span></label>';
                            html += '<textarea name="remarks[' + responsibility.id + ']" class="form-control" rows="3" placeholder="<?=translate("explain_why_you_are_eligible")?>" required style="border-color: #ced4da; resize: vertical;"></textarea>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                        });
                        $('#responsibilities_list').html(html);
                        $('#responsibilities_section').show();
                    } else {
                        $('#responsibilities_list').html('<div class="alert alert-info"><i class="fas fa-info-circle"></i> <?=translate("no_new_responsibilities_for_this_promotion")?></div>');
                        $('#responsibilities_section').show();
                    }
                }
            });
        } else {
            $('#responsibilities_section').hide();
        }
    });
});
</script>