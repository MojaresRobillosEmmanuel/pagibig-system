<?php
header("Content-Type: application/json; charset=utf-8");
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    require_once __DIR__ . '/../database/db_connect.php';
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['pagibig_numbers'])) {
        throw new Exception("Invalid request data");
    }
    
    $pagibigNumbers = $data['pagibig_numbers'];
    if (!is_array($pagibigNumbers)) {
        throw new Exception("pagibig_numbers must be an array");
    }
    
    $conn = getConnection();
    $stlConn = getSTLConnection();
    
    if (!$conn || !$stlConn) {
        throw new Exception("Database connection failed");
    }
    
    // Ensure STL database and table exist
    if (!$stlConn->query("CREATE DATABASE IF NOT EXISTS pagibig_stl")) {
        throw new Exception("Failed to create STL database");
    }
    
    if (!$stlConn->select_db("pagibig_stl")) {
        throw new Exception("Failed to select STL database");
    }
    
    $create_table_sql = "CREATE TABLE IF NOT EXISTS selected_stl (
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
        INDEX idx_user_id (user_id)
    )";
    
    $stlConn->query($create_table_sql);
    
    $registered = 0;
    $duplicates = 0;
    $errors = [];
    
    foreach ($pagibigNumbers as $pagibigNo) {
        if (empty($pagibigNo)) continue;
        
        // Get employee details from main database
        $empQuery = "SELECT pagibig_number, id_number, last_name, first_name, middle_name, 
                           tin, birthdate, ee, er FROM employees WHERE pagibig_number = ? AND status = 1";
        
        $stmt = $conn->prepare($empQuery);
        if (!$stmt) {
            $errors[] = "Prepare failed: " . $conn->error;
            continue;
        }
        
        $stmt->bind_param('s', $pagibigNo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $errors[] = "Employee with Pag-IBIG $pagibigNo not found";
            $stmt->close();
            continue;
        }
        
        $employee = $result->fetch_assoc();
        $stmt->close();
        
        // Check if already registered
        $checkQuery = "SELECT id FROM selected_stl WHERE pagibig_no = ?";
        $checkStmt = $stlConn->prepare($checkQuery);
        if ($checkStmt) {
            $checkStmt->bind_param('s', $pagibigNo);
            $checkStmt->execute();
            if ($checkStmt->get_result()->num_rows > 0) {
                $duplicates++;
                $checkStmt->close();
                continue;
            }
            $checkStmt->close();
        }
        
        // Insert into STL table
        $insertQuery = "INSERT INTO selected_stl (
            pagibig_no, id_number, user_id, last_name, first_name, 
            middle_name, tin, birthdate, ee, er, loan_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        
        $insertStmt = $stlConn->prepare($insertQuery);
        if (!$insertStmt) {
            $errors[] = "Failed to register " . $employee['last_name'] . ": " . $stlConn->error;
            continue;
        }
        
        $insertStmt->bind_param(
            'ssisssssd',
            $pagibigNo,
            $employee['id_number'],
            $_SESSION['user_id'],
            $employee['last_name'],
            $employee['first_name'],
            $employee['middle_name'],
            $employee['tin'],
            $employee['birthdate'],
            $employee['ee'],
            $employee['er']
        );
        
        if ($insertStmt->execute()) {
            $registered++;
        } else {
            $errors[] = "Failed to register " . $employee['last_name'] . ": " . $stlConn->error;
        }
        $insertStmt->close();
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Registered $registered employees successfully",
        'registered' => $registered,
        'duplicates' => $duplicates,
        'errors' => $errors
    ]);
    
} catch (Exception $e) {
    error_log("Error in register_stl_employees.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
