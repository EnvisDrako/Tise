<?php
// Define system access for includes
define('SYSTEM_ACCESS', true);

$page_title = "Our Trainers";
$page_description = "Meet our experienced faculty and industry experts who guide students towards success";

// Get trainers from database
require_once '../includes/db_connect.php';

try {
    $db = DatabaseConnection::getInstance();
    $pdo = $db->getConnection();
    $stmt = $pdo->query("SELECT * FROM trainers WHERE status='active' ORDER BY created_at DESC");
    $trainers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $trainers = [];
}

include '../includes/public_header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Our Expert Trainers</h1>
        <p>Learn from industry professionals and experienced educators</p>
    </div>
</div>

<section class="trainers-filter section">
    <div class="container">
        <div class="filter-controls">
            <div class="filter-group">
                <label for="department">Department:</label>
                <select id="department" class="filter-select" data-filter="department">
                    <option value="all">All Departments</option>
                    <option value="technical">Technical</option>
                    <option value="business">Business</option>
                    <option value="creative">Creative</option>
                    <option value="language">Language</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="experience">Experience:</label>
                <select id="experience" class="filter-select" data-filter="experience">
                    <option value="all">All Experience Levels</option>
                    <option value="junior">5-10 Years</option>
                    <option value="senior">10-15 Years</option>
                    <option value="expert">15+ Years</option>
                </select>
            </div>
            <div class="filter-group search-group">
                <label for="search">Search:</label>
                <div class="search-input-wrapper">
                    <input type="text" id="search" class="search-input" placeholder="Search trainers by name or expertise...">
                    <i class="fas fa-search"></i>
                </div>
            </div>
        </div>
        <div class="results-count">Showing all trainers</div>
    </div>
</section>

<section class="trainers-grid section">
    <div class="container">
        <?php if (empty($trainers)): ?>
            <div class="empty-state">
                <i class="fas fa-chalkboard-teacher"></i>
                <h3>No Trainers Available</h3>
                <p>Trainer profiles will be displayed here once they are added by the admin.</p>
            </div>
        <?php else: ?>
        <div class="trainers-container">
            <?php foreach ($trainers as $trainer): ?>
            <div class="trainer-card filterable-item searchable-item">
                <div class="trainer-header">
                    <div class="trainer-photo">
                        <?php if ($trainer['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($trainer['image_url']); ?>" alt="<?php echo htmlspecialchars($trainer['name']); ?>">
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                    </div>
                    <div class="trainer-info">
                        <h3><?php echo htmlspecialchars($trainer['name']); ?></h3>
                        <p class="designation"><?php echo htmlspecialchars($trainer['designation']); ?></p>
                        <p class="specialization"><i class="fas fa-star"></i> <?php echo htmlspecialchars($trainer['specialization']); ?></p>
                    </div>
                </div>
                <div class="trainer-content">
                    <?php if ($trainer['bio']): ?>
                        <p><?php echo htmlspecialchars($trainer['bio']); ?></p>
                    <?php endif; ?>
                    <div class="trainer-details">
                        <div class="detail-item">
                            <i class="fas fa-briefcase"></i>
                            <span><?php echo htmlspecialchars($trainer['experience']); ?> Experience</span>
                        </div>
                        <?php if ($trainer['qualifications']): ?>
                        <div class="detail-item">
                            <i class="fas fa-graduation-cap"></i>
                            <span><?php echo htmlspecialchars($trainer['qualifications']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($trainer['email']): ?>
                        <div class="detail-item">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo htmlspecialchars($trainer['email']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($trainer['linkedin']): ?>
                        <div class="detail-item">
                            <i class="fab fa-linkedin"></i>
                            <a href="<?php echo htmlspecialchars($trainer['linkedin']); ?>" target="_blank">LinkedIn Profile</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
            </section>

<style>
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.empty-state i {
    font-size: 5rem;
    color: #bdc3c7;
    margin-bottom: 1rem;
}

.empty-state h3 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #7f8c8d;
    margin-bottom: 2rem;
}

.trainers-filter {
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

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
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

.search-input-wrapper i {
    position: absolute;
    right: 1rem;
    color: #666;
    pointer-events: none;
}

.results-count {
    text-align: center;
    color: #666;
    font-weight: 500;
    margin-top: 1rem;
}

.trainers-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 2rem;
}

.trainer-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.trainer-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.trainer-image {
    position: relative;
    height: 250px;
    overflow: hidden;
}

.trainer-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.trainer-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: #e74c3c;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.trainer-content {
    padding: 2rem;
}

.trainer-content h3 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
    font-size: 1.4rem;
}

.trainer-title {
    color: #3498db;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.trainer-department {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
}

.trainer-bio p {
    color: #555;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.trainer-details {
    margin-bottom: 2rem;
}

.detail-section {
    margin-bottom: 1.5rem;
}

.detail-section h4 {
    color: #2c3e50;
    font-size: 1rem;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.detail-section h4 i {
    color: #3498db;
    width: 16px;
}

.detail-section ul {
    list-style: none;
    padding-left: 1.5rem;
}

.detail-section ul li {
    color: #666;
    margin-bottom: 0.5rem;
    position: relative;
}

.detail-section ul li:before {
    content: "•";
    color: #3498db;
    position: absolute;
    left: -1rem;
}

.skills-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding-left: 1.5rem;
}

.skill-tag {
    background: #ecf0f1;
    color: #2c3e50;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 500;
}

.trainer-contact {
    text-align: center;
}

.contact-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #3498db;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 500;
    transition: background 0.3s ease;
}

.contact-btn:hover {
    background: #2980b9;
}

@media (max-width: 768px) {
    .trainers-container {
        grid-template-columns: 1fr;
    }
    
    .trainer-card {
        margin-bottom: 1rem;
    }
}
</style>

<?php include '../includes/public_footer.php'; ?>