<?php
session_start();
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/premium_check.php';
require_once __DIR__ . '/../../../includes/header_dashboard.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}
requirePremium();

if (!isset($_GET['quiz_id']) || !is_numeric($_GET['quiz_id'])) {
    die('Invalid quiz');
}

$quiz_id = (int)$_GET['quiz_id'];
$uid = $_SESSION['user_id'];

/* Fetch quiz */
$stmt = $pdo->prepare("
  SELECT * FROM accessarena_quizzes
  WHERE id=? AND creator_id=?
");
$stmt->execute([$quiz_id, $uid]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    die('Quiz not found');
}

/* If already published → do NOT regenerate code */
if ($quiz['status'] === 'published') {
    $code = $quiz['quiz_code'];
} else {

    // Generate ONCE
    $code = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

    $pdo->prepare("
      UPDATE accessarena_quizzes
      SET status='published', quiz_code=?
      WHERE id=?
    ")->execute([$code, $quiz_id]);
}
?>

<!-- BACKGROUND -->
<div class="dashboard-bg" style="background-image:url('../../assets/images/infovault_bg.jpg')"></div>

<div class="collab-card accessarena-card" style="max-width:600px;text-align:center">

  <h1>Quiz Published 🎉</h1>
  <p class="lead"><?= htmlspecialchars($quiz['title']) ?></p>

  <div class="glass-card" style="padding:22px;margin:24px 0">
    <h3>Quiz Code</h3>
    <code style="font-size:26px;letter-spacing:3px">
      <?= htmlspecialchars($code) ?>
    </code>
    <p class="small-muted" style="margin-top:10px">
      Share this code with participants to join
    </p>
  </div>

  <div style="display:flex;gap:12px;justify-content:center">
    <a href="../quiz_history.php" class="btn primary">
      📚 Quiz History
    </a>

    <a href="../results/quiz_results.php?quiz_id=<?= $quiz_id ?>" class="btn ghost">
      📊 View Results
    </a>
  </div>

</div>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
