document.addEventListener("DOMContentLoaded", function () {
  const runAuditBtn = document.getElementById("runAuditBtn");
  const auditFileInput = document.getElementById("auditCsvFile");

  if (runAuditBtn && auditFileInput) {
    runAuditBtn.addEventListener("click", () => {
      const file = auditFileInput.files[0];
      if (!file) return alert("Please select a CSV file to audit.");

      const formData = new FormData();
      formData.append("csv_file", file);

      fetch("/Forms/Assets/audit_table.php", {
        method: "POST",
        body: formData
      })
      .then(res => res.text()) // read as raw text first
      .then(text => {
        try {
          const data = JSON.parse(text); // parse JSON safely
          if (data.success) {
            alert("Audit complete. Refreshing...");
            location.reload();
          } else {
            alert("Audit failed: " + data.message);
          }
        } catch (err) {
          console.error("Invalid JSON response:", text);
          alert("Audit failed: Invalid response format.");
        }
      })
      .catch(err => {
        alert("An error occurred: " + err);
      });
    });
  }
});
// Audit Laptop Modal logic
document.addEventListener("DOMContentLoaded", function () {
  const auditModal = document.getElementById("auditLaptopModal");
  const openAuditBtn = document.getElementById("audit-laptop-btn");
  const closeAuditBtn = document.getElementById("closeAuditLaptopModal");

  if (auditModal && openAuditBtn && closeAuditBtn) {
    openAuditBtn.addEventListener("click", function (e) {
      e.preventDefault();
      auditModal.style.display = "block";
    });

    closeAuditBtn.addEventListener("click", function () {
      auditModal.style.display = "none";
    });

    window.addEventListener("click", function (event) {
      if (event.target === auditModal) {
        auditModal.style.display = "none";
      }
    });
  }
});
// ========== Delete Selected Devices and Undo Delete ==========
document.addEventListener("DOMContentLoaded", function () {
    const deleteBtn = document.getElementById("delete-selected-btn");
    // Add deleteInProgress variable to prevent double prompts
    let deleteInProgress = false;
    if (deleteBtn) {
        deleteBtn.addEventListener("click", function () {
            if (deleteBtn.disabled || deleteInProgress) return;
            deleteInProgress = true;
            const checkboxes = document.querySelectorAll(".row-checkbox:checked");
            if (!checkboxes.length) {
                alert("Please select devices to delete.");
                deleteInProgress = false;
                return;
            }
            if (!confirm("Are you sure you want to delete the selected devices?")) {
                deleteInProgress = false;
                return;
            }
            const ids = Array.from(checkboxes).map(cb => cb.value);
            deleteBtn.disabled = true;
            fetch("delete_laptop.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ device_ids: ids })
            })
            .then(res => res.text())
            .then(msg => {
                alert(msg);
                location.reload();
            })
            .catch(err => {
                alert("Delete failed: " + err);
                deleteBtn.disabled = false;
            })
            .finally(() => {
                deleteInProgress = false;
            });
        });
    }
    // Undo Last Delete button
    const undoBtn = document.getElementById("undo-delete-btn");
    if (undoBtn) {
        let undoInProgress = false;
        undoBtn.addEventListener("click", () => {
            if (undoInProgress) return;
            undoInProgress = true;
            fetch("undo_delete.php")
                .then(res => res.text())
                .then(msg => {
                    alert(msg);
                    location.reload();
                })
                .catch(err => alert("Undo failed: " + err))
                .finally(() => {
                    undoInProgress = false;
                });
        });
    }
});
// ========== Script Initialization & UI Interaction Logic ==========
document.addEventListener("DOMContentLoaded", function () {
    console.log("JavaScript Loaded ✅");

    // Handle profile dropdown menu toggle on click
    // Profile Dropdown
    const profileBtn = document.querySelector(".profile-btn");
    const profileDropdown = document.querySelector(".profile-dropdown");

    if (profileBtn) {
        profileBtn.addEventListener("click", function (event) {
            event.stopPropagation();
            profileDropdown.classList.toggle("active");
        });
    }

    // Close profile dropdown if clicking outside
    document.addEventListener("click", function (event) {
        if (!profileDropdown.contains(event.target)) {
            profileDropdown.classList.remove("active");
        }
    });

    // Handle expandable sidebar dropdown sections
    // Sidebar Dropdowns
    const dropdownBtns = document.querySelectorAll(".dropdown-btn");
    
    dropdownBtns.forEach((btn) => {
        btn.addEventListener("click", function () {
            const dropdownContainer = this.parentElement;
            const isOpen = dropdownContainer.classList.toggle("open");

            // Toggle 'open' class on the button itself
            this.classList.toggle("open", isOpen);

            // Close other dropdowns when one is opened
            dropdownBtns.forEach(otherBtn => {
                if (otherBtn !== btn) {
                    otherBtn.classList.remove("open");
                    otherBtn.parentElement.classList.remove("open");
                }
            });
        });
    });

    // Enable column sorting on tables
    // Table Sorting
    const tables = document.querySelectorAll("table");

tables.forEach((table) => {
    const headers = table.querySelectorAll("th.sortable");

    headers.forEach((header, index) => {
        header.addEventListener("click", function () {
            const actualIndex = index + 1; // Offset for checkbox column
            let tbody = table.querySelector("tbody");
            let rows = Array.from(tbody.rows);
            let isAscending = header.classList.contains("asc");

            rows.sort((rowA, rowB) => {
                let cellA = rowA.cells[actualIndex]?.textContent.trim().toLowerCase() || "";
                let cellB = rowB.cells[actualIndex]?.textContent.trim().toLowerCase() || "";

                if (!isNaN(cellA) && !isNaN(cellB)) {
                    return isAscending ? cellA - cellB : cellB - cellA;
                }
                return isAscending ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
            });

            headers.forEach(h => h.classList.remove("asc", "desc"));
            header.classList.toggle("asc", !isAscending);
            header.classList.toggle("desc", isAscending);

            tbody.append(...rows);
        });
    });
});

    // Setup filtering by name, tag, category, and status
    // Table Filtering
    const filterName = document.getElementById("filter-name");
    const filterTag = document.getElementById("filter-tag");
    const filterCategory = document.getElementById("filter-category");
    const filterStatus = document.getElementById("filter-status");

    // Updated: Filter table dynamically for Asset Tag and Status columns
    function filterTable() {
        const headers = document.querySelectorAll("#device-table thead th");
        // Find column indexes by header label
        const tagIndex = Array.from(headers).findIndex(th => th.textContent.trim() === "Asset Tag");
        const statusIndex = Array.from(headers).findIndex(th => th.textContent.trim() === "Status");

        const tagValue = filterTag?.value?.toLowerCase() || "";
        const statusValue = filterStatus?.value?.toLowerCase() || "";

        document.querySelectorAll("#device-table tbody tr").forEach(row => {
            const cells = row.cells;
            if (!cells.length) return;

            const tag = cells[tagIndex]?.textContent.toLowerCase() || "";
            const status = cells[statusIndex]?.textContent.toLowerCase() || "";

            const tagMatch = tagValue === "" || tag.includes(tagValue);
            // "status" option (default) acts as "All"
            const statusMatch = statusValue === "status" || status.includes(statusValue);

            row.style.display = tagMatch && statusMatch ? "" : "none";
        });
    }

    if (filterName) filterName.addEventListener("input", filterTable);
    if (filterTag) filterTag.addEventListener("input", filterTable);
    if (filterCategory) filterCategory.addEventListener("change", filterTable);
    if (filterStatus) filterStatus.addEventListener("change", filterTable);

    // Setup logic for Edit User modal (opening, closing, form submission)
    // Modal Functionality
    const modal = document.getElementById("editModal");
    const closeModal = document.querySelector(".close");
    const editForm = document.getElementById("editUserForm");

    document.querySelectorAll(".edit-btn").forEach(button => {
        button.addEventListener("click", function () {
            document.getElementById("edit-user-id").value = this.dataset.id;
            document.getElementById("edit-username").value = this.dataset.username;
            document.getElementById("edit-email").value = this.dataset.email;
            document.getElementById("edit-role").value = this.dataset.role;
            modal.style.display = "block";
        });
    });

    if (closeModal) {
        closeModal.addEventListener("click", function () {
            modal.style.display = "none";
        });
    }

    if (editForm) {
        editForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const formData = new FormData(editForm);
            fetch("user_Edit.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    location.reload();
                }
            });
        });
    }
});

document.addEventListener("DOMContentLoaded", function () {
    // Setup logic for Create Device modal (opening, closing, form submission)
    // Create Modal logic
    const createModal = document.getElementById('createModal');
    const openBtn = document.getElementById('openCreateModal');
    const closeBtn = document.getElementById('closeCreateModal');

    if (openBtn && closeBtn && createModal) {
        openBtn.onclick = () => {
            createModal.style.display = 'block';
        };

        closeBtn.onclick = () => {
            createModal.style.display = 'none';
        };

        window.onclick = (event) => {
            if (event.target === createModal) {
                createModal.style.display = 'none';
            }
        };
    }

// Edit Modal logic
const editModal = document.getElementById("editModal");
const closeEditModal = document.getElementById("closeEditModal");

if (editModal && closeEditModal) {
    // Setup dynamic population of Edit User modal fields
    // Example: open modal dynamically with user data
    document.querySelectorAll(".edit-btn").forEach(button => {
        button.addEventListener("click", function () {
            const userId = this.dataset.id;
            const username = this.dataset.username;
            const email = this.dataset.email;
            const role = this.dataset.role;

            // Populate form fields
            document.getElementById("edit-user-id").value = userId;
            document.getElementById("edit-username").value = username;
            document.getElementById("edit-email").value = email;
            document.getElementById("edit-role").value = role;

            // Show modal
            editModal.style.display = "block";
        });
    });

    // Close on "×" button
    closeEditModal.onclick = () => {
        editModal.style.display = "none";
    };

    // Close on outside click
    window.addEventListener("click", function (event) {
        if (event.target === editModal) {
            editModal.style.display = "none";
        }
    });
}

    // Setup logic for Delete User modal (opening, closing)
    // Delete Modal logic
    const deleteModal = document.getElementById("deleteModal");
    const closeDeleteModal = document.getElementById("closeDeleteModal");
    const cancelDeleteBtn = document.getElementById("cancelDeleteBtn");

    if (deleteModal && closeDeleteModal && cancelDeleteBtn) {
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function () {
                const userId = this.dataset.id;
                const username = this.dataset.username;

                document.getElementById('delete-user-id').value = userId;
                document.getElementById('delete-username').textContent = username;

                deleteModal.style.display = "block";
            });
        });

        closeDeleteModal.onclick = () => {
            deleteModal.style.display = "none";
        };

        cancelDeleteBtn.onclick = () => {
            deleteModal.style.display = "none";
        };

        window.addEventListener("click", function (event) {
            if (event.target === deleteModal) {
                deleteModal.style.display = "none";
            }
        });
    }
    
});

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
        
// Add sort functionality to each column header in the table
const table = document.getElementById("device-table");
if (table) {
  const headers = table.querySelectorAll("th.sortable");
  let sortDirection = 1;
  let sortColumnIndex = null;

  headers.forEach((header, index) => {
    header.addEventListener("click", () => {
      if (sortColumnIndex === index) sortDirection *= -1;
      else {
        sortColumnIndex = index;
        sortDirection = 1;
      }

      const rows = Array.from(table.querySelectorAll("tbody > tr"));
      rows.sort((a, b) => {
        const cellA = a.children[index].textContent.trim().toLowerCase();
        const cellB = b.children[index].textContent.trim().toLowerCase();
        return cellA.localeCompare(cellB) * sortDirection;
      });

      const tbody = table.querySelector("tbody");
      rows.forEach(row => tbody.appendChild(row));
    });
  });
}

// Toggle modal for selecting visible table columns
const editBtn = document.getElementById("edit-columns-btn");
const columnModal = document.getElementById("column-selector");

if (editBtn && columnModal) {
  editBtn.addEventListener("click", () => {
    columnModal.style.display = columnModal.style.display === "none" ? "block" : "none";
  });

  document.getElementById("column-form")?.addEventListener("submit", function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch("update_columns.php", {
      method: "POST",
      body: formData
    }).then(() => location.reload());
  });
}

document.addEventListener("DOMContentLoaded", function () {
    const editBtn = document.getElementById("edit-columns-btn");
    const columnModal = document.getElementById("column-selector");

    if (editBtn && columnModal) {
        editBtn.addEventListener("click", () => {
            columnModal.style.display = columnModal.style.display === "none" ? "block" : "none";
        });

        const columnForm = document.getElementById("column-form");
        if (columnForm) {
            columnForm.addEventListener("submit", function (e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch("update_columns.php", {
                    method: "POST",
                    body: formData
                }).then(() => location.reload());
            });
        }
    }
});


document.addEventListener("DOMContentLoaded", function () {
    // Toggle inline edit mode for table rows and handle click-to-edit behavior
    const editBtn = document.getElementById("edit-mode-btn");
    const cancelEditBtn = document.getElementById("cancel-edit-btn");
    let originalTableHTML = null;
    const deleteBtn = document.getElementById("delete-selected-btn");
    const undoBtn = document.getElementById("undo-delete-btn");
    let editing = false;

    // Toggle editing mode
    editBtn.addEventListener("click", () => {
        editing = !editing;
        document.body.classList.toggle("editing-mode", editing);

        // Always add .editable-cell class and enable/disable checkboxes
        document.querySelectorAll(".device-table td").forEach(cell => {
            cell.classList.add("editable-cell");
        });
        document.querySelectorAll(".row-checkbox").forEach(cb => cb.disabled = !editing);

        if (editing) {
            originalTableHTML = document.querySelector(".device-table tbody").innerHTML;
            editBtn.textContent = "Save Table";
            cancelEditBtn.style.display = "inline-block";
            if (deleteBtn) deleteBtn.style.display = "inline-block";
            if (undoBtn) undoBtn.style.display = "inline-block";
        } else {
            // Remove editable-cell class and disable checkboxes when exiting edit mode
            document.querySelectorAll(".device-table td").forEach(cell => {
                cell.classList.remove("editable-cell");
            });
            document.querySelectorAll(".row-checkbox").forEach(cb => cb.disabled = true);
            editBtn.textContent = "Edit Table";
            cancelEditBtn.style.display = "none";
            if (deleteBtn) deleteBtn.style.display = "none";
            if (undoBtn) undoBtn.style.display = "none";
            bindRowEvents();
        }
    });
    
    if (cancelEditBtn) {
        cancelEditBtn.addEventListener("click", () => {
            if (originalTableHTML) {
                document.querySelector(".device-table tbody").innerHTML = originalTableHTML;
            }
            editing = false;
            document.body.classList.remove("editing-mode");
            editBtn.textContent = "Edit Table";
            cancelEditBtn.style.display = "none";
            if (deleteBtn) deleteBtn.style.display = "none";
            if (undoBtn) undoBtn.style.display = "none";

            // Re-enable double click handlers on reverted rows
            document.querySelectorAll(".clickable-row").forEach(row => {
                row.addEventListener("dblclick", function (e) {
                    const isEditing = document.body.classList.contains("editing-mode");
                    const href = this.getAttribute("data-href");
                    if (!isEditing && href) {
                        window.location.href = href;
                    } else {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                });
            });

            // Rebind inline editing after cancel
            document.querySelectorAll(".device-table td").forEach(cell => {
                cell.addEventListener("dblclick", function (e) {
                    if (!document.body.classList.contains("editing-mode")) return;
                    if (this.querySelector("input, select")) return;
                    e.stopPropagation();

                    const column = this.getAttribute("data-column");
                    const deviceId = this.getAttribute("data-id");
                    const currentText = this.textContent.trim();

                    if (!column || !deviceId) return;

                    if (selectOptions[column?.toLowerCase()]) {
                        const select = document.createElement("select");
                        select.className = "inline-edit-select";

                        if (column === "assigned_to") {
                            selectOptions[column.toLowerCase()].forEach(opt => {
                                const option = document.createElement("option");
                                option.value = opt.id;
                                option.textContent = opt.name;
                                const currentId = cell.getAttribute('data-emp-id');
                                if (opt.name === currentText) option.selected = true;
                                select.appendChild(option);
                            });
                        } else {
                            selectOptions[column.toLowerCase()].forEach(opt => {
                                const option = document.createElement("option");
                                option.value = opt;
                                option.textContent = opt;
                                if (opt.toLowerCase() === currentText.toLowerCase()) option.selected = true;
                                select.appendChild(option);
                            });
                        }

                        select.addEventListener("blur", () => {
                            const newValue = select.value;
                            sendUpdate(deviceId, column, newValue, cell, currentText);
                        });

                        this.textContent = "";
                        this.appendChild(select);
                        select.focus();
                    } else {
                        const input = document.createElement("input");
                        input.type = "text";
                        input.value = currentText;
                        input.className = "inline-edit-input";

                        input.addEventListener("blur", () => {
                            const newValue = input.value.trim();
                            sendUpdate(deviceId, column, newValue, cell, currentText);
                        });

                        input.addEventListener("keydown", e => {
                            if (e.key === "Enter") input.blur();
                            if (e.key === "Escape") this.textContent = currentText;
                        });

                        this.textContent = "";
                        this.appendChild(input);
                        input.focus();
                    }
                });
            });
        });
    }

    document.querySelectorAll(".clickable-row").forEach(row => {
        row.addEventListener("dblclick", function (e) {
            const deviceId = this.getAttribute("data-device-id");
            if (deviceId) {
                e.preventDefault();
                e.stopPropagation();
                const modal = document.getElementById("logEventModal");
                const formDeviceId = document.getElementById("log-device-id");
                if (modal && formDeviceId) {
                    formDeviceId.value = deviceId;
                    modal.style.display = "block";
                }
            }
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    // Handle Create Device modal open, close, and form submission
    const openBtn = document.getElementById("open-create-modal");
    const modal = document.querySelector(".create-device-modal");
    const closeBtn = document.getElementById("close-create-modal");
    const form = document.getElementById("create-device-form");

    if (openBtn && closeBtn && modal && form) {
        openBtn.onclick = () => {
            if (modal.style.display !== "block") {
                modal.style.display = "block";
            }
        };
        closeBtn.onclick = () => {
            if (modal.style.display !== "none") {
                modal.style.display = "none";
            }
        };
        window.addEventListener("click", function (e) {
            if (e.target === modal) {
                modal.style.display = "none";
            }
        });
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(form);
            fetch("create_laptop.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.text())
            .then(response => {
                const result = document.getElementById("create-result-message");
                if (response.toLowerCase().includes("success")) {
                    result.textContent = response;
                    result.style.color = "green";
                    modal.style.display = "none";
                    form.reset();
                    location.reload();
                } else {
                    result.textContent = response;
                    result.style.color = "red";
                    result.style.display = "block";
                }
            })
            .catch(err => alert("Error: " + err));
        });
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const selectOptions = {
        status: ['Active', 'Pending Return', 'Shelf', 'Lost', 'Decommissioned'],
        internet_policy: ['Admin', 'Default', 'Office'],
        assigned_to: window.employeeOptions || []  // will be injected from PHP
    };

    // 1. Add a new object to store pending edits
    let pendingEdits = {};

    // Enable inline editing for supported fields (dropdown or text input)
    document.querySelectorAll(".device-table td").forEach(cell => {
        cell.addEventListener("dblclick", function (e) {
            if (!document.body.classList.contains("editing-mode")) return;
            if (this.querySelector("input, select")) return;
            e.stopPropagation();

            const column = this.getAttribute("data-column");
            const deviceId = this.getAttribute("data-id");
            const currentText = this.textContent.trim();

            if (!column || !deviceId) return;

            if (selectOptions[column?.toLowerCase()]) {
                const select = document.createElement("select");
                select.className = "inline-edit-select";

                if (column === "assigned_to") {
                    selectOptions[column.toLowerCase()].forEach(opt => {
                        const option = document.createElement("option");
                        option.value = opt.id;
                        option.textContent = opt.name;
                        const currentId = cell.getAttribute('data-emp-id');
                        if ((opt.id === "" && !currentId) || opt.id == currentId) option.selected = true;
                        select.appendChild(option);
                    });
                } else {
                    selectOptions[column.toLowerCase()].forEach(opt => {
                        const option = document.createElement("option");
                        option.value = opt;
                        option.textContent = opt;
                        if (opt.toLowerCase() === currentText.toLowerCase()) option.selected = true;
                        select.appendChild(option);
                    });
                }

                select.addEventListener("blur", () => {
                    const newValue = select.value;
                    // 2. Store pending edit instead of sending update
                    if (!pendingEdits[deviceId]) pendingEdits[deviceId] = {};
                    pendingEdits[deviceId][column] = newValue;
                    cell.textContent = newValue;
                });

                this.textContent = "";
                this.appendChild(select);
                select.focus();

            } else {
                const input = document.createElement("input");
                input.type = "text";
                input.value = currentText;
                input.className = "inline-edit-input";

                input.addEventListener("blur", () => {
                    const newValue = input.value.trim();
                    // 2. Store pending edit instead of sending update
                    if (!pendingEdits[deviceId]) pendingEdits[deviceId] = {};
                    pendingEdits[deviceId][column] = newValue;
                    cell.textContent = newValue;
                });

                input.addEventListener("keydown", e => {
                    if (e.key === "Enter") input.blur();
                    if (e.key === "Escape") this.textContent = currentText;
                });

                this.textContent = "";
                this.appendChild(input);
                input.focus();
            }
        });
    });

    // 3. Modify the "Save Table" button logic
    const editBtn = document.getElementById("edit-mode-btn");
    const cancelEditBtn = document.getElementById("cancel-edit-btn");
    const deleteBtn = document.getElementById("delete-selected-btn");
    let originalTableHTML = null;
    let editing = false;

    editBtn.addEventListener("click", () => {
        editing = !editing;
        document.body.classList.toggle("editing-mode", editing);

        if (editing) {
            originalTableHTML = document.querySelector(".device-table tbody").innerHTML;
            editBtn.textContent = "Save Table";
            cancelEditBtn.style.display = "inline-block";
            if (deleteBtn) deleteBtn.style.display = "inline-block";
        } else {
            editBtn.textContent = "Edit Table";
            cancelEditBtn.style.display = "none";
            if (deleteBtn) deleteBtn.style.display = "none";
            // Submit all pending edits
            const promises = [];
            for (const deviceId in pendingEdits) {
                for (const column in pendingEdits[deviceId]) {
                    const value = pendingEdits[deviceId][column];
                    promises.push(fetch("update_cell.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: `device_id=${encodeURIComponent(deviceId)}&column=${encodeURIComponent(column)}&value=${encodeURIComponent(value)}`
                    }));
                }
            }
            Promise.all(promises).then(() => location.reload());
        }
    });

    if (cancelEditBtn) {
        cancelEditBtn.addEventListener("click", () => {
            if (originalTableHTML) {
                document.querySelector(".device-table tbody").innerHTML = originalTableHTML;
            }
            editing = false;
            document.body.classList.remove("editing-mode");
            editBtn.textContent = "Edit Table";
            cancelEditBtn.style.display = "none";
            if (deleteBtn) deleteBtn.style.display = "none";

            // Re-enable double click handlers on reverted rows
            document.querySelectorAll(".clickable-row").forEach(row => {
                row.addEventListener("dblclick", function (e) {
                    const isEditing = document.body.classList.contains("editing-mode");
                    const href = this.getAttribute("data-href");
                    if (!isEditing && href) {
                        window.location.href = href;
                    } else {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                });
            });

            // Rebind inline editing after cancel
            document.querySelectorAll(".device-table td").forEach(cell => {
                cell.addEventListener("dblclick", function (e) {
                    if (!document.body.classList.contains("editing-mode")) return;
                    if (this.querySelector("input, select")) return;
                    e.stopPropagation();

                    const column = this.getAttribute("data-column");
                    const deviceId = this.getAttribute("data-id");
                    const currentText = this.textContent.trim();

                    if (!column || !deviceId) return;

                    if (selectOptions[column?.toLowerCase()]) {
                        const select = document.createElement("select");
                        select.className = "inline-edit-select";

                        if (column === "assigned_to") {
                            selectOptions[column.toLowerCase()].forEach(opt => {
                                const option = document.createElement("option");
                                option.value = opt.id;
                                option.textContent = opt.name;
                                const currentId = cell.getAttribute('data-emp-id');
                                if ((opt.id === "" && !currentId) || opt.id == currentId) option.selected = true;
                                select.appendChild(option);
                            });
                        } else {
                            selectOptions[column.toLowerCase()].forEach(opt => {
                                const option = document.createElement("option");
                                option.value = opt;
                                option.textContent = opt;
                                if (opt.toLowerCase() === currentText.toLowerCase()) option.selected = true;
                                select.appendChild(option);
                            });
                        }

                        select.addEventListener("blur", () => {
                            const newValue = select.value;
                            if (!pendingEdits[deviceId]) pendingEdits[deviceId] = {};
                            pendingEdits[deviceId][column] = newValue;
                            cell.textContent = newValue;
                        });

                        this.textContent = "";
                        this.appendChild(select);
                        select.focus();
                    } else {
                        const input = document.createElement("input");
                        input.type = "text";
                        input.value = currentText;
                        input.className = "inline-edit-input";

                        input.addEventListener("blur", () => {
                            const newValue = input.value.trim();
                            if (!pendingEdits[deviceId]) pendingEdits[deviceId] = {};
                            pendingEdits[deviceId][column] = newValue;
                            cell.textContent = newValue;
                        });

                        input.addEventListener("keydown", e => {
                            if (e.key === "Enter") input.blur();
                            if (e.key === "Escape") this.textContent = currentText;
                        });

                        this.textContent = "";
                        this.appendChild(input);
                        input.focus();
                    }
                });
            });

            // 4. Clear unsaved changes
            pendingEdits = {};
        });
    }
});

// Import Modal Script
document.addEventListener("DOMContentLoaded", function () {
    const importModal = document.getElementById("importLaptopModal");
    const openImportBtn = document.getElementById("openImportLaptopModal");
    const closeImportBtn = document.getElementById("closeImportLaptopModal");

    if (openImportBtn && closeImportBtn && importModal) {
        openImportBtn.addEventListener("click", () => {
            importModal.style.display = "block";
        });

        closeImportBtn.addEventListener("click", () => {
            importModal.style.display = "none";
        });
        window.addEventListener("click", (e) => {
            if (e.target === importModal) {
                importModal.style.display = "none";
            }
        });
    }
});

// Helper function to bind row events
function bindRowEvents() {
    document.querySelectorAll(".clickable-row").forEach(row => {
        row.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();
        });
        row.addEventListener("dblclick", function (e) {
            const deviceId = this.getAttribute("data-device-id");
            if (deviceId) {
                e.preventDefault();
                e.stopPropagation();
                const modal = document.getElementById("logEventModal");
                const formDeviceId = document.getElementById("log-device-id");
                if (modal && formDeviceId) {
                    formDeviceId.value = deviceId;
                    modal.style.display = "block";
                }
            }
        });
    });

    document.querySelectorAll(".device-table td").forEach(cell => {
        cell.addEventListener("dblclick", function (e) {
            if (!document.body.classList.contains("editing-mode")) return;
            if (this.querySelector("input, select")) return;
            e.stopPropagation();

            const column = this.getAttribute("data-column");
            const deviceId = this.getAttribute("data-id");
            const currentText = this.textContent.trim();

            if (!column || !deviceId) return;

            if (selectOptions[column?.toLowerCase()]) {
                const select = document.createElement("select");
                select.className = "inline-edit-select";

                if (column === "assigned_to") {
                    selectOptions[column.toLowerCase()].forEach(opt => {
                        const option = document.createElement("option");
                        option.value = opt.id;
                        option.textContent = opt.name;
                        const currentId = cell.getAttribute('data-emp-id');
                        if ((opt.id === "" && !currentId) || opt.id == currentId) option.selected = true;
                        select.appendChild(option);
                    });
                } else {
                    selectOptions[column.toLowerCase()].forEach(opt => {
                        const option = document.createElement("option");
                        option.value = opt;
                        option.textContent = opt;
                        if (opt.toLowerCase() === currentText.toLowerCase()) option.selected = true;
                        select.appendChild(option);
                    });
                }

                select.addEventListener("blur", () => {
                    const newValue = select.value;
                    sendUpdate(deviceId, column, newValue, cell, currentText);
                });

                this.textContent = "";
                this.appendChild(select);
                select.focus();
            } else {
                const input = document.createElement("input");
                input.type = "text";
                input.value = currentText;
                input.className = "inline-edit-input";

                input.addEventListener("blur", () => {
                    const newValue = input.value.trim();
                    sendUpdate(deviceId, column, newValue, cell, currentText);
                });

                input.addEventListener("keydown", e => {
                    if (e.key === "Enter") input.blur();
                    if (e.key === "Escape") this.textContent = currentText;
                });

                this.textContent = "";
                this.appendChild(input);
                input.focus();
            }
        });
    });
}


// Import Laptop CSV Form Submission (AJAX)
document.addEventListener("DOMContentLoaded", () => {
  const importForm = document.getElementById("importLaptopForm");
  const importResult = document.getElementById("import-result-message");

  if (importForm) {
    importForm.addEventListener("submit", function (e) {
      e.preventDefault();
      const formData = new FormData(importForm);

      fetch("import_laptops.php", {
        method: "POST",
        body: formData
      })
        .then(res => res.json())
        .then(data => {
          importResult.style.display = "block";
          importResult.innerHTML = data.message;
          importResult.style.color = data.status === "success" ? "green" : "red";
        })
        .catch(err => {
          importResult.style.display = "block";
          importResult.textContent = "An error occurred while importing.";
          importResult.style.color = "red";
        });
    });
  }
});