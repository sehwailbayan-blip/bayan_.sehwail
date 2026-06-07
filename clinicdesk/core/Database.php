<?php
require_once __DIR__ . '/../config/database.php';

class Database {
    private static ?Database $instance = null;
    private mysqli $conn;

    private function __construct() {
        mysqli_report(MYSQLI_REPORT_OFF);
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            throw new RuntimeException('Database connection failed. Please try again later.');
        }
        $this->conn->set_charset(DB_CHARSET);
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function query(string $sql, string $types = '', array $params = []): mixed {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Query preparation failed.');
        }
        if ($types && $params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result === false && $this->conn->errno === 0) {
            return true;
        }
        return $result;
    }

    public function lastInsertId(): int {
        return $this->conn->insert_id;
    }

    public function errno(): int {
        return $this->conn->errno;
    }
}
