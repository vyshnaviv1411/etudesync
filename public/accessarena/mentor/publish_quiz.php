<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/premium_check.php';

$body_class = 'dashboard-page accessarena-page';
$disable_dashboard_bg = true;

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}
requirePremium();

$uid = $_SESSION['user_id'];

/* Fetch ONLY published quizzes */
$stmt = $pdo->prepare("
  SELECT id, title, quiz_code, total_questions, time_limit, created_at
  FROM accessarena_quizzes
  WHERE creator_id = ? AND status = 'published'
  ORDER BY created_at DESC
");
$stmt->execute([$uid]);
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../../../includes/header_dashboard.php';
?>

<!-- Background -->
<div class="dashboard-bg"
     aria-hidden="true"
     style="background-image:url('../../assets/images/infovault_bg.jpg');">
</div>

<link rel="stylesheet" href="../../assets/css/accessarena.css">

<div class="collab-viewport">
  <div class="collab-hero">

    <!-- MAIN GLASS CONTAINER -->
    <div class="collab-card accessarena-card publish-wrapper">

      <!-- HEADER -->
      <div class="accessarena-header">
        <h1 class="accessarena-title">Published Quizzes</h1>
        <p class="accessarena-subtitle">
          Share quiz codes or push quizzes directly to CollabSphere rooms
        </p>
      </div>

      <?php if (!$quizzes): ?>
        <div class="publish-empty">
          <p>No quizzes published yet.</p>
          <a href="quiz_history.php" class="btn primary">
            Go to Quiz History
          </a>
        </div>
      <?php else: ?>

        <div class="publish-grid">
          <?php foreach ($quizzes as $q): ?>
            <div class="publish-card">

              <h3><?= htmlspecialchars($q['title']) ?></h3>

              <div class="publish-row">
                <span>Quiz Code</span>
                <strong><?= htmlspecialchars($q['quiz_code']) ?></strong>
              </div>

              <div class="publish-row">
                <span>Questions</span>
                <span><?= (int)$q['total_questions'] ?></span>
              </div>

              <div class="publish-row">
                <span>Time Limit</span>
                <span><?= $q['time_limit'] ? $q['time_limit'].' min' : '—' ?></span>
              </div>

              <div class="publish-actions">

                <!-- COPY CODE -->
                <button type="button"
                        class="btn small copy-quiz-btn"
                        data-code="<?= htmlspecialchars($q['quiz_code']) ?>">
                  📋 Copy Code
                </button>

                <!-- SHARE TO ROOM (REAL REDIRECT) -->
    <button
  type="button"
  class="btn small primary share-room-btn"
  data-quiz="<?= (int)$q['id'] ?>">
  🔗 Share to Room
</button>




              </div>

            </div>
          <?php endforeach; ?>
        </div>

      <?php endif; ?>

      <div style="text-align:center;margin-top:30px">
        <a href="mentor_home.php" class="btn small">
          ← Back to Dashboard
        </a>
      </div>

    </div>
  </div>
</div>

<!-- COPY CODE SCRIPT -->
<script>
document.addEventListener('click', function (e) {
  const btn = e.target.closest('.copy-quiz-btn');
  if (!btn) return;

  const code = btn.dataset.code;
  if (!code) return;

  navigator.clipboard.writeText(code).then(() => {
    const original = btn.innerText;
    btn.innerText = '✅ Copied';
    setTimeout(() => {
      btn.innerText = original;
    }, 1500);
  }).catch(() => {
    alert('Failed to copy quiz code');
  });
});
</script>
<script>
document.addEventListener('click', function (e) {
  const btn = e.target.closest('.share-room-btn');
  if (!btn) return;

  const roomId = prompt('Enter ROOM ID:');
  if (!roomId) return;

  const roomCode = prompt('Enter ROOM CODE:');
  if (!roomCode) return;

  // ✅ DIRECT REDIRECT — NO BACKEND TOUCH
  window.location.href =
    `/etudesync/public/room.php?room_id=${encodeURIComponent(roomId)}&code=${encodeURIComponent(roomCode)}`;
});
</script>


<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
