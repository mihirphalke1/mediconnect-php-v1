<?php
/**
 * Doctor Prescription Upload Page
 * Allows doctors to upload prescriptions for patients
 */

// Include configuration
require_once 'includes/config.php';

// Check if user is logged in as a doctor
requireRole('doctor');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $patientId = sanitizeInput($_POST['patient_id']);
    $prescriptionTitle = sanitizeInput($_POST['prescription_title']);
    
    // Validate form
    $errors = [];
    
    if (empty($patientId)) {
        $errors[] = "Patient is required";
    }
    
    if (empty($prescriptionTitle)) {
        $errors[] = "Prescription title is required";
    }
    
    // Check if file was uploaded properly
    if (!isset($_FILES['prescription_file']) || $_FILES['prescription_file']['error'] != 0) {
        $errors[] = "File upload error: " . ($_FILES['prescription_file']['error'] ?? 'Unknown error');
    } else {
        // Validate file
        $file = $_FILES['prescription_file'];
        $fileName = sanitizeInput($file['name']);
        $fileType = $file['type'];
        $fileSize = $file['size'];
        $fileTmp = $file['tmp_name'];
        
        // Check file size (limit to 10MB)
        if ($fileSize > 10000000) {
            $errors[] = "File size must be less than 10MB";
        }
        
        // Check file type - only allow PDF
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        if ($fileExtension !== 'pdf') {
            $errors[] = "Only PDF files are allowed for prescriptions";
        }
    }
    
    // If no errors, proceed with upload
    if (empty($errors)) {
        try {
            // Start transaction
            $conn->beginTransaction();
            
            // Create unique filename
            $newFileName = time() . '_' . $fileName;
            $uploadPath = PRESCRIPTIONS_DIR . $newFileName;
            
            // Move uploaded file
            if (move_uploaded_file($fileTmp, $uploadPath)) {
                // Insert prescription into database
                $stmt = $conn->prepare("
                    INSERT INTO prescriptions (user_id, doctor_id, title, file_path, file_type, file_size) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $patientId,
                    $_SESSION['user_id'],
                    $prescriptionTitle,
                    $uploadPath,
                    $fileType,
                    $fileSize
                ]);
                
                // Create notification for patient
                $notificationStmt = $conn->prepare("
                    INSERT INTO notifications (user_id, title, message, type, reference_id) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                
                // Get doctor name
                $doctorStmt = $conn->prepare("SELECT full_name FROM doctors WHERE id = ?");
                $doctorStmt->execute([$_SESSION['user_id']]);
                $doctor = $doctorStmt->fetch(PDO::FETCH_ASSOC);
                $doctorName = $doctor ? $doctor['full_name'] : 'Your doctor';
                
                $notificationTitle = "New Prescription";
                $notificationMessage = "Dr. $doctorName has uploaded a new prescription: $prescriptionTitle";
                
                $notificationStmt->execute([
                    $patientId,
                    $notificationTitle,
                    $notificationMessage,
                    'prescription',
                    $conn->lastInsertId()
                ]);
                
                // Commit transaction
                $conn->commit();
                
                // Set success message
                setFlashMessage('success', "Prescription uploaded successfully");
                header("Location: doctor_dashboard.php");
                exit();
            } else {
                // Rollback transaction
                $conn->rollBack();
                $errors[] = "Failed to upload file";
            }
        } catch (PDOException $e) {
            // Rollback transaction
            $conn->rollBack();
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

// Get patients for dropdown
$stmt = $conn->prepare("
    SELECT u.id, u.full_name, COUNT(a.id) as appointment_count 
    FROM users u 
    LEFT JOIN appointments a ON u.id = a.user_id AND a.doctor_id = ? AND a.status IN ('completed', 'confirmed', 'pending') 
    GROUP BY u.id 
    HAVING appointment_count > 0
    ORDER BY u.full_name
");
$stmt->execute([$_SESSION['user_id']]);
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If no patients have appointments, show all users
if (empty($patients)) {
    $stmt = $conn->prepare("
        SELECT id, full_name
        FROM users
        ORDER BY full_name
    ");
    $stmt->execute();
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Set active page for navigation
$activePage = 'doctor_prescription.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Upload Prescription - <?php echo APP_NAME; ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/styles/styles.css">
    <link rel="stylesheet" href="assets/styles/practo-enhanced.css">
    <link rel="stylesheet" href="assets/styles/minimalist-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .upload-container {
            max-width: 700px;
            margin: 0 auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .form-header {
            margin-bottom: 20px;
            text-align: center;
        }
        
        .form-header h2 {
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
        
        .file-input-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .file-input-container:hover {
            border-color: var(--primary-color);
        }
        
        .file-input-container i {
            font-size: 48px;
            color: #718096;
            margin-bottom: 15px;
        }
        
        .file-input-container p {
            margin: 0 0 10px 0;
            color: #4a5568;
        }
        
        .file-input-container .hint {
            font-size: 14px;
            color: #718096;
        }
        
        .file-input {
            display: none;
        }
        
        #file-name {
            margin-top: 10px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .btn-container {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }
        
        .btn-upload {
            padding: 12px 30px;
        }
        
        .error-list {
            background-color: #fed7d7;
            border-left: 4px solid #e53e3e;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .error-list ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .error-list li {
            color: #c53030;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="upload-container fade-in">
            <div class="form-header">
                <h2><i class="fas fa-prescription"></i> Upload Prescription</h2>
                <p>Create and upload a prescription for your patient</p>
            </div>
            
            <?php echo showFlashMessage(); ?>
            
            <?php if (!empty($errors)): ?>
                <div class="error-list">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form action="doctor_prescription.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="patient">Select Patient:</label>
                    <select id="patient" name="patient_id" class="form-control" required>
                        <option value="">-- Select Patient --</option>
                        <?php foreach ($patients as $patient): ?>
                            <option value="<?php echo $patient['id']; ?>" <?php echo (isset($_POST['patient_id']) && $_POST['patient_id'] == $patient['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($patient['full_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($patients)): ?>
                        <p class="hint text-danger">There are no patients in the system yet. Ask patients to register first.</p>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="prescription_title">Prescription Title:</label>
                    <input type="text" id="prescription_title" name="prescription_title" class="form-control" 
                           placeholder="E.g., Antibiotics, Pain Management, Monthly Medication" 
                           value="<?php echo isset($_POST['prescription_title']) ? htmlspecialchars($_POST['prescription_title']) : ''; ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label>Upload Prescription PDF:</label>
                    <label for="prescription_file" class="file-input-container">
                        <i class="fas fa-file-prescription"></i>
                        <p>Click or drag PDF file to upload</p>
                        <span class="hint">Only PDF format is supported (Max 10MB)</span>
                        <input type="file" id="prescription_file" name="prescription_file" class="file-input" accept=".pdf" required>
                        <div id="file-name"></div>
                    </label>
                </div>
                
                <div class="btn-container">
                    <button type="submit" class="btn btn-primary btn-upload">
                        <i class="fas fa-upload"></i> Upload Prescription
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Display selected filename
        document.getElementById('prescription_file').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : '';
            document.getElementById('file-name').textContent = fileName;
        });
    </script>
</body>
</html> 