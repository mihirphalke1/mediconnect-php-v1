<?php
/**
 * Medical Records Page
 * Displays patient's medical records
 */

// Include configuration
require_once 'includes/config.php';

// Check if user is logged in as a patient
requireRole('user');

// Fetch user's medical records
$stmt = $conn->prepare("
    SELECT mr.*, d.full_name as doctor_name, d.specialization
    FROM medical_records mr 
    JOIN doctors d ON mr.doctor_id = d.id 
    WHERE mr.user_id = ? 
    ORDER BY mr.uploaded_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$medical_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set active page for navigation
$activePage = 'medical_records.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Medical Records - <?php echo APP_NAME; ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/styles/styles.css">
    <link rel="stylesheet" href="assets/styles/practo-enhanced.css">
    <link rel="stylesheet" href="assets/styles/profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .records-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .record-item {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .record-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        
        .record-header {
            background: linear-gradient(135deg, #4a6fa5, #5b86e5);
            color: white;
            padding: 15px 20px;
            position: relative;
        }
        
        .record-header h3 {
            margin: 0;
            color: white;
            font-size: 18px;
        }
        
        .record-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            font-size: 14px;
            margin-top: 5px;
            color: rgba(255,255,255,0.9);
        }
        
        .record-meta div {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .record-content {
            padding: 20px;
        }
        
        .file-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 6px;
        }
        
        .file-icon {
            font-size: 24px;
            color: #5b86e5;
        }
        
        .file-details {
            flex: 1;
        }
        
        .file-name {
            font-weight: 500;
            margin-bottom: 3px;
        }
        
        .file-meta {
            font-size: 13px;
            color: #718096;
        }
        
        .file-actions {
            display: flex;
            gap: 10px;
        }
        
        .file-actions a {
            padding: 6px 12px;
            font-size: 14px;
            border-radius: 4px;
        }
        
        .no-records {
            text-align: center;
            padding: 50px 0;
            color: #718096;
        }
        
        .no-records i {
            font-size: 48px;
            color: #cbd5e0;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <section class="profile-header-section fade-in">
            <div class="profile-header">
                <div class="profile-info">
                    <h1><i class="fas fa-file-medical"></i> Medical Records</h1>
                    <p class="lead">View and manage your health records and documents.</p>
                </div>
            </div>
        </section>

        <div class="records-container fade-in">
            <?php echo showFlashMessage(); ?>
            
            <?php if (empty($medical_records)): ?>
                <div class="no-records">
                    <i class="fas fa-file-medical-alt"></i>
                    <h3>No Medical Records Found</h3>
                    <p>You don't have any medical records in the system yet.</p>
                    <p>Your doctor will upload records after your appointments.</p>
                </div>
            <?php else: ?>
                <?php foreach ($medical_records as $record): ?>
                    <div class="record-item">
                        <div class="record-header">
                            <h3><?php echo htmlspecialchars($record['file_name']); ?></h3>
                            <div class="record-meta">
                                <div>
                                    <i class="fas fa-user-md"></i>
                                    Dr. <?php echo htmlspecialchars($record['doctor_name']); ?>
                                </div>
                                <div>
                                    <i class="fas fa-stethoscope"></i>
                                    <?php echo htmlspecialchars($record['specialization']); ?>
                                </div>
                                <div>
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('F j, Y', strtotime($record['uploaded_at'])); ?>
                                </div>
                            </div>
                        </div>
                        <div class="record-content">
                            <div class="file-info">
                                <div class="file-icon">
                                    <?php 
                                    $file_extension = pathinfo($record['file_path'], PATHINFO_EXTENSION);
                                    if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                                        echo '<i class="fas fa-file-image"></i>';
                                    } elseif ($file_extension == 'pdf') {
                                        echo '<i class="fas fa-file-pdf"></i>';
                                    } elseif (in_array($file_extension, ['doc', 'docx'])) {
                                        echo '<i class="fas fa-file-word"></i>';
                                    } else {
                                        echo '<i class="fas fa-file-medical"></i>';
                                    }
                                    ?>
                                </div>
                                <div class="file-details">
                                    <div class="file-name"><?php echo htmlspecialchars($record['file_name']); ?></div>
                                    <div class="file-meta">
                                        <?php if(!empty($record['file_size'])): ?>
                                            <?php echo round($record['file_size'] / 1024, 2); ?> KB •
                                        <?php endif; ?>
                                        <?php if(!empty($record['file_type'])): ?>
                                            <?php echo strtoupper($file_extension); ?> File
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="file-actions">
                                    <a href="<?php echo htmlspecialchars($record['file_path']); ?>" class="btn btn-primary" target="_blank">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="<?php echo htmlspecialchars($record['file_path']); ?>" class="btn btn-secondary" download>
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 