<?php
// public/infovault_files.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// require login
if (empty($_SESSION['user_id'])) {
    $_SESSION['after_login_redirect'] = 'public/infovault_files.php';
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
       background-image: url('assets/images/infovault-bg.jpeg');
       background-size: cover;
       background-position: center;
       background-repeat: no-repeat;
     ">
  <div class="dashboard-bg-overlay"></div>
</div>

<link rel="stylesheet" href="assets/css/info.css?v=2" />

<div class="collab-viewport">
  <div class="collab-hero">
    <div class="collab-card" style="max-width:760px;margin:0 auto;">

      <!-- HEADER -->
      <div class="collab-card-head">
        <img src="assets/images/file-vault-icon.png"
             alt="File Vault"
             class="collab-logo" />

        <h1>File Vault</h1>

        <p class="lead">
          Upload, organize, and securely store your study materials
          for quick access anytime.
        </p>
      </div>

      <!-- FILE UPLOAD FORM -->
      <form
        method="POST"
        enctype="multipart/form-data"
        autocomplete="off"
      >

        <div style="margin-bottom:16px;">
          <label style="display:block;font-weight:700;margin-bottom:6px;">
            Select file
          </label>

          <input
            type="file"
            name="vault_file"
            required
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

        <div style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
          <div style="flex:1;min-width:200px;">
            <label style="display:block;font-weight:700;margin-bottom:6px;">
              File label (optional)
            </label>

            <input
              type="text"
              name="file_label"
              placeholder="e.g. DBMS Unit-3 Notes"
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

          <div style="flex:0 0 160px;">
            <button
              type="submit"
              class="btn primary"
              style="width:100%;padding:12px;border-radius:10px;">
              Upload
            </button>
          </div>
        </div>
      </form>

      <!-- BACK LINK -->
      <div style="margin-top:16px;">
        <a href="infovault.php"
           class="btn primary"
           style="
             display:inline-block;
             padding:10px 18px;
             border-radius:10px;
             font-size:0.95rem;
             text-decoration:none;
           ">
          ← Back to InfoVault
        </a>
      </div>

      <!-- FILE LIST (UI ONLY) -->
      <div style="margin-top:26px;">
        <h3 style="margin-bottom:12px;">Your Files</h3>

        <div style="
          padding:12px;
          border-radius:10px;
          background:rgba(255,255,255,0.03);
          display:flex;
          justify-content:space-between;
          align-items:center;
          margin-bottom:10px;
        ">
          <div>
            <strong>DBMS_Notes.pdf</strong><br>
            <span style="font-size:0.85rem;opacity:0.7;">
              Uploaded on 02 Jan 2026
            </span>
          </div>
          <button class="btn"
                  style="background:#dc3545;color:#fff;border-radius:8px;">
            Delete
          </button>
        </div>

        <div style="
          padding:12px;
          border-radius:10px;
          background:rgba(255,255,255,0.03);
          display:flex;
          justify-content:space-between;
          align-items:center;
        ">
          <div>
            <strong>OS_CheatSheet.png</strong><br>
            <span style="font-size:0.85rem;opacity:0.7;">
              Uploaded on 28 Dec 2025
            </span>
          </div>
          <button class="btn"
                  style="background:#dc3545;color:#fff;border-radius:8px;">
            Delete
          </button>
        </div>
      </div>

      <!-- TIP -->
      <div style="margin-top:18px;color:rgba(255,255,255,0.7);font-size:0.95rem;">
        <strong>Tip:</strong>
        Keep your files labeled properly for faster access during revision.
      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
