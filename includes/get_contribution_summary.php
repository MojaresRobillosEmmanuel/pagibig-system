<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../database/db_connect.php';

try {
    $conn = getConnection();
    
    // Query from contribution_summary table
    $query = "
        SELECT 
            id,
            filename,
            month,
            year,
            num_contributors,
            total_deducted_amount,
            created_date
        FROM contribution_summary
        ORDER BY year DESC, FIELD(month, 'January', 'February', 'March', 'April', 'May', 'June', 
                                        'July', 'August', 'September', 'October', 'November', 'December') DESC
    ";
    
    $result = $conn->query($query);
    
    if (!$result) {
        echo json_encode(['status' => 'error', 'message' => 'Query failed: ' . $conn->error]);
        exit;
    }
    
    $summaryData = [];
    
    while ($row = $result->fetch_assoc()) {
        $summaryData[] = [
            'id' => $row['id'],
            'year_month' => $row['month'] . ' ' . $row['year'],
            'month_name' => $row['month'],
            'year' => $row['year'],
            'num_contributors' => intval($row['num_contributors']),
            'deducted_amount' => floatval($row['total_deducted_amount']),
            'filename' => $row['filename'],
            'created_date' => $row['created_date']
        ];
    }
    
    if (empty($summaryData)) {
        echo json_encode(['status' => 'success', 'data' => [], 'message' => 'No contribution summary records found']);
        exit;
    }
    
    echo json_encode(['status' => 'success', 'data' => $summaryData]);

} catch (Exception $e) {
    error_log("Error fetching contribution summary: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
?>
