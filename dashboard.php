<?php
require_once 'includes/functions.php';
if (!isLoggedIn()) {
    redirect('auth/login.php');
}

require_once 'models/User.php';
require_once 'models/Subject.php';
require_once 'models/Assignment.php';
require_once 'models/Task.php';
require_once 'models/Note.php';
require_once 'models/Schedule.php';

$user = new User();
$subjectModel = new Subject();
$assignmentModel = new Assignment();
$taskModel = new Task();
$noteModel = new Note();
$scheduleModel = new Schedule();

$userData = $user->getUserById($_SESSION['user_id']);
$subjects = $subjectModel->getUserSubjects($_SESSION['user_id']);
$upcomingAssignments = $assignmentModel->getUpcomingAssignments($_SESSION['user_id']);
$upcomingTasks = $taskModel->getUpcomingTasks($_SESSION['user_id']);
$todaysSchedule = $scheduleModel->getTodaysSchedule($_SESSION['user_id']);
$assignmentStats = $assignmentModel->getAssignmentStats($_SESSION['user_id']);
$scheduleStats = $scheduleModel->getScheduleStats($_SESSION['user_id']);
$missingAssignments = $assignmentModel->getOverdueAssignments($_SESSION['user_id']);
$missingCount = count($missingAssignments);

// Notes count per subject
$subjectNotesCount = [];
if (!empty($subjects)) {
    foreach ($subjects as $subject) {
        $count = $noteModel->getNotesCountBySubject($_SESSION['user_id'], $subject['id']);
        $subjectNotesCount[$subject['id']] = (int)$count;
    }
}

// Get saved folder images from session
$savedFolderImages = [];
$savedFolderNames = [];
for ($i = 1; $i <= 6; $i++) {
    $savedFolderImages[$i] = $_SESSION["folder_image_$i"] ?? null;
    $savedFolderNames[$i] = $_SESSION["folder_name_$i"] ?? null;
}

// Handle folder image upload via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_folder_image') {
        $folderId = $_POST['folder_id'] ?? '';
        $imageData = $_POST['image_data'] ?? '';
        
        if ($folderId && $imageData) {
            $_SESSION["folder_image_$folderId"] = $imageData;
            echo json_encode(['success' => true]);
            exit;
        }
        echo json_encode(['success' => false]);
        exit;
    }
    
    if ($_POST['action'] === 'save_folder_name') {
        $folderId = $_POST['folder_id'] ?? '';
        $folderName = $_POST['folder_name'] ?? '';
        
        if ($folderId && $folderName) {
            $_SESSION["folder_name_$folderId"] = $folderName;
            echo json_encode(['success' => true]);
            exit;
        }
        echo json_encode(['success' => false]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - StudyHub</title>

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Press+Start+2P&display=swap" rel="stylesheet">

    <style>
        :root {
            --folder-bg-1: <?= $_SESSION['folder_bg_1'] ?? 'rgba(52,152,219,0.2)' ?>;
            --folder-bg-2: <?= $_SESSION['folder_bg_2'] ?? 'rgba(46,204,113,0.2)' ?>;
            --folder-bg-3: <?= $_SESSION['folder_bg_3'] ?? 'rgba(155,89,182,0.2)' ?>;
            --folder-bg-4: <?= $_SESSION['folder_bg_4'] ?? 'rgba(241,196,15,0.2)' ?>;
            --folder-bg-5: <?= $_SESSION['folder_bg_5'] ?? 'rgba(230,126,34,0.2)' ?>;
            --folder-bg-6: <?= $_SESSION['folder_bg_6'] ?? 'rgba(231,76,60,0.2)' ?>;
        }

        .dashboard-background {
            background-image: url('<?= $_SESSION['dashboard_bg'] ?? "assets/Dashboard.png" ?>');
        }

        body::before {
            background-image: url('<?= $_SESSION['dashboard_bg'] ?? "assets/Dashboard.png" ?>');
        }
    </style>
</head>
<body class="dashboard-background">
<div class="container">

    <header class="header">
        <a href="auth/logout.php" class="logout-btn" title="Logout">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </header>

    <div class="split-layout">



        <!-- RIGHT SIDE - Stats and Folders -->
        <div class="split-right">
            <!-- Stats -->
            <div class="stats-horizontal">
                <div class="stat-card pending">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-content">
                        <div class="stat-number" data-target="<?= $assignmentStats['pending'] ?? 0 ?>">0</div>
                        <div class="stat-label">Pending</div>
                    </div>
                </div>

                <div class="stat-card completed">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-content">
                        <div class="stat-number" data-target="<?= $assignmentStats['completed'] ?? 0 ?>">0</div>
                        <div class="stat-label">Completed</div>
                    </div>
                </div>

                <div class="stat-card missing">
                    <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="stat-content">
                        <div class="stat-number" data-target="<?= $missingCount ?>">0</div>
                        <div class="stat-label">Missing</div>
                    </div>
                </div>
            </div>

            <!-- Folders Grid -->
            <div class="folders-grid">
                <?php
                $defaultFolders = [
                    ['name' => 'Mathematics', 'notes' => 0, 'default' => 'assets/default-folder1.jpg'],
                    ['name' => 'Science', 'notes' => 0, 'default' => 'assets/default-folder2.jpg'],
                    ['name' => 'History', 'notes' => 0, 'default' => 'assets/default-folder3.jpg'],
                    ['name' => 'Literature', 'notes' => 0, 'default' => 'assets/default-folder4.jpg'],
                    ['name' => 'Programming', 'notes' => 0, 'default' => 'assets/default-folder5.jpg'],
                    ['name' => 'Art', 'notes' => 0, 'default' => 'assets/default-folder6.jpg'],
                ];

                for ($i = 0; $i < 6; $i++) :
                    if (isset($subjects[$i])) {
                        $subject = $subjects[$i];
                        $id = $i + 1;
                        $folderImage = $savedFolderImages[$id] ?? $defaultFolders[$i]['default'];
                        $folderName = $savedFolderNames[$id] ?? $subject['name'];
                        $notesCount = $subjectNotesCount[$subject['id']] ?? 0;
                    } else {
                        $id = $i + 1;
                        $folderImage = $savedFolderImages[$id] ?? $defaultFolders[$i]['default'];
                        $folderName = $savedFolderNames[$id] ?? $defaultFolders[$i]['name'];
                        $notesCount = $defaultFolders[$i]['notes'];
                    }
                ?>
                <div class="custom-folder-wrapper">
                    <a href="#" class="custom-folder" data-folder-id="<?= $id ?>" data-folder-name="<?= htmlspecialchars($folderName) ?>" data-folder-image="<?= $folderImage ?>">
                        <button class="customize-btn" data-folder-id="<?= $id ?>">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <div class="folder-icon">
                            <img src="<?= $folderImage ?>" alt="<?= $folderName ?> Folder" id="folderImg<?= $id ?>">
                        </div>
                        <div class="folder-info">
                            <h4 id="folderName<?= $id ?>"><?= $folderName ?></h4>
                            <p><?= $notesCount ?> notes</p>
                        </div>
                    </a>
                    <input type="file" class="folderUpload" data-folder-id="<?= $id ?>" accept="image/*" style="display: none;">
                </div>
                <?php endfor; ?>
            </div>
        </div>

    </div>
</div>

<!-- Image Placeholder Section - Full screen background below hero -->
<div class="image-placeholder-section">
<img src="assets/logo.png" alt="A cute cat">
    
<!-- Customize Modal -->
<div id="customizeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Customize Folder</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-options">
            <button class="modal-option-btn" id="nameOption">
                <i class="fas fa-edit"></i> Change Folder Name
            </button>
            <button class="modal-option-btn" id="uploadOption">
                <i class="fas fa-upload"></i> Upload Image
            </button>
            <button class="modal-option-btn" id="urlOption">
                <i class="fas fa-link"></i> Use Image URL
            </button>
            
            <div class="name-input-group" id="nameInputGroup">
                <input type="text" id="folderNameInput" placeholder="Enter new folder name" maxlength="20">
                <div class="name-actions">
                    <button class="btn btn-primary" id="saveName">Save Name</button>
                    <button class="btn btn-outline" id="cancelName">Cancel</button>
                </div>
            </div>
            
            <div class="url-input-group" id="urlInputGroup">
                <input type="url" id="imageUrl" placeholder="https://example.com/image.jpg">
                <div class="url-actions">
                    <button class="btn btn-primary" id="useUrl">Use URL</button>
                    <button class="btn btn-outline" id="cancelUrl">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Assignment Modal -->
<div id="addAssignmentModal" class="form-modal">
    <div class="form-modal-content">
        <div class="form-modal-header">
            <h3>Add New Assignment</h3>
            <button class="form-modal-close">&times;</button>
        </div>
        <form id="assignmentForm">
            <div class="form-group">
                <label for="assignmentTitle">Assignment Title</label>
                <input type="text" id="assignmentTitle" class="form-control" placeholder="Enter assignment title" required>
            </div>
            <div class="form-group">
                <label for="assignmentSubject">Subject</label>
                <select id="assignmentSubject" class="form-control" required>
                    <option value="">Select a subject</option>
                    <?php foreach($subjects as $subject): ?>
                        <option value="<?= $subject['id'] ?>"><?= htmlspecialchars($subject['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="assignmentDueDate">Due Date</label>
                <input type="date" id="assignmentDueDate" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="assignmentDescription">Description</label>
                <textarea id="assignmentDescription" class="form-control" placeholder="Enter assignment description"></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add Assignment</button>
                <button type="button" class="btn btn-outline" id="cancelAssignment">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Task Modal -->
<div id="addTaskModal" class="form-modal">
    <div class="form-modal-content">
        <div class="form-modal-header">
            <h3>Add New Task</h3>
            <button class="form-modal-close">&times;</button>
        </div>
        <form id="taskForm">
            <div class="form-group">
                <label for="taskTitle">Task Title</label>
                <input type="text" id="taskTitle" class="form-control" placeholder="Enter task title" required>
            </div>
            <div class="form-group">
                <label for="taskPriority">Priority</label>
                <select id="taskPriority" class="form-control" required>
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                </select>
            </div>
            <div class="form-group">
                <label for="taskDueDate">Due Date</label>
                <input type="date" id="taskDueDate" class="form-control">
            </div>
            <div class="form-group">
                <label for="taskDescription">Description</label>
                <textarea id="taskDescription" class="form-control" placeholder="Enter task description"></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add Task</button>
                <button type="button" class="btn btn-outline" id="cancelTask">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Subject Modal -->
<div id="addSubjectModal" class="form-modal">
    <div class="form-modal-content">
        <div class="form-modal-header">
            <h3>Add New Subject</h3>
            <button class="form-modal-close">&times;</button>
        </div>
        <form id="subjectForm">
            <div class="form-group">
                <label for="subjectName">Subject Name</label>
                <input type="text" id="subjectName" class="form-control" placeholder="Enter subject name" required>
            </div>
            <div class="form-group">
                <label for="subjectCode">Subject Code</label>
                <input type="text" id="subjectCode" class="form-control" placeholder="Enter subject code">
            </div>
            <div class="form-group">
                <label for="subjectColor">Color Theme</label>
                <input type="color" id="subjectColor" class="form-control" value="#6B8E9C">
            </div>
            <div class="form-group">
                <label for="subjectDescription">Description</label>
                <textarea id="subjectDescription" class="form-control" placeholder="Enter subject description"></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add Subject</button>
                <button type="button" class="btn btn-outline" id="cancelSubject">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Subject Modal -->
<div id="subjectModal" class="subject-modal">
    <div class="subject-modal-content">
        <div class="subject-modal-header">
            <div class="subject-modal-title">
                <div class="subject-modal-icon">
                    <img id="subjectModalIcon" src="" alt="Subject Icon">
                </div>
                <h2 id="subjectModalTitle">Subject Name</h2>
            </div>
            <button class="subject-modal-close">&times;</button>
        </div>
        
        <div class="subject-modal-body">
            <div class="subject-modal-left">
                <div class="subject-section">
                    <h3><i class="fas fa-sticky-note"></i> Notes</h3>
                    <div class="notes-container">
                        <div class="note-editor" contenteditable="true"></div>
                        <button class="btn btn-primary" id="saveNote">Save Note</button>
                        <div class="notes-list" id="notesList">
                            <div class="empty-state">
                                <i class="fas fa-sticky-note"></i>
                                <p>No notes yet. Start typing above to create your first note!</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="subject-section">
                    <h3><i class="fas fa-tasks"></i> Assignments</h3>
                    <div class="assignments-list" id="assignmentsList">
                        <div class="empty-state">
                            <i class="fas fa-tasks"></i>
                            <p>No assignments for this subject.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="subject-modal-right">
                <div class="subject-section">
                    <h3><i class="fas fa-clock"></i> Today's Schedule</h3>
                    <div class="schedule-list" id="scheduleList">
                        <div class="empty-state">
                            <i class="fas fa-clock"></i>
                            <p>No classes scheduled for today.</p>
                        </div>
                    </div>
                </div>
                
                <div class="subject-section">
                    <h3><i class="fas fa-stopwatch"></i> Study Timer</h3>
                    <div class="timer-container">
                        <div class="timer-display" id="timerDisplay">25:00</div>
                        <div class="timer-controls">
                            <button class="timer-btn start" id="timerStart">Start</button>
                            <button class="timer-btn pause" id="timerPause">Pause</button>
                            <button class="timer-btn reset" id="timerReset">Reset</button>
                        </div>
                    </div>
                </div>
                
                <div class="subject-section">
                    <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                    <div class="modal-quick-actions">
                        <button class="modal-action-btn" id="addAssignmentBtn">
                            <i class="fas fa-plus"></i> Add Assignment
                        </button>
                        <button class="modal-action-btn" id="addScheduleBtn">
                            <i class="fas fa-plus"></i> Add Class
                        </button>
                        <button class="modal-action-btn" id="viewAllNotesBtn">
                            <i class="fas fa-book"></i> All Notes
                        </button>
                        <button class="modal-action-btn" id="studyMaterialsBtn">
                            <i class="fas fa-folder"></i> Materials
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Note Modal -->
<div id="editNoteModal" class="edit-note-modal">
    <div class="edit-note-content">
        <div class="edit-note-header">
            <h3>Edit Note</h3>
            <button class="edit-note-close">&times;</button>
        </div>
        <textarea class="edit-note-textarea" id="editNoteTextarea"></textarea>
        <div class="edit-note-actions">
            <button class="btn btn-primary" id="saveEditedNote">Save Changes</button>
            <button class="btn btn-outline" id="cancelEditNote">Cancel</button>
        </div>
    </div>
</div>

<script src="js/dashboard.js"></script>
</body>
</html>