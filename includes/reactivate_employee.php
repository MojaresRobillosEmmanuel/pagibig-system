<?php
session_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Use the database-level MySQLi connector
require_once __DIR__ . '/../database/db_connect.php';

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit;
}

// Get POST data
$employeeId = $_POST['id'] ?? null;

if (!$employeeId) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Employee ID is required'
    ]);
    exit;
}

try {
    $conn = getConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Check if employee exists and is inactive (status = 0)
    $checkSql = "SELECT id FROM employees WHERE id = ? AND status = 0";
    $checkStmt = $conn->prepare($checkSql);
    if (!$checkStmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $checkStmt->bind_param('i', $employeeId);
    if (!$checkStmt->execute()) {
        throw new Exception('Execute failed: ' . $checkStmt->error);
    }
    
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Employee not found or is already active'
        ]);
        exit;
    }

    // Perform the reactivation (set status to 1)
    $sql = "UPDATE employees SET status = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param('i', $employeeId);
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Employee reactivated successfully'
        ]);
    } else {
        throw new Exception('Failed to reactivate employee');
    }
    
    $checkStmt->close();
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log("Error in reactivate_employee.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to reactivate employee: ' . $e->getMessage()
    ]);
}
?>
