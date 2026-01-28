<?php
// Convert Google Drive sharing links to viewable ones
function convertGoogleDriveLink($link) {
    if (strpos($link, 'drive.google.com') !== false && preg_match('/\/d\/(.*?)\//', $link, $matches)) {
        return "https://drive.google.com/uc?export=view&id=" . $matches[1];
    }
    return $link;
}

$widget = ((in_array(loggedin_role_id(), [1, 2, 3, 5])) ? 4 : 6);
?>
<section class="panel">
    <?= form_open($this->uri->uri_string()); ?>
    <header class="panel-heading">
        <h4 class="panel-title"><?= translate('select_ground') ?></h4>
    </header>
    <div class="panel-body">
        <div class="row mb-sm">
           <?php if (in_array(loggedin_role_id(), [1, 2, 3, 5])) : ?>
                <div class="col-md-4 mb-sm">
                    <div class="form-group">
                        <label class="control-label"><?= translate('business') ?> <span class="required">*</span></label>
                        <?php
                        $arrayBranch = ['all' => translate('all')] + $this->app_lib->getSelectList('branch');
                        echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), 
                            "class='form-control' onchange='getDesignationByBranch(this.value)' 
                            data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
                        ?>
                        <span class="error"><?= form_error('branch_id') ?></span>
                    </div>
                </div>
            

            <div class="col-md-<?= $widget ?> mb-sm">
                <div class="form-group">
                    <label class="control-label"><?= translate('role') ?> <span class="required">*</span></label>
                    <?php
                    $role_list = ['all' => translate('all')] + $this->app_lib->getRoles();
                    echo form_dropdown("staff_role", $role_list, set_value('staff_role'), 
                        "class='form-control' data-plugin-selectTwo required data-width='100%' data-minimum-results-for-search='Infinity'");
                    ?>
                </div>
            </div>
			<?php endif; ?>
            <div class="col-md-<?= $widget ?> mb-sm">
                <div class="form-group">
                    <label class="control-label"><?= translate('month') ?> <span class="required">*</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="timestamp" value="<?= set_value('timestamp', date('Y-F')) ?>" 
                            data-plugin-datepicker required
                            data-plugin-options='{ "format": "yyyy-MM", "minViewMode": "months", "orientation": "bottom"}' />
                        <span class="input-group-addon"><i class="icon-event icons"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer class="panel-footer">
        <div class="row">
            <div class="col-md-offset-10 col-md-2">
                <button type="submit" name="search" value="1" class="btn btn-default btn-block">
                    <i class="fas fa-filter"></i> <?= translate('filter') ?>
                </button>
            </div>
        </div>
    </footer>
    <?= form_close(); ?>
</section>

<?php if (isset($stafflist)): ?>
<?php 
    $weekends = $this->attendance_model->getWeekendDaysSession($branch_id);
    $getHolidays = explode('","', $this->attendance_model->getHolidays($branch_id));
	
	$validDays = [];
	for ($i = 1; $i <= $days; $i++) {
		$date = date('Y-m-d', strtotime("$year-$month-$i"));
		$isWeekend = in_array($date, $weekends);
		$isHoliday = in_array($date, $getHolidays);
		$validDays[$i] = [
			'date' => $date,
			'type' => $isWeekend ? 'weekend' : ($isHoliday ? 'holiday' : 'working')
		];

	}

?><section class="panel appear-animation mt-sm" data-appear-animation="<?= $global_config['animations'] ?>" data-appear-animation-delay="100">
    <header class="panel-heading">
        <h4 class="panel-title"><i class="fas fa-users"></i> Attendance Report</h4>
    </header>
    <div class="panel-body">
        <!-- Export Button -->
        <div class="mb-sm text-right">
            <button id="exportExcel" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel"></i> Export to Excel
            </button>
        </div>

        <!-- Initialize DataTable -->
        <script>
        $(document).ready(function() {
            $('#attendanceTable').DataTable({
                scrollX: true,
                paging: false,
                ordering: false,
                searching: true,
                fixedHeader: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search employee or data..."
                }
            });
        });
        </script>

        <!-- Attendance Table -->
        <div class="table-responsive" style="max-height: 600px; overflow-x: auto;">
            <table class="table table-bordered text-center align-middle" id="attendanceTable" style="min-width: 1200px;">
                <thead class="table-primary">
                    <tr>
                        <th>Employee</th>
                        <?php foreach ($validDays as $day => $info): ?>
                            <th><?= date('d M', strtotime($info['date'])) ?></th>
                        <?php endforeach; ?>
                        <th>Total Lates</th>
                        <th>Total Leaves</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stafflist as $row): 
                        $staffID = $row['id']; 
                        $employeeID = $row['staff_id'];
                        $late_count = 0;
                        $leave_count = 0;
                    ?>
                    <tr>
                        <td><?= $row['employee_id'] ?? $employeeID . ' - '.$row['name'] ?></td>

                        <?php foreach ($validDays as $info): 
                            $date = $info['date'];

                            // Check if on leave
                            $on_leave = $this->db->where('user_id', $staffID)
                                ->where('status', 2)
                                ->group_start()
                                    ->where('start_date <=', $date)
                                    ->where('end_date >=', $date)
                                ->group_end()
                                ->get('leave_application')
                                ->num_rows() > 0;

                            if ($info['type'] == 'holiday') {
                                echo "<td style='background-color: #ffeeba; font-weight: bold;'>H</td>";
                                continue;
                            }

                            if ($info['type'] == 'weekend') {
                                echo "<td style='background-color: #f0f0f0; font-weight: bold;'>W</td>";
                                continue;
                            }

                            if ($on_leave) {
                                $leave_count++;
                                echo "<td style='background-color: #d1ecf1; font-weight: bold;'>L</td>";
                                continue;
                            }

                            // Get attendance
                            $attendance = $this->db->get_where('staff_attendance', [
                                'staff_id' => $staffID,
                                'date' => $date
                            ])->row_array();

                            $is_pending = isset($attendance['is_manual']) && $attendance['is_manual'] == 1 && $attendance['approval_status'] != 'approved';

                            $checkin = $attendance['in_time'] ?? '';
                            $checkout = $attendance['out_time'] ?? '';
                            $checkin_img = $attendance['check_in_img'] ?? '';
                            $checkout_img = $attendance['check_out_img'] ?? '';

                            $checkin_display = $checkin ? date('h:i A', strtotime($checkin)) : '-';
                            $checkout_display = $checkout ? date('h:i A', strtotime($checkout)) : '-';

                            $content = '';
                            $cell_style = '';

                            if ($is_pending) {
                                $cell_style = "style='background-color: #fff3cd;'";
                                $content = '<span class="badge badge-warning">Needs Approval</span>';
                            } elseif (empty($checkin) && empty($checkout)) {
                                $cell_style = "style='background-color: #fdd;'";
                                $content = '- / -';
                            } else {
                                if (strtotime($checkin) > strtotime('10:30:59')) {
                                    $late_count++;
                                }
                                ob_start(); ?>
                                <?php if (!empty($checkin_img)): ?>
                                    <a href="javascript:void(0);" onclick="showImagePopup('<?= base_url('attendance/proxy_image?url=' . urlencode($checkin_img)) ?>', 'Check-in Image')">
                                        <?= $checkin_display ?>
                                    </a>
                                <?php else: ?>
                                    <?= $checkin_display ?>
                                <?php endif; ?>
                                /
                                <?php if (!empty($checkout_img)): ?>
                                    <a href="javascript:void(0);" onclick="showImagePopup('<?= base_url('attendance/proxy_image?url=' . urlencode($checkout_img)) ?>', 'Check-out Image')">
                                        <?= $checkout_display ?>
                                    </a>
                                <?php else: ?>
                                    <?= $checkout_display ?>
                                <?php endif; ?>
                                <?php $content = ob_get_clean();
                            }
                        ?>
                        <td <?= $cell_style ?>><?= $content ?></td>
                        <?php endforeach; ?>

                        <td style="background-color: #fce5cd; font-weight: bold;"><?= $late_count ?></td>
                        <td style="background-color: #d1ecf1; font-weight: bold;"><?= $leave_count ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function showImagePopup(imageUrl, title) {
    if (imageUrl && imageUrl.trim() !== '') {
        Swal.fire({
            title: title,
            imageUrl: imageUrl,
            imageAlt: title,
            showCloseButton: true,
            confirmButtonText: 'Close',
            width: 600,
            padding: '1em',
            background: '#fff'
        });
    } else {
        Swal.fire({
            icon: 'error',
            title: 'No Image Available',
            text: 'There is no image to display.',
            confirmButtonText: 'Close'
        });
    }
}
</script>
<?php endif; ?>
<!-- SheetJS for Excel Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
function showImagePopup(imageUrl, title) {
    if (imageUrl && imageUrl.trim() !== '') {
        Swal.fire({
            title: title,
            imageUrl: imageUrl,
            imageAlt: title,
            showCloseButton: true,
            confirmButtonText: 'Close',
            width: 600,
            padding: '1em',
            background: '#fff'
        });
    } else {
        Swal.fire({
            icon: 'error',
            title: 'No Image Available',
            text: 'There is no image to display.',
            confirmButtonText: 'Close'
        });
    }
}

// Export to Excel
document.getElementById('exportExcel').addEventListener('click', function () {
    var table = document.getElementById('attendanceTable');
    var wb = XLSX.utils.table_to_book(table, { sheet: "Attendance" });
    XLSX.writeFile(wb, "Attendance_Report.xlsx");
});
</script>

