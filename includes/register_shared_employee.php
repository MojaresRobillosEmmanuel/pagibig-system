<?php
session_start();
require_once 'Database.php';
require_once 'Response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Invalid request method');
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Get POST data
    $pagibigNumber = $_POST['pagibig_number'];
    $idNumber = $_POST['id_number'];
    $lastName = strtoupper($_POST['last_name']);
    $firstName = strtoupper($_POST['first_name']);
    $middleName = strtoupper($_POST['middle_name'] ?? '');
    $tin = $_POST['tin'] ?? '';
    $birthdate = $_POST['birthdate'];
    $module = $_POST['module'] ?? ''; // 'stl' or 'contribution'

    // Begin transaction
    $conn->begin_transaction();

    // Check if employee already exists
    $stmt = $conn->prepare("SELECT pagibig_number FROM employees WHERE pagibig_number = ?");
    $stmt->bind_param("s", $pagibigNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Insert into employees table if not exists
        $stmt = $conn->prepare("INSERT INTO employees (pagibig_number, id_number, last_name, first_name, middle_name, tin, birthdate, active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("sssssss", $pagibigNumber, $idNumber, $lastName, $firstName, $middleName, $tin, $birthdate);
        $stmt->execute();
    }

    // Insert into respective module table
    if ($module === 'stl') {
        // Check if already in STL
        $stmt = $conn->prepare("SELECT pagibig_number FROM stl WHERE pagibig_number = ?");
        $stmt->bind_param("s", $pagibigNumber);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            $stmt = $conn->prepare("INSERT INTO stl (pagibig_number) VALUES (?)");
            $stmt->bind_param("s", $pagibigNumber);
            $stmt->execute();
        }
    } elseif ($module === 'contribution') {
        // Check if already in contributions
        $stmt = $conn->prepare("SELECT pagibig_number FROM contributions WHERE pagibig_number = ?");
        $stmt->bind_param("s", $pagibigNumber);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            $stmt = $conn->prepare("INSERT INTO contributions (pagibig_number, ee_share, er_share) VALUES (?, ?, ?)");
            $eeShare = $_POST['ee_share'] ?? 0;
            $erShare = $_POST['er_share'] ?? 0;
            $stmt->bind_param("sdd", $pagibigNumber, $eeShare, $erShare);
            $stmt->execute();
        }
    }

    // Commit transaction
    $conn->commit();

    Response::success('Employee registered successfully');

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    Response::error('Registration failed: ' . $e->getMessage());
}
?>
