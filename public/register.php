<?php
// public/register.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
$disable_dashboard_bg = false;
$body_class = "register-page";

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

$errors = [];

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
   } elseif (!preg_match('/^[A-Za-z0-9._%+-]+@gmail\.com$/i', $email)) {
    $errors[] = "Only Gmail addresses (@gmail.com) are allowed.";
}




elseif ($password !== $pw2) {
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
          $_SESSION['success'] = "Account created successfully. You can now log in.";
header("Location: register.php");
exit;

        }
    }
}

require_once __DIR__ . '/../includes/header_public.php';
?>

<div class="auth-page">
  <div class="auth-wrap ">
   

<div class="glass-auth-card">

  <h2>Create Account</h2>

  <?php if (!empty($errors)): ?>
      <div class="form-error flash-message">
          <?php foreach ($errors as $e): ?>
              <div><?= htmlspecialchars($e) ?></div>
          <?php endforeach; ?>
      </div>
  <?php endif; ?>

  <?php if ($success): ?>
      <div class="form-ok flash-message">
          <?= htmlspecialchars($success) ?>
      </div>
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
    <span class="toggle-eye" onclick="togglePassword('password_confirm')">👁️</span>
  </div>
</div>

        <button type="submit" class="btn-login">Create account</button>

        <div class="meta">Already have an account? <a href="login.php">Sign in</a></div>

      </form>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

