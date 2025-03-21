<?php
/**
 * Doctor Dashboard
 * Displays doctor's appointments, medical records, and notifications
 */

// Include configuration
require_once 'includes/config.php';

// Check if user is logged in as a doctor
requireRole('doctor');

// Handle appointment status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $appointment_id = filter_var($_POST['appointment_id'], FILTER_SANITIZE_NUMBER_INT);
    $new_status = filter_var($_POST['status'], FILTER_SANITIZE_STRING);
    $message = isset($_POST['message']) ? filter_var($_POST['message'], FILTER_SANITIZE_STRING) : '';
    
    try {
        // Start transaction
        $conn->beginTransaction();
        
        // Update appointment status and message
        $stmt = $conn->prepare("
            UPDATE appointments 
            SET status = ?, 
                doctor_message = ?,
                updated_at = NOW() 
            WHERE id = ? AND doctor_id = ?
        ");
        $stmt->execute([$new_status, $message, $appointment_id, $_SESSION['user_id']]);
        
        // Get patient ID
        $stmt = $conn->prepare("SELECT user_id FROM appointments WHERE id = ?");
        $stmt->execute([$appointment_id]);
        $patient_id = $stmt->fetchColumn();
        
        // Create notification for the patient
        $notification_title = ($new_status == 'confirmed') ? 'Appointment Confirmed' : 'Appointment Cancelled';
        $notification_message = ($new_status == 'confirmed') 
            ? "Your appointment has been confirmed by Dr. {$_SESSION['full_name']}." 
            : "Your appointment has been cancelled by Dr. {$_SESSION['full_name']}.";
        
        if (!empty($message)) {
            $notification_message .= " Message from doctor: $message";
        }
        
        $stmt = $conn->prepare("
            INSERT INTO notifications (user_id, title, message, type, reference_id, created_at)
            VALUES (?, ?, ?, 'appointment', ?, NOW())
        ");
        $stmt->execute([$patient_id, $notification_title, $notification_message, $appointment_id]);
        
        // Commit transaction
        $conn->commit();
        
        setFlashMessage('success', "Appointment status updated successfully!");
        header("Location: doctor_dashboard.php");
        exit();
    } catch(PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        setFlashMessage('error', "Error updating appointment status: " . $e->getMessage());
    }
}

// Count today's appointments
$today = date('Y-m-d');
$stmt = $conn->prepare("
    SELECT COUNT(*) FROM appointments 
    WHERE doctor_id = ? AND appointment_date = ? AND status = 'confirmed'
");
$stmt->execute([$_SESSION['user_id'], $today]);
$today_appointments = $stmt->fetchColumn();

// Count pending appointments
$stmt = $conn->prepare("
    SELECT COUNT(*) FROM appointments 
    WHERE doctor_id = ? AND status = 'pending'
");
$stmt->execute([$_SESSION['user_id']]);
$pending_appointments = $stmt->fetchColumn();

// Count total patients (unique patients who have appointments with this doctor)
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT user_id) FROM appointments 
    WHERE doctor_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$total_patients = $stmt->fetchColumn();

// Fetch doctor's appointments with patient details
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

// Fetch doctor's uploaded medical records
$stmt = $conn->prepare("
    SELECT mr.*, u.full_name as patient_name 
    FROM medical_records mr 
    JOIN users u ON mr.user_id = u.id 
    WHERE mr.doctor_id = ? 
    ORDER BY mr.uploaded_at DESC
    LIMIT 10
");
$stmt->execute([$_SESSION['user_id']]);
$medical_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch unread notifications
$stmt = $conn->prepare("
    SELECT * FROM notifications 
    WHERE user_id = ? AND read_at IS NULL 
    ORDER BY created_at DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set active page for navigation
$activePage = 'doctor_dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Doctor Dashboard - <?php echo APP_NAME; ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/styles/styles.css">
    <link rel="stylesheet" href="assets/styles/practo-enhanced.css">
    <link rel="stylesheet" href="assets/styles/profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container dashboard-container">
        <div class="welcome-section">
            <h1>Welcome, Dr. <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
            <p>Manage your appointments and patient records here.</p>
            
            <?php echo showFlashMessage(); ?>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card">
                <i class="fas fa-calendar-check"></i>
                <h3>Today's Appointments</h3>
                <p class="stat-number"><?php echo $today_appointments; ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-user-clock"></i>
                <h3>Pending Appointments</h3>
                <p class="stat-number"><?php echo $pending_appointments; ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <h3>Total Patients</h3>
                <p class="stat-number"><?php echo $total_patients; ?></p>
            </div>
        </div>

        <div class="btn-container">
            <a href="pages/appointments.php" class="btn btn-primary">
                <i class="fas fa-calendar-alt"></i> View All Appointments
            </a>
            <a href="pages/patient_records.php" class="btn btn-secondary">
                <i class="fas fa-file-medical"></i> Patient Records
            </a>
            <a href="doctor_upload.php" class="btn btn-primary">
                <i class="fas fa-file-upload"></i> Upload Medical Records
            </a>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-calendar-check"></i> Appointments</h3>
                </div>
                <div class="card-content">
                    <?php if (empty($appointments)): ?>
                        <p class="no-data">No appointments scheduled.</p>
                    <?php else: ?>
                        <?php foreach ($appointments as $appointment): ?>
                            <div class="appointment-item">
                                <div class="appointment-info">
                                    <h4><?php echo htmlspecialchars($appointment['patient_name']); ?></h4>
                                    <p>
                                        <i class="fas fa-envelope"></i> 
                                        <?php echo htmlspecialchars($appointment['patient_email']); ?>
                                    </p>
                                    <p>
                                        <i class="fas fa-phone"></i> 
                                        <?php echo htmlspecialchars($appointment['patient_phone']); ?>
                                    </p>
                                    <p>
                                        <i class="fas fa-calendar"></i> 
                                        <?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?>
                                    </p>
                                    <p>
                                        <i class="fas fa-clock"></i> 
                                        <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?>
                                    </p>
                                    <p>
                                        <i class="fas fa-comment"></i> 
                                        Notes: <?php echo htmlspecialchars($appointment['notes'] ?: 'No notes provided'); ?>
                                    </p>
                                    <span class="status status-<?php echo strtolower($appointment['status']); ?>">
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                </div>
                                <div class="appointment-actions">
                                    <?php if ($appointment['status'] == 'pending'): ?>
                                        <form method="POST" action="" class="status-form">
                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                            <div class="form-group">
                                                <label for="message-<?php echo $appointment['id']; ?>">Message to Patient:</label>
                                                <textarea name="message" id="message-<?php echo $appointment['id']; ?>" 
                                                        placeholder="Optional message for the patient"></textarea>
                                            </div>
                                            <div class="button-group">
                                                <button type="submit" name="status" value="confirmed" class="btn btn-success">
                                                    <i class="fas fa-check"></i> Accept
                                                </button>
                                                <button type="submit" name="status" value="cancelled" class="btn btn-danger">
                                                    <i class="fas fa-times"></i> Decline
                                                </button>
                                            </div>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    <?php else: ?>
                                        <?php if ($appointment['doctor_message']): ?>
                                            <div class="doctor-message">
                                                <strong>Your message:</strong><br>
                                                <?php echo htmlspecialchars($appointment['doctor_message']); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-file-medical"></i> Medical Records</h3>
                    <a href="doctor_upload.php" class="btn btn-sm btn-primary">Upload New</a>
                </div>
                <div class="card-content">
                    <?php if (empty($medical_records)): ?>
                        <p class="no-data">No medical records uploaded.</p>
                    <?php else: ?>
                        <?php foreach ($medical_records as $record): ?>
                            <div class="record-item">
                                <div class="record-info">
                                    <h4><?php echo htmlspecialchars($record['file_name']); ?></h4>
                                    <p>Patient: <?php echo htmlspecialchars($record['patient_name']); ?></p>
                                    <p>
                                        <i class="fas fa-calendar"></i> 
                                        <?php echo date('F j, Y', strtotime($record['uploaded_at'])); ?>
                                    </p>
                                </div>
                                <a href="<?php echo htmlspecialchars($record['file_path']); ?>" 
                                   class="btn btn-secondary btn-sm" 
                                   target="_blank">
                                    View
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 