<div class="row">
	<div class="col-md-3">
        <?php include 'sidebar.php'; ?>
    </div>
    <div class="col-md-9">
	
		<div class="panel">
			<header class="panel-heading d-flex justify-content-between align-items-center">
				<h4 class="panel-title"><?= html_escape($department->title) ?> - <?= translate('initiatives') ?></h4>
				<?php if (get_permission('tracker_initiatives', 'is_add')): ?>
				<div class="panel-btn">
					<a href="javascript:void(0);" class="btn btn-sm btn-primary" onclick="getComponentAddModal(<?= $department->id ?>)">
						<i class="fa fa-plus-circle"></i> <?= translate('add') ?>
					</a>
				</div>
				<?php endif; ?>
			</header>

    <style>

        .accordion-card {
            border: 1px solid #dee2e6;
            border-radius: 12px;
            margin-bottom: 5px;
            background: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .accordion-card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }
        
        .accordion-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background: linear-gradient(135deg, #2a1e28 0%, #361a11 100%);
            cursor: pointer;
            font-weight: 600;
            font-size: 1.1rem;
            color: white;
            user-select: none;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .accordion-header:hover {
            background: linear-gradient(135deg, #080b10 0%, #0e0c0c 100%)
        }
        
        .accordion-header::after {
            content: '\f078';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 0.9rem;
            transition: transform 0.3s ease;
        }
        
        .accordion-header.active::after {
            transform: rotate(180deg);
        }
        
        .lead-info {
            display: flex;
            align-items: center;
            gap: 12px;
			font-size: 1.3rem;
        }
        
        .lead-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .item-count {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 1rem;
            font-weight: 500;
			text-align:center;
        }
        
        .accordion-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease-out, padding 0.4s ease-out;
            background-color: #ffffff;
        }
        
        .accordion-body.active {
            max-height: 100%;
            padding: 24px;
        }
        
        .component-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            border-bottom: 1px solid #f0f0f1;
            transition: all 0.2s ease;
        }
        
        .component-item:last-child {
            border-bottom: none;
        }
        
        .component-item:hover {
            background-color: #f0f0f0;
            margin: 0 -12px;
            padding-left: 12px;
            padding-right: 12px;
            border-radius: 8px;
        }
        
        .component-content {
            flex: 1;
        }
        
        .component-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 4px;
        }
        
        .component-desc {
            font-size: 1.45rem;
            color: #718096;
            margin-bottom: 8px;
        }
        
        
        <!--.component-status {
            font-size: 0.85rem;
            font-weight: 600;
            color: #fff;
            padding: 4px 10px;
            border-radius: 15px;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-pending { background: linear-gradient(135deg, #f6ad55, #ed8936); }
        .status-inprogress { background: linear-gradient(135deg, #4fd1c7, #38b2ac); }
        .status-completed { background: linear-gradient(135deg, #68d391, #48bb78); }
        .status-onhold { background: linear-gradient(135deg, #fc8181, #f56565); }
         -->
        .component-actions {
            display: flex;
            gap: 8px;
            margin-left: 16px;
        }
        
     
        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #a0aec0;
            font-size: 1.1rem;
        }
        
        .no-data i {
            font-size: 3rem;
            margin-bottom: 16px;
            opacity: 0.3;
        }
        
		.square-img {
		  width: 40px;
		  height: 40px;
		  object-fit: cover;
		  border-radius: 8px; /* or 6px, depending on how round you want */
		}

        @media (max-width: 768px) {
            .component-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .component-actions {
                margin-left: 0;
                width: 100%;
            }
            
            .stats-bar {
                flex-direction: column;
                gap: 8px;
            }
        }


    </style>

    <div class="panel-body">
        <?php
        $grouped = [];
        foreach ($components as $component) {
            $grouped[$component->lead_id][] = $component;
        }
        if (!empty($grouped)):
            foreach ($grouped as $lead_id => $items):
                $getStaff = $this->db->select('name,staff_id,photo')->where('id', $lead_id)->get('staff')->row_array();
                $lead_name = $getStaff['name'];
                $lead_photo = $getStaff['photo'];
                $uniqueId = 'acc' . $lead_id;
                
                // Get initials for avatar
                $name_parts = explode(' ', $lead_name);
                $initials = '';
                foreach ($name_parts as $part) {
                    if (!empty($part)) {
                        $initials .= strtoupper(substr($part, 0, 1));
                    }
                }
                $initials = substr($initials, 0, 2); // Limit to 2 characters
        ?>
            <div class="accordion-card">
                <div class="accordion-header" onclick="toggleAccordion('<?= $uniqueId ?>')">
					<div class="col-md-10">
						<div class="lead-info">
							<div class="lead-avatar"><img class="square-img" src="<?php echo get_image_url('staff', $lead_photo); ?>" width="40" height="40" />
							</div>
							<span><?= html_escape($lead_name) ?></span>
						</div>
					</div>
					<div class="col-md-2">
                    <div class="item-count"><?= count($items) ?> items</div>
					</div>
                </div>
                <div class="accordion-body" id="accordion-body-<?= $uniqueId ?>">
                    <?php foreach ($items as $row): ?>
                    <div class="component-item">
                        <div class="component-content">
                            <div class="component-title"><?= html_escape($row->title) ?></div>
                        </div>
                        <div class="component-actions">
                            <a href="javascript:void(0);" class="btn btn-default btn-circle icon" onclick="getComponentEdit(<?= $row->id ?>)">
                                <i class="fas fa-pen-nib"></i>
                            </a>
                            <?= btn_delete('tracker/delete_component/' . $row->id); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php
            endforeach;
        else:
        ?>
            <div class="no-data">
                <i class="fas fa-inbox"></i>
                <div><?= translate('no_data_found') ?></div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleAccordion(id) {
            const header = document.querySelector(`[onclick="toggleAccordion('${id}')"]`);
            const body = document.getElementById('accordion-body-' + id);
            
            // Toggle active state
            header.classList.toggle('active');
            body.classList.toggle('active');
            
            // Close other accordions (optional - remove if you want multiple open)
            const allHeaders = document.querySelectorAll('.accordion-header');
            const allBodies = document.querySelectorAll('.accordion-body');
            
            allHeaders.forEach(h => {
                if (h !== header) {
                    h.classList.remove('active');
                }
            });
            
            allBodies.forEach(b => {
                if (b !== body) {
                    b.classList.remove('active');
                }
            });
        }

        // Your existing functions
        function getComponentEdit(id) {
            // Your existing edit functionality
            console.log('Edit component:', id);
        }

        // Initialize - you can remove this if you don't want any accordion open by default
        document.addEventListener('DOMContentLoaded', function() {
            // toggleAccordion('acc1'); // Uncomment to open first accordion by default
        });
    </script>



		</div>

		<!-- Edit Component Modal -->
		<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
			<section class="panel" id="quick_view"></section>
		</div>

		<!-- Add Component Modal -->
		<div id="componentAddModal" class="zoom-anim-dialog modal-block modal-block-primary mfp-hide">
			<section class="panel">
				<header class="panel-heading">
					<h4 class="panel-title"><i class="fas fa-plus-circle"></i> <?= translate('add_component') ?></h4>
				</header>
				<?php echo form_open('tracker/add_component', ['class' => 'form-horizontal']); ?>
				<div class="panel-body">
					<input type="hidden" name="department_id" id="component_department_id" value="">
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('title') ?> <span class="required">*</span></label>
						<div class="col-md-8">
							<input type="text" class="form-control" name="title" required />
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('description') ?></label>
						<div class="col-md-8">
							<textarea name="description" class="form-control" rows="2"></textarea>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><i class="fas fa-user"></i><?= translate(' lead') ?><span class="required">*</span></label>
						<div class="col-md-8">
							 <?php
								  $array = $this->app_lib->getSelectList('staff');
									// Remove ID 1(superadmin) from the array
									unset($array[1]);
									echo form_dropdown("lead_id", $array, array(), "class='form-control' id='lead_id' required data-plugin-selectTwo data-placeholder=\"Select\" data-width='100%' "
									);
								?>
						</div>
					</div>
				</div>
				<footer class="panel-footer">
					<div class="row">
						<div class="col-md-12 text-right">
							<button type="submit" class="btn btn-default mr-xs" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
								<i class="fas fa-plus-circle"></i> <?= translate('add_component') ?>
							</button>
							<button class="btn btn-default modal-dismiss"><?= translate('cancel') ?></button>
						</div>
					</div>
				</footer>
				<?php echo form_close(); ?>
			</section>
		</div>
	</div>
</div>

<script type="text/javascript">
function getComponentEdit(id) {
    $.ajax({
        url: base_url + 'tracker/get_component_edit',
        type: 'POST',
        data: { id: id },
        success: function(response) {
            $('#quick_view').html(response);
            mfp_modal('#modal');
        }
    });
}

function getComponentAddModal(departmentId) {
    $('#component_department_id').val(departmentId);
    mfp_modal('#componentAddModal');
}
</script>
