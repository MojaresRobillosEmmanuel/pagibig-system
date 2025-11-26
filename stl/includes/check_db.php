<?php
require_once __DIR__ . '/../../database/db_connect.php';

$conn = getConnection();

echo "=== STL DATABASE DIAGNOSTIC ===\n\n";

// 1. Check selected_stl table
echo "1. Total records in selected_stl table:\n";
$result = $conn->query("SELECT COUNT(*) as total FROM selected_stl");
$row = $result->fetch_assoc();
echo "   Count: " . $row['total'] . "\n\n";

// 2. Show all records from selected_stl
echo "2. All records from selected_stl:\n";
$result = $conn->query("DESCRIBE selected_stl");
echo "   Table columns: ";
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . "), ";
}
echo "\n\n";

$result = $conn->query("SELECT * FROM selected_stl ORDER BY date_added DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "   - Pagibig: " . $row['pagibig_no'] . " | EE: " . $row['ee'] . " | ER: " . $row['er'] . " | Active: " . $row['is_active'] . "\n";
    }
} else {
    echo "   Query error or no records found\n";
}
echo "\n";

// 3. Check STL employees in employees table
echo "3. STL employees in employees table:\n";
$result = $conn->query("SELECT COUNT(*) as total FROM employees WHERE system_type = 'stl'");
$row = $result->fetch_assoc();
echo "   Total STL employees: " . $row['total'] . "\n";

// 4. Check active STL employees
echo "4. Active STL employees:\n";
$result = $conn->query("SELECT COUNT(*) as total FROM employees WHERE system_type = 'stl' AND status = 1");
$row = $result->fetch_assoc();
echo "   Active count: " . $row['total'] . "\n\n";

// 5. Check JOIN result (what the query actually returns)
echo "5. Testing the JOIN query (selected_stl + active STL employees):\n";
$result = $conn->query("SELECT COUNT(*) as total FROM selected_stl ss
                        INNER JOIN employees e ON e.pagibig_number = ss.pagibig_no
                        WHERE e.status = 1 AND e.system_type = 'stl'");
$row = $result->fetch_assoc();
echo "   Records found in JOIN: " . $row['total'] . "\n\n";

// 6. Show details of the JOIN query
echo "6. Sample of JOIN query results (first 5):\n";
$result = $conn->query("SELECT 
    e.pagibig_number,
    e.last_name,
    e.first_name,
    e.status,
    e.system_type,
    ss.ee,
    ss.er
FROM selected_stl ss
INNER JOIN employees e ON e.pagibig_number = ss.pagibig_no
WHERE e.status = 1 AND e.system_type = 'stl'
LIMIT 5");

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "   - " . $row['first_name'] . " " . $row['last_name'] . " | Status: " . $row['status'] . " | Type: " . $row['system_type'] . " | EE: " . $row['ee'] . " | ER: " . $row['er'] . "\n";
    }
} else {
    echo "   No results from JOIN\n";
}

echo "\n=== END DIAGNOSTIC ===\n";

$conn->close();
?>
