<?php
// public/accessarena/participant/participant_home.php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/premium_check.php';

$body_class = 'dashboard-page accessarena-page';
$disable_dashboard_bg = true;

// Auth check
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

// Premium check
requirePremium();

require_once __DIR__ . '/../../../includes/header_dashboard.php';
?>

<script>
  document.body.classList.add('dashboard-page');
</script>

<!-- Background -->
<div class="dashboard-bg" aria-hidden="true"
     style="background-image: url('../../assets/images/infovault_bg.jpg');">
</div>

<link rel="stylesheet" href="../../assets/css/info.css">
<link rel="stylesheet" href="../../assets/css/accessarena.css?v=1">

<div class="collab-viewport">
  <div class="collab-hero">

    <!-- BIG GLASS CARD -->
    <div class="collab-card accessarena-card">

      <!-- Header -->
      <div class="accessarena-header">
        <h2>Participant Dashboard</h2>
        <p>Join quizzes, view results, and track your progress</p>
      </div>

      <!-- Participant Menu -->
      <div class="accessarena-actions">

        <a href="join_quiz.php" class="action-card">
          <div class="action-icon">🔑</div>
          <div class="action-title">Join Quiz</div>
          <div class="action-sub">Enter quiz code to participate</div>
        </a>

        <a href="participant_results.php" class="action-card">
          <div class="action-icon">📊</div>
          <div class="action-title">Results & Analysis</div>
          <div class="action-sub">View your performance in detail</div>
        </a>

        <a href="participant_leaderboard.php" class="action-card">
          <div class="action-icon">🏆</div>
          <div class="action-title">Leaderboard</div>
          <div class="action-sub">See top scorers</div>
        </a>

        <a href="my_attempts.php" class="action-card">
          <div class="action-icon">📚</div>
          <div class="action-title">My Quiz History</div>
          <div class="action-sub">All quizzes you attempted</div>
        </a>

      </div>
       <div style="margin-top:16px">
        <a href="../accessarena_home.php" class="btn small">
          ← Back to Dashboard
        </a>
        </div>
    </div>
  </div>
</div>

<script src="../../assets/js/accessarena.js" defer></script>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
