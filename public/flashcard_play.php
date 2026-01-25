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

$stmt = $pdo->prepare("
  SELECT f.question, f.answer
  FROM flashcards f
  JOIN flashcard_sets s ON s.id = f.set_id
  WHERE f.set_id = ? AND s.user_id = ?
");
$stmt->execute([$set_id, $uid]);
$cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$cards) {
  echo "No flashcards";
  exit;
}
?>

<!-- PAGE MARK -->
<script>
  document.body.classList.add('dashboard-page','flashcard-play-page');
</script>

<!-- SAME BACKGROUND AS OTHERS -->
<div class="dashboard-bg" style="
  background-image:url('assets/images/infovault_bg.jpg');
  background-size:cover;
  background-position:center;">
  <div class="dashboard-bg-overlay"></div>
</div>

<link rel="stylesheet" href="assets/css/collab.css">

<!-- PAGE CONTENT -->
<div class="collab-viewport">
<div class="collab-hero">

  <!-- CARD WRAPPER -->
  <div class="flashcard-wrapper">

    <div class="flashcard-3d" id="card" onclick="flip()">
      <div class="flashcard-face flashcard-front" id="card-front"></div>
      <div class="flashcard-face flashcard-back" id="card-back"></div>
    </div>

    <!-- CONTROLS -->
    <div class="flashcard-controls">
      <button class="btn small outline" onclick="prev()">← Prev</button>
      <button class="btn small" onclick="flip()">Flip</button>
      <button class="btn small outline" onclick="next()">Next →</button>
    </div>

    <!-- BACK -->
    <div class="flashcard-back-btn">
      <a href="flashcard_set.php?set_id=<?= $set_id ?>"
         class="btn primary small">
        ← Back
      </a>
    </div>

  </div>

</div>
</div>

<!-- STYLES -->
<style>
/* ===============================
   FLASHCARD PLAY PAGE
================================ */
.flashcard-play-page .flashcard-wrapper{
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:22px;
}

.flashcard-3d{
  width:600px;
  height:320px;
  position:relative;
  transform-style:preserve-3d;
  transition:transform 0.6s ease;
  cursor:pointer;
}

.flashcard-3d.flipped{
  transform:rotateY(180deg);
}

.flashcard-face{
  position:absolute;
  inset:0;
  display:flex;
  align-items:center;
  justify-content:center;
  padding:32px;
  font-size:1.25rem;
  line-height:1.6;
  text-align:center;
  backface-visibility:hidden;
  border-radius:18px;
  background:rgba(15,20,30,0.55);
  backdrop-filter:blur(14px);
  border:1px solid rgba(255,255,255,0.1);
  box-shadow:0 30px 80px rgba(0,0,0,0.35);
}

.flashcard-back{
  transform:rotateY(180deg);
}

.flashcard-controls{
  display:flex;
  gap:12px;
  justify-content:center;
}

.flashcard-back-btn{
  margin-top:6px;
}
</style>

<!-- SCRIPT -->
<script>
const cards = <?= json_encode($cards) ?>;
let i = 0;
let flipped = false;

const card = document.getElementById('card');
const front = document.getElementById('card-front');
const back  = document.getElementById('card-back');

function render(){
  front.innerText = cards[i].question;
  back.innerText  = cards[i].answer;
}

function flip(){
  flipped = !flipped;
  card.classList.toggle('flipped', flipped);
}

function next(){
  i = (i + 1) % cards.length;
  flipped = false;
  card.classList.remove('flipped');
  render();
}

function prev(){
  i = (i - 1 + cards.length) % cards.length;
  flipped = false;
  card.classList.remove('flipped');
  render();
}

render();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
