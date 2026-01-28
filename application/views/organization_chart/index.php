<div class="row">
<?php if (get_permission('organization_chart', 'is_add')): ?>
    <div class="col-md-5">
        <section class="panel">
            <header class="panel-heading">
                <h4 class="panel-title"><i class="far fa-edit"></i> <?php echo translate('add') . " " . translate('organization_chart'); ?></h4>
            </header>
            <?php echo form_open($this->uri->uri_string()); ?>
            <div class="panel-body">
                <?php if (is_superadmin_loggedin()) : ?>
                <div class="form-group">
                    <label class="control-label"><?=translate('business')?> <span class="required">*</span></label>
                    <?php
                        $arrayBranch = $this->app_lib->getSelectList('branch');
                        echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='branch_id'
                        data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
                    ?>
                    <span class="error"><?=form_error('branch_id')?></span>
                </div>
                <?php endif; ?>

                <div class="form-group mb-md">
                    <label class="control-label"><?=translate('employee')?> <span class="required">*</span></label>
					<?php
						$this->db->select('s.id, s.name');
						$this->db->from('staff AS s');
						$this->db->join('login_credential AS lc', 'lc.user_id = s.id');
						$this->db->where('lc.active', 1);   // only active users
						$this->db->where_not_in('lc.role', [1, 9, 11,12]);   // exclude super admin, etc.
						$this->db->where_not_in('s.id', [49]);
						$this->db->order_by('s.name', 'ASC');
						$query = $this->db->get();

						$staffArray = ['' => 'Select']; // default first option
						foreach ($query->result() as $row) {
							$staffArray[$row->id] = $row->name;
						}

						echo form_dropdown(
							"staff_id",
							$staffArray,
							set_value('staff_id'),
							"class='form-control' id='staff_id' data-plugin-selectTwo data-width='100%'"
						);
					?>


                    <span class="error"><?=form_error('staff_id')?></span>
                </div>

                <div class="form-group mb-md">
                    <label class="control-label"><?=translate('department')?> <span class="required">*</span></label>
                    <?php
                        $departmentArray = $this->app_lib->getSelectList('staff_department');
                        echo form_dropdown("department_id", $departmentArray, set_value('department_id'), "class='form-control' id='department_id'
                        data-plugin-selectTwo data-width='100%'");
                    ?>
                    <span class="error"><?=form_error('department_id')?></span>
                </div>

                <div class="form-group mb-md">
                    <label class="control-label"><?=translate('position_type')?> <span class="required">*</span></label>
                    <?php
                        $positionType = [
                            '' => translate('select'),
                            'COO' => 'COO',
                            'Head' => 'Head',
                            'HR' => 'HR',
                            'Incharge' => 'Incharge',
                            'Employee' => 'Employee',
                            'Other' => 'Other'
                        ];
                        echo form_dropdown("position_type", $positionType, set_value('position_type'), "class='form-control'
                        data-plugin-selectTwo data-width='100%'");
                    ?>
                    <span class="error"><?=form_error('position_type')?></span>
                </div>

                <div class="form-group mb-md">
                    <label class="control-label"><?=translate('parent_staff')?></label>
                     <?php
						$staffArray = $this->app_lib->getSelectList('staff');

						// Remove staff with id = 1
						unset($staffArray[1]);

						echo form_dropdown(
							"parent_staff_id",
							$staffArray,
							set_value('staff_id'),
							"class='form-control' id='parent_staff_id' data-plugin-selectTwo data-width='100%'"
						);
					?>
                </div>
            </div>

            <div class="panel-footer">
                <div class="row">
                    <div class="col-md-12">
                        <button class="btn btn-default pull-right" type="submit"><i class="fas fa-plus-circle"></i> <?php echo translate('save'); ?></button>
                    </div>  
                </div>
            </div>
            <?php echo form_close(); ?>
        </section>
    </div>
<?php endif; ?>

<?php if (get_permission('organization_chart', 'is_view')): ?>
    <div class="col-md-<?php if (get_permission('organization_chart', 'is_add')){ echo "7"; }else{echo "12";} ?>">
        <section class="panel">
            <header class="panel-heading">
                <h4 class="panel-title"><i class="fas fa-list-ul"></i> <?php echo translate('organization_chart') . " " . translate('list'); ?></h4>
            </header>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-condensed table-export" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th><?=translate('sl')?></th>
                                <th><?=translate('business')?></th>
                                <th><?=translate('staff')?></th>
                                <th><?=translate('department')?></th>
                                <th><?=translate('position_type')?></th>
                                <th><?=translate('action')?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $count = 1;
                            if (!empty($org_chart_list)) {
                                foreach ($org_chart_list as $row):
                            ?>
                            <tr>
                                <td><?php echo $count++; ?></td>
                                <td><?php echo $row['branch_id'] ? get_type_name_by_id('branch', $row['branch_id']) : '-'; ?></td>
                                <td><?php echo $row['staff_name']; ?></td>
                                <td><?php echo $row['department_name']; ?></td>
                                <td><?php echo $row['position_type']; ?></td>
                                <td class="min-w-xs">
                                    <?php if (get_permission('organization_chart', 'is_edit')): ?>
                                       <a class="btn btn-default btn-circle icon" href="javascript:void(0);" onclick="getOrganizationChartDetails('<?=$row['id']?>')">
											<i class="fas fa-pen-nib"></i>
										</a>

                                    <?php endif; if (get_permission('organization_chart', 'is_delete')): ?>
                                        <?php echo btn_delete('organization_chart/delete/' . $row['id']); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php
                                endforeach;
                            } else {
                                echo '<tr><td colspan="6"><h5 class="text-danger text-center">' . translate('no_information_available') . '</h5></td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
<?php endif; ?>
</div>

<?php if (get_permission('organization_chart', 'is_edit')): ?>
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
    <section class="panel">
        <header class="panel-heading">
            <h4 class="panel-title"><i class="far fa-edit"></i> <?php echo translate('edit') . " " . translate('organization_chart'); ?></h4>
        </header>
        <?php echo form_open('organization_chart/edit', array('class' => 'frm-submit-edit')); ?>
            <div class="panel-body">
                <input type="hidden" name="id" id="e_id" value="" />

                <?php if (is_superadmin_loggedin()) : ?>
                <div class="form-group">
                    <label class="control-label"><?=translate('branch')?> <span class="required">*</span></label>
                    <?php
                        $arrayBranch = $this->app_lib->getSelectList('branch');
                        echo form_dropdown("branch_id", $arrayBranch, '', "class='form-control' id='e_branch_id'
                        data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
                    ?>
                    <span class="error"></span>
                </div>
                <?php endif; ?>

                <div class="form-group mb-md">
                    <label class="control-label"><?=translate('staff')?> <span class="required">*</span></label>
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

						echo form_dropdown(
							"staff_id",
							$staffArray,
							set_value('staff_id'),
							"class='form-control' id='e_staff_id' data-plugin-selectTwo data-width='100%'"
						);
					?>

                    <span class="error"></span>
                </div>

                <div class="form-group mb-md">
                    <label class="control-label"><?=translate('department')?> <span class="required">*</span></label>
                    <?php
                        $departmentArray = $this->app_lib->getSelectList('staff_department');
                        echo form_dropdown("department_id", $departmentArray, '', "class='form-control' id='e_department_id'
                        data-plugin-selectTwo data-width='100%'");
                    ?>
                    <span class="error"></span>
                </div>

                <div class="form-group mb-md">
                    <label class="control-label"><?=translate('position_type')?> <span class="required">*</span></label>
                    <?php
                        $positionType = [
                            '' => translate('select'),
                            'COO' => 'COO',
                            'Head' => 'Head',
                            'HR' => 'HR',
                            'Incharge' => 'Incharge',
                            'Employee' => 'Employee',
                            'Other' => 'Other'
                        ];
                        echo form_dropdown("position_type", $positionType, '', "class='form-control' id='e_position_type'
                        data-plugin-selectTwo data-width='100%'");
                    ?>
                    <span class="error"></span>
                </div>

                <div class="form-group mb-md">
                    <label class="control-label"><?=translate('parent_staff')?></label>
                    <?php
                        echo form_dropdown("parent_staff_id", $staffArray, '', "class='form-control' id='e_parent_staff_id'
                        data-plugin-selectTwo data-width='100%'");
                    ?>
                </div>

            </div>
            <footer class="panel-footer">
                <div class="row">
                    <div class="col-md-12 text-right">
                        <button type="submit" class="btn btn-default" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
                            <i class="fas fa-plus-circle"></i> <?php echo translate('update'); ?>
                        </button>
                        <button class="btn btn-default modal-dismiss"><?php echo translate('cancel'); ?></button>
                    </div>
                </div>
            </footer>
        <?php echo form_close(); ?>
    </section>
</div>
<?php endif; ?>


<script>
function getOrganizationChartDetails(id) {
    $.ajax({
        url: base_url + 'organization_chart/get_single',
        type: 'POST',
        data: {'id': id},
        dataType: "json",
        success: function(data) {
            $('#e_id').val(data.id);
            $('#e_staff_id').val(data.staff_id).trigger('change');
            $('#e_department_id').val(data.department_id).trigger('change');
            $('#e_branch_id').val(data.branch_id).trigger('change');
            $('#e_position_type').val(data.position_type).trigger('change');
            $('#e_parent_staff_id').val(data.parent_staff_id).trigger('change');
            mfp_modal('#modal');
        }
    });
}

</script>