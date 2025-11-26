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

    if (!isset($data['pagibig_no'])) {
        throw new Exception('Pag-IBIG number is required');
    }

    $conn = getSTLConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Prepare statement to delete STL record
        $stmt = $conn->prepare("
            DELETE FROM selected_stl 
            WHERE pagibig_no = ?
        ");

        $stmt->bind_param('s', $data['pagibig_no']);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                // Commit transaction
                $conn->commit();
                Response::success("Successfully removed employee from STL");
            } else {
                throw new Exception('Employee not found in STL list');
            }
        } else {
            throw new Exception('Failed to remove employee from STL');
        }
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }
} catch (Exception $e) {
    error_log('Error in remove_from_stl.php: ' . $e->getMessage());
    Response::error($e->getMessage());
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}