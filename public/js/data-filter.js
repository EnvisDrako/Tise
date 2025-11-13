/**
 * Advanced Data Filtering Framework
 * Provides real-time search, filtering, sorting, and pagination
 */
class DataFilter {
    constructor(config) {
        this.config = {
            tableId: config.tableId,
            apiEndpoint: config.apiEndpoint,
            searchInputId: config.searchInputId || 'search',
            filtersContainerId: config.filtersContainerId || 'filters',
            paginationId: config.paginationId || 'pagination',
            exportButtonId: config.exportButtonId || 'export-btn',
            ...config
        };
        
        this.filters = {};
        this.sortConfig = { column: null, direction: 'asc' };
        this.searchTerm = '';
        this.pagination = { page: 1, limit: 25 };
        this.savedFilters = this.loadSavedFilters();
        this.debounceTimer = null;
        this.isLoading = false;
        
        this.init();
    }
    
    init() {
        this.setupSearch();
        this.setupColumnSorting();
        this.setupFilterControls();
        this.setupPagination();
        this.setupExportFunctionality();
        this.setupQuickFilters();
        this.setupSavedFilters();
        this.loadData();
    }
    
    // Real-time search with debouncing
    setupSearch() {
        const searchInput = document.getElementById(this.config.searchInputId);
        if (!searchInput) return;
        
        searchInput.addEventListener('input', (e) => {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                this.searchTerm = e.target.value.trim();
                this.pagination.page = 1; // Reset to first page
                this.applyFilters();
            }, 300);
        });
        
        // Add search suggestions
        this.setupSearchSuggestions(searchInput);
    }
    
    setupSearchSuggestions(searchInput) {
        const suggestionsContainer = document.createElement('div');
        suggestionsContainer.className = 'search-suggestions';
        suggestionsContainer.style.display = 'none';
        searchInput.parentNode.appendChild(suggestionsContainer);
        
        searchInput.addEventListener('focus', () => {
            if (this.searchTerm.length > 2) {
                this.showSearchSuggestions(suggestionsContainer);
            }
        });
        
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                suggestionsContainer.style.display = 'none';
            }
        });
    }
    
    async showSearchSuggestions(container) {
        try {
            const response = await fetch(`${this.config.apiEndpoint}/suggestions`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ term: this.searchTerm })
            });
            
            const suggestions = await response.json();
            if (suggestions.success && suggestions.data.length > 0) {
                container.innerHTML = suggestions.data.map(item => 
                    `<div class="suggestion-item" data-value="${item.value}">${item.label}</div>`
                ).join('');
                container.style.display = 'block';
                
                // Add click handlers
                container.querySelectorAll('.suggestion-item').forEach(item => {
                    item.addEventListener('click', () => {
                        document.getElementById(this.config.searchInputId).value = item.dataset.value;
                        this.searchTerm = item.dataset.value;
                        container.style.display = 'none';
                        this.applyFilters();
                    });
                });
            }
        } catch (error) {
            console.error('Error fetching suggestions:', error);
        }
    }
    
    // Column sorting with visual indicators
    setupColumnSorting() {
        const table = document.getElementById(this.config.tableId);
        if (!table) return;
        
        const headers = table.querySelectorAll('th[data-sortable]');
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.innerHTML += ' <span class="sort-indicator"></span>';
            
            header.addEventListener('click', () => {
                const column = header.dataset.column;
                this.toggleSort(column);
                this.updateSortIndicators(headers, header, column);
            });
        });
    }
    
    toggleSort(column) {
        if (this.sortConfig.column === column) {
            this.sortConfig.direction = this.sortConfig.direction === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortConfig.column = column;
            this.sortConfig.direction = 'asc';
        }
        this.applyFilters();
    }
    
    updateSortIndicators(headers, activeHeader, column) {
        // Remove all sort indicators
        headers.forEach(header => {
            const indicator = header.querySelector('.sort-indicator');
            indicator.innerHTML = '';
            header.classList.remove('sort-asc', 'sort-desc');
        });
        
        // Add indicator to active header
        const indicator = activeHeader.querySelector('.sort-indicator');
        if (this.sortConfig.direction === 'asc') {
            indicator.innerHTML = ' ↑';
            activeHeader.classList.add('sort-asc');
        } else {
            indicator.innerHTML = ' ↓';
            activeHeader.classList.add('sort-desc');
        }
    }
    
    // Setup filter controls (dropdowns, date pickers, range sliders)
    setupFilterControls() {
        const filtersContainer = document.getElementById(this.config.filtersContainerId);
        if (!filtersContainer) return;
        
        // Multi-select dropdowns
        const selectFilters = filtersContainer.querySelectorAll('select[data-filter]');
        selectFilters.forEach(select => {
            select.addEventListener('change', () => {
                const filterName = select.dataset.filter;
                const value = select.value;
                
                if (value === '' || value === 'all') {
                    delete this.filters[filterName];
                } else {
                    this.filters[filterName] = value;
                }
                
                this.pagination.page = 1;
                this.applyFilters();
            });
        });
        
        // Date range pickers
        const dateFilters = filtersContainer.querySelectorAll('input[type="date"][data-filter]');
        dateFilters.forEach(input => {
            input.addEventListener('change', () => {
                const filterName = input.dataset.filter;
                const value = input.value;
                
                if (value === '') {
                    delete this.filters[filterName];
                } else {
                    this.filters[filterName] = value;
                }
                
                this.pagination.page = 1;
                this.applyFilters();
            });
        });
        
        // Range sliders
        const rangeFilters = filtersContainer.querySelectorAll('input[type="range"][data-filter]');
        rangeFilters.forEach(input => {
            const output = document.getElementById(input.dataset.output);
            
            input.addEventListener('input', () => {
                if (output) {
                    output.textContent = input.value;
                }
            });
            
            input.addEventListener('change', () => {
                const filterName = input.dataset.filter;
                const value = input.value;
                
                this.filters[filterName] = value;
                this.pagination.page = 1;
                this.applyFilters();
            });
        });
        
        // Multi-select checkboxes
        const checkboxFilters = filtersContainer.querySelectorAll('input[type="checkbox"][data-filter]');
        checkboxFilters.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const filterName = checkbox.dataset.filter;
                const value = checkbox.value;
                
                if (!this.filters[filterName]) {
                    this.filters[filterName] = [];
                }
                
                if (checkbox.checked) {
                    if (!this.filters[filterName].includes(value)) {
                        this.filters[filterName].push(value);
                    }
                } else {
                    this.filters[filterName] = this.filters[filterName].filter(v => v !== value);
                    if (this.filters[filterName].length === 0) {
                        delete this.filters[filterName];
                    }
                }
                
                this.pagination.page = 1;
                this.applyFilters();
            });
        });
    }
    
    // Setup pagination controls
    setupPagination() {
        const paginationContainer = document.getElementById(this.config.paginationId);
        if (!paginationContainer) return;
        
        // Page size selector
        const pageSizeSelect = paginationContainer.querySelector('select[data-page-size]');
        if (pageSizeSelect) {
            pageSizeSelect.addEventListener('change', () => {
                this.pagination.limit = parseInt(pageSizeSelect.value);
                this.pagination.page = 1;
                this.applyFilters();
            });
        }
    }
    
    // Setup export functionality
    setupExportFunctionality() {
        const exportButton = document.getElementById(this.config.exportButtonId);
        if (!exportButton) return;
        
        exportButton.addEventListener('click', () => {
            this.showExportModal();
        });
    }
    
    showExportModal() {
        const modal = document.createElement('div');
        modal.className = 'export-modal';
        modal.innerHTML = `
            <div class="export-modal-content">
                <div class="export-modal-header">
                    <h3>Export Data</h3>
                    <button class="close-modal">&times;</button>
                </div>
                <div class="export-modal-body">
                    <div class="export-options">
                        <label>
                            <input type="radio" name="export-format" value="csv" checked>
                            CSV (Comma Separated Values)
                        </label>
                        <label>
                            <input type="radio" name="export-format" value="excel">
                            Excel Spreadsheet
                        </label>
                        <label>
                            <input type="radio" name="export-format" value="pdf">
                            PDF Report
                        </label>
                    </div>
                    <div class="export-scope">
                        <label>
                            <input type="radio" name="export-scope" value="filtered" checked>
                            Export filtered results only
                        </label>
                        <label>
                            <input type="radio" name="export-scope" value="all">
                            Export all data
                        </label>
                    </div>
                </div>
                <div class="export-modal-footer">
                    <button class="btn btn-secondary cancel-export">Cancel</button>
                    <button class="btn btn-primary confirm-export">Export</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Event handlers
        modal.querySelector('.close-modal').addEventListener('click', () => {
            document.body.removeChild(modal);
        });
        
        modal.querySelector('.cancel-export').addEventListener('click', () => {
            document.body.removeChild(modal);
        });
        
        modal.querySelector('.confirm-export').addEventListener('click', () => {
            const format = modal.querySelector('input[name="export-format"]:checked').value;
            const scope = modal.querySelector('input[name="export-scope"]:checked').value;
            this.exportData(format, scope);
            document.body.removeChild(modal);
        });
    }
    
    async exportData(format, scope) {
        try {
            const exportParams = {
                format: format,
                scope: scope,
                filters: scope === 'filtered' ? this.filters : {},
                search: scope === 'filtered' ? this.searchTerm : '',
                sort: this.sortConfig
            };
            
            const response = await fetch(`${this.config.apiEndpoint}/export`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(exportParams)
            });
            
            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `export_${Date.now()}.${format === 'excel' ? 'xlsx' : format}`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            } else {
                throw new Error('Export failed');
            }
        } catch (error) {
            console.error('Export error:', error);
            alert('Export failed. Please try again.');
        }
    }
    
    // Setup quick filter buttons
    setupQuickFilters() {
        const quickFilters = document.querySelectorAll('[data-quick-filter]');
        quickFilters.forEach(button => {
            button.addEventListener('click', () => {
                const filterData = JSON.parse(button.dataset.quickFilter);
                this.filters = { ...this.filters, ...filterData };
                this.pagination.page = 1;
                this.updateFilterUI();
                this.applyFilters();
            });
        });
    }
    
    updateFilterUI() {
        // Update filter controls to reflect current filters
        Object.keys(this.filters).forEach(filterName => {
            const filterValue = this.filters[filterName];
            
            // Update select dropdowns
            const select = document.querySelector(`select[data-filter="${filterName}"]`);
            if (select) {
                select.value = filterValue;
            }
            
            // Update date inputs
            const dateInput = document.querySelector(`input[type="date"][data-filter="${filterName}"]`);
            if (dateInput) {
                dateInput.value = filterValue;
            }
            
            // Update range inputs
            const rangeInput = document.querySelector(`input[type="range"][data-filter="${filterName}"]`);
            if (rangeInput) {
                rangeInput.value = filterValue;
                const output = document.getElementById(rangeInput.dataset.output);
                if (output) {
                    output.textContent = filterValue;
                }
            }
            
            // Update checkboxes
            if (Array.isArray(filterValue)) {
                filterValue.forEach(value => {
                    const checkbox = document.querySelector(`input[type="checkbox"][data-filter="${filterName}"][value="${value}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
            }
        });
    }
    
    // Setup saved filters functionality
    setupSavedFilters() {
        const saveFilterButton = document.getElementById('save-filter-btn');
        const savedFiltersSelect = document.getElementById('saved-filters-select');
        const loadFilterButton = document.getElementById('load-filter-btn');
        const deleteFilterButton = document.getElementById('delete-filter-btn');
        
        if (saveFilterButton) {
            saveFilterButton.addEventListener('click', () => {
                this.showSaveFilterModal();
            });
        }
        
        if (loadFilterButton && savedFiltersSelect) {
            loadFilterButton.addEventListener('click', () => {
                const filterName = savedFiltersSelect.value;
                if (filterName) {
                    this.loadSavedFilter(filterName);
                }
            });
        }
        
        if (deleteFilterButton && savedFiltersSelect) {
            deleteFilterButton.addEventListener('click', () => {
                const filterName = savedFiltersSelect.value;
                if (filterName && confirm('Are you sure you want to delete this saved filter?')) {
                    this.deleteSavedFilter(filterName);
                }
            });
        }
        
        this.updateSavedFiltersSelect();
    }
    
    showSaveFilterModal() {
        const filterName = prompt('Enter a name for this filter:');
        if (filterName) {
            this.saveFilter(filterName);
        }
    }
    
    saveFilter(name) {
        const filterData = {
            filters: this.filters,
            search: this.searchTerm,
            sort: this.sortConfig
        };
        
        this.savedFilters[name] = filterData;
        localStorage.setItem('dataFilter_savedFilters', JSON.stringify(this.savedFilters));
        this.updateSavedFiltersSelect();
    }
    
    loadSavedFilter(name) {
        const filterData = this.savedFilters[name];
        if (filterData) {
            this.filters = filterData.filters || {};
            this.searchTerm = filterData.search || '';
            this.sortConfig = filterData.sort || { column: null, direction: 'asc' };
            
            // Update UI
            document.getElementById(this.config.searchInputId).value = this.searchTerm;
            this.updateFilterUI();
            this.applyFilters();
        }
    }
    
    deleteSavedFilter(name) {
        delete this.savedFilters[name];
        localStorage.setItem('dataFilter_savedFilters', JSON.stringify(this.savedFilters));
        this.updateSavedFiltersSelect();
    }
    
    loadSavedFilters() {
        const saved = localStorage.getItem('dataFilter_savedFilters');
        return saved ? JSON.parse(saved) : {};
    }
    
    updateSavedFiltersSelect() {
        const select = document.getElementById('saved-filters-select');
        if (!select) return;
        
        select.innerHTML = '<option value="">Select a saved filter...</option>';
        Object.keys(this.savedFilters).forEach(name => {
            const option = document.createElement('option');
            option.value = name;
            option.textContent = name;
            select.appendChild(option);
        });
    }
    
    // Apply all filters and load data
    async applyFilters() {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.showLoading();
        
        try {
            const params = {
                filters: this.filters,
                search: this.searchTerm,
                sort: this.sortConfig,
                page: this.pagination.page,
                limit: this.pagination.limit
            };
            
            const response = await fetch(this.config.apiEndpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(params)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.updateTable(result.data);
                this.updatePagination(result.pagination);
                this.updateResultsCount(result.pagination.total);
            } else {
                throw new Error(result.message || 'Failed to load data');
            }
        } catch (error) {
            console.error('Filter error:', error);
            this.showError('Failed to load data. Please try again.');
        } finally {
            this.isLoading = false;
            this.hideLoading();
        }
    }
    
    // Load initial data
    async loadData() {
        await this.applyFilters();
    }
    
    // Update table with new data
    updateTable(data) {
        const table = document.getElementById(this.config.tableId);
        const tbody = table.querySelector('tbody');
        
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="100%" class="text-center">No data found</td></tr>';
            return;
        }
        
        data.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = this.config.rowTemplate(row);
            tbody.appendChild(tr);
        });
    }
    
    // Update pagination controls
    updatePagination(paginationData) {
        const container = document.getElementById(this.config.paginationId);
        if (!container) return;
        
        const { page, totalPages, total, limit } = paginationData;
        
        let paginationHTML = '<nav><ul class="pagination">';
        
        // Previous button
        if (page > 1) {
            paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${page - 1}">Previous</a></li>`;
        } else {
            paginationHTML += '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
        }
        
        // Page numbers
        const startPage = Math.max(1, page - 2);
        const endPage = Math.min(totalPages, page + 2);
        
        if (startPage > 1) {
            paginationHTML += '<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>';
            if (startPage > 2) {
                paginationHTML += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            if (i === page) {
                paginationHTML += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
            } else {
                paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
            }
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationHTML += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
        }
        
        // Next button
        if (page < totalPages) {
            paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${page + 1}">Next</a></li>`;
        } else {
            paginationHTML += '<li class="page-item disabled"><span class="page-link">Next</span></li>';
        }
        
        paginationHTML += '</ul></nav>';
        
        container.innerHTML = paginationHTML;
        
        // Add click handlers
        container.querySelectorAll('a[data-page]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.pagination.page = parseInt(link.dataset.page);
                this.applyFilters();
            });
        });
    }
    
    // Update results count display
    updateResultsCount(total) {
        const countElement = document.getElementById('results-count');
        if (countElement) {
            const start = (this.pagination.page - 1) * this.pagination.limit + 1;
            const end = Math.min(this.pagination.page * this.pagination.limit, total);
            countElement.textContent = `Showing ${start}-${end} of ${total} results`;
        }
    }
    
    // Show loading indicator
    showLoading() {
        const table = document.getElementById(this.config.tableId);
        const tbody = table.querySelector('tbody');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="100%" class="text-center"><div class="loading-spinner"></div> Loading...</td></tr>';
        }
    }
    
    // Hide loading indicator
    hideLoading() {
        // Loading will be hidden when updateTable is called
    }
    
    // Show error message
    showError(message) {
        const table = document.getElementById(this.config.tableId);
        const tbody = table.querySelector('tbody');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="100%" class="text-center text-danger">${message}</td></tr>`;
        }
    }
    
    // Clear all filters
    clearFilters() {
        this.filters = {};
        this.searchTerm = '';
        this.sortConfig = { column: null, direction: 'asc' };
        this.pagination.page = 1;
        
        // Reset UI
        document.getElementById(this.config.searchInputId).value = '';
        
        // Reset filter controls
        const filtersContainer = document.getElementById(this.config.filtersContainerId);
        if (filtersContainer) {
            filtersContainer.querySelectorAll('select').forEach(select => {
                select.value = '';
            });
            filtersContainer.querySelectorAll('input[type="date"]').forEach(input => {
                input.value = '';
            });
            filtersContainer.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });
        }
        
        this.applyFilters();
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DataFilter;
}