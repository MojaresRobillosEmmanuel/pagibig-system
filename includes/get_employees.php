<?php
require_once 'config.php';

// Fetch active employees
$query = "SELECT id, pagibig_number, id_number, last_name, first_name, middle_name, ee, er, tin, birthdate 
          FROM employees 
          WHERE status = 'active' 
          ORDER BY last_name, first_name";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$employees = array();
while ($row = mysqli_fetch_assoc($result)) {
    $employees[] = array(
        'id' => $row['id'],
        'pagibig' => $row['pagibig_number'],
        'employee_id' => $row['id_number'],
        'lastname' => $row['last_name'],
        'firstname' => $row['first_name'],
        'middlename' => $row['middle_name'] ?? '',
        'ee' => $row['ee'],
        'er' => $row['er'],
        'tin' => $row['tin'] ?? '',
        'birthdate' => $row['birthdate']
    );
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($employees);
?>
