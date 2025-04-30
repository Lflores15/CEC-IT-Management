<?php
require_once("../../PHP/config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (
        !isset($_POST["emp_code"]) ||
        !isset($_POST["first_name"]) ||
        !isset($_POST["last_name"]) ||
        (!isset($_POST["login_id"]) && !isset($_POST["username"])) ||
        !isset($_POST["phone_number"])
    ) {
        echo json_encode(["success" => false, "message" => "Missing required fields."]);
        exit;
    }

    $employee_id = $_POST["emp_code"];  
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $username = $_POST["username"] ?? $_POST["login_id"] ?? null;
    if (!$username) {
        echo json_encode(["success" => false, "message" => "Username is required."]);
        exit;
    }
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