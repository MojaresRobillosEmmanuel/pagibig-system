<?php
require_once 'Database.php';
require_once 'Response.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $sql = "SELECT 
        e.pagibig_number,
        e.id_number,
        e.last_name,
        e.first_name,
        e.middle_name,
        e.tin,
        e.birthdate,
        CASE 
            WHEN s.pagibig_number IS NOT NULL THEN 1 
            ELSE 0 
        END as has_stl
        FROM employees e
        LEFT JOIN stl s ON e.pagibig_number = s.pagibig_number
        WHERE e.status = 1
        ORDER BY e.last_name ASC";
        
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $employees = [];
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $employees]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
