<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session and include dependencies
session_start();
require_once '../database/db_connect.php';
require_once 'Response.php';

try {
    // Get the JSON data from the request
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    if (!isset($data['pagibig_nos']) || !is_array($data['pagibig_nos'])) {
        throw new Exception('Invalid or missing pagibig numbers');
    }

    $conn = getSTLConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Get user ID from session
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        if (!$userId) {
            throw new Exception('User not authenticated');
        }

        // Prepare statement to insert STL records with default ER value of 200
        $stmt = $conn->prepare("
            INSERT IGNORE INTO selected_stl (user_id, pagibig_no, loan_status, er) 
            VALUES (?, ?, 'pending', 200)
        ");

        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $conn->error);
        }

        // Process each Pag-IBIG number
        $successCount = 0;
        foreach ($data['pagibig_nos'] as $pagibigNo) {
            if (!$stmt->bind_param('is', $userId, $pagibigNo)) {
                error_log('Bind param failed: ' . $stmt->error);
                continue;
            }
            if (!$stmt->execute()) {
                error_log('Execute failed: ' . $stmt->error);
                continue;
            }
            $successCount++;
        }

        // Commit transaction
        $conn->commit();

        Response::success("Successfully added $successCount employee(s) to STL");
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }
} catch (Exception $e) {
    error_log('Error in add_to_stl.php: ' . $e->getMessage());
    Response::error($e->getMessage());
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
