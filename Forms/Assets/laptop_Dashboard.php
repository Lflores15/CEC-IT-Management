<?php
// Forms/Assets/laptop_Dashboard.php

// bootstrap session & auth
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login.php");
    exit();
}

// bring in your DB connection
require_once __DIR__ . '/../../PHP/config.php';

// 1) pull every laptop + its device record + its assigned employee (if any)
$sql = "
  SELECT
    d.status,
    l.internet_policy,
    d.asset_tag,
    e.login_id,
    e.first_name,
    e.last_name,
    e.emp_code      AS employee_id,
    e.phone_number,
    l.cpu,
    l.ram,
    l.os
  FROM Devices AS d
  JOIN Laptops AS l
    ON l.device_id = d.device_id
  LEFT JOIN Employees AS e
    ON d.assigned_to = e.emp_code
  ORDER BY d.asset_tag ASC
";

if (!($stmt = $conn->prepare($sql))) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}
$stmt->execute();
$result = $stmt->get_result();
$devices = $result->fetch_all(MYSQLI_ASSOC);

// 2) fetch employee list for the “edit mode” dropdown
$employeeOptions = [
    ['id' => '', 'name' => 'Not Assigned']
];
$empRes = $conn->query("
    SELECT emp_code, CONCAT(first_name,' ',last_name) AS name
      FROM Employees
     ORDER BY name
");
while ($row = $empRes->fetch_assoc()) {
    $employeeOptions[] = [
        'id'   => $row['emp_code'],
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
  <title>Laptops Dashboard | CEC-IT</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="/Assets/styles.css">
  <script src="/Assets/script.js?v=<?php echo time(); ?>" defer></script>
</head>
<body>
  <?php include __DIR__ . '/../../includes/navbar.php'; ?>

  <div class="main-layout">
    <h1>Laptops Dashboard</h1>

    <!-- toolbar omitted for brevity… -->

    <div class="table-container">
      <table class="device-table" id="device-table">
        <thead>
          <tr>
            <th><input class="edit-only" type="checkbox" id="select-all"></th>
            <th>Status</th>
            <th>Internet Policy</th>
            <th>Asset Tag</th>
            <th>Login ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Employee ID</th>
            <th>Phone Number</th>
            <th>CPU</th>
            <th>RAM (GB)</th>
            <th>OS</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($devices)): ?>
            <tr><td colspan="12">No laptops found.</td></tr>
          <?php else: ?>
            <?php foreach ($devices as $d): ?>
              <tr data-device-id="<?= htmlspecialchars($d['asset_tag']) ?>">
                <td><input class="row-checkbox edit-only" type="checkbox" value="<?= htmlspecialchars($d['asset_tag']) ?>"></td>
                <td><?= htmlspecialchars($d['status']) ?></td>
                <td><?= htmlspecialchars($d['internet_policy']) ?></td>
                <td><?= htmlspecialchars($d['asset_tag']) ?></td>
                <td><?= htmlspecialchars($d['login_id']    ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($d['first_name']  ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($d['last_name']   ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($d['employee_id'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($d['phone_number']?? 'N/A') ?></td>
                <td><?= htmlspecialchars($d['cpu']) ?></td>
                <td><?= htmlspecialchars($d['ram']) ?></td>
                <td><?= htmlspecialchars($d['os']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    // make the PHP‐built employee list available to your script.js
    window.employeeOptions = <?= json_encode($employeeOptions, JSON_HEX_TAG) ?>;
  </script>
</body>
</html>
