<?php
require_once("../../PHP/config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $employee_id = $_POST["employee_id"];
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $login_id = $_POST["login_id"];
    $phone_number = $_POST["phone_number"];

    // Prepare and execute insert
    $stmt = $conn->prepare("INSERT INTO Employees (employee_id, first_name, last_name, login_id, phone_number) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("issss", $employee_id, $first_name, $last_name, $login_id, $phone_number);
    
    if ($stmt->execute()) {
        header("Location: employee_Dashboard.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>