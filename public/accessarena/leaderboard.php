<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

/* -------------------------
   FETCH QUIZ
-------------------------- */
$quiz = null;

if (!empty($_GET['code'])) {
    $stmt = $pdo->prepare("
        SELECT id, title, quiz_code, total_questions
        FROM accessarena_quizzes
        WHERE quiz_code = ?
        LIMIT 1
    ");
    $stmt->execute([$_GET['code']]);
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$quiz) {
    die('Quiz not found');
}

$quiz_id = (int)$quiz['id'];

/* -------------------------
   LEADERBOARD DATA
-------------------------- */
$stmt = $pdo->prepare("
    SELECT 
        u.username,
        p.score,
        p.joined_at
    FROM accessarena_participants p
    JOIN users u ON u.id = p.user_id
    WHERE p.quiz_id = ?
      AND p.completed = 1
    ORDER BY p.score DESC, p.joined_at ASC
    LIMIT 5
");
$stmt->execute([$quiz_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../../includes/header_dashboard.php';
?>
<script>
  document.body.classList.add('accessarena-leaderboard-only');
</script>

<div class="dashboard-bg"
     style="background-image:url('../assets/images/infovault_bg.jpg')"></div>

<link rel="stylesheet" href="../assets/css/accessarena.css">

<!-- 👇 SCOPED CLASS -->
<div class="collab-viewport leaderboard-scope">
  <div class="collab-hero">

    <div class="collab-card accessarena-card leaderboard-card">

      <!-- HEADER -->
      <div class="leaderboard-header">
        <h2>🏆 <?= htmlspecialchars($quiz['title']) ?> Leaderboard</h2>
        <p>Quiz Code: <strong><?= htmlspecialchars($quiz['quiz_code']) ?></strong></p>
      </div>

      <?php if (!$rows): ?>
        <p class="leaderboard-empty">No completed attempts yet.</p>
      <?php else: ?>

      <!-- TABLE -->
      <div class="leaderboard-table">

        <div class="leaderboard-row head">
          <span>Rank</span>
          <span>Username</span>
          <span>Score</span>
          <span>Accuracy</span>
        </div>

        <?php foreach ($rows as $i => $r): 
          $accuracy = $quiz['total_questions']
            ? round(($r['score'] / $quiz['total_questions']) * 100)
            : 0;

          $rankEmoji = match ($i) {
            0 => '🥇',
            1 => '🥈',
            2 => '🥉',
            default => '⭐'
          };
        ?>

        <div class="leaderboard-row <?= $i < 3 ? 'top' : '' ?>">
          <span class="rank-badge"><?= $rankEmoji ?> #<?= $i + 1 ?></span>
          <span class="username"><?= htmlspecialchars($r['username']) ?></span>
          <span class="score"><?= $r['score'] ?>/<?= $quiz['total_questions'] ?></span>
          <span class="accuracy"><?= $accuracy ?>%</span>
        </div>

        <?php endforeach; ?>
      </div>
      <?php endif; ?>


    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
