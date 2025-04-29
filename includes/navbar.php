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
$role        = $_SESSION['role'] ?? '';
$currentPage = basename($_SERVER['PHP_SELF'], ".php"); 

// Determine if we are on an asset-related page
$assetPages = ["asset_Dashboard", "laptop_Dashboard", "pc_Dashboard", "phone_Dashboard", "tablet_Dashboard"];
$isAssetPage = in_array($currentPage, $assetPages);

// Set custom titles for each page
$pageTitles = [
    "dashboard" => "Dashboard",
    "assets" => "Assets",
    "laptop_Dashboard" => "Laptops",
    "pc_Dashboard" => "PCs",
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
        <!-- Depricated Search Bar 
            <input type="text" placeholder="Search assets...">
        -->
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
        <a href="../Assets/dashboard.php" class="<?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>">Dashboard</a>

 <!-- Assets dropdown -->
 <div class="dropdown">
      <button class="dropdown-btn <?= in_array($currentPage, ['laptop_Dashboard','pc_Dashboard','tablet_Dashboard','phone_Dashboard']) ? 'open' : '' ?>">
        Assets
      </button>
      <div class="dropdown-content">
        <a href="../Assets/laptop_Dashboard.php"
           class="<?= $currentPage==='laptop_Dashboard' ? 'active' : '' ?>">
          Laptops
        </a>
      </div>
    </div>

    <!-- Users: only for Managers -->
    <?php if ($role === 'Manager'): ?>
      <a href="../Users/user_Dashboard.php"
         class="<?= $currentPage==='user_Dashboard' ? 'active' : '' ?>">
        Users
      </a>
    <?php endif; ?>

    <!-- Employees & Logs: for Managers & Technicians -->
    <?php if (in_array($role, ['Manager','Technician'], true)): ?>
      <a href="/Forms/Employees/employee_Dashboard.php"
         class="<?= $currentPage==='employee_Dashboard' ? 'active' : '' ?>">
        Employees
      </a>
      <a href="../Admin/log_dashboard.php"
         class="<?= $currentPage==='log_dashboard' ? 'active' : '' ?>">
        Logs
      </a>
    <?php endif; ?>

    <a href="../Settings/settings.php"
       class="<?= $currentPage==='settings' ? 'active' : '' ?>">
      Settings
    </a>
  </div>

    <!-- Main Content Wrapper -->
    <div class="<?php echo $isAssetPage ? 'asset-content' : 'main-content'; ?>">

    <!-- Script Link -->
    <script src="/Assets/script.js?v=<?php echo time(); ?>"></script>


</body>
</html>