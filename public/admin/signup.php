<?php
session_start();
require_once __DIR__ . '/../../includes/db.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '' || $confirm === '') {
        $errors[] = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    } elseif ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    } else {
        // Check if admin already exists
        $stmt = $pdo->prepare("SELECT admin_id FROM admins WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors[] = 'Admin with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare(
                "INSERT INTO admins (name, email, password) VALUES (?, ?, ?)"
            )->execute([$name, $email, $hash]);

            $success = 'Admin account created successfully. You can now login.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Signup – ÉtudeSync</title>

<style>
* {
  box-sizing: border-box;
  font-family: 'Poppins', system-ui, sans-serif;
}

body {
  margin: 0;
  min-height: 100vh;
  background: url("../assets/images/admin.jpg") center / cover no-repeat fixed;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* 🌤️ Soft overlay */
body::before {
  content: "";
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.35);
  backdrop-filter: blur(2px);
  z-index: -1;
}

/* GLASS CARD */
.admin-card {
  width: 440px;
  padding: 34px;
  border-radius: 22px;
  background: rgba(255,255,255,0.18);
  backdrop-filter: blur(22px) saturate(140%);
  border: 1px solid rgba(255,255,255,0.35);
  box-shadow: 0 40px 90px rgba(0,0,0,0.35);
  color: #fff;
}

.admin-card h2 {
  text-align: center;
  margin-bottom: 24px;
  font-weight: 700;
}

/* MESSAGES */
.error {
  background: rgba(255,0,0,0.18);
  padding: 10px 14px;
  border-radius: 10px;
  margin-bottom: 14px;
  font-size: 14px;
}

.success {
  background: rgba(0,255,140,0.18);
  padding: 10px 14px;
  border-radius: 10px;
  margin-bottom: 14px;
  font-size: 14px;
}

/* FORM */
.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 6px;
  font-weight: 600;
  font-size: 14px;
}

/* PASSWORD BOX */
.password-box {
  position: relative;
}

.form-group input {
  width: 100%;
  padding: 12px 42px 12px 14px;
  border-radius: 12px;
  border: none;
  outline: none;
  font-size: 14px;
}

/* 👁️ Eye toggle */
.toggle-eye {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  font-size: 16px;
  opacity: 0.7;
}

.toggle-eye:hover {
  opacity: 1;
}

/* BUTTON */
.btn {
  width: 100%;
  padding: 14px;
  border-radius: 14px;
  border: none;
  font-weight: 700;
  cursor: pointer;
  background: linear-gradient(90deg, #7c4dff, #47d7d3);
  color: #fff;
  margin-top: 10px;
}

.btn:hover {
  opacity: 0.96;
}

/* LINKS */
.meta {
  margin-top: 16px;
  text-align: center;
  font-size: 14px;
}

.meta a {
  color: #a5f3fc;
  text-decoration: underline;
}
</style>
</head>

<body>

<div class="admin-card">
  <h2>Create Admin Account</h2>

  <?php if ($errors): ?>
    <div class="error">
      <ul style="margin:0;padding-left:18px;">
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group">
      <label>Name</label>
      <input type="text" name="name" required>
    </div>

    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" required>
    </div>

    <div class="form-group">
      <label>Password</label>
      <div class="password-box">
        <input type="password" id="password" name="password" required>
        <span class="toggle-eye" onclick="togglePassword('password')">👁️</span>
      </div>
    </div>

    <div class="form-group">
      <label>Confirm Password</label>
      <div class="password-box">
        <input type="password" id="confirm" name="confirm_password" required>
        <span class="toggle-eye" onclick="togglePassword('confirm')">👁️</span>
      </div>
    </div>

    <button class="btn">Create Admin</button>
  </form>

  <div class="meta">
    Already have an account? <a href="login.php">Login</a>
  </div>
</div>

<script>
function togglePassword(id) {
  const el = document.getElementById(id);
  el.type = el.type === 'password' ? 'text' : 'password';
}
</script>

</body>
</html>
