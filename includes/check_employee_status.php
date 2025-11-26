<?php
// Disable display errors to prevent JSON corruption
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once '../database/db_connect.php';

header('Content-Type: application/json');

// Check if pagibig_no is provided
if (!isset($_POST['pagibig_no'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Pag-IBIG number is required']);
    exit;
}

$pagibig_no = $_POST['pagibig_no'];

try {
    $conn = getConnection();
    
    if (!$conn) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        exit;
    }

    // Check if employee is in selected_contributions table (Contribution system)
    $contrib_query = "SELECT COUNT(*) as count FROM selected_contributions WHERE pagibig_no = ?";
    $contrib_stmt = $conn->prepare($contrib_query);
    
    if (!$contrib_stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $conn->error]);
        exit;
    }
    
    $contrib_stmt->bind_param('s', $pagibig_no);
    $contrib_stmt->execute();
    $contrib_result = $contrib_stmt->get_result();
    $contrib_row = $contrib_result->fetch_assoc();
    $in_contributions = ($contrib_row && $contrib_row['count'] > 0);
    
    $contrib_stmt->close();

    // Return status
    echo json_encode([
        'success' => true,
        'in_contributions' => $in_contributions,
        'status' => $in_contributions ? 'ALREADY ADDED' : 'NOT IN TABLE'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("check_employee_status error: " . $e->getMessage());
    echo json_encode([
        'error' => 'Error checking employee status'
    ]);
}
?>
