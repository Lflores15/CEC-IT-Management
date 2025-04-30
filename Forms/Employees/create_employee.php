<?php
require_once("../../PHP/config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $employee_id = $_POST["employee_id"];  
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $username = $_POST["login_id"];     
    $phone_number = $_POST["phone_number"];

    $stmt = $conn->prepare("INSERT INTO Employees (emp_code, first_name, last_name, username, phone_number) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sssss", $employee_id, $first_name, $last_name, $username, $phone_number);

    if ($stmt->execute()) {
        header("Location: employee_Dashboard.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>