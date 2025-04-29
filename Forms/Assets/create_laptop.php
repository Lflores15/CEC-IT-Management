<?php
require_once "../../PHP/config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $status = $_POST['status'] ?? '';
    $internet_policy = $_POST['internet_policy'] ?? '';
    $asset_tag = $_POST['asset_tag'] ?? '';
    $assigned_to = $_POST['assigned_to'] ?? '';
    $cpu = $_POST['cpu'] ?? null;
    $ram = $_POST['ram'] ?? null;
    $os = $_POST['os'] ?? '';

    if (!$status || !$internet_policy || !$asset_tag || $assigned_to === '' || !$os) {
        echo "missing_required_fields";
        exit;
    }

    // cpu and ram normalization for binding (cpu is now VARCHAR, ram is int or null)
    $conn->begin_transaction();

    try {
        // Insert into Devices
        $deviceStmt = $conn->prepare("INSERT INTO Devices (status, asset_tag, assigned_to) VALUES (?, ?, ?)");
        $deviceStmt->bind_param("sss", $status, $asset_tag, $assigned_to);
        if (!$deviceStmt->execute()) {
            throw new Exception("device_insert_failed: " . $deviceStmt->error);
        }
        $device_id = $conn->insert_id;
        $deviceStmt->close();

        // Insert into Laptops
        $laptopStmt = $conn->prepare("INSERT INTO Laptops (device_id, cpu, ram, os, internet_policy) VALUES (?, ?, ?, ?, ?)");
        if (!$laptopStmt) {
            throw new Exception("laptop_prepare_failed: " . $conn->error);
        }

        // Explicitly cast and safeguard CPU and RAM values
        $cpu_value = ($cpu !== null && $cpu !== '') ? $cpu : null;
        $ram_value = ($ram !== null && $ram !== '' && $ram !== 'N/A') ? (int)$ram : null;

        // Safeguard for N/A values
        if ($ram_value === null || $ram_value === 'N/A') {
            $ram_value = 0;
        }

        $laptopStmt->bind_param(
            "isiss",
            $device_id,
            $cpu_value,
            $ram_value,
            $os,
            $internet_policy
        );
        if (!$laptopStmt->execute()) {
            throw new Exception("laptop_insert_failed: " . $laptopStmt->error);
        }
        $laptopStmt->close();

        $conn->commit();
        echo "success";
    } catch (Exception $e) {
        $conn->rollback();
        echo $e->getMessage();
    }

    $conn->close();
} else {
    echo "invalid_request";
}
?>