<?php
session_start();
require_once 'database/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$conn = getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete_all_employees') {
        // Delete all employees from contribution system
        $sql = "DELETE FROM employees WHERE system_type = 'contribution'";
        
        if ($conn->query($sql)) {
            $count = $conn->affected_rows;
            
            // Also delete selected contributions
            $conn->query("DELETE FROM selected_contributions");
            
            echo json_encode([
                'success' => true,
                'message' => "Deleted $count employees from Contribution system"
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $conn->error
            ]);
        }
        exit;
    }

    if ($action === 'get_employees') {
        // Get all employees in contribution system
        $sql = "SELECT id, pagibig_number, id_number, last_name, first_name, system_type 
                FROM employees WHERE system_type = 'contribution' 
                ORDER BY pagibig_number";
        
        $result = $conn->query($sql);
        $employees = $result->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode([
            'success' => true,
            'count' => count($employees),
            'employees' => $employees
        ]);
        exit;
    }

    if ($action === 'delete_employee') {
        $id = intval($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            exit;
        }

        $sql = "DELETE FROM employees WHERE id = ? AND system_type = 'contribution'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Employee deleted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
        }
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>
