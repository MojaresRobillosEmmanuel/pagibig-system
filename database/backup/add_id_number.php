<?php
require_once '../includes/db_connect.php';

// Add id_number column to users table
$alterQuery = "ALTER TABLE users ADD COLUMN id_number VARCHAR(50) UNIQUE AFTER id";

try {
    if ($conn->query($alterQuery)) {
        echo "Successfully added id_number column to users table\n";
    } else {
        echo "Error adding id_number column: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
