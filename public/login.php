<?php
// public/login.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header_public.php';
?>

<div class="auth-page">
  <div class="auth-wrap">
    <div class="glass-auth-card">

      <img src="assets/images/logo.jpg" alt="ÉtudeSync" class="logo-center" loading="lazy" />

      <?php
      if (!empty($_SESSION['error'])) {
          echo '<div class="form-error flash-message">' . htmlspecialchars($_SESSION['error']) . '</div>';
          unset($_SESSION['error']);
      } elseif (!empty($_SESSION['success'])) {
          echo '<div class="form-ok flash-message">' . htmlspecialchars($_SESSION['success']) . '</div>';
          unset($_SESSION['success']);
      }
      ?>

      <form action="authenticate.php" method="POST" class="auth-form" novalidate>
        <div class="input-group">
          <label for="email">Email</label>
          <input id="email" type="email" name="email" required placeholder="you@example.com" />
        </div>

      <div class="input-group">
  <label>Password</label>
  <div class="password-box">
    <input type="password" id="password" name="password" required>
    <span class="toggle-eye" onclick="togglePassword('password')">👁️</span>
  </div>
</div>


        <div class="auth-actions">
          <a href="forgot.php" class="forgot-link">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn-login">Sign In</button>
      </form>

      <div class="meta">
        Don't have an account? <a href="register.php">Create account</a>
      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>


