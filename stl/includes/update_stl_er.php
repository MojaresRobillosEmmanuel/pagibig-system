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
    $pagibig_no = isset($_POST['pagibig_no']) ? trim($_POST['pagibig_no']) : '';
    $er = isset($_POST['er']) ? floatval($_POST['er']) : 0;
    
    if (empty($pagibig_no)) {
        throw new Exception('PAG-IBIG number is required');
    }
    
    $conn = getConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Update ER value in selected_stl
    $updateStmt = $conn->prepare("UPDATE selected_stl SET er = ? WHERE pagibig_no = ?");
    $updateStmt->bind_param('ds', $er, $pagibig_no);
    
    if (!$updateStmt->execute()) {
        throw new Exception("Update failed: " . $updateStmt->error);
    }
    
    echo json_encode(['success' => true, 'message' => 'ER value updated successfully']);
    $updateStmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log("Error in update_stl_er.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
