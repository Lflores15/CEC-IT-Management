<?php
session_start();
require_once "../../PHP/config.php";

// Redirect if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../Login/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$username = $_SESSION["username"];

// Fetch user info
$stmt = $conn->prepare("SELECT email, role, emp_id FROM Users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($email, $role, $emp_id);
$stmt->fetch();
$stmt->close();

// Device stats
$totalDevices     = $conn->query("SELECT COUNT(*) as total FROM Devices")->fetch_assoc()["total"];
$activeDevices    = $conn->query("SELECT COUNT(*) as active FROM Devices WHERE status = 'Active'")->fetch_assoc()["active"];
$pendingReturns   = $conn->query("SELECT COUNT(*) as pending FROM Devices WHERE status = 'Pending Return'")->fetch_assoc()["pending"];

// Assigned devices
$stmt = $conn->prepare("SELECT device_name, asset_tag, category, status FROM Devices WHERE assigned_to = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$result = $stmt->get_result();

$devices = [];
while ($row = $result->fetch_assoc()) {
    $devices[] = $row;
}
$stmt->close();
?>