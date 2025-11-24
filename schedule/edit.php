<?php
require_once '../includes/functions.php';
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

require_once '../models/Schedule.php';
require_once '../models/Subject.php';

$scheduleModel = new Schedule();
$subjectModel = new Subject();

$id = $_GET['id'] ?? 0;
$class = $scheduleModel->getSchedule($id, $_SESSION['user_id']);

if (!$class) {
    $_SESSION['error'] = "Class not found!";
    redirect('index.php');
}

$subjects = $subjectModel->getUserSubjects($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'subject_id' => $_POST['subject_id'],
        'day_of_week' => $_POST['day_of_week'],
        'start_time' => $_POST['start_time'],
        'end_time' => $_POST['end_time'],
        'location' => sanitize($_POST['location']),
        'class_type' => $_POST['class_type'],
        'recurring' => isset($_POST['recurring']) ? 1 : 0
    ];
    
    if ($scheduleModel->update($id, $_SESSION['user_id'], $data)) {
        $_SESSION['success'] = "Class updated successfully!";
        redirect('index.php');
    } else {
        $error = "Failed to update class!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Class - StudyHub</title>
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
                <h1>Edit Class</h1>
            </div>
        </header>

        <?php if (isset($error)): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST">
                <div class="form-group">
                    <label>Subject</label>
                    <select name="subject_id" required>
                        <option value="">Select Subject</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?php echo $subject['id']; ?>" <?php echo $subject['id'] == $class['subject_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($subject['name']); ?> (<?php echo htmlspecialchars($subject['professor']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Day of Week</label>
                    <select name="day_of_week" required>
                        <?php 
                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                        foreach ($days as $day): 
                        ?>
                            <option value="<?php echo $day; ?>" <?php echo $day == $class['day_of_week'] ? 'selected' : ''; ?>>
                                <?php echo $day; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Start Time</label>
                        <input type="time" name="start_time" value="<?php echo htmlspecialchars($class['start_time']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>End Time</label>
                        <input type="time" name="end_time" value="<?php echo htmlspecialchars($class['end_time']); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" value="<?php echo htmlspecialchars($class['location']); ?>" placeholder="e.g., Room 101, Building A">
                </div>
                
                <div class="form-group">
                    <label>Class Type</label>
                    <select name="class_type">
                        <option value="Lecture" <?php echo $class['class_type'] == 'Lecture' ? 'selected' : ''; ?>>Lecture</option>
                        <option value="Lab" <?php echo $class['class_type'] == 'Lab' ? 'selected' : ''; ?>>Lab</option>
                        <option value="Tutorial" <?php echo $class['class_type'] == 'Tutorial' ? 'selected' : ''; ?>>Tutorial</option>
                        <option value="Seminar" <?php echo $class['class_type'] == 'Seminar' ? 'selected' : ''; ?>>Seminar</option>
                        <option value="Workshop" <?php echo $class['class_type'] == 'Workshop' ? 'selected' : ''; ?>>Workshop</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="recurring" <?php echo $class['recurring'] ? 'checked' : ''; ?>> Recurring weekly
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Class</button>
                    <a href="index.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>