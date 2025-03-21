<?php
/**
 * Utility functions for MediConnect
 */

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if current user is a patient
 * 
 * @return bool True if user is a patient, false otherwise
 */
function isPatient() {
    return isLoggedIn() && $_SESSION['user_type'] === 'user';
}

/**
 * Check if current user is a doctor
 * 
 * @return bool True if user is a doctor, false otherwise
 */
function isDoctor() {
    return isLoggedIn() && $_SESSION['user_type'] === 'doctor';
}

/**
 * Redirect to login page if user is not logged in
 * 
 * @return void
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header("Location: " . getBaseUrl() . "login.php");
        exit();
    }
}

/**
 * Redirect to appropriate page if user doesn't have required role
 * 
 * @param string $requiredRole 'user' or 'doctor'
 * @return void 
 */
function requireRole($requiredRole) {
    requireLogin();
    
    if ($_SESSION['user_type'] !== $requiredRole) {
        $redirectUrl = ($requiredRole === 'user') 
            ? 'doctor_dashboard.php' 
            : 'dashboard.php';
        
        header("Location: " . getBaseUrl() . $redirectUrl);
        exit();
    }
}

/**
 * Get the base URL based on the current directory
 * 
 * @param bool $isSubdirectory Whether the current file is in a subdirectory
 * @return string Base URL ('../' if in subdirectory, '' if in root)
 */
function getBaseUrl($isSubdirectory = false) {
    return $isSubdirectory ? '../' : '';
}

/**
 * Display success message
 * 
 * @param string $message Message to display
 * @return string HTML for success message
 */
function showSuccess($message) {
    if (empty($message)) return '';
    
    return '<div class="alert alert-success">
        <i class="fas fa-check-circle"></i> ' . htmlspecialchars($message) . '
    </div>';
}

/**
 * Display error message
 * 
 * @param string $message Message to display
 * @return string HTML for error message
 */
function showError($message) {
    if (empty($message)) return '';
    
    return '<div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($message) . '
    </div>';
}

/**
 * Set a flash message to be displayed on the next page
 * 
 * @param string $type 'success' or 'error'
 * @param string $message Message to display
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and remove flash message
 * 
 * @return array|null Flash message if exists, null otherwise
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Display flash message if exists
 * 
 * @return string HTML for flash message
 */
function showFlashMessage() {
    $flash = getFlashMessage();
    if (!$flash) return '';
    
    return ($flash['type'] === 'success') 
        ? showSuccess($flash['message']) 
        : showError($flash['message']);
}

/**
 * Securely sanitize user input
 * 
 * @param string $input User input to sanitize
 * @return string Sanitized input
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
} 