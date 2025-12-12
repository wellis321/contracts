<?php
/**
 * Updates Page
 */
require_once dirname(__DIR__, 2) . '/config/config.php';

$pageTitle = 'Updates';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <h1>Updates</h1>
        <p style="color: var(--text-light); margin-top: 0.5rem;">System updates, announcements, and important information</p>
    </div>
    
    <div style="max-width: 900px; margin: 0 auto; padding-left: 2rem; padding-right: 2rem;">
        <style>
            .content-container ul,
            .content-container ol {
                list-style: disc;
                margin-left: 1.5rem;
                margin-bottom: 1rem;
                line-height: 1.8;
                padding-left: 0.5rem;
            }
            .content-container ol {
                list-style: decimal;
            }
            .content-container ul ul,
            .content-container ol ul {
                list-style: circle;
                margin-left: 1.5rem;
                margin-top: 0.5rem;
            }
            .content-container li {
                margin-bottom: 0.5rem;
            }
        </style>
        <div class="content-container">
        <div style="margin-bottom: 2rem;">
            <h2>Terminology Preferences Now Available</h2>
            <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 0.5rem;">
                <i class="fa-solid fa-calendar" style="margin-right: 0.5rem;"></i>
                <?php echo date('F Y'); ?>
            </p>
            <p>We're excited to announce that organisation administrators can now customise how people are referred to throughout the system. This allows your organisation to use terminology that aligns with your values and practices.</p>
            <p>To set your terminology preferences:</p>
            <ol>
                <li>Navigate to "Admin" → "Organisation"</li>
                <li>Scroll to the "Terminology Preferences" section</li>
                <li>Enter your preferred singular and plural terms</li>
                <li>Click "Update Terminology"</li>
            </ol>
            <p>Changes apply immediately throughout the entire system. Examples include "person we support" / "people we support", "service user" / "service users", "client" / "clients", or any other terminology your organisation prefers.</p>
        </div>
        
        <div style="margin-bottom: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
            <h2>Enhanced Navigation Menu</h2>
            <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 0.5rem;">
                <i class="fa-solid fa-calendar" style="margin-right: 0.5rem;"></i>
                <?php echo date('F Y'); ?>
            </p>
            <p>We've reorganised the navigation menu to make it easier to find content. Resources are now split into two sections:</p>
            <ul style="list-style: disc; margin-left: 1.5rem; line-height: 1.8;">
                <li><strong>Learn:</strong> Educational content including Contracts Guide, Glossary, Documentation, How-to Guides, and FAQ</li>
                <li><strong>News & Updates:</strong> Current information including Local Authority Rates, Articles, Updates, and Changelog</li>
            </ul>
            <p>This makes it easier to find what you're looking for, whether you need guidance or want to stay updated with the latest information.</p>
        </div>
        
        <div style="margin-bottom: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
            <h2>Comprehensive Documentation Now Available</h2>
            <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 0.5rem;">
                <i class="fa-solid fa-calendar" style="margin-right: 0.5rem;"></i>
                <?php echo date('F Y'); ?>
            </p>
            <p>We've significantly expanded our documentation to provide comprehensive guidance on using the system:</p>
            <ul style="list-style: disc; margin-left: 1.5rem; line-height: 1.8;">
                <li><strong>Documentation:</strong> Complete system guide covering all features, from registration to advanced reporting</li>
                <li><strong>How-to Guides:</strong> Step-by-step instructions for common tasks including contract creation, person management, rate setting, and more</li>
                <li><strong>FAQ:</strong> Expanded with answers to questions about all system features, terminology, permissions, and best practices</li>
            </ul>
            <p>All documentation is accessible from the "Learn" menu. Whether you're new to the system or looking for specific guidance, you'll find detailed information to help you make the most of the platform.</p>
        </div>
        
        <div style="margin-bottom: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
            <h2>Glossary System Launched</h2>
            <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 0.5rem;">
                <i class="fa-solid fa-calendar" style="margin-right: 0.5rem;"></i>
                <?php echo date('F Y', strtotime('-1 month')); ?>
            </p>
            <p>We've launched a comprehensive glossary system to help users understand technical terms related to social care and contracts. Features include:</p>
            <ul style="list-style: disc; margin-left: 1.5rem; line-height: 1.8;">
                <li>Searchable glossary with definitions of key terms</li>
                <li>User suggestions for new terms</li>
                <li>Administrative review and approval process</li>
                <li>Email notifications for suggestion outcomes</li>
            </ul>
            <p>Logged-in users can suggest new terms, and super administrators review and manage all glossary content. Visit the Glossary from the "Learn" menu to explore terms or suggest new ones.</p>
        </div>
        
        <div style="margin-bottom: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
            <h2>Mobile Optimisation Improvements</h2>
            <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 0.5rem;">
                <i class="fa-solid fa-calendar" style="margin-right: 0.5rem;"></i>
                <?php echo date('F Y', strtotime('-1 month')); ?>
            </p>
            <p>We've made significant improvements to the mobile experience:</p>
            <ul style="list-style: disc; margin-left: 1.5rem; line-height: 1.8;">
                <li>Enhanced responsive design for all pages</li>
                <li>Better touch targets (minimum 44px) for easier mobile interaction</li>
                <li>Optimised typography and spacing for smaller screens</li>
                <li>Improved form layouts for mobile devices</li>
                <li>Better card layouts that stack properly on mobile</li>
            </ul>
            <p>The system is now fully functional and easy to use on mobile phones, tablets, and desktop computers. All features are accessible regardless of device.</p>
        </div>
        
        <div style="margin-top: 2rem; padding: 1.5rem; background: var(--bg-light); border-radius: 0.5rem;">
            <h3 style="margin-top: 0;">Stay Updated</h3>
            <p style="margin-bottom: 0;">We regularly post updates about new features, improvements, and important announcements. Check back regularly or review the <a href="<?php echo htmlspecialchars(url('pages/changelog.php')); ?>">Changelog</a> for a complete version history.</p>
        </div>
        </div>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
