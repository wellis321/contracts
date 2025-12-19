<?php
/**
 * Create First Superadmin User
 * 
 * ⚠️ SECURITY WARNING: DELETE THIS FILE AFTER CREATING YOUR SUPERADMIN USER ⚠️
 * 
 * This script helps you create your first superadmin user.
 * Run from command line: php create_superadmin.php
 * Or access via browser (delete after use for security)
 * 
 * This file should NOT be committed to production repositories.
 * 
 * Usage:
 *   php create_superadmin.php
 *   OR
 *   php create_superadmin.php --email=admin@example.com --password=SecurePass123 --first-name=Admin --last-name=User
 */

require_once dirname(__DIR__) . '/config/config.php';

// Check if running from CLI or web
$isCli = php_sapi_name() === 'cli';

if ($isCli) {
    // Command line mode
    if ($argc > 1 && strpos($argv[1], '--help') !== false) {
        echo "\nCreate First Superadmin User\n";
        echo "============================\n\n";
        echo "Usage:\n";
        echo "  php create_superadmin.php\n";
        echo "  php create_superadmin.php --email=admin@example.com --password=SecurePass123 --first-name=Admin --last-name=User\n\n";
        echo "If no arguments provided, you'll be prompted interactively.\n\n";
        exit(0);
    }
    
    // Parse command line arguments
    $email = '';
    $password = '';
    $firstName = '';
    $lastName = '';
    
    for ($i = 1; $i < $argc; $i++) {
        if (strpos($argv[$i], '--email=') === 0) {
            $email = substr($argv[$i], 8);
        } elseif (strpos($argv[$i], '--password=') === 0) {
            $password = substr($argv[$i], 11);
        } elseif (strpos($argv[$i], '--first-name=') === 0) {
            $firstName = substr($argv[$i], 14);
        } elseif (strpos($argv[$i], '--last-name=') === 0) {
            $lastName = substr($argv[$i], 12);
        }
    }
    
    // Interactive prompts if not provided
    if (empty($email)) {
        echo "Enter email address: ";
        $email = trim(fgets(STDIN));
    }
    
    if (empty($password)) {
        echo "Enter password: ";
        // Hide password input on Unix systems
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $password = shell_exec('read -s -p "" password && echo $password');
            $password = trim($password);
        } else {
            $password = trim(fgets(STDIN));
        }
        echo "\n";
    }
    
    if (empty($firstName)) {
        echo "Enter first name: ";
        $firstName = trim(fgets(STDIN));
    }
    
    if (empty($lastName)) {
        echo "Enter last name: ";
        $lastName = trim(fgets(STDIN));
    }
} else {
    // Web mode
    $error = '';
    $success = '';
    $email = '';
    $password = '';
    $firstName = '';
    $lastName = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        
        if (empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
            $error = 'All fields are required.';
        }
        // If validation passes, continue to creation logic below
    }
}

// Validate inputs
if (empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
    if ($isCli) {
        echo "Error: All fields are required.\n";
        exit(1);
    }
    // Web mode will show form below
} else {
    // Check if superadmin already exists
    try {
        $db = getDbConnection();
        
        // Check if any superadmin exists
        $stmt = $db->prepare("
            SELECT COUNT(*) as count 
            FROM user_roles ur
            JOIN roles r ON ur.role_id = r.id
            WHERE r.name = 'superadmin'
        ");
        $stmt->execute();
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            if ($isCli) {
                echo "\nWarning: A superadmin user already exists!\n";
                echo "Do you want to create another one? (yes/no): ";
                $confirm = trim(strtolower(fgets(STDIN)));
                if ($confirm !== 'yes' && $confirm !== 'y') {
                    echo "Cancelled.\n";
                    exit(0);
                }
            } else {
                $error = 'A superadmin user already exists. You can create additional superadmins through the superadmin panel after logging in.';
            }
        }
        
        // Only proceed if no error (for web mode)
        if (empty($error)) {
            // Check if email already exists
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                if ($isCli) {
                    echo "Error: A user with this email already exists.\n";
                    exit(1);
                } else {
                    $error = 'A user with this email already exists.';
                }
            } else {
            // Create the user
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            $db->beginTransaction();
            
            // Insert user (organisation_id is NULL for superadmin)
            $stmt = $db->prepare("
                INSERT INTO users (organisation_id, email, password_hash, first_name, last_name)
                VALUES (NULL, ?, ?, ?, ?)
            ");
            $stmt->execute([$email, $passwordHash, $firstName, $lastName]);
            
            $userId = $db->lastInsertId();
            
            // Get superadmin role ID
            $stmt = $db->prepare("SELECT id FROM roles WHERE name = 'superadmin'");
            $stmt->execute();
            $role = $stmt->fetch();
            
            if (!$role) {
                throw new Exception('Superadmin role not found in database. Please run schema.sql first.');
            }
            
            // Assign superadmin role
            $stmt = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
            $stmt->execute([$userId, $role['id']]);
            
            $db->commit();
            
                if ($isCli) {
                    echo "\n✓ Superadmin user created successfully!\n\n";
                    echo "Email: $email\n";
                    echo "Name: $firstName $lastName\n";
                    echo "\nYou can now log in with these credentials.\n";
                    echo "DELETE THIS FILE AFTER USE FOR SECURITY!\n\n";
                } else {
                    $success = 'Superadmin user created successfully! You can now log in.';
                    $email = '';
                    $password = '';
                    $firstName = '';
                    $lastName = '';
                }
            }
        }
    } catch (Exception $e) {
        if ($isCli) {
            echo "Error: " . $e->getMessage() . "\n";
            exit(1);
        } else {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

// Web interface
if (!$isCli):
?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Superadmin - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url('assets/css/style.css')); ?>">
</head>
<body>
    <main class="container" style="max-width: 600px; margin: 4rem auto;">
        <div class="card">
            <div class="card-header">
                <h1>Create First Superadmin User</h1>
                <p style="color: var(--text-light);">This script creates your first superadmin user. Delete this file after use for security.</p>
            </div>
            
            <?php if (isset($error) && $error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <div style="margin-top: 1.5rem;">
                    <a href="<?php echo htmlspecialchars(url('login.php')); ?>" class="btn btn-primary">Go to Login</a>
                </div>
            <?php else: ?>
                <form method="POST" action="" style="margin-top: 1.5rem;">
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" 
                               value="<?php echo htmlspecialchars($firstName); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" 
                               value="<?php echo htmlspecialchars($lastName); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Create Superadmin</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        
        <div style="margin-top: 2rem; padding: 1rem; background: var(--bg-light); border-radius: 0.5rem;">
            <p style="font-size: 0.9rem; color: var(--text-light);">
                <strong>Security Note:</strong> Delete this file (<code>create_superadmin.php</code>) after creating your superadmin user.
            </p>
        </div>
    </main>
</body>
</html>
<?php endif; ?>
