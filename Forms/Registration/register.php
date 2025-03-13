<?php
require_once '../../PHP/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // ✅ Check if passwords match
    if ($password !== $confirm_password) {
        die("❌ Passwords do not match.");
    }

    // ✅ Check if username or email already exists
    $stmt = $conn->prepare("SELECT user_id FROM Users WHERE username = ? OR email = ?");
    if (!$stmt) {
        die("❌ SQL Error: " . $conn->error);
    }

    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        die("❌ User already exists. Try a different username or email.");
    }
    $stmt->close();

    // ✅ Hash the password securely
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    // ✅ Insert user into database
    $stmt = $conn->prepare("INSERT INTO Users (username, email, password_hash, role) VALUES (?, ?, ?, ?, 'user')");
    if (!$stmt) {
        die("❌ SQL Error on INSERT: " . $conn->error);
    }

    $stmt->bind_param("ssss", $username, $email, $hashedPassword);

    if ($stmt->execute()) {
        echo "✅ Registration successful! You can now <a href='../Login/login.php'>login here</a>.";
    } else {
        die("❌ Error executing query: " . $stmt->error);
    }

    $stmt->close();
}
?>

<!-- ✅ Secure Registration Form -->
<form action="register.php" method="post">
    <h2>Register</h2>
    <label>Username</label> <input type="text" name="username" required><br>
    <label>Email</label> <input type="email" name="email" required><br>
    <label>Password</label> <input type="password" name="password" required><br>
    <label>Confirm Password</label> <input type="password" name="confirm_password" required><br>
    <button type="submit">Register</button>
</form>