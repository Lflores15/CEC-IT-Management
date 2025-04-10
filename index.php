<?php
require_once __DIR__ . "/includes/session.php";

// If the user is not logged in, redirect to login page
if (!isset($_SESSION["username"])) {
    header("Location: Forms/Login/login.php"); 
    exit();
}

// Redirect to the dashboard instead of displaying links
header("Location: Forms/Assets/dashboard.php");
exit();
?>