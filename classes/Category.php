<?php
class Category {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        try {
            $query = "INSERT INTO categories (name, description, parent_id) 
                     VALUES (:name, :description, :parent_id)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':parent_id', $data['parent_id']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getById($id) {
        try {
            $query = "SELECT c.*, p.name as parent_name 
                     FROM categories c 
                     LEFT JOIN categories p ON c.parent_id = p.id 
                     WHERE c.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update($id, $data) {
        try {
            $query = "UPDATE categories SET name = :name, description = :description, 
                     parent_id = :parent_id WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':parent_id', $data['parent_id']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete($id) {
        try {
            $check_query = "SELECT COUNT(*) as count FROM products WHERE category_id = :id";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(':id', $id);
            $check_stmt->execute();
            $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                return false;
            }

            $query = "DELETE FROM categories WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getAll($include_inactive = false) {
        try {
            $where_clause = $include_inactive ? "" : "WHERE is_active = 1";
            
            $query = "SELECT c.*, p.name as parent_name 
                     FROM categories c 
                     LEFT JOIN categories p ON c.parent_id = p.id 
                     $where_clause 
                     ORDER BY c.parent_id, c.name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getMainCategories() {
        try {
            $query = "SELECT * FROM categories WHERE parent_id IS NULL AND is_active = 1 ORDER BY name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getSubCategories($parent_id) {
        try {
            $query = "SELECT * FROM categories WHERE parent_id = :parent_id AND is_active = 1 ORDER BY name";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':parent_id', $parent_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getProductCount($id) {
        try {
            $query = "SELECT COUNT(*) as count FROM products WHERE category_id = :id AND is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (PDOException $e) {
            return 0;
        }
    }
}
?>