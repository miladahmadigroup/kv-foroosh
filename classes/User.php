<?php
class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        try {
            $query = "INSERT INTO users (full_name, username, mobile, email, country, province, 
                     user_role, customer_type, password_hash) 
                     VALUES (:full_name, :username, :mobile, :email, :country, :province, 
                     :user_role, :customer_type, :password_hash)";
            
            $stmt = $this->conn->prepare($query);
            
            $password_hash = generate_password_hash($data['password']);
            
            $stmt->bindParam(':full_name', $data['full_name']);
            $stmt->bindParam(':username', $data['username']);
            $stmt->bindParam(':mobile', $data['mobile']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':country', $data['country']);
            $stmt->bindParam(':province', $data['province']);
            $stmt->bindParam(':user_role', $data['user_role']);
            $stmt->bindParam(':customer_type', $data['customer_type']);
            $stmt->bindParam(':password_hash', $password_hash);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function login($username, $password) {
        try {
            $query = "SELECT * FROM users WHERE username = :username AND is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (verify_password($password, $user['password_hash'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['user_role'] = $user['user_role'];
                    $_SESSION['customer_type'] = $user['customer_type'];
                    return true;
                }
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
        return true;
    }

    public function getById($id) {
        try {
            $query = "SELECT * FROM users WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getByUsername($username) {
        try {
            $query = "SELECT * FROM users WHERE username = :username";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update($id, $data) {
        try {
            $fields = [];
            $params = [':id' => $id];
            
            foreach ($data as $key => $value) {
                if ($key == 'password' && !empty($value)) {
                    $fields[] = "password_hash = :password_hash";
                    $params[':password_hash'] = generate_password_hash($value);
                } elseif ($key != 'password') {
                    $fields[] = "$key = :$key";
                    $params[":$key"] = $value;
                }
            }
            
            if (empty($fields)) return false;
            
            $query = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete($id) {
        try {
            $query = "DELETE FROM users WHERE id = :id AND user_role != 'system_admin'";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getAll($limit = null, $offset = 0, $search = '') {
        try {
            $where_clause = "";
            $params = [];
            
            if (!empty($search)) {
                $where_clause = "WHERE full_name LIKE :search OR username LIKE :search OR mobile LIKE :search";
                $params[':search'] = "%$search%";
            }
            
            $limit_clause = "";
            if ($limit) {
                $limit_clause = "LIMIT :offset, :limit";
                $params[':offset'] = $offset;
                $params[':limit'] = $limit;
            }
            
            $query = "SELECT * FROM users $where_clause ORDER BY created_at DESC $limit_clause";
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

    public function count($search = '') {
        try {
            $where_clause = "";
            $params = [];
            
            if (!empty($search)) {
                $where_clause = "WHERE full_name LIKE :search OR username LIKE :search OR mobile LIKE :search";
                $params[':search'] = "%$search%";
            }
            
            $query = "SELECT COUNT(*) as total FROM users $where_clause";
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

    public function setResetToken($email) {
        try {
            $token = generate_reset_token();
            $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            $query = "UPDATE users SET reset_token = :token, reset_token_expiry = :expiry WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':expiry', $expiry);
            $stmt->bindParam(':email', $email);
            
            if ($stmt->execute()) {
                return $token;
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function resetPassword($token, $new_password) {
        try {
            $query = "SELECT * FROM users WHERE reset_token = :token AND reset_token_expiry > NOW()";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            
            if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $password_hash = generate_password_hash($new_password);
                
                $update_query = "UPDATE users SET password_hash = :password_hash, reset_token = NULL, reset_token_expiry = NULL WHERE id = :id";
                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->bindParam(':password_hash', $password_hash);
                $update_stmt->bindParam(':id', $user['id']);
                
                return $update_stmt->execute();
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function usernameExists($username, $exclude_id = null) {
        try {
            $query = "SELECT id FROM users WHERE username = :username";
            $params = [':username' => $username];
            
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
}
?>