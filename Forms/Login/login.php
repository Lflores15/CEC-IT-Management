<?php
session_start();
require_once '../../PHP/config.php';
require_once '../../includes/log_event.php';

$error_message   = '';
$submitted_user  = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $submitted_user = trim($_POST["username"]   ?? '');
    $password       = $_POST["password"]         ?? '';

    // 1) Lookup only the columns that exist in Users
    $stmt = $conn->prepare(
        "SELECT user_id, password_hash, role
           FROM Users
          WHERE username = ?"
    ) or die("Prepare failed: (" . $conn->errno . ") " . $conn->error);

    $stmt->bind_param("s", $submitted_user);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        // 2) Bind only 3 results (no emp_id)
        $stmt->bind_result($user_id, $hashed_password, $role);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // 3) Success → populate session
            $_SESSION["loggedin"]  = true;
            $_SESSION["user_id"]   = $user_id;
            $_SESSION["username"]  = $submitted_user;
            $_SESSION["role"]      = $role;

            logUserEvent(
                "LOGIN_SUCCESS",
                "User '{$submitted_user}' logged in",
                $submitted_user
            );

            header("Location: ../Assets/dashboard.php");
            exit();
        }
    }

    // 4) Failure path
    logUserEvent(
        "LOGIN_FAIL",
        "Invalid login for '{$submitted_user}'",
        $submitted_user ?: 'UNKNOWN'
    );
    $error_message = "❌ Invalid username or password.";
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="../../Assets/styles.css">
</head>
<body>
  <div class="login-container">
    <h1>Login</h1>

    <?php if ($error_message): ?>
      <div class="error" style="color:red;"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <form method="post">
      <label for="username">Username
        <input
          type="text"
          name="username"
          id="username"
          value="<?= htmlspecialchars($submitted_user) ?>"
          required
        >
      </label>

      <label for="password">Password
        <input type="password" name="password" id="password" required>
      </label>

      <button type="submit">Login</button>
    </form>
  </div>
</body>
</html>
