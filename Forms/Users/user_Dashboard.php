<?php
session_start();
require_once "../../PHP/config.php";
require_once "../../includes/navbar.php";

// Redirect if not admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== 'admin') {
    header("Location: ../Login/login.php");
    exit();
}

// Fetch all users
$query = "SELECT user_id, username, email, role FROM Users";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../../Assets/styles.css">
</head>
<body>
    <div class="asset-content">
        <h2>Manage Users</h2>
        <table class="user-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user["username"]); ?></td>
                        <td><?php echo htmlspecialchars($user["email"]); ?></td>
                        <td><?php echo htmlspecialchars($user["role"]); ?></td>
                        <td>
                            <button class="edit-btn" data-id="<?php echo $user['user_id']; ?>"
                                data-username="<?php echo $user['username']; ?>"
                                data-email="<?php echo $user['email']; ?>"
                                data-role="<?php echo $user['role']; ?>">Edit</button>

                            <button class="delete-btn" data-id="<?php echo $user['user_id']; ?>"
                                data-username="<?php echo $user['username']; ?>"
                                data-email="<?php echo $user['email']; ?>"
                                data-role="<?php echo $user['role']; ?>">Delete</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Edit User</h3>
            <form id="editUserForm">
                <input type="hidden" id="edit-user-id" name="user_id">
                <label for="edit-username">Username:</label>
                <input type="text" id="edit-username" name="username" required>

                <label for="edit-email">Email:</label>
                <input type="email" id="edit-email" name="email" required>

                <label for="edit-role">Role:</label>
                <select id="edit-role" name="role">
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>

                <button type="submit">Update</button>
            </form>
        </div>
    </div>

</body>
</html>