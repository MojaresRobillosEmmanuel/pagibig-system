<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_POST['pagibig_no']) || empty($_POST['pagibig_no'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Pag-IBIG number is required'
    ]);
    exit;
}

$pagibigNo = $_POST['pagibig_no'];

try {
    // Start transaction
    $conn->beginTransaction();

    // Check if employee exists and is active
    $checkStmt = $conn->prepare("SELECT id FROM employees WHERE pagibig_number = ? AND status = 'active'");
    $checkStmt->execute([$pagibigNo]);
    
    if ($checkStmt->rowCount() === 0) {
        throw new Exception('Employee not found or is not active');
    }

    // Check if employee is already in the contribution list
    $checkContribStmt = $conn->prepare("SELECT id FROM employee_contributions WHERE pagibig_number = ? AND status = 'active'");
    $checkContribStmt->execute([$pagibigNo]);
    
    if ($checkContribStmt->rowCount() > 0) {
        throw new Exception('Employee is already in the contribution list');
    }

    // Add employee to contribution list
    $insertStmt = $conn->prepare("INSERT INTO employee_contributions (pagibig_number, status, created_at) VALUES (?, 'active', NOW())");
    if (!$insertStmt->execute([$pagibigNo])) {
        throw new Exception('Failed to add employee to contribution list');
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Employee added to contribution list successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log("Error in add_to_contribution.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
