<?php
// PHP/config.php

// 1) Use MYSQL_HOST (not MYSQL_SERVERNAME) for the Docker service name
$dbHost     = getenv('MYSQL_HOST') ?: 'db';

// 2) Use the “app” user if you set one, otherwise root
$dbUser     = getenv('MYSQL_USER') ?: 'root';

// 3) For root, use MYSQL_ROOT_PASSWORD, not MYSQL_PASSWORD
$dbPass     = getenv('MYSQL_PASSWORD')
             ?: getenv('MYSQL_ROOT_PASSWORD')
             ?: '';

// 4) Your database name
$dbName     = getenv('MYSQL_DATABASE') ?: 'cec_it_management';

// Create Database Connection
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

// Check Connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Set Secure Encoding
$conn->set_charset("utf8mb4");
?>
