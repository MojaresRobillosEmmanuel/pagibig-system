<?php
session_start();
require_once 'database/db_connect.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get database connection
$conn = getConnection();

if (!$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['action'])) {
            throw new Exception('No action specified');
        }

        if (!$conn->begin_transaction()) {
            throw new Exception('Failed to start transaction: ' . $conn->error);
        }

        switch ($data['action']) {
            case 'edit':
                if (!isset($data['employee'])) {
                    throw new Exception('No employee data provided');
                }

                $employee = $data['employee'];
                $userId = $_SESSION['user_id'];

                // The selected_contributions table only stores: id, pagibig_no, id_number, user_id, date_added, status
                // We can only update id_number
                $sql = "UPDATE selected_contributions 
                        SET id_number = ?
                        WHERE user_id = ? AND pagibig_no = ?";
                
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Prepare failed: ' . $conn->error);
                }
                
                $stmt->bind_param("sis", 
                    $employee['id_no'],
                    $userId,
                    $employee['pagibig_no']
                );
                
                if (!$stmt->execute()) {
                    throw new Exception('Execute failed: ' . $stmt->error);
                }

                if ($stmt->affected_rows === 0) {
                    throw new Exception('No employee found with the given Pag-IBIG number');
                }
                
                $stmt->close();
                break;

            case 'add':
                if (!isset($data['employee'])) {
                    throw new Exception('No employee data provided');
                }

                $employee = $data['employee'];
                $userId = $_SESSION['user_id'];

                // Check if employee already exists
                $checkSql = "SELECT id FROM selected_contributions WHERE user_id = ? AND pagibig_no = ?";
                $checkStmt = $conn->prepare($checkSql);
                if (!$checkStmt) {
                    throw new Exception('Prepare check failed: ' . $conn->error);
                }
                
                $checkStmt->bind_param("is", $userId, $employee['pagibig_no']);
                if (!$checkStmt->execute()) {
                    throw new Exception('Check execute failed: ' . $checkStmt->error);
                }
                
                $result = $checkStmt->get_result();

                if ($result->num_rows > 0) {
                    throw new Exception('Employee already selected');
                }
                
                $checkStmt->close();

                // Add new selected contribution
                // The selected_contributions table only stores: id, pagibig_no, id_number, user_id, date_added, status
                $sql = "INSERT INTO selected_contributions 
                        (user_id, pagibig_no, id_number) 
                        VALUES (?, ?, ?)";
                
                $pagibig_no = $employee['pagibig_no'] ?? '';
                $id_number = $employee['id_no'] ?? '';
                
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Prepare insert failed: ' . $conn->error);
                }
                
                $stmt->bind_param("iss", 
                    $userId,
                    $pagibig_no,
                    $id_number
                );
                
                if (!$stmt->execute()) {
                    throw new Exception('Insert execute failed: ' . $stmt->error);
                }
                
                $stmt->close();
                break;

            case 'remove':
                if (!isset($data['pagibig_no'])) {
                    throw new Exception('No Pag-IBIG number provided');
                }

                $userId = $_SESSION['user_id'];
                $pagibigNo = $data['pagibig_no'];

                $sql = "DELETE FROM selected_contributions WHERE user_id = ? AND pagibig_no = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Prepare delete failed: ' . $conn->error);
                }
                
                $stmt->bind_param("is", $userId, $pagibigNo);
                if (!$stmt->execute()) {
                    throw new Exception('Delete execute failed: ' . $stmt->error);
                }
                
                $stmt->close();
                break;

            default:
                throw new Exception('Invalid action');
        }

        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Operation completed successfully'
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

$conn->close();
?>
