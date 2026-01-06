<?php
// includes/header_public.php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' — ÉtudeSync' : 'ÉtudeSync' ?></title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700;800&display=swap" rel="stylesheet">

  <!-- Main CSS -->
  <link rel="stylesheet" href="assets/css/style.css" />
</head>

<body class="index-page page-wrapper">

  <!-- Background slider (public pages only) -->
  <div id="bg-slider" class="bg-slider" aria-hidden="true"></div>
  <div class="bg-overlay" aria-hidden="true"></div>

  <!-- ===== PUBLIC SIMPLE HEADER ===== -->
  <header class="site-topbar container" role="banner" aria-label="Main site header">
    <div class="brand-left">
      <a href="index.php" class="brand-link" style="display:flex;align-items:center;gap:10px;text-decoration:none">
        <img src="assets/images/logo.jpg" alt="ÉtudeSync logo" class="brand-logo" />
        <span class="brand-name">ÉtudeSync</span>
      </a>
    </div>

    <!-- simple navigation -->
    <nav class="main-nav" role="navigation" aria-label="Primary">
      <a href="about.php">About</a>
      <a href="services.php">Services</a>
    </nav>

    <!-- only login button -->
    <div class="header-controls">
      <a href="login.php" class="btn primary small">Login</a>
    </div>
  </header>

  <main class="main-content page-content">
    <div class="container">
