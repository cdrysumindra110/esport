<?php
// config.php - Simple Database Connection
session_start();
// Database credentials
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'esport';

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, 'utf8mb4');

// Optional: Set timezone
date_default_timezone_set('Asia/Kathmandu');