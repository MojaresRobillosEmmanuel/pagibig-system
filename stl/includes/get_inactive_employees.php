<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Use project's PDO connector
require_once __DIR__ . '/../../includes/db_connect.php';
header('Content-Type: application/json');

try {
    $pdo = getConnection();
    if (!$pdo) throw new Exception('Database connection failed');

    $stmt = $pdo->prepare("SELECT id, id_number, first_name, middle_name, last_name
        FROM employees
        WHERE LOWER(status) = 'inactive'
        ORDER BY last_name, first_name");
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute query");
    }

    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $employees
    ]);

} catch (Exception $e) {
    error_log("Error in get_inactive_employees.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch inactive employees: ' . $e->getMessage()
    ]);
}
