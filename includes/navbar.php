<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"])) {
    header("Location: ../Login/login.php");
    exit();
}

// Get the current page name dynamically
$pageTitle = "CEC-IT";
$currentPage = basename($_SERVER['PHP_SELF'], ".php");

$pageTitles = [
    "dashboard" => "Dashboard",
    "laptop_Dashboard" => "Laptops",
    "user_Dashboard" => "Users",
    "settings" => "Settings",
];

if (array_key_exists($currentPage, $pageTitles)) {
    $pageTitle = $pageTitles[$currentPage];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?php echo $pageTitle; ?> | CEC-IT</title>
    <link rel="stylesheet" href="../../Assets/styles.css" />
</head>
<body>

    <!-- Top Navbar -->
    <div class="top-navbar">
        <img src="../../Assets/CEC-Logo.png" alt="CEC-IT Logo" class="logo" />
        <div class="navbar-right">
            <input type="text" placeholder="Search assets..." />
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

        <div class="dropdown">
        <button class="dropdown-btn">Assets</button>
        <div class="dropdown-content">
            <a href="../Dashboard/laptop_Dashboard.php">Laptops</a>
        </div>
        </div>

        <a href="../Dashboard/employee_Dashboard.php">Employees</a>
        <a href="../Users/user_Dashboard.php">Users</a>
        <a href="../Admin/log_dashboard.php">Logs</a>
        <a href="../Settings/settings.php">Settings</a>

    </div>

    <!-- Main Content Wrapper -->
    <div class="main-content">

    <script src="../../Assets/script.js?v=<?php echo time(); ?>"></script>
    <script>
        document.querySelectorAll(".dropdown-btn").forEach(button => {
            button.addEventListener("click", () => {
                const dropdown = button.parentElement;
                dropdown.classList.toggle("open");
            });
        });
    </script>

</body>
</html>