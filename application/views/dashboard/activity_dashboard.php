<style>
.activity-dashboard {
    background: #f8fafb;
    min-height: 100vh;
    padding: 20px;
}

.activity-card, .tracking-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid #e8ecf4;
    transition: all 0.3s ease;
    height: 550px;
    display: flex;
    flex-direction: column;
}

.activity-card:hover, .tracking-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.card-content {
    flex: 1;
    overflow-y: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.card-content::-webkit-scrollbar {
    display: none;
}

.reports-table {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    border: 1px solid #e8ecf4;
    overflow: hidden;
}

.table-header {
    color: #2d3748;
    padding: 20px 25px;
    font-size: 18px;
    font-weight: 600;
}

.table-responsive {
    overflow-x: hidden;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.table-responsive::-webkit-scrollbar {
    display: none;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
    margin: 0;
}

.data-table th {
    background: #f8f9fa;
    padding: 12px 10px;
    text-align: left;
    font-weight: 600;
    color: #2d3748;
    border-bottom: 2px solid #e2e8f0;
    word-wrap: break-word;
}

.data-table td {
    padding: 12px 10px;
    border-bottom: 1px solid #e2e8f0;
    color: #4a5568;
    word-wrap: break-word;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* CDR and Email table widths */
.data-table th:nth-child(1) { width: 12%; }
.data-table th:nth-child(2) { width: 10%; }
.data-table th:nth-child(3) { width: 20%; }
.data-table th:nth-child(4) { width: 25%; }
.data-table th:nth-child(5) { width: 12%; }
.data-table th:nth-child(6) { width: 21%; }

/* CRM Activities table widths */
#crm .data-table th:nth-child(1) { width: 8%; }  /* Activity ID */
#crm .data-table th:nth-child(2) { width: 10%; } /* Type */
#crm .data-table th:nth-child(3) { width: 25%; } /* Subject */
#crm .data-table th:nth-child(4) { width: 10%; } /* Status */
#crm .data-table th:nth-child(5) { width: 12%; } /* Start Date */
#crm .data-table th:nth-child(6) { width: 10%; } /* Due Date */
#crm .data-table th:nth-child(7) { width: 15%; } /* Assigned To */
#crm .data-table th:nth-child(8) { width: 10%; } /* Related To */

.data-table tr:hover {
    background: #f7fafc;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-completed { background: #c6f6d5; color: #22543d; }
.status-pending { background: #fed7d7; color: #742a2a; }
.status-in-progress { background: #bee3f8; color: #2a4365; }

.section-title {
    font-size: 20px;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.activity-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    margin-bottom: 12px;
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    border-radius: 8px;
    border-left: 4px solid #4299e1;
    transition: all 0.2s ease;
}

.activity-item:hover {
    background: linear-gradient(135deg, #e6fffa 0%, #b2f5ea 100%);
    border-left-color: #38b2ac;
    transform: translateX(5px);
}

.activity-label {
    font-weight: 500;
    color: #2d3748;
    font-size: 14px;
}

.activity-count {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 13px;
    min-width: 50px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(66, 153, 225, 0.3);
}

.tracking-item {
    background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
    border: 1px solid #feb2b2;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 15px;
    transition: all 0.3s ease;
}

.tracking-item:hover {
    background: linear-gradient(135deg, #f0fff4 0%, #c6f6d5 100%);
    border-color: #9ae6b4;
    transform: scale(1.02);
}

.tracking-title {
    font-size: 16px;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.shipment-detail {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 15px;
    background: white;
    border-radius: 6px;
    margin-bottom: 8px;
    border: 1px solid #e2e8f0;
}

.shipment-name {
    font-weight: 500;
    color: #4a5568;
}

.shipment-count {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    color: white;
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
}

.shipment-code {
    font-size: 11px;
    color: #718096;
    font-style: italic;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.stat-card {
    background: linear-gradient(135deg, #e6fffa 0%, #b2f5ea 100%);
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    border: 1px solid #81e6d9;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(129, 230, 217, 0.3);
}

.stat-number {
    font-size: 24px;
    font-weight: 700;
    color: #2d3748;
    display: block;
}

.stat-label {
    font-size: 12px;
    color: #4a5568;
    margin-top: 5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.icon {
    font-size: 18px;
    margin-right: 8px;
}

.phone-icon { color: #4299e1; }
.email-icon { color: #ed8936; }
.meeting-icon { color: #9f7aea; }
.client-icon { color: #f56565; }
.support-icon { color: #48bb78; }
.shipment-icon { color: #38b2ac; }

@media (max-width: 768px) {
    .activity-dashboard {
        padding: 10px;
    }
    
    .activity-card, .tracking-card {
        height: auto;
        padding: 15px;
        margin-bottom: 15px;
    }
    
    .section-title {
        font-size: 18px;
        margin-bottom: 15px;
    }
    
    .activity-item {
        padding: 12px;
        margin-bottom: 8px;
    }
    
    .activity-label {
        font-size: 13px;
    }
    
    .activity-count {
        font-size: 12px;
        padding: 4px 8px;
        min-width: 40px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }
    
    .stat-card {
        padding: 15px;
    }
    
    .stat-number {
        font-size: 20px;
    }
    
    .stat-label {
        font-size: 11px;
    }
    
    .tracking-item {
        padding: 15px;
        margin-bottom: 10px;
    }
    
    .tracking-title {
        font-size: 14px;
    }
    
    .shipment-detail {
        padding: 8px 12px;
    }
    
    .shipment-name {
        font-size: 13px;
    }
    
    .data-table {
        font-size: 12px;
    }
    
    .data-table th, .data-table td {
        padding: 8px 6px;
    }
    
    .table-responsive {
        overflow-x: auto;
    }
    
    .nav-tabs {
        font-size: 12px;
    }
    
    .nav-tabs > li > a {
        padding: 8px 12px;
    }
}
</style>
<style>
.ceo-dashboard {
    background: #f8fafc;
    min-height: 100vh;
    padding: 20px;
}

.dashboard-subtitle {
    font-size: 16px;
    opacity: 0.9;
    margin: 5px 0 0 0;
}

.positive { color: #10b981; }
.negative { color: #ef4444; }
.neutral { color: #64748b; }

.section-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.metric-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #f1f5f9;
}

.metric-row:last-child {
    border-bottom: none;
}

.metric-label {
    font-weight: 500;
    color: #475569;
    display: flex;
    align-items: center;
    gap: 10px;
}

.metric-value {
    font-weight: 700;
    font-size: 18px;
    color: #1e293b;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: #f1f5f9;
    border-radius: 4px;
    overflow: hidden;
    margin-top: 8px;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981, #059669);
    border-radius: 4px;
    transition: width 0.3s ease;
}

.alert-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 15px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    margin-bottom: 10px;
}

.alert-icon {
    color: #dc2626;
    font-size: 16px;
}

.alert-text {
    color: #7f1d1d;
    font-weight: 500;
    font-size: 14px;
}

#tasksModal .modal-dialog {
    width: 80%;
    max-width: none;
    height: 80vh;
    margin: 10vh auto;
}

#tasksModal .modal-content {
    height: 100%;
    display: flex;
    flex-direction: column;
}

#tasksModal .modal-body {
    flex: 1;
    overflow-y: auto;
}

#taskDetailsModal .modal-dialog {
    width: 80%;
    max-width: none;
    height: 80vh;
    margin: 10vh auto;
}

#taskDetailsModal .modal-content {
    height: 100%;
    display: flex;
    flex-direction: column;
}

#taskDetailsModal .modal-body {
    flex: 1;
    overflow-y: auto;
}

@media (max-width: 768px) {
    .ceo-dashboard {
        padding: 10px;
    }
    
    .section-card {
        padding: 15px;
        margin-bottom: 15px;
    }
    
    .metric-row {
        padding: 10px 0;
    }
    
    .metric-label {
        font-size: 13px;
    }
    
    .metric-value {
        font-size: 16px;
    }
    
    #tasksModal .modal-dialog,
    #taskDetailsModal .modal-dialog {
        width: 95%;
        height: 90vh;
        margin: 5vh auto;
    }
    
    #emailModal .modal-dialog {
        width: 95%;
        margin: 10vh auto;
    }
    
    #shipmentsModal .modal-dialog {
        width: 95%;
        margin: 10vh auto;
    }
}
</style>
<div class="activity-dashboard">
    <div class="row">
        <!-- Left Side - Activities -->
        <div class="col-lg-6 col-md-12">
            <div class="activity-card">
                <h3 class="section-title">
                    <i class="fas fa-tasks icon"></i>
                    Daily Activities
                </h3>
                
                <div class="card-content">
				    <div class="activity-item" data-task-type="all" style="cursor: pointer;">
                        <span class="activity-label">
                            <i class="fas fa-tasks phone-icon"></i>
                            Tasks Generated Today
                        </span>
                        <span class="activity-count" id="total-tasks-today"><?php echo isset($total_tasks_today) ? $total_tasks_today : 0; ?></span>
                    </div>

                    
				    <div class="activity-item" data-task-type="milestone" style="cursor: pointer;">
                        <span class="activity-label">
                            <i class="fas fa-flag-checkered" style="color: #8b5cf6;"></i>
                            Milestone Tasks Today
                        </span>
                        <span class="activity-count" id="total-milestone-tasks-today"><?php echo isset($milestone_task) ? $milestone_task : 0; ?></span>
                    </div>
                    
				    <div class="activity-item" data-task-type="incident" style="cursor: pointer;">
                        <span class="activity-label">
                            <i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i>
                            Incident Tasks Today
                        </span>
                        <span class="activity-count" id="total-incident-tasks-today"><?php echo isset($incident_task) ? $incident_task : 0; ?></span>
                    </div>
                    
				    <div class="activity-item" data-task-type="customer_query" style="cursor: pointer;">
                        <span class="activity-label">
                            <i class="fas fa-question-circle" style="color: #f59e0b;"></i>
                            Customer Query Tasks Today
                        </span>
                        <span class="activity-count" id="total-customer-query-tasks-today"><?php echo isset($customer_query_task) ? $customer_query_task : 0; ?></span>
                    </div>
                    
				    <div class="activity-item" data-task-type="explore" style="cursor: pointer;">
                        <span class="activity-label">
                            <i class="fas fa-search" style="color: #10b981;"></i>
                            Explore Tasks Today
                        </span>
                        <span class="activity-count" id="total-explore-tasks-today"><?php echo isset($explore_task) ? $explore_task : 0; ?></span>
                    </div>
                    
				    <div class="activity-item" data-task-type="emp_request" style="cursor: pointer;">
                        <span class="activity-label">
                            <i class="fas fa-user-plus" style="color: #06b6d4;"></i>
                            EMP Request Tasks Today
                        </span>
                        <span class="activity-count" id="total-emp-request-tasks-today"><?php echo isset($request_task) ? $request_task : 0; ?></span>
                    </div>
                    
                    <div class="activity-item" data-task-type="email" style="cursor: pointer;">
                        <span class="activity-label">
                            <i class="fas fa-envelope email-icon"></i>
                            Email Contacts Today
                        </span>
                        <span class="activity-count" id="email-contacts-today"><?php echo isset($email_contacts_today) ? $email_contacts_today : 0; ?></span>
                    </div>
                    
                    <div class="activity-item" data-task-type="meeting" style="cursor: pointer;">
                        <span class="activity-label">
                            <i class="fas fa-users meeting-icon"></i>
                            Team Meetings
                        </span>
                        <span class="activity-count" id="meeting-minutes-today"><?php echo isset($meeting_minutes_today) ? $meeting_minutes_today : 0; ?></span>
                    </div>
                    
                    <div class="activity-item" data-task-type="physical_meeting" style="cursor: pointer;">
                        <span class="activity-label">
                            <i class="fas fa-handshake client-icon"></i>
                            Client Physical Meet
                        </span>
                        <span class="activity-count" id="client-physical-meet"><?php echo isset($client_physical_meet) ? $client_physical_meet : 0; ?></span>
                    </div>
                    
                    <div class="activity-item" data-task-type="online_meeting" style="cursor: pointer;">
                        <span class="activity-label">
                            <i class="fas fa-video client-icon"></i>
                            Client Online Meet
                        </span>
                        <span class="activity-count" id="client-online-meet"><?php echo isset($client_online_meet) ? $client_online_meet : 0; ?></span>
                    </div>
                    
                    <div class="activity-item" data-task-type="support" style="cursor: pointer;">
                        <span class="activity-label">
                            <i class="fas fa-headset support-icon"></i>
                            Support
                        </span>
                        <span class="activity-count" id="support-tasks"><?php echo isset($support_tasks) ? $support_tasks : 0; ?></span>
                    </div>
					
                    <div class="activity-item" data-task-type="billing" style="cursor: pointer;">
                        <span class="activity-label">
                            <i class="fas fa-coins" style="color: #f59e0b;"></i>
                            Billing
                        </span>
                        <span class="activity-count" id="billing-tasks"><?php echo isset($billing_tasks) ? $billing_tasks : 0; ?></span>
                    </div>
                </div>

                <!-- Activity Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <span class="stat-number" id="total-activities"><?php echo isset($total_activities_today) ? $total_activities_today : 0; ?></span>
                        <span class="stat-label">Total Activities</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number" id="completion-rate"><?php echo isset($completion_rate_today) ? $completion_rate_today : 0; ?>%</span>
                        <span class="stat-label">Completion Rate</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Tracking -->
        <div class="col-lg-6 col-md-12">
            <div class="tracking-card">
                <h3 class="section-title">
                    <i class="fas fa-shipping-fast shipment-icon"></i>
                    Shipment Tracking
                </h3>
                
                <div class="card-content">
                    <div class="tracking-item" data-shipment-status="ordered" style="cursor: pointer;">
                        <div class="tracking-title">
                            <i class="fas fa-shopping-cart shipment-icon"></i>
                            Ordered
                        </div>
                        <div class="shipment-detail">
                            <div>
                                <div class="shipment-name">Order Placed</div>
                                <div class="shipment-code">Processing</div>
                            </div>
                            <div class="shipment-count"><?php echo isset($shipment_activities['ordered']) ? $shipment_activities['ordered'] : 0; ?></div>
                        </div>
                    </div>
                    
                    <div class="tracking-item" data-shipment-status="in_transit" style="cursor: pointer;">
                        <div class="tracking-title">
                            <i class="fas fa-shipping-fast shipment-icon"></i>
                            In Transit
                        </div>
                        <div class="shipment-detail">
                            <div>
                                <div class="shipment-name">Currently Moving</div>
                                <div class="shipment-code">On the Way</div>
                            </div>
                            <div class="shipment-count"><?php echo isset($shipment_activities['in_transit']) ? $shipment_activities['in_transit'] : 0; ?></div>
                        </div>
                    </div>
                    
                    <div class="tracking-item" data-shipment-status="received" style="cursor: pointer;">
                        <div class="tracking-title">
                            <i class="fas fa-inbox shipment-icon"></i>
                            Received
                        </div>
                        <div class="shipment-detail">
                            <div>
                                <div class="shipment-name">Arrived at Destination</div>
                                <div class="shipment-code">Received</div>
                            </div>
                            <div class="shipment-count"><?php echo isset($shipment_activities['received']) ? $shipment_activities['received'] : 0; ?></div>
                        </div>
                    </div>
                    
                    <div class="tracking-item" data-shipment-status="delivered" style="cursor: pointer;">
                        <div class="tracking-title">
                            <i class="fas fa-check-circle shipment-icon"></i>
                            Delivered
                        </div>
                        <div class="shipment-detail">
                            <div>
                                <div class="shipment-name">Successfully Delivered</div>
                                <div class="shipment-code">Completed</div>
                            </div>
                            <div class="shipment-count"><?php echo isset($shipment_activities['delivered']) ? $shipment_activities['delivered'] : 0; ?></div>
                        </div>
                    </div>
                </div>

                <!-- Tracking Stats -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <span class="stat-number"><?php echo isset($shipment_activities['total_shipments']) ? $shipment_activities['total_shipments'] : 0; ?></span>
                        <span class="stat-label">Total Shipments</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?php echo isset($shipment_completion_rate) ? $shipment_completion_rate : 0; ?>%</span>
                        <span class="stat-label">Completion Rate</span>
                    </div>
                </div>


            </div>
        </div>
    </div>
    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-6">
            <!-- Operational Metrics -->
            <div class="section-card">
                <h3 class="section-title">
                    <i class="fas fa-cogs" style="color: #3b82f6;"></i>
                    Operational Metrics
                </h3>
                
                <div class="metric-row">
                    <span class="metric-label">
                        <i class="fas fa-users" style="color: #10b981;"></i>
                        Active Employees
                    </span>
                    <span class="metric-value"><?php echo isset($active_employee) ? $active_employee : 0; ?></span>
                </div>
                
                <div class="metric-row">
                    <span class="metric-label">
                        <i class="fas fa-clock" style="color: #f59e0b;"></i>
                        Weekly Avg. Working Hours
                    </span>
                    <span class="metric-value"><?php echo isset($average_working_hour) ? $average_working_hour : 0; ?>H</span>
                </div>
                
                <div class="metric-row">
                    <span class="metric-label">
                        <i class="fas fa-calendar-check" style="color: #06b6d4;"></i>
                        Attendance Rate
                    </span>
                    <span class="metric-value"><?php echo isset($attendance_rate_today) ? $attendance_rate_today : 0; ?>%</span>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-6">
            <!-- Financial Overview -->
            <div class="section-card">
                <h3 class="section-title">
                    <i class="fas fa-chart-pie" style="color: #10b981;"></i>
                    Financial Overview
                </h3>
                <?php 
                $total_projects = count($milestone_data);
                $total_budget = array_sum(array_column($milestone_data, 'total_fund'));
                $total_spent_hours = array_sum(array_column($milestone_data, 'spent_time'));
                $total_remaining_hours = array_sum(array_column($milestone_data, 'remaining_time'));
                $total_indirect_cost = $total_spent_hours * $global_config['cost_per_hour'];
                ?>
				                
                <div class="metric-row">
                    <span class="metric-label">
                        <i class="fas fa-arrow-down" style="color: #ef4444;"></i>
                        Direct Costs
                    </span>
                    <span class="metric-value">৳<?= number_format($total_budget) ?></span>
                </div>
                
                <div class="metric-row">
                    <span class="metric-label">
                        <i class="fas fa-chart-pie" style="color: #f59e0b;"></i>
                        Indirect Cost
                    </span>
                    <span class="metric-value">৳<?= number_format($total_indirect_cost) ?></span>
                </div>
                
                <div class="metric-row">
                    <span class="metric-label">
                        <i class="fas fa-project-diagram" style="color: #8b5cf6;"></i>
                        Active Projects
                    </span>
                    <span class="metric-value"><?= $total_projects ?></span>
                </div>
            </div>
        </div>
    </div>
	
    <!-- Communication Logs Tabs -->
    <div class="row panel-body" style="background:none;">
        <div class="reports-table">
            <div class="tabs-custom">
			<ul class="nav nav-tabs">
				<li class="active">
					<a href="#cdr" data-toggle="tab"><i class="fas fa-phone"></i> <?=translate('CDR Reports')?></a>
				</li>
				<li>
					<a href="#email" data-toggle="tab"><i class="fas fa-envelope"></i> <?=translate('email communications')?></a>
				</li>
				<li>
					<a href="#crm" data-toggle="tab"><i class="fas fa-calendar-alt"></i> CRM Activities</a>
				</li>
			</ul>
		<div class="tab-content">
			<div id="cdr" class="tab-pane active">
				<!-- CDR Tab -->
				<div class="table-responsive">
					<table class="table table-bordered table-hover table-condensed table_default" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>Sl</th>
								<th>Date/Time</th>
								<th>Call Type</th>
								<th>Caller</th>
								<th>Destination</th>
								<th>Status</th>
								<th>Duration</th>
								<th>Recording</th>
							</tr>
						</thead>
						<tbody>
							<?php $count=1; if (!empty($cdr_logs)): ?>
								<?php foreach ($cdr_logs as $log): ?>
									<tr>
										<td><?php echo $count++; ?></td>
										<td><?= date('Y-m-d H:i', strtotime($log['Timestamp'])) ?></td>
										<td>
											<?php if ($log['CallDirection'] === 'Incoming'): ?>
												<span style="color: #16a34a; font-weight: 500;"><?= htmlspecialchars($log['CallDirection']) ?></span>
											<?php elseif ($log['CallDirection'] === 'Outgoing'): ?>
												<span style="color: #dc2626; font-weight: 500;"><?= htmlspecialchars($log['CallDirection']) ?></span>
											<?php else: ?>
												<span><?= htmlspecialchars($log['CallDirection']) ?></span>
											<?php endif; ?>
										</td>
										<td><?= htmlspecialchars($log['CallerIDName']) ?><br><small><?= htmlspecialchars($log['CallerIDNum']) ?></small></td>
										<td><?= htmlspecialchars($log['DialedNumber']) ?></td>
										<td>
											<?php if ($log['CallDisposition'] === 'ANSWERED'): ?>
												<span class="status-badge status-completed">Answered</span>
											<?php else: ?>
												<span class="status-badge status-pending"><?= htmlspecialchars($log['CallDisposition']) ?></span>
											<?php endif; ?>
										</td>
										<td><?= $log['CallDuration'] ?> sec</td>
										<td>
											<?php if (!empty($log['RecordingFile'])): ?>
												<button class="btn btn-sm btn-primary" onclick="playRecording('<?= htmlspecialchars($log['RecordingFile']) ?>')">
													<i class="fas fa-play"></i> Play
												</button>
											<?php else: ?>
												<span class="text-muted">No Recording</span>
											<?php endif; ?>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php else: ?>
								<tr>
									<td colspan="8" class="text-center">No CDR logs found</td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
			
			<div class="tab-pane" id="email">
				<div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>Source</th>
                                    <th>To Email</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($email_logs)): ?>
                                    <?php foreach ($email_logs as $log): ?>
                                        <tr>
                                            <td><?= date('Y-m-d H:i', strtotime($log['created_at'])) ?></td>
                                            <td><?= isset($log['trigger_source']) ? translate($log['trigger_source']) : 'Manual' ?></td>
                                            <td><?= htmlspecialchars($log['to_email']) ?></td>
                                            <td><?= htmlspecialchars($log['subject']) ?></td>
                                            <td><span class="status-badge status-completed"><?= translate($log['status']) ?></span></td>
                                            <td>
                                                <button class="btn btn-sm btn-info view-email-btn" data-subject="<?= htmlspecialchars($log['subject']) ?>" data-to="<?= htmlspecialchars($log['to_email']) ?>" data-body="<?= htmlspecialchars($log['body']) ?>" data-date="<?= date('Y-m-d H:i', strtotime($log['created_at'])) ?>">View</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No email logs found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
			</div>
			
			<div class="tab-pane" id="crm">
				<div class="table-responsive">
                        <table class="table table-bordered table-hover table-condensed table_default" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>Activity ID</th>
                                    <th width="5px">Type</th>
                                    <th width="300px">Subject</th>
                                    <th>Status</th>
                                    <th width="100px">Start Date</th>
                                    <th width="100px">Due Date</th>
                                    <th width="150px">Assigned To</th>
                                    <th width="150px">Related To</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($crm_activities)): ?>
                                    <?php foreach ($crm_activities as $activity): ?>
                                        <tr>
                                            <td>
                                                <a href="https://crm.com.bd/index.php?module=Calendar&view=Detail&app=MARKETING&record=<?= urlencode($activity['activityid']) ?>" 
                                                   target="_blank" 
                                                   rel="noopener noreferrer"
                                                   class="btn btn-sm btn-primary" 
                                                   style="padding: 2px 6px; font-size: 11px;">
                                                    <?= htmlspecialchars($activity['activityid']) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge" style="background: #3b82f6; color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px;">
                                                    <?= htmlspecialchars($activity['activity_type']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($activity['subject']) ?></td>
                                            <td>
                                                <?php 
                                                $status_class = 'status-pending';
                                                if (strtolower($activity['status']) == 'completed') {
                                                    $status_class = 'status-completed';
                                                } elseif (in_array(strtolower($activity['status']), ['in progress', 'planned'])) {
                                                    $status_class = 'status-in-progress';
                                                }
                                                ?>
                                                <span class="status-badge <?= $status_class ?>"><?= htmlspecialchars($activity['status']) ?></span>
                                            </td>
                                            <td>
                                                <?php if (!empty($activity['start_date'])): ?>
                                                    <?= date('Y-m-d', strtotime($activity['start_date'])) ?>
                                                    <?php if (!empty($activity['time_start'])): ?>
                                                        <br><small><?= date('H:i', strtotime($activity['time_start'])) ?></small>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($activity['due_date'])): ?>
                                                    <?= date('Y-m-d', strtotime($activity['due_date'])) ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($activity['assigned_to']) ?></td>
                                            <td><?= htmlspecialchars($activity['related_to']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No CRM activities found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
			</div>
		</div>
	</div>
        </div>
    </div>
</div>

<!-- Email View Modal -->
<div class="modal fade" id="emailModal" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><i class="fas fa-envelope"></i> Email Details</h5>
				<button type="button" class="close" data-dismiss="modal">
					<span>&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="row mb-3">
					<div class="col-md-6">
						<strong>To:</strong> <span id="modal-to-email"></span>
					</div>
					<div class="col-md-6">
						<strong>Date:</strong> <span id="modal-date"></span>
					</div>
				</div>
				<div class="mb-3">
					<strong>Subject:</strong> <span id="modal-subject"></span>
				</div>
				<div class="mb-3">
					<strong>Message:</strong>
					<div id="modal-body" style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 10px; white-space: pre-wrap;"></div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Tasks Modal -->
<div class="modal fade" id="tasksModal" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
			<div class="modal-header" style="background: #fff; color: #000; border-radius: 12px 12px 0 0;">
				<h4 class="modal-title" style="font-weight: 600; margin: 0;"><i class="fas fa-tasks mr-2"></i> <span id="tasks-modal-title">Tasks</span></h4>
				<button type="button" class="close" data-dismiss="modal" style="color: #000; opacity: 0.8; font-size: 24px;">&times;</button>
			</div>
			<div class="modal-body" style="padding: 25px; background: #f8f9fa;">
				<div id="tasks-loading" class="text-center" style="display: none; padding: 40px;">
					<i class="fas fa-spinner fa-spin" style="font-size: 24px; color: #667eea;"></i>
					<p style="margin-top: 15px; color: #6c757d;">Loading tasks...</p>
				</div>
				<div id="tasks-content">
					<!-- Tasks will be loaded here -->
				</div>
			</div>
			<div class="modal-footer" style="background: white; border-top: 1px solid #e9ecef; border-radius: 0 0 12px 12px;">
				<div id="tasks-progress" style="width: 100%;">
					<!-- Progress bar will be inserted here -->
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Shipments Modal -->
<div class="modal fade" id="shipmentsModal" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><i class="fas fa-shipping-fast"></i> <span id="shipments-modal-title">Shipments</span></h5>
				<button type="button" class="close" data-dismiss="modal">
					<span>&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div id="shipments-loading" class="text-center" style="display: none;">
					<i class="fas fa-spinner fa-spin"></i> Loading shipments...
				</div>
				<div class="table-responsive">
					<table class="table table-bordered table-hover" id="shipments-table">
						<thead>
							<tr>
								<th>Tracking Number</th>
								<th>Sender</th>
								<th>Receiver</th>
								<th>Origin</th>
								<th>Destination</th>
								<th>Status</th>
								<th>Created</th>
							</tr>
						</thead>
						<tbody id="shipments-table-body">
						</tbody>
					</table>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Task Details Modal -->
<div class="modal fade" id="taskDetailsModal" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Task Details</h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<!-- Task details will be loaded here -->
			</div>
		</div>
	</div>
</div>

<!-- Audio Modal -->
<div class="modal fade" id="audioModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><i class="fas fa-volume-up"></i> Audio Recording</h5>
				<button type="button" class="close" data-dismiss="modal">
					<span>&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<!-- Audio player will be loaded here -->
			</div>
		</div>
	</div>
</div>


<!-- Trigger click on page load -->
<script>
$(document).ready(function() {
    // Ensure the button exists and is visible
    if ($('#sidebar-toggle-button').length) {
        setTimeout(function() {
            $('#sidebar-toggle-button').click();  // Trigger the click event
        }, 10);  // 100ms delay to ensure it's ready
    }
});
</script>

<script>
// Email modal event handler
$(document).on('click', '.view-email-btn', function() {
	var subject = $(this).data('subject');
	var toEmail = $(this).data('to');
	var body = $(this).data('body');
	var date = $(this).data('date');
	
	$('#modal-subject').text(subject);
	$('#modal-to-email').text(toEmail);
	$('#modal-body').text(body);
	$('#modal-date').text(date);
	$('#emailModal').modal('show');
});

// Activity item click handler
$(document).on('click', '.activity-item', function() {
	var taskType = $(this).data('task-type');
	var title = $(this).find('.activity-label').text().trim();
	
	$('#tasks-modal-title').text(title);
	$('#tasks-loading').show();
	$('#tasks-content').empty();
	$('#tasksModal').modal('show');
	
	// Map task types to proper category values
	var categoryFilter = null;
	switch(taskType) {
		case 'milestone':
			categoryFilter = 'Milestone';
			break;
		case 'incident':
			categoryFilter = 'Incident';
			break;
		case 'customer_query':
			categoryFilter = 'Customer Query';
			break;
		case 'explore':
			categoryFilter = 'Explore';
			break;
		case 'emp_request':
			categoryFilter = 'EMP Request';
			break;
		default:
			categoryFilter = null; // For 'all' and other types
	}
	
	// Fetch tasks via AJAX
	$.ajax({
		url: '<?php echo base_url('dashboard/get_tracker_tasks'); ?>',
		type: 'POST',
		data: { 
			task_type: taskType,
			category: categoryFilter
		},
		dataType: 'json',
		success: function(response) {
			$('#tasks-loading').hide();
			if (response.status === 'success' && response.tasks.length > 0) {
				// Group tasks by status
				const groupedTasks = {
					todo: [],
					in_progress: [],
					in_review: [],
					completed: [],
					hold: [],
					canceled: []
				};
				
				response.tasks.forEach(function(task) {
					const status = task.task_status || 'todo';
					if (groupedTasks[status]) {
						groupedTasks[status].push(task);
					} else {
						groupedTasks.todo.push(task);
					}
				});
				
				// Status configuration
				const statusConfig = {
					todo: { title: 'To Do', color: '#ffbe0b', icon: 'fas fa-clipboard-list' },
					in_progress: { title: 'In Progress', color: '#3a86ff', icon: 'fas fa-play-circle' },
					in_review: { title: 'In Review', color: '#17a2b8', icon: 'fas fa-eye' },
					completed: { title: 'Completed', color: '#06d6a0', icon: 'fas fa-check-circle' },
					hold: { title: 'On Hold', color: '#fd7e14', icon: 'fas fa-pause-circle' },
					canceled: { title: 'Canceled', color: '#dc3545', icon: 'fas fa-times-circle' }
				};
				
				let html = '';
				Object.keys(statusConfig).forEach(function(status) {
					if (groupedTasks[status].length > 0) {
						const config = statusConfig[status];
						html += `
							<div class="accordion-card" style="border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 8px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.07);">
								<div class="accordion-header" onclick="toggleTaskAccordion('task_${status}')" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 15px; cursor: pointer; font-weight: 600; font-size: 14px; color: #000; background: #f7f7f8; user-select: none; transition: all 0.3s ease;">
									<div class="status-info" style="display: flex; align-items: center; gap: 12px;">
										<div class="status-icon" style="width: 24px; height: 24px; border-radius: 50%; background: ${config.color}; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px;">
											<i class="${config.icon}"></i>
										</div>
										<span>${config.title}</span>
									</div>
									<div class="item-count" style="background: #e8e8e9; color: black; padding: 4px 8px; border-radius: 12px; font-size: 12px;">${groupedTasks[status].length} items</div>
								</div>
								<div class="accordion-body" id="accordion-body-task_${status}" style="max-height: 0; overflow: hidden; transition: max-height 0.4s ease-out, padding 0.4s ease-out; background-color: #ffffff;">
						`;
						
						groupedTasks[status].forEach(function(task) {
							html += `
								<div class="task-item"
									 onclick="viewTask('${task.unique_id}')"
									 style="cursor: pointer; display: flex; justify-content: space-between; align-items: center; padding: 6px 10px; border-bottom: 1px solid #f0f0f1; background: #f7f7f8; transition: all 0.2s ease; border-radius: 8px; margin-bottom: 3px;"
									 onmouseover="this.style.background='#e8e8e9'"
									 onmouseout="this.style.background='#f7f7f8'">
									<div style="display: flex; align-items: center; gap: 10px; overflow: hidden;">
										<div class="task-id" style="width: 80px; flex-shrink: 0; font-weight: 600; color: #1976d2; font-size: 12px;">
											${task.unique_id}
										</div>
										<div class="task-title" style="font-weight: 600; color: #2d3748; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 12px;">
											${task.task_title}
										</div>
									</div>
									<div class="task-estimate" style="flex-shrink: 0; padding: 3px 8px; border: 1px solid #cccccc; background: #f2f2f4; color: #000; border-radius: 20px; font-size: 11px; min-width: 40px; text-align: center;">
										${task.estimation_time || '0'}h
									</div>
								</div>
							`;
						});
						
						html += `
								</div>
							</div>
						`;
					}
				});
				
				// Calculate progress
				const totalTasks = response.tasks.length;
				const completedTasks = groupedTasks.completed.length;
				const progressPercentage = totalTasks > 0 ? Math.round((completedTasks / totalTasks) * 100) : 0;
				
				// Create progress bar
				const progressHtml = `
					<div style="margin-bottom: 15px;">
						<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
							<h6 style="margin: 0; color: #495057; font-weight: 600;">Overall Progress</h6>
							<span style="color: #6c757d; font-size: 14px; font-weight: 500;">${completedTasks}/${totalTasks} tasks completed (${progressPercentage}%)</span>
						</div>
						<div style="background: #e9ecef; border-radius: 10px; height: 12px; overflow: hidden;">
							<div style="background: linear-gradient(90deg, #06d6a0 0%, #20c997 100%); height: 100%; width: ${progressPercentage}%; transition: width 0.3s ease; border-radius: 10px;"></div>
						</div>
					</div>
				`;
				$('#tasks-progress').html(progressHtml);
				$('#tasks-content').html(html);
			} else {
				$('#tasks-content').html('<div class="text-center text-muted" style="padding: 40px;"><i class="fas fa-tasks" style="font-size: 48px; color: #dee2e6; margin-bottom: 15px;"></i><p>No tasks found</p></div>');
				$('#tasks-progress').html('<div style="text-align: center; color: #6c757d;">No progress data available</div>');
			}
		},
		error: function() {
			$('#tasks-loading').hide();
			$('#tasks-content').html('<div class="text-center text-danger" style="padding: 40px;"><i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 15px;"></i><p>Error loading tasks</p></div>');
			$('#tasks-progress').html('<div style="text-align: center; color: #dc3545;">Unable to load progress data</div>');
		}
	});
});

// Toggle task accordion sections
function toggleTaskAccordion(id) {
	const header = document.querySelector(`[onclick="toggleTaskAccordion('${id}')"]`);
	const body = document.getElementById('accordion-body-' + id);
	
	if (body.style.maxHeight && body.style.maxHeight !== '0px') {
		body.style.maxHeight = '0px';
		body.style.padding = '0px';
		header.style.background = '#f7f7f8';
	} else {
		body.style.maxHeight = 'none';
		body.style.padding = '15px';
		header.style.background = '#e8e8e9';
		
		// Ensure proper height calculation
		setTimeout(() => {
			body.style.maxHeight = body.scrollHeight + 20 + 'px';
		}, 10);
	}
}

// View task details in modal (global function)
function viewTask(id) {
	$.ajax({
		url: '<?php echo base_url('dashboard/viewTracker_Issue'); ?>',
		type: 'POST',
		data: {'id': id},
		dataType: "html",
		success: function (data) {
			$('#taskDetailsModal .modal-body').html(data);
			$('#taskDetailsModal').modal('show');
		}
	});
}

// Shipment item click handler
$(document).on('click', '.tracking-item', function() {
	var shipmentStatus = $(this).data('shipment-status');
	var title = $(this).find('.tracking-title').text().trim();
	
	$('#shipments-modal-title').text(title + ' Shipments');
	$('#shipments-loading').show();
	$('#shipments-table-body').empty();
	$('#shipmentsModal').modal('show');
	
	// Fetch shipments via AJAX
	$.ajax({
		url: '<?php echo base_url('dashboard/get_shipments'); ?>',
		type: 'POST',
		data: { shipment_status: shipmentStatus },
		dataType: 'json',
		success: function(response) {
			$('#shipments-loading').hide();
			if (response.status === 'success' && response.shipments.length > 0) {
				var tbody = '';
				$.each(response.shipments, function(index, shipment) {
					var statusClass = shipment.status === 'delivered' ? 'status-completed' : 
									  shipment.status === 'in_transit' ? 'status-in-progress' : 'status-pending';
					tbody += '<tr>' +
						'<td>' + shipment.tracking_number + '</td>' +
						'<td>' + shipment.sender_name + '</td>' +
						'<td>' + shipment.receiver_name + '</td>' +
						'<td>' + shipment.origin + '</td>' +
						'<td>' + shipment.destination + '</td>' +
						'<td><span class="status-badge ' + statusClass + '">' + shipment.status + '</span></td>' +
						'<td>' + shipment.created_at + '</td>' +
						'</tr>';
				});
				$('#shipments-table-body').html(tbody);
			} else {
				$('#shipments-table-body').html('<tr><td colspan="7" class="text-center">No shipments found</td></tr>');
			}
		},
		error: function() {
			$('#shipments-loading').hide();
			$('#shipments-table-body').html('<tr><td colspan="7" class="text-center">Error loading shipments</td></tr>');
		}
	});
});

// Recording play function
function playRecording(recordingFile) {
	if (recordingFile) {
		var recordingUrl = 'https://connect.com.bd/recordings/' + encodeURIComponent(recordingFile);
		$('#audioModal .modal-body').html('<audio controls style="width:100%;"><source src="' + recordingUrl + '" type="audio/wav">Your browser does not support audio playback.</audio>');
		$('#audioModal').modal('show');
	}
}
</script>