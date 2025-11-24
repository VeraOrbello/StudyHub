<?php
class Assignment {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function getUserAssignments($user_id) {
        $conn = $this->db->getConnection();
        
        $query = "SELECT a.*, s.name as subject_name 
                  FROM assignments a 
                  LEFT JOIN subjects s ON a.subject_id = s.id 
                  WHERE a.user_id = :user_id 
                  ORDER BY a.due_date ASC";
        $stmt = $conn->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUpcomingAssignments($user_id, $limit = 10) {
        $conn = $this->db->getConnection();
        
        $query = "SELECT a.*, s.name as subject_name 
                  FROM assignments a 
                  LEFT JOIN subjects s ON a.subject_id = s.id 
                  WHERE a.user_id = :user_id AND a.status != 'Completed'
                  ORDER BY a.due_date ASC 
                  LIMIT :limit";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getCompletedAssignments($user_id) {
        $conn = $this->db->getConnection();
        
        $query = "SELECT COUNT(*) as count FROM assignments 
                  WHERE user_id = :user_id AND status = 'Completed'";
        $stmt = $conn->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    public function getAssignmentStats($user_id) {
        $conn = $this->db->getConnection();
        
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress
                  FROM assignments 
                  WHERE user_id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getAssignment($id, $user_id) {
        $conn = $this->db->getConnection();
        
        $query = "SELECT a.*, s.name as subject_name 
                  FROM assignments a 
                  LEFT JOIN subjects s ON a.subject_id = s.id 
                  WHERE a.id = :id AND a.user_id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':id' => $id,
            ':user_id' => $user_id
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        $conn = $this->db->getConnection();
        
        $query = "INSERT INTO assignments (user_id, subject_id, title, description, due_date, priority, status) 
                  VALUES (:user_id, :subject_id, :title, :description, :due_date, :priority, :status)";
        $stmt = $conn->prepare($query);
        
        return $stmt->execute([
            ':user_id' => $data['user_id'],
            ':subject_id' => $data['subject_id'],
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':due_date' => $data['due_date'],
            ':priority' => $data['priority'],
            ':status' => $data['status']
        ]);
    }
    
    public function update($id, $user_id, $data) {
        $conn = $this->db->getConnection();
        
        $query = "UPDATE assignments SET 
                    subject_id = :subject_id,
                    title = :title,
                    description = :description,
                    due_date = :due_date,
                    priority = :priority,
                    status = :status
                  WHERE id = :id AND user_id = :user_id";
        $stmt = $conn->prepare($query);
        
        return $stmt->execute([
            ':subject_id' => $data['subject_id'],
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':due_date' => $data['due_date'],
            ':priority' => $data['priority'],
            ':status' => $data['status'],
            ':id' => $id,
            ':user_id' => $user_id
        ]);
    }
    
    public function updateStatus($id, $user_id, $status) {
        $conn = $this->db->getConnection();
        
        $query = "UPDATE assignments SET status = :status WHERE id = :id AND user_id = :user_id";
        $stmt = $conn->prepare($query);
        
        return $stmt->execute([
            ':status' => $status,
            ':id' => $id,
            ':user_id' => $user_id
        ]);
    }
    
    public function delete($id, $user_id) {
        $conn = $this->db->getConnection();
        
        $query = "DELETE FROM assignments WHERE id = :id AND user_id = :user_id";
        $stmt = $conn->prepare($query);
        
        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $user_id
        ]);
    }
    
    public function getOverdueAssignments($user_id) {
        $conn = $this->db->getConnection();
        
        $query = "SELECT a.*, s.name as subject_name 
                  FROM assignments a 
                  LEFT JOIN subjects s ON a.subject_id = s.id 
                  WHERE a.user_id = :user_id 
                  AND a.due_date < NOW() 
                  AND a.status != 'Completed' 
                  ORDER BY a.due_date ASC";
        $stmt = $conn->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAssignmentsBySubject($user_id, $subject_id) {
        $conn = $this->db->getConnection();
        
        $query = "SELECT a.*, s.name as subject_name 
                  FROM assignments a 
                  LEFT JOIN subjects s ON a.subject_id = s.id 
                  WHERE a.user_id = :user_id AND a.subject_id = :subject_id 
                  ORDER BY a.due_date ASC";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':user_id' => $user_id,
            ':subject_id' => $subject_id
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function searchAssignments($user_id, $search_term) {
        $conn = $this->db->getConnection();
        
        $query = "SELECT a.*, s.name as subject_name 
                  FROM assignments a 
                  LEFT JOIN subjects s ON a.subject_id = s.id 
                  WHERE a.user_id = :user_id 
                  AND (a.title LIKE :search OR a.description LIKE :search)
                  ORDER BY a.due_date ASC";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':user_id' => $user_id,
            ':search' => '%' . $search_term . '%'
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>