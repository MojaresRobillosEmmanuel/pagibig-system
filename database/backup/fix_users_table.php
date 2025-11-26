<?php
require_once '../includes/db_connect.php';

// Drop the id_number constraint if it exists
$alterQuery = "ALTER TABLE users DROP INDEX id_number";
try {
    $conn->query($alterQuery);
} catch (Exception $e) {
    // Ignore if constraint doesn't exist
}

echo "Database structure updated successfully!";
?>
