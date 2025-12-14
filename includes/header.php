<?php
/**
 * Header Template
 * Includes navigation bar
 */
if (!defined('ROOT_PATH')) {
    require_once dirname(__DIR__) . '/config/config.php';
}

$isLoggedIn = Auth::isLoggedIn();
$isAdmin = $isLoggedIn && RBAC::isAdmin();
$isSuperAdmin = $isLoggedIn && RBAC::isSuperAdmin();
$user = $isLoggedIn ? Auth::getUser() : null;

// Get base URL (function is defined in config.php)
$baseUrl = getBaseUrl();

$cssPath = $baseUrl . '/assets/css/style.css';
?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?php echo htmlspecialchars($cssPath); ?>">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <div class="nav-brand">
                    <a href="<?php echo htmlspecialchars(url('index.php')); ?>" title="Social Care Contracts Management">
                        <span>SCCM</span>
                    </a>
                </div>
                <button class="nav-toggle" aria-label="Toggle navigation menu" aria-expanded="false">
                    <span class="nav-toggle-icon"></span>
                    <span class="nav-toggle-icon"></span>
                    <span class="nav-toggle-icon"></span>
                </button>
                <ul class="nav-menu">
                    <?php if ($isLoggedIn): ?>
                        <!-- Main Navigation -->
                        <li><a href="<?php echo htmlspecialchars(url('people.php')); ?>"><?php echo ucfirst(htmlspecialchars(getPersonTerm(false))); ?></a></li>
                        
                        <!-- Contracts Dropdown -->
                        <li class="nav-dropdown">
                            <a href="#" class="nav-dropdown-toggle">Contracts <span class="dropdown-arrow">▼</span></a>
                            <ul class="nav-dropdown-menu">
                                <li><a href="<?php echo htmlspecialchars(url('contracts.php')); ?>">View Contracts</a></li>
                                <li><a href="<?php echo htmlspecialchars(url('contract-workflow.php')); ?>">Workflow Dashboard</a></li>
                                <?php if ($isAdmin): ?>
                                    <li><a href="<?php echo htmlspecialchars(url('tender-opportunities.php')); ?>">Tender Opportunities</a></li>
                                    <li><a href="<?php echo htmlspecialchars(url('tender-monitoring.php')); ?>">Tender Monitoring</a></li>
                                    <li><a href="<?php echo htmlspecialchars(url('tender-applications.php')); ?>">Tender Applications</a></li>
                                <?php endif; ?>
                                <li><a href="<?php echo htmlspecialchars(url('contract-types.php')); ?>">Contract Types</a></li>
                                <li><a href="<?php echo htmlspecialchars(url('rates.php')); ?>">Rates</a></li>
                                <li><a href="<?php echo htmlspecialchars(url('reports.php')); ?>">Reports</a></li>
                                <li><a href="<?php echo htmlspecialchars(url('ai-assistant.php')); ?>">AI Assistant</a></li>
                            </ul>
                        </li>
                        
                        <!-- Learn Dropdown -->
                        <li class="nav-dropdown">
                            <a href="#" class="nav-dropdown-toggle">Learn <span class="dropdown-arrow">▼</span></a>
                            <ul class="nav-dropdown-menu">
                                <li><a href="<?php echo htmlspecialchars(url('pages/why-use-this-system.php')); ?>"><strong>Why Use This System</strong></a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a href="<?php echo htmlspecialchars(url('pages/social-care-contracts-guide.php')); ?>">Contracts Guide</a></li>
                                <li><a href="<?php echo htmlspecialchars(url('pages/glossary.php')); ?>">Glossary</a></li>
                                <li><a href="<?php echo htmlspecialchars(url('pages/documentation.php')); ?>">Documentation</a></li>
                                <li><a href="<?php echo htmlspecialchars(url('pages/how-tos.php')); ?>">How-to Guides</a></li>
                                <li><a href="<?php echo htmlspecialchars(url('pages/faq.php')); ?>">FAQ</a></li>
                            </ul>
                        </li>
                        
                        <!-- News & Updates Dropdown -->
                        <li class="nav-dropdown">
                            <a href="#" class="nav-dropdown-toggle">News & Updates <span class="dropdown-arrow">▼</span></a>
                            <ul class="nav-dropdown-menu">
                                <li><a href="<?php echo htmlspecialchars(url('pages/local-authority-rates.php')); ?>">Local Authority Rates</a></li>
                                <li><a href="<?php echo htmlspecialchars(url('pages/articles.php')); ?>">Articles</a></li>
                                <li><a href="<?php echo htmlspecialchars(url('pages/updates.php')); ?>">Updates</a></li>
                                <li><a href="<?php echo htmlspecialchars(url('pages/changelog.php')); ?>">Changelog</a></li>
                            </ul>
                        </li>
                        
                        <!-- Admin Dropdown (for organisation admins) -->
                        <?php if ($isAdmin && !$isSuperAdmin): ?>
                            <li class="nav-dropdown">
                                <a href="#" class="nav-dropdown-toggle">Admin <span class="dropdown-arrow">▼</span></a>
                                <ul class="nav-dropdown-menu">
                                    <li><a href="<?php echo htmlspecialchars(url('organisation.php')); ?>">Organisation</a></li>
                                    <li><a href="<?php echo htmlspecialchars(url('users.php')); ?>">Users</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a href="<?php echo htmlspecialchars(url('teams.php')); ?>">Teams</a></li>
                                    <li><a href="<?php echo htmlspecialchars(url('teams-import.php')); ?>">Import Teams</a></li>
                                    <li><a href="<?php echo htmlspecialchars(url('rate-monitoring.php')); ?>">Rate Monitoring</a></li>
                                    <li><a href="<?php echo htmlspecialchars(url('local-authority-updates.php')); ?>">Rate Updates</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Super Admin Dropdown -->
                        <?php if ($isSuperAdmin): ?>
                            <li class="nav-dropdown">
                                <a href="#" class="nav-dropdown-toggle">Admin <span class="dropdown-arrow">▼</span></a>
                                <ul class="nav-dropdown-menu">
                                    <li><a href="<?php echo htmlspecialchars(url('organisation.php')); ?>">Organisation</a></li>
                                    <li><a href="<?php echo htmlspecialchars(url('users.php')); ?>">Users</a></li>
                                    <li><a href="<?php echo htmlspecialchars(url('superadmin.php')); ?>">Super Admin</a></li>
                                    <li><a href="<?php echo htmlspecialchars(url('seat-requests.php')); ?>">Seat Requests</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a href="<?php echo htmlspecialchars(url('teams.php')); ?>">Teams</a></li>
                                    <li><a href="<?php echo htmlspecialchars(url('teams-import.php')); ?>">Import Teams</a></li>
                                    <li><a href="<?php echo htmlspecialchars(url('rate-monitoring.php')); ?>">Rate Monitoring</a></li>
                                    <li><a href="<?php echo htmlspecialchars(url('local-authority-updates.php')); ?>">Rate Updates</a></li>
                                    <li><a href="<?php echo htmlspecialchars(url('glossary-suggestions.php')); ?>">Glossary Suggestions</a></li>
                                    <li><a href="<?php echo htmlspecialchars(url('glossary-manage.php')); ?>">Manage Glossary Terms</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                        
                        <!-- User Menu Dropdown -->
                        <li class="nav-dropdown nav-user-dropdown">
                            <a href="#" class="nav-dropdown-toggle">
                                <span class="user-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                                <span class="dropdown-arrow">▼</span>
                            </a>
                            <ul class="nav-dropdown-menu">
                                <li class="user-info">
                                    <div class="user-name-full"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                                    <div class="user-role">
                                        <?php 
                                        $roles = RBAC::getUserRoles();
                                        $formattedRoles = array_map(function($role) {
                                            // Replace underscores with spaces and capitalize each word
                                            return ucwords(str_replace('_', ' ', $role));
                                        }, $roles);
                                        echo htmlspecialchars(implode(', ', $formattedRoles));
                                        ?>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a href="<?php echo htmlspecialchars(url('index.php?view=public')); ?>"><i class="fa-solid fa-house" style="margin-right: 0.5rem;"></i>View Public Home</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a href="<?php echo htmlspecialchars(url('logout.php')); ?>">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li><a href="<?php echo htmlspecialchars(url('index.php')); ?>">Home</a></li>
                        <li><a href="<?php echo htmlspecialchars(url('login.php')); ?>">Login</a></li>
                        <li><a href="<?php echo htmlspecialchars(url('register.php')); ?>">Register</a></li>
                        
                        <!-- Learn Dropdown -->
                        <li class="nav-dropdown">
                            <a href="#" class="nav-dropdown-toggle">Learn <span class="dropdown-arrow">▼</span></a>
                            <ul class="nav-dropdown-menu">
                                <li><a href="<?php echo htmlspecialchars(url('pages/why-use-this-system.php')); ?>"><strong>Why Use This System</strong></a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a href="<?php echo htmlspecialchars(url('pages/social-care-contracts-guide.php')); ?>">Contracts Guide</a></li>
                                <li><a href="<?php echo htmlspecialchars(url('pages/glossary.php')); ?>">Glossary</a></li>
                                <li><a href="<?php echo htmlspecialchars(url('pages/documentation.php')); ?>">Documentation</a></li>
                                <li><a href="<?php echo htmlspecialchars(url('pages/how-tos.php')); ?>">How-to Guides</a></li>
                                <li><a href="<?php echo htmlspecialchars(url('pages/faq.php')); ?>">FAQ</a></li>
                            </ul>
                        </li>
                        
                        <!-- News & Updates Dropdown -->
                        <li class="nav-dropdown">
                            <a href="#" class="nav-dropdown-toggle">News & Updates <span class="dropdown-arrow">▼</span></a>
                            <ul class="nav-dropdown-menu">
                                <li><a href="<?php echo htmlspecialchars(url('pages/local-authority-rates.php')); ?>">Local Authority Rates</a></li>
                                <li><a href="<?php echo htmlspecialchars(url('pages/articles.php')); ?>">Articles</a></li>
                                <li><a href="<?php echo htmlspecialchars(url('pages/updates.php')); ?>">Updates</a></li>
                                <li><a href="<?php echo htmlspecialchars(url('pages/changelog.php')); ?>">Changelog</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>
    
    <script>
    // Mobile menu toggle
    document.addEventListener('DOMContentLoaded', function() {
        const navToggle = document.querySelector('.nav-toggle');
        const navMenu = document.querySelector('.nav-menu');
        const header = document.querySelector('header');
        
        // Calculate header height and set menu top position
        function setMenuTopPosition() {
            if (window.innerWidth <= 768 && header && navMenu) {
                const headerHeight = header.offsetHeight;
                navMenu.style.top = headerHeight + 'px';
                navMenu.style.maxHeight = 'calc(100vh - ' + headerHeight + 'px)';
            }
        }
        
        // Set initial position
        setMenuTopPosition();
        
        // Update on resize
        window.addEventListener('resize', setMenuTopPosition);
        
        if (navToggle && navMenu) {
            navToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                const isExpanded = navToggle.getAttribute('aria-expanded') === 'true';
                navToggle.setAttribute('aria-expanded', !isExpanded);
                navMenu.classList.toggle('nav-menu-active');
                navToggle.classList.toggle('nav-toggle-active');
                setMenuTopPosition();
            });
            
            // Handle dropdown toggles on mobile
            const dropdownToggles = document.querySelectorAll('.nav-dropdown-toggle');
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768) {
                        e.preventDefault();
                        const dropdown = this.closest('.nav-dropdown');
                        dropdown.classList.toggle('active');
                    }
                });
            });
            
            // Close menu when clicking outside
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 768) {
                    if (!navToggle.contains(event.target) && !navMenu.contains(event.target)) {
                        navMenu.classList.remove('nav-menu-active');
                        navToggle.classList.remove('nav-toggle-active');
                        navToggle.setAttribute('aria-expanded', 'false');
                    }
                }
            });
            
            // Close menu when clicking on a non-dropdown link (mobile)
            const navLinks = navMenu.querySelectorAll('a:not(.nav-dropdown-toggle)');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        navMenu.classList.remove('nav-menu-active');
                        navToggle.classList.remove('nav-toggle-active');
                        navToggle.setAttribute('aria-expanded', 'false');
                    }
                });
            });
            
            // Handle window resize
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    if (window.innerWidth > 768) {
                        navMenu.classList.remove('nav-menu-active');
                        navToggle.classList.remove('nav-toggle-active');
                        navToggle.setAttribute('aria-expanded', 'false');
                        // Reset dropdown states
                        document.querySelectorAll('.nav-dropdown').forEach(dropdown => {
                            dropdown.classList.remove('active');
                        });
                    }
                }, 250);
            });
        }
    });
    </script>
    
    <main class="container">
