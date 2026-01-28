$(document).ready(function() {
    // Handle comment form submission
    $(document).on('submit', '#comment-form', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.text();
        
        // Disable submit button
        submitBtn.prop('disabled', true).text('Adding...');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Clear form
                    form[0].reset();
                    
                    // Add new comment to the list
                    addCommentToList(response.comment);
                    
                    // Show success message
                    showAlert('success', response.message);
                } else {
                    showAlert('error', response.message || 'Failed to add comment');
                }
            },
            error: function() {
                showAlert('error', 'An error occurred while adding the comment');
            },
            complete: function() {
                // Re-enable submit button
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });
    
    // Handle comment edit
    $(document).on('click', '.edit-comment-btn', function() {
        const commentId = $(this).data('comment-id');
        const commentDiv = $(this).closest('.comment-item');
        const commentText = commentDiv.find('.comment-text').text().trim();
        
        // Replace comment text with textarea
        const textarea = $('<textarea class="form-control edit-comment-textarea" rows="3"></textarea>').val(commentText);
        const saveBtn = $('<button class="btn btn-sm btn-success save-comment-btn me-2" data-comment-id="' + commentId + '">Save</button>');
        const cancelBtn = $('<button class="btn btn-sm btn-secondary cancel-edit-btn">Cancel</button>');
        
        commentDiv.find('.comment-text').hide().after(textarea);
        commentDiv.find('.comment-actions').hide().after($('<div class="edit-actions mt-2"></div>').append(saveBtn, cancelBtn));
    });
    
    // Handle comment save
    $(document).on('click', '.save-comment-btn', function() {
        const commentId = $(this).data('comment-id');
        const commentDiv = $(this).closest('.comment-item');
        const newText = commentDiv.find('.edit-comment-textarea').val().trim();
        
        if (!newText) {
            showAlert('error', 'Comment cannot be empty');
            return;
        }
        
        $.ajax({
            url: base_url + 'tracker/update_comment',
            type: 'POST',
            data: {
                comment_id: commentId,
                comment_text: newText
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update comment text and restore view
                    commentDiv.find('.comment-text').text(newText).show();
                    commentDiv.find('.edit-comment-textarea, .edit-actions').remove();
                    commentDiv.find('.comment-actions').show();
                    showAlert('success', response.message);
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function() {
                showAlert('error', 'Failed to update comment');
            }
        });
    });
    
    // Handle comment edit cancel
    $(document).on('click', '.cancel-edit-btn', function() {
        const commentDiv = $(this).closest('.comment-item');
        commentDiv.find('.comment-text').show();
        commentDiv.find('.edit-comment-textarea, .edit-actions').remove();
        commentDiv.find('.comment-actions').show();
    });
    
    // Handle comment delete
    $(document).on('click', '.delete-comment-btn', function() {
        if (!confirm('Are you sure you want to delete this comment?')) {
            return;
        }
        
        const commentId = $(this).data('comment-id');
        const commentDiv = $(this).closest('.comment-item');
        
        $.ajax({
            url: base_url + 'tracker/delete_comment',
            type: 'POST',
            data: { comment_id: commentId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    commentDiv.fadeOut(300, function() {
                        $(this).remove();
                    });
                    showAlert('success', response.message);
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function() {
                showAlert('error', 'Failed to delete comment');
            }
        });
    });
});

// Add new comment to the comments list
function addCommentToList(comment) {
    const currentUserId = $('#current-user-id').val(); // Add this hidden input to your form
    const isOwner = comment.author_id == currentUserId;
    
    const commentHtml = `
        <div class="comment-item border-bottom pb-3 mb-3">
            <div class="d-flex">
                <img src="${comment.author_photo ? comment.author_photo : base_url + 'assets/images/default-avatar.png'}" 
                     alt="${comment.author_name}" class="rounded-circle me-3" width="40" height="40">
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>${comment.author_name}</strong>
                            <small class="text-muted ms-2">${comment.formatted_date}</small>
                        </div>
                        ${isOwner ? `
                        <div class="comment-actions">
                            <button class="btn btn-sm btn-outline-primary edit-comment-btn" data-comment-id="${comment.id}">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-comment-btn ms-1" data-comment-id="${comment.id}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                        ` : ''}
                    </div>
                    <div class="comment-text mt-2">${comment.comment_text}</div>
                </div>
            </div>
        </div>
    `;
    
    $('#comments-list').prepend(commentHtml);
}

// Show alert messages
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Remove existing alerts
    $('.alert').remove();
    
    // Add new alert at the top of the page
    $('body').prepend(alertHtml);
    
    // Auto-hide after 3 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 3000);
}