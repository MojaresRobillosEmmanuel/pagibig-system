<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../../database/pdo_connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_POST['employee_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Employee ID is required']);
    exit;
}

try {
    $pdo = getPDOConnection();
    if (!$pdo) throw new Exception('Database connection failed');

    // Update status to 1 (active) - ONLY for STL employees
    // ⚠️ IMPORTANT: Only reactivate STL employees, not contribution employees
    $stmt = $pdo->prepare("UPDATE employees SET status = 1 WHERE id = :employee_id AND system_type = 'stl'");
    $stmt->bindParam(':employee_id', $_POST['employee_id']);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Employee reactivated successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'STL employee not found or already active']);
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log('Error in stl/reactivate_employee.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
