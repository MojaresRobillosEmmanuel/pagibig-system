<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

class Database {
    private $host;
    private $username;
    private $password;
    private $database;
    private $conn;

    public function __construct() {
        $this->host = $_ENV['DB_HOST'];
        $this->username = $_ENV['DB_USER'];
        $this->password = $_ENV['DB_PASSWORD'];
        $this->database = $_ENV['DB_NAME'];
    }

    public function getConnection() {
        try {
            if ($this->conn === null) {
                $this->conn = new mysqli(
                    $this->host,
                    $this->username,
                    $this->password,
                    $this->database
                );

                if ($this->conn->connect_error) {
                    throw new Exception("Connection failed: " . $this->conn->connect_error);
                }

                $this->conn->set_charset("utf8mb4");
            }
            return $this->conn;
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }

    public function closeConnection() {
        if ($this->conn !== null) {
            $this->conn->close();
            $this->conn = null;
        }
    }
}
