<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../../../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];

/* -------------------------
   FETCH STUDENT ATTEMPTS
-------------------------- */
$stmt = $pdo->prepare("
  SELECT 
    q.id AS quiz_id,
    q.title,
    q.quiz_code,
    q.total_questions,
    p.score,
    p.completed,
    p.joined_at
  FROM accessarena_participants p
  JOIN accessarena_quizzes q ON q.id = p.quiz_id
  WHERE p.user_id = ?
  ORDER BY p.joined_at ASC
");
$stmt->execute([$user_id]);
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalAttempts = count($attempts);

require_once __DIR__ . '/../../../includes/header_dashboard.php';
?>

<div class="dashboard-bg"
     style="background-image:url('../../assets/images/infovault_bg.jpg')"></div>

<link rel="stylesheet" href="../../assets/css/accessarena.css">

<div class="collab-viewport">
  <div class="collab-hero">

    <!-- SINGLE MAIN CARD -->
   <div class="collab-card accessarena-card quiz-history-scope quiz-history-solid">


      <!-- HEADER -->
      <div class="accessarena-header spaced">
        <h2>My Quiz History</h2>
        <p>All quizzes you have attempted</p>
      </div>

      <?php if (!$attempts): ?>
        <div class="quiz-empty">
          <h3>No quizzes attempted</h3>
          <p>Join a quiz to see your history here.</p>
          <a href="join_quiz.php" class="btn primary">Join Quiz</a>
        </div>
      <?php else: ?>

      <div class="quiz-history-grid">

        <?php foreach ($attempts as $index => $a): 
          $attemptNumber = $index + 1; // oldest = 1, newest = highest
          $accuracy = $a['total_questions']
            ? round(($a['score'] / $a['total_questions']) * 100)
            : 0;
        ?>

        <div class="quiz-card">

          <div class="quiz-card-top">
            <span class="quiz-index">#<?= $attemptNumber ?></span>

            <!-- ALWAYS COMPLETED -->
            <span class="quiz-status completed">
              Completed
            </span>
          </div>

          <h3 class="quiz-title">
            <?= htmlspecialchars($a['title']) ?>
          </h3>

          <div class="quiz-meta">
            <span><strong>Quiz Code:</strong> <?= htmlspecialchars($a['quiz_code']) ?></span>
            <span><strong>Date:</strong> <?= date('d M Y', strtotime($a['joined_at'])) ?></span>
          </div>

          <div class="quiz-meta">
            <span><strong>Score:</strong> <?= (int)$a['score'] ?>/<?= (int)$a['total_questions'] ?></span>
            <span><strong>Accuracy:</strong> <?= $accuracy ?>%</span>
          </div>

          <div class="quiz-actions">
            <a href="result.php?code=<?= urlencode($a['quiz_code']) ?>"
               class="btn small primary">
              View Result
            </a>

            <a href="../leaderboard.php?code=<?= urlencode($a['quiz_code']) ?>"
               class="btn small outline">
              Leaderboard
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
