<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/premium_check.php';
require_once __DIR__ . '/../../../includes/header_dashboard.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

requirePremium();
?>

<script>document.body.classList.add('dashboard-page');</script>

<div class="dashboard-bg"
     style="background-image:url('../../assets/images/infovault_bg.jpg');">
</div>

<link rel="stylesheet" href="../../assets/css/accessarena.css">

<div class="collab-viewport no-quiz-page">
  <div class="no-quiz-card">

    <h1>No Quiz Selected</h1>

    <p class="lead">
      You need to create a quiz before adding questions.
    </p>

    <div class="glass-card">
      <p>
        Start by creating a quiz draft.
        Once created, you can add questions,
        publish it, and share the quiz code.
      </p>
    </div>

    <a href="create_quiz.php" class="btn primary">
      ➕ Create Quiz
    </a>

    <div>
      <a href="mentor_home.php" class="btn small">
        ← Back to Dashboard
      </a>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
