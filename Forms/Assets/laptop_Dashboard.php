<?php
// Forms/Assets/laptop_Dashboard.php
declare(strict_types=1);

// 1) Bootstrapping & session check
require __DIR__ . '/../../PHP/config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Login/login.php');
    exit;
}

// 2) Pull in your sidebar/navbar
include __DIR__ . '/../../includes/navbar.php';

// 3) Build the query string
$sql = "
  SELECT
    d.status,
    l.internet_policy,
    d.asset_tag,
    COALESCE(e.login_id,'N/A')      AS login_id,
    COALESCE(e.first_name,'N/A')    AS first_name,
    COALESCE(e.last_name,'N/A')     AS last_name,
    COALESCE(d.assigned_to,'N/A')   AS employee_id,
    COALESCE(e.phone_number,'N/A')  AS phone_number,
    l.cpu,
    l.ram,
    l.os
  FROM Devices d
  JOIN Laptops  l  ON l.device_id      = d.device_id
  LEFT JOIN Employees e ON d.assigned_to = e.emp_code
  ORDER BY d.asset_tag;
";

// … right before your prepare call …
error_log("LAPTOP SQL: " . $sql);
echo "<pre style='color:red'>SQL=>\n" . htmlspecialchars($sql) . "</pre>";
$stmt = $conn->prepare($sql);
if ( ! $stmt ) {
    echo "<div style='color:red'>Prepare failed: " . htmlspecialchars($conn->error) . "</div>";
    exit;
}

$stmt->execute();
$result  = $stmt->get_result();
$devices = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// 5) Helper to safely escape any value as string
function h($val): string {
    return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Laptops Dashboard | CEC-IT</title>
  <link rel="stylesheet" href="/Assets/styles.css">
</head>
<body>
  <div class="main-content">
    <h1>Laptops Dashboard</h1>
    <table class="device-table">
      <thead>
        <tr>
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
          <tr><td colspan="11">No laptops found.</td></tr>
        <?php else: ?>
          <?php foreach ($devices as $row): ?>
            <tr>
              <td><?= h($row['status']) ?></td>
              <td><?= h($row['internet_policy']) ?></td>
              <td><?= h($row['asset_tag']) ?></td>
              <td><?= h($row['login_id']) ?></td>
              <td><?= h($row['first_name']) ?></td>
              <td><?= h($row['last_name']) ?></td>
              <td><?= h($row['employee_id']) ?></td>
              <td><?= h($row['phone_number']) ?></td>
              <td><?= h($row['cpu']) ?></td>
              <td><?= h($row['ram']) ?></td>
              <td><?= h($row['os']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
