<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/db_connect.php';

try {
    // Handle both POST form data and JSON input
    $data = null;
    
    if (isset($_POST['employee_id'])) {
        // Handle form POST data (for backward compatibility)
        $data = ['employee_id' => intval($_POST['employee_id'])];
    } else {
        // Handle JSON input
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);
    }

    if (!$data) {
        throw new Exception('No data provided');
    }

    $stlConn = getSTLConnection();

    if (isset($data['pagibig_no'])) {
        $pagibigNo = $data['pagibig_no'];
        
        // Check if employee is already in STL table
        $checkStmt = $stlConn->prepare("SELECT id FROM selected_stl WHERE pagibig_no = ? LIMIT 1");
        $checkStmt->bind_param('s', $pagibigNo);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $checkStmt->close();
        
        if ($checkResult->num_rows > 0) {
            // Employee already in STL, just update status to pending
            $updateStmt = $stlConn->prepare("UPDATE selected_stl SET loan_status = 'pending' WHERE pagibig_no = ?");
            $updateStmt->bind_param('s', $pagibigNo);
            $updated = $updateStmt->execute();
            $updateStmt->close();
            
            if (!$updated) {
                throw new Exception('Failed to update employee status in STL');
            }
        } else {
            // Employee not in STL, insert them back with minimal fields (like add_to_stl.php does)
            // Get user_id from session
            session_start();
            $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            if (!$userId) {
                $userId = null; // Allow NULL if session not available
            }
            
            $insertStmt = $stlConn->prepare("
                INSERT INTO selected_stl (user_id, pagibig_no, loan_status) 
                VALUES (?, ?, 'pending')
            ");
            
            if (!$insertStmt) {
                throw new Exception('Prepare failed: ' . $stlConn->error);
            }
            
            $insertStmt->bind_param('is', $userId, $pagibigNo);
            
            if (!$insertStmt->execute()) {
                throw new Exception('Insert failed: ' . $insertStmt->error);
            }
            $insertStmt->close();
        }
        
        echo json_encode([
            'status' => 'success',
            'success' => true,
            'message' => 'Employee reactivated in STL successfully'
        ]);
    } else {
        throw new Exception('Pag-IBIG number is required');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
