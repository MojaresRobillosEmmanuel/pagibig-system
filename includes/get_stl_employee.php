<?php
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../database/db_connect.php';

try {
    if (!isset($_GET['pagibig_no'])) {
        throw new Exception('Pag-IBIG number is required');
    }

    $pagibigNo = $_GET['pagibig_no'];
    $conn = getConnection();

    // Get employee data from STL table
    $sql = "SELECT 
                id,
                pagibig_no,
                id_number,
                last_name,
                first_name,
                middle_name,
                tin,
                birthdate,
                stl_amount,
                active
            FROM stl_selected_contributions 
            WHERE pagibig_no = ?";

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

    // Format birthdate
    if ($employee['birthdate']) {
        $employee['birthdate'] = date('Y-m-d', strtotime($employee['birthdate']));
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