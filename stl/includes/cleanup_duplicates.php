<?php
/**
 * STL Duplicate Cleanup Script
 * Removes duplicate entries from selected_stl table
 * Keeps the most recent entry (by date_added) for each pagibig_no
 */

session_start();
header('Content-Type: application/json');

// Check if user is admin or authorized
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require_once __DIR__ . '/../../database/db_connect.php';

try {
    $conn = getConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Step 1: Find duplicates (multiple records with same pagibig_no)
    $findDupQuery = "
        SELECT pagibig_no, COUNT(*) as count, GROUP_CONCAT(id) as ids
        FROM selected_stl
        GROUP BY pagibig_no
        HAVING count > 1
    ";
    
    $dupResult = $conn->query($findDupQuery);
    if (!$dupResult) {
        throw new Exception('Failed to find duplicates: ' . $conn->error);
    }

    $duplicatesFound = $dupResult->num_rows;
    $deletedCount = 0;

    if ($duplicatesFound > 0) {
        // Step 2: For each duplicate, keep only the most recent (latest date_added)
        while ($dupRow = $dupResult->fetch_assoc()) {
            $pagibigNo = $conn->real_escape_string($dupRow['pagibig_no']);
            
            // Find the ID of the most recent entry for this pagibig_no
            $keepQuery = "
                SELECT id FROM selected_stl
                WHERE pagibig_no = '$pagibigNo'
                ORDER BY date_added DESC, id DESC
                LIMIT 1
            ";
            
            $keepResult = $conn->query($keepQuery);
            if (!$keepResult) {
                throw new Exception('Failed to find entry to keep: ' . $conn->error);
            }

            $keepRow = $keepResult->fetch_assoc();
            $keepId = $keepRow['id'];

            // Delete all other entries with the same pagibig_no
            $deleteQuery = "
                DELETE FROM selected_stl
                WHERE pagibig_no = '$pagibigNo' AND id != $keepId
            ";
            
            if (!$conn->query($deleteQuery)) {
                throw new Exception('Failed to delete duplicates: ' . $conn->error);
            }

            $deletedCount += $conn->affected_rows;
        }
    }

    // Step 3: Fix UNIQUE constraint if needed
    // Check if there are still any violations (should be none after cleanup)
    $checkQuery = "
        SELECT pagibig_no, COUNT(*) as count
        FROM selected_stl
        GROUP BY pagibig_no
        HAVING count > 1
    ";
    
    $checkResult = $conn->query($checkQuery);
    $violationsRemain = $checkResult->num_rows;

    $conn->close();

    echo json_encode([
        'success' => true,
        'message' => "Cleanup completed successfully",
        'results' => [
            'duplicates_found' => $duplicatesFound,
            'records_deleted' => $deletedCount,
            'violations_remaining' => $violationsRemain
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    error_log('Error in stl/includes/cleanup_duplicates.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
