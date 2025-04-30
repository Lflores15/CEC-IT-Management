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
                <button id="delete-selected-btn" class="delete-btn" disabled>Delete Selected</button>
            </div>
        </div>

        <div style="overflow-y: auto; height: calc(105vh - 280px);">
            <table id="employee-table" class="device-table">
                <thead style="position: sticky; top: 0; background-color: #007bff; z-index: 2;">
                    <tr>
                        <th class="checkbox-col" style="display: none;"><input type="checkbox" id="select-all"></th>
                        <th class="sortable" data-column="1" style="text-align: left;">Employee Code<br><input type="text" class="filter-input" data-column="1" placeholder="Filter Employee Code"></th>
                        <th class="sortable" data-column="2" style="text-align: left;">First Name<br><input type="text" class="filter-input" data-column="2" placeholder="Filter First Name"></th>
                        <th class="sortable" data-column="3" style="text-align: left;">Last Name<br><input type="text" class="filter-input" data-column="3" placeholder="Filter Last Name"></th>
                        <th class="sortable" data-column="4" style="text-align: left;">Username<br><input type="text" class="filter-input" data-column="4" placeholder="Filter Username"></th>
                        <th class="sortable" data-column="5" style="text-align: left;">Phone Number<br><input type="text" class="filter-input" data-column="5" placeholder="Filter Phone Number"></th>
                        <th class="checkbox-col" style="display: none;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->prepare("SELECT emp_code, first_name, last_name, username, phone_number, active FROM Employees");
                    $stmt->execute();
                    $result = $stmt->get_result();

                    while ($row = $result->fetch_assoc()):
                    ?>
                    <tr class="clickable-row<?php echo $row['active'] ? '' : ' missing-employee'; ?>" data-id="<?php echo $row['emp_code']; ?>">
                        <td class="checkbox-col" style="display: none;"><input type="checkbox" class="row-select" value="<?php echo htmlspecialchars($row['emp_code']); ?>"></td>
                        <td><?php echo htmlspecialchars($row['emp_code']); ?></td>
                        <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                        <td class="checkbox-col" style="display: none;"><span class="edit-icon">✏️</span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Add/Edit Employee Modal -->
    <div id="createEmployeeModal" class="modal laptop-modal" style="width: 500px;">
        <div class="laptop-modal-content">
            <span id="closeCreateEmployeeModal" class="close">&times;</span>
            <h2>Add/Edit Employee</h2>
            <form id="create-employee-form" method="post" action="create_employee.php">
                <input type="hidden" name="is_edit" value="false">
                <div class="form-group">
                    <label for="emp_code">Employee ID:</label>
                    <input type="hidden" name="emp_id" value="">
                    <input type="text" name="emp_code" required pattern="\d{1,4}" maxlength="4" placeholder="e.g. 1001">
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
                    <label for="username">Login ID:</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label for="phone_number">Phone Number:</label>
                    <input type="tel" name="phone_number" required pattern="\(\d{3}\) \d{3}-\d{4}" placeholder="(123) 456-7890">
                </div>
                <button type="submit">Save</button>
                <div id="edit-user-message" style="margin-top: 10px; font-weight: bold;"></div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const toggleEdit = document.getElementById("toggle-edit-mode");
        const controls = document.getElementById("edit-controls");
        const checkCols = document.querySelectorAll(".checkbox-col");
        const deleteBtn = document.getElementById("delete-selected-btn");

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

        // Delete selected employees
        if (deleteBtn) {
            deleteBtn.addEventListener("click", function () {
                if (deleteBtn.disabled) return;
                const checkboxes = document.querySelectorAll(".row-select:checked");
                if (!checkboxes.length) {
                    alert("Please select employees to delete.");
                    return;
                }
                if (!confirm("Are you sure you want to delete the selected employees?")) {
                    return;
                }
                const ids = Array.from(checkboxes).map(cb => cb.value);
                deleteBtn.disabled = true;
                fetch("delete_employees.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ employee_ids: ids })
                })
                .then(res => res.text())
                .then(msg => {
                    alert(msg);
                    location.reload();
                })
                .catch(err => {
                    alert("Delete failed: " + err);
                    deleteBtn.disabled = false;
                });
            });
        }

        // Edit icon click event: open modal with prefilled employee data
        document.querySelectorAll(".edit-icon").forEach(icon => {
            icon.addEventListener("click", function () {
                const row = this.closest("tr");
                const empCode = row.querySelector("td:nth-child(2)").textContent.trim();
                const firstName = row.querySelector("td:nth-child(3)").textContent.trim();
                const lastName = row.querySelector("td:nth-child(4)").textContent.trim();
                const username = row.querySelector("td:nth-child(5)").textContent.trim();
                const phoneNumber = row.querySelector("td:nth-child(6)").textContent.trim();

                // Populate the modal
                document.querySelector('#createEmployeeModal h2').textContent = "Edit Employee";
                document.querySelector('input[name="emp_code"]').value = empCode;
                document.querySelector('input[name="emp_id"]').value = empCode;
                document.querySelector('input[name="first_name"]').value = firstName;
                document.querySelector('input[name="last_name"]').value = lastName;
                document.querySelector('input[name="username"]').value = username;
                document.querySelector('input[name="phone_number"]').value = phoneNumber;

                // Change form action to edit endpoint
                const form = document.getElementById("create-employee-form");
                form.action = "edit_employee.php";
                // Set is_edit to true
                form.querySelector('input[name="is_edit"]').value = "true";

                // Show modal
                document.getElementById("createEmployeeModal").style.display = "block";
            });
        });

        // Modal behavior
        const openBtn = document.getElementById("open-create-employee");
        const modal = document.getElementById("createEmployeeModal");
        const closeBtn = document.getElementById("closeCreateEmployeeModal");

        openBtn.onclick = () => {
            // Reset the form to creation mode before showing modal
            const form = document.getElementById("create-employee-form");
            form.reset();
            form.action = "create_employee.php";
            document.querySelector('#createEmployeeModal h2').textContent = "Add Employee";
            form.querySelector('input[name="is_edit"]').value = "false";
            form.querySelector('input[name="emp_id"]').value = "";
            modal.style.display = "block";
        };
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
    <script>
    // Add sorting to employee-table columns
    document.addEventListener("DOMContentLoaded", function () {
      const employeeTable = document.getElementById("employee-table");
      if (employeeTable) {
        const headers = employeeTable.querySelectorAll("th.sortable");
        let sortDirection = 1;
        let sortColumnIndex = null;

        headers.forEach((header, index) => {
          header.addEventListener("click", () => {
            if (sortColumnIndex === index) sortDirection *= -1;
            else {
              sortColumnIndex = index;
              sortDirection = 1;
            }

            const rows = Array.from(employeeTable.querySelector("tbody > tr"));
            rows.sort((a, b) => {
              const cellA = a.children[index + 1].textContent.trim().toLowerCase();
              const cellB = b.children[index + 1].textContent.trim().toLowerCase();
              return cellA.localeCompare(cellB) * sortDirection;
            });

            const tbody = employeeTable.querySelector("tbody");
            rows.forEach(row => tbody.appendChild(row));
          });
        });
      }
    });
    </script>