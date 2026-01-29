<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <header class="panel-heading">
                <h2 class="panel-title"><?=translate('promotion_application_details')?></h2>
            </header>
            <div class="panel-body">
                <!-- Staff Information -->
                <div class="row">
                    <div class="col-md-6">
                        <h4><?=translate('employee_information')?></h4>
                        <table class="table table-bordered">
                            <tr>
                                <td><strong><?=translate('name')?>:</strong></td>
                                <td><?=$application['staff_name']?></td>
                            </tr>
                            <tr>
                                <td><strong><?=translate('employee_id')?>:</strong></td>
                                <td><?=$application['employee_id']?></td>
                            </tr>
                            <tr>
                                <td><strong><?=translate('department')?>:</strong></td>
                                <td><?=$application['department_name']?></td>
                            </tr>
                            <tr>
                                <td><strong><?=translate('email')?>:</strong></td>
                                <td><?=$application['email']?></td>
                            </tr>
                            <tr>
                                <td><strong><?=translate('mobile')?>:</strong></td>
                                <td><?=$application['mobileno']?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h4><?=translate('promotion_details')?></h4>
                        <table class="table table-bordered">
                            <tr>
                                <td><strong><?=translate('current_designation')?>:</strong></td>
                                <td><?=$application['current_designation_name']?></td>
                            </tr>
                            <tr>
                                <td><strong><?=translate('target_designation')?>:</strong></td>
                                <td><?=$application['target_designation_name']?></td>
                            </tr>
                            <tr>
                                <td><strong><?=translate('applied_date')?>:</strong></td>
                                <td><?=date('M d, Y H:i', strtotime($application['created_at']))?></td>
                            </tr>
                            <tr>
                                <td><strong><?=translate('status')?>:</strong></td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    switch($application['status']) {
                                        case 'pending': $status_class = 'label-warning'; break;
                                        case 'review': $status_class = 'label-info'; break;
                                        case 'approved': $status_class = 'label-success'; break;
                                        case 'rejected': $status_class = 'label-danger'; break;
                                    }
                                    ?>
                                    <span class="label <?=$status_class?>"><?=ucfirst($application['status'])?></span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Summary Reason -->
                <div class="row">
                    <div class="col-md-12">
                        <h4><?=translate('summary_reason')?></h4>
                        <div class="well">
                            <?=nl2br(htmlspecialchars($application['summary_reason']))?>
                        </div>
                    </div>
                </div>

                <!-- Responsibilities -->
                <div class="row">
                    <div class="col-md-12">
                        <h4><?=translate('selected_responsibilities')?></h4>
                        <?php if (empty($responsibilities)): ?>
                            <div class="alert alert-info">
                                <?=translate('no_responsibilities_selected')?>
                            </div>
                        <?php else: ?>
                            <?php foreach($responsibilities as $resp): ?>
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h5 class="panel-title">
                                            <?=$resp['title']?>
                                            <span class="pull-right">
                                                <?php
                                                $resp_status_class = '';
                                                $resp_status_text = '';
                                                switch($resp['status']) {
                                                    case 1: 
                                                        $resp_status_class = 'label-warning'; 
                                                        $resp_status_text = 'Pending';
                                                        break;
                                                    case 2: 
                                                        $resp_status_class = 'label-success'; 
                                                        $resp_status_text = 'Approved';
                                                        break;
                                                    case 3: 
                                                        $resp_status_class = 'label-danger'; 
                                                        $resp_status_text = 'Rejected';
                                                        break;
                                                }
                                                ?>
                                                <span class="label <?=$resp_status_class?>"><?=$resp_status_text?></span>
                                            </span>
                                        </h5>
                                    </div>
                                    <div class="panel-body">
                                        <?php if ($resp['description']): ?>
                                            <p class="text-muted"><?=$resp['description']?></p>
                                        <?php endif; ?>
                                        <h6><?=translate('applicant_remarks')?>:</h6>
                                        <p><?=nl2br(htmlspecialchars($resp['remarks']))?></p>
                                        
                                        <?php if (get_permission('promotion', 'is_edit') && in_array(loggedin_role_id(), [1, 2, 3, 5, 8])): ?>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-success btn-sm update-status" 
                                                        data-id="<?=$resp['id']?>" data-status="2"
                                                        <?=$resp['status'] == 2 ? 'disabled' : ''?>>
                                                    <i class="fas fa-check"></i> <?=translate('approve')?>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm update-status" 
                                                        data-id="<?=$resp['id']?>" data-status="3"
                                                        <?=$resp['status'] == 3 ? 'disabled' : ''?>>
                                                    <i class="fas fa-times"></i> <?=translate('reject')?>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Overall Application Actions -->
                <?php if (get_permission('promotion', 'is_edit') && in_array(loggedin_role_id(), [1, 2, 3, 5, 8])): ?>
                    <div class="row">
                        <div class="col-md-12">
                            <h4><?=translate('overall_application_status')?></h4>
                            <div class="btn-group">
                                <button type="button" class="btn btn-info update-app-status" 
                                        data-id="<?=$application['id']?>" data-status="review"
                                        <?=$application['status'] == 'review' ? 'disabled' : ''?>>
                                    <i class="fas fa-search"></i> <?=translate('mark_under_review')?>
                                </button>
                                <button type="button" class="btn btn-success update-app-status" 
                                        data-id="<?=$application['id']?>" data-status="approved"
                                        <?=$application['status'] == 'approved' ? 'disabled' : ''?>>
                                    <i class="fas fa-check"></i> <?=translate('approve_application')?>
                                </button>
                                <button type="button" class="btn btn-danger update-app-status" 
                                        data-id="<?=$application['id']?>" data-status="rejected"
                                        <?=$application['status'] == 'rejected' ? 'disabled' : ''?>>
                                    <i class="fas fa-times"></i> <?=translate('reject_application')?>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<script>
$(document).ready(function() {
    // Update responsibility status
    $('.update-status').click(function() {
        var $btn = $(this);
        var responsibilityDetailId = $btn.data('id');
        var status = $btn.data('status');
        
        $btn.prop('disabled', true);
        
        $.ajax({
            url: '<?=base_url("promotion/update_responsibility_status")?>',
            type: 'POST',
            data: {
                responsibility_detail_id: responsibilityDetailId,
                status: status
            },
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    location.reload();
                } else {
                    alert(response.message);
                    $btn.prop('disabled', false);
                }
            },
            error: function() {
                $btn.prop('disabled', false);
            }
        });
    });

    // Update application status
    $('.update-app-status').click(function() {
        var $btn = $(this);
        var applicationId = $btn.data('id');
        var status = $btn.data('status');
        
        $btn.prop('disabled', true);
        
        $.ajax({
            url: '<?=base_url("promotion/update_application_status")?>',
            type: 'POST',
            data: {
                application_id: applicationId,
                status: status
            },
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    location.reload();
                } else {
                    alert(response.message);
                    $btn.prop('disabled', false);
                }
            },
            error: function() {
                $btn.prop('disabled', false);
            }
        });
    });
});
</script>