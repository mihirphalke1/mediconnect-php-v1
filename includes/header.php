<?php
/**
 * Common header component for MediConnect
 * Ensures consistent navigation across the application
 */

// Determine if we're in the root directory or a subdirectory
$isRoot = !isset($isSubdirectory) || $isSubdirectory === false;
$baseUrl = $isRoot ? '' : '../';

// Determine active page
$currentPage = basename($_SERVER['PHP_SELF']);
$activePage = isset($activePage) ? $activePage : $currentPage;

// Function to check if a page is active
function isActive($page, $activePage) {
    return ($page === $activePage) ? 'class="active"' : '';
}
?>
<link rel="stylesheet" href="<?php echo $baseUrl; ?>assets/styles/minimalist-theme.css">
<!-- Favicon -->
<link rel="icon" href="<?php echo $baseUrl; ?>assets/images/favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="<?php echo $baseUrl; ?>assets/images/favicon.ico" type="image/x-icon">
<link rel="apple-touch-icon" sizes="180x180" href="<?php echo $baseUrl; ?>assets/images/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo $baseUrl; ?>assets/images/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo $baseUrl; ?>assets/images/favicon-16x16.png">
<header>
    <div class="container">
        <div class="header-content">
            <a href="<?php echo $baseUrl; ?>index.php" class="logo">
                <i class="fas fa-heartbeat"></i>
                <h1>MediConnect</h1>
            </a>
            <nav>
                <a href="<?php echo $baseUrl; ?>index.php" <?php echo isActive('index.php', $activePage); ?>>Home</a>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if($_SESSION['user_type'] === 'user'): ?>
                        <!-- Patient Navigation -->
                        <a href="<?php echo $baseUrl; ?>dashboard.php" <?php echo isActive('dashboard.php', $activePage); ?>>My Dashboard</a>
                        <a href="<?php echo $baseUrl; ?>pages/doctors.php" <?php echo isActive('doctors.php', $activePage); ?>>Find Doctors</a>
                        <a href="<?php echo $baseUrl; ?>pages/book_appointment.php" <?php echo isActive('book_appointment.php', $activePage); ?>>Book Appointment</a>
                        <a href="<?php echo $baseUrl; ?>medical_records.php" <?php echo isActive('medical_records.php', $activePage); ?>>Medical Records</a>
                        <a href="<?php echo $baseUrl; ?>pages/profile.php" <?php echo isActive('profile.php', $activePage); ?>>Profile</a>
                        <a href="<?php echo $baseUrl; ?>pages/feedback.php" <?php echo isActive('feedback.php', $activePage); ?>>Feedback</a>
                    <?php elseif($_SESSION['user_type'] === 'doctor'): ?>
                        <!-- Doctor Navigation -->
                        <a href="<?php echo $baseUrl; ?>doctor_dashboard.php" <?php echo isActive('doctor_dashboard.php', $activePage); ?>>Dashboard</a>
                        <a href="<?php echo $baseUrl; ?>pages/appointments.php" <?php echo isActive('appointments.php', $activePage); ?>>My Appointments</a>
                        <a href="<?php echo $baseUrl; ?>doctor_upload.php" <?php echo isActive('doctor_upload.php', $activePage); ?>>Upload Records</a>
                        <a href="<?php echo $baseUrl; ?>pages/patient_records.php" <?php echo isActive('patient_records.php', $activePage); ?>>Patient Records</a>
                        <a href="<?php echo $baseUrl; ?>pages/profile.php" <?php echo isActive('profile.php', $activePage); ?>>Profile</a>
                        <a href="<?php echo $baseUrl; ?>pages/feedback.php" <?php echo isActive('feedback.php', $activePage); ?>>Feedback</a>
                    <?php endif; ?>
                    <a href="<?php echo $baseUrl; ?>logout.php" class="btn btn-secondary">Logout</a>
                <?php else: ?>
                    <!-- Guest Navigation -->
                    <a href="<?php echo $baseUrl; ?>pages/doctors.php" <?php echo isActive('doctors.php', $activePage); ?>>Find Doctors</a>
                    <?php if($activePage == 'index.php'): ?>
                        <a href="#" class="btn btn-primary login-trigger">Login</a>
                    <?php else: ?>
                        <a href="<?php echo $baseUrl; ?>login.php" class="btn btn-primary">Login</a>
                    <?php endif; ?>
                    <a href="<?php echo $baseUrl; ?>register.php" class="btn btn-secondary">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</header>