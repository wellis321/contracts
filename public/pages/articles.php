<?php
/**
 * Articles Page
 */
require_once dirname(__DIR__, 2) . '/config/config.php';

$pageTitle = 'Articles';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <h1>Articles</h1>
    </div>
    
    <div style="max-width: 800px;">
        <p>Articles and updates will be posted here in the future. Check back regularly for tips, best practices, and news about the Social Care Contracts Management Application.</p>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
