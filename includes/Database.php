<?php
class Database {
    private $conn;
    private static $instance = null;

    private function __construct() {
        require_once __DIR__ . '/../database/db_connect.php';
        $this->conn = getConnection();
    }

    // Singleton pattern to ensure only one database connection
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // General query method with prepared statements
    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            
            if ($params) {
                $types = '';
                foreach ($params as $param) {
                    if (is_int($param)) {
                        $types .= 'i';
                    } elseif (is_float($param)) {
                        $types .= 'd';
                    } elseif (is_string($param)) {
                        $types .= 's';
                    } else {
                        $types .= 'b';
                    }
                }
                
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            return $stmt;
            
        } catch (Exception $e) {
            error_log("Database Error: " . $e->getMessage());
            throw new Exception("Database error occurred");
        }
    }

    // Fetch all results
    public function fetchAll($sql, $params = []) {
        try {
            $stmt = $this->query($sql, $params);
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Fetch Error: " . $e->getMessage());
            throw new Exception("Error fetching data");
        }
    }

    // Fetch single row
    public function fetchOne($sql, $params = []) {
        try {
            $stmt = $this->query($sql, $params);
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Fetch Error: " . $e->getMessage());
            throw new Exception("Error fetching data");
        }
    }

    // Insert data
    public function insert($table, $data) {
        try {
            $columns = implode(', ', array_keys($data));
            $values = implode(', ', array_fill(0, count($data), '?'));
            $sql = "INSERT INTO $table ($columns) VALUES ($values)";
            
            $stmt = $this->query($sql, array_values($data));
            return $this->conn->insert_id;
        } catch (Exception $e) {
            error_log("Insert Error: " . $e->getMessage());
            throw new Exception("Error inserting data");
        }
    }

    // Update data
    public function update($table, $data, $where, $whereParams = []) {
        try {
            $set = implode('=?, ', array_keys($data)) . '=?';
            $sql = "UPDATE $table SET $set WHERE $where";
            
            $params = array_merge(array_values($data), $whereParams);
            $this->query($sql, $params);
            return $this->conn->affected_rows;
        } catch (Exception $e) {
            error_log("Update Error: " . $e->getMessage());
            throw new Exception("Error updating data");
        }
    }

    // Delete data
    public function delete($table, $where, $params = []) {
        try {
            $sql = "DELETE FROM $table WHERE $where";
            $this->query($sql, $params);
            return $this->conn->affected_rows;
        } catch (Exception $e) {
            error_log("Delete Error: " . $e->getMessage());
            throw new Exception("Error deleting data");
        }
    }
}
