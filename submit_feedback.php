<?php
/**
 * Submit Feedback
 * Processes the feedback form submission
 */

// Include configuration
require_once 'includes/config.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlashMessage('error', 'Invalid request method');
    header('Location: pages/feedback.php');
    exit();
}

// Get and sanitize form data
$subject = sanitizeInput($_POST['subject']);
$message = sanitizeInput($_POST['message']);
$rating = sanitizeInput($_POST['rating']);
$appointmentId = isset($_POST['appointment_id']) ? sanitizeInput($_POST['appointment_id']) : null;

// For users who are not logged in
$name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : null;
$email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : null;

// Get user ID if logged in
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Validate input
if (empty($subject) || empty($message) || empty($rating)) {
    setFlashMessage('error', 'Please fill in all required fields.');
    header('Location: pages/feedback.php');
    exit();
}

// If user is not logged in, name and email are required
if (!$userId && (empty($name) || empty($email))) {
    setFlashMessage('error', 'Please provide your name and email.');
    header('Location: pages/feedback.php');
    exit();
}

try {
    // Insert feedback into database
    $stmt = $conn->prepare("
        INSERT INTO feedback (user_id, name, email, subject, message, rating, appointment_id, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $userId,
        $name,
        $email,
        $subject,
        $message,
        $rating,
        $appointmentId
    ]);
    
    // Set success message
    setFlashMessage('success', 'Thank you for your feedback! We appreciate your input.');
    
    // Redirect to thank you page
    header('Location: pages/thank_you.php?type=feedback');
    exit();
    
} catch (PDOException $e) {
    // Handle database error
    setFlashMessage('error', 'Database error: ' . $e->getMessage());
    header('Location: pages/feedback.php');
    exit();
}
