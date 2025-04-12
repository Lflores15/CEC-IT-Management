<?php
// Ensure you're requiring the config file correctly first:
require_once(__DIR__ . "/../../PHP/config.php");
include(__DIR__ . "/../../includes/navbar.php");


$query = "
    SELECT 
        d.status,
        l.internet_policy,
        d.asset_tag,
        e.login_id AS login,
        e.first_name,
        e.last_name,
        e.phone_number, 
        l.cpu,
        l.ram,
        d.os
    FROM Devices d
    LEFT JOIN Laptops l ON d.device_id = l.device_id
    LEFT JOIN Employees e ON d.assigned_to = e.emp_id
    WHERE d.category = 'laptop'
    ORDER BY d.asset_tag
";


$result = $conn->query($query);
if (!$result) {
    die("SQL Error: " . $conn->error);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laptops Dashboard</title>
    <link rel="stylesheet" href="../../Assets/styles.css">
</head>
<body>
    <!-- Include your navbar or sidebar if needed -->
    <?php include(__DIR__ . "/../../includes/navbar.php"); ?>

    <div class="asset-content">
        <h2>Laptops</h2>
        <table id="laptop-table" class="device-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>InternetPolicy</th>
                    <th>Asset Tag</th>
                    <th>Login</th>
                    <th>First</th>
                    <th>Last</th>
                    <th>Contact</th>
                    <th>CPU</th>
                    <th>RAM</th>
                    <th>OS</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()) : ?>
                <tr class="laptop-row" data-assetid="<?php echo $row['asset_tag']; ?>">
                    <td><?php echo $row['status']; ?></td>
                    <td><?php echo $row['internet_policy']; ?></td>
                    <td><?php echo $row['asset_tag']; ?></td>
                    <td><?php echo $row['login']; ?></td>
                    <td><?php echo $row['first_name']; ?></td>
                    <td><?php echo $row['last_name']; ?></td>
                    <td><?php echo $row['phone_number']; ?></td>
                    <td><?php echo $row['cpu']; ?></td>
                    <td><?php echo $row['ram']; ?></td>
                    <td><?php echo $row['os']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <!-- Temporarily remove any code related to the asset detail panel -->
    <script src="../../Assets/script.js"></script>
</body>
</html>