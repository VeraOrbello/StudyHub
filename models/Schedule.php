<?php
class Schedule {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    
    public function getUserSchedule($user_id) {
        $conn = $this->db->getConnection();
        
        $query = "SELECT cs.*, s.name as subject_name, s.color as subject_color, s.professor 
                  FROM class_schedule cs 
                  JOIN subjects s ON cs.subject_id = s.id 
                  WHERE cs.user_id = :user_id 
                  ORDER BY 
                    FIELD(cs.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                    cs.start_time";
        $stmt = $conn->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTodaysSchedule($user_id) {
        $conn = $this->db->getConnection();
        $today = date('l'); // Gets current day name (Monday, Tuesday, etc.)
        
        $query = "SELECT cs.*, s.name as subject_name, s.color as subject_color, s.professor 
                  FROM class_schedule cs 
                  JOIN subjects s ON cs.subject_id = s.id 
                  WHERE cs.user_id = :user_id AND cs.day_of_week = :day 
                  ORDER BY cs.start_time";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':user_id' => $user_id,
            ':day' => $today
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getWeeklySchedule($user_id) {
        $conn = $this->db->getConnection();
        
        $query = "SELECT cs.*, s.name as subject_name, s.color as subject_color, s.professor 
                  FROM class_schedule cs 
                  JOIN subjects s ON cs.subject_id = s.id 
                  WHERE cs.user_id = :user_id 
                  ORDER BY 
                    FIELD(cs.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                    cs.start_time";
        $stmt = $conn->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        
        $schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Organize by day
        $weeklySchedule = [];
        foreach ($schedule as $class) {
            $day = $class['day_of_week'];
            if (!isset($weeklySchedule[$day])) {
                $weeklySchedule[$day] = [];
            }
            $weeklySchedule[$day][] = $class;
        }
        
        return $weeklySchedule;
    }
    
    public function getSchedule($id, $user_id) {
        $conn = $this->db->getConnection();
        
        $query = "SELECT cs.*, s.name as subject_name, s.color as subject_color 
                  FROM class_schedule cs 
                  JOIN subjects s ON cs.subject_id = s.id 
                  WHERE cs.id = :id AND cs.user_id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':id' => $id,
            ':user_id' => $user_id
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        $conn = $this->db->getConnection();
        
        $query = "INSERT INTO class_schedule (user_id, subject_id, day_of_week, start_time, end_time, location, class_type, recurring) 
                  VALUES (:user_id, :subject_id, :day_of_week, :start_time, :end_time, :location, :class_type, :recurring)";
        $stmt = $conn->prepare($query);
        
        return $stmt->execute([
            ':user_id' => $data['user_id'],
            ':subject_id' => $data['subject_id'],
            ':day_of_week' => $data['day_of_week'],
            ':start_time' => $data['start_time'],
            ':end_time' => $data['end_time'],
            ':location' => $data['location'],
            ':class_type' => $data['class_type'],
            ':recurring' => $data['recurring']
        ]);
    }
    
    public function update($id, $user_id, $data) {
        $conn = $this->db->getConnection();
        
        $query = "UPDATE class_schedule SET 
                    subject_id = :subject_id,
                    day_of_week = :day_of_week,
                    start_time = :start_time,
                    end_time = :end_time,
                    location = :location,
                    class_type = :class_type,
                    recurring = :recurring
                  WHERE id = :id AND user_id = :user_id";
        $stmt = $conn->prepare($query);
        
        return $stmt->execute([
            ':subject_id' => $data['subject_id'],
            ':day_of_week' => $data['day_of_week'],
            ':start_time' => $data['start_time'],
            ':end_time' => $data['end_time'],
            ':location' => $data['location'],
            ':class_type' => $data['class_type'],
            ':recurring' => $data['recurring'],
            ':id' => $id,
            ':user_id' => $user_id
        ]);
    }
    
    public function delete($id, $user_id) {
        $conn = $this->db->getConnection();
        
        $query = "DELETE FROM class_schedule WHERE id = :id AND user_id = :user_id";
        $stmt = $conn->prepare($query);
        
        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $user_id
        ]);
    }
    
    public function getUpcomingClasses($user_id, $limit = 5) {
        $conn = $this->db->getConnection();
        
        // Get current day and time
        $current_day = date('l');
        $current_time = date('H:i:s');
        
        $query = "SELECT cs.*, s.name as subject_name, s.color as subject_color 
                  FROM class_schedule cs 
                  JOIN subjects s ON cs.subject_id = s.id 
                  WHERE cs.user_id = :user_id 
                  AND (
                    (cs.day_of_week = :current_day AND cs.start_time > :current_time) 
                    OR cs.day_of_week > :current_day
                  )
                  ORDER BY 
                    FIELD(cs.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                    cs.start_time
                  LIMIT :limit";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':current_day', $current_day, PDO::PARAM_STR);
        $stmt->bindValue(':current_time', $current_time, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getScheduleStats($user_id) {
        $conn = $this->db->getConnection();
        
        $query = "SELECT 
                    COUNT(*) as total_classes,
                    COUNT(DISTINCT day_of_week) as days_with_classes,
                    COUNT(DISTINCT subject_id) as subjects_with_schedule
                  FROM class_schedule 
                  WHERE user_id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>