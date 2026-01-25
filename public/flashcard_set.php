<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (empty($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header_dashboard.php';

$uid = (int)$_SESSION['user_id'];
$set_id = (int)($_GET['set_id'] ?? 0);

/* Validate ownership */
$stmt = $pdo->prepare("
  SELECT * FROM flashcard_sets
  WHERE id = ? AND user_id = ?
");
$stmt->execute([$set_id, $uid]);
$set = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$set) {
  echo "Invalid set";
  exit;
}

/* Fetch cards */
$stmt = $pdo->prepare("
  SELECT * FROM flashcards
  WHERE set_id = ?
  ORDER BY created_at DESC
");
$stmt->execute([$set_id]);
$cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

function e($s){
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>

<!-- MARK PAGE -->
<script>
  document.body.classList.add('dashboard-page','flashcard-page');
</script>

<!-- BACKGROUND -->
<div class="dashboard-bg" style="
  background-image:url('assets/images/infovault_bg.jpg');
  background-size:cover;">
  <div class="dashboard-bg-overlay"></div>
</div>

<link rel="stylesheet" href="assets/css/collab.css">

<div class="collab-viewport">
<div class="collab-hero">
<div class="collab-card" style="max-width:900px;">

  <!-- HEADER -->
  <div class="collab-card-head">
    <h1><?= e($set['title']) ?></h1>
    <p class="lead">Add and review your flashcards</p>
  </div>

  <!-- ADD CARD -->
  <form method="POST" action="api/add_flashcard.php">
    <input type="hidden" name="set_id" value="<?= $set_id ?>">

    <label>Question</label>
    <textarea name="question" required class="glass-input"></textarea>

    <label style="margin-top:10px;">Answer</label>
    <textarea name="answer" required class="glass-input"></textarea>

    <button class="btn primary" style="margin-top:14px;">
     + Add Flashcard
    </button>
  </form>

  <!-- ACTION BAR -->
  <div style="
    margin-top:20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
  ">
    <a href="infovault_flashcards.php" class="btn primary small">
      ← Back
    </a>

    <a href="flashcard_play.php?set_id=<?= $set_id ?>"
       class="btn primary small">
      ✔ Done
    </a>
  </div>

  <!-- FLASHCARDS -->
  <div style="margin-top:28px;">
    <h3>Flashcards</h3>

    <?php if (!$cards): ?>
      <div class="small-muted">No cards added yet</div>
    <?php endif; ?>

    <?php foreach ($cards as $c): ?>
      <div class="glass-card" style="
        margin-top:12px;
        display:flex;
        justify-content:space-between;
        gap:16px;
      ">

        <!-- CONTENT -->
        <div>
          <div style="margin-bottom:6px;">
            <strong>Q:</strong> <?= e($c['question']) ?>
          </div>
          <div>
            <strong>A:</strong> <?= e($c['answer']) ?>
          </div>
        </div>

        <!-- CARD ACTIONS -->
        <div style="display:flex; gap:8px; align-items:flex-start;">

          <a href="edit_flashcard.php?id=<?= (int)$c['id'] ?>&set_id=<?= $set_id ?>"
             class="btn small">
            ✏ Edit
          </a>

          <form method="POST"
                action="api/delete_flashcard.php"
                onsubmit="return confirm('Delete this flashcard?');"
                style="margin:0;">
            <input type="hidden" name="card_id" value="<?= (int)$c['id'] ?>">
            <input type="hidden" name="set_id" value="<?= $set_id ?>">
            <button class="btn small danger">
              🗑 Delete
            </button>
          </form>

        </div>
      </div>
    <?php endforeach; ?>
  </div>

</div>
</div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
