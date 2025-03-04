<?php
session_start();
require_once '../../PHP/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    // ✅ Fetch user details securely
    $stmt = $conn->prepare("SELECT user_id, password_hash, role FROM Users WHERE username = ?");
    if (!$stmt) {
        die("❌ SQL Error: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashed_password, $role);
        $stmt->fetch();

        // ✅ Verify hashed password using `password_verify()`
        if (password_verify($password, $hashed_password)) {
            $_SESSION["user_id"] = $user_id;
            $_SESSION["username"] = $username;
            $_SESSION["role"] = $role;

            // ✅ Redirect based on user role
            if ($role === 'admin') {
                header("Location: ../Admin/admin_dashboard.php");
            } else {
                header("Location: ../Users/user_dashboard.php");
            }
            exit();
        } else {
            echo "❌ Incorrect password!";
        }
    } else {
        echo "❌ User not found!";
    }

    $stmt->close();
}
?>

<!-- ✅ Secure Login Form -->
<form action="login.php" method="POST">
    <h2>Login</h2>
    <label>Username:</label> <input type="text" name="username" required><br>
    <label>Password:</label> <input type="password" name="password" required><br>
    <button type="submit">Login</button>
</form>