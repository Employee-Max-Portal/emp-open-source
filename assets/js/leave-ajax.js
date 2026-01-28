/**
 * Leave Management AJAX Utilities
 * Handles AJAX operations for leave management system
 */

// Global AJAX setup for CSRF protection if needed
$.ajaxSetup({
    beforeSend: function(xhr, settings) {
        // Add any global headers or CSRF tokens here if needed
        if (settings.type === 'POST' && typeof csrf_token !== 'undefined') {
            xhr.setRequestHeader('X-CSRF-TOKEN', csrf_token);
        }
    }
});

/**
 * Submit leave form via AJAX
 * @param {string} formSelector - jQuery selector for the form
 * @param {string} buttonSelector - jQuery selector for submit button
 * @param {function} successCallback - Callback function on success
 */
function submitLeaveForm(formSelector, buttonSelector, successCallback) {
    $(formSelector).on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        var $submitBtn = $(buttonSelector);
        var originalText = $submitBtn.html();
        
        // Disable button and show loading
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        // Clear previous errors
        $('.error').text('');
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Show success message
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message || 'Operation completed successfully!');
                    } else {
                        alert(response.message || 'Operation completed successfully!');
                    }
                    
                    // Execute success callback
                    if (typeof successCallback === 'function') {
                        successCallback(response);
                    }
                } else {
                    // Show validation errors
                    if (response.error) {
                        $.each(response.error, function(field, message) {
                            var $errorSpan = $('span.error').filter(function() {
                                return $(this).closest('.form-group').find('[name="' + field + '"]').length > 0;
                            });
                            if ($errorSpan.length) {
                                $errorSpan.text(message);
                            }
                        });
                    }
                    
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Please fix the errors and try again.');
                    } else {
                        alert('Please fix the errors and try again.');
                    }
                }
            },
            error: function(xhr, status, error) {
                if (typeof toastr !== 'undefined') {
                    toastr.error('An error occurred. Please try again.');
                } else {
                    alert('An error occurred. Please try again.');
                }
                console.error('AJAX Error:', error);
            },
            complete: function() {
                // Re-enable button
                $submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
}

/**
 * Load leave details modal
 * @param {number} leaveId - Leave application ID
 * @param {string} modalSelector - jQuery selector for modal container
 */
function loadLeaveDetails(leaveId, modalSelector) {
    $.ajax({
        url: base_url + 'leave/getApprovelLeaveDetails',
        type: 'POST',
        data: {'id': leaveId},
        dataType: "html",
        beforeSend: function() {
            $(modalSelector).html('<div class="text-center p-lg"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
        },
        success: function (data) {
            $(modalSelector).html(data);
            if (typeof mfp_modal === 'function') {
                mfp_modal('#modal');
            }
        },
        error: function() {
            $(modalSelector).html('<div class="text-center p-lg text-danger">Error loading details</div>');
        }
    });
}

/**
 * Load leave request details modal
 * @param {number} leaveId - Leave application ID
 * @param {string} modalSelector - jQuery selector for modal container
 */
function loadLeaveRequestDetails(leaveId, modalSelector) {
    $.ajax({
        url: base_url + 'leave/getRequestDetails',
        type: 'POST',
        data: {'id': leaveId},
        dataType: "html",
        beforeSend: function() {
            $(modalSelector).html('<div class="text-center p-lg"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
        },
        success: function (data) {
            $(modalSelector).html(data);
            if (typeof mfp_modal === 'function') {
                mfp_modal('#modal');
            }
        },
        error: function() {
            $(modalSelector).html('<div class="text-center p-lg text-danger">Error loading details</div>');
        }
    });
}

/**
 * Delete leave application
 * @param {number} leaveId - Leave application ID
 * @param {string} deleteUrl - Delete URL
 * @param {function} successCallback - Callback function on success
 */
function deleteLeaveApplication(leaveId, deleteUrl, successCallback) {
    if (confirm('Are you sure you want to delete this leave application?')) {
        $.ajax({
            url: deleteUrl,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response && response.status === 'success') {
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Leave application deleted successfully!');
                    } else {
                        alert('Leave application deleted successfully!');
                    }
                    
                    if (typeof successCallback === 'function') {
                        successCallback();
                    } else {
                        // Default: reload page
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    }
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Failed to delete leave application.');
                    } else {
                        alert('Failed to delete leave application.');
                    }
                }
            },
            error: function() {
                if (typeof toastr !== 'undefined') {
                    toastr.error('An error occurred while deleting.');
                } else {
                    alert('An error occurred while deleting.');
                }
            }
        });
    }
}

/**
 * Initialize leave balance display
 * @param {object} leaveBalances - Leave balance data
 * @param {string} categorySelector - jQuery selector for category dropdown
 * @param {string} balanceSelector - jQuery selector for balance display field
 */
function initializeLeaveBalance(leaveBalances, categorySelector, balanceSelector) {
    $(categorySelector).on('change', function() {
        const categoryId = $(this).val();
        const balance = leaveBalances[categoryId] || '';
        const displayText = categoryId === 'unpaid' ? 'Unpaid Leave' : 
                           (balance !== '' ? balance + ' day(s)' : '');
        $(balanceSelector).val(displayText);
    }).trigger('change');
}

// Document ready functions
$(document).ready(function() {
    // Initialize tooltips if available
    if (typeof $().tooltip === 'function') {
        $('[data-toggle="tooltip"]').tooltip();
    }
    
    // Initialize date range picker if available
    if (typeof $().daterangepicker === 'function') {
        $('.daterange-picker').daterangepicker({
            opens: 'left',
            locale: {format: 'YYYY/MM/DD'}
        });
    }
});