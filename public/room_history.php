<?php
// public/room_history.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// require login
if (empty($_SESSION['user_id'])) {
    $_SESSION['after_login_redirect'] = 'public/room_history.php';
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';          // provides $pdo (PDO)
require_once __DIR__ . '/../includes/header_dashboard.php'; // dashboard header

$uid = (int) $_SESSION['user_id'];
?>

<link rel="stylesheet" href="assets/css/collab.css?v=1" />
<script>document.body.classList.add('dashboard-page');</script>
<!-- Background image ONLY -->
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

<div class="collab-viewport">
  <div class="collab-hero">
    <div class="collab-card" style="max-width:1100px;margin:0 auto;">
      <div class="collab-card-head" style="align-items:flex-start;flex-direction:row;gap:18px;">
        <img src="assets/images/room-history.jpg" alt="History" class="collab-logo" style="width:64px;height:64px;border-radius:12px;"/>
        <div>
          <h1 style="margin:0">Room History</h1>
          <p class="lead" style="margin:6px 0 0">Rooms you've created and recently joined. Click "Open" to enter a room.</p>
        </div>
      </div>

      <?php
      // Fetch rooms created by user
      $createdStmt = $pdo->prepare("SELECT room_id, title, topic, room_code, created_at FROM rooms WHERE host_user_id = :uid ORDER BY created_at DESC");
      $createdStmt->execute([':uid' => $uid]);
      $createdRooms = $createdStmt->fetchAll();

      // Fetch rooms the user joined (excluding ones they host to avoid duplicates)
      $joinedStmt = $pdo->prepare("
        SELECT r.room_id, r.title, r.topic, r.room_code, rp.joined_at
        FROM room_participants rp
        JOIN rooms r ON rp.room_id = r.room_id
        WHERE rp.user_id = :uid AND r.host_user_id != :uid
        ORDER BY rp.joined_at DESC
      ");
      $joinedStmt->execute([':uid' => $uid]);
      $joinedRooms = $joinedStmt->fetchAll();
      ?>

      <section style="margin-top:18px;">
        <h2 style="margin:0 0 8px 0;font-size:1.05rem">Rooms you created</h2>
        <?php if (empty($createdRooms)): ?>
          <div style="padding:12px;color:var(--muted)">You haven't created any rooms yet. <a href="collabsphere.php" style="color:#b8a8ff;text-decoration:underline">Create one now</a>.</div>
        <?php else: ?>
          <div class="card-grid" style="margin-top:12px;">
            <?php foreach ($createdRooms as $r): ?>
              <div class="card glass-card" style="display:flex;align-items:center;gap:12px;">
                <div style="width:94px;height:72px;border-radius:10px;flex-shrink:0;background:linear-gradient(90deg,var(--accent1),var(--accent2));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;">
                  <?= htmlspecialchars(substr($r['room_code'],0,6)) ?>
                </div>
                <div style="flex:1">
                  <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
                    <div>
                      <div style="font-weight:800;font-size:1rem;"><?= htmlspecialchars($r['title']) ?></div>
                      <div style="color:var(--muted);font-size:0.95rem;margin-top:4px;"><?= htmlspecialchars($r['topic'] ?: '—') ?></div>
                    </div>
                    <div style="text-align:right">
                      <div style="font-size:0.85rem;color:var(--muted)"><?= date('d M Y, H:i', strtotime($r['created_at'])) ?></div>
                      <div style="margin-top:8px">
                        <a class="btn small" href="room.php?room_id=<?= (int)$r['room_id'] ?>&code=<?= urlencode($r['room_code']) ?>">Open</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <section style="margin-top:20px;">
        <h2 style="margin:0 0 8px 0;font-size:1.05rem">Rooms you joined</h2>
        <?php if (empty($joinedRooms)): ?>
          <div style="padding:12px;color:var(--muted)">You haven't joined any rooms yet. Use a room code to join a friend.</div>
        <?php else: ?>
          <div class="card-grid" style="margin-top:12px;">
            <?php foreach ($joinedRooms as $r): ?>
              <div class="card glass-card" style="display:flex;align-items:center;gap:12px;">
                <div style="width:94px;height:72px;border-radius:10px;flex-shrink:0;background:linear-gradient(90deg,var(--accent2),var(--accent1));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;">
                  <?= htmlspecialchars(substr($r['room_code'],0,6)) ?>
                </div>
                <div style="flex:1">
                  <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
                    <div>
                      <div style="font-weight:800;font-size:1rem;"><?= htmlspecialchars($r['title']) ?></div>
                      <div style="color:var(--muted);font-size:0.95rem;margin-top:4px;"><?= htmlspecialchars($r['topic'] ?: '—') ?></div>
                    </div>
                    <div style="text-align:right">
                      <div style="font-size:0.85rem;color:var(--muted)"><?= date('d M Y, H:i', strtotime($r['joined_at'])) ?></div>
                      <div style="margin-top:8px">
                        <a class="btn small" href="room.php?room_id=<?= (int)$r['room_id'] ?>&code=<?= urlencode($r['room_code']) ?>">Open</a>
                        <a class="btn small outline" href="collabsphere.php" style="margin-left:8px">Details</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    <div style="margin-bottom:14px;">
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
</div>

<?php
// close main wrapper and include footer
echo '</div></main>';
require_once __DIR__ . '/../includes/footer.php';
