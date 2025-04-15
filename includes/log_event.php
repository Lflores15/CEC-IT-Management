<?php
function logUserEvent($eventType, $message, $username = 'SYSTEM') {
    logToFile(__DIR__ . '/../logs/user_event_log.txt', $eventType, $message, $username);
}

function logDeviceEvent($eventType, $message, $username = 'SYSTEM') {
    logToFile(__DIR__ . '/../logs/device_event_log.txt', $eventType, $message, $username);
}

function logToFile($filePath, $eventType, $message, $username) {
    $timestamp = date("Y-m-d H:i:s");
    $logEntry = "[$timestamp] [$eventType] [$username] $message" . PHP_EOL;
    file_put_contents($filePath, $logEntry, FILE_APPEND);
}
?>
