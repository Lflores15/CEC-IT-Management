<?php

function logUserEvent($eventType, $message, $username = null) {
    if ($username === null) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $username = $_SESSION['login'] ?? $_SESSION['user'] ?? 'SYSTEM';
    }
    logToFile(__DIR__ . '/../logs/user_event_log.txt', $eventType, $message, $username);
}

function logDeviceEvent($eventType, $message, $username = null) {
    if ($username === null) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $username = $_SESSION['login'] ?? $_SESSION['user'] ?? 'SYSTEM';
    }
    logToFile(__DIR__ . '/../logs/device_event_log.txt', $eventType, $message, $username);
}

function logToFile($filePath, $eventType, $message, $username) {
    $logTime = date("Y-m-d H:i:s");
    $logEntry = "[$logTime] [$eventType] [$username] $message\n";
    file_put_contents($filePath, $logEntry, FILE_APPEND);
}
