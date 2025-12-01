<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../database/db_connect.php';

try {
    $conn = getConnection();
    $generatedDir = __DIR__ . '/../../generated excel files';
    
    if (!is_dir($generatedDir)) {
        echo json_encode(['status' => 'error', 'message' => 'Generated files directory not found']);
        exit;
    }
    
    $files = array_diff(scandir($generatedDir), ['.', '..']);
    $populated = 0;
    $errors = [];
    
    foreach ($files as $file) {
        // Skip non-excel files
        if (!preg_match('/\.(xls|xlsx)$/i', $file)) {
            continue;
        }
        
        // Parse filename pattern: Month_Year_Stl.xls or month_year_stl.xls
        if (!preg_match('/^([a-zA-Z]+)_(\d{4})_[Ss]tl\.(xls|xlsx)$/i', $file, $matches)) {
            $errors[] = "Cannot parse filename: $file";
            continue;
        }
        
        $month = ucfirst(strtolower($matches[1]));
        $year = intval($matches[2]);
        
        // Validate month
        $validMonths = ['January', 'February', 'March', 'April', 'May', 'June', 
                       'July', 'August', 'September', 'October', 'November', 'December'];
        if (!in_array($month, $validMonths)) {
            $errors[] = "Invalid month in filename: $file";
            continue;
        }
        
        // Check if record already exists
        $checkQuery = "SELECT id FROM stl_summary WHERE month = ? AND year = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param('si', $month, $year);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            $checkStmt->close();
            continue; // Record already exists
        }
        $checkStmt->close();
        
        // Insert the new record with 0 values (actual data would come from Excel parsing if needed)
        $insertQuery = "
            INSERT INTO stl_summary (filename, backup_file_path, month, year, num_borrowers, total_deducted_amount)
            VALUES (?, ?, ?, ?, 0, 0)
        ";
        
        $backupPath = __DIR__ . '/../../stl_backup_files/' . $file;
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param('sssi', $file, $backupPath, $month, $year);
        
        if ($insertStmt->execute()) {
            $populated++;
        } else {
            $errors[] = "Error inserting $file: " . $insertStmt->error;
        }
        $insertStmt->close();
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => "Populated $populated records",
        'errors' => $errors,
        'data' => [
            'populated' => $populated,
            'errors' => count($errors)
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error populating STL summary: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
?>
