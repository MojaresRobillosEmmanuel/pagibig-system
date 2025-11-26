<?php
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../database/db_connect.php';

try {
    // Get JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        throw new Exception('Invalid input data');
    }

    // Validate required fields
    $requiredFields = ['pagibig_no', 'id_number', 'last_name', 'first_name', 'birthdate'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            throw new Exception("$field is required");
        }
    }

    $conn = getConnection();
    
    // Start transaction
    $conn->begin_transaction();

    try {
        // Update employee data in STL table
        $sql = "UPDATE stl_selected_contributions SET 
                id_number = ?,
                last_name = ?,
                first_name = ?,
                middle_name = ?,
                tin = ?,
                birthdate = ?,
                stl_amount = ?
            WHERE pagibig_no = ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            'ssssssds',
            $data['id_number'],
            $data['last_name'],
            $data['first_name'],
            $data['middle_name'],
            $data['tin'],
            $data['birthdate'],
            $data['stl_amount'],
            $data['pagibig_no']
        );

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        if ($stmt->affected_rows === 0) {
            throw new Exception("No employee found with the provided Pag-IBIG number");
        }

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Employee updated successfully'
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>