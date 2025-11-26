<?php
// Database connection utility class
class DatabaseConnection {
    private static $conn = null;
    private static $stlConn = null;

    public static function getPDO() {
        if (self::$conn === null) {
            $username = 'root';
            $password = '';
            $dbname = 'pagibig_db';
            $host = 'localhost';

            try {
                self::$conn = new PDO(
                    "mysql:host=$host;dbname=$dbname;charset=utf8",
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                error_log("PDO Connection failed: " . $e->getMessage());
                throw $e;
            }
        }
        return self::$conn;
    }

    public static function getSTLPDO() {
        if (self::$stlConn === null) {
            $username = 'root';
            $password = '';
            $dbname = 'pagibig_stl';
            $host = 'localhost';

            try {
                self::$stlConn = new PDO(
                    "mysql:host=$host;dbname=$dbname;charset=utf8",
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                error_log("STL PDO Connection failed: " . $e->getMessage());
                throw $e;
            }
        }
        return self::$stlConn;
    }
}