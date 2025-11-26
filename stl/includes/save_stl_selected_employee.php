<?php
session_start();
require_once __DIR__ . '/../../database/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['pagibig_number']) || !isset($data['id_number'])) {
        throw new Exception('Missing required fields');
    }
    
    $conn = getConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    $pagibig_number = $data['pagibig_number'];
    $id_number = $data['id_number'];
    $er = isset($data['er']) ? floatval($data['er']) : 200;
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    
    // Check if already exists
    $checkStmt = $conn->prepare("SELECT id FROM selected_stl WHERE pagibig_no = ?");
    $checkStmt->bind_param('s', $pagibig_number);
    $checkStmt->execute();
    
    if ($checkStmt->get_result()->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Employee already added']);
        $checkStmt->close();
        exit;
    }
    $checkStmt->close();
    
    // Get employee details from employees table - ONLY STL EMPLOYEES
    $getStmt = $conn->prepare("SELECT last_name, first_name, middle_name, tin, birthdate FROM employees WHERE pagibig_number = ? AND system_type = 'stl'");
    $getStmt->bind_param('s', $pagibig_number);
    $getStmt->execute();
    $empResult = $getStmt->get_result();
    
    if ($empResult->num_rows === 0) {
        throw new Exception('STL employee not found');
    }
    
    $employee = $empResult->fetch_assoc();
    $getStmt->close();
    
    // Check if selected_stl table has these columns
    $columnsResult = $conn->query("DESCRIBE selected_stl");
    $columns = [];
    while ($row = $columnsResult->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    // Build INSERT statement based on available columns
    $insertColumns = ['pagibig_no', 'id_number', 'user_id', 'er'];
    $insertValues = ['?', '?', '?', '?'];
    $bindTypes = 'ssii';
    $bindValues = [$pagibig_number, $id_number, $user_id, $er];
    
    // Add optional columns if they exist
    if (in_array('last_name', $columns)) {
        $insertColumns[] = 'last_name';
        $insertValues[] = '?';
        $bindTypes .= 's';
        $bindValues[] = $employee['last_name'];
    }
    
    if (in_array('first_name', $columns)) {
        $insertColumns[] = 'first_name';
        $insertValues[] = '?';
        $bindTypes .= 's';
        $bindValues[] = $employee['first_name'];
    }
    
    if (in_array('middle_name', $columns)) {
        $insertColumns[] = 'middle_name';
        $insertValues[] = '?';
        $bindTypes .= 's';
        $bindValues[] = $employee['middle_name'];
    }
    
    if (in_array('tin', $columns)) {
        $insertColumns[] = 'tin';
        $insertValues[] = '?';
        $bindTypes .= 's';
        $bindValues[] = $employee['tin'];
    }
    
    if (in_array('birthdate', $columns)) {
        $insertColumns[] = 'birthdate';
        $insertValues[] = '?';
        $bindTypes .= 's';
        $bindValues[] = $employee['birthdate'];
    }
    
    // Insert into selected_stl
    $sql = "INSERT INTO selected_stl (" . implode(', ', $insertColumns) . ") VALUES (" . implode(', ', $insertValues) . ")";
    $insertStmt = $conn->prepare($sql);
    
    if (!$insertStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    call_user_func_array([$insertStmt, 'bind_param'], array_merge([$bindTypes], $bindValues));
    
    if (!$insertStmt->execute()) {
        throw new Exception("Execute failed: " . $insertStmt->error);
    }
    
    echo json_encode(['success' => true, 'message' => 'Employee saved successfully']);
    $insertStmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log("Error in save_stl_selected_employee.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
