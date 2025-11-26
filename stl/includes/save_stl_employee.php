<?php
session_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../../database/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON data
$inputData = file_get_contents('php://input');
$data = json_decode($inputData, true);

if (!isset($data['pagibig_number']) || !isset($data['id_number'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', array_keys($data ?? []))]);
    exit;
}

try {
    $conn = getConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    $pagibig_number = $data['pagibig_number'];
    $id_number = $data['id_number'];
    
    // Check if already exists in selected_contributions
    $checkQuery = "SELECT COUNT(*) as count FROM selected_contributions 
                   WHERE pagibig_no = ? AND system_type = 'stl'";
    $checkStmt = $conn->prepare($checkQuery);
    if (!$checkStmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $checkStmt->bind_param('s', $pagibig_number);
    if (!$checkStmt->execute()) {
        throw new Exception('Execute failed: ' . $checkStmt->error);
    }
    
    $checkResult = $checkStmt->get_result();
    $checkRow = $checkResult->fetch_assoc();
    $checkStmt->close();
    
    if ($checkRow['count'] > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Employee already added']);
        exit;
    }
    
    // Insert into selected_contributions table
    $insertQuery = "INSERT INTO selected_contributions 
                    (pagibig_no, employee_id, system_type, created_at) 
                    VALUES (?, ?, 'stl', NOW())";
    
    $insertStmt = $conn->prepare($insertQuery);
    if (!$insertStmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $insertStmt->bind_param('ss', $pagibig_number, $id_number);
    
    if (!$insertStmt->execute()) {
        throw new Exception('Execute failed: ' . $insertStmt->error);
    }
    
    echo json_encode(['success' => true, 'message' => 'Employee added successfully']);
    
    $insertStmt->close();
    $conn->close();
    
} catch(Exception $e) {
    error_log("Error in save_stl_employee.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save employee: ' . $e->getMessage()]);
}
?>

