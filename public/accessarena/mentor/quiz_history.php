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

$uid = $_SESSION['user_id'];

/* Fetch mentor quizzes */
$stmt = $pdo->prepare("
  SELECT id, title, total_questions, status, quiz_code, created_at
  FROM accessarena_quizzes
  WHERE creator_id = ?
  ORDER BY created_at DESC
");
$stmt->execute([$uid]);
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="dashboard-bg"
     style="background-image:url('../../assets/images/infovault_bg.jpg')"></div>

<link rel="stylesheet" href="../../assets/css/info.css">
<link rel="stylesheet" href="../../assets/css/accessarena.css">

<div class="collab-viewport">
  <div class="collab-hero">

    <div class="collab-card accessarena-card quiz-history-wrapper">

      <!-- HEADER -->
     <div class="collab-card-head quiz-history-head">
  <h1>Quiz History</h1>
  <div class="header-line"></div>
  <p class="lead">Manage, publish, and analyze all your quizzes</p>
</div>


      <!-- EMPTY STATE -->
      <?php if (!$quizzes): ?>
        <div class="glass-card empty-state">
          <h3>No quizzes created yet</h3>
          <p class="small-muted">
            Create a quiz to start adding questions and sharing with participants.
          </p>
          <a href="create_quiz.php" class="btn primary">
            ➕ Create Quiz
          </a>
        </div>
      <?php endif; ?>

      <!-- QUIZ GRID -->
      <div class="quiz-history-grid">
        <?php foreach ($quizzes as $q): ?>
          <div class="quiz-card">

            <!-- TITLE + STATUS -->
            <div class="quiz-card-top">
              <h3 class="quiz-title"><?= htmlspecialchars($q['title']) ?></h3>
              <span class="quiz-status <?= $q['status'] ?>">
                <?= ucfirst($q['status']) ?>
              </span>
            </div>

            <!-- META -->
            <div class="quiz-meta">
              <span>Questions: <?= (int)$q['total_questions'] ?></span>
              <span>Created: <?= date('d M Y', strtotime($q['created_at'])) ?></span>
            </div>

            <!-- ACTIONS -->
            <div class="quiz-actions">

              <?php if ($q['status'] === 'draft'): ?>

                <a href="add_questions.php?quiz_id=<?= $q['id'] ?>"
                   class="btn small">
                  ✏️ Add Questions
                </a>

                <a href="publish_quiz.php?quiz_id=<?= $q['id'] ?>"
                   class="btn small primary">
                  🚀 Publish
                </a>

                <a href="delete_quiz.php?id=<?= $q['id'] ?>"
                   class="btn small danger"
                   onclick="return confirm('Delete this quiz permanently?')">
                  🗑 Delete
                </a>

              <?php else: ?>

                <div class="quiz-code-box">
                  <span>Quiz Code</span>
                  <strong><?= htmlspecialchars($q['quiz_code']) ?></strong>
                </div>

                <button class="btn small"
                        onclick="navigator.clipboard.writeText('<?= $q['quiz_code'] ?>')">
                  📋 Copy Code
                </button>

                <a href="quiz_results.php?quiz_id=<?= $q['id'] ?>"
                   class="btn small">
                  📊 Results
                </a>

                <a href="../leaderboard.php?quiz_id=<?= $q['id'] ?>"
                   class="btn small">
                  🏆 Leaderboard
                </a>

              <?php endif; ?>

            </div>
          </div>
        <?php endforeach; ?>
      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
