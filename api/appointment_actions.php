<?php
/**
 * Appointment Actions API
 * Handles appointment status updates and provides real-time data
 */

// Enable error reporting for debugging (can be removed in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include configuration
require_once '../includes/config.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Log API request for debugging
$logFile = '../logs/api_debug.log';
$requestData = [
    'time' => date('Y-m-d H:i:s'),
    'action' => $_REQUEST['action'] ?? 'none',
    'method' => $_SERVER['REQUEST_METHOD'],
    'user_id' => $_SESSION['user_id'] ?? 'not logged in',
    'user_type' => $_SESSION['user_type'] ?? 'none'
];

if (!file_exists('../logs/')) {
    mkdir('../logs/', 0755, true);
}

file_put_contents($logFile, json_encode($requestData) . "\n", FILE_APPEND);

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to perform this action']);
    exit();
}

// Handle different API actions
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'update_status':
        updateAppointmentStatus();
        break;
    case 'get_appointments':
        getAppointments();
        break;
    case 'get_patient_appointments':
        getPatientAppointments();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

/**
 * Update appointment status (doctor only)
 */
function updateAppointmentStatus() {
    global $conn, $logFile;
    
    // Log the function entry
    file_put_contents($logFile, "Entering updateAppointmentStatus function\n", FILE_APPEND);
    
    // Only doctors can update appointment status
    if ($_SESSION['user_type'] !== 'doctor') {
        file_put_contents($logFile, "Error: Unauthorized user type: {$_SESSION['user_type']}\n", FILE_APPEND);
        echo json_encode(['success' => false, 'message' => 'Unauthorized action']);
        exit();
    }
    
    // Get and validate request data
    $appointmentId = filter_var($_POST['appointment_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
    $status = filter_var($_POST['status'] ?? '', FILTER_SANITIZE_STRING);
    $message = filter_var($_POST['message'] ?? '', FILTER_SANITIZE_STRING);
    
    // Log the request data
    file_put_contents($logFile, "Request data: " . json_encode([
        'appointment_id' => $appointmentId,
        'status' => $status,
        'message' => $message
    ]) . "\n", FILE_APPEND);
    
    // Validate required data
    if (empty($appointmentId) || empty($status) || !in_array($status, ['confirmed', 'cancelled'])) {
        file_put_contents($logFile, "Error: Invalid appointment data\n", FILE_APPEND);
        echo json_encode(['success' => false, 'message' => 'Invalid appointment data']);
        exit();
    }
    
    try {
        // Simple approach without transactions to minimize errors
        
        // Check if appointment belongs to current doctor
        $stmt = $conn->prepare("
            SELECT a.*, u.full_name AS patient_name 
            FROM appointments a
            JOIN users u ON a.user_id = u.id
            WHERE a.id = ? AND a.doctor_id = ?
        ");
        $stmt->execute([$appointmentId, $_SESSION['user_id']]);
        $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$appointment) {
            file_put_contents($logFile, "Error: Appointment not found or does not belong to this doctor\n", FILE_APPEND);
            echo json_encode(['success' => false, 'message' => 'Appointment not found or does not belong to you']);
            exit();
        }
        
        // Log found appointment
        file_put_contents($logFile, "Found appointment: " . json_encode($appointment) . "\n", FILE_APPEND);
        
        // Update appointment status - single operation
        $stmt = $conn->prepare("
            UPDATE appointments 
            SET status = ?, 
                doctor_message = ?,
                updated_at = NOW() 
            WHERE id = ?
        ");
        $result = $stmt->execute([$status, $message, $appointmentId]);
        
        if (!$result) {
            file_put_contents($logFile, "Error executing update query: " . json_encode($stmt->errorInfo()) . "\n", FILE_APPEND);
            echo json_encode(['success' => false, 'message' => 'Failed to update appointment status']);
            exit();
        }
        
        // Create notification in a separate operation
        $notificationTitle = ($status == 'confirmed') ? 'Appointment Confirmed' : 'Appointment Cancelled';
        $notificationMessage = ($status == 'confirmed') 
            ? "Your appointment has been confirmed by Dr. {$_SESSION['full_name']}." 
            : "Your appointment has been cancelled by Dr. {$_SESSION['full_name']}.";
        
        if (!empty($message)) {
            $notificationMessage .= " Message from doctor: $message";
        }
        
        try {
            $stmt = $conn->prepare("
                INSERT INTO notifications (user_id, title, message, type, reference_id, created_at)
                VALUES (?, ?, ?, 'appointment', ?, NOW())
            ");
            $stmt->execute([$appointment['user_id'], $notificationTitle, $notificationMessage, $appointmentId]);
            file_put_contents($logFile, "Created notification successfully\n", FILE_APPEND);
        } catch (Exception $e) {
            // Log but continue - notification is not critical
            file_put_contents($logFile, "Notice: Could not create notification but continuing: " . $e->getMessage() . "\n", FILE_APPEND);
        }
        
        file_put_contents($logFile, "Successfully updated appointment status to $status\n", FILE_APPEND);
        
        // Return success response
        echo json_encode([
            'success' => true, 
            'message' => 'Appointment status updated successfully',
            'appointment' => [
                'id' => $appointment['id'],
                'status' => $status,
                'message' => $message,
                'patient_name' => $appointment['patient_name'],
                'appointment_date' => date('F j, Y', strtotime($appointment['appointment_date'])),
                'appointment_time' => date('g:i A', strtotime($appointment['appointment_time']))
            ]
        ]);
        
    } catch (PDOException $e) {
        file_put_contents($logFile, "Database error: " . $e->getMessage() . "\n", FILE_APPEND);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

/**
 * Get doctor's appointments (doctor only)
 */
function getAppointments() {
    global $conn;
    
    // Only doctors can get their appointments
    if ($_SESSION['user_type'] !== 'doctor') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized action']);
        exit();
    }
    
    try {
        // Get doctor's appointments
        $stmt = $conn->prepare("
            SELECT 
                a.*, 
                u.full_name as patient_name, 
                u.email as patient_email, 
                u.phone as patient_phone
            FROM appointments a 
            JOIN users u ON a.user_id = u.id 
            WHERE a.doctor_id = ? 
            ORDER BY 
                CASE 
                    WHEN a.status = 'pending' THEN 1
                    WHEN a.status = 'confirmed' THEN 2
                    ELSE 3
                END,
                a.appointment_date ASC, 
                a.appointment_time ASC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format data for frontend
        $formattedAppointments = [];
        foreach ($appointments as $appointment) {
            $formattedAppointments[] = [
                'id' => $appointment['id'],
                'patient_name' => $appointment['patient_name'],
                'patient_email' => $appointment['patient_email'],
                'patient_phone' => $appointment['patient_phone'],
                'appointment_date' => date('F j, Y', strtotime($appointment['appointment_date'])),
                'appointment_time' => date('g:i A', strtotime($appointment['appointment_time'])),
                'status' => $appointment['status'],
                'doctor_message' => $appointment['doctor_message'],
                'notes' => $appointment['notes']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'appointments' => $formattedAppointments
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

/**
 * Get patient's appointments (patient only)
 */
function getPatientAppointments() {
    global $conn;
    
    // Only patients can get their appointments
    if ($_SESSION['user_type'] !== 'user') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized action']);
        exit();
    }
    
    try {
        // Get patient's appointments
        $stmt = $conn->prepare("
            SELECT 
                a.*,
                d.full_name as doctor_name,
                d.specialization,
                d.phone as doctor_phone,
                d.email as doctor_email
            FROM appointments a 
            JOIN doctors d ON a.doctor_id = d.id 
            WHERE a.user_id = ? 
            ORDER BY 
                CASE 
                    WHEN a.status = 'confirmed' AND a.appointment_date >= CURDATE() THEN 1
                    WHEN a.status = 'pending' THEN 2
                    ELSE 3
                END,
                a.appointment_date ASC, 
                a.appointment_time ASC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format data for frontend
        $formattedAppointments = [];
        foreach ($appointments as $appointment) {
            $formattedAppointments[] = [
                'id' => $appointment['id'],
                'doctor_name' => $appointment['doctor_name'],
                'specialization' => $appointment['specialization'],
                'doctor_phone' => $appointment['doctor_phone'],
                'doctor_email' => $appointment['doctor_email'],
                'appointment_date' => date('F j, Y', strtotime($appointment['appointment_date'])),
                'appointment_time' => date('g:i A', strtotime($appointment['appointment_time'])),
                'status' => $appointment['status'],
                'doctor_message' => $appointment['doctor_message'],
                'notes' => $appointment['notes']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'appointments' => $formattedAppointments
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} 