<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../../../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];

/* -------------------------
   FETCH QUIZZES ATTEMPTED BY PARTICIPANT
-------------------------- */
$stmt = $pdo->prepare("
  SELECT 
    q.id,
    q.title,
    q.quiz_code,
    q.total_questions,
    p.score,
    p.joined_at
  FROM accessarena_participants p
  JOIN accessarena_quizzes q ON q.id = p.quiz_id
  WHERE p.user_id = ?
    AND p.completed = 1
  ORDER BY p.joined_at DESC
");
$stmt->execute([$user_id]);
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../../../includes/header_dashboard.php';
?>

<div class="dashboard-bg"
     style="background-image:url('../../assets/images/infovault_bg.jpg')"></div>

<link rel="stylesheet" href="../../assets/css/accessarena.css">

<div class="collab-viewport">
  <div class="collab-hero">

    <div class="collab-card accessarena-card quiz-history-scope quiz-history-solid">

      <div class="accessarena-header">
        <h2>Leaderboard</h2>
        <p>Leaderboards of quizzes you attempted</p>
      </div>

      <?php if (!$attempts): ?>
        <div class="quiz-empty">
          <h3>No attempts found</h3>
          <p>Join a quiz to view leaderboard.</p>
          <a href="join_quiz.php" class="btn primary">Join Quiz</a>
        </div>
      <?php else: ?>

      <div class="quiz-history-grid">
        <?php foreach ($attempts as $i => $a):
          $accuracy = $a['total_questions']
            ? round(($a['score'] / $a['total_questions']) * 100)
            : 0;
        ?>
          <div class="quiz-card">

            <div class="quiz-card-top">
              <span class="quiz-index">#<?= $i + 1 ?></span>
              <span class="quiz-status completed">Completed</span>
            </div>

            <h3 class="quiz-title">
              <?= htmlspecialchars($a['title']) ?>
            </h3>

            <div class="quiz-meta">
              <span><strong>Quiz Code:</strong> <?= htmlspecialchars($a['quiz_code']) ?></span>
              <span><strong>Date:</strong> <?= date('d M Y', strtotime($a['joined_at'])) ?></span>
            </div>

            <div class="quiz-meta">
              <span><strong>Your Score:</strong> <?= $a['score'] ?>/<?= $a['total_questions'] ?></span>
              <span><strong>Accuracy:</strong> <?= $accuracy ?>%</span>
            </div>

            <div class="quiz-actions">
              <a href="../leaderboard.php?code=<?= urlencode($a['quiz_code']) ?>"
                 class="btn small primary">
                View Leaderboard
              </a>
            </div>

          </div>
        <?php endforeach; ?>
      </div>

      <?php endif; ?>

      <div class="center-back">
        <a href="participant_home.php" class="btn small">
          ← Back to Dashboard
        </a>
      </div>

    </div>
  </div>
</div>
