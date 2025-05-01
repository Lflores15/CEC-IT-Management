<?php
session_start();
require_once "../../PHP/config.php";

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $emp_id = isset($_POST['emp_id']) ? intval($_POST['emp_id']) : 0;
    $emp_code = $_POST['emp_code'] ?? '';
    $username = $_POST['username'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $active = isset($_POST['active']) ? 1 : 0;

    if (empty($emp_code)) {
        echo json_encode(["success" => false, "message" => "Missing employee code."]);
        exit;
    }

    $sql = "UPDATE Employees SET emp_code=?, username=?, first_name=?, last_name=?, phone_number=?, active=? WHERE emp_code=?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["success" => false, "message" => "SQL prepare failed: " . $conn->error]);
        exit;
    }
    $stmt->bind_param("ssssssi", $emp_code, $username, $first_name, $last_name, $phone_number, $active, $emp_code);

    if ($stmt->execute()) {
        require_once "../../includes/log_event.php";
        $actor = $_SESSION['login'] ?? $_SESSION['username'] ?? $_SESSION['user'] ?? 'unknown';
        $message = "Employee '$emp_code' updated: username='$username', first='$first_name', last='$last_name', phone='$phone_number', active=$active";
        logUserEvent("UPDATE_EMPLOYEE", $message, $actor);
        echo json_encode(["success" => true, "message" => "Employee updated successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update employee."]);
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>