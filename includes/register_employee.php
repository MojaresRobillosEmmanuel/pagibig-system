<?php
session_start();
header('Content-Type: application/json');

// Enable error reporting (but don't display to user - log instead)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Include database connection
require_once '../database/db_connect.php';

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

    // Get database connection
    $conn = getConnection();

    // ⚠️ IMPORTANT: This file ONLY registers employees in the CONTRIBUTION system
    // DO NOT automatically create STL employees here
    // STL employees must be registered separately via the STL module
    $system_type = 'contribution'; // 👈 TAG: Contribution System ONLY

    // 👇 CHECK FOR EXACT DUPLICATE WITHIN SAME SYSTEM ONLY
    // Only prevent registering if BOTH pagibig_number AND id_number are EXACTLY the same
    // Different employees (even with same last name) are allowed if they have different Pag-IBIG or ID numbers
    $checkStmt = $conn->prepare("SELECT id FROM employees WHERE pagibig_number = ? AND id_number = ? AND system_type = ?");
    $checkStmt->bind_param('sss', $data['pagibig_number'], $data['id_number'], $system_type);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        throw new Exception('This exact employee (same Pag-IBIG #' . $data['pagibig_number'] . ' and ID #' . $data['id_number'] . ') already exists in Contribution system.');
    }

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

    // Prepare status value
    $status = 1;

    // Bind parameters (9 parameters: pagibig_number, id_number, last_name, first_name, middle_name, tin, birthdate, system_type, status)
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

    // ✅ Employee registered successfully in CONTRIBUTION system
    // The employee can now be added to selected contributions list

    // Send success response
    echo json_encode([
        'success' => true,
        'message' => 'Employee registered successfully in Contribution system!',
        'system' => 'contribution',
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
