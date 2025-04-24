<?php
session_start();
require_once "../../PHP/config.php";
if (!isset($_SESSION["user_id"])) {
    header("Location: ../Login/login.php");
    exit();
}

// helper to run stats queries
function getCount($conn, $sql) {
    $res = $conn->query($sql);
    if (!$res) {
        die("Stats query failed: ({$conn->errno}) {$conn->error}");
    }
    return (int)$res->fetch_assoc()['c'];
}

$totalLaptops   = getCount($conn, "SELECT COUNT(*) AS c FROM Laptops");
$activeLaptops  = getCount($conn, "
    SELECT COUNT(*) AS c 
      FROM Devices d 
      JOIN Laptops l ON d.device_id = l.device_id
     WHERE d.status = 'Active'
");
$pendingReturns = getCount($conn, "
    SELECT COUNT(*) AS c 
      FROM Devices d 
      JOIN Laptops l ON d.device_id = l.device_id
     WHERE d.status = 'Pending Return'
");

// Pull exactly the columns, in the desired order:
$stmt = $conn->prepare("
    SELECT
      d.status,
      d.internet_policy,
      d.asset_tag,
      e.login_id,
      e.first_name,
      e.last_name,
      e.employee_id,
      e.phone_number,
      d.cpu,
      d.ram,
      d.os
    FROM Devices d
    JOIN Laptops l
      ON d.device_id = l.device_id
    LEFT JOIN Employees e
      ON d.assigned_to = e.emp_id
    ORDER BY d.asset_tag
") or die("Prepare failed: ({$conn->errno}) {$conn->error}");
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Laptops Dashboard</title>
  <link rel="stylesheet" href="../../Assets/styles.css">
</head>
<body>
  <?php require_once '../../includes/navbar.php'; ?>

  <main class="container">
    <h1>Laptops Dashboard</h1>

    <section class="stats">
      <div class="stat-card">
        <h2>Total Laptops</h2>
        <p><?= $totalLaptops ?></p>
      </div>
      <div class="stat-card">
        <h2>Active Laptops</h2>
        <p><?= $activeLaptops ?></p>
      </div>
      <div class="stat-card">
        <h2>Pending Returns</h2>
        <p><?= $pendingReturns ?></p>
      </div>
    </section>

    <section class="all-laptops">
      <h2>All Laptops by Employee</h2>

      <?php if (empty($rows)): ?>
        <p>No laptops found.</p>
      <?php else: ?>
        <table>
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
            <?php foreach ($rows as $d): ?>
            <tr>
              <td><?= htmlspecialchars($d['status'])           ?></td>
              <td><?= htmlspecialchars($d['internet_policy'])  ?></td>
              <td><?= htmlspecialchars($d['asset_tag'])         ?></td>
              <td><?= htmlspecialchars($d['login_id'] ?? '-')   ?></td>
              <td><?= htmlspecialchars($d['first_name'] ?? '-') ?></td>
              <td><?= htmlspecialchars($d['last_name'] ?? '-')  ?></td>
              <td><?= htmlspecialchars($d['employee_id'] ?? '-')?></td>
              <td><?= htmlspecialchars($d['phone_number'] ?? '-')?></td>
              <td><?= htmlspecialchars($d['cpu'])              ?></td>
              <td><?= htmlspecialchars($d['ram'])              ?></td>
              <td><?= htmlspecialchars($d['os'])               ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
