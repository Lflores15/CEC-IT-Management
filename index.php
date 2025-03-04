<?php
require_once __DIR__ . "/includes/session.php";

if (!isset($_SESSION["username"])) {
    header("Location: Forms/Login/login.php"); // No leading "/"
    exit();
}

echo "Welcome, " . htmlspecialchars($_SESSION["username"]) . "!<br>";

if ($_SESSION["role"] === "admin") {
    echo "<a href='Forms/Users/manage.php'>Manage Users</a><br>"; 
}

echo "<a href='Forms/Users/user_dashboard.php'>Dashboard</a><br>";
echo "<a href='Forms/inventory.php'>Manage Inventory</a><br>";
echo "<a href='Forms/logout.php'>Logout</a>";
?>