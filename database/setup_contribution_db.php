<?php
/**
 * Run this script to create the contribution database and tables from
 * database/contribution_setup.sql.
 *
 * Usage (CLI):
 *   cd c:\xampp\htdocs\pagibig\database
 *   php setup_contribution_db.php
 *
 * Or open in browser:
 *   http://localhost/Pagibig/database/setup_contribution_db.php
 */

set_time_limit(0);

echo "<pre>";
try {
    // DB credentials - match db_connect.php
    $host = 'localhost';
    $user = 'root';
    $pass = '';

    // Path to SQL file
    $sqlFile = __DIR__ . DIRECTORY_SEPARATOR . 'contribution_setup.sql';

    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: $sqlFile");
    }

    $sql = file_get_contents($sqlFile);
    if ($sql === false || strlen(trim($sql)) === 0) {
        throw new Exception("SQL file is empty or unreadable: $sqlFile");
    }

    // Connect without selecting a database to allow CREATE DATABASE
    $mysqli = new mysqli($host, $user, $pass);
    if ($mysqli->connect_error) {
        throw new Exception('Connection error: ' . $mysqli->connect_error);
    }

    // Execute multi query
    if (!$mysqli->multi_query($sql)) {
        throw new Exception('Multi query failed: ' . $mysqli->error);
    }

    // Consume results to finish multi_query
    do {
        if ($res = $mysqli->store_result()) {
            $res->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());

    echo "Database and tables created/verified successfully.\n";
    echo "You can now use the Contribution system.\n";
    echo "\nVerification steps:\n";
    echo " - Open contrib.php in browser and try to register an employee.\n";
    echo " - Or test the database directly with test_contribution_employees.php.\n";

    $mysqli->close();
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
}
echo "</pre>";
?>