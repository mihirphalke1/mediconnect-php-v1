<?php
require_once '../includes/config.php';

// Get search parameters
$specialization = isset($_GET['specialization']) ? $_GET['specialization'] : '';
$location = isset($_GET['location']) ? $_GET['location'] : '';

// Build query
$query = "SELECT * FROM doctors WHERE 1=1";
$params = [];

if (!empty($specialization)) {
    $query .= " AND specialization LIKE ?";
    $params[] = "%$specialization%";
}

if (!empty($location)) {
    $query .= " AND (address LIKE ? OR clinic_address LIKE ?)";
    $params[] = "%$location%";
    $params[] = "%$location%";
}

$query .= " ORDER BY full_name ASC";

// Execute query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->execute($params);
} else {
    $stmt->execute();
}
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find a Doctor - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .doctor-card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 20px;
            overflow: hidden;
            background: white;
        }
        
        .doctor-card.expanded {
            transform: scale(1.02);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        
        .doctor-card-header {
            cursor: pointer;
            padding: 20px;
            background: linear-gradient(135deg, #6B73FF 0%, #000DFF 100%);
            color: white;
            position: relative;
        }
        
        .expand-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            transition: transform 0.3s ease;
        }
        
        .doctor-card.expanded .expand-icon {
            transform: translateY(-50%) rotate(180deg);
        }
        
        .doctor-card-body {
            display: none;
            padding: 20px;
        }
        
        .doctor-card.expanded .doctor-card-body {
            display: block;
        }
        
        .doctor-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
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
        
        .section-title {
            color: #000DFF;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }
        
        .info-item {
            margin-bottom: 0.5rem;
        }
        
        .info-item i {
            width: 25px;
            color: #6B73FF;
        }
        
        .search-box {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container py-5">
        <h1 class="mb-4">Find a Doctor</h1>
        
        <div class="search-box">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <label for="specialization" class="form-label">Specialization</label>
                    <input type="text" class="form-control" id="specialization" name="specialization" 
                           value="<?php echo htmlspecialchars($specialization); ?>" placeholder="e.g., Cardiologist">
                </div>
                <div class="col-md-5">
                    <label for="location" class="form-label">Location</label>
                    <input type="text" class="form-control" id="location" name="location" 
                           value="<?php echo htmlspecialchars($location); ?>" placeholder="e.g., New York">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>
        </div>

        <div class="row">
            <?php foreach ($doctors as $doctor): ?>
            <div class="col-md-6">
                <div class="doctor-card">
                    <div class="doctor-card-header">
                        <div class="d-flex align-items-center">
                            <img src="<?php echo $doctor['profile_image'] ?: '../assets/images/default-doctor.png'; ?>" 
                                 alt="Doctor" class="doctor-image me-3">
                            <div>
                                <h3 class="h5 mb-1">Dr. <?php echo htmlspecialchars($doctor['full_name']); ?></h3>
                                <p class="mb-0"><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-down expand-icon"></i>
                    </div>
                    
                    <div class="doctor-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="section-title">Professional Information</h4>
                                <div class="info-item">
                                    <i class="fas fa-graduation-cap"></i>
                                    <strong>Experience:</strong> <?php echo $doctor['experience']; ?> years
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-dollar-sign"></i>
                                    <strong>Consultation Fee:</strong> $<?php echo number_format($doctor['consultation_fee'], 2); ?>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-clock"></i>
                                    <strong>Duration:</strong> <?php echo $doctor['consultation_duration']; ?> minutes
                                </div>
                                
                                <h4 class="section-title">Education & Qualifications</h4>
                                <?php 
                                $qualifications = explode("\n", $doctor['qualifications']);
                                foreach ($qualifications as $qualification):
                                    if (trim($qualification)): ?>
                                        <span class="qualification-badge">
                                            <?php echo htmlspecialchars(trim($qualification)); ?>
                                        </span>
                                    <?php endif;
                                endforeach; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <h4 class="section-title">Clinic Information</h4>
                                <div class="info-item">
                                    <i class="fas fa-hospital"></i>
                                    <strong>Clinic:</strong> <?php echo htmlspecialchars($doctor['clinic_name']); ?>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <strong>Address:</strong> <?php echo htmlspecialchars($doctor['clinic_address']); ?>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-phone"></i>
                                    <strong>Phone:</strong> <?php echo htmlspecialchars($doctor['clinic_phone']); ?>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-envelope"></i>
                                    <strong>Email:</strong> <?php echo htmlspecialchars($doctor['clinic_email']); ?>
                                </div>
                                
                                <h4 class="section-title">Working Hours</h4>
                                <div class="info-item">
                                    <?php echo nl2br(htmlspecialchars($doctor['working_hours'])); ?>
                                </div>
                                
                                <h4 class="section-title">Languages</h4>
                                <div class="info-item">
                                    <?php echo htmlspecialchars($doctor['languages_spoken']); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <a href="book-appointment.php?doctor_id=<?php echo $doctor['id']; ?>" 
                               class="btn btn-primary">
                                <i class="fas fa-calendar-check"></i> Book Appointment
                            </a>
                            <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($doctor['clinic_address']); ?>" 
                               class="btn btn-outline-primary ms-2" target="_blank">
                                <i class="fas fa-map-marker-alt"></i> View on Map
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $('.doctor-card-header').on('click', function(e) {
            // Don't trigger if clicking on buttons or links
            if ($(e.target).is('a, button')) {
                return;
            }
            
            const $card = $(this).closest('.doctor-card');
            const $allCards = $('.doctor-card');
            
            // Close all other cards
            $allCards.not($card).removeClass('expanded');
            
            // Toggle current card
            $card.toggleClass('expanded');
            
            // Scroll into view if expanded
            if ($card.hasClass('expanded')) {
                $card[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });
    });
    </script>
</body>
</html> 