<?php
/**
 * STL Employee Registration Handler
 * This file handles employee registration for the STL (Short Term Loan) system
 * It proxies requests to the main register_employee.php in the includes folder
 */

session_start();
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once '../../database/db_connect.php';

try {
    // Get the JSON data from the request
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        throw new Exception('Invalid input data');
    }

    // Normalize Pag-IBIG number - remove all non-digits
    if (isset($data['pagibig_number'])) {
        $data['pagibig_number'] = preg_replace('/\D/', '', $data['pagibig_number']);
    }

    // Normalize and format TIN - ensure it's in XXX-XXX-XXX-0000 format
    if (isset($data['tin']) && !empty($data['tin'])) {
        $tinDigits = preg_replace('/\D/', '', $data['tin']);
        if (strlen($tinDigits) === 12) {
            // Format as XXX-XXX-XXX-0000
            $data['tin'] = substr($tinDigits, 0, 3) . '-' . 
                          substr($tinDigits, 3, 3) . '-' . 
                          substr($tinDigits, 6, 3) . '-' . 
                          substr($tinDigits, 9);
        } else {
            $data['tin'] = $tinDigits;
        }
    }

    // Validate required fields
    $requiredFields = ['pagibig_number', 'id_number', 'last_name', 'first_name'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Ensure middle_name is set (can be empty string or null)
    if (!isset($data['middle_name'])) {
        $data['middle_name'] = '';
    }

    // Get database connection
    $conn = getConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Set system type to STL
    $system_type = 'stl';

    // CHECK FOR DUPLICATE WITHIN SAME SYSTEM ONLY
    // Prevent registering the same employee twice in the STL system
    // Only check if employee exists with BOTH same pagibig_number AND same system_type
    $checkStmt = $conn->prepare("SELECT id FROM employees WHERE pagibig_number = ? AND system_type = ?");
    if (!$checkStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $checkStmt->bind_param('ss', $data['pagibig_number'], $system_type);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        throw new Exception('This employee already exists in STL system. Cannot register the same employee twice in the same system.');
    }
    $checkStmt->close();

    // Format birthdate if provided - MUST be MM/DD/YYYY format with forward slashes
    $birthdate = null;
    if (!empty($data['birthdate'])) {
        $birthdate = trim($data['birthdate']);
        $birthdate = str_replace(' ', '', $birthdate);
        
        // REJECT if it contains dashes instead of slashes
        if (strpos($birthdate, '-') !== false) {
            throw new Exception('Invalid birthdate format. Use forward slashes (/) not dashes (-). Example: 01/20/2001');
        }
        
        // Try to parse the birthdate - ONLY MM/DD/YYYY or MM/DD/YY with forward slashes
        $date = DateTime::createFromFormat('m/d/Y', $birthdate);
        if (!$date) {
            // Try MM/DD/YY format (2-digit year)
            $date = DateTime::createFromFormat('m/d/y', $birthdate);
        }
        if (!$date) {
            // Try n/j/Y format (single digit month/day with 4-digit year)
            $date = DateTime::createFromFormat('n/j/Y', $birthdate);
        }
        if (!$date) {
            // Try n/j/y format (single digit month/day with 2-digit year)
            $date = DateTime::createFromFormat('n/j/y', $birthdate);
        }
        
        if (!$date) {
            throw new Exception('Invalid birthdate format. Please use MM/DD/YYYY (example: 01/20/2001). Use forward slashes (/) not dashes (-).');
        }
        
        // ALWAYS store in MM/DD/YYYY format with forward slashes
        $birthdate = $date->format('m/d/Y');
        
        // Verify it has forward slashes
        if (strpos($birthdate, '/') === false) {
            throw new Exception('Internal error: birthdate format validation failed');
        }
    }
    // If birthdate is empty/null, it stays null - birthdate is optional

    // Prepare insert statement
    $sql = "INSERT INTO employees (
        pagibig_number, 
        id_number, 
        last_name, 
        first_name, 
        middle_name,
        tin,
        birthdate,
        system_type,
        status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // Bind parameters - status is integer 1
    $status = 1;
    $stmt->bind_param(
        'ssssssssi',
        $data['pagibig_number'],
        $data['id_number'],
        $data['last_name'],
        $data['first_name'],
        $data['middle_name'],
        $data['tin'],
        $birthdate,
        $system_type,
        $status
    );

    // Execute the statement
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $employee_id = $conn->insert_id;
    
    // Do NOT automatically add the newly registered STL employee to selected_stl
    // Let the user manually select and add employees using the "Select Active Employees" modal
    
    $stmt->close();
    $conn->close();

    // Employee registered successfully
    // Send success response
    echo json_encode([
        'success' => true,
        'message' => 'Employee registered successfully! Use "Select Active Employees" to add them to STL.',
        'employee_id' => $employee_id
    ]);

} catch (Exception $e) {
    // Send error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
