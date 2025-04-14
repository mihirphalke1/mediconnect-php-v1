<?php
/**
 * Patient Records
 * Allows doctors to view all medical records they have uploaded
 */

// Include configuration
require_once '../includes/config.php';

// Check if user is logged in as a doctor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'doctor') {
    header("Location: ../login.php");
    exit();
}

// Fetch doctor's uploaded medical records
$stmt = $conn->prepare("
    SELECT mr.*, u.full_name as patient_name 
    FROM medical_records mr 
    JOIN users u ON mr.user_id = u.id 
    WHERE mr.doctor_id = ? 
    ORDER BY mr.uploaded_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$medical_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set active page for navigation
$activePage = 'patient_records.php';
$isSubdirectory = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Patient Records - <?php echo APP_NAME; ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/styles/styles.css">
    <link rel="stylesheet" href="../assets/styles/practo-enhanced.css">
    <link rel="stylesheet" href="../assets/styles/profile.css">
    <link rel="stylesheet" href="../assets/styles/doctor.css">
    <link rel="stylesheet" href="../assets/styles/minimalist-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container dashboard-container">
        <section class="profile-header-section fade-in">
            <div class="profile-header">
                <div class="profile-info">
                    <h1><i class="fas fa-file-medical"></i> Patient Records</h1>
                    <p class="lead">View and manage all medical records you've uploaded for patients.</p>
                </div>
            </div>
        </section>

        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['flash_type']; ?>">
                <?php echo $_SESSION['flash_message']; ?>
            </div>
            <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
        <?php endif; ?>

        <div class="btn-container">
            <a href="../doctor_upload.php" class="btn btn-primary">
                <i class="fas fa-file-upload"></i> Upload New Record
            </a>
        </div>

        <section class="card">
            <div class="dashboard-card">
                <div class="card-content">
                    <?php if (empty($medical_records)): ?>
                        <div class="no-data">
                            <i class="fas fa-file-medical-alt"></i>
                            <p>You haven't uploaded any medical records yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="records-list">
                            <?php foreach ($medical_records as $record): ?>
                                <div class="record-item">
                                    <div class="record-info">
                                        <h4><?php echo htmlspecialchars($record['file_name'] ?? 'Untitled Document'); ?></h4>
                                        <p>
                                            <i class="fas fa-user"></i>
                                            Patient: <?php echo htmlspecialchars($record['patient_name'] ?? 'Unknown Patient'); ?>
                                        </p>
                                        <p>
                                            <i class="fas fa-tag"></i>
                                            Type: <?php echo htmlspecialchars($record['record_type'] ?? 'Unspecified'); ?>
                                        </p>
                                        <p>
                                            <i class="fas fa-calendar-alt"></i>
                                            Date: <?php 
                                                $recordDate = $record['record_date'] ?? null;
                                                echo $recordDate ? date('F j, Y', strtotime($recordDate)) : 'Not specified';
                                            ?>
                                        </p>
                                        <p>
                                            <i class="fas fa-clock"></i>
                                            Uploaded: <?php 
                                                $uploadDate = $record['uploaded_at'] ?? null;
                                                echo $uploadDate ? date('F j, Y g:i A', strtotime($uploadDate)) : 'Not specified';
                                            ?>
                                        </p>
                                        <?php if (!empty($record['notes'])): ?>
                                            <p>
                                                <i class="fas fa-sticky-note"></i>
                                                Notes: <?php echo htmlspecialchars($record['notes']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="record-actions">
                                        <a href="<?php echo htmlspecialchars($record['file_path'] ?? '#'); ?>" 
                                           class="btn btn-primary btn-sm" 
                                           target="_blank">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="../download_record.php?id=<?php echo $record['id']; ?>" 
                                           class="btn btn-secondary btn-sm">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html> 