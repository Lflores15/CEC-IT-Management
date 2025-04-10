<?php
session_start();
require_once "../../PHP/config.php";
require_once "../../includes/navbar.php";

// Admin-only access
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== 'admin') {
    header("Location: ../Login/login.php");
    exit();
}

$logFile = __DIR__ . "/../../logs/event_log.txt";
$logEntries = [];

if (file_exists($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach (array_reverse($lines) as $line) {
        preg_match('/\[(.*?)\] \[(.*?)\] \[(.*?)\] (.*)/', $line, $matches);
        if (count($matches) === 5) {
            $logEntries[] = [
                'timestamp' => $matches[1],
                'type' => $matches[2],
                'user' => $matches[3],
                'message' => $matches[4],
            ];
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

    <?php if (empty($logEntries)): ?>
        <p>No log entries found.</p>
    <?php else: ?>
        <table class="user-table" id="logTable">
            <thead>
                <tr>
                    <th onclick="sortTable(0)">Username</th>
                    <th onclick="sortTable(1)">Event Type</th>
                    <th onclick="sortTable(2)">Timestamp</th>
                    <th onclick="sortTable(3)">Message</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logEntries as $entry): ?>
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
</div>

<script>
function sortTable(colIndex) {
    const table = document.getElementById("logTable");
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