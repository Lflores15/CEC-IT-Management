<?php
session_start();
require_once "../../PHP/config.php";
require_once "../../includes/log_event.php"; 

// Ensure only admins can create users
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== 'admin') {
    header("Location: ../Login/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $role = $_POST["role"];

    if (empty($username) || empty($email) || empty($_POST["password"])) {
        header("Location: manage_users.php?error=missing_fields");
        exit();
    }

    // Check for existing username or email
    $checkStmt = $conn->prepare("SELECT user_id FROM `Users` WHERE username = ? OR email = ?");
    if (!$checkStmt) {
        die("Check prepare failed: (" . $conn->errno . ") " . $conn->error);
    }

    $checkStmt->bind_param("ss", $username, $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $checkStmt->close();
        header("Location: user_Dashboard.php?error=duplicate");
        exit();
    }
    $checkStmt->close();

    // Insert new user using correct column name: password_hash
    $stmt = $conn->prepare("INSERT INTO `Users` (`username`, `email`, `password_hash`, `role`) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        die("Insert prepare failed: (" . $conn->errno . ") " . $conn->error);
    }

    $stmt->bind_param("ssss", $username, $email, $password, $role);

    if ($stmt->execute()) {
        logEvent("CREATE_USER", "User '$username' was created by " . $_SESSION['username'], $_SESSION['username']); 
        $stmt->close();
        header("Location: user_Dashboard.php?created=1");
        exit();
    } else {
        die("Insert failed: (" . $stmt->errno . ") " . $stmt->error);
    }

    $conn->close();
}
?>