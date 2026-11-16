<?php
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? APP_NAME;
$user = current_user();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="description" content="Book haircuts, coloring, treatments, manicure, pedicure, and rebonding at XXL’s Salon.">
<meta name="theme-color" content="#d85c91">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="XXL’s Salon">
<meta name="format-detection" content="telephone=no">
<title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
<link rel="manifest" href="<?= e(BASE_URL) ?>/manifest.webmanifest">
<link rel="icon" type="image/svg+xml" href="<?= e(BASE_URL) ?>/assets/icon.svg">
<link rel="apple-touch-icon" href="<?= e(BASE_URL) ?>/assets/icons/icon-192.png">
<link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/style.css">
<script defer src="<?= e(BASE_URL) ?>/assets/app.js"></script>
</head>
<body>
<header class="topbar">
  <a class="brand" href="<?= e(BASE_URL) ?>/index.php"><span class="brand-mark">XX</span><span>XXL’s <small>SALON</small></span></a>
  <button class="menu-toggle" type="button" aria-label="Toggle navigation" onclick="document.body.classList.toggle('nav-open')">☰</button>
  <nav class="nav-links">
    <a href="<?= e(BASE_URL) ?>/index.php">Home</a>
    <a href="<?= e(BASE_URL) ?>/services.php">Services</a>
    <?php if ($user): ?>
      <?php if ($user['role'] === 'admin'): ?>
        <a href="<?= e(BASE_URL) ?>/dashboard.php">Dashboard</a>
        <a href="<?= e(BASE_URL) ?>/admin_appointments.php">Appointments</a>
        <a href="<?= e(BASE_URL) ?>/admin_staff.php">Staff</a>
        <a href="<?= e(BASE_URL) ?>/admin_services.php">Manage Services</a>
      <?php elseif ($user['role'] === 'staff'): ?>
        <a href="<?= e(BASE_URL) ?>/staff_dashboard.php">My Schedule</a>
      <?php else: ?>
        <a href="<?= e(BASE_URL) ?>/book.php">Book Now</a>
        <a href="<?= e(BASE_URL) ?>/my_appointments.php">My Bookings</a>
      <?php endif; ?>
      <span class="nav-user">Hi, <?= e($user['name']) ?></span>
      <a class="nav-cta" href="<?= e(BASE_URL) ?>/logout.php">Logout</a>
    <?php else: ?>
      <a href="<?= e(BASE_URL) ?>/login.php">Login</a>
      <a class="nav-cta" href="<?= e(BASE_URL) ?>/register.php">Create Account</a>
    <?php endif; ?>
  </nav>
</header>
<main class="container">
<?php if ($msg = flash('success')): ?><div class="alert success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="alert error"><?= e($msg) ?></div><?php endif; ?>
