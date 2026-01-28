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

$uid = $_SESSION['user_id'];
$quiz_code = trim($_GET['code'] ?? '');

if ($quiz_code === '') {
    die('Invalid quiz');
}

/* Fetch quiz */
$stmt = $pdo->prepare("
    SELECT id, title, time_limit
    FROM accessarena_quizzes
    WHERE quiz_code = ? AND status = 'published'
");
$stmt->execute([$quiz_code]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    die('Quiz not found');
}

/* Fetch participant */
$stmt = $pdo->prepare("
    SELECT id, completed
    FROM accessarena_participants
    WHERE quiz_id = ? AND user_id = ?
");
$stmt->execute([$quiz['id'], $uid]);
$participant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$participant) {
    die('Quiz not started correctly');
}

if ((int)$participant['completed'] === 1) {
    header("Location: result.php?code=" . urlencode($quiz_code));
    exit;
}

$participant_id = (int)$participant['id'];

/* Fetch questions */
$stmt = $pdo->prepare("
    SELECT id, question_text, option_a, option_b, option_c, option_d
    FROM accessarena_questions
    WHERE quiz_id = ?
");
$stmt->execute([$quiz['id']]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$questions) {
    die('No questions available');
}

require_once __DIR__ . '/../../../includes/header_dashboard.php';
?>

<script>
  document.body.classList.add('accessarena-attempt-only');
</script>

<div class="dashboard-bg"
     style="background-image:url('../../assets/images/infovault_bg.jpg');">
</div>

<link rel="stylesheet" href="../../assets/css/accessarena.css">

<div class="collab-viewport">
  <div class="collab-hero">

    <div class="collab-card accessarena-card" style="max-width:900px;">

      <!-- HEADER -->
      <div class="accessarena-header">
        <h2><?= htmlspecialchars($quiz['title']) ?></h2>
        <p>
          Time Remaining:
          <strong><span id="timer"><?= (int)$quiz['time_limit'] ?>:00</span></strong>
        </p>
      </div>

      <form id="quizForm" method="post" action="submit_quiz.php">

        <input type="hidden" name="quiz_code" value="<?= htmlspecialchars($quiz_code) ?>">

        <?php foreach ($questions as $index => $q): ?>
          <div class="quiz-attempt-card" style="margin-bottom:18px;">
            <div style="font-weight:700;margin-bottom:10px;">
              Q<?= $index + 1 ?>. <?= htmlspecialchars($q['question_text']) ?>
            </div>

            <?php foreach (['A','B','C','D'] as $opt): ?>
              <?php if (!empty($q['option_' . strtolower($opt)])): ?>
                <label style="display:block;margin-bottom:6px;">
                  <input type="radio"
                         name="answers[<?= $q['id'] ?>]"
                         value="<?= $opt ?>">
                  <?= htmlspecialchars($q['option_' . strtolower($opt)]) ?>
                </label>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>

        <div style="text-align:center;margin-top:30px;">
          <button type="submit" class="btn primary">
            Submit Quiz
          </button>
        </div>

      </form>

    </div>
  </div>
</div>

<script>
/* TIMER */
let totalSeconds = <?= (int)$quiz['time_limit'] ?> * 60;
const timerEl = document.getElementById('timer');
const form = document.getElementById('quizForm');

function updateTimer() {
  const min = Math.floor(totalSeconds / 60);
  const sec = totalSeconds % 60;
  timerEl.textContent =
    String(min).padStart(2,'0') + ':' + String(sec).padStart(2,'0');

  if (totalSeconds <= 0) {
    form.submit(); // auto submit
  }
  totalSeconds--;
}

setInterval(updateTimer, 1000);

/* Prevent accidental refresh */
window.onbeforeunload = function () {
  return "Quiz in progress. Are you sure?";
};
</script>


