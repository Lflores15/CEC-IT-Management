<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../Login/login.php");
    exit();
}

// Get the current page name dynamically
$pageTitle = "CEC-IT"; // Default title
$currentPage = basename($_SERVER['PHP_SELF'], ".php"); // Extracts filename without extension

// Set custom titles for each page
$pageTitles = [
    "dashboard" => "Dashboard",
    "assets" => "Assets",
    "users" => "Users",
    "settings" => "Settings",
];

if (array_key_exists($currentPage, $pageTitles)) {
    $pageTitle = $pageTitles[$currentPage];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | CEC-IT</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>

    <!-- Top Navbar -->
<div class="top-navbar">
    <img src="../../Assets/CEC-Logo.png" alt="CEC-IT Logo" class="logo">

    <div class="navbar-right">
        <input type="text" placeholder="Search assets...">

        <!-- Profile Dropdown -->
        <div class="profile-dropdown">
            <button class="profile-btn">Profile ▼</button>
            <div class="profile-dropdown-content">
                <a href="../Users/profile.php">View Profile</a>
                <a href="../Settings/settings.php">Settings</a>
                <a href="../Login/logout.php">Logout</a>
            </div>
        </div>
    </div>
</div>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="../Dashboard/dashboard.php" class="<?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>">Dashboard</a>
        <a href="../Dashboard/asset_Dashboard.php" class="<?php echo ($currentPage == 'assets') ? 'active' : ''; ?>">Assets</a>
        <a href="../Users/users.php" class="<?php echo ($currentPage == 'users') ? 'active' : ''; ?>">Users</a>
    </div>

    <!-- Main Content Wrapper -->
    <div class="main-content">

    <!-- Script Link -->
    <script src="../../Assets/script.js"></script>

</body>
</html>