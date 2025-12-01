<?php
require_once 'database/db_connect.php';
$conn = getConnection();

// Check active records
$result = $conn->query('SELECT COUNT(*) as cnt FROM selected_stl WHERE is_active = 1');
$row = $result->fetch_assoc();
echo 'Active STL records: ' . $row['cnt'] . "\n";

// Check summary records
$result = $conn->query('SELECT month, year, num_borrowers, total_deducted_amount FROM stl_summary');
while ($row = $result->fetch_assoc()) {
    echo 'Summary: ' . $row['month'] . ' ' . $row['year'] . ' - ' . $row['num_borrowers'] . ' borrowers' . "\n";
}
?>
