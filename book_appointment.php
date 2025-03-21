<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

// Get doctor ID from URL
$doctor_id = isset($_GET['doctor_id']) ? filter_var($_GET['doctor_id'], FILTER_SANITIZE_NUMBER_INT) : 0;

// Get doctor details
if ($doctor_id) {
    try {
        $stmt = $conn->prepare("SELECT * FROM doctors WHERE id = ?");
        $stmt->execute([$doctor_id]);
        $doctor = $stmt->fetch();
        
        if (!$doctor) {
            header("Location: doctors.php");
            exit();
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $appointment_date = filter_var($_POST['appointment_date'], FILTER_SANITIZE_STRING);
    $appointment_time = filter_var($_POST['appointment_time'], FILTER_SANITIZE_STRING);
    $notes = filter_var($_POST['notes'], FILTER_SANITIZE_STRING);
    
    // Validate input
    if (empty($appointment_date) || empty($appointment_time)) {
        $error = "Please select both date and time";
    } else {
        // Check if the selected time slot is available
        try {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'cancelled'");
            $stmt->execute([$doctor_id, $appointment_date, $appointment_time]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                $error = "This time slot is already booked. Please select another time.";
            } else {
                // Insert appointment
                $stmt = $conn->prepare("INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, notes) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $doctor_id, $appointment_date, $appointment_time, $notes]);
                
                $success = "Appointment booked successfully!";
            }
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Book Appointment - MediConnect</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/styles/styles.css">
    <link rel="stylesheet" href="assets/styles/practo-enhanced.css">
    <link rel="stylesheet" href="assets/styles/profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="appointment-container">
            <div class="appointment-box">
                <h2><i class="fas fa-calendar-check"></i> Book Appointment</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($doctor): ?>
                    <div class="doctor-info">
                        <img src="<?php echo $doctor['profile_image'] ?: 'assets/images/default-doctor.png'; ?>" alt="Doctor" class="doctor-image">
                        <h3>Dr. <?php echo htmlspecialchars($doctor['full_name']); ?></h3>
                        <p><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                        <p>Experience: <?php echo $doctor['experience']; ?> years</p>
                    </div>
                    
                    <form method="POST" action="" class="appointment-form">
                        <div class="form-group">
                            <label for="appointment_date">Appointment Date</label>
                            <input type="date" id="appointment_date" name="appointment_date" required
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="appointment_time">Appointment Time</label>
                            <select id="appointment_time" name="appointment_time" required>
                                <option value="">Select time</option>
                                <?php
                                $start_time = strtotime('09:00');
                                $end_time = strtotime('17:00');
                                $interval = 30 * 60; // 30 minutes
                                
                                for ($time = $start_time; $time <= $end_time; $time += $interval) {
                                    echo '<option value="' . date('H:i', $time) . '">' . date('h:i A', $time) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">Additional Notes</label>
                            <textarea id="notes" name="notes" rows="4"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">Book Appointment</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-danger">Doctor not found</div>
                <?php endif; ?>
                
                <div class="back-link">
                    <a href="doctors.php" class="btn btn-secondary">Back to Doctors</a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Client-side validation
    document.querySelector('.appointment-form').addEventListener('submit', function(e) {
        const date = document.getElementById('appointment_date').value;
        const time = document.getElementById('appointment_time').value;
        
        if (!date || !time) {
            e.preventDefault();
            alert('Please select both date and time');
            return;
        }
        
        // Check if selected date is in the past
        const selectedDate = new Date(date);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate < today) {
            e.preventDefault();
            alert('Please select a future date');
            return;
        }
    });
    </script>
</body>
</html> 