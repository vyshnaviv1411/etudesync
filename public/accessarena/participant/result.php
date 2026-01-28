<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../../../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$code = $_GET['code'] ?? null;

if (!$code) {
    die('Invalid or missing quiz code');
}

/* -------------------------
   FETCH QUIZ
-------------------------- */
$qStmt = $pdo->prepare("
    SELECT id, title, total_questions
    FROM accessarena_quizzes
    WHERE quiz_code = ?
    LIMIT 1
");
$qStmt->execute([$code]);
$quiz = $qStmt->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    die('Quiz not found');
}

$quiz_id = (int)$quiz['id'];

/* -------------------------
   FETCH PARTICIPANT
-------------------------- */
$pStmt = $pdo->prepare("
    SELECT id, score
    FROM accessarena_participants
    WHERE quiz_id = ? AND user_id = ? AND completed = 1
    LIMIT 1
");
$pStmt->execute([$quiz_id, $user_id]);
$participant = $pStmt->fetch(PDO::FETCH_ASSOC);

if (!$participant) {
    die('Result not available');
}

$participant_id = (int)$participant['id'];
$user_score = (int)$participant['score'];

/* -------------------------
   CALCULATE RANK
-------------------------- */

/* Count how many scored MORE than this user */
$rankStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM accessarena_participants
    WHERE quiz_id = ?
      AND completed = 1
      AND score > ?
");
$rankStmt->execute([$quiz_id, $user_score]);
$higher_scores = (int)$rankStmt->fetchColumn();

$rank = $higher_scores + 1;

/* Total participants */
$totalStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM accessarena_participants
    WHERE quiz_id = ? AND completed = 1
");
$totalStmt->execute([$quiz_id]);
$total_participants = (int)$totalStmt->fetchColumn();

/* -------------------------
   FETCH ANSWERS
-------------------------- */
$aStmt = $pdo->prepare("
    SELECT 
        q.question_text,
        q.correct_option,
        a.selected_option,
        a.is_correct
    FROM accessarena_answers a
    JOIN accessarena_questions q ON q.id = a.question_id
    WHERE a.participant_id = ?
    ORDER BY q.id ASC
");
$aStmt->execute([$participant_id]);
$answers = $aStmt->fetchAll(PDO::FETCH_ASSOC);

/* -------------------------
   CALCULATIONS
-------------------------- */
$total = count($answers);
$correct = array_sum(array_column($answers, 'is_correct'));
$wrong = $total - $correct;
$accuracy = $total > 0 ? round(($correct / $total) * 100) : 0;

require_once __DIR__ . '/../../../includes/header_dashboard.php';
?>

<script>
  document.body.classList.add('accessarena-result-only');
</script>

<div class="dashboard-bg"
     style="background-image:url('../../assets/images/infovault_bg.jpg')"></div>

<link rel="stylesheet" href="../../assets/css/accessarena.css">

<div class="collab-viewport">
  <div class="collab-hero">

    <div class="collab-card accessarena-card">

      <!-- HEADER -->
      <div class="accessarena-header">
        <h2><?= htmlspecialchars($quiz['title']) ?> – Result</h2>
        <p>Your performance summary</p>
      </div>

      <!-- SUMMARY -->
      <div class="quiz-summary-grid">

        <div class="summary-box">
          <span>Score</span>
          <strong><?= $correct ?> / <?= $total ?></strong>
        </div>

        <div class="summary-box">
          <span>Accuracy</span>
          <strong><?= $accuracy ?>%</strong>
        </div>

        <div class="summary-box">
          <span>Rank</span>
          <strong>#<?= $rank ?> / <?= $total_participants ?></strong>
        </div>

        <div class="summary-box">
          <span>Correct</span>
          <strong><?= $correct ?></strong>
        </div>

        <div class="summary-box">
          <span>Wrong</span>
          <strong><?= $wrong ?></strong>
        </div>

      </div>

      <!-- QUESTION ANALYSIS -->
      <div style="margin-top:36px">

        <h3 style="margin-bottom:18px">Question-wise Analysis</h3>

        <?php foreach ($answers as $i => $a): ?>
          <div class="analysis-card">

            <div class="analysis-q">
              Q<?= $i + 1 ?>. <?= htmlspecialchars($a['question_text']) ?>
            </div>

            <div class="analysis-rows">
              <div>
                <span>Your Answer:</span>
                <strong><?= $a['selected_option'] ?: 'Not Answered' ?></strong>
              </div>

              <div>
                <span>Correct Answer:</span>
                <strong><?= $a['correct_option'] ?></strong>
              </div>

              <div class="<?= $a['is_correct'] ? 'correct' : 'wrong' ?>">
                <?= $a['is_correct'] ? '✔ Correct' : '✖ Wrong' ?>
              </div>
            </div>

          </div>
        <?php endforeach; ?>

      </div>

      <!-- BACK -->
      <div style="text-align:center;margin-top:36px">
        <a href="participant_home.php" class="btn small">
          ← Back to Dashboard
        </a>
      </div>

    </div>
  </div>
</div>

