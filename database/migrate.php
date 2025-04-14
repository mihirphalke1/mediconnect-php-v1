<?php
require_once '../config/database.php';

try {
    // Read the migration file
    $migration = file_get_contents(__DIR__ . '/migrations/add_doctor_profile_columns.sql');
    
    // Execute the migration
    $conn->exec($migration);
    
    echo "Migration completed successfully!";
} catch (PDOException $e) {
    echo "Error running migration: " . $e->getMessage();
}
?> 