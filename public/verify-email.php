<?php
/**
 * Email Verification Page
 */
require_once dirname(__DIR__) . '/config/config.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = 'No verification token provided.';
} else {
    $result = Auth::verifyEmail($token);
    if ($result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['message'];
    }
}

$pageTitle = 'Verify Email';
include INCLUDES_PATH . '/header.php';
?>

<div class="card" style="max-width: 500px; margin: 2rem auto;">
    <div class="card-header">
        <h2>Email Verification</h2>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php if (strpos($error, 'expired') !== false): ?>
            <div style="margin-top: 1rem;">
                <p>Would you like to request a new verification email?</p>
                <form method="POST" action="<?php echo htmlspecialchars(url('resend-verification.php')); ?>" style="margin-top: 1rem;">
                    <?php echo CSRF::tokenField(); ?>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Resend Verification Email</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <div style="margin-top: 1.5rem;">
            <a href="<?php echo htmlspecialchars(url('login.php')); ?>" class="btn btn-primary" style="width: 100%;">Go to Login</a>
        </div>
    <?php endif; ?>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
