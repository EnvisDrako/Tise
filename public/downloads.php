<?php
define('SYSTEM_ACCESS', true);
$page_title = "Downloads";
require_once __DIR__ . '/../includes/public_header.php';

$category_filter = $_GET['category'] ?? '';

$query = "SELECT * FROM downloads WHERE is_active = 1";
$params = [];

if ($category_filter) {
    $query .= " AND category = ?";
    $params[] = $category_filter;
}

$query .= " ORDER BY category, created_at DESC";
$downloads = fetchAll($query, $params);

// Group by category
$grouped = [];
foreach ($downloads as $download) {
    $grouped[$download['category']][] = $download;
}
?>

<div class="container" style="padding: 3rem 2rem;">
    <div class="page-header" style="background: linear-gradient(135deg, #27ae60, #2c3e50); color: white; padding: 3rem 2rem; border-radius: 10px; margin-bottom: 3rem; text-align: center;">
        <h1 style="font-size: 2.5rem; margin-bottom: 1rem;"><i class="fas fa-download"></i> Downloads</h1>
        <p style="font-size: 1.1rem; opacity: 0.9;">Access important documents and resources</p>
    </div>

    <!-- Category Filter -->
    <div style="margin-bottom: 2rem; text-align: center;">
        <a href="<?php echo url('downloads'); ?>" class="btn <?php echo !$category_filter ? 'btn-primary' : 'btn-outline-primary'; ?>" style="margin: 0.25rem;">All</a>
        <a href="<?php echo url('downloads?category=syllabus'); ?>" class="btn <?php echo $category_filter === 'syllabus' ? 'btn-primary' : 'btn-outline-primary'; ?>" style="margin: 0.25rem;">Syllabus</a>
        <a href="<?php echo url('downloads?category=forms'); ?>" class="btn <?php echo $category_filter === 'forms' ? 'btn-primary' : 'btn-outline-primary'; ?>" style="margin: 0.25rem;">Forms</a>
        <a href="<?php echo url('downloads?category=brochures'); ?>" class="btn <?php echo $category_filter === 'brochures' ? 'btn-primary' : 'btn-outline-primary'; ?>" style="margin: 0.25rem;">Brochures</a>
        <a href="<?php echo url('downloads?category=prospectus'); ?>" class="btn <?php echo $category_filter === 'prospectus' ? 'btn-primary' : 'btn-outline-primary'; ?>" style="margin: 0.25rem;">Prospectus</a>
        <a href="<?php echo url('downloads?category=results'); ?>" class="btn <?php echo $category_filter === 'results' ? 'btn-primary' : 'btn-outline-primary'; ?>" style="margin: 0.25rem;">Results</a>
    </div>

    <?php if (empty($downloads)): ?>
        <div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: 10px;">
            <i class="fas fa-folder-open fa-4x" style="color: #bdc3c7; margin-bottom: 1.5rem;"></i>
            <h3 style="color: #7f8c8d;">No Downloads Available</h3>
        </div>
    <?php else: ?>
        <?php foreach ($grouped as $category => $items): ?>
            <div style="margin-bottom: 3rem;">
                <h3 style="color: #2c3e50; margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 2px solid #3498db;">
                    <i class="fas fa-folder"></i> <?php echo ucfirst($category); ?>
                </h3>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
                    <?php foreach ($items as $download): ?>
                        <div style="background: white; border-radius: 10px; padding: 1.5rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1); transition: transform 0.3s ease;">
                            <h4 style="color: #2c3e50; margin-bottom: 0.75rem; font-size: 1.1rem;">
                                <?php echo htmlspecialchars($download['title']); ?>
                            </h4>
                            
                            <?php if ($download['description']): ?>
                                <p style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 1rem;">
                                    <?php echo htmlspecialchars($download['description']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                                <?php if ($download['file_type'] === 'upload'): ?>
                                    <small style="color: #95a5a6;"><i class="fas fa-file"></i> <?php echo $download['file_size']; ?></small>
                                    <a href="<?php echo asset($download['file_path']); ?>" 
                                       class="btn btn-sm btn-success" download style="padding: 0.5rem 1rem; background: #27ae60; color: white; text-decoration: none; border-radius: 5px;">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                <?php else: ?>
                                    <small style="color: #95a5a6;"><i class="fas fa-link"></i> External</small>
                                    <a href="<?php echo htmlspecialchars($download['external_link']); ?>" 
                                       class="btn btn-sm btn-info" target="_blank" style="padding: 0.5rem 1rem; background: #3498db; color: white; text-decoration: none; border-radius: 5px;">
                                        <i class="fas fa-external-link-alt"></i> Open Link
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/public_footer.php'; ?>
