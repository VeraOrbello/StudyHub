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
            // Save to session
            $_SESSION["folder_image_$folderId"] = $imageData;
            
            // In a real application, you would save to database here
            // Example: $user->saveFolderImage($_SESSION['user_id'], $folderId, $imageData);
            
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
            // Save to session
            $_SESSION["folder_name_$folderId"] = $folderName;
            
            // In a real application, you would save to database here
            // Example: $user->saveFolderName($_SESSION['user_id'], $folderId, $folderName);
            
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
        --dark-pastel-blue: #6B8E9C;
        --dark-pastel-blue-light: #7FA3B2;
        --dark-pastel-blue-dark: #5A7A8C;
    }

    * {
        font-family: 'Montserrat', sans-serif;
    }

    .dashboard-background {
        background-image: url('<?= $_SESSION['dashboard_bg'] ?? "assets/Dashboard.png" ?>');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        min-height: 100vh;
    }

    .container {
        padding: 0.5rem;
    }

    .header {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-bottom: 1rem;
        padding: 0.5rem;
    }

    /* Glass effect logout button */
    .logout-btn {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: rgba(107, 142, 156, 0.3);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-decoration: none;
        font-size: 1.2rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .logout-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: 0.5s;
    }

    .logout-btn:hover::before {
        left: 100%;
    }

    .logout-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
        background: rgba(107, 142, 156, 0.4);
    }

    .split-layout {
        display: grid;
        grid-template-columns: 1fr 1.5fr;
        gap: 2rem;
        margin-top: 1rem;
    }

    .split-left, .split-right {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .split-left {
        margin-right: -1rem;
    }

    .split-right {
        margin-left: -1rem;
    }

    .card {
        background: rgba(107, 142, 156, 0.15);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 1.2rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.8rem;
    }

    .card-header h3 {
        margin: 0;
        color: white;
        font-size: 0.9rem;
        font-weight: 600;
    }

    /* Stats Section - Pure Text Only */
    .stats-horizontal {
        margin-top: 12rem;
        display: grid;
        margin-left: 15rem;
        grid-template-columns: repeat(3, 1fr);
        gap: 5rem;
        margin-bottom: 1.9rem;
        padding: 1rem;
    }

    .stat-card {
        background: transparent !important;
        backdrop-filter: none !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: white;
        position: relative;
    }

    .stat-icon {
        font-size: 1.5rem;
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 0.5rem;
    }

    .stat-content .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
        line-height: 1;
        position: relative;
        font-family: 'Press Start 2P', cursive;
        color: white;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        margin-bottom: 0.3rem;
    }

    .stat-content .stat-label {
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.9);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Folders Grid - Bigger and wider with glass containers */
    .folders-grid {
        display: grid;
        margin-left: 14rem;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.2rem;
        margin-top: 1rem;

    }

    .custom-folder-wrapper {
        text-align: center;
        position: relative;
    }

    .custom-folder {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 1rem;
        background: rgba(107, 142, 156, 0.2);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        text-decoration: none;
        color: inherit;
        border: 1px solid rgba(255, 255, 255, 0.15);
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        min-height: 160px;
        position: relative;
        cursor: pointer;
    }

    .custom-folder:hover {
        transform: translateY(-5px) scale(1.02);
        text-decoration: none;
        color: inherit;
        background: rgba(107, 142, 156, 0.3);
        box-shadow: 0 6px 20px rgba(0,0,0,0.3);
    }

    .folder-icon {
        width: 90px;
        height: 90px;
        border-radius: 12px;
        position: relative;
        background-color: rgba(253, 241, 199, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.3);
        box-shadow: 3px 3px 15px rgba(0,0,0,0.3);
        margin-bottom: 0.6rem;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .folder-icon:hover {
        box-shadow: 5px 5px 20px rgba(0,0,0,0.4);
        border-color: rgba(255, 255, 255, 0.5);
    }

    .folder-icon img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .folder-info h4 {
        margin: 0 0 0.3rem 0;
        color: white;
        font-size: 0.85rem;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
        font-weight: 600;
        max-width: 120px;
        word-wrap: break-word;
        line-height: 1.2;
    }

    .folder-info p {
        margin: 0;
        font-size: 0.7rem;
        color: rgba(255, 255, 255, 0.9);
        text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
        font-weight: 500;
    }

    .customize-btn {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: 1px solid rgba(255, 255, 255, 0.3);
        background: rgba(107, 142, 156, 0.8);
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.6rem;
        backdrop-filter: blur(5px);
    }

    .customize-btn:hover {
        background: rgba(90, 122, 140, 0.9);
        transform: scale(1.1);
    }

    /* Quick Actions - With fixed images */
    .quick-actions-card {   
        margin-right: 19rem;
        background: rgba(107, 142, 156, 0.15);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 1.2rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        margin-bottom: 1rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .quick-actions {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.8rem;
    }

    .action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 1rem;
        background: rgba(107, 142, 156, 0.25);
        border-radius: 8px;
        text-decoration: none;
        color: white;
        transition: all 0.3s ease;
        text-align: center;
        font-size: 0.8rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }

    .action-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
        transition: 0.5s;
    }

    .action-btn:hover::before {
        left: 100%;
    }

    .action-btn:hover {
        background: rgba(107, 142, 156, 0.4);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }

    .action-btn img {
        width: 32px;
        height: 32px;
        margin-bottom: 0.5rem;
        filter: brightness(0) invert(1);
        transition: transform 0.3s ease;
    }

    .action-btn:hover img {
        transform: scale(1.1);
    }

    .action-btn span {
        font-size: 0.75rem;
        font-weight: 600;
    }

    /* Notes Section Styles */
    .notes-card {
        margin-right: 19rem;
        background: rgba(107, 142, 156, 0.15);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 1.2rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        margin-bottom: 1rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .notes-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.8rem;
    }

    .notes-header h3 {
        margin: 0;
        color: white;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .notes-list {
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
        max-height: 300px;
        overflow-y: auto;
        padding-right: 0.5rem;
    }

    .note-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 0.8rem;
        background: rgba(107, 142, 156, 0.25);
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: all 0.3s ease;
        position: relative;
    }

    .note-item:hover {
        background: rgba(107, 142, 156, 0.35);
        transform: translateY(-1px);
    }

    .note-content {
        flex: 1;
        color: white;
        font-size: 0.8rem;
        font-weight: 500;
        line-height: 1.4;
        word-break: break-word;
        padding-right: 1rem;
    }

    .note-date {
        font-size: 0.7rem;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 0.3rem;
    }

    .note-actions {
        display: flex;
        gap: 0.3rem;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .note-item:hover .note-actions {
        opacity: 1;
    }

    .note-btn {
        width: 24px;
        height: 24px;
        border-radius: 4px;
        border: none;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
    }

    .note-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.1);
    }

    .note-btn.edit {
        background: rgba(52, 152, 219, 0.6);
    }

    .note-btn.delete {
        background: rgba(231, 76, 60, 0.6);
    }

    .note-btn.edit:hover {
        background: rgba(52, 152, 219, 0.8);
    }

    .note-btn.delete:hover {
        background: rgba(231, 76, 60, 0.8);
    }

    .notes-add {
        display: flex;
        gap: 0.5rem;
        margin-top: 0.8rem;
    }

    .notes-input {
        flex: 1;
        padding: 0.6rem;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 6px;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        font-size: 0.8rem;
    }

    .notes-input::placeholder {
        color: rgba(255, 255, 255, 0.6);
    }

    .notes-input:focus {
        outline: none;
        border-color: rgba(255, 255, 255, 0.6);
        background: rgba(255, 255, 255, 0.15);
    }

    .add-note-btn {
        padding: 0.6rem 1rem;
        background: rgba(107, 142, 156, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 6px;
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .add-note-btn:hover {
        background: rgba(107, 142, 156, 0.8);
        transform: translateY(-1px);
    }

    /* Empty state for notes */
    .notes-empty {
        text-align: center;
        padding: 2rem;
        color: rgba(255, 255, 255, 0.7);
    }

    .notes-empty i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        opacity: 0.5;
    }

    .notes-empty p {
        margin: 0;
        font-size: 0.8rem;
    }

    /* To-Do List Styles */
    .todo-card {
        margin-right: 19rem;
        background: rgba(107, 142, 156, 0.15);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 1.2rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        margin-bottom: 1rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .todo-list {
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
        max-height: 300px;
        overflow-y: auto;
    }

    .todo-item {
        display: flex;
        align-items: center;
        padding: 0.8rem;
        background: rgba(107, 142, 156, 0.25);
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .todo-item:hover {
        background: rgba(107, 142, 156, 0.35);
        transform: translateY(-1px);
    }

    .todo-checkbox {
        width: 20px;
        height: 20px;
        border-radius: 4px;
        border: 2px solid rgba(255, 255, 255, 0.5);
        background: transparent;
        margin-right: 0.8rem;
        cursor: pointer;
        position: relative;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }

    .todo-checkbox.checked {
        background: rgba(107, 142, 156, 0.8);
        border-color: rgba(255, 255, 255, 0.8);
    }

    .todo-checkbox.checked::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 12px;
        font-weight: bold;
    }

    .todo-text {
        flex: 1;
        color: white;
        font-size: 0.8rem;
        font-weight: 500;
        word-break: break-word;
    }

    .todo-item.completed .todo-text {
        text-decoration: line-through;
        opacity: 0.7;
    }

    .todo-actions {
        display: flex;
        gap: 0.3rem;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .todo-item:hover .todo-actions {
        opacity: 1;
    }

    .todo-btn {
        width: 20px;
        height: 20px;
        border-radius: 4px;
        border: none;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.6rem;
    }

    .todo-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.1);
    }

    .todo-btn.delete {
        background: rgba(231, 76, 60, 0.6);
    }

    .todo-btn.delete:hover {
        background: rgba(231, 76, 60, 0.8);
    }

    .todo-add {
        display: flex;
        gap: 0.5rem;
        margin-top: 0.8rem;
    }

    .todo-input {
        flex: 1;
        padding: 0.6rem;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 6px;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        font-size: 0.8rem;
    }

    .todo-input::placeholder {
        color: rgba(255, 255, 255, 0.6);
    }

    .todo-input:focus {
        outline: none;
        border-color: rgba(255, 255, 255, 0.6);
        background: rgba(255, 255, 255, 0.15);
    }

    .add-todo-btn {
        padding: 0.6rem 1rem;
        background: rgba(107, 142, 156, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 6px;
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .add-todo-btn:hover {
        background: rgba(107, 142, 156, 0.8);
        transform: translateY(-1px);
    }

    /* Calendar */
    .calendar-card {
        min-height: 180px;
    }

    .calendar-nav {
        display: flex;
        gap: 0.3rem;
    }

    .calendar-placeholder {
        text-align: center;
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.8rem;
        padding: 1rem;
    }

    /* Enhanced Modal Styles with Transparent Glass Effect */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: rgba(107, 142, 156, 0.15);
        backdrop-filter: blur(20px);
        padding: 2rem;
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        max-width: 480px;
        width: 90%;
        border: 1px solid rgba(255, 255, 255, 0.2);
        position: relative;
        overflow: hidden;
    }

    .modal-content::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .modal-header h3 {
        margin: 0;
        color: white;
        font-weight: 600;
        font-size: 1.3rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .modal-close {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 8px;
        width: 36px;
        height: 36px;
        font-size: 1.1rem;
        cursor: pointer;
        color: rgba(255, 255, 255, 0.9);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(10px);
    }

    .modal-close:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.5);
        transform: scale(1.1);
    }

    .modal-options {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    /* Enhanced Rectangular Rounded Buttons */
    .modal-option-btn {
        padding: 1.2rem 1.5rem;
        border: 2px solid rgba(255, 255, 255, 0.3);
        background: rgba(107, 142, 156, 0.25);
        color: white;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 1rem;
        font-weight: 500;
        text-align: left;
        display: flex;
        align-items: center;
        gap: 0.8rem;
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(10px);
    }

    .modal-option-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
        transition: 0.5s;
    }

    .modal-option-btn:hover::before {
        left: 100%;
    }

    .modal-option-btn:hover {
        background: rgba(107, 142, 156, 0.4);
        border-color: rgba(255, 255, 255, 0.5);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.2);
    }

    .modal-option-btn i {
        font-size: 1.2rem;
        width: 24px;
        text-align: center;
    }

    .url-input-group, .name-input-group {
        display: none;
        margin-top: 1.5rem;
        padding: 1.5rem;
        background: rgba(107, 142, 156, 0.2);
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
    }

    .url-input-group input, .name-input-group input {
        width: 100%;
        padding: 0.8rem 1rem;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 8px;
        font-size: 0.9rem;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        transition: all 0.3s ease;
    }

    .url-input-group input:focus, .name-input-group input:focus {
        outline: none;
        border-color: rgba(255, 255, 255, 0.6);
        background: rgba(255, 255, 255, 0.15);
    }

    .url-input-group input::placeholder, .name-input-group input::placeholder {
        color: rgba(255, 255, 255, 0.6);
    }

    .url-actions, .name-actions {
        display: flex;
        gap: 0.8rem;
        margin-top: 1rem;
    }

    .btn {
        padding: 0.8rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        flex: 1;
        text-align: center;
    }

    .btn-primary {
        background: rgba(107, 142, 156, 0.6);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .btn-primary:hover {
        background: rgba(107, 142, 156, 0.8);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .btn-outline {
        background: transparent;
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .btn-outline:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.5);
    }

    /* Form Modal Styles */
    .form-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
        z-index: 3000;
        align-items: center;
        justify-content: center;
    }

    .form-modal-content {
        background: rgba(107, 142, 156, 0.2);
        backdrop-filter: blur(20px);
        padding: 2rem;
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        max-width: 500px;
        width: 90%;
        border: 1px solid rgba(255, 255, 255, 0.2);
        position: relative;
    }

    .form-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .form-modal-header h3 {
        margin: 0;
        color: white;
        font-weight: 600;
        font-size: 1.3rem;
    }

    .form-modal-close {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 8px;
        width: 36px;
        height: 36px;
        font-size: 1.1rem;
        cursor: pointer;
        color: rgba(255, 255, 255, 0.9);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .form-modal-close:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.5);
        transform: scale(1.1);
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: white;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .form-control {
        width: 100%;
        padding: 0.8rem 1rem;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: rgba(255, 255, 255, 0.6);
        background: rgba(255, 255, 255, 0.15);
    }

    .form-control::placeholder {
        color: rgba(255, 255, 255, 0.6);
    }

    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }

    .form-actions {
        display: flex;
        gap: 0.8rem;
        margin-top: 2rem;
    }

    /* Edit Note Modal */
    .edit-note-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
        z-index: 3000;
        align-items: center;
        justify-content: center;
    }

    .edit-note-content {
        background: rgba(107, 142, 156, 0.2);
        backdrop-filter: blur(20px);
        padding: 2rem;
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        max-width: 500px;
        width: 90%;
        border: 1px solid rgba(255, 255, 255, 0.2);
        position: relative;
    }

    .edit-note-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .edit-note-header h3 {
        margin: 0;
        color: white;
        font-weight: 600;
        font-size: 1.3rem;
    }

    .edit-note-close {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 8px;
        width: 36px;
        height: 36px;
        font-size: 1.1rem;
        cursor: pointer;
        color: rgba(255, 255, 255, 0.9);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .edit-note-close:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.5);
        transform: scale(1.1);
    }

    .edit-note-textarea {
        width: 100%;
        min-height: 150px;
        padding: 1rem;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        font-size: 0.9rem;
        resize: vertical;
        margin-bottom: 1.5rem;
    }

    .edit-note-textarea:focus {
        outline: none;
        border-color: rgba(255, 255, 255, 0.6);
        background: rgba(255, 255, 255, 0.15);
    }

    .edit-note-actions {
        display: flex;
        gap: 0.8rem;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .split-layout {
            grid-template-columns: 1fr;
        }
        
        .stats-horizontal {
            grid-template-columns: repeat(3, 1fr);
        }
        
        .folders-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .split-left, .split-right {
            margin: 0;
        }
        
        .quick-actions-card,
        .todo-card,
        .notes-card {
            margin-right: 0;
        }
    }

    @media (max-width: 768px) {
        .stats-horizontal {
            grid-template-columns: repeat(3, 1fr);
        }
        
        .folders-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .quick-actions {
            grid-template-columns: 1fr;
        }
        
        .stat-content .stat-number {
            font-size: 2rem;
        }
        
        .modal-content {
            padding: 1.5rem;
        }
        
        .modal-option-btn {
            padding: 1rem 1.2rem;
            font-size: 0.9rem;
        }
    }

    @media (max-width: 480px) {
        .folders-grid {
            grid-template-columns: 1fr;
        }
        
        .stats-horizontal {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .folder-icon {
            width: 80px;
            height: 80px;
        }
        
        .stat-content .stat-number {
            font-size: 1.8rem;
        }
        
        .folder-info h4 {
            max-width: 100px;
        }
    }

    /* Subject Modal Styles */
    .subject-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        z-index: 2000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .subject-modal-content {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 12px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        max-width: 900px;
        width: 95%;
        max-height: 90vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .subject-modal-header {
        padding: 1.5rem 2rem;
        border-bottom: 1px solid #e1e5e9;
        background: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .subject-modal-title {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .subject-modal-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        overflow: hidden;
    }

    .subject-modal-icon img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .subject-modal-title h2 {
        margin: 0;
        color: #2d3436;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .subject-modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #636e72;
        padding: 0.5rem;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .subject-modal-close:hover {
        background: #f8f9fa;
        color: #2d3436;
    }

    .subject-modal-body {
        flex: 1;
        overflow-y: auto;
        padding: 2rem;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }

    .subject-section {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid #e1e5e9;
    }

    .subject-section h3 {
        margin: 0 0 1rem 0;
        color: #2d3436;
        font-size: 1.1rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .subject-section h3 i {
        color: #6c5ce7;
    }

    /* Notes Section */
    .notes-container {
        min-height: 200px;
    }

    .note-editor {
        border: 1px solid #e1e5e9;
        border-radius: 6px;
        padding: 1rem;
        min-height: 120px;
        background: #f8f9fa;
        margin-bottom: 1rem;
        font-family: inherit;
    }

    .note-editor:empty:before {
        content: "Start typing your notes here...";
        color: #636e72;
    }

    .note-editor:focus {
        outline: none;
        border-color: #6c5ce7;
        background: white;
    }

    .notes-list {
        max-height: 300px;
        overflow-y: auto;
    }

    .note-item {
        background: white;
        border: 1px solid #e1e5e9;
        border-radius: 6px;
        padding: 1rem;
        margin-bottom: 0.5rem;
        position: relative;
    }

    .note-date {
        font-size: 0.8rem;
        color: #636e72;
        margin-bottom: 0.5rem;
    }

    .note-content {
        font-size: 0.9rem;
        color: #2d3436;
        line-height: 1.4;
    }

    /* Assignments Section */
    .assignments-list {
        max-height: 300px;
        overflow-y: auto;
    }

    .assignment-item {
        background: white;
        border: 1px solid #e1e5e9;
        border-radius: 6px;
        padding: 1rem;
        margin-bottom: 0.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .assignment-info h4 {
        margin: 0 0 0.3rem 0;
        color: #2d3436;
        font-size: 0.9rem;
    }

    .assignment-info p {
        margin: 0;
        font-size: 0.8rem;
        color: #636e72;
    }

    .assignment-status {
        display: flex;
        gap: 0.5rem;
    }

    .status-badge {
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
    }

    .status-not-started {
        background: #ffeaa7;
        color: #e17055;
    }

    .status-in-progress {
        background: #81ecec;
        color: #00b894;
    }

    .status-done {
        background: #55efc4;
        color: #00b894;
    }

    .status-badge:hover {
        transform: scale(1.05);
    }

    /* Schedule Section */
    .schedule-list {
        max-height: 200px;
        overflow-y: auto;
    }

    .schedule-item {
        background: white;
        border: 1px solid #e1e5e9;
        border-radius: 6px;
        padding: 1rem;
        margin-bottom: 0.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .schedule-time {
        font-weight: 600;
        color: #6c5ce7;
        font-size: 0.9rem;
    }

    .schedule-details h4 {
        margin: 0 0 0.3rem 0;
        color: #2d3436;
        font-size: 0.9rem;
    }

    .schedule-details p {
        margin: 0;
        font-size: 0.8rem;
        color: #636e72;
    }

    /* Timer Section */
    .timer-container {
        text-align: center;
        padding: 1.5rem;
        background: linear-gradient(135deg, #6c5ce7, #a29bfe);
        border-radius: 8px;
        color: white;
    }

    .timer-display {
        font-size: 3rem;
        font-weight: bold;
        font-family: 'Press Start 2P', monospace;
        margin: 1rem 0;
    }

    .timer-controls {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
    }

    .timer-btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 6px;
        background: rgba(255,255,255,0.2);
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 600;
    }

    .timer-btn:hover {
        background: rgba(255,255,255,0.3);
        transform: translateY(-2px);
    }

    .timer-btn.start {
        background: #00b894;
    }

    .timer-btn.pause {
        background: #fdcb6e;
    }

    .timer-btn.reset {
        background: #e17055;
    }

    /* Quick Actions in Modal */
    .modal-quick-actions {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.8rem;
        margin-top: 1rem;
    }

    .modal-action-btn {
        padding: 0.8rem;
        background: #f8f9fa;
        border: 1px solid #e1e5e9;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        font-size: 0.8rem;
        font-weight: 600;
        color: #2d3436;
        border: none;
    }

    .modal-action-btn:hover {
        background: #6c5ce7;
        color: white;
        transform: translateY(-2px);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: #636e72;
    }

    .empty-state i {
        font-size: 2rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .note-actions {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .note-item:hover .note-actions {
        opacity: 1;
    }

    .note-delete {
        background: #e17055;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 0.3rem 0.6rem;
        font-size: 0.7rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .note-delete:hover {
        background: #d63031;
        transform: scale(1.1);
    }

    /* Subject Modal Layout */
    .subject-modal-left,
    .subject-modal-right {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .subject-modal-left {
        grid-column: 1;
    }

    .subject-modal-right {
        grid-column: 2;
    }

    @media (max-width: 768px) {
        .subject-modal-body {
            grid-template-columns: 1fr;
            padding: 1rem;
        }
        
        .subject-modal-left,
        .subject-modal-right {
            grid-column: 1;
        }
        
        .subject-modal-header {
            padding: 1rem;
        }
        
        .timer-display {
            font-size: 2rem;
        }
    }

    /* Add this to your existing CSS */

.dashboard-background {
    background-image: url('<?= $_SESSION['dashboard_bg'] ?? "assets/Dashboard.png" ?>');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    min-height: 100vh;
    position: relative;
}

.container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    overflow-y: auto;
    padding: 0.5rem;
    z-index: 1;
}

/* Make all dashboard content fixed */
.header,
.split-layout,
.stats-horizontal,
.folders-grid {
    position: relative;
    z-index: 2;
}

/* Ensure modals stay on top */
.modal,
.form-modal,
.subject-modal,
.edit-note-modal {
    position: fixed;
    z-index: 1000;
}

/* Add a pseudo-element to create the scrollable background */
body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url('<?= $_SESSION['dashboard_bg'] ?? "assets/Dashboard.png" ?>');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    z-index: -1;
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

        <!-- LEFT SIDE - Quick Actions, Notes, and Calendar -->
        <div class="split-left">
            <!-- Quick Actions - Moved more to the left -->
            <div class="quick-actions-card">
                <h3>Quick Actions</h3>
                <div class="quick-actions">
                    <div class="action-btn" id="addAssignmentAction">
                        <img src="../assets/quick-actions/2.png" alt="Add Assignment">
                        <span>Add Assignment</span>
                    </div>
                    <div class="action-btn" id="addScheduleAction">
                        <img src="assets/schedule-icon.png" alt="Add Class">
                        <span>Add Class</span>
                    </div>
                    <div class="action-btn" id="addTaskAction">
                        <img src="assets/task-icon.png" alt="Add Task">
                        <span>Add Task</span>
                    </div>
                    <div class="action-btn" id="addSubjectAction">
                        <img src="assets/subject-icon.png" alt="Add Subject">
                        <span>Add Subject</span>
                    </div>
                </div>
            </div>

            <!-- Notes Section - Added above the calendar -->
            <div class="notes-card">
                <div class="notes-header">
                    <h3><i class="fas fa-sticky-note"></i> Quick Notes</h3>
                </div>
                <div class="notes-list" id="quickNotesList">
                    <!-- Notes will be dynamically added here -->
                </div>
                <div class="notes-add">
                    <input type="text" class="notes-input" id="quickNoteInput" placeholder="Add a quick note...">
                    <button class="add-note-btn" id="addQuickNoteBtn">Add</button>
                </div>
            </div>

            <!-- To-Do List -->
            <div class="todo-card">
                <h3>To-Do List</h3>
                <div class="todo-list" id="todoList">
                    <!-- To-do items will be dynamically added here -->
                </div>
                <div class="todo-add">
                    <input type="text" class="todo-input" id="todoInput" placeholder="Add a new task...">
                    <button class="add-todo-btn" id="addTodoBtn">Add</button>
                </div>
            </div>

            <!-- Calendar - Moved more to the left -->
            <div class="card calendar-card">
                <div class="card-header">
                    <h3><i class="fas fa-calendar"></i> <?= date('F Y'); ?></h3>
                    <div class="calendar-nav">
                        <button class="btn btn-sm" id="prevMonth"><i class="fas fa-chevron-left"></i></button>
                        <button class="btn btn-sm" id="nextMonth"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
                <div class="calendar-placeholder">
                    <p>Calendar will be implemented here</p>
                </div>
            </div>
        </div>

        <!-- RIGHT SIDE - Stats and Folders -->
        <div class="split-right">
            <!-- Stats - Pure Text Only, No Backgrounds -->
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

            <!-- Folders Grid - Moved more to the right -->
            <div class="folders-grid">
                <?php
                // Default folder data for empty slots
                $defaultFolders = [
                    ['name' => 'Mathematics', 'notes' => 0, 'default' => 'assets/default-folder1.jpg'],
                    ['name' => 'Science', 'notes' => 0, 'default' => 'assets/default-folder2.jpg'],
                    ['name' => 'History', 'notes' => 0, 'default' => 'assets/default-folder3.jpg'],
                    ['name' => 'Literature', 'notes' => 0, 'default' => 'assets/default-folder4.jpg'],
                    ['name' => 'Programming', 'notes' => 0, 'default' => 'assets/default-folder5.jpg'],
                    ['name' => 'Art', 'notes' => 0, 'default' => 'assets/default-folder6.jpg'],
                ];

                // Display actual subjects first, then default folders for empty slots
                for ($i = 0; $i < 6; $i++) :
                    if (isset($subjects[$i])) {
                        // Use actual subject data
                        $subject = $subjects[$i];
                        $id = $i + 1;
                        $folderImage = $savedFolderImages[$id] ?? $defaultFolders[$i]['default'];
                        $folderName = $savedFolderNames[$id] ?? $subject['name'];
                        $notesCount = $subjectNotesCount[$subject['id']] ?? 0;
                        $folderLink = "#"; // Changed to hash to prevent 404 errors
                    } else {
                        // Use default folder data
                        $id = $i + 1;
                        $folderImage = $savedFolderImages[$id] ?? $defaultFolders[$i]['default'];
                        $folderName = $savedFolderNames[$id] ?? $defaultFolders[$i]['name'];
                        $notesCount = $defaultFolders[$i]['notes'];
                        $folderLink = "#"; // Changed to hash to prevent 404 errors
                    }
                ?>
                <div class="custom-folder-wrapper">
                    <a href="<?= $folderLink ?>" class="custom-folder" data-folder-id="<?= $id ?>" data-folder-name="<?= htmlspecialchars($folderName) ?>" data-folder-image="<?= $folderImage ?>">
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
            
            <!-- Name Change Section -->
            <div class="name-input-group" id="nameInputGroup">
                <input type="text" id="folderNameInput" placeholder="Enter new folder name" maxlength="20">
                <div class="name-actions">
                    <button class="btn btn-primary" id="saveName">Save Name</button>
                    <button class="btn btn-outline" id="cancelName">Cancel</button>
                </div>
            </div>
            
            <!-- URL Input Section -->
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
            <!-- Left Column -->
            <div class="subject-modal-left">
                <!-- Notes Section -->
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
                
                <!-- Assignments Section -->
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
            
            <!-- Right Column -->
            <div class="subject-modal-right">
                <!-- Schedule Section -->
                <div class="subject-section">
                    <h3><i class="fas fa-clock"></i> Today's Schedule</h3>
                    <div class="schedule-list" id="scheduleList">
                        <div class="empty-state">
                            <i class="fas fa-clock"></i>
                            <p>No classes scheduled for today.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Timer Section -->
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
                
                <!-- Quick Actions -->
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

<script>
    let currentFolderId = null;
    let currentEditingNoteId = null;
    const modal = document.getElementById('customizeModal');
    const urlInputGroup = document.getElementById('urlInputGroup');
    const nameInputGroup = document.getElementById('nameInputGroup');
    const imageUrlInput = document.getElementById('imageUrl');
    const folderNameInput = document.getElementById('folderNameInput');
    const editNoteModal = document.getElementById('editNoteModal');

    // Quick notes functionality
    let quickNotes = JSON.parse(localStorage.getItem('quickNotes')) || [];

    // To-Do List Functionality
    let todos = JSON.parse(localStorage.getItem('todos')) || [];

    // Enhanced count-up animation with timer effect
    document.addEventListener('DOMContentLoaded', function() {
        // Animate all stat numbers
        document.querySelectorAll('.stat-number[data-target]').forEach(counter => {
            const target = +counter.getAttribute('data-target');
            let count = 0;
            const increment = target / 50;
            
            const updateCount = () => {
                if (count < target) {
                    count += increment;
                    counter.innerText = Math.ceil(count);
                    setTimeout(updateCount, 30);
                } else {
                    counter.innerText = target;
                }
            }
            updateCount();
        });

        // Initialize to-do list
        initializeTodoList();

        // Initialize quick notes
        initializeQuickNotes();

        // To-do functionality
        document.getElementById('addTodoBtn').addEventListener('click', addTodo);
        document.getElementById('todoInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                addTodo();
            }
        });

        // Quick notes functionality
        document.getElementById('addQuickNoteBtn').addEventListener('click', addQuickNote);
        document.getElementById('quickNoteInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                addQuickNote();
            }
        });

        // Quick Actions Modals
        document.getElementById('addAssignmentAction').addEventListener('click', function() {
            document.getElementById('addAssignmentModal').style.display = 'flex';
        });

        document.getElementById('addTaskAction').addEventListener('click', function() {
            document.getElementById('addTaskModal').style.display = 'flex';
        });

        document.getElementById('addSubjectAction').addEventListener('click', function() {
            document.getElementById('addSubjectModal').style.display = 'flex';
        });

        // Close form modals
        document.querySelectorAll('.form-modal-close').forEach(closeBtn => {
            closeBtn.addEventListener('click', function() {
                this.closest('.form-modal').style.display = 'none';
            });
        });

        document.getElementById('cancelAssignment').addEventListener('click', function() {
            document.getElementById('addAssignmentModal').style.display = 'none';
        });

        document.getElementById('cancelTask').addEventListener('click', function() {
            document.getElementById('addTaskModal').style.display = 'none';
        });

        document.getElementById('cancelSubject').addEventListener('click', function() {
            document.getElementById('addSubjectModal').style.display = 'none';
        });

        // Form submissions
        document.getElementById('assignmentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const title = document.getElementById('assignmentTitle').value;
            const subject = document.getElementById('assignmentSubject').value;
            const dueDate = document.getElementById('assignmentDueDate').value;
            const description = document.getElementById('assignmentDescription').value;
            
            // Here you would typically send this data to your backend
            console.log('Adding assignment:', { title, subject, dueDate, description });
            alert('Assignment added successfully!');
            document.getElementById('addAssignmentModal').style.display = 'none';
            this.reset();
        });

        document.getElementById('taskForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const title = document.getElementById('taskTitle').value;
            const priority = document.getElementById('taskPriority').value;
            const dueDate = document.getElementById('taskDueDate').value;
            const description = document.getElementById('taskDescription').value;
            
            // Add to to-do list
            addTodoItem(title, false);
            document.getElementById('addTaskModal').style.display = 'none';
            this.reset();
        });

        document.getElementById('subjectForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const name = document.getElementById('subjectName').value;
            const code = document.getElementById('subjectCode').value;
            const color = document.getElementById('subjectColor').value;
            const description = document.getElementById('subjectDescription').value;
            
            console.log('Adding subject:', { name, code, color, description });
            alert('Subject added successfully!');
            document.getElementById('addSubjectModal').style.display = 'none';
            this.reset();
        });

        // Close modals when clicking outside
        document.querySelectorAll('.form-modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                }
            });
        });

        // Edit note modal functionality
        document.querySelector('.edit-note-close').addEventListener('click', function() {
            editNoteModal.style.display = 'none';
        });

        document.getElementById('cancelEditNote').addEventListener('click', function() {
            editNoteModal.style.display = 'none';
        });

        document.getElementById('saveEditedNote').addEventListener('click', saveEditedNote);

        // Close edit note modal when clicking outside
        editNoteModal.addEventListener('click', function(e) {
            if (e.target === this) {
                editNoteModal.style.display = 'none';
            }
        });

        // Customize button click
        document.querySelectorAll(".customize-btn").forEach(btn => {
            btn.addEventListener("click", function(e) {
                e.preventDefault();
                e.stopPropagation();
                currentFolderId = this.getAttribute("data-folder-id");
                
                // Get current folder name for the input
                const currentName = document.getElementById(`folderName${currentFolderId}`).textContent;
                folderNameInput.value = currentName;
                
                modal.style.display = 'flex';
                urlInputGroup.style.display = 'none';
                nameInputGroup.style.display = 'none';
            });
        });

        // Modal close
        document.querySelector('.modal-close').addEventListener('click', function() {
            modal.style.display = 'none';
        });

        // Name option
        document.getElementById('nameOption').addEventListener('click', function() {
            nameInputGroup.style.display = 'block';
            urlInputGroup.style.display = 'none';
        });

        // Upload option
        document.getElementById('uploadOption').addEventListener('click', function() {
            const fileInput = document.querySelector(`.folderUpload[data-folder-id="${currentFolderId}"]`);
            fileInput.click();
            modal.style.display = 'none';
        });

        // URL option
        document.getElementById('urlOption').addEventListener('click', function() {
            urlInputGroup.style.display = 'block';
            nameInputGroup.style.display = 'none';
        });

        // Save Name
        document.getElementById('saveName').addEventListener('click', function() {
            const newName = folderNameInput.value.trim();
            if (newName) {
                const nameElement = document.getElementById(`folderName${currentFolderId}`);
                if (nameElement) {
                    nameElement.textContent = newName;
                    // Save the name to server
                    saveFolderName(currentFolderId, newName);
                }
                modal.style.display = 'none';
                folderNameInput.value = '';
            } else {
                alert('Please enter a folder name');
            }
        });

        // Cancel Name
        document.getElementById('cancelName').addEventListener('click', function() {
            nameInputGroup.style.display = 'none';
            folderNameInput.value = '';
        });

        // Use URL
        document.getElementById('useUrl').addEventListener('click', function() {
            const url = imageUrlInput.value.trim();
            if (url) {
                const img = document.getElementById(`folderImg${currentFolderId}`);
                if (img) {
                    img.src = url;
                    // Save the URL to server
                    saveFolderImage(currentFolderId, url);
                }
                modal.style.display = 'none';
                imageUrlInput.value = '';
            } else {
                alert('Please enter a valid URL');
            }
        });

        // Cancel URL
        document.getElementById('cancelUrl').addEventListener('click', function() {
            urlInputGroup.style.display = 'none';
            imageUrlInput.value = '';
        });

        // File upload
        document.querySelectorAll(".folderUpload").forEach(input => {
            input.addEventListener("change", function(e) {
                const folderId = e.target.getAttribute("data-folder-id");
                const file = e.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.getElementById(`folderImg${folderId}`);
                    if (img) {
                        img.src = e.target.result;
                        // Save the image data to server
                        saveFolderImage(folderId, e.target.result);
                    }   
                };
                reader.readAsDataURL(file);
            });
        });

        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });

        // Update folder links to open modal instead
        document.querySelectorAll('.custom-folder').forEach(folder => {
            folder.addEventListener('click', function(e) {
                e.preventDefault();
                const folderName = this.getAttribute('data-folder-name');
                const folderImage = this.getAttribute('data-folder-image');
                
                openSubjectModal(folderName, folderImage);
            });
        });

        // Modal event listeners
        document.querySelector('.subject-modal-close').addEventListener('click', closeSubjectModal);
        document.getElementById('subjectModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeSubjectModal();
            }
        });

        // Timer controls
        document.getElementById('timerStart').addEventListener('click', startTimer);
        document.getElementById('timerPause').addEventListener('click', pauseTimer);
        document.getElementById('timerReset').addEventListener('click', resetTimer);

        // Note functionality
        document.getElementById('saveNote').addEventListener('click', function() {
            const noteContent = document.querySelector('.note-editor').innerHTML.trim();
            if (noteContent && noteContent !== '<br>') {
                const notesList = document.getElementById('notesList');
                
                // Remove empty state if it exists
                if (notesList.querySelector('.empty-state')) {
                    notesList.innerHTML = '';
                }
                
                // Create new note
                const noteItem = document.createElement('div');
                noteItem.className = 'note-item';
                const noteId = Date.now();
                noteItem.innerHTML = `
                    <div class="note-date">${new Date().toLocaleString()}</div>
                    <div class="note-content">${noteContent}</div>
                    <div class="note-actions">
                        <button class="note-btn edit" onclick="editNote(${noteId})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="note-btn delete" onclick="deleteNote(${noteId})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
                noteItem.setAttribute('data-note-id', noteId);
                notesList.appendChild(noteItem);
                
                // Store note
                notes.push({
                    id: noteId,
                    content: noteContent,
                    date: new Date().toLocaleString()
                });
                
                // Clear editor
                document.querySelector('.note-editor').innerHTML = '';
            } else {
                alert('Please write something in the note editor first!');
            }
        });

        // Quick actions
        document.getElementById('addAssignmentBtn').addEventListener('click', function() {
            alert('Redirecting to Add Assignment page...');
        });

        document.getElementById('addScheduleBtn').addEventListener('click', function() {
            alert('Redirecting to Add Class page...');
        });

        document.getElementById('viewAllNotesBtn').addEventListener('click', function() {
            alert('Showing all notes for this subject...');
        });

        document.getElementById('studyMaterialsBtn').addEventListener('click', function() {
            alert('Opening study materials...');
        });

        // Initialize timer display
        updateTimerDisplay();
    });

    // Quick Notes Functions
    function initializeQuickNotes() {
        const quickNotesList = document.getElementById('quickNotesList');
        quickNotesList.innerHTML = '';
        
        if (quickNotes.length === 0) {
            quickNotesList.innerHTML = `
                <div class="notes-empty">
                    <i class="fas fa-sticky-note"></i>
                    <p>No quick notes yet. Add one above!</p>
                </div>
            `;
        } else {
            quickNotes.forEach(note => {
                addQuickNoteItem(note.id, note.content, note.date);
            });
        }
    }

    function addQuickNote() {
        const quickNoteInput = document.getElementById('quickNoteInput');
        const text = quickNoteInput.value.trim();
        
        if (text) {
            const noteId = Date.now();
            addQuickNoteItem(noteId, text, new Date().toLocaleString());
            quickNoteInput.value = '';
            
            // Save to storage
            quickNotes.push({
                id: noteId,
                content: text,
                date: new Date().toLocaleString()
            });
            saveQuickNotes();
        }
    }

    function addQuickNoteItem(id, content, date) {
        const quickNotesList = document.getElementById('quickNotesList');
        
        // Remove empty state if it exists
        if (quickNotesList.querySelector('.notes-empty')) {
            quickNotesList.innerHTML = '';
        }
        
        const noteItem = document.createElement('div');
        noteItem.className = 'note-item';
        noteItem.setAttribute('data-note-id', id);
        
        noteItem.innerHTML = `
            <div class="note-content">
                <div class="note-date">${date}</div>
                <div>${content}</div>
            </div>
            <div class="note-actions">
                <button class="note-btn edit" onclick="editQuickNote(${id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="note-btn delete" onclick="deleteQuickNote(${id})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        
        quickNotesList.appendChild(noteItem);
    }

    function editQuickNote(noteId) {
        const note = quickNotes.find(n => n.id === noteId);
        if (note) {
            currentEditingNoteId = noteId;
            document.getElementById('editNoteTextarea').value = note.content;
            editNoteModal.style.display = 'flex';
        }
    }

    function deleteQuickNote(noteId) {
        if (confirm('Are you sure you want to delete this note?')) {
            // Remove from DOM
            const noteElement = document.querySelector(`[data-note-id="${noteId}"]`);
            if (noteElement) {
                noteElement.remove();
            }
            
            // Remove from storage
            quickNotes = quickNotes.filter(note => note.id !== noteId);
            saveQuickNotes();
            
            // Show empty state if no notes left
            const quickNotesList = document.getElementById('quickNotesList');
            if (quickNotesList.children.length === 0) {
                quickNotesList.innerHTML = `
                    <div class="notes-empty">
                        <i class="fas fa-sticky-note"></i>
                        <p>No quick notes yet. Add one above!</p>
                    </div>
                `;
            }
        }
    }

    function saveQuickNotes() {
        localStorage.setItem('quickNotes', JSON.stringify(quickNotes));
    }

    // To-Do List Functions
    function initializeTodoList() {
        const todoList = document.getElementById('todoList');
        todoList.innerHTML = '';
        
        if (todos.length === 0) {
            // Add some default tasks
            const defaultTasks = [
                'Complete math assignment',
                'Read chapter 5',
                'Prepare for quiz',
                'Review notes'
            ];
            
            defaultTasks.forEach(task => {
                addTodoItem(task, false);
            });
        } else {
            todos.forEach(todo => {
                addTodoItem(todo.text, todo.completed);
            });
        }
    }

    function addTodo() {
        const todoInput = document.getElementById('todoInput');
        const text = todoInput.value.trim();
        
        if (text) {
            addTodoItem(text, false);
            todoInput.value = '';
            saveTodos();
        }
    }

    function addTodoItem(text, completed) {
        const todoList = document.getElementById('todoList');
        const todoId = Date.now();
        
        const todoItem = document.createElement('div');
        todoItem.className = `todo-item ${completed ? 'completed' : ''}`;
        todoItem.setAttribute('data-todo-id', todoId);
        
        todoItem.innerHTML = `
            <div class="todo-checkbox ${completed ? 'checked' : ''}" onclick="toggleTodo(${todoId})"></div>
            <div class="todo-text">${text}</div>
            <div class="todo-actions">
                <button class="todo-btn delete" onclick="deleteTodo(${todoId})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        
        todoList.appendChild(todoItem);
        
        // Add to todos array
        if (!todos.find(todo => todo.text === text)) {
            todos.push({
                id: todoId,
                text: text,
                completed: completed
            });
            saveTodos();
        }
    }

    function toggleTodo(todoId) {
        const todoItem = document.querySelector(`[data-todo-id="${todoId}"]`);
        const checkbox = todoItem.querySelector('.todo-checkbox');
        const todoText = todoItem.querySelector('.todo-text');
        
        // Toggle completed state
        todoItem.classList.toggle('completed');
        checkbox.classList.toggle('checked');
        
        // Update todos array
        const todoIndex = todos.findIndex(todo => todo.id === todoId);
        if (todoIndex !== -1) {
            todos[todoIndex].completed = !todos[todoIndex].completed;
            saveTodos();
        }
    }

    function deleteTodo(todoId) {
        if (confirm('Are you sure you want to delete this task?')) {
            // Remove from DOM
            const todoElement = document.querySelector(`[data-todo-id="${todoId}"]`);
            if (todoElement) {
                todoElement.remove();
            }
            
            // Remove from storage
            todos = todos.filter(todo => todo.id !== todoId);
            saveTodos();
            
            // Show empty state if no todos left
            const todoList = document.getElementById('todoList');
            if (todoList.children.length === 0) {
                // Add default tasks if empty
                const defaultTasks = [
                    'Complete math assignment',
                    'Read chapter 5',
                    'Prepare for quiz',
                    'Review notes'
                ];
                
                defaultTasks.forEach(task => {
                    addTodoItem(task, false);
                });
            }
        }
    }

    function saveTodos() {
        localStorage.setItem('todos', JSON.stringify(todos));
    }

    // Function to save image to server
    function saveFolderImage(folderId, imageSrc) {
        const formData = new FormData();
        formData.append('action', 'save_folder_image');
        formData.append('folder_id', folderId);
        formData.append('image_data', imageSrc);

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Folder image saved successfully');
            } else {
                console.error('Failed to save folder image');
            }
        })
        .catch(error => {
            console.error('Error saving folder image:', error);
        });
    }

    // Function to save folder name to server
    function saveFolderName(folderId, folderName) {
        const formData = new FormData();
        formData.append('action', 'save_folder_name');
        formData.append('folder_id', folderId);
        formData.append('folder_name', folderName);

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Folder name saved successfully');
            } else {
                console.error('Failed to save folder name');
            }
        })
        .catch(error => {
            console.error('Error saving folder name:', error);
        });
    }

    // Subject Modal Functionality
    let timerInterval;
    let timerSeconds = 25 * 60;
    let isTimerRunning = false;
    let notes = [];

    function openSubjectModal(subjectName, folderImage) {
        console.log('Opening modal for:', subjectName);
        
        // Update modal content
        document.getElementById('subjectModalTitle').textContent = subjectName;
        document.getElementById('subjectModalIcon').src = folderImage;
        
        // Reset and load data
        resetModalData();
        loadSampleData(subjectName);
        
        // Show modal
        document.getElementById('subjectModal').style.display = 'flex';
    }

    function closeSubjectModal() {
        document.getElementById('subjectModal').style.display = 'none';
        pauseTimer();
        resetModalData();
    }

    function resetModalData() {
        // Clear notes
        notes = [];
        document.getElementById('notesList').innerHTML = `
            <div class="empty-state">
                <i class="fas fa-sticky-note"></i>
                <p>No notes yet. Start typing above to create your first note!</p>
            </div>
        `;
        document.querySelector('.note-editor').innerHTML = '';
        
        // Reset assignments
        document.getElementById('assignmentsList').innerHTML = `
            <div class="empty-state">
                <i class="fas fa-tasks"></i>
                <p>No assignments for this subject.</p>
            </div>
        `;
        
        // Reset schedule
        document.getElementById('scheduleList').innerHTML = `
            <div class="empty-state">
                <i class="fas fa-clock"></i>
                <p>No classes scheduled for today.</p>
            </div>
        `;
        
        // Reset timer
        resetTimer();
    }

    function loadSampleData(subjectName) {
        // Sample assignments data
        const sampleAssignments = [
            { id: 1, title: 'Chapter 5 Exercises', dueDate: '2024-01-15', status: 'Not Started' },
            { id: 2, title: 'Research Paper', dueDate: '2024-01-20', status: 'In Progress' },
            { id: 3, title: 'Weekly Quiz', dueDate: '2024-01-12', status: 'Done' }
        ];

        // Sample schedule data
        const sampleSchedule = [
            { classType: 'Lecture', location: 'Room 301', startTime: '09:00', endTime: '10:30' },
            { classType: 'Lab', location: 'Science Building', startTime: '14:00', endTime: '16:00' }
        ];

        // Load sample assignments if none exist
        const assignmentsList = document.getElementById('assignmentsList');
        if (assignmentsList.querySelector('.empty-state')) {
            assignmentsList.innerHTML = '';
            sampleAssignments.forEach(assignment => {
                const assignmentItem = createAssignmentItem(assignment);
                assignmentsList.appendChild(assignmentItem);
            });
        }

        // Load sample schedule if none exist
        const scheduleList = document.getElementById('scheduleList');
        if (scheduleList.querySelector('.empty-state')) {
            scheduleList.innerHTML = '';
            sampleSchedule.forEach(schedule => {
                const scheduleItem = createScheduleItem(schedule);
                scheduleList.appendChild(scheduleItem);
            });
        }
    }

    function createAssignmentItem(assignment) {
        const div = document.createElement('div');
        div.className = 'assignment-item';
        
        const statusClass = `status-${assignment.status.toLowerCase().replace(' ', '-')}`;
        
        div.innerHTML = `
            <div class="assignment-info">
                <h4>${assignment.title}</h4>
                <p>Due: ${assignment.dueDate}</p>
            </div>
            <div class="assignment-status">
                <button class="status-badge ${statusClass}" onclick="updateAssignmentStatus(this, ${assignment.id})">
                    ${assignment.status}
                </button>
            </div>
        `;
        
        return div;
    }

    function createScheduleItem(schedule) {
        const div = document.createElement('div');
        div.className = 'schedule-item';
        
        div.innerHTML = `
            <div class="schedule-details">
                <h4>${schedule.classType}</h4>
                <p>${schedule.location}</p>
            </div>
            <div class="schedule-time">
                ${schedule.startTime} - ${schedule.endTime}
            </div>
        `;
        
        return div;
    }

    // Timer functionality
    function updateTimerDisplay() {
        const minutes = Math.floor(timerSeconds / 60);
        const seconds = timerSeconds % 60;
        document.getElementById('timerDisplay').textContent = 
            `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }

    function startTimer() {
        if (!isTimerRunning) {
            isTimerRunning = true;
            timerInterval = setInterval(() => {
                timerSeconds--;
                updateTimerDisplay();
                
                if (timerSeconds <= 0) {
                    clearInterval(timerInterval);
                    isTimerRunning = false;
                    alert('Study session completed! 🎉');
                    timerSeconds = 25 * 60;
                    updateTimerDisplay();
                }
            }, 1000);
        }
    }

    function pauseTimer() {
        clearInterval(timerInterval);
        isTimerRunning = false;
    }

    function resetTimer() {
        clearInterval(timerInterval);
        isTimerRunning = false;
        timerSeconds = 25 * 60;
        updateTimerDisplay();
    }

    // Edit note function
    function editNote(noteId) {
        const note = notes.find(n => n.id === noteId);
        if (note) {
            currentEditingNoteId = noteId;
            document.getElementById('editNoteTextarea').value = note.content;
            editNoteModal.style.display = 'flex';
        }
    }

    // Save edited note
    function saveEditedNote() {
        const editedContent = document.getElementById('editNoteTextarea').value.trim();
        if (editedContent) {
            // Update in notes array
            const noteIndex = notes.findIndex(note => note.id === currentEditingNoteId);
            if (noteIndex !== -1) {
                notes[noteIndex].content = editedContent;
                
                // Update in DOM
                const noteElement = document.querySelector(`[data-note-id="${currentEditingNoteId}"] .note-content`);
                if (noteElement) {
                    noteElement.innerHTML = editedContent;
                }
            }
            
            // Update in quick notes if it's a quick note
            const quickNoteIndex = quickNotes.findIndex(note => note.id === currentEditingNoteId);
            if (quickNoteIndex !== -1) {
                quickNotes[quickNoteIndex].content = editedContent;
                saveQuickNotes();
                
                // Update in DOM
                const quickNoteElement = document.querySelector(`[data-note-id="${currentEditingNoteId}"] .note-content div:last-child`);
                if (quickNoteElement) {
                    quickNoteElement.textContent = editedContent;
                }
            }
            
            editNoteModal.style.display = 'none';
            currentEditingNoteId = null;
        } else {
            alert('Note content cannot be empty');
        }
    }

    // Delete note function
    function deleteNote(noteId) {
        if (confirm('Are you sure you want to delete this note?')) {
            const noteElement = document.querySelector(`[data-note-id="${noteId}"]`);
            if (noteElement) {
                noteElement.remove();
            }
            
            // Remove from storage
            notes = notes.filter(note => note.id !== noteId);
            
            // Remove from quick notes if it's a quick note
            quickNotes = quickNotes.filter(note => note.id !== noteId);
            saveQuickNotes();
            
            // Show empty state if no notes left
            const notesList = document.getElementById('notesList');
            if (notesList.children.length === 0) {
                notesList.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-sticky-note"></i>
                        <p>No notes yet. Start typing above to create your first note!</p>
                    </div>
                `;
            }
            
            // Update quick notes list if needed
            const quickNotesList = document.getElementById('quickNotesList');
            if (quickNotesList && quickNotes.length === 0) {
                quickNotesList.innerHTML = `
                    <div class="notes-empty">
                        <i class="fas fa-sticky-note"></i>
                        <p>No quick notes yet. Add one above!</p>
                    </div>
                `;
            }
        }
    }

    // Update assignment status
    function updateAssignmentStatus(element, assignmentId) {
        const currentStatus = element.textContent.trim();
        let newStatus;
        
        // Cycle through statuses
        switch(currentStatus) {
            case 'Not Started':
                newStatus = 'In Progress';
                break;
            case 'In Progress':
                newStatus = 'Done';
                break;
            case 'Done':
                newStatus = 'Not Started';
                break;
            default:
                newStatus = 'Not Started';
        }
        
        // Update UI
        element.textContent = newStatus;
        element.className = `status-badge status-${newStatus.toLowerCase().replace(' ', '-')}`;
        
        console.log(`Assignment ${assignmentId} status updated to: ${newStatus}`);
    }
</script>
</body>
</html>
