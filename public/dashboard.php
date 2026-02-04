<?php
// public/dashboard.php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/premium_check.php';

/* -----------------------
   PROTECT PAGE
------------------------ */
if (empty($_SESSION['user_id'])) {
    $_SESSION['after_login_redirect'] = $_SERVER['REQUEST_URI'] ?? 'dashboard.php';
    $_SESSION['error'] = 'Please sign in to access the dashboard.';
    header('Location: login.php');
    exit;
}

/* -----------------------
   ENSURE USERNAME IN SESSION
------------------------ */
/* -----------------------
   USER DATA (ALWAYS FROM DB)
------------------------ */
$userName = htmlspecialchars($_SESSION['user_name']);
$isPremium = isPremiumUser($_SESSION['user_id']);




require_once __DIR__ . '/../includes/header_dashboard.php';

$dashboardQuotes = $pdo
  ->query("SELECT quote_text FROM dashboard_quotes WHERE is_active = 1 ORDER BY created_at ASC")
  ->fetchAll(PDO::FETCH_COLUMN);

// fallback (safety)
if (empty($dashboardQuotes)) {
    $dashboardQuotes = ['Welcome back 🌱'];
}

?>

<!-- mark body so header/global slider can be hidden by CSS -->
<script>
document.addEventListener('DOMContentLoaded', function(){
  document.body.classList.add('dashboard-page');
});
</script>

<!-- Local video background -->
<div class="dashboard-bg">
  <video id="dashVideo" autoplay muted loop playsinline>
    <source src="assets/videos/desk1.mp4" type="video/mp4">
  </video>
  <div class="dashboard-bg-overlay"></div>
</div>

<div class="dashboard-content container">
  <div class="hero-inner ff-glass">

    <h2 class="dash-title">
      Good to see you, <span class="dash-user"><?= $userName ?></span>
      <?php if ($isPremium): ?>
        <span style="font-size:0.9rem;color:#47d7d3;">(Premium)</span>
      <?php endif; ?>
    </h2>

    <p id="dash-quote" class="dash-tagline">
      A neat study desk in your browser — focus without distractions.
    </p>
   <script>
  window.DASHBOARD_QUOTES = <?= json_encode($dashboardQuotes, JSON_UNESCAPED_UNICODE) ?>;
</script>



    <!-- =========================
         FREE MODULES
    ========================== -->
    <div class="dash-modules-grid">
      <a href="collabsphere.php" class="module-card">
        <img src="assets/images/icon-collabsphere.png" alt="CollabSphere" class="module-icon" />
        <div class="module-name">CollabSphere</div>
      </a>

      <a href="focusflow.php" class="module-card">
        <img src="assets/images/icon-focusflow.png" alt="FocusFlow" class="module-icon" />
        <div class="module-name">FocusFlow</div>
      </a>

      <a href="mindplay.php" class="module-card">
        <img src="assets/images/icon-mindplay.png" alt="MindPlay" class="module-icon" />
        <div class="module-name">MindPlay</div>
      </a>
    </div>

    <!-- =========================
         PREMIUM MODULES
    ========================== -->
    <div class="dash-premium-row">


      <!-- AccessArena -->
      <a href="<?= $isPremium ? 'accessarena/accessarena_home.php' : '#' ?>"
         class="module-card <?= $isPremium ? '' : 'locked' ?>">
        <img src="assets/images/icon-assessarena.png" alt="AccessArena" class="module-icon" />
        <div class="module-name">AccessArena</div>
        <?php if (!$isPremium): ?>
          <span class="lock-badge">🔒 Premium</span>
        <?php endif; ?>
      </a>

      <!-- InfoVault -->
      <a href="<?= $isPremium ? 'infovault.php' : '#' ?>"
         class="module-card <?= $isPremium ? '' : 'locked' ?>">
        <img src="assets/images/icon-infovault.png" alt="InfoVault" class="module-icon" />
        <div class="module-name">InfoVault</div>
        <?php if (!$isPremium): ?>
          <span class="lock-badge">🔒 Premium</span>
        <?php endif; ?>
      </a>


    </div>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

  /* =========================
     DASHBOARD QUOTES ROTATION
  ========================== */
  if (Array.isArray(window.DASHBOARD_QUOTES) && window.DASHBOARD_QUOTES.length) {
    const quotes = window.DASHBOARD_QUOTES;
    let qIdx = 0;
    const qEl = document.getElementById('dash-quote');

    if (qEl) {
      qEl.style.opacity = 1;
      qEl.textContent = quotes[qIdx];

      setInterval(() => {
        qIdx = (qIdx + 1) % quotes.length;
        qEl.style.opacity = 0;

        setTimeout(() => {
          qEl.textContent = quotes[qIdx];
          qEl.style.opacity = 1;
        }, 300);
      }, 6000);
    }
  }

  /* =========================
     PREMIUM LOCK HANDLING
  ========================== */
  document.querySelectorAll('.module-card.locked').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();

      const t = document.createElement('div');
      t.className = 'upgrade-toast';
      t.textContent = 'This feature is premium. Redirecting to upgrade...';
      document.body.appendChild(t);

      setTimeout(() => t.classList.add('visible'), 20);

      setTimeout(() => {
        window.location.href = 'premium_access.php';
      }, 1200);
    });
  });

});
</script>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>
