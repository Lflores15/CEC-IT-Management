<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../../PHP/config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../Login/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$username = trim($_POST["username"]);
$password = trim($_POST["password"]);

// Validate input
if (empty($username)) {
    die("Username cannot be empty.");
}

// If password is provided, hash it and update both username and password
if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE Users SET login = ?, password_hash = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $username, $hashed_password, $user_id);
} else {
    // Only update username
    $stmt = $conn->prepare("UPDATE Users SET login = ? WHERE user_id = ?");
    $stmt->bind_param("si", $username, $user_id);
}

if ($stmt->execute()) {
    $_SESSION["login"] = $username;
    $_SESSION["username"] = $username;

    require_once "../../includes/log_event.php";
    logUserEvent("UPDATE_PROFILE", "Updated profile username to '$username'" . (!empty($password) ? " and updated password." : "."), $username);

    header("Location: profile_Dashboard.php?success=1");
    exit();
} else {
    echo "Update failed: " . $stmt->error;
}
?>