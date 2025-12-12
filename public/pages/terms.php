<?php
/**
 * Terms of Service
 * Terms and conditions for using the Social Care Contracts Management system
 */
require_once dirname(__DIR__, 2) . '/config/config.php';

$pageTitle = 'Terms of Service';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <h1>Terms of Service</h1>
        <p style="color: var(--text-light); margin-top: 0.5rem;">
            Last updated: <?php echo date('F Y'); ?>
        </p>
    </div>
    
    <div style="max-width: 900px; margin: 0 auto; line-height: 1.7;">
        <section style="margin-bottom: 2rem;">
            <h2>1. Acceptance of Terms</h2>
            <p>
                By accessing and using <?php echo APP_NAME; ?> ("the Service"), you accept and agree to be bound by these Terms of Service. If you do not agree to these terms, you must not use the Service.
            </p>
            <p>
                These terms apply to all users of the Service, including organisations, administrators, and staff members.
            </p>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>2. Description of Service</h2>
            <p>
                <?php echo APP_NAME; ?> is a web-based platform designed to help social care providers in Scotland manage contracts, track procurement processes, monitor rates, and handle contract-related data. The Service includes:
            </p>
            <ul style="margin-left: 2rem;">
                <li>Contract management and tracking</li>
                <li>Rate monitoring and historical data</li>
                <li>Procurement workflow management</li>
                <li>Person tracking across contracts</li>
                <li>Reporting and analytics</li>
                <li>Documentation and resource materials</li>
            </ul>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>3. User Accounts and Registration</h2>
            
            <h3>3.1 Account Creation</h3>
            <p>To use the Service, you must:</p>
            <ul style="margin-left: 2rem;">
                <li>Register with a valid email address associated with your organisation</li>
                <li>Provide accurate and complete information</li>
                <li>Maintain the security of your account credentials</li>
                <li>Notify us immediately of any unauthorised access</li>
            </ul>
            
            <h3>3.2 Account Responsibility</h3>
            <p>
                You are responsible for all activities that occur under your account. You must not share your account credentials with unauthorised persons. Organisations are responsible for managing user access and ensuring appropriate permissions are assigned.
            </p>
            
            <h3>3.3 Account Suspension</h3>
            <p>
                We reserve the right to suspend or terminate accounts that violate these terms, engage in fraudulent activity, or pose a security risk.
            </p>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>4. Acceptable Use</h2>
            <p>You agree not to:</p>
            <ul style="margin-left: 2rem;">
                <li>Use the Service for any unlawful purpose or in violation of any applicable laws or regulations</li>
                <li>Upload, post, or transmit any content that is illegal, harmful, or violates the rights of others</li>
                <li>Attempt to gain unauthorised access to the Service or other users' data</li>
                <li>Interfere with or disrupt the Service or servers connected to the Service</li>
                <li>Use automated systems to access the Service without permission</li>
                <li>Reverse engineer, decompile, or disassemble any part of the Service</li>
                <li>Remove or alter any proprietary notices or labels</li>
            </ul>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>5. Data and Content</h2>
            
            <h3>5.1 Your Data</h3>
            <p>
                You retain ownership of all data and content you upload to the Service. You grant us a licence to use, store, and process this data solely for the purpose of providing the Service.
            </p>
            
            <h3>5.2 Data Accuracy</h3>
            <p>
                You are responsible for ensuring the accuracy and completeness of data you enter into the Service. We are not liable for errors or omissions in data you provide.
            </p>
            
            <h3>5.3 Data Backup</h3>
            <p>
                While we implement backup procedures, you are responsible for maintaining your own backups of critical data. We recommend exporting important information regularly.
            </p>
            
            <h3>5.4 Data Deletion</h3>
            <p>
                Upon account termination, we will delete your data in accordance with our data retention policy, subject to legal requirements to retain certain information.
            </p>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>6. Intellectual Property</h2>
            <p>
                The Service, including its design, features, functionality, and content (excluding user-uploaded data), is owned by us and protected by copyright, trademark, and other intellectual property laws.
            </p>
            <p>
                You may not copy, modify, distribute, or create derivative works based on the Service without our express written permission.
            </p>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>7. Service Availability</h2>
            <p>
                We strive to maintain high availability of the Service but do not guarantee uninterrupted or error-free operation. The Service may be temporarily unavailable due to:
            </p>
            <ul style="margin-left: 2rem;">
                <li>Scheduled maintenance</li>
                <li>Technical issues or system failures</li>
                <li>Circumstances beyond our reasonable control</li>
            </ul>
            <p>
                We will make reasonable efforts to notify users of planned maintenance and minimise service disruptions.
            </p>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>8. Limitation of Liability</h2>
            <p>
                To the maximum extent permitted by law, we shall not be liable for any indirect, incidental, special, consequential, or punitive damages, or any loss of profits or revenues, whether incurred directly or indirectly, or any loss of data, use, goodwill, or other intangible losses resulting from your use of the Service.
            </p>
            <p>
                Our total liability for any claims arising from or related to the Service shall not exceed the amount you paid to us in the twelve months preceding the claim.
            </p>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>9. Indemnification</h2>
            <p>
                You agree to indemnify and hold us harmless from any claims, damages, losses, liabilities, and expenses (including legal fees) arising from your use of the Service, violation of these terms, or infringement of any rights of another party.
            </p>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>10. Termination</h2>
            <p>
                We may terminate or suspend your access to the Service immediately, without prior notice, for any breach of these Terms of Service. You may terminate your account at any time by contacting us.
            </p>
            <p>
                Upon termination, your right to use the Service will cease immediately, and we may delete your account and data in accordance with our data retention policy.
            </p>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>11. Governing Law</h2>
            <p>
                These Terms of Service shall be governed by and construed in accordance with the laws of Scotland and the United Kingdom. Any disputes arising from these terms shall be subject to the exclusive jurisdiction of the Scottish courts.
            </p>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>12. Changes to Terms</h2>
            <p>
                We reserve the right to modify these Terms of Service at any time. We will notify users of material changes by posting the updated terms on this page and updating the "Last updated" date. Your continued use of the Service after such changes constitutes acceptance of the new terms.
            </p>
        </section>
        
        <section style="margin-bottom: 2rem;">
            <h2>13. Contact Information</h2>
            <p>
                If you have questions about these Terms of Service, please contact us:
            </p>
            <p>
                <strong>Email:</strong> socialcarecontracts@outlook.com<br>
                <strong>Subject:</strong> Terms of Service Inquiry
            </p>
        </section>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
