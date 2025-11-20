// SEO and Social Media Integration JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize social sharing functionality
    initializeSocialSharing();
    
    // Initialize newsletter signup
    initializeNewsletterSignup();
    
    // Initialize accessibility features
    initializeAccessibility();
    
    // Initialize SEO enhancements
    initializeSEOEnhancements();
    
    // Initialize print functionality
    initializePrintFeatures();
});

function initializeSocialSharing() {
    // Note: floating-share-btn has been replaced with scroll-to-top-btn
    // Keeping share functionality for share-page-btn only
    const sharePageBtn = document.getElementById('share-page-btn');
    const socialShareModal = document.getElementById('social-share-modal');
    const closeShareModal = document.querySelector('.close-share-modal');
    
    // Get current page information
    const currentUrl = window.location.href;
    const pageTitle = document.title;
    const pageDescription = document.querySelector('meta[name="description"]')?.content || '';
    const pageImage = document.querySelector('meta[property="og:image"]')?.content || '';
    
    // Open share modal
    function openShareModal() {
        if (!socialShareModal) return;
        
        updateShareLinks();
        socialShareModal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Focus management for accessibility
        const firstButton = socialShareModal.querySelector('.share-btn');
        if (firstButton) {
            setTimeout(() => firstButton.focus(), 100);
        }
        
        console.log('Share modal opened'); // Debug log
    }
    
    // Close share modal
    function closeShareModalFunc() {
        socialShareModal.classList.remove('active');
        document.body.style.overflow = '';
        
        // Reset copy button when modal closes
        const copyLinkBtn = document.querySelector('.copy-link');
        if (copyLinkBtn && copyLinkBtn.classList.contains('copied')) {
            copyLinkBtn.querySelector('span').textContent = 'Copy Link';
            copyLinkBtn.classList.remove('copied');
        }
    }
    
    // Event listeners
    // Note: floatingShareBtn removed - now using scroll-to-top button instead
    
    if (sharePageBtn) {
        sharePageBtn.addEventListener('click', openShareModal);
    }
    
    if (closeShareModal) {
        closeShareModal.addEventListener('click', closeShareModalFunc);
    }
    
    // Close modal when clicking outside
    if (socialShareModal) {
        socialShareModal.addEventListener('click', function(e) {
            if (e.target === socialShareModal) {
                closeShareModalFunc();
            }
        });
    }
    
    // Escape key to close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && socialShareModal.classList.contains('active')) {
            closeShareModalFunc();
        }
    });
    
    // Update share links with current page data
    function updateShareLinks() {
        const encodedUrl = encodeURIComponent(currentUrl);
        const encodedTitle = encodeURIComponent(pageTitle);
        const encodedDescription = encodeURIComponent(pageDescription);
        
        // Facebook share
        const facebookShare = document.querySelector('.facebook-share');
        if (facebookShare) {
            facebookShare.href = `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`;
        }
        
        // Twitter share
        const twitterShare = document.querySelector('.twitter-share');
        if (twitterShare) {
            const twitterText = `${pageTitle} - ${pageDescription}`;
            twitterShare.href = `https://twitter.com/intent/tweet?url=${encodedUrl}&text=${encodeURIComponent(twitterText)}&via=eduportal`;
        }
        
        // LinkedIn share
        const linkedinShare = document.querySelector('.linkedin-share');
        if (linkedinShare) {
            linkedinShare.href = `https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}`;
        }
        
        // WhatsApp share
        const whatsappShare = document.querySelector('.whatsapp-share');
        if (whatsappShare) {
            const whatsappText = `${pageTitle} - ${pageDescription} ${currentUrl}`;
            whatsappShare.href = `https://wa.me/?text=${encodeURIComponent(whatsappText)}`;
        }
        
        // Email share
        const emailShare = document.querySelector('.email-share');
        if (emailShare) {
            const emailSubject = `Check out: ${pageTitle}`;
            const emailBody = `I thought you might be interested in this:\n\n${pageTitle}\n${pageDescription}\n\n${currentUrl}`;
            emailShare.href = `mailto:?subject=${encodeURIComponent(emailSubject)}&body=${encodeURIComponent(emailBody)}`;
        }
        
        // Copy link functionality
        const copyLinkBtn = document.querySelector('.copy-link');
        if (copyLinkBtn) {
            copyLinkBtn.dataset.url = currentUrl;
            
            // Remove old event listeners by cloning
            const newCopyBtn = copyLinkBtn.cloneNode(true);
            copyLinkBtn.parentNode.replaceChild(newCopyBtn, copyLinkBtn);
            
            newCopyBtn.addEventListener('click', function(e) {
                e.preventDefault();
                copyToClipboard(currentUrl);
                
                // Visual feedback - stays green until modal closes
                this.querySelector('span').textContent = 'Copied!';
                this.classList.add('copied');
                
                // Don't reset - will reset when modal closes
            });
        }
    }
    
    // Copy to clipboard function
    function copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text);
        } else {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
            } catch (err) {
                console.error('Failed to copy text: ', err);
            }
            
            document.body.removeChild(textArea);
        }
    }
    
    // Track social sharing events (for analytics)
    document.querySelectorAll('.share-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const platform = this.classList[1]?.replace('-share', '') || 'unknown';
            
            // Send analytics event if available
            if (typeof gtag !== 'undefined') {
                gtag('event', 'share', {
                    method: platform,
                    content_type: 'page',
                    item_id: currentUrl
                });
            }
            
            // Custom analytics tracking
            trackSocialShare(platform, currentUrl, pageTitle);
        });
    });
}

function initializeNewsletterSignup() {
    const newsletterForm = document.querySelector('.newsletter-form');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const emailInput = this.querySelector('input[name="email"]');
            const email = emailInput.value.trim();
            
            if (!isValidEmail(email)) {
                showNotification('Please enter a valid email address', 'error');
                return;
            }
            
            // Disable form during submission
            const submitBtn = this.querySelector('button');
            const originalBtnContent = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            submitBtn.disabled = true;
            
            // Submit newsletter signup
            fetch('/api/newsletter', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Thank you for subscribing to our newsletter!', 'success');
                    emailInput.value = '';
                } else {
                    showNotification(data.message || 'Subscription failed. Please try again.', 'error');
                }
            })
            .catch(error => {
                console.error('Newsletter signup error:', error);
                showNotification('Network error. Please try again later.', 'error');
            })
            .finally(() => {
                // Re-enable form
                submitBtn.innerHTML = originalBtnContent;
                submitBtn.disabled = false;
            });
        });
    }
}

function initializeAccessibility() {
    // Add skip link
    const skipLink = document.createElement('a');
    skipLink.href = '#main-content';
    skipLink.className = 'skip-link';
    skipLink.textContent = 'Skip to main content';
    document.body.insertBefore(skipLink, document.body.firstChild);
    
    // Add main content ID if not present
    const mainContent = document.querySelector('.main-content');
    if (mainContent && !mainContent.id) {
        mainContent.id = 'main-content';
    }
    
    // Enhance keyboard navigation
    document.addEventListener('keydown', function(e) {
        // Tab trap for modal
        if (e.key === 'Tab' && document.querySelector('.social-share-modal.active')) {
            trapFocus(e, document.querySelector('.share-modal-content'));
        }
    });
    
    // Add ARIA labels to social links
    document.querySelectorAll('.social-links a').forEach(link => {
        const platform = link.querySelector('i').className.match(/fa-(\w+)/)?.[1];
        if (platform && !link.getAttribute('aria-label')) {
            link.setAttribute('aria-label', `Follow us on ${platform.charAt(0).toUpperCase() + platform.slice(1)}`);
        }
    });
    
    // Announce dynamic content changes
    const announcer = document.createElement('div');
    announcer.setAttribute('aria-live', 'polite');
    announcer.setAttribute('aria-atomic', 'true');
    announcer.className = 'sr-only';
    announcer.style.position = 'absolute';
    announcer.style.left = '-10000px';
    announcer.style.width = '1px';
    announcer.style.height = '1px';
    announcer.style.overflow = 'hidden';
    document.body.appendChild(announcer);
    
    window.announceToScreenReader = function(message) {
        announcer.textContent = message;
        setTimeout(() => {
            announcer.textContent = '';
        }, 1000);
    };
}

function initializeSEOEnhancements() {
    // Update page title dynamically for SPA-like behavior
    const originalTitle = document.title;
    
    // Add structured data for breadcrumbs if applicable
    addBreadcrumbStructuredData();
    
    // Generate and inject sitemap link
    addSitemapLink();
    
    // Add canonical URL if not present
    if (!document.querySelector('link[rel="canonical"]')) {
        const canonical = document.createElement('link');
        canonical.rel = 'canonical';
        canonical.href = window.location.href.split('?')[0].split('#')[0];
        document.head.appendChild(canonical);
    }
    
    // Add hreflang attributes for international SEO (if applicable)
    addHreflangAttributes();
    
    // Monitor and report Core Web Vitals
    if ('web-vital' in window) {
        monitorWebVitals();
    }
    
    // Add JSON-LD for current page type
    enhanceStructuredData();
}

function initializePrintFeatures() {
    // Add print styles dynamically
    const printStylesheet = document.createElement('link');
    printStylesheet.rel = 'stylesheet';
    printStylesheet.href = '/css/print.css';
    printStylesheet.media = 'print';
    document.head.appendChild(printStylesheet);
    
    // Print button functionality
    const printBtn = document.getElementById('print-btn');
    if (printBtn) {
        printBtn.addEventListener('click', function() {
            // Add print-specific content
            addPrintHeader();
            
            // Track print action
            if (typeof gtag !== 'undefined') {
                gtag('event', 'print', {
                    event_category: 'engagement',
                    event_label: window.location.pathname
                });
            }
            
            // Trigger print
            window.print();
        });
    }
    
    // Before print event
    window.addEventListener('beforeprint', function() {
        addPrintHeader();
        document.body.classList.add('printing');
    });
    
    // After print event
    window.addEventListener('afterprint', function() {
        document.body.classList.remove('printing');
    });
}

// Utility Functions

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function trapFocus(e, container) {
    const focusableElements = container.querySelectorAll(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];
    
    if (e.shiftKey) {
        if (document.activeElement === firstElement) {
            lastElement.focus();
            e.preventDefault();
        }
    } else {
        if (document.activeElement === lastElement) {
            firstElement.focus();
            e.preventDefault();
        }
    }
}

function addBreadcrumbStructuredData() {
    const pathSegments = window.location.pathname.split('/').filter(segment => segment);
    
    if (pathSegments.length > 0) {
        const breadcrumbList = {
            "@context": "https://schema.org",
            "@type": "BreadcrumbList",
            "itemListElement": []
        };
        
        // Add home
        breadcrumbList.itemListElement.push({
            "@type": "ListItem",
            "position": 1,
            "name": "Home",
            "item": window.location.origin
        });
        
        // Add path segments
        let currentPath = '';
        pathSegments.forEach((segment, index) => {
            currentPath += '/' + segment;
            breadcrumbList.itemListElement.push({
                "@type": "ListItem",
                "position": index + 2,
                "name": segment.charAt(0).toUpperCase() + segment.slice(1).replace('-', ' '),
                "item": window.location.origin + currentPath
            });
        });
        
        const script = document.createElement('script');
        script.type = 'application/ld+json';
        script.textContent = JSON.stringify(breadcrumbList);
        document.head.appendChild(script);
    }
}

function addSitemapLink() {
    if (!document.querySelector('link[rel="sitemap"]')) {
        const sitemap = document.createElement('link');
        sitemap.rel = 'sitemap';
        sitemap.type = 'application/xml';
        sitemap.href = '/sitemap.xml';
        document.head.appendChild(sitemap);
    }
}

function addHreflangAttributes() {
    // Add hreflang for current language (English)
    if (!document.querySelector('link[hreflang]')) {
        const hreflang = document.createElement('link');
        hreflang.rel = 'alternate';
        hreflang.hreflang = 'en';
        hreflang.href = window.location.href;
        document.head.appendChild(hreflang);
        
        // Add x-default
        const xDefault = document.createElement('link');
        xDefault.rel = 'alternate';
        xDefault.hreflang = 'x-default';
        xDefault.href = window.location.href;
        document.head.appendChild(xDefault);
    }
}

function enhanceStructuredData() {
    const currentPage = window.location.pathname;
    
    // Add page-specific structured data
    if (currentPage.includes('/courses')) {
        addCourseStructuredData();
    }
}

function addCourseStructuredData() {
    // This would be enhanced with actual course data
    const courseData = {
        "@context": "https://schema.org",
        "@type": "Course",
        "name": "Professional Courses",
        "description": "Comprehensive professional courses for career development",
        "provider": {
            "@type": "EducationalOrganization",
            "name": "EduPortal",
            "url": "https://eduportal.com"
        }
    };
    
    const script = document.createElement('script');
    script.type = 'application/ld+json';
    script.textContent = JSON.stringify(courseData);
    document.head.appendChild(script);
}


                    "@type": "EducationalOrganization",
                    "name": "EduPortal"
                }
            };
            
            const script = document.createElement('script');
            script.type = 'application/ld+json';
            script.textContent = JSON.stringify(reviewData);
            document.head.appendChild(script);
        }
    });
}

function addPrintHeader() {
    // Add print-specific information
    const printInfo = document.createElement('div');
    printInfo.className = 'print-only print-info';
    printInfo.innerHTML = `
        <h2>EduPortal - Educational Institution</h2>
        <p>Printed from: ${window.location.href}</p>
        <p>Date: ${new Date().toLocaleDateString()}</p>
        <hr>
    `;
    
    const mainContent = document.querySelector('.main-content');
    if (mainContent && !document.querySelector('.print-info')) {
        mainContent.insertBefore(printInfo, mainContent.firstChild);
    }
}

function trackSocialShare(platform, url, title) {
    // Custom analytics tracking for social shares
    console.log(`Social share tracked: ${platform} - ${title} - ${url}`);
    
    // Send to custom analytics endpoint
    fetch('/api/analytics/social-share', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            platform: platform,
            url: url,
            title: title,
            timestamp: new Date().toISOString()
        })
    }).catch(error => {
        console.log('Analytics tracking failed:', error);
    });
}

function monitorWebVitals() {
    // Monitor Core Web Vitals for SEO
    if ('PerformanceObserver' in window) {
        // Largest Contentful Paint
        new PerformanceObserver((entryList) => {
            for (const entry of entryList.getEntries()) {
                console.log('LCP:', entry.startTime);
            }
        }).observe({ entryTypes: ['largest-contentful-paint'] });
        
        // First Input Delay
        new PerformanceObserver((entryList) => {
            for (const entry of entryList.getEntries()) {
                console.log('FID:', entry.processingStart - entry.startTime);
            }
        }).observe({ entryTypes: ['first-input'] });
        
        // Cumulative Layout Shift
        new PerformanceObserver((entryList) => {
            for (const entry of entryList.getEntries()) {
                if (!entry.hadRecentInput) {
                    console.log('CLS:', entry.value);
                }
            }
        }).observe({ entryTypes: ['layout-shift'] });
    }
}

// Individual Content Sharing Enhancement
document.addEventListener('DOMContentLoaded', function() {
    initializeContentSharing();
});

function initializeContentSharing() {
    // Handle individual share buttons on content items
    document.querySelectorAll('.btn-share, .content-share-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const title = this.dataset.title || document.title;
            const description = this.dataset.description || document.querySelector('meta[name="description"]')?.content || '';
            const image = this.dataset.image || document.querySelector('meta[property="og:image"]')?.content || '';
            const url = this.dataset.url || window.location.href;
            
            openContentShareModal(title, description, image, url);
        });
    });
    
    // Handle inline share buttons
    document.querySelectorAll('.inline-share-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const platform = this.classList[1]; // facebook, twitter, etc.
            const title = this.closest('.card, .course-card')
                ?.querySelector('h3')?.textContent || document.title;
            const description = this.closest('.card, .course-card')
                ?.querySelector('p')?.textContent || '';
            const url = window.location.href;
            
            shareToSpecificPlatform(platform, title, description, url);
        });
    });
    
    // Add share buttons to existing content dynamically
    addShareButtonsToContent();
}

function openContentShareModal(title, description, image, url) {
    // Create or update the share modal with specific content
    const modal = document.getElementById('social-share-modal');
    if (modal) {
        // Update share links with specific content
        updateShareLinksWithContent(title, description, image, url);
        
        // Show modal
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Focus management
        const firstButton = modal.querySelector('.share-btn');
        if (firstButton) {
            firstButton.focus();
        }
    }
}

function updateShareLinksWithContent(title, description, image, url) {
    const encodedUrl = encodeURIComponent(url);
    const encodedTitle = encodeURIComponent(title);
    const encodedDescription = encodeURIComponent(description);
    
    // Update Facebook share
    const facebookShare = document.querySelector('.facebook-share');
    if (facebookShare) {
        facebookShare.href = `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}&quote=${encodedTitle}`;
    }
    
    // Update Twitter share
    const twitterShare = document.querySelector('.twitter-share');
    if (twitterShare) {
        const twitterText = `${title} - ${description}`;
        twitterShare.href = `https://twitter.com/intent/tweet?url=${encodedUrl}&text=${encodeURIComponent(twitterText)}&via=eduportal`;
    }
    
    // Update LinkedIn share
    const linkedinShare = document.querySelector('.linkedin-share');
    if (linkedinShare) {
        linkedinShare.href = `https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}&title=${encodedTitle}&summary=${encodedDescription}`;
    }
    
    // Update WhatsApp share
    const whatsappShare = document.querySelector('.whatsapp-share');
    if (whatsappShare) {
        const whatsappText = `${title}\n${description}\n${url}`;
        whatsappShare.href = `https://wa.me/?text=${encodeURIComponent(whatsappText)}`;
    }
    
    // Update Email share
    const emailShare = document.querySelector('.email-share');
    if (emailShare) {
        const emailSubject = `Check out: ${title}`;
        const emailBody = `I thought you might be interested in this:\n\n${title}\n${description}\n\n${url}`;
        emailShare.href = `mailto:?subject=${encodeURIComponent(emailSubject)}&body=${encodeURIComponent(emailBody)}`;
    }
    
    // Update copy link
    const copyLinkBtn = document.querySelector('.copy-link');
    if (copyLinkBtn) {
        copyLinkBtn.dataset.url = url;
    }
}

function shareToSpecificPlatform(platform, title, description, url) {
    const encodedUrl = encodeURIComponent(url);
    const encodedTitle = encodeURIComponent(title);
    const encodedDescription = encodeURIComponent(description);
    
    let shareUrl = '';
    
    switch (platform) {
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}&quote=${encodedTitle}`;
            break;
        case 'twitter':
            const twitterText = `${title} - ${description}`;
            shareUrl = `https://twitter.com/intent/tweet?url=${encodedUrl}&text=${encodeURIComponent(twitterText)}&via=eduportal`;
            break;
        case 'linkedin':
            shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}&title=${encodedTitle}&summary=${encodedDescription}`;
            break;
        case 'whatsapp':
            const whatsappText = `${title}\n${description}\n${url}`;
            shareUrl = `https://wa.me/?text=${encodeURIComponent(whatsappText)}`;
            break;
    }
    
    if (shareUrl) {
        window.open(shareUrl, '_blank', 'width=600,height=400,scrollbars=yes,resizable=yes');
        
        // Track the share
        trackSocialShare(platform, url, title);
    }
}

function addShareButtonsToContent() {
    // Add share buttons to course cards
    document.querySelectorAll('.course-card').forEach(card => {
        if (!card.querySelector('.content-share-btn')) {
            const title = card.querySelector('h3')?.textContent || '';
            const description = card.querySelector('p')?.textContent || '';
            const image = card.querySelector('img')?.src || '';
            
            const shareBtn = document.createElement('button');
            shareBtn.className = 'content-share-btn';
            shareBtn.innerHTML = '<i class="fas fa-share-alt"></i>';
            shareBtn.setAttribute('aria-label', `Share ${title}`);
            shareBtn.dataset.title = title;
            shareBtn.dataset.description = description;
            shareBtn.dataset.image = image;
            
            card.style.position = 'relative';
            card.appendChild(shareBtn);
            
            shareBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                openContentShareModal(title, description, image, window.location.href);
            });
        }
    });
    
    // Add inline share buttons to blog cards
    document.querySelectorAll('.blog-card').forEach(card => {
        if (!card.querySelector('.inline-share')) {
            const title = card.querySelector('h3 a')?.textContent || '';
            const description = card.querySelector('p')?.textContent || '';
            
            const shareSection = document.createElement('div');
            shareSection.className = 'inline-share';
            shareSection.innerHTML = `
                <span class="inline-share-label">Share:</span>
                <div class="inline-share-buttons">
                    <button class="inline-share-btn facebook" aria-label="Share on Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </button>
                    <button class="inline-share-btn twitter" aria-label="Share on Twitter">
                        <i class="fab fa-twitter"></i>
                    </button>
                    <button class="inline-share-btn linkedin" aria-label="Share on LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </button>
                    <button class="inline-share-btn whatsapp" aria-label="Share on WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </button>
                </div>
            `;
            
            const cardContent = card.querySelector('.blog-content');
            if (cardContent) {
                cardContent.appendChild(shareSection);
            }
        }
    });
    
    // Add inline share buttons to testimonial cards
    document.querySelectorAll('.testimonial-card').forEach(card => {
        if (!card.querySelector('.inline-share')) {
            const studentName = card.querySelector('.student-info h3')?.textContent || '';
            const testimonialText = card.querySelector('.testimonial-content p')?.textContent || '';
            const title = `Success Story: ${studentName}`;
            
            const shareSection = document.createElement('div');
            shareSection.className = 'inline-share';
            shareSection.innerHTML = `
                <span class="inline-share-label">Share this story:</span>
                <div class="inline-share-buttons">
                    <button class="inline-share-btn facebook" aria-label="Share success story on Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </button>
                    <button class="inline-share-btn twitter" aria-label="Share success story on Twitter">
                        <i class="fab fa-twitter"></i>
                    </button>
                    <button class="inline-share-btn linkedin" aria-label="Share success story on LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </button>
                </div>
            `;
            
            card.appendChild(shareSection);
        }
    });
}

// Enhanced SEO functions
function generateBreadcrumbs() {
    const pathSegments = window.location.pathname.split('/').filter(segment => segment);
    
    if (pathSegments.length > 0) {
        const breadcrumbNav = document.createElement('nav');
        breadcrumbNav.className = 'breadcrumb';
        breadcrumbNav.setAttribute('aria-label', 'Breadcrumb navigation');
        
        const container = document.createElement('div');
        container.className = 'container';
        
        const breadcrumbList = document.createElement('ol');
        breadcrumbList.className = 'breadcrumb-list';
        breadcrumbList.setAttribute('itemscope', '');
        breadcrumbList.setAttribute('itemtype', 'https://schema.org/BreadcrumbList');
        
        // Add home
        const homeItem = document.createElement('li');
        homeItem.className = 'breadcrumb-item';
        homeItem.setAttribute('itemprop', 'itemListElement');
        homeItem.setAttribute('itemscope', '');
        homeItem.setAttribute('itemtype', 'https://schema.org/ListItem');
        homeItem.innerHTML = `
            <a href="/" class="breadcrumb-link" itemprop="item">
                <span itemprop="name">Home</span>
            </a>
            <meta itemprop="position" content="1">
        `;
        breadcrumbList.appendChild(homeItem);
        
        // Add path segments
        let currentPath = '';
        pathSegments.forEach((segment, index) => {
            currentPath += '/' + segment;
            const item = document.createElement('li');
            item.className = 'breadcrumb-item';
            item.setAttribute('itemprop', 'itemListElement');
            item.setAttribute('itemscope', '');
            item.setAttribute('itemtype', 'https://schema.org/ListItem');
            
            const segmentName = segment.charAt(0).toUpperCase() + segment.slice(1).replace('-', ' ');
            const position = index + 2;
            
            if (index === pathSegments.length - 1) {
                // Current page
                item.innerHTML = `
                    <span class="breadcrumb-current" itemprop="name">${segmentName}</span>
                    <meta itemprop="position" content="${position}">
                `;
            } else {
                // Link to parent page
                item.innerHTML = `
                    <a href="${currentPath}" class="breadcrumb-link" itemprop="item">
                        <span itemprop="name">${segmentName}</span>
                    </a>
                    <meta itemprop="position" content="${position}">
                `;
            }
            
            breadcrumbList.appendChild(item);
        });
        
        container.appendChild(breadcrumbList);
        breadcrumbNav.appendChild(container);
        
        // Insert breadcrumbs after header
        const header = document.querySelector('.main-header');
        const mainContent = document.querySelector('.main-content');
        if (header && mainContent && !document.querySelector('.breadcrumb')) {
            header.insertAdjacentElement('afterend', breadcrumbNav);
        }
    }
}

// Breadcrumbs disabled - not needed for this site
// if (window.location.pathname !== '/' && window.location.pathname !== '') {
//     generateBreadcrumbs();
// }

// Enhanced performance monitoring
function initializePerformanceMonitoring() {
    // Monitor page load performance
    window.addEventListener('load', function() {
        setTimeout(() => {
            const perfData = performance.getEntriesByType('navigation')[0];
            
            if (perfData) {
                const loadTime = perfData.loadEventEnd - perfData.loadEventStart;
                const domContentLoaded = perfData.domContentLoadedEventEnd - perfData.domContentLoadedEventStart;
                
                // Send performance data to analytics
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'page_load_time', {
                        event_category: 'performance',
                        value: Math.round(loadTime),
                        custom_parameter: {
                            dom_content_loaded: Math.round(domContentLoaded),
                            page_path: window.location.pathname
                        }
                    });
                }
                
                console.log('Page Performance:', {
                    loadTime: Math.round(loadTime) + 'ms',
                    domContentLoaded: Math.round(domContentLoaded) + 'ms'
                });
            }
        }, 0);
    });
}

// Initialize performance monitoring
initializePerformanceMonitoring();