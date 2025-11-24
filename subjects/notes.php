<?php
require_once '../includes/functions.php';
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

require_once '../models/Subject.php';
require_once '../models/Note.php';

$subject_id = $_GET['subject_id'] ?? 0;
$subjectModel = new Subject();
$noteModel = new Note();

$subject = $subjectModel->getSubject($subject_id, $_SESSION['user_id']);
if (!$subject) {
    $_SESSION['error'] = "Subject not found!";
    redirect('../dashboard.php');
}

$notes = $noteModel->getSubjectNotes($_SESSION['user_id'], $subject_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'user_id' => $_SESSION['user_id'],
        'subject_id' => $subject_id,
        'title' => sanitize($_POST['title']),
        'content' => sanitize($_POST['content'])
    ];
    
    if ($noteModel->create($data)) {
        $_SESSION['success'] = "Note created successfully!";
        header("Location: notes.php?subject_id=" . $subject_id);
        exit();
    } else {
        $error = "Failed to create note!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($subject['name']); ?> Notes - StudyHub</title>
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
                <h1><?php echo htmlspecialchars($subject['name']); ?> Notes</h1>
            </div>
            <div class="user-info">
                <a href="../auth/logout.php" class="btn btn-outline">Logout</a>
            </div>
        </header>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="notes-layout">
            <!-- Add Note Form -->
            <div class="card">
                <h3>Add New Note</h3>
                <form method="POST" class="note-form">
                    <div class="form-group">
                        <input type="text" name="title" placeholder="Note Title" required>
                    </div>
                    <div class="form-group">
                        <textarea name="content" placeholder="Write your notes here..." rows="6" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Note
                    </button>
                </form>
            </div>

            <!-- Notes List -->
            <div class="card">
                <h3>Your Notes (<?php echo is_array($notes) ? count($notes) : 0; ?>)</h3>
                <?php if (empty($notes)): ?>
                    <div class="empty-state">
                        <i class="fas fa-sticky-note fa-3x"></i>
                        <p>No notes yet. Create your first note above!</p>
                    </div>
                <?php else: ?>
                    <div class="notes-list">
                        <?php foreach ($notes as $note): ?>
                            <div class="note-item">
                                <div class="note-header">
                                    <h4><?php echo htmlspecialchars($note['title']); ?></h4>
                                    <small><?php echo date('M j, Y g:i A', strtotime($note['created_at'])); ?></small>
                                </div>
                                <p class="note-content"><?php echo nl2br(htmlspecialchars($note['content'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>