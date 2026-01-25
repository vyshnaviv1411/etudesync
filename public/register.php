<?php
// public/register.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
$disable_dashboard_bg = false;

$errors = [];
$success = '';
$old = ['username' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $pw2 = $_POST['password_confirm'] ?? '';

    $old['username'] = $username;
    $old['email'] = $email;

    if ($username === '' || $email === '' || $password === '') {
        $errors[] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email.";
    } elseif ($password !== $pw2) {
        $errors[] = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors[] = "Email already registered.";
        } else {
            $pwHash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $ins->execute([$username, $email, $pwHash]);
            $success = "Account created. Please login.";
            $old = ['username' => '', 'email' => ''];
        }
    }
}

require_once __DIR__ . '/../includes/header_public.php';
?>

<div class="auth-page">
  <div class="auth-wrap container">

    <div class="glass-auth-card">
      <img src="assets/images/logo.jpg" alt="ÉtudeSync" class="logo-center" loading="lazy" />

      <h2>Create Account</h2>

      <?php if ($errors): ?>
        <div class="form-error flash-message"><ul>
        <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
        </ul></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="form-ok flash-message"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <form method="post" class="auth-form">

        <div class="input-group">
          <label>Username</label>
          <input type="text" name="username" value="<?= htmlspecialchars($old['username']) ?>" required />
        </div>

        <div class="input-group">
          <label>Email</label>
          <input type="email" name="email" value="<?= htmlspecialchars($old['email']) ?>" required />
        </div>

       <div class="input-group">
  <label>Password</label>
  <div class="password-box">
    <input type="password" id="password" name="password" required>
    <span class="toggle-eye" onclick="togglePassword('password')">👁️</span>
  </div>
</div>


        <div class="input-group">
          <label>Confirm</label>
  <div class="password-box">
      <input type="password" name="password_confirm" required />
    <span class="toggle-eye" onclick="togglePassword('password')">👁️</span>
  </div>
</div>


        <button type="submit" class="btn-login">Create account</button>

        <div class="meta">Already have an account? <a href="login.php">Sign in</a></div>

      </form>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

