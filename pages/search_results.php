<?php
session_start();
require_once '../config/database.php';

// Get search parameters
$search = isset($_GET['search']) ? filter_var($_GET['search'], FILTER_SANITIZE_STRING) : '';
$specialty = isset($_GET['specialty']) ? filter_var($_GET['specialty'], FILTER_SANITIZE_STRING) : '';

// Prepare query
$query = "SELECT * FROM doctors WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (full_name LIKE ? OR specialization LIKE ? OR address LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($specialty)) {
    $query .= " AND specialization LIKE ?";
    $params[] = "%$specialty%";
}

$query .= " ORDER BY full_name ASC";

// Execute query
$stmt = $conn->prepare($query);
$stmt->execute($params);
$doctors = $stmt->fetchAll();

// Set subdirectory flag for header/footer
$isSubdirectory = true;
$activePage = 'search_results.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Search Results - MediConnect</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/styles/styles.css">
    <link rel="stylesheet" href="../assets/styles/practo-enhanced.css">
    <link rel="stylesheet" href="../assets/styles/profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/styles/minimalist-theme.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container dashboard-container">
        <section class="profile-header-section fade-in">
            <div class="profile-header">
                <div class="profile-info">
                    <h1>Search Results</h1>
                    <p class="lead">
                        <?php if (!empty($search) && !empty($specialty)): ?>
                            Results for "<?php echo htmlspecialchars($search); ?>" in <?php echo htmlspecialchars($specialty); ?>
                        <?php elseif (!empty($search)): ?>
                            Results for "<?php echo htmlspecialchars($search); ?>"
                        <?php elseif (!empty($specialty)): ?>
                            Results for <?php echo htmlspecialchars($specialty); ?> specialists
                        <?php else: ?>
                            All doctors
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </section>

        <section class="doctor-cards fade-in">
            <?php if (empty($doctors)): ?>
                <div class="no-results">
                    <i class="fas fa-user-md"></i>
                    <h3>No doctors found</h3>
                    <p>Try changing your search criteria</p>
                    <a href="doctors.php" class="btn btn-primary">Back to Search</a>
                </div>
            <?php else: ?>
                <?php foreach($doctors as $doctor): ?>
                    <div class="enhanced-card pulse-hover">
                        <i class="fas fa-user-md doctor-icon"></i>
                        <div class="doctor-info">
                            <h3>Dr. <?php echo htmlspecialchars($doctor['full_name']); ?></h3>
                            <p><strong>Specialty:</strong> <?php echo htmlspecialchars($doctor['specialization']); ?></p>
                            <p><strong>Experience:</strong> <?php echo htmlspecialchars($doctor['experience']); ?> years</p>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($doctor['address']); ?></p>
                            <a href="book_appointment.php?doctor_id=<?php echo $doctor['id']; ?>" class="btn btn-primary">
                                Book Appointment
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
