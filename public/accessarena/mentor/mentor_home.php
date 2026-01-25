<?php
// public/accessarena/mentor/mentor_home.php

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
        <h2>Mentor Dashboard</h2>
        <p>Manage your quizzes from creation to results</p>
      </div>

      <!-- Mentor Menu -->
      <div class="accessarena-actions">

        <a href="create_quiz.php" class="action-card">
          <div class="action-icon">📝</div>
          <div class="action-title">Create Quiz</div>
          <div class="action-sub">Create a new quiz draft</div>
        </a>

        <a href="add_questions.php" class="action-card">
          <div class="action-icon">➕</div>
          <div class="action-title">Add Questions</div>
          <div class="action-sub">Add MCQs to your quiz</div>
        </a>

        <a href="publish_quiz.php" class="action-card">
          <div class="action-icon">🚀</div>
          <div class="action-title">Publish Quiz</div>
          <div class="action-sub">Generate quiz code</div>
        </a>

        <a href="quiz_results.php" class="action-card">
          <div class="action-icon">📊</div>
          <div class="action-title">Results & Analysis</div>
          <div class="action-sub">View participant performance</div>
        </a>

        <a href="quiz_history.php" class="action-card">
          <div class="action-icon">📚</div>
          <div class="action-title">Quiz History</div>
          <div class="action-sub">View all your quizzes</div>
        </a>

        <a href="../leaderboard.php" class="action-card">
          <div class="action-icon">🏆</div>
          <div class="action-title">Leaderboard</div>
          <div class="action-sub">Top performers</div>
        </a>

      </div>

    </div>
  </div>
</div>

<script src="../../assets/js/accessarena.js" defer></script>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
