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

<!-- Background -->
<div class="dashboard-bg" aria-hidden="true"
     style="background-image:url('../../assets/images/infovault_bg.jpg');">
</div>


<link rel="stylesheet" href="../../assets/css/accessarena.css">

<div class="collab-viewport no-quiz-page">
  <div class="collab-hero">


  <div class="collab-card accessarena-card no-quiz-card">


      <h1 style="margin-bottom:10px;">No Quiz Selected</h1>

      <p class="lead" style="margin-bottom:20px;">
        You need to create a quiz before adding questions.
      </p>

      <div class="glass-card" style="padding:18px;margin-bottom:20px">
        <p class="small-muted">
          Start by creating a quiz draft.  
          Once created, you can add questions, publish it, and share the code.
        </p>
      </div>

      <a href="create_quiz.php" class="btn primary">
        ➕ Create Quiz
      </a>

      <div style="margin-top:16px">
        <a href="mentor_home.php" class="btn small">
          ← Back to Dashboard
        </a>
      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
