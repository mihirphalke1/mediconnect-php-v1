<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Get type from query parameter
$type = isset($_GET['type']) ? $_GET['type'] : '';
$title = '';
$message = '';
$redirect_url = '';
$redirect_text = '';

switch ($type) {
    case 'appointment':
        $title = 'Appointment Booked Successfully';
        $message = 'Your appointment request has been submitted and is pending confirmation from the doctor. You will receive a notification once the doctor confirms or reschedules.';
        $redirect_url = '../dashboard.php';
        $redirect_text = 'View My Dashboard';
        break;
    case 'feedback':
        $title = 'Feedback Submitted Successfully';
        $message = 'Thank you for your valuable feedback. We appreciate your input and will use it to improve our services.';
        $redirect_url = '../dashboard.php';
        $redirect_text = 'Back to Dashboard';
        break;
    default:
        $title = 'Operation Completed Successfully';
        $message = 'Thank you for using MediConnect. Your request has been processed successfully.';
        $redirect_url = '../index.php';
        $redirect_text = 'Back to Home';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Thank You - MediConnect</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/styles/styles.css">
    <link rel="stylesheet" href="../assets/styles/practo-enhanced.css">
    <link rel="stylesheet" href="../assets/styles/profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .thank-you-container {
            text-align: center;
            padding: 60px 20px;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .success-icon {
            font-size: 80px;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        
        .thank-you-title {
            font-size: 30px;
            color: #2d3748;
            margin-bottom: 20px;
        }
        
        .thank-you-message {
            font-size: 18px;
            color: #4a5568;
            line-height: 1.6;
            margin-bottom: 40px;
        }
        
        .redirect-container {
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <a href="../index.php" class="logo">
                    <i class="fas fa-heartbeat"></i>
                    <h1>MediConnect</h1>
                </a>
                <nav>
                    <a href="../index.php">Home</a>
                    <?php if($_SESSION['user_type'] == 'user'): ?>
                        <a href="../dashboard.php">My Dashboard</a>
                        <a href="doctors.php">Find Doctors</a>
                        <a href="book_appointment.php">Book Appointment</a>
                        <a href="../medical_records.php">Medical Records</a>
                    <?php else: ?>
                        <a href="../doctor_dashboard.php">Doctor Dashboard</a>
                        <a href="appointments.php">My Appointments</a>
                    <?php endif; ?>
                    <a href="profile.php">Profile</a>
                    <a href="feedback.php">Feedback</a>
                    <a href="../logout.php" class="btn btn-secondary">Logout</a>
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="thank-you-container fade-in">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1 class="thank-you-title"><?php echo $title; ?></h1>
            <p class="thank-you-message"><?php echo $message; ?></p>
            <div class="redirect-container">
                <a href="<?php echo $redirect_url; ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-right"></i> <?php echo $redirect_text; ?>
                </a>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <a href="doctors.php">Find Doctors</a>
                    <?php if($_SESSION['user_type'] == 'user'): ?>
                        <a href="../dashboard.php">Dashboard</a>
                    <?php else: ?>
                        <a href="../doctor_dashboard.php">Dashboard</a>
                    <?php endif; ?>
                    <a href="feedback.php">Feedback</a>
                </div>
                <div class="footer-section">
                    <h4>Legal</h4>
                    <a href="terms.php">Terms of Service</a>
                    <a href="privacy.php">Privacy Policy</a>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <p><i class="fas fa-envelope"></i> support@mediconnect.com</p>
                    <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 MediConnect. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
