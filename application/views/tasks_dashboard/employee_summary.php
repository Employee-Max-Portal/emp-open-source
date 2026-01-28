<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <header class="panel-heading">
                <h4 class="panel-title">
                    <i class="fas fa-users"></i> <?=translate('financial_report')?>
                </h4>
            </header>
            <div class="panel-body">
                <div class="mb-md">
                    <form method="get" action="<?=base_url('tasks_dashboard/employee_summary_report')?>" id="monthFilterForm">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="month" name="month" class="form-control" value="<?=$month?>" required onchange="this.form.submit()">
                            </div>
                        </div>
                    </form>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-condensed">
                        <thead>
                            <tr>
                                <th><?=translate('employee_id')?></th>
                                <th><?=translate('name')?></th>
                                <th><?=translate('department')?></th>
                                <th><?=translate('attendance')?></th>
                                <th>KPI</th>
                                <th><?=translate('tasks')?></th>
                                <th><?=translate('outcome')?></th>
                                <th><?=translate('gross_salary')?></th>
                                <th><?=translate('deduction')?></th>
                                <th><?=translate('convenience')?></th>
                                <th><?=translate('final_salary')?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($employees)): ?>
                                <?php foreach ($employees as $emp): ?>
                                <tr>
                                    <td><?=$emp['staff_id']?></td>
                                    <td><?=$emp['name']?></td>
                                    <td><?=$emp['department_name'] ?: 'N/A'?></td>
                                    <td><?=$emp['attendance']?></td>
                                    <td><?=number_format($emp['kpi'], 2)?></td>
                                    <td><?=$emp['tasks']?></td>
                                    <td><?=$emp['outcome']?></td>
                                    <td><?=currencyFormat($emp['gross_salary'])?></td>
                                    <td><?=currencyFormat($emp['deduction'])?></td>
                                    <td><?=currencyFormat($emp['convenience'])?></td>
                                    <td><?=currencyFormat($emp['final_salary'])?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" class="text-center text-danger">
                                        <?=translate('no_information_available')?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>
