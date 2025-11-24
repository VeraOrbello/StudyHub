<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'studyhub');
define('DB_USER', 'root');
define('DB_PASS', '');

// App configuration
define('APP_NAME', 'StudyHub');
define('BASE_URL', 'http://localhost/StudyHub/');

// Start session
session_start();

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>