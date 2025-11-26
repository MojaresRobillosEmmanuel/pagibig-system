<?php
/**
 * Database Schema Migration
 * Adds missing columns to employees table if needed
 */

header('Content-Type: application/json');

require_once __DIR__ . '/db_connect.php';

try {
    $conn = getConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Check if ee column exists
    $result = $conn->query("SHOW COLUMNS FROM employees LIKE 'ee'");
    $hasEE = $result && $result->num_rows > 0;
    
    // Check if er column exists
    $result = $conn->query("SHOW COLUMNS FROM employees LIKE 'er'");
    $hasER = $result && $result->num_rows > 0;
    
    // Check if system_type column exists
    $result = $conn->query("SHOW COLUMNS FROM employees LIKE 'system_type'");
    $hasSystemType = $result && $result->num_rows > 0;

    $addedColumns = [];

    // Add ee column if missing
    if (!$hasEE) {
        $conn->query("ALTER TABLE employees ADD COLUMN ee DECIMAL(10,2) DEFAULT 0");
        $addedColumns[] = 'ee';
    }

    // Add er column if missing
    if (!$hasER) {
        $conn->query("ALTER TABLE employees ADD COLUMN er DECIMAL(10,2) DEFAULT 0");
        $addedColumns[] = 'er';
    }

    // Add system_type column if missing
    if (!$hasSystemType) {
        $conn->query("ALTER TABLE employees ADD COLUMN system_type VARCHAR(50) DEFAULT 'contribution'");
        $addedColumns[] = 'system_type';
    }

    echo json_encode([
        'success' => true,
        'message' => empty($addedColumns) ? 'All columns already exist' : 'Columns added successfully',
        'columns_added' => $addedColumns,
        'ee_exists' => $hasEE,
        'er_exists' => $hasER,
        'system_type_exists' => $hasSystemType
    ]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("Migration Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
