<?php
// public/create_room.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// require login
if (empty($_SESSION['user_id'])) {
    $_SESSION['after_login_redirect'] = 'public/create_room.php';
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../includes/header_dashboard.php';
?>

<!-- ensure dashboard styles apply -->
<script>
  document.body.classList.add('dashboard-page');
</script>

<!-- Background -->
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

<link rel="stylesheet" href="assets/css/collab.css?v=2" />

<div class="collab-viewport">
  <div class="collab-hero">
    <div class="collab-card" style="max-width:760px;margin:0 auto;">
      
      <div class="collab-card-head">
        <img src="assets/images/whiteboard-icon.png"
             alt="Create Room"
             class="collab-logo" />
        <h1>Create a Room</h1>
        <p class="lead">
          Fill details and create a private study room.
          Share the room code to invite others.
        </p>
      </div>

      <!-- 🔑 CREATE ROOM FORM -->
      <form
  id="createRoomForm"
  class="create-room-form"
  method="POST"
  action="api/create_room.php"
  autocomplete="off"
>

  <!-- INPUT ROW -->
  <div style="display:flex;gap:14px;flex-wrap:wrap;margin-bottom:18px;">
    
    <div style="flex:1;min-width:240px;">
      <label for="title" style="display:block;font-weight:700;margin-bottom:6px;">
        Room Title
      </label>
      <input
        id="title"
        name="title"
        type="text"
        required
        maxlength="200"
        placeholder="e.g. DBMS Quick Revision"
        style="
          width:100%;
          padding:12px;
          border-radius:10px;
          border:1px solid rgba(255,255,255,0.08);
          background:rgba(255,255,255,0.02);
          color:#fff;
        "
      />
    </div>

    <div style="flex:1;min-width:240px;">
      <label for="topic" style="display:block;font-weight:700;margin-bottom:6px;">
        Topic (optional)
      </label>
      <input
        id="topic"
        name="topic"
        type="text"
        maxlength="200"
        placeholder="e.g. ER Diagrams"
        style="
          width:100%;
          padding:12px;
          border-radius:10px;
          border:1px solid rgba(255,255,255,0.08);
          background:rgba(255,255,255,0.02);
          color:#fff;
        "
      />
    </div>

  </div>

  <!-- BUTTON ROW -->
  <div style="text-align:center;margin-top:10px;">
    <button
      id="submitCreate"
      type="submit"
      class="btn primary"
      style="padding:12px 26px;border-radius:10px;">
      Create Room
    </button>
  </div>

  <!-- BACK -->
  <div style="margin-top:18px;text-align:center;">
    <a href="collabsphere.php"
       class="btn primary"
       style="padding:10px 18px;border-radius:10px;">
      ← Back to Modules
    </a>
  </div>

  <!-- STATUS -->
  <div
    id="crMsg"
    style="margin-top:14px;color:rgba(255,255,255,0.9);display:none;">
  </div>

</form>

      <div style="margin-top:18px;color:rgba(255,255,255,0.7);font-size:0.95rem;">
        <strong>Tip:</strong>
        After creation, you’ll be redirected into the room automatically.
        You can still share the room code with others.
      </div>

    </div>
  </div>
</div>

<!-- Create room logic -->
<script src="assets/js/create_room.js?v=1"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
