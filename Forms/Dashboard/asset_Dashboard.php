<?php
session_start();
require_once "../../PHP/config.php";
require_once "../../includes/navbar.php"; 

if (!isset($_SESSION["user_id"])) {
    header("Location: ../Login/login.php");
    exit();
}

// Fetch all devices with similar attributes
$query = "
    SELECT d.device_id, d.device_name, d.asset_tag, d.serial_number, d.category, d.brand, d.model, d.os, 
           d.cpu, d.ram, d.storage, d.status, d.assigned_to, d.location, d.purchase_date, d.warranty_expiry, d.notes,
           l.backup_type, l.internet_policy, l.backup_removed, l.sinton_backup, l.midland_backup, l.c2_backup, l.actions_needed,
           dl.broken, dl.duplicate, dl.decommission_status, dl.additional_notes AS decommission_notes,
           iph.responsible_party AS responsible_party, iph.carrier, iph.phone_number, iph.previous_owner, iph.notes AS iphone_notes,
           t.responsible_party AS tablet_responsible, t.type AS tablet_type, t.carrier AS tablet_carrier, t.phone_number AS tablet_number, t.imei AS tablet_imei, t.notes AS tablet_notes
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

$devices = [];
while ($row = $result->fetch_assoc()) {
    $devices[] = $row;
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
    <div class="asset-content">
        <h2>Asset Dashboard</h2>

        <!-- Filters -->
        <div class="filters">
            <input type="text" id="filter-name" placeholder="Filter by Name">
            <input type="text" id="filter-tag" placeholder="Filter by Asset Tag">
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

        <!-- Unified Table for All Devices -->
        <table class="device-table" id="device-table">
            <thead>
                <tr>
                    <th class="sortable" data-column="0">Device Name</th> 
                    <th class="sortable" data-column="1">Asset Tag</th>
                    <th class="sortable" data-column="2">Category</th>
                    <th class="sortable" data-column="3">Brand</th>
                    <th class="sortable" data-column="4">Model</th>
                    <th class="sortable" data-column="5">OS</th>
                    <th class="sortable" data-column="6">CPU</th>
                    <th class="sortable" data-column="7">RAM</th>
                    <th class="sortable" data-column="8">Storage</th>
                    <th class="sortable" data-column="9">Backup Type</th>
                    <th class="sortable" data-column="10">Internet Policy</th>
                    <th class="sortable" data-column="11">Status</th>
                    <th class="sortable" data-column="12">Decommissioned</th>
                    <th class="sortable" data-column="13">Responsible Party</th>
                    <th class="sortable" data-column="14">Carrier</th>
                    <th class="sortable" data-column="15">Phone Number</th>
                    <th class="sortable" data-column="16">IMEI</th>
                    <th class="sortable" data-column="17">Notes</th>
                    <th class="sortable" data-column="18">Location</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($devices)) : ?>
                    <tr>
                        <td colspan="20">No devices found.</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($devices as $device) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($device["device_name"]); ?></td> 
                            <td><?php echo htmlspecialchars($device["asset_tag"]); ?></td>
                            <td><?php echo htmlspecialchars($device["category"]); ?></td>
                            <td><?php echo htmlspecialchars($device["brand"] ?? "N/A"); ?></td>
                            <td><?php echo htmlspecialchars($device["model"] ?? "N/A"); ?></td>
                            <td><?php echo htmlspecialchars($device["os"] ?? "N/A"); ?></td>
                            <td><?php echo htmlspecialchars($device["cpu"] ?? "N/A"); ?></td>
                            <td><?php echo htmlspecialchars($device["ram"] ?? "N/A"); ?></td>
                            <td><?php echo htmlspecialchars($device["storage"] ?? "N/A"); ?></td>
                            <td><?php echo htmlspecialchars($device["backup_type"] ?? "N/A"); ?></td>
                            <td><?php echo htmlspecialchars($device["internet_policy"] ?? "N/A"); ?></td>
                            <td><?php echo htmlspecialchars($device["status"]); ?></td>
                            <td><?php echo ($device["decommission_status"] && $device["decommission_status"] != 'Decommissioned') ? htmlspecialchars($device["decommission_status"]) : "No"; ?></td>
                            <td><?php echo htmlspecialchars($device["responsible_party"] ?? "N/A"); ?></td>
                            <td><?php echo htmlspecialchars($device["carrier"] ?? "N/A"); ?></td>
                            <td><?php echo htmlspecialchars($device["phone_number"] ?? "N/A"); ?></td>
                            <td><?php echo htmlspecialchars($device["tablet_imei"] ?? "N/A"); ?></td>
                            <td><?php echo htmlspecialchars($device["notes"] ?? "N/A"); ?></td>
                            <td><?php echo htmlspecialchars($device["location"]); ?></td>
                            <td>
                                <a href="edit_device.php?id=<?php echo $device['device_id']; ?>">Edit</a> |
                                <a href="delete_device.php?id=<?php echo $device['device_id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const table = document.getElementById("device-table");
            const headers = table.querySelectorAll("th.sortable");
            const filterInputs = document.querySelectorAll(".filters input, .filters select");
            
            let sortDirection = {}; 

            headers.forEach(header => {
                let columnIndex = header.getAttribute("data-column");
                sortDirection[columnIndex] = true;

                header.addEventListener("click", () => {
                    let rows = Array.from(table.querySelector("tbody").rows);
                    let ascending = sortDirection[columnIndex];

                    rows.sort((rowA, rowB) => {
                        let cellA = rowA.cells[columnIndex].textContent.trim().toLowerCase();
                        let cellB = rowB.cells[columnIndex].textContent.trim().toLowerCase();
                        return ascending ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
                    });

                    sortDirection[columnIndex] = !ascending;
                    table.querySelector("tbody").append(...rows);
                });
            });

            function filterTable() {
                let name = document.getElementById("filter-name").value.toLowerCase();
                let tag = document.getElementById("filter-tag").value.toLowerCase();
                let category = document.getElementById("filter-category").value.toLowerCase();
                let status = document.getElementById("filter-status").value.toLowerCase();

                document.querySelectorAll("#device-table tbody tr").forEach(row => {
                    let rowText = row.textContent.toLowerCase();
                    row.style.display = (rowText.includes(name) && rowText.includes(tag) && rowText.includes(category) && rowText.includes(status)) ? "" : "none";
                });
            }

            filterInputs.forEach(input => input.addEventListener("input", filterTable));
        });
    </script>
</body>
</html>