<?php
/**
 * Doctors Page
 * Allows users to search for and view doctors
 */

// Include configuration
require_once '../includes/config.php';

// Get filter parameters
$specialty = isset($_GET['specialty']) ? sanitizeInput($_GET['specialty']) : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Build query based on filter parameters
$query = "SELECT * FROM doctors WHERE 1=1";
$params = [];

if (!empty($specialty)) {
    $query .= " AND specialization = ?";
    $params[] = $specialty;
}

if (!empty($search)) {
    $query .= " AND (full_name LIKE ? OR specialization LIKE ? OR address LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$query .= " ORDER BY full_name ASC";

// Fetch doctors from database
$stmt = $conn->prepare($query);
$stmt->execute($params);
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get list of unique specializations for the filter dropdown
$stmt = $conn->prepare("SELECT DISTINCT specialization FROM doctors ORDER BY specialization");
$stmt->execute();
$specializations = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Set variables for header
$activePage = 'doctors.php';
$isSubdirectory = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Find Doctors - <?php echo APP_NAME; ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/styles/styles.css">
    <link rel="stylesheet" href="../assets/styles/practo-enhanced.css">
    <link rel="stylesheet" href="../assets/styles/profile.css">
    <link rel="stylesheet" href="../assets/styles/doctor.css">
    <link rel="stylesheet" href="../assets/styles/minimalist-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .doctor-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .doctor-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .doctor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .doctor-header {
            background: linear-gradient(135deg, #4a6fa5, #5b86e5);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .doctor-header h3 {
            margin: 0;
            color: white;
        }
        
        .doctor-specialty {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
            margin-top: 10px;
        }
        
        .doctor-info {
            padding: 20px;
        }
        
        .doctor-info p {
            margin: 10px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .doctor-info p i {
            color: #5b86e5;
        }
        
        .doctor-actions {
            display: flex;
            border-top: 1px solid #eee;
        }
        
        .doctor-actions a {
            flex: 1;
            text-align: center;
            padding: 15px 0;
            font-weight: 500;
        }
        
        .search-filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .filter-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        
        .filter-form .form-group {
            flex: 1;
            min-width: 200px;
            margin-bottom: 0;
        }
        
        .filter-form .btn {
            margin-bottom: 0;
        }
        
        .no-results {
            text-align: center;
            padding: 40px 0;
            color: #718096;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
      <section class="profile-header-section fade-in">
        <div class="profile-header">
          <div class="profile-info">
            <h1><i class="fas fa-user-md"></i> Find Doctors</h1>
            <p class="lead">
              Search for healthcare specialists and book an appointment.
            </p>
          </div>
        </div>
      </section>

      <section class="search-filters fade-in">
        <form action="" method="GET" class="filter-form">
          <div class="form-group">
            <label for="specialty"><i class="fas fa-stethoscope"></i> Specialty</label>
            <select id="specialty" name="specialty" class="form-control">
              <option value="">All Specialties</option>
              <?php foreach ($specializations as $spec): ?>
                <option value="<?php echo htmlspecialchars($spec); ?>" <?php if($specialty == $spec) echo 'selected'; ?>>
                  <?php echo htmlspecialchars($spec); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="search"><i class="fas fa-search"></i> Search</label>
            <input type="text" id="search" name="search" class="form-control" 
                  placeholder="Search by name, specialty, or location"
                  value="<?php echo htmlspecialchars($search); ?>">
          </div>
          <button type="submit" class="btn btn-primary">Search</button>
          <?php if(!empty($search) || !empty($specialty)): ?>
            <a href="doctors.php" class="btn btn-secondary">Clear</a>
          <?php endif; ?>
        </form>
      </section>

      <?php if (empty($doctors)): ?>
        <div class="no-results fade-in">
          <i class="fas fa-user-md fa-4x"></i>
          <h3>No doctors found</h3>
          <p>Try adjusting your search criteria or clear the filters</p>
        </div>
      <?php else: ?>
        <div class="doctor-list fade-in">
          <?php foreach ($doctors as $doctor): ?>
            <div class="doctor-card">
              <div class="doctor-header">
                <h3>Dr. <?php echo htmlspecialchars($doctor['full_name']); ?></h3>
                <span class="doctor-specialty"><?php echo htmlspecialchars($doctor['specialization']); ?></span>
              </div>
              <div class="doctor-info">
                <p><i class="fas fa-graduation-cap"></i> Experience: <?php echo $doctor['experience']; ?> years</p>
                <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($doctor['address']); ?></p>
                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($doctor['phone']); ?></p>
                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($doctor['email']); ?></p>
              </div>
              <div class="doctor-actions">
                <?php if(isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'user'): ?>
                  <a href="book_appointment.php?doctor_id=<?php echo $doctor['id']; ?>" class="btn-primary">
                    <i class="fas fa-calendar-plus"></i> Book Appointment
                  </a>
                <?php else: ?>
                  <a href="../login.php" class="btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login to Book
                  </a>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
