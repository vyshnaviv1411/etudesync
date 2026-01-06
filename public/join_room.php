<?php
// public/join_room.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// require login to join rooms (keeps participants linked to a user account)
if (empty($_SESSION['user_id'])) {
    $_SESSION['after_login_redirect'] = 'public/join_room.php';
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<!-- apply dashboard background/video styling -->
<script>document.body.classList.add('dashboard-page');</script>

<div class="dashboard-bg"
     aria-hidden="true"
     style="
       background-image: url('assets/images/collabsbg.jpg');
       background-size: cover;
       background-position: center;
       background-repeat: no-repeat;
     ">
  <div class="dashboard-bg-overlay"></div>
</div>

<link rel="stylesheet" href="assets/css/collab.css?v=2" />

<div class="collab-viewport">
  <div class="collab-hero">
    <div class="collab-card" style="max-width:720px; margin:0 auto;">
      <div class="collab-card-head" style="align-items:center;">
        <!-- production-friendly logo path -->
        <img src="assets/images/join-logo.png" alt="Join Room" class="collab-logo" style="width:72px;height:72px;" />
        <h1>Join a Room</h1>
        <p class="lead">Have a room code? Enter it below to join your study room instantly.</p>
      </div>

      <form id="joinRoomForm" class="create-room-form" autocomplete="off" method="post" action="api/join_room.php">
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
          <div style="flex:1;min-width:220px">
            <label for="room_code" style="display:block;font-weight:700;margin-bottom:6px">Room Code</label>
            <input id="room_code" name="room_code" required maxlength="12" placeholder="Enter room code (e.g. AB12CD)" style="width:100%;padding:12px;border-radius:10px;border:1px solid rgba(255,255,255,0.08);background:rgba(255,255,255,0.02);color:#fff" />
          </div>


          <div style="flex:0 0 150px;display:flex;flex-direction:column;justify-content:flex-end;">
            <button id="submitJoin" type="submit" class="btn primary" style="padding:12px;border-radius:10px;margin-top:6px;">Join Room</button>
          </div>
        </div>

        <div id="jrMsg" style="margin-top:14px;color:rgba(255,255,255,0.9);display:none;"></div>
      </form>
     
      <div style="margin-top:18px;margin-bottom:20px;color:rgba(255,255,255,0.72);font-size:0.90rem;">
        Tip: If you don't have a code, ask the host to share it or create a new room from CollabSphere.
      </div>
           <div style="margin-bottom:22px;">
  <a href="collabsphere.php"
     class="btn primary"
     style="
       display:inline-block;
       padding:10px 18px;
       border-radius:10px;
       font-size:0.95rem;
       text-decoration:none;
     ">
    ← Back to Modules
  </a>
</div>
  </div>
</div>

<script src="assets/js/join_room.js?v=1" defer></script>

<?php
require_once __DIR__ . '/../includes/footer.php';
