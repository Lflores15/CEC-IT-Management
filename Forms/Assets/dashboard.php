<?php
session_start();
require_once "../../PHP/config.php";
require_once "../../includes/navbar.php"; 

if (!isset($_SESSION["user_id"])) {
    header("Location: ../Login/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$username = $_SESSION["login"];
$emp_id = $_SESSION["emp_id"] ?? null;


$sql = "SELECT login, role FROM Users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($login, $role);
$stmt->fetch();
$stmt->close();


$query = "
    SELECT 
        (SELECT COUNT(*) FROM Devices) AS total, 
        (SELECT COUNT(*) FROM Devices WHERE status = 'Active') AS active, 
        (SELECT COUNT(*) FROM Devices WHERE status = 'Decommissioned') AS decommissioned, 
        (SELECT COUNT(*) FROM Devices WHERE status = 'Lost') AS lost, 
        (SELECT COUNT(*) FROM Devices WHERE status = 'Pending Return') AS pending, 
        (SELECT COUNT(*) FROM Devices WHERE status = 'Shelf') AS shelf
";
$result = $conn->query($query);
if (!$result) {
    die("Dashboard counts query failed: (" . $conn->errno . ") " . $conn->error);
}
$counts = $result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="/Assets/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-wrapper">
      <div class="dashboard-left">
        <h2>Device Status Overview</h2>
        <section class="dashboard-cards">
            <div class="card total">
                <h3>Total Devices</h3>
                <p><?php echo $counts["total"]; ?></p>
            </div>
            <div class="card active">
                <h3>Active Devices</h3>
                <p><?php echo $counts["active"]; ?></p>
            </div>
            <div class="card decommissioned">
                <h3>Decommissioned</h3>
                <p><?php echo $counts["decommissioned"]; ?></p>
            </div>
            <div class="card lost">
                <h3>Lost Devices</h3>
                <p><?php echo $counts["lost"]; ?></p>
            </div>
            <div class="card pending">
                <h3>Pending Return</h3>
                <p><?php echo $counts["pending"]; ?></p>
            </div>
            <div class="card shelf">
                <h3>On Shelf</h3>
                <p><?php echo $counts["shelf"]; ?></p>
            </div>
        </section>
        <section class="recent-activity-graph-combo">
          <div class="log-table">
            <h3>Recent Activity</h3>
            <table class="device-table">
              <thead>
                <tr>
                  <th>Username</th>
                  <th>Event Type</th>
                  <th>Timestamp</th>
                  <th>Message</th>
                </tr>
              </thead>
              <tbody>
<?php
$logFile = __DIR__ . '/../../logs/device_event_log.txt';
if (file_exists($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $recentLines = array_reverse($lines);
    $counter = 0;
    foreach ($recentLines as $line) {
        if ($counter >= 10) break;
        preg_match('/\[(.*?)\] \[(.*?)\] \[(.*?)\] (.*)/', $line, $matches);
        if (count($matches) === 5) {
            $timestamp = $matches[1];
            $eventType = $matches[2];
            $username = $matches[3];
            $message = $matches[4];
            echo "<tr>
                    <td>" . htmlspecialchars($username) . "</td>
                    <td>" . htmlspecialchars($eventType) . "</td>
                    <td>" . htmlspecialchars($timestamp) . "</td>
                    <td>" . htmlspecialchars($message) . "</td>
                  </tr>";
            $counter++;
        }
    }
    if ($counter === 0) {
        echo "<tr><td colspan='4'>No recent activity found.</td></tr>";
    }
} else {
    echo "<tr><td colspan='4'>No recent activity found.</td></tr>";
}
?>
              </tbody>
            </table>
          </div>
          <div class="dashboard-right">
            <div class="dashboard-graph">
              <h3>Asset Distribution</h3>
              <canvas id="deviceChart" style="width: 280px; height: 280px;"></canvas>
            </div>
          </div>
        </section>
      </div>
    </div>

    <script>
        var ctx = document.getElementById('deviceChart').getContext('2d');
        var deviceChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Active', 'Decommissioned', 'Lost', 'Pending Return', 'Shelf'],
                datasets: [{
                    data: [<?php echo $counts["active"]; ?>, <?php echo $counts["decommissioned"]; ?>, <?php echo $counts["lost"]; ?>, <?php echo $counts["pending"]; ?>, <?php echo $counts["shelf"]; ?>],
                    backgroundColor: ['#28a745', '#606162', '#dc3545', '#ffc107', '#17a2b8']
                }]
            }
        });
    </script>
</body>
</html>