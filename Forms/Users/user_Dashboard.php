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
        window.location.href = window.location.pathname; 
    </script>
<?php elseif (isset($_GET['error']) && $_GET['error'] === 'duplicate'): ?>
    <script>
        alert("❌ Username or email already exists.");
        window.location.href = window.location.pathname; 
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
    <div id="createModal" class="modal laptop-modal-content-wrapper">
        <div class="laptop-modal-content" style="max-width: 600px; padding: 25px 30px;">
            <div class="modal-header">
                <h5 style="font-size: 1.5rem;">Create New User</h5>
                <span class="close" id="closeCreateModal">&times;</span>
            </div>
            <form action="create_user.php" method="POST">
                <label for="new-username" style="font-weight: 600; display: block; margin: 10px 0 5px; text-align: left;">Username:</label>
                <input type="text" id="new-username" name="login" required style="padding: 12px 14px; font-size: 1.05rem; border-radius: 6px; border: 1px solid #ccc; width: 100%; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);">

                <label for="new-password" style="font-weight: 600; display: block; margin: 15px 0 5px; text-align: left;">Password:</label>
                <input type="password" id="new-password" name="password" required style="padding: 12px 14px; font-size: 1.05rem; border-radius: 6px; border: 1px solid #ccc; width: 100%; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);">

                <label for="new-role" style="font-weight: 600; display: block; margin: 15px 0 5px; text-align: left;">Role:</label>
                <select id="new-role" name="role" style="padding: 12px 14px; font-size: 1.05rem; border-radius: 6px; border: 1px solid #ccc; width: 100%; background-color: #fff; appearance: none; background-image: url('data:image/svg+xml;charset=UTF-8,%3Csvg fill=\'%23333\' height=\'24\' viewBox=\'0 0 24 24\' width=\'24\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath d=\'M7 10l5 5 5-5z\'/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 10px center; background-size: 16px 16px; cursor: pointer;">
                    <option value="Manager">Manager</option>
                    <option value="Technician">Technician</option>
                </select>

                <button type="submit" style="margin-top: 20px; padding: 10px 20px; font-size: 1rem; background-color: #007bff; color: white; border: none; border-radius: 6px; font-weight: bold;">Create User</button>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="modal laptop-modal-content-wrapper">
        <div class="laptop-modal-content" style="max-width: 600px; padding: 25px 30px;">
            <div class="modal-header">
                <h5 style="font-size: 1.5rem;">Edit User</h5>
                <span class="close" id="closeEditModal">&times;</span>
            </div>
            <form id="editUserForm">
                <input type="hidden" id="edit-user-id" name="user_id">
                <label for="edit-username" style="font-weight: 600; display: block; margin: 10px 0 5px; text-align: left;">Username:</label>
                <input type="text" id="edit-username" name="login" required style="padding: 12px 14px; font-size: 1.05rem; border-radius: 6px; border: 1px solid #ccc; width: 100%; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);">

                <label for="edit-role" style="font-weight: 600; display: block; margin: 15px 0 5px; text-align: left;">Role:</label>
                <select id="edit-role" name="role" style="padding: 12px 14px; font-size: 1.05rem; border-radius: 6px; border: 1px solid #ccc; width: 100%; background-color: #fff; appearance: none; background-image: url('data:image/svg+xml;charset=UTF-8,%3Csvg fill=\'%23333\' height=\'24\' viewBox=\'0 0 24 24\' width=\'24\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath d=\'M7 10l5 5 5-5z\'/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 10px center; background-size: 16px 16px; cursor: pointer;">
                    <option value="Manager">Manager</option>
                    <option value="Technician">Technician</option>
                </select>

                <button type="submit" style="margin-top: 20px; padding: 10px 20px; font-size: 1rem; background-color: #007bff; color: white; border: none; border-radius: 6px; font-weight: bold;">Update</button>
                <p id="edit-user-message" style="margin-top:10px; text-align:center;"></p>
            </form>
        </div>
    </div>

    <!-- Delete User Modal -->
    <div id="deleteModal" class="modal laptop-modal-content-wrapper">
        <div class="laptop-modal-content" style="max-width: 600px; padding: 25px 30px 20px;">
            <div class="modal-header">
                <h5 style="font-size: 1.6rem;">Confirm User Deletion</h5>
                <span class="close" id="closeDeleteModal">&times;</span>
            </div>
            <form id="deleteUserForm" method="GET" action="delete_user.php">
                <input type="hidden" name="id" id="delete-user-id">
                <p style="text-align:center; font-size: 1.2rem; margin: 20px 0;">Are you sure you want to delete <strong id="delete-username"></strong>?</p>
                <div style="display:flex; justify-content:center; gap:16px; margin-top:0; margin-bottom:0;">
                    <button type="submit" style="padding: 10px 20px; font-size: 1rem; border: none; border-radius: 6px; background-color: red; color: white;">Delete</button>
                    <button type="button" id="cancelDeleteBtn" style="padding: 10px 20px; font-size: 1rem; border: none; border-radius: 6px;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>