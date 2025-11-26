<?php
session_start();
require_once __DIR__ . '/../../database/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $pagibig_number = isset($_POST['pagibig_number']) ? trim($_POST['pagibig_number']) : '';
    $id_number = isset($_POST['id_number']) ? trim($_POST['id_number']) : '';
    $last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
    $first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $middle_name = isset($_POST['middle_name']) ? trim($_POST['middle_name']) : '';
    $tin = isset($_POST['tin']) ? trim($_POST['tin']) : '';
    $birthdate = isset($_POST['birthdate']) ? trim($_POST['birthdate']) : null;
    
    if (!$id || !$pagibig_number) {
        throw new Exception('Employee ID and Pag-IBIG number are required');
    }
    
    $conn = getConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Update employee in employees table
    $updateStmt = $conn->prepare("
        UPDATE employees 
        SET id_number = ?, last_name = ?, first_name = ?, middle_name = ?, tin = ?, birthdate = ?
        WHERE id = ? AND pagibig_number = ?
    ");
    
    if (!$updateStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $updateStmt->bind_param('ssssssss', $id_number, $last_name, $first_name, $middle_name, $tin, $birthdate, $id, $pagibig_number);
    
    if (!$updateStmt->execute()) {
        throw new Exception("Update failed: " . $updateStmt->error);
    }
    
    $affectedRows = $updateStmt->affected_rows;
    $updateStmt->close();
    
    if ($affectedRows === 0) {
        throw new Exception('Employee not found or no changes made');
    }
    
    echo json_encode(['success' => true, 'message' => 'Employee updated successfully']);
    $conn->close();
    
} catch (Exception $e) {
    error_log("Error in update_employee.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
