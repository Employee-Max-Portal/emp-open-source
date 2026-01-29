<div class="row" style="margin-bottom: 30px;">
    <!-- Modern Summary Cards -->
    <div class="col-md-4">
        <div class="modern-card total-card">
            <div class="card-icon">
                <i class="fas fa-tasks"></i>
            </div>
            <div class="card-content">
                <h3 class="card-number"><?= $total_tasks ?></h3>
                <p class="card-title">Total Tasks</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="modern-card completed-card">
            <div class="card-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="card-content">
                <h3 class="card-number"><?= $completed_tasks ?></h3>
                <p class="card-title">Completed</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="modern-card incomplete-card">
            <div class="card-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="card-content">
                <h3 class="card-number"><?= $incomplete_tasks ?></h3>
                <p class="card-title">Incomplete</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <header class="panel-heading">
                <h2 class="panel-title">Task Lists</h2>
            </header>
            <div class="panel-body">
                <?php if (empty($nhd_tasks)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No pending NHD tasks found.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
						  <table class="table table-bordered table-hover table-condensed table_default" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th width="3%"><?= translate('sl'); ?></th>
                                    <th width="20%"><?= translate('assigned_to'); ?></th>
                                    <th width="30%"><?= translate('title'); ?></th>
                                    <th width="10%"><?= translate('status'); ?></th>
                                    <th width="12%"><?= translate('created_at'); ?></th>
                                    <th width="25%"><?= translate('actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $count = 1; foreach ($nhd_tasks as $task): ?>
                                    <tr>
                                        <td><?php echo $count++; ?></td>
                                        <td><?= $task->staff_id .' - '. $task->applicant ?></td>
                                        <td><?= $task->task_title ?></td>
                                        <td><?= translate($task->task_status); ?></td>
                                        <td><?= date('M d, Y H:i', strtotime($task->logged_at)) ?></td>
                                        <td>
											<a class="btn btn-info btn-circle icon" href="javascript:void(0);" data-toggle="tooltip" data-original-title="<?php echo translate('View Task'); ?>" onclick="viewTask('<?=$task->id?>')">
                                                <i class="fas fa-eye" style="color: #ffffff;"></i>
                                            </a>

                                            <button type="button" class="btn btn-info btn-sm" 
                                                    onclick="viewComments(<?= $task->id ?>, '<?= addslashes($task->task_title) ?>')">
                                                <i class="fas fa-comments"></i> Comments
                                            </button>
                                            <button type="button" class="btn btn-success btn-sm" 
                                                    onclick="completeTask(<?= $task->id ?>, '<?= addslashes($task->task_title) ?>')">
                                                <i class="fas fa-check"></i> Complete
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<!-- Complete Task Modal -->
<div class="zoom-anim-dialog modal-block modal-block-danger mfp-hide" id="completeTaskModal">
       <section class="panel text-center" style="padding: 30px 20px;">
        <div class="panel-body">
            <!-- Green Check Icon -->
            <div class="modal-Text" style="font-size: 60px; color: #28a745; margin-bottom: 15px;">
                <i class="fas fa-check-circle"></i>
            </div>
           
            <!-- Confirmation Text -->
            <div class="modal-text">
                <h4 style="font-weight: bold; margin-bottom: 10px;">Are you sure?</h4>
                <p style="margin-bottom: 5px;">Do you want to complete this task?</p>
                <p><strong id="taskTitle" style="color: #333;"></strong></p>
            </div>
        </div>
        <!-- Buttons -->
        <footer class="panel-footer text-center" style="border-top: none; margin-top: 20px;">
			<button class="btn btn-secondary modal-dismiss custom-cancel-btn">Cancel</button>
            <button class="btn btn-success" onclick="confirmComplete()">Yes, Complete</button>
        </footer>
    </section>
</div>

<style>
  /* Modern Summary Cards */
  .modern-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 25px;
    color: white;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    position: relative;
    overflow: hidden;
  }

  .modern-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.1);
    opacity: 0;
    transition: opacity 0.3s ease;
  }

  .modern-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
  }

  .modern-card:hover::before {
    opacity: 1;
  }

  .total-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  .completed-card {
    background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
  }

  .incomplete-card {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  }

  .card-icon {
    font-size: 40px;
    margin-right: 20px;
    opacity: 0.9;
  }

  .card-content {
    flex: 1;
  }

  .card-number {
    font-size: 36px;
    font-weight: 700;
    margin: 0;
    line-height: 1;
  }

  .card-title {
    font-size: 16px;
    margin: 5px 0 0 0;
    opacity: 0.9;
    font-weight: 500;
  }

  .custom-cancel-btn {
    margin-right: 10px;
    border: 1px solid #000;
    transition: all 0.3s ease;
  }

  .custom-cancel-btn:hover {
    background-color: #000;
    color: #fff;
    border-color: #000;
  }
</style>

<!-- Comments Modal -->
<div class="modal fade" id="commentsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Comments - <span id="commentsTaskTitle"></span></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="commentsList" style="max-height: 250px; overflow-y: auto; padding-right: 10px;">
                    <!-- Comments will be loaded here -->
                </div>
                <hr>
                <div class="form-group">
                    <label for="newComment">Add Comment</label>
                    <div class="mention-container">
                        <textarea id="newComment" class="form-control" rows="3" placeholder="Write your comment... (Type @ to mention someone)"></textarea>
                        <div id="mentionDropdown" class="mention-dropdown" style="display: none;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="addComment()">Add Comment</button>
            </div>
        </div>
    </div>
</div>

<!-- Task Details Modal -->
<div class="modal fade" id="taskDetailsModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xl custom-task-modal" role="document">
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

<style>
  .custom-task-modal {
    width: 80% !important;
    max-width: 1200px;
    height: 90vh;
  }

  .custom-task-modal .modal-content {
    height: 100%;
    display: flex;
    flex-direction: column;
  }

  .custom-task-modal .modal-body {
    flex: 1;
    overflow-y: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
  }

  .custom-task-modal .modal-body::-webkit-scrollbar {
    width: 0px;
    background: transparent;
  }

  #commentsList {
    scrollbar-width: none;
    -ms-overflow-style: none;
  }

  #commentsList::-webkit-scrollbar {
    width: 0px;
    background: transparent;
  }

  /* Mention system styles */
  .mention-container {
    position: relative;
  }

  .mention-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    min-width: 200px;
  }

  #mentionDropdown {
    bottom: 100%;
    top: auto;
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
  
  .mention-name {
    font-size: 14px;
    color: #333;
  }

  .mention-highlight {
    background: #e3f2fd;
    color: #1976d2;
    padding: 2px 4px;
    border-radius: 3px;
    font-weight: 500;
  }
</style>


<script>
let currentTaskId = null;
let currentTaskTitle = '';

function completeTask(taskId, taskTitle) {
    currentTaskId = taskId;
    currentTaskTitle = taskTitle;
    $('#taskTitle').text(taskTitle);
    mfp_modal('#completeTaskModal');
}

function confirmComplete() {
    $.ajax({
        url: '<?= base_url('dashboard/nhd_dashboard') ?>',
        type: 'POST',
        data: {
            complete_task: 1,
            task_id: currentTaskId
        },
        success: function(response) {
            $.magnificPopup.close();
            location.reload();
        },
        error: function() {
            alert('Error completing task');
        }
    });
}

function viewComments(taskId, taskTitle) {
    currentTaskId = taskId;
    $('#commentsTaskTitle').text(taskTitle);
    $('#commentsModal').modal('show');
    loadComments(taskId);
}

function loadComments(taskId) {
    $.ajax({
        url: '<?= base_url('dashboard/get_task_comments') ?>',
        type: 'POST',
        data: { task_id: taskId },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                displayComments(response.comments);
            } else {
                $('#commentsList').html('<p class="text-danger">Error loading comments: ' + response.message + '</p>');
            }
        },
        error: function() {
            $('#commentsList').html('<p class="text-danger">Error loading comments</p>');
        }
    });
}

function displayComments(comments) {
    let html = '';
    if (comments.length === 0) {
        html = '<p class="text-muted">No comments yet.</p>';
    } else {
        comments.forEach(function(comment) {
            const isOwn = comment.author_id == <?= get_loggedin_user_id() ?>;
            html += `
                <div class="comment-item" style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 12px; margin-bottom: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <div class="comment-header d-flex justify-content-between align-items-center" style="margin-bottom: 10px;">
						<div class="comment-header" style="overflow: hidden; margin-bottom: 8px;">
						  <!-- Author + Date (left side) -->
						  <span class="comment-author" style="font-weight: bold; margin-right: 10px;">
							${comment.author_name || 'Unknown'}
						  </span>
						  <span class="comment-meta" style="color: #888; font-size: 0.9em;">
							${formatDate(comment.created_at)}
						  </span>

						  <!-- Actions (right side) -->
						  ${isOwn ? `
						  <div class="comment-actions" style="float: right;">
							<button onclick="editComment(${comment.id}, \`${comment.comment_text.replace(/`/g, "\\`")}\`)"
							  class="btn btn-sm btn-link" title="Edit">
							  <i class="fas fa-edit"></i>
							</button>
							<button onclick="deleteComment(${comment.id})"
							  class="btn btn-sm btn-link text-danger" title="Delete">
							  <i class="fas fa-trash"></i>
							</button>
						  </div>` : ''}
						</div>
                    </div>
                    <div class="comment-text" id="comment-text-${comment.id}" style="color: #212529; line-height: 1.5;">${highlightMentions(comment.comment_text)}</div>
                    <div class="comment-edit" id="comment-edit-${comment.id}" style="display:none;">
                        <div class="mention-container">
                            <textarea class="form-control mention-textarea" id="edit-text-${comment.id}" rows="3" style="margin-bottom: 10px;">${comment.comment_text}</textarea>
                            <div class="mention-dropdown" style="display: none;"></div>
                        </div>
                        <div class="text-right">
                            <button class="btn btn-sm btn-success" onclick="saveEdit(${comment.id})"><i class="fas fa-save"></i> Save</button>
                            <button class="btn btn-sm btn-secondary ml-1" onclick="cancelEdit(${comment.id})"><i class="fas fa-times"></i> Cancel</button>
                        </div>
                    </div>
                </div>
            `;
        });
    }
    $('#commentsList').html(html);
}

function addComment() {
    const commentText = $('#newComment').val().trim();
    if (!commentText) {
        alert('Please enter a comment');
        return;
    }
    
    // Convert mentions to proper format before submission
    var processedComment = commentText;
    var mentionData = $('#newComment').data('mentions') || [];
    
    // Replace @Name with @[id] format for server processing
    mentionData.forEach(function(mention) {
        var namePattern = new RegExp('@' + mention.name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g');
        processedComment = processedComment.replace(namePattern, '@[' + mention.id + ']');
    });
    
    $.ajax({
        url: '<?= base_url('dashboard/add_task_comment') ?>',
        type: 'POST',
        data: {
            task_id: currentTaskId,
            comment_text: processedComment
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                $('#newComment').val('');
                clearMentionData($('#newComment'));
                hideMentionDropdown();
                loadComments(currentTaskId); // Reload comments
            } else {
                alert('Error adding comment: ' + response.message);
            }
        },
        error: function() {
            alert('Error adding comment');
        }
    });
}

// View task details in modal
function viewTask(id) {
    $.ajax({
        url: base_url + 'dashboard/viewTracker_Issue',
        type: 'POST',
        data: {'id': id},
        dataType: "html",
        success: function (data) {
            $('#taskDetailsModal .modal-body').html(data);
            $('#taskDetailsModal').modal('show');
        }
    });
}

// Close modal when clicking outside
$(document).on('click', '.modal', function(e) {
    if (e.target === this) {
        $(this).modal('hide');
    }
});
function editComment(commentId, commentText) {
    $(`#comment-text-${commentId}`).hide();
    $(`#comment-edit-${commentId}`).show();
    // Focus on the edit textarea to enable mention functionality
    $(`#edit-text-${commentId}`).focus();
}

function cancelEdit(commentId) {
    $(`#comment-text-${commentId}`).show();
    $(`#comment-edit-${commentId}`).hide();
}

function saveEdit(commentId) {
    const textarea = $(`#edit-text-${commentId}`);
    const newText = textarea.val().trim();
    if (!newText) {
        alert('Comment cannot be empty');
        return;
    }
    
    // Convert mentions to proper format before submission
    var processedComment = newText;
    var mentionData = textarea.data('mentions') || [];
    
    // Replace @Name with @[id] format for server processing
    mentionData.forEach(function(mention) {
        var namePattern = new RegExp('@' + mention.name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g');
        processedComment = processedComment.replace(namePattern, '@[' + mention.id + ']');
    });
    
    $.ajax({
        url: '<?= base_url('dashboard/edit_task_comment') ?>',
        type: 'POST',
        data: {
            comment_id: commentId,
            comment_text: processedComment
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                loadComments(currentTaskId);
            } else {
                alert('Error updating comment: ' + response.message);
            }
        },
        error: function() {
            alert('Error updating comment');
        }
    });
}

function deleteComment(commentId) {
    if (!confirm('Are you sure you want to delete this comment?')) {
        return;
    }
    
    $.ajax({
        url: '<?= base_url('dashboard/delete_task_comment') ?>',
        type: 'POST',
        data: { comment_id: commentId },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                loadComments(currentTaskId);
            } else {
                alert('Error deleting comment: ' + response.message);
            }
        },
        error: function() {
            alert('Error deleting comment');
        }
    });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

function highlightMentions(text) {
    // Highlight mentions that are already in @name format
    return text.replace(/@([A-Za-z\s]+)/g, '<span class="mention-highlight">@$1</span>');
}

// Clear mention data when textarea is cleared
function clearMentionData(textarea) {
    textarea.removeData('mentions');
}

// Mention functionality
var mentionUsers = [];
var selectedMentionIndex = -1;

$(document).ready(function() {
    // Setup mention functionality for main comment textarea
    setupMentionForTextarea($('#newComment'));
    
    // Setup mention functionality for edit textareas when they are created
    $(document).on('focus', '.mention-textarea', function() {
        setupMentionForTextarea($(this));
    });
});

function setupMentionForTextarea(textarea) {
    textarea.off('input.mention').on('input.mention', function(e) {
        var text = $(this).val();
        var cursorPos = this.selectionStart;
        var textBeforeCursor = text.substring(0, cursorPos);
        var atIndex = textBeforeCursor.lastIndexOf('@');
        
        if (atIndex !== -1) {
            var searchTerm = textBeforeCursor.substring(atIndex + 1);
            if (searchTerm.length >= 0 && !searchTerm.includes(' ')) {
                searchMentionUsers(searchTerm, atIndex, textarea);
            } else {
                hideMentionDropdown();
            }
        } else {
            hideMentionDropdown();
        }
    });
    
    textarea.off('keydown.mention').on('keydown.mention', function(e) {
        var visibleDropdown = $(this).siblings('.mention-dropdown:visible');
        if (visibleDropdown.length) {
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
                selectMentionUser(mentionUsers[selectedMentionIndex], textarea);
            } else if (e.key === 'Escape') {
                hideMentionDropdown();
            }
        }
    });
}

function searchMentionUsers(searchTerm, atIndex, textarea) {
    $.get('<?= base_url('dashboard/get_mention_users') ?>', {search: searchTerm}, function(users) {
        mentionUsers = users;
        selectedMentionIndex = users.length > 0 ? 0 : -1;
        showMentionDropdown(users, atIndex, textarea);
    });
}

function showMentionDropdown(users, atIndex, textarea) {
    var dropdown = textarea.siblings('.mention-dropdown');
    if (dropdown.length === 0) {
        // Create dropdown if it doesn't exist (for edit mode)
        dropdown = $('<div class="mention-dropdown" style="display: none;"></div>');
        textarea.parent().append(dropdown);
    }
    
    var html = '';
    users.forEach(function(user, index) {
        html += '<div class="mention-item' + (index === 0 ? ' selected' : '') + '" data-user-id="' + user.id + '" data-user-name="' + user.name + '">';
        html += '<span class="mention-name">' + user.name + '</span>';
        html += '</div>';
    });
    
    dropdown.html(html).show();
}

function updateMentionSelection() {
    $('.mention-dropdown:visible .mention-item').removeClass('selected');
    $('.mention-dropdown:visible .mention-item').eq(selectedMentionIndex).addClass('selected');
}

function selectMentionUser(user, textarea) {
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
    $('.mention-dropdown').hide();
    selectedMentionIndex = -1;
}

$(document).on('click', '.mention-item', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    var userId = $(this).data('user-id');
    var userName = $(this).data('user-name');
    
    // Find the textarea associated with this dropdown
    var dropdown = $(this).closest('.mention-dropdown');
    var textarea = dropdown.siblings('textarea');
    
    if (textarea.length) {
        selectMentionUser({id: userId, name: userName}, textarea);
    }
});

$(document).on('click', function(e) {
    if (!$(e.target).closest('.mention-container, .mention-dropdown').length) {
        hideMentionDropdown();
    }
});
</script>