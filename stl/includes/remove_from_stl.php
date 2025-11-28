<?php
// Set JSON header FIRST - must be before any output
header('Content-Type: application/json; charset=utf-8');

// Start session
session_start();

// Include database connection
require_once '../../database/db_connect.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Validate pagibig_no parameter exists
if (!isset($_POST['pagibig_no']) || empty($_POST['pagibig_no'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Pagibig number is required']);
    exit;
}

try {
    // Get database connection
    $conn = getConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    $pagibig_no = trim($_POST['pagibig_no']);
    
    // First attempt: exact match with provided value
    $deleteQuery = "DELETE FROM selected_stl WHERE pagibig_no = ?";
    $stmt = $conn->prepare($deleteQuery);
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param('s', $pagibig_no);
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $deletedRows = $stmt->affected_rows;
    $stmt->close();
    
    // If no rows deleted, try with cleaned digits (in case there's formatting mismatch)
    if ($deletedRows == 0) {
        $pagibig_clean = preg_replace('/[^0-9]/', '', $pagibig_no);
        
        $deleteQuery = "DELETE FROM selected_stl WHERE REPLACE(REPLACE(pagibig_no, '-', ''), ' ', '') = ?";
        $stmt = $conn->prepare($deleteQuery);
        
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param('s', $pagibig_clean);
        
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        
        $deletedRows = $stmt->affected_rows;
        $stmt->close();
    }
    
    // Return response
    if ($deletedRows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Employee removed from STL successfully',
            'rows_deleted' => $deletedRows
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Employee not found in STL table'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log('Error in remove_from_stl.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
