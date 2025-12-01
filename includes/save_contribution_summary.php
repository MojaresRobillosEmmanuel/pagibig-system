<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../database/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filename = isset($_POST['filename']) ? $_POST['filename'] : '';
    $month = isset($_POST['month']) ? $_POST['month'] : '';
    $year = isset($_POST['year']) ? intval($_POST['year']) : 0;
    $num_contributors = isset($_POST['num_contributors']) ? intval($_POST['num_contributors']) : 0;
    $total_deducted_amount = isset($_POST['total_deducted_amount']) ? floatval($_POST['total_deducted_amount']) : 0;
    
    if (!$filename || !$month || !$year) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit;
    }
    
    try {
        $conn = getConnection();
        
        // Insert or update the summary record
        $query = "
            INSERT INTO contribution_summary (filename, month, year, num_contributors, total_deducted_amount)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                filename = VALUES(filename),
                num_contributors = VALUES(num_contributors),
                total_deducted_amount = VALUES(total_deducted_amount),
                updated_date = CURRENT_TIMESTAMP
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ssidd', $filename, $month, $year, $num_contributors, $total_deducted_amount);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Contribution summary saved successfully']);
        } else {
            echo json_encode(['status' => 'success', 'message' => 'Contribution summary already exists']);
        }
        $stmt->close();
        
    } catch (Exception $e) {
        error_log("Error saving contribution summary: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
