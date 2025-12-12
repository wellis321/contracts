<?php
/**
 * Resend Verification Email Page
 */
require_once dirname(__DIR__) . '/config/config.php';

// Redirect if already logged in
if (Auth::isLoggedIn()) {
    header('Location: ' . url('index.php'));
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validatePost()) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email)) {
            $error = 'Please enter your email address.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            $result = Auth::resendVerificationEmail($email);
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    }
}

$pageTitle = 'Resend Verification Email';
include INCLUDES_PATH . '/header.php';
?>

<div class="card" style="max-width: 500px; margin: 2rem auto;">
    <div class="card-header">
        <h2>Resend Verification Email</h2>
        <p style="color: var(--text-light); margin-top: 0.5rem;">Enter your email address to receive a new verification link</p>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <div style="margin-top: 1.5rem;">
            <a href="<?php echo htmlspecialchars(url('login.php')); ?>" class="btn btn-primary" style="width: 100%;">Go to Login</a>
        </div>
    <?php else: ?>
        <form method="POST" action="" style="margin-top: 1.5rem;">
            <?php echo CSRF::tokenField(); ?>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Resend Verification Email</button>
            </div>
        </form>
        
        <div class="text-center" style="margin-top: 1.5rem;">
            <p><a href="<?php echo htmlspecialchars(url('login.php')); ?>">Back to Login</a></p>
        </div>
    <?php endif; ?>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
