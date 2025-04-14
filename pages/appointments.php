<?php
/**
 * Doctor Appointments Page
 * Comprehensive interface for managing appointments
 */

// Include configuration
require_once '../includes/config.php';

// Check if user is logged in as a doctor
requireRole('doctor');

// Get date filter
$date_filter = isset($_GET['date']) ? filter_var($_GET['date'], FILTER_SANITIZE_STRING) : '';
$status_filter = isset($_GET['status']) ? filter_var($_GET['status'], FILTER_SANITIZE_STRING) : '';

// Prepare SQL query with filters
$sql = "
    SELECT 
        a.*, 
        u.full_name as patient_name, 
        u.email as patient_email, 
        u.phone as patient_phone
    FROM appointments a 
    JOIN users u ON a.user_id = u.id 
    WHERE a.doctor_id = ?
";

$params = [$_SESSION['user_id']];

// Add date filter if provided
if (!empty($date_filter)) {
    $sql .= " AND a.appointment_date = ?";
    $params[] = $date_filter;
}

// Add status filter if provided
if (!empty($status_filter) && in_array($status_filter, ['pending', 'confirmed', 'cancelled'])) {
    $sql .= " AND a.status = ?";
    $params[] = $status_filter;
}

// Add ordering
$sql .= "
    ORDER BY 
        CASE 
            WHEN a.status = 'pending' THEN 1
            WHEN a.status = 'confirmed' AND a.appointment_date >= CURDATE() THEN 2
            ELSE 3
        END,
        a.appointment_date ASC, 
        a.appointment_time ASC
";

// Fetch appointments
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique dates for filter
$stmt = $conn->prepare("
    SELECT DISTINCT appointment_date 
    FROM appointments 
    WHERE doctor_id = ? 
    ORDER BY appointment_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$dates = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get appointment counts by status
$stmt = $conn->prepare("
    SELECT status, COUNT(*) as count
    FROM appointments 
    WHERE doctor_id = ?
    GROUP BY status
");
$stmt->execute([$_SESSION['user_id']]);
$status_counts = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $status_counts[$row['status']] = $row['count'];
}

$pending_count = $status_counts['pending'] ?? 0;
$confirmed_count = $status_counts['confirmed'] ?? 0;
$cancelled_count = $status_counts['cancelled'] ?? 0;
$total_count = $pending_count + $confirmed_count + $cancelled_count;

// Set active page for navigation
$activePage = 'appointments.php';
$isSubdirectory = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Appointments - <?php echo APP_NAME; ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/styles/styles.css">
    <link rel="stylesheet" href="../assets/styles/practo-enhanced.css">
    <link rel="stylesheet" href="../assets/styles/profile.css">
    <link rel="stylesheet" href="../assets/styles/doctor.css">
    <link rel="stylesheet" href="../assets/styles/minimalist-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .filter-label {
            font-weight: 500;
            min-width: 50px;
        }
        
        .appointment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .appointment-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .appointment-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        
        .appointment-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
        }
        
        .appointment-card.status-pending::before {
            background-color: #ffc107;
        }
        
        .appointment-card.status-confirmed::before {
            background-color: #28a745;
        }
        
        .appointment-card.status-cancelled::before {
            background-color: #dc3545;
        }
        
        .appointment-date {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 10px;
        }
        
        .appointment-patient {
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 10px;
        }
        
        .appointment-details {
            margin-bottom: 15px;
        }
        
        .appointment-details p {
            margin: 5px 0;
            font-size: 0.9rem;
        }
        
        .appointment-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 15px;
        }
        
        .appointment-notes {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-left: 5px;
            color: #fff;
            font-size: 0.7rem;
        }
        
        .badge-pending {
            background-color: #ffc107;
        }
        
        .badge-confirmed {
            background-color: #28a745;
        }
        
        .badge-cancelled {
            background-color: #dc3545;
        }
        
        .stats-container {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .stat-box {
            text-align: center;
            padding: 15px 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            flex: 1;
            min-width: 140px;
            margin: 5px;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 10px 0;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #666;
        }
        
        .doctor-message {
            margin-top: 10px;
            font-style: italic;
        }
    </style>
</head>
<body class="doctor-dashboard">
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-calendar-check"></i> Appointment Management</h1>
            <p>View and manage all your patient appointments</p>
        </div>
        
        <?php echo showFlashMessage(); ?>
        
        <div class="stats-container">
            <div class="stat-box">
                <div class="stat-label">Total</div>
                <div class="stat-number"><?php echo $total_count; ?></div>
                <div>Appointments</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Pending</div>
                <div class="stat-number pending-count"><?php echo $pending_count; ?></div>
                <div>Need Action</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Confirmed</div>
                <div class="stat-number"><?php echo $confirmed_count; ?></div>
                <div>Upcoming</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Cancelled</div>
                <div class="stat-number"><?php echo $cancelled_count; ?></div>
                <div>Declined</div>
            </div>
        </div>
        
        <div class="filter-container">
            <form method="GET" action="" class="d-flex flex-wrap">
                <div class="filter-group">
                    <span class="filter-label">Date:</span>
                    <select name="date" class="form-control">
                        <option value="">All Dates</option>
                        <?php foreach ($dates as $date): ?>
                            <option value="<?php echo $date; ?>" <?php echo ($date_filter == $date) ? 'selected' : ''; ?>>
                                <?php echo date('F j, Y', strtotime($date)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <span class="filter-label">Status:</span>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo ($status_filter == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="cancelled" <?php echo ($status_filter == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Filter</button>
                
                <?php if (!empty($date_filter) || !empty($status_filter)): ?>
                    <a href="appointments.php" class="btn btn-secondary ml-2">Clear Filters</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="appointments-container appointment-grid">
            <?php if (empty($appointments)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No appointments found matching your filters.
                </div>
            <?php else: ?>
                <?php foreach ($appointments as $appointment): ?>
                    <div class="appointment-card status-<?php echo $appointment['status']; ?>" data-appointment-id="<?php echo $appointment['id']; ?>">
                        <div class="appointment-date">
                            <i class="fas fa-calendar"></i> <?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?>
                            <span class="ml-2"><i class="fas fa-clock"></i> <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></span>
                        </div>
                        
                        <div class="appointment-patient">
                            <?php echo htmlspecialchars($appointment['patient_name']); ?>
                        </div>
                        
                        <div class="appointment-details">
                            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($appointment['patient_email']); ?></p>
                            <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($appointment['patient_phone']); ?></p>
                        </div>
                        
                        <span class="appointment-status status-<?php echo strtolower($appointment['status']); ?>">
                            <?php echo ucfirst($appointment['status']); ?>
                        </span>
                        
                        <?php if (!empty($appointment['notes'])): ?>
                            <div class="appointment-notes">
                                <strong>Patient Notes:</strong><br>
                                <?php echo htmlspecialchars($appointment['notes']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($appointment['status'] == 'pending'): ?>
                            <div class="form-group">
                                <label for="message-<?php echo $appointment['id']; ?>">Message to Patient:</label>
                                <textarea name="message" id="message-<?php echo $appointment['id']; ?>" 
                                        placeholder="Optional message for the patient" class="form-control"></textarea>
                            </div>
                            <div class="button-group">
                                <button type="button" data-id="<?php echo $appointment['id']; ?>" class="btn btn-success accept-appointment-btn">
                                    <i class="fas fa-check"></i> Accept
                                </button>
                                <button type="button" data-id="<?php echo $appointment['id']; ?>" class="btn btn-danger decline-appointment-btn">
                                    <i class="fas fa-times"></i> Decline
                                </button>
                            </div>
                        <?php else: ?>
                            <?php if (!empty($appointment['doctor_message'])): ?>
                                <div class="doctor-message">
                                    <strong>Your message:</strong><br>
                                    <?php echo htmlspecialchars($appointment['doctor_message']); ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    
    <!-- Include appointment management script -->
    <script src="../assets/js/appointment.js"></script>
</body>
</html> 