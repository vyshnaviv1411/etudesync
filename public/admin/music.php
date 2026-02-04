<?php
session_start();
require_once __DIR__ . '/../../includes/db.php';

if (empty($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit;
}

$tracks = $pdo
  ->query("SELECT * FROM background_music ORDER BY created_at DESC")
  ->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Music Management</title>

<style>
* {
  box-sizing: border-box;
  font-family: "Poppins", system-ui, sans-serif;
}

body {
  margin: 0;
  min-height: 100vh;
  background: url("../assets/images/admin.jpg") center/cover no-repeat fixed;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #0f172a;
}

/* soft overlay like dashboard */
body::before {
  content: "";
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.32);
  backdrop-filter: blur(2px);
  z-index: -1;
}

/* MAIN GLASS CONTAINER */
.glass {
  width: 900px;
  padding: 40px;
  border-radius: 26px;
  background: rgba(255,255,255,0.35);
  backdrop-filter: blur(20px) saturate(140%);
  border: 1px solid rgba(255,255,255,0.35);
  box-shadow: 0 40px 90px rgba(0,0,0,0.35);
}

/* HEADER */
.glass h2 {
  margin-top: 0;
  font-size: 1.8rem;
  font-weight: 800;
  letter-spacing: -0.3px;
}

.subtitle {
  color: #334155;
  margin-bottom: 28px;
}

/* TRACK LIST */
.track-list {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px,1fr));
  gap: 18px;
}

.track-card {
  padding: 18px 20px;
  border-radius: 18px;
  background: rgba(255,255,255,0.55);
  box-shadow: 0 16px 40px rgba(0,0,0,0.25);
}

.track-title {
  font-weight: 700;
  margin-bottom: 6px;
}

.track-path {
  font-size: 13px;
  color: #475569;
  margin-bottom: 14px;
  word-break: break-all;
}

.track-actions {
  display: flex;
  gap: 10px;
}

/* BUTTONS */
.btn {
  padding: 8px 14px;
  border-radius: 10px;
  font-weight: 700;
  text-decoration: none;
  font-size: 13px;
  display: inline-block;
}

.btn-toggle {
  background: rgba(255,255,255,0.7);
  color: #0f172a;
}

.btn-delete {
  background: linear-gradient(90deg, #ef4444, #b91c1c);
  color: #fff;
}

/* UPLOAD SECTION */
.upload-box {
  margin-top: 36px;
  padding-top: 26px;
  border-top: 1px solid rgba(15,23,42,0.15);
}

.upload-box h3 {
  margin-top: 0;
}

.upload-box input {
  padding: 10px;
  border-radius: 10px;
  border: 1px solid rgba(0,0,0,0.2);
  margin-bottom: 12px;
  width: 100%;
}

/* PRIMARY ACTION */
.btn-primary {
  background: linear-gradient(90deg, #7c4dff, #47d7d3);
  color: #fff;
  padding: 12px 18px;
  border-radius: 14px;
  border: none;
  font-weight: 800;
  cursor: pointer;
}

/* FOOTER NAV */
.back-link {
  margin-top: 28px;
}

.back-link a {
  text-decoration: none;
  font-weight: 700;
  color: #0f172a;
}
</style>
</head>

<body>

<div class="glass">
  <h2>🎵 Music Management</h2>
  <p class="subtitle">
    Manage ambient background music used across the platform.
  </p>

  <!-- MUSIC LIST -->
  <div class="track-list">
    <?php if (!$tracks): ?>
      <p>No music uploaded yet.</p>
    <?php endif; ?>

    <?php foreach ($tracks as $t): ?>
      <div class="track-card">
        <div class="track-title">
          <?= htmlspecialchars($t['title'] ?: 'Untitled Track') ?>
        </div>

        <div class="track-path">
          <?= htmlspecialchars($t['file_path']) ?>
        </div>

        <div class="track-actions">
          <a class="btn btn-toggle"
             href="music_toggle.php?id=<?= $t['music_id'] ?>">
            <?= $t['is_active'] ? 'Disable' : 'Enable' ?>
          </a>

          <a class="btn btn-delete"
             href="music_delete.php?id=<?= $t['music_id'] ?>"
             onclick="return confirm('Delete this track?')">
            Delete
          </a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- UPLOAD -->
  <div class="upload-box">
    <h3>Add New Music</h3>

    <form action="music_upload.php" method="POST" enctype="multipart/form-data">
      <input type="text" name="title" placeholder="Music title" required>
      <input type="file" name="music" accept=".mp3,.mpeg" required>
      <button class="btn-primary">Upload Music</button>
    </form>
  </div>

  <div class="back-link">
    <a href="dashboard.php">← Back to Dashboard</a>
  </div>
</div>

</body>
</html>
