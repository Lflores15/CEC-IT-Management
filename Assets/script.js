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
// DOMContentLoaded Event
// =====================
document.addEventListener("DOMContentLoaded", function () {
    // Import Laptop Modal
    const openImportBtn = document.getElementById("openImportLaptopModal");
    const closeImportBtn = document.getElementById("closeImportLaptopModal");
    const importForm = document.getElementById("importLaptopForm");
    const importResult = document.getElementById("import-result-message");

    if (openImportBtn) {
        openImportBtn.addEventListener("click", function (e) {
            e.preventDefault();
            openModal("importLaptopModal");
            importResult.style.display = "none"; // ✅ clear previous result
            importResult.innerHTML = "";
        });
        
    }

    if (closeImportBtn) {
        closeImportBtn.addEventListener("click", function () {
            const success = importResult?.innerHTML.includes("Imported/updated");
            closeModal("importLaptopModal");
            if (success) location.reload(); // ✅ refresh if import success
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
                importResult.innerHTML = data.message;

                // Refresh button
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

    // Audit Modal
    const openAuditBtn = document.getElementById("audit-laptop-btn");
    const closeAuditBtn = document.getElementById("closeAuditLaptopModal");
    const runAuditBtn = document.getElementById("runAuditBtn");
    const auditFileInput = document.getElementById("auditCsvFile");
    const auditResultBox = document.getElementById("audit-result-message");

    if (openAuditBtn) {
        openAuditBtn.addEventListener("click", function (e) {
            e.preventDefault();
            openModal("auditLaptopModal");
            auditResultBox.style.display = "none"; // ✅ clear previous result
            auditResultBox.innerHTML = "";
        });
    }

    if (closeAuditBtn) {
        closeAuditBtn.addEventListener("click", function () {
            const success = auditResultBox?.innerHTML.includes("Audit complete");
            closeModal("auditLaptopModal");
            if (success) location.reload(); // ✅ refresh if audit success was shown
        });
    } // ✅ THIS was missing and is required
    
        

    if (runAuditBtn && auditFileInput && auditResultBox) {
        runAuditBtn.addEventListener("click", function auditHandler(e) {
            e.preventDefault();
            const file = auditFileInput.files[0];
            if (!file) return alert("Please select a CSV file to audit.");

            auditResultBox.style.display = "block";
            auditResultBox.style.color = "#333";
            auditResultBox.style.backgroundColor = "#fff3cd";
            auditResultBox.style.padding = "12px"; // ✅ add this if missing

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
                    auditResultBox.innerHTML = `Audit complete: ${data.count} employee(s) processed.`;

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

    // Export CSV
    const exportBtn = document.getElementById("export-csv-btn");
    if (exportBtn) {
        exportBtn.addEventListener("click", function () {
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

    // Global: Close modal when clicking outside
    document.addEventListener("click", function (e) {
        if (e.target.classList.contains("modal-overlay")) {
            closeAllModals();
        }
    });
});
