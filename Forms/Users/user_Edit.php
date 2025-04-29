<?php
session_start();
require_once "../../PHP/config.php";
require_once "../../includes/log_event.php"; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_POST["user_id"];
    $login = trim($_POST["login"]);
    $role = $_POST["role"];

    if (empty($login) || empty($role)) {
        logUserEvent("UPDATE_USER_FAIL", "Update failed: missing fields for user ID $user_id");
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit();
    }

    $query = "UPDATE Users SET login = ?, role = ? WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        logUserEvent("UPDATE_USER_FAIL", "Prepare failed for user ID $user_id: " . $conn->error);
        echo json_encode(["success" => false, "message" => "Database error."]);
        exit();
    }

    $stmt->bind_param("ssi", $login, $role, $user_id);

    if ($stmt->execute()) {
        logUserEvent("UPDATE_USER", "User '$login' updated to login '$login', role '$role'");
        echo json_encode(["success" => true, "message" => "User updated successfully."]);
    } else {
        logUserEvent("UPDATE_USER_FAIL", "Failed to update User '$login': " . $stmt->error, $_SESSION["login"]);
        echo json_encode(["success" => false, "message" => "Failed to update user."]);
    }

    $stmt->close();
    $conn->close();
}
?>