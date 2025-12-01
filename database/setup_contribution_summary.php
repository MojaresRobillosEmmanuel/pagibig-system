<?php
// Simple script to create contribution_summary table
require_once __DIR__ . '/db_connect.php';

try {
    $conn = getConnection();
    
    $sql = "CREATE TABLE IF NOT EXISTS contribution_summary (
      id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      filename VARCHAR(255) NOT NULL,
      backup_file_path VARCHAR(255),
      month VARCHAR(20) NOT NULL,
      year INT(11) NOT NULL,
      num_contributors INT(11) NOT NULL DEFAULT 0,
      total_deducted_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
      created_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      updated_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      KEY month_year (month, year)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql) === TRUE) {
        echo "Contribution Summary table created successfully!";
    } else {
        echo "Error creating table: " . $conn->error;
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
