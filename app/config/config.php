<?php
// app/config/config.php

// Load .env file manually (tanpa Composer)
$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments and empty lines
        if (empty(trim($line)) || strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse key=value
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove surrounding quotes
            $value = trim($value, '"\'');
            
            // Set to environment
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Error reporting
$debugMode = getenv('APP_DEBUG') === 'true' || 
             (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true');

if ($debugMode) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Database Constants dengan fallback default
define('DB_HOST', getenv('DB_HOST') ?: (isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : 'localhost'));
define('DB_NAME', getenv('DB_NAME') ?: (isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : 'nilai_mahasiswa'));
define('DB_USER', getenv('DB_USER') ?: (isset($_ENV['DB_USER']) ? $_ENV['DB_USER'] : 'root'));
define('DB_PASS', getenv('DB_PASS') ?: (isset($_ENV['DB_PASS']) ? $_ENV['DB_PASS'] : ''));

// Application Constants
define('BASE_URL', getenv('BASE_URL') ?: (isset($_ENV['BASE_URL']) ? $_ENV['BASE_URL'] : 'http://localhost/test-it/public/'));

// WhatsApp API URL
define('WHATSAPP_API_URL', getenv('WHATSAPP_API_URL') ?: (isset($_ENV['WHATSAPP_API_URL']) ? $_ENV['WHATSAPP_API_URL'] : 'http://localhost:3000'));

// Debug mode
define('DEBUG', $debugMode);

// Autoload classes (sesuaikan path jika perlu)
spl_autoload_register(function($className) {
    // Convert namespace to path
    $className = str_replace('\\', '/', $className);
    
    $paths = [
        __DIR__ . '/../controllers/' . $className . '.php',
        __DIR__ . '/../models/' . $className . '.php',
        __DIR__ . '/../database/' . $className . '.php',
        __DIR__ . '/../lib/' . $className . '.php', // tambahan jika ada
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
    
    // Jika tidak ditemukan, log error
    if (DEBUG) {
        error_log("Class not found: $className");
    }
});

// Start session
session_start();

// Set default timezone
date_default_timezone_set('Asia/Jakarta');

// Helper function untuk mendapatkan env value
function env($key, $default = null) {
    $value = getenv($key);
    if ($value === false) {
        $value = isset($_ENV[$key]) ? $_ENV[$key] : $default;
    }
    return $value;
}
?>