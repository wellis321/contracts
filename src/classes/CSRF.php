<?php
/**
 * CSRF Protection Class
 * Handles CSRF token generation and validation
 */

class CSRF {
    
    /**
     * Generate and store CSRF token
     */
    public static function generateToken() {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }
    
    /**
     * Get current CSRF token
     */
    public static function getToken() {
        return self::generateToken();
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateToken($token) {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            return false;
        }
        
        return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }
    
    /**
     * Generate CSRF token field for forms
     */
    public static function tokenField() {
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . self::getToken() . '">';
    }
    
    /**
     * Validate POST request CSRF token
     */
    public static function validatePost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }
        
        $token = $_POST[CSRF_TOKEN_NAME] ?? '';
        return self::validateToken($token);
    }
}
