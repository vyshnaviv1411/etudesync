<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header_dashboard.php';

$uid     = (int)$_SESSION['user_id'];
$card_id = (int)($_GET['id'] ?? 0);
$set_id  = (int)($_GET['set_id'] ?? 0);

/* Validate flashcard ownership via set */
$stmt = $pdo->prepare("
  SELECT f.*
  FROM flashcards f
  JOIN flashcard_sets s ON s.id = f.set_id
  WHERE f.id = ? AND s.id = ? AND s.user_id = ?
");
$stmt->execute([$card_id, $set_id, $uid]);
$card = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$card) {
    echo "Invalid flashcard";
    exit;
}

function e($s){
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>

<script>
  document.body.classList.add('dashboard-page','flashcard-page');
</script>

<div class="dashboard-bg" style="
  background-image:url('assets/images/infovault_bg.jpg');
  background-size:cover;">
  <div class="dashboard-bg-overlay"></div>
</div>

<link rel="stylesheet" href="assets/css/collab.css">

<div class="collab-viewport">
<div class="collab-hero">
<div class="collab-card" style="max-width:800px;">

  <div class="collab-card-head">
    <h1>Edit Flashcard</h1>
    <p class="lead">Update your question and answer</p>
  </div>

  <!-- EDIT FORM -->
  <form method="POST" action="api/update_flashcard.php">
    <input type="hidden" name="card_id" value="<?= $card_id ?>">
    <input type="hidden" name="set_id" value="<?= $set_id ?>">

    <label>Question</label>
    <textarea name="question"
              class="glass-input"
              required><?= e($card['question']) ?></textarea>

    <label style="margin-top:10px;">Answer</label>
    <textarea name="answer"
              class="glass-input"
              required><?= e($card['answer']) ?></textarea>

    <div style="margin-top:18px; display:flex; gap:12px;">
      <button class="btn primary">
        Save Changes
      </button>

      <a href="flashcard_set.php?set_id=<?= $set_id ?>"
         class="btn primary small">
        Cancel
      </a>
    </div>
  </form>

</div>
</div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
