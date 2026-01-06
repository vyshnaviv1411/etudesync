<?php
// public/collabsphere.php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../includes/header_dashboard.php';
?>

<!-- Ensure dashboard background behavior -->
<script>
  document.body.classList.add('dashboard-page');
</script>

<!-- Background -->
<div class="dashboard-bg" aria-hidden="true" style="background-image: url('assets/images/collab-bg.jpg');">
  <video autoplay muted loop playsinline>
    <source src="assets/videos/desk1.mp4" type="video/mp4">
  </video>
  <div class="dashboard-bg-overlay"></div>
</div>

<link rel="stylesheet" href="assets/css/collab.css?v=4">

<div class="collab-viewport">
  <div class="collab-hero">
    <div class="collab-card">

      <!-- ONLY ACTION CARDS -->
      <div class="collab-actions-grid">

        <a class="action-card" href="create_room.php">
          <div class="action-icon">
            <img src="assets/images/whiteboard-icon.png" alt="Create Room">
          </div>
          <div class="action-title">Create a Room</div>
          <div class="action-sub">Start a new private or scheduled room</div>
        </a>

        <a class="action-card" href="join_room.php">
          <div class="action-icon">
            <img src="assets/images/chat-icon.png" alt="Join Room">
          </div>
          <div class="action-title">Join a Room</div>
          <div class="action-sub">Enter room code to join instantly</div>
        </a>

        <a class="action-card" href="room_history.php">
          <div class="action-icon">
            <img src="assets/images/participants-icon.png" alt="Room History">
          </div>
          <div class="action-title">Room History</div>
          <div class="action-sub">View rooms you created or joined earlier</div>
        </a>

        <a class="action-card" href="collab_reports.php">
          <div class="action-icon">
            <img src="assets/images/report-icon.png" alt="Reports">
          </div>
          <div class="action-title">Reports</div>
          <div class="action-sub">Insights & activity analysis</div>
        </a>

      </div>

    </div>
  </div>
</div>

<script src="assets/js/collab_main.js" defer></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
