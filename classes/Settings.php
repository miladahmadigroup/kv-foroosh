<?php
// classes/Settings.php
class Settings {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // دریافت تنظیمات
    public function get($key) {
        try {
            $query = "SELECT setting_value FROM settings WHERE setting_key = :key";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':key', $key);
            $stmt->execute();
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return $row['setting_value'];
            }
            return null;
        } catch (PDOException $e) {
            return null;
        }
    }

    // تنظیم مقدار
    public function set($key, $value, $description = '') {
        try {
            $query = "INSERT INTO settings (setting_key, setting_value, description) 
                     VALUES (:key, :value, :description) 
                     ON DUPLICATE KEY UPDATE setting_value = :value, description = :description";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':key', $key);
            $stmt->bindParam(':value', $value);
            $stmt->bindParam(':description', $description);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    // دریافت همه تنظیمات
    public function getAll() {
        try {
            $query = "SELECT * FROM settings ORDER BY setting_key";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // حذف تنظیمات
    public function delete($key) {
        try {
            $query = "DELETE FROM settings WHERE setting_key = :key";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':key', $key);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>