<?php

function logUserEvent($eventType, $message, $username = null) {
    if ($username === null) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $username = $_SESSION['login'] ?? $_SESSION['user'] ?? 'unknown';
    }
    $logPath = getenv('USER_LOG_PATH') ?: __DIR__ . '/../logs/user_event_log.txt';
    logToFile($logPath, $eventType, $message, $username);
}

function logDeviceEvent($eventType, $message, $username = null) {
    if ($username === null) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $username = $_SESSION['login'] ?? $_SESSION['user'] ?? 'unknown';
    }

    $logPath = getenv('DEVICE_LOG_PATH') ?: __DIR__ . '/../logs/device_event_log.txt';
    logToFile($logPath, $eventType, $message, $username);
}

/**
 * Core logging function. Ensures the target directory exists
 * before appending the timestamped entry.
 *
 * @param string $filePath   Full path to the log file
 * @param string $eventType
 * @param string $message
 * @param string $username
 */
function logToFile($filePath, $eventType, $message, $username) {
    // 1) Make sure the logs directory exists
    $logDir = dirname($filePath);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    // 2) Build and write the log entry
    $timestamp = date("Y-m-d H:i:s");
    $logEntry  = "[$timestamp] [$eventType] [$username] $message" . PHP_EOL;
    file_put_contents($filePath, $logEntry, FILE_APPEND | LOCK_EX);
}
