<link rel="stylesheet" href="<?= base_url('assets/plugins/dropify/css/dropify.min.css') ?>" />

<section class="panel">
    <div class="tabs-custom">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#list" data-toggle="tab"><i class="fas fa-list-ul"></i> Notification List</a></li>
            <li><a href="#create" data-toggle="tab"><i class="far fa-paper-plane"></i> Send New Notification</a></li>
        </ul>

        <div class="tab-content">

            <!-- LIST -->
            <div id="list" class="tab-pane active">
                <?php if (empty($notification_list)): ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-bell-slash fa-2x mb-2"></i>
                        <h4>No Notifications Available</h4>
                        <p>No notifications have been sent yet.</p>
                    </div>
                <?php else: ?>
                    <table class="table table-bordered table-hover table-condensed">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Text</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($notification_list as $row): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= html_escape($row->title) ?></td>
                                <td><?= html_escape($row->text) ?></td>
                                <td>
                                    <?php if ($row->image): ?>
                                        <img src="<?= html_escape($row->image) ?>" height="40" />
                                    <?php endif; ?>
                                </td>
                                <td><?= html_escape($row->name) ?></td>
                                <td><?= date("Y-m-d H:i", strtotime($row->datetime)) ?></td>
                                <td>
                                    <button class="btn btn-info btn-sm" onclick="viewNotification('<?= html_escape($row->title) ?>', '<?= html_escape($row->text) ?>', '<?= html_escape($row->image) ?>')">
                                        <i class="fas fa-eye"></i> View
                                    </button>
									
									<?php echo btn_delete('notification/delete/' . $row->id); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- CREATE -->
            <div id="create" class="tab-pane">
                <div class="row">
                    <div class="col-md-6">
                        <?= form_open_multipart('', ['class' => 'form-horizontal form-bordered']) ?>
                        <input type="hidden" name="id" value="" />

                        <div class="form-group">
                            <label class="col-md-4 control-label">Notification Title <span class="required">*</span></label>
                            <div class="col-md-8">
                                <input type="text" name="title" id="title" class="form-control" oninput="updatePreview()" placeholder="Enter notification title" required />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-4 control-label">Notification Text <span class="required">*</span></label>
                            <div class="col-md-8">
                                <textarea name="text" id="text" class="form-control" oninput="updatePreview()" placeholder="Enter notification text" rows="3" required></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-4 control-label">Upload Image</label>
                            <div class="col-md-8">
                                <input type="file" name="notification_image" id="notification_image" class="dropify" accept="image/*" onchange="handleImageUpload()" />
                                <small class="text-muted">Upload an image for the notification</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-4 control-label">Notification Name</label>
                            <div class="col-md-8">
                                <input type="text" name="name" class="form-control" placeholder="Enter sender name (optional)" />
                            </div>
                        </div>

                        <footer class="panel-footer">
                            <div class="row">
                                <div class="col-md-offset-4 col-md-8">
                                    <button type="submit" class="btn btn-primary btn-lg" name="save_notification" value="1">
                                        <i class="fas fa-paper-plane"></i> Send Notification
                                    </button>
                                </div>
                            </div>
                        </footer>
                        <?= form_close(); ?>
                    </div>

                    <!-- NOTIFICATION PREVIEW -->
                    <div class="col-md-6">
                        <div class="preview-container">
                            <h5 class="text-center mb-3">Live Preview</h5>
                            <div class="iphone-notification">
                                <div class="notification-header">
                                   <div class="time"><?php echo date('g:i A'); ?></div>
                                    <div class="status-icons">
                                        <span class="signal">●●●</span>
                                        <span class="wifi">📶</span>
                                        <span class="battery">🔋</span>
                                    </div>
                                </div>
                                <div class="notification-banner">
                                    <div class="notification-content">
                                        <img id="defaultLogo" src="https://smart-vm.com.bd/uploads/app_image/tolpar_fab.png" alt="app icon" />
                                        <div class="notification-text">
                                            <div id="previewTitle" class="notification-title">Notification Title</div>
                                            <div id="previewText" class="notification-body">Notification Text</div>
                                        </div>
                                    </div>
                                    <div class="notification-image-container">
                                        <img id="previewImage" style="display: none;" alt="notification image" />
                                    </div>
                                </div>
                            </div>
                            <p class="text-center text-muted mt-2"><small>Notification Preview</small></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- View Notification Modal -->
<div class="modal fade" id="viewNotificationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Notification Preview</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">
                        <div class="iphone-notification">
                            <div class="notification-header">
                                <div class="time"><?php echo date('g:i A'); ?></div>
                                <div class="status-icons">
                                    <span class="signal">●●●</span>
                                    <span class="wifi">📶</span>
                                    <span class="battery">🔋</span>
                                </div>
                            </div>
                            <div class="notification-banner">
                                <div class="notification-content">
                                    <img src="https://smart-vm.com.bd/uploads/app_image/tolpar_fab.png" alt="app icon" />
                                    <div class="notification-text">
                                        <div id="modalTitle" class="notification-title"></div>
                                        <div id="modalText" class="notification-body"></div>
                                    </div>
                                </div>
                                <div class="notification-image-container">
                                    <img id="modalImage" style="display: none;" alt="notification image" />
                                </div>
                            </div>
                        </div>
                        <p class="text-center text-muted mt-2"><small>How users saw this notification</small></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.preview-container {
    position: sticky;
    top: 20px;
}

.iphone-notification {
    width: 375px;
    background: #000;
    border-radius: 25px;
    padding: 0;
    margin: 0 auto;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    overflow: hidden;
}

.notification-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 20px 8px;
    color: white;
    font-size: 14px;
    font-weight: 600;
}

.status-icons {
    display: flex;
    gap: 5px;
    font-size: 12px;
}

.notification-banner {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    margin: 0 12px 20px;
    border-radius: 16px;
    padding: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.notification-content {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.notification-content img {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    flex-shrink: 0;
}

.notification-text {
    flex: 1;
    min-width: 0;
}

.notification-title {
    font-weight: 600;
    font-size: 15px;
    color: #000;
    margin-bottom: 2px;
    line-height: 1.2;
}

.notification-body {
    font-size: 14px;
    color: #666;
    line-height: 1.3;
}

.notification-image-container {
    text-align: center;
    margin-top: 12px;
}

.notification-image-container img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    max-height: 150px;
}
</style>

<script src="<?= base_url('assets/plugins/dropify/js/dropify.min.js') ?>"></script>
<script>
$(document).ready(function() {
    $('.dropify').dropify();
    
    // Handle dropify clear event
    $('#notification_image').on('dropify.afterClear', function(event, element) {
        document.getElementById('previewImage').style.display = 'none';
    });
});

function updatePreview() {
    const title = document.getElementById('title').value || 'Notification Title';
    const text = document.getElementById('text').value || 'Notification Text';

    document.getElementById('previewTitle').textContent = title;
    document.getElementById('previewText').textContent = text;
}

function handleImageUpload() {
    const fileInput = document.getElementById('notification_image');
    const file = fileInput.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const imgElement = document.getElementById('previewImage');
            imgElement.src = e.target.result;
            imgElement.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('previewImage').style.display = 'none';
    }
}

function viewNotification(title, text, image) {
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalText').textContent = text;
    
    const modalImage = document.getElementById('modalImage');
    if (image && image.trim() !== '') {
        modalImage.src = image;
        modalImage.style.display = 'block';
    } else {
        modalImage.style.display = 'none';
    }
    
    $('#viewNotificationModal').modal('show');
}
</script>
