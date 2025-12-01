<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../database/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $month = isset($_POST['month']) ? trim($_POST['month']) : '';
    $year = isset($_POST['year']) ? intval($_POST['year']) : 0;
    $num_borrowers = isset($_POST['num_borrowers']) ? intval($_POST['num_borrowers']) : 0;
    $total_deducted_amount = isset($_POST['total_deducted_amount']) ? floatval($_POST['total_deducted_amount']) : 0;
    
    header('Content-Type: application/json');
    
    if (!$month || !$year) {
        echo json_encode(['status' => 'error', 'message' => 'Missing month or year']);
        exit;
    }
    
    try {
        // Create backup folder if not exists
        $backupFolder = __DIR__ . '/../../stl_backup_files/';
        if (!is_dir($backupFolder)) {
            if (!mkdir($backupFolder, 0755, true)) {
                throw new Exception('Failed to create backup folder');
            }
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload failed: ' . ($_FILES['file']['error'] ?? 'Unknown error'));
        }
        
        // Generate filename
        $filename = $month . '_' . $year . '_Stl.xls';
        $backupFilePath = $backupFolder . $filename;
        
        // Save to backup folder only
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $backupFilePath)) {
            throw new Exception('Failed to save backup file');
        }
        
        // Connect to database
        $conn = getConnection();
        if (!$conn) {
            throw new Exception('Database connection failed');
        }
        
        // Check if record already exists
        $checkQuery = "SELECT id FROM stl_summary WHERE month = ? AND year = ?";
        $checkStmt = $conn->prepare($checkQuery);
        if (!$checkStmt) {
            throw new Exception('Database prepare failed: ' . $conn->error);
        }
        
        $checkStmt->bind_param('si', $month, $year);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $recordExists = $result->num_rows > 0;
        $checkStmt->close();
        
        if ($recordExists) {
            // Update existing record
            $updateQuery = "
                UPDATE stl_summary 
                SET backup_file_path = ?,
                    num_borrowers = ?, 
                    total_deducted_amount = ?,
                    updated_date = CURRENT_TIMESTAMP
                WHERE month = ? AND year = ?
            ";
            $updateStmt = $conn->prepare($updateQuery);
            if (!$updateStmt) {
                throw new Exception('Database prepare failed: ' . $conn->error);
            }
            
            $updateStmt->bind_param('siisi', $backupFilePath, $num_borrowers, $total_deducted_amount, $month, $year);
            if (!$updateStmt->execute()) {
                throw new Exception('Database update failed: ' . $updateStmt->error);
            }
            $updateStmt->close();
        } else {
            // Insert new record
            $insertQuery = "
                INSERT INTO stl_summary (backup_file_path, month, year, num_borrowers, total_deducted_amount)
                VALUES (?, ?, ?, ?, ?)
            ";
            $insertStmt = $conn->prepare($insertQuery);
            if (!$insertStmt) {
                throw new Exception('Database prepare failed: ' . $conn->error);
            }
            
            $insertStmt->bind_param('ssidd', $backupFilePath, $month, $year, $num_borrowers, $total_deducted_amount);
            if (!$insertStmt->execute()) {
                throw new Exception('Database insert failed: ' . $insertStmt->error);
            }
            $insertStmt->close();
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Backup saved successfully. File ready for download.',
            'data' => [
                'filename' => $filename,
                'backup_path' => $backupFilePath
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Error saving STL Excel backup: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>



