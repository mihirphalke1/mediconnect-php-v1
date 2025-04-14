<?php
/**
 * Common footer component for MediConnect
 * Ensures consistent footer across the application
 */

// Determine if we're in the root directory or a subdirectory
$isRoot = !isset($isSubdirectory) || $isSubdirectory === false;
$baseUrl = $isRoot ? '' : '../';
?>
<footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h4>Quick Links</h4>
                <a href="<?php echo $baseUrl; ?>pages/doctors.php">Find Doctors</a>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if($_SESSION['user_type'] === 'user'): ?>
                        <a href="<?php echo $baseUrl; ?>dashboard.php">Dashboard</a>
                        <a href="<?php echo $baseUrl; ?>pages/book_appointment.php">Book Appointment</a>
                    <?php elseif($_SESSION['user_type'] === 'doctor'): ?>
                        <a href="<?php echo $baseUrl; ?>doctor_dashboard.php">Dashboard</a>
                        <a href="<?php echo $baseUrl; ?>pages/appointments.php">My Appointments</a>
                        <a href="<?php echo $baseUrl; ?>pages/patient_records.php">Patient Records</a>
                    <?php endif; ?>
                <?php endif; ?>
                
                <a href="<?php echo $baseUrl; ?>pages/feedback.php">Feedback</a>
            </div>
            <div class="footer-section">
                <h4>Legal</h4>
                <a href="<?php echo $baseUrl; ?>pages/terms.php">Terms of Service</a>
                <a href="<?php echo $baseUrl; ?>pages/privacy.php">Privacy Policy</a>
            </div>
            <div class="footer-section">
                <h4>Contact</h4>
                <p><i class="fas fa-envelope"></i> support@mediconnect.com</p>
                <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> MediConnect. All rights reserved.</p>
        </div>
    </div>
</footer> 