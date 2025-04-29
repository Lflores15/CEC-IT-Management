<?php
session_start();
date_default_timezone_set('America/Chicago');
require_once "../../PHP/config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $device_id = $_POST['device_id'] ?? null;
    $event_type = $_POST['event_type'] ?? null;
    $memo = $_POST['memo'] ?? null;

    if ($device_id && $event_type && $memo) {
        $logEntry = date("Y-m-d") . " | " . date("h:i:s A") . " | Asset Tag: $device_id | Type: $event_type | Memo: $memo" . PHP_EOL;
        $logFile = realpath(__DIR__ . '/../../logs') . "/manual_event_log.txt";

        if (@file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX)) {
            $_SESSION['log_message'] = "Event logged to file.";
        } else {
            $_SESSION['log_message'] = "Failed to write log to file.";
        }
    } else {
        $_SESSION['log_message'] = "Missing required fields.";
    }

    header("Location: laptop_Dashboard.php");
    exit();
}
?>