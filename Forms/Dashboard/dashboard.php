<?php
session_start();
require_once "../../PHP/config.php";
require_once "../../includes/navbar.php"; 

// Redirect to login if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../Login/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$username = $_SESSION["username"];
$emp_id = $_SESSION["emp_id"] ?? null; // Ensure $emp_id is correctly retrieved

// Fetch user information
$stmt = $conn->prepare("SELECT email, role FROM Users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($email, $role);
$stmt->fetch();
$stmt->close();

// Fetch device statistics
$totalDevicesQuery = $conn->query("SELECT COUNT(*) as total FROM Devices");
$activeDevicesQuery = $conn->query("SELECT COUNT(*) as active FROM Devices WHERE status = 'Active'");
$pendingReturnQuery = $conn->query("SELECT COUNT(*) as pending FROM Devices WHERE status = 'Pending Return'");

$totalDevices = $totalDevicesQuery->fetch_assoc()["total"];
$activeDevices = $activeDevicesQuery->fetch_assoc()["active"];
$pendingReturns = $pendingReturnQuery->fetch_assoc()["pending"];

// Fetch all devices
$stmt = $conn->prepare("SELECT device_name, asset_tag, category, status FROM Devices");
$stmt->execute();
$result = $stmt->get_result();

$devices = [];
while ($row = $result->fetch_assoc()) {
    $devices[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="../../Assets/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
        <!-- Stat Cards -->
        <section class="dashboard-cards">
            <div class="card total">
                <h3>Total Devices</h3>
                <p><?php echo $totalDevices; ?></p>
            </div>
            <div class="card active">
                <h3>Active Devices</h3>
                <p><?php echo $activeDevices; ?></p>
            </div>
            <div class="card pending">
                <h3>Pending Returns</h3>
                <p><?php echo $pendingReturns; ?></p>
            </div>
        </section>

        <!-- Chart.js Section -->
            <h3>Device Status Overview</h3>
            <canvas id="deviceChart" style="max-width: 600px; max-height: 400px;"></canvas>        
        <script>
            var ctx = document.getElementById('deviceChart').getContext('2d');
            var deviceChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Active', 'Pending Return', 'Total Devices'],
                    datasets: [{
                        data: [<?php echo $activeDevices; ?>, <?php echo $pendingReturns; ?>, <?php echo $totalDevices; ?>],
                        backgroundColor: ['#28a745', '#ff9800', '#17a2b8']
                    }]
                }
            });
        </script>
    </div>
    </section>

</body>
</html>