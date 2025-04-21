<?php
session_start();
require_once "../../PHP/config.php";
require_once "../../includes/navbar.php";

// Admin-only access
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== 'admin') {
    header("Location: ../Login/login.php");
    exit();
}

$userlogFile = __DIR__ . "/../../logs/user_event_log.txt";
$devicelogFile = __DIR__ . "/../../logs/device_event_log.txt";
$userLogEntries = [];
$deviceLogEntries = [];

// Parse both user and device event logs
$logFiles = [
    $userlogFile => 'user_event_log.txt',
    $devicelogFile => 'device_event_log.txt'
];

foreach ($logFiles as $file => $label) {
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach (array_reverse($lines) as $line) {
            preg_match('/\[(.*?)\] \[(.*?)\] \[(.*?)\] (.*)/', $line, $matches);
            if (count($matches) === 5) {
                $entry = [
                    'timestamp' => $matches[1],
                    'type' => $matches[2],
                    'user' => $matches[3],
                    'message' => $matches[4],
                ];
                if ($label === 'user_event_log.txt') {
                    $userLogEntries[] = $entry;
                } else {
                    $deviceLogEntries[] = $entry;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Logs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/Assets/styles.css">
</head>
<body>
<div class="asset-content-user">
    <h2>System Event Logs</h2>

    <?php if (!empty($userLogEntries)): ?>
        <h3>User Event Logs</h3>
        <table class="user-table" id="userLogTable">
            <thead>
                <tr>
                    <th onclick="sortTable(0, 'userLogTable')">Username</th>
                    <th onclick="sortTable(1, 'userLogTable')">Event Type</th>
                    <th onclick="sortTable(2, 'userLogTable')">Timestamp</th>
                    <th onclick="sortTable(3, 'userLogTable')">Message</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($userLogEntries as $entry): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($entry['user']); ?></td>
                        <td><?php echo htmlspecialchars($entry['type']); ?></td>
                        <td><?php echo htmlspecialchars($entry['timestamp']); ?></td>
                        <td><?php echo htmlspecialchars($entry['message']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if (!empty($deviceLogEntries)): ?>
        <h3>Device Event Logs</h3>
        <table class="user-table" id="deviceLogTable">
            <thead>
                <tr>
                    <th onclick="sortTable(0, 'deviceLogTable')">Username</th>
                    <th onclick="sortTable(1, 'deviceLogTable')">Event Type</th>
                    <th onclick="sortTable(2, 'deviceLogTable')">Timestamp</th>
                    <th onclick="sortTable(3, 'deviceLogTable')">Message</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deviceLogEntries as $entry): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($entry['user']); ?></td>
                        <td><?php echo htmlspecialchars($entry['type']); ?></td>
                        <td><?php echo htmlspecialchars($entry['timestamp']); ?></td>
                        <td><?php echo htmlspecialchars($entry['message']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if (empty($userLogEntries) && empty($deviceLogEntries)): ?>
        <p>No log entries found.</p>
    <?php endif; ?>
</div>

<script>
function sortTable(colIndex, tableId) {
    const table = document.getElementById(tableId);
    const rows = Array.from(table.rows).slice(1); // exclude header
    let asc = table.getAttribute("data-sort-dir") !== "asc";
    rows.sort((a, b) => {
        const A = a.cells[colIndex].innerText.toLowerCase();
        const B = b.cells[colIndex].innerText.toLowerCase();
        return asc ? A.localeCompare(B) : B.localeCompare(A);
    });
    rows.forEach(row => table.tBodies[0].appendChild(row));
    table.setAttribute("data-sort-dir", asc ? "asc" : "desc");
}
</script>
</body>
</html>