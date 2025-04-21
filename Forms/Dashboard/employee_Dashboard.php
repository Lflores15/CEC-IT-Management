<?php
require_once(__DIR__ . "/../../PHP/config.php");
require_once(__DIR__ . "/../../includes/session.php");
require_once(__DIR__ . "/../../includes/navbar.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employees Dashboard</title>
    <link rel="stylesheet" href="../../../Assets/styles.css">
</head>
<body>
    <div class="main-content">
        <div class="table-header">
            <h1>Employees</h1>
            <button id="editToggleBtn">Edit Table</button>
        </div>

        <div id="editActions" style="display:none;">
            <button id="addEmployeeBtn">Add New Employee</button>
            <select id="deleteSelectedDropdown">
                <option value="" disabled selected>Select action</option>
                <option value="delete">Delete Selected</option>
            </select>
        </div>

        <table id="employeeTable">
            <thead>
                <tr>
                    <th class="checkbox-col" style="display:none;"></th>
                    <th>Employee ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Login ID</th>
                    <th>Phone Number</th>
                    <th class="edit-col" style="display:none;"></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT employee_id, first_name, last_name, login_id, phone_number FROM Employees";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td class='checkbox-col' style='display:none;'><input type='checkbox' class='row-checkbox'></td>";
                        echo "<td>{$row['employee_id']}</td>";
                        echo "<td>{$row['first_name']}</td>";
                        echo "<td>{$row['last_name']}</td>";
                        echo "<td>{$row['login_id']}</td>";
                        echo "<td>{$row['phone_number']}</td>";
                        echo "<td class='edit-col' style='display:none;'><button class='edit-btn'>✎</button></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No employees found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="../../../Assets/script.js?v=<?php echo time(); ?>"></script>

</body>
</html>