<?php
// Do not output raw PHP errors to clients — return JSON on error instead
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Use the database-level MySQLi connector
require_once __DIR__ . '/../database/db_connect.php';

// Tell client it's JSON output
header('Content-Type: application/json');

// Initialize connection
$conn = null;
try {
    $conn = getConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log("DB init error in get_inactive_employees.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'code' => 'DB_ERROR'
    ]);
    exit;
}

try {
    // Query for inactive employees (status = 0) in contribution system
    $sql = "SELECT id, pagibig_number, id_number, last_name, first_name, middle_name, status, system_type 
            FROM employees 
            WHERE status = 0 AND system_type = 'contribution' 
            ORDER BY last_name ASC";
    
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Failed to execute query: " . $conn->error);
    }

    $employees = [];
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }

    echo json_encode([
        'success' => true,
        'data' => $employees,
        'count' => count($employees),
        'system' => 'contribution'
    ]);
    
    $conn->close();
    exit;

} catch (Exception $e) {
    http_response_code(500);
    error_log("Error in get_inactive_employees.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch inactive employees: ' . $e->getMessage(),
        'error' => true
    ]);
    exit;
}
?>
