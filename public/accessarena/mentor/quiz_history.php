<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/premium_check.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}
requirePremium();

$uid = $_SESSION['user_id'];

/* Fetch all quizzes created by mentor */
$stmt = $pdo->prepare("
  SELECT id, title, total_questions, status, created_at
  FROM accessarena_quizzes
  WHERE creator_id = ?
  ORDER BY created_at DESC
");
$stmt->execute([$uid]);
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../../../includes/header_dashboard.php';
?>
<script>
  document.body.classList.add('dashboard-page');
</script>

<!-- Background -->
<div class="dashboard-bg"
     aria-hidden="true"
     style="background-image:url('../../assets/images/infovault_bg.jpg');">
</div>

<link rel="stylesheet" href="../../assets/css/accessarena.css">

<div class="collab-viewport">
  <div class="collab-hero">

    <!-- MAIN GLASS CONTAINER -->
<div class="collab-card quiz-history-glass quiz-history-scope">
  <div class="quiz-history-content">



      <!-- HEADER -->
      <div class="accessarena-header">
        <h1 class="accessarena-title">Quiz History</h1>
        <p class="accessarena-subtitle">
          Complete history of quizzes you have created
        </p>
      </div>

      <?php if (!$quizzes): ?>
        <div class="publish-empty">
          <p>No quizzes created yet.</p>
          <a href="create_quiz.php" class="btn primary">
            ➕ Create Quiz
          </a>
        </div>
      <?php else: ?>

        <!-- QUIZ CARDS GRID -->
        <div class="publish-grid">

          <?php foreach ($quizzes as $index => $q): ?>
            <div class="publish-card">

              <!-- TOP -->
              <div class="publish-top">
                <span class="quiz-number">#<?= $index + 1 ?></span>
                <span class="quiz-status <?= $q['status'] ?>">
                  <?= ucfirst($q['status']) ?>
                </span>
              </div>

              <!-- TITLE -->
              <h3 class="publish-title">
                <?= htmlspecialchars($q['title']) ?>
              </h3>

              <!-- DETAILS -->
              <div class="publish-row">
                <span>Total Questions</span>
                <strong><?= (int)$q['total_questions'] ?></strong>
              </div>

              <div class="publish-row">
                <span>Created On</span>
                <span><?= date('d M Y', strtotime($q['created_at'])) ?></span>
              </div>

              <!-- ACTIONS (ONLY FOR DRAFT) -->
              <?php if ($q['status'] === 'draft'): ?>
                <div class="publish-actions">
                  <a href="add_questions.php?quiz_id=<?= $q['id'] ?>"
                     class="btn small">
                    ✏️ Edit
                  </a>

        <a href="publish_action.php?quiz_id=<?= $q['id'] ?>"
   class="btn primary publish-btn"
   onclick="return confirm('Publish this quiz? You cannot edit it after publishing.');">
  🚀 Publish Quiz
</a>


                </div>
              <?php endif; ?>

            </div>
          <?php endforeach; ?>

        </div>

      <?php endif; ?>

      <div style="text-align:center;margin-top:32px">
        <a href="mentor_home.php" class="btn small">
          ← Back to Dashboard
        </a>
      </div>

    </div>
   </div>
  </div> 
              </div>


  
