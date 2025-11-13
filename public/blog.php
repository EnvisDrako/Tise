<?php
// Define system access for includes
define('SYSTEM_ACCESS', true);

$page_title = "Blog & News";
$page_description = "Stay updated with the latest news, insights, and updates from our institution";
include '../includes/public_header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Blog & News</h1>
        <p>Stay updated with our latest news, insights, and educational content</p>
    </div>
</div>

<section class="blog-filter section">
    <div class="container">
        <div class="filter-controls">
            <div class="filter-group">
                <label for="category-filter">Category:</label>
                <select id="category-filter" class="filter-select" data-filter="category">
                    <option value="all">All Categories</option>
                    <option value="news">News & Updates</option>
                    <option value="education">Educational</option>
                    <option value="technology">Technology</option>
                    <option value="career">Career Guidance</option>
                    <option value="events">Events</option>
                    <option value="success-stories">Success Stories</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="date-filter">Date:</label>
                <select id="date-filter" class="filter-select" data-filter="dateRange">
                    <option value="all">All Time</option>
                    <option value="recent">Last Month</option>
                    <option value="2024">2024</option>
                    <option value="2023">2023</option>
                    <option value="2022">2022</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="tags-filter">Tags:</label>
                <select id="tags-filter" class="filter-select" data-filter="tags">
                    <option value="all">All Tags</option>
                    <option value="programming">Programming</option>
                    <option value="design">Design</option>
                    <option value="marketing">Marketing</option>
                    <option value="analytics">Analytics</option>
                    <option value="tips">Tips & Tricks</option>
                    <option value="industry">Industry Trends</option>
                </select>
            </div>
            <div class="search-group">
                <input type="text" class="search-input" placeholder="Search articles by title, content, or tags...">
                <i class="fas fa-search"></i>
            </div>
        </div>
        <div class="results-count">Showing all articles</div>
    </div>
</section>

<section class="blog-grid section">
    <div class="container">
        <div class="blog-container">
            <!-- Blog Article 1 -->
            <article class="blog-card filterable-item searchable-item" 
                     data-category="technology" 
                     data-date-range="recent" 
                     data-tags="programming">
                <div class="blog-image">
                    <img src="/images/blog-web-trends-2024.jpg" alt="Web Development Trends 2024">
                    <div class="blog-category">Technology</div>
                </div>
                <div class="blog-content">
                    <div class="blog-meta">
                        <span class="blog-date">
                            <i class="fas fa-calendar"></i>
                            December 15, 2024
                        </span>
                        <span class="blog-author">
                            <i class="fas fa-user"></i>
                            Prof. John Smith
                        </span>
                    </div>
                    <h3><a href="/blog/web-development-trends-2024">Top Web Development Trends to Watch in 2024</a></h3>
                    <p>Discover the latest trends shaping web development in 2024, from AI integration to progressive web apps and modern JavaScript frameworks.</p>
                    <div class="blog-tags">
                        <span class="tag">Programming</span>
                        <span class="tag">Web Development</span>
                        <span class="tag">Trends</span>
                    </div>
                    <div class="blog-footer">
                        <a href="/blog/web-development-trends-2024" class="read-more">Read More</a>
                        <div class="blog-stats">
                            <span><i class="fas fa-eye"></i> 1,234</span>
                            <span><i class="fas fa-comment"></i> 23</span>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Blog Article 2 -->
            <article class="blog-card filterable-item searchable-item" 
                     data-category="career" 
                     data-date-range="recent" 
                     data-tags="tips">
                <div class="blog-image">
                    <img src="/images/blog-career-tips.jpg" alt="Career Tips for Tech Professionals">
                    <div class="blog-category">Career Guidance</div>
                </div>
                <div class="blog-content">
                    <div class="blog-meta">
                        <span class="blog-date">
                            <i class="fas fa-calendar"></i>
                            December 10, 2024
                        </span>
                        <span class="blog-author">
                            <i class="fas fa-user"></i>
                            Ms. Sarah Wilson
                        </span>
                    </div>
                    <h3><a href="/blog/career-tips-tech-professionals">Essential Career Tips for Tech Professionals</a></h3>
                    <p>Learn how to advance your tech career with practical tips on skill development, networking, and building a strong professional portfolio.</p>
                    <div class="blog-tags">
                        <span class="tag">Career</span>
                        <span class="tag">Tips & Tricks</span>
                        <span class="tag">Professional Development</span>
                    </div>
                    <div class="blog-footer">
                        <a href="/blog/career-tips-tech-professionals" class="read-more">Read More</a>
                        <div class="blog-stats">
                            <span><i class="fas fa-eye"></i> 987</span>
                            <span><i class="fas fa-comment"></i> 15</span>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Blog Article 3 -->
            <article class="blog-card filterable-item searchable-item" 
                     data-category="education" 
                     data-date-range="2024" 
                     data-tags="analytics">
                <div class="blog-image">
                    <img src="/images/blog-data-science-guide.jpg" alt="Data Science Learning Guide">
                    <div class="blog-category">Educational</div>
                </div>
                <div class="blog-content">
                    <div class="blog-meta">
                        <span class="blog-date">
                            <i class="fas fa-calendar"></i>
                            December 5, 2024
                        </span>
                        <span class="blog-author">
                            <i class="fas fa-user"></i>
                            Dr. Michael Chen
                        </span>
                    </div>
                    <h3><a href="/blog/complete-data-science-learning-guide">Complete Guide to Learning Data Science in 2024</a></h3>
                    <p>A comprehensive roadmap for aspiring data scientists, covering essential skills, tools, and career paths in the data science field.</p>
                    <div class="blog-tags">
                        <span class="tag">Data Science</span>
                        <span class="tag">Analytics</span>
                        <span class="tag">Learning Guide</span>
                    </div>
                    <div class="blog-footer">
                        <a href="/blog/complete-data-science-learning-guide" class="read-more">Read More</a>
                        <div class="blog-stats">
                            <span><i class="fas fa-eye"></i> 2,156</span>
                            <span><i class="fas fa-comment"></i> 42</span>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Blog Article 4 -->
            <article class="blog-card filterable-item searchable-item" 
                     data-category="news" 
                     data-date-range="recent" 
                     data-tags="industry">
                <div class="blog-image">
                    <img src="/images/blog-new-partnership.jpg" alt="New Industry Partnership">
                    <div class="blog-category">News & Updates</div>
                </div>
                <div class="blog-content">
                    <div class="blog-meta">
                        <span class="blog-date">
                            <i class="fas fa-calendar"></i>
                            November 28, 2024
                        </span>
                        <span class="blog-author">
                            <i class="fas fa-user"></i>
                            Admin Team
                        </span>
                    </div>
                    <h3><a href="/blog/new-industry-partnerships-2024">Announcing New Industry Partnerships for 2024</a></h3>
                    <p>We're excited to announce new partnerships with leading tech companies that will provide enhanced internship and job opportunities for our students.</p>
                    <div class="blog-tags">
                        <span class="tag">Partnership</span>
                        <span class="tag">Industry Trends</span>
                        <span class="tag">Opportunities</span>
                    </div>
                    <div class="blog-footer">
                        <a href="/blog/new-industry-partnerships-2024" class="read-more">Read More</a>
                        <div class="blog-stats">
                            <span><i class="fas fa-eye"></i> 1,567</span>
                            <span><i class="fas fa-comment"></i> 28</span>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Blog Article 5 -->
            <article class="blog-card filterable-item searchable-item" 
                     data-category="events" 
                     data-date-range="recent" 
                     data-tags="design">
                <div class="blog-image">
                    <img src="/images/blog-design-workshop.jpg" alt="Design Workshop Event">
                    <div class="blog-category">Events</div>
                </div>
                <div class="blog-content">
                    <div class="blog-meta">
                        <span class="blog-date">
                            <i class="fas fa-calendar"></i>
                            November 20, 2024
                        </span>
                        <span class="blog-author">
                            <i class="fas fa-user"></i>
                            Ms. Jennifer Lee
                        </span>
                    </div>
                    <h3><a href="/blog/upcoming-design-workshop-december">Upcoming Design Workshop: Modern UI/UX Principles</a></h3>
                    <p>Join us for an intensive workshop on modern UI/UX design principles, featuring hands-on exercises and industry best practices.</p>
                    <div class="blog-tags">
                        <span class="tag">Workshop</span>
                        <span class="tag">Design</span>
                        <span class="tag">UI/UX</span>
                    </div>
                    <div class="blog-footer">
                        <a href="/blog/upcoming-design-workshop-december" class="read-more">Read More</a>
                        <div class="blog-stats">
                            <span><i class="fas fa-eye"></i> 892</span>
                            <span class="fas fa-comment"></i> 12</span>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Blog Article 6 -->
            <article class="blog-card filterable-item searchable-item" 
                     data-category="success-stories" 
                     data-date-range="2024" 
                     data-tags="marketing">
                <div class="blog-image">
                    <img src="/images/blog-student-success.jpg" alt="Student Success Story">
                    <div class="blog-category">Success Stories</div>
                </div>
                <div class="blog-content">
                    <div class="blog-meta">
                        <span class="blog-date">
                            <i class="fas fa-calendar"></i>
                            November 15, 2024
                        </span>
                        <span class="blog-author">
                            <i class="fas fa-user"></i>
                            Ms. Emily Davis
                        </span>
                    </div>
                    <h3><a href="/blog/student-success-digital-marketing">From Student to Marketing Manager: Emily's Journey</a></h3>
                    <p>Read about Emily's inspiring journey from our digital marketing course to becoming a successful marketing manager at a leading agency.</p>
                    <div class="blog-tags">
                        <span class="tag">Success Story</span>
                        <span class="tag">Marketing</span>
                        <span class="tag">Career Growth</span>
                    </div>
                    <div class="blog-footer">
                        <a href="/blog/student-success-digital-marketing" class="read-more">Read More</a>
                        <div class="blog-stats">
                            <span><i class="fas fa-eye"></i> 1,345</span>
                            <span><i class="fas fa-comment"></i> 31</span>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </div>
</section>

<?php include '../includes/public_footer.php'; ?>