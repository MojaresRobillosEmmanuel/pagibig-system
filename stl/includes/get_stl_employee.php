<?php
session_start();
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../../database/db_connect.php';

try {
    if (!isset($_GET['pagibig_no'])) {
        throw new Exception('Pag-IBIG number is required');
    }

    $pagibigNo = $_GET['pagibig_no'];
    $conn = getConnection();

    // Get employee data from employees table (for STL system)
    $sql = "SELECT 
                id,
                pagibig_number AS pagibig_no,
                id_number,
                last_name,
                first_name,
                middle_name,
                tin,
                birthdate,
                er AS stl_amount,
                status AS active
            FROM employees 
            WHERE pagibig_number = ?
            AND system_type = 'stl'";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param('s', $pagibigNo);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();

    if (!$employee) {
        throw new Exception('Employee not found');
    }

    // Format birthdate if it exists
    if (!empty($employee['birthdate']) && $employee['birthdate'] !== '0000-00-00' && $employee['birthdate'] !== 'N/A') {
        // Try to parse and reformat
        $timestamp = @strtotime($employee['birthdate']);
        if ($timestamp !== false && $timestamp > 0) {
            $employee['birthdate'] = @date('m/d/Y', $timestamp);
        } else {
            $employee['birthdate'] = $employee['birthdate'];
        }
    } else {
        $employee['birthdate'] = null;
    }

    echo json_encode([
        'success' => true,
        'employee' => $employee
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
