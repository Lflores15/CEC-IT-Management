<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// enforce login
if (!isset($_SESSION["user_id"])) {
    header("Location: ../Login/login.php");
    exit();
}

// Determine page for titles and active classes
$pageTitle = "CEC-IT";
$currentPage = basename($_SERVER['PHP_SELF'], ".php");

$pageTitles = [
    "dashboard"           => "Dashboard",
    "laptop_Dashboard"    => "Laptops",
    "asset_Dashboard"     => "Assets Dashboard",
    "user_Dashboard"      => "Users",
    "employee_Dashboard"  => "Employees",
    "log_dashboard"       => "Logs",
    "settings"            => "Settings",
];
if (isset($pageTitles[$currentPage])) {
    $pageTitle = $pageTitles[$currentPage];
}

// Determine if on an Assets-related page
$assetPages = ["asset_Dashboard", "laptop_Dashboard"];
$isAssetPage = in_array($currentPage, $assetPages);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | CEC-IT</title>
    <link rel="stylesheet" href="/Assets/styles.css">
</head>
<body>

    <!-- Top Navbar -->
    <div class="top-navbar">
        <img src="/Assets/CEC-logo.png" alt="CEC-IT Logo" class="logo">

        <div class="navbar-right">
            <input type="text" placeholder="Search assets...">
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
        <h2><?php echo $pageTitle; ?></h2>

        <a href="../Assets/dashboard.php" class="<?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>">Dashboard</a>

        <!-- Assets Dropdown -->
        <div class="dropdown">
            <button class="dropdown-btn <?php echo in_array($currentPage, ['asset_Dashboard', 'laptop_Dashboard']) ? 'active' : ''; ?>">Assets</button>
            <div class="dropdown-content">
                <a href="../Assets/dashboard.php" class="<?php echo ($currentPage == 'asset_Dashboard') ? 'active' : ''; ?>">Dashboard</a>
                <a href="../Assets/laptop_Dashboard.php" class="<?php echo ($currentPage == 'laptop_Dashboard') ? 'active' : ''; ?>">Laptops</a>
            </div>
        </div>

        <a href="../Users/user_Dashboard.php" class="<?php echo ($currentPage == 'user_Dashboard') ? 'active' : ''; ?>">Users</a>
        <a href="../Employees/employee_Dashboard.php" class="<?php echo ($currentPage == 'employee_Dashboard') ? 'active' : ''; ?>">Employees</a>
        <a href="../Admin/log_dashboard.php" class="<?php echo ($currentPage == 'log_dashboard') ? 'active' : ''; ?>">Logs</a>
        <a href="../Settings/settings.php" class="<?php echo ($currentPage == 'settings') ? 'active' : ''; ?>">Settings</a>
    </div>

    <!-- Main Content Wrapper -->
    <div class="<?php echo $isAssetPage ? 'asset-content' : 'main-content'; ?>">

    <!-- Script Link -->
    <script src="/Assets/script.js?v=<?php echo time(); ?>"></script>
