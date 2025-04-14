<?php
/**
 * Book Appointment Page
 * Allows patients to book appointments with doctors
 */

// Include configuration
require_once '../includes/config.php';

// Check if user is logged in as a patient
requireRole('user');

// Get doctor_id from URL if provided
$selected_doctor_id = isset($_GET['doctor_id']) ? sanitizeInput($_GET['doctor_id']) : null;

// Get doctors for dropdown
$stmt = $conn->prepare("SELECT id, full_name, specialization FROM doctors ORDER BY full_name");
$stmt->execute();
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set active page for navigation
$activePage = 'book_appointment.php';
$isSubdirectory = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Book Appointment - <?php echo APP_NAME; ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/styles/styles.css">
    <link rel="stylesheet" href="../assets/styles/practo-enhanced.css">
    <link rel="stylesheet" href="../assets/styles/minimalist-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .appointment-form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .form-header {
            margin-bottom: 30px;
            text-align: center;
        }
        
        .form-header h2 {
            font-size: 24px;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1 0 45%;
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
            }
            
            .form-row .form-group {
                flex: 1 0 100%;
            }
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }
        
        .btn-container {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }
        
        .btn-large {
            padding: 12px 30px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="appointment-form-container fade-in">
            <div class="form-header">
                <h2><i class="fas fa-calendar-check"></i> Book an Appointment</h2>
                <p>Fill in the details below to schedule your appointment</p>
            </div>
            
            <?php echo showFlashMessage(); ?>
            
            <form action="../submit_appointment.php" method="POST">
                <div class="form-group">
                    <label for="doctor">Select Doctor:</label>
                    <select id="doctor" name="doctor_id" class="form-control" required>
                        <option value="">-- Select a doctor --</option>
                        <?php foreach ($doctors as $doctor): ?>
                            <option value="<?php echo $doctor['id']; ?>" <?php echo ($selected_doctor_id == $doctor['id']) ? 'selected' : ''; ?>>
                                Dr. <?php echo htmlspecialchars($doctor['full_name']); ?> - <?php echo htmlspecialchars($doctor['specialization']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="appointment_date">Appointment Date:</label>
                        <input type="date" id="appointment_date" name="appointment_date" class="form-control" 
                               min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="appointment_time">Preferred Time:</label>
                        <select id="appointment_time" name="appointment_time" class="form-control" required>
                            <option value="">-- Select a time --</option>
                            <option value="09:00:00">9:00 AM</option>
                            <option value="10:00:00">10:00 AM</option>
                            <option value="11:00:00">11:00 AM</option>
                            <option value="13:00:00">1:00 PM</option>
                            <option value="14:00:00">2:00 PM</option>
                            <option value="15:00:00">3:00 PM</option>
                            <option value="16:00:00">4:00 PM</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="reason">Reason for Visit:</label>
                    <textarea id="reason" name="reason" class="form-control" 
                              placeholder="Please describe your symptoms or reason for the appointment" required></textarea>
                </div>
                
                <div class="btn-container">
                    <button type="submit" class="btn btn-primary btn-large">
                        <i class="fas fa-calendar-check"></i> Schedule Appointment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
