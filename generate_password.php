<?php
/**
 * Temporary password hash generator
 * 
 * ⚠️ SECURITY WARNING: DELETE THIS FILE AFTER USE ⚠️
 * This file should NOT be committed to production repositories.
 * 
 * Usage: php generate_password.php <your_password>
 */

if (php_sapi_name() !== 'cli') {
    die("This script must be run from command line.\n");
}

if ($argc < 2) {
    echo "Usage: php generate_password.php <your_password>\n";
    echo "Example: php generate_password.php MySecurePassword123\n";
    exit(1);
}

$password = $argv[1];
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "\nPassword Hash: " . $hash . "\n\n";
echo "Copy this hash and use it in your SQL to create a superadmin user.\n";
echo "DELETE THIS FILE AFTER USE!\n\n";
