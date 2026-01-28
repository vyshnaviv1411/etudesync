<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/premium_check.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: ../../login.php');
  exit;
}
requirePremium();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $roomCode = trim($_POST['room_code'] ?? '');

  if ($roomCode === '') {
    $error = 'Room code is required.';
  } else {
    // Redirect ONLY to the room
    header(
      "Location: /etudesync/public/collab/room.php?room_code="
      . urlencode($roomCode)
    );
    exit;
  }
}

require_once __DIR__ . '/../../../includes/header_dashboard.php';
?>

<div class="dashboard-bg"
     style="background-image:url('../../assets/images/infovault_bg.jpg')"></div>

<div class="collab-viewport">
  <div class="collab-hero">

    <div class="collab-card accessarena-card" style="max-width:420px;">

      <div class="accessarena-header">
        <h1>Share to Room</h1>
        <p>Enter the room code to continue</p>
      </div>

      <?php if ($error): ?>
        <p style="color:#ff6b6b;text-align:center;margin-bottom:12px;">
          <?= htmlspecialchars($error) ?>
        </p>
      <?php endif; ?>

      <form method="POST" class="accessarena-form">

        <label>Room Code</label>
        <input type="text"
               name="room_code"
               placeholder="Enter room code"
               required>

        <div style="text-align:center;margin-top:20px;">
          <button type="submit" class="btn primary">
            Go to Room
          </button>
        </div>

      </form>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
