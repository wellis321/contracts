<?php
/**
 * Main Configuration File
 * Social Care Contracts Management Application
 */

// Error reporting (environment-based)
$isProduction = getenv('APP_ENV') === 'production' || getenv('APP_ENV') === 'prod';
if ($isProduction) {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Timezone
date_default_timezone_set('Europe/London');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
// Use secure cookies in production (requires HTTPS)
ini_set('session.cookie_secure', $isProduction ? 1 : 0);

// Application paths
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('SRC_PATH', ROOT_PATH . '/src');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('ASSETS_PATH', PUBLIC_PATH . '/assets');

// Load environment variables
require_once __DIR__ . '/env_loader.php';

// Application settings (from .env or defaults)
define('APP_NAME', getenv('APP_NAME') ?: 'Social Care Contracts Management');
define('APP_VERSION', '1.0.0');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost'); // Update for production

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REQUIRE_UPPERCASE', true);
define('PASSWORD_REQUIRE_LOWERCASE', true);
define('PASSWORD_REQUIRE_NUMBER', true);
define('PASSWORD_REQUIRE_SPECIAL', true);
define('VERIFICATION_TOKEN_EXPIRY_HOURS', 24);

// Pagination
define('ITEMS_PER_PAGE', 20);

// Date format (UK format)
define('DATE_FORMAT', 'd/m/Y');
define('DATETIME_FORMAT', 'd/m/Y H:i');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoload classes (simple autoloader)
spl_autoload_register(function ($class) {
    $paths = [
        SRC_PATH . '/models/' . $class . '.php',
        SRC_PATH . '/controllers/' . $class . '.php',
        SRC_PATH . '/classes/' . $class . '.php',
        SRC_PATH . '/services/' . $class . '.php',
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Include database configuration (env_loader already loaded above)
require_once CONFIG_PATH . '/database.php';

// Calculate base URL dynamically based on document root
// This is used for generating correct URLs regardless of server configuration
if (!function_exists('getBaseUrl')) {
    function getBaseUrl() {
        static $baseUrl = null;
        if ($baseUrl === null) {
            $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
            $docRootNormalized = rtrim(str_replace('\\', '/', $docRoot), '/');
            $lastPart = strtolower(basename($docRootNormalized));
            $isPublicRoot = ($lastPart === 'public');
            $baseUrl = $isPublicRoot ? '' : '/public';
        }
        return $baseUrl;
    }
}

// Helper function to generate URLs with proper base path
// Usage: url('index.php') or url('pages/documentation.php')
if (!function_exists('url')) {
    function url($path) {
        $baseUrl = getBaseUrl();
        // Remove leading slash from path if present, we'll add it
        $path = ltrim($path, '/');
        // If baseUrl is empty, we still need a leading slash
        if (empty($baseUrl)) {
            return '/' . $path;
        }
        return $baseUrl . '/' . $path;
    }
}

// Helper function to get organisation terminology preferences
// Returns array with 'singular' and 'plural' keys
if (!function_exists('getPersonTerm')) {
    function getPersonTerm($singular = true) {
        static $terms = null;
        
        if ($terms === null) {
            // Default terms
            $terms = [
                'singular' => 'person',
                'plural' => 'people'
            ];
            
            // Try to get organisation-specific terms if user is logged in
            if (Auth::isLoggedIn()) {
                $organisationId = Auth::getOrganisationId();
                if ($organisationId) {
                    $organisation = Organisation::findById($organisationId);
                    if ($organisation) {
                        $terms['singular'] = !empty($organisation['person_singular']) 
                            ? $organisation['person_singular'] 
                            : 'person';
                        $terms['plural'] = !empty($organisation['person_plural']) 
                            ? $organisation['person_plural'] 
                            : 'people';
                    }
                }
            }
        }
        
        return $singular ? $terms['singular'] : $terms['plural'];
    }
}

// Helper function to get both terms at once
if (!function_exists('getPersonTerms')) {
    function getPersonTerms() {
        return [
            'singular' => getPersonTerm(true),
            'plural' => getPersonTerm(false)
        ];
    }
}
