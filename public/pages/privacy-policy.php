<?php
/**
 * Privacy Policy
 * Information about how we collect, use, and protect user data
 */
require_once dirname(__DIR__, 2) . '/config/config.php';

$pageTitle = 'Privacy Policy';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <h1>Privacy Policy</h1>
        <p style="color: var(--text-light); margin-top: 0.5rem;">
            Last updated: <?php echo date('F Y'); ?>
        </p>
    </div>
    
    <div style="max-width: 900px; margin: 0 auto; line-height: 1.7;">
        <section style="margin-bottom: 2rem;">
            <h2>1. Introduction</h2>
            <p>
                <?php echo APP_NAME; ?> ("we", "our", or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our social care contract management system.
            </p>
            <p>
                By using our service, you agree to the collection and use of information in accordance with this policy.
            </p>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>2. Information We Collect</h2>
            
            <h3>2.1 Personal Information</h3>
            <p>We collect information that you provide directly to us, including:</p>
            <ul style="margin-left: 2rem;">
                <li><strong>Account Information:</strong> Name, email address, organisation details</li>
                <li><strong>Contract Data:</strong> Contract details, rates, procurement information, and related documentation</li>
                <li><strong>Person Data:</strong> Information about individuals linked to contracts (as required for contract management)</li>
                <li><strong>Organisation Data:</strong> Organisation name, domain, and user management information</li>
            </ul>
            
            <h3>2.2 Automatically Collected Information</h3>
            <p>When you use our service, we may automatically collect:</p>
            <ul style="margin-left: 2rem;">
                <li>Session information and login timestamps</li>
                <li>Browser type and version</li>
                <li>IP address (for security and system administration)</li>
                <li>Pages visited and actions taken within the system</li>
            </ul>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>3. How We Use Your Information</h2>
            <p>We use the information we collect to:</p>
            <ul style="margin-left: 2rem;">
                <li>Provide, maintain, and improve our services</li>
                <li>Process and manage contracts and related data</li>
                <li>Authenticate users and manage access control</li>
                <li>Send important service-related communications</li>
                <li>Ensure system security and prevent fraud</li>
                <li>Comply with legal obligations</li>
                <li>Respond to your inquiries and support requests</li>
            </ul>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>4. Data Storage and Security</h2>
            <p>
                We implement appropriate technical and organisational measures to protect your personal information against unauthorised access, alteration, disclosure, or destruction. This includes:
            </p>
            <ul style="margin-left: 2rem;">
                <li>Encrypted password storage using industry-standard hashing</li>
                <li>Secure session management</li>
                <li>Role-based access control to ensure data isolation between organisations</li>
                <li>Regular security updates and monitoring</li>
                <li>Secure database connections</li>
            </ul>
            <p>
                Your data is stored on secure servers and is only accessible to authorised personnel who require access to provide our services.
            </p>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>5. Data Sharing and Disclosure</h2>
            <p>We do not sell, trade, or rent your personal information to third parties. We may share your information only in the following circumstances:</p>
            <ul style="margin-left: 2rem;">
                <li><strong>Within Your Organisation:</strong> Data is accessible to authorised users within your organisation based on role permissions</li>
                <li><strong>Service Providers:</strong> We may share information with trusted service providers who assist in operating our system (e.g., hosting providers), subject to confidentiality agreements</li>
                <li><strong>Legal Requirements:</strong> We may disclose information if required by law or in response to valid legal requests</li>
                <li><strong>Protection of Rights:</strong> We may share information to protect our rights, privacy, safety, or property, or that of our users</li>
            </ul>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>6. Data Retention</h2>
            <p>
                We retain your personal information for as long as necessary to provide our services and fulfil the purposes outlined in this Privacy Policy, unless a longer retention period is required or permitted by law.
            </p>
            <p>
                When you request account deletion, we will delete or anonymise your personal information, except where we are required to retain it for legal or regulatory purposes.
            </p>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>7. Your Rights</h2>
            <p>Under UK data protection law, you have the right to:</p>
            <ul style="margin-left: 2rem;">
                <li><strong>Access:</strong> Request a copy of the personal information we hold about you</li>
                <li><strong>Correction:</strong> Request correction of inaccurate or incomplete information</li>
                <li><strong>Deletion:</strong> Request deletion of your personal information (subject to legal requirements)</li>
                <li><strong>Objection:</strong> Object to processing of your personal information in certain circumstances</li>
                <li><strong>Data Portability:</strong> Request transfer of your data to another service provider</li>
                <li><strong>Withdraw Consent:</strong> Withdraw consent where processing is based on consent</li>
            </ul>
            <p>
                To exercise these rights, please contact us using the details provided in Section 9.
            </p>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>8. Cookies and Tracking Technologies</h2>
            <p>
                We use cookies and similar technologies to maintain your session and improve your experience. For detailed information about our use of cookies, please see our <a href="<?php echo url('pages/cookie-policy.php'); ?>" style="color: var(--primary-color);">Cookie Policy</a>.
            </p>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>9. Contact Us</h2>
            <p>
                If you have questions about this Privacy Policy or wish to exercise your rights, please contact us:
            </p>
            <p>
                <strong>Email:</strong> socialcarecontracts@outlook.com<br>
                <strong>Subject:</strong> Privacy Policy Inquiry
            </p>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>10. Changes to This Privacy Policy</h2>
            <p>
                We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last updated" date. You are advised to review this Privacy Policy periodically for any changes.
            </p>
        </section>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
