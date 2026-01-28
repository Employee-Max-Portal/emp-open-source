<style>
body { background: #f8fafc; }
.track-header {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    text-align: center;
}
.track-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}
.track-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.status-progress {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 24px 0;
    position: relative;
}
.status-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    position: relative;
}
.status-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
    font-size: 20px;
    color: white;
}
.status-icon.completed { background: #10b981; }
.status-icon.current { background: #3b82f6; }
.status-icon.pending { background: #e5e7eb; color: #9ca3af; }
.status-line {
    position: absolute;
    top: 24px;
    left: 50%;
    right: -50%;
    height: 2px;
    background: #e5e7eb;
    z-index: -1;
}
.status-line.completed { background: #10b981; }
.tracking-timeline {
    position: relative;
    padding: 10px 0;
    margin-left: 10px;
}
.timeline-item {
    position: relative;
    padding-left: 35px;
    padding-bottom: 40px;
}
.timeline-item:last-child {
    padding-bottom: 0;
}
.timeline-item::before {
    content: '';
    position: absolute;
    left: 7px;
    top: 18px;
    bottom: -18px; /* Connect to next dot */
    width: 2px;
    background: #e5e7eb;
}
.timeline-item:last-child::before {
    display: none;
}
.timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #9ca3af;
    z-index: 1;
}
.timeline-item.current .timeline-marker {
    background: #000;
    width: 20px;
    height: 20px;
    left: -2px;
}
.timeline-content {
    position: relative;
    top: -4px;
}
.timeline-title {
    font-weight: 700;
    color: #000;
    font-size: 16px;
    margin-bottom: 6px;
    line-height: 1.2;
}
.timeline-desc {
    color: #374151;
    font-size: 15px;
    margin-bottom: 6px;
    line-height: 1.4;
}
.timeline-date {
    color: #6b7280;
    font-size: 13px;
}
.form-control {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 16px;
    font-size: 16px;
}
.form-control:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}
.btn {
    border-radius: 12px;
    font-weight: 500;
    padding: 12px 24px;
}
.btn-primary {
    background: #6366f1;
    border-color: #6366f1;
}
.btn-primary:hover {
    background: #5b5bd6;
    border-color: #5b5bd6;
}
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.info-item {
    padding: 16px;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}
.info-label {
    font-size: 12px;
    color: #64748b;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 4px;
}
.info-value {
    font-size: 16px;
    color: #1e293b;
    font-weight: 600;
}
</style>

<div class="container-fluid">
    <!-- Header -->
    <div class="track-header">
        <h1 class="mb-3" style="color: #1e293b; font-weight: 700;">
            <i class="fas fa-route" style="color: #6366f1;"></i> Track Your Shipment
        </h1>
        <p class="text-muted mb-4" style="font-size: 16px;">Enter your tracking number to get real-time updates</p>
        
        <form method="get" action="<?= base_url('shipment/track') ?>">
            <div class="row justify-content-center">
                <div class="col-md-9">
					<input type="text" name="tracking" class="form-control flex-grow-1" placeholder="Enter tracking number (e.g., TRK20241118...)" value="<?= isset($_GET['tracking']) ? htmlspecialchars($_GET['tracking']) : '' ?>" style="font-size: 16px;">
                </div>
                <div class="col-md-3">
					<button type="submit" class="btn btn-primary btn-lg px-4" style="    padding: 4px 10px;">
                            <i class="fas fa-search"></i> Track Package
                        </button>
                </div>
            </div>
        </form>
    </div>

    <?php if (isset($shipment) && $shipment): ?>
        <!-- Status Progress -->
        <div class="track-card">
            <h4 class="mb-4" style="color: #1e293b; font-weight: 600;">
                <i class="fas fa-box" style="color: #6366f1;"></i> 
                <?= !empty($shipment['shipment_name']) ? htmlspecialchars($shipment['shipment_name']) : htmlspecialchars($shipment['tracking_number']) ?>
            </h4>
            <?php if (!empty($shipment['shipment_name'])): ?>
            <p class="text-muted" style="font-size: 14px; margin-top: -10px;">Tracking: <?= htmlspecialchars($shipment['tracking_number']) ?></p>
            <?php endif; ?>
            
            <div class="status-progress">
                <?php 
                $statuses = ['ordered', 'in_production', 'agent_warehouse', 'in_transit', 'bd_customs', 'received'];
                $statusLabels = ['Ordered', 'In Production', 'Agent Warehouse', 'In Transit', 'BD Customs', 'Received'];
                $statusIcons = ['fas fa-clipboard-check', 'fas fa-cogs', 'fas fa-warehouse', 'fas fa-shipping-fast', 'fas fa-building', 'fas fa-check-circle'];
                $currentIndex = array_search($shipment['status'], $statuses);
                ?>
                
                <?php foreach ($statuses as $index => $status): ?>
                    <div class="status-step">
                        <div class="status-icon <?= $index <= $currentIndex ? 'completed' : ($index == $currentIndex + 1 ? 'current' : 'pending') ?>">
                            <i class="<?= $statusIcons[$index] ?>"></i>
                        </div>
                        <span style="font-size: 12px; color: #64748b; font-weight: 500;"><?= $statusLabels[$index] ?></span>
                        <?php if ($index < count($statuses) - 1): ?>
                            <div class="status-line <?= $index < $currentIndex ? 'completed' : '' ?>"></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Shipment Details -->
        <div class="track-card">
            <h5 class="mb-4" style="color: #1e293b; font-weight: 600;">
                <i class="fas fa-info-circle" style="color: #6366f1;"></i> Shipment Information
            </h5>
            
            <div class="info-grid">
                <?php if (!empty($shipment['shipment_name'])): ?>
                <div class="info-item">
                    <div class="info-label">Products</div>
                    <div class="info-value"><?= htmlspecialchars($shipment['shipment_name']) ?></div>
                </div>
                <?php endif; ?>
                <div class="info-item">
                    <div class="info-label">From</div>
                    <div class="info-value"><?= htmlspecialchars($shipment['origin'] ?? 'N/A') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">To</div>
                    <div class="info-value"><?= htmlspecialchars($shipment['destination'] ?? 'N/A') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Shipping Agent</div>
                    <div class="info-value"><?= htmlspecialchars($shipment['agent_name'] ?? 'N/A') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Milestones</div>
                    <div class="info-value" style="display: flex; flex-wrap: wrap; gap: 4px; word-break: break-word;">
                        <?php if (!empty($shipment['milestone_names'])): ?>
                            <?php 
                            // Use a unique separator to handle milestone titles that contain commas
                            $milestones = explode('|||', $shipment['milestone_names']);
                            foreach ($milestones as $milestone): ?>
                                <span class="badge" style="background-color: #eee; color: #000; padding: 4px 8px; border-radius: 12px; font-size: 11px; white-space: nowrap; max-width: 100%; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars(trim($milestone)) ?></span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="text-muted">No milestones</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Current Status</div>
                    <div class="info-value" style="color: <?= $shipment['status'] === 'received' ? '#10b981' : ($shipment['status'] === 'in_transit' ? '#f59e0b' : '#6366f1') ?>;">
                        <?= ucfirst(str_replace('_', ' ', $shipment['status'])) ?>
                    </div>
                </div>
                <?php if (!empty($shipment['shipping_method'])): ?>
                <div class="info-item">
                    <div class="info-label">Shipping Method</div>
                    <div class="info-value"><?= htmlspecialchars(ucfirst($shipment['shipping_method'])) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($shipment['origin_weight'])): ?>
                <div class="info-item">
                    <div class="info-label">Origin Weight</div>
                    <div class="info-value"><?= htmlspecialchars($shipment['origin_weight']) ?> kg</div>
                </div>
                <?php endif; ?>
                <div class="info-item">
                    <div class="info-label">Created Date</div>
                    <div class="info-value"><?= date('M d, Y H:i', strtotime($shipment['created_at'])) ?></div>
                </div>
            </div>
        </div>
        
        <!-- Tracking History -->
        <?php if (!empty($tracking_history)): ?>
        <div class="track-card">
            <h5 class="mb-4" style="color: #1e293b; font-weight: 600;">
                <i class="fas fa-history" style="color: #6366f1;"></i> Tracking History
            </h5>
            
            <div class="tracking-timeline">
                <?php foreach ($tracking_history as $index => $history): ?>
                    <div class="timeline-item <?= $index === 0 ? 'current' : '' ?>">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">
                                <?php 
                                $statusLabels = [
                                    'ordered' => 'Ordered',
                                    'in_production' => 'In Production', 
                                    'agent_warehouse' => 'Agent Warehouse',
                                    'in_transit' => 'In Transit',
                                    'bd_customs' => 'BD Customs',
                                    'received' => 'Received',
                                    'cancelled' => 'Cancelled'
                                ];
                                echo $statusLabels[$history['status']] ?? ucfirst(str_replace('_', ' ', $history['status']));
                                ?>
                            </div>
                            <div class="timeline-desc"><?= htmlspecialchars($history['location']) ?></div>
                            <div class="timeline-date"><?= date('M d, H:i', strtotime($history['created_at'])) ?> GMT+06:00</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php elseif (isset($_GET['tracking'])): ?>
        <div class="track-card text-center">
            <i class="fas fa-search fa-4x text-muted mb-3"></i>
            <h4 class="text-muted mb-2">Package Not Found</h4>
            <p class="text-muted mb-4">No shipment found with tracking number: <strong><?= htmlspecialchars($_GET['tracking']) ?></strong></p>
            <p style="color: #64748b;">Please check your tracking number and try again.</p>
        </div>
    <?php endif; ?>
</div>

