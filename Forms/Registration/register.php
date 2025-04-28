<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../PHP/config.php';

$registrationMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $registrationMessage = "❌ Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT user_id FROM Users WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $registrationMessage = "❌ Username already taken.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $role = 'user';

                $insertStmt = $conn->prepare("INSERT INTO Users (username, password_hash, role) VALUES (?, ?, ?)");
                if ($insertStmt) {
                    $insertStmt->bind_param("sss", $username, $hashedPassword, $role);
                    if ($insertStmt->execute()) {
                        $registrationMessage = "✅ Registration successful! <a href='../Login/login.php'>Login here</a>.";
                    } else {
                        $registrationMessage = "❌ Registration failed: " . htmlspecialchars($insertStmt->error);
                    }
                    $insertStmt->close();
                } else {
                    $registrationMessage = "❌ Database error (Insert): " . htmlspecialchars($conn->error);
                }
            }
            $stmt->close();
        } else {
            $registrationMessage = "❌ Database error (Check): " . htmlspecialchars($conn->error);
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
                <div class="message"><?= $registrationMessage ?></div>
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
