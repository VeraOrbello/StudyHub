<?php
class Note {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // REMOVED DUPLICATE METHOD - KEEP ONLY ONE VERSION
    public function getNotesCountBySubject($user_id, $subject_id) {
        $conn = $this->db->getConnection();
        
        $query = "SELECT COUNT(*) as count FROM notes WHERE user_id = :user_id AND subject_id = :subject_id";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':user_id' => $user_id,
            ':subject_id' => $subject_id
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    public function getSubjectNotes($user_id, $subject_id) {
        $conn = $this->db->getConnection();
        
        $query = "SELECT * FROM notes WHERE user_id = :user_id AND subject_id = :subject_id ORDER BY created_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':user_id' => $user_id,
            ':subject_id' => $subject_id
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        $conn = $this->db->getConnection();
        
        $query = "INSERT INTO notes (user_id, subject_id, title, content) 
                  VALUES (:user_id, :subject_id, :title, :content)";
        $stmt = $conn->prepare($query);
        
        return $stmt->execute([
            ':user_id' => $data['user_id'],
            ':subject_id' => $data['subject_id'],
            ':title' => $data['title'],
            ':content' => $data['content']
        ]);
    }
    
    public function getNote($id, $user_id) {
        $conn = $this->db->getConnection();
        
        $query = "SELECT n.*, s.name as subject_name 
                  FROM notes n 
                  JOIN subjects s ON n.subject_id = s.id 
                  WHERE n.id = :id AND n.user_id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':id' => $id,
            ':user_id' => $user_id
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function update($id, $user_id, $data) {
        $conn = $this->db->getConnection();
        
        $query = "UPDATE notes SET title = :title, content = :content WHERE id = :id AND user_id = :user_id";
        $stmt = $conn->prepare($query);
        
        return $stmt->execute([
            ':title' => $data['title'],
            ':content' => $data['content'],
            ':id' => $id,
            ':user_id' => $user_id
        ]);
    }
    
    public function delete($id, $user_id) {
        $conn = $this->db->getConnection();
        
        $query = "DELETE FROM notes WHERE id = :id AND user_id = :user_id";
        $stmt = $conn->prepare($query);
        
        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $user_id
        ]);
    }
}
?>