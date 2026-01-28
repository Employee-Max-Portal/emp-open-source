<section class="panel">
    <div class="tabs-custom">
    <ul class="nav nav-tabs">
        <li class="<?php echo (!isset($validation_error) ? 'active' : ''); ?>">
            <a href="#config" data-toggle="tab"><i class="fas fa-list-ul"></i> <?php echo translate('Email Configuration'); ?></a>
        </li>
        <li class="<?php echo (isset($validation_error) ? 'active' : ''); ?>">
            <a href="#create" data-toggle="tab"><i class="far fa-envelope"></i> <?php echo translate('send') . " " . translate('email'); ?></a>
        </li>
    </ul>
        <div class="tab-content">
			<div id="config" class="tab-pane <?php echo (!isset($validation_error) ? 'active' : ''); ?>">

                <?php echo form_open(base_url('email/config'), array('class' => 'form-horizontal form-bordered')); ?>
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo translate('email'); ?> <span class="required">*</span></label>
                        <div class="col-md-6">
                            <input type="email" class="form-control" name="email" value="<?php echo set_value('email', isset($config['email']) ? $config['email'] : ''); ?>" />
                            <span class="error"><?php echo form_error('email'); ?></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo translate('protocol'); ?> <span class="required">*</span></label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="protocol" value="<?php echo set_value('protocol', isset($config['protocol']) ? $config['protocol'] : 'smtp'); ?>" />
                            <span class="error"><?php echo form_error('protocol'); ?></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo translate('smtp_host'); ?> <span class="required">*</span></label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="smtp_host" value="<?php echo set_value('smtp_host', isset($config['smtp_host']) ? $config['smtp_host'] : ''); ?>" />
                            <span class="error"><?php echo form_error('smtp_host'); ?></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo translate('smtp_user'); ?> <span class="required">*</span></label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="smtp_user" value="<?php echo set_value('smtp_user', isset($config['smtp_user']) ? $config['smtp_user'] : ''); ?>" />
                            <span class="error"><?php echo form_error('smtp_user'); ?></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo translate('smtp_pass'); ?> <span class="required">*</span></label>
                        <div class="col-md-6">
                            <input type="password" class="form-control" name="smtp_pass" value="<?php echo set_value('smtp_pass', isset($config['smtp_pass']) ? $config['smtp_pass'] : ''); ?>" />
                            <span class="error"><?php echo form_error('smtp_pass'); ?></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo translate('smtp_port'); ?> <span class="required">*</span></label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="smtp_port" value="<?php echo set_value('smtp_port', isset($config['smtp_port']) ? $config['smtp_port'] : '587'); ?>" />
                            <span class="error"><?php echo form_error('smtp_port'); ?></span>
                        </div>
                    </div>
                    <footer class="panel-footer">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-2">
                                <button type="submit" class="btn btn-default btn-block">
                                    <i class="fas fa-plus-circle"></i> <?php echo translate('save'); ?>
                                </button>
                            </div>
                        </div>
                    </footer>
                <?php echo form_close(); ?>
            </div>
			
			<div class="tab-pane <?php echo (isset($validation_error) ? 'active' : ''); ?>" id="create">
            <?php echo form_open('email/send', array('class' => 'form-horizontal', 'enctype' => 'multipart/form-data')); ?>

                <!-- From Email Field -->
                <div class="form-group <?php if (form_error('from_email')) echo 'has-error'; ?>">
                    <label class="col-md-3 control-label"><?php echo translate('from') . " " . translate('email'); ?> <span class="required">*</span></label>
                    <div class="col-md-6 mb-sm">
                        <select class="form-control" name="from_email">
                            <?php 
                            if(isset($config['email'])) {
                                echo '<option value="'.$config['email'].'">'.$config['email'].'</option>';
                            }
                            ?>
                        </select>
                        <span class="error"><?php echo form_error('from_email'); ?></span>
                    </div>
                </div>

                <!-- To Email Field -->
				<div class="form-group <?php echo (form_error('to_email')) ? 'has-error' : ''; ?>">
					<label class="col-md-3 control-label">
						<?php echo translate('to') . " " . translate('email'); ?> <span class="required">*</span>
					</label>
					<div class="col-md-6 mb-sm">
						<?php
						$options = [
							'tech@emp.com.bd' => 'Tech (tech@emp.com.bd)',
							'hello@emp.com.bd' => 'EMP (hello@emp.com.bd)',
						];

						foreach ($staff_emails as $staff) {
							$options[$staff->email] = $staff->name . ' (' . $staff->email . ')';
						}

						echo form_dropdown(
							'to_email[]',
							$options,
							set_value('to_email'),
							"class='form-control' data-plugin-selectTwo data-width='100%' multiple='multiple'"
						);
						?>

						<span class="error"><?php echo form_error('to_email'); ?></span>
					</div>
				</div>

                <!-- Subject Field -->
                <div class="form-group <?php if (form_error('subject')) echo 'has-error'; ?>">
                    <label class="col-md-3 control-label"><?php echo translate('subject'); ?> <span class="required">*</span></label>
                    <div class="col-md-6 mb-sm">
                        <input type="text" class="form-control" name="subject" value="<?php echo set_value('subject'); ?>" placeholder="Enter email subject">
                        <span class="error"><?php echo form_error('subject'); ?></span>
                    </div>
                </div>

                <!-- Message Body Field -->
                <div class="form-group <?php if (form_error('message')) echo 'has-error'; ?>">
                    <label class="col-md-3 control-label"><?php echo translate('message'); ?> <span class="required">*</span></label>
                    <div class="col-md-6 mb-sm">
                        <textarea class="form-control" name="message" rows="5" placeholder="Enter your message"><?php echo set_value('message'); ?></textarea>
                        <span class="error"><?php echo form_error('message'); ?></span>
                    </div>
                </div>

                <!-- Attachment Field (Optional) -->
                <div class="form-group">
                    <label class="col-md-3 control-label"><?php echo translate('attachment'); ?></label>
                    <div class="col-md-6 mb-sm">
                        <input type="file" class="form-control" name="attachment">
                    </div>
                </div>

                <footer class="panel-footer mt-lg">
                    <div class="row">
                        <div class="col-md-2 col-md-offset-3">
                            <button type="submit" name="send_email" value="1" class="btn btn-default btn-block">
                                <i class="fas fa-paper-plane"></i> <?php echo translate('send'); ?>
                            </button>
                        </div>
                    </div>    
                </footer>
            <?php echo form_close(); ?>
        </div>
        </div>
    </div>
</section>
