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
define('PRESCRIPTIONS_DIR', UPLOAD_DIR . 'prescriptions/');

// Create uploads directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

if (!file_exists(MEDICAL_RECORDS_DIR)) {
    mkdir(MEDICAL_RECORDS_DIR, 0777, true);
}

if (!file_exists(PRESCRIPTIONS_DIR)) {
    mkdir(PRESCRIPTIONS_DIR, 0777, true);
}

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

// Error reporting (turn on for debugging)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connect to database
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS, array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ));
} catch(PDOException $e) {
    // Log the error and display a user-friendly message
    file_put_contents('logs/db_error.log', date('Y-m-d H:i:s') . ': ' . $e->getMessage() . "\n", FILE_APPEND);
    die("Database connection error. Please try again later or contact support.");
}

// Include utility functions
require_once 'functions.php'; 