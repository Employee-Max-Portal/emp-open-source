<?php $task_type = $this->db->get_where('task_types', ['id' => $task_type_id])->row(); ?>

<header class="panel-heading">
    <h4 class="panel-title"><i class="fas fa-edit"></i> <?= translate('edit_task_type') ?></h4>
</header>

<?php echo form_open('tracker/update_task_type', ['class' => 'form-horizontal']); ?>
<div class="panel-body">
    <input type="hidden" name="id" value="<?= $task_type->id ?>">
    
    <div class="form-group">
        <label class="col-md-3 control-label"><?= translate('name') ?> <span class="required">*</span></label>
        <div class="col-md-8">
            <input type="text" class="form-control" name="name" value="<?= html_escape($task_type->name) ?>" required />
        </div>
    </div>
    
    <div class="form-group">
        <label class="col-md-3 control-label"><?= translate('description') ?></label>
        <div class="col-md-8">
            <textarea name="description" class="form-control" rows="3" placeholder="Enter task type description..."><?= html_escape($task_type->description) ?></textarea>
        </div>
    </div>
</div>

<footer class="panel-footer">
    <div class="row">
        <div class="col-md-12 text-right">
            <button type="submit" class="btn btn-default mr-xs" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
                <i class="fas fa-save"></i> <?= translate('update') ?>
            </button>
            <button class="btn btn-default modal-dismiss"><?= translate('cancel') ?></button>
        </div>
    </div>
</footer>
<?php echo form_close(); ?>