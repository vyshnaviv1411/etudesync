<?php
// includes/header_public.php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/* Default */
if (!isset($is_index_page)) {
    $is_index_page = false;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ÉtudeSync</title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700;800&display=swap" rel="stylesheet">

  <!-- Main CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="page-wrapper public-page<?= $is_index_page ? ' index-page' : '' ?>">

<!-- 🔥 GLOBAL BACKGROUND (ALL PUBLIC PAGES) -->
<div id="bg-slider" class="bg-slider" aria-hidden="true"></div>
<div class="bg-overlay" aria-hidden="true"></div>

<!-- ===== GLASS HEADER ===== -->
<header class="site-topbar glass-header">
  <div class="container site-topbar-inner">

    <a href="index.php" class="brand-link">
      <img src="assets/images/logo.jpg" alt="ÉtudeSync" class="brand-logo">
      <span class="brand-name">ÉtudeSync</span>
    </a>

    <nav class="main-nav">
      <a href="about.php">About</a>
      <a href="services.php">Services</a>
      <a href="get_started.php">Login</a>
    </nav>

  </div>
</header>

<main class="main-content">
