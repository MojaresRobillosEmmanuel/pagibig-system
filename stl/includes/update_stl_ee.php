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

// Validate required parameters
if (!isset($_POST['pagibig_no']) || empty($_POST['pagibig_no'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Pagibig number is required']);
    exit;
}

if (!isset($_POST['ee']) || $_POST['ee'] === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'EE value is required']);
    exit;
}

try {
    // Get database connection
    $conn = getConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    $pagibig_no = trim($_POST['pagibig_no']);
    $ee_value = floatval($_POST['ee']);
    
    // Update EE in selected_stl table and set ER to 0.00
    $updateQuery = "UPDATE selected_stl 
                    SET ee = ?, er = 0.00 
                    WHERE pagibig_no = ?";
    $stmt = $conn->prepare($updateQuery);
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param('ds', $ee_value, $pagibig_no);
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $updatedRows = $stmt->affected_rows;
    $stmt->close();
    
    // If no rows updated, try with cleaned digits (in case there's formatting mismatch)
    if ($updatedRows == 0) {
        $pagibig_clean = preg_replace('/[^0-9]/', '', $pagibig_no);
        
        $updateQuery = "UPDATE selected_stl 
                        SET ee = ?, er = 0.00 
                        WHERE REPLACE(REPLACE(pagibig_no, '-', ''), ' ', '') = ?";
        $stmt = $conn->prepare($updateQuery);
        
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param('ds', $ee_value, $pagibig_clean);
        
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        
        $updatedRows = $stmt->affected_rows;
        $stmt->close();
    }
    
    // Return response
    if ($updatedRows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'EE value updated successfully and ER cleared',
            'ee' => number_format($ee_value, 2),
            'er' => '0.00',
            'rows_updated' => $updatedRows
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
    error_log('Error in update_stl_ee.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
