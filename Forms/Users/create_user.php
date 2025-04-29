<?php
session_start();
require_once "../../PHP/config.php";
require_once "../../includes/log_event.php"; 

// Ensure only admins can create users
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== 'Manager') {
    header("Location: ../Login/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["login"] ?? '');
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $role = $_POST["role"];

    $validRoles = ['Technician', 'Manager'];
    if (!in_array($role, $validRoles)) {
        die("Invalid role selected.");
    }

    

    // Check for existing username 
    $checkStmt = $conn->prepare("SELECT user_id FROM `Users` WHERE login = ?");
    if (!$checkStmt) {
        die("Check prepare failed: (" . $conn->errno . ") " . $conn->error);
    }

    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $checkStmt->close();
        header("Location: user_Dashboard.php?error=duplicate");
        exit();
    }
    $checkStmt->close();

    // Insert new user using correct column name: password_hash
    $stmt = $conn->prepare("INSERT INTO `Users` (`login`, `password_hash`, `role`) VALUES (?, ?, ?)");
    if (!$stmt) {
        die("Insert prepare failed: (" . $conn->errno . ") " . $conn->error);
    }

    $stmt->bind_param("sss", $username, $password, $role);

    if ($stmt->execute()) {
        logUserEvent("CREATE_USER", "User '$username' was created by"); 
        $stmt->close();
        header("Location: user_Dashboard.php?created=1");
        exit();
    } else {
        die("Insert failed: (" . $stmt->errno . ") " . $stmt->error);
    }

    $conn->close();
}
?>