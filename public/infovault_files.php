<?php
// public/infovault_files.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/premium_check.php';

// require login
if (empty($_SESSION['user_id'])) {
    $_SESSION['after_login_redirect'] = 'public/infovault_files.php';
    header('Location: login.php');
    exit;
}

// CRITICAL: Require premium access
requirePremium();

$uid = (int)$_SESSION['user_id'];

// fetch vault files
$stmt = $pdo->prepare('SELECT * FROM infovault_files WHERE user_id = ? ORDER BY uploaded_at DESC');
$stmt->execute([$uid]);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function assetUrl($path) {
  return ltrim((string)$path, '/\\');
}

require_once __DIR__ . '/../includes/header_dashboard.php';
?>

<script>document.body.classList.add('dashboard-page');</script>

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

<link rel="stylesheet" href="assets/css/info.css?v=3" />
<div class="collab-viewport">
  <div class="collab-hero">
    <div class="collab-card">
      <div class="infovault-inner">

        <!-- HEADER -->
        <div class="collab-card-head">
          <img src="assets/images/file-vault-icon.png"
               alt="File Vault"
               class="collab-logo" />

          <h1>File Vault</h1>

          <p class="lead">
            Your personal, permanent storage for study materials.
            Only you can access these files.
          </p>
        </div>

        <!-- UPLOAD -->
        <form
          action="api/upload_infovault_file.php"
          method="POST"
          enctype="multipart/form-data"
          style="margin-top:24px;"
        >
          <div style="display:flex;gap:14px;align-items:end;flex-wrap:wrap;">
            <div style="flex:1;">
              <label class="small-muted">Select file</label>
              <input
                type="file"
                name="file"
                required
                style="
                  width:100%;
                  padding:12px;
                  border-radius:10px;
                  border:1px solid rgba(255,255,255,0.08);
                  background:rgba(255,255,255,0.03);
                  color:#fff;
                "
              />
            </div>

            <button class="btn primary" style="padding:12px 22px;">
              Upload
            </button>
          </div>
        </form>

        <!-- FILES -->
        <div style="margin-top:36px;">

          <!-- IMPORTANT -->
          <h3>⭐ Important</h3>
          <div class="file-list">
            <?php
            $hasStarred = false;
            foreach ($files as $f):
              if ((int)$f['is_starred'] !== 1) continue;
              $hasStarred = true;
            ?>
              <div class="file-card">
                <div>
                  <strong><?= e($f['file_name']) ?></strong><br>
                  <span class="file-meta">
                    Uploaded on <?= date('d M Y', strtotime($f['uploaded_at'])) ?>
                  </span>
                </div>

                <div class="file-actions">
                  <a href="/etudesync/<?= e($f['file_path']) ?>" target="_blank" class="btn small">
                    Open
                  </a>

                  <form method="POST" action="api/toggle_star.php" style="display:inline;">
                    <input type="hidden" name="file_id" value="<?= (int)$f['id'] ?>">
                    <button class="btn small outline" title="Unstar">
                      ★
                    </button>
                  </form>

        
                
                </div>
              </div>
            <?php endforeach; ?>

            <?php if (!$hasStarred): ?>
              <div class="small-muted">No important files yet.</div>
            <?php endif; ?>
          </div>

          <!-- ALL FILES -->
          <h3 style="margin-top:28px;">📁 All Files</h3>
          <div class="file-list">
            <?php if (count($files) === 0): ?>
              <div class="small-muted">No files uploaded yet.</div>
            <?php endif; ?>

            <?php foreach ($files as $f): ?>
              <div class="file-card">
                <div>
                  <strong><?= e($f['file_name']) ?></strong><br>
                  <span class="file-meta">
                    Uploaded on <?= date('d M Y', strtotime($f['uploaded_at'])) ?>
                  </span>
                </div>

                <div class="file-actions">
                  <a href="/etudesync/<?= e($f['file_path']) ?>"
                     target="_blank"
                     class="btn small">
                    Open
                  </a>

                  <form method="POST" action="api/toggle_star.php" style="display:inline;">
                    <input type="hidden" name="file_id" value="<?= (int)$f['id'] ?>">
                    <button class="btn small outline" title="Toggle star">
                      <?= ((int)$f['is_starred'] === 1) ? '★' : '☆' ?>
                    </button>
                  </form>

                  <form method="POST" action="api/delete_infovault_file.php" style="display:inline;">
                    <input type="hidden" name="file_id" value="<?= (int)$f['id'] ?>">
                    <button class="btn small danger">Delete</button>
                  </form>

                  <button class="btn small primary share-btn"
                          data-file-id="<?= (int)$f['id'] ?>">
                    Share to Room
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

        </div>

        <!-- BACK -->
        <div style="margin-top:30px;">
          <a href="infovault.php" class="btn primary">
            ← Back to InfoVault
          </a>
        </div>

        <!-- TIP -->
        <div style="margin-top:18px;color:rgba(255,255,255,0.7);font-size:0.95rem;">
          <strong>Tip:</strong>
          Star important files to access them faster during revision.
        </div>

      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('click', function (e) {
  if (!e.target.classList.contains('share-btn')) return;

  const fileId = e.target.getAttribute('data-file-id');

  if (!fileId) {
    alert('File ID missing');
    return;
  }

  const roomCode = prompt("Enter Room Code to share this file:");

  if (!roomCode) return;

  fetch('api/share_file_to_room.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: new URLSearchParams({
      file_id: fileId,
      room_code: roomCode
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('File shared to room successfully ✅');
    } else {
      alert(data.error || 'Failed to share file');
    }
  })
  .catch(err => {
    console.error(err);
    alert('Network error');
  });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
