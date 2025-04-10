<?php
session_start();
require_once "../../PHP/config.php";
require_once "../../includes/navbar.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../Login/login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Missing or invalid device ID.";
    exit();
}

$device_id = intval($_GET['id']);

$query = "
    SELECT d.*, 
           CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
           e.login_id AS employee_login,
           e.employee_id AS employee_external_id,
           e.email AS employee_email,
           e.phone_number AS employee_phone,
           a.assigned_at,
           l.backup_type, l.internet_policy, l.backup_removed, l.sinton_backup, l.midland_backup, l.c2_backup, l.actions_needed,
           dl.broken, dl.duplicate, dl.decommission_status, dl.additional_notes AS decommission_notes,
           iph.responsible_party AS iphone_responsible, iph.carrier AS iphone_carrier, iph.phone_number AS iphone_number,
           iph.previous_owner AS iphone_previous, iph.notes AS iphone_notes,
           t.responsible_party AS tablet_responsible, t.type AS tablet_type, t.carrier AS tablet_carrier,
           t.phone_number AS tablet_number, t.imei AS tablet_imei, t.notes AS tablet_notes
    FROM Devices d
    LEFT JOIN Assignments a ON d.device_id = a.device_id AND a.status = 'Active'
    LEFT JOIN Employees e ON a.emp_id = e.emp_id
    LEFT JOIN Laptops l ON d.device_id = l.device_id
    LEFT JOIN Decommissioned_Laptops dl ON l.id = dl.laptop_id
    LEFT JOIN iPhones iph ON d.device_id = iph.device_id
    LEFT JOIN Tablets t ON d.device_id = t.device_id
    WHERE d.device_id = ?
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("SQL prepare error: " . $conn->error);
}

$stmt->bind_param("i", $device_id);
$stmt->execute();
$result = $stmt->get_result();
$device = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$device) {
    echo "Device not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Device Details - <?= htmlspecialchars($device['device_name']) ?></title>
    <link rel="stylesheet" href="/Assets/styles.css">
    <script src="/Assets/script.js" defer></script>
</head>
<body>

<div class="device-details-container">
    <h2>Device Details: <?= htmlspecialchars($device['device_name']) ?></h2>

    <div class="action-buttons">
        <a href="edit_device.php?id=<?= $device_id ?>" class="edit-btn">Edit</a>
        <a href="delete_device.php?id=<?= $device_id ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this device?');">Delete</a>
    </div>

    <h3 class="section-title">Device Info</h3>
    <div class="device-details-grid">
        <?php
        $fields = [
            'Asset Tag' => 'asset_tag',
            'Serial Number' => 'serial_number',
            'Category' => 'category',
            'Brand' => 'brand',
            'Model' => 'model',
            'OS' => 'os',
            'CPU' => 'cpu',
            'RAM (GB)' => 'ram',
            'Storage (GB)' => 'storage',
            'Status' => 'status',
            'Location' => 'location',
            'Purchase Date' => 'purchase_date',
            'Warranty Expiry' => 'warranty_expiry',
            'Notes' => 'notes'
        ];
        foreach ($fields as $label => $key) {
            if (!empty($device[$key])) {
                echo "<div><label>$label:</label><div>" . htmlspecialchars($device[$key]) . "</div></div>";
            }
        }

        // Laptop-specific info
        if ($device['category'] === 'laptop') {
            echo "<h4 class='section-subtitle'>Laptop Details</h4>";
            $laptop_fields = [
                'Backup Type' => 'backup_type',
                'Internet Policy' => 'internet_policy',
                'Backup Removed' => $device['backup_removed'] ? 'Yes' : 'No',
                'Sinton Backup' => $device['sinton_backup'] ? 'Yes' : 'No',
                'Midland Backup' => $device['midland_backup'] ? 'Yes' : 'No',
                'C2 Backup' => $device['c2_backup'] ? 'Yes' : 'No',
                'Actions Needed' => 'actions_needed'
            ];
            foreach ($laptop_fields as $label => $key) {
                $value = is_string($key) ? ($device[$key] ?? 'N/A') : $key;
                echo "<div><label>$label:</label><div>" . htmlspecialchars($value) . "</div></div>";
            }
        }

        // iPhone-specific info
        if ($device['category'] === 'iPhone') {
            echo "<h4 class='section-subtitle'>iPhone Details</h4>";
            $iphone_fields = [
                'Responsible Party' => 'iphone_responsible',
                'Carrier' => 'iphone_carrier',
                'Phone Number' => 'iphone_number',
                'Previous Owner' => 'iphone_previous',
                'Notes' => 'iphone_notes'
            ];
            foreach ($iphone_fields as $label => $key) {
                echo "<div><label>$label:</label><div>" . htmlspecialchars($device[$key] ?? 'N/A') . "</div></div>";
            }
        }

        // Tablet-specific info
        if ($device['category'] === 'tablet') {
            echo "<h4 class='section-subtitle'>Tablet Details</h4>";
            $tablet_fields = [
                'Responsible Party' => 'tablet_responsible',
                'Type' => 'tablet_type',
                'Carrier' => 'tablet_carrier',
                'Phone Number' => 'tablet_number',
                'IMEI' => 'tablet_imei',
                'Notes' => 'tablet_notes'
            ];
            foreach ($tablet_fields as $label => $key) {
                echo "<div><label>$label:</label><div>" . htmlspecialchars($device[$key] ?? 'N/A') . "</div></div>";
            }
        }

        
        ?>
    </div>

    <?php if (!empty($device['decommission_status'])): ?>
        <h3 class="section-title">Decommission Info</h3>
        <div class="device-details-grid">
            <div><label>Status:</label><div><?= htmlspecialchars($device['decommission_status']) ?></div></div>
            <div><label>Broken:</label><div><?= isset($device['broken']) ? ($device['broken'] ? 'Yes' : 'No') : 'N/A' ?></div></div>
            <div><label>Duplicate:</label><div><?= isset($device['duplicate']) ? ($device['duplicate'] ? 'Yes' : 'No') : 'N/A' ?></div></div>
            <div><label>Notes:</label><div><?= htmlspecialchars($device['decommission_notes'] ?? 'N/A') ?></div></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($device['employee_name'])): ?>
        <h3 class="section-title">User Info</h3>
        <div class="device-details-grid">
            <div><label>Employee ID:</label><div><?= htmlspecialchars($device['employee_external_id']) ?></div></div>
            <div><label>Full Name:</label><div><?= htmlspecialchars($device['employee_name']) ?></div></div>
            <div><label>Login Username:</label><div><?= htmlspecialchars($device['employee_login']) ?></div></div>
            <div><label>Email:</label><div><?= htmlspecialchars($device['employee_email']) ?></div></div>
            <div><label>Phone Number:</label><div><?= htmlspecialchars($device['employee_phone']) ?></div></div>
            <div><label>Assigned At:</label><div><?= htmlspecialchars($device['assigned_at']) ?></div></div>
        </div>
    <?php endif; ?>

    <a href="asset_Dashboard.php" class="back-link">← Back to Dashboard</a>
</div>

</body>
</html>