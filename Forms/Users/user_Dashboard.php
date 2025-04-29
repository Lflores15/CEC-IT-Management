<?php
session_start();
require_once "../../PHP/config.php";
require_once "../../includes/navbar.php";

// Redirect if not manager
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== 'Manager') {
    header("Location: ../Login/login.php");
    exit();
}

// Fetch all users
$query = "SELECT user_id, login, role FROM Users";
$result = $conn->query($query);
if (!$result) {
    die("Query failed: (" . $conn->errno . ") " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="/Assets/styles.css">
    <script src="../../Assets/scripts.js"></script>
</head>
<h1>Manage Users</h1>
<?php if (isset($_GET['created']) && $_GET['created'] == 1): ?>
    <script>
        alert("✅ User created successfully!");
        window.location.href = window.location.pathname; // removes ?created=1
    </script>
<?php elseif (isset($_GET['error']) && $_GET['error'] === 'duplicate'): ?>
    <script>
        alert("❌ Username or email already exists.");
        window.location.href = window.location.pathname; // removes ?error=duplicate
    </script>
<?php elseif (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
    <script>
        alert("🗑️ User deleted successfully.");
        window.location.href = window.location.pathname;
    </script>
<?php elseif (isset($_GET['error']) && $_GET['error'] === 'cannot_delete_self'): ?>
    <script>
        alert("⚠️ You cannot delete your own manager account.");
        window.location.href = window.location.pathname;
    </script>
<?php endif; ?>
<body>
    <div class="asset-content-user">
        <button class="create-user-btn" id="openCreateModal">+ Create User</button>
        <table class="user-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user["login"]); ?></td>
                        <td><?php echo htmlspecialchars($user["role"]); ?></td>
                        <td>
                            <button class="edit-btn" data-id="<?php echo $user['user_id']; ?>"
                                data-username="<?php echo $user['login']; ?>"
                                data-role="<?php echo $user['role']; ?>">Edit</button>

                            <button class="delete-btn" data-id="<?php echo $user['user_id']; ?>"
                                data-username="<?php echo $user['login']; ?>"
                                data-role="<?php echo $user['role']; ?>">Delete</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Create User Modal -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeCreateModal">&times;</span>
            <h3>Create New User</h3>
            <form action="create_user.php" method="POST">
                <label for="new-username">Username:</label>
                <input type="text" id="new-username" name="login" required>

                <label for="new-password">Password:</label>
                <input type="password" id="new-password" name="password" required>

                <label for="new-role">Role:</label>
                <select id="new-role" name="role">
                    <option value="Manager">Manager</option>
                    <option value="Technician">Technician</option>
                </select>

                <button type="submit">Create User</button>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeEditModal">&times;</span>
            <h3>Edit User</h3>
            <form id="editUserForm">
                <input type="hidden" id="edit-user-id" name="user_id">
                <label for="edit-username">Username:</label>
                <input type="text" id="edit-username" name="login" required>
                <label for="edit-role">Role:</label>
                <select id="edit-role" name="role">
                    <option value="Manager">Manager</option>
                    <option value="Technician">Technician</option>
                </select>

                <button type="submit">Update</button>
            </form>
        </div>
    </div>

    <!-- Delete User Modal -->
<div id="deleteModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" id="closeDeleteModal">&times;</span>
        <h3>Confirm Delete</h3>
        <p>Are you sure you want to delete <strong id="delete-username"></strong>?</p>
        <form id="deleteUserForm" method="GET" action="delete_user.php">
            <input type="hidden" name="id" id="delete-user-id">
            <button type="submit" style="background-color: red; color: white;">Delete</button>
            <button type="button" id="cancelDeleteBtn">Cancel</button>
        </form>
    </div>
</div>

</body>
</html>