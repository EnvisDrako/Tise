<?php
// Define system access for includes
define('SYSTEM_ACCESS', true);

$page_title = "Our Trainers";
$page_description = "Meet our experienced faculty and industry experts who guide students towards success";
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
            <div class="search-group">
                <input type="text" class="search-input" placeholder="Search trainers by name or expertise...">
                <i class="fas fa-search"></i>
            </div>
        </div>
        <div class="results-count">Showing all trainers</div>
    </div>
</section>

<section class="trainers-grid section">
    <div class="container">
        <div class="trainers-container">
            <!-- Trainer 1 -->
            <div class="trainer-card filterable-item searchable-item" 
                 data-department="technical" 
                 data-experience="expert">
                <div class="trainer-image">
                    <img src="/images/trainer-john-smith.jpg" alt="Prof. John Smith" data-src="/images/trainer-john-smith.jpg" class="lazy">
                    <div class="trainer-badge">Lead Faculty</div>
                </div>
                <div class="trainer-content">
                    <h3>Prof. John Smith</h3>
                    <div class="trainer-title">Senior Web Development Instructor</div>
                    <div class="trainer-department">Technical Department</div>
                    
                    <div class="trainer-bio">
                        <p>With over 15 years of industry experience, Prof. Smith has worked with leading tech companies and now shares his expertise in full-stack development, JavaScript frameworks, and modern web technologies.</p>
                    </div>
                    
                    <div class="trainer-details">
                        <div class="detail-section">
                            <h4><i class="fas fa-graduation-cap"></i> Qualifications</h4>
                            <ul>
                                <li>M.S. Computer Science - Stanford University</li>
                                <li>B.Tech Information Technology - MIT</li>
                                <li>Certified AWS Solutions Architect</li>
                            </ul>
                        </div>
                        
                        <div class="detail-section">
                            <h4><i class="fas fa-briefcase"></i> Experience</h4>
                            <ul>
                                <li>15+ years in software development</li>
                                <li>Former Senior Developer at Google</li>
                                <li>5 years teaching experience</li>
                            </ul>
                        </div>
                        
                        <div class="detail-section">
                            <h4><i class="fas fa-code"></i> Expertise</h4>
                            <div class="skills-tags">
                                <span class="skill-tag">JavaScript</span>
                                <span class="skill-tag">React</span>
                                <span class="skill-tag">Node.js</span>
                                <span class="skill-tag">Python</span>
                                <span class="skill-tag">AWS</span>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h4><i class="fas fa-chalkboard-teacher"></i> Courses Teaching</h4>
                            <ul>
                                <li>Full Stack Web Development</li>
                                <li>Advanced JavaScript</li>
                                <li>Cloud Computing Fundamentals</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="trainer-contact">
                        <a href="mailto:john.smith@eduportal.com" class="contact-btn">
                            <i class="fas fa-envelope"></i> Contact
                        </a>
                    </div>
                </div>
            </div>

            <!-- Trainer 2 -->
            <div class="trainer-card filterable-item searchable-item" 
                 data-department="technical" 
                 data-experience="senior">
                <div class="trainer-image">
                    <img src="/images/trainer-sarah-wilson.jpg" alt="Ms. Sarah Wilson" data-src="/images/trainer-sarah-wilson.jpg" class="lazy">
                </div>
                <div class="trainer-content">
                    <h3>Ms. Sarah Wilson</h3>
                    <div class="trainer-title">Data Science & Analytics Instructor</div>
                    <div class="trainer-department">Technical Department</div>
                    
                    <div class="trainer-bio">
                        <p>Sarah brings 12 years of experience in data science and machine learning from top consulting firms. She specializes in making complex statistical concepts accessible to students.</p>
                    </div>
                    
                    <div class="trainer-details">
                        <div class="detail-section">
                            <h4><i class="fas fa-graduation-cap"></i> Qualifications</h4>
                            <ul>
                                <li>Ph.D. Statistics - Harvard University</li>
                                <li>M.S. Data Science - UC Berkeley</li>
                                <li>Certified Data Scientist (CDS)</li>
                            </ul>
                        </div>
                        
                        <div class="detail-section">
                            <h4><i class="fas fa-briefcase"></i> Experience</h4>
                            <ul>
                                <li>12 years in data analytics</li>
                                <li>Former Data Scientist at McKinsey</li>
                                <li>3 years teaching experience</li>
                            </ul>
                        </div>
                        
                        <div class="detail-section">
                            <h4><i class="fas fa-chart-bar"></i> Expertise</h4>
                            <div class="skills-tags">
                                <span class="skill-tag">Python</span>
                                <span class="skill-tag">R</span>
                                <span class="skill-tag">Machine Learning</span>
                                <span class="skill-tag">Tableau</span>
                                <span class="skill-tag">SQL</span>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h4><i class="fas fa-chalkboard-teacher"></i> Courses Teaching</h4>
                            <ul>
                                <li>Data Science & Analytics</li>
                                <li>Machine Learning Fundamentals</li>
                                <li>Business Intelligence</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="trainer-contact">
                        <a href="mailto:sarah.wilson@eduportal.com" class="contact-btn">
                            <i class="fas fa-envelope"></i> Contact
                        </a>
                    </div>
                </div>
            </div>

            <!-- Trainer 3 -->
            <div class="trainer-card filterable-item searchable-item" 
                 data-department="business" 
                 data-experience="senior">
                <div class="trainer-image">
                    <img src="/images/trainer-emily-davis.jpg" alt="Ms. Emily Davis" data-src="/images/trainer-emily-davis.jpg" class="lazy">
                    <div class="trainer-badge">Industry Expert</div>
                </div>
                <div class="trainer-content">
                    <h3>Ms. Emily Davis</h3>
                    <div class="trainer-title">Digital Marketing Specialist</div>
                    <div class="trainer-department">Business Department</div>
                    
                    <div class="trainer-bio">
                        <p>Emily has 10 years of hands-on experience in digital marketing, having managed campaigns for Fortune 500 companies. She brings real-world insights to the classroom.</p>
                    </div>
                    
                    <div class="trainer-details">
                        <div class="detail-section">
                            <h4><i class="fas fa-graduation-cap"></i> Qualifications</h4>
                            <ul>
                                <li>MBA Marketing - Wharton School</li>
                                <li>B.A. Communications - NYU</li>
                                <li>Google Ads & Analytics Certified</li>
                            </ul>
                        </div>
                        
                        <div class="detail-section">
                            <h4><i class="fas fa-briefcase"></i> Experience</h4>
                            <ul>
                                <li>10 years in digital marketing</li>
                                <li>Former Marketing Director at Adobe</li>
                                <li>4 years teaching experience</li>
                            </ul>
                        </div>
                        
                        <div class="detail-section">
                            <h4><i class="fas fa-bullhorn"></i> Expertise</h4>
                            <div class="skills-tags">
                                <span class="skill-tag">SEO/SEM</span>
                                <span class="skill-tag">Social Media</span>
                                <span class="skill-tag">Content Strategy</span>
                                <span class="skill-tag">Google Ads</span>
                                <span class="skill-tag">Analytics</span>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h4><i class="fas fa-chalkboard-teacher"></i> Courses Teaching</h4>
                            <ul>
                                <li>Digital Marketing Mastery</li>
                                <li>Social Media Marketing</li>
                                <li>Content Marketing Strategy</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="trainer-contact">
                        <a href="mailto:emily.davis@eduportal.com" class="contact-btn">
                            <i class="fas fa-envelope"></i> Contact
                        </a>
                    </div>
                </div>
            </div>

            <!-- Trainer 4 -->
            <div class="trainer-card filterable-item searchable-item" 
                 data-department="creative" 
                 data-experience="senior">
                <div class="trainer-image">
                    <img src="/images/trainer-jennifer-lee.jpg" alt="Ms. Jennifer Lee" data-src="/images/trainer-jennifer-lee.jpg" class="lazy">
                </div>
                <div class="trainer-content">
                    <h3>Ms. Jennifer Lee</h3>
                    <div class="trainer-title">Creative Design Instructor</div>
                    <div class="trainer-department">Creative Department</div>
                    
                    <div class="trainer-bio">
                        <p>Jennifer is an award-winning graphic designer with 11 years of experience in branding and visual communication. Her work has been featured in major design publications.</p>
                    </div>
                    
                    <div class="trainer-details">
                        <div class="detail-section">
                            <h4><i class="fas fa-graduation-cap"></i> Qualifications</h4>
                            <ul>
                                <li>M.F.A. Graphic Design - RISD</li>
                                <li>B.A. Visual Arts - Parsons</li>
                                <li>Adobe Certified Expert (ACE)</li>
                            </ul>
                        </div>
                        
                        <div class="detail-section">
                            <h4><i class="fas fa-briefcase"></i> Experience</h4>
                            <ul>
                                <li>11 years in graphic design</li>
                                <li>Former Creative Director at Ogilvy</li>
                                <li>6 years teaching experience</li>
                            </ul>
                        </div>
                        
                        <div class="detail-section">
                            <h4><i class="fas fa-palette"></i> Expertise</h4>
                            <div class="skills-tags">
                                <span class="skill-tag">Adobe Creative Suite</span>
                                <span class="skill-tag">Branding</span>
                                <span class="skill-tag">Typography</span>
                                <span class="skill-tag">UI/UX Design</span>
                                <span class="skill-tag">Print Design</span>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h4><i class="fas fa-chalkboard-teacher"></i> Courses Teaching</h4>
                            <ul>
                                <li>Graphic Design & Visual Arts</li>
                                <li>Brand Identity Design</li>
                                <li>UI/UX Design Fundamentals</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="trainer-contact">
                        <a href="mailto:jennifer.lee@eduportal.com" class="contact-btn">
                            <i class="fas fa-envelope"></i> Contact
                        </a>
                    </div>
                </div>
            </div>

            <!-- Trainer 5 -->
            <div class="trainer-card filterable-item searchable-item" 
                 data-department="language" 
                 data-experience="junior">
                <div class="trainer-image">
                    <img src="/images/trainer-rachel-green.jpg" alt="Ms. Rachel Green" data-src="/images/trainer-rachel-green.jpg" class="lazy">
                </div>
                <div class="trainer-content">
                    <h3>Ms. Rachel Green</h3>
                    <div class="trainer-title">English Communication Instructor</div>
                    <div class="trainer-department">Language Department</div>
                    
                    <div class="trainer-bio">
                        <p>Rachel specializes in business English and communication skills with 8 years of experience in corporate training and language instruction.</p>
                    </div>
                    
                    <div class="trainer-details">
                        <div class="detail-section">
                            <h4><i class="fas fa-graduation-cap"></i> Qualifications</h4>
                            <ul>
                                <li>M.A. Applied Linguistics - Oxford</li>
                                <li>B.A. English Literature - Cambridge</li>
                                <li>TESOL Certification</li>
                            </ul>
                        </div>
                        
                        <div class="detail-section">
                            <h4><i class="fas fa-briefcase"></i> Experience</h4>
                            <ul>
                                <li>8 years in language instruction</li>
                                <li>Former Corporate Trainer at IBM</li>
                                <li>5 years teaching experience</li>
                            </ul>
                        </div>
                        
                        <div class="detail-section">
                            <h4><i class="fas fa-comments"></i> Expertise</h4>
                            <div class="skills-tags">
                                <span class="skill-tag">Business English</span>
                                <span class="skill-tag">Public Speaking</span>
                                <span class="skill-tag">Writing Skills</span>
                                <span class="skill-tag">Presentation Skills</span>
                                <span class="skill-tag">Interview Prep</span>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h4><i class="fas fa-chalkboard-teacher"></i> Courses Teaching</h4>
                            <ul>
                                <li>Business English Communication</li>
                                <li>Public Speaking & Presentation</li>
                                <li>Professional Writing</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="trainer-contact">
                        <a href="mailto:rachel.green@eduportal.com" class="contact-btn">
                            <i class="fas fa-envelope"></i> Contact
                        </a>
                    </div>
                </div>
            </div>

            <!-- Trainer 6 -->
            <div class="trainer-card filterable-item searchable-item" 
                 data-department="business" 
                 data-experience="expert">
                <div class="trainer-image">
                    <img src="/images/trainer-mark-johnson.jpg" alt="Prof. Mark Johnson" data-src="/images/trainer-mark-johnson.jpg" class="lazy">
                    <div class="trainer-badge">Department Head</div>
                </div>
                <div class="trainer-content">
                    <h3>Prof. Mark Johnson</h3>
                    <div class="trainer-title">Business Analytics Professor</div>
                    <div class="trainer-department">Business Department</div>
                    
                    <div class="trainer-bio">
                        <p>Prof. Johnson leads our business analytics program with 18 years of experience in data-driven business strategy and analytics consulting for major corporations.</p>
                    </div>
                    
                    <div class="trainer-details">
                        <div class="detail-section">
                            <h4><i class="fas fa-graduation-cap"></i> Qualifications</h4>
                            <ul>
                                <li>Ph.D. Business Analytics - MIT Sloan</li>
                                <li>MBA Finance - Harvard Business School</li>
                                <li>Certified Analytics Professional (CAP)</li>
                            </ul>
                        </div>
                        
                        <div class="detail-section">
                            <h4><i class="fas fa-briefcase"></i> Experience</h4>
                            <ul>
                                <li>18 years in business analytics</li>
                                <li>Former Principal at Deloitte</li>
                                <li>8 years teaching experience</li>
                            </ul>
                        </div>
                        
                        <div class="detail-section">
                            <h4><i class="fas fa-chart-line"></i> Expertise</h4>
                            <div class="skills-tags">
                                <span class="skill-tag">Business Intelligence</span>
                                <span class="skill-tag">Excel Advanced</span>
                                <span class="skill-tag">Tableau</span>
                                <span class="skill-tag">Power BI</span>
                                <span class="skill-tag">SQL</span>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h4><i class="fas fa-chalkboard-teacher"></i> Courses Teaching</h4>
                            <ul>
                                <li>Business Analytics & Intelligence</li>
                                <li>Advanced Excel for Business</li>
                                <li>Data Visualization</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="trainer-contact">
                        <a href="mailto:mark.johnson@eduportal.com" class="contact-btn">
                            <i class="fas fa-envelope"></i> Contact
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.trainers-filter {
    background: #f8f9fa;
    padding: 2rem 0;
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