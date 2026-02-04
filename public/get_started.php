<?php
// public/get_started.php
$is_index_page = false;
require_once __DIR__ . '/../includes/header_public.php';
?>

<div class="auth-page">
  <div class="auth-wrap">

    <div class="glass-auth-card">
      <img src="assets/images/logo.jpg" alt="ÉtudeSync" class="logo-center" />

      <h2>Get Started</h2>
      <p class="muted" style="margin-bottom:18px;">
        Choose how you want to sign in
      </p>

      <!-- USER LOGIN -->
      <a href="login.php" class="btn-login" style="margin-bottom:14px; display:block;">
        👤 User Login
      </a>

      <!-- ADMIN LOGIN -->
      <a href="admin/login.php" class="btn-login" style="
        display:block;
        background: linear-gradient(90deg, #ef4444, #b91c1c);
        box-shadow: 0 14px 40px rgba(127, 29, 29, 0.45);
      ">
        🛠️ Admin Login
      </a>

    </div>

  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
