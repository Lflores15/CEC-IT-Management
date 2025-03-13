document.addEventListener("DOMContentLoaded", function () {
    const profileBtn = document.querySelector(".profile-btn");
    const profileDropdown = document.querySelector(".profile-dropdown");

    if (profileBtn) {
        profileBtn.addEventListener("click", function (event) {
            event.stopPropagation(); // Prevents immediate closing
            profileDropdown.classList.toggle("active");
        });
    }

    // Close dropdown when clicking outside
    document.addEventListener("click", function (event) {
        if (!profileDropdown.contains(event.target)) {
            profileDropdown.classList.remove("active");
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const filterName = document.getElementById("filter-name");
    const filterTag = document.getElementById("filter-tag");
    const filterCategory = document.getElementById("filter-category");
    const filterStatus = document.getElementById("filter-status");
    const tableRows = document.querySelectorAll("#device-table tbody tr");

    function filterTable() {
        const nameValue = filterName.value.toLowerCase();
        const tagValue = filterTag.value.toLowerCase();
        const categoryValue = filterCategory.value.toLowerCase();
        const statusValue = filterStatus.value.toLowerCase();

        tableRows.forEach(row => {
            const name = row.cells[0].textContent.toLowerCase();
            const tag = row.cells[1].textContent.toLowerCase();
            const category = row.cells[2].textContent.toLowerCase();
            const status = row.cells[3].textContent.toLowerCase();

            const nameMatch = name.includes(nameValue);
            const tagMatch = tag.includes(tagValue);
            const categoryMatch = categoryValue === "" || category.includes(categoryValue);
            const statusMatch = statusValue === "" || status.includes(statusValue);

            if (nameMatch && tagMatch && categoryMatch && statusMatch) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    filterName.addEventListener("input", filterTable);
    filterTag.addEventListener("input", filterTable);
    filterCategory.addEventListener("change", filterTable);
    filterStatus.addEventListener("change", filterTable);
});