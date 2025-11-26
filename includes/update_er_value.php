<?php
header('Content-Type: application/json');
session_start();

// Include database connection
require_once '../database/db_connect.php';

try {
    // Get JSON data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        throw new Exception('Invalid input data');
    }

    if (!isset($data['pagibig_no']) || !isset($data['er'])) {
        throw new Exception('Pag-IBIG number and ER value are required');
    }

    // Get STL database connection
    $stlConn = getSTLConnection();

    // Normalize Pag-IBIG number (remove dashes)
    $pagibigNo = preg_replace('/\D/', '', $data['pagibig_no']);

    // Update ER value in selected_stl table
    $sql = "UPDATE selected_stl SET er = ? WHERE pagibig_no = ?";
    
    $stmt = $stlConn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $stlConn->error);
    }

    $er = floatval($data['er']);

    $stmt->bind_param('ds', $er, $pagibigNo);

    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'ER value updated successfully'
        ]);
    } else {
        throw new Exception('No rows updated. Employee may not be in STL system.');
    }

    $stmt->close();

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
