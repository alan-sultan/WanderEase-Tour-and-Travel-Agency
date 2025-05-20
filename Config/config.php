<?php
// filepath: c:\Users\ahmed\2nd Semister php project\WanderEase-Tour-and-Travel-Agency-main\Backend\config.php

// Database configuration
define('DB_HOST', 'localhost'); // Replace with your database host
define('DB_USER', 'root');      // Replace with your database username
define('DB_PASS', '');          // Replace with your database password
define('DB_NAME', 'wander_ease'); // Replace with your database name

// Create a database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check the connection
if ($conn->connect_error) {
    error_log('Database connection failed: ' . $conn->connect_error, 3, 'error_log.txt');
    die('Database connection failed. Please try again later.');
}

// Set the character set to UTF-8 (optional, but recommended)
$conn->set_charset('utf8mb4');
?>
