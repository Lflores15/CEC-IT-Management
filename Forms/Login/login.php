<?php
session_start();
require_once '../../PHP/config.php';
require_once '../../includes/log_event.php';

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT user_id, password_hash, role FROM Users WHERE username = ?");
    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashed_password, $role);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION["user_id"] = $user_id;
            $_SESSION["username"] = $username;
            $_SESSION["role"] = $role;

            logUserEvent("LOGIN_SUCCESS", "User '$username' successfully logged in", $username);
            header("Location: ..../Assets/dashboard.php");
            exit();
        }
    }

    logUserEvent("LOGIN_FAIL", "Invalid login attempt for username '$username'");
    $error_message = "❌ Invalid username or password.";
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Login</title>
    <link rel="stylesheet" href="../..../Assets/styles.css"> 
</head>
<body>
    <div class="login-modal modal">
        <form class="login-form" method="POST" action="login.php">
            <h2>Login</h2>

            <div class="form-group">
                <label for="username">Username</label>
                <input name="username" id="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>