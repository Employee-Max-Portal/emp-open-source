<section class="panel">
    <div class="tabs-custom">
        <ul class="nav nav-tabs">
            <li>
                <a href="<?= base_url('event/index') ?>">
                    <i class="fas fa-list-ul"></i> <?= translate('event_list') ?>
                </a>
            </li>
            <li class="active">
                <a href="#add" data-toggle="tab">
                    <i class="far fa-edit"></i> <?= translate('edit_event') ?>
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane active" id="add">
                <?= form_open_multipart($this->uri->uri_string(), ['class' => 'form-bordered form-horizontal frm-submit-data']); ?>
                    <input type="hidden" name="id" value="<?= $event['id'] ?>">

                    <div class="form-group">
                        <label class="col-md-3 control-label"><?= translate('title') ?> <span class="required">*</span></label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="title" value="<?= $event['title'] ?>" />
                            <span class="error"></span>
                        </div>
                    </div>

					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('event_type') ?> <span class="required">*</span></label>
						<div class="col-md-6">
							<select class="form-control" name="type_id" id="event_type_select" required>
								<option value="holiday" <?= $event['type'] == 'holiday' ? 'selected' : '' ?>><?= translate('holiday') ?></option>
								<option value="meeting" <?= $event['type'] == 'meeting' ? 'selected' : '' ?>><?= translate('meeting') ?></option>
								<option value="training" <?= $event['type'] == 'training' ? 'selected' : '' ?>><?= translate('training') ?></option>
								<option value="celebration" <?= $event['type'] == 'celebration' ? 'selected' : '' ?>><?= translate('celebration') ?></option>
								<option value="client_visit" <?= $event['type'] == 'client_visit' ? 'selected' : '' ?>><?= translate('client_visit') ?></option>
								<option value="deadline" <?= $event['type'] == 'deadline' ? 'selected' : '' ?>><?= translate('deadline') ?></option>
								<option value="workshop" <?= $event['type'] == 'workshop' ? 'selected' : '' ?>><?= translate('workshop') ?></option>
								<option value="team_outing" <?= $event['type'] == 'team_outing' ? 'selected' : '' ?>><?= translate('team_outing') ?></option>
								<option value="others" <?= $event['type'] == 'others' ? 'selected' : '' ?>><?= translate('others') ?></option>
							</select>
						</div>
					</div>
		
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?= translate('date') ?> <span class="required">*</span></label>
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
                                <input type="text" class="form-control" name="daterange" id="daterange" value="<?= set_value('daterange', $event['start_date'] . ' - ' . $event['end_date']) ?>" />
                            </div>
                            <span class="error"></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label"><?= translate('description') ?></label>
                        <div class="col-md-6">
                            <textarea name="remarks" class="summernote"><?= $event['remark'] ?></textarea>
                        </div>
                    </div>

                    <footer class="panel-footer">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-2">
                                <button type="submit" class="btn btn-default btn-block" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
                                    <i class="fas fa-plus-circle"></i> <?= translate('update') ?>
                                </button>
                            </div>
                        </div>
                    </footer>
                <?= form_close(); ?>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
    $(document).ready(function () {
        $('#daterange').daterangepicker({
            opens: 'left',
            locale: { format: 'YYYY/MM/DD' }
        });

        $('#branch_id').on('change', function () {
            const branchID = $(this).val();
            $.post("<?= base_url('ajax/getDataByBranch') ?>", {
                branch_id: branchID,
                table: 'event_types'
            }, function (data) {
                $('#type_id').html(data);
            });
        });

        $('#chk_holiday').on('change', function () {
            if ($(this).is(':checked')) {
                $('#typeDiv').hide('slow');
            } else {
                $('#typeDiv').show('slow');
            }
        });
    });
</script>
