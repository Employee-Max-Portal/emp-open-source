<aside id="sidebar-left" class="sidebar-left">
    <div class="nano">
        <div class="nano-content">
            <nav id="menu" class="nav-main" role="navigation">
                <ul class="nav nav-main">
                    <!-- dashboard -->
					<?php 
					$role_id   = loggedin_role_id();
					$branch_id = $this->input->get('branch_id');
					?>

					<li class="nav-parent <?php if ($main_menu == 'dashboard') echo 'nav-active nav-expanded'; ?>">
						<a>
							<i class="icons icon-grid"></i><span><?= translate('dashboard') ?></span>
						</a>
						<ul class="nav nav-children">

							<?php if (in_array($role_id, [1, 2, 3, 5])): ?>
							<!-- All Dashboard -->
							<li class="<?php if ($sub_page == 'dashboard/index' && empty($branch_id)) echo 'nav-active'; ?>">
								<a href="<?= base_url('dashboard') ?>">
									<span><i class="fas fa-caret-right" aria-hidden="true"></i> <?= translate('all') ?></span>
								</a>
							</li>

							<!-- Branch-wise Dashboard -->
							<?php 
							$branches = $this->db->get('branch')->result();
							foreach ($branches as $row): ?>
								<li class="<?php if ($branch_id == $row->id) echo 'nav-active'; ?>">
									<a href="<?= base_url('dashboard/index?branch_id='.$row->id) ?>">
										<span><i class="fas fa-caret-right" aria-hidden="true"></i> <?= html_escape($row->name) ?></span>
									</a>
								</li>
							<?php endforeach; ?>
							<!-- employee summary Dashboard -->
							<li class="<?php if ($sub_page == 'tasks_dashboard/employee_summary') echo 'nav-active'; ?>">
								<a href="<?= base_url('tasks_dashboard/employee_financial_report') ?>">
									<span><i class="fas fa-caret-right" aria-hidden="true"></i> <?= translate('financial - summary') ?></span>
								</a>
							</li>
							<?php elseif (in_array($role_id, [10])): ?>
							<!-- Advisor Dashboard -->
							<li class="<?php if ($sub_page == 'advisor/dashboard' || $sub_page == 'advisor/finance') echo 'nav-active'; ?>">
								<a href="<?= base_url('advisor') ?>">
									<span><i class="fas fa-caret-right" aria-hidden="true"></i> <?= translate('advisor dashboard') ?></span>
								</a>
							</li>
								
							<?php else: ?>
							<!-- Employee Dashboard -->
							<li class="<?php if ($sub_page == 'dashboard/index') echo 'nav-active'; ?>">
								<a href="<?= base_url('dashboard') ?>">
									<span><i class="fas fa-caret-right" aria-hidden="true"></i> <?= translate('EMP dashboard') ?></span>
								</a>
							</li>
							<?php endif; ?>

							<!-- RDC Dashboard (common for all, with permission) -->
							<?php if (get_permission('rdc_dashboard', 'is_view')): ?>
							<li class="<?php if ($sub_page == 'rdc/dashboard') echo 'nav-active'; ?>">
								<a href="<?= base_url('rdc/dashboard') ?>">
									<span><i class="fas fa-caret-right" aria-hidden="true"></i> <?= translate('RDC - Dashboard') ?></span>
								</a>
							</li>
							<?php endif; ?>
							
							<!-- Tasks Dashboard (common for all, with permission) -->
							<li class="<?php if ($sub_page == 'tasks_dashboard/index') echo 'nav-active'; ?>">
								<a href="<?= base_url('tasks_dashboard') ?>">
									<span><i class="fas fa-caret-right" aria-hidden="true"></i> <?= translate('Tasks - Dashboard') ?></span>
								</a>
							</li>
							
							<!-- Activity Dashboard (common for all, with permission) -->
							<li class="<?php if ($sub_page == 'dashboard/activity_dashboard') echo 'nav-active'; ?>">
								<a href="<?= base_url('dashboard/activity_dashboard') ?>">
									<span><i class="fas fa-caret-right" aria-hidden="true"></i> <?= translate('Activity - Dashboard') ?></span>
								</a>
							</li>
							
							<!-- Goal Dashboard (common for all, with permission) -->
							<li class="<?php if ($sub_page == 'goals/dashboard') echo 'nav-active'; ?>">
								<a href="<?php echo base_url('goals'); ?>">
									<span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('Goal - Dashboard'); ?></span>
								</a>
							</li>
							
						</ul>
					</li>

					 
					<?php
					if (get_permission('notification', 'is_view') || get_permission('activity_logs', 'is_view') || get_permission('work_summary', 'is_view')) {
					?>
						<li class="nav-parent <?php if ($main_menu == 'notification') echo 'nav-expanded nav-active'; ?>">
							<a><i class="fas fa-bell"></i><span><?php echo translate('activity_feeds'); ?></span></a>
							<ul class="nav nav-children">
								<?php if (get_permission('notification', 'is_view')) { ?>
								<li class="<?php if ($sub_page == 'dashboard/notification') echo 'nav-active'; ?>">
									<a href="<?php echo base_url('dashboard/notification'); ?>">
										<span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('notifications'); ?></span>
									</a>
								</li>
								<?php } if (get_permission('activity_logs', 'is_view')) { ?>
								<li class="<?php if ($sub_page == 'dashboard/activity_logs') echo 'nav-active'; ?>">
									<a href="<?php echo base_url('dashboard/activity_logs'); ?>">
										<span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('activity_logs'); ?></span>
									</a>
								</li>
								<?php } if (get_permission('work_summary', 'is_view')) { ?>
								<li class="<?php if ($sub_page == 'dashboard/work_summary') echo 'nav-active'; ?>">
									<a href="<?php echo base_url('dashboard/work_summary'); ?>">
										<span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('work_summary'); ?></span>
									</a>
								</li>
								<?php } ?>
							</ul>
						</li>
					<?php
					}
					?>

                   
                    <?php
                    if (moduleIsEnabled('human_resource')) {
                        if(get_permission('generate_certificate', 'is_view') ||
                        get_permission('certificate_templete', 'is_view') ||
                        get_permission('salary_template', 'is_view') ||
                        get_permission('salary_assign', 'is_view') ||
                        get_permission('salary_payment', 'is_view') ||
                        get_permission('salary_increment', 'is_view') ||
                        get_permission('advance_salary_manage', 'is_view') ||
                        get_permission('advance_salary_request', 'is_view') ||
                        get_permission('fund_requisition_manage', 'is_view') ||
                        get_permission('fund_requisition_request', 'is_view') ||
                        get_permission('leave_category', 'is_view') ||
                        get_permission('leave_category', 'is_add') ||
                        get_permission('leave_request', 'is_view') ||
                        get_permission('leave_manage', 'is_view')) {
                    ?>
                    <!-- human resource -->
                    <li class="nav-parent <?php if ($main_menu == 'employee' ||$main_menu == 'payroll' || $main_menu == 'advance_salary' || $main_menu == 'promotion' || $main_menu == 'fund_requisition' || $main_menu == 'leave' || $main_menu == 'attendance' || $main_menu == 'assets' || $main_menu == 'organization_chart' || $main_menu == 'employee_award' || $main_menu == 'certificate' || $main_menu == 'cashbook' || $main_menu == 'contact_info' || $main_menu == 'goals') echo 'nav-expanded nav-active';?>">
                        <a>
                            <i class="icons icon-loop"></i><span><?=translate('hrm')?></span>
                        </a>
                        <ul class="nav nav-children">
						  <?php
                    if(get_permission('employee', 'is_view') ||
                    get_permission('employee', 'is_add') ||
                    get_permission('designation', 'is_view') ||
                    get_permission('designation', 'is_add') ||
                    get_permission('department', 'is_view') ||
                    get_permission('responsibilities', 'is_view') ||
                    get_permission('roles_responsibilities', 'is_view') ||
                    get_permission('employee_disable_authentication', 'is_view')) {
                    ?>
                    <!-- Employees -->
                    <li class="nav-parent <?php if ($main_menu == 'employee') echo 'nav-expanded nav-active'; ?>">
                        <a><i class="fas fa-users"></i><span><?php echo translate('employee'); ?></span></a>
                        <ul class="nav nav-children">
                        <?php if(get_permission('employee', 'is_view')){ ?>
                            <li class="<?php if ($sub_page == 'employee/view' ||  $sub_page == 'employee/profile' ) echo 'nav-active'; ?>">
                                <a href="<?php echo base_url('employee/view'); ?>">
                                    <span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('employee_list'); ?></span>
                                </a>
                            </li>
                        <?php } if(get_permission('department', 'is_view') || get_permission('department', 'is_add')){ ?>
                            <li class="<?php if ($sub_page == 'employee/department') echo 'nav-active'; ?>">
                                <a href="<?php echo base_url('employee/department'); ?>">
                                    <span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('add_department'); ?></span>
                                </a>
                            </li>
                        <?php }  if(get_permission('designation', 'is_view') || get_permission('designation', 'is_add')){ ?>
                            <li class="<?php if ($sub_page == 'employee/designation') echo 'nav-active'; ?>">
                                <a href="<?php echo base_url('employee/designation'); ?>">
                                    <span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('add_designation'); ?></span>
                                </a>
                            </li>
                        <?php } if(get_permission('employee', 'is_add')){ ?>
                            <li class="<?php if ($sub_page == 'employee/add') echo 'nav-active'; ?>">
                                <a href="<?php echo base_url('employee/add'); ?>">
                                    <span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('add_employee'); ?></span>
                                </a>
                            </li>
                        <?php } if(get_permission('employee_disable_authentication', 'is_view')){ ?>
                            <li class="<?php if ($sub_page == 'employee/disable_authentication') echo 'nav-active'; ?>">
                                <a href="<?php echo base_url('employee/disable_authentication'); ?>">
                                    <span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('login_deactivate'); ?></span>
                                </a>
                            </li>
                        <?php } ?>
						
                        </ul>
                    </li>
                    <?php } ?>

					<?php
					if(get_permission('salary_template', 'is_view') ||
					get_permission('salary_assign', 'is_view') ||
					get_permission('salary_payment', 'is_view')) {
					?>
					<!-- payroll -->
					<li class="nav-parent <?php if($main_menu == 'payroll') echo 'nav-expanded nav-active';?>">
						<a>
							<i class="far fa-address-card" aria-hidden="true"></i>
							<span><?=translate('payroll')?></span>
						</a>
						<ul class="nav nav-children">
							<?php if(get_permission('salary_template', 'is_view')){ ?>
							<li class="<?php if ($sub_page == 'payroll/salary_templete' || $sub_page == 'payroll/salary_templete_edit') echo 'nav-active';?>">
								<a href="<?=base_url('payroll/salary_template')?>">
									<span><i class="fas fa-caret-right"></i><?=translate('salary_template')?></span>
								</a>
							</li>
							<?php } if(get_permission('salary_assign', 'is_view')){ ?>
							<li class="<?php if ($sub_page == 'payroll/salary_assign') echo 'nav-active';?>">
								<a href="<?=base_url('payroll/salary_assign')?>">
									<span><i class="fas fa-caret-right"></i><?=translate('salary_assign')?></span>
								</a>
							</li>
							<?php } if(get_permission('salary_increment', 'is_view')){ ?>
							<li class="<?php if ($sub_page == 'payroll/salary_increment') echo 'nav-active';?>">
								<a href="<?=base_url('payroll/salary_increment')?>">
									<span><i class="fas fa-caret-right"></i><?=translate('salary_increment')?></span>
								</a>
							</li>
							<?php } if(get_permission('salary_payment', 'is_view')){ ?>
							<li class="<?php if ($sub_page == 'payroll/salary_payment' || $sub_page == 'payroll/adjustment_form' ||  $sub_page == 'payroll/create' || $sub_page == 'payroll/invoice') echo 'nav-active';?>">
								<a href="<?=base_url('payroll')?>">
								   <span><i class="fas fa-caret-right"></i><?=translate('salary_payment')?></span>
								</a>
							</li>
							<?php } ?>
						</ul>
					</li>
					<?php } ?>
					<?php
					if(get_permission('advance_salary_manage', 'is_view') ||
					get_permission('advance_salary_request', 'is_view')) {
					?>
					<!-- advance salary managements -->
					<li class="nav-parent <?php
					if ($main_menu == 'advance_salary') echo 'nav-expanded nav-active';?>">
						<a>
							<i class="fas fa-funnel-dollar" aria-hidden="true"></i>
							<span><?=translate('advance_salary')?></span>
						</a>
						<ul class="nav nav-children">
							<?php if(get_permission('advance_salary_request', 'is_view')){ ?>
							<li class="<?php if ($sub_page == 'advance_salary/request') echo 'nav-active';?>">
								<a href="<?=base_url('advance_salary/request')?>">
									<span><i class="fas fa-caret-right"></i><?=translate('my_application')?></span>
								</a>
							</li>
							<?php } if(get_permission('advance_salary_manage', 'is_view')){ ?>
							<li class="<?php if ($sub_page == 'advance_salary/index') echo 'nav-active';?>">
								<a href="<?=base_url('advance_salary')?>">
									<span><i class="fas fa-caret-right"></i><?=translate('manage_application')?></span>
								</a>
							</li>
							<?php } ?>
						</ul>
					</li>
					<?php } ?> 
					
					<?php
					if(get_permission('fund_requisition_category', 'is_view') ||
					get_permission('fund_requisition_manage', 'is_view') ||
					get_permission('fund_requisition_request', 'is_view')) {
					?>
					<!-- fund_requisition managements -->
					<li class="nav-parent <?php
					if ($main_menu == 'fund_requisition') echo 'nav-expanded nav-active';?>">
						<a>
							<i class="fas fa-hand-holding-usd" aria-hidden="true"></i>
							<span><?=translate('fund_requisition')?></span>
						</a>
						<ul class="nav nav-children">
							<?php if(get_permission('fund_requisition_category', 'is_view')){ ?>
							<li class="<?php if ($sub_page == 'fund_requisition/category') echo 'nav-active';?>">
								<a href="<?=base_url('fund_requisition/category')?>">
									<span><i class="fas fa-caret-right"></i><?=translate('category')?></span>
								</a>
							</li>
							<?php } if(get_permission('fund_requisition_request', 'is_view')){ ?>
							<li class="<?php if ($sub_page == 'fund_requisition/request') echo 'nav-active';?>">
								<a href="<?=base_url('fund_requisition/request')?>">
									<span><i class="fas fa-caret-right"></i><?=translate('my_application')?></span>
								</a>
							</li>
							<?php } if(get_permission('fund_requisition_manage', 'is_view')){ ?>
							<li class="<?php if ($sub_page == 'fund_requisition/index') echo 'nav-active';?>">
								<a href="<?=base_url('fund_requisition')?>">
									<span><i class="fas fa-caret-right"></i><?=translate('manage_application')?></span>
								</a>
							</li>
							<?php } ?>
						</ul>
					</li>
					<?php } ?>
					
					<?php
					if(get_permission('leave_category', 'is_view') ||
					get_permission('leave_manage', 'is_view') ||
					get_permission('leave_request', 'is_view')) {
					?>
					<!-- leave managements -->
					<li class="nav-parent <?php
					if ($main_menu == 'leave') echo 'nav-expanded nav-active';?>">
						<a>
							<i class="fas fa-umbrella-beach" aria-hidden="true"></i>
							<span><?=translate('leave')?></span>
						</a>
						<ul class="nav nav-children">
						<?php if(get_permission('leave_category', 'is_view')){ ?>
							<li class="<?php if ($sub_page == 'leave/category') echo 'nav-active';?>">
								<a href="<?=base_url('leave/category')?>">
									<span><i class="fas fa-caret-right"></i><?=translate('category')?></span>
								</a>
							</li>
						<?php } if(get_permission('leave_request', 'is_view')){ ?>
							<li class="<?php if ($sub_page == 'leave/request') echo 'nav-active';?>">
								<a href="<?=base_url('leave/request')?>">
									<span><i class="fas fa-caret-right"></i><?=translate('my_application')?></span>
								</a>
							</li>
						<?php } if(get_permission('leave_manage', 'is_view')){ ?>
							<li class="<?php if ($sub_page == 'leave/index') echo 'nav-active';?>">
								<a href="<?=base_url('leave')?>">
									<span><i class="fas fa-caret-right"></i><?=translate('manage_application')?></span>
								</a>
							</li>
						<?php } ?>
						</ul>
					</li>
					<?php } ?>
	  
					<?php
						if(get_permission('attendance_approval', 'is_add') ||
						get_permission('manage_attendance', 'is_add')) {
					?>
					<!-- attendance -->
					  <li class="nav-parent <?php
							if ($main_menu == 'attendance') echo 'nav-expanded nav-active';?>">
								<a>
									<i class="fas fa-user-check"></i><span><?=translate('attendance')?></span>
								</a>
					
						<ul class="nav nav-children">
							<?php if (get_permission('attendance_approval', 'is_add')) {  ?>
							<li class="<?php if ($sub_page == 'attendance/attendance_approval') echo 'nav-active';?>">
								<a href="<?=base_url('attendance/attendance_approval')?>">
									<span><i class="fas fa-caret-right"></i><?=translate('attendance_approval')?></span>
								</a>
							</li>
							<?php } if (get_permission('manage_attendance', 'is_add')) {  ?>
							<li class="<?php if ($sub_page == 'attendance/manage_entries') echo 'nav-active';?>">
								<a href="<?=base_url('attendance/manage_attendances')?>">
									<span><i class="fas fa-caret-right"></i><?=translate('manage_attendances')?></span>
								</a>
							</li>
							<?php } ?>
						</ul>
					</li>
					<?php } ?>
			
					<?php
						if(get_permission('assets', 'is_view') ||
						get_permission('assets_category', 'is_view')) {
					?>
					<!-- Assets -->
					<li class="nav-parent <?php
						if ($main_menu == 'assets') echo 'nav-expanded nav-active';?>">
							<a>
								<i class="fas fa-cog"></i><span><?=translate('Assets')?></span>
							</a>
				
					<ul class="nav nav-children">
						<?php if (get_permission('assets', 'is_view')) {  ?>
						<li class="<?php if ($sub_page == 'assets/index' || $sub_page == 'assets/edit' ) echo 'nav-active';?>">
							<a href="<?=base_url('assets/lists')?>">
								<span><i class="fas fa-caret-right"></i><?=translate('all_assets')?></span>
							</a>
						</li>
						<?php } if (get_permission('assets_category', 'is_view')) {  ?>
						<li class="<?php if ($sub_page == 'assets/category') echo 'nav-active';?>">
							<a href="<?=base_url('assets/category')?>">
								<span><i class="fas fa-caret-right"></i><?=translate('assets_category')?></span>
							</a>
						</li>
					   
						<?php } ?>
					</ul>
				</li>
				<?php } ?>
				<?php
					if(get_permission('organization_chart', 'is_view') ||
					get_permission('organization_chart', 'is_add')) {
					?>
					<!-- Employees -->
					<li class="nav-parent <?php if ($main_menu == 'organization_chart') echo 'nav-expanded nav-active'; ?>">
					<a><i class="fas fa-sitemap"></i><span><?php echo translate('organizational_chart'); ?></span></a>
						<ul class="nav nav-children">
						<?php if(get_permission('organization_chart', 'is_add')){ ?>
							<li class="<?php if ($sub_page == 'organization_chart/index' ) echo 'nav-active'; ?>">
								<a href="<?php echo base_url('organization_chart'); ?>">
									<span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('Hierarchy Configuration'); ?></span>
								</a>
							</li>
						<?php } if(get_permission('organization_chart', 'is_view')){ ?>
							<li class="<?php if ($sub_page == 'organization_chart/chart') echo 'nav-active'; ?>">
								<a href="<?php echo base_url('organization_chart/chart'); ?>">
									<span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('chart'); ?></span>
								</a>
							</li>
						<?php } ?>
			
						</ul>
					</li>
					<?php } ?>
					
					<?php
						if(get_permission('certificate_templete', 'is_view') ||
						get_permission('generate_certificate', 'is_view')) {
						?>
					<!-- certificate -->
					 <li class="nav-parent <?php if ($main_menu == 'certificate') echo 'nav-expanded nav-active';?>">
						<a>
							<i class="icons icon-social-spotify"></i><span><?=translate('certificate')?></span>
						</a>
						<ul class="nav nav-children">
							<?php if(get_permission('certificate_templete', 'is_view')){ ?>
							<li class="<?php if ($sub_page == 'certificate/index' || $sub_page == 'certificate/edit') echo 'nav-active'; ?>">
								<a href="<?php echo base_url('certificate'); ?>">
									<span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('certificate') . " " .  translate('templete'); ?></span>
								</a>
							</li>
							<?php } if(get_permission('generate_certificate', 'is_view')){ ?>
							<li class="<?php if ($sub_page == 'certificate/generate_employee') echo 'nav-active'; ?>">
								<a href="<?php echo base_url('certificate/generate'); ?>">
									<span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('generate') . " " .  translate('certificate'); ?></span>
								</a>
							</li>
							<?php } ?>
						</ul>
					</li>
					<?php } ?>
							
					<?php
						if(get_permission('cashbook_manage', 'is_view') ||
						get_permission('cashbook_accounts', 'is_view')) {
						?>
					<!-- Cashbook Management -->
					 <li class="nav-parent <?php if ($main_menu == 'cashbook') echo 'nav-expanded nav-active';?>">
						<a>
							<i class="fas fa-money-bill-wave"></i><span><?=translate('cashbook')?></span>
						</a>
						<ul class="nav nav-children">
							<?php if(get_permission('cashbook_accounts', 'is_view')){ ?>
							<li class="<?php if ($sub_page == 'cashbook/accounts') echo 'nav-active'; ?>">
								<a href="<?php echo base_url('cashbook/accounts'); ?>">
									<span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('accounts type'); ?></span>
								</a>
							</li>
							<?php } if(get_permission('cashbook_manage', 'is_view')){ ?>
							<li class="<?php if ($sub_page == 'cashbook/index') echo 'nav-active'; ?>">
								<a href="<?php echo base_url('cashbook'); ?>">
									<span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('all') . " " .  translate('transactions'); ?></span>
								</a>
							</li>
							<?php } if(get_permission('cashbook_manage', 'is_view')){ ?>
							<li class="<?php if ($sub_page == 'cashbook/expense') echo 'nav-active'; ?>">
								<a href="<?php echo base_url('cashbook/expense'); ?>">
									<span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('all expenses'); ?></span>
								</a>
							</li>
							<?php } if(get_permission('cashbook_manage', 'is_view')){ ?>
							<li class="<?php if ($sub_page == 'cashbook/income') echo 'nav-active'; ?>">
								<a href="<?php echo base_url('cashbook/income'); ?>">
									<span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('all sales revenue'); ?></span>
								</a>
							</li>
							<?php } if(get_permission('cashbook_manage', 'is_view')){ ?>
							<li class="<?php if ($sub_page == 'cashbook/dues') echo 'nav-active'; ?>">
								<a href="<?php echo base_url('cashbook/dues'); ?>">
									<span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('dues_report'); ?></span>
								</a>
							</li>
							<?php } if(get_permission('cashbook_manage', 'is_view')){ ?>
							<li class="<?php if ($sub_page == 'cashbook/report') echo 'nav-active'; ?>">
								<a href="<?php echo base_url('cashbook/report'); ?>">
									<span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('profit & loss report'); ?></span>
								</a>
							</li>
							<?php } if(get_permission('cashbook_manage', 'is_view')){ ?>
							<li class="<?php if ($sub_page == 'cashbook/monthly_sales_revenue') echo 'nav-active'; ?>">
								<a href="<?php echo base_url('cashbook/monthly_sales_revenue'); ?>">
									<span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('monthly_sales_revenue'); ?></span>
								</a>
							</li>
							<?php } ?>
						</ul>
					</li>
					<?php } ?>
							
					<?php if(get_permission('employee_award', 'is_view')){ ?>
					<li class="<?php if ($main_menu == 'employee_award') echo 'nav-expanded nav-active';?>">
						 <a href="<?php echo base_url('employee/employee_award'); ?>">
						   <i class="fas fa-medal"></i><span><?=translate('employee_award')?></span>
						</a>
					</li>
					<?php } ?>
						 
					<?php if(get_permission('contact_info', 'is_view')){ ?>
					<li class="<?php if ($main_menu == 'contact_info') echo 'nav-expanded nav-active';?>">
						 <a href="<?php echo base_url('contact_info'); ?>">
						   <i class="fas fa-address-book"></i><span><?=translate('contact_info')?></span>
						</a>
					</li>
					 <?php }?>
					
					<!-- Goals Dashboard -->
					<li class="<?php if ($main_menu == 'goals') echo 'nav-expanded nav-active';?>">
						<a href="<?php echo base_url('goals/manage'); ?>">
							<i class="fas fa-bullseye"></i><span><?=translate('manage goals')?></span>
						</a>
					</li>
					
                        </ul>
                    </li>
                    <?php }} ?>
					
					<?php
                    if(get_permission('policy', 'is_view') ||
						get_permission('policy', 'is_edit') ||
						get_permission('policy_category', 'is_view') ||
						get_permission('policy', 'is_add'))  :
                    ?>
						<li class="nav-parent <?php if ($main_menu == 'library' || $main_menu == 'blacklist') echo 'nav-expanded nav-active'; ?>">
                        <a><i class="icons icon-notebook"></i><span><?=translate('Rules & Policy')?></span></a>
                        <ul class="nav nav-children">
                        
						   <?php
							if(get_permission('policy', 'is_view') ||
							get_permission('policy', 'is_edit') ||
							get_permission('policy_category', 'is_view') ||
							get_permission('policy', 'is_add')) {
							?>
							<!-- library -->
							  <?php if (get_permission('policy', 'is_add')) {  ?>
								<li class="<?php if ($sub_page == 'library/book') echo 'nav-active';?>">
									<a href="<?=base_url('library/book')?>">
										<span><i class="fas fa-caret-right"></i><?=translate('add_rules_&_policy')?></span>
									</a>
								</li>
								<?php } if (get_permission('policy_category', 'is_view')) {  ?>
								<li class="<?php if ($sub_page == 'library/category') echo 'nav-active';?>">
									<a href="<?=base_url('library/category')?>">
										<span><i class="fas fa-caret-right"></i><?=translate('category')?></span>
									</a>
								</li>
								<?php } if (get_permission('policy', 'is_view')) { ?>

								<li class="<?php if ($sub_page == 'library/by_category' || $sub_page == 'library/book_edit' ) echo 'nav-active';?>">
									<a href="<?=base_url('library/categorized_view')?>">
										<span><i class="fas fa-caret-right"></i><?=translate('company_rules & policy')?></span>
									</a>
								</li>
							   
								<?php } ?>
							<?php } ?>
							
                        </ul>
                    </li>
					
					<?php endif; ?>
					
					
					<?php
                    if(get_permission('separation', 'is_view') ||
                    get_permission('separation', 'is_add')) :
                    ?>
					
						 <li class="nav-parent <?php if ($main_menu == 'separation') echo 'nav-expanded nav-active'; ?>">
                        <a><i class="fas fa-user-minus"></i><span><?php echo translate('separation'); ?></span></a>
                        <ul class="nav nav-children">
                        <?php if(get_permission('separation', 'is_add')){ ?>
                            <li class="<?php if ($sub_page == 'separation/index' ) echo 'nav-active'; ?>">
                                <a href="<?php echo base_url('separation'); ?>">
                                    <span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('My Application'); ?></span>
                                </a>
                            </li>
                        <?php } if(get_permission('separation', 'is_view')){ ?>
                            <li class="<?php if ($sub_page == 'separation/lists') echo 'nav-active'; ?>">
                                <a href="<?php echo base_url('separation/lists'); ?>">
                                    <span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('Manage Application'); ?></span>
                                </a>
                            </li>
                        <?php } ?>
						
                        </ul>
                    </li>
					
					<?php endif; ?>
					
					
					
					<?php
                    if(get_permission('probation', 'is_view') ||
                    get_permission('probation', 'is_add')) :
                    ?>
					
						 <li class="nav-parent <?php if ($main_menu == 'probation') echo 'nav-expanded nav-active'; ?>">
                        <a><i class="fas fa-user-clock"></i><span><?php echo translate('probation'); ?></span></a>
                        <ul class="nav nav-children">
                        <?php if(get_permission('probation', 'is_add')){ ?>
                            <li class="<?php if ($sub_page == 'probation/index' ||  $sub_page == 'probation/profile') echo 'nav-active'; ?>">
                                <a href="<?php echo base_url('probation'); ?>">
                                    <span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('evaluation'); ?></span>
                                </a>
                            </li>
                        <?php } if(get_permission('probation', 'is_view')){ ?>
                            <li class="<?php if ($sub_page == 'probation/acknowledgement') echo 'nav-active'; ?>">
                                <a href="<?php echo base_url('probation/acknowledgement'); ?>">
                                    <span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('acknowledgement'); ?></span>
                                </a>
                            </li>
                        <?php } ?>
						
                        </ul>
                    </li>
					
					<?php endif; ?>
				
					<?php
                        if(get_permission('ppm', 'is_view') ||
                        get_permission('objectives_kpi', 'is_view') ||
                        get_permission('objectives_kpi', 'is_add')) {
                        ?>
                    <!-- office accounting -->
                    <li class="nav-parent <?php if ($main_menu == 'ppm') echo 'nav-expanded nav-active';?>">
                        <a>
                            <i class="fas fa-chart-line"></i><span><?=translate('ppm')?></span>
                        </a>
                        <ul class="nav nav-children">
                            <?php if(get_permission('objectives_kpi', 'is_add')){ ?>
                                <li class="<?php if ($sub_page == 'ppm/objectives_kpi') echo 'nav-active'; ?>">
                                    <a href="<?php echo base_url('kpi'); ?>">
                                        <span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('add_objectives_kpi'); ?></span>
                                    </a>
                                </li> 
                            <?php } if(get_permission('objectives_kpi', 'is_view')){ ?>
                            <li class="<?php if ($sub_page == 'ppm/index' ||  $sub_page == 'ppm/profile' ) echo 'nav-active'; ?>">
                                <a href="<?php echo base_url('kpi/view'); ?>">
                                    <span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('employee_objectives'); ?></span>
                                </a>
                            </li>
                            
                           
                            <?php } ?>
                        </ul>
                    </li>
                    <?php } ?>
					 
					 
                    <li class="<?php if ($main_menu == 'tracker') echo 'nav-expanded nav-active';?>">
                         <a href="<?php echo base_url('tracker'); ?>">
						   <i class="fa-solid fa-bars-progress"></i><span><?=translate('tracker')?></span>
						</a>
                    </li>

                    <!-- Shipment Management -->
                    <?php if (get_permission('shipment_management', 'is_view')): ?>
                    <li class="nav-parent <?php if ($main_menu == 'shipment') echo 'nav-expanded nav-active'; ?>">
                        <a><i class="fas fa-shipping-fast"></i><span><?php echo translate('shipment_management'); ?></span></a>
                        <ul class="nav nav-children">
                            <li class="<?php if ($sub_page == 'shipment/index') echo 'nav-active'; ?>">
                                <a href="<?php echo base_url('shipment'); ?>">
                                    <span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('all_shipments'); ?></span>
                                </a>
                            </li>
                            <li class="<?php if ($sub_page == 'shipment/track') echo 'nav-active'; ?>">
                                <a href="<?php echo base_url('shipment/track'); ?>">
                                    <span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('track_shipment'); ?></span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>
					 
				   <?php if (get_permission('event', 'is_view')) {
                        ?>
					 <?php if(get_permission('event', 'is_view')){ ?>
                    <li class="<?php if ($main_menu == 'event') echo 'nav-expanded nav-active';?>">
                         <a href="<?php echo base_url('event'); ?>">
						   <i class="fas fa-calendar-day"></i><span><?=translate('calendar')?></span>
						</a>
                    </li>
					 <?php } }?>
					 
				   <?php if (get_permission('todo', 'is_view')) { ?>
					 <?php if(get_permission('todo', 'is_view')){ ?>
                    <li class="<?php if ($main_menu == 'todo') echo 'nav-expanded nav-active';?>">
                         <a href="<?php echo base_url('todo'); ?>">
						   <i class="fas fa-exclamation-triangle text-warning"></i><span><?=translate('To-Do')?></span>
						</a>
                    </li>
					 <?php } }?>
					
					<?php if (get_permission('team_meetings', 'is_view')) { ?>
					 <?php if(get_permission('team_meetings', 'is_view')){ ?>
                    <li class="<?php if ($main_menu == 'team_meetings') echo 'nav-expanded nav-active';?>">
                         <a href="<?php echo base_url('team_meetings'); ?>">
						   <i class="fas fa-users"></i><span><?=translate('team_meetings')?></span>
						</a>
                    </li>
					 <?php } }?>
					
					<?php
                    if((get_permission('sop_management', 'is_view') || get_permission('sop_management', 'is_add')) ||
                       (get_permission('rdc_management', 'is_view') || get_permission('rdc_management', 'is_add') ||
                        get_permission('rdc_escalations', 'is_view') || get_permission('rdc_salary_blocks', 'is_view') ||
                        get_permission('rdc_configuration', 'is_view'))) :
                    ?>
						<li class="nav-parent <?php if ($main_menu == 'sop' || $main_menu == 'rdc') echo 'nav-expanded nav-active'; ?>">
                        <a><i class="fas fa-cogs"></i><span><?php echo translate('Workflow Management'); ?></span></a>
                        <ul class="nav nav-children">
                        
                        <?php if(get_permission('sop_management', 'is_view') || get_permission('sop_management', 'is_add')){ ?>
                            <!-- SOP Management -->
                            <li class="nav-parent <?php if ($main_menu == 'sop') echo 'nav-expanded nav-active'; ?>">
                                <a><i class="fas fa-file-alt"></i><span><?php echo translate('SOP Management'); ?></span></a>
                                <ul class="nav nav-children">
                                <?php if(get_permission('sop_management', 'is_add')){ ?>
                                    <li class="<?php if ($sub_page == 'sop/create' ) echo 'nav-active'; ?>">
                                        <a href="<?php echo base_url('sop/create'); ?>">
                                            <span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('create'); ?></span>
                                        </a>
                                    </li>
                                <?php } if(get_permission('sop_management', 'is_view')){ ?>
                                    <li class="<?php if ($sub_page == 'sop/index' || $sub_page == 'sop/edit') echo 'nav-active'; ?>">
                                        <a href="<?php echo base_url('sop'); ?>">
                                            <span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('sop_list'); ?></span>
                                        </a>
                                    </li>
                                <?php } if(get_permission('sop_management', 'is_view')){ ?>
                                    <li class="<?php if ($sub_page == 'sop/logs') echo 'nav-active'; ?>">
                                        <a href="<?php echo base_url('sop/logs'); ?>">
                                            <span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('logs'); ?></span>
                                        </a>
                                    </li>
                                <?php } ?>
                                </ul>
                            </li>
                        <?php } ?>
                        
                        <?php if(get_permission('rdc_management', 'is_view') || get_permission('rdc_management', 'is_add') ||
                                 get_permission('rdc_escalations', 'is_view') || get_permission('rdc_salary_blocks', 'is_view') ||
                                 get_permission('rdc_configuration', 'is_view')){ ?>
                            <!-- RDC Management -->
                            <li class="nav-parent <?php if ($main_menu == 'rdc') echo 'nav-expanded nav-active'; ?>">
                                <a><i class="fas fa-tasks"></i><span><?php echo translate('RDC Management'); ?></span></a>
                                <ul class="nav nav-children">
                                <?php if(get_permission('rdc_management', 'is_add')){ ?>
                                    <li class="<?php if ($sub_page == 'rdc/create' ) echo 'nav-active'; ?>">
                                        <a href="<?php echo base_url('rdc/create'); ?>">
                                            <span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('create_template'); ?></span>
                                        </a>
                                    </li>
                                <?php } if(get_permission('rdc_management', 'is_add')){ ?>
                                    <li class="<?php if ($sub_page == 'rdc/templates' || $sub_page == 'rdc/edit') echo 'nav-active'; ?>">
                                        <a href="<?php echo base_url('rdc/templates'); ?>">
                                            <span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('rdc_templates'); ?></span>
                                        </a>
                                    </li>
                                <?php } if(get_permission('rdc_management', 'is_view')){ ?>
                                    <li class="<?php if ($sub_page == 'rdc/index') echo 'nav-active'; ?>">
                                        <a href="<?php echo base_url('rdc'); ?>">
                                            <span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('rdc_task'); ?></span>
                                        </a>
                                    </li>
                                <?php } if(get_permission('rdc_escalations', 'is_view')){ ?>
                                    <li class="<?php if ($sub_page == 'rdc/escalations') echo 'nav-active'; ?>">
                                        <a href="<?php echo base_url('rdc/escalations'); ?>">
                                            <span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('escalations'); ?></span>
                                        </a>
                                    </li>
                                <?php } if(get_permission('rdc_configuration', 'is_view')){ ?>
                                    <li class="<?php if ($sub_page == 'rdc/configuration') echo 'nav-active'; ?>">
                                        <a href="<?php echo base_url('rdc/configuration'); ?>">
                                            <span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('configuration'); ?></span>
                                        </a>
                                    </li>
                                <?php } if(get_permission('rdc_escalations', 'is_view')){ ?>
                                    <li class="<?php if ($sub_page == 'rdc/logs') echo 'nav-active'; ?>">
                                        <a href="<?php echo base_url('rdc/logs'); ?>">
                                            <span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('escalation_logs'); ?></span>
                                        </a>
                                    </li>
                               <?php } if(get_permission('rdc_salary_blocks', 'is_view')){ ?>
                                    <li class="<?php if ($sub_page == 'blocked_salary/index') echo 'nav-active'; ?>">
                                        <a href="<?php echo base_url('blocked_salary'); ?>">
                                            <span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('blocked_salaries'); ?></span>
                                        </a>
                                    </li>
								<?php } if (loggedin_role_id() == 1) { ?>
                                    <li class="<?php if ($sub_page == 'rdc/deleted_tasks') echo 'nav-active'; ?>">
                                        <a href="<?php echo base_url('rdc/deleted_tasks'); ?>">
                                            <span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('deleted_tasks'); ?></span>
                                        </a>
                                    </li>
                                <?php } ?>
                                </ul>
                            </li>
                        <?php } ?>
                        
                        </ul>
                    </li>
					
					<?php endif; ?>
					
					
					<!--Reports -->
                    <?php 
                    if(get_permission('salary_summary_report', 'is_view') ||
                    get_permission('leave_reports', 'is_view') ||
                    get_permission('employee_attendance_report', 'is_view') ||
                    ($attendance_report == true)) {
                    ?>
                    <!-- reports -->
                    <li class="nav-parent <?php if ($main_menu == 'attendance_report' ||
					$main_menu == 'payroll_reports' || $main_menu == 'salary_sheet' || $main_menu == 'penalty_report' || $main_menu == 'rdc_reports' || $main_menu == 'champion_badges' || $main_menu == 'leave_reports') echo 'nav-expanded nav-active';?>">
                        <a>
                            <i class="icons icon-pie-chart icons"></i><span><?=translate('reports')?></span>
                        </a>
                        <ul class="nav nav-children">
                        
						 <?php if(get_permission('employee_attendance_report', 'is_view')) { ?>
						     <!-- attendance control -->
						<li class="<?php if ($main_menu == 'attendance_report') echo 'nav-expanded nav-active';?>">
						 
							 <?php if(get_permission('employee_attendance_report', 'is_view')) { ?>
								<li class="<?php if ($sub_page == 'attendance/employees_report') echo 'nav-active';?>">
									<a href="<?=base_url('attendance/employeewise_report')?>">
									   <i class="fas fa-print"></i><span><?=translate('attendance_report')?></span>
									</a>
								</li>
							   
								<?php } ?>
						</li>
						<?php } ?>
						
						 <?php if(get_permission('penalty_report', 'is_view')) { ?>
						     <!-- attendance control -->
						<li class="<?php if ($main_menu == 'penalty_report') echo 'nav-expanded nav-active';?>">
						 
							 <?php if(get_permission('penalty_report', 'is_view')) { ?>
								<li class="<?php if ($sub_page == 'todo/reports') echo 'nav-active';?>">
									<a href="<?=base_url('todo/reports')?>">
									   <i class="fas fa-print"></i><span><?=translate('penalty_report')?></span>
									</a>
								</li>
							   
								<?php } ?>
						</li>
						<?php } ?>	
						
						 <?php if(get_permission('rdc_management', 'is_view')) { ?>
						     <!-- attendance control -->
						<li class="<?php if ($main_menu == 'rdc_reports') echo 'nav-expanded nav-active';?>">
						 
							 <?php if(get_permission('rdc_management', 'is_view')) { ?>
								<li class="<?php if ($sub_page == 'rdc/reports') echo 'nav-active';?>">
									<a href="<?=base_url('rdc/reports')?>">
									   <i class="fas fa-print"></i><span><?=translate('pending_tasks')?></span>
									</a>
								</li>
							   
								<?php } ?>
						</li>
						<?php } ?>
						
						 <?php if(get_permission('champion_badges', 'is_view')) { ?>
						     <!-- attendance control -->
						<li class="<?php if ($main_menu == 'champion_badges') echo 'nav-expanded nav-active';?>">
						 
							 <?php if(get_permission('champion_badges', 'is_view')) { ?>
								<li class="<?php if ($sub_page == 'tracker/champion_badges') echo 'nav-active';?>">
									<a href="<?=base_url('tracker/champion_badges')?>">
									   <i class="fas fa-print"></i><span><?=translate('champion_badges')?></span>
									</a>
								</li>
							   
								<?php } ?>
						</li>
						<?php } ?>
		
						
                        <?php 
                        if (moduleIsEnabled('human_resource')) {
                            if(get_permission('salary_summary_report', 'is_view') || get_permission('leave_reports', 'is_view')){ ?>
                            <li class="nav-parent <?php if ($main_menu == 'payroll_reports' || $main_menu == 'salary_sheet' || $main_menu == 'leave_reports') echo 'nav-expanded nav-active'; ?>">
                                <a><i class="fas fa-print"></i><span><?php echo translate('hrm'); ?></span></a>
                                <ul class="nav nav-children">
                                    <?php if(get_permission('salary_summary_report', 'is_view')){ ?>
                                    <li class="<?php if ($sub_page == 'payroll/salary_statement') echo 'nav-active';?>">
                                        <a href="<?=base_url('payroll/salary_statement')?>">
                                            <span><i class="fas fa-caret-right"></i><?=translate('payroll_summary')?></span>
                                        </a>
                                    </li>
                                    <?php } if (get_permission('salary_sheet', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'payroll/salary_sheet') echo 'nav-active';?>">
                                        <a href="<?=base_url('payroll/salary_sheet')?>">
                                            <span><i class="fas fa-caret-right"></i><?=translate('salary') . " " . translate('sheet_analysis')?></span>
                                        </a>
                                    </li>
                                    <?php } if (get_permission('leave_reports', 'is_view')) { ?>
                                    <li class="<?php if ($sub_page == 'leave/reports') echo 'nav-active';?>">
                                        <a href="<?=base_url('leave/reports')?>">
                                            <span><i class="fas fa-caret-right"></i><?=translate('leave') . " " . translate('reports')?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </li>
                        <?php }} ?>
                       
                        </ul>
                    </li>
                    <?php } ?>

                    <?php
                    if (get_permission('global_settings', 'is_view') ||
                    get_permission('backup', 'is_view')||
                    get_permission('user_login_log', 'is_view')||
                    get_permission('breaks', 'is_view')||
                    get_permission('email', 'is_view')) {
                    ?>
                    <!-- setting -->
                    <li class="nav-parent <?php if ($main_menu == 'settings' || $main_menu == 'branch'  || $main_menu == 'email') echo 'nav-expanded nav-active';?>">
                        <a>
                            <i class="icons icon-briefcase"></i><span><?=translate('settings')?></span>
                        </a>
                        <ul class="nav nav-children">
                            <?php if(get_permission('global_settings', 'is_view')){ ?>
                            <li class="<?php if($sub_page == 'settings/universal') echo 'nav-active';?>">
                                <a href="<?=base_url('settings/universal')?>">
                                    <span><i class="fas fa-caret-right" aria-hidden="true"></i><?=translate('global_settings')?></span>
                                </a>
                            </li>
							 <!-- business_settings item -->
							<?php } if (in_array(loggedin_role_id(), [1, 2, 3, 5])) { ?>
                                    <li class="<?php if ($main_menu == 'branch') echo 'nav-active'; ?>">
                                        <a href="<?=base_url('branch')?>">
                                             <span><i class="fas fa-caret-right" aria-hidden="true"></i><?=translate('business_settings')?></span>
                                        </a>
                                    </li>
                                <!-- New type management item -->
							<?php } if (get_permission('email', 'is_view')) { ?>
                                    <li class="<?php if ($main_menu == 'email') echo 'nav-active'; ?>">
                                        <a href="<?=base_url('email')?>">
                                             <span><i class="fas fa-caret-right" aria-hidden="true"></i><?=translate('email_settings')?></span>
                                        </a>
                                    </li>
                               
							<?php } if(get_permission('breaks', 'is_view') ){ ?>
							<li class="<?php if ($sub_page == 'employee/breaks' || $sub_page == 'employee/break_history') echo 'nav-active'; ?>">
								<a href="<?php echo base_url('employee/staff_break_history'); ?>">
									<span><i class="fas fa-caret-right" aria-hidden="true"></i><?php echo translate('breaks'); ?></span>
								</a>
							</li>
							
                            <?php } if (is_superadmin_loggedin()) { ?>
                            <li class="<?php if ($sub_page == 'role/index' || $sub_page == 'role/permission') echo 'nav-active';?>">
                                <a href="<?=base_url('role')?>">
                                    <span><i class="fas fa-caret-right" aria-hidden="true"></i><?=translate('role_permission')?></span>
                                </a>
                            </li>
							
                            <?php } if(get_permission('backup', 'is_view')){ ?>
                            <li class="<?php if ($sub_page == 'database_backup/index') echo 'nav-active';?>">
                                <a href="<?=base_url('backup')?>">
                                    <span><i class="fas fa-caret-right" aria-hidden="true"></i><?=translate('database_backup')?></span>
                                </a>
                            </li>
                            
                            <?php } if(get_permission('user_login_log', 'is_view')){ ?>
                            <li class="<?php if ($sub_page == 'user_login_log/index') echo 'nav-active';?>">
                                <a href="<?=base_url('user_login_log/index')?>">
                                    <span><i class="fas fa-caret-right" aria-hidden="true"></i><?=translate('user_login_log')?></span>
                                </a>
                            </li>
                            <?php } ?>

                        </ul>
                    </li>
                    <?php } ?>
                </ul>
            </nav>
        </div>
        <script>
            // maintain scroll position
            if (typeof localStorage !== 'undefined') {
                if (localStorage.getItem('sidebar-left-position') !== null) {
                    var initialPosition = localStorage.getItem('sidebar-left-position'),
                        sidebarLeft = document.querySelector('#sidebar-left .nano-content');
                    sidebarLeft.scrollTop = initialPosition;
                }
            }
        </script>
    </div>
</aside>
<!-- end sidebar -->