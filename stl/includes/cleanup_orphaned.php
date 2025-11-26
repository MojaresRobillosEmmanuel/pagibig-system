<?php
/**
 * Cleanup orphaned STL records
 * Remove records from selected_stl that don't have matching employees with system_type='stl'
 */

require_once __DIR__ . '/../../database/db_connect.php';

$conn = getConnection();

echo "=== CLEANUP ORPHANED STL RECORDS ===\n\n";

// Find orphaned records (in selected_stl but not in employees or not marked as STL)
echo "1. Finding orphaned records...\n";
$result = $conn->query("
    SELECT ss.id, ss.pagibig_no
    FROM selected_stl ss
    LEFT JOIN employees e ON e.pagibig_number = ss.pagibig_no
    WHERE e.pagibig_number IS NULL OR e.system_type != 'stl'
");

$orphaned = [];
while ($row = $result->fetch_assoc()) {
    $orphaned[] = $row;
    echo "   Found orphaned: " . $row['pagibig_no'] . " (ID: " . $row['id'] . ")\n";
}

echo "   Total orphaned records: " . count($orphaned) . "\n\n";

// Delete orphaned records
if (count($orphaned) > 0) {
    echo "2. Deleting orphaned records...\n";
    
    foreach ($orphaned as $record) {
        $pagibigNo = $conn->real_escape_string($record['pagibig_no']);
        $deleteResult = $conn->query("DELETE FROM selected_stl WHERE pagibig_no = '$pagibigNo'");
        
        if ($deleteResult) {
            echo "   ✓ Deleted: " . $record['pagibig_no'] . "\n";
        } else {
            echo "   ✗ Failed to delete: " . $record['pagibig_no'] . " - " . $conn->error . "\n";
        }
    }
    
    echo "\n3. Cleanup complete!\n";
} else {
    echo "2. No orphaned records found. Database is clean!\n";
}

// Show remaining valid records
echo "\n4. Remaining valid STL records:\n";
$result = $conn->query("
    SELECT ss.pagibig_no, e.first_name, e.last_name, e.system_type, e.status
    FROM selected_stl ss
    INNER JOIN employees e ON e.pagibig_number = ss.pagibig_no
    WHERE e.system_type = 'stl' AND e.status = 1
    ORDER BY ss.date_added DESC
");

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "   - " . $row['pagibig_no'] . " | " . $row['first_name'] . " " . $row['last_name'] . " | Type: " . $row['system_type'] . " | Status: " . $row['status'] . "\n";
    }
} else {
    echo "   No valid STL employees found\n";
}

echo "\n=== CLEANUP COMPLETE ===\n";

$conn->close();
?>
