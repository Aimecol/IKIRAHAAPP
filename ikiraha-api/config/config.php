<?php
/**
 * Main Configuration for IKIRAHA API
 * Production-ready settings for XAMPP environment
 */

// Error reporting for production
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Timezone
date_default_timezone_set('Africa/Kigali');

// Application settings
define('APP_NAME', 'IKIRAHA API');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'production');

// Security settings
define('JWT_SECRET', 'ikiraha_jwt_secret_key_2024_very_secure_change_in_production');
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRY', 3600); // 1 hour
define('JWT_REFRESH_EXPIRY', 604800); // 7 days

// Password settings
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_HASH_ALGO', PASSWORD_DEFAULT);

// File upload settings
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// API settings
define('API_VERSION', 'v1');
define('API_BASE_URL', '/ikiraha-api/public/api/v1');

// CORS settings
define('CORS_ALLOWED_ORIGINS', ['*']);
define('CORS_ALLOWED_METHODS', ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS']);
define('CORS_ALLOWED_HEADERS', ['Content-Type', 'Authorization', 'X-Requested-With']);

// Database settings (imported from database.php)
require_once __DIR__ . '/database.php';

// Autoloader for classes
spl_autoload_register(function ($class_name) {
    $directories = [
        __DIR__ . '/../models/',
        __DIR__ . '/../controllers/',
        __DIR__ . '/../middleware/',
        __DIR__ . '/../utils/'
    ];

    foreach ($directories as $directory) {
        $file = $directory . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Helper functions
function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

function generateOrderNumber() {
    return 'ORD' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePhone($phone) {
    // Rwanda phone number validation (+250 7XX XXX XXX)
    return preg_match('/^(\+250|250)?7[0-9]{8}$/', $phone);
}

function logError($message, $context = []) {
    $log = date('Y-m-d H:i:s') . ' - ' . $message;
    if (!empty($context)) {
        $log .= ' - Context: ' . json_encode($context);
    }
    error_log($log . PHP_EOL, 3, __DIR__ . '/../logs/app.log');
}

// Create logs directory if it doesn't exist
if (!is_dir(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}

// Create uploads directory if it doesn't exist
if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}
?>