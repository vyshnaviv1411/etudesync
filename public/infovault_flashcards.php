<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (empty($_SESSION['user_id'])) {
    $_SESSION['after_login_redirect'] = 'public/infovault_flashcards.php';
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header_dashboard.php';

$uid = (int)$_SESSION['user_id'];

/* Fetch flashcard sets */
$stmt = $pdo->prepare("
  SELECT id, title, created_at
  FROM flashcard_sets
  WHERE user_id = ?
  ORDER BY created_at DESC
");
$stmt->execute([$uid]);
$sets = $stmt->fetchAll(PDO::FETCH_ASSOC);

function e($s) {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>

<script>document.body.classList.add('dashboard-page');</script>

<!-- Background -->
<div class="dashboard-bg" style="
  background-image:url('assets/images/infovault_bg.jpg');
  background-size:cover;
  background-position:center;
">
  <div class="dashboard-bg-overlay"></div>
</div>

<link rel="stylesheet" href="assets/css/collab.css">

<div class="collab-viewport">
  <div class="collab-hero">
    <div class="collab-card" style="max-width:760px;margin:auto;">

      <!-- HEADER -->
      <div class="collab-card-head">
        <img src="assets/images/flashcards-icon.png" class="collab-logo">
        <h1>Flashcards</h1>
        <p class="lead">
          Create flashcard sets and revise concepts effectively.
        </p>
      </div>

      <!-- CREATE SET -->
      <form method="POST" action="api/create_flashcard_set.php">
        <label style="font-weight:800;margin-bottom:6px;display:block">
          Flashcard Set Name
        </label>

        <div style="display:flex;gap:12px;">
          <input
            type="text"
            name="title"
            required
            placeholder="e.g. DBMS - SET 01"
            style="
              flex:1;
              padding:12px;
              border-radius:10px;
              border:1px solid rgba(255,255,255,0.08);
              background:rgba(255,255,255,0.02);
              color:#fff;
            "
          />

          <button class="btn primary" style="padding:12px 18px;">
            Create Set
          </button>
        </div>
      </form>

      <!-- SET LIST -->
      <div style="margin-top:28px;">
        <h3>Your Flashcard Sets</h3>

        <?php if (!$sets): ?>
          <div class="small-muted">No flashcard sets created yet.</div>
        <?php endif; ?>

        <?php foreach ($sets as $s): ?>
          <div class="glass-card"
               style="padding:12px;margin-top:10px;
               display:flex;justify-content:space-between;align-items:center;">
            <div>
              <strong><?= e($s['title']) ?></strong><br>
              <span class="small-muted">
                Created on <?= date('d M Y', strtotime($s['created_at'])) ?>
              </span>
            </div>

            <div style="display:flex; gap:8px; align-items:center;">

  <!-- OPEN -->
  <a href="flashcard_set.php?set_id=<?= (int)$s['id'] ?>"
     class="btn small">
    Open
  </a>

  <!-- PLAY -->
  <a href="flashcard_play.php?set_id=<?= (int)$s['id'] ?>"
     class="btn small"
     title="Play flashcards">
    ▶ Play
  </a>

  <!-- DELETE -->
  <form method="POST"
        action="api/delete_flashcard_set.php"
        onsubmit="return confirm('Delete this flashcard set permanently?');"
        style="margin:0;">
    <input type="hidden" name="set_id" value="<?= (int)$s['id'] ?>">
    <button type="submit" class="btn small danger">
      🗑 Delete
    </button>
  </form>

</div>

          </div>
        <?php endforeach; ?>
      </div>

      <!-- BACK -->
      <div style="margin-top:22px;">
        <a href="infovault.php" class="btn primary">
          ← Back to InfoVault
        </a>
      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
