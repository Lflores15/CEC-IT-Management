<?php
session_start();
require_once '../../PHP/config.php';
require_once '../../includes/log_event.php';

$error_message = '';
$username      = '';
$password      = '';
$confirm_pw    = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Grab & trim inputs
    $username   = trim($_POST['username']   ?? '');
    $password   = $_POST['password']        ?? '';
    $confirm_pw = $_POST['confirm_password']?? '';

    // Basic validation
    if ($username === '' || $password === '' || $confirm_pw === '') {
        $error_message = '❌ All fields are required.';
    } elseif ($password !== $confirm_pw) {
        $error_message = '❌ Passwords do not match.';
    } else {
        // Check username uniqueness
        $stmt = $conn->prepare("SELECT user_id FROM Users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error_message = '❌ That username is already taken.';
        } else {
            // Insert new user
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'Technician';

            $stmt = $conn->prepare("
                INSERT INTO Users (username, password_hash, role)
                VALUES (?, ?, ?)
            ");
            if (!$stmt) {
                die("SQL Error: " . $conn->error);
            }
            $stmt->bind_param("sss", $username, $hash, $role);

            if ($stmt->execute()) {
                logUserEvent(
                    "USER_REGISTER",
                    "New user \"$username\" registered as Technician",
                    $username
                );
                // Redirect to login
                header("Location: ../Login/login.php?registered=1");
                exit();
            } else {
                $error_message = '❌ Registration failed: ' . $stmt->error;
            }
        }
        $stmt->close();
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
  <div class="register-container">
    <h1>Create Account</h1>

    <?php if ($error_message): ?>
      <div class="error" style="color:red;"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label for="username">Username</label>
        <input
          type="text"
          id="username"
          name="username"
          value="<?= htmlspecialchars($username) ?>"
          required
        >
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input
          type="password"
          id="password"
          name="password"
          required
        >
      </div>

      <div class="form-group">
        <label for="confirm_password">Confirm Password</label>
        <input
          type="password"
          id="confirm_password"
          name="confirm_password"
          required
        >
      </div>

      <button type="submit">Register</button>
    </form>
  </div>
</body>
</html>
