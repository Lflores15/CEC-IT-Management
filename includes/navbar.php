<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../Login/login.php");
    exit();
}

// Get the current page name dynamically
$pageTitle = "CEC-IT"; // Default title
$currentPage = basename($_SERVER['PHP_SELF'], ".php"); 

// Determine if we are on an asset-related page
$assetPages = ["asset_Dashboard", "laptop_Dashboard", "pc_Dashboard", "phone_Dashboard", "tablet_Dashboard"];
$isAssetPage = in_array($currentPage, $assetPages);

// Set custom titles for each page
$pageTitles = [
    "dashboard" => "Dashboard",
    "assets" => "Assets",
    "asset_Dashboard" => "All Assets",
    "laptop_Dashboard" => "Laptops",
    "pc_Dashboard" => "PCs",
    "phone_Dashboard" => "Phones",
    "tablet_Dashboard" => "Tablets",
    "user_Dashboard" => "Users",
    "log_dashboard" => "Logs",
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
    <link rel="stylesheet" href="/Assets/styles.css">
</head>
<body>

    <!-- Top Navbar -->
    <div class="top-navbar">
        <img src="/Assets/CEC-Logo.png" alt="CEC-IT Logo" class="logo">

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
        <h2><?php echo $pageTitle; ?></h2>
        <a href="../Dashboard/dashboard.php" class="<?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>">Dashboard</a>

        <!-- Assets Dropdown -->
        <div class="dropdown">
            <button class="dropdown-btn">Assets</button>
            <div class="dropdown-content">
                <a href="../Dashboard/asset_Dashboard.php">All Assets</a>
                <a href="../Dashboard/laptop_Dashboard.php">Laptops</a>
                <a href="../Dashboard/pc_Dashboard.php">PCs</a>
                <a href="../Dashboard/phone_Dashboard.php">Phones</a>
                <a href="../Dashboard/tablet_Dashboard.php">Tablets</a>
            </div>
        </div>
        <?php if (isset($_SESSION["role"]) && $_SESSION["role"] === 'admin'): ?>
            <a href="../Users/user_Dashboard.php" class="<?php echo ($currentPage == 'users') ? 'active' : ''; ?>">Users</a>
            <a href="../Admin/log_dashboard.php" class="<?php echo ($currentPage == 'logs') ? 'active' : ''; ?>">Logs</a>
        <?php endif; ?>
        <a href="../Settings/settings.php" class="<?php echo ($currentPage == 'settings') ? 'active' : ''; ?>">Settings</a>
    </div>

    <!-- Main Content Wrapper -->
    <div class="<?php echo $isAssetPage ? 'asset-content' : 'main-content'; ?>">

    <!-- Script Link -->
    <script src="/Assets/script.js?v=<?php echo time(); ?>"></script>


</body>
</html>