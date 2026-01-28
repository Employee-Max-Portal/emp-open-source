<section class="panel">
    <header class="panel-heading">
        <h4 class="panel-title"><i class="far fa-edit"></i> <?=translate('add') . " " . translate('probation')?></h4>
    </header>
    <?php echo form_open($this->uri->uri_string(), array('class' => 'form-horizontal form-bordered frm-submit')); ?>
    <div class="panel-body">
        <div class="form-group mt-md">
            <label class="col-md-3 control-label"><?=translate('employee')?> <span class="required">*</span></label>
            <div class="col-md-6">
                <select name="staff_id" class="form-control staff_id" id="staff_id" required>
                    <option value=""><?=translate('select')?></option>
                    <?php foreach ($staff_list as $staff): ?>
                    <option value="<?=$staff['id']?>"><?=$staff['name'] . " (" . $staff['staff_id'] . ")"?></option>
                    <?php endforeach; ?>
                </select>
                <span class="error"></span>
            </div>
        </div>
        
        <div id="employee_details" class="mt-sm"></div>
        <hr>
        
        <h4 class="mt-none text-dark"><?=translate('assessment_details')?></h4>
        <p class="mb-lg text-muted"><?=translate('make_detailed_assessment_for_probation_period')?></p>
        
        <div class="form-group">
            <label class="col-md-3 control-label"><?=translate('performance')?> <span class="required">*</span></label>
            <div class="col-md-6">
                <select name="performance_rating" class="form-control" required>
                    <option value=""><?=translate('select')?></option>
                    <option value="5"><?=translate('excellent')?></option>
                    <option value="4"><?=translate('good')?></option>
                    <option value="3"><?=translate('satisfactory')?></option>
                    <option value="2"><?=translate('poor')?></option>
                    <option value="1"><?=translate('unacceptable')?></option>
                </select>
                <span class="error"></span>
            </div>
        </div>
        
        <div class="form-group">
            <label class="col-md-3 control-label"><?=translate('analytical_skills')?> <span class="required">*</span></label>
            <div class="col-md-6">
                <select name="analytical_rating" class="form-control" required>
                    <option value=""><?=translate('select')?></option>
                    <option value="5"><?=translate('excellent')?></option>
                    <option value="4"><?=translate('good')?></option>
                    <option value="3"><?=translate('satisfactory')?></option>
                    <option value="2"><?=translate('poor')?></option>
                    <option value="1"><?=translate('unacceptable')?></option>
                </select>
                <span class="error"></span>
            </div>
        </div>
        
        <div class="form-group">
            <label class="col-md-3 control-label"><?=translate('work_attitude')?> <span class="required">*</span></label>
            <div class="col-md-6">
                <select name="work_attitude_rating" class="form-control" required>
                    <option value=""><?=translate('select')?></option>
                    <option value="5"><?=translate('excellent')?></option>
                    <option value="4"><?=translate('good')?></option>
                    <option value="3"><?=translate('satisfactory')?></option>
                    <option value="2"><?=translate('poor')?></option>
                    <option value="1"><?=translate('unacceptable')?></option>
                </select>
                <span class="error"></span>
            </div>
        </div>
        
        <div class="form-group">
            <label class="col-md-3 control-label"><?=translate('communication')?> <span class="required">*</span></label>
            <div class="col-md-6">
                <select name="communication_rating" class="form-control" required>
                    <option value=""><?=translate('select')?></option>
                    <option value="5"><?=translate('excellent')?></option>
                    <option value="4"><?=translate('good')?></option>
                    <option value="3"><?=translate('satisfactory')?></option>
                    <option value="2"><?=translate('poor')?></option>
                    <option value="1"><?=translate('unacceptable')?></option>
                </select>
                <span class="error"></span>
            </div>
        </div>
        
        <div class="form-group">
            <label class="col-md-3 control-label"><?=translate('deadline_management')?> <span class="required">*</span></label>
            <div class="col-md-6">
                <select name="deadline_rating" class="form-control" required>
                    <option value=""><?=translate('select')?></option>
                    <option value="5"><?=translate('excellent')?></option>
                    <option value="4"><?=translate('good')?></option>
                    <option value="3"><?=translate('satisfactory')?></option>
                    <option value="2"><?=translate('poor')?></option>
                    <option value="1"><?=translate('unacceptable')?></option>
                </select>
                <span class="error"></span>
            </div>
        </div>
        
        <div class="form-group">
            <label class="col-md-3 control-label"><?=translate('attendance')?> <span class="required">*</span></label>
            <div class="col-md-6">
                <select name="attendance_rating" class="form-control" required>
                    <option value=""><?=translate('select')?></option>
                    <option value="5"><?=translate('excellent')?></option>
                    <option value="4"><?=translate('good')?></option>
                    <option value="3"><?=translate('satisfactory')?></option>
                    <option value="2"><?=translate('poor')?></option>
                    <option value="1"><?=translate('unacceptable')?></option>
                </select>
                <span class="error"></span>
            </div>
        </div>
        
        <div class="form-group">
            <label class="col-md-3 control-label"><?=translate('core_values')?> <span class="required">*</span></label>
            <div class="col-md-6">
                <select name="core_values_rating" class="form-control" required>
                    <option value=""><?=translate('select')?></option>
                    <option value="5"><?=translate('excellent')?></option>
                    <option value="4"><?=translate('good')?></option>
                    <option value="3"><?=translate('satisfactory')?></option>
                    <option value="2"><?=translate('poor')?></option>
                    <option value="1"><?=translate('unacceptable')?></option>
                </select>
                <span class="error"></span>
            </div>
        </div>
        
        <hr>
        <h4 class="mt-none text-dark"><?=translate('managerial_leadership_aptitude')?></h4>
        
        <div class="form-group">
            <label class="col-md-3 control-label"><?=translate('initiative_creativity')?> <span class="required">*</span></label>
            <div class="col-md-6">
                <select name="initiative_rating" class="form-control" required>
                    <option value=""><?=translate('select')?></option>
                    <option value="5"><?=translate('excellent')?></option>
                    <option value="4"><?=translate('good')?></option>
                    <option value="3"><?=translate('satisfactory')?></option>
                    <option value="2"><?=translate('poor')?></option>
                    <option value="1"><?=translate('unacceptable')?></option>
                </select>
                <span class="error"></span>
            </div>
        </div>
        
        <div class="form-group">
            <label class="col-md-3 control-label"><?=translate('results_focus')?> <span class="required">*</span></label>
            <div class="col-md-6">
                <select name="results_rating" class="form-control" required>
                    <option value=""><?=translate('select')?></option>
                    <option value="5"><?=translate('excellent')?></option>
                    <option value="4"><?=translate('good')?></option>
                    <option value="3"><?=translate('satisfactory')?></option>
                    <option value="2"><?=translate('poor')?></option>
                    <option value="1"><?=translate('unacceptable')?></option>
                </select>
                <span class="error"></span>
            </div>
        </div>
        
        <div class="form-group">
            <label class="col-md-3 control-label"><?=translate('people_management')?> <span class="required">*</span></label>
            <div class="col-md-6">
                <select name="people_mgmt_rating" class="form-control" required>
                    <option value=""><?=translate('select')?></option>
                    <option value="5"><?=translate('excellent')?></option>
                    <option value="4"><?=translate('good')?></option>
                    <option value="3"><?=translate('satisfactory')?></option>
                    <option value="2"><?=translate('poor')?></option>
                    <option value="1"><?=translate('unacceptable')?></option>
                </select>
                <span class="error"></span>
            </div>
        </div>
        
        <div class="form-group">
            <label class="col-md-3 control-label"><?=translate('decision_making')?> <span class="required">*</span></label>
            <div class="col-md-6">
                <select name="decision_rating" class="form-control" required>
                    <option value=""><?=translate('select')?></option>
                    <option value="5"><?=translate('excellent')?></option>
                    <option value="4"><?=translate('good')?></option>
                    <option value="3"><?=translate('satisfactory')?></option>
                    <option value="2"><?=translate('poor')?></option>
                    <option value="1"><?=translate('unacceptable')?></option>
                </select>
                <span class="error"></span>
            </div>
        </div>
        
        <hr>
        <h4 class="mt-none text-dark"><?=translate('final_assessment')?></h4>
        
        <div class="form-group">
            <label class="col-md-3 control-label"><?=translate('overall_assessment')?> <span class="required">*</span></label>
            <div class="col-md-6">
                <select name="overall_assessment" class="form-control" required>
                    <option value=""><?=translate('select')?></option>
                    <option value="<?=translate('outstanding')?>"><?=translate('outstanding')?></option>
                    <option value="<?=translate('very_good')?>"><?=translate('very_good')?></option>
                    <option value="<?=translate('satisfactory')?>"><?=translate('satisfactory')?></option>
                    <option value="<?=translate('needs_improvement')?>"><?=translate('needs_improvement')?></option>
                    <option value="<?=translate('poor')?>"><?=translate('poor')?></option>
                </select>
                <span class="error"></span>