<?php
// Database Configuration
// In production, use environment variables for these values
$host = 'localhost';
$db   = 'attendance_system';
$user = 'root';
$pass = ''; // Default XAMPP password is empty
$port = 3306;
$charset = 'utf8mb4';

// DSN (Data Source Name)
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

// PDO Options for better security and performance
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
];

// Database connection with error handling
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Log error for debugging (in production, don't expose error details)
    error_log("Database connection failed: " . $e->getMessage());
    
    // Show user-friendly error message
    if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
        die("Database connection failed: " . $e->getMessage());
    } else {
        die("Database connection failed. Please try again later.");
    }
}

// Set timezone
date_default_timezone_set('Asia/Manila');

// Define constants for better code organization
define('SITE_URL', 'http://localhost:8000');
define('UPLOAD_PATH', __DIR__ . '/../../uploads/');
define('LOG_PATH', __DIR__ . '/../../logs/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// Ensure upload and log directories exist
if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}
if (!is_dir(LOG_PATH)) {
    mkdir(LOG_PATH, 0755, true);
}
