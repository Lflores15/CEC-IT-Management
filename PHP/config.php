<?php
// Load Environment Variables
$servername = getenv('MYSQL_SERVERNAME') ?: 'db';  // ✅ Use correct variable
$username = getenv('MYSQL_USER') ?: 'root';
$password = getenv('MYSQL_PASSWORD') ?: 'root';
$dbname = getenv('MYSQL_DATABASE') ?: 'cec_it_management';

// Create Database Connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check Connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Set Secure Encoding
$conn->set_charset("utf8mb4");

?>