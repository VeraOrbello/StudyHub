<?php
class User {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function register($data) {
        $conn = $this->db->getConnection();
        
        // Check if user exists
        $checkQuery = "SELECT id FROM users WHERE email = :email OR username = :username";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->execute([
            ':email' => $data['email'],
            ':username' => $data['username']
        ]);
        
        if ($checkStmt->rowCount() > 0) {
            return false;
        }
        
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $query = "INSERT INTO users (username, email, password_hash, first_name, last_name) 
                  VALUES (:username, :email, :password, :first_name, :last_name)";
        $stmt = $conn->prepare($query);
        
        return $stmt->execute([
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':password' => $hashedPassword,
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name']
        ]);
    }
    
    public function login($username, $password) {
        $conn = $this->db->getConnection();
        
        $query = "SELECT * FROM users WHERE username = :username OR email = :username";
        $stmt = $conn->prepare($query);
        $stmt->execute([':username' => $username]);
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        
        return false;
    }
    
    public function getUserById($id) {
        $conn = $this->db->getConnection();
        
        $query = "SELECT id, username, email, first_name, last_name, created_at 
                  FROM users WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>