<?php
require_once '../../database/db_connect.php';
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Employee ID is required']);
    exit;
}

try {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("
        SELECT id, id_number, firstname as first_name, middlename as middle_name, 
               lastname as last_name, pagibig_number, tin, birthdate, ee, er
        FROM employees 
        WHERE id = :employee_id AND active = 1
    ");
    
    $stmt->execute([':employee_id' => $_GET['id']]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($employee) {
        echo json_encode(['success' => true, 'employee' => $employee]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Employee not found']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
