<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (empty($_SESSION['user_id'])) {
  $_SESSION['after_login_redirect'] = 'public/infovault_mindmaps.php';
  header('Location: login.php');
  exit;
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header_dashboard.php';

$uid = (int)$_SESSION['user_id'];

/* Fetch mindmaps */
$stmt = $pdo->prepare("
  SELECT id, title, created_at
  FROM mindmaps
  WHERE user_id = ?
  ORDER BY created_at DESC
");
$stmt->execute([$uid]);
$mindmaps = $stmt->fetchAll(PDO::FETCH_ASSOC);

function e($s){
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>

<!-- MARK PAGE -->
<script>
  document.body.classList.add('dashboard-page','mindmap-page');
</script>

<!-- BACKGROUND -->
<div class="dashboard-bg" style="
  background-image:url('assets/images/infovault_bg.jpg');
  background-size:cover;
  background-position:center;">
  <div class="dashboard-bg-overlay"></div>
</div>

<link rel="stylesheet" href="assets/css/collab.css">

<div class="collab-viewport">
<div class="collab-hero">
<div class="collab-card" style="max-width:760px;margin:auto;">

  <!-- HEADER -->
  <div class="collab-card-head">
    <img src="assets/images/mindmap-icon.png" class="collab-logo">
    <h1>Mindmaps</h1>
    <p class="lead">
      Create visual diagrams to organize your ideas.
    </p>
  </div>

  <!-- CREATE MINDMAP -->
  <form method="POST" action="api/create_mindmap.php">
    <label style="font-weight:800;margin-bottom:6px;display:block">
      Mindmap Name
    </label>

    <div style="display:flex;gap:12px;">
      <input
        type="text"
        name="title"
        required
        placeholder="e.g. Stranger Things Characters"
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
        Create Mindmap
      </button>
    </div>
  </form>

  <!-- LIST -->
  <div style="margin-top:28px;">
    <h3>Your Mindmaps</h3>

    <?php if (!$mindmaps): ?>
      <div class="small-muted">No mindmaps created yet.</div>
    <?php endif; ?>

    <?php foreach ($mindmaps as $m): ?>
      <div class="glass-card"
           style="padding:12px;margin-top:10px;
           display:flex;justify-content:space-between;align-items:center;">
        <div>
          <strong><?= e($m['title']) ?></strong><br>
          <span class="small-muted">
            Created on <?= date('d M Y', strtotime($m['created_at'])) ?>
          </span>
        </div>

        <div style="display:flex;gap:8px;">
          <a href="mindmap_editor.php?mindmap_id=<?= (int)$m['id'] ?>"
             class="btn small">
            Open
          </a>

          <form method="POST"
                action="api/delete_mindmap.php"
                onsubmit="return confirm('Delete this mindmap permanently?');"
                style="margin:0;">
            <input type="hidden" name="mindmap_id" value="<?= (int)$m['id'] ?>">
            <button class="btn small danger">
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
