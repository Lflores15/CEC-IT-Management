<?php
session_start();
require_once "../../PHP/config.php";
require_once "../../includes/navbar.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../Login/login.php");
    exit();
}

$default_columns = [
  'status'          => 'Status',
  'internet_policy'=> 'Internet Policy',
  'asset_tag'      => 'Asset Tag',
  'username'       => 'Login ID',       // matches e.username
  'first_name'     => 'First Name',     // matches e.first_name
  'last_name'      => 'Last Name',      // matches e.last_name
  'emp_code'       => 'Employee ID',    // matches e.emp_code
  'phone_number'   => 'Phone Number',
  'cpu'            => 'CPU',
  'ram'            => 'RAM (GB)',
  'os'             => 'OS',
  'assigned_to'    => 'Assigned To'
];

$visible_columns = $_SESSION['visible_columns'] ?? array_keys($default_columns);


$query = "
SELECT
  d.device_id,
  d.status,
  l.internet_policy,
  d.asset_tag,
  l.cpu,
  l.ram,
  l.os,
  e.username      AS username,
  e.first_name    AS first_name,
  e.last_name     AS last_name,
  e.emp_code      AS emp_code,
  e.phone_number  AS phone_number,
  d.assigned_to
FROM Devices   AS d
LEFT JOIN Laptops   AS l ON d.device_id    = l.device_id
LEFT JOIN Employees AS e ON d.assigned_to  = e.emp_code
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
// Fetch employee dropdown values before closing the connection
$employeeOptions = [];
$empQuery = $conn->query("
  SELECT emp_id, emp_code, first_name, last_name
  FROM Employees
  ORDER BY CASE WHEN emp_code = '0000' THEN 0 ELSE 1 END, first_name ASC, last_name ASC
");
while ($row = $empQuery->fetch_assoc()) {
    if ($row['emp_code'] === '0000') {
        $displayName = 'Unassigned';
    } else {
        $displayName = trim($row['first_name'] . ' ' . $row['last_name']);
    }
    $employeeOptions[] = [
        'id'   => $row['emp_code'],
        'name' => $displayName,
    ];
}

$stmt->close();
$conn->close();

$activeEmployeeIDs = $_SESSION['active_employee_ids'] ?? [];
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
    <div class="filters-container" style="padding-top: 0;">
      <div style="display: flex; justify-content: space-between; width: 100%;">
        <div style="display: flex; gap: 10px;">
            <button id="edit-mode-btn" class="edit-mode-btn">Edit Table</button>
            <button id="cancel-edit-btn" class="cancel-edit-btn" style="display: none;">Cancel</button>
            <button id="delete-selected-btn" class="delete-btn" style="display: none;">Delete Selected</button>
            <button id="undo-delete-btn" class="undo-btn" style="display: none;">Undo Last Delete</button>
            <button id="open-create-modal" class="create-device-btn">+ Create Device</button>
            <button id="edit-columns-btn" class="edit-columns-btn">Edit Columns</button>
            <!-- Filters moved here -->
            <!-- Deprecated filters
            <div class="filters" style="margin-left: 10px;">
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
            </div> --> 
        </div> 
        <div style="display: flex; gap: 10px;">
            <button id="openImportLaptopModal" class="import-btn">Populate Table</button>
            <button id="audit-laptop-btn" class="create-device-btn">Audit Laptops</button>
            <button id="export-csv-btn" class="create-device-btn">Export CSV</button>
        </div>
      </div>
      <!-- JS logic for undo, delete, modals, import, etc. is handled by script.js -->
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
    </div>

    <div class="table-container">
        <table class="device-table" id="device-table">
            <thead>
                <tr>
                    <th>
                        <input class="edit-only" type="checkbox" id="select-all">
                    </th>
                    <?php foreach ($visible_columns as $col): ?>
                        <th class="sortable <?= $col === 'assigned_to' ? 'edit-only' : '' ?>">
                            <?= htmlspecialchars($default_columns[$col]) ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <thead class="filter-header">
                <tr>
                    <th></th>
                    <?php foreach ($visible_columns as $col): ?>
                        <th <?= $col === 'assigned_to' ? 'class="edit-only"' : '' ?>>
                            <?php if ($col !== 'assigned_to'): ?>
                                <input type="text" class="filter-input" data-column="<?= $col ?>" placeholder="Filter <?= htmlspecialchars($default_columns[$col]) ?>" style="width: 95%;">
                            <?php endif; ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($devices)) : ?>
                    <tr style="background-color: #ffffff;">
                        <td colspan="<?= count($visible_columns) + 1 ?>" style="padding: 12px; text-align: center; color: #555; font-size: 0.9em;">
                            No devices found.
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($devices as $device): ?>
                        <?php
                        $empId = isset($device['emp_code']) ? trim($device['emp_code']) : null;
                        $isUnassigned = $empId === '0000';
                        $isMissingEmployee = !$isUnassigned && $empId && !in_array($empId, $activeEmployeeIDs);
                        ?>
                        <tr class="clickable-row log-event-btn<?= $isMissingEmployee ? ' missing-employee' : '' ?>" data-device-id="<?= $device['device_id'] ?>" style="cursor: pointer;">
                            <td><input type="checkbox" class="row-checkbox delete-checkbox" value="<?= $device['device_id'] ?>"></td>
                            <?php foreach ($visible_columns as $col): ?>
                                <td data-column="<?= $col ?>" data-id="<?= $device['device_id'] ?>" <?= $col === 'assigned_to' ? 'class="edit-only" data-emp-id="' . $device['assigned_to'] . '"' : '' ?>>
                                    <?php if ($col === 'assigned_to'): ?>
                                        <?= htmlspecialchars(trim(($device['first_name'] ?? '') . ' ' . ($device['last_name'] ?? ''))) . ' (' . ($device['emp_code'] ?? 'N/A') . ')' ?>
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
    <form id="importLaptopForm" enctype="multipart/form-data">
      <input type="file" name="csv_file" accept=".csv" required>
      <button type="submit">Import</button>
    </form>
    <div id="import-result-message" style="margin-top: 10px; display: none; max-height: 200px; overflow-y: auto; padding: 10px; background-color: #f8f9fa; border: 1px solid #ccc; border-radius: 5px; font-size: 0.9em;"></div>
  </div>
</div>
<!-- Audit Laptop Modal -->
<div id="auditLaptopModal" class="laptop-modal-content-wrapper" style="display: none;">
  <div class="laptop-modal-content">
    <div class="laptop-modal-header" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
      <h2 style="margin: 0;">Audit Laptops</h2>
      <span id="closeAuditLaptopModal" class="close" style="font-size: 24px; cursor: pointer;">&times;</span>
    </div>
    <div id="auditLaptopForm">
      <p>Select an Active Employee CSV file to audit assigned laptops.</p>
      <input type="file" id="auditCsvFile" accept=".csv" required>
      <button type="button" id="runAuditBtn">Run Audit</button>
    </div>
  </div>
</div>
<!-- All modal, import, and row navigation JS logic is handled by script.js -->
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
              <option value="active">Active</option>
              <option value="shelf-cc">Shelf-CC</option>
              <option value="shelf-md">Shelf-MD</option>
              <option value="shelf-hs">Shelf-HS</option>
              <option value="pending return">Pending Return</option>
              <option value="lost">Lost</option>
              <option value="decommissioned">Decommissioned</option>
            </select>
          </label>

          <label>Internet Policy:
            <select name="internet_policy" required>
              <option value="Default">Default</option>
              <option value="Office">Office</option>
              <option value="Admin">Admin</option>
              <option value="Accounting">Accounting</option>
              <option value="Estimating">Estimating</option>
              <option value="Executive">Executive</option>
              <option value="HR">HR</option>
            </select>
          </label>

          <label>Asset Tag: <input type="text" name="asset_tag" required></label>

          <label>Assign To:
            <select name="assigned_to" id="assigned_to" required onchange="fetchEmployeeDetails(this.value)">
              <?php foreach ($employeeOptions as $employee): ?>
                <option value="<?= htmlspecialchars($employee['id']) ?>"><?= htmlspecialchars($employee['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <input type="hidden" name="first_name" id="first_name">
          <input type="hidden" name="last_name" id="last_name">
          <input type="hidden" name="username" id="username">
          <input type="hidden" name="phone_number" id="phone_number">

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
  <!-- Log Event Modal -->
  <div id="logEventModal" class="modal create-device-modal" style="display: none;">
    <div class="laptop-modal-content" style="display: flex; gap: 30px; align-items: flex-start;">
      <div style="flex: 1 1 50%; min-width: 300px;">
        <div class="laptop-modal-header">
          <h2>Log Laptop Event</h2>
          <span class="close" onclick="document.getElementById('logEventModal').style.display='none'">&times;</span>
        </div>
        <form id="log-event-form" method="post" action="manual_log.php" style="display: flex; flex-direction: column; gap: 10px;">
          <input type="hidden" id="log-device-id" name="device_id">
          <label for="log-event-time">Event Time:</label>
          <p id="log-event-time" style="font-style: italic; font-size: 1em; color: #555;"></p>
          <label for="event_type">Event Type:</label>
          <select name="event_type" required>
            <option value="">Select Event Type</option>
            <option value="New User Setup">New User Setup</option>
            <option value="Updated">Updated</option>
            <option value="User Archived">User Archived</option>
            <option value="Maintenance">Maintenance</option>
            <option value="Damaged">Damaged</option>
            <option value="Decommissioned">Decommissioned</option>
            <option value="Location Change">Location Change</option>
          </select>
          <label for="memo">Memo:</label>
          <textarea name="memo" rows="4" placeholder="Add a note about this event..." required></textarea>
          <button type="submit">Log Event</button>
        </form>
      </div>
      <div style="flex: 1 1 50%; min-width: 300px;">
        <h3>Asset History Log</h3>
        <div id="log-history-container" class="log-table-scrollable" style= "overflow-y: auto;">
          <table class="device-table" style="font-size: 0.85em;">
            <thead>
              <tr id="log-header-row">
                <th>Date</th>
                <th>Time</th>
                <th>Type</th>
                <th>Memo</th>
              </tr>
            </thead>
            <tbody id="device-log-history" style="font-size: 0.85em;">
              <!-- JS will populate log entries here -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</html>