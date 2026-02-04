<?php
session_start();
require_once __DIR__ . '/../../includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
if ($admin && password_verify($password, $admin['password'])) {
    $_SESSION['admin_id']    = $admin['admin_id'];
    $_SESSION['admin_name']  = $admin['name'];
    $_SESSION['admin_email'] = $admin['email']; // ✅ MUST be here

    header('Location: dashboard.php');
    exit;
}
else {
        $error = 'Invalid admin credentials';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login – ÉtudeSync</title>

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

/* 🌤️ SOFTER OVERLAY (keeps original brightness) */
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
  width: 420px;
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
  margin-bottom: 26px;
  font-weight: 700;
}

/* ERROR */
.error {
  background: rgba(255,0,0,0.18);
  padding: 10px 14px;
  border-radius: 10px;
  margin-bottom: 16px;
  font-size: 14px;
}

/* FORM */
.form-group {
  margin-bottom: 18px;
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

/* 👁️ EYE TOGGLE */
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
  <h2>Admin Login</h2>

  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" required>
    </div>

    <div class="form-group">
      <label>Password</label>
      <div class="password-box">
        <input type="password" id="password" name="password" required>
        <span class="toggle-eye" onclick="togglePassword()">👁️</span>
      </div>
    </div>

    <button class="btn">Login</button>
  </form>

  <div class="meta">
    New admin? <a href="signup.php">Create admin account</a>
  </div>
</div>

<script>
function togglePassword() {
  const p = document.getElementById('password');
  p.type = p.type === 'password' ? 'text' : 'password';
}
</script>

</body>
</html>
