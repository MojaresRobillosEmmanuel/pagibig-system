<?php
// Disable error display but keep error logging
ini_set('display_errors', 0);
error_reporting(E_ALL);

function getPDOConnection() {
    static $pdo;
    
    if ($pdo === null) {
        $username = 'root';
        $password = '';
        $dbname = 'pagibig_db'; // Default database for employee data
        $host = 'localhost';

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            error_log("PDO Connection failed: " . $e->getMessage());
            
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Database connection failed',
                    'code' => 'DB_ERROR'
                ]);
                exit;
            } else {
                die("Database connection error. Please try again later.");
            }
        }
    }
    
    return $pdo;
}

// Function to get STL database PDO connection
function getPDOSTLConnection() {
    static $stlPdo;
    
    if ($stlPdo === null) {
        $username = 'root';
        $password = '';
        $dbname = 'pagibig_db'; // Using pagibig_db for STL as well
        $host = 'localhost';

        try {
            $stlPdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            error_log("STL PDO Connection failed: " . $e->getMessage());
            
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'STL Database connection failed',
                    'code' => 'DB_ERROR'
                ]);
                exit;
            } else {
                die("STL Database connection error. Please try again later.");
            }
        }
    }
    
    return $stlPdo;
}