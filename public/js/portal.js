/**
 * Portal JavaScript - Shared functionality for admin, faculty, and enquirer portals
 */

// Global portal utilities
const Portal = {
    // Initialize portal functionality
    init: function() {
        this.setupConfirmations();
        this.setupTooltips();
        this.setupFormValidation();
        this.setupTableSorting();
        this.setupSearchFilters();
        this.setupSessionTimeout();
    },

    // Setup confirmation dialogs for dangerous actions
    setupConfirmations: function() {
        document.addEventListener('click', function(e) {
            const target = e.target.closest('[data-confirm]');
            if (target) {
                const message = target.getAttribute('data-confirm');
                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    },

    // Setup Bootstrap tooltips
    setupTooltips: function() {
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    },

    // Setup form validation
    setupFormValidation: function() {
        const forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    },

    // Setup table sorting
    setupTableSorting: function() {
        const sortableHeaders = document.querySelectorAll('th[data-sortable]');
        sortableHeaders.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                const table = this.closest('table');
                const column = this.cellIndex;
                const isAscending = this.classList.contains('sort-asc');
                
                // Remove existing sort classes
                table.querySelectorAll('th').forEach(th => {
                    th.classList.remove('sort-asc', 'sort-desc');
                });
                
                // Add new sort class
                this.classList.add(isAscending ? 'sort-desc' : 'sort-asc');
                
                // Sort table rows
                Portal.sortTable(table, column, !isAscending);
            });
        });
    },

    // Sort table by column
    sortTable: function(table, column, ascending = true) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        rows.sort((a, b) => {
            const aText = a.cells[column].textContent.trim();
            const bText = b.cells[column].textContent.trim();
            
            // Try to parse as numbers
            const aNum = parseFloat(aText);
            const bNum = parseFloat(bText);
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return ascending ? aNum - bNum : bNum - aNum;
            }
            
            // Sort as strings
            return ascending ? 
                aText.localeCompare(bText) : 
                bText.localeCompare(aText);
        });
        
        // Reorder rows in DOM
        rows.forEach(row => tbody.appendChild(row));
    },

    // Setup search and filter functionality
    setupSearchFilters: function() {
        const searchInputs = document.querySelectorAll('[data-search-target]');
        searchInputs.forEach(input => {
            input.addEventListener('input', function() {
                const target = document.querySelector(this.getAttribute('data-search-target'));
                const searchTerm = this.value.toLowerCase();
                
                if (target) {
                    Portal.filterTable(target, searchTerm);
                }
            });
        });
    },

    // Filter table rows based on search term
    filterTable: function(table, searchTerm) {
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const shouldShow = searchTerm === '' || text.includes(searchTerm);
            row.style.display = shouldShow ? '' : 'none';
        });
        
        // Update row count if exists
        const countElement = document.querySelector('[data-row-count]');
        if (countElement) {
            const visibleRows = table.querySelectorAll('tbody tr:not([style*="display: none"])').length;
            countElement.textContent = visibleRows;
        }
    },

    // Setup session timeout warning
    setupSessionTimeout: function() {
        let warningShown = false;
        const sessionTimeout = 3600000; // 1 hour in milliseconds
        const warningTime = sessionTimeout - 300000; // 5 minutes before timeout
        
        setTimeout(() => {
            if (!warningShown) {
                warningShown = true;
                if (confirm('Your session will expire in 5 minutes. Click OK to extend your session.')) {
                    // Ping server to extend session
                    fetch(window.location.href, { method: 'HEAD' })
                        .then(() => {
                            warningShown = false;
                            Portal.setupSessionTimeout(); // Reset timer
                        })
                        .catch(() => {
                            alert('Unable to extend session. Please save your work and login again.');
                        });
                }
            }
        }, warningTime);
    },

    // Show loading spinner
    showLoading: function(element) {
        if (element) {
            element.innerHTML = '<span class="loading-spinner"></span> Loading...';
            element.disabled = true;
        }
    },

    // Hide loading spinner
    hideLoading: function(element, originalText) {
        if (element) {
            element.innerHTML = originalText;
            element.disabled = false;
        }
    },

    // Show toast notification
    showToast: function(message, type = 'info') {
        const toastContainer = document.getElementById('toast-container') || Portal.createToastContainer();
        
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        if (typeof bootstrap !== 'undefined') {
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        }
    },

    // Create toast container if it doesn't exist
    createToastContainer: function() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '1055';
        document.body.appendChild(container);
        return container;
    },

    // Format date for display
    formatDate: function(dateString, includeTime = true) {
        const date = new Date(dateString);
        const options = {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        };
        
        if (includeTime) {
            options.hour = '2-digit';
            options.minute = '2-digit';
        }
        
        return date.toLocaleDateString('en-US', options);
    },

    // Format file size
    formatFileSize: function(bytes) {
        const units = ['B', 'KB', 'MB', 'GB'];
        let size = bytes;
        let unitIndex = 0;
        
        while (size >= 1024 && unitIndex < units.length - 1) {
            size /= 1024;
            unitIndex++;
        }
        
        return `${size.toFixed(1)} ${units[unitIndex]}`;
    },

    // Validate email format
    isValidEmail: function(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },

    // Validate phone number format
    isValidPhone: function(phone) {
        const phoneRegex = /^[0-9+\-\s()]{10,15}$/;
        return phoneRegex.test(phone);
    },

    // Export table data to CSV
    exportTableToCSV: function(tableId, filename = 'export.csv') {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        const rows = table.querySelectorAll('tr');
        const csvContent = [];
        
        rows.forEach(row => {
            const cols = row.querySelectorAll('td, th');
            const rowData = Array.from(cols).map(col => {
                return '"' + col.textContent.trim().replace(/"/g, '""') + '"';
            });
            csvContent.push(rowData.join(','));
        });
        
        const csvString = csvContent.join('\n');
        const blob = new Blob([csvString], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }
};

// Initialize portal when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    Portal.init();
});

// Add CSS for sorting indicators
const style = document.createElement('style');
style.textContent = `
    th[data-sortable]:after {
        content: ' ↕';
        color: #ccc;
    }
    th.sort-asc:after {
        content: ' ↑';
        color: #333;
    }
    th.sort-desc:after {
        content: ' ↓';
        color: #333;
    }
`;
document.head.appendChild(style);