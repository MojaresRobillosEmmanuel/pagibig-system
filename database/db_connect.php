<?php
// Disable error display but keep error logging
ini_set('display_errors', 0);
error_reporting(E_ALL);

function getConnection() {
    static $conn;
    
    if ($conn === null) {
        $username = 'root';
        $password = '';
        $dbname = 'pagibig_db'; // Using the main pagibig_db database
        $host = 'localhost';

        try {
            $conn = new mysqli($host, $username, $password, $dbname);
            
            if ($conn->connect_error) {
                error_log("Connection failed: " . $conn->connect_error);
                throw new Exception("Database connection failed");
            }

            // Set charset to utf8
            $conn->set_charset("utf8");
            
        } catch (Exception $e) {
            error_log("Connection failed: " . $e->getMessage());
            
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
    
    return $conn;
}

// Function to get STL database connection
function getSTLConnection() {
    static $stlConn;
    
    if ($stlConn === null) {
        $username = 'root';
        $password = '';
        $dbname = 'pagibig_db'; // Using pagibig_db for STL as well
        $host = 'localhost';

        try {
            $stlConn = new mysqli($host, $username, $password, $dbname);
            
            if ($stlConn->connect_error) {
                error_log("STL Connection failed: " . $stlConn->connect_error);
                throw new Exception("STL Database connection failed");
            }

            // Set charset to utf8
            $stlConn->set_charset("utf8");
            
        } catch (Exception $e) {
            error_log("STL Connection failed: " . $e->getMessage());
            
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
    
    return $stlConn;
}