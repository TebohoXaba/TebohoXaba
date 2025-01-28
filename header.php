<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZX Fleet Partners</title>
    <!-- Use Bootswatch Lux theme -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/lux/bootstrap.min.css">
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: darkslategrey;">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <img src="uploads/ZX-White.png" alt="ZX FLEET PARTNERS" style="height: 50px; width: auto;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" href="index.php">Home</a>
                    </li>
                    <?php if (isset($_SESSION['user'])): ?>
                        <!-- Links for logged-in users -->
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'view_appointments.php' ? 'active' : '' ?>" href="view_appointments.php">Appointments</a>
                        </li>
                        <?php if ($_SESSION['user']['role_id'] == 2): ?>
                            <!-- Admin dropdown -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="admin-management-icon">&#9881;</span> Admin
                                </a>
                                <ul class="dropdown-menu dropdown-menu-dark shadow" aria-labelledby="adminDropdown">
                                    <li><a class="dropdown-item <?= basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : '' ?>" href="admin_dashboard.php">Dashboard</a></li>
                                    <li><a class="dropdown-item <?= basename($_SERVER['PHP_SELF']) == 'manage_roles.php' ? 'active' : '' ?>" href="manage_roles.php">Manage Roles</a></li>
                                    <li><a class="dropdown-item <?= basename($_SERVER['PHP_SELF']) == 'driver_register.php' ? 'active' : '' ?>" href="driver_register.php">Driver Registration</a></li>
                                    <li><a class="dropdown-item <?= basename($_SERVER['PHP_SELF']) == 'calculate_distance.php' ? 'active' : '' ?>" href="calculate_distance.php">Distance Tester</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                        <?php if ($_SESSION['user']['role_id'] == 3): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'driver_dashboard.php' ? 'active' : '' ?>" href="driver_dashboard.php">Driver Dashboard</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'my_profile.php' ? 'active' : '' ?>" href="my_profile.php">My Profile</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <!-- Links for guests -->
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : '' ?>" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'register.php' ? 'active' : '' ?>" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
<style>
    .navbar {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .nav-link {
        position: relative;
        text-decoration: none;
        padding: 8px 15px;
        transition: color 0.3s ease;
    }

    .nav-link:hover {
        color: #ffc107; /* Gold */
    }

    .nav-link::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 3px;
        background-color: #ffc107;
        transition: width 0.3s ease;
    }

    .nav-link:hover::after {
        width: 100%;
    }

    .dropdown-menu {
        background-color: #343a40;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .dropdown-item {
        color: #f8f9fa;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .dropdown-item:hover {
        background-color: #ffc107;
        color: #212529;
    }

    .admin-management-icon {
        font-size: 16px;
        margin-right: 5px;
        color: #ffc107;
    }
</style>
<main>
    <!-- Loader -->
    <div id="page-loader" style="display: none;">
        <div class="spinner-wrapper">
            <div class="spinner"></div>
        </div>
    </div>

