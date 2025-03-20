<?php
$host = "db"; // Use 'db' if running in Docker, 'localhost' for local MySQL
$dbname = "cec_it_management";
$username = getenv('MYSQL_USER'); 
$password = getenv('MYSQL_PASSWORD');  

try { 
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "Connected successfully!";
} catch (PDOException $e) {
    die("Could not connect to the database $dbname: " . $e->getMessage());
}
?>