<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clean any existing output and start fresh
while (ob_get_level()) {
    ob_end_clean();
}

require_once __DIR__ . '/../database/db_connect.php';
require_once 'Database.php';

// Set headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required',
        'code' => 'AUTH_REQUIRED'
    ]);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Get the status from query parameter
    $status = $_GET['status'] ?? 'ACTIVE';
    $status = strtoupper($status);

    // Fetch employees with their STL status
    $sql = "SELECT 
        e.*,
        CASE WHEN stl.pagibig_no IS NOT NULL THEN 'Added' ELSE 'Not Added' END as stl_status
    FROM 
        employees e
    LEFT JOIN 
        stl_employees stl ON e.pagibig_number = stl.pagibig_no
    WHERE 
        e.status = :status
    ORDER BY 
        e.last_name, e.first_name";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute query');
    }

    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $employees,
        'count' => count($employees)
    ]);

} catch (Exception $e) {
    error_log('Error in get_contribution_employees.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'code' => 'DB_ERROR'
    ]);
}

// Clean output buffer and send response
ob_end_clean();
exit();
?>
