<?php
// Define system access for includes
define('SYSTEM_ACCESS', true);

$page_title = "Gallery";
$page_description = "Explore our campus facilities, events, and student activities through our photo and video gallery";
include '../includes/public_header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Photo & Video Gallery</h1>
        <p>Discover our vibrant campus life and modern facilities</p>
    </div>
</div>

<section class="gallery-filter section">
    <div class="container">
        <div class="filter-controls">
            <div class="filter-group">
                <label for="category">Category:</label>
                <select id="category" class="filter-select" data-filter="category">
                    <option value="all">All Categories</option>
                    <option value="facilities">Facilities</option>
                    <option value="events">Events</option>
                    <option value="activities">Student Activities</option>
                    <option value="graduation">Graduation</option>
                    <option value="workshops">Workshops</option>
                    <option value="campus">Campus Life</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="media-type">Media Type:</label>
                <select id="media-type" class="filter-select" data-filter="mediaType">
                    <option value="all">All Media</option>
                    <option value="photo">Photos</option>
                    <option value="video">Videos</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="date-range">Date:</label>
                <select id="date-range" class="filter-select" data-filter="dateRange">
                    <option value="all">All Time</option>
                    <option value="recent">Last 3 Months</option>
                    <option value="2024">2024</option>
                    <option value="2023">2023</option>
                    <option value="2022">2022</option>
                </select>
            </div>
            <div class="search-group">
                <input type="text" class="search-input" placeholder="Search by event name, description, or tags...">
                <i class="fas fa-search"></i>
            </div>
        </div>
        <div class="results-count">Showing all media</div>
    </div>
</section>

<section class="gallery-grid section">
    <div class="container">
        <div class="gallery-container">
            <!-- Photo Gallery Item 1 -->
            <div class="gallery-item filterable-item searchable-item" 
                 data-category="facilities" 
                 data-media-type="photo" 
                 data-date-range="2024">
                <div class="gallery-image">
                    <img src="/images/gallery-computer-lab.jpg" alt="Modern Computer Lab" data-src="/images/gallery-computer-lab.jpg" class="lazy">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Modern Computer Lab</h3>
                            <p>State-of-the-art computer lab with latest hardware and software</p>
                            <div class="gallery-meta">
                                <span class="category">Facilities</span>
                                <span class="date">March 2024</span>
                            </div>
                        </div>
                        <div class="gallery-actions">
                            <button class="view-btn" data-image="/images/gallery-computer-lab.jpg">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="download-btn" data-image="/images/gallery-computer-lab.jpg">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                    <div class="media-type-badge">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>
            </div>

            <!-- Video Gallery Item 2 -->
            <div class="gallery-item filterable-item searchable-item" 
                 data-category="events" 
                 data-media-type="video" 
                 data-date-range="recent">
                <div class="gallery-image">
                    <img src="/images/gallery-graduation-ceremony.jpg" alt="Graduation Ceremony 2024" data-src="/images/gallery-graduation-ceremony.jpg" class="lazy">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Graduation Ceremony 2024</h3>
                            <p>Highlights from our annual graduation ceremony celebrating student achievements</p>
                            <div class="gallery-meta">
                                <span class="category">Events</span>
                                <span class="date">December 2024</span>
                            </div>
                        </div>
                        <div class="gallery-actions">
                            <button class="play-btn" data-video="/videos/graduation-2024.mp4">
                                <i class="fas fa-play"></i>
                            </button>
                            <button class="download-btn" data-video="/videos/graduation-2024.mp4">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                    <div class="media-type-badge">
                        <i class="fas fa-video"></i>
                    </div>
                </div>
            </div>

            <!-- Photo Gallery Item 3 -->
            <div class="gallery-item filterable-item searchable-item" 
                 data-category="activities" 
                 data-media-type="photo" 
                 data-date-range="2024">
                <div class="gallery-image">
                    <img src="/images/gallery-coding-workshop.jpg" alt="Coding Workshop" data-src="/images/gallery-coding-workshop.jpg" class="lazy">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Interactive Coding Workshop</h3>
                            <p>Students participating in hands-on coding exercises and collaborative projects</p>
                            <div class="gallery-meta">
                                <span class="category">Student Activities</span>
                                <span class="date">November 2024</span>
                            </div>
                        </div>
                        <div class="gallery-actions">
                            <button class="view-btn" data-image="/images/gallery-coding-workshop.jpg">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="download-btn" data-image="/images/gallery-coding-workshop.jpg">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                    <div class="media-type-badge">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>
            </div>

            <!-- Photo Gallery Item 4 -->
            <div class="gallery-item filterable-item searchable-item" 
                 data-category="facilities" 
                 data-media-type="photo" 
                 data-date-range="2024">
                <div class="gallery-image">
                    <img src="/images/gallery-library.jpg" alt="Digital Library" data-src="/images/gallery-library.jpg" class="lazy">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Digital Library & Study Area</h3>
                            <p>Quiet study spaces with access to digital resources and research materials</p>
                            <div class="gallery-meta">
                                <span class="category">Facilities</span>
                                <span class="date">February 2024</span>
                            </div>
                        </div>
                        <div class="gallery-actions">
                            <button class="view-btn" data-image="/images/gallery-library.jpg">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="download-btn" data-image="/images/gallery-library.jpg">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                    <div class="media-type-badge">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>
            </div>

            <!-- Video Gallery Item 5 -->
            <div class="gallery-item filterable-item searchable-item" 
                 data-category="workshops" 
                 data-media-type="video" 
                 data-date-range="2024">
                <div class="gallery-image">
                    <img src="/images/gallery-design-workshop.jpg" alt="Design Workshop" data-src="/images/gallery-design-workshop.jpg" class="lazy">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>UI/UX Design Workshop</h3>
                            <p>Professional design workshop featuring industry experts and practical exercises</p>
                            <div class="gallery-meta">
                                <span class="category">Workshops</span>
                                <span class="date">October 2024</span>
                            </div>
                        </div>
                        <div class="gallery-actions">
                            <button class="play-btn" data-video="/videos/design-workshop-2024.mp4">
                                <i class="fas fa-play"></i>
                            </button>
                            <button class="download-btn" data-video="/videos/design-workshop-2024.mp4">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                    <div class="media-type-badge">
                        <i class="fas fa-video"></i>
                    </div>
                </div>
            </div>

            <!-- Photo Gallery Item 6 -->
            <div class="gallery-item filterable-item searchable-item" 
                 data-category="campus" 
                 data-media-type="photo" 
                 data-date-range="2024">
                <div class="gallery-image">
                    <img src="/images/gallery-campus-life.jpg" alt="Campus Life" data-src="/images/gallery-campus-life.jpg" class="lazy">
                    <div class="gallery-overlay">
                        <div class="gallery-info">
                            <h3>Vibrant Campus Life</h3>
                            <p>Students enjoying recreational activities and social interactions on campus</p>
                            <div class="gallery-meta">
                                <span class="category">Campus Life</span>
                                <span class="date">September 2024</span>
                            </div>
                        </div>
                        <div class="gallery-actions">
                            <button class="view-btn" data-image="/images/gallery-campus-life.jpg">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="download-btn" data-image="/images/gallery-campus-life.jpg">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                    <div class="media-type-badge">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Image/Video Modal -->
<div id="media-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div class="modal-media">
            <img id="modal-image" src="" alt="">
            <video id="modal-video" controls style="display: none;">
                <source src="" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
        <div class="modal-info">
            <h3 id="modal-title"></h3>
            <p id="modal-description"></p>
        </div>
    </div>
</div>

<style>
.gallery-filter {
    background: #f8f9fa;
    padding: 2rem 0;
}

.filter-controls {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1rem;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #2c3e50;
}

.filter-select {
    padding: 0.75rem;
    border: 2px solid #ddd;
    border-radius: 5px;
    background: white;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.filter-select:focus {
    outline: none;
    border-color: #3498db;
}

.search-group {
    position: relative;
    display: flex;
    align-items: flex-end;
}

.search-input {
    width: 100%;
    padding: 0.75rem 2.5rem 0.75rem 1rem;
    border: 2px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: #3498db;
}

.search-group i {
    position: absolute;
    right: 1rem;
    color: #666;
}

.results-count {
    text-align: center;
    color: #666;
    font-weight: 500;
    margin-top: 1rem;
}

.gallery-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.gallery-item {
    position: relative;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.gallery-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.gallery-image {
    position: relative;
    height: 250px;
    overflow: hidden;
}

.gallery-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.gallery-item:hover .gallery-image img {
    transform: scale(1.05);
}

.gallery-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.8) 100%);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 1.5rem;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.gallery-item:hover .gallery-overlay {
    opacity: 1;
}

.gallery-info {
    margin-top: auto;
}

.gallery-info h3 {
    color: white;
    margin-bottom: 0.5rem;
    font-size: 1.2rem;
}

.gallery-info p {
    color: rgba(255,255,255,0.9);
    font-size: 0.9rem;
    line-height: 1.4;
    margin-bottom: 1rem;
}

.gallery-meta {
    display: flex;
    gap: 1rem;
}

.gallery-meta span {
    background: rgba(255,255,255,0.2);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 500;
}

.gallery-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
}

.gallery-actions button {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.gallery-actions button:hover {
    background: rgba(255,255,255,0.3);
}

.media-type-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: #3498db;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.9);
}

.modal-content {
    position: relative;
    margin: 5% auto;
    padding: 2rem;
    width: 90%;
    max-width: 800px;
    background: white;
    border-radius: 15px;
}

.close {
    position: absolute;
    top: 1rem;
    right: 1.5rem;
    color: #aaa;
    font-size: 2rem;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: #000;
}

.modal-media img,
.modal-media video {
    width: 100%;
    height: auto;
    border-radius: 10px;
}

.modal-info {
    margin-top: 1.5rem;
}

.modal-info h3 {
    color: #2c3e50;
    margin-bottom: 1rem;
}

.modal-info p {
    color: #666;
    line-height: 1.6;
}

@media (max-width: 768px) {
    .filter-controls {
        grid-template-columns: 1fr;
    }
    
    .gallery-container {
        grid-template-columns: 1fr;
    }
    
    .modal-content {
        width: 95%;
        margin: 10% auto;
        padding: 1rem;
    }
}
</style>

<script>
// Gallery modal functionality
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('media-modal');
    const modalImage = document.getElementById('modal-image');
    const modalVideo = document.getElementById('modal-video');
    const modalTitle = document.getElementById('modal-title');
    const modalDescription = document.getElementById('modal-description');
    const closeBtn = document.querySelector('.close');

    // View image buttons
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const imageSrc = this.dataset.image;
            const galleryItem = this.closest('.gallery-item');
            const title = galleryItem.querySelector('h3').textContent;
            const description = galleryItem.querySelector('p').textContent;

            modalImage.src = imageSrc;
            modalImage.style.display = 'block';
            modalVideo.style.display = 'none';
            modalTitle.textContent = title;
            modalDescription.textContent = description;
            modal.style.display = 'block';
        });
    });

    // Play video buttons
    document.querySelectorAll('.play-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const videoSrc = this.dataset.video;
            const galleryItem = this.closest('.gallery-item');
            const title = galleryItem.querySelector('h3').textContent;
            const description = galleryItem.querySelector('p').textContent;

            modalVideo.querySelector('source').src = videoSrc;
            modalVideo.load();
            modalVideo.style.display = 'block';
            modalImage.style.display = 'none';
            modalTitle.textContent = title;
            modalDescription.textContent = description;
            modal.style.display = 'block';
        });
    });

    // Close modal
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
        modalVideo.pause();
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
            modalVideo.pause();
        }
    });

    // Download functionality
    document.querySelectorAll('.download-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const mediaUrl = this.dataset.image || this.dataset.video;
            const link = document.createElement('a');
            link.href = mediaUrl;
            link.download = mediaUrl.split('/').pop();
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    });
});
</script>

<?php include '../includes/public_footer.php'; ?>