<?php
/**
 * Email Class
 * Handles sending emails for the application
 */

class Email {
    
    /**
     * Send email verification link
     */
    public static function sendVerificationEmail($email, $firstName, $verificationToken) {
        $verificationUrl = APP_URL . url('verify-email.php?token=' . urlencode($verificationToken));
        
        $subject = 'Verify Your Email Address - ' . APP_NAME;
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .button { display: inline-block; padding: 12px 24px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; margin: 20px 0; }
                .footer { padding: 20px; text-align: center; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>" . htmlspecialchars(APP_NAME) . "</h1>
                </div>
                <div class='content'>
                    <p>Hello " . htmlspecialchars($firstName) . ",</p>
                    <p>Thank you for registering with " . htmlspecialchars(APP_NAME) . ". Please verify your email address to activate your account.</p>
                    <p style='text-align: center;'>
                        <a href='" . htmlspecialchars($verificationUrl) . "' class='button'>Verify Email Address</a>
                    </p>
                    <p>Or copy and paste this link into your browser:</p>
                    <p style='word-break: break-all; color: #666;'>" . htmlspecialchars($verificationUrl) . "</p>
                    <p>This link will expire in 24 hours.</p>
                    <p>If you did not create an account, please ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated email from " . htmlspecialchars(APP_NAME) . "</p>
                    <p>If you have any questions, please contact: socialcarecontracts@outlook.com</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return self::sendEmail($email, $subject, $message);
    }
    
    /**
     * Send email using PHP mail() function
     * In production, you may want to use a service like SendGrid, Mailgun, or SMTP
     */
    private static function sendEmail($to, $subject, $message) {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . APP_NAME . ' <' . (getenv('MAIL_FROM') ?: 'noreply@socialcarecontracts.com') . '>',
            'Reply-To: ' . (getenv('MAIL_REPLY_TO') ?: 'socialcarecontracts@outlook.com'),
            'X-Mailer: PHP/' . phpversion()
        ];
        
        return mail($to, $subject, $message, implode("\r\n", $headers));
    }
    
    /**
     * Generate a secure random token for email verification
     */
    public static function generateVerificationToken() {
        return bin2hex(random_bytes(32)); // 64 character hex string
    }
    
    /**
     * Send glossary suggestion rejection email
     */
    public static function sendGlossaryRejectionEmail($email, $firstName, $term, $reason) {
        $subject = 'Glossary Suggestion Update - ' . APP_NAME;
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #2563eb; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .term-box { background-color: #fff; border-left: 4px solid #2563eb; padding: 15px; margin: 15px 0; }
                .reason-box { background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 15px; margin: 15px 0; }
                .footer { padding: 20px; text-align: center; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Glossary Suggestion Update</h2>
                </div>
                <div class='content'>
                    <p>Hello " . htmlspecialchars($firstName) . ",</p>
                    <p>Thank you for your suggestion to add a term to the " . htmlspecialchars(APP_NAME) . " glossary.</p>
                    <p>Unfortunately, we are unable to include your suggested term at this time.</p>
                    
                    <div class='term-box'>
                        <strong>Your Suggestion:</strong><br>
                        <strong>" . htmlspecialchars($term) . "</strong>
                    </div>
                    
                    <div class='reason-box'>
                        <strong>Reason:</strong><br>
                        " . nl2br(htmlspecialchars($reason)) . "
                    </div>
                    
                    <p>We appreciate your contribution and encourage you to continue suggesting terms that would be valuable to the community.</p>
                    <p>If you have any questions, please don't hesitate to contact us.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated email from " . htmlspecialchars(APP_NAME) . "</p>
                    <p>If you have any questions, please contact: socialcarecontracts@outlook.com</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return self::sendEmail($email, $subject, $message);
    }
    
    /**
     * Send glossary suggestion approval email
     */
    public static function sendGlossaryApprovalEmail($email, $firstName, $term, $reason = null) {
        $subject = 'Glossary Suggestion Approved - ' . APP_NAME;
        
        $reasonSection = '';
        if ($reason) {
            $reasonSection = "
            <div style='background-color: #f0fdf4; border-left: 4px solid #10b981; padding: 15px; margin: 15px 0;'>
                <strong>Note:</strong><br>
                " . nl2br(htmlspecialchars($reason)) . "
            </div>";
        }
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #10b981; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .term-box { background-color: #fff; border-left: 4px solid #10b981; padding: 15px; margin: 15px 0; }
                .footer { padding: 20px; text-align: center; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Glossary Suggestion Approved!</h2>
                </div>
                <div class='content'>
                    <p>Hello " . htmlspecialchars($firstName) . ",</p>
                    <p>Great news! Your glossary suggestion has been approved and will be added to the " . htmlspecialchars(APP_NAME) . " glossary.</p>
                    
                    <div class='term-box'>
                        <strong>Your Approved Term:</strong><br>
                        <strong>" . htmlspecialchars($term) . "</strong>
                    </div>
                    " . $reasonSection . "
                    
                    <p>Thank you for contributing to the glossary. Your suggestion helps make the platform more useful for everyone.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated email from " . htmlspecialchars(APP_NAME) . "</p>
                    <p>If you have any questions, please contact: socialcarecontracts@outlook.com</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return self::sendEmail($email, $subject, $message);
    }
}
