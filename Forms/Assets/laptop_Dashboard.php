<?php
session_start();
require_once "../../PHP/config.php";
require_once "../../includes/navbar.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../Login/login.php");
    exit();
}

$default_columns = [
    'status' => 'Status',
    'internet_policy' => 'Internet Policy',
    'asset_tag' => 'Asset Tag',    
    'login_id' => 'Login ID',
    'emp_first_name' => 'First Name',
    'emp_last_name' => 'Last Name',
    'employee_id' => 'Employee ID',
    'phone_number' => 'Phone Number',
    'cpu' => 'CPU',
    'ram' => 'RAM (GB)',
    'os' => 'OS',
    'assigned_to' => 'Assigned To' 
];

$visible_columns = $_SESSION['visible_columns'] ?? array_keys($default_columns);

$query = "
    SELECT d.device_id, d.asset_tag, d.serial_number, d.brand, d.model, d.os, 
           l.cpu, l.ram, l.storage, d.status, d.assigned_to, d.location, d.purchase_date, d.warranty_expiry, d.notes,
           l.backup_type, l.internet_policy, l.backup_removed, l.sinton_backup, l.midland_backup, l.c2_backup, l.actions_needed,
           dl.broken, dl.duplicate, dl.decommission_status, dl.additional_notes AS decommission_notes,
           e.first_name AS emp_first_name, e.last_name AS emp_last_name, e.login_id AS login_id, e.employee_id AS employee_id, e.phone_number AS phone_number
    FROM Devices d
    LEFT JOIN Laptops l ON d.device_id = l.device_id
    LEFT JOIN Decommissioned_Laptops dl ON l.laptop_id = dl.laptop_id
    LEFT JOIN Employees e ON d.assigned_to = e.emp_id
    WHERE d.category = 'laptop'
    ORDER BY d.asset_tag
";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$devices = $result->fetch_all(MYSQLI_ASSOC);

$employeeOptions = [];
$empQuery = $conn->query("SELECT emp_id, CONCAT(first_name, ' ', last_name) AS name FROM Employees ORDER BY name ASC");
while ($row = $empQuery->fetch_assoc()) {
    $employeeOptions[] = [
        'id' => $row['emp_id'],
        'name' => $row['name']
    ];
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laptops Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/Assets/styles.css">
    <script src="/Assets/script.js" defer></script>
</head>
<body>
<div class="main-layout">
    <div class="filters-container">
        <button id="edit-mode-btn" class="edit-mode-btn">Edit Table</button>
        <button id="cancel-edit-btn" class="cancel-edit-btn" style="display: none;">Cancel</button>
        <button id="openImportLaptopModal" class="import-btn">Import CSV</button>
        <button id="open-create-modal" class="create-device-btn">+ Create Device</button>
        <button id="edit-columns-btn" class="edit-columns-btn">Edit Columns</button>
        <button id="delete-selected-btn" class="delete-btn" style="display: none;">Delete Selected</button>
    </div>

    <div class="table-container">
        <table class="device-table" id="device-table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all"></th>
                    <?php foreach ($visible_columns as $col): ?>
                        <th class="sortable <?= $col === 'assigned_to' ? 'edit-only' : '' ?>">
                            <?= htmlspecialchars($default_columns[$col]) ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($devices)) : ?>
                    <tr><td colspan="<?= count($visible_columns) + 1 ?>">No devices found.</td></tr>
                <?php else : ?>
                    <?php foreach ($devices as $device): ?>
                        <tr class="clickable-row" data-href="device_details.php?id=<?= $device['device_id'] ?>">
                            <td><input type="checkbox" class="row-checkbox delete-checkbox" value="<?= $device['device_id'] ?>"></td>
                            <?php foreach ($visible_columns as $col): ?>
                                <td data-column="<?= $col ?>" data-id="<?= $device['device_id'] ?>" <?= $col === 'assigned_to' ?  'class="edit-only" data-emp-id="' . $device['assigned_to'] . '"' : '' ?>>
                                    <?php if ($col === 'assigned_to'): ?>
                                        <?= htmlspecialchars(trim(($device['emp_first_name'] ?? '') . ' ' . ($device['emp_last_name'] ?? ''))) . ' (' . ($device['employee_id'] ?? 'N/A') . ')' ?>
                                    <?php else: ?>
                                        <?= htmlspecialchars($device[$col] ?? 'N/A') ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Import Laptop Modal -->
<div id="importLaptopModal" class="laptop-modal-content-wrapper">
  <div class="laptop-modal-content">
    <div class="laptop-modal-header">
      <h2>Import Laptops from CSV</h2>
      <span id="closeImportLaptopModal" class="close">&times;</span>
    </div>
    <form id="import-laptop-form" method="post" enctype="multipart/form-data">
      <input type="file" name="csvFile" id="csvFile" accept=".csv" required />
      <button type="submit">Import</button>
    </form>
    <div id="import-result-message" style="margin-top: 10px; display: none;"></div>
  </div>
</div>

<script>
    window.employeeOptions = <?= json_encode($employeeOptions) ?>;
</script>
</body>
</html>
