document.addEventListener("DOMContentLoaded", function () {
    console.log("JavaScript Loaded ✅");

    // Profile Dropdown
    const profileBtn = document.querySelector(".profile-btn");
    const profileDropdown = document.querySelector(".profile-dropdown");

    if (profileBtn) {
        profileBtn.addEventListener("click", function (event) {
            event.stopPropagation();
            profileDropdown.classList.toggle("active");
        });
    }

    document.addEventListener("click", function (event) {
        if (!profileDropdown.contains(event.target)) {
            profileDropdown.classList.remove("active");
        }
    });

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

    // Table Sorting
    const tables = document.querySelectorAll("table");

    tables.forEach((table) => {
        const headers = table.querySelectorAll("th.sortable");

        headers.forEach((header, index) => {
            header.addEventListener("click", function () {
                let tbody = table.querySelector("tbody");
                let rows = Array.from(tbody.rows);
                let isAscending = header.classList.contains("asc");

                rows.sort((rowA, rowB) => {
                    let cellA = rowA.cells[index].textContent.trim().toLowerCase();
                    let cellB = rowB.cells[index].textContent.trim().toLowerCase();

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

    // Table Filtering
    const filterName = document.getElementById("filter-name");
    const filterTag = document.getElementById("filter-tag");
    const filterCategory = document.getElementById("filter-category");
    const filterStatus = document.getElementById("filter-status");

    function filterTable() {
        tables.forEach(table => {
            const rows = table.querySelectorAll("tbody tr");

            rows.forEach(row => {
                const cells = row.cells;

                if (!cells.length) return;

                const name = cells[0].textContent.toLowerCase();
                const tag = cells[1].textContent.toLowerCase();
                const category = cells[2].textContent.toLowerCase();
                const status = cells[11].textContent.toLowerCase(); 

                const nameMatch = filterName.value === "" || name.includes(filterName.value.toLowerCase());
                const tagMatch = filterTag.value === "" || tag.includes(filterTag.value.toLowerCase());
                const categoryMatch = filterCategory.value === "" || category.includes(filterCategory.value.toLowerCase());
                const statusMatch = filterStatus.value === "" || status.includes(filterStatus.value.toLowerCase());

                row.style.display = nameMatch && tagMatch && categoryMatch && statusMatch ? "" : "none";
            });
        });
    }

    if (filterName) filterName.addEventListener("input", filterTable);
    if (filterTag) filterTag.addEventListener("input", filterTable);
    if (filterCategory) filterCategory.addEventListener("change", filterTable);
    if (filterStatus) filterStatus.addEventListener("change", filterTable);

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

            // ✅ Make rows clickable
            document.querySelectorAll(".clickable-row").forEach(row => {
                row.addEventListener("click", () => {
                    window.location.href = row.dataset.href;
                });
            });
        });



        // Sortable columns
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

// Edit Columns modal toggle
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