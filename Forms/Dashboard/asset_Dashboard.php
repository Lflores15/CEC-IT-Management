<?php
session_start();
require_once "../../PHP/config.php";
require_once "../../includes/navbar.php"; 

// Redirect to login if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../Login/login.php");
    exit();
}

// Fetch assets grouped by category
$devices = [
    'laptops' => [],
    'desktops' => [],
    'iPhones' => [],
    'tablets' => []
];

$query = "
    SELECT d.device_id, d.device_name, d.asset_tag, d.serial_number, d.category, d.brand, d.model, d.os, 
           d.cpu, d.ram, d.storage, d.status, d.assigned_to, d.location, d.purchase_date, d.warranty_expiry, d.notes,
           l.backup_type, l.internet_policy, l.backup_removed, l.sinton_backup, l.midland_backup, l.c2_backup, l.actions_needed,
           dl.broken, dl.duplicate, dl.decommission_status, dl.additional_notes AS decommission_notes,
           iph.responsible_party AS iphone_responsible, iph.carrier AS iphone_carrier, iph.phone_number AS iphone_number, iph.previous_owner AS iphone_prev_owner, iph.notes AS iphone_notes,
           t.responsible_party AS tablet_responsible, t.type AS tablet_type, t.carrier AS tablet_carrier, t.phone_number AS tablet_number, t.imei AS tablet_imei, t.notes AS tablet_notes
    FROM Devices d
    LEFT JOIN Laptops l ON d.device_id = l.device_id
    LEFT JOIN Decommissioned_Laptops dl ON l.id = dl.laptop_id
    LEFT JOIN iPhones iph ON d.device_id = iph.device_id
    LEFT JOIN Tablets t ON d.device_id = t.device_id
    ORDER BY d.category, d.device_name
";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    if ($row['category'] === 'laptop') {
        $devices['laptops'][] = $row;
    } elseif ($row['category'] === 'desktop') {
        $devices['desktops'][] = $row;
    } elseif ($row['category'] === 'iPhone') {
        $devices['iPhones'][] = $row;
    } elseif ($row['category'] === 'tablet') {
        $devices['tablets'][] = $row;
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Dashboard</title>
    <link rel="stylesheet" href="../../Assets/styles.css">
</head>
<body>
    <div class="main-content">
        <h2>Asset Dashboard</h2>

        <!-- Laptop Table -->
        <section>
            <h3>Laptops</h3>
            <table>
                <tr>
                    <th>Device Name</th>
                    <th>Asset Tag</th>
                    <th>CPU</th>
                    <th>RAM</th>
                    <th>Storage</th>
                    <th>Backup Type</th>
                    <th>Internet Policy</th>
                    <th>Status</th>
                    <th>Decommissioned</th>
                    <th>Notes</th>
                </tr>
                <?php foreach ($devices['laptops'] as $device) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($device["device_name"]); ?></td>
                        <td><?php echo htmlspecialchars($device["asset_tag"]); ?></td>
                        <td><?php echo htmlspecialchars($device["cpu"]); ?></td>
                        <td><?php echo htmlspecialchars($device["ram"]); ?></td>
                        <td><?php echo htmlspecialchars($device["storage"]); ?></td>
                        <td><?php echo htmlspecialchars($device["backup_type"]); ?></td>
                        <td><?php echo htmlspecialchars($device["internet_policy"]); ?></td>
                        <td><?php echo htmlspecialchars($device["status"]); ?></td>
                        <td><?php echo $device["decommission_status"] ? "Yes" : "No"; ?></td>
                        <td><?php echo htmlspecialchars($device["decommission_notes"]); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </section>

        <!-- iPhones Table -->
        <section>
            <h3>iPhones</h3>
            <table>
                <tr>
                    <th>Device Name</th>
                    <th>Asset Tag</th>
                    <th>Responsible Party</th>
                    <th>Carrier</th>
                    <th>Phone Number</th>
                    <th>Previous Owner</th>
                    <th>Notes</th>
                </tr>
                <?php foreach ($devices['iPhones'] as $device) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($device["device_name"]); ?></td>
                        <td><?php echo htmlspecialchars($device["asset_tag"]); ?></td>
                        <td><?php echo htmlspecialchars($device["iphone_responsible"]); ?></td>
                        <td><?php echo htmlspecialchars($device["iphone_carrier"]); ?></td>
                        <td><?php echo htmlspecialchars($device["iphone_number"]); ?></td>
                        <td><?php echo htmlspecialchars($device["iphone_prev_owner"]); ?></td>
                        <td><?php echo htmlspecialchars($device["iphone_notes"]); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </section>

        <!-- Tablets Table -->
        <section>
            <h3>Tablets</h3>
            <table>
                <tr>
                    <th>Device Name</th>
                    <th>Asset Tag</th>
                    <th>Responsible Party</th>
                    <th>Type</th>
                    <th>Carrier</th>
                    <th>Phone Number</th>
                    <th>IMEI</th>
                    <th>Notes</th>
                </tr>
                <?php foreach ($devices['tablets'] as $device) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($device["device_name"]); ?></td>
                        <td><?php echo htmlspecialchars($device["asset_tag"]); ?></td>
                        <td><?php echo htmlspecialchars($device["tablet_responsible"]); ?></td>
                        <td><?php echo htmlspecialchars($device["tablet_type"]); ?></td>
                        <td><?php echo htmlspecialchars($device["tablet_carrier"]); ?></td>
                        <td><?php echo htmlspecialchars($device["tablet_number"]); ?></td>
                        <td><?php echo htmlspecialchars($device["tablet_imei"]); ?></td>
                        <td><?php echo htmlspecialchars($device["tablet_notes"]); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </section>
    </div>
</body>
</html>