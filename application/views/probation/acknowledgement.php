<section class="panel">
    <header class="panel-heading">
        <h4 class="panel-title"><i class="fas fa-list-alt"></i> <?= translate('Probation Acknowledgements') ?></h4>
    </header>
    <div class="panel-body">
   
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped table-export" id="acknowledgementTable">
                <thead>
                    <tr>
						<th><?= translate('employee') ?></th>
						<th><?= translate('joining_date') ?></th>
						<th><?= translate('probation_ends_on') ?></th>
						<th><?= translate('1st_month') ?></th>
						<th><?= translate('2nd_month') ?></th>
						<th><?= translate('3rd_month') ?></th>
						<th><?= translate('4th_month') ?></th>
						<th><?= translate('5th_month') ?></th>
						<th><?= translate('6th_month') ?></th>
						<th><?= translate('total_completed') ?></th>
						<th><?= translate('status') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($acknowledgements as $item): ?>
                        <?php 
                            $completed_months = 0;
                            $month_statuses = [];

                            foreach (range(1, 6) as $m) {
                                $is_done = !empty($item['assessment'][$m]['meeting_done']) && !empty($item['assessment'][$m]['meeting_date']);
                                if ($is_done) {
                                    $completed_months++;
                                    $month_statuses[$m] = '<span class="label label-success">✔</span>';
                                } else {
                                    $month_statuses[$m] = '<span class="label label-default">–</span>';
                                }
                            }

                            $status = ($completed_months > 0) ? 'Evaluation Completed' : 'Pending';
                        ?>
                        <tr>
                            <td><?= html_escape($item['staff_id'] . ' - ' . $item['name']) ?></td>
                            <td><?= _d($item['joining_date']) ?></td>
                            <td><?= date('d M Y', strtotime('+6 months', strtotime($item['joining_date']))) ?></td>
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                <td class="text-center"><?= $month_statuses[$i] ?></td>
                            <?php endfor; ?>
                            <td class="text-center"><?= $completed_months ?></td>
                            <td><?= $status ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
