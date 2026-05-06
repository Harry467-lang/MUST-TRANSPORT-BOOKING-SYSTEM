<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'UniTransport' ?> — University Transport Booking</title>
    <link rel="stylesheet" href="<?= isset($isAdmin) ? '../style.css' : 'style.css' ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,500;9..40,700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php if (isLoggedIn()): ?>
<div class="app-layout">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon"><i class="fas fa-bus"></i></div>
            <div><h2>UniTransport</h2><small>Booking System</small></div>
        </div>
        <nav class="sidebar-nav">
            <?php if (isAdmin()): ?>
            <span class="nav-label">Administration</span>
            <a href="<?= isset($isAdmin) ? 'dashboard.php' : 'admin/dashboard.php' ?>" class="nav-link <?= $currentPage === 'dashboard.php' && isset($isAdmin) ? 'active' : '' ?>"><i class="fas fa-chart-pie"></i> Dashboard</a>
            <a href="<?= isset($isAdmin) ? 'manage_cars.php' : 'admin/manage_cars.php' ?>" class="nav-link <?= $currentPage === 'manage_cars.php' ? 'active' : '' ?>"><i class="fas fa-car"></i> Manage Cars</a>
            <a href="<?= isset($isAdmin) ? 'manage_bookings.php' : 'admin/manage_bookings.php' ?>" class="nav-link <?= $currentPage === 'manage_bookings.php' ? 'active' : '' ?>"><i class="fas fa-calendar-check"></i> All Bookings</a>
            <a href="<?= isset($isAdmin) ? 'manage_drivers.php' : 'admin/manage_drivers.php' ?>" class="nav-link <?= $currentPage === 'manage_drivers.php' ? 'active' : '' ?>"><i class="fas fa-id-card"></i> Drivers</a>
            <a href="<?= isset($isAdmin) ? 'manage_payments.php' : 'admin/manage_payments.php' ?>" class="nav-link <?= $currentPage === 'manage_payments.php' ? 'active' : '' ?>"><i class="fas fa-credit-card"></i> Payments</a>
            <a href="<?= isset($isAdmin) ? 'manage_users.php' : 'admin/manage_users.php' ?>" class="nav-link <?= $currentPage === 'manage_users.php' ? 'active' : '' ?>"><i class="fas fa-users"></i> Users</a>
            <div class="nav-divider"></div>
            <?php endif; ?>
            <span class="nav-label">Navigation</span>
            <a href="<?= isset($isAdmin) ? '../dashboard.php' : 'dashboard.php' ?>" class="nav-link <?= $currentPage === 'dashboard.php' && !isset($isAdmin) ? 'active' : '' ?>"><i class="fas fa-th-large"></i> My Dashboard</a>
            <a href="<?= isset($isAdmin) ? '../booking.php' : 'booking.php' ?>" class="nav-link <?= $currentPage === 'booking.php' ? 'active' : '' ?>"><i class="fas fa-plus-circle"></i> Book a Ride</a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?></div>
                <div class="user-info"><strong><?= sanitize($_SESSION['full_name']) ?></strong><small><?= ucfirst($_SESSION['role']) ?></small></div>
            </div>
            <a href="<?= isset($isAdmin) ? '../logout.php' : 'logout.php' ?>" class="btn btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>
    <main class="main-content">
        <header class="top-bar">
            <button class="sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="fas fa-bars"></i></button>
            <h1 class="page-title"><?= $pageTitle ?? 'Dashboard' ?></h1>
            <div class="top-bar-right"><span class="greeting">Hello, <?= sanitize(explode(' ', $_SESSION['full_name'])[0]) ?></span></div>
        </header>
        <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>">
            <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= sanitize($flash['message']) ?>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
        <?php endif; ?>
        <div class="content-area">
<?php else: ?>
    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>" style="position:fixed;top:20px;left:50%;transform:translateX(-50%);z-index:9999;min-width:320px;">
        <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= sanitize($flash['message']) ?>
        <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
    </div>
    <?php endif; ?>
<?php endif; ?>
