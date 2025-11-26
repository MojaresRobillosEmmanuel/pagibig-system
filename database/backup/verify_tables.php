<?php
require_once '../includes/db_connect.php';

// Check if the users table exists and its structure
$sql = "DESCRIBE users";
try {
    $result = $conn->query($sql);
    if ($result) {
        echo "Users table structure:\n";
        while ($row = $result->fetch_assoc()) {
            echo "{$row['Field']} - {$row['Type']}\n";
        }
    } else {
        echo "Error: Users table does not exist";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
