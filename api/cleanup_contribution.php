<?php
session_start();
require_once 'database/db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$conn = getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'get_contribution_count') {
        // Count employees in contribution system
        $sql = "SELECT COUNT(*) as count FROM employees WHERE system_type = 'contribution'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'count' => $row['count']
        ]);
        exit;
    }

    if ($action === 'clear_all_contribution') {
        // Delete ALL employees from contribution system
        $sql = "DELETE FROM employees WHERE system_type = 'contribution'";
        
        if ($conn->query($sql)) {
            $deletedCount = $conn->affected_rows;
            
            // Also clear selected_contributions for all users
            $deleteSql = "DELETE FROM selected_contributions";
            $conn->query($deleteSql);
            
            echo json_encode([
                'success' => true,
                'message' => "Successfully cleared $deletedCount contribution employees and their selections"
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to clear: ' . $conn->error
            ]);
        }
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>
