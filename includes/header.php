<?php
require_once 'auth.php';
requireLogin();

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Integrated Green Campus Management System</title>
    <!-- Use Google Fonts and Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/style.css?v=12.0">
</head>
<body>
<div class="wrapper">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <span class="material-symbols-outlined">eco</span> IGCMS
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">dashboard</span> Dashboard
            </a>
            <a href="add_energy.php" class="nav-item <?php echo $currentPage == 'add_energy.php' ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">bolt</span> Energy Usage
            </a>
            <a href="add_waste.php" class="nav-item <?php echo $currentPage == 'add_waste.php' ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">delete_outline</span> Waste Mgmt
            </a>
            <a href="add_plantation.php" class="nav-item <?php echo $currentPage == 'add_plantation.php' ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">forest</span> Plantation
            </a>
            <a href="events.php" class="nav-item <?php echo $currentPage == 'events.php' ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">event_available</span> Events
            </a>
            <a href="view_reports.php" class="nav-item <?php echo $currentPage == 'view_reports.php' ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">bar_chart</span> Reports
            </a>
            <?php if (isAdmin()): ?>
            <a href="admin_manage_users.php" class="nav-item <?php echo $currentPage == 'admin_manage_users.php' ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">group</span> Manage Users
            </a>
            <a href="feedback.php" class="nav-item <?php echo $currentPage == 'feedback.php' ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">chat_bubble</span> Feedback
            </a>
            <?php endif; ?>
            
            <a href="logout.php" class="nav-item logout-nav">
                <span class="material-symbols-outlined">logout</span> Logout
            </a>
        </nav>
    </aside>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Top Header -->
        <header class="top-header">
            <button class="menu-toggle" id="menu-toggle">
                <span class="material-symbols-outlined">menu</span>
            </button>
            <div class="user-info">
                <span class="welcome-text">Welcome, <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span></span>
            </div>
        </header>

        <!-- Content Area -->
        <main class="content">
