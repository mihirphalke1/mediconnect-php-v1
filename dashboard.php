<?php
/**
 * Patient Dashboard
 * Displays user's appointments, medical records, and notifications
 */

// Include configuration
require_once 'includes/config.php';

// Check if user is logged in as a patient
requireRole('user');

// Mark notification as read if requested
if (isset($_POST['mark_read']) && isset($_POST['notification_id'])) {
    $notification_id = filter_var($_POST['notification_id'], FILTER_SANITIZE_NUMBER_INT);
    $stmt = $conn->prepare("UPDATE notifications SET read_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->execute([$notification_id, $_SESSION['user_id']]);
    
    // Redirect to avoid form resubmission
    header("Location: dashboard.php");
    exit();
}

// Fetch user's appointments with doctor details
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

// Count upcoming appointments (confirmed and future date)
$stmt = $conn->prepare("
    SELECT COUNT(*) FROM appointments 
    WHERE user_id = ? AND status = 'confirmed' AND appointment_date >= CURDATE()
");
$stmt->execute([$_SESSION['user_id']]);
$upcoming_count = $stmt->fetchColumn();

// Count pending appointments
$stmt = $conn->prepare("
    SELECT COUNT(*) FROM appointments 
    WHERE user_id = ? AND status = 'pending'
");
$stmt->execute([$_SESSION['user_id']]);
$pending_count = $stmt->fetchColumn();

// Count medical records
$stmt = $conn->prepare("
    SELECT COUNT(*) FROM medical_records 
    WHERE user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$records_count = $stmt->fetchColumn();

// Fetch unread notifications
$stmt = $conn->prepare("
    SELECT * FROM notifications 
    WHERE user_id = ? AND read_at IS NULL 
    ORDER BY created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's medical records
$stmt = $conn->prepare("
    SELECT mr.*, d.full_name as doctor_name 
    FROM medical_records mr 
    JOIN doctors d ON mr.doctor_id = d.id 
    WHERE mr.user_id = ? 
    ORDER BY mr.uploaded_at DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$medical_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set active page for navigation
$activePage = 'dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Patient Dashboard - <?php echo APP_NAME; ?></title>
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
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
            <p>Manage your appointments and health records here.</p>
            
            <?php echo showFlashMessage(); ?>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card">
                <i class="fas fa-calendar-check"></i>
                <h3>Upcoming Appointments</h3>
                <p class="stat-number"><?php echo $upcoming_count; ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-file-medical"></i>
                <h3>Medical Records</h3>
                <p class="stat-number"><?php echo $records_count; ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-clock"></i>
                <h3>Pending Appointments</h3>
                <p class="stat-number"><?php echo $pending_count; ?></p>
            </div>
        </div>

        <div class="btn-container">
            <a href="pages/book_appointment.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Book New Appointment
            </a>
            <a href="medical_records.php" class="btn btn-secondary">
                <i class="fas fa-file-medical"></i> View Medical Records
            </a>
        </div>

        <?php if (!empty($notifications)): ?>
            <div class="notifications-section">
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item fade-in">
                        <div class="notification-content">
                            <h4><?php echo htmlspecialchars($notification['title'] ?? 'Notification'); ?></h4>
                            <p><?php echo htmlspecialchars($notification['message']); ?></p>
                            <small><?php echo date('F j, Y g:i A', strtotime($notification['created_at'])); ?></small>
                        </div>
                        <form method="POST" class="notification-action">
                            <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                            <button type="submit" name="mark_read" class="btn btn-sm">
                                <i class="fas fa-check"></i> Mark as Read
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-calendar-check"></i> Your Appointments</h3>
                    <a href="pages/book_appointment.php" class="btn btn-primary btn-sm">Book New</a>
                </div>
                <div class="card-content">
                    <?php if (empty($appointments)): ?>
                        <p class="no-data">No appointments scheduled. <a href="pages/book_appointment.php">Book your first appointment</a></p>
                    <?php else: ?>
                        <?php foreach ($appointments as $appointment): ?>
                            <div class="appointment-item">
                                <div class="appointment-info">
                                    <h4>Dr. <?php echo htmlspecialchars($appointment['doctor_name']); ?></h4>
                                    <p><?php echo htmlspecialchars($appointment['specialization']); ?></p>
                                    <p>
                                        <i class="fas fa-calendar"></i> 
                                        <?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?>
                                    </p>
                                    <p>
                                        <i class="fas fa-clock"></i> 
                                        <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?>
                                    </p>
                                    <p>
                                        <i class="fas fa-phone"></i> 
                                        <?php echo htmlspecialchars($appointment['doctor_phone']); ?>
                                    </p>
                                    <p>
                                        <i class="fas fa-envelope"></i> 
                                        <?php echo htmlspecialchars($appointment['doctor_email']); ?>
                                    </p>
                                    <span class="status status-<?php echo strtolower($appointment['status']); ?>">
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                    <?php if ($appointment['doctor_message']): ?>
                                        <div class="doctor-message">
                                            <strong>Doctor's Message:</strong><br>
                                            <?php echo htmlspecialchars($appointment['doctor_message']); ?>
                                        </div>
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
                </div>
                <div class="card-content">
                    <?php if (empty($medical_records)): ?>
                        <p class="no-data">No medical records available.</p>
                    <?php else: ?>
                        <?php foreach ($medical_records as $record): ?>
                            <div class="record-item">
                                <div class="record-info">
                                    <h4><?php echo htmlspecialchars($record['file_name']); ?></h4>
                                    <p>Dr. <?php echo htmlspecialchars($record['doctor_name']); ?></p>
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