<?php
define('SYSTEM_ACCESS', true);
$page_title = "Placements";
require_once __DIR__ . '/../includes/public_header.php';

$year_filter = $_GET['year'] ?? '';

$query = "SELECT * FROM placements WHERE is_active = 1";
$params = [];

if ($year_filter) {
    $query .= " AND placement_year = ?";
    $params[] = $year_filter;
}

$query .= " ORDER BY is_featured DESC, placement_year DESC, created_at DESC";
$placements = fetchAll($query, $params);

// Get available years
$years = fetchAll("SELECT DISTINCT placement_year FROM placements WHERE is_active = 1 ORDER BY placement_year DESC");

// Get statistics
$stats = fetchOne("
    SELECT 
        COUNT(*) as total,
        COUNT(DISTINCT company_name) as companies,
        COUNT(CASE WHEN placement_year = YEAR(CURDATE()) THEN 1 END) as this_year
    FROM placements WHERE is_active = 1
");
?>

<div class="container" style="padding: 3rem 2rem;">
    <div class="page-header" style="background: linear-gradient(135deg, #9b59b6, #2c3e50); color: white; padding: 3rem 2rem; border-radius: 10px; margin-bottom: 3rem; text-align: center;">
        <h1 style="font-size: 2.5rem; margin-bottom: 1rem;"><i class="fas fa-briefcase"></i> Our Placements</h1>
        <p style="font-size: 1.1rem; opacity: 0.9;">Success stories of our students</p>
    </div>

    <!-- Statistics -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
        <div style="background: #3498db; color: white; padding: 2rem; border-radius: 10px; text-align: center;">
            <h2 style="font-size: 2.5rem; margin-bottom: 0.5rem;"><?php echo number_format($stats['total']); ?></h2>
            <p style="margin: 0; opacity: 0.9;">Total Placements</p>
        </div>
        <div style="background: #27ae60; color: white; padding: 2rem; border-radius: 10px; text-align: center;">
            <h2 style="font-size: 2.5rem; margin-bottom: 0.5rem;"><?php echo number_format($stats['companies']); ?></h2>
            <p style="margin: 0; opacity: 0.9;">Companies</p>
        </div>
        <div style="background: #e67e22; color: white; padding: 2rem; border-radius: 10px; text-align: center;">
            <h2 style="font-size: 2.5rem; margin-bottom: 0.5rem;"><?php echo number_format($stats['this_year']); ?></h2>
            <p style="margin: 0; opacity: 0.9;">This Year</p>
        </div>
    </div>

    <!-- Year Filter -->
    <div style="margin-bottom: 2rem; text-align: center;">
        <a href="<?php echo url('placements'); ?>" class="btn <?php echo !$year_filter ? 'btn-primary' : 'btn-outline-primary'; ?>" style="margin: 0.25rem;">All Years</a>
        <?php foreach ($years as $year): ?>
            <a href="<?php echo url('placements?year=' . $year['placement_year']); ?>" 
               class="btn <?php echo $year_filter == $year['placement_year'] ? 'btn-primary' : 'btn-outline-primary'; ?>" 
               style="margin: 0.25rem;">
                <?php echo $year['placement_year']; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($placements)): ?>
        <div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: 10px;">
            <i class="fas fa-briefcase fa-4x" style="color: #bdc3c7; margin-bottom: 1.5rem;"></i>
            <h3 style="color: #7f8c8d;">No Placements Found</h3>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 2rem;">
            <?php foreach ($placements as $placement): ?>
                <div style="background: white; border-radius: 10px; padding: 2rem; box-shadow: 0 5px 15px rgba(0,0,0,0.1); transition: transform 0.3s ease; position: relative;">
                    <?php if ($placement['is_featured']): ?>
                        <div style="position: absolute; top: 1rem; right: 1rem; background: #f39c12; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                            <i class="fas fa-star"></i> Featured
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($placement['company_logo']): ?>
                        <div style="text-align: center; margin-bottom: 1.5rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                            <img src="<?php echo asset($placement['company_logo']); ?>" 
                                 alt="<?php echo htmlspecialchars($placement['company_name']); ?>" 
                                 style="max-height: 60px; max-width: 100%; object-fit: contain;">
                        </div>
                    <?php endif; ?>
                    
                    <h3 style="color: #2c3e50; font-size: 1.3rem; margin-bottom: 0.5rem;">
                        <?php echo htmlspecialchars($placement['student_name']); ?>
                    </h3>
                    
                    <div style="color: #7f8c8d; margin-bottom: 1rem;">
                        <p style="margin: 0.25rem 0;"><i class="fas fa-building" style="color: #3498db; width: 20px;"></i> <?php echo htmlspecialchars($placement['company_name']); ?></p>
                        <p style="margin: 0.25rem 0;"><i class="fas fa-user-tie" style="color: #3498db; width: 20px;"></i> <?php echo htmlspecialchars($placement['position']); ?></p>
                        <?php if ($placement['package']): ?>
                            <p style="margin: 0.25rem 0;"><i class="fas fa-money-bill-wave" style="color: #27ae60; width: 20px;"></i> <?php echo htmlspecialchars($placement['package']); ?></p>
                        <?php endif; ?>
                        <p style="margin: 0.25rem 0;"><i class="fas fa-calendar" style="color: #3498db; width: 20px;"></i> <?php echo $placement['placement_year']; ?></p>
                    </div>
                    
                    <?php if ($placement['description']): ?>
                        <p style="color: #555; font-size: 0.9rem; line-height: 1.6; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #ecf0f1;">
                            <?php echo htmlspecialchars($placement['description']); ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/public_footer.php'; ?>
