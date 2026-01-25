<?php
$page_title = 'Pomodoro Timer';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>

<style>
.dashboard-bg {
  background-image: url('assets/images/infovault_bg.jpg') !important;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
}
.dashboard-bg video {
  display: none !important;
}
</style>

<div class="collab-viewport">
  <div class="collab-hero">
    <div class="collab-card">

      <h2 class="ff-title">Pomodoro Timer</h2>

      <div class="pomodoro-box">
        <h1 id="timer-display">25:00</h1>

        <div class="pomodoro-buttons">
          <button id="startBtn" class="btn primary">Start</button>
          <button id="pauseBtn" class="btn">Pause</button>
          <button id="resetBtn" class="btn">Reset</button>
        </div>

        <p class="ff-note">Your timer auto-saves even if you reload.</p>
      </div>

    </div>
  </div>
</div>

<script src="assets/js/focusflow/pomodoro.js" defer></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
