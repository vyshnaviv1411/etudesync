<?php
// public/accessarena/accessarena_home.php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// ✅ CORRECT PATHS
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/premium_check.php';
$body_class = 'dashboard-page accessarena-page';


$disable_dashboard_bg = true;
// Check login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Require premium
requirePremium();

/* ---------------------------
   HANDLE MODE SELECTION
---------------------------- */
if (isset($_GET['mode'])) {
    if ($_GET['mode'] === 'mentor') {
        header('Location: mentor/mentor_home.php');
        exit;
    }

    if ($_GET['mode'] === 'participant') {
        header('Location: participant/participant_home.php');
        exit;
    }
}

// Header
require_once __DIR__ . '/../../includes/header_dashboard.php';
?>

<script>
  document.body.classList.add('dashboard-page');
</script>

<!-- Background -->
<div class="dashboard-bg" aria-hidden="true"
     style="background-image: url('../assets/images/assessarena-bg.jpg');">
</div>
<link rel="stylesheet" href="../assets/css/info.css">
<link rel="stylesheet" href="../assets/css/accessarena.css?v=1">

<div class="collab-viewport">
  <div class="collab-hero">
    <div class="collab-card accessarena-card">

      <!-- HEADER -->
<div class="accessarena-header">
  <h1 class="accessarena-title">AccessArena</h1>
  <p class="accessarena-subtitle">Choose how you want to continue</p>
</div>


      <!-- ACTIONS -->
      <div class="accessarena-actions">

        <a class="action-card" href="accessarena_home.php?mode=mentor">
          <div class="action-icon">
            <img src="../assets/images/mentor-icon.png" alt="Mentor Mode">
          </div>
          <div class="action-title">Mentor Mode</div>
          <div class="action-sub">
            Create, publish, and analyze quizzes
          </div>
        </a>

        <a class="action-card" href="accessarena_home.php?mode=participant">
          <div class="action-icon">
            <img src="../assets/images/participant-icon.png" alt="Participant Mode">
          </div>
          <div class="action-title">Participant Mode</div>
          <div class="action-sub">
            Join quizzes and view your performance
          </div>
        </a>

      </div>

    </div>
  </div>
</div>



<script src="../assets/js/accessarena.js" defer></script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
