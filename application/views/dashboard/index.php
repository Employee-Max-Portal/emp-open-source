   <!--table row height -->
   <style>
	@media screen and (min-width: 992px) {
	  .fc-event {
		display: flex !important;
		align-items: center !important;
		justify-content: center !important;
		font-size: .72em;
	  }

	  .fc-row.fc-week.fc-widget-content {
		height: auto!important;
	  }
	}

     .dashboard-flex-wrapper {
         display: flex;
         flex-wrap: wrap;
         gap: 20px;
         justify-content: space-between;
     }

     .dashboard-panel {
         flex: 1;
         min-width: 300px;
         max-width: 100%;
     }

     .activity-card {
         border: 1px solid #ddd;
         border-radius: 10px;
         padding: 10px;
         margin-bottom: 10px;
         background: #f9f9f9;
     }

     .card-list-wrapper {
         max-height: 400px;
         overflow-y: auto;
     }

     @media (max-width: 768px) {
         .dashboard-panel {
             min-width: 100% !important;
         }

         .form-control-sm {
             width: 100% !important;
         }
     }
 
 <!-- ✅ Embedded Styles -->

 .btn-yellow {
     background-color: #ffc107 !important; /* Bootstrap's warning/yellow color */
     color: #fff !important;
     border-color: #ffc107 !important;
 }

 .btn-sm, .btn-group-sm > .btn {
     padding: 4px 4px;
 }

 .card-list-wrapper {
     display: flex;
     flex-direction: column;
     gap: 12px;
     padding: 6px;
 }

 .activity-card {
     background-color: #fff;
     border: 1px solid #dee2e6;
     border-radius: 12px;
     padding: 12px 16px;
     box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
     display: flex;
     flex-direction: column;
     width: 100%;
     font-size: 14px;
     cursor: pointer;
     transition: transform 0.2s ease, box-shadow 0.2s ease;
     will-change: transform, box-shadow;
 }

 .activity-card:hover {
     box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
     transform: translateY(-2px);
 }

 .activity-card .left-info {
     display: flex;
     flex-direction: column;
     gap: 4px;
 }

 .activity-card .staff-name {
     font-weight: 600;
     font-size: 15px;
     color: #007bff;
 }

 .activity-card div {
     color: #6c757d;
     font-size: 13px;
 }

 .activity-card .attachments ul {
     list-style-type: disc;
     margin-left: 18px;
     padding-left: 0;
 }

 .activity-card .attachments ul li a {
     color: #007bff;
     text-decoration: none;
 }

 .activity-card .attachments ul li a:hover {
     text-decoration: underline;
 }

 .no-activity {
     text-align: center;
     padding: 15px;
     font-size: 14px;
     color: #999;
 }

 /* Task Drawer Styles */
 .task-drawer {
     position: fixed;
     top: 0;
     right: -30%;
     width: 30%;
     height: 100%;
     background-color: #fff;
     box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
     z-index: 1050;
     transition: right 0.25s cubic-bezier(0.4, 0, 0.2, 1);
     padding: 20px;
     overflow-y: auto;
 }



 .task-drawer.open {
     right: 0;
 }

 .drawer-header {
     display: flex;
     justify-content: space-between;
     align-items: center;
     margin-bottom: 20px;
     padding-bottom: 10px;
     border-bottom: 1px solid #eee;
 }

 .close-drawer {
     background: none;
     border: none;
     font-size: 24px;
     cursor: pointer;
 }

 .drawer-body .form-group {
     margin-bottom: 15px;
 }

 .drawer-body label {
     font-weight: 600;
     margin-bottom: 5px;
     display: block;
 }
 </style>

 <style>


 /* Confirmation Modal Styles */
 .modal {
     display: none;
     position: fixed;
     top: 0;
     left: 0;
     width: 100%;
     height: 100%;
     background: rgba(0, 0, 0, 0.5);
     z-index: 9999;
     justify-content: center;
     align-items: center;
 }

 .modal.active {
     display: flex;
 }

 .modal-content {
     background: #fff;
     padding: 2rem;
     border-radius: 10px;
     width: 300px;
     text-align: center;
 }

 .modal-header {
     display: flex;
     justify-content: space-between;
     align-items: center;
 }

 .close-modal {
     background: none;
     border: none;
     font-size: 1.5rem;
     color: #333;
     cursor: pointer;
 }

 .modal-footer {
     margin-top: 1rem;
 }

 .modal-footer .btn {
     margin: 0 5px;
 }


 .drawer-modal {
     position: fixed;
     top: 0;
     right: -30%;
     width: 30%;
     height: 100%;
     background: #fff;
     box-shadow: -2px 0 10px rgba(0,0,0,0.1);
     transition: right 0.25s cubic-bezier(0.4, 0, 0.2, 1);
     z-index: 9999;
     padding: 2rem;
 }

 .drawer-modal.active {
     right: 0;
 }

 .drawer-header {
     display: flex;
     justify-content: space-between;
     align-items: center;
     margin-bottom: 1rem;
 }

 .close-drawer {
     background: none;
     border: none;
     font-size: 1.5rem;
     line-height: 1;
     color: #333;
     cursor: pointer;
 }




 </style>


 <style>
 /* Previous styles remain the same, just add/modify these styles */

 .employee-dashboard-panel {
     padding: 1.5rem;
     background: #f8fafc;
     font-family: 'Inter', system-ui, -apple-system, sans-serif;
     max-height: calc(54vh);
     overflow-y: auto;
     scrollbar-width: none;
     -ms-overflow-style: none;
 }

 .employee-dashboard-panel::-webkit-scrollbar {
     display: none;
 }

 .dashboard-section {
     scrollbar-width: none;
     -ms-overflow-style: none;
 }

 .dashboard-section::-webkit-scrollbar {
     display: none;
 }

 /* Hide scrollbars for pending attendance, task summary and break history */
 .table-responsive {
     scrollbar-width: none;
     -ms-overflow-style: none;
 }

 .table-responsive::-webkit-scrollbar {
     display: none;
 }

 .card-list-wrapper {
     scrollbar-width: none;
     -ms-overflow-style: none;
 }

 .card-list-wrapper::-webkit-scrollbar {
     display: none;
 }
 </style>


 <style>
 body .btn-primary {
     color: #ffffff;
     text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
     background-color: #00a51f;
     border-color: #11a300;
 }
 body .btn-primary:hover {
     border-color: #2dc4e9 !important;
     background-color: #089296;
 }

 </style>

<style>

.stats-card {
    background: white;
    border-radius: 10px;
    padding: 15px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    border: none;
    overflow: hidden;
    position: relative;
    display: flex;
    align-items: center;
    gap: 16px;
    height: 80px;
    will-change: transform, box-shadow;
}

.stats-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 16px 48px rgba(0,0,0,0.15);
}

.stats-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--card-color), var(--card-color-light));
}

.stats-card.employees { --card-color: #667eea; --card-color-light: #764ba2; }
.stats-card.present { --card-color: #00a51f; --card-color-light: #68d391; }
.stats-card.absent { --card-color: #e30404; --card-color-light: #fc8181; }
.stats-card.under-you { --card-color: #ed8936; --card-color-light: #f6ad55; }

.stats-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    flex-shrink: 0;
    background: linear-gradient(135deg, var(--card-color), var(--card-color-light));
}

.stats-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.stats-number {
    font-size: 2rem;
    font-weight: 700;
    color: #2d3748;
    margin: 0;
    line-height: 1;
}

.stats-label {
    font-size: 0.875rem;
    color: #718096;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0;
}

.dashboard-section {
    background: white;
    margin: 15px 0;
    box-shadow: 0 8px 32px rgba(0,0,0,0.08);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 5px;
    border-bottom: 2px solid #f7fafc;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2d3748;
    display: flex;
    align-items: center;
    gap: 12px;
}

.filter-buttons {
    display: flex;
    gap: 8px;
}

.filter-btn {
    padding: 8px 16px;
    border: 2px solid #e2e8f0;
    background: white;
    border-radius: 10px;
    font-size: 0.875rem;
    font-weight: 500;
    color: #4a5568;
    cursor: pointer;
    transition: all 0.2s ease;
}

.filter-btn.active, .filter-btn:hover {
    background: #667eea;
    border-color: #667eea;
    color: white;
}

/* Task Summary and Break History Button Styles */
.btn-yellow {
    background-color: #ffc107 !important;
    color: #fff !important;
    border-color: #ffc107 !important;
    font-weight: 600 !important;
}

.btn-light {
    background-color: #f8f9fa !important;
    border-color: #dee2e6 !important;
    color: #6c757d !important;
}

.btn-light:hover {
    background-color: #e2e6ea !important;
    border-color: #dae0e5 !important;
    color: #545b62 !important;
}

.btn-sm {
    padding: 6px 12px !important;
    font-size: 0.875rem !important;
    border-radius: 6px !important;
    transition: all 0.2s ease !important; background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
}

.filter-btn.active, .filter-btn:hover {
    background: #667eea;
    border-color: #667eea;
    color: white;
}

.employee-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.employee-card {
    background: white;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border-top: 4px solid var(--status-color);
    display: flex;
    flex-direction: column;
    min-height: 350px;
}

.employee-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 32px rgba(0,0,0,0.12);
}

.employee-card.present { --status-color: #00a51f; }
.employee-card.late { --status-color: #ed8936; }
.employee-card.absent { --status-color: #e30404; }
.employee-card.leave { --status-color: #e59824; }

.champion-badge-count {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ffd700;
    color: #b45309;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: bold;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.employee-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 10px;
}

.employee-avatar {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    object-fit: cover;
    border: 3px solid var(--status-color);
}

.employee-info h3 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2d3748;
    margin: 0 0 4px 0;
}

.employee-id {
    font-size: 0.875rem;
    color: #718096;
}

.action-buttons {
    display: flex;
    gap: 8px;
    margin-top: auto;
}

.employee-card-content {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.employee-card-footer {
    margin-top: auto;
}

.time-info {
    display: flex;
    justify-content: space-between;
    padding: 12px;
    background: #f7fafc;
    border-radius: 10px;
    font-size: 1rem;
    margin-top: auto;
}

.btn-modern {
    flex: 1;
    padding: 10px 16px;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-success {
    background: #00a51f;
    color: white;
}

.btn-warning {
    background: #ed8936;
    color: white;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.btn-modern:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.chart-container {
    background: white;
    border-radius: 10px;
    padding: 15px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    text-align: center;
}

.chart-legend {
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
}


.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.875rem;
    color: #4a5568;
}

.legend-color {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

@media (max-width: 768px) {
    .dashboard-container {
        padding: 5px;
    }
    
    .stats-card {
        padding: 8px;
        /* margin-bottom: 12px; */
        height: auto;
        /* flex-direction: column; */
        text-align: center;
        display: inherit;
    }
    
    .section-title {
        width: 40px;
        height: 40px;
        font-size: 18px;
		display: none;
    }
    
    .stats-icon {
        width: 40px;
        height: 40px;
        font-size: 18px;
		display: none;
    }
    
    .stats-number {
        font-size: 1.5rem;
    }
    
    .employee-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .employee-card {
        min-height: auto;
        padding: 12px;
    }
    
    .employee-header {
        flex-direction: column;
        text-align: center;
        gap: 8px;
    }
    
    .employee-avatar {
        width: 48px;
        height: 48px;
    }
    
    .section-header {
        /* flex-direction: column;
        gap: 12px; */
        align-items: stretch;
        display: inherit;
    }
    
    .filter-buttons {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .filter-btn {
        padding: 6px 12px;
        font-size: 0.8rem;
    }
    
    .chart-container {
        padding: 10px;
        margin-bottom: 15px;
    }
    
    .legend-item {
        font-size: 0.8rem;
    }
    
    .time-info {
        flex-direction: column;
        gap: 4px;
        font-size: 0.9rem;
    }
    
    .action-buttons {
        /* flex-direction: column; */
        gap: 6px;
    }
    
    .btn-modern {
        padding: 8px 12px;
        font-size: 0.8rem;
    }
    
    .task-drawer {
        width: 95%;
        right: -95%;
        padding: 15px;
    }
    
    .dashboard-flex-wrapper {
        flex-direction: column;
        gap: 15px;
    }
    
    .activity-card {
        padding: 10px;
        font-size: 13px;
    }
    
    .table-responsive {
        font-size: 11px;
    }
    
    .container-fluid {
        padding: 0 5px;
    }
    
    .modal-content {
        width: 90%;
        padding: 1rem;
    }
    
    .drawer-modal {
        width: 95%;
        right: -95%;
        padding: 1rem;
    }
    
    /* Task Log Summary & Break History Mobile Optimization */
    .card-title {
        font-size: 1.1rem !important;
        margin-bottom: 8px;
    }
    
    .card .row {
        margin: 0;
    }
    
    .card .col-md-12 {
        padding: 0;
    }
    
    /* Header button groups */
    .card div[style*="display: flex"] {
        flex-direction: column !important;
        gap: 8px !important;
        align-items: stretch !important;
    }
    
    .card div[style*="display: flex"] > div {
        display: flex !important;
        gap: 4px !important;
        justify-content: center !important;
    }
    
    /* Filter buttons optimization */
    .btn-sm {
        padding: 6px 8px !important;
        font-size: 0.75rem !important;
        min-width: 60px;
    }
    
    .btn-yellow {
        font-weight: 600 !important;
    }
    
    /* Search input optimization */
    .form-control-sm {
        width: 100% !important;
        max-width: none !important;
        margin-top: 8px;
        font-size: 0.8rem !important;
    }
    
    /* Card list wrapper */
    .card-list-wrapper {
        max-height: 300px !important;
        padding: 4px !important;
    }
}

/* Additional CSS for smooth filtering */
.employee-grid {
    min-height: 200px;
}

.employee-grid:empty::after {
    content: 'No staff members found for the selected filter.';
    grid-column: 1 / -1;
    text-align: center;
    padding: 40px;
    color: #718096;
    font-size: 1rem;
    background: #f7fafc;
    border-radius: 10px;
    border: 2px dashed #e2e8f0;
}

.no-staff-message {
    grid-column: 1 / -1;
    text-align: center;
    padding: 40px;
    color: #718096;
    font-size: 1rem;
    background: #f7fafc;
    border-radius: 10px;
    border: 2px dashed #e2e8f0;
}
</style>

<?php
$this->db->select('
sa.id as atten_id,
sa.staff_id,
sa.in_time,
sa.date,
sa.status as att_status,
sa.remark as att_remark,
s.name as name,
s.staff_id as staff_code,
s.id
');
$this->db->from('staff_attendance sa');
$this->db->join('staff s', 's.id = sa.staff_id');
$this->db->join('login_credential lc', 'lc.user_id = sa.staff_id');
$this->db->group_start();
$this->db->where('sa.is_manual', 1);
$this->db->where('s.id !=', 1);
$this->db->where('lc.active =', 1);
$this->db->group_end();
$this->db->order_by('s.staff_id', 'ASC');
$pending_attendance = $this->db->get()->result_array();
?>

 <?php
     $isRole8 = loggedin_role_id() == 8;
     $cardColClass = $isRole8 ? 'col-lg-3 col-sm-3 col-xs-3' : 'col-md-4 col-sm-4 col-xs-4';
 ?>
 
<div class="dashboard-container">
    <!-- Statistics Cards -->
    <div class="container-fluid" style = "padding-right: 0px; padding-left: 0px; padding-bottom: 15px;">
        <div class="row">
            <div class="<?= $cardColClass ?>">
                <div class="stats-card employees">
                    <div class="stats-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-number"><?= htmlspecialchars($total_staff) ?></div>
                        <div class="stats-label">Total Employees</div>
                    </div>
                </div>
            </div>
            
            <div class="<?= $cardColClass ?>">
                <div class="stats-card present">
                    <div class="stats-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-number"><?= htmlspecialchars($total_present) ?></div>
                        <div class="stats-label">Present Today</div>
                    </div>
                </div>
            </div>
            
            <div class="<?= $cardColClass ?>">
                <div class="stats-card absent">
                    <div class="stats-icon">
                        <i class="fas fa-user-times"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-number"><?= htmlspecialchars($total_staff - $total_present) ?></div>
                        <div class="stats-label">Absent Today</div>
                    </div>
                </div>
            </div>
            
            <?php if ($isRole8): ?>
            <div class="<?= $cardColClass ?>">
                <div class="stats-card under-you">
                    <div class="stats-icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-number"><?= htmlspecialchars($total_under_you) ?></div>
                        <div class="stats-label">Under You</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
                
        <?php if (!empty($leave_user_cards)): ?>
        <div class="<?= (!in_array(loggedin_role_id(), [4]) && !empty($pending_attendance)) ? 'col-md-8' : 'col-md-12' ?>">
        <div class="dashboard-section" style="height: 500px; overflow-y: auto; border: 1px solid #e3e6f0; border-radius: 10px; padding: 15px;">
			<div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-users"></i>
                    Employee Status
                </h2>
                <div class="filter-buttons">
                    <button class="filter-btn active" onclick="filterEmployees('all')">All</button>
                    <button class="filter-btn" onclick="filterEmployees('present')">Present</button>
                    <button class="filter-btn" onclick="filterEmployees('late')">Late</button>
                    <button class="filter-btn" onclick="filterEmployees('absent')">Absent</button>
                    <button class="filter-btn" onclick="filterEmployees('leave')">On Leave</button>
                </div>
            </div>
            
            <div class="employee-grid" id="employeeGrid">
                <?php if (!empty($leave_user_cards)): ?>
                <?php foreach ($leave_user_cards as $card): 
                    $status = $card['is_on_leave'] ? 'leave' : strtolower($card['attendance']);
                    $statusText = $card['is_on_leave'] ? 'On Leave' : $card['attendance'];
                ?>
                <div class="employee-card <?= $status ?>" data-status="<?= $status ?>">
                    <div class="employee-header">
                        <div style="position: relative; display: inline-block;">
                            <img src="<?= get_image_url('staff', $card['photo']); ?>" 
                                 alt="<?= html_escape($card['name']) ?>" 
                                 class="employee-avatar">
                            <?php 
                                $badge_count = $this->db->where('staff_id', $card['staff_id'])->where('status', 'active')->count_all_results('milestone_champion_badges');
                                if ($badge_count > 0): 
                            ?>
                            <span class="champion-badge-count" title="Champion Badges"><?= $badge_count ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="employee-info">
                            <?php if (in_array(loggedin_role_id(), [1, 2, 3, 5])) : ?>
								<a href="<?= base_url('employee/profile/' . $card['staff_id']) ?>" class="employee-card-link" style="text-decoration: none;">
								<?php endif; ?>
								 <div class="employee-card__details">
									 <h3 class="employee-card__name"><?= html_escape($card['name']) ?> (<?= html_escape($card['username']) ?>)</h3>
									 <div class="employee-card__id"> <?= html_escape($card['designation']) ?></div>
								 </div>
								<?php if (in_array(loggedin_role_id(), [1, 2, 3, 5])) : ?>
								</a>
								<?php endif; ?>
                        </div>
                    </div>
                   
					<?php if (!$card['is_on_break'] && !$card['is_on_leave'] && !empty($card['latest_task'])): ?>
 						<div style="margin: 5px 0; padding: 8px; background: #f0f4f8; border-radius: 10px; font-size: 1.2rem;">
                        <strong>Current Task:</strong> <?= !$card['is_on_break'] && !$card['is_on_leave'] && !empty($card['latest_task']) ? html_escape($card['latest_task']) : 'Empty' ?>
                    </div>
 					<?php endif; ?>
					<?php if ($card['is_on_break']): ?>
					  <div class="mt-2 text-info fw-bold" style="margin: 5px 0; padding: 8px; background: #f0f4f8; border-radius: 10px; font-size: 1.2rem;">
						<strong><i class="fas fa-coffee me-1"></i> On Break
						<?php if (!empty($card['break_name'])): ?>
						  (<?= html_escape($card['break_name']) ?>)
						<?php endif; ?></strong>
					  </div>
					<?php endif; ?>

					<?php if ($card['is_on_leave']): ?>
					  <div class="mt-2 text-danger fw-bold" style="margin: 5px 0; padding: 8px; background: #f0f4f8; border-radius: 10px; font-size: 1.2rem;">
						<i class="fas fa-plane-departure me-1"></i> On Leave
						<?php if (!empty($card['leave_name'])): ?>
						  (<?= html_escape($card['leave_name']) ?>)
						<?php endif; ?>
					  </div>
					<?php endif; ?>

				<div class="leave-stats-container mt-3" style="margin: 5px 0; padding: 8px; background: #f0f4f8; border-radius: 10px; font-size: 1.2rem;">
					<div class="leave-progress__header mb-2">
						<strong>Leave Balance:</strong>
					</div>

					<?php 
					$displayed_categories = [];
					foreach ($card['leave_summary'] as $leave): 
						// Skip if category already displayed
						if (in_array($leave['category'], $displayed_categories)) {
							continue;
						}
						$displayed_categories[] = $leave['category'];
						
						$available = max(0, $leave['allowed'] - $leave['used']);
					?>
						<div>
							<strong><?= html_escape($leave['category']) ?>:</strong> <?= $available ?> days
						</div>
					<?php endforeach; ?>
				</div>

				<?php if (!empty($card['pending_count'])): ?>
				  <div class="mt-2 fw-bold">
					 <i class="fas fa-exclamation-triangle me-1"></i> <strong>Penalty Work:</strong> <?= $card['pending_count'] ?> days
				  </div>
				<?php endif; ?>


                    <div class="time-info">
                        <span>
                            <i class="fas fa-sign-in-alt text-success"></i>
                            IN: <?= !empty($card['in_time']) ? date('h:i A', strtotime($card['in_time'])) : '--' ?>
                        </span>
                        <span>
                            <i class="fas fa-sign-out-alt text-danger"></i>
                            OUT: <?= !empty($card['out_time']) ? date('h:i A', strtotime($card['out_time'])) : '--' ?>
                        </span>
                    </div>
                    
                    <div class="action-buttons">
                        <button class="btn-modern btn-success checkinBtn" 
                                data-staff-id="<?= $card['staff_id'] ?>"
                                <?= !empty($card['in_time']) ? 'disabled' : '' ?>>
                            <?= !empty($card['in_time']) ? 'In' : 'In' ?>
                        </button>
                        <button class="btn-modern btn-warning checkoutBtn" 
                                data-staff-id="<?= $card['staff_id'] ?>"
                                <?= empty($card['in_time']) ? 'disabled' : '' ?>>
                            <?= !empty($card['out_time']) ? 'Out' : 'Out' ?>
                        </button>
                        <!-- <button class="btn-modern btn-primary log-task-btn" 
                                data-staff-id="<?= $card['staff_id'] ?>">
                            Log Task
                        </button> -->
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="no-staff-message">
                    No staff members available to display.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
		
 <!-- Confirmation Modal -->
 <div id="confirmationModal" class="modal">
     <div class="modal-content">
         <div class="modal-header">
             <h5>Confirm Action</h5>
             <button class="close-modal">&times;</button>
         </div>
         <div class="modal-body">
             <p id="modalMessage">Are you sure you want to proceed with this action?</p>

             <!-- Remarks Input Field -->
             <div class="mb-3" id="remarksSection">
                 <label for="remarks" class="form-label">Remarks</label>
                 <textarea class="form-control" id="remarks" name="remarks" rows="2"></textarea>
             </div>
         </div>
         <div class="modal-footer">
             <button type="button" id="confirmBtn" class="btn btn-success">Confirm</button>
             <button type="button" id="cancelBtn" class="btn btn-danger">Cancel</button>
         </div>
     </div>
 </div>
        <?php endif; ?>
	<?php if (!in_array(loggedin_role_id(), [4]) && !empty($pending_attendance)): ?>
        <div class="col-md-4">
            <!-- Pending Attendance Section -->
            <div class="dashboard-section" style="height: 500px; overflow-y: auto; border: 1px solid #e3e6f0; border-radius: 10px; padding: 15px;">
                <h3 class="section-title mb-3">
                    <i class="fas fa-clock"></i>
                    Pending Attendance
                </h3>
                <div class="card mb-3" style="max-width: 100%; margin: 0; height: 100%;">

				   <hr style="border-top: 1px solid grey; margin: 4px;">
				   <div class="table-responsive" style="overflow-y: auto;">
					<!-- Bulk Update Form -->
					<form method="POST" action="<?= base_url('dashboard/update_attendance') ?>" id="bulkForm">
					  <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />

					  <table class="table table-bordered table-hover table-condensed" cellspacing="0" width="100%" style="font-size: 12px;">
						<thead>
						  <tr>
							<th><?php echo translate('employee'); ?></th>
							<th><?php echo translate('Check-in'); ?></th>
							<th><?php echo translate('status'); ?></th>
							<th><?php echo translate('action'); ?></th>
						  </tr>
						</thead>
						<tbody>
							<?php

						  $this->db->select('
							sa.id as atten_id,
							sa.staff_id,
							sa.in_time,
							sa.date,
							sa.status as att_status,
							sa.remark as att_remark,
							s.name as name,
							s.staff_id as staff_code,
							s.id
						  ');
						  $this->db->from('staff_attendance sa');
						  $this->db->join('staff s', 's.id = sa.staff_id');
						  $this->db->join('login_credential lc', 'lc.user_id = sa.staff_id');
						  $this->db->group_start();
						  $this->db->where('sa.is_manual', 1);
						  $this->db->where('s.id !=', 1);
						  $this->db->where('lc.active =', 1);
						  $this->db->group_end();
						  $this->db->order_by('s.staff_id', 'ASC');
						  $pending_attendance = $this->db->get()->result_array();

						  foreach ($pending_attendance as $key => $att):
						  ?>
							<tr>
							  <!-- These fields will go to the bulk form -->
							  <input type="hidden" name="attendance[<?= $key ?>][attendance_id]" value="<?= $att['atten_id'] ?>">
							  <td><?= htmlspecialchars($att['staff_code']) . ' - ' . htmlspecialchars($att['name']) ?></td>
							  <td><?= !empty($att['in_time']) ? date('h:i A, d M Y', strtotime($att['in_time'] . $att['date'])) : 'N/A' ?></td>
							  <td>
								<?php
								  if ($att['att_status'] === 'L') {
									echo '<span class="btn btn-warning btn-sm" data-toggle="tooltip" title="' . htmlspecialchars($att['att_remark']) . '">Late</span>';
								  } elseif ($att['att_status'] === 'P') {
									echo '<span class="btn btn-success btn-sm" data-toggle="tooltip" title="' . htmlspecialchars($att['att_remark']) . '">Present</span>';
								  } else {
									echo '<span class="btn btn-secondary btn-sm" data-toggle="tooltip" title="' . htmlspecialchars($att['att_remark']) . '">' . htmlspecialchars($att['att_status']) . '</span>';
								  }
								?>
							  </td>
							  <td>
								<!-- Radio buttons for bulk update -->
								<div class="radio-custom radio-success radio-inline mt-xs">
								  <input type="radio" value="P" <?=($att['att_status'] == 'P' ? 'checked' : '')?> name="attendance[<?=$key?>][status]" id="pstatus_<?=$key?>">
								  <label for="pstatus_<?=$key?>"><?=translate('present')?></label>
								</div>
								<br>
								<div class="radio-custom radio-inline mt-xs">
								  <input type="radio" value="L" <?=($att['att_status'] == 'L' ? 'checked' : '')?> name="attendance[<?=$key?>][status]" id="lstatus_<?=$key?>">
								  <label for="lstatus_<?=$key?>"><?=translate('late')?></label>
								</div>
								<br>
								<!-- Single update form (must NOT be nested) -->
								  <button type="button" class="btn btn-xs btn-primary mt-1 single-update-btn" data-id="<?= $att['atten_id'] ?>" data-key="<?= $key ?>">Submit</button>
							  </td>
							</tr>
						  <?php endforeach; ?>
						</tbody>
					  </table>

					  <div class="panel-footer">
						<div class="row">
						  <div class="col-md-offset-6 col-md-6">
							<button type="button" id="openConfirmModal" class="btn btn-default btn-block"><?=translate('update')?></button>
						  </div>
						</div>
					  </div>
					</form>

				   </div>
				 </div>
            </div>
        </div>
    <?php endif; ?>


  <!-- Charts Section -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <div class="chart-container">
                    <h4>Monthly Attendance</h4>
					   <div style="text-align: center;">
						 <canvas id="attendancePieChart"></canvas>
						 <div style="position: relative; top: -85px;">
							 Total Attendance: <?= ($attendance_pie_data['P'] ?? 0) + ($attendance_pie_data['L'] ?? 0) ?>
						 </div>
					   </div>
                    <div class="chart-legend">
                        <div class="legend-item">
                            <div class="legend-color" style="background: #00a51f;"></div>
                            <span>Present (<?= $attendance_pie_data['P'] ?? 0 ?>)</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #ed8936;"></div>
                            <span>Late (<?= $attendance_pie_data['L'] ?? 0 ?>)</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #e30404;"></div>
                            <span>Absent (<?= $attendance_pie_data['A'] ?? 0 ?>)</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #667eea;"></div>
                            <span>Leave (<?= $attendance_pie_data['LV'] ?? 0 ?>)</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="chart-container">
                    <h4>Tracker Issues</h4>
					  <div style="text-align: center;">
						<canvas id="trackerIssuesPieChart"></canvas>
						<div style="position: relative; top: -85px;">
							Total Issues: <?= array_sum($tracker_issues_pie_data) ?>
						</div>
					  </div>
                    <div class="chart-legend">
                        <div class="legend-item">
                            <div class="legend-color" style="background: #0ea5e9;"></div>
                            <span>To-Do (<?= $tracker_issues_pie_data['todo'] ?? 0 ?>)</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #8b5cf6;"></div>
                            <span>In Progress (<?= $tracker_issues_pie_data['in_progress'] ?? 0 ?>)</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #16a34a;"></div>
                            <span>Completed (<?= $tracker_issues_pie_data['completed'] ?? 0 ?>)</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="chart-container">
                    <h4>RDC Tasks</h4>
					<div style="text-align: center;">
                    <canvas id="rdcTaskPieChart"></canvas>
						<div style="position: relative; top: -85px;">
							Total Tasks: <?= array_sum($rdc_task_pie_data) ?>
						</div>
					  </div>
                    <div class="chart-legend">
                        <div class="legend-item">
                            <div class="legend-color" style="background: #f59e0b;"></div>
                            <span>Pending (<?= $rdc_task_pie_data['pending'] ?? 0 ?>)</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #22c55e;"></div>
                            <span>Completed (<?= $rdc_task_pie_data['completed'] ?? 0 ?>)</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #ef4444;"></div>
                            <span>Canceled (<?= $rdc_task_pie_data['canceled'] ?? 0 ?>)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


 <!-- Confirm Modal -->
 <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel">
   <div class="modal-dialog modal-sm" role="document">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title" id="confirmModalLabel">Confirm Update</h5>
       </div>
       <div class="modal-body">
         Are you sure you want to update attendance?
       </div>
       <div class="modal-footer">
         <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
         <button type="button" id="confirmSubmit" class="btn btn-primary">Yes, Update</button>
       </div>
     </div>
   </div>
 </div>


 </div>
 <br>

 <div class="dashboard-flex-wrapper">

     <!-- Task Log Summary Panel -->
     <section class="panel dashboard-panel">
         <div class="panel-body">
             <div class="card mb-3">
                  <div style="display: flex; justify-content: space-between; align-items: center;">
					 <h5 class="card-title"><b>Task Log Summary</b></h5>
					 <div>
						<button id="taskBtnToday" class="btn btn-sm btn-yellow" onclick="filterTasksBy('today')">Today</button>
						<button id="taskBtnYesterday" class="btn btn-sm btn-light" onclick="filterTasksBy('yesterday')">Previous</button>
						<button id="taskBtnOlder" class="btn btn-sm btn-light" onclick="filterTasksBy('older')">Old</button>
					 </div>
					<input type="text" id="taskLogSearch"
					   class="form-control form-control-sm"
					   placeholder="🔍 Search..."
					   style="font-size: 13px; padding: 4px 8px; width: 100px; display: inline-block;"
					   onkeyup="filterTaskLogs()" />
				 </div>
             <hr style="margin: 4px;">
    
                  <div class="card-list-wrapper" id="taskLogContainer" style="max-height: 400px; overflow-y: auto;">
                <?php
 				$login_id = get_loggedin_user_id();
 				$login_data = $this->db
 					->select('login_credential.role, staff.department')
 					->from('login_credential')
 					->join('staff', 'login_credential.user_id = staff.id')
 					->where('login_credential.user_id', $login_id)
 					->get()->row_array();

 				$login_role = $login_data['role'];
 				$login_department = $login_data['department'];

 				$this->db->select('
 					staff_task_log.*, 
 					staff.staff_id AS employee_id,
 					staff.name AS staff_name,
 					sup.name AS supervisor_name
 				');
 				$this->db->from('staff_task_log');
 				$this->db->join('staff', 'staff_task_log.staff_id = staff.id', 'left');
 				$this->db->join('staff AS sup', 'staff_task_log.supervisor = sup.id', 'left');
 				$this->db->where('staff.id !=', 1);
 				$this->db->where('staff_task_log.staff_id', $login_id); 

 				$this->db->order_by('staff_task_log.id', 'DESC');
 				$task_logs = $this->db->get()->result_array();

 				$today = date('Y-m-d');
 				$has_today_logs = false;

 				if (!empty($task_logs)):
 					foreach ($task_logs as $log):
 						$attachments = !empty($log['attachments']) ? json_decode($log['attachments'], true) : [];

 						$start = $log['start_time'];
 						$end = $log['ended_at'] ?: date('Y-m-d H:i:s');
 						$duration = '-';

 						if (!empty($start)) {
 							$start_dt = new DateTime($start);
 							$end_dt = new DateTime($end);
 							$diff = $start_dt->diff($end_dt);
 							$duration = str_pad($diff->h, 2, '0', STR_PAD_LEFT) . ':' .
 										str_pad($diff->i, 2, '0', STR_PAD_LEFT) . ':' .
 										str_pad($diff->s, 2, '0', STR_PAD_LEFT);
 						}

 						$log_date = date('Y-m-d', strtotime($start));
 						if ($log_date === $today) {
 							$has_today_logs = true;
 						}
 				?>
 					<div class="activity-card"
 						 data-staff-id="<?= $log['staff_id'] ?>"
 						 data-task-id="<?= $log['id'] ?>"
 						 data-task-status="<?= htmlspecialchars($log['task_status'], ENT_QUOTES) ?>"
 						 data-task-title="<?= htmlspecialchars($log['task_title'], ENT_QUOTES) ?>"
 						 data-location="<?= htmlspecialchars($log['location'], ENT_QUOTES) ?>"
 						 data-start-time="<?= $log['start_time'] ?>"
 						 data-end-time="<?= $log['actual_end_time'] ?>"
 						 data-proof="<?= htmlspecialchars($log['proof'] ?? '', ENT_QUOTES) ?>"
 						 data-reason="<?= htmlspecialchars($log['reason'] ?? '', ENT_QUOTES) ?>"
 						 data-date="<?= $log_date ?>"
 						 style="<?= $log_date === $today ? '' : 'display: none;' ?>; cursor: pointer;"
 						 onclick="handleTaskCardClick(this)">

 						<div class="left-info w-100">
 							<div class="staff-name">👤 <?= htmlspecialchars($log['staff_name']); ?> (ID: <?= htmlspecialchars($log['employee_id']); ?>)</div>
 							<div class="task-title">📝 <b><?= htmlspecialchars($log['task_title']); ?></b></div>
 							<div class="status">📌 Status: <?= htmlspecialchars($log['task_status']); ?></div>
 							<div class="location">📍 Location: <?= htmlspecialchars($log['location']); ?></div>
 							<div class="time-range">
 								🕒 Start: <?= !empty($log['start_time']) ? date('d M Y, h:i A', strtotime($log['start_time'])) : 'N/A'; ?><br>
 								<?php if (!empty($log['ended_at'])): ?>
 								✅ End: <?= date('d M Y, h:i A', strtotime($log['ended_at'])); ?><br>
 								⏱️ Duration: <?= $duration ?>
 								<?php endif; ?>
 							</div>
 						</div>
 					</div>
				<?php endforeach;
					if (!$has_today_logs):
				?>
 					<div class="no-activity">
 						<p class="no-activity-text">No task logs for today</p>
 					</div>
 				<?php endif;
 				else: ?>
 					<div class="no-activity">
 						<p class="no-activity-text">No task logs available</p>
 					</div>
 				<?php endif; ?>
             </div>
             </div>
         </div>
     </section>

     <!-- Break History Panel -->
     <section class="panel dashboard-panel">
         <div class="panel-body">
             <div class="card mb-3">
                 <div style="display: flex; justify-content: space-between; align-items: center;">
					 <h5 class="card-title"><b>Break History</b></h5>
					 <div>
						<button id="btnToday" class="btn btn-sm btn-yellow" onclick="filterBreaksBy('today')">Today</button>
						<button id="btnYesterday" class="btn btn-sm btn-light" onclick="filterBreaksBy('yesterday')">Previous</button>
						<button id="btnOlder" class="btn btn-sm btn-light" onclick="filterBreaksBy('older')">Old</button>
					 </div>
					<input type="text" id="breakHistorySearch"
					   class="form-control form-control-sm"
					   placeholder="🔍 Search..."
					   style="font-size: 13px; padding: 4px 8px; width: 100px; display: inline-block;"
					   onkeyup="filterBreakLogs()" />
				 </div>
                 <hr style="margin: 4px;">

                <div class="card-list-wrapper" id="breakHistoryContainer" style="max-height: 400px; overflow-y: auto;">
            <?php
 			$login_id = get_loggedin_user_id();
 			$login_data = $this->db
 				->select('login_credential.role, staff.department')
 				->from('login_credential')
 				->join('staff', 'login_credential.user_id = staff.id')
 				->where('login_credential.user_id', $login_id)
 				->get()->row_array();

 			$login_role = $login_data['role'];
 			$login_department = $login_data['department'];

 			$this->db->select('
 				pause_history.*, 
 				pauses.name AS pause_name,
 				staff.staff_id AS employee_id,
 				staff.name AS staff_name
 			');
 			$this->db->from('pause_history');
 			$this->db->join('staff', 'pause_history.user_id = staff.id', 'left');
 			$this->db->join('pauses', 'pause_history.pause_id = pauses.id', 'left');
 			$this->db->where('staff.id !=', 1);

 			// 🔒 Department filter if not privileged role
 			if (!in_array($login_role, [1, 2, 3, 5])) {
 				$this->db->where('staff.department', $login_department);
 			}

 			$this->db->order_by('pause_history.id', 'DESC');
 			$breaks = $this->db->get()->result_array();

 			$today = date('Y-m-d');
 			$has_today_breaks = false;

 			if (!empty($breaks)):
 				foreach ($breaks as $break):
 					$start = $break['start_datetime'];
 					$end = $break['end_datetime'] ?: date('Y-m-d H:i:s');
 					$duration = '-';

 					if (!empty($start)) {
 						$start_dt = new DateTime($start);
 						$end_dt = new DateTime($end);
 						$diff = $start_dt->diff($end_dt);
 						$duration = str_pad($diff->h, 2, '0', STR_PAD_LEFT) . ':' .
 									str_pad($diff->i, 2, '0', STR_PAD_LEFT) . ':' .
 									str_pad($diff->s, 2, '0', STR_PAD_LEFT);
 					}

 					$status = ($break['status'] == 1)
 						? "<span style='color: red; font-weight: bold;'>On Break</span>"
 						: "<span style='color: green;'>Completed</span>";

 					$log_date = date('Y-m-d', strtotime($start));
 					if ($log_date === $today) {
 						$has_today_breaks = true;
 					}
 			?>

 			<div class="activity-card"
 				 data-date="<?= $log_date ?>"
 				 style="<?= $log_date === $today ? '' : 'display: none;' ?>">
 				<div class="left-info w-100">
 					<div class="staff-name">👤 <?= htmlspecialchars($break['staff_name']); ?> (<?= htmlspecialchars($break['employee_id']); ?>)</div>
 					<div class="duration">⏯️ Pause Type: <?= htmlspecialchars($break['pause_name']); ?></div>
 					<div class="date-time">🔁 Start: <?= date('h:i A, d M Y', strtotime($start)); ?></div>
 					<div class="date-time">🔁 End: <?= !empty($break['end_datetime']) ? date('h:i A, d M Y', strtotime($break['end_datetime'])) : '-'; ?></div>
 					<div class="duration">⏱ Duration: <?= $duration; ?></div>
 					<div class="status-text">📌 Status: <?= $status; ?></div>
 				</div>
 			</div>

 			<?php endforeach;
 				if (!$has_today_breaks):
 			?>
 				<div class="no-activity">
 					<p class="no-activity-text">No break history for today</p>
 				</div>
 			<?php endif;
 			else: ?>
 				<div class="no-activity">
 					<p class="no-activity-text">No break history available</p>
 				</div>
 			<?php endif; ?>

         </div>
             </div>
         </div>
     </section>
 </div>

 <!-- Task Drawer (unchanged, optimized separately) -->
 <div id="taskOverlay" class="task-overlay" onclick="closeTaskDrawer()"></div>

 <style>
 .task-overlay {
 	display: none;
 	position: fixed;
 	top: 0;
 	left: 0;
 	width: 100%;
 	height: 100%; 
 	background-color: rgba(0, 0, 0, 0.4); z-index: 1050;
 	}

 .task-drawer.open + .task-overlay,
 .task-overlay.active {
     display: block;
 }

 </style>
 <div class="task-drawer" id="taskDrawer">
     <div class="drawer-header">
         <h4>Update Task</h4>
         <button type="button" class="close-drawer" onclick="closeTaskDrawer()">&times;</button>
     </div>
     <div class="drawer-body">
        <form id="updateTaskForm">
 			<input type="hidden" id="task_id" name="task_id">

 			<div class="form-group">
 				<label for="task_title">Title</label>
 				<input type="text" class="form-control" id="task_title" name="task_title">
 			</div>

 			<div class="form-group">
 				<label for="location">Location</label>
 				<input type="text" class="form-control" id="location" name="location">
 			</div>

 			<div class="form-group">
 				<label for="start_time">Start Time</label>
 				<input type="datetime-local" class="form-control" id="start_time" name="start_time">
 			</div>

 			<div class="form-group">
 				<label for="task_status">Status</label>
 				<select class="form-control" id="task_status" name="task_status">
 					<option value="Pending">Pending</option>
 					<option value="In Progress">In Progress</option>
 					<option value="Completed">Completed</option>
 					<option value="On Hold">On Hold</option>
 				</select>
 			</div>

 			<div class="form-group">
 				<label for="end_time">End Time</label>
 				<input type="datetime-local" class="form-control" id="end_time" name="actual_end_time">
 			</div>
 			<div class="form-group">
 				<label for="proof">Proof of Work</label>
 				<input type="text" name="proof" class="form-control" id="proof" placeholder="Enter URL or short note">
 			</div>

 			<button type="button" class="btn btn-primary" onclick="updateTask()">Update</button>
 		</form>

     </div>
 </div>

 	
<!---Calendar Events -->
<!-- FullCalendar CSS and JS -->
<link rel="stylesheet" href="<?= base_url('assets/vendor/fullcalendar/fullcalendar.min.css') ?>">
<script src="<?= base_url('assets/vendor/fullcalendar/lib/moment.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/fullcalendar/fullcalendar.min.js') ?>"></script>

 <div class="row">
     <div class="col-md-12">
         <div class="panel">
 			<div class="panel-heading">
 				<div class="row">
 					<div class="col-md-10">
 						<h4 class="card-title"><b>Calendar Events</b></h4>
 					</div>
 				</div>
 				<div class="panel-body">
					<div id="event_calendar" style="min-height: 400px;"></div>
 				</div>
 			</div>
			
			<!-- Modal -->
			<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
				<section class="panel">
					<header class="panel-heading">
						<div class="panel-btn">
							<button onclick="fn_printElem('printResult')" class="btn btn-default btn-circle icon"><i class="fas fa-print"></i></button>
						</div>
						<h4 class="panel-title"><i class="fas fa-info-circle"></i> <?= translate('event_details') ?></h4>
					</header>
					<div class="panel-body">
						<div id="printResult" class="pt-sm pb-sm">
							<div class="table-responsive">
								<table class="table table-bordered table-condensed text-dark tbr-top" id="ev_table"></table>
							</div>
						</div>
					</div>
					<footer class="panel-footer text-right">
						<button class="btn btn-default modal-dismiss"><?= translate('close') ?></button>
					</footer>
				</section>
			</div>
 		</div>
 	</div>
 </div>
	

 <div class="row">
     <div class="col-md-12">
         <div class="panel">
 			<div class="panel-heading">
 				<div class="row">
 					<div class="col-md-10">
 						<h4 class="card-title"><b>Daily Work Summary</b> (Last 7 Days)</h4>
 					</div>
 				</div>
 				<div class="panel-body">
 					<div class="table-responsive responsive-table">
 						
						<table class="table table-bordered table-hover table-condensed table_default" cellspacing="0" width="100%">
 							<thead>
 								<tr>
 								<th style="width: 10px;"><?= translate('sl') ?></th>
 								<th style="width: 80px;"><?= translate('employee') ?></th>
 								<th style="width: 40px;"><?= translate('date') ?></th>
 								<th style="width: 400px;"><?= translate('assigned_tasks') ?></th>
 								<th style="width: 400px;"><?= translate('completed_tasks') ?></th>
 								<th class="text-center" style="width: 50px;"><?= translate('Time') ?></th>
 								<th class="text-center" style="width: 30px;"><?= translate('ratio') ?></th>
 								<th class="text-center" style="width: 30px;"><?= translate('overall_rating') ?></th>

 								</tr>
 							</thead>
 							<tbody>
 								<?php 
 								if (!empty($recent_summaries)):
 									$count = 1;
 									foreach ($recent_summaries as $row):
 										$assigned = json_decode($row['assigned_tasks'], true);
 										$completed = json_decode($row['completed_tasks'], true);
 										$totalTime = 0;
 								?>
 									<tr>
 										<td><?php echo $count++; ?></td>
 										<td>
 											<?php
 												$getStaff = $this->db->select('name,staff_id')->where('id', $row['user_id'])->get('staff')->row_array();
 												echo $getStaff['staff_id'] . ' - ' . $getStaff['name'];
 											?>
 										</td>
 										<td><?= date("M d, Y", strtotime($row['summary_date'])); ?></td>
 										
 										<!-- Assigned Tasks -->
 										<td>
 											<ul class="pl-3 mb-0">
 												<?php foreach ($assigned as $task): ?>
 													<li>
 														<?= html_escape($task['title']); ?>
 														<?php
 														$link = trim($task['link'] ?? '');
 														if (!empty($link)) {
 															if (strpos($link, 'http://') === 0 || strpos($link, 'https://') === 0) {
 																echo '<a href="' . html_escape($link) . '" target="_blank" title="Open link">';
 																echo '<i class="fas fa-link text-primary ml-1"></i>';
 																echo '</a>';
 															} else {
 																echo '<strong class="text-muted small"> - ' . html_escape($link) . '</strong>';
 															}
 														}
 														$planner_status = isset($task['planner']) && $task['planner'] == 1 ? 'In Planner' : 'Not in Planner';
 														?>
 														<span class="text-muted small"> - <?= $planner_status ?></span>
 													</li>
 												<?php endforeach; ?>
 											</ul>
 										</td>

 										<!-- Completed Tasks with Time & Planner -->
 										<td>
 											<ul class="pl-3 mb-0">
 												<?php foreach ((array) $completed as $task): ?>
 													<?php
 														$taskTitle = html_escape($task['title']);
 														$linkRaw   = trim($task['link'] ?? '');
 														$taskTime = isset($task['time']) ? floatval($task['time']) : 0;
 														$totalTime += $taskTime;
 														$hours = floor($totalTime); 
 														$minutes = round(($totalTime - $hours) * 60);
 														$timeFormatted = "{$hours} hour" . ($hours != 1 ? "s" : "") . " {$minutes} min" . ($minutes != 1 ? "s" : "");

 														$isURL = (strpos($linkRaw, 'http://') === 0 || strpos($linkRaw, 'https://') === 0);
 													?>
 													<li>
 														<?= $taskTitle ?>

 														<?php if (!empty($linkRaw)): ?>
 															<?php if ($isURL): ?>
 																<a href="<?= html_escape($linkRaw) ?>" target="_blank" title="Open link">
 																	<i class="fas fa-check-circle text-success ml-1"></i>
 																</a>
 															<?php else: ?>
 																<strong class="text-muted small"> - <?= $linkRaw ?></strong>
 															<?php endif; ?>
 														<?php endif; ?>
 														<span class="text-muted small">(<?= $taskTime ?> hours)</span>
 													</li>
 												<?php endforeach; ?>
 											</ul>
 										</td>

 										<!-- Total Time -->
 										<td class="text-center"><?= $timeFormatted?></td>

 										<!-- Completion Ratio -->
 										<td class="text-center"><?= $row['completion_ratio'] ?>%</td>
 										<td class="text-center">
 											<?= is_null($row['rating']) ? 'Under Review' : $row['rating'] ?>
 										</td>
 									</tr>
 								<?php 
 									endforeach;
 								else: ?>
 									<tr>
 										<td colspan="9" class="text-center">No summary found.</td>
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


<?php if (!empty($meetings)): ?>
<section class="panel appear-animation mt-sm"
         data-appear-animation="<?= $global_config['animations'] ?>"
         data-appear-animation-delay="100">
    <header class="panel-heading">
        <h4 class="panel-title">
            <i class="fas fa-tasks"></i> <?= translate('recent team meetings') ?>
        </h4>
    </header>
    <div class="panel-body">
        <div class="table-responsive responsive-table">
            <table class="table table-bordered table-hover table-condensed mb-none text-dark table-export" style="width: 100%;">
                <thead class="text-nowrap">
                    <tr>
                        <th style="width:2%;"><?= translate('sl'); ?></th>
                        <th style="width:30%;"><?= translate('title'); ?></th>
                        <th style="width:5%;"><?= translate('type'); ?></th>
                        <th style="width:36%;"><?= translate('participants'); ?></th>
                        <th style="width:10%;"><?= translate('date'); ?></th>
                        <th style="width:15%;"><?= translate('created_by'); ?></th>
                        <th style="width:2%;"><?= translate('action'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($meetings)): ?>
                        <?php $i = 1; foreach ($meetings as $meeting): ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td><?= $meeting['title']; ?></td>
                                <td>
                                    <span class="label label-<?= ($meeting['meeting_type'] == 'management') ? 'warning' : 'success'; ?>">
                                        <?= ucfirst($meeting['meeting_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?= !empty($meeting['participant_names']) ? $meeting['participant_names'] : '-'; ?>
                                </td>
                                <td><?= date('d M Y', strtotime($meeting['date'])); ?></td>
                                <td><?= $meeting['created_by_name']; ?></td>
                                <td class="min-w-c">
                                    <a href="javascript:void(0);" class="btn btn-circle icon btn-default" onclick="getDetails('<?= $meeting['id'] ?>')">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (!empty($meeting['attachments'])): ?>
                                        <a href="<?= base_url('team_meetings/download/' . $meeting['id']); ?>" 
                                           class="btn btn-circle icon btn-default" data-toggle="tooltip" data-original-title="<?= translate('download'); ?>">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">
                                <?= translate('no_task_logs_found') ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

		<!-- View Modal -->
		<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="meeting_modal" style="max-width: 70%;">
			<section class="panel" id='quick_view'></section>
		</div>
		
		<script>
		// get details
	function getDetails(id) {
	    $.ajax({
	        url: base_url + 'team_meetings/getDetails',
	        type: 'POST',
	        data: {'id': id},
	        dataType: "html",
	        success: function (data) {
				$('#quick_view').html(data);
				mfp_modal('#meeting_modal');
	        }
	    });
	}
		</script>

<?php endif; ?>
<style>

@media (min-width: 769px) {
    .responsive-card-table {
        display: none;
    }
}
.noti-card {
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 10px;
    background-color: #fff;
}
.noti-card span {
    display: block;
    margin-bottom: 4px;
}
</style>

 <script>
    document.addEventListener('DOMContentLoaded', function () {
     const confirmModal = document.getElementById('confirmationModal');
     const confirmBtn = document.getElementById('confirmBtn');
     const cancelBtn = document.getElementById('cancelBtn');
     const closeModalBtn = document.querySelector('.close-modal');
     const remarksSection = document.getElementById('remarksSection');
     
     let currentAction = ''; // To track the current action (check-in or check-out)
     let staffId = '';

     // Open the confirmation modal for Check-In or Check-Out
     function openConfirmationModal(action, id) {
         currentAction = action;
         staffId = id;
         document.getElementById('modalMessage').textContent = `Are you sure you want to ${action}?`;

         // Show or hide the remarks section based on the action
         if (currentAction === 'Check Out') {
             remarksSection.style.display = 'none'; // Hide the remarks input field for Check-Out
         } else {
             remarksSection.style.display = 'block'; // Show the remarks input field for Check-In
         }

         confirmModal.classList.add('active');
     }

     // Close the confirmation modal
     function closeConfirmationModal() {
         confirmModal.classList.remove('active');
     }

     // Event listener for Check-In button
     document.querySelectorAll('.checkinBtn').forEach(btn => {
         btn.addEventListener('click', function () {
             const staffId = btn.getAttribute('data-staff-id');
             openConfirmationModal('Check In', staffId);
         });
     });

     // Event listener for Check-Out button
     document.querySelectorAll('.checkoutBtn').forEach(btn => {
         btn.addEventListener('click', function () {
             const staffId = btn.getAttribute('data-staff-id');
             openConfirmationModal('Check Out', staffId);
         });
     });

     // Confirm the action (Check-In or Check-Out)
     confirmBtn.addEventListener('click', function () {
         if (currentAction === 'Check In') {
             // Perform Check-In action via AJAX
             checkInAction(staffId);
         } else if (currentAction === 'Check Out') {
             // Perform Check-Out action via AJAX
             checkOutAction(staffId);
         }
         closeConfirmationModal();
     });

     // Cancel the action (Close Modal)
     cancelBtn.addEventListener('click', closeConfirmationModal);
     closeModalBtn.addEventListener('click', closeConfirmationModal);

     // Perform the Check-In action (AJAX)
     function checkInAction(staffId) {
         const now = new Date();
         const currentTime = now.toTimeString().split(' ')[0];
         const today = now.toISOString().split('T')[0];
         const remarks = document.getElementById('remarks').value;  // Capture remarks

         $.ajax({
             url: "<?= base_url('dashboard/manual_checkin') ?>",
             method: "POST",
             dataType: "json",
             data: {
                 in_time: currentTime,
                 date: today,
                 staff_id: staffId,
                 remarks: remarks  // Include remarks in the data
             },
             success: function (res) {
                 if (res.status === 'inserted' || res.status === 'updated') {
                     // Redirect to dashboard
                     window.location.href = "<?= base_url('dashboard') ?>";
                 } else if (res.status === 'error') {
                     alert(res.message);
                 }
             },
             error: function (xhr) {
                 alert('Check-in failed: ' + xhr.responseText);
             }
         });
     }

     // Perform the Check-Out action (AJAX)
     function checkOutAction(staffId) {
         const now = new Date();
         const currentTime = now.toTimeString().split(' ')[0];
         const today = now.toISOString().split('T')[0];
         const remarks = '';  // No remarks for Check-Out, so we send an empty value or null

         $.ajax({
             url: "<?= base_url('dashboard/manual_checkout') ?>",
             method: "POST",
             dataType: "json",
             data: {
                 out_time: currentTime,
                 date: today,
                 staff_id: staffId,
                 //remarks: remarks  // No remarks in the data for Check-Out
             },
             success: function (res) {
                 if (res.status === 'updated') {
                     // Redirect to dashboard
                     window.location.href = "<?= base_url('dashboard') ?>";
                 }
             },
             error: function (xhr, status, error) {
                 alert("Check-out failed. Please try again.");
             }
         });
     }
 });

 </script>

 <!-- JS to update hidden status --> 
 <script>
 	document.querySelectorAll('.single-update-btn').forEach(function(button) {
 	  button.addEventListener('click', function() {
 		var attendance_id = this.getAttribute('data-id');
 		var key = this.getAttribute('data-key');
 		var status = document.querySelector(`input[name="attendance[${key}][status]"]:checked`)?.value;

 		if (!attendance_id || !status) {
 		  alert('Please select a status.');
 		  return;
 		}

 		fetch('<?= base_url('dashboard/update_attendance_single') ?>', {
 		  method: 'POST',
 		  headers: {
 			'Content-Type': 'application/x-www-form-urlencoded',
 			'X-CSRF-TOKEN': '<?= $this->security->get_csrf_hash(); ?>'
 		  },
 		  body: `attendance_id=${attendance_id}&status=${status}&<?= $this->security->get_csrf_token_name(); ?>=<?= $this->security->get_csrf_hash(); ?>`
 		})
 		.then(response => response.text())
 		.then(result => {
 		  location.reload();
 		})
 		.catch(error => {
 		  alert('Error updating attendance');
 		});
 	  });
 	});
 	</script>
 <script>
   document.getElementById('openConfirmModal').addEventListener('click', function () {
     $('#confirmModal').modal('show');
   });

   document.getElementById('confirmSubmit').addEventListener('click', function () {
     document.getElementById('bulkForm').submit();
   });
 </script>

 <!-- Add Tooltip Activation Script -->
 <script>
     $(document).ready(function() {
         // Initialize tooltips
         $('[data-toggle="tooltip"]').tooltip();
     });
 </script>

 <script>
 function filterTaskLogs() {
     const input = document.getElementById('taskLogSearch').value.toLowerCase();
     const cards = document.querySelectorAll('#taskLogContainer .activity-card');

     cards.forEach(card => {
         const text = card.innerText.toLowerCase();
         card.style.display = text.includes(input) ? '' : 'none';
     });
 }

 function filterTasksBy(type) {
     const cards = document.querySelectorAll('#taskLogContainer .activity-card');
     const today = new Date().toISOString().split('T')[0];
     const yesterday = new Date(Date.now() - 86400000).toISOString().split('T')[0];

     let matchDates = [];

     if (type === 'today') {
         matchDates = [today];
     } else if (type === 'yesterday') {
         matchDates = [yesterday];
     } else {
         matchDates = [today, yesterday];
     }

     cards.forEach(card => {
         const cardDate = card.getAttribute('data-date');
         const show = type === 'older' ? !matchDates.includes(cardDate) : matchDates.includes(cardDate);
         card.style.display = show ? '' : 'none';
     });

     // Remove active class from all task buttons
     ['taskBtnToday', 'taskBtnYesterday', 'taskBtnOlder'].forEach(id => {
         const btn = document.getElementById(id);
         if (btn) {
             btn.classList.remove('btn-yellow');
             btn.classList.add('btn-light');
         }
     });
     
     // Add active class to selected button
     const activeBtn = document.getElementById('taskBtn' + capitalizeFirstLetter(type));
     if (activeBtn) {
         activeBtn.classList.remove('btn-light');
         activeBtn.classList.add('btn-yellow');
     }
 }

 function capitalizeFirstLetter(string) {
     return string.charAt(0).toUpperCase() + string.slice(1);
 }
 </script>

 <!-- JavaScript for Task Drawer -->
 <script>
 function toDateTimeLocalString(dateString) {
     const date = new Date(dateString);
     if (isNaN(date.getTime())) return ''; // Invalid date fallback

     const year = date.getFullYear();
     const month = String(date.getMonth() + 1).padStart(2, '0');
     const day = String(date.getDate()).padStart(2, '0');
     const hours = String(date.getHours()).padStart(2, '0');
     const minutes = String(date.getMinutes()).padStart(2, '0');
     return `${year}-${month}-${day}T${hours}:${minutes}`;
 }
 ;
 function get_loggedin_user_id() {
     // Replace this with your actual method if needed (e.g. via JS var passed from PHP)
     return parseInt("<?= get_loggedin_user_id() ?>");
 }

 function handleTaskCardClick(elem) {
     const staffId = parseInt(elem.dataset.staffId);
     if (staffId !== get_loggedin_user_id()) {
         Swal.fire({
             icon: 'warning',
             title: 'Permission Denied',
             text: "You can't change this log.",
             confirmButtonText: 'OK',
             confirmButtonColor: '#d33'
         });
         return;
     }

     openTaskDrawer(
         parseInt(elem.dataset.taskId),
         elem.dataset.taskStatus,
         elem.dataset.taskTitle,
         elem.dataset.location,
         elem.dataset.startTime,
         elem.dataset.endTime,
         elem.dataset.proof,
         parseInt(elem.dataset.isScrap),
         elem.dataset.reason
     );
 }


 function openTaskDrawer(taskId, status, title, location, startTime, endTime, proof, isScrap, reason) {
     console.log("Drawer opened for task ID:", taskId);
     document.getElementById('task_id').value = taskId;
     document.getElementById('task_status').value = status || '';
     document.getElementById('task_title').value = title || '';
     document.getElementById('location').value = location || '';
     document.getElementById('start_time').value = toDateTimeLocalString(startTime);
     document.getElementById('end_time').value = toDateTimeLocalString(endTime || new Date());
     document.getElementById('proof').value = proof || '';

     const reasonField = document.querySelector('[name="reason"]');
     if (reasonField) {
         reasonField.value = reason || '';
     }

     toggleScrapFields();

     document.getElementById('taskDrawer').classList.add('open');
     document.getElementById('taskOverlay').classList.add('active');
 }

 function closeTaskDrawer() {
     document.getElementById('taskDrawer').classList.remove('open');
     document.getElementById('taskOverlay').classList.remove('active'); // Hide overlay
 }

 function updateTask() {
     const taskId = document.getElementById('task_id').value;
     const status = document.getElementById('task_status').value;
     const title = document.getElementById('task_title').value;
     const location = document.getElementById('location').value;
     const startTime = document.getElementById('start_time').value;
     const endTime = document.getElementById('end_time').value;
 	const proof = document.getElementById('proof').value;

 	const isScrap = document.getElementById('isScrapWork')?.checked ? 1 : 0;
     const reason = document.querySelector('[name="reason"]')?.value || '';

     // Validate required fields
     if (!taskId || !status) {
         alert('Task ID and Status are required');
         return;
     }
 	
     $.ajax({
         url: '<?= base_url('dashboard/update_task_log') ?>',
         type: 'POST',
         data: {
             task_id: taskId,
             task_status: status,
             task_title: title,
             location: location,
             start_time: startTime,
             end_time: endTime,
             reason: reason,
             proof: proof
         },
         success: function(response) {
             const result = JSON.parse(response);
             if (result.success) {
                 closeTaskDrawer();
 				window.location.href = window.location.href; // redirect to current page
             } else {
                 alert('Error updating task: ' + result.message);
             }
         },
         error: function() {
             alert('An error occurred while updating the task.');
         }
     });
 }


 // Close drawer when clicking outside
 document.addEventListener('click', function(event) {
     const drawer = document.getElementById('taskDrawer');
     const cards = document.querySelectorAll('.activity-card');
     
     let clickedInsideCard = false;
     cards.forEach(card => {
         if (card.contains(event.target)) {
             clickedInsideCard = true;
         }
     });
     
     if (!drawer.contains(event.target) && !clickedInsideCard && drawer.classList.contains('open')) {
         closeTaskDrawer();
     }
 });
 </script>
 <!-- JavaScript for Task Drawer -->

 <script>
 function filterBreakLogs() {
     const input = document.getElementById('breakHistorySearch').value.toLowerCase();
     const cards = document.querySelectorAll('#breakHistoryContainer .activity-card');

     cards.forEach(card => {
         const text = card.innerText.toLowerCase();
         card.style.display = text.includes(input) ? '' : 'none';
     });
 }

 function filterBreaksBy(type) {
     const cards = document.querySelectorAll('#breakHistoryContainer .activity-card');
     const today = new Date().toISOString().split('T')[0];
     const yesterday = new Date(Date.now() - 86400000).toISOString().split('T')[0];

     let matchDates = [];

     if (type === 'today') {
         matchDates = [today];
     } else if (type === 'yesterday') {
         matchDates = [yesterday];
     } else {
         matchDates = [today, yesterday];
     }

     cards.forEach(card => {
         const cardDate = card.getAttribute('data-date');
         const show = type === 'older' ? !matchDates.includes(cardDate) : matchDates.includes(cardDate);
         card.style.display = show ? '' : 'none';
     });

     // Remove active class from all break buttons
     ['btnToday', 'btnYesterday', 'btnOlder'].forEach(id => {
         const btn = document.getElementById(id);
         if (btn) {
             btn.classList.remove('btn-yellow');
             btn.classList.add('btn-light');
         }
     });
     
     // Add active class to selected button
     const activeBtn = document.getElementById('btn' + capitalizeFirstLetter(type));
     if (activeBtn) {
         activeBtn.classList.remove('btn-light');
         activeBtn.classList.add('btn-yellow');
     }
 }

 function capitalizeFirstLetter(string) {
     return string.charAt(0).toUpperCase() + string.slice(1);
 }
 </script>

 	<!-- Include Chart.js CDN -->
 <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
 <!-- Chart Script -->
 <script>
 document.addEventListener('DOMContentLoaded', function () {
   const ctx = document.getElementById('attendancePieChart').getContext('2d');

   const chart = new Chart(ctx, {
     type: 'doughnut',
     data: {
 		  labels: ['Present', 'Late', 'Absent', 'Leave'],
 		  datasets: [{
 			data: [
 			  <?= $attendance_pie_data['P'] ?? 0 ?>,
 			  <?= $attendance_pie_data['L'] ?? 0 ?>,
 			  <?= $attendance_pie_data['A'] ?? 0 ?>,
 			  <?= $attendance_pie_data['LV'] ?? 0 ?>
 			],
 			backgroundColor: ['#4CAF50', '#FFC107', '#F44336', '#2196F3'],
 			borderWidth: 0,
 		  }]
 		},

     options: {
       cutout: '70%',
       rotation: -90,
       circumference: 180,
       plugins: {
         legend: {
           display: false
         },
         tooltip: {
           callbacks: {
             label: function(context) {
               return `${context.label}: ${context.parsed}`;
             }
           }
         }
       }
     }
   });

   // Tracker Issues Pie Chart
   const trackerCtx = document.getElementById('trackerIssuesPieChart');
   if (trackerCtx) {
     const trackerChart = new Chart(trackerCtx.getContext('2d'), {
       type: 'doughnut',
       data: {
         labels: ['To-Do', 'In Progress', 'Completed', 'Canceled'],
         datasets: [{
           data: [
             <?= $tracker_issues_pie_data['todo'] ?? 0 ?>,
             <?= $tracker_issues_pie_data['in_progress'] ?? 0 ?>,
             <?= $tracker_issues_pie_data['completed'] ?? 0 ?>,
             <?= $tracker_issues_pie_data['canceled'] ?? 0 ?>
           ],
           backgroundColor: ['#0ea5e9', '#8b5cf6', '#16a34a', '#b91c1c'],
           borderWidth: 0,
         }]
       },
       options: {
         cutout: '70%',
         rotation: -90,
         circumference: 180,
         plugins: {
           legend: {
             display: false
           },
           tooltip: {
             callbacks: {
               label: function(context) {
                 return `${context.label}: ${context.parsed}`;
               }
             }
           }
         }
       }
     });
   }

   // RDC Tasks Pie Chart
   const rdcCtx = document.getElementById('rdcTaskPieChart');
   if (rdcCtx) {
     const rdcChart = new Chart(rdcCtx.getContext('2d'), {
       type: 'doughnut',
       data: {
         labels: ['Pending', 'Completed', 'Canceled', 'Hold'],
         datasets: [{
           data: [
             <?= $rdc_task_pie_data['pending'] ?? 0 ?>,
             <?= $rdc_task_pie_data['completed'] ?? 0 ?>,
             <?= $rdc_task_pie_data['canceled'] ?? 0 ?>,
             <?= $rdc_task_pie_data['hold'] ?? 0 ?>
           ],
           backgroundColor: ['#f59e0b', '#22c55e', '#ef4444', '#6b7280'],
           borderWidth: 0,
         }]
       },
       options: {
         cutout: '70%',
         rotation: -90,
         circumference: 180,
         plugins: {
           legend: {
             display: false
           },
           tooltip: {
             callbacks: {
               label: function(context) {
                 return `${context.label}: ${context.parsed}`;
               }
             }
           }
         }
       }
     });
   }
 });
 </script>

 <script>
 document.addEventListener('DOMContentLoaded', function () {
 	const drawer = document.getElementById('logTaskDrawer');
 	const overlay = document.querySelector('.drawer-overlay');
 	const closeBtn = document.querySelector('.close-drawer');
 	const taskBtns = document.querySelectorAll('.log-task-btn');
 	const staffIdInput = document.getElementById('logTaskStaffId');

 	taskBtns.forEach(btn => {
 		btn.addEventListener('click', () => {
 			const staffId = btn.getAttribute('data-staff-id');
 			staffIdInput.value = staffId;
 			drawer.classList.add('active');
 			overlay.style.display = 'block';
 		});
 	});

 	closeBtn.addEventListener('click', closeDrawer);
 	overlay.addEventListener('click', closeDrawer);

 	function closeDrawer() {
 		drawer.classList.remove('active');
 		overlay.style.display = 'none';
 	}
 });
 </script>

 
<script>
(function ($) {
    // Wait for DOM and all scripts to load
    $(document).ready(function() {
        // Check if FullCalendar is loaded
        if (typeof $.fn.fullCalendar === 'undefined') {
            console.error('FullCalendar library is not loaded');
            $('#event_calendar').html('<div class="alert alert-warning">Calendar library not loaded. Please refresh the page.</div>');
            return;
        }
        
        try {
            $('#event_calendar').fullCalendar({
                header: {
                    left: 'prev,next,today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay,listWeek'
                },
                firstDay: 1,
                droppable: false,
                editable: true,
                timezone: 'UTC',
                height: 'auto',
                //lang: '<?= $language ?>',
                events: {
                    url: "<?= base_url('event/getEventsList/') ?>",
                    error: function() {
                        console.error('Failed to load calendar events');
                        $('#event_calendar').append('<div class="alert alert-info">No events to display or failed to load events.</div>');
                    }
                },
                eventRender: function (event, element) {
                    // Store the type as a data attribute for reliable access
                    $(element).attr('data-event-type', event.type);
                    $(element).on("click", function () {
                        var eventType = $(this).attr('data-event-type');
                        viewCalendarEvent(event.id, eventType);
                    });
                },
                loading: function(bool) {
                    if (bool) {
                        $('#event_calendar').append('<div class="loading-indicator">Loading events...</div>');
                    } else {
                        $('.loading-indicator').remove();
                    }
                }
            });
        } catch (error) {
            console.error('Error initializing FullCalendar:', error);
            $('#event_calendar').html('<div class="alert alert-danger">Error loading calendar. Please check console for details.</div>');
        }
    });
})(jQuery);

function viewCalendarEvent(id, type) {
    var base_url = '<?= base_url() ?>';
    
    if (type === 'leave') {
        // Handle leave click - show leave details
        $.ajax({
            url: base_url + "event/getLeaveDetails",
            type: 'POST',
            data: {
                leave_id: id
            },
            success: function (data) {
                $('#ev_table').html(data);
                mfp_modal('#modal');
            }
        });
    } else {
        // Handle regular event click - use the existing app.js function
        viewEvent(id);
    }
}

$(document).on('click', '.modal-dismiss', function (e) {
 	e.preventDefault();
 	$.magnificPopup.close();
 });

</script>


<style>
.fc-event-primary {
    background-color: #2F5249 !important;
    border-color: #2F5249 !important;
    color: #fff !important;
}
.fc-event-warning {
    background-color: #3B060A !important;
    border-color: #3B060A !important;
    color: #fff !important;
}
.fc-event {
    background-color: #2F5249 !important;
    border-color: #2F5249 !important;
    color: #fff !important;
    white-space: normal !important;
    word-wrap: break-word !important;
    overflow-wrap: break-word !important;
}
.fc-event:hover {
    background-color: #3e80cd !important;
    border-color: #3e80cd !important;
}
.fc-event .fc-title {
    color: #fff !important;
    white-space: normal !important;
    word-wrap: break-word !important;
    line-height: 1.2 !important;
}
.fc-event .fc-time {
    color: #fff !important;
}
/* Leave events specific styling */
.fc-event[data-event-type="leave"] {
    background-color: #3B060A !important;
    border-color: #3B060A !important;
    color: #fff !important;
}
.fc-event[data-event-type="leave"]:hover {
    background-color: #C83F12 !important;
    border-color: #C83F12 !important;
}
</style>


 
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Filter employees by status
function filterEmployees(status) {
    const cards = document.querySelectorAll('.employee-card');
    const buttons = document.querySelectorAll('.filter-btn');
    
    // Update button states
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    // Filter cards with smooth transition
    cards.forEach(card => {
        if (status === 'all' || card.dataset.status === status) {
            card.style.display = 'flex';
            card.style.opacity = '1';
        } else {
            card.style.display = 'none';
            card.style.opacity = '0';
        }
    });
}

// Initialize charts
document.addEventListener('DOMContentLoaded', function() {
    // Attendance Chart
    const attendanceCtx = document.getElementById('attendancePieChart');
    if (attendanceCtx) {
        new Chart(attendanceCtx, {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Late', 'Absent', 'Leave'],
                datasets: [{
                    data: [
                        <?= $attendance_pie_data['P'] ?? 0 ?>,
                        <?= $attendance_pie_data['L'] ?? 0 ?>,
                        <?= $attendance_pie_data['A'] ?? 0 ?>,
                        <?= $attendance_pie_data['LV'] ?? 0 ?>
                    ],
                    backgroundColor: ['#00a51f', '#ed8936', '#e30404', '#667eea'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '70%',
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 1,
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
    
    // Tracker Issues Chart
    const trackerCtx = document.getElementById('trackerIssuesPieChart');
    if (trackerCtx) {
        new Chart(trackerCtx, {
            type: 'doughnut',
            data: {
                labels: ['To-Do', 'In Progress', 'Completed', 'Canceled'],
                datasets: [{
                    data: [
                        <?= $tracker_issues_pie_data['todo'] ?? 0 ?>,
                        <?= $tracker_issues_pie_data['in_progress'] ?? 0 ?>,
                        <?= $tracker_issues_pie_data['completed'] ?? 0 ?>,
                        <?= $tracker_issues_pie_data['canceled'] ?? 0 ?>
                    ],
                    backgroundColor: ['#0ea5e9', '#8b5cf6', '#16a34a', '#b91c1c'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '70%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
    
    // RDC Tasks Chart
    const rdcCtx = document.getElementById('rdcTaskPieChart');
    if (rdcCtx) {
        new Chart(rdcCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Completed', 'Canceled', 'Hold'],
                datasets: [{
                    data: [
                        <?= $rdc_task_pie_data['pending'] ?? 0 ?>,
                        <?= $rdc_task_pie_data['completed'] ?? 0 ?>,
                        <?= $rdc_task_pie_data['canceled'] ?? 0 ?>,
                        <?= $rdc_task_pie_data['hold'] ?? 0 ?>
                    ],
                    backgroundColor: ['#f59e0b', '#22c55e', '#ef4444', '#6b7280'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '70%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
});
</script>