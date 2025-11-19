// Public website JavaScript functionality

document.addEventListener('DOMContentLoaded', function() {
    // Mobile navigation toggle
    const navToggle = document.getElementById('nav-toggle');
    const navMenu = document.getElementById('nav-menu');

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            navToggle.classList.toggle('active');
        });

        // Close mobile menu when clicking on a link
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
                navToggle.classList.remove('active');
            });
        });
    }

    // Smooth scrolling for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                    showFieldError(field, 'This field is required');
                } else {
                    field.classList.remove('error');
                    hideFieldError(field);
                }

                // Email validation
                if (field.type === 'email' && field.value.trim()) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(field.value)) {
                        isValid = false;
                        field.classList.add('error');
                        showFieldError(field, 'Please enter a valid email address');
                    }
                }

                // Phone validation
                if (field.type === 'tel' && field.value.trim()) {
                    const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
                    if (!phoneRegex.test(field.value.replace(/\s/g, ''))) {
                        isValid = false;
                        field.classList.add('error');
                        showFieldError(field, 'Please enter a valid phone number');
                    }
                }
            });

            if (!isValid) {
                e.preventDefault();
            }
        });
    });

    // Filter functionality for pages with filters
    initializeFilters();

    // Search functionality
    initializeSearch();

    // Image lazy loading
    initializeLazyLoading();
});

function showFieldError(field, message) {
    hideFieldError(field);
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
}

function hideFieldError(field) {
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}

function initializeFilters() {
    const filterSelects = document.querySelectorAll('.filter-select');
    const filterableItems = document.querySelectorAll('.filterable-item');

    if (filterSelects.length === 0 || filterableItems.length === 0) return;

    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            if (window.applyCombinedFilters) {
                window.applyCombinedFilters();
            } else {
                applyFilters();
            }
        });
    });

    function applyFilters() {
        const activeFilters = {};
        
        filterSelects.forEach(select => {
            if (select.value && select.value !== 'all') {
                activeFilters[select.dataset.filter] = select.value;
            }
        });

        filterableItems.forEach(item => {
            let shouldShow = true;

            Object.keys(activeFilters).forEach(filterType => {
                const itemValue = item.dataset[filterType];
                
                // Handle different filter types
                if (filterType === 'feeRange') {
                    shouldShow = shouldShow && checkFeeRange(item, activeFilters[filterType]);
                } else if (filterType === 'dateRange') {
                    shouldShow = shouldShow && checkDateRange(item, activeFilters[filterType]);
                } else if (filterType === 'tags') {
                    shouldShow = shouldShow && checkTags(item, activeFilters[filterType]);
                } else {
                    // Standard exact match filtering
                    if (itemValue && itemValue !== activeFilters[filterType]) {
                        shouldShow = false;
                    }
                }
            });

            item.style.display = shouldShow ? 'block' : 'none';
        });

        updateResultsCount();
    }

    function checkFeeRange(item, rangeFilter) {
        const feeText = item.querySelector('.detail-item:has(i.fa-dollar-sign) span')?.textContent;
        if (!feeText) return true;
        
        const fee = parseInt(feeText.replace(/[^0-9]/g, ''));
        
        switch (rangeFilter) {
            case 'low':
                return fee < 500;
            case 'medium':
                return fee >= 500 && fee <= 1000;
            case 'high':
                return fee > 1000;
            default:
                return true;
        }
    }

    function checkDateRange(item, rangeFilter) {
        const dateElement = item.querySelector('.blog-date, .graduation-info, .gallery-meta .date');
        if (!dateElement) return true;
        
        const dateText = dateElement.textContent;
        const currentYear = new Date().getFullYear();
        const currentMonth = new Date().getMonth();
        
        switch (rangeFilter) {
            case 'recent':
                // Check if within last 3 months
                return dateText.includes(currentYear.toString()) && 
                       (dateText.includes('December') || dateText.includes('November') || dateText.includes('October'));
            case '2024':
                return dateText.includes('2024');
            case '2023':
                return dateText.includes('2023');
            case '2022':
                return dateText.includes('2022');
            case '2021':
                return dateText.includes('2021');
            case '2020':
                return dateText.includes('2020');
            default:
                return true;
        }
    }

    function checkTags(item, tagFilter) {
        const tagElements = item.querySelectorAll('.tag, .feature');
        const itemTags = Array.from(tagElements).map(tag => tag.textContent.toLowerCase());
        
        return itemTags.some(tag => tag.includes(tagFilter.toLowerCase()));
    }

    function updateResultsCount() {
        const visibleItems = document.querySelectorAll('.filterable-item[style="display: block"], .filterable-item:not([style*="display: none"])');
        const countElement = document.querySelector('.results-count');
        if (countElement) {
            const itemType = getItemType();
            countElement.textContent = `Showing ${visibleItems.length} ${itemType}`;
        }
    }

    function getItemType() {
        if (document.querySelector('.course-card')) return 'courses';
        if (document.querySelector('.testimonial-card')) return 'testimonials';
        if (document.querySelector('.gallery-item')) return 'media items';
        if (document.querySelector('.blog-card')) return 'articles';
        return 'results';
    }

    // Initialize with all items visible
    updateResultsCount();
}

function initializeSearch() {
    const searchInput = document.querySelector('.search-input');
    const searchableItems = document.querySelectorAll('.searchable-item');

    if (!searchInput || searchableItems.length === 0) return;

    let searchTimeout;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applyCombinedFilters();
        }, 300);
    });

    // Combined filtering and search function
    function applyCombinedFilters() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const activeFilters = {};
        
        // Get active filters
        document.querySelectorAll('.filter-select').forEach(select => {
            if (select.value && select.value !== 'all') {
                activeFilters[select.dataset.filter] = select.value;
            }
        });

        searchableItems.forEach(item => {
            let shouldShow = true;

            // Apply search filter
            if (searchTerm !== '') {
                shouldShow = shouldShow && performAdvancedSearch(item, searchTerm);
            }

            // Apply other filters
            Object.keys(activeFilters).forEach(filterType => {
                if (filterType === 'feeRange') {
                    shouldShow = shouldShow && checkFeeRange(item, activeFilters[filterType]);
                } else if (filterType === 'dateRange') {
                    shouldShow = shouldShow && checkDateRange(item, activeFilters[filterType]);
                } else if (filterType === 'tags') {
                    shouldShow = shouldShow && checkTags(item, activeFilters[filterType]);
                } else {
                    const itemValue = item.dataset[filterType];
                    if (itemValue && itemValue !== activeFilters[filterType]) {
                        shouldShow = false;
                    }
                }
            });

            item.style.display = shouldShow ? 'block' : 'none';
        });

        updateSearchResultsCount();
    }

    // Helper functions for combined filtering
    function checkFeeRange(item, rangeFilter) {
        const feeText = item.querySelector('.detail-item:has(i.fa-dollar-sign) span')?.textContent;
        if (!feeText) return true;
        
        const fee = parseInt(feeText.replace(/[^0-9]/g, ''));
        
        switch (rangeFilter) {
            case 'low':
                return fee < 500;
            case 'medium':
                return fee >= 500 && fee <= 1000;
            case 'high':
                return fee > 1000;
            default:
                return true;
        }
    }

    function checkDateRange(item, rangeFilter) {
        const dateElement = item.querySelector('.blog-date, .graduation-info, .gallery-meta .date');
        if (!dateElement) return true;
        
        const dateText = dateElement.textContent;
        const currentYear = new Date().getFullYear();
        
        switch (rangeFilter) {
            case 'recent':
                return dateText.includes(currentYear.toString()) && 
                       (dateText.includes('December') || dateText.includes('November') || dateText.includes('October'));
            case '2024':
                return dateText.includes('2024');
            case '2023':
                return dateText.includes('2023');
            case '2022':
                return dateText.includes('2022');
            case '2021':
                return dateText.includes('2021');
            case '2020':
                return dateText.includes('2020');
            default:
                return true;
        }
    }

    function checkTags(item, tagFilter) {
        const tagElements = item.querySelectorAll('.tag, .feature');
        const itemTags = Array.from(tagElements).map(tag => tag.textContent.toLowerCase());
        
        return itemTags.some(tag => tag.includes(tagFilter.toLowerCase()));
    }

    // Make applyCombinedFilters available to the filters function
    window.applyCombinedFilters = applyCombinedFilters;

    function performAdvancedSearch(item, searchTerm) {
        // Search in different content areas based on item type
        const searchableContent = [];
        
        // Course cards
        const courseTitle = item.querySelector('h3')?.textContent || '';
        const courseDescription = item.querySelector('p')?.textContent || '';
        const courseFaculty = item.querySelector('.course-faculty')?.textContent || '';
        const courseFeatures = Array.from(item.querySelectorAll('.feature')).map(f => f.textContent).join(' ');
        
        // Testimonial cards
        const studentName = item.querySelector('.student-info h3')?.textContent || '';
        const currentRole = item.querySelector('.current-role')?.textContent || '';
        const testimonialContent = item.querySelector('.testimonial-content p')?.textContent || '';
        
        // Gallery items
        const galleryTitle = item.querySelector('.gallery-info h3')?.textContent || '';
        const galleryDescription = item.querySelector('.gallery-info p')?.textContent || '';
        
        // Blog articles
        const blogTitle = item.querySelector('h3 a')?.textContent || '';
        const blogContent = item.querySelector('.blog-content p')?.textContent || '';
        const blogTags = Array.from(item.querySelectorAll('.tag')).map(t => t.textContent).join(' ');
        const blogAuthor = item.querySelector('.blog-author')?.textContent || '';
        
        // Combine all searchable content
        const allContent = [
            courseTitle, courseDescription, courseFaculty, courseFeatures,
            studentName, currentRole, testimonialContent,
            galleryTitle, galleryDescription,
            blogTitle, blogContent, blogTags, blogAuthor
        ].join(' ').toLowerCase();
        
        // Check if search term matches any content
        return allContent.includes(searchTerm);
    }

    function updateSearchResultsCount() {
        const visibleItems = document.querySelectorAll('.searchable-item[style="display: block"], .searchable-item:not([style*="display: none"])');
        const countElement = document.querySelector('.results-count');
        if (countElement && searchInput.value.trim() !== '') {
            const itemType = getItemType();
            countElement.textContent = `Found ${visibleItems.length} ${itemType} matching "${searchInput.value}"`;
        }
    }

    function getItemType() {
        if (document.querySelector('.course-card')) return 'courses';
        if (document.querySelector('.testimonial-card')) return 'testimonials';
        if (document.querySelector('.gallery-item')) return 'media items';
        if (document.querySelector('.blog-card')) return 'articles';
        return 'results';
    }
}

function initializeLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback for browsers without IntersectionObserver
        images.forEach(img => {
            img.src = img.dataset.src;
            img.classList.remove('lazy');
        });
    }
}

// Utility functions
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

function truncateText(text, maxLength) {
    if (text.length <= maxLength) return text;
    return text.substr(0, maxLength) + '...';
}

// Dropdown menu functionality for mobile
document.addEventListener('DOMContentLoaded', function() {
    const dropdowns = document.querySelectorAll('.nav-dropdown');
    
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        
        if (toggle) {
            // For mobile: toggle dropdown on click
            toggle.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    dropdown.classList.toggle('active');
                    
                    // Close other dropdowns
                    dropdowns.forEach(otherDropdown => {
                        if (otherDropdown !== dropdown) {
                            otherDropdown.classList.remove('active');
                        }
                    });
                }
            });
        }
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.nav-dropdown')) {
            dropdowns.forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
    });
});


// Sticky header with layout change on scroll - Smooth both directions
let isScrolled = false;
let ticking = false;

function updateHeaderState() {
    const header = document.querySelector('.main-header');
    const navContainer = document.querySelector('.nav-container');
    
    if (!header || !navContainer) {
        ticking = false;
        return;
    }
    
    const scrollY = window.scrollY || window.pageYOffset;
    
    // Thresholds for smooth transition
    const activateThreshold = 50;   // Logo moves to left at 50px
    const resetThreshold = 20;      // Logo returns to center at 20px
    
    if (scrollY > activateThreshold && !isScrolled) {
        // Scrolling down - add scrolled state
        isScrolled = true;
        header.classList.add('scrolled');
        navContainer.classList.add('scrolled');
    } else if (scrollY < resetThreshold && isScrolled) {
        // Scrolling up to top - remove scrolled state smoothly
        isScrolled = false;
        header.classList.remove('scrolled');
        navContainer.classList.remove('scrolled');
    }
    
    ticking = false;
}

window.addEventListener('scroll', function() {
    if (!ticking) {
        window.requestAnimationFrame(updateHeaderState);
        ticking = true;
    }
}, { passive: true });


// Scroll to Top Button - Show/Hide and Functionality
(function() {
    const scrollToTopBtn = document.getElementById('scroll-to-top-btn');
    
    if (!scrollToTopBtn) return;
    
    let isVisible = false;
    let scrollTimeout;
    
    // Show/hide button based on scroll position
    window.addEventListener('scroll', function() {
        clearTimeout(scrollTimeout);
        
        scrollTimeout = setTimeout(function() {
            const scrollY = window.scrollY || window.pageYOffset;
            
            if (scrollY > 300 && !isVisible) {
                // Show button when scrolled down 300px
                isVisible = true;
                scrollToTopBtn.style.display = 'flex';
                setTimeout(function() {
                    scrollToTopBtn.style.opacity = '1';
                    scrollToTopBtn.style.transform = 'scale(1)';
                }, 10);
            } else if (scrollY <= 300 && isVisible) {
                // Hide button when near top
                isVisible = false;
                scrollToTopBtn.style.opacity = '0';
                scrollToTopBtn.style.transform = 'scale(0.8)';
                setTimeout(function() {
                    scrollToTopBtn.style.display = 'none';
                }, 300);
            }
        }, 100);
    }, { passive: true });
    
    // Scroll to top when clicked
    scrollToTopBtn.addEventListener('click', function() {
        // Smooth scroll to top
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Add hover effect
    scrollToTopBtn.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.1)';
        this.style.background = '#2980b9';
    });
    
    scrollToTopBtn.addEventListener('mouseleave', function() {
        this.style.transform = isVisible ? 'scale(1)' : 'scale(0.8)';
        this.style.background = '#3498db';
    });
})();
