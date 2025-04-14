<?php
/**
 * Fix Prescriptions Table
 * This script adds the missing uploaded_at column to the prescriptions table if it doesn't exist
 */

// Include configuration
require_once 'includes/config.php';

echo "<h1>Checking and fixing prescriptions table</h1>";

try {
    // Start transaction
    $conn->beginTransaction();
    
    // Check if uploaded_at column exists
    $stmt = $conn->prepare("SHOW COLUMNS FROM `prescriptions` LIKE 'uploaded_at'");
    $stmt->execute();
    $column_exists = $stmt->rowCount() > 0;
    
    if (!$column_exists) {
        echo "<p>The uploaded_at column is missing. Adding it now...</p>";
        
        // Add the missing column
        $stmt = $conn->prepare("ALTER TABLE `prescriptions` ADD COLUMN `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP");
        $stmt->execute();
        
        echo "<p>Column added successfully!</p>";
    } else {
        echo "<p>The uploaded_at column already exists.</p>";
    }
    
    // Commit transaction
    $conn->commit();
    
    echo "<p>Table check completed successfully.</p>";
    echo "<p><a href='prescriptions.php'>Go to Prescriptions Page</a></p>";
    
} catch (PDOException $e) {
    // Rollback transaction
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?> 