<?php
require_once '../includes/db_connect.php';

// Add the missing columns
$alterQueries = [
    "ALTER TABLE users ADD COLUMN first_name VARCHAR(100) NOT NULL AFTER id",
    "ALTER TABLE users ADD COLUMN last_name VARCHAR(100) NOT NULL AFTER first_name",
    "ALTER TABLE users ADD COLUMN middle_name VARCHAR(100) AFTER last_name"
];

foreach ($alterQueries as $query) {
    try {
        if ($conn->query($query)) {
            echo "Successfully executed: $query\n";
        } else {
            echo "Error executing: $query\n" . $conn->error . "\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

echo "Migration completed!\n";
?>
