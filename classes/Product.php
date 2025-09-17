<?php
class Product {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data, $images = []) {
        try {
            $this->conn->beginTransaction();

            $query = "INSERT INTO products (name, code, category_id, description, main_image, video_url,
                     price_representative, price_partner, price_expert, price_consumer) 
                     VALUES (:name, :code, :category_id, :description, :main_image, :video_url,
                     :price_representative, :price_partner, :price_expert, :price_consumer)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':code', $data['code']);
            $stmt->bindParam(':category_id', $data['category_id']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':main_image', $data['main_image']);
            $stmt->bindParam(':video_url', $data['video_url']);
            $stmt->bindParam(':price_representative', $data['price_representative']);
            $stmt->bindParam(':price_partner', $data['price_partner']);
            $stmt->bindParam(':price_expert', $data['price_expert']);
            $stmt->bindParam(':price_consumer', $data['price_consumer']);
            
            $stmt->execute();
            $product_id = $this->conn->lastInsertId();

            if (!empty($images)) {
                $this->addImages($product_id, $images);
            }

            $this->conn->commit();
            return $product_id;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function getById($id) {
        try {
            $query = "SELECT p.*, c.name as category_name 
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     WHERE p.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($product) {
                $product['images'] = $this->getImages($id);
            }
            
            return $product;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update($id, $data, $new_images = []) {
        try {
            $this->conn->beginTransaction();

            $fields = [];
            $params = [':id' => $id];
            
            foreach ($data as $key => $value) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
            
            $query = "UPDATE products SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);

            if (!empty($new_images)) {
                $this->addImages($id, $new_images);
            }

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function delete($id) {
        try {
            $this->conn->beginTransaction();

            $images = $this->getImages($id);
            foreach ($images as $image) {
                delete_file($image['image_path']);
            }

            $product = $this->getById($id);
            if ($product && $product['main_image']) {
                delete_file($product['main_image']);
            }

            $this->conn->prepare("DELETE FROM product_images WHERE product_id = :id")->execute([':id' => $id]);
            $this->conn->prepare("DELETE FROM products WHERE id = :id")->execute([':id' => $id]);

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function getAll($limit = null, $offset = 0, $search = '', $category_id = null) {
        try {
            $where_conditions = ["p.is_active = 1"];
            $params = [];
            
            if (!empty($search)) {
                $where_conditions[] = "(p.name LIKE :search OR p.code LIKE :search OR p.description LIKE :search)";
                $params[':search'] = "%$search%";
            }
            
            if ($category_id) {
                $where_conditions[] = "p.category_id = :category_id";
                $params[':category_id'] = $category_id;
            }
            
            $where_clause = "WHERE " . implode(' AND ', $where_conditions);
            
            $limit_clause = "";
            if ($limit) {
                $limit_clause = "LIMIT :offset, :limit";
                $params[':offset'] = $offset;
                $params[':limit'] = $limit;
            }
            
            $query = "SELECT p.*, c.name as category_name 
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     $where_clause 
                     ORDER BY p.created_at DESC 
                     $limit_clause";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                if ($key == ':offset' || $key == ':limit') {
                    $stmt->bindValue($key, (int)$value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function count($search = '', $category_id = null) {
        try {
            $where_conditions = ["is_active = 1"];
            $params = [];
            
            if (!empty($search)) {
                $where_conditions[] = "(name LIKE :search OR code LIKE :search OR description LIKE :search)";
                $params[':search'] = "%$search%";
            }
            
            if ($category_id) {
                $where_conditions[] = "category_id = :category_id";
                $params[':category_id'] = $category_id;
            }
            
            $where_clause = "WHERE " . implode(' AND ', $where_conditions);
            
            $query = "SELECT COUNT(*) as total FROM products $where_clause";
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function addImages($product_id, $images) {
        try {
            foreach ($images as $index => $image_path) {
                $query = "INSERT INTO product_images (product_id, image_path, sort_order) 
                         VALUES (:product_id, :image_path, :sort_order)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':product_id', $product_id);
                $stmt->bindParam(':image_path', $image_path);
                $stmt->bindParam(':sort_order', $index);
                $stmt->execute();
            }
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getImages($product_id) {
        try {
            $query = "SELECT * FROM product_images WHERE product_id = :product_id ORDER BY sort_order";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function deleteImage($image_id) {
        try {
            $query = "SELECT * FROM product_images WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $image_id);
            $stmt->execute();
            $image = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($image) {
                delete_file($image['image_path']);
                
                $delete_query = "DELETE FROM product_images WHERE id = :id";
                $delete_stmt = $this->conn->prepare($delete_query);
                $delete_stmt->bindParam(':id', $image_id);
                return $delete_stmt->execute();
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function toggleStatus($id) {
        try {
            $query = "UPDATE products SET is_active = !is_active WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function codeExists($code, $exclude_id = null) {
        try {
            $query = "SELECT id FROM products WHERE code = :code";
            $params = [':code' => $code];
            
            if ($exclude_id) {
                $query .= " AND id != :id";
                $params[':id'] = $exclude_id;
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getPrice($product_id, $customer_type) {
        try {
            $price_field = 'price_consumer';
            
            switch ($customer_type) {
                case 'representative':
                    $price_field = 'price_representative';
                    break;
                case 'partner':
                    $price_field = 'price_partner';
                    break;
                case 'expert':
                    $price_field = 'price_expert';
                    break;
                case 'consumer':
                default:
                    $price_field = 'price_consumer';
                    break;
            }
            
            $query = "SELECT $price_field as price FROM products WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $product_id);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['price'] : 0;
        } catch (PDOException $e) {
            return 0;
        }
    }
}
?>