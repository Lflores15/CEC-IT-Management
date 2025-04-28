<?php
require_once("../../PHP/config.php");
require_once("../../includes/session.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employees</title>
    <link rel="stylesheet" href="../../Assets/styles.css">
    <script src="../../Assets/script.js" defer></script>
</head>
<body>
    <?php include("../../includes/navbar.php"); ?>

    <main>
    <div class="asset-content-user">
        <h1>Employee Management</h1>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <button id="toggle-edit-mode" class="edit-btn">Edit Table</button>
            <div id="edit-controls" style="display: none;">
                <button id="open-create-employee" class="create-device-btn">Add New Employee</button>
                <button id="delete-selected" class="delete-btn" disabled>Delete Selected</button>
            </div>
        </div>

        <table id="employee-table" class="device-table">
            <thead>
                <tr>
                    <th class="checkbox-col" style="display: none;"><input type="checkbox" id="select-all"></th>
                    <th style="text-align: left;">Employee ID<br><input type="text" class="filter-input" data-column="1"></th>
                    <th style="text-align: left;">First Name<br><input type="text" class="filter-input" data-column="2"></th>
                    <th style="text-align: left;">Last Name<br><input type="text" class="filter-input" data-column="3"></th>
                    <th style="text-align: left;">Login ID<br><input type="text" class="filter-input" data-column="4"></th>
                    <th style="text-align: left;">Phone Number<br><input type="text" class="filter-input" data-column="5"></th>
                    <th class="checkbox-col" style="display: none;"></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->prepare("SELECT employee_id, first_name, last_name, login_id, phone_number, active FROM Employees");
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()):
                ?>
                <tr class="clickable-row<?php echo $row['active'] ? '' : ' missing-employee'; ?>" data-id="<?php echo $row['employee_id']; ?>">
                    <td class="checkbox-col" style="display: none;"><input type="checkbox" class="row-select"></td>
                    <td><?php echo htmlspecialchars($row['employee_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['login_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                    <td class="checkbox-col" style="display: none;"><span class="edit-icon">✏️</span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>

    <!-- Add Employee Modal -->
    <div id="createEmployeeModal" class="modal">
        <div class="modal-content">
            <span id="closeCreateEmployeeModal" class="close">&times;</span>
            <h2>Add New Employee</h2>
            <form id="create-employee-form" method="post" action="create_employee.php">
                <div class="form-group">
                    <label for="employee_id">Employee ID:</label>
                    <input type="text" name="employee_id" required pattern="\d{1,4}" maxlength="4" placeholder="e.g. 1001">
                </div>
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" name="last_name" required>
                </div>
                <div class="form-group">
                    <label for="login_id">Login ID:</label>
                    <input type="text" name="login_id" required>
                </div>
                <div class="form-group">
                    <label for="phone_number">Phone Number:</label>
                    <input type="tel" name="phone_number" required pattern="\(\d{3}\) \d{3}-\d{4}" placeholder="(123) 456-7890">
                </div>
                <button type="submit">Create</button>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const toggleEdit = document.getElementById("toggle-edit-mode");
        const controls = document.getElementById("edit-controls");
        const checkCols = document.querySelectorAll(".checkbox-col");
        const deleteBtn = document.getElementById("delete-selected");

        toggleEdit.addEventListener("click", () => {
            const editing = document.body.classList.toggle("editing-mode");
            controls.style.display = editing ? "block" : "none";
            checkCols.forEach(col => col.style.display = editing ? "table-cell" : "none");
            toggleEdit.textContent = editing ? "Save Table" : "Edit Table";
            deleteBtn.disabled = true;
        });

        // Mass select
        document.getElementById("select-all").addEventListener("change", function () {
            const checked = this.checked;
            document.querySelectorAll(".row-select").forEach(cb => cb.checked = checked);
            deleteBtn.disabled = !checked;
        });

        // Individual checkbox logic
        document.querySelectorAll(".row-select").forEach(cb => {
            cb.addEventListener("change", () => {
                const anyChecked = [...document.querySelectorAll(".row-select:checked")].length > 0;
                deleteBtn.disabled = !anyChecked;
            });
        });

        // Placeholder click event for edit icon
        document.querySelectorAll(".edit-icon").forEach(icon => {
            icon.addEventListener("click", () => {
                alert("Edit form coming soon...");
            });
        });

        // Modal behavior
        const openBtn = document.getElementById("open-create-employee");
        const modal = document.getElementById("createEmployeeModal");
        const closeBtn = document.getElementById("closeCreateEmployeeModal");

        openBtn.onclick = () => modal.style.display = "block";
        closeBtn.onclick = () => modal.style.display = "none";
        window.onclick = e => { if (e.target === modal) modal.style.display = "none"; };

        // Filtering
        document.querySelectorAll(".filter-input").forEach(input => {
            input.addEventListener("input", () => {
                const rows = document.querySelectorAll("#employee-table tbody tr");
                rows.forEach(row => {
                    const cells = row.querySelectorAll("td");
                    let match = true;
                    document.querySelectorAll(".filter-input").forEach(input => {
                        const col = parseInt(input.dataset.column);
                        const val = input.value.toLowerCase();
                        const text = cells[col]?.textContent.toLowerCase() || "";
                        if (!text.includes(val)) match = false;
                    });
                    row.style.display = match ? "" : "none";
                });
            });
        });

        // Phone formatting
        const phoneInput = document.querySelector('input[name="phone_number"]');
        phoneInput.addEventListener("input", function (e) {
            let input = e.target.value.replace(/\D/g, "").substring(0, 10);
            const area = input.substring(0, 3);
            const middle = input.substring(3, 6);
            const last = input.substring(6, 10);
            if (input.length > 6) e.target.value = `(${area}) ${middle}-${last}`;
            else if (input.length > 3) e.target.value = `(${area}) ${middle}`;
            else if (input.length > 0) e.target.value = `(${area}`;
        });
    });
    </script>
</body>
</html>