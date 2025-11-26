<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../database/db_connect.php';

// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/registration_errors.log');

// Helper function to send response
function sendResponse($success, $message) {
    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);
    exit;
}

try {
    // Get raw input
    $input = file_get_contents('php://input');
    error_log('Raw input received: ' . $input);
    
    if (empty($input)) {
        echo json_encode([
            'success' => false,
            'message' => 'No data received'
        ]);
        exit;
    }

    // Try to decode JSON
    $data = json_decode($input, true);
    $jsonError = json_last_error();
    
    if ($jsonError !== JSON_ERROR_NONE) {
        error_log('JSON decode error: ' . json_last_error_msg());
        error_log('Raw input: ' . bin2hex($input));
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON format: ' . json_last_error_msg(),
            'debug' => [
                'raw_input' => substr($input, 0, 1000),
                'error_code' => $jsonError
            ]
        ]);
        exit;
    }

    if (!is_array($data)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid data format: expected JSON object'
        ]);
        exit;
    }

    // Validate required fields
    $requiredFields = ['pagibig_number', 'id_number', 'last_name', 'first_name', 'birthdate'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            throw new Exception(ucfirst(str_replace('_', ' ', $field)) . ' is required');
        }
    }

    $conn = getConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // First, ensure STL database exists by creating it via main connection
    error_log('Creating STL database via main connection...');
    if (!$conn->query("CREATE DATABASE IF NOT EXISTS pagibig_stl")) {
        throw new Exception('Failed to create STL database: ' . $conn->error);
    }
    error_log('STL database creation completed');

    // Get STL connection AFTER database exists
    $stlConn = getSTLConnection();
    if (!$stlConn) {
        throw new Exception('STL Database connection failed');
    }
    error_log('STL connection established');

    // Create STL selected_stl table if it doesn't exist
    // Drop foreign key constraint first if exists to avoid issues
    $stlConn->query("ALTER TABLE selected_stl DROP FOREIGN KEY IF EXISTS selected_stl_ibfk_1");
    
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS selected_stl (
            id INT PRIMARY KEY AUTO_INCREMENT,
            pagibig_no VARCHAR(50),
            id_number VARCHAR(50),
            user_id INT,
            date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            loan_amount DECIMAL(10,2) DEFAULT 0,
            loan_status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
            INDEX idx_pagibig_no (pagibig_no)
        )
    ";
    
    error_log('Creating selected_stl table...');
    if (!$stlConn->query($createTableSQL)) {
        error_log('Note: STL table creation error: ' . $stlConn->error);
    } else {
        error_log('STL table created successfully');
    }

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Format and validate birthdate
        $birthdate = trim($data['birthdate']);
        $formattedDate = null;
        
        // Handle different date formats: MM/DD/YYYY or MM/DD/YY
        // Pattern accepts: MM/DD/YYYY, MM/DD/YY, and partial entries during typing
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})$/', $birthdate, $matches)) {
            $month = (int)$matches[1];
            $day = (int)$matches[2];
            $year = (int)$matches[3];
            
            // Validate month range
            if ($month < 1 || $month > 12) {
                sendResponse(false, 'Invalid month. Must be between 01 and 12');
            }
            
            // Validate day range
            if ($day < 1 || $day > 31) {
                sendResponse(false, 'Invalid day. Must be between 01 and 31');
            }
            
            // Validate year - must be 2 or 4 digits
            if ($year < 1 || ($year > 99 && $year < 1900) || $year > 2025) {
                sendResponse(false, 'Invalid year. Enter YY (00-99) or YYYY (1900-2025)');
            }
            
            // Convert 2-digit year to 4-digit
            if ($year < 100) {
                // 2-digit year conversion: 00-30 = 2000-2030, 31-99 = 1931-1999
                $year = ($year <= 30) ? (2000 + $year) : (1900 + $year);
            }
            
            // Validate the date is actually valid (e.g., Feb 30 doesn't exist)
            if (!checkdate($month, $day, $year)) {
                sendResponse(false, 'Invalid date. Please check your month, day, and year');
            }
            
            // Format as YYYY-MM-DD for MySQL
            $formattedDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
            error_log('Formatted date: ' . $formattedDate . ' (from ' . $birthdate . ')');
        } else {
            sendResponse(false, 'Birthdate must be in MM/DD/YYYY or MM/DD/YY format');
        }

        // Insert into employees table
        $stmt = $conn->prepare("
            INSERT INTO employees (
                pagibig_number,
                id_number,
                last_name,
                first_name,
                middle_name,
                tin,
                birthdate,
                status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        $status = 1;
        $stmt->bind_param(
            'sssssssi',
            $data['pagibig_number'],
            $data['id_number'],
            $data['last_name'],
            $data['first_name'],
            $data['middle_name'],
            $data['tin'],
            $formattedDate,
            $status
        );

        if (!$stmt->execute()) {
            // Check for duplicate entry error
            if (strpos($stmt->error, 'Duplicate entry') !== false) {
                throw new Exception('This PAG-IBIG number is already registered');
            }
            throw new Exception('Failed to insert employee: ' . $stmt->error);
        }

        $employee_id = $conn->insert_id;

        // Add to selected_stl table using pagibig_no and STL connection
        $pagibigNumber = $data['pagibig_number'];
        $idNumber = $data['id_number'];
        
        $insertSQL = "INSERT INTO selected_stl (pagibig_no, id_number, date_added) VALUES (?, ?, NOW())";
        error_log('Preparing STL insert: ' . $insertSQL);
        error_log('STL Connection status - Host: ' . ($stlConn ? 'Connected' : 'Failed'));
        
        $stmt = $stlConn->prepare($insertSQL);
        
        if (!$stmt) {
            error_log('Prepare failed for: ' . $insertSQL);
            error_log('Prepare error (string): "' . $stlConn->error . '"');
            error_log('Prepare error (code): ' . $stlConn->errno);
            error_log('Connection error: ' . $stlConn->connect_error);
            
            // Try to get more debugging info
            error_log('Selected DB check - Query result: ' . var_export($stlConn->query("SELECT DATABASE()"), true));
            
            throw new Exception('Failed to prepare STL insert. Error: ' . ($stlConn->error ?: 'Connection issue (errno: ' . $stlConn->errno . ')'));
        }
        
        error_log('Binding params: pagibig_no=' . $pagibigNumber . ', id_number=' . $idNumber);
        if (!$stmt->bind_param('ss', $pagibigNumber, $idNumber)) {
            error_log('Bind_param failed: ' . $stmt->error);
            throw new Exception('Failed to bind parameters: ' . $stmt->error);
        }
        
        if (!$stmt->execute()) {
            error_log('Execute failed: ' . $stmt->error);
            throw new Exception('Failed to add employee to STL list: ' . $stmt->error);
        }
        
        error_log('STL insert successful for pagibig: ' . $pagibigNumber);
        $stmt->close();

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Employee registered successfully'
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    error_log('Registration error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    $errorResponse = [
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ];
    
    if (isset($conn)) {
        $conn->rollback();
    }
    
    echo json_encode($errorResponse);
}
?>
