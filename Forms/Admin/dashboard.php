<?php
session_start();
require_once "../../PHP/config.php";

// ✅ Redirect to login if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../Login/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$username = $_SESSION["username"];
$emp_id = ["emp_id"];

// ✅ Fetch user information from database
$stmt = $conn->prepare("SELECT email, role FROM Users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($email, $role);
$stmt->fetch();
$stmt->close();

// ✅ Fetch device statistics
$totalDevicesQuery = $conn->query("SELECT COUNT(*) as total FROM Devices");
$activeDevicesQuery = $conn->query("SELECT COUNT(*) as active FROM Devices WHERE status = 'Active'");
$pendingReturnQuery = $conn->query("SELECT COUNT(*) as pending FROM Devices WHERE status = 'Pending Return'");

$totalDevices = $totalDevicesQuery->fetch_assoc()["total"];
$activeDevices = $activeDevicesQuery->fetch_assoc()["active"];
$pendingReturns = $pendingReturnQuery->fetch_assoc()["pending"];

// ✅ Fetch assigned devices
$stmt = $conn->prepare("SELECT device_name, asset_tag, category, status FROM Devices WHERE assigned_to = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$result = $stmt->get_result();

$devices = [];
while ($row = $result->fetch_assoc()) {
    $devices[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- ✅ Chart.js -->
</head>
<body>
    <header>
        <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
        <a href="../Login/logout.php">Logout</a>
    </header>

    <section class="dashboard">
        <div class="stat-box">
            <h3>Total Devices</h3>
            <p><?php echo $totalDevices; ?></p>
        </div>
        <div class="stat-box">
            <h3>Active Devices</h3>
            <p><?php echo $activeDevices; ?></p>
        </div>
        <div class="stat-box">
            <h3>Pending Returns</h3>
            <p><?php echo $pendingReturns; ?></p>
        </div>
    </section>

    <section>
        <h3>Assigned Devices</h3>
        <?php if (empty($devices)) : ?>
            <p>No assigned devices.</p>
        <?php else : ?>
            <table>
                <tr>
                    <th>Device Name</th>
                    <th>Asset Tag</th>
                    <th>Category</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($devices as $device) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($device["device_name"]); ?></td>
                        <td><?php echo htmlspecialchars($device["asset_tag"]); ?></td>
                        <td><?php echo htmlspecialchars($device["category"]); ?></td>
                        <td><?php echo htmlspecialchars($device["status"]); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </section>

    <!-- ✅ Chart.js Section -->
    <section>
        <h3>Device Status Overview</h3>
        <canvas id="deviceChart"></canvas>
    </section>

    <script>
        var ctx = document.getElementById('deviceChart').getContext('2d');
        var deviceChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Active', 'Pending Return', 'Total Devices'],
                datasets: [{
                    data: [<?php echo $activeDevices; ?>, <?php echo $pendingReturns; ?>, <?php echo $totalDevices; ?>],
                    backgroundColor: ['#4CAF50', '#FF9800', '#2196F3']
                }]
            }
        });
    </script>

</body>
</html>