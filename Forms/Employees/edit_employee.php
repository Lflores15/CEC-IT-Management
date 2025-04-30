<?php
session_start();
require_once "../../PHP/config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $emp_id = isset($_POST['emp_id']) ? intval($_POST['emp_id']) : 0;
    $emp_code = $_POST['emp_code'] ?? '';
    $username = $_POST['username'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $active = isset($_POST['active']) ? 1 : 0;

    if ($emp_id <= 0) {
        echo json_encode(["success" => false, "message" => "Missing employee ID."]);
        exit;
    }

    $sql = "UPDATE Employees SET emp_code=?, username=?, first_name=?, last_name=?, phone_number=?, active=? WHERE emp_id=?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["success" => false, "message" => "SQL prepare failed: " . $conn->error]);
        exit;
    }
    $stmt->bind_param("ssssssi", $emp_code, $username, $first_name, $last_name, $phone_number, $active, $emp_id);

    if ($stmt->execute()) {
        // Log to user_event_log.txt
        $logPath = __DIR__ . "/../../logs/user_event_log.txt";
        $user = $_SESSION['username'] ?? 'unknown';
        $timestamp = date("Y-m-d H:i:s");
        $logMsg = "$user\tUPDATE\t$timestamp\tUpdated employee: $emp_code\n";
        file_put_contents($logPath, $logMsg, FILE_APPEND);

        $debugLogMsg = "$user\tDEBUG\t$timestamp\tEMP_ID=$emp_id | CODE=$emp_code | USERNAME=$username | FIRST=$first_name | LAST=$last_name | PHONE=$phone_number | ACTIVE=$active\n";
        file_put_contents($logPath, $debugLogMsg, FILE_APPEND);

        echo json_encode(["success" => true, "message" => "Employee updated successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update employee."]);
    }

    $stmt->close();
    $conn->close();
}
?>