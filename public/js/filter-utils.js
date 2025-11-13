/**
 * Filter Utilities
 * Common functions and configurations for the DataFilter framework
 */

const FilterUtils = {
    
    // Common filter configurations for different portal types
    configs: {
        admin: {
            users: {
                tableId: 'users-table',
                apiEndpoint: '/admin/api/users',
                searchInputId: 'user-search',
                filtersContainerId: 'user-filters',
                paginationId: 'user-pagination',
                exportButtonId: 'export-users-btn',
                rowTemplate: (user) => `
                    <td>${user.id}</td>
                    <td>${user.full_name}</td>
                    <td>${user.email}</td>
                    <td><span class="badge badge-${user.role === 'admin' ? 'danger' : user.role === 'faculty' ? 'primary' : 'info'}">${user.role}</span></td>
                    <td>${user.institution_name || 'N/A'}</td>
                    <td><span class="badge badge-${user.is_active ? 'success' : 'secondary'}">${user.is_active ? 'Active' : 'Inactive'}</span></td>
                    <td>${FilterUtils.formatDate(user.created_at)}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="/admin/users.php?edit=${user.id}" class="btn btn-outline-primary btn-sm">Edit</a>
                            <button class="btn btn-outline-danger btn-sm" data-confirm="Are you sure you want to delete this user?" onclick="deleteUser(${user.id})">Delete</button>
                        </div>
                    </td>
                `
            },
            
            enquiries: {
                tableId: 'enquiries-table',
                apiEndpoint: '/admin/api/enquiries',
                searchInputId: 'enquiry-search',
                filtersContainerId: 'enquiry-filters',
                paginationId: 'enquiry-pagination',
                exportButtonId: 'export-enquiries-btn',
                rowTemplate: (enquiry) => `
                    <td>${enquiry.id}</td>
                    <td>${enquiry.full_name}</td>
                    <td>${enquiry.phone}</td>
                    <td>${enquiry.email || 'N/A'}</td>
                    <td>${enquiry.course_interest || 'N/A'}</td>
                    <td><span class="badge badge-${FilterUtils.getStatusBadgeClass(enquiry.status)}">${enquiry.status}</span></td>
                    <td>${enquiry.assigned_to_name || 'Unassigned'}</td>
                    <td>${FilterUtils.formatDate(enquiry.created_at)}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="/admin/enquiries.php?view=${enquiry.id}" class="btn btn-outline-info btn-sm">View</a>
                            <button class="btn btn-outline-primary btn-sm" onclick="assignEnquiry(${enquiry.id})">Assign</button>
                        </div>
                    </td>
                `
            },
            
            students: {
                tableId: 'students-table',
                apiEndpoint: '/admin/api/students',
                searchInputId: 'student-search',
                filtersContainerId: 'student-filters',
                paginationId: 'student-pagination',
                exportButtonId: 'export-students-btn',
                rowTemplate: (student) => `
                    <td>${student.student_id}</td>
                    <td>${student.full_name}</td>
                    <td>${student.institution_name}</td>
                    <td>${student.course}</td>
                    <td>${student.batch_year}</td>
                    <td><span class="badge badge-${student.is_exam_eligible ? 'success' : 'warning'}">${student.is_exam_eligible ? 'Eligible' : 'Not Eligible'}</span></td>
                    <td>${FilterUtils.formatDate(student.created_at)}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="/admin/students.php?view=${student.id}" class="btn btn-outline-info btn-sm">View</a>
                            <button class="btn btn-outline-primary btn-sm" onclick="toggleEligibility(${student.id})">${student.is_exam_eligible ? 'Remove' : 'Grant'} Eligibility</button>
                        </div>
                    </td>
                `
            },
            
            certificates: {
                tableId: 'certificates-table',
                apiEndpoint: '/admin/api/certificates',
                searchInputId: 'certificate-search',
                filtersContainerId: 'certificate-filters',
                paginationId: 'certificate-pagination',
                exportButtonId: 'export-certificates-btn',
                rowTemplate: (cert) => `
                    <td>${cert.id}</td>
                    <td>${cert.student_name}</td>
                    <td>${cert.student_id}</td>
                    <td>${cert.institution_name}</td>
                    <td>${cert.request_type}</td>
                    <td><span class="badge badge-${FilterUtils.getCertificateStatusBadgeClass(cert.status)}">${cert.status}</span></td>
                    <td>${FilterUtils.formatDate(cert.requested_at)}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            ${cert.status === 'pending' ? `
                                <button class="btn btn-outline-success btn-sm" onclick="approveCertificate(${cert.id})">Approve</button>
                                <button class="btn btn-outline-danger btn-sm" onclick="rejectCertificate(${cert.id})">Reject</button>
                            ` : cert.status === 'approved' ? `
                                <a href="/admin/certificates.php?download=${cert.id}" class="btn btn-outline-primary btn-sm">Download</a>
                            ` : ''}
                        </div>
                    </td>
                `
            }
        },
        
        faculty: {
            students: {
                tableId: 'students-table',
                apiEndpoint: '/institution/api/students',
                searchInputId: 'student-search',
                filtersContainerId: 'student-filters',
                paginationId: 'student-pagination',
                exportButtonId: 'export-students-btn',
                rowTemplate: (student) => `
                    <td>${student.student_id}</td>
                    <td>${student.full_name}</td>
                    <td>${student.course}</td>
                    <td>${student.batch_year}</td>
                    <td><span class="badge badge-${student.is_exam_eligible ? 'success' : 'warning'}">${student.is_exam_eligible ? 'Eligible' : 'Not Eligible'}</span></td>
                    <td>${FilterUtils.formatDate(student.created_at)}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="/institution/students.php?edit=${student.id}" class="btn btn-outline-primary btn-sm">Edit</a>
                            <button class="btn btn-outline-info btn-sm" onclick="toggleEligibility(${student.id})">${student.is_exam_eligible ? 'Remove' : 'Grant'} Eligibility</button>
                        </div>
                    </td>
                `
            },
            
            marklists: {
                tableId: 'marklists-table',
                apiEndpoint: '/institution/api/marklists',
                searchInputId: 'marklist-search',
                filtersContainerId: 'marklist-filters',
                paginationId: 'marklist-pagination',
                exportButtonId: 'export-marklists-btn',
                rowTemplate: (marklist) => `
                    <td>${marklist.student_name}</td>
                    <td>${marklist.student_id}</td>
                    <td>${marklist.subject}</td>
                    <td>${marklist.marks_obtained}/${marklist.total_marks}</td>
                    <td><span class="badge badge-${FilterUtils.getGradeBadgeClass(marklist.grade)}">${marklist.grade}</span></td>
                    <td>${FilterUtils.formatDate(marklist.exam_date)}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary btn-sm" onclick="editMarklist(${marklist.id})">Edit</button>
                            <button class="btn btn-outline-danger btn-sm" data-confirm="Are you sure you want to delete this marklist?" onclick="deleteMarklist(${marklist.id})">Delete</button>
                        </div>
                    </td>
                `
            },
            
            certificates: {
                tableId: 'certificates-table',
                apiEndpoint: '/institution/api/certificates',
                searchInputId: 'certificate-search',
                filtersContainerId: 'certificate-filters',
                paginationId: 'certificate-pagination',
                exportButtonId: 'export-certificates-btn',
                rowTemplate: (cert) => `
                    <td>${cert.student_name}</td>
                    <td>${cert.student_id}</td>
                    <td>${cert.request_type}</td>
                    <td><span class="badge badge-${FilterUtils.getCertificateStatusBadgeClass(cert.status)}">${cert.status}</span></td>
                    <td>${FilterUtils.formatDate(cert.requested_at)}</td>
                    <td>${cert.processed_at ? FilterUtils.formatDate(cert.processed_at) : 'N/A'}</td>
                    <td>
                        ${cert.status === 'approved' ? `
                            <a href="/institution/certificates.php?download=${cert.id}" class="btn btn-outline-primary btn-sm">Download</a>
                        ` : cert.status === 'pending' ? `
                            <span class="text-muted">Pending Review</span>
                        ` : `
                            <span class="text-danger">Rejected</span>
                        `}
                    </td>
                `
            }
        },
        
        enquirer: {
            enquiries: {
                tableId: 'enquiries-table',
                apiEndpoint: '/enquiry/api/enquiries',
                searchInputId: 'enquiry-search',
                filtersContainerId: 'enquiry-filters',
                paginationId: 'enquiry-pagination',
                exportButtonId: 'export-enquiries-btn',
                rowTemplate: (enquiry) => `
                    <td>${enquiry.full_name}</td>
                    <td>${enquiry.phone}</td>
                    <td>${enquiry.course_interest || 'N/A'}</td>
                    <td><span class="badge badge-${FilterUtils.getStatusBadgeClass(enquiry.status)}">${enquiry.status}</span></td>
                    <td>${enquiry.last_contact ? FilterUtils.formatDate(enquiry.last_contact) : 'Never'}</td>
                    <td>${FilterUtils.formatDate(enquiry.created_at)}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="/enquiry/update_status.php?id=${enquiry.id}" class="btn btn-outline-primary btn-sm">Update</a>
                            <button class="btn btn-outline-success btn-sm" onclick="makeCall('${enquiry.phone}')">Call</button>
                        </div>
                    </td>
                `
            },
            
            callLogs: {
                tableId: 'call-logs-table',
                apiEndpoint: '/enquiry/api/call-logs',
                searchInputId: 'call-search',
                filtersContainerId: 'call-filters',
                paginationId: 'call-pagination',
                exportButtonId: 'export-calls-btn',
                rowTemplate: (call) => `
                    <td>${call.enquiry_name}</td>
                    <td>${call.phone}</td>
                    <td><span class="badge badge-${FilterUtils.getCallOutcomeBadgeClass(call.call_outcome)}">${call.call_outcome}</span></td>
                    <td>${FilterUtils.formatDuration(call.call_duration)}</td>
                    <td><span class="badge badge-${call.follow_up_required ? 'warning' : 'secondary'}">${call.follow_up_required ? 'Yes' : 'No'}</span></td>
                    <td>${FilterUtils.formatDate(call.call_timestamp)}</td>
                    <td>
                        ${call.notes ? `
                            <button class="btn btn-outline-info btn-sm" onclick="showNotes('${call.notes}')">View Notes</button>
                        ` : ''}
                    </td>
                `
            }
        }
    },
    
    // Quick filter configurations
    quickFilters: {
        admin: {
            users: [
                { label: 'Active Users', filters: { is_active: '1' } },
                { label: 'Inactive Users', filters: { is_active: '0' } },
                { label: 'Admin Users', filters: { role: 'admin' } },
                { label: 'Faculty Users', filters: { role: 'faculty' } },
                { label: 'Enquirer Users', filters: { role: 'enquirer' } },
                { label: 'Recent Registrations', filters: { date_range: 'last_week' } }
            ],
            enquiries: [
                { label: 'New Enquiries', filters: { status: 'new' } },
                { label: 'Assigned', filters: { status: 'assigned' } },
                { label: 'Contacted', filters: { status: 'contacted' } },
                { label: 'Interested', filters: { status: 'interested' } },
                { label: 'Unassigned', filters: { assigned_to_user_id: 'null' } },
                { label: 'Today\'s Enquiries', filters: { date_range: 'today' } }
            ],
            students: [
                { label: 'Exam Eligible', filters: { is_exam_eligible: '1' } },
                { label: 'Not Eligible', filters: { is_exam_eligible: '0' } },
                { label: 'Current Batch', filters: { batch_year: new Date().getFullYear().toString() } },
                { label: 'Recent Registrations', filters: { date_range: 'last_month' } }
            ],
            certificates: [
                { label: 'Pending Approval', filters: { status: 'pending' } },
                { label: 'Approved', filters: { status: 'approved' } },
                { label: 'Rejected', filters: { status: 'rejected' } },
                { label: 'Today\'s Requests', filters: { date_range: 'today' } }
            ]
        },
        enquirer: {
            enquiries: [
                { label: 'High Priority', filters: { priority: 'high' } },
                { label: 'New Leads', filters: { status: 'new' } },
                { label: 'Interested', filters: { status: 'interested' } },
                { label: 'Follow-up Required', filters: { follow_up_required: '1' } },
                { label: 'Overdue Calls', filters: { overdue: '1' } }
            ],
            callLogs: [
                { label: 'Successful Calls', filters: { call_outcome: 'answered' } },
                { label: 'Missed Calls', filters: { call_outcome: 'no_answer' } },
                { label: 'Follow-up Required', filters: { follow_up_required: '1' } },
                { label: 'Today\'s Calls', filters: { date_range: 'today' } }
            ]
        }
    },
    
    // Utility functions
    formatDate: function(dateString, includeTime = true) {
        if (!dateString) return 'N/A';
        
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
    
    formatDuration: function(seconds) {
        if (!seconds || seconds === 0) return '0s';
        
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        
        if (hours > 0) {
            return `${hours}h ${minutes}m ${secs}s`;
        } else if (minutes > 0) {
            return `${minutes}m ${secs}s`;
        } else {
            return `${secs}s`;
        }
    },
    
    getStatusBadgeClass: function(status) {
        const statusClasses = {
            'new': 'primary',
            'assigned': 'info',
            'contacted': 'warning',
            'interested': 'success',
            'not_interested': 'secondary',
            'enrolled': 'success'
        };
        return statusClasses[status] || 'secondary';
    },
    
    getCertificateStatusBadgeClass: function(status) {
        const statusClasses = {
            'pending': 'warning',
            'approved': 'success',
            'rejected': 'danger'
        };
        return statusClasses[status] || 'secondary';
    },
    
    getCallOutcomeBadgeClass: function(outcome) {
        const outcomeClasses = {
            'answered': 'success',
            'no_answer': 'warning',
            'busy': 'info',
            'invalid': 'danger'
        };
        return outcomeClasses[outcome] || 'secondary';
    },
    
    getGradeBadgeClass: function(grade) {
        if (!grade) return 'secondary';
        
        const gradeUpper = grade.toUpperCase();
        if (['A+', 'A'].includes(gradeUpper)) return 'success';
        if (['B+', 'B'].includes(gradeUpper)) return 'primary';
        if (['C+', 'C'].includes(gradeUpper)) return 'info';
        if (['D+', 'D'].includes(gradeUpper)) return 'warning';
        return 'danger';
    },
    
    // Initialize filter for a specific page
    initializeFilter: function(portal, page, customConfig = {}) {
        const config = this.configs[portal] && this.configs[portal][page];
        if (!config) {
            console.error(`Filter configuration not found for ${portal}/${page}`);
            return null;
        }
        
        const finalConfig = { ...config, ...customConfig };
        return new DataFilter(finalConfig);
    },
    
    // Create quick filter buttons
    createQuickFilterButtons: function(portal, page, containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        const filters = this.quickFilters[portal] && this.quickFilters[portal][page];
        if (!filters) return;
        
        const buttonsHTML = filters.map(filter => 
            `<button class="quick-filter-btn" data-quick-filter='${JSON.stringify(filter.filters)}'>${filter.label}</button>`
        ).join('');
        
        container.innerHTML = buttonsHTML;
    },
    
    // Create filter form HTML
    createFilterForm: function(portal, page, filters) {
        let html = '<div class="filter-row">';
        
        // Search input
        html += `
            <div class="search-container">
                <input type="text" id="${page}-search" class="search-input" placeholder="Search...">
                <i class="fas fa-search search-icon"></i>
            </div>
        `;
        
        // Filter controls
        filters.forEach(filter => {
            html += `<div class="filter-group">`;
            html += `<label for="${filter.id}">${filter.label}</label>`;
            
            switch (filter.type) {
                case 'select':
                    html += `<select id="${filter.id}" data-filter="${filter.name}">`;
                    html += `<option value="">All ${filter.label}</option>`;
                    filter.options.forEach(option => {
                        html += `<option value="${option.value}">${option.label}</option>`;
                    });
                    html += `</select>`;
                    break;
                    
                case 'date':
                    html += `<input type="date" id="${filter.id}" data-filter="${filter.name}">`;
                    break;
                    
                case 'range':
                    html += `<input type="range" id="${filter.id}" data-filter="${filter.name}" min="${filter.min}" max="${filter.max}" value="${filter.default}" data-output="${filter.id}-output">`;
                    html += `<div class="range-output" id="${filter.id}-output">${filter.default}</div>`;
                    break;
            }
            
            html += `</div>`;
        });
        
        html += '</div>';
        
        // Filter actions
        html += `
            <div class="filter-actions">
                <button class="btn-filter btn-primary" id="apply-filters">Apply Filters</button>
                <button class="btn-filter btn-outline" id="clear-filters">Clear All</button>
                <button class="btn-filter btn-secondary" id="export-${page}-btn">Export</button>
            </div>
        `;
        
        return html;
    }
};

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FilterUtils;
}

// Make available globally
window.FilterUtils = FilterUtils;