<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/premium_check.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

requirePremium();

$user_id = $_SESSION['user_id'];
$quiz_code = trim($_GET['code'] ?? '');

if ($quiz_code === '') {
    die('Invalid quiz');
}

/* Fetch quiz details */
$stmt = $pdo->prepare("
    SELECT id, title, total_questions, time_limit
    FROM accessarena_quizzes
    WHERE quiz_code = ? AND status = 'published'
    LIMIT 1
");
$stmt->execute([$quiz_code]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    die('Quiz not found or not published');
}

/* Check if already attempted */
$stmt = $pdo->prepare("
    SELECT completed
    FROM accessarena_participants
    WHERE quiz_id = ? AND user_id = ?
");
$stmt->execute([$quiz['id'], $user_id]);
$attempt = $stmt->fetch();

if ($attempt && (int)$attempt['completed'] === 1) {
    header("Location: result.php?code=" . urlencode($quiz_code));
    exit;
}

/* Start quiz */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("
        INSERT INTO accessarena_participants (quiz_id, user_id, joined_at)
        VALUES (?, ?, NOW())
        ON DUPLICATE KEY UPDATE joined_at = NOW()
    ");
    $stmt->execute([$quiz['id'], $user_id]);

    header("Location: attempt_quiz.php?code=" . urlencode($quiz_code));
    exit;
}

require_once __DIR__ . '/../../../includes/header_dashboard.php';
?>

<!-- Page scope (IMPORTANT for CSS isolation) -->
<script>
  document.body.classList.add('accessarena-instructions-only');
</script>

<div class="dashboard-bg"
     style="background-image:url('../../assets/images/infovault_bg.jpg');">
</div>

<link rel="stylesheet" href="../../assets/css/accessarena.css">

<div class="collab-viewport">
  <div class="collab-hero">

    
    <div class="collab-card accessarena-card instructions-card" style="max-width:720px;">

      <div class="accessarena-header">
        <h2><?= htmlspecialchars($quiz['title']) ?></h2>
        <p>Please read the instructions carefully before starting</p>
      </div>

      <div class="quiz-instructions-card">
        <ul>
          <li>Total Questions: <strong><?= (int)$quiz['total_questions'] ?></strong></li>
          <li>Time Limit: <strong><?= (int)$quiz['time_limit'] ?> minutes</strong></li>
          <li>No going back once the quiz starts</li>
          <li>Quiz auto-submits when time ends</li>
          <li>Do not refresh the page</li>
        </ul>
      </div>

      <form method="POST" style="text-align:center;">
        <button type="submit" class="btn primary">
          ▶ Start Quiz
        </button>
      </form>

      <div style="margin-top:16px; text-align:center;">
        <a href="participant_home.php" class="btn small">
          ← Cancel
        </a>
      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
