<?php
define('SYSTEM_ACCESS', true);
$page_title = "Notices & Announcements";
require_once __DIR__ . '/../includes/public_header.php';

// Get active notices
$notices = fetchAll("
    SELECT * FROM notices 
    WHERE is_active = 1 
    AND start_date <= CURDATE() 
    AND (end_date IS NULL OR end_date >= CURDATE())
    ORDER BY 
        CASE type 
            WHEN 'urgent' THEN 1 
            WHEN 'important' THEN 2 
            WHEN 'event' THEN 3 
            ELSE 4 
        END,
        created_at DESC
");
?>

<div class="container" style="padding: 3rem 2rem;">
    <div class="page-header" style="background: linear-gradient(135deg, #3498db, #2c3e50); color: white; padding: 3rem 2rem; border-radius: 10px; margin-bottom: 3rem; text-align: center;">
        <h1 style="font-size: 2.5rem; margin-bottom: 1rem;"><i class="fas fa-bullhorn"></i> Notices & Announcements</h1>
        <p style="font-size: 1.1rem; opacity: 0.9;">Stay updated with the latest news and announcements</p>
    </div>

    <?php if (empty($notices)): ?>
        <div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <i class="fas fa-inbox fa-4x" style="color: #bdc3c7; margin-bottom: 1.5rem;"></i>
            <h3 style="color: #7f8c8d;">No Active Notices</h3>
            <p style="color: #95a5a6;">There are currently no notices to display.</p>
        </div>
    <?php else: ?>
        <div style="display: grid; gap: 2rem;">
            <?php foreach ($notices as $notice): ?>
                <div class="notice-card" style="background: white; border-radius: 10px; padding: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-left: 5px solid <?php 
                    echo $notice['type'] === 'urgent' ? '#e74c3c' : 
                        ($notice['type'] === 'important' ? '#f39c12' : 
                        ($notice['type'] === 'event' ? '#3498db' : '#95a5a6')); 
                ?>;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                        <div>
                            <h2 style="color: #2c3e50; margin-bottom: 0.5rem; font-size: 1.5rem;">
                                <?php echo htmlspecialchars($notice['title']); ?>
                            </h2>
                            <div style="display: flex; gap: 1rem; align-items: center;">
                                <span style="padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; background: <?php 
                                    echo $notice['type'] === 'urgent' ? '#e74c3c' : 
                                        ($notice['type'] === 'important' ? '#f39c12' : 
                                        ($notice['type'] === 'event' ? '#3498db' : '#95a5a6')); 
                                ?>; color: white;">
                                    <?php echo ucfirst($notice['type']); ?>
                                </span>
                                <small style="color: #7f8c8d;">
                                    <i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($notice['start_date'])); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div style="color: #555; line-height: 1.8; margin-bottom: 1rem;">
                        <?php echo nl2br(htmlspecialchars($notice['content'])); ?>
                    </div>
                    
                    <?php if ($notice['attachment_path']): ?>
                        <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #ecf0f1;">
                            <a href="<?php echo asset($notice['attachment_path']); ?>" 
                               class="btn btn-primary" download style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: #3498db; color: white; text-decoration: none; border-radius: 5px;">
                                <i class="fas fa-paperclip"></i> Download Attachment
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/public_footer.php'; ?>
