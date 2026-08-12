/**
 * Admin Theme - JavaScript
 * Professional Admin Dashboard Interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize sidebar toggle on mobile
    const toggleBtn = document.getElementById('toggleSidebar');
    const sidebar = document.querySelector('.sidebar');
    
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            if (sidebar) {
                sidebar.classList.toggle('show');
            }
        });
    }
    
    // Close sidebar when clicking outside
    document.addEventListener('click', function(event) {
        if (sidebar && sidebar.classList.contains('show')) {
            if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                sidebar.classList.remove('show');
            }
        }
    });
    
    // Close alerts
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        const closeBtn = alert.querySelector('.btn-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                alert.style.display = 'none';
            });
        }
    });
    
    // Form validation
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(form)) {
                e.preventDefault();
            }
        });
    });
    
    // Character counter for textareas
    const textareas = document.querySelectorAll('textarea[data-maxlength]');
    textareas.forEach(textarea => {
        const maxLength = textarea.getAttribute('data-maxlength');
        const counter = document.createElement('small');
        counter.className = 'text-muted d-block mt-2';
        textarea.parentNode.insertBefore(counter, textarea.nextSibling);
        
        const updateCounter = () => {
            counter.textContent = textarea.value.length + '/' + maxLength + ' characters';
        };
        
        textarea.addEventListener('input', updateCounter);
        textarea.addEventListener('change', updateCounter);
        updateCounter();
    });
});

/**
 * Validate form inputs
 */
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('[data-required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            showInputError(input, 'This field is required');
            isValid = false;
        } else {
            clearInputError(input);
        }
    });
    
    return isValid;
}

/**
 * Show input error
 */
function showInputError(input, message) {
    const group = input.closest('.form-group');
    if (group) {
        input.classList.add('is-invalid');
        let errorDiv = group.querySelector('.invalid-feedback');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback d-block';
            group.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
    }
}

/**
 * Clear input error
 */
function clearInputError(input) {
    const group = input.closest('.form-group');
    if (group) {
        input.classList.remove('is-invalid');
        const errorDiv = group.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
}

/**
 * Show loading state on button
 */
function showButtonLoading(button) {
    const text = button.textContent;
    button.disabled = true;
    button.innerHTML = '<span class="loading" style="display: inline-block; margin-right: 8px;"><i data-feather="loader" style="width: 16px; height: 16px;"></i></span> Loading...';
    button.dataset.originalText = text;
}

/**
 * Hide loading state on button
 */
function hideButtonLoading(button) {
    if (button.dataset.originalText) {
        button.textContent = button.dataset.originalText;
    }
    button.disabled = false;
}

/**
 * Show modal success message
 */
function showSuccessMessage(message) {
    const bgColor = 'rgba(46, 216, 182, 0.1)';
    const textColor = '#2ed8b6';
    createNotification(message, bgColor, textColor, 'success');
}

/**
 * Show modal error message
 */
function showErrorMessage(message) {
    const bgColor = 'rgba(255, 83, 112, 0.1)';
    const textColor = '#ff5370';
    createNotification(message, bgColor, textColor, 'error');
}

/**
 * Create notification
 */
function createNotification(message, bgColor, textColor, type) {
    // Check if notification already exists
    let notification = document.querySelector('.notification-container');
    if (!notification) {
        notification = document.createElement('div');
        notification.className = 'notification-container';
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        `;
        document.body.appendChild(notification);
    }
    
    const notif = document.createElement('div');
    notif.className = 'notification';
    notif.style.cssText = `
        background-color: ${bgColor};
        color: ${textColor};
        padding: 15px 20px;
        border-radius: 8px;
        border-left: 4px solid ${textColor};
        min-width: 300px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        animation: slideIn 0.3s ease-out;
    `;
    notif.textContent = message;
    
    notification.appendChild(notif);
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        notif.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notif.remove(), 300);
    }, 4000);
}

/**
 * Add CSS animations
 */
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
    
    .disabled {
        opacity: 0.6;
        cursor: not-allowed !important;
        pointer-events: none;
    }
    
    .is-invalid {
        border-color: #ff5370 !important;
    }
    
    .invalid-feedback {
        color: #ff5370;
        font-size: 12px;
        margin-top: 5px;
    }
`;
document.head.appendChild(style);

// Export functions for use in other scripts
window.AdminTheme = {
    validateForm,
    showInputError,
    clearInputError,
    showButtonLoading,
    hideButtonLoading,
    showSuccessMessage,
    showErrorMessage,
    createNotification
};
