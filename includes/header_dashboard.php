<?php
// includes/header_dashboard.php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$disable_dashboard_bg = $disable_dashboard_bg ?? false;


$webBase = '/etudesync/public';
$body_class = $body_class ?? 'page-wrapper';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' — ÉtudeSync' : 'ÉtudeSync' ?></title>
  <link rel="stylesheet" href="<?= htmlspecialchars($webBase) ?>/assets/css/style.css?v=4" />
</head>

<body class="<?= htmlspecialchars($body_class) ?>">

<?php if (!$disable_dashboard_bg): ?>
  <div class="dashboard-bg" aria-hidden="true">
    <video autoplay muted loop playsinline>
      <source src="<?= htmlspecialchars($webBase) ?>/assets/videos/desk1.mp4" type="video/mp4">
    </video>
    <div class="dashboard-bg-overlay"></div>
  </div>

  <div id="bg-slider" class="bg-slider" aria-hidden="true"></div>
  <div class="bg-overlay" aria-hidden="true"></div>
<?php endif; ?>


<!-- ===== HEADER ===== -->
<header class="site-topbar" role="banner" style="position:relative; z-index:99999;">
  <div class="container"
       style="display:flex;align-items:center;justify-content:space-between;gap:12px;">

    <!-- LEFT: BRAND -->
    <div class="brand-left">
      <a href="<?= $webBase ?>/dashboard.php"
         class="brand-link"
         style="display:flex;align-items:center;gap:10px;text-decoration:none;">
        <img src="<?= $webBase ?>/assets/images/logo.jpg"
             alt="ÉtudeSync logo"
             class="brand-logo" />
        <span class="brand-name">ÉtudeSync</span>
      </a>
    </div>

    <!-- RIGHT: CONTROLS -->
    <div class="header-controls"
         style="display:flex;align-items:center;gap:12px;position:relative;">

      <!-- MUSIC -->
      <button id="musicToggle"
        class="header-icon music-btn"
        type="button"
        title="Play soothing music"
        style="cursor:pointer;">
  <svg id="musicIcon" width="18" height="18" viewBox="0 0 24 24" fill="none">
    <path d="M7 6v12l10-6L7 6z" fill="currentColor"></path>
  </svg>
</button>


      <!-- PROFILE -->
      <?php if (!empty($_SESSION['user_id'])):
        $sessAvatar = $_SESSION['user_avatar'] ?? 'assets/images/avatar-default.png';
        $imgUrl = rtrim($webBase, '/') . '/' . ltrim($sessAvatar, '/');
      ?>
        <div class="profile-menu" id="profileMenu"
             style="position:relative; z-index:100000;">

          <button id="profileTrigger"
                  type="button"
                  style="all:unset; cursor:pointer;">
            <img src="<?= htmlspecialchars($imgUrl) ?>"
                 alt="Profile"
                 class="profile-avatar"
                 style="width:36px;height:36px;border-radius:10px;object-fit:cover;">
          </button>

          <div id="profileDropdown"
               class="profile-dropdown"
               style="
                 position:absolute;
                 top:48px;
                 right:0;
                 min-width:180px;
                 background:rgba(20,25,35,0.97);
                 backdrop-filter:blur(12px);
                 border:1px solid rgba(255,255,255,0.15);
                 border-radius:12px;
                 box-shadow:0 20px 60px rgba(0,0,0,0.6);
                 display:none;
                 flex-direction:column;
                 z-index:100001;
               ">
            <a href="<?= $webBase ?>/profile.php">Edit Profile</a>

            <?php if (empty($_SESSION['is_premium'])): ?>
              <a href="<?= $webBase ?>/premium_access.php">Upgrade</a>
            <?php endif; ?>

            <a href="<?= $webBase ?>/logout.php">Logout</a>
          </div>
        </div>
      <?php else: ?>
        <a href="<?= $webBase ?>/login.php" class="btn primary small">Login</a>
      <?php endif; ?>

    </div>
  </div>

  <audio id="bgMusic" preload="none"></audio>

</header>

<!-- ===== JS (INLINE, BULLETPROOF) ===== -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  const trigger = document.getElementById('profileTrigger');
  const menu = document.getElementById('profileMenu');
  const dropdown = document.getElementById('profileDropdown');

  if (!trigger || !menu || !dropdown) return;

  trigger.addEventListener('click', (e) => {
    e.stopPropagation();
    dropdown.style.display =
      dropdown.style.display === 'flex' ? 'none' : 'flex';
  });

  dropdown.addEventListener('click', (e) => {
    e.stopPropagation();
  });

  document.addEventListener('click', () => {
    dropdown.style.display = 'none';
  });
});
</script>

<main class="main-content page-content">
  <div class="container">
