<?php
/**
 * Auto-setup and Validation Script
 * Runs at startup to ensure database is properly configured
 */

require_once __DIR__ . '/../database/db_connect.php';

try {
    $conn = getConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Log setup check
    error_log("Running database schema validation...");

    // Check if ee column exists
    $result = $conn->query("SHOW COLUMNS FROM employees LIKE 'ee'");
    if (!$result || $result->num_rows === 0) {
        error_log("Adding missing ee column to employees table");
        $conn->query("ALTER TABLE employees ADD COLUMN ee DECIMAL(10,2) DEFAULT 0");
    }

    // Check if er column exists
    $result = $conn->query("SHOW COLUMNS FROM employees LIKE 'er'");
    if (!$result || $result->num_rows === 0) {
        error_log("Adding missing er column to employees table");
        $conn->query("ALTER TABLE employees ADD COLUMN er DECIMAL(10,2) DEFAULT 0");
    }

    // Check if system_type column exists
    $result = $conn->query("SHOW COLUMNS FROM employees LIKE 'system_type'");
    if (!$result || $result->num_rows === 0) {
        error_log("Adding missing system_type column to employees table");
        $conn->query("ALTER TABLE employees ADD COLUMN system_type VARCHAR(50) DEFAULT 'contribution'");
    }

    // Check if selected_stl table exists
    $result = $conn->query("SHOW TABLES LIKE 'selected_stl'");
    if (!$result || $result->num_rows === 0) {
        error_log("Creating missing selected_stl table");
        $createTableSQL = "CREATE TABLE IF NOT EXISTS selected_stl (
            id INT PRIMARY KEY AUTO_INCREMENT,
            pagibig_no VARCHAR(50) NOT NULL UNIQUE,
            id_number VARCHAR(50),
            user_id INT,
            last_name VARCHAR(100),
            first_name VARCHAR(100),
            middle_name VARCHAR(100),
            tin VARCHAR(20),
            birthdate DATE,
            ee DECIMAL(10,2) DEFAULT 0,
            er DECIMAL(10,2) DEFAULT 0,
            date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            loan_amount DECIMAL(10,2) DEFAULT 0,
            loan_status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
            is_active TINYINT(1) DEFAULT 1,
            INDEX idx_pagibig_no (pagibig_no),
            INDEX idx_user_id (user_id),
            INDEX idx_date_added (date_added),
            FOREIGN KEY (pagibig_no) REFERENCES employees(pagibig_number) ON DELETE CASCADE
        )";
        $conn->query($createTableSQL);
    }

    // Check if selected_stl has all required columns
    $result = $conn->query("DESCRIBE selected_stl");
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }

    $requiredColumns = ['pagibig_no', 'id_number', 'user_id', 'ee', 'er', 'tin', 'birthdate', 'is_active'];
    $columnsAdded = [];
    
    foreach ($requiredColumns as $col) {
        if (!in_array($col, $columns)) {
            error_log("Adding missing column $col to selected_stl table");
            $added = false;
            
            switch ($col) {
                case 'ee':
                    $added = $conn->query("ALTER TABLE selected_stl ADD COLUMN ee DECIMAL(10,2) DEFAULT 0");
                    break;
                case 'er':
                    $added = $conn->query("ALTER TABLE selected_stl ADD COLUMN er DECIMAL(10,2) DEFAULT 0");
                    break;
                case 'tin':
                    $added = $conn->query("ALTER TABLE selected_stl ADD COLUMN tin VARCHAR(20)");
                    break;
                case 'birthdate':
                    $added = $conn->query("ALTER TABLE selected_stl ADD COLUMN birthdate DATE");
                    break;
                case 'is_active':
                    $added = $conn->query("ALTER TABLE selected_stl ADD COLUMN is_active TINYINT(1) DEFAULT 1");
                    break;
            }
            
            if ($added) {
                $columnsAdded[] = $col;
            } else {
                error_log("Failed to add column $col: " . $conn->error);
            }
        }
    }

    error_log("Database schema validation completed successfully");

} catch (Exception $e) {
    error_log("Database setup error: " . $e->getMessage());
}
?>
