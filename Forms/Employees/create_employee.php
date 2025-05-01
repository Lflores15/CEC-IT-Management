<?php
session_start();
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
        require_once "../../includes/log_event.php";
        $actor = $_SESSION['login'] ?? $_SESSION['username'] ?? $_SESSION['user'] ?? 'unknown';
        $message = "Employee '$employee_id' was created";
        logUserEvent("CREATE_EMPLOYEE", $message, $actor);
        echo json_encode(["success" => true, "message" => "Employee created successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
    }
    exit;
}
?>
