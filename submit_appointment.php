<?php
/**
 * Submit Appointment
 * Processes the appointment form submission
 */

// Include configuration
require_once 'includes/config.php';

// Check if user is logged in as a patient
requireRole('user');

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlashMessage('error', 'Invalid request method');
    header('Location: pages/book_appointment.php');
    exit();
}

// Get and sanitize form data
$doctorId = sanitizeInput($_POST['doctor_id']);
$date = sanitizeInput($_POST['appointment_date']);
$time = sanitizeInput($_POST['appointment_time']);
$reason = sanitizeInput($_POST['reason']);

// Validate input
if (empty($doctorId) || empty($date) || empty($time) || empty($reason)) {
    $_SESSION['flash_message'] = "Please fill in all required fields.";
    $_SESSION['flash_type'] = "error";
    header('Location: pages/book_appointment.php');
    exit();
}

// Validate date format (must be in the future)
$appointmentDate = new DateTime($date);
$today = new DateTime(date('Y-m-d'));
if ($appointmentDate < $today) {
    $_SESSION['flash_message'] = "Please select a future date for your appointment.";
    $_SESSION['flash_type'] = "error";
    header('Location: pages/book_appointment.php?doctor_id=' . $doctorId);
    exit();
}

try {
    // Start transaction
    $conn->beginTransaction();
    
    // Check if the appointment slot is available
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM appointments 
        WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'cancelled'
    ");
    $stmt->execute([$doctorId, $date, $time]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        // Slot already booked
        $conn->rollBack();
        setFlashMessage('error', 'This time slot is already booked. Please choose another time.');
        header("Location: pages/book_appointment.php?doctor_id=$doctorId");
        exit();
    }
    
    // Insert appointment
    $stmt = $conn->prepare("
        INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, notes, status)
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([$_SESSION['user_id'], $doctorId, $date, $time, $reason]);
    $appointmentId = $conn->lastInsertId();
    
    // Get user's full name
    $userStmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
    $userStmt->execute([$_SESSION['user_id']]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    $userName = $user ? $user['full_name'] : 'A patient';
    
    // Get doctor's name for confirmation
    $doctorStmt = $conn->prepare("SELECT full_name FROM doctors WHERE id = ?");
    $doctorStmt->execute([$doctorId]);
    $doctor = $doctorStmt->fetch(PDO::FETCH_ASSOC);
    $doctorName = $doctor ? $doctor['full_name'] : 'the doctor';
    
    // Create notification for doctor
    $notificationStmt = $conn->prepare("
        INSERT INTO notifications (user_id, title, message, type, reference_id) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $title = 'New Appointment Request';
    $message = $userName . ' has requested an appointment on ' . 
               date('F j, Y', strtotime($date)) . ' at ' . 
               date('g:i A', strtotime($time));
    
    $notificationStmt->execute([
        $doctorId,
        $title,
        $message,
        'appointment',
        $appointmentId
    ]);
    
    // Commit transaction
    $conn->commit();
    
    // Set success message
    setFlashMessage('success', "Your appointment with Dr. $doctorName has been scheduled for " . 
                   date('F j, Y', strtotime($date)) . " at " . 
                   date('g:i A', strtotime($time)) . ". Please wait for confirmation.");
    
    // Redirect to dashboard
    header('Location: dashboard.php');
    exit();
    
} catch (PDOException $e) {
    // Rollback transaction on error
    $conn->rollBack();
    setFlashMessage('error', 'Database error: ' . $e->getMessage());
    header('Location: pages/book_appointment.php');
    exit();
} 