<?php
session_start();
require_once "../../PHP/config.php";
require_once "../../includes/navbar.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../Login/login.php");
    exit();
}

$default_columns = [
    'device_name' => 'Device Name',
    'asset_tag' => 'Asset Tag',
    'serial_number' => 'Serial #',
    'category' => 'Category',
    'brand' => 'Brand',
    'model' => 'Model',
    'os' => 'OS',
    'cpu' => 'CPU',
    'ram' => 'RAM (GB)',
    'storage' => 'Storage (GB)',
    'status' => 'Status',
    'assigned_to' => 'Assigned To',
    'location' => 'Location',
    'purchase_date' => 'Purchase Date',
    'warranty_expiry' => 'Warranty Expiry',
    'backup_type' => 'Backup Type',
    'internet_policy' => 'Internet Policy',
    'decommission_status' => 'Decommissioned',
    'responsible_party' => 'Responsible Party',
    'carrier' => 'Carrier',
    'phone_number' => 'Phone Number',
    'tablet_imei' => 'IMEI',
    'notes' => 'Notes'
];

$visible_columns = $_SESSION['visible_columns'] ?? array_keys($default_columns);

$query = "
    SELECT d.device_id, d.device_name, d.asset_tag, d.serial_number, d.category, d.brand, d.model, d.os, 
           d.cpu, d.ram, d.storage, d.status, d.assigned_to, d.location, d.purchase_date, d.warranty_expiry, d.notes,
           l.backup_type, l.internet_policy,
           dl.decommission_status,
           iph.responsible_party, iph.carrier, iph.phone_number,
           t.imei AS tablet_imei
    FROM Devices d
    LEFT JOIN Laptops l ON d.device_id = l.device_id
    LEFT JOIN Decommissioned_Laptops dl ON l.id = dl.laptop_id
    LEFT JOIN iPhones iph ON d.device_id = iph.device_id
    LEFT JOIN Tablets t ON d.device_id = t.device_id
    ORDER BY d.device_name
";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$devices = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Asset Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/Assets/styles.css">
    <script src="/Assets/script.js" defer></script>
</head>
<body>
<div class="main-layout">
    <div class="filters-container">
    <button id="edit-columns-btn" class="edit-columns-btn">Edit Columns</button>
    <div id="column-selector" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('column-selector').style.display='none'">&times;</span>
            <form id="column-form">
                <h3>Select Visible Columns</h3>
                <?php foreach ($default_columns as $key => $label): ?>
                    <label>
                        <input type="checkbox" name="columns[]" value="<?= $key ?>" <?= in_array($key, $visible_columns) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($label) ?>
                    </label><br>
                <?php endforeach; ?>
                <button type="submit">Apply</button>
            </form>
        </div>
    </div>

    
        <div class="filters">
            <input type="text" id="filter-name" placeholder="Filter by Name">
            <input type="text" id="filter-tag" placeholder="Filter by Asset Tag">
        </div>
        <div class="filters">
            <select id="filter-category">
                <option value="">Filter by Category</option>
                <option value="laptop">Laptops</option>
                <option value="desktop">Desktops</option>
                <option value="iPhone">iPhones</option>
                <option value="tablet">Tablets</option>
            </select>
            <select id="filter-status">
                <option value="">Filter by Status</option>
                <option value="Active">Active</option>
                <option value="Decommissioned">Decommissioned</option>
                <option value="Lost">Lost</option>
                <option value="Pending Return">Pending Return</option>
                <option value="Shelf">Shelf</option>
            </select>
        </div>
    </div>

    <div class="table-container">
        <table class="device-table" id="device-table">
            <thead>
                <tr>
                    <?php foreach ($visible_columns as $col): ?>
                        <th class="sortable"><?= htmlspecialchars($default_columns[$col]) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($devices)) : ?>
                    <tr><td colspan="<?= count($visible_columns) ?>">No devices found.</td></tr>
                <?php else : ?>
                    <?php foreach ($devices as $device): ?>
                        <tr class="clickable-row" data-href="device_details.php?id=<?= $device['device_id'] ?>">
                            <?php foreach ($visible_columns as $col): ?>
                                <td><?= htmlspecialchars($device[$col] ?? 'N/A') ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>