<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../database/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $month = isset($_POST['month']) ? $_POST['month'] : '';
    $year = isset($_POST['year']) ? intval($_POST['year']) : 0;
    
    if (!$month || !$year) {
        echo json_encode(['status' => 'error', 'message' => 'Missing month or year']);
        exit;
    }
    
    try {
        $conn = getConnection();
        
        // Get backup file path from database
        $query = "SELECT backup_file_path FROM stl_summary WHERE month = ? AND year = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('si', $month, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        if (!$row || empty($row['backup_file_path'])) {
            echo json_encode(['status' => 'error', 'message' => 'Backup file not found in database']);
            exit;
        }
        
        $backupFilePath = $row['backup_file_path'];
        
        // Check if file exists
        if (!file_exists($backupFilePath)) {
            echo json_encode(['status' => 'error', 'message' => 'Backup file not found']);
            exit;
        }
        
        // Generate filename
        $filename = $month . '_' . $year . '_Stl.xls';
        
        // Download the file
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($backupFilePath));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        readfile($backupFilePath);
        exit;
        
    } catch (Exception $e) {
        error_log("Error downloading STL file: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>

