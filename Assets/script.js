
document.addEventListener("DOMContentLoaded", function () {
    const editToggleBtn = document.getElementById("editToggleBtn");
    const editActions = document.getElementById("editActions");
    const rowCheckboxes = document.querySelectorAll(".row-checkbox");
    const deleteDropdown = document.getElementById("deleteSelectedDropdown");

    if (editToggleBtn) {
        editToggleBtn.addEventListener("click", function () {
            const editing = editActions.style.display === "block";
            editActions.style.display = editing ? "none" : "block";
            editToggleBtn.textContent = editing ? "Edit Table" : "Save Table";

            document.querySelectorAll(".checkbox-col").forEach(col => {
                col.style.display = editing ? "none" : "table-cell";
            });

            document.querySelectorAll(".edit-col").forEach(col => {
                col.style.display = editing ? "none" : "table-cell";
            });
        });
    }

    if (deleteDropdown) {
        deleteDropdown.addEventListener("change", function () {
            if (deleteDropdown.value === "delete") {
                const selected = Array.from(document.querySelectorAll(".row-checkbox:checked"))
                    .map(cb => cb.closest("tr"));

                if (selected.length === 0) {
                    alert("Please select at least one row.");
                    return;
                }

                if (confirm("Are you sure you want to delete the selected row(s)?")) {
                    const ids = selected.map(row => row.children[1].textContent.trim());
                    fetch("../../Forms/Assets/delete_employees.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify({ employee_ids: ids }),
                    })
                        .then(res => res.text())
                        .then(data => {
                            alert(data);
                            location.reload();
                        })
                        .catch(err => {
                            alert("Error deleting employees.");
                            console.error(err);
                        });
                }
            }
        });
    }

    document.querySelectorAll(".edit-btn").forEach(btn => {
        btn.addEventListener("click", function () {
            const row = btn.closest("tr");
            const id = row.children[1].textContent.trim();
            window.location.href = "../../Forms/Users/user_Edit.php?id=" + id;
        });
    });
});
