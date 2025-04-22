<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user']) && isset($_SESSION['username'])) {
    $_SESSION['user'] = $_SESSION['username'];
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
        // Insert back into Devices
        $stmt1 = $conn->prepare("INSERT INTO Devices (device_id, asset_tag, serial_number, category, brand, model, os, status, assigned_to, location, purchase_date, warranty_expiry, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt1->bind_param(
            "issssssssssss",
            $device['device_id'],
            $device['asset_tag'],
            $device['serial_number'],
            $device['category'],
            $device['brand'],
            $device['model'],
            $device['os'],
            $device['status'],
            $device['assigned_to'],
            $device['location'],
            $device['purchase_date'],
            $device['warranty_expiry'],
            $device['notes']
        );
        $stmt1->execute();

        // Insert back into Laptops if needed
        if (isset($device['laptop'])) {
            $laptop = $device['laptop'];
            $stmt2 = $conn->prepare("INSERT INTO Laptops (device_id, cpu, ram, storage, backup_type, internet_policy, backup_removed, sinton_backup, midland_backup, c2_backup, actions_needed) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param(
                "isiiissssss",
                $device['device_id'],
                $laptop['cpu'],
                $laptop['ram'],
                $laptop['storage'],
                $laptop['backup_type'],
                $laptop['internet_policy'],
                $laptop['backup_removed'],
                $laptop['sinton_backup'],
                $laptop['midland_backup'],
                $laptop['c2_backup'],
                $laptop['actions_needed']
            );
            $stmt2->execute();
        }
    }

    $conn->commit();
    // Logging section
    $user = $_SESSION['user'] ?? 'unknown';
    $logPath = __DIR__ . "/../../Logs/device_event_log.txt";
    $logTime = date("Y-m-d H:i:s");

    foreach ($lastDeleted as $device) {
        file_put_contents($logPath, "[$logTime] [UNDO DELETE] [$user] Restored device_id: {$device['device_id']}\n", FILE_APPEND);
    }
    unset($_SESSION['last_deleted_devices']);
    unset($_SESSION['undo_token']);
    echo "Undo successful. Devices restored.";
} catch (Exception $e) {
    $conn->rollback();
    echo "Undo failed: " . $e->getMessage();
}
?>