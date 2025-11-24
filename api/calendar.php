<?php
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

require_once '../models/Schedule.php';

$user_id = $_SESSION['user_id'];

// Get month and year from query params, default to current month
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Validate month and year
if ($month < 1 || $month > 12) {
    $month = date('n');
}
if ($year < 1970 || $year > 2100) {
    $year = date('Y');
}

$scheduleModel = new Schedule();
$schedules = $scheduleModel->getUserSchedule($user_id);

// Map numeric day to day_of_week string
$daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

// Number of days in the requested month
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

// Prepare calendar data
$calendar = [];

for ($day = 1; $day <= $daysInMonth; $day++) {
    $dateObj = DateTime::createFromFormat('Y-n-j', "$year-$month-$day");
    $dayOfWeek = $dateObj->format('l'); // Full day name e.g. Monday

    // Filter schedules for this day_of_week
    $classesForDay = array_filter($schedules, function($class) use ($dayOfWeek) {
        return $class['day_of_week'] === $dayOfWeek;
    });

    // Re-index array
    $classesForDay = array_values($classesForDay);

    $calendar[$day] = $classesForDay;
}

$response = [
    'month' => $month,
    'month_name' => date('F', mktime(0, 0, 0, $month, 10)),
    'year' => $year,
    'calendar' => $calendar,
];

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
