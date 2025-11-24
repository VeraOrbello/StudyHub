<?php
class Task {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function getUserTasks($user_id) {
        $conn = $this->db->getConnection();
        
        $query = "SELECT * FROM org_tasks WHERE user_id = :user_id ORDER BY due_date ASC";
        $stmt = $conn->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUpcomingTasks($user_id, $limit = 10) {
        $conn = $this->db->getConnection();
        
        $query = "SELECT * FROM org_tasks 
                  WHERE user_id = :user_id AND status = 'Pending'
                  ORDER BY due_date ASC 
                  LIMIT :limit";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        $conn = $this->db->getConnection();
        
        $query = "INSERT INTO org_tasks (user_id, title, description, due_date, category, status) 
                  VALUES (:user_id, :title, :description, :due_date, :category, :status)";
        $stmt = $conn->prepare($query);
        
        return $stmt->execute([
            ':user_id' => $data['user_id'],
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':due_date' => $data['due_date'],
            ':category' => $data['category'],
            ':status' => $data['status']
        ]);
    }
    
    public function updateStatus($id, $user_id, $status) {
        $conn = $this->db->getConnection();
        
        $query = "UPDATE org_tasks SET status = :status WHERE id = :id AND user_id = :user_id";
        $stmt = $conn->prepare($query);
        
        return $stmt->execute([
            ':status' => $status,
            ':id' => $id,
            ':user_id' => $user_id
        ]);
    }
}
?>