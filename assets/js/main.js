/**
 * main.js - اسکریپت‌های اصلی سیستم فروش کیان ورنا
 */

// Document Ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initializeComponents();
    
    // Auto-hide alerts
    autoHideAlerts();
    
    // Initialize tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
        initializeTooltips();
    }
});

/**
 * Initialize all JavaScript components
 */
function initializeComponents() {
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize file upload previews
    initializeFileUploads();
    
    // Initialize mobile sidebar
    initializeMobileSidebar();
    
    // Initialize price formatting
    initializePriceFormatting();
    
    // Initialize confirmation dialogs
    initializeConfirmations();
}

/**
 * Auto-hide alert messages after 5 seconds
 */
function autoHideAlerts() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            } else {
                alert.style.display = 'none';
            }
        }, 5000);
    });
}

/**
 * Initialize Bootstrap tooltips
 */
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    // Add was-validated class to forms on submit
    const forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Real-time validation for specific fields
    const requiredFields = document.querySelectorAll('input[required], select[required], textarea[required]');
    requiredFields.forEach(function(field) {
        field.addEventListener('blur', function() {
            if (!this.value.trim()) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    });
}

/**
 * Initialize file upload previews
 */
function initializeFileUploads() {
    // Single image preview
    window.previewImage = function(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById(previewId);
                if (preview) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    preview.classList.add('fade-in');
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    };

    // Multiple images preview
    window.previewMultipleImages = function(input, previewContainerId) {
        const container = document.getElementById(previewContainerId);
        if (!container) return;

        container.innerHTML = '';
        
        if (input.files) {
            Array.from(input.files).forEach(function(file, index) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'image-item fade-in';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'image-preview';
                    img.alt = 'پیش‌نمایش تصویر ' + (index + 1);
                    
                    div.appendChild(img);
                    container.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        }
    };

    // Drag and drop functionality
    const dropZones = document.querySelectorAll('.file-upload-area');
    dropZones.forEach(function(dropZone) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        dropZone.addEventListener('drop', handleDrop, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    function highlight(e) {
        e.target.classList.add('dragover');
    }

    function unhighlight(e) {
        e.target.classList.remove('dragover');
    }

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        const fileInput = e.target.querySelector('input[type="file"]');
        if (fileInput) {
            fileInput.files = files;
            fileInput.dispatchEvent(new Event('change'));
        }
    }
}

/**
 * Initialize mobile sidebar
 */
function initializeMobileSidebar() {
    // Toggle sidebar function
    window.toggleSidebar = function() {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.toggle('show');
        }
    };

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        const sidebar = document.querySelector('.sidebar');
        const toggleBtn = document.querySelector('.sidebar-toggle');
        
        if (sidebar && sidebar.classList.contains('show')) {
            if (!sidebar.contains(e.target) && (!toggleBtn || !toggleBtn.contains(e.target))) {
                sidebar.classList.remove('show');
            }
        }
    });

    // Close sidebar on window resize if desktop
    window.addEventListener('resize', function() {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar && window.innerWidth >= 768) {
            sidebar.classList.remove('show');
        }
    });
}

/**
 * Initialize price formatting
 */
function initializePriceFormatting() {
    // Format price inputs
    const priceInputs = document.querySelectorAll('input[type="number"][name*="price"]');
    priceInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            // Remove non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        input.addEventListener('blur', function() {
            if (this.value) {
                // Format the display value
                const formatted = new Intl.NumberFormat('fa-IR').format(this.value);
                // You can add a display element to show formatted value
            }
        });
    });

    // Global price formatting function
    window.formatPrice = function(price) {
        return new Intl.NumberFormat('fa-IR').format(price) + ' ریال';
    };
}

/**
 * Initialize confirmation dialogs
 */
function initializeConfirmations() {
    // Global confirm delete function
    window.confirmDelete = function(message) {
        message = message || 'آیا از حذف این مورد اطمینان دارید؟ این عمل قابل برگشت نیست.';
        return confirm(message);
    };

    // Attach to delete links
    const deleteLinks = document.querySelectorAll('a[href*="delete"], .btn-delete');
    deleteLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            if (!confirmDelete()) {
                e.preventDefault();
                return false;
            }
        });
    });
}

/**
 * Loading states
 */
window.showLoading = function(element, text) {
    text = text || 'در حال پردازش...';
    if (element) {
        element.disabled = true;
        const originalText = element.innerHTML;
        element.setAttribute('data-original-text', originalText);
        element.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>' + text;
        element.classList.add('loading');
    }
};

window.hideLoading = function(element) {
    if (element && element.getAttribute('data-original-text')) {
        element.disabled = false;
        element.innerHTML = element.getAttribute('data-original-text');
        element.removeAttribute('data-original-text');
        element.classList.remove('loading');
    }
};

/**
 * Form utilities
 */
window.validateForm = function(formId) {
    const form = document.getElementById(formId);
    if (form) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(function(field) {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
            }
        });
        
        return isValid;
    }
    return true;
};

/**
 * AJAX utilities
 */
window.sendAjaxRequest = function(url, data, method, callback) {
    method = method || 'POST';
    
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (callback) callback(null, response);
                } catch (e) {
                    if (callback) callback(e, null);
                }
            } else {
                if (callback) callback(new Error('Network error'), null);
            }
        }
    };
    
    // Convert data object to query string
    let queryString = '';
    if (data && typeof data === 'object') {
        queryString = Object.keys(data).map(key => 
            encodeURIComponent(key) + '=' + encodeURIComponent(data[key])
        ).join('&');
    } else {
        queryString = data || '';
    }
    
    xhr.send(queryString);
};

/**
 * Notification system
 */
window.showNotification = function(message, type, duration) {
    type = type || 'info';
    duration = duration || 5000;
    
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    }[type] || 'alert-info';
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto remove after duration
    setTimeout(function() {
        if (alertDiv.parentNode) {
            alertDiv.parentNode.removeChild(alertDiv);
        }
    }, duration);
};

/**
 * Data table utilities
 */
window.initializeDataTable = function(tableId) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    // Add sorting functionality to headers
    const headers = table.querySelectorAll('th[data-sort]');
    headers.forEach(function(header) {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function() {
            const sortField = this.dataset.sort;
            const sortOrder = this.dataset.sortOrder === 'asc' ? 'desc' : 'asc';
            this.dataset.sortOrder = sortOrder;
            
            // Add sorting icon
            headers.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
            this.classList.add('sort-' + sortOrder);
            
            // Trigger sort (you can implement actual sorting logic here)
            console.log('Sort by:', sortField, sortOrder);
        });
    });
};

/**
 * Search functionality
 */
window.initializeSearch = function(inputId, targetSelector) {
    const searchInput = document.getElementById(inputId);
    const targets = document.querySelectorAll(targetSelector);
    
    if (!searchInput || !targets.length) return;
    
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.toLowerCase().trim();
        
        searchTimeout = setTimeout(function() {
            targets.forEach(function(target) {
                const text = target.textContent.toLowerCase();
                if (query === '' || text.includes(query)) {
                    target.style.display = '';
                } else {
                    target.style.display = 'none';
                }
            });
        }, 300);
    });
};

/**
 * Animation utilities
 */
window.animateElement = function(element, animation) {
    animation = animation || 'fadeIn';
    if (element) {
        element.classList.add('animated', animation);
        element.addEventListener('animationend', function() {
            element.classList.remove('animated', animation);
        }, { once: true });
    }
};

// Error handling
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
    // You can add error reporting here
});

// Unhandled promise rejection
window.addEventListener('unhandledrejection', function(e) {
    console.error('Unhandled Promise Rejection:', e.reason);
    e.preventDefault();
});

// Initialize on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeComponents);
} else {
    initializeComponents();
}