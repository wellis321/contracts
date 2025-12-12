    </main>
    
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About</h3>
                    <p><?php echo APP_NAME; ?> helps social care providers in Scotland manage their contracts, track rates, and handle various payment methods.</p>
                </div>
                <div class="footer-section">
                    <h3>Resources</h3>
                    <ul>
                        <li><a href="<?php echo htmlspecialchars(url('pages/social-care-contracts-guide.php')); ?>">Contracts Guide</a></li>
                        <li><a href="<?php echo htmlspecialchars(url('pages/glossary.php')); ?>">Glossary</a></li>
                        <li><a href="<?php echo htmlspecialchars(url('pages/documentation.php')); ?>">Documentation</a></li>
                        <li><a href="<?php echo htmlspecialchars(url('pages/how-tos.php')); ?>">How-to Guides</a></li>
                        <li><a href="<?php echo htmlspecialchars(url('pages/faq.php')); ?>">FAQ</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Information</h3>
                    <ul>
                        <li><a href="<?php echo htmlspecialchars(url('pages/local-authority-rates.php')); ?>">Local Authority Rates</a></li>
                        <li><a href="<?php echo htmlspecialchars(url('pages/changelog.php')); ?>">Changelog</a></li>
                        <li><a href="<?php echo htmlspecialchars(url('pages/articles.php')); ?>">Articles</a></li>
                        <li><a href="<?php echo htmlspecialchars(url('pages/updates.php')); ?>">Updates</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
                <p style="margin-top: 0.5rem; font-size: 0.875rem;">
                    <a href="<?php echo htmlspecialchars(url('pages/privacy-policy.php')); ?>" style="color: #9ca3af; text-decoration: none; margin: 0 0.5rem;">Privacy Policy</a>
                    <span style="color: #9ca3af;">|</span>
                    <a href="<?php echo htmlspecialchars(url('pages/terms.php')); ?>" style="color: #9ca3af; text-decoration: none; margin: 0 0.5rem;">Terms of Service</a>
                    <span style="color: #9ca3af;">|</span>
                    <a href="<?php echo htmlspecialchars(url('pages/cookie-policy.php')); ?>" style="color: #9ca3af; text-decoration: none; margin: 0 0.5rem;">Cookie Policy</a>
                </p>
            </div>
        </div>
    </footer>
    
    <!-- Cookie Consent Banner -->
    <div id="cookieBanner" class="cookie-banner" style="display: none;">
        <div class="cookie-banner-content">
            <div class="cookie-banner-text">
                <i class="fa-solid fa-cookie-bite" style="margin-right: 0.75rem; color: var(--primary-color); font-size: 1.25rem;"></i>
                <div>
                    <strong>We use cookies</strong>
                    <p style="margin: 0.25rem 0 0 0; font-size: 0.9rem; color: var(--text-light);">
                        This website uses cookies to ensure you get the best experience. We use essential cookies for site functionality and session management. 
                        <a href="<?php echo htmlspecialchars(url('pages/cookie-policy.php')); ?>" style="color: var(--primary-color); text-decoration: underline;">Learn more</a>
                    </p>
                </div>
            </div>
            <div class="cookie-banner-actions">
                <button onclick="acceptCookies()" class="btn btn-primary" style="white-space: nowrap;">
                    Accept
                </button>
                <button onclick="declineCookies()" class="btn btn-secondary" style="white-space: nowrap;">
                    Decline
                </button>
            </div>
        </div>
    </div>
    
    <style>
    .cookie-banner {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: var(--bg-color);
        border-top: 1px solid var(--border-color);
        box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1), 0 -2px 4px -1px rgba(0, 0, 0, 0.06);
        z-index: 1000;
        padding: 1rem;
        animation: slideUp 0.3s ease-out;
    }
    
    @keyframes slideUp {
        from {
            transform: translateY(100%);
        }
        to {
            transform: translateY(0);
        }
    }
    
    .cookie-banner-content {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        flex-wrap: wrap;
    }
    
    .cookie-banner-text {
        display: flex;
        align-items: flex-start;
        flex: 1;
        min-width: 250px;
    }
    
    .cookie-banner-actions {
        display: flex;
        gap: 0.75rem;
        flex-shrink: 0;
    }
    
    @media (max-width: 768px) {
        .cookie-banner {
            padding: 1rem;
        }
        
        .cookie-banner-content {
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
        }
        
        .cookie-banner-text {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .cookie-banner-actions {
            width: 100%;
            flex-direction: column;
        }
        
        .cookie-banner-actions .btn {
            width: 100%;
        }
    }
    </style>
    
    <script>
    // Check if user has already made a cookie choice
    function checkCookieConsent() {
        const consent = localStorage.getItem('cookieConsent');
        if (consent === null) {
            // Show banner if no choice has been made
            document.getElementById('cookieBanner').style.display = 'block';
        }
    }
    
    // Accept cookies
    function acceptCookies() {
        localStorage.setItem('cookieConsent', 'accepted');
        localStorage.setItem('cookieConsentDate', new Date().toISOString());
        hideCookieBanner();
    }
    
    // Decline cookies
    function declineCookies() {
        localStorage.setItem('cookieConsent', 'declined');
        localStorage.setItem('cookieConsentDate', new Date().toISOString());
        hideCookieBanner();
    }
    
    // Hide the banner
    function hideCookieBanner() {
        const banner = document.getElementById('cookieBanner');
        banner.style.animation = 'slideDown 0.3s ease-out';
        setTimeout(() => {
            banner.style.display = 'none';
        }, 300);
    }
    
    // Add slideDown animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideDown {
            from {
                transform: translateY(0);
            }
            to {
                transform: translateY(100%);
            }
        }
    `;
    document.head.appendChild(style);
    
    // Check on page load
    document.addEventListener('DOMContentLoaded', checkCookieConsent);
    </script>
</body>
</html>
