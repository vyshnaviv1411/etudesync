<?php
// public/room.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Dev helper while debugging — remove or set to 0 for production
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php';           // provides $pdo
require_once __DIR__ . '/../includes/header_dashboard.php'; // dashboard header

// require login
if (empty($_SESSION['user_id'])) {
    $_SESSION['after_login_redirect'] = 'public/room.php';
    header('Location: login.php');
    exit;
}

$uid = (int) $_SESSION['user_id'];
// check premium status
$stmt = $pdo->prepare("SELECT is_premium FROM users WHERE id = ?");
$stmt->execute([$uid]);
$isPremium = (int)$stmt->fetchColumn();



// validate inputs: need room_id and code (code optional)
$room_code = isset($_GET['code']) ? trim($_GET['code']) : '';

if ($room_code === '') {
    echo '<div style="padding:24px">Invalid room. <a href="collabsphere.php">Back</a></div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// load room using code
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE room_code = :code LIMIT 1");
$stmt->execute([':code' => $room_code]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$room) {
    echo '<div style="padding:24px">Room not found. <a href="collabsphere.php">Back</a></div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$room_id = (int)$room['room_id'];


// optional: verify code if provided
if ($room_code !== '' && strcasecmp($room_code, $room['room_code']) !== 0) {
    echo '<div style="padding:24px">Invalid room code. <a href="collabsphere.php">Back</a></div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// mark participant presence (update or insert)
// mark participant presence
try {
    $stmt = $pdo->prepare(
        "SELECT id FROM room_participants WHERE room_id = :room AND user_id = :user"
    );
    $stmt->execute([':room' => $room_id, ':user' => $uid]);
if (!$stmt->fetchColumn()) {
    // first time join
    $ins = $pdo->prepare(
        "INSERT INTO room_participants (room_id, user_id, last_active)
         VALUES (:room, :user, NOW())"
    );
    $ins->execute([':room' => $room_id, ':user' => $uid]);
} else {
    // already joined → update activity
    $upd = $pdo->prepare(
        "UPDATE room_participants
         SET last_active = NOW()
         WHERE room_id = :room AND user_id = :user"
    );
    $upd->execute([':room' => $room_id, ':user' => $uid]);
}

} catch (Exception $e) {
    // ignore for now
}



// determine manage permission (simple check)
$me = $uid;
$canManage = false;
try {
    $stmt = $pdo->prepare("SELECT role FROM room_participants WHERE room_id = :r AND user_id = :u LIMIT 1");
    $stmt->execute([':r'=>$room_id, ':u'=>$me]);
    $myRole = $stmt->fetchColumn();
    $canManage = in_array($myRole, ['host','moderator']);
} catch (Exception $e) { /* ignore */ }

// thumbnail: prefer room-specific uploaded thumbnail, else fallback to uploaded file or project placeholder
$room_thumbnail = 'assets/images/collab-bg.jpg';
if (!empty($room['thumbnail'])) {
    $thumbPath = __DIR__ . '/../' . ltrim($room['thumbnail'], '/');
    if (file_exists($thumbPath)) {
        $room_thumbnail = $room['thumbnail'];
    }
} else {
    // dev fallback: prefer public asset
    if (file_exists(__DIR__ . '/../assets/images/collab-bg.jpg')) {
        $room_thumbnail = 'assets/images/collab-bg.jpg';
    } else {
        $room_thumbnail = 'assets/images/placeholder-room.png';
    }
}

// escape helper
function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
?>
<!-- ensure dashboard visuals load -->
<script>document.body.classList.add('dashboard-page');</script>
<script src="assets/js/polls.js?v=1" defer></script>

<link rel="stylesheet" href="assets/css/collab.css?v=3" />
<style>
/* small layout helpers (keep in external CSS if preferred) */
/* 🔓 UNLOCK PAGE WIDTH FOR ROOM */
body.dashboard-page .page-wrapper,
body.dashboard-page main,
body.dashboard-page .collab-hero {
  max-width: none !important;
  width: 100% !important;
  padding-left: 0 !important;
  padding-right: 0 !important;
}

.room-layout { display:grid; grid-template-columns: 68% 32%; gap:18px; align-items:start; }

@media (max-width: 980px) { .room-layout { grid-template-columns: 1fr; } }
.chat-panel { min-height: 520px; display:flex; flex-direction:column; gap:12px; }
.chat-messages { flex:1; overflow:auto; padding:12px; border-radius:12px; background: linear-gradient(180deg, rgba(255,255,255,0.01), rgba(255,255,255,0.02)); border:1px solid rgba(255,255,255,0.04); }
.glass-card { background: rgba(255,255,255,0.01); border:1px solid rgba(255,255,255,0.03); border-radius:12px; padding:12px; }
.small-muted { color:var(--muted); font-size:0.9rem; }
.room-panel { padding:12px; border-radius:10px; }
.chat-row { display:flex; gap:10px; align-items:flex-start; padding:8px; border-radius:8px; }
.chat-avatar { width:40px; height:40px; border-radius:8px; object-fit:cover; }
.chat-body { flex:1; }
.chat-meta { font-size:0.85rem; color:var(--muted); display:flex; gap:8px; align-items:center; }
.chat-text { margin-top:6px; white-space:pre-wrap; }
.chat-actions { display:flex; gap:8px; align-items:center; }
</style>

<script>document.body.classList.add('dashboard-page');</script>
<!-- Background image ONLY -->
<div class="dashboard-bg"
     aria-hidden="true"
     style="
       background-image: url('assets/images/infovault_bg.jpg');
       background-size: cover;
       background-position: center;
       background-repeat: no-repeat;
     ">
  <div class="dashboard-bg-overlay"></div>
</div>

<div class="collab-viewport" style="padding-top:18px;">
  <div class="collab-hero" style="align-items:flex-start; justify-content:center;">
    <div class="collab-card" style="width:100%; max-width:5000px; padding:50px 60px;">
<div class="room-header">

        <div style="display:flex;gap:12px;align-items:center">
          <img src="assets/images/inside_room.jpg" alt="Room" style="width:64px;height:64px;border-radius:10px;object-fit:cover"/>
          <div>
            <div style="font-weight:800;font-size:1.1rem;"><?= e($room['title']) ?></div>
<div class="small-muted"><?= e($room['topic'] ?: '—') ?></div>
          </div>
        </div>

<div class="room-header-center">
  <div class="room-code">Room CODE:<?= e($room['room_code']) ?></div>
</div>

<div style="display:flex;gap:8px;align-items:center">

          <div class="small-muted">Room ID: <?= (int)$room['room_id'] ?></div>

          <form method="POST" action="leave_room.php" style="display:inline;">
  <input type="hidden" name="room_id" value="<?= (int)$room_id ?>">
  <button type="submit" class="btn small danger">
    End Session
  </button>
</form>

        </div>
      </div>

      <div class="room-layout">

  <!-- LEFT MAIN COLUMN -->
  <div class="room-main">

    <!-- CHAT -->
    <div class="room-panel glass-card" id="chatPanel" style="display:flex;flex-direction:column;gap:8px;padding:12px;">
      <div style="font-weight:800;display:flex;justify-content:space-between;align-items:center;">
        <div>Chat</div>
        <small style="color:var(--muted)">Realtime (polling)</small>
      </div>

      <div id="chatList" style="flex:1;overflow:auto;max-height:360px;padding:8px;display:flex;flex-direction:column;gap:8px;">
      </div>

      <form id="chatForm" onsubmit="return false;" style="display:flex;gap:8px;align-items:center;margin-top:8px;">
        <input id="chatInput" type="text" placeholder="Write a message..."
               style="flex:1;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,0.06);background:rgba(255,255,255,0.02);color:#fff" />
        <button id="chatSendBtn" type="button" class="btn primary small">Send</button>
      </form>
    </div>

    <!-- WHITEBOARD -->
    <div id="whiteboardArea" class="glass-card" style="margin-top:18px;padding:12px;">

      <!-- TOOLBAR -->
      <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:8px;">
        <div style="font-weight:800">Whiteboard</div>

        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
          <label class="small-muted" style="font-size:0.9rem;">Brush:</label>

          <input id="wbBrushSize" type="range" min="1" max="20" value="3" />
          <input id="wbColor" type="color" value="#ffffff"
                 style="width:42px;height:30px;border-radius:8px;border:none" />

          <button id="wbUndo"   type="button" class="btn small">Undo</button>
          <button id="wbClear"  type="button" class="btn small">Clear</button>
          <button id="wbExport" type="button" class="btn small">Export PNG</button>
        </div>
      </div>

      <!-- SINGLE CANVAS (ONLY ONE!) -->
      <canvas
        id="wbCanvas"
        style="width:100%;height:420px;border-radius:10px;
               background:transparent;touch-action:none;
               border:1px solid rgba(255,255,255,0.04)">
      </canvas>

      <div id="wbStatus" style="margin-top:8px;color:var(--muted);font-size:0.9rem">
        Whiteboard ready. Changes autosaved.
      </div>
    </div> 
  </div> 
<aside class="room-sidebar">

  <!-- PARTICIPANTS -->
  <div class="participants-box glass-card" style="padding:12px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
      <div style="font-weight:800">Participants</div>
      <div id="participantCount" class="small-muted">–</div>
    </div>
    <div id="participantsListBox" style="max-height:360px;overflow:auto;"></div>
  </div>

  <div style="height:14px"></div>

  <!-- POLLS -->
  <div class="room-panel glass-card" id="pollPanel" style="margin-top:18px;">
    <div style="display:flex;align-items:center;justify-content:space-between;">
      <h3 style="margin:0">Polls</h3>
      <?= $canManage ? '<small class="small-muted">You can manage polls</small>' : '' ?>
    </div>

    <!-- POLL CREATION -->
    <div style="display:flex;flex-direction:column;gap:10px;margin-top:12px;">

      <input
        id="pollQ"
        type="text"
        placeholder="Poll question"
        style="padding:10px;border-radius:10px;
               border:1px solid rgba(255,255,255,0.08);
               background:rgba(255,255,255,0.04);
               color:#fff;"
      />

      <div style="display:flex;gap:8px;">
        <input
          id="pollOpt1"
          type="text"
          placeholder="Option 1"
          style="flex:1;padding:10px;border-radius:10px;
                 border:1px solid rgba(255,255,255,0.08);
                 background:rgba(255,255,255,0.04);
                 color:#fff;"
        />

        <input
          id="pollOpt2"
          type="text"
          placeholder="Option 2"
          style="flex:1;padding:10px;border-radius:10px;
                 border:1px solid rgba(255,255,255,0.08);
                 background:rgba(255,255,255,0.04);
                 color:#fff;"
        />
      </div>

      <button id="createPollBtn" class="btn small" style="align-self:flex-end;">
        Create Poll
      </button>
    </div>

    <!-- POLL LIST -->
    <div id="pollArea"
         data-room="<?= (int)$room_id ?>"
         style="margin-top:14px;display:flex;flex-direction:column;gap:12px;">
    </div>
  </div>

  <div style="height:14px"></div>

 
<!-- ACCESSARENA : LIVE QUIZ -->
<div class="room-panel glass-card" id="accessArenaPanel">

  <div style="display:flex;justify-content:space-between;align-items:center;">
    <strong>AccessArena</strong>
    <span class="small-muted">Live Quiz</span>
  </div>

  <div style="height:12px"></div>

  <?php if ($isPremium): ?>

    <!-- MENTOR SECTION -->
    <div class="aa-section">
      <div class="aa-title">Mentor</div>

      <a href="accessarena/mentor/create_quiz.php"
         class="btn primary small"
         style="width:100%;margin-top:8px;">
        ➕ Create Quiz
      </a>
      

      <a href="accessarena/mentor/publish_quiz.php"
         class="btn primary small"
         style="width:100%;margin-top:8px;">
        ➕ Upload Quiz
      </a>

    </div>

    <div class="aa-divider"></div>

    <!-- PARTICIPANT SECTION -->
    <div class="aa-section">
      <div class="aa-title">Participant</div>

      <input
        id="aaQuizCode"
        type="text"
        placeholder="Enter quiz code"
        class="aa-input"
      />

      <button
        id="aaJoinQuizBtn"
        class="btn primary small"
        style="width:100%;margin-top:8px;">
        Join Quiz
      </button>
    </div>

  <?php else: ?>

    <!-- NON-PREMIUM -->
    <div class="small-muted" style="margin-top:8px;">
      Live quizzes are a premium feature.
    </div>

    <a href="premium_access.php"
       class="btn primary small"
       style="width:100%;margin-top:10px;">
      ⭐ Upgrade to Premium
    </a>

  <?php endif; ?>

</div>




  <!-- FILES -->
  <div class="room-panel glass-card" id="filesPanel">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
      <strong>Files</strong>
      <small style="color:var(--muted)">Upload important files</small>
    </div>

   <?php if ($isPremium): ?>

  <!-- PREMIUM USER -->
  <form id="fileUploadForm"
        data-room="<?= (int)$room_id ?>"
        style="display:flex;gap:8px;align-items:center;">
      <a href="infovault_files.php" class="btn primary small">
  Choose from InfoVault
</a>

  </form>

<?php else: ?>

  <!-- NON-PREMIUM USER -->
  <div style="display:flex;flex-direction:column;gap:8px;">
    <div class="small-muted">
      File upload is a premium feature.
    </div>

    <a href="premium_access.php"
   class="btn primary small"
   style="box-shadow:0 0 12px rgba(124,77,255,0.6);">
  ⭐ Upgrade to Premium
</a>

  </div>

<?php endif; ?>



    <div id="filesList"
         style="margin-top:12px;display:flex;flex-direction:column;gap:10px">
    </div>
  </div>

  <div style="height:14px"></div>

</aside>


    </div> <!-- .collab-card -->
  </div>
</div>

<script>
const ROOM_ID = <?= (int)$room_id ?>;
const ROOM_CODE = <?= json_encode($room['room_code']) ?>;
const USER_ID = <?= (int)$uid ?>;
const CAN_MANAGE = <?= $canManage ? 'true' : 'false' ?>;

/* 🔑 THIS IS STEP 3 */
sessionStorage.setItem('ACTIVE_ROOM_ID', ROOM_ID);
</script>


<!-- load core feature scripts (chat has fallback inline below) -->
<script src="assets/js/whiteboard.js?v=1" defer></script>
<script src="assets/js/chat.js?v=1" defer></script>
<script src="assets/js/files.js?v=1" defer></script>
<script src="assets/js/participants.js?v=1" defer></script>

<!-- FILE PREVIEW MODAL -->
<div id="filePreviewModal" style="
  display:none;
  position:fixed;
  inset:0;
  background:rgba(0,0,0,0.7);
  z-index:9999;
  align-items:center;
  justify-content:center;
">
  <div style="
    width:90%;
    max-width:900px;
    height:80%;
    background:#111;
    border-radius:12px;
    overflow:hidden;
    display:flex;
    flex-direction:column;
  ">
    <div style="padding:10px;display:flex;justify-content:space-between;">
      <strong id="fpTitle">File Preview</strong>
      <button onclick="closeFilePreview()" class="btn small outline">Close</button>
    </div>

    <iframe id="fpFrame"
            style="flex:1;border:none;background:#000"></iframe>
  </div>
</div>
<script>
document.getElementById('endRoomBtn')?.addEventListener('click', () => {
  if (!confirm('End session? Files will be removed.')) return;

  fetch('api/end_room.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'room_id=' + ROOM_ID
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('Session ended');
      location.reload();
    }
  });
});
</script>

<script>
document.getElementById('aaJoinQuizBtn')?.addEventListener('click', () => {
  const code = document.getElementById('aaQuizCode').value.trim();

  if (!code) {
    alert('Please enter a quiz code');
    return;
  }

  window.location.href =
window.location.href =
  'accessarena/participant/join_quiz.php?code=' + encodeURIComponent(code);

});
</script>


<?php
require_once __DIR__ . '/../includes/footer.php';
