<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in as a patient
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: ../index.php");
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: book_appointment.php");
    exit();
}

// Get form data
$doctor_id = filter_var($_POST['doctor_id'], FILTER_SANITIZE_NUMBER_INT);
$appointment_date = filter_var($_POST['appointment_date'], FILTER_SANITIZE_STRING);
$appointment_time = filter_var($_POST['appointment_time'], FILTER_SANITIZE_STRING);
$reason = filter_var($_POST['reason'], FILTER_SANITIZE_STRING);

// Validate input
if (empty($doctor_id) || empty($appointment_date) || empty($appointment_time) || empty($reason)) {
    $_SESSION['error_message'] = "Please fill in all required fields";
    header("Location: book_appointment.php");
    exit();
}

try {
    // Check if the appointment slot is available
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM appointments 
        WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'cancelled'
    ");
    $stmt->execute([$doctor_id, $appointment_date, $appointment_time]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $_SESSION['error_message'] = "This time slot is already booked. Please choose another time.";
        header("Location: book_appointment.php");
        exit();
    } else {
        // Start transaction
        $conn->beginTransaction();
        
        // Insert appointment
        $stmt = $conn->prepare("
            INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, notes, status) 
            VALUES (?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([$_SESSION['user_id'], $doctor_id, $appointment_date, $appointment_time, $reason]);
        $appointment_id = $conn->lastInsertId();
        
        // Get doctor details for notification
        $stmt = $conn->prepare("SELECT full_name FROM doctors WHERE id = ?");
        $stmt->execute([$doctor_id]);
        $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Create notification for doctor
        $stmt = $conn->prepare("
            INSERT INTO notifications (user_id, title, message, type, reference_id, created_at)
            VALUES (?, ?, ?, 'appointment', ?, NOW())
        ");
        $stmt->execute([
            $doctor_id, 
            'New Appointment Request',
            'A new appointment request from '.$_SESSION['full_name'].' on '.date('F j, Y', strtotime($appointment_date)).' at '.date('g:i A', strtotime($appointment_time)),
            $appointment_id
        ]);
        
        // Commit transaction
        $conn->commit();
        
        // Redirect to thank you page
        header("Location: thank_you.php?type=appointment");
        exit();
    }
} catch(PDOException $e) {
    // Rollback transaction on error
    $conn->rollBack();
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    header("Location: book_appointment.php");
    exit();
}
?>
