<?php
function logEvent($eventType, $message, $username = 'SYSTEM') {
    // Define the logs directory relative to this file
    $logDir = __DIR__ . '/../logs';

    // Check if the logs directory exists; if not, create it with permissions 0777
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }

    // Define the log file within the logs directory
    $logFile = $logDir . '/event_log.txt';

    // Create the log entry with a timestamp
    $timestamp = date("Y-m-d H:i:s");
    $logEntry = "[$timestamp] [$eventType] [$username] $message" . PHP_EOL;

    // Append the log entry to the log file
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}
?>
