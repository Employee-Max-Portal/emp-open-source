<style>
body { background: #f8fafc; }
.stats-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}
.stats-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.stats-card.total .icon { color: #6366f1; }
.stats-card.delivered .icon { color: #10b981; }
.stats-card.transit .icon { color: #f59e0b; }
.stats-card.pending .icon { color: #3b82f6; }
.stats-number { font-size: 2rem; font-weight: 700; color: #1e293b; }
.stats-label { color: #64748b; font-size: 1.5rem; font-weight: 500; }
.shipment-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    margin-bottom: 20px;
    transition: all 0.3s ease;
    overflow: hidden;
}
.shipment-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border-color: #cbd5e1;
}
.status-badge {
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.search-filter-bar {
    background: white;
    border: 1px solid #e2e8f0;
    padding: 24px;
    border-radius: 16px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.form-control:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}
.btn {
    border-radius: 12px;
    font-weight: 500;
    padding: 10px 20px;
}
.btn-primary {
    background: #6366f1;
    border-color: #6366f1;
}
.btn-primary:hover {
    background: #5b5bd6;
    border-color: #5b5bd6;
}

.empty-state {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 60px 40px;
    text-align: center;
}
</style>
 <!--table row height -->
  
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

</style>
  
	<!-- Statistics Cards -->
    <div class="container-fluid" style = "padding-right: 0px; padding-left: 0px;">
	    <?php 
        $total = count($shipments);
        $delivered = count(array_filter($shipments, function($s) { return $s['status'] === 'received'; }));
        $in_transit = count(array_filter($shipments, function($s) { return in_array($s['status'], ['in_transit', 'agent_warehouse', 'bd_customs']); }));
        $pending = count(array_filter($shipments, function($s) { return in_array($s['status'], ['ordered', 'in_production']); }));
        ?>
		
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-6">
                <div class="stats-card total">
                    <div class="stats-icon">
                    <i class="fas fa-boxes fa-3x icon"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-number"><?= htmlspecialchars($total) ?></div>
                        <div class="stats-label">Total Shipment</div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 col-xs-6">
                <div class="stats-card delivered">
                    <div class="stats-icon">
                        <i class="fas fa-check-circle fa-3x icon"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-number"><?= htmlspecialchars($delivered) ?></div>
                        <div class="stats-label">Received</div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 col-xs-6">
                <div class="stats-card transit">
                    <div class="stats-icon">
                        <i class="fas fa-truck fa-3x icon"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-number"><?= htmlspecialchars($in_transit) ?></div>
                        <div class="stats-label">In Transit</div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 col-xs-6">
                <div class="stats-card pending">
                    <div class="stats-icon">
                        <i class="fas fa-clock fa-3x icon"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-number"><?= htmlspecialchars($pending) ?></div>
                        <div class="stats-label">Processing</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php 
/* echo "<pre>";
print_r ($shipments);
echo "</pre>"; */
?>
 <div class="row">
     <div class="col-md-12">
         <div class="panel">
 			<div class="panel-heading">
 				<div class="row">
 					<div class="col-md-10">
 						<h4 class="card-title"><b>Shipment Summary</b></h4>
 					</div>
 					<div class="col-md-2">
 						<?php if (get_permission('shipment_management', 'is_add')): ?>
                    <button type="button" class="btn btn-primary btn-lg shadow-sm" data-toggle="modal" data-target="#createShipmentModal" style="padding: 2px 10px;">
                        <i class="fas fa-plus me-2"></i> Add New                    </button>
                    <?php endif; ?>
 					</div>
 				</div>
 				<div class="panel-body">
 					<div class="table-responsive responsive-table">
 						
						<table class="table table-bordered table-hover table-condensed table_default" cellspacing="0" width="100%" id="shipmentsTable">
 							<thead>
 								<tr>
 								<th class="text-center" style="width: 20%;">Products</th>
 								<th class="text-center" style="width: 10%;">Route</th>
 								<th class="text-center" style="width: 15%;">Suppliers</th>
 								<th class="text-center" style="width: 10%;">Shipping Agent</th>
 								<th class="text-center" style="width: 8%;">Status</th>
 								<th class="text-center" style="width: 8%;">Date</th>
 								<th class="text-center" style="width: 29%;">Actions</th>
 								</tr>
 							</thead>
 							<tbody>
 								<?php if (!empty($shipments)): ?>
                                    <?php foreach ($shipments as $shipment): ?>
                                        <tr class="shipment-item" 
                                            data-tracking="<?= strtolower($shipment['tracking_number']) ?>" 
                                            data-shipment-name="<?= strtolower($shipment['shipment_name'] ?? '') ?>"
                                            data-sender="<?= strtolower($shipment['sender_name'] ?? '') ?>" 
                                            data-receiver="<?= strtolower($shipment['agent_name'] ?? '') ?>" 
                                            data-status="<?= $shipment['status'] ?>" 
                                            data-created="<?= strtotime($shipment['created_at']) ?>"
                                            data-origin="<?= htmlspecialchars($shipment['origin'] ?? '') ?>"
                                            data-destination="<?= htmlspecialchars($shipment['destination'] ?? '') ?>"
                                            data-shipping-mark="<?= htmlspecialchars($shipment['shipping_mark'] ?? 'N/A') ?>"
                                            data-quantity="<?= htmlspecialchars($shipment['quantity_cartons'] ?? 'N/A') ?>"
                                            data-weight="<?= htmlspecialchars($shipment['origin_weight'] ?? 'N/A') ?>"
                                            data-shipping-method="<?= htmlspecialchars($shipment['shipping_method'] ?? 'N/A') ?>"
                                            data-description="<?= htmlspecialchars($shipment['description'] ?? 'N/A') ?>"
                                            data-attachments="<?= htmlspecialchars($shipment['attachments'] ?? 'N/A') ?>"
                                            data-suppliers="<?= htmlspecialchars($shipment['supplier_names'] ?? 'N/A') ?>"
                                            data-agent="<?= htmlspecialchars($shipment['agent_name'] ?? 'N/A') ?>"
                                            data-milestones="<?= htmlspecialchars($shipment['milestone_name'] ?? 'N/A') ?>"
                                            data-delivery-kg="<?= htmlspecialchars($shipment['delivery_kg'] ?? '') ?>"
                                            data-per-kg-amount="<?= htmlspecialchars($shipment['per_kg_amount'] ?? '') ?>"
                                            data-total-cost="<?= htmlspecialchars($shipment['total_cost'] ?? '') ?>"
                                            data-received-by="<?= htmlspecialchars($shipment['received_by_name'] ?? '') ?>"
                                            data-verified-by="<?= htmlspecialchars($shipment['verified_by_name'] ?? '') ?>"
                                            data-storage-location="<?= htmlspecialchars($shipment['storage_location'] ?? '') ?>">
                                            <td class="text-center">
												<?php if (!empty($shipment['shipment_name'])): ?>
													<strong><?= htmlspecialchars($shipment['shipment_name']) ?></strong>
												<?php else: ?>
													<span class="text-muted">-</span>
												<?php endif; ?>
											</td>
                                            <td class="text-center">
												<div style="font-size:14px; display:flex; align-items:center; gap:5px; justify-content:center;">
													<strong><?= htmlspecialchars($shipment['origin']) ?></strong>
													<i class="fas fa-arrow-right" style="font-size:10px;"></i>
													<strong><?= htmlspecialchars($shipment['destination']) ?></strong>
												</div>
											</td>

                                            <td class="text-center">
												<?php if (!empty($shipment['supplier_names']) && $shipment['supplier_names'] !== 'N/A'): ?>
													<?= htmlspecialchars($shipment['supplier_names']) ?>
												<?php else: ?>
													<span class="text-muted">No Suppliers</span>
												<?php endif; ?>
											</td>
											<td class="text-center">
												<?php if (!empty($shipment['agent_name']) && $shipment['agent_name'] !== 'N/A'): ?>
													<?= htmlspecialchars($shipment['agent_name']) ?>
												<?php else: ?>
													<span class="text-muted">No Agent</span>
												<?php endif; ?>
											</td>

                                            <td class="text-center">
												<?php 
												$statusColors = [
													'received'      => '#10b981',
													'in_transit'    => '#f59e0b', 
													'bd_customs'    => '#f59e0b',
													'agent_warehouse' => '#f59e0b',
													'in_production'  => '#3b82f6',
													'ordered'       => '#3b82f6',
													'cancelled'     => '#ef4444'
												];

												$bgColor = $statusColors[$shipment['status']] ?? '#6b7280';

												$statusLabels = [
													'ordered' => 'Ordered',
													'in_production' => 'In Production',
													'agent_warehouse' => 'Agent Warehouse',
													'in_transit' => 'In Transit',
													'bd_customs' => 'BD Customs',
													'received' => 'Received',
													'cancelled' => 'Cancelled'
												];

												$label = $statusLabels[$shipment['status']] ?? ucfirst(str_replace('_', ' ', $shipment['status']));
												?>
												
												<span class="badge" 
													  style="background-color: <?= $bgColor ?>; color: white; padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 600;">
													<?= $label ?>
												</span>
											</td>

                                            <td class="text-center">
                                                <div style="font-size: 14px; color: #64748b;">
                                                    <?= date('M d, Y', strtotime($shipment['created_at'])) ?> 
													<!-- <br>
                                                    <small><?= date('H:i', strtotime($shipment['created_at'])) ?></small> -->
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-1 justify-content-center">
                                                    <button class="btn btn-outline-info btn-sm" onclick="viewShipment('<?= $shipment['tracking_number'] ?>')" title="View Details" style="padding: 5px 10px;">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="<?= base_url('shipment/track/' . $shipment['tracking_number']) ?>" class="btn btn-outline-primary btn-sm" title="Track" style="padding: 5px 10px;">
                                                        <i class="fas fa-route"></i>
                                                    </a>
                                                    <?php if (get_permission('shipment_management', 'is_edit')): ?>
                                                    <button class="btn btn-outline-success btn-sm" onclick="editShipment('<?= $shipment['tracking_number'] ?>')" title="Edit Shipment" style="padding: 5px 10px;">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-warning btn-sm" onclick="updateStatus('<?= $shipment['tracking_number'] ?>', '<?= $shipment['status'] ?>')" title="Update Status" style="padding: 5px 10px;">
                                                        <i class="fas fa-sync"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    <?php if (get_permission('shipment_management', 'is_delete')): ?>
													<?php echo btn_delete('shipment/delete/' . $shipment['tracking_number']); ?>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <i class="fas fa-shipping-fast fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted mb-2">No Shipments Found</h5>
                                            <p class="text-muted">Start by creating your first shipment</p>
                                        </td>
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

<!-- Create Shipment Modal -->
<?php if (get_permission('shipment_management', 'is_add')): ?>
<div class="modal fade" id="createShipmentModal" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="panel">
			<div class="modal-header">
				<h4 class="modal-title"><i class="fas fa-plus-circle"></i> Create New Shipment</h4>
				<button type="button" class="close" data-dismiss="modal">
					<span>&times;</span>
				</button>
			</div>
			<form method="post" action="<?= base_url('shipment/create') ?>" class="form-horizontal" enctype="multipart/form-data">
				<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />
			<div class="modal-body">
				<div class="form-group">
					<label class="col-md-3 control-label">Tracking Number</label>
					<div class="col-md-9">
						<input type="text" class="form-control" name="tracking_number" value="TRK<?= date('YmdHis') ?>" required />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Products <span class="text-danger">*</span></label>
					<div class="col-md-9">
						<input type="text" class="form-control" name="shipment_name" placeholder="Enter shipment name" required />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Milestones <span class="text-danger">*</span></label>
					<div class="col-md-9">
						<?php
							$array = $this->app_lib->getSelectList_v2('tracker_milestones', '', ['status' => 'in_progress']);
							unset($array['']);			
							echo form_dropdown("milestone_ids[]", $array, set_value('milestone_ids'), "class='form-control' id='milestone_ids'
							data-plugin-selectTwo data-width='100%' multiple required");
						?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Suppliers <span class="text-danger">*</span></label>
					<div class="col-md-9">
						<?php
							$this->db->select('s.id, s.name');
							$this->db->from('staff s');
							$this->db->join('login_credential lc', 's.id = lc.user_id');
							$this->db->where('lc.role', 11);
							$this->db->where('lc.active', 1);
							$staff_role_11 = $this->db->get()->result_array();
							$supplier_options = array();
							foreach($staff_role_11 as $staff) {
								$supplier_options[$staff['id']] = $staff['name'];
							}
							echo form_dropdown("supplier_ids[]", $supplier_options, set_value('supplier_ids'), "class='form-control' id='supplier_ids'
							data-plugin-selectTwo data-width='100%' multiple required");
						?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Shipment Agent <span class="text-danger">*</span></label>
					<div class="col-md-9">
						<?php
							$this->db->select('s.id, s.name');
							$this->db->from('staff s');
							$this->db->join('login_credential lc', 's.id = lc.user_id');
							$this->db->where('lc.role', 12);
							$this->db->where('lc.active', 1);
							$staff_role_12 = $this->db->get()->result_array();
							$receiver_options = array('' => 'Select Shipment Agent');
							foreach($staff_role_12 as $staff) {
								$receiver_options[$staff['id']] = $staff['name'];
							}
							echo form_dropdown("shipment_agent_id", $receiver_options, set_value('shipment_agent_id'), "class='form-control' id='shipment_agent_id'
							data-plugin-selectTwo data-width='100%' required");
						?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Origin <span class="text-danger">*</span></label>
					<div class="col-md-9">
						<input type="text" class="form-control" name="origin" required />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Destination <span class="text-danger">*</span></label>
					<div class="col-md-9">
						<input type="text" class="form-control" name="destination" required />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Shipping Mark</label>
					<div class="col-md-9">
						<input type="text" class="form-control" name="shipping_mark" placeholder="Enter shipping mark" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Quantity (Cartons/Boxes)</label>
					<div class="col-md-9">
						<input type="number" class="form-control" name="quantity_cartons" placeholder="Enter quantity" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Origin Weight (kg)</label>
					<div class="col-md-9">
						<input type="number" step="0.01" class="form-control" name="origin_weight" placeholder="Enter origin weight" />
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-3 control-label">Shipping Method</label>
					<div class="col-md-9">
						<?php
							$shipping_methods = array(
								'' => 'Select Method',
								'air' => 'Air',
								'sea' => 'Sea',
								'hongkong' => 'Hong Kong'
							);
							echo form_dropdown("shipping_method", $shipping_methods, set_value('shipping_method'), "class='form-control' data-plugin-selectTwo data-width='100%'");
						?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Attachments</label>
					<div class="col-md-9">
						<input type="file" name="attachments[]" class="form-control" multiple data-allowed-file-extensions="pdf doc docx jpg jpeg png" />
						<small class="text-muted">You can select multiple files (PDF, DOC, DOCX, JPG, JPEG, PNG)</small>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Description</label>
					<div class="col-md-9">
						<textarea class="form-control" rows="3" name="description" placeholder="Enter description"></textarea>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="submit" class="btn btn-primary">
					<i class="fas fa-plus-circle"></i> Create Shipment
				</button>
			</div>
			</form>
		</div>
	</div>
</div>
<?php endif; ?>

<!-- Update Status Modal -->
<?php if (get_permission('shipment_management', 'is_edit')): ?>
<div class="modal fade" id="updateStatusModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="panel-body">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit" style="color: #f59e0b;"></i>
                    Update Shipment Status
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="updateStatusForm">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />
                <div class="modal-body">
                    <input type="hidden" id="update_tracking_number" name="tracking_number">
                    <div class="form-group">
                        <label>Status <span class="text-danger">*</span></label>
                        <select id="update_status" name="status" class="form-control" required>
                            <option value="ordered">Ordered</option>
                            <option value="in_production">In Production</option>
                            <option value="agent_warehouse">Agent Warehouse</option>
                            <option value="in_transit">In Transit</option>
                            <option value="bd_customs">BD Customs</option>
                            <option value="received">Received</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" id="update_location" name="location" class="form-control" placeholder="Current location">
                    </div>
                    
                    <!-- Delivery Details - Show only when status is 'received' -->
                    <div id="delivery_details" style="display: none;">
                        <hr>
                        <h6><strong>Delivery Details</strong></h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Delivery Weight (kg)</label>
                                    <input type="number" step="0.01" id="delivery_kg" name="delivery_kg" class="form-control" placeholder="Enter delivery weight">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Per KG Amount</label>
                                    <input type="number" step="0.01" id="per_kg_amount" name="per_kg_amount" class="form-control" placeholder="Enter per kg amount">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Total Cost Amount</label>
                            <input type="number" step="0.01" id="total_cost" name="total_cost" class="form-control" placeholder="Auto-calculated" readonly>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Received By</label>
                                    <select id="received_by" name="received_by" class="form-control">
                                        <option value="">Select Employee</option>
                                        <?php
                                        $this->db->select('s.id, s.name');
                                        $this->db->from('staff s');
                                        $this->db->join('login_credential lc', 's.id = lc.user_id');
                                        $this->db->where_not_in('lc.role', [9, 10, 11, 12]);
                                        $this->db->where('lc.active', 1);
                                        $employees = $this->db->get()->result_array();
                                        foreach($employees as $emp) {
                                            echo '<option value="'.$emp['id'].'">'.$emp['name'].'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Verified By</label>
                                    <select id="verified_by" name="verified_by" class="form-control">
                                        <option value="">Select Employee</option>
                                        <?php
                                        foreach($employees as $emp) {
                                            echo '<option value="'.$emp['id'].'">'.$emp['name'].'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Storage Location</label>
                            <input type="text" id="storage_location" name="storage_location" class="form-control" placeholder="Where is it stored?">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- View Shipment Modal -->
<div class="modal fade" id="viewShipmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="panel-body">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye" style="color: #3b82f6;"></i>
                    Shipment Details
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><strong>Basic Information</strong></h6>
                        <table class="table table-borderless">
                            <tr><td><strong>Tracking Number:</strong></td><td id="view_tracking_number">-</td></tr>
                            <tr><td><strong>Products:</strong></td><td id="view_shipment_name">-</td></tr>
                            <tr><td><strong>Origin:</strong></td><td id="view_origin">-</td></tr>
                            <tr><td><strong>Destination:</strong></td><td id="view_destination">-</td></tr>
                            <tr><td><strong>Status:</strong></td><td id="view_status">-</td></tr>
                            <tr><td><strong>Created Date:</strong></td><td id="view_created_date">-</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6><strong>Shipment Details</strong></h6>
                        <table class="table table-borderless">
                            <tr><td><strong>Shipping Mark:</strong></td><td id="view_shipping_mark">-</td></tr>
                            <tr><td><strong>Quantity:</strong></td><td id="view_quantity">-</td></tr>
                            <tr><td><strong>Origin Weight:</strong></td><td id="view_weight">-</td></tr>
                            <tr><td><strong>Shipping Method:</strong></td><td id="view_shipping_method">-</td></tr>
                            <tr><td><strong>Suppliers:</strong></td><td id="view_suppliers">-</td></tr>
                            <tr><td><strong>Shipping Agent:</strong></td><td id="view_agent">-</td></tr>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h6><strong>Description</strong></h6>
                        <div id="view_description">-</div>
                    </div>
					<div class="col-md-6">
                        <h6><strong>Milestones</strong></h6>
                        <div id="view_milestones">-</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <h6><strong>Attachments</strong></h6>
                        <div id="view_attachments">-</div>
                    </div>
                </div>
                
                <!-- Delivery Details - Show only for delivered shipments -->
                <div id="view_delivery_details" style="display: none;">
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <h6><strong>Delivery Details</strong></h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr><td><strong>Delivery Weight:</strong></td><td id="view_delivery_kg">-</td></tr>
                                <tr><td><strong>Per KG Amount:</strong></td><td id="view_per_kg_amount">-</td></tr>
                                <tr><td><strong>Total Cost:</strong></td><td id="view_total_cost">-</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr><td><strong>Received By:</strong></td><td id="view_received_by">-</td></tr>
                                <tr><td><strong>Verified By:</strong></td><td id="view_verified_by">-</td></tr>
                                <tr><td><strong>Storage Location:</strong></td><td id="view_storage_location">-</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Shipment Modal -->
<?php if (get_permission('shipment_management', 'is_edit')): ?>
<div class="modal fade" id="editShipmentModal" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="panel">
			<div class="modal-header">
				<h4 class="modal-title"><i class="fas fa-edit"></i> Edit Shipment</h4>
				<button type="button" class="close" data-dismiss="modal">
					<span>&times;</span>
				</button>
			</div>
			<form id="editShipmentForm" class="form-horizontal" enctype="multipart/form-data">
				<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />
				<input type="hidden" id="old_tracking_number" name="old_tracking_number" />
			<div class="modal-body">
				<div class="form-group">
					<label class="col-md-3 control-label">Tracking Number <span class="text-danger">*</span></label>
					<div class="col-md-9">
						<input type="text" class="form-control" id="edit_tracking_number" name="tracking_number" required />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Products <span class="text-danger">*</span></label>
					<div class="col-md-9">
						<input type="text" class="form-control" id="edit_shipment_name" name="shipment_name" required />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Milestones <span class="text-danger">*</span></label>
					<div class="col-md-9">
						<?php
							$array = $this->app_lib->getSelectList_v2('tracker_milestones', '', ['status' => 'in_progress']);
							unset($array['']);			
							echo form_dropdown("milestone_ids[]", $array, set_value('milestone_ids'), "class='form-control' id='edit_milestone_ids'
							data-plugin-selectTwo data-width='100%' multiple required");
						?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Suppliers <span class="text-danger">*</span></label>
					<div class="col-md-9">
						<?php
							$this->db->select('s.id, s.name');
							$this->db->from('staff s');
							$this->db->join('login_credential lc', 's.id = lc.user_id');
							$this->db->where('lc.role', 11);
							$this->db->where('lc.active', 1);
							$staff_role_11 = $this->db->get()->result_array();
							$supplier_options = array();
							foreach($staff_role_11 as $staff) {
								$supplier_options[$staff['id']] = $staff['name'];
							}
							echo form_dropdown("supplier_ids[]", $supplier_options, set_value('supplier_ids'), "class='form-control' id='edit_supplier_ids'
							data-plugin-selectTwo data-width='100%' multiple required");
						?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Shipment Agent <span class="text-danger">*</span></label>
					<div class="col-md-9">
						<?php
							$this->db->select('s.id, s.name');
							$this->db->from('staff s');
							$this->db->join('login_credential lc', 's.id = lc.user_id');
							$this->db->where('lc.role', 12);
							$this->db->where('lc.active', 1);
							$staff_role_12 = $this->db->get()->result_array();
							$receiver_options = array('' => 'Select Shipment Agent');
							foreach($staff_role_12 as $staff) {
								$receiver_options[$staff['id']] = $staff['name'];
							}
							echo form_dropdown("shipment_agent_id", $receiver_options, set_value('shipment_agent_id'), "class='form-control' id='edit_shipment_agent_id'
							data-plugin-selectTwo data-width='100%' required");
						?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Origin <span class="text-danger">*</span></label>
					<div class="col-md-9">
						<input type="text" class="form-control" id="edit_origin" name="origin" required />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Destination <span class="text-danger">*</span></label>
					<div class="col-md-9">
						<input type="text" class="form-control" id="edit_destination" name="destination" required />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Shipping Mark</label>
					<div class="col-md-9">
						<input type="text" class="form-control" id="edit_shipping_mark" name="shipping_mark" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Quantity (Cartons/Boxes)</label>
					<div class="col-md-9">
						<input type="number" class="form-control" id="edit_quantity_cartons" name="quantity_cartons" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Origin Weight (kg)</label>
					<div class="col-md-9">
						<input type="number" step="0.01" class="form-control" id="edit_origin_weight" name="origin_weight" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Shipping Method</label>
					<div class="col-md-9">
						<?php
							$shipping_methods = array(
								'' => 'Select Method',
								'air' => 'Air',
								'sea' => 'Sea',
								'hongkong' => 'Hong Kong'
							);
							echo form_dropdown("shipping_method", $shipping_methods, set_value('shipping_method'), "class='form-control' id='edit_shipping_method' data-plugin-selectTwo data-width='100%'");
						?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Description</label>
					<div class="col-md-9">
						<textarea class="form-control" rows="3" id="edit_description" name="description"></textarea>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="submit" class="btn btn-success">
					<i class="fas fa-save"></i> Update Shipment
				</button>
			</div>
			</form>
		</div>
	</div>
</div>
<?php endif; ?>

<script>
// Create milestone mapping
const milestoneMap = {
    <?php 
    $milestones = $this->db->get('tracker_milestones')->result();
    foreach ($milestones as $milestone) {
        echo $milestone->id . ': "' . addslashes($milestone->title) . '",';
    }
    ?>
};

function updateStatus(trackingNumber, currentStatus) {
    $('#update_tracking_number').val(trackingNumber);
    $('#update_status').val(currentStatus);
    
    // Reset delivery details
    $('#delivery_details input').val('');
    toggleDeliveryDetails(currentStatus);
    
    $('#updateStatusModal').modal('show');
}

function toggleDeliveryDetails(status) {
    if (status === 'received') {
        $('#delivery_details').show();
    } else {
        $('#delivery_details').hide();
    }
}

function calculateTotalCost() {
    const deliveryKg = parseFloat($('#delivery_kg').val()) || 0;
    const perKgAmount = parseFloat($('#per_kg_amount').val()) || 0;
    const totalCost = deliveryKg * perKgAmount;
    $('#total_cost').val(totalCost.toFixed(2));
}

function editShipment(trackingNumber) {
    $.ajax({
        url: '<?= base_url('shipment/get_shipment_details') ?>',
        type: 'POST',
        data: {
            '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>',
            'tracking_number': trackingNumber
        },
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                const shipment = data.shipment;
                
                // Populate form fields
                $('#old_tracking_number').val(shipment.tracking_number);
                $('#edit_tracking_number').val(shipment.tracking_number);
                $('#edit_shipment_name').val(shipment.shipment_name || '');
                $('#edit_origin').val(shipment.origin || '');
                $('#edit_destination').val(shipment.destination || '');
                $('#edit_shipping_mark').val(shipment.shipping_mark || '');
                $('#edit_quantity_cartons').val(shipment.quantity_cartons || '');
                $('#edit_origin_weight').val(shipment.origin_weight || '');
                $('#edit_description').val(shipment.description || '');
                
                // Set select2 values
                $('#edit_shipping_method').val(shipment.shipping_method || '').trigger('change');
                $('#edit_shipment_agent_id').val(shipment.shipment_agent_id || '').trigger('change');
                
                // Set milestone and supplier selections
                if (data.milestone_ids && data.milestone_ids.length > 0) {
                    $('#edit_milestone_ids').val(data.milestone_ids).trigger('change');
                }
                if (data.supplier_ids && data.supplier_ids.length > 0) {
                    $('#edit_supplier_ids').val(data.supplier_ids).trigger('change');
                }
                
                $('#editShipmentModal').modal('show');
            } else {
                alert('Error: ' + (data.message || 'Failed to load shipment details'));
            }
        },
        error: function(xhr, status, error) {
            console.log('AJAX Error:', xhr.responseText);
            alert('Error loading shipment details: ' + error);
        }
    });
}

function viewShipment(trackingNumber) {
    // Find the shipment data from the table row
    const shipmentRow = $(`.shipment-item[data-tracking="${trackingNumber.toLowerCase()}"]`);
    
    if (shipmentRow.length) {
        // Get data from data attributes
        $('#view_tracking_number').text(trackingNumber);
        $('#view_shipment_name').text(shipmentRow.data('shipment-name') || 'N/A');
        $('#view_origin').text(shipmentRow.data('origin') || 'N/A');
        $('#view_destination').text(shipmentRow.data('destination') || 'N/A');
        $('#view_status').html($(shipmentRow.find('td')[4]).html());
        $('#view_created_date').text($(shipmentRow.find('td')[5]).text());
        
        // Set detailed fields from data attributes
        $('#view_shipping_mark').text(shipmentRow.data('shipping-mark') || 'N/A');
        $('#view_quantity').text(shipmentRow.data('quantity') || 'N/A');
        $('#view_weight').text(shipmentRow.data('weight') ? shipmentRow.data('weight') + ' kg' : 'N/A');
        $('#view_shipping_method').text(shipmentRow.data('shipping-method') || 'N/A');
        $('#view_agent').text(shipmentRow.data('agent') || 'N/A');
        $('#view_suppliers').text(shipmentRow.data('suppliers') || 'N/A');
        // Handle milestones from data attribute
        const milestones = shipmentRow.data('milestones');
        if (milestones && milestones !== 'N/A' && milestones !== '') {
            const milestoneNames = milestones.toString().split('|||');
            let milestoneHtml = '<div style="font-size: 12px;">';
            
            milestoneNames.forEach(name => {
                const trimmedName = name.trim();
                if (trimmedName) {
                    milestoneHtml += `<span class="badge badge-warning" style="margin: 2px; background-color: #eee; color: #000; padding: 4px 8px; border-radius: 12px;">${trimmedName}</span>`;
                }
            });
            
            milestoneHtml += '</div>';
            $('#view_milestones').html(milestoneHtml);
        } else {
            $('#view_milestones').html('<span class="text-muted">No milestones</span>');
        }
        $('#view_description').text(shipmentRow.data('description') || 'N/A');
        
        // Handle attachments
        const attachments = shipmentRow.data('attachments');
        if (attachments && attachments !== 'N/A' && attachments !== 'null' && attachments !== '') {
            try {
                let files = [];
                
                // Handle different attachment data formats
                if (typeof attachments === 'string') {
                    // Try to parse as JSON first
                    if (attachments.startsWith('[') && attachments.endsWith(']')) {
                        files = JSON.parse(attachments);
                    } else {
                        // Handle comma-separated string
                        files = attachments.split(',').map(f => f.trim()).filter(f => f);
                    }
                } else if (Array.isArray(attachments)) {
                    files = attachments;
                }
                
                if (files && files.length > 0) {
                    let attachmentHtml = '<div class="d-flex flex-wrap" style="gap: 8px;">';
                    files.forEach(file => {
                        // Clean filename (remove quotes and brackets if present)
                        const cleanFile = file.replace(/["\[\]]/g, '').trim();
                        if (cleanFile) {
                            const fileExt = cleanFile.split('.').pop().toLowerCase();
                            let icon = 'fas fa-file';
                            let btnClass = 'btn-outline-secondary';
                            
                            if (['pdf'].includes(fileExt)) {
                                icon = 'fas fa-file-pdf';
                                btnClass = 'btn-outline-danger';
                            } else if (['doc', 'docx'].includes(fileExt)) {
                                icon = 'fas fa-file-word';
                                btnClass = 'btn-outline-primary';
                            } else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExt)) {
                                icon = 'fas fa-file-image';
                                btnClass = 'btn-outline-success';
                            } else if (['xls', 'xlsx'].includes(fileExt)) {
                                icon = 'fas fa-file-excel';
                                btnClass = 'btn-outline-success';
                            }
                            
                            const fileName = cleanFile.length > 20 ? cleanFile.substring(0, 17) + '...' : cleanFile;
                            
                            attachmentHtml += `
                                <a href="<?= base_url('uploads/shipments/') ?>${cleanFile}" target="_blank" 
                                   class="btn btn-sm ${btnClass}" title="Download ${cleanFile}" 
                                   style="margin-bottom: 5px;">
                                    <i class="${icon}"></i> ${fileName}
                                </a>`;
                        }
                    });
                    attachmentHtml += '</div>';
                    $('#view_attachments').html(attachmentHtml);
                } else {
                    $('#view_attachments').html('<span class="text-muted"><i class="fas fa-info-circle"></i> No attachments</span>');
                }
            } catch (e) {
                console.error('Attachment parsing error:', e);
                $('#view_attachments').html('<span class="text-warning"><i class="fas fa-exclamation-triangle"></i> Unable to load attachments</span>');
            }
        } else {
            $('#view_attachments').html('<span class="text-muted"><i class="fas fa-info-circle"></i> No attachments</span>');
        }
        
        // Handle delivery details for received shipments
        const status = shipmentRow.data('status');
        if (status === 'received') {
            const deliveryKg = shipmentRow.data('delivery-kg');
            const perKgAmount = shipmentRow.data('per-kg-amount');
            const totalCost = shipmentRow.data('total-cost');
            const receivedBy = shipmentRow.data('received-by');
            const verifiedBy = shipmentRow.data('verified-by');
            const storageLocation = shipmentRow.data('storage-location');
            
            $('#view_delivery_kg').text(deliveryKg ? deliveryKg + ' kg' : 'N/A');
            $('#view_per_kg_amount').text(perKgAmount ? perKgAmount  + ' BDT' : 'N/A');
            $('#view_total_cost').text(totalCost ? totalCost + ' BDT' : 'N/A');
            $('#view_received_by').text(receivedBy || 'N/A');
            $('#view_verified_by').text(verifiedBy || 'N/A');
            $('#view_storage_location').text(storageLocation || 'N/A');
            
            $('#view_delivery_details').show();
        } else {
            $('#view_delivery_details').hide();
        }
        
        $('#viewShipmentModal').modal('show');
    }
}

function clearFilters() {
    $('#searchInput').val('');
    $('#statusFilter').val('');
    $('#sortBy').val('created_desc');
    filterShipments();
}

function filterShipments() {
    const searchTerm = $('#searchInput').val().toLowerCase();
    const statusFilter = $('#statusFilter').val();
    const sortBy = $('#sortBy').val();
    
    let shipments = $('.shipment-item').get();
    
    shipments.forEach(function(item) {
        const $item = $(item);
        const tracking = $item.data('tracking');
        const sender = $item.data('sender');
        const receiver = $item.data('receiver');
        const status = $item.data('status');
        
        const matchesSearch = !searchTerm || tracking.includes(searchTerm) || sender.includes(searchTerm) || receiver.includes(searchTerm);
        const matchesStatus = !statusFilter || status === statusFilter;
        
        if (matchesSearch && matchesStatus) {
            $item.show();
        } else {
            $item.hide();
        }
    });
    
    const visibleShipments = $('.shipment-item:visible').get();
    visibleShipments.sort(function(a, b) {
        const $a = $(a), $b = $(b);
        switch(sortBy) {
            case 'created_asc':
                return $a.data('created') - $b.data('created');
            case 'created_desc':
                return $b.data('created') - $a.data('created');
            case 'tracking':
                return $a.data('tracking').localeCompare($b.data('tracking'));
            case 'status':
                return $a.data('status').localeCompare($b.data('status'));
            default:
                return $b.data('created') - $a.data('created');
        }
    });
    
    $('#shipmentsTable tbody').append(visibleShipments);
}

$(document).ready(function() {
    $('#searchInput, #statusFilter, #sortBy').on('change keyup', function() {
        filterShipments();
    });
    
    // Handle status change to show/hide delivery details
    $('#update_status').on('change', function() {
        toggleDeliveryDetails($(this).val());
    });
    
    // Auto-calculate total cost when delivery weight or per kg amount changes
    $('#delivery_kg, #per_kg_amount').on('input', function() {
        calculateTotalCost();
    });
    
    $('#updateStatusForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?= base_url('shipment/update_status') ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#updateStatusModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error: ' + (response.message || 'Failed to update status'));
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error:', xhr.responseText);
                alert('Error updating status: ' + error);
            }
        });
    });
    
    $('#editShipmentForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?= base_url('shipment/update') ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#editShipmentModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error: ' + (response.message || 'Failed to update shipment'));
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error:', xhr.responseText);
                alert('Error updating shipment: ' + error);
            }
        });
    });

});
</script>