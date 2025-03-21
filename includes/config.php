<?php
/**
 * MediConnect Configuration File
 * Central place for application configuration
 */

// Application settings
define('APP_NAME', 'MediConnect');
define('APP_VERSION', '1.0.0');
define('APP_EMAIL', 'support@mediconnect.com');

// Database settings
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mediconnect');

// Path settings
define('BASE_URL', '/mediconnect-php/');
define('UPLOAD_DIR', 'uploads/');
define('MEDICAL_RECORDS_DIR', UPLOAD_DIR . 'medical_records/');

// Create uploads directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

if (!file_exists(MEDICAL_RECORDS_DIR)) {
    mkdir(MEDICAL_RECORDS_DIR, 0777, true);
}

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

// Error reporting (turn off in production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Connect to database
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Include utility functions
require_once 'functions.php'; 