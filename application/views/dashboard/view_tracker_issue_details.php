<?php
// Fetch complete task details from database
$this->db->select('
    t.*,
    assigned_staff.name as assigned_to_name,
    assigned_staff.photo as assigned_to_photo,
    created_staff.name as created_by_name,
    created_staff.photo as created_by_photo,
    coordinator_staff.name as coordinator_name,
    coordinator_staff.photo as coordinator_photo,
    approved_staff.name as approved_by_name,
    d.title as department_name,
    m.title as milestone_name,
    comp.title as component_name,
    tt.name as task_type_name,
    parent_task.id as parent_issue_id,
    parent_task.unique_id as parent_issue_unique_id,
    parent_task.task_title as parent_task_title
');
$this->db->from('tracker_issues t');
$this->db->join('tracker_issues parent_task', 't.parent_issue = parent_task.id', 'left');
$this->db->join('staff assigned_staff', 't.assigned_to = assigned_staff.id', 'left');
$this->db->join('staff created_staff', 't.created_by = created_staff.id', 'left');
$this->db->join('staff coordinator_staff', 't.coordinator = coordinator_staff.id', 'left');
$this->db->join('staff approved_staff', 't.approved_by = approved_staff.id', 'left');
$this->db->join('tracker_departments d', 't.department = d.id', 'left');
$this->db->join('tracker_milestones m', 't.milestone = m.id', 'left');
$this->db->join('tracker_components comp', 't.component = comp.id', 'left');
$this->db->join('task_types tt', 't.task_type = tt.id', 'left');

// Make sure we get task by id OR unique_id
$this->db->group_start();
$this->db->where('t.id', $task_id);
$this->db->or_where('t.unique_id', $task_id);
$this->db->group_end();

$task = $this->db->get()->row();

if (!$task) {
    http_response_code(404);
    echo '<div class="alert alert-danger">Task not found</div>';
    return;
}

// Fetch labels as array if they exist
$labels = [];
if (!empty($task->label)) {
    $label_ids = array_map('trim', explode(',', $task->label));
    $label_results = $this->db->where_in('id', $label_ids)->get('task_labels')->result();
    $labels = array_map(function($l) { return $l->name; }, $label_results);
}

// Fetch comments for this task using the numeric ID
$this->db->select('c.*, s.name as author_name, s.photo as author_photo');
$this->db->from('tracker_comments c');
$this->db->join('staff s', 'c.author_id = s.id', 'left');
$this->db->where('c.task_id', $task->id); // Use numeric ID, not unique_id
$this->db->order_by('c.created_at', 'ASC');
$comments = $this->db->get()->result();



// Fetch active staff for mentions
$this->db->select('s.id, s.name, s.photo');
$this->db->from('staff s');
$this->db->join('login_credential lc', 'lc.user_id = s.id');
$this->db->where('lc.active', 1);
$this->db->where_not_in('lc.role', [1, 9]); // exclude super admin
$this->db->order_by('s.name', 'ASC');
$staff_list = $this->db->get()->result();
?>
    <!-- Sub-tasks Section -->
    <?php 
    // Fetch sub-tasks for this task
    $this->db->select('t.*, s.name as assigned_to_name, s.photo as assigned_to_photo');
    $this->db->from('tracker_issues t');
    $this->db->join('staff s', 't.assigned_to = s.id', 'left');
    $this->db->where('t.parent_issue', $task->id);
    $this->db->order_by('t.id', 'DESC');
    $sub_tasks = $this->db->get()->result();
    
    // Group sub-tasks by status
    $grouped_sub_tasks = [];
    foreach ($sub_tasks as $sub_task) {
        $status_key = trim($sub_task->task_status);
        $grouped_sub_tasks[$status_key][] = $sub_task;
    }
    
    // Status configuration
    $status_config = [
        'todo' => ['title' => 'To Do', 'color' => '#ffbe0b', 'icon' => 'fas fa-clipboard-list'],
        'in_progress' => ['title' => 'In Progress', 'color' => '#3a86ff', 'icon' => 'fas fa-play-circle'],
        'in_review' => ['title' => 'In Review', 'color' => '#17a2b8', 'icon' => 'fas fa-eye'],
        'completed' => ['title' => 'Completed', 'color' => '#06d6a0', 'icon' => 'fas fa-check-circle'],
        'hold' => ['title' => 'On Hold', 'color' => '#fd7e14', 'icon' => 'fas fa-pause-circle'],
        'canceled' => ['title' => 'Canceled', 'color' => '#dc3545', 'icon' => 'fas fa-times-circle']
    ];
    ?>
<style>
.task-detail-modal {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    height: 90vh;
    display: flex;
    flex-direction: column;
}

.task-detail-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    background: #f8f9fa;
}

.task-detail-body {
    display: flex;
    flex: 1;
    overflow: hidden;
}

.task-detail-main {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
    padding-bottom: 40px;
}

.task-detail-main::-webkit-scrollbar {
    width: 0px;
    background: transparent;
}

.task-detail-sidebar {
    width: 310px;
    background: #f8f9fa;
    padding: 20px;
    border-left: 1px solid #eee;
    overflow-y: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.task-detail-sidebar::-webkit-scrollbar {
    width: 0px;
    background: transparent;
}

.task-id-badge {
    background: #e3f2fd;
    color: #1976d2;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
    margin-bottom: 10px;
    display: inline-block;
}

.task-title {
    font-size: 18px;
    font-weight: 600;
    margin: 0 0 15px 0;
    color: #333;
}

.task-description {
    margin-bottom: 20px;
    line-height: 1.6;
    color: #666;
}

.sidebar-item {
    display: flex;
    margin-bottom: 10px;
    padding: 0px 10px; /* 👈 added padding (top-bottom: 6px, left-right: 10px) */
    border-radius: 6px; /* optional – gives a smooth edge */
}

.sidebar-item label {
    font-size: 12px;
    font-weight: 600;
    color: #666;
    margin: 0;
}



.user-info {
    display: flex;
    align-items: center;
    gap: 8px;
}

.user-avatar {
    width: 20px;
    height: 20px;
    border-radius: 50%;
}

.labels-container {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

.label-tag {
    background: #f0f0f0;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 500;
}

.parent-task-info {
    margin-bottom: 10px;
    padding: 8px 12px;
    background: #e3f2fd;
    border-radius: 4px;
    font-size: 0.9em;
    color: #1976d2;
}

@media (max-width: 768px) {
    .task-detail-body {
        flex-direction: column;
    }
    
    .task-detail-sidebar {
        width: 100%;
        border-left: none;
        border-top: 1px solid #eee;
    }
}

.priority-low {
    color: #28a745 !important;
}
.priority-medium {
    color: #ffc107 !important;
}
.priority-high {
    color: #dc3545 !important;
}
.priority-urgent {
    color: #dc3545 !important;
    font-weight: bold !important;
}

.sub-task-item:hover {
    background: #f0f0f0 !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.comment-item {
    display: flex;
    margin-bottom: 15px;
    padding: 10px;
    border-radius: 4px;
    background: #f9f9f9;
}

.comment-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 10px;
    object-fit: cover;
}

.comment-content {
    flex: 1;
    padding-left: 5px;
}

.comment-author {
    font-weight: bold;
    margin-bottom: 5px;
}

.comment-text {
    margin-bottom: 5px;
    white-space: pre-wrap;
}

.comment-meta {
    font-size: 12px;
    color: #666;
}

.comment-actions {
    display: flex;
    gap: 5px;
    margin-left: auto;
}

.edit-comment-textarea {
    margin-top: 10px;
}

.edit-actions {
    margin-top: 10px;
}

#submitCommentBtn:hover {
    background: #45a049;
}

.mention-item {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
}

.mention-item:hover,
.mention-item.selected {
    background: #f0f8ff;
}

.mention-item:last-child {
    border-bottom: none;
}

.mention-dropdown {
    position: absolute;
    bottom: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
}

.mention-name {
    font-size: 14px;
    color: #333;
}

.mentioned-user {
    background: #e3f2fd;
    color: #1976d2;
    padding: 2px 4px;
    border-radius: 3px;
    font-weight: 500;
}

.mention-container {
    position: relative;
}
</style>

<div class="task-detail-modal">
    <div class="task-detail-header">
        <span class="task-id-badge"><?= html_escape($task->unique_id) ?></span>
        
        <?php if ($task->parent_issue_unique_id && $task->parent_task_title): ?>
            <div class="parent-task-info">
                <i class="fas fa-arrow-up" style="margin-right: 5px;"></i>
                <strong>Parent Task:</strong> <?= html_escape($task->parent_issue_unique_id) ?> - <?= html_escape($task->parent_task_title) ?>
            </div>
        <?php endif; ?>
        
        <h2 class="task-title"><?= html_escape($task->task_title) ?></h2>
    </div>

    <div class="task-detail-body">
        <div class="task-detail-main">
            <div class="task-description">
                <?php if (!empty($task->task_description)): ?>
                    <?= $task->task_description ?>
                <?php else: ?>
                    <em>empty description...</em>
                <?php endif; ?>
            </div>
			 <?php if (!empty($sub_tasks)): ?>
        <div style="margin: 20px 0; padding: 20px; border-top: 1px solid #eee; background: #fff;">
            <h4 style="font-size: 16px; margin-bottom: 20px; color: #555; display: flex; align-items: center;">
                <i class="fas fa-tasks" style="margin-right: 10px; color: #4a89dc;"></i>
                Sub Tasks
                <span style="background: #4a89dc; color: white; padding: 2px 8px; border-radius: 10px; font-size: 12px; margin-left: 10px;"><?= count($sub_tasks) ?></span>
            </h4>
            
            <?php foreach ($status_config as $status => $config): ?>
                <?php if (!empty($grouped_sub_tasks[$status])): ?>
                    <div class="accordion-card" style="border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 8px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.07);">
                        <div class="accordion-header" onclick="toggleSubTaskAccordion('subtask_<?= $status ?>')" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 15px; cursor: pointer; font-weight: 600; font-size: 14px; color: #000; background: #f7f7f8; user-select: none; transition: all 0.3s ease;">
                            <div class="status-info" style="display: flex; align-items: center; gap: 12px;">
                                <div class="status-icon" style="width: 24px; height: 24px; border-radius: 50%; background: <?= $config['color'] ?>; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px;">
                                    <i class="<?= $config['icon'] ?>"></i>
                                </div>
                                <span><?= $config['title'] ?></span>
                            </div>
                            <div class="item-count" style="background: #e8e8e9; color: black; padding: 4px 8px; border-radius: 12px; font-size: 12px;"><?= count($grouped_sub_tasks[$status]) ?> items</div>
                        </div>
                        <div class="accordion-body" id="accordion-body-subtask_<?= $status ?>" style="max-height: 0; overflow: hidden; transition: max-height 0.4s ease-out, padding 0.4s ease-out; background-color: #ffffff;">
                            <?php foreach ($grouped_sub_tasks[$status] as $sub_task): ?>
                                <div class="task-item" onclick="viewTask('<?= $sub_task->unique_id ?>')" style="cursor: pointer; display: flex; align-items: center; padding: 5px 10px; border-bottom: 1px solid #f0f0f1; background: #f7f7f8; transition: all 0.2s ease; border-radius: 8px; gap: 10px; margin-bottom: 2px;" onmouseover="this.style.background='#e8e8e9'" onmouseout="this.style.background='#f7f7f8'">
                                    <div class="task-id" style="width: 80px; flex-shrink: 0; font-weight: 600; color: #1976d2; font-size: 12px;">
                                        <?= html_escape($sub_task->unique_id) ?>
                                    </div>
                                    <div class="task-title" style="width: 200px; flex-shrink: 0; font-weight: 600; color: #2d3748; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 12px;">
                                        <?= html_escape($sub_task->task_title) ?>
                                    </div>
                                    <div class="task-estimate" style="width: 60px; flex-shrink: 0; padding: 3px 8px; border: 1px solid #cccccc; background: #f2f2f4; color: #000; border-radius: 20px; text-align: center; font-size: 11px;">
                                        <?= html_escape($sub_task->estimation_time ?: '0') ?>h
                                    </div>
                                    <div class="user-avatar-container" style="width: 30px; flex-shrink: 0; margin-left: auto;">
                                        <?php if ($sub_task->assigned_to_name): ?>
                                            <img src="<?= get_image_url('staff', $sub_task->assigned_to_photo) ?>" width="25" height="25" class="rounded" alt="<?= html_escape($sub_task->assigned_to_name) ?>" title="<?= html_escape($sub_task->assigned_to_name) ?>" />
                                        <?php else: ?>
                                            <div style="background: #e2e8f0; display: flex; align-items: center; justify-content: center; color: #a0aec0; width: 25px; height: 25px; border-radius: 50%; font-size: 10px;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Comments Section -->
    <div class="comments-section" style="margin: 20px 0; padding: 20px; border-top: 1px solid #eee; background: #fff;">
        <h4 style="font-size: 16px; margin-bottom: 20px; color: #555; display: flex; align-items: center;">
            <i class="fas fa-comments" style="margin-right: 10px; color: #6c757d;"></i>
            Comments
            <?php if (!empty($comments)): ?>
                <span style="background: #6c757d; color: white; padding: 2px 8px; border-radius: 10px; font-size: 12px; margin-left: 10px;"><?= count($comments) ?></span>
            <?php endif; ?>
        </h4>
        
        <div class="comments-list" style="max-height: 300px; overflow-y: auto; margin-bottom: 20px;">
            <?php if (!empty($comments)): ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-item" data-comment-id="<?= $comment->id ?>">
                        <img class="comment-avatar" src="<?= get_image_url('staff', $comment->author_photo) ?>" alt="<?= html_escape($comment->author_name) ?>">
                        <div class="comment-content">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="comment-author"><?= $comment->author_id == 1 ? 'System' : html_escape($comment->author_name ?: 'Unknown User') ?></div>
                                <?php if ($comment->author_id == get_loggedin_user_id()): ?>
                                <div class="comment-actions">
                                    <button class="btn btn-sm btn-outline-primary edit-comment-btn" data-comment-id="<?= $comment->id ?>">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger delete-comment-btn ms-1" data-comment-id="<?= $comment->id ?>">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="comment-text"><?php 
                                $comment_text = nl2br(html_escape($comment->comment_text));
                                // Process mentions: extract user names from @[id]Name format
                                $comment_text = preg_replace('/@\[(\d+)\]([^@\s]+)/', '<span class="mentioned-user">@$2</span>', $comment_text);
                                echo $comment_text;
                            ?></div>
                            <div class="comment-meta"><?= date('M d, Y H:i', strtotime($comment->created_at)) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-comments" style="color: #999; text-align: center; padding: 20px;">No comments yet</div>
            <?php endif; ?>
        </div>
        
        <form id="commentForm" method="POST" action="<?= base_url('tracker/add_comment') ?>">
            <input type="hidden" name="task_id" value="<?= $task->unique_id ?>">
            <input type="hidden" id="current-user-id" value="<?= get_loggedin_user_id() ?>">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
            <div class="mention-container">
                <textarea name="comment_text" id="commentText" class="form-control mention-textarea" rows="3" placeholder="Write Comment Here (Type @ to mention someone)" required></textarea>
                <div id="mentionDropdown" class="mention-dropdown" style="display: none;"></div>
            </div>
            <button type="submit" class="btn btn-primary" id="submitCommentBtn" style="margin-top: 10px;">Submit</button>
        </form>
    </div>
        </div>

        <div class="task-detail-sidebar">
            <table class="sidebar-table" style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px; font-weight: 600; width: 40%; vertical-align: top;"><i class="fas fa-columns"></i> Status:</td>
                    <td style="padding: 8px;"><?= ucfirst(str_replace('_', ' ', $task->task_status)) ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: 600; vertical-align: top;"><i class="fas fa-exclamation-circle"></i> Priority:</td>
                    <td style="padding: 8px;"><?= ucfirst($task->priority_level) ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: 600; vertical-align: top;"><i class="fas fa-list-alt"></i> <?= translate('category: ') ?></td>
                    <td style="padding: 8px;"><?= html_escape($task->category ?: 'Unknown') ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: 600; vertical-align: top;"><i class="fas fa-user-plus"></i> Created By:</td>
                    <td style="padding: 8px;"><?= html_escape($task->created_by_name ?: 'Unknown') ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: 600; vertical-align: top;"><i class="fas fa-user-check"></i> Assignee:</td>
                    <td style="padding: 8px;"><?= html_escape($task->assigned_to_name ?: 'Unassigned') ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: 600; vertical-align: top;"><i class="fas fa-user-tie"></i> Coordinator:</td>
                    <td style="padding: 8px;"><?= html_escape($task->coordinator_name ?: 'No Coordinator') ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: 600; vertical-align: top;"><i class="fas fa-tags"></i> Labels:</td>
                    <td style="padding: 8px;">
                        <?php if (!empty($labels)): ?>
                            <?php foreach ($labels as $label): ?>
                                <span class="label-tag"><?= html_escape($label) ?></span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span>No labels</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: 600; vertical-align: top;"><i class="fas fa-file-alt"></i> SOP:</td>
                    <td style="padding: 8px;">
                        <?php if (!empty($task->sop_ids)): ?>
                            <?php 
                            $sop_ids = explode(',', $task->sop_ids);
                            foreach ($sop_ids as $sop_id): 
                                $sop_id = trim($sop_id);
                                if (!empty($sop_id)):
                                    $sop = $this->db->select('title')->where('id', $sop_id)->get('sop')->row();
                            ?>
                                <span class="label-tag"><?= $sop ? html_escape($sop->title) : 'SOP ' . html_escape($sop_id) ?></span>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        <?php else: ?>
                            <span>No SOP</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: 600; vertical-align: top;"><i class="fas fa-puzzle-piece"></i> Initiative:</td>
                    <td style="padding: 8px;"><?= html_escape($task->component_name ?: 'N/A') ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: 600; vertical-align: top;"><i class="fas fa-flag-checkered"></i> Milestone:</td>
                    <td style="padding: 8px;"><?= html_escape($task->milestone_name ?: 'N/A') ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: 600; vertical-align: top;"><i class="fa-solid fa-database"></i> Task Type:</td>
                    <td style="padding: 8px;"><?= html_escape($task->task_type_name ?: 'N/A') ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: 600; vertical-align: top;"><i class="fas fa-calendar-alt"></i> Due Date:</td>
                    <td style="padding: 8px;"><?= $task->estimated_end_time ? date('M d, Y', strtotime($task->estimated_end_time)) : 'N/A' ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: 600; vertical-align: top;"><i class="fas fa-clock"></i> Estimation:</td>
                    <td style="padding: 8px;"><?= html_escape($task->estimation_time ?: '0') ?>h</td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: 600; vertical-align: top;"><i class="fas fa-hourglass-half"></i> Spent Time:</td>
                    <td style="padding: 8px;"><?= html_escape($task->spent_time ?: '0') ?>h</td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: 600; vertical-align: top;"><i class="fas fa-hourglass-end"></i> Remaining Time:</td>
                    <td style="padding: 8px;"><?= html_escape($task->remaining_time ?: '0') ?>h</td>
                </tr>
                
                <?php if ($task->approved_by_name && $task->approved_at): ?>
                <tr>
                    <td style="padding: 8px; font-weight: 600; vertical-align: top;"><i class="fas fa-check-circle"></i> Accepted By:</td>
                    <td style="padding: 8px;"><?= html_escape($task->approved_by_name) ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: 600; vertical-align: top;"><i class="fas fa-calendar-check"></i> Accepted At:</td>
                    <td style="padding: 8px;"><?= date('M d, Y H:i', strtotime($task->approved_at)) ?></td>
                </tr>
                <?php endif; ?>
                
                <?php if ($task->category === 'Customer Query'): ?>
                    <?php 
                    $customer_query_data = null;
                    if (!empty($task->customer_query_data)) {
                        $customer_query_data = json_decode($task->customer_query_data, true);
                    }
                    
                    // Fallback to individual fields if JSON data is not available
                    $source = $customer_query_data['source'] ?? $task->source ?? null;
                    $contact_info = $customer_query_data['contact_info'] ?? $task->contact_info ?? null;
                    $requested_at = $customer_query_data['requested_at'] ?? $task->requested_at ?? null;
                    $request_body = $customer_query_data['request_body'] ?? $task->request_body ?? null;
                    
                    if ($source || $contact_info || $requested_at || $request_body):
                    ?>
                    <tr>
                        <td colspan="2" style="padding: 12px 8px 4px 8px; font-weight: 600; color: #1976d2; border-top: 1px solid #eee;"><i class="fas fa-headset"></i> Customer Query Details</td>
                    </tr>
                    <?php if ($source): ?>
                    <tr>
                        <td style="padding: 4px 8px; font-weight: 600; vertical-align: top; padding-left: 20px;">Source:</td>
                        <td style="padding: 4px 8px;"><?= html_escape($source) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($contact_info): ?>
                    <tr>
                        <td style="padding: 4px 8px; font-weight: 600; vertical-align: top; padding-left: 20px;">Contact:</td>
                        <td style="padding: 4px 8px;"><?= html_escape($contact_info) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($requested_at): ?>
                    <tr>
                        <td style="padding: 4px 8px; font-weight: 600; vertical-align: top; padding-left: 20px;">Requested At:</td>
                        <td style="padding: 4px 8px;"><?= html_escape($requested_at) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($request_body): ?>
                    <tr>
                        <td style="padding: 4px 8px; font-weight: 600; vertical-align: top; padding-left: 20px;">Request Body:</td>
                        <td style="padding: 4px 8px; max-width: 200px; word-wrap: break-word;"><div style="max-height: 100px; overflow-y: auto; padding: 8px; background: #f8f9fa; border-radius: 4px; font-size: 12px;"><?= nl2br(html_escape($request_body)) ?></div></td>
                    </tr>
                    <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </table>
        </div>
    </div>   
</div>

<?php
function getStatusColor($status) {
    $colors = [
        'todo' => '#ffbe0b',
        'in_progress' => '#3a86ff',
        'in_review' => '#17a2b8',
        'completed' => '#06d6a0',
        'done' => '#06d6a0',
        'hold' => '#fd7e14',
        'canceled' => '#dc3545',
        'backlog' => '#adb5bd'
    ];
    return $colors[$status] ?? '#6c757d';
}
?>


<script>
function toggleSubTaskAccordion(id) {
    const header = document.querySelector(`[onclick="toggleSubTaskAccordion('${id}')"]`);
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

$(document).ready(function() {
    $('#commentForm').submit(function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitBtn = $('#submitCommentBtn');
        var originalText = submitBtn.text();
        
        // Convert mentions to proper format before submission
        var commentText = $('#commentText').val();
        var mentionData = $('#commentText').data('mentions') || [];
        
        // Replace @Name with @[id]Name format for server processing
        mentionData.forEach(function(mention) {
            var namePattern = new RegExp('@' + mention.name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g');
            commentText = commentText.replace(namePattern, '@[' + mention.id + ']' + mention.name);
        });
        
        // Disable submit button
        submitBtn.prop('disabled', true).text('Adding...');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize().replace(/comment_text=[^&]*/, 'comment_text=' + encodeURIComponent(commentText)),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Clear form
                    form[0].reset();
                    $('#commentText').data('mentions', []);
                    
                    // Add new comment to the list
                    addCommentToList(response.comment);
                    
                } else {
                    alert(response.message || 'Failed to add comment');
                }
            },
            error: function(xhr) {
                console.log('Error:', xhr.responseText);
                alert('An error occurred while adding the comment');
            },
            complete: function() {
                // Re-enable submit button
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });
    
    // Mention functionality
    var mentionUsers = [];
    var selectedMentionIndex = -1;
    
    $('#commentText').on('input', function(e) {
        var text = $(this).val();
        var cursorPos = this.selectionStart;
        var textBeforeCursor = text.substring(0, cursorPos);
        var atIndex = textBeforeCursor.lastIndexOf('@');
        
        // Clean up mention data when text changes
        var mentionData = $(this).data('mentions') || [];
        var validMentions = [];
        mentionData.forEach(function(mention) {
            if (text.includes('@' + mention.name)) {
                validMentions.push(mention);
            }
        });
        $(this).data('mentions', validMentions);
        
        if (atIndex !== -1) {
            var searchTerm = textBeforeCursor.substring(atIndex + 1);
            if (searchTerm.length >= 0 && !searchTerm.includes(' ')) {
                searchMentionUsers(searchTerm, atIndex);
            } else {
                hideMentionDropdown();
            }
        } else {
            hideMentionDropdown();
        }
    });
    
    $('#commentText').on('keydown', function(e) {
        if ($('#mentionDropdown').is(':visible')) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedMentionIndex = Math.min(selectedMentionIndex + 1, mentionUsers.length - 1);
                updateMentionSelection();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedMentionIndex = Math.max(selectedMentionIndex - 1, 0);
                updateMentionSelection();
            } else if (e.key === 'Enter' && selectedMentionIndex >= 0) {
                e.preventDefault();
                selectMentionUser(mentionUsers[selectedMentionIndex]);
            } else if (e.key === 'Escape') {
                hideMentionDropdown();
            }
        }
    });
    
    function searchMentionUsers(searchTerm, atIndex) {
        var staffList = <?= json_encode($staff_list) ?>;
        var filteredUsers = staffList.filter(function(user) {
            return user.name.toLowerCase().includes(searchTerm.toLowerCase());
        });
        
        mentionUsers = filteredUsers;
        selectedMentionIndex = filteredUsers.length > 0 ? 0 : -1;
        showMentionDropdown(filteredUsers, atIndex);
    }
    
    function showMentionDropdown(users, atIndex) {
        var dropdown = $('#mentionDropdown');
        var html = '';
        
        users.forEach(function(user, index) {
            html += '<div class="mention-item' + (index === 0 ? ' selected' : '') + '" data-user-id="' + user.id + '" data-user-name="' + user.name + '">';
            html += '<span class="mention-name">' + user.name + '</span>';
            html += '</div>';
        });
        
        dropdown.html(html).show();
    }
    
    function updateMentionSelection() {
        $('.mention-item').removeClass('selected');
        $('.mention-item').eq(selectedMentionIndex).addClass('selected');
    }
    
    function selectMentionUser(user) {
        var textarea = $('#commentText');
        var textareaEl = textarea[0];
        var text = textareaEl.value;
        var cursorPos = textareaEl.selectionStart;
        var textBeforeCursor = text.substring(0, cursorPos);
        var atIndex = textBeforeCursor.lastIndexOf('@');
        
        var beforeMention = text.substring(0, atIndex);
        var afterCursor = text.substring(cursorPos);
        var mentionText = '@' + user.name + ' ';
        
        textareaEl.value = beforeMention + mentionText + afterCursor;
        textareaEl.selectionStart = textareaEl.selectionEnd = beforeMention.length + mentionText.length;
        
        // Store the mention data for form submission
        var mentionData = textarea.data('mentions') || [];
        mentionData.push({name: user.name, id: user.id, position: atIndex});
        textarea.data('mentions', mentionData);
        
        hideMentionDropdown();
        textareaEl.focus();
    }
    
    function hideMentionDropdown() {
        $('#mentionDropdown').hide();
        selectedMentionIndex = -1;
    }
    
    $(document).on('click', '.mention-item', function() {
        var userId = $(this).data('user-id');
        var userName = $(this).data('user-name');
        selectMentionUser({id: userId, name: userName});
    });
    
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.mention-container, #mentionDropdown').length) {
            hideMentionDropdown();
        }
    });
    
    // Handle comment edit
    $(document).on('click', '.edit-comment-btn', function() {
        var commentId = $(this).data('comment-id');
        var commentDiv = $(this).closest('.comment-item');
        var commentText = commentDiv.find('.comment-text').text().trim();
        
        var textarea = $('<textarea class="form-control edit-comment-textarea" rows="3"></textarea>').val(commentText);
        var saveBtn = $('<button class="btn btn-sm btn-success save-comment-btn me-2" data-comment-id="' + commentId + '">Save</button>');
        var cancelBtn = $('<button class="btn btn-sm btn-secondary cancel-edit-btn">Cancel</button>');
        
        commentDiv.find('.comment-text').hide().after(textarea);
        commentDiv.find('.comment-actions').hide().after($('<div class="edit-actions"></div>').append(saveBtn, cancelBtn));
    });
    
    // Handle comment save
    $(document).on('click', '.save-comment-btn', function() {
        var commentId = $(this).data('comment-id');
        var commentDiv = $(this).closest('.comment-item');
        var newText = commentDiv.find('.edit-comment-textarea').val().trim();
        
        if (!newText) {
            alert('Comment cannot be empty');
            return;
        }
        
        $.ajax({
            url: '<?= base_url('tracker/update_comment') ?>',
            type: 'POST',
            data: {
                comment_id: commentId,
                comment_text: newText
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    commentDiv.find('.comment-text').text(newText).show();
                    commentDiv.find('.edit-comment-textarea, .edit-actions').remove();
                    commentDiv.find('.comment-actions').show();
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Failed to update comment');
            }
        });
    });
    
    // Handle comment edit cancel
    $(document).on('click', '.cancel-edit-btn', function() {
        var commentDiv = $(this).closest('.comment-item');
        commentDiv.find('.comment-text').show();
        commentDiv.find('.edit-comment-textarea, .edit-actions').remove();
        commentDiv.find('.comment-actions').show();
    });
    
    // Handle comment delete
    $(document).on('click', '.delete-comment-btn', function() {
        if (!confirm('Are you sure you want to delete this comment?')) {
            return;
        }
        
        var commentId = $(this).data('comment-id');
        var commentDiv = $(this).closest('.comment-item');
        
        $.ajax({
            url: '<?= base_url('tracker/delete_comment') ?>',
            type: 'POST',
            data: { comment_id: commentId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    commentDiv.fadeOut(300, function() {
                        $(this).remove();
                        // Update comment count
                        var countSpan = $('.comments-section h4 span');
                        if (countSpan.length) {
                            var currentCount = parseInt(countSpan.text()) || 0;
                            if (currentCount > 1) {
                                countSpan.text(currentCount - 1);
                            } else {
                                countSpan.remove();
                            }
                        }
                    });
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Failed to delete comment');
            }
        });
    });
    
    // Add new comment to the comments list
    function addCommentToList(comment) {
        var currentUserId = $('#current-user-id').val();
        var isOwner = comment.author_id == currentUserId;
        
        var actionsHtml = '';
        if (isOwner) {
            actionsHtml = '<div class="comment-actions">' +
                '<button class="btn btn-sm btn-outline-primary edit-comment-btn" data-comment-id="' + comment.id + '">' +
                    '<i class="fa fa-edit"></i>' +
                '</button>' +
                '<button class="btn btn-sm btn-outline-danger delete-comment-btn ms-1" data-comment-id="' + comment.id + '">' +
                    '<i class="fa fa-trash"></i>' +
                '</button>' +
            '</div>';
        }
        
        // Process mentions in new comment text - extract name from @[id]Name format
        var processedText = comment.comment_text.replace(/@\[(\d+)\]([^@\s]+)/g, '<span class="mentioned-user">@$2</span>');
        
        var commentHtml = '<div class="comment-item" data-comment-id="' + comment.id + '">' +
            '<img class="comment-avatar" src="' + (comment.author_photo || 'assets/images/default-avatar.png') + '" alt="' + comment.author_name + '">' +
            '<div class="comment-content">' +
                '<div class="d-flex justify-content-between align-items-start">' +
                    '<div class="comment-author">' + (comment.author_id == 1 ? 'System' : comment.author_name) + '</div>' +
                    actionsHtml +
                '</div>' +
                '<div class="comment-text">' + processedText + '</div>' +
                '<div class="comment-meta">' + comment.formatted_date + '</div>' +
            '</div>' +
        '</div>';
        
        $('.comments-list').append(commentHtml);
        
        // Update comment count
        var countSpan = $('.comments-section h4 span');
        if (countSpan.length) {
            var currentCount = parseInt(countSpan.text()) || 0;
            countSpan.text(currentCount + 1);
        } else {
            $('.comments-section h4').append('<span style="background: #6c757d; color: white; padding: 2px 8px; border-radius: 10px; font-size: 12px; margin-left: 10px;">1</span>');
        }
        
        // Remove "no comments" message if it exists
        $('.no-comments').remove();
    }
});
</script>
