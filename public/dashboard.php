<?php
// public/dashboard.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/premium_check.php';

// protect page
if (empty($_SESSION['user_id'])) {
    $_SESSION['after_login_redirect'] = $_SERVER['REQUEST_URI'] ?? 'dashboard.php';
    $_SESSION['error'] = 'Please sign in to access the dashboard.';
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../includes/header_dashboard.php';

$userName = htmlspecialchars($_SESSION['user_name'] ?? 'Guest');
$userIsPremium = isPremiumUser($_SESSION['user_id']);
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
  <div class="dashboard-glass">

    <h2 class="dash-title">Good to see you, <span class="dash-user"><?= $userName ?></span></h2>
    <p id="dash-quote" class="dash-tagline">A neat study desk in your browser — focus without distractions.</p>

    <!-- ROW 1: FREE MODULES (grid) -->
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

    <!-- ROW 2: PREMIUM MODULES (centered flex row) -->
    <div class="dash-premium-row">
      <?php if ($userIsPremium): ?>
        <!-- User is premium - show unlocked cards -->
        <a href="quizforge.php" class="module-card">
          <img src="assets/images/icon-quizforge.png" alt="QuizForge" class="module-icon" />
          <div class="module-name">QuizForge</div>
          <span class="unlock-badge">✨ Premium</span>
        </a>

        <a href="infovault.php" class="module-card">
          <img src="assets/images/icon-infovault.png" alt="InfoVault" class="module-icon" />
          <div class="module-name">InfoVault</div>
          <span class="unlock-badge">✨ Premium</span>
        </a>
      <?php else: ?>
        <!-- User is not premium - show locked cards -->
        <a href="upgrade.php" class="module-card locked">
          <img src="assets/images/icon-quizforge.png" alt="QuizForge" class="module-icon" />
          <div class="module-name">QuizForge</div>
          <span class="lock-badge">🔒 Premium</span>
        </a>

        <a href="upgrade.php" class="module-card locked">
          <img src="assets/images/icon-infovault.png" alt="InfoVault" class="module-icon" />
          <div class="module-name">InfoVault</div>
          <span class="lock-badge">🔒 Premium</span>
        </a>
      <?php endif; ?>
    </div>

  </div>
</div>

<script>
(function(){

  // rotate quotes every 6 seconds
  const quotes = [
    "A neat study desk in your browser — focus without distractions.",
    "Create study rooms and stay focused with friends.",
    "Upload notes, create flashcards and revise smarter.",
    "Pomodoro + planner = better study flow.",
    "Quizzes, leaderboards and progress — see your improvement."
  ];
  let qIdx = 0;
  const qEl = document.getElementById('dash-quote');
  if (qEl) {
    setInterval(() => {
      qIdx = (qIdx + 1) % quotes.length;
      qEl.style.opacity = 0;
      setTimeout(() => {
        qEl.textContent = quotes[qIdx];
        qEl.style.opacity = 1;
      }, 300);
    }, 6000);
  }

})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
