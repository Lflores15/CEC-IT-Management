<?php
/**
 * Log helpers: user events and device events.
 * Auto-creates the logs directory if missing.
 */

/**
 * Log a user-related event.
 *
 * @param string $eventType  A short code for the event (e.g. "LOGIN_SUCCESS")
 * @param string $message    Detailed message about what happened
 * @param string $username   Who triggered the event (defaults to SYSTEM)
 */
function logUserEvent($eventType, $message, $username = 'SYSTEM') {
    logToFile(__DIR__ . '/../logs/user_event_log.txt', $eventType, $message, $username);
}

/**
 * Log a device-related event.
 *
 * @param string $eventType
 * @param string $message
 * @param string $username
 */
function logDeviceEvent($eventType, $message, $username = 'SYSTEM') {
    logToFile(__DIR__ . '/../logs/device_event_log.txt', $eventType, $message, $username);
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
