<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/db_connect.php';

try {
    // Handle both POST form data and JSON input
    $data = null;
    
    if (isset($_POST['employee_id'])) {
        // Handle form POST data (for backward compatibility)
        $data = ['employee_id' => intval($_POST['employee_id'])];
    } else {
        // Handle JSON input
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);
    }

    if (!$data) {
        throw new Exception('No data provided');
    }

    $stlConn = getSTLConnection();

    if (isset($data['employee_id'])) {
        // Delete by employee_id (from employees table)
        $stmt = $stlConn->prepare("DELETE FROM selected_stl WHERE id = ?");
        $stmt->bind_param('i', $data['employee_id']);
    } elseif (isset($data['pagibig_no'])) {
        // Delete by pagibig_no (from selected_stl table)
        $stmt = $stlConn->prepare("DELETE FROM selected_stl WHERE pagibig_no = ?");
        $stmt->bind_param('s', $data['pagibig_no']);
    } else {
        throw new Exception('Employee ID or Pag-IBIG number is required');
    }
    
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'success' => true,
            'message' => 'Employee removed from STL successfully'
        ]);
    } else {
        throw new Exception('Failed to remove employee from STL');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
