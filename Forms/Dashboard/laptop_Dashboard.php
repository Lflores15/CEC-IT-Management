<?php
session_start();
require_once "../../PHP/config.php";
require_once "../../includes/navbar.php";

// Redirect to login if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../Login/login.php");
    exit();
}

// Fetch laptops from the Devices table that are categorized as 'laptop'
// and join additional details from Laptops and Decommissioned_Laptops tables.
$query = "
    SELECT d.device_id, d.device_name, d.asset_tag, d.serial_number, d.brand, d.model, d.os, 
           d.cpu, d.ram, d.storage, d.status, d.assigned_to, d.location, d.purchase_date, d.warranty_expiry, d.notes,
           l.backup_type, l.internet_policy, l.backup_removed, l.sinton_backup, l.midland_backup, l.c2_backup, l.actions_needed,
           dl.broken, dl.duplicate, dl.decommission_status, dl.additional_notes AS decommission_notes
    FROM Devices d
    LEFT JOIN Laptops l ON d.device_id = l.device_id
    LEFT JOIN Decommissioned_Laptops dl ON l.id = dl.laptop_id
    WHERE d.category = 'laptop'
    ORDER BY d.device_name
";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$laptops = [];
while ($row = $result->fetch_assoc()) {
    $laptops[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laptops Dashboard</title>
    <link rel="stylesheet" href="../../Assets/styles.css">
</head>
<body>
    <div class="main-content">
        <h2>Laptops Dashboard</h2>

        <!-- Table for Laptops -->
        <table class="device-table">
            <thead>
                <tr>
                    <th>Device Name</th>
                    <th>Asset Tag</th>
                    <th>Serial Number</th>
                    <th>Brand</th>
                    <th>Model</th>
                    <th>OS</th>
                    <th>CPU</th>
                    <th>RAM</th>
                    <th>Storage</th>
                    <th>Backup Type</th>
                    <th>Internet Policy</th>
                    <th>Status</th>
                    <th>Decommissioned</th>
                    <th>Decommission Notes</th>
                    <th>Location</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($laptops)) : ?>
                    <tr>
                        <td colspan="16">No laptops found.</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($laptops as $laptop) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($laptop["device_name"]); ?></td>
                            <td><?php echo htmlspecialchars($laptop["asset_tag"]); ?></td>
                            <td><?php echo htmlspecialchars($laptop["serial_number"]); ?></td>
                            <td><?php echo htmlspecialchars($laptop["brand"]); ?></td>
                            <td><?php echo htmlspecialchars($laptop["model"]); ?></td>
                            <td><?php echo htmlspecialchars($laptop["os"]); ?></td>
                            <td><?php echo htmlspecialchars($laptop["cpu"]); ?></td>
                            <td><?php echo htmlspecialchars($laptop["ram"]); ?></td>
                            <td><?php echo htmlspecialchars($laptop["storage"]); ?></td>
                            <td><?php echo htmlspecialchars($laptop["backup_type"]); ?></td>
                            <td><?php echo htmlspecialchars($laptop["internet_policy"]); ?></td>
                            <td><?php echo htmlspecialchars($laptop["status"]); ?></td>
                            <td><?php echo ($laptop["decommission_status"] && $laptop["decommission_status"] != 'Decommissioned') ? htmlspecialchars($laptop["decommission_status"]) : "No"; ?></td>
                            <td><?php echo htmlspecialchars($laptop["decommission_notes"] ?? "N/A"); ?></td>
                            <td><?php echo htmlspecialchars($laptop["location"]); ?></td>
                            <td>
                                <a href="edit_laptop.php?id=<?php echo $laptop['device_id']; ?>">Edit</a> |
                                <a href="delete_laptop.php?id=<?php echo $laptop['device_id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>