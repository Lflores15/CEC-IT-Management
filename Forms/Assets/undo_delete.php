<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user'])) {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
        $_SESSION['user'] = $_SESSION['user_id'];
    } elseif (isset($_SESSION['login'])) {
        $_SESSION['user'] = $_SESSION['login'];
    } elseif (isset($_SESSION['username'])) {
        $_SESSION['user'] = $_SESSION['username'];
    } else {
        $_SESSION['user'] = 'unknown';
    }
}
require_once "../../PHP/config.php";

// Retrieve the last deleted devices from session
$lastDeleted = $_SESSION['last_deleted_devices'] ?? [];

if (empty($lastDeleted)) {
    echo "No recent deletions to undo.";
    exit;
}

try {
    $conn->begin_transaction();

    foreach ($lastDeleted as $device) {
        $stmt1 = $conn->prepare("INSERT INTO Devices (device_id, asset_tag, status, assigned_to) VALUES (?, ?, ?, ?)");
        $stmt1->bind_param(
            "isss",
            $device['device_id'],
            $device['asset_tag'],
            $device['status'],
            $device['assigned_to']
        );
        if (!$stmt1->execute()) {
            throw new Exception("Devices Insert Failed: " . $stmt1->error);
        }

        // Insert back into Laptops if needed
        if (isset($device['laptop'])) {
            $laptop = $device['laptop'];
            $stmt2 = $conn->prepare("INSERT INTO Laptops (device_id, cpu, ram, os, internet_policy) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt2) {
                throw new Exception("Laptops Prepare Failed: " . $conn->error);
            }
            $stmt2->bind_param(
                "isiss",
                $device['device_id'],
                $laptop['cpu'],
                $laptop['ram'],
                $laptop['os'],
                $laptop['internet_policy']
            );
            if (!$stmt2->execute()) {
                throw new Exception("Laptops Insert Failed: " . $stmt2->error);
            }
        }
    }

    $conn->commit();
    // Logging section
    $user = $_SESSION['login'] ?? $_SESSION['user'] ?? $_SESSION['username'] ?? 'unknown';
    $logPath = __DIR__ . "/../../Logs/device_event_log.txt";
    $logTime = date("Y-m-d H:i:s");

    foreach ($lastDeleted as $device) {
        $assetTag = $device['asset_tag'] ?? 'unknown';
        file_put_contents($logPath, "[$logTime] [UNDO DELETE] [$user] Restored asset_tag: $assetTag\n", FILE_APPEND);
    }
    unset($_SESSION['last_deleted_devices']);
    unset($_SESSION['undo_token']);
    echo "Undo successful. Devices restored.";
} catch (Exception $e) {
    $conn->rollback();
    echo "Undo failed: " . $e->getMessage();
}
?>