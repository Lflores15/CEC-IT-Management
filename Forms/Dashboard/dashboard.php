<?php
session_start();
require_once "../../PHP/config.php";
require_once "../../includes/navbar.php"; 

if (!isset($_SESSION["user_id"])) {
    header("Location: ../Login/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$username = $_SESSION["username"];
$emp_id = $_SESSION["emp_id"] ?? null;

$stmt = $conn->prepare("SELECT email, role FROM Users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($email, $role);
$stmt->fetch();
$stmt->close();

$query = "
    SELECT 
        (SELECT COUNT(*) FROM Devices) AS total, 
        (SELECT COUNT(*) FROM Devices WHERE status = 'Active') AS active, 
        (SELECT COUNT(*) FROM Decommissioned_Laptops) AS decommissioned, 
        (SELECT COUNT(*) FROM Devices WHERE status = 'Lost') AS lost, 
        (SELECT COUNT(*) FROM Devices WHERE status = 'Pending Return') AS pending, 
        (SELECT COUNT(*) FROM Devices WHERE status = 'Shelf') AS shelf
";

$result = $conn->query($query);
$counts = $result->fetch_assoc();

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

    <canvas id="deviceChart" style="max-width: 600px; max-height: 400px;"></canvas>        

    <script>
        var ctx = document.getElementById('deviceChart').getContext('2d');
        var deviceChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Active', 'Decommissioned', 'Lost', 'Pending Return', 'Shelf'],
                datasets: [{
                    data: [<?php echo $counts["active"]; ?>, <?php echo $counts["decommissioned"]; ?>, <?php echo $counts["lost"]; ?>, <?php echo $counts["pending"]; ?>, <?php echo $counts["shelf"]; ?>],
                    backgroundColor: ['#28a745', '#ff9800', '#dc3545', '#ffc107', '#17a2b8']
                }]
            }
        });
    </script>
</body>
</html>