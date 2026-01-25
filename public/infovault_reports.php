<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    $_SESSION['after_login_redirect'] = 'public/infovault_reports.php';
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header_dashboard.php';

$uid = (int)$_SESSION['user_id'];

/* =========================
   FLASHCARDS
========================= */
$stmt = $pdo->prepare("SELECT COUNT(*) FROM flashcard_sets WHERE user_id = ?");
$stmt->execute([$uid]);
$totalSets = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("
  SELECT COUNT(f.id)
  FROM flashcards f
  JOIN flashcard_sets s ON s.id = f.set_id
  WHERE s.user_id = ?
");
$stmt->execute([$uid]);
$totalCards = (int)$stmt->fetchColumn();

/* =========================
   MINDMAPS
========================= */
$stmt = $pdo->prepare("SELECT COUNT(*) FROM mindmaps WHERE user_id = ?");
$stmt->execute([$uid]);
$totalMindmaps = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("
  SELECT COUNT(n.id)
  FROM mindmap_nodes n
  JOIN mindmaps m ON m.id = n.mindmap_id
  WHERE m.user_id = ?
");
$stmt->execute([$uid]);
$totalNodes = (int)$stmt->fetchColumn();

/* =========================
   INFOVAULT FILES
========================= */
$stmt = $pdo->prepare("
  SELECT COUNT(*) FROM infovault_files WHERE user_id = ?
");
$stmt->execute([$uid]);
$totalFiles = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("
  SELECT COALESCE(SUM(size_bytes),0)
  FROM infovault_files WHERE user_id = ?
");
$stmt->execute([$uid]);
$totalBytes = (int)$stmt->fetchColumn();
$totalMB = round($totalBytes / (1024 * 1024), 2);

$stmt = $pdo->prepare("
  SELECT file_name, uploaded_at
  FROM infovault_files
  WHERE user_id = ?
  ORDER BY uploaded_at DESC
  LIMIT 1
");
$stmt->execute([$uid]);
$recentFile = $stmt->fetch(PDO::FETCH_ASSOC);

/* =========================
   RECENT ACTIVITY
========================= */
$stmt = $pdo->prepare("
  SELECT 'Flashcard Set' AS type, title, created_at
  FROM flashcard_sets WHERE user_id = ?
  UNION ALL
  SELECT 'Mindmap', title, created_at
  FROM mindmaps WHERE user_id = ?
  ORDER BY created_at DESC
  LIMIT 1
");
$stmt->execute([$uid, $uid]);
$recentActivity = $stmt->fetch(PDO::FETCH_ASSOC);

/* =========================
   INSIGHTS
========================= */
if ($totalCards > $totalNodes) {
    $learningStyle = "Flashcard-Oriented Learner";
    $styleMsg = "You prefer structured revision using flashcards.";
} elseif ($totalNodes > $totalCards) {
    $learningStyle = "Visual Thinker";
    $styleMsg = "You prefer organizing ideas visually using mindmaps.";
} else {
    $learningStyle = "Balanced Learner";
    $styleMsg = "You balance both visual mapping and revision.";
}

$totalUsage = $totalSets + $totalMindmaps + $totalFiles;

if ($totalUsage === 0) {
    $engagement = "Low";
} elseif ($totalUsage <= 5) {
    $engagement = "Moderate";
} else {
    $engagement = "High";
}

if ($totalMB > 500) {
    $storageInsight = "Heavy Storage User";
} elseif ($totalMB > 50) {
    $storageInsight = "Moderate Storage User";
} else {
    $storageInsight = "Light Storage User";
}
?>

<link rel="stylesheet" href="assets/css/collab.css">
<script>
  document.body.classList.add('dashboard-page');
</script>

<!-- BACKGROUND -->
<div class="dashboard-bg" aria-hidden="true"
     style="background-image:url('assets/images/infovault_bg.jpg');
            background-size:cover;
            background-position:center;">
  <div class="dashboard-bg-overlay"></div>
</div>

<div class="collab-viewport">
<div class="collab-hero">
<div class="collab-card" style="max-width:1000px;margin:auto;">

  <!-- HEADER -->
  <div class="collab-card-head" style="align-items:center;gap:16px;">
    <img src="assets/images/report-icon.png"
         class="collab-logo"
         style="width:64px;height:64px;border-radius:14px;">
    <div>
      <h1 style="margin:0;">InfoVault Report</h1>
      <p class="lead" style="margin-top:6px;">
        A clear summary of your personal learning and storage activity.
      </p>
    </div>
  </div>

  <!-- METRICS -->
  <div class="card-grid"
       style="margin-top:26px;
              grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">

    <div class="card glass-card">
      <h3>Flashcard Sets</h3>
      <p class="metric"><?= $totalSets ?></p>
    </div>

    <div class="card glass-card">
      <h3>Total Flashcards</h3>
      <p class="metric"><?= $totalCards ?></p>
    </div>

    <div class="card glass-card">
      <h3>Mindmaps</h3>
      <p class="metric"><?= $totalMindmaps ?></p>
    </div>

    <div class="card glass-card">
      <h3>Mindmap Nodes</h3>
      <p class="metric"><?= $totalNodes ?></p>
    </div>

    <div class="card glass-card">
      <h3>Files Stored</h3>
      <p class="metric"><?= $totalFiles ?></p>
    </div>

    <div class="card glass-card">
      <h3>Storage Used</h3>
      <p class="metric"><?= $totalMB ?> MB</p>
    </div>

  </div>

  <!-- INSIGHTS -->
  <section style="margin-top:34px;">
    <h2 class="section-title">Insights</h2>

    <div class="card-grid"
         style="grid-template-columns:repeat(auto-fit,minmax(260px,1fr));">

      <div class="card glass-card">
        <h4>🧠 Learning Style</h4>
        <p class="highlight"><?= htmlspecialchars($learningStyle) ?></p>
        <p class="muted"><?= htmlspecialchars($styleMsg) ?></p>
      </div>

      <div class="card glass-card">
        <h4>📊 Engagement Level</h4>
        <p class="highlight"><?= $engagement ?></p>
        <p class="muted">Based on overall InfoVault usage.</p>
      </div>

      <div class="card glass-card">
        <h4>📁 Storage Behavior</h4>
        <p class="highlight"><?= $storageInsight ?></p>
        <p class="muted">Based on files and storage size.</p>
      </div>

      <div class="card glass-card">
        <h4>🕒 Recent Activity</h4>
        <?php if ($recentActivity): ?>
          <p class="highlight">
            <?= htmlspecialchars($recentActivity['title']) ?>
          </p>
          <p class="muted">
            <?= htmlspecialchars($recentActivity['type']) ?> ·
            <?= date('d M Y, H:i', strtotime($recentActivity['created_at'])) ?>
          </p>
        <?php else: ?>
          <p class="muted">No activity yet</p>
        <?php endif; ?>
      </div>

      <div class="card glass-card">
        <h4>📂 Recent File Upload</h4>
        <?php if ($recentFile): ?>
          <p class="highlight">
            <?= htmlspecialchars($recentFile['file_name']) ?>
          </p>
          <p class="muted">
            Uploaded on <?= date('d M Y, H:i', strtotime($recentFile['uploaded_at'])) ?>
          </p>
        <?php else: ?>
          <p class="muted">No files uploaded yet</p>
        <?php endif; ?>
      </div>

    </div>
  </section>

  <!-- BACK -->
  <div style="margin-top:28px;">
    <a href="infovault.php" class="btn primary">
      ← Back to InfoVault
    </a>
  </div>

</div>
</div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
