<?php
/**
 * Faculty Dashboard
 * Main dashboard for faculty users with institution-specific overview
 */

// Define system access constant
define('SYSTEM_ACCESS', true);

// Include faculty authorization
require_once __DIR__ . '/../../includes/auth_check_faculty.php';

// Page title
$pageTitle = 'Faculty Dashboard';

// Get institution details
$institution = getCurrentInstitution();
$institutionId = getCurrentUserInstitutionId();

// Get statistics
$stats = getInstitutionStudentStats($institutionId);
$recentActivities = getInstitutionRecentActivities($institutionId, 5);
$courseDistribution = getInstitutionCourseDistribution($institutionId);

// Render header
renderFacultyHeader($pageTitle);
?>

<div class="row">
    <div class="col-12">
        <h1 class="text-gradient mb-4">
            <i class="fas fa-tachometer-alt"></i> Faculty Dashboard
        </h1>
        <p class="lead">Welcome to the faculty portal, <?php echo htmlspecialchars(getCurrentUserName()); ?>!</p>
        <?php if ($institution): ?>
            <div class="alert alert-success">
                <i class="fas fa-university"></i>
                You are managing <strong><?php echo htmlspecialchars($institution['name']); ?></strong>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="stat-card position-relative">
            <div class="stat-number"><?php echo number_format($stats['total_students']); ?></div>
            <div class="stat-label">Total Students</div>
            <i class="fas fa-graduation-cap stat-icon"></i>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="stat-card position-relative">
            <div class="stat-number"><?php echo number_format($stats['exam_eligible']); ?></div>
            <div class="stat-label">Exam Eligible</div>
            <i class="fas fa-check-circle stat-icon"></i>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="stat-card position-relative">
            <div class="stat-number"><?php echo number_format($stats['total_marklists']); ?></div>
            <div class="stat-label">Marklists</div>
            <i class="fas fa-list-alt stat-icon"></i>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="stat-card position-relative">
            <div class="stat-number"><?php echo number_format($stats['total_certificates']); ?></div>
            <div class="stat-label">Certificates</div>
            <i class="fas fa-certificate stat-icon"></i>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-line"></i> Institution Overview
            </div>
            <div class="card-body">
                <?php if ($institution): ?>
                    <h5><?php echo htmlspecialchars($institution['name']); ?></h5>
                    <p><strong>Code:</strong> <?php echo htmlspecialchars($institution['code']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($institution['email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($institution['phone']); ?></p>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Institution details not found.
                    </div>
                <?php endif; ?>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Authentication system is working correctly! You are logged in as a faculty user.
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-tasks"></i> Quick Actions
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="register_student.php" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Register Student
                    </a>
                    <a href="students.php" class="btn btn-success">
                        <i class="fas fa-graduation-cap"></i> Manage Students
                    </a>
                    <a href="marklists.php" class="btn btn-info">
                        <i class="fas fa-list-alt"></i> Manage Marklists
                    </a>
                    <a href="certificates.php" class="btn btn-warning">
                        <i class="fas fa-certificate"></i> Certificate Requests
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-clock"></i> Recent Activities
            </div>
            <div class="card-body">
                <?php if (!empty($recentActivities)): ?>
                    <div class="activity-list">
                        <?php foreach ($recentActivities as $activity): ?>
                            <div class="activity-item d-flex align-items-center mb-2">
                                <div class="activity-icon me-3">
                                    <?php if ($activity['type'] === 'student'): ?>
                                        <i class="fas fa-user-plus text-success"></i>
                                    <?php else: ?>
                                        <i class="fas fa-certificate text-warning"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="activity-content flex-grow-1">
                                    <div class="activity-title"><?php echo htmlspecialchars($activity['name']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($activity['activity']); ?></small>
                                </div>
                                <div class="activity-date">
                                    <small class="text-muted"><?php echo date('M j', strtotime($activity['date'])); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>No recent activities</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-pie"></i> Course Distribution
            </div>
            <div class="card-body">
                <?php if (!empty($courseDistribution)): ?>
                    <div class="course-distribution">
                        <?php foreach ($courseDistribution as $course): ?>
                            <div class="course-item mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="course-name"><?php echo htmlspecialchars($course['course']); ?></span>
                                    <span class="badge bg-primary"><?php echo $course['student_count']; ?></span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <?php 
                                    $percentage = $stats['total_students'] > 0 ? ($course['student_count'] / $stats['total_students']) * 100 : 0;
                                    ?>
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                                <small class="text-muted">
                                    <?php echo $course['eligible_count']; ?> exam eligible
                                </small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted">
                        <i class="fas fa-graduation-cap fa-2x mb-2"></i>
                        <p>No courses found</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle"></i> Session Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>User ID:</strong> <?php echo getCurrentUserId(); ?><br>
                        <strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?><br>
                        <strong>Role:</strong> <?php echo htmlspecialchars(getCurrentUserRole()); ?><br>
                    </div>
                    <div class="col-md-6">
                        <strong>Institution ID:</strong> <?php echo getCurrentUserInstitutionId(); ?><br>
                        <strong>Login Time:</strong> <?php echo date('Y-m-d H:i:s', $_SESSION['login_time']); ?><br>
                        <strong>Last Activity:</strong> <?php echo date('Y-m-d H:i:s', $_SESSION['last_activity']); ?><br>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php renderFacultyFooter(); ?>