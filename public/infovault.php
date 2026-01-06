<?php
// public/infovault.php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/premium_check.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// CRITICAL: Require premium access
requirePremium();

require_once __DIR__ . '/../includes/header_dashboard.php';
?>

<!-- Ensure dashboard background behavior -->
<script>
  document.body.classList.add('dashboard-page');
</script>

<!-- Background -->
<div class="dashboard-bg" aria-hidden="true" style="background-image: url('assets/images/infovault-bg.jpeg');">
</div>

<link rel="stylesheet" href="assets/css/info.css?v=1">

<div class="collab-viewport">
  <div class="collab-hero">
    <div class="collab-card">

      <!-- ACTION CARDS GRID -->
      <div class="collab-actions-grid">

        <!-- FILE VAULT -->
        <a class="action-card" href="infovault_files.php">
          <div class="action-icon">
            <img src="assets/images/file-vault-icon.png" alt="File Vault">
          </div>
          <div class="action-title">File Vault</div>
          <div class="action-sub">
            Upload, organize, and manage your study files securely
          </div>
        </a>

        <!-- FLASHCARDS -->
        <a class="action-card" href="infovault_flashcards.php">
          <div class="action-icon">
            <img src="assets/images/flashcards-icon.png" alt="Flashcards">
          </div>
          <div class="action-title">Flashcards</div>
          <div class="action-sub">
            Create flip cards to revise concepts faster
          </div>
        </a>

        <!-- MIND MAPS -->
        <a class="action-card" href="infovault_mindmaps.php">
          <div class="action-icon">
            <img src="assets/images/mindmap-icon.png" alt="Mind Maps">
          </div>
          <div class="action-title">Mind Maps</div>
          <div class="action-sub">
            Visualize topics with connected ideas and nodes
          </div>
        </a>

        <!-- REPORTS -->
        <a class="action-card" href="infovault_reports.php">
          <div class="action-icon">
            <img src="assets/images/report-icon.png" alt="Reports">
          </div>
          <div class="action-title">Reports & Insights</div>
          <div class="action-sub">
            Track your files, flashcards, and mind map usage
          </div>
        </a>

      </div>

    </div>
  </div>
</div>

<script src="assets/js/info_main.js" defer></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
