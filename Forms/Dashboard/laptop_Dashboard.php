<?php
require_once(__DIR__ . "/../../PHP/config.php");
require_once(__DIR__ . "/../../includes/session.php");
require_once(__DIR__ . "/../../includes/navbar.php");


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laptops Dashboard</title>
    <link rel="stylesheet" href="../../../Assets/styles.css">
</head>
<body>
    <div class="main-content">
        <div class="table-header">
            <h1>Laptops</h1>
            <button id="editToggleBtn">Edit Table</button>
        </div>

        <div id="editActions" style="display:none;">
            <button id="addLaptopBtn">Add New Laptop</button>
            <select id="deleteSelectedDropdown">
                <option value="" disabled selected>Select action</option>
                <option value="delete">Delete Selected</option>
            </select>
        </div>

        <table id="laptopsTable">
            <thead>
                <tr>
                    <th class="checkbox-col" style="display:none;"></th>
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
                    <th class="edit-col" style="display:none;"></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT l.status, l.internet_policy, l.asset_tag, l.login_id, 
                               e.first_name, e.last_name, e.employee_id, e.phone_number,
                               l.cpu, l.ram, l.os
                        FROM Laptops l
                        LEFT JOIN Employees e ON l.employee_id = e.employee_id";

                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td class='checkbox-col' style='display:none;'><input type='checkbox' class='row-checkbox'></td>";
                        echo "<td>{$row['status']}</td>";
                        echo "<td>{$row['internet_policy']}</td>";
                        echo "<td>{$row['asset_tag']}</td>";
                        echo "<td>{$row['login_id']}</td>";
                        echo "<td>{$row['first_name']}</td>";
                        echo "<td>{$row['last_name']}</td>";
                        echo "<td>{$row['employee_id']}</td>";
                        echo "<td>{$row['phone_number']}</td>";
                        echo "<td>{$row['cpu']}<`/td>";
                        echo "<td>{$row['ram']}</td>";
                        echo "<td>{$row['os']}</td>";
                        echo "<td class='edit-col' style='display:none;'><button class='edit-btn'>✎</button></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='13'>No laptops found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="../../../Assets/script.js?v=<?php echo time(); ?>"></script>
</body>
</html>