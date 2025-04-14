<?php
/**
 * Profile Page
 * Allows users to view and edit their profile information
 */

// Include configuration
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    
    // Additional fields for doctors
    if ($_SESSION['user_type'] == 'doctor') {
        $specialization = sanitizeInput($_POST['specialization']);
        $experience = sanitizeInput($_POST['experience']);
        $consultation_fee = sanitizeInput($_POST['consultation_fee']);
        $consultation_duration = sanitizeInput($_POST['consultation_duration']);
        $clinic_name = sanitizeInput($_POST['clinic_name']);
        $clinic_address = sanitizeInput($_POST['clinic_address']);
        $clinic_phone = sanitizeInput($_POST['clinic_phone']);
        $clinic_email = sanitizeInput($_POST['clinic_email']);
        $working_hours = sanitizeInput($_POST['working_hours']);
        $languages_spoken = sanitizeInput($_POST['languages_spoken']);
        $education = sanitizeInput($_POST['education']);
        $certifications = sanitizeInput($_POST['certifications']);
        $awards = sanitizeInput($_POST['awards']);
        $bio = sanitizeInput($_POST['bio']);
    }
    
    try {
        if ($_SESSION['user_type'] == 'doctor') {
            $stmt = $conn->prepare("
                UPDATE doctors 
                SET full_name = ?, email = ?, phone = ?, address = ?, 
                    specialization = ?, experience = ?, consultation_fee = ?,
                    consultation_duration = ?, clinic_name = ?, clinic_address = ?,
                    clinic_phone = ?, clinic_email = ?, working_hours = ?,
                    languages_spoken = ?, education = ?, certifications = ?,
                    awards = ?, bio = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $full_name, $email, $phone, $address, 
                $specialization, $experience, $consultation_fee,
                $consultation_duration, $clinic_name, $clinic_address,
                $clinic_phone, $clinic_email, $working_hours,
                $languages_spoken, $education, $certifications,
                $awards, $bio, $_SESSION['user_id']
            ]);
        } else {
            $stmt = $conn->prepare("
                UPDATE users 
                SET full_name = ?, email = ?, phone = ?, address = ?
                WHERE id = ?
            ");
            $stmt->execute([$full_name, $email, $phone, $address, $_SESSION['user_id']]);
        }
        
        $_SESSION['full_name'] = $full_name;
        setFlashMessage('success', 'Profile updated successfully');
        header("Location: profile.php");
        exit();
    } catch (PDOException $e) {
        setFlashMessage('error', 'Error updating profile: ' . $e->getMessage());
    }
}

// Fetch user data
if ($_SESSION['user_type'] == 'doctor') {
    $stmt = $conn->prepare("SELECT * FROM doctors WHERE id = ?");
} else {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
}
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Ensure all fields have default values if null
$user['full_name'] = $user['full_name'] ?? '';
$user['email'] = $user['email'] ?? '';
$user['phone'] = $user['phone'] ?? '';
$user['address'] = $user['address'] ?? '';
$user['specialization'] = $user['specialization'] ?? '';
$user['experience'] = $user['experience'] ?? '';
$user['consultation_fee'] = $user['consultation_fee'] ?? '';
$user['consultation_duration'] = $user['consultation_duration'] ?? 30;
$user['clinic_name'] = $user['clinic_name'] ?? '';
$user['clinic_address'] = $user['clinic_address'] ?? '';
$user['clinic_phone'] = $user['clinic_phone'] ?? '';
$user['clinic_email'] = $user['clinic_email'] ?? '';
$user['working_hours'] = $user['working_hours'] ?? '';
$user['languages_spoken'] = $user['languages_spoken'] ?? '';
$user['education'] = $user['education'] ?? '';
$user['certifications'] = $user['certifications'] ?? '';
$user['awards'] = $user['awards'] ?? '';
$user['bio'] = $user['bio'] ?? '';

// Set active page for navigation
$activePage = 'profile.php';
$isSubdirectory = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Profile - <?php echo APP_NAME; ?></title>
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

    <div class="container">
        <div class="profile-container fade-in">
            <div class="profile-header">
                <div class="profile-image">
                    <img src="<?php echo $_SESSION['user_type'] == 'doctor' ? '../assets/images/default-doctor.png' : '../assets/images/default-user.png'; ?>" 
                         alt="Profile Picture" class="profile-picture">
                    <button class="change-photo-btn">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
                    <p class="user-type">
                        <i class="fas fa-user-md"></i> 
                        <?php echo $_SESSION['user_type'] == 'doctor' ? 'Doctor' : 'Patient'; ?>
                    </p>
                    <?php if ($_SESSION['user_type'] == 'doctor'): ?>
                        <p class="specialization">
                            <i class="fas fa-stethoscope"></i> 
                            <?php echo htmlspecialchars($user['specialization']); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <?php echo showFlashMessage(); ?>

            <div class="profile-content">
                <form action="profile.php" method="POST" class="profile-form">
                    <div class="form-section">
                        <h3><i class="fas fa-user"></i> Personal Information</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="full_name">Full Name</label>
                                <input type="text" id="full_name" name="full_name" 
                                       value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="tel" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea id="address" name="address" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <?php if ($_SESSION['user_type'] == 'doctor'): ?>
                        <div class="form-section">
                            <h3><i class="fas fa-graduation-cap"></i> Professional Information</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="specialization">Specialization</label>
                                    <input type="text" id="specialization" name="specialization" 
                                           value="<?php echo htmlspecialchars($user['specialization']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="experience">Years of Experience</label>
                                    <input type="number" id="experience" name="experience" 
                                           value="<?php echo htmlspecialchars($user['experience']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="consultation_fee">Consultation Fee ($)</label>
                                    <input type="number" id="consultation_fee" name="consultation_fee" 
                                           value="<?php echo htmlspecialchars($user['consultation_fee']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="consultation_duration">Consultation Duration (minutes)</label>
                                    <input type="number" id="consultation_duration" name="consultation_duration" 
                                           value="<?php echo htmlspecialchars($user['consultation_duration']); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3><i class="fas fa-hospital"></i> Clinic Information</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="clinic_name">Clinic Name</label>
                                    <input type="text" id="clinic_name" name="clinic_name" 
                                           value="<?php echo htmlspecialchars($user['clinic_name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="clinic_address">Clinic Address</label>
                                    <textarea id="clinic_address" name="clinic_address" required><?php echo htmlspecialchars($user['clinic_address']); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="clinic_phone">Clinic Phone</label>
                                    <input type="tel" id="clinic_phone" name="clinic_phone" 
                                           value="<?php echo htmlspecialchars($user['clinic_phone']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="clinic_email">Clinic Email</label>
                                    <input type="email" id="clinic_email" name="clinic_email" 
                                           value="<?php echo htmlspecialchars($user['clinic_email']); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3><i class="fas fa-clock"></i> Working Hours</h3>
                            <div class="form-group">
                                <label for="working_hours">Working Hours (e.g., Mon-Fri: 9AM-5PM, Sat: 9AM-1PM)</label>
                                <textarea id="working_hours" name="working_hours" required><?php echo htmlspecialchars($user['working_hours']); ?></textarea>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3><i class="fas fa-graduation-cap"></i> Education & Qualifications</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="education">Education (One per line)</label>
                                    <textarea id="education" name="education" rows="3"><?php echo htmlspecialchars($user['education']); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="certifications">Certifications (One per line)</label>
                                    <textarea id="certifications" name="certifications" rows="3"><?php echo htmlspecialchars($user['certifications']); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="awards">Awards & Achievements (One per line)</label>
                                    <textarea id="awards" name="awards" rows="3"><?php echo htmlspecialchars($user['awards']); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3><i class="fas fa-language"></i> Languages & Bio</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="languages_spoken">Languages Spoken (comma separated)</label>
                                    <input type="text" id="languages_spoken" name="languages_spoken" 
                                           value="<?php echo htmlspecialchars($user['languages_spoken']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="bio">Professional Bio</label>
                                    <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($user['bio']); ?></textarea>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="change_password.php" class="btn btn-secondary">
                            <i class="fas fa-key"></i> Change Password
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
