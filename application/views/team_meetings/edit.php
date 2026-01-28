<section class="panel">
    <div class="tabs-custom">
        <ul class="nav nav-tabs">
            <li>
                <a href="<?php echo base_url('team_meetings'); ?>">
                    <i class="fas fa-list"></i> <?php echo translate('team_meetings'); ?>
                </a>
            </li>
            <li class="active">
                <a href="#edit" data-toggle="tab">
                    <i class="fas fa-edit"></i> <?php echo translate('edit_meeting'); ?>
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane box active" id="edit">
                <?php echo form_open_multipart('team_meetings/edit/' . $meeting['id'], array('class' => 'form-horizontal form-bordered validate')); ?>
                    <div class="form-group">
						<label class="col-md-3 control-label"><?=translate('meeting_host')?> <span class="required">*</span></label>
						<div class="col-md-6">
							<?php
							$this->db->select('s.id, s.name');
							$this->db->from('staff AS s');
							$this->db->join('login_credential AS lc', 'lc.user_id = s.id');
							$this->db->where('lc.active', 1);   // only active users
							$this->db->where_not_in('lc.role', [1, 9]);   // exclude super admin, etc.
							$this->db->order_by('s.name', 'ASC');
							$query = $this->db->get();

							$staffArray = ['' => 'Select']; // <-- default first option
							foreach ($query->result() as $row) {
								$staffArray[$row->id] = $row->name;
							}
							echo form_dropdown("meeting_host", $staffArray, $meeting['meeting_host'], "class='form-control' id='meeting_host' required data-plugin-selectTwo data-placeholder=\"Select\" data-width='100%' ");
							?>
										
							<span class="error"></span>
						</div>
					</div>
					
					<div class="form-group">
                        <label class="col-md-3 control-label"><?php echo translate('title'); ?> <span class="required">*</span></label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="title" 
                                   value="<?php echo set_value('title', $meeting['title']); ?>" required />
                            <span class="error"><?php echo form_error('title'); ?></span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo translate('date'); ?> <span class="required">*</span></label>
                        <div class="col-md-6">
                            <input type="date" class="form-control" name="date" 
                                   value="<?php echo set_value('date', $meeting['date']); ?>" required />
                            <span class="error"><?php echo form_error('date'); ?></span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo translate('meeting_type'); ?> <span class="required">*</span></label>
                        <div class="col-md-6">
                            <select name="meeting_type" class="form-control" required>
                                <option value=""><?php echo translate('select'); ?></option>
                                <option value="public" <?php echo set_select('meeting_type', 'public', ($meeting['meeting_type'] == 'public')); ?>>
                                    <?php echo translate('public'); ?>
                                </option>
                                <option value="management" <?php echo set_select('meeting_type', 'management', ($meeting['meeting_type'] == 'management')); ?>>
                                    <?php echo translate('management'); ?>
                                </option>
                            </select>
                            <span class="error"><?php echo form_error('meeting_type'); ?></span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo translate('participants'); ?></label>
                        <div class="col-md-6">
                            <?php $selected_participants = !empty($meeting['participants']) ? explode(',', $meeting['participants']) : array(); ?>
                           <?php
						$this->db->select('s.id, s.name');
						$this->db->from('staff AS s');
						$this->db->join('login_credential AS lc', 'lc.user_id = s.id');
						$this->db->where('lc.active', 1);   // only active users
						$this->db->where_not_in('lc.role', [1, 9]);   // exclude super admin, etc.
						$this->db->order_by('s.name', 'ASC');
						$query = $this->db->get();

						$staffArray = [];
						foreach ($query->result() as $row) {
							$staffArray[$row->id] = $row->name;
						}
                        echo form_dropdown("staff_id[]", $staffArray, $selected_participants, "class='form-control' id='staff_id' multiple data-plugin-selectTwo data-placeholder=\"Select Participants\" data-width='100%' "
					);
						
						?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo translate('meeting_minutes'); ?> <span class="required">*</span></label>
                        <div class="col-md-6">
                            <textarea name="summary" class="form-control summernote" rows="10" required><?php echo set_value('summary', $meeting['summary']); ?></textarea>
                            <span class="error"><?php echo form_error('summary'); ?></span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo translate('attachment'); ?></label>
                        <div class="col-md-6">
                            <?php if (!empty($meeting['attachments'])): ?>
                                <div class="mb-sm">
                                    <strong><?php echo translate('current_file'); ?>:</strong> 
                                    <a href="<?php echo base_url('team_meetings/download/' . $meeting['id']); ?>" target="_blank">
                                        <?php echo $meeting['attachments']; ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="attachment" class="dropify" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" />
                            <small class="help-block"><?php echo translate('allowed_file_types'); ?>: PDF, DOC, DOCX, JPG, PNG (Max: 5MB)</small>
                        </div>
                    </div>
                    
                    <footer class="panel-footer">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-2">
                                <button type="submit" class="btn btn btn-default btn-block">
                                    <i class="fas fa-save"></i> <?php echo translate('update'); ?>
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