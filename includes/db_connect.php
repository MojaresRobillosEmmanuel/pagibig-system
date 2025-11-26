<?php
// Disable error display but keep error logging
ini_set('display_errors', 0);
error_reporting(E_ALL);

function getConnection() {
    static $conn;
    
    if ($conn === null) {
        $username = 'root';
        $password = '';
        $dbname = 'pagibig_db'; // Change this to your actual DB name
        $host = 'localhost';

        try {
            $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
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
?>
