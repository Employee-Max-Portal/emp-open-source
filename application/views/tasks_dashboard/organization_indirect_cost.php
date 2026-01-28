<div class="row">
    <div class="col-md-12">
        <!-- Filter Section -->
        <section class="panel">
            <header class="panel-heading">
                <h4 class="panel-title">Cost Calculation Parameters</h4>
            </header>
            <?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
                <div class="panel-body">
                    <div class="row mb-sm">
                        <div class="col-md-6 mb-sm">
                            <div class="form-group">
                                <label class="control-label">Monthly Office Cost (including snacks, rent, bills) <span class="required">*</span></label>
                                <input type="number" class="form-control" name="office_overhead" 
                                       value="<?= $this->input->post('office_overhead') ?: $this->input->get('office_overhead') ?: 50000 ?>" 
                                       min="0" step="1000" placeholder="Enter overhead amount">
                            </div>
                        </div>
                    </div>
                </div>
                <footer class="panel-footer">
                    <div class="row">
                        <div class="col-md-offset-10 col-md-2">
                            <button type="submit" class="btn btn-default btn-block">
                                <i class="fas fa-calculator"></i> Calculate
                            </button>
                        </div>
                    </div>
                </footer>
            <?php echo form_close(); ?>
        </section>

        <!-- Results Section -->
        <section class="panel appear-animation" data-appear-animation="fadeInUp" data-appear-animation-delay="100">
            <header class="panel-heading">
                <h4 class="panel-title"><i class="fas fa-chart-line" aria-hidden="true"></i> Organization Indirect Cost Analysis</h4>
            </header>
            <div class="panel-body">
                <!-- Key Metrics Row -->
                <div class="row mb-lg">
                    <div class="col-md-3 mb-sm">
                        <div class="widget-summary widget-summary-sm">
                            <div class="widget-summary-col widget-summary-col-icon">
                                <div class="summary-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            <div class="widget-summary-col">
                                <div class="summary">
                                    <h4 class="title"> Active Staff</h4>
                                    <div class="info">
                                        <strong class="amount"><?= $organization_indirect_cost['explanation']['total_active_staff'] ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-sm">
                        <div class="widget-summary widget-summary-sm">
                            <div class="widget-summary-col widget-summary-col-icon">
                                <div class="summary-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                            </div>
                            <div class="widget-summary-col">
                                <div class="summary">
                                    <h4 class="title"> Total Salary</h4>
                                    <div class="info">
                                        <strong class="amount"><?= number_format($organization_indirect_cost['explanation']['total_organization_salary']) ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-sm">
                        <div class="widget-summary widget-summary-sm">
                            <div class="widget-summary-col widget-summary-col-icon">
                                <div class="summary-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                            </div>
                            <div class="widget-summary-col">
                                <div class="summary">
                                    <h4 class="title">Monthly Office Cost</h4>
                                    <div class="info">
                                        <strong class="amount"><?= number_format($organization_indirect_cost['explanation']['office_overhead']) ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-sm">
                        <div class="widget-summary widget-summary-sm">
                            <div class="widget-summary-col widget-summary-col-icon">
                                <div class="summary-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            <div class="widget-summary-col">
                                <div class="summary">
                                    <h4 class="title"> Working Hours</h4>
                                    <div class="info">
                                        <strong class="amount"><?= number_format($organization_indirect_cost['explanation']['total_working_hours']) ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Result -->
                <div class="row mb-lg">
                    <div class="col-md-12">
                        <div class="alert alert-info text-center">
                            <h3 class="mb-xs">Indirect Cost Per Hour</h3>
                            <h1 class="text-primary mb-xs"><?= $organization_indirect_cost['breakdown']['indirect_cost_per_hour'] ?></h1>
                            <p class="mb-none">This is your organization's true hourly cost including all expenses</p>
                        </div>
                    </div>
                </div>

                <!-- Detailed Tables -->
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered table-striped mb-none">
                            <thead>
                                <tr>
                                    <th colspan="2" class="text-center">Cost Breakdown</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Average Salary per Staff</strong></td>
                                    <td class="text-right"><?= number_format($organization_indirect_cost['breakdown']['average_salary_per_staff']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Salary Cost per Hour</strong></td>
                                    <td class="text-right"><?= $organization_indirect_cost['breakdown']['salary_cost_per_hour'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Monthly Office Cost per Hour</strong></td>
                                    <td class="text-right"><?= $organization_indirect_cost['breakdown']['office_cost_per_hour'] ?></td>
                                </tr>
                                <tr class="bg-success text-white">
                                    <td><strong>Total Indirect Cost per Hour</strong></td>
                                    <td class="text-right"><strong><?= $organization_indirect_cost['breakdown']['indirect_cost_per_hour'] ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered table-striped mb-none">
                            <thead>
                                <tr>
                                    <th colspan="2" class="text-center">Usage Examples</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Daily Cost (8.5 hours)</strong></td>
                                    <td class="text-right"><?= number_format($organization_indirect_cost['breakdown']['indirect_cost_per_hour'] * 8.5) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Weekly Cost (42.5 hours)</strong></td>
                                    <td class="text-right"><?= number_format($organization_indirect_cost['breakdown']['indirect_cost_per_hour'] * 42.5) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Monthly Cost (187 hours)</strong></td>
                                    <td class="text-right"><?= number_format($organization_indirect_cost['breakdown']['indirect_cost_per_hour'] * 187) ?></td>
                                </tr>
                                <tr class="bg-warning">
                                    <td><strong>100-Hour Project Cost</strong></td>
                                    <td class="text-right"><strong><?= number_format($organization_indirect_cost['breakdown']['indirect_cost_per_hour'] * 100) ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- Formula Section -->
        <section class="panel appear-animation" data-appear-animation="fadeInUp" data-appear-animation-delay="200">
            <header class="panel-heading">
                <h4 class="panel-title"><i class="fas fa-calculator" aria-hidden="true"></i> Calculation Formula</h4>
            </header>
            <div class="panel-body">
                <div class="alert alert-warning">
                    <h5><strong>Step 1: Total Cost</strong></h5>
                    <p class="mb-sm"><?= $organization_indirect_cost['formula']['total_cost'] ?></p>
                    
                    <h5><strong>Step 2: Total Working Hours</strong></h5>
                    <p class="mb-sm"><?= $organization_indirect_cost['formula']['total_hours'] ?></p>
                    
                    <h5><strong>Step 3: Indirect Cost Per Hour</strong></h5>
                    <p class="mb-none"><?= $organization_indirect_cost['formula']['hourly_rate'] ?></p>
                </div>
            </div>
        </section>
    </div>
</div>