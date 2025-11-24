<?php
require_once '../includes/functions.php';
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

require_once '../models/Schedule.php';
require_once '../models/Subject.php';

$scheduleModel = new Schedule();
$subjectModel = new Subject();

$subjects = $subjectModel->getUserSubjects($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'user_id' => $_SESSION['user_id'],
        'subject_id' => $_POST['subject_id'],
        'day_of_week' => $_POST['day_of_week'],
        'start_time' => $_POST['start_time'],
        'end_time' => $_POST['end_time'],
        'location' => sanitize($_POST['location']),
        'class_type' => $_POST['class_type'],
        'recurring' => isset($_POST['recurring']) ? 1 : 0
    ];
    
    if ($scheduleModel->create($data)) {
        $_SESSION['success'] = "Class added to schedule successfully!";
        redirect('index.php');
    } else {
        $error = "Failed to add class to schedule!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Class - StudyHub</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        select, input[type="text"], input[type="time"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid #007bff;
            color: #007bff;
        }
        
        .btn-outline:hover {
            background: #007bff;
            color: white;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
        }
        
        .empty-state i {
            color: #6c757d;
            margin-bottom: 20px;
        }
        
        .empty-state p {
            color: #6c757d;
            margin-bottom: 20px;
            font-size: 18px;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .header-left {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-left">
                <a href="index.php" class="btn btn-outline">
                    ← Back to Schedule
                </a>
                <h1>Add Class to Schedule</h1>
            </div>
        </header>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($subjects)): ?>
            <div class="card">
                <div class="empty-state">
                    <i>📚</i>
                    <p>You need to add subjects first before scheduling classes!</p>
                    <a href="../subjects/add.php" class="btn btn-primary">Add Subject</a>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <form method="POST">
                    <div class="form-group">
                        <label>Subject</label>
                        <select name="subject_id" required>
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo $subject['id']; ?>">
                                    <?php echo htmlspecialchars($subject['name']); ?> 
                                    <?php if (!empty($subject['professor'])): ?>
                                        (<?php echo htmlspecialchars($subject['professor']); ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Day of Week</label>
                        <select name="day_of_week" required>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Start Time</label>
                            <input type="time" name="start_time" required>
                        </div>
                        <div class="form-group">
                            <label>End Time</label>
                            <input type="time" name="end_time" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="location" placeholder="e.g., Room 101, Building A">
                    </div>
                    
                    <div class="form-group">
                        <label>Class Type</label>
                        <select name="class_type">
                            <option value="Lecture">Lecture</option>
                            <option value="Lab">Lab</option>
                            <option value="Tutorial">Tutorial</option>
                            <option value="Seminar">Seminar</option>
                            <option value="Workshop">Workshop</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="recurring" checked> Recurring weekly
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Add Class</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>