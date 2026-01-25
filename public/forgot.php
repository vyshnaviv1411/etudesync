<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../includes/header_public.php';

// read reset link from session
$resetLink = $_SESSION['reset_link'] ?? null;
unset($_SESSION['reset_link']);
?>

<div class="auth-page">
  <div class="auth-wrap">
    <div class="glass-auth-card">

      <h2>Forgot Password</h2>
      <p class="small-muted">Enter your registered email</p>

      <?php if ($resetLink): ?>
        <div class="form-ok">
          <p style="margin-bottom:8px;">Reset link generated:</p>
          <a href="<?= htmlspecialchars($resetLink) ?>"
             class="btn small"
             style="display:inline-block">
            Reset Password
          </a>
        </div>
      <?php endif; ?>

      <form action="send_reset.php" method="POST">
        <div class="input-group">
          <label>Email</label>
          <input type="email" name="email" required>
        </div>

        <button class="btn btn-login">Send Reset Link</button>
      </form>

      <div class="meta">
        <a href="login.php">Back to login</a>
      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
