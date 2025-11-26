<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../database/Database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        $token = $_POST['token'];
        $newPassword = $_POST['newPassword'];
        
        // Validate password
        if (strlen($newPassword) < 8 || 
            !preg_match('/[A-Z]/', $newPassword) || 
            !preg_match('/[a-z]/', $newPassword) || 
            !preg_match('/[0-9]/', $newPassword) || 
            !preg_match('/[^A-Za-z0-9]/', $newPassword)) {
            
            echo json_encode([
                'status' => 'error',
                'message' => 'Password does not meet the requirements'
            ]);
            exit;
        }
        
        // Get token info
        $stmt = $conn->prepare("
            SELECT pr.user_id, pr.id as reset_id
            FROM password_resets pr
            WHERE pr.token = ? AND pr.used = 0 AND pr.expires_at > NOW()
        ");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid or expired reset token'
            ]);
            exit;
        }
        
        $resetInfo = $result->fetch_assoc();
        
        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $resetInfo['user_id']);
        
        if ($stmt->execute()) {
            // Mark token as used
            $stmt = $conn->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
            $stmt->bind_param("i", $resetInfo['reset_id']);
            $stmt->execute();
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Your password has been reset successfully. You can now login with your new password.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to update password'
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Password reset error: " . $e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => 'An error occurred while processing your request.'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}
