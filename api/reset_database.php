<?php
session_start();
require_once 'database/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$conn = getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'reset_database') {
        try {
            // Step 1: Delete all employees
            $sql1 = "DELETE FROM employees";
            if (!$conn->query($sql1)) {
                throw new Exception('Failed to delete employees: ' . $conn->error);
            }
            $deletedCount = $conn->affected_rows;

            // Step 2: Reset the AUTO_INCREMENT counter for employees table
            $sql2 = "ALTER TABLE employees AUTO_INCREMENT = 1";
            if (!$conn->query($sql2)) {
                throw new Exception('Failed to reset AUTO_INCREMENT: ' . $conn->error);
            }

            // Step 3: Delete all selected_contributions
            $sql3 = "DELETE FROM selected_contributions";
            if (!$conn->query($sql3)) {
                throw new Exception('Failed to delete selected_contributions: ' . $conn->error);
            }

            echo json_encode([
                'success' => true,
                'message' => "✅ Database reset successfully! Deleted $deletedCount employees. ID counter reset to 1."
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>
