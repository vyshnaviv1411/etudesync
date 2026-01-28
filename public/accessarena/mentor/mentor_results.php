<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/premium_check.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}
requirePremium();

$mentor_id = (int) $_SESSION['user_id'];

/* -------------------------
   FETCH QUIZZES CREATED BY MENTOR
-------------------------- */
$stmt = $pdo->prepare("
  SELECT 
    q.id,
    q.title,
    q.quiz_code,
    q.total_questions,
    q.created_at,
    COUNT(p.id) AS participants
  FROM accessarena_quizzes q
  LEFT JOIN accessarena_participants p 
         ON p.quiz_id = q.id AND p.completed = 1
  WHERE q.creator_id = ?
    AND q.status IN ('published','ended')
  GROUP BY q.id
  ORDER BY q.created_at DESC
");
$stmt->execute([$mentor_id]);
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../../../includes/header_dashboard.php';
?>

<div class="dashboard-bg"
     style="background-image:url('../../assets/images/infovault_bg.jpg')"></div>

<link rel="stylesheet" href="../../assets/css/accessarena.css">

<div class="collab-viewport">
  <div class="collab-hero">

    <div class="collab-card accessarena-card quiz-history-scope quiz-history-solid">

      <!-- HEADER -->
      <div class="accessarena-header">
        <h2>Results & Analysis</h2>
        <p>Detailed performance insights of your quizzes</p>
      </div>

      <?php if (!$quizzes): ?>
        <div class="quiz-empty">
          <h3>No published quizzes</h3>
          <p>Create and publish a quiz to view results.</p>
          <a href="create_quiz.php" class="btn primary">Create Quiz</a>
        </div>
      <?php else: ?>

      <div class="quiz-history-grid">
        <?php foreach ($quizzes as $i => $q): ?>
          <div class="quiz-card">

            <div class="quiz-card-top">
              <span class="quiz-index">#<?= $i + 1 ?></span>
              <span class="quiz-status completed">Published</span>
            </div>

            <h3 class="quiz-title">
              <?= htmlspecialchars($q['title']) ?>
            </h3>

            <div class="quiz-meta">
              <span><strong>Quiz Code:</strong> <?= htmlspecialchars($q['quiz_code']) ?></span>
              <span><strong>Participants:</strong> <?= (int)$q['participants'] ?></span>
            </div>

            <div class="quiz-meta">
              <span><strong>Questions:</strong> <?= (int)$q['total_questions'] ?></span>
              <span><strong>Date:</strong> <?= date('d M Y', strtotime($q['created_at'])) ?></span>
            </div>

            <div class="quiz-actions">
              <a href="quiz_results.php?quiz_id=<?= (int)$q['id'] ?>"
                 class="btn small primary">
                View Analysis
              </a>
            </div>

          </div>
        <?php endforeach; ?>
      </div>

      <?php endif; ?>

      <div class="center-back">
        <a href="mentor_home.php" class="btn small">
          ← Back to Dashboard
        </a>
      </div>

    </div>
  </div>
</div>


