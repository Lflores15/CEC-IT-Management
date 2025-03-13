<?php
session_start();
require_once "../../PHP/config.php";
require_once "../../includes/navbar.php"; 

// Fetch all devices grouped by category
$query = "SELECT device_name, asset_tag, category, status FROM Devices ORDER BY category, device_name";
$result = $conn->query($query);

$devicesByCategory = [];
while ($row = $result->fetch_assoc()) {
    $devicesByCategory[$row['category']][] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Dashboard | CEC-IT</title>
    <link rel="stylesheet" href="../../Assets/styles.css">
</head>
<body>

    <div class="main-content">
        <h2>Asset Dashboard</h2>

        <!-- Generate Tables for Each Category -->
        <?php foreach ($devicesByCategory as $category => $devices): ?>
            <h3 class="category-title"><?php echo htmlspecialchars($category); ?></h3>

            <table class="device-table <?php echo strtolower(str_replace(' ', '-', $category)); ?>">
                <thead>
                    <tr>
                        <th>Device Name</th>
                        <th>Asset Tag</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($devices as $device): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($device["device_name"]); ?></td>
                            <td><?php echo htmlspecialchars($device["asset_tag"]); ?></td>
                            <td><?php echo htmlspecialchars($device["status"]); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    </div>

    <script src="../assets/script.js"></script>
</body>
</html>