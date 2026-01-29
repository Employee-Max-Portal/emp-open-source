<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <header class="panel-heading">
                <h2 class="panel-title"><?=translate('my_promotion_applications')?></h2>

            </header>
            <div class="panel-body">
                <?php if (empty($applications)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <?=translate('no_promotion_applications_found')?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-condensed">
                            <thead>
                                <tr>
                                    <th><?=translate('sl')?></th>
                                    <th><?=translate('current_designation')?></th>
                                    <th><?=translate('target_designation')?></th>
                                    <th><?=translate('applied_date')?></th>
                                    <th><?=translate('status')?></th>
                                    <th><?=translate('action')?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $sl = 1; foreach($applications as $app): ?>
                                    <tr>
                                        <td><?=$sl++?></td>
                                        <td><?=$app['current_designation_name']?></td>
                                        <td><?=$app['target_designation_name']?></td>
                                        <td><?=date('M d, Y', strtotime($app['created_at']))?></td>
                                        <td>
                                            <?php
                                            $status_class = '';
                                            switch($app['status']) {
                                                case 'pending': $status_class = 'label-warning'; break;
                                                case 'review': $status_class = 'label-info'; break;
                                                case 'approved': $status_class = 'label-success'; break;
                                                case 'rejected': $status_class = 'label-danger'; break;
                                            }
                                            ?>
                                            <span class="label <?=$status_class?>"><?=ucfirst($app['status'])?></span>
                                        </td>
                                        <td>
                                            <a href="<?=base_url('promotion/view/'.$app['id'])?>" class="btn btn-default btn-xs">
                                                <i class="fas fa-eye"></i> <?=translate('view')?>
                                            </a>
                                            <?php if ($app['status'] == 'pending'): ?>
                                                <a href="<?=base_url('promotion/delete/'.$app['id'])?>" class="btn btn-danger btn-xs" onclick="return confirm('<?=translate('are_you_sure')?>')">
                                                    <i class="fas fa-trash"></i> <?=translate('delete')?>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>