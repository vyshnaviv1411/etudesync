<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/premium_check.php';

$body_class = 'dashboard-page accessarena-page edit-question-page';

$disable_dashboard_bg = true;

require_once __DIR__ . '/../../../includes/header_dashboard.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}
requirePremium();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid question');
}

$qid = (int)$_GET['id'];
$uid = $_SESSION['user_id'];

/* Fetch question + quiz */
$stmt = $pdo->prepare("
  SELECT q.*, quiz.id AS quiz_id, quiz.title
  FROM accessarena_questions q
  JOIN accessarena_quizzes quiz ON quiz.id = q.quiz_id
  WHERE q.id = ? AND quiz.creator_id = ? AND quiz.status = 'draft'
");
$stmt->execute([$qid, $uid]);
$question = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$question) {
    die('Question not editable');
}

/* Update */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("
      UPDATE accessarena_questions
      SET question_text=?, option_a=?, option_b=?, option_c=?, option_d=?, correct_option=?
      WHERE id=?
    ");
    $stmt->execute([
        $_POST['question'],
        $_POST['option_a'],
        $_POST['option_b'],
        $_POST['option_c'],
        $_POST['option_d'],
        $_POST['correct_option'],
        $qid
    ]);

    header("Location: add_questions.php?quiz_id=".$question['quiz_id']."&updated=1");
    exit;
}
?>

<!-- BACKGROUND -->
<div class="dashboard-bg"
     style="background-image:url('../../assets/images/infovault_bg.jpg')"></div>

<link rel="stylesheet" href="../../assets/css/info.css">
<link rel="stylesheet" href="../../assets/css/accessarena.css">

<div class="collab-card accessarena-card questions-layout">

  <div class="collab-card-head">
    <h1>Edit Question</h1>
    <p class="lead">
      Quiz: <strong><?= htmlspecialchars($question['title']) ?></strong>
    </p>
  </div>

  <div class="questions-split">

    <!-- LEFT -->
    <div class="questions-left edit-card">
      <h2>Update Question</h2>
      <p class="sub">Modify the question details below</p>

      <form method="post" class="accessarena-form">

        <label>Question</label>
        <textarea name="question" required><?= htmlspecialchars($question['question_text']) ?></textarea>

        <div class="options-grid">
          <div>
            <label>Option A</label>
            <input name="option_a" value="<?= htmlspecialchars($question['option_a']) ?>" required>
          </div>
          <div>
            <label>Option B</label>
            <input name="option_b" value="<?= htmlspecialchars($question['option_b']) ?>" required>
          </div>
          <div>
            <label>Option C</label>
            <input name="option_c" value="<?= htmlspecialchars($question['option_c']) ?>">
          </div>
          <div>
            <label>Option D</label>
            <input name="option_d" value="<?= htmlspecialchars($question['option_d']) ?>">
          </div>
        </div>

        <label>Correct Answer</label>
        <select name="correct_option">
          <?php foreach(['A','B','C','D'] as $opt): ?>
            <option value="<?= $opt ?>" <?= $opt==$question['correct_option']?'selected':'' ?>>
              <?= $opt ?>
            </option>
          <?php endforeach; ?>
        </select>

        <button class="btn primary" style="margin-top:16px;">
          💾 Save Changes
        </button>

        <a href="add_questions.php?quiz_id=<?= $question['quiz_id'] ?>"
           class="btn small" style="margin-top:10px;">
          ← Back to Questions
        </a>

      </form>
    </div>


  </div>
</div>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
