<?php
// public/accessarena/mentor/create_quiz.php

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

$error = '';

/* ---------------------------
   HANDLE FORM SUBMISSION
---------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $time_limit = !empty($_POST['time_limit']) ? (int)$_POST['time_limit'] : null;

    if ($title === '') {
        $error = 'Quiz title is required.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO accessarena_quizzes 
              (creator_id, title, description, time_limit, status)
            VALUES 
              (:creator_id, :title, :description, :time_limit, 'draft')
        ");

        $stmt->execute([
            ':creator_id' => $_SESSION['user_id'],
            ':title' => $title,
            ':description' => $description,
            ':time_limit' => $time_limit
        ]);

        $quiz_id = $pdo->lastInsertId();

        header("Location: add_questions.php?quiz_id={$quiz_id}");
        exit;
    }
}

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
    <div class="collab-card accessarena-card" style="max-width:640px;">

      <div class="accessarena-header">
        <h1 class="accessarena-title">Create Quiz</h1>
        <p class="accessarena-subtitle">Start by creating a quiz draft</p>
      </div>

      <?php if ($error): ?>
        <p style="color:#ff6b6b; text-align:center; margin-bottom:12px;">
          <?= htmlspecialchars($error) ?>
        </p>
      <?php endif; ?>

      <form method="POST" class="accessarena-form">

        <div class="form-group">
          <label>Quiz Title *</label>
          <input type="text" name="title" required>
        </div>

        <div class="form-group">
          <label>Description (optional)</label>
          <textarea name="description" rows="3"></textarea>
        </div>

        <div class="form-group">
          <label>Time Limit (minutes, optional)</label>
          <input type="number" name="time_limit" min="1">
        </div>

        <div style="text-align:center; margin-top:24px;">
          <button type="submit" class="btn primary">
            Create Quiz
          </button>
        </div>

        
      <div style="margin-top:16px">
        <a href="mentor_home.php" class="btn small">
          ← Back to Dashboard
        </a>
      </div>

      </form>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
