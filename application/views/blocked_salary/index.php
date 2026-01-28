<div class="row">
    <div class="col-md-12">
	
		<?php if (in_array(loggedin_role_id(), [1, 2, 3, 5, 8])): ?>
        <section class="panel">
            <header class="panel-heading">
                <h4 class="panel-title"><?=translate('blocked_salary')?></h4>
            </header>
            <?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
            <div class="panel-body">
                <div class="row mb-sm">
                     <div class="col-md-offset-3 col-md-4 mb-sm">
                        <?php
                            $arrayBranch = $this->app_lib->getSelectList('branch');
                            echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id', $branch_id), "class='form-control' id='branch_id'
                            data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
                        ?>
                    </div>
                    <div class="col-md-3 mb-sm">
                        <button type="submit" name="search" value="1" class="btn btn-default btn-md">
                            <i class="fas fa-search"></i> <?=translate('search')?>
                        </button>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </section>
		
        <?php endif; ?>

        <section class="panel">
            <div class="panel-body">
                <div class="export_title"><?=translate('blocked_salary')?></div>
                <div class="table-responsive">
                <table class="table table-bordered table-hover table-condensed mb-none tbr-top" cellspacing="0" width="100%" id="table-export">
                    <thead>
                        <tr>
                            <th width="50"><?=translate('sl')?></th>
                            <th class="min-w-150"><?=translate('employee')?></th>
                            <th class="hidden-xs"><?=translate('department')?></th>
                            <th class="hidden-xs"><?=translate('task_title')?></th>
                            <th class="min-w-120"><?=translate('blocked_date')?></th>
                            <th class="min-w-80"><?=translate('status')?></th>
                            <th class="hidden-xs"><?=translate('approved_by')?></th>
                            <th class="hidden-xs"><?=translate('cleared_date')?></th>
                            <th width="120"><?=translate('action')?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $count = 1;
                        if (count($blocked_salary_list)):
                            foreach($blocked_salary_list as $row):
                        ?>
                        <tr>
                            <td><?php echo $count++; ?></td>
                            <td class="min-w-150">
                                <div class="text-truncate"><?php echo $row['employee_id'] . ' - ' . $row['staff_name']; ?></div>
                                <small class="visible-xs text-muted">
                                    <?php echo translate($row['department_name']); ?> | <?php echo translate($row['task_title']); ?>
                                </small>
                            </td>
                            <td class="hidden-xs"><?php echo translate($row['department_name']); ?></td>
                            <td class="hidden-xs"><?php echo translate($row['task_title']); ?></td>
                            <td class="min-w-120">
                                <div><?php echo date('d-m-Y', strtotime($row['created_at'])); ?></div>
                                <small class="text-muted"><?php echo date('H:i:s', strtotime($row['created_at'])); ?></small>
                            </td>
                            <td>
                                <?php
                                $status = $row['status'];
                                $labelClass = '';
                                $statusText = '';
                                switch($status) {
                                    case 1:
                                        $labelClass = 'label-warning';
                                        $statusText = translate('pending');
                                        break;
                                    case 2:
                                        $labelClass = 'label-success';
                                        $statusText = translate('unblocked');
                                        break;
                                    case 3:
                                        $labelClass = 'label-danger';
                                        $statusText = translate('rejected');
                                        break;
                                }
                                ?>
                                <span class="label <?php echo $labelClass; ?>"><?php echo $statusText; ?></span>
                            </td>
                            <td class="hidden-xs"><?php echo translate($row['approved_by_name']); ?></td>
                            <td class="hidden-xs">
                                <?php if (!empty($row['cleared_on'])): ?>
                                    <div><?php echo date('d-m-Y', strtotime($row['cleared_on'])); ?></div>
                                    <small class="text-muted"><?php echo date('H:i:s', strtotime($row['cleared_on'])); ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="min-w-c">
							
								<a class="btn btn-info btn-circle icon" href="javascript:void(0);" data-toggle="tooltip" data-original-title="<?php echo translate('View Reason'); ?>"
								onclick="getView('<?php echo html_escape($row['id']); ?>')">
									<i class="fas fa-eye" style="color: #ffffff;"></i>
								</a>
												
                                <?php if (get_permission('rdc_salary_blocks', 'is_edit')): ?>
									<a href="javascript:void(0);" class="btn btn-circle icon btn-default" onclick="getApprovelDetails('<?=$row['id']?>')">
									<i class="fas fa-bars"></i>
								</a>
                                <?php endif; ?>
                                <?php if (get_permission('rdc_salary_blocks', 'is_delete')): ?>
                                    <?php echo btn_delete('blocked_salary/delete/' . $row['id']); ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                            endforeach;
                        else:
                        ?>
                        <tr>
                            <td colspan="11" class="text-center"><?=translate('no_information_available')?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </section>
    </div>
</div>
<!-- View Modal -->
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
	<section class="panel" id='quick_view'></section>
</div>	
			
<div class="zoom-anim-dialog modal-block modal-block-lg mfp-hide payroll-t-modal" id="modal_equipment_details" style="width: 100%!important;">
    <section class="panel">
        <header class="panel-heading d-flex justify-content-between align-items-center">
            <div class="row">
                <div class="col-md-6 text-left">
                    <h4 class="panel-title">
						<i class="fas fa-bars"></i> <?php echo translate('Salary Block') . " " . translate('Reason'); ?>
					</h4>
                </div>
               
            </div>
        </header>
        <div class="panel-body">
            <div id="equipment_details_view_tray">
                <!-- The description content will be loaded here dynamically -->
            </div>
        </div>
        <footer class="panel-footer">
            <div class="row">

                <div class="col-md-12 text-right">
                    <button class="btn btn-default modal-dismiss"><?php echo translate('close'); ?></button>
                </div>
            </div>
        </footer>
    </section>
</div>


<script>
function getView(id) {
    $.ajax({
        url: base_url + 'blocked_salary/get_view_description', // Update the URL to the correct controller path
        type: 'POST',
        data: { id: id },
        success: function(response) {
            // Inject the response into the modal
            $('#equipment_details_view_tray').html(response);

            // Open the modal
            $.magnificPopup.open({
                items: {
                    src: '#modal_equipment_details'
                },
                type: 'inline'
            });
        },
        error: function() {
            alert('Failed to retrieve description.');
        }
    });
}

// get approvel details
function getApprovelDetails(id) {
	$.ajax({
		url: base_url + 'blocked_salary/getApprovelDetails',
		type: 'POST',
		data: {'id': id},
		dataType: "html",
		success: function (data) {
			$('#quick_view').html(data);
			mfp_modal('#modal');
		}
	});
}

</script>
<style>
@media (max-width: 768px) {
    .min-w-150 { min-width: 150px; }
    .min-w-120 { min-width: 120px; }
    .min-w-80 { min-width: 80px; }
    .text-truncate { 
        white-space: nowrap; 
        overflow: hidden; 
        text-overflow: ellipsis; 
        max-width: 150px; 
    }
    .table-responsive {
        border: none;
    }
    .table-responsive .table {
        margin-bottom: 0;
    }
}
</style>