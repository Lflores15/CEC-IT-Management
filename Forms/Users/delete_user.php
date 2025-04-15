<?php
session_start();
require_once "../../PHP/config.php";
require_once "../../includes/log_event.php";

// Ensure only admins can delete users
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== 'admin') {
    header("Location: ../Login/login.php");
    exit();
}

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);

    // Optional: Prevent deleting your own account
    if ($_SESSION["user_id"] == $user_id) {
        logUserEvent("DELETE_USER_FAIL", "Admin attempted to delete their own account", $_SESSION["username"]);
        header("Location: user_Dashboard.php?error=cannot_delete_self");
        exit();
    }

    // Fetch username before deletion
    $userQuery = $conn->prepare("SELECT username FROM Users WHERE user_id = ?");
    $userQuery->bind_param("i", $user_id);
    $userQuery->execute();
    $userQuery->bind_result($deleted_username);
    $userQuery->fetch();
    $userQuery->close();

    if (empty($deleted_username)) {
        logUserEvent("DELETE_USER_FAIL", "Attempted to delete non-existent user ID $user_id", $_SESSION["username"]);
        header("Location: user_Dashboard.php?error=user_not_found");
        exit();
    }

    // Delete user
    $stmt = $conn->prepare("DELETE FROM Users WHERE user_id = ?");
    if (!$stmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        logUserEvent("DELETE_USER", "User $deleted_username was deleted by admin", $_SESSION["username"]);
        $stmt->close();
        $conn->close();
        header("Location: user_Dashboard.php?deleted=1");
        exit();
    } else {
        logUserEvent("DELETE_USER_FAIL", "Failed to delete user $deleted_username", $_SESSION["username"]);
        $stmt->close();
        $conn->close();
        header("Location: user_Dashboard.php?error=delete_failed");
        exit();
    }
} else {
    logUserEvent("DELETE_USER_FAIL", "Attempted deletion without user ID in URL", $_SESSION["username"]);
    header("Location: user_Dashboard.php?error=no_id");
    exit();
}
?>