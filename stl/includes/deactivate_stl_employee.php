<?php
// Enable error reporting but don't display to output
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Start session
session_start();

// Include database connection
require_once '../../database/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_POST['employee_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Employee ID is required']);
    exit;
}

try {
    $conn = getConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    $employee_id = (int)$_POST['employee_id'];

    // Update employee status to inactive (0)
    $query = "UPDATE employees SET status = 0 WHERE id = ? AND system_type = 'stl'";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param('i', $employee_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Employee deactivated successfully'
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Employee not found or already inactive'
        ]);
    }

    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    error_log('Error in stl/includes/deactivate_stl_employee.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
