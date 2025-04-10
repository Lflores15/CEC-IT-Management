<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['visible_columns'] = $_POST['columns'] ?? [];
}

header("Location: asset_Dashboard.php");
exit();