<?php
session_start();
require_once "../../PHP/config.php";
require_once "../../includes/log_event.php"; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_POST["user_id"];
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $role = $_POST["role"];

    if (empty($username) || empty($email) || empty($role)) {
        logEvent("UPDATE_USER_FAIL", "Update failed: missing fields for user ID $user_id", $_SESSION["username"]);
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit();
    }

    $query = "UPDATE Users SET username = ?, email = ?, role = ? WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        logEvent("UPDATE_USER_FAIL", "Prepare failed for user ID $user_id: " . $conn->error, $_SESSION["username"]);
        echo json_encode(["success" => false, "message" => "Database error."]);
        exit();
    }

    $stmt->bind_param("sssi", $username, $email, $role, $user_id);

    if ($stmt->execute()) {
        logEvent("UPDATE_USER", "User ID $user_id updated to username '$username', email '$email', role '$role'", $_SESSION["username"]);
        echo json_encode(["success" => true, "message" => "User updated successfully."]);
    } else {
        logEvent("UPDATE_USER_FAIL", "Failed to update User ID $user_id: " . $stmt->error, $_SESSION["username"]);
        echo json_encode(["success" => false, "message" => "Failed to update user."]);
    }

    $stmt->close();
    $conn->close();
}
?>