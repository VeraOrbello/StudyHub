<?php
require_once 'database.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitize($data) {
    return htmlspecialchars(trim($data));
}

function getUpcomingDeadlines($user_id, $limit = 5) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $query = "SELECT * FROM assignments 
              WHERE user_id = :user_id AND status != 'Completed' 
              ORDER BY due_date ASC LIMIT :limit";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>