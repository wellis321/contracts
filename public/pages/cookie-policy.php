<?php
/**
 * Cookie Policy
 * Information about how we use cookies and similar technologies
 */
require_once dirname(__DIR__, 2) . '/config/config.php';

$pageTitle = 'Cookie Policy';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <h1>Cookie Policy</h1>
        <p style="color: var(--text-light); margin-top: 0.5rem;">
            Last updated: <?php echo date('F Y'); ?>
        </p>
    </div>
    
    <div style="max-width: 900px; margin: 0 auto; line-height: 1.7;">
        <section style="margin-bottom: 2rem;">
            <h2>1. What Are Cookies?</h2>
            <p>
                Cookies are small text files that are placed on your device (computer, tablet, or mobile) when you visit a website. They are widely used to make websites work more efficiently and provide information to website owners.
            </p>
            <p>
                Cookies allow a website to recognise your device and store some information about your preferences or past actions. This helps improve your browsing experience and allows the website to function properly.
            </p>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>2. How We Use Cookies</h2>
            <p>
                <?php echo APP_NAME; ?> uses cookies to:
            </p>
            <ul style="margin-left: 2rem;">
                <li>Maintain your login session and keep you signed in</li>
                <li>Remember your preferences and settings</li>
                <li>Ensure the security and proper functioning of the Service</li>
                <li>Remember your cookie consent preferences</li>
            </ul>
            <p>
                We only use essential cookies that are necessary for the Service to function. We do not use cookies for advertising, tracking, or analytics purposes.
            </p>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>3. Types of Cookies We Use</h2>
            
            <h3>3.1 Essential Cookies (Strictly Necessary)</h3>
            <p>
                These cookies are essential for the Service to function and cannot be switched off. They are usually set in response to actions you take, such as:
            </p>
            <ul style="margin-left: 2rem;">
                <li><strong>Session Cookies:</strong> Maintain your login session and authenticate your identity</li>
                <li><strong>Security Cookies:</strong> Protect against unauthorised access and maintain system security</li>
                <li><strong>CSRF Protection Cookies:</strong> Prevent cross-site request forgery attacks</li>
            </ul>
            <p>
                These cookies do not store personal information but are necessary for the Service to operate securely.
            </p>
            
            <h3>3.2 Preference Cookies</h3>
            <p>
                These cookies allow the Service to remember information about your preferences:
            </p>
            <ul style="margin-left: 2rem;">
                <li><strong>Cookie Consent:</strong> Remembers your choice regarding cookie acceptance</li>
                <li><strong>User Preferences:</strong> Stores your interface preferences and settings</li>
            </ul>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>4. Third-Party Cookies</h2>
            <p>
                We use Font Awesome (via CDN) for icons on our website. Font Awesome may set cookies for their service functionality. Please refer to <a href="https://fontawesome.com/privacy" target="_blank" rel="noopener" style="color: var(--primary-color);">Font Awesome's Privacy Policy</a> for information about their cookie usage.
            </p>
            <p>
                We do not use third-party advertising or tracking cookies.
            </p>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>5. Managing Cookies</h2>
            
            <h3>5.1 Browser Settings</h3>
            <p>
                Most web browsers allow you to control cookies through their settings. You can:
            </p>
            <ul style="margin-left: 2rem;">
                <li>View what cookies are stored on your device</li>
                <li>Delete cookies individually or all at once</li>
                <li>Block cookies from specific websites</li>
                <li>Block all cookies</li>
                <li>Delete all cookies when you close your browser</li>
            </ul>
            <p>
                <strong>Important:</strong> If you block essential cookies, the Service may not function properly, and you may not be able to log in or use certain features.
            </p>
            
            <h3>5.2 How to Manage Cookies in Popular Browsers</h3>
            <ul style="margin-left: 2rem;">
                <li><strong>Chrome:</strong> Settings → Privacy and Security → Cookies and other site data</li>
                <li><strong>Firefox:</strong> Options → Privacy & Security → Cookies and Site Data</li>
                <li><strong>Safari:</strong> Preferences → Privacy → Cookies and website data</li>
                <li><strong>Edge:</strong> Settings → Privacy, Search, and Services → Cookies and site permissions</li>
            </ul>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>6. Local Storage</h2>
            <p>
                In addition to cookies, we use browser local storage to:
            </p>
            <ul style="margin-left: 2rem;">
                <li>Remember your cookie consent preference</li>
                <li>Store temporary data for improved performance</li>
            </ul>
            <p>
                You can clear local storage through your browser settings, similar to clearing cookies.
            </p>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>7. Cookie Consent</h2>
            <p>
                When you first visit our website, you will see a cookie consent banner. You can choose to:
            </p>
            <ul style="margin-left: 2rem;">
                <li><strong>Accept:</strong> Allow us to use essential cookies</li>
                <li><strong>Decline:</strong> Reject non-essential cookies (note: essential cookies are still required for the Service to function)</li>
            </ul>
            <p>
                Your choice is stored in your browser's local storage and will be remembered for future visits. You can change your preference at any time by clearing your browser's local storage and cookies.
            </p>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>8. Updates to This Cookie Policy</h2>
            <p>
                We may update this Cookie Policy from time to time to reflect changes in our practices or for other operational, legal, or regulatory reasons. We will notify you of any material changes by updating the "Last updated" date on this page.
            </p>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>9. Contact Us</h2>
            <p>
                If you have questions about our use of cookies, please contact us:
            </p>
            <p>
                <strong>Email:</strong> socialcarecontracts@outlook.com<br>
                <strong>Subject:</strong> Cookie Policy Inquiry
            </p>
        </section>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
