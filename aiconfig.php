<?php
// config.php - Enhanced Database Configuration
// Only start session if one is not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'esport');

// Application Configuration
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['image/png', 'image/jpeg']);
define('APP_VERSION', '2.0.0');

// OCR Configuration
define('OCR_ENABLED', true);
define('OCR_ENGINE', 'tesseract'); // tesseract or gocr
define('OCR_LANGUAGE', 'eng');

// ML Configuration
define('ML_ENHANCED', true);
define('ALGORITHM_VERSION', 'v2.1');

// Performance Configuration
define('ENABLE_CACHE', true);
define('CACHE_TTL', 3600); // 1 hour

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create database connection with error handling
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset
    $conn->set_charset("utf8mb4");
    
    // Create tables if they don't exist
    createTables($conn);
    
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Include utility classes
require_once __DIR__ . '/lib/Security.php';
require_once __DIR__ . '/lib/Predictor.php';
require_once __DIR__ . '/lib/Database.php';

// Initialize Security Handler
$security = new SecurityHandler();

// Initialize Enhanced Database
$db = new EnhancedDatabase(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Initialize Predictor
$predictor = new InfiknightPredictor($db);
?>