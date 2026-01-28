<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/premium_check.php';

$body_class = 'dashboard-page accessarena-page';
$disable_dashboard_bg = true;

/* ---------------------------
   AUTH & PREMIUM CHECK
---------------------------- */
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}
requirePremium();

$user_id = (int)$_SESSION['user_id'];
$error = '';

/* ---------------------------
   GET QUIZ CODE (GET or POST)
---------------------------- */
$quiz_code = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz_code = trim($_POST['quiz_code'] ?? '');
} elseif (isset($_GET['code'])) {
    $quiz_code = trim($_GET['code']);
}

/* ---------------------------
   JOIN LOGIC (COMMON)
---------------------------- */
if ($quiz_code !== '') {

    /* Fetch published quiz */
    $stmt = $pdo->prepare("
        SELECT id
        FROM accessarena_quizzes
        WHERE quiz_code = ? AND status = 'published'
        LIMIT 1
    ");
    $stmt->execute([$quiz_code]);
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$quiz) {
        $error = 'Invalid or unpublished quiz code.';
    } else {

        $quiz_id = (int)$quiz['id'];

        /* Ensure participant exists */
        $stmt = $pdo->prepare("
            SELECT id
            FROM accessarena_participants
            WHERE quiz_id = ? AND user_id = ?
        ");
        $stmt->execute([$quiz_id, $user_id]);

        if (!$stmt->fetch()) {
            $pdo->prepare("
                INSERT INTO accessarena_participants (quiz_id, user_id)
                VALUES (?, ?)
            ")->execute([$quiz_id, $user_id]);
        }

        /* ✅ DIRECT TO INSTRUCTIONS */
        header("Location: instructions.php?code=" . urlencode($quiz_code));
        exit;
    }
}

/* ---------------------------
   PAGE UI (ONLY IF ERROR / MANUAL JOIN)
---------------------------- */
require_once __DIR__ . '/../../../includes/header_dashboard.php';
?>

<script>
  document.body.classList.add('dashboard-page');
</script>

<!-- Background -->
<div class="dashboard-bg" aria-hidden="true"
     style="background-image: url('../../assets/images/infovault_bg.jpg');">
</div>

<link rel="stylesheet" href="../../assets/css/info.css">
<link rel="stylesheet" href="../../assets/css/accessarena.css?v=1">

<div class="collab-viewport">
  <div class="collab-hero">

    <div class="collab-card accessarena-card" style="max-width:520px;">

      <div class="accessarena-header">
        <h2>Join Quiz</h2>
        <p>Enter the quiz code provided by your mentor</p>
      </div>

      <?php if ($error): ?>
        <p style="color:#ff6b6b;text-align:center;margin-bottom:12px;">
          <?= htmlspecialchars($error) ?>
        </p>
      <?php endif; ?>

      <form method="POST" class="accessarena-form">

        <label>Quiz Code</label>
        <input type="text"
               name="quiz_code"
               placeholder="Enter quiz code"
               required
               autocomplete="off">

        <div style="text-align:center;margin-top:22px;">
          <button type="submit" class="btn primary">
            Join Quiz
          </button>
        </div>

        <div style="margin-top:16px;text-align:center;">
          <a href="participant_home.php" class="btn small">
            ← Back to Dashboard
          </a>
        </div>

      </form>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
