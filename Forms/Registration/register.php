<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../PHP/config.php';

$registrationMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($login) || empty($password) || empty($confirmPassword)) {
        $registrationMessage = "❌ All fields are required.";
    } elseif ($password !== $confirmPassword) {
        $registrationMessage = "❌ Passwords do not match.";
    } else {
        // Check if the username already exists
        $stmt = $conn->prepare("SELECT user_id FROM Users WHERE login = ?");
        if ($stmt) {
            $stmt->bind_param("s", $login);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $registrationMessage = "❌ Username already taken.";
            } else {
                // Username is available; create user
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $role = 'Technician'; // Default role

                $insertStmt = $conn->prepare("INSERT INTO Users (login, password_hash, role) VALUES (?, ?, ?)");
                if ($insertStmt) {
                    $insertStmt->bind_param("sss", $login, $hashedPassword, $role);
                    if ($insertStmt->execute()) {
                        $registrationMessage = "✅ Registration successful! Click 'Back to Login' to sign in.";

                    } else {
                        $registrationMessage = "❌ Registration failed: " . htmlspecialchars($insertStmt->error);
                    }
                    $insertStmt->close();
                } else {
                    $registrationMessage = "❌ Database error during insert: " . htmlspecialchars($conn->error);
                }
            }
            $stmt->close();
        } else {
            $registrationMessage = "❌ Database error during username check: " . htmlspecialchars($conn->error);
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="../../Assets/styles.css">
</head>
<body>
    <div class="register-modal">
        <form class="register-form" method="POST" action="register.php">
            <h2>Register</h2>

            <?php if (!empty($registrationMessage)): ?>
                <div class="registration-success"><?= $registrationMessage ?></div>
            <?php endif; ?>


            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" required>
            </div>

            <button type="submit" class="login-btn">Register</button>

            <div class="register-link">
                <a href="../Login/login.php" class="register-btn">Back to Login</a>
            </div>
        </form>
    </div>
</body>
</html>
