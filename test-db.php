<?php
// Database connection test
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';

echo "<h1>Database Connection Test</h1>";

try {
    echo "<p>Connected to database successfully!</p>";
    
    // Check appointments table structure
    $stmt = $conn->prepare("DESCRIBE appointments");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Appointments Table Structure:</h2>";
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    
    // Check if doctor_message column exists
    $hasMessage = false;
    foreach ($columns as $column) {
        if ($column['Field'] == 'doctor_message') {
            $hasMessage = true;
            break;
        }
    }
    
    if (!$hasMessage) {
        echo "<p style='color: red;'>The doctor_message column is missing from the appointments table.</p>";
        echo "<p>Run this SQL to add it: <code>ALTER TABLE appointments ADD COLUMN doctor_message TEXT AFTER notes;</code></p>";
    } else {
        echo "<p style='color: green;'>The doctor_message column exists. Table structure is correct.</p>";
    }
    
    // Check if there are any pending appointments
    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE status = 'pending'");
    $stmt->execute();
    $pendingCount = $stmt->fetchColumn();
    
    echo "<p>There are $pendingCount pending appointments.</p>";
    
    // Create a test notification
    if (isset($_GET['test_notification'])) {
        $stmt = $conn->prepare("
            INSERT INTO notifications (user_id, title, message, type, reference_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([1, 'Test Notification', 'This is a test notification', 'test', 1]);
        
        if ($result) {
            echo "<p style='color: green;'>Test notification created successfully!</p>";
        } else {
            echo "<p style='color: red;'>Failed to create test notification.</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Database error: " . $e->getMessage() . "</p>";
}

// Add a test notification link
echo "<p><a href='test-db.php?test_notification=1'>Create Test Notification</a></p>";
?> 