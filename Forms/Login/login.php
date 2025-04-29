<?php
session_start();
require_once '../../PHP/config.php';
require_once '../../includes/log_event.php';

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login = trim($_POST["login"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT user_id, password_hash, role FROM Users WHERE login = ?");
    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param("s", $login);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashed_password, $role);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION["user_id"] = $user_id;
            $_SESSION["login"] = $login;
            $_SESSION["role"] = $role;

            logUserEvent("LOGIN_SUCCESS", "User '$login' successfully logged in", $login);
            header("Location: ../Assets/dashboard.php");
            exit();
        }
    }

    logUserEvent("LOGIN_FAIL", "Invalid login attempt for login '$login'");
    $error_message = "❌ Invalid login or password.";
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Login</title>
    <link rel="stylesheet" href="../../Assets/styles.css"> 
</head>
<body>
    <div class="login-modal modal">
        <form class="login-form" method="POST" action="login.php">
            <h2>Login</h2>

            <div class="form-group">
                <label for="login">Username</label>
                <input name="login" id="login" required>
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