// =====================
// General Modal Functions
// =====================
function openModal(modalId) {
    closeAllModals();
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = "block";
        const overlay = document.querySelector(".modal-overlay");
        if (overlay) overlay.style.display = "block";
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = "none";
    }
    const overlay = document.querySelector(".modal-overlay");
    if (overlay) overlay.style.display = "none";
}

function closeAllModals() {
    document.querySelectorAll('.laptop-modal-content-wrapper').forEach(modal => {
        modal.style.display = 'none';
    });
    const overlay = document.querySelector(".modal-overlay");
    if (overlay) overlay.style.display = "none";
}

// =====================
// Run When DOM Ready
// =====================
document.addEventListener("DOMContentLoaded", function () {

    // Setup Populate Table (Import Modal)
    const openImportBtn = document.getElementById("openImportLaptopModal");
    const closeImportBtn = document.getElementById("closeImportLaptopModal");
    const importForm = document.getElementById("importLaptopForm");
    const importResult = document.getElementById("import-result-message");

    if (openImportBtn) {
        openImportBtn.addEventListener("click", function (e) {
            e.preventDefault();
            openModal("importLaptopModal");
            importResult.style.display = "none";
            importResult.innerHTML = "";
        });
    }

    if (closeImportBtn) {
        closeImportBtn.addEventListener("click", function () {
            const success = importResult?.innerHTML.includes("Imported/updated") || importResult?.innerHTML.includes("Success");
            closeModal("importLaptopModal");
            if (success) location.reload();
        });
    }

    if (importForm && importResult) {
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
                importResult.style.backgroundColor = "#d4edda";
                importResult.style.color = "#155724";
                importResult.style.border = "1px solid #c3e6cb";
                importResult.style.padding = "12px";
                importResult.innerHTML = data.message || "✅ Import successful.";

                const refreshBtn = document.createElement("button");
                refreshBtn.textContent = "Refresh Page Now";
                refreshBtn.style.marginTop = "10px";
                refreshBtn.style.width = "100%";
                refreshBtn.style.padding = "10px";
                refreshBtn.style.backgroundColor = "#28a745";
                refreshBtn.style.color = "white";
                refreshBtn.style.border = "none";
                refreshBtn.style.borderRadius = "5px";
                refreshBtn.style.cursor = "pointer";

                refreshBtn.addEventListener("click", function () {
                    closeModal("importLaptopModal");
                    location.reload();
                });

                importResult.appendChild(document.createElement("br"));
                importResult.appendChild(refreshBtn);
            })
            .catch(err => {
                importResult.style.display = "block";
                importResult.style.backgroundColor = "#f8d7da";
                importResult.style.color = "#721c24";
                importResult.style.border = "1px solid #f5c6cb";
                importResult.style.padding = "12px";
                importResult.innerHTML = `❌ Import failed: ${err}`;
            });
        });
    }

    // Setup Audit Laptops Modal
    const openAuditBtn = document.getElementById("audit-laptop-btn");
    const closeAuditBtn = document.getElementById("closeAuditLaptopModal");
    const runAuditBtn = document.getElementById("runAuditBtn");
    const auditFileInput = document.getElementById("auditCsvFile");
    const auditResultBox = document.getElementById("audit-result-message");

    if (openAuditBtn) {
        openAuditBtn.addEventListener("click", function (e) {
            e.preventDefault();
            openModal("auditLaptopModal");
            auditResultBox.style.display = "none";
            auditResultBox.innerHTML = "";
        });
    }

    if (closeAuditBtn) {
        closeAuditBtn.addEventListener("click", function () {
            const success = auditResultBox?.innerHTML.includes("Audit complete");
            closeModal("auditLaptopModal");
            if (success) location.reload();
        });
    }

    if (runAuditBtn && auditFileInput && auditResultBox) {
        runAuditBtn.addEventListener("click", function auditHandler(e) {
            e.preventDefault();
            const file = auditFileInput.files[0];
            if (!file) return alert("Please select a CSV file to audit.");

            auditResultBox.style.display = "block";
            auditResultBox.style.color = "#333";
            auditResultBox.style.backgroundColor = "#fff3cd";
            auditResultBox.style.padding = "12px";
            auditResultBox.innerHTML = "⏳ Auditing employee assignments...";

            const formData = new FormData();
            formData.append("csv_file", file);

            fetch("/Forms/Assets/audit_table.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    auditResultBox.style.color = "#155724";
                    auditResultBox.style.backgroundColor = "#d4edda";
                    auditResultBox.innerHTML = `✅ Audit complete: ${data.count} employee(s) processed.`;

                    runAuditBtn.textContent = "Refresh Page Now";
                    runAuditBtn.style.backgroundColor = "#28a745";
                    runAuditBtn.removeEventListener("click", auditHandler);

                    runAuditBtn.addEventListener("click", function () {
                        closeModal("auditLaptopModal");
                        location.reload();
                    });
                } else {
                    auditResultBox.style.color = "#721c24";
                    auditResultBox.style.backgroundColor = "#f8d7da";
                    auditResultBox.innerHTML = `❌ Audit failed: ${data.message}`;
                }
            })
            .catch(err => {
                auditResultBox.style.color = "#721c24";
                auditResultBox.style.backgroundColor = "#f8d7da";
                auditResultBox.innerHTML = `❌ Audit failed: ${err}`;
            });
        });
    }

    // Setup Export CSV
    const exportButton = document.getElementById("export-csv-btn");
    if (exportButton) {
        exportButton.addEventListener("click", function () {
            fetch('export_laptops.php')
                .then(response => response.blob())
                .then(blob => {
                    const now = new Date();
                    const filename = `laptops_export_${now.getFullYear()}-${now.getMonth() + 1}-${now.getDate()}_${now.getHours()}-${now.getMinutes()}-${now.getSeconds()}.csv`;
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                })
                .catch(error => {
                    alert('Error exporting CSV.');
                    console.error(error);
                });
        });
    }

    // Global Click to close modals when clicking on overlay
    document.addEventListener("click", function (e) {
        if (e.target.classList.contains("modal-overlay")) {
            closeAllModals();
        }
    });
});

// =====================
// Table Inline Editing + Save
// =====================
document.addEventListener("DOMContentLoaded", function () {
    const editBtn = document.getElementById("edit-mode-btn");
    const cancelEditBtn = document.getElementById("cancel-edit-btn");
    const deleteBtn = document.getElementById("delete-selected-btn");
    const undoBtn = document.getElementById("undo-delete-btn");
    let originalTableHTML = null;
    let editing = false;
    let pendingEdits = {};

    const selectOptions = {
        status: ['active', 'shelf-cc', 'shelf-md', 'shelf-hx', 'pending return', 'lost', 'decommissioned'],
        internet_policy: ['default', 'office', 'admin', 'accounting', 'estimating', 'executive', 'hr'],
        assigned_to: window.employeeOptions || [] // This is populated from PHP
    };

    editBtn.addEventListener("click", () => {
        editing = !editing;
        document.body.classList.toggle("editing-mode", editing);

        if (editing) {
            originalTableHTML = document.querySelector(".device-table tbody").innerHTML;
            editBtn.textContent = "Save Table";
            cancelEditBtn.style.display = "inline-block";
            if (deleteBtn) deleteBtn.style.display = "inline-block";
            if (undoBtn) undoBtn.style.display = "inline-block";
        } else {
            editBtn.textContent = "Edit Table";
            cancelEditBtn.style.display = "none";
            if (deleteBtn) deleteBtn.style.display = "none";
            if (undoBtn) undoBtn.style.display = "none";

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
            if (undoBtn) undoBtn.style.display = "none";
        });
    }

    // Double click for inline edit
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

                if (column.toLowerCase() === "assigned_to") {
                    selectOptions.assigned_to.forEach(opt => {
                        const option = document.createElement("option");
                        option.value = opt.id;
                        option.textContent = opt.name;
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
});


// =====================
// Double-click Row to Open Asset Log Modal
// =====================
document.addEventListener("DOMContentLoaded", function () {
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

// Fetch logs for device
function fetchDeviceLog(assetTag) {
    if (!assetTag || typeof assetTag !== 'string' || assetTag.trim() === '') {
        console.error("fetchDeviceLog called with invalid asset tag:", assetTag);
        return;
    }

    fetch(`/Forms/Assets/fetch_event_log.php?asset_tag=${encodeURIComponent(assetTag.trim())}`)
        .then(res => res.json())
        .then(data => {
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

// =====================
// Table Filtering by Input Fields
// =====================
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("device-table");
    if (!table) return;

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

// =====================
// Better Audit and Import Feedback (Green Messages)
// =====================
function showResultBox(resultBox, type, message) {
    resultBox.style.display = "block";
    resultBox.style.padding = "12px";
    if (type === "success") {
        resultBox.style.backgroundColor = "#d4edda";
        resultBox.style.color = "#155724";
        resultBox.style.border = "1px solid #c3e6cb";
    } else {
        resultBox.style.backgroundColor = "#f8d7da";
        resultBox.style.color = "#721c24";
        resultBox.style.border = "1px solid #f5c6cb";
    }
    resultBox.innerHTML = message;
}

// Populate Table Submit
document.addEventListener("DOMContentLoaded", function () {
    const importForm = document.getElementById("importLaptopForm");
    const importResult = document.getElementById("import-result-message");

    if (importForm && importResult) {
        importForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(importForm);

            fetch("import_laptops.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    showResultBox(importResult, "success", `✅ ${data.message}`);
                    const refreshBtn = document.createElement("button");
                    refreshBtn.textContent = "Refresh Page Now";
                    refreshBtn.className = "success-refresh-btn";
                    refreshBtn.onclick = () => {
                        closeModal("importLaptopModal");
                        location.reload();
                    };
                    importResult.appendChild(document.createElement("br"));
                    importResult.appendChild(refreshBtn);
                })
                .catch(err => {
                    showResultBox(importResult, "error", `❌ Import failed: ${err}`);
                });
        });
    }
});

// Audit Laptop Submit
document.addEventListener("DOMContentLoaded", function () {
    const runAuditBtn = document.getElementById("runAuditBtn");
    const auditFileInput = document.getElementById("auditCsvFile");
    const auditResultBox = document.getElementById("audit-result-message");

    if (runAuditBtn && auditFileInput && auditResultBox) {
        runAuditBtn.addEventListener("click", function (e) {
            e.preventDefault();
            const file = auditFileInput.files[0];
            if (!file) return alert("Please select a CSV file to audit.");

            auditResultBox.style.display = "block";
            auditResultBox.style.backgroundColor = "#fff3cd";
            auditResultBox.style.color = "#856404";
            auditResultBox.style.border = "1px solid #ffeeba";
            auditResultBox.style.padding = "12px";
            auditResultBox.innerHTML = "⏳ Auditing employee assignments...";

            const formData = new FormData();
            formData.append("csv_file", file);

            fetch("/Forms/Assets/audit_table.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showResultBox(auditResultBox, "success", `✅ Audit complete: ${data.count} employee(s) processed.`);

                        const refreshBtn = document.createElement("button");
                        refreshBtn.textContent = "Refresh Page Now";
                        refreshBtn.className = "success-refresh-btn";
                        refreshBtn.onclick = () => {
                            closeModal("auditLaptopModal");
                            location.reload();
                        };
                        auditResultBox.appendChild(document.createElement("br"));
                        auditResultBox.appendChild(refreshBtn);
                    } else {
                        showResultBox(auditResultBox, "error", `❌ Audit failed: ${data.message}`);
                    }
                })
                .catch(err => {
                    showResultBox(auditResultBox, "error", `❌ Audit failed: ${err}`);
                });
        });
    }
});

// =====================
// Inline Editing Setup for Specific Columns (Dropdowns or Inputs)
// =====================
document.addEventListener("DOMContentLoaded", function () {
    const selectOptions = {
        status: ['active', 'shelf-cc', 'shelf-md', 'shelf-hx', 'pending return', 'lost', 'decommissioned'],
        internet_policy: ['default', 'office', 'admin', 'accounting', 'estimating', 'executive', 'hr'],
        assigned_to: window.employeeOptions || [] // employeeOptions injected from PHP if available
    };

    let pendingEdits = {};

    function makeCellEditable(cell) {
        if (!document.body.classList.contains("editing-mode")) return;
        if (cell.querySelector("input, select")) return;

        const column = cell.getAttribute("data-column");
        const deviceId = cell.getAttribute("data-id");
        const currentText = cell.textContent.trim();

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

            cell.textContent = "";
            cell.appendChild(select);
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
                if (e.key === "Escape") cell.textContent = currentText;
            });

            cell.textContent = "";
            cell.appendChild(input);
            input.focus();
        }
    }

    document.querySelectorAll(".device-table td").forEach(cell => {
        cell.addEventListener("dblclick", function (e) {
            makeCellEditable(cell);
        });
    });

    // Save Table Logic
    const editBtn = document.getElementById("edit-mode-btn");
    const cancelEditBtn = document.getElementById("cancel-edit-btn");
    const deleteBtn = document.getElementById("delete-selected-btn");
    const undoBtn = document.getElementById("undo-delete-btn");
    let originalTableHTML = null;
    let editing = false;

    editBtn?.addEventListener("click", () => {
        editing = !editing;
        document.body.classList.toggle("editing-mode", editing);

        if (editing) {
            originalTableHTML = document.querySelector(".device-table tbody").innerHTML;
            editBtn.textContent = "Save Table";
            cancelEditBtn.style.display = "inline-block";
            if (deleteBtn) deleteBtn.style.display = "inline-block";
            if (undoBtn) undoBtn.style.display = "inline-block";
        } else {
            editBtn.textContent = "Edit Table";
            cancelEditBtn.style.display = "none";
            if (deleteBtn) deleteBtn.style.display = "none";
            if (undoBtn) undoBtn.style.display = "none";

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

    cancelEditBtn?.addEventListener("click", () => {
        if (originalTableHTML) {
            document.querySelector(".device-table tbody").innerHTML = originalTableHTML;
        }
        editing = false;
        document.body.classList.remove("editing-mode");
        editBtn.textContent = "Edit Table";
        cancelEditBtn.style.display = "none";
        if (deleteBtn) deleteBtn.style.display = "none";
        if (undoBtn) undoBtn.style.display = "none";
        pendingEdits = {};

        // Rebind editable cells
        document.querySelectorAll(".device-table td").forEach(cell => {
            cell.addEventListener("dblclick", function () {
                makeCellEditable(cell);
            });
        });
    });
});
