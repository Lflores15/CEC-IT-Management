<?php
function logEvent($eventType, $message, $username = 'SYSTEM') {
    $logFile = __DIR__ . '/../logs/event_log.txt';
    $timestamp = date("Y-m-d H:i:s");
    $logEntry = "[$timestamp] [$eventType] [$username] $message" . PHP_EOL;

    file_put_contents($logFile, $logEntry, FILE_APPEND);
}