<?php
// Enable error reporting but don't display to output
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Start session
session_start();

// Include database connection
require_once '../../database/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_POST['pagibig_no'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Pagibig number is required']);
    exit;
}

try {
    $conn = getConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    $pagibig_no = $conn->real_escape_string($_POST['pagibig_no']);

    // Delete ALL entries with this pagibig_no from selected_stl
    // (in case there are duplicates, remove all)
    $deleteQuery = "DELETE FROM selected_stl WHERE pagibig_no = '$pagibig_no'";
    
    if (!$conn->query($deleteQuery)) {
        throw new Exception('Delete query failed: ' . $conn->error);
    }

    $deletedRows = $conn->affected_rows;
    
    // If no rows were deleted, the employee wasn't found
    if ($deletedRows == 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Employee not found in STL table'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Employee removed from STL successfully',
        'rows_deleted' => $deletedRows
    ]);

} catch (Exception $e) {
    http_response_code(500);
    error_log('Error in stl/includes/remove_from_stl.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
