<?php
/**
 * Registration Page
 * Domain-based organisation association
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
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        
        // Validation
        if (empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
            $error = 'Please fill in all fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } else {
            // Validate password strength
            $passwordErrors = Auth::validatePasswordStrength($password);
            if (!empty($passwordErrors)) {
                $error = implode(' ', $passwordErrors);
            } else {
                // Extract domain from email address
                $domain = substr(strrchr($email, '@'), 1);
                if (empty($domain)) {
                    $error = 'Invalid email address format.';
                } else {
                    $result = Auth::register($email, $password, $firstName, $lastName, $domain);
                    if ($result['success']) {
                        $success = $result['message'];
                        // Clear form
                        $_POST = [];
                    } else {
                        $error = $result['message'];
                    }
                }
            }
        }
    }
}

$pageTitle = 'Register';
include INCLUDES_PATH . '/header.php';
?>

<div class="card" style="max-width: 500px; margin: 2rem auto;">
    <div class="card-header">
        <h2>Register</h2>
        <p style="color: var(--text-light); margin-top: 0.5rem;">Your organisation will be automatically detected from your email address</p>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <?php echo CSRF::tokenField(); ?>
        
        <div class="form-group">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" class="form-control" 
                   value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" class="form-control" 
                   value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" 
                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            <small style="color: var(--text-light);">Your organisation will be automatically detected from your email domain</small>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" 
                   minlength="<?php echo PASSWORD_MIN_LENGTH; ?>" required>
            <small style="color: var(--text-light); display: block; margin-top: 0.5rem;">
                Password must be at least <?php echo PASSWORD_MIN_LENGTH; ?> characters and contain:
                <ul style="margin: 0.5rem 0 0 1.5rem; padding: 0;">
                    <?php if (PASSWORD_REQUIRE_UPPERCASE): ?><li>One uppercase letter</li><?php endif; ?>
                    <?php if (PASSWORD_REQUIRE_LOWERCASE): ?><li>One lowercase letter</li><?php endif; ?>
                    <?php if (PASSWORD_REQUIRE_NUMBER): ?><li>One number</li><?php endif; ?>
                    <?php if (PASSWORD_REQUIRE_SPECIAL): ?><li>One special character</li><?php endif; ?>
                </ul>
            </small>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary" style="width: 100%;">Register</button>
        </div>
        
        <div class="text-center">
            <p>Already have an account? <a href="<?php echo htmlspecialchars(url('login.php')); ?>">Login here</a></p>
        </div>
    </form>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
