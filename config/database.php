<?php
// config/database.php
class Database {
    private $host = 'localhost';
    private $db_name = 'kian_varna_db';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                                $this->username, $this->password);
            $this->conn->exec("set names utf8mb4");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "خطا در اتصال به پایگاه داده: " . $e->getMessage();
        }
        return $this->conn;
    }

    public function getConnection() {
        return $this->connect();
    }
}
?>