<?php
require_once '../includes/db_connect.php';

// Create employees table if it doesn't exist
$createTableSQL = "CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pagibig_number VARCHAR(50) UNIQUE NOT NULL,
    id_number VARCHAR(50) UNIQUE NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    tin VARCHAR(50),
    birthdate DATE NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

try {
    if ($conn->query($createTableSQL)) {
        echo "Employees table created successfully or already exists\n";
    } else {
        echo "Error creating table: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
