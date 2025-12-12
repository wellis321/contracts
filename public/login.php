<?php
/**
 * Login Page
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
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $error = 'Please fill in all fields.';
        } else {
            $loginResult = Auth::login($email, $password);
            if ($loginResult === true) {
                header('Location: ' . url('index.php'));
                exit;
            } elseif (is_array($loginResult) && isset($loginResult['error'])) {
                $error = $loginResult['message'];
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
}

$pageTitle = 'Login';
include INCLUDES_PATH . '/header.php';
?>

<div class="card" style="max-width: 400px; margin: 2rem auto;">
    <div class="card-header">
        <h2>Login</h2>
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
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
        </div>
        
        <div class="text-center">
            <p>Don't have an account? <a href="<?php echo htmlspecialchars(url('register.php')); ?>">Register here</a></p>
            <p style="margin-top: 0.5rem;"><a href="<?php echo htmlspecialchars(url('resend-verification.php')); ?>">Resend verification email</a></p>
        </div>
    </form>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
