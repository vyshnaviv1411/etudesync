<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/premium_check.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}
requirePremium();

$mentor_id = (int)$_SESSION['user_id'];
$quiz_id   = (int)($_GET['quiz_id'] ?? 0);

if (!$quiz_id) die('Quiz not selected');

/* =========================
   FETCH QUIZ (SECURE)
========================= */
$qStmt = $pdo->prepare("
  SELECT id, title, total_questions
  FROM accessarena_quizzes
  WHERE id = ? AND creator_id = ?
");
$qStmt->execute([$quiz_id, $mentor_id]);
$quiz = $qStmt->fetch(PDO::FETCH_ASSOC);

if (!$quiz) die('Unauthorized');

/* =========================
   PARTICIPANTS
========================= */
$pStmt = $pdo->prepare("
  SELECT 
    u.username,
    p.id AS participant_id,
    p.score
  FROM accessarena_participants p
  JOIN users u ON u.id = p.user_id
  WHERE p.quiz_id = ? AND p.completed = 1
");
$pStmt->execute([$quiz_id]);
$participants = $pStmt->fetchAll(PDO::FETCH_ASSOC);

$totalParticipants = count($participants);

$scores = array_column($participants, 'score');
$highest = $scores ? max($scores) : 0;
$lowest  = $scores ? min($scores) : 0;
$average = $scores ? round(array_sum($scores) / count($scores), 2) : 0;

/* =========================
   SCORE DISTRIBUTION
========================= */
$ranges = [
  '0–25%'  => 0,
  '26–50%' => 0,
  '51–75%' => 0,
  '76–100%' => 0
];

foreach ($participants as $p) {
  $percent = $quiz['total_questions']
    ? ($p['score'] / $quiz['total_questions']) * 100
    : 0;

  if ($percent <= 25) $ranges['0–25%']++;
  elseif ($percent <= 50) $ranges['26–50%']++;
  elseif ($percent <= 75) $ranges['51–75%']++;
  else $ranges['76–100%']++;
}

/* =========================
   QUESTION ANALYSIS
========================= */
$qAnalysisStmt = $pdo->prepare("
  SELECT 
    q.id,
    q.question_text,
    SUM(a.is_correct = 1) AS correct_count,
    SUM(a.is_correct = 0) AS wrong_count
  FROM accessarena_questions q
  LEFT JOIN accessarena_answers a ON a.question_id = q.id
  WHERE q.quiz_id = ?
  GROUP BY q.id
");
$qAnalysisStmt->execute([$quiz_id]);
$questions = $qAnalysisStmt->fetchAll(PDO::FETCH_ASSOC);

/* Most difficult question */
usort($questions, fn($a, $b) => ($a['correct_count'] ?? 0) <=> ($b['correct_count'] ?? 0));
$hardest = $questions[0] ?? null;

require_once __DIR__ . '/../../../includes/header_dashboard.php';
?>

<div class="dashboard-bg" style="background-image:url('../../assets/images/infovault_bg.jpg')"></div>
<link rel="stylesheet" href="../../assets/css/accessarena.css">

<div class="collab-viewport">
  <div class="collab-hero">

    <div class="collab-card accessarena-card mentor-analysis">

      <div class="accessarena-header">
        <h2><?= htmlspecialchars($quiz['title']) ?> – Mentor Analysis</h2>
        <p>Complete performance breakdown</p>
      </div>

      <!-- OVERVIEW -->
      <div class="quiz-summary-grid">
        <div class="summary-box"><span>Participants</span><strong><?= $totalParticipants ?></strong></div>
        <div class="summary-box"><span>Average Score</span><strong><?= $average ?></strong></div>
        <div class="summary-box"><span>Highest</span><strong><?= $highest ?></strong></div>
        <div class="summary-box"><span>Lowest</span><strong><?= $lowest ?></strong></div>
      </div>

      <!-- DISTRIBUTION -->
      <h3>Score Distribution</h3>
      <div class="analysis-distribution">
        <?php foreach ($ranges as $label => $count): ?>
          <div class="dist-box">
            <strong><?= $count ?></strong>
            <span><?= $label ?></span>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- PARTICIPANTS -->
      <h3>Participant Performance</h3>
      <div class="analysis-table">
        <div class="analysis-row head">
          <span>Name</span>
          <span>Score</span>
          <span>Accuracy</span>
        </div>
        <?php foreach ($participants as $p):
          $acc = $quiz['total_questions']
            ? round(($p['score'] / $quiz['total_questions']) * 100)
            : 0;
        ?>
        <div class="analysis-row">
          <span><?= htmlspecialchars($p['username']) ?></span>
          <span><?= $p['score'] ?>/<?= $quiz['total_questions'] ?></span>
          <span><?= $acc ?>%</span>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- QUESTIONS -->
      <h3>Question-wise Analysis</h3>
      <?php foreach ($questions as $i => $q): 
        $difficulty =
          ($q['correct_count'] >= $totalParticipants * 0.7) ? 'easy' :
          (($q['correct_count'] >= $totalParticipants * 0.4) ? 'medium' : 'hard');
      ?>
        <div class="analysis-card">
          <strong>Q<?= $i + 1 ?>. <?= htmlspecialchars($q['question_text']) ?></strong>
          <div class="analysis-rows">
            <div class="correct">✔ <?= $q['correct_count'] ?? 0 ?></div>
            <div class="wrong">✖ <?= $q['wrong_count'] ?? 0 ?></div>
            <div class="tag <?= $difficulty ?>"><?= ucfirst($difficulty) ?></div>
          </div>
        </div>
      <?php endforeach; ?>

      <div class="center-back">
        <a href="mentor_results.php" class="btn small">← Back to Results</a>
      </div>

    </div>
  </div>
</div>


