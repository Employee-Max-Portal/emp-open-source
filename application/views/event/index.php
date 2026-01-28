<div class="row">
    <!-- Event List and Add Form -->
    <div class="col-md-7">
        <section class="panel">
            <div class="tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#list" data-toggle="tab"><i class="fas fa-list-ul"></i> <?= translate('event_list') ?></a>
                    </li>
                    <?php if (get_permission('event', 'is_add')): ?>
                        <li>
                            <a href="#add" data-toggle="tab"><i class="far fa-edit"></i> <?= translate('create_event') ?></a>
                        </li>
                    <?php endif; ?>
                </ul>

                <div class="tab-content">
                    <!-- Event List Tab -->
                    <div class="tab-pane box active mb-md" id="list">
                        <table class="table table-bordered table-hover table-export">
                            <thead>
                                <tr>
                                    <th><?= translate('SL.') ?></th>
                                    <th><?= translate('title') ?></th>
                                    <th><?= translate('date') ?></th>
                                    <th><?= translate('created_by') ?></th>
                                    <th><?= translate('action') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $count = 1;
                                $branch_id = get_loggedin_branch_id();
                                $this->db->order_by('id', 'asc');
                                foreach ($this->db->get('event')->result() as $event): ?>
                                    <tr>
                                        <td><?= $count++ ?></td>
                                        <td><?= $event->title; ?></td>
                                        <td>
                                            <strong><?= translate('from') ?>:</strong> <?= date('j F, Y', strtotime($event->start_date)); ?>
											<br>
                                            <strong><?= translate('to') ?>:</strong> <?= date('j F, Y', strtotime($event->end_date)); ?>

                                        </td>
                                        <td><?= get_type_name_by_id('staff', $event->created_by); ?></td>
                                        <td class="action">
                                            <a href="javascript:void(0);" class="btn btn-circle btn-default icon" onclick="viewEvent('<?= $event->id ?>');"><i class="far fa-eye"></i></a>
                                            <?php if (get_permission('event', 'is_edit')): ?>
                                                <a href="<?= base_url('event/edit/' . $event->id); ?>" class="btn btn-circle btn-default icon"><i class="fas fa-pen-nib"></i></a>
                                            <?php endif; ?>
                                            <?php if (get_permission('event', 'is_delete')): ?>
                                                <?= btn_delete('event/delete/' . $event->id); ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Create Event Tab -->
                    <?php if (get_permission('event', 'is_add')): ?>
                        <div class="tab-pane" id="add">
                            <?= form_open_multipart($this->uri->uri_string(), ['class' => 'form-bordered form-horizontal frm-submit-data']); ?>
       
                                <div class="form-group">
                                    <label class="control-label col-md-3"><?= translate('title') ?> <span class="required">*</span></label>
                                    <div class="col-md-6">
                                        <input type="text" name="title" class="form-control" value="" />
                                        <span class="error"></span>
                                    </div>
                                </div>
											
								<div class="form-group">
									<label class="col-md-3 control-label"><?= translate('event_type') ?></label>
									<div class="col-md-6">
										<select class="form-control" name="type_id" id="event_type_select">
											<option value="holiday"><?= translate('holiday') ?></option>
											<option value="meeting"><?= translate('meeting') ?></option>
											<option value="training"><?= translate('training') ?></option>
											<option value="celebration"><?= translate('celebration') ?></option>
											<option value="client_visit"><?= translate('client_visit') ?></option>
											<option value="deadline"><?= translate('deadline') ?></option>
											<option value="workshop"><?= translate('workshop') ?></option>
											<option value="team_outing"><?= translate('team_outing') ?></option>
											<option value="others"><?= translate('others') ?></option>
										</select>
									</div>
								</div>

								<div class="form-group" id="other_event_type" style="display: none;">
									<label class="col-md-3 control-label"><?= translate('please_specify') ?></label>
									<div class="col-md-6">
										<input type="text" class="form-control" name="custom_event_type" id="custom_event_type" placeholder="<?= translate('type_your_event_here') ?>">
									</div>
								</div>

								<style>
								#other_event_type {
									display: none;
								}

								</style>
								
                                <div class="form-group">
                                    <label class="control-label col-md-3"><?= translate('date') ?> <span class="required">*</span></label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
                                            <input type="text" name="daterange" id="daterange" class="form-control" value="<?= set_value('daterange', date("Y/m/d") . ' - ' . date("Y/m/d", strtotime("+2 days"))); ?>" />
                                        </div>
                                        <span class="error"></span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-3"><?= translate('description') ?></label>
                                    <div class="col-md-6">
                                        <textarea name="remarks" class="summernote"></textarea>
                                    </div>
                                </div>

                                <footer class="panel-footer">
                                    <div class="row">
                                        <div class="col-md-offset-3 col-md-2">
                                            <button type="submit" class="btn btn-default btn-block" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
                                                <i class="fas fa-plus-circle"></i> <?= translate('save') ?>
                                            </button>
                                        </div>
                                    </div>
                                </footer>
                            <?= form_close(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>

    <!-- Event Calendar -->
    <div class="col-md-5">
        <section class="panel">
            <div class="panel-body">
                <div id="event_calendar"></div>
            </div>
        </section>
    </div>
</div>

<!-- Modal -->
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
    <section class="panel">
        <header class="panel-heading">
            <div class="panel-btn">
                <button onclick="fn_printElem('printResult')" class="btn btn-default btn-circle icon"><i class="fas fa-print"></i></button>
            </div>
            <h4 class="panel-title"><i class="fas fa-info-circle"></i> <?= translate('event_details') ?></h4>
        </header>
        <div class="panel-body">
            <div id="printResult" class="pt-sm pb-sm">
                <div class="table-responsive">
                    <table class="table table-bordered table-condensed text-dark tbr-top" id="ev_table"></table>
                </div>
            </div>
        </div>
        <footer class="panel-footer text-right">
            <button class="btn btn-default modal-dismiss"><?= translate('close') ?></button>
        </footer>
    </section>
</div>


<script>
$(document).ready(function () {
    $('#daterange').daterangepicker({
        opens: 'left',
        locale: { format: 'YYYY/MM/DD' }
    });   
});
</script>


<style>
.fc-event-primary {
    background-color: #2F5249 !important;
    border-color: #2F5249 !important;
    color: #fff !important;
}
.fc-event-warning {
    background-color: #3B060A !important;
    border-color: #3B060A !important;
    color: #fff !important;
}
.fc-event {
    background-color: #2F5249 !important;
    border-color: #2F5249 !important;
    color: #fff !important;
    white-space: normal !important;
    word-wrap: break-word !important;
    overflow-wrap: break-word !important;
}
.fc-event:hover {
    background-color: #3e80cd !important;
    border-color: #3e80cd !important;
}
.fc-event .fc-title {
    color: #fff !important;
    white-space: normal !important;
    word-wrap: break-word !important;
    line-height: 1.2 !important;
}
.fc-event .fc-time {
    color: #fff !important;
}
/* Leave events specific styling */
.fc-event[data-event-type="leave"] {
    background-color: #3B060A !important;
    border-color: #3B060A !important;
    color: #fff !important;
}
.fc-event[data-event-type="leave"]:hover {
    background-color: #C83F12 !important;
    border-color: #C83F12 !important;
}
</style>

<script>
(function ($) {
    $('#event_calendar').fullCalendar({
        header: {
            left: 'prev,next,today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay,listWeek'
        },
        firstDay: 1,
        droppable: false,
        editable: true,
        timezone: 'UTC',
        lang: '<?= $language ?>',
        events: {
            url: "<?= base_url('event/getEventsList/') ?>"
        },
        eventRender: function (event, element) {
            // Store the type as a data attribute for reliable access
            $(element).attr('data-event-type', event.type);
            $(element).on("click", function () {
                var eventType = $(this).attr('data-event-type');
                viewCalendarEvent(event.id, eventType);
            });
        }
    });
})(jQuery);

function viewCalendarEvent(id, type) {
    var base_url = '<?= base_url() ?>';
    
    if (type === 'leave') {
        // Handle leave click - show leave details
        $.ajax({
            url: base_url + "event/getLeaveDetails",
            type: 'POST',
            data: {
                leave_id: id
            },
            success: function (data) {
                $('#ev_table').html(data);
                mfp_modal('#modal');
            }
        });
    } else {
        // Handle regular event click - use the existing app.js function
        viewEvent(id);
    }
}
</script>
