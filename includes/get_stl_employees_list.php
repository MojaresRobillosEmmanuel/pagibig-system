<?php
header("Content-Type: application/json; charset=utf-8");
// Do not display PHP errors directly to the client for API endpoints
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Include database connection
require_once __DIR__ . '/../database/db_connect.php';

try {
    // Get database connections
    $conn = getConnection();
    $stlConn = getSTLConnection();
    
    // Create STL database and table if they don't exist using STL connection
    if (!$stlConn->query("CREATE DATABASE IF NOT EXISTS pagibig_stl")) {
        throw new Exception("Failed to create STL database");
    }
    
    $create_table_sql = "CREATE TABLE IF NOT EXISTS selected_stl (
        id INT PRIMARY KEY AUTO_INCREMENT,
        pagibig_no VARCHAR(50),
        id_number VARCHAR(50),
        user_id INT,
        date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        loan_amount DECIMAL(10,2) DEFAULT 0,
        loan_status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
        INDEX idx_pagibig_no (pagibig_no)
    )";
    
    $stlConn->query($create_table_sql);

    // Query to get ONLY employees in the STL system (those registered in selected_stl)
    // Using separate queries for different databases
    $stlQuery = "SELECT pagibig_no, date_added, loan_status, er FROM selected_stl ORDER BY date_added DESC";
    
    error_log('STL Query: ' . $stlQuery);
    $stlResult = $stlConn->query($stlQuery);

    if (!$stlResult) {
        error_log('STL Query Error: ' . $stlConn->error);
        throw new Exception("Database query failed: " . $stlConn->error);
    }
    
    // Get STL employee pagibig numbers
    $stlEmployees = array();
    while ($row = $stlResult->fetch_assoc()) {
        $stlEmployees[$row['pagibig_no']] = array(
            'date_added' => $row['date_added'],
            'loan_status' => $row['loan_status'],
            'er' => $row['er']
        );
    }
    
    // Now query the main database for active employees
    $empQuery = "SELECT 
                    id,
                    pagibig_number,
                    id_number,
                    last_name,
                    first_name,
                    middle_name,
                    tin,
                    birthdate,
                    ee,
                    er
                FROM employees
                WHERE status = 1
                ORDER BY last_name, first_name";
    
    error_log('Employee Query: ' . $empQuery);
    $empResult = $conn->query($empQuery);

    if (!$empResult) {
        error_log('Employee Query Error: ' . $conn->error);
        throw new Exception("Database query failed: " . $conn->error);
    }

    $employees = [];
    while ($row = $empResult->fetch_assoc()) {
        // Only include employees that are in STL
        if (isset($stlEmployees[$row['pagibig_number']])) {
            $stlData = $stlEmployees[$row['pagibig_number']];
            $employees[] = [
                'id' => $row['id'],
                'pagibig_number' => $row['pagibig_number'],
                'id_number' => $row['id_number'],
                'last_name' => $row['last_name'],
                'first_name' => $row['first_name'],
                'middle_name' => $row['middle_name'],
                'tin' => $row['tin'],
                'birthdate' => $row['birthdate'],
                'ee' => $row['ee'],
                'er' => $stlData['er'],  // Use ER from STL table, not employee table
                'date_added' => $stlData['date_added'],
                'loan_status' => $stlData['loan_status']
            ];
        }
    }
    
    error_log('STL Employees found: ' . count($employees));

    // Return JSON response
    echo json_encode([
        'success' => true,
        'message' => 'STL Employees retrieved successfully',
        'data' => $employees,
        'count' => count($employees)
    ]);
    exit;

} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
