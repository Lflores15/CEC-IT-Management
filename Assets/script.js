// ========== Dynamic Log Event Time Display for Modal ==========
document.addEventListener("DOMContentLoaded", function () {
  function updateLogEventTime() {
    const now = new Date();
    const options = { month: '2-digit', day: '2-digit', year: 'numeric' };
    const dateStr = now.toLocaleDateString('en-US', options);
    const timeStr = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true });
    const timeDisplay = document.getElementById("log-event-time");
    if (timeDisplay) {
      timeDisplay.textContent = `${dateStr} | ${timeStr}`;
    }
  }
  updateLogEventTime();
  setInterval(updateLogEventTime, 1000);
});

// ========== Laptop Log Event Modal and Log Fetching ==========
function fetchDeviceLog(assetTag) {
  if (!assetTag || typeof assetTag !== 'string' || assetTag.trim() === '') {
    console.error("fetchDeviceLog called with invalid asset tag:", assetTag);
    return;
  }

  fetch(`/Forms/Assets/fetch_event_log.php?asset_tag=${encodeURIComponent(assetTag.trim())}`)
    .then(res => res.json())
    .then(data => {
      console.log("Fetched log entries:", data);
      const logHistoryTable = document.getElementById("device-log-history");
      logHistoryTable.innerHTML = "";

      if (!Array.isArray(data) || data.length === 0) {
        const row = document.createElement("tr");
        const cell = document.createElement("td");
        cell.colSpan = 4;
        cell.textContent = "No logs found for this device.";
        row.appendChild(cell);
        logHistoryTable.appendChild(row);
        return;
      }

      data.forEach(entry => {
        const row = document.createElement("tr");

        const dateCell = document.createElement("td");
        dateCell.textContent = entry.date;
        row.appendChild(dateCell);

        const timeCell = document.createElement("td");
        timeCell.textContent = entry.time;
        row.appendChild(timeCell);

        const typeCell = document.createElement("td");
        typeCell.textContent = entry.event_type;
        row.appendChild(typeCell);

        const memoCell = document.createElement("td");
        memoCell.textContent = entry.memo;
        row.appendChild(memoCell);

        logHistoryTable.appendChild(row);
      });
    })
    .catch(err => {
      console.error("Error fetching logs:", err);
    });
}

document.addEventListener("DOMContentLoaded", function () {
  const logButtons = document.querySelectorAll("tr.log-event-btn");
  const modal = document.getElementById("logEventModal");
  const logDeviceInput = document.getElementById("log-device-id");

  // Modal should open only on double-click, not single-click
  logButtons.forEach(btn => {
    btn.addEventListener("dblclick", function (e) {
      if (document.body.classList.contains("editing-mode")) {
        e.preventDefault();
        e.stopPropagation();
        return;
      }

      // Use Asset Tag for log filtering
      const assetTagCell = this.querySelector('td[data-column="asset_tag"]');
      const assetTag = assetTagCell ? assetTagCell.textContent.trim() : null;
      logDeviceInput.value = assetTag;

      if (!assetTag) {
        console.error("Asset tag is missing for selected device.");
        return;
      }
      modal.style.display = "block";
      fetchDeviceLog(assetTag);
    });
  });

  // Optionally handle a manual log modal open (if such a button exists)
  const manualLogBtn = document.getElementById("open-log-manual-modal");
  if (manualLogBtn) {
    manualLogBtn.addEventListener("click", function () {
      document.getElementById("log-device-id").value = "";
      document.getElementById("logEventModal").style.display = "block";
      // No asset tag, so fetch all logs
      fetch('/Forms/Assets/fetch_event_log.php')
        .then(res => res.json())
        .then(data => {
          const table = document.getElementById("device-log-history");
          table.innerHTML = "";
          if (!Array.isArray(data) || data.length === 0) {
            table.innerHTML = "<tr><td colspan='4'>No logs available.</td></tr>";
            return;
          }
          data.forEach(log => {
            const row = `<tr>
              <td>${log.date}</td>
              <td>${log.time}</td>
              <td>${log.event_type}</td>
              <td>${log.memo}</td>
            </tr>`;
            table.innerHTML += row;
          });
        });
    });
  }

  // ADD: Log Event Form Submission Handler (AJAX)
  const logEventForm = document.getElementById("log-event-form");
  if (logEventForm) {
    logEventForm.addEventListener("submit", function (e) {
      e.preventDefault();

      const form = e.target;
      const formData = new FormData(form);
      // Use value from hidden input for asset tag
      const assetTag = document.getElementById("log-device-id").value;
      console.log("Submitting log for asset tag:", assetTag);

      // Ensure assetTag is not null before fetch
      if (!assetTag) {
        console.error("No asset tag found, cannot fetch logs.");
        return;
      }

      fetch("manual_log.php", {
        method: "POST",
        body: formData
      })
        .then(() => {
          // Optionally, reset form fields
          form.reset();
          // Fetch updated logs for this asset tag using the hidden input value
          const refreshedAssetTag = document.getElementById("log-device-id").value;
          if (refreshedAssetTag) {
            fetchDeviceLog(refreshedAssetTag);
          }
        });
    });
  }
});
// Per-column filtering for device-table (laptop dashboard)
document.addEventListener("DOMContentLoaded", function () {
    // Only run if device-table exists
    const table = document.getElementById("device-table");
    if (!table) return;
    // Listen for input on all filter-inputs
    document.querySelectorAll(".filter-input").forEach(input => {
        input.addEventListener("input", () => {
            const rows = document.querySelectorAll("#device-table tbody tr");
            rows.forEach(row => {
                let match = true;
                document.querySelectorAll(".filter-input").forEach(filter => {
                    const col = filter.dataset.column;
                    const val = filter.value.toLowerCase();
                    const cell = row.querySelector(`td[data-column="${col}"]`);
                    const text = cell?.textContent.toLowerCase() || '';
                    if (!text.includes(val)) match = false;
                });
                row.style.display = match ? "" : "none";
            });
        });
    });
});
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
// The delete-selected-btn logic for employees is now handled inline in employee_Dashboard.php
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
            const role = this.dataset.role;

            // Populate form fields (no email field)
            document.getElementById("edit-user-id").value = userId;
            document.getElementById("edit-username").value = username;
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

                const deleteUserForm = document.getElementById('deleteUserForm');
                document.getElementById('delete-user-id').value = userId;
                document.getElementById('delete-username').textContent = username;

                // Update the form action dynamically to include the user ID
                deleteUserForm.action = `delete_user.php?id=${userId}`;

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
        
// Add sort functionality to each column header in the employee-table
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
        // +1 offset: skip the first checkbox column
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

    // Column selector buttons toggle logic with background color update
    document.querySelectorAll('.column-toggle-btn').forEach(button => {
      const input = button.nextElementSibling;

      // Set initial color based on 'active' class
      if (button.classList.contains('active')) {
        button.style.backgroundColor = '#28a745'; // green
      } else {
        button.style.backgroundColor = '#dc3545'; // red
      }

      button.addEventListener('click', () => {
        const isActive = button.classList.toggle('active');
        input.disabled = !isActive;

        // Update color based on active state
        if (isActive) {
          button.style.backgroundColor = '#28a745'; // green
        } else {
          button.style.backgroundColor = '#dc3545'; // red
        }
      });
    });
});


document.addEventListener("DOMContentLoaded", function () {
    // Toggle inline edit mode for table rows and handle click-to-edit behavior
    const editBtn = document.getElementById("edit-mode-btn");
    const cancelEditBtn = document.getElementById("cancel-edit-btn");
    let originalTableHTML = null;
    const deleteBtn = document.getElementById("delete-selected-btn");
    const undoBtn = document.getElementById("undo-delete-btn");
    let editing = false;

    // Restore delete-selected-btn logic for laptops
    if (deleteBtn) {
      deleteBtn.addEventListener("click", () => {
        const selected = Array.from(document.querySelectorAll(".row-checkbox:checked")).map(cb => cb.value);
        if (selected.length === 0) {
          alert("Please select device(s) to delete.");
          return;
        }

        if (!confirm(`Are you sure you want to delete ${selected.length} device(s)?`)) return;

        fetch("/Forms/Assets/delete_laptop.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ device_ids: selected })
        })
        .then(res => res.text())
        .then(response => {
          if (response.trim().toLowerCase().includes("successfully deleted selected laptops")) {
            alert("Devices deleted successfully.");
            location.reload();
          } else {
            alert("Error deleting devices: " + response);
          }
        })
        .catch(err => {
          alert("Request failed: " + err);
        });
      });
    }

    // Undo delete logic for laptops
    if (undoBtn) {
      undoBtn.addEventListener("click", function () {
        // Optionally, confirm undo
        if (!confirm("Are you sure you want to undo the last delete?")) return;
        fetch("/Forms/Assets/undo_delete.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" }
        })
        .then(res => res.text())
        .then(response => {
          if (response.trim().toLowerCase().includes("undo successful")) {
            alert("Undo successful. Devices have been restored.");
            location.reload();
          } else {
            alert("Undo failed: " + response);
          }
        })
        .catch(err => {
          alert("Undo request failed: " + err);
        });
      });
    }

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
                const tbody = document.querySelector(".device-table tbody");
                tbody.innerHTML = originalTableHTML;

                // Clear old double-click handlers by replacing each row with its clone
                const refreshedRows = document.querySelectorAll(".device-table .clickable-row");
                refreshedRows.forEach(row => {
                  const clone = row.cloneNode(true);
                  row.parentNode.replaceChild(clone, row);
                });
                // Rebind double-click modal logic for clickable-row
                document.querySelectorAll(".device-table .clickable-row").forEach(row => {
                  row.addEventListener("dblclick", function (e) {
                    if (document.body.classList.contains("editing-mode")) {
                      e.preventDefault();
                      e.stopPropagation();
                      return;
                    }

                    const assetTagCell = this.querySelector('td[data-column="asset_tag"]');
                    const assetTag = assetTagCell ? assetTagCell.textContent.trim() : null;
                    const modal = document.getElementById("logEventModal");
                    const logDeviceInput = document.getElementById("log-device-id");

                    if (!assetTag) {
                      console.error("Asset tag is missing for selected device.");
                      return;
                    }

                    logDeviceInput.value = assetTag;
                    modal.style.display = "block";
                    fetchDeviceLog(assetTag);
                  });
                });
            }

            editing = false;
            document.body.classList.remove("editing-mode");
            editBtn.textContent = "Edit Table";
            cancelEditBtn.style.display = "none";
            if (deleteBtn) deleteBtn.style.display = "none";
            if (undoBtn) undoBtn.style.display = "none";

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

    // Initial .clickable-row logic: open modal only on double-click
    document.querySelectorAll(".clickable-row").forEach(row => {
      row.addEventListener("dblclick", function (e) {
        if (document.body.classList.contains("editing-mode")) {
          e.preventDefault();
          e.stopPropagation();
          return;
        }

        const assetTagCell = this.querySelector('td[data-column="asset_tag"]');
        const assetTag = assetTagCell ? assetTagCell.textContent.trim() : null;
        const modal = document.getElementById("logEventModal");
        const logDeviceInput = document.getElementById("log-device-id");

        if (!assetTag) {
          console.error("Asset tag is missing for selected device.");
          return;
        }

        logDeviceInput.value = assetTag;
        modal.style.display = "block";
        fetchDeviceLog(assetTag);
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
        status: ['Active', 'Pending Return', 'Shelf-CC', 'Shelf-MD', 'Shelf-HS', 'Lost', 'Decommissioned'],
        internet_policy: [
            'Default',
            'Office',
            'Admin',
            'Accounting',
            'Estimating',
            'Executive',
            'HR'
        ],
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
            // Retrieve asset tag from the row's asset_tag cell
            const assetTagCell = this.querySelector('td[data-column="asset_tag"]');
            const assetTag = assetTagCell ? assetTagCell.textContent.trim() : null;
            if (assetTag) {
                e.preventDefault();
                e.stopPropagation();
                const modal = document.getElementById("logEventModal");
                const formDeviceId = document.getElementById("log-device-id");
                if (modal && formDeviceId) {
                    formDeviceId.value = assetTag;
                    modal.style.display = "block";
                    fetchDeviceLog(assetTag);
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

// ========== Export CSV Button Handler ==========
document.addEventListener("DOMContentLoaded", function () {
    const exportCsvBtn = document.getElementById("export-csv-btn");
    if (exportCsvBtn) {
        exportCsvBtn.addEventListener("click", function () {
            window.location.href = "export_laptops.php";
        });
    }
});

// ========== Fetch Employee Details for Assign To Dropdown ==========
function fetchEmployeeDetails(emp_code) {
  if (!emp_code) return;

  fetch(`/Forms/Employees/get_employee_info.php?emp_code=${encodeURIComponent(emp_code)}`)
    .then(res => res.json())
    .then(data => {
      document.getElementById('first_name').value = data.first_name || '';
      document.getElementById('last_name').value = data.last_name || '';
      document.getElementById('username').value = data.username || '';
      document.getElementById('phone_number').value = data.phone_number || '';
    })
    .catch(err => console.error("Failed to fetch employee details:", err));
}