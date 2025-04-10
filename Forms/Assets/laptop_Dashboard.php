<?php
session_start();
require_once "../../PHP/config.php";
require_once "../../includes/navbar.php";

// Redirect if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../Login/login.php");
    exit();
}

// Fetch laptops from the Devices table categorized as 'laptop'
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

$result = $conn->query($query);

// Error Handling for Query Execution
if (!$result) {
    die("Database Query Failed: " . $conn->error);
}

$laptops = [];
while ($row = $result->fetch_assoc()) {
    $laptops[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laptops Dashboard</title>
    <link rel="stylesheet" href="/Assets/styles.css">
    <script src="/Assets/script.js?v=<?php echo time(); ?>"></script> 
</head>
<body>
        <h2>Laptops Dashboard</h2>

         <!-- Filters -->
         <div class="filters">
            <input type="text" id="filter-name" placeholder="Filter by Name">
            <input type="text" id="filter-tag" placeholder="Filter by Asset Tag">
            <select id="filter-status">
                <option value="">All Statuses</option>
                <option value="Active">Active</option>
                <option value="Decommissioned">Decommissioned</option>
            </select>
        </div>

        <!-- Table for Laptops -->
        <table class="device-table" id="device-table">
            <thead>
                <tr>
                    <th class="sortable">Device Name</th>
                    <th class="sortable">Asset Tag</th>
                    <th class="sortable">Serial Number</th>
                    <th class="sortable">Brand</th>
                    <th class="sortable">Model</th>
                    <th class="sortable">OS</th>
                    <th class="sortable">CPU</th>
                    <th class="sortable">RAM</th>
                    <th class="sortable">Storage</th>
                    <!-- <th class="sortable">Backup Type</th> -->
                    <!-- <th class="sortable">Internet Policy</th> --> 
                    <th class="sortable">Status</th>
                    <th class="sortable">Decommissioned</th>
                    <th>Decommission Notes</th>
                    <th class="sortable">Location</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($laptops)) : ?>
                    <tr>
                        <td colspan="16">No laptops found.</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($laptops as $index => $laptop) : ?>
                        <tr class="<?php echo ($index % 2 == 0) ? 'even-row' : 'odd-row'; ?>">
                            <td><?php echo htmlspecialchars($laptop["device_name"]); ?></td>
                            <td><?php echo htmlspecialchars($laptop["asset_tag"]); ?></td>
                            <td><?php echo htmlspecialchars($laptop["serial_number"]); ?></td>
                            <td><?php echo htmlspecialchars($laptop["brand"]); ?></td>
                            <td><?php echo htmlspecialchars($laptop["model"]); ?></td>
                            <td><?php echo htmlspecialchars($laptop["os"]); ?></td>
                            <td><?php echo htmlspecialchars($laptop["cpu"]); ?></td>
                            <td><?php echo htmlspecialchars($laptop["ram"]); ?></td>
                            <td><?php echo htmlspecialchars($laptop["storage"]); ?></td>
                          <!--  <td><?php echo htmlspecialchars($laptop["backup_type"]); ?></td> -->
                          <!--  <td><?php echo htmlspecialchars($laptop["internet_policy"]); ?></td> -->
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
    <script src="/Assets/script.js?v=<?php echo time(); ?>"></script>

    
</body>
</html>