<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$doctor_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch doctor details
$stmt = $conn->prepare("SELECT * FROM doctors WHERE id = ?");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();

if (!$doctor) {
    header('Location: index.php');
    exit();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $clinic_address = $_POST['clinic_address'];
    $qualifications = $_POST['qualifications'];
    $bio = $_POST['bio'];
    $consultation_fee = $_POST['consultation_fee'];
    $working_hours = $_POST['working_hours'];
    $languages = $_POST['languages'];
    
    // Handle profile image upload
    $profile_image = $doctor['profile_image'];
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $target_dir = "uploads/doctors/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            $profile_image = $target_file;
        }
    }
    
    // Update doctor profile
    $stmt = $conn->prepare("UPDATE doctors SET 
        clinic_address = ?, 
        qualifications = ?, 
        bio = ?, 
        consultation_fee = ?, 
        working_hours = ?, 
        languages_spoken = ?, 
        profile_image = ? 
        WHERE id = ?");
    
    $stmt->bind_param("sssdsssi", 
        $clinic_address, 
        $qualifications, 
        $bio, 
        $consultation_fee, 
        $working_hours, 
        $languages, 
        $profile_image, 
        $doctor_id
    );
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Profile updated successfully!";
        header("Location: doctor_profile.php?id=" . $doctor_id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Profile - <?php echo htmlspecialchars($doctor['full_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-header {
            background: linear-gradient(135deg, #6B73FF 0%, #000DFF 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .profile-image {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
        }
        .doctor-card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .doctor-card:hover {
            transform: translateY(-5px);
        }
        .qualification-badge {
            background-color: #e9ecef;
            color: #495057;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            display: inline-block;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-3 text-center">
                    <img src="<?php echo $doctor['profile_image'] ?: 'assets/images/default-doctor.jpg'; ?>" 
                         alt="Doctor Profile" class="profile-image mb-3">
                </div>
                <div class="col-md-9">
                    <h1 class="display-4">Dr. <?php echo htmlspecialchars($doctor['full_name']); ?></h1>
                    <p class="lead"><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                    <p><i class="fas fa-star text-warning"></i> Experience: <?php echo $doctor['experience']; ?> years</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <div class="card doctor-card mb-4">
                    <div class="card-body">
                        <h3 class="card-title">About</h3>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($doctor['bio'])); ?></p>
                        
                        <h4 class="mt-4">Qualifications</h4>
                        <div class="qualifications">
                            <?php 
                            $qualifications = explode("\n", $doctor['qualifications']);
                            foreach ($qualifications as $qualification) {
                                echo '<span class="qualification-badge">' . htmlspecialchars(trim($qualification)) . '</span>';
                            }
                            ?>
                        </div>

                        <h4 class="mt-4">Working Hours</h4>
                        <p><?php echo nl2br(htmlspecialchars($doctor['working_hours'])); ?></p>

                        <h4 class="mt-4">Languages Spoken</h4>
                        <p><?php echo htmlspecialchars($doctor['languages_spoken']); ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card doctor-card mb-4">
                    <div class="card-body">
                        <h4>Clinic Location</h4>
                        <div class="mt-3">
                            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($doctor['clinic_address']); ?></p>
                            <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($doctor['clinic_address']); ?>" 
                               class="btn btn-primary" target="_blank">
                                <i class="fas fa-search"></i> Search on Google Maps
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card doctor-card">
                    <div class="card-body">
                        <h4>Consultation Fee</h4>
                        <p class="h3">$<?php echo number_format($doctor['consultation_fee'], 2); ?></p>
                        <a href="book-appointment.php?doctor_id=<?php echo $doctor_id; ?>" class="btn btn-success">
                            Book Appointment
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Update Modal -->
    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $doctor_id): ?>
    <div class="modal fade" id="updateProfileModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="profile_image" class="form-label">Profile Image</label>
                            <input type="file" class="form-control" id="profile_image" name="profile_image">
                        </div>
                        <div class="mb-3">
                            <label for="clinic_address" class="form-label">Clinic Address</label>
                            <textarea class="form-control" id="clinic_address" name="clinic_address" rows="3"><?php echo htmlspecialchars($doctor['clinic_address']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="qualifications" class="form-label">Qualifications (One per line)</label>
                            <textarea class="form-control" id="qualifications" name="qualifications" rows="3"><?php echo htmlspecialchars($doctor['qualifications']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="3"><?php echo htmlspecialchars($doctor['bio']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="consultation_fee" class="form-label">Consultation Fee</label>
                            <input type="number" class="form-control" id="consultation_fee" name="consultation_fee" value="<?php echo $doctor['consultation_fee']; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="working_hours" class="form-label">Working Hours</label>
                            <textarea class="form-control" id="working_hours" name="working_hours" rows="3"><?php echo htmlspecialchars($doctor['working_hours']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="languages" class="form-label">Languages Spoken (comma separated)</label>
                            <input type="text" class="form-control" id="languages" name="languages" value="<?php echo htmlspecialchars($doctor['languages_spoken']); ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show update profile modal for doctors
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $doctor_id): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = new bootstrap.Modal(document.getElementById('updateProfileModal'));
            modal.show();
        });
        <?php endif; ?>
    </script>
</body>
</html> 