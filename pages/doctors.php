<?php
session_start();
require_once '../config/database.php';

// Get filter parameters
$specialty = isset($_GET['specialty']) ? filter_var($_GET['specialty'], FILTER_SANITIZE_STRING) : '';
$search = isset($_GET['search']) ? filter_var($_GET['search'], FILTER_SANITIZE_STRING) : '';

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
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Find Doctors - MediConnect</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/styles/styles.css">
    <link rel="stylesheet" href="../assets/styles/practo-enhanced.css">
    <link rel="stylesheet" href="../assets/styles/profile.css">
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
    <header>
      <div class="container">
        <div class="header-content">
          <a href="../index.php" class="logo">
            <i class="fas fa-heartbeat"></i>
            <h1>MediConnect</h1>
          </a>
          <nav>
            <a href="../index.php">Home</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <?php if($_SESSION['user_type'] == 'user'): ?>
                    <a href="../dashboard.php">My Dashboard</a>
                    <a href="doctors.php" class="active">Find Doctors</a>
                    <a href="book_appointment.php">Book Appointment</a>
                    <a href="../medical_records.php">Medical Records</a>
                <?php else: ?>
                    <a href="../doctor_dashboard.php">Doctor Dashboard</a>
                    <a href="appointments.php">My Appointments</a>
                <?php endif; ?>
                <a href="profile.php">Profile</a>
                <a href="feedback.php">Feedback</a>
                <a href="../logout.php" class="btn btn-secondary">Logout</a>
            <?php else: ?>
                <a href="doctors.php" class="active">Find Doctors</a>
                <a href="#" class="btn btn-primary login-trigger">Login</a>
                <a href="../register.php" class="btn btn-secondary">Register</a>
            <?php endif; ?>
          </nav>
        </div>
      </div>
    </header>

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
            <select id="specialty" name="specialty">
              <option value="">All Specialties</option>
              <?php foreach($specializations as $spec): ?>
                <option value="<?php echo htmlspecialchars($spec); ?>" <?php echo ($specialty == $spec) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($spec); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="form-group">
            <label for="search"><i class="fas fa-search"></i> Search</label>
            <input type="text" id="search" name="search" placeholder="Search by name or location" value="<?php echo htmlspecialchars($search); ?>">
          </div>
          
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-filter"></i> Filter Results
          </button>
          
          <?php if(!empty($specialty) || !empty($search)): ?>
            <a href="doctors.php" class="btn btn-secondary">
              <i class="fas fa-undo"></i> Clear Filters
            </a>
          <?php endif; ?>
        </form>
      </section>

      <section class="doctor-list-section fade-in">
        <?php if(empty($doctors)): ?>
          <div class="no-results">
            <i class="fas fa-user-md" style="font-size: 48px; color: #cbd5e0; display: block; margin-bottom: 20px;"></i>
            <h3>No doctors found</h3>
            <p>Try changing your search criteria or check back later.</p>
          </div>
        <?php else: ?>
          <div class="doctor-list">
            <?php foreach($doctors as $doctor): ?>
              <div class="doctor-card">
                <div class="doctor-header">
                  <h3>Dr. <?php echo htmlspecialchars($doctor['full_name']); ?></h3>
                  <div class="doctor-specialty"><?php echo htmlspecialchars($doctor['specialization']); ?></div>
                </div>
                <div class="doctor-info">
                  <?php if($doctor['experience']): ?>
                    <p><i class="fas fa-user-clock"></i> <?php echo htmlspecialchars($doctor['experience']); ?> years experience</p>
                  <?php endif; ?>
                  <?php if($doctor['phone']): ?>
                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($doctor['phone']); ?></p>
                  <?php endif; ?>
                  <?php if($doctor['address']): ?>
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($doctor['address']); ?></p>
                  <?php endif; ?>
                </div>
                <div class="doctor-actions">
                  <?php if(isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'user'): ?>
                    <a href="book_appointment.php?doctor_id=<?php echo $doctor['id']; ?>" class="btn btn-primary">
                      <i class="fas fa-calendar-plus"></i> Book Appointment
                    </a>
                  <?php else: ?>
                    <a href="../index.php" class="btn btn-primary login-trigger">
                      <i class="fas fa-sign-in-alt"></i> Login to Book
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </div>

    <footer>
      <div class="container">
        <div class="footer-content">
          <div class="footer-section">
            <h4>Quick Links</h4>
            <a href="../index.php">Home</a>
            <a href="doctors.php">Find Doctors</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <?php if($_SESSION['user_type'] == 'user'): ?>
                    <a href="../dashboard.php">Dashboard</a>
                <?php else: ?>
                    <a href="../doctor_dashboard.php">Dashboard</a>
                <?php endif; ?>
            <?php endif; ?>
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
