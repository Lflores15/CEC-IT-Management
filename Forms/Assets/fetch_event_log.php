<?php
header('Content-Type: application/json');

$requestedTag = $_GET['asset_tag'] ?? null;
$logPath = realpath(__DIR__ . '/../../logs/manual_event_log.txt');

if (!$logPath || !file_exists($logPath)) {
    echo json_encode([]);
    exit;
}

$lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Debug output: log the requested asset tag and the raw log file contents
error_log("Requested asset tag: " . $requestedTag);
error_log("Raw log file contents:");
foreach ($lines as $logLine) {
    error_log($logLine);
}
$logs = [];

foreach ($lines as $line) {
    // Expected format: YYYY-MM-DD | HH:MM:SS AM/PM | Asset Tag: xxx | Type: EVENT | Memo: MESSAGE
    $parts = explode(' | ', $line);
    if (count($parts) < 5) continue;

    $date = trim($parts[0]);
    $time = trim($parts[1]);

    $tagPart = $parts[2] ?? '';
    $assetTag = trim(str_replace("Asset Tag:", "", $tagPart));

    $typePart = $parts[3] ?? '';
    $eventType = trim(str_replace("Type:", "", $typePart));

    $memoPart = $parts[4] ?? '';
    $memo = trim(str_replace("Memo:", "", $memoPart));

    if ($requestedTag && $requestedTag !== $assetTag) continue;

    $logs[] = [
        'date' => $date,
        'time' => $time,
        'event_type' => $eventType,
        'memo' => $memo
    ];
}

echo json_encode($logs);