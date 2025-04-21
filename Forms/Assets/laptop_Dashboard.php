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

$query = " SELECT d.device_id, d.asset_tag, d.serial_number, d.brand, d.model, d.os,
           l.cpu, l.ram, l.storage, d.status, d.assigned_to, d.location, d.purchase_date, d.warranty_expiry, d.notes,
           l.backup_type, l.internet_policy, l.backup_removed, l.sinton_backup, l.midland_backup, l.c2_backup, l.actions_needed,
           dl.broken, dl.duplicate, dl.decommission_status, dl.additional_notes AS decommission_notes,
           e.first_name AS emp_first_name, e.last_name AS emp_last_name, e.login_id AS login_id, e.employee_id AS employee_id, e.phone_number AS phone_number
    FROM Devices d
    LEFT JOIN Laptops l ON d.device_id = l.device_id
LEFT JOIN Decommissioned_Laptops dl
  ON l.id = dl.laptop_id
    LEFT JOIN Employees e ON d.assigned_to = e.emp_id
    WHERE d.category = 'laptop'
    ORDER BY d.asset_tag
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->execute();
$result = $stmt->get_result();
$devices = $result->fetch_all(MYSQLI_ASSOC);
// Fetch employee dropdown values before closing the connection
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
    <script src="/Assets/script.js?v=<?php echo time(); ?>" defer></script> 
</head>
<body>
<div class="main-layout">
    <h1>Laptops Dashboard</h1>
    <div class="filters-container">
        <button id="edit-mode-btn" class="edit-mode-btn">Edit Table</button>
        <button id="cancel-edit-btn" class="cancel-edit-btn" style="display: none;">Cancel</button>
        <button id="openImportLaptopModal" class="import-btn">Import CSV</button>
        <button id="open-create-modal" class="create-device-btn">+ Create Device</button>
        <button id="edit-columns-btn" class="edit-columns-btn">Edit Columns</button>
        <button id="delete-selected-btn" class="delete-btn" style="display: none;">Delete Selected</button>

        <div id="column-selector" class="modal">
          <div class="laptop-modal-content">
              <div class="laptop-modal-header">
              <h2 style="margin: 0;">Edit Visible Columns</h2>
              <span class="close" onclick="document.getElementById('column-selector').style.display='none'">&times;</span>
            </div>
            <form id="column-form">
              <div>
                <?php foreach ($default_columns as $key => $label): ?>
                  <label style="display: block; margin-bottom: 8px; font-size: 15px;">
                    <input type="checkbox" name="columns[]" value="<?= $key ?>" <?= in_array($key, $visible_columns) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                  </label>
                <?php endforeach; ?>
              </div>
              <button type="submit">Apply</button>
            </form>
          </div>
        </div>

        <div class="filters">
            <input type="text" id="filter-tag" placeholder="Filter by Asset Tag">
        </div>
        <div class="filters">
            <select id="filter-status">
                <option value="Status">Filter by Status</option>
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
                                <td data-column="<?= $col ?>" data-id="<?= $device['device_id'] ?>" <?= $col === 'assigned_to' ?  'class="edit-only"data-emp-id="' . $device['assigned_to'] . '"' : '' ?>>                                <?php if ($col === 'assigned_to'): ?>
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
<script>
    window.employeeOptions = <?= json_encode($employeeOptions) ?>;
</script>

<!-- Import Laptop CSV Modal -->
<div id="importLaptopModal" class="laptop-modal-content-wrapper">
  <div class="laptop-modal-content">
    <div class="laptop-modal-header" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
      <h2 style="margin: 0;">Import Laptops from CSV</h2>
      <span id="closeImportLaptopModal" class="close" style="font-size: 24px; cursor: pointer;">&times;</span>
    </div>
    <form id="importLaptopForm" method="post" action="import_laptops.php" enctype="multipart/form-data">
      <input type="file" name="csv_file" accept=".csv" required>
      <button type="submit">Import</button>
    </form>
    <div id="import-result-message" style="margin-top: 10px; display: none;"></div>
  </div>
</div>
</body>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const importForm = document.querySelector("#importLaptopForm");
        const resultMessage = document.getElementById("import-result-message");
        const modal = document.getElementById("importLaptopModal");
        const closeBtn = document.getElementById("closeImportLaptopModal");

        if (importForm) {
            importForm.addEventListener("submit", function (e) {
                e.preventDefault();
                const formData = new FormData(importForm);
                fetch("import_laptops.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    resultMessage.innerHTML = data.message.replace(/\|/g, "<br>");
                    resultMessage.style.display = "block";
                    resultMessage.style.color = data.status === "success" ? "green" : "red";
                })
                .catch(error => {
                    resultMessage.textContent = "Import failed: " + error.message;
                    resultMessage.style.color = "red";
                    resultMessage.style.display = "block";
                });
            });
        }

        if (closeBtn && modal) {
            closeBtn.addEventListener("click", () => {
                modal.style.display = "none";
                resultMessage.style.display = "none";
                importForm.reset();
            });
        }
    });
</script>
  <!-- Create Device Modal -->
  <div id="create-device-modal" class="modal create-device-modal" style="display: none;">
    <div class="laptop-modal-content">
      <div class="laptop-modal-header">
        <h2>Create New Laptop</h2>
        <span id="close-create-modal" class="close">&times;</span>
      </div>
      <form id="create-device-form" method="post" action="create_laptop.php">
        <fieldset>
          <legend>Device Info</legend>
 
          <label>Status:
            <select name="status">
              <option value="Active">Active</option>
              <option value="Pending Return">Pending Return</option>
              <option value="Shelf">Shelf</option>
              <option value="Lost">Lost</option>
              <option value="Decommissioned">Decommissioned</option>
            </select>
          </label>
 
          <label>Internet Policy:
            <select name="internet_policy">
              <option value="admin">Admin</option>
              <option value="default">Default</option>
              <option value="office">Office</option>
            </select>
          </label>
 
          <label>Asset Tag: <input type="text" name="asset_tag" required></label>
          <label>Login ID: <input type="text" name="login_id"></label>
          <label>First Name: <input type="text" name="first_name"></label>
          <label>Last Name: <input type="text" name="last_name"></label>
          <label>Employee ID: <input type="text" name="employee_id"></label>
          <label>Phone Number: <input type="text" name="phone_number"></label>
          <label>CPU: <input type="text" name="cpu"></label>
          <label>RAM (GB): <input type="number" name="ram"></label>
          <label>OS: <input type="text" name="os"></label>
 
        </fieldset>
        <button type="submit">Submit</button>
      </form>
      <p id="create-result-message" style="margin-top: 10px; display: none;"></p>
    </div>
  </div>
</html>