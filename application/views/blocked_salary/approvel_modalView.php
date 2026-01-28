<?php
$block_details = $this->blocked_salary_model->getBlockedSalaryById($block_id);
$user_role = loggedin_role_id();
$logged_staff_id = get_loggedin_user_id();
$is_manager = in_array($user_role, [1, 2, 3, 5, 8]);
$is_affected_staff = ($block_details['staff_id'] == $logged_staff_id);
?>

<?php echo form_open('blocked_salary'); ?>
<header class="panel-heading">
    <h4 class="panel-title"><i class="fas fa-list-ol"></i> <?=translate('review')?></h4>
</header>
<div class="panel-body">
    <div class="table-responsive">
        <table class="table borderless mb-none">
            <tbody>
                <input type="hidden" name="id" value="<?=$block_id?>">
                <tr>
                    <th width="120"><?=translate('blocked_by')?> :</th>
                    <td><?=ucfirst($block_details['blocked_by_name'])?></td>
                </tr>
                <tr>
                    <th><?=translate('employee_name')?> :</th>
                    <td><?=ucfirst($block_details['staff_name'])?></td>
                </tr>
                <tr>
                    <th><?=translate('employee_id')?> :</th>
                    <td><?=ucfirst($block_details['employee_id'])?></td>
                </tr>
                <tr>
                    <th><?=translate('department')?> :</th>
                    <td><?=ucfirst($block_details['department_name'])?></td>
                </tr>
                <tr>
                    <th><?=translate('task_title')?> :</th>
                    <td><?=ucfirst($block_details['task_title'])?></td>
                </tr>
                <tr>
                    <th><?=translate('blocked_date')?> :</th>
                    <td><?php echo _d($block_details['created_at']);?></td>
                </tr>
                <tr>
                    <th><?=translate('reason')?> :</th>
                    <td width="350"><?=(empty($block_details['reason']) ? 'N/A' : $block_details['reason']);?></td>
                </tr>
                <?php if ($is_affected_staff): ?>
                <tr>
                    <th><?=translate('staff_explanation')?> :</th>
                    <td>
                        <textarea name="staff_explanation" class="form-control" rows="3" placeholder="<?=translate('add_your_explanation')?>"><?php echo $block_details['staff_explanation']; ?></textarea>
                    </td>
                </tr>
                <?php elseif (!empty($block_details['staff_explanation'])): ?>
                <tr>
                    <th><?=translate('staff_explanation')?> :</th>
                    <td><?=nl2br($block_details['staff_explanation'])?></td>
                </tr>
                <?php endif; ?>
                <?php if ($is_manager): ?>
                <tr>
                    <th><?=translate('manager_approval')?> :</th>
                    <th colspan="1">
                        <div class="radio-custom radio-inline">
                            <input type="radio" id="pending" name="status" value="1" <?php echo ($block_details['status'] == 1 ? ' checked' : '');?>>
                            <label for="pending"><?=translate('pending')?></label>
                        </div>
                        <div class="radio-custom radio-inline">
                            <input type="radio" id="unblocked" name="status" value="2" <?php echo ($block_details['status'] == 2 ? ' checked' : '');?>>
                            <label for="unblocked"><?=translate('unblocked')?></label>
                        </div>
                        <div class="radio-custom radio-inline">
                            <input type="radio" id="rejected" name="status" value="3" <?php echo ($block_details['status'] == 3 ? ' checked' : '');?>>
                            <label for="rejected"><?=translate('rejected')?></label>
                        </div>
                    </th>
                </tr>
                <tr>
                    <th><?=translate('manager_comments')?> :</th>
                    <td><textarea class="form-control" name="manager_comments" rows="3"><?php echo $block_details['manager_comments']; ?></textarea></td>
                </tr>
                <?php else: ?>
                <tr>
                    <th><?=translate('status')?> :</th>
                    <td>
                        <?php
                        $status_text = array(1 => 'pending', 2 => 'unblocked', 3 => 'rejected');
                        echo translate($status_text[$block_details['status']]);
                        ?>
                    </td>
                </tr>
                <?php if (!empty($block_details['manager_comments'])): ?>
                <tr>
                    <th><?=translate('manager_comments')?> :</th>
                    <td><?=nl2br($block_details['manager_comments'])?></td>
                </tr>
                <?php endif; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<footer class="panel-footer">
    <div class="row">
        <div class="col-md-12 text-right">
            <?php if ($is_manager || $is_affected_staff): ?>
            <button class="btn btn-default mr-xs" type="submit" name="update" value="1">
                <i class="fas fa-plus-circle"></i> <?php echo translate('update'); ?>
            </button>
            <?php endif; ?>
            <button class="btn btn-default modal-dismiss"><?=translate('close')?></button>
        </div>
    </div>
</footer>
<?php echo form_close(); ?>