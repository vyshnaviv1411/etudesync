<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/premium_check.php';

$body_class = 'dashboard-page accessarena-page';
$disable_dashboard_bg = true;

require_once __DIR__ . '/../../../includes/header_dashboard.php';
// Auth
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

requirePremium();

// Validate quiz_id
if (!isset($_GET['quiz_id']) || !is_numeric($_GET['quiz_id'])) {
    header('Location: no_quiz.php');
    exit;
}


$quiz_id = (int) $_GET['quiz_id'];
$user_id = $_SESSION['user_id'];

// Ensure quiz belongs to mentor
$stmt = $pdo->prepare("
    SELECT * FROM accessarena_quizzes
    WHERE id = ? AND creator_id = ? AND status = 'draft'
");
$stmt->execute([$quiz_id, $user_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    die('Quiz not found or already published');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $question = trim($_POST['question']);
    $a = trim($_POST['option_a']);
    $b = trim($_POST['option_b']);
    $c = trim($_POST['option_c'] ?? '');
    $d = trim($_POST['option_d'] ?? '');
    $correct = $_POST['correct_option'];

    if ($question && $a && $b && in_array($correct, ['A','B','C','D'])) {

        $stmt = $pdo->prepare("
            INSERT INTO accessarena_questions
            (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$quiz_id, $question, $a, $b, $c, $d, $correct]);

        // Update total_questions
        $pdo->prepare("
            UPDATE accessarena_quizzes
            SET total_questions = total_questions + 1
            WHERE id = ?
        ")->execute([$quiz_id]);

        $success = "Question added successfully!";
    } else {
        $error = "Please fill required fields correctly.";
    }
}
// Fetch questions for this quiz
$qStmt = $pdo->prepare("
  SELECT *
  FROM accessarena_questions
  WHERE quiz_id = ?
  ORDER BY id DESC
");
$qStmt->execute([$quiz_id]);
$questions = $qStmt->fetchAll(PDO::FETCH_ASSOC);

?>
<?php if (!empty($success)): ?>
  <div class="alert success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="alert error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>


<!-- BACKGROUND -->
<div class="dashboard-bg" aria-hidden="true"
     style="background-image: url('../../assets/images/infovault_bg.jpg');">
</div>

<link rel="stylesheet" href="../../assets/css/info.css">
<link rel="stylesheet" href="../../assets/css/accessarena.css?v=1">

<div class="collab-card accessarena-card questions-layout">

  <!-- HEADER -->
  <div class="collab-card-head">
    <h1>Add Questions</h1>
    <p class="lead">
      Quiz: <strong><?= htmlspecialchars($quiz['title']) ?></strong>
    </p>
  </div>

  <!-- SPLIT LAYOUT -->
  <div class="questions-split">

    <!-- LEFT : ADD QUESTION FORM -->
    <div class="questions-left">
      <form method="post" class="accessarena-form">

        <label>Question</label>
        <textarea name="question" required
          placeholder="Enter the question here"></textarea>

        <div class="options-grid">
          <div>
            <label>Option A</label>
            <input type="text" name="option_a" required>
          </div>

          <div>
            <label>Option B</label>
            <input type="text" name="option_b" required>
          </div>

          <div>
            <label>Option C</label>
            <input type="text" name="option_c">
          </div>

          <div>
            <label>Option D</label>
            <input type="text" name="option_d">
          </div>
        </div>

        <label>Correct Answer</label>
        <select name="correct_option" required>
          <option value="">Select correct option</option>
          <option value="A">A</option>
          <option value="B">B</option>
          <option value="C">C</option>
          <option value="D">D</option>
        </select>

        <button type="submit" class="btn primary" style="margin-top:14px;">
          ➕ Add Question
        </button>

      </form>
    </div>

    <!-- RIGHT : QUESTIONS LIST -->
    <div class="questions-right">

      <h3 style="margin-bottom:12px;">Questions Added</h3>

      <?php if ($questions): ?>
        <?php foreach ($questions as $q): ?>
          <div class="question-card">

            <div class="question-title">
              <?= htmlspecialchars($q['question_text']) ?>
            </div>

            <ul class="options-list">
              <li><b>A.</b> <?= htmlspecialchars($q['option_a']) ?></li>
              <li><b>B.</b> <?= htmlspecialchars($q['option_b']) ?></li>
              <?php if ($q['option_c']): ?>
                <li><b>C.</b> <?= htmlspecialchars($q['option_c']) ?></li>
              <?php endif; ?>
              <?php if ($q['option_d']): ?>
                <li><b>D.</b> <?= htmlspecialchars($q['option_d']) ?></li>
              <?php endif; ?>
            </ul>

            <div class="correct-answer">
              Correct Answer: <strong><?= $q['correct_option'] ?></strong>
            </div>

            <div class="question-actions">
              <a href="edit_question.php?id=<?= $q['id'] ?>" class="btn small">
                Edit
              </a>

              <a href="delete_question.php?id=<?= $q['id'] ?>&quiz_id=<?= $quiz_id ?>"
                 class="btn small danger"
                 onclick="return confirm('Delete this question?');">
                Delete
              </a>
            </div>

          </div>
        <?php endforeach; ?>

        <!-- PUBLISH BUTTON -->
        <a href="publish_quiz.php?quiz_id=<?= $quiz_id ?>"
           class="btn primary publish-btn">
          🚀 Publish Quiz
        </a>

      <?php else: ?>
        <div class="small-muted">No questions added yet.</div>
      <?php endif; ?>

    </div>

  </div>
</div>

<script src="../../assets/js/accessarena.js" defer></script>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
