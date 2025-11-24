<?php
require_once '../includes/functions.php';
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

require_once '../models/Schedule.php';
require_once '../models/Subject.php';

$scheduleModel = new Schedule();
$subjectModel = new Subject();

$weeklySchedule = $scheduleModel->getWeeklySchedule($_SESSION['user_id']);
$subjects = $subjectModel->getUserSubjects($_SESSION['user_id']);

// Days of the week in order
$daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Schedule - StudyHub</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-left">
                <a href="../dashboard.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <h1>Class Schedule</h1>
            </div>
            <div class="user-info">
                <a href="add.php" class="btn btn-primary">Add Class</a>
                <a href="../auth/logout.php" class="btn btn-outline">Logout</a>
            </div>
        </header>

        <div class="weekly-schedule">
            <?php foreach ($daysOfWeek as $day): ?>
                <div class="schedule-day">
                    <h3><?php echo $day; ?></h3>
                    <div class="day-classes">
                        <?php if (isset($weeklySchedule[$day]) && !empty($weeklySchedule[$day])): ?>
                            <?php foreach ($weeklySchedule[$day] as $class): ?>
                                <div class="schedule-class-card" style="border-left: 4px solid <?php echo $class['subject_color'] ?: '#3498db'; ?>">
                                    <div class="class-time">
                                        <?php echo date('g:i A', strtotime($class['start_time'])); ?> - <?php echo date('g:i A', strtotime($class['end_time'])); ?>
                                    </div>
                                    <div class="class-details">
                                        <h4><?php echo htmlspecialchars($class['subject_name']); ?></h4>
                                        <p class="class-type"><?php echo $class['class_type']; ?></p>
                                        <?php if ($class['location']): ?>
                                            <p class="class-location">
                                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($class['location']); ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if ($class['professor']): ?>
                                            <p class="class-professor">
                                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($class['professor']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="class-actions">
                                        <a href="edit.php?id=<?php echo $class['id']; ?>" class="btn btn-sm">Edit</a>
                                        <a href="delete.php?id=<?php echo $class['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-classes">
                                <p>No classes scheduled</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>