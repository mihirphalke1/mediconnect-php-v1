<?php
/**
 * Prescriptions Page
 * Displays patient's prescriptions
 */

// Include configuration
require_once 'includes/config.php';

// Check if user is logged in as a patient
requireRole('user');

// Fetch user's prescriptions
$stmt = $conn->prepare("
    SELECT p.*, d.full_name as doctor_name, d.specialization
    FROM prescriptions p 
    JOIN doctors d ON p.doctor_id = d.id 
    WHERE p.user_id = ? 
    ORDER BY p.id DESC
");
$stmt->execute([$_SESSION['user_id']]);
$prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set active page for navigation
$activePage = 'prescriptions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Prescriptions - <?php echo APP_NAME; ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/styles/styles.css">
    <link rel="stylesheet" href="assets/styles/practo-enhanced.css">
    <link rel="stylesheet" href="assets/styles/profile.css">
    <link rel="stylesheet" href="assets/styles/minimalist-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .prescriptions-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .prescription-item {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .prescription-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        
        .prescription-header {
            background: linear-gradient(135deg, #3f8069, #43b692);
            color: white;
            padding: 15px 20px;
            position: relative;
        }
        
        .prescription-header h3 {
            margin: 0;
            color: white;
            font-size: 18px;
        }
        
        .prescription-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            font-size: 14px;
            margin-top: 5px;
            color: rgba(255,255,255,0.9);
        }
        
        .prescription-meta div {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .prescription-content {
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
            color: #43b692;
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
        
        .no-prescriptions {
            text-align: center;
            padding: 50px 0;
            color: #718096;
        }
        
        .no-prescriptions i {
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
                    <h1><i class="fas fa-prescription"></i> My Prescriptions</h1>
                    <p class="lead">View and download prescriptions from your doctors.</p>
                </div>
            </div>
        </section>

        <div class="prescriptions-container fade-in">
            <?php echo showFlashMessage(); ?>
            
            <?php if (empty($prescriptions)): ?>
                <div class="no-prescriptions">
                    <i class="fas fa-file-prescription"></i>
                    <h3>No Prescriptions Found</h3>
                    <p>You don't have any prescriptions in the system yet.</p>
                    <p>Your doctor will upload prescriptions after your appointments.</p>
                </div>
            <?php else: ?>
                <?php foreach ($prescriptions as $prescription): ?>
                    <div class="prescription-item">
                        <div class="prescription-header">
                            <h3><?php echo htmlspecialchars($prescription['title']); ?></h3>
                            <div class="prescription-meta">
                                <div>
                                    <i class="fas fa-user-md"></i>
                                    Dr. <?php echo htmlspecialchars($prescription['doctor_name']); ?>
                                </div>
                                <div>
                                    <i class="fas fa-stethoscope"></i>
                                    <?php echo htmlspecialchars($prescription['specialization']); ?>
                                </div>
                                <?php if(isset($prescription['uploaded_at'])): ?>
                                <div>
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('F j, Y', strtotime($prescription['uploaded_at'])); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="prescription-content">
                            <div class="file-info">
                                <div class="file-icon">
                                    <i class="fas fa-file-prescription"></i>
                                </div>
                                <div class="file-details">
                                    <div class="file-name"><?php echo htmlspecialchars($prescription['title']); ?></div>
                                    <div class="file-meta">
                                        <?php if(!empty($prescription['file_size'])): ?>
                                            <?php echo round($prescription['file_size'] / 1024, 2); ?> KB •
                                        <?php endif; ?>
                                        PDF Prescription
                                    </div>
                                </div>
                                <div class="file-actions">
                                    <a href="<?php echo htmlspecialchars($prescription['file_path']); ?>" class="btn btn-primary" target="_blank">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="<?php echo htmlspecialchars($prescription['file_path']); ?>" class="btn btn-secondary" download>
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