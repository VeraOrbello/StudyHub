<?php
class Subject {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }

    public function addSubject($userId, $name, $code = '', $color = '#6B8E9C', $description = '') {
        $sql = "INSERT INTO subjects (user_id, name, code, color, description, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute([$userId, $name, $code, $color, $description])) {
            return $this->db->lastInsertId();
        }
        return false;
    }
    
    public function getUserSubjects($user_id) {
        $conn = $this->db->getConnection();
        
        $query = "SELECT * FROM subjects WHERE user_id = :user_id ORDER BY name";
        $stmt = $conn->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getSubject($id, $user_id) {
        $conn = $this->db->getConnection();
        
        $query = "SELECT * FROM subjects WHERE id = :id AND user_id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':id' => $id, ':user_id' => $user_id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        $conn = $this->db->getConnection();
        
        $query = "INSERT INTO subjects (user_id, name, professor, schedule, color, credit_hours) 
                  VALUES (:user_id, :name, :professor, :schedule, :color, :credit_hours)";
        $stmt = $conn->prepare($query);
        
        return $stmt->execute([
            ':user_id' => $data['user_id'],
            ':name' => $data['name'],
            ':professor' => $data['professor'],
            ':schedule' => $data['schedule'],
            ':color' => $data['color'],
            ':credit_hours' => $data['credit_hours']
        ]);
    }
    
    public function update($id, $user_id, $data) {
        $conn = $this->db->getConnection();
        
        $query = "UPDATE subjects SET name = :name, professor = :professor, 
                  schedule = :schedule, color = :color, credit_hours = :credit_hours
                  WHERE id = :id AND user_id = :user_id";
        $stmt = $conn->prepare($query);
        
        return $stmt->execute([
            ':name' => $data['name'],
            ':professor' => $data['professor'],
            ':schedule' => $data['schedule'],
            ':color' => $data['color'],
            ':credit_hours' => $data['credit_hours'],
            ':id' => $id,
            ':user_id' => $user_id
        ]);
    }
    
    public function delete($id, $user_id) {
        $conn = $this->db->getConnection();
        
        $query = "DELETE FROM subjects WHERE id = :id AND user_id = :user_id";
        $stmt = $conn->prepare($query);
        
        return $stmt->execute([':id' => $id, ':user_id' => $user_id]);
    }
}
?>