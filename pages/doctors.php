<?php
/**
 * Doctors Page
 * Allows users to search for and view doctors
 */

// Include configuration
require_once '../includes/config.php';
require_once '../includes/functions.php'; // Assuming sanitizeInput is here

// Get filter parameters
$specialty = isset($_GET['specialty']) ? sanitizeInput($_GET['specialty']) : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Build query based on filter parameters
// Selecting all necessary fields for the detailed view
$query = "SELECT * FROM doctors WHERE 1=1";
$params = [];

if (!empty($specialty)) {
    $query .= " AND specialization = ?";
    $params[] = $specialty;
}

if (!empty($search)) {
    // Search in name, specialty, and both address fields
    $query .= " AND (full_name LIKE ? OR specialization LIKE ? OR address LIKE ? OR clinic_address LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param; // Add param for clinic_address
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
    <!-- Keep existing CSS links -->
    <link rel="stylesheet" href="../assets/styles/styles.css">
    <link rel="stylesheet" href="../assets/styles/practo-enhanced.css">
    <link rel="stylesheet" href="../assets/styles/profile.css">
    <link rel="stylesheet" href="../assets/styles/doctor.css">
    <link rel="stylesheet" href="../assets/styles/minimalist-theme.css">
    <!-- Add Bootstrap if not already included globally -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Styles adapted from find_doctor.php */
        .doctor-card {
            border-radius: 15px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            margin-bottom: 30px;
            overflow: hidden;
            background: white;
            border: 1px solid #eaeaea;
        }
        
        /* Enhanced shadow on expand/hover */
        .doctor-card.expanded,
        .doctor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.12);
        }
        
        .doctor-card-header {
            cursor: pointer;
            padding: 22px 25px;
            /* More professional medical gradient */
            background: rgb(102, 170, 195);
            color: white;
            position: relative;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            justify-content: flex-start; /* Explicitly align content to the left */
        }
        
        .expand-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            transition: transform 0.3s ease;
            font-size: 1.1rem;
            width: 30px;
            height: 30px;
            background-color: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .doctor-card.expanded .expand-icon {
            transform: translateY(-50%) rotate(180deg);
            background-color: rgba(255, 255, 255, 0.25);
        }
        
        .doctor-card-body {
            display: none;
            padding: 30px;
            background-color: #fcfcfc;
            border-top: 1px solid #eaeaea;
        }
        
        .doctor-card.expanded .doctor-card-body {
            display: block;
        }
        
        .doctor-image {
            width: 85px;
            height: 85px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid rgba(255, 255, 255, 0.7);
            margin-right: 20px;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }
        
        .header-info {
            padding-right: 35px; /* Space for expand icon */
            flex: 1; /* Take up available space, pushing expand icon to the right */
            min-width: 0; /* Prevent overflow issues */
        }
        
        .header-info h3 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
            color: white;
            letter-spacing: 0.2px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .header-info p {
            margin: 5px 0 0;
            font-size: 0.95rem;
            opacity: 0.95;
            color: white;
            font-weight: 300;
        }

        .qualification-badge {
            background-color: #f0f2f5;
            color: #3a4a5d;
            padding: 5px 12px;
            border-radius: 20px;
            margin-right: 8px;
            margin-bottom: 8px;
            display: inline-block;
            font-size: 0.85rem;
            border: 1px solid #e8ecf2;
            font-weight: 500;
        }
        
        .section-title {
            color: #2c5282;
            margin-top: 1.8rem;
            margin-bottom: 1.2rem;
            font-size: 1.15rem;
            font-weight: 600;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 8px;
            position: relative;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 2px;
            background-color: #4776E6;
        }
        
        .section-title:first-child {
            margin-top: 0;
        }
        
        .info-item {
            margin-bottom: 12px;
            display: flex;
            align-items: flex-start;
            font-size: 0.95rem;
            color: #4a5568;
            line-height: 1.5;
        }
        
        .info-item i {
            width: 28px;
            color: #4776E6;
            margin-right: 10px;
            margin-top: 2px;
            text-align: center;
            font-size: 1rem;
        }
        
        .info-item strong {
           color: #2d3748;
           font-weight: 600;
        }
        
        .info-item div {
            flex: 1;
        }

        /* Search filters styling */
        .search-filters {
            background: white;
            padding: 25px 30px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 35px;
            border: 1px solid #e8ecf2;
        }
        
        /* Filter form specific styles */
        .filter-form {
            display: flex;
            gap: 18px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        
        .filter-form .form-group {
            flex: 1;
            min-width: 200px;
            margin-bottom: 0;
        }
        
        .filter-form .form-label {
            font-weight: 500;
            color: #2d3748;
            margin-bottom: 6px;
        }
        
        .filter-form .form-control {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 10px 15px;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        
        .filter-form .form-control:focus {
            border-color: #4776E6;
            box-shadow: 0 0 0 3px rgba(71, 118, 230, 0.15);
        }
        
        /* No results styling */
        .no-results {
            text-align: center;
            padding: 50px 0;
            color: #718096;
            background-color: #f8f9fa;
            border-radius: 15px;
            border: 1px solid #e2e8f0;
        }
        
        .no-results i {
            font-size: 3.5rem;
            margin-bottom: 20px;
            display: block;
            color: #4776E6;
            opacity: 0.7;
        }
        
        .no-results h3 {
            color: #2c5282;
            margin-bottom: 12px;
            font-weight: 600;
        }
        
        /* Container styling */
        .container {
            padding-top: 2rem;
            padding-bottom: 3rem;
        }
        
        h1 {
            color: #2d3748;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        /* General button styling for consistency */
        .btn {
            border-radius: 6px;
            padding: 8px 18px;
            font-weight: 500;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .btn i {
            margin-right: 6px;
        }
        
        /* Primary button styling */
        .btn-primary {
            background-color: #4776E6;
            border-color: #4776E6;
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background-color: #3a67d8;
            border-color: #3a67d8;
        }
        
        .btn-primary:active {
            background-color: #2d59ca !important;
            border-color: #2d59ca !important;
        }
        
        /* Outline button styling */
        .btn-outline-primary {
            color: #4776E6;
            border-color: #4776E6;
            background-color: transparent;
        }
        
        .btn-outline-primary:hover, .btn-outline-primary:focus {
            background-color: #4776E6;
            color: white;
            border-color: #4776E6;
        }
        
        .btn-outline-secondary {
            color: #4a5568;
            border-color: #cbd5e0;
            background-color: transparent;
        }
        
        .btn-outline-secondary:hover, .btn-outline-secondary:focus {
            background-color: #f8fafc;
            color: #2d3748;
            border-color: #a0aec0;
        }
        
        /* Button wrapper in the filter form */
        .filter-form .btn-wrapper {
            display: flex;
            gap: 10px;
            margin-bottom: 0;
            align-self: flex-end;
        }
        
        /* Search filters form buttons */
        .filter-form .btn {
            margin-bottom: 0;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Doctor card action buttons */
        .doctor-card-body .btn {
            margin-top: 15px;
            margin-right: 10px;
            margin-bottom: 5px;
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
            <label for="specialty" class="form-label"><i class="fas fa-stethoscope"></i> Specialty</label>
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
            <label for="search" class="form-label"><i class="fas fa-search"></i> Search</label>
            <input type="text" id="search" name="search" class="form-control"
                  placeholder="Name, specialty, location..."
                  value="<?php echo htmlspecialchars($search); ?>">
          </div>
          <div class="btn-wrapper">
            <button type="submit" class="btn btn-primary" id="search-doctor-btn"><i class="fas fa-search"></i> Search</button>
            <?php if(!empty($search) || !empty($specialty)): ?>
              <a href="doctors.php" class="btn btn-outline-secondary">Clear</a>
            <?php endif; ?>
          </div>
        </form>
      </section>

      <?php if (empty($doctors)): ?>
        <div class="no-results fade-in">
          <i class="fas fa-user-md"></i>
          <h3>No doctors found</h3>
          <p>Try adjusting your search criteria or clear the filters.</p>
        </div>
      <?php else: ?>
        <!-- Use Bootstrap row for layout -->
        <div class="row fade-in">
          <?php foreach ($doctors as $doctor): ?>
            <div class="col-md-6"> <!-- Each card takes half the width on medium screens and up -->
                <div class="doctor-card">
                    <div class="doctor-card-header">
                        <img src="<?php echo htmlspecialchars($doctor['profile_image'] ?: '../assets/images/default-doctor.png'); ?>"
                             alt="Dr. <?php echo htmlspecialchars($doctor['full_name']); ?>" class="doctor-image">
                        <div class="header-info">
                            <h3>Dr. <?php echo htmlspecialchars($doctor['full_name']); ?></h3>
                            <p><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                        </div>
                        <i class="fas fa-chevron-down expand-icon"></i>
                    </div>
                    
                    <div class="doctor-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="section-title">Professional Info</h4>
                                <div class="info-item">
                                    <i class="fas fa-medal"></i>
                                    <div><strong>Experience:</strong> <?php echo htmlspecialchars($doctor['experience'] ?? 'N/A'); ?> years</div>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-dollar-sign"></i>
                                    <div><strong>Fee:</strong> $<?php echo number_format($doctor['consultation_fee'] ?? 0, 2); ?></div>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-clock"></i>
                                    <div><strong>Duration:</strong> <?php echo htmlspecialchars($doctor['consultation_duration'] ?? 'N/A'); ?> minutes</div>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-language"></i>
                                    <div><strong>Languages:</strong> <?php echo htmlspecialchars($doctor['languages_spoken'] ?? 'N/A'); ?></div>
                                </div>

                                <h4 class="section-title">Education & Qualifications</h4>
                                <?php
                                $qualifications = explode("
", $doctor['qualifications'] ?? '');
                                if (!empty(trim($doctor['qualifications'] ?? ''))):
                                    foreach ($qualifications as $qualification):
                                        if (trim($qualification)): ?>
                                            <span class="qualification-badge">
                                                <?php echo htmlspecialchars(trim($qualification)); ?>
                                            </span>
                                        <?php endif;
                                    endforeach;
                                else: ?>
                                    <p>No qualifications listed.</p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <h4 class="section-title">Clinic Information</h4>
                                <div class="info-item">
                                    <i class="fas fa-hospital"></i>
                                    <div><strong>Clinic:</strong> <?php echo htmlspecialchars($doctor['clinic_name'] ?? 'N/A'); ?></div>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <div><strong>Address:</strong> <?php echo htmlspecialchars($doctor['clinic_address'] ?? $doctor['address'] ?? 'N/A'); ?></div>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-phone"></i>
                                    <div><strong>Phone:</strong> <?php echo htmlspecialchars($doctor['clinic_phone'] ?? $doctor['phone'] ?? 'N/A'); ?></div>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-envelope"></i>
                                     <div><strong>Email:</strong> <?php echo htmlspecialchars($doctor['clinic_email'] ?? $doctor['email'] ?? 'N/A'); ?></div>
                                </div>
                                
                                <h4 class="section-title">Working Hours</h4>
                                <div class="info-item">
                                    <i class="fas fa-business-time"></i>
                                    <div><?php echo nl2br(htmlspecialchars($doctor['working_hours'] ?? 'Not specified')); ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-center"> <!-- Center buttons -->
                           <?php if(isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'user'): ?>
                               <a href="book_appointment.php?doctor_id=<?php echo $doctor['id']; ?>"
                                  class="btn btn-primary">
                                   <i class="fas fa-calendar-check"></i> Book Appointment
                               </a>
                           <?php else: ?>
                                <a href="../login.php?redirect=pages/doctors.php" class="btn btn-primary">
                                   <i class="fas fa-sign-in-alt"></i> Login to Book
                               </a>
                           <?php endif; ?>
                           <?php
                           $mapQuery = urlencode($doctor['clinic_address'] ?? $doctor['address'] ?? ($doctor['full_name'] . ', ' . $doctor['specialization']));
                           ?>
                           <a href="https://www.google.com/maps/search/?api=1&query=<?php echo $mapQuery; ?>"
                               class="btn btn-outline-primary" target="_blank">
                               <i class="fas fa-map-marker-alt"></i> View on Map
                           </a>
                        </div>
                    </div>
                </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>

    <!-- Add jQuery and Bootstrap JS (ensure jQuery is loaded first) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        $('.doctor-card-header').on('click', function(e) {
            // Don't trigger if clicking on interactive elements within the header if any were added
            // if ($(e.target).is('a, button, input, select, textarea')) {
            //    return;
            // }
            
            const $card = $(this).closest('.doctor-card');
            const $allCards = $('.doctor-card'); // Select all cards on the page
            const $body = $card.find('.doctor-card-body');
            
            // Check if the current card is already expanded
            const isExpanded = $card.hasClass('expanded');

            // Close all other cards first
            $allCards.not($card).removeClass('expanded').find('.doctor-card-body').slideUp(300); // Use slideUp for smooth closing

            // Toggle the current card
            if (isExpanded) {
                $body.slideUp(300, function() { // Slide up animation
                     $card.removeClass('expanded');
                });
            } else {
                $body.slideDown(300, function() { // Slide down animation
                    $card.addClass('expanded');
                    // Optional: Scroll into view after opening, adjust block position as needed
                    // $card[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                });
            }
        });
    });
    </script>
</body>
</html>
