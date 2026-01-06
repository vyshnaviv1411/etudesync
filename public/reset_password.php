<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
date_default_timezone_set('Asia/Kolkata');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header_public.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if (!$token) {
    $error = 'Invalid or missing reset token.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token    = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {

        $stmt = $pdo->prepare("
            SELECT * FROM password_resets
            WHERE token = ?
              AND expires_at > NOW()
            LIMIT 1
        ");
        $stmt->execute([$token]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reset) {
            $error = 'Reset link expired or invalid.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $pdo->prepare(
                "UPDATE users SET password_hash = ? WHERE id = ?"
            )->execute([$hash, $reset['user_id']]);

            // IMPORTANT: delete token after use
            $pdo->prepare(
                "DELETE FROM password_resets WHERE token = ?"
            )->execute([$token]);

            $success = 'Password updated successfully. You can now log in.';
        }
    }
}
?>

<div class="auth-page reset-only">
  <div class="auth-wrap">
    <div class="glass-auth-card reset-only">

      <h2>Reset Password</h2>
      <p class="small-muted">Enter your new password</p>

      <?php if ($error): ?>
        <div class="form-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="form-ok"><?= htmlspecialchars($success) ?></div>
        <a href="login.php" class="btn btn-login" style="margin-top:12px">
          Back to Login
        </a>
      <?php else: ?>

      <form method="POST" novalidate>
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

        <div class="input-group">
          <label>New Password</label>
          <div class="password-box">
            <input type="password" name="password" required>
            <span class="toggle-eye">👁️</span>
          </div>
        </div>

        <div class="input-group">
          <label>Confirm Password</label>
          <div class="password-box">
            <input type="password" name="confirm_password" required>
            <span class="toggle-eye">👁️</span>
          </div>
        </div>

        <button type="submit" class="btn btn-login">
          Update Password
        </button>
      </form>

      <div class="meta">
        <a href="login.php">Back to login</a>
      </div>

      <?php endif; ?>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
