<?php
class Database {
    private $host = 'localhost';
    private $username = 'root';
    private $password = '';
    private $database;
    private $conn;

    public function __construct($database = null) {
        $this->database = $database;
        $this->connect();
    }

    private function connect() {
        try {
            // Create connection without database first
            $this->conn = new mysqli($this->host, $this->username, $this->password);
            
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }

            // Create STL database if it doesn't exist
            if ($this->database === 'pagibig_stl') {
                $this->conn->query("CREATE DATABASE IF NOT EXISTS pagibig_stl");
            }
            
            // Create contributions database if it doesn't exist
            if ($this->database === 'pagibig_contributions') {
                $this->conn->query("CREATE DATABASE IF NOT EXISTS pagibig_contributions");
            }

            // Select the database
            if ($this->database) {
                $this->conn->select_db($this->database);
            }

            // Set character set
            $this->conn->set_charset("utf8mb4");

        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw $e;
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function closeConnection() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>
