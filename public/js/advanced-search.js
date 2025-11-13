/**
 * Advanced Search Component
 * Provides comprehensive search functionality across all data types
 */

class AdvancedSearch {
    constructor(config) {
        this.config = {
            containerId: config.containerId || 'advanced-search',
            apiEndpoint: config.apiEndpoint || '/api/search.php',
            exportEndpoint: config.exportEndpoint || '/api/export.php',
            enableGlobalSearch: config.enableGlobalSearch !== false,
            enableTypeFilters: config.enableTypeFilters !== false,
            enableExport: config.enableExport !== false,
            maxResults: config.maxResults || 100,
            ...config
        };
        
        this.searchHistory = this.loadSearchHistory();
        this.currentResults = [];
        this.selectedDataTypes = [];
        this.activeFilters = {};
        this.debounceTimer = null;
        
        this.init();
    }
    
    init() {
        this.createSearchInterface();
        this.setupEventListeners();
        this.loadDataTypeOptions();
    }
    
    createSearchInterface() {
        const container = document.getElementById(this.config.containerId);
        if (!container) {
            console.error('Advanced search container not found');
            return;
        }
        
        container.innerHTML = `
            <div class="advanced-search-container">
                <div class="search-header">
                    <h3>Advanced Search</h3>
                    <button class="toggle-search-options" id="toggle-options">
                        <i class="fas fa-cog"></i> Options
                    </button>
                </div>
                
                <div class="search-main">
                    <div class="search-input-container">
                        <input type="text" id="global-search-input" class="search-input" 
                               placeholder="Search across all data..." autocomplete="off">
                        <button class="search-button" id="search-button">
                            <i class="fas fa-search"></i>
                        </button>
                        <button class="clear-search" id="clear-search" style="display: none;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="search-suggestions" id="search-suggestions" style="display: none;"></div>
                </div>
                
                <div class="search-options" id="search-options" style="display: none;">
                    <div class="options-row">
                        <div class="data-type-filters">
                            <label>Search in:</label>
                            <div class="checkbox-group" id="data-type-checkboxes">
                                <!-- Data type checkboxes will be populated here -->
                            </div>
                        </div>
                        
                        <div class="search-settings">
                            <label>
                                <input type="checkbox" id="include-highlights" checked>
                                Highlight matches
                            </label>
                            <label>
                                <input type="checkbox" id="group-by-type" checked>
                                Group by type
                            </label>
                        </div>
                    </div>
                    
                    <div class="search-history">
                        <label>Recent searches:</label>
                        <div class="history-items" id="search-history-items"></div>
                    </div>
                </div>
                
                <div class="search-results" id="search-results" style="display: none;">
                    <div class="results-header">
                        <div class="results-info">
                            <span class="results-count" id="results-count">0 results</span>
                            <span class="search-time" id="search-time"></span>
                        </div>
                        <div class="results-actions">
                            <button class="export-results" id="export-results" style="display: none;">
                                <i class="fas fa-download"></i> Export Results
                            </button>
                        </div>
                    </div>
                    
                    <div class="results-content" id="results-content">
                        <!-- Search results will be displayed here -->
                    </div>
                    
                    <div class="results-pagination" id="results-pagination" style="display: none;">
                        <!-- Pagination controls -->
                    </div>
                </div>
                
                <div class="search-loading" id="search-loading" style="display: none;">
                    <div class="loading-spinner"></div>
                    <span>Searching...</span>
                </div>
            </div>
        `;
    }
    
    setupEventListeners() {
        const searchInput = document.getElementById('global-search-input');
        const searchButton = document.getElementById('search-button');
        const clearButton = document.getElementById('clear-search');
        const toggleOptions = document.getElementById('toggle-options');
        const exportButton = document.getElementById('export-results');
        
        // Search input events
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.trim();
            
            if (query.length > 0) {
                clearButton.style.display = 'block';
                
                // Show suggestions after delay
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    if (query.length >= 2) {
                        this.showSuggestions(query);
                    }
                }, 300);
            } else {
                clearButton.style.display = 'none';
                this.hideSuggestions();
            }
        });
        
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.performSearch();
            }
        });
        
        searchInput.addEventListener('focus', () => {
            const query = searchInput.value.trim();
            if (query.length >= 2) {
                this.showSuggestions(query);
            }
        });
        
        // Search button
        searchButton.addEventListener('click', () => {
            this.performSearch();
        });
        
        // Clear button
        clearButton.addEventListener('click', () => {
            this.clearSearch();
        });
        
        // Toggle options
        toggleOptions.addEventListener('click', () => {
            this.toggleSearchOptions();
        });
        
        // Export button
        if (exportButton) {
            exportButton.addEventListener('click', () => {
                this.showExportModal();
            });
        }
        
        // Data type checkboxes
        document.addEventListener('change', (e) => {
            if (e.target.matches('#data-type-checkboxes input[type="checkbox"]')) {
                this.updateSelectedDataTypes();
            }
        });
        
        // Click outside to hide suggestions
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.search-input-container') && 
                !e.target.closest('.search-suggestions')) {
                this.hideSuggestions();
            }
        });
    }
    
    async loadDataTypeOptions() {
        try {
            const response = await fetch(`${this.config.apiEndpoint}?action=stats`);
            const result = await response.json();
            
            if (result.success) {
                this.populateDataTypeCheckboxes(result.stats);
            }
        } catch (error) {
            console.error('Failed to load data type options:', error);
        }
    }
    
    populateDataTypeCheckboxes(stats) {
        const container = document.getElementById('data-type-checkboxes');
        if (!container) return;
        
        const dataTypes = [
            { key: 'users', label: 'Users', icon: 'fa-users' },
            { key: 'enquiries', label: 'Enquiries', icon: 'fa-phone' },
            { key: 'students', label: 'Students', icon: 'fa-graduation-cap' },
            { key: 'institutions', label: 'Institutions', icon: 'fa-building' },
            { key: 'certificates', label: 'Certificates', icon: 'fa-certificate' },
            { key: 'marklists', label: 'Marklists', icon: 'fa-list-alt' },
            { key: 'call_logs', label: 'Call Logs', icon: 'fa-phone-alt' }
        ];
        
        container.innerHTML = dataTypes.map(type => {
            const count = stats[type.key]?.total_records || 0;
            return `
                <label class="data-type-option">
                    <input type="checkbox" value="${type.key}" checked>
                    <i class="fas ${type.icon}"></i>
                    <span>${type.label}</span>
                    <small>(${count.toLocaleString()})</small>
                </label>
            `;
        }).join('');
        
        this.updateSelectedDataTypes();
    }
    
    updateSelectedDataTypes() {
        const checkboxes = document.querySelectorAll('#data-type-checkboxes input[type="checkbox"]:checked');
        this.selectedDataTypes = Array.from(checkboxes).map(cb => cb.value);
    }
    
    async showSuggestions(query) {
        try {
            const response = await fetch(this.config.apiEndpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'suggestions',
                    query: query,
                    data_types: this.selectedDataTypes,
                    limit: 8
                })
            });
            
            const result = await response.json();
            
            if (result.success && result.suggestions.length > 0) {
                this.displaySuggestions(result.suggestions);
            } else {
                this.hideSuggestions();
            }
        } catch (error) {
            console.error('Failed to get suggestions:', error);
            this.hideSuggestions();
        }
    }
    
    displaySuggestions(suggestions) {
        const container = document.getElementById('search-suggestions');
        if (!container) return;
        
        container.innerHTML = suggestions.map(suggestion => `
            <div class="suggestion-item" data-text="${suggestion.text}" data-type="${suggestion.type}">
                <div class="suggestion-text">${this.highlightSuggestion(suggestion.text)}</div>
                <div class="suggestion-meta">
                    <span class="suggestion-type">${suggestion.type}</span>
                    <span class="suggestion-field">${suggestion.field}</span>
                </div>
            </div>
        `).join('');
        
        container.style.display = 'block';
        
        // Add click handlers
        container.querySelectorAll('.suggestion-item').forEach(item => {
            item.addEventListener('click', () => {
                const text = item.dataset.text;
                document.getElementById('global-search-input').value = text;
                this.hideSuggestions();
                this.performSearch();
            });
        });
    }
    
    highlightSuggestion(text) {
        const query = document.getElementById('global-search-input').value.trim();
        if (!query) return text;
        
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<mark>$1</mark>');
    }
    
    hideSuggestions() {
        const container = document.getElementById('search-suggestions');
        if (container) {
            container.style.display = 'none';
        }
    }
    
    async performSearch() {
        const query = document.getElementById('global-search-input').value.trim();
        
        if (query.length < 2) {
            alert('Please enter at least 2 characters to search');
            return;
        }
        
        this.showLoading();
        this.hideSuggestions();
        
        try {
            const includeHighlights = document.getElementById('include-highlights')?.checked ?? true;
            const groupByType = document.getElementById('group-by-type')?.checked ?? true;
            
            const response = await fetch(this.config.apiEndpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'search',
                    query: query,
                    data_types: this.selectedDataTypes,
                    filters: this.activeFilters,
                    options: {
                        limit: this.config.maxResults,
                        include_highlights: includeHighlights,
                        group_by_type: groupByType,
                        min_score: 0.1
                    }
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.displayResults(result);
                this.addToSearchHistory(query);
            } else {
                this.showError(result.message || 'Search failed');
            }
        } catch (error) {
            console.error('Search error:', error);
            this.showError('Search failed. Please try again.');
        } finally {
            this.hideLoading();
        }
    }
    
    displayResults(result) {
        const resultsContainer = document.getElementById('search-results');
        const resultsContent = document.getElementById('results-content');
        const resultsCount = document.getElementById('results-count');
        const searchTime = document.getElementById('search-time');
        const exportButton = document.getElementById('export-results');
        
        if (!resultsContainer || !resultsContent) return;
        
        this.currentResults = result;
        
        // Update results info
        resultsCount.textContent = `${result.total_results} results`;
        if (result.execution_time) {
            searchTime.textContent = `(${(result.execution_time * 1000).toFixed(0)}ms)`;
        }
        
        // Show/hide export button
        if (exportButton && result.total_results > 0) {
            exportButton.style.display = 'block';
        }
        
        // Display results
        if (result.total_results === 0) {
            resultsContent.innerHTML = `
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h4>No results found</h4>
                    <p>Try adjusting your search terms or filters</p>
                </div>
            `;
        } else {
            resultsContent.innerHTML = this.formatResults(result.results);
        }
        
        resultsContainer.style.display = 'block';
    }
    
    formatResults(results) {
        const groupByType = document.getElementById('group-by-type')?.checked ?? true;
        
        if (groupByType && typeof results === 'object' && !Array.isArray(results)) {
            // Results are grouped by type
            return Object.keys(results).map(type => {
                const typeResults = results[type];
                return `
                    <div class="result-group">
                        <h4 class="result-group-title">
                            ${this.getTypeIcon(type)} ${this.getTypeLabel(type)} 
                            <span class="result-count">(${typeResults.length})</span>
                        </h4>
                        <div class="result-items">
                            ${typeResults.map(item => this.formatResultItem(item)).join('')}
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            // Results are flat array
            const resultArray = Array.isArray(results) ? results : [];
            return `
                <div class="result-items">
                    ${resultArray.map(item => this.formatResultItem(item)).join('')}
                </div>
            `;
        }
    }
    
    formatResultItem(item) {
        const highlights = item._highlights || {};
        const type = item._type || 'unknown';
        const score = item._score || 0;
        
        // Get primary fields to display
        const primaryField = this.getPrimaryField(item, type);
        const secondaryField = this.getSecondaryField(item, type);
        
        return `
            <div class="result-item" data-type="${type}" data-id="${item.id}">
                <div class="result-header">
                    <div class="result-title">
                        ${highlights[primaryField] || item[primaryField] || 'N/A'}
                    </div>
                    <div class="result-meta">
                        <span class="result-type">${this.getTypeLabel(type)}</span>
                        <span class="result-score">${(score * 100).toFixed(0)}%</span>
                    </div>
                </div>
                <div class="result-content">
                    <div class="result-secondary">
                        ${highlights[secondaryField] || item[secondaryField] || ''}
                    </div>
                    ${Object.keys(highlights).length > 0 ? `
                        <div class="result-highlights">
                            ${Object.entries(highlights).slice(0, 2).map(([field, highlight]) => 
                                `<div class="highlight-item"><strong>${field}:</strong> ${highlight}</div>`
                            ).join('')}
                        </div>
                    ` : ''}
                </div>
                <div class="result-actions">
                    <button class="view-details" onclick="viewDetails('${type}', ${item.id})">
                        View Details
                    </button>
                </div>
            </div>
        `;
    }
    
    getPrimaryField(item, type) {
        const primaryFields = {
            'users': 'full_name',
            'enquiries': 'full_name',
            'students': 'full_name',
            'institutions': 'name',
            'certificates': 'student_name',
            'marklists': 'student_name',
            'call_logs': 'enquiry_name'
        };
        
        return primaryFields[type] || Object.keys(item)[0];
    }
    
    getSecondaryField(item, type) {
        const secondaryFields = {
            'users': 'email',
            'enquiries': 'phone',
            'students': 'student_id',
            'institutions': 'code',
            'certificates': 'request_type',
            'marklists': 'subject',
            'call_logs': 'phone'
        };
        
        return secondaryFields[type] || Object.keys(item)[1];
    }
    
    getTypeIcon(type) {
        const icons = {
            'users': 'fa-users',
            'enquiries': 'fa-phone',
            'students': 'fa-graduation-cap',
            'institutions': 'fa-building',
            'certificates': 'fa-certificate',
            'marklists': 'fa-list-alt',
            'call_logs': 'fa-phone-alt'
        };
        
        return `<i class="fas ${icons[type] || 'fa-file'}"></i>`;
    }
    
    getTypeLabel(type) {
        const labels = {
            'users': 'Users',
            'enquiries': 'Enquiries',
            'students': 'Students',
            'institutions': 'Institutions',
            'certificates': 'Certificates',
            'marklists': 'Marklists',
            'call_logs': 'Call Logs'
        };
        
        return labels[type] || type;
    }
    
    showExportModal() {
        if (!this.currentResults || this.currentResults.total_results === 0) {
            alert('No results to export');
            return;
        }
        
        const modal = document.createElement('div');
        modal.className = 'export-modal';
        modal.innerHTML = `
            <div class="export-modal-content">
                <div class="export-modal-header">
                    <h3>Export Search Results</h3>
                    <button class="close-modal">&times;</button>
                </div>
                <div class="export-modal-body">
                    <div class="export-info">
                        <p>Exporting ${this.currentResults.total_results} search results</p>
                        <p>Query: "${this.currentResults.query}"</p>
                    </div>
                    <div class="export-options">
                        <label>Format:</label>
                        <select id="export-format">
                            <option value="csv">CSV</option>
                            <option value="excel">Excel</option>
                            <option value="json">JSON</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                    <div class="export-settings">
                        <label>
                            <input type="checkbox" id="include-highlights" checked>
                            Include highlights
                        </label>
                        <label>
                            <input type="checkbox" id="compress-export">
                            Compress file
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
            const format = modal.querySelector('#export-format').value;
            const includeHighlights = modal.querySelector('#include-highlights').checked;
            const compress = modal.querySelector('#compress-export').checked;
            
            this.exportResults(format, { includeHighlights, compress });
            document.body.removeChild(modal);
        });
    }
    
    async exportResults(format, options) {
        try {
            // Prepare export data
            const exportData = {
                action: 'export',
                format: format,
                search_results: this.currentResults,
                options: {
                    filename: `search_results_${Date.now()}`,
                    include_highlights: options.includeHighlights,
                    compress: options.compress,
                    search_query: this.currentResults.query,
                    data_types: this.currentResults.data_types_searched
                }
            };
            
            // Create form for file download
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = this.config.exportEndpoint;
            form.style.display = 'none';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'export_data';
            input.value = JSON.stringify(exportData);
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
            
        } catch (error) {
            console.error('Export error:', error);
            alert('Export failed. Please try again.');
        }
    }
    
    toggleSearchOptions() {
        const options = document.getElementById('search-options');
        if (options) {
            const isVisible = options.style.display !== 'none';
            options.style.display = isVisible ? 'none' : 'block';
        }
    }
    
    clearSearch() {
        document.getElementById('global-search-input').value = '';
        document.getElementById('clear-search').style.display = 'none';
        document.getElementById('search-results').style.display = 'none';
        this.hideSuggestions();
        this.currentResults = [];
    }
    
    showLoading() {
        const loading = document.getElementById('search-loading');
        if (loading) {
            loading.style.display = 'block';
        }
    }
    
    hideLoading() {
        const loading = document.getElementById('search-loading');
        if (loading) {
            loading.style.display = 'none';
        }
    }
    
    showError(message) {
        const resultsContainer = document.getElementById('search-results');
        const resultsContent = document.getElementById('results-content');
        
        if (resultsContainer && resultsContent) {
            resultsContent.innerHTML = `
                <div class="search-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h4>Search Error</h4>
                    <p>${message}</p>
                </div>
            `;
            resultsContainer.style.display = 'block';
        }
    }
    
    addToSearchHistory(query) {
        if (!this.searchHistory.includes(query)) {
            this.searchHistory.unshift(query);
            this.searchHistory = this.searchHistory.slice(0, 10); // Keep last 10
            this.saveSearchHistory();
            this.updateSearchHistoryDisplay();
        }
    }
    
    updateSearchHistoryDisplay() {
        const container = document.getElementById('search-history-items');
        if (!container) return;
        
        container.innerHTML = this.searchHistory.map(query => `
            <button class="history-item" data-query="${query}">
                <i class="fas fa-history"></i>
                ${query}
            </button>
        `).join('');
        
        // Add click handlers
        container.querySelectorAll('.history-item').forEach(item => {
            item.addEventListener('click', () => {
                const query = item.dataset.query;
                document.getElementById('global-search-input').value = query;
                this.performSearch();
            });
        });
    }
    
    loadSearchHistory() {
        try {
            const saved = localStorage.getItem('advancedSearch_history');
            return saved ? JSON.parse(saved) : [];
        } catch (error) {
            return [];
        }
    }
    
    saveSearchHistory() {
        try {
            localStorage.setItem('advancedSearch_history', JSON.stringify(this.searchHistory));
        } catch (error) {
            console.error('Failed to save search history:', error);
        }
    }
}

// Global function for viewing details (to be implemented by each portal)
function viewDetails(type, id) {
    // This function should be implemented by each portal
    // to handle viewing details of specific records
    console.log(`View details for ${type} with ID ${id}`);
    
    // Example implementation:
    const urls = {
        'users': '/admin/users.php?view=',
        'enquiries': '/admin/enquiries.php?view=',
        'students': '/admin/students.php?view=',
        'institutions': '/admin/institutions.php?view=',
        'certificates': '/admin/certificates.php?view=',
        'marklists': '/institution/marklists.php?view=',
        'call_logs': '/enquiry/call_logs.php?view='
    };
    
    if (urls[type]) {
        window.open(urls[type] + id, '_blank');
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AdvancedSearch;
}

// Make available globally
window.AdvancedSearch = AdvancedSearch;