<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"])) {
    header("Location: ../Login/login.php");
    exit();
}

require_once "../../PHP/config.php";
require_once "../../includes/navbar.php";

$user_id = $_SESSION["user_id"];
$result = $conn->query("SELECT login, role FROM Users WHERE user_id = " . intval($user_id));
$user = $result->fetch_assoc();
?>

<div class="dashboard-container" style="max-width: 600px; margin: 250px auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border: 1px solid #ddd;">
    <h2 style="margin-bottom: 25px; font-size: 1.5rem; text-align: center;">My Profile</h2>
    <form method="POST" action="update_profile.php">
        <label for="username" style="font-weight: 600;">Username:</label>
        <input
            type="text"
            name="username"
            id="username"
            value="<?= htmlspecialchars($user['login']) ?>"
            required
            style="width: 100%; padding: 12px 14px; font-size: 1.05rem; border-radius: 6px; border: 1px solid #ccc; margin-bottom: 20px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);"
        >

        <label for="password" style="font-weight: 600;">New Password:</label>
        <input
            type="password"
            name="password"
            id="password"
            placeholder="Leave blank to keep current password"
            style="width: 100%; padding: 12px 14px; font-size: 1.05rem; border-radius: 6px; border: 1px solid #ccc; margin-bottom: 20px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);"
        >

        <label for="role" style="font-weight: 600;">Role:</label>
        <select
            name="role"
            id="role"
            style="width: 100%; padding: 12px 14px; font-size: 1.05rem; border-radius: 6px; border: 1px solid #ccc; margin-bottom: 20px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);"
            disabled
        >
            <option value="Manager" <?= $user['role'] === 'Manager' ? 'selected' : '' ?>>Manager</option>
            <option value="Technician" <?= $user['role'] === 'Technician' ? 'selected' : '' ?>>Technician</option>
        </select>

        <button type="submit" style="width: 100%; padding: 12px 14px; font-size: 1.05rem; background-color: #007bff; color: white; font-weight: bold; border: none; border-radius: 6px; cursor: pointer;">Update Profile</button>
    </form>
</div>

