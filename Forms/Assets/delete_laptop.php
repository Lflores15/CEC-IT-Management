<?php
require_once "../../PHP/config.php";

$data = json_decode(file_get_contents("php://input"), true);
$deviceIds = $data['device_ids'] ?? [];

if (empty($deviceIds)) {
    echo "No device IDs provided.";
    exit;
}

$placeholders = implode(',', array_fill(0, count($deviceIds), '?'));
$types = str_repeat('i', count($deviceIds));


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user']) && isset($_SESSION['username'])) {
    $_SESSION['user'] = $_SESSION['username'];
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Store deleted device data for undo
    $lastDeleted = [];
    foreach ($deviceIds as $deviceId) {
        // Get device info from Devices table
        $stmtDev = $conn->prepare("SELECT * FROM Devices WHERE device_id = ?");
        $stmtDev->bind_param("i", $deviceId);
        $stmtDev->execute();
        $resultDev = $stmtDev->get_result();
        $deviceRow = $resultDev->fetch_assoc();
        if ($deviceRow) {
            // Get laptop info if exists
            $stmtLap = $conn->prepare("SELECT * FROM Laptops WHERE device_id = ?");
            $stmtLap->bind_param("i", $deviceId);
            $stmtLap->execute();
            $resultLap = $stmtLap->get_result();
            $laptopRow = $resultLap->fetch_assoc();
            if ($laptopRow) {
                $deviceRow['laptop'] = $laptopRow;
            }
            $lastDeleted[] = $deviceRow;
        }
    }
    $_SESSION['last_deleted_devices'] = $lastDeleted;
    $_SESSION['undo_token'] = bin2hex(random_bytes(8));

    // Delete from Laptops table first
    $stmt1 = $conn->prepare("DELETE FROM Laptops WHERE device_id IN ($placeholders)");
    $stmt1->bind_param($types, ...$deviceIds);
    $stmt1->execute();

    // Then delete from Devices table
    $stmt2 = $conn->prepare("DELETE FROM Devices WHERE device_id IN ($placeholders)");
    $stmt2->bind_param($types, ...$deviceIds);
    $stmt2->execute();

    $conn->commit();
    // Logging section
    $user = $_SESSION['user'] ?? 'unknown';
    $logPath = __DIR__ . "/../../Logs/device_event_log.txt";
    $logTime = date("Y-m-d H:i:s");

    foreach ($deviceIds as $id) {
        file_put_contents($logPath, "[$logTime] [DELETE] [$user] Deleted device_id: $id\n", FILE_APPEND);
    }
    echo "Successfully deleted selected laptops.";
} catch (Exception $e) {
    $conn->rollback();
    echo "Delete failed: " . $e->getMessage();
}
?>