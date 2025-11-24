<?php
require_once '../includes/functions.php';
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

require_once '../models/Schedule.php';

$scheduleModel = new Schedule();

$id = $_GET['id'] ?? 0;
$class = $scheduleModel->getSchedule($id, $_SESSION['user_id']);

if (!$class) {
    $_SESSION['error'] = "Class not found!";
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($scheduleModel->delete($id, $_SESSION['user_id'])) {
        $_SESSION['success'] = "Class deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete class!";
    }
    redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Class - StudyHub</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-left">
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Schedule
                </a>
                <h1>Delete Class</h1>
            </div>
        </header>

        <div class="card">
            <div class="delete-confirmation">
                <div class="warning-icon">
                    <i class="fas fa-exclamation-triangle fa-3x"></i>
                </div>
                <h3>Are you sure you want to delete this class?</h3>
                
                <div class="class-details-preview">
                    <div class="detail-item">
                        <strong>Subject:</strong> <?php echo htmlspecialchars($class['subject_name']); ?>
                    </div>
                    <div class="detail-item">
                        <strong>Day:</strong> <?php echo htmlspecialchars($class['day_of_week']); ?>
                    </div>
                    <div class="detail-item">
                        <strong>Time:</strong> <?php echo date('g:i A', strtotime($class['start_time'])); ?> - <?php echo date('g:i A', strtotime($class['end_time'])); ?>
                    </div>
                    <div class="detail-item">
                        <strong>Type:</strong> <?php echo htmlspecialchars($class['class_type']); ?>
                    </div>
                    <?php if ($class['location']): ?>
                    <div class="detail-item">
                        <strong>Location:</strong> <?php echo htmlspecialchars($class['location']); ?>
                    </div>
                    <?php endif; ?>
                </div>

                <form method="POST" class="delete-form">
                    <div class="form-actions">
                        <button type="submit" class="btn btn-danger">Yes, Delete Class</button>
                        <a href="index.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>